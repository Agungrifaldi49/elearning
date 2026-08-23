<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* Modern Digital Library Architecture */
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

/* Book Cover Styling */
.book-card-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}
.book-card-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px -5px rgba(15, 23, 42, 0.09) !important;
    border-color: #cbd5e1;
}

.book-cover-aspect {
    height: 160px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.book-title-clamp {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
    font-size: 0.92rem;
    line-height: 1.35;
}

/* Responsive Overrides */
@media (max-width: 575.98px) {
    .library-hero-banner {
        padding: 1.25rem !important;
        border-radius: 16px !important;
    }
    .book-cover-aspect {
        height: 130px;
    }
    .book-cover-aspect .fs-1 {
        font-size: 1.8rem !important;
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
                    <h3 class="fw-bold text-white mb-1" style="letter-spacing: -0.4px;">Perpustakaan Digital</h3>
                    <p class="text-blue-100 small mb-0 fw-medium">Koleksi E-Book, Modul KBM Guru, Referensi Kejuruan, dan Video Pembelajaran Digital.</p>
                </div>
            </div>

            <?php if (in_array(AuthHelper::user()['role_name'] ?? '', ['administrator', 'Guru'])): ?>
            <a href="<?= BASE_URL ?>index.php?url=library/upload" class="btn btn-warning text-dark fw-bold rounded-pill shadow-sm px-4 py-2.5 text-nowrap" style="font-size: 0.88rem; width: fit-content; max-width: 100%;">
                <i class="bi bi-cloud-upload-fill me-1.5"></i> Upload Koleksi Baru
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border-0 rounded-4 shadow-sm p-3.5 mb-4 bg-white">
        <div class="row g-2.5 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control bg-light border-start-0 rounded-end-pill ps-0" placeholder="Cari judul e-book, penulis, atau kata kunci..." style="font-size:0.88rem;">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select id="filterKategori" class="form-select rounded-pill fw-bold text-dark" style="font-size:0.85rem;">
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
                <select id="filterTipe" class="form-select rounded-pill fw-bold text-dark" style="font-size:0.85rem;">
                    <option value="">Semua Format</option>
                    <option value="pdf">PDF E-Book</option>
                    <option value="docx">Word (DOCX)</option>
                    <option value="pptx">PowerPoint (PPTX)</option>
                    <option value="video">Video Pembelajaran</option>
                </select>
            </div>
            <div class="col-12 col-md-1 text-end">
                <button class="btn btn-outline-secondary rounded-circle p-2 w-100" onclick="resetFilter()" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise fs-5"></i>
                </button>
            </div>
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
                                <div class="position-relative z-1">
                                    <i class="bi <?= $tipeIcon ?> fs-1 mb-1 d-block shadow-xs"></i>
                                    <div class="fw-bold text-truncate" style="font-size:0.78rem; max-width: 140px; margin: 0 auto; opacity: 0.95;"><?= htmlspecialchars($book['judul']) ?></div>
                                </div>
                            </div>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-dark bg-opacity-75 rounded-pill px-2.5 py-1" style="font-size:0.65rem;"><?= strtoupper($fileExt) ?></span>
                            </div>
                        </div>

                        <!-- Book Content Info -->
                        <div class="p-3">
                            <h6 class="fw-bold text-dark mb-1 book-title-clamp" title="<?= htmlspecialchars($book['judul']) ?>">
                                <?= htmlspecialchars($book['judul']) ?>
                            </h6>
                            <small class="text-muted d-block mb-2 font-monospace" style="font-size:0.71rem;">
                                <i class="bi bi-person-circle text-primary me-1"></i><?= htmlspecialchars($book['penulis'] ?: 'Tim Penyusun') ?>
                            </small>

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
                                <i class="bi bi-eye me-0.5 text-primary"></i><?= number_format($book['view_count'] ?? 0) ?>
                                &nbsp;&bull;&nbsp;
                                <i class="bi bi-download me-0.5 text-success"></i><?= number_format($book['download_count'] ?? 0) ?>
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
// Real-time catalog filtering
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

function resetFilter() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterKategori').value = '';
    document.getElementById('filterTipe').value = '';
    filterBooks();
}

document.getElementById('searchInput').addEventListener('input', filterBooks);
document.getElementById('filterKategori').addEventListener('change', filterBooks);
document.getElementById('filterTipe').addEventListener('change', filterBooks);
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
