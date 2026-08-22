<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$currentModuleUrl = $_GET['url'] ?? '';
$isAdminRoute = (strpos($currentModuleUrl, 'admin/') === 0 || strtolower(AuthHelper::user()['role_name'] ?? '') === 'administrator');
$scanQrUrl = $isAdminRoute ? BASE_URL . 'index.php?url=admin/scanQr' : BASE_URL . 'index.php?url=guru/scanQr';
$formAbsensiUrl = $isAdminRoute ? BASE_URL . 'index.php?url=admin/absensi' : BASE_URL . 'index.php?url=guru/absensi';
?>
<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-calendar-check-fill text-primary me-2"></i>Rekap Absensi Siswa & QR Code Scanner</h4>
                <p class="text-muted small mb-0">Data presensi otomatis terhubung secara langsung dengan hasil Scan QR Code siswa.</p>
            </div>
            <div>
                <a href="<?= $scanQrUrl ?>" class="btn btn-success text-white rounded-pill px-4 fw-bold shadow-sm">
                    <i class="bi bi-qr-code-scan me-1"></i> Buka QR Code Scanner
                </a>
            </div>
        </div>

        <div class="card card-custom p-4 mb-4 border-0 rounded-4 shadow-sm bg-white">
            <form action="<?= $formAbsensiUrl ?>" method="GET" class="row g-3 align-items-end mb-4">
                <input type="hidden" name="url" value="<?= $isAdminRoute ? 'admin/absensi' : 'guru/absensi' ?>">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-dark">Pilih Jadwal Mengajar</label>
                    <select name="jadwal_id" class="form-select rounded-3">
                        <?php foreach ($jadwalList as $j): ?>
                            <option value="<?= $j['id'] ?>" <?= $selectedJadwal == $j['id'] ? 'selected' : '' ?>>
                                <?= $j['hari'] ?> | <?= htmlspecialchars($j['nama_mapel']) ?> - <?= htmlspecialchars($j['nama_kelas']) ?> (<?= substr($j['jam_mulai'],0,5) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-dark">Tanggal Absen</label>
                    <input type="date" name="tanggal" class="form-control rounded-3" value="<?= htmlspecialchars($tanggal) ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold"><i class="bi bi-search me-1"></i> Tampilkan Siswa</button>
                </div>
            </form>

            <form action="<?= $formAbsensiUrl ?>&jadwal_id=<?= $selectedJadwal ?>&tanggal=<?= $tanggal ?>" method="POST">
                <?= Security::csrfField() ?>

                <?php if (!empty($recap)): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <small class="text-muted fw-semibold">Menampilkan <?= count($recap) ?> siswa terdaftar</small>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold" onclick="setSemuaHadir()">
                            <i class="bi bi-check-all me-1"></i> Set Semua Hadir
                        </button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive mb-4">
                    <table class="table table-hover align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Status Kehadiran</th>
                                <th>Keterangan / Informasi QR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recap)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">Pilih jadwal mengajar untuk menampilkan siswa.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recap as $i => $row): 
                                    $isQrScan = !empty($row['qr_code']) && strpos($row['qr_code'], 'QR_') === 0;
                                    $waktuHadirStr = !empty($row['waktu_hadir']) ? date('H:i', strtotime($row['waktu_hadir'])) : '';
                                    $currentStatus = $row['status'] ?? 'Alpa';
                                    if (empty($row['status']) && $isQrScan) $currentStatus = 'Hadir';
                                ?>
                                    <tr class="border-bottom">
                                        <td><span class="badge bg-secondary rounded-circle py-1 px-2"><?= $i + 1 ?></span></td>
                                        <td><code><?= htmlspecialchars($row['nis'] ?: ($row['nisn'] ?: '-')) ?></code></td>
                                        <td class="fw-bold text-dark">
                                            <?= htmlspecialchars($row['nama_lengkap']) ?>
                                            <?php if ($isQrScan): ?>
                                                <span class="badge bg-success-subtle text-success border border-success ms-1 px-2 py-1" style="font-size: 0.7rem;" title="Presensi terikat langsung dari Scan QR Code Digital">
                                                    <i class="bi bi-qr-code-scan me-1"></i>Scan QR (<?= $waktuHadirStr ?> WIB)
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <select name="absensi[<?= $row['siswa_id'] ?>]" class="form-select form-select-sm rounded-3 select-status">
                                                <option value="Hadir" <?= strtolower($currentStatus) === 'hadir' ? 'selected' : '' ?>>Hadir</option>
                                                <option value="Izin" <?= strtolower($currentStatus) === 'izin' ? 'selected' : '' ?>>Izin</option>
                                                <option value="Sakit" <?= strtolower($currentStatus) === 'sakit' ? 'selected' : '' ?>>Sakit</option>
                                                <option value="Alpa" <?= strtolower($currentStatus) === 'alpa' ? 'selected' : '' ?>>Alpa</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="keterangan[<?= $row['siswa_id'] ?>]" class="form-control form-control-sm rounded-3" value="<?= htmlspecialchars($row['keterangan'] ?? '') ?>" placeholder="Opsional (misal: Sakit flu, Izin keperluan keluarga)">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($recap)): ?>
                    <div class="text-end pt-2 border-top">
                        <button type="submit" class="btn btn-success px-5 py-2.5 rounded-pill fw-bold shadow-sm">
                            <i class="bi bi-save me-1"></i> Simpan Rekap Absensi
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</main>

<script>
function setSemuaHadir() {
    const selects = document.querySelectorAll('.select-status');
    selects.forEach(s => s.value = 'Hadir');
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
