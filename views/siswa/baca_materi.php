<?php
/**
 * Halaman Khusus Pembaca Materi & Modul Siswa (Mobile-First Reader Mode)
 * Dioptimalkan khusus agar tidak ngebug, lancar, dan nyaman dibaca di smartphone (HP) maupun Desktop.
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

<!-- Include PDF.js Library (Mozilla Open Source) for Bulletproof Mobile PDF Rendering -->
<?php if ($isPdf && $fileUrl): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<?php endif; ?>

<style>
/* Mobile-First Reader Screen Styles */
.materi-reader-container {
    max-width: 1060px;
    margin: 0 auto;
}

/* Sticky Reader Top Bar */
.reader-topbar {
    position: sticky;
    top: 68px;
    z-index: 1020;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
}

/* Mobile responsive adjustments */
@media (max-width: 767.98px) {
    .reader-topbar {
        top: 60px;
        padding: 10px 12px !important;
        border-radius: 12px;
    }
    .reader-title-header {
        font-size: 1.1rem !important;
    }
    .btn-reader-action span {
        display: none;
    }
    .btn-reader-action {
        padding: 6px 10px !important;
    }
}

/* Document Canvas Container */
.pdf-viewport-wrapper {
    background-color: #334155;
    border-radius: 16px;
    padding: 12px;
    box-shadow: inset 0 2px 10px rgba(0,0,0,0.2);
    min-height: 400px;
}

@media (max-width: 767.98px) {
    .pdf-viewport-wrapper {
        padding: 6px;
        border-radius: 12px;
    }
}

.pdf-page-canvas {
    display: block;
    margin: 0 auto 14px auto;
    background-color: #ffffff;
    box-shadow: 0 4px 16px rgba(0,0,0,0.35);
    border-radius: 6px;
    max-width: 100%;
    height: auto !important;
}

/* Floating / Sticky Zoom Controls */
.pdf-floating-toolbar {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #0f172a;
    color: #ffffff;
    padding: 6px 14px;
    border-radius: 50rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
}

.video-responsive-container {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
    height: 0;
    overflow: hidden;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.video-responsive-container iframe,
.video-responsive-container video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
}
</style>

