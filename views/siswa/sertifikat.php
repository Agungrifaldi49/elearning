<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$appSettings = [];
$settingsPath = ROOT_PATH . 'config/settings.json';
if (file_exists($settingsPath)) {
    $appSettings = json_decode(file_get_contents($settingsPath), true) ?: [];
}
$schoolName = !empty($appSettings['nama_sekolah']) ? $appSettings['nama_sekolah'] : APP_NAME;
$kepalaSekolah = !empty($appSettings['kepala_sekolah']) ? $appSettings['kepala_sekolah'] : 'H. Supriyadi, M.M.';
$alamat = !empty($appSettings['alamat']) ? $appSettings['alamat'] : 'Jl. Raya Cicalengka, Kab. Bandung, Jawa Barat';

$rawLogo = $appSettings['logo'] ?? '';
$logoUrl = null;
if (!empty($rawLogo)) {
    if (strpos($rawLogo, 'assets/uploads/') === 0 || strpos($rawLogo, 'uploads/') === 0) {
        $logoUrl = BASE_URL . $rawLogo;
    } else {
        $logoUrl = BASE_URL . 'assets/uploads/logo/' . $rawLogo;
    }
}
?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 no-print flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-award-fill text-warning me-2"></i>Sertifikat Kelulusan & Prestasi</h4>
            <p class="text-muted small mb-0">Sertifikat digital resmi diterbitkan oleh <?= htmlspecialchars($schoolName) ?>.</p>
        </div>
        <button class="btn btn-warning text-dark fw-bold px-4 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Sertifikat
        </button>
    </div>

    <!-- Demo Sertifikat -->
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="sertifikat-wrapper text-center p-4 p-md-5 bg-white rounded-4 shadow-sm border border-2 border-primary">

                <!-- Header Brand -->
                <div class="d-flex justify-content-center align-items-center gap-3 mb-3">
                    <?php if ($logoUrl): ?>
                        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Sekolah" style="height:70px; max-width:200px; object-fit:contain;">
                    <?php else: ?>
                        <div class="bg-primary text-white rounded-4 p-3 px-4 shadow-sm me-2 d-inline-flex align-items-center justify-content-center" style="width:70px; height:70px;">
                            <i class="bi bi-mortarboard-fill fs-2"></i>
                        </div>
                    <?php endif; ?>
                    <div class="text-start">
                        <h4 class="fw-bold mb-0 text-primary" style="letter-spacing: 1px;"><?= htmlspecialchars(strtoupper($schoolName)) ?></h4>
                        <small class="text-muted"><?= htmlspecialchars($alamat) ?></small>
                    </div>
                </div>

                <hr class="border-2 border-primary opacity-50 my-3">

                <div class="py-2">
                    <span class="badge bg-warning text-dark px-4 py-2 fs-6 rounded-pill">
                        <i class="bi bi-award me-1"></i> SERTIFIKAT PENGHARGAAN KELULUSAN
                    </span>
                </div>

                <p class="text-muted mb-1 fs-6 mt-3">Dengan bangga diberikan kepada:</p>
                <h2 class="fw-bold text-primary display-6 my-2"><?= htmlspecialchars($siswa['nama_lengkap'] ?? 'Nama Siswa') ?></h2>
                <p class="text-muted mb-1">NIS: <?= htmlspecialchars($siswa['nis'] ?? '-') ?> &nbsp;|&nbsp; NISN: <?= htmlspecialchars($siswa['nisn'] ?? '-') ?></p>
                <p class="text-muted mb-4"><?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?> — <?= htmlspecialchars($siswa['nama_jurusan'] ?? '-') ?></p>

                <div class="bg-light rounded-4 p-4 mx-auto mb-4 border" style="max-width: 620px;">
                    <p class="fs-6 text-muted mb-0 fst-italic">
                        Telah berhasil menyelesaikan Program Pembelajaran Digital E-Learning
                        Semester Ganjil Tahun Pelajaran 2025/2026 dengan hasil yang memuaskan
                        dan menunjukkan semangat belajar yang luar biasa.
                    </p>
                </div>

                <!-- Real Stats Badges from Database -->
                <div class="row g-3 mb-4 justify-content-center">
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-primary-subtle text-primary rounded-3 shadow-xs">
                            <div class="fw-bold fs-5 mb-1"><?= htmlspecialchars($certStats['predikat'] ?? 'Belum Ada Data') ?></div>
                            <small class="text-muted fw-semibold">Predikat Hasil Belajar</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-success-subtle text-success rounded-3 shadow-xs">
                            <div class="fw-bold fs-5 mb-1"><?= htmlspecialchars($certStats['presensi_log'] ?? 'Belum Ada Data') ?></div>
                            <small class="text-muted fw-semibold">Tingkat Kehadiran KBM Real</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-warning-subtle text-warning rounded-3 shadow-xs">
                            <div class="fw-bold fs-5 mb-1"><?= htmlspecialchars($certStats['evaluasi_lms'] ?? 'Belum Ada Nilai') ?></div>
                            <small class="text-muted fw-semibold">Rata-Rata Evaluasi LMS Real</small>
                        </div>
                    </div>
                </div>

                <!-- Nomor Sertifikat & QR -->
                <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mt-4 pt-2">
                    <div class="text-start">
                        <small class="text-muted d-block">Nomor Sertifikat:</small>
                        <code class="fs-6 text-primary fw-bold">SMKMH/SERT/2025/<?= str_pad($siswa['id'] ?? 1, 4, '0', STR_PAD_LEFT) ?></code>
                        <small class="text-muted d-block mt-1">Diterbitkan: <?= date('d F Y') ?></small>
                    </div>

                    <div>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=85x85&data=SMKMH-CERT-<?= urlencode($siswa['nisn'] ?? 'UNKNOWN') ?>&color=0056d3&bgcolor=ffffff"
                             alt="QR Verifikasi" width="85" height="85" class="rounded-3 border border-2 border-primary">
                        <small class="text-muted d-block text-center mt-1" style="font-size:.68rem;">Scan untuk verifikasi</small>
                    </div>

                    <div class="text-center" style="min-width: 170px;">
                        <small class="text-muted d-block mb-4">Cicalengka, <?= date('d F Y') ?></small>
                        <div class="fw-bold text-dark border-bottom border-dark pb-1 mb-1"><?= htmlspecialchars($kepalaSekolah) ?></div>
                        <small class="text-muted">Kepala Sekolah</small>
                    </div>
                </div>

            </div><!-- end sertifikat -->
        </div>
    </div>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
