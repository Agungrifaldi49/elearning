<?php
$hosts = ['127.0.0.1', 'localhost', '::1'];
foreach ($hosts as $host) {
    try {
        $pdo = new PDO("mysql:host={$host};port=3306", 'root', '', [
            PDO::ATTR_TIMEOUT => 2,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "SUCCESS connecting to {$host}\n";
    } catch (Exception $e) {
        echo "FAIL connecting to {$host}: " . $e->getMessage() . "\n";
    }
}