<main class="main-content px-2 px-md-4 py-3">
    <div class="container-fluid materi-reader-container">

        <!-- 1. STICKY MOBILE-FIRST TOPBAR NAVIGASI -->
        <div class="reader-topbar p-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASE_URL ?>index.php?url=siswa/materi" class="btn btn-light rounded-pill border fw-bold text-dark px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1" title="Kembali ke Daftar Materi">
                    <i class="bi bi-arrow-left"></i>
                    <span class="d-none d-sm-inline">Daftar Materi</span>
                </a>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold small">
                    <i class="bi bi-journal-bookmark-fill me-1"></i><?= htmlspecialchars($materi['nama_mapel']) ?>
                </span>
            </div>

            <!-- Quick Action Toolbar -->
            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                <?php if ($fileUrl): ?>
                    <!-- Buka Langsung di PDF Viewer Bawaan HP (Full Experience) -->
                    <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-warning text-dark rounded-pill fw-bold btn-reader-action px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5" title="Buka dengan Aplikasi Pembaca PDF di HP Anda (Google Drive / Adobe / Safari)">
                        <i class="bi bi-phone-fill"></i>
                        <span>Buka di HP</span>
                    </a>

                    <!-- Tombol Unduh Offline -->
                    <a href="<?= $fileUrl ?>" download class="btn btn-outline-primary rounded-pill fw-bold btn-reader-action px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5" title="Unduh Berkas ke Memori HP untuk Dibaca Tanpa Kuota">
                        <i class="bi bi-download"></i>
                        <span>Unduh</span>
                    </a>
                <?php endif; ?>

                <?php if ($isYouTube && !empty($materi['youtube_url'])): ?>
                    <a href="<?= htmlspecialchars($materi['youtube_url']) ?>" target="_blank" class="btn btn-danger rounded-pill fw-bold btn-reader-action px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5" title="Buka di Aplikasi YouTube">
                        <i class="bi bi-youtube"></i>
                        <span>Buka YouTube</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. KARTU INFORMASI MATERI & GURU PENGAMPU -->
        <div class="card card-custom border-0 rounded-4 shadow-sm p-4 mb-4" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge bg-primary text-uppercase px-3 py-1 rounded-pill">
                            <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($materi['jenis_file'] ?: 'MODUL') ?>
                        </span>
                        <span class="badge bg-light text-muted border rounded-pill px-3 py-1">
                            <i class="bi bi-people-fill me-1"></i><?= htmlspecialchars($materi['nama_kelas'] ?? 'Rombel Anda') ?>
                        </span>
                        <span class="badge bg-light text-muted border rounded-pill px-3 py-1">
                            <i class="bi bi-calendar3 me-1"></i><?= date('d M Y, H:i', strtotime($materi['created_at'])) ?> WIB
                        </span>
                    </div>
                    <h3 class="fw-bold text-dark mb-1 reader-title-header"><?= htmlspecialchars($materi['judul']) ?></h3>
                    <p class="small text-muted mb-0">
                        Pengampu: <b><?= htmlspecialchars($materi['nama_guru'] ?? 'Guru Mata Pelajaran') ?></b>
                        <?= !empty($materi['nip']) ? " (NIP: <code>" . htmlspecialchars($materi['nip']) . "</code>)" : "" ?>
                    </p>
                </div>
            </div>

            <!-- Petunjuk / Deskripsi Pembelajaran Guru -->
            <?php if (!empty($materi['deskripsi'])): ?>
                <div class="mt-3 p-3 bg-white rounded-3 border-start border-4 border-primary shadow-xs">
                    <b class="small text-primary d-block mb-1"><i class="bi bi-chat-quote-fill me-1"></i>Petunjuk Pembelajaran dari Guru:</b>
                    <p class="small text-dark mb-0 lh-base"><?= nl2br(htmlspecialchars($materi['deskripsi'])) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- 3. MOBILE-OPTIMIZED READER & VIEWER CANVAS -->
        <div class="card card-custom border-0 rounded-4 shadow-sm p-3 p-md-4 mb-4">

            <?php if ($isPdf && $fileUrl): ?>
                <!-- 📄 MODE BACA PDF LENGKAP (BERBASIS HTML5 CANVAS PDF.JS - 100% LANCAR DI HP) -->
                <div>
                    <!-- Toolbar Kontrol PDF Mobile-First -->
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 bg-light p-2.5 rounded-4 border">
                        <div class="d-flex align-items-center gap-1.5">
                            <button type="button" class="btn btn-sm btn-white border rounded-pill px-2.5 py-1 fw-bold shadow-xs" id="btnPdfPrev" title="Halaman Sebelumnya">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <span class="small fw-bold text-dark px-2" id="pdfPageInfo">Memuat Dokumen...</span>
                            <button type="button" class="btn btn-sm btn-white border rounded-pill px-2.5 py-1 fw-bold shadow-xs" id="btnPdfNext" title="Halaman Selanjutnya">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>

                        <div class="d-flex align-items-center gap-1.5">
                            <button type="button" class="btn btn-sm btn-white border rounded-pill px-2.5 py-1 fw-bold shadow-xs" id="btnPdfZoomOut" title="Perkecil Tampilan">
                                <i class="bi bi-zoom-out"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-white border rounded-pill px-2.5 py-1 fw-bold shadow-xs" id="btnPdfZoomIn" title="Perbesar Tampilan">
                                <i class="bi bi-zoom-in"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold shadow-xs" id="btnPdfFitWidth" title="Sesuaikan dengan Lebar Layar Ponsel">
                                <i class="bi bi-arrows-expand me-1"></i> <span class="d-none d-sm-inline">Pas Layar</span>
                            </button>
                        </div>
                    </div>

                    <!-- Loading State Spinner -->
                    <div id="pdfLoadingSpinner" class="text-center py-5">
                        <div class="spinner-border text-primary mb-2" role="status" style="width: 2.5rem; height: 2.5rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="fw-bold text-dark small">Menyiapkan Modul PDF...</div>
                        <small class="text-muted">Dokumen dioptimalkan agar ringan dan pas di layar smartphone Anda.</small>
                    </div>

                    <!-- Tempat Canvas Halaman PDF Dirender -->
                    <div class="pdf-viewport-wrapper" id="pdfViewportWrapper">
                        <div id="pdfCanvasContainer"></div>
                    </div>

                    <!-- Tips Membaca Ramah Ponsel -->
                    <div class="alert alert-light border rounded-4 p-3 mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <i class="bi bi-lightbulb-fill text-warning fs-5"></i>
                            <span><b>Tips Layar HP:</b> Gulir ke bawah dengan jari untuk membaca seluruh halaman secara berurutan. Klik tombol <b>Buka di HP</b> di kanan atas jika ingin membaca di aplikasi PDF bawaan ponsel Anda.</span>
                        </div>
                        <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Fullscreen
                        </a>
                    </div>
                </div>

            <?php elseif ($isYouTube && !empty($embedUrl)): ?>
                <!-- 📺 MODE PEMUTAR VIDEO YOUTUBE RESPONSIVE -->
                <div>
                    <div class="video-responsive-container">
                        <iframe src="<?= htmlspecialchars($embedUrl) ?>?rel=0&autoplay=0" title="<?= htmlspecialchars($materi['judul']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                    <div class="p-3 bg-light rounded-4 border mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i>Video pembelajaran diputar resmi via server YouTube Studio.</small>
                        <a href="<?= htmlspecialchars($materi['youtube_url']) ?>" target="_blank" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold">
                            <i class="bi bi-youtube me-1"></i> Buka di Aplikasi YouTube
                        </a>
                    </div>
                </div>

            <?php elseif ($isVideoMp4 && $fileUrl): ?>
                <!-- 🎥 MODE PEMUTAR VIDEO MP4 LOKAL -->
                <div>
                    <div class="video-responsive-container bg-dark rounded-4">
                        <video src="<?= $fileUrl ?>" controls class="w-100 h-100 rounded-4" style="object-fit: contain;">
                            Browser Anda tidak mendukung pemutar video HTML5.
                        </video>
                    </div>
                    <div class="p-3 bg-light rounded-4 border mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted"><i class="bi bi-film text-primary me-1"></i>Video MP4 interaktif KBM sekolah.</small>
                        <a href="<?= $fileUrl ?>" download class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                            <i class="bi bi-download me-1"></i> Unduh Video (MP4)
                        </a>
                    </div>
                </div>

            <?php elseif ($isImage && $fileUrl): ?>
                <!-- 🖼️ MODE PENAMPIL GAMBAR / INFOGRAFIS -->
                <div class="text-center py-2">
                    <img src="<?= $fileUrl ?>" alt="<?= htmlspecialchars($materi['judul']) ?>" class="img-fluid rounded-4 shadow-sm border mx-auto d-block" style="max-height: 680px; object-fit: contain;">
                    <div class="mt-3">
                        <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-4 fw-bold">
                            <i class="bi bi-zoom-in me-1"></i> Lihat Gambar Ukuran Asli
                        </a>
                    </div>
                </div>

            <?php elseif ($isOfficeDoc && $fileUrl): ?>
                <!-- 📑 MODE DOKUMEN OFFICE (WORD / PPT / EXCEL) -->
                <div class="text-center py-5 bg-light rounded-4 border">
                    <div class="bg-primary-subtle text-primary p-4 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-file-earmark-word-fill fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($materi['judul']) ?></h5>
                    <p class="text-muted small mb-4" style="max-width: 500px; margin: 0 auto;">
                        Dokumen Microsoft Office (<?= strtoupper($ext) ?>) dapat dibuka langsung menggunakan aplikasi pembaca dokumen di HP Anda atau diunduh untuk disimpan.
                    </p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="<?= $fileUrl ?>" download class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                            <i class="bi bi-download me-1.5"></i> Unduh File (<?= strtoupper($ext) ?>)
                        </a>
                        <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold">
                            <i class="bi bi-box-arrow-up-right me-1.5"></i> Buka File di HP
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <!-- FALLBACK VIEW -->
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-folder2-open fs-1 d-block mb-2 text-secondary opacity-50"></i>
                    <h6 class="fw-bold text-dark">Informasi Materi Pembelajaran</h6>
                    <p class="small text-muted mb-3">Tidak ada berkas media terlampir pada materi ini. Silakan periksa petunjuk guru di atas.</p>
                </div>
            <?php endif; ?>

        </div>

        <!-- 4. MATERI TERKAIT PADA MATA PELAJARAN INI (NAVIGASI TOPIK LAIN) -->
        <?php if (!empty($materiTerkait)): ?>
            <div class="card card-custom border-0 rounded-4 shadow-sm p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-collection-play-fill text-primary me-2"></i>Materi Lain pada Mapel <?= htmlspecialchars($materi['nama_mapel']) ?></h6>
                    <span class="badge bg-light text-muted border rounded-pill px-3 py-1.5"><?= count($materiTerkait) ?> Modul Lain</span>
                </div>
                <div class="row g-3">
                    <?php foreach ($materiTerkait as $mt): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="p-3 bg-light rounded-4 border h-100 d-flex flex-column justify-content-between shadow-xs">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary text-uppercase" style="font-size:0.7rem;"><?= htmlspecialchars($mt['jenis_file'] ?: 'MODUL') ?></span>
                                        <small class="text-muted" style="font-size:0.75rem;"><?= date('d/m/Y', strtotime($mt['created_at'])) ?></small>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($mt['judul']) ?>"><?= htmlspecialchars($mt['judul']) ?></h6>
                                    <small class="text-muted d-block text-truncate"><?= htmlspecialchars($mt['deskripsi'] ?: 'Modul pembelajaran KBM') ?></small>
                                </div>
                                <div class="mt-3 pt-2 border-top">
                                    <a href="<?= BASE_URL ?>index.php?url=siswa/bacaMateri&id=<?= $mt['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill w-100 fw-bold">
                                        <i class="bi bi-book-half me-1"></i> Baca Materi Ini
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</main>

