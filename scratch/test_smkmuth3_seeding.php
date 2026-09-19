<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';

$pdo = new PDO('mysql:host=127.0.0.1;dbname=smkmuth3_db_elearning_smkmh;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$refProp = new ReflectionProperty('Database', 'conn');
$refProp->setAccessible(true);
$refProp->setValue(null, $pdo);

Database::ensureCustomTables();

echo 'Kurikulum in smkmuth3: ' . $pdo->query('SELECT COUNT(*) FROM kurikulum')->fetchColumn() . PHP_EOL;
echo 'Fase in smkmuth3: ' . $pdo->query('SELECT COUNT(*) FROM fase')->fetchColumn() . PHP_EOL;
echo 'Komponen in smkmuth3: ' . $pdo->query('SELECT COUNT(*) FROM komponen_penilaian')->fetchColumn() . PHP_EOL;
