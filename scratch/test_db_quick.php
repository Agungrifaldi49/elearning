<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=db_elearning_smkmh;charset=utf8mb4', 'root', '', [
        PDO::ATTR_TIMEOUT => 2,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "CONNECTED TO DB SUCCESSFULLY\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables count: " . count($tables) . "\n";
} catch (Throwable $ex) {
    echo "DB ERROR: " . $ex->getMessage() . "\n";
}
