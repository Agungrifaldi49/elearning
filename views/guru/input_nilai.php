<?php require_once ROOT_PATH . 'models/NilaiModel.php'; ?>
<?php require_once ROOT_PATH . 'models/CurriculumModel.php'; ?>
<?php
$userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
$isReadOnly = in_array($userRole, ['kepala sekolah', 'kepsek']);
$formTargetUrl = in_array($userRole, ['administrator', 'admin']) ? 'admin/inputNilai' : 'guru/inputNilai';

// Ambil konfigurasi kurikulum & bobot penilaian aktif rombel kelas terpilih
$currModel = new CurriculumModel();
$kurInfo = $currModel->getActiveKurikulumForRombel((int)$selectedKelasId);
$kompList = $currModel->getKomponenPenilaian($kurInfo['kurikulum_id'] ?? 1);

$wTugas = 20.0; $wQuiz = 20.0; $wUts = 30.0; $wUas = 30.0;
$labelTugas = 'Tugas Mandiri'; $labelQuiz = 'Kuis / Formatif'; $labelUts = 'Sumatif Tengah Semester (STS)'; $labelUas = 'Sumatif Akhir Semester (SAS)';
foreach ($kompList as $kp) {
    $code = strtolower(trim($kp['kode_komponen']));
    $b = (float)$kp['bobot_persen'];
    $n = trim($kp['nama_komponen']);
    if (strpos($code, 'tugas') !== false || strpos($code, 'formatif') !== false || strpos($code, 'tp') !== false) {
        $wTugas = $b; $labelTugas = $n;
    } elseif (strpos($code, 'quiz') !== false || strpos($code, 'kuis') !== false || strpos($code, 'teori') !== false || strpos($code, 'sumatif_lm') !== false) {
        $wQuiz = $b; $labelQuiz = $n;
    } elseif (strpos($code, 'uts') !== false || strpos($code, 'sts') !== false || strpos($code, 'praktik') !== false) {
        $wUts = $b; $labelUts = $n;
    } elseif (strpos($code, 'uas') !== false || strpos($code, 'sas') !== false || strpos($code, 'sumatif_akhir') !== false) {
        $wUas = $b; $labelUas = $n;
    }
}
?>
<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* Modern E-Rapor Leger Styling */
.leger-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 25px -4px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.leger-filter-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
}

.hero-rombel-banner {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 16px;
    color: #ffffff;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 8px 24px -6px rgba(15, 23, 42, 0.25);
}

.hero-weight-badge {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    padding: 6px 14px;
    backdrop-filter: blur(8px);
    transition: all 0.2s ease;
}
.hero-weight-badge:hover {
    background: rgba(255, 255, 255, 0.14);
    transform: translateY(-1px);
}

.table-leger-wrapper {
    max-height: 68vh;
    overflow-y: auto;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
}

.table-leger {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.table-leger thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f8fafc;
    color: #475569;
    font-size: 0.76rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 14px;
    border-bottom: 2px solid #e2e8f0;
    vertical-align: middle;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.table-leger tbody td {
    padding: 10px 14px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.88rem;
    transition: background-color 0.15s ease;
}

.table-leger tbody tr:hover td {
    background-color: #f8fbff;
}

.student-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.82rem;
    color: #ffffff;
    background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
    box-shadow: 0 2px 6px rgba(59, 130, 246, 0.25);
    flex-shrink: 0;
}

.score-input {
    width: 82px;
    height: 38px;
    font-family: var(--bs-font-monospace, monospace);
    font-weight: 700;
    font-size: 0.95rem;
    text-align: center;
    border-radius: 10px;
    border: 1.5px solid #cbd5e1;
    background: #f8fafc;
    color: #0f172a;
    transition: all 0.2s ease;
    margin: 0 auto;
}
.score-input:hover {
    border-color: #94a3b8;
    background: #ffffff;
}
.score-input:focus {
    background: #ffffff;
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
    transform: scale(1.02);
}

.pill-nilai-akhir {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 60px;
    padding: 5px 12px;
    border-radius: 10px;
    font-family: var(--bs-font-monospace, monospace);
    font-weight: 800;
    font-size: 0.95rem;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    box-shadow: 0 1px 2px rgba(29, 78, 216, 0.05);
}

.pill-predikat {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 9999px;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.2px;
    white-space: nowrap;
}

