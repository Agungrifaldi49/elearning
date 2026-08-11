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
                        <!-- STEP 2: FORM SETUP PASSWORD BARU WITH LIVE VALIDATION CHECKLIST -->
                        <form action="<?= BASE_URL ?>index.php?url=auth/forgotPassword&step=2" method="POST" id="formResetPassword">
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
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                    <input type="password" name="new_password" id="newPassword" class="form-control border-start-0 border-end-0 ps-0" placeholder="Minimal 8 karakter (kombinasi huruf, angka, simbol)..." minlength="8" required autofocus>
                                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassVisibility('newPassword', 'toggleIcon1')">
                                        <i class="bi bi-eye" id="toggleIcon1"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Live Password Requirements Checklist & Strength Bar -->
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-bold text-dark"><i class="bi bi-shield-check text-primary me-1"></i> Syarat Keamanan Password:</span>
                                    <span id="strengthText" class="badge bg-secondary small">Belum Diisi</span>
                                </div>

                                <div class="progress mb-3" style="height: 6px;">
                                    <div id="strengthBar" class="progress-bar bg-secondary" role="progressbar" style="width: 0%;"></div>
                                </div>

                                <div class="row g-2 small" id="passwordChecklist">
                                    <div class="col-6 text-muted" id="ruleLength">
                                        <i class="bi bi-x-circle me-1"></i> Min 8 Karakter
                                    </div>
                                    <div class="col-6 text-muted" id="ruleUpper">
                                        <i class="bi bi-x-circle me-1"></i> Huruf Besar (A-Z)
                                    </div>
                                    <div class="col-6 text-muted" id="ruleLower">
                                        <i class="bi bi-x-circle me-1"></i> Huruf Kecil (a-z)
                                    </div>
                                    <div class="col-6 text-muted" id="ruleNumber">
                                        <i class="bi bi-x-circle me-1"></i> Angka (0-9)
                                    </div>
                                    <div class="col-12 text-muted" id="ruleSymbol">
                                        <i class="bi bi-x-circle me-1"></i> Simbol / Spesial (!@#$%)
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-dark">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control border-start-0 border-end-0 ps-0" placeholder="Ulangi password baru..." minlength="8" required>
                                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassVisibility('confirmPassword', 'toggleIcon2')">
                                        <i class="bi bi-eye" id="toggleIcon2"></i>
                                    </button>
                                </div>
                                <div id="matchText" class="small mt-1 d-none"></div>
                            </div>

                            <button type="submit" id="btnSubmitReset" class="btn btn-primary w-100 py-3 fw-bold shadow-sm mb-2 hover-scale">
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

document.addEventListener('DOMContentLoaded', function() {
    const passInput = document.getElementById('newPassword');
    const confirmInput = document.getElementById('confirmPassword');
    if (!passInput) return;

    function updateRule(elemId, isValid) {
        const el = document.getElementById(elemId);
        if (!el) return;

        if (isValid) {
            el.className = 'col-6 text-success fw-bold';
            if (elemId === 'ruleSymbol') el.className = 'col-12 text-success fw-bold';
            el.querySelector('i').className = 'bi bi-check-circle-fill me-1';
        } else {
            el.className = 'col-6 text-muted';
            if (elemId === 'ruleSymbol') el.className = 'col-12 text-muted';
            el.querySelector('i').className = 'bi bi-x-circle me-1';
        }
    }

    function checkStrength() {
        const val = passInput.value;
        const confirmVal = confirmInput ? confirmInput.value : '';

        const hasLen = val.length >= 8;
        const hasUpper = /[A-Z]/.test(val);
        const hasLower = /[a-z]/.test(val);
        const hasNum = /[0-9]/.test(val);
        const hasSym = /[\W_]/.test(val);

        updateRule('ruleLength', hasLen);
        updateRule('ruleUpper', hasUpper);
        updateRule('ruleLower', hasLower);
        updateRule('ruleNumber', hasNum);
        updateRule('ruleSymbol', hasSym);

        let score = (hasLen ? 1 : 0) + (hasUpper ? 1 : 0) + (hasLower ? 1 : 0) + (hasNum ? 1 : 0) + (hasSym ? 1 : 0);

        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        if (val.length === 0) {
            strengthBar.style.width = '0%';
            strengthBar.className = 'progress-bar bg-secondary';
            strengthText.textContent = 'Belum Diisi';
            strengthText.className = 'badge bg-secondary small';
        } else if (score <= 2) {
            strengthBar.style.width = '35%';
            strengthBar.className = 'progress-bar bg-danger';
            strengthText.textContent = 'Sangat Lemah ⚠️';
            strengthText.className = 'badge bg-danger small';
        } else if (score <= 4) {
            strengthBar.style.width = '70%';
            strengthBar.className = 'progress-bar bg-warning text-dark';
            strengthText.textContent = 'Sedang 🟡';
            strengthText.className = 'badge bg-warning text-dark small';
        } else {
            strengthBar.style.width = '100%';
            strengthBar.className = 'progress-bar bg-success';
            strengthText.textContent = 'Sangat Kuat 🔥';
            strengthText.className = 'badge bg-success small';
        }

        // Check password match
        const matchText = document.getElementById('matchText');
        if (confirmInput && confirmVal.length > 0) {
            matchText.classList.remove('d-none');
            if (val === confirmVal) {
                matchText.textContent = '✓ Konfirmasi password cocok';
                matchText.className = 'small mt-1 text-success fw-bold';
            } else {
                matchText.textContent = '✗ Konfirmasi password tidak cocok';
                matchText.className = 'small mt-1 text-danger fw-bold';
            }
        } else if (matchText) {
            matchText.classList.add('d-none');
        }
    }

    passInput.addEventListener('input', checkStrength);
    if (confirmInput) {
        confirmInput.addEventListener('input', checkStrength);
    }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
