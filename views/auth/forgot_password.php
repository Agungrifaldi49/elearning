<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>

<div class="min-vh-100 d-flex align-items-center justify-content-center bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                <div class="card card-custom p-4 p-md-5 shadow-lg border-0">
                    <div class="text-center mb-4">
                        <div class="bg-warning text-dark d-inline-flex p-3 rounded-4 mb-3 shadow">
                            <i class="bi bi-key-fill fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1">Lupa Password?</h4>
                        <p class="small text-muted mb-0">Masukkan email Anda untuk instruksi pemulihan akun.</p>
                    </div>

                    <form action="<?= BASE_URL ?>index.php?url=auth/forgotPassword" method="POST">
                        <?= Security::csrfField() ?>
                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Email Terdaftar</label>
                            <input type="email" name="email" class="form-control" placeholder="contoh@smkmh-cicalengka.sch.id" required>
                        </div>
                        <button type="submit" class="btn btn-warning text-dark w-100 py-2 fw-bold shadow-sm mb-3">
                            <i class="bi bi-send-fill me-2"></i> Kirim Link Reset
                        </button>
                        <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-secondary w-100 py-2">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
