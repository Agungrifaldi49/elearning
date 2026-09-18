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
?>

<style>
/* ══════════════════════════════════════════════════════════════════
   🎨 MODERN E-READER DESIGN SYSTEM (RESPONSIVE & MOBILE-FIRST)
   ══════════════════════════════════════════════════════════════════ */
.reader-page-wrapper {
    max-width: 1600px;
    margin: 0 auto;
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
    padding: 10px 12px;
    margin-bottom: 12px;
}
[data-bs-theme="dark"] .reader-header-mobile {
    background: #1e293b;
    border-color: #334155;
}

/* 3. Panggung Pembaca Utama (Reader Stage) */
.reader-stage-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 175px);
    min-height: 740px;
    transition: all 0.25s ease;
}
[data-bs-theme="dark"] .reader-stage-card {
    background: #1e293b;
    border-color: #334155;
}

/* Toolbar Atas Panggung */
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

/* Frame Pemirsa Dokumen */
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

/* 4. Companion Sidebar (Desktop) */
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
    padding: 9px 12px;
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
    /* Sembunyikan header desktop yang panjang, tampilkan app bar HP yang rapi */
    .reader-header-desktop {
        display: none !important;
    }
    .reader-header-mobile {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
    }

    /* Panggung pembaca menyesuaikan ketinggian layar ponsel */
    .reader-stage-card {
        border-radius: 14px;
        height: 70vh;
        min-height: 480px;
        margin-bottom: 14px;
    }

    .reader-stage-toolbar {
        padding: 8px 10px;
    }

    /* Tabs Companion di HP */
    .mobile-companion-tabs .nav-link {
        font-size: 0.8rem;
        padding: 6px 12px;
        border-radius: 50rem;
        font-weight: 600;
        color: #64748b;
    }
    .mobile-companion-tabs .nav-link.active {
        background: #2563eb;
        color: #ffffff;
    }

    /* Sidebar Desktop disembunyikan di HP diganti tab rapi di bawah */
    .desktop-sidebar-only {
        display: none !important;
    }
    .mobile-companion-container {
        display: block !important;
    }
}

@media (min-width: 992px) {
    .mobile-companion-container {
        display: none !important;
    }
}
</style>

