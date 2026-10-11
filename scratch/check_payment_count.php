<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
$db = Database::getConnection();
$tagihanCount = $db->query("SELECT COUNT(*) FROM pembayaran_tagihan")->fetchColumn();
$riwayatCount = $db->query("SELECT COUNT(*) FROM pembayaran_riwayat")->fetchColumn();
echo "Tagihan count: {$tagihanCount}, Riwayat count: {$riwayatCount}\n";
