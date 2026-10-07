<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
if (!function_exists('formatTpDescriptionHtml')) {
    function formatTpDescriptionHtml($text) {
        if (empty($text)) return '';
        $lines = preg_split('/\r\n|\r|\n/', trim($text));
        if (count($lines) <= 1 && !preg_match('/^(\d+[\.\)\-]|[a-zA-Z][\.\)]|[•\-\*✓✔☑▪▫►▶→➔➢+~–—\x{2022}\x{25AA}\x{2713}\x{2714}])\s*/u', trim($text))) {
            return '<div class="tp-deskripsi-text" style="color: #1e293b; line-height: 1.65; font-size: 0.91rem;">' . nl2br(htmlspecialchars($text)) . '</div>';
        }
        $html = '<div class="tp-formatted-list d-flex flex-column" style="gap: 7px; margin-top: 4px;">';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;
            // 1. Numbered: 1. or 1) or 1-
            if (preg_match('/^(\d+)[\.\)\-]\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 9px;">'
                      . '<span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace rounded-pill flex-shrink-0" style="font-size:0.75rem; min-width:24px; padding: 2.5px 7px; text-align:center; font-weight:700;">' . $m[1] . '</span>'
                      . '<span class="tp-list-text" style="color: #1e293b; line-height: 1.62; font-size: 0.91rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            // 2. Lettered: a. or A. or a)
            } elseif (preg_match('/^([a-zA-Z])[\.\)]\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 9px;">'
                      . '<span class="badge bg-secondary-subtle text-dark border font-monospace rounded-pill flex-shrink-0" style="font-size:0.75rem; min-width:24px; padding: 2.5px 7px; text-align:center; font-weight:700;">' . strtoupper($m[1]) . '</span>'
                      . '<span class="tp-list-text" style="color: #1e293b; line-height: 1.62; font-size: 0.91rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            // 3. Checkmarks: ✓, ✔, ☑
            } elseif (preg_match('/^([✓✔☑\x{2713}\x{2714}])\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 9px;">'
                      . '<span class="text-success flex-shrink-0 fw-bold" style="font-size:1rem; line-height:1.5; width:20px; text-align:center;"><i class="bi bi-check-circle-fill"></i></span>'
                      . '<span class="tp-list-text" style="color: #1e293b; line-height: 1.62; font-size: 0.91rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            // 4. Arrows: →, ➔, ➢, ►, >
            } elseif (preg_match('/^([→➔➢►▶>])\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 9px;">'
                      . '<span class="text-primary flex-shrink-0 fw-bold" style="font-size:1rem; line-height:1.5; width:20px; text-align:center;"><i class="bi bi-arrow-right-short fs-5"></i></span>'
                      . '<span class="tp-list-text" style="color: #1e293b; line-height: 1.62; font-size: 0.91rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            // 5. Bullets: •, -, *, ▪, ▫, +
            } elseif (preg_match('/^([•\-\*▪▫+–—\x{2022}\x{25AA}])\s*(.*)$/u', $trimmed, $m)) {
                $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 9px;">'
                      . '<span class="text-primary flex-shrink-0 fw-bold" style="font-size:1.2rem; line-height:1.2; width:20px; text-align:center;">•</span>'
                      . '<span class="tp-list-text" style="color: #1e293b; line-height: 1.62; font-size: 0.91rem;">' . htmlspecialchars($m[2]) . '</span>'
                      . '</div>';
            } else {
                $html .= '<div class="tp-list-text" style="color: #1e293b; line-height: 1.62; font-size: 0.91rem;">' . htmlspecialchars($trimmed) . '</div>';
            }
        }
        $html .= '</div>';
        return $html;
    }
}
?>

<style>
/* Modern, Clean & Responsive CP/TP Styles for Students */
:root {
    --cptp-primary: #3b82f6;
    --cptp-primary-dark: #1d4ed8;
    --cptp-success: #10b981;
    --cptp-warning: #f59e0b;
    --cptp-slate-50: #f8fafc;
    --cptp-slate-100: #f1f5f9;
    --cptp-slate-200: #e2e8f0;
    --cptp-slate-700: #334155;
    --cptp-slate-800: #1e293b;
}

.cptp-header-gradient {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #38bdf8 100%);
    color: #ffffff;
    border-radius: 18px;
    position: relative;
    overflow: hidden;
}
.cptp-header-gradient::after {
    content: '';
    position: absolute;
    top: -40%;
    right: -10%;
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.cptp-stat-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid var(--cptp-slate-200);
    padding: 18px 20px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
}
.cptp-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
}

.mapel-section-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid var(--cptp-slate-200);
    box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
    margin-bottom: 24px;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}
.mapel-section-card:hover {
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.07);
}

