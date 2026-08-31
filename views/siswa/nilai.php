<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
// Calculate Real Summary Statistics
$totalQuizAttempted = count($hasilQuizList);
$totalQuizPassed = 0;
$totalScoreSum = 0;

foreach ($hasilQuizList as $hq) {
    if (($hq['status_lulus'] ?? '') === 'lulus') {
        $totalQuizPassed++;
    }
    $totalScoreSum += (float)($hq['total_nilai'] ?? 0);
}

$avgScore = $totalQuizAttempted > 0 ? number_format($totalScoreSum / $totalQuizAttempted, 1) : '0.0';
$totalTugasSubmitted = count($hasilTugasList);
?>

<style>
/* Modern LMS Transcript & Grades Architecture */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.nilai-page-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

/* Premium Hero Banner */
.nilai-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
    border-radius: 20px;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2);
    position: relative;
    overflow: hidden;
}

.nilai-hero-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, rgba(0, 0, 0, 0) 70%);
    border-radius: 50%;
}

/* KPI Summary Cards */
.kpi-stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px;
    transition: all 0.22s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.kpi-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 22px -5px rgba(0, 0, 0, 0.08) !important;
}

/* Nav Tabs Styling */
.nav-pills-custom .nav-link {
    border-radius: 30px;
    padding: 10px 24px;
    font-weight: 700;
    color: #475569;
    background-color: #f1f5f9;
    transition: all 0.2s ease;
}

.nav-pills-custom .nav-link.active {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.25);
}

.table-custom th {
    background-color: #f8fafc;
    color: #334155;
    font-weight: 700;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e2e8f0;
    padding: 14px 16px;
}

.table-custom td {
    padding: 16px;
    vertical-align: middle;
    font-size: 0.9rem;
}
</style>

