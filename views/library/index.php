<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
// Calculate KPI Summary Stats
$totalBooksCount = count($books);
$pdfCount = 0;
$videoCount = 0;
$totalViewsCount = 0;

if (!empty($books)) {
    foreach ($books as $bItem) {
        $fType = strtolower($bItem['file_type'] ?? 'pdf');
        if ($fType === 'pdf') $pdfCount++;
        if (in_array($fType, ['video','mp4','mkv','avi'])) $videoCount++;
        $totalViewsCount += (int)($bItem['view_count'] ?? 0);
    }
}
$userRole = AuthHelper::user()['role_name'] ?? '';
$isAdmin = ($userRole === 'administrator');
$canUpload = in_array($userRole, ['administrator', 'Guru']);
?>

<style>
/* Modern LMS Digital Library Architecture */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.library-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
    background-color: #f8fafc;
    min-height: 100vh;
}

/* Glassmorphic Premium Hero Banner */
.library-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #2563eb 100%);
    border-radius: 24px;
    box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.25);
    position: relative;
    overflow: hidden;
    color: #ffffff;
}
.library-hero-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 420px;
    height: 420px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}
.library-hero-banner::after {
    content: '';
    position: absolute;
    bottom: -40%;
    left: -10%;
    width: 350px;
    height: 350px;
    background: radial-gradient(circle, rgba(147, 51, 234, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}

/* KPI Summary Cards */
.lib-kpi-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #ffffff;
    padding: 16px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
    transition: all 0.25s ease;
    height: 100%;
}
.lib-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0,0,0,0.06);
    border-color: #cbd5e1;
}
.lib-kpi-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

/* 3D Modern Book Card */
.book-card-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
}
.book-card-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 35px -5px rgba(15, 23, 42, 0.1) !important;
    border-color: #cbd5e1;
}

/* Book Cover Styling */
.book-cover-aspect {
    height: 160px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: inset -5px 0 15px rgba(0, 0, 0, 0.25);
}
.book-ribbon-spine {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 12px;
    background: rgba(0, 0, 0, 0.25);
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.15);
}

.text-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
}

