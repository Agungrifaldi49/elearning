<?php
if (!defined('ROOT_PATH')) define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/app.php';
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'helpers/AuthHelper.php';
require_once ROOT_PATH . 'helpers/Security.php';
require_once ROOT_PATH . 'helpers/FlashHelper.php';
require_once ROOT_PATH . 'models/BaseModel.php';
require_once ROOT_PATH . 'models/GuruModel.php';
require_once ROOT_PATH . 'models/LearningModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';
require_once ROOT_PATH . 'models/CommunicationModel.php';
require_once ROOT_PATH . 'controllers/GuruController.php';

$db = Database::getConnection();
$stmt = $db->query("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'Guru' LIMIT 1");
$user = $stmt->fetch();

if (!$user) {
    die("No guru user found in DB");
}

$_SESSION['user'] = $user;

try {
    $controller = new GuruController();
    echo "Executing liveClass()...\n";
    ob_start();
    $controller->liveClass();
    $output = ob_get_clean();
    echo "Success! Output length: " . strlen($output) . " bytes.\n";
    echo "Output snippet:\n" . substr($output, 0, 500) . "\n";
} catch (Throwable $e) {
    if (ob_get_level() > 0) ob_end_clean();
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
