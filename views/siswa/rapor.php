<?php
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/navbar.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';
?>

<style>
/* Modern LMS E-Rapor Architecture */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.rapor-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

/* Glassmorphic Hero Banner */
.rapor-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #2563eb 100%);
    border-radius: 20px;
    box-shadow: 0 12px 30px -5px rgba(30, 58, 138, 0.25);
    position: relative;
    overflow: hidden;
}

.rapor-card-paper {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
}

.grade-table-header {
    background: #f8fafc;
    color: #1e293b;
    font-weight: 700;
}

/* Mobile Responsive Table Scroll Container */
.rapor-table-scroll {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
}

.kop-divider {
    border-top: 2.5px solid #0f172a;
    border-bottom: 1px solid #0f172a;
    height: 4px;
    margin: 8px 0 12px 0;
}

.student-info-box {
    background: #ffffff;
    border: 1.5px solid #0f172a;
    border-radius: 6px;
}

.signature-line {
    border-top: 1px solid #0f172a;
    width: 75%;
    margin: 40px auto 4px auto;
}

.print-seal-note {
    display: none;
}

@media (max-width: 767.98px) {
    table.grade-table {
        min-width: 680px !important;
    }
    .rapor-hero-banner {
        padding: 1.25rem !important;
        border-radius: 16px !important;
    }
    .rapor-card-paper {
        padding: 1.25rem !important;
        border-radius: 16px !important;
    }
}

/* PAGE MEDIA SETUP: Standar Cetak A4 Maupun F4 (Folio) Bebas Header-Footer Browser */
@page {
    size: auto; 
    margin: 0; /* Margin 0 mematikan URL, tanggal/waktu, dan header/footer default browser */
}

