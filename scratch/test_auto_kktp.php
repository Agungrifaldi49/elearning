<?php
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'models/CurriculumModel.php';
require_once ROOT_PATH . 'models/AssessmentModel.php';

$assessModel = new AssessmentModel();
$currModel = new CurriculumModel();

echo "=== TEST: AUTO DERIVATION KKTP DARI CP & TP ===\n\n";

// Test 1: Numbered list TP description
$descNumbered = "1. Memahami konsep class dan object dalam OOP\n2. Mengimplementasikan encapsulation dan access modifiers\n3. Merancang struktur pewarisan (inheritance) dan polymorphism";
$indsNumbered = $assessModel->parseIndicatorsFromTpDescription($descNumbered, 'Pemrograman Berorientasi Objek', 'Rekayasa Perangkat Lunak');

echo "Test 1 (Numbered List TP):\n";
echo "- Total indicators parsed: " . count($indsNumbered) . "\n";
foreach ($indsNumbered as $i) {
    echo "  * {$i['nama_indikator']} => {$i['deskripsi_kriteria']}\n";
}
assert(count($indsNumbered) === 3, "Harus menghasilkan 3 indikator");
echo "  [PASS]\n\n";

// Test 2: Bullet list TP description
$descBullet = "- Menjelaskan siklus air dan dampaknya bagi bumi\n- Mengidentifikasi tahapan evaporasi, kondensasi, dan presipitasi\n- Menganalisis peran hutan dalam menjaga keseimbangan air tanah";
$indsBullet = $assessModel->parseIndicatorsFromTpDescription($descBullet, 'Siklus Hidrologi', 'Ilmu Pengetahuan Alam');

echo "Test 2 (Bullet List TP):\n";
echo "- Total indicators parsed: " . count($indsBullet) . "\n";
foreach ($indsBullet as $i) {
    echo "  * {$i['nama_indikator']} => {$i['deskripsi_kriteria']}\n";
}
assert(count($indsBullet) === 3, "Harus menghasilkan 3 indikator");
echo "  [PASS]\n\n";

// Test 3: Paragraph TP description without list format
$descParagraph = "Peserta didik mampu merancang dan membuat antarmuka web responsif berbasis CSS Grid dan Flexbox sesuai standar industri.";
$indsParagraph = $assessModel->parseIndicatorsFromTpDescription($descParagraph, 'Desain Web Responsif', 'Pemrograman Web');

echo "Test 3 (Paragraph TP):\n";
echo "- Total indicators derived: " . count($indsParagraph) . "\n";
foreach ($indsParagraph as $i) {
    echo "  * {$i['nama_indikator']} => {$i['deskripsi_kriteria']}\n";
}
assert(count($indsParagraph) >= 2, "Harus menghasilkan minimal 2 indikator bertahap");
echo "  [PASS]\n\n";

// Test 4: End-to-end addTP with auto-seed KKTP in database
$db = Database::getConnection();
$stmtCp = $db->query("SELECT id, kode_cp FROM capaian_pembelajaran LIMIT 1");
$cp = $stmtCp->fetch(PDO::FETCH_ASSOC);

if ($cp) {
    echo "Test 4 (End-to-End TP Creation with Auto-Seed KKTP in DB):\n";
    $testTpCode = 'TP-AUTO-' . time();
    $tpRes = $currModel->addTP([
        'cp_id' => $cp['id'],
        'kode_tp' => $testTpCode,
        'materi_pokok' => 'Routing dan Controller MVC',
        'deskripsi' => "1. Memahami alur request-response pada arsitektur MVC\n2. Membuat route dinamis dan controller penangan\n3. Mengirimkan data dari controller ke view"
    ]);

    assert($tpRes['status'] === true, "TP harus berhasil dibuat");
    $newTpId = $tpRes['id'];
    echo "- TP Created ID: {$newTpId} (Code: {$testTpCode})\n";

    // Check KKTP in DB
    $kktp = $assessModel->getKktpByTp($newTpId);
    echo "- KKTP Metod: {$kktp['metode']}\n";
    echo "- KKTP Min: {$kktp['nilai_minimum']}\n";
    echo "- KKTP Deskripsi: {$kktp['deskripsi_kriteria']}\n";
    echo "- KKTP Indikator Count: " . count($kktp['indikator']) . "\n";
    foreach ($kktp['indikator'] as $ind) {
        echo "  * [ID: {$ind['id']}] {$ind['nama_indikator']} (Bobot: {$ind['bobot']})\n";
    }

    assert(count($kktp['indikator']) === 3, "Database harus memiliki 3 indikator otomatis");
    echo "  [PASS] Auto-seed KKTP & Indikator berhasil disimpan di database!\n\n";

    // Clean up test data
    $db->prepare("DELETE FROM kktp_indikator WHERE kktp_id IN (SELECT id FROM kktp WHERE tp_id = ?)")->execute([$newTpId]);
    $db->prepare("DELETE FROM kktp WHERE tp_id = ?")->execute([$newTpId]);
    $db->prepare("DELETE FROM tujuan_pembelajaran WHERE id = ?")->execute([$newTpId]);
    echo "Cleanup complete.\n";
}

echo "\n=== ALL AUTO-KKTP TESTS PASSED SUCCESSFULLY! ===\n";
