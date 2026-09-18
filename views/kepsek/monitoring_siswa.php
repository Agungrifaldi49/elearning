<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$currentKelasName = 'Semua Kelas';
if ($selectedKelasId > 0 && !empty($kelasList)) {
    foreach ($kelasList as $k) {
        if ((int)$k['id'] === $selectedKelasId) {
            $currentKelasName = $k['nama_kelas'];
            break;
        }
    }
}

// Hitung metrik ringkasan
$totalSiswa = count($siswaList);
$tuntasCount = count(array_filter($siswaList, fn($s) => (float)($s['avg_rapor'] ?? 0) >= 75));
$perluBimbinganCount = count(array_filter($siswaList, fn($s) => (float)($s['avg_rapor'] ?? 0) > 0 && (float)($s['avg_rapor'] ?? 0) < 75));
$avgRaporTotal = $totalSiswa > 0 ? round(array_sum(array_column($siswaList, 'avg_rapor')) / $totalSiswa, 1) : 0;
?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Header Halaman -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-people-fill text-success me-2"></i>Monitoring Siswa & Progress Belajar</h4>
                <p class="text-muted small mb-0">Laporan capaian akademik rata-rata E-Rapor, evaluasi Kuis CBT, dan keaktifan pengumpulan tugas per siswa.</p>
            </div>
            <a href="<?= BASE_URL ?>index.php?url=kepsek/cetakLaporan&type=siswa<?= $selectedKelasId > 0 ? '&kelas_id=' . $selectedKelasId : '' ?>" target="_blank" class="btn btn-primary shadow-sm fw-bold">
                <i class="bi bi-printer me-1"></i> Cetak Laporan PDF <?= $selectedKelasId > 0 ? '(' . htmlspecialchars($currentKelasName) . ')' : '' ?>
            </a>
        </div>

        <!-- Toolbar Filter Rombel Kelas -->
        <div class="card card-custom p-3 p-md-4 mb-4 shadow-sm border-0 rounded-4">
            <form action="<?= BASE_URL ?>index.php" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="url" value="kepsek/monitoringSiswa">

                <div class="col-12 col-md-6 col-lg-5">
                    <label class="form-label small fw-bold text-dark mb-1">
                        <i class="bi bi-funnel-fill text-primary me-1"></i> Filter Berdasarkan Rombel Kelas:
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-3 text-muted">
                            <i class="bi bi-diagram-3-fill"></i>
                        </span>
                        <select name="kelas_id" id="filterKelasId" class="form-select bg-light border-start-0 rounded-end-3 fw-semibold" onchange="this.form.submit()">
                            <option value="0">-- Semua Rombel Kelas --</option>
                            <?php if (!empty($kelasList)): ?>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= $selectedKelasId == $k['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['nama_kelas']) ?> <?= !empty($k['tingkat']) ? '(Tingkat ' . htmlspecialchars($k['tingkat']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-7 d-flex gap-2 align-items-center flex-wrap">
                    <button type="submit" class="btn btn-primary fw-bold px-3 py-2 rounded-3 shadow-xs">
                        <i class="bi bi-search me-1"></i> Terapkan Filter
                    </button>
                    <?php if ($selectedKelasId > 0): ?>
                        <a href="<?= BASE_URL ?>index.php?url=kepsek/monitoringSiswa" class="btn btn-outline-secondary fw-semibold px-3 py-2 rounded-3">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                        </a>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill small">
                            <i class="bi bi-check-circle-fill me-1"></i> Menampilkan Kelas: <b><?= htmlspecialchars($currentKelasName) ?></b>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill small">
                            <i class="bi bi-globe me-1"></i> Menampilkan Seluruh Rombel Sekolah
                        </span>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Ringkasan Statistik Siswa -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-primary text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Total Siswa Terdata</div>
                    <div class="display-6 fw-bold my-1"><?= $totalSiswa ?> Siswa</div>
                    <small><?= $selectedKelasId > 0 ? 'Rombel: ' . htmlspecialchars($currentKelasName) : 'Seluruh Rombel Belajar' ?></small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-success text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Tuntas Belajar (KKM &ge; 75)</div>
                    <div class="display-6 fw-bold my-1"><?= $tuntasCount ?> Siswa</div>
                    <small><?= $totalSiswa > 0 ? round(($tuntasCount / $totalSiswa) * 100, 1) . '% dari total rombel' : '0% dari total rombel' ?></small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-warning text-dark">
                    <div class="small fw-semibold text-uppercase opacity-75">Perlu Pendampingan (< 75)</div>
                    <div class="display-6 fw-bold my-1"><?= $perluBimbinganCount ?> Siswa</div>
                    <small>Memerlukan remedial / bimbingan</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-info text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Rata-Rata Skor Rapor</div>
                    <div class="display-6 fw-bold my-1"><?= number_format($avgRaporTotal, 1) ?></div>
                    <small><?= $avgRaporTotal >= 75 ? 'Rata-rata di atas KKM Sekolah' : 'Perlu evaluasi berkala KBM' ?></small>
                </div>
            </div>
        </div>

        <!-- Tabel Data Siswa -->
        <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-table text-success me-2"></i>Daftar Kemajuan Belajar Siswa
                    <?php if ($selectedKelasId > 0): ?>
                        <span class="badge bg-primary ms-2"><?= htmlspecialchars($currentKelasName) ?></span>
                    <?php endif; ?>
                </h6>
                <span class="badge bg-light text-muted border px-3 py-2 rounded-pill">Total: <?= $totalSiswa ?> Siswa</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle <?= !empty($siswaList) ? 'datatable' : '' ?>">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>NIS / NISN</th>
                            <th>Nama Siswa</th>
                            <th>Rombel Kelas</th>
                            <th>Program Keahlian</th>
                            <th class="text-center">Tugas Dikumpul</th>
                            <th class="text-center">Rata-Rata Kuis CBT</th>
                            <th class="text-center">Rata-Rata E-Rapor</th>
                            <th class="text-center">Status Ketuntasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($siswaList)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    <div class="fw-bold text-dark mb-1">Tidak Ada Data Siswa Ditemukan</div>
                                    <p class="small text-muted mb-0">
                                        <?= $selectedKelasId > 0 ? 'Belum ada siswa yang terdaftar pada kelas <b>' . htmlspecialchars($currentKelasName) . '</b>.' : 'Belum ada data siswa terdaftar dalam sistem.' ?>
                                    </p>
                                    <?php if ($selectedKelasId > 0): ?>
                                        <a href="<?= BASE_URL ?>index.php?url=kepsek/monitoringSiswa" class="btn btn-sm btn-outline-primary mt-3 rounded-pill px-3">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Tampilkan Semua Kelas
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($siswaList as $i => $s): 
                                $avgRapor = (float)($s['avg_rapor'] ?? 0);
                                $avgQuiz = (float)($s['avg_quiz'] ?? 0);
                                $totalTugas = (int)($s['total_tugas_dikumpul'] ?? 0);

                                if ($avgRapor == 0 && $totalTugas == 0) {
                                    $statusBadge = 'bg-secondary';
                                    $statusText = 'BELUM ADA DATA';
                                } elseif ($avgRapor >= 75) {
                                    $statusBadge = 'bg-success';
                                    $statusText = 'TUNTAS';
                                } else {
                                    $statusBadge = 'bg-danger';
                                    $statusText = 'BELUM TUNTAS';
                                }
                            ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><code><?= htmlspecialchars($s['nis'] ?? '-') ?></code> / <?= htmlspecialchars($s['nisn'] ?? '-') ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($s['nama_lengkap']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($s['email'] ?? ($s['username'] ?? '-')) ?></small>
                                    </td>
                                    <td><span class="badge bg-primary px-2.5 py-1.5 rounded-pill"><?= htmlspecialchars($s['nama_kelas'] ?? 'Belum Ada Kelas') ?></span></td>
                                    <td><span class="badge bg-secondary-subtle text-dark border px-2.5 py-1.5 rounded-pill"><?= htmlspecialchars($s['nama_jurusan'] ?? 'Umum') ?></span></td>
                                    <td class="text-center">
                                        <span class="badge <?= $totalTugas > 0 ? 'bg-info text-dark' : 'bg-light text-muted border' ?> px-2.5 py-1.5 rounded-pill">
                                            <?= $totalTugas > 0 ? $totalTugas . ' Tugas' : '0 Tugas' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($avgQuiz > 0): ?>
                                            <span class="fw-bold text-dark"><?= number_format($avgQuiz, 1) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($avgRapor > 0): ?>
                                            <span class="fw-bold fs-6 text-primary"><?= number_format($avgRapor, 1) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Belum Dinilai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $statusBadge ?> px-3 py-1.5 rounded-pill">
                                            <?= $statusText ?>
                                        </span>
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
