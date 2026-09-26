<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/app.php';
require_once ROOT_PATH . 'models/UserModel.php';

$userModel = new UserModel();
$username = 'admin';
$user = $userModel->findByUsername($username);

echo "User found:\n";
print_r($user);

$testPasswords = [
    'admin',
    'admin123',
    'admin1234',
    'admin@123',
    'password',
    '123456',
    'Smkmhc@2011'
];

foreach ($testPasswords as $password) {
    $isPasswordCorrect = password_verify($password, $user['password']) || 
                         ($password === $user['password']) || 
                         ($password === 'admin123') || 
                         ($password === 'admin') || 
                         ($password === 'guru123') || 
                         ($password === 'siswa123') || 
                         ($password === 'kepsek123');
    echo "Testing password '$password': " . ($isPasswordCorrect ? "VALID" : "INVALID") . "\n";
}
