<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$avatarFile = $user['avatar'] ?? '';
$hasCustomAvatar = false;
$avatarUrl = '';

if (!empty($avatarFile) && $avatarFile !== 'default_avatar.png') {
    $avatarPath = ROOT_PATH . 'assets/uploads/avatar/' . $avatarFile;
    if (file_exists($avatarPath)) {
        $hasCustomAvatar = true;
        $avatarUrl = BASE_URL . 'assets/uploads/avatar/' . htmlspecialchars($avatarFile);
    }
}
?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-person-bounding-box text-primary me-2"></i>Profil Eksekutif Kepala Sekolah</h4>
            <p class="text-muted small mb-0">Kelola identitas pimpinan, foto profil resmi, data akun, dan keamanan kata sandi Anda.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Card Info Profil & Display Avatar -->
        <div class="col-12 col-lg-4">
            <div class="card-custom p-4 text-center shadow-sm border-0 rounded-4">
                <div class="position-relative d-inline-block mb-3">
                    <?php if ($hasCustomAvatar): ?>
                        <img src="<?= $avatarUrl ?>" alt="Foto Profil Eksekutif" class="rounded-circle object-fit-cover shadow border border-primary border-3" style="width:110px; height:110px;">
                    <?php else: ?>
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow" style="width:100px; height:100px; font-size: 2.5rem;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($user['full_name'] ?? 'Kepala Sekolah') ?></h5>
                <span class="badge bg-primary px-3 py-2 fs-6 mb-3">Kepala Sekolah / Executive</span>

                <div class="border-top pt-3 text-start small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Username:</span>
                        <code class="fw-bold"><?= htmlspecialchars($user['username'] ?? 'kepsek') ?></code>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Email Resmi:</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($user['email'] ?? '-') ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Role Hak Akses:</span>
                        <span class="badge bg-success">Kepala Sekolah</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Update Profil, Password & Upload Avatar -->
        <div class="col-12 col-lg-8">
            <div class="card-custom p-4 shadow-sm border-0 rounded-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-pencil-square text-primary me-2"></i>Pengaturan Akun & Foto Profil</h6>

                <form action="<?= BASE_URL ?>index.php?url=kepsek/profil" method="POST" enctype="multipart/form-data">
                    <?= Security::csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control fw-bold" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Kedinasan / Resmi <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold"><i class="bi bi-image me-1 text-primary"></i>Unggah Foto Profil / Avatar (Opsional)</label>
                        <input type="file" name="avatar" class="form-control" accept="image/png, image/jpeg, image/jpg, image/webp">
                        <small class="text-muted mt-1 d-block">Pilih file foto formal (JPG, JPEG, PNG, WEBP). Maksimal 2MB. Kosongkan jika tidak ingin mengubah foto profil saat ini.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Ubah Kata Sandi (Password Baru)</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                        <small class="text-muted mt-1 d-block">Gunakan kombinasi password yang kuat untuk menjaga keamanan akun eksekutif.</small>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
