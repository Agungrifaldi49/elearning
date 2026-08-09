<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Rekap Absensi Siswa & QR Code Scanner</h4>
                <p class="text-muted small mb-0">Input dan simpan presensi kehadiran kelas harian.</p>
            </div>
        </div>

        <div class="card card-custom p-4 mb-4">
            <form action="<?= BASE_URL ?>index.php?url=guru/absensi" method="GET" class="row g-3 align-items-end mb-4">
                <input type="hidden" name="url" value="guru/absensi">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Pilih Jadwal Mengajar</label>
                    <select name="jadwal_id" class="form-select">
                        <?php foreach ($jadwalList as $j): ?>
                            <option value="<?= $j['id'] ?>" <?= $selectedJadwal == $j['id'] ? 'selected' : '' ?>>
                                <?= $j['hari'] ?> | <?= htmlspecialchars($j['nama_mapel']) ?> - <?= htmlspecialchars($j['nama_kelas']) ?> (<?= substr($j['jam_mulai'],0,5) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tanggal Absen</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal) ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Tampilkan Siswa</button>
                </div>
            </form>

            <form action="<?= BASE_URL ?>index.php?url=guru/absensi&jadwal_id=<?= $selectedJadwal ?>&tanggal=<?= $tanggal ?>" method="POST">
                <?= Security::csrfField() ?>

                <div class="table-responsive mb-4">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Status Kehadiran</th>
                                <th>Keterangan / Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recap)): ?>
                                <tr><td colspan="5" class="text-center text-muted">Pilih jadwal mengajar untuk menampilkan siswa.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recap as $i => $row): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><code><?= htmlspecialchars($row['nis']) ?></code></td>
                                        <td class="fw-bold"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                        <td>
                                            <select name="absensi[<?= $row['siswa_id'] ?>]" class="form-select form-select-sm">
                                                <option value="Hadir" <?= $row['status'] === 'Hadir' ? 'selected' : '' ?>>Hadir</option>
                                                <option value="Izin" <?= $row['status'] === 'Izin' ? 'selected' : '' ?>>Izin</option>
                                                <option value="Sakit" <?= $row['status'] === 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                                                <option value="Alpa" <?= $row['status'] === 'Alpa' ? 'selected' : '' ?>>Alpa</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="keterangan[<?= $row['siswa_id'] ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars($row['keterangan'] ?? '') ?>" placeholder="Opsional">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($recap)): ?>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Rekap Absensi
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
