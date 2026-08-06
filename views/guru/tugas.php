<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* Modern Tugas Guru Portal Styling */
.tugas-guru-page-wrapper {
    padding-top: 28px !important;
}

/* Glassmorphic Hero Banner */
.tugas-guru-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0d9488 100%);
    border-radius: 20px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
    position: relative;
    overflow: hidden;
}

.tugas-guru-hero::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(45, 212, 191, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}

/* Stat Cards */
.kpi-tugas-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    transition: all 0.25s ease;
}
.kpi-tugas-card:hover {
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
.tugas-nav-tabs .nav-link {
    border: none;
    color: #64748b;
    font-weight: 600;
    padding: 12px 20px;
    border-radius: 12px;
    transition: all 0.2s ease;
    font-size: 0.92rem;
}
.tugas-nav-tabs .nav-link:hover {
    color: #0d9488;
    background-color: rgba(13, 148, 136, 0.06);
}
.tugas-nav-tabs .nav-link.active {
    color: #ffffff !important;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important;
    box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
}

/* Table Card Styling */
.table-card-custom {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    border-top: 4px solid #0d9488;
}

/* Badge Tags */
.badge-mapel-tag {
    background: #ccfbf1;
    color: #0f766e;
    border: 1px solid #99f6e4;
    font-weight: 700;
    font-size: 0.76rem;
    padding: 5px 12px;
    border-radius: 50rem;
}

/* Responsive Table Overrides */
@media (max-width: 767.98px) {
    .tugas-guru-hero {
        padding: 20px !important;
    }
    .tugas-nav-tabs .nav-link {
        padding: 8px 14px;
        font-size: 0.82rem;
    }
}
</style>

<?php 
$isAdminMonitoring = (strtolower(AuthHelper::user()['role_name'] ?? '') === 'administrator');
?>

<main class="main-content px-3 px-md-4 tugas-guru-page-wrapper pt-4 mt-4 mt-md-5">
    <div class="container-fluid">
        <?php if ($isAdminMonitoring): ?>
            <div class="alert alert-info border-0 rounded-4 p-3 mb-4 shadow-sm d-flex align-items-center gap-3" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-left: 5px solid #0284c7 !important;">
                <div class="bg-primary text-white p-2.5 rounded-3 shadow-xs">
                    <i class="bi bi-shield-lock-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-eye-fill me-1 text-primary"></i>Mode Monitoring Administrator (Pengawasan Guru)</h6>
                    <small class="text-secondary fw-medium">Secara hak akses, Administrator hanya berwenang **mengawasi / memantau (Monitoring Only)** data penugasan & evaluasi siswa. Admin dapat melihat detail petunjuk tugas dan pengumpulan siswa tanpa membuat, mengedit, atau menghapus tugas milik Guru.</small>
                </div>
            </div>
        <?php endif; ?>

        <!-- 🚀 HERO BANNER GURU -->
        <div class="tugas-guru-hero p-4 p-md-5 mb-4">
            <div class="row align-items-center relative-zIndex-1">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="d-inline-flex align-items-center gap-2 px-3.5 py-2 rounded-pill bg-warning text-dark shadow-sm small fw-bold mb-3">
                        <i class="bi bi-card-checklist text-dark fs-6"></i>
                        <span>Control Center Penugasan & Evaluation</span>
                    </div>
                    <h2 class="fw-bold mb-2 text-white" style="letter-spacing: -0.5px;">Kelola Penugasan & Rubrik Evaluation</h2>
                    <p class="text-white text-opacity-85 small mb-0 lh-lg" style="max-width: 650px;">
                        Buat tugas modul baru, unggah berkas petunjuk pengerjaan, atur deadline penutupan, periksa berkas kiriman siswa, serta berikan skor nilai portofolio secara terstruktur.
                    </p>
                </div>
                <?php if (!$isAdminMonitoring): ?>
                    <div class="col-lg-4 text-lg-end">
                        <button class="btn btn-warning text-dark px-4 py-2.5 rounded-pill fw-bold shadow-lg d-inline-flex align-items-center gap-2 hover-scale" data-bs-toggle="modal" data-bs-target="#modalAddTugas">
                            <i class="bi bi-plus-circle-fill fs-5"></i>
                            <span>Buat Tugas Baru</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 📊 KPI SUMMARY STATS CARDS -->
        <?php 
        $learningModel = new LearningModel();
        $totalTugasCount = count($tugasList ?? []);
        $totalSusulanCount = count($tugasSusulanRequests ?? []);
        
        $totalSubmissions = 0;
        $ungradedSubmissions = 0;

        if (!empty($tugasList)) {
            foreach ($tugasList as $tItem) {
                $subs = $learningModel->getPengumpulanByTugas($tItem['id']);
                $totalSubmissions += count($subs);
                foreach ($subs as $sItem) {
                    if ($sItem['nilai'] === null) {
                        $ungradedSubmissions++;
                    }
                }
            }
        }
        ?>
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-tugas-card p-3.5 d-flex align-items-center gap-3">
                    <div class="kpi-icon-box bg-teal bg-opacity-10 text-teal-emphasis" style="background: rgba(13, 148, 136, 0.1); color: #0d9488;">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Penugasan</div>
                        <h4 class="fw-bold mb-0 text-dark"><?= $totalTugasCount ?> <span class="fs-6 fw-normal text-muted">Modul</span></h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-tugas-card p-3.5 d-flex align-items-center gap-3">
                    <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Pengumpulan</div>
                        <h4 class="fw-bold mb-0 text-dark"><?= $totalSubmissions ?> <span class="fs-6 fw-normal text-muted">Berkas</span></h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-tugas-card p-3.5 d-flex align-items-center gap-3">
                    <div class="kpi-icon-box bg-warning bg-opacity-15 text-warning-emphasis">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Belum Dinilai</div>
                        <h4 class="fw-bold mb-0 text-dark"><?= $ungradedSubmissions ?> <span class="fs-6 fw-normal text-muted">Tugas</span></h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-tugas-card p-3.5 d-flex align-items-center gap-3">
                    <div class="kpi-icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-envelope-paper-heart-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Izin Susulan Tugas</div>
                        <h4 class="fw-bold mb-0 text-dark"><?= $totalSusulanCount ?> <span class="fs-6 fw-normal text-muted">Pengajuan</span></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 📑 TABBED NAVIGATION -->
        <ul class="nav nav-pills tugas-nav-tabs gap-2 mb-4 p-1.5 bg-white rounded-4 border shadow-xs" id="tugasGuruTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active d-flex align-items-center gap-2" id="tab-daftar-tab" data-bs-toggle="tab" data-bs-target="#tab-daftar" type="button" role="tab">
                    <i class="bi bi-card-checklist"></i> Daftar Penugasan & Rubrik Evaluasi
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center gap-2" id="tab-susulan-tugas-tab" data-bs-toggle="tab" data-bs-target="#tab-susulan-tugas" type="button" role="tab">
                    <i class="bi bi-envelope-paper-heart-fill"></i> Izin Susulan Tugas
                    <?php if ($totalSusulanCount > 0): ?>
                        <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5" style="font-size:0.7rem;"><?= $totalSusulanCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <!-- TAB CONTENT -->
        <div class="tab-content" id="tugasGuruTabContent">
            
            <!-- TAB 1: DAFTAR PENUGASAN & RUBRIK EVALUASI -->
            <div class="tab-pane fade show active" id="tab-daftar" role="tabpanel">
                <div class="table-card-custom p-4">
                    
                    <!-- Search & Filter Controls -->
                    <div class="row g-3 mb-4 align-items-center">
                        <div class="col-12 col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" id="searchTugasInput" class="form-control border-start-0 ps-0" placeholder="Cari judul tugas..." onkeyup="filterGuruTugasTable()">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <select id="filterTugasMapel" class="form-select" onchange="filterGuruTugasTable()">
                                <option value="">Semua Mata Pelajaran</option>
                                <?php foreach ($mapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if (!$isAdminMonitoring): ?>
                            <div class="col-12 col-sm-6 col-md-4 text-sm-end">
                                <button class="btn btn-teal text-white px-3 py-2 rounded-pill fw-bold shadow-sm small" style="background:#0d9488;" data-bs-toggle="modal" data-bs-target="#modalAddTugas">
                                    <i class="bi bi-plus-circle me-1"></i> Buat Tugas Baru
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Judul Tugas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Kelas Target</th>
                                    <th>Batas Deadline</th>
                                    <th class="text-center" style="min-width: 310px;">Aksi / Penilaian Siswa</th>
                                </tr>
                            </thead>
                            <tbody id="guruTugasTableBody">
                                <?php if (empty($tugasList)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-card-checklist fs-1 text-slate-300 d-block mb-2"></i>
                                            Belum ada tugas pembelajaran yang dibuat.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($tugasList as $i => $t): 
                                        $subs = $learningModel->getPengumpulanByTugas($t['id']);
                                        $subCount = count($subs);
                                    ?>
                                        <tr class="guru-tugas-row" data-title="<?= htmlspecialchars($t['judul']) ?>" data-mapel="<?= $t['mapel_id'] ?>">
                                            <td class="fw-bold text-muted"><?= $i + 1 ?></td>
                                            <td>
                                                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($t['judul']) ?></div>
                                                <small class="text-muted d-block"><?= count($subs) ?> Siswa Mengumpulkan</small>
                                            </td>
                                            <td><span class="badge-mapel-tag"><i class="bi bi-journal-bookmark me-1"></i><?= htmlspecialchars($t['nama_mapel']) ?></span></td>
                                            <td><span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1.5 fw-bold"><?= htmlspecialchars($t['nama_kelas']) ?></span></td>
                                            <td>
                                                <?php if (!empty($t['deadline'])): 
                                                    $isExp = (date('Y-m-d H:i:s') > $t['deadline']);
                                                ?>
                                                    <span class="badge bg-<?= $isExp ? 'danger' : 'warning text-dark' ?> rounded-pill px-3 py-1.5">
                                                        <i class="bi bi-clock-history me-1"></i><?= date('d M Y, H:i', strtotime($t['deadline'])) ?> WIB
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border rounded-pill px-3 py-1.5">Tanpa Deadline</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                                    <button class="btn btn-sm btn-info text-white px-2.5 rounded-pill shadow-xs" style="font-size:0.76rem;" data-bs-toggle="modal" data-bs-target="#modalPreviewTugas<?= $t['id'] ?>" title="Detail / Petunjuk Tugas">
                                                        <i class="bi bi-eye me-1"></i> Detail
                                                    </button>

                                                    <?php if (!$isAdminMonitoring): ?>
                                                        <button class="btn btn-sm btn-warning text-dark px-2.5 rounded-pill shadow-xs" style="font-size:0.76rem;" data-bs-toggle="modal" data-bs-target="#modalEditTugas<?= $t['id'] ?>" title="Edit Tugas">
                                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                                        </button>
                                                    <?php endif; ?>

                                                    <button class="btn btn-sm btn-primary px-2.5 rounded-pill shadow-xs" style="font-size:0.76rem;" data-bs-toggle="modal" data-bs-target="#modalGrade<?= $t['id'] ?>" title="Lihat Pengumpulan & Nilai">
                                                        <i class="bi bi-award me-1"></i> <?= $isAdminMonitoring ? 'Lihat Pengumpulan' : 'Nilai Siswa' ?> (<?= $subCount ?>)
                                                    </button>

                                                    <?php if (!$isAdminMonitoring): ?>
                                                        <form action="<?= BASE_URL ?>index.php?url=guru/tugas" method="POST" onsubmit="return confirm('Hapus tugas ini beserta seluruh hasil pengumpulan siswa?');" class="d-inline">
                                                            <?= Security::csrfField() ?>
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger px-2.5 rounded-pill shadow-xs" style="font-size:0.76rem;" title="Hapus Tugas">
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

            <!-- TAB 2: IZIN SUSULAN TUGAS SISWA -->
            <div class="tab-pane fade" id="tab-susulan-tugas" role="tabpanel">
                <div class="table-card-custom p-4 border-top border-4 border-danger">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-envelope-paper-heart-fill text-danger me-2"></i>Permintaan Izin Susulan Pengumpulan Tugas Siswa</h5>
                        <span class="badge bg-danger px-3 py-1.5 rounded-pill fw-bold">Total: <?= count($tugasSusulanRequests ?? []) ?> Pengajuan</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th>Kelas Target</th>
                                    <th>Judul Tugas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Catatan Pengajuan</th>
                                    <th>Status Izin</th>
                                    <th class="text-center">Aksi Persetujuan Guru</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tugasSusulanRequests)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-check-all fs-1 text-slate-300 d-block mb-2"></i>
                                            Belum ada pengajuan izin susulan tugas dari siswa.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($tugasSusulanRequests as $req): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($req['nama_siswa']) ?></td>
                                            <td><span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1.5 fw-bold"><?= htmlspecialchars($req['nama_kelas']) ?></span></td>
                                            <td><?= htmlspecialchars($req['judul_tugas']) ?></td>
                                            <td><span class="badge-mapel-tag"><?= htmlspecialchars($req['nama_mapel']) ?></span></td>
                                            <td class="small text-muted"><?= htmlspecialchars($req['catatan'] ?? 'Pengajuan Susulan Tugas') ?></td>
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
                                                        <form action="<?= BASE_URL ?>index.php?url=guru/tugas" method="POST" class="d-inline">
                                                            <?= Security::csrfField() ?>
                                                            <input type="hidden" name="action" value="approve_tugas_susulan">
                                                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-success px-3 rounded-pill fw-bold" style="font-size:0.75rem;">
                                                                <i class="bi bi-check-circle me-1"></i> Izinkan
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($req['status'] !== 'ditolak'): ?>
                                                        <form action="<?= BASE_URL ?>index.php?url=guru/tugas" method="POST" class="d-inline">
                                                            <?= Security::csrfField() ?>
                                                            <input type="hidden" name="action" value="reject_tugas_susulan">
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

<!-- Modal Add Tugas -->
<div class="modal fade" id="modalAddTugas" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="bg-teal rounded-3 p-2 text-white shadow-xs" style="background:#0d9488;">
                        <i class="bi bi-plus-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Buat Tugas Pembelajaran Baru</h5>
                        <small class="text-info fw-medium" style="font-size:0.75rem;">Tambahkan tugas modul & petunjuk pengerjaan bagi siswa</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=guru/tugas" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="create">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Judul Tugas <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" placeholder="Contoh: Tugas 1 Modul Auth MVC" required>
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
                            <label class="form-label small fw-bold">Batas Akhir (Deadline) <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="deadline" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Deskripsi / Petunjuk Pengerjaan & Rubrik <span class="text-danger">*</span></label>
                            <textarea name="deskripsi" class="form-control" rows="4" placeholder="Tuliskan petunjuk pengerjaan dan kriteria rubrik penilaian bagi siswa..." required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold"><i class="bi bi-paperclip text-primary me-1"></i>Lampiran File Soal / Modul <span class="text-muted fw-normal">(Opsional)</span></label>
                            <input type="file" name="file" class="form-control">
                            <small class="text-muted d-block" style="font-size:0.72rem;">Unggah modul tugas (PDF/DOCX/ZIP/PNG) jika ada.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 p-4 justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal text-white px-4 rounded-pill fw-bold shadow-sm" style="background:#0d9488;">
                        <i class="bi bi-save-fill me-1"></i> Simpan Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals Preview, Edit & Grade for each Task -->
<?php foreach ($tugasList as $t): 
    $submissions = $learningModel->getPengumpulanByTugas($t['id']);
?>
    <!-- Modal Detail / Preview Tugas -->
    <div class="modal fade" id="modalPreviewTugas<?= $t['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="bg-info rounded-3 p-2 text-white shadow-xs">
                            <i class="bi bi-eye-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-white mb-0">Detail Penugasan: <?= htmlspecialchars($t['judul']) ?></h6>
                            <small class="text-info fw-medium" style="font-size:0.75rem;">Mapel: <?= htmlspecialchars($t['nama_mapel']) ?> &bull; Kelas: <?= htmlspecialchars($t['nama_kelas']) ?></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block">Batas Akhir (Deadline):</span>
                                <strong class="text-warning-emphasis"><i class="bi bi-clock-history me-1"></i><?= date('d F Y, H:i', strtotime($t['deadline'])) ?> WIB</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block">Status Pengumpulan Siswa:</span>
                                <strong class="text-success"><i class="bi bi-people me-1"></i><?= count($submissions) ?> Siswa Mengumpulkan</strong>
                            </div>
                        </div>
                    </div>

                    <div class="p-3.5 bg-light rounded-3 border mb-3">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-file-earmark-text text-primary me-1"></i>Petunjuk Pengerjaan & Rubrik Penilaian:</h6>
                        <div class="text-slate-800 small lh-lg" style="white-space: pre-line; color:#334155;"><?= htmlspecialchars($t['deskripsi']) ?></div>
                    </div>

                    <?php if (!empty($t['file_path'])): ?>
                        <div class="p-3 border rounded-3 d-flex justify-content-between align-items-center bg-white shadow-sm">
                            <div class="d-flex align-items-center gap-2.5">
                                <i class="bi bi-file-earmark-pdf fs-2 text-danger"></i>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Lampiran Berkas Soal / Modul</h6>
                                    <small class="text-muted"><?= htmlspecialchars($t['file_path']) ?></small>
                                </div>
                            </div>
                            <a href="<?= BASE_URL ?>assets/uploads/tugas/<?= htmlspecialchars($t['file_path']) ?>" class="btn btn-sm btn-outline-primary fw-bold rounded-pill" download>
                                <i class="bi bi-download me-1"></i> Unduh Lampiran
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-0 pt-0 p-4 justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalGrade<?= $t['id'] ?>">
                        <i class="bi bi-award me-1"></i> Buka Nilai Siswa (<?= count($submissions) ?>)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Tugas -->
    <div class="modal fade" id="modalEditTugas<?= $t['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="bg-warning rounded-3 p-2 text-dark shadow-xs">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-white mb-0">Edit Data Penugasan</h6>
                            <small class="text-info fw-medium" style="font-size:0.75rem;">Perbarui judul, kelas target, deadline, dan petunjuk tugas</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=guru/tugas" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Judul Tugas <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($t['judul']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                                <select name="mapel_id" class="form-select" required>
                                    <?php foreach ($mapelList as $mp): ?>
                                        <option value="<?= $mp['id'] ?>" <?= $t['mapel_id'] == $mp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Kelas Target <span class="text-danger">*</span></label>
                                <select name="kelas_id" class="form-select" required>
                                    <?php foreach ($kelasList as $k): ?>
                                        <option value="<?= $k['id'] ?>" <?= $t['kelas_id'] == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Batas Akhir (Deadline) <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="deadline" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($t['deadline'])) ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Deskripsi / Petunjuk Pengerjaan <span class="text-danger">*</span></label>
                                <textarea name="deskripsi" class="form-control" rows="4" required><?= htmlspecialchars($t['deskripsi']) ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Ganti File Soal / Lampiran (Opsional)</label>
                                <input type="file" name="file" class="form-control">
                                <?php if (!empty($t['file_path'])): ?>
                                    <small class="text-muted d-block mt-1">Berkas saat ini: <?= htmlspecialchars($t['file_path']) ?></small>
                                <?php endif; ?>
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

    <!-- Modal Grade Submissions -->
    <div class="modal fade" id="modalGrade<?= $t['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="bg-primary rounded-3 p-2 text-white shadow-xs">
                            <i class="bi bi-award-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-white mb-0">Pengumpulan & Grading Siswa: <?= htmlspecialchars($t['judul']) ?></h6>
                            <small class="text-info fw-medium" style="font-size:0.75rem;">Periksa hasil berkas jawaban dan berikan nilai portofolio siswa</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <?php if (empty($submissions)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-300"></i>
                            Belum ada siswa yang mengumpulkan tugas ini.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle table-hover">
                                <thead class="table-light">
                                    <tr><th>Nama Siswa</th><th>Waktu Kirim</th><th>Berkas Jawaban</th><th>Skor Nilai</th><th>Input Form Grading Guru</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submissions as $sub): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark"><i class="bi bi-person-circle text-primary me-1"></i><?= htmlspecialchars($sub['nama_lengkap']) ?></div>
                                                <small class="text-muted">NIS: <?= htmlspecialchars($sub['nis']) ?></small>
                                            </td>
                                            <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($sub['submitted_at'])) ?> WIB</small></td>
                                            <td>
                                                <?php if ($sub['file_path']): ?>
                                                    <a href="<?= BASE_URL ?>assets/uploads/tugas/<?= htmlspecialchars($sub['file_path']) ?>" download class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" style="font-size:0.75rem;">
                                                        <i class="bi bi-download me-1"></i> Unduh
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border">Tanpa File</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= ($sub['nilai'] !== null && $sub['nilai'] >= 75) ? 'success' : ($sub['nilai'] !== null ? 'danger' : 'warning text-dark') ?> fs-6 rounded-pill px-3 py-1.5">
                                                    <?= $sub['nilai'] !== null ? $sub['nilai'] : 'Belum' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form action="<?= BASE_URL ?>index.php?url=guru/tugas" method="POST" class="d-flex gap-1.5 align-items-center">
                                                    <?= Security::csrfField() ?>
                                                    <input type="hidden" name="action" value="grade">
                                                    <input type="hidden" name="pengumpulan_id" value="<?= $sub['id'] ?>">
                                                    <input type="number" name="nilai" class="form-control form-control-sm fw-bold" style="width:75px;" value="<?= $sub['nilai'] ?>" placeholder="0-100" min="0" max="100" required>
                                                    <input type="text" name="komentar" class="form-control form-control-sm" value="<?= htmlspecialchars($sub['komentar_guru'] ?? '') ?>" placeholder="Catatan">
                                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-2.5">Simpan</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-0 pt-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
function filterGuruTugasTable() {
    const searchVal = document.getElementById('searchTugasInput').value.toLowerCase();
    const filterMapel = document.getElementById('filterTugasMapel').value;

    const rows = document.querySelectorAll('.guru-tugas-row');
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
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
