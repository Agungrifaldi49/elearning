<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.upload-library-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
    background-color: #f8fafc;
    min-height: 100vh;
}

.upload-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #064e3b 45%, #059669 100%);
    border-radius: 24px;
    box-shadow: 0 20px 40px -15px rgba(5, 150, 105, 0.25);
    position: relative;
    overflow: hidden;
    color: #ffffff;
}

/* Custom Interactive Dropzone Box */
.custom-dropzone-box {
    border: 2px dashed #a7f3d0;
    border-radius: 18px;
    background-color: #f0fdf4;
    padding: 24px;
    text-align: center;
    transition: all 0.22s ease;
    cursor: pointer;
    position: relative;
}
.custom-dropzone-box:hover, .custom-dropzone-box.dragover {
    border-color: #10b981;
    background-color: #ecfdf5;
}
.custom-dropzone-box input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.hover-scale {
    transition: transform 0.2s ease;
}
.hover-scale:hover {
    transform: scale(1.02);
}
</style>

<!-- Top Clearance for Fixed Navbar -->
<main class="main-content px-3 px-md-4 upload-library-wrapper pt-4 mt-4 mt-md-5 pb-5">
<div class="container-fluid max-width-1400 pt-2">

    <!-- Hero Header Banner -->
    <div class="upload-hero-banner text-white p-4 p-md-5 mb-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-3 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-emerald-500 bg-gradient p-3.5 rounded-4 text-white shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 60px; height: 60px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="bi bi-cloud-upload-fill fs-2"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-white mb-1" style="letter-spacing: -0.4px;">Upload Koleksi Perpustakaan Digital</h3>
                    <p class="text-white text-opacity-85 small mb-0 fw-medium">Tambahkan E-Book Digital, Modul KBM Guru, Referensi Kejuruan, atau Media Video Pembelajaran.</p>
                </div>
            </div>

            <a href="<?= BASE_URL ?>index.php?url=library" class="btn btn-outline-light rounded-pill px-4 py-2.5 fw-bold text-nowrap hover-scale" style="font-size: 0.88rem; width: fit-content; max-width: 100%;">
                <i class="bi bi-arrow-left me-1.5"></i> Kembali ke Katalog Perpustakaan
            </a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4 shadow-sm border-0">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4 shadow-sm border-0">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 bg-white mb-4">
                <form action="<?= BASE_URL ?>index.php?url=library/upload" method="POST" enctype="multipart/form-data">
                    <?= Security::csrfField() ?>

                    <div class="mb-3.5">
                        <label class="form-label fw-bold small text-dark"><i class="bi bi-fonts text-primary me-1"></i>Judul E-Book / Modul / Dokumen <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control rounded-3 py-2.5" placeholder="Contoh: Modul Pemrograman Web Native PHP 8 & MySQL" required style="font-size:0.9rem;">
                    </div>

                    <div class="row g-3 mb-3.5">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark"><i class="bi bi-person-fill text-primary me-1"></i>Penulis / Pengarang</label>
                            <input type="text" name="penulis" class="form-control rounded-3 py-2.5" placeholder="Contoh: Tim Guru RPL SMK Muthia Harapan" style="font-size:0.9rem;">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark"><i class="bi bi-tag-fill text-primary me-1"></i>Kategori Koleksi <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select rounded-3 py-2.5 fw-semibold text-dark" required style="font-size:0.9rem;">
                                <option value="Kejuruan">Kejuruan / Produktif</option>
                                <option value="Matematika">Matematika</option>
                                <option value="IPA">IPA &amp; Sains</option>
                                <option value="IPS">IPS &amp; Sejarah</option>
                                <option value="Bahasa">Bahasa (Indo/Inggris)</option>
                                <option value="Modul">Modul Pembelajaran Guru</option>
                                <option value="Referensi">Referensi Umum</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3.5">
                        <label class="form-label fw-bold small text-dark"><i class="bi bi-mortarboard-fill text-primary me-1"></i>Target Rombel / Kelas (Opsional)</label>
                        <input type="text" name="kelas_target" class="form-control rounded-3 py-2.5" placeholder="Contoh: X RPL 1, XI TKJ 2 (Kosongkan jika untuk semua kelas)" style="font-size:0.9rem;">
                    </div>

                    <div class="mb-3.5">
                        <label class="form-label fw-bold small text-dark"><i class="bi bi-text-paragraph text-primary me-1"></i>Deskripsi Ringkas Koleksi</label>
                        <textarea name="deskripsi" class="form-control rounded-3" rows="3" placeholder="Jelaskan ringkasan materi, modul, atau e-book ini..." style="font-size:0.9rem;"></textarea>
                    </div>

                    <div class="mb-3.5">
                        <label class="form-label fw-bold small text-dark"><i class="bi bi-image text-primary me-1"></i>Gambar Sampul / Cover Custom (Opsional)</label>
                        <input type="file" name="cover" class="form-control rounded-3" accept="image/png, image/jpeg, image/webp" style="font-size:0.88rem;">
                        <small class="text-muted d-block mt-1" style="font-size:0.75rem;">
                            <i class="bi bi-info-circle me-1 text-primary"></i>Jika dikosongkan, halaman pertama PDF atau tampilan file akan otomatis dijadikan sampul pratinjau.
                        </small>
                    </div>

                    <!-- Dropzone Upload Area -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-dark"><i class="bi bi-file-earmark-arrow-up-fill text-success me-1"></i>Berkas File Koleksi <span class="text-danger">*</span></label>
                        
                        <div class="custom-dropzone-box" onclick="document.getElementById('libFileInput').click()">
                            <input type="file" name="file" id="libFileInput" required accept=".pdf,.docx,.doc,.pptx,.ppt,.xlsx,.mp4,.mkv" onchange="handleLibFileSelected(this)">
                            
                            <div id="libDropzoneInitial">
                                <div class="bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                                    <i class="bi bi-cloud-arrow-up-fill fs-3"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1 small">Pilih File Koleksi atau Tarik Berkas Ke Sini</h6>
                                <p class="text-muted mb-0" style="font-size:0.78rem;">Format: PDF, Word (DOCX), PowerPoint (PPTX), Excel (XLSX), MP4 Video (Maks 50MB)</p>
                            </div>

                            <div id="libDropzoneSelected" class="d-none py-1">
                                <i class="bi bi-file-earmark-check-fill text-success fs-1 mb-1 d-block"></i>
                                <span id="libFileName" class="fw-bold text-dark d-block text-truncate mx-auto small" style="max-width: 300px;">-</span>
                                <small id="libFileSize" class="text-muted d-block mb-2" style="font-size:0.75rem;">-</small>
                                <span class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold small" onclick="resetLibFileInput(event)">
                                    <i class="bi bi-x-circle me-1"></i> Ganti File
                                </span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success fw-bold rounded-pill px-4 py-2.5 shadow-sm text-white hover-scale" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); font-size: 0.9rem;">
                        <i class="bi bi-cloud-upload-fill me-1.5"></i> Upload ke Perpustakaan Digital
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
</main>

<script>
function handleLibFileSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('libFileName').textContent = file.name;
        document.getElementById('libFileSize').textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        
        document.getElementById('libDropzoneInitial').classList.add('d-none');
        document.getElementById('libDropzoneSelected').classList.remove('d-none');
    }
}

function resetLibFileInput(e) {
    e.stopPropagation();
    const input = document.getElementById('libFileInput');
    if (input) input.value = '';
    
    document.getElementById('libDropzoneInitial').classList.remove('d-none');
    document.getElementById('libDropzoneSelected').classList.add('d-none');
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
