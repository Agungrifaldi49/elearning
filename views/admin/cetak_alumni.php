<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Data Alumni - SMK Muthia Harapan Cicalengka</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            color: #212529;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .print-container {
            max-width: 1050px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border-radius: 8px;
        }
        .no-print-bar {
            max-width: 1050px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #0d6efd;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-print:hover { background: #0b5ed7; }
        .btn-back {
            background: #6c757d;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
        }
        .kop-surat {
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 25px;
            text-align: center;
        }
        .kop-surat h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-surat h3 {
            margin: 4px 0;
            font-size: 18px;
            font-weight: 700;
            color: #0d6efd;
        }
        .kop-surat p {
            margin: 2px 0;
            font-size: 13px;
            color: #555;
        }
        .judul-laporan {
            text-align: center;
            margin-bottom: 25px;
        }
        .judul-laporan h4 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .judul-laporan p {
            margin: 5px 0 0;
            font-size: 13px;
            color: #666;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 30px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #333;
            padding: 8px 10px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f1f3f5;
            font-weight: 700;
            text-align: center;
        }
        .text-center { text-align: center !important; }
        .summary-box {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            font-size: 13px;
        }
        .summary-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px 15px;
            border-radius: 6px;
            flex: 1;
        }
        .summary-item strong { color: #0d6efd; }
        .ttd-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            page-break-inside: avoid;
        }
        .ttd-box {
            width: 250px;
            text-align: center;
            font-size: 13px;
        }
        .ttd-box .line {
            margin-top: 70px;
            font-weight: 700;
            text-decoration: underline;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .print-container {
                box-shadow: none;
                padding: 0;
                width: 100%;
            }
            .no-print-bar { display: none !important; }
            table.data-table th {
                background-color: #eee !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <a href="<?= BASE_URL ?>index.php?url=admin/alumni" class="btn-back"><i class="bi bi-arrow-left"></i> Kembali ke Sistem</a>
        <button onclick="window.print()" class="btn-print"><i class="bi bi-printer-fill"></i> Cetak / Simpan PDF</button>
    </div>

    <div class="print-container">
        <!-- Kop Surat -->
        <div class="kop-surat">
            <h2>SMK MUTHIA HARAPAN CICALENGKA</h2>
            <h3>LAPORAN RESMI DATA ALUMNI & KELULUSAN</h3>
            <p>Jl. Raya Cicalengka - Majalaya No. 12, Kec. Cicalengka, Kab. Bandung, Jawa Barat</p>
            <p>Email: info@smkmuthia.sch.id | Website: https://smkmuthia.sch.id</p>
        </div>

        <!-- Judul -->
        <div class="judul-laporan">
            <h4>DAFTAR PESERTA DIDIK LULUS (ALUMNI)</h4>
            <p>Dicetak pada tanggal: <?= date('d F Y, H:i') ?> WIB</p>
        </div>

        <!-- Ringkasan Singkat -->
        <div class="summary-box">
            <div class="summary-item">Total Alumni: <strong><?= count($alumniList) ?> Orang</strong></div>
            <div class="summary-item">Laki-laki: <strong><?= count(array_filter($alumniList, fn($x) => ($x['jenis_kelamin'] ?? 'L') === 'L')) ?> Orang</strong></div>
            <div class="summary-item">Perempuan: <strong><?= count(array_filter($alumniList, fn($x) => ($x['jenis_kelamin'] ?? '') === 'P')) ?> Orang</strong></div>
        </div>

        <!-- Tabel Data -->
        <table class="data-table">
            <thead>
                <tr>
                    <th width="30">No</th>
                    <th>NIS</th>
                    <th>NISN</th>
                    <th>Nama Lengkap</th>
                    <th class="text-center" width="40">JK</th>
                    <th>Kompetensi Keahlian (Jurusan)</th>
                    <th>Kelas Terakhir</th>
                    <th>No. Telepon Siswa</th>
                    <th>No. Orang Tua</th>
                    <th>Alamat Rumah</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($alumniList)): ?>
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 20px;">Tidak ada data alumni yang sesuai filter.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($alumniList as $a): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($a['nis'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($a['nisn'] ?: '-') ?></td>
                            <td><strong><?= htmlspecialchars($a['nama_lengkap']) ?></strong></td>
                            <td class="text-center"><?= htmlspecialchars($a['jenis_kelamin'] ?? 'L') ?></td>
                            <td><?= htmlspecialchars($a['nama_jurusan'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($a['nama_kelas'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($a['no_telepon'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($a['no_ortu'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($a['alamat'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Tanda Tangan -->
        <div class="ttd-section">
            <div class="ttd-box">
                <p>Mengetahui,<br>Kepala Sekolah</p>
                <div class="line">H. ASEP SAEPULLOH, S.Ag</div>
                <small>NIP / NUPTK: -</small>
            </div>
            <div class="ttd-box">
                <p>Bandung, <?= date('d F Y') ?><br>Administrator Data</p>
                <div class="line"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Administrator Utama') ?></div>
                <small>Staf Administrasi & Kesiswaan</small>
            </div>
        </div>
    </div>

</body>
</html>
