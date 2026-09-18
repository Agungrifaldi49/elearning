<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* =========================================================
   Executive Admin Payment Portal Styles
   ========================================================= */

/* Hero Banner */
.admin-payment-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 45%, #075985 100%);
    border-radius: 1.25rem;
    color: #ffffff;
    box-shadow: 0 12px 30px -8px rgba(15, 23, 42, 0.35);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.08);
}
.admin-payment-hero::before {
    content: "";
    position: absolute;
    top: -50%;
    right: -15%;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(14, 165, 233, 0.25) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.admin-payment-hero::after {
    content: "";
    position: absolute;
    bottom: -30%;
    left: 10%;
    width: 260px;
    height: 260px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.12) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

/* Stat KPI Cards */
.admin-stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1.15rem;
    padding: 1.35rem 1.25rem;
    box-shadow: 0 4px 10px -2px rgba(15, 23, 42, 0.04);
    transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}
.admin-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px -4px rgba(15, 23, 42, 0.09);
    border-color: #cbd5e1;
}
.stat-icon-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    flex-shrink: 0;
    transition: transform 0.25s ease;
}
.admin-stat-card:hover .stat-icon-wrapper {
    transform: scale(1.08);
}

/* Pulse Animation for Live Connection */
.pulse-dot-live {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    display: inline-block;
    background-color: #10b981;
    box-shadow: 0 0 0 rgba(16, 185, 129, 0.6);
    animation: pulseLive 2s infinite;
}
@keyframes pulseLive {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
    70% { box-shadow: 0 0 0 9px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.pulse-dot-waiting {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    display: inline-block;
    background-color: #f59e0b;
    box-shadow: 0 0 0 rgba(245, 158, 11, 0.6);
    animation: pulseWaiting 2s infinite;
}
@keyframes pulseWaiting {
    0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.6); }
    70% { box-shadow: 0 0 0 9px rgba(245, 158, 11, 0); }
    100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
}

/* Filter Card Bar */
.filter-card-bar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1.15rem;
    box-shadow: 0 3px 8px -2px rgba(15, 23, 42, 0.03);
}

/* Fast Filter Pills */
.fast-pill-btn {
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 5px 14px;
    border-radius: 50rem;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.fast-pill-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.fast-pill-btn.active {
    background: #0284c7;
    border-color: #0284c7;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.35);
}

/* Modern Data Table */
.payment-admin-table {
    margin-bottom: 0;
}
.payment-admin-table th {
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.72rem;
    letter-spacing: 0.6px;
    color: #475569;
    background-color: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    padding: 13px 16px;
    white-space: nowrap;
}
.payment-admin-table td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}
.payment-admin-table tr:hover td {
    background-color: #f8fafc;
}

/* Numeric alignment and tabular font */
.currency-num {
    font-variant-numeric: tabular-nums;
    font-feature-settings: "tnum";
    letter-spacing: -0.2px;
}

/* Student Avatar Chip */
.avatar-chip-admin {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    color: #ffffff;
    font-weight: 700;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);
}

/* Code block container */
.code-block-wrapper {
    position: relative;
    background: #0f172a;
    border-radius: 0.75rem;
    padding: 1rem;
    color: #e2e8f0;
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 0.82rem;
    line-height: 1.5;
}
.btn-copy-code {
    position: absolute;
    top: 8px;
    right: 8px;
    padding: 4px 10px;
    font-size: 0.72rem;
}
</style>

