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
        if (in_array($fType, ['video','mp4','mkv'])) $videoCount++;
        $totalViewsCount += (int)($bItem['view_count'] ?? 0);
    }
}
?>

<style>
/* Modern LMS Digital Library Architecture */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.library-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
    padding-top: 28px !important;
}

/* Glassmorphic Hero Banner */
.library-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #2563eb 100%);
    border-radius: 20px;
    box-shadow: 0 12px 30px -5px rgba(37, 99, 235, 0.25);
    position: relative;
    overflow: hidden;
}

.library-hero-banner::after {
    content: '';
    position: absolute;
    top: -40%;
    right: -15%;
    width: 360px;
    height: 360px;
    background: radial-gradient(circle, rgba(96, 165, 250, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}

/* KPI Summary Cards */
.lib-kpi-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #ffffff;
    transition: all 0.2s ease;
}
.lib-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
}

/* 3D Glassmorphic Book Card */
.book-card-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    position: relative;
}
.book-card-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 32px -5px rgba(15, 23, 42, 0.12) !important;
    border-color: #cbd5e1;
}

.book-cover-aspect {
    height: 165px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: inset -4px 0 12px rgba(0, 0, 0, 0.2);
}

/* Book Ribbon Accent */
.book-ribbon-spine {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 10px;
    background: rgba(0, 0, 0, 0.25);
}

.book-title-clamp {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
    font-size: 0.93rem;
    line-height: 1.35;
}

/* Category Filter Pills Bar */
.cat-pill-btn {
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    font-weight: 600;
    font-size: 0.8rem;
    border-radius: 30px;
    padding: 6px 16px;
    transition: all 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
}
.cat-pill-btn:hover, .cat-pill-btn.active {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}

/* Responsive Overrides */
@media (max-width: 575.98px) {
    .library-hero-banner {
        padding: 1.25rem !important;
        border-radius: 16px !important;
    }
    .book-cover-aspect {
        height: 135px;
    }
    .book-cover-aspect .fs-1 {
        font-size: 1.75rem !important;
    }
    .lib-kpi-card {
        padding: 0.65rem !important;
    }
    .lib-kpi-card .fs-4 {
        font-size: 1.15rem !important;
    }
}
</style>

