<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$user = AuthHelper::user();

// Load Dynamic Settings from Admin (Logo, School Name, Address, Academic Year, Headmaster)
$appSettings = [];
$settingsPath = ROOT_PATH . 'config/settings.json';
if (file_exists($settingsPath)) {
    $appSettings = json_decode(file_get_contents($settingsPath), true) ?: [];
} else {
    try {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $appSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    } catch (Exception $e) {}
}

$schoolName = !empty($appSettings['nama_sekolah']) ? $appSettings['nama_sekolah'] : 'SMK Muthia Harapan Cicalengka';
$schoolAddress = !empty($appSettings['alamat']) ? $appSettings['alamat'] : 'Jalan Babakan Peuteuy No. 300, Cicalengka, Kab. Bandung';
$academicYear = !empty($appSettings['tahun_ajaran']) ? $appSettings['tahun_ajaran'] : '2026/2027';
$headmasterName = !empty($appSettings['kepala_sekolah']) ? $appSettings['kepala_sekolah'] : 'H. ASEP SAEPULLOH, S. Ag';

// Resolve Official School Logo from Admin Settings
$rawLogo = $appSettings['logo'] ?? '';
$schoolLogoUrl = null;
if (!empty($rawLogo)) {
    $possibleLogoPaths = [
        'assets/uploads/logo/' . $rawLogo,
        'assets/uploads/' . $rawLogo,
        'assets/images/' . $rawLogo
    ];
    foreach ($possibleLogoPaths as $lp) {
        if (file_exists(ROOT_PATH . $lp)) {
            $schoolLogoUrl = BASE_URL . $lp;
            break;
        }
    }
}

// Teacher Avatar Resolution
$avFile = $guru['avatar'] ?? ($user['avatar'] ?? '');
$hasAvatar = false;
$avatarUrl = '';
if (!empty($avFile) && $avFile !== 'default_avatar.png') {
    $possibleAvatarPaths = [
        'assets/uploads/profile/' . $avFile,
        'assets/uploads/avatar/' . $avFile,
        'assets/uploads/' . $avFile
    ];
    foreach ($possibleAvatarPaths as $ap) {
        if (file_exists(ROOT_PATH . $ap)) {
            $hasAvatar = true;
            $avatarUrl = BASE_URL . $ap;
            break;
        }
    }
}

$nipVal = !empty($guru['nip']) ? $guru['nip'] : ('G' . date('Ym') . str_pad($guru['id'] ?? 1, 3, '0', STR_PAD_LEFT));
$qrData = "SMKMH-GURU-" . $nipVal;
$qrCodeApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qrData);
?>

