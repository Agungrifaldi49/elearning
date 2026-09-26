<?php 
require_once ROOT_PATH . 'views/layouts/header.php'; 

$rawLogo = $settings['logo'] ?? '';
$logoUrl = null;
if (!empty($rawLogo)) {
    if (strpos($rawLogo, 'assets/uploads/') === 0 || strpos($rawLogo, 'uploads/') === 0) {
        $logoUrl = BASE_URL . $rawLogo;
    } else {
        $logoUrl = BASE_URL . 'assets/uploads/logo/' . $rawLogo;
    }
}

$rawVideoUrl = $settings['landing_video_url'] ?? 'https://www.youtube.com/embed/dQw4w9WgXcQ';
if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $rawVideoUrl, $matches)) {
    $videoEmbedUrl = 'https://www.youtube.com/embed/' . $matches[1];
} else {
    $videoEmbedUrl = $rawVideoUrl;
}

$mapsUrl = !empty($settings['landing_maps_url']) ? $settings['landing_maps_url'] : 'https://maps.google.com/maps?q=Cicalengka&t=&z=13&ie=UTF8&iwloc=&output=embed';
$schoolName = Security::safeText($settings['nama_sekolah'] ?? 'SMK Muthia Harapan Cicalengka');
$misiContent = Security::safeHtml($settings['landing_misi_desc'] ?? 'Mengembangkan kurikulum industri & sertifikasi kompetensi keahlian.');
$visiContent = Security::safeHtml($settings['landing_visi_desc'] ?? 'Menjadi SMK Unggulan berstandar Nasional berbasis Teknologi & Imtaq.');

// Pengaturan Chat Cepat WhatsApp Landing Page
$waChatEnabled = !isset($settings['landing_wa_enabled']) || $settings['landing_wa_enabled'] === '1';
$rawWaPhone = !empty($settings['landing_wa_number']) ? $settings['landing_wa_number'] : ($settings['telepon'] ?? '082198765433');
$cleanWaPhone = preg_replace('/[^0-9]/', '', (string)$rawWaPhone);
if (strpos($cleanWaPhone, '0') === 0) {
    $cleanWaPhone = '62' . substr($cleanWaPhone, 1);
} elseif (strpos($cleanWaPhone, '8') === 0) {
    $cleanWaPhone = '62' . $cleanWaPhone;
}
if (empty($cleanWaPhone) || strlen($cleanWaPhone) < 8) {
    $cleanWaPhone = '6282198765433';
}

$waChatLabel = !empty($settings['landing_wa_label']) ? Security::safeText($settings['landing_wa_label']) : 'Butuh Bantuan? Chat Admin';
$waChatMsg = !empty($settings['landing_wa_text']) ? $settings['landing_wa_text'] : 'Halo Tim Bantuan E-Learning SMK Muthia Harapan, saya mengalami kendala teknis saat menggunakan website. Mohon bantuannya.';
$waChatUrl = 'https://wa.me/' . $cleanWaPhone . '?text=' . rawurlencode($waChatMsg);
?>

<style>
/* Header Navbar Responsive Custom Styling */
.landing-navbar-title {
    max-width: 580px;
    display: inline-block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
}

@media (max-width: 991.98px) {
    .landing-navbar-title {
        max-width: calc(100vw - 140px);
        font-size: 1.05rem !important;
    }
    #mainNavbar .navbar-collapse {
        background: linear-gradient(135deg, #0a58ca 0%, #073896 100%);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-radius: 20px;
        padding: 20px;
        margin-top: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    }
    #mainNavbar .nav-link {
        padding: 10px 16px !important;
        border-radius: 12px;
        transition: background 0.2s ease;
    }
    #mainNavbar .nav-link:hover,
    #mainNavbar .nav-link:focus {
        background: rgba(255, 255, 255, 0.15);
    }
    #mainNavbar .btn-warning {
        width: 100%;
        margin-top: 10px;
        padding-top: 12px;
        padding-bottom: 12px;
        text-align: center;
    }
}

@media (max-width: 575.98px) {
    .landing-navbar-title {
        max-width: calc(100vw - 110px);
        font-size: 0.95rem !important;
    }
}

/* Profil Sekolah, Visi Utama & Misi Presisi Text Justify Alignment */
.landing-profil-desc,
.landing-misi-content,
.landing-misi-content p,
.landing-misi-content li {
    text-align: justify !important;
    text-justify: inter-word !important;
    line-height: 1.75 !important;
}

.p-2\.5 {
    padding: 0.75rem !important;
}

