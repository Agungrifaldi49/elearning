<?php
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'models/CurriculumModel.php';
require_once ROOT_PATH . 'models/AssessmentModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';

echo "=== TEST SUITE: CP -> TP -> KKTP -> ASESMEN -> NILAI -> STATUS KETERCAPAIAN ===\n\n";

$db = Database::getConnection();
$currModel = new CurriculumModel();
$assessModel = new AssessmentModel();
$academicModel = new AcademicModel();

$testsPassed = 0;
$totalTests = 10;

// Setup Context
$activeTa = $academicModel->getActiveTahunAjaran();
$taId = (int)($activeTa['id'] ?? 1);
$semester = 1;

// Get a valid mapel, kurikulum, rombel, guru, siswa
$kurikulum = $currModel->getAllKurikulum()[0] ?? null;
$mapel = $academicModel->getMapel()[0] ?? null;
$rombel = $academicModel->getKelas()[0] ?? null;

$stmtSiswa = $db->prepare("SELECT s.id, s.nama_lengkap FROM siswa s WHERE s.kelas_id = ? LIMIT 2");
$stmtSiswa->execute([$rombel['id']]);
$siswaList = $stmtSiswa->fetchAll(PDO::FETCH_ASSOC);

if (empty($siswaList)) {
    // Pick any siswa
    $stmtAny = $db->query("SELECT id, nama_lengkap FROM siswa LIMIT 2");
    $siswaList = $stmtAny->fetchAll(PDO::FETCH_ASSOC);
}

$siswa1 = $siswaList[0];
$siswa2 = $siswaList[1] ?? $siswa1;

echo "Context:\n";
echo "- Kurikulum: {$kurikulum['nama']} (ID: {$kurikulum['id']})\n";
echo "- Mapel: {$mapel['nama_mapel']} (ID: {$mapel['id']})\n";
echo "- Rombel/Kelas: {$rombel['nama_kelas']} (ID: {$rombel['id']})\n";
echo "- Siswa 1: {$siswa1['nama_lengkap']} (ID: {$siswa1['id']})\n";
echo "------------------------------------------------------------------\n\n";

// TEST 1: Buat CP baru
echo "TEST 1: Buat Capaian Pembelajaran (CP) baru... ";
$testCpCode = 'CP-TEST-' . time();
$cpRes = $currModel->addCP([
    'kurikulum_id' => $kurikulum['id'],
    'mapel_id' => $mapel['id'],
    'fase_id' => null,
    'guru_id' => 1,
    'kode_cp' => $testCpCode,
    'elemen' => 'Pemrograman Berorientasi Objek',
    'deskripsi' => 'Peserta didik mampu memahami konsep dasar pemrograman berorientasi objek, enkapsulasi, dan pewarisan.'
]);
$stmtCp = $db->prepare("SELECT id FROM capaian_pembelajaran WHERE kode_cp = ?");
$stmtCp->execute([$testCpCode]);
$cpId = (int)$stmtCp->fetchColumn();

if ($cpId > 0) {
    echo "PASS (CP ID: {$cpId}, Kode: {$testCpCode})\n";
    $testsPassed++;
} else {
    echo "FAIL: " . ($cpRes['message'] ?? 'Unknown error') . "\n";
}

// TEST 2: Buat 3 TP untuk CP tersebut
echo "TEST 2: Buat 3 Tujuan Pembelajaran (TP) untuk CP tersebut... ";
$tp1Res = $currModel->addTP([
    'cp_id' => $cpId,
    'guru_id' => 1,
    'kode_tp' => 'TP-OOP-01',
    'materi_pokok' => 'Class & Object',
    'deskripsi' => 'Mampu mendefinisikan class, object, attribute, dan method pada bahasa pemrograman.'
]);
$tp1Id = (int)($tp1Res['id'] ?? 0);

