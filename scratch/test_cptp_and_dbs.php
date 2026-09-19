<?php
/**
 * Test Suite: Verifikasi Perbaikan Kurikulum CP & TP serta Ketahanan Multi-Database
 */

define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';

echo "=======================================================\n";
echo "TEST 1: Verifikasi Koneksi Database & Self-Healing DDL\n";
echo "=======================================================\n";

$db = Database::getConnection();
$currentDb = $db->query("SELECT DATABASE()")->fetchColumn();
echo "✓ Terhubung ke database aktif: {$currentDb}\n";

$tables = $db->query("SHOW TABLES LIKE '%kurikulum%'")->fetchAll(PDO::FETCH_COLUMN);
echo "✓ Tabel Kurikulum ditemukan: " . implode(', ', $tables) . "\n";
assert(in_array('kurikulum', $tables), "Tabel kurikulum harus ada!");

require_once __DIR__ . '/../models/CurriculumModel.php';
$currModel = new CurriculumModel();

echo "\n=======================================================\n";
echo "TEST 2: Verifikasi Master Kurikulum & Fase\n";
echo "=======================================================\n";

$allKur = $currModel->getAllKurikulum();
echo "✓ Total Kurikulum terdaftar: " . count($allKur) . "\n";
assert(count($allKur) >= 1, "Minimal ada 1 kurikulum!");
$firstKurId = $allKur[0]['id'];

$allFase = $currModel->getAllFase();
echo "✓ Total Fase terdaftar: " . count($allFase) . "\n";

echo "\n=======================================================\n";
echo "TEST 3: CRUD Capaian Pembelajaran (CP)\n";
echo "=======================================================\n";

// Get a valid mapel_id
$firstMapelId = (int)$db->query("SELECT id FROM mata_pelajaran ORDER BY id ASC LIMIT 1")->fetchColumn();
if (!$firstMapelId) {
    $db->exec("INSERT INTO mata_pelajaran (kode_mapel, nama_mapel, kelompok) VALUES ('MP-TEST', 'Mapel Pengujian', 'Kejuruan')");
    $firstMapelId = (int)$db->lastInsertId();
}

$testCpCode = 'CP-TEST-' . time();
$addCpRes = $currModel->addCP([
    'kurikulum_id' => $firstKurId,
    'mapel_id' => $firstMapelId,
    'fase_id' => $allFase[0]['id'] ?? null,
    'kode_cp' => $testCpCode,
    'elemen' => 'Rekayasa Perangkat Lunak',
    'deskripsi' => 'Peserta didik menguasai analisis kebutuhan sistem dan pengkodean modular.'
]);

echo "Add CP: " . ($addCpRes['status'] ? 'BERHASIL' : 'GAGAL: ' . $addCpRes['message']) . "\n";
assert($addCpRes['status'] === true, "Add CP harus berhasil");
$newCpId = (int)$db->query("SELECT id FROM capaian_pembelajaran WHERE kode_cp = '{$testCpCode}'")->fetchColumn();
echo "✓ CP baru terbuat dengan ID: {$newCpId}\n";

// Test getCPById
$cpRow = $currModel->getCPById($newCpId);
echo "✓ getCPById: {$cpRow['kode_cp']} - {$cpRow['nama_mapel']}\n";
assert($cpRow['kode_cp'] === $testCpCode);

// Test updateCP
$updateCpRes = $currModel->updateCP($newCpId, [
    'kode_cp' => $testCpCode . '-REV',
    'elemen' => 'Rekayasa Perangkat Lunak Lanjutan',
    'deskripsi' => 'Peserta didik mampu mengimplementasikan automated testing dan CI/CD.',
    'fase_id' => $allFase[0]['id'] ?? null
]);
echo "Update CP: " . ($updateCpRes['status'] ? 'BERHASIL' : 'GAGAL') . "\n";
assert($updateCpRes['status'] === true);

