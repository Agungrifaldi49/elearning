<?php
$userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
$isAdmin = in_array($userRole, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);

$bulan = sprintf('%02d', (int)($recap['bulan'] ?? date('m')));
$tahun = (int)($recap['tahun'] ?? date('Y'));
$type = $_GET['type'] ?? 'siswa';
if (!$isAdmin) $type = 'siswa';

$namaBulanList = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$namaBulan = $namaBulanList[$bulan] ?? $bulan;

$numDays = $recap['num_days'] ?? 30;
$recapData = $recap['data'] ?? [];

// Load Dynamic Settings from Admin (Logo, School Name, Address, NPSN, Headmaster)
$appSettings = [];
$settingsPath = ROOT_PATH . 'config/settings.json';
if (file_exists($settingsPath)) {
    $appSettings = json_decode(file_get_contents($settingsPath), true) ?: [];
}
if (empty($appSettings)) {
    try {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $appSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    } catch (Throwable $e) {}
}

$schoolName = !empty($appSettings['nama_sekolah']) ? $appSettings['nama_sekolah'] : 'SMK MUTHIA HARAPAN CICALENGKA';
$schoolAddress = !empty($appSettings['alamat']) ? $appSettings['alamat'] : 'Jalan Babakan Peuteuy No. 300, Cicalengka, Kab. Bandung';
$schoolPhone = !empty($appSettings['telepon']) ? $appSettings['telepon'] : '(022) 7950123';
$schoolNpsn = !empty($appSettings['npsn']) ? $appSettings['npsn'] : '69725846';
$headmasterName = !empty($appSettings['kepala_sekolah']) ? $appSettings['kepala_sekolah'] : ($appSettings['nama_kepsek'] ?? 'H. ASEP SAEPULLOH, S. Ag');
$headmasterNip = !empty($appSettings['nip_kepala_sekolah']) ? $appSettings['nip_kepala_sekolah'] : ($appSettings['nip_kepsek'] ?? ($appSettings['nip'] ?? ''));

