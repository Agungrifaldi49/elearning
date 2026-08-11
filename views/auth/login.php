<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>

<div class="min-vh-100 d-flex align-items-center justify-content-center bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                <div class="card card-custom p-4 p-md-5 shadow-lg border-0 rounded-4">
                    <div class="text-center mb-4">
                        <div class="bg-primary text-white d-inline-flex p-3 rounded-4 mb-3 shadow">
                            <i class="bi bi-mortarboard-fill fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1 text-dark">Masuk E-Learning</h4>
                        <p class="small text-muted mb-0">SMK Muthia Harapan Cicalengka Mandiri</p>
                    </div>

                    <form action="<?= BASE_URL ?>login.php" method="POST">
                        <?= Security::csrfField() ?>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">Username atau Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="Masukkan username atau email" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-dark mb-0">Password</label>
                                <a href="<?= BASE_URL ?>index.php?url=auth/forgotPassword" class="small text-primary text-decoration-none fw-bold"><i class="bi bi-key me-1"></i>Lupa Password?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="password" id="loginPassword" class="form-control border-start-0 border-end-0 ps-0" placeholder="Masukkan password" required>
                                <button class="btn btn-outline-secondary border-start-0" type="button" onclick="toggleLoginPassword()" title="Tampilkan / Sembunyikan Password">
                                    <i class="bi bi-eye" id="loginToggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Math Captcha Security -->
                        <div class="mb-3 p-3 bg-light rounded-3 border">
                            <label class="form-label small fw-semibold text-primary d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-shield-check"></i> Verifikasi Captcha Security
                            </label>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-primary fs-6 px-3 py-2 shadow-sm"><?= $captchaQuestion ?> = ?</span>
                                <input type="number" name="captcha" class="form-control rounded-3" placeholder="Jawaban" required>
                            </div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" name="remember_me" value="1" class="form-check-input" id="rememberMe">
                            <label class="form-check-label small text-muted" for="rememberMe">Ingat Saya di Perangkat Ini</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow hover-scale">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleLoginPassword() {
    const input = document.getElementById('loginPassword');
    const icon = document.getElementById('loginToggleIcon');
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash text-primary';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
