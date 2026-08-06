<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-person-badge-fill text-primary me-2"></i>Monitoring Tenaga Pengajar / Guru</h4>
                <p class="text-muted small mb-0">Laporan produktivitas pengampuan, jumlah modul materi, tugas, dan kuis CBT per Guru.</p>
            </div>
            <a href="<?= BASE_URL ?>index.php?url=kepsek/cetakLaporan&type=guru" target="_blank" class="btn btn-primary shadow-sm fw-bold">
                <i class="bi bi-printer me-1"></i> Cetak Laporan Guru PDF
            </a>
        </div>

        <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle <?= !empty($guruList) ? 'datatable' : '' ?>">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>NIP & Nama Guru</th>
                            <th>No. Telepon & Email</th>
                            <th>Rombel Ajar</th>
                            <th class="text-center">Modul Materi</th>
                            <th class="text-center">Tugas Terbit</th>
                            <th class="text-center">Kuis CBT</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($guruList)): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data pengajar terdaftar.</td></tr>
                        <?php else: ?>
                            <?php foreach ($guruList as $i => $g): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($g['nama_lengkap']) ?></div>
                                        <small class="text-muted">NIP: <code><?= htmlspecialchars($g['nip'] ?? '-') ?></code> | JK: <?= $g['jenis_kelamin'] ?></small>
                                    </td>
                                    <td>
                                        <div class="small text-dark"><i class="bi bi-telephone me-1 text-muted"></i><?= htmlspecialchars($g['no_telepon'] ?? '-') ?></div>
                                        <small class="text-muted"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($g['email'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark"><?= htmlspecialchars($g['kelas_ajar'] ?? 'Belum Ada Jadwal') ?></span>
                                    </td>
                                    <td class="text-center"><span class="badge bg-primary fs-6"><?= $g['total_materi'] ?> File</span></td>
                                    <td class="text-center"><span class="badge bg-warning text-dark fs-6"><?= $g['total_tugas'] ?> Tugas</span></td>
                                    <td class="text-center"><span class="badge bg-success fs-6"><?= $g['total_quiz'] ?> Kuis</span></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= ($g['status'] ?? 'aktif') === 'aktif' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($g['status'] ?? 'aktif') ?>
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
