<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* Modern Admin E-Rapor Portal Styling */
.admin-nilai-page-wrapper {
}

/* Glassmorphic Hero Banner */
.admin-nilai-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #d97706 100%);
    border-radius: 20px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
    position: relative;
    overflow: hidden;
}

.admin-nilai-hero::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(251, 191, 36, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}

/* Table Card Styling */
.table-card-custom {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    border-top: 4px solid #d97706;
}

/* Responsive Table Overrides */
@media (max-width: 767.98px) {
    .admin-nilai-hero {
        padding: 20px !important;
    }
}
</style>

<main class="main-content px-3 px-md-4 admin-nilai-page-wrapper">
    <div class="container-fluid">

        <!-- 🚀 HERO BANNER ADMIN E-RAPOR -->
        <div class="admin-nilai-hero p-4 p-md-5 mb-4">
            <div class="row align-items-center relative-zIndex-1">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="d-inline-flex align-items-center gap-2 px-3.5 py-2 rounded-pill bg-warning text-dark shadow-sm small fw-bold mb-3">
                        <i class="bi bi-award-fill text-dark fs-6"></i>
                        <span>Control Center E-Rapor & Rekap Nilai Terpadu Admin</span>
                    </div>
                    <h2 class="fw-bold mb-2 text-white" style="letter-spacing: -0.5px;">Rekapitulasi Nilai E-Rapor Realtime</h2>
                    <p class="text-white text-opacity-85 small mb-0 lh-lg" style="max-width: 680px;">
                        Data nilai e-rapor terintegrasi otomatis 100% secara real-time dengan seluruh aktivitas pengerjaan kuis, ujian CBT, serta penilaian tugas portofolio dari Guru dan Siswa.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <form action="<?= BASE_URL ?>index.php?url=admin/nilai" method="POST" class="d-inline">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="sync_all">
                        <button type="submit" class="btn btn-warning text-dark px-4 py-2.5 rounded-pill fw-bold shadow-lg d-inline-flex align-items-center gap-2 hover-scale">
                            <i class="bi bi-arrow-repeat fs-5"></i>
                            <span>Sinkronkan Nilai Realtime</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 🎛️ FILTER CLASS & MAPEL CONTROLS -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-3 align-items-end">
                    <input type="hidden" name="url" value="<?= htmlspecialchars($_GET['url'] ?? 'admin/nilai') ?>">
                    
                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-bold text-dark"><i class="bi bi-building text-primary me-1"></i>Pilih Kelas (Rombel)</label>
                        <select name="kelas_id" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= $selectedKelasId == $k['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_kelas']) ?> (<?= htmlspecialchars($k['nama_jurusan'] ?? 'Semua Jurusan') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-bold text-dark"><i class="bi bi-journal-bookmark text-warning me-1"></i>Pilih Mata Pelajaran</label>
                        <select name="mapel_id" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($mapelList as $mp): ?>
                                <option value="<?= $mp['id'] ?>" <?= $selectedMapelId == $mp['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mp['nama_mapel']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-2 text-end">
                        <button type="submit" class="btn btn-primary w-100 fw-bold rounded-3">
                            <i class="bi bi-filter me-1"></i> Tampilkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 📊 TABLE REKAP NILAI REALTIME -->
        <div class="table-card-custom p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-table text-warning me-2"></i>Tabel Rekapitulasi E-Rapor Siswa</h5>
                    <small class="text-muted">Formula E-Rapor: <strong>20% Tugas + 20% Quiz + 30% UTS + 30% UAS</strong></small>
                </div>
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">
                    Total: <?= count($siswaList) ?> Siswa Terdaftar
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px;">No</th>
                            <th>NIS / NISN</th>
                            <th>Nama Lengkap Siswa</th>
                            <th class="text-center">Tugas (20%)</th>
                            <th class="text-center">Quiz CBT (20%)</th>
                            <th class="text-center">UTS (30%)</th>
                            <th class="text-center">UAS (30%)</th>
                            <th class="text-center">Nilai Akhir</th>
                            <th class="text-center">Predikat</th>
                            <th class="text-center">Kelulusan KKM</th>
                            <th class="text-center">Aksi Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($siswaList)): ?>
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 text-slate-300 d-block mb-2"></i>
                                    Belum ada siswa pada kelas yang dipilih.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($siswaList as $idx => $s): 
                                $nData = $existingNilai[$s['id']] ?? null;
                                $tugas = (float)($nData['nilai_tugas'] ?? 0);
                                $quiz = (float)($nData['nilai_quiz'] ?? 0);
                                $uts = (float)($nData['nilai_uts'] ?? 0);
                                $uas = (float)($nData['nilai_uas'] ?? 0);
                                $akhir = (float)($nData['nilai_akhir'] ?? 0);

                                $pred = NilaiModel::getPredikat($akhir);
                                $isLulus = ($akhir >= 75);
                            ?>
                                <tr>
                                    <td class="fw-bold text-muted"><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size:0.85rem;"><?= htmlspecialchars($s['nis'] ?? '-') ?></div>
                                        <small class="text-muted" style="font-size:0.75rem;">NISN: <?= htmlspecialchars($s['nisn'] ?? '-') ?></small>
                                    </td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($s['nama_lengkap']) ?></td>
                                    <td class="text-center fw-bold text-teal" style="color:#0d9488;"><?= number_format($tugas, 1) ?></td>
                                    <td class="text-center fw-bold text-primary"><?= number_format($quiz, 1) ?></td>
                                    <td class="text-center fw-bold text-warning-emphasis"><?= number_format($uts, 1) ?></td>
                                    <td class="text-center fw-bold text-info-emphasis"><?= number_format($uas, 1) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-dark fs-6 px-3 py-1.5 rounded-pill"><?= number_format($akhir, 1) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $pred['class'] ?> px-2.5 py-1 rounded-pill fw-bold" style="font-size:0.75rem;">
                                            <?= $pred['grade'] ?> (<?= $pred['label'] ?>)
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isLulus): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check-circle me-1"></i>Tuntas KKM</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold"><i class="bi bi-x-circle me-1"></i>Remedial</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning text-dark px-2.5 rounded-pill shadow-xs" style="font-size:0.75rem;" data-bs-toggle="modal" data-bs-target="#modalEditNilai<?= $s['id'] ?>">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </button>
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

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- 📝 MODALS SECTION EDIT NILAI -->
<!-- ════════════════════════════════════════════════════════════════ -->

