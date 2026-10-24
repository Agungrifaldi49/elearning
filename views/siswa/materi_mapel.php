<?php
/**
 * Halaman E-Book Materi per Mata Pelajaran (Siswa)
 * Tampilan terstruktur per-bab lengkap dengan Quiz & Tugas terkait.
 */
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/navbar.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';

// Helper embed YouTube
if (!function_exists('ytEmbedUrl')) {
    function ytEmbedUrl($url) {
        if (empty($url)) return '';
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        return $url;
    }
}

// Helper render rich content HTML aman untuk materi singkat
if (!function_exists('renderRichContent')) {
    function renderRichContent($content) {
        if (empty($content)) return '';
        // Normalisasi non-breaking space & whitespace agar justify teks bekerja sempurna
        $content = str_replace('&nbsp;', ' ', $content);
        if (preg_match('/<[a-z][\s\S]*>/i', $content)) {
            return Security::safeHtml($content);
        }
        return '<p class="mb-0 text-justify">' . nl2br(htmlspecialchars($content)) . '</p>';
    }
}

// Warna bab berputar (palet harmonis)
$babColors = ['#2563eb','#7c3aed','#0891b2','#059669','#d97706','#dc2626','#db2777','#65a30d'];

// Helper: ikon tipe file
if (!function_exists('getFileIcon')) {
    function getFileIcon($jenis, $ext = '') {
        if ($jenis === 'youtube' || $ext === 'youtube') return ['bi-youtube text-danger', 'YouTube'];
        if ($jenis === 'video' || in_array($ext, ['mp4','webm'])) return ['bi-play-circle-fill text-danger', 'Video'];
        if ($jenis === 'pdf' || $ext === 'pdf') return ['bi-file-earmark-pdf-fill text-danger', 'PDF'];
        if (in_array($jenis, ['doc','docx']) || in_array($ext, ['doc','docx'])) return ['bi-file-earmark-word-fill text-primary', 'Word'];
        if (in_array($jenis, ['ppt','pptx']) || in_array($ext, ['ppt','pptx'])) return ['bi-file-earmark-ppt-fill text-warning', 'PPT'];
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) return ['bi-file-earmark-image-fill text-success', 'Gambar'];
        return ['bi-file-earmark-text-fill text-secondary', 'Berkas'];
    }
}
?>

<style>
/* ══════════════════════════════════════════════════════════
   E-BOOK MATERI MAPEL — MODERN, RESPONSIVE & JUSTIFIED
   ══════════════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap');

.ebook-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    color: #1e293b;
    letter-spacing: -0.01em;
    -webkit-font-smoothing: antialiased;
}

h1, h2, h3, h4, h5, .bab-title, .materi-unit-title, .qa-item-title {
    font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif !important;
    letter-spacing: -0.02em;
}

/* ── Hero Banner ── */
.ebook-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 45%, #312e81 100%);
    border-radius: 20px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.ebook-hero::before {
    content: '';
    position: absolute;
    width: 340px; height: 340px;
    background: radial-gradient(circle, rgba(139,92,246,.3) 0%, transparent 70%);
    top: -80px; right: -60px;
    pointer-events: none;
}
.ebook-hero::after {
    content: '';
    position: absolute;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(56,189,248,.2) 0%, transparent 70%);
    bottom: -40px; left: 40px;
    pointer-events: none;
}

/* ── Stat Pills ── */
.ebook-stat-pill {
    background: rgba(255,255,255,.12);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 50rem;
    padding: 6px 16px;
    font-size: .82rem;
    font-weight: 600;
    color: #fff;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* ── Progress Bar ── */
.mapel-progress-bar { border-radius: 50rem; height: 6px; background: rgba(255,255,255,.2); overflow: hidden; }
.mapel-progress-fill { height: 100%; background: linear-gradient(90deg, #38bdf8, #a78bfa); border-radius: 50rem; transition: width .6s ease; }

/* ── BAB Card ── */
.bab-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0,0,0,.04);
    overflow: hidden;
    transition: box-shadow .2s ease;
}
.bab-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,.08); }
[data-bs-theme="dark"] .bab-card {
    background: #1e293b;
    border-color: #334155;
}

