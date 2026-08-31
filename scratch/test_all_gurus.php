<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'models/AbsensiModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';

$db = Database::getConnection();
$absensiModel = new AbsensiModel();
$academicModel = new AcademicModel();

$gurus = $db->query("SELECT id, nama_lengkap, nip FROM guru")->fetchAll(PDO::FETCH_ASSOC);

echo "=== DIAGNOSTIC ALL GURU RECAP (UPDATED TARGET KELAS) ===\n";
foreach ($gurus as $g) {
    $gId = $g['id'];
    $kelasList = $academicModel->getKelasByGuru($gId);
    $myKelasIds = array_column($kelasList, 'id');
    
    // Simulate GuruController logic for Seluruh Kelas (kelas_id = 0)
    $targetKelasSeluruh = !empty($myKelasIds) ? $myKelasIds : 0;
    
    $recap0 = $absensiModel->getMonthlyRecapSiswa(date('m'), date('Y'), $targetKelasSeluruh, $gId);
    $count0 = count($recap0['data'] ?? []);
    
    echo "Guru #{$gId} ({$g['nama_lengkap']}):\n";
    echo "  - Classes taught (" . count($kelasList) . "): " . implode(', ', array_column($kelasList, 'nama_kelas')) . " [IDs: " . implode(',', $myKelasIds) . "]\n";
    echo "  - Recap count for '-- Seluruh Kelas --': {$count0} students\n";
    
    foreach ($kelasList as $k) {
        $recapK = $absensiModel->getMonthlyRecapSiswa(date('m'), date('Y'), $k['id'], $gId);
        $countK = count($recapK['data'] ?? []);
        echo "    * Class #{$k['id']} ({$k['nama_kelas']}): {$countK} students\n";
    }
    echo "-----------------------------------------\n";
}
