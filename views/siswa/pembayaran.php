<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* ==========================================================================
   PORTAL PEMBAYARAN SISWA - MODERN, LEGA, RAPI & PROPORSIONAL
   ========================================================================== */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap');

.portal-pembayaran-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

.font-mono {
    font-family: 'JetBrains Mono', monospace !important;
}

.currency-num {
    font-variant-numeric: tabular-nums;
    font-feature-settings: "tnum";
    letter-spacing: -0.3px;
}

/* ==========================================================================
   1. HERO BANNER: LEGA & SEAMLESS
   ========================================================================== */
.hero-payment-card {
    background: linear-gradient(135deg, #091e3a 0%, #102a4e 50%, #0369a1 100%);
    border-radius: 16px;
    color: #ffffff;
    box-shadow: 0 6px 20px rgba(9, 30, 58, 0.12);
    position: relative;
    overflow: hidden;
    padding: 1.85rem 2.2rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 767.98px) {
    .hero-payment-card {
        padding: 1.35rem 1.25rem;
        border-radius: 14px;
        margin-bottom: 1.25rem;
    }
}

.hero-pill-badge {
    background: rgba(255, 255, 255, 0.14);
    backdrop-filter: blur(6px);
    color: #ffffff;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 0.4rem 0.85rem;
    border-radius: 50rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.student-meta-chip {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 0.35rem 0.75rem;
    font-size: 0.78rem;
    color: #f1f5f9;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

/* ==========================================================================
   2. FINANCIAL KPI CARDS: BERJARAK LEGA & HARMONIS
   ========================================================================== */
.kpi-stat-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.4rem 1.5rem;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
    transition: all 0.22s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.kpi-stat-box:hover {
    border-color: #cbd5e1;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    transform: translateY(-2px);
}

.kpi-icon-wrapper {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.kpi-amount-text {
    font-size: clamp(1.4rem, 2.2vw, 1.75rem);
    font-weight: 800;
    line-height: 1.2;
    margin: 0.5rem 0 0.35rem 0;
}

/* ==========================================================================
   3. UNIFIED TABS & TOOLBAR
   ========================================================================== */
.custom-tab-container {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    overflow: hidden;
    margin-bottom: 1.75rem;
}

.modern-tab-header {
    background: #ffffff;
    border-bottom: 1px solid #edf2f7;
    padding: 1rem 1.4rem;
}
@media (max-width: 767.98px) {
    .modern-tab-header {
        padding: 0.85rem 1rem;
    }
}

.modern-nav-pills {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    margin-bottom: 0;
}
.modern-nav-pills::-webkit-scrollbar {
    display: none;
}

.modern-nav-pills .nav-link {
    border-radius: 10px;
    font-size: 0.83rem;
    font-weight: 700;
    padding: 0.55rem 1.15rem;
    color: #475569;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    white-space: nowrap;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}
.modern-nav-pills .nav-link:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.modern-nav-pills .nav-link.active {
    background: #0284c7;
    color: #ffffff !important;
    border-color: #0284c7;
    box-shadow: 0 3px 10px rgba(2, 132, 199, 0.25);
}

/* ==========================================================================
   4. BILL ITEM CARDS (DESKTOP & MOBILE RESPONSIVE)
   ========================================================================== */
.bill-card-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.35rem 1.4rem;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.025);
    transition: all 0.22s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    max-width: 100%;
    word-break: break-word;
}
@media (max-width: 575.98px) {
    .bill-card-item {
        padding: 1rem 0.95rem;
        border-radius: 12px;
    }
}
.bill-card-item:hover {
    border-color: #cbd5e1;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    transform: translateY(-2px);
}

.bill-amount-box {
    background: #f8fafc;
    border: 1px solid #edf2f7;
    border-radius: 10px;
    padding: 0.85rem 1rem;
    margin: 0.95rem 0 1.15rem 0;
}

/* Mobile-Friendly List Row View */
.bill-list-row {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 0.75rem;
    transition: all 0.2s ease;
}
.bill-list-row:hover {
    border-color: #cbd5e1;
    background-color: #fbfcfe;
    box-shadow: 0 3px 10px rgba(0,0,0,0.03);
}

/* ==========================================================================
   5. DATA TABLE
   ========================================================================== */
.payment-data-table {
    margin-bottom: 0;
}
.payment-data-table th {
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.72rem;
    letter-spacing: 0.5px;
    color: #475569;
    background-color: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    padding: 13px 18px;
    white-space: nowrap;
}
.payment-data-table td {
    padding: 14px 18px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.85rem;
}
.payment-data-table tr:hover td {
    background-color: #f8fafc;
}

/* Mobile Transaction Card */
.mobile-trx-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.1rem;
    margin-bottom: 0.75rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
}

/* Bank Account Card */
.bank-account-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem 1.2rem;
    transition: all 0.2s ease;
}
.bank-account-card:hover {
    border-color: #cbd5e1;
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
}
</style>

<?php
$totalTagihanNilai = (float)($summary['total_tagihan'] ?? ($summary['total_nominal_tagihan'] ?? 0));
$totalTerbayarNilai = (float)($summary['total_terbayar'] ?? 0);
$totalTunggakanNilai = (float)($summary['total_tunggakan'] ?? 0);
$persenLunas = min(100, max(0, (float)($summary['persen_lunas'] ?? 0)));
?>

<main class="main-content px-3 px-md-4 portal-pembayaran-wrapper py-3 pb-5">
<div class="container-fluid">

    <!-- FLASH NOTIFICATIONS -->
    <?php if (FlashHelper::hasSuccess()): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-2xs mb-3 border-0 border-start border-4 border-success bg-white p-3" role="alert">
            <div class="d-flex align-items-center gap-2.5">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <span class="text-dark small"><?= FlashHelper::getSuccess() ?></span>
            </div>
            <button type="button" class="btn-close btn-sm p-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (FlashHelper::hasError()): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-2xs mb-3 border-0 border-start border-4 border-danger bg-white p-3" role="alert">
            <div class="d-flex align-items-center gap-2.5">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                <span class="text-dark small"><?= FlashHelper::getError() ?></span>
            </div>
            <button type="button" class="btn-close btn-sm p-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- 1. HERO BANNER: INFORMASI STATUS PEMBAYARAN SISWA (LEGA & RAPI) -->
    <div class="hero-payment-card mb-4">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-8">
                <!-- Status Badges -->
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="hero-pill-badge">
                        <i class="bi bi-wallet2 text-warning"></i> Keuangan SPP
                    </span>
                    <span class="badge <?= $summary['badge_class'] ?> rounded-pill fw-semibold px-3 py-1.5" style="font-size: 0.74rem;">
                        <i class="bi <?= $summary['is_bebas_keuangan'] ? 'bi-shield-check' : 'bi-exclamation-circle' ?> me-1"></i>
                        <?= htmlspecialchars($summary['status_label']) ?>
                    </span>
                    <span class="badge bg-success text-white rounded-pill fw-semibold px-2.5 py-1.5 d-inline-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                        <i class="bi bi-broadcast"></i> Terhubung Live Kasir TU
                    </span>
                </div>

                <!-- Nama Siswa & Identitas -->
                <h3 class="fw-bold mb-2 text-white fs-4 fs-md-3" style="letter-spacing: -0.3px;">
                    <?= htmlspecialchars($siswa['nama_lengkap']) ?>
                </h3>
                
                <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                    <span class="student-meta-chip">NIS: <strong class="font-mono text-white"><?= htmlspecialchars($siswa['nis'] ?? '-') ?></strong></span>
                    <span class="student-meta-chip">NISN: <strong class="font-mono text-white"><?= htmlspecialchars($siswa['nisn'] ?? '-') ?></strong></span>
                    <span class="student-meta-chip">Kelas: <strong class="text-white"><?= htmlspecialchars($siswa['nama_kelas'] ?? 'Rombel') ?></strong></span>
                    <span class="student-meta-chip">Jurusan: <strong class="text-white"><?= htmlspecialchars($siswa['nama_jurusan'] ?? 'Kejuruan') ?></strong></span>
                </div>
            </div>

            <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                <button type="button" class="btn btn-warning text-dark fw-bold px-3.5 py-2.5 rounded-3 shadow-sm w-100 w-md-auto d-inline-flex align-items-center justify-content-center gap-2" style="font-size: 0.88rem;" data-bs-toggle="modal" data-bs-target="#modalInfoRekening">
                    <i class="bi bi-bank2 fs-5"></i>
                    <span>Info Rekening & Cara Bayar</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. FINANCIAL METRICS KPI CARDS (LEGA, SIMETRIS & MENYATU) -->
    <div class="row g-3 g-xl-4 mb-4">
        <!-- KPI 1: Total Tagihan Tahun Ini -->
        <div class="col-12 col-md-4">
            <div class="kpi-stat-box">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            Total Tagihan Tahun Ini
                        </span>
                        <div class="kpi-amount-text text-dark currency-num">
                            Rp <?= number_format($totalTagihanNilai, 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="kpi-icon-wrapper bg-primary-subtle text-primary">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2.5 border-top mt-2" style="font-size: 0.76rem;">
                    <span class="text-muted"><i class="bi bi-calendar3 me-1 text-primary"></i>T.A. 2025/2026</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-0.5 rounded-pill fw-semibold">
                        <?= count($bills) ?> Total Tagihan
                    </span>
                </div>
            </div>
        </div>

        <!-- KPI 2: Total Sudah Terbayar -->
        <div class="col-12 col-md-4">
            <div class="kpi-stat-box">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            Total Sudah Terbayar
                        </span>
                        <div class="kpi-amount-text text-success currency-num">
                            Rp <?= number_format($totalTerbayarNilai, 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="kpi-icon-wrapper bg-success-subtle text-success">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
                <div class="pt-2.5 border-top mt-2">
                    <div class="d-flex align-items-center justify-content-between mb-1.5" style="font-size: 0.76rem;">
                        <span class="text-muted">Kelunasan:</span>
                        <strong class="text-success"><?= $persenLunas ?>%</strong>
                    </div>
                    <div class="progress" style="height: 7px; border-radius: 8px; background-color: #f1f5f9;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $persenLunas ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 3: Sisa Tunggakan Aktif -->
        <div class="col-12 col-md-4">
            <div class="kpi-stat-box">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="text-muted fw-bold text-uppercase d-block" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            Sisa Tagihan / Tunggakan
                        </span>
                        <div class="kpi-amount-text <?= $totalTunggakanNilai > 0 ? 'text-danger' : 'text-success' ?> currency-num">
                            Rp <?= number_format($totalTunggakanNilai, 0, ',', '.') ?>
                        </div>
                    </div>
                    <div class="kpi-icon-wrapper <?= $totalTunggakanNilai > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?>">
                        <i class="bi <?= $totalTunggakanNilai > 0 ? 'bi-clock-history' : 'bi-shield-check' ?>"></i>
                    </div>
                </div>
                <div class="pt-2.5 border-top mt-2" style="font-size: 0.76rem;">
                    <?php if ($summary['count_belum_lunas'] > 0): ?>
                        <span class="text-danger fw-semibold">
                            <i class="bi bi-exclamation-circle me-1"></i><?= $summary['count_belum_lunas'] ?> item belum diselesaikan
                        </span>
                    <?php else: ?>
                        <span class="text-success fw-semibold">
                            <i class="bi bi-patch-check-fill me-1"></i>Seluruh tagihan terselesaikan (Lunas)
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. KONTEN UTAMA: TABEL & KARTU TAGIHAN (BERJARAK LEGA) -->
    <div class="custom-tab-container mb-4">
        <!-- Header Bar: Tab Navigation + Search Filter -->
        <div class="modern-tab-header">
            <div class="row align-items-center g-2.5">
                <div class="col-12 col-lg-8">
                    <ul class="nav modern-nav-pills" id="pembayaranTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tagihan-tab" data-bs-toggle="tab" data-bs-target="#tagihan-pane" type="button" role="tab">
                                <i class="bi bi-hourglass-split text-warning"></i> Tagihan Belum Lunas (<?= count($unpaidBills) ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="riwayat-tab" data-bs-toggle="tab" data-bs-target="#riwayat-pane" type="button" role="tab">
                                <i class="bi bi-clock-history text-primary"></i> Riwayat Pembayaran (<?= count($riwayat) ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="semua-tab" data-bs-toggle="tab" data-bs-target="#semua-pane" type="button" role="tab">
                                <i class="bi bi-card-list text-secondary"></i> Semua Tagihan (<?= count($bills) ?>)
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Instant Quick Search & View Switcher -->
                <div class="col-12 col-lg-4 d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm flex-grow-1">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="filterBillInput" class="form-control border-start-0 py-2" placeholder="Cari uraian tagihan/bulan..." onkeyup="filterBillsClientSide()">
                        <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('filterBillInput').value=''; filterBillsClientSide();"><i class="bi bi-x"></i></button>
                    </div>
                    <!-- View Toggle -->
                    <div class="btn-group btn-group-sm flex-shrink-0" role="group" aria-label="Tampilan">
                        <button type="button" class="btn btn-outline-secondary active px-2.5 py-1.5" id="viewModeGridBtn" onclick="setViewMode('grid')" title="Mode Kartu">
                            <i class="bi bi-grid-fill"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary px-2.5 py-1.5" id="viewModeListBtn" onclick="setViewMode('list')" title="Mode Daftar Ringkas">
                            <i class="bi bi-list-ul"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Body Tab Contents (Padding Lega) -->
        <div class="p-3 p-md-4 bg-white">
            <div class="tab-content" id="pembayaranTabContent">

                <!-- ========================================== -->
                <!-- TAB 1: TAGIHAN BELUM LUNAS                 -->
                <!-- ========================================== -->
                <div class="tab-pane fade show active" id="tagihan-pane" role="tabpanel">
                    <?php if (empty($bills)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-wallet2 fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <h5 class="fw-bold text-dark mb-1">Belum Ada Data Tagihan</h5>
                            <p class="small mb-0" style="max-width: 500px; margin: 0 auto;">Data administrasi tagihan SPP Anda saat ini belum tercatat dari kasir sekolah.</p>
                        </div>
                    <?php elseif (empty($unpaidBills)): ?>
                        <div class="text-center py-5">
                            <div class="bg-success-subtle text-success p-3 rounded-circle d-inline-flex mb-3">
                                <i class="bi bi-patch-check-fill fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Alhamdulillah, Tidak Ada Tunggakan!</h5>
                            <p class="text-muted small mb-3">Seluruh administrasi pembayaran SPP dan biaya pendidikan Anda telah lunas tercatat di kasir.</p>
                            <button type="button" class="btn btn-outline-primary rounded-pill px-4 py-2 btn-sm fw-semibold" onclick="document.getElementById('riwayat-tab').click()">
                                <i class="bi bi-clock-history me-1"></i> Buka Riwayat Transaksi Lunas
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Quick Category Filter Chips (Berjarak Lega) -->
                        <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom flex-wrap gap-2" style="font-size: 0.78rem;">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="text-muted fw-bold me-1"><i class="bi bi-funnel me-1"></i>Filter Kategori:</span>
                                <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 py-1 filter-category-btn active" data-cat="all" onclick="filterByCategory('all', this)" style="font-size: 0.74rem;">Semua (<?= count($unpaidBills) ?>)</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 filter-category-btn" data-cat="spp" onclick="filterByCategory('spp', this)" style="font-size: 0.74rem;">SPP Bulanan</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 filter-category-btn" data-cat="non-spp" onclick="filterByCategory('non-spp', this)" style="font-size: 0.74rem;">Iuran Non-SPP</button>
                            </div>
                            <small class="text-muted d-none d-sm-inline">
                                Menampilkan <strong id="unpaidCountDisplay"><?= count($unpaidBills) ?></strong> item tagihan aktif
                            </small>
                        </div>

                        <!-- 1. TAMPILAN MODE GRID (BERJARAK LEGA & TIDAK BERHIMPITAN) -->
                        <div class="row g-3 g-xl-4" id="unpaidBillsGrid">
                            <?php foreach ($unpaidBills as $bill): 
                                $isDue = !empty($bill['tanggal_jatuh_tempo']) && strtotime($bill['tanggal_jatuh_tempo']) < time();
                                $isSpp = stripos($bill['judul'], 'SPP') !== false || stripos($bill['jenis_pembayaran'], 'SPP') !== false;
                                $catType = $isSpp ? 'spp' : 'non-spp';
                                $terbayarSebagian = $bill['nominal_terbayar'] > 0;
                            ?>
                                <div class="col-12 col-md-6 col-xl-4 unpaid-bill-card" data-category="<?= $catType ?>" data-title="<?= strtolower(htmlspecialchars($bill['judul'] . ' ' . $bill['kode_tagihan'])) ?>">
                                    <div class="bill-card-item">
                                        <div>
                                            <!-- Header Item: Kategori & Status Badge (Ultra Responsive, Anti-Overflow) -->
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-1.5 mb-2.5">
                                                <span class="badge bg-light text-secondary border px-2 py-0.5 rounded font-mono text-truncate" style="font-size: 0.68rem; max-width: calc(100% - 92px);" title="<?= htmlspecialchars($bill['kode_tagihan']) ?>">
                                                    <?= htmlspecialchars($bill['kode_tagihan']) ?>
                                                </span>
                                                <?php if ($terbayarSebagian): ?>
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-0.5 fw-semibold flex-shrink-0 ms-auto" style="font-size: 0.7rem; white-space: nowrap;">
                                                        Sebagian
                                                    </span>
                                                <?php elseif ($isDue): ?>
                                                    <span class="badge bg-danger text-white rounded-pill px-2.5 py-0.5 fw-semibold flex-shrink-0 ms-auto" style="font-size: 0.7rem; white-space: nowrap;">
                                                        Lewat Tempo
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-0.5 fw-semibold flex-shrink-0 ms-auto" style="font-size: 0.7rem; white-space: nowrap;">
                                                        Belum Lunas
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Judul Tagihan -->
                                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem; line-height: 1.35;">
                                                <?= htmlspecialchars($bill['judul']) ?>
                                            </h6>
                                            <small class="text-muted d-block mb-2" style="font-size: 0.75rem;">
                                                <?= htmlspecialchars($bill['jenis_pembayaran']) ?>
                                                <?php if (!empty($bill['periode_bulan'])): ?>
                                                    &bull; <?= htmlspecialchars($bill['periode_bulan']) ?>
                                                <?php endif; ?>
                                            </small>

                                            <!-- Nominal Box dengan Jarak Lega -->
                                            <div class="bill-amount-box">
                                                <div class="d-flex justify-content-between align-items-center mb-1.5" style="font-size: 0.75rem;">
                                                    <span class="text-muted">Total Tagihan:</span>
                                                    <span class="fw-semibold text-secondary currency-num">Rp <?= number_format($bill['nominal'], 0, ',', '.') ?></span>
                                                </div>
                                                <?php if ($terbayarSebagian): ?>
                                                    <div class="d-flex justify-content-between align-items-center mb-1.5" style="font-size: 0.75rem;">
                                                        <span class="text-success">Sudah Dibayar:</span>
                                                        <span class="fw-semibold text-success currency-num">Rp <?= number_format($bill['nominal_terbayar'], 0, ',', '.') ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="d-flex justify-content-between align-items-center pt-2 mt-1 border-top">
                                                    <span class="fw-bold text-danger" style="font-size: 0.78rem;">Sisa Tagihan:</span>
                                                    <span class="fw-bold text-danger currency-num fs-5">Rp <?= number_format($bill['sisa_tagihan'], 0, ',', '.') ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer: Tempo & Cara Bayar -->
                                        <div class="d-flex justify-content-between align-items-center pt-2.5 border-top flex-wrap gap-2">
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                <i class="bi bi-calendar-event me-1"></i>Tempo: 
                                                <strong class="<?= $isDue ? 'text-danger' : 'text-dark' ?>">
                                                    <?= !empty($bill['tanggal_jatuh_tempo']) ? date('d M Y', strtotime($bill['tanggal_jatuh_tempo'])) : '-' ?>
                                                </strong>
                                            </small>
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;" data-bs-toggle="modal" data-bs-target="#modalInfoRekening">
                                                <i class="bi bi-info-circle me-1"></i>Cara Bayar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- 2. TAMPILAN MODE LIST (SUPER RINGKAS, LEGA & RAPI) -->
                        <div id="unpaidBillsList" style="display: none;">
                            <?php foreach ($unpaidBills as $bill): 
                                $isDue = !empty($bill['tanggal_jatuh_tempo']) && strtotime($bill['tanggal_jatuh_tempo']) < time();
                                $isSpp = stripos($bill['judul'], 'SPP') !== false || stripos($bill['jenis_pembayaran'], 'SPP') !== false;
                                $catType = $isSpp ? 'spp' : 'non-spp';
                            ?>
                                <div class="bill-list-row unpaid-bill-card" data-category="<?= $catType ?>" data-title="<?= strtolower(htmlspecialchars($bill['judul'] . ' ' . $bill['kode_tagihan'])) ?>">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                <span class="fw-bold text-dark text-truncate" style="font-size: 0.9rem; max-width: 320px;">
                                                    <?= htmlspecialchars($bill['judul']) ?>
                                                </span>
                                                <span class="badge bg-light text-secondary border px-2 py-0.5 font-mono" style="font-size: 0.67rem;">
                                                    <?= htmlspecialchars($bill['kode_tagihan']) ?>
                                                </span>
                                            </div>
                                            <div class="text-muted text-truncate" style="font-size: 0.74rem;">
                                                <?= htmlspecialchars($bill['jenis_pembayaran']) ?>
                                                <?php if (!empty($bill['tanggal_jatuh_tempo'])): ?>
                                                    &bull; Tempo: <?= date('d M Y', strtotime($bill['tanggal_jatuh_tempo'])) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-end flex-shrink-0 ms-2">
                                            <div class="fw-bold text-danger currency-num fs-6">
                                                Rp <?= number_format($bill['sisa_tagihan'], 0, ',', '.') ?>
                                            </div>
                                            <span class="badge <?= $isDue ? 'bg-danger text-white' : 'bg-secondary-subtle text-secondary border' ?> rounded-pill px-2.5 py-0.5 flex-shrink-0" style="font-size: 0.68rem; white-space: nowrap;">
                                                <?= $isDue ? 'Lewat Tempo' : 'Belum Lunas' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ========================================== -->
                <!-- TAB 2: RIWAYAT TRANSAKSI LUNAS             -->
                <!-- ========================================== -->
                <div class="tab-pane fade" id="riwayat-pane" role="tabpanel">
                    <?php if (empty($riwayat)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-receipt-cutoff fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <h6 class="fw-bold text-dark mb-1">Belum Ada Riwayat Transaksi</h6>
                            <p class="small mb-0">Transaksi yang dilunasi di loket kasir Tata Usaha akan otomatis tercatat di sini.</p>
                        </div>
                    <?php else: ?>
                        <!-- Tampilan Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table payment-data-table align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 18%;">Waktu Bayar</th>
                                        <th style="width: 22%;">No. Kuitansi Kasir</th>
                                        <th style="width: 28%;">Uraian Tagihan</th>
                                        <th style="width: 12%;">Metode</th>
                                        <th style="width: 12%; text-align: right;">Nominal</th>
                                        <th style="width: 8%; text-align: center;">Slip</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($riwayat as $trx): ?>
                                        <tr class="bill-row-item" data-title="<?= strtolower(htmlspecialchars($trx['nama_tagihan'] . ' ' . $trx['nomor_transaksi'])) ?>">
                                            <td>
                                                <div class="fw-semibold text-dark small"><?= date('d M Y', strtotime($trx['tanggal_bayar'])) ?></div>
                                                <small class="text-muted" style="font-size: 0.72rem;"><?= date('H:i', strtotime($trx['tanggal_bayar'])) ?> WIB</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2.5 py-1 font-mono small">
                                                    <?= htmlspecialchars($trx['nomor_transaksi']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark" style="font-size: 0.88rem;"><?= htmlspecialchars($trx['nama_tagihan']) ?></div>
                                                <small class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($trx['jenis_pembayaran'] ?? 'Iuran Sekolah') ?> &bull; Loket TU</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                                                    <?= htmlspecialchars(ucfirst($trx['metode_pembayaran'] ?? 'Tunai')) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold text-success currency-num fs-6">Rp <?= number_format($trx['nominal_bayar'], 0, ',', '.') ?></span>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= BASE_URL ?>index.php?url=siswa/cetakSlip&id=<?= $trx['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" style="font-size:0.75rem;">
                                                    <i class="bi bi-printer me-1"></i>Slip
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Tampilan Mobile Cards untuk Riwayat -->
                        <div class="d-block d-md-none">
                            <?php foreach ($riwayat as $trx): ?>
                                <div class="mobile-trx-card bill-row-item" data-title="<?= strtolower(htmlspecialchars($trx['nama_tagihan'] . ' ' . $trx['nomor_transaksi'])) ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-light text-dark border px-2 py-0.5 font-mono" style="font-size: 0.67rem;">
                                                <?= htmlspecialchars($trx['nomor_transaksi']) ?>
                                            </span>
                                            <div class="fw-bold text-dark mt-1" style="font-size: 0.88rem;">
                                                <?= htmlspecialchars($trx['nama_tagihan']) ?>
                                            </div>
                                        </div>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 fw-semibold" style="font-size: 0.68rem;">
                                            Lunas
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center py-2 border-top border-bottom my-2" style="font-size: 0.75rem;">
                                        <span class="text-muted"><i class="bi bi-clock me-1"></i><?= date('d M Y H:i', strtotime($trx['tanggal_bayar'])) ?></span>
                                        <span class="fw-bold text-success currency-num fs-6">Rp <?= number_format($trx['nominal_bayar'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center pt-1">
                                        <small class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars(ucfirst($trx['metode_pembayaran'] ?? 'Tunai')) ?></small>
                                        <a href="<?= BASE_URL ?>index.php?url=siswa/cetakSlip&id=<?= $trx['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" style="font-size: 0.75rem;">
                                            <i class="bi bi-printer me-1"></i>Cetak Slip
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ========================================== -->
                <!-- TAB 3: SEMUA DAFTAR TAGIHAN                -->
                <!-- ========================================== -->
                <div class="tab-pane fade" id="semua-pane" role="tabpanel">
                    <div class="table-responsive d-none d-md-block">
                        <table class="table payment-data-table align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Kode</th>
                                    <th style="width: 33%;">Uraian Tagihan</th>
                                    <th style="width: 14%; text-align: right;">Total</th>
                                    <th style="width: 14%; text-align: right;">Terbayar</th>
                                    <th style="width: 14%; text-align: right;">Sisa</th>
                                    <th style="width: 10%; text-align: center;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bills as $b): ?>
                                    <tr class="bill-row-item" data-title="<?= strtolower(htmlspecialchars($b['judul'] . ' ' . $b['kode_tagihan'])) ?>">
                                        <td>
                                            <span class="badge bg-light text-dark border px-2.5 py-1 font-mono small">
                                                <?= htmlspecialchars($b['kode_tagihan']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark" style="font-size: 0.88rem;"><?= htmlspecialchars($b['judul']) ?></div>
                                            <small class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($b['jenis_pembayaran']) ?> &bull; <?= htmlspecialchars($b['periode_bulan'] ?? '-') ?></small>
                                        </td>
                                        <td class="text-end fw-semibold text-dark currency-num">Rp <?= number_format($b['nominal'], 0, ',', '.') ?></td>
                                        <td class="text-end fw-semibold text-success currency-num">Rp <?= number_format($b['nominal_terbayar'], 0, ',', '.') ?></td>
                                        <td class="text-end fw-bold <?= $b['sisa_tagihan'] > 0 ? 'text-danger' : 'text-muted' ?> currency-num">
                                            Rp <?= number_format($b['sisa_tagihan'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($b['status'] === 'lunas'): ?>
                                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1 small fw-semibold">Lunas</span>
                                            <?php elseif ($b['status'] === 'sebagian'): ?>
                                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 small fw-semibold">Sebagian</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 small fw-semibold">Belum Lunas</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tampilan Mobile Cards untuk Semua Tagihan -->
                    <div class="d-block d-md-none">
                        <?php foreach ($bills as $b): ?>
                            <div class="mobile-trx-card bill-row-item" data-title="<?= strtolower(htmlspecialchars($b['judul'] . ' ' . $b['kode_tagihan'])) ?>">
                                <div class="d-flex justify-content-between align-items-start mb-1.5">
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;"><?= htmlspecialchars($b['judul']) ?></div>
                                    <span class="badge <?= $b['status'] === 'lunas' ? 'bg-success' : ($b['status'] === 'sebagian' ? 'bg-warning text-dark' : 'bg-danger') ?> rounded-pill px-2.5 py-0.5" style="font-size: 0.68rem;">
                                        <?= ucfirst($b['status']) ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2 border-top border-bottom my-2" style="font-size: 0.76rem;">
                                    <span class="text-muted">Total: <strong class="text-dark currency-num">Rp <?= number_format($b['nominal'], 0, ',', '.') ?></strong></span>
                                    <span class="text-muted">Sisa: <strong class="<?= $b['sisa_tagihan'] > 0 ? 'text-danger' : 'text-success' ?> currency-num">Rp <?= number_format($b['sisa_tagihan'], 0, ',', '.') ?></strong></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
</main>

<!-- ==========================================================================
     MODAL: INFORMASI REKENING RESMI & CARA PEMBAYARAN
     ========================================================================== -->
<div class="modal fade" id="modalInfoRekening" tabindex="-1" aria-labelledby="modalInfoRekeningLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom p-3.5 bg-light rounded-top-4">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalInfoRekeningLabel">
                    <i class="bi bi-bank2 text-primary fs-5"></i>
                    <span>Informasi Rekening Sekolah & Cara Bayar</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3.5 p-sm-4">
                <p class="small text-muted mb-3" style="line-height: 1.6;">
                    Pembayaran tagihan SPP dan administrasi sekolah dapat disetorkan langsung di loket kasir Tata Usaha atau transfer ke rekening resmi <strong><?= htmlspecialchars($rekeningConfig['nama_sekolah'] ?? 'SMK Muthia Harapan Cicalengka') ?></strong> berikut:
                </p>

                <!-- Bank Accounts List -->
                <div class="d-flex flex-column gap-2.5 mb-3">
                    <?php if (!empty($rekeningConfig['bank_accounts'])): ?>
                        <?php foreach ($rekeningConfig['bank_accounts'] as $acc): 
                            $badgeColor = htmlspecialchars($acc['warna_badge'] ?? 'primary');
                            $cleanNum = preg_replace('/[^0-9]/', '', $acc['nomor_rekening']);
                        ?>
                            <div class="bank-account-card d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <span class="badge bg-<?= $badgeColor ?> px-2.5 py-0.5 rounded text-white fw-bold mb-1" style="font-size: 0.67rem; letter-spacing: 0.4px;">
                                        <?= htmlspecialchars(strtoupper($acc['bank'])) ?>
                                    </span>
                                    <div class="fw-bold font-mono fs-6 text-dark" id="rek-num-<?= $cleanNum ?>"><?= htmlspecialchars($acc['nomor_rekening']) ?></div>
                                    <small class="text-muted d-block mt-0.5" style="font-size: 0.74rem;">a.n <?= htmlspecialchars($acc['atas_nama']) ?></small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 copy-btn" onclick="copyRekening('<?= $cleanNum ?>', this)" style="font-size: 0.76rem;">
                                    <i class="bi bi-clipboard me-1"></i> Salin
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-light border small text-muted text-center py-3">
                            Informasi rekening bank resmi belum dikonfigurasi oleh Admin.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Prosedur Konfirmasi -->
                <div class="p-3 bg-warning-subtle rounded-3 border border-warning-subtle text-dark small" style="font-size: 0.78rem;">
                    <div class="fw-bold mb-1.5 text-warning-emphasis d-flex align-items-center gap-1.5">
                        <i class="bi bi-info-circle-fill"></i> Prosedur Konfirmasi Pembayaran:
                    </div>
                    <?php if (!empty($rekeningConfig['prosedur']) && is_array($rekeningConfig['prosedur'])): ?>
                        <ol class="mb-2 ps-3" style="line-height: 1.6;">
                            <?php foreach ($rekeningConfig['prosedur'] as $step): ?>
                                <li><?= htmlspecialchars($step) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php else: ?>
                        <p class="mb-2" style="line-height: 1.5;"><?= nl2br(htmlspecialchars($rekeningConfig['prosedur'] ?? 'Simpan bukti transfer dan tunjukkan kepada petugas kasir TU untuk diverifikasi.')) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($rekeningConfig['kontak_konfirmasi'])): ?>
                        <div class="pt-2 border-top border-warning-subtle d-flex align-items-center gap-2 fw-semibold text-dark">
                            <i class="bi bi-whatsapp text-success fs-5"></i>
                            <span>WhatsApp Loket Keuangan: <?= htmlspecialchars($rekeningConfig['kontak_konfirmasi']) ?></span>
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

<script>
// Filter realtime client-side input
function filterBillsClientSide() {
    const query = (document.getElementById('filterBillInput')?.value || '').toLowerCase().trim();
    
    const cards = document.querySelectorAll('.unpaid-bill-card');
    let visibleCount = 0;
    cards.forEach(card => {
        const title = (card.getAttribute('data-title') || '').toLowerCase();
        if (!query || title.includes(query)) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    const rows = document.querySelectorAll('.bill-row-item');
    rows.forEach(row => {
        const title = (row.getAttribute('data-title') || '').toLowerCase();
        if (!query || title.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Filter kategori cepat di Tab 1 (Semua / SPP / Non-SPP)
function filterByCategory(cat, btn) {
    document.querySelectorAll('.filter-category-btn').forEach(b => {
        b.classList.remove('active', 'btn-dark');
        b.classList.add('btn-outline-secondary');
    });
    btn.classList.add('active', 'btn-dark');
    btn.classList.remove('btn-outline-secondary');

    const cards = document.querySelectorAll('.unpaid-bill-card');
    cards.forEach(card => {
        const itemCat = card.getAttribute('data-category');
        if (cat === 'all' || itemCat === cat) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// Switcher View Mode (Grid vs List)
function setViewMode(mode) {
    const gridEl = document.getElementById('unpaidBillsGrid');
    const listEl = document.getElementById('unpaidBillsList');
    const gridBtn = document.getElementById('viewModeGridBtn');
    const listBtn = document.getElementById('viewModeListBtn');

    if (mode === 'list') {
        if (gridEl) gridEl.style.display = 'none';
        if (listEl) listEl.style.display = 'block';
        gridBtn.classList.remove('active');
        listBtn.classList.add('active');
    } else {
        if (gridEl) gridEl.style.display = '';
        if (listEl) listEl.style.display = 'none';
        listBtn.classList.remove('active');
        gridBtn.classList.add('active');
    }
}

// Copy Rekening Function with Visual Feedback
function copyRekening(num, btn) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(num).then(() => {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-lg text-success"></i> Tersalin!';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success', 'text-white');
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success', 'text-white');
                btn.classList.add('btn-outline-secondary');
            }, 2000);
        });
    } else {
        const temp = document.createElement('textarea');
        temp.value = num;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        alert('Nomor rekening ' + num + ' berhasil disalin!');
    }
}

// Auto detect mobile screen on load to optimize view if needed
document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth < 576) {
        setViewMode('list');
    }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
