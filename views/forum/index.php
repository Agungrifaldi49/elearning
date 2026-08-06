<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Forum Diskusi Akademik</h4>
                <p class="text-muted small mb-0">Wadah tanya jawab dan diskusi antara siswa dan guru.</p>
            </div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddTopic">
                <i class="bi bi-plus-circle me-1"></i> Buat Topik Diskusi
            </button>
        </div>

        <div class="row g-4">
            <?php foreach ($topics as $t): ?>
                <div class="col-12">
                    <div class="card card-custom p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px;">
                                <?= strtoupper(substr($t['full_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-bold mb-0"><?= htmlspecialchars($t['full_name']) ?> <span class="badge bg-secondary ms-1"><?= htmlspecialchars($t['role_name']) ?></span></div>
                                <small class="text-muted"><?= date('d F Y, H:i', strtotime($t['created_at'])) ?> <?= $t['nama_mapel'] ? '| Mapel: ' . htmlspecialchars($t['nama_mapel']) : '' ?></small>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-2">
                            <a href="<?= BASE_URL ?>index.php?url=forum/detail&id=<?= $t['id'] ?>" class="text-decoration-none text-dark hover-primary">
                                <?= htmlspecialchars($t['judul']) ?>
                            </a>
                        </h5>
                        <p class="text-muted mb-3"><?= htmlspecialchars(substr($t['konten'], 0, 200)) ?>...</p>

                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                            <a href="<?= BASE_URL ?>index.php?url=forum/detail&id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-chat-text me-1"></i> <?= $t['total_replies'] ?> Balasan Diskusi
                            </a>
                            <span class="small text-muted"><i class="bi bi-heart-fill text-danger me-1"></i> <?= $t['likes_count'] ?> Suka</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<!-- Modal Add Topic -->
<div class="modal fade" id="modalAddTopic" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title">Buat Topik Diskusi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=forum" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= Security::csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Judul Topik / Pertanyaan</label>
                        <input type="text" name="judul" class="form-control" placeholder="Contoh: Kendala Instalasi XAMPP di Windows 11" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mata Pelajaran (Opsional)</label>
                        <select name="mapel_id" class="form-select">
                            <option value="0">Umum / Semua Mapel</option>
                            <?php foreach ($mapelList as $mp): ?>
                                <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Isi Pertanyaan / Penjelasan</label>
                        <textarea name="konten" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Lampiran Gambar Screenshot (Opsional)</label>
                        <input type="file" name="gambar" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Posting Topik</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
