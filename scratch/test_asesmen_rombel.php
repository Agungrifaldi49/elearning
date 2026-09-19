<?php
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'models/AcademicModel.php';
require_once ROOT_PATH . 'models/AssessmentModel.php';

$academicModel = new AcademicModel();
$assessModel = new AssessmentModel();

echo "=== TEST: ROMBEL KELAS IN ASESMEN (RPL & TBSM) ===\n\n";

$rombelList = $academicModel->getKelas();
echo "Total Rombel in Database: " . count($rombelList) . "\n";

$rplCount = 0;
$tbsmCount = 0;

foreach ($rombelList as $r) {
    $nama = $r['nama_kelas'];
    $rombel = $r['nama_rombel'];
    $kode = $r['kode_jurusan'];
    $tingkat = $r['tingkat'];
    echo "  - ID: {$r['id']} | Kelas: '{$nama}' | Rombel: '{$rombel}' | Tingkat: '{$tingkat}' | Jurusan: '{$kode}'\n";
    
    assert(!empty($nama), "nama_kelas tidak boleh kosong");
    assert(!empty($rombel), "nama_rombel tidak boleh kosong");
    
    if ($kode === 'RPL') $rplCount++;
    if ($kode === 'TBSM') $tbsmCount++;
}

echo "\nSummary Counts:\n";
echo "- RPL Classes: {$rplCount}\n";
echo "- TBSM Classes: {$tbsmCount}\n";

assert($rplCount >= 3, "Harus ada minimal 3 kelas RPL");
assert($tbsmCount >= 3, "Harus ada minimal 3 kelas TBSM (X TBSM 1, XI TBSM 1, XII TBSM 1)");

// Test Grouping
$rombelByJurusan = [];
foreach ($rombelList as $rb) {
    $jKode = !empty($rb['kode_jurusan']) ? strtoupper(trim($rb['kode_jurusan'])) : 'UMUM';
    $jNama = !empty($rb['nama_jurusan']) ? trim($rb['nama_jurusan']) : 'Umum';
    $groupTitle = "Jurusan {$jNama} ({$jKode})";
    if (!isset($rombelByJurusan[$groupTitle])) {
        $rombelByJurusan[$groupTitle] = [];
    }
    $rombelByJurusan[$groupTitle][] = $rb;
}

echo "\nGrouped Titles in Optgroups:\n";
foreach (array_keys($rombelByJurusan) as $title) {
    echo "  * {$title} (" . count($rombelByJurusan[$title]) . " kelas)\n";
}

function testRenderRombelOptgroupsHtml($rombelByJurusan, $selectedId, $showAllOption = false) {
    $html = '';
    if ($showAllOption) {
        $html .= '<option value="">-- Semua Rombel / Kelas --</option>';
    }
    if (!empty($rombelByJurusan) && is_array($rombelByJurusan)) {
        foreach ($rombelByJurusan as $groupTitle => $classes) {
            $html .= '<optgroup label="' . htmlspecialchars($groupTitle) . '">';
            foreach ($classes as $r) {
                $sel = ($selectedId == $r['id']) ? ' selected' : '';
                $rombelName = !empty($r['nama_kelas']) ? $r['nama_kelas'] : (!empty($r['nama_rombel']) ? $r['nama_rombel'] : 'Kelas ' . $r['tingkat']);
                $jurBadge = !empty($r['kode_jurusan']) ? ' [' . htmlspecialchars($r['kode_jurusan']) . ']' : '';
                $html .= '<option value="' . $r['id'] . '"' . $sel . '>' . htmlspecialchars($rombelName) . ' (Tingkat ' . htmlspecialchars($r['tingkat']) . ')' . $jurBadge . '</option>';
            }
            $html .= '</optgroup>';
        }
    }
    return $html;
}

$html = testRenderRombelOptgroupsHtml($rombelByJurusan, 1, true);
assert(strpos($html, 'Jurusan Rekayasa Perangkat Lunak (RPL)') !== false, "HTML harus mengandung optgroup RPL");
assert(strpos($html, 'Jurusan Teknik Bisnis Sepeda Motor (TBSM)') !== false, "HTML harus mengandung optgroup TBSM");
assert(strpos($html, 'X TBSM 1') !== false, "HTML harus mengandung X TBSM 1");
assert(strpos($html, 'X RPL 1') !== false, "HTML harus mengandung X RPL 1");
echo "\nOptgroups HTML Render: SUCCESS & VERIFIED!\n";

echo "\n=== ALL ROMBEL KELAS TESTS PASSED! ===\n";
