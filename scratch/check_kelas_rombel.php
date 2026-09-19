<?php
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/database.php';
$db = Database::getConnection();

echo "=== TABLES ===\n";
$stmt = $db->query("SHOW TABLES LIKE '%kelas%'");
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));

$stmt = $db->query("SHOW TABLES LIKE '%rombel%'");
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));

$stmt = $db->query("SHOW TABLES LIKE '%jurusan%'");
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));

echo "\n=== ALL JURUSAN ===\n";
$j = $db->query("SELECT * FROM jurusan")->fetchAll(PDO::FETCH_ASSOC);
print_r($j);

echo "\n=== ALL KELAS ===\n";
$k = $db->query("SELECT k.*, j.nama_jurusan, j.kode_jurusan FROM kelas k LEFT JOIN jurusan j ON k.jurusan_id = j.id")->fetchAll(PDO::FETCH_ASSOC);
print_r($k);

echo "\n=== ROMBEL_BELAJAR OR SIMILAR (IF EXISTS) ===\n";
try {
    $r = $db->query("SELECT * FROM rombel_belajar LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    print_r($r);
} catch (\Throwable $e) {
    echo "No rombel_belajar table.\n";
}

try {
    $r2 = $db->query("SELECT * FROM kurikulum_rombel LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    print_r($r2);
} catch (\Throwable $e) {
    echo "No kurikulum_rombel table.\n";
}
