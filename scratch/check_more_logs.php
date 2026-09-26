<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';
$db = Database::getConnection();
$logs = $db->query("SELECT * FROM log_login ORDER BY id DESC LIMIT 30")->fetchAll(PDO::FETCH_ASSOC);
foreach ($logs as $l) {
    echo "ID: {$l['id']} | User: {$l['username']} | Status: {$l['status']} | Time: {$l['created_at']}\n";
}
