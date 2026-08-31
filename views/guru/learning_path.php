<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* Modern LMS Teacher Learning Path Architecture */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.learning-path-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

/* Glassmorphic Hero Banner */
.learning-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #064e3b 50%, #059669 100%);
    border-radius: 20px;
    box-shadow: 0 12px 30px -5px rgba(6, 78, 59, 0.25);
    position: relative;
    overflow: hidden;
}

.learning-hero-banner::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(52, 211, 153, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}

/* Step Flow Cards */
.step-flow-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    transition: all 0.2s ease;
}
.step-flow-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
}

/* Responsive Overrides */
@media (max-width: 575.98px) {
    .learning-hero-banner {
        padding: 1.25rem !important;
        border-radius: 16px !important;
    }
}
</style>

<main class="main-content px-2 px-sm-3 px-md-4 py-3 learning-path-wrapper">
<div class="container-fluid pt-3">

    <!-- Hero Banner Header -->
    <div class="learning-hero-banner text-white p-4 p-md-5 mb-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-3 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-emerald-500 bg-gradient p-3.5 rounded-4 text-white shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 58px; height: 58px; background: #10b981;">
                    <i class="bi bi-compass-fill fs-2"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-white mb-1" style="letter-spacing: -0.4px;">Learning Path &amp; Alur Silabus KBM</h3>
                    <p class="text-emerald-100 small mb-0 fw-medium">Kelola alur kurikulum digital bertahap per-mata pelajaran yang Anda ampu secara terstruktur.</p>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= BASE_URL ?>index.php?url=guru/materi" class="btn btn-warning text-dark fw-bold rounded-pill shadow-sm px-3.5 py-2.5" style="font-size: 0.85rem;">
                    <i class="bi bi-cloud-upload-fill me-1"></i> Upload Materi
                </a>
                <a href="<?= BASE_URL ?>index.php?url=guru/tugas" class="btn btn-light fw-bold rounded-pill shadow-sm px-3.5 py-2.5 text-primary" style="font-size: 0.85rem;">
                    <i class="bi bi-plus-circle-fill me-1"></i> Buat Tugas
                </a>
            </div>
        </div>
    </div>

    <!-- Step Progression Guide Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="step-flow-card p-3 d-flex align-items-center gap-3 border-start border-4 border-success">
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px; height:48px;">
                    <i class="bi bi-book-half fs-4"></i>
                </div>
                <div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 fw-bold small mb-1">Langkah 1</span>
                    <h6 class="fw-bold text-dark mb-0 fs-6">Modul Materi Digital</h6>
                    <small class="text-muted" style="font-size:0.75rem;">Materi awal KBM (Terbuka)</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="step-flow-card p-3 d-flex align-items-center gap-3 border-start border-4 border-primary">
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px; height:48px;">
                    <i class="bi bi-card-checklist fs-4"></i>
                </div>
                <div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-0.5 fw-bold small mb-1">Langkah 2</span>
                    <h6 class="fw-bold text-dark mb-0 fs-6">Penugasan KBM</h6>
                    <small class="text-muted" style="font-size:0.75rem;">Prasyarat Langkah 1</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="step-flow-card p-3 d-flex align-items-center gap-3 border-start border-4 border-warning">
                <div class="bg-warning bg-opacity-15 text-warning-emphasis p-3 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px; height:48px;">
                    <i class="bi bi-patch-check-fill fs-4"></i>
                </div>
                <div>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-0.5 fw-bold small mb-1">Langkah 3</span>
                    <h6 class="fw-bold text-dark mb-0 fs-6">Evaluasi Kuis CBT</h6>
                    <small class="text-muted" style="font-size:0.75rem;">Ujian Online Akhir</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Mata Pelajaran Pengampuan -->
    <div class="card border-0 rounded-4 shadow-sm p-3.5 mb-4 bg-white">
        <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-3 align-items-center">
            <input type="hidden" name="url" value="guru/learningPath">
            <div class="col-12 col-md-8 col-lg-7 d-flex align-items-center gap-2">
                <label class="form-label mb-0 fw-bold small text-nowrap"><i class="bi bi-journal-bookmark-fill text-success me-1.5 fs-6"></i>Pilih Mata Pelajaran Pengampuan:</label>
                <select name="mapel_id" class="form-select rounded-pill fw-bold text-dark" onchange="this.form.submit()" style="font-size: 0.88rem;">
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
            <div class="col-12 col-md-4 col-lg-5 text-md-end">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold" style="font-size:0.8rem;">
                    <i class="bi bi-check-circle-fill me-1"></i>Hak Akses Guru Pengampu Real
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

    <!-- Learning Sequence Matrix Card -->
    <div class="card border-0 rounded-4 shadow-sm p-3.5 p-md-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-3 border-bottom">
            <div>
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-layers-fill text-success me-2"></i>Matriks Alur Pembelajaran: 
                    <span class="text-success"><?= htmlspecialchars($selectedMapelInfo['nama_mapel'] ?? 'Mata Pelajaran') ?></span>
                </h5>
                <small class="text-muted">Daftar <?= count($sequenceItems) ?> modul, tugas, dan kuis terurut yang Anda unggah untuk mata pelajaran ini.</small>
            </div>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold" style="font-size:0.8rem;">
                <i class="bi bi-stack me-1 text-primary"></i>Total: <?= count($sequenceItems) ?> Tahapan
            </span>
        </div>

        <?php if (empty($sequenceItems)): ?>
            <div class="text-center py-5 text-muted">
                <div class="bg-slate-100 text-slate-400 rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px; background-color: #f1f5f9;">
                    <i class="bi bi-journal-x fs-1 text-secondary"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Belum Ada Modul Materi atau Evaluasi pada Mapel Ini</h6>
                <p class="small text-muted mb-3">Unggah modul materi atau buat tugas baru untuk membentuk alur pembelajaran bertahap bagi siswa.</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="<?= BASE_URL ?>index.php?url=guru/materi" class="btn btn-sm btn-success rounded-pill fw-bold px-3 py-2">
                        <i class="bi bi-cloud-upload me-1"></i> Upload Materi
                    </a>
                    <a href="<?= BASE_URL ?>index.php?url=guru/tugas" class="btn btn-sm btn-outline-primary rounded-pill fw-bold px-3 py-2">
                        <i class="bi bi-file-earmark-plus me-1"></i> Buat Tugas
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px;" class="text-center">Tahap</th>
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
                                    <span class="badge <?= $isFirst ? 'bg-success' : 'bg-primary' ?> rounded-circle p-2 shadow-xs" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; font-size: 0.9rem;">
                                        <?= $stepNo ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-6 mb-0.5"><?= htmlspecialchars($item['title']) ?></div>
                                    <small class="text-muted d-block"><?= htmlspecialchars($item['desc']) ?></small>
                                </td>
                                <td>
                                    <?php if ($item['type'] === 'materi'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fw-bold px-2.5 py-1.5" style="font-size:0.75rem;">
                                            <i class="bi bi-book-half me-1"></i>Modul Materi
                                        </span>
                                    <?php elseif ($item['type'] === 'tugas'): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fw-bold px-2.5 py-1.5" style="font-size:0.75rem;">
                                            <i class="bi bi-card-checklist me-1"></i>Penugasan KBM
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill fw-bold px-2.5 py-1.5" style="font-size:0.75rem;">
                                            <i class="bi bi-patch-check-fill me-1"></i>Kuis / CBT
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1.5 fw-semibold" style="font-size:0.72rem;">
                                        <i class="bi bi-building me-1 text-secondary"></i><?= htmlspecialchars($item['kelas']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($isFirst): ?>
                                        <span class="badge bg-success text-white rounded-pill px-3 py-1.5 fw-bold" style="font-size:0.75rem;">
                                            <i class="bi bi-unlock-fill me-1"></i>Terbuka Awal
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border rounded-pill px-3 py-1.5 fw-semibold" style="font-size:0.75rem;">
                                            <i class="bi bi-lock-fill text-warning me-1"></i>Prasyarat: Tahap <?= $stepNo - 1 ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($item['type'] === 'materi'): ?>
                                        <a href="<?= BASE_URL ?>index.php?url=guru/materi" class="btn btn-sm btn-outline-success fw-bold rounded-pill px-3 py-1.5 shadow-xs" style="font-size:0.78rem;">
                                            <i class="bi bi-pencil-square me-1"></i> Kelola
                                        </a>
                                    <?php elseif ($item['type'] === 'tugas'): ?>
                                        <a href="<?= BASE_URL ?>index.php?url=guru/tugas" class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3 py-1.5 shadow-xs" style="font-size:0.78rem;">
                                            <i class="bi bi-eye me-1"></i> Submission
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>index.php?url=guru/bankSoal" class="btn btn-sm btn-outline-warning text-dark fw-bold rounded-pill px-3 py-1.5 shadow-xs" style="font-size:0.78rem;">
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
