<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
// Calculate Schedule KPI Metrics
$totalSessionsWeekly = 0;
$todaySessionsCount = 0;
$uniqueKelasMap = [];
$todayIndex = (int)date('N'); // 1 = Senin, 6 = Sabtu
$dayNamesMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
$todayName = $dayNamesMap[$todayIndex] ?? 'Senin';

foreach ($hariList as $hName) {
    $hItems = $jadwalByHari[$hName] ?? [];
    $totalSessionsWeekly += count($hItems);
    if ($hName === $todayName) {
        $todaySessionsCount = count($hItems);
    }
    foreach ($hItems as $jVal) {
        if (!empty($jVal['kelas_id'])) {
            $uniqueKelasMap[$jVal['kelas_id']] = true;
        }
    }
}
$totalUniqueKelas = count($uniqueKelasMap);
?>

<style>
/* Modern LMS Teacher Schedule Architecture */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.jadwal-guru-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

/* Glassmorphic Hero Banner */
.jadwal-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #78350f 50%, #d97706 100%);
    border-radius: 20px;
    box-shadow: 0 12px 30px -5px rgba(217, 119, 6, 0.25);
    position: relative;
    overflow: hidden;
}

.jadwal-hero-banner::after {
    content: '';
    position: absolute;
    top: -40%;
    right: -15%;
    width: 360px;
    height: 360px;
    background: radial-gradient(circle, rgba(251, 191, 36, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}

/* KPI Summary Cards */
.jadwal-kpi-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #ffffff;
    transition: all 0.2s ease;
}
.jadwal-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
}

/* Day Schedule Card */
.schedule-day-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}
.schedule-day-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px -5px rgba(15, 23, 42, 0.09) !important;
    border-color: #cbd5e1;
}

/* Session Item Box */
.session-item-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    transition: all 0.2s ease;
}
.session-item-box:hover {
    background: #ffffff;
    box-shadow: 0 6px 16px rgba(0,0,0,0.04);
}

/* Day Filter Pills */
.day-pill-btn {
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    font-weight: 600;
    font-size: 0.8rem;
    border-radius: 30px;
    padding: 6px 16px;
    transition: all 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
}
.day-pill-btn:hover, .day-pill-btn.active {
    background: #d97706;
    color: #ffffff;
    border-color: #d97706;
    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);
}

/* Responsive Overrides */
@media (max-width: 575.98px) {
    .jadwal-hero-banner {
        padding: 1.25rem !important;
        border-radius: 16px !important;
    }
    .jadwal-kpi-card {
        padding: 0.65rem !important;
    }
    .jadwal-kpi-card .fs-5 {
        font-size: 1.15rem !important;
    }
}
</style>

