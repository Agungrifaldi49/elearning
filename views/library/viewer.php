<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.viewer-library-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
    background-color: #f8fafc;
    min-height: 100vh;
}

.viewer-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #2563eb 100%);
    border-radius: 24px;
    box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.25);
    position: relative;
    overflow: hidden;
    color: #ffffff;
}

.hover-scale {
    transition: transform 0.2s ease;
}
.hover-scale:hover {
    transform: scale(1.02);
}
</style>

<!-- Top Clearance for Fixed Navbar -->
<main class="main-content px-3 px-md-4 viewer-library-wrapper pt-4 mt-4 mt-md-5 pb-5">
<div class="container-fluid max-width-1400 pt-2">

    <!-- Header Glassmorphic Banner -->
    <div class="viewer-hero-banner text-white p-4 p-md-5 mb-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-3 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
                <a href="<?= BASE_URL ?>index.php?url=library" class="btn btn-outline-light rounded-circle p-2.5 d-inline-flex align-items-center justify-content-center flex-shrink-0 hover-scale" style="width:48px; height:48px;" title="Kembali ke Katalog">
                    <i class="bi bi-arrow-left fs-4"></i>
                </a>
                <div>
                    <h4 class="fw-extrabold text-white mb-1" style="letter-spacing: -0.4px;"><?= htmlspecialchars($book['judul']) ?></h4>
                    <p class="text-white text-opacity-85 small mb-0 fw-medium">
                        <i class="bi bi-person-circle me-1"></i>Penulis: <strong><?= htmlspecialchars($book['penulis'] ?: 'Tim Guru') ?></strong>
                        &nbsp;&bull;&nbsp; Kategori: <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-0.5"><?= htmlspecialchars($book['kategori'] ?? 'Umum') ?></span>
                    </p>
                </div>
            </div>

            <?php if (in_array(strtolower($book['file_type'] ?? ''), ['pdf','docx','doc','pptx','ppt','xlsx'])): ?>
            <a href="<?= BASE_URL ?>index.php?url=library/download&id=<?= $book['id'] ?>" class="btn btn-success fw-bold rounded-pill shadow-sm px-4 py-2.5 text-nowrap hover-scale" style="font-size: 0.88rem; width: fit-content; max-width: 100%; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <i class="bi bi-download me-1.5"></i> Unduh Dokumen (<?= strtoupper($book['file_type']) ?>)
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Document Viewer Area -->
        <div class="col-12 col-lg-8 col-xl-9">
            <div class="card border-0 rounded-4 shadow-sm p-0 overflow-hidden bg-white" style="min-height:600px;">
                <?php $ext = strtolower($book['file_type'] ?? ''); ?>

                <?php if ($ext === 'pdf'): ?>
                    <!-- PDF Embed Reader -->
                    <iframe src="<?= BASE_URL . $book['file_path'] ?>"
                            style="width:100%; height:750px; border:none;" class="rounded-4" allowfullscreen></iframe>

                <?php elseif (in_array($ext, ['mp4','mkv','avi'])): ?>
                    <!-- Video Player Container -->
                    <div class="p-3 p-md-4 bg-dark d-flex align-items-center justify-content-center" style="min-height:500px;">
                        <video controls class="w-100 rounded-4 shadow-lg" style="max-height:550px;">
                            <source src="<?= BASE_URL . $book['file_path'] ?>" type="video/mp4">
                            Browser Anda tidak mendukung pemutar video HTML5.
                        </video>
                    </div>

                <?php else: ?>
                    <!-- Office Document Fallback Download Box -->
                    <div class="p-5 text-center my-auto">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px; height:80px;">
                            <i class="bi bi-file-earmark-arrow-down-fill fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Pratinjau Dokumen Format <?= strtoupper($ext) ?></h5>
                        <p class="text-muted small mb-4 mx-auto" style="max-width: 440px;">Dokumen ini merupakan file berformat Microsoft Office (<?= strtoupper($ext) ?>). Silakan unduh file untuk membukanya secara penuh di perangkat Anda.</p>
                        <a href="<?= BASE_URL ?>index.php?url=library/download&id=<?= $book['id'] ?>"
                           class="btn btn-primary fw-bold rounded-pill px-4 py-2.5 shadow-sm text-white hover-scale" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                            <i class="bi bi-download me-1.5"></i> Download File <?= strtoupper($ext) ?> Sekarang
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar Metadata Panel -->
        <div class="col-12 col-lg-4 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm p-4 mb-3 bg-white">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle-fill text-primary me-1.5"></i>Informasi Koleksi</h6>
                <div class="d-flex flex-column gap-2.5 small">
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                        <span class="text-muted">Format File:</span>
                        <span class="badge bg-secondary rounded-pill px-2.5 py-1"><?= strtoupper($book['file_type'] ?? '-') ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                        <span class="text-muted">Kategori:</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1"><?= htmlspecialchars($book['kategori'] ?? 'Umum') ?></span>
                    </div>
                    <div class="pb-2 border-bottom">
                        <span class="text-muted d-block mb-0.5">Diupload Oleh:</span>
                        <strong class="text-dark"><i class="bi bi-person-fill text-primary me-1"></i><?= htmlspecialchars($book['uploader_name'] ?? 'Admin System') ?></strong>
                    </div>
                    <div class="pb-2 border-bottom">
                        <span class="text-muted d-block mb-0.5">Tanggal Publikasi:</span>
                        <strong class="text-dark"><i class="bi bi-calendar-check text-success me-1"></i><?= date('d M Y, H:i', strtotime($book['created_at'])) ?> WIB</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-eye text-primary me-1"></i>Total Dilihat:</span>
                        <strong class="text-dark fs-6"><?= number_format($book['view_count']) ?>x</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="bi bi-download text-success me-1"></i>Total Diunduh:</span>
                        <strong class="text-dark fs-6"><?= number_format($book['download_count']) ?>x</strong>
                    </div>
                </div>
            </div>

            <?php if (!empty($book['deskripsi'])): ?>
            <div class="card border-0 rounded-4 shadow-sm p-4 mb-3 bg-white">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-text-paragraph text-primary me-1.5"></i>Deskripsi Ringkas</h6>
                <p class="small text-secondary mb-0 lh-base" style="font-size:0.85rem;"><?= nl2br(htmlspecialchars($book['deskripsi'])) ?></p>
            </div>
            <?php endif; ?>

            <?php if (in_array(strtolower($book['file_type'] ?? ''), ['pdf','docx','doc','pptx','ppt','xlsx','mp4','mkv'])): ?>
            <a href="<?= BASE_URL ?>index.php?url=library/download&id=<?= $book['id'] ?>"
               class="btn btn-success w-100 fw-bold rounded-pill shadow-sm py-2.5 text-white hover-scale" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); font-size:0.88rem;">
                <i class="bi bi-download me-1.5"></i> Unduh Koleksi Ini
            </a>
            <?php endif; ?>
        </div>
    </div>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