$tp2Res = $currModel->addTP([
    'cp_id' => $cpId,
    'guru_id' => 1,
    'kode_tp' => 'TP-OOP-02',
    'materi_pokok' => 'Enkapsulasi',
    'deskripsi' => 'Mampu menerapkan prinsip enkapsulasi dengan visibility public, protected, private.'
]);
$tp2Id = (int)($tp2Res['id'] ?? 0);

$tp3Res = $currModel->addTP([
    'cp_id' => $cpId,
    'guru_id' => 1,
    'kode_tp' => 'TP-OOP-03',
    'materi_pokok' => 'Pewarisan & Polymorphism',
    'deskripsi' => 'Mampu mengimplementasikan inheritance dan overriding method antar class turunan.'
]);
$tp3Id = (int)($tp3Res['id'] ?? 0);

if ($tp1Id > 0 && $tp2Id > 0 && $tp3Id > 0) {
    echo "PASS (TP1: {$tp1Id}, TP2: {$tp2Id}, TP3: {$tp3Id})\n";
    $testsPassed++;
} else {
    echo "FAIL: Gagal membuat 3 TP\n";
}

// TEST 3: Konfigurasikan KKTP untuk tiap TP (Metode A, B, C)
echo "TEST 3: Konfigurasi KKTP dengan 3 metode berbeda:\n";
// TP 1: Metode A (Interval Nilai min 75.00)
$kktp1Res = $assessModel->saveKktp($tp1Id, [
    'metode' => 'interval_nilai',
    'nilai_minimum' => 75.00,
    'target_indikator_count' => 0,
    'deskripsi_kriteria' => 'Batas minimum interval nilai 75.00'
]);
$kktp1 = $assessModel->getKktpByTp($tp1Id);

// TP 2: Metode B (Rubrik kriteria min 75.00)
$kktp2Res = $assessModel->saveKktp($tp2Id, [
    'metode' => 'rubrik',
    'nilai_minimum' => 75.00,
    'target_indikator_count' => 0,
    'deskripsi_kriteria' => 'Rubrik Enkapsulasi: Mahir (>=85), Cakap (75-84), Berkembang (<75)'
]);
$kktp2 = $assessModel->getKktpByTp($tp2Id);

// TP 3: Metode C (Checklist Indikator target min 3 dari 4)
$kktp3Res = $assessModel->saveKktp($tp3Id, [
    'metode' => 'checklist',
    'nilai_minimum' => 75.00,
    'target_indikator_count' => 3,
    'deskripsi_kriteria' => 'Checklist kompetensi pewarisan',
    'indikator' => [
        ['nama_indikator' => 'Memahami keyword extends', 'deskripsi_kriteria' => 'Sintaks pewarisan benar', 'bobot' => 1.0],
        ['nama_indikator' => 'Memanggil constructor parent (super)', 'deskripsi_kriteria' => 'Parent constructor terpanggil', 'bobot' => 1.0],
        ['nama_indikator' => 'Melakukan method overriding', 'deskripsi_kriteria' => 'Method child menggantikan method parent', 'bobot' => 1.0],
        ['nama_indikator' => 'Mengimplementasikan polymorphism dinamis', 'deskripsi_kriteria' => 'Objek polimorfik berjalan sesuai tipe runtime', 'bobot' => 1.0],
    ]
]);
$kktp3 = $assessModel->getKktpByTp($tp3Id);

$t3Ok = ($kktp1['metode'] === 'interval_nilai' && $kktp2['metode'] === 'rubrik' && $kktp3['metode'] === 'checklist' && count($kktp3['indikator']) === 4);
if ($t3Ok) {
    echo "  - TP1: Metode {$kktp1['metode']} (Min {$kktp1['nilai_minimum']})\n";
    echo "  - TP2: Metode {$kktp2['metode']} (Min {$kktp2['nilai_minimum']})\n";
    echo "  - TP3: Metode {$kktp3['metode']} (Target {$kktp3['target_indikator_count']} dari " . count($kktp3['indikator']) . " indikator)\n";
    echo "  PASS\n";
    $testsPassed++;
} else {
    echo "  FAIL: KKTP configuration did not match specifications.\n";
}