.bab-header {
    padding: 18px 22px 14px;
    display: flex;
    align-items: center;
    gap: 14px;
    cursor: pointer;
    user-select: none;
}
.bab-number {
    width: 42px; height: 42px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; font-weight: 800;
    color: #fff;
    flex-shrink: 0;
}
.bab-title { font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0; line-height: 1.35; }
[data-bs-theme="dark"] .bab-title { color: #f1f5f9; }
.bab-meta { font-size: .78rem; color: #64748b; margin: 0; }
.bab-chevron { margin-left: auto; color: #94a3b8; transition: transform .25s ease; font-size: 1.1rem; }
.bab-card.is-open .bab-chevron { transform: rotate(-180deg); }

.bab-body {
    border-top: 1px solid #f1f5f9;
    padding: 6px 0 16px;
    display: none;
}
[data-bs-theme="dark"] .bab-body { border-color: #334155; }
.bab-card.is-open .bab-body { display: block; }

/* ── Unit Card Materi di dalam Bab ── */
.materi-unit-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px 22px;
    margin: 14px 18px;
    transition: all 0.25s ease;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.materi-unit-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 8px 24px rgba(15,23,42,0.06);
}
[data-bs-theme="dark"] .materi-unit-card {
    background: #1e293b;
    border-color: #334155;
}

.materi-row-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}

/* ── Box Materi Singkat & Rangkuman (E-Book Reading Sheet) ── */
.materi-singkat-preview-box {
    background: #fbfcfe;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #2563eb;
    border-radius: 14px;
    padding: 16px 20px;
    margin-top: 14px;
    margin-bottom: 14px;
    box-shadow: 0 2px 8px rgba(15,23,42,0.02);
    transition: all 0.2s ease;
}
.materi-singkat-preview-box:hover {
    border-color: #cbd5e1;
    border-left-color: #1d4ed8;
    box-shadow: 0 4px 14px rgba(15,23,42,0.04);
}
[data-bs-theme="dark"] .materi-singkat-preview-box {
    background: #0f172a;
    border-color: #334155;
    border-left-color: #60a5fa;
    box-shadow: none;
}

/* ── Typografi Rich Content: Justify, Rapi, & List A, a, 1, I ── */
.rendered-materi-content {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.85;
    font-size: 0.94rem;
    color: #1e293b;
    text-align: justify !important;
    text-justify: inter-word !important;
    word-break: break-word;
    hyphens: auto;
}
[data-bs-theme="dark"] .rendered-materi-content {
    color: #cbd5e1;
}

.rendered-materi-content p {
    margin-bottom: 12px;
    text-align: justify !important;
    text-justify: inter-word !important;
    line-height: 1.85;
}
.rendered-materi-content div {
    text-align: justify !important;
    text-justify: inter-word !important;
}
.rendered-materi-content ol {
    padding-left: 28px;
    margin-bottom: 14px;
    text-align: justify !important;
    text-justify: inter-word !important;
}
.rendered-materi-content ul {
    padding-left: 28px;
    margin-bottom: 14px;
    list-style-type: disc !important;
    text-align: justify !important;
    text-justify: inter-word !important;
}
.rendered-materi-content li {
    margin-bottom: 8px;
    line-height: 1.8;
    text-align: justify !important;
    text-justify: inter-word !important;
    padding-left: 4px;
}
.rendered-materi-content li::marker {
    font-weight: 700;
    color: #2563eb;
}
[data-bs-theme="dark"] .rendered-materi-content li::marker {
    color: #60a5fa;
}

.rendered-materi-content ol[type="A"], .rendered-materi-content ol.list-alpha-upper {
    list-style-type: upper-alpha !important;
}
.rendered-materi-content ol[type="a"], .rendered-materi-content ol.list-alpha-lower {
    list-style-type: lower-alpha !important;
}
.rendered-materi-content ol[type="1"], .rendered-materi-content ol.list-decimal {
    list-style-type: decimal !important;
}
.rendered-materi-content ol[type="I"], .rendered-materi-content ol.list-roman-upper {
    list-style-type: upper-roman !important;
}
.rendered-materi-content ol[type="i"], .rendered-materi-content ol.list-roman-lower {
    list-style-type: lower-roman !important;
}

/* Sub-judul dalam materi */
.rendered-materi-content h5 {
    font-family: 'Outfit', sans-serif !important;
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin-top: 16px;
    margin-bottom: 8px;
}
[data-bs-theme="dark"] .rendered-materi-content h5 {
    color: #f1f5f9;
}

/* Callout Box Catatan KBM Guru */
.rich-callout-box, .rendered-materi-content .rich-callout-box {
    background-color: #f0fdf4;
    border-left: 4px solid #16a34a;
    padding: 12px 16px;
    border-radius: 8px;
    margin: 12px 0;
    color: #166534;
    font-size: 0.9rem;
    line-height: 1.75;
    text-align: justify !important;
    text-justify: inter-word !important;
}
[data-bs-theme="dark"] .rich-callout-box,
[data-bs-theme="dark"] .rendered-materi-content .rich-callout-box {
    background-color: #064e3b;
    border-color: #34d399;
    color: #ecfdf5;
}

/* ── Evaluasi Section di Bawah Materi ── */
.eval-section-box {
    background: #fdfefe;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    padding: 14px 16px;
    margin-top: 14px;
}
[data-bs-theme="dark"] .eval-section-box {
    background: rgba(15,23,42,0.3);
    border-color: #334155;
}

.eval-item-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 14px;
    transition: all 0.2s ease;
}
.eval-item-card:hover {
    border-color: #93c5fd;
    box-shadow: 0 4px 12px rgba(59,130,246,0.08);
}
[data-bs-theme="dark"] .eval-item-card {
    background: #1e293b;
    border-color: #334155;
}

.qa-item-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem; flex-shrink: 0;
}
.qa-item-title { font-size: .88rem; font-weight: 700; margin: 0; color: #0f172a; }
[data-bs-theme="dark"] .qa-item-title { color: #f1f5f9; }

/* ── Empty State ── */
.bab-empty { padding: 28px 22px; text-align: center; color: #94a3b8; font-size: .88rem; }
.ebook-empty-state {
    background: #fff; border-radius: 18px; border: 1px solid #e2e8f0;
    padding: 60px 24px; text-align: center;
}
[data-bs-theme="dark"] .ebook-empty-state { background: #1e293b; border-color: #334155; }

/* ── Back Button ── */
.btn-ebook-back {
    background: rgba(255,255,255,.12);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,.22);
    color: #fff; border-radius: 50rem;
    padding: 6px 18px;
    font-size: .82rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 6px;
    text-decoration: none; transition: all .2s ease;
}
.btn-ebook-back:hover { background: rgba(255,255,255,.22); color: #fff; }

/* ── Hover Effects & Utility ── */
.hover-scale {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-scale:hover {
    transform: translateY(-2px);
}
.btn-xs {
    padding: 3px 10px;
    font-size: 0.72rem;
    border-radius: 50rem;
    font-weight: 600;
}
.berkas-media-strip {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    transition: all 0.2s ease;
}
[data-bs-theme="dark"] .berkas-media-strip {
    background: #0f172a;
    border-color: #334155;
}

/* Scrollbar halus untuk rendered-materi-content */
.rendered-materi-content::-webkit-scrollbar {
    width: 5px;
}
.rendered-materi-content::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.03);
    border-radius: 4px;
}
.rendered-materi-content::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
[data-bs-theme="dark"] .rendered-materi-content::-webkit-scrollbar-thumb {
    background: #475569;
}

/* ══════════════════════════════════════════════════════════
   DETAIL RESPONSIVE (MOBILE & TABLET OPTIMIZED)
   ══════════════════════════════════════════════════════════ */
@media (max-width: 991.98px) {
    .ebook-hero {
        padding: 24px 20px !important;
        border-radius: 16px;
    }
    .materi-actions-wrap {
        justify-content: flex-start !important;
        width: 100%;
        margin-top: 10px;
    }
}

@media (max-width: 767.98px) {
    .bab-header {
        padding: 14px 16px;
        gap: 12px;
    }
    .bab-number {
        width: 36px;
        height: 36px;
        font-size: 0.9rem;
        border-radius: 10px;
    }
    .bab-title {
        font-size: 0.96rem;
    }
    .materi-unit-card {
        margin: 12px 10px;
        padding: 14px 14px;
        border-radius: 12px;
    }
    .materi-row-icon {
        width: 40px;
        height: 40px;
        font-size: 1.15rem;
        border-radius: 10px;
    }
    .materi-singkat-preview-box {
        padding: 12px 14px;
        border-radius: 10px;
    }
    .rendered-materi-content {
        font-size: 0.91rem;
        line-height: 1.8;
    }
}

@media (max-width: 575.98px) {
    .ebook-wrapper {
        padding-left: 4px !important;
        padding-right: 4px !important;
    }
    .ebook-hero {
        padding: 18px 14px !important;
        border-radius: 14px;
        margin-bottom: 14px !important;
    }
    .btn-ebook-back {
        font-size: 0.74rem;
        padding: 4px 12px;
    }
    .ebook-stat-pill {
        font-size: 0.72rem;
        padding: 4px 10px;
    }
    .bab-card {
        border-radius: 14px;
        margin-bottom: 12px !important;
    }
    .materi-unit-card {
        margin: 8px 4px;
        padding: 12px 10px;
        border-radius: 10px;
    }
    .materi-actions-wrap {
        display: flex !important;
        width: 100%;
        gap: 6px;
        flex-wrap: wrap;
    }
    .materi-actions-wrap .btn {
        flex: 1 1 auto;
        justify-content: center;
        padding: 6px 10px;
        font-size: 0.76rem;
    }
    .berkas-media-strip {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 6px;
        padding: 8px 10px;
    }
    .berkas-media-strip > div:last-child {
        width: 100%;
        text-align: right;
    }
    .eval-item-card {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 10px !important;
        padding: 10px 12px;
    }
    .eval-item-card > div:last-child {
        width: 100%;
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: center !important;
        border-top: 1px dashed #e2e8f0;
        padding-top: 8px;
    }
    [data-bs-theme="dark"] .eval-item-card > div:last-child {
        border-top-color: #334155;
    }
}
</style>

<main class="main-content px-3 px-md-4 ebook-wrapper">
<div class="container-fluid">

    <?php
    // Flash messages
    if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_SESSION['flash_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['flash_success']); endif;
    if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['flash_error']); endif; ?>

    <!-- ══════════ HERO BANNER ══════════ -->
    <div class="ebook-hero p-4 p-md-5 mb-4">
        <div class="position-relative" style="z-index:2;">
            <!-- Back Button -->
            <div class="mb-3">
                <a href="<?= BASE_URL ?>index.php?url=siswa/materi" class="btn-ebook-back">
                    <i class="bi bi-arrow-left"></i> Semua Mata Pelajaran
                </a>
            </div>

            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-3"
                         style="background:rgba(255,255,255,.12); backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,.2); font-size:.78rem; font-weight:700; color:#fff;">
                        <i class="bi bi-book-half"></i>
                        <span>E-Book Materi Pembelajaran</span>
                    </div>
                    <h2 class="fw-bold text-white mb-1" style="font-size:clamp(1.4rem,3vw,2rem); letter-spacing:-.5px;">
                        <?= htmlspecialchars($namaMapel ?: 'Mata Pelajaran') ?>
                    </h2>
                    <?php if (!empty($namaGuru)): ?>
                    <p class="text-white mb-3" style="opacity:.85; font-size:.9rem;">
                        <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($namaGuru) ?>
                    </p>
                    <?php endif; ?>

                    <!-- Stat Pills -->
                    <div class="d-flex flex-wrap gap-2">
                        <span class="ebook-stat-pill"><i class="bi bi-journals"></i> <?= $totalMateri ?> Materi</span>
                        <span class="ebook-stat-pill"><i class="bi bi-patch-question-fill"></i> <?= $totalQuiz ?> Quiz</span>
                        <span class="ebook-stat-pill"><i class="bi bi-clipboard2-check-fill"></i> <?= $totalTugas ?> Tugas</span>
                        <?php if ($totalQuiz > 0 || $totalTugas > 0): ?>
                        <span class="ebook-stat-pill" style="background:rgba(52,211,153,.2); border-color:rgba(52,211,153,.3);">
                            <i class="bi bi-check2-all"></i>
                            Selesai: <?= $doneQuiz ?>/<?= $totalQuiz ?> Quiz · <?= $doneTugas ?>/<?= $totalTugas ?> Tugas
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <?php
                    $totalTask = $totalQuiz + $totalTugas;
                    $doneTask  = $doneQuiz + $doneTugas;
                    $pctDone   = $totalTask > 0 ? round(($doneTask / $totalTask) * 100) : ($totalMateri > 0 ? 15 : 0);
                    ?>
                    <div class="text-white mb-2" style="font-size:.82rem; font-weight:600; opacity:.8;">Progress Penyelesaian</div>
                    <div class="mapel-progress-bar mb-1">
                        <div class="mapel-progress-fill" style="width:<?= $pctDone ?>%"></div>
                    </div>
                    <div class="text-white" style="font-size:.82rem; opacity:.7;"><?= $pctDone ?>% Selesai</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════ BAB LIST (E-BOOK) ══════════ -->
    <?php if (empty($materiGroupedWithLinks)): ?>
        <div class="ebook-empty-state">
            <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex mb-3"
                 style="width:64px;height:64px;align-items:center;justify-content:center;">
                <i class="bi bi-journals fs-2"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">Belum Ada Materi</h5>
            <p class="text-muted small mb-3">Guru belum mengunggah materi untuk mata pelajaran ini.</p>
            <a href="<?= BASE_URL ?>index.php?url=siswa/materi" class="btn btn-primary rounded-pill px-4 fw-bold">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

    <?php else:
        $babIndex = 0;
        foreach ($materiGroupedWithLinks as $babNama => $materiItems):
            $color = $babColors[$babIndex % count($babColors)];
            $babIndex++;
            $totalItemBab = count($materiItems);
            $totalQuizBab = 0; $totalTugasBab = 0;
            foreach ($materiItems as $mi) {
                $totalQuizBab  += count($mi['linked_quiz']);
                $totalTugasBab += count($mi['linked_tugas']);
            }
    ?>
        <div class="bab-card mb-4 is-open" id="bab-card-<?= $babIndex ?>">
            <!-- BAB HEADER (clickable toggle) -->
            <div class="bab-header" onclick="toggleBab(<?= $babIndex ?>)" role="button" tabindex="0"
                 onkeydown="if(event.key==='Enter') toggleBab(<?= $babIndex ?>)">
                <div class="bab-number" style="background:<?= $color ?>;">
                    <?= $babIndex ?>
                </div>
                <div class="flex-grow-1 min-width-0">
                    <p class="bab-title"><?= htmlspecialchars($babNama) ?></p>
                    <p class="bab-meta">
                        <?= $totalItemBab ?> Materi
                        <?php if ($totalQuizBab > 0): ?> &bull; <?= $totalQuizBab ?> Quiz<?php endif; ?>
                        <?php if ($totalTugasBab > 0): ?> &bull; <?= $totalTugasBab ?> Tugas<?php endif; ?>
                    </p>
                </div>
                <i class="bi bi-chevron-down bab-chevron"></i>
            </div>

            <!-- BAB BODY -->
            <div class="bab-body">
                <?php foreach ($materiItems as $idx => $m):
                    $ext = strtolower(pathinfo($m['file_path'] ?? '', PATHINFO_EXTENSION));
                    [$fileIconClass, $fileTypeName] = getFileIcon($m['jenis_file'] ?? '', $ext);
                    $isYT   = ($m['jenis_file'] === 'youtube' || !empty($m['youtube_url']));
                    $hasFile = !empty($m['file_path']);
                    $fileUrl = $hasFile ? BASE_URL . 'assets/uploads/materi/' . htmlspecialchars($m['file_path']) : null;

                    // bg ikon sesuai warna bab (muted)
                    $iconBg = 'rgba(' . implode(',', sscanf(ltrim($color, '#'), "%02x%02x%02x")) . ',.12)';
                    $mQuiz  = $m['linked_quiz']  ?? [];
                    $mTugas = $m['linked_tugas'] ?? [];
                ?>
                    <!-- ══ UNIT CARD MATERI PEMBELAJARAN ══ -->
                    <div class="materi-unit-card shadow-xs mb-3">
                        <!-- 1. Header Metadata Materi: Nomor Materi, Tanggal, Tipe Berkas -->
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-2 mb-3 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill fw-bold px-3 py-1.5 shadow-xs" style="background:<?= $color ?>; color: #fff; font-size: 0.75rem;">
                                    <i class="bi bi-bookmark-check-fill me-1"></i> Materi #<?= $idx + 1 ?>
                                </span>
                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($m['created_at'])) ?>
                                </span>
                            </div>
                            <div>
                                <span class="badge" style="background:<?= $iconBg ?>; color:<?= $color ?>; font-size: 0.75rem; font-weight: 700; border-radius: 50rem; padding: 4px 12px;">
                                    <i class="bi <?= $fileIconClass ?> me-1"></i> <?= $fileTypeName ?>
                                </span>
                            </div>
                        </div>

                        <!-- 2. JUDUL MATERI & Tombol Aksi Pembelajaran -->
                        <div class="row align-items-center g-3 mb-3">
                            <div class="col-lg-8">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="materi-row-icon shadow-xs" style="background:<?= $iconBg ?>; min-width: 46px; width: 46px; height: 46px;">
                                        <i class="bi <?= $fileIconClass ?>" style="font-size: 1.4rem;"></i>
                                    </div>
                                    <div>
                                        <h4 class="fw-bold text-dark mb-1" style="font-size: 1.15rem; line-height: 1.4;">
                                            <?= htmlspecialchars($m['judul']) ?>
                                        </h4>
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($namaGuru ?: 'Guru Pengampu') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 text-lg-end">
                                <div class="d-flex align-items-center gap-2 justify-content-lg-end flex-wrap materi-actions-wrap">
                                    <?php if ($hasFile || $m['jenis_file'] !== 'youtube'): ?>
                                        <a href="<?= BASE_URL ?>index.php?url=siswa/bacaMateri&id=<?= $m['id'] ?>" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5 hover-scale">
                                            <i class="bi bi-book-half"></i>
                                            <span><?= $m['jenis_file'] === 'video' ? 'Tonton Video' : 'Buka Modul' ?></span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($isYT && !empty($m['youtube_url'])): ?>
                                        <a href="<?= BASE_URL ?>index.php?url=siswa/bacaMateri&id=<?= $m['id'] ?>" class="btn btn-sm btn-danger rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5 text-white hover-scale">
                                            <i class="bi bi-youtube"></i>
                                            <span>Video Pembelajaran</span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($hasFile): ?>
                                        <a href="<?= $fileUrl ?>" download class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center" title="Unduh Berkas Lampiran">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Materi Singkat & Rangkuman Pembelajaran -->
                        <?php if (!empty($m['deskripsi'])): ?>
                            <div class="materi-singkat-preview-box">
                                <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom flex-wrap gap-2">
                                    <span class="fw-bold text-primary d-flex align-items-center gap-1.5" style="font-size: 0.88rem;">
                                        <i class="bi bi-file-earmark-richtext-fill fs-6"></i>
                                        <span>3. Materi Singkat &amp; Rangkuman Pembelajaran</span>
                                    </span>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1" style="font-size: 0.7rem; font-weight: 600;">
                                        <i class="bi bi-text-paragraph me-1"></i>Format Rapi &amp; Justify
                                    </span>
                                </div>
                                <div class="rendered-materi-content px-1" style="max-height: 320px; overflow-y: auto;">
                                    <?= renderRichContent($m['deskripsi']) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- 4. Media & Berkas Pembelajaran -->
                        <div class="berkas-media-strip d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2 text-secondary" style="font-size: 0.82rem;">
                                <i class="bi bi-folder-check text-primary fs-6"></i>
                                <span class="fw-semibold text-dark">Media/Berkas:</span>
                                <?php if ($hasFile): ?>
                                    <span class="text-dark fw-bold"><?= htmlspecialchars(basename($m['file_path'])) ?></span>
                                    <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 0.7rem;"><?= strtoupper($ext ?: $m['jenis_file']) ?></span>
                                <?php elseif ($isYT): ?>
                                    <span class="text-danger fw-bold"><i class="bi bi-youtube me-1"></i>Video Streaming YouTube</span>
                                <?php else: ?>
                                    <span class="text-muted">Materi Teks Digital</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <a href="<?= BASE_URL ?>index.php?url=siswa/bacaMateri&id=<?= $m['id'] ?>" class="text-primary text-decoration-none fw-bold d-inline-flex align-items-center gap-1" style="font-size: 0.8rem;">
                                    <span>Baca Versi Lengkap E-Book</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>

                        <!-- 5. Evaluasi Terkait (Kuis & Tugas di bawah materi) -->
                        <?php if (!empty($mQuiz) || !empty($mTugas)): ?>
                            <div class="eval-section-box">
                                <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning-emphasis rounded-circle" style="width:26px; height:26px; font-size:0.8rem;">
                                            <i class="bi bi-award-fill"></i>
                                        </span>
                                        <span class="fw-bold text-dark" style="font-size: 0.85rem;">
                                            5. Evaluasi &amp; Penugasan Terkait Materi Ini
                                        </span>
                                    </div>
                                    <span class="text-muted" style="font-size: 0.72rem;">
                                        <i class="bi bi-info-circle me-1"></i>Selesai membaca? Kerjakan kuis &amp; tugas berikut
                                    </span>
                                </div>

                                <div class="row g-2">
                                    <!-- Kuis Terhubung -->
                                    <?php foreach ($mQuiz as $q):
                                        $sdDikerjakan = $q['sudah_dikerjakan'] ?? false;
                                        $nilaiQ       = $q['nilai_siswa'] ?? null;
                                        $isExpired    = !empty($q['tanggal_selesai']) && strtotime($q['tanggal_selesai']) < time();
                                    ?>
                                    <div class="col-md-6">
                                        <div class="eval-item-card d-flex align-items-center justify-content-between gap-2 h-100">
                                            <div class="d-flex align-items-center gap-2.5 min-width-0">
                                                <div class="qa-item-icon" style="background:<?= $sdDikerjakan ? '#dcfce7' : '#fef3c7' ?>;">
                                                    <i class="bi bi-patch-question-fill" style="color:<?= $sdDikerjakan ? '#16a34a' : '#d97706' ?>;"></i>
                                                </div>
                                                <div class="min-width-0">
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 mb-1" style="font-size:0.65rem;">Quiz</span>
                                                    <h6 class="qa-item-title text-truncate" title="<?= htmlspecialchars($q['judul']) ?>"><?= htmlspecialchars($q['judul']) ?></h6>
                                                    <span class="text-muted" style="font-size:0.7rem;">
                                                        <?php if (!empty($q['tanggal_selesai'])): ?>
                                                            <i class="bi bi-clock me-1"></i>Batas: <?= date('d M Y, H:i', strtotime($q['tanggal_selesai'])) ?>
                                                        <?php else: ?>
                                                            <i class="bi bi-infinity me-1"></i>Fleksibel
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                                                <?php if ($sdDikerjakan): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size:0.7rem;">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Selesai
                                                    </span>
                                                    <?php if ($nilaiQ !== null): ?>
                                                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size:0.7rem;">Nilai: <?= $nilaiQ ?></span>
                                                    <?php endif; ?>
                                                    <a href="<?= BASE_URL ?>index.php?url=siswa/reviewQuiz&quiz_id=<?= $q['id'] ?>" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1" style="font-size:0.72rem;">
                                                        <i class="bi bi-eye me-1"></i>Review
                                                    </a>
                                                <?php elseif ($isExpired): ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-0.5" style="font-size:0.7rem;">
                                                        <i class="bi bi-lock-fill me-1"></i>Ditutup
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size:0.7rem;">
                                                        <i class="bi bi-clock-fill me-1"></i>Belum
                                                    </span>
                                                    <a href="<?= BASE_URL ?>index.php?url=siswa/kerjakanQuiz&quiz_id=<?= $q['id'] ?>" class="btn btn-xs btn-warning rounded-pill px-3 py-1 fw-bold text-dark shadow-xs" style="font-size:0.72rem;">
                                                        <i class="bi bi-pencil-fill me-1"></i>Kerjakan
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>

                                    <!-- Tugas Terhubung -->
                                    <?php foreach ($mTugas as $t):
                                        $sdDikumpul = $t['sudah_dikumpul'] ?? false;
                                        $nilaiT     = $t['nilai_siswa']   ?? null;
                                        $isExpired  = !empty($t['deadline']) && strtotime($t['deadline']) < time();
                                    ?>
                                    <div class="col-md-6">
                                        <div class="eval-item-card d-flex align-items-center justify-content-between gap-2 h-100">
                                            <div class="d-flex align-items-center gap-2.5 min-width-0">
                                                <div class="qa-item-icon" style="background:<?= $sdDikumpul ? '#dcfce7' : '#fce7f3' ?>;">
                                                    <i class="bi bi-clipboard2-check-fill" style="color:<?= $sdDikumpul ? '#16a34a' : '#be185d' ?>;"></i>
                                                </div>
                                                <div class="min-width-0">
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5 mb-1" style="font-size:0.65rem;">Tugas</span>
                                                    <h6 class="qa-item-title text-truncate" title="<?= htmlspecialchars($t['judul']) ?>"><?= htmlspecialchars($t['judul']) ?></h6>
                                                    <span class="text-muted" style="font-size:0.7rem;">
                                                        <?php if (!empty($t['deadline'])): ?>
                                                            <i class="bi bi-calendar-event me-1"></i>Deadline: <?= date('d M, H:i', strtotime($t['deadline'])) ?>
                                                            <span class="badge <?= $isExpired ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> rounded-pill ms-1" style="font-size:0.62rem;"><?= $isExpired ? 'Lewat' : 'Aktif' ?></span>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                                                <?php if ($sdDikumpul): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size:0.7rem;">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Dikumpul
                                                    </span>
                                                    <?php if ($nilaiT !== null): ?>
                                                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size:0.7rem;">Nilai: <?= $nilaiT ?></span>
                                                    <?php endif; ?>
                                                    <a href="<?= BASE_URL ?>index.php?url=siswa/tugas&id=<?= $t['id'] ?>" class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-1" style="font-size:0.72rem;">
                                                        <i class="bi bi-eye me-1"></i>Lihat
                                                    </a>
                                                <?php elseif ($isExpired): ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size:0.7rem;">
                                                        <i class="bi bi-x-circle-fill me-1"></i>Terlambat
                                                    </span>
                                                    <a href="<?= BASE_URL ?>index.php?url=siswa/tugas&id=<?= $t['id'] ?>" class="btn btn-xs btn-outline-danger rounded-pill px-2.5 py-1 fw-semibold" style="font-size:0.72rem;">
                                                        <i class="bi bi-upload me-1"></i>Susulan
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size:0.7rem;">
                                                        <i class="bi bi-clock-fill me-1"></i>Belum
                                                    </span>
                                                    <a href="<?= BASE_URL ?>index.php?url=siswa/tugas&id=<?= $t['id'] ?>" class="btn btn-xs btn-danger rounded-pill px-3 py-1 fw-bold text-white shadow-xs" style="font-size:0.72rem;">
                                                        <i class="bi bi-upload me-1"></i>Kumpulkan
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div><!-- /.materi-unit-card -->
                <?php endforeach; // foreach materiItems ?>

                <?php if (empty($materiItems)): ?>
                    <div class="bab-empty"><i class="bi bi-inbox me-2"></i>Belum ada materi dalam bab ini.</div>
                <?php endif; ?>
            </div><!-- /.bab-body -->
        </div><!-- /.bab-card -->
    <?php endforeach; // foreach bab ?>
    <?php endif; // if materiGroupedWithLinks ?>

    <!-- Quiz & Tugas Tidak Terhubung (ditampilkan di bagian bawah) -->
    <?php
    // Kumpulkan quiz/tugas yang mapel ini tapi belum di-link ke materi mana pun
    $linkedQuizIds  = [];
    $linkedTugasIds = [];
    foreach ($materiGroupedWithLinks as $bab => $items) {
        foreach ($items as $item) {
            foreach ($item['linked_quiz']  as $q) { $linkedQuizIds[]  = (int)$q['id']; }
            foreach ($item['linked_tugas'] as $t) { $linkedTugasIds[] = (int)$t['id']; }
        }
    }
    $unlinkQuiz  = array_values(array_filter($quizForMapel,  fn($q) => !in_array((int)$q['id'], $linkedQuizIds)));
    $unlinkTugas = array_values(array_filter($tugasForMapel, fn($t) => !in_array((int)$t['id'], $linkedTugasIds)));

    if (!empty($unlinkQuiz) || !empty($unlinkTugas)): ?>
    <div class="bab-card mb-4 is-open" id="bab-card-extra">
        <div class="bab-header" onclick="toggleBabExtra()" role="button" tabindex="0">
            <div class="bab-number" style="background:#475569;">
                <i class="bi bi-collection-fill" style="font-size:1rem;"></i>
            </div>
            <div class="flex-grow-1 min-width-0">
                <p class="bab-title">Evaluasi Umum Mapel</p>
                <p class="bab-meta">Kuis &amp; Tugas umum yang tidak terikat dengan bab tertentu</p>
            </div>
            <i class="bi bi-chevron-down bab-chevron"></i>
        </div>
        <div class="bab-body p-3">
            <div class="row g-2">
                <?php foreach ($unlinkQuiz as $q):
                    $sdDikerjakan = isset($hasilQuizMap[(int)$q['id']]);
                    $nilaiQ       = $hasilQuizMap[(int)$q['id']]['total_nilai'] ?? null;
                    $isExpired    = !empty($q['tanggal_selesai']) && strtotime($q['tanggal_selesai']) < time();
                ?>
                <div class="col-md-6">
                    <div class="eval-item-card d-flex align-items-center justify-content-between gap-2 h-100">
                        <div class="d-flex align-items-center gap-2.5 min-width-0">
                            <div class="qa-item-icon" style="background:<?= $sdDikerjakan ? '#dcfce7' : '#fef3c7' ?>;">
                                <i class="bi bi-patch-question-fill" style="color:<?= $sdDikerjakan ? '#16a34a' : '#d97706' ?>;"></i>
                            </div>
                            <div class="min-width-0">
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 mb-1" style="font-size:0.65rem;">Quiz</span>
                                <h6 class="qa-item-title text-truncate" title="<?= htmlspecialchars($q['judul']) ?>"><?= htmlspecialchars($q['judul']) ?></h6>
                                <span class="text-muted" style="font-size:0.7rem;">
                                    <?php if (!empty($q['tanggal_selesai'])): ?>
                                        <i class="bi bi-clock me-1"></i>Batas: <?= date('d M Y, H:i', strtotime($q['tanggal_selesai'])) ?>
                                    <?php else: ?>
                                        <i class="bi bi-infinity me-1"></i>Fleksibel
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                            <?php if ($sdDikerjakan): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size:0.7rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i>Selesai
                                </span>
                                <?php if ($nilaiQ !== null): ?>
                                    <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size:0.7rem;">Nilai: <?= $nilaiQ ?></span>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>index.php?url=siswa/reviewQuiz&quiz_id=<?= $q['id'] ?>" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1" style="font-size:0.72rem;">
                                    <i class="bi bi-eye me-1"></i>Review
                                </a>
                            <?php elseif ($isExpired): ?>
                                <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-0.5" style="font-size:0.7rem;">
                                    <i class="bi bi-lock-fill me-1"></i>Ditutup
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size:0.7rem;">
                                    <i class="bi bi-clock-fill me-1"></i>Belum
                                </span>
                                <a href="<?= BASE_URL ?>index.php?url=siswa/kerjakanQuiz&quiz_id=<?= $q['id'] ?>" class="btn btn-xs btn-warning rounded-pill px-3 py-1 fw-bold text-dark shadow-xs" style="font-size:0.72rem;">
                                    <i class="bi bi-pencil-fill me-1"></i>Kerjakan
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php foreach ($unlinkTugas as $t):
                    $sdDikumpul = isset($pengumpulanMap[(int)$t['id']]);
                    $nilaiT     = $pengumpulanMap[(int)$t['id']]['nilai'] ?? null;
                    $isExpired  = !empty($t['deadline']) && strtotime($t['deadline']) < time();
                ?>
                <div class="col-md-6">
                    <div class="eval-item-card d-flex align-items-center justify-content-between gap-2 h-100">
                        <div class="d-flex align-items-center gap-2.5 min-width-0">
                            <div class="qa-item-icon" style="background:<?= $sdDikumpul ? '#dcfce7' : '#fce7f3' ?>;">
                                <i class="bi bi-clipboard2-check-fill" style="color:<?= $sdDikumpul ? '#16a34a' : '#be185d' ?>;"></i>
                            </div>
                            <div class="min-width-0">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5 mb-1" style="font-size:0.65rem;">Tugas</span>
                                <h6 class="qa-item-title text-truncate" title="<?= htmlspecialchars($t['judul']) ?>"><?= htmlspecialchars($t['judul']) ?></h6>
                                <span class="text-muted" style="font-size:0.7rem;">
                                    <?php if (!empty($t['deadline'])): ?>
                                        <i class="bi bi-calendar-event me-1"></i>Deadline: <?= date('d M, H:i', strtotime($t['deadline'])) ?>
                                        <span class="badge <?= $isExpired ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> rounded-pill ms-1" style="font-size:0.62rem;"><?= $isExpired ? 'Lewat' : 'Aktif' ?></span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                            <?php if ($sdDikumpul): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size:0.7rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i>Dikumpul
                                </span>
                                <?php if ($nilaiT !== null): ?>
                                    <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size:0.7rem;">Nilai: <?= $nilaiT ?></span>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>index.php?url=siswa/tugas&id=<?= $t['id'] ?>" class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-1" style="font-size:0.72rem;">
                                    <i class="bi bi-eye me-1"></i>Lihat
                                </a>
                            <?php elseif ($isExpired): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size:0.7rem;">
                                    <i class="bi bi-x-circle-fill me-1"></i>Terlambat
                                </span>
                                <a href="<?= BASE_URL ?>index.php?url=siswa/tugas&id=<?= $t['id'] ?>" class="btn btn-xs btn-outline-danger rounded-pill px-2.5 py-1 fw-semibold" style="font-size:0.72rem;">
                                    <i class="bi bi-upload me-1"></i>Susulan
                                </a>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size:0.7rem;">
                                    <i class="bi bi-clock-fill me-1"></i>Belum
                                </span>
                                <a href="<?= BASE_URL ?>index.php?url=siswa/tugas&id=<?= $t['id'] ?>" class="btn btn-xs btn-danger rounded-pill px-3 py-1 fw-bold text-white shadow-xs" style="font-size:0.72rem;">
                                    <i class="bi bi-upload me-1"></i>Kumpulkan
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
</main>

<script>
function toggleBab(idx) {
    const card = document.getElementById('bab-card-' + idx);
    if (card) card.classList.toggle('is-open');
}
function toggleBabExtra() {
    const card = document.getElementById('bab-card-extra');
    if (card) card.classList.toggle('is-open');
}

// Keyboard accessibility
document.querySelectorAll('.bab-header').forEach(function(header) {
    header.addEventListener('keydown', function(e) {
        if (e.key === ' ') { e.preventDefault(); header.click(); }
    });
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
