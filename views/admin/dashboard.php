<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Top Header & Quick Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Executive Administrator</h4>
                <p class="text-muted small mb-0">Statistik realtime, grafik analitik LMS, informasi sekolah, & pemantauan data akademik SMK MH Cicalengka.</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold">
                    <i class="bi bi-calendar-event me-1"></i> Periode Aktif: <?= htmlspecialchars($activeTa['tahun_ajaran'] ?? '2025/2026') ?> - <?= htmlspecialchars($activeTa['semester'] ?? 'Ganjil') ?>
                </span>
                <button type="button" class="btn btn-warning text-dark fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddPengumuman">
                    <i class="bi bi-megaphone-fill me-1"></i> + Terbitkan Informasi
                </button>
                <a href="<?= BASE_URL ?>index.php?url=admin/users" class="btn btn-outline-primary shadow-sm">
                    <i class="bi bi-people-fill me-1"></i> Kelola User
                </a>
                <a href="<?= BASE_URL ?>index.php?url=admin/backup" class="btn btn-primary shadow-sm">
                    <i class="bi bi-database-down me-1"></i> Backup DB
                </a>
            </div>
        </div>

        <!-- Primary Stat Cards Row -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-primary shadow-sm h-100 p-4 rounded-4 position-relative overflow-hidden">
                    <div class="fw-bold small text-uppercase opacity-75">Total Tenaga Pengajar</div>
                    <div class="display-6 fw-bold my-1"><?= (int)($stats['total_guru'] ?? 0) ?></div>
                    <small class="d-flex align-items-center gap-1"><i class="bi bi-person-badge-fill"></i> Guru Terverifikasi</small>
                    <i class="bi bi-person-badge icon opacity-25" style="position:absolute; right:15px; bottom:15px; font-size:3.5rem;"></i>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-success shadow-sm h-100 p-4 rounded-4 position-relative overflow-hidden">
                    <div class="fw-bold small text-uppercase opacity-75">Total Siswa Terdaftar</div>
                    <div class="display-6 fw-bold my-1"><?= (int)($stats['total_siswa'] ?? 0) ?></div>
                    <small class="d-flex align-items-center gap-1"><i class="bi bi-person-check-fill"></i> Siswa Aktif KBM</small>
                    <i class="bi bi-people-fill icon opacity-25" style="position:absolute; right:15px; bottom:15px; font-size:3.5rem;"></i>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-warning text-dark shadow-sm h-100 p-4 rounded-4 position-relative overflow-hidden">
                    <div class="fw-bold small text-uppercase opacity-75">Rombongan Belajar (Kelas)</div>
                    <div class="display-6 fw-bold my-1"><?= (int)($stats['total_kelas'] ?? 0) ?></div>
                    <small class="d-flex align-items-center gap-1"><i class="bi bi-building-check"></i> Rombel Kelas Terdaftar</small>
                    <i class="bi bi-building icon opacity-25" style="position:absolute; right:15px; bottom:15px; font-size:3.5rem;"></i>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-danger shadow-sm h-100 p-4 rounded-4 position-relative overflow-hidden">
                    <div class="fw-bold small text-uppercase opacity-75">Modul Pembelajaran & CBT</div>
                    <div class="display-6 fw-bold my-1"><?= (int)($stats['total_materi'] + $stats['total_tugas'] + $stats['total_quiz']) ?></div>
                    <small class="d-flex align-items-center gap-1"><i class="bi bi-journal-richtext"></i> Materi, Tugas & Quiz</small>
                    <i class="bi bi-journal-check icon opacity-25" style="position:absolute; right:15px; bottom:15px; font-size:3.5rem;"></i>
                </div>
            </div>
        </div>

        <!-- Secondary Performance Highlights Row -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card card-custom p-3 shadow-sm border-start border-4 border-info">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Rata-rata Nilai E-Rapor</small>
                            <h4 class="fw-bold text-info mb-0"><?= number_format((float)($analytics['avg_score'] ?? $analytics['avgScore'] ?? 0), 1) ?> / 100</h4>
                        </div>
                        <div class="bg-info-subtle text-info p-3 rounded-circle">
                            <i class="bi bi-award fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card card-custom p-3 shadow-sm border-start border-4 border-success">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Tingkat Kehadiran Siswa</small>
                            <h4 class="fw-bold text-success mb-0"><?= number_format((float)($analytics['attendance_rate'] ?? $analytics['attRate'] ?? 0), 1) ?>%</h4>
                        </div>
                        <div class="bg-success-subtle text-success p-3 rounded-circle">
                            <i class="bi bi-calendar-check fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card card-custom p-3 shadow-sm border-start border-4 border-warning">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Pengumuman Diterbitkan</small>
                            <h4 class="fw-bold text-dark mb-0"><?= count($pengumumanList) ?> Informasi</h4>
                        </div>
                        <div class="bg-warning-subtle text-dark p-3 rounded-circle">
                            <i class="bi bi-megaphone fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Charts Row -->
        <div class="row g-4 mb-4">
            <!-- Chart 1: Grafik Aktivitas LMS & Akademik -->
            <div class="col-12 col-lg-7">
                <div class="card card-custom p-4 h-100 shadow-sm border-0">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-line-fill text-primary me-2 fs-5"></i>Grafik Statistik Data Akademik & LMS Realtime</h6>
                            <small class="text-muted">Metrik kuantitatif seluruh komponen sistem database sekolah.</small>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">Data Realtime DB</span>
                    </div>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="adminLmsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart 2: Grafik Distribusi Siswa Per-Jurusan (Donut Chart) -->
            <div class="col-12 col-lg-5">
                <div class="card card-custom p-4 h-100 shadow-sm border-0">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pie-chart-fill text-success me-2 fs-5"></i>Distribusi Siswa Per-Jurusan</h6>
                            <small class="text-muted">Proporsi jumlah siswa aktif tiap program keahlian.</small>
                        </div>
                    </div>
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="jurusanDonutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Information & Announcement Board (Informasi Admin) -->
        <div class="card card-custom p-4 shadow-sm mb-4 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-megaphone-fill text-warning me-2 fs-4"></i>Informasi & Pengumuman Sekolah (Manajemen Admin)
                    </h5>
                    <small class="text-muted">Informasi resmi yang diterbitkan Administrator untuk Guru, Siswa, maupun Seluruh Pengguna.</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-warning text-dark fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddPengumuman">
                        <i class="bi bi-plus-circle me-1"></i> Terbitkan Informasi Baru
                    </button>
                    <a href="<?= BASE_URL ?>index.php?url=admin/pengumuman" class="btn btn-outline-secondary fw-bold shadow-sm">
                        <i class="bi bi-gear-fill me-1"></i> Kelola Semua
                    </a>
                </div>
            </div>

            <?php if (empty($pengumumanList)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-megaphone fs-1 d-block mb-2 text-warning"></i>
                    Belum ada pengumuman / informasi sekolah yang diterbitkan. Klik <strong>Terbitkan Informasi Baru</strong> untuk menambah.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach (array_slice($pengumumanList, 0, 3) as $p): ?>
                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-light rounded-4 border h-100 d-flex flex-column justify-content-between shadow-xs">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars(strtoupper($p['target_role'])) ?></span>
                                        <small class="text-muted"><i class="bi bi-clock me-1"></i><?= date('d M Y, H:i', strtotime($p['created_at'])) ?></small>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-2"><?= htmlspecialchars($p['judul']) ?></h6>
                                    <p class="small text-muted mb-3" style="display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                                        <?= htmlspecialchars($p['isi']) ?>
                                    </p>
                                </div>
                                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                    <small class="text-primary fw-bold"><i class="bi bi-shield-check me-1"></i>Resmi Admin</small>
                                    <?php if ($p['is_popup']): ?>
                                        <span class="badge bg-danger"><i class="bi bi-bell-fill me-1"></i>Pop-Up Dialog</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Activity & Login Logs Section -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-7">
                <div class="card card-custom p-4 h-100 shadow-sm border-0">
                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-journal-text text-primary me-2 fs-5"></i>Log Aktivitas Sistem Realtime</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>User</th>
                                    <th>Aktivitas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($activities)): ?>
                                    <?php foreach (array_slice($activities, 0, 6) as $act): ?>
                                        <tr>
                                            <td class="text-muted" style="white-space:nowrap;"><?= date('d/m/Y H:i', strtotime($act['created_at'])) ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($act['full_name'] ?? 'System') ?></td>
                                            <td><?= htmlspecialchars($act['activity']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted">Belum ada aktivitas tercatat.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="card card-custom p-4 h-100 shadow-sm border-0">
                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-box-arrow-in-right text-success me-2 fs-5"></i>Riwayat Sesi Login Pengguna</h6>
                    <div class="list-group list-group-flush small">
                        <?php if (!empty($loginLogs)): ?>
                            <?php foreach (array_slice($loginLogs, 0, 5) as $ll): ?>
                                <div class="list-group-item px-0 py-2 border-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($ll['full_name'] ?? $ll['username'] ?? 'User') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($ll['role_name'] ?? 'User') ?> • IP: <?= htmlspecialchars($ll['ip_address'] ?? '127.0.0.1') ?></small>
                                    </div>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><?= date('H:i:s', strtotime($ll['login_time'] ?? $ll['created_at'])) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">Belum ada riwayat sesi login.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- Modal Add Pengumuman / Informasi (Admin Quick Create) -->
<div class="modal fade" id="modalAddPengumuman" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-megaphone-fill text-warning me-2"></i>Terbitkan Informasi / Pengumuman Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=admin/dashboard" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="create_pengumuman">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Judul Informasi / Pengumuman <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control" placeholder="Contoh: Pengumuman Ujian CBT Semester Ganjil" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Target Penerima Informasi</label>
                        <select name="target_role" class="form-select" required>
                            <option value="all">Semua Pengguna (Siswa & Guru)</option>
                            <option value="siswa">Khusus Siswa</option>
                            <option value="guru">Khusus Guru / Tenaga Pengajar</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold d-flex justify-content-between align-items-center">
                            <span>Gambar Banner Pengumuman</span>
                            <span class="badge bg-secondary-subtle text-secondary border">Opsional</span>
                        </label>
                        <input type="file" name="banner" class="form-control" accept="image/*">
                        <small class="text-muted d-block mt-1">Unggah header banner opsional (JPG, PNG, WEBP, GIF. Maks 10 MB).</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Isi Pesan Informasi / Pengumuman <span class="text-danger">*</span></label>
                        <textarea name="isi" class="form-control" rows="4" placeholder="Tuliskan pengumuman resmi sekolah secara jelas..." required></textarea>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_popup" value="1" id="switchPopup">
                        <label class="form-check-label small fw-bold text-dark" for="switchPopup">
                            Tampilkan sebagai Notifikasi Pop-Up saat Pengguna Login
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark px-4 fw-bold"><i class="bi bi-send-fill me-1"></i> Terbitkan Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Chart.js Realtime Script -->
<script>
(function() {
    function initDashboardCharts() {
        if (typeof Chart === 'undefined') {
            setTimeout(initDashboardCharts, 100);
            return;
        }

        // 1. Chart Aktivitas LMS Realtime (Bar Chart)
        const canvasLms = document.getElementById('adminLmsChart');
        if (canvasLms) {
            const ctxLms = canvasLms.getContext('2d');
            new Chart(ctxLms, {
                type: 'bar',
                data: {
                    labels: ['Guru', 'Siswa', 'Kelas', 'Materi', 'Tugas', 'Quiz'],
                    datasets: [{
                        label: 'Jumlah Realtime Data',
                        data: [
                            <?= (int)($stats['total_guru'] ?? 0) ?>,
                            <?= (int)($stats['total_siswa'] ?? 0) ?>,
                            <?= (int)($stats['total_kelas'] ?? 0) ?>,
                            <?= (int)($stats['total_materi'] ?? 0) ?>,
                            <?= (int)($stats['total_tugas'] ?? 0) ?>,
                            <?= (int)($stats['total_quiz'] ?? 0) ?>
                        ],
                        backgroundColor: [
                            '#0d6efd',
                            '#198754',
                            '#ffc107',
                            '#dc3545',
                            '#0dcaf0',
                            '#6f42c1'
                        ],
                        borderRadius: 10,
                        borderSkipped: false,
                        barPercentage: 0.65
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
                                    return ' ' + context.dataset.label + ': ' + context.parsed.y + ' Items';
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

        // 2. Chart Distribusi Siswa Per-Jurusan (Donut Chart)
        const canvasDonut = document.getElementById('jurusanDonutChart');
        if (canvasDonut) {
            <?php
            $jStats = $analytics['jurusan_stats'] ?? $analytics['jurusanStats'] ?? [];
            $jLabels = [];
            $jData = [];
            if (!empty($jStats)) {
                foreach ($jStats as $js) {
                    $jLabels[] = $js['nama_jurusan'];
                    $jData[] = (int)$js['total_siswa'];
                }
            } else {
                $jLabels = ['Belum Ada Jurusan'];
                $jData = [0];
            }
            ?>
            const ctxDonut = canvasDonut.getContext('2d');
            new Chart(ctxDonut, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($jLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($jData) ?>,
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
        document.addEventListener('DOMContentLoaded', initDashboardCharts);
    } else {
        initDashboardCharts();
    }
})();
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
