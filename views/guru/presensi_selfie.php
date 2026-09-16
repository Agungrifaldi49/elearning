<?php 
require_once ROOT_PATH . 'views/layouts/header.php'; 
require_once ROOT_PATH . 'views/layouts/navbar.php'; 
require_once ROOT_PATH . 'views/layouts/sidebar.php'; 

$lokasiNama = $settings['lokasi_sekolah_nama'] ?? 'SMK Muthia Harapan Cicalengka';
$lokasiLat = isset($settings['lokasi_sekolah_lat']) ? (float)$settings['lokasi_sekolah_lat'] : -6.984042;
$lokasiLng = isset($settings['lokasi_sekolah_lng']) ? (float)$settings['lokasi_sekolah_lng'] : 107.838612;
$lokasiRadius = isset($settings['lokasi_sekolah_radius']) ? (int)$settings['lokasi_sekolah_radius'] : 150;
$jamMasukMulai = $settings['presensi_jam_masuk_mulai'] ?? '06:00';
$jamMasukBatas = $settings['presensi_jam_masuk_batas'] ?? '07:30';
$jamPulangMulai = $settings['presensi_jam_pulang_mulai'] ?? '15:00';

$sudahMasuk = !empty($presensiHariIni['waktu_masuk']) && $presensiHariIni['waktu_masuk'] !== '0000-00-00 00:00:00';
$sudahPulang = !empty($presensiHariIni['waktu_pulang']) && $presensiHariIni['waktu_pulang'] !== '0000-00-00 00:00:00';
$waktuMasukDisplay = $sudahMasuk ? date('H:i', strtotime($presensiHariIni['waktu_masuk'])) : '';
$waktuPulangDisplay = $sudahPulang ? date('H:i', strtotime($presensiHariIni['waktu_pulang'])) : '';
?>

<!-- Leaflet Map CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
/* Modern Styling for Presensi Selfie & Geofencing */
.selfie-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.camera-container {
    position: relative;
    width: 100%;
    aspect-ratio: 4/3;
    max-height: 380px;
    background: #0f172a;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.camera-video, .camera-preview-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scaleX(-1); /* Mirror view for front camera */
}

.camera-video.rear-cam {
    transform: none;
}

/* Biometric Oval Face Guide */
.face-guide-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 180px;
    height: 240px;
    border: 2px dashed rgba(16, 185, 129, 0.85);
    border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
    box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.45);
    pointer-events: none;
    z-index: 10;
    transition: all 0.3s ease;
}

.face-guide-overlay.warning {
    border-color: #ef4444;
}

.face-guide-text {
    position: absolute;
    bottom: 12px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(4px);
    color: #ffffff;
    font-size: 0.75rem;
    padding: 4px 12px;
    border-radius: 20px;
    white-space: nowrap;
    z-index: 11;
    pointer-events: none;
}

/* Digital Clock */
.clock-display {
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    font-weight: 800;
    letter-spacing: -0.5px;
    background: linear-gradient(135deg, #1e293b, #0f172a);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Geofence Status Badges */
.geofence-radar-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    animation: radar-pulse 1.8s infinite;
}

.geofence-radar-dot.in-range {
    background-color: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
}

.geofence-radar-dot.out-range {
    background-color: #ef4444;
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
}

@keyframes radar-pulse {
    0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }
    70% {
        transform: scale(1);
        box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
    }
    100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
    }
}

