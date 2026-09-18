<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-book-fill text-primary me-2"></i>Monitoring Modul & Bahan Ajar Guru</h4>
                <p class="text-muted small mb-0">Pengawasan distribusi materi pembelajaran, modul PDF, dan video edukasi yang diunggah oleh tenaga pendidik.</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fs-6">
                    <i class="bi bi-eye-fill me-1"></i> Supervisi Bahan Ajar
                </span>
            </div>
        </div>

        <!-- Summary Stats -->
        <?php
        $totalMateri = count($materiList);
        $totalPdf = count(array_filter($materiList, fn($m) => !empty($m['file_path']) && preg_match('/\.pdf$/i', $m['file_path'])));
        $totalVideo = count(array_filter($materiList, fn($m) => !empty($m['video_url'])));
        ?>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-4">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-primary text-white text-center">
                    <div class="small fw-semibold text-uppercase opacity-75">Total Modul Materi</div>
                    <div class="display-6 fw-bold my-1"><?= $totalMateri ?> Modul</div>
                    <small>Bahan Ajar Terdistribusi</small>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-danger text-white text-center">
                    <div class="small fw-semibold text-uppercase opacity-75">Dokumen & Modul PDF</div>
                    <div class="display-6 fw-bold my-1"><?= $totalPdf ?> Dokumen</div>
                    <small>Siap Unduh & Baca</small>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-success text-white text-center">
                    <div class="small fw-semibold text-uppercase opacity-75">Video Pembelajaran</div>
                    <div class="display-6 fw-bold my-1"><?= $totalVideo ?> Video</div>
                    <small>Pembelajaran Interaktif</small>
                </div>
            </div>
        </div>

        <!-- Tabel Modul Materi -->
        <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-folder-fill text-warning me-2"></i>Katalog Bahan Ajar Seluruh Rombel</h6>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">Data Realtime</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle datatable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Judul Modul Materi</th>
                            <th>Mata Pelajaran & Guru Pengampu</th>
                            <th>Sasaran Kelas</th>
                            <th>Tipe Bahan Ajar</th>
                            <th>Tanggal Terbit</th>
                            <th class="text-center" style="width:100px;">Berkas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($materiList)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada modul materi yang diunggah.</td></tr>
                        <?php else: ?>
                            <?php foreach ($materiList as $i => $m): 
                                $hasFile = !empty($m['file_path']);
                                $hasVideo = !empty($m['video_url']);
                            ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($m['judul']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars(substr($m['deskripsi'] ?? '', 0, 60)) ?>...</small>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-primary"><?= htmlspecialchars($m['nama_mapel'] ?? '-') ?></div>
                                        <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($m['nama_guru'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars($m['nama_kelas'] ?? 'Semua Kelas') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($hasFile && $hasVideo): ?>
                                            <span class="badge bg-primary me-1"><i class="bi bi-file-earmark-pdf me-1"></i>Dokumen</span>
                                            <span class="badge bg-danger"><i class="bi bi-youtube me-1"></i>Video</span>
                                        <?php elseif ($hasFile): ?>
                                            <span class="badge bg-primary"><i class="bi bi-file-earmark-pdf me-1"></i>Dokumen / PDF</span>
                                        <?php elseif ($hasVideo): ?>
                                            <span class="badge bg-danger"><i class="bi bi-youtube me-1"></i>Video Learning</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Teks Pembelajaran</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= date('d M Y, H:i', strtotime($m['created_at'])) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($hasFile): ?>
                                            <a href="<?= BASE_URL . htmlspecialchars($m['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Buka Dokumen">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        <?php elseif ($hasVideo): ?>
                                            <a href="<?= htmlspecialchars($m['video_url']) ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="Putar Video">
                                                <i class="bi bi-play-circle"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