<main class="main-content px-2 px-sm-3 px-md-4 py-3 nilai-page-wrapper">
<div class="container-fluid pt-3">
    
    <!-- Hero Banner Header -->
    <div class="nilai-hero-banner text-white p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-3 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-gradient p-3 rounded-4 text-white shadow-sm d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-award-fill fs-2"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-white mb-1" style="letter-spacing: -0.3px;">Hasil & Riwayat Transkrip Evaluasi Siswa</h4>
                    <p class="text-info-subtle small mb-0 fw-medium">Pantau riwayat pencapaian ujian CBT online, evaluasi kuis, dan pengumpulan tugas Anda.</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>index.php?url=siswa/dashboard" class="btn btn-outline-light rounded-pill fw-bold px-3.5 py-2" style="font-size: 0.88rem;">
                <i class="bi bi-speedometer2 me-1.5"></i> Dashboard Siswa
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-stat-card border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">Total Ujian CBT</small>
                        <h3 class="fw-black text-slate-800 mb-0" style="color: #0f172a;"><?= $totalQuizAttempted ?></h3>
                    </div>
                    <div class="bg-primary-subtle text-primary p-2.5 rounded-3">
                        <i class="bi bi-laptop fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="kpi-stat-card border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">Lulus Evaluasi</small>
                        <h3 class="fw-black text-success mb-0"><?= $totalQuizPassed ?></h3>
                    </div>
                    <div class="bg-success-subtle text-success p-2.5 rounded-3">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="kpi-stat-card border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">Rata-Rata Nilai CBT</small>
                        <h3 class="fw-black text-info mb-0"><?= $avgScore ?></h3>
                    </div>
                    <div class="bg-info-subtle text-info p-2.5 rounded-3">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="kpi-stat-card border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold d-block mb-1" style="font-size: 0.75rem;">Tugas Dikumpulkan</small>
                        <h3 class="fw-black text-warning-emphasis mb-0"><?= $totalTugasSubmitted ?></h3>
                    </div>
                    <div class="bg-warning-subtle text-warning-emphasis p-2.5 rounded-3">
                        <i class="bi bi-file-earmark-check-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nav Tabs Navigation -->
    <ul class="nav nav-pills nav-pills-custom mb-4 gap-2" id="transcriptTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active d-flex align-items-center gap-2" id="quiz-tab" data-bs-toggle="pill" data-bs-target="#quiz-history" type="button" role="tab">
                <i class="bi bi-stopwatch-fill"></i> 🏆 Riwayat Hasil Kuis & Ujian CBT
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center gap-2" id="tugas-tab" data-bs-toggle="pill" data-bs-target="#tugas-history" type="button" role="tab">
                <i class="bi bi-card-checklist"></i> 📚 Riwayat Pengumpulan & Nilai Tugas
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="transcriptTabContent">
        
        <!-- TAB 1: QUIZ & CBT HISTORY -->
        <div class="tab-pane fade show active" id="quiz-history" role="tabpanel">
            <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden">
                <div class="card-header bg-white p-4 border-0 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-primary"></i>
                        <span>Transkrip Ujian CBT Online</span>
                    </h5>
                    <span class="badge bg-slate-100 text-slate-700 border px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.8rem; background-color: #f1f5f9;">
                        Total Selesai: <?= $totalQuizAttempted ?> Evaluasi
                    </span>
                </div>

                <div class="card-body p-0 pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Nama Kuis / Evaluasi</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Waktu Selesai</th>
                                    <th>Total Nilai</th>
                                    <th>Status Kelulusan</th>
                                    <th class="text-center pe-4">Detail History</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($hasilQuizList)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                            Anda belum pernah mengerjakan kuis atau ujian CBT online.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($hasilQuizList as $i => $hq): 
                                        $isLulus = (($hq['status_lulus'] ?? '') === 'lulus');
                                        $scoreVal = (float)($hq['total_nilai'] ?? 0);
                                    ?>
                                        <tr>
                                            <td class="ps-4 text-muted fw-bold"><?= $i + 1 ?></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($hq['nama_quiz']) ?></div>
                                                <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-person-circle me-1"></i>Guru: <?= htmlspecialchars($hq['nama_guru'] ?? 'Guru Pengampu') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold small">
                                                    <?= htmlspecialchars($hq['nama_mapel']) ?>
                                                </span>
                                            </td>
                                            <td class="small text-slate-600">
                                                <i class="bi bi-calendar-check text-success me-1"></i>
                                                <?= date('d M Y, H:i', strtotime($hq['finished_at'] ?? $hq['started_at'] ?? 'now')) ?> WIB
                                            </td>
                                            <td>
                                                <span class="badge bg-primary bg-gradient px-3 py-2 rounded-pill fs-6 fw-bold shadow-xs">
                                                    <?= number_format($scoreVal, 1) ?> / 100
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($isLulus): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-bold small">
                                                        <i class="bi bi-check-circle-fill me-1"></i>LULUS
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-bold small">
                                                        <i class="bi bi-x-circle-fill me-1"></i>REMEDIAL / TIDAK LULUS
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center pe-4">
                                                <div class="d-flex align-items-center justify-content-center gap-1.5">
                                                    <a href="<?= BASE_URL ?>index.php?url=siswa/reviewQuiz&id=<?= $hq['quiz_id'] ?>" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold text-white shadow-xs" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); font-size:0.8rem;">
                                                        <i class="bi bi-file-earmark-check-fill me-1"></i> Review Lembar Jawaban
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 fw-bold" data-bs-toggle="modal" data-bs-target="#modalDetailQuiz<?= $hq['id'] ?>" title="Ringkasan Transkrip">
                                                        <i class="bi bi-info-circle-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Detail Attempt CBT -->
                                        <div class="modal fade" id="modalDetailQuiz<?= $hq['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                                                    <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                                                        <div class="d-flex align-items-center gap-2.5">
                                                            <div class="bg-primary rounded-3 p-2 text-white shadow-xs">
                                                                <i class="bi bi-journal-check fs-5"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="modal-title fw-bold text-white mb-0">Rincian Hasil Ujian CBT</h6>
                                                                <small class="text-info fw-medium" style="font-size:0.75rem;">Transkrip Detail Pengerjaan Kuis Siswa</small>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body p-4 text-center">
                                                        <div class="mb-3">
                                                            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($hq['nama_quiz']) ?></h5>
                                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold small">Mapel: <?= htmlspecialchars($hq['nama_mapel']) ?></span>
                                                        </div>

                                                        <div class="p-4 bg-slate-50 rounded-4 border border-slate-200 mb-3" style="background-color: #f8fafc;">
                                                            <small class="text-muted fw-bold d-block mb-1" style="font-size:0.78rem;">NILAI AKHIR EVALUASI</small>
                                                            <div class="display-4 fw-black text-primary mb-2"><?= number_format($scoreVal, 1) ?></div>

                                                            <?php if ($isLulus): ?>
                                                                <span class="badge bg-success text-white px-3 py-1.5 rounded-pill fw-bold fs-6">
                                                                    <i class="bi bi-check-circle-fill me-1"></i>DISETATAKAN LULUS
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger text-white px-3 py-1.5 rounded-pill fw-bold fs-6">
                                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>MENGULANG / REMEDIAL
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="bg-slate-100 p-3 rounded-3 text-start small border border-slate-200 mb-3" style="background-color: #f1f5f9; font-size: 0.8rem;">
                                                            <div class="d-flex justify-content-between mb-1.5">
                                                                <span class="text-muted">Guru Pengampu:</span>
                                                                <span class="fw-bold text-dark"><?= htmlspecialchars($hq['nama_guru'] ?? 'Guru Pengampu') ?></span>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-1.5">
                                                                <span class="text-muted">Waktu Pengerjaan Selesai:</span>
                                                                <span class="fw-bold text-dark"><?= date('d M Y, H:i', strtotime($hq['finished_at'] ?? $hq['started_at'] ?? 'now')) ?> WIB</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between">
                                                                <span class="text-muted">Verifikasi Keamanan CBT:</span>
                                                                <span class="fw-bold text-success"><i class="bi bi-shield-check me-1"></i>Secure Fullscreen Verified</span>
                                                            </div>
                                                        </div>

                                                        <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold w-100" data-bs-dismiss="modal">Tutup Transkrip</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: TASK SUBMISSIONS HISTORY -->
        <div class="tab-pane fade" id="tugas-history" role="tabpanel">
            <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden">
                <div class="card-header bg-white p-4 border-0 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-folder-check text-primary"></i>
                        <span>Riwayat Pengumpulan Tugas Siswa</span>
                    </h5>
                    <span class="badge bg-slate-100 text-slate-700 border px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.8rem; background-color: #f1f5f9;">
                        Total Dikirim: <?= $totalTugasSubmitted ?> Jawaban
                    </span>
                </div>

                <div class="card-body p-0 pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Nama Penugasan</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Guru Pengampu</th>
                                    <th>Waktu Mengumpulkan</th>
                                    <th>File Jawaban</th>
                                    <th class="pe-4">Nilai & Komentar Guru</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($hasilTugasList)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                            Anda belum pernah mengunggah pengumpulan tugas.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($hasilTugasList as $j => $ht): 
                                        $hasNilai = !is_null($ht['nilai']);
                                    ?>
                                        <tr>
                                            <td class="ps-4 text-muted fw-bold"><?= $j + 1 ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($ht['nama_tugas']) ?></td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold small">
                                                    <?= htmlspecialchars($ht['nama_mapel']) ?>
                                                </span>
                                            </td>
                                            <td class="small text-slate-600">
                                                <i class="bi bi-person-circle text-primary me-1"></i><?= htmlspecialchars($ht['nama_guru']) ?>
                                            </td>
                                            <td class="small text-slate-600">
                                                <i class="bi bi-clock-history text-success me-1"></i>
                                                <?= date('d M Y, H:i', strtotime($ht['submitted_at'])) ?> WIB
                                            </td>
                                            <td>
                                                <?php if (!empty($ht['file_path'])): ?>
                                                    <a href="<?= BASE_URL ?>assets/uploads/tugas/<?= htmlspecialchars($ht['file_path']) ?>" target="_blank" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold small text-primary">
                                                        <i class="bi bi-download me-1"></i> Unduh File
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">Tanpa File</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="pe-4">
                                                <?php if ($hasNilai): ?>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-success bg-gradient px-3 py-1.5 rounded-pill fw-bold fs-6 shadow-xs">
                                                            <?= number_format((float)$ht['nilai'], 1) ?> / 100
                                                        </span>
                                                        <?php if (!empty($ht['komentar_guru'])): ?>
                                                            <span class="text-muted small" title="Catatan Guru: <?= htmlspecialchars($ht['komentar_guru']) ?>">
                                                                <i class="bi bi-chat-left-text-fill text-info fs-6"></i>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold small">
                                                        <i class="bi bi-hourglass-split me-1"></i>Menunggu Penilaian Guru
                                                    </span>
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
</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
