<?php
/**
 * Halaman Khusus Pembaca Materi & Modul Pembelajaran Siswa (Modern Studio E-Reader)
 * Desain Responsif, Profesional, Elegan, dan Dioptimalkan Sempurna untuk Desktop & Smartphone (HP).
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

// Ukuran file jika berkas fisik tersedia
$fileSizeBytes = null;
if (!empty($materi['file_path']) && file_exists(ROOT_PATH . 'assets/uploads/materi/' . $materi['file_path'])) {
    $fileSizeBytes = @filesize(ROOT_PATH . 'assets/uploads/materi/' . $materi['file_path']);
}
$fileSizeFormatted = $fileSizeBytes ? round($fileSizeBytes / (1024 * 1024), 1) . ' MB' : null;
?>

<style>
/* ══════════════════════════════════════════════════════════════════
   🎨 MODERN STUDIO E-READER (HIGH AESTHETICS & RESPONSIVE GRID)
   ══════════════════════════════════════════════════════════════════ */
.reader-page-wrapper {
    max-width: 1600px;
    margin: 0 auto;
}

/* 1. Header Card */
.reader-header-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    padding: 16px 22px;
}
[data-bs-theme="dark"] .reader-header-card {
    background: #1e293b;
    border-color: #334155;
}

/* 2. Reader Stage (Panggung Baca Utama) */
.reader-stage-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 175px);
    min-height: 760px;
    transition: all 0.25s ease;
}
[data-bs-theme="dark"] .reader-stage-card {
    background: #1e293b;
    border-color: #334155;
}

/* Top Toolbar inside Stage Card */
.reader-stage-toolbar {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 10px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    z-index: 10;
}
[data-bs-theme="dark"] .reader-stage-toolbar {
    background: #0f172a;
    border-color: #334155;
}

/* Embedded Frame Area */
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

/* 3. Companion Sidebar (Kartu Guru, Petunjuk, & Materi Terkait) */
.companion-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    padding: 20px;
    margin-bottom: 18px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
[data-bs-theme="dark"] .companion-card {
    background: #1e293b;
    border-color: #334155;
}

.teacher-avatar-circle {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.2rem;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
}

.playlist-materi-item {
    padding: 11px 14px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    text-decoration: none;
    display: block;
    transition: all 0.2s ease;
}
.playlist-materi-item:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
    transform: translateX(4px);
}
[data-bs-theme="dark"] .playlist-materi-item {
    background: #0f172a;
    border-color: #334155;
}

/* Fullscreen Stage Mode */
.reader-stage-card:fullscreen {
    border-radius: 0 !important;
    height: 100vh !important;
    max-height: 100vh !important;
    width: 100vw !important;
}

/* Video Stage Ratio */
.video-responsive-ratio-169 {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    background: #000;
    border-radius: 16px;
}
.video-responsive-ratio-169 iframe,
.video-responsive-ratio-169 video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

/* Mobile Adjustments (Smartphone HP) */
@media (max-width: 991.98px) {
    .reader-header-card {
        padding: 12px 14px;
        border-radius: 14px;
    }
    .reader-stage-card {
        border-radius: 16px;
        height: 76vh;
        min-height: 520px;
    }
    .reader-stage-toolbar {
        padding: 8px 12px;
    }
    .btn-label-mobile-hide span {
        display: none !important;
    }
    .btn-label-mobile-hide {
        padding: 6px 10px !important;
    }
}
</style>

