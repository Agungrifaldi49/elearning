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
.siswa-hero-card {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #0f172a 100%);
    border-radius: 1rem;
    color: #ffffff;
    box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.25);
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
    border-radius: 0.85rem;
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
    font-weight: 600;
    font-size: 0.8rem;
    padding: 6px 14px;
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
    background: #0d6efd;
    color: #ffffff !important;
    border-color: #0d6efd;
    box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2);
}
</style>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <!-- Executive Student Welcome Hero Card -->
    <div class="siswa-hero-card p-4 p-md-5 mb-4 shadow-sm">
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <!-- Title Badge: Black/Dark High-Contrast Text on Solid White -->
                    <span class="badge bg-white text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase shadow-xs" style="font-size: 0.75rem;">
                        <i class="bi bi-person-workspace text-primary me-1.5"></i> Portal Pembelajaran Digital Siswa
                    </span>
                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold shadow-xs" style="font-size: 0.75rem;">
                        <i class="bi bi-calendar-event me-1"></i> T.A. <?= htmlspecialchars($activeTa['tahun_ajaran'] ?? '2025/2026') ?> — Semester <?= htmlspecialchars($activeTa['semester'] ?? 'Ganjil') ?>
                    </span>
                </div>
                <h3 class="fw-bold mb-2 text-white">Selamat Datang, <?= htmlspecialchars($user['full_name']) ?>!</h3>
                <p class="text-white text-opacity-90 mb-0 small" style="max-width: 650px;">
                    Rombel Kelas Utama: <strong class="text-white"><?= htmlspecialchars($siswaProfile['nama_kelas'] ?? 'Rombel Kelas') ?></strong> 
                    &nbsp;|&nbsp; Jurusan: <strong class="text-white"><?= htmlspecialchars($siswaProfile['nama_jurusan'] ?? 'Kejuruan') ?></strong>
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
                        <span class="text-muted small fw-bold d-block text-uppercase mb-1" style="font-size: 0.72rem;">Materi Siap Dibaca</span>
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
                        <span class="text-muted small fw-bold d-block text-uppercase mb-1" style="font-size: 0.72rem;">Tugas Aktif KBM</span>
                        <h3 class="fw-bold mb-0 text-warning"><?= count($tugasList) ?></h3>
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
                        <span class="text-muted small fw-bold d-block text-uppercase mb-1" style="font-size: 0.72rem;">Presensi Log Real</span>
                        <h3 class="fw-bold mb-0 text-info"><?= htmlspecialchars($certStats['presensi_log'] ?? '0%') ?></h3>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1.5 mt-2 flex-wrap" style="font-size: 0.72rem;">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5 rounded-pill fw-bold">H: <?= (int)($certStats['total_hadir'] ?? 0) ?></span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1.5 py-0.5 rounded-pill fw-bold">I: <?= (int)($certStats['total_izin'] ?? 0) ?></span>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-1.5 py-0.5 rounded-pill fw-bold">S: <?= (int)($certStats['total_sakit'] ?? 0) ?></span>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1.5 py-0.5 rounded-pill fw-bold">A: <?= (int)($certStats['total_alpa'] ?? 0) ?></span>
                </div>
                <small class="text-muted d-block mt-1.5" style="font-size:0.75rem;"><i class="bi bi-database-check me-1 text-info"></i><?= (int)($certStats['total_absensi'] ?? 0) ?> Catatan Terverifikasi</small>
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
                            <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
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
                    <a href="<?= BASE_URL ?>index.php?url=siswa/tugas" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-3 shadow-xs" style="font-size:0.85rem;">
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
                            <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
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

            <!-- REAL DATABASE EVALUASI & PREDIKAT BELAJAR CARD -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden border-start border-4 <?= $certStats['border_class'] ?? 'border-success' ?>">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-award-fill text-warning fs-5"></i>
                            <span>Evaluasi & Predikat Belajar Real</span>
                        </h6>
                        <span class="badge bg-slate-100 text-slate-700 border border-slate-200 px-2.5 py-1 rounded-pill small fw-bold" style="font-size:0.7rem;">
                            <i class="bi bi-shield-check text-success me-1"></i>Akademik Terverifikasi
                        </span>
                    </div>

                    <!-- Hero Grade & Score Banner -->
                    <div class="p-3.5 rounded-4 mb-3 text-center position-relative overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1.5px solid #e2e8f0;">
                        <div class="d-flex justify-content-center align-items-baseline gap-2 mb-1">
                            <span class="badge <?= $certStats['predikat_class'] ?? 'bg-success text-white' ?> px-3 py-1.5 rounded-pill fw-black fs-5 shadow-xs" style="letter-spacing: 0.5px;">
                                Predikat <?= htmlspecialchars($certStats['predikat_grade'] ?? 'D') ?>
                            </span>
                        </div>
                        <div class="fw-bold text-slate-800 mb-1" style="font-size: 1rem;">
                            <?= htmlspecialchars($certStats['predikat_label'] ?? 'Belum Ada Data') ?>
                        </div>

                        <div class="d-flex justify-content-center align-items-center gap-2 mb-2 flex-wrap">
                            <span class="badge bg-white text-dark border px-2.5 py-1 rounded-pill fw-bold small shadow-2xs">
                                <i class="bi bi-graph-up text-primary me-1"></i>Rerata Nilai: <strong class="text-primary"><?= number_format($certStats['evaluasi_nilai'] ?? 0, 1) ?></strong> / 100
                            </span>
                            <?php if (!empty($certStats['is_tuntas'])): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-bold small">
                                    <i class="bi bi-check-circle-fill me-1"></i>Tuntas KKM (75)
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 rounded-pill fw-bold small">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>Perlu Bimbingan (< 75)
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- KKM Progress Bar Indicator -->
                        <div class="w-100 px-2 mb-1">
                            <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 0.7rem;">
                                <span>Capaian KKM (Standar: 75)</span>
                                <span class="fw-bold"><?= number_format($certStats['evaluasi_nilai'] ?? 0, 1) ?>%</span>
                            </div>
                            <div class="progress" style="height: 7px; background-color: #e2e8f0; border-radius: 10px;">
                                <div class="progress-bar <?= !empty($certStats['is_tuntas']) ? 'bg-success' : 'bg-warning' ?>" 
                                     role="progressbar" 
                                     style="width: <?= min(100, max(0, $certStats['evaluasi_nilai'] ?? 0)) ?>%; border-radius: 10px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- 3 Academic Evaluation Pillars Breakdown -->
                    <div class="row g-2 mb-3 text-center">
                        <div class="col-4">
                            <div class="p-2 rounded-3 border bg-slate-50 text-start" style="background-color: #f8fafc;">
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <i class="bi bi-card-checklist text-warning" style="font-size: 0.8rem;"></i>
                                    <small class="text-muted fw-bold" style="font-size:0.68rem;">TUGAS</small>
                                </div>
                                <div class="fw-bold text-dark fs-6 lh-1"><?= $certStats['avg_tugas'] !== null ? number_format($certStats['avg_tugas'], 1) : '-' ?></div>
                                <small class="text-muted" style="font-size:0.65rem;"><?= (int)($certStats['total_tugas'] ?? 0) ?> Dinilai</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3 border bg-slate-50 text-start" style="background-color: #f8fafc;">
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <i class="bi bi-laptop text-primary" style="font-size: 0.8rem;"></i>
                                    <small class="text-muted fw-bold" style="font-size:0.68rem;">KUIS CBT</small>
                                </div>
                                <div class="fw-bold text-dark fs-6 lh-1"><?= $certStats['avg_quiz'] !== null ? number_format($certStats['avg_quiz'], 1) : '-' ?></div>
                                <small class="text-muted" style="font-size:0.65rem;"><?= (int)($certStats['total_quiz_lulus'] ?? 0) ?>/<?= (int)($certStats['total_quiz'] ?? 0) ?> Lulus</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3 border bg-slate-50 text-start" style="background-color: #f8fafc;">
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <i class="bi bi-journal-bookmark-fill text-success" style="font-size: 0.8rem;"></i>
                                    <small class="text-muted fw-bold" style="font-size:0.68rem;">E-RAPOR</small>
                                </div>
                                <div class="fw-bold text-dark fs-6 lh-1"><?= $certStats['avg_rapor'] !== null ? number_format($certStats['avg_rapor'], 1) : '-' ?></div>
                                <small class="text-muted" style="font-size:0.65rem;"><?= (int)($certStats['total_mapel_rapor'] ?? 0) ?> Mapel</small>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Navigation Actions -->
                    <div class="d-flex flex-column gap-2">
                        <a href="<?= BASE_URL ?>index.php?url=siswa/nilai" class="btn btn-primary w-100 fw-bold rounded-3 py-2 text-nowrap shadow-xs d-flex align-items-center justify-content-center gap-1.5" style="font-size: 0.85rem;">
                            <i class="bi bi-journal-text"></i>
                            <span>Buka Transkrip Nilai & E-Rapor</span>
                        </a>
                        <a href="<?= BASE_URL ?>index.php?url=siswa/sertifikat" class="btn btn-outline-success w-100 fw-bold rounded-3 py-2 text-nowrap d-flex align-items-center justify-content-center gap-1.5" style="font-size: 0.85rem;">
                            <i class="bi bi-patch-check"></i>
                            <span>Lihat Sertifikat Kelulusan</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- REAL DATABASE PRESENSI LOG SISWA CARD -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden border-start border-4 border-info">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-calendar-check-fill text-info fs-5"></i>
                            <span>Presensi Log Real Siswa</span>
                        </h6>
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 rounded-pill small fw-bold" style="font-size:0.7rem;">
                            <?= htmlspecialchars($certStats['presensi_log'] ?? '0%') ?> Kehadiran
                        </span>
                    </div>

                    <!-- 4 Quick Presensi Counter Matrix -->
                    <div class="row g-2 mb-3 text-center">
                        <div class="col-3">
                            <div class="p-2 rounded-3 border bg-success-subtle text-success">
                                <div class="fw-bold fs-5 lh-1"><?= (int)($certStats['total_hadir'] ?? 0) ?></div>
                                <small class="fw-semibold" style="font-size:0.65rem;">Hadir</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded-3 border bg-primary-subtle text-primary">
                                <div class="fw-bold fs-5 lh-1"><?= (int)($certStats['total_izin'] ?? 0) ?></div>
                                <small class="fw-semibold" style="font-size:0.65rem;">Izin</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded-3 border bg-warning-subtle text-warning-emphasis">
                                <div class="fw-bold fs-5 lh-1"><?= (int)($certStats['total_sakit'] ?? 0) ?></div>
                                <small class="fw-semibold" style="font-size:0.65rem;">Sakit</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded-3 border bg-danger-subtle text-danger">
                                <div class="fw-bold fs-5 lh-1"><?= (int)($certStats['total_alpa'] ?? 0) ?></div>
                                <small class="fw-semibold" style="font-size:0.65rem;">Alpa</small>
                            </div>
                        </div>
                    </div>

                    <!-- Real Attendance Logs Timeline -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-slate-500 fw-bold" style="font-size: 0.75rem;">Riwayat Presensi Terbaru:</small>
                        <small class="text-muted" style="font-size: 0.7rem;">Total: <?= (int)($certStats['total_absensi'] ?? 0) ?> Pertemuan</small>
                    </div>

                    <?php if (empty($presensiLogs)): ?>
                        <div class="text-center py-4 bg-light rounded-4 border">
                            <i class="bi bi-calendar-x text-muted fs-3 d-block mb-1"></i>
                            <span class="small text-muted fw-medium">Belum ada log presensi tercatat di database sekolah.</span>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-2 mb-3">
                            <?php foreach ($presensiLogs as $log): 
                                $st = strtolower($log['status'] ?? 'hadir');
                                $badgeCls = 'bg-success text-white';
                                $iconCls = 'bi-check-circle-fill text-success';
                                if ($st === 'izin') {
                                    $badgeCls = 'bg-primary text-white';
                                    $iconCls = 'bi-info-circle-fill text-primary';
                                } elseif ($st === 'sakit') {
                                    $badgeCls = 'bg-warning text-dark';
                                    $iconCls = 'bi-hospital-fill text-warning';
                                } elseif ($st === 'alpa') {
                                    $badgeCls = 'bg-danger text-white';
                                    $iconCls = 'bi-x-circle-fill text-danger';
                                }
                                $tglIndo = date('d M Y', strtotime($log['tanggal']));
                                $jamHadir = !empty($log['waktu_masuk']) ? date('H:i', strtotime($log['waktu_masuk'])) . ' WIB' : (!empty($log['waktu_hadir']) ? date('H:i', strtotime($log['waktu_hadir'])) . ' WIB' : 'Tercatat');
                            ?>
                                <div class="p-2.5 rounded-3 border bg-light shadow-2xs d-flex justify-content-between align-items-start gap-2">
                                    <div class="d-flex align-items-start gap-2 overflow-hidden">
                                        <i class="bi <?= $iconCls ?> fs-5 mt-0.5"></i>
                                        <div class="text-truncate">
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.82rem;">
                                                <?= htmlspecialchars($log['nama_mapel'] ?? 'Kegiatan KBM Harian') ?>
                                            </div>
                                            <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">
                                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($log['nama_guru'] ?? 'Guru Pengampu') ?>
                                                <?php if (!empty($log['keterangan'])): ?>
                                                    &bull; <span class="fst-italic"><?= htmlspecialchars($log['keterangan']) ?></span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="text-end text-nowrap">
                                        <span class="badge <?= $badgeCls ?> rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.68rem;">
                                            <?= htmlspecialchars($log['status']) ?>
                                        </span>
                                        <div class="text-muted mt-0.5" style="font-size: 0.68rem;">
                                            <?= $tglIndo ?> &bull; <?= $jamHadir ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <small class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-qr-code me-1"></i>Presensi via QR / Manual Guru</small>
                        <a href="<?= BASE_URL ?>index.php?url=siswa/kartuPelajar" class="small fw-bold text-primary text-decoration-none" style="font-size: 0.75rem;">
                            Buka QR Presensi <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
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
                                    <?php if (!empty($p['banner'])): ?>
                                        <div class="mb-2.5 overflow-hidden rounded-3 border">
                                            <img src="<?= BASE_URL . htmlspecialchars($p['banner']) ?>" class="w-100" style="max-height: 160px; object-fit: cover;" alt="Banner">
                                        </div>
                                    <?php endif; ?>
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
                    backgroundColor: 'rgba(13, 110, 253, 0.75)',
                    borderColor: '#0d6efd',
                    borderWidth: 2,
                    borderRadius: 6,
                    hoverBackgroundColor: '#0b5ed7'
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
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { font: { weight: 'bold' } },
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
