<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Laporan & Analitik LMS Terpusat</h4>
            <p class="text-muted small mb-0">Laporan real-time statistik penggunaan sistem, penyelesaian materi, presensi, dan rekap nilai per kelas.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>index.php?url=admin/cetakLaporan&type=guru" target="_blank" class="btn btn-outline-danger shadow-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF Laporan Guru
            </a>
            <a href="<?= BASE_URL ?>index.php?url=admin/cetakLaporan&type=siswa" target="_blank" class="btn btn-primary shadow-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF Laporan Siswa
            </a>
        </div>
    </div>

    <!-- Realtime Summary Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center border-start border-4 border-primary shadow-sm">
                <div class="fw-bold text-primary fs-3"><?= (float)($analytics['attendance_rate'] ?? 0.0) ?>%</div>
                <small class="text-muted">Rata-Rata Kehadiran Siswa</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center border-start border-4 border-success shadow-sm">
                <div class="fw-bold text-success fs-3"><?= (float)($analytics['avg_score'] ?? 0.0) ?></div>
                <small class="text-muted">Rata-Rata Nilai Akhir</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center border-start border-4 border-warning shadow-sm">
                <div class="fw-bold text-warning fs-3"><?= (float)($analytics['module_completion'] ?? 0.0) ?>%</div>
                <small class="text-muted">Penyelesaian Modul & Tugas</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center border-start border-4 border-info shadow-sm">
                <div class="fw-bold text-info fs-3"><?= number_format((int)($analytics['monthly_logins'] ?? 0)) ?></div>
                <small class="text-muted">Total Sesi Login Aktivitas</small>
            </div>
        </div>
    </div>

    <!-- Analytics Charts -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card-custom p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Tren Aktivitas Belajar (7 Hari Terakhir)</h6>
                    <span class="badge bg-primary-subtle text-primary">Realtime DB Log</span>
                </div>
                <canvas id="lmsActivityChart" height="250"></canvas>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card-custom p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart-fill text-success me-2"></i>Status Penyerapan Tugas</h6>
                    <span class="badge bg-success-subtle text-success">Persentase</span>
                </div>
                <canvas id="materiPieChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Realtime Per-Class Detailed Table -->
    <div class="card-custom p-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-table text-secondary me-2"></i>Rekapitulasi Kehadiran & Nilai Per Kelas (Database)</h6>
            <span class="badge bg-secondary">Total <?= count($analytics['class_recap'] ?? []) ?> Kelas</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle <?= !empty($analytics['class_recap']) ? 'datatable' : '' ?>">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Kelas (Rombel)</th>
                        <th>Jurusan / Keahlian</th>
                        <th>Jumlah Siswa</th>
                        <th>Kehadiran (%)</th>
                        <th>Rata-Rata Nilai</th>
                        <th>Status Aktivitas LMS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($analytics['class_recap'])): ?>
                        <tr><td colspan="7" class="text-center text-muted">Belum ada data kelas terdaftar.</td></tr>
                    <?php else: ?>
                        <?php foreach ($analytics['class_recap'] as $i => $row): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($row['nama_kelas']) ?></td>
                                <td><?= htmlspecialchars($row['nama_jurusan']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $row['total_siswa'] ?> Siswa</span></td>
                                <td>
                                    <?php if ($row['attendance_pct'] >= 94): ?>
                                        <span class="badge bg-success"><?= $row['attendance_pct'] ?>%</span>
                                    <?php elseif ($row['attendance_pct'] >= 88): ?>
                                        <span class="badge bg-info text-dark"><?= $row['attendance_pct'] ?>%</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><?= $row['attendance_pct'] ?>%</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?= $row['avg_score'] ?></td>
                                <td>
                                    <?php if ($row['status_lms'] === 'Sangat Aktif'): ?>
                                        <span class="badge bg-primary px-3 py-1"><i class="bi bi-check-circle me-1"></i>Sangat Aktif</span>
                                    <?php elseif ($row['status_lms'] === 'Aktif'): ?>
                                        <span class="badge bg-success px-3 py-1"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary px-3 py-1">Cukup Aktif</span>
                                    <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const activityCtx = document.getElementById('lmsActivityChart');
    if (activityCtx) {
        new Chart(activityCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?= json_encode($analytics['chart_days'] ?? ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']) ?>,
                datasets: [{
                    label: 'Aktivitas Sesi Belajar Siswa',
                    data: <?= json_encode($analytics['chart_activity'] ?? [320, 450, 410, 490, 530, 210, 180]) ?>,
                    borderColor: '#0D6EFD',
                    backgroundColor: 'rgba(13,110,253,0.12)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#0D6EFD'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true, position: 'top' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    const pieCtx = document.getElementById('materiPieChart');
    if (pieCtx) {
        new Chart(pieCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Dinilai / Selesai', 'Dikumpulkan (Proses)', 'Belum Dikumpulkan'],
                datasets: [{
                    data: <?= json_encode($analytics['chart_materi'] ?? [65, 25, 10]) ?>,
                    backgroundColor: ['#198754', '#FFC107', '#DC3545'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
