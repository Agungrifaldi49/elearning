<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
.kepsek-finance-hero {
    background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #0f172a 100%);
    border-radius: 1.25rem;
    color: #ffffff;
    box-shadow: 0 10px 25px -5px rgba(6, 78, 59, 0.3);
    position: relative;
    overflow: hidden;
}
.kepsek-finance-hero::after {
    content: "";
    position: absolute;
    top: -40%;
    right: -10%;
    width: 280px;
    height: 280px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.exec-kpi-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
    transition: all 0.25s ease;
    height: 100%;
}
.exec-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.08);
}
</style>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <!-- Executive Welcome Card -->
    <div class="kepsek-finance-hero p-4 p-md-5 mb-4 shadow-sm">
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <span class="badge bg-white text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase shadow-xs" style="font-size: 0.75rem;">
                        <i class="bi bi-shield-lock-fill text-success me-1.5"></i> Dashboard Pengawasan Eksekutif
                    </span>
                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold shadow-xs" style="font-size: 0.75rem;">
                        <i class="bi bi-calendar-check me-1"></i> T.A. 2025/2026
                    </span>
                </div>
                <h3 class="fw-bold mb-2 text-white">Monitoring Keuangan & Administrasi SPP</h3>
                <p class="text-white text-opacity-90 mb-0 small" style="max-width: 650px;">
                    Ikhtisar eksekutif realisasi penerimaan iuran pendidikan, efektivitas pelunasan SPP/DSP, dan pengawasan piutang sekolah secara realtime.
                </p>
            </div>
            
            <div class="col-12 col-lg-4 text-lg-end">
                <a href="<?= BASE_URL ?>index.php?url=kepsek/cetakLaporanPembayaran" target="_blank" class="btn btn-warning text-dark fw-bold px-4 py-2.5 rounded-3 shadow-sm text-nowrap w-100 w-sm-auto">
                    <i class="bi bi-printer-fill me-1.5"></i> Cetak Laporan Keuangan Eksekutif
                </a>
            </div>
        </div>
    </div>

    <!-- 4 High-Level Executive Financial KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="exec-kpi-card border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Target Penerimaan Sekolah</span>
                    <div class="bg-primary-subtle text-primary p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-dark">Rp <?= number_format($global['total_target'], 0, ',', '.') ?></h4>
                <small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-people me-1 text-primary"></i>Total <?= $global['total_siswa'] ?> Siswa Terdaftar</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="exec-kpi-card border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Realisasi Kas Masuk</span>
                    <div class="bg-success-subtle text-success p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-success">Rp <?= number_format($global['total_masuk'], 0, ',', '.') ?></h4>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <div class="progress flex-grow-1" style="height: 6px; border-radius: 10px;">
                        <div class="progress-bar bg-success" style="width: <?= min(100, $global['rate_pelunasan']) ?>%;"></div>
                    </div>
                    <span class="fw-bold text-success small" style="font-size: 0.75rem;"><?= $global['rate_pelunasan'] ?>%</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="exec-kpi-card border-start border-4 border-danger">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Total Sisa Piutang</span>
                    <div class="bg-danger-subtle text-danger p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-danger">Rp <?= number_format($global['total_piutang'], 0, ',', '.') ?></h4>
                <small class="text-danger fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-exclamation-triangle me-1"></i>Tunggakan Belum Terbayar</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="exec-kpi-card border-start border-4 border-info">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Rasio Kepatuhan Siswa</span>
                    <div class="bg-info-subtle text-info p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-info"><?= $global['siswa_lunas'] ?> / <?= $global['total_siswa'] ?> Siswa</h4>
                <div class="d-flex align-items-center gap-1.5 mt-1" style="font-size:0.75rem;">
                    <span class="badge bg-success-subtle text-success px-2 py-0.5 rounded-pill fw-bold">Lunas: <?= $global['siswa_lunas'] ?></span>
                    <span class="badge bg-danger-subtle text-danger px-2 py-0.5 rounded-pill fw-bold">Menunggak: <?= $global['siswa_menunggak'] ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Strategic Breakdown Panels -->
    <div class="row g-4 mb-4">
        <!-- 1. Realisasi Berdasarkan Pos / Kategori Pembayaran -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-tags-fill text-primary"></i>
                            <span>Realisasi Per Pos Biaya</span>
                        </h6>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 small fw-bold">
                            <?= count($breakdownKategori) ?> Kategori
                        </span>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($breakdownKategori as $kat): ?>
                            <div class="p-3 rounded-3 border bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($kat['jenis_pembayaran']) ?></span>
                                    <span class="badge bg-success text-white rounded-pill px-2.5 py-0.5 small fw-bold">
                                        <?= $kat['persentase'] ?>% Tercapai
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-1.5" style="font-size: 0.75rem;">
                                    <span>Realisasi: <strong class="text-success">Rp <?= number_format($kat['realisasi_nominal'], 0, ',', '.') ?></strong></span>
                                    <span>Target: <strong>Rp <?= number_format($kat['target_nominal'], 0, ',', '.') ?></strong></span>
                                </div>
                                <div class="progress" style="height: 7px; border-radius: 10px;">
                                    <div class="progress-bar bg-success" style="width: <?= min(100, $kat['persentase']) ?>%;"></div>
                                </div>
                                <?php if ($kat['sisa_nominal'] > 0): ?>
                                    <div class="text-end mt-1">
                                        <small class="text-danger fw-semibold" style="font-size: 0.7rem;">Sisa Piutang: Rp <?= number_format($kat['sisa_nominal'], 0, ',', '.') ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Kepatuhan Pembayaran Per-Rombel Kelas -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-mortarboard-fill text-success"></i>
                            <span>Tingkat Kepatuhan Pembayaran Per-Rombel Kelas</span>
                        </h6>
                        <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 small fw-bold">
                            Peringkat Capaian
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr style="font-size: 0.8rem;">
                                    <th>Rombel Kelas</th>
                                    <th>Target Kelas</th>
                                    <th>Realisasi Masuk</th>
                                    <th>Sisa Piutang</th>
                                    <th style="text-align: right;">Capaian (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($breakdownKelas as $bk): 
                                    $pct = $bk['persentase_kelas'] ?? 0;
                                    $badgeClr = ($pct >= 75) ? 'bg-success' : (($pct >= 50) ? 'bg-warning text-dark' : 'bg-danger text-white');
                                ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($bk['nama_kelas']) ?></div>
                                            <small class="text-muted" style="font-size: 0.72rem;"><?= (int)$bk['total_siswa'] ?> Siswa Terdata</small>
                                        </td>
                                        <td class="fw-semibold text-dark small">Rp <?= number_format($bk['target_kelas'] ?? 0, 0, ',', '.') ?></td>
                                        <td class="fw-semibold text-success small">Rp <?= number_format($bk['realisasi_kelas'] ?? 0, 0, ',', '.') ?></td>
                                        <td class="fw-semibold text-danger small">Rp <?= number_format($bk['sisa_kelas'] ?? 0, 0, ',', '.') ?></td>
                                        <td class="text-end">
                                            <span class="badge <?= $badgeClr ?> rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                                <?= $pct ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
