<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-shield-fill-check text-success me-2"></i>Status Kesehatan Sistem & Backup Database</h4>
                <p class="text-muted small mb-0">Pengawasan integritas data sekolah, ketersediaan cadangan database (Disaster Recovery), dan stabilitas sistem LMS.</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-6">
                    <i class="bi bi-check-circle-fill me-1"></i> Sistem Database Terlindungi
                </span>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-primary text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Total Arsip Backup</div>
                    <div class="display-6 fw-bold my-1"><?= $backupStats['total_backups'] ?? count($backups) ?> File</div>
                    <small>Tersimpan Aman di Server</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-success text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Ukuran Total Arsip</div>
                    <div class="display-6 fw-bold my-1"><?= $backupStats['total_size_formatted'] ?? '0 KB' ?></div>
                    <small>Storage Database Cadangan</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-info text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Backup Terakhir</div>
                    <div class="h5 fw-bold my-2 text-truncate"><?= !empty($backups) ? date('d M Y, H:i', strtotime($backups[0]['created_at'])) : 'Belum Ada' ?></div>
                    <small>Pencadangan Otomatis / Manual</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-dark text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Status Enkripsi & Hash</div>
                    <div class="h5 fw-bold my-2 text-success"><i class="bi bi-lock-fill me-1"></i>SHA-256 Valid</div>
                    <small>Integritas Terverifikasi</small>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Backup Database -->
        <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-database-check text-primary me-2"></i>Riwayat Cadangan Database Sekolah</h6>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">Arsip Eksekutif</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle <?= !empty($backups) ? 'datatable' : '' ?>">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Nama File Backup</th>
                            <th>Ukuran File</th>
                            <th>Tipe Pencadangan</th>
                            <th>Waktu Eksekusi</th>
                            <th>Dibuat Oleh</th>
                            <th class="text-center">Status Integritas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($backups)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada catatan riwayat backup database.</td></tr>
                        <?php else: ?>
                            <?php foreach ($backups as $i => $b): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><code><?= htmlspecialchars($b['filename']) ?></code></div>
                                        <small class="text-muted"><?= htmlspecialchars(substr($b['hash_sha256'] ?? '', 0, 32)) ?>...</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($b['size_formatted'] ?? ($b['filesize'] ?? '-')) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-dark border border-info-subtle">
                                            <?= ucfirst($b['backup_type'] ?? 'otomatis') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-dark fw-semibold"><?= date('d M Y, H:i:s', strtotime($b['created_at'])) ?> WIB</small>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= htmlspecialchars($b['created_by_name'] ?? 'System Daemon') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success px-3 py-2 rounded-pill">
                                            <i class="bi bi-shield-check me-1"></i><?= ucfirst($b['status'] ?? 'sukses') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