<main class="main-content px-2 px-sm-3 px-md-4 py-3 library-wrapper">
<div class="container-fluid pt-3">

    <!-- Hero Banner Header -->
    <div class="library-hero-banner text-white p-4 p-md-5 mb-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-3 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-gradient p-3.5 rounded-4 text-white shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 58px; height: 58px; background: #2563eb;">
                    <i class="bi bi-book-fill fs-2"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-white mb-1" style="letter-spacing: -0.4px;">Perpustakaan Digital SMK Muthia Harapan</h3>
                    <p class="text-blue-100 small mb-0 fw-medium">Pusat Koleksi E-Book, Modul KBM Guru, Referensi Kejuruan, dan Media Pembelajaran Digital.</p>
                </div>
            </div>

            <?php if (in_array(AuthHelper::user()['role_name'] ?? '', ['administrator', 'Guru'])): ?>
            <a href="<?= BASE_URL ?>index.php?url=library/upload" class="btn btn-warning text-dark fw-bold rounded-pill shadow-sm px-4 py-2.5 text-nowrap" style="font-size: 0.88rem; width: fit-content; max-width: 100%;">
                <i class="bi bi-cloud-upload-fill me-1.5"></i> Upload Koleksi Baru
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- KPI Stats Summary Bar -->
    <div class="row g-2.5 mb-4">
        <div class="col-6 col-md-3">
            <div class="lib-kpi-card p-3 d-flex align-items-center gap-2.5">
                <div class="bg-primary bg-opacity-10 text-primary p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-bookshelf fs-4"></i>
                </div>
                <div>
                    <small class="text-muted d-block" style="font-size:0.72rem;">Total Koleksi</small>
                    <strong class="text-dark fs-5 mb-0 fw-extrabold"><?= number_format($totalBooksCount) ?></strong>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="lib-kpi-card p-3 d-flex align-items-center gap-2.5">
                <div class="bg-danger bg-opacity-10 text-danger p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-file-earmark-pdf-fill fs-4"></i>
                </div>
                <div>
                    <small class="text-muted d-block" style="font-size:0.72rem;">E-Book PDF</small>
                    <strong class="text-dark fs-5 mb-0 fw-extrabold"><?= number_format($pdfCount) ?></strong>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="lib-kpi-card p-3 d-flex align-items-center gap-2.5">
                <div class="bg-info bg-opacity-10 text-info p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-play-circle-fill fs-4"></i>
                </div>
                <div>
                    <small class="text-muted d-block" style="font-size:0.72rem;">Video &amp; Modul</small>
                    <strong class="text-dark fs-5 mb-0 fw-extrabold"><?= number_format($totalBooksCount - $pdfCount) ?></strong>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="lib-kpi-card p-3 d-flex align-items-center gap-2.5">
                <div class="bg-success bg-opacity-10 text-success p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-eye-fill fs-4"></i>
                </div>
                <div>
                    <small class="text-muted d-block" style="font-size:0.72rem;">Total Dibaca</small>
                    <strong class="text-dark fs-5 mb-0 fw-extrabold"><?= number_format($totalViewsCount) ?>x</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filters Container -->
    <div class="card border-0 rounded-4 shadow-sm p-3.5 mb-4 bg-white">
        <div class="row g-2.5 align-items-center mb-3">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control bg-light border-start-0 rounded-end-pill ps-0" placeholder="Cari judul e-book, penulis..." style="font-size:0.85rem;">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select id="filterKategori" class="form-select rounded-pill fw-bold text-dark" style="font-size:0.83rem;">
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
                <select id="filterTipe" class="form-select rounded-pill fw-bold text-dark" style="font-size:0.83rem;">
                    <option value="">Semua Format</option>
                    <option value="pdf">PDF E-Book</option>
                    <option value="docx">Word (DOCX)</option>
                    <option value="pptx">PowerPoint (PPTX)</option>
                    <option value="video">Video MP4</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="button" class="btn btn-outline-danger fw-bold rounded-pill w-100 py-2 d-flex align-items-center justify-content-center gap-1.5 shadow-xs" onclick="resetFilter()" title="Reset Semua Filter" style="font-size:0.82rem;">
                    <i class="bi bi-arrow-counterclockwise fs-6"></i> Reset Filter
                </button>
            </div>
        </div>

        <!-- Quick Category Pills Horizontal Strip -->
        <div class="d-flex align-items-center gap-2 overflow-x-auto pb-1" style="-webkit-overflow-scrolling: touch;">
            <button type="button" class="cat-pill-btn active" onclick="selectCategoryPill('', this)">Semua Koleksi</button>
            <button type="button" class="cat-pill-btn" onclick="selectCategoryPill('Kejuruan', this)"><i class="bi bi-laptop me-1"></i>Kejuruan / Produktif</button>
            <button type="button" class="cat-pill-btn" onclick="selectCategoryPill('Matematika', this)"><i class="bi bi-calculator me-1"></i>Matematika</button>
            <button type="button" class="cat-pill-btn" onclick="selectCategoryPill('IPA', this)"><i class="bi bi-virus me-1"></i>IPA &amp; Sains</button>
            <button type="button" class="cat-pill-btn" onclick="selectCategoryPill('IPS', this)"><i class="bi bi-globe me-1"></i>IPS &amp; Sejarah</button>
            <button type="button" class="cat-pill-btn" onclick="selectCategoryPill('Bahasa', this)"><i class="bi bi-translate me-1"></i>Bahasa</button>
            <button type="button" class="cat-pill-btn" onclick="selectCategoryPill('Modul', this)"><i class="bi bi-journal-check me-1"></i>Modul Guru</button>
        </div>
    </div>

    <!-- Catalog Collection Grid -->
    <div class="row g-3" id="bookGrid">
        <?php if (empty($books)): ?>
            <div class="col-12">
                <div class="card border-0 rounded-4 shadow-sm p-5 text-center text-muted bg-white">
                    <div class="bg-slate-100 text-slate-400 rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px; background-color: #f1f5f9;">
                        <i class="bi bi-bookshelf fs-1 text-secondary"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Perpustakaan Masih Kosong</h6>
                    <p class="small text-muted mb-0">Belum ada koleksi yang diunggah. Guru dan Admin dapat menambahkan e-book dan modul pembelajaran.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($books as $book): ?>
            <div class="col-6 col-md-4 col-lg-3 book-item"
                 data-kategori="<?= htmlspecialchars($book['kategori']) ?>"
                 data-tipe="<?= htmlspecialchars($book['file_type'] ?? 'pdf') ?>"
                 data-judul="<?= strtolower(htmlspecialchars($book['judul'])) ?>">
                <div class="book-card-item h-100 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Cover Header Graphic -->
                        <div class="position-relative">
                            <?php
                            $coverColors = ['#0d6efd','#10b981','#ef4444','#f59e0b','#8b5cf6','#06b6d4'];
                            $color = $coverColors[crc32($book['judul']) % count($coverColors)];
                            $fileExt = strtolower($book['file_type'] ?? 'pdf');
                            $tipeIcon = match($fileExt) {
                                'pdf' => 'bi-file-earmark-pdf-fill',
                                'docx','doc' => 'bi-file-earmark-word-fill',
                                'pptx','ppt' => 'bi-file-earmark-ppt-fill',
                                'video','mp4','mkv' => 'bi-play-circle-fill',
                                default => 'bi-file-earmark-text-fill'
                            };
                            ?>
                            <div class="book-cover-aspect text-white p-3 text-center"
                                 style="background: linear-gradient(135deg, <?= $color ?> 0%, <?= $color ?>cc 100%);">
                                <div class="book-ribbon-spine"></div>
                                <div class="position-relative z-1">
                                    <i class="bi <?= $tipeIcon ?> fs-1 mb-1 d-block shadow-xs"></i>
                                    <div class="fw-bold text-truncate px-2" style="font-size:0.78rem; max-width: 150px; margin: 0 auto; opacity: 0.95;"><?= htmlspecialchars($book['judul']) ?></div>
                                </div>
                            </div>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-dark bg-opacity-75 rounded-pill px-2.5 py-1" style="font-size:0.65rem;"><?= strtoupper($fileExt) ?></span>
                            </div>
                        </div>

                        <!-- Book Content Info -->
                        <div class="p-3">
                            <h6 class="fw-bold text-dark mb-1.5 book-title-clamp" title="<?= htmlspecialchars($book['judul']) ?>">
                                <?= htmlspecialchars($book['judul']) ?>
                            </h6>
                            <div class="mb-2" style="font-size:0.72rem;">
                                <span class="text-muted"><i class="bi bi-person-fill text-primary me-1"></i><?= htmlspecialchars($book['penulis'] ?: 'Tim Guru') ?></span>
                            </div>

                            <div class="d-flex gap-1 flex-wrap mb-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" style="font-size:.65rem;"><?= htmlspecialchars($book['kategori'] ?? 'Umum') ?></span>
                                <?php if (!empty($book['kelas_target'])): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size:.65rem;"><?= htmlspecialchars($book['kelas_target']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action Strip -->
                    <div class="p-3 pt-0">
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <div class="text-muted" style="font-size:.7rem;">
                                <i class="bi bi-eye text-primary me-0.5"></i><?= number_format($book['view_count'] ?? 0) ?>
                                &nbsp;&bull;&nbsp;
                                <i class="bi bi-download text-success me-0.5"></i><?= number_format($book['download_count'] ?? 0) ?>
                            </div>
                            <a href="<?= BASE_URL ?>index.php?url=library/view&id=<?= $book['id'] ?>"
                               class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold text-white shadow-xs" style="font-size:.75rem; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Buka
                            </a>
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
