<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';

$db = Database::getConnection();
$newHash = password_hash('admin123', PASSWORD_BCRYPT);

$stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$newHash]);

echo "Admin password updated successfully in database.\n";
$user = $db->query("SELECT id, username, password FROM users WHERE username = 'admin'")->fetch(PDO::FETCH_ASSOC);
echo "New hash: " . $user['password'] . "\n";
echo "Verify 'admin123': " . (password_verify('admin123', $user['password']) ? "SUCCESS" : "FAIL") . "\n";
echo "Verify 'admin': " . (password_verify('admin', $user['password']) ? "SUCCESS" : "FAIL") . "\n";
