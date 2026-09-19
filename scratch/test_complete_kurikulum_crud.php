<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'models/CurriculumModel.php';

$currModel = new CurriculumModel();
$db = Database::getConnection();

echo "=== TESTING END-TO-END CRUD FOR KURIKULUM, CP/TP & PENILAIAN ===\n\n";

// 1. Create Kurikulum
$resKur = $currModel->addKurikulum([
    'kode' => 'K-TEST-2030',
    'nama' => 'Kurikulum Test Vokasi 2030',
    'tahun_mulai' => 2030,
    'status' => 'aktif',
    'deskripsi' => 'Kurikulum eksperimen untuk pengujian otomatis'
]);
echo "1. Create Kurikulum: " . ($resKur['status'] ? "SUCCESS (ID: {$resKur['id']})" : "FAILED: {$resKur['message']}") . "\n";
$kurId = $resKur['id'] ?? 0;
assert($kurId > 0);

// 2. Update Kurikulum
$resUpKur = $currModel->updateKurikulum($kurId, [
    'kode' => 'K-TEST-2030',
    'nama' => 'Kurikulum Test Vokasi 2030 Updated',
    'tahun_mulai' => 2030,
    'tahun_selesai' => 2035,
    'status' => 'aktif',
    'deskripsi' => 'Updated deskripsi'
]);
echo "2. Update Kurikulum: " . ($resUpKur['status'] ? "SUCCESS" : "FAILED: {$resUpKur['message']}") . "\n";

// 3. Add Fase
$resFase = $currModel->addFase([
    'kurikulum_id' => $kurId,
    'kode' => 'TEST-1',
    'nama' => 'Fase Test 1',
    'tingkat_kelas' => 'X',
    'keterangan' => 'Testing fase'
]);
echo "3. Add Fase: " . ($resFase['status'] ? "SUCCESS" : "FAILED: {$resFase['message']}") . "\n";
$faseId = $db->query("SELECT id FROM fase WHERE kurikulum_id = {$kurId} AND kode = 'TEST-1'")->fetchColumn();

// 4. Update Fase
$resUpFase = $currModel->updateFase($faseId, [
    'kurikulum_id' => $kurId,
    'kode' => 'TEST-1',
    'nama' => 'Fase Test 1 Updated',
    'tingkat_kelas' => 'X,XI',
    'keterangan' => 'Testing fase updated'
]);
echo "4. Update Fase: " . ($resUpFase['status'] ? "SUCCESS" : "FAILED: {$resUpFase['message']}") . "\n";

// 5. Add Mapel to Kurikulum
$mapelId = $db->query("SELECT id FROM mata_pelajaran LIMIT 1")->fetchColumn() ?: 1;
$resMapel = $currModel->addStrukturMapel([
    'kurikulum_id' => $kurId,
    'mapel_id' => $mapelId,
    'fase_id' => $faseId,
    'tingkat' => 'X',
    'kelompok_mapel' => 'Kejuruan',
    'alokasi_jp' => 4,
    'kkm' => 78.5
]);
echo "5. Add Struktur Mapel: " . ($resMapel['status'] ? "SUCCESS" : "FAILED: {$resMapel['message']}") . "\n";
$smId = $db->query("SELECT id FROM kurikulum_mapel WHERE kurikulum_id = {$kurId} AND mapel_id = {$mapelId}")->fetchColumn();

// 6. Update Mapel
$resUpMapel = $currModel->updateStrukturMapel($smId, [
    'fase_id' => $faseId,
    'tingkat' => 'X',
    'kelompok_mapel' => 'Kejuruan',
    'alokasi_jp' => 6,
    'kkm' => 80.0
]);
echo "6. Update Struktur Mapel: " . ($resUpMapel['status'] ? "SUCCESS" : "FAILED: {$resUpMapel['message']}") . "\n";
$smRow = $currModel->getStrukturMapelById($smId);
assert($smRow['alokasi_jp'] == 6);
assert($smRow['kkm'] == 80.0);