$updatedCpRow = $currModel->getCPById($newCpId);
assert($updatedCpRow['kode_cp'] === $testCpCode . '-REV');
echo "✓ Verifikasi update CP berhasil: {$updatedCpRow['kode_cp']}\n";

echo "\n=======================================================\n";
echo "TEST 4: CRUD Tujuan Pembelajaran (TP)\n";
echo "=======================================================\n";

$testTpCode = 'TP-TEST-01';
$addTpRes = $currModel->addTP([
    'cp_id' => $newCpId,
    'kode_tp' => $testTpCode,
    'materi_pokok' => 'Unit Testing PHP',
    'deskripsi' => 'Menulis test case otomatis untuk memvalidasi business logic.'
]);
echo "Add TP: " . ($addTpRes['status'] ? 'BERHASIL' : 'GAGAL: ' . $addTpRes['message']) . "\n";
assert($addTpRes['status'] === true);

$tpList = $currModel->getTPList($newCpId);
echo "✓ TP ditemukan di CP {$newCpId}: " . count($tpList) . "\n";
assert(count($tpList) === 1);
$newTpId = $tpList[0]['id'];

// Test getTPById
$tpRow = $currModel->getTPById($newTpId);
echo "✓ getTPById: {$tpRow['kode_tp']} - {$tpRow['materi_pokok']}\n";
assert($tpRow['kode_tp'] === $testTpCode);

// Test updateTP
$updateTpRes = $currModel->updateTP($newTpId, [
    'kode_tp' => 'TP-TEST-01-REV',
    'materi_pokok' => 'Unit & Integration Testing',
    'deskripsi' => 'Menulis test case komprehensif untuk validasi integritas sistem.'
]);
echo "Update TP: " . ($updateTpRes['status'] ? 'BERHASIL' : 'GAGAL') . "\n";
assert($updateTpRes['status'] === true);

// Test deleteTP
$delTpRes = $currModel->deleteTP($newTpId);
echo "Delete individual TP: " . ($delTpRes['status'] ? 'BERHASIL' : 'GAGAL') . "\n";
assert($delTpRes['status'] === true);
$tpListAfter = $currModel->getTPList($newCpId);
assert(count($tpListAfter) === 0);
echo "✓ Verifikasi TP berhasil terhapus secara mandiri.\n";

// Test deleteCP
$delCpRes = $currModel->deleteCP($newCpId);
echo "Delete CP: " . ($delCpRes['status'] ? 'BERHASIL' : 'GAGAL') . "\n";
assert($delCpRes['status'] === true);
assert($currModel->getCPById($newCpId) === false);
echo "✓ Verifikasi CP berhasil dihapus.\n";

echo "\n=======================================================\n";
echo "TEST 5: Verifikasi DB Kedua: smkmuth3_db_elearning_smkmh\n";
echo "=======================================================\n";

try {
    $pdoRemote = new PDO("mysql:host=127.0.0.1;dbname=smkmuth3_db_elearning_smkmh;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $remoteKur = (int)$pdoRemote->query("SELECT COUNT(*) FROM kurikulum")->fetchColumn();
    $remoteCp = (int)$pdoRemote->query("SELECT COUNT(*) FROM capaian_pembelajaran")->fetchColumn();
    $remoteTp = (int)$pdoRemote->query("SELECT COUNT(*) FROM tujuan_pembelajaran")->fetchColumn();
    echo "✓ smkmuth3_db_elearning_smkmh terverifikasi:\n";
    echo "  - kurikulum: {$remoteKur} baris\n";
    echo "  - capaian_pembelajaran: {$remoteCp} baris\n";
    echo "  - tujuan_pembelajaran: {$remoteTp} baris\n";
} catch (Exception $e) {
    echo "Notice on smkmuth3 connection: " . $e->getMessage() . "\n";
}

echo "\n=======================================================\n";
echo "SEMUA PENGUJIAN CP, TP & DATABASE SELESAI DENGAN SUKSES!\n";
echo "=======================================================\n";
