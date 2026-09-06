<?php
/**
 * Diagnostic & Testing Tool for FCM Push Notifications
 * E-Learning SMK Muthia Harapan Cicalengka
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/FcmHelper.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Diagnostic FCM Push Notification - E-Learning SMKMH</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f3f4f6; padding: 30px; color: #1f2937; }
        .card { background: white; border-radius: 12px; padding: 24px; max-width: 720px; margin: 0 auto 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #4f46e5; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; }
        .status { padding: 10px 14px; border-radius: 6px; font-weight: 600; margin-bottom: 12px; }
        .status.success { background: #def7ec; color: #03543f; }
        .status.error { background: #fde8e8; color: #9b1c1c; }
        .btn { background: #4f46e5; color: white; border: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .btn:hover { background: #4338ca; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; font-size: 13px; }
        th { background: #f9fafb; }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-family: monospace; color: #dc2626; }
    </style>
</head>
<body>

<div class="card">
    <h2>🔍 Diagnostik Firebase Cloud Messaging (FCM)</h2>
    
    <?php
    $credPath = __DIR__ . '/config/firebase_credentials.json';
    $credPathAlt = __DIR__ . '/config/firebase_credentials.json.json';
    $hasCred = file_exists($credPath) || file_exists($credPathAlt);
    ?>

    <div class="status <?= $hasCred ? 'success' : 'error' ?>">
        1. File Service Account JSON (<code>config/firebase_credentials.json</code>): 
        <strong><?= $hasCred ? 'ADA (Siap Digunakan)' : 'TIDAK DITEMUKAN! (Harap upload ke folder config/)' ?></strong>
    </div>

    <?php
    try {
        $db = Database::getConnection();
        $stmtCol = $db->query("SHOW COLUMNS FROM users LIKE 'fcm_token'");
        $hasCol = (bool)$stmtCol->fetch();
        ?>
        <div class="status <?= $hasCol ? 'success' : 'error' ?>">
            2. Kolom <code>fcm_token</code> pada tabel <code>users</code>: 
            <strong><?= $hasCol ? 'ADA' : 'BELUM ADA! (Akan dibuat otomatis saat API dipanggil)' ?></strong>
        </div>

        <?php
        $stmtTokens = $db->query("SELECT id, username, full_name, fcm_token FROM users WHERE fcm_token IS NOT NULL AND fcm_token != ''");
        $users = $stmtTokens->fetchAll();
        ?>

        <h3>📱 Daftar Perangkat Terdaftar (Total: <?= count($users) ?> Akun)</h3>
        <?php if (empty($users)): ?>
            <p style="color: #dc2626;"><strong>Perhatian:</strong> Belum ada akun yang memiliki FCM Token di database. Silakan buka aplikasi Flutter di HP dan Login agar token HP terdaftar di sini.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Nama Lengkap</th>
                    <th>FCM Token Snippet</th>
                </tr>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['full_name'] ?? '-') ?></td>
                    <td><code><?= htmlspecialchars(substr($u['fcm_token'], 0, 30)) ?>...</code></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

    <?php } catch (Exception $e) { ?>
        <div class="status error">Error Database: <?= htmlspecialchars($e->getMessage()) ?></div>
    <?php } ?>
</div>

<div class="card">
    <h2>🚀 Uji Coba Pengiriman Push Notification Realtime</h2>
    <form method="POST">
        <div style="margin-bottom: 12px;">
            <label style="display:block; font-weight:600; margin-bottom:5px;">Judul Notifikasi:</label>
            <input type="text" name="title" value="📢 Pengumuman Tes Realtime" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;" required>
        </div>
        <div style="margin-bottom: 15px;">
            <label style="display:block; font-weight:600; margin-bottom:5px;">Isi Pesan:</label>
            <textarea name="message" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;" rows="3" required>Halo, ini pesan pengujian push notification realtime dari server live!</textarea>
        </div>
        <button type="submit" name="send_test" class="btn">Kirim Push Notification Sekarang</button>
    </form>

    <?php
    if (isset($_POST['send_test'])) {
        echo "<h3 style='margin-top:20px;'>Hasil Pengiriman:</h3>";
        $title = trim($_POST['title']);
        $msg = trim($_POST['message']);
        
        $res = FcmHelper::sendToAll($title, $msg, ['type' => 'test']);
        if ($res) {
            echo "<div class='status success'>✅ Berhasil! Perintah Push Notification telah dikirim ke Google Firebase v1 API. Cek layar HP Anda!</div>";
        } else {
            echo "<div class='status error'>❌ Gagal! Pastikan file credentials JSON sudah ada di folder config/ dan minimal ada 1 user yang terdaftar memiliki fcm_token.</div>";
        }
    }
    ?>
</div>

</body>
</html>
