<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <a href="<?= BASE_URL ?>index.php?url=forum" class="btn btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Forum
        </a>

        <!-- Topic Detail -->
        <div class="card card-custom p-4 p-md-5 mb-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 48px; height: 48px;">
                    <?= strtoupper(substr($topic['full_name'], 0, 1)) ?>
                </div>
                <div>
                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($topic['full_name']) ?> <span class="badge bg-secondary ms-1"><?= htmlspecialchars($topic['role_name']) ?></span></h6>
                    <small class="text-muted"><?= date('d F Y, H:i', strtotime($topic['created_at'])) ?></small>
                </div>
            </div>

            <h4 class="fw-bold mb-3"><?= htmlspecialchars($topic['judul']) ?></h4>
            <p class="lead text-muted fs-6 mb-4"><?= nl2br(htmlspecialchars($topic['konten'])) ?></p>

            <?php if ($topic['gambar']): ?>
                <div class="mb-4">
                    <img src="<?= BASE_URL ?>assets/uploads/tugas/<?= htmlspecialchars($topic['gambar']) ?>" class="img-fluid rounded-4 shadow-sm" style="max-height: 400px; object-fit: cover;">
                </div>
            <?php endif; ?>
        </div>

        <!-- Comments Section -->
        <div class="card card-custom p-4 mb-4">
            <h5 class="fw-bold mb-4"><i class="bi bi-chat-left-dots text-primary me-2"></i> Tanggapan Diskusi (<?= count($comments) ?>)</h5>

            <div class="d-flex flex-column gap-3 mb-4">
                <?php foreach ($comments as $c): ?>
                    <div class="p-3 bg-light rounded-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="fw-bold small"><?= htmlspecialchars($c['full_name']) ?></span>
                            <span class="badge bg-primary" style="font-size:0.65rem;"><?= htmlspecialchars($c['role_name']) ?></span>
                            <small class="text-muted ms-auto"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></small>
                        </div>
                        <p class="mb-0 text-muted small"><?= nl2br(htmlspecialchars($c['komentar'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Comment Form -->
            <form action="<?= BASE_URL ?>index.php?url=forum/detail&id=<?= $topic['id'] ?>" method="POST">
                <?= Security::csrfField() ?>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Tulis Tanggapan Anda</label>
                    <textarea name="komentar" class="form-control" rows="3" placeholder="Tuliskan komentar atau solusi..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary px-4 fw-bold">
                    <i class="bi bi-send me-1"></i> Kirim Balasan
                </button>
            </form>
        </div>
    </div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
