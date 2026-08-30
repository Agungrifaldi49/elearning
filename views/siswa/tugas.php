<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* Modern Compact LMS Task Module Architecture */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.task-page-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
    background-color: #f8fafc;
    min-height: 100vh;
}

/* Compact KPI Cards */
.task-kpi-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    padding: 12px 16px;
    transition: all 0.2s ease;
    height: 100%;
}
.task-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
    border-color: #cbd5e1;
}
.task-kpi-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}

/* Modern Compact Filter Bar */
.filter-bar-container {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    padding: 8px 12px;
}
.filter-tab-btn {
    border: none;
    background: transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 0.8rem;
    padding: 5px 14px;
    border-radius: 20px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    white-space: nowrap;
}
.filter-tab-btn:hover {
    color: #0f172a;
    background-color: #f1f5f9;
}
.filter-tab-btn.active {
    background-color: #2563eb;
    color: #ffffff !important;
}
.filter-tab-btn .tab-count {
    font-size: 0.72rem;
    padding: 1px 6px;
    border-radius: 10px;
    background-color: rgba(0, 0, 0, 0.08);
    color: inherit;
}
.filter-tab-btn.active .tab-count {
    background-color: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}

/* Compact Task Card Item Architecture */
.task-card-item {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 100%;
}
.task-card-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06) !important;
    border-color: #cbd5e1;
}

/* Top Accent Bar */
.task-accent-bar {
    height: 3px;
    width: 100%;
}
.status-submitted .task-accent-bar {
    background: #10b981;
}
.status-active .task-accent-bar {
    background: #2563eb;
}
.status-locked .task-accent-bar {
    background: #f59e0b;
}
.status-expired .task-accent-bar {
    background: #ef4444;
}

/* Inner Action Box */
.task-action-box {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 14px;
}

/* Compact File Dropzone Box */
.custom-dropzone {
    border: 1.5px dashed #cbd5e1;
    border-radius: 10px;
    background-color: #ffffff;
    padding: 12px 16px;
    text-align: center;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
}
.custom-dropzone:hover, .custom-dropzone.dragover {
    border-color: #2563eb;
    background-color: #f0f7ff;
}
.custom-dropzone input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
.dropzone-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background-color: #eff6ff;
    color: #2563eb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    margin-bottom: 4px;
}

/* Chips Reason Buttons */
.chip-reason {
    font-size: 0.75rem;
    padding: 4px 10px;
    border-radius: 20px;
    border: 1px solid #cbd5e1;
    background-color: #ffffff;
    color: #475569;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.18s ease;
    display: inline-block;
}
.chip-reason:hover {
    background-color: #f1f5f9;
    border-color: #94a3b8;
    color: #0f172a;
}
</style>

