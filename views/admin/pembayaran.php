<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

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

    <!-- Page Header & Action Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-wallet2 text-primary fs-3"></i>
                <span>Portal & Rekapitulasi Pembayaran Siswa</span>
            </h4>
            <p class="text-muted small mb-0">Manajemen penarikan data pembayaran siswa terintegrasi lintas server (Cross-Server Bridge).</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-primary rounded-3 fw-bold px-3 py-2 shadow-xs d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalTarikData">
                <i class="bi bi-cloud-arrow-down-fill"></i>
                <span>Tarik Data (Lintas Server)</span>
            </button>
            <button type="button" class="btn btn-success text-white rounded-3 fw-bold px-3 py-2 shadow-xs d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalImportCsv">
                <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                <span>Import File CSV</span>
            </button>
            <button type="button" class="btn btn-info text-white rounded-3 fw-bold px-3 py-2 shadow-xs d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalPanduanBedaServer">
                <i class="bi bi-book-half"></i>
                <span>Panduan Beda Server</span>
            </button>
            <form method="POST" action="<?= BASE_URL ?>index.php?url=admin/syncPembayaran" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan seluruh data tagihan pembayaran di LMS? Data yang ada akan dihapus.');" class="d-inline">
                <input type="hidden" name="action" value="clear_data">
                <button type="submit" class="btn btn-outline-danger rounded-3 fw-bold px-3 py-2 shadow-xs d-flex align-items-center gap-1" title="Kosongkan Semua Data Tagihan">
                    <i class="bi bi-trash3"></i>
                    <span class="d-none d-md-inline">Kosongkan Data</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Status Server Bridge Banner -->
    <?php if (!empty($bridgeConfig['server_url'])): ?>
        <div class="alert alert-light border rounded-4 p-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2 shadow-2xs">
            <div class="d-flex align-items-center gap-2.5">
                <div class="bg-primary text-white p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                    <i class="bi bi-hdd-network"></i>
                </div>
                <div>
                    <div class="small fw-bold text-dark">URL Server Pembayaran Terhubung: <code class="text-primary font-monospace"><?= htmlspecialchars($bridgeConfig['server_url']) ?></code></div>
                    <small class="text-muted" style="font-size:0.75rem;">
                        Terakhir ditarik: <strong><?= htmlspecialchars($bridgeConfig['last_sync'] ?? 'Belum pernah') ?></strong> &bull; Status: <span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($bridgeConfig['last_status'] ?? '-') ?></span>
                    </small>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalTarikData">
                <i class="bi bi-gear me-1"></i> Ubah Pengaturan API
            </button>
        </div>
    <?php endif; ?>

    <!-- 4 Global Financial KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3.5 border-start border-4 border-primary h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Total Target Tagihan</span>
                    <div class="bg-primary-subtle text-primary p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:38px; height:38px;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-dark">Rp <?= number_format($globalStats['total_target'], 0, ',', '.') ?></h4>
                <small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-people me-1 text-primary"></i><?= $globalStats['total_siswa'] ?> Siswa Terdata</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3.5 border-start border-4 border-success h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Total Penerimaan Masuk</span>
                    <div class="bg-success-subtle text-success p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:38px; height:38px;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-success">Rp <?= number_format($globalStats['total_masuk'], 0, ',', '.') ?></h4>
                <small class="text-success fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-graph-up-arrow me-1"></i>Efektivitas: <?= $globalStats['rate_pelunasan'] ?>%</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3.5 border-start border-4 border-danger h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Total Sisa Piutang</span>
                    <div class="bg-danger-subtle text-danger p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:38px; height:38px;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-danger">Rp <?= number_format($globalStats['total_piutang'], 0, ',', '.') ?></h4>
                <small class="text-danger fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-clock-history me-1"></i>Tunggakan Belum Lunas</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3.5 border-start border-4 border-info h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Rasio Pelunasan Siswa</span>
                    <div class="bg-info-subtle text-info p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width:38px; height:38px;">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-info"><?= $globalStats['siswa_lunas'] ?> / <?= $globalStats['total_siswa'] ?></h4>
                <div class="d-flex align-items-center gap-1.5 mt-1" style="font-size:0.75rem;">
                    <span class="badge bg-success-subtle text-success px-2 py-0.5 rounded-pill fw-bold">Lunas: <?= $globalStats['siswa_lunas'] ?></span>
                    <span class="badge bg-danger-subtle text-danger px-2 py-0.5 rounded-pill fw-bold">Menunggak: <?= $globalStats['siswa_menunggak'] ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3.5">
            <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-2 align-items-center">
                <input type="hidden" name="url" value="admin/pembayaran">

                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.75rem;">Filter Rombel Kelas</label>
                    <select name="kelas_id" class="form-select form-select-sm rounded-3">
                        <option value="">Semua Rombel Kelas</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= ($filters['kelas_id'] == $k['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.75rem;">Filter Jurusan</label>
                    <select name="jurusan_id" class="form-select form-select-sm rounded-3">
                        <option value="">Semua Jurusan</option>
                        <?php foreach ($jurusanList as $j): ?>
                            <option value="<?= $j['id'] ?>" <?= ($filters['jurusan_id'] == $j['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($j['nama_jurusan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.75rem;">Status Keuangan</label>
                    <select name="status" class="form-select form-select-sm rounded-3">
                        <option value="">Semua Status</option>
                        <option value="lunas" <?= ($filters['status'] === 'lunas') ? 'selected' : '' ?>>Bebas / Lunas</option>
                        <option value="belum_lunas" <?= ($filters['status'] === 'belum_lunas') ? 'selected' : '' ?>>Ada Tunggakan</option>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1" style="font-size: 0.75rem;">Cari Siswa</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control rounded-start-3" placeholder="NIS, NISN, Nama..." value="<?= htmlspecialchars($filters['search']) ?>">
                        <button type="submit" class="btn btn-primary rounded-end-3 px-3"><i class="bi bi-search"></i></button>
                    </div>
                </div>

                <div class="col-12 col-md-1 d-flex align-items-end pt-md-3">
                    <a href="<?= BASE_URL ?>index.php?url=admin/pembayaran" class="btn btn-sm btn-outline-secondary rounded-3 w-100" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Data Table -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-table text-primary"></i>
                    <span>Daftar Status Pembayaran Siswa</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-bold" style="font-size: 0.75rem;">
                    Total: <?= count($studentsPaymentList) ?> Siswa
                </span>
            </div>

            <?php if ($globalStats['total_target'] <= 0): ?>
                <!-- Clean Empty State Ready for Real Data -->
                <div class="text-center py-5">
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle d-inline-flex mb-3">
                        <i class="bi bi-cloud-arrow-down fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Database Pembayaran Bersih (Siap Menerima Data Asli)</h5>
                    <p class="text-muted small mb-4" style="max-width: 580px; margin: 0 auto;">
                        Seluruh data contoh (dummy) telah dikosongkan. Sistem siap menarik data asli dari Server Pembayaran Anda. Silakan gunakan tombol di bawah untuk menarik data lintas server atau mengunggah file CSV.
                    </p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalTarikData">
                            <i class="bi bi-cloud-arrow-down-fill me-1.5"></i> Tarik Data (Lintas Server)
                        </button>
                        <button type="button" class="btn btn-success text-white rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalImportCsv">
                            <i class="bi bi-file-earmark-spreadsheet-fill me-1.5"></i> Import File CSV / Excel
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr style="font-size: 0.82rem;">
                                <th style="width: 5%;">No</th>
                                <th style="width: 25%;">Identitas Siswa</th>
                                <th style="width: 18%;">Kelas & Jurusan</th>
                                <th style="width: 14%;">Total Tagihan</th>
                                <th style="width: 14%;">Terbayar</th>
                                <th style="width: 14%;">Sisa Tunggakan</th>
                                <th style="width: 10%; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($studentsPaymentList as $row): 
                                $isLunas = ($row['total_tunggakan'] <= 0 && $row['total_nominal_tagihan'] > 0);
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                        <small class="text-muted" style="font-size: 0.72rem;">
                                            NIS: <strong><?= htmlspecialchars($row['nis'] ?? '-') ?></strong> &bull; NISN: <strong><?= htmlspecialchars($row['nisn'] ?? '-') ?></strong>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary px-2 py-0.5 rounded-pill fw-bold small">
                                            <?= htmlspecialchars($row['nama_kelas'] ?? '-') ?>
                                        </span>
                                        <div class="text-muted mt-0.5" style="font-size: 0.72rem;"><?= htmlspecialchars($row['nama_jurusan'] ?? '-') ?></div>
                                    </td>
                                    <td class="fw-semibold text-dark">Rp <?= number_format($row['total_nominal_tagihan'], 0, ',', '.') ?></td>
                                    <td class="fw-semibold text-success">Rp <?= number_format($row['total_terbayar'], 0, ',', '.') ?></td>
                                    <td class="fw-bold <?= $row['total_tunggakan'] > 0 ? 'text-danger' : 'text-muted' ?>">
                                        Rp <?= number_format($row['total_tunggakan'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isLunas): ?>
                                            <span class="badge bg-success text-white rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i>Lunas
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                                                <i class="bi bi-exclamation-circle-fill me-1"></i>Tunggakan
                                            </span>
                                        <?php endif; ?>
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

<!-- MODAL 1: TARIK DATA LINTAS SERVER (REST API PULL) -->
<div class="modal fade" id="modalTarikData" tabindex="-1" aria-labelledby="modalTarikDataLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="<?= BASE_URL ?>index.php?url=admin/syncPembayaran">
                <input type="hidden" name="action" value="remote_pull">

                <div class="modal-header border-bottom p-3.5 bg-light rounded-top-4">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalTarikDataLabel">
                        <i class="bi bi-cloud-arrow-down-fill text-primary fs-5"></i>
                        <span>Tarik Data dari Server Pembayaran (Beda Server)</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        LMS akan menghubungi API di server pembayaran Anda melalui internet (cURL) dan memperbarui database lokal secara otomatis.
                    </p>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">URL Server Pembayaran (Endpoint API) <span class="text-danger">*</span></label>
                        <input type="url" name="server_url" class="form-control rounded-3" placeholder="https://domain-pembayaran.com/api/get_tagihan.php" value="<?= htmlspecialchars($bridgeConfig['server_url'] ?? '') ?>" required>
                        <small class="text-muted" style="font-size:0.75rem;">Alamat lengkap skrip bridge / API di server pembayaran Anda.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Token Rahasia API (Secret Token / Bearer Key)</label>
                        <input type="password" name="secret_token" class="form-control rounded-3 font-monospace" placeholder="Contoh: SMKMH_PAYMENT_SECRET_KEY_2026" value="<?= htmlspecialchars($bridgeConfig['secret_token'] ?? '') ?>">
                        <small class="text-muted" style="font-size:0.75rem;">Kode otentikasi agar hanya server LMS yang berhak menarik data.</small>
                    </div>

                    <div class="p-3 bg-light rounded-3 border small">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-info-circle text-primary"></i>
                            <span class="fw-bold text-dark">Belum pasang skrip di Server Pembayaran?</span>
                        </div>
                        <span class="text-muted">Buka tab <strong>Panduan Beda Server</strong> untuk menyalin skrip PHP yang tinggal Anda letakkan di server pembayaran.</span>
                    </div>
                </div>

                <div class="modal-footer border-top p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-3 btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold">
                        <i class="bi bi-arrow-repeat me-1"></i> Tarik & Sinkronkan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: IMPORT FILE CSV / EXCEL -->
<div class="modal fade" id="modalImportCsv" tabindex="-1" aria-labelledby="modalImportCsvLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="<?= BASE_URL ?>index.php?url=admin/syncPembayaran" enctype="multipart/form-data">
                <input type="hidden" name="action" value="import_csv">

                <div class="modal-header border-bottom p-3.5 bg-light rounded-top-4">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalImportCsvLabel">
                        <i class="bi bi-file-earmark-spreadsheet-fill text-success fs-5"></i>
                        <span>Import Data Tagihan (CSV / Excel)</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        Jika Anda mengekspor rekap pembayaran dari sistem kasir/TU ke format CSV atau Excel, Anda dapat langsung mengunggahnya ke sini.
                    </p>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Pilih File CSV Rekap Tagihan <span class="text-danger">*</span></label>
                        <input type="file" name="csv_file" class="form-control rounded-3" accept=".csv,text/csv" required>
                        <small class="text-muted" style="font-size:0.75rem;">Format didukung: <code>.csv</code> (Pemisah koma atau titik koma).</small>
                    </div>

                    <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold small text-dark">Unduh Format Template CSV</div>
                            <small class="text-muted" style="font-size:0.72rem;">Gunakan format kolom ini agar data terbaca sempurna.</small>
                        </div>
                        <a href="<?= BASE_URL ?>assets/template_import_pembayaran.csv" class="btn btn-sm btn-outline-success rounded-pill px-3" download>
                            <i class="bi bi-download me-1"></i> Unduh Template
                        </a>
                    </div>
                </div>

                <div class="modal-footer border-top p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-3 btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success text-white rounded-pill px-4 btn-sm fw-bold">
                        <i class="bi bi-upload me-1"></i> Unggah & Proses
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: PANDUAN LENGKAP BEDA SERVER -->
<div class="modal fade" id="modalPanduanBedaServer" tabindex="-1" aria-labelledby="modalPanduanBedaServerLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom p-3.5 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalPanduanBedaServerLabel">
                    <i class="bi bi-diagram-3-fill text-info fs-4"></i>
                    <span>Panduan Integrasi Sistem Pembayaran (Kasus Beda Server)</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                
                <div class="alert alert-primary rounded-3 p-3 mb-3 border-0 small">
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-lightbulb-fill text-warning me-1"></i>Mengapa Menggunakan Jembatan API (Bridge)?</h6>
                    Karena sistem pembayaran dan E-Learning berada di server yang berbeda, LMS tidak bisa langsung membuka database MySQL server pembayaran lewat <code>localhost</code>. Jembatan API memungkinkan LMS menarik data secara aman lewat internet tanpa membahayakan keamanan server pembayaran Anda.
                </div>

                <h6 class="fw-bold text-dark mb-2">Langkah 1: Letakkan File Jembatan di Server Pembayaran</h6>
                <p class="small text-muted mb-2">
                    Kami telah membuatkan file siap pakai bernama: <strong class="text-dark"><code>bridge_server_pembayaran.php</code></strong> (tersedia di root sistem LMS).
                </p>
                <ol class="small text-muted ps-3 mb-3">
                    <li>Salin atau unduh file <code>bridge_server_pembayaran.php</code>.</li>
                    <li>Upload file tersebut ke Server Pembayaran Anda (misal di folder web: <code>public_html/api/tagihan.php</code>).</li>
                    <li>Buka file tersebut di text editor dan sesuaikan 4 baris koneksi database server pembayaran Anda:
                        <div class="bg-dark text-white p-2.5 rounded-3 font-monospace my-1.5" style="font-size:0.75rem;">
                            $db_host = '127.0.0.1';<br>
                            $db_name = 'nama_database_pembayaran_anda';<br>
                            $db_user = 'user_db';<br>
                            $db_pass = 'password_db';<br>
                            $secret_token = 'SMKMH_PAYMENT_SECRET_KEY_2026';
                        </div>
                    </li>
                </ol>

                <h6 class="fw-bold text-dark mb-2">Langkah 2: Hubungkan di LMS E-Learning</h6>
                <ol class="small text-muted ps-3 mb-3">
                    <li>Buka menu **Portal & Rekap Pembayaran** di Admin LMS.</li>
                    <li>Klik tombol **Tarik Data (Lintas Server)**.</li>
                    <li>Masukkan URL file yang Anda upload tadi (misal: <code>https://pembayaran-sekolah.com/api/tagihan.php</code>).</li>
                    <li>Masukkan Token Rahasia yang sama (contoh: <code>SMKMH_PAYMENT_SECRET_KEY_2026</code>).</li>
                    <li>Klik **Tarik & Sinkronkan Sekarang**.</li>
                </ol>

                <h6 class="fw-bold text-dark mb-2">Langkah 3: Selesai!</h6>
                <p class="small text-muted mb-0">
                    LMS akan otomatis mengambil data tagihan siswa berdasarkan NISN/NIS, dan detik itu juga siswa dapat langsung melihat status pembayarannya di portal siswa.
                </p>

            </div>
            <div class="modal-footer border-top p-3 bg-light rounded-bottom-4 d-flex justify-content-between">
                <a href="<?= BASE_URL ?>bridge_server_pembayaran.php" target="_blank" class="btn btn-outline-primary rounded-pill btn-sm px-3" download>
                    <i class="bi bi-download me-1"></i> Unduh File bridge_server_pembayaran.php
                </a>
                <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
