<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';
$db = Database::getConnection();

echo "=== 1. KEPSEK: MONITORING GURU ===\n";
$gurus = $db->query("
    SELECT g.id, g.nip, g.nama_lengkap, g.status, u.username, u.email
    FROM guru g
    JOIN users u ON g.user_id = u.id
    ORDER BY g.nama_lengkap ASC
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($gurus as $g) {
    echo "ID: {$g['id']} | NIP: {$g['nip']} | Nama: {$g['nama_lengkap']} | User: {$g['username']}\n";
}

echo "\n=== 2. KEPSEK: MONITORING SISWA (KELAS X RPL 1) ===\n";
$siswaKelas1 = $db->query("
    SELECT s.id, s.nis, s.nama_lengkap, k.nama_kelas, u.username, r.name as role_name
    FROM siswa s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN roles r ON u.role_id = r.id
    LEFT JOIN kelas k ON s.kelas_id = k.id
    WHERE s.kelas_id = 1
    ORDER BY s.nama_lengkap ASC
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($siswaKelas1 as $s) {
    echo "ID: {$s['id']} | NIS: {$s['nis']} | Nama: {$s['nama_lengkap']} | Kelas: {$s['nama_kelas']} | User: {$s['username']} | Role: {$s['role_name']}\n";
}

echo "\n=== 3. KEPSEK: MONITORING WALI KELAS (ROMBEL X RPL 1) ===\n";
$wali = $db->query("
    SELECT k.nama_kelas, g.nama_lengkap as nama_wali,
           (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id AND s.status = 'aktif') as total_siswa
    FROM kelas k
    LEFT JOIN guru g ON k.wali_kelas_id = g.id
    WHERE k.id = 1
")->fetch(PDO::FETCH_ASSOC);
echo "Kelas: {$wali['nama_kelas']} | Wali: {$wali['nama_wali']} | Total Siswa: {$wali['total_siswa']}\n";