<main class="main-content px-3 px-md-4 task-page-wrapper pt-3 mt-3 mt-md-4 pb-5">
<div class="container-fluid max-width-1400">
    
    <!-- 📊 COMPACT KPI SUMMARY CARDS -->
    <?php
    $totalTasks = count($tugasList);
    $submittedCount = count($submittedMap ?? []);
    $pendingCount = max(0, $totalTasks - $submittedCount);
    
    // Count locked/expired tasks
    $expiredOrLockedCount = 0;
    foreach ($tugasList as $tItem) {
        $isEnr = isset($enrolledMapels[$tItem['mapel_id'] . '_' . $tItem['guru_id']]) || isset($enrolledMapels[$tItem['mapel_id']]);
        $aCheck = $learningModel->canSiswaSubmitTugas($tItem['id'], $siswaId);
        $isExp = $aCheck['is_expired'] ?? false;
        $canAcc = $aCheck['access'] ?? false;
        $isSub = isset($submittedMap[$tItem['id']]);
        if (!$isSub && (!$isEnr || ($isExp && !$canAcc))) {
            $expiredOrLockedCount++;
        }
    }
    ?>
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="task-kpi-card d-flex align-items-center gap-2.5">
                <div class="task-kpi-icon bg-primary-subtle text-primary">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div>
                    <h5 class="fw-extrabold text-dark mb-0 lh-1"><?= $totalTasks ?></h5>
                    <span class="text-muted fw-semibold" style="font-size: 0.75rem;">Total Penugasan</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="task-kpi-card d-flex align-items-center gap-2.5">
                <div class="task-kpi-icon bg-success-subtle text-success">
                    <i class="bi bi-cloud-check-fill"></i>
                </div>
                <div>
                    <h5 class="fw-extrabold text-dark mb-0 lh-1"><?= $submittedCount ?></h5>
                    <span class="text-muted fw-semibold" style="font-size: 0.75rem;">Sudah Dikirim</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="task-kpi-card d-flex align-items-center gap-2.5">
                <div class="task-kpi-icon bg-warning-subtle text-warning-emphasis">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <h5 class="fw-extrabold text-dark mb-0 lh-1"><?= $pendingCount ?></h5>
                    <span class="text-muted fw-semibold" style="font-size: 0.75rem;">Perlu Dikumpul</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="task-kpi-card d-flex align-items-center gap-2.5">
                <div class="task-kpi-icon bg-info-subtle text-info">
                    <i class="bi bi-building-check"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0 text-truncate lh-1" style="max-width: 120px; font-size: 0.92rem;"><?= htmlspecialchars($siswa['nama_kelas'] ?? 'Rombel') ?></h6>
                    <span class="text-muted fw-semibold" style="font-size: 0.75rem;">Kelas Target</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 🎛️ COMPACT FILTER BAR & SEARCH CONTROLS -->
    <div class="filter-bar-container mb-3">
        <div class="row g-2 align-items-center">
            <!-- Tab Buttons -->
            <div class="col-12 col-lg-7">
                <div class="d-flex align-items-center gap-1 overflow-x-auto pb-1 pb-lg-0" style="scrollbar-width: thin;">
                    <button type="button" class="filter-tab-btn active" onclick="switchTaskTab('all', this)">
                        <i class="bi bi-grid-fill me-1"></i> Semua Tugas
                        <span class="tab-count"><?= $totalTasks ?></span>
                    </button>
                    <button type="button" class="filter-tab-btn" onclick="switchTaskTab('terdaftar', this)">
                        <i class="bi bi-hourglass-split me-1"></i> Perlu Dikumpul
                        <span class="tab-count"><?= $pendingCount ?></span>
                    </button>
                    <button type="button" class="filter-tab-btn" onclick="switchTaskTab('dikumpulkan', this)">
                        <i class="bi bi-check-circle-fill me-1"></i> Sudah Dikirim
                        <span class="tab-count"><?= $submittedCount ?></span>
                    </button>
                    <button type="button" class="filter-tab-btn" onclick="switchTaskTab('terkunci', this)">
                        <i class="bi bi-lock-fill me-1"></i> Terkunci / Expired
                        <span class="tab-count"><?= $expiredOrLockedCount ?></span>
                    </button>
                </div>
            </div>

            <!-- Search & Mapel Dropdown -->
            <div class="col-12 col-lg-5">
                <div class="row g-2">
                    <div class="col-7 col-sm-8">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-2.5 text-muted">
                                <i class="bi bi-search" style="font-size:0.8rem;"></i>
                            </span>
                            <input type="text" id="searchInput" class="form-control form-control-sm bg-light border-start-0 rounded-end-pill ps-0 text-dark" placeholder="Cari judul tugas / mapel..." oninput="applyFilters()" style="font-size: 0.8rem;">
                        </div>
                    </div>
                    <div class="col-5 col-sm-4">
                        <select id="filterMapel" class="form-select form-select-sm rounded-pill text-dark" onchange="applyFilters()" style="font-size: 0.8rem;">
                            <option value="">Semua Mapel</option>
                            <?php 
                            $mapelNames = array_unique(array_column($tugasList, 'nama_mapel'));
                            foreach ($mapelNames as $mName):
                            ?>
                                <option value="<?= htmlspecialchars($mName) ?>"><?= htmlspecialchars($mName) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 📋 TASK CARDS GRID -->
    <div class="row g-3 mb-4" id="taskCardsGrid">
        <?php if (empty($tugasList)): ?>
            <div class="col-12">
                <div class="card border-0 rounded-3 shadow-sm p-4 text-center bg-white">
                    <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px;">
                        <i class="bi bi-journal-check fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Belum Ada Penugasan Aktif</h6>
                    <p class="text-muted small mb-0">Belum ada tugas baru yang ditambahkan oleh Guru untuk kelas Anda saat ini.</p>
                </div>
            </div>
        <?php else: ?>
            <div id="emptyFilterNotice" class="col-12 text-center d-none py-4 bg-white rounded-3 shadow-sm border">
                <i class="bi bi-search fs-3 text-muted d-block mb-1"></i>
                <h6 class="fw-bold text-dark mb-1 small">Tugas Tidak Ditemukan</h6>
                <p class="text-muted small mb-0" style="font-size:0.78rem;">Tidak ada tugas yang cocok dengan filter atau kata kunci pencarian Anda.</p>
            </div>

            <?php foreach ($tugasList as $t): 
                $isEnrolled = isset($enrolledMapels[$t['mapel_id'] . '_' . $t['guru_id']]) || isset($enrolledMapels[$t['mapel_id']]);
                $accessCheck = $learningModel->canSiswaSubmitTugas($t['id'], $siswaId);
                $isExpired = $accessCheck['is_expired'] ?? false;
                $canAccess = $accessCheck['access'] ?? false;
                $statusAccess = $accessCheck['status'] ?? 'terbuka';
                
                $isSubmitted = isset($submittedMap[$t['id']]);
                $subData = $isSubmitted ? $submittedMap[$t['id']] : null;

                $cardStatusClass = $isSubmitted ? 'status-submitted' : (!$isEnrolled ? 'status-locked' : ($isExpired && !$canAccess ? 'status-expired' : 'status-active'));
                $statusCardVal = $isSubmitted ? 'dikumpulkan' : (!$isEnrolled ? 'terkunci' : ($isExpired && !$canAccess ? 'terkunci' : 'terdaftar'));
                
                // Calculate deadline status
                $deadlineTime = strtotime($t['deadline']);
                $isNearDeadline = ($deadlineTime - time() < 86400) && ($deadlineTime > time());
            ?>
                <div class="col-12 col-lg-6 task-item-col" data-title="<?= htmlspecialchars(strtolower($t['judul'])) ?>" data-mapel="<?= htmlspecialchars($t['nama_mapel']) ?>" data-status="<?= $statusCardVal ?>">
                    <div class="task-card-item <?= $cardStatusClass ?>">
                        
                        <!-- Top Accent Bar -->
                        <div class="task-accent-bar"></div>

                        <div class="p-3 p-md-3.5 d-flex flex-column h-100 justify-content-between">
                            <div>
                                <!-- Top Header Badges -->
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-1 px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                                        <i class="bi bi-book me-1"></i><?= htmlspecialchars($t['nama_mapel']) ?>
                                    </span>

                                    <div>
                                        <?php if ($isSubmitted): ?>
                                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.7rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i>Sudah Dikirim
                                            </span>
                                        <?php elseif (!$isEnrolled): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.7rem;">
                                                <i class="bi bi-lock-fill me-1"></i>Mapel Terkunci
                                            </span>
                                        <?php elseif ($isExpired && !$canAccess): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.7rem;">
                                                <i class="bi bi-clock-history me-1"></i>Terkunci (Expired)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.7rem;">
                                                <i class="bi bi-clock me-1"></i>Aktif / Belum Dikumpul
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Title -->
                                <h6 class="fw-bold mb-1.5 text-dark lh-sm" style="font-size: 0.95rem; letter-spacing: -0.2px;"><?= htmlspecialchars($t['judul']) ?></h6>
                                
                                <!-- Meta Info (Guru & Deadline) -->
                                <div class="d-flex align-items-center flex-wrap gap-2.5 mb-2.5 text-muted" style="font-size: 0.78rem;">
                                    <span><i class="bi bi-person-circle text-primary me-1"></i>Guru: <strong class="text-dark"><?= htmlspecialchars($t['nama_guru']) ?></strong></span>
                                    <span class="<?= $isNearDeadline ? 'text-danger fw-bold' : 'text-danger' ?>">
                                        <i class="bi bi-calendar-event me-1"></i>Deadline: <?= date('d M Y, H:i', strtotime($t['deadline'])) ?> WIB
                                    </span>
                                </div>

                                <!-- Task Description Snippet -->
                                <p class="text-secondary mb-2.5 lh-sm" style="font-size: 0.82rem; color: #475569;">
                                    <?= nl2br(htmlspecialchars($t['deskripsi'])) ?>
                                </p>
                            </div>

                            <!-- 📎 LAMPIRAN BERKAS SOAL GURU -->
                            <?php if (!empty($t['file_path'])): 
                                $tFilePath = BASE_URL . 'assets/uploads/tugas/' . htmlspecialchars($t['file_path']);
                                $fileExt = strtoupper(pathinfo($t['file_path'], PATHINFO_EXTENSION));
                            ?>
                                <div class="p-2 px-2.5 bg-light rounded-3 border mb-2.5">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                                            <i class="bi bi-file-earmark-arrow-down-fill text-primary fs-6 flex-shrink-0"></i>
                                            <div class="overflow-hidden">
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="fw-semibold text-dark text-truncate" style="font-size: 0.78rem; max-width: 180px;"><?= htmlspecialchars($t['file_path']) ?></span>
                                                    <span class="badge bg-secondary-subtle text-secondary rounded px-1.5 py-0.5" style="font-size: 0.65rem;"><?= $fileExt ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-1 ms-auto flex-shrink-0">
                                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.73rem;" data-bs-toggle="modal" data-bs-target="#modalPreviewTaskFile<?= $t['id'] ?>">
                                                <i class="bi bi-eye-fill me-1"></i> Baca
                                            </button>
                                            <a href="<?= $tFilePath ?>" download class="btn btn-xs btn-primary rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.73rem;">
                                                <i class="bi bi-download me-1"></i> Unduh
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- 📥 ACTION AREA (SUBMISSION / STATUS) -->
                            <div class="mt-1">
                                <?php if (!$isEnrolled): ?>
                                    <div class="task-action-box bg-warning-subtle border-warning-subtle text-center p-2.5">
                                        <div class="fw-bold text-warning-emphasis mb-1 small"><i class="bi bi-shield-lock-fill me-1"></i>Akses Penugasan Terkunci</div>
                                        <p class="text-muted mb-2" style="font-size:0.75rem;">Terdaftar mapel ini dahulu dengan Kode Akses (Key) dari Guru.</p>
                                        <a href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" class="btn btn-warning text-dark w-100 fw-bold rounded-pill py-1.5" style="font-size:0.78rem;">
                                            <i class="bi bi-key-fill me-1"></i> Input Key Mapel
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <?php if ($isSubmitted): ?>
                                        <!-- SUBMITTED ANSWER STATUS BOX -->
                                        <div class="task-action-box bg-success-subtle border-success-subtle p-2.5 rounded-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-1">
                                                <span class="fw-bold text-success d-flex align-items-center gap-1" style="font-size:0.78rem;">
                                                    <i class="bi bi-check-circle-fill"></i> Jawaban Terkirim
                                                </span>
                                                <small class="text-muted" style="font-size:0.72rem;">
                                                    <?= date('d M Y, H:i', strtotime($subData['created_at'])) ?> WIB
                                                </small>
                                            </div>

                                            <?php if (!empty($subData['file_path'])): 
                                                $subFilePath = BASE_URL . 'assets/uploads/tugas/' . htmlspecialchars($subData['file_path']);
                                            ?>
                                                <div class="p-2 bg-white rounded border d-flex align-items-center justify-content-between mb-1.5">
                                                    <div class="d-flex align-items-center gap-1.5 overflow-hidden">
                                                        <i class="bi bi-file-earmark-check-fill text-success" style="font-size:0.9rem;"></i>
                                                        <span class="fw-semibold text-dark text-truncate" style="font-size:0.75rem; max-width: 200px;"><?= htmlspecialchars($subData['file_path']) ?></span>
                                                    </div>
                                                    <a href="<?= $subFilePath ?>" download class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-0.5 fw-bold" style="font-size:0.72rem;">
                                                        <i class="bi bi-download me-1"></i> Unduh
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <!-- SCORE & TEACHER NOTE -->
                                            <?php if ($subData['nilai'] !== null): ?>
                                                <div class="p-2 bg-white rounded border border-success-subtle mb-1">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="fw-bold text-dark" style="font-size:0.76rem;"><i class="bi bi-star-fill text-warning me-1"></i>Nilai Guru:</span>
                                                        <span class="badge bg-success text-white fw-bold px-2 py-0.5 rounded-pill" style="font-size:0.8rem;">
                                                            <?= number_format($subData['nilai'], 1) ?>
                                                        </span>
                                                    </div>
                                                    <?php if (!empty($subData['catatan_guru'])): ?>
                                                        <div class="mt-1 pt-1 border-top text-muted" style="font-size:0.74rem;">
                                                            <strong>Catatan Guru:</strong> <?= htmlspecialchars($subData['catatan_guru']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="p-1.5 bg-white rounded border text-center text-muted" style="font-size:0.74rem;">
                                                    <i class="bi bi-hourglass-split me-1 text-warning"></i>Status: <strong>Belum Dinilai Guru</strong>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($canAccess): ?>
                                                <div class="mt-2 pt-1 border-top">
                                                    <button type="button" class="btn btn-xs btn-outline-primary w-100 rounded-pill fw-bold py-1" style="font-size:0.75rem;" data-bs-toggle="collapse" data-bs-target="#reuploadForm<?= $t['id'] ?>">
                                                        <i class="bi bi-arrow-repeat me-1"></i> Update / Kirim Ulang Jawaban
                                                    </button>

                                                    <div class="collapse mt-2" id="reuploadForm<?= $t['id'] ?>">
                                                        <form action="<?= BASE_URL ?>index.php?url=siswa/tugas" method="POST" enctype="multipart/form-data" class="p-2.5 bg-white rounded border shadow-sm">
                                                            <?= Security::csrfField() ?>
                                                            <input type="hidden" name="tugas_id" value="<?= $t['id'] ?>">
                                                            
                                                            <div class="mb-2">
                                                                <label class="form-label fw-bold text-dark mb-1" style="font-size:0.75rem;">Upload Berkas Perbaikan</label>
                                                                <input type="file" name="file" class="form-control form-control-sm rounded" required style="font-size:0.78rem;">
                                                            </div>
                                                            <div class="mb-2">
                                                                <input type="text" name="catatan_siswa" class="form-control form-control-sm rounded" placeholder="Catatan perbaikan (Opsional)" style="font-size:0.78rem;">
                                                            </div>
                                                            <button type="submit" class="btn btn-xs btn-primary w-100 rounded-pill fw-bold py-1.5" style="font-size:0.78rem;">
                                                                <i class="bi bi-send-fill me-1"></i> Kirim Pembaharuan
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif ($canAccess): ?>
                                        <!-- NEW SUBMISSION FORM BOX (DROPZONE) -->
                                        <div class="task-action-box">
                                            <?php if ($statusAccess === 'disetujui_susulan'): ?>
                                                <div class="p-1.5 bg-success-subtle text-success border border-success-subtle rounded mb-2 text-center fw-bold" style="font-size:0.74rem;">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Izin Susulan Disetujui (Pengumpulan Dibuka)
                                                </div>
                                            <?php endif; ?>

                                            <span class="fw-bold text-primary mb-2 d-block" style="font-size:0.78rem;">
                                                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Unggah Lembar Jawaban Tugas
                                            </span>
                                            
                                            <form action="<?= BASE_URL ?>index.php?url=siswa/tugas" method="POST" enctype="multipart/form-data">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="tugas_id" value="<?= $t['id'] ?>">

                                                <!-- Compact File Dropzone -->
                                                <div class="custom-dropzone mb-2" id="dropzoneBox<?= $t['id'] ?>" onclick="document.getElementById('fileInput<?= $t['id'] ?>').click()">
                                                    <input type="file" name="file" id="fileInput<?= $t['id'] ?>" required onchange="handleFileSelected(this, '<?= $t['id'] ?>')">
                                                    <div id="dropzoneInitial<?= $t['id'] ?>">
                                                        <div class="dropzone-icon">
                                                            <i class="bi bi-cloud-upload-fill"></i>
                                                        </div>
                                                        <div class="fw-bold text-dark mb-0" style="font-size:0.78rem;">Pilih Berkas atau Tarik File Ke Sini</div>
                                                        <span class="text-muted d-block" style="font-size:0.7rem;">Format: PDF, DOCX, PPTX, ZIP, PNG, JPG (Maks 25MB)</span>
                                                    </div>
                                                    <div id="dropzoneSelected<?= $t['id'] ?>" class="d-none py-1">
                                                        <i class="bi bi-file-earmark-check-fill text-success fs-5 mb-0.5 d-block"></i>
                                                        <span id="fileName<?= $t['id'] ?>" class="fw-bold text-dark d-block text-truncate mx-auto" style="font-size:0.76rem; max-width: 220px;">-</span>
                                                        <small id="fileSize<?= $t['id'] ?>" class="text-muted d-block mb-1" style="font-size:0.7rem;">-</small>
                                                        <span class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0.5 fw-bold" style="font-size:0.68rem;" onclick="resetFileInput(event, '<?= $t['id'] ?>')">
                                                            <i class="bi bi-x-circle me-1"></i> Ganti File
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="mb-2">
                                                    <input type="text" name="catatan_siswa" class="form-control form-control-sm rounded" placeholder="Catatan tambahan untuk Guru (Opsional)" style="font-size:0.78rem;">
                                                </div>
                                                
                                                <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill py-1.5 text-white" style="font-size:0.8rem; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                                                    <i class="bi bi-send-fill me-1"></i> Kirim Jawaban Tugas
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <!-- EXPIRED STATE -->
                                        <div class="task-action-box text-center border-danger-subtle bg-danger-subtle p-2.5" style="background-color: #fff5f5;">
                                            <div class="fw-bold text-danger mb-1" style="font-size:0.78rem;"><i class="bi bi-clock-history me-1"></i>Batas Waktu Pengumpulan Berakhir</div>
                                            <p class="text-muted mb-2" style="font-size:0.74rem;">Tugas telah melewati deadline. Silakan minta izin susulan ke Guru.</p>
                                            
                                            <?php
                                            $db = Database::getConnection();
                                            $stmtSus = $db->prepare("SELECT status FROM tugas_susulan WHERE tugas_id = ? AND siswa_id = ?");
                                            $stmtSus->execute([$t['id'], $siswaId]);
                                            $susStatus = $stmtSus->fetchColumn();
                                            ?>
                                            <?php if ($susStatus === 'pending'): ?>
                                                <button class="btn btn-warning text-dark w-100 fw-bold rounded-pill py-1.5" style="font-size:0.75rem;" disabled>
                                                    <i class="bi bi-hourglass-split me-1"></i> Permohonan Susulan Dikirim (Menunggu Guru)
                                                </button>
                                            <?php elseif ($susStatus === 'ditolak'): ?>
                                                <div class="badge bg-danger-subtle text-danger border border-danger-subtle w-100 py-1.5 rounded-pill fw-bold" style="font-size:0.75rem;">
                                                    <i class="bi bi-x-circle-fill me-1"></i> Permohonan Susulan Ditolak Guru
                                                </div>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline-danger w-100 fw-bold rounded-pill py-1.5" style="font-size:0.78rem;" data-bs-toggle="modal" data-bs-target="#modalSusulanTugas<?= $t['id'] ?>">
                                                    <i class="bi bi-envelope-paper-fill me-1"></i> Ajukan Permohonan Susulan
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Modal Susulan Tugas -->
                <div class="modal fade" id="modalSusulanTugas<?= $t['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-3 border-0 shadow-lg overflow-hidden">
                            <div class="modal-header border-0 bg-dark text-white p-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                                <h6 class="modal-title fw-bold text-white mb-0" style="font-size:0.9rem;"><i class="bi bi-envelope-paper-fill text-warning me-1.5"></i>Permohonan Izin Susulan Tugas</h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="<?= BASE_URL ?>index.php?url=siswa/tugas" method="POST">
                                <div class="modal-body p-3.5 bg-light">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="request_tugas_susulan">
                                    <input type="hidden" name="tugas_id" value="<?= $t['id'] ?>">

                                    <div class="p-2.5 bg-white rounded border mb-2.5 shadow-xs">
                                        <small class="text-muted d-block" style="font-size:0.72rem;">Tugas Target:</small>
                                        <h6 class="fw-bold text-dark mb-0.5" style="font-size:0.88rem;"><?= htmlspecialchars($t['judul']) ?></h6>
                                        <small class="text-primary fw-bold" style="font-size:0.75rem;"><?= htmlspecialchars($t['nama_mapel']) ?></small>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label fw-bold text-dark mb-1" style="font-size:0.78rem;">Pilih Alasan Cepat (Atau Ketik Alasan Custom):</label>
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            <span class="chip-reason" onclick="setSusulanReason(this, '<?= $t['id'] ?>')">Sakit / Izin Medis</span>
                                            <span class="chip-reason" onclick="setSusulanReason(this, '<?= $t['id'] ?>')">Kendala Jaringan Internet / Lampu Padam</span>
                                            <span class="chip-reason" onclick="setSusulanReason(this, '<?= $t['id'] ?>')">Kendala Laptop / Handphone Rusak</span>
                                            <span class="chip-reason" onclick="setSusulanReason(this, '<?= $t['id'] ?>')">Urusan Keluarga Important</span>
                                        </div>

                                        <textarea id="catatanSusulan<?= $t['id'] ?>" name="catatan_susulan" class="form-control form-control-sm rounded" rows="3" placeholder="Jelaskan alasan keterlambatan Anda secara sopan kepada Guru..." style="font-size:0.8rem;" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0 p-3 justify-content-between gap-2">
                                    <button type="button" class="btn btn-light rounded-pill px-3 py-1.5" style="font-size:0.8rem;" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger px-4 py-1.5 fw-bold rounded-pill text-white shadow-xs" style="font-size:0.8rem; background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);">
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