/* Category Filter Pills Bar */
.cat-pill-btn {
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    font-weight: 600;
    font-size: 0.8rem;
    border-radius: 50rem;
    padding: 6px 16px;
    transition: all 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.cat-pill-btn:hover {
    background-color: #f1f5f9;
    color: #0f172a;
}
.cat-pill-btn.active {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}

.hover-scale {
    transition: transform 0.2s ease;
}
.hover-scale:hover {
    transform: scale(1.02);
}
</style>

<!-- Top Clearance for Fixed Navbar -->
<main class="main-content px-3 px-md-4 library-wrapper pt-4 mt-4 mt-md-5 pb-5">
<div class="container-fluid max-width-1400 pt-2">

    <!-- 🚀 HERO BANNER PERPUSTAKAAN -->
    <div class="library-hero-banner text-white p-4 p-md-5 mb-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-3 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white p-3.5 rounded-4 shadow-md d-flex align-items-center justify-content-center flex-shrink-0" style="width: 60px; height: 60px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                    <i class="bi bi-journal-bookmark-fill fs-2"></i>
                </div>
                <div>
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-warning text-dark shadow-xs small fw-bold mb-2">
                        <i class="bi bi-mortarboard-fill text-dark fs-6"></i>
                        <span>Pusat Literasi & Reference Center</span>
                    </div>
                    <h3 class="fw-extrabold text-white mb-1" style="letter-spacing: -0.5px;">Perpustakaan Digital SMK Muthia Harapan</h3>
                    <p class="text-white text-opacity-85 small mb-0 lh-base" style="max-width: 680px;">
                        Koleksi E-Book Digital, Modul Pembelajaran Guru, Referensi Kejuruan, dan Media Video Pembelajaran Interaktif.
                    </p>
                </div>
            </div>

            <?php if ($canUpload): ?>
            <a href="<?= BASE_URL ?>index.php?url=library/upload" class="btn btn-warning text-dark fw-bold rounded-pill shadow-lg px-4 py-2.5 d-inline-flex align-items-center gap-2 hover-scale text-nowrap" style="font-size: 0.88rem;">
                <i class="bi bi-cloud-upload-fill fs-5"></i>
                <span>Upload Koleksi Baru</span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- 📊 KPI STATS SUMMARY BAR (4 COLUMNS) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="lib-kpi-card d-flex align-items-center gap-3">
                <div class="lib-kpi-icon bg-primary-subtle text-primary">
                    <i class="bi bi-bookshelf"></i>
                </div>
                <div class="overflow-hidden">
                    <h4 class="fw-extrabold text-dark mb-0 lh-1"><?= number_format($totalBooksCount) ?></h4>
                    <span class="text-muted fw-semibold" style="font-size:0.75rem;">Total Koleksi</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="lib-kpi-card d-flex align-items-center gap-3">
                <div class="lib-kpi-icon bg-danger-subtle text-danger">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                </div>
                <div class="overflow-hidden">
                    <h4 class="fw-extrabold text-dark mb-0 lh-1"><?= number_format($pdfCount) ?></h4>
                    <span class="text-muted fw-semibold" style="font-size:0.75rem;">E-Book PDF</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="lib-kpi-card d-flex align-items-center gap-3">
                <div class="lib-kpi-icon bg-indigo-subtle text-indigo" style="background-color: #e0e7ff; color: #4338ca;">
                    <i class="bi bi-play-circle-fill"></i>
                </div>
                <div class="overflow-hidden">
                    <h4 class="fw-extrabold text-dark mb-0 lh-1"><?= number_format($totalBooksCount - $pdfCount) ?></h4>
                    <span class="text-muted fw-semibold" style="font-size:0.75rem;">Video & Modul</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="lib-kpi-card d-flex align-items-center gap-3">
                <div class="lib-kpi-icon bg-success-subtle text-success">
                    <i class="bi bi-eye-fill"></i>
                </div>
                <div class="overflow-hidden">
                    <h4 class="fw-extrabold text-dark mb-0 lh-1"><?= number_format($totalViewsCount) ?>x</h4>
                    <span class="text-muted fw-semibold" style="font-size:0.75rem;">Total Dibaca</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 🎛️ SEARCH & FILTERS CONTAINER -->
    <div class="card border-0 rounded-4 shadow-sm p-3.5 mb-4 bg-white">
        <div class="row g-2.5 align-items-center mb-3">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control bg-light border-start-0 rounded-end-pill ps-0 text-dark" placeholder="Cari judul e-book, penulis..." style="font-size:0.85rem;">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select id="filterKategori" class="form-select rounded-pill text-dark fw-medium" style="font-size:0.83rem;">
                    <option value="">Semua Kategori</option>
                    <option value="Kejuruan">Kejuruan / Produktif</option>
                    <option value="Matematika">Matematika</option>
                    <option value="IPA">IPA &amp; Sains</option>
                    <option value="IPS">IPS &amp; Sejarah</option>
                    <option value="Bahasa">Bahasa (Indo/Inggris)</option>
                    <option value="Modul">Modul Pembelajaran Guru</option>
                    <option value="Referensi">Referensi Umum</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select id="filterTipe" class="form-select rounded-pill text-dark fw-medium" style="font-size:0.83rem;">
                    <option value="">Semua Format</option>
                    <option value="pdf">PDF E-Book</option>
                    <option value="docx">Word (DOCX)</option>
                    <option value="pptx">PowerPoint (PPTX)</option>
                    <option value="video">Video MP4</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="button" class="btn btn-outline-danger fw-bold rounded-pill w-100 py-2 d-flex align-items-center justify-content-center gap-1.5 shadow-xs" onclick="resetFilter()" title="Reset Semua Filter" style="font-size:0.82rem;">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
            </div>
        </div>

        <!-- Quick Category Pills Horizontal Strip -->
        <div class="d-flex align-items-center gap-2 overflow-x-auto pb-1" style="scrollbar-width: thin;">
            <button type="button" class="cat-pill-btn active" onclick="selectCategoryPill('', this)">Semua Koleksi</button>
            <button type="button" class="cat-pill-btn" onclick="selectCategoryPill('Kejuruan', this)"><i class="bi bi-laptop"></i>Kejuruan / Produktif</button>
            <button type="button" class="cat-pill-btn" onclick="selectCategoryPill('Matematika', this)"><i class="bi bi-calculator"></i>Matematika</button>
            <button type="button" class="cat-pill-btn" onclick="selectCategoryPill('IPA', this)"><i class="bi bi-virus"></i>IPA &amp; Sains</button>
            <button type="button" class="cat-pill-btn" onclick="selectCategoryPill('IPS', this)"><i class="bi bi-globe"></i>IPS &amp; Sejarah</button>
            <button type="button" class="cat-pill-btn" onclick="selectCategoryPill('Bahasa', this)"><i class="bi bi-translate"></i>Bahasa</button>
            <button type="button" class="cat-pill-btn" onclick="selectCategoryPill('Modul', this)"><i class="bi bi-journal-check"></i>Modul Guru</button>
        </div>
    </div>

    <!-- 📚 CATALOG COLLECTION GRID (RESPONSIVE 3-4 COLUMNS) -->
    <div class="row g-3" id="bookGrid">
        <?php if (empty($books)): ?>
            <div class="col-12">
                <div class="card border-0 rounded-4 shadow-sm p-5 text-center text-muted bg-white">
                    <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-journal-x fs-1"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Perpustakaan Masih Kosong</h6>
                    <p class="small text-muted mb-0">Belum ada koleksi yang diunggah. Guru dan Admin dapat menambahkan e-book dan modul pembelajaran.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($books as $book): ?>
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 book-item"
                 data-kategori="<?= htmlspecialchars($book['kategori']) ?>"
                 data-tipe="<?= htmlspecialchars($book['file_type'] ?? 'pdf') ?>"
                 data-judul="<?= strtolower(htmlspecialchars($book['judul'])) ?>">
                <div class="book-card-item">
                    <div>
                        <!-- 3D Live Cover Preview Graphic -->
                        <div class="position-relative">
                            <?php
                            $coverColors = ['#0d6efd','#10b981','#e11d48','#f59e0b','#8b5cf6','#06b6d4','#1e293b'];
                            $color = $coverColors[crc32($book['judul']) % count($coverColors)];
                            $fileExt = strtolower($book['file_type'] ?? 'pdf');
                            $tipeIcon = match($fileExt) {
                                'pdf' => 'bi-file-earmark-pdf-fill',
                                'docx','doc' => 'bi-file-earmark-word-fill',
                                'pptx','ppt' => 'bi-file-earmark-ppt-fill',
                                'video','mp4','mkv' => 'bi-play-circle-fill',
                                default => 'bi-file-earmark-text-fill'
                            };
                            $hasCustomCover = !empty($book['cover_path']) && file_exists(ROOT_PATH . $book['cover_path']);
                            $isPdfFile = ($fileExt === 'pdf') && file_exists(ROOT_PATH . $book['file_path']);
                            ?>
                            <div class="book-cover-aspect overflow-hidden position-relative bg-dark" style="height: 175px;">
                                <div class="book-ribbon-spine z-3"></div>

                                <?php if ($hasCustomCover): ?>
                                    <img src="<?= BASE_URL . htmlspecialchars($book['cover_path']) ?>" alt="Cover" class="w-100 h-100 object-fit-cover">
                                <?php elseif ($isPdfFile): ?>
                                    <!-- Live PDF 1st Page Thumbnail Embed -->
                                    <div class="w-100 h-100 position-relative overflow-hidden bg-white">
                                        <iframe src="<?= BASE_URL . htmlspecialchars($book['file_path']) ?>#page=1&toolbar=0&navpanes=0&scrollbar=0&view=FitH" 
                                                style="width: 140%; height: 280px; border: none; pointer-events: none; transform: scale(0.75); transform-origin: top left; opacity: 0.96;" 
                                                scrolling="no" 
                                                loading="lazy"></iframe>
                                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.02) 0%, rgba(0,0,0,0.15) 100%); pointer-events: none;"></div>
                                    </div>
                                <?php elseif (in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp', 'gif']) && file_exists(ROOT_PATH . $book['file_path'])): ?>
                                    <img src="<?= BASE_URL . htmlspecialchars($book['file_path']) ?>" alt="Preview" class="w-100 h-100 object-fit-cover">
                                <?php elseif (in_array($fileExt, ['video', 'mp4', 'mkv', 'avi']) && file_exists(ROOT_PATH . $book['file_path'])): ?>
                                    <div class="position-relative w-100 h-100 bg-dark d-flex align-items-center justify-content-center">
                                        <video src="<?= BASE_URL . htmlspecialchars($book['file_path']) ?>#t=1" preload="metadata" muted class="w-100 h-100 object-fit-cover" style="opacity: 0.8;"></video>
                                        <div class="position-absolute top-50 start-50 translate-middle text-white bg-dark bg-opacity-60 p-2 rounded-circle shadow-sm">
                                            <i class="bi bi-play-fill fs-3"></i>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Elegant Typography Cover Fallback -->
                                    <div class="w-100 h-100 text-white p-3 text-center d-flex flex-column align-items-center justify-content-center"
                                         style="background: linear-gradient(135deg, <?= $color ?> 0%, <?= $color ?>cc 100%);">
                                        <i class="bi <?= $tipeIcon ?> fs-1 mb-1 d-block text-white opacity-90 shadow-xs"></i>
                                        <div class="fw-bold text-truncate px-2 text-white" style="font-size:0.78rem; max-width: 160px; margin: 0 auto;"><?= htmlspecialchars($book['judul']) ?></div>
                                    </div>
                                <?php endif; ?>

                                <div class="position-absolute top-0 end-0 m-2 z-3">
                                    <span class="badge bg-dark bg-opacity-75 text-white rounded-pill px-2.5 py-1 fw-bold shadow-xs" style="font-size:0.65rem; backdrop-filter: blur(4px);"><?= strtoupper($fileExt) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Book Content Info -->
                        <div class="p-3">
                            <h6 class="fw-bold text-dark mb-1.5 text-clamp-2" style="font-size: 0.9rem; line-height: 1.35; min-height: 2.4em;" title="<?= htmlspecialchars($book['judul']) ?>">
                                <?= htmlspecialchars($book['judul']) ?>
                            </h6>
                            <div class="mb-2 text-muted" style="font-size:0.75rem;">
                                <span class="text-truncate d-block"><i class="bi bi-person-circle text-primary me-1"></i><?= htmlspecialchars($book['penulis'] ?: 'Tim Guru') ?></span>
                            </div>

                            <div class="d-flex gap-1 flex-wrap mb-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" style="font-size:.68rem;"><?= htmlspecialchars($book['kategori'] ?? 'Umum') ?></span>
                                <?php if (!empty($book['kelas_target'])): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size:.68rem;"><?= htmlspecialchars($book['kelas_target']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action Strip -->
                    <div class="p-3 pt-0 mt-auto">
                        <div class="d-flex align-items-center justify-content-between pt-2.5 border-top">
                            <div class="text-muted" style="font-size:.72rem;">
                                <i class="bi bi-eye text-primary me-0.5"></i><?= number_format($book['view_count'] ?? 0) ?>
                                &nbsp;&bull;&nbsp;
                                <i class="bi bi-download text-success me-0.5"></i><?= number_format($book['download_count'] ?? 0) ?>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <?php if ($isAdmin): ?>
                                    <a href="<?= BASE_URL ?>index.php?url=library/delete&id=<?= $book['id'] ?>&csrf_token=<?= Security::generateCsrfToken() ?>" 
                                       class="btn btn-xs btn-outline-danger rounded-circle p-1 d-inline-flex align-items-center justify-content-center" 
                                       style="width: 28px; height: 28px;"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus koleksi e-book ini?')"
                                       title="Hapus Koleksi">
                                        <i class="bi bi-trash-fill" style="font-size: 0.72rem;"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>index.php?url=library/view&id=<?= $book['id'] ?>"
                                   class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold text-white shadow-xs hover-scale" style="font-size:.75rem; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
</main>

<script>
// Filter functions
function filterBooks() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    const kategori = document.getElementById('filterKategori').value.toLowerCase();
    const tipe = document.getElementById('filterTipe').value.toLowerCase();

    document.querySelectorAll('.book-item').forEach(el => {
        const judul = el.dataset.judul;
        const k = el.dataset.kategori.toLowerCase();
        const t = el.dataset.tipe.toLowerCase();
        const match = (!q || judul.includes(q)) && (!kategori || k.includes(kategori)) && (!tipe || t.includes(tipe));
        el.style.display = match ? '' : 'none';
    });
}

function selectCategoryPill(catName, btnEl) {
    document.querySelectorAll('.cat-pill-btn').forEach(b => b.classList.remove('active'));
    btnEl.classList.add('active');
    document.getElementById('filterKategori').value = catName;
    filterBooks();
}

function resetFilter() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterKategori').value = '';
    document.getElementById('filterTipe').value = '';
    document.querySelectorAll('.cat-pill-btn').forEach(b => b.classList.remove('active'));
    if (document.querySelector('.cat-pill-btn')) {
        document.querySelector('.cat-pill-btn').classList.add('active');
    }
    filterBooks();
}

document.getElementById('searchInput').addEventListener('input', filterBooks);
document.getElementById('filterKategori').addEventListener('change', filterBooks);
document.getElementById('filterTipe').addEventListener('change', filterBooks);
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
