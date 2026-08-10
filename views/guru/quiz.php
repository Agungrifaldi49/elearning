<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* Modern Quiz Guru Portal Styling */
.quiz-guru-page-wrapper {
    padding-top: 28px !important;
}

/* Glassmorphic Hero Banner */
.quiz-guru-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0284c7 100%);
    border-radius: 20px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
    position: relative;
    overflow: hidden;
}

.quiz-guru-hero::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}

/* Stat Cards */
.kpi-quiz-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    transition: all 0.25s ease;
}
.kpi-quiz-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06);
}

.kpi-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
}

/* Tab Navigation Styling */
.quiz-nav-tabs .nav-link {
    border: none;
    color: #64748b;
    font-weight: 600;
    padding: 12px 20px;
    border-radius: 12px;
    transition: all 0.2s ease;
    font-size: 0.92rem;
}
.quiz-nav-tabs .nav-link:hover {
    color: #0284c7;
    background-color: rgba(2, 132, 199, 0.06);
}
.quiz-nav-tabs .nav-link.active {
    color: #ffffff !important;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
}

/* Table Card Styling */
.table-card-custom {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    border-top: 4px solid #0284c7;
}

/* Badge Tags */
.badge-mapel-tag {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
    font-weight: 700;
    font-size: 0.76rem;
    padding: 5px 12px;
    border-radius: 50rem;
}

/* Responsive Table Overrides */
@media (max-width: 767.98px) {
    .quiz-guru-hero {
        padding: 20px !important;
    }
    .quiz-nav-tabs .nav-link {
        padding: 8px 14px;
        font-size: 0.82rem;
    }
}
</style>

<?php 
$isAdminMonitoring = (strtolower(AuthHelper::user()['role_name'] ?? '') === 'administrator');
$hasilQuizSubmissions = $hasilQuizSubmissions ?? [];
$susulanRequests = $susulanRequests ?? [];

$activeTab = $_GET['tab'] ?? 'paket';
if (!in_array($activeTab, ['paket', 'koreksi', 'susulan'])) {
    $activeTab = 'paket';
}
?>

