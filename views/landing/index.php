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
$schoolName = $settings['nama_sekolah'] ?? 'SMK Muthia Harapan Cicalengka';
$misiContent = Security::sanitizeHtml($settings['landing_misi_desc'] ?? 'Mengembangkan kurikulum industri & sertifikasi kompetensi keahlian.');
$visiContent = Security::sanitizeHtml($settings['landing_visi_desc'] ?? 'Menjadi SMK Unggulan berstandar Nasional berbasis Teknologi & Imtaq.');
?>

<!-- Navbar Landing Page -->
<nav class="navbar navbar-expand-lg navbar-dark landing-navbar fixed-top py-3" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#">
            <?php if ($logoUrl): ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" class="rounded-3 bg-white p-1 shadow-sm" style="height:38px; object-fit:contain;">
            <?php else: ?>
                <div class="bg-warning text-dark rounded-3 d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm" style="width:38px; height:38px;">
                    🎓
                </div>
            <?php endif; ?>
            <span class="fs-5 tracking-tight text-white font-heading"><?= htmlspecialchars($schoolName) ?></span>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navPublic" aria-controls="navPublic" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navPublic">
            <ul class="navbar-nav ms-auto me-lg-4 gap-1 py-2 py-lg-0">
                <li class="nav-item"><a class="nav-link text-white-50 hover-white fw-medium px-3" href="#profil"><i class="bi bi-info-circle me-1"></i> Profil</a></li>
                <li class="nav-item"><a class="nav-link text-white-50 hover-white fw-medium px-3" href="#fitur"><i class="bi bi-stars me-1"></i> Fitur LMS</a></li>
                <li class="nav-item"><a class="nav-link text-white-50 hover-white fw-medium px-3" href="#jurusan"><i class="bi bi-award me-1"></i> Jurusan</a></li>
                <li class="nav-item"><a class="nav-link text-white-50 hover-white fw-medium px-3" href="#guru"><i class="bi bi-people me-1"></i> Pengajar</a></li>
                <li class="nav-item"><a class="nav-link text-white-50 hover-white fw-medium px-3" href="#kontak"><i class="bi bi-geo-alt me-1"></i> Kontak</a></li>
            </ul>
            <a href="<?= BASE_URL ?>login.php" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-box-arrow-in-right fs-5"></i>
                <span>Masuk E-Learning</span>
            </a>
        </div>
    </div>
</nav>

