<?php
/**
 * REST API Entry Point for Mobile Application (elearning_mobile)
 * E-Learning SMK Muthia Harapan Cicalengka
 */

// Buffer any potential PHP notices/warnings to ensure clean JSON response
ob_start();

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/AuthHelper.php';
require_once __DIR__ . '/helpers/Security.php';
require_once __DIR__ . '/helpers/FlashHelper.php';
require_once __DIR__ . '/controllers/ApiController.php';

// Clear output buffer before controller execution
if (ob_get_length()) ob_clean();

// Parse endpoint action from query parameters, PATH_INFO, or REQUEST_URI
$action = $_GET['action'] ?? $_GET['url'] ?? $_GET['endpoint'] ?? '';

if (empty($action) && isset($_SERVER['PATH_INFO'])) {
    $action = trim($_SERVER['PATH_INFO'], '/');
}

if (empty($action)) {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (strpos($requestUri, 'api.php') !== false) {
        $parts = explode('api.php', $requestUri);
        $action = trim(end($parts), '/');
    } elseif (strpos($requestUri, '/api/') !== false) {
        $parts = explode('/api/', $requestUri);
        $action = trim(end($parts), '/');
    }
}

$parts = explode('/', trim($action, '/'));
$method = strtolower($parts[0] ?? 'index');
$param = strtolower($parts[1] ?? 'index');

// If 'api' is passed as the first segment, shift to real method name
if ($method === 'api') {
    $method = $param;
    $param = strtolower($parts[2] ?? 'index');
}

if (empty($method)) {
    $method = 'index';
}

$controller = new ApiController();

if (method_exists($controller, $method)) {
    $controller->$method($param);
} else {
    $controller->index();
}