.badge-soft-success {
    background-color: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.badge-soft-danger {
    background-color: rgba(239, 68, 68, 0.12);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

.badge-soft-warning {
    background-color: rgba(245, 158, 11, 0.12);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.25);
}

.badge-soft-primary {
    background-color: rgba(79, 70, 229, 0.12);
    color: #4f46e5;
    border: 1px solid rgba(79, 70, 229, 0.25);
}

.selfie-thumb {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid #e2e8f0;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.selfie-thumb:hover {
    transform: scale(1.08);
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
}
</style>

<main class="main-content px-3 px-md-4 py-3">
<div class="container-fluid">

    <!-- Header & Clock -->
    <div class="row align-items-center mb-4 gy-3">
        <div class="col-12 col-md-7">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4">
                    <i class="bi bi-camera-fill fs-2"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Presensi Selfie Mandiri Guru</h4>
                    <p class="text-muted small mb-0">Presensi kehadiran GTK menggunakan kamera wajah & validasi koordinat GPS Geofencing radius sekolah.</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-5 text-md-end">
            <div class="d-inline-flex flex-column align-items-md-end bg-white px-4 py-2 rounded-4 border shadow-xs">
                <div class="d-flex align-items-baseline gap-2">
                    <span class="clock-display fs-3" id="liveClock">00:00:00</span>
                    <span class="badge bg-dark text-white rounded-pill px-2 py-1 small">WIB</span>
                </div>
                <div class="small text-muted fw-medium" id="liveDate"><?= date('l, d F Y') ?></div>
            </div>
        </div>
    </div>

    <!-- Status Presensi Hari Ini Cards -->
    <div class="row g-3 mb-4">
        <!-- Card Masuk -->
        <div class="col-12 col-md-6">
            <div class="card selfie-card p-3 p-md-4 border-start border-4 <?= $sudahMasuk ? 'border-success' : 'border-secondary' ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="badge <?= $sudahMasuk ? 'badge-soft-success' : 'badge-soft-warning' ?> rounded-pill px-3 py-1 mb-2 fw-bold">
                            <i class="bi <?= $sudahMasuk ? 'bi-check-circle-fill' : 'bi-hourglass-split' ?> me-1"></i> Presensi Masuk
                        </span>
                        <h5 class="fw-bold text-dark mb-1">
                            <?= $sudahMasuk ? $waktuMasukDisplay . ' WIB' : 'Belum Melakukan Presensi' ?>
                        </h5>
                        <p class="text-muted small mb-0">
                            <?php if ($sudahMasuk): ?>
                                Status: <span class="badge <?= ($presensiHariIni['status'] ?? '') === 'Hadir' ? 'bg-success' : 'bg-warning text-dark' ?> rounded-pill px-2 py-0.5"><?= htmlspecialchars($presensiHariIni['status'] ?? 'Hadir') ?></span>
                                &bull; Jarak: <b><?= htmlspecialchars($presensiHariIni['jarak_masuk_meter'] ?? '0') ?>m</b> dari sekolah
                            <?php else: ?>
                                Batas jam masuk tepat waktu: <b><?= htmlspecialchars($jamMasukBatas) ?> WIB</b>
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php if ($sudahMasuk && !empty($presensiHariIni['foto_masuk'])): ?>
                        <div class="text-end">
                            <img src="<?= BASE_URL . htmlspecialchars($presensiHariIni['foto_masuk']) ?>" 
                                 class="selfie-thumb shadow-xs" 
                                 title="Klik untuk melihat foto selfie masuk" 
                                 onclick="previewSelfieModal('<?= BASE_URL . htmlspecialchars($presensiHariIni['foto_masuk']) ?>', 'Presensi Masuk (<?= $waktuMasukDisplay ?> WIB)')">
                            <small class="d-block text-muted mt-1" style="font-size: 0.7rem;">Selfie Masuk</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card Pulang -->
        <div class="col-12 col-md-6">
            <div class="card selfie-card p-3 p-md-4 border-start border-4 <?= $sudahPulang ? 'border-primary' : 'border-secondary' ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="badge <?= $sudahPulang ? 'badge-soft-primary' : 'badge-soft-warning' ?> rounded-pill px-3 py-1 mb-2 fw-bold">
                            <i class="bi <?= $sudahPulang ? 'bi-door-open-fill' : 'bi-hourglass-split' ?> me-1"></i> Presensi Pulang
                        </span>
                        <h5 class="fw-bold text-dark mb-1">
                            <?= $sudahPulang ? $waktuPulangDisplay . ' WIB' : ($sudahMasuk ? 'Menunggu Jam Pulang' : 'Belum Presensi Masuk') ?>
                        </h5>
                        <p class="text-muted small mb-0">
                            <?php if ($sudahPulang): ?>
                                Jam Checkout: <b><?= $waktuPulangDisplay ?> WIB</b>
                                &bull; Jarak: <b><?= htmlspecialchars($presensiHariIni['jarak_pulang_meter'] ?? '0') ?>m</b> dari sekolah
                            <?php else: ?>
                                Jam buka kepulangan: <b><?= htmlspecialchars($jamPulangMulai) ?> WIB</b>
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php if ($sudahPulang && !empty($presensiHariIni['foto_pulang'])): ?>
                        <div class="text-end">
                            <img src="<?= BASE_URL . htmlspecialchars($presensiHariIni['foto_pulang']) ?>" 
                                 class="selfie-thumb shadow-xs" 
                                 title="Klik untuk melihat foto selfie pulang" 
                                 onclick="previewSelfieModal('<?= BASE_URL . htmlspecialchars($presensiHariIni['foto_pulang']) ?>', 'Presensi Pulang (<?= $waktuPulangDisplay ?> WIB)')">
                            <small class="d-block text-muted mt-1" style="font-size: 0.7rem;">Selfie Pulang</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Working Area: Camera Viewfinder & Geofencing Map -->
    <div class="row g-4 mb-4">
        <!-- Kolom Kiri: Kamera Selfie -->
        <div class="col-12 col-lg-6">
            <div class="card selfie-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-camera me-2 text-primary"></i>Kamera Selfie Wajah
                    </h5>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btnSwitchCamera">
                        <i class="bi bi-arrow-repeat me-1"></i> Ganti Kamera
                    </button>
                </div>

                <!-- Camera Container -->
                <div class="camera-container mb-3 shadow-inner" id="cameraBox">
                    <video id="webcamVideo" class="camera-video" autoplay playsinline muted></video>
                    <img id="capturedPhotoPreview" class="camera-preview-img d-none" alt="Selfie Preview">
                    
                    <!-- Biometric Overlay Frame -->
                    <div class="face-guide-overlay" id="faceGuide"></div>
                    <div class="face-guide-text" id="faceGuideText">
                        <i class="bi bi-person-bounding-box me-1"></i> Posisikan wajah di dalam bingkai
                    </div>
                </div>

                <!-- Hidden Canvas for Watermark Processing -->
                <canvas id="canvasCapture" class="d-none"></canvas>

                <!-- Camera Action Controls -->
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-dark flex-grow-1 py-2.5 rounded-3 fw-bold shadow-sm" id="btnCapturePhoto">
                        <i class="bi bi-camera-fill me-1 text-warning"></i> Ambil Foto Selfie
                    </button>
                    <button type="button" class="btn btn-outline-danger py-2.5 px-3 rounded-3 fw-bold d-none" id="btnRetakePhoto">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Foto Ulang
                    </button>
                </div>

                <!-- Keterangan / Catatan Tambahan (Optional) -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Keterangan / Catatan (Opsional)</label>
                    <input type="text" id="presensiKeterangan" class="form-control rounded-3" placeholder="Contoh: Mengikuti rapat pagi / Tepat waktu">
                </div>

                <!-- Presensi Action Buttons -->
                <div class="row g-2 mt-auto">
                    <div class="col-6">
                        <button type="button" class="btn btn-success w-100 py-2.5 rounded-3 fw-bold shadow-sm" id="btnSubmitMasuk" <?= $sudahMasuk ? 'disabled' : '' ?>>
                            <i class="bi bi-box-arrow-in-right me-1"></i> <?= $sudahMasuk ? 'Sudah Masuk' : 'Presensi Masuk' ?>
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-primary w-100 py-2.5 rounded-3 fw-bold shadow-sm" id="btnSubmitPulang" <?= (!$sudahMasuk || $sudahPulang) ? 'disabled' : '' ?>>
                            <i class="bi bi-box-arrow-right me-1"></i> <?= $sudahPulang ? 'Sudah Pulang' : 'Presensi Pulang' ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Status Lokasi & Leaflet Geofence Map -->
        <div class="col-12 col-lg-6">
            <div class="card selfie-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-geo-alt-fill me-2 text-danger"></i>Titik Lokasi & Radius Presensi
                        </h5>
                        <small class="text-muted"><?= htmlspecialchars($lokasiNama) ?></small>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="btnRefreshGPS">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh GPS
                    </button>
                </div>

                <!-- GPS Status Bar -->
                <div class="p-3 rounded-4 mb-3 border bg-light" id="gpsStatusBar">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="geofence-radar-dot out-range" id="geofenceDot"></span>
                            <span class="fw-bold small text-dark" id="gpsStatusText">Mendeteksi koordinat GPS perangkat Anda...</span>
                        </div>
                        <span class="badge badge-soft-primary rounded-pill px-3 py-1" id="badgeAkurasi">Akurasi: Memuat...</span>
                    </div>

                    <div class="row g-2 text-muted small pt-2 border-top">
                        <div class="col-6">
                            <span>Jarak ke Sekolah:</span>
                            <b class="text-dark d-block fs-6" id="txtDistance">- meter</b>
                        </div>
                        <div class="col-6">
                            <span>Radius Maksimal:</span>
                            <b class="text-dark d-block fs-6"><?= $lokasiRadius ?> meter</b>
                        </div>
                    </div>
                </div>

                <!-- Leaflet Interactive Map -->
                <div class="mb-3" style="position: relative;">
                    <div id="guruMapContainer" style="height: 280px; width: 100%; border-radius: 14px; border: 1px solid rgba(0,0,0,0.1); z-index: 1;"></div>
                    <button type="button" class="btn btn-light btn-sm rounded-pill shadow-sm position-absolute" id="btnCenterMap" style="bottom: 15px; right: 15px; z-index: 400;">
                        <i class="bi bi-crosshair me-1 text-primary"></i> Pusatkan
                    </button>
                </div>

                <div class="alert alert-info py-2 px-3 small rounded-3 mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check fs-5 text-primary"></i>
                    <span>Sistem menggunakan enkripsi lokasi dan rumus Haversine server-side untuk menjamin keaslian data presensi.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Presensi Guru Terbaru -->
    <div class="card selfie-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Presensi Mandiri Anda</h5>
                <p class="text-muted small mb-0">Catatan kehadiran, waktu check-in/out, jarak geofencing, dan foto verifikasi wajah.</p>
            </div>
            <a href="<?= BASE_URL ?>index.php?url=guru/absensi&tab=guru" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-table me-1"></i> Lihat Rekap Lengkap
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">Tanggal</th>
                        <th class="border-0">Foto Selfie</th>
                        <th class="border-0">Waktu Masuk</th>
                        <th class="border-0">Waktu Pulang</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Jarak Lokasi</th>
                        <th class="border-0">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($riwayatPresensi)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                Belum ada riwayat presensi selfie tercatat.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($riwayatPresensi as $rw): ?>
                            <tr>
                                <td class="fw-bold text-dark">
                                    <?= date('d M Y', strtotime($rw['tanggal'])) ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <?php if (!empty($rw['foto_masuk'])): ?>
                                            <img src="<?= BASE_URL . htmlspecialchars($rw['foto_masuk']) ?>" 
                                                 class="selfie-thumb" 
                                                 title="Lihat Selfie Masuk"
                                                 onclick="previewSelfieModal('<?= BASE_URL . htmlspecialchars($rw['foto_masuk']) ?>', 'Selfie Masuk - <?= date('d M Y', strtotime($rw['tanggal'])) ?>')">
                                        <?php endif; ?>
                                        <?php if (!empty($rw['foto_pulang'])): ?>
                                            <img src="<?= BASE_URL . htmlspecialchars($rw['foto_pulang']) ?>" 
                                                 class="selfie-thumb" 
                                                 title="Lihat Selfie Pulang"
                                                 onclick="previewSelfieModal('<?= BASE_URL . htmlspecialchars($rw['foto_pulang']) ?>', 'Selfie Pulang - <?= date('d M Y', strtotime($rw['tanggal'])) ?>')">
                                        <?php endif; ?>
                                        <?php if (empty($rw['foto_masuk']) && empty($rw['foto_pulang'])): ?>
                                            <span class="badge bg-light text-muted border">QR/Manual</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($rw['waktu_masuk']) && $rw['waktu_masuk'] !== '0000-00-00 00:00:00'): ?>
                                        <span class="badge badge-soft-success rounded-pill px-2.5 py-1">
                                            <i class="bi bi-arrow-down-right me-1"></i><?= date('H:i', strtotime($rw['waktu_masuk'])) ?> WIB
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($rw['waktu_pulang']) && $rw['waktu_pulang'] !== '0000-00-00 00:00:00'): ?>
                                        <span class="badge badge-soft-primary rounded-pill px-2.5 py-1">
                                            <i class="bi bi-arrow-up-right me-1"></i><?= date('H:i', strtotime($rw['waktu_pulang'])) ?> WIB
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $st = $rw['status'] ?? 'Hadir';
                                    $stClass = 'bg-success';
                                    if ($st === 'Terlambat') $stClass = 'bg-warning text-dark';
                                    elseif (in_array($st, ['Izin', 'Sakit'])) $stClass = 'bg-info text-white';
                                    elseif ($st === 'Alfa') $stClass = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $stClass ?> rounded-pill px-2.5 py-1"><?= htmlspecialchars($st) ?></span>
                                </td>
                                <td>
                                    <small class="text-muted d-block">
                                        <?php if (!empty($rw['jarak_masuk_meter'])): ?>
                                            Masuk: <b><?= $rw['jarak_masuk_meter'] ?>m</b>
                                        <?php endif; ?>
                                        <?php if (!empty($rw['jarak_pulang_meter'])): ?>
                                            &bull; Pulang: <b><?= $rw['jarak_pulang_meter'] ?>m</b>
                                        <?php endif; ?>
                                        <?php if (empty($rw['jarak_masuk_meter']) && empty($rw['jarak_pulang_meter'])): ?>
                                            -
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($rw['keterangan'] ?? '-') ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>

