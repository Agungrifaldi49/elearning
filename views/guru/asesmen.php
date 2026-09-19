<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>
<?php
if (!function_exists('renderRombelOptgroupsHtml')) {
    function renderRombelOptgroupsHtml($rombelByJurusan, $selectedId, $showAllOption = false) {
        $html = '';
        if ($showAllOption) {
            $html .= '<option value="">-- Semua Rombel / Kelas --</option>';
        }
        if (!empty($rombelByJurusan) && is_array($rombelByJurusan)) {
            foreach ($rombelByJurusan as $groupTitle => $classes) {
                $html .= '<optgroup label="' . htmlspecialchars($groupTitle) . '">';
                foreach ($classes as $r) {
                    $sel = ($selectedId == $r['id']) ? ' selected' : '';
                    $rombelName = !empty($r['nama_kelas']) ? $r['nama_kelas'] : (!empty($r['nama_rombel']) ? $r['nama_rombel'] : 'Kelas ' . $r['tingkat']);
                    $jurBadge = !empty($r['kode_jurusan']) ? ' [' . htmlspecialchars($r['kode_jurusan']) . ']' : '';
                    $html .= '<option value="' . $r['id'] . '"' . $sel . '>' . htmlspecialchars($rombelName) . ' (Tingkat ' . htmlspecialchars($r['tingkat']) . ')' . $jurBadge . '</option>';
                }
                $html .= '</optgroup>';
            }
        }
        return $html;
    }
}
?>

