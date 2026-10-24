<?php
/**
 * Halaman Khusus Pembaca Materi & Modul Pembelajaran Siswa (Modern Studio E-Reader)
 * Desain Responsif, Profesional, Rapi Sempurna untuk Smartphone (HP) maupun Desktop PC.
 */
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/navbar.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';

if (!function_exists('getYouTubeEmbedUrlReader')) {
    function getYouTubeEmbedUrlReader($url) {
        if (empty($url)) return '';
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
        if (preg_match($pattern, $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
        return $url;
    }
}

$ext = strtolower(pathinfo($materi['file_path'] ?? '', PATHINFO_EXTENSION));
$isPdf = ($materi['jenis_file'] === 'pdf' || $ext === 'pdf');
$isImage = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg']);
$isVideoMp4 = ($materi['jenis_file'] === 'video' || in_array($ext, ['mp4', 'webm', 'ogg']));
$isYouTube = ($materi['jenis_file'] === 'youtube' || !empty($materi['youtube_url']));
$isOfficeDoc = in_array($ext, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx']);

$fileUrl = !empty($materi['file_path']) ? BASE_URL . 'assets/uploads/materi/' . htmlspecialchars($materi['file_path']) : null;
$embedUrl = $isYouTube ? getYouTubeEmbedUrlReader($materi['youtube_url'] ?? '') : '';

// Hitung ukuran file fisik jika ada
$fileSizeBytes = null;
if (!empty($materi['file_path']) && file_exists(ROOT_PATH . 'assets/uploads/materi/' . $materi['file_path'])) {
    $fileSizeBytes = @filesize(ROOT_PATH . 'assets/uploads/materi/' . $materi['file_path']);
}
$fileSizeFormatted = $fileSizeBytes ? round($fileSizeBytes / (1024 * 1024), 1) . ' MB' : null;

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
?>

<style>
/* ══════════════════════════════════════════════════════════════════
   🎨 MODERN E-READER DESIGN SYSTEM (RESPONSIVE & MOBILE-FIRST)
   ══════════════════════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap');

.reader-page-wrapper {
    max-width: 1540px;
    margin: 0 auto;
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    color: #1e293b;
    letter-spacing: -0.01em;
    -webkit-font-smoothing: antialiased;
}

h1, h2, h3, h4, h5, .materi-main-title {
    font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif !important;
    letter-spacing: -0.02em;
}

/* 1. Header Desktop */
.reader-header-desktop {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.03);
    padding: 14px 20px;
}
[data-bs-theme="dark"] .reader-header-desktop {
    background: #1e293b;
    border-color: #334155;
}

/* 2. Header Mobile (Sleek Native App Bar - Khusus HP) */
.reader-header-mobile {
    display: none;
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 3px 14px rgba(0, 0, 0, 0.04);
    padding: 10px 14px;
    margin-bottom: 14px;
}
[data-bs-theme="dark"] .reader-header-mobile {
    background: #1e293b;
    border-color: #334155;
}

/* 3. Badge Penanda Bab & Judul */
.bab-badge-pill {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    color: #ffffff;
    border-radius: 50rem;
    padding: 6px 14px;
    font-size: 0.82rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.2);
}
.materi-main-title {
    font-size: 1.58rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.35;
    letter-spacing: -0.3px;
}
[data-bs-theme="dark"] .materi-main-title {
    color: #f8fafc;
}

/* 4. Rich Text Content & List Formatting */
.rendered-materi-content {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.85;
    font-size: 0.95rem;
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
    margin-bottom: 14px;
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

/* Callout Box Catatan */
.rich-callout-box, .rendered-materi-content .rich-callout-box {
    background-color: #f0fdf4;
    border-left: 4px solid #16a34a;
    padding: 12px 16px;
    border-radius: 8px;
    margin: 12px 0;
    color: #166534;
    font-size: 0.92rem;
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

/* 5. Panggung Pembaca Berkas & Media */
.reader-stage-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 720px;
    min-height: 520px;
    transition: all 0.25s ease;
}
[data-bs-theme="dark"] .reader-stage-card {
    background: #1e293b;
    border-color: #334155;
}

.reader-stage-toolbar {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    z-index: 10;
}
[data-bs-theme="dark"] .reader-stage-toolbar {
    background: #0f172a;
    border-color: #334155;
}

.reader-stage-body {
    flex: 1;
    position: relative;
    background: #f1f5f9;
    overflow: hidden;
}
[data-bs-theme="dark"] .reader-stage-body {
    background: #0b1120;
}

.reader-iframe-element {
    width: 100%;
    height: 100%;
    border: none;
    display: block;
    background: #ffffff;
}

/* 6. Evaluasi Cards (Quiz & Tugas) */
.eval-card-item {
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    transition: all 0.2s ease;
}
.eval-card-item:hover {
    border-color: #cbd5e1;
    box-shadow: 0 6px 18px rgba(0,0,0,0.05);
    transform: translateY(-2px);
}
[data-bs-theme="dark"] .eval-card-item {
    background: #1e293b;
    border-color: #334155;
}

/* 7. Companion Sidebar (Desktop) */
.companion-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 3px 14px rgba(0, 0, 0, 0.03);
    padding: 18px;
    margin-bottom: 16px;
}
[data-bs-theme="dark"] .companion-card {
    background: #1e293b;
    border-color: #334155;
}

.teacher-avatar-circle {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.15rem;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}

.playlist-materi-item {
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    text-decoration: none;
    display: block;
    transition: all 0.18s ease;
}
.playlist-materi-item:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
    transform: translateX(3px);
}
[data-bs-theme="dark"] .playlist-materi-item {
    background: #0f172a;
    border-color: #334155;
}

