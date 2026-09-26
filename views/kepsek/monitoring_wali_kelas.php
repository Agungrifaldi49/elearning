<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
// Helper resolusi avatar yang aman & fleksibel
if (!function_exists('resolveWaliAvatarUrl')) {
    function resolveWaliAvatarUrl($avatarFile, $fullName = 'Guru') {
        $cleanName = trim($fullName ?: 'Guru');
        $defaultUi = "https://ui-avatars.com/api/?name=" . urlencode($cleanName) . "&background=0D6EFD&color=fff&bold=true&size=128";

        if (empty($avatarFile) || !is_string($avatarFile) || in_array($avatarFile, ['default_avatar.png', 'default.png', 'avatar.png'])) {
            return $defaultUi;
        }

        $candidatePaths = [
            'assets/uploads/profile/' . $avatarFile,
            'assets/uploads/avatar/' . $avatarFile,
            'assets/uploads/guru/' . $avatarFile,
            'assets/uploads/foto/' . $avatarFile
        ];

        foreach ($candidatePaths as $relPath) {
            if (file_exists(ROOT_PATH . $relPath)) {
                return BASE_URL . $relPath;
            }
        }

        if (filter_var($avatarFile, FILTER_VALIDATE_URL)) {
            return $avatarFile;
        }

        return $defaultUi;
    }
}
?>

<style>
.wali-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, #0369a1 100%);
    border-radius: 1.25rem;
    color: #ffffff;
    box-shadow: 0 10px 25px -5px rgba(3, 105, 161, 0.25);
    position: relative;
    overflow: hidden;
}
.wali-hero-banner::after {
    content: "";
    position: absolute;
    top: -40%;
    right: -10%;
    width: 280px;
    height: 280px;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.stat-kpi-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
    transition: all 0.25s ease;
    height: 100%;
}
.stat-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.08);
}
@media print {
    .no-print, .main-navbar, .sidebar, .btn, .breadcrumb, form {
        display: none !important;
    }
    body, .main-content {
        padding: 0 !important;
        margin: 0 !important;
        background: #fff !important;
    }
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
}
</style>

