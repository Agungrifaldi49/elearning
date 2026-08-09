<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Backup & Restore Database</h4>
                <p class="text-muted small mb-0">Pembuatan salinan cadangan basis data sistem secara instan.</p>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/backup" method="POST">
                <button type="submit" class="btn btn-success shadow-sm">
                    <i class="bi bi-cloud-arrow-down-fill me-1"></i> Buat Backup SQL Baru
                </button>
            </form>
        </div>

        <div class="card card-custom p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama File Backup</th>
                            <th>Ukuran File</th>
                            <th>Waktu Dibuat</th>
                            <th>Download</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $i => $b): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="fw-bold"><code><?= htmlspecialchars($b['file_name']) ?></code></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($b['file_size']) ?></span></td>
                                <td><?= date('d F Y, H:i:s', strtotime($b['created_at'])) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>database/<?= htmlspecialchars($b['file_name']) ?>" class="btn btn-sm btn-outline-primary" download>
                                        <i class="bi bi-download"></i> Unduh File
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
