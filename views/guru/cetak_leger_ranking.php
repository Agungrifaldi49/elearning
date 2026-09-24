<?php
/**
 * Dokumen Cetak Resmi: Leger Nilai & Peringkat Hasil Belajar Peserta Didik
 * SMK Muthia Harapan Cicalengka
 * Format Khusus Siap Print Kertas Landscape (A4/F4)
 */

// Resolusi Logo Sekolah Dinamis & Aman
$rawLogo = $settings['logo'] ?? '';
$logoUrl = '';
if (!empty($rawLogo)) {
    if (strpos($rawLogo, 'http') === 0) {
        $logoUrl = $rawLogo;
    } elseif (strpos($rawLogo, 'assets/') === 0) {
        $logoUrl = BASE_URL . $rawLogo;
    } else {
        $logoUrl = BASE_URL . 'assets/uploads/logo/' . $rawLogo;
    }
}
if (empty($logoUrl)) {
    $logoFiles = @glob(ROOT_PATH . 'assets/uploads/logo/*.*');
    if (!empty($logoFiles)) {
        $logoUrl = BASE_URL . 'assets/uploads/logo/' . basename(end($logoFiles));
    } else {
        $logoUrl = BASE_URL . 'assets/images/logo.png';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leger Nilai & Peringkat <?= htmlspecialchars($selectedKelas['nama_kelas'] ?? '') ?> - SMK Muthia Harapan</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Times+New+Roman&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', Arial, sans-serif !important;
            background-color: #f1f5f9;
            color: #000;
            font-size: 0.74rem;
            line-height: 1.3;
        }

        /* TOOLBAR LAYAR (Hanya tampil di monitor, sembunyi saat print) */
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 9999;
            background: #ffffff;
            border-bottom: 2px solid #cbd5e1;
            padding: 10px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        .btn-action-print {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 8px 22px;
            font-size: 0.88rem;
            font-weight: 700;
            border-radius: 9999px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
            transition: all 0.2s;
        }
        .btn-action-print:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        .btn-action-close {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 8px 18px;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 9999px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-action-close:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        /* SHEET CETAK LANDSCAPE RESMI */
        .print-sheet {
            background: #ffffff;
            width: 100%;
            max-width: 1380px;
            margin: 20px auto;
            padding: 24px 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 4px;
        }

        /* KOP SURAT SEKOLAH RESMI */
        .kop-header {
            display: flex;
            align-items: center;
            gap: 18px;
            padding-bottom: 12px;
            border-bottom: 3px double #000000;
            margin-bottom: 14px;
        }
        .kop-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .kop-text {
            flex-grow: 1;
            text-align: center;
        }
        .kop-text .instansi {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 0;
            color: #1e293b;
        }
        .kop-text .nama-sekolah {
            font-size: 1.25rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 2px 0;
            color: #0f172a;
        }
        .kop-text .npsn-akred {
            font-size: 0.72rem;
            font-weight: 600;
            color: #334155;
            margin: 0;
        }
        .kop-text .alamat {
            font-size: 0.68rem;
            color: #475569;
            margin: 2px 0 0 0;
            line-height: 1.3;
        }

        /* JUDUL DOKUMEN */
        .doc-title-box {
            text-align: center;
            margin-bottom: 14px;
        }
        .doc-title-box h5 {
            font-size: 1rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
            text-decoration: underline;
            color: #000;
        }
        .doc-title-box span {
            font-size: 0.76rem;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
        }

        /* METADATA ROMBEL (2 Kolom) */
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 0.75rem;
            line-height: 1.35;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        /* TABEL LEGER NILAI */
        .table-leger {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.72rem;
            margin-bottom: 14px;
            background: #ffffff;
        }
        .table-leger th, 
        .table-leger td {
            border: 1px solid #1e293b;
            padding: 4px 4px;
            vertical-align: middle;
        }
        .table-leger thead th {
            background-color: #f1f5f9 !important;
            font-weight: 800;
            text-align: center;
            color: #0f172a;
        }
        .table-leger tbody td {
            color: #000;
        }
        .table-leger tfoot td {
            background-color: #f8fafc !important;
            font-weight: 800;
        }

        /* BADGE MEDAL PRINT */
        .rank-gold {
            background-color: #fef3c7 !important;
            font-weight: 800;
        }
        .rank-silver {
            background-color: #f1f5f9 !important;
            font-weight: 700;
        }
        .rank-bronze {
            background-color: #fff1f2 !important;
            font-weight: 700;
        }

        /* TANDA TANGAN RESMI */
        .signature-section {
            margin-top: 18px;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            font-size: 0.76rem;
            text-align: center;
        }
        .signature-table td {
            padding: 4px;
            vertical-align: top;
        }
        .sign-space {
            height: 65px;
        }
        .sign-name {
            font-weight: 800;
            text-decoration: underline;
            color: #000;
        }

        /* PRINT STYLING */
        @media print {
            @page {
                size: landscape;
                margin: 8mm 10mm;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                font-size: 0.68rem !important;
            }
            .print-toolbar {
                display: none !important;
            }
            .print-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            .table-leger th, 
            .table-leger td {
                padding: 3px 2px !important;
                font-size: 0.68rem !important;
            }
        }
    </style>
</head>
<body>

    <!-- TOOLBAR ATAS (MONITOR ONLY) -->
    <div class="print-toolbar">
        <div class="d-flex align-items-center gap-3">
            <button onclick="window.print()" class="btn-action-print">
                <i class="bi bi-printer-fill fs-6"></i> Cetak Leger Sekarang (Landscape)
            </button>
            <span class="text-muted small d-none d-md-inline">
                <i class="bi bi-info-circle me-1"></i>Format Dokumen Resmi Siap Cetak (A4 / F4 Landscape). Gunakan opsi <em>Save as PDF</em> atau cetak langsung.
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= BASE_URL ?>index.php?url=guru/rankingKelas&kelas_id=<?= $selectedKelasId ?>" class="btn-action-close">
                <i class="bi bi-arrow-left"></i> Kembali ke Layar Leger
            </a>
            <button onclick="window.close()" class="btn-action-close">
                <i class="bi bi-x-lg"></i> Tutup
            </button>
        </div>
    </div>

    <!-- LEMBAR DOKUMEN CETAK UTAMA -->
    <div class="print-sheet">
        <!-- KOP SURAT SEKOLAH -->
        <div class="kop-header">
            <?php if (!empty($logoUrl)): ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Sekolah" class="kop-logo">
            <?php endif; ?>
            <div class="kop-text">
                <div class="instansi">YAYASAN PENDIDIKAN MUTHI'AH</div>
                <div class="nama-sekolah"><?= htmlspecialchars($settings['nama_sekolah'] ?? 'SMK MUTHIA HARAPAN CICALENGKA') ?></div>
                <div class="npsn-akred">
                    NPSN: <?= htmlspecialchars($settings['npsn'] ?? '69725846') ?> | AKREDITASI: "<?= htmlspecialchars($settings['akreditasi'] ?? 'B') ?>"
                </div>
                <div class="alamat">
                    <?= htmlspecialchars(!empty($settings['alamat']) ? $settings['alamat'] : (!empty($settings['alamat_sekolah']) ? $settings['alamat_sekolah'] : 'Jalan Babakan Peuteuy Nomor 300, Desa Babakanpeuteuy, Kecamatan Cicalengka, Kabupaten Bandung, Jawa Barat')) ?>
                    <?php if (!empty($settings['telepon'])): ?> | Telp: <?= htmlspecialchars($settings['telepon']) ?><?php endif; ?><br>
                    Website: <?= htmlspecialchars(!empty($settings['website']) ? $settings['website'] : 'www.smkmuthiaharapan.sch.id') ?> | Email: <?= htmlspecialchars(!empty($settings['email']) ? $settings['email'] : (!empty($settings['landing_email']) ? $settings['landing_email'] : 'info@smkmh-cicalengka.sch.id')) ?>
                </div>
            </div>
        </div>

        <!-- JUDUL DOKUMEN -->
        <div class="doc-title-box">
            <h5>LEGER NILAI & PERINGKAT HASIL BELAJAR PESERTA DIDIK</h5>
            <span>TAHUN AJARAN <?= htmlspecialchars($activeTa['tahun'] ?? '2026/2027') ?> — SEMESTER <?= strtoupper(htmlspecialchars($activeSemester ?? 'GANJIL')) ?></span>
        </div>

        <!-- METADATA ROMBEL -->
        <table class="meta-table">
            <tr>
                <td style="width: 15%; font-weight: 600;">Satuan Pendidikan</td>
                <td style="width: 2%;">:</td>
                <td style="width: 33%; font-weight: 700;"><?= htmlspecialchars($settings['nama_sekolah'] ?? 'SMK MUTHIA HARAPAN CICALENGKA') ?></td>

                <td style="width: 18%; font-weight: 600;">Wali Kelas</td>
                <td style="width: 2%;">:</td>
                <td style="width: 30%; font-weight: 700;"><?= htmlspecialchars($waliKelas['nama_lengkap'] ?? '-') ?></td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Kelas / Rombel</td>
                <td>:</td>
                <td style="font-weight: 700;"><?= htmlspecialchars($selectedKelas['nama_kelas'] ?? '-') ?></td>

                <td style="font-weight: 600;">NIP / NUPTK</td>
                <td>:</td>
                <td><?= htmlspecialchars(!empty($waliKelas['nip']) ? $waliKelas['nip'] : '-') ?></td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Kompetensi Keahlian</td>
                <td>:</td>
                <td style="font-weight: 700;"><?= htmlspecialchars($selectedKelas['nama_jurusan'] ?? '-') ?></td>

                <td style="font-weight: 600;">Jumlah Siswa</td>
                <td>:</td>
                <td><strong><?= $countSiswa ?></strong> Peserta Didik (<?= count($mapelList) ?> Mata Pelajaran)</td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Kurikulum / Fase</td>
                <td>:</td>
                <td><?= htmlspecialchars($kurInfo['nama_kurikulum'] ?? 'Kurikulum Merdeka') ?></td>

                <td style="font-weight: 600;">Tanggal Cetak</td>
                <td>:</td>
                <td><?= date('d F Y') ?></td>
            </tr>
        </table>

        <!-- TABEL LEGER NILAI MATRIKS PER MAPEL & RANKING -->
        <table class="table-leger text-center align-middle">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 40px;">Rank</th>
                    <th rowspan="2" style="width: 80px;">NIS</th>
                    <th rowspan="2" style="width: 80px;">NISN</th>
                    <th rowspan="2" style="min-width: 180px; text-align: left; padding-left: 6px;">Nama Lengkap Peserta Didik</th>
                    
                    <!-- Header Mata Pelajaran -->
                    <?php if (!empty($mapelList)): ?>
                        <th colspan="<?= count($mapelList) ?>" style="background-color: #e2e8f0 !important;">MATA PELAJARAN (NILAI AKHIR / KKTP)</th>
                    <?php endif; ?>

                    <th rowspan="2" style="width: 65px; background-color: #e0e7ff !important;">Total Skor</th>
                    <th rowspan="2" style="width: 60px; background-color: #fef3c7 !important;">Rata-Rata</th>
                    <th rowspan="2" style="width: 45px;">Pred</th>
                    <th rowspan="2" style="width: 70px;">Ketuntasan</th>
                </tr>
                <tr>
                    <?php foreach ($mapelList as $m): ?>
                        <th style="font-size: 0.68rem; min-width: 100px; max-width: 160px; padding: 4px 3px; line-height: 1.25;" title="<?= htmlspecialchars($m['nama_mapel']) ?>">
                            <div style="font-weight: 700; color: #000;"><?= htmlspecialchars($m['nama_mapel']) ?></div>
                            <span style="font-size: 0.6rem; font-weight: normal; color: #475569;">
                                <?= !empty($m['kode_mapel']) ? htmlspecialchars($m['kode_mapel']) . ' • ' : '' ?>KKM <?= (float)$m['kkm'] ?>
                            </span>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rankingData)): ?>
                    <tr>
                        <td colspan="<?= 8 + count($mapelList) ?>" class="py-4 text-muted">Belum ada data nilai peserta didik pada rombel ini.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rankingData as $row): ?>
                        <?php 
                        $rank = $row['ranking'];
                        $rowHighlight = '';
                        if ($rank === 1) $rowHighlight = 'rank-gold';
                        elseif ($rank === 2) $rowHighlight = 'rank-silver';
                        elseif ($rank === 3) $rowHighlight = 'rank-bronze';
                        ?>
                        <tr class="<?= $rowHighlight ?>">
                            <!-- Peringkat -->
                            <td style="font-weight: 800; font-size: 0.76rem;">
                                <?= $rank ?>
                            </td>

                            <!-- NIS & NISN -->
                            <td style="font-family: monospace;"><?= htmlspecialchars($row['siswa']['nis'] ?? '-') ?></td>
                            <td style="font-family: monospace; font-size: 0.66rem;"><?= htmlspecialchars($row['siswa']['nisn'] ?? '-') ?></td>

                            <!-- Nama Lengkap -->
                            <td style="text-align: left; font-weight: 700; padding-left: 6px;">
                                <?= htmlspecialchars($row['siswa']['nama_lengkap'] ?? '-') ?>
                            </td>

                            <!-- Nilai Per Mapel -->
                            <?php foreach ($mapelList as $m): ?>
                                <?php 
                                $mId = (int)$m['id'];
                                $nItem = $row['nilai_mapel'][$mId] ?? null;
                                $val = (float)($nItem['nilai'] ?? 0);
                                $kkm = (float)$m['kkm'];
                                $isTuntas = ($val >= $kkm);
                                $hasScore = !empty($nItem['has_score']);
                                ?>
                                <td style="font-family: monospace; font-size: 0.72rem; <?= (!$isTuntas && $val > 0) ? 'color: #dc2626; font-weight: bold;' : '' ?>">
                                    <?php if (empty($nItem['is_enrolled'])): ?>
                                        <span style="color: #94a3b8;">-</span>
                                    <?php elseif (!$hasScore && $val <= 0): ?>
                                        <span style="color: #94a3b8;">0.0</span>
                                    <?php else: ?>
                                        <?= number_format($val, 1) ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>

                            <!-- Total Skor -->
                            <td style="font-family: monospace; font-weight: 800; background-color: #f1f5f9;">
                                <?= number_format($row['total_nilai'], 1) ?>
                            </td>

                            <!-- Rata-Rata -->
                            <td style="font-family: monospace; font-weight: 800; background-color: #fef3c7;">
                                <?= number_format($row['rata_rata'], 2) ?>
                            </td>

                            <!-- Predikat -->
                            <td style="font-weight: 700;">
                                <?= htmlspecialchars($row['predikat']['grade'] ?? '-') ?>
                            </td>

                            <!-- Ketuntasan -->
                            <td style="font-size: 0.65rem; font-weight: 600;">
                                <?php if ($row['total_mapels'] > 0 && $row['tuntas_count'] === $row['total_mapels']): ?>
                                    <span style="color: #16a34a;">TUNTAS</span>
                                <?php elseif ($row['tuntas_count'] > 0): ?>
                                    <span style="color: #ea580c;">REMEDIAL</span>
                                <?php else: ?>
                                    <span style="color: #64748b;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>

            <!-- FOOTER REKAPITULASI KELAS -->
            <tfoot>
                <tr style="border-top: 2px solid #000; font-weight: bold;">
                    <td colspan="4" style="text-align: right; padding-right: 8px;">Rata-Rata Rombel:</td>
                    <?php foreach ($mapelList as $m): ?>
                        <?php $spm = $statsPerMapel[$m['id']] ?? ['avg' => 0]; ?>
                        <td style="font-family: monospace; color: #1e3a8a;">
                            <?= number_format($spm['avg'] ?? 0, 1) ?>
                        </td>
                    <?php endforeach; ?>
                    <td style="font-family: monospace; background-color: #e0e7ff;">
                        <?= number_format(array_sum(array_column($statsPerMapel, 'avg')), 1) ?>
                    </td>
                    <td style="font-family: monospace; background-color: #fef3c7; font-weight: 800;">
                        <?= number_format($rombelAvgTotal, 2) ?>
                    </td>
                    <td colspan="2"></td>
                </tr>
                <tr style="font-size: 0.68rem; color: #475569;">
                    <td colspan="4" style="text-align: right; padding-right: 8px;">Nilai Tertinggi:</td>
                    <?php foreach ($mapelList as $m): ?>
                        <?php $spm = $statsPerMapel[$m['id']] ?? ['max' => 0]; ?>
                        <td style="font-family: monospace; color: #16a34a;">
                            <?= number_format($spm['max'] ?? 0, 1) ?>
                        </td>
                    <?php endforeach; ?>
                    <td colspan="4"></td>
                </tr>
                <tr style="font-size: 0.68rem; color: #475569;">
                    <td colspan="4" style="text-align: right; padding-right: 8px;">Nilai Terendah:</td>
                    <?php foreach ($mapelList as $m): ?>
                        <?php $spm = $statsPerMapel[$m['id']] ?? ['min' => 0]; ?>
                        <td style="font-family: monospace; color: #dc2626;">
                            <?= number_format($spm['min'] ?? 0, 1) ?>
                        </td>
                    <?php endforeach; ?>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>

        <!-- RINGKASAN BINTANG PRESTASI KELAS (3 BESAR) -->
        <?php if (!empty($topThree)): ?>
            <div style="margin-top: 10px; margin-bottom: 12px; padding: 6px 12px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.72rem;">
                <strong>Bintang Prestasi Rombel <?= htmlspecialchars($selectedKelas['nama_kelas'] ?? '') ?>:</strong>
                <span class="mx-2">
                    🥇 <strong>Juara 1:</strong> <?= htmlspecialchars($topThree[0]['siswa']['nama_lengkap'] ?? '-') ?> (Rata-rata: <?= number_format($topThree[0]['rata_rata'], 2) ?>)
                </span>
                <?php if (isset($topThree[1])): ?>
                    <span class="mx-2">
                        🥈 <strong>Juara 2:</strong> <?= htmlspecialchars($topThree[1]['siswa']['nama_lengkap'] ?? '-') ?> (Rata-rata: <?= number_format($topThree[1]['rata_rata'], 2) ?>)
                    </span>
                <?php endif; ?>
                <?php if (isset($topThree[2])): ?>
                    <span class="mx-2">
                        🥉 <strong>Juara 3:</strong> <?= htmlspecialchars($topThree[2]['siswa']['nama_lengkap'] ?? '-') ?> (Rata-rata: <?= number_format($topThree[2]['rata_rata'], 2) ?>)
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- BAGIAN TANDA TANGAN RESMI -->
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td style="width: 50%;">
                        Mengetahui,<br>
                        <strong>Kepala <?= htmlspecialchars($settings['nama_sekolah'] ?? 'SMK Muthia Harapan Cicalengka') ?></strong>
                        <div class="sign-space"></div>
                        <div class="sign-name"><?= htmlspecialchars($kepsekNama) ?></div>
                        <div>NIP. <?= htmlspecialchars($kepsekNip) ?></div>
                    </td>
                    <td style="width: 50%;">
                        Cicalengka, <?= date('d F Y') ?><br>
                        <strong>Wali Kelas <?= htmlspecialchars($selectedKelas['nama_kelas'] ?? '') ?></strong>
                        <div class="sign-space"></div>
                        <div class="sign-name"><?= htmlspecialchars($waliKelas['nama_lengkap'] ?? '-') ?></div>
                        <div>NIP. <?= htmlspecialchars(!empty($waliKelas['nip']) ? $waliKelas['nip'] : '-') ?></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