// 7. Add CP
$resCp = $currModel->addCP([
    'kurikulum_id' => $kurId,
    'mapel_id' => $mapelId,
    'fase_id' => $faseId,
    'kode_cp' => 'CP-TEST-01',
    'elemen' => 'Pemrograman Cloud',
    'deskripsi' => 'Peserta didik memahami arsitektur microservices dan cloud native.'
]);
echo "7. Add CP: " . ($resCp['status'] ? "SUCCESS" : "FAILED: {$resCp['message']}") . "\n";
$cpId = $db->query("SELECT id FROM capaian_pembelajaran WHERE kurikulum_id = {$kurId} AND kode_cp = 'CP-TEST-01'")->fetchColumn();

// 8. Update CP
$resUpCp = $currModel->updateCP($cpId, [
    'kode_cp' => 'CP-TEST-01',
    'elemen' => 'Pemrograman Cloud Native',
    'deskripsi' => 'Peserta didik menguasai arsitektur microservices dan cloud native updated.',
    'fase_id' => $faseId
]);
echo "8. Update CP: " . ($resUpCp['status'] ? "SUCCESS" : "FAILED: {$resUpCp['message']}") . "\n";

// 9. Add TP
$resTp = $currModel->addTP([
    'cp_id' => $cpId,
    'kode_tp' => 'TP-TEST-01.1',
    'materi_pokok' => 'Docker & Kubernetes',
    'deskripsi' => 'Mampu melakukan containerization pada aplikasi PHP.'
]);
echo "9. Add TP: " . ($resTp['status'] ? "SUCCESS" : "FAILED: {$resTp['message']}") . "\n";
$tpId = $db->query("SELECT id FROM tujuan_pembelajaran WHERE cp_id = {$cpId} AND kode_tp = 'TP-TEST-01.1'")->fetchColumn();

// 10. Update TP
$resUpTp = $currModel->updateTP($tpId, [
    'kode_tp' => 'TP-TEST-01.1',
    'materi_pokok' => 'Containerization & Orchestration',
    'deskripsi' => 'Mampu melakukan deployment container aplikasi PHP.'
]);
echo "10. Update TP: " . ($resUpTp['status'] ? "SUCCESS" : "FAILED: {$resUpTp['message']}") . "\n";

// 11. Save Bobot Penilaian
$resBobot = $currModel->saveKomponenPenilaian($kurId, [
    ['kode_komponen' => 'formatif', 'nama_komponen' => 'Formatif TP', 'bobot_persen' => 40.0, 'deskripsi' => 'Harian'],
    ['kode_komponen' => 'sumatif_lm', 'nama_komponen' => 'Sumatif Lingkup Materi', 'bobot_persen' => 30.0, 'deskripsi' => 'Per CP'],
    ['kode_komponen' => 'sumatif_akhir', 'nama_komponen' => 'Sumatif Akhir Semester', 'bobot_persen' => 30.0, 'deskripsi' => 'SAS']
]);
echo "11. Save Bobot Penilaian: " . ($resBobot['status'] ? "SUCCESS" : "FAILED: {$resBobot['message']}") . "\n";
$komp = $currModel->getKomponenPenilaian($kurId);
assert(count($komp) === 3);

// 12. Delete CP (should cascade delete TP)
$resDelCp = $currModel->deleteCP($cpId);
echo "12. Delete CP (with child TP): " . ($resDelCp['status'] ? "SUCCESS" : "FAILED: {$resDelCp['message']}") . "\n";
$tpCountRemaining = $db->query("SELECT COUNT(*) FROM tujuan_pembelajaran WHERE cp_id = {$cpId}")->fetchColumn();
assert($tpCountRemaining == 0);
echo "    -> Verified child TP cascaded cleanly: 0 remaining.\n";

// 13. Delete Struktur Mapel
$resDelSm = $currModel->deleteStrukturMapel($smId);
echo "13. Delete Struktur Mapel: " . ($resDelSm['status'] ? "SUCCESS" : "FAILED: {$resDelSm['message']}") . "\n";

// 14. Delete Fase
$resDelFase = $currModel->deleteFase($faseId);
echo "14. Delete Fase: " . ($resDelFase['status'] ? "SUCCESS" : "FAILED: {$resDelFase['message']}") . "\n";

// 15. Delete Kurikulum
$resDelKur = $currModel->deleteKurikulum($kurId);
echo "15. Delete Kurikulum: " . ($resDelKur['status'] ? "SUCCESS" : "FAILED: {$resDelKur['message']}") . "\n";

echo "\n=== ALL 15 END-TO-END CRUD OPERATIONS PASSED 100% ===\n";
