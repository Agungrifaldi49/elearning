<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
// Helper to extract YouTube Embed URL
if (!function_exists('getYouTubeEmbedUrl')) {
    function getYouTubeEmbedUrl($url) {
        if (empty($url)) return '';
        if (strpos($url, 'embed/') !== false) return $url;
        
        $videoId = '';
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $match)) {
            $videoId = $match[1];
        }
        return !empty($videoId) ? "https://www.youtube.com/embed/" . $videoId : $url;
    }
}
?>

<style>
/* Modern Siswa Materi & Video Learning Styling */
.materi-siswa-page-wrapper {
    padding-top: 28px !important;
}

/* Glassmorphic Hero Banner */
.materi-siswa-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #2563eb 100%);
    border-radius: 20px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
    position: relative;
    overflow: hidden;
}

.materi-siswa-hero::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}

/* Cards Architecture */
.materi-card-item {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    transition: all 0.25s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.materi-card-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
}

.badge-mapel-tag {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-weight: 700;
    font-size: 0.76rem;
    padding: 5px 12px;
    border-radius: 50rem;
}

/* Tab Navigation Styling */
.materi-nav-tabs .nav-link {
    border: none;
    color: #64748b;
    font-weight: 600;
    padding: 12px 22px;
    border-radius: 12px;
    transition: all 0.2s ease;
    font-size: 0.92rem;
}
.materi-nav-tabs .nav-link:hover {
    color: #2563eb;
    background-color: rgba(37, 99, 235, 0.06);
}
.materi-nav-tabs .nav-link.active {
    color: #ffffff !important;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
}

.video-responsive-ratio {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 */
    height: 0;
    overflow: hidden;
    border-radius: 12px;
}
.video-responsive-ratio iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}
</style>