<?php foreach ($siswaList as $s): 
    $nData = $existingNilai[$s['id']] ?? null;
    $tugas = (float)($nData['nilai_tugas'] ?? 0);
    $quiz = (float)($nData['nilai_quiz'] ?? 0);
    $uts = (float)($nData['nilai_uts'] ?? 0);
    $uas = (float)($nData['nilai_uas'] ?? 0);
    $akhir = (float)($nData['nilai_akhir'] ?? 0);
?>
    <div class="modal fade" id="modalEditNilai<?= $s['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-0 bg-dark text-white p-3.5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="bg-warning rounded-3 p-2 text-dark shadow-xs">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-white mb-0">Input / Edit Nilai E-Rapor</h6>
                            <small class="text-info fw-medium" style="font-size:0.75rem;"><?= htmlspecialchars($s['nama_lengkap']) ?> (NIS: <?= htmlspecialchars($s['nis']) ?>)</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>index.php?url=admin/nilai" method="POST">
                    <div class="modal-body p-4">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="action" value="single_save">
                        <input type="hidden" name="siswa_id" value="<?= $s['id'] ?>">
                        <input type="hidden" name="mapel_id" value="<?= $selectedMapelId ?>">

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Nilai Tugas (20%)</label>
                                <input type="number" step="0.1" name="nilai_tugas" class="form-control" value="<?= $tugas ?>" min="0" max="100" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Nilai Quiz CBT (20%)</label>
                                <input type="number" step="0.1" name="nilai_quiz" class="form-control" value="<?= $quiz ?>" min="0" max="100" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Nilai UTS (30%)</label>
                                <input type="number" step="0.1" name="nilai_uts" class="form-control" value="<?= $uts ?>" min="0" max="100" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Nilai UAS (30%)</label>
                                <input type="number" step="0.1" name="nilai_uas" class="form-control" value="<?= $uas ?>" min="0" max="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 p-4 justify-content-between">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning px-4 rounded-pill fw-bold text-dark shadow-sm">Simpan E-Rapor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
