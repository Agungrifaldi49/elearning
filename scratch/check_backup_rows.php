<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';
$db = Database::getConnection();

$rows = $db->query('SELECT * FROM backup ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
echo "Rows in backup table:\n";
print_r($rows);
