<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-compass-fill text-success me-2"></i>Learning Path & Progression Map</h4>
            <p class="text-muted small mb-0">Peta alur kurikulum digital. Pelajari modul materi dan evaluasi secara bertahap untuk membuka modul berikutnya.</p>
        </div>
        <a href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" class="btn btn-warning text-dark fw-bold shadow-sm">
            <i class="bi bi-key-fill me-1"></i> Input Key Mapel Baru
        </a>
    </div>

    <!-- Edu Guide Alert Box -->
    <div class="card-custom p-4 mb-4 shadow-sm border-start border-4 border-success">
        <div class="d-flex align-items-start gap-3">
            <div class="bg-success-subtle text-success p-3 rounded-circle fs-3 d-flex align-items-center justify-content-center" style="width:50px; height:50px; flex-shrink:0;">
                <i class="bi bi-lightbulb-fill"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-dark">Panduan Belajar Bertahap (Step-by-Step Learning Path)</h6>
                <p class="small text-muted mb-2 leading-relaxed">
                    Sistem <strong>Learning Path</strong> dirancang oleh Guru agar Anda belajar secara terstruktur. 
                    Anda <strong>wajib membaca dan menyelesaikan Langkah 1 (Modul Materi)</strong> terlebih dahulu sebelum dapat membuka modul tugas maupun kuis pada tahapan berikutnya.
                </p>
                <div class="d-flex gap-2 flex-wrap small">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5"><i class="bi bi-unlock-fill me-1"></i>Langkah 1: Modul Materi (Terbuka)</span>
                    <span class="text-muted align-self-center">➔</span>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-dark px-3 py-1.5"><i class="bi bi-lock-fill me-1"></i>Langkah 2: Tugas (Prasyarat Langkah 1)</span>
                    <span class="text-muted align-self-center">➔</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5"><i class="bi bi-patch-check-fill me-1"></i>Langkah 3: Kuis CBT Final</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Mata Pelajaran Card -->
    <div class="card-custom p-3.5 mb-4 shadow-sm border-start border-4 border-primary">
        <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-3 align-items-center">
            <input type="hidden" name="url" value="siswa/learningPath">
            <div class="col-12 col-md-8 col-lg-6 d-flex align-items-center gap-2">
                <label class="form-label mb-0 fw-bold small text-nowrap"><i class="bi bi-journal-bookmark-fill text-primary me-1"></i>Pilih Mata Pelajaran Saya:</label>
                <select name="mapel_id" class="form-select fw-bold text-primary" onchange="this.form.submit()">
                    <?php if (empty($myMapelList)): ?>
                        <option value="">-- Belum Ada Mapel Terdaftar --</option>
                    <?php else: ?>
                        <?php foreach ($myMapelList as $m): 
                            $mId = $m['mapel_id'] ?? ($m['id'] ?? null);
                            $mNama = $m['nama_mapel'] ?? 'Mata Pelajaran';
                        ?>
                            <option value="<?= $mId ?>" <?= ($selectedMapelId == $mId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mNama) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-auto ms-auto">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-bold">
                    <i class="bi bi-check-circle-fill me-1"></i>Siswa Rombel Real Database
                </span>
            </div>
        </form>
    </div>

    <?php
    // Combine materi, tugas, quiz into unified real sequence items for Siswa
    $sequenceItems = [];

    if (!empty($materiList)) {
        foreach ($materiList as $mat) {
            $sequenceItems[] = [
                'type' => 'materi',
                'title' => $mat['judul'],
                'desc' => $mat['deskripsi'] ?? 'Modul materi digital KBM.',
                'guru' => $mat['nama_guru'] ?? 'Guru Pengampu',
                'mapel_id' => $mat['mapel_id'],
                'guru_id' => $mat['guru_id'] ?? 0,
                'created' => $mat['created_at'] ?? null,
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
                'guru' => $tug['nama_guru'] ?? 'Guru Pengampu',
                'mapel_id' => $tug['mapel_id'],
                'guru_id' => $tug['guru_id'] ?? 0,
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
                'guru' => $qz['nama_guru'] ?? 'Guru Pengampu',
                'mapel_id' => $qz['mapel_id'] ?? 0,
                'guru_id' => $qz['guru_id'] ?? 0,
                'created' => $qz['created_at'] ?? null,
                'raw' => $qz
            ];
        }
    }
    ?>

    <!-- Table Matriks Urutan Pembelajaran Siswa -->
    <div class="card-custom p-4 shadow-sm mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-3 border-bottom">
            <div>
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-list-ol text-success me-2"></i>Matriks Pembelajaran: 
                    <span class="text-success"><?= htmlspecialchars($selectedMapelInfo['nama_mapel'] ?? 'Mata Pelajaran') ?></span>
                </h5>
                <small class="text-muted">Total <?= count($sequenceItems) ?> modul materi & evaluasi terurut dari database.</small>
            </div>
            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                <i class="bi bi-layers-fill me-1 text-primary"></i>Total: <?= count($sequenceItems) ?> Tahapan
            </span>
        </div>

        <?php if (empty($sequenceItems)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
                <h6 class="fw-bold text-dark mb-1">Belum Ada Modul Materi atau Evaluasi pada Mapel Ini</h6>
                <p class="small text-muted mb-3">Guru belum mengunggah materi atau tugas pada mata pelajaran ini.</p>
                <a href="<?= BASE_URL ?>index.php?url=siswa/materi" class="btn btn-sm btn-primary fw-bold px-3">
                    <i class="bi bi-book me-1"></i> Lihat Seluruh Materi Saya
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px;" class="text-center">Tahap</th>
                            <th>Modul / Evaluasi Pembelajaran</th>
                            <th>Kategori</th>
                            <th>Guru Pengampu</th>
                            <th>Status Akses Siswa</th>
                            <th class="text-center" style="width:180px;">Aksi Belajar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sequenceItems as $idx => $item): 
                            $stepNo = $idx + 1;
                            $isFirst = ($stepNo === 1);

                            // Strict enrollment check
                            $isEnrolled = isset($enrolledMapels[$item['mapel_id'] . '_' . $item['guru_id']]) || isset($enrolledMapels[$item['mapel_id']]);
                        ?>
                            <tr>
                                <td class="text-center">
                                    <span class="badge <?= $isFirst ? 'bg-success' : ($isEnrolled ? 'bg-primary' : 'bg-secondary') ?> fs-6 rounded-circle p-2" style="width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center;">
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
                                    <span class="fw-semibold text-secondary small">
                                        <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($item['guru']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!$isEnrolled): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 fw-bold">
                                            <i class="bi bi-lock-fill me-1"></i>Terkunci (Butuh Key Mapel)
                                        </span>
                                    <?php elseif ($isFirst): ?>
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
                                    <?php if (!$isEnrolled): ?>
                                        <a href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" class="btn btn-sm btn-warning text-dark fw-bold px-3">
                                            <i class="bi bi-key-fill me-1"></i> Input Key
                                        </a>
                                    <?php elseif ($item['type'] === 'materi'): ?>
                                        <a href="<?= BASE_URL ?>index.php?url=siswa/materi" class="btn btn-sm btn-success fw-bold px-3">
                                            <i class="bi bi-book-half me-1"></i> Buka Materi
                                        </a>
                                    <?php elseif ($item['type'] === 'tugas'): ?>
                                        <a href="<?= BASE_URL ?>index.php?url=siswa/tugas" class="btn btn-sm btn-primary fw-bold px-3">
                                            <i class="bi bi-upload me-1"></i> Kirim Tugas
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>index.php?url=siswa/quiz" class="btn btn-sm btn-warning text-dark fw-bold px-3">
                                            <i class="bi bi-play-circle me-1"></i> Ikuti Kuis
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