<!-- Modal Preview Foto Selfie High-Res -->
<div class="modal fade" id="modalPreviewSelfie" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-0 bg-dark text-white px-4 py-3">
                <h6 class="modal-title fw-bold" id="modalSelfieTitle">Foto Selfie Presensi</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 bg-black text-center">
                <img id="modalSelfieImage" src="" class="img-fluid" style="max-height: 520px; width: 100%; object-fit: contain;">
            </div>
            <div class="modal-footer border-0 bg-dark px-4 py-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet Library -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Server-side Geofencing Parameters
const SCHOOL_LAT = <?= json_encode($lokasiLat) ?>;
const SCHOOL_LNG = <?= json_encode($lokasiLng) ?>;
const SCHOOL_RADIUS = <?= json_encode($lokasiRadius) ?>;
const SCHOOL_NAME = <?= json_encode($lokasiNama) ?>;
const TEACHER_NAME = <?= json_encode($guru['nama_lengkap'] ?? 'Guru') ?>;
const TEACHER_NIP = <?= json_encode($guru['nip'] ?? '') ?>;
const BASE_AJAX_URL = '<?= BASE_URL ?>index.php?url=guru/presensiGuru';

// State Variables
let currentStream = null;
let currentFacingMode = 'user'; // 'user' (front) or 'environment' (back)
let capturedBase64 = null;
let userLat = null;
let userLng = null;
let userAccuracy = null;
let currentDistance = null;
let isWithinGeofence = false;

