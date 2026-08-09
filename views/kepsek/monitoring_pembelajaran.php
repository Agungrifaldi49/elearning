<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-display-fill text-primary me-2"></i>Monitoring Pembelajaran Virtual Class</h4>
            <p class="text-muted small mb-0">Pantau keaktifan kelas, penyelesaian materi, tugas, dan evaluasi CBT di seluruh rombel secara realtime.</p>
        </div>
        <a href="<?= BASE_URL ?>index.php?url=kepsek/cetakLaporan&type=siswa" target="_blank" class="btn btn-primary shadow-sm fw-bold">
            <i class="bi bi-printer me-1"></i> Cetak Laporan Monitoring PDF
        </a>
    </div>

    <!-- Summary Widgets (100% Real Database Queries) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center shadow-sm border-start border-4 border-primary">
                <div class="fw-bold text-primary fs-3"><?= $summary['total_kelas'] ?> Rombel</div>
                <small class="text-muted">Total Kelas Virtual Aktif</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center shadow-sm border-start border-4 border-success">
                <div class="fw-bold text-success fs-3"><?= $summary['total_materi'] ?> Modul</div>
                <small class="text-muted">Materi Pembelajaran Publik</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center shadow-sm border-start border-4 border-warning">
                <div class="fw-bold text-warning fs-3"><?= $summary['total_tugas'] ?> Tugas</div>
                <small class="text-muted">Penugasan Diberikan</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center shadow-sm border-start border-4 border-info">
                <div class="fw-bold text-info fs-3"><?= $summary['total_quiz'] ?> Ujian</div>
                <small class="text-muted">Paket Kuis & CBT</small>
            </div>
        </div>
    </div>

    <!-- Monitoring Table (100% Real Database Data) -->
    <div class="card-custom p-4 shadow-sm border-0 rounded-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-journal-check text-success me-2"></i>Status Pembelajaran Per Rombongan Belajar</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle <?= !empty($kelasPembelajaran) ? 'datatable' : '' ?>">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Nama Kelas</th>
                        <th>Program Keahlian</th>
                        <th>Wali Kelas Pengampu</th>
                        <th class="text-center">Modul Materi</th>
                        <th class="text-center">Tugas Terbit</th>
                        <th class="text-center">Kuis CBT</th>
                        <th class="text-center">Rata-Rata Nilai</th>
                        <th class="text-center">Status Keaktifan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kelasPembelajaran)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">Belum ada data rombel kelas virtual terdaftar.</td></tr>
                    <?php else: ?>
                        <?php foreach ($kelasPembelajaran as $i => $kp): 
                            $avgVal = (float)($kp['avg_nilai'] ?? 0);
                            $totalMat = (int)($kp['total_materi'] ?? 0);
                            $totalTug = (int)($kp['total_tugas'] ?? 0);
                            $totalQuiz = (int)($kp['total_quiz'] ?? 0);
                            $totAktivitas = $totalMat + $totalTug + $totalQuiz;

                            if ($avgVal == 0 && $totAktivitas == 0) {
                                $statusLabel = 'BELUM ADA KBM';
                                $statusBadge = 'bg-secondary';
                            } elseif ($totAktivitas >= 5 || $avgVal >= 80) {
                                $statusLabel = 'SANGAT AKTIF';
                                $statusBadge = 'bg-success';
                            } elseif ($totAktivitas >= 1 || $avgVal >= 75) {
                                $statusLabel = 'AKTIF';
                                $statusBadge = 'bg-primary';
                            } else {
                                $statusLabel = 'CUKUP';
                                $statusBadge = 'bg-warning text-dark';
                            }
                        ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($kp['nama_kelas']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($kp['nama_jurusan'] ?? 'Umum') ?></span></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($kp['nama_walikelas'] ?? 'Belum Ditentukan') ?></td>
                                <td class="text-center"><span class="badge bg-info text-dark fs-6"><?= $totalMat ?> Modul</span></td>
                                <td class="text-center"><span class="badge bg-warning text-dark fs-6"><?= $totalTug ?> Tugas</span></td>
                                <td class="text-center"><span class="badge bg-success fs-6"><?= $totalQuiz ?> Kuis</span></td>
                                <td class="text-center">
                                    <?php if ($avgVal > 0): ?>
                                        <span class="fw-bold fs-6 text-primary"><?= number_format($avgVal, 1) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Belum Dinilai</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span>
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
