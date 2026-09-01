<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$currentModuleUrl = $_GET['url'] ?? '';
$userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
$isAdmin = in_array($userRole, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);

$baseUrlRoute = $isAdmin ? BASE_URL . 'index.php?url=admin/recapBulanan' : BASE_URL . 'index.php?url=guru/recapBulanan';
$exportCsvUrl = $isAdmin ? BASE_URL . 'index.php?url=admin/exportRecapBulananCsv' : BASE_URL . 'index.php?url=guru/exportRecapBulananCsv';
$exportPdfUrl = $isAdmin ? BASE_URL . 'index.php?url=admin/exportRecapBulananPdf' : BASE_URL . 'index.php?url=guru/exportRecapBulananPdf';

$bulan = sprintf('%02d', (int)($monthlyRecap['bulan'] ?? date('m')));
$tahun = (int)($monthlyRecap['tahun'] ?? date('Y'));
$type = $_GET['type'] ?? 'siswa';
if (!$isAdmin) $type = 'siswa'; // Force teacher to siswa only

$namaBulanList = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$namaBulanSelect = $namaBulanList[$bulan] ?? $bulan;

$numDays = $monthlyRecap['num_days'] ?? 30;
$recapData = $monthlyRecap['data'] ?? [];

// Calculate Overall Aggregates
$totalSubjek = count($recapData);
$totalHadirAll = 0;
$totalTerlambatAll = 0;
$totalPulangAll = 0;
$persentaseSum = 0;

