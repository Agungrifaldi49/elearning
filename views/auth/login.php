<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>

<div class="min-vh-100 d-flex align-items-center justify-content-center bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                <div class="card card-custom p-4 p-md-5 shadow-lg border-0">
                    <div class="text-center mb-4">
                        <div class="bg-primary text-white d-inline-flex p-3 rounded-4 mb-3 shadow">
                            <i class="bi bi-mortarboard-fill fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1">Masuk E-Learning</h4>
                        <p class="small text-muted mb-0">SMK Muthia Harapan Cicalengka Mandiri</p>
                    </div>

                    <form action="<?= BASE_URL ?>login.php" method="POST">
                        <?= Security::csrfField() ?>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Username atau Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="Masukkan username" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label small fw-semibold">Password</label>
                                <a href="<?= BASE_URL ?>index.php?url=auth/forgotPassword" class="small text-primary text-decoration-none">Lupa Password?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="Masukkan password" required>
                            </div>
                        </div>

                        <!-- Math Captcha -->
                        <div class="mb-3 p-3 bg-light rounded-3 border">
                            <label class="form-label small fw-semibold text-primary d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-shield-check"></i> Verifikasi Captcha Security
                            </label>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-primary fs-6 px-3 py-2"><?= $captchaQuestion ?> = ?</span>
                                <input type="number" name="captcha" class="form-control" placeholder="Jawaban" required>
                            </div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" name="remember_me" value="1" class="form-check-input" id="rememberMe">
                            <label class="form-check-label small" for="rememberMe">Ingat Saya di Perangkat Ini</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Sekarang
                        </button>
                    </form>

                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
