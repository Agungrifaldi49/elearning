<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$currentModuleUrl = $_GET['url'] ?? '';
$isAdminRoute = (strpos($currentModuleUrl, 'admin/') === 0 || strtolower(AuthHelper::user()['role_name'] ?? '') === 'administrator');
$scanQrUrl = $isAdminRoute ? BASE_URL . 'index.php?url=admin/scanQr' : BASE_URL . 'index.php?url=guru/scanQr';
$formAbsensiUrl = $isAdminRoute ? BASE_URL . 'index.php?url=admin/absensi' : BASE_URL . 'index.php?url=guru/absensi';
$recapBulananUrl = $isAdminRoute ? BASE_URL . 'index.php?url=admin/recapBulanan' : BASE_URL . 'index.php?url=guru/recapBulanan';
$activeTabParam = $_GET['tab'] ?? 'siswa';
?>
<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-calendar-check-fill text-primary me-2"></i>Rekap Absensi Presensi & QR Code Scanner</h4>
                <p class="text-muted small mb-0">Data presensi otomatis terhubung secara langsung dengan hasil Scan QR Code Siswa dan Guru/GTK.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= $recapBulananUrl ?>" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-bold shadow-xs">
                    <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Rekap Absensi Bulanan
                </a>
                <a href="<?= $scanQrUrl ?>" class="btn btn-success text-white rounded-pill px-3 py-2 fw-bold shadow-xs">
                    <i class="bi bi-qr-code-scan me-1"></i> QR Code Scanner
                </a>
            </div>
        </div>

        <!-- Nav Tabs -->
        <ul class="nav nav-tabs border-bottom-0 mb-3" id="absensiTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTabParam !== 'guru' ? 'active' : '' ?> fw-bold px-4 py-2.5 rounded-top-3" id="siswa-tab" data-bs-toggle="tab" data-bs-target="#siswa-panel" type="button" role="tab" aria-controls="siswa-panel" aria-selected="<?= $activeTabParam !== 'guru' ? 'true' : 'false' ?>">
                    <i class="bi bi-people-fill text-primary me-2"></i>Rekap Presensi Siswa (KBM)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTabParam === 'guru' ? 'active' : '' ?> fw-bold px-4 py-2.5 rounded-top-3" id="guru-tab" data-bs-toggle="tab" data-bs-target="#guru-panel" type="button" role="tab" aria-controls="guru-panel" aria-selected="<?= $activeTabParam === 'guru' ? 'true' : 'false' ?>">
                    <i class="bi bi-person-badge-fill text-success me-2"></i>Rekap Presensi Guru & GTK
                </button>
            </li>
        </ul>

        <div class="tab-content" id="absensiTabContent">
            <!-- TAB 1: PRESENSI SISWA -->
            <div class="tab-pane fade <?= $activeTabParam !== 'guru' ? 'show active' : '' ?>" id="siswa-panel" role="tabpanel" aria-labelledby="siswa-tab">
                <div class="card card-custom p-4 mb-4 border-0 rounded-4 shadow-sm bg-white">
                    <form action="<?= $formAbsensiUrl ?>" method="GET" class="row g-3 align-items-end mb-4">
                        <input type="hidden" name="url" value="<?= $isAdminRoute ? 'admin/absensi' : 'guru/absensi' ?>">
                        <input type="hidden" name="tab" value="siswa">
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-dark">Pilih Jadwal Mengajar</label>
                            <select name="jadwal_id" class="form-select rounded-3" onchange="this.form.submit()">
                                <?php foreach ($jadwalList as $j): ?>
                                    <option value="<?= $j['id'] ?>" <?= $selectedJadwal == $j['id'] ? 'selected' : '' ?>>
                                        <?= $j['hari'] ?> | <?= htmlspecialchars($j['nama_mapel']) ?> - <?= htmlspecialchars($j['nama_kelas']) ?> (<?= substr($j['jam_mulai'],0,5) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Tanggal Absen</label>
                            <input type="date" name="tanggal" class="form-control rounded-3" value="<?= htmlspecialchars($tanggal) ?>" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-3 d-none d-md-block">
                            <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold"><i class="bi bi-filter me-1"></i> Filter Siswa</button>
                        </div>
                    </form>

                    <form action="<?= $formAbsensiUrl ?>&jadwal_id=<?= $selectedJadwal ?>&tanggal=<?= $tanggal ?>&tab=siswa" method="POST">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="tab" value="siswa">

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
                                    <i class="bi bi-save me-1"></i> Simpan Rekap Absensi Siswa
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- TAB 2: REKAP PRESENSI GURU & GTK -->
            <div class="tab-pane fade <?= $activeTabParam === 'guru' ? 'show active' : '' ?>" id="guru-panel" role="tabpanel" aria-labelledby="guru-tab">
                <div class="card card-custom p-4 mb-4 border-0 rounded-4 shadow-sm bg-white">
                    <form action="<?= $formAbsensiUrl ?>" method="GET" class="row g-3 align-items-end mb-4">
                        <input type="hidden" name="url" value="<?= $isAdminRoute ? 'admin/absensi' : 'guru/absensi' ?>">
                        <input type="hidden" name="jadwal_id" value="<?= $selectedJadwal ?>">
                        <input type="hidden" name="tab" value="guru">
                        <div class="col-md-9">
                            <label class="form-label small fw-semibold text-dark"><i class="bi bi-calendar3 text-success me-1"></i> Filter Tanggal Presensi Guru &amp; GTK</label>
                            <input type="date" name="tanggal" class="form-control rounded-3" value="<?= htmlspecialchars($tanggal) ?>" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-3 d-none d-md-block">
                            <button type="submit" class="btn btn-success rounded-3 w-100 fw-bold"><i class="bi bi-filter me-1"></i> Filter Data Guru</button>
                        </div>
                    </form>

                    <form action="<?= $formAbsensiUrl ?>&jadwal_id=<?= $selectedJadwal ?>&tanggal=<?= $tanggal ?>&tab=guru" method="POST">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="tab" value="guru">

                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-journal-text me-1 text-success"></i> Rekap Harian Tenaga Pendidik / GTK</h6>
                                <small class="text-muted">Tanggal: <?= date('d F Y', strtotime($tanggal)) ?></small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success border border-success px-3 py-2">
                                    Hadir: <?= count(array_filter($recapGuru ?? [], fn($g) => !empty($g['waktu_masuk']) || !empty($g['waktu_hadir']) || strtolower($g['status'] ?? '') === 'hadir')) ?> Guru
                                </span>
                                <?php if ($isAdminRoute): ?>
                                <button type="submit" class="btn btn-success text-white rounded-pill px-4 fw-bold shadow-xs">
                                    <i class="bi bi-save me-1"></i> Simpan Presensi Guru
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-hover align-middle small">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>NIP</th>
                                        <th>Nama Lengkap Guru / GTK</th>
                                        <th>Status Presensi</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Pulang</th>
                                        <th>Keterangan / Scan QR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recapGuru)): ?>
                                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data guru terdaftar.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($recapGuru as $idx => $g): 
                                            $gId = $g['guru_id'];
                                            $jamMasuk = !empty($g['waktu_masuk']) ? date('H:i', strtotime($g['waktu_masuk'])) : (!empty($g['waktu_hadir']) ? date('H:i', strtotime($g['waktu_hadir'])) : '-');
                                            $jamPulang = !empty($g['waktu_pulang']) ? date('H:i', strtotime($g['waktu_pulang'])) : '-';
                                            $isHadir = !empty($g['waktu_masuk']) || !empty($g['waktu_hadir']);
                                            $isPulang = !empty($g['waktu_pulang']);
                                            $currentStatus = $g['status'] ?? ($isHadir ? 'Hadir' : 'Alpa');
                                        ?>
                                            <tr class="border-bottom">
                                                <td><span class="badge bg-secondary rounded-circle py-1 px-2"><?= $idx + 1 ?></span></td>
                                                <td><code><?= htmlspecialchars($g['nip'] ?: '-') ?></code></td>
                                                <td class="fw-bold text-dark">
                                                    <i class="bi bi-person-circle me-1 text-primary"></i><?= htmlspecialchars($g['nama_lengkap']) ?>
                                                </td>
                                                <td>
                                                    <?php if ($isAdminRoute): ?>
                                                        <select name="absensi_guru[<?= $gId ?>]" class="form-select form-select-sm rounded-3 select-status-guru" style="min-width: 125px;">
                                                            <option value="Hadir" <?= strtolower($currentStatus) === 'hadir' ? 'selected' : '' ?>>Hadir</option>
                                                            <option value="Terlambat" <?= strtolower($currentStatus) === 'terlambat' ? 'selected' : '' ?>>Terlambat</option>
                                                            <option value="Izin" <?= strtolower($currentStatus) === 'izin' ? 'selected' : '' ?>>Izin</option>
                                                            <option value="Sakit" <?= strtolower($currentStatus) === 'sakit' ? 'selected' : '' ?>>Sakit</option>
                                                            <option value="Alpa" <?= strtolower($currentStatus) === 'alpa' ? 'selected' : '' ?>>Alpa</option>
                                                        </select>
                                                    <?php else: ?>
                                                        <?php if ($isPulang): ?>
                                                            <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1"><i class="bi bi-check-all me-1"></i>Lengkap (Masuk &amp; Pulang)</span>
                                                        <?php elseif ($isHadir): ?>
                                                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Hadir</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1"><i class="bi bi-clock me-1"></i>Belum Presensi</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-bold text-success">
                                                    <?php if ($isHadir): ?>
                                                        <i class="bi bi-box-arrow-in-right me-1"></i><?= $jamMasuk ?> WIB
                                                    <?php else: ?>
                                                        <span class="text-muted fw-normal">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-bold text-primary">
                                                    <?php if ($isPulang): ?>
                                                        <i class="bi bi-box-arrow-right me-1"></i><?= $jamPulang ?> WIB
                                                    <?php else: ?>
                                                        <span class="text-muted fw-normal small"><?= $isHadir ? 'Belum Pulang' : '-' ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($isAdminRoute): ?>
                                                        <input type="text" name="keterangan_guru[<?= $gId ?>]" class="form-control form-control-sm rounded-3" value="<?= htmlspecialchars($g['keterangan'] ?? '') ?>" placeholder="Opsional (Sakit, Dinas Luar)">
                                                    <?php else: ?>
                                                        <?php if ($isHadir): ?>
                                                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size:0.75rem;">
                                                                <i class="bi bi-qr-code-scan me-1 text-success"></i><?= htmlspecialchars($g['keterangan'] ?: 'Presensi Digital Scan QR') ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted small">-</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($isAdminRoute && !empty($recapGuru)): ?>
                            <div class="text-end pt-2 border-top">
                                <button type="submit" class="btn btn-success px-5 py-2.5 rounded-pill fw-bold shadow-sm">
                                    <i class="bi bi-save me-1"></i> Simpan Rekap Presensi Guru &amp; GTK
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function setSemuaHadir() {
    const selects = document.querySelectorAll('.select-status');
    selects.forEach(s => s.value = 'Hadir');
}

document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('#absensiTab button[data-bs-toggle="tab"]');
    tabBtns.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            const targetId = e.target.getAttribute('id');
            const tabName = targetId === 'guru-tab' ? 'guru' : 'siswa';
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', url.toString());
        });
    });
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
