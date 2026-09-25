<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SettingsModel.php';
require_once __DIR__ . '/../helpers/WhatsAppHelper.php';

echo "=== 1. Test Format Phone ===\n";
$phones = ['08123456789', '+628123456789', '628123456789', '0812-3456-7890', '(0812) 9988 7766'];
foreach ($phones as $p) {
    echo "$p -> " . WhatsAppHelper::formatPhone($p) . "\n";
}

echo "\n=== 2. Test Settings Model Defaults ===\n";
$sm = new SettingsModel();
$all = $sm->getAll();
echo "Gateway URL: " . ($all['wa_gateway_url'] ?? 'NOT FOUND') . "\n";
echo "API Key: " . ($all['wa_api_key'] ?? 'NOT FOUND') . "\n";
echo "Template Masuk Tepat:\n" . ($all['wa_template_masuk_tepat'] ?? 'NOT FOUND') . "\n";

echo "\n=== 3. Test Parse Template ===\n";
$tpl = $all['wa_template_masuk_tepat'] ?? "Halo {nama_siswa}, jam {jam}";
$parsed = WhatsAppHelper::parseTemplate($tpl, [
    'nama_siswa' => 'Ahmad Fadhil',
    'kelas' => 'XII RPL 1',
    'sekolah' => 'SMK Muthia Harapan Cicalengka',
    'tanggal' => 'Jumat, 25 September 2026',
    'jam' => '07:10 WIB',
    'status' => 'Hadir Tepat Waktu'
]);
echo "Parsed Output:\n$parsed\n";

echo "\n=== 4. Test Webhook Ping Response ===\n";
$ch = curl_init('http://localhost/elearning/webhook.php');
// Note: test only if web server is listening or test webhook file directly
