<?php
// Terbilang Helper function
function terbilang($angka) {
    $angka = abs((float)$angka);
    $baca = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
    $terbilang = "";

    if ($angka < 12) {
        $terbilang = " " . $baca[$angka];
    } elseif ($angka < 20) {
        $terbilang = terbilang($angka - 10) . " Belas";
    } elseif ($angka < 100) {
        $terbilang = terbilang($angka / 10) . " Puluh" . terbilang($angka % 10);
    } elseif ($angka < 200) {
        $terbilang = " Seratus" . terbilang($angka - 100);
    } elseif ($angka < 1000) {
        $terbilang = terbilang($angka / 100) . " Ratus" . terbilang($angka % 100);
    } elseif ($angka < 2000) {
        $terbilang = " Seribu" . terbilang($angka - 1000);
    } elseif ($angka < 1000000) {
        $terbilang = terbilang($angka / 1000) . " Ribu" . terbilang($angka % 1000);
    } elseif ($angka < 1000000000) {
        $terbilang = terbilang($angka / 1000000) . " Juta" . terbilang($angka % 1000000);
    }
    return trim($terbilang);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran - <?= htmlspecialchars($slip['nomor_transaksi'] ?? 'Slip') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000000;
            background: #ffffff;
            padding: 20px;
        }
        .slip-box {
            max-width: 650px;
            margin: 0 auto;
            border: 2px solid #000000;
            padding: 24px;
        }
        .kop-slip {
            border-bottom: 2px double #000000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print mb-4 d-flex justify-content-between align-items-center bg-light p-3 rounded border" style="max-width: 650px; margin: 0 auto 20px auto;">
        <div>
            <h6 class="fw-bold mb-0 text-dark">Bukti Pembayaran Digital</h6>
            <small class="text-muted">Siap dicetak sebagai bukti transaksi sah sekolah.</small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold px-3">
                Cetak Slip (PDF)
            </button>
            <a href="<?= BASE_URL ?>index.php?url=siswa/pembayaran" class="btn btn-secondary btn-sm px-3">Kembali</a>
        </div>
    </div>

    <div class="slip-box">
        <!-- Kop Surat -->
        <div class="kop-slip text-center">
            <h4 class="fw-bold mb-0 text-uppercase">SMK MUTHIA HARAPAN CICALENGKA</h4>
            <div class="small">BADAN PENGELOLA KEUANGAN & TATA USAHA SEKOLAH</div>
            <div class="small text-muted" style="font-size: 8pt;">Jl. Cicalengka - Majalaya No. 49, Cicalengka, Kab. Bandung &bull; Telp: (022) 7949-xxxx</div>
        </div>

        <div class="text-center mb-3">
            <h6 class="fw-bold text-uppercase mb-0 text-decoration-underline">BUKTI PENERIMAAN PEMBAYARAN</h6>
            <small class="font-monospace">No. Transaksi: <?= htmlspecialchars($slip['nomor_transaksi']) ?></small>
        </div>

        <table class="table table-sm table-borderless small mb-3">
            <tr>
                <td style="width: 25%; font-weight: bold;">Telah Terima Dari</td>
                <td style="width: 2%;">:</td>
                <td class="fw-bold text-uppercase"><?= htmlspecialchars($slip['nama_lengkap']) ?></td>
            </tr>
            <tr>
                <td class="fw-bold">NIS / NISN</td>
                <td>:</td>
                <td><?= htmlspecialchars($slip['nis'] ?? '-') ?> / <?= htmlspecialchars($slip['nisn'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="fw-bold">Rombel Kelas / Jurusan</td>
                <td>:</td>
                <td><?= htmlspecialchars($slip['nama_kelas'] ?? '-') ?> &bull; <?= htmlspecialchars($slip['nama_jurusan'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="fw-bold">Untuk Pembayaran</td>
                <td>:</td>
                <td class="fw-bold text-primary"><?= htmlspecialchars($slip['nama_tagihan']) ?> (<?= htmlspecialchars($slip['periode_bulan'] ?? '-') ?>)</td>
            </tr>
            <tr>
                <td class="fw-bold">Tanggal Pembayaran</td>
                <td>:</td>
                <td><?= date('d F Y, H:i', strtotime($slip['tanggal_bayar'])) ?> WIB</td>
            </tr>
            <tr>
                <td class="fw-bold">Metode / Kanal</td>
                <td>:</td>
                <td><?= htmlspecialchars($slip['metode_pembayaran']) ?> (<?= htmlspecialchars($slip['channel'] ?? 'Loket Keuangan') ?>)</td>
            </tr>
            <tr>
                <td class="fw-bold">Terbilang</td>
                <td>:</td>
                <td class="fst-italic fw-bold" style="background-color: #f8fafc; padding: 4px 8px; border: 1px solid #e2e8f0;">
                    ## <?= terbilang($slip['nominal_bayar']) ?> Rupiah ##
                </td>
            </tr>
        </table>

        <div class="p-2 border border-2 border-dark text-center my-3 bg-light">
            <div class="small fw-bold text-uppercase text-muted">Jumlah Dibayar:</div>
            <h3 class="fw-bold text-dark mb-0">Rp <?= number_format($slip['nominal_bayar'], 0, ',', '.') ?></h3>
            <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold text-uppercase mt-1" style="letter-spacing: 1px;">
                LUNAS &bull; SAH DIVERIFIKASI SISTEM
            </span>
        </div>

        <div class="row mt-4 small">
            <div class="col-6">
                <div class="text-muted" style="font-size: 7.5pt;">
                    <strong>Catatan:</strong>
                    <ul class="ps-3 mb-0">
                        <li>Bukti ini merupakan dokumen elektronik yang sah dari SMK Muthia Harapan Cicalengka.</li>
                        <li>Harap disimpan sebagai arsip pembayaran resmi siswa.</li>
                    </ul>
                </div>
            </div>
            <div class="col-6 text-center">
                <div>Cicalengka, <?= date('d M Y', strtotime($slip['tanggal_bayar'])) ?></div>
                <div class="mb-5">Bagian Keuangan & Kasir Sekolah,</div>
                <div class="fw-bold text-decoration-underline">Petugas Keuangan TU</div>
            </div>
        </div>
    </div>

</body>
</html>