// Leaflet Map Objects
let guruMap = null;
let schoolMarker = null;
let schoolCircle = null;
let userMarker = null;
let connectionLine = null;

// Real-time Clock
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const clockEl = document.getElementById('liveClock');
    if (clockEl) {
        clockEl.innerText = `${hours}:${minutes}:${seconds}`;
    }
}
setInterval(updateClock, 1000);
updateClock();

// Haversine Distance Calculator (Client-side mirror for real-time UI)
function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // Radius of Earth in meters
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return Math.round(R * c);
}

// 1. Initialize Camera
async function startCamera(facing = 'user') {
    const video = document.getElementById('webcamVideo');
    if (!video) return;

    if (currentStream) {
        currentStream.getTracks().forEach(track => track.stop());
    }

    try {
        const constraints = {
            video: {
                facingMode: facing,
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        };
        const stream = await navigator.mediaDevices.getUserMedia(constraints);
        currentStream = stream;
        video.srcObject = stream;

        if (facing === 'user') {
            video.classList.remove('rear-cam');
        } else {
            video.classList.add('rear-cam');
        }
    } catch (err) {
        console.error('Camera error:', err);
        Swal.fire({
            icon: 'error',
            title: 'Kamera Gagal Diakses',
            text: 'Harap izinkan akses kamera pada peramban Anda untuk mengambil foto selfie presensi.',
            confirmButtonText: 'Mengerti'
        });
    }
}

// 2. Initialize Leaflet Map
function initGuruMap() {
    if (guruMap) return;

    const mapContainer = document.getElementById('guruMapContainer');
    if (!mapContainer) return;

    guruMap = L.map('guruMapContainer').setView([SCHOOL_LAT, SCHOOL_LNG], 16);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(guruMap);

    // School Marker
    schoolMarker = L.marker([SCHOOL_LAT, SCHOOL_LNG]).addTo(guruMap);
    schoolMarker.bindPopup(`<b>${SCHOOL_NAME}</b><br>Titik Pusat Presensi`).openPopup();

    // Geofence Radius Circle
    schoolCircle = L.circle([SCHOOL_LAT, SCHOOL_LNG], {
        color: '#10b981',
        fillColor: '#10b981',
        fillOpacity: 0.2,
        weight: 2,
        radius: SCHOOL_RADIUS
    }).addTo(guruMap);
}

// 3. Geolocation Tracker
function trackGPS() {
    if (!navigator.geolocation) {
        document.getElementById('gpsStatusText').innerText = 'Browser tidak mendukung GPS Geolocation.';
        return;
    }

    document.getElementById('gpsStatusText').innerText = 'Mencari sinyal GPS perangkat...';

    navigator.geolocation.watchPosition(
        function(position) {
            userLat = position.coords.latitude;
            userLng = position.coords.longitude;
            userAccuracy = Math.round(position.coords.accuracy);

            // Update Haversine Distance
            currentDistance = calculateHaversineDistance(userLat, userLng, SCHOOL_LAT, SCHOOL_LNG);
            isWithinGeofence = currentDistance <= SCHOOL_RADIUS;

            // Update UI Badges
            const badgeAkurasi = document.getElementById('badgeAkurasi');
            if (badgeAkurasi) badgeAkurasi.innerText = `Akurasi: ±${userAccuracy}m`;

            const txtDistance = document.getElementById('txtDistance');
            if (txtDistance) txtDistance.innerText = `${currentDistance} meter`;

            const geofenceDot = document.getElementById('geofenceDot');
            const gpsStatusText = document.getElementById('gpsStatusText');

            if (isWithinGeofence) {
                if (geofenceDot) {
                    geofenceDot.className = 'geofence-radar-dot in-range';
                }
                if (gpsStatusText) {
                    gpsStatusText.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Dalam Radius Presensi</span> (${currentDistance}m dari sekolah)`;
                }
                document.getElementById('faceGuide')?.classList.remove('warning');
            } else {
                if (geofenceDot) {
                    geofenceDot.className = 'geofence-radar-dot out-range';
                }
                if (gpsStatusText) {
                    gpsStatusText.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Di Luar Radius Sekolah</span> (${currentDistance}m, max: ${SCHOOL_RADIUS}m)`;
                }
                document.getElementById('faceGuide')?.classList.add('warning');
            }

            // Update Map User Marker & Line
            if (guruMap) {
                if (!userMarker) {
                    const userIcon = L.divIcon({
                        className: 'custom-user-pin',
                        html: '<div style="background-color:#ef4444; width:16px; height:16px; border:3px solid #fff; border-radius:50%; box-shadow:0 0 8px rgba(0,0,0,0.4);"></div>',
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    });
                    userMarker = L.marker([userLat, userLng], { icon: userIcon }).addTo(guruMap);
                    userMarker.bindPopup(`<b>Posisi Anda</b><br>Jarak: ${currentDistance}m`);
                } else {
                    userMarker.setLatLng([userLat, userLng]);
                    userMarker.getPopup().setContent(`<b>Posisi Anda</b><br>Jarak: ${currentDistance}m`);
                }

                if (connectionLine) {
                    guruMap.removeLayer(connectionLine);
                }
                connectionLine = L.polyline([
                    [userLat, userLng],
                    [SCHOOL_LAT, SCHOOL_LNG]
                ], {
                    color: isWithinGeofence ? '#10b981' : '#ef4444',
                    dashArray: '5, 8',
                    weight: 2
                }).addTo(guruMap);
            }
        },
        function(error) {
            console.warn('GPS Error:', error);
            const gpsStatusText = document.getElementById('gpsStatusText');
            if (gpsStatusText) {
                gpsStatusText.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Akses GPS Ditolak / Tidak Aktif (${error.message})</span>`;
            }
        },
        { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
    );
}

// 4. Capture Selfie & Watermark Stamping
document.getElementById('btnCapturePhoto').addEventListener('click', function() {
    const video = document.getElementById('webcamVideo');
    const canvas = document.getElementById('canvasCapture');
    const preview = document.getElementById('capturedPhotoPreview');
    const faceGuide = document.getElementById('faceGuide');
    const faceGuideText = document.getElementById('faceGuideText');
    const btnRetake = document.getElementById('btnRetakePhoto');

    if (!video || !video.videoWidth) {
        Swal.fire('Kamera Belum Siap', 'Pastikan aliran kamera telah menyala sebelum mengambil foto.', 'warning');
        return;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');

    // Draw video frame (mirroring if front camera)
    if (currentFacingMode === 'user') {
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        ctx.setTransform(1, 0, 0, 1, 0, 0);
    } else {
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    }

    // Add Professional Watermark Box
    const boxHeight = 90;
    ctx.fillStyle = 'rgba(15, 23, 42, 0.78)';
    ctx.fillRect(0, canvas.height - boxHeight, canvas.width, boxHeight);

    // Accent line
    ctx.fillStyle = '#10b981';
    ctx.fillRect(0, canvas.height - boxHeight, canvas.width, 3);

    // Text Watermarks
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 20px "Plus Jakarta Sans", sans-serif';
    ctx.fillText(`${TEACHER_NAME} (${TEACHER_NIP || 'GTK'})`, 20, canvas.height - boxHeight + 30);

    ctx.fillStyle = '#94a3b8';
    ctx.font = '14px "Plus Jakarta Sans", sans-serif';
    const nowStr = new Date().toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'medium' });
    ctx.fillText(`Presensi: ${nowStr} WIB`, 20, canvas.height - boxHeight + 54);

    const coordStr = `GPS: ${userLat ? userLat.toFixed(6) : '-'}, ${userLng ? userLng.toFixed(6) : '-'} | Jarak: ${currentDistance !== null ? currentDistance + 'm' : '-'}`;
    ctx.fillText(coordStr, 20, canvas.height - boxHeight + 74);

    // Save Base64
    capturedBase64 = canvas.toDataURL('image/jpeg', 0.85);

    // Switch view to preview
    preview.src = capturedBase64;
    preview.classList.remove('d-none');
    video.classList.add('d-none');
    faceGuide.classList.add('d-none');
    faceGuideText.classList.add('d-none');
    btnRetake.classList.remove('d-none');
    this.classList.add('d-none');
});

// 5. Retake Photo
document.getElementById('btnRetakePhoto').addEventListener('click', function() {
    const video = document.getElementById('webcamVideo');
    const preview = document.getElementById('capturedPhotoPreview');
    const faceGuide = document.getElementById('faceGuide');
    const faceGuideText = document.getElementById('faceGuideText');
    const btnCapture = document.getElementById('btnCapturePhoto');

    capturedBase64 = null;
    preview.classList.add('d-none');
    video.classList.remove('d-none');
    faceGuide.classList.remove('d-none');
    faceGuideText.classList.remove('d-none');
    this.classList.add('d-none');
    btnCapture.classList.remove('d-none');
});

// 6. Camera Switcher
document.getElementById('btnSwitchCamera').addEventListener('click', function() {
    currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
    startCamera(currentFacingMode);
});

// 7. Refresh GPS
document.getElementById('btnRefreshGPS').addEventListener('click', function() {
    trackGPS();
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: 'Memperbarui koordinat GPS...',
        showConfirmButton: false,
        timer: 1500
    });
});

// Center Map Button
document.getElementById('btnCenterMap').addEventListener('click', function() {
    if (guruMap && userLat && userLng) {
        guruMap.setView([userLat, userLng], 17);
    } else if (guruMap) {
        guruMap.setView([SCHOOL_LAT, SCHOOL_LNG], 16);
    }
});

// 8. Submit Presensi (Masuk & Pulang)
async function submitPresensi(jenis) {
    if (!capturedBase64) {
        Swal.fire({
            icon: 'warning',
            title: 'Foto Selfie Diperlukan',
            text: 'Harap ambil foto selfie wajah terlebih dahulu menggunakan tombol "Ambil Foto Selfie".',
            confirmButtonColor: '#4f46e5'
        });
        return;
    }

    if (userLat === null || userLng === null) {
        Swal.fire({
            icon: 'warning',
            title: 'GPS Belum Terdeteksi',
            text: 'Koordinat lokasi Anda belum ditemukan. Pastikan izin lokasi aktif pada peramban Anda.',
            confirmButtonColor: '#4f46e5'
        });
        return;
    }

    if (!isWithinGeofence) {
        Swal.fire({
            icon: 'error',
            title: 'Di Luar Radius Presensi!',
            html: `Jarak Anda saat ini <b>${currentDistance} meter</b> dari sekolah.<br>Batas maksimal toleransi radius adalah <b>${SCHOOL_RADIUS} meter</b>.<br><br><span class="text-danger">Presensi hanya dapat dilakukan di lingkungan sekolah.</span>`,
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    const keteranganVal = document.getElementById('presensiKeterangan').value.trim();
    const btnSubmit = jenis === 'masuk' ? document.getElementById('btnSubmitMasuk') : document.getElementById('btnSubmitPulang');
    const originalBtnHtml = btnSubmit.innerHTML;

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim Presensi...';

    try {
        const formData = new FormData();
        formData.append('action', 'submit_presensi_selfie');
        formData.append('jenis', jenis);
        formData.append('latitude', userLat);
        formData.append('longitude', userLng);
        formData.append('image_base64', capturedBase64);
        formData.append('keterangan', keteranganVal);

        const response = await fetch(BASE_AJAX_URL, {
            method: 'POST',
            body: formData
        });

        const res = await response.json();

        if (res.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Presensi Berhasil!',
                text: res.message,
                confirmButtonColor: '#10b981'
            }).then(() => {
                window.location.reload();
            });
        } else if (res.status === 'warning') {
            Swal.fire({
                icon: 'warning',
                title: 'Informasi Presensi',
                text: res.message,
                confirmButtonColor: '#f59e0b'
            });
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnHtml;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Presensi Gagal',
                text: res.message || 'Terjadi kendala saat menyimpan presensi.',
                confirmButtonColor: '#ef4444'
            });
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnHtml;
        }
    } catch (err) {
        console.error('Submit error:', err);
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Sistem',
            text: 'Gagal terhubung ke server. Periksa koneksi internet Anda.',
            confirmButtonColor: '#ef4444'
        });
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = originalBtnHtml;
    }
}

document.getElementById('btnSubmitMasuk').addEventListener('click', function() {
    submitPresensi('masuk');
});

document.getElementById('btnSubmitPulang').addEventListener('click', function() {
    submitPresensi('pulang');
});

// Modal Preview Photo
function previewSelfieModal(url, title) {
    document.getElementById('modalSelfieImage').src = url;
    document.getElementById('modalSelfieTitle').innerText = title;
    const modal = new bootstrap.Modal(document.getElementById('modalPreviewSelfie'));
    modal.show();
}

// Lifecycle Init
document.addEventListener('DOMContentLoaded', function() {
    startCamera(currentFacingMode);
    initGuruMap();
    trackGPS();
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