/* Print Friendly Styles - 100% Background Putih, Presisi, Rapih & Elegan (A4 & F4 Ready) */
@media print {
    .d-print-none, .no-print, header, nav, .sidebar, .navbar, .main-content-header, #btn-print-wrapper {
        display: none !important;
    }
    .d-print-block {
        display: block !important;
    }
    
    html, body {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        color: #0f172a !important;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .main-content, .container-fluid, .rapor-wrapper {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .rapor-hero-banner {
        display: none !important;
    }

    /* Kertas Bersih Presisi - Siap Cetak A4 Maupun F4 (Folio) & 100% Background Putih */
    .rapor-card-paper {
        box-shadow: none !important;
        border: none !important;
        padding: 8mm 12mm 12mm 12mm !important; /* Jarak aman tepi kertas */
        border-radius: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 auto !important;
        background: #ffffff !important;
        box-sizing: border-box !important;
        page-break-inside: auto !important;
        break-inside: auto !important;
    }

    .rapor-kop-header {
        margin-bottom: 2px !important;
        background: #ffffff !important;
    }

    .kop-divider {
        border-top: 2.5px solid #0f172a !important;
        border-bottom: 1px solid #0f172a !important;
        height: 4px !important;
        margin: 5px 0 8px 0 !important;
    }

    /* Kotak Identitas Siswa saat Print - Murni Putih & Rapi Sejajar */
    .student-info-box {
        background: #ffffff !important;
        border: 1.5px solid #0f172a !important;
        border-radius: 6px !important;
        padding: 5px 10px !important;
        margin-bottom: 8px !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    /* Dedicated Print Table */
    table.table-print-official {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 1.5px solid #0f172a !important;
        background: #ffffff !important;
        table-layout: fixed !important;
        page-break-inside: auto !important;
        break-inside: auto !important;
    }
    table.table-print-official thead {
        display: table-header-group !important; /* Header kolom otomatis mengulang di halaman lanjutan */
    }
    table.table-print-official tr {
        page-break-inside: avoid !important;
        break-inside: avoid !important; /* Mencegah baris tabel terbelah di batas bawah kertas */
    }
    table.table-print-official th {
        border: 1px solid #0f172a !important;
        background: #ffffff !important;
        color: #0f172a !important;
        font-weight: 700 !important;
        font-size: 0.82rem !important;
        letter-spacing: 0.2px !important;
        vertical-align: middle !important;
        text-align: center !important;
        padding: 7px 5px !important;
        line-height: 1.3 !important;
        box-sizing: border-box !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    table.table-print-official td {
        border: 1px solid #0f172a !important;
        background: #ffffff !important;
        color: #0f172a !important;
        vertical-align: middle !important;
        box-sizing: border-box !important;
        padding: 5.5px 6px !important;
        font-size: 0.82rem !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    /* Badges Saat Print - Murni Putih dengan Border Hitam Tegas */
    .badge {
        font-size: 0.78rem !important;
        padding: 2px 7px !important;
        border-radius: 4px !important;
        border: 1px solid #0f172a !important;
        background: #ffffff !important;
        color: #0f172a !important;
        font-weight: 700 !important;
    }

    /* Tanda Tangan & Footer */
    .signature-section {
        margin-top: 14px !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    .signature-section p, .signature-section small {
        color: #0f172a !important;
    }
    .signature-line {
        border-top: 1.3px solid #0f172a !important;
        width: 78% !important;
        margin: 42px auto 6px auto !important;
    }

    /* Catatan Keabsahan & Validasi Dokumen */
    .print-seal-note {
        display: block !important;
        margin-top: 12px !important;
        padding-top: 6px !important;
        border-top: 1px solid #0f172a !important;
        font-size: 0.74rem !important;
        color: #475569 !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .avoid-break {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    ::-webkit-scrollbar {
        display: none !important;
    }
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>

<main class="main-content px-2 px-sm-3 px-md-4 py-3 rapor-wrapper">
<div class="container-fluid pt-3">

    <!-- Hero Banner Header (Screen Only) -->
    <div class="rapor-hero-banner text-white p-4 p-md-5 mb-4 no-print">
        <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-3 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-gradient p-3.5 rounded-4 text-white shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 54px; height: 54px; background: #2563eb;">
                    <i class="bi bi-file-earmark-text-fill fs-2"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-white mb-1" style="letter-spacing: -0.4px;">E-Rapor Digital Siswa</h3>
                    <p class="text-blue-100 small mb-0 fw-medium">Laporan Hasil Belajar Resmi dengan Predikat, KKM, dan Kalkulasi Nilai Akhir Semester.</p>
                </div>
            </div>

            <div class="d-flex flex-column align-items-start align-items-md-end gap-1.5" id="btn-print-wrapper">
                <button type="button" onclick="printOfficialRapor()" class="btn btn-success fw-bold rounded-pill shadow-sm px-4 py-2.5 text-nowrap d-flex align-items-center gap-2" style="font-size: 0.88rem; width: fit-content; max-width: 100%;">
                    <i class="bi bi-printer-fill fs-5"></i> Cetak / Simpan PDF E-Rapor
                </button>
                <small class="text-white-50" style="font-size: 0.74rem;">
                    <i class="bi bi-info-circle me-1"></i> Tips Cetak Bersih: Hilangkan centang <em>"Header dan footer"</em> di dialog print browser.
                </small>
            </div>
        </div>
    </div>

    <!-- Rapor Container Paper -->
    <div class="rapor-card-paper p-3 p-sm-4 p-md-5 mb-4">

        <!-- School Header Kop (Resmi, Sejajar, Background Putih) -->
        <div class="rapor-kop-header mb-2" style="background: #ffffff;">
            <table style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 2px;">
                <tr>
                    <td style="width: 85px; text-align: center; vertical-align: middle; border: none; padding: 0;">
                        <?php 
                            $rawLogo = $settings['logo'] ?? '';
                            $logoUrl = '';
                            if (!empty($rawLogo)) {
                                if (strpos($rawLogo, 'http') === 0) {
                                    $logoUrl = $rawLogo;
                                } elseif (strpos($rawLogo, 'assets/') === 0 && file_exists(ROOT_PATH . $rawLogo)) {
                                    $logoUrl = BASE_URL . $rawLogo;
                                } elseif (file_exists(ROOT_PATH . 'assets/uploads/logo/' . $rawLogo)) {
                                    $logoUrl = BASE_URL . 'assets/uploads/logo/' . $rawLogo;
                                } elseif (file_exists(ROOT_PATH . 'assets/uploads/' . $rawLogo)) {
                                    $logoUrl = BASE_URL . 'assets/uploads/' . $rawLogo;
                                } elseif (file_exists(ROOT_PATH . $rawLogo)) {
                                    $logoUrl = BASE_URL . $rawLogo;
                                }
                            }
                        ?>
                        <?php if (!empty($logoUrl)): ?>
                            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Sekolah" style="max-height: 68px; width: auto;" class="img-fluid">
                        <?php else: ?>
                            <div style="width:55px; height:55px; background:#0f172a; color:#fff; border-radius:10px; display:inline-flex; align-items:center; justify-content:center;">
                                <i class="bi bi-mortarboard-fill fs-3"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center; vertical-align: middle; border: none; padding: 0 10px;">
                        <div style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #1e293b; line-height: 1.3;">
                            PEMERINTAH DAERAH PROVINSI JAWA BARAT &bull; DINAS PENDIDIKAN
                        </div>
                        <div style="font-size: 1.30rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #0f172a; margin: 2px 0;">
                            <?= htmlspecialchars($settings['nama_sekolah'] ?? 'SMK MUTHIA HARAPAN CICALENGKA') ?>
                        </div>
                        <div style="font-size: 0.76rem; color: #475569; line-height: 1.35;">
                            <?= htmlspecialchars($settings['alamat'] ?? 'Jl. Raya Cicalengka, Kab. Bandung, Jawa Barat 40395') ?> <?= !empty($settings['telepon']) ? '| Telp: ' . htmlspecialchars($settings['telepon']) : '' ?>
                        </div>
                    </td>
                    <td style="width: 85px; border: none; padding: 0;"></td> <!-- Penyeimbang Simetris Logo -->
                </tr>
            </table>

            <!-- Garis Ganda Kop Surat Resmi Dinas -->
            <div class="kop-divider"></div>

            <?php
            $tingkatSiswa = strtoupper(trim($siswa['tingkat'] ?? ($raporData['tingkat'] ?? '')));
            if (empty($tingkatSiswa)) {
                $namaK = strtoupper(trim($siswa['nama_kelas'] ?? ($raporData['nama_kelas'] ?? '')));
                if (strpos($namaK, 'XII') !== false || strpos($namaK, '12') !== false) {
                    $tingkatSiswa = 'XII';
                } elseif (strpos($namaK, 'XI') !== false || strpos($namaK, '11') !== false) {
                    $tingkatSiswa = 'XI';
                } elseif (strpos($namaK, 'X') !== false || strpos($namaK, '10') !== false) {
                    $tingkatSiswa = 'X';
                }
            }
            $isFaseF = in_array($tingkatSiswa, ['XI', 'XII', '11', '12']);
            $defaultFase = $isFaseF ? 'Fase F (Kelas XI - XII)' : 'Fase E (Kelas X)';

            $kurikulumText = !empty($raporData['kurikulum_nama_snapshot']) ? $raporData['kurikulum_nama_snapshot'] : 'Kurikulum Merdeka SMK';
            $faseText = !empty($raporData['fase_nama_snapshot']) ? $raporData['fase_nama_snapshot'] : $defaultFase;
            $tahunAjaranText = $raporData['tahun_ajaran'] ?? ($activeTa['tahun_ajaran'] ?? '2026/2027');
            $semesterText = $raporData['semester'] ?? ($activeTa['semester'] ?? 'Ganjil');

            // Persentase & Label Bobot Komponen Penilaian Dinamis
            $pTugas = $bobotKomponen['pct_tugas'] ?? 20;
            $pQuiz  = $bobotKomponen['pct_quiz'] ?? 20;
            $pUts   = $bobotKomponen['pct_uts'] ?? 30;
            $pUas   = $bobotKomponen['pct_uas'] ?? 30;

            $lblTugas = $bobotKomponen['labels']['tugas'] ?? 'Tugas Mandiri / Terstruktur';
            $lblQuiz  = $bobotKomponen['labels']['quiz'] ?? 'Kuis / Formatif Harian';
            $lblUts   = $bobotKomponen['labels']['uts'] ?? 'Sumatif Tengah Semester (STS)';
            $lblUas   = $bobotKomponen['labels']['uas'] ?? 'Sumatif Akhir Semester (SAS)';

            // Data Rekap Absensi & Riwayat Presensi Siswa (Defensive Fallback)
            if (!isset($absensiRekap) || !is_array($absensiRekap)) {
                $absensiRekap = [
                    'total' => 0,
                    'hadir' => 0,
                    'izin'  => 0,
                    'sakit' => 0,
                    'alpa'  => 0
                ];
            }
            if (!isset($historyAbsen) || !is_array($historyAbsen)) {
                $historyAbsen = [];
            }
            if (!isset($ekskulList) || !is_array($ekskulList)) {
                $ekskulList = [];
            }
            ?>
            <div class="text-center my-2">
                <div style="font-size: 1.05rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4px; color: #0f172a;">
                    LAPORAN HASIL BELAJAR PESERTA DIDIK (E-RAPOR DIGITAL)
                </div>
                <div style="font-size: 0.78rem; font-weight: 600; color: #475569; margin-top: 2px;">
                    Tahun Ajaran <?= htmlspecialchars($tahunAjaranText) ?> (Semester <?= htmlspecialchars($semesterText) ?>) &bull; <?= htmlspecialchars($kurikulumText) ?> &bull; <?= htmlspecialchars($faseText) ?>
                </div>
            </div>
        </div>

        <!-- Student Info Header Block (Kotak Identitas Siswa Rapi, 2 Kolom Sejajar & Background Putih) -->
        <div class="student-info-box mb-3 p-2.5" style="background: #ffffff !important; border: 1.5px solid #0f172a !important; border-radius: 6px;">
            <table style="width: 100%; border-collapse: collapse; border: none; font-size: 0.78rem;">
                <tr>
                    <!-- Kolom Kiri -->
                    <td style="width: 50%; vertical-align: top; border: none; padding: 0 12px 0 4px;">
                        <table style="width: 100%; border-collapse: collapse; border: none;">
                            <tr>
                                <td style="width: 135px; color: #475569; padding: 2.5px 0; border: none; font-size: 0.78rem;">Nama Peserta Didik</td>
                                <td style="width: 12px; color: #0f172a; font-weight: bold; border: none; text-align: center; font-size: 0.78rem;">:</td>
                                <td style="font-weight: 700; color: #0f172a; padding: 2.5px 0; border: none; font-size: 0.78rem;"><?= htmlspecialchars($siswa['nama_lengkap'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td style="width: 135px; color: #475569; padding: 2.5px 0; border: none; font-size: 0.78rem;">NIS / NISN</td>
                                <td style="width: 12px; color: #0f172a; font-weight: bold; border: none; text-align: center; font-size: 0.78rem;">:</td>
                                <td style="font-weight: 700; color: #0f172a; padding: 2.5px 0; border: none; font-size: 0.78rem;"><?= htmlspecialchars($siswa['nis'] ?? '-') ?> / <?= htmlspecialchars($siswa['nisn'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td style="width: 135px; color: #475569; padding: 2.5px 0; border: none; font-size: 0.78rem;">Kelas / Rombel</td>
                                <td style="width: 12px; color: #0f172a; font-weight: bold; border: none; text-align: center; font-size: 0.78rem;">:</td>
                                <td style="font-weight: 700; color: #0f172a; padding: 2.5px 0; border: none; font-size: 0.78rem;"><?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </td>
                    <!-- Kolom Kanan -->
                    <td style="width: 50%; vertical-align: top; border: none; padding: 0 4px 0 12px;">
                        <table style="width: 100%; border-collapse: collapse; border: none;">
                            <tr>
                                <td style="width: 135px; color: #475569; padding: 2.5px 0; border: none; font-size: 0.78rem;">Program Keahlian</td>
                                <td style="width: 12px; color: #0f172a; font-weight: bold; border: none; text-align: center; font-size: 0.78rem;">:</td>
                                <td style="font-weight: 700; color: #0f172a; padding: 2.5px 0; border: none; font-size: 0.78rem;"><?= htmlspecialchars($siswa['nama_jurusan'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td style="width: 135px; color: #475569; padding: 2.5px 0; border: none; font-size: 0.78rem;">Fase & Kurikulum</td>
                                <td style="width: 12px; color: #0f172a; font-weight: bold; border: none; text-align: center; font-size: 0.78rem;">:</td>
                                <td style="font-weight: 700; color: #0f172a; padding: 2.5px 0; border: none; font-size: 0.78rem;"><?= htmlspecialchars($faseText) ?> (<?= htmlspecialchars($kurikulumText) ?>)</td>
                            </tr>
                            <tr>
                                <td style="width: 135px; color: #475569; padding: 2.5px 0; border: none; font-size: 0.78rem;">Status E-Rapor</td>
                                <td style="width: 12px; color: #0f172a; font-weight: bold; border: none; text-align: center; font-size: 0.78rem;">:</td>
                                <td style="font-weight: 700; color: #166534; padding: 2.5px 0; border: none; font-size: 0.78rem;"><i class="bi bi-patch-check-fill me-1 text-primary"></i> <?= htmlspecialchars(ucfirst($raporData['status'] ?? 'Terverifikasi')) ?> Resmi</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ASSESSMENT COMPONENTS INFO BADGE (Screen Only) -->
        <div class="p-3 mb-4 rounded-3 border bg-light d-flex flex-wrap align-items-center justify-content-between gap-3 no-print">
            <div class="d-flex align-items-center gap-2.5">
                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle">
                    <i class="bi bi-calculator-fill fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0 fs-6">Komponen Penilaian E-Rapor</h6>
                    <small class="text-muted">Kalkulasi Nilai Akhir mengacu pada instrumen penilaian resmi <?= htmlspecialchars($kurikulumText) ?>:</small>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php if (!empty($komponenList)): ?>
                    <?php 
                    $badgeStyles = [
                        ['icon' => 'bi-file-earmark-text', 'class' => 'text-primary'],
                        ['icon' => 'bi-ui-checks', 'class' => 'text-warning'],
                        ['icon' => 'bi-calendar2-check', 'class' => 'text-info'],
                        ['icon' => 'bi-award-fill', 'class' => 'text-success'],
                        ['icon' => 'bi-tools', 'class' => 'text-secondary']
                    ];
                    $idxC = 0;
                    foreach ($komponenList as $kp):
                        $bStyle = $badgeStyles[$idxC % count($badgeStyles)];
                        $idxC++;
                    ?>
                        <span class="badge bg-white text-dark border shadow-xs px-2.5 py-1.5" style="font-size:0.75rem;">
                            <i class="bi <?= $bStyle['icon'] ?> <?= $bStyle['class'] ?> me-1"></i> <?= htmlspecialchars($kp['nama_komponen']) ?> <strong>(<?= (float)$kp['bobot_persen'] ?>%)</strong>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="badge bg-white text-dark border shadow-xs px-2.5 py-1.5" style="font-size:0.75rem;">
                        <i class="bi bi-file-earmark-text text-primary me-1"></i> <?= htmlspecialchars($lblTugas) ?> <strong>(<?= $pTugas ?>%)</strong>
                    </span>
                    <span class="badge bg-white text-dark border shadow-xs px-2.5 py-1.5" style="font-size:0.75rem;">
                        <i class="bi bi-ui-checks text-warning me-1"></i> <?= htmlspecialchars($lblQuiz) ?> <strong>(<?= $pQuiz ?>%)</strong>
                    </span>
                    <span class="badge bg-white text-dark border shadow-xs px-2.5 py-1.5" style="font-size:0.75rem;">
                        <i class="bi bi-calendar2-check text-info me-1"></i> <?= htmlspecialchars($lblUts) ?> <strong>(<?= $pUts ?>%)</strong>
                    </span>
                    <span class="badge bg-white text-dark border shadow-xs px-2.5 py-1.5" style="font-size:0.75rem;">
                        <i class="bi bi-award-fill text-success me-1"></i> <?= htmlspecialchars($lblUas) ?> <strong>(<?= $pUas ?>%)</strong>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- MOBILE GRADE CARD VIEW (Screen Only - Displays on Mobile < 768px) -->
        <?php if (!empty($nilaiList)): ?>
            <div class="d-block d-md-none mb-4 no-print">
                <h6 class="fw-bold text-dark mb-2.5"><i class="bi bi-grid-fill text-primary me-1.5"></i>Ringkasan Nilai Mata Pelajaran:</h6>
                <div class="row g-2.5">
                    <?php 
                    $mobTotalAkhir = 0;
                    $mobTotalTugas = 0;
                    $mobTotalQuiz  = 0;
                    $mobTotalUts   = 0;
                    $mobTotalUas   = 0;
                    $mobAllTuntas  = true;

                    foreach ($nilaiList as $mn):
                        $kkmVal = (float)($mn['kkm'] ?? 75);
                        $recalcAkhir = NilaiModel::hitungNilaiAkhir(
                            (float)($mn['nilai_tugas'] ?? 0),
                            (float)($mn['nilai_quiz'] ?? 0),
                            (float)($mn['nilai_uts'] ?? 0),
                            (float)($mn['nilai_uas'] ?? 0),
                            $bobotKomponen
                        );
                        $rowAkhir = ($recalcAkhir > 0 || (float)($mn['nilai_akhir'] ?? 0) <= 0) ? $recalcAkhir : (float)$mn['nilai_akhir'];
                        $pred = NilaiModel::getPredikat($rowAkhir);
                        $isTuntas = ($rowAkhir >= $kkmVal);
                        if (!$isTuntas) $mobAllTuntas = false;

                        $mobTotalTugas += (float)($mn['nilai_tugas'] ?? 0);
                        $mobTotalQuiz  += (float)($mn['nilai_quiz'] ?? 0);
                        $mobTotalUts   += (float)($mn['nilai_uts'] ?? 0);
                        $mobTotalUas   += (float)($mn['nilai_uas'] ?? 0);
                        $mobTotalAkhir += $rowAkhir;
                    ?>
                        <div class="col-12">
                            <div class="p-3 bg-white rounded-3 border shadow-xs">
                                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 fs-6"><?= htmlspecialchars($mn['nama_mapel']) ?></h6>
                                        <span class="badge bg-secondary rounded-pill" style="font-size:0.68rem;">KKM: <?= (int)$kkmVal ?></span>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <span class="fw-bold fs-5 text-primary d-block"><?= number_format($rowAkhir, 1) ?></span>
                                        <span class="badge <?= $pred['class'] ?> rounded-pill px-2 py-0.5" style="font-size:0.68rem;"><?= $pred['grade'] ?></span>
                                        <span class="badge <?= $isTuntas ? 'bg-success' : 'bg-danger' ?> rounded-pill px-2 py-0.5" style="font-size:0.68rem;"><?= $isTuntas ? 'TUNTAS' : 'BELUM' ?></span>
                                    </div>
                                </div>
                                <div class="row g-2 text-center bg-light rounded-3 p-2 border" style="font-size:0.75rem;">
                                    <div class="col-6 col-sm-3">
                                        <span class="text-muted d-block small" style="font-size:0.68rem; line-height: 1.2;"><?= htmlspecialchars($lblTugas) ?></span>
                                        <strong class="fs-6 text-dark d-block mt-0.5"><?= number_format((float)($mn['nilai_tugas'] ?? 0), 0) ?></strong>
                                        <span class="text-primary small fw-semibold" style="font-size:0.65rem;">(<?= $pTugas ?>%)</span>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <span class="text-muted d-block small" style="font-size:0.68rem; line-height: 1.2;"><?= htmlspecialchars($lblQuiz) ?></span>
                                        <strong class="fs-6 text-dark d-block mt-0.5"><?= number_format((float)($mn['nilai_quiz'] ?? 0), 0) ?></strong>
                                        <span class="text-warning-emphasis small fw-semibold" style="font-size:0.65rem;">(<?= $pQuiz ?>%)</span>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <span class="text-muted d-block small" style="font-size:0.68rem; line-height: 1.2;"><?= htmlspecialchars($lblUts) ?></span>
                                        <strong class="fs-6 text-dark d-block mt-0.5"><?= number_format((float)($mn['nilai_uts'] ?? 0), 0) ?></strong>
                                        <span class="text-info-emphasis small fw-semibold" style="font-size:0.65rem;">(<?= $pUts ?>%)</span>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <span class="text-muted d-block small" style="font-size:0.68rem; line-height: 1.2;"><?= htmlspecialchars($lblUas) ?></span>
                                        <strong class="fs-6 text-dark d-block mt-0.5"><?= number_format((float)($mn['nilai_uas'] ?? 0), 0) ?></strong>
                                        <span class="text-success small fw-semibold" style="font-size:0.65rem;">(<?= $pUas ?>%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php 
                    $mobCount = count($nilaiList);
                    $mobAvgAkhir = $mobCount > 0 ? ($mobTotalAkhir / $mobCount) : 0;
                    $mobAvgTugas = $mobCount > 0 ? ($mobTotalTugas / $mobCount) : 0;
                    $mobAvgQuiz  = $mobCount > 0 ? ($mobTotalQuiz / $mobCount) : 0;
                    $mobAvgUts   = $mobCount > 0 ? ($mobTotalUts / $mobCount) : 0;
                    $mobAvgUas   = $mobCount > 0 ? ($mobTotalUas / $mobCount) : 0;
                    $mobPred = NilaiModel::getPredikat($mobAvgAkhir);
                    ?>
                    <!-- Mobile Average Summary Card -->
                    <div class="col-12 mt-2">
                        <div class="p-3 rounded-3 border bg-primary bg-opacity-10 border-primary shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-primary fs-6"><i class="bi bi-calculator-fill me-1.5"></i>Rata-Rata Nilai Akhir:</span>
                                <div class="text-end">
                                    <span class="fw-bold fs-5 text-primary"><?= number_format($mobAvgAkhir, 1) ?></span>
                                    <span class="badge <?= $mobPred['class'] ?> rounded-pill px-2 py-0.5" style="font-size:0.68rem;"><?= $mobPred['grade'] ?></span>
                                    <span class="badge <?= $mobAllTuntas ? 'bg-success' : 'bg-warning text-dark' ?> rounded-pill px-2 py-0.5" style="font-size:0.68rem;"><?= $mobAllTuntas ? 'TUNTAS' : 'REMEDIAL' ?></span>
                                </div>
                            </div>
                            <div class="row g-2 text-center bg-white rounded-3 p-2 border" style="font-size:0.75rem;">
                                <div class="col-6 col-sm-3">
                                    <span class="text-muted d-block small" style="font-size:0.68rem;">Rata-Rata Tugas</span>
                                    <strong class="fs-6 text-dark d-block"><?= number_format($mobAvgTugas, 1) ?></strong>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <span class="text-muted d-block small" style="font-size:0.68rem;">Rata-Rata Kuis</span>
                                    <strong class="fs-6 text-dark d-block"><?= number_format($mobAvgQuiz, 1) ?></strong>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <span class="text-muted d-block small" style="font-size:0.68rem;">Rata-Rata STS</span>
                                    <strong class="fs-6 text-dark d-block"><?= number_format($mobAvgUts, 1) ?></strong>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <span class="text-muted d-block small" style="font-size:0.68rem;">Rata-Rata SAS</span>
                                    <strong class="fs-6 text-dark d-block"><?= number_format($mobAvgUas, 1) ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- OFFICIAL GRADE TRANSKRIP DATA CALCULATION -->
        <?php
        $capaianMap = [];
        if (!empty($raporData['nilai_list'])) {
            foreach ($raporData['nilai_list'] as $rd) {
                $capaianMap[$rd['mapel_id']] = $rd['capaian_kompetensi'] ?? '';
            }
        }

        $calculatedRows = [];
        $totalAkhir = 0;
        $totalTugas = 0;
        $totalQuiz  = 0;
        $totalUts   = 0;
        $totalUas   = 0;
        $allTuntas  = true;

        if (!empty($nilaiList)) {
            foreach ($nilaiList as $i => $n) {
                $kkmVal = (float)($n['kkm'] ?? 75);
                $recalcAkhir = NilaiModel::hitungNilaiAkhir(
                    (float)($n['nilai_tugas'] ?? 0),
                    (float)($n['nilai_quiz'] ?? 0),
                    (float)($n['nilai_uts'] ?? 0),
                    (float)($n['nilai_uas'] ?? 0),
                    $bobotKomponen
                );
                $akhirRow = ($recalcAkhir > 0 || (float)($n['nilai_akhir'] ?? 0) <= 0) ? $recalcAkhir : (float)$n['nilai_akhir'];
                $pred = NilaiModel::getPredikat($akhirRow);
                $isTuntas = ($akhirRow >= $kkmVal);
                if (!$isTuntas) $allTuntas = false;

                $totalTugas += (float)($n['nilai_tugas'] ?? 0);
                $totalQuiz  += (float)($n['nilai_quiz'] ?? 0);
                $totalUts   += (float)($n['nilai_uts'] ?? 0);
                $totalUas   += (float)($n['nilai_uas'] ?? 0);
                $totalAkhir += $akhirRow;

                $deskripsiCapaian = $capaianMap[$n['mapel_id']] ?? (
                    $isTuntas 
                    ? "Menunjukkan penguasaan sangat baik dalam menuntaskan seluruh tujuan pembelajaran {$n['nama_mapel']}."
                    : "Perlu bimbingan dan tindak lanjut remedial pada beberapa kompetensi dasar mata pelajaran {$n['nama_mapel']}."
                );

                $calculatedRows[] = [
                    'no' => $i + 1,
                    'mapel' => $n['nama_mapel'],
                    'kkm' => $kkmVal,
                    'tugas' => (float)($n['nilai_tugas'] ?? 0),
                    'quiz' => (float)($n['nilai_quiz'] ?? 0),
                    'uts' => (float)($n['nilai_uts'] ?? 0),
                    'uas' => (float)($n['nilai_uas'] ?? 0),
                    'akhir' => $akhirRow,
                    'pred' => $pred,
                    'is_tuntas' => $isTuntas,
                    'deskripsi' => $deskripsiCapaian
                ];
            }
        }
        $countMapel = count($calculatedRows);
        $avgTugas = $countMapel > 0 ? ($totalTugas / $countMapel) : 0;
        $avgQuiz  = $countMapel > 0 ? ($totalQuiz  / $countMapel) : 0;
        $avgUts   = $countMapel > 0 ? ($totalUts   / $countMapel) : 0;
        $avgUas   = $countMapel > 0 ? ($totalUas   / $countMapel) : 0;
        $avgAkhir = $countMapel > 0 ? ($totalAkhir / $countMapel) : 0;
        $avgPred  = NilaiModel::getPredikat($avgAkhir);
        ?>

        <!-- SCREEN TABLE (Hanya Tampil di Layar / Desktop & Tablet) -->
        <div class="rapor-table-scroll mb-4 d-print-none">
            <table class="table grade-table table-bordered text-center align-middle mb-0">
                <thead class="grade-table-header">
                    <tr class="header-screen">
                        <th class="text-start" rowspan="2" style="width:40px;">No</th>
                        <th class="text-start" rowspan="2" style="min-width:180px;">Mata Pelajaran</th>
                        <th rowspan="2" style="width:60px;">KKM</th>
                        <th colspan="4" class="text-center py-2 col-komponen" style="background-color: #f1f5f9;">Komponen Penilaian</th>
                        <th rowspan="2" style="width:85px;">Nilai Akhir</th>
                        <th rowspan="2" style="width:75px;">Predikat</th>
                        <th rowspan="2" style="width:95px;">Ketuntasan</th>
                        <th rowspan="2" class="text-start cell-deskripsi" style="min-width:240px;">Deskripsi Capaian Kompetensi</th>
                    </tr>
                    <tr class="header-screen" style="background-color: #f8fafc;">
                        <th style="min-width:130px; vertical-align:middle;" class="px-2 py-2 col-komponen">
                            <div class="fw-bold text-dark" style="font-size:0.8rem; line-height:1.25;"><?= htmlspecialchars($lblTugas) ?></div>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0.5 mt-1" style="font-size:0.68rem; font-weight:600;">(<?= $pTugas ?>%)</span>
                        </th>
                        <th style="min-width:130px; vertical-align:middle;" class="px-2 py-2 col-komponen">
                            <div class="fw-bold text-dark" style="font-size:0.8rem; line-height:1.25;"><?= htmlspecialchars($lblQuiz) ?></div>
                            <span class="badge bg-warning bg-opacity-10 text-dark rounded-pill px-2 py-0.5 mt-1" style="font-size:0.68rem; font-weight:600; background-color: rgba(245, 158, 11, 0.15) !important;">(<?= $pQuiz ?>%)</span>
                        </th>
                        <th style="min-width:140px; vertical-align:middle;" class="px-2 py-2 col-komponen">
                            <div class="fw-bold text-dark" style="font-size:0.8rem; line-height:1.25;"><?= htmlspecialchars($lblUts) ?></div>
                            <span class="badge bg-info bg-opacity-10 text-dark rounded-pill px-2 py-0.5 mt-1" style="font-size:0.68rem; font-weight:600; background-color: rgba(14, 165, 233, 0.15) !important;">(<?= $pUts ?>%)</span>
                        </th>
                        <th style="min-width:140px; vertical-align:middle;" class="px-2 py-2 col-komponen">
                            <div class="fw-bold text-dark" style="font-size:0.8rem; line-height:1.25;"><?= htmlspecialchars($lblUas) ?></div>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5 mt-1" style="font-size:0.68rem; font-weight:600; background-color: rgba(16, 185, 129, 0.15) !important;">(<?= $pUas ?>%)</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($calculatedRows)): ?>
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada data nilai yang diinput Guru Pengampu. Nilai E-Rapor akan muncul setelah Guru menyimpan nilai rombel.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($calculatedRows as $row): ?>
                        <tr>
                            <td class="text-center"><?= $row['no'] ?></td>
                            <td class="text-start fw-bold text-dark"><?= htmlspecialchars($row['mapel']) ?></td>
                            <td class="text-center"><span class="badge bg-secondary rounded-pill"><?= (int)$row['kkm'] ?></span></td>
                            <td class="col-komponen text-center"><?= number_format($row['tugas'], 0) ?></td>
                            <td class="col-komponen text-center"><?= number_format($row['quiz'], 0) ?></td>
                            <td class="col-komponen text-center"><?= number_format($row['uts'], 0) ?></td>
                            <td class="col-komponen text-center"><?= number_format($row['uas'], 0) ?></td>
                            <td class="fw-bold fs-6 text-primary text-center"><?= number_format($row['akhir'], 1) ?></td>
                            <td class="text-center">
                                <span class="badge <?= $row['pred']['class'] ?> rounded-pill px-2.5 py-1">
                                    <?= $row['pred']['grade'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $row['is_tuntas'] ? 'bg-success' : 'bg-danger' ?> rounded-pill px-2 py-1" style="font-size:0.75rem;">
                                    <?= $row['is_tuntas'] ? 'TUNTAS' : 'BELUM' ?>
                                </span>
                            </td>
                            <td class="text-start small cell-deskripsi" style="color: #1e293b;">
                                <?= htmlspecialchars($row['deskripsi']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <tr class="table-primary fw-bold text-center align-middle" style="background-color: #e0e7ff !important; border-top: 2px solid #6366f1;">
                            <td colspan="3" class="text-end fw-bold py-2.5 px-3" style="letter-spacing: 0.3px;">RATA-RATA NILAI AKHIR SEMESTER</td>
                            <td class="fw-bold text-dark col-komponen"><?= number_format($avgTugas, 1) ?></td>
                            <td class="fw-bold text-dark col-komponen"><?= number_format($avgQuiz, 1) ?></td>
                            <td class="fw-bold text-dark col-komponen"><?= number_format($avgUts, 1) ?></td>
                            <td class="fw-bold text-dark col-komponen"><?= number_format($avgUas, 1) ?></td>
                            <td class="fs-6 text-primary fw-bold"><?= number_format($avgAkhir, 1) ?></td>
                            <td>
                                <span class="badge <?= $avgPred['class'] ?> rounded-pill px-2.5 py-1">
                                    <?= $avgPred['grade'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $allTuntas ? 'bg-success' : 'bg-warning text-dark' ?> rounded-pill px-2 py-1" style="font-size:0.75rem;">
                                    <?= $allTuntas ? 'TUNTAS' : 'REMEDIAL' ?>
                                </span>
                            </td>
                            <td class="text-start small text-primary fw-semibold cell-deskripsi">
                                <?= $allTuntas 
                                    ? 'Status Akademik: Memenuhi Kriteria Ketercapaian Tujuan Pembelajaran (KKTP).' 
                                    : 'Status Akademik: Terdapat mata pelajaran yang memerlukan pendampingan/remedial.' ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- DEDICATED PRINT TABLE (Hanya Tampil Saat Print - 7 Kolom Presisi, 100% Background Putih, Rata-Rata Diperkecil) -->
        <div class="d-none d-print-block mb-3">
            <table class="table-print-official" style="width: 100%; border-collapse: collapse; border: 1.5px solid #0f172a; background: #ffffff; table-layout: fixed; font-size: 0.82rem;">
                <thead>
                    <tr style="background: #ffffff;">
                        <th style="width: 5%; text-align: center !important; white-space: nowrap; padding: 7px 3px;">NO</th>
                        <th style="width: 25%; text-align: center !important; white-space: nowrap; padding: 7px 6px;">MATA PELAJARAN</th>
                        <th style="width: 6%; text-align: center !important; white-space: nowrap; padding: 7px 3px;">KKM</th>
                        <th style="width: 11%; text-align: center !important; white-space: nowrap; padding: 7px 3px;">NILAI AKHIR</th>
                        <th style="width: 10%; text-align: center !important; white-space: nowrap; padding: 7px 4px;">PREDIKAT</th>
                        <th style="width: 13%; text-align: center !important; white-space: nowrap; padding: 7px 4px;">KETUNTASAN</th>
                        <th style="width: 30%; text-align: center !important; white-space: nowrap; padding: 7px 6px;">CAPAIAN KOMPETENSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($calculatedRows)): ?>
                        <tr>
                            <td colspan="7" style="border: 1px solid #0f172a; padding: 14px; text-align: center; color: #475569; background: #ffffff; font-size: 0.82rem;">
                                Belum ada data nilai yang diinput Guru Pengampu.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($calculatedRows as $row): ?>
                        <tr style="background: #ffffff; page-break-inside: avoid !important; break-inside: avoid !important;">
                            <td style="border: 1px solid #0f172a; padding: 5px 3px; text-align: center; color: #0f172a; background: #ffffff; vertical-align: middle; font-size: 0.80rem;"><?= $row['no'] ?></td>
                            <td style="border: 1px solid #0f172a; padding: 5px 6px; text-align: left; font-weight: 700; color: #0f172a; background: #ffffff; vertical-align: middle; font-size: 0.84rem;"><?= htmlspecialchars($row['mapel']) ?></td>
                            <td style="border: 1px solid #0f172a; padding: 5px 3px; text-align: center; color: #0f172a; background: #ffffff; vertical-align: middle; font-size: 0.82rem;"><?= (int)$row['kkm'] ?></td>
                            <td style="border: 1px solid #0f172a; padding: 5px 3px; text-align: center; font-weight: 800; font-size: 0.92rem; color: #0f172a; background: #ffffff; vertical-align: middle;"><?= number_format($row['akhir'], 1) ?></td>
                            <td style="border: 1px solid #0f172a; padding: 5px 3px; text-align: center; background: #ffffff; vertical-align: middle;">
                                <span style="display: inline-block; border: 1px solid #0f172a; border-radius: 4px; padding: 2px 6px; font-weight: 700; font-size: 0.80rem; color: #0f172a; background: #ffffff;"><?= $row['pred']['grade'] ?></span>
                            </td>
                            <td style="border: 1px solid #0f172a; padding: 5px 4px; text-align: center; background: #ffffff; vertical-align: middle; white-space: nowrap;">
                                <span style="display: inline-block; border: 1px solid #0f172a; border-radius: 4px; padding: 2px 7px; font-weight: 700; font-size: 0.74rem; letter-spacing: 0.3px; color: #0f172a; background: #ffffff;"><?= $row['is_tuntas'] ? 'TUNTAS' : 'REMEDIAL' ?></span>
                            </td>
                            <td style="border: 1px solid #0f172a; padding: 5.5px 6px; text-align: left; font-size: 0.78rem; line-height: 1.38; color: #0f172a; background: #ffffff; vertical-align: middle;">
                                <?= htmlspecialchars($row['deskripsi']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <!-- BARIS RATA-RATA: DITAMPILKAN DI AKHIR SETELAH SELURUH MAPEL SELESAI DICETAK -->
                        <tr style="background: #ffffff; border-top: 2px solid #0f172a; page-break-inside: avoid !important; break-inside: avoid !important;">
                            <td colspan="3" style="border: 1px solid #0f172a; padding: 6px 6px; text-align: right; font-weight: 700; font-size: 0.82rem; letter-spacing: 0.2px; white-space: nowrap; color: #0f172a; background: #ffffff; vertical-align: middle;">
                                RATA-RATA NILAI AKHIR SEMESTER
                            </td>
                            <td style="border: 1px solid #0f172a; padding: 6px 3px; text-align: center; font-weight: 800; font-size: 0.94rem; color: #0f172a; background: #ffffff; vertical-align: middle;">
                                <?= number_format($avgAkhir, 1) ?>
                            </td>
                            <td style="border: 1px solid #0f172a; padding: 6px 3px; text-align: center; background: #ffffff; vertical-align: middle;">
                                <span style="display: inline-block; border: 1px solid #0f172a; border-radius: 4px; padding: 2px 6px; font-weight: 700; font-size: 0.80rem; color: #0f172a; background: #ffffff;"><?= $avgPred['grade'] ?></span>
                            </td>
                            <td style="border: 1px solid #0f172a; padding: 6px 4px; text-align: center; background: #ffffff; vertical-align: middle; white-space: nowrap;">
                                <span style="display: inline-block; border: 1px solid #0f172a; border-radius: 4px; padding: 2px 7px; font-weight: 700; font-size: 0.74rem; letter-spacing: 0.3px; color: #0f172a; background: #ffffff;"><?= $allTuntas ? 'TUNTAS' : 'REMEDIAL' ?></span>
                            </td>
                            <td style="border: 1px solid #0f172a; padding: 6px 6px; text-align: left; font-size: 0.78rem; line-height: 1.35; font-weight: 600; color: #0f172a; background: #ffffff; vertical-align: middle;">
                                <?= $allTuntas 
                                    ? 'Status Akademik: Memenuhi Kriteria Ketercapaian Tujuan Pembelajaran (KKTP).' 
                                    : 'Status Akademik: Terdapat mata pelajaran yang memerlukan pendampingan/remedial.' ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- TABEL REKAP KETIDAKHADIRAN & CATATAN WALI KELAS (PRINT VERSION) -->
        <div class="d-none d-print-block mb-3" style="page-break-inside: avoid; break-inside: avoid;">
            <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #0f172a; background: #ffffff; table-layout: fixed; font-size: 0.80rem;">
                <tr>
                    <!-- Kolom Kiri: Rekap Ketidakhadiran (Sakit, Izin, Alpa) -->
                    <td style="width: 45%; border-right: 1.5px solid #0f172a; padding: 0; vertical-align: top; background: #ffffff;">
                        <table style="width: 100%; border-collapse: collapse; background: #ffffff;">
                            <thead>
                                <tr style="background: #ffffff; border-bottom: 1.5px solid #0f172a;">
                                    <th colspan="3" style="padding: 6px 8px; text-align: center; font-weight: 700; font-size: 0.82rem; color: #0f172a; letter-spacing: 0.2px;">
                                        REKAP KETIDAKHADIRAN SISWA
                                    </th>
                                </tr>
                                <tr style="background: #ffffff; border-bottom: 1px solid #0f172a;">
                                    <th style="width: 15%; border-right: 1px solid #0f172a; padding: 4px 4px; text-align: center; font-size: 0.78rem; color: #0f172a;">NO</th>
                                    <th style="width: 55%; border-right: 1px solid #0f172a; padding: 4px 6px; text-align: left; font-size: 0.78rem; color: #0f172a;">KETERANGAN</th>
                                    <th style="width: 30%; padding: 4px 4px; text-align: center; font-size: 0.78rem; color: #0f172a;">JUMLAH</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid #0f172a;">
                                    <td style="border-right: 1px solid #0f172a; padding: 4.5px 4px; text-align: center; color: #0f172a;">1</td>
                                    <td style="border-right: 1px solid #0f172a; padding: 4.5px 6px; color: #0f172a;">Sakit (S)</td>
                                    <td style="padding: 4.5px 4px; text-align: center; font-weight: 700; color: #0f172a;"><?= (int)($absensiRekap['sakit'] ?? 0) ?> hari</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #0f172a;">
                                    <td style="border-right: 1px solid #0f172a; padding: 4.5px 4px; text-align: center; color: #0f172a;">2</td>
                                    <td style="border-right: 1px solid #0f172a; padding: 4.5px 6px; color: #0f172a;">Izin (I)</td>
                                    <td style="padding: 4.5px 4px; text-align: center; font-weight: 700; color: #0f172a;"><?= (int)($absensiRekap['izin'] ?? 0) ?> hari</td>
                                </tr>
                                <tr>
                                    <td style="border-right: 1px solid #0f172a; padding: 4.5px 4px; text-align: center; color: #0f172a;">3</td>
                                    <td style="border-right: 1px solid #0f172a; padding: 4.5px 6px; color: #0f172a;">Tanpa Keterangan / Alpa (A)</td>
                                    <td style="padding: 4.5px 4px; text-align: center; font-weight: 700; color: #0f172a;"><?= (int)($absensiRekap['alpa'] ?? 0) ?> hari</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <!-- Kolom Kanan: Catatan Wali Kelas & Kedisiplinan -->
                    <td style="width: 55%; padding: 8px 12px; vertical-align: top; background: #ffffff;">
                        <div style="font-weight: 700; font-size: 0.82rem; color: #0f172a; margin-bottom: 4px; letter-spacing: 0.2px;">
                            CATATAN WALI KELAS & KEDISIPLINAN:
                        </div>
                        <div style="font-size: 0.80rem; line-height: 1.45; color: #0f172a;">
                            <?= !empty($raporData['catatan_wali_kelas']) 
                                ? nl2br(htmlspecialchars($raporData['catatan_wali_kelas'])) 
                                : 'Tingkatkan kedisiplinan belajar, pertahankan prestasi akademik, serta maksimalkan kehadiran pada setiap kegiatan pembelajaran semester berikutnya.' ?>
                        </div>
                        <div style="margin-top: 6px; font-size: 0.76rem; color: #475569;">
                            Kehadiran Tercatat: <strong><?= (int)($absensiRekap['hadir'] ?? 0) ?></strong> hari hadir dari total <strong><?= (int)($absensiRekap['total'] ?? 0) ?></strong> presensi.
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- TABEL EKSTRAKURIKULER SISWA (PRINT VERSION - DI BAWAH REKAP KETIDAKHADIRAN) -->
        <div class="d-none d-print-block mb-3" style="page-break-inside: avoid; break-inside: avoid;">
            <table class="table-print-official" style="width: 100%; border-collapse: collapse; border: 1.5px solid #0f172a; background: #ffffff; table-layout: fixed; font-size: 0.80rem;">
                <thead>
                    <tr style="background: #ffffff; border-bottom: 1.5px solid #0f172a;">
                        <th style="width: 5%; text-align: center !important; padding: 6px 3px;">NO</th>
                        <th style="width: 25%; text-align: center !important; padding: 6px 6px;">KEGIATAN EKSTRAKURIKULER</th>
                        <th style="width: 15%; text-align: center !important; padding: 6px 4px;">PREDIKAT</th>
                        <th style="width: 55%; text-align: center !important; padding: 6px 6px;">KETERANGAN / NILAI CAPAIAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ekskulList)): ?>
                        <tr>
                            <td style="border: 1px solid #0f172a; padding: 5px 3px; text-align: center; color: #475569;">1</td>
                            <td style="border: 1px solid #0f172a; padding: 5px 6px; text-align: left; color: #475569;">-</td>
                            <td style="border: 1px solid #0f172a; padding: 5px 4px; text-align: center; color: #475569;">-</td>
                            <td style="border: 1px solid #0f172a; padding: 5px 6px; text-align: left; color: #475569;">Belum mengikuti kegiatan ekstrakurikuler pada semester ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ekskulList as $iEks => $ek): ?>
                            <tr>
                                <td style="border: 1px solid #0f172a; padding: 5px 3px; text-align: center; color: #0f172a; vertical-align: middle; font-size: 0.80rem;"><?= $iEks + 1 ?></td>
                                <td style="border: 1px solid #0f172a; padding: 5px 6px; text-align: left; font-weight: 700; color: #0f172a; vertical-align: middle; font-size: 0.82rem;">
                                    <?= htmlspecialchars($ek['nama_ekskul']) ?>
                                </td>
                                <td style="border: 1px solid #0f172a; padding: 5px 4px; text-align: center; font-weight: 700; color: #0f172a; vertical-align: middle; font-size: 0.80rem;">
                                    <?= htmlspecialchars($ek['predikat'] ?: 'Sangat Baik') ?>
                                </td>
                                <td style="border: 1px solid #0f172a; padding: 5px 6px; text-align: left; font-size: 0.78rem; line-height: 1.38; color: #0f172a; vertical-align: middle;">
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

        <!-- SECTION REKAP KEHADIRAN & HISTORY ABSEN (SCREEN VIEW ONLY) -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 no-print" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                            <i class="bi bi-calendar2-check-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0 fs-6">Rekapitulasi Kehadiran & History Absensi Siswa</h6>
                            <small class="text-muted">Data presensi riil yang terhubung langsung dengan sistem absensi & presensi digital sekolah.</small>
                        </div>
                    </div>
                    <?php
                    $rateKehadiran = ($absensiRekap['total'] > 0) 
                        ? round(($absensiRekap['hadir'] / $absensiRekap['total']) * 100) 
                        : 100;
                    ?>
                    <div class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-xs">
                        Tingkat Kehadiran: <strong class="text-success ms-1"><?= $rateKehadiran ?>%</strong>
                    </div>
                </div>

                <!-- 4 METRIC CARDS: Hadir, Sakit, Izin, Alpa -->
                <div class="row g-2 g-md-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between shadow-xs">
                            <div>
                                <small class="text-muted d-block fw-semibold" style="font-size: 0.72rem;">HADIR</small>
                                <span class="fs-4 fw-bold text-success"><?= (int)($absensiRekap['hadir'] ?? 0) ?></span>
                                <small class="text-muted ms-1">Hari</small>
                            </div>
                            <div class="bg-success bg-opacity-10 text-success p-2.5 rounded-circle">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between shadow-xs">
                            <div>
                                <small class="text-muted d-block fw-semibold" style="font-size: 0.72rem;">SAKIT</small>
                                <span class="fs-4 fw-bold text-info"><?= (int)($absensiRekap['sakit'] ?? 0) ?></span>
                                <small class="text-muted ms-1">Hari</small>
                            </div>
                            <div class="bg-info bg-opacity-10 text-info p-2.5 rounded-circle">
                                <i class="bi bi-heart-pulse-fill fs-5"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between shadow-xs">
                            <div>
                                <small class="text-muted d-block fw-semibold" style="font-size: 0.72rem;">IZIN</small>
                                <span class="fs-4 fw-bold text-warning"><?= (int)($absensiRekap['izin'] ?? 0) ?></span>
                                <small class="text-muted ms-1">Hari</small>
                            </div>
                            <div class="bg-warning bg-opacity-10 text-warning p-2.5 rounded-circle">
                                <i class="bi bi-envelope-paper-fill fs-5"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between shadow-xs">
                            <div>
                                <small class="text-muted d-block fw-semibold" style="font-size: 0.72rem;">ALPA / TANPA KET.</small>
                                <span class="fs-4 fw-bold text-danger"><?= (int)($absensiRekap['alpa'] ?? 0) ?></span>
                                <small class="text-muted ms-1">Hari</small>
                            </div>
                            <div class="bg-danger bg-opacity-10 text-danger p-2.5 rounded-circle">
                                <i class="bi bi-x-circle-fill fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- HISTORY ABSEN DETAIL TABLE -->
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-dark small">
                            <i class="bi bi-clock-history text-primary me-1"></i> Rincian History Riwayat Absensi:
                        </span>
                        <span class="text-muted small" style="font-size: 0.75rem;">
                            Menampilkan <?= min(count($historyAbsen), 10) ?> riwayat presensi terakhir
                        </span>
                    </div>

                    <?php if (empty($historyAbsen)): ?>
                        <div class="text-center py-3 bg-white rounded-3 border text-muted small">
                            <i class="bi bi-calendar-check text-success fs-4 d-block mb-1"></i>
                            Belum ada riwayat absensi khusus tercatat atau kehadiran telah 100% tuntas.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive bg-white rounded-3 border shadow-xs" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.78rem;">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="text-center py-2" style="width: 45px;">No</th>
                                        <th class="py-2" style="width: 120px;">Tanggal</th>
                                        <th class="py-2">Mata Pelajaran / Agenda</th>
                                        <th class="py-2">Guru / Pengampu</th>
                                        <th class="text-center py-2" style="width: 110px;">Status</th>
                                        <th class="py-2">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $noAbs = 1;
                                    foreach ($historyAbsen as $ha): 
                                        $stLow = strtolower(trim($ha['status'] ?? 'hadir'));
                                        $badgeClass = 'bg-success-subtle text-success border border-success';
                                        $badgeIcon = 'bi-check-circle-fill';
                                        if ($stLow === 'sakit') {
                                            $badgeClass = 'bg-info-subtle text-info-emphasis border border-info';
                                            $badgeIcon = 'bi-heart-pulse-fill';
                                        } elseif ($stLow === 'izin' || $stLow === 'ijin') {
                                            $badgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning';
                                            $badgeIcon = 'bi-envelope-paper-fill';
                                        } elseif (in_array($stLow, ['alpa', 'alpha', 'tanpa keterangan'])) {
                                            $badgeClass = 'bg-danger-subtle text-danger border border-danger';
                                            $badgeIcon = 'bi-x-circle-fill';
                                        }
                                        $tglFormatted = !empty($ha['tanggal']) ? date('d M Y', strtotime($ha['tanggal'])) : '-';
                                    ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= $noAbs++ ?></td>
                                        <td class="fw-semibold text-dark"><?= $tglFormatted ?></td>
                                        <td><?= htmlspecialchars($ha['nama_mapel'] ?? '-') ?></td>
                                        <td class="text-muted"><?= htmlspecialchars($ha['nama_guru'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <span class="badge <?= $badgeClass ?> rounded-pill px-2.5 py-1" style="font-size: 0.70rem;">
                                                <i class="bi <?= $badgeIcon ?> me-1"></i><?= htmlspecialchars(ucfirst($ha['status'] ?? 'Hadir')) ?>
                                            </span>
                                        </td>
                                        <td class="text-muted">
                                            <?= !empty($ha['keterangan']) ? htmlspecialchars($ha['keterangan']) : '<span class="text-black-50 fst-italic">-</span>' ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- SECTION EKSTRAKURIKULER SISWA (SCREEN VIEW ONLY - DI BAWAH REKAP KEHADIRAN) -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 no-print" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                            <i class="bi bi-activity fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0 fs-6">Kegiatan Ekstrakurikuler yang Diikuti</h6>
                            <small class="text-muted">Capaian kompetensi dan nilai pembinaan minat-bakat siswa.</small>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>index.php?url=siswa/ekstrakurikuler" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold">
                        <i class="bi bi-compass me-1"></i> Jelajahi / Ikuti Ekskul
                    </a>
                </div>

                <?php if (empty($ekskulList)): ?>
                    <div class="text-center py-3 bg-white rounded-3 border text-muted small">
                        <i class="bi bi-activity text-secondary fs-4 d-block mb-1"></i>
                        Anda belum terdaftar dalam kegiatan ekstrakurikuler semester ini. Silakan pilih ekskul yang diminati melalui menu <a href="<?= BASE_URL ?>index.php?url=siswa/ekstrakurikuler" class="fw-bold text-primary">Kegiatan Ekstrakurikuler</a>.
                    </div>
                <?php else: ?>
                    <div class="table-responsive bg-white rounded-3 border shadow-xs">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.82rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 45px;" class="text-center">No</th>
                                    <th style="min-width: 170px;">Nama Ekstrakurikuler</th>
                                    <th style="min-width: 180px;">Pembimbing</th>
                                    <th style="width: 120px;" class="text-center">Predikat</th>
                                    <th style="min-width: 280px;">Keterangan / Nilai Capaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ekskulList as $iEks => $ek): ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= $iEks + 1 ?></td>
                                        <td>
                                            <strong class="text-dark d-block"><?= htmlspecialchars($ek['nama_ekskul']) ?></strong>
                                            <small class="text-muted"><?= htmlspecialchars($ek['hari'] ?: '-') ?> (<?= htmlspecialchars($ek['jam'] ?: '-') ?>)</small>
                                        </td>
                                        <td>
                                            <span class="text-dark fw-semibold d-block"><?= htmlspecialchars($ek['pembimbing']) ?></span>
                                            <?php if (!empty($ek['kontak_pembimbing'])): ?>
                                                <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($ek['kontak_pembimbing']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-2.5 py-1">
                                                <?= htmlspecialchars($ek['predikat'] ?: 'Sangat Baik') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <p class="mb-0 text-dark" style="font-size: 0.78rem; line-height: 1.35;">
                                                <?= !empty($ek['nilai_deskripsi']) 
                                                    ? htmlspecialchars($ek['nilai_deskripsi']) 
                                                    : '<span class="text-muted fst-italic">Belum ada deskripsi nilai capaian dari pembimbing.</span>' ?>
                                            </p>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Predikat Legend (Screen Only) -->
        <div class="row g-2 mb-4 no-print">
            <div class="col-12"><small class="fw-bold text-muted">Keterangan Skala Predikat SMK:</small></div>
            <?php foreach ([['A','88-100','Sangat Baik','success'],['B','78-87','Baik','primary'],['C','68-77','Cukup','warning text-dark'],['D','0-67','Kurang','danger']] as $p): ?>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3 border">
                        <span class="badge bg-<?= $p[3] ?> rounded-pill"><?= $p[0] ?></span>
                        <small style="font-size:0.75rem;"><?= $p[1] ?> — <?= $p[2] ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Blok Tanda Tangan Resmi & Keabsahan Sistem (Anti-Terbelah) -->
        <div class="avoid-break signature-seal-block" style="page-break-inside: avoid !important; break-inside: avoid !important; margin-top: 10px;">
            <!-- Signature Section (Tanda Tangan Resmi 3 Kolom Rapi) -->
            <div class="row g-2 mt-3 text-center signature-section" style="page-break-inside: avoid; break-inside: avoid;">
                <div class="col-4">
                    <p class="mb-0 small text-muted" style="font-size: 0.80rem;">Mengetahui,</p>
                    <p class="fw-bold mb-0 text-dark" style="font-size:0.86rem;">Orang Tua / Wali Siswa</p>
                    <div class="signature-line"></div>
                    <small class="text-muted d-block mt-1" style="font-size:0.76rem;">( ................................................ )</small>
                </div>
                <div class="col-4">
                    <p class="mb-0 small text-muted" style="font-size: 0.80rem;">Mengetahui,</p>
                    <p class="fw-bold mb-0 text-dark" style="font-size:0.86rem;">Kepala Sekolah</p>
                    <div class="signature-line"></div>
                    <small class="fw-bold text-dark d-block mt-1" style="font-size:0.84rem;"><?= htmlspecialchars($kepsekNama) ?></small>
                    <small class="text-muted d-block" style="font-size:0.74rem;">NIP/NUPTK: <?= htmlspecialchars($kepsekNip ?: '-') ?></small>
                </div>
                <div class="col-4">
                    <?php
                    $kotaSekolah = 'Cicalengka';
                    if (!empty($settings['alamat'])) {
                        if (stripos($settings['alamat'], 'Bandung') !== false) $kotaSekolah = 'Bandung';
                        elseif (stripos($settings['alamat'], 'Cicalengka') !== false) $kotaSekolah = 'Cicalengka';
                    }
                    ?>
                    <p class="mb-0 small text-muted" style="font-size: 0.80rem;"><?= htmlspecialchars($kotaSekolah) ?>, <?= date('d F Y') ?></p>
                    <p class="fw-bold mb-0 text-dark" style="font-size:0.86rem;">Wali Kelas Rombel</p>
                    <div class="signature-line"></div>
                    <?php if (!empty($waliKelas['nama_lengkap'])): ?>
                        <small class="fw-bold text-dark d-block mt-1" style="font-size:0.84rem;"><?= htmlspecialchars($waliKelas['nama_lengkap']) ?></small>
                        <small class="text-muted d-block" style="font-size:0.74rem;">NIP/NUPTK: <?= htmlspecialchars($waliKelas['nip'] ?: '-') ?></small>
                    <?php else: ?>
                        <small class="text-muted d-block mt-1" style="font-size:0.76rem;">( ................................................ )</small>
                        <small class="text-muted d-block" style="font-size:0.74rem;">Wali Kelas Belum Ditentukan</small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Official Modern Seal / Verification Footnote (Print Only) -->
            <div class="print-seal-note">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-patch-check-fill text-primary me-1"></i>
                        <strong>Dokumen Resmi E-Rapor Digital SMK Muthia Harapan Cicalengka</strong> — Dicetak melalui Sistem E-Learning & E-Rapor Resmi.
                    </div>
                    <div class="text-end fw-semibold">
                        Sah Tanpa Tanda Tangan Basah &bull; Validasi Sistem
                    </div>
                </div>
            </div>
        </div>

    </div><!-- end card paper -->

</div>
</main>

<script>
function printOfficialRapor() {
    const originalTitle = document.title;
    // Mengosongkan document.title sementara sebelum print agar browser tidak mencetak judul halaman di header
    document.title = '';
    window.print();
    // Mengembalikan document.title setelah dialog print
    setTimeout(function() {
        document.title = originalTitle;
    }, 1000);
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
