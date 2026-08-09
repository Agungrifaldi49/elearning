<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* Modern LMS Task Module Architecture */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.task-page-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
    padding-top: 28px !important;
}

/* Premium Glassmorphic Hero Banner */
.task-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #2563eb 100%);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
    position: relative;
    overflow: hidden;
    color: #ffffff;
}
.task-hero-banner::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}

/* KPI Summary Cards */
.task-kpi-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    padding: 18px 20px;
    transition: all 0.2s ease;
    height: 100%;
}
.task-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.06);
}
.task-kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
}

/* Task Card Architecture */
.task-card-item {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
}
.task-card-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08) !important;
    border-color: #cbd5e1;
}

/* Card Top Border Accents */
.task-card-item.status-submitted {
    border-top: 4px solid #10b981 !important;
}
.task-card-item.status-active {
    border-top: 4px solid #2563eb !important;
}
.task-card-item.status-locked {
    border-top: 4px solid #f59e0b !important;
}
.task-card-item.status-expired {
    border-top: 4px solid #ef4444 !important;
}

/* Inner Action Box */
.task-action-box {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 18px;
}

/* Custom Badges */
.badge-mapel-tag {
    background-color: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-weight: 700;
    font-size: 0.78rem;
    padding: 6px 14px;
    border-radius: 50rem;
}

.badge-deadline-tag {
    background-color: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    font-weight: 600;
    font-size: 0.78rem;
    padding: 6px 12px;
    border-radius: 50rem;
}
</style>

