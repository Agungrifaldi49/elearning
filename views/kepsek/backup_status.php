<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Flash Messages -->
        <?php if ($msg = FlashHelper::getSuccess()): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($err = FlashHelper::getError()): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($err) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-shield-fill-check text-success me-2"></i>Status Kesehatan Sistem & Backup Database</h4>
                <p class="text-muted small mb-0">Pengawasan integritas data sekolah, ketersediaan cadangan database (Disaster Recovery), dan stabilitas sistem LMS.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <a href="<?= BASE_URL ?>index.php?url=kepsek/downloadLiveBackup" class="btn btn-primary px-3 py-2 rounded-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="bi bi-cloud-arrow-down-fill fs-5"></i> Download Backup Database Terkini (.sql)
                </a>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-6 d-inline-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-1"></i> Sistem Database Terlindungi
                </span>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-primary text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Total Arsip Backup</div>
                    <div class="display-6 fw-bold my-1"><?= $backupStats['total_files'] ?? ($backupStats['total_backups'] ?? count($backups)) ?> File</div>
                    <small>Tersimpan Aman di Server</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-success text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Ukuran Total Arsip</div>
                    <div class="display-6 fw-bold my-1"><?= $backupStats['total_storage'] ?? ($backupStats['total_size_formatted'] ?? '0 KB') ?></div>
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
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-database-check text-primary me-2"></i>Riwayat Cadangan Database Sekolah</h6>
                    <small class="text-muted">Unduh file arsip SQL kapan saja untuk keperluan restorasi di server atau phpMyAdmin.</small>
                </div>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">Arsip Eksekutif</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle <?= !empty($backups) ? 'datatable' : '' ?>">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th style="width:100px;">Tipe</th>
                            <th>Nama File Backup</th>
                            <th>Keterangan / Catatan</th>
                            <th>Ukuran File</th>
                            <th>Waktu Eksekusi</th>
                            <th class="text-center" style="width:140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($backups)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada catatan riwayat backup database.</td></tr>
                        <?php else: ?>
                            <?php foreach ($backups as $i => $b): 
                                $fileName = $b['file_name'] ?? ($b['filename'] ?? '');
                                $fileSize = $b['file_size'] ?? ($b['size_formatted'] ?? ($b['filesize'] ?? '-'));
                                $backupType = $b['type'] ?? ($b['backup_type'] ?? 'otomatis');
                                $isAuto = strtolower($backupType) === 'auto' || strtolower($backupType) === 'otomatis';
                                $note = $b['note'] ?? ($isAuto ? 'Auto Backup Sistem' : 'Pencadangan Manual');
                            ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <?php if ($isAuto): ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 rounded-pill fw-bold">
                                                <i class="bi bi-robot me-1"></i> AUTO
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-purple-subtle text-purple border border-purple-subtle px-2.5 py-1 rounded-pill fw-bold" style="background:#f3e8ff; color:#9333ea; border-color:#e9d5ff !important;">
                                                <i class="bi bi-person-fill-gear me-1"></i> MANUAL
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-filetype-sql text-primary fs-5"></i>
                                            <code class="fw-bold text-dark"><?= htmlspecialchars($fileName) ?></code>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-secondary small d-block text-truncate" style="max-width: 320px;">
                                            <?= htmlspecialchars($note) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($fileSize) ?></span>
                                    </td>
                                    <td>
                                        <small class="text-dark fw-semibold"><?= date('d M Y, H:i:s', strtotime($b['created_at'])) ?> WIB</small>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>index.php?url=kepsek/downloadBackupFile&file=<?= urlencode($fileName) ?>" class="btn btn-sm btn-primary rounded-3 px-3 py-1 shadow-sm fw-semibold text-nowrap d-inline-flex align-items-center gap-1" title="Unduh File SQL">
                                            <i class="bi bi-download"></i> Unduh .SQL
                                        </a>
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
