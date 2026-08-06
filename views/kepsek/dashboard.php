<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Executive Header & Quick PDF Export -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h4 class="fw-bold mb-0"><i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Executive / Kepala Sekolah</h4>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                        <i class="bi bi-arrow-repeat me-1"></i> Database Realtime
                    </span>
                </div>
                <p class="text-muted small mb-0">Pengawasan Eksekutif Keseluruhan: Keaktifan Guru, Monitoring Rombel Kelas, & Analytics Pembelajaran Virtual SMK Muthia Harapan Cicalengka.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>index.php?url=kepsek/cetakLaporan&type=guru" target="_blank" class="btn btn-outline-primary shadow-sm fw-bold">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Cetak Laporan Guru PDF
                </a>
                <a href="<?= BASE_URL ?>index.php?url=kepsek/cetakLaporan&type=siswa" target="_blank" class="btn btn-primary shadow-sm fw-bold">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Cetak Laporan Siswa PDF
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards (100% Real Database Stats) -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-primary shadow-sm text-white p-3 rounded-4 position-relative overflow-hidden">
                    <div class="fw-bold small text-uppercase opacity-75">Guru & Produktivitas</div>
                    <div class="display-6 fw-bold my-1"><?= $stats['total_guru'] ?> Guru</div>
                    <small><?= $stats['total_materi'] ?> Modul & <?= $stats['total_tugas'] ?> Tugas Terbit</small>
                    <i class="bi bi-person-badge icon opacity-25 position-absolute end-0 bottom-0 me-3 mb-2 fs-1"></i>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-success shadow-sm text-white p-3 rounded-4 position-relative overflow-hidden">
                    <div class="fw-bold small text-uppercase opacity-75">Siswa & Rombel Kelas</div>
                    <div class="display-6 fw-bold my-1"><?= $stats['total_siswa'] ?> Siswa</div>
                    <small>Tersebar di <?= $stats['total_kelas'] ?> Rombel Virtual</small>
                    <i class="bi bi-people icon opacity-25 position-absolute end-0 bottom-0 me-3 mb-2 fs-1"></i>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-warning text-dark shadow-sm p-3 rounded-4 position-relative overflow-hidden">
                    <div class="fw-bold small text-uppercase opacity-75">Rata-Rata Nilai Akhir</div>
                    <div class="display-6 fw-bold my-1"><?= $stats['avg_score'] > 0 ? number_format($stats['avg_score'], 1) : '0.0' ?></div>
                    <small><?= $stats['avg_score'] > 0 ? 'Capaian Leger E-Rapor & CBT' : 'Belum Ada Data Nilai' ?></small>
                    <i class="bi bi-award icon opacity-25 position-absolute end-0 bottom-0 me-3 mb-2 fs-1"></i>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-info text-white shadow-sm p-3 rounded-4 position-relative overflow-hidden">
                    <div class="fw-bold small text-uppercase opacity-75">Tingkat Kehadiran KBM</div>
                    <div class="display-6 fw-bold my-1"><?= $stats['attendance_rate'] > 0 ? number_format($stats['attendance_rate'], 1) . '%' : '0.0%' ?></div>
                    <small><?= $stats['attendance_rate'] > 0 ? 'Presensi Harian Siswa' : 'Belum Ada Data Presensi' ?></small>
                    <i class="bi bi-check-circle icon opacity-25 position-absolute end-0 bottom-0 me-3 mb-2 fs-1"></i>
                </div>
            </div>
        </div>

        <!-- Real Dynamic Charts Section -->
        <div class="row g-4 mb-4">
            <!-- Chart 1: Keaktifan Guru dalam Memberikan Tugas & Materi -->
            <div class="col-12 col-lg-7">
                <div class="card card-custom p-4 h-100 shadow-sm border-0 rounded-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Keaktifan Guru (Modul Materi, Tugas & Kuis CBT)</h6>
                            <small class="text-muted">Monitoring produktivitas pemberian tugas & materi per Guru</small>
                        </div>
                        <span class="badge bg-primary-subtle text-primary">Monitoring Pengajar</span>
                    </div>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartKeaktifanGuru"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart 2: Sebaran Siswa Per Jurusan -->
            <div class="col-12 col-lg-5">
                <div class="card card-custom p-4 h-100 shadow-sm border-0 rounded-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-pie-chart-fill text-success me-2"></i>Sebaran Siswa Per Program Keahlian</h6>
                            <small class="text-muted">Distribusi jumlah siswa aktif per Jurusan</small>
                        </div>
                        <span class="badge bg-success-subtle text-success">Komposisi Siswa</span>
                    </div>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartJurusan"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 3: Perbandingan Performa Nilai & Kehadiran Per Rombel Kelas -->
        <div class="card card-custom p-4 mb-4 shadow-sm border-0 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-graph-up-arrow text-warning me-2"></i>Performa Akademik & Persentase Kehadiran Per Rombel Kelas</h6>
                    <small class="text-muted">Perbandingan rata-rata E-Rapor dan tingkat kehadiran presensi per-kelas</small>
                </div>
                <span class="badge bg-warning-subtle text-dark">Rombel Analytics</span>
            </div>
            <div style="position: relative; height: 280px;">
                <canvas id="chartRombelAkademik"></canvas>
            </div>
        </div>

        <!-- Executive Supervision Tables Section -->
        <div class="row g-4 mb-4">
            <!-- Monitoring Keaktifan Guru -->
            <div class="col-12 col-lg-6">
                <div class="card card-custom p-4 h-100 shadow-sm border-0 rounded-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-person-workspace text-primary me-2"></i>Keaktifan Pengungsian & Tugas Guru</h6>
                        <a href="<?= BASE_URL ?>index.php?url=kepsek/monitoringGuru" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold">Detail Semua <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Guru</th>
                                    <th class="text-center">Modul</th>
                                    <th class="text-center">Tugas</th>
                                    <th class="text-center">Kuis CBT</th>
                                    <th class="text-center">Keaktifan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($stats['guru_keaktifan'])): ?>
                                    <tr><td colspan="6" class="text-center py-3 text-muted">Belum ada data pengajar terdaftar.</td></tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($stats['guru_keaktifan'], 0, 5) as $idx => $gk): 
                                        $tot = (int)$gk['total_aktivitas'];
                                        $badgeAktif = ($tot >= 5) ? 'bg-success' : (($tot >= 2) ? 'bg-primary' : 'bg-warning text-dark');
                                        $labelAktif = ($tot >= 5) ? 'Sangat Rajin' : (($tot >= 2) ? 'Aktif' : 'Perlu Ditingkatkan');
                                    ?>
                                        <tr>
                                            <td><?= $idx + 1 ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($gk['nama_lengkap']) ?></td>
                                            <td class="text-center"><span class="badge bg-info text-dark"><?= $gk['total_materi'] ?></span></td>
                                            <td class="text-center"><span class="badge bg-warning text-dark"><?= $gk['total_tugas'] ?></span></td>
                                            <td class="text-center"><span class="badge bg-success"><?= $gk['total_quiz'] ?></span></td>
                                            <td class="text-center"><span class="badge <?= $badgeAktif ?>"><?= $labelAktif ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Monitoring Rombel Kelas & Keaktifan Siswa Per Kelas -->
            <div class="col-12 col-lg-6">
                <div class="card card-custom p-4 h-100 shadow-sm border-0 rounded-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bounding-box-circles text-success me-2"></i>Keaktifan Siswa Per Rombel Kelas</h6>
                        <a href="<?= BASE_URL ?>index.php?url=kepsek/monitoringPembelajaran" class="btn btn-sm btn-link text-success text-decoration-none fw-bold">Detail Semua <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Rombel</th>
                                    <th>Wali Kelas</th>
                                    <th class="text-center">Siswa</th>
                                    <th class="text-center">Kehadiran</th>
                                    <th class="text-center">Nilai Rapor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($stats['rekap_rombel'])): ?>
                                    <tr><td colspan="6" class="text-center py-3 text-muted">Belum ada data rombel kelas.</td></tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($stats['rekap_rombel'], 0, 5) as $idx => $rb): 
                                        $attPct = (float)($rb['pct_kehadiran'] ?? 0);
                                        $avgNil = (float)($rb['avg_nilai'] ?? 0);
                                    ?>
                                        <tr>
                                            <td><?= $idx + 1 ?></td>
                                            <td>
                                                <div class="fw-bold text-primary"><?= htmlspecialchars($rb['nama_kelas']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($rb['nama_jurusan'] ?? '-') ?></small>
                                            </td>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($rb['nama_walikelas'] ?? 'Belum Ditentukan') ?></td>
                                            <td class="text-center"><span class="badge bg-secondary"><?= $rb['total_siswa'] ?> Siswa</span></td>
                                            <td class="text-center">
                                                <span class="badge <?= $attPct > 0 ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $attPct > 0 ? number_format($attPct, 1) . '%' : 'Belum Absensi' ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($avgNil > 0): ?>
                                                    <span class="fw-bold text-primary fs-6"><?= number_format($avgNil, 1) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Belum Ada Nilai</span>
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
        </div>

    </div>
</main>

<!-- Chart.js Integration with Real Database Data -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // 1. Chart Keaktifan Guru dalam Memberikan Tugas & Materi (Grouped Bar Chart)
    const guruData = <?= json_encode($stats['guru_keaktifan']) ?>;
    const guruNames = guruData.map(g => g.nama_lengkap.split(' ')[0] + ' ' + (g.nama_lengkap.split(' ')[1] || ''));
    const guruMateri = guruData.map(g => parseInt(g.total_materi));
    const guruTugas = guruData.map(g => parseInt(g.total_tugas));
    const guruQuiz = guruData.map(g => parseInt(g.total_quiz));

    const ctxGuru = document.getElementById('chartKeaktifanGuru');
    if (ctxGuru) {
        new Chart(ctxGuru.getContext('2d'), {
            type: 'bar',
            data: {
                labels: guruNames.length ? guruNames : ['Guru 1', 'Guru 2'],
                datasets: [
                    { label: 'Modul Materi', data: guruMateri.length ? guruMateri : [2, 4], backgroundColor: '#0D6EFD', borderRadius: 4 },
                    { label: 'Tugas Terbit', data: guruTugas.length ? guruTugas : [3, 2], backgroundColor: '#FFC107', borderRadius: 4 },
                    { label: 'Kuis CBT', data: guruQuiz.length ? guruQuiz : [1, 2], backgroundColor: '#198754', borderRadius: 4 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    }

    // 2. Chart Sebaran Siswa Per Jurusan (Doughnut Chart)
    const jurusanLabels = <?= json_encode(array_column($stats['jurusan_stats'], 'nama_jurusan')) ?>;
    const jurusanData = <?= json_encode(array_map('intval', array_column($stats['jurusan_stats'], 'total_siswa'))) ?>;

    const ctxJurusan = document.getElementById('chartJurusan');
    if (ctxJurusan) {
        new Chart(ctxJurusan.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: jurusanLabels.length ? jurusanLabels : ['RPL', 'TKJ', 'DKV'],
                datasets: [{
                    data: jurusanData.length ? jurusanData : [1, 1, 1],
                    backgroundColor: ['#0D6EFD', '#198754', '#FFC107', '#DC3545', '#0DCAF0', '#6C757D']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // 3. Chart Performa Akademik & Kehadiran Per Rombel Kelas (Bar & Line Chart)
    const rombelData = <?= json_encode($stats['rekap_rombel']) ?>;
    const rombelNames = rombelData.map(r => r.nama_kelas);
    const rombelNilai = rombelData.map(r => parseFloat(r.avg_nilai));
    const rombelAtt = rombelData.map(r => parseFloat(r.pct_kehadiran));

    const ctxRombel = document.getElementById('chartRombelAkademik');
    if (ctxRombel) {
        new Chart(ctxRombel.getContext('2d'), {
            type: 'bar',
            data: {
                labels: rombelNames.length ? rombelNames : ['X RPL 1', 'XI TKJ 1'],
                datasets: [
                    {
                        label: 'Rata-Rata E-Rapor',
                        data: rombelNilai.length ? rombelNilai : [80, 85],
                        backgroundColor: '#0D6EFD',
                        borderRadius: 6
                    },
                    {
                        label: '% Kehadiran Presensi',
                        data: rombelAtt.length ? rombelAtt : [95, 98],
                        backgroundColor: '#198754',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: false, min: 40, max: 100 }
                },
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
