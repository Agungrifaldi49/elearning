<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.upload-library-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
    padding-top: 28px !important;
}

.upload-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #064e3b 50%, #059669 100%);
    border-radius: 20px;
    box-shadow: 0 12px 30px -5px rgba(5, 150, 105, 0.25);
    position: relative;
    overflow: hidden;
}
</style>

<main class="main-content px-2 px-sm-3 px-md-4 py-3 upload-library-wrapper">
<div class="container-fluid pt-3">

    <!-- Hero Header -->
    <div class="upload-hero-banner text-white p-4 p-md-5 mb-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-3 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-emerald-500 bg-gradient p-3.5 rounded-4 text-white shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 58px; height: 58px; background: #10b981;">
                    <i class="bi bi-cloud-upload-fill fs-2"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-white mb-1" style="letter-spacing: -0.4px;">Upload Koleksi Perpustakaan Digital</h3>
                    <p class="text-emerald-100 small mb-0 fw-medium">Tambahkan E-Book, Modul KBM Guru, Referensi Kejuruan, atau Video ke Perpustakaan.</p>
                </div>
            </div>

            <a href="<?= BASE_URL ?>index.php?url=library" class="btn btn-outline-light rounded-pill px-4 py-2.5 fw-bold text-nowrap" style="font-size: 0.88rem; width: fit-content; max-width: 100%;">
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
                        <input type="text" name="judul" class="form-control rounded-3 py-2.5" placeholder="Contoh: Modul Pemrograman Web Native PHP 8 & MySQL" required>
                    </div>

                    <div class="row g-3 mb-3.5">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark"><i class="bi bi-person-fill text-primary me-1"></i>Penulis / Pengarang</label>
                            <input type="text" name="penulis" class="form-control rounded-3 py-2.5" placeholder="Contoh: Tim Guru RPL SMK Muthia Harapan">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark"><i class="bi bi-tag-fill text-primary me-1"></i>Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select rounded-3 py-2.5 fw-semibold" required>
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
                        <label class="form-label fw-bold small text-dark"><i class="bi bi-mortarboard-fill text-primary me-1"></i>Target Kelas (Opsional)</label>
                        <input type="text" name="kelas_target" class="form-control rounded-3 py-2.5" placeholder="Contoh: X RPL 1, XI TKJ 2 (Kosongkan jika untuk semua kelas)">
                    </div>

                    <div class="mb-3.5">
                        <label class="form-label fw-bold small text-dark"><i class="bi bi-text-paragraph text-primary me-1"></i>Deskripsi Singkat Koleksi</label>
                        <textarea name="deskripsi" class="form-control rounded-3" rows="3" placeholder="Jelaskan ringkasan materi, modul, atau e-book ini..."></textarea>
                    </div>

                    <div class="mb-4 p-3 bg-light rounded-4 border">
                        <label class="form-label fw-bold small text-dark"><i class="bi bi-file-earmark-arrow-up-fill text-success me-1"></i>File Koleksi <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control rounded-3" required accept=".pdf,.docx,.doc,.pptx,.ppt,.xlsx,.mp4,.mkv">
                        <small class="text-muted d-block mt-2" style="font-size:0.75rem;">
                            <i class="bi bi-info-circle me-1 text-primary"></i>Format yang didukung: PDF, Word (DOCX), PowerPoint (PPTX), Excel, MP4 Video. Ukuran Maksimum: 50MB.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-success fw-bold rounded-pill px-4 py-2.5 shadow-sm text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="bi bi-cloud-upload-fill me-1.5"></i> Upload ke Perpustakaan Digital
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
