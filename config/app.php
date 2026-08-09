<?php
/**
 * Application Configuration
 * E-Learning SMK Muthia Harapan Cicalengka
 */

// Set Default Timezone
date_default_timezone_set('Asia/Jakarta');

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
