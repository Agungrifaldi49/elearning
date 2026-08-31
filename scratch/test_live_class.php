<?php
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/app.php';
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'helpers/AuthHelper.php';
require_once ROOT_PATH . 'helpers/Security.php';
require_once ROOT_PATH . 'helpers/FlashHelper.php';
require_once ROOT_PATH . 'models/BaseModel.php';
require_once ROOT_PATH . 'models/GuruModel.php';
require_once ROOT_PATH . 'models/SiswaModel.php';
require_once ROOT_PATH . 'models/LearningModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';
require_once ROOT_PATH . 'models/CommunicationModel.php';
require_once ROOT_PATH . 'controllers/GuruController.php';
require_once ROOT_PATH . 'controllers/SiswaController.php';

try {
    $db = Database::getConnection();
    echo "DB Connection OK!\n";
    
    // Check live_class table structure
    $stmt = $db->query("SHOW TABLES LIKE 'live_class'");
    $tableExists = $stmt->fetch();
    echo "Table live_class exists: " . ($tableExists ? 'YES' : 'NO') . "\n";

    $learningModel = new LearningModel();
    $learningModel->ensureLiveClassTableExist();
    
    // Check columns
    $stmt = $db->query("DESCRIBE live_class");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(', ', $cols) . "\n";

    // Test Guru liveClass
    $stmt = $db->query("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'Guru' LIMIT 1");
    $guruUser = $stmt->fetch();
    if ($guruUser) {
        $_SESSION['user_id'] = $guruUser['id'];
        $_SESSION['user'] = $guruUser;
        $_SESSION['role_name'] = $guruUser['role_name'];
        $_SESSION['role_id'] = $guruUser['role_id'];
        $controller = new GuruController();
        ob_start();
        $controller->liveClass();
        $out = ob_get_clean();
        echo "Guru liveClass OK! Output len: " . strlen($out) . "\n";
    }

    // Test Siswa liveClass
    $stmt = $db->query("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'Siswa' LIMIT 1");
    $siswaUser = $stmt->fetch();
    if ($siswaUser) {
        $_SESSION['user_id'] = $siswaUser['id'];
        $_SESSION['user'] = $siswaUser;
        $_SESSION['role_name'] = $siswaUser['role_name'];
        $_SESSION['role_id'] = $siswaUser['role_id'];
        $controller = new SiswaController();
        ob_start();
        $controller->liveClass();
        $out = ob_get_clean();
        echo "Siswa liveClass OK! Output len: " . strlen($out) . "\n";
    }

    // Test Admin liveClass
    require_once ROOT_PATH . 'controllers/AdminController.php';
    $stmt = $db->query("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name IN ('Administrator', 'Admin') LIMIT 1");
    $adminUser = $stmt->fetch();
    if ($adminUser) {
        $_SESSION['user_id'] = $adminUser['id'];
        $_SESSION['user'] = $adminUser;
        $_SESSION['role_name'] = $adminUser['role_name'];
        $_SESSION['role_id'] = $adminUser['role_id'];
        $controller = new AdminController();
        ob_start();
        $controller->liveClass();
        $out = ob_get_clean();
        echo "Admin liveClass OK! Output len: " . strlen($out) . "\n";
    }

} catch (Throwable $e) {
    if (ob_get_level() > 0) ob_end_clean();
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

