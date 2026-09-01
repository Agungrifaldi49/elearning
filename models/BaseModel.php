<?php
/**
 * Base Model Class
 */
require_once ROOT_PATH . 'config/database.php';

class BaseModel {
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Helper to log user activity
     */
    public function logActivity($userId, $activity) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmt = $this->db->prepare("INSERT INTO aktivitas (user_id, activity, ip_address) VALUES (?, ?, ?)");
            $stmt->execute([$userId, substr((string)$activity, 0, 255), $ip]);
        } catch (\Throwable $e) {
            // Fail-safe logging so high traffic log table lock doesn't crash user requests
        }
    }
}