<!-- Modal Preview Lampiran Soal Guru -->
<?php if (!empty($tugasList)): ?>
    <?php foreach ($tugasList as $t): ?>
        <?php if (!empty($t['file_path'])): 
            $tFilePath = BASE_URL . 'assets/uploads/tugas/' . htmlspecialchars($t['file_path']);
            $tExt = strtolower(pathinfo($t['file_path'], PATHINFO_EXTENSION));
            $isPdf = ($tExt === 'pdf');
            $isImg = in_array($tExt, ['png', 'jpg', 'jpeg', 'gif', 'webp']);
        ?>
            <div class="modal fade" id="modalPreviewTaskFile<?= $t['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content rounded-3 border-0 shadow-lg overflow-hidden">
                        <div class="modal-header border-0 bg-dark text-white p-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary rounded p-1.5 text-white">
                                    <i class="bi bi-file-earmark-text-fill fs-6"></i>
                                </div>
                                <div>
                                    <h6 class="modal-title fw-bold text-white mb-0" style="font-size:0.9rem;">Lampiran Soal Guru: <?= htmlspecialchars($t['judul']) ?></h6>
                                    <small class="text-info fw-medium" style="font-size:0.75rem;">Mapel: <?= htmlspecialchars($t['nama_mapel']) ?> &bull; Guru: <?= htmlspecialchars($t['nama_guru']) ?></small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-3.5 bg-light">
                            <div class="p-2.5 bg-white rounded border mb-2.5 shadow-xs">
                                <h6 class="fw-bold text-dark mb-1" style="font-size:0.85rem;"><i class="bi bi-info-circle text-primary me-1"></i>Instruksi Penugasan:</h6>
                                <p class="text-secondary small mb-0 lh-sm" style="font-size:0.8rem;"><?= nl2br(htmlspecialchars($t['deskripsi'])) ?></p>
                            </div>

                            <div class="border rounded bg-white p-1.5 shadow-sm overflow-hidden text-center">
                                <?php if ($isPdf): ?>
                                    <iframe src="<?= $tFilePath ?>#toolbar=0" style="width:100%; height:550px; border:none;" class="rounded"></iframe>
                                <?php elseif ($isImg): ?>
                                    <img src="<?= $tFilePath ?>" alt="Preview Lampiran Soal" class="img-fluid rounded mx-auto d-block shadow-sm" style="max-height:500px; object-fit:contain;">
                                <?php else: ?>
                                    <div class="p-4 text-center">
                                        <i class="bi bi-file-earmark-zip-fill fs-2 text-primary mb-1 d-block"></i>
                                        <h6 class="fw-bold text-dark small">Pratinjau Langsung Tidak Tersedia untuk Format Berkas Ini</h6>
                                        <p class="text-muted small mb-2" style="font-size:0.78rem;">Berkas ini adalah dokumen berformat .<?= $tExt ?>. Silakan unduh berkas untuk membukanya secara penuh.</p>
                                        <a href="<?= $tFilePath ?>" download class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold" style="font-size:0.78rem;">
                                            <i class="bi bi-download me-1"></i> Unduh Berkas Soal (<?= strtoupper($tExt) ?>)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 p-3 justify-content-between bg-white border-top">
                            <button type="button" class="btn btn-light rounded-pill px-3 py-1.5" style="font-size:0.8rem;" data-bs-dismiss="modal">Tutup</button>
                            <a href="<?= $tFilePath ?>" download class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold" style="font-size:0.8rem;">
                                <i class="bi bi-download me-1"></i> Unduh Lampiran Soal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

