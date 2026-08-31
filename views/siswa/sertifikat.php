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

<style>
/* Modern Luxury Certificate Architecture */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&display=swap');

.sertifikat-page-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

/* Glassmorphic Amber Hero Banner */
.sertifikat-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #78350f 50%, #d97706 100%);
    border-radius: 20px;
    box-shadow: 0 12px 30px -5px rgba(217, 119, 6, 0.25);
    position: relative;
    overflow: hidden;
}

/* Luxury Certificate Frame */
.sertifikat-paper-frame {
    background: #ffffff;
    border: 6px double #d97706;
    border-radius: 24px;
    box-shadow: 0 15px 45px rgba(15, 23, 42, 0.08);
    position: relative;
    overflow: hidden;
}

.sertifikat-paper-frame::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    border: 2px solid #fef3c7;
    margin: 8px;
    border-radius: 16px;
    pointer-events: none;
}

.recipient-name {
    font-family: 'Playfair Display', Georgia, serif !important;
    letter-spacing: -0.5px;
    color: #0f172a;
}

.stat-pill-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    transition: all 0.2s ease;
}
.stat-pill-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.05);
}

@media print {
    .no-print, header, nav, .sidebar, .navbar, .main-content-header {
        display: none !important;
    }
    body, .main-content, .container-fluid, .sertifikat-page-wrapper {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .sertifikat-hero-banner {
        display: none !important;
    }
    .sertifikat-paper-frame {
        box-shadow: none !important;
        border: 4px double #d97706 !important;
        padding: 1.5rem !important;
        border-radius: 0 !important;
    }
    ::-webkit-scrollbar {
        display: none !important;
    }
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>

<main class="main-content px-2 px-sm-3 px-md-4 py-3 sertifikat-page-wrapper">
<div class="container-fluid pt-3">

    <!-- Hero Banner Header (Screen Only) -->
    <div class="sertifikat-hero-banner text-white p-4 p-md-5 mb-4 no-print">
        <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-3 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-warning bg-gradient p-3.5 rounded-4 text-dark shadow-sm d-flex align-items-center justify-content-center" style="width: 58px; height: 58px; background: #f59e0b;">
                    <i class="bi bi-award-fill fs-2"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-white mb-1" style="letter-spacing: -0.4px;">Sertifikat Digital Kelulusan &amp; Prestasi</h3>
                    <p class="text-amber-100 small mb-0 fw-medium">Sertifikat resmi terverifikasi penerbitan otomatis dari <?= htmlspecialchars($schoolName) ?>.</p>
                </div>
            </div>

            <button onclick="window.print()" class="btn btn-warning text-dark fw-bold rounded-pill shadow-sm px-4 py-2.5 text-nowrap" style="font-size: 0.88rem;">
                <i class="bi bi-printer-fill me-1.5"></i> Cetak / Simpan PDF Sertifikat
            </button>
        </div>
    </div>

    <!-- Luxury Certificate Frame -->
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
            <div class="sertifikat-paper-frame text-center p-4 p-sm-5 bg-white mb-4">

                <!-- Header Brand & Logo -->
                <div class="d-flex justify-content-center align-items-center gap-3 mb-3 flex-wrap">
                    <?php if ($logoUrl): ?>
                        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Sekolah" style="height:65px; max-width:200px; object-fit:contain;">
                    <?php else: ?>
                        <div class="bg-primary text-white rounded-4 p-3 shadow-sm me-1 d-inline-flex align-items-center justify-content-center" style="width:65px; height:65px;">
                            <i class="bi bi-mortarboard-fill fs-2"></i>
                        </div>
                    <?php endif; ?>
                    <div class="text-center text-sm-start">
                        <h4 class="fw-bold mb-0 text-primary" style="letter-spacing: 1px;"><?= htmlspecialchars(strtoupper($schoolName)) ?></h4>
                        <small class="text-muted"><?= htmlspecialchars($alamat) ?></small>
                    </div>
                </div>

                <hr class="border-2 border-warning opacity-50 my-3">

                <!-- Certificate Title Ribbon -->
                <div class="py-2">
                    <span class="badge bg-warning text-dark px-4 py-2 fs-6 rounded-pill shadow-xs" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; color:#ffffff !important;">
                        <i class="bi bi-award-fill me-1.5"></i> SERTIFIKAT PENGHARGAAN KELULUSAN DIGITAL
                    </span>
                </div>

                <!-- Recipient Main Content -->
                <p class="text-muted mb-1 fs-6 mt-3 fw-medium">Dengan bangga diberikan kepada:</p>
                <h1 class="recipient-name display-5 fw-extrabold my-2 text-dark"><?= htmlspecialchars($siswa['nama_lengkap'] ?? 'Nama Siswa') ?></h1>
                
                <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap mb-4">
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1.5 fw-semibold" style="font-size:0.8rem;">
                        <i class="bi bi-card-heading text-primary me-1"></i>NIS: <?= htmlspecialchars($siswa['nis'] ?? '-') ?> &nbsp;|&nbsp; NISN: <?= htmlspecialchars($siswa['nisn'] ?? '-') ?>
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-semibold" style="font-size:0.8rem;">
                        <i class="bi bi-house-door-fill me-1"></i><?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?> — <?= htmlspecialchars($siswa['nama_jurusan'] ?? '-') ?>
                    </span>
                </div>

                <div class="rounded-4 p-4 mx-auto mb-4 border" style="max-width: 650px; background: #f8fafc; border-color: #e2e8f0 !important;">
                    <p class="fs-6 text-muted mb-0 fst-italic leading-relaxed">
                        "Telah berhasil menyelesaikan seluruh Program Pembelajaran Digital E-Learning 
                        Semester Ganjil Tahun Pelajaran 2025/2026 dengan perolehan hasil yang memuaskan 
                        serta menunjukkan kedisiplinan dan semangat belajar yang luar biasa."
                    </p>
                </div>

                <!-- Real Database Performance Stats -->
                <div class="row g-3 mb-4 justify-content-center">
                    <div class="col-12 col-md-4">
                        <div class="p-3 stat-pill-box text-center">
                            <div class="fw-bold fs-5 text-primary mb-0.5"><?= htmlspecialchars($certStats['predikat'] ?? 'Belum Ada Data') ?></div>
                            <small class="text-muted fw-semibold" style="font-size:0.75rem;"><i class="bi bi-star-fill text-warning me-1"></i>Predikat Hasil Belajar</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 stat-pill-box text-center">
                            <div class="fw-bold fs-5 text-success mb-0.5"><?= htmlspecialchars($certStats['presensi_log'] ?? 'Belum Ada Data') ?></div>
                            <small class="text-muted fw-semibold" style="font-size:0.75rem;"><i class="bi bi-calendar-check-fill text-success me-1"></i>Kehadiran KBM Real</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 stat-pill-box text-center">
                            <div class="fw-bold fs-5 text-warning-emphasis mb-0.5"><?= htmlspecialchars($certStats['evaluasi_lms'] ?? 'Belum Ada Nilai') ?></div>
                            <small class="text-muted fw-semibold" style="font-size:0.75rem;"><i class="bi bi-trophy-fill text-warning me-1"></i>Rata-Rata Evaluasi LMS</small>
                        </div>
                    </div>
                </div>

                <!-- Nomor Sertifikat & QR Code Verification Section -->
                <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mt-4 pt-3 border-top">
                    <div class="text-center text-sm-start">
                        <small class="text-muted d-block" style="font-size:0.75rem;">Nomor Registrasi Sertifikat:</small>
                        <code class="fs-6 text-primary fw-bold">SMKMH/SERT/2025/<?= str_pad($siswa['id'] ?? 1, 4, '0', STR_PAD_LEFT) ?></code>
                        <small class="text-muted d-block mt-1" style="font-size:0.73rem;"><i class="bi bi-calendar-check me-1"></i>Diterbitkan: <?= date('d F Y') ?></small>
                    </div>

                    <div class="text-center my-2 my-sm-0">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=85x85&data=SMKMH-CERT-<?= urlencode($siswa['nisn'] ?? 'UNKNOWN') ?>&color=0056d3&bgcolor=ffffff"
                             alt="QR Verifikasi" width="82" height="82" class="rounded-3 border border-2 border-primary p-1 shadow-xs">
                        <small class="text-muted d-block text-center mt-1" style="font-size:.68rem;">Scan Verifikasi QR</small>
                    </div>

                    <div class="text-center" style="min-width: 180px;">
                        <small class="text-muted d-block mb-4" style="font-size:0.75rem;">Cicalengka, <?= date('d F Y') ?></small>
                        <div class="fw-bold text-dark border-bottom border-dark pb-1 mb-1"><?= htmlspecialchars($kepalaSekolah) ?></div>
                        <small class="text-muted" style="font-size:0.75rem;">Kepala Sekolah Pengesah</small>
                    </div>
                </div>

            </div><!-- end sertifikat paper frame -->
        </div>
    </div>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
