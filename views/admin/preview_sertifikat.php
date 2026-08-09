<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>

<?php
$templateType = $_GET['template'] ?? ($settings['sertifikat_active_template'] ?? 'kelulusan');

$templateTitles = [
    'kelulusan' => 'SERTIFIKAT KELULUSAN LMS',
    'prestasi' => 'SERTIFIKAT PRESTASI AKADEMIK',
    'ukk' => 'SERTIFIKAT UJI KOMPETENSI KEAHLIAN (UKK)'
];

$templateTitle = $templateTitles[$templateType] ?? 'SERTIFIKAT PENGHARGAAN';
$schoolName = $settings['nama_sekolah'] ?? 'SMK MUTHIA HARAPAN CICALENGKA';
$kepalaSekolah = $settings['kepala_sekolah'] ?? 'H. Supriyadi, M.M.';
$alamat = $settings['alamat'] ?? 'Jl. Raya Cicalengka No. 45, Cicalengka, Kabupaten Bandung';

$rawLogo = $settings['logo'] ?? '';
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
.cert-container {
    background: #ffffff;
    border: 12px double #0d6efd;
    border-radius: 16px;
    padding: 40px;
    position: relative;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    background-image: radial-gradient(circle at center, rgba(13,110,253,0.02) 0%, transparent 70%);
}
@media print {
    .no-print { display: none !important; }
    body { background: white !important; padding: 0 !important; margin: 0 !important; }
    .cert-container { border: 10px double #000 !important; box-shadow: none !important; margin: 0 !important; padding: 30px !important; }
}
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-award-fill text-warning me-2"></i>Preview Certificate & Print View</h4>
            <p class="text-muted small mb-0">Pratinjau resmi sertifikat digital sebelum diterbitkan atau dicetak.</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-warning text-dark fw-bold px-4 shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> Cetak / Download PDF
            </button>
            <a href="<?= BASE_URL ?>index.php?url=admin/sertifikat" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Certificate Design Box -->
    <div class="row justify-content-center">
        <div class="col-12 col-lg-11">
            <div class="cert-container text-center">

                <!-- Header Brand Logo -->
                <div class="d-flex justify-content-center align-items-center gap-3 mb-3">
                    <?php if ($logoUrl): ?>
                        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Sekolah" style="height:75px; max-width:220px; object-fit:contain;">
                    <?php else: ?>
                        <div class="bg-primary text-white rounded-4 p-3 px-4 shadow-sm me-2 d-inline-flex align-items-center justify-content-center" style="width:75px; height:75px;">
                            <i class="bi bi-mortarboard-fill fs-1"></i>
                        </div>
                    <?php endif; ?>
                    <div class="text-start">
                        <h3 class="fw-bold mb-0 text-primary" style="letter-spacing: 1px;"><?= htmlspecialchars(strtoupper($schoolName)) ?></h3>
                        <small class="text-muted fs-6"><?= htmlspecialchars($alamat) ?></small>
                    </div>
                </div>

                <hr class="border-2 border-primary opacity-75 my-3">

                <!-- Certificate Badge & Title -->
                <div class="py-2">
                    <span class="badge bg-warning text-dark px-4 py-2 fs-6 rounded-pill text-uppercase shadow-sm">
                        <i class="bi bi-award-fill me-1"></i> <?= htmlspecialchars($templateTitle) ?>
                    </span>
                </div>

                <p class="text-muted mb-1 fs-6 mt-3">Diberikan secara sah dan resmi kepada:</p>
                <h1 class="fw-bold text-primary display-5 my-2"><?= htmlspecialchars($siswa['nama_lengkap'] ?? 'Siswa SMK Muthia Harapan') ?></h1>
                <p class="text-muted mb-1 fs-6">
                    NIS: <strong><?= htmlspecialchars($siswa['nis'] ?? '222310001') ?></strong> &nbsp;|&nbsp; 
                    NISN: <strong><?= htmlspecialchars($siswa['nisn'] ?? '0051234567') ?></strong>
                </p>
                <p class="text-muted mb-4 fs-6">
                    Kelas: <strong><?= htmlspecialchars($siswa['nama_kelas'] ?? 'X RPL 1') ?></strong> &nbsp;•&nbsp; 
                    Kompetensi Keahlian: <strong><?= htmlspecialchars($siswa['nama_jurusan'] ?? 'Rekayasa Perangkat Lunak') ?></strong>
                </p>

                <!-- Body Description -->
                <div class="bg-light rounded-4 p-4 mx-auto mb-4 border" style="max-width: 680px;">
                    <p class="fs-6 text-muted mb-0 fst-italic">
                        <?php if ($templateType === 'prestasi'): ?>
                            Telah berhasil meraih <strong>Prestasi Akademik Terbaik (Peringkat Utama)</strong> pada Evaluasi Pembelajaran LMS Semester Ganjil Tahun Pelajaran 2025/2026 dengan dedikasi dan nilai keunggulan yang luar biasa.
                        <?php elseif ($templateType === 'ukk'): ?>
                            Telah lulus dan memenuhi standar kualifikasi pada <strong>Uji Kompetensi Keahlian (UKK) Kejuruan SMK</strong> Tahun Pelajaran 2025/2026 dengan predikat Sangat Memuaskan.
                        <?php else: ?>
                            Telah menyelesaikan seluruh modul pembelajaran digital, tugas, evaluasi, dan kuis online pada Portal E-Learning SMK Muthia Harapan Cicalengka dengan predikat hasil yang sangat memuaskan.
                        <?php endif; ?>
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

                <!-- Footer Signatures & QR -->
                <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mt-4 pt-2">
                    <div class="text-start">
                        <small class="text-muted d-block">Nomor Registrasi Sertifikat:</small>
                        <code class="fs-6 text-primary fw-bold">SMKMH/CERT/<?= date('Y') ?>/<?= str_pad($siswa['id'] ?? 1, 4, '0', STR_PAD_LEFT) ?></code>
                        <small class="text-muted d-block mt-1">Diterbitkan pada: <?= date('d F Y') ?></small>
                    </div>

                    <div class="text-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=VERIFIED-CERT-<?= urlencode($siswa['nisn'] ?? 'SMKMH001') ?>&color=0d6efd&bgcolor=ffffff"
                             alt="QR Code Verifikasi" width="90" height="90" class="rounded-3 border border-2 border-primary shadow-sm">
                        <small class="text-muted d-block mt-1" style="font-size:0.7rem;">Pindai untuk Verifikasi Digital</small>
                    </div>

                    <div class="text-center" style="min-width: 180px;">
                        <small class="text-muted d-block mb-4">Cicalengka, <?= date('d F Y') ?></small>
                        <div class="fw-bold text-dark border-bottom border-dark pb-1 mb-1"><?= htmlspecialchars($kepalaSekolah) ?></div>
                        <small class="text-muted">Kepala Sekolah</small>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
