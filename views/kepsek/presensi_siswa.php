<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-calendar-check-fill text-primary me-2"></i>Monitoring Presensi Harian Siswa</h4>
                <p class="text-muted small mb-0">Pemantauan rekapitulasi kehadiran peserta didik per rombel kelas & tanggal KBM.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>index.php?url=kepsek/recapBulanan&type=siswa" class="btn btn-outline-primary shadow-sm fw-bold">
                    <i class="bi bi-calendar-range me-1"></i> Rekap Bulanan Siswa
                </a>
                <a href="<?= BASE_URL ?>index.php?url=kepsek/cetakLaporan&type=siswa" target="_blank" class="btn btn-primary shadow-sm fw-bold">
                    <i class="bi bi-printer me-1"></i> Cetak Laporan PDF
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card card-custom p-4 mb-4 shadow-sm border-start border-4 border-primary">
            <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-3 align-items-end">
                <input type="hidden" name="url" value="kepsek/presensiSiswa">

                <div class="col-12 col-md-5">
                    <label class="form-label small fw-bold text-dark"><i class="bi bi-bounding-box-circles me-1 text-primary"></i>Pilih Rombel Kelas</label>
                    <select name="kelas_id" class="form-select fw-semibold" onchange="this.form.submit()">
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= $selectedKelasId == $k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-5">
                    <label class="form-label small fw-bold text-dark"><i class="bi bi-calendar3 me-1 text-success"></i>Pilih Tanggal Presensi</label>
                    <input type="date" name="tanggal" class="form-control fw-semibold" value="<?= htmlspecialchars($tanggal) ?>" onchange="this.form.submit()">
                </div>

                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="bi bi-filter me-1"></i> Tampilkan
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistik Ringkasan -->
        <?php
        $totalSiswa = count($recap);
        $countHadir = count(array_filter($recap, fn($r) => ($r['status'] ?? '') === 'Hadir'));
        $countIzin = count(array_filter($recap, fn($r) => ($r['status'] ?? '') === 'Izin'));
        $countSakit = count(array_filter($recap, fn($r) => ($r['status'] ?? '') === 'Sakit'));
        $countAlpa = count(array_filter($recap, fn($r) => ($r['status'] ?? '') === 'Alpa'));
        $rateHadir = $totalSiswa > 0 ? round(($countHadir / $totalSiswa) * 100, 1) : 0;
        ?>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card card-custom p-3 shadow-sm text-center border-0 rounded-4 bg-success-subtle border border-success-subtle">
                    <div class="text-success display-6 fw-bold"><?= $countHadir ?></div>
                    <small class="fw-semibold text-success"><i class="bi bi-check-circle-fill me-1"></i>Hadir (<?= $rateHadir ?>%)</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-custom p-3 shadow-sm text-center border-0 rounded-4 bg-info-subtle border border-info-subtle">
                    <div class="text-info-emphasis display-6 fw-bold"><?= $countIzin ?></div>
                    <small class="fw-semibold text-info-emphasis"><i class="bi bi-info-circle-fill me-1"></i>Izin Resmi</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-custom p-3 shadow-sm text-center border-0 rounded-4 bg-warning-subtle border border-warning-subtle">
                    <div class="text-warning-emphasis display-6 fw-bold"><?= $countSakit ?></div>
                    <small class="fw-semibold text-warning-emphasis"><i class="bi bi-bandaid-fill me-1"></i>Sakit</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-custom p-3 shadow-sm text-center border-0 rounded-4 bg-danger-subtle border border-danger-subtle">
                    <div class="text-danger display-6 fw-bold"><?= $countAlpa ?></div>
                    <small class="fw-semibold text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Alpa / Tanpa Keterangan</small>
                </div>
            </div>
        </div>

        <!-- Tabel Siswa -->
        <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-list-check text-primary me-2"></i>Status Kehadiran Siswa</h6>
                <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill">
                    Total: <?= $totalSiswa ?> Siswa Terdata
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle datatable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>NIS / NISN</th>
                            <th>Nama Lengkap Siswa</th>
                            <th class="text-center">Status Kehadiran</th>
                            <th>Keterangan Tambahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recap)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data jadwal/presensi untuk rombel ini pada tanggal yang dipilih.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recap as $idx => $r): 
                                $st = $r['status'] ?? 'Belum Dicatat';
                                $badgeSt = match($st) {
                                    'Hadir' => 'bg-success',
                                    'Izin' => 'bg-info text-dark',
                                    'Sakit' => 'bg-warning text-dark',
                                    'Alpa' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><code><?= htmlspecialchars($r['nis'] ?? '-') ?></code></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($r['nama_lengkap']) ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= $badgeSt ?> px-3 py-2 rounded-pill">
                                            <?= htmlspecialchars($st) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= htmlspecialchars($r['keterangan'] ?? '-') ?></small>
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