<main class="main-content px-2 px-md-3 px-lg-4 py-2 py-md-3">
    <div class="container-fluid reader-page-wrapper p-0">

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 🧭 1. MODERN TOPBAR: BREADCRUMB, INFO MAPEL & ACTION BUTTONS -->
        <!-- ════════════════════════════════════════════════════════════════ -->
        <div class="reader-header-card mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2.5">
            <!-- Navigasi Kiri & Identitas Modul -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>index.php?url=siswa/materi" class="btn btn-sm btn-light rounded-pill border fw-bold text-dark px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5 hover-scale" title="Kembali ke Daftar Materi">
                    <i class="bi bi-arrow-left"></i>
                    <span class="d-none d-sm-inline">Daftar Materi</span>
                </a>

                <div class="vr bg-secondary opacity-25 d-none d-sm-block" style="height: 20px;"></div>

                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold" style="font-size:0.8rem;">
                    <i class="bi bi-journal-bookmark-fill me-1"></i><?= htmlspecialchars($materi['nama_mapel']) ?>
                </span>

                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1.5 fw-semibold d-none d-md-inline-block" style="font-size:0.75rem;">
                    <i class="bi bi-mortarboard-fill me-1"></i><?= htmlspecialchars($materi['nama_kelas'] ?? 'Rombel Anda') ?>
                </span>

                <?php if ($fileSizeFormatted): ?>
                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1.5 fw-semibold d-none d-lg-inline-block" style="font-size:0.75rem;">
                        <i class="bi bi-hdd me-1"></i><?= $fileSizeFormatted ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Action Toolbar Kanan -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php if ($fileUrl): ?>
                    <!-- Buka Langsung di Aplikasi Bawaan HP (Google Drive / Adobe / Safari) -->
                    <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-warning text-dark rounded-pill fw-bold px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5 btn-label-mobile-hide" title="Buka dengan Aplikasi Pembaca PDF / Dokumen Bawaan HP">
                        <i class="bi bi-phone-fill"></i>
                        <span>Buka di HP</span>
                    </a>

                    <!-- Tombol Unduh Offline -->
                    <a href="<?= $fileUrl ?>" download class="btn btn-sm btn-outline-primary rounded-pill fw-bold px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5 btn-label-mobile-hide" title="Unduh Berkas ke Komputer / Smartphone">
                        <i class="bi bi-download"></i>
                        <span>Unduh File</span>
                    </a>
                <?php endif; ?>

                <?php if ($isYouTube && !empty($materi['youtube_url'])): ?>
                    <a href="<?= htmlspecialchars($materi['youtube_url']) ?>" target="_blank" class="btn btn-sm btn-danger rounded-pill fw-bold px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5 btn-label-mobile-hide" title="Tonton di Aplikasi YouTube Resmi">
                        <i class="bi bi-youtube"></i>
                        <span>YouTube</span>
                    </a>
                <?php endif; ?>

                <!-- Tombol Gulir ke Petunjuk Guru (Khusus Layar HP) -->
                <a href="#petunjukGuruSection" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1.5 d-lg-none" title="Lihat Petunjuk Guru">
                    <i class="bi bi-chat-quote-fill"></i>
                </a>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════ -->
        <!-- 📐 2. GRID UTAMA 2-KOLOM (PANGGUNG BACA LEBAR + SIDEBAR) -->
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
                        <div class="d-flex align-items-center gap-2 overflow-hidden me-auto">
                            <span class="badge bg-primary text-uppercase px-2.5 py-1 rounded-pill" style="font-size:0.7rem;">
                                <?= htmlspecialchars($materi['jenis_file'] ?: ($isPdf ? 'PDF' : 'MODUL')) ?>
                            </span>
                            <h6 class="fw-bold text-dark mb-0 text-truncate" title="<?= htmlspecialchars($materi['judul']) ?>" style="letter-spacing: -0.2px;">
                                <?= htmlspecialchars($materi['judul']) ?>
                            </h6>
                        </div>

                        <!-- Kontrol Pemirsa untuk Dokumen PDF -->
                        <?php if ($isPdf && $fileUrl): ?>
                            <div class="d-flex align-items-center gap-1.5">
                                <!-- Engine Mode Switcher (Tampilan Langsung vs Google Cloud) -->
                                <div class="btn-group btn-group-sm rounded-pill border p-0.5 bg-white shadow-xs" role="group">
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-bold" id="btnEngineNative" onclick="setViewerEngine('native')" title="Tampilan Bawaan Browser (Kualitas Tertinggi & Cepat)">
                                        <i class="bi bi-file-earmark-pdf-fill me-1"></i><span class="d-none d-sm-inline">Standar</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-white rounded-pill px-2.5 py-0.5 fw-semibold text-muted" id="btnEngineGoogle" onclick="setViewerEngine('google')" title="Mode Google Cloud Viewer (Sangat Ringan di HP)">
                                        <i class="bi bi-cloud-check-fill me-1"></i><span class="d-none d-sm-inline">Cloud View</span>
                                    </button>
                                </div>

                                <!-- Buka Tab Baru -->
                                <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-white border rounded-pill px-2.5 py-1 shadow-xs text-dark" title="Buka Dokumen di Tab Penuh">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>

                                <!-- Tombol Layar Penuh (Fullscreen Stage) -->
                                <button type="button" class="btn btn-sm btn-white border rounded-pill px-2.5 py-1 shadow-xs text-dark" id="btnToggleFullscreen" onclick="toggleFullscreenStage()" title="Mode Layar Penuh (Fokus Baca)">
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
                            <!-- 1. PDF EMBEDDED VIEWER (NATIVE & CLOUD SWITCHABLE - 100% RELIABLE) -->
                            <iframe 
                                id="readerIframe" 
                                src="<?= $fileUrl ?>#toolbar=1&navpanes=0" 
                                class="reader-iframe-element" 
                                allowfullscreen 
                                title="<?= htmlspecialchars($materi['judul']) ?>">
                            </iframe>

                        <?php elseif ($isYouTube && !empty($embedUrl)): ?>
                            <!-- 2. PEMUTAR VIDEO YOUTUBE STREAMING RESPONSIVE -->
                            <div class="p-3 p-md-4 h-100 d-flex flex-column justify-content-center bg-black">
                                <div class="video-responsive-ratio-169 shadow-lg">
                                    <iframe src="<?= htmlspecialchars($embedUrl) ?>?rel=0&autoplay=0" title="<?= htmlspecialchars($materi['judul']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            </div>

                        <?php elseif ($isVideoMp4 && $fileUrl): ?>
                            <!-- 3. PEMUTAR VIDEO MP4 LOKAL -->
                            <div class="p-3 p-md-4 h-100 d-flex flex-column justify-content-center bg-black">
                                <div class="video-responsive-ratio-169 shadow-lg">
                                    <video src="<?= $fileUrl ?>" controls class="w-100 h-100" style="object-fit: contain;">
                                        Browser Anda tidak mendukung pemutar video HTML5 bawaan.
                                    </video>
                                </div>
                            </div>

                        <?php elseif ($isImage && $fileUrl): ?>
                            <!-- 4. PENAMPIL GAMBAR / INFOGRAFIS -->
                            <div class="p-3 p-md-4 h-100 text-center d-flex flex-column align-items-center justify-content-center bg-light">
                                <img src="<?= $fileUrl ?>" alt="<?= htmlspecialchars($materi['judul']) ?>" class="img-fluid rounded-3 shadow-sm border mx-auto d-block" style="max-height: 85%; object-fit: contain;">
                                <div class="mt-3">
                                    <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">
                                        <i class="bi bi-zoom-in me-1"></i> Buka Gambar Resolusi Penuh
                                    </a>
                                </div>
                            </div>

                        <?php elseif ($isOfficeDoc && $fileUrl): ?>
                            <!-- 5. DOKUMEN OFFICE (WORD, PPT, EXCEL) - INTEGRASI GOOGLE CLOUD VIEWER -->
                            <iframe 
                                src="https://docs.google.com/gview?embedded=true&url=<?= urlencode($fileUrl) ?>" 
                                class="reader-iframe-element" 
                                allowfullscreen 
                                title="<?= htmlspecialchars($materi['judul']) ?>">
                            </iframe>

                        <?php else: ?>
                            <!-- 6. MATERI BERUPA TEKS / TANPA BERKAS FISIK -->
                            <div class="p-5 text-center text-muted my-auto">
                                <i class="bi bi-journal-text fs-1 d-block mb-3 text-primary opacity-75"></i>
                                <h5 class="fw-bold text-dark">Informasi Pembelajaran</h5>
                                <p class="small text-muted mb-0">Materi ini disajikan berbasis instruksi dan bimbingan guru. Silakan baca petunjuk di kolom sebelah kanan.</p>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- 📚 KOLOM KANAN (SIDEBAR): GURU, PETUNJUK & MATERI LAIN -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <div class="col-12 col-lg-4 col-xl-3" id="petunjukGuruSection">

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

                <!-- Banner Tips Pintar Akses di HP -->
                <div class="alert alert-primary border-0 rounded-4 p-3 shadow-xs" style="background:#eff6ff; color:#1e40af;">
                    <div class="d-flex gap-2">
                        <i class="bi bi-phone-vibrate-fill text-primary fs-5 mt-0.5"></i>
                        <div class="small" style="font-size:0.8rem; line-height:1.45;">
                            <b>Membaca di Ponsel (HP)?</b><br>
                            Tekan tombol <b>Buka di HP</b> di kanan atas untuk membuka langsung dengan aplikasi pembaca PDF bawaan smartphone Anda (Google Drive PDF / Adobe Acrobat) dengan fitur cubit-layar (*pinch to zoom*) yang sangat mulus.
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

// Pengganti Engine Pemirsa Dokumen (Standar Native vs Google Cloud Viewer)
function setViewerEngine(engine) {
    const iframe = document.getElementById('readerIframe');
    const btnNative = document.getElementById('btnEngineNative');
    const btnGoogle = document.getElementById('btnEngineGoogle');

    if (!iframe) return;

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
