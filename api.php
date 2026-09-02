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

// Get raw JSON post input if any
$jsonInput = [];
$rawBody = file_get_contents('php://input');
if (!empty($rawBody)) {
    $decoded = json_decode($rawBody, true);
    if (is_array($decoded)) {
        $jsonInput = $decoded;
    }
}

// Parse endpoint action from query parameters, POST body, PATH_INFO, or REQUEST_URI
$rawAction = $_GET['action'] ?? $_GET['url'] ?? $_GET['endpoint'] ?? $_POST['action'] ?? $_POST['endpoint'] ?? $jsonInput['action'] ?? $jsonInput['endpoint'] ?? '';
if (strpos($rawAction, '?') !== false) {
    $partsAction = explode('?', $rawAction, 2);
    $action = $partsAction[0];
    parse_str($partsAction[1], $embeddedParams);
    $_GET = array_merge($embeddedParams, $_GET);
    $_REQUEST = array_merge($embeddedParams, $_REQUEST);
} else {
    $action = $rawAction;
}

if (empty($action) && isset($_SERVER['PATH_INFO'])) {
    $action = trim(explode('?', $_SERVER['PATH_INFO'])[0], '/');
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
$method = strtolower(explode('?', $parts[0] ?? 'index')[0]);
$param = strtolower(explode('?', $parts[1] ?? 'index')[0]);

// Strip any query strings if attached (e.g. scan_qr?user_id=1)
if (strpos($method, '?') !== false) {
    $method = explode('?', $method)[0];
}
if (strpos($param, '?') !== false) {
    $param = explode('?', $param)[0];
}

// If 'api' is passed as the first segment, shift to real method name
if ($method === 'api') {
    $method = $param;
    $param = strtolower($parts[2] ?? 'index');
    if (strpos($param, '?') !== false) {
        $param = explode('?', $param)[0];
    }
}

if (empty($method)) {
    $method = 'index';
}

$controller = new ApiController();

// If method is not a direct controller action, check if it belongs to 'siswa' or 'guru'
if (!method_exists($controller, $method)) {
    $knownSiswaActions = [
        'request_tugas_susulan', 'request_tugas_permission', 'request_tugas_izin', 'request_tugas', 
        'submit_tugas', 'request_susulan', 'ajukan_susulan', 'quiz_detail', 'submit_quiz', 
        'quiz_review', 'record_violation', 'checkin_absensi', 'learning_path', 'alur_belajar'
    ];
    if (in_array($method, $knownSiswaActions) || strpos($method, 'siswa_') === 0 || strpos($method, 'request_') === 0) {
        $param = $method;
        $method = 'siswa';
    } elseif (strpos($method, 'guru_') === 0) {
        $param = $method;
        $method = 'guru';
    }
}

// If method is 'siswa' but param is 'index', attempt fallback to POST/GET sub-action
if ($method === 'siswa' && ($param === 'index' || $param === 'dashboard' || $param === '')) {
    $subActionCandidate = $_POST['action'] ?? $jsonInput['action'] ?? $_GET['sub_action'] ?? $_GET['endpoint'] ?? $_POST['endpoint'] ?? $jsonInput['endpoint'] ?? '';
    if (!empty($subActionCandidate) && strtolower($subActionCandidate) !== 'siswa') {
        $cleanSub = strtolower(trim(explode('?', $subActionCandidate)[0], '/'));
        if (strpos($cleanSub, 'siswa/') === 0) {
            $cleanSub = substr($cleanSub, 6);
        }
        if (!empty($cleanSub) && $cleanSub !== 'siswa') {
            $param = $cleanSub;
        }
    }
}

if (method_exists($controller, $method)) {
    $controller->$method($param);
} else {
    $controller->index();
}

