<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-cloud-upload-fill text-success me-2"></i>Upload Koleksi Perpustakaan Digital</h4>
            <p class="text-muted small mb-0">Tambahkan E-Book, Modul Guru, Referensi, atau Video ke Perpustakaan.</p>
        </div>
        <a href="<?= BASE_URL ?>index.php?url=library" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Perpustakaan
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card-custom p-4 p-md-5">
                <form action="<?= BASE_URL ?>index.php?url=library/upload" method="POST" enctype="multipart/form-data">
                    <?= Security::csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Judul E-Book / Modul / Dokumen <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control" placeholder="Contoh: Modul Pemrograman Web Native PHP 8 & MySQL" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Penulis / Pengarang</label>
                            <input type="text" name="penulis" class="form-control" placeholder="Contoh: Tim Guru RPL SMK Muthia Harapan">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select" required>
                                <option value="Kejuruan">Kejuruan / Produktif</option>
                                <option value="Matematika">Matematika</option>
                                <option value="IPA">IPA & Sains</option>
                                <option value="IPS">IPS & Sejarah</option>
                                <option value="Bahasa">Bahasa (Indo/Inggris)</option>
                                <option value="Modul">Modul Pembelajaran Guru</option>
                                <option value="Referensi">Referensi Umum</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Target Kelas (Opsional)</label>
                        <input type="text" name="kelas_target" class="form-control" placeholder="Contoh: X RPL 1, XI TKJ 2 (atau kosongkan untuk semua kelas)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Deskripsi Singkat</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan secara ringkas isi materi atau modul ini..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Upload File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required accept=".pdf,.docx,.doc,.pptx,.ppt,.xlsx,.mp4,.mkv">
                        <small class="text-muted">Format yang didukung: PDF, Word (DOCX), PowerPoint (PPTX), Excel, MP4 Video. Maksimal 50MB.</small>
                    </div>

                    <button type="submit" class="btn btn-success fw-bold px-4 py-2">
                        <i class="bi bi-cloud-upload me-1"></i> Upload ke Perpustakaan
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
