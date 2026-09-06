<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$backups = is_array($backups ?? null) ? $backups : [];
$backupStats = is_array($backupStats ?? null) ? $backupStats : [
    'total_files' => 0,
    'auto_files' => 0,
    'manual_files' => 0,
    'last_auto' => null,
    'last_manual' => null,
    'total_storage' => '0 KB'
];
?>

<style>
.backup-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
    border-radius: 20px;
    color: #ffffff;
    padding: 32px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
.backup-hero-banner::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 350px;
    height: 350px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.stat-card-glass {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.8);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.stat-card-glass:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
}
.pulse-indicator {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #10b981;
    box-shadow: 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulse-ring 2s infinite;
}
@keyframes pulse-ring {
    0% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
    }
}
</style>

<main class="main-content px-3 px-md-4 py-3">
    <div class="container-fluid">

        <!-- Hero Header -->
        <div class="backup-hero-banner mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success-subtle text-success px-3 py-1.5 rounded-pill fw-bold border border-success-subtle d-inline-flex align-items-center gap-2">
                            <span class="pulse-indicator"></span>
                            <span>Auto-Backup Realtime System Active</span>
                        </span>
                    </div>
                    <h3 class="fw-bold text-white mb-1"><i class="bi bi-database-fill-gear text-indigo-400 me-2"></i>Pusat Data, Backup & Restore Database</h3>
                    <p class="text-white-50 small mb-0">Manajemen pemulihan data, pencadangan otomatis aktivitas pengguna (Admin, Guru, Siswa, Kepsek), serta ekspor/impor SQL.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-light text-dark fw-bold rounded-3 px-3 py-2 shadow-xs d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalUploadRestore">
                        <i class="bi bi-cloud-arrow-up-fill text-primary"></i> Upload & Restore SQL
                    </button>
                    <button class="btn btn-indigo btn-primary fw-bold rounded-3 px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCreateBackup">
                        <i class="bi bi-plus-circle-fill"></i> Buat Backup Manual
                    </button>
                </div>
            </div>
        </div>

        <!-- Flash Notice -->
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

        <!-- Statistic Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card-glass p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Total File Backup</span>
                        <div class="bg-primary-subtle text-primary p-2 rounded-3">
                            <i class="bi bi-folder-fill fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-0"><?= number_format($backupStats['total_files']) ?></h3>
                    <span class="text-muted text-nowrap" style="font-size:0.78rem;">Berkas tersimpan</span>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card-glass p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Auto Backups</span>
                        <div class="bg-info-subtle text-info p-2 rounded-3">
                            <i class="bi bi-cpu-fill fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-info mb-0"><?= number_format($backupStats['auto_files']) ?></h3>
                    <span class="text-muted text-nowrap" style="font-size:0.78rem;"><?= $backupStats['last_auto'] ? 'Terakhir: ' . date('d/m H:i', strtotime($backupStats['last_auto'])) : 'Belum ada' ?></span>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card-glass p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Manual Backups</span>
                        <div class="bg-purple-subtle text-purple p-2 rounded-3" style="background:#f3e8ff; color:#9333ea;">
                            <i class="bi bi-person-fill-gear fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-purple mb-0" style="color:#9333ea;"><?= number_format($backupStats['manual_files']) ?></h3>
                    <span class="text-muted text-nowrap" style="font-size:0.78rem;"><?= $backupStats['last_manual'] ? 'Terakhir: ' . date('d/m H:i', strtotime($backupStats['last_manual'])) : 'Belum ada' ?></span>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card-glass p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Storage Terpakai</span>
                        <div class="bg-success-subtle text-success p-2 rounded-3">
                            <i class="bi bi-hdd-stack-fill fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-success mb-0"><?= htmlspecialchars($backupStats['total_storage']) ?></h3>
                    <span class="text-muted text-nowrap" style="font-size:0.78rem;">Ukuran folder database/</span>
                </div>
            </div>
        </div>

        <!-- Table Backup Files -->
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-4">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
                <div>
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Daftar Berkas Cadangan Database</h5>
                    <p class="text-muted small mb-0">Seluruh snapshot database otomatis dan manual tersimpan secara aman di server.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable" style="font-size: 0.9rem;">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4" style="width: 50px;">No</th>
                            <th style="width: 130px;">Tipe</th>
                            <th>Nama File Backup</th>
                            <th>Keterangan / Trigger Activity</th>
                            <th style="width: 110px;">Ukuran</th>
                            <th style="width: 170px;">Waktu Dibuat</th>
                            <th class="pe-4 text-center" style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($backups)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    Belum ada berkas backup tersimpan. Klik "Buat Backup Manual" untuk memulai.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($backups as $i => $b): 
                                $isAuto = ($b['type'] ?? 'manual') === 'auto';
                            ?>
                                <tr>
                                    <td class="ps-4 fw-semibold text-muted"><?= $i + 1 ?></td>
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
                                            <code class="fw-bold text-dark"><?= htmlspecialchars($b['file_name']) ?></code>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-secondary small d-block text-truncate" style="max-width: 320px;">
                                            <?= htmlspecialchars($b['note'] ?: ($isAuto ? 'Auto backup aktivitas pengguna' : 'Pencadangan manual')) ?>
                                        </span>
                                    </td>
                                    <td><span class="badge bg-secondary-subtle text-secondary fw-semibold"><?= htmlspecialchars($b['file_size']) ?></span></td>
                                    <td class="text-muted small">
                                        <i class="bi bi-calendar-event me-1"></i><?= date('d M Y, H:i:s', strtotime($b['created_at'])) ?>
                                    </td>
                                    <td class="pe-4 text-center">
                                        <div class="d-inline-flex gap-1">
                                            <a href="<?= BASE_URL ?>database/<?= htmlspecialchars($b['file_name']) ?>" class="btn btn-sm btn-outline-primary rounded-3 px-2.5 py-1" download title="Unduh SQL">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-warning rounded-3 px-2.5 py-1" data-bs-toggle="modal" data-bs-target="#modalRestore<?= $b['id'] ?>" title="Restore Database">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger rounded-3 px-2.5 py-1" data-bs-toggle="modal" data-bs-target="#modalDelete<?= $b['id'] ?>" title="Hapus Backup">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Restore -->
                                <div class="modal fade" id="modalRestore<?= $b['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow-lg">
                                            <div class="modal-header border-0 pb-0 px-4 pt-4">
                                                <h5 class="fw-bold text-warning"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Pemulihan (Restore)</h5>
                                                <button class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="<?= BASE_URL ?>index.php?url=admin/backup" method="POST" class="p-4 pt-2">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="restore">
                                                <input type="hidden" name="file_name" value="<?= htmlspecialchars($b['file_name']) ?>">

                                                <div class="alert alert-warning rounded-3 small mb-3">
                                                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                                                    <strong>PERHATIAN:</strong> Memulihkan database akan menimpa seluruh data aktif aplikasi saat ini dengan data dari file backup <code><?= htmlspecialchars($b['file_name']) ?></code>.
                                                </div>

                                                <p class="small text-muted mb-4">Pastikan Anda telah membuat backup manual data saat ini jika diperlukan sebelum melakukan restore.</p>

                                                <div class="d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-3 px-4 shadow-sm">
                                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Ya, Pulihkan Database
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Delete -->
                                <div class="modal fade" id="modalDelete<?= $b['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow-lg">
                                            <div class="modal-header border-0 pb-0 px-4 pt-4">
                                                <h5 class="fw-bold text-danger"><i class="bi bi-trash-fill me-2"></i>Hapus Berkas Backup</h5>
                                                <button class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="<?= BASE_URL ?>index.php?url=admin/backup" method="POST" class="p-4 pt-2">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $b['id'] ?>">

                                                <p class="small text-secondary mb-4">Apakah Anda yakin ingin menghapus berkas backup <code><?= htmlspecialchars($b['file_name']) ?></code> secara permanen dari server?</p>

                                                <div class="d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger fw-bold rounded-3 px-4 shadow-sm">
                                                        <i class="bi bi-trash-fill me-1"></i> Hapus Permanen
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- Modal Create Backup Manual -->
<div class="modal fade" id="modalCreateBackup" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-bold text-dark"><i class="bi bi-cloud-arrow-down-fill text-primary me-2"></i>Buat Backup SQL Baru</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/backup" method="POST" class="p-4 pt-2">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create">

                <div class="mb-3">
                    <label class="form-label small fw-bold">Catatan / Keterangan Backup</label>
                    <input type="text" name="note" class="form-control rounded-3" placeholder="Contoh: Backup sebelum maintenance bulanan" value="Manual Backup oleh Administrator">
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4 shadow-sm">
                        <i class="bi bi-cloud-arrow-down-fill me-1"></i> Proses Backup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Upload & Restore SQL -->
<div class="modal fade" id="modalUploadRestore" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-bold text-primary"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Upload & Restore Database (.SQL)</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/backup" method="POST" enctype="multipart/form-data" class="p-4 pt-2">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="upload_restore">

                <div class="mb-3">
                    <label class="form-label small fw-bold">Pilih File Backup SQL (.sql)</label>
                    <input type="file" name="sql_file" class="form-control rounded-3" accept=".sql" required>
                    <div class="form-text small text-muted">File .sql yang diunggah akan disimpan di folder database/ dan langsung dipulihkan ke sistem.</div>
                </div>

                <div class="alert alert-warning rounded-3 small mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Peringatan:</strong> Seluruh tabel dan data database aktif akan diperbarui sesuai isi berkas SQL yang diunggah.
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4 shadow-sm">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Unggah & Restore Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>

