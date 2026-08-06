<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.siswa-hero-card {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #0f172a 100%);
    border-radius: 1.25rem;
    color: #ffffff;
    box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.3);
    position: relative;
    overflow: hidden;
}
.siswa-hero-card::after {
    content: "";
    position: absolute;
    top: -40%;
    right: -10%;
    width: 280px;
    height: 280px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.stat-kpi-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
    transition: all 0.25s ease;
}
.stat-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.08);
}
</style>

<main class="main-content px-3 px-md-4 py-4">
<div class="container-fluid pt-3 pt-md-4">

    <!-- Executive Student Welcome Hero Card -->
    <div class="siswa-hero-card p-4 p-md-5 mb-4 mt-4 mt-md-5 shadow-lg">
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge bg-white bg-opacity-20 text-white px-3 py-1.5 rounded-pill fw-bold text-uppercase" style="font-size: 0.75rem;">
                        <i class="bi bi-person-workspace me-1"></i> Portal Pembelajaran Digital Siswa
                    </span>
                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold shadow-xs" style="font-size: 0.75rem;">
                        <i class="bi bi-calendar-event me-1"></i> Periode: T.A. <?= htmlspecialchars($activeTa['tahun_ajaran'] ?? '2025/2026') ?> — Semester <?= htmlspecialchars($activeTa['semester'] ?? 'Ganjil') ?>
                    </span>
                </div>
                <h3 class="fw-bold mb-2 text-white">Selamat Datang, <?= htmlspecialchars($user['full_name']) ?>!</h3>
                <p class="text-white-50 mb-0 leading-relaxed" style="max-width: 650px;">
                    Rombel Kelas Utama: <strong class="text-white"><?= htmlspecialchars($siswaProfile['nama_kelas'] ?? 'X RPL 1') ?></strong> 
                    &nbsp;|&nbsp; Jurusan: <strong class="text-white"><?= htmlspecialchars($siswaProfile['nama_jurusan'] ?? 'Rekayasa Perangkat Lunak') ?></strong>
                </p>
            </div>
            
            <div class="col-12 col-lg-4 text-lg-end">
                <a href="<?= BASE_URL ?>index.php?url=siswa/kartuPelajar" class="btn btn-warning text-dark fw-bold px-4 py-2.5 rounded-3 shadow-sm text-nowrap w-100 w-sm-auto">
                    <i class="bi bi-credit-card-2-front-fill me-1.5"></i> Kartu Pelajar Digital
                </a>
            </div>
        </div>
    </div>

    <!-- 4 Real Database KPI Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-kpi-card border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold d-block text-uppercase mb-1">Materi Siap Dibaca</span>
                        <h3 class="fw-bold mb-0 text-primary"><?= count($materiList) ?></h3>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                        <i class="bi bi-book-fill"></i>
                    </div>
                </div>
                <small class="text-muted d-block mt-2" style="font-size:0.78rem;"><i class="bi bi-check-circle me-1 text-primary"></i>Tersedia untuk Rombel Anda</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-kpi-card border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold d-block text-uppercase mb-1">Tugas Aktif KBM</span>
                        <h3 class="fw-bold mb-0 text-warning"><?= count($tugasList) ?></h3>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                        <i class="bi bi-card-checklist"></i>
                    </div>
                </div>
                <small class="text-muted d-block mt-2" style="font-size:0.78rem;"><i class="bi bi-clock-history me-1 text-warning"></i>Perlu Dikumpulkan Guru</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-kpi-card border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold d-block text-uppercase mb-1">Kuis & Ujian CBT</span>
                        <h3 class="fw-bold mb-0 text-success"><?= count($quizList) ?></h3>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                </div>
                <small class="text-muted d-block mt-2" style="font-size:0.78rem;"><i class="bi bi-shield-check me-1 text-success"></i>Evaluasi CBT Sekolah</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-kpi-card border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold d-block text-uppercase mb-1">Presensi Log Real</span>
                        <h3 class="fw-bold mb-0 text-info"><?= htmlspecialchars($certStats['presensi_log'] ?? '0%') ?></h3>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                </div>
                <small class="text-muted d-block mt-2" style="font-size:0.78rem;"><i class="bi bi-database-check me-1 text-info"></i>Data Real Database</small>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="row g-4">
        
        <!-- Left Column (8 Columns) -->
        <div class="col-12 col-lg-8">

            <!-- Chart.js Real Database Student Performance -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-2 border-bottom">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-bar-chart-line-fill text-primary fs-4"></i>
                                <span>Grafik Evaluasi & Rerata Nilai Real Per-Mapel</span>
                            </h5>
                            <small class="text-muted">Dihitung otomatis dari nilai tugas & evaluasi yang telah diperiksa oleh Guru.</small>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold">
                            <i class="bi bi-graph-up me-1"></i>Real Database MySQL
                        </span>
                    </div>

                    <?php if (empty($chartData)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-graph-down fs-1 d-block mb-2 text-secondary"></i>
                            Belum ada rerata nilai evaluasi yang di-inputkan oleh Guru untuk grafik Anda.
                        </div>
                    <?php else: ?>
                        <div style="position: relative; height: 260px; width: 100%;">
                            <canvas id="siswaPerformanceChart"></canvas>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Countdown Tenggat Tugas Nearest -->
            <?php if (!empty($tugasList)): ?>
            <div class="card-custom p-4 mb-4 border-start border-4 border-warning shadow-sm bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <span class="badge bg-warning text-dark mb-1 fw-bold"><i class="bi bi-clock-history me-1"></i>TENGGAT PENUGASAN TERDEKAT</span>
                        <h6 class="fw-bold mb-0 text-dark fs-5"><?= htmlspecialchars($tugasList[0]['judul']) ?></h6>
                        <small class="text-muted">Batas Pengumpulan: <strong><?= date('d M Y, H:i', strtotime($tugasList[0]['deadline'])) ?> WIB</strong></small>
                    </div>
                    <a href="<?= BASE_URL ?>index.php?url=siswa/tugas" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-3 shadow-xs">
                        <i class="bi bi-pencil-square me-1"></i> Kerjakan Sekarang
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Jadwal Pelajaran Rombel Hari Ini -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-2 border-bottom">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-calendar3 text-primary fs-4"></i>
                            <span>Jadwal Pelajaran KBM Rombel Hari Ini</span>
                        </h5>
                        <span class="badge bg-primary px-3 py-2 rounded-pill fw-semibold"><?= date('l, d F Y') ?></span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Hari</th>
                                    <th>Jam KBM</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Guru Pengampu</th>
                                    <th>Ruangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($jadwalList)): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada jadwal pelajaran terdaftar untuk rombel Anda hari ini.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($jadwalList as $j): ?>
                                        <tr>
                                            <td><span class="badge bg-primary px-2.5 py-1.5"><?= htmlspecialchars($j['hari']) ?></span></td>
                                            <td class="fw-semibold small"><?= substr($j['jam_mulai'],0,5) ?> - <?= substr($j['jam_selesai'],0,5) ?> WIB</td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($j['nama_mapel']) ?></td>
                                            <td class="small"><i class="bi bi-person me-1 text-secondary"></i><?= htmlspecialchars($j['nama_guru']) ?></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($j['ruangan'] ?? 'Ruang KBM') ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column (4 Columns) -->
        <div class="col-12 col-lg-4">

            <!-- Real Database Rerata Nilai & Predikat Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-success">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-award-fill text-warning fs-5"></i>
                        <span>Evaluasi & Predikat Belajar Real</span>
                    </h6>

                    <div class="p-3 bg-light rounded-4 border mb-3 text-center">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Predikat Hasil Belajar</div>
                        <div class="fs-4 fw-bold text-primary mb-1"><?= htmlspecialchars($certStats['predikat'] ?? 'Belum Ada Data') ?></div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fw-bold">
                            Rerata LMS: <?= htmlspecialchars($certStats['evaluasi_lms'] ?? '0.0') ?>
                        </span>
                    </div>

                    <a href="<?= BASE_URL ?>index.php?url=siswa/sertifikat" class="btn btn-outline-success w-100 fw-bold rounded-3">
                        <i class="bi bi-patch-check me-1"></i> Lihat Sertifikat Digital
                    </a>
                </div>
            </div>

            <!-- Pengumuman Resmi Sekolah -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-megaphone-fill text-danger fs-5"></i>
                        <span>Pengumuman Resmi Sekolah</span>
                    </h6>

                    <?php if (empty($pengumumanList)): ?>
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-chat-left-dots fs-3 d-block mb-1 text-secondary"></i>
                            Belum ada pengumuman terbaru dari Admin / Sekolah.
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($pengumumanList as $p): ?>
                                <div class="p-3 bg-light rounded-3 border-start border-3 border-danger">
                                    <h6 class="fw-bold text-primary mb-1 fs-6"><?= htmlspecialchars($p['judul']) ?></h6>
                                    <p class="small text-muted mb-0 leading-relaxed"><?= htmlspecialchars($p['isi']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>

</div>
</main>

<?php if (!empty($chartData)): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('siswaPerformanceChart').getContext('2d');
    
    const mapelLabels = <?= json_encode(array_column($chartData, 'nama_mapel')) ?>;
    const nilaiData = <?= json_encode(array_column($chartData, 'avg_nilai')) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: mapelLabels,
            datasets: [{
                label: 'Rerata Nilai Real',
                data: nilaiData,
                backgroundColor: 'rgba(13, 110, 253, 0.75)',
                borderColor: '#0d6efd',
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        stepSize: 20
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
