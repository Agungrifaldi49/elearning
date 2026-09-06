<?php
/**
 * E-Learning SMK Muthia Harapan Cicalengka
 * Main Front Controller & Router
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/AuthHelper.php';
require_once __DIR__ . '/helpers/Security.php';
require_once __DIR__ . '/helpers/FlashHelper.php';

// Global Automatic Database Backup Trigger (Admin, Guru, Siswa, Kepsek Activity)
if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($_SESSION['user_id'])) {
    try {
        require_once ROOT_PATH . 'models/ReportModel.php';
        $globalReportModel = new ReportModel();
        $globalReportModel->triggerAutoBackupIfNeeded('user_activity');
    } catch (Throwable $eAutoBackup) {}
}

// Route parser
$url = $_GET['url'] ?? 'landing';
$url = rtrim($url, '/');
$parts = explode('/', $url);

$routeGroup = strtolower($parts[0] ?? 'landing');
$action = strtolower($parts[1] ?? 'index');

// Dispatcher logic
switch ($routeGroup) {
    case 'landing':
    case '':
        require_once ROOT_PATH . 'controllers/LandingController.php';
        $controller = new LandingController();
        $controller->index();
        break;

    case 'admin':
        require_once ROOT_PATH . 'controllers/AdminController.php';
        $controller = new AdminController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->dashboard();
        }
        break;

    case 'guru':
        require_once ROOT_PATH . 'controllers/GuruController.php';
        $controller = new GuruController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->dashboard();
        }
        break;

    case 'siswa':
        require_once ROOT_PATH . 'controllers/SiswaController.php';
        $controller = new SiswaController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->dashboard();
        }
        break;

    case 'kepsek':
        require_once ROOT_PATH . 'controllers/KepsekController.php';
        $controller = new KepsekController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->dashboard();
        }
        break;

    case 'library':
        require_once ROOT_PATH . 'controllers/LibraryController.php';
        $controller = new LibraryController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'chat':
        require_once ROOT_PATH . 'controllers/ChatController.php';
        $controller = new ChatController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'game':
        require_once ROOT_PATH . 'controllers/GameController.php';
        $controller = new GameController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'forum':
        require_once ROOT_PATH . 'controllers/ForumController.php';
        $controller = new ForumController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'auth':
        require_once ROOT_PATH . 'controllers/AuthController.php';
        $controller = new AuthController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->login();
        }
        break;

    case 'api':
        require_once ROOT_PATH . 'controllers/ApiController.php';
        $controller = new ApiController();
        $subAction = strtolower($parts[1] ?? 'index');
        $endpoint = strtolower($parts[2] ?? 'index');
        if (method_exists($controller, $subAction)) {
            $controller->$subAction($endpoint);
        } else {
            $controller->index();
        }
        break;

    default:
        require_once ROOT_PATH . 'controllers/LandingController.php';
        $controller = new LandingController();
        $controller->index();
        break;
}
