<?php
/**
 * FCM Push Notification & In-App Notification Helper (Firebase HTTP v1 API)
 * E-Learning SMK Muthia Harapan Cicalengka
 */

require_once ROOT_PATH . 'config/database.php';

class FcmHelper {
    private static $db = null;
    private static $tableInitialized = false;
    private static $cachedAccessToken = null;
    private static $accessTokenExpiresAt = 0;

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
                CREATE TABLE IF NOT EXISTS notifikasi (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    type VARCHAR(50) DEFAULT 'general',
                    target_id INT NULL,
                    link VARCHAR(255) NULL,
                    is_read TINYINT(1) DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_is_read (is_read)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            try { $db->exec("ALTER TABLE notifikasi ADD COLUMN type VARCHAR(50) DEFAULT 'general'"); } catch (\Throwable $e) {}
            try { $db->exec("ALTER TABLE notifikasi ADD COLUMN target_id INT NULL"); } catch (\Throwable $e) {}
            self::$tableInitialized = true;
        } catch (\Throwable $e) {
            // Ignore if table creation failed or exists
        }
    }

    /**
     * Helper to base64UrlEncode string data according to JWT spec
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Generate OAuth 2.0 Access Token from Service Account JSON using pure PHP OpenSSL
     */
    private static function getGoogleAccessToken() {
        if (self::$cachedAccessToken && time() < (self::$accessTokenExpiresAt - 60)) {
            return self::$cachedAccessToken;
        }

        $credentialsPath = defined('FCM_CREDENTIALS_PATH') ? FCM_CREDENTIALS_PATH : ROOT_PATH . 'config/firebase_credentials.json';
        if (!file_exists($credentialsPath)) {
            $altPath = ROOT_PATH . 'config/firebase_credentials.json.json';
            if (file_exists($altPath)) {
                $credentialsPath = $altPath;
            } else {
                error_log("FcmHelper: Firebase Service Account JSON file not found at: $credentialsPath");
                return null;
            }
        }

        $jsonContent = file_get_contents($credentialsPath);
        $json = json_decode($jsonContent, true);
        if (!$json || !isset($json['private_key']) || !isset($json['client_email'])) {
            error_log("FcmHelper: Invalid Service Account JSON file format at $credentialsPath");
            return null;
        }

        $clientEmail = $json['client_email'];
        $privateKey = $json['private_key'];
        $tokenUri = $json['token_uri'] ?? 'https://oauth2.googleapis.com/token';

        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claimSet = [
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $tokenUri,
            'iat' => $now,
            'exp' => $now + 3600
        ];

        $encodedHeader = self::base64UrlEncode(json_encode($header));
        $encodedClaimSet = self::base64UrlEncode(json_encode($claimSet));

        $signingInput = $encodedHeader . '.' . $encodedClaimSet;
        $signature = '';

        if (!openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            error_log("FcmHelper: Failed to sign JWT with OpenSSL.");
            return null;
        }

        $jwt = $signingInput . '.' . self::base64UrlEncode($signature);

        // Exchange JWT for Google OAuth2 Access Token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUri);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("FcmHelper: OAuth token exchange failed with HTTP $httpCode: $response");
            return null;
        }

        $data = json_decode($response, true);
        if (isset($data['access_token'])) {
            self::$cachedAccessToken = $data['access_token'];
            self::$accessTokenExpiresAt = $now + (int)($data['expires_in'] ?? 3600);
            return self::$cachedAccessToken;
        }

        return null;
    }

    /**
     * Save notification to database for in-app notification bell list
     */
    public static function saveInAppNotification($userId, $title, $message, $type = 'general', $targetId = null) {
        try {
            $db = self::getDb();
            $stmt = $db->prepare("
                INSERT INTO notifikasi (user_id, title, message, type, target_id, created_at)
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
     * Send notification to all Teachers (Guru) in system or specific Guru User IDs
     */
    public static function sendToTeachers($title, $message, $data = []) {
        try {
            $db = self::getDb();
            $stmt = $db->query("
                SELECT u.id 
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                JOIN guru g ON (g.user_id = u.id OR g.nip = u.username)
                WHERE (r.name IN ('Guru', 'Kepala Sekolah') OR u.role_id IN (2, 4))
            ");
            $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($userIds)) {
                return self::sendToUsers($userIds, $title, $message, $data);
            }
        } catch (\Throwable $e) {
            error_log("FcmHelper sendToTeachers Error: " . $e->getMessage());
        }
        return false;
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
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE s.kelas_id = ? AND (r.name = 'Siswa' OR u.role_id = 3)
            ");
            $stmt->execute([$kelasId]);
            $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($userIds)) {
                // Fallback: try finding users via siswa role directly
                $stmt2 = $db->prepare("
                    SELECT u.id 
                    FROM users u
                    LEFT JOIN roles r ON u.role_id = r.id
                    WHERE (r.name = 'Siswa' OR u.role_id = 3)
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
            $stmt = $db->query("SELECT id FROM users");
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
     * Core payload dispatcher sending request via Firebase Cloud Messaging HTTP v1 API
     */
    private static function sendFcmPayload(array $tokens, $title, $message, $data = []) {
        $tokens = array_values(array_unique(array_filter($tokens)));
        if (empty($tokens)) return false;

        $accessToken = self::getGoogleAccessToken();
        if (!$accessToken) {
            error_log("FcmHelper: Failed to obtain Google OAuth2 access token. Notification saved to DB only.");
            return false;
        }

        $projectId = defined('FCM_PROJECT_ID') ? FCM_PROJECT_ID : 'elearning-ff3d0';
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        // Format data map values as strings (FCM v1 requirement: map<string, string>)
        $stringData = [];
        foreach ($data as $k => $v) {
            $stringData[(string)$k] = (string)$v;
        }
        $stringData['title'] = (string)$title;
        $stringData['body'] = (string)$message;
        $stringData['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';

        $successCount = 0;

        foreach ($tokens as $token) {
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => (string)$title,
                        'body' => (string)$message,
                    ],
                    'data' => $stringData,
                    'android' => [
                        'priority' => 'HIGH',
                        'direct_boot_ok' => true,
                        'notification' => [
                            'title' => (string)$title,
                            'body' => (string)$message,
                            'channel_id' => 'high_importance_channel',
                            'sound' => 'default',
                            'default_sound' => true,
                            'default_vibrate_timings' => true,
                            'notification_priority' => 'PRIORITY_MAX',
                            'visibility' => 'PUBLIC',
                            'icon' => '@mipmap/ic_launcher'
                        ]
                    ]
                ]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $successCount++;
            } else {
                error_log("FcmHelper FCM v1 Error (HTTP $httpCode) for token $token: " . $result);
            }
        }

        return ($successCount > 0);
    }
}