<main class="main-content">
<div class="container-fluid px-3 px-md-4 py-4">

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?url=guru/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Asesmen & KKTP</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-bullseye text-danger"></i>
                <span>Asesmen, Penilaian TP & Ketercapaian (KKTP)</span>
            </h4>
            <p class="text-muted small mb-0">
                Pipeline: <strong>CP &rarr; TP &rarr; KKTP &rarr; ASESMEN &rarr; NILAI &rarr; STATUS KETERCAPAIAN (1 / 0)</strong> dengan Histori Nilai & Remedial.
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>index.php?url=guru/cptp" class="btn btn-outline-primary shadow-sm fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2">
                <i class="bi bi-card-checklist fs-5"></i>
                <span>Kelola CP & TP</span>
            </a>
            <button type="button" class="btn btn-danger shadow-sm fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAddAsesmen">
                <i class="bi bi-plus-circle-fill fs-5"></i>
                <span>+ Buat Asesmen Baru</span>
            </button>
        </div>
    </div>

    <!-- Flash Alerts -->
    <?php if (class_exists('FlashHelper')): ?>
        <?php if (FlashHelper::hasSuccess()): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-xs mb-4 border-0 border-start border-success border-4" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <div><?= FlashHelper::getSuccess() ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (FlashHelper::hasError()): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-xs mb-4 border-0 border-start border-danger border-4" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                    <div><?= FlashHelper::getError() ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills nav-fill bg-white p-2 rounded-4 shadow-sm border mb-4 gap-1">
        <li class="nav-item">
            <a class="nav-link rounded-3 fw-bold <?= ($activeTab === 'asesmen') ? 'active bg-danger text-white' : 'text-dark' ?>" 
               href="<?= BASE_URL ?>index.php?url=guru/asesmen&tab=asesmen&rombel_id=<?= $filterRombelId ?>&mapel_id=<?= $filterMapelId ?>">
                <i class="bi bi-journal-check me-1.5"></i> 1. Daftar Asesmen
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-3 fw-bold <?= ($activeTab === 'penilaian') ? 'active bg-danger text-white' : 'text-dark' ?>" 
               href="<?= BASE_URL ?>index.php?url=guru/asesmen&tab=penilaian&asesmen_id=<?= $selectedAsesmenId ?>&rombel_id=<?= $filterRombelId ?>&mapel_id=<?= $filterMapelId ?>">
                <i class="bi bi-pencil-square me-1.5"></i> 2. Lembar Nilai & Remedial Per-TP
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-3 fw-bold <?= ($activeTab === 'rekap_kelas') ? 'active bg-danger text-white' : 'text-dark' ?>" 
               href="<?= BASE_URL ?>index.php?url=guru/asesmen&tab=rekap_kelas&rombel_id=<?= $filterRombelId ?>&mapel_id=<?= $filterMapelId ?>">
                <i class="bi bi-bar-chart-fill me-1.5"></i> 3. Rekap Ketercapaian Kelas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-3 fw-bold <?= ($activeTab === 'rekap_siswa') ? 'active bg-danger text-white' : 'text-dark' ?>" 
               href="<?= BASE_URL ?>index.php?url=guru/asesmen&tab=rekap_siswa&rombel_id=<?= $filterRombelId ?>&mapel_id=<?= $filterMapelId ?>&siswa_id=<?= $selectedSiswaId ?>">
                <i class="bi bi-person-lines-fill me-1.5"></i> 4. Profil Ketercapaian Siswa & Draf Rapor
            </a>
        </li>
    </ul>

    <!-- ======================================================================= -->
    <!-- TAB 1: DAFTAR ASESMEN -->
    <!-- ======================================================================= -->
    <?php if ($activeTab === 'asesmen'): ?>
        <!-- Filter Toolbar -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-body p-3.5">
                <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-3 align-items-end">
                    <input type="hidden" name="url" value="guru/asesmen">
                    <input type="hidden" name="tab" value="asesmen">

                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-bold text-secondary mb-1">Rombel / Kelas</label>
                        <select name="rombel_id" class="form-select rounded-3" onchange="this.form.submit()">
                            <?= renderRombelOptgroupsHtml($rombelByJurusan ?? [], $filterRombelId, true) ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-bold text-secondary mb-1">Mata Pelajaran</label>
                        <select name="mapel_id" class="form-select rounded-3" onchange="this.form.submit()">
                            <option value="">-- Semua Mapel --</option>
                            <?php foreach ($teacherMapelList as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= ($filterMapelId == $m['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nama_mapel']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-outline-secondary w-100 rounded-3 fw-semibold">
                            <i class="bi bi-filter"></i> Terapkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Asesmen -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                    <i class="bi bi-list-task text-danger"></i> Daftar Asesmen Pembelajaran Aktif
                </div>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill font-monospace fw-bold">
                    Total: <?= count($asesmenList) ?> Asesmen
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary small fw-bold text-uppercase">
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Nama Asesmen</th>
                                <th>Kelas / Rombel</th>
                                <th>Mata Pelajaran</th>
                                <th class="text-center">Jenis & Tanggal</th>
                                <th class="text-center">TP Diukur</th>
                                <th class="text-center">Siswa Dinilai</th>
                                <th class="text-center" style="width: 160px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($asesmenList)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        Belum ada asesmen yang dibuat untuk filter ini.<br>
                                        <button class="btn btn-sm btn-danger mt-2" data-bs-toggle="modal" data-bs-target="#modalAddAsesmen">
                                            + Buat Asesmen Pertama Sekarang
                                        </button>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($asesmenList as $a): ?>
                                    <tr>
                                        <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($a['nama_asesmen']) ?></div>
                                            <div class="small text-muted">Bobot: <?= $a['bobot'] ?> | Maks: <?= $a['nilai_maksimum'] ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                                                <?= htmlspecialchars($a['nama_kelas'] ?? ($a['nama_rombel'] ?? 'Rombel')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($a['nama_mapel']) ?></div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-secondary text-capitalize rounded-pill px-2.5 py-1 mb-1">
                                                <?= htmlspecialchars($a['jenis_asesmen']) ?>
                                            </span>
                                            <div class="small text-muted font-monospace"><?= date('d/m/Y', strtotime($a['tanggal'])) ?></div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1 rounded-pill fw-bold">
                                                <i class="bi bi-check2-all me-1"></i><?= (int)($a['total_tp'] ?: 1) ?> TP
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-bold">
                                                <?= (int)$a['total_siswa_dinilai'] ?> Siswa
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= BASE_URL ?>index.php?url=guru/asesmen&tab=penilaian&asesmen_id=<?= $a['id'] ?>" 
                                               class="btn btn-sm btn-primary rounded-3 px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs">
                                                <i class="bi bi-pencil-fill"></i> Nilai TP
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ======================================================================= -->
    <!-- TAB 2: LEMBAR PENILAIAN SISWA PER-TP & REMEDIAL -->
    <!-- ======================================================================= -->
    <?php if ($activeTab === 'penilaian'): ?>
        <?php if (!$matrixData || empty($matrixData['asesmen'])): ?>
            <div class="alert alert-warning rounded-4 shadow-sm p-4 text-center">
                <i class="bi bi-exclamation-circle-fill fs-2 text-warning d-block mb-2"></i>
                <h5 class="fw-bold text-dark">Pilih Asesmen Terlebih Dahulu</h5>
                <p class="text-muted small mb-3">Silakan pilih asesmen yang ingin dinilai dari daftar asesmen.</p>
                <a href="<?= BASE_URL ?>index.php?url=guru/asesmen&tab=asesmen" class="btn btn-danger px-4 rounded-3">
                    &larr; Kembali ke Daftar Asesmen
                </a>
            </div>
        <?php else: ?>
            <?php 
                $asesmen = $matrixData['asesmen'];
                $tps = $matrixData['tujuan_pembelajaran'];
                $siswaList = $matrixData['siswa_list'];
                $scores = $matrixData['scores'];
            ?>

            <!-- Asesmen Header Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3.5">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="badge bg-danger text-white rounded-pill px-3 py-1 font-monospace">
                                <?= strtoupper($asesmen['jenis_asesmen']) ?>
                            </span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                <?= htmlspecialchars($asesmen['nama_kelas'] ?? ($asesmen['nama_rombel'] ?? 'Rombel')) ?>
                            </span>
                            <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                                <?= htmlspecialchars($asesmen['nama_mapel']) ?>
                            </span>
                        </div>
                        <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($asesmen['nama_asesmen']) ?></h4>
                        <div class="text-muted small mt-1">
                            Tanggal: <strong><?= date('d M Y', strtotime($asesmen['tanggal'])) ?></strong> | Nilai Maksimum: <strong><?= $asesmen['nilai_maksimum'] ?></strong>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <select class="form-select rounded-3" style="max-width: 250px;" onchange="location.href='<?= BASE_URL ?>index.php?url=guru/asesmen&tab=penilaian&asesmen_id=' + this.value">
                            <?php foreach ($asesmenList as $al): ?>
                                <option value="<?= $al['id'] ?>" <?= ($al['id'] == $asesmen['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($al['nama_asesmen']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <a href="<?= BASE_URL ?>index.php?url=guru/asesmen&tab=asesmen" class="btn btn-outline-secondary rounded-3">
                            &larr; Kembali
                        </a>
                    </div>
                </div>

                <!-- TP & KKTP Target Badges Info -->
                <div class="mt-3 pt-3 border-top">
                    <div class="small fw-bold text-secondary mb-2 text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="bi bi-bullseye text-danger me-1"></i>Tujuan Pembelajaran (TP) & Ambang Batas KKTP yang Diukur:
                    </div>
                    <div class="row g-2">
                        <?php foreach ($tps as $tp): ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="p-2.5 rounded-3 border bg-light h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace fw-bold">
                                            <?= htmlspecialchars($tp['kode_tp']) ?>
                                        </span>
                                        <?php if ($tp['kktp_metode'] === 'checklist'): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.72rem;">
                                                Checklist (Target <?= (int)($tp['kktp_target_ind'] ?: 1) ?>)
                                            </span>
                                        <?php elseif ($tp['kktp_metode'] === 'rubrik'): ?>
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size: 0.72rem;">
                                                Rubrik (Min <?= number_format($tp['kktp_nilai_min'] ?? 75, 0) ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size: 0.72rem;">
                                                Batas Nilai (Min <?= number_format($tp['kktp_nilai_min'] ?? 75, 0) ?>)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-dark text-truncate" title="<?= htmlspecialchars($tp['deskripsi_tp']) ?>">
                                        <?= htmlspecialchars($tp['deskripsi_tp']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Scoring Matrix Form -->
            <form action="<?= BASE_URL ?>index.php?url=guru/asesmen" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="save_batch_nilai">
                <input type="hidden" name="asesmen_id" value="<?= $asesmen['id'] ?>">

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                            <i class="bi bi-table text-primary"></i> Matriks Penilaian Siswa Per-TP (Status 1 = Tercapai, 0 = Belum)
                        </div>
                        <button type="submit" class="btn btn-success shadow-sm fw-bold px-4 py-2 rounded-3 d-flex align-items-center gap-2">
                            <i class="bi bi-save-fill"></i> Simpan Semua Nilai
                        </button>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="bg-light text-secondary small fw-bold text-uppercase">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">No</th>
                                        <th style="width: 100px;">NIS</th>
                                        <th style="min-width: 180px;">Nama Siswa</th>
                                        <?php foreach ($tps as $tp): ?>
                                            <th class="text-center" style="min-width: 190px;">
                                                <div class="font-monospace text-primary fw-bold"><?= htmlspecialchars($tp['kode_tp']) ?></div>
                                                <div class="text-muted fw-normal" style="font-size: 0.72rem;">
                                                    KKTP: <strong><?= number_format($tp['kktp_nilai_min'] ?? 75, 0) ?></strong>
                                                </div>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($siswaList)): ?>
                                        <tr>
                                            <td colspan="<?= 3 + count($tps) ?>" class="text-center py-5 text-muted">
                                                Belum ada siswa yang terdaftar dalam rombel ini.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $no = 1; foreach ($siswaList as $s): ?>
                                            <tr>
                                                <td class="text-center text-muted fw-bold"><?= $no++ ?></td>
                                                <td class="font-monospace small"><?= htmlspecialchars($s['nis']) ?></td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($s['nama_lengkap']) ?></div>
                                                </td>

                                                <?php foreach ($tps as $tp): ?>
                                                    <?php 
                                                        $sc = $scores[$s['siswa_id']][$tp['tp_id']] ?? null;
                                                        $val = $sc ? $sc['nilai_asli'] : '';
                                                        $statusCode = $sc ? (int)$sc['status_code'] : null;
                                                        $isRemedial = $sc && !empty($sc['is_remedial']);
                                                        $nilaiAwal = $sc ? $sc['nilai_awal'] : null;
                                                    ?>
                                                    <td class="p-2 text-center align-middle">
                                                        <div class="d-flex flex-column gap-1 align-items-center">
                                                            <!-- Input Skor Asli -->
                                                            <div class="d-flex align-items-center gap-1 w-100 justify-content-center">
                                                                <input type="number" step="0.1" min="0" max="<?= $asesmen['nilai_maksimum'] ?>" 
                                                                       name="nilai[<?= $s['siswa_id'] ?>][<?= $tp['tp_id'] ?>]"
                                                                       class="form-control form-control-sm text-center fw-bold font-monospace matrix-score-input" 
                                                                       style="max-width: 80px;"
                                                                       value="<?= htmlspecialchars((string)$val) ?>"
                                                                       data-kktp-min="<?= $tp['kktp_nilai_min'] ?? 75.00 ?>"
                                                                       placeholder="0-100">

                                                                <!-- Tombol Modal Remedial jika sudah dinilai -->
                                                                <?php if ($sc): ?>
                                                                    <button type="button" class="btn btn-xs btn-outline-warning py-1 px-1.5 rounded-2 btn-open-remedial"
                                                                            title="Input / Edit Nilai Remedial (Menjaga Nilai Awal)"
                                                                            data-bs-toggle="modal" data-bs-target="#modalRemedial"
                                                                            data-asesmen-id="<?= $asesmen['id'] ?>"
                                                                            data-tp-id="<?= $tp['tp_id'] ?>"
                                                                            data-tp-kode="<?= htmlspecialchars($tp['kode_tp']) ?>"
                                                                            data-siswa-id="<?= $s['siswa_id'] ?>"
                                                                            data-siswa-nama="<?= htmlspecialchars($s['nama_lengkap']) ?>"
                                                                            data-nilai-saat-ini="<?= $sc['nilai_asli'] ?>"
                                                                            data-nilai-awal="<?= $sc['nilai_awal'] ?? $sc['nilai_asli'] ?>">
                                                                        <i class="bi bi-arrow-repeat"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>

                                                            <!-- Status Ketercapaian Badge (1/0) -->
                                                            <?php if ($statusCode !== null): ?>
                                                                <div class="d-flex align-items-center gap-1 mt-1">
                                                                    <?php if ($statusCode === 1): ?>
                                                                        <span class="badge bg-success text-white py-1 px-2 rounded-pill small fw-bold" style="font-size: 0.72rem;">
                                                                            <i class="bi bi-check-circle-fill me-1"></i>Tercapai (1)
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-danger text-white py-1 px-2 rounded-pill small fw-bold" style="font-size: 0.72rem;">
                                                                            <i class="bi bi-x-circle-fill me-1"></i>Belum (0)
                                                                        </span>
                                                                    <?php endif; ?>

                                                                    <?php if ($isRemedial): ?>
                                                                        <span class="badge bg-warning text-dark py-1 px-1.5 rounded-pill small" title="Hasil Remedial (Nilai Awal: <?= $nilaiAwal ?>)">
                                                                            Remedial
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-white py-3 px-4 border-top d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i> Nilai asli siswa disimpan utuh. Status binary (1 = Tercapai, 0 = Belum Tercapai) dievaluasi otomatis sesuai ambang KKTP.
                        </div>
                        <button type="submit" class="btn btn-success shadow-sm fw-bold px-4 py-2 rounded-3 d-flex align-items-center gap-2">
                            <i class="bi bi-save-fill"></i> Simpan Semua Nilai
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ======================================================================= -->
    <!-- TAB 3: REKAP KETERCAPAIAN KELAS PER-TP -->
    <!-- ======================================================================= -->
    <?php if ($activeTab === 'rekap_kelas'): ?>
        <!-- Filter Toolbar -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-body p-3.5">
                <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-3 align-items-end">
                    <input type="hidden" name="url" value="guru/asesmen">
                    <input type="hidden" name="tab" value="rekap_kelas">

                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-bold text-secondary mb-1">Rombel / Kelas</label>
                        <select name="rombel_id" class="form-select rounded-3" onchange="this.form.submit()">
                            <?= renderRombelOptgroupsHtml($rombelByJurusan ?? [], $filterRombelId, false) ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-bold text-secondary mb-1">Mata Pelajaran</label>
                        <select name="mapel_id" class="form-select rounded-3" onchange="this.form.submit()">
                            <?php foreach ($teacherMapelList as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= ($filterMapelId == $m['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nama_mapel']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-outline-secondary w-100 rounded-3 fw-semibold">
                            <i class="bi bi-arrow-repeat"></i> Segarkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($rekapKelas): ?>
            <!-- Class Recap Table -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                            <i class="bi bi-pie-chart-fill text-danger"></i> Analisis Tingkat Ketercapaian TP Kelas
                        </div>
                        <p class="text-muted small mb-0">Identifikasi TP yang persentase ketercapaiannya rendah (&lt; 70%) untuk pelaksanaan tindak lanjut / remedial klasikal.</p>
                    </div>
                    <span class="badge bg-primary text-white px-3 py-1.5 rounded-pill font-monospace">
                        <?= $rekapKelas['total_siswa_kelas'] ?> Siswa Terdaftar
                    </span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary small fw-bold text-uppercase">
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th style="width: 130px;">Kode TP</th>
                                    <th>Deskripsi Tujuan Pembelajaran</th>
                                    <th class="text-center">Ambang KKTP</th>
                                    <th class="text-center">Tercapai / Total</th>
                                    <th style="min-width: 200px;">% Ketercapaian Kelas</th>
                                    <th class="text-center" style="min-width: 220px;">Rekomendasi Tindak Lanjut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rekapKelas['tp_list'])): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-clipboard-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                            Belum ada data nilai asesmen TP yang diinput untuk kelas dan mapel ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($rekapKelas['tp_list'] as $row): ?>
                                        <tr class="<?= ($row['persentase_ketercapaian'] < 70) ? 'table-danger-subtle' : '' ?>">
                                            <td class="text-center text-muted fw-bold"><?= $no++ ?></td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace fw-bold px-2.5 py-1">
                                                    <?= htmlspecialchars($row['kode_tp']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($row['deskripsi_tp']) ?></div>
                                                <?php if (!empty($row['materi_pokok'])): ?>
                                                    <span class="small text-muted"><i class="bi bi-tag me-1"></i><?= htmlspecialchars($row['materi_pokok']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border font-monospace">
                                                    Min: <?= number_format($row['kktp_nilai_min'] ?? 75, 0) ?>
                                                </span>
                                            </td>
                                            <td class="text-center font-monospace fw-bold">
                                                <span class="text-success"><?= $row['total_tercapai'] ?></span> / 
                                                <span class="text-dark"><?= $row['total_asesmen_siswa'] ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height: 10px;">
                                                        <div class="progress-bar bg-<?= $row['badge_color'] ?>" 
                                                             role="progressbar" 
                                                             style="width: <?= $row['persentase_ketercapaian'] ?>%" 
                                                             aria-valuenow="<?= $row['persentase_ketercapaian'] ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <span class="small fw-bold text-dark font-monospace" style="min-width: 45px;">
                                                        <?= $row['persentase_ketercapaian'] ?>%
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-<?= $row['badge_color'] ?> px-3 py-1.5 rounded-pill fw-semibold">
                                                    <?= $row['kategori_tindak_lanjut'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ======================================================================= -->
    <!-- TAB 4: PROFIL KETERCAPAIAN SISWA & INTEGRASI DRAF E-RAPOR -->
    <!-- ======================================================================= -->
    <?php if ($activeTab === 'rekap_siswa'): ?>
        <!-- Filter Siswa Toolbar -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-body p-3.5">
                <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-3 align-items-end">
                    <input type="hidden" name="url" value="guru/asesmen">
                    <input type="hidden" name="tab" value="rekap_siswa">

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Rombel / Kelas</label>
                        <select name="rombel_id" class="form-select rounded-3" onchange="this.form.submit()">
                            <?= renderRombelOptgroupsHtml($rombelByJurusan ?? [], $filterRombelId, false) ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Mata Pelajaran</label>
                        <select name="mapel_id" class="form-select rounded-3" onchange="this.form.submit()">
                            <?php foreach ($teacherMapelList as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= ($filterMapelId == $m['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nama_mapel']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Pilih Siswa</label>
                        <select name="siswa_id" class="form-select rounded-3" onchange="this.form.submit()">
                            <?php foreach ($siswaInRombel as $sw): ?>
                                <option value="<?= $sw['id'] ?>" <?= ($selectedSiswaId == $sw['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sw['nis']) ?> - <?= htmlspecialchars($sw['nama_lengkap']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($rekapSiswa): ?>
            <!-- Auto-Draft E-Rapor Deskripsi Card -->
            <?php 
                $raporDesc = $assessModel->generateDeskripsiRaporFromTp($selectedSiswaId, $filterMapelId, $activeTaId, $activeSemester);
            ?>
            <div class="card border border-primary border-opacity-25 rounded-4 shadow-sm mb-4 bg-white overflow-hidden">
                <div class="card-header bg-primary bg-opacity-10 py-3 px-4 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="fw-bold text-primary fs-6 d-flex align-items-center gap-2">
                        <i class="bi bi-magic text-primary"></i> Draf Otomatis Capaian Kompetensi E-Rapor (Kurikulum Merdeka)
                    </div>
                    <span class="badge bg-success px-3 py-1.5 rounded-pill font-monospace fw-bold">
                        Ketercapaian: <?= $rekapSiswa['persentase_ketercapaian'] ?>% (<?= $rekapSiswa['total_tercapai'] ?>/<?= $rekapSiswa['total_tp_dinilai'] ?> TP)
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">
                            <i class="bi bi-award text-success me-1"></i>Deskripsi Capaian Kompetensi Tertinggi (Tercapai):
                        </label>
                        <div class="p-3 rounded-3 bg-success bg-opacity-10 text-success-emphasis border border-success-subtle fw-medium" id="preview_desc_tercapai">
                            <?= htmlspecialchars($raporDesc['deskripsi_tercapai']) ?>
                        </div>
                    </div>

                    <?php if (!empty($raporDesc['deskripsi_perlu_bimbingan'])): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary mb-1">
                                <i class="bi bi-exclamation-circle text-danger me-1"></i>Deskripsi Kompetensi yang Perlu Peningkatan / Bimbingan:
                            </label>
                            <div class="p-3 rounded-3 bg-danger bg-opacity-10 text-danger-emphasis border border-danger-subtle fw-medium" id="preview_desc_perlu">
                                <?= htmlspecialchars($raporDesc['deskripsi_perlu_bimbingan']) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top flex-wrap gap-2">
                        <div class="text-muted small">
                            <i class="bi bi-lightbulb me-1"></i> Rumusan deskripsi di atas dihasilkan secara cerdas dari TP dengan skor tuntas dan TP yang belum tercapai.
                        </div>
                        <a href="<?= BASE_URL ?>index.php?url=guru/inputNilai" class="btn btn-outline-primary fw-semibold rounded-3 d-flex align-items-center gap-2">
                            <i class="bi bi-box-arrow-up-right"></i> Buka Modul Input Nilai E-Rapor
                        </a>
                    </div>
                </div>
            </div>

            <!-- Student Individual TP Records Table -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                        <i class="bi bi-person-check-fill text-success"></i> Riwayat Pencapaian TP Siswa
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary small fw-bold text-uppercase">
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>Asesmen</th>
                                    <th>Kode TP</th>
                                    <th>Deskripsi TP</th>
                                    <th class="text-center">Nilai Asli</th>
                                    <th class="text-center">Histori Awal</th>
                                    <th class="text-center">Status Binary</th>
                                    <th class="text-center">Status Ketercapaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rekapSiswa['records'])): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            Belum ada riwayat asesmen TP untuk siswa ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($rekapSiswa['records'] as $rec): ?>
                                        <tr>
                                            <td class="text-center text-muted fw-bold"><?= $no++ ?></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($rec['nama_asesmen']) ?></div>
                                                <div class="small text-muted"><?= date('d/m/Y', strtotime($rec['tanggal'])) ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace fw-bold">
                                                    <?= htmlspecialchars($rec['kode_tp']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="small text-dark"><?= htmlspecialchars($rec['deskripsi_tp']) ?></div>
                                            </td>
                                            <td class="text-center font-monospace fw-bold fs-6">
                                                <?= number_format($rec['nilai_asli'], 1) ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($rec['is_remedial'])): ?>
                                                    <span class="badge bg-warning text-dark font-monospace" title="Nilai awal sebelum remedial">
                                                        <?= number_format($rec['nilai_awal'] ?? $rec['nilai_asli'], 1) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center font-monospace fw-bold">
                                                <span class="badge <?= ($rec['status_code'] == 1) ? 'bg-success' : 'bg-danger' ?> rounded-pill px-2.5 py-1">
                                                    <?= $rec['status_code'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($rec['status_code'] == 1): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-bold">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Tercapai
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-bold">
                                                        <i class="bi bi-x-circle-fill me-1"></i>Belum Tercapai
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
        <?php endif; ?>
    <?php endif; ?>

</div>
</main>

<!-- =========================================================================== -->
<!-- MODAL BUAT ASESMEN BARU (MULTI-TP CHECKBOX SELECTOR) -->
<!-- =========================================================================== -->
<div class="modal fade" id="modalAddAsesmen" tabindex="-1" aria-labelledby="modalAddAsesmenLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 820px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=guru/asesmen" method="POST" id="formAddAsesmen">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_asesmen">

                <div class="modal-header bg-danger text-white py-3 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-white bg-opacity-25 p-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-bullseye fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="modalAddAsesmenLabel">Buat Asesmen Pembelajaran Baru</h5>
                            <p class="small text-white-50 mb-0">1 Asesmen dapat mengukur satu atau beberapa Tujuan Pembelajaran (TP) sekaligus.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Rombel / Kelas <span class="text-danger">*</span></label>
                            <select name="rombel_id" id="add_asesmen_rombel" class="form-select rounded-3 py-2" required>
                                <option value="">-- Pilih Rombel Kelas Target --</option>
                                <?= renderRombelOptgroupsHtml($rombelByJurusan ?? [], $filterRombelId, false) ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="mapel_id" id="add_asesmen_mapel" class="form-select rounded-3 py-2" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                <?php foreach ($teacherMapelList as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= ($filterMapelId == $m['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m['nama_mapel']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-bold text-secondary mb-1">Nama Asesmen <span class="text-danger">*</span></label>
                            <input type="text" name="nama_asesmen" class="form-control rounded-3 py-2 fw-semibold" placeholder="Contoh: Formatif 1 - Logika Algoritma & Flowchart" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary mb-1">Jenis Asesmen <span class="text-danger">*</span></label>
                            <select name="jenis_asesmen" class="form-select rounded-3 py-2" required>
                                <option value="formatif">Formatif Harian</option>
                                <option value="tugas">Tugas Mandiri / Kelompok</option>
                                <option value="projek">Projek / Praktik Kejuruan</option>
                                <option value="sumatif_tengah">Sumatif Tengah Semester (STS)</option>
                                <option value="sumatif_akhir">Sumatif Akhir Semester (SAS)</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary mb-1">Tanggal Pelaksanaan</label>
                            <input type="date" name="tanggal" class="form-control rounded-3 py-2" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary mb-1">Nilai Maksimum</label>
                            <input type="number" step="1" name="nilai_maksimum" class="form-control rounded-3 py-2 text-center font-monospace" value="100">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary mb-1">Bobot Asesmen</label>
                            <input type="number" step="0.1" min="0.1" name="bobot" class="form-control rounded-3 py-2 text-center font-monospace" value="1.0">
                        </div>
                    </div>

                    <!-- Multi-TP Checkbox Selection Section -->
                    <div class="card border-0 rounded-3 shadow-xs bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label small fw-bold text-secondary mb-0">
                                <i class="bi bi-check2-square text-danger me-1"></i>Pilih Tujuan Pembelajaran (TP) yang Diukur <span class="text-danger">*</span>
                            </label>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" id="btnSelectAllTp">Pilih Semua</button>
                        </div>
                        <div class="form-text text-muted small mb-2">Pilih satu atau beberapa TP sekaligus yang dinilai pada asesmen/projek ini.</div>

                        <div id="tpCheckboxList" class="d-flex flex-column gap-2" style="max-height: 240px; overflow-y: auto;">
                            <div class="text-muted small py-3 text-center">Pilih Mata Pelajaran terlebih dahulu untuk memuat daftar TP...</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white py-3 px-4 border-top">
                    <button type="button" class="btn btn-light border px-4 py-2 fw-semibold rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i> Buat Asesmen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =========================================================================== -->
<!-- MODAL INPUT NILAI REMEDIAL (PRESERVASI NILAI AWAL) -->
<!-- =========================================================================== -->
<div class="modal fade" id="modalRemedial" tabindex="-1" aria-labelledby="modalRemedialLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=guru/asesmen" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="save_remedial_modal">
                <input type="hidden" name="asesmen_id" id="remedial_asesmen_id" value="">
                <input type="hidden" name="tp_id" id="remedial_tp_id" value="">
                <input type="hidden" name="siswa_id" id="remedial_siswa_id" value="">

                <div class="modal-header bg-warning text-dark py-3 px-4">
                    <div class="d-flex align-items-center gap-2.5">
                        <i class="bi bi-arrow-repeat fs-4 text-dark"></i>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="modalRemedialLabel">Input Hasil Remedial Siswa</h5>
                            <p class="small text-dark-50 mb-0">Nilai awal akan dipertahankan dalam histori belajar.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <div class="card border rounded-3 p-3 bg-white mb-3 shadow-xs">
                        <div class="text-muted small">Siswa:</div>
                        <div class="fw-bold text-dark fs-6" id="remedial_nama_siswa">-</div>
                        <div class="d-flex align-items-center gap-2 mt-1.5">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" id="remedial_kode_tp">TP-...</span>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" id="remedial_nilai_awal_badge">Nilai Awal: -</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Nilai Baru Setelah Remedial <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" min="0" max="100" name="nilai_remedial" id="remedial_input_nilai" class="form-control text-center font-monospace fw-bold fs-4 py-2 text-primary" required>
                        <div class="form-text text-muted small">Jika nilai baru &ge; ambang KKTP, status otomatis berubah menjadi <strong>Tercapai (1)</strong>.</div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-secondary mb-1">Catatan Pelaksanaan Remedial</label>
                        <input type="text" name="catatan" class="form-control py-2" placeholder="Contoh: Remedial tes tulis ulang / penugasan konsep">
                    </div>
                </div>

                <div class="modal-footer bg-white py-3 px-4 border-top">
                    <button type="button" class="btn btn-light border px-4 py-2 fw-semibold rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2">
                        <i class="bi bi-save-fill"></i> Simpan Hasil Remedial
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dynamic TP loader for modalAddAsesmen
    const mapelSelect = document.getElementById('add_asesmen_mapel');
    const tpListWrapper = document.getElementById('tpCheckboxList');
    const btnSelectAll = document.getElementById('btnSelectAllTp');

    function loadTpsForMapel(mapelId) {
        if (!mapelId || !tpListWrapper) return;
        tpListWrapper.innerHTML = '<div class="text-muted small py-2 text-center"><i class="bi bi-hourglass-split"></i> Memuat Tujuan Pembelajaran...</div>';

        fetch(`<?= BASE_URL ?>index.php?url=guru/asesmen&ajax_action=get_tp_by_mapel&mapel_id=${mapelId}`)
            .then(r => r.json())
            .then(res => {
                if (res.status && res.data && res.data.length > 0) {
                    let html = '';
                    res.data.forEach(t => {
                        html += `
                            <label class="card border rounded-3 p-2.5 bg-light d-flex flex-row align-items-start gap-2.5 cursor-pointer mb-1" style="cursor: pointer;">
                                <input type="checkbox" name="tp_ids[]" value="${t.id}" class="form-check-input mt-1 flex-shrink-0 tp-check-item">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2 mb-0.5">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace fw-bold" style="font-size: 0.72rem;">${t.kode_tp}</span>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">KKTP Min: ${t.kktp_nilai_min}</span>
                                    </div>
                                    <div class="small text-dark text-truncate" style="line-height: 1.4;">${t.deskripsi}</div>
                                </div>
                            </label>
                        `;
                    });
                    tpListWrapper.innerHTML = html;
                } else {
                    tpListWrapper.innerHTML = '<div class="text-warning small py-3 text-center"><i class="bi bi-info-circle"></i> Belum ada TP yang dirumuskan untuk Mapel ini. Silakan tambahkan di menu Penyusunan CP & TP.</div>';
                }
            })
            .catch(() => {
                tpListWrapper.innerHTML = '<div class="text-danger small py-3 text-center">Gagal memuat TP. Silakan periksa koneksi.</div>';
            });
    }

    if (mapelSelect) {
        mapelSelect.addEventListener('change', function() {
            loadTpsForMapel(this.value);
        });
        if (mapelSelect.value) {
            loadTpsForMapel(mapelSelect.value);
        }
    }

    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', function() {
            const checks = document.querySelectorAll('.tp-check-item');
            const allChecked = Array.from(checks).every(c => c.checked);
            checks.forEach(c => c.checked = !allChecked);
            btnSelectAll.textContent = allChecked ? 'Pilih Semua' : 'Batal Pilih';
        });
    }

    // Modal Remedial Handler
    document.querySelectorAll('.btn-open-remedial').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('remedial_asesmen_id').value = this.dataset.asesmenId;
            document.getElementById('remedial_tp_id').value = this.dataset.tpId;
            document.getElementById('remedial_siswa_id').value = this.dataset.siswaId;
            document.getElementById('remedial_nama_siswa').textContent = this.dataset.siswaNama;
            document.getElementById('remedial_kode_tp').textContent = this.dataset.tpKode;
            document.getElementById('remedial_nilai_awal_badge').textContent = 'Nilai Awal: ' + this.dataset.nilaiAwal;
            document.getElementById('remedial_input_nilai').value = this.dataset.nilaiSaatIni;
        });
    });
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
