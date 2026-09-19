<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();
echo "Current connected database: " . $db->query("SELECT DATABASE()")->fetchColumn() . "\n";
echo "All databases in MySQL:\n";
print_r($db->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN));
