<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
// Calculate current day in Indonesian for default Schedule view
$daysMap = [
    'Sunday'    => 'Minggu',
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu'
];
$currentDayEng = date('l');
$hariIniIndo = $daysMap[$currentDayEng] ?? 'Senin';

// Check how many schedule items match today
$todayJadwalCount = 0;
if (!empty($jadwalList)) {
    foreach ($jadwalList as $jCheck) {
        if (strcasecmp($jCheck['hari'], $hariIniIndo) === 0) {
            $todayJadwalCount++;
        }
    }
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.siswa-dashboard-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
    background-color: #f8fafc;
    min-height: 100vh;
}

.siswa-hero-card {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #0f172a 100%);
    border-radius: 1.25rem;
    color: #ffffff;
    box-shadow: 0 12px 30px -5px rgba(2, 132, 199, 0.25);
    position: relative;
    overflow: hidden;
}
.siswa-hero-card::after {
    content: "";
    position: absolute;
    top: -40%;
    right: -10%;
    width: 300px;
    height: 300px;
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
    height: 100%;
}
.stat-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.08);
}

/* Day Switcher Filter Buttons */
.day-tab-btn {
    border: 1px solid #cbd5e1;
    background-color: #ffffff;
    color: #475569;
    font-weight: 700;
    font-size: 0.78rem;
    padding: 5px 14px;
    border-radius: 50rem;
    transition: all 0.2s ease;
    cursor: pointer;
    white-space: nowrap;
}
.day-tab-btn:hover {
    background-color: #f1f5f9;
    color: #0f172a;
}
.day-tab-btn.active {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff !important;
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}

.hover-scale {
    transition: transform 0.2s ease;
}
.hover-scale:hover {
    transform: scale(1.02);
}
</style>

