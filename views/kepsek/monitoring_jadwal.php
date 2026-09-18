<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-clock-history text-info me-2"></i>Monitoring Jadwal Pelajaran Sekolah</h4>
                <p class="text-muted small mb-0">Pengawasan matriks jadwal KBM mingguan, penugasan ruangan/lab, dan alokasi jam mengajar guru.</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill fs-6">
                    <i class="bi bi-calendar-week me-1"></i> Matriks Jadwal Terpusat
                </span>
            </div>
        </div>

        <!-- Filter Hari & Kelas -->
        <?php
        $selectedHari = $_GET['hari'] ?? '';
        $selectedKelas = (int)($_GET['kelas_id'] ?? 0);

        $filteredJadwal = array_filter($jadwalList, function($j) use ($selectedHari, $selectedKelas) {
            $matchHari = empty($selectedHari) || strtolower($j['hari']) === strtolower($selectedHari);
            $matchKelas = empty($selectedKelas) || (int)$j['kelas_id'] === $selectedKelas;
            return $matchHari && $matchKelas;
        });
        ?>

        <div class="card card-custom p-4 mb-4 shadow-sm border-start border-4 border-info">
            <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-3 align-items-end">
                <input type="hidden" name="url" value="kepsek/monitoringJadwal">

                <div class="col-12 col-md-5">
                    <label class="form-label small fw-bold text-dark"><i class="bi bi-calendar-day me-1 text-primary"></i>Filter Hari</label>
                    <select name="hari" class="form-select fw-semibold" onchange="this.form.submit()">
                        <option value="">-- Semua Hari (Senin - Sabtu) --</option>
                        <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h): ?>
                            <option value="<?= $h ?>" <?= strtolower($selectedHari) === strtolower($h) ? 'selected' : '' ?>><?= $h ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-5">
                    <label class="form-label small fw-bold text-dark"><i class="bi bi-bounding-box-circles me-1 text-success"></i>Filter Rombel Kelas</label>
                    <select name="kelas_id" class="form-select fw-semibold" onchange="this.form.submit()">
                        <option value="0">-- Semua Rombel Kelas --</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= $selectedKelas == $k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-info text-white w-100 fw-bold">
                        <i class="bi bi-filter me-1"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Jadwal -->
        <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-calendar-week-fill text-primary me-2"></i>Daftar Jadwal Mengajar (<?= count($filteredJadwal) ?> Sesi)</h6>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">Tahun Ajaran Aktif</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle <?= !empty($filteredJadwal) ? 'datatable' : '' ?>">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Hari</th>
                            <th>Jam Belajar</th>
                            <th>Mata Pelajaran</th>
                            <th>Guru Pengampu</th>
                            <th>Rombel Kelas</th>
                            <th>Ruangan / Lab</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($filteredJadwal)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada jadwal KBM yang sesuai filter.</td></tr>
                        <?php else: ?>
                            <?php foreach (array_values($filteredJadwal) as $i => $j): 
                                $badgeHari = match(strtolower($j['hari'])) {
                                    'senin' => 'bg-primary',
                                    'selasa' => 'bg-success',
                                    'rabu' => 'bg-info text-dark',
                                    'kamis' => 'bg-warning text-dark',
                                    'jumat' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <span class="badge <?= $badgeHari ?> px-3 py-2 rounded-pill">
                                            <?= htmlspecialchars($j['hari']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-clock me-1 text-muted"></i><?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?> WIB</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($j['nama_mapel'] ?? '-') ?></div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-primary"><i class="bi bi-person me-1"></i><?= htmlspecialchars($j['nama_guru'] ?? '-') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars($j['nama_kelas'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><i class="bi bi-door-closed me-1"></i><?= htmlspecialchars($j['ruangan'] ?? 'Ruang Kelas') ?></small>
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
