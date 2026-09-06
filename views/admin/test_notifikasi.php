<?php
/**
 * Admin View: Tes & Diagnostik Notifikasi Realtime (FCM)
 * E-Learning SMK Muthia Harapan Cicalengka
 */
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';

$credPath = ROOT_PATH . 'config/firebase_credentials.json';
$credPathAlt = ROOT_PATH . 'config/firebase_credentials.json.json';
$hasCred = file_exists($credPath) || file_exists($credPathAlt);
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">🚀 Diagnostik & Tes Notifikasi Realtime (FCM)</h3>
                <p class="text-muted small mb-0">Uji coba pengiriman Push Notification ke seluruh perangkat HP siswa & guru terdaftar.</p>
            </div>
            <a href="<?= BASE_URL ?>index.php?url=admin/pengumuman" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Pengumuman
            </a>
        </div>

        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                <?= $_SESSION['flash']['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Column: Status System -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold text-dark mb-0">🔍 Status Konfigurasi System</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="p-3 mb-3 rounded-3 <?= $hasCred ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' ?>">
                            <div class="d-flex align-items-center">
                                <i class="bi <?= $hasCred ? 'bi-check-circle-fill fs-4 me-3 text-success' : 'bi-x-circle-fill fs-4 me-3 text-danger' ?>"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Firebase Credentials JSON</h6>
                                    <small><?= $hasCred ? 'File Service Account JSON terdeteksi (Siap Mengirim Notifikasi Realtime).' : 'File config/firebase_credentials.json BELUM DITEMUKAN di server hosting. Upload file json ke folder config/ di cPanel.' ?></small>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark mt-4 mb-3">📱 Perangkat HP Terdaftar di Database (Total: <?= count($usersWithToken) ?> Device)</h6>
                        <?php if (empty($usersWithToken)): ?>
                            <div class="alert alert-warning rounded-3 small">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Belum ada perangkat terdaftar:</strong> Silakan buka aplikasi Flutter di HP Anda dan <strong>Login</strong> agar token HP tersimpan di database.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>FCM Token Snippet</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usersWithToken as $u): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary"><?= $u['id'] ?></span></td>
                                                <td class="fw-bold"><?= htmlspecialchars($u['username']) ?></td>
                                                <td><code class="text-danger small"><?= htmlspecialchars(substr($u['fcm_token'], 0, 20)) ?>...</code></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Form Tes Kirim -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold text-dark mb-0">✉️ Form Tes Kirim Push Notification</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <form action="<?= BASE_URL ?>index.php?url=admin/testNotifikasi" method="POST">
                            <input type="hidden" name="action" value="send_test">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Judul Notifikasi</label>
                                <input type="text" name="judul" class="form-control rounded-3" value="📢 Pengumuman Tes Realtime" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold text-dark">Isi Pesan Notifikasi</label>
                                <textarea name="isi" class="form-control rounded-3" rows="4" required>Halo, ini pesan uji coba push notification realtime dari Admin E-Learning!</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-bold shadow-sm">
                                <i class="bi bi-send-fill me-2"></i> Kirim Notifikasi Realtime Ke HP
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
