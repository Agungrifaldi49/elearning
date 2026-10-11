<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* =========================================================
   Portal Pembayaran & Administrasi Siswa - Modern Responsive
   ========================================================= */

/* Hero Banner */
.pembayaran-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0369a1 100%);
    border-radius: 1.25rem;
    color: #ffffff;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.28);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.08);
}
.pembayaran-hero::before {
    content: "";
    position: absolute;
    top: -40%;
    right: -10%;
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(14, 165, 233, 0.22) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.pembayaran-hero::after {
    content: "";
    position: absolute;
    bottom: -30%;
    left: 5%;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.12) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

/* Financial KPI Cards */
.payment-kpi-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1.15rem;
    padding: 1.4rem 1.35rem;
    box-shadow: 0 4px 10px -2px rgba(15, 23, 42, 0.04);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.payment-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}

.kpi-icon-circle {
    width: 44px;
    height: 44px;
    border-radius: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}

/* Bill Item Card */
.bill-card-item {
    border: 1px solid #e2e8f0;
    border-radius: 1.1rem;
    background: #ffffff;
    padding: 1.35rem 1.35rem;
    box-shadow: 0 2px 6px -1px rgba(15, 23, 42, 0.04);
    transition: all 0.22s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.bill-card-item:hover {
    border-color: #94a3b8;
    box-shadow: 0 8px 20px -3px rgba(15, 23, 42, 0.08);
    transform: translateY(-2px);
}

.bill-amount-box {
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 0.9rem;
    padding: 0.9rem 1.1rem;
}

/* Tab Navigation Horizontal Scroll on Mobile */
.student-pills-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
@media (max-width: 767.98px) {
    .student-pills-nav {
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 6px;
    }
    .student-pills-nav .nav-link {
        white-space: nowrap;
        flex-shrink: 0;
    }
}

/* Tabular Numeric Font */
.currency-num {
    font-variant-numeric: tabular-nums;
    font-feature-settings: "tnum";
    letter-spacing: -0.2px;
}

/* Modern Data Table */
.student-pay-table th {
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.72rem;
    letter-spacing: 0.5px;
    color: #475569;
    background-color: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    padding: 13px 16px;
    white-space: nowrap;
}
.student-pay-table td {
    padding: 13px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}
.student-pay-table tr:hover td {
    background-color: #f8fafc;
}
</style>

<?php
$totalTagihanNilai = (float)($summary['total_tagihan'] ?? ($summary['total_nominal_tagihan'] ?? 0));
$totalTerbayarNilai = (float)($summary['total_terbayar'] ?? 0);
$totalTunggakanNilai = (float)($summary['total_tunggakan'] ?? 0);
?>

<main class="main-content px-2 px-sm-3 px-md-4 py-3">
<div class="container-fluid">

    <!-- Flash Notification -->
    <?php if (FlashHelper::hasSuccess()): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0 border-start border-4 border-success bg-white p-3" role="alert">
            <div class="d-flex align-items-center gap-2.5">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <span class="text-dark small"><?= FlashHelper::getSuccess() ?></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (FlashHelper::hasError()): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0 border-start border-4 border-danger bg-white p-3" role="alert">
            <div class="d-flex align-items-center gap-2.5">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                <span class="text-dark small"><?= FlashHelper::getError() ?></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- 1. Hero Summary Card Banner -->
    <div class="pembayaran-hero p-3.5 p-sm-4 p-md-5 mb-4 shadow-sm position-relative">
        <div class="row align-items-center g-3 g-md-4 position-relative" style="z-index: 2;">
            <div class="col-12 col-lg-8">
                <!-- Badges Status -->
                <div class="d-flex align-items-center gap-2 mb-2.5 flex-wrap">
                    <span class="badge bg-white text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase shadow-xs" style="font-size: 0.74rem; letter-spacing: 0.4px;">
                        <i class="bi bi-wallet2 text-primary me-1.5"></i> Portal Pembayaran & Administrasi SPP
                    </span>
                    <span class="badge <?= $summary['badge_class'] ?> px-3 py-1.5 rounded-pill fw-bold shadow-xs" style="font-size: 0.74rem;">
                        <i class="bi <?= $summary['is_bebas_keuangan'] ? 'bi-shield-check' : 'bi-exclamation-circle' ?> me-1"></i>
                        Status: <?= htmlspecialchars($summary['status_label']) ?>
                    </span>
                    <span class="badge bg-success text-white px-3 py-1.5 rounded-pill fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                        <i class="bi bi-broadcast"></i> Terhubung Realtime ke API Tata Usaha
                    </span>
                </div>

                <h3 class="fw-bold mb-2 text-white fs-4 fs-md-3"><?= htmlspecialchars($siswa['nama_lengkap']) ?></h3>
                
                <!-- Identitas Siswa Chips -->
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 bg-white bg-opacity-10 text-white small flex-wrap" style="backdrop-filter: blur(4px);">
                    <span>NIS: <strong><?= htmlspecialchars($siswa['nis'] ?? '-') ?></strong></span>
                    <span class="opacity-50">&bull;</span>
                    <span>NISN: <strong><?= htmlspecialchars($siswa['nisn'] ?? '-') ?></strong></span>
                    <span class="opacity-50">&bull;</span>
                    <span>Kelas: <strong><?= htmlspecialchars($siswa['nama_kelas'] ?? 'Rombel') ?></strong></span>
                    <span class="opacity-50">&bull;</span>
                    <span>Jurusan: <strong><?= htmlspecialchars($siswa['nama_jurusan'] ?? 'Kejuruan') ?></strong></span>
                </div>
            </div>

            <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                <button type="button" class="btn btn-warning text-dark fw-bold px-3.5 py-2.5 rounded-3 shadow-sm w-100 w-lg-auto d-inline-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#modalInfoRekening">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <span>Prosedur & Rekening Sekolah</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Financial Metrics KPI Cards (3 Kolom Responsif) -->
    <div class="row g-3 mb-4">
        <!-- KPI 1: Total Tagihan Tahun Ini -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="payment-kpi-card border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Tagihan Tahun Ini</span>
                    <div class="kpi-icon-circle bg-primary-subtle text-primary">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 text-dark currency-num">Rp <?= number_format($totalTagihanNilai, 0, ',', '.') ?></h3>
                <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-2">
                    <small class="text-muted" style="font-size: 0.74rem;">
                        <i class="bi bi-calendar-check me-1 text-primary"></i>T.A. 2025/2026
                    </small>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 rounded-pill small fw-semibold" style="font-size: 0.72rem;">
                        <?= count($bills) ?> Total Item
                    </span>
                </div>
            </div>
        </div>

        <!-- KPI 2: Total Sudah Terbayar -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="payment-kpi-card border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Sudah Terbayar</span>
                    <div class="kpi-icon-circle bg-success-subtle text-success">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 text-success currency-num">Rp <?= number_format($totalTerbayarNilai, 0, ',', '.') ?></h3>
                <div class="d-flex align-items-center gap-2 pt-2 border-top mt-2">
                    <div class="progress flex-grow-1" style="height: 7px; border-radius: 10px;">
                        <div class="progress-bar bg-success" style="width: <?= min(100, (float)$summary['persen_lunas']) ?>%;"></div>
                    </div>
                    <span class="fw-bold text-success small" style="font-size:0.75rem;"><?= $summary['persen_lunas'] ?>%</span>
                </div>
            </div>
        </div>

        <!-- KPI 3: Sisa Tunggakan Aktif -->
        <div class="col-12 col-sm-12 col-lg-4">
            <div class="payment-kpi-card border-start border-4 <?= $totalTunggakanNilai > 0 ? 'border-danger' : 'border-success' ?>">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Sisa Tagihan / Tunggakan</span>
                    <div class="kpi-icon-circle <?= $totalTunggakanNilai > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?>">
                        <i class="bi <?= $totalTunggakanNilai > 0 ? 'bi-clock-history' : 'bi-shield-check' ?>"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 <?= $totalTunggakanNilai > 0 ? 'text-danger' : 'text-success' ?> currency-num">
                    Rp <?= number_format($totalTunggakanNilai, 0, ',', '.') ?>
                </h3>
                <div class="pt-2 border-top mt-2">
                    <small class="text-muted" style="font-size: 0.74rem;">
                        <?= $summary['count_belum_lunas'] > 0 
                            ? "<span class='text-danger fw-semibold'><i class='bi bi-exclamation-circle me-1'></i>{$summary['count_belum_lunas']} item belum diselesaikan</span>" 
                            : "<span class='text-success fw-semibold'><i class='bi bi-check-all me-1'></i>Seluruh tagihan terselesaikan (Lunas)</span>" ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Navigation Tabs: Tagihan Aktif vs Riwayat Pembayaran -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-header bg-white border-bottom p-3 p-md-4">
            <ul class="nav nav-pills student-pills-nav card-header-pills" id="pembayaranTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill fw-bold px-3.5 py-2" id="tagihan-tab" data-bs-toggle="tab" data-bs-target="#tagihan-pane" type="button" role="tab" style="font-size: 0.85rem;">
                        <i class="bi bi-hourglass-split me-1.5 text-warning"></i> Tagihan Belum Lunas (<?= count($unpaidBills) ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold px-3.5 py-2" id="riwayat-tab" data-bs-toggle="tab" data-bs-target="#riwayat-pane" type="button" role="tab" style="font-size: 0.85rem;">
                        <i class="bi bi-clock-history me-1.5 text-primary"></i> Riwayat Pembayaran (<?= count($riwayat) ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold px-3.5 py-2" id="semua-tab" data-bs-toggle="tab" data-bs-target="#semua-pane" type="button" role="tab" style="font-size: 0.85rem;">
                        <i class="bi bi-card-list me-1.5 text-secondary"></i> Semua Daftar Tagihan (<?= count($bills) ?>)
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-3 p-sm-4">
            <div class="tab-content" id="pembayaranTabContent">

                <!-- TAB 1: TAGIHAN AKTIF / BELUM LUNAS -->
                <div class="tab-pane fade show active" id="tagihan-pane" role="tabpanel">
                    <?php if (empty($bills)): ?>
                        <div class="text-center py-5">
                            <div class="bg-primary-subtle text-primary p-3 rounded-circle d-inline-flex mb-3">
                                <i class="bi bi-wallet2 fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Belum Ada Data Tagihan Terdaftar</h5>
                            <p class="text-muted small mb-3" style="max-width: 550px; margin: 0 auto; line-height: 1.6;">
                                Data administrasi tagihan SPP dan iuran pendidikan Anda saat ini belum dimuat dari sistem keuangan sekolah. Silakan hubungi bagian Tata Usaha / Keuangan untuk informasi lebih lanjut.
                            </p>
                            <button type="button" class="btn btn-outline-primary rounded-pill px-4 btn-sm mt-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalInfoRekening">
                                <i class="bi bi-info-circle me-1"></i> Prosedur & Rekening Sekolah
                            </button>
                        </div>
                    <?php elseif (empty($unpaidBills)): ?>
                        <div class="text-center py-5">
                            <div class="bg-success-subtle text-success p-3 rounded-circle d-inline-flex mb-3">
                                <i class="bi bi-patch-check-fill fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Alhamdulillah, Tidak Ada Tunggakan!</h5>
                            <p class="text-muted small mb-3">Seluruh administrasi pembayaran SPP dan biaya pendidikan Anda saat ini telah lunas tercatat di kasir sekolah.</p>
                            <button type="button" class="btn btn-outline-primary rounded-pill px-4 fw-semibold" onclick="document.getElementById('riwayat-tab').click()">
                                <i class="bi bi-clock-history me-1"></i> Lihat Riwayat Pembayaran
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($unpaidBills as $bill): 
                                $isDue = !empty($bill['tanggal_jatuh_tempo']) && strtotime($bill['tanggal_jatuh_tempo']) < time();
                            ?>
                                <div class="col-12 col-md-6 col-xl-6">
                                    <div class="bill-card-item border-start border-4 <?= $isDue ? 'border-danger' : 'border-warning' ?>">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2.5">
                                                <div>
                                                    <span class="badge bg-light text-dark border px-2 py-0.5 rounded-pill small fw-semibold font-monospace" style="font-size: 0.7rem;">
                                                        <?= htmlspecialchars($bill['jenis_pembayaran']) ?> &bull; <?= htmlspecialchars($bill['kode_tagihan']) ?>
                                                    </span>
                                                    <h6 class="fw-bold text-dark mb-0 mt-1.5" style="font-size:0.95rem;"><?= htmlspecialchars($bill['judul']) ?></h6>
                                                    <small class="text-muted" style="font-size: 0.74rem;"><?= htmlspecialchars($bill['keterangan'] ?? 'Iuran Sekolah') ?></small>
                                                </div>
                                                <span class="badge <?= $isDue ? 'bg-danger text-white' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' ?> rounded-pill px-2.5 py-1 fw-bold flex-shrink-0" style="font-size: 0.72rem;">
                                                    <?= $isDue ? '<i class="bi bi-exclamation-diamond me-1"></i>Lewat Tempo' : '<i class="bi bi-clock me-1"></i>Belum Lunas' ?>
                                                </span>
                                            </div>

                                            <!-- Rincian Nominal Box -->
                                            <div class="bill-amount-box d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <small class="text-muted d-block" style="font-size:0.7rem;">Nominal Tagihan</small>
                                                    <span class="fw-bold text-dark currency-num fs-6">Rp <?= number_format($bill['nominal'], 0, ',', '.') ?></span>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted d-block" style="font-size:0.7rem;">Sisa yang Harus Dibayar</small>
                                                    <span class="fw-bold text-danger currency-num fs-6">Rp <?= number_format($bill['sisa_tagihan'], 0, ',', '.') ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center pt-2.5 border-top flex-wrap gap-2">
                                            <small class="text-muted" style="font-size: 0.74rem;">
                                                <i class="bi bi-calendar-event me-1"></i>Batas Tempo: 
                                                <strong class="<?= $isDue ? 'text-danger' : 'text-dark' ?>">
                                                    <?= !empty($bill['tanggal_jatuh_tempo']) ? date('d M Y', strtotime($bill['tanggal_jatuh_tempo'])) : '-' ?>
                                                </strong>
                                            </small>
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1" style="font-size:0.78rem;" data-bs-toggle="modal" data-bs-target="#modalInfoRekening">
                                                <i class="bi bi-cash-coin"></i> Cara Bayar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB 2: RIWAYAT PEMBAYARAN LUNAS -->
                <div class="tab-pane fade" id="riwayat-pane" role="tabpanel">
                    <?php if (empty($riwayat)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-receipt-cutoff fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <h6 class="fw-bold text-dark mb-1">Belum Ada Riwayat Pembayaran</h6>
                            <p class="small mb-0">Riwayat transaksi pembayaran yang telah Anda lunasi di loket kasir Tata Usaha akan tercatat otomatis di sini.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table student-pay-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Tanggal & Jam</th>
                                        <th style="width: 24%;">No. Transaksi Kasir</th>
                                        <th style="width: 26%;">Nama Tagihan</th>
                                        <th style="width: 14%;">Metode</th>
                                        <th style="width: 16%; text-align: right;">Nominal Lunas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($riwayat as $trx): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark small"><?= date('d M Y', strtotime($trx['tanggal_bayar'])) ?></div>
                                                <small class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-clock me-1 text-primary"></i><?= date('H:i', strtotime($trx['tanggal_bayar'])) ?> WIB</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2.5 py-1 rounded font-monospace small">
                                                    <?= htmlspecialchars($trx['nomor_transaksi']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark" style="font-size: 0.88rem;"><?= htmlspecialchars($trx['nama_tagihan']) ?></div>
                                                <small class="text-muted" style="font-size:0.72rem;"><?= htmlspecialchars($trx['jenis_pembayaran'] ?? 'Iuran') ?> &bull; Loket Kasir Tata Usaha</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                                                    <i class="bi bi-credit-card me-1"></i><?= htmlspecialchars(ucfirst($trx['metode_pembayaran'] ?? 'Tunai')) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold text-success currency-num fs-6">Rp <?= number_format($trx['nominal_bayar'], 0, ',', '.') ?></span>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 d-block mt-0.5 ms-auto fw-bold" style="width:fit-content; font-size:0.68rem;">Berhasil Lunas</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB 3: SEMUA DAFTAR TAGIHAN -->
                <div class="tab-pane fade" id="semua-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table student-pay-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 14%;">Kode</th>
                                    <th style="width: 32%;">Uraian Tagihan</th>
                                    <th style="width: 15%; text-align: right;">Nominal</th>
                                    <th style="width: 15%; text-align: right;">Terbayar</th>
                                    <th style="width: 14%; text-align: right;">Sisa</th>
                                    <th style="width: 10%; text-align: center;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bills as $b): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark border px-2 py-1 font-monospace small"><?= htmlspecialchars($b['kode_tagihan']) ?></span></td>
                                        <td>
                                            <div class="fw-bold text-dark" style="font-size: 0.88rem;"><?= htmlspecialchars($b['judul']) ?></div>
                                            <small class="text-muted" style="font-size:0.72rem;"><?= htmlspecialchars($b['jenis_pembayaran']) ?> &bull; Periode <?= htmlspecialchars($b['periode_bulan'] ?? '-') ?></small>
                                        </td>
                                        <td class="text-end fw-semibold text-dark currency-num">Rp <?= number_format($b['nominal'], 0, ',', '.') ?></td>
                                        <td class="text-end fw-semibold text-success currency-num">Rp <?= number_format($b['nominal_terbayar'], 0, ',', '.') ?></td>
                                        <td class="text-end fw-semibold <?= $b['sisa_tagihan'] > 0 ? 'text-danger' : 'text-muted' ?> currency-num">Rp <?= number_format($b['sisa_tagihan'], 0, ',', '.') ?></td>
                                        <td class="text-center">
                                            <?php if ($b['status'] === 'lunas'): ?>
                                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1 small fw-bold shadow-2xs">Lunas</span>
                                            <?php elseif ($b['status'] === 'sebagian'): ?>
                                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 small fw-bold shadow-2xs">Sebagian</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 small fw-bold shadow-2xs">Belum Lunas</span>
                                            <?php endif; ?>
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

<!-- Modal: Informasi Rekening & Prosedur Pembayaran -->
<div class="modal fade" id="modalInfoRekening" tabindex="-1" aria-labelledby="modalInfoRekeningLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom p-3.5 bg-light rounded-top-4">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalInfoRekeningLabel">
                    <i class="bi bi-bank2 text-primary fs-5"></i>
                    <span>Informasi Rekening & Prosedur Pembayaran</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">
                    Pembayaran SPP dan administrasi sekolah dapat dilakukan melalui transfer bank ke rekening resmi <strong><?= htmlspecialchars($rekeningConfig['nama_sekolah'] ?? 'SMK Muthia Harapan Cicalengka') ?></strong> berikut:
                </p>

                <!-- Bank Accounts List -->
                <div class="d-flex flex-column gap-2.5 mb-3">
                    <?php if (!empty($rekeningConfig['bank_accounts'])): ?>
                        <?php foreach ($rekeningConfig['bank_accounts'] as $acc): 
                            $badgeColor = htmlspecialchars($acc['warna_badge'] ?? 'primary');
                            $cleanNum = preg_replace('/[^0-9]/', '', $acc['nomor_rekening']);
                        ?>
                            <div class="p-3 rounded-3 border bg-white shadow-2xs d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="badge bg-<?= $badgeColor ?> px-2 py-0.5 rounded text-white fw-bold mb-1" style="font-size:0.68rem;">
                                        <?= htmlspecialchars(strtoupper($acc['bank'])) ?>
                                    </span>
                                    <div class="fw-bold font-monospace fs-6 text-dark"><?= htmlspecialchars($acc['nomor_rekening']) ?></div>
                                    <small class="text-muted" style="font-size:0.75rem;">a.n <?= htmlspecialchars($acc['atas_nama']) ?></small>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" onclick="navigator.clipboard.writeText('<?= $cleanNum ?>'); alert('Nomor Rekening <?= addslashes(htmlspecialchars($acc['bank'])) ?> (<?= $cleanNum ?>) berhasil disalin!');">
                                    <i class="bi bi-copy me-1"></i> Salin
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-light border small text-muted text-center py-3">
                            Informasi rekening belum dikonfigurasi oleh Admin. Silakan hubungi bagian Keuangan / TU.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="p-3 bg-warning-subtle rounded-3 border border-warning-subtle text-dark small">
                    <div class="fw-bold mb-1 d-flex align-items-center gap-1.5 text-warning-emphasis">
                        <i class="bi bi-exclamation-circle-fill"></i> Prosedur Konfirmasi Pembayaran:
                    </div>
                    <?php if (!empty($rekeningConfig['prosedur']) && is_array($rekeningConfig['prosedur'])): ?>
                        <ol class="mb-2 ps-3">
                            <?php foreach ($rekeningConfig['prosedur'] as $step): ?>
                                <li><?= htmlspecialchars($step) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php else: ?>
                        <p class="mb-2"><?= nl2br(htmlspecialchars($rekeningConfig['prosedur'] ?? 'Silakan konfirmasi bukti transfer ke loket TU / Keuangan sekolah.')) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($rekeningConfig['kontak_konfirmasi'])): ?>
                        <div class="pt-2 border-top border-warning-subtle d-flex align-items-center gap-1.5 fw-semibold text-dark" style="font-size: 0.78rem;">
                            <i class="bi bi-whatsapp text-success fs-6"></i>
                            <span>Kontak Keuangan: <?= htmlspecialchars($rekeningConfig['kontak_konfirmasi']) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($rekeningConfig['catatan_tambahan'])): ?>
                        <div class="mt-1 text-muted" style="font-size: 0.72rem;">
                            * <?= htmlspecialchars($rekeningConfig['catatan_tambahan']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer border-top p-3 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
