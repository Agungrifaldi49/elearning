<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
if (!function_exists('formatTpDescriptionHtml')) {
    function formatTpDescriptionHtml($text) {
        if (empty($text)) return '';
        $lines = preg_split('/\r\n|\r|\n/', trim($text));
        if (count($lines) <= 1 && !preg_match('/^(\d+[\.\)\-]|[a-zA-Z][\.\)]|[•\-\*✓✔☑▪▫►▶→➔➢+~–—\x{2022}\x{25AA}\x{2713}\x{2714}])\s*/u', trim($text))) {
            return '<div class="tp-deskripsi-text" style="color: #0f172a !important; line-height: 1.6; font-size: 0.9rem;">' . nl2br(htmlspecialchars($text)) . '</div>';
        }
        $html = '<div class="tp-formatted-list d-flex flex-column" style="gap: 6px; margin-top: 3px;">';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;
            // 1. Numbered: 1. or 1) or 1-
            if (preg_match('/^(\d+)[\.\)\-]\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 8px;">'
                      . '<span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace rounded-pill flex-shrink-0" style="font-size:0.72rem; min-width:22px; padding: 2.5px 6px; text-align:center; font-weight:700;">' . $m[1] . '</span>'
                      . '<span class="tp-list-text" style="color: #0f172a !important; line-height: 1.6; font-size: 0.9rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            // 2. Lettered: a. or A. or a)
            } elseif (preg_match('/^([a-zA-Z])[\.\)]\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 8px;">'
                      . '<span class="badge bg-secondary-subtle text-dark border font-monospace rounded-pill flex-shrink-0" style="font-size:0.72rem; min-width:22px; padding: 2.5px 6px; text-align:center; font-weight:700;">' . strtoupper($m[1]) . '</span>'
                      . '<span class="tp-list-text" style="color: #0f172a !important; line-height: 1.6; font-size: 0.9rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            // 3. Checkmarks: ✓, ✔, ☑
            } elseif (preg_match('/^([✓✔☑\x{2713}\x{2714}])\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 8px;">'
                      . '<span class="text-success flex-shrink-0 fw-bold" style="font-size:0.95rem; line-height:1.5; width:18px; text-align:center;"><i class="bi bi-check-circle-fill"></i></span>'
                      . '<span class="tp-list-text" style="color: #0f172a !important; line-height: 1.6; font-size: 0.9rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            // 4. Arrows: →, ➔, ➢, ►, >
            } elseif (preg_match('/^([→➔➢►▶>])\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 8px;">'
                      . '<span class="text-primary flex-shrink-0 fw-bold" style="font-size:0.95rem; line-height:1.5; width:18px; text-align:center;"><i class="bi bi-arrow-right-short fs-5"></i></span>'
                      . '<span class="tp-list-text" style="color: #0f172a !important; line-height: 1.6; font-size: 0.9rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            // 5. Bullets & Other Symbols: •, -, *, ▪, ▫, +
            } elseif (preg_match('/^([•\-\*▪▫+–—\x{2022}\x{25AA}])\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 8px;">'
                      . '<span class="text-primary flex-shrink-0 fw-bold" style="font-size:1.15rem; line-height:1.2; width:18px; text-align:center;">•</span>'
                      . '<span class="tp-list-text" style="color: #0f172a !important; line-height: 1.6; font-size: 0.9rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            } else {
                $html .= '<div class="tp-list-text" style="color: #0f172a !important; line-height: 1.6; font-size: 0.9rem;">' . htmlspecialchars($trimmed) . '</div>';
            }
        }
        $html .= '</div>';
        return $html;
    }
}
?>

<style>
/* Modern & Responsive Aesthetics for CP/TP Management */
.btn-xs {
    padding: 0.18rem 0.45rem;
    font-size: 0.72rem;
    line-height: 1.3;
}
.tp-formatted-list {
    font-size: 0.9rem;
}
.tp-list-row {
    margin-bottom: 0.35rem;
}
.tp-list-row:last-child {
    margin-bottom: 0;
}
.tp-list-text {
    word-break: break-word;
    color: #0f172a !important;
    font-size: 0.9rem;
    line-height: 1.62;
}
.card {
    transition: all 0.2s ease-in-out;
}
.shadow-xs {
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
}
.table td, .table th {
    vertical-align: top !important;
}
.tp-item-card {
    background-color: #ffffff;
    border: 1px solid #e2e8f0 !important;
    border-left: 4px solid #0d6efd !important;
    border-radius: 10px !important;
    padding: 12px 14px !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}
.tp-item-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px -1px rgba(0,0,0,0.08), 0 2px 4px -1px rgba(0,0,0,0.05) !important;
    border-color: #cbd5e1 !important;
}
.tp-deskripsi-wrapper {
    color: #0f172a !important;
    font-size: 0.9rem;
    line-height: 1.62;
}
.modal-body {
    max-height: calc(85vh - 120px);
    overflow-y: auto;
}
@media (max-width: 767.98px) {
    .main-content {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }
    .modal-body {
        padding: 1rem !important;
    }
}
</style>

<main class="main-content px-3 px-md-4 py-3">
<div class="container-fluid">

    <!-- Header Breadcrumb & Page Actions -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?url=guru/dashboard" class="text-decoration-none text-muted"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Penilaian & Kurikulum</li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Penyusunan CP & TP</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <span class="badge rounded-circle p-2 bg-primary-subtle text-primary">
                    <i class="bi bi-card-checklist fs-4"></i>
                </span>
                Penyusunan Capaian & Tujuan Pembelajaran (CP & TP)
            </h4>
            <p class="text-muted small mb-0">Kelola dan rumuskan Capaian Pembelajaran (CP) serta Tujuan Pembelajaran (TP) untuk mata pelajaran yang Anda ampu secara terstruktur.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-success shadow-sm fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCopyTP" title="Salin Tujuan Pembelajaran dari CP lain / Tahun Ajaran Sebelumnya">
                <i class="bi bi-box-arrow-in-down fs-5"></i>
                <span>Salin dari Bank TP</span>
            </button>
            <button type="button" class="btn btn-outline-primary shadow-sm fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAddTP">
                <i class="bi bi-plus-circle fs-5"></i>
                <span>+ Tambah TP Baru</span>
            </button>
            <button type="button" class="btn btn-primary shadow-sm fw-semibold px-3.5 py-2 rounded-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAddCP">
                <i class="bi bi-plus-circle-fill fs-5"></i>
                <span>+ Tambah CP Baru</span>
            </button>
        </div>
    </div>

    <!-- Flash Notification Alerts -->
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

    <!-- Summary Metrics & Teacher Banner (Balanced Grid) -->
    <div class="row g-3 mb-4">
        <!-- Teacher Profile & Mapel Card -->
        <div class="col-12 col-xl-6">
            <div class="card h-100 border border-primary border-opacity-25 shadow-sm rounded-4 p-3 bg-white position-relative overflow-hidden">
                <div class="position-absolute top-0 start-0 h-100 bg-primary" style="width: 4px;"></div>
                <div class="d-flex align-items-center gap-3 ps-2">
                    <!-- Circular Teacher Avatar with Verified Ring -->
                    <div class="position-relative flex-shrink-0">
                        <div class="rounded-circle text-white shadow-sm d-flex align-items-center justify-content-center" 
                             style="width: 58px; height: 58px; background: linear-gradient(135deg, #0d6efd 0%, #0284c7 100%); border: 3px solid #e0f2fe;">
                            <i class="bi bi-person-fill" style="font-size: 1.85rem;"></i>
                        </div>
                        <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-1" style="width: 12px; height: 12px;" title="Guru Aktif"></span>
                    </div>

                    <!-- Teacher Information Block -->
                    <div class="flex-grow-1 min-w-0">
                        <!-- Top Meta: Guru Pengampu Badge & Kurikulum Tag -->
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" style="font-size: 0.72rem;">
                                <i class="bi bi-person-badge-fill"></i> Guru Pengampu
                            </span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-bold font-monospace" style="font-size: 0.72rem;">
                                <?= htmlspecialchars($kurikulumList[0]['kode'] ?? 'Kurikulum') ?>
                            </span>
                        </div>

                        <!-- Teacher Full Name with Icon & Verified Badge -->
                        <h5 class="fw-bold text-dark mb-1 text-truncate d-flex align-items-center gap-1.5">
                            <span class="text-truncate"><?= htmlspecialchars($guru['nama_lengkap'] ?? 'Bpk/Ibu Guru') ?></span>
                            <i class="bi bi-patch-check-fill text-primary flex-shrink-0" style="font-size: 1.05rem;" title="Terverifikasi"></i>
                        </h5>

                        <!-- Assigned Subject Badges -->
                        <div class="text-secondary small d-flex flex-wrap align-items-center gap-1.5 mt-1">
                            <span class="text-muted fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.78rem;">
                                <i class="bi bi-book-half text-primary"></i> Mapel Diampu:
                            </span>
                            <?php if (!empty($teacherMapelList)): ?>
                                <?php foreach ($teacherMapelList as $tmp): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill fw-medium" style="font-size: 0.72rem;">
                                        <?= htmlspecialchars($tmp['nama_mapel']) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill" style="font-size: 0.72rem;">Semua Mata Pelajaran</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric CP Card -->
        <div class="col-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-3.5 bg-white d-flex justify-content-between">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size:0.75rem; letter-spacing: 0.5px;">Capaian (CP)</span>
                        <h2 class="fw-bold text-primary mb-0"><?= count($cpList) ?></h2>
                    </div>
                    <div class="rounded-circle p-2.5 bg-primary-subtle text-primary">
                        <i class="bi bi-journal-check fs-4"></i>
                    </div>
                </div>
                <div class="text-muted small mt-2" style="font-size:0.75rem;">
                    <i class="bi bi-check-circle-fill text-primary me-1"></i>Tersedia dalam kurikulum
                </div>
            </div>
        </div>

        <!-- Metric TP Card -->
        <div class="col-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-3.5 bg-white d-flex justify-content-between">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size:0.75rem; letter-spacing: 0.5px;">Tujuan (TP)</span>
                        <h2 class="fw-bold text-success mb-0"><?= count($tpList) ?></h2>
                    </div>
                    <div class="rounded-circle p-2.5 bg-success-subtle text-success">
                        <i class="bi bi-bullseye fs-4"></i>
                    </div>
                </div>
                <div class="text-muted small mt-2" style="font-size:0.75rem;">
                    <i class="bi bi-check-circle-fill text-success me-1"></i>Siap untuk asesmen & rapor
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card (Uniform 42px Alignment) -->
    <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white">
        <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-2.5 align-items-end">
            <input type="hidden" name="url" value="guru/cptp">
            
            <div class="col-12 col-md-4">
                <label class="small fw-bold text-secondary mb-1.5 d-flex align-items-center gap-1">
                    <i class="bi bi-book text-primary"></i>
                    <span>Mata Pelajaran:</span>
                </label>
                <select name="filter_mapel_id" class="form-select rounded-3" style="height: 42px;" onchange="this.form.submit()">
                    <option value="">-- Semua Mata Pelajaran Saya --</option>
                    <?php foreach ($teacherMapelList as $mp): ?>
                        <option value="<?= $mp['id'] ?>" <?= ($filterMapelId == $mp['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel'] ?? 'MP') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="small fw-bold text-secondary mb-1.5 d-flex align-items-center gap-1">
                    <i class="bi bi-mortarboard text-primary"></i>
                    <span>Kurikulum:</span>
                </label>
                <select name="filter_kurikulum_id" class="form-select rounded-3" style="height: 42px;" onchange="this.form.submit()">
                    <?php foreach ($kurikulumList as $kur): ?>
                        <option value="<?= $kur['id'] ?>" <?= ($filterKurId == $kur['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="small fw-bold text-secondary mb-1.5 d-flex align-items-center gap-1">
                    <i class="bi bi-layers text-primary"></i>
                    <span>Fase / Jenjang:</span>
                </label>
                <select name="filter_fase_id" class="form-select rounded-3" style="height: 42px;" onchange="this.form.submit()">
                    <option value="">-- Semua Fase --</option>
                    <?php foreach ($allFaseList as $f): ?>
                        <option value="<?= $f['id'] ?>" <?= ($filterFaseId == $f['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-1.5">
                <button type="submit" class="btn btn-primary flex-fill fw-semibold rounded-3 d-flex align-items-center justify-content-center gap-1" style="height: 42px;">
                    <i class="bi bi-funnel-fill"></i>
                    <span>Filter</span>
                </button>
                <?php if ($filterMapelId || $filterFaseId): ?>
                    <a href="<?= BASE_URL ?>index.php?url=guru/cptp" class="btn btn-outline-secondary rounded-3 px-3 d-flex align-items-center justify-content-center" style="height: 42px;" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- CP & TP Table Card (Roomy 6-Column Responsive Layout) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-list-stars text-primary"></i>
                    Daftar Rumusan Capaian (CP) & Tujuan Pembelajaran (TP)
                </h5>
                <p class="text-muted small mb-0 mt-0.5">Disusun per elemen kompetensi untuk dasar penilaian formatif, sumatif, dan deskripsi rapor.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-muted border px-2.5 py-1.5 rounded-pill small">
                    Total CP: <strong class="text-primary"><?= count($cpList) ?></strong>
                </span>
                <button type="button" class="btn btn-primary btn-sm rounded-3 fw-semibold px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#modalAddCP">
                    <i class="bi bi-plus-circle me-1"></i> Tambah CP
                </button>
            </div>
        </div>

        <div class="table-responsive p-3">
            <table class="table table-hover align-middle datatable mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:40px;">No</th>
                        <th style="width:120px;">Kurikulum & Fase</th>
                        <th style="width:180px;">Mata Pelajaran</th>
                        <th style="min-width:320px;">Capaian Pembelajaran (CP)</th>
                        <th style="min-width:340px;">Tujuan Pembelajaran (TP) Terkait</th>
                        <th class="text-center" style="width:80px;">Aksi CP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cpList)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <span class="fw-semibold">Belum ada data Capaian Pembelajaran (CP) untuk filter ini.</span><br>
                                <small class="text-muted">Klik tombol <strong>Tambah CP Baru</strong> di atas untuk mulai merumuskan CP.</small>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cpList as $i => $cp): 
                            $childTps = array_filter($tpList, function($t) use ($cp) { return $t['cp_id'] == $cp['id']; });
                            $isMyCp = ($cp['guru_id'] == $guruId);
                        ?>
                            <tr>
                                <td class="text-center fw-semibold text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-2 mb-1 d-inline-block font-monospace">
                                        <?= htmlspecialchars($cp['kode_kurikulum']) ?>
                                    </span>
                                    <?php if (!empty($cp['nama_fase'])): ?>
                                        <span class="badge bg-secondary-subtle text-dark border px-2 py-0.5 rounded-2 d-block small">
                                            <?= htmlspecialchars($cp['nama_fase']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($cp['nama_mapel']) ?></div>
                                    <div class="mt-1 d-flex align-items-center">
                                        <?php if ($isMyCp): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1 rounded-pill px-2.5 py-0.5" style="font-size:0.73rem;">
                                                <i class="bi bi-person-check-fill"></i> Guru: Anda Sendiri
                                            </span>
                                        <?php elseif (!empty($cp['nama_guru'])): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center gap-1 rounded-pill px-2.5 py-0.5" style="font-size:0.73rem;">
                                                <i class="bi bi-person-badge-fill"></i> Guru: <?= htmlspecialchars($cp['nama_guru']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border d-inline-flex align-items-center gap-1 rounded-pill px-2.5 py-0.5" style="font-size:0.72rem;">
                                                <i class="bi bi-shield-check text-muted"></i> Tim Kurikulum
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <!-- Merged Spacious CP Column (Kode, Elemen, Full Deskripsi) -->
                                <td>
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap mb-2">
                                        <span class="badge bg-primary text-white font-monospace px-2.5 py-1 rounded-2 shadow-xs">
                                            <?= htmlspecialchars($cp['kode_cp']) ?>
                                        </span>
                                        <?php if (!empty($cp['elemen'])): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-2 small">
                                                <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($cp['elemen']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-dark small" style="line-height: 1.65; color: #1e293b;">
                                        <?= nl2br(htmlspecialchars($cp['deskripsi'])) ?>
                                    </div>
                                </td>
                                <td style="min-width: 360px; padding: 14px 16px !important;">
                                    <!-- Child TP Cards List -->
                                    <?php if (empty($childTps)): ?>
                                        <div class="p-3 rounded-3 bg-light border border-dashed text-center text-muted small mb-2">
                                            <i class="bi bi-info-circle me-1"></i> Belum ada TP turunan untuk CP ini.
                                        </div>
                                    <?php else: ?>
                                        <div class="tp-container d-flex flex-column gap-2.5 mb-2.5">
                                            <?php foreach ($childTps as $tp): ?>
                                                <div class="tp-item-card d-flex justify-content-between align-items-start gap-3">
                                                    <div class="flex-grow-1 min-w-0">
                                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1 rounded fw-bold" style="font-size: 0.78rem;">
                                                                <?= htmlspecialchars($tp['kode_tp']) ?>
                                                            </span>
                                                            <?php if (!empty($tp['materi_pokok'])): ?>
                                                                <span class="badge bg-light text-dark border px-2.5 py-1 rounded fw-semibold" style="font-size: 0.75rem; background-color: #f8fafc !important;">
                                                                    <i class="bi bi-tag-fill text-primary me-1"></i><?= htmlspecialchars($tp['materi_pokok']) ?>
                                                                </span>
                                                            <?php endif; ?>

                                                            <!-- KKTP Status Badge -->
                                                            <?php 
                                                                $kMetode = $tp['kktp_metode'] ?? 'interval_nilai';
                                                                $kMin = $tp['kktp_nilai_min'] ?? 75.00;
                                                                $kTarget = (int)($tp['kktp_target_ind'] ?? 0);
                                                            ?>
                                                            <?php if ($kMetode === 'checklist'): ?>
                                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 rounded fw-semibold" style="font-size: 0.75rem;" title="KKTP Checklist: Target minimal <?= $kTarget ?> indikator">
                                                                    <i class="bi bi-check2-square me-1"></i>KKTP: Checklist (Target <?= $kTarget ?> Indikator)
                                                                </span>
                                                            <?php elseif ($kMetode === 'rubrik'): ?>
                                                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1 rounded fw-semibold" style="font-size: 0.75rem;" title="KKTP Rubrik: Ambang batas skor <?= $kMin ?>">
                                                                    <i class="bi bi-ui-checks-grid me-1"></i>KKTP: Rubrik (Min <?= number_format($kMin, 0) ?>)
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-2.5 py-1 rounded fw-semibold" style="font-size: 0.75rem;" title="KKTP Interval Nilai: Ambang batas minimal <?= $kMin ?>">
                                                                    <i class="bi bi-bullseye me-1"></i>KKTP: Batas Nilai (Min <?= number_format($kMin, 0) ?>)
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="tp-deskripsi-wrapper">
                                                            <?= formatTpDescriptionHtml($tp['deskripsi']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-1.5 flex-shrink-0 align-items-center pt-0.5">
                                                        <!-- Tombol Atur KKTP -->
                                                        <button type="button" class="btn btn-sm btn-outline-info rounded-2 p-1.5 px-2.5 btn-kktp fw-semibold d-inline-flex align-items-center gap-1"
                                                            title="Atur Kriteria Ketercapaian Tujuan Pembelajaran (KKTP)"
                                                            data-bs-toggle="modal" data-bs-target="#modalKktp"
                                                            data-id="<?= $tp['id'] ?>"
                                                            data-kode="<?= htmlspecialchars($tp['kode_tp']) ?>"
                                                            data-deskripsi="<?= htmlspecialchars($tp['deskripsi']) ?>"
                                                            data-metode="<?= $kMetode ?>"
                                                            data-nilai-min="<?= $kMin ?>"
                                                            data-target-ind="<?= $kTarget ?>"
                                                            data-kriteria="<?= htmlspecialchars($tp['kktp_kriteria'] ?? '') ?>">
                                                            <i class="bi bi-sliders text-info" style="font-size: 0.85rem;"></i>
                                                            <span style="font-size: 0.78rem;">KKTP</span>
                                                        </button>

                                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-2 p-1.5 px-2 btn-edit-tp" 
                                                            title="Edit TP"
                                                            data-bs-toggle="modal" data-bs-target="#modalEditTP"
                                                            data-id="<?= $tp['id'] ?>"
                                                            data-cp-id="<?= $tp['cp_id'] ?>"
                                                            data-kode="<?= htmlspecialchars($tp['kode_tp']) ?>"
                                                            data-materi="<?= htmlspecialchars($tp['materi_pokok'] ?? '') ?>"
                                                            data-deskripsi="<?= htmlspecialchars($tp['deskripsi']) ?>">
                                                            <i class="bi bi-pencil-fill" style="font-size: 0.82rem;"></i>
                                                        </button>
                                                        <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus / mengarsipkan Tujuan Pembelajaran (TP) ini?');">
                                                            <?= Security::csrfField() ?>
                                                            <input type="hidden" name="action" value="delete_tp">
                                                            <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                                                            <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                                                            <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">
                                                            <input type="hidden" name="id" value="<?= $tp['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-2 p-1.5 px-2" title="Hapus / Arsipkan TP">
                                                                <i class="bi bi-trash-fill" style="font-size: 0.82rem;"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Button to Add TP for this specific CP -->
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold btn-add-tp-for-cp px-3 py-1.5 d-inline-flex align-items-center gap-1.5 mt-1"
                                        data-bs-toggle="modal" data-bs-target="#modalAddTP"
                                        data-cp-id="<?= $cp['id'] ?>"
                                        data-cp-kode="<?= htmlspecialchars($cp['kode_cp']) ?>"
                                        data-cp-mapel="<?= htmlspecialchars($cp['nama_mapel']) ?>"
                                        data-cp-fase="<?= htmlspecialchars($cp['nama_fase'] ?? '') ?>"
                                        data-cp-kurikulum="<?= htmlspecialchars($cp['kode_kurikulum'] ?? '') ?>"
                                        data-cp-elemen="<?= htmlspecialchars($cp['elemen'] ?? '') ?>"
                                        data-cp-deskripsi="<?= htmlspecialchars($cp['deskripsi']) ?>"
                                        title="Rumuskan Tujuan Pembelajaran baru berdasarkan CP ini">
                                        <i class="bi bi-plus-circle"></i> Tambah TP Turunan
                                    </button>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary btn-edit-cp rounded-start-3" 
                                            title="Edit CP"
                                            data-bs-toggle="modal" data-bs-target="#modalEditCP"
                                            data-id="<?= $cp['id'] ?>"
                                            data-kurikulum-id="<?= $cp['kurikulum_id'] ?>"
                                            data-mapel-id="<?= $cp['mapel_id'] ?>"
                                            data-fase-id="<?= $cp['fase_id'] ?? '' ?>"
                                            data-kode="<?= htmlspecialchars($cp['kode_cp']) ?>"
                                            data-elemen="<?= htmlspecialchars($cp['elemen'] ?? '') ?>"
                                            data-deskripsi="<?= htmlspecialchars($cp['deskripsi']) ?>">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST" class="d-inline" onsubmit="return confirm('Hapus Capaian Pembelajaran (CP) ini beserta seluruh TP turunannya? Data asesmen yang merujuk juga akan terdampak.');">
                                            <?= Security::csrfField() ?>
                                            <input type="hidden" name="action" value="delete_cp">
                                            <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                                            <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                                            <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">
                                            <input type="hidden" name="id" value="<?= $cp['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger rounded-end-3" title="Hapus CP">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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
</main>

<!-- =========================================================================== -->
<!-- MODALS CP & TP GURU (ENLARGED, SPACIOUS & PROFESSIONAL) -->
<!-- =========================================================================== -->

<!-- Modal Add CP (Spacious Large Modal) -->
<div class="modal fade" id="modalAddCP" tabindex="-1" aria-labelledby="modalAddCPLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 880px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_cp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">

                <!-- Modal Header -->
                <div class="modal-header py-3.5 px-4 border-bottom" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-2.5 bg-primary text-white shadow-xs">
                            <i class="bi bi-plus-circle-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-bold text-dark mb-0" id="modalAddCPLabel">Tambah Capaian Pembelajaran (CP) Baru</h5>
                                <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size:0.68rem;">Formulir Baru</span>
                            </div>
                            <small class="text-muted">Rumuskan kompetensi akhir mata pelajaran untuk tingkat atau fase kurikulum tertentu.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <!-- Kurikulum -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-mortarboard text-primary me-1"></i>Pilih Kurikulum <span class="text-danger">*</span>
                            </label>
                            <select name="kurikulum_id" id="add_cp_kurikulum_id" class="form-select rounded-3 py-2" required>
                                <?php foreach ($kurikulumList as $kur): ?>
                                    <option value="<?= $kur['id'] ?>" <?= ($filterKurId == $kur['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small text-muted">Kurikulum yang sedang aktif di sekolah.</div>
                        </div>

                        <!-- Mata Pelajaran -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-book text-primary me-1"></i>Mata Pelajaran yang Diampu <span class="text-danger">*</span>
                            </label>
                            <select name="mapel_id" id="add_cp_mapel_id" class="form-select rounded-3 py-2" required>
                                <?php foreach ($teacherMapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>" <?= ($filterMapelId == $mp['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel'] ?? 'MP') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small text-muted">Mata pelajaran yang Anda susun CP-nya.</div>
                        </div>

                        <!-- Fase -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-layers text-primary me-1"></i>Fase / Jenjang Kelas
                            </label>
                            <select name="fase_id" id="add_cp_fase_id" class="form-select rounded-3 py-2">
                                <option value="" data-kurikulum-id="">-- Tanpa Fase Khusus --</option>
                                <?php foreach ($allFaseList as $f): ?>
                                    <option value="<?= $f['id'] ?>" data-kurikulum-id="<?= $f['kurikulum_id'] ?>" <?= ($filterFaseId == $f['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small text-muted">Contoh: Fase E (Kelas X), Fase F (Kelas XI - XII).</div>
                        </div>

                        <!-- Elemen / Ranah -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-tags text-primary me-1"></i>Elemen / Ranah Pembelajaran
                            </label>
                            <input type="text" name="elemen" id="add_cp_elemen" class="form-control rounded-3 py-2" placeholder="Contoh: Pemrograman Dasar / Analisis Data">
                            <div class="form-text small text-muted">Domain materi inti capaian pembelajaran.</div>
                        </div>

                        <!-- Kode CP Auto -->
                        <div class="col-12">
                            <div class="p-3 rounded-3 border bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <label class="form-label small fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                                        <i class="bi bi-upc-scan text-primary"></i>
                                        Kode CP (Terstandar Otomatis)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 ms-1" style="font-size:0.7rem;">Otomatis</span>
                                    </label>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-2" id="btn_regen_cp_code" title="Generate ulang kode CP">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Regenerate Kode
                                    </button>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 font-monospace text-muted"><i class="bi bi-hash"></i></span>
                                    <input type="text" name="kode_cp" id="add_cp_kode" class="form-control font-monospace fw-bold border-start-0 py-2" placeholder="CP-..." value="" required>
                                </div>
                                <div class="form-text small text-muted mt-1">Kode otomatis dibentuk berdasarkan Kurikulum & Mata Pelajaran. Anda dapat menyesuaikannya bila diperlukan.</div>
                            </div>
                        </div>

                        <!-- Deskripsi CP -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-card-text text-primary me-1"></i>Deskripsi Capaian Pembelajaran <span class="text-danger">*</span>
                            </label>
                            <textarea name="deskripsi" id="add_cp_deskripsi" class="form-control rounded-3 p-3" rows="5" required placeholder="Tuliskan rumusan capaian pembelajaran secara lengkap... Contoh: Peserta didik mampu merancang, memprogram, dan menguji solusi perangkat lunak menggunakan bahasa pemrograman terstruktur serta berorientasi objek secara mandiri dan bertanggung jawab."></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="form-text small text-muted">Gunakan bahasa kompetensi yang jelas dan terukur.</span>
                                <span class="form-text small text-muted" id="add_cp_char_count">0 karakter</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer py-3 px-4 border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-3.5 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold shadow-sm d-flex align-items-center gap-1.5">
                        <i class="bi bi-save"></i>
                        <span>Simpan Capaian Pembelajaran</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit CP (Spacious Large Modal) -->
<div class="modal fade" id="modalEditCP" tabindex="-1" aria-labelledby="modalEditCPLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 880px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_cp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">
                <input type="hidden" name="id" id="edit_cp_id">

                <!-- Modal Header -->
                <div class="modal-header py-3.5 px-4 border-bottom" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-2.5 bg-primary text-white shadow-xs">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-bold text-dark mb-0" id="modalEditCPLabel">Perbarui Capaian Pembelajaran (CP)</h5>
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5" style="font-size:0.68rem;">Mode Perubahan Data</span>
                            </div>
                            <small class="text-muted">Edit informasi atau redaksi kompetensi capaian pembelajaran yang telah dibuat.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-mortarboard text-primary me-1"></i>Pilih Kurikulum <span class="text-danger">*</span>
                            </label>
                            <select name="kurikulum_id" id="edit_cp_kurikulum_id" class="form-select rounded-3 py-2" required>
                                <?php foreach ($kurikulumList as $kur): ?>
                                    <option value="<?= $kur['id'] ?>"><?= htmlspecialchars($kur['nama']) ?> (<?= $kur['kode'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-book text-primary me-1"></i>Mata Pelajaran <span class="text-danger">*</span>
                            </label>
                            <select name="mapel_id" id="edit_cp_mapel_id" class="form-select rounded-3 py-2" required>
                                <?php foreach ($teacherMapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kode_mapel'] ?? 'MP') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-layers text-primary me-1"></i>Fase / Jenjang Kelas
                            </label>
                            <select name="fase_id" id="edit_cp_fase_id" class="form-select rounded-3 py-2">
                                <option value="" data-kurikulum-id="">-- Tanpa Fase Khusus --</option>
                                <?php foreach ($allFaseList as $f): ?>
                                    <option value="<?= $f['id'] ?>" data-kurikulum-id="<?= $f['kurikulum_id'] ?>">
                                        <?= htmlspecialchars($f['nama']) ?> (<?= $f['nama_kurikulum'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-tags text-primary me-1"></i>Elemen / Ranah Pembelajaran
                            </label>
                            <input type="text" name="elemen" id="edit_cp_elemen" class="form-control rounded-3 py-2" placeholder="Elemen ranah materi">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-upc-scan text-primary me-1"></i>Kode CP <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="kode_cp" id="edit_cp_kode" class="form-control font-monospace fw-bold rounded-3 py-2" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-card-text text-primary me-1"></i>Deskripsi Capaian Pembelajaran <span class="text-danger">*</span>
                            </label>
                            <textarea name="deskripsi" id="edit_cp_deskripsi" class="form-control rounded-3 p-3" rows="5" required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer py-3 px-4 border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-3.5 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold shadow-sm d-flex align-items-center gap-1.5">
                        <i class="bi bi-check2-circle"></i>
                        <span>Perbarui Capaian Pembelajaran</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add TP (With COMPLETE Parent CP Reference Card & Clean Inputs) -->
<div class="modal fade" id="modalAddTP" tabindex="-1" aria-labelledby="modalAddTPLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 880px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create_tp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">

                <!-- Modal Header -->
                <div class="modal-header py-3.5 px-4 border-bottom" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-2.5 bg-success text-white shadow-xs">
                            <i class="bi bi-plus-circle-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-bold text-dark mb-0" id="modalAddTPLabel">Tambah Tujuan Pembelajaran (TP) Baru</h5>
                                <span class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size:0.68rem;">Formulir Baru</span>
                            </div>
                            <small class="text-muted">Rumuskan tujuan pembelajaran spesifik sebagai turunan operasional dari Capaian Pembelajaran terpilih.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <!-- Step 1: Parent CP Selection -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1.5 d-flex align-items-center gap-1">
                            <i class="bi bi-diagram-3-fill text-success"></i>
                            <span>Pilih Induk Capaian Pembelajaran (CP) Acuan:</span>
                            <span class="text-danger">*</span>
                        </label>
                        <select name="cp_id" id="add_tp_cp_id" class="form-select rounded-3 py-2 border-primary-subtle" required>
                            <?php 
                            $groupedCpAdd = [];
                            foreach (($allCpForDropdown ?? $cpList) as $c) {
                                $grpKey = ($c['nama_kurikulum'] ?? 'Kurikulum') . ' — ' . ($c['nama_mapel'] ?? 'Mapel');
                                $groupedCpAdd[$grpKey][] = $c;
                            }
                            foreach ($groupedCpAdd as $grpName => $cList): ?>
                                <optgroup label="<?= htmlspecialchars($grpName) ?>">
                                    <?php foreach ($cList as $c): ?>
                                        <option value="<?= $c['id'] ?>" 
                                                data-kode="<?= htmlspecialchars($c['kode_cp']) ?>"
                                                data-mapel="<?= htmlspecialchars($c['nama_mapel'] ?? '') ?>"
                                                data-kurikulum="<?= htmlspecialchars($c['nama_kurikulum'] ?? '') ?> (<?= htmlspecialchars($c['kode_kurikulum'] ?? '') ?>)"
                                                data-fase="<?= htmlspecialchars($c['nama_fase'] ?? '') ?>"
                                                data-elemen="<?= htmlspecialchars($c['elemen'] ?? '') ?>"
                                                data-deskripsi="<?= htmlspecialchars($c['deskripsi']) ?>">
                                            [<?= htmlspecialchars($c['kode_cp']) ?>] <?= htmlspecialchars($c['nama_mapel'] ?? '') ?> — <?= htmlspecialchars(mb_strimwidth($c['deskripsi'], 0, 75, '...')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Step 2: COMPLETE Parent CP Reference Card ("Kumplit & Terlihat") -->
                    <div class="card border border-primary border-opacity-25 rounded-3 mb-3.5 bg-light overflow-hidden shadow-xs">
                        <div class="card-header bg-primary bg-opacity-10 py-2.5 px-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-primary text-white font-monospace px-2.5 py-1.5 shadow-xs" id="preview_cp_kode">CP-...</span>
                                <span class="fw-bold text-dark fs-6" id="preview_cp_mapel">Mata Pelajaran</span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill small" id="preview_cp_fase" style="display: none;">Fase</span>
                                <span class="badge bg-white text-dark border px-2.5 py-1 rounded-pill small" id="preview_cp_elemen" style="display: none;">Elemen</span>
                            </div>
                        </div>
                        <div class="card-body p-3 bg-white">
                            <div class="small fw-bold text-primary text-uppercase mb-2 d-flex align-items-center gap-1.5" style="font-size:0.75rem; letter-spacing:0.5px;">
                                <i class="bi bi-journal-text fs-6"></i>
                                <span>Rumusan Capaian Pembelajaran (CP) Acuan:</span>
                            </div>
                            <div class="p-3.5 rounded-3 text-dark border border-primary-subtle" id="preview_cp_deskripsi" style="line-height: 1.75; font-size: 0.93rem; background-color: #f8fafc; white-space: pre-wrap; word-break: break-word;">
                                Pilih Capaian Pembelajaran (CP) di atas untuk menampilkan rumusan capaian acuan...
                            </div>
                            <div class="text-secondary small mt-2 d-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                                <i class="bi bi-info-circle-fill text-primary"></i>
                                <span>Gunakan capaian di atas sebagai pedoman kompetensi dasar untuk merumuskan butir-butir <strong>Tujuan Pembelajaran (TP) turunan</strong> pada formulir baru di bawah ini:</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Clean New TP Input Card -->
                    <div class="card border border-success border-opacity-25 rounded-3 p-3 bg-white shadow-xs">
                        <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
                            <span class="fw-bold text-success d-flex align-items-center gap-1.5 small text-uppercase" style="letter-spacing: 0.5px;">
                                <i class="bi bi-file-earmark-plus-fill"></i> Formulir Input TP Baru (Ketik Data Di Sini)
                            </span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size:0.7rem;">
                                Input Bersih / Siap Diisi
                            </span>
                        </div>

                        <div class="row g-3">
                            <!-- Kode TP Auto -->
                            <div class="col-12 col-md-5">
                                <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                                    <i class="bi bi-upc-scan text-success"></i> Kode TP (Otomatis)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-1.5 py-0.5 ms-1" style="font-size:0.68rem;">Otomatis</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 font-monospace text-muted"><i class="bi bi-hash"></i></span>
                                    <input type="text" name="kode_tp" id="add_tp_kode" class="form-control font-monospace fw-bold border-start-0 py-2" placeholder="TP-..." value="" required>
                                    <button type="button" class="btn btn-outline-secondary" id="btn_regen_tp_code" title="Generate ulang kode TP">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                                <div class="form-text small text-muted mt-1" style="font-size:0.75rem;">Turunan terstandar dari kode CP induk.</div>
                            </div>

                            <!-- Materi Pokok -->
                            <div class="col-12 col-md-7">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    <i class="bi bi-bookmark text-success me-1"></i>Materi Pokok / Pokok Bahasan
                                </label>
                                <input type="text" name="materi_pokok" id="add_tp_materi" class="form-control rounded-3 py-2" placeholder="Ketik topik atau materi pokok baru...">
                                <div class="form-text small text-muted mt-1" style="font-size:0.75rem;">Subjek materi pembelajaran yang akan diujikan.</div>
                            </div>

                            <!-- Deskripsi TP -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-2">
                                    <label class="form-label small fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                                        <i class="bi bi-card-text text-success me-1"></i>Deskripsi Rumusan Tujuan Pembelajaran (TP) Baru <span class="text-danger">*</span>
                                    </label>
                                    <!-- Quick List Formatting Toolbar -->
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="text-muted small me-1" style="font-size:0.72rem;">Format List:</span>
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Format List TP">
                                            <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('add_tp_deskripsi', 'number')" title="Daftar Bernomor (1., 2., 3.)">
                                                <i class="bi bi-list-ol"></i> 1. 2. 3.
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('add_tp_deskripsi', 'letter')" title="Daftar Berhuruf (a., b., c.)">
                                                <i class="bi bi-fonts"></i> a. b. c.
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('add_tp_deskripsi', 'bullet')" title="Daftar Simbol Bullet (•)">
                                                <i class="bi bi-list-ul"></i> • Bullet
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('add_tp_deskripsi', 'dash')" title="Daftar Simbol Strip (-)">
                                                <i class="bi bi-dash"></i> - Strip
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('add_tp_deskripsi', 'check')" title="Daftar Simbol Centang (✓)">
                                                <i class="bi bi-check2-square"></i> ✓ Cek
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('add_tp_deskripsi', 'arrow')" title="Daftar Simbol Panah (→)">
                                                <i class="bi bi-arrow-right"></i> → Panah
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <textarea name="deskripsi" id="add_tp_deskripsi" class="form-control rounded-3 p-3" rows="5" required placeholder="Tuliskan rumusan Tujuan Pembelajaran baru di sini... Anda dapat menuliskan butir berupa list (1., 2. / a., b. / •, -)..."></textarea>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="form-text small text-muted"><i class="bi bi-lightbulb text-warning me-1"></i>Tekan <strong>Enter</strong> pada baris list untuk otomatis melanjutkan nomor/huruf/simbol list berikutnya.</span>
                                    <span class="form-text small text-muted" id="add_tp_char_count">0 karakter</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer py-3 px-4 border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-3.5 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-semibold shadow-sm d-flex align-items-center gap-1.5">
                        <i class="bi bi-plus-circle"></i>
                        <span>Simpan TP Baru</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit TP (Spacious Large Modal - Visually Distinct Warning Theme) -->
<div class="modal fade" id="modalEditTP" tabindex="-1" aria-labelledby="modalEditTPLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 880px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_tp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">
                <input type="hidden" name="id" id="edit_tp_id">

                <!-- Modal Header (Distinct Amber Theme for Editing) -->
                <div class="modal-header py-3.5 px-4 border-bottom" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-2.5 bg-warning text-dark shadow-xs">
                            <i class="bi bi-pencil-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-bold text-dark mb-0" id="modalEditTPLabel">Edit / Perbarui Data TP</h5>
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5" style="font-size:0.68rem;">Mode Perubahan Data</span>
                            </div>
                            <small class="text-muted">Perbarui rumusan atau materi pokok pada data Tujuan Pembelajaran yang sudah ada.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1.5">
                            <i class="bi bi-diagram-3 text-warning me-1"></i>Pilih Induk Capaian Pembelajaran (CP) <span class="text-danger">*</span>
                        </label>
                        <select name="cp_id" id="edit_tp_cp_id" class="form-select rounded-3 py-2" required>
                            <?php foreach ($groupedCpAdd as $grpName => $cList): ?>
                                <optgroup label="<?= htmlspecialchars($grpName) ?>">
                                    <?php foreach ($cList as $c): ?>
                                        <option value="<?= $c['id'] ?>"
                                                data-kode="<?= htmlspecialchars($c['kode_cp']) ?>"
                                                data-mapel="<?= htmlspecialchars($c['nama_mapel'] ?? '') ?>"
                                                data-fase="<?= htmlspecialchars($c['nama_fase'] ?? '') ?>"
                                                data-elemen="<?= htmlspecialchars($c['elemen'] ?? '') ?>"
                                                data-deskripsi="<?= htmlspecialchars($c['deskripsi']) ?>">
                                            [<?= htmlspecialchars($c['kode_cp']) ?>] <?= htmlspecialchars($c['nama_mapel'] ?? '') ?> — <?= htmlspecialchars(mb_strimwidth($c['deskripsi'], 0, 75, '...')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Parent CP Reference Card in Edit Modal -->
                    <div class="card border border-warning border-opacity-25 rounded-3 mb-3.5 bg-light overflow-hidden shadow-xs">
                        <div class="card-header bg-warning bg-opacity-10 py-2.5 px-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-warning text-dark font-monospace px-2.5 py-1.5 shadow-xs" id="edit_preview_cp_kode">CP-...</span>
                                <span class="fw-bold text-dark fs-6" id="edit_preview_cp_mapel">Mata Pelajaran</span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <span class="badge bg-white text-dark border px-2.5 py-1 rounded-pill small" id="edit_preview_cp_elemen" style="display: none;">Elemen</span>
                            </div>
                        </div>
                        <div class="card-body p-3 bg-white">
                            <div class="small fw-bold text-warning-emphasis text-uppercase mb-1.5" style="font-size:0.75rem; letter-spacing:0.5px;">
                                <i class="bi bi-journal-text me-1"></i>Rumusan Capaian Pembelajaran (CP) Acuan:
                            </div>
                            <div class="p-3.5 rounded-3 text-dark border border-warning-subtle" id="edit_preview_cp_deskripsi" style="line-height: 1.7; font-size: 0.92rem; background-color: #fffdf5; white-space: pre-wrap; word-break: break-word;">
                                Memuat CP acuan...
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-upc-scan text-warning me-1"></i>Kode TP <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="kode_tp" id="edit_tp_kode" class="form-control font-monospace fw-bold rounded-3 py-2" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary mb-1.5">
                                <i class="bi bi-bookmark text-warning me-1"></i>Materi Pokok
                            </label>
                            <input type="text" name="materi_pokok" id="edit_tp_materi" class="form-control rounded-3 py-2">
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-2">
                                <label class="form-label small fw-bold text-secondary mb-0 d-flex align-items-center gap-1">
                                    <i class="bi bi-card-text text-warning me-1"></i>Deskripsi Tujuan Pembelajaran <span class="text-danger">*</span>
                                </label>
                                <!-- Quick List Formatting Toolbar -->
                                <div class="d-flex align-items-center gap-1">
                                    <span class="text-muted small me-1" style="font-size:0.72rem;">Format List:</span>
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Format List TP">
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('edit_tp_deskripsi', 'number')" title="Daftar Bernomor (1., 2., 3.)">
                                            <i class="bi bi-list-ol"></i> 1. 2. 3.
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('edit_tp_deskripsi', 'letter')" title="Daftar Berhuruf (a., b., c.)">
                                            <i class="bi bi-fonts"></i> a. b. c.
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('edit_tp_deskripsi', 'bullet')" title="Daftar Simbol Bullet (•)">
                                            <i class="bi bi-list-ul"></i> • Bullet
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('edit_tp_deskripsi', 'dash')" title="Daftar Simbol Strip (-)">
                                            <i class="bi bi-dash"></i> - Strip
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('edit_tp_deskripsi', 'check')" title="Daftar Simbol Centang (✓)">
                                            <i class="bi bi-check2-square"></i> ✓ Cek
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="insertTpListFormat('edit_tp_deskripsi', 'arrow')" title="Daftar Simbol Panah (→)">
                                            <i class="bi bi-arrow-right"></i> → Panah
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <textarea name="deskripsi" id="edit_tp_deskripsi" class="form-control rounded-3 p-3" rows="5" required placeholder="Edit rumusan tujuan pembelajaran..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer py-3 px-4 border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-3.5 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-3 px-4 py-2 fw-bold text-dark shadow-sm d-flex align-items-center gap-1.5">
                        <i class="bi bi-check2-circle"></i>
                        <span>Simpan Perubahan TP</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =========================================================================== -->
<!-- MODAL ATUR KKTP (KRITERIA KETERCAPAIAN TUJUAN PEMBELAJARAN) -->
<!-- =========================================================================== -->
<div class="modal fade" id="modalKktp" tabindex="-1" aria-labelledby="modalKktpLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 820px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST" id="formKktp">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="save_kktp">
                <input type="hidden" name="tp_id" id="kktp_tp_id" value="">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">

                <!-- Modal Header -->
                <div class="modal-header bg-info bg-opacity-10 py-3.5 px-4 border-bottom border-info-subtle">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-info text-white p-2.5 d-flex align-items-center justify-content-center shadow-xs" style="width: 44px; height: 44px;">
                            <i class="bi bi-sliders fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="modalKktpLabel">Konfigurasi KKTP (Kriteria Ketercapaian TP)</h5>
                            <p class="text-muted small mb-0">Tentukan kriteria ketercapaian secara terukur (Batas Nilai, Rubrik, atau Checklist Indikator).</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4 bg-light">
                    <!-- TP Summary Header Card -->
                    <div class="card border border-info border-opacity-25 rounded-3 mb-3 bg-white shadow-xs">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-info text-dark font-monospace fw-bold" id="kktp_preview_tp_kode">TP-...</span>
                                <span class="text-muted small fw-semibold">Tujuan Pembelajaran yang Dikonfigurasi</span>
                            </div>
                            <div class="text-dark small fw-medium" id="kktp_preview_tp_desc" style="line-height: 1.6;">
                                Memuat ringkasan TP...
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Metode KKTP -->
                    <div class="card border-0 rounded-3 shadow-xs mb-3 bg-white p-3">
                        <label class="form-label small fw-bold text-secondary mb-2 d-flex align-items-center gap-1.5">
                            <i class="bi bi-diagram-3-fill text-info"></i> Pilih Pendekatan / Metode KKTP:
                        </label>
                        <div class="row g-2">
                            <div class="col-12 col-md-4">
                                <label class="card h-100 p-2.5 border rounded-3 cursor-pointer kktp-method-card active" for="metode_interval" id="card_metode_interval" style="cursor: pointer;">
                                    <div class="d-flex align-items-start gap-2">
                                        <input class="form-check-input mt-1" type="radio" name="metode" id="metode_interval" value="interval_nilai" checked>
                                        <div>
                                            <div class="fw-bold small text-dark">A. Interval Nilai</div>
                                            <div class="text-muted" style="font-size: 0.72rem; line-height: 1.4;">Batas angka minimum (misal: 75.00). Nilai di atas batas = Tercapai (1).</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="card h-100 p-2.5 border rounded-3 cursor-pointer kktp-method-card" for="metode_rubrik" id="card_metode_rubrik" style="cursor: pointer;">
                                    <div class="d-flex align-items-start gap-2">
                                        <input class="form-check-input mt-1" type="radio" name="metode" id="metode_rubrik" value="rubrik">
                                        <div>
                                            <div class="fw-bold small text-dark">B. Rubrik Kriteria</div>
                                            <div class="text-muted" style="font-size: 0.72rem; line-height: 1.4;">Rubrik berjenjang (Mahir, Cakap, Layak, Baru Berkembang).</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="card h-100 p-2.5 border rounded-3 cursor-pointer kktp-method-card" for="metode_checklist" id="card_metode_checklist" style="cursor: pointer;">
                                    <div class="d-flex align-items-start gap-2">
                                        <input class="form-check-input mt-1" type="radio" name="metode" id="metode_checklist" value="checklist">
                                        <div>
                                            <div class="fw-bold small text-dark">C. Checklist Indikator</div>
                                            <div class="text-muted" style="font-size: 0.72rem; line-height: 1.4;">Target jumlah indikator tercapai (misal: min 3 dari 4 indikator).</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Config Section: Interval Nilai -->
                    <div id="section_interval_nilai" class="card border-0 rounded-3 shadow-xs mb-3 bg-white p-3">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">
                                    <i class="bi bi-bullseye text-success me-1"></i>Nilai Batas Minimum Ketuntasan (0 - 100) <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="0.5" min="0" max="100" name="nilai_minimum" id="kktp_nilai_minimum" class="form-control fw-bold font-monospace text-primary fs-5 py-2" value="75.00">
                                <div class="form-text text-muted small">Siswa dengan nilai &ge; ambang ini otomatis tercapai (Status: 1).</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Deskripsi Kriteria Ketuntasan</label>
                                <input type="text" name="deskripsi_kriteria" id="kktp_deskripsi_kriteria_interval" class="form-control py-2" placeholder="Contoh: Memahami konsep dasar minimal 75%">
                            </div>
                        </div>
                    </div>

                    <!-- Config Section: Rubrik -->
                    <div id="section_rubrik" class="card border-0 rounded-3 shadow-xs mb-3 bg-white p-3" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">
                                    <i class="bi bi-ui-checks-grid text-info me-1"></i>Skor Minimal Kriteria Tuntas <span class="text-danger">*</span>
                                </label>
                                <input type="number" step="1" min="0" max="100" name="rubrik_nilai_min" id="kktp_rubrik_nilai_min" class="form-control fw-bold font-monospace text-info fs-5 py-2" value="75">
                                <div class="form-text text-muted small">Ambang batas skor akumulasi rubrik untuk predikat minimal Layak/Cakap.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary mb-1">Deskripsi Kriteria Jenjang Rubrik</label>
                                <textarea name="rubrik_deskripsi" id="kktp_rubrik_deskripsi" class="form-control" rows="3" placeholder="Contoh:&#10;- Mahir (&ge;85): Mampu menjelaskan dan mempraktikkan secara mandiri&#10;- Cakap (75-84): Mampu menjelaskan dengan baik&#10;- Perlu Bimbingan (<75): Masih membutuhkan arahan intensif"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Config Section: Checklist Indikator -->
                    <div id="section_checklist" class="card border-0 rounded-3 shadow-xs mb-3 bg-white p-3" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div>
                                <label class="form-label small fw-bold text-secondary mb-0">
                                    <i class="bi bi-check2-square text-warning-emphasis me-1"></i>Target Minimal Indikator yang Wajib Tercapai <span class="text-danger">*</span>
                                </label>
                                <div class="text-muted small">Berapa banyak indikator yang harus dicapai siswa agar TP dinyatakan tuntas (1)?</div>
                            </div>
                            <div style="width: 120px;">
                                <input type="number" min="1" max="20" name="target_indikator_count" id="kktp_target_indikator_count" class="form-control text-center fw-bold font-monospace" value="3">
                            </div>
                        </div>

                        <hr class="my-2.5">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-secondary">Rincian Indikator Ketercapaian TP:</span>
                            <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2.5 rounded-pill d-flex align-items-center gap-1" id="btnTambahIndikator">
                                <i class="bi bi-plus-circle"></i> + Tambah Indikator
                            </button>
                        </div>

                        <div id="indikatorListWrapper" class="d-flex flex-column gap-2">
                            <!-- Dynamic Indicator Rows will be appended here -->
                        </div>
                    </div>

                    <!-- Historical Stability Notice -->
                    <div class="alert alert-light border border-info-subtle rounded-3 py-2 px-3 small text-muted d-flex align-items-center gap-2 mb-0">
                        <i class="bi bi-shield-check text-info fs-5 flex-shrink-0"></i>
                        <div>
                            <strong>Perlindungan Histori Penilaian:</strong> Jika KKTP ini diubah di kemudian hari sementara sudah ada asesmen terdahulu yang dinilai, sistem otomatis membuat <em>versi baru</em> sehingga nilai siswa di semester/tahun lalu tetap aman dan tidak berubah.
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-white py-3 px-4 border-top">
                    <button type="button" class="btn btn-light border px-4 py-2 fw-semibold rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-dark fw-bold px-4 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i> Simpan Konfigurasi KKTP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =========================================================================== -->
<!-- MODAL SALIN TP DARI BANK TP (COPY TP FROM PREVIOUS CP) -->
<!-- =========================================================================== -->
<div class="modal fade" id="modalCopyTP" tabindex="-1" aria-labelledby="modalCopyTPLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 760px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="<?= BASE_URL ?>index.php?url=guru/cptp" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="copy_tp">
                <input type="hidden" name="filter_kurikulum_id" value="<?= $filterKurId ?? '' ?>">
                <input type="hidden" name="filter_mapel_id" value="<?= $filterMapelId ?? '' ?>">
                <input type="hidden" name="filter_fase_id" value="<?= $filterFaseId ?? '' ?>">

                <!-- Modal Header -->
                <div class="modal-header bg-success bg-opacity-10 py-3.5 px-4 border-bottom border-success-subtle">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-success text-white p-2.5 d-flex align-items-center justify-content-center shadow-xs" style="width: 44px; height: 44px;">
                            <i class="bi bi-box-arrow-in-down fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="modalCopyTPLabel">Salin Tujuan Pembelajaran (Bank TP)</h5>
                            <p class="text-muted small mb-0">Duplikasi rumusan TP beserta konfigurasi KKTP dari CP sumber ke CP target pembelajaran.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4 bg-light">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-1">
                                <i class="bi bi-box-arrow-up-right text-primary me-1"></i>Pilih Capaian Pembelajaran (CP) Sumber <span class="text-danger">*</span>
                            </label>
                            <select name="source_cp_id" id="copy_source_cp_id" class="form-select rounded-3 py-2" required>
                                <option value="">-- Pilih CP Sumber yang memiliki TP --</option>
                                <?php foreach (($allCpForDropdown ?? $cpList) as $c): ?>
                                    <option value="<?= $c['id'] ?>">
                                        [<?= htmlspecialchars($c['kode_cp']) ?>] <?= htmlspecialchars($c['nama_mapel']) ?> - <?= htmlspecialchars($c['elemen'] ?? 'Umum') ?> (<?= $c['total_tp'] ?? 0 ?> TP)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted small">Pilih CP acuan yang sudah memiliki rumusan Tujuan Pembelajaran terstruktur.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-1">
                                <i class="bi bi-box-arrow-in-down text-success me-1"></i>Pilih Capaian Pembelajaran (CP) Target <span class="text-danger">*</span>
                            </label>
                            <select name="target_cp_id" id="copy_target_cp_id" class="form-select rounded-3 py-2" required>
                                <option value="">-- Pilih CP Tujuan Penerima TP --</option>
                                <?php foreach ($cpList as $c): ?>
                                    <option value="<?= $c['id'] ?>">
                                        [<?= htmlspecialchars($c['kode_cp']) ?>] <?= htmlspecialchars($c['nama_mapel']) ?> - <?= htmlspecialchars($c['elemen'] ?? 'Umum') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted small">Tujuan Pembelajaran akan disalin ke CP ini tanpa menimpa kode TP yang sudah ada.</div>
                        </div>
                    </div>

                    <div class="alert alert-light border border-success-subtle rounded-3 p-3 small text-muted mt-3 mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill text-success fs-5 flex-shrink-0"></i>
                        <div>
                            Fitur ini memudahkan Bapak/Ibu Guru menggunakan kembali rumusan TP dan KKTP yang telah teruji pada tahun ajaran/semester sebelumnya secara instan.
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-white py-3 px-4 border-top">
                    <button type="button" class="btn btn-light border px-4 py-2 fw-semibold rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2">
                        <i class="bi bi-clipboard-check-fill"></i> Salin Tujuan Pembelajaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Build compact lookup maps for JS: keyed by ID
$cpDataForJs = [];
$combinedCp = array_merge($cpList ?? [], $allCpForDropdown ?? []);
foreach ($combinedCp as $cp) {
    if (isset($cp['id'])) {
        $cpDataForJs[(int)$cp['id']] = [
            'id'           => (int)$cp['id'],
            'kurikulum_id' => (int)$cp['kurikulum_id'],
            'mapel_id'     => (int)$cp['mapel_id'],
            'fase_id'      => !empty($cp['fase_id']) ? (int)$cp['fase_id'] : '',
            'kode_cp'      => (string)$cp['kode_cp'],
            'elemen'       => (string)($cp['elemen'] ?? ''),
            'deskripsi'    => (string)$cp['deskripsi'],
        ];
    }
}
$tpDataForJs = [];
if (!empty($tpList)) {
    foreach ($tpList as $tp) {
        if (isset($tp['id'])) {
            $tpDataForJs[(int)$tp['id']] = [
                'id'           => (int)$tp['id'],
                'cp_id'        => (int)$tp['cp_id'],
                'kode_tp'      => (string)$tp['kode_tp'],
                'materi_pokok' => (string)($tp['materi_pokok'] ?? ''),
                'deskripsi'    => (string)$tp['deskripsi'],
            ];
        }
    }
}
?>
<script>
// Embedded data lookup maps (avoids HTML data attribute encoding issues with long text/newlines)
const _cpData = <?= json_encode($cpDataForJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?: '{}' ?>;
const _tpData = <?= json_encode($tpDataForJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?: '{}' ?>;

// Quick List Toolbar Formatter
function insertTpListFormat(textareaId, type) {
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;

    let prefix = '1. ';
    if (type === 'letter') prefix = 'a. ';
    else if (type === 'bullet') prefix = '• ';
    else if (type === 'dash') prefix = '- ';
    else if (type === 'check') prefix = '✓ ';
    else if (type === 'arrow') prefix = '→ ';

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const val = textarea.value;

    if (start === end) {
        const before = val.substring(0, start);
        const after = val.substring(end);
        const needsNewline = before.length > 0 && !before.endsWith('\n');
        const insertText = (needsNewline ? '\n' : '') + prefix;
        textarea.value = before + insertText + after;
        textarea.selectionStart = textarea.selectionEnd = start + insertText.length;
    } else {
        const selectedText = val.substring(start, end);
        const lines = selectedText.split('\n');
        let counter = 1;
        let letterCode = 97; // 'a'
        const formatted = lines.map(line => {
            if (line.trim() === '') return line;
            if (type === 'number') return (counter++) + '. ' + line.replace(/^(\d+[\.\)\-]|[a-zA-Z][\.\)]|[•\-\*✓▪→\u2022\u25AA\u2713])\s*/u, '');
            if (type === 'letter') return String.fromCharCode(letterCode++) + '. ' + line.replace(/^(\d+[\.\)\-]|[a-zA-Z][\.\)]|[•\-\*✓▪→\u2022\u25AA\u2713])\s*/u, '');
            return prefix + line.replace(/^(\d+[\.\)\-]|[a-zA-Z][\.\)]|[•\-\*✓▪→\u2022\u25AA\u2713])\s*/u, '');
        }).join('\n');

        textarea.value = val.substring(0, start) + formatted + val.substring(end);
        textarea.selectionStart = start;
        textarea.selectionEnd = start + formatted.length;
    }
    textarea.focus();
    textarea.dispatchEvent(new Event('input'));
}

// Smart Enter List Continuation
function setupSmartListTextarea(textareaId) {
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;

    textarea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            const cursor = this.selectionStart;
            const text = this.value;
            const lastLineBreak = text.lastIndexOf('\n', cursor - 1);
            const lineStart = lastLineBreak === -1 ? 0 : lastLineBreak + 1;
            const currentLine = text.substring(lineStart, cursor);

            // Match Number: e.g. "1. " or "1) "
            const matchNum = currentLine.match(/^(\d+)[\.\)]\s*(.*)$/);
            if (matchNum) {
                e.preventDefault();
                const num = parseInt(matchNum[1], 10);
                const content = matchNum[2];
                if (content.trim() === '') {
                    this.value = text.substring(0, lineStart) + text.substring(cursor);
                    this.selectionStart = this.selectionEnd = lineStart;
                } else {
                    const nextItem = '\n' + (num + 1) + '. ';
                    this.value = text.substring(0, cursor) + nextItem + text.substring(cursor);
                    this.selectionStart = this.selectionEnd = cursor + nextItem.length;
                }
                this.dispatchEvent(new Event('input'));
                return;
            }

            // Match Letter: e.g. "a. " or "A. "
            const matchLetter = currentLine.match(/^([a-zA-Z])[\.\)]\s*(.*)$/);
            if (matchLetter) {
                e.preventDefault();
                const charCode = matchLetter[1].charCodeAt(0);
                const content = matchLetter[2];
                if (content.trim() === '') {
                    this.value = text.substring(0, lineStart) + text.substring(cursor);
                    this.selectionStart = this.selectionEnd = lineStart;
                } else {
                    const isUpper = matchLetter[1] === matchLetter[1].toUpperCase();
                    let nextChar = String.fromCharCode(charCode + 1);
                    if (isUpper && charCode === 90) nextChar = 'A';
                    if (!isUpper && charCode === 122) nextChar = 'a';
                    const nextItem = '\n' + nextChar + '. ';
                    this.value = text.substring(0, cursor) + nextItem + text.substring(cursor);
                    this.selectionStart = this.selectionEnd = cursor + nextItem.length;
                }
                this.dispatchEvent(new Event('input'));
                return;
            }

            // Match Bullet / Symbol: e.g. "• ", "- ", "* ", "✓ ", "→ "
            const matchSymbol = currentLine.match(/^([•\-\*✓▪▫►▶→➔➢+~–—\u2022\u25aa\u2713])\s*(.*)$/u);
            if (matchSymbol) {
                e.preventDefault();
                const sym = matchSymbol[1];
                const content = matchSymbol[2];
                if (content.trim() === '') {
                    this.value = text.substring(0, lineStart) + text.substring(cursor);
                    this.selectionStart = this.selectionEnd = lineStart;
                } else {
                    const nextItem = '\n' + sym + ' ';
                    this.value = text.substring(0, cursor) + nextItem + text.substring(cursor);
                    this.selectionStart = this.selectionEnd = cursor + nextItem.length;
                }
                this.dispatchEvent(new Event('input'));
                return;
            }
        }
    });
}

// Next Code Maps for Auto-generation
const nextCpCodeMap = <?= json_encode($nextCpCodeMap ?? []) ?>;
const nextTpCodeMap = <?= json_encode($nextTpCodeMap ?? []) ?>;

function updateAutoCpCode() {
    const kurId = document.getElementById('add_cp_kurikulum_id')?.value;
    const mapelId = document.getElementById('add_cp_mapel_id')?.value;
    const kodeInp = document.getElementById('add_cp_kode');
    if (kodeInp && kurId && mapelId) {
        if (nextCpCodeMap[kurId] && nextCpCodeMap[kurId][mapelId]) {
            kodeInp.value = nextCpCodeMap[kurId][mapelId];
        }
    }
}

function updateAutoTpCode() {
    const cpId = document.getElementById('add_tp_cp_id')?.value;
    const kodeInp = document.getElementById('add_tp_kode');
    if (kodeInp && cpId) {
        if (nextTpCodeMap[cpId]) {
            kodeInp.value = nextTpCodeMap[cpId];
        }
    }
    updateParentCpPreview();
}

function updateParentCpPreview() {
    const cpSelect = document.getElementById('add_tp_cp_id');
    const previewKode = document.getElementById('preview_cp_kode');
    const previewMapel = document.getElementById('preview_cp_mapel');
    const previewFase = document.getElementById('preview_cp_fase');
    const previewElemen = document.getElementById('preview_cp_elemen');
    const previewDeskripsi = document.getElementById('preview_cp_deskripsi');

    if (!cpSelect) return;

    const opt = cpSelect.options[cpSelect.selectedIndex];
    if (opt) {
        const kode = opt.getAttribute('data-kode') || (opt.text.match(/^\[(.*?)\]/) ? opt.text.match(/^\[(.*?)\]/)[1] : 'CP Induk');
        const mapel = opt.getAttribute('data-mapel') || '';
        const fase = opt.getAttribute('data-fase') || '';
        const elem = opt.getAttribute('data-elemen') || '';
        const desk = opt.getAttribute('data-deskripsi') || opt.text || '';

        if (previewKode) previewKode.textContent = kode;
        if (previewMapel) previewMapel.textContent = mapel ? mapel : 'Mata Pelajaran';
        
        if (previewFase) {
            if (fase) {
                previewFase.textContent = fase;
                previewFase.style.display = 'inline-block';
            } else {
                previewFase.style.display = 'none';
            }
        }

        if (previewElemen) {
            if (elem) {
                previewElemen.textContent = 'Elemen: ' + elem;
                previewElemen.style.display = 'inline-block';
            } else {
                previewElemen.style.display = 'none';
            }
        }

        if (previewDeskripsi) previewDeskripsi.textContent = desk ? desk : 'Deskripsi CP belum tersedia.';
    }
}

function updateEditParentCpPreview() {
    const cpSelect = document.getElementById('edit_tp_cp_id');
    const previewKode = document.getElementById('edit_preview_cp_kode');
    const previewMapel = document.getElementById('edit_preview_cp_mapel');
    const previewElemen = document.getElementById('edit_preview_cp_elemen');
    const previewDeskripsi = document.getElementById('edit_preview_cp_deskripsi');

    if (!cpSelect) return;

    const opt = cpSelect.options[cpSelect.selectedIndex];
    if (opt) {
        const kode = opt.getAttribute('data-kode') || (opt.text.match(/^\[(.*?)\]/) ? opt.text.match(/^\[(.*?)\]/)[1] : 'CP Induk');
        const mapel = opt.getAttribute('data-mapel') || '';
        const elem = opt.getAttribute('data-elemen') || '';
        const desk = opt.getAttribute('data-deskripsi') || opt.text || '';

        if (previewKode) previewKode.textContent = kode;
        if (previewMapel) previewMapel.textContent = mapel ? mapel : 'Mata Pelajaran';
        if (previewElemen) {
            if (elem) {
                previewElemen.textContent = 'Elemen: ' + elem;
                previewElemen.style.display = 'inline-block';
            } else {
                previewElemen.style.display = 'none';
            }
        }
        if (previewDeskripsi) previewDeskripsi.textContent = desk ? desk : 'Deskripsi CP belum tersedia.';
    }
}

function filterFaseDropdown(kurikulumSelectId, faseSelectId) {
    const kurSel = document.getElementById(kurikulumSelectId);
    const faseSel = document.getElementById(faseSelectId);
    if (!kurSel || !faseSel) return;

    const selectedKurId = kurSel.value;
    const options = faseSel.querySelectorAll('option');

    let currentlySelectedStillValid = false;

    options.forEach(opt => {
        const optKurId = opt.getAttribute('data-kurikulum-id');
        if (!optKurId || optKurId === '' || optKurId === selectedKurId) {
            opt.style.display = '';
            opt.disabled = false;
            if (opt.selected) currentlySelectedStillValid = true;
        } else {
            opt.style.display = 'none';
            opt.disabled = true;
            if (opt.selected) opt.selected = false;
        }
    });

    if (!currentlySelectedStillValid) {
        faseSel.value = '';
    }
}

// Populate Edit CP Modal — robust helper supporting both JSON lookup & HTML data-attribute fallback
function populateEditCpModal(btn) {
    if (!btn) return;
    const cpId = parseInt(btn.getAttribute('data-id') || '0', 10);
    const cp = (typeof _cpData !== 'undefined' && _cpData && _cpData[cpId]) ? _cpData[cpId] : null;

    const id = cp ? cp.id : (btn.getAttribute('data-id') || '');
    const kode = cp ? cp.kode_cp : (btn.getAttribute('data-kode') || '');
    const elemen = cp ? (cp.elemen || '') : (btn.getAttribute('data-elemen') || '');
    const deskripsi = cp ? (cp.deskripsi || '') : (btn.getAttribute('data-deskripsi') || '');
    const kurikulumId = cp ? cp.kurikulum_id : (btn.getAttribute('data-kurikulum-id') || '');
    const mapelId = cp ? cp.mapel_id : (btn.getAttribute('data-mapel-id') || '');
    const faseId = cp ? (cp.fase_id || '') : (btn.getAttribute('data-fase-id') || '');

    const idInp = document.getElementById('edit_cp_id');
    const kodeInp = document.getElementById('edit_cp_kode');
    const elemenInp = document.getElementById('edit_cp_elemen');
    const deskInp = document.getElementById('edit_cp_deskripsi');

    if (idInp) idInp.value = id;
    if (kodeInp) kodeInp.value = kode;
    if (elemenInp) elemenInp.value = elemen;
    if (deskInp) deskInp.value = deskripsi;

    const selKur = document.getElementById('edit_cp_kurikulum_id');
    if (selKur && kurikulumId) {
        selKur.value = kurikulumId;
        filterFaseDropdown('edit_cp_kurikulum_id', 'edit_cp_fase_id');
    }

    const selMapel = document.getElementById('edit_cp_mapel_id');
    if (selMapel && mapelId) selMapel.value = mapelId;

    const selFase = document.getElementById('edit_cp_fase_id');
    if (selFase) selFase.value = faseId;
}

// Populate Edit TP Modal — robust helper supporting both JSON lookup & HTML data-attribute fallback
function populateEditTpModal(btn) {
    if (!btn) return;
    const tpId = parseInt(btn.getAttribute('data-id') || '0', 10);
    const tp = (typeof _tpData !== 'undefined' && _tpData && _tpData[tpId]) ? _tpData[tpId] : null;

    const id = tp ? tp.id : (btn.getAttribute('data-id') || '');
    const kode = tp ? tp.kode_tp : (btn.getAttribute('data-kode') || '');
    const materi = tp ? (tp.materi_pokok || '') : (btn.getAttribute('data-materi') || '');
    const deskripsi = tp ? (tp.deskripsi || '') : (btn.getAttribute('data-deskripsi') || '');
    const cpId = tp ? tp.cp_id : (btn.getAttribute('data-cp-id') || '');

    const idInp = document.getElementById('edit_tp_id');
    const kodeInp = document.getElementById('edit_tp_kode');
    const materiInp = document.getElementById('edit_tp_materi');
    const deskInp = document.getElementById('edit_tp_deskripsi');
    const selCp = document.getElementById('edit_tp_cp_id');

    if (idInp) idInp.value = id;
    if (kodeInp) kodeInp.value = kode;
    if (materiInp) materiInp.value = materi;
    if (deskInp) deskInp.value = deskripsi;

    if (selCp && cpId) {
        selCp.value = cpId;
    }

    // Trigger CP preview update after values are set
    updateEditParentCpPreview();
}

document.addEventListener('DOMContentLoaded', () => {
    // Character counters for textareas
    const addCpDesk = document.getElementById('add_cp_deskripsi');
    const addCpCount = document.getElementById('add_cp_char_count');
    if (addCpDesk && addCpCount) {
        addCpDesk.addEventListener('input', () => {
            addCpCount.textContent = addCpDesk.value.length + ' karakter';
        });
    }

    const addTpDesk = document.getElementById('add_tp_deskripsi');
    const addTpCount = document.getElementById('add_tp_char_count');
    if (addTpDesk && addTpCount) {
        addTpDesk.addEventListener('input', () => {
            addTpCount.textContent = addTpDesk.value.length + ' karakter';
        });
    }

    // Initialize Smart List Handlers
    setupSmartListTextarea('add_tp_deskripsi');
    setupSmartListTextarea('edit_tp_deskripsi');

    // Dynamic Fase filtering in modals
    const kurAddCp = document.getElementById('add_cp_kurikulum_id');
    if (kurAddCp) {
        kurAddCp.addEventListener('change', () => {
            filterFaseDropdown('add_cp_kurikulum_id', 'add_cp_fase_id');
            updateAutoCpCode();
        });
        filterFaseDropdown('add_cp_kurikulum_id', 'add_cp_fase_id');
    }

    const mapelAddCp = document.getElementById('add_cp_mapel_id');
    if (mapelAddCp) {
        mapelAddCp.addEventListener('change', updateAutoCpCode);
    }

    const btnRegenCp = document.getElementById('btn_regen_cp_code');
    if (btnRegenCp) {
        btnRegenCp.addEventListener('click', updateAutoCpCode);
    }

    // Always reset modalAddCP to blank state when opened
    const modalAddCpEl = document.getElementById('modalAddCP');
    if (modalAddCpEl) {
        modalAddCpEl.addEventListener('show.bs.modal', () => {
            const elemenInp = document.getElementById('add_cp_elemen');
            if (elemenInp) elemenInp.value = '';

            const deskInp = document.getElementById('add_cp_deskripsi');
            if (deskInp) deskInp.value = '';

            const countSpan = document.getElementById('add_cp_char_count');
            if (countSpan) countSpan.textContent = '0 karakter';

            updateAutoCpCode();
        });
    }

    // TP Auto Code Listeners & Parent Preview
    const selectAddTpCp = document.getElementById('add_tp_cp_id');
    if (selectAddTpCp) {
        selectAddTpCp.addEventListener('change', updateAutoTpCode);
    }

    const selectEditTpCp = document.getElementById('edit_tp_cp_id');
    if (selectEditTpCp) {
        selectEditTpCp.addEventListener('change', updateEditParentCpPreview);
    }

    const btnRegenTp = document.getElementById('btn_regen_tp_code');
    if (btnRegenTp) {
        btnRegenTp.addEventListener('click', updateAutoTpCode);
    }

    // Always reset modalAddTP to blank input state while showing complete parent CP
    const modalAddTpEl = document.getElementById('modalAddTP');
    function resetAddTpForm(targetCpId = null, btnEl = null) {
        const select = document.getElementById('add_tp_cp_id');
        if (select && targetCpId) {
            select.value = targetCpId;
        }

        const materiInp = document.getElementById('add_tp_materi');
        if (materiInp) materiInp.value = '';

        const deskInp = document.getElementById('add_tp_deskripsi');
        if (deskInp) deskInp.value = '';

        const countSpan = document.getElementById('add_tp_char_count');
        if (countSpan) countSpan.textContent = '0 karakter';

        updateAutoTpCode();

        if (btnEl && (btnEl.getAttribute('data-cp-deskripsi') || btnEl.dataset.cpDeskripsi)) {
            const previewKode = document.getElementById('preview_cp_kode');
            const previewMapel = document.getElementById('preview_cp_mapel');
            const previewFase = document.getElementById('preview_cp_fase');
            const previewElemen = document.getElementById('preview_cp_elemen');
            const previewDeskripsi = document.getElementById('preview_cp_deskripsi');

            const kode = btnEl.getAttribute('data-cp-kode') || btnEl.dataset.cpKode;
            const mapel = btnEl.getAttribute('data-cp-mapel') || btnEl.dataset.cpMapel;
            const fase = btnEl.getAttribute('data-cp-fase') || btnEl.dataset.cpFase;
            const elem = btnEl.getAttribute('data-cp-elemen') || btnEl.dataset.cpElemen;
            const desk = btnEl.getAttribute('data-cp-deskripsi') || btnEl.dataset.cpDeskripsi;

            if (previewKode && kode) previewKode.textContent = kode;
            if (previewMapel && mapel) previewMapel.textContent = mapel;
            if (previewFase) {
                if (fase) {
                    previewFase.textContent = fase;
                    previewFase.style.display = 'inline-block';
                } else {
                    previewFase.style.display = 'none';
                }
            }
            if (previewElemen) {
                if (elem) {
                    previewElemen.textContent = 'Elemen: ' + elem;
                    previewElemen.style.display = 'inline-block';
                } else {
                    previewElemen.style.display = 'none';
                }
            }
            if (previewDeskripsi && desk) previewDeskripsi.textContent = desk;
        } else {
            updateParentCpPreview();
        }
    }

    if (modalAddTpEl) {
        modalAddTpEl.addEventListener('show.bs.modal', function(e) {
            const button = e.relatedTarget;
            const targetCpId = button ? button.getAttribute('data-cp-id') : null;
            resetAddTpForm(targetCpId, button);
        });
    }

    // Direct "+ Tambah TP Turunan" from CP row
    document.querySelectorAll('.btn-add-tp-for-cp').forEach(btn => {
        btn.addEventListener('click', function() {
            const cpId = this.dataset.cpId;
            resetAddTpForm(cpId, this);
        });
    });

    // Populate Edit CP Modal — show.bs.modal handler
    const modalEditCpEl = document.getElementById('modalEditCP');
    if (modalEditCpEl) {
        modalEditCpEl.addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget ? (e.relatedTarget.closest('.btn-edit-cp') || e.relatedTarget) : null;
            if (btn && btn.classList && btn.classList.contains('btn-edit-cp')) {
                populateEditCpModal(btn);
            }
        });
    }

    // Populate Edit TP Modal — show.bs.modal handler
    const modalEditTpEl = document.getElementById('modalEditTP');
    if (modalEditTpEl) {
        modalEditTpEl.addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget ? (e.relatedTarget.closest('.btn-edit-tp') || e.relatedTarget) : null;
            if (btn) {
                populateEditTpModal(btn);
            }
        });
    }

    // Direct click event delegation for Edit buttons (guarantees data populates even before/outside Bootstrap show event)
    document.addEventListener('click', function(e) {
        const cpBtn = e.target.closest('.btn-edit-cp');
        if (cpBtn) {
            populateEditCpModal(cpBtn);
        }
        const tpBtn = e.target.closest('.btn-edit-tp');
        if (tpBtn) {
            populateEditTpModal(tpBtn);
        }
    });

    // KKTP Modal Handling
    const modalKktpEl = document.getElementById('modalKktp');
    const metodeRadios = document.querySelectorAll('input[name="metode"]');
    const secInterval = document.getElementById('section_interval_nilai');
    const secRubrik = document.getElementById('section_rubrik');
    const secChecklist = document.getElementById('section_checklist');
    const indListWrapper = document.getElementById('indikatorListWrapper');
    const btnTambahInd = document.getElementById('btnTambahIndikator');

    function switchKktpMetode(metode) {
        document.querySelectorAll('.kktp-method-card').forEach(card => card.classList.remove('active', 'border-primary', 'border-info', 'border-warning'));
        
        if (metode === 'rubrik') {
            secInterval.style.display = 'none';
            secRubrik.style.display = 'block';
            secChecklist.style.display = 'none';
            document.getElementById('card_metode_rubrik').classList.add('active', 'border-info');
        } else if (metode === 'checklist') {
            secInterval.style.display = 'none';
            secRubrik.style.display = 'none';
            secChecklist.style.display = 'block';
            document.getElementById('card_metode_checklist').classList.add('active', 'border-warning');
        } else {
            secInterval.style.display = 'block';
            secRubrik.style.display = 'none';
            secChecklist.style.display = 'none';
            document.getElementById('card_metode_interval').classList.add('active', 'border-primary');
        }
    }

    metodeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            switchKktpMetode(this.value);
        });
    });

    function addIndikatorRow(nama = '', desc = '', bobot = 1.0) {
        if (!indListWrapper) return;
        const rowId = 'ind_row_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
        const html = `
            <div class="card border rounded-3 p-2.5 bg-light position-relative" id="${rowId}">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-5">
                        <input type="text" name="indikator_nama[]" class="form-control form-control-sm fw-semibold" placeholder="Nama Indikator (wajib)" value="${nama.replace(/"/g, '&quot;')}" required>
                    </div>
                    <div class="col-12 col-md-5">
                        <input type="text" name="indikator_desc[]" class="form-control form-control-sm" placeholder="Kriteria / Bukti Ketercapaian" value="${desc.replace(/"/g, '&quot;')}">
                    </div>
                    <div class="col-8 col-md-1">
                        <input type="number" step="0.5" min="1" name="indikator_bobot[]" class="form-control form-control-sm text-center" value="${bobot}" title="Bobot">
                    </div>
                    <div class="col-4 col-md-1 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger p-1 px-2 rounded-2" onclick="document.getElementById('${rowId}').remove()" title="Hapus Indikator">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        indListWrapper.insertAdjacentHTML('beforeend', html);
    }

    if (btnTambahInd) {
        btnTambahInd.addEventListener('click', function() {
            addIndikatorRow();
        });
    }

    function populateKktpModal(btn) {
        if (!btn) return;
        const tpId = btn.dataset.id;
        const tpKode = btn.dataset.kode || '';
        const tpDesc = btn.dataset.deskripsi || '';
        const metode = btn.dataset.metode || 'interval_nilai';
        const nilaiMin = btn.dataset.nilaiMin || '75.00';
        const targetInd = btn.dataset.targetInd || '0';
        const kriteria = btn.dataset.kriteria || '';

        document.getElementById('kktp_tp_id').value = tpId;
        document.getElementById('kktp_preview_tp_kode').textContent = tpKode;
        document.getElementById('kktp_preview_tp_desc').textContent = tpDesc;

        // Set radio
        const radio = document.querySelector(`input[name="metode"][value="${metode}"]`);
        if (radio) radio.checked = true;
        switchKktpMetode(metode);

        // Populate fields
        document.getElementById('kktp_nilai_minimum').value = parseFloat(nilaiMin) || 75;
        document.getElementById('kktp_rubrik_nilai_min').value = parseFloat(nilaiMin) || 75;
        document.getElementById('kktp_deskripsi_kriteria_interval').value = kriteria;
        document.getElementById('kktp_rubrik_deskripsi').value = kriteria;
        document.getElementById('kktp_target_indikator_count').value = parseInt(targetInd) || 3;

        // Fetch detail indicator via AJAX if available
        if (indListWrapper) indListWrapper.innerHTML = '';
        fetch(`<?= BASE_URL ?>index.php?url=guru/asesmen&ajax_action=get_kktp_info&tp_id=${tpId}`)
            .then(r => r.json())
            .then(res => {
                if (res.status && res.data && res.data.indikator && res.data.indikator.length > 0) {
                    res.data.indikator.forEach(ind => {
                        addIndikatorRow(ind.nama_indikator, ind.deskripsi_kriteria, ind.bobot);
                    });
                } else {
                    // Provide 2 default indicator templates if empty
                    addIndikatorRow('Indikator 1: Mampu mengidentifikasi konsep dasar', '', 1);
                    addIndikatorRow('Indikator 2: Mampu menerapkan prosedur secara benar', '', 1);
                }
            })
            .catch(() => {
                addIndikatorRow('Indikator 1', '', 1);
                addIndikatorRow('Indikator 2', '', 1);
            });
    }

    document.addEventListener('click', function(e) {
        const btnKktp = e.target.closest('.btn-kktp');
        if (btnKktp) {
            populateKktpModal(btnKktp);
        }
    });

    // Auto adjust datatables on window resize
    window.addEventListener('resize', () => {
        if (window.jQuery && jQuery.fn.dataTable) {
            jQuery.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        }
    });
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
