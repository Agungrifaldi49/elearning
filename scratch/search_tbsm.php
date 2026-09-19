<?php
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/database.php';
$db = Database::getConnection();

echo "=== JURUSAN ===\n";
print_r($db->query("SELECT * FROM jurusan")->fetchAll(PDO::FETCH_ASSOC));

echo "=== KELAS ===\n";
print_r($db->query("SELECT k.*, j.kode_jurusan, j.nama_jurusan FROM kelas k LEFT JOIN jurusan j ON k.jurusan_id = j.id")->fetchAll(PDO::FETCH_ASSOC));

echo "=== SISWA JURUSAN/KELAS ===\n";
print_r($db->query("SELECT s.id, s.nama_lengkap, s.nis, s.kelas_id, k.nama_kelas, k.jurusan_id, j.kode_jurusan FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id LEFT JOIN jurusan j ON k.jurusan_id = j.id LIMIT 20")->fetchAll(PDO::FETCH_ASSOC));

echo "=== CHECK IF ANY TBSM IN ANY TABLE ===\n";
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    try {
        $cols = $db->query("SHOW COLUMNS FROM `{$t}`")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($cols as $c) {
            $stmt = $db->query("SELECT COUNT(*) FROM `{$t}` WHERE `{$c}` LIKE '%TBSM%' OR `{$c}` LIKE '%Sepeda Motor%'");
            $cnt = (int)$stmt->fetchColumn();
            if ($cnt > 0) {
                echo "Found {$cnt} matches in {$t}.{$c}\n";
            }
        }
    } catch (\Throwable $e) {}
}
