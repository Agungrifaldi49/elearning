<?php
/**
 * View Cetak Sekaligus Semua E-Rapor Siswa dalam Satu Rombel (Bulk Print)
 * Format Standar A4/F4 Resmi, Pembatas Halaman CSS (Page Break), Background Putih Bersih
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak E-Rapor Rombel <?= htmlspecialchars($kelas['nama_kelas'] ?? '') ?> - SMK Muthia Harapan</title>
    <!-- Google Fonts & Bootstrap Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
            padding: 8px 20px;
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
            gap: 28px;
        }

        .rapor-sheet {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            padding: 10mm 12mm 10mm 12mm;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 4px;
            position: relative;
        }

        /* KOP & DIVIDER */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .kop-table td {
            border: none;
            padding: 0;
        }
        .kop-divider {
            border-top: 2.5px solid #0f172a;
            border-bottom: 1px solid #0f172a;
            height: 3px;
            margin: 6px 0 10px 0;
        }

        /* TABLE STYLES */
        .table-official {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #0f172a;
            table-layout: fixed;
            background: #ffffff;
            margin-bottom: 8px;
            font-size: 0.72rem;
        }
        .table-official th, .table-official td {
            border: 1px solid #0f172a;
            padding: 4px 4px;
            background: #ffffff;
        }
        .table-official th {
            text-align: center;
            font-weight: 700;
            font-size: 0.69rem;
            letter-spacing: 0.2px;
        }

        /* SIGNATURE */
        .sig-row {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            text-align: center;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .sig-col {
            width: 32%;
        }
        .sig-line {
            width: 75%;
            margin: 40px auto 4px auto;
            border-bottom: 1.2px solid #0f172a;
        }

        /* SEAL NOTE */
        .seal-note {
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px dashed #64748b;
            font-size: 0.65rem;
            color: #475569;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* PRINT MEDIA RULES */
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 8mm 8mm 8mm;
            }
            body {
                background: #ffffff !important;
                color: #0f172a !important;
                font-size: 0.72rem;
            }
            .no-print {
                display: none !important;
            }
            .paper-wrapper {
                padding: 0 !important;
                gap: 0 !important;
            }
            .rapor-sheet {
                width: 100% !important;
                min-height: auto !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                border-radius: 0 !important;
            }
            .page-break {
                page-break-after: always !important;
                break-after: page !important;
            }
        }
    </style>
