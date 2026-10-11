<?php
function testBridge($queryParams = [], $headers = []) {
    $url = 'http://localhost/elearning/bridge_server_pembayaran.php';
    // Since Apache might not be serving, we can simulate directly by setting $_GET and $_SERVER and including, or testing via php CLI server or cURL if apache is up.
}

// Direct execution test with simulated environments
echo "--- TEST 1: Ping (No token required) ---\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = ['action' => 'ping'];
$_POST = [];
$_SERVER['HTTP_AUTHORIZATION'] = '';

ob_start();
include __DIR__ . '/../bridge_server_pembayaran.php';
$output = ob_get_clean();
echo $output . "\n\n";
