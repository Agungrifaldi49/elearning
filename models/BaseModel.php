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
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $this->db->prepare("INSERT INTO aktivitas (user_id, activity, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $activity, $ip]);
    }
}
