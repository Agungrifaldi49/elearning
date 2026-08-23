<?php
$user = AuthHelper::user();
$role = strtolower($user['role_name'] ?? '');
$currentUrl = $_GET['url'] ?? '';

function isActive($currentUrl, $segment) {
    return strpos($currentUrl, $segment) !== false ? 'active' : '';
}

$sidebarSettings = [];
$sidebarSettingsPath = ROOT_PATH . 'config/settings.json';
if (file_exists($sidebarSettingsPath)) {
    $sidebarSettings = json_decode(file_get_contents($sidebarSettingsPath), true) ?: [];
}
$sidebarSchoolName = $sidebarSettings['nama_sekolah'] ?? 'SMK Muthia Harapan';
$sidebarRawLogo = $sidebarSettings['logo'] ?? '';
$sidebarLogo = null;
if (!empty($sidebarRawLogo)) {
    if (strpos($sidebarRawLogo, 'assets/uploads/') === 0 || strpos($sidebarRawLogo, 'uploads/') === 0) {
        $sidebarLogo = BASE_URL . $sidebarRawLogo;
    } else {
        $sidebarLogo = BASE_URL . 'assets/uploads/logo/' . $sidebarRawLogo;
    }
}
?>
<aside class="app-sidebar p-0 d-flex flex-column" id="appSidebar">
    <!-- Brand Header -->
    <div class="px-4 py-3 border-bottom d-flex align-items-center gap-2">
        <?php if ($sidebarLogo): ?>
            <img src="<?= htmlspecialchars($sidebarLogo) ?>" alt="Logo" class="rounded-3 flex-shrink-0" style="width:36px; height:36px; object-fit:contain;">
        <?php else: ?>
            <div class="bg-primary text-white p-2 rounded-3 flex-shrink-0">
                <i class="bi bi-mortarboard-fill fs-5"></i>
            </div>
        <?php endif; ?>
        <div class="overflow-hidden">
            <h6 class="fw-bold mb-0 text-primary small text-truncate"><?= htmlspecialchars($sidebarSchoolName) ?></h6>
            <small class="text-muted" style="font-size: 0.7rem;">Portal E-Learning Cicalengka</small>
        </div>
    </div>

    <!-- Navigation Scroll Area -->
    <div class="overflow-auto flex-grow-1 py-2 px-2">
        <ul class="nav nav-pills flex-column gap-1">

        <!-- ====================================================================================
             1. ADMINISTRATOR
        ==================================================================================== -->
        <?php if ($role === 'administrator'): ?>

            <li class="nav-section-title">Dashboard & Panduan</li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/dashboard') ?>" href="<?= BASE_URL ?>index.php?url=admin/dashboard">
                <i class="bi bi-grid-1x2-fill"></i> Ringkasan Sistem
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/panduan') ?>" href="<?= BASE_URL ?>index.php?url=admin/panduan">
                <i class="bi bi-book-half text-warning me-1"></i> Panduan Pengguna Admin
            </a></li>

            <li class="nav-section-title">Master Data</li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/guru') ?>" href="<?= BASE_URL ?>index.php?url=admin/guru">
                <i class="bi bi-person-badge-fill"></i> Data Guru
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/siswa') ?>" href="<?= BASE_URL ?>index.php?url=admin/siswa">
                <i class="bi bi-people-fill"></i> Data Siswa
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/akademik') ?>" href="<?= BASE_URL ?>index.php?url=admin/akademik">
                <i class="bi bi-journal-bookmark-fill"></i> Kelas, Jurusan & Mapel
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/tahunAjaran') ?>" href="<?= BASE_URL ?>index.php?url=admin/tahunAjaran">
                <i class="bi bi-calendar-event-fill text-warning"></i> Tahun Ajaran & Semester
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/enrollmentKey') ?>" href="<?= BASE_URL ?>index.php?url=admin/enrollmentKey">
                <i class="bi bi-key-fill text-primary"></i> Key & Kode Akses Mapel
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/users') ?>" href="<?= BASE_URL ?>index.php?url=admin/users">
                <i class="bi bi-person-gear"></i> Hak Akses & User
            </a></li>

            <li class="nav-section-title">Learning Management</li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/kelasVirtual') ?>" href="<?= BASE_URL ?>index.php?url=admin/kelasVirtual">
                <i class="bi bi-bounding-box-circles"></i> Kelas Virtual
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/materi') ?>" href="<?= BASE_URL ?>index.php?url=admin/materi">
                <i class="bi bi-book-fill"></i> Modul & Materi Pembelajaran
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/tugas') ?>" href="<?= BASE_URL ?>index.php?url=admin/tugas">
                <i class="bi bi-card-checklist"></i> Tugas & Evaluasi
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'game') ?>" href="<?= BASE_URL ?>index.php?url=game">
                <i class="bi bi-controller text-danger me-1"></i> Game Edukasi
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/quiz') ?>" href="<?= BASE_URL ?>index.php?url=admin/quiz">
                <i class="bi bi-patch-question-fill"></i> Quiz & CBT Ujian
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/bankSoal') ?>" href="<?= BASE_URL ?>index.php?url=admin/bankSoal">
                <i class="bi bi-database"></i> Bank Soal Terpusat
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/sertifikat') ?>" href="<?= BASE_URL ?>index.php?url=admin/sertifikat">
                <i class="bi bi-award-fill"></i> Template Sertifikat Digital
            </a></li>

            <li class="nav-section-title">Penilaian & Presensi</li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/jadwal') ?>" href="<?= BASE_URL ?>index.php?url=admin/jadwal">
                <i class="bi bi-clock-history text-info"></i> Jadwal Pelajaran Sekolah
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/inputNilai') ?>" href="<?= BASE_URL ?>index.php?url=admin/inputNilai">
                <i class="bi bi-pencil-fill"></i> Rekap Nilai E-Rapor
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/absensi') ?>" href="<?= BASE_URL ?>index.php?url=admin/absensi">
                <i class="bi bi-calendar-check-fill"></i> Presensi Guru & Siswa
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/recapBulanan') ?>" href="<?= BASE_URL ?>index.php?url=admin/recapBulanan">
                <i class="bi bi-file-earmark-spreadsheet-fill text-success"></i> Rekap Absensi Bulanan
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/scanQr') ?>" href="<?= BASE_URL ?>index.php?url=admin/scanQr">
                <i class="bi bi-qr-code-scan text-success"></i> Scan QR Code Hadir
            </a></li>

            <li class="nav-section-title">Konten & Komunikasi</li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/landingPage') ?>" href="<?= BASE_URL ?>index.php?url=admin/landingPage">
                <i class="bi bi-window-stack text-warning"></i> Kelola Landing Page & Visi Misi
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/pengumuman') ?>" href="<?= BASE_URL ?>index.php?url=admin/pengumuman">
                <i class="bi bi-megaphone-fill text-danger"></i> Kelola Pengumuman & Informasi
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'forum') ?>" href="<?= BASE_URL ?>index.php?url=forum">
                <i class="bi bi-chat-square-quote-fill"></i> Forum Diskusi
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'chat') ?>" href="<?= BASE_URL ?>index.php?url=chat">
                <i class="bi bi-chat-dots-fill"></i> Chat Realtime
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'library') ?>" href="<?= BASE_URL ?>index.php?url=library">
                <i class="bi bi-bookshelf"></i> Perpustakaan Digital
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/kalender') ?>" href="<?= BASE_URL ?>index.php?url=admin/kalender">
                <i class="bi bi-calendar3"></i> Kalender Akademik
            </a></li>

            <li class="nav-section-title">Laporan & Pengaturan</li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/laporan') ?>" href="<?= BASE_URL ?>index.php?url=admin/laporan">
                <i class="bi bi-graph-up-arrow"></i> Laporan & Analitik LMS
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/pengaturan') ?>" href="<?= BASE_URL ?>index.php?url=admin/pengaturan">
                <i class="bi bi-gear-fill"></i> Pengaturan & Profil Sekolah
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/logs') ?>" href="<?= BASE_URL ?>index.php?url=admin/logs">
                <i class="bi bi-shield-check"></i> Audit Log & Keamanan
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'admin/backup') ?>" href="<?= BASE_URL ?>index.php?url=admin/backup">
                <i class="bi bi-database-fill-gear"></i> Backup & Restore Database
            </a></li>

        <!-- ====================================================================================
             2. GURU
        ==================================================================================== -->
        <?php elseif ($role === 'guru'): ?>

            <li class="nav-section-title">Dashboard & Panduan</li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/dashboard') ?>" href="<?= BASE_URL ?>index.php?url=guru/dashboard">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard Mengajar
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/panduan') ?>" href="<?= BASE_URL ?>index.php?url=guru/panduan">
                <i class="bi bi-book-half text-warning me-1"></i> Panduan Pengguna Guru
            </a></li>

            <li class="nav-section-title">Kelas Saya & Learning Path</li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/kelasVirtual') ?>" href="<?= BASE_URL ?>index.php?url=guru/kelasVirtual">
                <i class="bi bi-bounding-box-circles"></i> Kelas Virtual Saya
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/learningPath') ?>" href="<?= BASE_URL ?>index.php?url=guru/learningPath">
                <i class="bi bi-compass-fill"></i> Urutan Learning Path
            </a></li>

            <li class="nav-section-title">Materi Pembelajaran</li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/materi') ?>" href="<?= BASE_URL ?>index.php?url=guru/materi">
                <i class="bi bi-cloud-upload-fill"></i> Upload Materi & Video
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'library') ?>" href="<?= BASE_URL ?>index.php?url=library">
                <i class="bi bi-bookshelf"></i> Perpustakaan Digital
            </a></li>

            <li class="nav-section-title">Tugas & Evaluasi</li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/tugas') ?>" href="<?= BASE_URL ?>index.php?url=guru/tugas">
                <i class="bi bi-card-checklist"></i> Kelola Tugas & Rubrik
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'game') ?>" href="<?= BASE_URL ?>index.php?url=game">
                <i class="bi bi-controller text-danger me-1"></i> Game Edukasi
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/quiz') ?>" href="<?= BASE_URL ?>index.php?url=guru/quiz">
                <i class="bi bi-patch-question-fill"></i> Quiz & Ujian CBT
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/bankSoal') ?>" href="<?= BASE_URL ?>index.php?url=guru/bankSoal">
                <i class="bi bi-database"></i> Bank Soal & Analisis
            </a></li>

            <li class="nav-section-title">Penilaian & Presensi</li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/jadwal') ?>" href="<?= BASE_URL ?>index.php?url=guru/jadwal">
                <i class="bi bi-clock-history text-warning"></i> Jadwal Mengajar Saya
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/inputNilai') ?>" href="<?= BASE_URL ?>index.php?url=guru/inputNilai">
                <i class="bi bi-pencil-fill"></i> Input Nilai E-Rapor
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/absensi') ?>" href="<?= BASE_URL ?>index.php?url=guru/absensi">
                <i class="bi bi-calendar-check-fill"></i> Presensi Siswa
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/recapBulanan') ?>" href="<?= BASE_URL ?>index.php?url=guru/recapBulanan">
                <i class="bi bi-file-earmark-spreadsheet-fill text-success"></i> Rekap Absensi Bulanan
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/scanQr') ?>" href="<?= BASE_URL ?>index.php?url=guru/scanQr">
                <i class="bi bi-qr-code-scan"></i> Scan QR Code Hadir
            </a></li>

            <li class="nav-section-title">Komunikasi & Virtual Meeting</li>
            <li><a class="nav-link <?= isActive($currentUrl,'forum') ?>" href="<?= BASE_URL ?>index.php?url=forum">
                <i class="bi bi-chat-square-quote-fill"></i> Forum Diskusi
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'chat') ?>" href="<?= BASE_URL ?>index.php?url=chat">
                <i class="bi bi-chat-dots-fill"></i> Chat Siswa
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/liveClass') ?>" href="<?= BASE_URL ?>index.php?url=guru/liveClass">
                <i class="bi bi-camera-reels-fill text-danger"></i> Live Virtual Meeting Room
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/profil') ?>" href="<?= BASE_URL ?>index.php?url=guru/profil">
                <i class="bi bi-person-circle"></i> Profil Saya
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'guru/kartuGuru') ?>" href="<?= BASE_URL ?>index.php?url=guru/kartuGuru">
                <i class="bi bi-person-badge-fill text-warning"></i> Kartu Guru Digital (QR)
            </a></li>

        <!-- ====================================================================================
             3. SISWA
        ==================================================================================== -->
        <?php elseif ($role === 'siswa'): ?>

            <li class="nav-section-title">Dashboard & Panduan</li>
            <li><a class="nav-link <?= isActive($currentUrl,'siswa/dashboard') ?>" href="<?= BASE_URL ?>index.php?url=siswa/dashboard">
                <i class="bi bi-grid-1x2-fill"></i> Beranda Siswa
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'siswa/panduan') ?>" href="<?= BASE_URL ?>index.php?url=siswa/panduan">
                <i class="bi bi-book-half text-warning me-1"></i> Panduan Pengguna Siswa
            </a></li>

            <li class="nav-section-title">Pembelajaran & Path</li>
            <li><a class="nav-link <?= isActive($currentUrl,'siswa/gabungKelas') ?>" href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas">
                <i class="bi bi-bounding-box-circles text-primary me-1"></i> Gabung Kelas Virtual
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'siswa/materi') ?>" href="<?= BASE_URL ?>index.php?url=siswa/materi">
                <i class="bi bi-book-fill"></i> Materi & Video Learning
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'siswa/learningPath') ?>" href="<?= BASE_URL ?>index.php?url=siswa/learningPath">
                <i class="bi bi-compass-fill"></i> Learning Path Progress
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'library') ?>" href="<?= BASE_URL ?>index.php?url=library">
                <i class="bi bi-bookshelf"></i> Perpustakaan Digital
            </a></li>

            <li class="nav-section-title">Tugas & Evaluasi</li>
            <li><a class="nav-link <?= isActive($currentUrl,'siswa/tugas') ?>" href="<?= BASE_URL ?>index.php?url=siswa/tugas">
                <i class="bi bi-pencil-square"></i> Kerjakan Tugas
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'game') ?>" href="<?= BASE_URL ?>index.php?url=game">
                <i class="bi bi-controller text-danger me-1"></i> Game Edukasi
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'siswa/quiz') ?>" href="<?= BASE_URL ?>index.php?url=siswa/quiz">
                <i class="bi bi-stopwatch-fill"></i> Kuis & Ujian CBT
            </a></li>

            <li class="nav-section-title">Hasil & Identitas Digital</li>
            <li><a class="nav-link <?= isActive($currentUrl,'siswa/nilai') ?>" href="<?= BASE_URL ?>index.php?url=siswa/nilai">
                <i class="bi bi-trophy-fill"></i> Hasil Nilai & Quiz
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'siswa/rapor') ?>" href="<?= BASE_URL ?>index.php?url=siswa/rapor">
                <i class="bi bi-file-earmark-text-fill"></i> E-Rapor Digital
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'siswa/sertifikat') ?>" href="<?= BASE_URL ?>index.php?url=siswa/sertifikat">
                <i class="bi bi-award-fill"></i> Sertifikat Saya
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'siswa/kartuPelajar') ?>" href="<?= BASE_URL ?>index.php?url=siswa/kartuPelajar">
                <i class="bi bi-credit-card-fill"></i> Kartu Pelajar Digital
            </a></li>

            <li class="nav-section-title">Komunikasi & Virtual Meeting</li>
            <li><a class="nav-link <?= isActive($currentUrl,'forum') ?>" href="<?= BASE_URL ?>index.php?url=forum">
                <i class="bi bi-chat-square-quote-fill"></i> Forum Diskusi
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'chat') ?>" href="<?= BASE_URL ?>index.php?url=chat">
                <i class="bi bi-chat-dots-fill"></i> Pesan Guru
            </a></li>
            <li><a class="nav-link" href="https://meet.google.com" target="_blank">
                <i class="bi bi-camera-reels-fill text-danger"></i> Join Live Meeting
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'siswa/profil') ?>" href="<?= BASE_URL ?>index.php?url=siswa/profil">
                <i class="bi bi-person-circle"></i> Profil Saya
            </a></li>

        <!-- ====================================================================================
             4. KEPALA SEKOLAH
        ==================================================================================== -->
        <?php elseif ($role === 'kepala sekolah'): ?>

            <li class="nav-section-title">Dashboard & Panduan</li>
            <li><a class="nav-link <?= isActive($currentUrl,'kepsek/dashboard') ?>" href="<?= BASE_URL ?>index.php?url=kepsek/dashboard">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard Eksekutif
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'kepsek/panduan') ?>" href="<?= BASE_URL ?>index.php?url=kepsek/panduan">
                <i class="bi bi-book-half text-warning me-1"></i> Panduan Pengguna Kepsek
            </a></li>

            <li class="nav-section-title">Monitoring & Analitik</li>
            <li><a class="nav-link <?= isActive($currentUrl,'kepsek/monitoringGuru') ?>" href="<?= BASE_URL ?>index.php?url=kepsek/monitoringGuru">
                <i class="bi bi-person-check-fill"></i> Monitoring Guru
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'kepsek/monitoringSiswa') ?>" href="<?= BASE_URL ?>index.php?url=kepsek/monitoringSiswa">
                <i class="bi bi-graph-up-arrow"></i> Monitoring Siswa & Progress
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'kepsek/monitoringPembelajaran') ?>" href="<?= BASE_URL ?>index.php?url=kepsek/monitoringPembelajaran">
                <i class="bi bi-display-fill"></i> Monitoring Pembelajaran Virtual
            </a></li>

            <li class="nav-section-title">Laporan & Profil</li>
            <li><a class="nav-link <?= isActive($currentUrl,'kepsek/cetakLaporan') ?>" href="<?= BASE_URL ?>index.php?url=kepsek/cetakLaporan&type=guru" target="_blank">
                <i class="bi bi-printer-fill"></i> Laporan Data Guru (PDF)
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'kepsek/laporan') ?>" href="<?= BASE_URL ?>index.php?url=kepsek/cetakLaporan&type=siswa" target="_blank">
                <i class="bi bi-file-earmark-pdf-fill"></i> Laporan Data Siswa (PDF)
            </a></li>
            <li><a class="nav-link <?= isActive($currentUrl,'kepsek/profil') ?>" href="<?= BASE_URL ?>index.php?url=kepsek/profil">
                <i class="bi bi-person-circle"></i> Profil Eksekutif
            </a></li>

        <?php endif; ?>

        </ul>
    </div>

    <!-- Footer Profile Sidebar -->
    <div class="px-3 py-3 border-top mt-auto">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width:34px; height:34px; font-size:0.85rem;">
                <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="overflow-hidden flex-grow-1">
                <div class="fw-bold small text-truncate"><?= htmlspecialchars($user['full_name'] ?? '') ?></div>
                <small class="text-muted" style="font-size:0.7rem;"><?= htmlspecialchars($user['role_name'] ?? '') ?></small>
            </div>
            <a href="<?= BASE_URL ?>logout.php" class="btn btn-sm btn-outline-danger p-1 px-2 rounded-3 flex-shrink-0" title="Keluar">
                <i class="bi bi-power"></i>
            </a>
        </div>
    </div>
</aside>
