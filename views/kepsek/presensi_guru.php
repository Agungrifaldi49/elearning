<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-camera-fill text-success me-2"></i>Monitoring Presensi Selfie Guru & GTK</h4>
                <p class="text-muted small mb-0">Pengawasan visual kehadiran harian tenaga pengajar, foto selfie check-in/out, dan kepatuhan radius geofencing sekolah.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>index.php?url=kepsek/cetakLaporan&type=presensi_guru&tanggal=<?= urlencode($tanggal) ?>" target="_blank" class="btn btn-primary shadow-sm fw-bold">
                    <i class="bi bi-printer me-1"></i> Cetak Laporan Presensi PDF
                </a>
            </div>
        </div>

        <!-- Filter Tanggal & Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 h-100">
                    <label class="form-label small fw-bold text-muted mb-2"><i class="bi bi-calendar3 me-1 text-primary"></i>Pilih Tanggal Presensi</label>
                    <form method="GET" action="<?= BASE_URL ?>index.php" class="d-flex gap-2">
                        <input type="hidden" name="url" value="kepsek/presensiGuru">
                        <input type="date" name="tanggal" class="form-control fw-semibold" value="<?= htmlspecialchars($tanggal) ?>" onchange="this.form.submit()">
                        <button type="submit" class="btn btn-outline-primary px-3 fw-bold">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                    <small class="text-muted mt-2 d-block">Menampilkan data: <b><?= date('d F Y', strtotime($tanggal)) ?></b></small>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 text-center h-100 bg-success-subtle border border-success-subtle">
                    <div class="text-success display-6 fw-bold"><?= $attendanceStats['hadir'] ?></div>
                    <small class="fw-semibold text-success"><i class="bi bi-check-circle-fill me-1"></i>Hadir Tepat Waktu</small>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 text-center h-100 bg-warning-subtle border border-warning-subtle">
                    <div class="text-warning-emphasis display-6 fw-bold"><?= $attendanceStats['terlambat'] ?></div>
                    <small class="fw-semibold text-warning-emphasis"><i class="bi bi-alarm-fill me-1"></i>Terlambat</small>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 text-center h-100 bg-info-subtle border border-info-subtle">
                    <div class="text-info-emphasis display-6 fw-bold"><?= $attendanceStats['izin'] ?></div>
                    <small class="fw-semibold text-info-emphasis"><i class="bi bi-file-earmark-medical-fill me-1"></i>Izin / Sakit</small>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 text-center h-100 bg-danger-subtle border border-danger-subtle">
                    <div class="text-danger display-6 fw-bold"><?= $attendanceStats['belum_hadir'] ?></div>
                    <small class="fw-semibold text-danger"><i class="bi bi-x-circle-fill me-1"></i>Belum Hadir</small>
                </div>
            </div>
        </div>

        <!-- Tabel Monitoring Presensi Guru -->
        <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-people-fill text-primary me-2"></i>Daftar Presensi Guru (<?= count($attendanceStats['list']) ?> Pengajar Terdaftar)
                </h6>
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                    Status: <?= $attendanceStats['hadir'] + $attendanceStats['terlambat'] ?> dari <?= $attendanceStats['total_guru'] ?> Hadir
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle datatable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Foto Selfie</th>
                            <th>Nama Guru & NIP</th>
                            <th>Waktu Masuk</th>
                            <th>Waktu Pulang</th>
                            <th>Radius / Jarak GPS</th>
                            <th class="text-center">Status Kehadiran</th>
                            <th class="text-center">Aksi / Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attendanceStats['list'])): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada catatan presensi pada tanggal ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($attendanceStats['list'] as $i => $row): 
                                $hasMasuk = !empty($row['waktu_masuk']);
                                $hasPulang = !empty($row['waktu_pulang']);
                                $statusKehadiran = $row['status_kehadiran'] ?? ($hasMasuk ? 'Hadir' : 'Belum Hadir');
                                
                                $badgeColor = match($statusKehadiran) {
                                    'Hadir' => 'bg-success',
                                    'Terlambat' => 'bg-warning text-dark',
                                    'Izin', 'Sakit' => 'bg-info text-dark',
                                    default => 'bg-secondary'
                                };

                                $fotoMasuk = !empty($row['foto_masuk']) ? BASE_URL . $row['foto_masuk'] : null;
                                $fotoPulang = !empty($row['foto_pulang']) ? BASE_URL . $row['foto_pulang'] : null;
                            ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <?php if ($fotoMasuk): ?>
                                            <a href="javascript:void(0)" onclick="viewSelfie('<?= $fotoMasuk ?>', 'Foto Masuk: <?= htmlspecialchars(addslashes($row['nama_lengkap'])) ?>', '<?= date('H:i', strtotime($row['waktu_masuk'])) ?> WIB')">
                                                <img src="<?= $fotoMasuk ?>" alt="Selfie" class="rounded-3 shadow-sm border border-2 border-success" style="width: 48px; height: 48px; object-fit: cover;">
                                            </a>
                                        <?php else: ?>
                                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 48px; height: 48px;">
                                                <i class="bi bi-camera text-secondary fs-5"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                        <small class="text-muted">NIP: <code><?= htmlspecialchars($row['nip'] ?? '-') ?></code> | Telp: <?= htmlspecialchars($row['no_telepon'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <?php if ($hasMasuk): ?>
                                            <span class="fw-bold text-success"><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($row['waktu_masuk'])) ?> WIB</span>
                                        <?php else: ?>
                                            <span class="text-muted small">- Belum Absen -</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($hasPulang): ?>
                                            <span class="fw-bold text-primary"><i class="bi bi-clock-history me-1"></i><?= date('H:i', strtotime($row['waktu_pulang'])) ?> WIB</span>
                                        <?php else: ?>
                                            <span class="text-muted small">- Belum Checkout -</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['jarak_masuk_meter'])): ?>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= $row['jarak_masuk_meter'] ?> m dari sekolah
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $badgeColor ?> px-3 py-2 rounded-pill shadow-xs">
                                            <?= htmlspecialchars($statusKehadiran) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($fotoMasuk || $fotoPulang): ?>
                                            <button class="btn btn-sm btn-outline-primary rounded-3" onclick="viewSelfie('<?= $fotoMasuk ?: $fotoPulang ?>', 'Pratinjau Selfie: <?= htmlspecialchars(addslashes($row['nama_lengkap'])) ?>', 'Masuk: <?= $hasMasuk ? date('H:i', strtotime($row['waktu_masuk'])) : '-' ?> WIB | Pulang: <?= $hasPulang ? date('H:i', strtotime($row['waktu_pulang'])) : '-' ?> WIB')">
                                                <i class="bi bi-eye-fill me-1"></i> Detail Selfie
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">Tidak Ada Foto</span>
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
</main>

<!-- Modal Pratinjau Foto Selfie Resolusi Penuh -->
<div class="modal fade" id="modalSelfieViewer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="modalSelfieTitle">Foto Selfie Presensi</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="position-relative d-inline-block">
                    <img id="modalSelfieImg" src="" alt="Selfie" class="img-fluid rounded-4 shadow-sm border" style="max-height: 420px; object-fit: cover;">
                </div>
                <div class="mt-3 text-muted small" id="modalSelfieSub"></div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewSelfie(imgUrl, title, subtitle) {
    document.getElementById('modalSelfieImg').src = imgUrl;
    document.getElementById('modalSelfieTitle').innerText = title;
    document.getElementById('modalSelfieSub').innerText = subtitle;
    var modal = new bootstrap.Modal(document.getElementById('modalSelfieViewer'));
    modal.show();
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
