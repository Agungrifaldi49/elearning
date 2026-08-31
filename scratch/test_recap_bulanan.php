<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'models/AbsensiModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';

echo "=== TEST REKAP ABSENSI BULANAN GURU ===\n";

$absensiModel = new AbsensiModel();
$academicModel = new AcademicModel();

// Get first guru ID from DB
$db = Database::getConnection();
$guru = $db->query("SELECT id, nama_lengkap FROM guru LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if (!$guru) {
    echo "No guru found in DB!\n";
    exit;
}

$guruId = $guru['id'];
echo "Guru ID: {$guruId} ({$guru['nama_lengkap']})\n";

$kelasList = $academicModel->getKelasByGuru($guruId);
echo "Classes taught by guru (" . count($kelasList) . "):\n";
foreach ($kelasList as $k) {
    echo " - Class ID {$k['id']}: {$k['nama_kelas']}\n";
}

// Test getMonthlyRecapSiswa for kelas_id = 0 (Seluruh Kelas)
$recapSeluruh = $absensiModel->getMonthlyRecapSiswa(date('m'), date('Y'), 0, $guruId);
echo "\nSeluruh Kelas (kelas_id = 0):\n";
echo " - Total Siswa count: " . count($recapSeluruh['data']) . "\n";

if (!empty($kelasList)) {
    $firstKelasId = $kelasList[0]['id'];
    $recapPerKelas = $absensiModel->getMonthlyRecapSiswa(date('m'), date('Y'), $firstKelasId, $guruId);
    echo "\nPer Kelas (kelas_id = {$firstKelasId}):\n";
    echo " - Total Siswa count: " . count($recapPerKelas['data']) . "\n";
}

echo "\nTEST COMPLETED SUCCESSFULLY!\n";
