<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <!-- Flash Notification -->
    <?php if (FlashHelper::has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-xs mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= FlashHelper::get('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (FlashHelper::has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-xs mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= FlashHelper::get('error') ?>
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
            <p class="text-muted small mb-0">Manajemen data administrasi SPP & iuran siswa terintegrasi dengan jembatan sistem pembayaran sekolah.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>index.php?url=admin/syncPembayaran" class="btn btn-outline-primary rounded-3 fw-bold px-3 py-2 shadow-xs d-flex align-items-center gap-1.5" onclick="return confirm('Tarik dan perbarui data dari jembatan sistem pembayaran?');">
                <i class="bi bi-arrow-repeat"></i>
                <span>Tarik & Sinkronkan Data</span>
            </a>
            <button type="button" class="btn btn-success text-white rounded-3 fw-bold px-3 py-2 shadow-xs d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalPanduanIntegrasi">
                <i class="bi bi-book-half"></i>
                <span>Panduan Integrasi Sistem</span>
            </button>
            <button type="button" class="btn btn-dark rounded-3 fw-bold px-3 py-2 shadow-xs d-flex align-items-center gap-1.5" onclick="window.print()">
                <i class="bi bi-printer-fill"></i>
                <span>Cetak Rekap</span>
            </button>
        </div>
    </div>

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
                    Total: <?= count($studentsPaymentList) ?> Siswa Ditemukan
                </span>
            </div>

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
                        <?php if (empty($studentsPaymentList)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted small">
                                    <i class="bi bi-search fs-3 d-block mb-1 text-secondary opacity-50"></i>
                                    Tidak ada data siswa yang cocok dengan filter pencarian.
                                </td>
                            </tr>
                        <?php else: ?>
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
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</main>

<!-- Modal: Panduan Integrasi Sistem Pembayaran (Untuk TU & Developer) -->
<div class="modal fade" id="modalPanduanIntegrasi" tabindex="-1" aria-labelledby="modalPanduanIntegrasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom p-3.5 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalPanduanIntegrasiLabel">
                    <i class="bi bi-plug-fill text-success fs-4"></i>
                    <span>Panduan Integrasi & Penarikan Data Sistem Pembayaran</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info rounded-3 p-3 mb-3 border-0 small">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-info-circle-fill text-info me-1"></i>Konsep Dasar Penghubung Sistem:</div>
                    Kunci utama yang menghubungkan sistem pembayaran luar Anda dengan LMS ini adalah <strong>NISN</strong> atau <strong>NIS</strong> siswa.
                </div>

                <h6 class="fw-bold text-dark mb-2">1. Format Data yang Dibutuhkan untuk Sinkronisasi:</h6>
                <p class="small text-muted mb-2">Sistem pembayaran cukup menyediakan data tagihan/pembayaran siswa dengan kolom-kolom berikut:</p>
                
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm small align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Kolom</th>
                                <th>Tipe Data</th>
                                <th>Keterangan</th>
                                <th>Contoh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>nisn</code> / <code>nis</code></td>
                                <td>VARCHAR</td>
                                <td>Identitas unik siswa di LMS (Wajib)</td>
                                <td><code>0071234567</code></td>
                            </tr>
                            <tr>
                                <td><code>kode_tagihan</code></td>
                                <td>VARCHAR</td>
                                <td>Kode unik tagihan agar tidak duplikat</td>
                                <td><code>SPP-2025-09-001</code></td>
                            </tr>
                            <tr>
                                <td><code>judul</code></td>
                                <td>VARCHAR</td>
                                <td>Nama tagihan iuran</td>
                                <td><code>SPP Bulan September 2025</code></td>
                            </tr>
                            <tr>
                                <td><code>jenis_pembayaran</code></td>
                                <td>VARCHAR</td>
                                <td>Kategori tagihan (SPP, DSP, Ujian, dll.)</td>
                                <td><code>SPP</code></td>
                            </tr>
                            <tr>
                                <td><code>nominal</code></td>
                                <td>DECIMAL</td>
                                <td>Jumlah total tagihan</td>
                                <td><code>250000</code></td>
                            </tr>
                            <tr>
                                <td><code>nominal_terbayar</code></td>
                                <td>DECIMAL</td>
                                <td>Jumlah yang sudah dibayar siswa</td>
                                <td><code>250000</code> (atau <code>0</code> jika belum)</td>
                            </tr>
                            <tr>
                                <td><code>status</code></td>
                                <td>ENUM</td>
                                <td>Status tagihan saat ini</td>
                                <td><code>lunas</code> / <code>belum_lunas</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold text-dark mb-2">2. Cara Menghubungkannya (Pilihan Metode):</h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <div class="fw-bold text-primary mb-1"><i class="bi bi-send-fill me-1"></i>Opsi A: Kirim Otomatis (Webhook API)</div>
                            <p class="small text-muted mb-2">Sistem pembayaran mengirimkan data JSON (POST) setiap kali siswa membayar ke endpoint LMS:</p>
                            <code class="d-block p-2 bg-dark text-warning rounded small font-monospace">POST <?= BASE_URL ?>index.php?url=api/pembayaran/sync</code>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <div class="fw-bold text-success mb-1"><i class="bi bi-arrow-repeat me-1"></i>Opsi B: Tarik Otomatis (Direct Database)</div>
                            <p class="small text-muted mb-2">Jika database pembayaran berada di server cPanel/MySQL yang sama, LMS langsung membaca tabel tagihan secara realtime melalui method <code>PembayaranModel</code>.</p>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-success-subtle rounded-3 text-dark small">
                    <i class="bi bi-check-circle-fill text-success me-1"></i> <strong>Sistem Anda Saat Ini Sudah Siap:</strong> Jembatan data lokal telah aktif dengan tabel <code>pembayaran_tagihan</code> dan <code>pembayaran_riwayat</code>. Tombol <em>"Tarik & Sinkronkan Data"</em> siap digunakan kapan saja.
                </div>
            </div>
            <div class="modal-footer border-top p-3 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Tutup Panduan</button>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
