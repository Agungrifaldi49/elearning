<?php
/**
 * Test index.php routing to guru/cptp
 */
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/elearning/index.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_GET['url'] = 'guru/cptp';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';

AuthHelper::login([
    'id' => 2,
    'username' => 'guru1',
    'full_name' => 'Guru Simulasi',
    'email' => 'guru@example.com',
    'role_id' => 2,
    'role_name' => 'Guru'
]);

ob_start();
try {
    require __DIR__ . '/../index.php';
    $res = ob_get_clean();
    if (strpos($res, 'Penyusunan Capaian & Tujuan Pembelajaran') !== false) {
        echo "HTTP DISPATCH SUCCESS: index.php?url=guru/cptp returned full view correctly!\n";
    } else {
        echo "WARNING: Content rendered without expected title. Bytes: " . strlen($res) . "\n";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo "HTTP DISPATCH FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