/* Hero Feature Pills (CBT, Absensi, E-Modul, E-Rapor) */
.hero-feature-pill {
    background: #ffffff;
    padding: 12px 16px;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    gap: 12px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
}

.hero-feature-pill:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.14);
}

.hero-feature-icon {
    width: 34px;
    height: 34px;
    background: rgba(25, 135, 84, 0.12);
    color: #198754;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}

.hero-feature-text {
    font-size: 0.88rem;
    font-weight: 700;
    color: #0f172a !important;
    line-height: 1.3;
}

@media (max-width: 575.98px) {
    .hero-feature-pill {
        padding: 10px 12px;
        gap: 9px;
    }
    .hero-feature-icon {
        width: 28px;
        height: 28px;
        font-size: 0.95rem;
    }
    .hero-feature-text {
        font-size: 0.78rem;
    }
}

/* WhatsApp Floating Quick Chat Widget Styling */
.wa-floating-container {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 1045;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif;
}

.wa-floating-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    color: #ffffff !important;
    text-decoration: none !important;
    padding: 10px 20px 10px 14px;
    border-radius: 50px;
    box-shadow: 0 8px 24px rgba(37, 211, 102, 0.45), 0 4px 12px rgba(0, 0, 0, 0.12);
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    position: relative;
    cursor: pointer;
    border: 2px solid rgba(255, 255, 255, 0.35);
    user-select: none;
}

.wa-floating-btn:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 14px 30px rgba(37, 211, 102, 0.6), 0 6px 16px rgba(0, 0, 0, 0.18);
    color: #ffffff !important;
}

.wa-btn-pulse {
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    border-radius: 50px;
    box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
    animation: waPulseAnim 2.2s infinite;
    pointer-events: none;
}

@keyframes waPulseAnim {
    0% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.75);
    }
    70% {
        box-shadow: 0 0 0 16px rgba(37, 211, 102, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
    }
}

.wa-icon-box {
    width: 38px;
    height: 38px;
    background: rgba(255, 255, 255, 0.22);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.wa-floating-btn:hover .wa-icon-box {
    transform: rotate(8deg) scale(1.1);
}

.wa-text-label {
    display: flex;
    flex-direction: column;
    text-align: left;
    line-height: 1.2;
}

.wa-text-title {
    font-weight: 700;
    font-size: 0.92rem;
    letter-spacing: -0.2px;
}

.wa-text-subtitle {
    font-size: 0.72rem;
    opacity: 0.9;
    font-weight: 500;
}

.wa-online-dot {
    width: 8px;
    height: 8px;
    background-color: #4ade80;
    border-radius: 50%;
    display: inline-block;
    margin-right: 4px;
    box-shadow: 0 0 6px #4ade80;
    animation: waDotBlink 2s infinite ease-in-out;
}

@keyframes waDotBlink {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.85); }
}

/* Chat Card Popup (Modern WhatsApp Box) */
.wa-chat-popup {
    width: 320px;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 18px 40px rgba(0, 0, 0, 0.2), 0 6px 16px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    margin-bottom: 14px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    transform-origin: bottom right;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    opacity: 0;
    visibility: hidden;
    transform: scale(0.85) translateY(20px);
    pointer-events: none;
}

.wa-chat-popup.show {
    opacity: 1;
    visibility: visible;
    transform: scale(1) translateY(0);
    pointer-events: auto;
}

.wa-popup-header {
    background: linear-gradient(135deg, #128C7E 0%, #075E54 100%);
    color: #ffffff;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.wa-popup-avatar {
    width: 38px;
    height: 38px;
    background: #25D366;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.wa-popup-body {
    padding: 16px;
    background: #efeae2;
    background-image: radial-gradient(#d1c7b7 0.75px, transparent 0.75px);
    background-size: 12px 12px;
}

.wa-chat-bubble {
    background: #ffffff;
    border-radius: 14px;
    border-top-left-radius: 3px;
    padding: 12px 14px;
    font-size: 0.84rem;
    color: #1e293b;
    line-height: 1.45;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
    position: relative;
}

.wa-chat-bubble::before {
    content: "";
    position: absolute;
    top: 0;
    left: -7px;
    width: 0;
    height: 0;
    border-top: 7px solid #ffffff;
    border-left: 7px solid transparent;
}

.wa-popup-footer {
    padding: 12px 16px;
    background: #ffffff;
    border-top: 1px solid #f1f5f9;
}

.wa-btn-close-popup {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.75);
    font-size: 1.1rem;
    line-height: 1;
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    transition: color 0.2s ease, background 0.2s ease;
}

.wa-btn-close-popup:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.15);
}