<main class="main-content px-2 px-md-4 py-3">
<div class="container-fluid px-1 px-md-2">

    <!-- 1. Executive Header -->
    <div class="wali-hero-banner p-4 p-md-4 mb-4 shadow-sm">
        <div class="row align-items-center g-3">
            <div class="col-12 col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge bg-white text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase shadow-xs" style="font-size: 0.72rem;">
                        <i class="bi bi-shield-check text-info me-1.5"></i> Supervisi Eksekutif
                    </span>
                    <span class="badge bg-info text-dark px-3 py-1.5 rounded-pill fw-bold shadow-xs" style="font-size: 0.72rem;">
                        <i class="bi bi-calendar-check me-1"></i> TA <?= htmlspecialchars($activeTa['tahun'] ?? '2025/2026') ?> (<?= htmlspecialchars($activeTa['semester'] ?? 'Ganjil') ?>)
                    </span>
                </div>
                <h3 class="fw-bold mb-1 text-white d-flex align-items-center gap-2 flex-wrap">
                    <i class="bi bi-person-workspace text-info"></i>
                    <span>Monitoring Wali Kelas & Rombel Binaan</span>
                </h3>
                <p class="text-white text-opacity-90 mb-0 small" style="max-width: 680px;">
                    Pengawasan komprehensif penugasan dewan wali kelas, pemantauan tingkat kehadiran rombel, kelengkapan catatan E-Rapor peserta didik, dan kepatuhan administrasi SPP sekolah.
                </p>
            </div>
            
            <div class="col-12 col-lg-4 text-lg-end no-print">
                <div class="d-flex gap-2 justify-content-lg-end flex-wrap">
                    <button type="button" onclick="window.print()" class="btn btn-outline-light btn-sm rounded-pill px-3 shadow-xs fw-semibold">
                        <i class="bi bi-printer me-1"></i> Cetak Rekap Supervisi
                    </button>
                    <a href="<?= BASE_URL ?>index.php?url=kepsek/monitoringWaliKelas" class="btn btn-info text-dark btn-sm rounded-pill px-3 shadow-xs fw-semibold">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. KPI Executive Stat Cards -->
    <?php 
        $rasioWali = $totalRombel > 0 ? round(($totalRombelTerisi / $totalRombel) * 100) : 0;
    ?>
    <div class="row g-3 mb-4">
        <!-- Total Rombel -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-kpi-card border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Total Rombel Kelas</span>
                    <div class="bg-primary-subtle text-primary p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                        <i class="bi bi-easel-fill"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-dark"><?= $totalRombel ?> <span class="fs-6 fw-normal text-muted">Kelas</span></h4>
                <small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-building me-1 text-primary"></i>Tingkat X, XI, dan XII</small>
            </div>
        </div>

        <!-- Penugasan Wali Kelas -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-kpi-card border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Penugasan Wali Kelas</span>
                    <div class="bg-success-subtle text-success p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-success"><?= $totalRombelTerisi ?> / <?= $totalRombel ?> <span class="fs-6 fw-normal text-muted">Terisi</span></h4>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <div class="progress flex-grow-1" style="height: 6px; border-radius: 10px;">
                        <div class="progress-bar bg-success" style="width: <?= $rasioWali ?>%;"></div>
                    </div>
                    <span class="fw-bold text-success small" style="font-size: 0.75rem;"><?= $rasioWali ?>%</span>
                </div>
            </div>
        </div>

        <!-- Total Siswa Binaan -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-kpi-card border-start border-4 border-info">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Populasi Peserta Didik</span>
                    <div class="bg-info-subtle text-info p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-info"><?= number_format($totalSiswaAll, 0, ',', '.') ?> <span class="fs-6 fw-normal text-muted">Siswa</span></h4>
                <small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-check2-all text-info me-1"></i>Aktif Terdata dalam Rombel</small>
            </div>
        </div>

        <!-- Rata-rata Kehadiran Rombel -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-kpi-card border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Rata-Rata Kehadiran Siswa</span>
                    <div class="bg-warning-subtle text-warning p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-dark"><?= $avgAttendanceAll ?>%</h4>
                <small class="text-success fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-activity me-1"></i>Keaktifan KBM Harian</small>
            </div>
        </div>
    </div>

    <!-- 3. Filter & Search Panel -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 no-print">
        <div class="card-body p-3 p-md-3">
            <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-2 align-items-center">
                <input type="hidden" name="url" value="kepsek/monitoringWaliKelas">

                <!-- Filter Tingkat -->
                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                    <label class="form-label small text-muted fw-semibold mb-1">Tingkat Kelas:</label>
                    <select name="tingkat" class="form-select form-select-sm rounded-3">
                        <option value="all" <?= $filterTingkat === 'all' ? 'selected' : '' ?>>Semua Tingkat</option>
                        <option value="X" <?= $filterTingkat === 'X' ? 'selected' : '' ?>>Kelas X (Sepuluh)</option>
                        <option value="XI" <?= $filterTingkat === 'XI' ? 'selected' : '' ?>>Kelas XI (Sebelas)</option>
                        <option value="XII" <?= $filterTingkat === 'XII' ? 'selected' : '' ?>>Kelas XII (Duabelas)</option>
                    </select>
                </div>

                <!-- Filter Jurusan -->
                <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                    <label class="form-label small text-muted fw-semibold mb-1">Program Keahlian:</label>
                    <select name="jurusan_id" class="form-select form-select-sm rounded-3">
                        <option value="">Semua Program Keahlian</option>
                        <?php foreach ($jurusanList as $jur): ?>
                            <option value="<?= $jur['id'] ?>" <?= $filterJurusan == $jur['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($jur['nama_jurusan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Status Wali Kelas -->
                <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                    <label class="form-label small text-muted fw-semibold mb-1">Status Penugasan:</label>
                    <select name="status_wali" class="form-select form-select-sm rounded-3">
                        <option value="all" <?= $filterStatusWali === 'all' ? 'selected' : '' ?>>Semua Status</option>
                        <option value="terisi" <?= $filterStatusWali === 'terisi' ? 'selected' : '' ?>>Sudah Ada Wali</option>
                        <option value="kosong" <?= $filterStatusWali === 'kosong' ? 'selected' : '' ?>>Belum Ditugaskan</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                    <label class="form-label small text-muted fw-semibold mb-1">Pencarian Cepat:</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control form-control-sm border-start-0 rounded-end-3" placeholder="Cari nama kelas / guru wali...">
                    </div>
                </div>

                <!-- Tombol Action -->
                <div class="col-12 col-lg-2 d-flex gap-1.5 mt-3 mt-lg-auto">
                    <button type="submit" class="btn btn-primary btn-sm rounded-3 flex-grow-1 fw-semibold">
                        <i class="bi bi-funnel-fill me-1"></i> Filter
                    </button>
                    <?php if ($filterTingkat !== 'all' || $filterJurusan || $filterStatusWali !== 'all' || $search !== ''): ?>
                        <a href="<?= BASE_URL ?>index.php?url=kepsek/monitoringWaliKelas" class="btn btn-outline-secondary btn-sm rounded-3 px-2" title="Reset Filter">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Notice Integrasi Sistem Pembayaran (Jika data belum disinkronkan dari server pembayaran) -->
    <?php 
        $anySppData = false;
        foreach ($rombelList as $chkR) {
            if (!empty($chkR['spp_has_data'])) { $anySppData = true; break; }
        }
    ?>
    <?php if (!$anySppData): ?>
        <div class="alert alert-light border border-info-subtle rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2 shadow-xs no-print">
            <div class="d-flex align-items-center gap-2.5">
                <div class="bg-info-subtle text-info rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                    <i class="bi bi-cloud-arrow-down-fill fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark small">Status Integrasi Sistem Pembayaran (Menunggu Data)</h6>
                    <p class="text-muted small mb-0">Kolom <em>Kepatuhan SPP</em> saat ini berstatus <strong>Belum Ada Data</strong> karena data tagihan belum ditarik/disinkronkan dari sistem pembayaran eksternal (API Bridge). Metrik dan grafik kepatuhan akan terhitung otomatis begitu sinkronisasi dijalankan.</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>index.php?url=kepsek/pembayaran" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-semibold">
                <i class="bi bi-wallet2 me-1"></i> Buka Portal SPP
            </a>
        </div>
    <?php endif; ?>

    <!-- 4. Matriks Rombel & Wali Kelas Table -->
    <div class="card card-custom border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-table me-2 text-primary"></i>Matriks Supervisi Rombel & Kinerja Wali Kelas</h6>
            <span class="badge bg-light text-muted border px-2.5 py-1 small">Total <?= count($rombelList) ?> Rombel Terdata</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
                <thead class="table-light text-secondary text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">
                    <tr>
                        <th class="ps-3 ps-md-4 text-center" style="width: 50px;">No</th>
                        <th>Rombel & Jurusan</th>
                        <th>Wali Kelas Binaan</th>
                        <th class="text-center" style="width: 120px;">Peserta Didik</th>
                        <th class="text-center" style="width: 130px;">Kehadiran KBM</th>
                        <th class="text-center" style="width: 140px;">Catatan Rapor</th>
                        <th class="text-center" style="width: 130px;">Kepatuhan SPP</th>
                        <th class="text-end pe-3 pe-md-4 no-print" style="width: 180px;">Aksi Supervisi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rombelList)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                Tidak ditemukan data rombel sesuai kriteria filter yang dipilih.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($rombelList as $r): ?>
                            <tr>
                                <td class="ps-3 ps-md-4 text-center text-muted fw-bold"><?= $no++ ?></td>
                                
                                <!-- Rombel & Jurusan -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-3 fw-bold">
                                            <?= htmlspecialchars($r['tingkat']) ?>
                                        </span>
                                        <div>
                                            <div class="fw-bold text-dark mb-0.5"><?= htmlspecialchars($r['nama_kelas']) ?></div>
                                            <small class="text-muted" style="font-size:0.74rem;">
                                                <i class="bi bi-mortarboard me-1"></i><?= htmlspecialchars($r['nama_jurusan'] ?? 'Reguler') ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- Wali Kelas -->
                                <td>
                                    <?php if (!empty($r['guru_id'])): ?>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <?php 
                                                $waliAvUrl = resolveWaliAvatarUrl($r['avatar_wali'] ?? '', $r['nama_wali'] ?? 'Wali Kelas');
                                            ?>
                                            <img src="<?= $waliAvUrl ?>" 
                                                 class="rounded-circle border shadow-xs flex-shrink-0" 
                                                 style="width: 38px; height: 38px; object-fit: cover;" 
                                                 alt="<?= htmlspecialchars($r['nama_wali']) ?>"
                                                 loading="lazy"
                                                 onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($r['nama_wali'] ?? 'Guru') ?>&background=0D6EFD&color=fff&bold=true';">
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($r['nama_wali']) ?></div>
                                                <div class="text-muted small d-flex align-items-center gap-2" style="font-size:0.75rem;">
                                                    <span>NIP: <?= !empty($r['nip_wali']) ? htmlspecialchars($r['nip_wali']) : '-' ?></span>
                                                    <?php if (!empty($r['kontak_wali'])): ?>
                                                        <?php 
                                                            $waNumber = preg_replace('/[^0-9]/', '', $r['kontak_wali']);
                                                            if (substr($waNumber, 0, 1) === '0') {
                                                                $waNumber = '62' . substr($waNumber, 1);
                                                            }
                                                        ?>
                                                        <a href="https://wa.me/<?= $waNumber ?>" target="_blank" class="text-success text-decoration-none fw-semibold" title="Hubungi via WhatsApp">
                                                            <i class="bi bi-whatsapp"></i> WA
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill small">
                                            <i class="bi bi-exclamation-circle-fill me-1"></i>Belum Ditugaskan
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Populasi Siswa -->
                                <td class="text-center">
                                    <div class="fw-bold text-dark"><?= (int)$r['total_siswa'] ?> Siswa</div>
                                    <div class="d-flex justify-content-center gap-1 mt-0.5" style="font-size: 0.72rem;">
                                        <span class="badge bg-primary-subtle text-primary px-1.5 py-0.5">L: <?= (int)$r['siswa_l'] ?></span>
                                        <span class="badge bg-danger-subtle text-danger px-1.5 py-0.5">P: <?= (int)$r['siswa_p'] ?></span>
                                    </div>
                                </td>

                                <!-- Kehadiran KBM -->
                                <td class="text-center">
                                    <?php 
                                        $attRate = $r['kehadiran_persen'];
                                        $attBadge = ($attRate >= 85) ? 'bg-success' : (($attRate >= 70) ? 'bg-warning text-dark' : 'bg-danger text-white');
                                    ?>
                                    <span class="badge <?= $attBadge ?> px-2.5 py-1 rounded-pill fw-bold">
                                        <?= $attRate ?>%
                                    </span>
                                    <small class="d-block text-muted mt-0.5" style="font-size: 0.72rem;"><?= (int)$r['total_absensi'] ?> Sesi KBM</small>
                                </td>

                                <!-- Catatan E-Rapor -->
                                <td class="text-center">
                                    <?php 
                                        $totS = (int)$r['total_siswa'];
                                        $isiS = (int)$r['catatan_rapor_terisi'];
                                        $isFull = ($totS > 0 && $isiS >= $totS);
                                    ?>
                                    <span class="badge <?= $isFull ? 'bg-success-subtle text-success border border-success-subtle' : ($isiS > 0 ? 'bg-warning-subtle text-dark border border-warning-subtle' : 'bg-light text-muted border') ?> px-2.5 py-1 fw-bold">
                                        <?= $isiS ?> / <?= $totS ?> Terisi
                                    </span>
                                    <small class="d-block text-muted mt-0.5" style="font-size: 0.72rem;"><?= $r['catatan_rapor_persen'] ?>% Capaian</small>
                                </td>

                                <!-- Kepatuhan SPP -->
                                <td class="text-center">
                                    <?php if (!empty($r['spp_has_data'])): ?>
                                        <?php 
                                            $sppRate = $r['spp_rate'] ?? 0;
                                            $sppClr = ($sppRate >= 80) ? 'text-success' : (($sppRate >= 50) ? 'text-warning' : 'text-danger');
                                        ?>
                                        <div class="fw-bold <?= $sppClr ?>"><?= $sppRate ?>% Lunas</div>
                                        <div class="progress mx-auto mt-1" style="height: 5px; width: 75px; border-radius: 10px;">
                                            <div class="progress-bar <?= $sppRate >= 80 ? 'bg-success' : ($sppRate >= 50 ? 'bg-warning' : 'bg-danger') ?>" style="width: <?= min(100, $sppRate) ?>%;"></div>
                                        </div>
                                        <small class="d-block text-muted mt-0.5" style="font-size: 0.7rem;"><?= (int)$r['spp_count_tagihan'] ?> Tagihan</small>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border px-2 py-1 rounded-pill small" title="Data tagihan belum disinkronkan dari server sistem pembayaran">
                                            <i class="bi bi-cloud-arrow-down me-1 text-secondary"></i>Belum Ada Data
                                        </span>
                                        <small class="d-block text-muted mt-0.5" style="font-size: 0.7rem;">Sinkronisasi API</small>
                                    <?php endif; ?>
                                </td>

                                <!-- Aksi Supervisi -->
                                <td class="text-end pe-3 pe-md-4 no-print">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>index.php?url=kepsek/monitoringWaliKelas&detail_kelas_id=<?= $r['kelas_id'] ?>#detailSection" class="btn btn-outline-primary rounded-start-3 px-2 py-1" title="Lihat Rincian Siswa Binaan">
                                            <i class="bi bi-people-fill me-1"></i> Rincian Siswa
                                        </a>
                                        <?php if (!empty($r['total_siswa'])): ?>
                                            <a href="<?= BASE_URL ?>index.php?url=guru/cetakRaporRombel&kelas_id=<?= $r['kelas_id'] ?>" target="_blank" class="btn btn-primary px-2 py-1" title="Pratinjau E-Rapor Digital Rombel">
                                                <i class="bi bi-printer-fill"></i> Rapor
                                            </a>
                                            <a href="<?= BASE_URL ?>index.php?url=guru/rankingKelas&kelas_id=<?= $r['kelas_id'] ?>" target="_blank" class="btn btn-outline-warning rounded-end-3 px-2 py-1 text-dark" title="Leger Nilai & Ranking Kelas">
                                                <i class="bi bi-trophy-fill"></i>
                                            </a>
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

    <!-- 5. Detail Siswa Drill-Down (Jika Tombol Rincian Siswa Diklik) -->
    <?php if ($detailKelas): ?>
    <div class="card card-custom border-0 shadow-sm rounded-4 bg-white mb-4" id="detailSection">
        <div class="card-header bg-primary text-white py-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <?php 
                    $detWaliAvUrl = resolveWaliAvatarUrl($detailKelas['avatar_wali'] ?? '', $detailKelas['nama_wali'] ?? 'Wali Kelas');
                ?>
                <img src="<?= $detWaliAvUrl ?>" 
                     class="rounded-circle border border-2 border-white shadow-sm flex-shrink-0" 
                     style="width: 46px; height: 46px; object-fit: cover;" 
                     alt="<?= htmlspecialchars($detailKelas['nama_wali'] ?? '') ?>"
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($detailKelas['nama_wali'] ?? 'Wali') ?>&background=ffffff&color=0D6EFD&bold=true';">
                <div>
                    <h5 class="fw-bold mb-0 text-white"><i class="bi bi-mortarboard me-2"></i>Rombel: <?= htmlspecialchars($detailKelas['nama_kelas']) ?></h5>
                    <small class="text-white-50">
                        Wali Kelas: <strong><?= htmlspecialchars($detailKelas['nama_wali'] ?: 'Belum Ditugaskan') ?></strong>
                        <?php if (!empty($detailKelas['nip_wali'])): ?>
                            &bull; NIP: <?= htmlspecialchars($detailKelas['nip_wali']) ?>
                        <?php endif; ?>
                        &bull; Jurusan: <?= htmlspecialchars($detailKelas['nama_jurusan'] ?? '-') ?>
                    </small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>index.php?url=guru/cetakRaporRombel&kelas_id=<?= $detailKelas['id'] ?>" target="_blank" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3">
                    <i class="bi bi-printer-fill me-1"></i> Cetak E-Rapor Rombel Ini
                </a>
                <a href="<?= BASE_URL ?>index.php?url=kepsek/monitoringWaliKelas" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">
                    <i class="bi bi-x-lg me-1"></i> Tutup Rincian
                </a>
            </div>
        </div>

        <div class="card-body p-3 p-md-4">
            <?php if (empty($detailSiswaList)): ?>
                <div class="alert alert-warning border-0 rounded-4 p-4 text-center mb-0">
                    <i class="bi bi-info-circle-fill text-warning fs-1 d-block mb-2"></i>
                    <h6 class="fw-bold text-dark">Belum Ada Peserta Didik Terdaftar di Rombel Ini</h6>
                    <p class="small text-muted mb-0">Rombel ini belum memiliki siswa aktif yang terdaftar oleh Bagian Tata Usaha / Kurikulum.</p>
                </div>
            <?php else: ?>
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <span class="fw-bold text-dark">Daftar <?= count($detailSiswaList) ?> Peserta Didik Terdaftar:</span>
                    <span class="badge bg-light text-muted border">Supervisi status kehadiran, pengisian catatan rapor, dan administrasi keuangan</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:0.85rem;">
                        <thead class="table-light text-secondary text-uppercase" style="font-size:0.72rem;">
                            <tr>
                                <th class="text-center" style="width: 45px;">No</th>
                                <th>Profil Peserta Didik</th>
                                <th>NIS / NISN</th>
                                <th class="text-center" style="width: 100px;">Gender</th>
                                <th class="text-center" style="width: 120px;">Kehadiran</th>
                                <th class="text-center" style="width: 180px;">Catatan Rapor Wali Kelas</th>
                                <th class="text-center" style="width: 130px;">Status SPP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sNo = 1; foreach ($detailSiswaList as $ds): ?>
                                <tr>
                                    <td class="text-center text-muted fw-bold"><?= $sNo++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php 
                                                $sisAvUrl = resolveWaliAvatarUrl($ds['avatar'] ?? '', $ds['nama_lengkap'] ?? 'Siswa');
                                            ?>
                                            <img src="<?= $sisAvUrl ?>" 
                                                 class="rounded-circle border shadow-xs flex-shrink-0" 
                                                 style="width: 34px; height: 34px; object-fit: cover;" 
                                                 alt="<?= htmlspecialchars($ds['nama_lengkap']) ?>"
                                                 onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($ds['nama_lengkap']) ?>&background=0284c7&color=fff&bold=true';">
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($ds['nama_lengkap']) ?></div>
                                                <small class="text-muted" style="font-size:0.72rem;">
                                                    <?= !empty($ds['no_telepon']) ? htmlspecialchars($ds['no_telepon']) : 'Tanpa No. Kontak' ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-monospace small text-dark fw-semibold"><?= htmlspecialchars($ds['nis'] ?: '-') ?></div>
                                        <small class="text-muted" style="font-size:0.72rem;">NISN: <?= htmlspecialchars($ds['nisn'] ?: '-') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $ds['jenis_kelamin'] === 'L' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' ?> px-2 py-1 rounded-pill small">
                                            <?= $ds['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $ds['presensi_pct'] >= 85 ? 'bg-success' : ($ds['presensi_pct'] >= 70 ? 'bg-warning text-dark' : 'bg-danger') ?> rounded-pill px-2.5 py-1">
                                            <?= $ds['presensi_pct'] ?>%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($ds['catatan_wali'])): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small" title="<?= htmlspecialchars($ds['catatan_wali']) ?>">
                                                <i class="bi bi-check-circle-fill me-1"></i>Sudah Terisi
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2.5 py-1 rounded-pill small">
                                                <i class="bi bi-clock-history me-1"></i>Belum Diisi
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($ds['spp_status'] === 'lunas'): ?>
                                            <span class="badge bg-success text-white px-2.5 py-1 rounded-pill small">
                                                <i class="bi bi-check2 me-1"></i>Lunas (<?= (int)$ds['spp_total_count'] ?>)
                                            </span>
                                        <?php elseif ($ds['spp_status'] === 'tunggakan'): ?>
                                            <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill small" title="<?= (int)$ds['spp_unpaid_count'] ?> tagihan belum lunas">
                                                <i class="bi bi-exclamation-circle me-1"></i>Tunggakan (<?= (int)$ds['spp_unpaid_count'] ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill small" title="Belum ada data tagihan yang dimuat dari sistem pembayaran">
                                                <i class="bi bi-dash-circle me-1 text-secondary"></i>Belum Ada Data
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
