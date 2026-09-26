<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';

$db = Database::getConnection();

echo "=== ROLES ===\n";
$roles = $db->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
print_r($roles);

echo "\n=== ADMIN USERS ===\n";
$adminUsers = $db->query("SELECT u.id, u.username, u.email, u.password, u.status, u.role_id, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE r.name LIKE '%admin%' OR u.username LIKE '%admin%' OR u.role_id = 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($adminUsers);

echo "\n=== RECENT LOGIN LOGS ===\n";
$logs = $db->query("SELECT * FROM log_login ORDER BY id DESC LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);
print_r($logs);
