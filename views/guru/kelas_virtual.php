<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* Modern LMS Teacher Virtual Class Architecture */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.guru-virtual-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

/* Glassmorphic Hero Banner */
.guru-virtual-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #2563eb 100%);
    border-radius: 20px;
    box-shadow: 0 12px 30px -5px rgba(37, 99, 235, 0.25);
    position: relative;
    overflow: hidden;
}

.guru-virtual-hero::after {
    content: '';
    position: absolute;
    top: -40%;
    right: -15%;
    width: 360px;
    height: 360px;
    background: radial-gradient(circle, rgba(96, 165, 250, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}

/* Class Card Architecture */
.guru-class-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}
.guru-class-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px -5px rgba(15, 23, 42, 0.09) !important;
    border-color: #cbd5e1;
}

/* Nav Pills Styling */
.custom-nav-pills .nav-link {
    border-radius: 30px !important;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 8px 18px;
    transition: all 0.2s ease;
}
.custom-nav-pills .nav-link.active {
    background: #2563eb !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25) !important;
}

/* Responsive Overrides */
@media (max-width: 575.98px) {
    .guru-virtual-hero {
        padding: 1.25rem !important;
        border-radius: 16px !important;
    }
    .hero-btn-group {
        width: 100% !important;
    }
    .hero-btn-group .btn {
        flex: 1 1 100% !important;
        text-align: center;
    }
    table.datatable {
        min-width: 650px !important;
    }
}
</style>

