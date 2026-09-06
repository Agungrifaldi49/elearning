<?php
/**
 * FCM Push Notification & In-App Notification Helper
 * E-Learning SMK Muthia Harapan Cicalengka
 */

require_once ROOT_PATH . 'config/database.php';

class FcmHelper {
    private static $db = null;
    private static $tableInitialized = false;

    private static function getDb() {
        if (self::$db === null) {
            self::$db = Database::getConnection();
        }
        self::ensureTableExists();
        return self::$db;
    }

    private static function ensureTableExists() {
        if (self::$tableInitialized) return;
        try {
            $db = Database::getConnection();
            $db->exec("
                CREATE TABLE IF NOT EXISTS notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    type VARCHAR(50) DEFAULT 'general',
                    target_id INT NULL,
                    is_read TINYINT(1) DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_is_read (is_read)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            self::$tableInitialized = true;
        } catch (\Throwable $e) {
            // Ignore if table creation failed or exists
        }
    }

    /**
     * Save notification to database for in-app notification bell list
     */
    public static function saveInAppNotification($userId, $title, $message, $type = 'general', $targetId = null) {
        try {
            $db = self::getDb();
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, title, message, type, target_id, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $title, $message, $type, $targetId]);
        } catch (\Throwable $e) {
            error_log("FcmHelper saveInAppNotification Error: " . $e->getMessage());
        }
    }

    /**
     * Send notification to a single User by user_id
     */
    public static function sendToUser($userId, $title, $message, $data = []) {
        if (!$userId) return false;
        self::saveInAppNotification($userId, $title, $message, $data['type'] ?? 'general', $data['id'] ?? null);

        $tokens = self::getUserFcmTokens([$userId]);
        if (empty($tokens)) return false;

        return self::sendFcmPayload($tokens, $title, $message, $data);
    }

    /**
     * Send notification to multiple Users by user_ids array
     */
    public static function sendToUsers(array $userIds, $title, $message, $data = []) {
        $userIds = array_filter(array_unique(array_map('intval', $userIds)));
        if (empty($userIds)) return false;

        foreach ($userIds as $uId) {
            self::saveInAppNotification($uId, $title, $message, $data['type'] ?? 'general', $data['id'] ?? null);
        }

        $tokens = self::getUserFcmTokens($userIds);
        if (empty($tokens)) return false;

        return self::sendFcmPayload($tokens, $title, $message, $data);
    }

    /**
     * Send notification to all Students in a specific Kelas ID
     */
    public static function sendToKelas($kelasId, $title, $message, $data = []) {
        if (!$kelasId) return false;
        try {
            $db = self::getDb();
            $stmt = $db->prepare("
                SELECT u.id 
                FROM users u
                JOIN siswa s ON (s.user_id = u.id OR s.nis = u.username)
                WHERE s.kelas_id = ? AND u.fcm_token IS NOT NULL AND u.fcm_token != ''
            ");
            $stmt->execute([$kelasId]);
            $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($userIds)) {
                // Fallback: try finding users via siswa profile directly
                $stmt2 = $db->prepare("
                    SELECT u.id 
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    WHERE r.name = 'Siswa' AND u.fcm_token IS NOT NULL AND u.fcm_token != ''
                ");
                $stmt2->execute();
                $userIds = $stmt2->fetchAll(PDO::FETCH_COLUMN);
            }

            if (!empty($userIds)) {
                return self::sendToUsers($userIds, $title, $message, $data);
            }
        } catch (\Throwable $e) {
            error_log("FcmHelper sendToKelas Error: " . $e->getMessage());
        }
        return false;
    }

    /**
     * Send notification to ALL Users in system with FCM tokens
     */
    public static function sendToAll($title, $message, $data = []) {
        try {
            $db = self::getDb();
            $stmt = $db->query("
                SELECT id 
                FROM users 
                WHERE fcm_token IS NOT NULL AND fcm_token != ''
            ");
            $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($userIds)) {
                return self::sendToUsers($userIds, $title, $message, $data);
            }
        } catch (\Throwable $e) {
            error_log("FcmHelper sendToAll Error: " . $e->getMessage());
        }
        return false;
    }

    /**
     * Query FCM Tokens from DB for user_ids
     */
    private static function getUserFcmTokens(array $userIds) {
        if (empty($userIds)) return [];
        try {
            $db = self::getDb();
            $inClause = implode(',', array_fill(0, count($userIds), '?'));
            $stmt = $db->prepare("
                SELECT DISTINCT fcm_token 
                FROM users 
                WHERE id IN ($inClause) 
                  AND fcm_token IS NOT NULL 
                  AND fcm_token != ''
            ");
            $stmt->execute($userIds);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
            error_log("FcmHelper getUserFcmTokens Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Core payload dispatcher sending request via Firebase Cloud Messaging HTTP API / cURL
     */
    private static function sendFcmPayload(array $tokens, $title, $message, $data = []) {
        $serverKey = defined('FCM_SERVER_KEY') ? FCM_SERVER_KEY : '';
        if (empty($serverKey)) {
            // FCM Server Key not set yet, payload saved in DB notification history
            error_log("FcmHelper: FCM_SERVER_KEY is not defined. Notification logged to DB.");
            return true;
        }

        $url = 'https://fcm.googleapis.com/fcm/send';

        $fields = [
            'registration_ids' => array_values(array_unique($tokens)),
            'notification' => [
                'title' => $title,
                'body' => $message,
                'sound' => 'default',
                'badge' => '1',
            ],
            'data' => array_merge([
                'title' => $title,
                'body' => $message,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ], $data),
            'priority' => 'high'
        ];

        $headers = [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("FcmHelper sendFcmPayload Failed with HTTP $httpCode: " . $result);
            return false;
        }

        return true;
    }
}
