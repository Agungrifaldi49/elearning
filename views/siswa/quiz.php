<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* Modern LMS Quiz & CBT Module Architecture */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.quiz-page-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

/* Premium Glassmorphic Hero Banner */
.quiz-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0284c7 100%);
    border-radius: 20px;
    box-shadow: 0 12px 30px -5px rgba(15, 23, 42, 0.22);
    position: relative;
    overflow: hidden;
}

.quiz-hero-banner::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}

/* Card Architecture */
.quiz-card-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

@media (min-width: 768px) {
    .quiz-card-item {
        height: 100%;
    }
}

.quiz-card-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.09) !important;
    border-color: #cbd5e1;
}

/* Card Top Border Accents */
.quiz-card-item.status-active {
    border-top: 4px solid #2563eb !important;
}
.quiz-card-item.status-locked {
    border-top: 4px solid #f59e0b !important;
}
.quiz-card-item.status-expired {
    border-top: 4px solid #ef4444 !important;
}

.quiz-title-clamp {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
    font-size: 1.02rem;
    line-height: 1.35;
}

/* Custom KPI Stat Cards */
.student-kpi-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #ffffff;
    transition: all 0.2s ease;
}
.student-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
}

@media (max-width: 575.98px) {
    .student-kpi-card {
        padding: 0.65rem !important;
    }
    .student-kpi-card .fs-4 {
        font-size: 1.15rem !important;
    }
    .student-kpi-card small {
        font-size: 0.68rem !important;
    }
    .quiz-hero-banner {
        padding: 1.25rem !important;
        border-radius: 16px !important;
    }
}

.badge-mapel-tag {
    background-color: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-weight: 700;
    font-size: 0.78rem;
    padding: 6px 14px;
    border-radius: 20px;
}
</style>

