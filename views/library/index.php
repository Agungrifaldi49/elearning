<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-book-fill text-success me-2"></i>Perpustakaan Digital</h4>
            <p class="text-muted small mb-0">Akses koleksi e-book, modul, dan referensi belajar digital kapan saja.</p>
        </div>
        <?php if (in_array(AuthHelper::user()['role_name'] ?? '', ['administrator', 'Guru'])): ?>
        <a href="<?= BASE_URL ?>index.php?url=library/upload" class="btn btn-success">
            <i class="bi bi-cloud-upload me-1"></i> Upload Koleksi Baru
        </a>
        <?php endif; ?>
    </div>

    <!-- Search & Filter -->
    <div class="card-custom p-4 mb-4">
        <div class="row g-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Cari judul, penulis, atau kategori…">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select id="filterKategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <option value="Matematika">Matematika</option>
                    <option value="IPA">IPA</option>
                    <option value="IPS">IPS</option>
                    <option value="Bahasa">Bahasa</option>
                    <option value="Kejuruan">Kejuruan</option>
                    <option value="Modul">Modul Guru</option>
                    <option value="Referensi">Referensi Umum</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select id="filterTipe" class="form-select">
                    <option value="">Semua Tipe</option>
                    <option value="pdf">PDF</option>
                    <option value="docx">Word</option>
                    <option value="pptx">PowerPoint</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <div class="col-12 col-md-1">
                <button class="btn btn-outline-secondary w-100" onclick="resetFilter()">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Book Grid -->
    <div class="row g-3" id="bookGrid">
        <?php if (empty($books)): ?>
            <div class="col-12">
                <div class="card-custom p-5 text-center text-muted">
                    <i class="bi bi-bookshelf fs-1 d-block mb-3 text-secondary"></i>
                    <h6>Perpustakaan Masih Kosong</h6>
                    <p class="small mb-0">Belum ada koleksi yang diupload. Guru atau Admin dapat mengupload e-book dan modul.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($books as $book): ?>
            <div class="col-6 col-md-4 col-lg-3 book-item"
                 data-kategori="<?= htmlspecialchars($book['kategori']) ?>"
                 data-tipe="<?= htmlspecialchars($book['file_type'] ?? 'pdf') ?>"
                 data-judul="<?= strtolower(htmlspecialchars($book['judul'])) ?>">
                <div class="card-custom book-card h-100">
                    <!-- Cover -->
                    <div class="position-relative">
                        <?php
                        $coverColors = ['#0D6EFD','#198754','#DC3545','#FD7E14','#6610F2','#0DCAF0'];
                        $color = $coverColors[crc32($book['judul']) % count($coverColors)];
                        $tipeIcon = match(strtolower($book['file_type'] ?? 'pdf')) {
                            'pdf' => 'bi-file-earmark-pdf-fill',
                            'docx','doc' => 'bi-file-earmark-word-fill',
                            'pptx','ppt' => 'bi-file-earmark-ppt-fill',
                            'video','mp4' => 'bi-play-circle-fill',
                            default => 'bi-file-earmark-fill'
                        };
                        ?>
                        <div class="book-cover d-flex align-items-center justify-content-center text-white"
                             style="background: linear-gradient(135deg, <?= $color ?> 0%, <?= $color ?>99 100%);">
                            <div class="text-center px-3">
                                <i class="bi <?= $tipeIcon ?> fs-1 mb-2 d-block"></i>
                                <div class="fw-bold small"><?= htmlspecialchars(substr($book['judul'], 0, 30)) ?><?= strlen($book['judul']) > 30 ? '...' : '' ?></div>
                            </div>
                        </div>
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-dark bg-opacity-75"><?= strtoupper($book['file_type'] ?? 'PDF') ?></span>
                        </div>
                    </div>

                    <div class="p-3">
                        <h6 class="fw-bold mb-1 small"><?= htmlspecialchars($book['judul']) ?></h6>
                        <p class="text-muted mb-2" style="font-size:.75rem; line-height:1.3;"><?= htmlspecialchars($book['deskripsi'] ?? '') ?></p>

                        <div class="d-flex gap-1 flex-wrap mb-2">
                            <span class="badge bg-primary-subtle text-primary" style="font-size:.65rem;"><?= htmlspecialchars($book['kategori'] ?? 'Umum') ?></span>
                            <?php if (!empty($book['kelas_target'])): ?>
                                <span class="badge bg-success-subtle text-success" style="font-size:.65rem;"><?= htmlspecialchars($book['kelas_target']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-muted" style="font-size:.72rem;">
                                <i class="bi bi-eye me-1"></i><?= number_format($book['view_count'] ?? 0) ?> lihat
                                &nbsp;|&nbsp;
                                <i class="bi bi-download me-1"></i><?= number_format($book['download_count'] ?? 0) ?>
                            </div>
                            <a href="<?= BASE_URL ?>index.php?url=library/view&id=<?= $book['id'] ?>"
                               class="btn btn-sm btn-primary py-1 px-2" style="font-size:.75rem;">
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
// Filter functionality
function filterBooks() {
    const q = document.getElementById('searchInput').value.toLowerCase();
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
