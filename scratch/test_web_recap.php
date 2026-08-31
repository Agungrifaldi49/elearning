<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'models/AbsensiModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';
require_once ROOT_PATH . 'models/GuruModel.php';

$db = Database::getConnection();
$absensiModel = new AbsensiModel();
$academicModel = new AcademicModel();
$guruModel = new GuruModel();

$users = $db->query("
    SELECT u.id as user_id, u.username, u.full_name, r.name as role_name 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.id
")->fetchAll(PDO::FETCH_ASSOC);

echo "=== DIAGNOSTIC FOR ALL LOGGED IN USERS ===\n";

foreach ($users as $u) {
    $userId = $u['user_id'];
    $roleName = strtolower($u['role_name'] ?? '');
    $isAdmin = in_array($roleName, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);
    
    $guruProfile = $guruModel->ensureGuruProfile($userId, $u['full_name']);
    $guruId = $guruProfile['id'] ?? 0;
    
    $bulan = date('m');
    $tahun = date('Y');
    $kelasId = 0;
    
    $queryGuruId = $isAdmin ? null : $guruId;
    if ($isAdmin) {
        $kelasList = $academicModel->getKelas();
        $targetKelas = $kelasId;
    } else {
        $kelasList = $academicModel->getKelasByGuru($guruId);
        $myKelasIds = array_column($kelasList, 'id');
        if ($kelasId > 0 && in_array($kelasId, $myKelasIds)) {
            $targetKelas = $kelasId;
        } else {
            $targetKelas = !empty($myKelasIds) ? $myKelasIds : 0;
        }
    }

    $monthlyRecap = $absensiModel->getMonthlyRecapSiswa($bulan, $tahun, $targetKelas, $queryGuruId);
    $dataCount = count($monthlyRecap['data'] ?? []);
    
    echo "User #{$userId} ({$u['username']} / {$u['full_name']}) - Role: {$u['role_name']} - GuruID: {$guruId}:\n";
    echo "  - Admin mode: " . ($isAdmin ? "YES" : "NO") . "\n";
    echo "  - Dropdown kelas count: " . count($kelasList) . "\n";
    echo "  - Target kelas passed to query: " . json_encode($targetKelas) . "\n";
    echo "  - Monthly recap data count returned: {$dataCount}\n";
    if ($dataCount === 0) {
        echo "  - WARNING: 0 DATA RETURNED!\n";
    }
    echo "-----------------------------------------------------\n";
}
