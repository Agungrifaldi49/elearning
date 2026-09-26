<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';
$db = Database::getConnection();

$user = $db->query("SELECT * FROM users WHERE id = 9")->fetch(PDO::FETCH_ASSOC);
echo "User 9 (Guru Agung Rifaldi):\n";
print_r($user);

$candidates = [
    'agg023',
    'agung',
    'agung123',
    'guru123',
    'guru',
    'admin123',
    '123456',
    'Smkmhc@2011',
    'bismillah',
    '32042523010400001'
];

foreach ($candidates as $c) {
    if (password_verify($c, $user['password'])) {
        echo "MATCH: '$c'\n";
    }
}
