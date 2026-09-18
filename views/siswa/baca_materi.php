<?php
/**
 * Halaman Khusus Pembaca Materi & Modul Pembelajaran Siswa (Modern Studio E-Reader)
 * Desain Responsif, Profesional, High-DPI Canvas, dan Dioptimalkan Sempurna untuk Smartphone (HP) & Desktop.
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
?>

<!-- Include PDF.js Library (Mozilla Open Source) for High-Quality Mobile PDF Rendering -->
<?php if ($isPdf && $fileUrl): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<?php endif; ?>

<style>
/* ══════════════════════════════════════════════════════════════════
   🎨 MODERN E-LEARNING STUDIO READER STYLING (RESPONSIVE & MOBILE FIRST)
   ══════════════════════════════════════════════════════════════════ */
:root {
    --reader-bg-dark: #0f172a;
    --reader-bg-sepia: #fbf7ee;
    --reader-bg-light: #f1f5f9;
}

.reader-page-wrapper {
    max-width: 1440px;
    margin: 0 auto;
}

/* 1. Header Breadcrumb & Quick Actions Bar */
.reader-header-bar {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    padding: 12px 18px;
}

[data-bs-theme="dark"] .reader-header-bar {
    background: #1e293b;
    border-color: #334155;
}

/* 2. Reader Stage (The Main Stage) */
.reader-stage-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    position: relative;
    transition: all 0.3s ease;
}

[data-bs-theme="dark"] .reader-stage-card {
    background: #1e293b;
    border-color: #334155;
}

/* Reader Toolbar Top */
.reader-toolbar-top {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 10px 16px;
}

[data-bs-theme="dark"] .reader-toolbar-top {
    background: #0f172a;
    border-color: #334155;
}

/* Viewport Area */
.pdf-viewport-stage {
    background-color: var(--reader-bg-dark);
    padding: 16px;
    min-height: 480px;
    max-height: 82vh;
    overflow-y: auto;
    overflow-x: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: background-color 0.25s ease;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.2) transparent;
}

/* Theme Variations for Viewport */
.pdf-viewport-stage.theme-sepia {
    background-color: var(--reader-bg-sepia) !important;
}
.pdf-viewport-stage.theme-light {
    background-color: var(--reader-bg-light) !important;
}

/* Canvas Styling - High Quality & Sharp Rendering */
.pdf-canvas-item {
    display: block;
    margin: 0 auto 16px auto;
    background-color: #ffffff;
    box-shadow: 0 8px 30px rgba(0,0,0,0.35);
    border-radius: 6px;
    max-width: 100% !important;
    height: auto !important;
    user-select: none;
}

/* Floating Bottom Thumb Navigation Bar (Mobile & Desktop Friendly) */
.reader-floating-dock {
    position: sticky;
    bottom: 14px;
    z-index: 100;
    margin-top: -54px;
    display: flex;
    justify-content: center;
    pointer-events: none;
}

.reader-dock-pill {
    pointer-events: auto;
    background: rgba(15, 23, 42, 0.92);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #ffffff;
    border-radius: 50rem;
    padding: 6px 14px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
}

.reader-dock-btn {
    background: transparent;
    border: none;
    color: #f8fafc;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.18s ease;
}
.reader-dock-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    transform: scale(1.08);
}
.reader-dock-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

/* Sidebar Info Cards */
.companion-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    padding: 20px;
    margin-bottom: 20px;
}

[data-bs-theme="dark"] .companion-card {
    background: #1e293b;
    border-color: #334155;
}

.teacher-avatar-badge {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}

/* Video Responsive Aspect Ratio */
.video-stage-wrapper {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 */
    height: 0;
    overflow: hidden;
    background: #000;
}
.video-stage-wrapper iframe,
.video-stage-wrapper video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