/* Responsive adjustments */
@media (max-width: 575.98px) {
    .wa-floating-container {
        bottom: 18px;
        right: 18px;
    }
    .wa-text-label {
        display: none;
    }
    .wa-floating-btn {
        padding: 0;
        border-radius: 50%;
        width: 54px;
        height: 54px;
        justify-content: center;
    }
    .wa-icon-box {
        width: 100%;
        height: 100%;
        background: transparent;
        font-size: 26px;
    }
    .wa-chat-popup {
        width: calc(100vw - 36px);
        max-width: 310px;
    }
}
</style>

<!-- Navbar Landing Page -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow-sm py-3" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#">
            <?php if ($logoUrl): ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" class="rounded-3 bg-white p-1 shadow-sm flex-shrink-0" style="height:36px; object-fit:contain;">
            <?php else: ?>
                <i class="bi bi-mortarboard-fill fs-3 text-warning flex-shrink-0"></i>
            <?php endif; ?>
            <span class="fs-5 tracking-tight font-heading text-white landing-navbar-title"><?= $schoolName ?></span>
        </a>
        <button class="navbar-toggler border-0 shadow-none p-2 rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#navPublic" aria-controls="navPublic" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navPublic">
            <ul class="navbar-nav ms-auto me-lg-3 gap-1 gap-lg-2 py-2 py-lg-0">
                <li class="nav-item"><a class="nav-link text-white fw-medium" href="#profil"><i class="bi bi-building d-lg-none me-2"></i>Profil</a></li>
                <li class="nav-item"><a class="nav-link text-white fw-medium" href="#fitur"><i class="bi bi-stars d-lg-none me-2"></i>Fitur LMS</a></li>
                <li class="nav-item"><a class="nav-link text-white fw-medium" href="#jurusan"><i class="bi bi-award d-lg-none me-2"></i>Jurusan</a></li>
                <li class="nav-item"><a class="nav-link text-white fw-medium" href="#guru"><i class="bi bi-people d-lg-none me-2"></i>Tenaga Pengajar</a></li>
                <li class="nav-item"><a class="nav-link text-white fw-medium" href="#kontak"><i class="bi bi-envelope d-lg-none me-2"></i>Kontak</a></li>
            </ul>
            <a href="<?= BASE_URL ?>login.php" class="btn btn-warning text-dark fw-bold px-4 rounded-pill shadow-sm">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk E-Learning
            </a>
        </div>
    </div>
</nav>

<!-- Hero Banner Section -->
<section class="text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 40%, #073896 100%) !important; padding-top: 110px !important; padding-bottom: 60px !important;">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-7 text-center text-lg-start">
                <div class="d-inline-flex align-items-center gap-2 bg-white px-3 py-2 rounded-pill mb-4 shadow">
                    <span class="badge bg-warning text-dark fw-bold rounded-pill"><i class="bi bi-lightning-charge-fill me-1"></i> Next-Gen LMS</span>
                    <span class="small fw-bold text-dark" style="color: #000000 !important;"><?= Security::safeText($settings['landing_hero_badge'] ?? 'Portal Pembelajaran Digital Terpadu') ?></span>
                </div>
                <h1 class="display-4 fw-extrabold mb-3 text-white font-heading lh-sm" style="text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                    <?= Security::safeText($settings['landing_hero_title'] ?? 'E-Learning SMK Muthia Harapan Cicalengka') ?>
                </h1>
                <p class="lead text-white opacity-90 mb-4 pe-lg-4 fw-normal fs-5" style="text-shadow: 0 1px 4px rgba(0,0,0,0.2);">
                    <?= Security::safeText($settings['landing_hero_desc'] ?? 'Sistem Manajemen Pembelajaran Digital Interaktif, Transparan, dan Modern untuk Membentuk Generasi Unggul Siap Kerja.') ?>
                </p>
                <div class="d-flex gap-3 justify-content-center justify-content-lg-start flex-wrap">
                    <a href="<?= BASE_URL ?>login.php" class="btn btn-warning btn-lg text-dark fw-bold px-4 py-3 rounded-pill shadow">
                        <i class="bi bi-rocket-takeoff-fill me-2"></i> Mulai Belajar Sekarang
                    </a>
                    <a href="#jurusan" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill">
                        <i class="bi bi-grid-fill me-2"></i> Program Keahlian
                    </a>
                </div>
            </div>
            
            <div class="col-lg-5 text-center">
                <div class="p-4 p-md-5 rounded-4 border border-white border-opacity-30 shadow-lg text-white" style="background: rgba(255, 255, 255, 0.15) !important; backdrop-filter: blur(12px);">
                    <div class="bg-warning text-dark d-inline-flex p-3 rounded-circle mb-3 shadow-sm">
                        <i class="bi bi-laptop display-4 text-dark"></i>
                    </div>
                    <h3 class="fw-bold mb-2 text-white font-heading"><?= Security::safeText($settings['landing_hero_card_title'] ?? 'KBM Digital Terpadu') ?></h3>
                    <p class="small text-white opacity-90 mb-4"><?= Security::safeText($settings['landing_hero_card_desc'] ?? 'Materi, CBT, Quiz, Absensi QR Code, & Laporan Real-time') ?></p>
                    
                    <div class="row g-3 text-start pt-3 border-top border-white border-opacity-20">
                        <div class="col-6">
                            <div class="hero-feature-pill">
                                <div class="hero-feature-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <span class="hero-feature-text">CBT &amp; Quiz Online</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hero-feature-pill">
                                <div class="hero-feature-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <span class="hero-feature-text">Absensi QR Code</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hero-feature-pill">
                                <div class="hero-feature-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <span class="hero-feature-text">E-Modul &amp; Video</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hero-feature-pill">
                                <div class="hero-feature-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <span class="hero-feature-text">E-Rapor &amp; Sertifikat</span>
                            </div>
                        </div>
                    </div>

                    <!-- Registered Students Badge Showcase -->
                    <div class="d-flex align-items-center justify-content-center gap-2 mt-3 pt-3 border-top border-white border-opacity-20 text-white">
                        <i class="bi bi-people-fill text-warning fs-5"></i>
                        <span class="small fw-semibold">Terhubung dengan <strong class="text-warning"><?= (int)($totalSiswa ?? 0) ?>+ Siswa Terdaftar</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Highlighting Stats Counter Bar -->
