<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>

<?php
$currentStep = (int)($_GET['step'] ?? 1);
$isVerified = !empty($_SESSION['reset_user_id']);

if ($currentStep === 2 && !$isVerified) {
    header('Location: ' . BASE_URL . 'index.php?url=auth/forgotPassword');
    exit();
}
?>

<div class="min-vh-100 d-flex align-items-center justify-content-center bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                <div class="card card-custom p-4 p-md-5 shadow-lg border-0 rounded-4">
                    
                    <!-- Header Icon & Title -->
                    <div class="text-center mb-4">
                        <div class="bg-warning bg-opacity-20 text-warning-emphasis d-inline-flex p-3 rounded-4 mb-3 shadow-sm border border-warning border-opacity-30">
                            <i class="bi bi-shield-lock-fill display-5"></i>
                        </div>
                        <h4 class="fw-bold mb-1 text-dark">Pemulihan & Reset Password</h4>
                        <p class="small text-muted mb-0">Portal E-Learning SMK Muthia Harapan Cicalengka</p>
                    </div>

                    <!-- Step Progress Indicator Bar -->
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-4 p-2 bg-light rounded-pill border">
                        <div class="badge rounded-pill px-3 py-2 <?= ($currentStep === 1) ? 'bg-primary text-white fw-bold shadow-sm' : 'bg-success text-white fw-bold' ?>">
                            <i class="bi bi-shield-check me-1"></i> 1. Verifikasi Akun
                        </div>
                        <i class="bi bi-chevron-right text-muted small"></i>
                        <div class="badge rounded-pill px-3 py-2 <?= ($currentStep === 2) ? 'bg-primary text-white fw-bold shadow-sm' : 'bg-secondary bg-opacity-20 text-muted' ?>">
                            <i class="bi bi-key me-1"></i> 2. Password Baru
                        </div>
                    </div>

                    <?php if ($currentStep === 1): ?>
                        <!-- STEP 1: VERIFIKASI KEAMANAN USERNAME & EMAIL -->
                        <form action="<?= BASE_URL ?>index.php?url=auth/forgotPassword&step=1" method="POST">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="step" value="1">

                            <div class="alert alert-info border-0 rounded-3 small mb-4">
                                <i class="bi bi-info-circle-fill me-1"></i> Untuk keamanan akun, silakan masukkan <strong>Username</strong> dan <strong>Email Terdaftar</strong> akun Anda yang sesuai di sistem.
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark">Username Terdaftar <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="Masukkan username akun Anda (Contoh: admin, guru, siswa)..." required autofocus>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark">Email Terdaftar <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="Masukkan email terdaftar Anda (Contoh: siswa@smkmh-cicalengka.sch.id)..." required>
                                </div>
                            </div>

                            <!-- Math Captcha Security Verification -->
                            <div class="mb-4 p-3 bg-light rounded-3 border">
                                <label class="form-label small fw-semibold text-primary d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-shield-check"></i> Verifikasi Captcha Keamanan
                                </label>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-primary fs-6 px-3 py-2 shadow-sm"><?= $captchaQuestion ?> = ?</span>
                                    <input type="number" name="captcha" class="form-control rounded-3" placeholder="Jawaban" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-warning text-dark w-100 py-3 fw-bold shadow-sm mb-3 hover-scale">
                                <i class="bi bi-shield-check me-2"></i> Verifikasi Keamanan Akun
                            </button>

                            <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-secondary w-100 py-2 rounded-pill fw-semibold">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Login
                            </a>
                        </form>

                    <?php elseif ($currentStep === 2 && $isVerified): ?>
                        <!-- STEP 2: FORM SETUP PASSWORD BARU -->
                        <form action="<?= BASE_URL ?>index.php?url=auth/forgotPassword&step=2" method="POST">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="step" value="2">

                            <!-- Confirmed Account Card -->
                            <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-4 mb-4 text-center">
                                <span class="badge bg-success rounded-pill px-3 py-1 small mb-1"><i class="bi bi-check-circle-fill me-1"></i> Akun Terkonfirmasi Sesuai</span>
                                <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($_SESSION['reset_user_name']) ?></h5>
                                <small class="text-muted">Username: <strong><?= htmlspecialchars($_SESSION['reset_user_username']) ?></strong> | Hak Akses: <strong class="text-primary"><?= htmlspecialchars($_SESSION['reset_user_role']) ?></strong></small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark">Password Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                    <input type="password" name="new_password" id="newPassword" class="form-control border-start-0 border-end-0 ps-0" placeholder="Minimal 5 karakter..." minlength="5" required autofocus>
                                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassVisibility('newPassword', 'toggleIcon1')">
                                        <i class="bi bi-eye" id="toggleIcon1"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-dark">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control border-start-0 border-end-0 ps-0" placeholder="Ulangi password baru..." minlength="5" required>
                                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassVisibility('confirmPassword', 'toggleIcon2')">
                                        <i class="bi bi-eye" id="toggleIcon2"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm mb-2 hover-scale">
                                <i class="bi bi-check-circle-fill me-2"></i> Simpan Password Baru
                            </button>

                            <a href="<?= BASE_URL ?>index.php?url=auth/forgotPassword&action=cancel" class="btn btn-outline-secondary w-100 py-2 rounded-pill small">
                                <i class="bi bi-x-circle me-1"></i> Batal & Verifikasi Ulang
                            </a>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