// TEST 4: Buat 1 Asesmen yang mengukur ketiga TP sekaligus (Multi-TP)
echo "TEST 4: Buat 1 Asesmen Multi-TP mengukur TP 1, TP 2, TP 3 sekaligus... ";
$asesmenRes = $assessModel->createAsesmenMultiTp([
    'rombel_id' => $rombel['id'],
    'mapel_id' => $mapel['id'],
    'guru_id' => 1,
    'kurikulum_id' => $kurikulum['id'],
    'tahun_ajaran_id' => $taId,
    'semester' => $semester,
    'nama_asesmen' => 'Projek Akhir Pemrograman OOP (Multi-TP)',
    'jenis_asesmen' => 'projek',
    'tanggal' => date('Y-m-d'),
    'nilai_maksimum' => 100,
    'bobot' => 2.0
], [$tp1Id, $tp2Id, $tp3Id]);

$asesmenId = (int)($asesmenRes['asesmen_id'] ?? 0);
$asesmenDetail = $assessModel->getAsesmenById($asesmenId);

if ($asesmenId > 0 && count($asesmenDetail['tujuan_pembelajaran']) === 3) {
    echo "PASS (Asesmen ID: {$asesmenId}, Total TP: " . count($asesmenDetail['tujuan_pembelajaran']) . ")\n";
    $testsPassed++;
} else {
    echo "FAIL: Gagal membuat Asesmen Multi-TP\n";
}

// TEST 5: Masukkan nilai siswa:
// - TP 1: 80 (KKTP 75) -> Status: Tercapai (1)
// - TP 2: 70 (KKTP 75) -> Status: Belum Tercapai (0)
// - TP 3: Checklist 2 indikator (Target 3) -> Status: Belum Tercapai (0)
echo "TEST 5: Input nilai per-TP & verifikasi status ketercapaian binary (1/0):\n";
$n1 = $assessModel->inputNilaiSiswaPerTp($asesmenId, $tp1Id, $siswa1['id'], 80.00);
$n2 = $assessModel->inputNilaiSiswaPerTp($asesmenId, $tp2Id, $siswa1['id'], 70.00);

// Untuk TP 3 checklist: berikan 2 centang dari 4
$chkIds = array_column($kktp3['indikator'], 'id');
$n3 = $assessModel->inputNilaiSiswaPerTp($asesmenId, $tp3Id, $siswa1['id'], 50.00, false, 'Dua indikator tercapai', [
    'checked_indicators' => [$chkIds[0], $chkIds[1]]
]);

$t5Pass = ($n1['status_code'] === 1 && $n2['status_code'] === 0 && $n3['status_code'] === 0);
echo "  - TP 1 (Skor 80 vs KKTP 75): Status Code = {$n1['status_code']} ({$n1['status_ketercapaian']})\n";
echo "  - TP 2 (Skor 70 vs KKTP 75): Status Code = {$n2['status_code']} ({$n2['status_ketercapaian']})\n";
echo "  - TP 3 (Checklist 2 dari 3): Status Code = {$n3['status_code']} ({$n3['status_ketercapaian']})\n";
if ($t5Pass) {
    echo "  PASS\n";
    $testsPassed++;
} else {
    echo "  FAIL: Status ketercapaian binary tidak sesuai ekspektasi.\n";
}

// TEST 6: Jalankan Remedial untuk TP 2 (70 -> 82)
// Status berubah jadi Tercapai (1), nilai_awal (70) tetap tersimpan utuh di database
echo "TEST 6: Remedial untuk TP 2 (70 -> 82) dengan preservasi nilai_awal... ";
$remRes = $assessModel->inputNilaiSiswaPerTp($asesmenId, $tp2Id, $siswa1['id'], 82.00, true, 'Remedial tes lisan konsep enkapsulasi');

// Verifikasi langsung dari database
$stmtVerify = $db->prepare("SELECT * FROM nilai_asesmen_tp WHERE asesmen_id = ? AND tp_id = ? AND siswa_id = ?");
$stmtVerify->execute([$asesmenId, $tp2Id, $siswa1['id']]);
$rowRem = $stmtVerify->fetch(PDO::FETCH_ASSOC);

