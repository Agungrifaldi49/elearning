<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- 1. Hero Executive Welcome Header -->
        <div class="card-custom p-4 mb-4 shadow-sm border-0 bg-primary text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 position-relative" style="z-index: 2;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-25 p-3 rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px;">
                        <i class="bi bi-person-badge fs-2 text-white"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($guru['nama_lengkap'] ?? $user['full_name']) ?></h4>
                        <div class="d-flex align-items-center gap-2 flex-wrap small opacity-90">
                            <span><i class="bi bi-card-text me-1"></i>NIP: <code><?= htmlspecialchars($guru['nip'] ?? '-') ?></code></span>
                            <span>•</span>
                            <span><i class="bi bi-calendar-check me-1"></i>Periode: <strong><?= htmlspecialchars($activeTa['tahun_ajaran'] ?? '2025/2026') ?> (<?= htmlspecialchars($activeTa['semester'] ?? 'Ganjil') ?>)</strong></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= BASE_URL ?>index.php?url=guru/kelasVirtual&tab=key" class="btn btn-warning text-dark fw-bold shadow-sm">
                        <i class="bi bi-key-fill me-1"></i> Key Mapel Saya
                    </a>
                    <a href="<?= BASE_URL ?>index.php?url=guru/scanQr" class="btn btn-light text-primary fw-bold shadow-sm">
                        <i class="bi bi-qr-code-scan me-1"></i> Scan QR Hadir
                    </a>
                    <a href="<?= BASE_URL ?>index.php?url=guru/kartuGuru" class="btn btn-success text-white fw-bold shadow-sm">
                        <i class="bi bi-person-badge-fill me-1"></i> Kartu Guru Digital
                    </a>
                    <a href="https://meet.google.com" target="_blank" class="btn btn-outline-light fw-bold">
                        <i class="bi bi-camera-reels-fill me-1"></i> Live Class
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Primary Stat Cards Grid -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-primary shadow-sm h-100 p-4 rounded-4 position-relative overflow-hidden">
                    <div class="fw-bold small text-uppercase opacity-75">Materi Diunggah</div>
                    <div class="display-6 fw-bold my-1"><?= count($materiList) ?></div>
                    <small class="d-flex align-items-center gap-1"><i class="bi bi-journal-text"></i> Modul & Video Pembelajaran</small>
                    <i class="bi bi-journal-text icon opacity-25" style="position:absolute; right:15px; bottom:15px; font-size:3.5rem;"></i>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-success shadow-sm h-100 p-4 rounded-4 position-relative overflow-hidden">
                    <div class="fw-bold small text-uppercase opacity-75">Tugas Active</div>
                    <div class="display-6 fw-bold my-1"><?= count($tugasList) ?></div>
                    <small class="d-flex align-items-center gap-1"><i class="bi bi-file-earmark-check"></i> Penugasan & Rubrik</small>
                    <i class="bi bi-file-earmark-check icon opacity-25" style="position:absolute; right:15px; bottom:15px; font-size:3.5rem;"></i>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-warning text-dark shadow-sm h-100 p-4 rounded-4 position-relative overflow-hidden">
                    <div class="fw-bold small text-uppercase opacity-75">Kuis & CBT Ujian</div>
                    <div class="display-6 fw-bold my-1"><?= count($quizList) ?></div>
                    <small class="d-flex align-items-center gap-1"><i class="bi bi-stopwatch-fill"></i> Paket Evaluasi Online</small>
                    <i class="bi bi-patch-question icon opacity-25" style="position:absolute; right:15px; bottom:15px; font-size:3.5rem;"></i>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-info text-dark shadow-sm h-100 p-4 rounded-4 position-relative overflow-hidden">
                    <div class="fw-bold small text-uppercase opacity-75">Siswa Terdaftar</div>
                    <div class="display-6 fw-bold my-1"><?= count($enrolledStudents) ?></div>
                    <small class="d-flex align-items-center gap-1"><i class="bi bi-people-fill"></i> Terdaftar di Mapel Saya</small>
                    <i class="bi bi-people-fill icon opacity-25" style="position:absolute; right:15px; bottom:15px; font-size:3.5rem;"></i>
                </div>
            </div>
        </div>

        <!-- 3. Main Balanced 2-Column Layout -->
        <div class="row g-4 mb-4">
            
            <!-- LEFT MAIN COLUMN (8 COLS) -->
            <div class="col-12 col-lg-8">
                
                <!-- WIDGET 1: PENGINGAT JADWAL KBM HARI INI -->
                <div class="card-custom p-4 shadow-sm mb-4 border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-clock-history text-warning me-2 fs-4"></i>Pengingat Sesi KBM Hari Ini (<?= $todayName ?>)
                            </h5>
                            <small class="text-muted">Pengingat waktu & ruang mengajar Anda pada hari berjalan.</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark px-3 py-2 fs-6 fw-bold">
                                <i class="bi bi-calendar-check-fill me-1"></i><?= count($jadwalHariIni) ?> Sesi
                            </span>
                            <a href="<?= BASE_URL ?>index.php?url=guru/jadwal" class="btn btn-sm btn-outline-warning text-dark fw-bold">
                                <i class="bi bi-calendar3 me-1"></i> Lihat Semua Hari
                            </a>
                        </div>
                    </div>

                    <?php if (empty($jadwalHariIni)): ?>
                        <div class="p-3 bg-light rounded-4 text-center text-muted">
                            <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-1"></i>
                            Tidak ada jadwal mengajar pada hari <strong><?= $todayName ?></strong>. Silakan periksa <a href="<?= BASE_URL ?>index.php?url=guru/jadwal" class="fw-bold text-primary">Jadwal Mengajar Saya</a> untuk melihat jadwal lengkap hari lainnya.
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($jadwalHariIni as $jh): ?>
                                <div class="col-12 col-md-6">
                                    <div class="p-3 bg-light rounded-4 border h-100 d-flex flex-column justify-content-between shadow-xs">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-warning text-dark fw-bold">
                                                    <i class="bi bi-clock me-1"></i><?= substr($jh['jam_mulai'], 0, 5) ?> - <?= substr($jh['jam_selesai'], 0, 5) ?> WIB
                                                </span>
                                                <span class="badge bg-primary-subtle text-primary fw-bold">
                                                    <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($jh['ruangan'] ?? 'Ruang Kelas') ?>
                                                </span>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($jh['nama_mapel']) ?></h6>
                                        </div>
                                        <div class="pt-2 border-top d-flex justify-content-between align-items-center mt-2">
                                            <small class="text-muted">Target Kelas:</small>
                                            <span class="badge bg-success text-white fw-bold">
                                                <i class="bi bi-building me-1"></i><?= htmlspecialchars($jh['nama_kelas']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- WIDGET 2: GRAFIK AKTIVITAS KBM GURU -->
                <div class="card card-custom p-4 shadow-sm mb-4 border-0">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-line-fill text-primary me-2 fs-5"></i>Grafik Statistik Aktivitas Pembelajaran Saya</h6>
                            <small class="text-muted">Metrik kuantitatif realtime seluruh aktivitas pengampuan Anda.</small>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold">Data Realtime DB</span>
                    </div>
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="guruBarChart"></canvas>
                    </div>
                </div>

                <!-- WIDGET 3: PAPAN INFORMASI RESMI DARI ADMIN -->
                <div class="card card-custom p-4 shadow-sm border-start border-4 border-danger">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-megaphone-fill text-danger me-2 fs-5"></i>Informasi & Pengumuman Resmi (Dari Admin Sekolah)
                            </h6>
                            <small class="text-muted">Pengumuman penting dan edaran resmi dari Administrator.</small>
                        </div>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fw-bold">
                            Total: <?= count($pengumumanList) ?> Informasi
                        </span>
                    </div>

                    <?php if (empty($pengumumanList)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-megaphone fs-1 d-block mb-2 text-danger"></i>
                            Belum ada pengumuman atau informasi terbaru dari Admin Sekolah.
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach (array_slice($pengumumanList, 0, 2) as $p): ?>
                                <div class="col-12 col-md-6">
                                    <div class="p-3 bg-light rounded-4 border h-100 d-flex flex-column justify-content-between shadow-xs">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-danger text-white fw-bold"><i class="bi bi-shield-check me-1"></i>ADMIN SEKOLAH</span>
                                                <small class="text-muted"><i class="bi bi-clock me-1"></i><?= date('d M Y, H:i', strtotime($p['created_at'])) ?></small>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-2"><?= htmlspecialchars($p['judul']) ?></h6>
                                            <p class="small text-muted mb-3" style="display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                                                <?= htmlspecialchars($p['isi']) ?>
                                            </p>
                                        </div>
                                        <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Penerima: <strong><?= htmlspecialchars(strtoupper($p['target_role'])) ?></strong></small>
                                            <?php if ($p['is_popup']): ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-bell-fill me-1"></i>Penting</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- RIGHT SIDEBAR COLUMN (4 COLS) -->
            <div class="col-12 col-lg-4">
                
                <!-- WIDGET 4: AKSES CEPAT LAYANAN GURU -->
                <div class="card card-custom p-4 shadow-sm mb-4 border-0">
                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-lightning-charge-fill text-warning me-2 fs-5"></i>Akses Cepat Fitur Guru</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="<?= BASE_URL ?>index.php?url=guru/materi" class="btn btn-outline-primary w-100 text-start p-3 rounded-4 shadow-xs">
                                <i class="bi bi-cloud-upload fs-3 d-block mb-1 text-primary"></i>
                                <span class="fw-bold text-dark d-block" style="font-size:0.85rem;">Upload Materi</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?= BASE_URL ?>index.php?url=guru/tugas" class="btn btn-outline-success w-100 text-start p-3 rounded-4 shadow-xs">
                                <i class="bi bi-plus-square fs-3 d-block mb-1 text-success"></i>
                                <span class="fw-bold text-dark d-block" style="font-size:0.85rem;">Buat Tugas</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?= BASE_URL ?>index.php?url=guru/quiz" class="btn btn-outline-warning text-dark w-100 text-start p-3 rounded-4 shadow-xs">
                                <i class="bi bi-stopwatch fs-3 d-block mb-1 text-warning"></i>
                                <span class="fw-bold text-dark d-block" style="font-size:0.85rem;">Kuis / CBT</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?= BASE_URL ?>index.php?url=guru/inputNilai" class="btn btn-outline-danger w-100 text-start p-3 rounded-4 shadow-xs">
                                <i class="bi bi-pencil-square fs-3 d-block mb-1 text-danger"></i>
                                <span class="fw-bold text-dark d-block" style="font-size:0.85rem;">Input Nilai</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?= BASE_URL ?>index.php?url=guru/kelasVirtual&tab=key" class="btn btn-outline-info text-dark w-100 text-start p-3 rounded-4 shadow-xs">
                                <i class="bi bi-key-fill fs-3 d-block mb-1 text-info"></i>
                                <span class="fw-bold text-dark d-block" style="font-size:0.85rem;">Key Mapel</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?= BASE_URL ?>index.php?url=guru/scanQr" class="btn btn-outline-secondary w-100 text-start p-3 rounded-4 shadow-xs">
                                <i class="bi bi-qr-code-scan fs-3 d-block mb-1 text-secondary"></i>
                                <span class="fw-bold text-dark d-block" style="font-size:0.85rem;">Scan QR</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- WIDGET 5: GRAFIK DONUT DISTRIBUSI SISWA PER-MAPEL -->
                <div class="card card-custom p-4 shadow-sm mb-4 border-0">
                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-pie-chart-fill text-success me-2 fs-5"></i>Distribusi Siswa Per-Mapel</h6>
                    <div style="position: relative; height: 240px; width: 100%;">
                        <canvas id="guruMapelDonutChart"></canvas>
                    </div>
                </div>

                <!-- WIDGET 6: MATERI TERBARU -->
                <div class="card card-custom p-4 shadow-sm border-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history text-primary me-2 fs-5"></i>Materi Terbaru</h6>
                        <a href="<?= BASE_URL ?>index.php?url=guru/materi" class="btn btn-sm btn-outline-primary fw-bold" style="font-size:0.75rem;">Semua</a>
                    </div>
                    <div class="list-group list-group-flush small">
                        <?php if (empty($materiList)): ?>
                            <div class="text-center py-3 text-muted">Belum ada materi diunggah.</div>
                        <?php else: ?>
                            <?php foreach (array_slice($materiList, 0, 3) as $m): ?>
                                <div class="list-group-item px-0 py-2 border-bottom">
                                    <div class="fw-bold text-dark text-truncate"><?= htmlspecialchars($m['judul']) ?></div>
                                    <div class="d-flex justify-content-between text-muted" style="font-size:0.75rem;">
                                        <span><?= htmlspecialchars($m['nama_mapel']) ?></span>
                                        <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($m['nama_kelas']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>

    </div>
</main>

<!-- Chart.js Realtime Script for Guru -->
<script>
(function() {
    function initGuruCharts() {
        if (typeof Chart === 'undefined') {
            setTimeout(initGuruCharts, 100);
            return;
        }

        // 1. Chart Aktivitas Pengampuan Guru (Bar Chart)
        const canvasBar = document.getElementById('guruBarChart');
        if (canvasBar) {
            const ctxBar = canvasBar.getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: ['Materi', 'Tugas', 'Kuis/CBT', 'Key Mapel', 'Siswa Terdaftar'],
                    datasets: [{
                        label: 'Jumlah Realtime Data Pengampuan',
                        data: [
                            <?= count($materiList) ?>,
                            <?= count($tugasList) ?>,
                            <?= count($quizList) ?>,
                            <?= count($myKeys) ?>,
                            <?= count($enrolledStudents) ?>
                        ],
                        backgroundColor: [
                            '#0d6efd',
                            '#198754',
                            '#ffc107',
                            '#dc3545',
                            '#0dcaf0'
                        ],
                        borderRadius: 10,
                        borderSkipped: false,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ' + context.parsed.y + ' Data';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, precision: 0 },
                            grid: { color: 'rgba(0, 0, 0, 0.05)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 2. Chart Distribusi Siswa Terdaftar Per-Mapel (Donut Chart)
        const canvasDonut = document.getElementById('guruMapelDonutChart');
        if (canvasDonut) {
            <?php
            $mLabels = [];
            $mData = [];
            if (!empty($mapelDistribution)) {
                foreach ($mapelDistribution as $mName => $cnt) {
                    $mLabels[] = $mName;
                    $mData[] = (int)$cnt;
                }
            } else {
                $mLabels = ['Mapel Pengampuan'];
                $mData = [0];
            }
            ?>
            const ctxDonut = canvasDonut.getContext('2d');
            new Chart(ctxDonut, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($mLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($mData) ?>,
                        backgroundColor: [
                            '#0d6efd',
                            '#198754',
                            '#ffc107',
                            '#dc3545',
                            '#0dcaf0',
                            '#6f42c1'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: { size: 11 }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGuruCharts);
    } else {
        initGuruCharts();
    }
})();
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
