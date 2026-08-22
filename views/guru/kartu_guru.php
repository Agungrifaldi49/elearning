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

<!-- Google Fonts: Plus Jakarta Sans for Ultra-Modern Typography -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* Modern Font Family & Print Precision Styling */
.kartu-wrapper-container * {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

@media print {
    @page {
        size: A4 landscape;
        margin: 8mm;
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
        gap: 20px !important;
        margin-top: 10px !important;
        page-break-inside: avoid !important;
    }
    .kartu-front-side, .kartu-back-side {
        background: linear-gradient(135deg, #022c22 0%, #064e3b 50%, #0f172a 100%) !important;
        color: #ffffff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        border: 2px solid #10b981 !important;
        box-shadow: none !important;
        height: 315px !important;
        width: 490px !important;
        page-break-inside: avoid !important;
    }
}

.kartu-front-side, .kartu-back-side {
    width: 100%;
    max-width: 490px;
    height: 315px;
    border-radius: 20px;
    background: linear-gradient(135deg, #022c22 0%, #064e3b 45%, #0f172a 100%);
    color: #ffffff;
    position: relative;
    overflow: hidden;
    border: 2px solid rgba(16, 185, 129, 0.6);
    box-shadow: 0 20px 45px rgba(2, 44, 34, 0.45);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 16px 18px !important;
}

.kartu-front-side:hover, .kartu-back-side:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(6, 78, 59, 0.55);
    border-color: #34d399;
}

.kartu-bg-pattern {
    position: absolute;
    top: -60px;
    right: -60px;
    width: 240px;
    height: 240px;
    background: radial-gradient(circle, rgba(52, 211, 153, 0.22) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.kartu-grid-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px);
    background-size: 14px 14px;
    pointer-events: none;
}

.gold-badge {
    background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
    color: #0f172a;
    font-weight: 800;
    font-size: 0.62rem;
    letter-spacing: 0.6px;
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    box-shadow: 0 3px 10px rgba(251, 191, 36, 0.45);
    white-space: nowrap;
}

.card-title-banner {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(52, 211, 153, 0.35);
    border-radius: 8px;
    letter-spacing: 2px;
    font-size: 0.68rem;
    color: #6ee7b7;
    font-weight: 800;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15);
}

.teacher-photo-frame {
    width: 88px;
    height: 106px;
    border-radius: 14px;
    border: 3px solid #10b981;
    object-fit: cover;
    background: #0f172a;
    box-shadow: 0 8px 20px rgba(0,0,0,0.45);
}

.qr-code-box {
    background: #ffffff;
    padding: 5px;
    border-radius: 12px;
    border: 2.5px solid #10b981;
    box-shadow: 0 8px 20px rgba(0,0,0,0.35);
}

.watermark-logo {
    position: absolute;
    bottom: -25px;
    right: -25px;
    opacity: 0.07;
    width: 230px;
    pointer-events: none;
}

.stamp-seal-badge {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    border: 2px dashed rgba(52, 211, 153, 0.5);
    background: rgba(16, 185, 129, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.55rem;
    color: #6ee7b7;
    font-weight: 700;
    text-align: center;
    line-height: 1.1;
    text-transform: uppercase;
}
</style>

<main class="main-content px-3 px-md-4 kartu-wrapper-container" style="padding-top: 95px !important;">
    <div class="container-fluid">
        
        <!-- Header & Action Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 no-print">
            <div>
                <h4 class="fw-extrabold mb-1 text-dark" style="letter-spacing: -0.5px;">
                    <i class="bi bi-person-badge-fill text-success me-2"></i>Kartu Tenaga Pendidik Digital (Kartu Guru)
                </h4>
                <p class="text-muted small mb-0">Kartu Identitas Resmi Tenaga Pendidik SMK Muthia Harapan Cicalengka dilengkapi Kode QR Presensi Digital.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button onclick="downloadKartuPDF()" class="btn btn-danger px-4 py-2.5 rounded-3 fw-bold shadow-sm d-flex align-items-center gap-1.5">
                    <i class="bi bi-file-earmark-pdf-fill fs-5"></i> Unduh PDF High-Res
                </button>
                <button onclick="window.print()" class="btn btn-success px-4 py-2.5 rounded-3 fw-bold shadow-sm d-flex align-items-center gap-1.5">
                    <i class="bi bi-printer-fill fs-5"></i> Cetak Kartu Physical
                </button>
                <a href="<?= $qrCodeApiUrl ?>" download="QR_Guru_<?= $nipVal ?>.png" target="_blank" class="btn btn-outline-success px-3 py-2.5 rounded-3 fw-bold d-flex align-items-center gap-1.5">
                    <i class="bi bi-download fs-5"></i> Unduh QR Code
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
                    <div class="kartu-grid-overlay"></div>
                    
                    <!-- Header Kop Sekolah -->
                    <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2" style="border-color: rgba(255,255,255,0.18) !important;">
                        <div class="d-flex align-items-center gap-2 overflow-hidden me-2" style="max-width: 76%;">
                            <?php if ($schoolLogoUrl): ?>
                                <img src="<?= $schoolLogoUrl ?>" alt="Logo Sekolah" style="height: 36px; width: auto; object-fit: contain; flex-shrink: 0;">
                            <?php else: ?>
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="bi bi-building fs-6 text-white"></i>
                                </div>
                            <?php endif; ?>
                            <div style="line-height: 1.15;">
                                <h6 class="fw-extrabold mb-0 text-white text-truncate" style="font-size: 0.82rem; letter-spacing: 0.3px;"><?= htmlspecialchars($schoolName) ?></h6>
                                <small class="text-white-50 d-block text-truncate" style="font-size: 0.6rem;"><?= htmlspecialchars($schoolAddress) ?></small>
                            </div>
                        </div>
                        <span class="gold-badge flex-shrink-0">GTK / PENDIDIK</span>
                    </div>

                    <!-- Judul Kartu Banner (Posisi Diturunkan Agak Kebawah & Lebih Seimbang) -->
                    <div class="card-title-banner text-center py-1 mt-1 mb-2.5 fw-bold text-uppercase">
                        <i class="bi bi-person-badge me-1"></i>KARTU TENAGA PENDIDIK DIGITAL
                    </div>

                    <!-- Body Content: Photo & Info -->
                    <div class="row g-2 align-items-center my-auto position-relative" style="z-index: 2;">
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
                            <h6 class="fw-extrabold text-white mb-0" style="font-size: 0.96rem; line-height: 1.25; letter-spacing: -0.2px;"><?= htmlspecialchars($guru['nama_lengkap'] ?? $user['full_name']) ?></h6>
                            <p class="text-emerald mb-1.5 fw-semibold" style="font-size: 0.72rem; color: #34d399;">
                                <i class="bi bi-check-circle-fill me-1"></i>Tenaga Pendidik / Guru Pengajar
                            </p>
                            
                            <table class="text-white-50 small w-100" style="font-size: 0.68rem; line-height: 1.45;">
                                <tr>
                                    <td style="width: 72px;">NIP/NUPTK</td>
                                    <td>: <strong class="text-white"><code class="px-1 py-0.5 rounded bg-dark border border-secondary text-warning" style="font-size:0.68rem;"><?= htmlspecialchars($nipVal) ?></code></strong></td>
                                </tr>
                                <tr>
                                    <td>Gender</td>
                                    <td>: <span class="text-white fw-medium"><?= ($guru['jenis_kelamin'] ?? 'L') === 'L' ? 'Laki-Laki' : 'Perempuan' ?></span></td>
                                </tr>
                                <tr>
                                    <td>Status GTK</td>
                                    <td>: <span class="badge bg-success text-white py-0.5 px-2" style="font-size: 0.62rem; font-weight: 700;">Aktif Mengajar</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-auto text-end">
                            <div class="qr-code-box">
                                <img src="<?= $qrCodeApiUrl ?>" alt="QR Code Guru" style="width: 72px; height: 72px; display: block;">
                            </div>
                            <small class="text-white-50 d-block mt-1 text-center fw-bold" style="font-size: 0.56rem; letter-spacing: 0.5px;">ID PRESENSI</small>
                        </div>
                    </div>

                    <!-- Footer Side -->
                    <div class="d-flex justify-content-between align-items-center border-top pt-1 text-white-50 position-relative" style="border-color: rgba(255,255,255,0.18) !important; font-size: 0.62rem; z-index: 2;">
                        <span>Tahun Ajaran <?= htmlspecialchars($academicYear) ?></span>
                        <span class="fw-extrabold text-emerald" style="color: #34d399;"><i class="bi bi-patch-check-fill me-1"></i>VERIFIED GTK DIGITAL CARD</span>
                    </div>

                    <img src="<?= $schoolLogoUrl ?: BASE_URL . 'assets/images/logo.png' ?>" class="watermark-logo" alt="Watermark">
                </div>
            </div>

            <!-- SISI BELAKANG (BACK SIDE) -->
            <div class="col-12 col-lg-6 d-flex justify-content-center">
                <div class="kartu-back-side d-flex flex-column justify-content-between">
                    <div class="kartu-bg-pattern"></div>
                    <div class="kartu-grid-overlay"></div>

                    <!-- Header Kop Belakang -->
                    <div class="card-title-banner text-center py-1 mb-2 fw-bold text-uppercase">
                        <i class="bi bi-shield-check me-1"></i>KETENTUAN PENGGUNAAN KARTU GURU
                    </div>

                    <!-- List Ketentuan (Tersusun Rapi Mengisi Celah Tanpa Celah Kosong) -->
                    <ul class="list-unstyled text-white-50 my-auto mb-0 position-relative" style="font-size: 0.66rem; line-height: 1.48; z-index: 2;">
                        <li class="mb-2 d-flex align-items-start gap-1.5">
                            <span class="badge rounded-circle px-1.5 py-0.5 fw-bold" style="font-size: 0.6rem; color:#34d399; background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.3);">1</span>
                            <span>Kartu ini merupakan bukti identitas resmi Tenaga Pendidik SMK Muthia Harapan Cicalengka.</span>
                        </li>
                        <li class="mb-2 d-flex align-items-start gap-1.5">
                            <span class="badge rounded-circle px-1.5 py-0.5 fw-bold" style="font-size: 0.6rem; color:#34d399; background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.3);">2</span>
                            <span>Gunakan Kode QR kartu ini untuk pemindaian presensi harian pada scanner sekolah.</span>
                        </li>
                        <li class="mb-2 d-flex align-items-start gap-1.5">
                            <span class="badge rounded-circle px-1.5 py-0.5 fw-bold" style="font-size: 0.6rem; color:#34d399; background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.3);">3</span>
                            <span>Dilarang menyerahkan atau meminjamkan Kode QR presensi kepada orang lain.</span>
                        </li>
                        <li class="mb-2 d-flex align-items-start gap-1.5">
                            <span class="badge rounded-circle px-1.5 py-0.5 fw-bold" style="font-size: 0.6rem; color:#34d399; background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.3);">4</span>
                            <span>Apabila terjadi kendala pemindaian, silakan hubungi bagian Administrasi/TIM IT Sekolah.</span>
                        </li>
                        <li class="mb-0 d-flex align-items-start gap-1.5">
                            <span class="badge rounded-circle px-1.5 py-0.5 fw-bold" style="font-size: 0.6rem; color:#34d399; background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.3);">5</span>
                            <span>Kartu berlaku selama Guru/GTK aktif mengajar pada TA <?= htmlspecialchars($academicYear) ?>.</span>
                        </li>
                    </ul>

                    <!-- Tanda Tangan Kepala Sekolah -->
                    <div class="d-flex justify-content-between align-items-center border-top pt-1.5 mt-2 position-relative" style="border-color: rgba(255,255,255,0.18) !important; z-index: 2;">
                        <div class="text-start text-white-50" style="font-size: 0.58rem; line-height: 1.3;">
                            <span>Cicalengka, Kab. Bandung</span><br>
                            <span>Diterbitkan Oleh: Kepala Sekolah</span>
                        </div>
                        
                        <!-- Circular Digital Seal Stamp -->
                        <div class="stamp-seal-badge mx-auto">
                            STEMPEL<br>RESMI
                        </div>

                        <div class="text-end text-white" style="line-height: 1.25;">
                            <small class="text-white-50 d-block" style="font-size: 0.58rem;">Mengetahui,</small>
                            <strong class="d-block text-white border-bottom border-white pb-0.5" style="font-size: 0.7rem; margin-top: 6px; margin-bottom: 2px;"><?= htmlspecialchars($headmasterName) ?></strong>
                            <small class="text-white-50 d-block" style="font-size: 0.58rem;">NIP. 19750812 200212 1 003</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</main>

<!-- html2pdf JS Library for High-Res Export PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadKartuPDF() {
    const element = document.getElementById('printableCardArea');
    const opt = {
        margin:       [8, 8, 8, 8],
        filename:     'Kartu_Guru_<?= preg_replace("/[^a-zA-Z0-9]/", "_", $guru['nama_lengkap'] ?? $user['full_name']) ?>.pdf',
        image:        { type: 'jpeg', quality: 1.0 },
        html2canvas:  { scale: 3, useCORS: true, allowTaint: true, logging: false },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
    };

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Mengunduh PDF High-Res...',
            text: 'Mohon tunggu sebentar, sedang memproses dokumen PDF 300 DPI...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
    }

    html2pdf().set(opt).from(element).save().then(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Unduh PDF!',
                text: 'Kartu Guru Digital 100% presisi berhasil disimpan sebagai file PDF.',
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