$t6Pass = (
    (int)$rowRem['status_code'] === 1 &&
    (float)$rowRem['nilai_asli'] === 82.00 &&
    (float)$rowRem['nilai_awal'] === 70.00 &&
    (int)$rowRem['is_remedial'] === 1
);

if ($t6Pass) {
    echo "PASS (Nilai Baru: {$rowRem['nilai_asli']}, Nilai Awal: {$rowRem['nilai_awal']}, Status: {$rowRem['status_ketercapaian']} ({$rowRem['status_code']}))\n";
    $testsPassed++;
} else {
    echo "FAIL: Remedial tidak mempreservasi nilai awal atau status salah (asli: {$rowRem['nilai_asli']}, awal: {$rowRem['nilai_awal']})\n";
}

// TEST 7: Ubah KKTP untuk periode berikutnya (versi baru & arsip versi lama)
echo "TEST 7: Modifikasi KKTP masa depan (arsip versi lama, asesmen lama tidak terpengaruh)... ";
$oldKktpId = (int)$kktp1['id'];

// Guru menaikkan KKTP TP1 dari 75 ke 85
$saveNewKktp = $assessModel->saveKktp($tp1Id, [
    'metode' => 'interval_nilai',
    'nilai_minimum' => 85.00,
    'target_indikator_count' => 0,
    'deskripsi_kriteria' => 'Batas Ketercapaian Baru 85.00 (Tahun Berikutnya)'
]);

// Cek apakah KKTP lama diarsipkan dan versi baru dibuat
$stmtOldKktp = $db->prepare("SELECT status, versi FROM kktp WHERE id = ?");
$stmtOldKktp->execute([$oldKktpId]);
$oldKktpRow = $stmtOldKktp->fetch(PDO::FETCH_ASSOC);

$newKktp = $assessModel->getKktpByTp($tp1Id);

// Cek apakah rekaman nilai siswa asesmen lama masih merujuk KKTP lama dan statusnya tetap Tercapai (1)
$stmtOldScore = $db->prepare("SELECT * FROM nilai_asesmen_tp WHERE asesmen_id = ? AND tp_id = ? AND siswa_id = ?");
$stmtOldScore->execute([$asesmenId, $tp1Id, $siswa1['id']]);
$oldScoreRow = $stmtOldScore->fetch(PDO::FETCH_ASSOC);

$t7Pass = (
    $oldKktpRow['status'] === 'arsip' &&
    $newKktp['versi'] > 1 &&
    (float)$newKktp['nilai_minimum'] === 85.00 &&
    (int)$oldScoreRow['status_code'] === 1 &&
    (int)$oldScoreRow['kktp_id'] === $oldKktpId
);

if ($t7Pass) {
    echo "PASS (KKTP Lama diarsipkan, KKTP Baru versi {$newKktp['versi']} aktif (Min: {$newKktp['nilai_minimum']}), Nilai lama tetap tuntas)\n";
    $testsPassed++;
} else {
    echo "FAIL: Modifikasi KKTP memengaruhi data lama atau gagal arsip\n";
}

// TEST 8: Rekap Ketercapaian Per Siswa
echo "TEST 8: Rekap Ketercapaian Siswa (menampilkan seluruh TP dengan status 1/0)... ";
$rekapSiswa = $assessModel->getRekapKetercapaianSiswa($siswa1['id'], $mapel['id'], $taId, $semester);

$tp1Summary = null; $tp2Summary = null; $tp3Summary = null;
foreach ($rekapSiswa['tp_summary'] as $sum) {
    if ($sum['tp_id'] === $tp1Id) $tp1Summary = $sum;
    if ($sum['tp_id'] === $tp2Id) $tp2Summary = $sum;
    if ($sum['tp_id'] === $tp3Id) $tp3Summary = $sum;
}

