<?php require_once ROOT_PATH . 'models/NilaiModel.php'; ?>
<?php
$userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
$isReadOnly = in_array($userRole, ['kepala sekolah', 'kepsek']);
$formTargetUrl = in_array($userRole, ['administrator', 'admin']) ? 'admin/inputNilai' : 'guru/inputNilai';
?>
<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-journal-check text-primary me-2"></i><?= $isReadOnly ? 'Rekap Leger & Nilai E-Rapor Siswa' : 'Input & Edit Nilai E-Rapor Siswa' ?></h4>
            <p class="text-muted small mb-0"><?= $isReadOnly ? 'Pemantauan rekap leger nilai E-Rapor siswa per-rombel kelas & mata pelajaran.' : 'Pengisian dan pengeditan nilai E-Rapor per-rombel kelas & mata pelajaran ajar secara presisi.' ?></p>
        </div>
        <?php if (!$isReadOnly): ?>
            <button class="btn btn-primary shadow-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalSingleSave">
                <i class="bi bi-person-plus-fill me-1"></i> Input Nilai 1 Siswa
            </button>
        <?php else: ?>
            <span class="badge bg-secondary rounded-pill px-3 py-2 shadow-sm fs-6">
                <i class="bi bi-eye-fill me-1"></i> Mode Lihat Saja (Kepala Sekolah)
            </span>
        <?php endif; ?>
    </div>

    <!-- Filter Bar Kelas & Mapel Ajar Guru -->
    <div class="card-custom p-4 mb-4 shadow-sm border-start border-4 border-primary">
        <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-3 align-items-end">
            <input type="hidden" name="url" value="<?= $formTargetUrl ?>">

            <div class="col-12 col-md-5">
                <label class="form-label small fw-bold text-dark"><i class="bi bi-bounding-box-circles me-1 text-primary"></i>Pilih Rombel Kelas Target</label>
                <select name="kelas_id" class="form-select fw-semibold" onchange="this.form.submit()">
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $selectedKelasId == $k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-5">
                <label class="form-label small fw-bold text-dark"><i class="bi bi-journal-text me-1 text-success"></i>Pilih Mata Pelajaran Ajar</label>
                <select name="mapel_id" class="form-select fw-semibold" onchange="this.form.submit()">
                    <?php foreach ($mapelList as $mp): ?>
                        <option value="<?= $mp['id'] ?>" <?= $selectedMapelId == $mp['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($mp['nama_mapel']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold">
                    <i class="bi bi-filter me-1"></i> Filter Leger
                </button>
            </div>
        </form>
    </div>

    <!-- Rombel Active Summary Banner -->
    <?php if (!empty($selectedKelasInfo)): ?>
        <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:42px; height:42px;">
                    <i class="bi bi-award-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">
                        Rombel Kelas: <?= htmlspecialchars($selectedKelasInfo['nama_kelas']) ?> (<?= htmlspecialchars($selectedKelasInfo['nama_jurusan'] ?? 'Umum') ?>)
                    </h6>
                    <small class="text-muted">
                        Mata Pelajaran: <strong><?= htmlspecialchars(array_column($mapelList, 'nama_mapel', 'id')[$selectedMapelId] ?? 'Mapel Ajar') ?></strong> |
                        Jumlah Anggota Siswa: <strong><?= count($siswaList) ?> Siswa</strong>
                    </small>
                </div>
            </div>
            <div class="text-muted small">
                Formula: <span class="badge bg-white text-dark border shadow-sm">Tugas 20% + Quiz 20% + UTS 30% + UAS 30%</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Matrix Batch Table Form -->
    <div class="card-custom p-4 shadow-sm mb-4">
        <form action="<?= BASE_URL ?>index.php?url=<?= $formTargetUrl ?>&kelas_id=<?= $selectedKelasId ?>&mapel_id=<?= $selectedMapelId ?>" method="POST">
            <?= Security::csrfField() ?>
            <input type="hidden" name="action" value="batch_save">

            <div class="table-responsive">
                <table class="table table-hover align-middle border-top">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>NIS & Nama Siswa</th>
                            <th style="width:120px;" class="text-center">Tugas (20%)</th>
                            <th style="width:120px;" class="text-center">Quiz (20%)</th>
                            <th style="width:120px;" class="text-center">UTS (30%)</th>
                            <th style="width:120px;" class="text-center">UAS (30%)</th>
                            <th style="width:110px;" class="text-center">Nilai Akhir</th>
                            <th style="width:130px;" class="text-center">Predikat</th>
                            <?php if (!$isReadOnly): ?>
                                <th style="width:100px;" class="text-center">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($siswaList)): ?>
                            <tr>
                                <td colspan="<?= $isReadOnly ? '8' : '9' ?>" class="text-center py-4 text-muted">Tidak ada siswa terdaftar di rombel kelas ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($siswaList as $i => $s): 
                                $nData = $existingNilai[$s['id']] ?? [];
                                $nTugas = min(100.0, max(0.0, (float)($nData['nilai_tugas'] ?? 0)));
                                $nQuiz  = min(100.0, max(0.0, (float)($nData['nilai_quiz'] ?? 0)));
                                $nUts   = min(100.0, max(0.0, (float)($nData['nilai_uts'] ?? 0)));
                                $nUas   = min(100.0, max(0.0, (float)($nData['nilai_uas'] ?? 0)));

                                $weightsRow = [];
                                if ($nTugas > 0) $weightsRow[] = ['val' => $nTugas, 'w' => 0.20];
                                if ($nQuiz > 0)  $weightsRow[] = ['val' => $nQuiz,  'w' => 0.20];
                                if ($nUts > 0)   $weightsRow[] = ['val' => $nUts,   'w' => 0.30];
                                if ($nUas > 0)   $weightsRow[] = ['val' => $nUas,   'w' => 0.30];

                                if (!empty($weightsRow)) {
                                    $sumValR = 0; $sumWR = 0;
                                    foreach ($weightsRow as $wr) {
                                        $sumValR += ($wr['val'] * $wr['w']);
                                        $sumWR += $wr['w'];
                                    }
                                    $nAkhir = ($sumWR > 0) ? round($sumValR / $sumWR, 2) : 0.00;
                                } else {
                                    $nAkhir = ($nTugas*0.2) + ($nQuiz*0.2) + ($nUts*0.3) + ($nUas*0.3);
                                }
                                $nAkhir = min(100.0, max(0.0, (float)$nAkhir));

                                $predikat = NilaiModel::getPredikat((float)$nAkhir);
                            ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($s['nama_lengkap']) ?></div>
                                        <small class="text-muted">NIS: <?= htmlspecialchars($s['nis'] ?? '-') ?> | Jurusan: <?= htmlspecialchars($s['nama_jurusan'] ?? '-') ?></small>
                                    </td>
                                    <?php if ($isReadOnly): ?>
                                        <td class="text-center"><span class="fw-bold text-dark"><?= number_format((float)$nTugas, 1) ?></span></td>
                                        <td class="text-center"><span class="fw-bold text-dark"><?= number_format((float)$nQuiz, 1) ?></span></td>
                                        <td class="text-center"><span class="fw-bold text-dark"><?= number_format((float)$nUts, 1) ?></span></td>
                                        <td class="text-center"><span class="fw-bold text-dark"><?= number_format((float)$nUas, 1) ?></span></td>
                                    <?php else: ?>
                                        <td>
                                            <input type="number" name="nilai[<?= $s['id'] ?>][tugas]" class="form-control form-control-sm text-center fw-semibold" data-siswa="<?= $s['id'] ?>" min="0" max="100" step="0.5" value="<?= $nTugas ?>" onchange="calcRow(<?= $s['id'] ?>)">
                                        </td>
                                        <td>
                                            <input type="number" name="nilai[<?= $s['id'] ?>][quiz]" class="form-control form-control-sm text-center fw-semibold" data-siswa="<?= $s['id'] ?>" min="0" max="100" step="0.5" value="<?= $nQuiz ?>" onchange="calcRow(<?= $s['id'] ?>)">
                                        </td>
                                        <td>
                                            <input type="number" name="nilai[<?= $s['id'] ?>][uts]" class="form-control form-control-sm text-center fw-semibold" data-siswa="<?= $s['id'] ?>" min="0" max="100" step="0.5" value="<?= $nUts ?>" onchange="calcRow(<?= $s['id'] ?>)">
                                        </td>
                                        <td>
                                            <input type="number" name="nilai[<?= $s['id'] ?>][uas]" class="form-control form-control-sm text-center fw-semibold" data-siswa="<?= $s['id'] ?>" min="0" max="100" step="0.5" value="<?= $nUas ?>" onchange="calcRow(<?= $s['id'] ?>)">
                                        </td>
                                    <?php endif; ?>
                                    <td class="text-center">
                                        <span class="fw-bold text-primary fs-6" id="valAkhir<?= $s['id'] ?>"><?= number_format((float)$nAkhir, 1) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $predikat['class'] ?>" id="badgePredikat<?= $s['id'] ?>">
                                            <?= $predikat['grade'] ?> (<?= $predikat['label'] ?>)
                                        </span>
                                    </td>
                                    <?php if (!$isReadOnly): ?>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalEditNilai<?= $s['id'] ?>">
                                                <i class="bi bi-pencil-square me-1"></i> Edit
                                            </button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!$isReadOnly && !empty($siswaList)): ?>
                <div class="d-flex justify-content-end mt-3 pt-3 border-top">
                    <button type="submit" class="btn btn-success btn-lg px-4 fw-bold shadow">
                        <i class="bi bi-check-circle-fill me-2"></i>Simpan Seluruh E-Rapor Kelas Ini
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>

