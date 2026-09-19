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
    }
    ::-webkit-scrollbar {
        display: none !important;
    }
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .badge {
        border: 1px solid #94a3b8 !important;
        color: #0f172a !important;
        background: #f8fafc !important;
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
            $kurikulumText = $raporData['kurikulum_nama_snapshot'] ?? 'Kurikulum Merdeka SMK';
            $faseText = $raporData['fase_nama_snapshot'] ?? 'Fase E (Kelas X)';
            $tahunAjaranText = $raporData['tahun_ajaran'] ?? ($activeTa['tahun_ajaran'] ?? '2026/2027');
            $semesterText = $raporData['semester'] ?? ($activeTa['semester'] ?? 'Ganjil');
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
                    <small class="text-muted">Kalkulasi Nilai Akhir mengacu pada 4 instrumen penilaian resmi Kurikulum Merdeka SMK:</small>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-white text-dark border shadow-xs px-2.5 py-1.5" style="font-size:0.75rem;">
                    <i class="bi bi-file-earmark-text text-primary me-1"></i> Tugas Mandiri / Terstruktur <strong>(20%)</strong>
                </span>
                <span class="badge bg-white text-dark border shadow-xs px-2.5 py-1.5" style="font-size:0.75rem;">
                    <i class="bi bi-ui-checks text-warning me-1"></i> Kuis / Formatif Harian <strong>(20%)</strong>
                </span>
                <span class="badge bg-white text-dark border shadow-xs px-2.5 py-1.5" style="font-size:0.75rem;">
                    <i class="bi bi-calendar2-check text-info me-1"></i> Sumatif Tengah Semester (STS) <strong>(30%)</strong>
                </span>
                <span class="badge bg-white text-dark border shadow-xs px-2.5 py-1.5" style="font-size:0.75rem;">
                    <i class="bi bi-award-fill text-success me-1"></i> Sumatif Akhir Semester (SAS) <strong>(30%)</strong>
                </span>
            </div>
        </div>

        <!-- MOBILE GRADE CARD VIEW (Screen Only - Displays on Mobile < 768px) -->
        <?php if (!empty($nilaiList)): ?>
            <div class="d-block d-md-none mb-4 no-print">
                <h6 class="fw-bold text-dark mb-2.5"><i class="bi bi-grid-fill text-primary me-1.5"></i>Ringkasan Nilai Mata Pelajaran:</h6>
                <div class="row g-2.5">
                    <?php 
                    $mobTotal = 0;
                    foreach ($nilaiList as $mn):
                        $kkmVal = $mn['kkm'] ?? 75;
                        $pred = NilaiModel::getPredikat((float)$mn['nilai_akhir']);
                        $mobTotal += $mn['nilai_akhir'];
                        $isTuntas = ((float)$mn['nilai_akhir'] >= $kkmVal);
                    ?>
                        <div class="col-12">
                            <div class="p-3 bg-white rounded-3 border shadow-xs">
                                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 fs-6"><?= htmlspecialchars($mn['nama_mapel']) ?></h6>
                                        <span class="badge bg-secondary rounded-pill" style="font-size:0.68rem;">KKM: <?= $kkmVal ?></span>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <span class="fw-bold fs-5 text-primary d-block"><?= number_format($mn['nilai_akhir'], 1) ?></span>
                                        <span class="badge <?= $pred['class'] ?> rounded-pill px-2 py-0.5" style="font-size:0.68rem;"><?= $pred['grade'] ?></span>
                                        <span class="badge <?= $isTuntas ? 'bg-success' : 'bg-danger' ?> rounded-pill px-2 py-0.5" style="font-size:0.68rem;"><?= $isTuntas ? 'TUNTAS' : 'BELUM' ?></span>
                                    </div>
                                </div>
                                <div class="row g-2 text-center bg-light rounded-3 p-2 border" style="font-size:0.75rem;">
                                    <div class="col-6 col-sm-3">
                                        <span class="text-muted d-block small" style="font-size:0.68rem; line-height: 1.2;">Tugas Mandiri / Terstruktur</span>
                                        <strong class="fs-6 text-dark d-block mt-0.5"><?= number_format($mn['nilai_tugas'], 0) ?></strong>
                                        <span class="text-primary small fw-semibold" style="font-size:0.65rem;">(20%)</span>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <span class="text-muted d-block small" style="font-size:0.68rem; line-height: 1.2;">Kuis / Formatif Harian</span>
                                        <strong class="fs-6 text-dark d-block mt-0.5"><?= number_format($mn['nilai_quiz'], 0) ?></strong>
                                        <span class="text-warning-emphasis small fw-semibold" style="font-size:0.65rem;">(20%)</span>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <span class="text-muted d-block small" style="font-size:0.68rem; line-height: 1.2;">Sumatif Tengah Sem. (STS)</span>
                                        <strong class="fs-6 text-dark d-block mt-0.5"><?= number_format($mn['nilai_uts'], 0) ?></strong>
                                        <span class="text-info-emphasis small fw-semibold" style="font-size:0.65rem;">(30%)</span>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <span class="text-muted d-block small" style="font-size:0.68rem; line-height: 1.2;">Sumatif Akhir Sem. (SAS)</span>
                                        <strong class="fs-6 text-dark d-block mt-0.5"><?= number_format($mn['nilai_uas'], 0) ?></strong>
                                        <span class="text-success small fw-semibold" style="font-size:0.65rem;">(30%)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
                    <tr>
                        <th class="text-start" rowspan="2" style="width:40px;">No</th>
                        <th class="text-start" rowspan="2" style="min-width:180px;">Mata Pelajaran</th>
                        <th rowspan="2" style="width:60px;">KKM</th>
                        <th colspan="4" class="text-center py-2" style="background-color: #f1f5f9;">Komponen Penilaian</th>
                        <th rowspan="2" style="width:85px;">Nilai Akhir</th>
                        <th rowspan="2" style="width:75px;">Predikat</th>
                        <th rowspan="2" style="width:95px;">Ketuntasan</th>
                        <th rowspan="2" class="text-start" style="min-width:240px;">Deskripsi Capaian Kompetensi</th>
                    </tr>
                    <tr style="background-color: #f8fafc;">
                        <th style="min-width:130px; vertical-align:middle;" class="px-2 py-2">
                            <div class="fw-bold text-dark" style="font-size:0.8rem; line-height:1.25;">Tugas Mandiri / Terstruktur</div>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0.5 mt-1" style="font-size:0.68rem; font-weight:600;">(20%)</span>
                        </th>
                        <th style="min-width:130px; vertical-align:middle;" class="px-2 py-2">
                            <div class="fw-bold text-dark" style="font-size:0.8rem; line-height:1.25;">Kuis / Formatif Harian</div>
                            <span class="badge bg-warning bg-opacity-10 text-dark rounded-pill px-2 py-0.5 mt-1" style="font-size:0.68rem; font-weight:600; background-color: rgba(245, 158, 11, 0.15) !important;">(20%)</span>
                        </th>
                        <th style="min-width:140px; vertical-align:middle;" class="px-2 py-2">
                            <div class="fw-bold text-dark" style="font-size:0.8rem; line-height:1.25;">Sumatif Tengah Semester (STS)</div>
                            <span class="badge bg-info bg-opacity-10 text-dark rounded-pill px-2 py-0.5 mt-1" style="font-size:0.68rem; font-weight:600; background-color: rgba(14, 165, 233, 0.15) !important;">(30%)</span>
                        </th>
                        <th style="min-width:140px; vertical-align:middle;" class="px-2 py-2">
                            <div class="fw-bold text-dark" style="font-size:0.8rem; line-height:1.25;">Sumatif Akhir Semester (SAS)</div>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5 mt-1" style="font-size:0.68rem; font-weight:600; background-color: rgba(16, 185, 129, 0.15) !important;">(30%)</span>
                        </th>
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
                        foreach ($nilaiList as $i => $n):
                            $kkmVal = $n['kkm'] ?? 75;
                            $pred = NilaiModel::getPredikat((float)$n['nilai_akhir']);
                            $totalAkhir += $n['nilai_akhir'];
                            $isTuntas = ((float)$n['nilai_akhir'] >= $kkmVal);
                            $deskripsiCapaian = $capaianMap[$n['mapel_id']] ?? (
                                $isTuntas 
                                ? "Menunjukkan penguasaan yang sangat baik dalam menuntaskan seluruh tujuan pembelajaran {$n['nama_mapel']}."
                                : "Perlu bimbingan dan tindak lanjut remedial pada beberapa kompetensi dasar mata pelajaran {$n['nama_mapel']}."
                            );
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="text-start fw-bold text-dark"><?= htmlspecialchars($n['nama_mapel']) ?></td>
                            <td><span class="badge bg-secondary rounded-pill"><?= $kkmVal ?></span></td>
                            <td><?= number_format($n['nilai_tugas'], 0) ?></td>
                            <td><?= number_format($n['nilai_quiz'], 0) ?></td>
                            <td><?= number_format($n['nilai_uts'], 0) ?></td>
                            <td><?= number_format($n['nilai_uas'], 0) ?></td>
                            <td class="fw-bold fs-6 text-primary"><?= number_format($n['nilai_akhir'], 1) ?></td>
                            <td>
                                <span class="badge <?= $pred['class'] ?> rounded-pill px-2.5 py-1">
                                    <?= $pred['grade'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $isTuntas ? 'bg-success' : 'bg-danger' ?> rounded-pill px-2 py-1" style="font-size:0.75rem;">
                                    <?= $isTuntas ? 'TUNTAS' : 'BELUM' ?>
                                </span>
                            </td>
                            <td class="text-start small text-muted lh-sm">
                                <?= htmlspecialchars($deskripsiCapaian) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-primary fw-bold">
                            <td colspan="7" class="text-end">Rata-Rata Nilai Akhir Semester</td>
                            <td class="fs-6 text-primary"><?= number_format($totalAkhir / count($nilaiList), 1) ?></td>
                            <?php $avgPred = NilaiModel::getPredikat($totalAkhir / count($nilaiList)); ?>
                            <td><span class="badge <?= $avgPred['class'] ?> rounded-pill px-2.5 py-1"><?= $avgPred['grade'] ?></span></td>
                            <td><span class="badge bg-success rounded-pill px-2.5 py-1">LULUS</span></td>
                            <td class="text-start small text-primary fw-semibold">Status Akademik: Memenuhi Kriteria Ketercapaian Tujuan Pembelajaran (KKTP).</td>
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
        <div class="row g-4 mt-3 text-center">
            <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                <p class="mb-0 small text-muted">Mengetahui,</p>
                <p class="fw-bold mb-0 text-dark">Orang Tua / Wali Siswa</p>
                <div style="height:50px;"></div>
                <div style="border-top: 1px dashed #333; width:80%; margin:auto;"></div>
                <small class="text-muted">(................................................)</small>
            </div>
            <div class="col-12 col-sm-4 mb-3 mb-sm-0">
                <p class="mb-0 small text-muted">Mengetahui,</p>
                <p class="fw-bold mb-0 text-dark">Kepala Sekolah</p>
                <div style="height:50px;"></div>
                <div style="border-top: 1px dashed #333; width:80%; margin:auto;"></div>
                <small class="fw-bold text-dark"><?= htmlspecialchars($settings['kepala_sekolah'] ?? 'H. Supriyadi, M.M.') ?></small>
            </div>
            <div class="col-12 col-sm-4">
                <p class="mb-0 small text-muted">Cicalengka, <?= date('d F Y') ?></p>
                <p class="fw-bold mb-0 text-dark">Wali Kelas Rombel</p>
                <div style="height:50px;"></div>
                <div style="border-top: 1px dashed #333; width:80%; margin:auto;"></div>
                <small class="text-muted">(................................................)</small>
            </div>
        </div>

    </div><!-- end card paper -->

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
