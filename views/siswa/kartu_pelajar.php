<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$user = AuthHelper::user();
$avFile = $siswa['avatar'] ?? ($user['avatar'] ?? '');
$hasAvatar = false;
$avatarUrl = '';
if (!empty($avFile) && $avFile !== 'default_avatar.png') {
    if (file_exists(ROOT_PATH . 'assets/uploads/profile/' . $avFile)) {
        $hasAvatar = true;
        $avatarUrl = BASE_URL . 'assets/uploads/profile/' . htmlspecialchars($avFile);
    } elseif (file_exists(ROOT_PATH . 'assets/uploads/avatar/' . $avFile)) {
        $hasAvatar = true;
        $avatarUrl = BASE_URL . 'assets/uploads/avatar/' . htmlspecialchars($avFile);
    }
}
?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-credit-card-fill text-primary me-2"></i>Kartu Pelajar Digital</h4>
            <p class="text-muted small mb-0">Identitas digital resmi siswa dilengkapi QR Code presensi.</p>
        </div>
        <button class="btn btn-primary no-print" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Kartu Pelajar
        </button>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Front Side -->
        <div class="col-12 col-md-6 col-xl-4">
            <p class="text-muted small fw-bold text-center mb-2 no-print">▌ SISI DEPAN KARTU</p>
            <div class="kartu-pelajar kartu-wrapper mx-auto">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="fw-bold fs-6 text-warning">SMK MUTHIA HARAPAN</div>
                        <small style="opacity:.8; font-size:.72rem;">Cicalengka, Kab. Bandung, Jawa Barat</small>
                    </div>
                    <div class="bg-white text-primary rounded-2 px-2 py-1 fw-bold small">PELAJAR</div>
                </div>

                <div class="d-flex gap-3 align-items-center mb-3">
                    <?php if ($hasAvatar): ?>
                        <img src="<?= $avatarUrl ?>" alt="Foto Siswa" 
                             class="rounded-3 object-fit-cover flex-shrink-0 border border-2 border-white shadow-sm"
                             style="width:70px; height:85px;">
                    <?php else: ?>
                        <div class="rounded-3 bg-white d-flex align-items-center justify-content-center fw-bold text-primary flex-shrink-0 shadow-sm"
                             style="width:70px; height:85px; font-size:1.8rem;">
                            <?= strtoupper(substr($siswa['nama_lengkap'] ?? 'S', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div class="fw-bold fs-6 mb-1"><?= htmlspecialchars($siswa['nama_lengkap'] ?? 'Nama Siswa') ?></div>
                        <div class="small" style="opacity:.85;">NIS: <?= htmlspecialchars($siswa['nis'] ?? '-') ?></div>
                        <div class="small" style="opacity:.85;">NISN: <?= htmlspecialchars($siswa['nisn'] ?? '-') ?></div>
                        <div class="mt-1">
                            <span class="badge bg-warning text-dark" style="font-size:.7rem;"><?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?></span>
                            <span class="badge bg-white text-primary ms-1" style="font-size:.7rem;"><?= htmlspecialchars($siswa['nama_jurusan'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-top border-white border-opacity-25 d-flex justify-content-between align-items-center">
                    <div>
                        <small style="opacity:.7; font-size:.68rem;">Tahun Pelajaran</small>
                        <div class="fw-bold small">2025 / 2026</div>
                    </div>
                    <!-- QR Code using public API -->
                    <div class="bg-white rounded-2 p-1">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data=SMKMH-SISWA-<?= urlencode($siswa['nisn'] ?? 'UNKNOWN') ?>&color=003d99&bgcolor=ffffff"
                             alt="QR Presensi" width="70" height="70" style="border-radius:4px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Side -->
        <div class="col-12 col-md-6 col-xl-4">
            <p class="text-muted small fw-bold text-center mb-2 no-print">▌ SISI BELAKANG KARTU</p>
            <div class="card-custom p-4 kartu-wrapper mx-auto h-100">
                <div class="text-center mb-3">
                    <i class="bi bi-mortarboard-fill text-primary fs-2"></i>
                    <h6 class="fw-bold mt-1">SMK Muthia Harapan Cicalengka</h6>
                    <small class="text-muted">Jl. Raya Cicalengka, Kab. Bandung</small>
                </div>

                <table class="table table-sm small mb-3">
                    <tbody>
                        <tr><td class="text-muted">Jenis Kelamin</td><td class="fw-semibold"><?= $siswa['jenis_kelamin'] === 'L' ? 'Laki-Laki' : 'Perempuan' ?></td></tr>
                        <tr><td class="text-muted">No. Telepon</td><td class="fw-semibold"><?= htmlspecialchars($siswa['no_telepon'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">Jurusan</td><td class="fw-semibold"><?= htmlspecialchars($siswa['nama_jurusan'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">Status</td><td><span class="badge bg-success"><?= ucfirst($siswa['status'] ?? 'aktif') ?></span></td></tr>
                    </tbody>
                </table>

                <div class="bg-light rounded-3 p-2 text-center small text-muted">
                    <i class="bi bi-shield-lock-fill text-success me-1"></i>
                    Scan QR Code untuk verifikasi identitas & presensi digital
                </div>

                <div class="mt-3 pt-3 border-top text-center">
                    <div style="height:50px; border-bottom: 1px solid #dee2e6; width:70%; margin: auto;"></div>
                    <small class="text-muted d-block mt-1">Tanda Tangan Wali Kelas</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="row justify-content-center mt-4 no-print">
        <div class="col-12 col-xl-8">
            <div class="card-custom p-4">
                <div class="d-flex gap-3 align-items-start">
                    <i class="bi bi-info-circle-fill text-primary fs-4 flex-shrink-0"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Cara Menggunakan Kartu Pelajar Digital</h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>QR Code pada kartu dapat di-scan oleh guru menggunakan fitur <strong>Scan QR Hadir</strong> untuk presensi otomatis.</li>
                            <li>Klik tombol <strong>Cetak Kartu Pelajar</strong> untuk mencetak dalam format standar ID Card (A4/laminating).</li>
                            <li>Kartu ini merupakan identitas digital resmi yang dikeluarkan oleh SMK Muthia Harapan Cicalengka.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
