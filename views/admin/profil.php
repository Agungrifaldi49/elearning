<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$avFile = $adminUser['avatar'] ?? '';
$hasAvatar = false;
$avatarUrl = '';
if (!empty($avFile) && $avFile !== 'default_avatar.png') {
    if (file_exists(ROOT_PATH . 'assets/uploads/profile/' . $avFile)) {
        $hasAvatar = true;
        $avatarUrl = BASE_URL . 'assets/uploads/profile/' . htmlspecialchars($avFile);
    } elseif (file_exists(ROOT_PATH . 'assets/uploads/avatar/' . $avFile)) {
        $hasAvatar = true;
        $avatarUrl = BASE_URL . 'assets/uploads/avatar/' . htmlspecialchars($avFile);
    }
}
?>

<style>
.admin-profil-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0d6efd 100%);
    border-radius: 20px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
    position: relative;
    overflow: hidden;
}

.avatar-preview-box {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #ffffff;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.avatar-initial-box {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    color: #ffffff;
    font-size: 3rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px solid #ffffff;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    margin: 0 auto;
}

.strength-bar {
    height: 6px;
    border-radius: 3px;
    transition: all 0.3s ease;
}

.pwd-toggle-btn {
    cursor: pointer;
    background: #f8fafc;
    border-left: none;
}
.pwd-toggle-btn:hover {
    background: #e2e8f0;
}
</style>