<section class="py-4 bg-white border-bottom shadow-sm">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <div class="p-2">
                    <h2 class="fw-extrabold text-primary mb-0 font-heading"><?= (int)($totalSiswa ?? 0) ?>+</h2>
                    <span class="small text-dark fw-bold d-flex align-items-center justify-content-center gap-1"><i class="bi bi-people-fill text-primary me-1"></i> Siswa Terdaftar</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2">
                    <h2 class="fw-extrabold text-success mb-0 font-heading"><?= count($jurusanList ?? []) ?>+</h2>
                    <span class="small text-dark fw-bold d-flex align-items-center justify-content-center gap-1"><i class="bi bi-award-fill text-success me-1"></i> Program Keahlian</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2">
                    <h2 class="fw-extrabold text-warning mb-0 font-heading"><?= count($guruList ?? []) ?>+</h2>
                    <span class="small text-dark fw-bold d-flex align-items-center justify-content-center gap-1"><i class="bi bi-person-badge-fill text-warning me-1"></i> Guru &amp; Pengajar</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2">
                    <h2 class="fw-extrabold text-danger mb-0 font-heading">24/7</h2>
                    <span class="small text-dark fw-bold d-flex align-items-center justify-content-center gap-1"><i class="bi bi-shield-check text-danger me-1"></i> Akses KBM &amp; Ujian</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Profil Sekolah, Visi & Misi Section -->