foreach ($recapData as $rd) {
    $totalHadirAll += $rd['total_hadir'];
    $totalTerlambatAll += $rd['total_terlambat'];
    $totalPulangAll += $rd['total_pulang'];
    $persentaseSum += $rd['persentase'];
}
$avgPersentase = ($totalSubjek > 0) ? round($persentaseSum / $totalSubjek, 1) : 0;
?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid py-3">
        
        <!-- Header Page -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bi bi-file-earmark-spreadsheet-fill text-success me-2"></i>Rekap Absensi Presensi Bulanan
                </h4>
                <p class="text-muted small mb-0">Laporan bulanan presensi dan statistik tingkat kehadiran komprehensif (Guru/GTK & Siswa).</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= $exportCsvUrl ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&type=<?= $type ?>&kelas_id=<?= $_GET['kelas_id'] ?? 0 ?>" class="btn btn-outline-success rounded-pill px-3 py-2 fw-semibold shadow-xs">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Download Excel / CSV
                </a>
                <a href="<?= $exportPdfUrl ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&type=<?= $type ?>&kelas_id=<?= $_GET['kelas_id'] ?? 0 ?>" target="_blank" class="btn btn-danger text-white rounded-pill px-3 py-2 fw-bold shadow-xs">
                    <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
                </a>
            </div>
        </div>

        <?php if ($isAdmin): ?>
        <!-- Role Tabs for Admin -->
        <div class="card border-0 rounded-4 shadow-sm p-2 mb-4 bg-white">
            <ul class="nav nav-pills nav-fill gap-2" id="typeTab">
                <li class="nav-item">
                    <a class="nav-link rounded-3 fw-bold py-2 <?= $type === 'siswa' ? 'active bg-primary' : 'text-dark' ?>" href="<?= $baseUrlRoute ?>&type=siswa&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>">
                        <i class="bi bi-people-fill me-1"></i> Rekap Presensi Siswa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-3 fw-bold py-2 <?= $type === 'guru' ? 'active bg-success' : 'text-dark' ?>" href="<?= $baseUrlRoute ?>&type=guru&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>">
                        <i class="bi bi-person-badge-fill me-1"></i> Rekap Presensi Guru / GTK
                    </a>
                </li>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Filter Bar Card -->
        <div class="card border-0 rounded-4 shadow-sm p-4 mb-4 bg-white">
            <form action="<?= $baseUrlRoute ?>" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="url" value="<?= $isAdmin ? 'admin/recapBulanan' : 'guru/recapBulanan' ?>">
                <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-dark mb-1">Pilih Bulan</label>
                    <select name="bulan" class="form-select rounded-3" onchange="this.form.submit()">
                        <?php foreach ($namaBulanList as $mKey => $mName): ?>
                            <option value="<?= $mKey ?>" <?= $bulan === $mKey ? 'selected' : '' ?>><?= $mName ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-dark mb-1">Pilih Tahun</label>
                    <select name="tahun" class="form-select rounded-3" onchange="this.form.submit()">
                        <?php for ($y = date('Y') + 1; $y >= 2024; $y--): ?>
                            <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <?php if ($type === 'siswa'): ?>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-dark mb-1">Filter Kelas</label>
                    <select name="kelas_id" class="form-select rounded-3" onchange="this.form.submit()">
                        <option value="0" <?= (isset($_GET['kelas_id']) && (string)$_GET['kelas_id'] === '0') || !isset($_GET['kelas_id']) ? 'selected' : '' ?>>-- Seluruh Kelas --</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= (isset($_GET['kelas_id']) && (int)$_GET['kelas_id'] === (int)$k['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kelas']) ?> (<?= htmlspecialchars($k['jurusan'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="col-12 col-md-2 d-none d-md-block">
                    <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Summary Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-4 border-primary">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-semibold d-block">Total <?= $type === 'guru' ? 'Guru/GTK' : 'Siswa' ?></small>
                            <h3 class="fw-bold mb-0 text-dark"><?= $totalSubjek ?></h3>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-3">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-4 border-success">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-semibold d-block">Rata-Rata Kehadiran</small>
                            <h3 class="fw-bold mb-0 text-success"><?= $avgPersentase ?>%</h3>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-3">
                            <i class="bi bi-pie-chart-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-4 border-warning">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-semibold d-block">Total Keterlambatan</small>
                            <h3 class="fw-bold mb-0 text-warning"><?= $totalTerlambatAll ?> <small class="fs-6 text-muted">kali</small></h3>
                        </div>
                        <div class="p-3 bg-warning-subtle text-warning rounded-3">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-4 border-info">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-semibold d-block">Total Presensi Pulang</small>
                            <h3 class="fw-bold mb-0 text-info"><?= $totalPulangAll ?> <small class="fs-6 text-muted">rekam</small></h3>
                        </div>
                        <div class="p-3 bg-info-subtle text-info rounded-3">
                            <i class="bi bi-box-arrow-right fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Attendance Matrix Table Card -->
        <div class="card border-0 rounded-4 shadow-sm p-4 bg-white mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="fw-bold mb-0 text-dark">
                    Matriks Presensi <?= $type === 'guru' ? 'Guru & GTK' : 'Siswa' ?> Bulan <?= $namaBulanSelect ?> <?= $tahun ?>
                </h5>
                <!-- Legend -->
                <div class="d-flex flex-wrap gap-2 small">
                    <span class="badge bg-success"><i class="bi bi-check me-1"></i>H (Hadir)</span>
                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>TL (Terlambat)</span>
                    <span class="badge bg-info text-dark"><i class="bi bi-hospital me-1"></i>S (Sakit)</span>
                    <span class="badge bg-secondary"><i class="bi bi-card-text me-1"></i>I (Izin)</span>
                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>A (Alpa)</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center small mb-0" style="font-size: 0.78rem;">
                    <thead class="table-light align-middle">
                        <tr>
                            <th style="width:30px;">No</th>
                            <th style="min-width:100px;"><?= $type === 'guru' ? 'NIP' : 'NIS/NISN' ?></th>
                            <th style="min-width:160px;" class="text-start">Nama Lengkap</th>
                            <th style="min-width:100px;"><?= $type === 'guru' ? 'Role' : 'Kelas' ?></th>
                            <?php for ($d = 1; $d <= $numDays; $d++): ?>
                                <th style="width:24px; padding: 4px 2px;"><?= $d ?></th>
                            <?php endfor; ?>
                            <th class="bg-success-subtle text-success">H</th>
                            <th class="bg-warning-subtle text-warning">TL</th>
                            <th class="bg-info-subtle text-info">S</th>
                            <th class="bg-secondary-subtle">I</th>
                            <th class="bg-danger-subtle text-danger">A</th>
                            <th class="bg-primary-subtle text-primary">Pulang</th>
                            <th class="bg-light fw-bold">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recapData)): ?>
                            <tr>
                                <td colspan="<?= $numDays + 11 ?>" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                    Belum ada data presensi untuk periode bulan ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($recapData as $row): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><code><?= $type === 'guru' ? ($row['nip'] ?: '-') : ($row['nis'] ?: ($row['nisn'] ?: '-')) ?></code></td>
                                    <td class="fw-bold text-start text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                    <td><?= $type === 'guru' ? '<span class="badge bg-warning-subtle text-dark border">Guru</span>' : htmlspecialchars($row['nama_kelas'] ?: '-') ?></td>
                                    <?php for ($d = 1; $d <= $numDays; $d++): 
                                        $val = $row['daily'][$d] ?? '-';
                                        $badgeBg = 'text-muted';
                                        if ($val === 'H') $badgeBg = 'bg-success text-white rounded-circle fw-bold';
                                        elseif ($val === 'TL') $badgeBg = 'bg-warning text-dark rounded-circle fw-bold';
                                        elseif ($val === 'S') $badgeBg = 'bg-info text-dark rounded-circle fw-bold';
                                        elseif ($val === 'I') $badgeBg = 'bg-secondary text-white rounded-circle fw-bold';
                                        elseif ($val === 'A') $badgeBg = 'bg-danger text-white rounded-circle fw-bold';
                                    ?>
                                        <td style="padding: 2px;"><span class="d-inline-block px-1 py-0.5 <?= $badgeBg ?>"><?= $val ?></span></td>
                                    <?php endfor; ?>
                                    <td class="fw-bold text-success bg-success-subtle"><?= $row['total_hadir'] ?></td>
                                    <td class="fw-bold text-warning bg-warning-subtle"><?= $row['total_terlambat'] ?></td>
                                    <td class="fw-bold text-info bg-info-subtle"><?= $row['total_sakit'] ?></td>
                                    <td class="fw-bold text-muted bg-secondary-subtle"><?= $row['total_izin'] ?></td>
                                    <td class="fw-bold text-danger bg-danger-subtle"><?= $row['total_alpa'] ?></td>
                                    <td class="fw-bold text-primary bg-primary-subtle"><?= $row['total_pulang'] ?></td>
                                    <td class="fw-bold text-dark bg-light"><?= $row['persentase'] ?>%</td>
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