<main class="main-content px-3 px-md-4 task-page-wrapper pt-4 mt-4 mt-md-5">
<div class="container-fluid">
    
    <!-- 🚀 HERO BANNER SISWA TUGAS -->
    <div class="task-hero-banner p-4 p-md-5 mb-4">
        <div class="row align-items-center relative-zIndex-1">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="d-inline-flex align-items-center gap-2 px-3.5 py-2 rounded-pill bg-warning text-dark shadow-sm small fw-bold mb-3">
                    <i class="bi bi-award-fill text-dark fs-6"></i>
                    <span>Control Center Penugasan & Evaluasi Siswa</span>
                </div>
                <h2 class="fw-bold mb-2 text-white" style="letter-spacing: -0.5px;">Daftar Tugas & Penugasan Portofolio</h2>
                <p class="text-white text-opacity-85 small mb-0 lh-lg" style="max-width: 650px;">
                    Periksa instruksi tugas, pratinjau berkas soal dari Guru, dan unggah lembar jawaban Anda secara praktis, aman, dan realtime.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" class="btn btn-warning text-dark px-4 py-2.5 rounded-pill fw-bold shadow-lg d-inline-flex align-items-center gap-2 hover-scale">
                    <i class="bi bi-key-fill fs-5"></i>
                    <span>Daftar Mapel Baru (Key)</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 📊 KPI SUMMARY CARDS -->
    <?php
    $totalTasks = count($tugasList);
    $submittedCount = count($submittedMap ?? []);
    $pendingCount = max(0, $totalTasks - $submittedCount);
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="task-kpi-card d-flex align-items-center gap-3">
                <div class="task-kpi-icon bg-primary-subtle text-primary">
                    <i class="bi bi-card-checklist"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-dark mb-0"><?= $totalTasks ?></h4>
                    <span class="text-muted small fw-semibold">Total Penugasan</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="task-kpi-card d-flex align-items-center gap-3">
                <div class="task-kpi-icon bg-success-subtle text-success">
                    <i class="bi bi-cloud-check-fill"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-dark mb-0"><?= $submittedCount ?></h4>
                    <span class="text-muted small fw-semibold">Sudah Dikumpul</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="task-kpi-card d-flex align-items-center gap-3">
                <div class="task-kpi-icon bg-warning-subtle text-warning-emphasis">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-dark mb-0"><?= $pendingCount ?></h4>
                    <span class="text-muted small fw-semibold">Belum Dikumpul</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="task-kpi-card d-flex align-items-center gap-3">
                <div class="task-kpi-icon bg-info-subtle text-info">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 120px;"><?= htmlspecialchars($siswa['nama_kelas'] ?? 'Rombel') ?></h5>
                    <span class="text-muted small fw-semibold">Kelas Target</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 🎛️ SEARCH & FILTER CONTROLS CARD -->
    <div class="card border-0 rounded-4 shadow-sm p-3.5 mb-4 bg-white">
        <div class="row g-2.5 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchInput" class="form-control bg-light border-start-0 rounded-end-pill ps-0 text-slate-800" placeholder="Cari judul tugas atau nama mapel..." oninput="filterTaskItems()" style="font-size: 0.88rem;">
                </div>
            </div>
            <div class="col-6 col-md-3.5">
                <select id="filterMapel" class="form-select rounded-pill text-slate-700" onchange="filterTaskItems()" style="font-size: 0.85rem;">
                    <option value="">Semua Mata Pelajaran</option>
                    <?php 
                    $mapelNames = array_unique(array_column($tugasList, 'nama_mapel'));
                    foreach ($mapelNames as $mName):
                    ?>
                        <option value="<?= htmlspecialchars($mName) ?>"><?= htmlspecialchars($mName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3.5">
                <select id="filterStatus" class="form-select rounded-pill text-slate-700" onchange="filterTaskItems()" style="font-size: 0.85rem;">
                    <option value="">Semua Status</option>
                    <option value="dikumpulkan">Sudah Dikumpulkan</option>
                    <option value="terdaftar">Aktif / Belum Dikumpulkan</option>
                    <option value="terkunci">Terkunci / Expired</option>
                </select>
            </div>
        </div>
    </div>

    <!-- 📋 TASK CARDS GRID -->
    <div class="row g-4 mb-4">
        <?php if (empty($tugasList)): ?>
            <div class="col-12">
                <div class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white">
                    <div class="bg-slate-100 text-slate-400 rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 75px; height: 75px; background-color: #f1f5f9;">
                        <i class="bi bi-check2-all fs-1 text-slate-400"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Belum Ada Penugasan Aktif</h5>
                    <p class="text-muted small mb-0">Belum ada tugas baru yang ditambahkan oleh Guru untuk kelas Anda saat ini.</p>
                </div>
            </div>
        <?php else: ?>
            <div id="emptyFilterNotice" class="col-12 text-center d-none py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-search fs-1 text-secondary d-block mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">Tugas Tidak Ditemukan</h6>
                <p class="text-muted small mb-0">Tidak ada tugas yang sesuai dengan pencarian atau filter yang Anda pilih.</p>
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
            ?>
                <div class="col-12 col-lg-6 task-item-col" data-title="<?= htmlspecialchars(strtolower($t['judul'])) ?>" data-mapel="<?= htmlspecialchars($t['nama_mapel']) ?>" data-status="<?= $statusCardVal ?>">
                    <div class="task-card-item <?= $cardStatusClass ?> p-4">
                        
                        <!-- Top Header Badges -->
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <span class="badge-mapel-tag">
                                <i class="bi bi-journal-bookmark-fill me-1"></i><?= htmlspecialchars($t['nama_mapel']) ?>
                            </span>

                            <div>
                                <?php if ($isSubmitted): ?>
                                    <span class="badge bg-success text-white rounded-pill px-3 py-1 fw-bold small shadow-xs">
                                        <i class="bi bi-check-all me-1"></i>Sudah Dikumpulkan
                                    </span>
                                <?php elseif (!$isEnrolled): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold small">
                                        <i class="bi bi-lock-fill me-1"></i>Mapel Terkunci
                                    </span>
                                <?php elseif ($isExpired && !$canAccess): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold small">
                                        <i class="bi bi-clock-history me-1"></i>Expired (Terkunci)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold small">
                                        <i class="bi bi-check-circle-fill me-1"></i>Terdaftar / Aktif
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Title & Meta Info -->
                        <h5 class="fw-bold mb-2 text-dark" style="letter-spacing: -0.2px; line-height: 1.35;"><?= htmlspecialchars($t['judul']) ?></h5>
                        
                        <div class="d-flex align-items-center flex-wrap gap-3 mb-3 pb-3 border-bottom text-muted small">
                            <span><i class="bi bi-person-circle text-primary me-1"></i>Guru: <strong><?= htmlspecialchars($t['nama_guru']) ?></strong></span>
                            <span class="badge-deadline-tag">
                                <i class="bi bi-calendar-event me-1"></i>Deadline: <?= date('d M Y, H:i', strtotime($t['deadline'])) ?> WIB
                            </span>
                        </div>

                        <!-- Task Description -->
                        <p class="text-slate-600 small mb-3 flex-grow-1" style="color: #475569; line-height: 1.6; font-size: 0.9rem;">
                            <?= nl2br(htmlspecialchars($t['deskripsi'])) ?>
                        </p>

                        <!-- 📎 LAMPIRAN BERKAS SOAL GURU -->
                        <?php if (!empty($t['file_path'])): 
                            $tFilePath = BASE_URL . 'assets/uploads/tugas/' . htmlspecialchars($t['file_path']);
                        ?>
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary-subtle text-primary p-2 rounded-3">
                                            <i class="bi bi-paperclip fs-5"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark small d-block mb-0.5"><i class="bi bi-file-earmark-arrow-down-fill text-primary me-1"></i>Lampiran Berkas Soal Guru</span>
                                            <small class="text-muted text-break" style="font-size:0.76rem;"><?= htmlspecialchars($t['file_path']) ?></small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-1.5 ms-auto">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalPreviewTaskFile<?= $t['id'] ?>">
                                            <i class="bi bi-eye-fill me-1"></i> Baca Lampiran
                                        </button>
                                        <a href="<?= $tFilePath ?>" download class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-xs">
                                            <i class="bi bi-download me-1"></i> Unduh
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- 📥 ACTION AREA (SUBMISSION / STATUS) -->
                        <div class="mt-auto">
                            <?php if (!$isEnrolled): ?>
                                <div class="task-action-box bg-danger-subtle border-danger-subtle text-center">
                                    <div class="fw-bold text-danger mb-1"><i class="bi bi-shield-lock-fill me-1"></i>Akses Penugasan Terkunci!</div>
                                    <p class="text-muted small mb-3">Anda wajib terdaftar pada mata pelajaran ini menggunakan Key resmi dari Guru.</p>
                                    <a href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" class="btn btn-warning text-dark w-100 fw-bold rounded-pill shadow-xs py-2">
                                        <i class="bi bi-key-fill me-1"></i> Input Key Mapel untuk Buka Tugas
                                    </a>
                                </div>
                            <?php else: ?>
                                <?php if ($isSubmitted): ?>
                                    <!-- SUBMITTED ANSWER STATUS BOX -->
                                    <div class="task-action-box bg-success-subtle border-success-subtle p-3 rounded-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                            <span class="fw-bold text-success small d-flex align-items-center gap-1">
                                                <i class="bi bi-check-circle-fill fs-6"></i> Jawaban Tugas Telah Berhasil Dikirim
                                            </span>
                                            <small class="text-muted fw-semibold" style="font-size:0.75rem;">
                                                <?= date('d M Y, H:i', strtotime($subData['created_at'])) ?> WIB
                                            </small>
                                        </div>

                                        <?php if (!empty($subData['file_path'])): 
                                            $subFilePath = BASE_URL . 'assets/uploads/tugas/' . htmlspecialchars($subData['file_path']);
                                        ?>
                                            <div class="p-2.5 bg-white rounded-3 border d-flex align-items-center justify-content-between mb-2">
                                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                    <i class="bi bi-file-earmark-check text-success fs-5"></i>
                                                    <span class="small fw-semibold text-dark text-truncate" style="max-width: 220px;"><?= htmlspecialchars($subData['file_path']) ?></span>
                                                </div>
                                                <a href="<?= $subFilePath ?>" download class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">
                                                    <i class="bi bi-download me-1"></i> Berkas Saya
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($subData['nilai'] !== null): ?>
                                            <div class="p-2.5 bg-white rounded-3 border border-success text-success fw-bold d-flex align-items-center justify-content-between mb-1">
                                                <span><i class="bi bi-star-fill text-warning me-1"></i>Nilai dari Guru:</span>
                                                <span class="fs-5 text-success fw-extrabold"><?= number_format($subData['nilai'], 1) ?></span>
                                            </div>
                                            <?php if (!empty($subData['catatan_guru'])): ?>
                                                <small class="text-muted d-block mt-1"><strong>Catatan Guru:</strong> <?= htmlspecialchars($subData['catatan_guru']) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="p-2 bg-white rounded-3 border text-center small text-muted font-monospace">
                                                <i class="bi bi-hourglass-split me-1 text-warning"></i>Status Penilaian: Belum Dinilai oleh Guru
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($canAccess): ?>
                                            <div class="mt-2.5 pt-2 border-top">
                                                <button type="button" class="btn btn-sm btn-outline-primary w-100 rounded-pill fw-bold" data-bs-toggle="collapse" data-bs-target="#reuploadForm<?= $t['id'] ?>">
                                                    <i class="bi bi-arrow-repeat me-1"></i> Update / Kirim Ulang Jawaban
                                                </button>

                                                <div class="collapse mt-2.5" id="reuploadForm<?= $t['id'] ?>">
                                                    <form action="<?= BASE_URL ?>index.php?url=siswa/tugas" method="POST" enctype="multipart/form-data" class="p-2.5 bg-white rounded-3 border">
                                                        <?= Security::csrfField() ?>
                                                        <input type="hidden" name="tugas_id" value="<?= $t['id'] ?>">
                                                        <div class="mb-2">
                                                            <input type="file" name="file" class="form-control rounded-3" required style="font-size:0.85rem;">
                                                        </div>
                                                        <div class="mb-2">
                                                            <input type="text" name="catatan_siswa" class="form-control rounded-3" placeholder="Catatan perbaikan (Opsional)" style="font-size:0.85rem;">
                                                        </div>
                                                        <button type="submit" class="btn btn-sm btn-primary w-100 rounded-pill fw-bold">
                                                            <i class="bi bi-send-fill me-1"></i> Kirim Pembaharuan
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($canAccess): ?>
                                    <!-- NEW SUBMISSION FORM BOX -->
                                    <div class="task-action-box">
                                        <?php if ($statusAccess === 'disetujui_susulan'): ?>
                                            <div class="p-2.5 bg-success-subtle text-success border border-success-subtle rounded-3 mb-3 small text-center fw-bold">
                                                <i class="bi bi-check-circle-fill me-1"></i> Izin Susulan Disetujui Guru (Pengumpulan Dibuka)
                                            </div>
                                        <?php endif; ?>

                                        <h6 class="fw-bold small text-primary mb-2.5 d-flex align-items-center gap-1.5">
                                            <i class="bi bi-cloud-arrow-up-fill fs-6"></i> Upload Lembar Jawaban Tugas
                                        </h6>
                                        
                                        <form action="<?= BASE_URL ?>index.php?url=siswa/tugas" method="POST" enctype="multipart/form-data">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="tugas_id" value="<?= $t['id'] ?>">

                                            <div class="mb-2.5">
                                                <input type="file" name="file" class="form-control rounded-3" required style="font-size:0.88rem;">
                                                <small class="text-muted" style="font-size:0.73rem;">Format: PDF, DOCX, PPTX, ZIP, PNG (Maks 25MB)</small>
                                            </div>
                                            <div class="mb-3">
                                                <input type="text" name="catatan_siswa" class="form-control rounded-3" placeholder="Catatan tambahan untuk Guru (Opsional)" style="font-size:0.88rem;">
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill py-2.5 shadow-sm text-white hover-scale" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                                                <i class="bi bi-send-fill me-1.5"></i> Kirim Jawaban Tugas
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <!-- EXPIRED STATE -->
                                    <div class="task-action-box text-center border-danger-subtle" style="background-color: #fff5f5;">
                                        <div class="fw-bold text-danger mb-1 small"><i class="bi bi-clock-history me-1"></i>Batas Waktu Pengumpulan Berakhir</div>
                                        <p class="text-muted small mb-3" style="font-size:0.8rem;">Tugas telah melewati deadline. Silakan minta izin susulan ke Guru untuk mengumpulkan jawaban.</p>
                                        
                                        <?php
                                        $db = Database::getConnection();
                                        $stmtSus = $db->prepare("SELECT status FROM tugas_susulan WHERE tugas_id = ? AND siswa_id = ?");
                                        $stmtSus->execute([$t['id'], $siswaId]);
                                        $susStatus = $stmtSus->fetchColumn();
                                        ?>
                                        <?php if ($susStatus === 'pending'): ?>
                                            <button class="btn btn-warning text-dark w-100 fw-bold rounded-pill shadow-xs py-2 small" disabled>
                                                <i class="bi bi-hourglass-split me-1"></i> Permohonan Susulan Dikirim (Menunggu Guru)
                                            </button>
                                        <?php elseif ($susStatus === 'ditolak'): ?>
                                            <div class="badge bg-danger-subtle text-danger border border-danger-subtle w-100 py-2 rounded-pill fw-bold small">
                                                <i class="bi bi-x-circle-fill me-1"></i> Permohonan Susulan Ditolak Guru
                                            </div>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-danger w-100 fw-bold rounded-pill shadow-xs py-2 small" data-bs-toggle="modal" data-bs-target="#modalSusulanTugas<?= $t['id'] ?>">
                                                <i class="bi bi-envelope-paper-fill me-1"></i> Ajukan Permohonan Susulan
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <!-- Modal Susulan Tugas -->
                <div class="modal fade" id="modalSusulanTugas<?= $t['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                            <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                                <h6 class="modal-title fw-bold text-white mb-0"><i class="bi bi-envelope-paper-fill text-warning me-2"></i>Permohonan Izin Susulan Tugas</h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="<?= BASE_URL ?>index.php?url=siswa/tugas" method="POST">
                                <div class="modal-body p-4 bg-light">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="action" value="request_susulan">
                                    <input type="hidden" name="tugas_id" value="<?= $t['id'] ?>">

                                    <div class="p-3 bg-white rounded-3 border mb-3 shadow-xs">
                                        <small class="text-muted d-block mb-1">Tugas Target:</small>
                                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($t['judul']) ?></h6>
                                        <small class="text-primary fw-bold"><?= htmlspecialchars($t['nama_mapel']) ?></small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Alasan Terlambat / Permohonan Izin</label>
                                        <textarea name="catatan_susulan" class="form-control rounded-3" rows="3" placeholder="Contoh: Mohon maaf Pak/Bu Guru, saya terlambat mengumpulkan karena ada kendala jaringan..." required></textarea>
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
                    <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                        <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="bg-primary rounded-3 p-2 text-white shadow-xs">
                                    <i class="bi bi-file-earmark-text-fill fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="modal-title fw-bold text-white mb-0">Lampiran Soal Guru: <?= htmlspecialchars($t['judul']) ?></h6>
                                    <small class="text-info fw-medium" style="font-size:0.75rem;">Mapel: <?= htmlspecialchars($t['nama_mapel']) ?> &bull; Guru: <?= htmlspecialchars($t['nama_guru']) ?></small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4 bg-light">
                            <div class="p-3 bg-white rounded-3 border mb-3 shadow-xs">
                                <h6 class="fw-bold text-dark mb-1"><i class="bi bi-info-circle text-primary me-1"></i>Instruksi Penugasan:</h6>
                                <p class="text-slate-700 small mb-0 lh-lg"><?= nl2br(htmlspecialchars($t['deskripsi'])) ?></p>
                            </div>

                            <div class="border rounded-4 bg-white p-2 shadow-sm overflow-hidden text-center">
                                <?php if ($isPdf): ?>
                                    <iframe src="<?= $tFilePath ?>#toolbar=0" style="width:100%; height:580px; border:none;" class="rounded-3"></iframe>
                                <?php elseif ($isImg): ?>
                                    <img src="<?= $tFilePath ?>" alt="Preview Lampiran Soal" class="img-fluid rounded-3 mx-auto d-block shadow-sm" style="max-height:550px; object-fit:contain;">
                                <?php else: ?>
                                    <iframe src="https://docs.google.com/gview?url=<?= urlencode($tFilePath) ?>&embedded=true" style="width:100%; height:580px; border:none;" class="rounded-3"></iframe>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 p-4 justify-content-between bg-white border-top">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                            <a href="<?= $tFilePath ?>" download class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">
                                <i class="bi bi-download me-1.5"></i> Unduh Lampiran Soal
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
function filterTaskItems() {
    const searchVal = document.getElementById('searchInput').value.toLowerCase().trim();
    const mapelVal = document.getElementById('filterMapel').value;
    const statusVal = document.getElementById('filterStatus').value;

    const items = document.querySelectorAll('.task-item-col');
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