<main class="main-content px-2 px-sm-3 px-md-4 py-3 jadwal-guru-wrapper">
<div class="container-fluid pt-3">

    <!-- Hero Banner Header -->
    <div class="jadwal-hero-banner text-white p-4 p-md-5 mb-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-3 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-warning bg-gradient p-3.5 rounded-4 text-dark shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 58px; height: 58px; background: #f59e0b;">
                    <i class="bi bi-clock-history fs-2"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-white mb-1" style="letter-spacing: -0.4px;">Jadwal Mengajar Guru</h3>
                    <p class="text-amber-100 small mb-0 fw-medium">Jadwal resmi Kegiatan Belajar Mengajar (KBM) Guru per-hari pada periode akademik aktif.</p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-white text-dark shadow-sm px-3.5 py-2 rounded-pill fw-bold" style="font-size:0.83rem;">
                    <i class="bi bi-calendar-event text-warning me-1.5"></i>T.A. <?= htmlspecialchars($activeTa['tahun_ajaran'] ?? '2025/2026') ?> (<?= htmlspecialchars($activeTa['semester'] ?? 'Ganjil') ?>)
                </span>
                <span class="badge bg-amber-900 bg-opacity-50 text-white border border-warning border-opacity-50 px-3.5 py-2 rounded-pill fw-semibold" style="font-size:0.83rem; background: rgba(120, 53, 15, 0.5);">
                    <i class="bi bi-lock-fill me-1"></i> Mode Read-Only Resmi
                </span>
            </div>
        </div>
    </div>

    <!-- KPI Metric Summary Bar -->
    <div class="row g-2.5 mb-4">
        <div class="col-6 col-md-3">
            <div class="jadwal-kpi-card p-3 d-flex align-items-center gap-2.5">
                <div class="bg-warning bg-opacity-15 text-warning-emphasis p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-calendar-week-fill fs-4 text-warning"></i>
                </div>
                <div>
                    <small class="text-muted d-block" style="font-size:0.72rem;">Sesi Mingguan</small>
                    <strong class="text-dark fs-5 mb-0 fw-extrabold"><?= number_format($totalSessionsWeekly) ?> Sesi</strong>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="jadwal-kpi-card p-3 d-flex align-items-center gap-2.5">
                <div class="bg-success bg-opacity-10 text-success p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-calendar-check-fill fs-4 text-success"></i>
                </div>
                <div>
                    <small class="text-muted d-block" style="font-size:0.72rem;">Jadwal Hari Ini (<?= $todayName ?>)</small>
                    <strong class="text-dark fs-5 mb-0 fw-extrabold"><?= number_format($todaySessionsCount) ?> Sesi</strong>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="jadwal-kpi-card p-3 d-flex align-items-center gap-2.5">
                <div class="bg-primary bg-opacity-10 text-primary p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-building-fill fs-4 text-primary"></i>
                </div>
                <div>
                    <small class="text-muted d-block" style="font-size:0.72rem;">Rombel Kelas Ajar</small>
                    <strong class="text-dark fs-5 mb-0 fw-extrabold"><?= number_format($totalUniqueKelas) ?> Rombel</strong>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="jadwal-kpi-card p-3 d-flex align-items-center gap-2.5">
                <div class="bg-info bg-opacity-10 text-info p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-stopwatch-fill fs-4 text-info"></i>
                </div>
                <div>
                    <small class="text-muted d-block" style="font-size:0.72rem;">Status Hari Ini</small>
                    <strong class="<?= $todaySessionsCount > 0 ? 'text-success' : 'text-muted' ?> fs-6 mb-0 fw-bold">
                        <?= $todaySessionsCount > 0 ? 'Ada KBM Aktif' : 'Tidak Ada KBM' ?>
                    </strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Day Filter Pills Bar -->
    <div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white">
        <div class="d-flex align-items-center gap-2 overflow-x-auto pb-1" style="-webkit-overflow-scrolling: touch;">
            <span class="fw-bold small text-muted text-nowrap me-2"><i class="bi bi-funnel-fill text-warning me-1"></i>Filter Hari:</span>
            <button type="button" class="day-pill-btn active" onclick="filterDayCards('all', this)">Semua Hari (Seminggu)</button>
            <?php foreach ($hariList as $hName): 
                $isH = ($hName === $todayName);
            ?>
                <button type="button" class="day-pill-btn" onclick="filterDayCards('<?= $hName ?>', this)">
                    Hari <?= $hName ?> <?= $isH ? '⚡' : '' ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Schedule By Day Cards Grid -->
    <div class="row g-3 mb-4" id="dayCardsGrid">
        <?php foreach ($hariList as $hari): 
            $items = $jadwalByHari[$hari] ?? [];
            $isToday = ($hari === $todayName);
        ?>
            <div class="col-12 col-md-6 col-xl-4 day-card-col" data-hari="<?= $hari ?>">
                <div class="schedule-day-card p-3.5 p-sm-4 h-100 position-relative border-top border-4 <?= $isToday ? 'border-success' : 'border-primary' ?> shadow-sm d-flex flex-column justify-content-between">
                    <div>
                        <!-- Day Header Strip -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle <?= $isToday ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' ?> d-flex align-items-center justify-content-center shadow-xs" style="width: 38px; height: 38px;">
                                    <i class="bi bi-calendar-day-fill fs-5"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-0 fs-5">Hari <?= $hari ?></h5>
                            </div>
                            <?php if ($isToday): ?>
                                <span class="badge bg-success text-white rounded-pill px-3 py-1.5 fw-bold" style="font-size:0.75rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i>Hari Ini
                                </span>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 fw-medium" style="font-size:0.75rem;">
                                    <?= count($items) ?> Sesi
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Session Items List -->
                        <?php if (empty($items)): ?>
                            <div class="p-4 bg-light rounded-4 text-center text-muted border" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                                <i class="bi bi-calendar-x d-block fs-3 mb-1.5 text-secondary"></i>
                                <span class="small fw-semibold d-block">Tidak Ada Jadwal Mengajar</span>
                                <small class="text-muted" style="font-size:0.72rem;">Tidak ada sesi KBM pada hari <?= $hari ?></small>
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-2.5">
                                <?php foreach ($items as $j): ?>
                                    <div class="session-item-box p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2 gap-1 flex-wrap">
                                            <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-1" style="font-size:0.73rem; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; color:#ffffff !important;">
                                                <i class="bi bi-clock-history me-1"></i><?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?> WIB
                                            </span>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fw-bold" style="font-size:0.7rem;">
                                                <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($j['ruangan'] ?? 'Ruang Kelas') ?>
                                            </span>
                                        </div>

                                        <h6 class="fw-bold text-dark mb-1 fs-6" style="line-height:1.35;"><?= htmlspecialchars($j['nama_mapel']) ?></h6>

                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                            <small class="text-muted" style="font-size:0.73rem;">Kelas Target:</small>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fw-bold" style="font-size:0.75rem;">
                                                <i class="bi bi-building me-1"></i><?= htmlspecialchars($j['nama_kelas']) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer Action Links -->
                    <?php if (!empty($items)): ?>
                        <div class="d-flex justify-content-between align-items-center gap-1.5 pt-3 mt-3 border-top">
                            <a href="<?= BASE_URL ?>index.php?url=guru/absensi" class="btn btn-sm btn-outline-success rounded-pill flex-grow-1 fw-bold py-1.5" style="font-size:0.78rem;">
                                <i class="bi bi-calendar-check me-1"></i> Presensi KBM
                            </a>
                            <a href="<?= BASE_URL ?>index.php?url=guru/materi" class="btn btn-sm btn-outline-primary rounded-pill flex-grow-1 fw-bold py-1.5" style="font-size:0.78rem;">
                                <i class="bi bi-book me-1"></i> Materi Mapel
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>
</main>

<script>
// Filter Day Cards JavaScript
function filterDayCards(dayName, btnEl) {
    document.querySelectorAll('.day-pill-btn').forEach(b => b.classList.remove('active'));
    btnEl.classList.add('active');

    document.querySelectorAll('.day-card-col').forEach(col => {
        if (dayName === 'all' || col.dataset.hari === dayName) {
            col.style.display = '';
        } else {
            col.style.display = 'none';
        }
    });
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
