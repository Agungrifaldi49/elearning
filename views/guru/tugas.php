<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* Modern Tugas Guru Portal Styling */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.tugas-guru-page-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
    background-color: #f8fafc;
    min-height: 100vh;
}

/* Glassmorphic Hero Banner */
.tugas-guru-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0d9488 100%);
    border-radius: 24px;
    color: #ffffff;
    box-shadow: 0 15px 35px -10px rgba(15, 23, 42, 0.2);
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

.hover-scale {
    transition: transform 0.2s ease;
}
.hover-scale:hover {
    transform: scale(1.02);
}

.text-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php 
$isAdminMonitoring = (strtolower(AuthHelper::user()['role_name'] ?? '') === 'administrator');
?>

<main class="main-content px-3 px-md-4 tugas-guru-page-wrapper pb-5">
    <div class="container-fluid max-width-1400 pt-2">
        <?php if ($isAdminMonitoring): ?>
            <div class="alert alert-info border-0 rounded-4 p-3 mb-4 shadow-sm d-flex align-items-center gap-3" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-left: 5px solid #0284c7 !important;">
                <div class="bg-primary text-white p-2.5 rounded-3 shadow-xs">
                    <i class="bi bi-shield-lock-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-eye-fill me-1 text-primary"></i>Mode Monitoring Administrator (Pengawasan Guru)</h6>
                    <small class="text-secondary fw-medium">Secara hak akses, Administrator berwenang **mengawasi (Monitoring Only)** data penugasan & evaluasi siswa. Admin dapat melihat detail petunjuk tugas dan pengumpulan siswa tanpa membuat, mengedit, atau menghapus tugas milik Guru.</small>
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
                    <h2 class="fw-bold mb-2 text-white" style="letter-spacing: -0.5px;">Kelola Penugasan & Rubrik Evaluasi</h2>
                    <p class="text-white text-opacity-85 small mb-0 lh-lg" style="max-width: 650px;">
                        Buat tugas modul baru, unggah berkas petunjuk pengerjaan, periksa berkas jawaban siswa secara langsung (*Live Preview*), serta berikan skor nilai rubrik portofolio secara terstruktur.
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
                                <button class="btn btn-teal text-white px-3 py-2 rounded-pill fw-bold shadow-sm small hover-scale" style="background:#0d9488;" data-bs-toggle="modal" data-bs-target="#modalAddTugas">
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
                                    <?php 
                                    $kelasListMap = [];
                                    foreach ($kelasList as $k) {
                                        $kelasListMap[$k['id']] = $k['nama_kelas'];
                                    }
                                    foreach ($tugasList as $i => $t): 
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
                                            <td>
                                                <?php 
                                                $targetIds = !empty($t['kelas_ids']) ? array_map('intval', explode(',', $t['kelas_ids'])) : [(int)$t['kelas_id']];
                                                foreach ($targetIds as $tid):
                                                    $kNama = $kelasListMap[$tid] ?? $t['nama_kelas'];
                                                ?>
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 fw-bold me-1 mb-1"><?= htmlspecialchars($kNama) ?></span>
                                                <?php endforeach; ?>
                                            </td>
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
                                                 <div class="d-inline-flex gap-1.5 align-items-center justify-content-center">
                                                     <button class="btn btn-sm btn-primary px-3 rounded-pill shadow-xs fw-bold hover-scale" style="font-size:0.78rem; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);" data-bs-toggle="modal" data-bs-target="#modalGrade<?= $t['id'] ?>" title="Lihat Pengumpulan & Nilai Siswa">
                                                         <i class="bi bi-award-fill me-1"></i> <?= $isAdminMonitoring ? 'Pengumpulan' : 'Nilai Siswa' ?> (<?= $subCount ?>)
                                                     </button>

                                                     <button class="btn btn-sm btn-info text-white px-2.5 rounded-pill shadow-xs fw-bold hover-scale" style="font-size:0.78rem;" data-bs-toggle="modal" data-bs-target="#modalPreviewTugas<?= $t['id'] ?>" title="Detail / Petunjuk Tugas">
                                                         <i class="bi bi-eye-fill me-1"></i> Detail
                                                     </button>

                                                     <?php if (!$isAdminMonitoring): ?>
                                                         <div class="dropdown d-inline">
                                                             <button class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 shadow-xs fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:0.78rem;">
                                                                 <i class="bi bi-gear-fill me-1 text-primary"></i> Kelola ▾
                                                             </button>
                                                             <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 p-2" style="min-width: 200px; font-size:0.85rem;">
                                                                 <li><h6 class="dropdown-header text-uppercase fw-bold text-muted px-2 py-1 mb-1" style="font-size:0.68rem;">Aksi & Evaluasi</h6></li>
                                                                 <li>
                                                                     <a class="dropdown-item rounded-2 py-1.5 text-primary fw-semibold" href="#" data-bs-toggle="modal" data-bs-target="#modalGrade<?= $t['id'] ?>">
                                                                         <i class="bi bi-award-fill me-2 text-primary"></i> Periksa & Nilai Siswa
                                                                     </a>
                                                                 </li>
                                                                 <li>
                                                                     <a class="dropdown-item rounded-2 py-1.5 text-dark fw-semibold" href="#" data-bs-toggle="modal" data-bs-target="#modalPreviewTugas<?= $t['id'] ?>">
                                                                         <i class="bi bi-eye-fill text-info me-2"></i> Lihat Petunjuk Tugas
                                                                     </a>
                                                                 </li>
                                                                 <li><hr class="dropdown-divider my-1"></li>
                                                                 <li><h6 class="dropdown-header text-uppercase fw-bold text-muted px-2 py-1 mb-1" style="font-size:0.68rem;">Pengaturan Tugas</h6></li>
                                                                 <li>
                                                                     <a class="dropdown-item rounded-2 py-1.5 text-dark fw-semibold" href="#" data-bs-toggle="modal" data-bs-target="#modalEditTugas<?= $t['id'] ?>">
                                                                         <i class="bi bi-pencil-square text-warning me-2"></i> Edit Tugas
                                                                     </a>
                                                                 </li>
                                                                 <li>
                                                                     <form action="<?= BASE_URL ?>index.php?url=guru/tugas" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini beserta seluruh hasil pengumpulan siswa?');">
                                                                         <?= Security::csrfField() ?>
                                                                         <input type="hidden" name="action" value="delete">
                                                                         <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                                                         <button type="submit" class="dropdown-item rounded-2 py-1.5 text-danger fw-semibold w-100 text-start">
                                                                             <i class="bi bi-trash-fill me-2"></i> Hapus Tugas
                                                                         </button>
                                                                     </form>
                                                                 </li>
                                                             </ul>
                                                         </div>
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
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Kelas Target <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none small fw-bold text-teal" id="selectAllKelasTugasAdd">
                                    <i class="bi bi-check-all me-1"></i> Pilih Semua Kelas
                                </button>
                            </div>
                            <div class="p-3 bg-light rounded-3 border" style="max-height: 150px; overflow-y: auto;">
                                <div class="row g-2">
                                    <?php foreach ($kelasList as $k): ?>
                                        <div class="col-6 col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input kelas-checkbox-add-tugas" type="checkbox" name="kelas_ids[]" value="<?= $k['id'] ?>" id="add_tugas_k_<?= $k['id'] ?>">
                                                <label class="form-check-label small fw-medium text-dark cursor-pointer" for="add_tugas_k_<?= $k['id'] ?>">
                                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size:0.72rem;">Bisa memilih lebih dari 1 kelas sekaligus.</small>
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
                    <button type="submit" class="btn btn-teal text-white px-4 rounded-pill fw-bold shadow-sm hover-scale" style="background:#0d9488;">
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
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold"
                                    onclick="previewSubmissionFile('<?= BASE_URL ?>assets/uploads/tugas/<?= htmlspecialchars($t['file_path']) ?>', '<?= htmlspecialchars($t['file_path']) ?>', 'Guru (Bahan Soal)')">
                                <i class="bi bi-eye-fill me-1"></i> Pratinjau
                            </button>
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
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Kelas Target <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none small fw-bold text-teal select-all-tugas-edit" data-target="edit_tugas_k_<?= $t['id'] ?>">
                                    <i class="bi bi-check-all me-1"></i> Pilih Semua Kelas
                                </button>
                            </div>
                            <div class="p-3 bg-light rounded-3 border" style="max-height: 150px; overflow-y: auto;">
                                <div class="row g-2">
                                    <?php 
                                    $editTargetIds = !empty($t['kelas_ids']) ? array_map('intval', explode(',', $t['kelas_ids'])) : [(int)$t['kelas_id']];
                                    foreach ($kelasList as $k): 
                                        $isChecked = in_array((int)$k['id'], $editTargetIds);
                                    ?>
                                        <div class="col-6 col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input edit_tugas_k_<?= $t['id'] ?>" type="checkbox" name="kelas_ids[]" value="<?= $k['id'] ?>" id="edit_tugas_k_<?= $t['id'] ?>_<?= $k['id'] ?>" <?= $isChecked ? 'checked' : '' ?>>
                                                <label class="form-check-label small fw-medium text-dark cursor-pointer" for="edit_tugas_k_<?= $t['id'] ?>_<?= $k['id'] ?>">
                                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size:0.72rem;">Bisa memilih lebih dari 1 kelas sekaligus.</small>
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

    <!-- Modal Grade Submissions (Extra Wide & Modern with Bulk Save) -->
    <div class="modal fade" id="modalGrade<?= $t['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4 shadow-2xl overflow-hidden bg-white">
                <div class="modal-header border-0 bg-dark text-white p-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0d9488 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-warning text-dark rounded-4 p-2.5 shadow-sm d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                            <i class="bi bi-award-fill fs-3"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span class="badge bg-white text-dark px-2.5 py-1 rounded-pill fw-bold" style="font-size:0.72rem;"><?= htmlspecialchars($t['nama_mapel']) ?></span>
                                <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill fw-bold" style="font-size:0.72rem;"><?= count($submissions) ?> Berkas Terkumpul</span>
                            </div>
                            <h5 class="modal-title fw-bold text-white mb-0" style="letter-spacing:-0.3px;">Pengumpulan & Penilaian Rubrik: <?= htmlspecialchars($t['judul']) ?></h5>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form action="<?= BASE_URL ?>index.php?url=guru/tugas" method="POST" class="d-flex flex-column h-100 mb-0">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="bulk_grade">

                    <div class="modal-body p-4 bg-light">
                        <!-- Instruction Snippet Banner -->
                        <div class="p-3 bg-white rounded-3 border mb-3 shadow-xs">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <small class="text-uppercase fw-bold text-muted d-block" style="font-size:0.68rem;">Petunjuk Pengerjaan & Rubrik Tugas:</small>
                                    <span class="text-dark small fw-medium text-clamp-2"><?= htmlspecialchars(mb_strimwidth($t['deskripsi'], 0, 180, '...')) ?></span>
                                </div>
                                <?php if (!empty($t['file_path'])): ?>
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 fw-bold"
                                            onclick="previewSubmissionFile('<?= BASE_URL ?>assets/uploads/tugas/<?= htmlspecialchars($t['file_path']) ?>', 'Lampiran Soal Guru: <?= htmlspecialchars($t['judul']) ?>', 'Bahan Soal Guru')">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Lihat Soal Modul
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (empty($submissions)): ?>
                            <div class="card border-0 rounded-4 shadow-sm p-5 text-center text-muted bg-white">
                                <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                                    <i class="bi bi-inbox fs-1"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Belum Ada Pengumpulan Siswa</h6>
                                <p class="small text-muted mb-0">Belum ada berkas tugas yang dikirimkan oleh siswa untuk rombel ini.</p>
                            </div>
                        <?php else: ?>

                            <?php if (!$isAdminMonitoring): ?>
                                <!-- Quick Fill All Bar & Bulk Save Button Header -->
                                <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-3 rounded-3 border shadow-xs flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <small class="fw-bold text-dark me-1"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Isi Cepat Semua Siswa:</small>
                                        <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-3 py-1 fw-bold" style="font-size:0.75rem;" onclick="bulkFillScores(<?= $t['id'] ?>, 100)">Set Semua 100</button>
                                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 fw-bold" style="font-size:0.75rem;" onclick="bulkFillScores(<?= $t['id'] ?>, 90)">Set Semua 90</button>
                                        <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-3 py-1 fw-bold" style="font-size:0.75rem;" onclick="bulkFillScores(<?= $t['id'] ?>, 85)">Set Semua 85</button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-bold" style="font-size:0.75rem;" onclick="bulkFillScores(<?= $t['id'] ?>, 75)">Set Semua 75</button>
                                    </div>
                                    <button type="submit" class="btn btn-success fw-bold rounded-pill px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); font-size: 0.88rem;">
                                        <i class="bi bi-check-all fs-5"></i>
                                        <span>Simpan Semua Nilai Siswa</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <div class="table-responsive rounded-4 border bg-white shadow-xs">
                                <table class="table align-middle table-hover mb-0">
                                    <thead class="table-light border-bottom">
                                        <tr style="font-size: 0.82rem;" class="text-uppercase text-muted fw-bold">
                                            <th class="ps-3" style="width: 25%;">Informasi Siswa</th>
                                            <th style="width: 15%;">Waktu Pengiriman</th>
                                            <th style="width: 23%;">Berkas Kiriman Siswa</th>
                                            <th style="width: 12%;">Status & Skor Saat Ini</th>
                                            <th style="width: 25%;" class="pe-3">Form Input Nilai & Catatan Rubrik</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($submissions as $sub): 
                                            $subFile = trim($sub['file_path'] ?? '');
                                            $catatanText = trim($sub['catatan_siswa'] ?? '');

                                            $extractedUrlFromCatatan = null;
                                            if (preg_match('/(https?:\/\/[^\s]+)/i', $catatanText, $matches)) {
                                                $extractedUrlFromCatatan = $matches[1];
                                            }

                                            $isUrlFile = (!empty($subFile) && (str_starts_with($subFile, 'http://') || str_starts_with($subFile, 'https://')));
                                            $isUrlCatatan = (!empty($extractedUrlFromCatatan));

                                            if ($isUrlFile) {
                                                $fileUrl = $subFile;
                                                $isDrive = (strpos($subFile, 'drive.google.com') !== false || strpos($subFile, 'docs.google.com') !== false);
                                            } elseif ($isUrlCatatan && empty($subFile)) {
                                                $fileUrl = $extractedUrlFromCatatan;
                                                $isDrive = (strpos($extractedUrlFromCatatan, 'drive.google.com') !== false || strpos($extractedUrlFromCatatan, 'docs.google.com') !== false);
                                            } elseif (!empty($subFile)) {
                                                $fileUrl = BASE_URL . 'assets/uploads/tugas/' . htmlspecialchars($subFile);
                                                $isDrive = false;
                                            } else {
                                                $fileUrl = null;
                                                $isDrive = false;
                                            }

                                            $fileExt = (!empty($subFile) && !$isUrlFile) ? strtolower(pathinfo($subFile, PATHINFO_EXTENSION)) : ($isDrive ? 'DRIVE' : ($isUrlFile || $isUrlCatatan ? 'LINK' : ''));
                                        ?>
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="d-flex align-items-center gap-2.5">
                                                        <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:38px; height:38px;">
                                                            <i class="bi bi-person-fill fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark fs-6 mb-0"><?= htmlspecialchars($sub['nama_lengkap']) ?></div>
                                                            <small class="text-muted d-block" style="font-size:0.75rem;">NIS: <strong><?= htmlspecialchars($sub['nis']) ?></strong> &bull; Kelas: <?= htmlspecialchars($sub['nama_kelas'] ?? '-') ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="small fw-semibold text-dark"><i class="bi bi-clock-history text-primary me-1"></i><?= date('d M Y', strtotime($sub['submitted_at'])) ?></div>
                                                    <small class="text-muted" style="font-size:0.72rem;"><?= date('H:i', strtotime($sub['submitted_at'])) ?> WIB</small>
                                                </td>
                                                <td>
                                                    <?php if ($fileUrl): ?>
                                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                            <button type="button" class="btn btn-sm <?= $isDrive ? 'btn-success' : 'btn-primary' ?> rounded-pill px-3 py-1 fw-bold shadow-xs hover-scale" style="font-size:0.75rem;"
                                                                    onclick="previewSubmissionFile('<?= htmlspecialchars($fileUrl) ?>', '<?= htmlspecialchars($isDrive ? 'Google Drive: ' . $sub['nama_lengkap'] : ($subFile ? $subFile : 'Link Jawaban Siswa')) ?>', '<?= htmlspecialchars($sub['nama_lengkap']) ?>')">
                                                                <i class="bi <?= $isDrive ? 'bi-google' : 'bi-eye-fill' ?> me-1"></i> <?= $isDrive ? 'Pratinjau Drive' : 'Lihat Berkas' ?>
                                                            </button>

                                                            <?php if ($isUrlFile || $isUrlCatatan): ?>
                                                                <a href="<?= htmlspecialchars($fileUrl) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.75rem;" title="Buka Link di Tab Baru">
                                                                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka Link
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="<?= htmlspecialchars($fileUrl) ?>" download class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.75rem;" title="Unduh File Berkas">
                                                                    <i class="bi bi-download me-1"></i> Unduh
                                                                </a>
                                                            <?php endif; ?>

                                                            <span class="badge bg-<?= $isDrive ? 'success' : 'secondary' ?>-subtle text-<?= $isDrive ? 'success' : 'secondary' ?> rounded-pill px-2 py-0.5" style="font-size:0.68rem;"><?= strtoupper($fileExt) ?></span>
                                                        </div>
                                                        <?php if (!empty($catatanText)): ?>
                                                            <div class="small text-muted mt-1" style="font-size:0.75rem;">
                                                                <i class="bi bi-chat-left-text me-1 text-primary"></i><?= htmlspecialchars($catatanText) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <?php if (!empty($catatanText)): ?>
                                                            <div class="small text-dark bg-light p-2 rounded border" style="font-size:0.78rem;">
                                                                <i class="bi bi-chat-left-text me-1 text-primary"></i><?= htmlspecialchars($catatanText) ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1" style="font-size:0.72rem;"><i class="bi bi-x-circle me-1"></i>Tanpa File / Link</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($sub['nilai'] !== null): ?>
                                                        <?php
                                                        $valScore = (float)$sub['nilai'];
                                                        $scoreBadgeClass = ($valScore >= 85) ? 'bg-success text-white' : (($valScore >= 75) ? 'bg-primary text-white' : 'bg-danger text-white');
                                                        ?>
                                                        <span class="badge <?= $scoreBadgeClass ?> fs-6 rounded-pill px-3 py-1.5 shadow-xs fw-extrabold">
                                                            <?= number_format($valScore, 1) ?> / 100
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark border border-warning-subtle rounded-pill px-2.5 py-1.5 fw-bold" style="font-size:0.72rem;">
                                                            <i class="bi bi-hourglass-split me-1"></i>Belum Dinilai
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="pe-3">
                                                    <?php if (!$isAdminMonitoring): ?>
                                                        <div class="bg-light p-2.5 rounded-3 border">
                                                            <div class="input-group input-group-sm mb-1.5">
                                                                <span class="input-group-text bg-white fw-bold text-primary">Skor</span>
                                                                <input type="number" name="grades[<?= $sub['id'] ?>][nilai]" class="form-control fw-extrabold text-primary score-input-<?= $t['id'] ?>" value="<?= $sub['nilai'] ?>" placeholder="0-100" min="0" max="100" style="font-size:0.9rem;">
                                                            </div>

                                                            <div class="mb-1.5">
                                                                <input type="text" name="grades[<?= $sub['id'] ?>][komentar]" class="form-control form-control-sm text-dark" value="<?= htmlspecialchars($sub['komentar_guru'] ?? '') ?>" placeholder="Catatan rubrik / evaluasi...">
                                                            </div>

                                                            <div class="d-flex align-items-center justify-content-between gap-1">
                                                                <div class="d-flex gap-1">
                                                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" style="font-size:0.68rem;" onclick="this.closest('.bg-light').querySelector('input[name*=\'[nilai\']').value=100">100</button>
                                                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" style="font-size:0.68rem;" onclick="this.closest('.bg-light').querySelector('input[name*=\'[nilai\']').value=90">90</button>
                                                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" style="font-size:0.68rem;" onclick="this.closest('.bg-light').querySelector('input[name*=\'[nilai\']').value=85">85</button>
                                                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5" style="font-size:0.68rem;" onclick="this.closest('.bg-light').querySelector('input[name*=\'[nilai\']').value=75">75</button>
                                                                </div>
                                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill" style="font-size:0.68rem;">Input Siap</span>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <small class="text-muted fw-semibold"><i class="bi bi-chat-quote me-1"></i><?= htmlspecialchars($sub['komentar_guru'] ?? 'Belum ada catatan rubrik') ?></small>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="modal-footer border-0 pt-0 p-4 justify-content-between bg-white border-top">
                        <span class="text-muted small"><i class="bi bi-info-circle me-1 text-primary"></i>Nilai tersimpan akan otomatis tersinkronisasi ke E-Rapor Digital.</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Tutup</button>
                            <?php if (!$isAdminMonitoring && !empty($submissions)): ?>
                                <button type="submit" class="btn btn-success fw-bold rounded-pill px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                    <i class="bi bi-check-all fs-5"></i>
                                    <span>Simpan Semua Nilai Siswa</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Universal Document & File Preview Modal for Teachers -->