// Resolve Official School Logo
$rawLogo = $appSettings['logo'] ?? '';
$schoolLogoUrl = null;
if (!empty($rawLogo)) {
    $possibleLogoPaths = [
        'assets/uploads/logo/' . $rawLogo,
        'assets/uploads/' . $rawLogo,
        'assets/images/' . $rawLogo
    ];
    foreach ($possibleLogoPaths as $lp) {
        if (file_exists(ROOT_PATH . $lp)) {
            $schoolLogoUrl = BASE_URL . $lp;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap_Absensi_Bulanan_<?= strtoupper($type) ?>_<?= $namaBulan ?>_<?= $tahun ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #fff;
            font-size: 11pt;
        }
        .kop-surat {
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .table-print th, .table-print td {
            border: 1px solid #000 !important;
            padding: 4px 6px;
            font-size: 9pt;
            text-align: center;
        }
        .table-print th {
            background-color: #f2f2f2 !important;
            font-weight: bold;
        }
        .legend-box {
            font-size: 8.5pt;
            margin-top: 10px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .container-fluid {
                width: 100% !important;
                padding: 0 !important;
            }
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
        }
    </style>
</head>
<body class="py-3">

<div class="container-fluid px-4">
    
    <!-- Action Bar (No Print) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print bg-light p-3 rounded-3 border">
        <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-printer-fill me-2 text-danger"></i>Cetak / Simpan PDF Rekap Absensi Bulanan</h5>
            <small class="text-muted">Dokumen telah disesuaikan secara resmi dalam format cetak Lanskap A4.</small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-danger fw-bold px-4 rounded-pill">
                <i class="bi bi-printer me-1"></i> Cetak / Download PDF
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary fw-semibold px-3 rounded-pill">
                Tutup
            </button>
        </div>
    </div>

    <!-- Kop Surat Sekolah Resmi -->
    <div class="kop-surat d-flex align-items-center justify-content-center gap-3">
        <?php if ($schoolLogoUrl): ?>
            <img src="<?= htmlspecialchars($schoolLogoUrl) ?>" alt="Logo Sekolah" style="width: 75px; height: 75px; object-fit: contain;">
        <?php endif; ?>
        <div class="text-center flex-grow-1">
            <h3 class="fw-bold text-uppercase mb-1" style="font-size: 16pt; letter-spacing: 1px;"><?= htmlspecialchars($schoolName) ?></h3>
            <p class="mb-0 small text-dark"><?= htmlspecialchars($schoolAddress) ?></p>
            <p class="mb-0 small text-dark">NPSN: <?= htmlspecialchars($schoolNpsn) ?> | Telp: <?= htmlspecialchars($schoolPhone) ?> | Website: smkmuthiaharapan.sch.id</p>
        </div>
    </div>

    <!-- Document Header -->
    <div class="text-center mb-3">
        <h5 class="fw-bold text-uppercase mb-1" style="text-decoration: underline; font-size: 13pt;">
            LAPORAN REKAPITULASI PRESENSI BULANAN <?= $type === 'guru' ? 'GURU / GTK' : 'SISWA' ?>
        </h5>
        <p class="mb-0 small">Periode: <b>Bulan <?= $namaBulan ?> <?= $tahun ?></b></p>
    </div>

    <!-- Main Attendance Table -->
    <table class="table table-print w-100 align-middle mb-2">
        <thead>
            <tr>
                <th style="width:25px;">No</th>
                <th style="width:110px;"><?= $type === 'guru' ? 'NIP' : 'NIS/NISN' ?></th>
                <th class="text-start" style="width:200px;">Nama Lengkap</th>
                <th style="width:80px;"><?= $type === 'guru' ? 'Role' : 'Kelas' ?></th>
                <?php for ($d = 1; $d <= $numDays; $d++): ?>
                    <th style="width:22px; padding: 2px;"><?= $d ?></th>
                <?php endfor; ?>
                <th style="width:30px;">H</th>
                <th style="width:30px;">TL</th>
                <th style="width:30px;">S</th>
                <th style="width:30px;">I</th>
                <th style="width:30px;">A</th>
                <th style="width:35px;">Pulang</th>
                <th style="width:40px;">%</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recapData)): ?>
                <tr>
                    <td colspan="<?= $numDays + 11 ?>" class="text-center py-3">Tidak ada data rekap presensi.</td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($recapData as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><code><?= $type === 'guru' ? ($row['nip'] ?: '-') : ($row['nis'] ?: ($row['nisn'] ?: '-')) ?></code></td>
                        <td class="text-start fw-bold"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= $type === 'guru' ? 'Guru/GTK' : htmlspecialchars($row['nama_kelas'] ?: '-') ?></td>
                        <?php for ($d = 1; $d <= $numDays; $d++): 
                            $val = $row['daily'][$d] ?? '-';
                        ?>
                            <td style="padding: 1px;"><?= $val ?></td>
                        <?php endfor; ?>
                        <td class="fw-bold"><?= $row['total_hadir'] ?></td>
                        <td class="fw-bold"><?= $row['total_terlambat'] ?></td>
                        <td class="fw-bold"><?= $row['total_sakit'] ?></td>
                        <td class="fw-bold"><?= $row['total_izin'] ?></td>
                        <td class="fw-bold"><?= $row['total_alpa'] ?></td>
                        <td class="fw-bold"><?= $row['total_pulang'] ?></td>
                        <td class="fw-bold"><?= $row['persentase'] ?>%</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Legend -->
    <div class="legend-box mb-4">
        <b>Keterangan Kode Status:</b>
        <span class="ms-2"><b>H</b> : Hadir Tepat Waktu</span> |
        <span class="ms-2"><b>TL</b> : Terlambat</span> |
        <span class="ms-2"><b>S</b> : Sakit</span> |
        <span class="ms-2"><b>I</b> : Izin</span> |
        <span class="ms-2"><b>A</b> : Alpa</span>
    </div>

    <!-- Signature Block -->
    <div class="row mt-4 pt-2">
        <div class="col-6 text-center">
            <p class="mb-1">Mengetahui,</p>
            <p class="fw-bold mb-5">Waka Kesiswaan / Kurikulum</p>
            <p class="fw-bold mb-0">...................................................</p>
            <p class="small text-muted mb-0">NIP. ...................................................</p>
        </div>
        <div class="col-6 text-center">
            <p class="mb-1">Cicalengka, <?= date('d') ?> <?= $namaBulan ?> <?= $tahun ?></p>
            <p class="fw-bold mb-5">Kepala Sekolah</p>
            <p class="fw-bold mb-0" style="text-decoration: underline;"><?= htmlspecialchars($headmasterName) ?></p>
            <p class="small text-muted mb-0"><?= $headmasterNip ? 'NIP. ' . htmlspecialchars($headmasterNip) : 'NIP. ...................................................' ?></p>
        </div>
    </div>

</div>

<script>
    // Auto-trigger print on page load
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            window.print();
        }, 500);
    });
</script>

</body>
</html>