<main class="main-content px-2 px-md-3 px-lg-4 py-2 py-md-3">
    <div class="container-fluid reader-page-wrapper p-0">

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 🖥️ 1A. HEADER TAMPILAN DESKTOP & LAPTOP (LEBAR & ELEGAN) -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div class="reader-header-desktop mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2.5">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>index.php?url=siswa/materi" class="btn btn-sm btn-light rounded-pill border fw-bold text-dark px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5 hover-scale">
                    <i class="bi bi-arrow-left"></i>
                    <span>Daftar Materi</span>
                </a>

                <div class="vr bg-secondary opacity-25" style="height: 20px;"></div>

                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold" style="font-size:0.8rem;">
                    <i class="bi bi-journal-bookmark-fill me-1"></i><?= htmlspecialchars($materi['nama_mapel']) ?>
                </span>

                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size:0.75rem;">
                    <i class="bi bi-mortarboard-fill me-1"></i><?= htmlspecialchars($materi['nama_kelas'] ?? 'Rombel Anda') ?>
                </span>

                <?php if ($fileSizeFormatted): ?>
                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size:0.75rem;">
                        <i class="bi bi-hdd me-1"></i><?= $fileSizeFormatted ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if ($fileUrl): ?>
                    <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-warning text-dark rounded-pill fw-bold px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5" title="Buka di Tab Baru">
                        <i class="bi bi-box-arrow-up-right"></i>
                        <span>Buka Tab Baru</span>
                    </a>
                    <a href="<?= $fileUrl ?>" download class="btn btn-sm btn-outline-primary rounded-pill fw-bold px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5" title="Unduh Berkas">
                        <i class="bi bi-download"></i>
                        <span>Unduh File</span>
                    </a>
                <?php endif; ?>

                <?php if ($isYouTube && !empty($materi['youtube_url'])): ?>
                    <a href="<?= htmlspecialchars($materi['youtube_url']) ?>" target="_blank" class="btn btn-sm btn-danger rounded-pill fw-bold px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-youtube"></i>
                        <span>YouTube</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 📱 1B. HEADER TAMPILAN SMARTPHONE (HP: COMPACT, RAPI, TANPA TUMPANG-TINDIH) -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div class="reader-header-mobile">
            <!-- Tombol Kembali Lingkaran -->
            <a href="<?= BASE_URL ?>index.php?url=siswa/materi" class="btn btn-sm btn-light border rounded-circle d-flex align-items-center justify-content-center shadow-xs text-dark" style="width: 36px; height: 36px; flex-shrink: 0;" title="Kembali">
                <i class="bi bi-arrow-left fs-6"></i>
            </a>

            <!-- Judul & Mapel Tengah -->
            <div class="px-2 text-truncate text-start flex-grow-1" style="min-width: 0;">
                <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem; letter-spacing: -0.2px;">
                    <?= htmlspecialchars($materi['judul']) ?>
                </div>
                <div class="text-primary small text-truncate fw-semibold" style="font-size: 0.72rem;">
                    <i class="bi bi-journal-bookmark-fill me-1"></i><?= htmlspecialchars($materi['nama_mapel']) ?>
                </div>
            </div>

            <!-- Aksi Kanan Cepat di HP -->
            <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                <?php if ($fileUrl): ?>
                    <!-- Tombol Utama: Buka di Aplikasi HP (Google Drive / Adobe) -->
                    <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-warning text-dark rounded-pill fw-bold px-2.5 py-1 d-inline-flex align-items-center gap-1 shadow-xs" style="font-size: 0.75rem;" title="Buka di Aplikasi HP">
                        <i class="bi bi-phone-fill"></i>
                        <span>Buka</span>
                    </a>

                    <!-- Tombol Unduh Cepat -->
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
        <!-- 📐 2. GRID UTAMA (PANGGUNG BACA LEBAR + SIDEBAR) -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div class="row g-3 g-xl-4">

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- 🌟 KOLOM UTAMA (KIRI): PANGGUNG DOKUMEN / MEDIA (STAGE) -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="col-12 col-lg-8 col-xl-9">
                <div class="reader-stage-card" id="readerStageCard">

                    <!-- Toolbar Panggung Baca -->
                    <div class="reader-stage-toolbar">
                        <!-- Judul & Badge Tipe Berkas -->
                        <div class="d-flex align-items-center gap-1.5 overflow-hidden me-auto" style="min-width: 0;">
                            <span class="badge bg-primary text-uppercase px-2 py-1 rounded-pill" style="font-size:0.68rem;">
                                <?= htmlspecialchars($materi['jenis_file'] ?: ($isPdf ? 'PDF' : 'MODUL')) ?>
                            </span>
                            <span class="fw-bold text-dark text-truncate small d-none d-sm-inline" title="<?= htmlspecialchars($materi['judul']) ?>">
                                <?= htmlspecialchars($materi['judul']) ?>
                            </span>
                            <?php if ($fileSizeFormatted): ?>
                                <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5 d-none d-md-inline" style="font-size:0.68rem;">
                                    <?= $fileSizeFormatted ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Kontrol Pemirsa untuk Dokumen PDF -->
                        <?php if ($isPdf && $fileUrl): ?>
                            <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                                <!-- Engine Switcher Ringkas (Google Cloud vs Standar) -->
                                <div class="btn-group btn-group-sm rounded-pill border p-0.5 bg-white shadow-xs" role="group">
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-bold" id="btnEngineGoogle" onclick="setViewerEngine('google')" style="font-size:0.75rem;" title="Mode Baca Web Ringan (Bebas Download Otomatis)">
                                        <i class="bi bi-cloud-check-fill me-1"></i><span>Web View</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-white rounded-pill px-2.5 py-0.5 fw-semibold text-muted" id="btnEngineNative" onclick="setViewerEngine('native')" style="font-size:0.75rem;" title="Beralih ke Engine Asli">
                                        <i class="bi bi-file-earmark-pdf me-1"></i><span class="d-none d-sm-inline">Asli</span>
                                    </button>
                                </div>

                                <!-- Buka Tab Baru -->
                                <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-white border rounded-pill px-2.5 py-1 shadow-xs text-dark d-none d-sm-inline-flex align-items-center" title="Buka Tab Penuh">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>

                                <!-- Tombol Layar Penuh -->
                                <button type="button" class="btn btn-sm btn-white border rounded-pill px-2.5 py-1 shadow-xs text-dark" id="btnToggleFullscreen" onclick="toggleFullscreenStage()" title="Mode Layar Penuh">
                                    <i class="bi bi-fullscreen" id="iconFullscreen"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ═══════════════════════════════════════════════════ -->
                    <!-- 📄 AREA KONTEN MODUL PEMBELAJARAN -->
                    <!-- ═══════════════════════════════════════════════════ -->
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
                                        <i class="bi bi-zoom-in me-1"></i> Lihat Gambar Ukuran Asli
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
                            <!-- 6. MATERI BERUPA TEKS / TANPA BERKAS FISIK -->
                            <div class="p-4 p-md-5 text-center text-muted my-auto">
                                <i class="bi bi-journal-text fs-1 d-block mb-2 text-primary opacity-75"></i>
                                <h6 class="fw-bold text-dark">Informasi Pembelajaran</h6>
                                <p class="small text-muted mb-0">Materi ini disajikan berbasis instruksi dan penugasan guru.</p>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>

                <!-- ═══════════════════════════════════════════════════════════ -->
                <!-- 📱 TABBED COMPANION (KHUSUS SMARTPHONE / HP: RAPI & TERSTRUKTUR) -->
                <!-- ═══════════════════════════════════════════════════════════ -->
                <div class="mobile-companion-container">
                    <div class="card border-0 rounded-4 shadow-xs p-3 bg-white mb-3" style="border: 1px solid #e2e8f0 !important;">
                        <!-- Navigasi Tab Segmented HP -->
                        <ul class="nav nav-pills mobile-companion-tabs gap-1.5 p-1 bg-light rounded-pill mb-3" id="pills-tab-mobile" role="tablist">
                            <li class="nav-item flex-fill text-center" role="presentation">
                                <button class="nav-link w-100 active" id="tab-petunjuk-mobile" data-bs-toggle="pill" data-bs-target="#content-petunjuk-mobile" type="button" role="tab">
                                    <i class="bi bi-chat-quote-fill me-1"></i>Petunjuk Guru
                                </button>
                            </li>
                            <li class="nav-item flex-fill text-center" role="presentation">
                                <button class="nav-link w-100" id="tab-modullain-mobile" data-bs-toggle="pill" data-bs-target="#content-modullain-mobile" type="button" role="tab">
                                    <i class="bi bi-collection-play-fill me-1"></i>Modul Lain (<?= count($materiTerkait) ?>)
                                </button>
                            </li>
                            <li class="nav-item flex-fill text-center" role="presentation">
                                <button class="nav-link w-100" id="tab-guru-mobile" data-bs-toggle="pill" data-bs-target="#content-guru-mobile" type="button" role="tab">
                                    <i class="bi bi-person-circle me-1"></i>Pengampu
                                </button>
                            </li>
                        </ul>

                        <!-- Isi Tab Mobile -->
                        <div class="tab-content" id="pills-tabContent-mobile">
                            <!-- Tab 1: Petunjuk Guru -->
                            <div class="tab-pane fade show active" id="content-petunjuk-mobile" role="tabpanel">
                                <?php if (!empty($materi['deskripsi'])): ?>
                                    <div class="p-3 bg-light rounded-3 border-start border-4 border-primary">
                                        <div class="small fw-bold text-primary mb-1"><i class="bi bi-info-circle-fill me-1"></i>Catatan KBM:</div>
                                        <div class="small text-dark lh-base"><?= nl2br(htmlspecialchars($materi['deskripsi'])) ?></div>
                                    </div>
                                <?php else: ?>
                                    <div class="small text-muted py-2 text-center">Tidak ada catatan instruksi khusus dari guru pengampu.</div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab 2: Modul Lain -->
                            <div class="tab-pane fade" id="content-modullain-mobile" role="tabpanel">
                                <?php if (!empty($materiTerkait)): ?>
                                    <div class="d-flex flex-column gap-2">
                                        <?php foreach ($materiTerkait as $mt): ?>
                                            <a href="<?= BASE_URL ?>index.php?url=siswa/bacaMateri&id=<?= $mt['id'] ?>" class="playlist-materi-item">
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
                                <?php else: ?>
                                    <div class="small text-muted py-2 text-center">Belum ada materi lain pada mata pelajaran ini.</div>
                                <?php endif; ?>
                            </div>

                            <!-- Tab 3: Profil Guru Pengampu -->
                            <div class="tab-pane fade" id="content-guru-mobile" role="tabpanel">
                                <div class="d-flex align-items-center gap-2.5 p-2 bg-light rounded-3">
                                    <div class="teacher-avatar-circle" style="width:38px; height:38px; font-size:1rem;">
                                        <?= strtoupper(substr($materi['nama_guru'] ?? 'G', 0, 1)) ?>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-dark text-truncate small"><?= htmlspecialchars($materi['nama_guru'] ?? 'Guru Pengampu') ?></div>
                                        <?php if (!empty($materi['nip'])): ?>
                                            <small class="text-muted d-block" style="font-size:0.7rem;">NIP: <?= htmlspecialchars($materi['nip']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tips Membaca di HP -->
                    <div class="alert alert-primary border-0 rounded-4 p-2.5 mb-2 shadow-xs" style="background:#eff6ff; color:#1e40af;">
                        <div class="d-flex gap-2 align-items-start">
                            <i class="bi bi-phone-vibrate-fill text-primary mt-0.5" style="font-size: 1.1rem;"></i>
                            <div class="small" style="font-size:0.75rem; line-height:1.4;">
                                <b>Tips Membaca di Ponsel:</b><br>
                                Tekan tombol <b>Buka</b> di pojok kanan atas untuk membaca langsung di aplikasi PDF bawaan ponsel Anda (Google Drive / Adobe) dengan gestur cubit-layar (*pinch to zoom*) yang sangat mulus.
                            </div>
                        </div>
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
                            <span class="fw-bold text-dark text-end text-truncate ms-2" style="max-width: 180px;" title="<?= htmlspecialchars($materi['nama_mapel']) ?>"><?= htmlspecialchars($materi['nama_mapel']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Diterbitkan:</span>
                            <span class="fw-semibold text-dark text-end"><?= date('d M Y, H:i', strtotime($materi['created_at'])) ?></span>
                        </div>
                    </div>
                </div>

                <!-- 2. Kartu Petunjuk & Instruksi Belajar dari Guru -->
                <?php if (!empty($materi['deskripsi'])): ?>
                    <div class="companion-card border-start border-4 border-primary">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="bg-primary-subtle text-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width:24px; height:24px;">
                                <i class="bi bi-chat-quote-fill" style="font-size:0.75rem;"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-0" style="font-size:0.9rem;">Petunjuk Pembelajaran</h6>
                        </div>
                        <div class="text-slate-700 small lh-base" style="color: #334155; font-size:0.86rem;">
                            <?= nl2br(htmlspecialchars($materi['deskripsi'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 3. Kartu Materi Lain pada Mapel Ini (Navigasi Cepat Topik) -->
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
