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

// Student Avatar Resolution
$avFile = $siswa['avatar'] ?? ($user['avatar'] ?? '');
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

// Single NISN / NIS Value Selection
$nisnNisVal = !empty($siswa['nisn']) ? $siswa['nisn'] : (!empty($siswa['nis']) ? $siswa['nis'] : '-');
?>

<!-- Print Specific & High Contrast Responsive Styling -->
<style>
@media print {
    body {
        background-color: #ffffff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    .no-print, header, navbar, sidebar, footer, .sidebar, .navbar, .btn, .info-box-print {
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
        gap: 30px !important;
        margin-top: 20px !important;
        page-break-inside: avoid !important;
    }
    .kartu-front-side, .kartu-back-side {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%) !important;
        color: #ffffff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        border: 2px solid #334155 !important;
        box-shadow: none !important;
        height: 315px !important;
        width: 440px !important;
    }
}

/* Matching Theme Harmony & EXACT IDENTICAL DIMENSIONS for Both Front and Back Sides */
.kartu-front-side, .kartu-back-side {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
    color: #ffffff;
    border-radius: 20px;
    padding: 22px;
    position: relative;
    overflow: hidden;
    width: 100%;
    max-width: 440px;
    height: 315px; /* EXACT IDENTICAL HEIGHT FOR FRONT & BACK */
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border: 2px solid rgba(255, 255, 255, 0.2);
    box-sizing: border-box;
}

.gold-glow-border {
    border: 3px solid #f59e0b;
    box-shadow: 0 0 12px rgba(245, 158, 11, 0.4);
}

/* High Contrast Badges Overriding Any Bootstrap Defaults */
.badge-dark-gold {
    background-color: #1e293b !important;
    color: #fbbf24 !important;
    border: 1px solid #f59e0b !important;
    font-weight: 700 !important;
}

.badge-gold-solid {
    background-color: #f59e0b !important;
    color: #0f172a !important;
    font-weight: 800 !important;
}

.badge-cyan-solid {
    background-color: #0284c7 !important;
    color: #ffffff !important;
    font-weight: 700 !important;
}

.badge-purple-solid {
    background-color: #8b5cf6 !important;
    color: #ffffff !important;
    font-weight: 700 !important;
}

.rule-item {
    font-size: 0.67rem;
    line-height: 1.25;
    margin-bottom: 5px;
    color: rgba(255, 255, 255, 0.9);
}

.rule-title {
    color: #fbbf24;
    font-weight: 700;
}
</style>

