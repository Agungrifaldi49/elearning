<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';
$db = Database::getConnection();

$hash = password_hash('siswa123', PASSWORD_BCRYPT);
$db->prepare('UPDATE users SET password = ? WHERE id = 7')->execute([$hash]);

$u7 = $db->query('SELECT id, username, full_name, role_id, password FROM users WHERE id = 7')->fetch(PDO::FETCH_ASSOC);
echo "User 7 Details:\n";
print_r($u7);
echo "Verify 'siswa123': " . (password_verify('siswa123', $u7['password']) ? "OK" : "FAIL") . "\n";
echo "Verify 'agung': " . (password_verify('agung', $u7['password']) ? "OK" : "FAIL") . "\n";
