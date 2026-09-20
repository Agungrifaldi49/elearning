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

/* Screen & Print Layout Rules */
@media screen {
    .header-print {
        display: none !important;
    }
}

/* Print Friendly Styles */
@media print {
    .no-print, header, nav, .sidebar, .navbar, .main-content-header {
        display: none !important;
    }
    body, .main-content, .container-fluid, .rapor-wrapper {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .rapor-hero-banner {
        display: none !important;
    }
    .rapor-card-paper {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        border-radius: 0 !important;
    }
    .rapor-table-scroll {
        border: none !important;
        overflow: visible !important;
        display: block !important;
    }
    table.grade-table {
        width: 100% !important;
        min-width: 100% !important;
        table-layout: auto !important;
        border-collapse: collapse !important;
    }
    table.grade-table th, 
    table.grade-table td {
        border: 1px solid #1e293b !important;
        padding: 6px 8px !important;
    }
    .grade-table-header {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
    }

    /* Saat Print: Sembunyikan Header 2-Baris Screen */
    .header-screen {
        display: none !important;
    }
    /* Saat Print: Tampilkan Header Tunggal Resmi Tanpa Komponen */
    .header-print {
        display: table-row !important;
    }
    .header-print th {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        font-weight: 700 !important;
        font-size: 0.82rem !important;
        vertical-align: middle !important;
        text-align: center !important;
    }
    .header-print th.text-start {
        text-align: left !important;
    }

    /* SEMBUNYIKAN SELURUH KOMPONEN PENILAIAN (Tugas, Kuis, UTS, UAS) SAAT PRINT */
    .col-komponen {
        display: none !important;
    }

    /* DESKRIPSI CAPAIAN KOMPETENSI WAJIB TAMPIL PENUH & JELAS SAAT PRINT */
    .cell-deskripsi {
        display: table-cell !important;
        width: 42% !important;
        min-width: 220px !important;
        text-align: left !important;
        font-size: 0.76rem !important;
        line-height: 1.35 !important;
        color: #0f172a !important;
        word-wrap: break-word !important;
        white-space: normal !important;
    }

    ::-webkit-scrollbar {
        display: none !important;
    }
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .badge {
        border: 1px solid #64748b !important;
        color: #0f172a !important;
        background: transparent !important;
        font-size: 0.72rem !important;
    }
    .badge.bg-secondary {
        border-color: #64748b !important;
        color: #334155 !important;
    }
    .badge.bg-success {
        border-color: #166534 !important;
        color: #166534 !important;
    }
    .badge.bg-danger {
        border-color: #991b1b !important;
        color: #991b1b !important;
    }
    .badge.bg-warning {
        border-color: #854d0e !important;
        color: #854d0e !important;
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

            <button onclick="window.print()" class="btn btn-success fw-bold rounded-pill shadow-sm px-4 py-2.5 text-nowrap" style="font-size: 0.88rem; width: fit-content; max-width: 100%;">
                <i class="bi bi-printer-fill me-1.5"></i> Cetak / Simpan PDF E-Rapor
            </button>
        </div>
    </div>

    <!-- Rapor Container Paper -->
    <div class="rapor-card-paper p-3 p-sm-4 p-md-5 mb-4">

        <!-- School Header Kop -->
        <div class="text-center border-bottom pb-4 mb-4">
            <div class="d-flex align-items-center justify-content-center gap-3 mb-2 flex-wrap text-center text-sm-start">
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
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Sekolah" style="max-height:64px; width:auto;" class="img-fluid me-sm-1">
                <?php else: ?>
                    <div class="bg-primary text-white rounded-4 p-2.5 px-3 shadow-sm mx-auto mx-sm-0">
                        <i class="bi bi-mortarboard-fill fs-2"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <h5 class="fw-bold mb-0 text-primary" style="letter-spacing:0.5px;"><?= htmlspecialchars($settings['nama_sekolah'] ?? 'SMK MUTHIA HARAPAN CICALENGKA') ?></h5>
                    <small class="text-muted d-block"><?= htmlspecialchars($settings['alamat'] ?? 'Jl. Raya Cicalengka, Kab. Bandung, Jawa Barat 40395') ?> <?= !empty($settings['telepon']) ? '| Telp: ' . htmlspecialchars($settings['telepon']) : '' ?></small>
                </div>
            </div>
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
            ?>
            <div class="mt-3 d-flex justify-content-center gap-2 flex-wrap">
                <span class="fw-bold text-uppercase border border-2 border-primary d-inline-block px-3 py-1.5 rounded-pill bg-primary bg-opacity-10 text-primary" style="font-size:0.8rem;">
                    <i class="bi bi-award-fill me-1"></i> Laporan Hasil Belajar (E-Rapor) T.A. <?= htmlspecialchars($tahunAjaranText) ?> (Semester <?= htmlspecialchars($semesterText) ?>)
                </span>
                <span class="fw-bold border border-2 border-info d-inline-block px-3 py-1.5 rounded-pill bg-info bg-opacity-10 text-dark" style="font-size:0.8rem;">
                    <i class="bi bi-mortarboard-fill me-1 text-primary"></i> <?= htmlspecialchars($kurikulumText) ?> — <?= htmlspecialchars($faseText) ?>
                </span>
            </div>
        </div>

        <!-- Student Info Header Block -->
        <div class="row g-3 mb-4 p-3 rounded-4 border" style="background: #f8fafc; border-color: #e2e8f0 !important;">
            <div class="col-12 col-md-6">
                <table class="table table-sm table-borderless small mb-0">
                    <tbody>
                        <tr><td class="text-muted" style="width:40%">Nama Siswa</td><td class="fw-bold text-dark">: <?= htmlspecialchars($siswa['nama_lengkap'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">NIS / NISN</td><td class="fw-bold text-dark">: <?= htmlspecialchars($siswa['nis'] ?? '-') ?> / <?= htmlspecialchars($siswa['nisn'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">Rombel Kelas</td><td class="fw-bold text-dark">: <?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="col-12 col-md-6">
                <table class="table table-sm table-borderless small mb-0">
                    <tbody>
                        <tr><td class="text-muted" style="width:40%">Program Keahlian</td><td class="fw-bold text-dark">: <?= htmlspecialchars($siswa['nama_jurusan'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">Kurikulum & Fase</td><td class="fw-bold text-primary">: <?= htmlspecialchars($kurikulumText) ?> (<?= htmlspecialchars($faseText) ?>)</td></tr>
                        <tr><td class="text-muted">Status E-Rapor</td><td class="fw-bold text-success">: <i class="bi bi-patch-check-fill me-1"></i> <?= htmlspecialchars(ucfirst($raporData['status'] ?? 'Terverifikasi')) ?> Resmi</td></tr>
                    </tbody>
                </table>
            </div>
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

        <!-- OFFICIAL GRADE TRANSKRIP TABLE (Scrollable container on mobile, full width on print) -->
        <?php
        $capaianMap = [];
        if (!empty($raporData['nilai_list'])) {
            foreach ($raporData['nilai_list'] as $rd) {
                $capaianMap[$rd['mapel_id']] = $rd['capaian_kompetensi'] ?? '';
            }
        }
        ?>
        <div class="rapor-table-scroll mb-4">
            <table class="table grade-table table-bordered text-center align-middle mb-0">
                <thead class="grade-table-header">
                    <!-- SCREEN HEADER: 2 Baris Lengkap dengan Rincian Komponen Penilaian (Tampil di Layar Monitor/HP) -->
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

                    <!-- PRINT HEADER: Baris Tunggal Resmi (Tanpa Komponen, Langsung Nilai Akhir & Deskripsi Wajib Muncul) -->
                    <tr class="header-print">
                        <th class="text-center" style="width:35px;">No</th>
                        <th class="text-start" style="width:26%;">Mata Pelajaran</th>
                        <th class="text-center" style="width:55px;">KKM</th>
                        <th class="text-center" style="width:85px;">Nilai Akhir</th>
                        <th class="text-center" style="width:70px;">Predikat</th>
                        <th class="text-center" style="width:90px;">Ketuntasan</th>
                        <th class="text-start cell-deskripsi" style="width:44%;">Deskripsi Capaian Kompetensi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($nilaiList)): ?>
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada data nilai yang diinput Guru Pengampu. Nilai E-Rapor akan muncul setelah Guru menyimpan nilai rombel.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $totalAkhir = 0;
                        $totalTugas = 0;
                        $totalQuiz  = 0;
                        $totalUts   = 0;
                        $totalUas   = 0;
                        $allTuntas  = true;

                        foreach ($nilaiList as $i => $n):
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
                                ? "Menunjukkan penguasaan yang sangat baik dalam menuntaskan seluruh tujuan pembelajaran {$n['nama_mapel']}."
                                : "Perlu bimbingan dan tindak lanjut remedial pada beberapa kompetensi dasar mata pelajaran {$n['nama_mapel']}."
                            );
                        ?>
                        <tr>
                            <td class="text-center"><?= $i + 1 ?></td>
                            <td class="text-start fw-bold text-dark"><?= htmlspecialchars($n['nama_mapel']) ?></td>
                            <td class="text-center"><span class="badge bg-secondary rounded-pill"><?= (int)$kkmVal ?></span></td>
                            <td class="col-komponen text-center"><?= number_format((float)($n['nilai_tugas'] ?? 0), 0) ?></td>
                            <td class="col-komponen text-center"><?= number_format((float)($n['nilai_quiz'] ?? 0), 0) ?></td>
                            <td class="col-komponen text-center"><?= number_format((float)($n['nilai_uts'] ?? 0), 0) ?></td>
                            <td class="col-komponen text-center"><?= number_format((float)($n['nilai_uas'] ?? 0), 0) ?></td>
                            <td class="fw-bold fs-6 text-primary text-center"><?= number_format($akhirRow, 1) ?></td>
                            <td class="text-center">
                                <span class="badge <?= $pred['class'] ?> rounded-pill px-2.5 py-1">
                                    <?= $pred['grade'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $isTuntas ? 'bg-success' : 'bg-danger' ?> rounded-pill px-2 py-1" style="font-size:0.75rem;">
                                    <?= $isTuntas ? 'TUNTAS' : 'BELUM' ?>
                                </span>
                            </td>
                            <td class="text-start small cell-deskripsi" style="color: #1e293b;">
                                <?= htmlspecialchars($deskripsiCapaian) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php
                        $countMapel = count($nilaiList);
                        $avgTugas = $countMapel > 0 ? ($totalTugas / $countMapel) : 0;
                        $avgQuiz  = $countMapel > 0 ? ($totalQuiz  / $countMapel) : 0;
                        $avgUts   = $countMapel > 0 ? ($totalUts   / $countMapel) : 0;
                        $avgUas   = $countMapel > 0 ? ($totalUas   / $countMapel) : 0;
                        $avgAkhir = $countMapel > 0 ? ($totalAkhir / $countMapel) : 0;
                        $avgPred  = NilaiModel::getPredikat($avgAkhir);
                        ?>
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

        <!-- Signature Section -->
        <div class="row g-4 mt-4 text-center signature-section" style="page-break-inside: avoid; break-inside: avoid;">
            <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                <p class="mb-0 small text-muted">Mengetahui,</p>
                <p class="fw-bold mb-0 text-dark">Orang Tua / Wali Siswa</p>
                <div style="height:60px;"></div>
                <div style="border-top: 1px dashed #333; width:75%; margin:auto;"></div>
                <small class="text-muted d-block mt-1">( ................................................ )</small>
            </div>
            <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                <p class="mb-0 small text-muted">Mengetahui,</p>
                <p class="fw-bold mb-0 text-dark">Kepala Sekolah</p>
                <div style="height:60px;"></div>
                <div style="border-top: 1px dashed #333; width:75%; margin:auto;"></div>
                <small class="fw-bold text-dark d-block mt-1"><?= htmlspecialchars($kepsekNama) ?></small>
                <small class="text-muted d-block" style="font-size:0.75rem;">NIP/NUPTK: <?= htmlspecialchars($kepsekNip ?: '-') ?></small>
            </div>
            <div class="col-12 col-sm-4">
                <?php
                $kotaSekolah = 'Cicalengka';
                if (!empty($settings['alamat'])) {
                    if (stripos($settings['alamat'], 'Bandung') !== false) $kotaSekolah = 'Bandung';
                    elseif (stripos($settings['alamat'], 'Cicalengka') !== false) $kotaSekolah = 'Cicalengka';
                }
                ?>
                <p class="mb-0 small text-muted"><?= htmlspecialchars($kotaSekolah) ?>, <?= date('d F Y') ?></p>
                <p class="fw-bold mb-0 text-dark">Wali Kelas Rombel</p>
                <div style="height:60px;"></div>
                <div style="border-top: 1px dashed #333; width:75%; margin:auto;"></div>
                <?php if (!empty($waliKelas['nama_lengkap'])): ?>
                    <small class="fw-bold text-dark d-block mt-1"><?= htmlspecialchars($waliKelas['nama_lengkap']) ?></small>
                    <small class="text-muted d-block" style="font-size:0.75rem;">NIP/NUPTK: <?= htmlspecialchars($waliKelas['nip'] ?: '-') ?></small>
                <?php else: ?>
                    <small class="text-muted d-block mt-1">( ................................................ )</small>
                    <small class="text-muted d-block" style="font-size:0.72rem;">Wali Kelas Belum Ditentukan</small>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- end card paper -->

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