<main class="main-content px-2 px-sm-3 px-md-4 py-3 quiz-page-wrapper">
<div class="container-fluid pt-3">
    
    <!-- Hero Banner Header -->
    <div class="quiz-hero-banner text-white p-4 p-md-5 mb-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-3 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-gradient p-3.5 rounded-4 text-white shadow-sm d-flex align-items-center justify-content-center" style="width: 58px; height: 58px;">
                    <i class="bi bi-patch-question-fill fs-2"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-white mb-1" style="letter-spacing: -0.4px;">Kuis & Ujian CBT Online</h3>
                    <p class="text-info-subtle small mb-0 fw-medium">Pilih kuis atau ujian CBT online aktif pada kelas terdaftar Anda.</p>
                </div>
            </div>

            <a href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" class="btn btn-warning text-dark fw-bold rounded-pill shadow-sm px-4 py-2.5 text-nowrap" style="font-size: 0.88rem; width: fit-content; max-width: 100%;">
                <i class="bi bi-key-fill me-1.5"></i> Daftar Mapel Baru (Key)
            </a>
        </div>
    </div>

    <!-- Class & Jurusan Scope Info Bar -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3.5 px-1">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-primary text-white shadow-xs px-3 py-1.5 rounded-pill fw-bold" style="font-size: 0.8rem;">
                <i class="bi bi-building me-1.5"></i>Kelas: <?= htmlspecialchars($siswa['nama_kelas'] ?? 'Rombel Kelas') ?>
            </span>
            <span class="badge bg-dark text-white shadow-xs px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                <i class="bi bi-mortarboard-fill me-1.5"></i>Jurusan: <?= htmlspecialchars($siswa['nama_jurusan'] ?? 'Umum') ?>
            </span>
        </div>
        <small class="text-secondary fw-semibold" style="font-size: 0.8rem;">
            <i class="bi bi-shield-check text-success me-1"></i>Filter khusus kelas & jurusan Anda
        </small>
    </div>

    <!-- 📊 KPI SUMMARY CARDS FOR STUDENT -->
    <?php
    $totalQuizzesStudent = count($quizList ?? []);
    $completedQuizzesCount = count($completedMap ?? []);
    $readyQuizzesCount = 0;
    $sumScores = 0;

    if (!empty($quizList)) {
        foreach ($quizList as $qItem) {
            if (isset($completedMap[$qItem['id']])) {
                $score = (float)($completedMap[$qItem['id']]['nilai_tertinggi'] ?? $completedMap[$qItem['id']]['total_nilai'] ?? 0);
                $sumScores += $score;
            } else {
                $readyQuizzesCount++;
            }
        }
    }
    $avgScore = $completedQuizzesCount > 0 ? round($sumScores / $completedQuizzesCount, 1) : 0;
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="student-kpi-card p-3 text-center h-100">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-collection-play-fill fs-5"></i>
                    </div>
                    <span class="fw-bold text-primary fs-4"><?= $totalQuizzesStudent ?></span>
                </div>
                <small class="text-muted fw-semibold">Total Kuis Tersedia</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="student-kpi-card p-3 text-center h-100">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </div>
                    <span class="fw-bold text-success fs-4"><?= $completedQuizzesCount ?></span>
                </div>
                <small class="text-muted fw-semibold">Kuis Selesai</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="student-kpi-card p-3 text-center h-100">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-play-circle-fill fs-5"></i>
                    </div>
                    <span class="fw-bold text-info fs-4"><?= $readyQuizzesCount ?></span>
                </div>
                <small class="text-muted fw-semibold">Belum Dikerjakan</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="student-kpi-card p-3 text-center h-100">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                    <div class="bg-warning bg-opacity-15 text-warning-emphasis rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-trophy-fill fs-5"></i>
                    </div>
                    <span class="fw-bold text-warning-emphasis fs-4"><?= number_format($avgScore, 1) ?></span>
                </div>
                <small class="text-muted fw-semibold">Rata-Rata Nilai</small>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls Card -->
    <div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchInput" class="form-control bg-light border-start-0 rounded-end-pill ps-0" placeholder="Cari judul kuis atau nama mapel..." oninput="filterQuizItems()" style="font-size: 0.88rem;">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select id="filterMapel" class="form-select rounded-pill" onchange="filterQuizItems()" style="font-size: 0.85rem;">
                    <option value="">Semua Mata Pelajaran</option>
                    <?php 
                    $mapelNames = array_unique(array_column($quizList, 'nama_mapel'));
                    foreach ($mapelNames as $mName):
                    ?>
                        <option value="<?= htmlspecialchars($mName) ?>"><?= htmlspecialchars($mName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select id="filterStatus" class="form-select rounded-pill" onchange="filterQuizItems()" style="font-size: 0.85rem;">
                    <option value="">Semua Status</option>
                    <option value="terdaftar">Terdaftar / Aktif</option>
                    <option value="terkunci">Terkunci / Expired</option>
                    <option value="didiskualifikasi">Didiskualifikasi</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Quiz Cards Grid -->
    <div class="row g-4 mb-4">
        <?php if (empty($quizList)): ?>
            <div class="col-12">
                <div class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white">
                    <div class="bg-slate-100 text-slate-400 rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 75px; height: 75px; background-color: #f1f5f9;">
                        <i class="bi bi-stopwatch fs-1 text-slate-400"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Belum Ada Ujian / Kuis Aktif</h5>
                    <p class="text-muted small mb-0">Belum ada kuis atau ujian CBT online yang tersedia untuk Anda saat ini.</p>
                </div>
            </div>
        <?php else: ?>
            <div id="emptyFilterNotice" class="col-12 text-center d-none py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-search fs-1 text-secondary d-block mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">Kuis Tidak Ditemukan</h6>
                <p class="text-muted small mb-0">Tidak ada kuis yang sesuai dengan pencarian atau filter yang Anda pilih.</p>
            </div>

            <?php foreach ($quizList as $q): 
                $isEnrolled = isset($enrolledMapels[$q['mapel_id'] . '_' . $q['guru_id']]) || isset($enrolledMapels[$q['mapel_id']]);
                $accessCheck = $examModel->canSiswaAccessQuiz($q['id'], $siswaId);
                $isExpired = $accessCheck['is_expired'] ?? false;
                $canAccess = $accessCheck['access'] ?? false;
                $statusAccess = $accessCheck['status'] ?? 'terbuka';
                $isDisqualified = ($statusAccess === 'didiskualifikasi');
                $cardStatusClass = !$isEnrolled ? 'status-locked' : ($isDisqualified || ($isExpired && !$canAccess) ? 'status-expired' : 'status-active');
                $statusCardVal = $isDisqualified ? 'didiskualifikasi' : (!$isEnrolled ? 'terkunci' : ($isExpired && !$canAccess ? 'terkunci' : 'terdaftar'));
            ?>
                <div class="col-12 col-md-6 col-xl-4 quiz-item-col" data-title="<?= htmlspecialchars(strtolower($q['judul'])) ?>" data-mapel="<?= htmlspecialchars($q['nama_mapel']) ?>" data-status="<?= $statusCardVal ?>">
                    <div class="quiz-card-item <?= $cardStatusClass ?> p-3.5 p-sm-4 shadow-sm border-0 rounded-4 d-flex flex-column justify-content-between text-center">
                        <div>
                            <!-- Top Bar: Category Pill & Status Pill -->
                            <div class="d-flex align-items-center justify-content-between mb-3 gap-2">
                                <div>
                                    <?php if (($q['kategori'] ?? '') === 'uts'): ?>
                                        <span class="badge text-white rounded-pill px-2.5 py-1" style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); font-size:0.7rem;"><i class="bi bi-trophy-fill me-1"></i>UTS</span>
                                    <?php elseif (($q['kategori'] ?? '') === 'uas'): ?>
                                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-1" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); font-size:0.7rem;"><i class="bi bi-award-fill me-1"></i>UAS</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary text-white rounded-pill px-2.5 py-1" style="font-size:0.7rem;"><i class="bi bi-file-text me-1"></i>Kuis Harian</span>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <?php if ($isDisqualified): ?>
                                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.68rem;">Diskualifikasi</span>
                                    <?php elseif ($isExpired): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.68rem;">Expired</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.68rem;">Aktif</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- CENTER AREA: Icon Avatar & Subject Name -->
                            <div class="my-2 text-center">
                                <div class="rounded-circle <?= !$canAccess ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> d-inline-flex align-items-center justify-content-center shadow-xs mb-2" style="width: 52px; height: 52px;">
                                    <i class="bi <?= !$canAccess ? 'bi-lock-fill' : 'bi-stopwatch-fill' ?> fs-3"></i>
                                </div>
                                <div>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold" style="font-size:0.8rem;" title="<?= htmlspecialchars($q['nama_mapel']) ?>">
                                        <i class="bi bi-journal-bookmark-fill me-1.5"></i><?= htmlspecialchars($q['nama_mapel']) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Quiz Title & Teacher Info -->
                            <h5 class="fw-bold text-dark mb-1.5 quiz-title-clamp text-center px-1" title="<?= htmlspecialchars($q['judul']) ?>">
                                <?= htmlspecialchars($q['judul']) ?>
                            </h5>
                            <div class="mb-3 text-center">
                                <span class="badge rounded-pill px-2.5 py-1 font-monospace border" style="font-size: 0.72rem; background: #f8fafc; color: #475569; border-color: #e2e8f0 !important;">
                                    <i class="bi bi-person-fill text-primary me-1"></i>Guru: <?= htmlspecialchars($q['nama_guru'] ?? 'Guru Pengampu') ?>
                                </span>
                            </div>

                            <!-- Meta Info Specs Box (Duration & Attempts & Deadline) -->
                            <div class="p-3 rounded-3 mb-3 border" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                                <div class="row g-2 text-center" style="font-size: 0.78rem;">
                                    <div class="col-6 border-end">
                                        <span class="text-muted d-block mb-0.5" style="font-size:0.72rem;"><i class="bi bi-clock-history me-1 text-warning"></i>Durasi Ujian</span>
                                        <strong class="text-dark fs-6"><?= $q['durasi_menit'] ?> Menit</strong>
                                    </div>
                                    <div class="col-6">
                                        <?php 
                                        $maxAttemptsVal = (int)($q['max_attempts'] ?? 1);
                                        $currentAttemptDone = isset($completedMap[$q['id']]) ? (int)($completedMap[$q['id']]['attempt_count'] ?? 1) : 0;
                                        $sisaAttempts = ($maxAttemptsVal == 0) ? '∞' : max(0, $maxAttemptsVal - $currentAttemptDone);
                                        ?>
                                        <span class="text-muted d-block mb-0.5" style="font-size:0.72rem;"><i class="bi bi-arrow-repeat me-1 text-primary"></i>Sisa Kesempatan</span>
                                        <strong class="text-dark fs-6"><?= $sisaAttempts ?>x</strong>
                                    </div>
                                </div>
                                <?php if (!empty($q['deadline'])): ?>
                                    <div class="mt-2 pt-2 border-top text-center text-danger fw-semibold" style="font-size: 0.73rem;">
                                        <i class="bi bi-calendar-event me-1"></i>Deadline: <?= date('d M Y, H:i', strtotime($q['deadline'])) ?> WIB
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Action Button Area -->
                        <div class="pt-1">
                            <?php 
                            $needsToken = in_array($q['kategori'] ?? '', ['uts', 'uas']) && !empty($q['access_key']) && empty($_SESSION['quiz_access_key_' . $q['id']]);
                            $susulanStatus = $accessCheck['status'] ?? '';
                            ?>
                            <?php if (!$isEnrolled): ?>
                                <a href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" class="btn btn-warning text-dark w-100 fw-bold rounded-pill shadow-xs py-2 d-flex align-items-center justify-content-center gap-1">
                                    <i class="bi bi-key-fill me-1"></i> Input Key Mapel (Terkunci <i class="bi bi-lock-fill"></i>)
                                </a>

                            <?php elseif ($susulanStatus === 'disetujui_susulan'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle mb-2 d-block py-2 rounded-pill fw-bold small text-wrap text-center">
                                    <i class="bi bi-check-circle-fill me-1"></i>Izin Susulan Disetujui Guru
                                </span>
                                <?php if ($needsToken): ?>
                                    <button type="button" class="btn btn-warning text-dark w-100 fw-bold rounded-pill shadow-xs py-2 d-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#modalAccessKey<?= $q['id'] ?>">
                                        <i class="bi bi-key-fill me-1"></i> Token Ujian (<?= strtoupper($q['kategori']) ?>)
                                    </button>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>index.php?url=siswa/quiz&id=<?= $q['id'] ?>" class="btn btn-success w-100 fw-bold rounded-pill shadow-sm py-2 text-white">
                                        <i class="bi bi-play-circle-fill me-1"></i> Kerjakan Ujian Susulan
                                    </a>
                                <?php endif; ?>

                            <?php elseif ($susulanStatus === 'pending'): ?>
                                <button type="button" class="btn btn-warning text-dark w-100 fw-bold rounded-pill shadow-xs py-2 w-100" disabled>
                                    <i class="bi bi-hourglass-split me-1"></i> Permohonan Susulan Terkirim
                                </button>

                            <?php elseif ($susulanStatus === 'ditolak'): ?>
                                <button type="button" class="btn btn-outline-danger w-100 fw-bold rounded-pill shadow-xs py-2 w-100" disabled>
                                    <i class="bi bi-x-circle-fill me-1"></i> Susulan Ditolak Guru
                                </button>

                            <?php elseif ($isDisqualified): ?>
                                <button type="button" class="btn btn-outline-danger w-100 fw-bold rounded-pill shadow-xs py-2 d-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#modalSusulan<?= $q['id'] ?>">
                                    <i class="bi bi-shield-x me-1"></i> Minta Buka Kunci / Susulan
                                </button>

                            <?php elseif ($needsToken): ?>
                                <button type="button" class="btn btn-warning text-dark w-100 fw-bold rounded-pill shadow-xs py-2 d-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#modalAccessKey<?= $q['id'] ?>">
                                    <i class="bi bi-key-fill me-1"></i> Masukkan Token Kunci Ujian (<?= strtoupper($q['kategori']) ?>)
                                </button>

                            <?php elseif (isset($completedMap[$q['id']])): 
                                $comp = $completedMap[$q['id']];
                                $attCount = (int)($comp['attempt_count'] ?? 1);
                                $maxAtt = (int)($q['max_attempts'] ?? 1);
                                $highestScore = (float)($comp['nilai_tertinggi'] ?? $comp['total_nilai'] ?? 0);
                                $canReattempt = ($maxAtt == 0 || $attCount < $maxAtt);
                            ?>
                                <div class="p-2.5 mb-2 rounded-3 text-center border" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(5, 150, 105, 0.15) 100%); border-color: rgba(16, 185, 129, 0.3) !important;">
                                    <div class="d-flex align-items-center justify-content-center gap-1 text-success fw-bold">
                                        <i class="bi bi-trophy-fill text-warning fs-6"></i>
                                        <span class="fs-6">Nilai Tertinggi: <?= number_format($highestScore, 1) ?></span>
                                    </div>
                                    <span class="text-muted d-block mt-0.5" style="font-size: 0.71rem;">(Percobaan ke-<?= $attCount ?> dari <?= $maxAtt > 0 ? $maxAtt.'x' : '∞' ?>)</span>
                                </div>

                                <div class="d-flex gap-2">
                                    <?php if ($canReattempt && $canAccess): ?>
                                        <a href="<?= BASE_URL ?>index.php?url=siswa/quiz&id=<?= $q['id'] ?>" onclick="localStorage.removeItem('cbt_violation_locked_<?= $q['id'] ?>'); sessionStorage.removeItem('cbt_warning_count_<?= $q['id'] ?>'); sessionStorage.removeItem('cbt_remaining_time_<?= $q['id'] ?>');" class="btn btn-primary flex-grow-1 fw-bold rounded-pill shadow-sm py-2 text-white small" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                                            <i class="bi bi-arrow-repeat me-1"></i> Kerjakan Ulang
                                        </a>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline-danger flex-grow-1 fw-bold rounded-pill shadow-xs py-2 small d-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#modalSusulan<?= $q['id'] ?>" style="font-size:0.75rem;">
                                            <i class="bi bi-shield-x me-1"></i> Minta Buka Kunci / Susulan
                                        </button>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>index.php?url=siswa/reviewQuiz&id=<?= $q['id'] ?>" class="btn btn-outline-secondary fw-bold rounded-pill py-2 small" style="min-width:75px;">
                                        <i class="bi bi-file-earmark-check-fill me-1"></i> Review
                                    </a>
                                </div>

                            <?php else: ?>
                                <?php if ($canAccess): ?>
                                    <a href="<?= BASE_URL ?>index.php?url=siswa/quiz&id=<?= $q['id'] ?>" onclick="localStorage.removeItem('cbt_violation_locked_<?= $q['id'] ?>'); sessionStorage.removeItem('cbt_warning_count_<?= $q['id'] ?>'); sessionStorage.removeItem('cbt_remaining_time_<?= $q['id'] ?>');" class="btn btn-primary w-100 fw-bold rounded-pill shadow-sm py-2.5 text-white" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                                        <i class="bi bi-pencil-square me-1"></i> Kerjakan Ujian Sekarang
                                    </a>
                                <?php else: ?>
                                    <button type="button" class="btn btn-outline-danger w-100 fw-bold rounded-pill shadow-xs py-2.5 d-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#modalSusulan<?= $q['id'] ?>">
                                        <i class="bi bi-lock-fill me-1"></i> Terkunci (Minta Izin Susulan)
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Modal Access Key Prompt for UTS / UAS -->
                <div class="modal fade" id="modalAccessKey<?= $q['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                            <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-warning rounded-3 p-2 text-dark shadow-xs">
                                        <i class="bi bi-key-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="modal-title fw-bold text-white mb-0">Token Kunci Ujian: <?= strtoupper($q['kategori'] ?? 'UTS') ?></h6>
                                        <small class="text-warning fw-medium" style="font-size:0.75rem;">Masukkan Kunci Akses dari Guru atau Admin</small>
                                    </div>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="<?= BASE_URL ?>index.php?url=siswa/quiz" method="POST">
                                <div class="modal-body p-4 bg-light text-start">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="verify_access_key">
                                    <input type="hidden" name="quiz_id" value="<?= $q['id'] ?>">

                                    <div class="p-3 bg-white rounded-3 border mb-3">
                                        <small class="text-muted d-block">Paket Ujian Target:</small>
                                        <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($q['judul']) ?></h6>
                                        <small class="text-muted">Mapel: <?= htmlspecialchars($q['nama_mapel']) ?> | Durasi: <?= $q['durasi_menit'] ?> Menit</small>
                                    </div>

                                    <div class="alert alert-warning border-0 rounded-3 p-3 mb-3 shadow-xs">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-shield-lock-fill text-warning fs-4"></i>
                                            <div>
                                                <small class="fw-bold text-dark d-block">Token Akses Ujian Diperlukan!</small>
                                                <small class="text-muted" style="font-size:0.75rem;">Pelaksanaan ujian <?= strtoupper($q['kategori'] ?? 'UTS') ?> ini menggunakan Token Kunci. Tanyakan Token kepada Guru Pengampu atau Admin Sekolah.</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small fw-bold text-dark"><i class="bi bi-key me-1 text-danger"></i>Masukkan Token Kunci Ujian <span class="text-danger">*</span></label>
                                        <input type="text" name="access_key" class="form-control form-control-lg text-uppercase fw-bold font-monospace text-center letter-spacing-2" placeholder="Contoh: UTS2026" required autocomplete="off">
                                    </div>
                                </div>
                                <div class="modal-footer border-0 p-3 bg-white border-top justify-content-between">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                        <i class="bi bi-unlock-fill me-1.5"></i> Verifikasi Token & Masuk
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Form Pengajuan Ujian Susulan -->
                <div class="modal fade" id="modalSusulan<?= $q['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                            <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-danger rounded-3 p-2 text-white shadow-xs">
                                        <i class="bi bi-shield-lock-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="modal-title fw-bold text-white mb-0">Permohonan Izin Ujian Susulan</h6>
                                        <small class="text-info fw-medium" style="font-size:0.75rem;">Pengajuan Membuka Kunci Kuis Online</small>
                                    </div>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="<?= BASE_URL ?>index.php?url=siswa/quiz" method="POST">
                                <div class="modal-body p-4">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="request_susulan">
                                    <input type="hidden" name="quiz_id" value="<?= $q['id'] ?>">

                                    <div class="p-3 bg-slate-50 rounded-3 border border-slate-200 mb-3 text-start" style="background-color: #f8fafc;">
                                        <h6 class="fw-bold text-primary mb-1"><?= htmlspecialchars($q['judul']) ?></h6>
                                        <small class="text-muted d-block mb-1">Guru Pengampu: <?= htmlspecialchars($q['nama_guru']) ?></small>
                                        <small class="text-danger fw-bold"><i class="bi bi-lock-fill me-1"></i>Status Akses: Terkunci (Perlu Izin Guru)</small>
                                    </div>

                                    <div class="mb-3 text-start">
                                        <label class="form-label small fw-bold text-slate-700">Alasan / Catatan Pengajuan Susulan <span class="text-danger">*</span></label>
                                        <textarea name="catatan_susulan" class="form-control rounded-3" rows="3" required placeholder="<?= $isDisqualified ? 'Tuliskan permohonan izin ujian susulan kembali kepada Guru...' : 'Tuliskan alasan Anda terlambat / ingin mengajukan ujian susulan kepada Guru...' ?>"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0 p-4 justify-content-between gap-2">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger px-4 fw-bold rounded-pill text-white shadow-xs" style="background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);">
                                        <i class="bi bi-send-fill me-1"></i> Kirim Permohonan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</main>

<script>
function filterQuizItems() {
    const searchVal = document.getElementById('searchInput').value.toLowerCase().trim();
    const mapelVal = document.getElementById('filterMapel').value;
    const statusVal = document.getElementById('filterStatus').value;

    const items = document.querySelectorAll('.quiz-item-col');
    let visibleCount = 0;

    items.forEach(item => {
        const title = item.getAttribute('data-title') || '';
        const mapel = item.getAttribute('data-mapel') || '';
        const status = item.getAttribute('data-status') || '';

        const matchSearch = title.includes(searchVal) || mapel.toLowerCase().includes(searchVal);
        const matchMapel = (mapelVal === '' || mapel === mapelVal);
        const matchStatus = (statusVal === '' || status === statusVal);

        if (matchSearch && matchMapel && matchStatus) {
            item.classList.remove('d-none');
            visibleCount++;
        } else {
            item.classList.add('d-none');
        }
    });

    const emptyNotice = document.getElementById('emptyFilterNotice');
    if (emptyNotice) {
        if (visibleCount === 0) {
            emptyNotice.classList.remove('d-none');
        } else {
            emptyNotice.classList.add('d-none');
        }
    }
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
