<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-compass-fill text-success me-2"></i>Learning Path & Urutan Pembelajaran Digital</h4>
            <p class="text-muted small mb-0">Kelola alur silabus KBM bertahap per-mata pelajaran yang Anda ampu secara terstruktur dari database.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>index.php?url=guru/materi" class="btn btn-success fw-bold shadow-sm">
                <i class="bi bi-cloud-upload me-1"></i> Upload Materi
            </a>
            <a href="<?= BASE_URL ?>index.php?url=guru/tugas" class="btn btn-primary fw-bold shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Buat Tugas
            </a>
        </div>
    </div>

    <!-- Edu Guide Alert Box -->
    <div class="card-custom p-4 mb-4 shadow-sm border-start border-4 border-success">
        <div class="d-flex align-items-start gap-3">
            <div class="bg-success-subtle text-success p-3 rounded-circle fs-3 d-flex align-items-center justify-content-center" style="width:50px; height:50px; flex-shrink:0;">
                <i class="bi bi-lightbulb-fill"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-dark">Panduan Alur Pembelajaran Bertahap (Step-by-Step Curriculum)</h6>
                <p class="small text-muted mb-2 leading-relaxed">
                    <strong>Learning Path</strong> adalah alur kurikulum digital yang membantu siswa belajar secara teratur dan berurutan. 
                    Siswa <strong>wajib menyelesaikan Langkah 1 (Modul Materi)</strong> sebelum dapat membuka modul tugas maupun kuis pada tahapan berikutnya.
                </p>
                <div class="d-flex gap-2 flex-wrap small">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5"><i class="bi bi-unlock-fill me-1"></i>Langkah 1: Modul Awal (Terbuka)</span>
                    <span class="text-muted align-self-center">➔</span>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-dark px-3 py-1.5"><i class="bi bi-lock-fill me-1"></i>Langkah 2: Tugas (Terkunci Prasyarat)</span>
                    <span class="text-muted align-self-center">➔</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5"><i class="bi bi-patch-check-fill me-1"></i>Langkah 3: Kuis & Evaluasi CBT</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Mata Pelajaran Card -->
    <div class="card-custom p-3.5 mb-4 shadow-sm border-start border-4 border-primary">
        <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-3 align-items-center">
            <input type="hidden" name="url" value="guru/learningPath">
            <div class="col-12 col-md-8 col-lg-6 d-flex align-items-center gap-2">
                <label class="form-label mb-0 fw-bold small text-nowrap"><i class="bi bi-journal-bookmark-fill text-primary me-1"></i>Pilih Mata Pelajaran Saya:</label>
                <select name="mapel_id" class="form-select fw-bold text-primary" onchange="this.form.submit()">
                    <?php if (empty($myMapelList)): ?>
                        <option value="">-- Belum Ada Mapel Pengampuan --</option>
                    <?php else: ?>
                        <?php foreach ($myMapelList as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= ($selectedMapelId == $m['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['nama_mapel']) ?> (<?= htmlspecialchars($m['kode_mapel'] ?? 'MP') ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-auto ms-auto">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-bold">
                    <i class="bi bi-check-circle-fill me-1"></i>Terhubung Hak Akses Guru Real
                </span>
            </div>
        </form>
    </div>

    <?php
    // Combine materi, tugas, quiz into unified real sequence items
    $sequenceItems = [];

    if (!empty($materiList)) {
        foreach ($materiList as $mat) {
            $sequenceItems[] = [
                'type' => 'materi',
                'title' => $mat['judul'],
                'desc' => $mat['deskripsi'] ?? 'Modul materi digital KBM.',
                'kelas' => $mat['nama_kelas'] ?? 'Semua Kelas',
                'created' => $mat['created_at'] ?? null,
                'file_path' => $mat['file_path'] ?? null,
                'raw' => $mat
            ];
        }
    }

    if (!empty($tugasList)) {
        foreach ($tugasList as $tug) {
            $sequenceItems[] = [
                'type' => 'tugas',
                'title' => 'Penugasan: ' . $tug['judul'],
                'desc' => $tug['deskripsi'] ?? 'Tugas praktikum / teori KBM.',
                'kelas' => $tug['nama_kelas'] ?? 'Semua Kelas',
                'created' => $tug['created_at'] ?? null,
                'deadline' => $tug['deadline'] ?? null,
                'raw' => $tug
            ];
        }
    }

    if (!empty($quizList)) {
        foreach ($quizList as $qz) {
            $sequenceItems[] = [
                'type' => 'quiz',
                'title' => 'Evaluasi CBT: ' . $qz['judul'],
                'desc' => 'Ujian / Kuis Online Berbasis CBT.',
                'kelas' => 'Rombel KBM',
                'created' => $qz['created_at'] ?? null,
                'raw' => $qz
            ];
        }
    }
    ?>

    <!-- Table Matriks Urutan Pembelajaran -->
    <div class="card-custom p-4 shadow-sm mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-3 border-bottom">
            <div>
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-list-ol text-success me-2"></i>Matriks Alur Pembelajaran: 
                    <span class="text-success"><?= htmlspecialchars($selectedMapelInfo['nama_mapel'] ?? 'Mata Pelajaran') ?></span>
                </h5>
                <small class="text-muted">Daftar <?= count($sequenceItems) ?> modul, tugas, dan kuis terurut yang Anda unggah untuk mata pelajaran ini.</small>
            </div>
            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                <i class="bi bi-layers-fill me-1 text-primary"></i>Total: <?= count($sequenceItems) ?> Tahapan
            </span>
        </div>

        <?php if (empty($sequenceItems)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
                <h6 class="fw-bold text-dark mb-1">Belum Ada Modul Materi atau Evaluasi pada Mapel Ini</h6>
                <p class="small text-muted mb-3">Unggah modul materi atau buat tugas baru untuk membentuk alur pembelajaran bertahap bagi siswa.</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="<?= BASE_URL ?>index.php?url=guru/materi" class="btn btn-sm btn-success fw-bold px-3">
                        <i class="bi bi-cloud-upload me-1"></i> Upload Materi
                    </a>
                    <a href="<?= BASE_URL ?>index.php?url=guru/tugas" class="btn btn-sm btn-outline-primary fw-bold px-3">
                        <i class="bi bi-file-earmark-plus me-1"></i> Buat Tugas
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px;" class="text-center">Tahap</th>
                            <th>Modul / Evaluasi Pembelajaran</th>
                            <th>Kategori</th>
                            <th>Rombel Kelas</th>
                            <th>Status Akses Siswa</th>
                            <th class="text-center" style="width:180px;">Aksi Kelola</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sequenceItems as $idx => $item): 
                            $stepNo = $idx + 1;
                            $isFirst = ($stepNo === 1);
                        ?>
                            <tr>
                                <td class="text-center">
                                    <span class="badge <?= $isFirst ? 'bg-success' : 'bg-secondary' ?> fs-6 rounded-circle p-2" style="width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center;">
                                        <?= $stepNo ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-6 mb-0"><?= htmlspecialchars($item['title']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($item['desc']) ?></small>
                                </td>
                                <td>
                                    <?php if ($item['type'] === 'materi'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-2.5 py-1.5">
                                            <i class="bi bi-book me-1"></i>Modul Materi
                                        </span>
                                    <?php elseif ($item['type'] === 'tugas'): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold px-2.5 py-1.5">
                                            <i class="bi bi-card-checklist me-1"></i>Penugasan KBM
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-dark fw-bold px-2.5 py-1.5">
                                            <i class="bi bi-patch-check me-1"></i>Kuis / CBT
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-medium px-2.5 py-1.5">
                                        <i class="bi bi-building me-1 text-secondary"></i><?= htmlspecialchars($item['kelas']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($isFirst): ?>
                                        <span class="badge bg-success px-3 py-1.5 fw-bold">
                                            <i class="bi bi-unlock-fill me-1"></i>Terbuka Awal
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border px-3 py-1.5 fw-semibold">
                                            <i class="bi bi-lock-fill text-warning me-1"></i>Prasyarat: Tahap <?= $stepNo - 1 ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($item['type'] === 'materi'): ?>
                                        <a href="<?= BASE_URL ?>index.php?url=guru/materi" class="btn btn-sm btn-outline-success fw-bold px-3">
                                            <i class="bi bi-pencil-square me-1"></i> Kelola
                                        </a>
                                    <?php elseif ($item['type'] === 'tugas'): ?>
                                        <a href="<?= BASE_URL ?>index.php?url=guru/tugas" class="btn btn-sm btn-outline-primary fw-bold px-3">
                                            <i class="bi bi-eye me-1"></i> Submission
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>index.php?url=guru/bankSoal" class="btn btn-sm btn-outline-warning text-dark fw-bold px-3">
                                            <i class="bi bi-gear me-1"></i> Kuis
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