/* Mobile Adjustments */
@media (max-width: 991.98px) {
    .reader-header-bar {
        padding: 10px 14px;
        border-radius: 12px;
    }
    .reader-stage-card {
        border-radius: 16px;
    }
    .pdf-viewport-stage {
        padding: 8px 4px;
        max-height: 76vh;
        min-height: 380px;
    }
    .reader-dock-pill {
        padding: 5px 10px;
        gap: 5px;
        font-size: 0.78rem;
    }
    .reader-dock-btn {
        width: 28px;
        height: 28px;
        font-size: 0.82rem;
    }
    .reader-hide-mobile {
        display: none !important;
    }
    .btn-mobile-icon-only span {
        display: none;
    }
    .btn-mobile-icon-only {
        padding: 6px 10px !important;
    }
}
</style>

<main class="main-content px-2 px-md-3 px-lg-4 py-2 py-md-3">
    <div class="container-fluid reader-page-wrapper p-0">

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 🧭 1. MODERN APP BAR (NAVIGASI & AKSI CEPAT) -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div class="reader-header-bar mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>index.php?url=siswa/materi" class="btn btn-sm btn-light rounded-pill border fw-bold text-dark px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5 hover-scale" title="Kembali ke Daftar Materi">
                    <i class="bi bi-arrow-left"></i>
                    <span class="d-none d-sm-inline">Daftar Materi</span>
                </a>
                
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold" style="font-size:0.8rem;">
                    <i class="bi bi-journal-bookmark-fill me-1"></i><?= htmlspecialchars($materi['nama_mapel']) ?>
                </span>

                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1.5 fw-semibold d-none d-md-inline-block" style="font-size:0.75rem;">
                    <i class="bi bi-mortarboard me-1"></i><?= htmlspecialchars($materi['nama_kelas'] ?? 'Rombel Anda') ?>
                </span>
            </div>

            <!-- Action Toolbar Kanan -->
            <div class="d-flex align-items-center gap-1.5">
                <?php if ($fileUrl): ?>
                    <!-- Buka di Aplikasi HP Langsung (Native App Viewer) -->
                    <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-warning text-dark rounded-pill fw-bold px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5 btn-mobile-icon-only" title="Buka dengan Aplikasi Pembaca PDF / Dokumen di HP">
                        <i class="bi bi-phone-fill"></i>
                        <span>Buka di HP</span>
                    </a>

                    <!-- Unduh Modul untuk Belajar Offline -->
                    <a href="<?= $fileUrl ?>" download class="btn btn-sm btn-outline-primary rounded-pill fw-bold px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5 btn-mobile-icon-only" title="Unduh Modul Belajar ke Memori Perangkat">
                        <i class="bi bi-download"></i>
                        <span>Unduh</span>
                    </a>
                <?php endif; ?>

                <?php if ($isYouTube && !empty($materi['youtube_url'])): ?>
                    <a href="<?= htmlspecialchars($materi['youtube_url']) ?>" target="_blank" class="btn btn-sm btn-danger rounded-pill fw-bold px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5 btn-mobile-icon-only" title="Tonton di Aplikasi YouTube">
                        <i class="bi bi-youtube"></i>
                        <span>YouTube</span>
                    </a>
                <?php endif; ?>

                <!-- Tombol Info Petunjuk Guru di HP (Scroll Halus ke Bawah) -->
                <a href="#petunjukGuruMobile" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1.5 d-lg-none" title="Lihat Petunjuk Guru">
                    <i class="bi bi-info-circle-fill"></i>
                </a>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 📐 2. GRID 2-COLUMN STUDIO (RESPONSIF DESKTOP & MOBILE) -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div class="row g-3 g-lg-4">

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- 🌟 KOLOM UTAMA (KIRI): PANGGUNG PEMBACA MODUL (STAGE) -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="col-12 col-lg-8 col-xl-8 col-xxl-9">
                <div class="reader-stage-card" id="readerStageCard">

                    <!-- Header Panggung Modul -->
                    <div class="reader-toolbar-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                            <span class="badge bg-primary text-uppercase px-2.5 py-1 rounded-pill" style="font-size:0.7rem;">
                                <?= htmlspecialchars($materi['jenis_file'] ?: ($isPdf ? 'PDF' : 'MODUL')) ?>
                            </span>
                            <h6 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 320px;" title="<?= htmlspecialchars($materi['judul']) ?>">
                                <?= htmlspecialchars($materi['judul']) ?>
                            </h6>
                        </div>

                        <!-- Kontrol Panggung Khusus PDF -->
                        <?php if ($isPdf && $fileUrl): ?>
                            <div class="d-flex align-items-center gap-1.5">
                                <!-- Mode Tampilan (Per Halaman vs Gulir Terus) -->
                                <div class="btn-group btn-group-sm rounded-pill border p-0.5 bg-white shadow-xs" role="group">
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-bold" id="btnModeSingle" title="Mode Baca Per Halaman (Ringan & Cepat)">
                                        <i class="bi bi-file-earmark me-1"></i><span class="reader-hide-mobile">Per Hal</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-white rounded-pill px-2.5 py-0.5 fw-semibold text-muted" id="btnModeScroll" title="Mode Gulir Berkelanjutan">
                                        <i class="bi bi-view-stacked me-1"></i><span class="reader-hide-mobile">Gulir</span>
                                    </button>
                                </div>

                                <!-- Pengganti Tema Latar Belakang (Dark, Light, Sepia) -->
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-white border rounded-pill px-2 py-1 shadow-xs" type="button" data-bs-toggle="dropdown" title="Ganti Warna Latar Belakang">
                                        <i class="bi bi-circle-half text-primary"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-4 py-1" style="font-size:0.82rem;">
                                        <li><a class="dropdown-item py-1.5 active" href="javascript:void(0)" onclick="setReaderTheme('dark', this)"><i class="bi bi-moon-fill text-indigo me-2"></i>Dark Studio (Fokus)</a></li>
                                        <li><a class="dropdown-item py-1.5" href="javascript:void(0)" onclick="setReaderTheme('sepia', this)"><i class="bi bi-sun-fill text-warning me-2"></i>Warm Sepia (Nyaman)</a></li>
                                        <li><a class="dropdown-item py-1.5" href="javascript:void(0)" onclick="setReaderTheme('light', this)"><i class="bi bi-brightness-high-fill text-secondary me-2"></i>Clean Light (Terang)</a></li>
                                    </ul>
                                </div>

                                <!-- Layar Penuh (Fullscreen Stage) -->
                                <button type="button" class="btn btn-sm btn-white border rounded-pill px-2 py-1 shadow-xs" id="btnFullscreenStage" title="Mode Layar Penuh">
                                    <i class="bi bi-fullscreen text-dark"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ═══════════════════════════════════════════════════ -->
                    <!-- 📄 KONTEN SESUAI TIPE MATERI -->
                    <!-- ═══════════════════════════════════════════════════ -->
                    <?php if ($isPdf && $fileUrl): ?>
                        <!-- 1. PDF VIEWER ENGINE (HIGH-DPI CANVAS) -->
                        <div class="pdf-viewport-stage" id="pdfViewportStage">
                            <!-- Spinner Animasi Memuat Dokumen -->
                            <div id="pdfLoadingSpinner" class="text-center my-auto py-5 text-white">
                                <div class="spinner-border text-primary mb-3" role="status" style="width: 2.75rem; height: 2.75rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <h6 class="fw-bold text-white mb-1">Menyiapkan Lembar Modul...</h6>
                                <p class="text-white text-opacity-75 small mb-0">Merender teks vektor berkualitas tinggi khusus layar Anda.</p>
                            </div>

                            <!-- Kontainer Tempat Canvas Dirender -->
                            <div id="pdfCanvasContainer" class="w-100 text-center" style="display:none;"></div>
                        </div>

                        <!-- 🕹️ FLOATING BOTTOM NAVIGATION DOCK (THUMB-FRIENDLY DI HP) -->
                        <div class="reader-floating-dock" id="readerFloatingDock" style="display:none;">
                            <div class="reader-dock-pill">
                                <!-- Tombol Prev -->
                                <button type="button" class="reader-dock-btn" id="dockBtnPrev" title="Halaman Sebelumnya">
                                    <i class="bi bi-chevron-left"></i>
                                </button>

                                <!-- Info Halaman Aktif -->
                                <span class="fw-bold px-1" id="dockPageIndicator" style="letter-spacing:0.3px;">1 / 1</span>

                                <!-- Tombol Next -->
                                <button type="button" class="reader-dock-btn" id="dockBtnNext" title="Halaman Selanjutnya">
                                    <i class="bi bi-chevron-right"></i>
                                </button>

                                <div class="vr bg-white opacity-25 mx-1" style="height:18px;"></div>

                                <!-- Zoom Out (-) -->
                                <button type="button" class="reader-dock-btn" id="dockBtnZoomOut" title="Perkecil">
                                    <i class="bi bi-dash"></i>
                                </button>

                                <!-- Fit Width (Pas Layar) -->
                                <button type="button" class="reader-dock-btn" id="dockBtnFit" title="Pas Lebar Layar Ponsel" style="width:auto; border-radius:12px; padding:0 8px; font-size:0.75rem; font-weight:700;">
                                    FIT
                                </button>

                                <!-- Zoom In (+) -->
                                <button type="button" class="reader-dock-btn" id="dockBtnZoomIn" title="Perbesar">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>

                    <?php elseif ($isYouTube && !empty($embedUrl)): ?>
                        <!-- 2. PEMUTAR VIDEO YOUTUBE RESPONSIVE -->
                        <div class="video-stage-wrapper">
                            <iframe src="<?= htmlspecialchars($embedUrl) ?>?rel=0&autoplay=0&enablejsapi=1" title="<?= htmlspecialchars($materi['judul']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                        <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i>Streaming video resmi disajikan via YouTube Studio.</small>
                            <a href="<?= htmlspecialchars($materi['youtube_url']) ?>" target="_blank" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold">
                                <i class="bi bi-youtube me-1"></i> Buka di Aplikasi YouTube
                            </a>
                        </div>

                    <?php elseif ($isVideoMp4 && $fileUrl): ?>
                        <!-- 3. PEMUTAR VIDEO MP4 LOKAL -->
                        <div class="video-stage-wrapper">
                            <video src="<?= $fileUrl ?>" controls class="w-100 h-100" style="object-fit: contain;">
                                Browser Anda tidak mendukung pemutar video HTML5 bawaan.
                            </video>
                        </div>
                        <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <small class="text-muted"><i class="bi bi-film text-primary me-1"></i>Video pembelajaran MP4 interaktif.</small>
                            <a href="<?= $fileUrl ?>" download class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                                <i class="bi bi-download me-1"></i> Unduh File Video
                            </a>
                        </div>

                    <?php elseif ($isImage && $fileUrl): ?>
                        <!-- 4. PENAMPIL GAMBAR / INFOGRAFIS -->
                        <div class="p-3 p-md-4 text-center bg-dark" style="min-height: 450px; display:flex; align-items:center; justify-content:center;">
                            <img src="<?= $fileUrl ?>" alt="<?= htmlspecialchars($materi['judul']) ?>" class="img-fluid rounded-3 shadow-lg" style="max-height: 75vh; object-fit: contain;">
                        </div>
                        <div class="p-3 bg-light border-top text-center">
                            <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                                <i class="bi bi-zoom-in me-1"></i> Buka Gambar Resolusi Penuh
                            </a>
                        </div>

                    <?php elseif ($isOfficeDoc && $fileUrl): ?>
                        <!-- 5. DOKUMEN OFFICE (WORD, PPT, EXCEL) -->
                        <div class="p-5 text-center bg-light">
                            <div class="bg-primary-subtle text-primary p-4 rounded-4 d-inline-flex align-items-center justify-content-center mb-3 shadow-xs" style="width: 80px; height: 80px;">
                                <i class="bi bi-file-earmark-word-fill fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($materi['judul']) ?></h5>
                            <p class="text-muted small mb-4" style="max-width: 480px; margin: 0 auto;">
                                Berkas Microsoft Office (<?= strtoupper($ext) ?>) dapat dibuka langsung di smartphone Anda menggunakan aplikasi Microsoft 365, Google Docs, atau WPS Office.
                            </p>
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                <a href="<?= $fileUrl ?>" download class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                                    <i class="bi bi-download me-1.5"></i> Unduh Berkas (<?= strtoupper($ext) ?>)
                                </a>
                                <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold">
                                    <i class="bi bi-box-arrow-up-right me-1.5"></i> Buka di HP
                                </a>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- 6. MATERI BERUPA TEKS / TANPA LAMPIRAN -->
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-journal-text fs-1 d-block mb-2 text-primary opacity-75"></i>
                            <h5 class="fw-bold text-dark">Informasi Pembelajaran</h5>
                            <p class="small text-muted mb-0">Materi ini disajikan berbasis instruksi dan penugasan guru tanpa berkas eksternal.</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- 📚 KOLOM KANAN (SIDEBAR): GURU, PETUNJUK & MATERI LAIN -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="col-12 col-lg-4 col-xl-4 col-xxl-3" id="petunjukGuruMobile">

                <!-- 1. Kartu Profil Guru Pengampu -->
                <div class="companion-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="teacher-avatar-badge">
                            <?= strtoupper(substr($materi['nama_guru'] ?? 'G', 0, 1)) ?>
                        </div>
                        <div class="overflow-hidden">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:0.7rem; letter-spacing:0.5px;">Guru Pengampu</small>
                            <h6 class="fw-bold text-dark mb-0 text-truncate" title="<?= htmlspecialchars($materi['nama_guru']) ?>">
                                <?= htmlspecialchars($materi['nama_guru'] ?? 'Guru Pengampu') ?>
                            </h6>
                            <?php if (!empty($materi['nip'])): ?>
                                <small class="text-muted" style="font-size:0.74rem;">NIP: <?= htmlspecialchars($materi['nip']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="border-top pt-2.5">
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Mata Pelajaran:</span>
                            <span class="fw-bold text-dark text-end"><?= htmlspecialchars($materi['nama_mapel']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Diterbitkan:</span>
                            <span class="fw-semibold text-dark text-end"><?= date('d M Y, H:i', strtotime($materi['created_at'])) ?></span>
                        </div>
                    </div>
                </div>

                <!-- 2. Kartu Petunjuk & Deskripsi Guru -->
                <?php if (!empty($materi['deskripsi'])): ?>
                    <div class="companion-card border-start border-4 border-primary">
                        <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-chat-quote-fill text-primary"></i>
                            <span>Petunjuk Pembelajaran</span>
                        </h6>
                        <div class="text-slate-700 small lh-base" style="color: #334155;">
                            <?= nl2br(htmlspecialchars($materi['deskripsi'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 3. Kartu Materi Lain pada Mapel Ini (Navigasi Cepat) -->
                <?php if (!empty($materiTerkait)): ?>
                    <div class="companion-card p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0" style="font-size:0.9rem;">
                                <i class="bi bi-collection-play-fill text-primary me-1.5"></i>Materi Lain Mapel Ini
                            </h6>
                            <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1" style="font-size:0.7rem;"><?= count($materiTerkait) ?> Modul</span>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($materiTerkait as $mt): ?>
                                <a href="<?= BASE_URL ?>index.php?url=siswa/bacaMateri&id=<?= $mt['id'] ?>" class="p-2.5 rounded-3 border bg-light text-decoration-none d-block transition-all hover-shadow" style="transition: all 0.2s;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-primary-subtle text-primary text-uppercase" style="font-size:0.65rem;">
                                            <?= htmlspecialchars($mt['jenis_file'] ?: 'MODUL') ?>
                                        </span>
                                        <small class="text-muted" style="font-size:0.7rem;"><?= date('d/m/Y', strtotime($mt['created_at'])) ?></small>
                                    </div>
                                    <div class="fw-bold text-dark text-truncate small" title="<?= htmlspecialchars($mt['judul']) ?>">
                                        <?= htmlspecialchars($mt['judul']) ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tips Pintar Baca di HP -->
                <div class="alert alert-info border-0 rounded-4 p-3 shadow-xs" style="background:#eff6ff; color:#1e40af;">
                    <div class="d-flex gap-2">
                        <i class="bi bi-lightbulb-fill text-primary fs-5 mt-0.5"></i>
                        <div class="small">
                            <b>Tips Belajar Nyaman di HP:</b><br>
                            Gunakan tombol <b>FIT</b> di bawah untuk menyesuaikan dokumen dengan lebar layar ponsel Anda. Putar layar ke posisi mendatar (*Landscape*) jika teks terasa kecil.
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</main>

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- ⚙️ SCRIPT ENGINE PDF.JS HIGH-DPI & INTERAKSI RESPONSIF -->
<!-- ════════════════════════════════════════════════════════════════ -->
<?php if ($isPdf && $fileUrl): ?>
<script>
// Fungsi Pengganti Tema Viewport (Dark, Sepia, Light)
function setReaderTheme(theme, element) {
    const stage = document.getElementById('pdfViewportStage');
    stage.classList.remove('theme-sepia', 'theme-light');
    if (theme === 'sepia') stage.classList.add('theme-sepia');
    if (theme === 'light') stage.classList.add('theme-light');

    if (element) {
        document.querySelectorAll('.dropdown-menu .dropdown-item').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof pdfjsLib === 'undefined') {
        const spinner = document.getElementById('pdfLoadingSpinner');
        if (spinner) {
            spinner.innerHTML = `
                <div class="alert alert-warning p-3 rounded-3 small text-dark">
                    Pustaka pembaca PDF tidak dapat dimuat secara online. Silakan klik tombol <b>Buka di HP</b> di bagian atas untuk membuka berkas secara langsung di aplikasi ponsel Anda.
                </div>
            `;
        }
        return;
    }

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const pdfUrl = '<?= $fileUrl ?>';
    const container = document.getElementById('pdfCanvasContainer');
    const spinner = document.getElementById('pdfLoadingSpinner');
    const stage = document.getElementById('pdfViewportStage');
    const dock = document.getElementById('readerFloatingDock');
    const pageIndicator = document.getElementById('dockPageIndicator');
    
    // Buttons
    const btnPrev = document.getElementById('dockBtnPrev');
    const btnNext = document.getElementById('dockBtnNext');
    const btnZoomIn = document.getElementById('dockBtnZoomIn');
    const btnZoomOut = document.getElementById('dockBtnZoomOut');
    const btnFit = document.getElementById('dockBtnFit');
    const btnModeSingle = document.getElementById('btnModeSingle');
    const btnModeScroll = document.getElementById('btnModeScroll');
    const btnFullscreen = document.getElementById('btnFullscreenStage');

    let pdfDoc = null;
    let currentPage = 1;
    let totalPages = 1;
    let renderMode = 'single'; // 'single' atau 'scroll'
    let currentScale = 1.0;
    let isFitWidth = true;
    let basePageWidth = 595;
    let renderTasks = {};

    // Hitung Skala Fit Width yang Presisi Tanpa Terpotong di HP
    function getFitWidthScale(pageWidth) {
        // Ambil lebar kontainer yang tersedia secara real-time
        const availableWidth = stage.clientWidth - (window.innerWidth < 768 ? 12 : 24);
        const w = pageWidth || basePageWidth;
        // Skala fleksibel dari layar kecil (260px) hingga layar lebar (2000px)
        return Math.max(0.35, Math.min(3.0, availableWidth / w));
    }

    // Muat Berkas PDF
    const loadingTask = pdfjsLib.getDocument(pdfUrl);
    loadingTask.promise.then(function(pdf) {
        pdfDoc = pdf;
        totalPages = pdf.numPages;
        
        // Sembunyikan spinner, tampilkan dock & container
        spinner.style.display = 'none';
        container.style.display = 'block';
        dock.style.display = 'flex';

        // Baca halaman pertama untuk acuan rasio
        pdfDoc.getPage(1).then(function(firstPage) {
            const vp = firstPage.getViewport({ scale: 1.0 });
            basePageWidth = vp.width;
            currentScale = getFitWidthScale(basePageWidth);

            updatePageIndicator();
            renderCurrentView();
        });
    }).catch(function(err) {
        console.error('Error membuka PDF:', err);
        spinner.innerHTML = `
            <div class="alert alert-danger p-3 rounded-4 small text-dark text-start">
                <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>
                <b>Gagal Menampilkan Pratinjau Dokumen.</b><br>
                Format dokumen tidak mendukung pratinjau browser langsung atau koneksi terputus.
                <div class="mt-2.5">
                    <a href="${pdfUrl}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                        <i class="bi bi-phone me-1"></i> Buka dengan Aplikasi HP
                    </a>
                </div>
            </div>
        `;
    });

    // Update Indikator Halaman & Status Tombol
    function updatePageIndicator() {
        if (pageIndicator) {
            pageIndicator.textContent = `${currentPage} / ${totalPages}`;
        }
        if (btnPrev) btnPrev.disabled = (currentPage <= 1);
        if (btnNext) btnNext.disabled = (currentPage >= totalPages);
    }

    // Render Tampilan Sesuai Mode (Single Page vs Scroll Continuous)
    function renderCurrentView() {
        if (!pdfDoc) return;

        if (renderMode === 'single') {
            renderSinglePageMode();
        } else {
            renderScrollMode();
        }
    }

    // MODE 1: SINGLE PAGE (Halaman Tunggal - Paling Cepat & Ringan di HP)
    function renderSinglePageMode() {
        container.innerHTML = '';
        renderPageToContainer(currentPage);
        updatePageIndicator();
    }

    // MODE 2: SCROLL MODE (Semua Halaman Berurutan)
    function renderScrollMode() {
        container.innerHTML = '';
        for (let p = 1; p <= totalPages; p++) {
            renderPageToContainer(p);
        }
        updatePageIndicator();
    }

    // Render Satu Halaman dengan High-DPI Canvas (Teks Tajam & Jernih di Retina/AMOLED)
    function renderPageToContainer(pageNum) {
        pdfDoc.getPage(pageNum).then(function(page) {
            const scale = isFitWidth ? getFitWidthScale(page.getViewport({ scale: 1.0 }).width) : currentScale;
            const viewport = page.getViewport({ scale: scale });

            // High-DPI Retina/OLED devicePixelRatio handling
            const dpr = Math.min(window.devicePixelRatio || 1, 2.5); // Batasi di 2.5 agar hemat RAM di ponsel

            let canvas = document.getElementById('pdf-canvas-' + pageNum);
            if (!canvas) {
                canvas = document.createElement('canvas');
                canvas.className = 'pdf-canvas-item';
                canvas.id = 'pdf-canvas-' + pageNum;
                container.appendChild(canvas);
            }

            // Atur ukuran bitmap (resolusi tajam)
            canvas.width = Math.floor(viewport.width * dpr);
            canvas.height = Math.floor(viewport.height * dpr);

            // Atur ukuran tampilan CSS (sesuai layar)
            canvas.style.width = Math.floor(viewport.width) + 'px';
            canvas.style.height = Math.floor(viewport.height) + 'px';

            const ctx = canvas.getContext('2d');
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

            // Batalkan render sebelumnya jika masih berjalan
            if (renderTasks[pageNum]) {
                renderTasks[pageNum].cancel();
            }

            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };

            const task = page.render(renderContext);
            renderTasks[pageNum] = task;

            task.promise.then(function() {
                delete renderTasks[pageNum];
            }).catch(function(err) {
                if (err && err.name !== 'RenderingCancelledException') {
                    console.error('Render error page ' + pageNum, err);
                }
            });
        });
    }

    // Navigasi Next & Prev
    if (btnNext) {
        btnNext.addEventListener('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                if (renderMode === 'single') {
                    renderSinglePageMode();
                    stage.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    scrollToPage(currentPage);
                }
            }
        });
    }

    if (btnPrev) {
        btnPrev.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                if (renderMode === 'single') {
                    renderSinglePageMode();
                    stage.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    scrollToPage(currentPage);
                }
            }
        });
    }

    function scrollToPage(num) {
        const target = document.getElementById('pdf-canvas-' + num);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            updatePageIndicator();
        }
    }

    // Zoom Controls
    if (btnZoomIn) {
        btnZoomIn.addEventListener('click', function() {
            isFitWidth = false;
            currentScale = Math.min(3.0, currentScale + 0.2);
            renderCurrentView();
        });
    }

    if (btnZoomOut) {
        btnZoomOut.addEventListener('click', function() {
            isFitWidth = false;
            currentScale = Math.max(0.4, currentScale - 0.2);
            renderCurrentView();
        });
    }

    if (btnFit) {
        btnFit.addEventListener('click', function() {
            isFitWidth = true;
            currentScale = getFitWidthScale();
            renderCurrentView();
        });
    }

    // Switch Mode Baca
    if (btnModeSingle) {
        btnModeSingle.addEventListener('click', function() {
            if (renderMode === 'single') return;
            renderMode = 'single';
            btnModeSingle.className = 'btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-bold';
            btnModeScroll.className = 'btn btn-sm btn-white rounded-pill px-2.5 py-0.5 fw-semibold text-muted';
            renderCurrentView();
        });
    }

    if (btnModeScroll) {
        btnModeScroll.addEventListener('click', function() {
            if (renderMode === 'scroll') return;
            renderMode = 'scroll';
            btnModeScroll.className = 'btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-bold';
            btnModeSingle.className = 'btn btn-sm btn-white rounded-pill px-2.5 py-0.5 fw-semibold text-muted';
            renderCurrentView();
        });
    }

    // Toggle Layar Penuh (Fullscreen API)
    if (btnFullscreen) {
        btnFullscreen.addEventListener('click', function() {
            const card = document.getElementById('readerStageCard');
            if (!document.fullscreenElement) {
                if (card.requestFullscreen) {
                    card.requestFullscreen();
                } else if (card.webkitRequestFullscreen) {
                    card.webkitRequestFullscreen();
                }
                btnFullscreen.innerHTML = '<i class="bi bi-fullscreen-exit text-primary"></i>';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
                btnFullscreen.innerHTML = '<i class="bi bi-fullscreen text-dark"></i>';
            }
        });
    }

    // Deteksi Pergantian Orientasi Layar Smartphone (Portrait <-> Landscape)
    window.addEventListener('resize', function() {
        clearTimeout(window.pdfResizeTimer);
        window.pdfResizeTimer = setTimeout(function() {
            if (pdfDoc && isFitWidth) {
                currentScale = getFitWidthScale();
                renderCurrentView();
            }
        }, 250);
    });

    // Dukungan Gestur Geser Jari di Layar Sentuh HP (Touch Swipe Left/Right di Mode Single Page)
    let touchStartX = 0;
    let touchEndX = 0;

    stage.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    stage.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipeGesture();
    }, { passive: true });

    function handleSwipeGesture() {
        if (renderMode !== 'single') return;
        const diff = touchEndX - touchStartX;
        if (diff > 50) {
            // Geser ke Kanan: Halaman Sebelumnya
            if (currentPage > 1) {
                currentPage--;
                renderSinglePageMode();
                stage.scrollTo({ top: 0, behavior: 'smooth' });
            }
        } else if (diff < -50) {
            // Geser ke Kiri: Halaman Selanjutnya
            if (currentPage < totalPages) {
                currentPage++;
                renderSinglePageMode();
                stage.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
    }
});
</script>
<?php endif; ?>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