</div>
</main>

<?php if (!$isReadOnly && !empty($siswaList)): ?>
    <!-- Modal Edit Nilai Per Siswa -->
    <?php foreach ($siswaList as $s): 
        $nData = $existingNilai[$s['id']] ?? [];
        $nTugas = $nData['nilai_tugas'] ?? 0;
        $nQuiz  = $nData['nilai_quiz'] ?? 0;
        $nUts   = $nData['nilai_uts'] ?? 0;
        $nUas   = $nData['nilai_uas'] ?? 0;
    ?>
        <div class="modal fade" id="modalEditNilai<?= $s['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0 bg-primary text-white p-3.5" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                        <h6 class="modal-title fw-bold text-white mb-0">
                            <i class="bi bi-pencil-square me-2"></i>Edit Nilai E-Rapor Siswa
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="<?= BASE_URL ?>index.php?url=<?= $formTargetUrl ?>&kelas_id=<?= $selectedKelasId ?>&mapel_id=<?= $selectedMapelId ?>" method="POST">
                        <div class="modal-body p-4 bg-light">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="action" value="single_save">
                            <input type="hidden" name="siswa_id" value="<?= $s['id'] ?>">
                            <input type="hidden" name="mapel_id" value="<?= $selectedMapelId ?>">

                            <div class="p-3 bg-white rounded-3 border mb-3">
                                <small class="text-muted d-block">Siswa Target:</small>
                                <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($s['nama_lengkap']) ?></h6>
                                <small class="text-muted">NIS: <?= htmlspecialchars($s['nis'] ?? '-') ?> | Jurusan: <?= htmlspecialchars($s['nama_jurusan'] ?? '-') ?></small>
                            </div>

                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-dark">Nilai Tugas (20%)</label>
                                    <input type="number" name="nilai_tugas" class="form-control rounded-3" min="0" max="100" step="0.5" value="<?= $nTugas ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-dark">Nilai Quiz (20%)</label>
                                    <input type="number" name="nilai_quiz" class="form-control rounded-3" min="0" max="100" step="0.5" value="<?= $nQuiz ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-dark">Nilai UTS (30%)</label>
                                    <input type="number" name="nilai_uts" class="form-control rounded-3" min="0" max="100" step="0.5" value="<?= $nUts ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-dark">Nilai UAS (30%)</label>
                                    <input type="number" name="nilai_uas" class="form-control rounded-3" min="0" max="100" step="0.5" value="<?= $nUas ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-3.5 justify-content-between bg-white border-top">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                <i class="bi bi-floppy-fill me-1.5"></i> Simpan Perubahan Nilai
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (!$isReadOnly): ?>
<!-- Modal Single Student Input -->
<div class="modal fade" id="modalSingleSave" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title"><i class="bi bi-person-plus text-primary me-2"></i>Input Nilai 1 Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=<?= $formTargetUrl ?>&kelas_id=<?= $selectedKelasId ?>&mapel_id=<?= $selectedMapelId ?>" method="POST">
                <div class="modal-body">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="single_save">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Siswa</label>
                        <select name="siswa_id" class="form-select" required>
                            <option value="">-- Pilih Siswa Rombel --</option>
                            <?php foreach ($siswaList as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama_lengkap'] . ' (' . ($s['nama_kelas'] ?? '') . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Mata Pelajaran</label>
                        <select name="mapel_id" class="form-select" required>
                            <?php foreach ($mapelList as $mp): ?>
                                <option value="<?= $mp['id'] ?>" <?= $selectedMapelId == $mp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Nilai Tugas</label>
                            <input type="number" name="nilai_tugas" class="form-control" min="0" max="100" step="0.5" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Nilai Quiz</label>
                            <input type="number" name="nilai_quiz" class="form-control" min="0" max="100" step="0.5" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Nilai UTS</label>
                            <input type="number" name="nilai_uts" class="form-control" min="0" max="100" step="0.5" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Nilai UAS</label>
                            <input type="number" name="nilai_uas" class="form-control" min="0" max="100" step="0.5" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Nilai</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function calcRow(siswaId) {
    const inputs = document.querySelectorAll(`input[data-siswa="${siswaId}"]`);
    if (inputs.length < 4) return;

    let t = parseFloat(inputs[0].value) || 0;
    let q = parseFloat(inputs[1].value) || 0;
    let uts = parseFloat(inputs[2].value) || 0;
    let uas = parseFloat(inputs[3].value) || 0;

    if (t > 100) { t = 100; inputs[0].value = 100; }
    if (q > 100) { q = 100; inputs[1].value = 100; }
    if (uts > 100) { uts = 100; inputs[2].value = 100; }
    if (uas > 100) { uas = 100; inputs[3].value = 100; }

    let sumVal = 0, sumW = 0;
    if (t > 0)   { sumVal += (t * 0.20);   sumW += 0.20; }
    if (q > 0)   { sumVal += (q * 0.20);   sumW += 0.20; }
    if (uts > 0) { sumVal += (uts * 0.30); sumW += 0.30; }
    if (uas > 0) { sumVal += (uas * 0.30); sumW += 0.30; }

    let akhir = sumW > 0 ? (sumVal / sumW) : ((t * 0.2) + (q * 0.2) + (uts * 0.3) + (uas * 0.3));
    if (akhir > 100) akhir = 100;

    const valElem = document.getElementById('valAkhir' + siswaId);
    const badgeElem = document.getElementById('badgePredikat' + siswaId);

    if (valElem) {
        valElem.textContent = akhir.toFixed(1);
    }

    if (badgeElem) {
        if (akhir >= 88) {
            badgeElem.className = 'badge bg-success';
            badgeElem.textContent = 'A (Sangat Baik)';
        } else if (akhir >= 78) {
            badgeElem.className = 'badge bg-primary';
            badgeElem.textContent = 'B (Baik)';
        } else if (akhir >= 68) {
            badgeElem.className = 'badge bg-warning text-dark';
            badgeElem.textContent = 'C (Cukup)';
        } else {
            badgeElem.className = 'badge bg-danger';
            badgeElem.textContent = 'D (Kurang)';
        }
    }
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
