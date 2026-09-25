<?php
/**
 * File: webhook.php
 * E-Learning SMK Muthia Harapan Cicalengka
 * 
 * Webhook Endpoint untuk WhatsApp Gateway
 * Gateway akan memanggil file ini secara instan saat ada pesan masuk atau status pengiriman berubah.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/SettingsModel.php';

// 1. Tangkap Payload JSON dari Gateway
$payloadRaw = file_get_contents('php://input');
$payload = json_decode($payloadRaw, true);

if (!$payload || !isset($payload['event'])) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid webhook payload']);
    exit;
}

// 2. Ambil Secret dari database pengaturan dan Verifikasi Signature
try {
    $settingsModel = new SettingsModel();
    $settings = $settingsModel->getAll();
    $webhookSecret = trim($settings['wa_webhook_secret'] ?? 'whsec_secret_anda');
} catch (\Throwable $e) {
    $webhookSecret = 'whsec_secret_anda';
}

$incomingSignature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? ($_SERVER['HTTP_WEBHOOK_SIGNATURE'] ?? '');

if (!empty($webhookSecret) && !empty($incomingSignature)) {
    $expected = hash_hmac('sha256', $payloadRaw, $webhookSecret);
    if (!hash_equals($expected, $incomingSignature)) {
        http_response_code(401);
        echo json_encode(['status' => false, 'message' => 'Unauthorized: Invalid signature']);
        exit;
    }
}

// 3. Tangani Event Berdasarkan Tipe
$event = $payload['event'];
$data  = $payload['data'] ?? [];

// Catat ke tabel wa_logs jika tersedia
try {
    $db = Database::getConnection();
    $senderPhone = $data['senderPhone'] ?? ($data['phone'] ?? 'webhook');
    $msgContent = $data['message'] ?? ($event . ' event received');
    
    $stmtLog = $db->prepare("
        INSERT INTO wa_logs (phone, type, status, message, response, created_at)
        VALUES (?, ?, 'success', ?, ?, NOW())
    ");
    $stmtLog->execute([
        $senderPhone,
        'webhook_' . $event,
        is_string($msgContent) ? $msgContent : json_encode($msgContent),
        $payloadRaw
    ]);
} catch (\Throwable $eLog) {
    // Abaikan error logging
}

switch ($event) {
    case 'message_received':
        // Ada pesan WhatsApp baru masuk dari pengguna/orang tua
        $pengirim = $data['senderPhone'] ?? ''; // Nomor HP pengirim (contoh: 08123456789)
        $pesan    = $data['message'] ?? '';     // Isi pesan teks
        break;

    case 'message_delivered':
        // Pesan telah sampai di HP penerima (centang 2 abu-abu)
        $msgId = $data['msgId'] ?? '';
        break;

    case 'message_read':
        // Pesan telah dibaca oleh penerima (centang 2 biru)
        $msgId = $data['msgId'] ?? '';
        break;

    case 'ping':
        // Uji coba koneksi (Ping Test) dari Dashboard Gateway
        break;
}

// 4. Kirim respon HTTP 200 OK ke Gateway
echo json_encode([
    'status'  => true,
    'event'   => $event,
    'message' => 'Webhook received successfully'
]);