.pill-predikat-a {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}
.pill-predikat-b {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}
.pill-predikat-c {
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
}
.pill-predikat-d {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.btn-edit-inline {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #3b82f6;
    border-radius: 10px;
    padding: 5px 12px;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.btn-edit-inline:hover {
    background: #3b82f6;
    color: #ffffff;
    border-color: #3b82f6;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(59, 130, 246, 0.25);
}

.stat-chip {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>

<main class="main-content px-3 px-md-4 py-3">
<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                    <i class="bi bi-mortarboard-fill me-1"></i>Modul E-Rapor Digital
                </span>
                <span class="text-muted small">•</span>
                <span class="text-muted small fw-medium">Kurikulum Merdeka SMK</span>
            </div>
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-journal-check text-primary"></i>
                <?= $isReadOnly ? 'Rekap Leger & Nilai E-Rapor Siswa' : 'Input & Edit Nilai E-Rapor Siswa' ?>
            </h4>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>index.php?url=guru/asesmen" class="btn btn-outline-danger shadow-xs rounded-3 fw-bold d-inline-flex align-items-center gap-1.5 px-3 py-2">
                <i class="bi bi-bullseye"></i> Rekap Ketercapaian TP & KKTP
            </a>
            <?php if (!$isReadOnly): ?>
                <button class="btn btn-primary shadow-sm rounded-3 fw-bold d-inline-flex align-items-center gap-1.5 px-3.5 py-2" data-bs-toggle="modal" data-bs-target="#modalSingleSave">
                    <i class="bi bi-person-plus-fill"></i> Input Nilai 1 Siswa
                </button>
            <?php else: ?>
                <span class="badge bg-secondary rounded-pill px-3 py-2 shadow-xs fs-6 d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-eye-fill"></i> Mode Lihat Saja (Kepala Sekolah)
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Bar Kelas & Mapel Ajar -->
    <div class="leger-filter-card p-3 p-md-4 mb-3">
        <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-3 align-items-end">
            <input type="hidden" name="url" value="<?= $formTargetUrl ?>">

            <div class="col-12 col-md-5">
                <label class="form-label small fw-bold text-dark mb-1.5">
                    <i class="bi bi-door-open-fill text-primary me-1.5"></i>Pilih Rombel Kelas Target
                </label>
                <select name="kelas_id" class="form-select rounded-3 fw-semibold py-2" onchange="this.form.submit()">
                    <?php if (empty($kelasList)): ?>
                        <option value="">-- Belum Ada Kelas Terdaftar --</option>
                    <?php else: ?>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= $selectedKelasId == $k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-12 col-md-5">
                <label class="form-label small fw-bold text-dark mb-1.5">
                    <i class="bi bi-book-half text-success me-1.5"></i>Pilih Mata Pelajaran Ajar
                </label>
                <select name="mapel_id" class="form-select rounded-3 fw-semibold py-2" onchange="this.form.submit()">
                    <?php if (empty($mapelList)): ?>
                        <option value="">-- Belum Ada Mapel Terdaftar --</option>
                    <?php else: ?>
                        <?php foreach ($mapelList as $mp): ?>
                            <option value="<?= $mp['id'] ?>" <?= $selectedMapelId == $mp['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mp['nama_mapel']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-3 shadow-xs d-flex align-items-center justify-content-center gap-1.5">
                    <i class="bi bi-funnel-fill"></i> Tampilkan Leger
                </button>
            </div>
        </form>
    </div>

    <!-- Hero Information Banner -->
    <?php if (!empty($selectedKelasInfo)): ?>
        <div class="hero-rombel-banner mb-3">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-6">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-25 border border-primary border-opacity-50 text-white rounded-4 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                            <i class="bi bi-award-fill fs-3 text-primary-subtle"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <h5 class="fw-bold mb-0 text-white"><?= htmlspecialchars($selectedKelasInfo['nama_kelas']) ?></h5>
                                <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-20 rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">
                                    <?= htmlspecialchars($selectedKelasInfo['nama_jurusan'] ?? 'Umum') ?>
                                </span>
                                <span class="badge bg-success bg-opacity-25 text-success-subtle border border-success border-opacity-30 rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">
                                    <i class="bi bi-people-fill me-1"></i><?= count($siswaList) ?> Siswa Terdaftar
                                </span>
                            </div>
                            <small class="text-white-50">
                                Mata Pelajaran: <strong class="text-white"><?= htmlspecialchars(array_column($mapelList, 'nama_mapel', 'id')[$selectedMapelId] ?? 'Mapel Ajar') ?></strong>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="d-flex align-items-center justify-content-lg-end gap-2 flex-wrap">
                        <div class="hero-weight-badge text-center">
                            <span class="d-block text-white-50 small" style="font-size: 0.68rem;"><?= htmlspecialchars($labelTugas) ?></span>
                            <strong class="text-white font-monospace"><?= $wTugas ?>%</strong>
                        </div>
                        <div class="hero-weight-badge text-center">
                            <span class="d-block text-white-50 small" style="font-size: 0.68rem;"><?= htmlspecialchars($labelQuiz) ?></span>
                            <strong class="text-info font-monospace"><?= $wQuiz ?>%</strong>
                        </div>
                        <div class="hero-weight-badge text-center">
                            <span class="d-block text-white-50 small" style="font-size: 0.68rem;"><?= htmlspecialchars($labelUts) ?></span>
                            <strong class="text-warning font-monospace"><?= $wUts ?>%</strong>
                        </div>
                        <div class="hero-weight-badge text-center">
                            <span class="d-block text-white-50 small" style="font-size: 0.68rem;"><?= htmlspecialchars($labelUas) ?></span>
                            <strong class="text-danger font-monospace"><?= $wUas ?>%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Summary Statistics & Quick Filter -->
    <?php if (!empty($siswaList)): ?>
        <div class="row g-2 mb-3 align-items-center">
            <div class="col-12 col-md-6 col-lg-7">
                <div class="d-flex gap-2 flex-wrap">
                    <div class="stat-chip py-1.5 px-3">
                        <i class="bi bi-calculator-fill text-primary fs-5"></i>
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.68rem; line-height: 1;">Rata-Rata Kelas</small>
                            <strong class="text-dark font-monospace" id="statRataRata">0.0</strong>
                        </div>
                    </div>
                    <div class="stat-chip py-1.5 px-3">
                        <i class="bi bi-patch-check-fill text-success fs-5"></i>
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.68rem; line-height: 1;">Siswa Tuntas (&ge;75)</small>
                            <strong class="text-success font-monospace" id="statTuntas">0 Siswa</strong>
                        </div>
                    </div>
                    <div class="stat-chip py-1.5 px-3">
                        <i class="bi bi-arrow-repeat text-danger fs-5"></i>
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.68rem; line-height: 1;">Perlu Bimbingan (&lt;75)</small>
                            <strong class="text-danger font-monospace" id="statRemedial">0 Siswa</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-5 text-md-end">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="searchSiswaInput" class="form-control border-start-0 rounded-end-3 py-2" placeholder="Cari nama siswa atau NIS cepat...">
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Matrix Batch Table Form -->
    <div class="leger-card p-3 p-md-4 mb-4">
        <form id="formBatchNilai" action="<?= BASE_URL ?>index.php?url=<?= $formTargetUrl ?>&kelas_id=<?= $selectedKelasId ?>&mapel_id=<?= $selectedMapelId ?>" method="POST">
            <?= Security::csrfField() ?>
            <input type="hidden" name="action" value="batch_save">

            <div class="table-leger-wrapper">
                <table class="table table-leger align-middle" id="tableNilai">
                    <thead>
                        <tr>
                            <th style="width: 48px;" class="text-center">No</th>
                            <th style="min-width: 270px;">Identitas Siswa</th>
                            <th style="width: 110px;" class="text-center">
                                <div><?= htmlspecialchars($labelTugas) ?></div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size: 0.68rem;"><?= $wTugas ?>%</span>
                            </th>
                            <th style="width: 110px;" class="text-center">
                                <div><?= htmlspecialchars($labelQuiz) ?></div>
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace" style="font-size: 0.68rem;"><?= $wQuiz ?>%</span>
                            </th>
                            <th style="width: 110px;" class="text-center">
                                <div><?= htmlspecialchars($labelUts) ?></div>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill font-monospace" style="font-size: 0.68rem;"><?= $wUts ?>%</span>
                            </th>
                            <th style="width: 110px;" class="text-center">
                                <div><?= htmlspecialchars($labelUas) ?></div>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace" style="font-size: 0.68rem;"><?= $wUas ?>%</span>
                            </th>
                            <th style="width: 105px;" class="text-center">Nilai Akhir</th>
                            <th style="width: 140px;" class="text-center">Predikat</th>
                            <?php if (!$isReadOnly): ?>
                                <th style="width: 85px;" class="text-center">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="tableNilaiBody">
                        <?php if (empty($siswaList)): ?>
                            <tr>
                                <td colspan="<?= $isReadOnly ? '8' : '9' ?>" class="text-center py-5 text-muted">
                                    <i class="bi bi-people text-muted opacity-50 d-block mb-2" style="font-size: 2.5rem;"></i>
                                    <h6 class="fw-bold text-dark mb-1">Belum Ada Siswa Terdaftar</h6>
                                    <p class="small text-muted mb-0">Silakan pilih rombel kelas dan mata pelajaran pada filter di atas untuk memulai pengisian nilai.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($siswaList as $i => $s): 
                                $nData = $existingNilai[$s['id']] ?? [];
                                $nTugas = min(100.0, max(0.0, (float)($nData['nilai_tugas'] ?? 0)));
                                $nQuiz  = min(100.0, max(0.0, (float)($nData['nilai_quiz'] ?? 0)));
                                $nUts   = min(100.0, max(0.0, (float)($nData['nilai_uts'] ?? 0)));
                                $nUas   = min(100.0, max(0.0, (float)($nData['nilai_uas'] ?? 0)));

                                $wTRow = $wTugas / 100.0;
                                $wQRow = $wQuiz / 100.0;
                                $wURow = $wUts / 100.0;
                                $wARow = $wUas / 100.0;

                                $weightsRow = [];
                                if ($nTugas > 0) $weightsRow[] = ['val' => $nTugas, 'w' => $wTRow];
                                if ($nQuiz > 0)  $weightsRow[] = ['val' => $nQuiz,  'w' => $wQRow];
                                if ($nUts > 0)   $weightsRow[] = ['val' => $nUts,   'w' => $wURow];
                                if ($nUas > 0)   $weightsRow[] = ['val' => $nUas,   'w' => $wARow];

                                if (!empty($weightsRow)) {
                                    $sumValR = 0; $sumWR = 0;
                                    foreach ($weightsRow as $wr) {
                                        $sumValR += ($wr['val'] * $wr['w']);
                                        $sumWR += $wr['w'];
                                    }
                                    $nAkhir = ($sumWR > 0) ? round($sumValR / $sumWR, 2) : 0.00;
                                } else {
                                    $nAkhir = ($nTugas * $wTRow) + ($nQuiz * $wQRow) + ($nUts * $wURow) + ($nUas * $wARow);
                                }
                                $nAkhir = min(100.0, max(0.0, (float)$nAkhir));

                                $predikat = NilaiModel::getPredikat((float)$nAkhir);

                                // Generate initials for avatar circle
                                $partsName = explode(' ', trim($s['nama_lengkap']));
                                $initials = strtoupper(substr($partsName[0] ?? 'S', 0, 1) . substr($partsName[1] ?? '', 0, 1));
                                if (strlen($initials) < 2) {
                                    $initials = strtoupper(substr($s['nama_lengkap'], 0, 2));
                                }

                                $searchData = strtolower($s['nama_lengkap'] . ' ' . ($s['nis'] ?? '') . ' ' . ($s['nisn'] ?? ''));
                            ?>
                                <tr data-search="<?= htmlspecialchars($searchData) ?>" id="rowSiswa<?= $s['id'] ?>">
                                    <td class="text-center text-muted font-monospace fw-semibold" style="font-size: 0.82rem;"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="student-avatar"><?= htmlspecialchars($initials) ?></div>
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-dark text-truncate" title="<?= htmlspecialchars($s['nama_lengkap']) ?>">
                                                    <?= htmlspecialchars($s['nama_lengkap']) ?>
                                                </div>
                                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                    <span class="badge bg-light text-secondary border font-monospace px-1.5 py-0.5" style="font-size: 0.68rem;">
                                                        NIS: <?= htmlspecialchars($s['nis'] ?? '-') ?>
                                                    </span>
                                                    <span class="badge bg-light text-secondary border px-1.5 py-0.5" style="font-size: 0.68rem;">
                                                        <?= htmlspecialchars($s['nama_jurusan'] ?? '-') ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <?php if ($isReadOnly): ?>
                                        <td class="text-center font-monospace fw-bold text-dark"><?= number_format((float)$nTugas, 1) ?></td>
                                        <td class="text-center font-monospace fw-bold text-dark"><?= number_format((float)$nQuiz, 1) ?></td>
                                        <td class="text-center font-monospace fw-bold text-dark"><?= number_format((float)$nUts, 1) ?></td>
                                        <td class="text-center font-monospace fw-bold text-dark"><?= number_format((float)$nUas, 1) ?></td>
                                    <?php else: ?>
                                        <td class="text-center">
                                            <input type="number" name="nilai[<?= $s['id'] ?>][tugas]" class="form-control score-input" data-siswa="<?= $s['id'] ?>" min="0" max="100" step="0.5" value="<?= $nTugas ?>" onchange="calcRow(<?= $s['id'] ?>)" oninput="calcRow(<?= $s['id'] ?>)">
                                        </td>
                                        <td class="text-center">
                                            <input type="number" name="nilai[<?= $s['id'] ?>][quiz]" class="form-control score-input" data-siswa="<?= $s['id'] ?>" min="0" max="100" step="0.5" value="<?= $nQuiz ?>" onchange="calcRow(<?= $s['id'] ?>)" oninput="calcRow(<?= $s['id'] ?>)">
                                        </td>
                                        <td class="text-center">
                                            <input type="number" name="nilai[<?= $s['id'] ?>][uts]" class="form-control score-input" data-siswa="<?= $s['id'] ?>" min="0" max="100" step="0.5" value="<?= $nUts ?>" onchange="calcRow(<?= $s['id'] ?>)" oninput="calcRow(<?= $s['id'] ?>)">
                                        </td>
                                        <td class="text-center">
                                            <input type="number" name="nilai[<?= $s['id'] ?>][uas]" class="form-control score-input" data-siswa="<?= $s['id'] ?>" min="0" max="100" step="0.5" value="<?= $nUas ?>" onchange="calcRow(<?= $s['id'] ?>)" oninput="calcRow(<?= $s['id'] ?>)">
                                        </td>
                                    <?php endif; ?>
                                    <td class="text-center">
                                        <span class="pill-nilai-akhir" id="valAkhir<?= $s['id'] ?>">
                                            <?= number_format((float)$nAkhir, 1) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="pill-predikat pill-predikat-<?= strtolower($predikat['grade']) ?>" id="badgePredikat<?= $s['id'] ?>">
                                            <?php if ($predikat['grade'] === 'A'): ?>
                                                <i class="bi bi-award-fill"></i>
                                            <?php elseif ($predikat['grade'] === 'B'): ?>
                                                <i class="bi bi-hand-thumbs-up-fill"></i>
                                            <?php elseif ($predikat['grade'] === 'C'): ?>
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                            <?php else: ?>
                                                <i class="bi bi-x-circle-fill"></i>
                                            <?php endif; ?>
                                            <?= $predikat['grade'] ?> (<?= $predikat['label'] ?>)
                                        </span>
                                    </td>
                                    <?php if (!$isReadOnly): ?>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-edit-inline" onclick="openEditModal(<?= (int)$s['id'] ?>, <?= htmlspecialchars(json_encode($s['nama_lengkap']), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode('NIS: ' . ($s['nis'] ?? '-') . ' | Jurusan: ' . ($s['nama_jurusan'] ?? '-')), ENT_QUOTES, 'UTF-8') ?>, <?= (float)$nTugas ?>, <?= (float)$nQuiz ?>, <?= (float)$nUts ?>, <?= (float)$nUas ?>)">
                                                <i class="bi bi-pencil-square me-1"></i>Edit
                                            </button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!$isReadOnly && !empty($siswaList)): ?>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top flex-wrap gap-2">
                    <div class="text-muted small">
                        <i class="bi bi-info-circle-fill text-primary me-1"></i>
                        Nilai Akhir & Predikat terhitung otomatis secara presisi menggunakan formula pembobotan aktif.
                    </div>
                    <button type="submit" class="btn btn-success btn-lg px-4 py-2.5 fw-bold shadow rounded-pill d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle fs-5"></i> Simpan Seluruh E-Rapor Kelas Ini
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>

</div>
</main>

<?php if (!$isReadOnly): ?>
    <!-- Single Dynamic Reusable Modal Edit Nilai Per Siswa -->
    <div class="modal fade" id="modalEditNilai" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 bg-primary text-white p-3.5" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                    <h6 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square"></i>Edit Nilai E-Rapor Siswa
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=<?= $formTargetUrl ?>&kelas_id=<?= $selectedKelasId ?>&mapel_id=<?= $selectedMapelId ?>" method="POST">
                    <div class="modal-body p-4 bg-light">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="single_save">
                        <input type="hidden" name="siswa_id" id="editModalSiswaId" value="">
                        <input type="hidden" name="mapel_id" value="<?= $selectedMapelId ?>">

                        <div class="p-3 bg-white rounded-3 border mb-3 shadow-xs">
                            <small class="text-muted d-block fw-semibold">Siswa Target:</small>
                            <h6 class="fw-bold text-dark mb-0" id="editModalNamaSiswa">-</h6>
                            <small class="text-muted" id="editModalInfoSiswa">-</small>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-dark"><?= htmlspecialchars($labelTugas) ?> (<?= $wTugas ?>%)</label>
                                <input type="number" name="nilai_tugas" id="editModalTugas" class="form-control rounded-3 font-monospace fw-bold text-center" min="0" max="100" step="0.5" value="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-dark"><?= htmlspecialchars($labelQuiz) ?> (<?= $wQuiz ?>%)</label>
                                <input type="number" name="nilai_quiz" id="editModalQuiz" class="form-control rounded-3 font-monospace fw-bold text-center" min="0" max="100" step="0.5" value="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-dark"><?= htmlspecialchars($labelUts) ?> (<?= $wUts ?>%)</label>
                                <input type="number" name="nilai_uts" id="editModalUts" class="form-control rounded-3 font-monospace fw-bold text-center" min="0" max="100" step="0.5" value="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-dark"><?= htmlspecialchars($labelUas) ?> (<?= $wUas ?>%)</label>
                                <input type="number" name="nilai_uas" id="editModalUas" class="form-control rounded-3 font-monospace fw-bold text-center" min="0" max="100" step="0.5" value="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-3 justify-content-between bg-white border-top">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5">
                            <i class="bi bi-floppy-fill"></i> Simpan Perubahan Nilai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Single Student Input -->
    <div class="modal fade" id="modalSingleSave" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold modal-title d-flex align-items-center gap-2">
                        <i class="bi bi-person-plus-fill text-primary"></i>Input Nilai 1 Siswa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=<?= $formTargetUrl ?>&kelas_id=<?= $selectedKelasId ?>&mapel_id=<?= $selectedMapelId ?>" method="POST">
                    <div class="modal-body">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="single_save">

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pilih Siswa</label>
                            <select name="siswa_id" class="form-select rounded-3" required>
                                <option value="">-- Pilih Siswa Rombel --</option>
                                <?php foreach ($siswaList as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama_lengkap'] . ' (' . ($s['nama_kelas'] ?? '') . ')') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Mata Pelajaran</label>
                            <select name="mapel_id" class="form-select rounded-3" required>
                                <?php foreach ($mapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>" <?= $selectedMapelId == $mp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold"><?= htmlspecialchars($labelTugas) ?> (<?= $wTugas ?>%)</label>
                                <input type="number" name="nilai_tugas" class="form-control rounded-3 font-monospace text-center fw-bold" min="0" max="100" step="0.5" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold"><?= htmlspecialchars($labelQuiz) ?> (<?= $wQuiz ?>%)</label>
                                <input type="number" name="nilai_quiz" class="form-control rounded-3 font-monospace text-center fw-bold" min="0" max="100" step="0.5" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold"><?= htmlspecialchars($labelUts) ?> (<?= $wUts ?>%)</label>
                                <input type="number" name="nilai_uts" class="form-control rounded-3 font-monospace text-center fw-bold" min="0" max="100" step="0.5" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold"><?= htmlspecialchars($labelUas) ?> (<?= $wUas ?>%)</label>
                                <input type="number" name="nilai_uas" class="form-control rounded-3 font-monospace text-center fw-bold" min="0" max="100" step="0.5" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 justify-content-between">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Simpan Nilai</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function openEditModal(id, nama, info, tugas, quiz, uts, uas) {
    var sIdInput = document.getElementById('editModalSiswaId');
    if (sIdInput) sIdInput.value = id;
    var namaEl = document.getElementById('editModalNamaSiswa');
    if (namaEl) namaEl.textContent = nama;
    var infoEl = document.getElementById('editModalInfoSiswa');
    if (infoEl) infoEl.textContent = info;
    var tInput = document.getElementById('editModalTugas');
    if (tInput) tInput.value = tugas;
    var qInput = document.getElementById('editModalQuiz');
    if (qInput) qInput.value = quiz;
    var uInput = document.getElementById('editModalUts');
    if (uInput) uInput.value = uts;
    var aInput = document.getElementById('editModalUas');
    if (aInput) aInput.value = uas;

    var modalEl = document.getElementById('modalEditNilai');
    if (modalEl) {
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

function calcRow(siswaId) {
    const inputs = document.querySelectorAll(`input[data-siswa="${siswaId}"]`);
    if (inputs.length < 4) return;

    let t = parseFloat(inputs[0].value) || 0;
    let q = parseFloat(inputs[1].value) || 0;
    let uts = parseFloat(inputs[2].value) || 0;
    let uas = parseFloat(inputs[3].value) || 0;

    if (t > 100) { t = 100; inputs[0].value = 100; }
    if (q > 100) { q = 100; inputs[1].value = 100; }
    if (uts > 100) { uts = 100; inputs[2].value = 100; }
    if (uas > 100) { uas = 100; inputs[3].value = 100; }

    const wT = <?= (float)$wTugas / 100.0 ?>;
    const wQ = <?= (float)$wQuiz / 100.0 ?>;
    const wU = <?= (float)$wUts / 100.0 ?>;
    const wA = <?= (float)$wUas / 100.0 ?>;

    let sumVal = 0, sumW = 0;
    if (t > 0)   { sumVal += (t * wT); sumW += wT; }
    if (q > 0)   { sumVal += (q * wQ); sumW += wQ; }
    if (uts > 0) { sumVal += (uts * wU); sumW += wU; }
    if (uas > 0) { sumVal += (uas * wA); sumW += wA; }

    let akhir = sumW > 0 ? (sumVal / sumW) : ((t * wT) + (q * wQ) + (uts * wU) + (uas * wA));
    if (akhir > 100) akhir = 100;

    const valElem = document.getElementById('valAkhir' + siswaId);
    const badgeElem = document.getElementById('badgePredikat' + siswaId);

    if (valElem) {
        valElem.textContent = akhir.toFixed(1);
    }

    if (badgeElem) {
        if (akhir >= 88) {
            badgeElem.className = 'pill-predikat pill-predikat-a';
            badgeElem.innerHTML = '<i class="bi bi-award-fill"></i> A (Sangat Baik)';
        } else if (akhir >= 78) {
            badgeElem.className = 'pill-predikat pill-predikat-b';
            badgeElem.innerHTML = '<i class="bi bi-hand-thumbs-up-fill"></i> B (Baik)';
        } else if (akhir >= 68) {
            badgeElem.className = 'pill-predikat pill-predikat-c';
            badgeElem.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> C (Cukup)';
        } else {
            badgeElem.className = 'pill-predikat pill-predikat-d';
            badgeElem.innerHTML = '<i class="bi bi-x-circle-fill"></i> D (Kurang)';
        }
    }

    updateSummaryStats();
}

function updateSummaryStats() {
    const valElems = document.querySelectorAll('.pill-nilai-akhir');
    if (!valElems || valElems.length === 0) return;

    let total = 0;
    let tuntasCount = 0;
    let remedialCount = 0;
    let count = valElems.length;

    valElems.forEach(el => {
        let val = parseFloat(el.textContent) || 0;
        total += val;
        if (val >= 75) {
            tuntasCount++;
        } else {
            remedialCount++;
        }
    });

    const avg = count > 0 ? (total / count).toFixed(1) : '0.0';
    const statRataEl = document.getElementById('statRataRata');
    const statTuntasEl = document.getElementById('statTuntas');
    const statRemedialEl = document.getElementById('statRemedial');

    if (statRataEl) statRataEl.textContent = avg;
    if (statTuntasEl) statTuntasEl.textContent = tuntasCount + ' Siswa';
    if (statRemedialEl) statRemedialEl.textContent = remedialCount + ' Siswa';
}

// Live Search Filter for Table
document.getElementById('searchSiswaInput')?.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#tableNilaiBody tr[data-search]');
    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        row.style.display = text.includes(q) ? '' : 'none';
    });
});

// Initialize summary stats on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSummaryStats();
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
