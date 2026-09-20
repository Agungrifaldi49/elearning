<?php
/**
 * View Cetak E-Rapor Digital Resmi SMK Muthia Harapan Cicalengka
 * Digunakan untuk: Cetak Sekaligus (Bulk Print Rombel) maupun Cetak Perorangan oleh Wali Kelas
 * Standar Presisi Ukuran Kertas A4 & F4 (Folio), Background Putih Bersih, Page Break Antar Siswa
 */

// Resolusi Logo Sekolah Dinamis & Aman (Anti-Gagal)
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
    <title>E-Rapor Digital <?= htmlspecialchars($kelas['nama_kelas'] ?? '') ?> - SMK Muthia Harapan</title>
    <!-- Fonts & CSS Framework -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            background-color: #f1f5f9;
            color: #0f172a;
            font-size: 0.74rem;
            line-height: 1.35;
        }

        /* STICKY TOP TOOLBAR SCREEN ONLY */
        .bulk-print-toolbar {
            position: sticky;
            top: 0;
            z-index: 9999;
            background: #ffffff;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }
        .btn-print {
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
        .btn-print:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        .btn-back {
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
        .btn-back:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* PAPER CONTAINER */
        .paper-wrapper {
            padding: 24px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }

        .rapor-sheet {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            padding: 10mm 14mm 14mm 14mm;
            box-shadow: 0 4px 25px rgba(15, 23, 42, 0.08);
            border-radius: 6px;
            position: relative;
            box-sizing: border-box;
        }

        /* KOP & DIVIDER */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 2px;
        }
        .kop-table td {
            border: none;
            padding: 0;
        }
        .kop-divider {
            border-top: 2.5px solid #0f172a;
            border-bottom: 1px solid #0f172a;
            height: 4px;
            margin: 5px 0 8px 0;
        }

        /* KOTAK IDENTITAS SISWA */
        .student-info-box {
            background: #ffffff !important;
            border: 1.5px solid #0f172a !important;
            border-radius: 6px;
            padding: 5px 10px;
            margin-bottom: 8px;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        /* TABLE OFFICIAL STYLES */
        table.table-official {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #0f172a;
            table-layout: fixed;
            background: #ffffff;
            margin-bottom: 8px;
            font-size: 0.70rem;
            page-break-inside: auto !important;
            break-inside: auto !important;
        }
        table.table-official thead {
            display: table-header-group !important; /* Header kolom otomatis mengulang jika tabel bersambung ke halaman berikutnya */
        }
        table.table-official tfoot {
            display: table-footer-group !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        table.table-official tbody tr, 
        table.table-official tfoot tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important; /* Mencegah baris tabel terbelah di batas bawah kertas */
        }
        table.table-official th, table.table-official td {
            border: 1px solid #0f172a;
            padding: 3.5px 5px;
            background: #ffffff;
            vertical-align: middle;
            box-sizing: border-box;
        }
        table.table-official th {
            text-align: center;
            font-weight: 700;
            font-size: 0.69rem;
            letter-spacing: 0.2px;
            background: #ffffff;
            color: #0f172a;
            line-height: 1.25;
            padding: 4px 3px;
        }

        /* SIGNATURE */
        .sig-row {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            text-align: center;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .sig-col {
            width: 32%;
        }
        .sig-line {
            width: 75%;
            margin: 32px auto 4px auto;
            border-bottom: 1.2px solid #0f172a;
        }

        /* SEAL NOTE */
        .seal-note {
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #0f172a;
            font-size: 0.65rem;
            color: #475569;
            display: flex;
            justify-content: space-between;
            align-items: center;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .avoid-break {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        /* PRINT MEDIA RULES (A4 & F4 READY DENGAN JARAK AMAN & ANTI-HEADER/FOOTER BROWSER) */
        @page {
            size: auto; /* Otomatis A4 / F4 */
            margin: 0; /* Margin 0 mematikan URL, tanggal, dan header/footer default browser */
        }
        @media print {
            .no-print, .bulk-print-toolbar {
                display: none !important;
            }
            html, body {
                background: #ffffff !important;
                color: #0f172a !important;
                font-size: 0.70rem;
                margin: 0 !important;
                padding: 0 !important;
            }
            .paper-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                gap: 0 !important;
                display: block !important;
            }
            .rapor-sheet {
                width: 100% !important;
                min-height: auto !important;
                box-shadow: none !important;
                padding: 8mm 12mm 12mm 12mm !important; /* Jarak aman tepi kertas */
                margin: 0 auto !important;
                border: none !important;
                border-radius: 0 !important;
                background: #ffffff !important;
                box-sizing: border-box !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
            }
            .page-break {
                page-break-after: always !important;
                break-after: page !important;
            }

            /* Anti Terpotong di Garis Bawah Kertas */
            table.table-official thead {
                display: table-header-group !important;
            }
            table.table-official tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            table.table-official td, table.table-official th {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            
            .student-info-box,
            .attendance-catatan-box,
            .ekskul-box,
            .sig-row,
            .seal-note,
            .signature-seal-block,
            .avoid-break {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    <!-- TOOLBAR ATAS (HANYA MUNCUL DI LAYAR - HILANG SAAT CETAK) -->
    <div class="bulk-print-toolbar no-print">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="<?= BASE_URL ?>index.php?url=guru/waliKelas&kelas_id=<?= $kelas['id'] ?>" class="btn-back">
                <i class="bi bi-arrow-left"></i> Kembali ke Panel Wali Kelas
            </a>
            <div>
                <strong style="font-size: 0.95rem; color: #0f172a;">
                    Dokumen Cetak E-Rapor Digital
                    <?= count($allRaporList) > 1 ? 'Massal' : 'Perorangan' ?>
                </strong>
                <div style="color: #64748b; font-size: 0.75rem;">
                    Rombel: <strong><?= htmlspecialchars($kelas['nama_kelas'] ?? '-') ?></strong> &bull; Total: <strong><?= count($allRaporList) ?> Lembar Rapor Siswa</strong>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 9999px; padding: 4px 14px; font-size: 0.74rem; color: #1e40af; display: inline-flex; align-items: center; gap: 6px;">
                <i class="bi bi-info-circle-fill text-primary"></i>
                <span><strong>Cetak Bersih:</strong> Di dialog print browser, pilih <em>"Setelan lainnya" (More settings)</em> &rarr; <strong>Hilangkan centang "Header dan footer"</strong> agar tanggal & link URL tidak muncul.</span>
            </div>
            <button type="button" class="btn-print" onclick="printOfficialBulkRapor()">
                <i class="bi bi-printer-fill fs-5"></i> Cetak E-Rapor (<?= count($allRaporList) ?> Siswa)
            </button>
        </div>
    </div>

    <!-- WRAPPER LEMBAR RAPOR SELURUH SISWA -->
    <div class="paper-wrapper">
        <?php if (empty($allRaporList)): ?>
            <div class="rapor-sheet text-center py-5">
                <i class="bi bi-inbox fs-1 text-secondary d-block mb-3"></i>
                <h5 class="fw-bold text-dark mb-1">Tidak Ada Data Siswa</h5>
                <p class="text-muted small mb-4">Belum ada peserta didik terdaftar dalam rombel ini.</p>
                <a href="<?= BASE_URL ?>index.php?url=guru/waliKelas" class="btn-back">Kembali ke Panel Wali Kelas</a>
            </div>
        <?php else: ?>
            <?php foreach ($allRaporList as $idx => $rData): ?>
                <?php
                $sw = $rData['siswa'] ?? [];
                $rHeader = $rData['raporData'] ?? [];
                $calcRows = $rData['calculatedRows'] ?? [];
                $avgAkhir = (float)($rData['avgAkhir'] ?? 0);
                $avgPred = $rData['avgPred'] ?? ['grade' => '-', 'class' => ''];
                $allTuntas = (bool)($rData['allTuntas'] ?? false);
                $attRekap = $rData['absensiRekap'] ?? ['total' => 0, 'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0];
                $eksList = $rData['ekskulList'] ?? [];
                $waliK = $rData['waliKelas'] ?? [];
                $st = $rData['settings'] ?? $settings;

                $tingkatSiswa = strtoupper(trim($sw['tingkat'] ?? ($rHeader['tingkat'] ?? '')));
                if (empty($tingkatSiswa)) {
                    $namaK = strtoupper(trim($sw['nama_kelas'] ?? ''));
                    if (strpos($namaK, 'XII') !== false || strpos($namaK, '12') !== false) $tingkatSiswa = 'XII';
                    elseif (strpos($namaK, 'XI') !== false || strpos($namaK, '11') !== false) $tingkatSiswa = 'XI';
                    elseif (strpos($namaK, 'X') !== false || strpos($namaK, '10') !== false) $tingkatSiswa = 'X';
                }
                $isFaseF = in_array($tingkatSiswa, ['XI', 'XII', '11', '12']);
                $faseText = !empty($rHeader['fase_nama_snapshot']) ? $rHeader['fase_nama_snapshot'] : ($isFaseF ? 'Fase F (Kelas XI - XII)' : 'Fase E (Kelas X)');
                $kurikulumText = !empty($rHeader['kurikulum_nama_snapshot']) ? $rHeader['kurikulum_nama_snapshot'] : 'Kurikulum Merdeka SMK';
                $tahunAjaranText = $rHeader['tahun_ajaran'] ?? ($activeTa['tahun_ajaran'] ?? '2026/2027');
                $semesterText = $rHeader['semester'] ?? ($activeTa['semester'] ?? 'Ganjil');

                $isLastSheet = ($idx === count($allRaporList) - 1);
                ?>
                <div class="rapor-sheet <?= !$isLastSheet ? 'page-break' : '' ?>">
                    
                    <!-- KOP SURAT RESMI SEKOLAH -->
                    <table class="kop-table">
                        <tr>
                            <td style="width: 85px; text-align: center; vertical-align: middle;">
                                <?php if (!empty($logoUrl)): ?>
                                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo SMK" style="max-height: 68px; width: auto; object-fit: contain;">
                                <?php else: ?>
                                    <div style="width: 55px; height: 55px; background: #0f172a; color: #fff; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-mortarboard-fill fs-3"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; vertical-align: middle; padding: 0 10px;">
                                <div style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #1e293b; line-height: 1.3;">
                                    PEMERINTAH DAERAH PROVINSI JAWA BARAT &bull; DINAS PENDIDIKAN
                                </div>
                                <div style="font-size: 0.70rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; margin: 1px 0;">
                                    YAYASAN PENDIDIKAN MUTHIA HARAPAN
                                </div>
                                <div style="font-size: 1.25rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; color: #0f172a; margin: 2px 0;">
                                    <?= htmlspecialchars($st['nama_sekolah'] ?? 'SMK MUTHIA HARAPAN CICALENGKA') ?>
                                </div>
                                <div style="font-size: 0.69rem; font-weight: 600; color: #334155; margin-bottom: 2px;">
                                    NPSN: <?= htmlspecialchars($st['npsn'] ?? '69888421') ?> &bull; Status Akreditasi: <strong><?= htmlspecialchars($st['akreditasi'] ?? 'A (Unggul)') ?></strong> &bull; Program Keahlian: <?= htmlspecialchars($sw['nama_jurusan'] ?? 'Teknologi Informasi') ?>
                                </div>
                                <div style="font-size: 0.66rem; color: #475569; line-height: 1.3;">
                                    <?= htmlspecialchars($st['alamat'] ?? 'Jl. Raya Cicalengka, Kab. Bandung, Jawa Barat 40395') ?> <?= !empty($st['telepon']) ? '| Telp: ' . htmlspecialchars($st['telepon']) : '' ?>
                                </div>
                            </td>
                            <td style="width: 85px;"></td>
                        </tr>
                    </table>

                    <div class="kop-divider"></div>

                    <!-- JUDUL DOKUMEN -->
                    <div style="text-align: center; margin: 6px 0 10px 0;">
                        <div style="font-size: 1.05rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4px; color: #0f172a;">
                            LAPORAN HASIL BELAJAR PESERTA DIDIK (E-RAPOR DIGITAL)
                        </div>
                        <div style="font-size: 0.76rem; font-weight: 600; color: #475569; margin-top: 2px;">
                            Tahun Ajaran <?= htmlspecialchars($tahunAjaranText) ?> (Semester <?= htmlspecialchars($semesterText) ?>) &bull; <?= htmlspecialchars($kurikulumText) ?> &bull; <?= htmlspecialchars($faseText) ?>
                        </div>
                    </div>

                    <!-- KOTAK IDENTITAS SISWA (2 KOLOM SEJAJAR, BACKGROUND PUTIH, BORDER RAPI) -->
                    <div class="student-info-box">
                        <table style="width: 100%; border-collapse: collapse; border: none; font-size: 0.78rem;">
                            <tr>
                                <td style="width: 50%; vertical-align: top; border: none; padding: 0 12px 0 4px;">
                                    <table style="width: 100%; border-collapse: collapse; border: none;">
                                        <tr>
                                            <td style="width: 135px; color: #475569; padding: 2.5px 0; border: none; font-size: 0.78rem;">Nama Peserta Didik</td>
                                            <td style="width: 12px; color: #0f172a; font-weight: bold; border: none; text-align: center; font-size: 0.78rem;">:</td>
                                            <td style="font-weight: 700; color: #0f172a; padding: 2.5px 0; border: none; font-size: 0.78rem;"><?= htmlspecialchars($sw['nama_lengkap'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #475569; padding: 2.5px 0; border: none; font-size: 0.78rem;">NIS / NISN</td>
                                            <td style="color: #0f172a; font-weight: bold; border: none; text-align: center; font-size: 0.78rem;">:</td>
                                            <td style="font-weight: 700; color: #0f172a; padding: 2.5px 0; border: none; font-size: 0.78rem;"><?= htmlspecialchars($sw['nis'] ?? '-') ?> / <?= htmlspecialchars($sw['nisn'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #475569; padding: 2.5px 0; border: none; font-size: 0.78rem;">Kelas / Rombel</td>
                                            <td style="color: #0f172a; font-weight: bold; border: none; text-align: center; font-size: 0.78rem;">:</td>
                                            <td style="font-weight: 700; color: #0f172a; padding: 2.5px 0; border: none; font-size: 0.78rem;"><?= htmlspecialchars($sw['nama_kelas'] ?? '-') ?></td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 50%; vertical-align: top; border: none; padding: 0 4px 0 12px;">
                                    <table style="width: 100%; border-collapse: collapse; border: none;">
                                        <tr>
                                            <td style="width: 135px; color: #475569; padding: 2.5px 0; border: none; font-size: 0.78rem;">Program Keahlian</td>
                                            <td style="width: 12px; color: #0f172a; font-weight: bold; border: none; text-align: center; font-size: 0.78rem;">:</td>
                                            <td style="font-weight: 700; color: #0f172a; padding: 2.5px 0; border: none; font-size: 0.78rem;"><?= htmlspecialchars($sw['nama_jurusan'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #475569; padding: 2.5px 0; border: none; font-size: 0.78rem;">Fase & Kurikulum</td>
                                            <td style="color: #0f172a; font-weight: bold; border: none; text-align: center; font-size: 0.78rem;">:</td>
                                            <td style="font-weight: 700; color: #0f172a; padding: 2.5px 0; border: none; font-size: 0.78rem;"><?= htmlspecialchars($faseText) ?> (<?= htmlspecialchars($kurikulumText) ?>)</td>
                                        </tr>
                                        <tr>
                                            <td style="color: #475569; padding: 2.5px 0; border: none; font-size: 0.78rem;">Status E-Rapor</td>
                                            <td style="color: #0f172a; font-weight: bold; border: none; text-align: center; font-size: 0.78rem;">:</td>
                                            <td style="font-weight: 700; color: #166534; padding: 2.5px 0; border: none; font-size: 0.78rem;">
                                                <i class="bi bi-patch-check-fill text-primary me-1"></i> Terverifikasi Resmi
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- TABEL NILAI MATA PELAJARAN (7 KOLOM RESMI SESUAI STANDAR RAPOR) -->
                    <table class="table-official">
                        <thead>
                            <tr style="background: #ffffff;">
                                <th style="width: 4%; padding: 6px 2px;">NO</th>
                                <th style="width: 24%; text-align: center !important; padding: 6px 4px;">MATA PELAJARAN</th>
                                <th style="width: 6%; padding: 6px 2px;">KKM</th>
                                <th style="width: 12%; padding: 6px 2px;">NILAI AKHIR</th>
                                <th style="width: 11%; padding: 6px 4px;">PREDIKAT</th>
                                <th style="width: 13%; padding: 6px 4px;">KETUNTASAN</th>
                                <th style="width: 30%; text-align: center !important; padding: 6px 4px;">CAPAIAN KOMPETENSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($calcRows)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 12px; color: #475569;">
                                        Belum ada data nilai yang diinput Guru Pengampu.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($calcRows as $cr): ?>
                                    <tr style="page-break-inside: avoid !important; break-inside: avoid !important;">
                                        <td style="text-align: center; vertical-align: middle; padding: 3px 2px; font-size: 0.70rem;"><?= $cr['no'] ?></td>
                                        <td style="text-align: left; font-weight: 700; vertical-align: middle; padding: 3px 5px; font-size: 0.71rem;"><?= htmlspecialchars($cr['mapel']) ?></td>
                                        <td style="text-align: center; vertical-align: middle; padding: 3px 2px; font-size: 0.70rem;"><?= (int)$cr['kkm'] ?></td>
                                        <td style="text-align: center; font-weight: 800; font-size: 0.78rem; vertical-align: middle; padding: 3px 2px;"><?= number_format($cr['akhir'], 1) ?></td>
                                        <td style="text-align: center; vertical-align: middle; padding: 3px 2px;">
                                            <span style="display: inline-block; border: 1px solid #0f172a; border-radius: 4px; padding: 1px 5px; font-weight: 700; font-size: 0.68rem;"><?= $cr['pred']['grade'] ?></span>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle; white-space: nowrap; padding: 3px 4px;">
                                            <span style="display: inline-block; border: 1px solid #0f172a; border-radius: 4px; padding: 1px 6px; font-weight: 700; font-size: 0.64rem;"><?= $cr['is_tuntas'] ? 'TUNTAS' : 'REMEDIAL' ?></span>
                                        </td>
                                        <td style="text-align: left; font-size: 0.66rem; line-height: 1.25; vertical-align: middle; padding: 3px 5px;">
                                            <?= htmlspecialchars($cr['deskripsi']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <!-- BARIS RATA-RATA NILAI AKHIR (DITAMPILKAN DI AKHIR SETELAH SELURUH MAPEL SELESAI DICETAK) -->
                                <tr style="background: #ffffff; border-top: 2px solid #0f172a; page-break-inside: avoid !important; break-inside: avoid !important;">
                                    <td colspan="3" style="text-align: right; font-weight: 700; font-size: 0.68rem; padding: 3.5px 5px; white-space: nowrap;">
                                        RATA-RATA NILAI AKHIR SEMESTER
                                    </td>
                                    <td style="text-align: center; font-weight: 800; font-size: 0.78rem; padding: 3.5px 2px;">
                                        <?= number_format($avgAkhir, 1) ?>
                                    </td>
                                    <td style="text-align: center; padding: 3.5px 2px;">
                                        <span style="display: inline-block; border: 1px solid #0f172a; border-radius: 4px; padding: 1px 5px; font-weight: 700; font-size: 0.68rem;"><?= $avgPred['grade'] ?></span>
                                    </td>
                                    <td style="text-align: center; padding: 3.5px 4px; white-space: nowrap;">
                                        <span style="display: inline-block; border: 1px solid #0f172a; border-radius: 4px; padding: 1px 6px; font-weight: 700; font-size: 0.64rem;"><?= $allTuntas ? 'TUNTAS' : 'REMEDIAL' ?></span>
                                    </td>
                                    <td style="text-align: left; font-size: 0.66rem; line-height: 1.22; font-weight: 600; padding: 3.5px 5px;">
                                        <?= $allTuntas 
                                            ? 'Status Akademik: Memenuhi Kriteria Ketercapaian Tujuan Pembelajaran (KKTP).' 
                                            : 'Status Akademik: Terdapat mata pelajaran yang memerlukan pendampingan/remedial.' ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- REKAP KETIDAKHADIRAN & CATATAN WALI KELAS (ANTI-TERPOTONG DI BATAS HALAMAN) -->
                    <div class="avoid-break attendance-catatan-box" style="margin-bottom: 8px; page-break-inside: avoid !important; break-inside: avoid !important;">
                        <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #0f172a; background: #ffffff; table-layout: fixed; font-size: 0.70rem;">
                            <tr>
                                <!-- Sisi Kiri: Rekap Absensi -->
                                <td style="width: 45%; border-right: 1.5px solid #0f172a; padding: 0; vertical-align: top; background: #ffffff;">
                                    <table style="width: 100%; border-collapse: collapse; background: #ffffff;">
                                        <tr style="border-bottom: 1.5px solid #0f172a;">
                                            <th colspan="3" style="padding: 4px 6px; text-align: center; font-weight: 700; font-size: 0.69rem; letter-spacing: 0.2px;">
                                                REKAP KETIDAKHADIRAN SISWA
                                            </th>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #0f172a;">
                                            <th style="width: 15%; border-right: 1px solid #0f172a; padding: 3px 4px; text-align: center; font-size: 0.67rem;">NO</th>
                                            <th style="width: 55%; border-right: 1px solid #0f172a; padding: 3px 6px; text-align: left; font-size: 0.67rem;">KETERANGAN</th>
                                            <th style="width: 30%; padding: 3px 4px; text-align: center; font-size: 0.67rem;">JUMLAH</th>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #0f172a;">
                                            <td style="border-right: 1px solid #0f172a; padding: 3.5px 4px; text-align: center;">1</td>
                                            <td style="border-right: 1px solid #0f172a; padding: 3.5px 6px;">Sakit (S)</td>
                                            <td style="padding: 3.5px 4px; text-align: center; font-weight: 700;"><?= (int)($attRekap['sakit'] ?? 0) ?> hari</td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #0f172a;">
                                            <td style="border-right: 1px solid #0f172a; padding: 3.5px 4px; text-align: center;">2</td>
                                            <td style="border-right: 1px solid #0f172a; padding: 3.5px 6px;">Izin (I)</td>
                                            <td style="padding: 3.5px 4px; text-align: center; font-weight: 700;"><?= (int)($attRekap['izin'] ?? 0) ?> hari</td>
                                        </tr>
                                        <tr>
                                            <td style="border-right: 1px solid #0f172a; padding: 3.5px 4px; text-align: center;">3</td>
                                            <td style="border-right: 1px solid #0f172a; padding: 3.5px 6px;">Tanpa Keterangan / Alpa (A)</td>
                                            <td style="padding: 3.5px 4px; text-align: center; font-weight: 700;"><?= (int)($attRekap['alpa'] ?? 0) ?> hari</td>
                                        </tr>
                                    </table>
                                </td>
                                <!-- Sisi Kanan: Catatan Wali Kelas -->
                                <td style="width: 55%; padding: 5px 8px; vertical-align: top; background: #ffffff;">
                                    <div style="font-weight: 700; font-size: 0.69rem; color: #0f172a; margin-bottom: 3px; letter-spacing: 0.2px;">
                                        CATATAN WALI KELAS & KEDISIPLINAN:
                                    </div>
                                    <div style="font-size: 0.67rem; line-height: 1.3; color: #0f172a;">
                                        <?= !empty($rHeader['catatan_wali_kelas']) 
                                            ? nl2br(htmlspecialchars($rHeader['catatan_wali_kelas'])) 
                                            : 'Tingkatkan kedisiplinan belajar, pertahankan prestasi akademik, serta maksimalkan kehadiran pada setiap kegiatan pembelajaran semester berikutnya.' ?>
                                    </div>
                                    <div style="margin-top: 5px; font-size: 0.65rem; color: #475569;">
                                        Kehadiran Tercatat: <strong><?= (int)($attRekap['hadir'] ?? 0) ?></strong> hari hadir dari total <strong><?= (int)($attRekap['total'] ?? 0) ?></strong> presensi.
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- TABEL EKSTRAKURIKULER SISWA (ANTI-TERPOTONG DI BATAS HALAMAN) -->
                    <div class="avoid-break ekskul-box" style="margin-bottom: 8px; page-break-inside: avoid !important; break-inside: avoid !important;">
                        <table class="table-official" style="font-size: 0.70rem; margin-bottom: 0;">
                            <thead>
                                <tr style="border-bottom: 1.5px solid #0f172a;">
                                    <th style="width: 5%; text-align: center !important; padding: 4px 2px;">NO</th>
                                    <th style="width: 25%; text-align: center !important; padding: 4px 6px;">KEGIATAN EKSTRAKURIKULER</th>
                                    <th style="width: 15%; text-align: center !important; padding: 4px 4px;">PREDIKAT</th>
                                    <th style="width: 55%; text-align: center !important; padding: 4px 6px;">KETERANGAN / NILAI CAPAIAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($eksList)): ?>
                                    <tr style="page-break-inside: avoid !important; break-inside: avoid !important;">
                                        <td style="text-align: center; color: #475569; padding: 4px 2px;">1</td>
                                        <td style="text-align: left; color: #475569; padding: 4px 6px;">-</td>
                                        <td style="text-align: center; color: #475569; padding: 4px 4px;">-</td>
                                        <td style="text-align: left; color: #475569; padding: 4px 6px;">Belum mengikuti kegiatan ekstrakurikuler pada semester ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($eksList as $iEks => $ek): ?>
                                        <tr style="page-break-inside: avoid !important; break-inside: avoid !important;">
                                            <td style="text-align: center; vertical-align: middle; padding: 4px 2px;"><?= $iEks + 1 ?></td>
                                            <td style="text-align: left; font-weight: 700; vertical-align: middle; padding: 4px 6px;"><?= htmlspecialchars($ek['nama_ekskul']) ?></td>
                                            <td style="text-align: center; font-weight: 700; vertical-align: middle; padding: 4px 4px;"><?= htmlspecialchars($ek['predikat'] ?: 'Sangat Baik') ?></td>
                                            <td style="text-align: left; font-size: 0.67rem; line-height: 1.3; vertical-align: middle; padding: 4px 6px;">
                                                <?= !empty($ek['nilai_deskripsi']) 
                                                    ? htmlspecialchars($ek['nilai_deskripsi']) 
                                                    : 'Aktif berpartisipasi dalam kegiatan dan menunjukkan capaian pembinaan yang baik.' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- BLOK TANDA TANGAN & KEABSAHAN (SATU KESATUAN ANTI-TERBELAH) -->
                    <div class="avoid-break signature-seal-block" style="page-break-inside: avoid !important; break-inside: avoid !important; margin-top: 10px;">
                        <!-- TANDA TANGAN RESMI 3 KOLOM -->
                        <div class="sig-row">
                            <div class="sig-col">
                                <p class="mb-0 small text-muted">Mengetahui,</p>
                                <p class="fw-bold mb-0 text-dark" style="font-size:0.83rem;">Orang Tua / Wali Siswa</p>
                                <div class="sig-line"></div>
                                <small class="text-muted d-block mt-1" style="font-size:0.73rem;">( ................................................ )</small>
                            </div>
                            <div class="sig-col">
                                <p class="mb-0 small text-muted">Mengetahui,</p>
                                <p class="fw-bold mb-0 text-dark" style="font-size:0.83rem;">Kepala Sekolah</p>
                                <div class="sig-line"></div>
                                <small class="fw-bold text-dark d-block mt-1" style="font-size:0.80rem;"><?= htmlspecialchars($rData['kepsekNama']) ?></small>
                                <small class="text-muted d-block" style="font-size:0.70rem;">NIP/NUPTK: <?= htmlspecialchars($rData['kepsekNip'] ?: '-') ?></small>
                            </div>
                            <div class="sig-col">
                                <?php
                                $kotaSekolah = 'Cicalengka';
                                if (!empty($st['alamat'])) {
                                    if (stripos($st['alamat'], 'Bandung') !== false) $kotaSekolah = 'Bandung';
                                    elseif (stripos($st['alamat'], 'Cicalengka') !== false) $kotaSekolah = 'Cicalengka';
                                }
                                ?>
                                <p class="mb-0 small text-muted"><?= htmlspecialchars($kotaSekolah) ?>, <?= date('d F Y') ?></p>
                                <p class="fw-bold mb-0 text-dark" style="font-size:0.83rem;">Wali Kelas Rombel</p>
                                <div class="sig-line"></div>
                                <?php if (!empty($waliK['nama_lengkap'])): ?>
                                    <small class="fw-bold text-dark d-block mt-1" style="font-size:0.80rem;"><?= htmlspecialchars($waliK['nama_lengkap']) ?></small>
                                    <small class="text-muted d-block" style="font-size:0.70rem;">NIP/NUPTK: <?= htmlspecialchars($waliK['nip'] ?: '-') ?></small>
                                <?php else: ?>
                                    <small class="text-muted d-block mt-1" style="font-size:0.73rem;">( ................................................ )</small>
                                    <small class="text-muted d-block" style="font-size:0.70rem;">Wali Kelas</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- FOOTER SEAL VALIDASI SISTEM RESMI -->
                        <div class="seal-note">
                            <div>
                                <i class="bi bi-patch-check-fill text-primary me-1"></i>
                                <strong>Dokumen Resmi E-Rapor Digital SMK Muthia Harapan Cicalengka</strong> — Dicetak melalui Sistem E-Learning & E-Rapor Resmi.
                            </div>
                            <div style="font-weight: 700;">
                                Sah Tanpa Tanda Tangan Basah &bull; Validasi Sistem
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    function printOfficialBulkRapor() {
        const originalTitle = document.title;
        document.title = '';
        window.print();
        setTimeout(function() {
            document.title = originalTitle;
        }, 1000);
    }
    </script>
</body>
</html>