.mapel-header-bar {
    background: linear-gradient(to right, #f8fafc, #ffffff);
    border-bottom: 1px solid var(--cptp-slate-200);
    padding: 16px 22px;
}

.cp-card-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    margin-bottom: 18px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.cp-card-item:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
}

.cp-header-box {
    background-color: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 14px 18px;
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
}

.tp-card-box {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #10b981;
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 12px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.tp-card-box:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
}

.filter-input-uniform {
    height: 44px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    background-color: #ffffff;
    font-size: 0.9rem;
}
.filter-input-uniform:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

@media print {
    body {
        background: #ffffff !important;
        font-size: 11pt;
    }
    .app-navbar, .sidebar, .cptp-actions-nonprint, .breadcrumb, #themeToggle, .btn {
        display: none !important;
    }
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .cptp-header-gradient {
        background: #1e3a8a !important;
        color: #ffffff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .mapel-section-card, .cp-card-item, .tp-card-box {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #cccccc !important;
    }
}
</style>

<main class="main-content px-3 px-md-4 py-3">
<div class="container-fluid">

    <!-- 1. Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-1 small">
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>index.php?url=siswa/dashboard" class="text-decoration-none text-muted">
                    <i class="bi bi-house-door me-1"></i>Beranda Siswa
                </a>
            </li>
            <li class="breadcrumb-item text-muted">Pembelajaran & Kurikulum</li>
            <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">
                Capaian & Tujuan Pembelajaran (CP & TP)
            </li>
        </ol>
    </nav>

    <!-- 2. Hero Banner Header -->
    <div class="cptp-header-gradient p-4 p-md-4 mb-4 shadow-sm">
        <div class="row align-items-center g-3">
            <div class="col-12 col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge bg-white text-primary rounded-pill px-3 py-1.5 fw-bold font-monospace shadow-xs" style="font-size: 0.76rem;">
                        <i class="bi bi-mortarboard-fill me-1"></i><?= htmlspecialchars($namaKurikulum) ?> (<?= htmlspecialchars($kodeKurikulum) ?>)
                    </span>
                    <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.76rem;">
                        <i class="bi bi-layers-fill me-1"></i><?= htmlspecialchars($faseInfo) ?>
                    </span>
                </div>
                <h3 class="fw-bold mb-1 d-flex align-items-center gap-2.5">
                    <i class="bi bi-diagram-3-fill fs-2"></i>
                    <span>Capaian & Tujuan Pembelajaran (CP & TP)</span>
                </h3>
                <p class="mb-0 text-white text-opacity-90 small" style="max-width: 680px; line-height: 1.6;">
                    Pantau target kompetensi pokok, rumusan capaian kurikulum, butir tujuan pembelajaran (TP), dan kriteria ketuntasan (KKTP) untuk mata pelajaran yang telah Anda ambil di rombel ini.
                </p>
            </div>
            <div class="col-12 col-lg-4 text-lg-end cptp-actions-nonprint">
                <div class="d-flex gap-2 justify-content-lg-end flex-wrap">
                    <button type="button" class="btn btn-light text-primary fw-bold px-3 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2" onclick="window.print()" title="Cetak atau simpan ringkasan CP & TP ke PDF">
                        <i class="bi bi-printer-fill fs-5"></i>
                        <span>Cetak Ringkasan</span>
                    </button>
                    <a href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" class="btn btn-outline-light fw-semibold px-3 py-2 rounded-3 d-inline-flex align-items-center gap-2" title="Kelola / gabung mata pelajaran lain">
                        <i class="bi bi-bounding-box-circles fs-5"></i>
                        <span>Kelas Virtual</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Metric 1: Mapel Terdaftar -->
        <div class="col-6 col-lg-3">
            <div class="cptp-stat-card border-start border-4 border-primary h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.73rem; letter-spacing: 0.5px;">Mapel Terdaftar</span>
                        <h3 class="fw-bold text-primary mb-0"><?= count($enrolledMapelIds) ?></h3>
                    </div>
                    <div class="rounded-circle p-2.5 bg-primary-subtle text-primary">
                        <i class="bi bi-journal-bookmark-fill fs-4"></i>
                    </div>
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">
                    <i class="bi bi-check-circle-fill text-primary me-1"></i>Sesuai jadwal rombel Anda
                </div>
            </div>
        </div>

        <!-- Metric 2: Capaian Pembelajaran (CP) -->
        <div class="col-6 col-lg-3">
            <div class="cptp-stat-card border-start border-4 border-info h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.73rem; letter-spacing: 0.5px;">Capaian (CP)</span>
                        <h3 class="fw-bold text-info mb-0"><?= $totalCpCount ?></h3>
                    </div>
                    <div class="rounded-circle p-2.5 bg-info-subtle text-info">
                        <i class="bi bi-diagram-2-fill fs-4"></i>
                    </div>
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">
                    <i class="bi bi-check-circle-fill text-info me-1"></i>Target kompetensi induk
                </div>
            </div>
        </div>

        <!-- Metric 3: Tujuan Pembelajaran (TP) -->
        <div class="col-6 col-lg-3">
            <div class="cptp-stat-card border-start border-4 border-success h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.73rem; letter-spacing: 0.5px;">Tujuan (TP)</span>
                        <h3 class="fw-bold text-success mb-0"><?= $totalTpCount ?></h3>
                    </div>
                    <div class="rounded-circle p-2.5 bg-success-subtle text-success">
                        <i class="bi bi-bullseye fs-4"></i>
                    </div>
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">
                    <i class="bi bi-check-circle-fill text-success me-1"></i>Sasaran pembelajaran KBM
                </div>
            </div>
        </div>

        <!-- Metric 4: Kriteria Ketuntasan (KKTP) -->
        <div class="col-6 col-lg-3">
            <div class="cptp-stat-card border-start border-4 border-warning h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.73rem; letter-spacing: 0.5px;">Standar Kelulusan</span>
                        <h4 class="fw-bold text-warning mb-0" style="font-size: 1.35rem;">KKTP / 75</h4>
                    </div>
                    <div class="rounded-circle p-2.5 bg-warning-subtle text-warning">
                        <i class="bi bi-award-fill fs-4"></i>
                    </div>
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">
                    <i class="bi bi-shield-check text-warning me-1"></i>Indikator E-Rapor Digital
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Filter Toolbar & Search Bar (HANYA Menampilkan Mapel Terdaftar) -->
    <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white cptp-actions-nonprint">
        <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-2.5 align-items-end" id="filterForm">
            <input type="hidden" name="url" value="siswa/cptp">

            <!-- Filter Mapel Terdaftar -->
            <div class="col-12 col-md-5">
                <label class="small fw-bold text-secondary mb-1.5 d-flex align-items-center gap-1">
                    <i class="bi bi-book-half text-primary"></i>
                    <span>Pilih Mata Pelajaran Terdaftar:</span>
                </label>
                <select name="filter_mapel_id" class="form-select filter-input-uniform" onchange="this.form.submit()">
                    <option value="">-- Semua Mata Pelajaran Saya (<?= count($enrolledMapels) ?>) --</option>
                    <?php foreach ($enrolledMapels as $em): ?>
                        <option value="<?= $em['mapel_id'] ?>" <?= ($filterMapelId == $em['mapel_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($em['nama_mapel']) ?> (Guru: <?= htmlspecialchars($em['nama_guru']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Quick Keyword Search -->
            <div class="col-12 col-md-5">
                <label class="small fw-bold text-secondary mb-1.5 d-flex align-items-center gap-1">
                    <i class="bi bi-search text-primary"></i>
                    <span>Cari Materi / Rumusan CP & TP:</span>
                </label>
                <div class="input-group">
                    <input type="text" name="q" id="keywordInput" class="form-control filter-input-uniform" placeholder="Ketik kata kunci CP, materi pokok, atau elemen..." value="<?= htmlspecialchars($searchKeyword) ?>">
                    <?php if (!empty($searchKeyword)): ?>
                        <a href="<?= BASE_URL ?>index.php?url=siswa/cptp<?= $filterMapelId ? '&filter_mapel_id=' . $filterMapelId : '' ?>" class="btn btn-outline-secondary d-flex align-items-center" title="Hapus Pencarian">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill fw-semibold rounded-3 d-flex align-items-center justify-content-center gap-1.5" style="height: 44px;">
                    <i class="bi bi-funnel-fill"></i>
                    <span>Cari</span>
                </button>
                <?php if ($filterMapelId || !empty($searchKeyword)): ?>
                    <a href="<?= BASE_URL ?>index.php?url=siswa/cptp" class="btn btn-outline-secondary rounded-3 px-3 d-flex align-items-center justify-content-center" style="height: 44px;" title="Reset Semua Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- 5. Interactive Expand/Collapse & Summary Helper -->
    <?php if (!empty($mapelGroups)): ?>
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 cptp-actions-nonprint">
            <div class="small text-muted">
                Menampilkan <strong><?= count($mapelGroups) ?></strong> mata pelajaran terdaftar dengan total <strong><?= $totalCpCount ?></strong> Capaian (CP) dan <strong><?= $totalTpCount ?></strong> Tujuan Pembelajaran (TP).
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold" id="btnExpandAll">
                    <i class="bi bi-arrows-expand me-1"></i>Buka Semua
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" id="btnCollapseAll">
                    <i class="bi bi-arrows-collapse me-1"></i>Tutup Semua
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- 6. Main Content Section: Subject Groups & CP/TP Cards -->
    <?php if (empty($enrolledMapelIds)): ?>
        <!-- EMPTY STATE: Belum ada mata pelajaran terdaftar sama sekali -->
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
            <div class="mb-3">
                <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center p-4" style="width: 84px; height: 84px;">
                    <i class="bi bi-journal-x fs-1"></i>
                </div>
            </div>
            <h4 class="fw-bold text-dark mb-2">Belum Ada Mata Pelajaran yang Terdaftar</h4>
            <p class="text-muted mx-auto mb-4" style="max-width: 540px; line-height: 1.6;">
                Anda belum terdaftar pada mata pelajaran manapun di rombel ini. Agar Capaian & Tujuan Pembelajaran (CP & TP) dapat ditampilkan, silakan bergabung ke kelas virtual dengan memasukkan enrollment key dari bapak/ibu guru.
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" class="btn btn-primary fw-bold px-4 py-2.5 rounded-3 shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="bi bi-bounding-box-circles fs-5"></i>
                    <span>Buka Menu Gabung Kelas Virtual</span>
                </a>
            </div>
        </div>

    <?php elseif (empty($mapelGroups)): ?>
        <!-- EMPTY STATE: Filter atau pencarian tidak menghasilkan data -->
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
            <div class="mb-3">
                <div class="rounded-circle bg-info-subtle text-info d-inline-flex align-items-center justify-content-center p-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-search fs-1"></i>
                </div>
            </div>
            <h5 class="fw-bold text-dark mb-2">Tidak Ditemukan Data CP / TP yang Sesuai</h5>
            <p class="text-muted mx-auto mb-3" style="max-width: 500px;">
                Tidak ada rumusan Capaian atau Tujuan Pembelajaran yang sesuai dengan kriteria filter atau kata kunci "<em><?= htmlspecialchars($searchKeyword) ?></em>".
            </p>
            <a href="<?= BASE_URL ?>index.php?url=siswa/cptp" class="btn btn-outline-primary fw-semibold px-4 py-2 rounded-3">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Kembalikan ke Semua Mapel
            </a>
        </div>

    <?php else: ?>
        <!-- LOOP PER MATA PELAJARAN (HANYA YANG TERDAFTAR) -->
        <?php foreach ($mapelGroups as $mId => $group): 
            $mInfo = $group['mapel'];
            $cps = $group['cps'];
            $mapelCpCount = count($cps);
            $mapelTpCount = array_sum(array_map(function($c) { return count($c['tps'] ?? []); }, $cps));
            $collapseId = 'collapseMapel_' . $mId;
        ?>
            <div class="mapel-section-card" id="mapel_card_<?= $mId ?>">
                
                <!-- Mapel Header Bar -->
                <div class="mapel-header-bar d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-primary text-white p-2.5 d-flex align-items-center justify-content-center shadow-xs" style="width: 46px; height: 46px;">
                            <i class="bi bi-book-half fs-4"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <h5 class="fw-bold mb-0 text-dark">
                                    <?= htmlspecialchars($mInfo['nama_mapel']) ?>
                                </h5>
                                <?php if (!empty($mInfo['kode_mapel'])): ?>
                                    <span class="badge bg-secondary-subtle text-dark border font-monospace px-2 py-0.5 rounded-2" style="font-size: 0.74rem;">
                                        <?= htmlspecialchars($mInfo['kode_mapel']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="small text-muted d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.82rem;">
                                <span class="d-inline-flex align-items-center gap-1 text-primary fw-medium">
                                    <i class="bi bi-person-badge-fill"></i> Guru: <?= htmlspecialchars($mInfo['nama_guru']) ?>
                                </span>
                                <?php if (!empty($mInfo['nama_kelas'])): ?>
                                    <span>•</span>
                                    <span><i class="bi bi-people-fill text-secondary me-1"></i><?= htmlspecialchars($mInfo['nama_kelas']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right Summary & Collapse Toggle -->
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.78rem;">
                            <i class="bi bi-diagram-2 me-1"></i><?= $mapelCpCount ?> Capaian (CP)
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.78rem;">
                            <i class="bi bi-bullseye me-1"></i><?= $mapelTpCount ?> Tujuan (TP)
                        </span>
                        <button class="btn btn-sm btn-outline-secondary rounded-circle p-2 d-flex align-items-center justify-content-center toggle-mapel-btn" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="true" aria-controls="<?= $collapseId ?>" title="Sembunyikan/Buka Rincian Mapel Ini" style="width: 34px; height: 34px;">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                </div>

                <!-- Collapsible Body for CP & TP Items -->
                <div class="collapse show mapel-collapse-target" id="<?= $collapseId ?>">
                    <div class="p-3 p-md-4 bg-white">
                        
                        <?php if (empty($cps)): ?>
                            <!-- Subject has no CP compiled yet by teacher -->
                            <div class="p-4 rounded-3 bg-light border border-dashed text-center">
                                <i class="bi bi-hourglass-split text-warning fs-3 d-block mb-2"></i>
                                <h6 class="fw-bold text-dark mb-1">CP & TP Sedang Dalam Penyusunan</h6>
                                <p class="text-muted small mb-0" style="max-width: 520px; margin: 0 auto;">
                                    Bapak/Ibu <strong><?= htmlspecialchars($mInfo['nama_guru']) ?></strong> sedang merumuskan butir Capaian dan Tujuan Pembelajaran untuk mata pelajaran ini. Silakan periksa kembali secara berkala.
                                </p>
                            </div>

                        <?php else: ?>
                            <!-- List of CPs under this subject -->
                            <?php foreach ($cps as $cpIdx => $cp): 
                                $childTps = $cp['tps'] ?? [];
                            ?>
                                <div class="cp-card-item">
                                    <!-- CP Card Header -->
                                    <div class="cp-header-box d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                        <div class="d-flex align-items-start gap-2.5">
                                            <span class="badge bg-primary text-white font-monospace px-2.5 py-1.5 rounded-2 shadow-xs fw-bold" style="font-size: 0.82rem;">
                                                <i class="bi bi-bookmark-fill me-1"></i><?= htmlspecialchars($cp['kode_cp']) ?>
                                            </span>
                                            <div>
                                                <?php if (!empty($cp['elemen'])): ?>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-2 small fw-bold mb-1 d-inline-block">
                                                        <i class="bi bi-tag-fill me-1"></i>Elemen: <?= htmlspecialchars($cp['elemen']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($cp['nama_fase'])): ?>
                                                    <span class="badge bg-secondary-subtle text-dark border px-2 py-1 rounded-2 small fw-semibold ms-1">
                                                        <?= htmlspecialchars($cp['nama_fase']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill small fw-semibold">
                                                <i class="bi bi-bullseye text-success me-1"></i><?= count($childTps) ?> Butir TP
                                            </span>
                                        </div>
                                    </div>

                                    <!-- CP Description Body -->
                                    <div class="p-3 p-md-3.5 border-bottom" style="background-color: #fafbfc;">
                                        <div class="text-uppercase fw-bold text-muted mb-1.5" style="font-size: 0.72rem; letter-spacing: 0.6px;">
                                            <i class="bi bi-card-text text-primary me-1"></i>Rumusan Capaian Pembelajaran (CP):
                                        </div>
                                        <div class="text-dark small lh-base" style="font-size: 0.92rem; color: #1e293b; line-height: 1.68;">
                                            <?= nl2br(htmlspecialchars($cp['deskripsi'])) ?>
                                        </div>
                                    </div>

                                    <!-- TP (Tujuan Pembelajaran) Child Container -->
                                    <div class="p-3 p-md-3.5">
                                        <div class="d-flex justify-content-between align-items-center mb-2.5">
                                            <span class="text-uppercase fw-bold text-dark d-flex align-items-center gap-1.5" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                                                <i class="bi bi-check2-all text-success fs-6"></i>
                                                <span>Rincian Tujuan Pembelajaran (TP) & Standar KKTP:</span>
                                            </span>
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                Materi pokok & target kelulusan asesmen
                                            </small>
                                        </div>

                                        <?php if (empty($childTps)): ?>
                                            <div class="p-3 rounded-3 bg-light border border-dashed text-center text-muted small">
                                                <i class="bi bi-info-circle me-1"></i> Belum ada butir TP turunan untuk Capaian Pembelajaran ini.
                                            </div>
                                        <?php else: ?>
                                            <div class="d-flex flex-column gap-2.5">
                                                <?php foreach ($childTps as $tp): 
                                                    $kMetode = $tp['kktp_metode'] ?? 'interval_nilai';
                                                    $kMin = !empty($tp['kktp_nilai_min']) ? floatval($tp['kktp_nilai_min']) : 75.00;
                                                    $kTarget = (int)($tp['kktp_target_ind'] ?? 0);
                                                    $kKriteria = trim($tp['kktp_kriteria'] ?? '');
                                                ?>
                                                    <div class="tp-card-box">
                                                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
                                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2.5 py-1 rounded fw-bold" style="font-size: 0.78rem;">
                                                                    <?= htmlspecialchars($tp['kode_tp']) ?>
                                                                </span>

                                                                <?php if (!empty($tp['materi_pokok'])): ?>
                                                                    <span class="badge bg-light text-dark border px-2.5 py-1 rounded fw-semibold" style="font-size: 0.76rem; background-color: #f8fafc !important;">
                                                                        <i class="bi bi-bookmark-star-fill text-primary me-1"></i><?= htmlspecialchars($tp['materi_pokok']) ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>

                                                            <!-- KKTP Status Pill for Student Awareness -->
                                                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                                <?php if ($kMetode === 'checklist'): ?>
                                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 rounded fw-semibold" style="font-size: 0.75rem;" title="Target ketuntasan checklist indikator">
                                                                        <i class="bi bi-check2-square me-1"></i>Target: <?= $kTarget ?> Indikator Tuntas
                                                                    </span>
                                                                <?php elseif ($kMetode === 'rubrik'): ?>
                                                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1 rounded fw-semibold" style="font-size: 0.75rem;" title="Target rubrik capaian kompetensi">
                                                                        <i class="bi bi-ui-checks-grid me-1"></i>Rubrik Min: <?= number_format($kMin, 0) ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-2.5 py-1 rounded fw-semibold" style="font-size: 0.75rem;" title="Ambang batas ketuntasan nilai minimal">
                                                                        <i class="bi bi-bullseye me-1"></i>Batas Tuntas (KKTP): <?= number_format($kMin, 0) ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <!-- TP Description Formatted -->
                                                        <div class="tp-deskripsi-wrapper mb-2">
                                                            <?= formatTpDescriptionHtml($tp['deskripsi']) ?>
                                                        </div>

                                                        <!-- KKTP Description Detail (if any) -->
                                                        <?php if (!empty($kKriteria)): ?>
                                                            <div class="mt-2 pt-2 border-top border-light d-flex align-items-start gap-2 small text-muted" style="font-size: 0.8rem;">
                                                                <i class="bi bi-info-circle-fill text-info mt-0.5"></i>
                                                                <div>
                                                                    <strong>Pedoman Ketuntasan:</strong> <?= htmlspecialchars($kKriteria) ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- 7. Informative Guide Card for Students -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 cptp-actions-nonprint">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-8">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 flex-shrink-0 d-none d-sm-block">
                        <i class="bi bi-lightbulb-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                            <i class="bi bi-info-circle text-primary d-sm-none"></i>
                            <span>Apa Arti CP, TP, dan KKTP bagi Siswa?</span>
                        </h6>
                        <p class="text-muted small mb-0 lh-base" style="font-size: 0.84rem;">
                            <strong>Capaian Pembelajaran (CP)</strong> merupakan kompetensi inti yang harus Anda kuasai dalam 1 fase belajar. <strong>Tujuan Pembelajaran (TP)</strong> adalah butir sasaran harian per materi. Sedangkan <strong>KKTP</strong> adalah kriteria ketuntasan minimal (standar 75) yang harus Anda raih pada nilai tugas, kuis CBT, dan evaluasi sumatif agar dinyatakan tuntas pada lembar E-Rapor Digital.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 text-md-end">
                <a href="<?= BASE_URL ?>index.php?url=siswa/panduan" class="btn btn-outline-primary fw-semibold rounded-3 px-3 py-2 small d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-book-half"></i>
                    <span>Baca Panduan Siswa Lengkap</span>
                </a>
            </div>
        </div>
    </div>

</div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Quick Expand All
    const btnExpandAll = document.getElementById("btnExpandAll");
    const btnCollapseAll = document.getElementById("btnCollapseAll");
    
    if (btnExpandAll) {
        btnExpandAll.addEventListener("click", function() {
            document.querySelectorAll(".mapel-collapse-target").forEach(function(el) {
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
                bsCollapse.show();
            });
        });
    }

    // Quick Collapse All
    if (btnCollapseAll) {
        btnCollapseAll.addEventListener("click", function() {
            document.querySelectorAll(".mapel-collapse-target").forEach(function(el) {
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
                bsCollapse.hide();
            });
        });
    }

    // Chevron rotation on accordion toggle
    document.querySelectorAll(".toggle-mapel-btn").forEach(function(btn) {
        btn.addEventListener("click", function() {
            const icon = this.querySelector("i");
            if (icon) {
                setTimeout(() => {
                    const isExpanded = this.getAttribute("aria-expanded") === "true";
                    if (isExpanded) {
                        icon.classList.remove("bi-chevron-right");
                        icon.classList.add("bi-chevron-down");
                    } else {
                        icon.classList.remove("bi-chevron-down");
                        icon.classList.add("bi-chevron-right");
                    }
                }, 150);
            }
        });
    });
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