<main class="main-content px-3 px-md-4 quiz-guru-page-wrapper pt-4 mt-4 mt-md-5">
    <div class="container-fluid">
        <?php if ($isAdminMonitoring): ?>
            <div class="alert alert-info border-0 rounded-4 p-3 mb-4 shadow-sm d-flex align-items-center gap-3" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-left: 5px solid #0284c7 !important;">
                <div class="bg-primary text-white p-2.5 rounded-3 shadow-xs">
                    <i class="bi bi-shield-lock-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-eye-fill me-1 text-primary"></i>Mode Monitoring Administrator (Pengawasan Guru)</h6>
                    <small class="text-secondary fw-medium">Secara hak akses, Administrator hanya berwenang **mengawasi / memantau (Monitoring Only)** data kuis, UTS, UAS, dan CBT online. Admin dapat memantau butir soal dan hasil ujian siswa tanpa membuat, mengedit, atau menghapus kuis milik Guru.</small>
                </div>
            </div>
        <?php endif; ?>

        <!-- 🚀 HERO BANNER GURU -->
        <div class="quiz-guru-hero p-4 p-md-5 mb-4">
            <div class="row align-items-center relative-zIndex-1">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="d-inline-flex align-items-center gap-2 px-3.5 py-2 rounded-pill bg-warning text-dark shadow-sm small fw-bold mb-3">
                        <i class="bi bi-patch-check-fill text-dark fs-6"></i>
                        <span>Control Center Evaluasi Guru & CBT</span>
                    </div>
                    <h2 class="fw-bold mb-2 text-white" style="letter-spacing: -0.5px;">Kelola Quiz & Bank Soal CBT Online</h2>
                    <p class="text-white text-opacity-85 small mb-0 lh-lg" style="max-width: 650px;">
                        Buat paket kuis interaktif (Pilihan Ganda, Essay, True/False), atur kesempatan pengerjaan, deadline, serta lakukan koreksi penilaian essay siswa secara terpusat.
                    </p>
                </div>
                <?php if (!$isAdminMonitoring): ?>
                    <div class="col-lg-4 text-lg-end d-flex flex-wrap gap-2 justify-content-lg-end">
                        <button class="btn btn-warning text-dark px-3.5 py-2.5 rounded-pill fw-bold shadow-lg d-inline-flex align-items-center gap-2 hover-scale" data-bs-toggle="modal" data-bs-target="#modalAddQuiz">
                            <i class="bi bi-plus-circle-fill fs-5"></i>
                            <span>Buat Manual</span>
                        </button>
                        <button class="btn btn-success text-white px-3.5 py-2.5 rounded-pill fw-bold shadow-lg d-inline-flex align-items-center gap-2 hover-scale" data-bs-toggle="modal" data-bs-target="#modalCreateImportExcel">
                            <i class="bi bi-file-earmark-excel-fill fs-5"></i>
                            <span>Import Excel</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 📊 KPI SUMMARY STATS CARDS -->
        <?php 
        $totalQuizzes = count($quizList ?? []);
        $totalSubmissions = count($hasilQuizSubmissions ?? []);
        
        $pendingEssayCount = 0;
        if (!empty($hasilQuizSubmissions)) {
            foreach ($hasilQuizSubmissions as $hqItem) {
                if (($hqItem['ungraded_essay_count'] ?? 0) > 0) {
                    $pendingEssayCount++;
                }
            }

            // Sort so pending essay items appear first
            usort($hasilQuizSubmissions, function($a, $b) {
                $aPending = ($a['ungraded_essay_count'] ?? 0) > 0 ? 1 : 0;
                $bPending = ($b['ungraded_essay_count'] ?? 0) > 0 ? 1 : 0;
                return $bPending <=> $aPending;
            });
        }

        $pendingSusulanCount = 0;
        if (!empty($susulanRequests)) {
            foreach ($susulanRequests as $srItem) {
                if (($srItem['status'] ?? '') === 'pending') {
                    $pendingSusulanCount++;
                }
            }

            // Sort so pending susulan requests appear first
            usort($susulanRequests, function($a, $b) {
                $aPending = ($a['status'] ?? '') === 'pending' ? 1 : 0;
                $bPending = ($b['status'] ?? '') === 'pending' ? 1 : 0;
                return $bPending <=> $aPending;
            });
        }
        ?>
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-quiz-card p-3.5 d-flex align-items-center gap-3">
                    <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-collection-play-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Paket Kuis</div>
                        <h4 class="fw-bold mb-0 text-dark"><?= $totalQuizzes ?> <span class="fs-6 fw-normal text-muted">Paket</span></h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-quiz-card p-3.5 d-flex align-items-center gap-3">
                    <div class="kpi-icon-box bg-warning bg-opacity-15 text-warning-emphasis">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Koreksi Essay Pending</div>
                        <h4 class="fw-bold mb-0 text-dark" id="kpiPendingEssayVal"><?= $pendingEssayCount ?> <span class="fs-6 fw-normal text-muted"><?= $pendingEssayCount > 0 ? 'Siswa Belum Dinilai' : 'Selesai' ?></span></h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-quiz-card p-3.5 d-flex align-items-center gap-3">
                    <div class="kpi-icon-box bg-success bg-opacity-10 text-success">
                        <i class="bi bi-journal-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Pengerjaan</div>
                        <h4 class="fw-bold mb-0 text-dark"><?= $totalSubmissions ?> <span class="fs-6 fw-normal text-muted">Selesai</span></h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-quiz-card p-3.5 d-flex align-items-center gap-3">
                    <div class="kpi-icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-envelope-paper-heart-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Izin Susulan Pending</div>
                        <h4 class="fw-bold mb-0 text-dark" id="kpiPendingSusulanVal"><?= $pendingSusulanCount ?> <span class="fs-6 fw-normal text-muted"><?= $pendingSusulanCount > 0 ? 'Menunggu Konfirmasi' : 'Tuntas' ?></span></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 📑 TABBED NAVIGATION -->
        <ul class="nav nav-pills quiz-nav-tabs gap-2 mb-4 p-1.5 bg-white rounded-4 border shadow-xs" id="quizGuruTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'paket' ? 'active' : '' ?> d-flex align-items-center gap-2" id="tab-paket-tab" data-bs-toggle="tab" data-bs-target="#tab-paket" type="button" role="tab">
                    <i class="bi bi-collection-play-fill"></i> Paket Kuis & Soal CBT
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'koreksi' ? 'active' : '' ?> d-flex align-items-center gap-2" id="tab-koreksi-tab" data-bs-toggle="tab" data-bs-target="#tab-koreksi" type="button" role="tab">
                    <i class="bi bi-award-fill"></i> Hasil & Koreksi Essay
                    <?php if ($pendingEssayCount > 0): ?>
                        <span id="badgeEssayCount" class="badge bg-danger rounded-pill px-2.5 py-1" style="font-size:0.72rem;"><?= $pendingEssayCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'susulan' ? 'active' : '' ?> d-flex align-items-center gap-2" id="tab-susulan-tab" data-bs-toggle="tab" data-bs-target="#tab-susulan" type="button" role="tab">
                    <i class="bi bi-envelope-paper-heart-fill"></i> Izin Susulan
                    <?php if ($pendingSusulanCount > 0): ?>
                        <span id="badgeSusulanCount" class="badge bg-warning text-dark rounded-pill px-2.5 py-1" style="font-size:0.72rem;"><?= $pendingSusulanCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <!-- TAB CONTENT -->
        <div class="tab-content" id="quizGuruTabContent">
            
            <!-- TAB 1: PAKET KUIS & BANK SOAL CBT -->
            <div class="tab-pane fade <?= $activeTab === 'paket' ? 'show active' : '' ?>" id="tab-paket" role="tabpanel">
                <div class="table-card-custom p-4">
                    
                    <!-- Search & Filter Controls -->
                    <div class="row g-3 mb-4 align-items-center">
                        <div class="col-12 col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" id="searchQuizInput" class="form-control border-start-0 ps-0" placeholder="Cari judul kuis..." onkeyup="filterGuruQuizTable()">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <select id="filterQuizMapel" class="form-select" onchange="filterGuruQuizTable()">
                                <option value="">Semua Mata Pelajaran</option>
                                <?php foreach ($mapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if (!$isAdminMonitoring): ?>
                            <div class="col-12 col-sm-6 col-md-4 text-sm-end">
                                <button class="btn btn-primary px-3 py-2 rounded-pill fw-bold shadow-sm small" data-bs-toggle="modal" data-bs-target="#modalAddQuiz">
                                    <i class="bi bi-plus-circle me-1"></i> Buat Kuis Baru
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Judul Quiz</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Kelas</th>
                                    <th>Durasi</th>
                                    <th>Deadline</th>
                                    <th>Kesempatan</th>
                                    <th class="text-center" style="min-width: 290px;">Kelola Paket & Soal</th>
                                </tr>
                            </thead>
                            <tbody id="guruQuizTableBody">
                                <?php if (empty($quizList)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-patch-question fs-1 text-slate-300 d-block mb-2"></i>
                                            Belum ada paket kuis CBT yang dibuat.<?= !$isAdminMonitoring ? ' Klik <strong>"Buat Kuis Baru"</strong> untuk memulai.' : '' ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($quizList as $i => $q): 
                                        $examModel = new ExamModel();
                                        $soalList = $examModel->getSoalByQuiz($q['id']);
                                    ?>
                                        <tr class="guru-quiz-row" data-title="<?= htmlspecialchars($q['judul']) ?>" data-mapel="<?= $q['mapel_id'] ?>">
                                            <td class="fw-bold text-muted"><?= $i + 1 ?></td>
                                            <td>
                                                <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-1.5 flex-wrap">
                                                    <?= htmlspecialchars($q['judul']) ?>
                                                    <?php if (($q['kategori'] ?? '') === 'uts'): ?>
                                                        <span class="badge text-white rounded-pill px-2.5 py-1" style="background:#7c3aed; font-size:0.7rem;"><i class="bi bi-trophy-fill me-1"></i>UTS</span>
                                                    <?php elseif (($q['kategori'] ?? '') === 'uas'): ?>
                                                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-1" style="font-size:0.7rem;"><i class="bi bi-award-fill me-1"></i>UAS</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary text-white rounded-pill px-2.5 py-1" style="font-size:0.7rem;"><i class="bi bi-file-text me-1"></i>Kuis</span>
                                                    <?php endif; ?>

                                                    <?php if (!empty($q['access_key'])): ?>
                                                        <span class="badge bg-dark text-warning border border-warning rounded-pill px-2.5 py-1 font-monospace" style="font-size:0.7rem;" title="Kunci Akses Token Ujian"><i class="bi bi-key-fill me-1"></i>Token: <?= htmlspecialchars($q['access_key']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted d-block"><?= htmlspecialchars($q['deskripsi'] ?? 'Tanpa deskripsi') ?></small>
                                            </td>
                                            <td><span class="badge-mapel-tag"><i class="bi bi-journal-bookmark me-1"></i><?= htmlspecialchars($q['nama_mapel']) ?></span></td>
                                            <td><span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1.5 fw-bold"><?= htmlspecialchars($q['nama_kelas']) ?></span></td>
                                            <td><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1.5 fw-bold"><i class="bi bi-clock me-1"></i><?= $q['durasi_menit'] ?> Menit</span></td>
                                            <td>
                                                <?php if (!empty($q['deadline'])): 
                                                    $isExp = (date('Y-m-d H:i:s') > $q['deadline']);
                                                ?>
                                                    <span class="badge bg-<?= $isExp ? 'danger' : 'warning text-dark' ?> rounded-pill px-3 py-1.5">
                                                        <i class="bi bi-clock-history me-1"></i><?= date('d M Y, H:i', strtotime($q['deadline'])) ?> WIB
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border rounded-pill px-3 py-1.5">Tanpa Deadline</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold">
                                                    <i class="bi bi-arrow-repeat me-1"></i><?= ($q['max_attempts'] ?? 1) == 0 ? 'Tanpa Batas' : ($q['max_attempts'] ?? 1) . 'x Percobaan' ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                                    <button class="btn btn-sm btn-info text-white px-2.5 rounded-pill shadow-xs" style="font-size:0.76rem;" data-bs-toggle="modal" data-bs-target="#modalPreviewQuiz<?= $q['id'] ?>" title="Detail & Bank Soal">
                                                        <i class="bi bi-eye me-1"></i> Soal (<?= count($soalList) ?>)
                                                    </button>
                                                    <?php if (!$isAdminMonitoring): ?>
                                                        <button class="btn btn-sm btn-success px-2.5 rounded-pill shadow-xs" style="font-size:0.76rem;" data-bs-toggle="modal" data-bs-target="#modalAddSoal<?= $q['id'] ?>" title="Tambah Soal Manual">
                                                            <i class="bi bi-plus-circle me-1"></i> +Soal Manual
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-success px-2.5 rounded-pill shadow-xs" style="font-size:0.76rem;" data-bs-toggle="modal" data-bs-target="#modalImportSoal<?= $q['id'] ?>" title="Import Soal Excel">
                                                            <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
                                                        </button>
                                                        <button class="btn btn-sm btn-warning text-dark px-2.5 rounded-pill shadow-xs" style="font-size:0.76rem;" data-bs-toggle="modal" data-bs-target="#modalEditQuiz<?= $q['id'] ?>" title="Edit Info Quiz">
                                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                                        </button>
                                                        <form action="<?= BASE_URL ?>index.php?url=guru/quiz" method="POST" onsubmit="return confirm('Hapus paket quiz ini beserta seluruh soalnya?');" class="d-inline">
                                                            <?= Security::csrfField() ?>
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger px-2.5 rounded-pill shadow-xs" style="font-size:0.76rem;" title="Hapus Quiz">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: HASIL UJIAN & KOREKSI ESSAY SISWA -->
            <div class="tab-pane fade <?= $activeTab === 'koreksi' ? 'show active' : '' ?>" id="tab-koreksi" role="tabpanel">
                <div class="table-card-custom p-4 border-top border-4 border-warning">
                    
                    <?php
                    $totalSubmissions = count($hasilQuizSubmissions ?? []);
                    $totalPendingEssay = 0;
                    $totalGradedEssay = 0;
                    $totalAutoPg = 0;

                    if (!empty($hasilQuizSubmissions)) {
                        foreach ($hasilQuizSubmissions as $hqItem) {
                            $tCount = (int)($hqItem['total_essay_count'] ?? 0);
                            $uCount = (int)($hqItem['ungraded_essay_count'] ?? 0);
                            if ($tCount > 0) {
                                if ($uCount > 0) {
                                    $totalPendingEssay++;
                                } else {
                                    $totalGradedEssay++;
                                }
                            } else {
                                $totalAutoPg++;
                            }
                        }
                    }
                    ?>

                    <!-- Filter Pills / Quick Buttons -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-3 border-bottom">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <button type="button" class="btn btn-sm btn-dark rounded-pill fw-bold px-3 py-1.5 essay-filter-pill active" onclick="setGuruEssayFilter('', this)">
                                <i class="bi bi-collection-fill me-1"></i> Semua (<?= $totalSubmissions ?>)
                            </button>
                            <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill fw-bold px-3 py-1.5 essay-filter-pill position-relative shadow-xs" onclick="setGuruEssayFilter('pending', this)">
                                <i class="bi bi-exclamation-circle-fill me-1 text-danger"></i> ⏳ Belum Dinilai (<?= $totalPendingEssay ?>)
                                <?php if ($totalPendingEssay > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle p-1.5 bg-danger border border-light rounded-circle"></span>
                                <?php endif; ?>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill fw-bold px-3 py-1.5 essay-filter-pill" onclick="setGuruEssayFilter('graded', this)">
                                <i class="bi bi-check-circle-fill me-1"></i> ✓ Selesai Dinilai (<?= $totalGradedEssay ?>)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill fw-bold px-3 py-1.5 essay-filter-pill" onclick="setGuruEssayFilter('pg_auto', this)">
                                <i class="bi bi-robot me-1"></i> 🤖 Kuis PG (<?= $totalAutoPg ?>)
                            </button>
                        </div>

                        <select id="filterEssayStatus" class="form-select form-select-sm d-inline-block w-auto rounded-pill fw-semibold" onchange="filterGuruEssaySubmissions()">
                            <option value="">Semua Status Koreksi</option>
                            <option value="pending">⏳ Belum Dinilai Guru (<?= $totalPendingEssay ?>)</option>
                            <option value="graded">✓ Koreksi Selesai (<?= $totalGradedEssay ?>)</option>
                            <option value="pg_auto">🤖 Kuis PG Otomatis (<?= $totalAutoPg ?>)</option>
                        </select>
                    </div>

                    <!-- Search Controls -->
                    <div class="row g-3 mb-4 align-items-center">
                        <div class="col-12 col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" id="searchEssayInput" class="form-control border-start-0 ps-0" placeholder="Cari nama siswa, kelas, atau judul kuis..." onkeyup="filterGuruEssaySubmissions()">
                            </div>
                        </div>
                        <div class="col-12 col-md-4 text-md-end text-muted small fw-medium">
                            Menampilkan data pengerjaan kuis real-time.
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Judul Kuis</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Waktu Selesai</th>
                                    <th>Nilai Akhir</th>
                                    <th>Status Koreksi Essay</th>
                                    <th class="text-center">Aksi Koreksi</th>
                                </tr>
                            </thead>
                            <tbody id="guruEssayTableBody">
                                <?php if (empty($hasilQuizSubmissions)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-journal-x fs-1 text-slate-300 d-block mb-2"></i>
                                            Belum ada pengerjaan kuis siswa yang masuk.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($hasilQuizSubmissions as $hq): 
                                        $tEssay = (int)($hq['total_essay_count'] ?? 0);
                                        $uEssay = (int)($hq['ungraded_essay_count'] ?? 0);
                                        
                                        if ($tEssay > 0) {
                                            if ($uEssay > 0) {
                                                $statusKey = 'pending';
                                                $rowStyle = 'style="background-color: #fffbeb !important;"';
                                                $borderStyle = 'border-start border-4 border-warning';
                                            } else {
                                                $statusKey = 'graded';
                                                $rowStyle = '';
                                                $borderStyle = 'border-start border-4 border-success';
                                            }
                                        } else {
                                            $statusKey = 'pg_auto';
                                            $rowStyle = '';
                                            $borderStyle = 'border-start border-4 border-info';
                                        }
                                    ?>
                                        <tr class="guru-essay-row <?= $borderStyle ?>" <?= $rowStyle ?> data-text="<?= htmlspecialchars($hq['nama_siswa'] . ' ' . $hq['nama_kelas'] . ' ' . $hq['nama_quiz']) ?>" data-status="<?= $statusKey ?>">
                                            <td class="fw-bold text-dark">
                                                <i class="bi bi-person-circle text-primary me-1.5"></i><?= htmlspecialchars($hq['nama_siswa']) ?>
                                            </td>
                                            <td><span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1.5 fw-bold"><?= htmlspecialchars($hq['nama_kelas']) ?></span></td>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($hq['nama_quiz']) ?></td>
                                            <td><span class="badge-mapel-tag"><?= htmlspecialchars($hq['nama_mapel']) ?></span></td>
                                            <td class="small text-muted"><?= date('d M Y, H:i', strtotime($hq['finished_at'] ?? $hq['started_at'])) ?> WIB</td>
                                            <td>
                                                <span class="badge bg-primary fs-6 px-3 py-1.5 rounded-pill shadow-xs">
                                                    <?= number_format((float)$hq['total_nilai'], 1) ?> / 100
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($statusKey === 'pending'): ?>
                                                    <span class="badge bg-warning text-dark border border-warning-subtle rounded-pill px-3 py-1.5 fw-bold shadow-xs">
                                                        <i class="bi bi-exclamation-circle-fill text-danger me-1"></i>Belum Dinilai (<?= $uEssay ?>/<?= $tEssay ?> Essay Pending)
                                                    </span>
                                                <?php elseif ($statusKey === 'graded'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Koreksi Selesai (<?= $tEssay ?> Essay)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold">
                                                        <i class="bi bi-robot me-1"></i>Kuis PG (Terpenuhi Otomatis)
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($statusKey === 'pending'): ?>
                                                    <button class="btn btn-sm btn-warning text-dark px-3 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGradeEssay<?= $hq['quiz_id'] ?>_<?= $hq['siswa_id'] ?>" style="font-size:0.78rem;">
                                                        <i class="bi bi-pencil-square me-1"></i> Koreksi Sekarang (<?= $uEssay ?>)
                                                    </button>
                                                <?php elseif ($statusKey === 'graded'): ?>
                                                    <button class="btn btn-sm btn-outline-success px-3 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#modalGradeEssay<?= $hq['quiz_id'] ?>_<?= $hq['siswa_id'] ?>" style="font-size:0.78rem;">
                                                        <i class="bi bi-check2-all me-1"></i> Edit Nilai Essay
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-primary px-3 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#modalGradeEssay<?= $hq['quiz_id'] ?>_<?= $hq['siswa_id'] ?>" style="font-size:0.78rem;">
                                                        <i class="bi bi-eye me-1"></i> Lihat Hasil Kuis
                                                    </button>
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

            <!-- TAB 3: IZIN SUSULAN SISWA -->
            <div class="tab-pane fade <?= $activeTab === 'susulan' ? 'show active' : '' ?>" id="tab-susulan" role="tabpanel">
                <div class="table-card-custom p-4 border-top border-4 border-danger">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-envelope-paper-heart-fill text-danger me-2"></i>Permintaan Izin Ujian Susulan Siswa</h5>
                        <span class="badge bg-danger px-3 py-1.5 rounded-pill fw-bold">Total: <?= count($susulanRequests ?? []) ?> Pengajuan</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Judul Kuis</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Catatan Siswa</th>
                                    <th>Status Izin</th>
                                    <th class="text-center">Aksi Persetujuan Guru</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($susulanRequests)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-check-all fs-1 text-slate-300 d-block mb-2"></i>
                                            Belum ada pengajuan izin ujian susulan dari siswa.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($susulanRequests as $req): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($req['nama_siswa']) ?></td>
                                            <td><span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1.5 fw-bold"><?= htmlspecialchars($req['nama_kelas']) ?></span></td>
                                            <td><?= htmlspecialchars($req['judul_quiz']) ?></td>
                                            <td><span class="badge-mapel-tag"><?= htmlspecialchars($req['nama_mapel']) ?></span></td>
                                            <td class="small text-muted"><?= htmlspecialchars($req['catatan'] ?? 'Permohonan Susulan') ?></td>
                                            <td>
                                                <?php if ($req['status'] === 'disetujui'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold"><i class="bi bi-check-circle me-1"></i>Disetujui</span>
                                                <?php elseif ($req['status'] === 'ditolak'): ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1.5 fw-bold"><i class="bi bi-x-circle me-1"></i>Ditolak</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1.5 fw-bold"><i class="bi bi-hourglass-split me-1"></i>Menunggu Konfirmasi</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-1.5">
                                                    <?php if ($req['status'] !== 'disetujui'): ?>
                                                        <form action="<?= BASE_URL ?>index.php?url=guru/quiz" method="POST" class="d-inline">
                                                            <?= Security::csrfField() ?>
                                                            <input type="hidden" name="action" value="approve_susulan">
                                                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-success px-3 rounded-pill fw-bold" style="font-size:0.75rem;">
                                                                <i class="bi bi-check-circle me-1"></i> Izinkan
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($req['status'] !== 'ditolak'): ?>
                                                        <form action="<?= BASE_URL ?>index.php?url=guru/quiz" method="POST" class="d-inline">
                                                            <?= Security::csrfField() ?>
                                                            <input type="hidden" name="action" value="reject_susulan">
                                                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger px-3 rounded-pill fw-bold" style="font-size:0.75rem;">
                                                                <i class="bi bi-x-circle me-1"></i> Tolak
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
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

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- 📝 MODALS SECTION -->
<!-- ════════════════════════════════════════════════════════════════ -->

<!-- Modal Create & Import Quiz via Excel -->
<div class="modal fade" id="modalCreateImportExcel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 bg-success text-white p-3.5" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="bg-white rounded-3 p-2 text-success shadow-xs">
                        <i class="bi bi-file-earmark-excel-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Buat Kuis & Import Soal Excel</h5>
                        <small class="text-white text-opacity-85 fw-medium" style="font-size:0.75rem;">Mendukung semua jenis soal: Pilihan Ganda (PG), Essay, dan True/False</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=guru/quiz" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="create_import_excel">

                    <div class="alert alert-light border rounded-3 p-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2 shadow-xs">
                        <div>
                            <div class="fw-bold text-dark small"><i class="bi bi-file-earmark-excel-fill text-success me-1"></i>Template Excel / CSV Resmi</div>
                            <small class="text-muted">Gunakan template resmi untuk format kolom: <code>jenis_soal</code>, <code>pertanyaan</code>, <code>bobot</code>, <code>opsi_a</code>..<code>opsi_e</code>, <code>jawaban_benar</code>.</small>
                        </div>
                        <a href="<?= BASE_URL ?>index.php?url=guru/downloadTemplateSoal" class="btn btn-sm btn-success fw-bold text-white rounded-pill px-3 shadow-xs">
                            <i class="bi bi-download me-1.5"></i> Unduh Template Excel (.csv)
                        </a>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Judul Quiz / Ujian <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required placeholder="Contoh: Kuis CBT Pemrograman Web">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="mapel_id" class="form-select" required>
                                <?php foreach ($mapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Kelas Target <span class="text-danger">*</span></label>
                            <select name="kelas_id" class="form-select" required>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Durasi Ujian (Menit) <span class="text-danger">*</span></label>
                            <input type="number" name="durasi_menit" class="form-control" value="45" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><i class="bi bi-clock-history text-danger me-1"></i>Batas Waktu / Deadline <span class="text-muted fw-normal">(Opsional)</span></label>
                            <input type="datetime-local" name="deadline" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><i class="bi bi-bookmark-star-fill text-warning me-1"></i>Kategori Pelaksanaan Ujian <span class="text-danger">*</span></label>
                            <select name="kategori" id="kategoriCreateExcel" class="form-select fw-bold text-dark" onchange="handleKategoriChange(this, 'accessKeyCreateExcel')">
                                <option value="kuis" selected>📝 Kuis Harian / Evaluasi CBT (Token Opsional)</option>
                                <option value="uts">🏆 UTS (Ujian Tengah Semester) - Auto Token 🔑</option>
                                <option value="uas">🎓 UAS (Ujian Akhir Semester) - Auto Token 🔑</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><i class="bi bi-key-fill text-danger me-1"></i>Kunci Akses (Token Ujian) <span class="text-muted fw-normal">(Auto Terisi Saat UTS/UAS)</span></label>
                            <div class="input-group">
                                <input type="text" name="access_key" id="accessKeyCreateExcel" class="form-control text-uppercase fw-bold font-monospace bg-warning-subtle" placeholder="Opsional untuk Kuis / Auto UTS/UAS">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="generateRandomToken('accessKeyCreateExcel')" title="Generate Token Acak"><i class="bi bi-arrow-clockwise"></i> Token</button>
                            </div>
                            <small class="text-muted d-block" style="font-size:0.72rem;">Otomatis terisi jika memilih UTS / UAS (atau klik tombol Token).</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><i class="bi bi-arrow-repeat text-primary me-1"></i>Kesempatan Mengerjakan</label>
                            <select name="max_attempts" class="form-select">
                                <option value="1" selected>1x Percobaan (Standar UTS/UAS)</option>
                                <option value="2">2x Percobaan</option>
                                <option value="3">3x Percobaan</option>
                                <option value="0">Tanpa Batas (Unlimited)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark"><i class="bi bi-file-earmark-excel-fill text-success me-1"></i>Unggah Berkas Excel / CSV Template Soal <span class="text-danger">*</span></label>
                            <input type="file" name="file_excel" class="form-control" accept=".csv, .xlsx, .xls" required>
                            <small class="text-muted d-block mt-1" style="font-size:0.75rem;">Sistem otomatis meng-import semua soal Pilihan Ganda (PG), Essay, True/False, dan Gambar.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark"><i class="bi bi-images text-primary me-1"></i>Unggah Berkas Gambar Soal Sekaligus <span class="text-muted fw-normal">(Opsional / Multiple Files)</span></label>
                            <input type="file" name="gambar_soal_files[]" class="form-control" accept="image/*" multiple>
                            <small class="text-muted d-block mt-1" style="font-size:0.75rem;">Pilih seluruh file gambar (misal: <code>diagram.png</code>, <code>soal1.jpg</code>) yang namanya dicantumkan pada kolom <code>gambar</code> di Excel.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3.5 bg-light justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success text-white rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-file-earmark-arrow-up-fill me-1.5"></i> Buat Kuis & Import Seluruh Soal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Quiz -->
<div class="modal fade" id="modalAddQuiz" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="bg-primary rounded-3 p-2 text-white shadow-xs">
                        <i class="bi bi-patch-question-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Buat Paket Quiz & Soal Baru</h5>
                        <small class="text-info fw-medium" style="font-size:0.75rem;">Tambahkan paket evaluasi pembelajaran untuk kelas target Anda</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=guru/quiz" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="create">

                    <div class="alert alert-success border-0 rounded-3 p-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2 shadow-xs">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-excel-fill text-success fs-3"></i>
                            <div>
                                <div class="fw-bold text-dark small">Ingin buat kuis lebih cepat pakai Excel?</div>
                                <small class="text-muted">Unduh template CSV/Excel, isi soal PG, Essay, True/False, dan import sekaligus!</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= BASE_URL ?>index.php?url=guru/downloadTemplateSoal" class="btn btn-sm btn-outline-success fw-bold rounded-pill px-3">
                                <i class="bi bi-download me-1"></i> Template Excel
                            </a>
                            <button type="button" class="btn btn-sm btn-success text-white fw-bold rounded-pill px-3" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalCreateImportExcel">
                                <i class="bi bi-file-earmark-excel me-1"></i> Mode Excel
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Judul Quiz / Ujian <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required placeholder="Contoh: Kuis 1 Pemrograman PHP MVC">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="mapel_id" class="form-select" required>
                                <?php foreach ($mapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Kelas Target <span class="text-danger">*</span></label>
                            <select name="kelas_id" class="form-select" required>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Durasi Ujian (Menit) <span class="text-danger">*</span></label>
                            <input type="number" name="durasi_menit" class="form-control" value="30" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Acak Urutan Soal</label>
                            <select name="random_soal" class="form-select">
                                <option value="Y">Ya (Randomkan Soal)</option>
                                <option value="N">Tidak (Sesuai Urutan Input)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Deskripsi / Petunjuk</label>
                            <input type="text" name="deskripsi" class="form-control" placeholder="Petunjuk pengerjaan ujian...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><i class="bi bi-clock-history text-danger me-1"></i>Batas Waktu / Deadline <span class="text-muted fw-normal">(Opsional)</span></label>
                            <input type="datetime-local" name="deadline" class="form-control">
                            <small class="text-muted d-block" style="font-size:0.72rem;">Kosongkan jika kuis tidak memiliki batas waktu penutupan.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><i class="bi bi-bookmark-star-fill text-warning me-1"></i>Kategori Pelaksanaan Ujian <span class="text-danger">*</span></label>
                            <select name="kategori" id="kategoriAddQuiz" class="form-select fw-bold text-dark" onchange="handleKategoriChange(this, 'accessKeyAddQuiz')">
                                <option value="kuis" selected>📝 Kuis Harian / Evaluasi CBT (Token Opsional)</option>
                                <option value="uts">🏆 UTS (Ujian Tengah Semester) - Auto Token 🔑</option>
                                <option value="uas">🎓 UAS (Ujian Akhir Semester) - Auto Token 🔑</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><i class="bi bi-key-fill text-danger me-1"></i>Kunci Akses (Token Ujian) <span class="text-muted fw-normal">(Auto Terisi Saat UTS/UAS)</span></label>
                            <div class="input-group">
                                <input type="text" name="access_key" id="accessKeyAddQuiz" class="form-control text-uppercase fw-bold font-monospace bg-warning-subtle" placeholder="Opsional untuk Kuis / Auto UTS/UAS">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="generateRandomToken('accessKeyAddQuiz')" title="Generate Token Acak"><i class="bi bi-arrow-clockwise"></i> Token</button>
                            </div>
                            <small class="text-muted d-block" style="font-size:0.72rem;">Otomatis terisi jika memilih UTS / UAS (atau klik tombol Token).</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold"><i class="bi bi-arrow-repeat text-primary me-1"></i>Kesempatan Mengerjakan (Max Attempts)</label>
                            <select name="max_attempts" class="form-select">
                                <option value="1" selected>1x Percobaan (Standar)</option>
                                <option value="2">2x Percobaan</option>
                                <option value="3">3x Percobaan (Ambil Nilai Tertinggi)</option>
                                <option value="5">5x Percobaan</option>
                                <option value="0">Tanpa Batas (Unlimited Attempts)</option>
                            </select>
                            <small class="text-muted d-block" style="font-size:0.72rem;">Siswa dapat mengulang kuis & sistem mengambil Nilai Tertinggi.</small>
                        </div>
                    </div>

                    <!-- Dynamic Question Cards Container -->
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-journal-text me-1.5 text-primary"></i>Daftar Soal Pembelajaran</h6>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill fw-bold px-3" onclick="addQuestionCard()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Soal Berikutnya
                        </button>
                    </div>

                    <div id="containerQuestions">
                        <!-- Initial Question #1 Card -->
                        <div class="p-3.5 bg-light rounded-3 border mb-3 question-card" id="qCard1">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-primary mb-0 q-number">Soal #1</h6>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Bentuk / Jenis Soal <span class="text-danger">*</span></label>
                                    <select name="jenis_soal[]" class="form-select form-select-sm" onchange="toggleSoalInputs(this, 1)" required>
                                        <option value="pg">Pilihan Ganda (PG)</option>
                                        <option value="tf">Benar / Salah (True / False)</option>
                                        <option value="essay">Essay / Uraian</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold">Pertanyaan Soal <span class="text-danger">*</span></label>
                                <input type="text" name="pertanyaan[]" class="form-control" placeholder="Masukkan pertanyaan soal..." required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold"><i class="bi bi-image text-primary me-1"></i>Lampiran Gambar Soal <span class="text-muted fw-normal">(Opsional)</span></label>
                                <input type="file" name="gambar_soal[]" class="form-control form-control-sm" accept="image/*">
                            </div>

                            <!-- Options Block for Pilihan Ganda -->
                            <div class="block-pg-1">
                                <div class="row g-2 mb-2">
                                    <div class="col-6"><input type="text" name="pil_a[]" class="form-control form-control-sm" placeholder="Pilihan A"></div>
                                    <div class="col-6"><input type="text" name="pil_b[]" class="form-control form-control-sm" placeholder="Pilihan B"></div>
                                    <div class="col-6"><input type="text" name="pil_c[]" class="form-control form-control-sm" placeholder="Pilihan C"></div>
                                    <div class="col-6"><input type="text" name="pil_d[]" class="form-control form-control-sm" placeholder="Pilihan D"></div>
                                    <div class="col-6"><input type="text" name="pil_e[]" class="form-control form-control-sm" placeholder="Pilihan E (Opsional)"></div>
                                    <div class="col-6"><input type="text" name="pil_f[]" class="form-control form-control-sm" placeholder="Pilihan F (Opsional)"></div>
                                </div>
                                <div>
                                    <label class="form-label small fw-bold text-success">Kunci Jawaban Benar (PG)</label>
                                    <select name="jawaban[]" class="form-select form-select-sm">
                                        <option value="A">Pilihan A</option>
                                        <option value="B">Pilihan B</option>
                                        <option value="C">Pilihan C</option>
                                        <option value="D">Pilihan D</option>
                                        <option value="E">Pilihan E</option>
                                        <option value="F">Pilihan F</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Options Block for True / False -->
                            <div class="block-tf-1 d-none">
                                <label class="form-label small fw-bold text-success">Kunci Jawaban Benar (Benar / Salah)</label>
                                <select name="jawaban_tf[]" class="form-select form-select-sm">
                                    <option value="BENAR">BENAR (True)</option>
                                    <option value="SALAH">SALAH (False)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 p-4 justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold text-white shadow-sm">
                        <i class="bi bi-save-fill me-1"></i> Simpan Paket Quiz
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals Preview, Add Soal, Edit Quiz for Each Quiz -->
<?php foreach ($quizList as $q): 
    $examModel = new ExamModel();
    $soalList = $examModel->getSoalByQuiz($q['id']);
?>
    <!-- Modal Preview Quiz -->
    <div class="modal fade" id="modalPreviewQuiz<?= $q['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="bg-info rounded-3 p-2 text-white shadow-xs">
                            <i class="bi bi-eye-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-white mb-0">Detail Quiz: <?= htmlspecialchars($q['judul']) ?></h6>
                            <small class="text-info fw-medium" style="font-size:0.75rem;">Mapel: <?= htmlspecialchars($q['nama_mapel']) ?> &bull; Kelas: <?= htmlspecialchars($q['nama_kelas']) ?></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 border mb-3 small">
                        <strong>Petunjuk Pengerjaan:</strong> <?= htmlspecialchars($q['deskripsi'] ?? 'Pilihlah satu jawaban paling tepat.') ?><br>
                        <strong>Durasi:</strong> <?= $q['durasi_menit'] ?> Menit &bull; <strong>Acak Soal:</strong> <?= $q['random_soal'] === 'Y' ? 'Ya (Diacak)' : 'Tidak' ?>
                    </div>

                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-journal-text text-primary me-1"></i>Daftar Soal Terdaftar (<?= count($soalList) ?> Soal)</h6>

                    <?php if (empty($soalList)): ?>
                        <div class="text-center py-4 text-muted">Belum ada soal dalam paket kuis ini. Silakan klik tombol +Soal.</div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($soalList as $sIndex => $s): ?>
                                <div class="p-3 border rounded-3 bg-white shadow-sm position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="fw-bold text-primary mb-0">Soal #<?= $sIndex + 1 ?></h6>
                                            <?php if ($s['jenis_soal'] === 'pg'): ?>
                                                <span class="badge bg-primary rounded-pill">Pilihan Ganda</span>
                                            <?php elseif ($s['jenis_soal'] === 'tf'): ?>
                                                <span class="badge bg-warning text-dark rounded-pill">Benar / Salah</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary rounded-pill">Essay / Uraian</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!$isAdminMonitoring): ?>
                                            <form action="<?= BASE_URL ?>index.php?url=guru/quiz" method="POST" onsubmit="return confirm('Hapus soal nomor ini?');" class="d-inline">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="delete_soal">
                                                <input type="hidden" name="soal_id" value="<?= $s['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" style="font-size:0.75rem;"><i class="bi bi-trash me-1"></i> Hapus</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    <p class="fw-semibold text-dark mb-2"><?= htmlspecialchars($s['pertanyaan']) ?></p>

                                    <?php if (!empty($s['gambar'])): ?>
                                        <div class="my-2 p-2 bg-light rounded-3 border text-center">
                                            <img src="<?= (strpos($s['gambar'], 'http') === 0) ? htmlspecialchars($s['gambar']) : BASE_URL . 'assets/uploads/soal/' . htmlspecialchars($s['gambar']) ?>" alt="Gambar Soal" class="img-fluid rounded-3 shadow-xs" style="max-height: 250px; object-fit: contain;">
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!$isAdminMonitoring): ?>
                                        <!-- Quick Upload / Replace Image Form for Question -->
                                        <form action="<?= BASE_URL ?>index.php?url=guru/quiz" method="POST" enctype="multipart/form-data" class="my-2">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="action" value="update_gambar_soal">
                                            <input type="hidden" name="soal_id" value="<?= $s['id'] ?>">
                                            <div class="input-group input-group-sm" style="max-width: 400px;">
                                                <span class="input-group-text bg-white text-muted"><i class="bi bi-image text-primary"></i></span>
                                                <input type="file" name="gambar_soal" class="form-control" accept="image/*" required>
                                                <button type="submit" class="btn btn-outline-primary fw-semibold" style="font-size:0.75rem;">
                                                    <i class="bi bi-upload me-1"></i><?= empty($s['gambar']) ? 'Upload Gambar' : 'Ganti Gambar' ?>
                                                </button>
                                            </div>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (!empty($s['pilihan'])): ?>
                                        <div class="row g-2 ms-1">
                                            <?php foreach ($s['pilihan'] as $pIdx => $p): 
                                                $letter = chr(65 + $pIdx);
                                                $isRight = ($p['is_benar'] == 1);
                                            ?>
                                                <div class="col-6">
                                                    <div class="p-2 rounded-2 border <?= $isRight ? 'bg-success-subtle border-success text-success fw-bold' : 'bg-light text-muted' ?>" style="font-size:0.85rem;">
                                                        <strong><?= $letter ?>.</strong> <?= htmlspecialchars($p['teks_pilihan']) ?>
                                                        <?php if ($isRight): ?>
                                                            <i class="bi bi-check-circle-fill ms-1"></i> (Kunci Benar)
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-0 pt-0 p-4 justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalAddSoal<?= $q['id'] ?>">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Soal Baru
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Soal ke Quiz Ini -->
    <div class="modal fade" id="modalAddSoal<?= $q['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="bg-success rounded-3 p-2 text-white shadow-xs">
                            <i class="bi bi-plus-circle-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-white mb-0">Tambah Soal ke: <?= htmlspecialchars($q['judul']) ?></h6>
                            <small class="text-info fw-medium" style="font-size:0.75rem;">Tambahkan butir soal PG, True/False, atau Essay baru</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=guru/quiz" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="add_soal">
                        <input type="hidden" name="quiz_id" value="<?= $q['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Bentuk / Jenis Soal <span class="text-danger">*</span></label>
                            <select name="jenis_soal" id="selectJenisAdd<?= $q['id'] ?>" class="form-select" onchange="toggleAddSoalType(<?= $q['id'] ?>)" required>
                                <option value="pg">Pilihan Ganda (PG)</option>
                                <option value="tf">Benar / Salah (True / False)</option>
                                <option value="essay">Essay / Uraian</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Teks Pertanyaan Soal <span class="text-danger">*</span></label>
                            <input type="text" name="pertanyaan" class="form-control" placeholder="Masukkan teks pertanyaan soal..." required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold"><i class="bi bi-image text-primary me-1"></i>Lampiran Gambar Soal <span class="text-muted fw-normal">(Opsional)</span></label>
                            <input type="file" name="gambar_soal" class="form-control" accept="image/*">
                        </div>

                        <!-- Block Choices for PG -->
                        <div id="blockAddPg<?= $q['id'] ?>">
                            <div class="row g-2 mb-3">
                                <div class="col-6"><input type="text" name="pil_a" class="form-control" placeholder="Pilihan Jawaban A"></div>
                                <div class="col-6"><input type="text" name="pil_b" class="form-control" placeholder="Pilihan Jawaban B"></div>
                                <div class="col-6"><input type="text" name="pil_c" class="form-control" placeholder="Pilihan Jawaban C"></div>
                                <div class="col-6"><input type="text" name="pil_d" class="form-control" placeholder="Pilihan Jawaban D"></div>
                                <div class="col-6"><input type="text" name="pil_e" class="form-control" placeholder="Pilihan Jawaban E (Opsional)"></div>
                                <div class="col-6"><input type="text" name="pil_f" class="form-control" placeholder="Pilihan Jawaban F (Opsional)"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-success">Kunci Jawaban Benar (PG)</label>
                                <select name="jawaban" class="form-select">
                                    <option value="A">Pilihan A</option>
                                    <option value="B">Pilihan B</option>
                                    <option value="C">Pilihan C</option>
                                    <option value="D">Pilihan D</option>
                                    <option value="E">Pilihan E</option>
                                    <option value="F">Pilihan F</option>
                                </select>
                            </div>
                        </div>

                        <!-- Block Choices for True / False -->
                        <div id="blockAddTf<?= $q['id'] ?>" class="d-none mb-3">
                            <label class="form-label small fw-bold text-success">Kunci Jawaban Benar (Benar / Salah)</label>
                            <select name="jawaban_tf" class="form-select">
                                <option value="BENAR">BENAR (True)</option>
                                <option value="SALAH">SALAH (False)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 p-4 justify-content-between">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">Tambah Soal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Import Soal Excel per Quiz -->
    <div class="modal fade" id="modalImportSoal<?= $q['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-0 bg-success text-white p-3.5" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="bg-white rounded-3 p-2 text-success shadow-xs">
                            <i class="bi bi-file-earmark-excel-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-white mb-0">Import Soal Excel</h6>
                            <small class="text-white text-opacity-85 fw-medium" style="font-size:0.72rem;">Tambah soal PG, Essay, True/False sekaligus dari file Excel</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=guru/quiz" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4 bg-light">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="import_soal_excel">
                        <input type="hidden" name="quiz_id" value="<?= $q['id'] ?>">

                        <div class="p-3 bg-white rounded-3 border mb-3">
                            <small class="text-muted d-block">Paket Kuis Target:</small>
                            <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($q['judul']) ?></h6>
                            <small class="text-muted">Mapel: <?= htmlspecialchars($q['nama_mapel']) ?> | Kelas: <?= htmlspecialchars($q['nama_kelas']) ?></small>
                        </div>

                        <div class="alert alert-info border-0 rounded-3 p-3 mb-3 shadow-xs">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <small class="fw-bold text-dark d-block"><i class="bi bi-info-circle-fill me-1"></i>Format Excel Template</small>
                                    <small class="text-muted" style="font-size:0.75rem;">Mendukung jenis soal PG, Essay, dan True/False.</small>
                                </div>
                                <a href="<?= BASE_URL ?>index.php?url=guru/downloadTemplateSoal" class="btn btn-sm btn-success text-white fw-bold rounded-pill px-3">
                                    <i class="bi bi-download me-1"></i> Template (.csv)
                                </a>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Pilih Berkas Excel / CSV <span class="text-danger">*</span></label>
                            <input type="file" name="file_excel" class="form-control" accept=".csv, .xlsx, .xls" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-dark"><i class="bi bi-images text-primary me-1"></i>Unggah Berkas Gambar Soal Sekaligus <span class="text-muted fw-normal">(Opsional / Multiple Files)</span></label>
                            <input type="file" name="gambar_soal_files[]" class="form-control" accept="image/*" multiple>
                            <small class="text-muted d-block mt-1" style="font-size:0.72rem;">Jika ada soal bergambar pada kolom <code>gambar</code> di Excel, pilih semua file gambarnya di sini.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 bg-white border-top justify-content-between">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success text-white rounded-pill px-4 fw-bold shadow-sm">
                            <i class="bi bi-file-earmark-arrow-up-fill me-1.5"></i> Upload & Import Soal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Quiz -->
    <div class="modal fade" id="modalEditQuiz<?= $q['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="bg-warning rounded-3 p-2 text-dark shadow-xs">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-white mb-0">Edit Informasi Quiz</h6>
                            <small class="text-info fw-medium" style="font-size:0.75rem;">Perbarui judul, durasi, deadline, dan kesempatan pengerjaan kuis</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=guru/quiz" method="POST">
                    <div class="modal-body p-4">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $q['id'] ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Judul Quiz / Ujian <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($q['judul']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                                <select name="mapel_id" class="form-select" required>
                                    <?php foreach ($mapelList as $mp): ?>
                                        <option value="<?= $mp['id'] ?>" <?= $q['mapel_id'] == $mp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Kelas Target <span class="text-danger">*</span></label>
                                <select name="kelas_id" class="form-select" required>
                                    <?php foreach ($kelasList as $k): ?>
                                        <option value="<?= $k['id'] ?>" <?= $q['kelas_id'] == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Durasi Ujian (Menit) <span class="text-danger">*</span></label>
                                <input type="number" name="durasi_menit" class="form-control" value="<?= $q['durasi_menit'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Acak Urutan Soal</label>
                                <select name="random_soal" class="form-select">
                                    <option value="Y" <?= $q['random_soal'] === 'Y' ? 'selected' : '' ?>>Ya (Randomkan Soal)</option>
                                    <option value="N" <?= $q['random_soal'] === 'N' ? 'selected' : '' ?>>Tidak (Sesuai Urutan Input)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Deskripsi / Petunjuk</label>
                                <input type="text" name="deskripsi" class="form-control" value="<?= htmlspecialchars($q['deskripsi'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold"><i class="bi bi-clock-history text-danger me-1"></i>Batas Waktu / Deadline <span class="text-muted fw-normal">(Opsional)</span></label>
                                <input type="datetime-local" name="deadline" class="form-control" value="<?= !empty($q['deadline']) ? date('Y-m-d\TH:i', strtotime($q['deadline'])) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold"><i class="bi bi-bookmark-star-fill text-warning me-1"></i>Kategori Pelaksanaan Ujian <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-select fw-bold text-dark" onchange="handleKategoriChange(this, 'accessKeyEdit<?= $q['id'] ?>')">
                                    <option value="kuis" <?= ($q['kategori'] ?? 'kuis') === 'kuis' ? 'selected' : '' ?>>📝 Kuis Harian / Evaluasi CBT (Token Opsional)</option>
                                    <option value="uts" <?= ($q['kategori'] ?? '') === 'uts' ? 'selected' : '' ?>>🏆 UTS (Ujian Tengah Semester) - Auto Token 🔑</option>
                                    <option value="uas" <?= ($q['kategori'] ?? '') === 'uas' ? 'selected' : '' ?>>🎓 UAS (Ujian Akhir Semester) - Auto Token 🔑</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold"><i class="bi bi-key-fill text-danger me-1"></i>Kunci Akses (Token Ujian) <span class="text-muted fw-normal">(Auto Terisi Saat UTS/UAS)</span></label>
                                <div class="input-group">
                                    <input type="text" name="access_key" id="accessKeyEdit<?= $q['id'] ?>" class="form-control text-uppercase fw-bold font-monospace bg-warning-subtle" value="<?= htmlspecialchars($q['access_key'] ?? '') ?>" placeholder="Opsional untuk Kuis / Auto UTS/UAS">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="generateRandomToken('accessKeyEdit<?= $q['id'] ?>')" title="Generate Token Acak"><i class="bi bi-arrow-clockwise"></i> Token</button>
                                </div>
                                <small class="text-muted d-block" style="font-size:0.72rem;">Otomatis terisi jika memilih UTS / UAS (atau klik tombol Token).</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold"><i class="bi bi-arrow-repeat text-primary me-1"></i>Kesempatan Mengerjakan (Max Attempts)</label>
                                <select name="max_attempts" class="form-select">
                                    <option value="1" <?= ($q['max_attempts'] ?? 1) == 1 ? 'selected' : '' ?>>1x Percobaan (Standar UTS/UAS)</option>
                                    <option value="2" <?= ($q['max_attempts'] ?? 1) == 2 ? 'selected' : '' ?>>2x Percobaan</option>
                                    <option value="3" <?= ($q['max_attempts'] ?? 1) == 3 ? 'selected' : '' ?>>3x Percobaan (Ambil Nilai Tertinggi)</option>
                                    <option value="5" <?= ($q['max_attempts'] ?? 1) == 5 ? 'selected' : '' ?>>5x Percobaan</option>
                                    <option value="0" <?= ($q['max_attempts'] ?? 1) == 0 ? 'selected' : '' ?>>Tanpa Batas (Unlimited Attempts)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 p-4 justify-content-between">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning px-4 rounded-pill fw-bold text-dark shadow-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modals Grade Essay for Each Submission -->
<?php foreach ($hasilQuizSubmissions as $hq): 
    $essayAnswers = $examModel->getEssayAnswersByHasil($hq['quiz_id'], $hq['siswa_id']);
?>
    <div class="modal fade" id="modalGradeEssay<?= $hq['quiz_id'] ?>_<?= $hq['siswa_id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="bg-primary rounded-3 p-2 text-white shadow-xs">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-white mb-0">Koreksi & Beri Nilai Essay Siswa</h6>
                            <small class="text-info fw-medium" style="font-size:0.75rem;">Siswa: <?= htmlspecialchars($hq['nama_siswa']) ?> &bull; Kelas: <?= htmlspecialchars($hq['nama_kelas']) ?></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form action="<?= BASE_URL ?>index.php?url=guru/quiz" method="POST">
                    <div class="modal-body p-4">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="grade_quiz_essay">
                        <input type="hidden" name="redirect_tab" value="koreksi">
                        <input type="hidden" name="quiz_id" value="<?= $hq['quiz_id'] ?>">
                        <input type="hidden" name="siswa_id" value="<?= $hq['siswa_id'] ?>">

                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <strong>Kuis / Evaluasi:</strong> <?= htmlspecialchars($hq['nama_quiz']) ?><br>
                            <strong>Mata Pelajaran:</strong> <?= htmlspecialchars($hq['nama_mapel']) ?><br>
                            <strong>Skor Saat Ini:</strong> <?= number_format((float)$hq['total_nilai'], 1) ?> / 100
                        </div>

                        <?php if (empty($essayAnswers)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                                Kuis ini hanya berisi Pilihan Ganda / True-False (Tanpa Soal Essay). Penilaian telah dikalkulasi otomatis secara penuh.
                            </div>
                        <?php else: ?>
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-file-earmark-text text-primary me-1"></i>Lembar Jawaban Essay Siswa:</h6>
                            <?php foreach ($essayAnswers as $eIdx => $ea): 
                                $isGraded = !is_null($ea['nilai']);
                                $boxStyle = $isGraded ? 'background-color: #f8fafc; border: 1px solid #cbd5e1 !important;' : 'background-color: #fffbeb; border: 2px solid #f59e0b !important;';
                            ?>
                                <div class="p-3.5 rounded-3 mb-3 shadow-xs" style="<?= $boxStyle ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-2.5 flex-wrap gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-dark rounded-pill px-3 py-1 fw-bold">Soal Essay #<?= $eIdx + 1 ?></span>
                                            <?php if ($isGraded): ?>
                                                <span class="badge bg-success text-white rounded-pill px-3 py-1 fw-bold shadow-xs">
                                                    <i class="bi bi-check-circle-fill me-1"></i>SUDAH DINILAI (Skor: <?= number_format((float)$ea['nilai'], 1) ?> Poin)
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger text-white rounded-pill px-3 py-1 fw-bold shadow-xs">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>BELUM DINILAI GURU
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge bg-secondary rounded-pill fw-semibold">Maksimal Bobot: <?= $ea['bobot'] ?? 10 ?> Poin</span>
                                    </div>
                                    <p class="fw-bold text-dark mb-2"><?= htmlspecialchars($ea['pertanyaan']) ?></p>
                                    <div class="p-3 bg-white border rounded-3 mb-3">
                                        <small class="text-muted fw-bold d-block mb-1">Teks Jawaban Siswa:</small>
                                        <div class="font-monospace text-slate-800" style="font-size:0.9rem;"><?= nl2br(htmlspecialchars($ea['teks_jawaban_essay'] ?? 'Siswa Tidak Mengisi Jawaban Essay')) ?></div>
                                    </div>

                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-7">
                                            <?php if ($isGraded): ?>
                                                <label class="form-label small fw-bold text-success mb-1">
                                                    <i class="bi bi-check2-all me-1"></i>Edit Nilai Essay (Saat Ini: <?= (float)$ea['nilai'] ?> / Max <?= $ea['bobot'] ?? 10 ?>):
                                                </label>
                                            <?php else: ?>
                                                <label class="form-label small fw-bold text-danger mb-1">
                                                    <i class="bi bi-pencil-fill me-1"></i>Input Nilai Essay (Belum Diisi, Maksimal Bobot <?= $ea['bobot'] ?? 10 ?>):
                                                </label>
                                            <?php endif; ?>
                                            <input type="number" step="0.5" min="0" max="<?= $ea['bobot'] ?? 10 ?>" name="nilai_essay[<?= $ea['jawaban_id'] ?>]" class="form-control fw-bold <?= $isGraded ? 'border-success' : 'border-danger bg-white' ?>" value="<?= $isGraded ? (float)$ea['nilai'] : '' ?>" placeholder="Masukkan skor (0 - <?= $ea['bobot'] ?? 10 ?>)..." required>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                    <div class="modal-footer border-0 pt-0 p-4 justify-content-between">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <?php if (!empty($essayAnswers)): ?>
                            <button type="submit" class="btn btn-primary px-4 fw-bold rounded-pill text-white shadow-sm">
                                <i class="bi bi-save-fill me-1"></i> Simpan Nilai Essay
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
let questionCount = 1;

function toggleSoalInputs(selectElem, cardId) {
    const val = selectElem.value;
    const card = document.getElementById('qCard' + cardId);
    if (!card) return;

    const blockPg = card.querySelector('.block-pg-' + cardId);
    const blockTf = card.querySelector('.block-tf-' + cardId);

    if (val === 'pg') {
        if (blockPg) blockPg.classList.remove('d-none');
        if (blockTf) blockTf.classList.add('d-none');
    } else if (val === 'tf') {
        if (blockPg) blockPg.classList.add('d-none');
        if (blockTf) blockTf.classList.remove('d-none');
    } else {
        if (blockPg) blockPg.classList.add('d-none');
        if (blockTf) blockTf.classList.add('d-none');
    }
}

function addQuestionCard() {
    questionCount++;
    const container = document.getElementById('containerQuestions');
    const newCard = document.createElement('div');
    newCard.className = 'p-3 bg-light rounded-3 border mb-3 question-card position-relative';
    newCard.id = 'qCard' + questionCount;
    newCard.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-primary mb-0 q-number">Soal #${questionCount}</h6>
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" style="font-size:0.75rem;" onclick="removeQuestionCard('qCard${questionCount}')">
                <i class="bi bi-trash"></i> Hapus Soal Ini
            </button>
        </div>
        <div class="row g-2 mb-2">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Bentuk / Jenis Soal <span class="text-danger">*</span></label>
                <select name="jenis_soal[]" class="form-select form-select-sm" onchange="toggleSoalInputs(this, ${questionCount})" required>
                    <option value="pg">Pilihan Ganda (PG)</option>
                    <option value="tf">Benar / Salah (True / False)</option>
                    <option value="essay">Essay / Uraian</option>
                </select>
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label small fw-bold">Pertanyaan Soal <span class="text-danger">*</span></label>
            <input type="text" name="pertanyaan[]" class="form-control" placeholder="Masukkan pertanyaan soal..." required>
        </div>
        <div class="mb-2">
            <label class="form-label small fw-bold"><i class="bi bi-image text-primary me-1"></i>Lampiran Gambar Soal <span class="text-muted fw-normal">(Opsional)</span></label>
            <input type="file" name="gambar_soal[]" class="form-control form-control-sm" accept="image/*">
        </div>

        <div class="block-pg-${questionCount}">
            <div class="row g-2 mb-2">
                <div class="col-6"><input type="text" name="pil_a[]" class="form-control form-control-sm" placeholder="Pilihan A"></div>
                <div class="col-6"><input type="text" name="pil_b[]" class="form-control form-control-sm" placeholder="Pilihan B"></div>
                <div class="col-6"><input type="text" name="pil_c[]" class="form-control form-control-sm" placeholder="Pilihan C"></div>
                <div class="col-6"><input type="text" name="pil_d[]" class="form-control form-control-sm" placeholder="Pilihan D"></div>
                <div class="col-6"><input type="text" name="pil_e[]" class="form-control form-control-sm" placeholder="Pilihan E (Opsional)"></div>
                <div class="col-6"><input type="text" name="pil_f[]" class="form-control form-control-sm" placeholder="Pilihan F (Opsional)"></div>
            </div>
            <div>
                <label class="form-label small fw-bold text-success">Kunci Jawaban Benar (PG)</label>
                <select name="jawaban[]" class="form-select form-select-sm">
                    <option value="A">Pilihan A</option>
                    <option value="B">Pilihan B</option>
                    <option value="C">Pilihan C</option>
                    <option value="D">Pilihan D</option>
                    <option value="E">Pilihan E</option>
                    <option value="F">Pilihan F</option>
                </select>
            </div>
        </div>

        <div class="block-tf-${questionCount} d-none">
            <label class="form-label small fw-bold text-success">Kunci Jawaban Benar (Benar / Salah)</label>
            <select name="jawaban_tf[]" class="form-select form-select-sm">
                <option value="BENAR">BENAR (True)</option>
                <option value="SALAH">SALAH (False)</option>
            </select>
        </div>
    `;
    container.appendChild(newCard);
}

function removeQuestionCard(cardId) {
    const card = document.getElementById(cardId);
    if (card) {
        card.remove();
        reindexQuestionNumbers();
    }
}

function reindexQuestionNumbers() {
    const cards = document.querySelectorAll('.question-card');
    cards.forEach((card, index) => {
        const numHeading = card.querySelector('.q-number');
        if (numHeading) {
            numHeading.textContent = 'Soal #' + (index + 1);
        }
    });
}

function toggleAddSoalType(quizId) {
    const select = document.getElementById('selectJenisAdd' + quizId);
    const blockPg = document.getElementById('blockAddPg' + quizId);
    const blockTf = document.getElementById('blockAddTf' + quizId);

    if (!select) return;
    const val = select.value;

    if (val === 'pg') {
        if (blockPg) blockPg.classList.remove('d-none');
        if (blockTf) blockTf.classList.add('d-none');
    } else if (val === 'tf') {
        if (blockPg) blockPg.classList.add('d-none');
        if (blockTf) blockTf.classList.remove('d-none');
    } else {
        if (blockPg) blockPg.classList.add('d-none');
        if (blockTf) blockTf.classList.add('d-none');
    }
}

function filterGuruQuizTable() {
    const searchVal = document.getElementById('searchQuizInput').value.toLowerCase();
    const filterMapel = document.getElementById('filterQuizMapel').value;

    const rows = document.querySelectorAll('.guru-quiz-row');
    rows.forEach(row => {
        const title = (row.dataset.title || '').toLowerCase();
        const mapelId = row.dataset.mapel || '';

        const matchSearch = title.includes(searchVal);
        const matchMapel = !filterMapel || mapelId === filterMapel;

        if (matchSearch && matchMapel) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function setGuruEssayFilter(statusKey, btnElem) {
    const selectElem = document.getElementById('filterEssayStatus');
    if (selectElem) selectElem.value = statusKey;
    
    document.querySelectorAll('.essay-filter-pill').forEach(btn => {
        btn.classList.remove('active', 'btn-dark', 'btn-warning', 'btn-outline-success', 'btn-outline-primary', 'btn-outline-secondary');
        btn.classList.add('btn-outline-secondary');
    });

    if (btnElem) {
        btnElem.classList.remove('btn-outline-secondary');
        btnElem.classList.add('active');
        if (statusKey === 'pending') btnElem.classList.add('btn-warning', 'text-dark');
        else if (statusKey === 'graded') btnElem.classList.add('btn-outline-success');
        else if (statusKey === 'pg_auto') btnElem.classList.add('btn-outline-primary');
        else btnElem.classList.add('btn-dark');
    }

    filterGuruEssaySubmissions();
}

function filterGuruEssaySubmissions() {
    const searchVal = (document.getElementById('searchEssayInput')?.value || '').toLowerCase();
    const filterStatus = document.getElementById('filterEssayStatus')?.value || '';

    const rows = document.querySelectorAll('.guru-essay-row');
    rows.forEach(row => {
        const text = (row.dataset.text || '').toLowerCase();
        const status = row.dataset.status || '';

        const matchSearch = text.includes(searchVal);
        const matchStatus = !filterStatus || status === filterStatus;

        if (matchSearch && matchStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function generateRandomToken(inputId) {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let result = '';
    for (let i = 0; i < 6; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    const input = document.getElementById(inputId);
    if (input) input.value = result;
}

function handleKategoriChange(selectElem, targetInputId) {
    if (!selectElem) return;
    const val = selectElem.value;
    const inputElem = document.getElementById(targetInputId);
    if (!inputElem) return;

    if (val === 'uts' || val === 'uas') {
        if (!inputElem.value.trim() || inputElem.value.startsWith('KUIS')) {
            const prefix = val.toUpperCase();
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            let randomPart = '';
            for (let i = 0; i < 4; i++) {
                randomPart += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            inputElem.value = prefix + randomPart;
        }
    }
}

// ⚡ LIVE REAL-TIME BADGE & STATUS AUTO-UPDATER (TANPA REFRESH)
function pollQuizLiveStatus() {
    const isAdmin = <?= json_encode($isAdminMonitoring ?? false) ?>;
    const targetUrl = '<?= BASE_URL ?>index.php?url=' + (isAdmin ? 'admin' : 'guru') + '/quizLiveStatus';

    fetch(targetUrl)
        .then(response => response.json())
        .then(data => {
            if (data && data.status) {
                // Update Koreksi Essay Badge
                const essayTab = document.getElementById('tab-koreksi-tab');
                if (essayTab) {
                    let essayBadge = document.getElementById('badgeEssayCount');
                    if (data.pending_essay_count > 0) {
                        if (!essayBadge) {
                            essayBadge = document.createElement('span');
                            essayBadge.id = 'badgeEssayCount';
                            essayBadge.className = 'badge bg-danger rounded-pill px-2.5 py-1 ms-1.5';
                            essayBadge.style.fontSize = '0.72rem';
                            essayTab.appendChild(essayBadge);
                        }
                        essayBadge.textContent = data.pending_essay_count;
                        essayBadge.style.display = 'inline-block';
                    } else if (essayBadge) {
                        essayBadge.remove();
                    }
                }

                // Update Izin Susulan Badge
                const susulanTab = document.getElementById('tab-susulan-tab');
                if (susulanTab) {
                    let susulanBadge = document.getElementById('badgeSusulanCount');
                    if (data.pending_susulan_count > 0) {
                        if (!susulanBadge) {
                            susulanBadge = document.createElement('span');
                            susulanBadge.id = 'badgeSusulanCount';
                            susulanBadge.className = 'badge bg-warning text-dark rounded-pill px-2.5 py-1 ms-1.5';
                            susulanBadge.style.fontSize = '0.72rem';
                            susulanTab.appendChild(susulanBadge);
                        }
                        susulanBadge.textContent = data.pending_susulan_count;
                        susulanBadge.style.display = 'inline-block';
                    } else if (susulanBadge) {
                        susulanBadge.remove();
                    }
                }

                // Update KPI Cards
                const kpiEssayVal = document.getElementById('kpiPendingEssayVal');
                if (kpiEssayVal) {
                    kpiEssayVal.innerHTML = data.pending_essay_count + ' <span class="fs-6 fw-normal text-muted">' + (data.pending_essay_count > 0 ? 'Siswa Belum Dinilai' : 'Selesai') + '</span>';
                }

                const kpiSusulanVal = document.getElementById('kpiPendingSusulanVal');
                if (kpiSusulanVal) {
                    kpiSusulanVal.innerHTML = data.pending_susulan_count + ' <span class="fs-6 fw-normal text-muted">' + (data.pending_susulan_count > 0 ? 'Menunggu Konfirmasi' : 'Tuntas') + '</span>';
                }
            }
        })
        .catch(err => console.log('Polling status error:', err));
}

// Poll every 4 seconds for instant real-time responsiveness
setInterval(pollQuizLiveStatus, 4000);
document.addEventListener('DOMContentLoaded', function() {
    pollQuizLiveStatus();

    // Auto-activate tab based on URL query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam) {
        const targetBtn = document.getElementById('tab-' + tabParam + '-tab');
        if (targetBtn) {
            const bsTab = new bootstrap.Tab(targetBtn);
            bsTab.show();
        }
    }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
