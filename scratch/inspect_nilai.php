<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();
echo "DISTINCT SISWA IN nilai_rapor:\n";
print_r($db->query("SELECT DISTINCT siswa_id FROM nilai_rapor")->fetchAll(PDO::FETCH_COLUMN));

echo "\nALL SISWA:\n";
print_r($db->query("SELECT id, nama_lengkap, kelas_id FROM siswa")->fetchAll(PDO::FETCH_ASSOC));

echo "\nRAPOR SISWA RECORDS:\n";
print_r($db->query("SELECT rs.id, rs.siswa_id, rs.tahun_ajaran_id, rs.kurikulum_nama_snapshot, COUNT(rd.id) as total_mapel FROM rapor_siswa rs LEFT JOIN rapor_nilai_detail rd ON rs.id = rd.rapor_id GROUP BY rs.id")->fetchAll(PDO::FETCH_ASSOC));
