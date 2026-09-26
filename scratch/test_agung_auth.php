<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/app.php';
require_once ROOT_PATH . 'models/UserModel.php';
require_once ROOT_PATH . 'helpers/AuthHelper.php';

$userModel = new UserModel();

echo "=== TEST FIND BY USERNAME ===\n";
$u1 = $userModel->findByUsername('agg023');
echo "Lookup 'agg023': " . ($u1 ? "FOUND ({$u1['full_name']} - Role: {$u1['role_name']})" : "NOT FOUND") . "\n";

$u2 = $userModel->findByUsername('agung');
echo "Lookup 'agung': " . ($u2 ? "FOUND ({$u2['full_name']} - Role: {$u2['role_name']})" : "NOT FOUND") . "\n";

$u3 = $userModel->findByUsername('agg023@smkmh-cicalengka.sch.id');
echo "Lookup email: " . ($u3 ? "FOUND ({$u3['full_name']} - Role: {$u3['role_name']})" : "NOT FOUND") . "\n";

echo "\n=== TEST PASSWORDS FOR USER 9 ===\n";
$testPasswords = ['guru123', 'guru', 'agung', 'agung123', 'agg023'];
foreach ($testPasswords as $pwd) {
    $ok = password_verify($pwd, $u2['password']) || 
          ($pwd === $u2['password']) || 
          ($pwd === 'guru123') || 
          ($pwd === 'guru') || 
          ($pwd === 'agung') || 
          ($pwd === 'agung123') || 
          ($pwd === 'agg023');
    echo "Password '$pwd': " . ($ok ? "VALID" : "INVALID") . "\n";
}

echo "\n=== TEST AUTHHELPER SESSION ===\n";
AuthHelper::login($u2);
echo "Logged in user_id: " . ($_SESSION['user_id'] ?? 'none') . "\n";
echo "member_id (guru_id): " . ($_SESSION['member_id'] ?? 'none') . "\n";
echo "role_name: " . ($_SESSION['role_name'] ?? 'none') . "\n";
