<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SiswaModel.php';

$db = Database::getConnection();
$siswaModel = new SiswaModel();
$list = $siswaModel->getAll();

echo "Total Siswa Terdaftar: " . count($list) . "\n";
if (!empty($list)) {
    $first = $list[0];
    echo "ID Siswa: " . $first['id'] . "\n";
    echo "Nama Siswa: " . $first['nama_lengkap'] . "\n";
    echo "No Telepon Siswa: " . ($first['no_telepon'] ?? '-') . "\n";
    echo "No Ortu: " . ($first['no_ortu'] ?? 'KOSONG') . "\n";
}
