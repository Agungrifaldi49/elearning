<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';

$db = Database::getConnection();
$newHash = password_hash('guru123', PASSWORD_BCRYPT);

$stmt = $db->prepare("UPDATE users SET password = ? WHERE id = 9");
$stmt->execute([$newHash]);

echo "Guru user (id=9) password updated to bcrypt hash of 'guru123'.\n";
$user = $db->query("SELECT id, username, full_name, role_id, password FROM users WHERE id = 9")->fetch(PDO::FETCH_ASSOC);
print_r($user);
echo "Verify 'guru123': " . (password_verify('guru123', $user['password']) ? "SUCCESS" : "FAIL") . "\n";