<!-- Hero Banner Section -->
<section class="landing-hero text-white pt-5 pb-5 position-relative">
    <div class="container py-5 my-lg-4 mt-4">
        <div class="row align-items-center gy-5">
            <div class="col-lg-7 text-center text-lg-start">
                <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 backdrop-blur px-3 py-2 rounded-pill mb-4 border border-white border-opacity-25 shadow-sm">
                    <span class="badge bg-warning text-dark fw-bold rounded-pill"><i class="bi bi-lightning-charge-fill me-1"></i> Next-Gen LMS</span>
                    <span class="small fw-semibold text-white"><?= htmlspecialchars($settings['landing_hero_badge'] ?? 'Portal Pembelajaran Digital Terpadu') ?></span>
                </div>
                <h1 class="display-4 fw-extrabold mb-3 text-white font-heading lh-sm">
                    <?= htmlspecialchars($settings['landing_hero_title'] ?? 'E-Learning SMK Muthia Harapan Cicalengka') ?>
                </h1>
                <p class="lead text-white-50 mb-4 pe-lg-4 fw-normal fs-5">
                    <?= htmlspecialchars($settings['landing_hero_desc'] ?? 'Sistem Manajemen Pembelajaran Digital Interaktif, Transparan, dan Modern untuk Membentuk Generasi Unggul Siap Kerja.') ?>
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
                <div class="glass-card p-4 p-md-5 rounded-4 border border-white border-opacity-25 shadow-lg text-white">
                    <div class="bg-warning bg-opacity-20 text-warning d-inline-flex p-3 rounded-circle mb-3 border border-warning border-opacity-25 shadow-sm">
                        <i class="bi bi-laptop display-4"></i>
                    </div>
                    <h3 class="fw-bold mb-2 font-heading"><?= htmlspecialchars($settings['landing_hero_card_title'] ?? 'KBM Digital Terpadu') ?></h3>
                    <p class="small text-white-50 mb-4"><?= htmlspecialchars($settings['landing_hero_card_desc'] ?? 'Materi, CBT, Quiz, Absensi QR Code, & Laporan Real-time') ?></p>
                    
                    <div class="row g-2 text-start pt-3 border-top border-white border-opacity-10">
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 p-2.5 rounded-3">
                                <i class="bi bi-check-circle-fill text-warning fs-5"></i>
                                <span class="small fw-semibold">CBT & Quiz Online</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 p-2.5 rounded-3">
                                <i class="bi bi-check-circle-fill text-warning fs-5"></i>
                                <span class="small fw-semibold">Absensi QR Code</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 p-2.5 rounded-3">
                                <i class="bi bi-check-circle-fill text-warning fs-5"></i>
                                <span class="small fw-semibold">E-Modul & Video</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 p-2.5 rounded-3">
                                <i class="bi bi-check-circle-fill text-warning fs-5"></i>
                                <span class="small fw-semibold">E-Rapor & Sertifikat</span>
                            </div>
                        </div>
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
                    <h2 class="fw-extrabold text-primary mb-0 font-heading">100%</h2>
                    <span class="small text-muted fw-semibold">Digital Learning Platform</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2">
                    <h2 class="fw-extrabold text-success mb-0 font-heading"><?= count($jurusanList ?? []) ?>+</h2>
                    <span class="small text-muted fw-semibold">Program Keahlian</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2">
                    <h2 class="fw-extrabold text-warning mb-0 font-heading"><?= count($guruList ?? []) ?>+</h2>
                    <span class="small text-muted fw-semibold">Guru & Pengajar Professional</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2">
                    <h2 class="fw-extrabold text-danger mb-0 font-heading">24/7</h2>
                    <span class="small text-muted fw-semibold">Akses KBM & Ujian Online</span>
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
                    <i class="bi bi-building me-1"></i> <?= htmlspecialchars($settings['landing_profil_tag'] ?? 'Profil Sekolah') ?>
                </span>
                <h2 class="display-6 fw-bold text-dark mb-3 font-heading">
                    <?= htmlspecialchars($settings['landing_profil_title'] ?? 'Mencetak Lulusan Berkarakter & Competent') ?>
                </h2>
                <p class="text-secondary lead fs-6 mb-4">
                    <?= htmlspecialchars($settings['landing_profil_desc'] ?? 'SMK Muthia Harapan Cicalengka berkomitmen memberikan pendidikan kejuruan berkualitas tinggi berbasis teknologi informasi dan industri modern di Jawa Barat.') ?>
                </p>
                
                <div class="row g-3">
                    <!-- Visi Card -->
                    <div class="col-12 col-md-6">
                        <div class="card-hover-effect h-100 p-4 rounded-4 border-start border-4 border-primary shadow-sm bg-white">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="bg-primary text-white rounded-3 d-inline-flex align-items-center justify-content-center shadow-sm" style="width:38px; height:38px;">
                                    <i class="bi bi-eye-fill fs-5"></i>
                                </div>
                                <h5 class="fw-bold text-primary mb-0 font-heading"><?= htmlspecialchars($settings['landing_visi_title'] ?? 'Visi Utama') ?></h5>
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
                                <h5 class="fw-bold text-success mb-0 font-heading"><?= htmlspecialchars($settings['landing_misi_title'] ?? 'Misi Presisi') ?></h5>
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
                            <h5 class="fw-bold mb-2 text-dark font-heading"><?= htmlspecialchars($j['nama_jurusan']) ?></h5>
                            <p class="small text-muted mb-3"><?= htmlspecialchars($j['deskripsi'] ?? 'Program keahlian terintegrasi dengan kebutuhan industri modern.') ?></p>
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
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card card-hover-effect h-100 text-center overflow-hidden border-0 bg-light rounded-4">
                            <div class="pt-4 px-4">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold fs-2 mx-auto mb-3 shadow-sm" style="width:84px; height:84px; background: linear-gradient(135deg, #0d6efd 0%, #0056d3 100%); border: 3px solid #fff;">
                                    <?= strtoupper(substr($g['nama_lengkap'], 0, 1)) ?>
                                </div>
                            </div>
                            <div class="card-body pt-1">
                                <h6 class="fw-bold text-dark mb-1 fs-6 font-heading"><?= htmlspecialchars($g['nama_lengkap']) ?></h6>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill small mb-2">Guru Pengajar</span>
                                <small class="text-muted d-block font-monospace">NIP: <?= htmlspecialchars($g['nip'] ?? '-') ?></small>
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
<section id="kontak" class="py-5 bg-light">
    <div class="container py-4">
        <div class="row gy-4">
            <div class="col-lg-5">
                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold text-uppercase px-3 py-2 rounded-pill mb-2">
                    <?= htmlspecialchars($settings['landing_kontak_tag'] ?? 'Hubungi Kami') ?>
                </span>
                <h2 class="fw-bold display-6 mb-4 font-heading"><?= htmlspecialchars($settings['landing_kontak_title'] ?? 'Lokasi & Kontak Sekolah') ?></h2>
                
                <div class="card-hover-effect p-3 mb-3 d-flex align-items-center gap-3 bg-white rounded-4">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3 flex-shrink-0">
                        <i class="bi bi-geo-alt-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 font-heading">Alamat Lengkap</h6>
                        <p class="small text-muted mb-0"><?= htmlspecialchars($settings['alamat'] ?? 'Jl. Raya Cicalengka, Kab. Bandung, Jawa Barat 40395') ?></p>
                    </div>
                </div>

                <div class="card-hover-effect p-3 mb-3 d-flex align-items-center gap-3 bg-white rounded-4">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 flex-shrink-0">
                        <i class="bi bi-telephone-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 font-heading">Telepon / WhatsApp</h6>
                        <p class="small text-muted mb-0"><?= htmlspecialchars($settings['telepon'] ?? '+62 812-3456-7890') ?></p>
                    </div>
                </div>

                <div class="card-hover-effect p-3 d-flex align-items-center gap-3 bg-white rounded-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 flex-shrink-0">
                        <i class="bi bi-envelope-fill fs-3"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 font-heading">Email Resmi</h6>
                        <p class="small text-muted mb-0"><?= htmlspecialchars($settings['landing_email'] ?? $settings['smtp_user'] ?? 'info@smkmh-cicalengka.sch.id') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7">
                <div class="card-hover-effect p-2 bg-white rounded-4 h-100">
                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm h-100">
                        <iframe src="<?= htmlspecialchars($mapsUrl) ?>" title="Lokasi Google Maps" allowfullscreen loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-4 border-top border-secondary">
    <div class="container">
        <div class="row gy-4 mb-4">
            <div class="col-lg-5">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <?php if ($logoUrl): ?>
                        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" class="rounded-3 bg-white p-1" style="height:38px;">
                    <?php else: ?>
                        <i class="bi bi-mortarboard-fill fs-3 text-warning"></i>
                    <?php endif; ?>
                    <h5 class="fw-bold text-warning mb-0 font-heading"><?= htmlspecialchars($schoolName) ?></h5>
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
                <p class="small text-white-50 mb-0">&copy; <?= date('Y') ?> <?= htmlspecialchars($schoolName) ?>. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</footer>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