<div class="modal fade" id="modalSubmissionFileViewer" tabindex="-1" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 rounded-4 shadow-2xl overflow-hidden bg-white">
            <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-gradient p-2.5 rounded-3 text-white shadow-sm">
                        <i class="bi bi-file-earmark-text-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="viewerFileName">Pratinjau Berkas Jawaban Siswa</h6>
                        <small class="text-info fw-medium" style="font-size:0.75rem;" id="viewerStudentInfo">Pengirim: Siswa</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="viewerDownloadBtn" class="btn btn-sm btn-success fw-bold rounded-pill px-3.5 py-1.5 shadow-xs" download>
                        <i class="bi bi-download me-1.5"></i> Unduh Berkas
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-0 bg-dark d-flex align-items-center justify-content-center" id="viewerContentBody" style="min-height: 580px;">
                <!-- Dynamic Content (PDF Iframe, Image, Video, Office Download Box) -->
            </div>
        </div>
    </div>
</div>

<script>
function getGoogleDriveEmbedUrl(url) {
    if (!url) return url;

    // File view: drive.google.com/file/d/FILE_ID/view...
    let matchFile = url.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/);
    if (matchFile && matchFile[1]) {
        return `https://drive.google.com/file/d/${matchFile[1]}/preview`;
    }

    // Docs/Sheets/Slides: docs.google.com/document/d/DOC_ID/edit...
    let matchDoc = url.match(/docs\.google\.com\/(document|spreadsheets|presentation|forms)\/d\/([a-zA-Z0-9_-]+)/);
    if (matchDoc && matchDoc[1] && matchDoc[2]) {
        return `https://docs.google.com/${matchDoc[1]}/d/${matchDoc[2]}/preview`;
    }

    // Folders: drive.google.com/drive/folders/FOLDER_ID
    let matchFolder = url.match(/drive\.google\.com\/drive\/(?:u\/\d+\/)?folders\/([a-zA-Z0-9_-]+)/);
    if (matchFolder && matchFolder[1]) {
        return `https://drive.google.com/embeddedfolderview?id=${matchFolder[1]}#list`;
    }

    // Open drive URL: drive.google.com/open?id=FILE_ID
    let matchOpen = url.match(/drive\.google\.com\/open\?id=([a-zA-Z0-9_-]+)/);
    if (matchOpen && matchOpen[1]) {
        return `https://drive.google.com/file/d/${matchOpen[1]}/preview`;
    }

    return url;
}

