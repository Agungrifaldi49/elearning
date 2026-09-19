<?php
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/database.php';
$db = Database::getConnection();

echo "=== ALL JURUSAN & KELAS IN DB ===\n";
$classes = $db->query("
    SELECT k.*, j.kode_jurusan, j.nama_jurusan 
    FROM kelas k 
    LEFT JOIN jurusan j ON k.jurusan_id = j.id
")->fetchAll(PDO::FETCH_ASSOC);
print_r($classes);

$jur = $db->query("SELECT * FROM jurusan")->fetchAll(PDO::FETCH_ASSOC);
print_r($jur);