<section id="profil" class="py-5 bg-light">
    <div class="container py-4">
        <div class="row align-items-center gy-4 mb-4">
            <div class="col-lg-6">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold text-uppercase px-3 py-2 rounded-pill mb-2">
                    <i class="bi bi-building me-1"></i> <?= Security::safeText($settings['landing_profil_tag'] ?? 'Profil Sekolah') ?>
                </span>
                <h2 class="display-6 fw-bold text-dark mb-3 font-heading">
                    <?= Security::safeText($settings['landing_profil_title'] ?? 'Mencetak Lulusan Berkarakter & Competent') ?>
                </h2>
                <p class="text-secondary lead fs-6 mb-4 landing-profil-desc">
                    <?= Security::safeText($settings['landing_profil_desc'] ?? 'SMK Muthia Harapan Cicalengka berkomitmen memberikan pendidikan kejuruan berkualitas tinggi berbasis teknologi informasi dan industri modern di Jawa Barat.') ?>
                </p>
                
                <div class="row g-3">
                    <!-- Visi Card -->
                    <div class="col-12 col-md-6">
                        <div class="card-hover-effect h-100 p-4 rounded-4 border-start border-4 border-primary shadow-sm bg-white">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="bg-primary text-white rounded-3 d-inline-flex align-items-center justify-content-center shadow-sm" style="width:38px; height:38px;">
                                    <i class="bi bi-eye-fill fs-5"></i>
                                </div>
                                <h5 class="fw-bold text-primary mb-0 font-heading"><?= Security::safeText($settings['landing_visi_title'] ?? 'Visi Utama') ?></h5>
                            </div>
                            <div class="landing-misi-content">
                                <?= $visiContent ?>
                            </div>
                        </div>
                    </div>

                    <!-- Misi Card (Supports Formatted Lists) -->
                    <div class="col-12 col-md-6">
                        <div class="card-hover-effect h-100 p-4 rounded-4 border-start border-4 border-success shadow-sm bg-white">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="bg-success text-white rounded-3 d-inline-flex align-items-center justify-content-center shadow-sm" style="width:38px; height:38px;">
                                    <i class="bi bi-bullseye fs-5"></i>
                                </div>
                                <h5 class="fw-bold text-success mb-0 font-heading"><?= Security::safeText($settings['landing_misi_title'] ?? 'Misi Presisi') ?></h5>
                            </div>
                            <div class="landing-misi-content">
                                <?= $misiContent ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Video Section -->
            <div class="col-lg-6">
                <div class="p-2.5 bg-white rounded-4 shadow-lg border">
                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden">
                        <iframe src="<?= htmlspecialchars($videoEmbedUrl) ?>" title="Profil Sekolah" allowfullscreen loading="lazy"></iframe>
                    </div>
                    <div class="p-3 text-center bg-light rounded-bottom-3 mt-1">
                        <small class="fw-bold text-muted"><i class="bi bi-youtube text-danger me-1"></i> Video Profil & Fasilitas SMK Muthia Harapan Cicalengka</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Fitur Unggulan LMS Section -->
<section id="fitur" class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-warning bg-opacity-10 text-dark fw-bold text-uppercase px-3 py-2 rounded-pill mb-2">
                <i class="bi bi-stars text-warning me-1"></i> Keunggulan System
            </span>
            <h2 class="fw-bold display-6 mb-2 font-heading">Fitur Unggulan E-Learning</h2>
            <p class="text-muted">Dirancang khusus untuk mendukung kegiatan belajar mengajar secara efisien, terintegrasi, dan fleksibel.</p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card card-hover-effect h-100 p-4 text-center rounded-4 border-0">
                    <div class="icon-gradient-primary rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width:64px; height:64px;">
                        <i class="bi bi-journal-text fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2 font-heading">Modul & KBM Digital</h5>
                    <p class="small text-muted mb-0">Akses materi pembelajaran, PDF, video, dan tugas interaktif kapan saja dan dari mana saja.</p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card card-hover-effect h-100 p-4 text-center rounded-4 border-0">
                    <div class="icon-gradient-warning rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width:64px; height:64px;">
                        <i class="bi bi-ui-checks fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2 font-heading">CBT & Quiz Online</h5>
                    <p class="small text-muted mb-0">Ujian berbasis komputer presisi tinggi dengan acak soal, timer otomatis, dan koreksi instan.</p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card card-hover-effect h-100 p-4 text-center rounded-4 border-0">
                    <div class="icon-gradient-success rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width:64px; height:64px;">
                        <i class="bi bi-qr-code-scan fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2 font-heading">Absensi QR Code</h5>
                    <p class="small text-muted mb-0">Pencatatan presensi siswa real-time menggunakan QR Code unik & pelaporan rekap harian.</p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card card-hover-effect h-100 p-4 text-center rounded-4 border-0">
                    <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width:64px; height:64px; background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);">
                        <i class="bi bi-award-fill fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-2 font-heading">E-Rapor & Sertifikat</h5>
                    <p class="small text-muted mb-0">Penerbitan laporan hasil belajar digital dan sertifikat kompetensi otomatis dari admin.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Jurusan / Program Keahlian -->
