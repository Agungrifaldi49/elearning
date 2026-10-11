<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PembayaranModel.php';

$model = new PembayaranModel();

echo "--- 1. Test Pull with Wrong Secret Token ---\n";
$res1 = $model->pullFromRemoteServer('http://127.0.0.1:8088/bridge_server_pembayaran.php', 'WRONG_SECRET');
print_r($res1);

echo "\n--- 2. Test Pull with Valid Secret Token (Credentials not yet filled) ---\n";
$res2 = $model->pullFromRemoteServer('http://127.0.0.1:8088/bridge_server_pembayaran.php', 'SMKMH_PAYMENT_SECRET_KEY_2026');
print_r($res2);
