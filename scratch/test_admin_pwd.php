<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';

$db = Database::getConnection();
$user = $db->query("SELECT * FROM users WHERE username = 'admin'")->fetch(PDO::FETCH_ASSOC);

echo "Admin User Details:\n";
print_r($user);

$candidates = [
    'admin',
    'admin123',
    'admin1234',
    'admin@123',
    'password',
    '123456',
    '12345678',
    'Smkmhc@2011',
    'smkmh2024',
    'smkmh2025',
    'smkmh2026',
    'smkmh123',
    'administrator',
    'adminlms',
    'admin_smkmh',
    'bismillah',
    'root',
    'secret'
];

echo "\nTesting password_verify:\n";
$matched = false;
foreach ($candidates as $pwd) {
    if (password_verify($pwd, $user['password'])) {
        echo "MATCH FOUND: '{$pwd}'\n";
        $matched = true;
        break;
    }
}

if (!$matched) {
    echo "No standard candidate matched the hash.\n";
    echo "Hash is: " . $user['password'] . "\n";
    $info = password_get_info($user['password']);
    print_r($info);
}