<section id="jurusan" class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold text-uppercase px-3 py-2 rounded-pill mb-2">Program Keahlian</span>
            <h2 class="fw-bold display-6 font-heading">Pilihan Jurusan Unggulan</h2>
            <p class="text-muted">Mempersiapkan siswa menjadi tenaga kerja profesional dan wirausahawan mandiri.</p>
        </div>
        <div class="row g-4">
            <?php if (!empty($jurusanList)): ?>
                <?php foreach ($jurusanList as $j): ?>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card card-hover-effect h-100 p-4 text-center bg-white rounded-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-4 d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:70px; height:70px;">
                                <i class="bi bi-laptop-fill fs-2"></i>
                            </div>
                            <h5 class="fw-bold mb-2 text-dark font-heading"><?= Security::safeText($j['nama_jurusan']) ?></h5>
                            <p class="small text-muted mb-3"><?= Security::safeText($j['deskripsi'] ?? 'Program keahlian terintegrasi dengan kebutuhan industri modern.') ?></p>
                            <span class="badge bg-light text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 mt-auto align-self-center">
                                <i class="bi bi-check2-circle me-1"></i> Siap Kerja
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted">Belum ada data jurusan.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Tenaga Pengajar -->
<section id="guru" class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge bg-success bg-opacity-10 text-success fw-bold text-uppercase px-3 py-2 rounded-pill mb-2">Tenaga Pengajar</span>
            <h2 class="fw-bold display-6 font-heading">Guru & Pengajar Professional</h2>
            <p class="text-muted">Didukung pendidik berpengalaman di bidang akademik dan kejuruan industri.</p>
        </div>
        <div class="row g-4">
            <?php if (!empty($guruList)): ?>
                <?php foreach (array_slice($guruList, 0, 8) as $g): ?>
                    <?php 
                        $guruPhotoUrl = null;
                        $avFile = $g['avatar'] ?? ($g['foto'] ?? ($g['foto_profil'] ?? ''));
                        if (!empty($avFile) && $avFile !== 'default_avatar.png') {
                            if (file_exists(ROOT_PATH . 'assets/uploads/profile/' . $avFile)) {
                                $guruPhotoUrl = BASE_URL . 'assets/uploads/profile/' . htmlspecialchars($avFile);
                            } elseif (file_exists(ROOT_PATH . 'assets/uploads/avatar/' . $avFile)) {
                                $guruPhotoUrl = BASE_URL . 'assets/uploads/avatar/' . htmlspecialchars($avFile);
                            } elseif (file_exists(ROOT_PATH . 'assets/uploads/' . $avFile)) {
                                $guruPhotoUrl = BASE_URL . 'assets/uploads/' . htmlspecialchars($avFile);
                            } elseif (strpos($avFile, 'http') === 0 || strpos($avFile, 'assets/') === 0) {
                                $guruPhotoUrl = (strpos($avFile, 'http') === 0) ? $avFile : BASE_URL . $avFile;
                            }
                        }
                    ?>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card card-hover-effect h-100 text-center overflow-hidden border-0 bg-light rounded-4">
                            <div class="pt-4 px-4">
                                <?php if ($guruPhotoUrl): ?>
                                    <img src="<?= $guruPhotoUrl ?>" alt="<?= Security::safeText($g['nama_lengkap']) ?>" class="guru-card-img mx-auto mb-3" style="width: 84px !important; height: 84px !important; max-width: 84px !important; max-height: 84px !important; object-fit: cover !important; border-radius: 50% !important; border: 3px solid #ffffff !important; box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12) !important; display: block !important;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold fs-2 mx-auto mb-3 shadow-sm" style="width:84px !important; height:84px !important; background: linear-gradient(135deg, #0d6efd 0%, #0056d3 100%); border: 3px solid #fff;">
                                        <?= strtoupper(substr(Security::safeText($g['nama_lengkap']), 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body pt-1">
                                <h6 class="fw-bold text-dark mb-1 fs-6 font-heading"><?= Security::safeText($g['nama_lengkap']) ?></h6>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill small mb-2">Guru Pengajar</span>
                                <small class="text-muted d-block font-monospace">NIP: <?= Security::safeText($g['nip'] ?? '-') ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted">Belum ada data pengajar.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Kontak & Google Maps -->
<section id="kontak" class="py-5 bg-light overflow-hidden">
    <style>
    /* Ultra-Responsive Modern Contact Cards & Icons */
    .contact-info-card {
        background: #ffffff;
        border: 1px solid #e2e8f0 !important;
        border-radius: 22px !important;
        padding: 20px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03) !important;
        width: 100%;
        overflow: hidden;
    }

    .contact-info-card:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 12px 28px rgba(13, 110, 253, 0.12) !important;
        border-color: #cbd5e1 !important;
    }

    .contact-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .contact-icon-box.icon-danger {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.12) 0%, rgba(239, 68, 68, 0.22) 100%);
        color: #dc3545;
        box-shadow: 0 6px 14px rgba(220, 53, 69, 0.15);
    }

    .contact-icon-box.icon-success {
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.12) 0%, rgba(16, 185, 129, 0.22) 100%);
        color: #198754;
        box-shadow: 0 6px 14px rgba(25, 135, 84, 0.15);
    }

    .contact-icon-box.icon-primary {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.12) 0%, rgba(59, 130, 246, 0.22) 100%);
        color: #0d6efd;
        box-shadow: 0 6px 14px rgba(13, 110, 253, 0.15);
    }

    @media (max-width: 575.98px) {
        .contact-info-card {
            padding: 16px !important;
            border-radius: 18px !important;
        }
        .contact-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            font-size: 1.2rem;
        }
    }
    </style>

    <div class="container py-4">
        <div class="row gy-4 align-items-stretch">
            
            <!-- Left Side: Kontak Information Cards -->
            <div class="col-lg-5 d-flex flex-column justify-content-between">
                <div>
                    <span class="badge bg-danger bg-opacity-10 text-danger fw-bold text-uppercase px-3 py-2 rounded-pill mb-2">
                        <i class="bi bi-chat-right-dots-fill me-1"></i> <?= Security::safeText($settings['landing_kontak_tag'] ?? 'Hubungi Kami') ?>
                    </span>
                    <h2 class="fw-bold display-6 mb-4 font-heading text-dark"><?= Security::safeText($settings['landing_kontak_title'] ?? 'Lokasi & Kontak Sekolah') ?></h2>
                </div>
                
                <div class="d-flex flex-column gap-3 mb-2">
                    <!-- Alamat Card -->
                    <div class="contact-info-card">
                        <div class="d-flex align-items-start gap-3">
                            <div class="contact-icon-box icon-danger">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0" style="overflow-wrap: anywhere; word-break: break-word;">
                                <h6 class="fw-bold mb-1 font-heading text-dark fs-6">Alamat Lengkap</h6>
                                <p class="small text-secondary mb-0" style="text-align: justify; text-justify: inter-word; line-height: 1.55;">
                                    <?= Security::safeText($settings['alamat'] ?? 'Jl. Raya Cicalengka, Kab. Bandung, Jawa Barat 40395') ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Telepon / WhatsApp Card -->
                    <div class="contact-info-card">
                        <div class="d-flex align-items-start gap-3">
                            <div class="contact-icon-box icon-success">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0" style="overflow-wrap: anywhere; word-break: break-word;">
                                <h6 class="fw-bold mb-1 font-heading text-dark fs-6">Telepon / WhatsApp</h6>
                                <p class="small text-secondary mb-0 fw-semibold">
                                    <?= Security::safeText($settings['telepon'] ?? '+62 812-3456-7890') ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Email Resmi Card -->
                    <div class="contact-info-card">
                        <div class="d-flex align-items-start gap-3">
                            <div class="contact-icon-box icon-primary">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0" style="overflow-wrap: anywhere; word-break: break-word;">
                                <h6 class="fw-bold mb-1 font-heading text-dark fs-6">Email Resmi</h6>
                                <p class="small text-secondary mb-0 fw-semibold">
                                    <?= Security::safeText($settings['landing_email'] ?? $settings['smtp_user'] ?? 'info@smkmh-cicalengka.sch.id') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Google Maps Embed Card -->
            <div class="col-lg-7">
                <div class="p-2.5 bg-white rounded-4 border shadow-sm h-100 d-flex flex-column" style="border: 1px solid #e2e8f0 !important; min-height: 380px; border-radius: 22px !important;">
                    <div class="flex-grow-1 rounded-3 overflow-hidden position-relative w-100" style="min-height: 320px;">
                        <iframe src="<?= htmlspecialchars($mapsUrl) ?>" title="Lokasi Google Maps" class="w-100 h-100 position-absolute top-0 start-0 border-0 rounded-3" allowfullscreen loading="lazy"></iframe>
                    </div>
                    <div class="p-2.5 text-center bg-light rounded-bottom-3 mt-2 border-top">
                        <small class="fw-bold text-muted"><i class="bi bi-map-fill text-danger me-1"></i> Peta Lokasi Google Maps SMK Muthia Harapan Cicalengka</small>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-4 border-top border-secondary" style="background: #0f172a !important;">
    <div class="container">
        <div class="row gy-4 mb-4">
            <div class="col-lg-5">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <?php if ($logoUrl): ?>
                        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" class="rounded-3 bg-white p-1" style="height:38px;">
                    <?php else: ?>
                        <i class="bi bi-mortarboard-fill fs-3 text-warning"></i>
                    <?php endif; ?>
                    <h5 class="fw-bold text-warning mb-0 font-heading"><?= $schoolName ?></h5>
                </div>
                <p class="small text-white-50 mb-3">
                    Portal Learning Management System Modern & Terintegrasi untuk mendukung kegiatan belajar mengajar berbasis digital di SMK Muthia Harapan Cicalengka.
                </p>
            </div>
            <div class="col-6 col-lg-3">
                <h6 class="fw-bold text-white mb-3 font-heading">Navigasi Cepat</h6>
                <ul class="list-unstyled small text-white-50 d-flex flex-column gap-2 mb-0">
                    <li><a href="#profil" class="text-white-50 text-decoration-none hover-white"><i class="bi bi-chevron-right me-1"></i> Profil Sekolah</a></li>
                    <li><a href="#fitur" class="text-white-50 text-decoration-none hover-white"><i class="bi bi-chevron-right me-1"></i> Fitur LMS</a></li>
                    <li><a href="#jurusan" class="text-white-50 text-decoration-none hover-white"><i class="bi bi-chevron-right me-1"></i> Program Keahlian</a></li>
                    <li><a href="#guru" class="text-white-50 text-decoration-none hover-white"><i class="bi bi-chevron-right me-1"></i> Tenaga Pengajar</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-4 text-lg-end">
                <h6 class="fw-bold text-white mb-3 font-heading">Akses Portal</h6>
                <a href="<?= BASE_URL ?>login.php" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-pill shadow-sm mb-3">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login E-Learning
                </a>
                <p class="small text-white-50 mb-0">&copy; <?= date('Y') ?> <?= $schoolName ?>. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</footer>

