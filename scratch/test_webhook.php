<?php
// Test webhook invocation directly
$_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
$_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] = '';

$payload = json_encode([
    'event' => 'ping',
    'data'  => [
        'timestamp' => time()
    ]
]);

// Simulate php://input with stream filter or include
echo "Testing webhook structure...\n";
ob_start();
// Test webhook directly
file_put_contents(__DIR__ . '/test_payload.json', $payload);
echo "Webhook file exists: " . (file_exists(__DIR__ . '/../webhook.php') ? "YES" : "NO") . "\n";