function previewSubmissionFile(fileUrl, fileName, studentName) {
    document.getElementById('viewerFileName').textContent = fileName;
    document.getElementById('viewerStudentInfo').textContent = 'Berkas Kiriman: ' + studentName;
    document.getElementById('viewerDownloadBtn').href = fileUrl;

    const isDrive = fileUrl.includes('drive.google.com') || fileUrl.includes('docs.google.com');
    const isExternalUrl = fileUrl.startsWith('http://') || fileUrl.startsWith('https://');

    const container = document.getElementById('viewerContentBody');
    container.innerHTML = '';

    if (isDrive) {
        const embedUrl = getGoogleDriveEmbedUrl(fileUrl);
        container.innerHTML = `
            <div class="w-100 h-100 d-flex flex-column bg-white">
                <div class="p-3 bg-warning-subtle text-dark border-bottom d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill text-warning fs-5 flex-shrink-0"></i>
                        <small class="fw-medium lh-sm">
                            <strong>Catatan Akses Google Drive:</strong> Tautan ini harus disetting dengan izin <strong>"Siapa saja yang memiliki link (Anyone with link)"</strong> di Google Drive agar dapat langsung terbaca dan tampil di bawah ini.
                        </small>
                    </div>
                    <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-bold text-nowrap shadow-xs" style="font-size:0.78rem;">
                        <i class="bi bi-box-arrow-up-right me-1.5"></i> Buka di Tab Baru
                    </a>
                </div>
                <div class="flex-grow-1 position-relative" style="min-height: 540px;">
                    <iframe src="${embedUrl}" style="width:100%; height:600px; border:none;" allowfullscreen></iframe>
                </div>
            </div>
        `;
    } else if (isExternalUrl) {
        container.innerHTML = `
            <div class="w-100 h-100 d-flex flex-column bg-white">
                <div class="p-3 bg-info-subtle text-dark border-bottom d-flex align-items-center justify-content-between gap-3">
                    <small class="fw-medium text-truncate">
                        <i class="bi bi-link-45deg me-1 text-primary fs-5"></i> Menampilkan Tautan Eksternal: <strong>${fileUrl}</strong>
                    </small>
                    <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold text-nowrap">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru
                    </a>
                </div>
                <iframe src="${fileUrl}" style="width:100%; height:600px; border:none;" allowfullscreen></iframe>
            </div>
        `;
    } else {
        const ext = fileName.split('.').pop().toLowerCase();
        if (ext === 'pdf') {
            container.innerHTML = `<iframe src="${fileUrl}" style="width:100%; height:660px; border:none;" allowfullscreen></iframe>`;
        } else if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext)) {
            container.innerHTML = `<div class="p-4 text-center w-100"><img src="${fileUrl}" alt="Preview" class="img-fluid rounded-4 shadow-lg" style="max-height: 600px; object-fit: contain;"></div>`;
        } else if (['mp4', 'mkv', 'webm', 'avi'].includes(ext)) {
            container.innerHTML = `<div class="p-4 w-100 text-center"><video src="${fileUrl}" controls class="w-100 rounded-4 shadow-lg" style="max-height: 580px;"></video></div>`;
        } else {
            container.innerHTML = `
                <div class="p-5 text-center my-auto bg-white w-100" style="min-height: 580px; display:flex; flex-direction:column; justify-content:center; align-items:center;">
                    <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px; height:80px;">
                        <i class="bi bi-file-earmark-arrow-down-fill fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Pratinjau Dokumen Format ${ext.toUpperCase()}</h5>
                    <p class="text-muted small mb-4 mx-auto" style="max-width: 440px;">Dokumen ini berformat Microsoft Office / Berkas Terkompresi (${ext.toUpperCase()}). Silakan unduh berkas untuk membukanya di komputer atau HP Anda.</p>
                    <a href="${fileUrl}" download class="btn btn-primary fw-bold rounded-pill px-4 py-2.5 shadow-sm text-white">
                        <i class="bi bi-download me-1.5"></i> Unduh Berkas ${ext.toUpperCase()} Sekarang
                    </a>
                </div>
            `;
        }
    }

    const viewerModal = new bootstrap.Modal(document.getElementById('modalSubmissionFileViewer'));
    viewerModal.show();
}

function bulkFillScores(tugasId, score) {
    const inputs = document.querySelectorAll('.score-input-' + tugasId);
    inputs.forEach(input => {
        input.value = score;
    });
}

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

document.addEventListener('DOMContentLoaded', function() {
    const selectAllAdd = document.getElementById('selectAllKelasTugasAdd');
    if (selectAllAdd) {
        selectAllAdd.addEventListener('click', function() {
            const boxes = document.querySelectorAll('.kelas-checkbox-add-tugas');
            const allChecked = Array.from(boxes).every(b => b.checked);
            boxes.forEach(b => b.checked = !allChecked);
        });
    }

    document.querySelectorAll('.select-all-tugas-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetClass = this.getAttribute('data-target');
            const boxes = document.querySelectorAll('.' + targetClass);
            const allChecked = Array.from(boxes).every(b => b.checked);
            boxes.forEach(b => b.checked = !allChecked);
        });
    });

    const addForm = document.querySelector('#modalAddTugas form');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            const checked = addForm.querySelectorAll('input[name="kelas_ids[]"]:checked');
            if (checked.length === 0) {
                e.preventDefault();
                alert('Pilih minimal satu kelas target.');
            }
        });
    }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
