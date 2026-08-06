<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#">
            <i class="bi bi-mortarboard-fill fs-3 text-warning"></i>
            <span>SMK Muthia Harapan</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPublic">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navPublic">
            <ul class="navbar-nav ms-auto me-3 gap-2">
                <li class="nav-item"><a class="nav-link text-white fw-medium" href="#profil">Profil</a></li>
                <li class="nav-item"><a class="nav-link text-white fw-medium" href="#jurusan">Jurusan</a></li>
                <li class="nav-item"><a class="nav-link text-white fw-medium" href="#guru">Tenaga Pengajar</a></li>
                <li class="nav-item"><a class="nav-link text-white fw-medium" href="#kontak">Kontak</a></li>
            </ul>
            <a href="<?= BASE_URL ?>login.php" class="btn btn-warning text-dark fw-bold px-4 rounded-pill shadow-sm">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk E-Learning
            </a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="bg-primary text-white pt-5 pb-5 mt-5 position-relative overflow-hidden">
    <div class="container py-5">
        <div class="row align-items-center gy-4">
            <div class="col-lg-7 text-center text-lg-start">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill mb-3">Portal Pembelajaran Digital</span>
                <h1 class="display-4 fw-extrabold mb-3">E-Learning SMK Muthia Harapan Cicalengka</h1>
                <p class="lead text-white-50 mb-4">Sistem Manajemen Pembelajaran Digital Interaktif, Transparan, dan Modern untuk Membentuk Generasi Unggul Siap Kerja.</p>
                <div class="d-flex gap-3 justify-content-center justify-content-lg-start flex-wrap">
                    <a href="<?= BASE_URL ?>login.php" class="btn btn-warning btn-lg fw-bold px-4 rounded-pill">Mulai Belajar Now</a>
                    <a href="#jurusan" class="btn btn-outline-light btn-lg px-4 rounded-pill">Lihat Program Keahlian</a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="p-4 bg-white bg-opacity-10 backdrop-blur rounded-4 border border-white border-opacity-25 shadow-lg">
                    <i class="bi bi-laptop display-1 text-warning"></i>
                    <h4 class="mt-3 fw-bold">KBM Digital Terpadu</h4>
                    <p class="small text-white-50">Materi, CBT, Quiz, Absensi QR Code, & Laporan Real-time</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Profil & Visi Misi -->
<section id="profil" class="py-5 bg-light">
    <div class="container py-4">
        <div class="row align-items-center gy-4">
            <div class="col-lg-6">
                <span class="text-primary fw-bold text-uppercase">Profil Sekolah</span>
                <h2 class="fw-bold mb-3">Mencetak Lulusan Berkarakter & Competent</h2>
                <p class="text-muted">SMK Muthia Harapan Cicalengka berkomitmen memberikan pendidikan kejuruan berkualitas tinggi berbasis teknologi informasi dan industri modern di Jawa Barat.</p>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 bg-white rounded-3 shadow-sm border-start border-4 border-primary">
                            <h5 class="fw-bold text-primary mb-1">Visi Utama</h5>
                            <small class="text-muted">Menjadi SMK Unggulan berstandar Nasional berbasis Teknologi & Imtaq.</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-white rounded-3 shadow-sm border-start border-4 border-success">
                            <h5 class="fw-bold text-success mb-1">Misi Presisi</h5>
                            <small class="text-muted">Mengembangkan kurikulum industri & sertifikasi kompetensi keahlian.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow">
                    <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Profil SMK Muthia Harapan" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Jurusan -->
<section id="jurusan" class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase">Program Keahlian</span>
            <h2 class="fw-bold">Pilihan Jurusan Unggulan</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($jurusanList as $j): ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card card-custom h-100 p-4 text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px; height:60px;">
                            <i class="bi bi-code-square fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-2"><?= htmlspecialchars($j['nama_jurusan']) ?></h5>
                        <p class="small text-muted mb-0"><?= htmlspecialchars($j['deskripsi']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Tenaga Pengajar -->
<section id="guru" class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase">Tenaga Pengajar</span>
            <h2 class="fw-bold">Guru & Pengajar Professional</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($guruList as $g): ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card card-custom h-100 text-center overflow-hidden">
                        <div class="pt-4 px-4">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center fw-bold fs-3 mx-auto mb-3" style="width:80px; height:80px;">
                                <?= strtoupper(substr($g['nama_lengkap'], 0, 1)) ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($g['nama_lengkap']) ?></h6>
                            <small class="text-muted">NIP: <?= htmlspecialchars($g['nip']) ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Kontak & Google Maps -->
<section id="kontak" class="py-5">
    <div class="container py-4">
        <div class="row gy-4">
            <div class="col-lg-5">
                <span class="text-primary fw-bold text-uppercase">Hubungi Kami</span>
                <h2 class="fw-bold mb-4">Lokasi & Kontak Sekolah</h2>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <i class="bi bi-geo-alt-fill fs-4 text-danger"></i>
                    <div>
                        <h6 class="fw-bold mb-0">Alamat Lengkap</h6>
                        <p class="small text-muted">Jl. Raya Cicalengka, Kab. Bandung, Jawa Barat 40395</p>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <i class="bi bi-telephone-fill fs-4 text-success"></i>
                    <div>
                        <h6 class="fw-bold mb-0">Telepon / WhatsApp</h6>
                        <p class="small text-muted">+62 812-3456-7890</p>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-envelope-fill fs-4 text-primary"></i>
                    <div>
                        <h6 class="fw-bold mb-0">Email Resmi</h6>
                        <p class="small text-muted">info@smkmh-cicalengka.sch.id</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow">
                    <iframe src="https://maps.google.com/maps?q=Cicalengka&t=&z=13&ie=UTF8&iwloc=&output=embed"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="bg-dark text-white pt-5 pb-4">
    <div class="container text-center">
        <h5 class="fw-bold text-warning mb-2">SMK Muthia Harapan Cicalengka</h5>
        <p class="small text-white-50 mb-3">&copy; <?= date('Y') ?> All Rights Reserved. Production Ready E-Learning System.</p>
    </div>
</footer>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
