<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'models/CurriculumModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';
require_once ROOT_PATH . 'helpers/Security.php';
require_once ROOT_PATH . 'helpers/FlashHelper.php';
require_once ROOT_PATH . 'helpers/AuthHelper.php';

// Mock session and auth
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user'] = ['id' => 1, 'nama' => 'Admin Test', 'role_id' => 1, 'username' => 'admin'];
if (!defined('BASE_URL')) define('BASE_URL', '/');

$currModel = new CurriculumModel();
$academicModel = new AcademicModel();

$activeTab = 'struktur';
$kurikulumList = $currModel->getAllKurikulum();
$allFaseList = $currModel->getAllFase();
$taList = $academicModel->getTahunAjaran();
$kelasList = $academicModel->getKelas();
$mapelList = $academicModel->getMapel();
$jurusanList = $academicModel->getJurusan();
$rombelKurikulumList = $currModel->getRombelKurikulum();

$filterCpKurId = null;
$filterCpMapelId = null;
$filterCpFaseId = null;

$cpList = $currModel->getCPList($filterCpKurId, $filterCpMapelId, $filterCpFaseId);
$allCpForDropdown = $cpList;
$tpList = $currModel->getTPList();

$selectedKurId = (int)($kurikulumList[0]['id'] ?? 1);
$selectedKurikulum = $currModel->getKurikulumById($selectedKurId) ?: ($kurikulumList[0] ?? null);
$strukturMapelList = $currModel->getStrukturMapel($selectedKurId);
$komponenList = $currModel->getKomponenPenilaian($selectedKurId);
$faseKurikulumList = $currModel->getFaseByKurikulum($selectedKurId);

ob_start();
try {
    include ROOT_PATH . 'views/admin/kurikulum.php';
    $rendered = ob_get_clean();
    echo "✓ Render kurikulum.php berhasil! Panjang HTML: " . strlen($rendered) . " bytes\n";
    
    // Check if DataTables_Table_3 issue can occur
    preg_match_all('/<table[^>]*class=[\'"][^\'"]*datatable[^\'"]*[\'"][^>]*>(.*?)<\/table>/is', $rendered, $renderedTables);
    echo "✓ Total DataTables terdeteksi di output HTML: " . count($renderedTables[0]) . "\n";
    
    foreach ($renderedTables[0] as $i => $tbl) {
        preg_match_all('/<th[^>]*>/i', $tbl, $th);
        preg_match('/<tbody[^>]*>(.*?)<\/tbody>/is', $tbl, $tb);
        $tbody = $tb[1] ?? '';
        if (stripos($tbody, 'colspan') !== false) {
            echo "  ⚠️ Table #{$i} STILL has COLSPAN in rendered HTML!\n";
        } else {
            echo "  ✓ Table #{$i} (TH count: " . count($th[0]) . ") valid tanpa colspan di tbody.\n";
        }
    }
} catch (\Throwable $e) {
    ob_end_clean();
    echo "ERROR during render: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}
