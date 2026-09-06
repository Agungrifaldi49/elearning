<?php
/**
 * Application Configuration
 * E-Learning SMK Muthia Harapan Cicalengka
 */

// Set Default Timezone
date_default_timezone_set('Asia/Jakarta');

// Error Reporting Config (Prevent raw warnings from breaking headers / HTTP 500)
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Global Exception Handler to prevent raw HTTP 500 error screens
set_exception_handler(function($exception) {
    error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine());
    if (headers_sent() === false) {
        http_response_code(500);
    }
    $isJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    if ($isJson || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => false,
            'message' => 'Terjadi kendala pada sistem: ' . $exception->getMessage()
        ]);
        exit();
    }
    $detailMsg = htmlspecialchars($exception->getMessage());
    echo "<div style='font-family:sans-serif; padding:30px; max-width:640px; margin:50px auto; background:#fff3cd; color:#856404; border:1px solid #ffeeba; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.05); text-align:center;'>
        <h2 style='margin-top:0; color:#856404;'>Permintaan Sedang Diproses / Kendala Sementara</h2>
        <p>Sistem sedang melayani lalu lintas yang padat atau terdapat penyesuaian layanan.</p>
        <div style='background:#fff; border:1px solid #f5c6cb; padding:10px; border-radius:6px; margin:15px 0; text-align:left; font-size:13px; color:#721c24; word-break:break-all;'>
            <strong>Detail Pesan:</strong> {$detailMsg}
        </div>
        <button onclick='window.location.reload()' style='background:#4f46e5; color:white; border:none; padding:10px 20px; border-radius:6px; font-weight:600; cursor:pointer; margin-top:10px;'>Muat Ulang Halaman</button>
    </div>";
    exit();
});

// Session Security & Configuration
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// App Information
define('APP_NAME', 'E-Learning SMK Muthia Harapan Cicalengka');
define('APP_SHORT_NAME', 'E-Learning SMKMH');
define('APP_VERSION', '1.0.0');

// Base URL Auto Detection
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base_url = rtrim($protocol . "://" . $host . $script_name, '/') . '/';

// Normalize BASE_URL for subfolders or root
define('BASE_URL', $base_url);

// Path Configurations
define('ROOT_PATH', dirname(__DIR__) . '/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('UPLOADS_PATH', ASSETS_PATH . 'uploads/');

// Session & Security Settings
define('SESSION_TIMEOUT', 1800); // 30 minutes inactivity timeout
define('MAX_LOGIN_ATTEMPTS', 5); // Lock temporarily after 5 fails
define('LOGIN_LOCKOUT_TIME', 300); // 5 minutes lockout

// Firebase Cloud Messaging (FCM) HTTP v1 API Configuration
define('FCM_CREDENTIALS_PATH', __DIR__ . '/firebase_credentials.json');
define('FCM_PROJECT_ID', 'elearning-ff3d0');