<main class="main-content px-2 px-sm-3 px-md-4 py-3 guru-virtual-wrapper">
<div class="container-fluid pt-3">

    <!-- Hero Banner Header -->
    <div class="guru-virtual-hero text-white p-4 p-md-5 mb-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-3 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-gradient p-3.5 rounded-4 text-white shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 54px; height: 54px; background: #2563eb;">
                    <i class="bi bi-bounding-box-circles fs-2"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-white mb-1" style="letter-spacing: -0.4px;">Manajemen Kelas &amp; Mapel Pengampuan</h3>
                    <p class="text-blue-100 small mb-0 fw-medium">Kelola Rombel Kelas Ajar, Kode Akses (Key) Mapel, dan Data Siswa Terdaftar secara terstruktur.</p>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap hero-btn-group">
                <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill shadow-sm px-3.5 py-2" data-bs-toggle="modal" data-bs-target="#modalGuruSetKey" style="font-size: 0.83rem;">
                    <i class="bi bi-key-fill me-1"></i> Set Key Mapel
                </button>
                <a href="<?= BASE_URL ?>index.php?url=guru/materi" class="btn btn-light fw-bold rounded-pill shadow-sm px-3.5 py-2 text-primary" style="font-size: 0.83rem;">
                    <i class="bi bi-cloud-upload me-1"></i> Upload Materi
                </a>
                <a href="<?= BASE_URL ?>index.php?url=guru/tugas" class="btn btn-success fw-bold rounded-pill shadow-sm px-3.5 py-2 text-white" style="font-size: 0.83rem;">
                    <i class="bi bi-plus-circle me-1"></i> Buat Tugas Baru
                </a>
            </div>
        </div>
    </div>

    <!-- Active Tab Selection Logic -->
    <?php
    $activeTab = $_GET['tab'] ?? (!empty($filterMapelId) || !empty($filterKelasId) || !empty($filterJurusanId) || !empty($filterSearch) ? 'siswa' : 'kelas');
    ?>

    <!-- Navigation Pills / Tabs -->
    <div class="card border-0 rounded-4 shadow-sm p-2 mb-4 bg-white">
        <ul class="nav nav-pills custom-nav-pills gap-2 flex-nowrap overflow-x-auto pb-1" id="guruVirtualTabs" role="tablist" style="-webkit-overflow-scrolling: touch;">
            <li class="nav-item" role="presentation">
                <button class="nav-link text-nowrap <?= $activeTab === 'kelas' ? 'active' : 'text-secondary' ?>" 
                        id="tab-kelas-btn" data-bs-toggle="pill" data-bs-target="#pane-kelas" type="button" role="tab" aria-controls="pane-kelas" aria-selected="<?= $activeTab === 'kelas' ? 'true' : 'false' ?>">
                    <i class="bi bi-grid-3x3-gap-fill me-1.5 text-primary"></i>1. Rombel Kelas Saya
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-nowrap <?= $activeTab === 'key' ? 'active' : 'text-secondary' ?>" 
                        id="tab-key-btn" data-bs-toggle="pill" data-bs-target="#pane-key" type="button" role="tab" aria-controls="pane-key" aria-selected="<?= $activeTab === 'key' ? 'true' : 'false' ?>">
                    <i class="bi bi-key-fill me-1.5 text-warning"></i>2. Kode Akses (Key) Mapel
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-nowrap <?= $activeTab === 'siswa' ? 'active' : 'text-secondary' ?>" 
                        id="tab-siswa-btn" data-bs-toggle="pill" data-bs-target="#pane-siswa" type="button" role="tab" aria-controls="pane-siswa" aria-selected="<?= $activeTab === 'siswa' ? 'true' : 'false' ?>">
                    <i class="bi bi-people-fill me-1.5 text-success"></i>3. Siswa Terdaftar (<?= count($siswaEnrolledList) ?>)
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Content Panes -->
    <div class="tab-content" id="guruVirtualTabsContent">
        
        <!-- PANE 1: ROMBEL KELAS VIRTUAL SAYA -->
        <div class="tab-pane fade <?= $activeTab === 'kelas' ? 'show active' : '' ?>" id="pane-kelas" role="tabpanel" aria-labelledby="tab-kelas-btn">
            <div class="row g-3 mb-4">
                <?php if (empty($kelasList)): ?>
                    <div class="col-12 text-center py-5 text-muted card border-0 rounded-4 shadow-sm bg-white">
                        <div class="bg-slate-100 text-slate-400 rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px; background-color: #f1f5f9;">
                            <i class="bi bi-bounding-box fs-1 text-secondary"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Belum Ada Rombel Kelas Terdaftar</h6>
                        <p class="small text-muted mb-0">Silakan hubungi Administrator Sekolah untuk penugasan pengampuan kelas.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($kelasList as $k): 
                        $isMyWaliKelas = ($k['wali_kelas_id'] ?? 0) == ($guru['id'] ?? 0);
                    ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="guru-class-card p-3.5 p-sm-4 h-100 position-relative border-top border-4 <?= $isMyWaliKelas ? 'border-success' : 'border-primary' ?> shadow-sm d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2 gap-1">
                                    <div>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill mb-1 fw-bold" style="font-size:0.7rem;">Tingkat <?= htmlspecialchars($k['tingkat'] ?? 'X') ?></span>
                                        <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($k['nama_kelas']) ?></h5>
                                    </div>
                                    <?php if ($isMyWaliKelas): ?>
                                        <span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.68rem;"><i class="bi bi-check-circle-fill me-1"></i>Saya Wali Kelas</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 fw-medium" style="font-size:0.68rem;">Rombel Ajar</span>
                                    <?php endif; ?>
                                </div>

                                <p class="text-muted small mb-3"><i class="bi bi-mortarboard me-1"></i>Jurusan: <?= htmlspecialchars($k['nama_jurusan'] ?? 'Umum') ?></p>

                                <div class="p-3 rounded-3 mb-3 border small" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                        <span class="text-muted" style="font-size:0.73rem;"><i class="bi bi-person-badge me-1 text-primary"></i>Wali Kelas:</span>
                                        <strong class="text-dark" style="font-size:0.76rem;"><?= htmlspecialchars($k['nama_walikelas'] ?? 'Belum Ditentukan') ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted" style="font-size:0.73rem;">Kode Gabung Rombel:</span>
                                        <code class="fw-bold text-primary fs-6">MH-<?= strtoupper(substr(md5($k['id']), 0, 6)) ?></code>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted" style="font-size:0.73rem;">Total Siswa Rombel:</span>
                                        <strong class="text-success" style="font-size:0.76rem;"><?= $k['total_siswa'] ?> Siswa</strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted" style="font-size:0.73rem;">Modul Unggahan Saya:</span>
                                        <strong class="text-info" style="font-size:0.76rem;"><?= $k['total_materi_guru'] ?> Modul</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center gap-1.5 pt-2 border-top flex-wrap">
                                <a href="<?= BASE_URL ?>index.php?url=guru/absensi&kelas_id=<?= $k['id'] ?>" class="btn btn-sm btn-outline-success rounded-pill flex-grow-1 fw-bold py-1.5 px-2 text-nowrap" style="font-size:0.75rem;">
                                    <i class="bi bi-calendar-check me-1"></i> Presensi
                                </a>
                                <a href="<?= BASE_URL ?>index.php?url=guru/materi&kelas_id=<?= $k['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill flex-grow-1 fw-bold py-1.5 px-2 text-nowrap" style="font-size:0.75rem;">
                                    <i class="bi bi-book me-1"></i> Materi
                                </a>
                                <a href="<?= BASE_URL ?>index.php?url=guru/tugas&kelas_id=<?= $k['id'] ?>" class="btn btn-sm btn-outline-warning text-dark rounded-pill flex-grow-1 fw-bold py-1.5 px-2 text-nowrap" style="font-size:0.75rem;">
                                    <i class="bi bi-card-checklist me-1"></i> Tugas
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- PANE 2: KODE AKSES (KEY / PASSCODE) MAPEL SAYA -->
        <div class="tab-pane fade <?= $activeTab === 'key' ? 'show active' : '' ?>" id="pane-key" role="tabpanel" aria-labelledby="tab-key-btn">
            <div class="card border-0 rounded-4 shadow-sm p-3.5 p-md-4 mb-4 bg-white border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-3 border-bottom">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-shield-lock-fill text-warning me-2 fs-5"></i>Kode Akses (Key / Passcode) Mapel Saya
                        </h5>
                        <small class="text-muted">Bagikan Key ini kepada siswa agar mereka terdaftar pada mata pelajaran pengampuan Anda.</small>
                    </div>
                    <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill shadow-sm px-3.5 py-2 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalGuruSetKey" style="font-size:0.83rem;">
                        <i class="bi bi-plus-circle me-1"></i> Buat / Edit Key Mapel
                    </button>
                </div>

                <div class="alert alert-info border-0 rounded-4 shadow-sm mb-3 d-flex align-items-center gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px; height:40px;">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                    </div>
                    <div class="small">
                        <strong class="text-dark d-block mb-0">💡 Kunci Mapel Otomatis Terpisah Per-Rombel Kelas:</strong>
                        <span class="text-secondary">Sistem secara otomatis telah membuatkan <strong>Key Pendaftaran unik untuk setiap Rombel Kelas</strong> yang Anda ampu (Kelas 10, 11, 12). Berikan Key yang sesuai dengan Rombel Kelas siswa agar siswa tidak tertukar atau salah kelas saat mendaftar!</span>
                    </div>
                </div>

                <?php if (empty($myKeys)): ?>
                    <div class="text-center py-5 text-muted">
                        <div class="bg-slate-100 text-slate-400 rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px; background-color: #f1f5f9;">
                            <i class="bi bi-key fs-1 text-warning"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Belum Ada Key Mapel Dibuat</h6>
                        <p class="small text-muted mb-0">Klik tombol <strong>Buat / Edit Key Mapel</strong> untuk membuat kode pendaftaran mapel baru.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($myKeys as $mk): ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="p-3.5 rounded-4 border h-100 d-flex flex-column justify-content-between" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1 gap-1">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fw-bold fs-6 text-truncate" style="max-width: 68%;"><?= htmlspecialchars($mk['nama_mapel']) ?></span>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fw-bold flex-shrink-0" style="font-size:0.7rem;">
                                                <i class="bi bi-people-fill me-1"></i><?= (int)$mk['total_siswa'] ?> Siswa
                                            </span>
                                        </div>
                                        <?php 
                                            $tNum = (int)($mk['tingkat'] ?? 0);
                                            $badgeStyle = ($tNum === 10) ? 'background:#e0e7ff; color:#3730a3;' :
                                                         (($tNum === 11) ? 'background:#f3e8ff; color:#6b21a8;' :
                                                         (($tNum === 12) ? 'background:#dcfce7; color:#15803d;' : 'background:#f1f5f9; color:#334155;'));
                                        ?>
                                        <div class="mb-3">
                                            <small class="text-muted d-block mb-1" style="font-size:0.75rem;">Target Rombel Kelas:</small>
                                            <?php if (!empty($mk['nama_kelas'])): ?>
                                                <span class="badge rounded-pill px-2.5 py-1 fw-bold border" style="<?= $badgeStyle ?> font-size:0.72rem;">
                                                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($mk['nama_kelas']) ?> (Kelas <?= $tNum ?>)
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-secondary border fw-semibold px-2.5 py-1" style="font-size:0.72rem;">
                                                    <i class="bi bi-globe me-1"></i>Semua Rombel (Global)
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between bg-white p-2.5 rounded-3 border gap-1">
                                        <code class="fs-6 fw-bold text-danger mb-0" style="letter-spacing:1px;"><?= htmlspecialchars($mk['enrollment_key']) ?></code>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.75rem;"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalGuruSetKey"
                                                    data-mapel="<?= $mk['mapel_id'] ?>"
                                                    data-kelas="<?= $mk['kelas_id'] ?? '' ?>"
                                                    data-key="<?= htmlspecialchars($mk['enrollment_key'], ENT_QUOTES) ?>">
                                                <i class="bi bi-pencil-square me-1"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-2.5 fw-bold" style="font-size:0.75rem;" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($mk['enrollment_key'], ENT_QUOTES) ?>'); alert('Key <?= htmlspecialchars($mk['enrollment_key'], ENT_QUOTES) ?> berhasil disalin!')">
                                                <i class="bi bi-clipboard me-1"></i> Salin
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- PANE 3: DATA SISWA TERDAFTAR MAPEL SAYA -->
        <div class="tab-pane fade <?= $activeTab === 'siswa' ? 'show active' : '' ?>" id="pane-siswa" role="tabpanel" aria-labelledby="tab-siswa-btn">
            <div class="card border-0 rounded-4 shadow-sm p-3.5 p-md-4 mb-4 bg-white border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-3 border-bottom">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-people-fill text-success me-2 fs-4"></i>Data Siswa Terdaftar di Mapel Saya
                        </h5>
                        <small class="text-muted">Daftar siswa yang telah memasukkan Key Mapel Anda secara otomatis terdata di bawah ini.</small>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-6 fw-bold">
                        Total: <?= count($siswaEnrolledList) ?> Siswa Terdaftar
                    </span>
                </div>

                <!-- Filter & Search Controls -->
                <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-2 mb-3">
                    <input type="hidden" name="url" value="guru/kelasVirtual">
                    <input type="hidden" name="tab" value="siswa">

                    <div class="col-12 col-sm-6 col-md-3">
                        <select name="mapel_id" class="form-select rounded-pill fw-semibold" style="font-size:0.83rem;" onchange="this.form.submit()">
                            <option value="">-- Semua Mapel Saya --</option>
                            <?php 
                            $guruMapels = !empty($myMapelList) ? $myMapelList : $mapelList;
                            foreach ($guruMapels as $m): 
                            ?>
                                <option value="<?= $m['id'] ?>" <?= (isset($filterMapelId) && $filterMapelId == $m['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nama_mapel']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <select name="kelas_id" class="form-select rounded-pill fw-semibold" style="font-size:0.83rem;" onchange="this.form.submit()">
                            <option value="">-- Semua Kelas Saya --</option>
                            <?php 
                            $guruKelasList = !empty($myKelasList) ? $myKelasList : $kelasList;
                            foreach ($guruKelasList as $k): 
                            ?>
                                <option value="<?= $k['id'] ?>" <?= (isset($filterKelasId) && $filterKelasId == $k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <select name="jurusan_id" class="form-select rounded-pill fw-semibold" style="font-size:0.83rem;" onchange="this.form.submit()">
                            <option value="">-- Semua Jurusan --</option>
                            <?php foreach ($jurusanList as $j): ?>
                                <option value="<?= $j['id'] ?>" <?= (isset($filterJurusanId) && $filterJurusanId == $j['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($j['nama_jurusan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3 d-flex gap-1.5">
                        <input type="text" name="search" class="form-control rounded-pill ps-3" placeholder="Cari Nama / NISN..." value="<?= htmlspecialchars($filterSearch ?? '') ?>" style="font-size:0.83rem;">
                        <button type="submit" class="btn btn-primary rounded-circle p-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                            <i class="bi bi-search"></i>
                        </button>
                        <?php if (!empty($filterMapelId) || !empty($filterKelasId) || !empty($filterJurusanId) || !empty($filterSearch)): ?>
                            <a href="<?= BASE_URL ?>index.php?url=guru/kelasVirtual&tab=siswa" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fw-bold text-nowrap" style="font-size:0.78rem;">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;">No</th>
                                <th>Nama Lengkap Siswa</th>
                                <th>Rombel Kelas</th>
                                <th>Jurusan</th>
                                <th>Mata Pelajaran</th>
                                <th>Tgl Terdaftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($siswaEnrolledList)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada siswa terdaftar yang sesuai filter.</td></tr>
                            <?php else: ?>
                                <?php foreach ($siswaEnrolledList as $i => $se): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td class="fw-bold text-dark">
                                            <i class="bi bi-person-fill text-secondary me-1"></i><?= htmlspecialchars($se['nama_lengkap']) ?>
                                            <small class="text-muted d-block">NISN: <?= htmlspecialchars($se['nisn'] ?? '-') ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fw-bold" style="font-size:0.75rem;">
                                                <?= htmlspecialchars($se['nama_kelas'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill" style="font-size:0.75rem;">
                                                <?= htmlspecialchars($se['nama_jurusan'] ?? 'Umum') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fw-bold" style="font-size:0.75rem;">
                                                <i class="bi bi-book-fill me-1"></i><?= htmlspecialchars($se['nama_mapel']) ?>
                                            </span>
                                        </td>
                                        <td class="text-muted small"><?= date('d M Y, H:i', strtotime($se['enrolled_at'])) ?> WIB</td>
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

<!-- Modal Set Key Mapel Saya (Guru) -->
<div class="modal fade" id="modalGuruSetKey" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title text-dark"><i class="bi bi-key-fill text-warning me-2"></i>Set / Edit Key Mapel Saya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=guru/kelasVirtual" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="save_key">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Pilih Mata Pelajaran Pengampuan <span class="text-danger">*</span></label>
                        <select name="mapel_id" id="guru_key_mapel_id" class="form-select rounded-3" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php 
                            $listM = !empty($myMapelList) ? $myMapelList : $mapelList;
                            foreach ($listM as $m): 
                            ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama_mapel']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Pilih Rombel Kelas Target <span class="text-danger">*Sangat Disarankan Per-Kelas</span></label>
                        <select name="kelas_id" id="guru_key_kelas_id" class="form-select rounded-3">
                            <option value="">-- Semua Kelas (Global) --</option>
                            <?php 
                            $listK = !empty($myKelasList) ? $myKelasList : $kelasList;
                            foreach ($listK as $k): 
                            ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?> (Kelas <?= htmlspecialchars($k['tingkat'] ?? '-') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted mt-1 d-block" style="font-size:0.75rem;">
                            <i class="bi bi-info-circle text-primary me-1"></i>Pilih Rombel Kelas spesifik agar Key Mapel Anda di Kelas 10, 11, dan 12 terpisah rapi.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Kode Akses / Key Mapel (Passcode) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="enrollment_key" id="guru_enrollment_key" class="form-control text-uppercase fw-bold rounded-start-3" placeholder="Contoh: AGAMA-X-RPL1" required style="letter-spacing:1px;">
                            <button type="button" class="btn btn-outline-secondary rounded-end-3" onclick="generateGuruSmartKey()">
                                <i class="bi bi-magic me-1"></i> Kunci Cerdas
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark"><i class="bi bi-check-circle-fill me-1"></i> Simpan Key Mapel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function generateGuruSmartKey() {
    const mapelSel = document.getElementById('guru_key_mapel_id');
    const kelasSel = document.getElementById('guru_key_kelas_id');
    
    let mapelText = mapelSel.options[mapelSel.selectedIndex] ? mapelSel.options[mapelSel.selectedIndex].text : 'MAPEL';
    let kelasText = kelasSel.options[kelasSel.selectedIndex] ? kelasSel.options[kelasSel.selectedIndex].text : '';
    
    mapelText = mapelText.replace(/[^a-zA-Z0-9]/g, '').substring(0, 5).toUpperCase();
    kelasText = kelasText.replace(/[^a-zA-Z0-9]/g, '').substring(0, 6).toUpperCase();
    
    const randNum = Math.floor(100 + Math.random() * 900);
    const resultKey = (mapelText || 'MPL') + (kelasText ? '-' + kelasText : '') + '-' + randNum;
    
    document.getElementById('guru_enrollment_key').value = resultKey;
}

document.addEventListener('DOMContentLoaded', function() {
    const modalGuruSetKey = document.getElementById('modalGuruSetKey');
    if (modalGuruSetKey) {
        modalGuruSetKey.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) return;
            
            const mapelId = button.getAttribute('data-mapel');
            const kelasId = button.getAttribute('data-kelas');
            const key = button.getAttribute('data-key');
            
            if (mapelId) {
                document.getElementById('guru_key_mapel_id').value = mapelId;
            }
            if (kelasId !== null && kelasId !== undefined) {
                document.getElementById('guru_key_kelas_id').value = kelasId;
            }
            if (key) {
                document.getElementById('guru_enrollment_key').value = key;
            }
        });
    }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
