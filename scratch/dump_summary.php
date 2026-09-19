<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "=== TABLE COUNTS ===\n";
foreach ($tables as $t) {
    $c = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo str_pad($t, 30) . " : $c\n";
}

echo "\n=== CURRENT TAHUN AJARAN ===\n";
print_r($db->query("SELECT * FROM tahun_ajaran")->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== CURRENT SEMESTER ===\n";
print_r($db->query("SELECT * FROM semester")->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== CURRENT KELAS ===\n";
print_r($db->query("SELECT id, nama_kelas, tingkat, jurusan_id FROM kelas")->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== SAMPLE MAPEL ===\n";
print_r($db->query("SELECT id, kode_mapel, nama_mapel, jurusan_id, kkm FROM mata_pelajaran LIMIT 10")->fetchAll(PDO::FETCH_ASSOC));