<style>
@media print {
    @page {
        size: A4 landscape;
        margin: 10mm;
    }
    body {
        background-color: #ffffff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    .no-print, header, navbar, sidebar, footer, .sidebar, .navbar, .btn, .info-box-print, .alert {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        margin-top: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .print-card-container {
        display: flex !important;
        flex-direction: row !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 24px !important;
        margin-top: 15px !important;
        page-break-inside: avoid !important;
    }
    .kartu-front-side, .kartu-back-side {
        background: linear-gradient(135deg, #064e3b 0%, #022c22 50%, #0f172a 100%) !important;
        color: #ffffff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        border: 2px solid #059669 !important;
        box-shadow: none !important;
        height: 315px !important;
        width: 480px !important;
        page-break-inside: avoid !important;
    }
}

.kartu-front-side, .kartu-back-side {
    width: 100%;
    max-width: 480px;
    height: 315px;
    border-radius: 18px;
    background: linear-gradient(135deg, #064e3b 0%, #022c22 50%, #0f172a 100%);
    color: #ffffff;
    position: relative;
    overflow: hidden;
    border: 2px solid #059669;
    box-shadow: 0 15px 35px rgba(6, 78, 59, 0.35);
    transition: all 0.3s ease;
    padding: 14px 16px !important;
}

.kartu-front-side:hover, .kartu-back-side:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(6, 78, 59, 0.45);
}

.kartu-bg-pattern {
    position: absolute;
    top: -50px;
    right: -50px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.gold-badge {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 0.62rem;
    letter-spacing: 0.4px;
    padding: 3px 9px;
    border-radius: 20px;
    text-transform: uppercase;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);
    white-space: nowrap;
}

.card-title-banner {
    background: rgba(16, 185, 129, 0.2);
    border: 1px solid rgba(16, 185, 129, 0.4);
    border-radius: 6px;
    letter-spacing: 1.5px;
    font-size: 0.68rem;
    color: #6ee7b7;
}

.teacher-photo-frame {
    width: 86px;
    height: 104px;
    border-radius: 12px;
    border: 3px solid #10b981;
    object-fit: cover;
    background: #0f172a;
    box-shadow: 0 6px 16px rgba(0,0,0,0.4);
}

.qr-code-box {
    background: #ffffff;
    padding: 5px;
    border-radius: 12px;
    border: 2px solid #10b981;
    box-shadow: 0 6px 16px rgba(0,0,0,0.3);
}

.watermark-logo {
    position: absolute;
    bottom: -20px;
    right: -20px;
    opacity: 0.08;
    width: 220px;
    pointer-events: none;
}
</style>

<main class="main-content px-3 px-md-4" style="padding-top: 90px !important;">
    <div class="container-fluid">
        
        <!-- Header & Action Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 no-print">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-person-badge-fill text-success me-2"></i>Kartu Tenaga Pendidik Digital (Kartu Guru)</h4>
                <p class="text-muted small mb-0">Kartu Identitas Resmi Tenaga Pendidik SMK Muthia Harapan Cicalengka dilengkapi Kode QR Presensi Digital.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button onclick="downloadKartuPDF()" class="btn btn-danger px-4 py-2 rounded-3 fw-bold shadow-sm">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Unduh PDF Kartu
                </button>
                <button onclick="window.print()" class="btn btn-success px-4 py-2 rounded-3 fw-bold shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Kartu Guru
                </button>
                <a href="<?= $qrCodeApiUrl ?>" download="QR_Guru_<?= $nipVal ?>.png" target="_blank" class="btn btn-outline-success px-3 py-2 rounded-3 fw-bold">
                    <i class="bi bi-download me-1"></i> Unduh QR Code
                </a>
            </div>
        </div>

        <!-- Information Callout Box -->
        <div class="alert alert-success border-0 rounded-4 shadow-sm p-3 mb-4 no-print d-flex align-items-center gap-3">
            <div class="fs-2 text-success"><i class="bi bi-qr-code-scan"></i></div>
            <div>
                <strong class="d-block text-dark">Informasi Presensi Guru Digital:</strong>
                <small class="text-muted">Gunakan Kode QR pada Kartu Guru ini untuk melakukan presensi kehadiran harian tenaga pendidik pada modul **Scan QR Code Hadir**.</small>
            </div>
        </div>

        <!-- Printable Cards Container -->
        <div id="printableCardArea" class="print-card-container row g-4 justify-content-center mb-5">
            
            <!-- SISI DEPAN (FRONT SIDE) -->
            <div class="col-12 col-lg-6 d-flex justify-content-center">
                <div class="kartu-front-side d-flex flex-column justify-content-between">
                    <div class="kartu-bg-pattern"></div>
                    
                    <!-- Header Kop Sekolah -->
                    <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-1.5" style="border-color: rgba(255,255,255,0.18) !important;">
                        <div class="d-flex align-items-center gap-2 overflow-hidden me-2" style="max-width: 76%;">
                            <?php if ($schoolLogoUrl): ?>
                                <img src="<?= $schoolLogoUrl ?>" alt="Logo Sekolah" style="height: 36px; width: auto; object-fit: contain; flex-shrink: 0;">
                            <?php else: ?>
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                    <i class="bi bi-building fs-6 text-white"></i>
                                </div>
                            <?php endif; ?>
                            <div style="line-height: 1.15;">
                                <h6 class="fw-bold mb-0 text-white text-truncate" style="font-size: 0.8rem; letter-spacing: 0.2px;"><?= htmlspecialchars($schoolName) ?></h6>
                                <small class="text-white-50 d-block text-truncate" style="font-size: 0.6rem;"><?= htmlspecialchars($schoolAddress) ?></small>
                            </div>
                        </div>
                        <span class="gold-badge flex-shrink-0">GURU / GTK</span>
                    </div>

                    <!-- Judul Kartu Banner -->
                    <div class="card-title-banner text-center py-0.5 mb-1.5 fw-bold text-uppercase">
                        <i class="bi bi-person-badge-fill me-1"></i>KARTU TENAGA PENDIDIK
                    </div>

                    <!-- Body Content: Photo & Info -->
                    <div class="row g-2 align-items-center my-auto">
                        <div class="col-auto">
                            <?php if ($hasAvatar): ?>
                                <img src="<?= $avatarUrl ?>" alt="Foto Guru" class="teacher-photo-frame">
                            <?php else: ?>
                                <div class="teacher-photo-frame d-flex align-items-center justify-content-center text-white-50">
                                    <i class="bi bi-person-fill display-4"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col ps-2">
                            <h6 class="fw-bold text-white mb-0" style="font-size: 0.92rem; line-height: 1.25;"><?= htmlspecialchars($guru['nama_lengkap'] ?? $user['full_name']) ?></h6>
                            <p class="text-success mb-1 fw-semibold" style="font-size: 0.7rem;">Tenaga Pendidik / Guru Pengajar</p>
                            
                            <table class="text-white-50 small w-100" style="font-size: 0.68rem; line-height: 1.4;">
                                <tr>
                                    <td style="width: 70px;">NIP/NUPTK</td>
                                    <td>: <strong class="text-white"><code><?= htmlspecialchars($nipVal) ?></code></strong></td>
                                </tr>
                                <tr>
                                    <td>Gender</td>
                                    <td>: <span class="text-white"><?= ($guru['jenis_kelamin'] ?? 'L') === 'L' ? 'Laki-Laki' : 'Perempuan' ?></span></td>
                                </tr>
                                <tr>
                                    <td>Status GTK</td>
                                    <td>: <span class="badge bg-success text-white py-0 px-2" style="font-size: 0.6rem;">Aktif Mengajar</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-auto text-end">
                            <div class="qr-code-box">
                                <img src="<?= $qrCodeApiUrl ?>" alt="QR Code Guru" style="width: 72px; height: 72px; display: block;">
                            </div>
                            <small class="text-white-50 d-block mt-1 text-center" style="font-size: 0.58rem; letter-spacing: 0.3px;">ID PRESENSI</small>
                        </div>
                    </div>

                    <!-- Footer Side -->
                    <div class="d-flex justify-content-between align-items-center border-top pt-1 text-white-50" style="border-color: rgba(255,255,255,0.18) !important; font-size: 0.62rem;">
                        <span>Tahun Ajaran <?= htmlspecialchars($academicYear) ?></span>
                        <span class="fw-bold text-success"><i class="bi bi-patch-check-fill me-1"></i>KARTU RESMI DIGITAL</span>
                    </div>

                    <img src="<?= $schoolLogoUrl ?: BASE_URL . 'assets/images/logo.png' ?>" class="watermark-logo" alt="Watermark">
                </div>
            </div>

            <!-- SISI BELAKANG (BACK SIDE) -->
            <div class="col-12 col-lg-6 d-flex justify-content-center">
                <div class="kartu-back-side d-flex flex-column justify-content-between">
                    <div class="kartu-bg-pattern"></div>

                    <!-- Header Kop Belakang -->
                    <div class="card-title-banner text-center py-0.5 mb-1.5 fw-bold text-uppercase">
                        <i class="bi bi-shield-check me-1"></i>KETENTUAN KARTU TENAGA PENDIDIK
                    </div>

                    <!-- List Ketentuan -->
                    <ol class="text-white-50 small my-auto ps-3 mb-0" style="font-size: 0.65rem; line-height: 1.38;">
                        <li class="mb-1">Kartu ini merupakan bukti identitas resmi Tenaga Pendidik SMK Muthia Harapan Cicalengka.</li>
                        <li class="mb-1">Gunakan Kode QR kartu ini untuk pemindaian presensi harian pada scanner sekolah.</li>
                        <li class="mb-1">Dilarang menyerahkan atau meminjamkan Kode QR presensi kepada orang lain.</li>
                        <li class="mb-1">Apabila terjadi kendala pemindaian, silakan hubungi bagian Administrasi/TIM IT Sekolah.</li>
                        <li class="mb-0">Kartu ini berlaku selama Guru/Tenaga Pendidik aktif mengajar pada TA <?= htmlspecialchars($academicYear) ?>.</li>
                    </ol>

                    <!-- Tanda Tangan Kepala Sekolah -->
                    <div class="d-flex justify-content-between align-items-end border-top pt-1.5 mt-1" style="border-color: rgba(255,255,255,0.18) !important;">
                        <div class="text-start text-white-50" style="font-size: 0.58rem; line-height: 1.3;">
                            <span>Cicalengka, Kab. Bandung</span><br>
                            <span>Diterbitkan Oleh: Kepala Sekolah</span>
                        </div>
                        <div class="text-end text-white" style="line-height: 1.2;">
                            <small class="text-white-50 d-block" style="font-size: 0.58rem;">Mengetahui,</small>
                            <strong class="d-block text-white border-bottom border-white pb-0.5" style="font-size: 0.7rem; margin-top: 10px; margin-bottom: 2px;"><?= htmlspecialchars($headmasterName) ?></strong>
                            <small class="text-white-50 d-block" style="font-size: 0.58rem;">NIP. 19750812 200212 1 003</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</main>

<!-- html2pdf JS Library for Export PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadKartuPDF() {
    const element = document.getElementById('printableCardArea');
    const opt = {
        margin:       [10, 10, 10, 10],
        filename:     'Kartu_Guru_<?= preg_replace("/[^a-zA-Z0-9]/", "_", $guru['nama_lengkap'] ?? $user['full_name']) ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true, logging: false },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
    };

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Mengunduh Kartu PDF...',
            text: 'Mohon tunggu sebentar, sistem sedang memproses PDF...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
    }

    html2pdf().set(opt).from(element).save().then(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Unduh PDF!',
                text: 'Kartu Guru Digital berhasil disimpan sebagai file PDF.',
                timer: 2500,
                showConfirmButton: false
            });
        }
    }).catch(() => {
        window.print();
    });
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