<main class="main-content px-3 px-md-4 py-3">
<div class="container-fluid">

    <!-- Flash Alert Notification -->
    <?php if (FlashHelper::hasSuccess()): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0 border-start border-4 border-success bg-white p-3" role="alert">
            <div class="d-flex align-items-center gap-3">
                <div class="p-2 bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                    <i class="bi bi-check-lg fs-5"></i>
                </div>
                <div>
                    <strong class="text-success d-block small">Pemberitahuan Berhasil</strong>
                    <span class="text-dark small"><?= FlashHelper::getSuccess() ?></span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (FlashHelper::hasError()): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0 border-start border-4 border-danger bg-white p-3" role="alert">
            <div class="d-flex align-items-center gap-3">
                <div class="p-2 bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                </div>
                <div>
                    <strong class="text-danger d-block small">Terjadi Kendala</strong>
                    <span class="text-dark small"><?= FlashHelper::getError() ?></span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- 1. Executive Modern Header Banner -->
    <div class="admin-payment-hero p-4 p-md-5 mb-4 shadow-sm">
        <div class="row align-items-center g-4">
            <div class="col-12 col-xl-7">
                <!-- Breadcrumb & Badges -->
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <span class="badge bg-white text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase shadow-xs" style="font-size: 0.74rem; letter-spacing: 0.5px; color: #000000 !important;">
                        <i class="bi bi-shield-check text-primary me-1.5"></i> <span style="color: #000000 !important;">Finance & Billing Bridge</span>
                    </span>
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-1.5 rounded-pill fw-bold" style="font-size: 0.72rem;">
                        <span class="pulse-dot-live me-1.5"></span> Arsitektur Lintas Server (Cross-Server)
                    </span>
                    <span class="badge bg-light text-dark px-2.5 py-1.5 rounded-pill fw-semibold" style="font-size: 0.72rem;">
                        T.A. 2025/2026
                    </span>
                </div>

                <h2 class="fw-bold mb-2 text-white d-flex align-items-center gap-2 flex-wrap">
                    <span>Portal & Rekapitulasi Pembayaran Siswa</span>
                </h2>
                <p class="text-white text-opacity-85 mb-0 small" style="max-width: 640px; line-height: 1.65;">
                    Pusat kendali administrasi iuran siswa (SPP, DSP, CBT Ujian) SMK Muthia Harapan Cicalengka. Dilengkapi jembatan sinkronisasi data langsung dengan server eksternal sistem kasir/keuangan sekolah.
                </p>
            </div>
            
            <div class="col-12 col-xl-5 text-xl-end">
                <div class="d-flex align-items-center justify-content-xl-end gap-2 flex-wrap">
                    <!-- Tarik Data Button -->
                    <button type="button" class="btn btn-warning text-dark fw-bold px-3.5 py-2.5 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTarikData">
                        <i class="bi bi-cloud-arrow-down-fill fs-5"></i>
                        <span>Tarik Data API</span>
                    </button>

                    <!-- Import CSV Button -->
                    <button type="button" class="btn btn-outline-light fw-bold px-3.5 py-2.5 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalImportCsv">
                        <i class="bi bi-file-earmark-spreadsheet-fill fs-5 text-success"></i>
                        <span>Import CSV</span>
                    </button>

                    <!-- Panduan Beda Server Button -->
                    <button type="button" class="btn btn-outline-light fw-bold px-3 py-2.5 rounded-3 shadow-sm d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalPanduanBedaServer" title="Buka Panduan Beda Server">
                        <i class="bi bi-journal-code fs-5 text-info"></i>
                        <span class="d-none d-md-inline small">Panduan</span>
                    </button>

                    <!-- Dropdown Tindakan Lanjutan (Maintenance / Reset) -->
                    <div class="dropdown d-inline">
                        <button class="btn btn-outline-light px-2.5 py-2.5 rounded-3 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu Opsi Lainnya">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0 py-2">
                            <li>
                                <a class="dropdown-item small d-flex align-items-center gap-2 py-2" href="<?= BASE_URL ?>assets/template_import_pembayaran.csv" download>
                                    <i class="bi bi-download text-success"></i> Unduh Format Template CSV
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item small d-flex align-items-center gap-2 py-2" href="<?= BASE_URL ?>bridge_server_pembayaran.php" target="_blank" download>
                                    <i class="bi bi-code-slash text-primary"></i> Unduh File Bridge PHP
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="<?= BASE_URL ?>index.php?url=admin/syncPembayaran" onsubmit="return confirm('PERINGATAN RESIKO TINGGI: Seluruh data tagihan dan riwayat pembayaran di LMS akan DIKOSONGKAN (dihapus permanen). Lanjutkan?');">
                                    <input type="hidden" name="action" value="clear_data">
                                    <button type="submit" class="dropdown-item small text-danger d-flex align-items-center gap-2 py-2">
                                        <i class="bi bi-trash3-fill"></i> Kosongkan Semua Data Tagihan
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Status Koneksi Jembatan API Server (Cross-Server Connectivity) -->
    <?php if (!empty($bridgeConfig['server_url'])): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden border-start border-4 border-success">
            <div class="card-body p-3.5 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper bg-success-subtle text-success">
                        <i class="bi bi-hdd-network"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="pulse-dot-live"></span>
                            <strong class="text-dark small">Server Pembayaran Terhubung:</strong>
                            <code class="text-primary font-monospace bg-light px-2.5 py-0.5 rounded small border"><?= htmlspecialchars($bridgeConfig['server_url']) ?></code>
                            <button type="button" class="btn btn-link btn-sm p-0 text-muted" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($bridgeConfig['server_url']) ?>'); alert('URL Server Pembayaran disalin!');" title="Salin URL">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        <div class="text-muted" style="font-size:0.75rem;">
                            Terakhir Sinkronisasi: <strong class="text-dark"><?= htmlspecialchars($bridgeConfig['last_sync'] ?? 'Belum ada data') ?></strong> 
                            &bull; Status Respon: <span class="badge bg-success-subtle text-success fw-semibold border border-success-subtle"><?= htmlspecialchars($bridgeConfig['last_status'] ?? 'Aktif') ?></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-bold d-flex align-items-center gap-1.5 shadow-xs" data-bs-toggle="modal" data-bs-target="#modalTarikData">
                        <i class="bi bi-arrow-repeat"></i>
                        <span>Tarik Data Ulang</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#modalTarikData">
                        <i class="bi bi-gear me-1"></i> Edit Endpoint / Token
                    </button>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden border-start border-4 border-warning">
            <div class="card-body p-3.5 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-wrapper bg-warning-subtle text-warning">
                        <i class="bi bi-hdd-network-slash"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-0.5">
                            <span class="pulse-dot-waiting"></span>
                            <strong class="text-dark small">Integrasi API Lintas Server Belum Dikonfigurasi</strong>
                        </div>
                        <p class="text-muted small mb-0" style="font-size:0.75rem;">
                            Sistem saat ini dalam mode mandiri. Hubungkan URL server pembayaran eksternal atau impor berkas CSV untuk memperbarui data tagihan siswa.
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3.5 py-1.5 fw-bold d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalTarikData">
                        <i class="bi bi-link-45deg fs-6"></i>
                        <span>Hubungkan Sekarang</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1.5 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalPanduanBedaServer">
                        <i class="bi bi-question-circle me-1"></i> Panduan
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 3. 4 Executive Financial KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- KPI 1: Target Tagihan Sekolah -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-stat-card border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Target Tagihan Sekolah</span>
                        <h3 class="fw-bold mb-0 text-dark currency-num">Rp <?= number_format($globalStats['total_target'], 0, ',', '.') ?></h3>
                    </div>
                    <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <div class="pt-2.5 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-people me-1 text-primary"></i><?= $globalStats['total_siswa'] ?> Siswa Terdaftar</small>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 rounded-pill small fw-bold">Akumulasi T.A.</span>
                </div>
            </div>
        </div>

        <!-- KPI 2: Realisasi Pembayaran Masuk -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-stat-card border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Realisasi Pembayaran Masuk</span>
                        <h3 class="fw-bold mb-0 text-success currency-num">Rp <?= number_format($globalStats['total_masuk'], 0, ',', '.') ?></h3>
                    </div>
                    <div class="stat-icon-wrapper bg-success-subtle text-success">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
                <div class="pt-2.5 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-1 small" style="font-size:0.72rem;">
                        <span class="text-muted">Tingkat Capaian Pelunasan</span>
                        <strong class="text-success"><?= $globalStats['rate_pelunasan'] ?>%</strong>
                    </div>
                    <div class="progress" style="height: 6px; border-radius: 10px; background-color: #e2e8f0;">
                        <div class="progress-bar bg-success rounded-pill" style="width: <?= min(100, $globalStats['rate_pelunasan']) ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 3: Sisa Piutang / Tunggakan -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-stat-card border-start border-4 <?= $globalStats['total_piutang'] > 0 ? 'border-danger' : 'border-secondary' ?>">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Sisa Piutang</span>
                        <h3 class="fw-bold mb-0 <?= $globalStats['total_piutang'] > 0 ? 'text-danger' : 'text-secondary' ?> currency-num">
                            Rp <?= number_format($globalStats['total_piutang'], 0, ',', '.') ?>
                        </h3>
                    </div>
                    <div class="stat-icon-wrapper <?= $globalStats['total_piutang'] > 0 ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' ?>">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
                <div class="pt-2.5 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock-history me-1 text-danger"></i>Belum Dilunasi</small>
                    <span class="badge <?= $globalStats['total_piutang'] > 0 ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-secondary-subtle text-secondary' ?> px-2 py-0.5 rounded-pill small fw-bold">
                        <?= $globalStats['siswa_menunggak'] ?> Siswa Menunggak
                    </span>
                </div>
            </div>
        </div>

        <!-- KPI 4: Rasio Siswa -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="admin-stat-card border-start border-4 border-info">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Kepatuhan Finansial Siswa</span>
                        <h3 class="fw-bold mb-0 text-info currency-num"><?= $globalStats['siswa_lunas'] ?> <span class="fs-6 text-muted fw-normal">/ <?= $globalStats['total_siswa'] ?> Siswa</span></h3>
                    </div>
                    <div class="stat-icon-wrapper bg-info-subtle text-info">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                </div>
                <div class="pt-2.5 border-top d-flex align-items-center gap-1.5 flex-wrap" style="font-size:0.72rem;">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill fw-bold">Lunas: <?= $globalStats['siswa_lunas'] ?></span>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 rounded-pill fw-bold">Tunggakan: <?= $globalStats['siswa_menunggak'] ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Filter Bar & Fast-Pills Toolbar -->
    <div class="filter-card-bar p-3.5 mb-4">
        <!-- Quick Filter Pills Navigation -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom">
            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                <span class="small fw-bold text-muted me-1" style="font-size:0.75rem;">Status Cepat:</span>
                <a href="<?= BASE_URL ?>index.php?url=admin/pembayaran<?= !empty($filters['kelas_id']) ? '&kelas_id='.$filters['kelas_id'] : '' ?><?= !empty($filters['jurusan_id']) ? '&jurusan_id='.$filters['jurusan_id'] : '' ?>" class="fast-pill-btn <?= empty($filters['status']) ? 'active' : '' ?>">
                    <i class="bi bi-grid-fill"></i> Semua Status
                </a>
                <a href="<?= BASE_URL ?>index.php?url=admin/pembayaran&status=lunas<?= !empty($filters['kelas_id']) ? '&kelas_id='.$filters['kelas_id'] : '' ?><?= !empty($filters['jurusan_id']) ? '&jurusan_id='.$filters['jurusan_id'] : '' ?>" class="fast-pill-btn <?= ($filters['status'] === 'lunas') ? 'active' : '' ?>">
                    <i class="bi bi-check-circle-fill text-success"></i> Bebas / Lunas
                </a>
                <a href="<?= BASE_URL ?>index.php?url=admin/pembayaran&status=belum_lunas<?= !empty($filters['kelas_id']) ? '&kelas_id='.$filters['kelas_id'] : '' ?><?= !empty($filters['jurusan_id']) ? '&jurusan_id='.$filters['jurusan_id'] : '' ?>" class="fast-pill-btn <?= ($filters['status'] === 'belum_lunas') ? 'active' : '' ?>">
                    <i class="bi bi-exclamation-triangle-fill text-danger"></i> Ada Tunggakan
                </a>
            </div>
            <div class="small text-muted" style="font-size:0.75rem;">
                <i class="bi bi-info-circle me-1"></i> Data diperbarui otomatis dari tabel administrasi
            </div>
        </div>

        <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-2.5 align-items-center" id="formFilterPembayaran">
            <input type="hidden" name="url" value="admin/pembayaran">

            <!-- Rombel Kelas -->
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-bold text-muted mb-1 d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                    <i class="bi bi-mortarboard text-primary"></i> Rombel Kelas
                </label>
                <select name="kelas_id" class="form-select form-select-sm rounded-3">
                    <option value="">Semua Rombel Kelas</option>
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= ($filters['kelas_id'] == $k['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Konsentrasi Kejuruan -->
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-bold text-muted mb-1 d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                    <i class="bi bi-diagram-3 text-info"></i> Konsentrasi Kejuruan
                </label>
                <select name="jurusan_id" class="form-select form-select-sm rounded-3">
                    <option value="">Semua Kejuruan</option>
                    <?php foreach ($jurusanList as $j): ?>
                        <option value="<?= $j['id'] ?>" <?= ($filters['jurusan_id'] == $j['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($j['nama_jurusan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status Finansial Dropdown -->
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label small fw-bold text-muted mb-1 d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                    <i class="bi bi-funnel text-warning"></i> Status
                </label>
                <select name="status" class="form-select form-select-sm rounded-3">
                    <option value="">Semua Status</option>
                    <option value="lunas" <?= ($filters['status'] === 'lunas') ? 'selected' : '' ?>>Bebas / Lunas</option>
                    <option value="belum_lunas" <?= ($filters['status'] === 'belum_lunas') ? 'selected' : '' ?>>Ada Tunggakan</option>
                </select>
            </div>

            <!-- Search Field -->
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-bold text-muted mb-1 d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                    <i class="bi bi-search text-secondary"></i> Cari Siswa
                </label>
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control rounded-start-3" placeholder="NIS, NISN, atau Nama..." value="<?= htmlspecialchars($filters['search']) ?>">
                    <button type="submit" class="btn btn-primary rounded-end-3 px-3 fw-semibold">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>

            <!-- Reset Button -->
            <div class="col-12 col-md-1 d-flex align-items-end pt-md-3">
                <a href="<?= BASE_URL ?>index.php?url=admin/pembayaran" class="btn btn-sm btn-outline-secondary rounded-3 w-100 d-flex align-items-center justify-content-center" title="Reset Semua Filter">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> <span class="d-md-none small">Reset</span>
                </a>
            </div>
        </form>
    </div>

    <!-- 5. Main Data Table Container -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 pb-2.5 border-bottom">
                <div>
                    <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-table text-primary fs-4"></i>
                        <span>Rekapitulasi Administrasi Pembayaran Siswa</span>
                    </h5>
                    <small class="text-muted">Daftar akumulasi tagihan, realisasi pembayaran, dan status piutang masing-masing siswa.</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-bold" style="font-size: 0.75rem;">
                        <i class="bi bi-people-fill me-1"></i> Total: <?= count($studentsPaymentList) ?> Siswa Ditemukan
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Cetak Rekap
                    </button>
                </div>
            </div>

            <?php if ($globalStats['total_target'] <= 0): ?>
                <!-- Clean & Modern Onboarding Empty State Ready for Real Cross-Server Sync -->
                <div class="text-center py-5 my-2">
                    <div class="p-3.5 bg-primary-subtle text-primary rounded-circle d-inline-flex mb-3 shadow-xs" style="width: 76px; height: 76px; align-items: center; justify-content: center;">
                        <i class="bi bi-cloud-arrow-down fs-1"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">Database Pembayaran Bersih & Siap Menerima Data Asli</h4>
                    <p class="text-muted small mb-4" style="max-width: 620px; margin: 0 auto; line-height: 1.65;">
                        Seluruh data dummy telah dikosongkan. LMS SMK Muthia Harapan Cicalengka sekarang siap menarik dan merekapitulasi data pembayaran resmi langsung dari server keuangan sekolah Anda.
                    </p>

                    <!-- 3 Onboarding Steps -->
                    <div class="row g-3 justify-content-center mb-4 text-start" style="max-width: 820px; margin: 0 auto;">
                        <div class="col-12 col-md-4">
                            <div class="p-3.5 rounded-4 border bg-light h-100 shadow-2xs">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-primary rounded-pill px-2.5 py-1">Langkah 1</span>
                                    <i class="bi bi-hdd-network text-primary fs-5"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">Siapkan Jembatan API</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.78rem; line-height: 1.5;">
                                    Unggah file <code>bridge_server_pembayaran.php</code> ke web server aplikasi pembayaran Anda atau ekspor CSV dari sistem kasir/TU.
                                </p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3.5 rounded-4 border bg-light h-100 shadow-2xs">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-success rounded-pill px-2.5 py-1">Langkah 2</span>
                                    <i class="bi bi-cloud-download-fill text-success fs-5"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">Tarik atau Unggah Data</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.78rem; line-height: 1.5;">
                                    Klik tombol <strong>Tarik Data API</strong> atau <strong>Import CSV</strong> untuk melakukan sinkronisasi massal dalam hitungan detik.
                                </p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3.5 rounded-4 border bg-light h-100 shadow-2xs">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-info text-dark rounded-pill px-2.5 py-1">Langkah 3</span>
                                    <i class="bi bi-check2-all text-info fs-5"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">Monitoring Terpusat</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.78rem; line-height: 1.5;">
                                    Status lunas, rincian tagihan, serta slip pembayaran langsung tampil secara realtime di hak akses Siswa, Admin, & Kepala Sekolah.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Action Buttons -->
                    <div class="d-flex justify-content-center gap-2.5 flex-wrap">
                        <button type="button" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTarikData">
                            <i class="bi bi-cloud-arrow-down-fill fs-5"></i>
                            <span>Tarik Data (Lintas Server)</span>
                        </button>
                        <button type="button" class="btn btn-success text-white rounded-pill px-4 py-2.5 fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalImportCsv">
                            <i class="bi bi-file-earmark-spreadsheet-fill fs-5"></i>
                            <span>Import File CSV / Excel</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 py-2.5 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalPanduanBedaServer">
                            <i class="bi bi-book-half me-1.5 text-info"></i> Panduan Beda Server
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <!-- Table when populated with real data -->
                <div class="table-responsive">
                    <table class="table payment-admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 4%; text-align: center;">No</th>
                                <th style="width: 25%;">Identitas Siswa</th>
                                <th style="width: 17%;">Kelas & Jurusan</th>
                                <th style="width: 14%; text-align: right;">Total Tagihan</th>
                                <th style="width: 14%; text-align: right;">Terbayar</th>
                                <th style="width: 14%; text-align: right;">Sisa Tunggakan</th>
                                <th style="width: 8%; text-align: center;">Status</th>
                                <th style="width: 8%; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($studentsPaymentList as $row): 
                                $isLunas = ($row['total_tunggakan'] <= 0 && $row['total_nominal_tagihan'] > 0);
                                $hasBills = ($row['total_nominal_tagihan'] > 0);
                                $initial = strtoupper(substr($row['nama_lengkap'] ?? 'S', 0, 1));
                            ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted" style="font-size:0.85rem;"><?= $no++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="avatar-chip-admin"><?= $initial ?></div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size:0.9rem;"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                                <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                                    <span class="badge bg-light text-muted border px-2 py-0.5 rounded font-monospace" style="font-size: 0.72rem;">
                                                        NIS: <?= htmlspecialchars($row['nis'] ?? '-') ?>
                                                    </span>
                                                    <span class="badge bg-light text-muted border px-2 py-0.5 rounded font-monospace" style="font-size: 0.72rem;">
                                                        NISN: <?= htmlspecialchars($row['nisn'] ?? '-') ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill fw-bold small">
                                            <?= htmlspecialchars($row['nama_kelas'] ?? 'Belum Ada Kelas') ?>
                                        </span>
                                        <div class="text-muted mt-1 small" style="font-size: 0.72rem;">
                                            <i class="bi bi-diagram-3 me-1 text-info"></i><?= htmlspecialchars($row['nama_jurusan'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold text-dark currency-num">
                                        Rp <?= number_format($row['total_nominal_tagihan'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-end fw-bold text-success currency-num">
                                        Rp <?= number_format($row['total_terbayar'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-end fw-bold <?= $row['total_tunggakan'] > 0 ? 'text-danger' : 'text-muted' ?> currency-num">
                                        Rp <?= number_format($row['total_tunggakan'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!$hasBills): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1 rounded-pill small fw-semibold">
                                                Belum Ada Data
                                            </span>
                                        <?php elseif ($isLunas): ?>
                                            <span class="badge bg-success text-white rounded-pill px-3 py-1 fw-bold shadow-2xs" style="font-size: 0.72rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i>Lunas
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-bold shadow-2xs" style="font-size: 0.72rem;">
                                                <i class="bi bi-exclamation-circle-fill me-1"></i>Tunggakan
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold btn-detail-siswa" 
                                                data-siswa-id="<?= $row['siswa_id'] ?>" 
                                                data-siswa-nama="<?= htmlspecialchars($row['nama_lengkap']) ?>"
                                                data-siswa-nis="<?= htmlspecialchars($row['nis']) ?>"
                                                data-siswa-nisn="<?= htmlspecialchars($row['nisn']) ?>"
                                                data-siswa-kelas="<?= htmlspecialchars($row['nama_kelas']) ?>"
                                                title="Lihat Rincian Tagihan & Riwayat">
                                            <i class="bi bi-eye me-1"></i> Rincian
                                        </button>
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
</main>

<!-- =========================================================
     MODALS SECTION
     ========================================================= -->

<!-- MODAL 1: TARIK DATA LINTAS SERVER (REST API PULL) -->
<div class="modal fade" id="modalTarikData" tabindex="-1" aria-labelledby="modalTarikDataLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form method="POST" action="<?= BASE_URL ?>index.php?url=admin/syncPembayaran" id="formTarikData">
                <input type="hidden" name="action" value="remote_pull">

                <div class="modal-header border-bottom p-3.5 bg-light rounded-top-4">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalTarikDataLabel">
                        <i class="bi bi-cloud-arrow-down-fill text-primary fs-5"></i>
                        <span>Tarik Data dari Server Pembayaran (Lintas Server)</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="alert alert-primary rounded-3 p-3 mb-3 border-0 small d-flex align-items-start gap-2.5">
                        <i class="bi bi-info-circle-fill text-primary fs-5 mt-0.5"></i>
                        <div class="text-dark">
                            LMS akan menghubungi skrip jembatan API di server pembayaran Anda via internet (cURL terenkripsi) dan memperbarui database tagihan secara otomatis.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">URL Endpoint Jembatan Server Pembayaran <span class="text-danger">*</span></label>
                        <input type="url" name="server_url" class="form-control rounded-3 font-monospace" placeholder="https://domain-pembayaran.com/api/tagihan.php" value="<?= htmlspecialchars($bridgeConfig['server_url'] ?? '') ?>" required>
                        <small class="text-muted" style="font-size:0.75rem;">Contoh: <code>https://keuangan.smkmuthiaharapan.sch.id/api/bridge.php</code></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Token Rahasia API (Secret Bearer Token)</label>
                        <div class="input-group">
                            <input type="password" name="secret_token" id="secretTokenInput" class="form-control rounded-start-3 font-monospace" placeholder="Contoh: SMKMH_PAYMENT_SECRET_KEY_2026" value="<?= htmlspecialchars($bridgeConfig['secret_token'] ?? '') ?>">
                            <button type="button" class="btn btn-outline-secondary rounded-end-3" onclick="toggleTokenVisibility()">
                                <i class="bi bi-eye" id="toggleTokenIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted" style="font-size:0.75rem;">Kunci otentikasi rahasia yang sama dengan yang dikonfigurasi di file bridge.</small>
                    </div>

                    <div class="p-3 bg-light rounded-3 border small">
                        <div class="d-flex align-items-center gap-1.5 fw-bold text-dark mb-1">
                            <i class="bi bi-lightbulb-fill text-warning"></i> Tips Konfigurasi:
                        </div>
                        <span class="text-muted">Jika belum memasang file bridge di server pembayaran, silakan buka tab <strong>Panduan Beda Server</strong> untuk menyalin skrip <code>bridge_server_pembayaran.php</code>.</span>
                    </div>
                </div>

                <div class="modal-footer border-top p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-3.5 btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold shadow-xs" id="btnSubmitTarik">
                        <i class="bi bi-arrow-repeat me-1.5"></i> Tarik & Sinkronkan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: IMPORT FILE CSV / EXCEL -->
<div class="modal fade" id="modalImportCsv" tabindex="-1" aria-labelledby="modalImportCsvLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form method="POST" action="<?= BASE_URL ?>index.php?url=admin/syncPembayaran" enctype="multipart/form-data">
                <input type="hidden" name="action" value="import_csv">

                <div class="modal-header border-bottom p-3.5 bg-light rounded-top-4">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalImportCsvLabel">
                        <i class="bi bi-file-earmark-spreadsheet-fill text-success fs-5"></i>
                        <span>Import Data Tagihan Siswa (File CSV)</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        Ekspor data pembayaran dari aplikasi kasir / TU Anda ke format CSV, lalu unggah file tersebut untuk sinkronisasi massal seluruh siswa.
                    </p>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Pilih File CSV Rekap Tagihan <span class="text-danger">*</span></label>
                        <input type="file" name="csv_file" class="form-control rounded-3" accept=".csv,text/csv" required>
                        <small class="text-muted" style="font-size:0.75rem;">Mendukung pemisah koma (<code>,</code>) maupun titik koma (<code>;</code>).</small>
                    </div>

                    <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <div class="fw-bold small text-dark">Unduh Format Template CSV</div>
                            <small class="text-muted" style="font-size:0.72rem;">File contoh dengan kolom nisn, kode_tagihan, nominal, status, dll.</small>
                        </div>
                        <a href="<?= BASE_URL ?>assets/template_import_pembayaran.csv" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold" download>
                            <i class="bi bi-download me-1"></i> Unduh Template
                        </a>
                    </div>
                </div>

                <div class="modal-footer border-top p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-3.5 btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success text-white rounded-pill px-4 btn-sm fw-bold shadow-xs">
                        <i class="bi bi-upload me-1.5"></i> Unggah & Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: PANDUAN LENGKAP BEDA SERVER (WIZARD & CODE SNIPPET) -->
<div class="modal fade" id="modalPanduanBedaServer" tabindex="-1" aria-labelledby="modalPanduanBedaServerLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom p-3.5 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalPanduanBedaServerLabel">
                    <i class="bi bi-journal-code text-info fs-4"></i>
                    <span>Panduan Integrasi Sistem Pembayaran (Kasus Beda Server)</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 72vh; overflow-y: auto;">
                
                <!-- Notice Why Bridge is Needed -->
                <div class="alert alert-primary rounded-3 p-3 mb-4 border-0 small">
                    <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                        <i class="bi bi-info-circle-fill text-primary"></i> Mengapa Membutuhkan Skrip Jembatan?
                    </h6>
                    Karena server aplikasi pembayaran dan server LMS E-Learning berada di hosting/mesin yang berbeda (beda server), LMS tidak dapat terhubung langsung ke database MySQL pembayaran lewat <code>localhost</code>. Skrip jembatan bertindak sebagai pintu gerbang API yang aman dan terenkripsi.
                </div>

                <!-- Step 1 -->
                <div class="d-flex align-items-start gap-3 mb-4 pb-3 border-bottom">
                    <div class="badge bg-primary p-2.5 rounded-circle fs-6" style="width:38px; height:38px; display:flex; align-items:center; justify-content:center; flex-shrink: 0;">1</div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold text-dark mb-1">Letakkan File Jembatan di Server Pembayaran</h6>
                        <p class="small text-muted mb-2">
                            Kami telah menyediakan file skrip jembatan siap pakai bernama <strong><code>bridge_server_pembayaran.php</code></strong>. Unggah file tersebut ke direktori web server aplikasi keuangan Anda (misal: <code>public_html/api/tagihan.php</code>).
                        </p>
                        
                        <!-- Snippet Code Box -->
                        <div class="code-block-wrapper mb-2">
                            <button type="button" class="btn btn-sm btn-outline-light btn-copy-code" onclick="copyCodeSnippet(this)">
                                <i class="bi bi-clipboard me-1"></i> Salin Konfigurasi
                            </button>
                            <pre class="mb-0" id="codeBridgeSnippet">// Konfigurasi koneksi database di server pembayaran Anda:
$db_host = '127.0.0.1';
$db_name = 'nama_database_keuangan_anda';
$db_user = 'user_db_anda';
$db_pass = 'password_db_anda';
$secret_token = 'SMKMH_PAYMENT_SECRET_KEY_2026';</pre>
                        </div>

                        <a href="<?= BASE_URL ?>bridge_server_pembayaran.php" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3" download>
                            <i class="bi bi-download me-1"></i> Unduh File bridge_server_pembayaran.php
                        </a>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="d-flex align-items-start gap-3 mb-4 pb-3 border-bottom">
                    <div class="badge bg-success p-2.5 rounded-circle fs-6" style="width:38px; height:38px; display:flex; align-items:center; justify-content:center; flex-shrink: 0;">2</div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold text-dark mb-1">Tarik Data dari Portal Admin LMS</h6>
                        <p class="small text-muted mb-0">
                            Buka tombol <strong>Tarik Data API</strong> di halaman ini, masukkan URL tempat Anda menaruh file tadi (contoh: <code>https://pembayaran-sekolah.com/api/tagihan.php</code>), masukkan Token Rahasia, dan klik <strong>Tarik & Sinkronkan Sekarang</strong>.
                        </p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="d-flex align-items-start gap-3">
                    <div class="badge bg-info text-dark p-2.5 rounded-circle fs-6" style="width:38px; height:38px; display:flex; align-items:center; justify-content:center; flex-shrink: 0;">3</div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold text-dark mb-1">Status Langsung Realtime & Sinkron</h6>
                        <p class="small text-muted mb-0">
                            LMS akan otomatis mencocokkan data tagihan berdasarkan <strong>NISN</strong> atau <strong>NIS</strong> siswa. Seluruh data tagihan dan riwayat pembayaran asli langsung dapat dilihat oleh siswa di akunnya dan oleh Kepala Sekolah di dashboard eksekutif.
                        </p>
                    </div>
                </div>

            </div>
            <div class="modal-footer border-top p-3 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Tutup Panduan</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 4: DETAIL RINCIAN PEMBAYARAN SISWA (DYNAMIC AJAX) -->
<div class="modal fade" id="modalDetailSiswa" tabindex="-1" aria-labelledby="modalDetailSiswaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom p-3.5 bg-light rounded-top-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="avatar-chip-admin" id="detailModalAvatar">S</div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="detailModalNama">Nama Siswa</h6>
                        <small class="text-muted" style="font-size:0.75rem;" id="detailModalSub">NIS: - &bull; Kelas: -</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Summary KPI Pills -->
                <div class="row g-2 mb-4" id="detailSummaryRow">
                    <div class="col-4">
                        <div class="p-2.5 rounded-3 bg-light border text-center">
                            <span class="text-muted small d-block" style="font-size:0.7rem;">TOTAL TAGIHAN</span>
                            <strong class="text-dark currency-num small" id="modalTotalTagihan">Rp 0</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2.5 rounded-3 bg-success-subtle border border-success-subtle text-center">
                            <span class="text-success small d-block" style="font-size:0.7rem;">TERBAYAR</span>
                            <strong class="text-success currency-num small" id="modalTotalTerbayar">Rp 0</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2.5 rounded-3 bg-danger-subtle border border-danger-subtle text-center">
                            <span class="text-danger small d-block" style="font-size:0.7rem;">SISA TUNGGAKAN</span>
                            <strong class="text-danger currency-num small" id="modalTotalTunggakan">Rp 0</strong>
                        </div>
                    </div>
                </div>

                <!-- Tabs: Tagihan vs Riwayat Transaksi -->
                <ul class="nav nav-pills nav-fill mb-3 bg-light p-1 rounded-3 small" id="detailTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-1.5" id="tab-bills-btn" data-bs-toggle="pill" data-bs-target="#tab-bills" type="button" role="tab">
                            <i class="bi bi-receipt me-1"></i> Rincian Tagihan (<span id="countBills">0</span>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-1.5" id="tab-history-btn" data-bs-toggle="pill" data-bs-target="#tab-history" type="button" role="tab">
                            <i class="bi bi-clock-history me-1"></i> Riwayat Pembayaran (<span id="countHistory">0</span>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="detailTabsContent">
                    <!-- Tab 1: Bills List -->
                    <div class="tab-pane fade show active" id="tab-bills" role="tabpanel">
                        <div id="billsLoadingSpinner" class="text-center py-4">
                            <div class="spinner-border text-primary spinner-border-sm me-1" role="status"></div>
                            <span class="text-muted small">Memuat data tagihan...</span>
                        </div>
                        <div class="table-responsive" id="billsTableWrapper" style="display:none;">
                            <table class="table table-sm align-middle small mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Tagihan</th>
                                        <th>Jatuh Tempo</th>
                                        <th class="text-end">Nominal</th>
                                        <th class="text-end">Terbayar</th>
                                        <th class="text-end">Sisa</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="billsTableBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Transaction History -->
                    <div class="tab-pane fade" id="tab-history" role="tabpanel">
                        <div class="table-responsive" id="historyTableWrapper">
                            <table class="table table-sm align-middle small mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No Transaksi</th>
                                        <th>Tanggal</th>
                                        <th>Item Tagihan</th>
                                        <th class="text-end">Jumlah Bayar</th>
                                        <th>Metode</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer border-top p-3 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle Password Visibility for Token Input
function toggleTokenVisibility() {
    const input = document.getElementById('secretTokenInput');
    const icon = document.getElementById('toggleTokenIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Copy Code Snippet in Modal Panduan
function copyCodeSnippet(btn) {
    const snippet = document.getElementById('codeBridgeSnippet').innerText;
    navigator.clipboard.writeText(snippet).then(() => {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg me-1 text-success"></i> Tersalin!';
        btn.classList.remove('btn-outline-light');
        btn.classList.add('btn-light', 'text-dark');
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-light', 'text-dark');
            btn.classList.add('btn-outline-light');
        }, 2000);
    });
}

// Loading state on form Tarik Data submission
document.getElementById('formTarikData')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnSubmitTarik');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menghubungi Server Pembayaran...';
    }
});

// Dynamic AJAX for Student Payment Detail Modal
document.querySelectorAll('.btn-detail-siswa').forEach(button => {
    button.addEventListener('click', function() {
        const siswaId = this.dataset.siswaId;
        const nama = this.dataset.siswaNama;
        const nis = this.dataset.siswaNis;
        const nisn = this.dataset.siswaNisn;
        const kelas = this.dataset.siswaKelas;

        // Set Header
        document.getElementById('detailModalNama').innerText = nama;
        document.getElementById('detailModalSub').innerText = `NIS: ${nis} • NISN: ${nisn} • Kelas: ${kelas}`;
        document.getElementById('detailModalAvatar').innerText = (nama.charAt(0) || 'S').toUpperCase();

        // Reset display
        document.getElementById('billsLoadingSpinner').style.display = 'block';
        document.getElementById('billsTableWrapper').style.display = 'none';
        document.getElementById('billsTableBody').innerHTML = '';
        document.getElementById('historyTableBody').innerHTML = '';

        // Open Modal
        const detailModal = new bootstrap.Modal(document.getElementById('modalDetailSiswa'));
        detailModal.show();

        // Fetch via AJAX
        fetch(`<?= BASE_URL ?>index.php?url=admin/pembayaranDetailAjax&siswa_id=${siswaId}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('billsLoadingSpinner').style.display = 'none';
                document.getElementById('billsTableWrapper').style.display = 'block';

                if (data.status) {
                    const summary = data.summary;
                    document.getElementById('modalTotalTagihan').innerText = 'Rp ' + Number(summary.total_nominal_tagihan || 0).toLocaleString('id-ID');
                    document.getElementById('modalTotalTerbayar').innerText = 'Rp ' + Number(summary.total_terbayar || 0).toLocaleString('id-ID');
                    document.getElementById('modalTotalTunggakan').innerText = 'Rp ' + Number(summary.total_tunggakan || 0).toLocaleString('id-ID');

                    // Bills
                    const bills = data.bills || [];
                    document.getElementById('countBills').innerText = bills.length;
                    const bTbody = document.getElementById('billsTableBody');
                    if (bills.length === 0) {
                        bTbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data tagihan.</td></tr>';
                    } else {
                        bills.forEach(b => {
                            const isPaid = (b.status === 'lunas');
                            const badge = isPaid 
                                ? '<span class="badge bg-success rounded-pill px-2 py-0.5">Lunas</span>' 
                                : '<span class="badge bg-danger rounded-pill px-2 py-0.5">Tunggakan</span>';
                            bTbody.innerHTML += `
                                <tr>
                                    <td>
                                        <strong>${b.judul}</strong>
                                        <div class="text-muted" style="font-size:0.72rem;">${b.jenis_pembayaran} • ${b.periode_bulan || '-'}</div>
                                    </td>
                                    <td class="text-muted">${b.tanggal_jatuh_tempo || '-'}</td>
                                    <td class="text-end fw-semibold currency-num">Rp ${Number(b.nominal).toLocaleString('id-ID')}</td>
                                    <td class="text-end text-success fw-semibold currency-num">Rp ${Number(b.nominal_terbayar).toLocaleString('id-ID')}</td>
                                    <td class="text-end text-danger fw-semibold currency-num">Rp ${Number(b.sisa_tagihan).toLocaleString('id-ID')}</td>
                                    <td class="text-center">${badge}</td>
                                </tr>
                            `;
                        });
                    }

                    // History
                    const history = data.history || [];
                    document.getElementById('countHistory').innerText = history.length;
                    const hTbody = document.getElementById('historyTableBody');
                    if (history.length === 0) {
                        hTbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Belum ada riwayat transaksi pembayaran.</td></tr>';
                    } else {
                        history.forEach(h => {
                            hTbody.innerHTML += `
                                <tr>
                                    <td><code class="text-primary">${h.nomor_transaksi || '-'}</code></td>
                                    <td class="text-muted">${h.tanggal_bayar || '-'}</td>
                                    <td>${h.nama_tagihan || '-'}</td>
                                    <td class="text-end text-success fw-semibold currency-num">Rp ${Number(h.nominal_bayar).toLocaleString('id-ID')}</td>
                                    <td><span class="badge bg-light text-dark border">${h.metode_pembayaran || 'Kasir'}</span></td>
                                    <td class="text-center"><span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Berhasil</span></td>
                                </tr>
                            `;
                        });
                    }
                }
            })
            .catch(err => {
                document.getElementById('billsLoadingSpinner').style.display = 'none';
                document.getElementById('billsTableWrapper').style.display = 'block';
                document.getElementById('billsTableBody').innerHTML = '<tr><td colspan="6" class="text-center text-danger py-3">Gagal memuat rincian data siswa.</td></tr>';
            });
    });
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
