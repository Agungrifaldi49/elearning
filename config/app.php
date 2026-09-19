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

// Global Exception Handler to prevent raw HTTP 500 error screens and LiteSpeed error overrides
set_exception_handler(function($exception) {
    error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine() . "\n" . $exception->getTraceAsString());
    
    $isJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    if ($isJson || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(200);
        }
        echo json_encode([
            'status' => false,
            'message' => 'Terjadi kendala pada sistem: ' . $exception->getMessage()
        ]);
        exit();
    }

    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(200); // Send 200 so LiteSpeed does NOT swallow the error page with generic HTTP 500
    }
    $detailMsg = htmlspecialchars($exception->getMessage());
    $fileInfo = htmlspecialchars(basename($exception->getFile()) . ':' . $exception->getLine());
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Pemberitahuan Sistem</title><meta name='viewport' content='width=device-width, initial-scale=1'></head><body style='background:#f8fafc; margin:0; padding:20px;'>
    <div style='font-family:sans-serif; padding:30px; max-width:640px; margin:40px auto; background:#fff3cd; color:#856404; border:1px solid #ffeeba; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.08); text-align:center;'>
        <h2 style='margin-top:0; color:#856404;'>Permintaan Sedang Diproses / Kendala Sementara</h2>
        <p style='color:#666;'>Sistem sedang melayani lalu lintas yang padat atau terdapat penyesuaian layanan.</p>
        <div style='background:#fff; border:1px solid #f5c6cb; padding:12px; border-radius:8px; margin:18px 0; text-align:left; font-size:13px; color:#721c24; word-break:break-all;'>
            <strong>Detail Pesan:</strong> {$detailMsg} ({$fileInfo})
        </div>
        <div style='display:flex; justify-content:center; gap:10px;'>
            <button onclick='window.location.reload()' style='background:#4f46e5; color:white; border:none; padding:10px 20px; border-radius:6px; font-weight:600; cursor:pointer;'>Muat Ulang Halaman</button>
            <a href='javascript:history.back()' style='background:#6b7280; color:white; text-decoration:none; padding:10px 20px; border-radius:6px; font-weight:600; display:inline-block;'>Kembali</a>
        </div>
    </div></body></html>";
    exit();
});

// Catch fatal shutdown errors (E_ERROR, E_PARSE, etc.)
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log("Fatal Error: " . $error['message'] . " in " . $error['file'] . ":" . $error['line']);
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
            http_response_code(200);
        }
        $detailMsg = htmlspecialchars($error['message'] . ' on line ' . $error['line'] . ' in ' . basename($error['file']));
        echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Pemberitahuan Sistem</title><meta name='viewport' content='width=device-width, initial-scale=1'></head><body style='background:#f8fafc; margin:0; padding:20px;'>
        <div style='font-family:sans-serif; padding:30px; max-width:640px; margin:40px auto; background:#fff3cd; color:#856404; border:1px solid #ffeeba; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.08); text-align:center;'>
            <h2 style='margin-top:0; color:#856404;'>Permintaan Sedang Diproses / Kendala Sementara</h2>
            <p style='color:#666;'>Terjadi penyesuaian teknis pada sistem.</p>
            <div style='background:#fff; border:1px solid #f5c6cb; padding:12px; border-radius:8px; margin:18px 0; text-align:left; font-size:13px; color:#721c24; word-break:break-all;'>
                <strong>Detail Pesan:</strong> {$detailMsg}
            </div>
            <div style='display:flex; justify-content:center; gap:10px;'>
                <button onclick='window.location.reload()' style='background:#4f46e5; color:white; border:none; padding:10px 20px; border-radius:6px; font-weight:600; cursor:pointer;'>Muat Ulang Halaman</button>
                <a href='javascript:history.back()' style='background:#6b7280; color:white; text-decoration:none; padding:10px 20px; border-radius:6px; font-weight:600; display:inline-block;'>Kembali</a>
            </div>
        </div></body></html>";
        exit();
    }
});

// Session Security & Configuration (Extended 8 hours lifetime for exams & LMS activity)
ini_set('session.gc_maxlifetime', '28800'); // 8 hours server session lifetime
ini_set('session.cookie_lifetime', '28800'); // 8 hours cookie lifetime
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
define('SESSION_TIMEOUT', 28800); // 8 hours inactivity timeout (prevents logout during long exams)
define('MAX_LOGIN_ATTEMPTS', 5); // Lock temporarily after 5 fails
define('LOGIN_LOCKOUT_TIME', 300); // 5 minutes lockout

// Firebase Cloud Messaging (FCM) HTTP v1 API Configuration
define('FCM_CREDENTIALS_PATH', __DIR__ . '/firebase_credentials.json');
define('FCM_PROJECT_ID', 'elearning-ff3d0');

// Universal Class Autoloader (Models, Helpers, Controllers)
spl_autoload_register(function ($class) {
    // Sanitize class name to prevent directory traversal
    $cleanClass = str_replace(['..', '/', '\\'], '', $class);
    $searchDirectories = [
        ROOT_PATH . 'models/',
        ROOT_PATH . 'helpers/',
        ROOT_PATH . 'controllers/'
    ];
    foreach ($searchDirectories as $dir) {
        $file = $dir . $cleanClass . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});