/* Fullscreen Mode */
.reader-stage-card:fullscreen {
    border-radius: 0 !important;
    height: 100vh !important;
    max-height: 100vh !important;
    width: 100vw !important;
}

/* Video Responsive Aspect Ratio */
.video-responsive-169 {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    background: #000;
    border-radius: 14px;
}
.video-responsive-169 iframe,
.video-responsive-169 video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

/* ══════════════════════════════════════════════════════════════════
   📱 OPTIMISASI KHUSUS TAMPILAN SMARTPHONE (HP: < 992px)
   ══════════════════════════════════════════════════════════════════ */
@media (max-width: 991.98px) {
    .reader-header-desktop {
        display: none !important;
    }
    .reader-header-mobile {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
    }
    .materi-main-title {
        font-size: 1.3rem;
    }
    .reader-stage-card {
        height: 60vh;
        min-height: 420px;
    }
    .desktop-sidebar-only {
        display: none !important;
    }
    .mobile-sidebar-section {
        display: block !important;
    }
}

@media (min-width: 992px) {
    .mobile-sidebar-section {
        display: none !important;
    }
}
</style>

<main class="main-content px-2 px-md-3 px-lg-4 py-2 py-md-3">
    <div class="container-fluid reader-page-wrapper p-0">

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 🖥️ 1A. HEADER ATAS DESKTOP (NAVIGASI & AKSI CEPAT) -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div class="reader-header-desktop mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2.5">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>index.php?url=siswa/materiMapel&mapel_id=<?= (int)$materi['mapel_id'] ?>" class="btn btn-sm btn-light rounded-pill border fw-bold text-dark px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5 hover-scale">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali ke E-Book Mapel</span>
                </a>

                <a href="<?= BASE_URL ?>index.php?url=siswa/materi" class="btn btn-sm btn-white rounded-pill border text-muted px-2.5 py-1.5 shadow-xs d-inline-flex align-items-center gap-1" title="Semua Mata Pelajaran">
                    <i class="bi bi-grid-fill"></i>
                    <span>Semua Mapel</span>
                </a>

                <div class="vr bg-secondary opacity-25" style="height: 20px;"></div>

                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold" style="font-size:0.8rem;">
                    <i class="bi bi-journal-bookmark-fill me-1"></i><?= htmlspecialchars($materi['nama_mapel']) ?>
                </span>

                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size:0.75rem;">
                    <i class="bi bi-mortarboard-fill me-1"></i><?= htmlspecialchars($materi['nama_kelas'] ?? 'Rombel Anda') ?>
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if ($fileUrl): ?>
                    <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-warning text-dark rounded-pill fw-bold px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5" title="Buka Dokumen di Tab Baru">
                        <i class="bi bi-box-arrow-up-right"></i>
                        <span>Buka Tab Baru</span>
                    </a>
                    <a href="<?= $fileUrl ?>" download class="btn btn-sm btn-outline-primary rounded-pill fw-bold px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5" title="Unduh Berkas ke Perangkat">
                        <i class="bi bi-download"></i>
                        <span>Unduh File</span>
                    </a>
                <?php endif; ?>

                <?php if ($isYouTube && !empty($materi['youtube_url'])): ?>
                    <a href="<?= htmlspecialchars($materi['youtube_url']) ?>" target="_blank" class="btn btn-sm btn-danger rounded-pill fw-bold px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-youtube"></i>
                        <span>Tonton di YouTube</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 📱 1B. HEADER ATAS SMARTPHONE (HP: COMPACT & SLEEK) -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div class="reader-header-mobile">
            <a href="<?= BASE_URL ?>index.php?url=siswa/materiMapel&mapel_id=<?= (int)$materi['mapel_id'] ?>" class="btn btn-sm btn-light border rounded-circle d-flex align-items-center justify-content-center shadow-xs text-dark" style="width: 36px; height: 36px; flex-shrink: 0;" title="Kembali ke E-Book Mapel">
                <i class="bi bi-arrow-left fs-6"></i>
            </a>

            <div class="px-2 text-truncate text-start flex-grow-1" style="min-width: 0;">
                <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem; letter-spacing: -0.2px;">
                    <?= htmlspecialchars($materi['judul']) ?>
                </div>
                <div class="text-primary small text-truncate fw-semibold" style="font-size: 0.72rem;">
                    <i class="bi bi-journal-bookmark-fill me-1"></i><?= htmlspecialchars($materi['nama_mapel']) ?>
                </div>
            </div>

            <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                <?php if ($fileUrl): ?>
                    <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-warning text-dark rounded-pill fw-bold px-2.5 py-1 d-inline-flex align-items-center gap-1 shadow-xs" style="font-size: 0.75rem;" title="Buka di Aplikasi HP">
                        <i class="bi bi-phone-fill"></i>
                        <span>Buka</span>
                    </a>
                    <a href="<?= $fileUrl ?>" download class="btn btn-sm btn-light border rounded-circle d-flex align-items-center justify-content-center shadow-xs text-primary" style="width: 34px; height: 34px;" title="Unduh File">
                        <i class="bi bi-download"></i>
                    </a>
                <?php endif; ?>

                <?php if ($isYouTube && !empty($materi['youtube_url'])): ?>
                    <a href="<?= htmlspecialchars($materi['youtube_url']) ?>" target="_blank" class="btn btn-sm btn-danger rounded-circle d-flex align-items-center justify-content-center shadow-xs text-white" style="width: 34px; height: 34px;" title="YouTube">
                        <i class="bi bi-youtube"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 📐 GRID UTAMA: ALUR PEMBELAJARAN (STAGE) + SIDEBAR INFO -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div class="row g-3 g-xl-4">

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- 🌟 KOLOM UTAMA: URUTAN SESUAI PERMINTAAN USER             -->
            <!-- 1. BAB                                                    -->
            <!-- 2. JUDUL MATERI                                           -->
            <!-- 3. MATERI SINGKAT & RANGKUMAN                             -->
            <!-- 4. BERKAS FILE & VIDEO                                    -->
            <!-- 5. TUGAS & QUIZ DI BAWAHNYA                               -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="col-12 col-lg-8 col-xl-9">

                <!-- ═══════════════════════════════════════════════════════ -->
                <!-- 1. BAB & 2. JUDUL MATERI (HEADER CARD PEMBELAJARAN) -->
                <!-- ═══════════════════════════════════════════════════════ -->
                <div class="card border rounded-4 shadow-xs p-3 p-md-4 bg-white mb-3" style="border: 1px solid #e2e8f0 !important;">
                    <!-- 1. BAB -->
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2.5">
                        <?php $babTitle = !empty($materi['judul_bab']) ? $materi['judul_bab'] : 'Materi Umum'; ?>
                        <div class="bab-badge-pill">
                            <i class="bi bi-folder2-open"></i>
                            <span>BAB: <?= htmlspecialchars($babTitle) ?></span>
                        </div>

                        <?php if (!empty($materi['urutan'])): ?>
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.74rem;">
                                <i class="bi bi-hash text-primary"></i>Materi Ke-<?= (int)$materi['urutan'] ?>
                            </span>
                        <?php endif; ?>

                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size:0.74rem;">
                            <i class="bi bi-journal-check me-1"></i><?= htmlspecialchars($materi['nama_mapel']) ?>
                        </span>
                    </div>

                    <!-- 2. JUDUL MATERI -->
                    <h1 class="materi-main-title mb-2.5">
                        <?= htmlspecialchars($materi['judul']) ?>
                    </h1>

                    <!-- Metadata Tambahan -->
                    <div class="d-flex align-items-center gap-3 text-muted small flex-wrap pt-2 border-top" style="font-size: 0.82rem;">
                        <span class="d-flex align-items-center gap-1.5">
                            <i class="bi bi-person-circle text-primary"></i>
                            <span>Guru Pengampu: <b class="text-dark"><?= htmlspecialchars($materi['nama_guru'] ?? 'Guru Pengampu') ?></b></span>
                        </span>
                        <span class="d-flex align-items-center gap-1.5">
                            <i class="bi bi-calendar3 text-muted"></i>
                            <span>Diterbitkan: <?= date('d M Y, H:i', strtotime($materi['created_at'])) ?> WIB</span>
                        </span>
                        <span class="d-flex align-items-center gap-1.5">
                            <i class="bi bi-file-earmark-code text-info"></i>
                            <span>Format: <b class="text-uppercase text-dark"><?= htmlspecialchars($materi['jenis_file'] ?: ($isPdf ? 'PDF' : 'MODUL')) ?></b></span>
                        </span>
                        <?php if ($fileSizeFormatted): ?>
                            <span class="d-flex align-items-center gap-1.5">
                                <i class="bi bi-hdd text-secondary"></i>
                                <span>Ukuran: <?= $fileSizeFormatted ?></span>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════ -->
                <!-- 3. MATERI SINGKAT & RANGKUMAN PEMBELAJARAN             -->
                <!-- ═══════════════════════════════════════════════════════ -->
                <div class="card border rounded-4 shadow-xs p-3 p-md-4 bg-white mb-3" style="border: 1px solid #e2e8f0 !important;">
                    <div class="d-flex justify-content-between align-items-center pb-2.5 mb-3 border-bottom flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary-subtle text-primary rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-file-earmark-richtext-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">
                                    3. Materi Singkat & Rangkuman Pembelajaran
                                </h5>
                                <small class="text-muted" style="font-size: 0.76rem;">Rangkuman materi, intisari, dan instruksi penting dari guru pengampu.</small>
                            </div>
                        </div>
                        <span class="badge bg-light text-primary border rounded-pill px-3 py-1 fw-bold" style="font-size:0.75rem;">
                            <i class="bi bi-book-half me-1"></i>Modul Baca Terstruktur
                        </span>
                    </div>

                    <?php if (!empty($materi['deskripsi'])): ?>
                        <div class="rendered-materi-content">
                            <?= renderRichContent($materi['deskripsi']) ?>
                        </div>
                    <?php else: ?>
                        <div class="p-3 bg-light rounded-3 text-muted small text-center">
                            <i class="bi bi-info-circle me-1 text-primary"></i>Tidak ada teks ringkasan tertulis untuk materi ini. Silakan langsung pelajari modul berkas atau tonton media video pada bagian ke-4 di bawah.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ═══════════════════════════════════════════════════════ -->
                <!-- 4. BERKAS FILE DAN VIDIO                               -->
                <!-- ═══════════════════════════════════════════════════════ -->
                <div class="card border rounded-4 shadow-xs bg-white mb-3 overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
                    <div class="card-header bg-light border-0 py-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-danger-subtle text-danger rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-play-circle-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">
                                    4. Berkas Modul & Media Pembelajaran
                                </h5>
                                <small class="text-muted" style="font-size: 0.76rem;">Pelajari dokumen digital, infografis gambar, atau tonton video streaming berikut.</small>
                            </div>
                        </div>

                        <!-- Kontrol Pemirsa Media / PDF -->
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <?php if ($isPdf && $fileUrl): ?>
                                <div class="btn-group btn-group-sm rounded-pill border p-0.5 bg-white shadow-xs" role="group">
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-bold" id="btnEngineGoogle" onclick="setViewerEngine('google')" style="font-size:0.75rem;" title="Mode Web Cloud (Bebas Download Otomatis)">
                                        <i class="bi bi-cloud-check-fill me-1"></i><span>Web View</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-white rounded-pill px-2.5 py-0.5 fw-semibold text-muted" id="btnEngineNative" onclick="setViewerEngine('native')" style="font-size:0.75rem;" title="Beralih ke Engine PDF Asli">
                                        <i class="bi bi-file-earmark-pdf me-1"></i><span class="d-none d-sm-inline">Asli</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <?php if ($fileUrl): ?>
                                <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-white border rounded-pill px-2.5 py-1 shadow-xs text-dark fw-bold d-none d-sm-inline-flex align-items-center" title="Buka Dokumen di Tab Baru">
                                    <i class="bi bi-box-arrow-up-right me-1 text-primary"></i><span>Buka Tab Baru</span>
                                </a>
                                <a href="<?= $fileUrl ?>" download class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 shadow-xs fw-bold d-inline-flex align-items-center" title="Unduh Berkas ke Perangkat">
                                    <i class="bi bi-download me-1"></i><span>Unduh</span>
                                </a>
                            <?php endif; ?>

                            <?php if ($isPdf && $fileUrl): ?>
                                <button type="button" class="btn btn-sm btn-white border rounded-pill px-2 py-1 shadow-xs text-dark" id="btnToggleFullscreen" onclick="toggleFullscreenStage()" title="Mode Layar Penuh">
                                    <i class="bi bi-fullscreen" id="iconFullscreen"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Area Panggung Dokumen / Media Player -->
                    <div class="reader-stage-card border-0 rounded-0" id="readerStageCard">
                        <div class="reader-stage-body">

                            <?php if ($isPdf && $fileUrl): ?>
                                <!-- 1. PDF EMBEDDED VIEWER (DEFAULT GOOGLE CLOUD VIEWER - BEBAS AUTO-DOWNLOAD) -->
                                <div id="readerLoadingOverlay" class="position-absolute top-50 start-50 translate-middle text-center p-4" style="z-index: 1; pointer-events: none;">
                                    <div class="spinner-border text-primary mb-2" role="status" style="width: 2.25rem; height: 2.25rem;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <div class="fw-bold text-dark small">Menyiapkan Tampilan Modul...</div>
                                    <small class="text-muted">Dokumen dibuka langsung tanpa mengunduh ke perangkat Anda.</small>
                                </div>
                                <iframe 
                                    id="readerIframe" 
                                    src="https://docs.google.com/gview?embedded=true&url=<?= urlencode($fileUrl) ?>" 
                                    class="reader-iframe-element position-relative" 
                                    style="z-index: 2;"
                                    onload="var ov = document.getElementById('readerLoadingOverlay'); if(ov) ov.style.display='none';"
                                    allowfullscreen 
                                    title="<?= htmlspecialchars($materi['judul']) ?>">
                                </iframe>

                            <?php elseif ($isYouTube && !empty($embedUrl)): ?>
                                <!-- 2. PEMUTAR VIDEO YOUTUBE STREAMING RESPONSIVE -->
                                <div class="p-2 p-md-4 h-100 d-flex flex-column justify-content-center bg-black">
                                    <div class="video-responsive-169 shadow-lg">
                                        <iframe src="<?= htmlspecialchars($embedUrl) ?>?rel=0&autoplay=0" title="<?= htmlspecialchars($materi['judul']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    </div>
                                </div>

                            <?php elseif ($isVideoMp4 && $fileUrl): ?>
                                <!-- 3. PEMUTAR VIDEO MP4 LOKAL -->
                                <div class="p-2 p-md-4 h-100 d-flex flex-column justify-content-center bg-black">
                                    <div class="video-responsive-169 shadow-lg">
                                        <video src="<?= $fileUrl ?>" controls class="w-100 h-100" style="object-fit: contain;">
                                            Browser Anda tidak mendukung pemutar video HTML5.
                                        </video>
                                    </div>
                                </div>

                            <?php elseif ($isImage && $fileUrl): ?>
                                <!-- 4. PENAMPIL GAMBAR / INFOGRAFIS -->
                                <div class="p-3 p-md-4 h-100 text-center d-flex flex-column align-items-center justify-content-center bg-light">
                                    <img src="<?= $fileUrl ?>" alt="<?= htmlspecialchars($materi['judul']) ?>" class="img-fluid rounded-3 shadow-sm border mx-auto d-block" style="max-height: 85%; object-fit: contain;">
                                    <div class="mt-3">
                                        <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">
                                            <i class="bi bi-zoom-in me-1"></i> Lihat Gambar Ukuran Penuh
                                        </a>
                                    </div>
                                </div>

                            <?php elseif ($isOfficeDoc && $fileUrl): ?>
                                <!-- 5. DOKUMEN OFFICE (WORD, PPT, EXCEL) - GOOGLE CLOUD VIEWER -->
                                <iframe 
                                    src="https://docs.google.com/gview?embedded=true&url=<?= urlencode($fileUrl) ?>" 
                                    class="reader-iframe-element" 
                                    allowfullscreen 
                                    title="<?= htmlspecialchars($materi['judul']) ?>">
                                </iframe>

                            <?php else: ?>
                                <!-- 6. MATERI BERUPA TEKS / TANPA BERKAS FISIK TAMBAHAN -->
                                <div class="p-4 p-md-5 text-center text-muted my-auto">
                                    <i class="bi bi-journal-check fs-1 d-block mb-2 text-primary opacity-75"></i>
                                    <h6 class="fw-bold text-dark">Materi Digital Berbasis Teks</h6>
                                    <p class="small text-muted mb-0">Materi ini telah tersaji lengkap pada bagian <b>3. Materi Singkat & Rangkuman Pembelajaran</b> di atas. Lanjutkan ke evaluasi di bawah.</p>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════ -->
                <!-- 5. TUGAS DAN QUIZ DI BAWAHNYA                          -->
                <!-- ═══════════════════════════════════════════════════════ -->
                <div class="card border rounded-4 shadow-xs p-3 p-md-4 bg-white mb-4" style="border: 1px solid #e2e8f0 !important;">
                    <div class="d-flex justify-content-between align-items-center pb-2.5 mb-3 border-bottom flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-success-subtle text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-check2-circle fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">
                                    5. Evaluasi Terkait (Kuis & Tugas Pembelajaran)
                                </h5>
                                <small class="text-muted" style="font-size: 0.76rem;">Selesaikan evaluasi berikut setelah mempelajari rangkuman dan berkas materi di atas.</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.75rem;">
                                <i class="bi bi-question-circle-fill text-warning me-1"></i><?= count($linkedQuiz) ?> Kuis
                            </span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.75rem;">
                                <i class="bi bi-clipboard2-check-fill text-info me-1"></i><?= count($linkedTugas) ?> Tugas
                            </span>
                        </div>
                    </div>

                    <?php if (empty($linkedQuiz) && empty($linkedTugas)): ?>
                        <div class="p-4 bg-light rounded-3 text-center">
                            <i class="bi bi-patch-check-fill fs-2 text-success mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark mb-1">Materi Selesai Dipelajari!</h6>
                            <p class="text-muted small mb-0">Guru pengampu tidak menetapkan tugas atau kuis khusus untuk sesi materi ini. Anda dapat melanjutkan membaca materi berikutnya.</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <!-- DAFTAR KUIS TERKAIT -->
                            <?php foreach ($linkedQuiz as $q): 
                                $sdDikerjakan = $q['sudah_dikerjakan'] ?? false;
                                $nilaiQ       = $q['nilai_siswa'] ?? null;
                                $isExpired    = !empty($q['tanggal_selesai']) && strtotime($q['tanggal_selesai']) < time();
                            ?>
                                <div class="col-12 col-md-6">
                                    <div class="eval-card-item p-3 shadow-xs h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.7rem;">
                                                    <i class="bi bi-patch-question-fill text-warning me-1"></i>Kuis Pembelajaran
                                                </span>
                                                <?php if ($sdDikerjakan): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.7rem;">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Sudah Selesai
                                                    </span>
                                                <?php elseif ($isExpired): ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1 fw-semibold" style="font-size:0.7rem;">
                                                        <i class="bi bi-lock-fill me-1"></i>Ditutup
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.7rem;">
                                                        <i class="bi bi-clock-fill me-1"></i>Belum Dikerjakan
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1.5" style="font-size:0.95rem; line-height:1.4;">
                                                <?= htmlspecialchars($q['judul']) ?>
                                            </h6>
                                            <?php if (!empty($q['tanggal_selesai'])): ?>
                                                <small class="text-muted d-block mb-2" style="font-size:0.75rem;">
                                                    <i class="bi bi-calendar-event me-1 text-primary"></i>Batas Waktu: <?= date('d M Y, H:i', strtotime($q['tanggal_selesai'])) ?> WIB
                                                </small>
                                            <?php endif; ?>
                                        </div>

                                        <div class="pt-2.5 mt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <?php if ($sdDikerjakan): ?>
                                                <span class="fw-bold text-success" style="font-size:0.88rem;">
                                                    <i class="bi bi-trophy-fill text-warning me-1"></i>Nilai: <?= $nilaiQ !== null ? $nilaiQ : '-' ?>
                                                </span>
                                                <a href="<?= BASE_URL ?>index.php?url=siswa/reviewQuiz&quiz_id=<?= $q['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                                                    <i class="bi bi-eye me-1"></i>Review Jawaban
                                                </a>
                                            <?php elseif ($isExpired): ?>
                                                <span class="text-muted small">Waktu kuis telah berakhir</span>
                                                <button class="btn btn-sm btn-secondary rounded-pill px-3 disabled" disabled>Ditutup</button>
                                            <?php else: ?>
                                                <span class="text-danger small fw-semibold">
                                                    <i class="bi bi-exclamation-circle me-1"></i>Wajib Dikerjakan
                                                </span>
                                                <a href="<?= BASE_URL ?>index.php?url=siswa/kerjakanQuiz&quiz_id=<?= $q['id'] ?>" class="btn btn-sm btn-warning text-dark rounded-pill px-3.5 py-1.5 fw-bold shadow-xs">
                                                    <i class="bi bi-pencil-square me-1"></i>Kerjakan Kuis
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- DAFTAR TUGAS TERKAIT -->
                            <?php foreach ($linkedTugas as $t): 
                                $sdDikumpul = $t['sudah_dikumpul'] ?? false;
                                $nilaiT     = $t['nilai_siswa'] ?? null;
                                $isExpired  = !empty($t['deadline']) && strtotime($t['deadline']) < time();
                            ?>
                                <div class="col-12 col-md-6">
                                    <div class="eval-card-item p-3 shadow-xs h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.7rem;">
                                                    <i class="bi bi-clipboard2-check-fill text-info me-1"></i>Tugas KBM
                                                </span>
                                                <?php if ($sdDikumpul): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.7rem;">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Sudah Dikumpulkan
                                                    </span>
                                                <?php elseif ($isExpired): ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1 fw-semibold" style="font-size:0.7rem;">
                                                        <i class="bi bi-lock-fill me-1"></i>Deadline Lewat
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size:0.7rem;">
                                                        <i class="bi bi-clock-fill me-1"></i>Belum Dikumpulkan
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1.5" style="font-size:0.95rem; line-height:1.4;">
                                                <?= htmlspecialchars($t['judul']) ?>
                                            </h6>
                                            <?php if (!empty($t['deadline'])): ?>
                                                <small class="text-muted d-block mb-2" style="font-size:0.75rem;">
                                                    <i class="bi bi-calendar-event me-1 text-primary"></i>Deadline: <?= date('d M Y, H:i', strtotime($t['deadline'])) ?> WIB
                                                </small>
                                            <?php endif; ?>
                                        </div>

                                        <div class="pt-2.5 mt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <?php if ($sdDikumpul): ?>
                                                <span class="fw-bold text-success" style="font-size:0.88rem;">
                                                    <i class="bi bi-award-fill text-success me-1"></i><?= $nilaiT !== null ? 'Nilai: ' . $nilaiT : 'Menunggu Penilaian' ?>
                                                </span>
                                                <a href="<?= BASE_URL ?>index.php?url=siswa/tugas" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">
                                                    <i class="bi bi-eye me-1"></i>Lihat Tugas
                                                </a>
                                            <?php elseif ($isExpired): ?>
                                                <span class="text-muted small">Batas waktu telah lewat</span>
                                                <a href="<?= BASE_URL ?>index.php?url=siswa/tugas" class="btn btn-sm btn-secondary rounded-pill px-3">Detail Tugas</a>
                                            <?php else: ?>
                                                <span class="text-primary small fw-semibold">
                                                    <i class="bi bi-info-circle me-1"></i>Siap Dikumpulkan
                                                </span>
                                                <a href="<?= BASE_URL ?>index.php?url=siswa/tugas" class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-bold shadow-xs">
                                                    <i class="bi bi-cloud-upload me-1"></i>Kumpulkan Tugas
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Bagian Khusus Mobile: Materi Lain pada Mapel Ini -->
                <div class="mobile-sidebar-section mb-4">
                    <div class="card border rounded-4 shadow-xs p-3 bg-white" style="border: 1px solid #e2e8f0 !important;">
                        <h6 class="fw-bold text-dark mb-2.5 d-flex align-items-center gap-1.5" style="font-size:0.92rem;">
                            <i class="bi bi-collection-play-fill text-primary"></i>Materi Lain pada Mata Pelajaran Ini
                        </h6>
                        <?php if (!empty($materiTerkait)): ?>
                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($materiTerkait as $mt): ?>
                                    <a href="<?= BASE_URL ?>index.php?url=siswa/bacaMateri&id=<?= $mt['id'] ?>" class="playlist-materi-item shadow-xs">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-primary-subtle text-primary text-uppercase" style="font-size:0.62rem;">
                                                <?= htmlspecialchars($mt['jenis_file'] ?: 'MODUL') ?>
                                            </span>
                                            <small class="text-muted" style="font-size:0.68rem;"><?= date('d/m/Y', strtotime($mt['created_at'])) ?></small>
                                        </div>
                                        <div class="fw-bold text-dark text-truncate small" style="font-size:0.84rem;">
                                            <?= htmlspecialchars($mt['judul']) ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted mb-0">Belum ada materi lain yang ditambahkan pada mapel ini.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- 📚 KOLOM KANAN (SIDEBAR): KHUSUS TAMPILAN DESKTOP & LAPTOP -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="col-12 col-lg-4 col-xl-3 desktop-sidebar-only" id="petunjukGuruSection">

                <!-- 1. Kartu Profil Guru Pengampu -->
                <div class="companion-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="teacher-avatar-circle">
                            <?= strtoupper(substr($materi['nama_guru'] ?? 'G', 0, 1)) ?>
                        </div>
                        <div class="overflow-hidden">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:0.68rem; letter-spacing:0.5px;">Guru Pengampu</small>
                            <h6 class="fw-bold text-dark mb-0 text-truncate" title="<?= htmlspecialchars($materi['nama_guru']) ?>" style="font-size:0.95rem;">
                                <?= htmlspecialchars($materi['nama_guru'] ?? 'Guru Pengampu') ?>
                            </h6>
                            <?php if (!empty($materi['nip'])): ?>
                                <small class="text-muted d-block text-truncate" style="font-size:0.73rem;">NIP: <?= htmlspecialchars($materi['nip']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="border-top pt-2.5">
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Mata Pelajaran:</span>
                            <span class="fw-bold text-dark text-end text-truncate ms-2" style="max-width: 170px;" title="<?= htmlspecialchars($materi['nama_mapel']) ?>"><?= htmlspecialchars($materi['nama_mapel']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Bab Pembahasan:</span>
                            <span class="fw-semibold text-primary text-end text-truncate ms-2" style="max-width: 170px;"><?= htmlspecialchars($babTitle) ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Diterbitkan:</span>
                            <span class="fw-semibold text-dark text-end"><?= date('d M Y', strtotime($materi['created_at'])) ?></span>
                        </div>
                    </div>

                    <div class="mt-3 pt-2 border-top">
                        <a href="<?= BASE_URL ?>index.php?url=siswa/materiMapel&mapel_id=<?= (int)$materi['mapel_id'] ?>" class="btn btn-sm btn-outline-primary w-100 rounded-pill fw-bold">
                            <i class="bi bi-book me-1"></i> Buka E-Book Lengkap
                        </a>
                    </div>
                </div>

                <!-- 2. Kartu Materi Lain pada Mapel Ini (Navigasi Cepat Topik) -->
                <?php if (!empty($materiTerkait)): ?>
                    <div class="companion-card p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2.5">
                            <h6 class="fw-bold text-dark mb-0" style="font-size:0.88rem;">
                                <i class="bi bi-collection-play-fill text-primary me-1.5"></i>Materi Lain Mapel Ini
                            </h6>
                            <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size:0.68rem;"><?= count($materiTerkait) ?> Modul</span>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($materiTerkait as $mt): ?>
                                <a href="<?= BASE_URL ?>index.php?url=siswa/bacaMateri&id=<?= $mt['id'] ?>" class="playlist-materi-item shadow-xs">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-primary-subtle text-primary text-uppercase" style="font-size:0.62rem;">
                                            <?= htmlspecialchars($mt['jenis_file'] ?: 'MODUL') ?>
                                        </span>
                                        <small class="text-muted" style="font-size:0.68rem;"><?= date('d/m/Y', strtotime($mt['created_at'])) ?></small>
                                    </div>
                                    <div class="fw-bold text-dark text-truncate small" style="font-size:0.82rem;" title="<?= htmlspecialchars($mt['judul']) ?>">
                                        <?= htmlspecialchars($mt['judul']) ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 3. Tips Belajar Siswa -->
                <div class="alert alert-primary border-0 rounded-4 p-3 shadow-xs" style="background:#eff6ff; color:#1e40af;">
                    <div class="d-flex gap-2.5 align-items-start">
                        <i class="bi bi-lightbulb-fill text-warning fs-5 mt-0.5"></i>
                        <div class="small" style="font-size:0.78rem; line-height:1.5;">
                            <b>Tips Belajar Efektif:</b><br>
                            Baca materi singkat dan pelajari modul hingga tuntas, lalu kerjakan evaluasi kuis dan tugas di bagian bawah untuk mendapatkan nilai terbaik.
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</main>

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- ⚙️ SCRIPT LOGIKA PEMIRSA (SWITCH ENGINE & FULLSCREEN) -->
<!-- ════════════════════════════════════════════════════════════════ -->
<script>
const nativePdfUrl = '<?= $fileUrl ?>#toolbar=1&navpanes=0';
const googleCloudPdfUrl = 'https://docs.google.com/gview?embedded=true&url=<?= urlencode($fileUrl ?? '') ?>';

// Pengganti Engine Pemirsa Dokumen (Google Cloud Viewer vs Standar Native)
function setViewerEngine(engine) {
    const iframe = document.getElementById('readerIframe');
    const btnNative = document.getElementById('btnEngineNative');
    const btnGoogle = document.getElementById('btnEngineGoogle');
    const overlay = document.getElementById('readerLoadingOverlay');

    if (!iframe) return;
    if (overlay) overlay.style.display = 'block';

    if (engine === 'native') {
        iframe.src = nativePdfUrl;
        if (btnNative) {
            btnNative.className = 'btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-bold';
        }
        if (btnGoogle) {
            btnGoogle.className = 'btn btn-sm btn-white rounded-pill px-2.5 py-0.5 fw-semibold text-muted';
        }
    } else {
        iframe.src = googleCloudPdfUrl;
        if (btnGoogle) {
            btnGoogle.className = 'btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-bold';
        }
        if (btnNative) {
            btnNative.className = 'btn btn-sm btn-white rounded-pill px-2.5 py-0.5 fw-semibold text-muted';
        }
    }
}

// Buka / Keluar Layar Penuh (Fullscreen API)
function toggleFullscreenStage() {
    const stage = document.getElementById('readerStageCard');
    const icon = document.getElementById('iconFullscreen');

    if (!document.fullscreenElement) {
        if (stage.requestFullscreen) {
            stage.requestFullscreen();
        } else if (stage.webkitRequestFullscreen) {
            stage.webkitRequestFullscreen();
        }
        if (icon) icon.className = 'bi bi-fullscreen-exit text-primary';
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
        if (icon) icon.className = 'bi bi-fullscreen';
    }
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
