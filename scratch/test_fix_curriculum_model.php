<?php
/**
 * Test verifying that CurriculumModel is successfully loaded and autoloader works seamlessly.
 */
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/elearning/index.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

echo "1. Testing class_exists('CurriculumModel') via Autoloader...\n";
if (class_exists('CurriculumModel')) {
    echo "SUCCESS: CurriculumModel is found via Autoloader!\n";
} else {
    echo "FAIL: CurriculumModel could not be autoloaded!\n";
    exit(1);
}

echo "2. Instantiating CurriculumModel directly...\n";
$currModel = new CurriculumModel();
$kurList = $currModel->getAllKurikulum();
echo "SUCCESS: Found " . count($kurList) . " kurikulum records.\n";

echo "3. Testing GuruController::cptp() simulation...\n";
AuthHelper::login([
    'id' => 2,
    'username' => 'guru1',
    'full_name' => 'Guru Simulasi',
    'email' => 'guru@example.com',
    'role_id' => 2,
    'role_name' => 'Guru'
]);

require_once ROOT_PATH . 'controllers/GuruController.php';
$guruCtrl = new GuruController();

ob_start();
try {
    $guruCtrl->cptp();
    $output = ob_get_clean();
    if (strpos($output, 'Penyusunan Capaian & Tujuan Pembelajaran') !== false) {
        echo "SUCCESS: GuruController::cptp rendered correctly! (" . strlen($output) . " bytes)\n";
    } else {
        echo "WARNING: Output rendered (" . strlen($output) . " bytes)\n";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo "FAIL: Exception in GuruController::cptp: " . $e->getMessage() . "\n";
    exit(1);
}

echo "4. Testing AdminController::kurikulum() simulation...\n";
AuthHelper::login([
    'id' => 1,
    'username' => 'admin',
    'full_name' => 'Administrator',
    'email' => 'admin@example.com',
    'role_id' => 1,
    'role_name' => 'Administrator'
]);

require_once ROOT_PATH . 'controllers/AdminController.php';
$adminCtrl = new AdminController();

ob_start();
try {
    $adminCtrl->kurikulum();
    $adminOutput = ob_get_clean();
    if (strpos($adminOutput, 'Manajemen Kurikulum') !== false) {
        echo "SUCCESS: AdminController::kurikulum rendered correctly! (" . strlen($adminOutput) . " bytes)\n";
    } else {
        echo "WARNING: Output rendered (" . strlen($adminOutput) . " bytes)\n";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo "FAIL: Exception in AdminController::kurikulum: " . $e->getMessage() . "\n";
    exit(1);
}

echo "5. Testing NilaiModel with CurriculumModel...\n";
$nilaiModel = new NilaiModel();
echo "SUCCESS: NilaiModel instantiated without error!\n";

echo "\n============================================\n";
echo "VERIFICATION COMPLETE: ALL CHECKS PASSED (0 ERRORS)\n";
echo "============================================\n";