$t8Pass = (
    $tp1Summary !== null && $tp1Summary['status_code'] === 1 &&
    $tp2Summary !== null && $tp2Summary['status_code'] === 1 && $tp2Summary['is_remedial'] === 1 &&
    $tp3Summary !== null && $tp3Summary['status_code'] === 0
);

if ($t8Pass) {
    echo "PASS (TP1: {$tp1Summary['status_code']}, TP2 Remedial: {$tp2Summary['status_code']} (Nilai: {$tp2Summary['nilai_asli']}), TP3: {$tp3Summary['status_code']})\n";
    $testsPassed++;
} else {
    echo "FAIL: Rekap siswa tidak sesuai untuk TP yang diuji\n";
}

// TEST 9: Rekap Ketercapaian Kelas
echo "TEST 9: Rekap Ketercapaian Kelas (analisis per-TP & identifikasi TP < 70% ketercapaian)... ";
$rekapKelas = $assessModel->getRekapKetercapaianKelas($rombel['id'], $mapel['id'], $taId, $semester);

$tp3Recap = null;
foreach ($rekapKelas['tp_list'] as $tpr) {
    if ((int)$tpr['tp_id'] === $tp3Id) {
        $tp3Recap = $tpr;
        break;
    }
}

$t9Pass = (
    !empty($rekapKelas['tp_list']) &&
    $tp3Recap !== null &&
    $tp3Recap['persentase_ketercapaian'] < 70 // Karena TP3 siswa1 belum tercapai
);

if ($t9Pass) {
    echo "PASS (TP3 Ketercapaian: {$tp3Recap['persentase_ketercapaian']}%, Kategori: '{$tp3Recap['kategori_tindak_lanjut']}')\n";
    $testsPassed++;
} else {
    echo "FAIL: Rekap kelas tidak menemukan TP bermasalah\n";
}

// TEST 10: Integrasi E-Rapor (Generator Deskripsi Capaian Kompetensi dari TP 1 dan 0)
echo "TEST 10: Generator Deskripsi Capaian Kompetensi E-Rapor dari Data TP... ";
$raporDesc = $assessModel->generateDeskripsiRaporFromTp($siswa1['id'], $mapel['id'], $taId, $semester);

$hasTercapaiDesc = !empty($raporDesc['deskripsi_tercapai']) && strpos($raporDesc['deskripsi_tercapai'], 'penguasaan') !== false;
$hasPerluDesc = !empty($raporDesc['deskripsi_perlu_bimbingan']) && strpos($raporDesc['deskripsi_perlu_bimbingan'], 'bimbingan') !== false;
$t10Pass = ($hasTercapaiDesc && $hasPerluDesc && $raporDesc['total_tp'] >= 3);

if ($t10Pass) {
    echo "PASS\n";
    echo "  [Draf Tercapai]: \"{$raporDesc['deskripsi_tercapai']}\"\n";
    echo "  [Draf Bimbingan]: \"{$raporDesc['deskripsi_perlu_bimbingan']}\"\n";
    $testsPassed++;
} else {
    echo "FAIL: Generator deskripsi rapor tidak menghasilkan kalimat yang sesuai\n";
}

// Clean up test data safely using soft-archive to test archive mechanisms
echo "\nTesting Soft-Archiving of CP and TP with existing assessments... ";
$delRes = $currModel->deleteCP($cpId);
$stmtCpStatus = $db->prepare("SELECT status FROM capaian_pembelajaran WHERE id = ?");
$stmtCpStatus->execute([$cpId]);
$finalCpStatus = $stmtCpStatus->fetchColumn();

if ($finalCpStatus === 'arsip') {
    echo "PASS (CP dengan riwayat nilai berhasil dilindungi & diarsipkan, bukan dihapus permanen)\n";
} else {
    echo "NOTICE: CP status is '{$finalCpStatus}'\n";
}

echo "\n==================================================================\n";
echo "SUMMARY: {$testsPassed} of {$totalTests} tests PASSED!\n";
echo "==================================================================\n";