</main>

<script>
let currentTabFilter = 'all';

function switchTaskTab(tabStatus, btnElem) {
    currentTabFilter = tabStatus;
    
    // Toggle tab active styles
    const tabBtns = document.querySelectorAll('.filter-tab-btn');
    tabBtns.forEach(btn => btn.classList.remove('active'));
    if (btnElem) {
        btnElem.classList.add('active');
    }
    
    applyFilters();
}

function applyFilters() {
    const searchVal = document.getElementById('searchInput').value.toLowerCase().trim();
    const mapelVal = document.getElementById('filterMapel').value;

    const items = document.querySelectorAll('.task-item-col');
    let visibleCount = 0;

    items.forEach(item => {
        const title = item.getAttribute('data-title') || '';
        const mapel = item.getAttribute('data-mapel') || '';
        const status = item.getAttribute('data-status') || '';

        const matchSearch = title.includes(searchVal) || mapel.toLowerCase().includes(searchVal);
        const matchMapel = (mapelVal === '' || mapel === mapelVal);
        const matchTab = (currentTabFilter === 'all' || status === currentTabFilter);

        if (matchSearch && matchMapel && matchTab) {
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

/* File Dropzone Handler */
function handleFileSelected(inputElem, taskId) {
    if (inputElem.files && inputElem.files[0]) {
        const file = inputElem.files[0];
        document.getElementById('fileName' + taskId).textContent = file.name;
        document.getElementById('fileSize' + taskId).textContent = formatBytes(file.size);
        
        document.getElementById('dropzoneInitial' + taskId).classList.add('d-none');
        document.getElementById('dropzoneSelected' + taskId).classList.remove('d-none');
    }
}

function resetFileInput(event, taskId) {
    event.stopPropagation();
    const input = document.getElementById('fileInput' + taskId);
    if (input) input.value = '';
    
    document.getElementById('dropzoneInitial' + taskId).classList.remove('d-none');
    document.getElementById('dropzoneSelected' + taskId).classList.add('d-none');
}

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

/* Preset Chips Reason for Late Submission Request */
function setSusulanReason(chipElem, taskId) {
    const reasonText = chipElem.textContent.trim();
    const textarea = document.getElementById('catatanSusulan' + taskId);
    if (textarea) {
        textarea.value = "Mohon maaf Bapak/Ibu Guru, saya mengajukan permohonan susulan dikarenakan: " + reasonText + ".";
    }
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
