<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
.pembayaran-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0369a1 100%);
    border-radius: 1.25rem;
    color: #ffffff;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3);
    position: relative;
    overflow: hidden;
}
.pembayaran-hero::after {
    content: "";
    position: absolute;
    top: -30%;
    right: -10%;
    width: 260px;
    height: 260px;
    background: radial-gradient(circle, rgba(14, 165, 233, 0.25) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.payment-kpi-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
    transition: all 0.25s ease;
    height: 100%;
}
.payment-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.07);
}
.bill-card-item {
    border: 1px solid #e2e8f0;
    border-radius: 0.95rem;
    background: #ffffff;
    transition: all 0.2s ease;
}
.bill-card-item:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}
</style>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <!-- Flash Notification -->
    <?php if (FlashHelper::hasSuccess()): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-xs mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= FlashHelper::getSuccess() ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (FlashHelper::hasError()): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-xs mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= FlashHelper::getError() ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- 1. Hero Summary Card -->
    <div class="pembayaran-hero p-4 p-md-5 mb-4 shadow-sm">
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <span class="badge bg-white text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase shadow-xs" style="font-size: 0.75rem;">
                        <i class="bi bi-wallet2 text-primary me-1.5"></i> Portal Pembayaran & Administrasi SPP
                    </span>
                    <span class="badge <?= $summary['badge_class'] ?> px-3 py-1.5 rounded-pill fw-bold shadow-xs" style="font-size: 0.75rem;">
                        <i class="bi <?= $summary['is_bebas_keuangan'] ? 'bi-shield-check' : 'bi-exclamation-circle' ?> me-1"></i>
                        Status: <?= htmlspecialchars($summary['status_label']) ?>
                    </span>
                </div>
                <h3 class="fw-bold mb-2 text-white"><?= htmlspecialchars($siswa['nama_lengkap']) ?></h3>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-3 bg-white bg-opacity-10 text-white small flex-wrap">
                    <span>NIS: <strong><?= htmlspecialchars($siswa['nis'] ?? '-') ?></strong></span>
                    <span class="opacity-50">&bull;</span>
                    <span>NISN: <strong><?= htmlspecialchars($siswa['nisn'] ?? '-') ?></strong></span>
                    <span class="opacity-50">&bull;</span>
                    <span>Kelas: <strong><?= htmlspecialchars($siswa['nama_kelas'] ?? 'Rombel') ?></strong></span>
                    <span class="opacity-50">&bull;</span>
                    <span>Jurusan: <strong><?= htmlspecialchars($siswa['nama_jurusan'] ?? 'Kejuruan') ?></strong></span>
                </div>
            </div>
            <div class="col-12 col-lg-4 text-lg-end">
                <button type="button" class="btn btn-warning text-dark fw-bold px-4 py-2.5 rounded-3 shadow-sm w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#modalInfoRekening">
                    <i class="bi bi-info-circle-fill me-1.5"></i> Prosedur & Rekening Sekolah
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Financial Metrics KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="payment-kpi-card border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Total Tagihan Tahun Ini</span>
                    <div class="bg-primary-subtle text-primary p-2.5 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 text-dark">Rp <?= number_format($summary['total_tagihan'], 0, ',', '.') ?></h3>
                <small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-calendar-check me-1 text-primary"></i>Tahun Ajaran 2025/2026</small>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="payment-kpi-card border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Total Sudah Terbayar (Lunas)</span>
                    <div class="bg-success-subtle text-success p-2.5 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 text-success">Rp <?= number_format($summary['total_terbayar'], 0, ',', '.') ?></h3>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <div class="progress flex-grow-1" style="height: 6px; border-radius: 10px;">
                        <div class="progress-bar bg-success" style="width: <?= min(100, $summary['persen_lunas']) ?>%;"></div>
                    </div>
                    <span class="fw-bold text-success small" style="font-size:0.75rem;"><?= $summary['persen_lunas'] ?>%</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="payment-kpi-card border-start border-4 <?= $summary['total_tunggakan'] > 0 ? 'border-danger' : 'border-secondary' ?>">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Sisa Tagihan / Tunggakan</span>
                    <div class="<?= $summary['total_tunggakan'] > 0 ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' ?> p-2.5 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 <?= $summary['total_tunggakan'] > 0 ? 'text-danger' : 'text-secondary' ?>">
                    Rp <?= number_format($summary['total_tunggakan'], 0, ',', '.') ?>
                </h3>
                <small class="text-muted" style="font-size: 0.75rem;">
                    <?= $summary['count_belum_lunas'] > 0 ? "<span class='text-danger fw-semibold'><i class='bi bi-exclamation-circle me-1'></i>{$summary['count_belum_lunas']} item belum diselesaikan</span>" : "<span class='text-success fw-semibold'><i class='bi bi-check-all me-1'></i>Seluruh tagihan terselesaikan</span>" ?>
                </small>
            </div>
        </div>
    </div>

    <!-- 3. Navigation Tabs: Tagihan Aktif vs Riwayat Pembayaran -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-header bg-white border-bottom p-3 p-md-4">
            <ul class="nav nav-pills card-header-pills gap-2" id="pembayaranTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill fw-bold px-4 py-2" id="tagihan-tab" data-bs-toggle="tab" data-bs-target="#tagihan-pane" type="button" role="tab" style="font-size: 0.85rem;">
                        <i class="bi bi-hourglass-split me-1.5"></i> Tagihan Belum Lunas (<?= count($unpaidBills) ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold px-4 py-2" id="riwayat-tab" data-bs-toggle="tab" data-bs-target="#riwayat-pane" type="button" role="tab" style="font-size: 0.85rem;">
                        <i class="bi bi-clock-history me-1.5"></i> Riwayat Pembayaran Lunas (<?= count($riwayat) ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold px-4 py-2" id="semua-tab" data-bs-toggle="tab" data-bs-target="#semua-pane" type="button" role="tab" style="font-size: 0.85rem;">
                        <i class="bi bi-card-list me-1.5"></i> Semua Daftar Tagihan (<?= count($bills) ?>)
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="pembayaranTabContent">

                <!-- TAB 1: TAGIHAN AKTIF / BELUM LUNAS -->
                <div class="tab-pane fade show active" id="tagihan-pane" role="tabpanel">
                    <?php if (empty($bills)): ?>
                        <div class="text-center py-5">
                            <div class="bg-primary-subtle text-primary p-3 rounded-circle d-inline-flex mb-3">
                                <i class="bi bi-wallet2 fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Belum Ada Data Tagihan Terdaftar</h5>
                            <p class="text-muted small mb-3" style="max-width: 550px; margin: 0 auto;">Data administrasi tagihan SPP dan iuran pendidikan Anda saat ini belum dimuat dari sistem keuangan sekolah. Silakan hubungi bagian Tata Usaha / Keuangan untuk informasi lebih lanjut.</p>
                            <button type="button" class="btn btn-outline-primary rounded-pill px-4 btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalInfoRekening">
                                <i class="bi bi-info-circle me-1"></i> Prosedur & Rekening Sekolah
                            </button>
                        </div>
                    <?php elseif (empty($unpaidBills)): ?>
                        <div class="text-center py-5">
                            <div class="bg-success-subtle text-success p-3 rounded-circle d-inline-flex mb-3">
                                <i class="bi bi-patch-check-fill fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Alhamdulillah, Tidak Ada Tunggakan!</h5>
                            <p class="text-muted small mb-3">Seluruh administrasi pembayaran SPP dan biaya pendidikan Anda saat ini telah lunas tercatat di sistem sekolah.</p>
                            <button type="button" class="btn btn-outline-primary rounded-pill px-4" onclick="document.getElementById('riwayat-tab').click()">
                                <i class="bi bi-clock-history me-1"></i> Lihat Riwayat Pembayaran
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($unpaidBills as $bill): 
                                $isDue = !empty($bill['tanggal_jatuh_tempo']) && strtotime($bill['tanggal_jatuh_tempo']) < time();
                            ?>
                                <div class="col-12 col-lg-6">
                                    <div class="bill-card-item p-3.5 border-start border-4 <?= $isDue ? 'border-danger' : 'border-warning' ?>">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <div>
                                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-0.5 rounded-pill small fw-semibold" style="font-size: 0.68rem;">
                                                    <?= htmlspecialchars($bill['jenis_pembayaran']) ?> &bull; Kode: <?= htmlspecialchars($bill['kode_tagihan']) ?>
                                                </span>
                                                <h6 class="fw-bold text-dark mb-0 mt-1"><?= htmlspecialchars($bill['judul']) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($bill['keterangan'] ?? 'Iuran Sekolah') ?></small>
                                            </div>
                                            <span class="badge <?= $isDue ? 'bg-danger text-white' : 'bg-warning text-dark' ?> rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                                                <?= $isDue ? '<i class="bi bi-exclamation-diamond me-1"></i>Lewat Jatuh Tempo' : '<i class="bi bi-clock me-1"></i>Belum Lunas' ?>
                                            </span>
                                        </div>

                                        <div class="p-2.5 bg-light rounded-3 d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <small class="text-muted d-block" style="font-size:0.7rem;">Nominal Tagihan</small>
                                                <span class="fw-bold text-dark fs-6">Rp <?= number_format($bill['nominal'], 0, ',', '.') ?></span>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block" style="font-size:0.7rem;">Sisa yang Harus Dibayar</small>
                                                <span class="fw-bold text-danger fs-6">Rp <?= number_format($bill['sisa_tagihan'], 0, ',', '.') ?></span>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center pt-2 border-top flex-wrap gap-2">
                                            <small class="text-muted" style="font-size: 0.72rem;">
                                                <i class="bi bi-calendar-event me-1"></i>Batas Tempo: 
                                                <strong class="<?= $isDue ? 'text-danger' : 'text-dark' ?>">
                                                    <?= !empty($bill['tanggal_jatuh_tempo']) ? date('d M Y', strtotime($bill['tanggal_jatuh_tempo'])) : '-' ?>
                                                </strong>
                                            </small>
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold" style="font-size:0.78rem;" data-bs-toggle="modal" data-bs-target="#modalInfoRekening">
                                                <i class="bi bi-cash-coin me-1"></i> Cara Pembayaran
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
                            <p class="small mb-0">Riwayat transaksi pembayaran yang telah Anda lunasi akan tercatat otomatis di sini.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr style="font-size: 0.82rem;">
                                        <th style="width: 20%;">Tanggal & Jam</th>
                                        <th style="width: 22%;">No. Transaksi</th>
                                        <th style="width: 26%;">Nama Tagihan</th>
                                        <th style="width: 15%;">Metode Bayar</th>
                                        <th style="width: 17%; text-align: right;">Nominal Lunas</th>
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
                                                <span class="badge bg-light text-dark border px-2 py-1 rounded font-monospace small">
                                                    <?= htmlspecialchars($trx['nomor_transaksi']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark" style="font-size: 0.85rem;"><?= htmlspecialchars($trx['nama_tagihan']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($trx['jenis_pembayaran']) ?> &bull; <?= htmlspecialchars($trx['periode_bulan'] ?? '') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                                                    <i class="bi bi-credit-card me-1"></i><?= htmlspecialchars($trx['metode_pembayaran']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold text-success fs-6">Rp <?= number_format($trx['nominal_bayar'], 0, ',', '.') ?></span>
                                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 d-block mt-0.5 ms-auto fw-bold" style="width:fit-content; font-size:0.68rem;">Lunas</span>
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
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr style="font-size: 0.82rem;">
                                    <th style="width: 15%;">Kode</th>
                                    <th style="width: 30%;">Uraian Tagihan</th>
                                    <th style="width: 15%;">Nominal</th>
                                    <th style="width: 15%;">Terbayar</th>
                                    <th style="width: 15%;">Sisa</th>
                                    <th style="width: 10%; text-align: center;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bills as $b): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark border px-2 py-1 font-monospace small"><?= htmlspecialchars($b['kode_tagihan']) ?></span></td>
                                        <td>
                                            <div class="fw-bold text-dark" style="font-size: 0.85rem;"><?= htmlspecialchars($b['judul']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($b['jenis_pembayaran']) ?> &bull; Periode <?= htmlspecialchars($b['periode_bulan'] ?? '-') ?></small>
                                        </td>
                                        <td class="fw-semibold text-dark">Rp <?= number_format($b['nominal'], 0, ',', '.') ?></td>
                                        <td class="fw-semibold text-success">Rp <?= number_format($b['nominal_terbayar'], 0, ',', '.') ?></td>
                                        <td class="fw-semibold <?= $b['sisa_tagihan'] > 0 ? 'text-danger' : 'text-muted' ?>">Rp <?= number_format($b['sisa_tagihan'], 0, ',', '.') ?></td>
                                        <td class="text-center">
                                            <?php if ($b['status'] === 'lunas'): ?>
                                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1 small fw-bold">Lunas</span>
                                            <?php elseif ($b['status'] === 'sebagian'): ?>
                                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 small fw-bold">Sebagian</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 small fw-bold">Belum Lunas</span>
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
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom p-3.5 bg-light rounded-top-4">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalInfoRekeningLabel">
                    <i class="bi bi-bank2 text-primary fs-5"></i>
                    <span>Informasi Rekening & Prosedur Pembayaran</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">
                    Pembayaran SPP dan administrasi sekolah dapat dilakukan melalui transfer bank ke rekening resmi <strong>SMK Muthia Harapan Cicalengka</strong> berikut:
                </p>

                <!-- Bank Accounts List -->
                <div class="d-flex flex-column gap-2.5 mb-3">
                    <div class="p-3 rounded-3 border bg-white shadow-2xs d-flex align-items-center justify-content-between">
                        <div>
                            <span class="badge bg-primary px-2 py-0.5 rounded text-white fw-bold mb-1" style="font-size:0.68rem;">BANK BCA</span>
                            <div class="fw-bold font-monospace fs-6 text-dark">7780 9182 34</div>
                            <small class="text-muted" style="font-size:0.75rem;">a.n SMK Muthia Harapan Cicalengka</small>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" onclick="navigator.clipboard.writeText('7780918234'); alert('Nomor Rekening BCA berhasil disalin!');">
                            <i class="bi bi-copy me-1"></i> Salin
                        </button>
                    </div>

                    <div class="p-3 rounded-3 border bg-white shadow-2xs d-flex align-items-center justify-content-between">
                        <div>
                            <span class="badge bg-info px-2 py-0.5 rounded text-dark fw-bold mb-1" style="font-size:0.68rem;">BANK MANDIRI</span>
                            <div class="fw-bold font-monospace fs-6 text-dark">131 00 1982 7721</div>
                            <small class="text-muted" style="font-size:0.75rem;">a.n SMK Muthia Harapan Cicalengka</small>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" onclick="navigator.clipboard.writeText('1310019827721'); alert('Nomor Rekening Mandiri berhasil disalin!');">
                            <i class="bi bi-copy me-1"></i> Salin
                        </button>
                    </div>
                </div>

                <div class="p-3 bg-warning-subtle rounded-3 border border-warning-subtle text-dark small">
                    <div class="fw-bold mb-1 d-flex align-items-center gap-1.5 text-warning-emphasis">
                        <i class="bi bi-exclamation-circle-fill"></i> Prosedur Konfirmasi Pembayaran:
                    </div>
                    <ol class="mb-0 ps-3">
                        <li>Cantumkan <strong>NISN / Nama Siswa</strong> pada berita transfer.</li>
                        <li>Kirimkan bukti transfer ke Bagian Keuangan / Tata Usaha (Loket TU Sekolah atau WhatsApp Keuangan: 0812-xxxx-xxxx).</li>
                        <li>Status pembayaran di portal ini akan terbarui otomatis secara realtime setelah divalidasi oleh sistem keuangan.</li>
                    </ol>
                </div>
            </div>
            <div class="modal-footer border-top p-3 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