<main class="main-content px-3 px-md-4 materi-siswa-page-wrapper pt-4 mt-4 mt-md-5">
    <div class="container-fluid">

        <!-- 🚀 HERO BANNER SISWA MATERI -->
        <div class="materi-siswa-hero p-4 p-md-5 mb-4">
            <div class="row align-items-center relative-zIndex-1">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="d-inline-flex align-items-center gap-2 px-3.5 py-2 rounded-pill bg-warning text-dark shadow-sm small fw-bold mb-3">
                        <i class="bi bi-book-half text-dark fs-6"></i>
                        <span>Materi & Video Streaming Learning Studio</span>
                    </div>
                    <h2 class="fw-bold mb-2 text-white" style="letter-spacing: -0.5px;">Materi & Video Pembelajaran</h2>
                    <p class="text-white text-opacity-85 small mb-0 lh-lg" style="max-width: 650px;">
                        Pelajari modul materi (PDF/Office/Gambar) dengan fitur pratinjau langsung, dan tonton video streaming YouTube dari Guru Pengampu secara interaktif tanpa keluar aplikasi.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" class="btn btn-warning text-dark px-4 py-2.5 rounded-pill fw-bold shadow-lg d-inline-flex align-items-center gap-2 hover-scale">
                        <i class="bi bi-key-fill fs-5"></i>
                        <span>Daftar Mapel Baru (Key)</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 📑 TABBED NAVIGATION -->
        <ul class="nav nav-pills materi-nav-tabs gap-2 mb-4 p-1.5 bg-white rounded-4 border shadow-xs" id="materiSiswaTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active d-flex align-items-center gap-2" id="tab-modul-tab" data-bs-toggle="tab" data-bs-target="#tab-modul" type="button" role="tab">
                    <i class="bi bi-file-earmark-text-fill"></i> Modul & Berkas Materi (<?= count($materiList) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center gap-2" id="tab-video-tab" data-bs-toggle="tab" data-bs-target="#tab-video" type="button" role="tab">
                    <i class="bi bi-youtube text-danger"></i> Video Streaming YouTube (<?= count($videoList ?? []) ?>)
                </button>
            </li>
        </ul>

        <!-- TAB CONTENT -->
        <div class="tab-content" id="materiSiswaTabContent">

            <!-- TAB 1: MODUL & BERKAS MATERI (PRATINJAU LANGSUNG) -->
            <div class="tab-pane fade show active" id="tab-modul" role="tabpanel">
                <div class="row g-4">
                    <?php if (empty($materiList)): ?>
                        <div class="col-12 text-center py-5 bg-white rounded-4 border shadow-sm">
                            <i class="bi bi-folder-x fs-1 text-slate-300 d-block mb-2"></i>
                            <h5 class="fw-bold text-dark mb-1">Belum Ada Modul Materi</h5>
                            <p class="text-muted small mb-0">Belum ada modul materi pembelajaran yang terdaftar untuk kelas dan mapel Anda saat ini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($materiList as $m): 
                            $isEnrolled = isset($enrolledMapels[$m['mapel_id'] . '_' . $m['guru_id']]) || isset($enrolledMapels[$m['mapel_id']]);
                            $ext = strtolower(pathinfo($m['file_path'] ?? '', PATHINFO_EXTENSION));
                            $isPdf = ($ext === 'pdf');
                            $isImage = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp']);
                            $isVideoFile = in_array($ext, ['mp4', 'webm', 'ogg']);
                            $filePath = !empty($m['file_path']) ? BASE_URL . 'assets/uploads/materi/' . htmlspecialchars($m['file_path']) : null;
                        ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="materi-card-item p-4 border-top border-4 <?= $isEnrolled ? 'border-primary' : 'border-danger' ?>">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="badge bg-primary text-uppercase px-3 py-1.5 rounded-pill"><i class="bi bi-file-earmark me-1"></i><?= htmlspecialchars($m['jenis_file'] ?: 'MODUL') ?></span>
                                        <?php if ($isEnrolled): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Terdaftar</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold"><i class="bi bi-lock-fill me-1"></i>Terkunci</span>
                                        <?php endif; ?>
                                    </div>

                                    <h5 class="fw-bold mb-1 text-dark lh-base" style="letter-spacing: -0.2px;"><?= htmlspecialchars($m['judul']) ?></h5>
                                    <p class="mb-2">
                                        <span class="badge-mapel-tag"><i class="bi bi-journal-bookmark-fill me-1"></i><?= htmlspecialchars($m['nama_mapel']) ?></span>
                                    </p>
                                    <p class="small text-muted mb-4 lh-lg" style="color: #475569;"><?= htmlspecialchars($m['deskripsi']) ?></p>

                                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <small class="fw-semibold text-muted"><i class="bi bi-person-circle text-primary me-1"></i><?= htmlspecialchars($m['nama_guru']) ?></small>
                                        
                                        <?php if ($isEnrolled): ?>
                                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                <?php if ($filePath): ?>
                                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalPreviewMateri<?= $m['id'] ?>">
                                                        <i class="bi bi-eye-fill"></i> Baca Materi
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (!empty($m['youtube_url'])): ?>
                                                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalPlayVideo<?= $m['id'] ?>">
                                                        <i class="bi bi-play-circle-fill"></i> Video
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <a href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" class="btn btn-sm btn-warning text-dark rounded-pill px-3 fw-bold">
                                                <i class="bi bi-key-fill me-1"></i> Input Key Mapel
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB 2: VIDEO STREAMING YOUTUBE (PEMUTARAN LANGSUNG) -->
            <div class="tab-pane fade" id="tab-video">
                <div class="row g-4">
                    <?php if (empty($videoList)): ?>
                        <div class="col-12 text-center py-5 bg-white rounded-4 border shadow-sm">
                            <i class="bi bi-youtube fs-1 text-danger d-block mb-2"></i>
                            <h5 class="fw-bold text-dark mb-1">Belum Ada Video Pembelajaran</h5>
                            <p class="text-muted small mb-0">Belum ada video streaming YouTube yang diunggah untuk kelas Anda saat ini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($videoList as $v): 
                            $isEnrolled = isset($enrolledMapels[$v['mapel_id'] . '_' . $v['guru_id']]) || isset($enrolledMapels[$v['mapel_id']]);
                            $embedUrl = getYouTubeEmbedUrl($v['youtube_url'] ?? '');
                        ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="materi-card-item p-4 border-top border-4 <?= $isEnrolled ? 'border-danger' : 'border-secondary' ?>">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="badge bg-danger px-3 py-1.5 rounded-pill"><i class="bi bi-play-btn-fill me-1"></i>STREAMING</span>
                                        <?php if ($isEnrolled): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Terdaftar</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold"><i class="bi bi-lock-fill me-1"></i>Terkunci</span>
                                        <?php endif; ?>
                                    </div>

                                    <h5 class="fw-bold mb-1 text-dark lh-base"><?= htmlspecialchars($v['judul']) ?></h5>
                                    <p class="mb-2">
                                        <span class="badge-mapel-tag"><i class="bi bi-journal-bookmark-fill me-1"></i><?= htmlspecialchars($v['nama_mapel']) ?></span>
                                    </p>
                                    <p class="small text-muted mb-4 lh-lg"><?= htmlspecialchars($v['deskripsi']) ?></p>

                                    <!-- Inline Embedded Video Player if Enrolled -->
                                    <?php if ($isEnrolled && !empty($embedUrl)): ?>
                                        <div class="video-responsive-ratio mb-3 shadow-sm border">
                                            <iframe src="<?= htmlspecialchars($embedUrl) ?>" title="<?= htmlspecialchars($v['judul']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <small class="fw-semibold text-muted"><i class="bi bi-person-circle text-primary me-1"></i><?= htmlspecialchars($v['nama_guru']) ?></small>
                                        
                                        <?php if ($isEnrolled): ?>
                                            <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalPlayVideo<?= $v['id'] ?>">
                                                <i class="bi bi-fullscreen"></i> Layar Penuh
                                            </button>
                                        <?php else: ?>
                                            <a href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" class="btn btn-sm btn-warning text-dark rounded-pill px-3 fw-bold">
                                                <i class="bi bi-key-fill me-1"></i> Input Key Mapel
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- 📝 MODALS SECTION PREVIEW MATERI & VIDEO STREAMING -->
<!-- ════════════════════════════════════════════════════════════════ -->

<?php foreach ($materiList as $m): 
    $ext = strtolower(pathinfo($m['file_path'] ?? '', PATHINFO_EXTENSION));
    $isPdf = ($ext === 'pdf');
    $isImage = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp']);
    $isVideoFile = in_array($ext, ['mp4', 'webm', 'ogg']);
    $filePath = !empty($m['file_path']) ? BASE_URL . 'assets/uploads/materi/' . htmlspecialchars($m['file_path']) : null;
    $embedUrl = getYouTubeEmbedUrl($m['youtube_url'] ?? '');
?>
    <!-- Modal Preview Modul Materi (Bisa Dilihat Dulu) -->
    <?php if ($filePath): ?>
        <div class="modal fade" id="modalPreviewMateri<?= $m['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                    <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="bg-primary rounded-3 p-2 text-white shadow-xs">
                                <i class="bi bi-eye-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="modal-title fw-bold text-white mb-0">Pratinjau Modul Materi: <?= htmlspecialchars($m['judul']) ?></h6>
                                <small class="text-info fw-medium" style="font-size:0.75rem;">Mapel: <?= htmlspecialchars($m['nama_mapel']) ?> &bull; Guru: <?= htmlspecialchars($m['nama_guru']) ?></small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        <div class="p-3 bg-white rounded-3 border mb-3 shadow-xs">
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-info-circle text-primary me-1"></i>Deskripsi & Petunjuk:</h6>
                            <p class="text-slate-700 small mb-0 lh-lg"><?= htmlspecialchars($m['deskripsi']) ?></p>
                        </div>

                        <!-- Document & Media Viewer Frame -->
                        <div class="border rounded-4 bg-white p-2 shadow-sm overflow-hidden text-center">
                            <?php if ($isPdf): ?>
                                <iframe src="<?= $filePath ?>#toolbar=0" style="width:100%; height:580px; border:none;" class="rounded-3"></iframe>
                            <?php elseif ($isImage): ?>
                                <img src="<?= $filePath ?>" alt="Preview" class="img-fluid rounded-3 mx-auto d-block shadow-sm" style="max-height:550px; object-fit:contain;">
                            <?php elseif ($isVideoFile): ?>
                                <video controls class="w-100 rounded-3 shadow-sm" style="max-height:550px;">
                                    <source src="<?= $filePath ?>" type="video/<?= $ext ?>">
                                    Browser Anda tidak mendukung pemutaran video langsung.
                                </video>
                            <?php else: ?>
                                <!-- Office documents / fallback Google Viewer -->
                                <iframe src="https://docs.google.com/gview?url=<?= urlencode($filePath) ?>&embedded=true" style="width:100%; height:580px; border:none;" class="rounded-3"></iframe>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 p-4 justify-content-between bg-white border-top">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                        <a href="<?= $filePath ?>" download class="btn btn-outline-primary rounded-pill px-4 fw-bold shadow-xs">
                            <i class="bi bi-download me-1.5"></i> Unduh File Modul
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal Play Video YouTube (Pemutaran Langsung) -->
    <?php if (!empty($embedUrl)): ?>
        <div class="modal fade" id="modalPlayVideo<?= $m['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                    <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="bg-danger rounded-3 p-2 text-white shadow-xs">
                                <i class="bi bi-youtube fs-5"></i>
                            </div>
                            <div>
                                <h6 class="modal-title fw-bold text-white mb-0">Video Streaming: <?= htmlspecialchars($m['judul']) ?></h6>
                                <small class="text-info fw-medium" style="font-size:0.75rem;">Mapel: <?= htmlspecialchars($m['nama_mapel']) ?> &bull; Guru: <?= htmlspecialchars($m['nama_guru']) ?></small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 bg-black">
                        <div class="video-responsive-ratio shadow-lg rounded-4 overflow-hidden">
                            <iframe src="<?= htmlspecialchars($embedUrl) ?>" title="<?= htmlspecialchars($m['judul']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 p-4 justify-content-between bg-dark text-white">
                        <small class="text-white text-opacity-75"><i class="bi bi-info-circle me-1"></i>Video diputar langsung dari server YouTube secara resmi.</small>
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Tutup Pemutar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php if (!empty($videoList)): ?>
    <?php foreach ($videoList as $v): 
        $embedUrl = getYouTubeEmbedUrl($v['youtube_url'] ?? '');
    ?>
        <?php if (!empty($embedUrl)): ?>
            <div class="modal fade" id="modalPlayVideo<?= $v['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                        <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="bg-danger rounded-3 p-2 text-white shadow-xs">
                                    <i class="bi bi-youtube fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="modal-title fw-bold text-white mb-0">Video Streaming: <?= htmlspecialchars($v['judul']) ?></h6>
                                    <small class="text-info fw-medium" style="font-size:0.75rem;">Mapel: <?= htmlspecialchars($v['nama_mapel']) ?> &bull; Guru: <?= htmlspecialchars($v['nama_guru']) ?></small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4 bg-black">
                            <div class="video-responsive-ratio shadow-lg rounded-4 overflow-hidden">
                                <iframe src="<?= htmlspecialchars($embedUrl) ?>" title="<?= htmlspecialchars($v['judul']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 p-4 justify-content-between bg-dark text-white">
                            <small class="text-white text-opacity-75"><i class="bi bi-info-circle me-1"></i>Video diputar langsung dari server YouTube secara resmi.</small>
                            <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Tutup Pemutar</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
