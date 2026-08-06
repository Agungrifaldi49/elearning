<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="<?= BASE_URL ?>index.php?url=library" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <div>
            <h5 class="fw-bold mb-0"><?= htmlspecialchars($book['judul']) ?></h5>
            <small class="text-muted"><?= htmlspecialchars($book['penulis'] ?? '') ?> &bull; <?= htmlspecialchars($book['kategori'] ?? '') ?></small>
        </div>
    </div>

    <div class="row g-4">
        <!-- Viewer -->
        <div class="col-12 col-lg-9">
            <div class="card-custom p-0 overflow-hidden" style="min-height:600px;">
                <?php $ext = strtolower($book['file_type'] ?? ''); ?>

                <?php if ($ext === 'pdf'): ?>
                    <!-- PDF Viewer -->
                    <iframe src="<?= BASE_URL . $book['file_path'] ?>"
                            style="width:100%; height:700px; border:none;" allowfullscreen></iframe>

                <?php elseif (in_array($ext, ['mp4','mkv'])): ?>
                    <!-- Video Player -->
                    <div class="p-4">
                        <video controls class="w-100 rounded-3" style="max-height:500px;">
                            <source src="<?= BASE_URL . $book['file_path'] ?>" type="video/mp4">
                            Browser tidak mendukung video HTML5.
                        </video>
                    </div>

                <?php else: ?>
                    <!-- Office Viewer via Google Docs (requires public URL for actual deployment) -->
                    <div class="p-5 text-center">
                        <i class="bi bi-file-earmark-fill text-muted fs-1 d-block mb-3"></i>
                        <h6 class="fw-bold">Preview tidak tersedia untuk format <?= strtoupper($ext) ?></h6>
                        <p class="text-muted small">Silakan unduh file untuk membuka di aplikasi yang sesuai.</p>
                        <a href="<?= BASE_URL ?>index.php?url=library/download&id=<?= $book['id'] ?>"
                           class="btn btn-primary">
                            <i class="bi bi-download me-1"></i> Download <?= strtoupper($ext) ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-12 col-lg-3">
            <div class="card-custom p-4 mb-3">
                <h6 class="fw-bold mb-3">Informasi Koleksi</h6>
                <div class="d-flex flex-column gap-2 small">
                    <div><span class="text-muted">Kategori:</span> <span class="badge bg-primary ms-1"><?= htmlspecialchars($book['kategori'] ?? 'Umum') ?></span></div>
                    <div><span class="text-muted">Format:</span> <span class="badge bg-secondary ms-1"><?= strtoupper($book['file_type'] ?? '-') ?></span></div>
                    <div><span class="text-muted">Diupload oleh:</span><br><strong><?= htmlspecialchars($book['uploader_name'] ?? 'Admin') ?></strong></div>
                    <div><span class="text-muted">Ditambahkan:</span><br><strong><?= date('d M Y', strtotime($book['created_at'])) ?></strong></div>
                    <div><span class="text-muted">Dilihat:</span> <strong><?= number_format($book['view_count']) ?> kali</strong></div>
                    <div><span class="text-muted">Diunduh:</span> <strong><?= number_format($book['download_count']) ?> kali</strong></div>
                </div>
            </div>

            <?php if (!empty($book['deskripsi'])): ?>
            <div class="card-custom p-4 mb-3">
                <h6 class="fw-bold mb-2">Deskripsi</h6>
                <p class="small text-muted mb-0"><?= nl2br(htmlspecialchars($book['deskripsi'])) ?></p>
            </div>
            <?php endif; ?>

            <?php if (in_array($book['file_type'], ['pdf','docx','doc','pptx','ppt','xlsx'])): ?>
            <a href="<?= BASE_URL ?>index.php?url=library/download&id=<?= $book['id'] ?>"
               class="btn btn-success w-100 fw-bold">
                <i class="bi bi-download me-1"></i> Unduh File
            </a>
            <?php endif; ?>
        </div>
    </div>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