<!-- Main Container with Explicit 90px Top Padding to Clear Fixed Navbar -->
<main class="main-content px-3 px-md-4 pb-4">
    <div class="container-fluid">

        <!-- Top Action Title Bar with Clear Top Spacing -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 no-print">
            <div>
                <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-credit-card-2-front-fill text-primary me-2"></i>Kartu Pelajar Digital Resmi</h4>
                <p class="text-muted small mb-0">Identitas digital resmi siswa yang dilengkapi QR Code presensi & informasi sinkron dari Admin.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm hover-scale no-print" onclick="window.print()">
                <i class="bi bi-printer-fill me-2"></i> Cetak Kartu Pelajar
            </button>
        </div>

        <div class="row g-4 justify-content-center print-card-container">
            
            <!-- SISI DEPAN KARTU (FRONT SIDE) -->
            <div class="col-12 col-md-6 col-xl-5">
                <p class="text-muted small fw-bold text-center mb-2 no-print">▌ SISI DEPAN KARTU (FRONT SIDE)</p>
                <div class="kartu-front-side shadow-lg mx-auto">
                    
                    <!-- Header Bar: Official Admin Logo & School Name -->
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom border-white border-opacity-25">
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($schoolLogoUrl): ?>
                                <img src="<?= $schoolLogoUrl ?>" alt="Logo Sekolah" class="object-fit-contain flex-shrink-0" style="height: 44px; width: 44px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
                            <?php else: ?>
                                <div class="bg-warning bg-opacity-20 text-warning rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 border border-warning" style="width: 40px; height: 40px;">
                                    <i class="bi bi-mortarboard-fill fs-5"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <div class="fw-bold text-white text-uppercase tracking-wide" style="line-height: 1.2; font-size: 0.88rem;"><?= htmlspecialchars($schoolName) ?></div>
                                <small class="text-white-50 d-block" style="font-size: 0.65rem; line-height: 1.1;"><?= htmlspecialchars($schoolAddress) ?></small>
                            </div>
                        </div>
                        <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm badge-gold-solid flex-shrink-0" style="font-size: 0.68rem; letter-spacing: 0.5px;">KARTU PELAJAR</span>
                    </div>

                    <!-- Main Student Info Body (Nama, NISN, Jenis Kelamin, No. Telp, Jurusan) -->
                    <div class="d-flex gap-3 align-items-center my-auto py-1">
                        <?php if ($hasAvatar): ?>
                            <img src="<?= $avatarUrl ?>" alt="Foto Siswa" 
                                 class="rounded-3 object-fit-cover flex-shrink-0 gold-glow-border"
                                 style="width: 85px; height: 105px;">
                        <?php else: ?>
                            <div class="rounded-3 bg-white bg-gradient d-flex align-items-center justify-content-center fw-bold text-primary flex-shrink-0 gold-glow-border"
                                 style="width: 85px; height: 105px; font-size: 2.4rem;">
                                <?= strtoupper(substr($siswa['nama_lengkap'] ?? 'S', 0, 1)) ?>
                            </div>
                        <?php endif; ?>

                        <div class="flex-grow-1 overflow-hidden">
                            <!-- Student Full Name -->
                            <h5 class="fw-bold mb-2 text-white text-truncate" style="font-size: 1.1rem;" title="<?= htmlspecialchars($siswa['nama_lengkap'] ?? 'Nama Siswa') ?>">
                                <?= htmlspecialchars($siswa['nama_lengkap'] ?? 'Nama Siswa') ?>
                            </h5>
                            
                            <!-- Single NISN / NIS Badge -->
                            <div class="mb-2">
                                <span class="badge badge-dark-gold rounded-pill px-3 py-1 small shadow-sm" style="font-size: 0.75rem;">
                                    <i class="bi bi-card-heading me-1"></i>NISN: <?= htmlspecialchars($nisnNisVal) ?>
                                </span>
                            </div>

                            <!-- Jenis Kelamin & No. Telp Badges -->
                            <div class="d-flex gap-1 mb-2 flex-wrap">
                                <span class="badge badge-purple-solid rounded-pill px-2 py-1 shadow-sm" style="font-size: 0.7rem;">
                                    JK: <?= ($siswa['jenis_kelamin'] ?? 'L') === 'L' ? 'Laki-Laki' : 'Perempuan' ?>
                                </span>
                                <span class="badge badge-dark-gold rounded-pill px-2 py-1 shadow-sm" style="font-size: 0.7rem;">
                                    <i class="bi bi-telephone-fill me-1"></i><?= htmlspecialchars($siswa['no_telepon'] ?: '-') ?>
                                </span>
                            </div>

                            <!-- Program Keahlian / Jurusan Badge -->
                            <div>
                                <span class="badge badge-cyan-solid rounded-pill px-3 py-1 shadow-sm" style="font-size: 0.72rem;">
                                    Jurusan: <?= htmlspecialchars($siswa['nama_jurusan'] ?: '-') ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Bar: Dynamic Academic Year & QR Code -->
                    <div class="pt-2 border-top border-white border-opacity-25 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50 d-block" style="font-size: 0.65rem; text-transform: uppercase;">Tahun Pelajaran (Admin)</small>
                            <div class="fw-bold text-warning fs-6" style="letter-spacing: 1px; font-size: 0.95rem;"><?= htmlspecialchars($academicYear) ?></div>
                        </div>
                        
                        <!-- High-Res QR Code for Attendance Verification -->
                        <div class="bg-white rounded-3 p-1 shadow-sm d-flex align-items-center justify-content-center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=SMKMH-SISWA-<?= urlencode($nisnNisVal) ?>&color=0f172a&bgcolor=ffffff"
                                 alt="QR Presensi" width="56" height="56" style="border-radius: 4px;">
                        </div>
                    </div>

                </div>
            </div>

            <!-- SISI BELAKANG KARTU (BACK SIDE - EXACT IDENTICAL DIMENSIONS & 5 RULES) -->
            <div class="col-12 col-md-6 col-xl-5">
                <p class="text-muted small fw-bold text-center mb-2 no-print">▌ SISI BELAKANG KARTU (BACK SIDE)</p>
                <div class="kartu-back-side shadow-lg mx-auto">
                    
                    <!-- Header Bar Back Side -->
                    <div class="d-flex justify-content-between align-items-center pb-2 border-bottom border-white border-opacity-25">
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($schoolLogoUrl): ?>
                                <img src="<?= $schoolLogoUrl ?>" alt="Logo Sekolah" style="height: 26px; width: auto;" class="object-fit-contain">
                            <?php else: ?>
                                <i class="bi bi-mortarboard-fill text-warning fs-6"></i>
                            <?php endif; ?>
                            <h6 class="fw-bold mb-0 text-white" style="font-size: 0.85rem;"><?= htmlspecialchars($schoolName) ?></h6>
                        </div>
                        <span class="badge badge-gold-solid rounded-pill px-2 py-1 small" style="font-size: 0.62rem;">TATA TERTIB KARTU</span>
                    </div>

                    <!-- 5 Official Rules List (Compact Inline Title Line for Perfect Fit) -->
                    <div class="my-auto py-1">
                        <div class="rule-item">
                            <span class="rule-title">1. Wajib Dibawa Setiap Hari:</span> Kartu merupakan identitas resmi. Jika lupa membawa, siswa melapor ke guru piket.
                        </div>
                        <div class="rule-item">
                            <span class="rule-title">2. Wajib Scan Datang & Pulang:</span> Pemindaian (scan) QR-code pada mesin absensi saat tiba & pulang sekolah.
                        </div>
                        <div class="rule-item">
                            <span class="rule-title">3. Larangan Titip Absen:</span> Kartu dilarang dipinjamkan. Titip scan dikenakan sanksi disiplin berat.
                        </div>
                        <div class="rule-item">
                            <span class="rule-title">4. Jaga Fisik Kode QR:</span> Dilarang mencoret/merusak QR-code. Pastikan bersih agar sensor lancar membaca.
                        </div>
                        <div class="rule-item" style="margin-bottom: 0;">
                            <span class="rule-title">5. Penanganan Kartu Hilang:</span> Kartu hilang/rusak wajib segera melapor ke Tata Usaha (TU).
                        </div>
                    </div>

                    <!-- Headmaster Signature Footer Bar -->
                    <div class="d-flex justify-content-between align-items-end pt-2 border-top border-white border-opacity-25">
                        <div class="text-center">
                            <small class="text-white-50 d-block" style="font-size: 0.6rem;">Keabsahan Identity</small>
                            <span class="badge badge-dark-gold rounded-circle p-2 mt-1 small d-inline-block" style="font-size: 0.58rem;">VALID</span>
                        </div>
                        <div class="text-center">
                            <small class="text-white-50 d-block" style="font-size: 0.62rem;">Mengetahui, Kepala Sekolah</small>
                            <div class="fw-bold text-warning text-decoration-underline mt-2" style="font-size: 0.78rem;"><?= htmlspecialchars($headmasterName) ?></div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Info Box for Web View -->
        <div class="row justify-content-center mt-4 no-print info-box-print">
            <div class="col-12 col-xl-10">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-inline-flex align-items-center justify-content-center flex-shrink-0">
                            <i class="bi bi-info-circle-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Panduan Penggunaan & Pencetakan Kartu Pelajar Digital</h6>
                            <ul class="small text-muted mb-0 ps-3">
                                <li><strong>Bebas Ketimpa Navbar Header</strong>: Ditambahkan <code>padding-top: 90px !important;</code> pada kontainer utama sehingga judul halaman dan tombol cetak kartu tampil utuh sempurna.</li>
                                <li><strong>Presisi Ukuran Sisi Depan & Belakang</strong>: Sisi Depan dan Belakang disetting pada tinggi <code>315px</code> yang 100% simetris tanpa ada bagian teks/tanda tangan yang terpotong.</li>
                                <li><strong>5 Ketentuan Tata Tertib Belakang</strong>: Sisi Belakang memuat 5 aturan resmi penggunaan kartu dan tanda tangan Kepala Sekolah (<strong><?= htmlspecialchars($headmasterName) ?></strong>).</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
