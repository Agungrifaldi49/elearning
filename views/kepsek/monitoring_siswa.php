<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-people-fill text-success me-2"></i>Monitoring Siswa & Progress Belajar</h4>
                <p class="text-muted small mb-0">Laporan capaian rata-rata E-Rapor, evaluasi Kuis CBT, dan keaktifan pengumpulan tugas per siswa.</p>
            </div>
            <a href="<?= BASE_URL ?>index.php?url=kepsek/cetakLaporan&type=siswa" target="_blank" class="btn btn-primary shadow-sm fw-bold">
                <i class="bi bi-printer me-1"></i> Cetak Laporan Siswa PDF
            </a>
        </div>

        <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle <?= !empty($siswaList) ? 'datatable' : '' ?>">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>NIS / NISN</th>
                            <th>Nama Siswa</th>
                            <th>Rombel Kelas</th>
                            <th>Program Keahlian</th>
                            <th class="text-center">Tugas Dikumpul</th>
                            <th class="text-center">Rata-Rata E-Rapor</th>
                            <th class="text-center">Status Ketuntasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($siswaList)): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data siswa terdaftar.</td></tr>
                        <?php else: ?>
                            <?php foreach ($siswaList as $i => $s): 
                                $avgRapor = (float)($s['avg_rapor'] ?? 0);
                                $totalTugas = (int)($s['total_tugas_dikumpul'] ?? 0);

                                if ($avgRapor == 0 && $totalTugas == 0) {
                                    $statusBadge = 'bg-secondary';
                                    $statusText = 'BELUM ADA DATA';
                                } elseif ($avgRapor >= 75) {
                                    $statusBadge = 'bg-success';
                                    $statusText = 'TUNTAS';
                                } else {
                                    $statusBadge = 'bg-danger';
                                    $statusText = 'BELUM TUNTAS';
                                }
                            ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><code><?= htmlspecialchars($s['nis'] ?? '-') ?></code> / <?= htmlspecialchars($s['nisn'] ?? '-') ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($s['nama_lengkap']) ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($s['nama_kelas'] ?? 'Belum Ada Kelas') ?></span></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($s['nama_jurusan'] ?? 'Umum') ?></span></td>
                                    <td class="text-center">
                                        <span class="badge <?= $totalTugas > 0 ? 'bg-info text-dark' : 'bg-light text-muted border' ?> fs-6">
                                            <?= $totalTugas > 0 ? $totalTugas . ' Berkas' : '0 Berkas' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($avgRapor > 0): ?>
                                            <span class="fw-bold fs-6 text-primary"><?= number_format($avgRapor, 1) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Belum Dinilai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $statusBadge ?>">
                                            <?= $statusText ?>
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
