<?php
// Load dynamic settings for School Info
$sidebarSettings = [];
$settingsPath = ROOT_PATH . 'config/settings.json';
if (file_exists($settingsPath)) {
    $sidebarSettings = json_decode(file_get_contents($settingsPath), true) ?: [];
}
$namaSekolah = $sidebarSettings['nama_sekolah'] ?? 'SMK MUTHIA HARAPAN CICALENGKA';
$alamatSekolah = $sidebarSettings['alamat'] ?? 'Jl. Cicalengka - Majalaya No. 49, Cicalengka, Kab. Bandung, Jawa Barat';
$teleponSekolah = $sidebarSettings['telepon'] ?? '(022) 7949-xxxx';
$emailSekolah = $sidebarSettings['email'] ?? 'info@smkmuthiaharapan.sch.id';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Eksekutif Keuangan & Realisasi SPP - <?= htmlspecialchars($namaSekolah) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000000;
            background: #ffffff;
            padding: 20px;
        }
        .kop-surat {
            border-bottom: 3px double #000000;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }
        .table-print {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
        }
        .table-print th, .table-print td {
            border: 1px solid #000000;
            padding: 6px 10px;
        }
        .table-print th {
            background-color: #f2f2f2 !important;
            text-align: center;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print mb-4 d-flex justify-content-between align-items-center bg-light p-3 rounded border">
        <div>
            <h6 class="fw-bold mb-0 text-dark">Pratinjau Cetak Laporan Keuangan Eksekutif</h6>
            <small class="text-muted">Gunakan tombol di kanan untuk mencetak langsung atau menyimpan sebagai PDF.</small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold px-4">
                <i class="bi bi-printer"></i> Cetak Dokumen / Simpan PDF
            </button>
            <button onclick="window.close()" class="btn btn-secondary btn-sm px-3">Tutup</button>
        </div>
    </div>

    <!-- Kop Surat Resmi -->
    <div class="kop-surat text-center">
        <h3 class="fw-bold mb-1 text-uppercase" style="letter-spacing: 1px;"><?= htmlspecialchars($namaSekolah) ?></h3>
        <div class="small mb-1">TERAKREDITASI "A" &bull; NSS: 402020811049 &bull; NPSN: 69824611</div>
        <div class="small mb-1"><?= htmlspecialchars($alamatSekolah) ?></div>
        <div class="small">Telp: <?= htmlspecialchars($teleponSekolah) ?> &bull; Email: <?= htmlspecialchars($emailSekolah) ?> &bull; Website: www.smkmuthiaharapan.sch.id</div>
    </div>

    <!-- Judul Dokumen -->
    <div class="text-center mb-4">
        <h5 class="fw-bold text-uppercase mb-1 text-decoration-underline">LAPORAN EKSEKUTIF REALISASI PEMBAYARAN SPP & IURAN SISWA</h5>
        <div class="small fw-semibold">TAHUN AJARAN 2025/2026 — PERIODE SEMESTER GANJIL</div>
        <div class="small text-muted">Tanggal Cetak: <?= date('d F Y, H:i') ?> WIB</div>
    </div>

    <!-- 1. Ringkasan Global Keuangan -->
    <h6 class="fw-bold text-uppercase mb-2">I. Ringkasan Realisasi Penerimaan Sekolah</h6>
    <table class="table-print mb-4">
        <thead>
            <tr>
                <th>Total Target Anggaran</th>
                <th>Realisasi Kas Masuk</th>
                <th>Sisa Piutang / Tunggakan</th>
                <th>Tingkat Efektivitas (%)</th>
                <th>Siswa Lunas</th>
                <th>Siswa Menunggak</th>
            </tr>
        </thead>
        <tbody>
            <tr class="text-center fw-bold">
                <td>Rp <?= number_format($global['total_target'], 0, ',', '.') ?></td>
                <td class="text-success">Rp <?= number_format($global['total_masuk'], 0, ',', '.') ?></td>
                <td class="text-danger">Rp <?= number_format($global['total_piutang'], 0, ',', '.') ?></td>
                <td><?= $global['rate_pelunasan'] ?>%</td>
                <td><?= $global['siswa_lunas'] ?> Siswa</td>
                <td><?= $global['siswa_menunggak'] ?> Siswa</td>
            </tr>
        </tbody>
    </table>

    <!-- 2. Breakdown per Pos Pembayaran -->
    <h6 class="fw-bold text-uppercase mb-2">II. Rincian Realisasi Penerimaan Berdasarkan Pos Pembayaran</h6>
    <table class="table-print mb-4">
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 32%;">Pos / Jenis Pembayaran</th>
                <th style="width: 20%;">Target Nominal</th>
                <th style="width: 20%;">Realisasi Masuk</th>
                <th style="width: 20%;">Sisa Piutang</th>
                <th style="width: 15%;">Capaian (%)</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($breakdownKategori as $kat): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($kat['jenis_pembayaran']) ?></td>
                    <td>Rp <?= number_format($kat['target_nominal'], 0, ',', '.') ?></td>
                    <td class="text-success">Rp <?= number_format($kat['realisasi_nominal'], 0, ',', '.') ?></td>
                    <td class="text-danger">Rp <?= number_format($kat['sisa_nominal'], 0, ',', '.') ?></td>
                    <td class="text-center fw-bold"><?= $kat['persentase'] ?>%</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- 3. Breakdown per Rombel Kelas -->
    <h6 class="fw-bold text-uppercase mb-2">III. Rekapitulasi Tingkat Kepatuhan Pembayaran Per-Rombel Kelas</h6>
    <table class="table-print mb-4">
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 32%;">Rombel Kelas</th>
                <th style="width: 15%;">Total Siswa</th>
                <th style="width: 22%;">Target Penerimaan</th>
                <th style="width: 23%;">Realisasi Masuk</th>
                <th style="width: 15%;">Capaian (%)</th>
            </tr>
        </thead>
        <tbody>
            <?php $noK = 1; foreach ($breakdownKelas as $bk): ?>
                <tr>
                    <td class="text-center"><?= $noK++ ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($bk['nama_kelas']) ?></td>
                    <td class="text-center"><?= (int)$bk['total_siswa'] ?> Siswa</td>
                    <td>Rp <?= number_format($bk['target_kelas'] ?? 0, 0, ',', '.') ?></td>
                    <td class="text-success">Rp <?= number_format($bk['realisasi_kelas'] ?? 0, 0, ',', '.') ?></td>
                    <td class="text-center fw-bold"><?= $bk['persentase_kelas'] ?? 0 ?>%</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Tanda Tangan Resmi Pengesahan -->
    <div class="row mt-5" style="page-break-inside: avoid;">
        <div class="col-6 text-center">
            <div class="small mb-1">Mengetahui,</div>
            <div class="fw-bold small mb-5">Kepala Tata Usaha & Keuangan,</div>
            <div class="fw-bold text-decoration-underline">Dra. Hj. Siti Aminah, M.Pd</div>
            <div class="small">NIP. 19780415 200501 2 008</div>
        </div>
        <div class="col-6 text-center">
            <div class="small mb-1">Cicalengka, <?= date('d F Y') ?></div>
            <div class="fw-bold small mb-5">Kepala SMK Muthia Harapan,</div>
            <div class="fw-bold text-decoration-underline">H. Dadang Suhendar, S.T., M.M.</div>
            <div class="small">NIP. 19720311 199802 1 004</div>
        </div>
    </div>

</body>
</html>
