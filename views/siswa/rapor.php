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
    padding-top: 28px !important;
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
    .table-responsive {
        overflow: visible !important;
        overflow-x: visible !important;
        display: block !important;
    }
    table.grade-table {
        width: 100% !important;
        table-layout: auto !important;
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
                <div class="bg-primary bg-gradient p-3.5 rounded-4 text-white shadow-sm d-flex align-items-center justify-content-center" style="width: 58px; height: 58px; background: #2563eb;">
                    <i class="bi bi-file-earmark-text-fill fs-2"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-white mb-1" style="letter-spacing: -0.4px;">E-Rapor Digital Siswa</h3>
                    <p class="text-blue-100 small mb-0 fw-medium">Laporan Hasil Belajar Resmi dengan Predikat, KKM, dan Kalkulasi Nilai Akhir Semester.</p>
                </div>
            </div>

            <button onclick="window.print()" class="btn btn-success fw-bold rounded-pill shadow-sm px-4 py-2.5 text-nowrap" style="font-size: 0.88rem;">
                <i class="bi bi-printer-fill me-1.5"></i> Cetak / Simpan PDF E-Rapor
            </button>
        </div>
    </div>

    <!-- Rapor Container Paper -->
    <div class="rapor-card-paper p-3.5 p-sm-4 p-md-5 mb-4">

        <!-- School Header Kop -->
        <div class="text-center border-bottom pb-4 mb-4">
            <div class="d-flex align-items-center justify-content-center gap-3 mb-2 flex-wrap">
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
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Sekolah" style="max-height:68px; width:auto;" class="img-fluid me-1">
                <?php else: ?>
                    <div class="bg-primary text-white rounded-4 p-2.5 px-3 shadow-sm">
                        <i class="bi bi-mortarboard-fill fs-2"></i>
                    </div>
                <?php endif; ?>
                <div class="text-center text-sm-start">
                    <h5 class="fw-bold mb-0 text-primary" style="letter-spacing:0.5px;"><?= htmlspecialchars($settings['nama_sekolah'] ?? 'SMK MUTHIA HARAPAN CICALENGKA') ?></h5>
                    <small class="text-muted d-block"><?= htmlspecialchars($settings['alamat'] ?? 'Jl. Raya Cicalengka, Kab. Bandung, Jawa Barat 40395') ?> <?= !empty($settings['telepon']) ? '| Telp: ' . htmlspecialchars($settings['telepon']) : '' ?></small>
                </div>
            </div>
            <div class="mt-3">
                <span class="fw-bold text-uppercase border border-2 border-primary d-inline-block px-3.5 py-1.5 rounded-pill bg-primary bg-opacity-10 text-primary" style="font-size:0.85rem;">
                    <i class="bi bi-award-fill me-1"></i> Laporan Hasil Belajar Siswa (E-Rapor Digital) Tahun Ajaran 2025/2026
                </span>
            </div>
        </div>

        <!-- Student Info Header -->
        <div class="row g-3 mb-4 p-3 rounded-4 border" style="background: #f8fafc; border-color: #e2e8f0 !important;">
            <div class="col-12 col-md-6">
                <table class="table table-sm table-borderless small mb-0">
                    <tbody>
                        <tr><td class="text-muted" style="width:38%">Nama Siswa</td><td class="fw-bold text-dark">: <?= htmlspecialchars($siswa['nama_lengkap'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">NIS / NISN</td><td class="fw-bold text-dark">: <?= htmlspecialchars($siswa['nis'] ?? '-') ?> / <?= htmlspecialchars($siswa['nisn'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">Rombel Kelas</td><td class="fw-bold text-dark">: <?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="col-12 col-md-6">
                <table class="table table-sm table-borderless small mb-0">
                    <tbody>
                        <tr><td class="text-muted" style="width:38%">Program Keahlian</td><td class="fw-bold text-dark">: <?= htmlspecialchars($siswa['nama_jurusan'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">Semester Target</td><td class="fw-bold text-dark">: Ganjil (1)</td></tr>
                        <tr><td class="text-muted">Status E-Rapor</td><td class="fw-bold text-success">: <i class="bi bi-patch-check-fill me-1"></i> Terverifikasi Resmi</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Grade Transkrip Table -->
        <div class="table-responsive mb-4">
            <table class="table grade-table table-bordered text-center align-middle">
                <thead class="grade-table-header">
                    <tr>
                        <th class="text-start" rowspan="2" style="width:40px;">No</th>
                        <th class="text-start" rowspan="2">Mata Pelajaran</th>
                        <th rowspan="2" style="width:70px;">KKM</th>
                        <th colspan="4">Komponen Penilaian</th>
                        <th rowspan="2" style="width:95px;">Nilai Akhir</th>
                        <th rowspan="2" style="width:90px;">Predikat</th>
                        <th rowspan="2" style="width:110px;">Ketuntasan</th>
                    </tr>
                    <tr>
                        <th style="width:75px;">Tugas</th>
                        <th style="width:75px;">Quiz</th>
                        <th style="width:75px;">UTS</th>
                        <th style="width:75px;">UAS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($nilaiList)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
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
                                <span class="badge <?= $isTuntas ? 'bg-success' : 'bg-danger' ?> rounded-pill px-2.5 py-1">
                                    <?= $isTuntas ? 'TUNTAS' : 'BELUM TUNTAS' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-primary fw-bold">
                            <td colspan="7" class="text-end">Rata-Rata Nilai Akhir Semester</td>
                            <td class="fs-6 text-primary"><?= number_format($totalAkhir / count($nilaiList), 1) ?></td>
                            <?php $avgPred = NilaiModel::getPredikat($totalAkhir / count($nilaiList)); ?>
                            <td><span class="badge <?= $avgPred['class'] ?> rounded-pill px-2.5 py-1"><?= $avgPred['grade'] ?></span></td>
                            <td><span class="badge bg-success rounded-pill px-2.5 py-1">LULUS</span></td>
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
                        <small><?= $p[1] ?> — <?= $p[2] ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Signature Section -->
        <div class="row g-4 mt-3">
            <div class="col-4 text-center">
                <p class="mb-0 small text-muted">Mengetahui,</p>
                <p class="fw-bold mb-0 text-dark">Orang Tua / Wali Siswa</p>
                <div style="height:55px;"></div>
                <div style="border-top: 1px dashed #333; width:80%; margin:auto;"></div>
                <small class="text-muted">(................................................)</small>
            </div>
            <div class="col-4 text-center">
                <p class="mb-0 small text-muted">Mengetahui,</p>
                <p class="fw-bold mb-0 text-dark">Kepala Sekolah</p>
                <div style="height:55px;"></div>
                <div style="border-top: 1px dashed #333; width:80%; margin:auto;"></div>
                <small class="fw-bold text-dark"><?= htmlspecialchars($settings['kepala_sekolah'] ?? 'H. Supriyadi, M.M.') ?></small>
            </div>
            <div class="col-4 text-center">
                <p class="mb-0 small text-muted">Cicalengka, <?= date('d F Y') ?></p>
                <p class="fw-bold mb-0 text-dark">Wali Kelas Rombel</p>
                <div style="height:55px;"></div>
                <div style="border-top: 1px dashed #333; width:80%; margin:auto;"></div>
                <small class="text-muted">(................................................)</small>
            </div>
        </div>

    </div><!-- end card paper -->

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