<main class="main-content px-3 px-md-4">
<div class="container-fluid py-3">

    <!-- Flash Alert Messages -->
    <?php if ($flashSuccess = FlashHelper::getSuccess()): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i> <?= htmlspecialchars($flashSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($flashError = FlashHelper::getError()): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> <?= htmlspecialchars($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- 🚀 HERO BANNER ADMIN PROFIL -->
    <div class="admin-profil-hero p-4 p-md-5 mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-warning text-dark p-3 rounded-4 shadow-sm d-flex align-items-center justify-content-center" style="width: 58px; height: 58px;">
                    <i class="bi bi-shield-lock-fill fs-2"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-1 text-white" style="letter-spacing: -0.5px;">Profil & Keamanan Akun Administrator</h3>
                    <p class="text-white text-opacity-85 small mb-0">Kelola informasi identitas akun, perbarui foto profil, dan ganti kata sandi login admin untuk keamanan maksimal.</p>
                </div>
            </div>
            <span class="badge bg-success bg-opacity-25 border border-success text-success fw-bold px-3 py-2 rounded-pill fs-7">
                <i class="bi bi-patch-check-fill me-1"></i> Akun Utama Aktif
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Kiri: Ringkasan Akun & Ganti Foto -->
        <div class="col-12 col-lg-4">
            <div class="card card-custom p-4 text-center h-100 shadow-sm border-0 rounded-4">
                <div class="mb-3 position-relative d-inline-block mx-auto">
                    <?php if ($hasAvatar): ?>
                        <img src="<?= $avatarUrl ?>" alt="Avatar" class="avatar-preview-box">
                    <?php else: ?>
                        <div class="avatar-initial-box">
                            <?= strtoupper(substr($adminUser['full_name'] ?? 'A', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($adminUser['full_name']) ?></h5>
                <p class="text-muted small mb-2"><code>@<?= htmlspecialchars($adminUser['username']) ?></code></p>
                <div class="mb-3">
                    <span class="badge bg-primary px-3 py-1.5 rounded-pill"><i class="bi bi-person-fill-gear me-1"></i> Administrator Utama</span>
                </div>

                <hr class="my-3 opacity-25">

                <div class="text-start small mb-3">
                    <div class="d-flex justify-content-between py-1.5 border-bottom border-light">
                        <span class="text-muted"><i class="bi bi-envelope me-1"></i> Email:</span>
                        <span class="fw-semibold text-truncate ms-2" style="max-width: 170px;"><?= htmlspecialchars($adminUser['email'] ?? '-') ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom border-light">
                        <span class="text-muted"><i class="bi bi-calendar-check me-1"></i> Terdaftar:</span>
                        <span class="fw-semibold"><?= !empty($adminUser['created_at']) ? date('d M Y', strtotime($adminUser['created_at'])) : '-' ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-1.5 border-bottom border-light">
                        <span class="text-muted"><i class="bi bi-clock-history me-1"></i> Terakhir Login:</span>
                        <span class="fw-semibold"><?= !empty($adminUser['last_seen']) ? date('d M Y H:i', strtotime($adminUser['last_seen'])) : '-' ?></span>
                    </div>
                </div>

                <div class="alert alert-info bg-info bg-opacity-10 border-0 rounded-3 text-start small mb-0">
                    <i class="bi bi-shield-exclamation text-info me-1"></i>
                    <strong>Tips Keamanan:</strong> Jangan gunakan password yang mudah ditebak seperti tanggal lahir, nama sekolah, atau kata sandi default <code>admin123</code>.
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Form Ganti Password & Edit Data Profil -->
        <div class="col-12 col-lg-8">
            <!-- Kartu 1: Form Ganti Password (Prioritas Utama) -->
            <div class="card card-custom p-4 p-md-4 shadow-sm border-0 rounded-4 mb-4" id="cardGantiPassword">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <div class="bg-danger text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                        <i class="bi bi-key-fill"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Ubah Kata Sandi (Password Baru)</h5>
                        <small class="text-muted">Perbarui kata sandi admin untuk mencegah akses tidak sah ke sistem E-Learning.</small>
                    </div>
                </div>

                <form action="<?= BASE_URL ?>index.php?url=admin/profil" method="POST" id="formGantiPassword">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="update_password">

                    <div class="row g-3">
                        <!-- Password Saat Ini / Lama -->
                        <div class="col-12">
                            <label class="form-label small fw-bold">Kata Sandi Saat Ini (Lama) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill text-muted"></i></span>
                                <input type="password" name="current_password" id="current_password" class="form-control border-start-0 border-end-0" placeholder="Masukkan password admin saat ini..." required>
                                <button class="btn btn-outline-secondary pwd-toggle-btn" type="button" onclick="togglePasswordVisibility('current_password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted" style="font-size:0.75rem;">Masukkan password yang Anda gunakan saat login saat ini.</small>
                        </div>

                        <!-- Password Baru -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Kata Sandi Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock-fill text-danger"></i></span>
                                <input type="password" name="new_password" id="new_password" class="form-control border-start-0 border-end-0" placeholder="Minimal 8 karakter..." required onkeyup="checkPasswordStrength(this.value)">
                                <button class="btn btn-outline-secondary pwd-toggle-btn" type="button" onclick="togglePasswordVisibility('new_password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <!-- Password Strength Indicator Bar -->
                            <div class="progress mt-2" style="height: 5px;">
                                <div id="strengthProgress" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="small" id="strengthText" style="font-size: 0.75rem; color: #64748b;">Kekuatan: Belum diisi</span>
                                <span class="small text-muted" style="font-size: 0.72rem;">Min. 8 Karakter</span>
                            </div>
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-check2-circle text-success"></i></span>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control border-start-0 border-end-0" placeholder="Ketik ulang kata sandi baru..." required onkeyup="validatePasswordMatch()">
                                <button class="btn btn-outline-secondary pwd-toggle-btn" type="button" onclick="togglePasswordVisibility('confirm_password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <small class="small text-muted d-block mt-1" id="matchFeedback" style="font-size: 0.75rem;">Pastikan kedua password sama persis.</small>
                        </div>
                    </div>

                    <div class="mt-4 pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Setelah password diubah, password lama tidak akan bisa dipakai lagi.</small>
                        <button type="submit" class="btn btn-danger fw-bold px-4 py-2 rounded-3 shadow-xs">
                            <i class="bi bi-shield-check me-1"></i> Simpan Password Baru
                        </button>
                    </div>
                </form>
            </div>

            <!-- Kartu 2: Form Edit Data Akun & Foto -->
            <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                        <i class="bi bi-person-gear"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Informasi Akun & Foto Profil</h5>
                        <small class="text-muted">Perbarui nama tampilan dan email resmi administrator.</small>
                    </div>
                </div>

                <form action="<?= BASE_URL ?>index.php?url=admin/profil" method="POST" enctype="multipart/form-data">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="update_profile">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Username Akun</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-at"></i></span>
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($adminUser['username']) ?>" readonly disabled>
                            </div>
                            <small class="text-muted" style="font-size:0.75rem;">Username utama tidak dapat diubah untuk menjaga integritas sistem.</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Nama Lengkap Administrator <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($adminUser['full_name']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Email Administrator <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($adminUser['email']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Upload Foto Profil Baru (Opsional)</label>
                            <input type="file" name="avatar" class="form-control" accept="image/png, image/jpeg, image/jpg">
                            <small class="text-muted" style="font-size:0.75rem;">Format JPG/PNG, ukuran maks. 2MB.</small>
                        </div>
                    </div>

                    <div class="mt-4 pt-2 border-top text-end">
                        <button type="submit" class="btn btn-primary fw-bold px-4 py-2 rounded-3 shadow-xs">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</main>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function checkPasswordStrength(password) {
    const progressBar = document.getElementById('strengthProgress');
    const strengthText = document.getElementById('strengthText');
    
    if (!password) {
        progressBar.style.width = '0%';
        progressBar.className = 'progress-bar bg-danger';
        strengthText.innerText = 'Kekuatan: Belum diisi';
        strengthText.style.color = '#64748b';
        return;
    }

    let score = 0;
    if (password.length >= 8) score += 25;
    if (password.length >= 12) score += 15;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score += 25;
    if (/\d/.test(password)) score += 20;
    if (/[^a-zA-Z0-9]/.test(password)) score += 15;

    progressBar.style.width = score + '%';

    if (score < 40) {
        progressBar.className = 'progress-bar bg-danger';
        strengthText.innerText = 'Kekuatan: Lemah (Mudah Ditebak)';
        strengthText.style.color = '#dc3545';
    } else if (score < 75) {
        progressBar.className = 'progress-bar bg-warning';
        strengthText.innerText = 'Kekuatan: Sedang (Cukup Aman)';
        strengthText.style.color = '#eab308';
    } else {
        progressBar.className = 'progress-bar bg-success';
        strengthText.innerText = 'Kekuatan: Sangat Kuat & Aman! 🛡️';
        strengthText.style.color = '#198754';
    }

    validatePasswordMatch();
}

function validatePasswordMatch() {
    const newPwd = document.getElementById('new_password').value;
    const confirmPwd = document.getElementById('confirm_password').value;
    const feedback = document.getElementById('matchFeedback');

    if (!confirmPwd) {
        feedback.innerText = 'Pastikan kedua password sama persis.';
        feedback.className = 'small text-muted d-block mt-1';
        return;
    }

    if (newPwd === confirmPwd) {
        feedback.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Kata sandi cocok!</span>';
    } else {
        feedback.innerHTML = '<span class="text-danger fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Kata sandi tidak cocok!</span>';
    }
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