<!-- SCRIPT PENGATURAN PDF.JS VIEWER UNTUK HP DAN DESKTOP -->
<?php if ($isPdf && $fileUrl): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof pdfjsLib === 'undefined') {
        document.getElementById('pdfLoadingSpinner').innerHTML = `
            <div class="alert alert-warning p-3 rounded-3 small">
                Pustaka PDF tidak dapat dimuat secara online. Silakan klik tombol <b>Buka di HP</b> atau <b>Unduh</b> di bagian atas untuk membaca modul ini langsung di ponsel Anda.
            </div>
        `;
        return;
    }

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const pdfUrl = '<?= $fileUrl ?>';
    const container = document.getElementById('pdfCanvasContainer');
    const spinner = document.getElementById('pdfLoadingSpinner');
    const pageInfo = document.getElementById('pdfPageInfo');
    const wrapper = document.getElementById('pdfViewportWrapper');

    let pdfDoc = null;
    let currentRenderScale = 1.0;
    let basePageWidth = 595; // Standar A4 width points

    // Hitung skala otomatis agar pas dengan lebar layar HP (Fit Width)
    function calculateFitWidthScale() {
        const availableWidth = wrapper.clientWidth - (window.innerWidth < 768 ? 16 : 30);
        return Math.min(2.5, Math.max(0.65, availableWidth / basePageWidth));
    }

    currentRenderScale = calculateFitWidthScale();

    // Muat Dokumen PDF
    const loadingTask = pdfjsLib.getDocument(pdfUrl);
    loadingTask.promise.then(function(pdf) {
        pdfDoc = pdf;
        spinner.style.display = 'none';
        pageInfo.textContent = `Total: ${pdf.numPages} Halaman`;

        // Render semua halaman secara kontinu (Scroll Mode - Sangat Alami di HP)
        renderAllPages();
    }).catch(function(error) {
        console.error('Error memuat PDF:', error);
        spinner.innerHTML = `
            <div class="alert alert-danger p-3 rounded-4 small">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                Tidak dapat menampilkan pratinjau PDF di dalam browser.
                <div class="mt-2">
                    <a href="${pdfUrl}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                        <i class="bi bi-phone me-1"></i> Buka dengan Pembaca PDF HP
                    </a>
                </div>
            </div>
        `;
    });

    function renderAllPages() {
        if (!pdfDoc) return;
        container.innerHTML = ''; // Bersihkan canvas lama

        // Dapatkan halaman pertama untuk mengukur rasio lebar standar
        pdfDoc.getPage(1).then(function(firstPage) {
            const viewport = firstPage.getViewport({ scale: 1.0 });
            basePageWidth = viewport.width;
            currentRenderScale = calculateFitWidthScale();

            for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                renderSinglePage(pageNum);
            }
        });
    }

    function renderSinglePage(pageNum) {
        pdfDoc.getPage(pageNum).then(function(page) {
            const viewport = page.getViewport({ scale: currentRenderScale });
            const canvas = document.createElement('canvas');
            canvas.className = 'pdf-page-canvas';
            canvas.id = 'pdf-page-' + pageNum;
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            container.appendChild(canvas);

            const renderContext = {
                canvasContext: canvas.getContext('2d'),
                viewport: viewport
            };
            page.render(renderContext);
        });
    }

    // Tombol Zoom In (+)
    document.getElementById('btnPdfZoomIn').addEventListener('click', function() {
        if (currentRenderScale < 2.5) {
            currentRenderScale += 0.2;
            renderAllPages();
        }
    });

    // Tombol Zoom Out (-)
    document.getElementById('btnPdfZoomOut').addEventListener('click', function() {
        if (currentRenderScale > 0.6) {
            currentRenderScale -= 0.2;
            renderAllPages();
        }
    });

    // Tombol Fit Width (Pas Lebar Layar Ponsel)
    document.getElementById('btnPdfFitWidth').addEventListener('click', function() {
        currentRenderScale = calculateFitWidthScale();
        renderAllPages();
    });

    // Navigasi Cepat Halaman (Scroll Halus ke Halaman Tersebut)
    let activePage = 1;
    document.getElementById('btnPdfNext').addEventListener('click', function() {
        if (pdfDoc && activePage < pdfDoc.numPages) {
            activePage++;
            scrollToPage(activePage);
        }
    });

    document.getElementById('btnPdfPrev').addEventListener('click', function() {
        if (activePage > 1) {
            activePage--;
            scrollToPage(activePage);
        }
    });

    function scrollToPage(num) {
        const targetCanvas = document.getElementById('pdf-page-' + num);
        if (targetCanvas) {
            targetCanvas.scrollIntoView({ behavior: 'smooth', block: 'start' });
            pageInfo.textContent = `Halaman ${num} dari ${pdfDoc.numPages}`;
        }
    }

    // Tangani perubahan orientasi layar HP (Portrait <-> Landscape)
    window.addEventListener('resize', function() {
        clearTimeout(window.pdfResizeTimer);
        window.pdfResizeTimer = setTimeout(function() {
            if (pdfDoc) {
                currentRenderScale = calculateFitWidthScale();
                renderAllPages();
            }
        }, 300);
    });
});
</script>
<?php endif; ?>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