<!-- Top Clearance for Fixed Navbar -->
<main class="main-content px-3 px-md-4 siswa-dashboard-wrapper pt-4 mt-4 mt-md-5 pb-5">
<div class="container-fluid max-width-1400 pt-2">

    <!-- Executive Student Welcome Hero Card -->
    <div class="siswa-hero-card p-4 p-md-5 mb-4 shadow-lg">
        <div class="row align-items-center g-4 relative-zIndex-1">
            <div class="col-12 col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <!-- Title Badge: Black/Dark High-Contrast Text on Solid White -->
                    <span class="badge bg-white text-dark px-3.5 py-2 rounded-pill fw-extrabold text-uppercase shadow-sm" style="font-size: 0.78rem; letter-spacing: 0.2px;">
                        <i class="bi bi-person-workspace text-primary me-1.5 fs-6"></i> Portal Pembelajaran Digital Siswa
                    </span>
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold shadow-xs" style="font-size: 0.78rem;">
                        <i class="bi bi-calendar-event me-1"></i> T.A. <?= htmlspecialchars($activeTa['tahun_ajaran'] ?? '2025/2026') ?> — Semester <?= htmlspecialchars($activeTa['semester'] ?? 'Ganjil') ?>
                    </span>
                </div>
                <h3 class="fw-extrabold mb-2 text-white" style="letter-spacing: -0.5px;">Selamat Datang, <?= htmlspecialchars($user['full_name']) ?>!</h3>
                <p class="text-white text-opacity-90 mb-0 leading-relaxed small" style="max-width: 650px;">
                    Rombel Kelas Utama: <strong class="text-white border-bottom border-white border-opacity-50 pb-0.5"><?= htmlspecialchars($siswaProfile['nama_kelas'] ?? 'Rombel Kelas') ?></strong> 
                    &nbsp;|&nbsp; Jurusan: <strong class="text-white border-bottom border-white border-opacity-50 pb-0.5"><?= htmlspecialchars($siswaProfile['nama_jurusan'] ?? 'Kejuruan') ?></strong>
                </p>
            </div>
            
            <div class="col-12 col-lg-4 text-lg-end">
                <a href="<?= BASE_URL ?>index.php?url=siswa/kartuPelajar" class="btn btn-warning text-dark fw-bold px-4 py-2.5 rounded-pill shadow-lg text-nowrap w-100 w-sm-auto hover-scale" style="font-size: 0.88rem;">
                    <i class="bi bi-credit-card-2-front-fill me-1.5 fs-5"></i> Kartu Pelajar Digital
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
                        <span class="text-muted small fw-bold d-block text-uppercase mb-1" style="font-size: 0.72rem;">Materi Siap Dibaca</span>
                        <h3 class="fw-extrabold mb-0 text-primary"><?= count($materiList) ?></h3>
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
                        <span class="text-muted small fw-bold d-block text-uppercase mb-1" style="font-size: 0.72rem;">Tugas Aktif KBM</span>
                        <h3 class="fw-extrabold mb-0 text-warning"><?= count($tugasList) ?></h3>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                        <i class="bi bi-card-checklist"></i>
                    </div>
                </div>
                <small class="text-muted d-block mt-2" style="font-size:0.78rem;"><i class="bi bi-clock-history me-1 text-warning"></i>Perlu Dikumpulkan ke Guru</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-kpi-card border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-bold d-block text-uppercase mb-1" style="font-size: 0.72rem;">Kuis & Ujian CBT</span>
                        <h3 class="fw-extrabold mb-0 text-success"><?= count($quizList) ?></h3>
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
                        <span class="text-muted small fw-bold d-block text-uppercase mb-1" style="font-size: 0.72rem;">Presensi Log Real</span>
                        <h3 class="fw-extrabold mb-0 text-info"><?= htmlspecialchars($certStats['presensi_log'] ?? '0%') ?></h3>
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
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-2.5 border-bottom">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2" style="letter-spacing: -0.3px;">
                                <i class="bi bi-bar-chart-line-fill text-primary fs-4"></i>
                                <span>Grafik Evaluasi & Rerata Nilai Real Per-Mapel</span>
                            </h5>
                            <small class="text-muted">Rerata nilai otomatis dari hasil tugas, kuis CBT, dan evaluasi KBM yang telah dinilai Guru.</small>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-bold" style="font-size: 0.75rem;">
                            <i class="bi bi-graph-up me-1"></i>Real Evaluasi Database
                        </span>
                    </div>

                    <!-- Canvas Chart Always Rendered -->
                    <div style="position: relative; height: 270px; width: 100%;">
                        <canvas id="siswaPerformanceChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Countdown Tenggat Tugas Nearest -->
            <?php if (!empty($tugasList)): ?>
            <div class="card border-0 p-4 mb-4 border-start border-4 border-warning shadow-sm bg-white rounded-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <span class="badge bg-warning text-dark mb-1 fw-bold" style="font-size:0.72rem;"><i class="bi bi-clock-history me-1"></i>TENGGAT PENUGASAN TERDEKAT</span>
                        <h6 class="fw-bold mb-1 text-dark fs-5"><?= htmlspecialchars($tugasList[0]['judul']) ?></h6>
                        <small class="text-muted">Batas Pengumpulan: <strong class="text-danger"><?= date('d M Y, H:i', strtotime($tugasList[0]['deadline'])) ?> WIB</strong></small>
                    </div>
                    <a href="<?= BASE_URL ?>index.php?url=siswa/tugas" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-pill shadow-xs hover-scale" style="font-size:0.85rem;">
                        <i class="bi bi-pencil-square me-1"></i> Kerjakan Sekarang
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- 📅 JADWAL PELAJARAN KBM ROMBEL (TODAY DEFAULT & DAY SWITCHER) -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-2.5 border-bottom">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2" style="letter-spacing: -0.3px;">
                                <i class="bi bi-calendar3 text-primary fs-4"></i>
                                <span>Jadwal Pelajaran KBM Rombel Hari Ini</span>
                            </h5>
                            <small class="text-muted">Menampilkan agenda pelajaran KBM aktif untuk rombel Anda secara realtime.</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white px-3 py-2 rounded-pill fw-bold" style="font-size: 0.78rem;">
                                <i class="bi bi-calendar-check me-1"></i><?= $hariIniIndo ?>, <?= date('d F Y') ?>
                            </span>
                        </div>
                    </div>

                    <!-- Day Filter Buttons Strip -->
                    <div class="d-flex align-items-center gap-1.5 overflow-x-auto mb-3 pb-1" style="scrollbar-width: thin;">
                        <button type="button" class="day-tab-btn active" onclick="filterJadwalByDay('<?= $hariIniIndo ?>', this)">
                            <i class="bi bi-star-fill text-warning me-1"></i> Hari Ini (<?= $hariIniIndo ?>)
                        </button>
                        <button type="button" class="day-tab-btn" onclick="filterJadwalByDay('Senin', this)">Senin</button>
                        <button type="button" class="day-tab-btn" onclick="filterJadwalByDay('Selasa', this)">Selasa</button>
                        <button type="button" class="day-tab-btn" onclick="filterJadwalByDay('Rabu', this)">Rabu</button>
                        <button type="button" class="day-tab-btn" onclick="filterJadwalByDay('Kamis', this)">Kamis</button>
                        <button type="button" class="day-tab-btn" onclick="filterJadwalByDay('Jumat', this)">Jumat</button>
                        <button type="button" class="day-tab-btn" onclick="filterJadwalByDay('Sabtu', this)">Sabtu</button>
                        <button type="button" class="day-tab-btn" onclick="filterJadwalByDay('all', this)">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i> Semua Jadwal
                        </button>
                    </div>

                    <!-- Notice if no KBM scheduled for today -->
                    <div id="noJadwalTodayNotice" class="alert alert-info rounded-3 mb-3 p-3 border-0 <?= $todayJadwalCount > 0 ? 'd-none' : '' ?>">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill fs-5 text-info"></i>
                            <span class="small fw-semibold text-dark">
                                Tidak ada agenda jadwal KBM untuk hari ini (<strong><?= $hariIniIndo ?></strong>). Klik tab hari lain di atas atau 'Semua Jadwal' untuk melihat jadwal mingguan.
                            </span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="jadwalTable">
                            <thead class="table-light">
                                <tr style="font-size: 0.83rem;">
                                    <th style="width: 15%;">Hari</th>
                                    <th style="width: 25%;">Jam KBM</th>
                                    <th style="width: 30%;">Mata Pelajaran</th>
                                    <th style="width: 20%;">Guru Pengampu</th>
                                    <th style="width: 10%;">Ruangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($jadwalList)): ?>
                                    <tr id="emptyJadwalRow"><td colspan="5" class="text-center py-4 text-muted small">Belum ada jadwal pelajaran terdaftar untuk rombel Anda.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($jadwalList as $j): ?>
                                        <tr class="jadwal-row-item" data-hari="<?= htmlspecialchars($j['hari']) ?>">
                                            <td><span class="badge bg-primary px-2.5 py-1.5 rounded-pill fw-bold" style="font-size: 0.75rem;"><?= htmlspecialchars($j['hari']) ?></span></td>
                                            <td class="fw-semibold small text-dark"><i class="bi bi-clock text-primary me-1"></i><?= substr($j['jam_mulai'],0,5) ?> - <?= substr($j['jam_selesai'],0,5) ?> WIB</td>
                                            <td class="fw-bold text-dark" style="font-size:0.88rem;"><?= htmlspecialchars($j['nama_mapel']) ?></td>
                                            <td class="small text-secondary"><i class="bi bi-person-circle me-1 text-primary"></i><?= htmlspecialchars($j['nama_guru']) ?></td>
                                            <td><span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;"><?= htmlspecialchars($j['ruangan'] ?? 'Ruang KBM') ?></span></td>
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
            <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-success bg-white">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-award-fill text-warning fs-5"></i>
                        <span>Evaluasi & Predikat Belajar Real</span>
                    </h6>

                    <div class="p-3 bg-light rounded-4 border mb-3 text-center">
                        <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size:0.72rem;">Predikat Hasil Belajar</div>
                        <div class="fs-4 fw-extrabold text-primary mb-1"><?= htmlspecialchars($certStats['predikat'] ?? 'Belum Ada Data') ?></div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fw-bold rounded-pill" style="font-size:0.75rem;">
                            Rerata LMS: <?= htmlspecialchars($certStats['evaluasi_lms'] ?? '0.0') ?>
                        </span>
                    </div>

                    <a href="<?= BASE_URL ?>index.php?url=siswa/sertifikat" class="btn btn-outline-success w-100 fw-bold rounded-pill py-2 text-nowrap hover-scale" style="font-size: 0.85rem;">
                        <i class="bi bi-patch-check me-1"></i> Lihat Sertifikat Digital
                    </a>
                </div>
            </div>

            <!-- Pengumuman Resmi Sekolah -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-megaphone-fill text-danger fs-5"></i>
                        <span>Pengumuman Resmi Sekolah</span>
                    </h6>

                    <?php if (empty($pengumumanList)): ?>
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-chat-left-dots fs-3 d-block mb-1 text-secondary"></i>
                            Belum ada pengumuman terbaru dari Sekolah.
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-2.5">
                            <?php foreach ($pengumumanList as $p): ?>
                                <div class="p-3 bg-light rounded-3 border-start border-3 border-danger shadow-xs">
                                    <h6 class="fw-bold text-primary mb-1 fs-6"><?= htmlspecialchars($p['judul']) ?></h6>
                                    <p class="small text-secondary mb-0 lh-base" style="font-size:0.82rem;"><?= htmlspecialchars($p['isi']) ?></p>
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Initialize Chart.js Performance Chart
    const canvasElem = document.getElementById('siswaPerformanceChart');
    if (canvasElem) {
        const ctx = canvasElem.getContext('2d');
        const chartRawData = <?= json_encode($chartData ?? []) ?>;

        const mapelLabels = chartRawData.map(item => item.nama_mapel);
        const nilaiData = chartRawData.map(item => parseFloat(item.avg_nilai) || 0);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: mapelLabels,
                datasets: [{
                    label: 'Rerata Nilai Real (0-100)',
                    data: nilaiData,
                    backgroundColor: 'rgba(37, 99, 235, 0.8)',
                    borderColor: '#2563eb',
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBackgroundColor: '#1d4ed8'
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
                            stepSize: 20,
                            font: { family: 'Plus Jakarta Sans', size: 11 }
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' } },
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Rerata Score: ' + context.raw + ' / 100';
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Default Schedule Filtering to Today
    filterJadwalByDay('<?= $hariIniIndo ?>', null);
});

/* Day Selector Filter Function for Schedule */
function filterJadwalByDay(dayName, btnElem) {
    // Update active tab styling
    if (btnElem) {
        document.querySelectorAll('.day-tab-btn').forEach(btn => btn.classList.remove('active'));
        btnElem.classList.add('active');
    }

    const rows = document.querySelectorAll('.jadwal-row-item');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowHari = row.getAttribute('data-hari') || '';
        if (dayName === 'all' || rowHari.toLowerCase() === dayName.toLowerCase()) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Handle notice box for today's filter
    const notice = document.getElementById('noJadwalTodayNotice');
    if (notice) {
        if (dayName.toLowerCase() === '<?= strtolower($hariIniIndo) ?>' && visibleCount === 0) {
            notice.classList.remove('d-none');
        } else {
            notice.classList.add('d-none');
        }
    }
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