<?php if ($waChatEnabled): ?>
<!-- Floating WhatsApp Quick Chat Widget -->
<div class="wa-floating-container" id="waQuickChatContainer">
    <!-- Popup Chat Bubble Card -->
    <div class="wa-chat-popup shadow-lg" id="waChatPopup" role="dialog" aria-label="Bantuan WhatsApp">
        <div class="wa-popup-header">
            <div class="d-flex align-items-center gap-2">
                <div class="wa-popup-avatar">
                    <i class="bi bi-whatsapp"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold fs-6 text-white">Helpdesk E-Learning</h6>
                    <small class="text-white-50 d-flex align-items-center" style="font-size: 0.72rem;">
                        <span class="wa-online-dot"></span> Admin Online & Siap Bantu
                    </small>
                </div>
            </div>
            <button type="button" class="wa-btn-close-popup" id="waClosePopup" title="Tutup pesan">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="wa-popup-body">
            <div class="wa-chat-bubble">
                <p class="mb-1 fw-semibold text-dark">Halo! 👋 Butuh bantuan terkait sistem?</p>
                <p class="mb-0 text-secondary" style="font-size: 0.8rem;">
                    Jika ada kesalahan akun, kendala login, materi, atau sistem, tim teknis kami siap membantu Anda secara langsung via WhatsApp.
                </p>
                <div class="text-end mt-1">
                    <span class="text-muted" style="font-size: 0.68rem;"><?= date('H:i') ?></span>
                </div>
            </div>
        </div>
        <div class="wa-popup-footer">
            <a href="<?= htmlspecialchars($waChatUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success w-100 fw-bold rounded-pill py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm" style="background: #25D366; border-color: #25D366;">
                <i class="bi bi-whatsapp fs-5"></i> Chat via WhatsApp
            </a>
        </div>
    </div>

    <!-- Main Floating WhatsApp Button -->
    <a href="<?= htmlspecialchars($waChatUrl) ?>" target="_blank" rel="noopener noreferrer" class="wa-floating-btn shadow-lg" id="waFloatingBtn" aria-label="Chat WhatsApp Bantuan E-Learning" title="<?= htmlspecialchars($waChatLabel) ?>">
        <div class="wa-btn-pulse"></div>
        <div class="wa-icon-box">
            <i class="bi bi-whatsapp"></i>
        </div>
        <div class="wa-text-label">
            <span class="wa-text-title"><?= htmlspecialchars($waChatLabel) ?></span>
            <span class="wa-text-subtitle">
                <span class="wa-online-dot"></span> Online Siap Bantu
            </span>
        </div>
    </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const waPopup = document.getElementById('waChatPopup');
    const waCloseBtn = document.getElementById('waClosePopup');
    const waBtn = document.getElementById('waFloatingBtn');

    // Tampilkan popup otomatis setelah 3.5 detik jika belum pernah ditutup di sesi ini
    const popupDismissed = sessionStorage.getItem('smk_wa_popup_dismissed');
    if (!popupDismissed && waPopup) {
        setTimeout(function() {
            waPopup.classList.add('show');
        }, 3500);
    }

    if (waCloseBtn && waPopup) {
        waCloseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            waPopup.classList.remove('show');
            sessionStorage.setItem('smk_wa_popup_dismissed', '1');
        });
    }

    // Klik kanan atau tahan tombol untuk toggle popup jika ingin membaca info
    if (waBtn && waPopup) {
        waBtn.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            waPopup.classList.toggle('show');
        });
    }
});
</script>
<?php endif; ?>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