</head>
<body>

    <!-- TOOLBAR ATAS (HANYA MUNCUL DI LAYAR - HILANG SAAT CETAK) -->
    <div class="bulk-print-toolbar no-print">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="<?= BASE_URL ?>index.php?url=guru/waliKelas&kelas_id=<?= $kelas['id'] ?>" class="btn-back">
                <i class="bi bi-arrow-left"></i> Kembali ke Panel Wali Kelas
            </a>
            <div>
                <strong style="font-size: 0.95rem; color: #0f172a;">Dokumen Cetak Massal E-Rapor Digital</strong>
                <div style="color: #64748b; font-size: 0.75rem;">
                    Rombel: <strong><?= htmlspecialchars($kelas['nama_kelas'] ?? '-') ?></strong> &bull; Total: <strong><?= count($allRaporList) ?> Lembar Rapor Siswa</strong>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 0.75rem; color: #64748b;">
                <i class="bi bi-info-circle me-1"></i>Pilih ukuran kertas A4 / F4 & centang <em>Background Graphics</em>
            </span>
            <button type="button" class="btn-print" onclick="printOfficialBulkRapor()">
                <i class="bi bi-printer-fill"></i> Cetak Seluruh Rapor Sekarang (<?= count($allRaporList) ?> Siswa)
            </button>
        </div>
    </div>

    <!-- WRAPPER LEMBAR RAPOR SELURUH SISWA -->
    <div class="paper-wrapper">
        <?php if (empty($allRaporList)): ?>
            <div class="rapor-sheet" style="text-align: center; padding: 60px 20px;">
                <h4 style="color: #64748b;">Tidak Ada Data Siswa</h4>
                <p>Belum ada siswa terdaftar dalam rombel ini.</p>
                <a href="<?= BASE_URL ?>index.php?url=guru/waliKelas" class="btn-back">Kembali</a>
            </div>
        <?php else: ?>
            <?php foreach ($allRaporList as $idx => $rData): ?>
                <?php
                $sw = $rData['siswa'];
                $rHeader = $rData['raporData'];
                $calcRows = $rData['calculatedRows'];
                $avgAkhir = (float)$rData['avgAkhir'];
                $avgPred = $rData['avgPred'];
                $allTuntas = (bool)$rData['allTuntas'];
                $attRekap = $rData['absensiRekap'];
                $eksList = $rData['ekskulList'];
                $waliK = $rData['waliKelas'];
                $settings = $rData['settings'];

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
                            <td style="width: 80px; text-align: center; vertical-align: middle;">
                                <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo SMK" style="height: 68px; width: auto; object-fit: contain;" onerror="this.src='<?= BASE_URL ?>assets/images/default-avatar.png';">
                            </td>
                            <td style="text-align: center; vertical-align: middle; padding: 0 8px;">
                                <div style="font-size: 0.70rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #0f172a;">
                                    PEMERINTAH DAERAH PROVINSI JAWA BARAT &bull; DINAS PENDIDIKAN
                                </div>
                                <div style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; margin: 1px 0;">
                                    YAYASAN PENDIDIKAN MUTHIA HARAPAN
                                </div>
                                <div style="font-size: 1.15rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.6px; color: #0f172a; margin: 1px 0;">
                                    <?= htmlspecialchars($settings['nama_sekolah'] ?? 'SMK MUTHIA HARAPAN CICALENGKA') ?>
                                </div>
                                <div style="font-size: 0.66rem; font-weight: 600; color: #334155; margin-bottom: 2px;">
                                    NPSN: <?= htmlspecialchars($settings['npsn'] ?? '69888421') ?> &bull; Status Akreditasi: <strong><?= htmlspecialchars($settings['akreditasi'] ?? 'A (Unggul)') ?></strong> &bull; Bidang & Program Keahlian: Teknologi Informasi & Bisnis Manajemen
                                </div>
                                <div style="font-size: 0.64rem; color: #475569; line-height: 1.25;">
                                    <?= htmlspecialchars($settings['alamat'] ?? 'Jl. Raya Cicalengka, Kab. Bandung, Jawa Barat 40395') ?> <?= !empty($settings['telepon']) ? '| Telp: ' . htmlspecialchars($settings['telepon']) : '' ?>
                                </div>
                            </td>
                            <td style="width: 80px;"></td>
                        </tr>
                    </table>

                    <div class="kop-divider"></div>

                    <!-- JUDUL DOKUMEN -->
                    <div style="text-align: center; margin: 4px 0 8px 0;">
                        <div style="font-size: 0.98rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4px; color: #0f172a;">
                            LAPORAN HASIL BELAJAR PESERTA DIDIK (E-RAPOR DIGITAL)
                        </div>
                        <div style="font-size: 0.72rem; font-weight: 600; color: #475569; margin-top: 1px;">
                            Tahun Ajaran <?= htmlspecialchars($tahunAjaranText) ?> (Semester <?= htmlspecialchars($semesterText) ?>) &bull; <?= htmlspecialchars($kurikulumText) ?> &bull; <?= htmlspecialchars($faseText) ?>
                        </div>
                    </div>

                    <!-- KOTAK IDENTITAS SISWA (2 KOLOM SEJAJAR, BACKGROUND PUTIH, BORDER RAPI) -->
                    <div style="background: #ffffff; border: 1.5px solid #0f172a; border-radius: 4px; padding: 6px 10px; margin-bottom: 8px;">
                        <table style="width: 100%; border-collapse: collapse; border: none; font-size: 0.73rem;">
                            <tr>
                                <td style="width: 50%; vertical-align: top; border: none; padding: 0 10px 0 0;">
                                    <table style="width: 100%; border-collapse: collapse; border: none;">
                                        <tr>
                                            <td style="width: 125px; color: #475569; padding: 2px 0; border: none;">Nama Peserta Didik</td>
                                            <td style="width: 10px; font-weight: bold; border: none; text-align: center;">:</td>
                                            <td style="font-weight: 700; color: #0f172a; padding: 2px 0; border: none;"><?= htmlspecialchars($sw['nama_lengkap'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #475569; padding: 2px 0; border: none;">NIS / NISN</td>
                                            <td style="font-weight: bold; border: none; text-align: center;">:</td>
                                            <td style="font-weight: 700; color: #0f172a; padding: 2px 0; border: none;"><?= htmlspecialchars($sw['nis'] ?? '-') ?> / <?= htmlspecialchars($sw['nisn'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #475569; padding: 2px 0; border: none;">Kelas / Rombel</td>
                                            <td style="font-weight: bold; border: none; text-align: center;">:</td>
                                            <td style="font-weight: 700; color: #0f172a; padding: 2px 0; border: none;"><?= htmlspecialchars($sw['nama_kelas'] ?? '-') ?></td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 50%; vertical-align: top; border: none; padding: 0 0 0 10px;">
                                    <table style="width: 100%; border-collapse: collapse; border: none;">
                                        <tr>
                                            <td style="width: 125px; color: #475569; padding: 2px 0; border: none;">Program Keahlian</td>
                                            <td style="width: 10px; font-weight: bold; border: none; text-align: center;">:</td>
                                            <td style="font-weight: 700; color: #0f172a; padding: 2px 0; border: none;"><?= htmlspecialchars($sw['nama_jurusan'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td style="color: #475569; padding: 2px 0; border: none;">Fase & Kurikulum</td>
                                            <td style="font-weight: bold; border: none; text-align: center;">:</td>
                                            <td style="font-weight: 700; color: #0f172a; padding: 2px 0; border: none;"><?= htmlspecialchars($faseText) ?> (<?= htmlspecialchars($kurikulumText) ?>)</td>
                                        </tr>
                                        <tr>
                                            <td style="color: #475569; padding: 2px 0; border: none;">Status E-Rapor</td>
                                            <td style="font-weight: bold; border: none; text-align: center;">:</td>
                                            <td style="font-weight: 700; color: #166534; padding: 2px 0; border: none;">Terverifikasi Resmi Sistem</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- TABEL NILAI MATA PELAJARAN (7 KOLOM RESMI DENGAN BARIS RATA-RATA BERSIH) -->
                    <table class="table-official">
                        <thead>
                            <tr style="background: #ffffff;">
                                <th style="width: 4%;">NO</th>
                                <th style="width: 25%; text-align: left !important; padding-left: 6px;">MATA PELAJARAN</th>
                                <th style="width: 6%;">KKM</th>
                                <th style="width: 11%;">NILAI AKHIR</th>
                                <th style="width: 10%;">PREDIKAT</th>
                                <th style="width: 13%;">KETUNTASAN</th>
                                <th style="width: 31%; text-align: left !important; padding-left: 6px;">CAPAIAN KOMPETENSI</th>
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
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle;"><?= $cr['no'] ?></td>
                                        <td style="text-align: left; font-weight: 700; vertical-align: middle; padding-left: 6px;"><?= htmlspecialchars($cr['mapel']) ?></td>
                                        <td style="text-align: center; vertical-align: middle;"><?= (int)$cr['kkm'] ?></td>
                                        <td style="text-align: center; font-weight: 800; font-size: 0.80rem; vertical-align: middle;"><?= number_format($cr['akhir'], 1) ?></td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <span style="display: inline-block; border: 1px solid #0f172a; border-radius: 3px; padding: 1px 5px; font-weight: 700; font-size: 0.68rem;"><?= $cr['pred']['grade'] ?></span>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle; white-space: nowrap;">
                                            <span style="display: inline-block; border: 1px solid #0f172a; border-radius: 3px; padding: 1px 6px; font-weight: 700; font-size: 0.65rem;"><?= $cr['is_tuntas'] ? 'TUNTAS' : 'REMEDIAL' ?></span>
                                        </td>
                                        <td style="text-align: left; font-size: 0.68rem; line-height: 1.3; vertical-align: middle; padding: 3px 6px;">
                                            <?= htmlspecialchars($cr['deskripsi']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <!-- BARIS RATA-RATA NILAI AKHIR (BACKGROUND PUTIH, FONT RAPI) -->
                                <tr style="border-top: 2px solid #0f172a;">
                                    <td colspan="3" style="text-align: right; font-weight: 700; font-size: 0.68rem; padding-right: 8px;">
                                        RATA-RATA NILAI AKHIR SEMESTER
                                    </td>
                                    <td style="text-align: center; font-weight: 800; font-size: 0.78rem;">
                                        <?= number_format($avgAkhir, 1) ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="display: inline-block; border: 1px solid #0f172a; border-radius: 3px; padding: 1px 5px; font-weight: 700; font-size: 0.68rem;"><?= $avgPred['grade'] ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="display: inline-block; border: 1px solid #0f172a; border-radius: 3px; padding: 1px 6px; font-weight: 700; font-size: 0.65rem;"><?= $allTuntas ? 'TUNTAS' : 'REMEDIAL' ?></span>
                                    </td>
                                    <td style="text-align: left; font-size: 0.66rem; line-height: 1.25; font-weight: 600; padding: 3px 6px;">
                                        <?= $allTuntas 
                                            ? 'Status Akademik: Memenuhi Kriteria Ketercapaian Tujuan Pembelajaran (KKTP).' 
                                            : 'Status Akademik: Terdapat mata pelajaran yang memerlukan pendampingan/remedial.' ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- REKAP KETIDAKHADIRAN & CATATAN WALI KELAS -->
                    <div style="margin-bottom: 8px; page-break-inside: avoid; break-inside: avoid;">
                        <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #0f172a; background: #ffffff; table-layout: fixed; font-size: 0.70rem;">
                            <tr>
                                <!-- Sisi Kiri: Rekap Absensi -->
                                <td style="width: 44%; border-right: 1.5px solid #0f172a; padding: 0; vertical-align: top;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr style="border-bottom: 1.5px solid #0f172a;">
                                            <th colspan="3" style="padding: 4px 6px; text-align: center; font-weight: 700; font-size: 0.68rem;">
                                                REKAP KETIDAKHADIRAN SISWA
                                            </th>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #0f172a;">
                                            <th style="width: 15%; border-right: 1px solid #0f172a; padding: 3px; text-align: center; font-size: 0.66rem;">NO</th>
                                            <th style="width: 55%; border-right: 1px solid #0f172a; padding: 3px 6px; text-align: left; font-size: 0.66rem;">KETERANGAN</th>
                                            <th style="width: 30%; padding: 3px; text-align: center; font-size: 0.66rem;">JUMLAH</th>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #0f172a;">
                                            <td style="border-right: 1px solid #0f172a; padding: 3px; text-align: center;">1</td>
                                            <td style="border-right: 1px solid #0f172a; padding: 3px 6px;">Sakit (S)</td>
                                            <td style="padding: 3px; text-align: center; font-weight: 700;"><?= (int)($attRekap['sakit'] ?? 0) ?> hari</td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #0f172a;">
                                            <td style="border-right: 1px solid #0f172a; padding: 3px; text-align: center;">2</td>
                                            <td style="border-right: 1px solid #0f172a; padding: 3px 6px;">Izin (I)</td>
                                            <td style="padding: 3px; text-align: center; font-weight: 700;"><?= (int)($attRekap['izin'] ?? 0) ?> hari</td>
                                        </tr>
                                        <tr>
                                            <td style="border-right: 1px solid #0f172a; padding: 3px; text-align: center;">3</td>
                                            <td style="border-right: 1px solid #0f172a; padding: 3px 6px;">Tanpa Keterangan / Alpa (A)</td>
                                            <td style="padding: 3px; text-align: center; font-weight: 700;"><?= (int)($attRekap['alpa'] ?? 0) ?> hari</td>
                                        </tr>
                                    </table>
                                </td>
                                <!-- Sisi Kanan: Catatan Wali Kelas -->
                                <td style="width: 56%; padding: 5px 8px; vertical-align: top;">
                                    <div style="font-weight: 700; font-size: 0.68rem; margin-bottom: 3px;">
                                        CATATAN WALI KELAS & KEDISIPLINAN:
                                    </div>
                                    <div style="font-size: 0.67rem; line-height: 1.35;">
                                        <?= !empty($rHeader['catatan_wali_kelas']) 
                                            ? nl2br(htmlspecialchars($rHeader['catatan_wali_kelas'])) 
                                            : 'Tingkatkan kedisiplinan belajar, pertahankan prestasi akademik, serta maksimalkan kehadiran pada setiap kegiatan pembelajaran semester berikutnya.' ?>
                                    </div>
                                    <div style="margin-top: 5px; font-size: 0.64rem; color: #475569;">
                                        Kehadiran Tercatat: <strong><?= (int)($attRekap['hadir'] ?? 0) ?></strong> hari hadir dari total <strong><?= (int)($attRekap['total'] ?? 0) ?></strong> presensi.
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- TABEL EKSTRAKURIKULER SISWA -->
                    <div style="margin-bottom: 8px; page-break-inside: avoid; break-inside: avoid;">
                        <table class="table-official" style="font-size: 0.70rem; margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">NO</th>
                                    <th style="width: 25%; text-align: left !important; padding-left: 6px;">KEGIATAN EKSTRAKURIKULER</th>
                                    <th style="width: 15%;">PREDIKAT</th>
                                    <th style="width: 55%; text-align: left !important; padding-left: 6px;">KETERANGAN / NILAI CAPAIAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($eksList)): ?>
                                    <tr>
                                        <td style="text-align: center;">1</td>
                                        <td style="text-align: left; padding-left: 6px; color: #475569;">-</td>
                                        <td style="text-align: center; color: #475569;">-</td>
                                        <td style="text-align: left; padding-left: 6px; color: #475569;">Belum mengikuti kegiatan ekstrakurikuler pada semester ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($eksList as $iEks => $ek): ?>
                                        <tr>
                                            <td style="text-align: center; vertical-align: middle;"><?= $iEks + 1 ?></td>
                                            <td style="text-align: left; font-weight: 700; vertical-align: middle; padding-left: 6px;"><?= htmlspecialchars($ek['nama_ekskul']) ?></td>
                                            <td style="text-align: center; font-weight: 700; vertical-align: middle;"><?= htmlspecialchars($ek['predikat'] ?: 'Sangat Baik') ?></td>
                                            <td style="text-align: left; font-size: 0.68rem; line-height: 1.3; vertical-align: middle; padding-left: 6px;">
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

                    <!-- TANDA TANGAN RESMI 3 KOLOM -->
                    <div class="sig-row">
                        <div class="sig-col">
                            <div style="font-size: 0.70rem; color: #475569;">Mengetahui,</div>
                            <div style="font-weight: 700; font-size: 0.76rem; margin-top: 1px;">Orang Tua / Wali Siswa</div>
                            <div class="sig-line"></div>
                            <div style="font-size: 0.70rem; color: #475569;">( ................................................ )</div>
                        </div>
                        <div class="sig-col">
                            <div style="font-size: 0.70rem; color: #475569;">Mengetahui,</div>
                            <div style="font-weight: 700; font-size: 0.76rem; margin-top: 1px;">Kepala Sekolah</div>
                            <div class="sig-line"></div>
                            <div style="font-weight: 700; font-size: 0.74rem;"><?= htmlspecialchars($rData['kepsekNama']) ?></div>
                            <div style="font-size: 0.66rem; color: #475569;">NIP/NUPTK: <?= htmlspecialchars($rData['kepsekNip'] ?: '-') ?></div>
                        </div>
                        <div class="sig-col">
                            <?php
                            $kotaSekolah = 'Cicalengka';
                            if (!empty($settings['alamat'])) {
                                if (stripos($settings['alamat'], 'Bandung') !== false) $kotaSekolah = 'Bandung';
                                elseif (stripos($settings['alamat'], 'Cicalengka') !== false) $kotaSekolah = 'Cicalengka';
                            }
                            ?>
                            <div style="font-size: 0.70rem; color: #475569;"><?= htmlspecialchars($kotaSekolah) ?>, <?= date('d F Y') ?></div>
                            <div style="font-weight: 700; font-size: 0.76rem; margin-top: 1px;">Wali Kelas Rombel</div>
                            <div class="sig-line"></div>
                            <?php if (!empty($waliK['nama_lengkap'])): ?>
                                <div style="font-weight: 700; font-size: 0.74rem;"><?= htmlspecialchars($waliK['nama_lengkap']) ?></div>
                                <div style="font-size: 0.66rem; color: #475569;">NIP/NUPTK: <?= htmlspecialchars($waliK['nip'] ?: '-') ?></div>
                            <?php else: ?>
                                <div style="font-size: 0.70rem; color: #475569;">( ................................................ )</div>
                                <div style="font-size: 0.66rem; color: #475569;">Wali Kelas</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- FOOTER SEAL VALIDASI SISTEM RESMI -->
                    <div class="seal-note">
                        <div>
                            <strong>Dokumen Resmi E-Rapor Digital SMK Muthia Harapan Cicalengka</strong> — Dicetak massal melalui Sistem Wali Kelas Resmi.
                        </div>
                        <div style="font-weight: 700;">
                            Sah Tanpa Tanda Tangan Basah &bull; Validasi Sistem
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
