<?php
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/database.php';
$db = Database::getConnection();

echo "=== ROMBEL_KURIKULUM ===\n";
$rk = $db->query("SELECT rk.*, k.nama_kelas, kur.nama as nama_kurikulum FROM rombel_kurikulum rk LEFT JOIN kelas k ON rk.rombel_id = k.id LEFT JOIN kurikulum kur ON rk.kurikulum_id = kur.id")->fetchAll(PDO::FETCH_ASSOC);
print_r($rk);

echo "\n=== SISWA PER KELAS ===\n";
$sk = $db->query("SELECT s.kelas_id, k.nama_kelas, COUNT(*) as total_siswa FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id GROUP BY s.kelas_id")->fetchAll(PDO::FETCH_ASSOC);
print_r($sk);

echo "\n=== JADWAL OR TEACHER ASSIGNMENTS PER KELAS ===\n";
$jdw = $db->query("SELECT j.*, k.nama_kelas, g.nama_lengkap as guru_nama FROM jadwal j LEFT JOIN kelas k ON j.kelas_id = k.id LEFT JOIN guru g ON j.guru_id = g.id")->fetchAll(PDO::FETCH_ASSOC);
print_r($jdw);

echo "\n=== MAPEL_ENROLLMENT_KEYS ===\n";
$mek = $db->query("SELECT mek.*, k.nama_kelas FROM mapel_enrollment_keys mek LEFT JOIN kelas k ON mek.kelas_id = k.id")->fetchAll(PDO::FETCH_ASSOC);
print_r($mek);

echo "\n=== ALL GURUS ===\n";
$gurus = $db->query("SELECT id, nama_lengkap, nip FROM guru")->fetchAll(PDO::FETCH_ASSOC);
print_r($gurus);
