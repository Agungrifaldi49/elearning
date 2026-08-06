<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<!-- Cropper.js CSS & JS for Interactive Photo Adjustment -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<?php
$avFile = $user['avatar'] ?? '';
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
.profil-page-wrapper {
    padding-top: 28px !important;
}

.profil-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #2563eb 100%);
    border-radius: 20px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
    position: relative;
    overflow: hidden;
}

.avatar-preview-box {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #ffffff;
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.avatar-initial-box {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    font-size: 3.2rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px solid #ffffff;
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    margin: 0 auto;
}

/* Cropper Circular Head Framing Guide Overlay */
.cropper-view-box,
.cropper-face {
    border-radius: 50%;
}
.cropper-view-box {
    box-shadow: 0 0 0 1px #3b82f6, 0 0 0 5000px rgba(15, 23, 42, 0.75);
    outline: 2px dashed #ffffff;
}

.crop-control-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}
</style>

<main class="main-content px-3 px-md-4 profil-page-wrapper pt-4 mt-4 mt-md-5">
<div class="container-fluid">

    <!-- 🚀 HERO BANNER SISWA PROFIL -->
    <div class="profil-hero-banner p-4 p-md-5 mb-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="bg-warning text-dark p-3 rounded-4 shadow-sm d-flex align-items-center justify-content-center" style="width: 58px; height: 58px;">
                <i class="bi bi-person-circle fs-2"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-1 text-white" style="letter-spacing: -0.5px;">Profil Pengguna & Keamanan Akun Siswa</h3>
                <p class="text-white text-opacity-85 small mb-0">Kelola identitas diri, atur framing/posisi foto profil kepala secara presisi, dan ubah kata sandi login.</p>
            </div>
        </div>
    </div>

    <form action="<?= BASE_URL ?>index.php?url=siswa/profil" method="POST" enctype="multipart/form-data" id="profileForm">
        <?= Security::csrfField() ?>
        <!-- Hidden base64 input for cropped photo -->
        <input type="hidden" name="cropped_avatar_base64" id="croppedAvatarBase64">

        <div class="row g-4">
            <!-- LEFT COLUMN: AVATAR & QUICK STATS -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 rounded-4 shadow-sm p-4 text-center bg-white">
                    <div class="position-relative d-inline-block mb-3">
                        <?php if ($hasAvatar): ?>
                            <img src="<?= $avatarUrl ?>" id="avatarPreviewImg" alt="Foto Profil" class="avatar-preview-box">
                        <?php else: ?>
                            <div id="avatarInitBox" class="avatar-initial-box">
                                <?= strtoupper(substr($user['full_name'] ?? 'S', 0, 1)) ?>
                            </div>
                            <img src="" id="avatarPreviewImg" alt="Foto Profil" class="avatar-preview-box d-none">
                        <?php endif; ?>
                    </div>

                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($user['full_name']) ?></h5>
                    <p class="text-primary fw-bold small mb-2"><i class="bi bi-mortarboard-fill me-1"></i>Siswa / Pelajar Active</p>
                    
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-3 flex-wrap">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">
                            <i class="bi bi-building me-1"></i><?= htmlspecialchars($siswa['nama_kelas'] ?? 'Rombel Kelas') ?>
                        </span>
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-1 fw-semibold">
                            NIS: <?= htmlspecialchars($siswa['nis'] ?? '-') ?>
                        </span>
                    </div>

                    <!-- FOTO PROFIL UPLOAD & CROPPER INPUT -->
                    <div class="p-3 bg-light rounded-4 text-start border mt-2">
                        <label class="form-label small fw-bold text-dark d-flex justify-content-between align-items-center mb-1">
                            <span><i class="bi bi-camera-fill text-primary me-1"></i>Foto Profil (Framing Posisi)</span>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:0.7rem;">Opsional</span>
                        </label>
                        <input type="file" name="foto_profil" id="fotoProfilInput" class="form-control form-control-sm rounded-3" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="openCropModal(this)">
                        <small class="text-muted d-block mt-1" style="font-size:0.73rem;">Format: JPG, PNG, WEBP. Maksimal 5 MB. Posisi kepala & zoom dapat digeser secara presisi.</small>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: EDIT FORM INFORMATION -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 bg-white">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-person-lines-fill text-primary"></i> Informasional Data Diri Siswa
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Nama Lengkap Siswa</label>
                            <input type="text" name="full_name" class="form-control rounded-3" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Username (NIS)</label>
                            <input type="text" class="form-control rounded-3 bg-light" value="<?= htmlspecialchars($user['username']) ?>" readonly tabindex="-1">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Email Siswa</label>
                            <input type="email" name="email" class="form-control rounded-3" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="no_telepon" class="form-control rounded-3" value="<?= htmlspecialchars($siswa['no_telepon'] ?? '') ?>" placeholder="Contoh: 081234567890">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select rounded-3">
                                <option value="L" <?= ($siswa['jenis_kelamin'] ?? 'L') === 'L' ? 'selected' : '' ?>>Laki-Laki</option>
                                <option value="P" <?= ($siswa['jenis_kelamin'] ?? 'L') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">NISN</label>
                            <input type="text" class="form-control rounded-3 bg-light" value="<?= htmlspecialchars($siswa['nisn'] ?? '-') ?>" readonly tabindex="-1">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Alamat Tempat Tinggal</label>
                            <textarea name="alamat" class="form-control rounded-3" rows="2" placeholder="Alamat rumah lengkap..."><?= htmlspecialchars($siswa['alamat'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock-fill text-danger"></i> Keamanan & Ganti Kata Sandi (Password)
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Password Baru</label>
                            <input type="password" name="password" class="form-control rounded-3" placeholder="Biarkan kosong jika tidak diubah">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control rounded-3" placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2 hover-scale">
                            <i class="bi bi-floppy-fill fs-5"></i>
                            <span>Simpan Perubahan Profil</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>
</main>

<!-- ✂️ MODAL INTERAKTIF ATUR POSISI FOTO & HEAD FRAMING -->
<div class="modal fade" id="modalCropFoto" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="bg-primary rounded-3 p-2 text-white shadow-xs">
                        <i class="bi bi-crop fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0">Atur Framing & Posisi Kepala Foto Profil</h6>
                        <small class="text-info fw-medium" style="font-size:0.75rem;">Geser foto ke atas, bawah, kiri, kanan, atau zoom agar posisi kepala pas dan profesional.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-slate-900 text-center">
                <!-- Image Container for Cropper -->
                <div class="w-100 bg-black rounded-4 overflow-hidden shadow-inner d-flex align-items-center justify-content-center" style="max-height: 480px; min-height: 320px;">
                    <img id="cropperSourceImage" src="" style="max-width: 100%; display: block;">
                </div>

                <!-- Interactive Control Toolbar -->
                <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap mt-3.5">
                    <button type="button" class="btn btn-outline-light crop-control-btn" onclick="cropMove(0, -15)" title="Geser Ke Atas"><i class="bi bi-arrow-up"></i></button>
                    <button type="button" class="btn btn-outline-light crop-control-btn" onclick="cropMove(0, 15)" title="Geser Ke Bawah"><i class="bi bi-arrow-down"></i></button>
                    <button type="button" class="btn btn-outline-light crop-control-btn" onclick="cropMove(-15, 0)" title="Geser Ke Kiri"><i class="bi bi-arrow-left"></i></button>
                    <button type="button" class="btn btn-outline-light crop-control-btn" onclick="cropMove(15, 0)" title="Geser Ke Kanan"><i class="bi bi-arrow-right"></i></button>
                    <span class="border-end border-secondary mx-1" style="height:25px;"></span>
                    <button type="button" class="btn btn-outline-light crop-control-btn" onclick="cropZoom(0.1)" title="Perbesar (Zoom In)"><i class="bi bi-zoom-in"></i></button>
                    <button type="button" class="btn btn-outline-light crop-control-btn" onclick="cropZoom(-0.1)" title="Perkecil (Zoom Out)"><i class="bi bi-zoom-out"></i></button>
                    <button type="button" class="btn btn-outline-light crop-control-btn" onclick="cropRotate(-45)" title="Putar Kiri"><i class="bi bi-arrow-counterclockwise"></i></button>
                    <button type="button" class="btn btn-outline-light crop-control-btn" onclick="cropRotate(45)" title="Putar Kanan"><i class="bi bi-arrow-clockwise"></i></button>
                    <button type="button" class="btn btn-outline-warning rounded-pill px-3 py-1.5 ms-2 fw-semibold small" onclick="cropReset()"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</button>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 p-4 justify-content-between bg-dark border-top border-secondary">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="applyCroppedImage()">
                    <i class="bi bi-check-circle-fill me-1.5"></i> Terapkan & Bingkai Foto
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let cropperInstance = null;
let cropModal = null;

document.addEventListener('DOMContentLoaded', function() {
    cropModal = new bootstrap.Modal(document.getElementById('modalCropFoto'));
});

function openCropModal(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const cropImg = document.getElementById('cropperSourceImage');
            cropImg.src = e.target.result;
            
            cropModal.show();

            // Destroy existing instance if any
            if (cropperInstance) {
                cropperInstance.destroy();
            }

            // Initialize Cropper.js after modal opens
            setTimeout(() => {
                cropperInstance = new Cropper(cropImg, {
                    aspectRatio: 1, // 1:1 Square/Circle ratio
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.85,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false
                });
            }, 300);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function cropMove(x, y) {
    if (cropperInstance) cropperInstance.move(x, y);
}

function cropZoom(ratio) {
    if (cropperInstance) cropperInstance.zoom(ratio);
}

function cropRotate(degree) {
    if (cropperInstance) cropperInstance.rotate(degree);
}

function cropReset() {
    if (cropperInstance) cropperInstance.reset();
}

function applyCroppedImage() {
    if (!cropperInstance) return;

    const canvas = cropperInstance.getCroppedCanvas({
        width: 400,
        height: 400,
        fillColor: '#ffffff',
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high'
    });

    const base64Url = canvas.toDataURL('image/png');

    // Save base64 string to hidden input
    document.getElementById('croppedAvatarBase64').value = base64Url;

    // Update live preview image on profile card
    const previewImg = document.getElementById('avatarPreviewImg');
    const initBox = document.getElementById('avatarInitBox');
    if (previewImg) {
        previewImg.src = base64Url;
        previewImg.classList.remove('d-none');
    }
    if (initBox) {
        initBox.classList.add('d-none');
    }

    cropModal.hide();
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
