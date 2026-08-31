<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$liveClasses = is_array($liveClasses ?? null) ? $liveClasses : [];
$mapelList = is_array($mapelList ?? null) ? $mapelList : [];
$kelasList = is_array($kelasList ?? null) ? $kelasList : [];
?>

<style>
/* Modern Cyber-Glassmorphic Live Class Design Tokens */
.live-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #831843 100%);
    border-radius: 24px;
    color: #ffffff;
    padding: 40px;
    margin-top: 0;
    position: relative;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(15, 23, 42, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.live-hero-banner::before {
    content: '';
    position: absolute;
    top: -40%;
    right: -10%;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(244, 63, 94, 0.3) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}

.live-badge-pulse {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(225, 29, 72, 0.15);
    color: #fb7185;
    border: 1px solid rgba(244, 63, 94, 0.3);
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.live-pulse-dot {
    width: 10px;
    height: 10px;
    background-color: #f43f5e;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.7);
    animation: pulse-ring 1.5s infinite;
}

@keyframes pulse-ring {
    0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.7);
    }
    70% {
        transform: scale(1.15);
        box-shadow: 0 0 0 10px rgba(244, 63, 94, 0);
    }
    100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(244, 63, 94, 0);
    }
}

.kpi-live-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    padding: 20px;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}

.kpi-live-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
    border-color: #cbd5e1;
}

.platform-launcher-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    padding: 24px;
    transition: all 0.25s ease;
}

.platform-launcher-card:hover {
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.07);
    transform: translateY(-3px);
}

.platform-icon-wrapper {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
}

.table-live-custom th {
    background-color: #f8fafc;
    color: #475569;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
    padding: 14px 16px;
    border-bottom: 2px solid #e2e8f0;
}

.table-live-custom td {
    padding: 16px;
    vertical-align: middle;
    font-size: 0.88rem;
}

.vicon-modal-container {
    background: #090d16;
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

#jitsiMeetingFrame {
    width: 100%;
    height: 650px;
    border: none;
    border-radius: 12px;
}
</style>

<main class="main-content px-3 px-md-4 py-4">
<div class="container-fluid">

    <!-- Flash Messages -->
    <?php if ($msg = FlashHelper::getSuccess()): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i><?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($err = FlashHelper::getError()): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i><?= $err ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Glassmorphic Hero Banner -->
    <div class="live-hero-banner mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="live-badge-pulse mb-3">
                    <span class="live-pulse-dot"></span>
                    Interactive Hybrid Classroom & Vicon Room
                </div>
                <h2 class="fw-bold mb-2 text-white">Live Virtual Meeting & Kelas Digital</h2>
                <p class="text-white-50 mb-0 leading-relaxed" style="font-size: 0.95rem;">
                    Platform tatap muka digital terintegrasi E-Learning SMK Muthia Harapan Cicalengka. Nikmati sesi Video Conference bawaan interaktif (*Embedded Jitsi WebRTC*) tanpa install aplikasi, atau hubungkan link Google Meet & Zoom secara instan.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <button class="btn btn-danger btn-lg rounded-pill px-4 py-2.5 shadow-lg fw-bold border-0" data-bs-toggle="modal" data-bs-target="#modalCreateRoom">
                    <i class="bi bi-camera-video-fill me-2"></i> + Buat Sesi Live Meeting
                </button>
            </div>
        </div>
    </div>

    <!-- KPI Stat Cards -->
    <?php
    $totalRooms = count($liveClasses ?? []);
    $todayCount = 0;
    $todayDate = date('Y-m-d');
    $activeCount = 0;

    foreach ($liveClasses as $lcItem) {
        if (($lcItem['tgl_pertemuan'] ?? '') === $todayDate) $todayCount++;
        if (!empty($lcItem['is_active'])) $activeCount++;
    }
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="kpi-live-card d-flex align-items-center gap-3">
                <div class="p-3 bg-primary-subtle text-primary rounded-4">
                    <i class="bi bi-camera-reels-fill fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-dark mb-0"><?= $totalRooms ?></h3>
                    <span class="text-muted small fw-semibold">Total Sesi Meeting</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-live-card d-flex align-items-center gap-3">
                <div class="p-3 bg-danger-subtle text-danger rounded-4">
                    <i class="bi bi-broadcast fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-dark mb-0"><?= $todayCount ?></h3>
                    <span class="text-muted small fw-semibold">Sesi Hari Ini</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-live-card d-flex align-items-center gap-3">
                <div class="p-3 bg-success-subtle text-success rounded-4">
                    <i class="bi bi-check-circle-fill fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-dark mb-0"><?= $activeCount ?></h3>
                    <span class="text-muted small fw-semibold">Sesi Aktif</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-live-card d-flex align-items-center gap-3">
                <div class="p-3 bg-warning-subtle text-warning-emphasis rounded-4">
                    <i class="bi bi-laptop-fill fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-dark mb-0">WebRTC + Meet</h3>
                    <span class="text-muted small fw-semibold">Platform Terhubung</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Platform Launchers Bar -->
    <div class="row g-4 mb-4">
        <!-- Instant Embedded Room -->
        <div class="col-md-4">
            <div class="platform-launcher-card border-start border-4 border-danger">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="platform-icon-wrapper bg-danger-subtle text-danger">
                        <i class="bi bi-display-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Embedded WebRTC Vicon</h6>
                        <small class="text-muted d-block">Ruang meeting bawaan e-learning tanpa install aplikasi.</small>
                    </div>
                </div>
                <button class="btn btn-outline-danger w-100 rounded-pill fw-bold" onclick="openEmbeddedVicon('SMKMH-INSTANT-ROOM-<?= rand(100,999) ?>', 'Instant Demo Meeting Room Guru')">
                    <i class="bi bi-play-circle-fill me-1"></i> Buka Instant Web Vicon
                </button>
            </div>
        </div>

        <!-- Google Meet Launcher -->
        <div class="col-md-4">
            <div class="platform-launcher-card border-start border-4 border-success">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="platform-icon-wrapper bg-success-subtle text-success">
                        <i class="bi bi-camera-video-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Google Meet Launcher</h6>
                        <small class="text-muted d-block">Buat ruang rapat instan secara resmi via Google Workspace.</small>
                    </div>
                </div>
                <a href="https://meet.google.com/new" target="_blank" class="btn btn-outline-success w-100 rounded-pill fw-bold">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Launch Instant Google Meet
                </a>
            </div>
        </div>

        <!-- Zoom Launcher -->
        <div class="col-md-4">
            <div class="platform-launcher-card border-start border-4 border-primary">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="platform-icon-wrapper bg-primary-subtle text-primary">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Zoom Meeting Portal</h6>
                        <small class="text-muted d-block">Buka aplikasi Zoom Desktop/Mobile untuk memulai kelas.</small>
                    </div>
                </div>
                <a href="https://zoom.us/start/videon" target="_blank" class="btn btn-outline-primary w-100 rounded-pill fw-bold">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Launch Zoom Meeting
                </a>
            </div>
        </div>
    </div>

    <!-- Active Scheduled Meetings Table -->
    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-event-fill text-danger me-2"></i>Daftar Sesi Live Virtual Meeting Terdaftar</h5>
                <small class="text-muted">Kelola jadwal tatap muka digital interaktif dan ruang Vicon siswa.</small>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                <div class="input-group input-group-sm" style="max-width: 260px;">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchLiveGuru" class="form-control bg-light border-start-0 rounded-end-pill" placeholder="Cari topik / mapel / kelas...">
                </div>
                <button class="btn btn-sm btn-danger rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCreateRoom">
                    <i class="bi bi-plus-lg me-1"></i> Sesi Baru
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-live-custom">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="30%">Topik / Pertemuan</th>
                            <th width="15%">Mata Pelajaran</th>
                            <th width="12%">Target Kelas</th>
                            <th width="12%">Platform</th>
                            <th width="14%">Waktu Pelaksanaan</th>
                            <th width="12%" class="text-center">Aksi & Room</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($liveClasses)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-camera-video-off fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                    Belum ada sesi Live Virtual Meeting yang dijadwalkan. Klik <strong>+ Buat Sesi Live Meeting</strong> untuk memulai.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($liveClasses as $room): 
                                $isToday = ($room['tgl_pertemuan'] === date('Y-m-d'));
                                $pType = strtolower($room['platform'] ?? 'embedded');
                            ?>
                                <tr>
                                    <td class="fw-bold text-muted"><?= $no++ ?></td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6 mb-0.5"><?= htmlspecialchars($room['topik']) ?></div>
                                        <?php if (!empty($room['deskripsi'])): ?>
                                            <small class="text-muted d-block text-truncate" style="max-width: 320px; font-size:0.78rem;"><?= htmlspecialchars($room['deskripsi']) ?></small>
                                        <?php endif; ?>
                                        <small class="text-secondary font-monospace" style="font-size:0.72rem;">ID Room: <?= htmlspecialchars($room['room_code']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold text-truncate d-inline-block" style="max-width: 160px;" title="<?= htmlspecialchars($room['nama_mapel']) ?>">
                                            <i class="bi bi-book-fill me-1"></i><?= htmlspecialchars($room['nama_mapel']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark border rounded-pill px-3 py-1.5 fw-semibold">
                                            <i class="bi bi-people-fill me-1 text-primary"></i><?= htmlspecialchars($room['nama_kelas']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($pType === 'embedded'): ?>
                                            <span class="badge bg-danger text-white rounded-pill px-3 py-1.5 fw-bold">
                                                <i class="bi bi-display-fill me-1"></i> WebRTC (Built-in)
                                            </span>
                                        <?php elseif ($pType === 'meet'): ?>
                                            <span class="badge bg-success text-white rounded-pill px-3 py-1.5 fw-bold">
                                                <i class="bi bi-camera-video-fill me-1"></i> Google Meet
                                            </span>
                                        <?php elseif ($pType === 'zoom'): ?>
                                            <span class="badge bg-primary text-white rounded-pill px-3 py-1.5 fw-bold">
                                                <i class="bi bi-headset me-1"></i> Zoom Meeting
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1.5 fw-bold">
                                                <i class="bi bi-link-45deg me-1"></i> External Link
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark" style="font-size:0.85rem;">
                                            <i class="bi bi-calendar-check me-1 text-danger"></i><?= date('d M Y', strtotime($room['tgl_pertemuan'])) ?>
                                        </div>
                                        <small class="text-muted d-block" style="font-size:0.78rem;">
                                            <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($room['jam_mulai'])) ?> WIB
                                            <?= !empty($room['jam_selesai']) ? ' - ' . date('H:i', strtotime($room['jam_selesai'])) . ' WIB' : '' ?>
                                        </small>
                                        <?php 
                                        $nowTime = date('H:i:s');
                                        $startTime = date('H:i:s', strtotime($room['jam_mulai']));
                                        $endTime = !empty($room['jam_selesai']) ? date('H:i:s', strtotime($room['jam_selesai'])) : date('H:i:s', strtotime($room['jam_mulai'] . ' + 2 hours'));
                                        $isLiveNow = ($isToday && $nowTime >= $startTime && $nowTime <= $endTime);
                                        ?>
                                        <?php if ($isLiveNow): ?>
                                            <span class="badge bg-success animate-pulse rounded-pill mt-1 px-2 py-0.5" style="font-size:0.65rem;"><i class="bi bi-broadcast me-1"></i>LIVE BERLANGSUNG</span>
                                        <?php elseif ($isToday): ?>
                                            <span class="badge bg-danger animate-pulse rounded-pill mt-1 px-2 py-0.5" style="font-size:0.65rem;">HARI INI</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-2.5">
                                            <?php if ($pType === 'embedded'): ?>
                                                <button class="btn btn-sm btn-danger rounded-pill px-3 py-1.5 shadow-xs fw-bold text-nowrap btn-launch-vicon d-inline-flex align-items-center gap-1.5" style="font-size:0.78rem;" data-room-code="<?= htmlspecialchars($room['room_code']) ?>" data-topik="<?= htmlspecialchars($room['topik']) ?>">
                                                    <i class="bi bi-play-circle-fill fs-6"></i>
                                                    <span>Masuk Vicon</span>
                                                </button>
                                            <?php else: ?>
                                                <a href="<?= htmlspecialchars($room['meeting_link'] ?: 'https://meet.google.com') ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 shadow-xs fw-bold text-nowrap d-inline-flex align-items-center gap-1.5" style="font-size:0.78rem;">
                                                    <i class="bi bi-box-arrow-up-right fs-6"></i>
                                                    <span>Buka Link</span>
                                                </a>
                                            <?php endif; ?>

                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary rounded-circle px-2 py-1 shadow-2xs" data-bs-toggle="dropdown" title="Menu Opsi">
                                                    <i class="bi bi-three-dots-vertical fs-6"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2" style="min-width: 180px; font-size:0.85rem;">
                                                    <li>
                                                        <button class="dropdown-item rounded-3 py-2 fw-semibold" onclick="copyMeetingLink('<?= htmlspecialchars($room['platform'] === 'embedded' ? BASE_URL . 'index.php?url=siswa/liveClass' : ($room['meeting_link'] ?: 'https://meet.google.com')) ?>')">
                                                            <i class="bi bi-share-fill text-primary me-2"></i> Salin Link Room
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item rounded-3 py-2 fw-semibold" href="#" data-bs-toggle="modal" data-bs-target="#modalEditRoom<?= $room['id'] ?>">
                                                            <i class="bi bi-pencil-square text-warning me-2"></i> Edit Sesi
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider my-1"></li>
                                                    <li>
                                                        <form action="<?= BASE_URL ?>index.php?url=guru/liveClass" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ruang Live Virtual Meeting ini?');">
                                                            <?= Security::csrfField() ?>
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?= $room['id'] ?>">
                                                            <button type="submit" class="dropdown-item rounded-3 py-2 text-danger fw-semibold w-100 text-start">
                                                                <i class="bi bi-trash-fill me-2"></i> Hapus Sesi
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit Room -->
                                <div class="modal fade" id="modalEditRoom<?= $room['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow-lg">
                                            <div class="modal-header border-0 pb-0 px-4 pt-4">
                                                <h5 class="fw-bold text-dark"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Sesi Live Class</h5>
                                                <button class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="<?= BASE_URL ?>index.php?url=guru/liveClass" method="POST" class="p-4 pt-2">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="id" value="<?= $room['id'] ?>">

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Topik Pertemuan / Judul Sesi</label>
                                                    <input type="text" name="topik" class="form-control rounded-3" value="<?= htmlspecialchars($room['topik']) ?>" required>
                                                </div>

                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-bold">Mata Pelajaran</label>
                                                        <select name="mapel_id" class="form-select rounded-3" required>
                                                            <?php foreach ($mapelList as $mp): ?>
                                                                <option value="<?= $mp['id'] ?>" <?= $mp['id'] == $room['mapel_id'] ? 'selected' : '' ?>><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-bold">Target Kelas</label>
                                                        <select name="kelas_id" class="form-select rounded-3">
                                                            <option value="">Semua Kelas (Publik)</option>
                                                            <?php foreach ($kelasList as $kls): ?>
                                                                <option value="<?= $kls['id'] ?>" <?= $kls['id'] == $room['kelas_id'] ? 'selected' : '' ?>><?= htmlspecialchars($kls['nama_kelas']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Platform Virtual Meeting</label>
                                                    <select name="platform" class="form-select rounded-3" required>
                                                        <option value="embedded" <?= $room['platform'] === 'embedded' ? 'selected' : '' ?>>Embedded WebRTC Vicon (Langsung di browser e-learning)</option>
                                                        <option value="meet" <?= $room['platform'] === 'meet' ? 'selected' : '' ?>>Google Meet (External Link)</option>
                                                        <option value="zoom" <?= $room['platform'] === 'zoom' ? 'selected' : '' ?>>Zoom Meeting (External Link)</option>
                                                        <option value="teams" <?= $room['platform'] === 'teams' ? 'selected' : '' ?>>Microsoft Teams</option>
                                                        <option value="youtube" <?= $room['platform'] === 'youtube' ? 'selected' : '' ?>>YouTube Live Stream</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Link Meeting (Opsional jika platform external)</label>
                                                    <input type="url" name="meeting_link" class="form-control rounded-3" value="<?= htmlspecialchars($room['meeting_link'] ?? '') ?>" placeholder="https://meet.google.com/xyz-abc">
                                                </div>

                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-bold">Tanggal Pertemuan</label>
                                                        <input type="date" name="tgl_pertemuan" class="form-control rounded-3" value="<?= $room['tgl_pertemuan'] ?>" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-bold">Jam Mulai</label>
                                                        <input type="time" name="jam_mulai" class="form-control rounded-3" value="<?= date('H:i', strtotime($room['jam_mulai'])) ?>" required>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Deskripsi / Pengantar</label>
                                                    <textarea name="deskripsi" class="form-control rounded-3" rows="2"><?= htmlspecialchars($room['deskripsi'] ?? '') ?></textarea>
                                                </div>

                                                <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill shadow-sm">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Simpan Perubahan Sesi
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</main>

<!-- Modal Create Room -->
<div class="modal fade" id="modalCreateRoom" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-bold text-dark"><i class="bi bi-camera-video-fill text-danger me-2"></i>Buat Sesi Live Class Baru</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=guru/liveClass" method="POST" class="p-4 pt-2">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="create">

                <div class="mb-3">
                    <label class="form-label small fw-bold">Topik Pertemuan / Judul Sesi <span class="text-danger">*</span></label>
                    <input type="text" name="topik" class="form-control rounded-3" required placeholder="Contoh: Sesi Review & Kuis Interaktif Pemrograman Web">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="mapel_id" class="form-select rounded-3" required>
                            <option value="">Pilih Mapel...</option>
                            <?php foreach ($mapelList as $mp): ?>
                                <option value="<?= $mp['id'] ?>"><?= htmlspecialchars($mp['nama_mapel']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Target Kelas</label>
                        <select name="kelas_id" class="form-select rounded-3">
                            <option value="">Semua Kelas (Publik)</option>
                            <?php foreach ($kelasList as $kls): ?>
                                <option value="<?= $kls['id'] ?>"><?= htmlspecialchars($kls['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Platform Virtual Meeting <span class="text-danger">*</span></label>
                    <select name="platform" class="form-select rounded-3" required>
                        <option value="embedded" selected>Embedded WebRTC Vicon (Langsung di browser e-learning)</option>
                        <option value="meet">Google Meet (External Link)</option>
                        <option value="zoom">Zoom Meeting (External Link)</option>
                        <option value="teams">Microsoft Teams</option>
                        <option value="youtube">YouTube Live Stream</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Link Meeting (URL External)</label>
                    <input type="url" name="meeting_link" class="form-control rounded-3" placeholder="https://meet.google.com/abc-defg-hij">
                    <small class="text-muted d-block mt-1" style="font-size:0.75rem;">Kosongkan jika menggunakan platform Embedded WebRTC Vicon bawaan.</small>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Tanggal Pertemuan <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_pertemuan" class="form-control rounded-3" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" name="jam_mulai" class="form-control rounded-3" value="08:00" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Deskripsi / Pengantar Sesi</label>
                    <textarea name="deskripsi" class="form-control rounded-3" rows="2" placeholder="Tuliskan petunjuk pengerjaan atau materi pengantar pertemuan..."></textarea>
                </div>

                <button type="submit" class="btn btn-danger w-100 fw-bold rounded-pill shadow-md py-2.5">
                    <i class="bi bi-broadcast me-1"></i> Publikasikan Sesi & Kirim Notifikasi ke Siswa
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Fullscreen Embedded Vicon Player -->
<div class="modal fade" id="modalViconPlayer" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content vicon-modal-container text-white">
            <div class="modal-header border-0 pb-0 px-4 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="badge bg-danger rounded-pill px-3 py-1 fw-bold mb-1"><i class="bi bi-broadcast me-1"></i> LIVE VICON ROOM</div>
                    <h5 class="fw-bold mb-0 text-white" id="viconRoomTitle">Interactive Virtual Classroom</h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a id="viconPopoutBtn" href="#" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-semibold">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="closeEmbeddedVicon()"></button>
                </div>
            </div>
            <div class="modal-body p-3">
                <div id="viconContainer" class="w-100 rounded-4 overflow-hidden" style="min-height:600px; background: #000;">
                    <!-- Embedded iframe player will be dynamically injected here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://meet.jit.si/external_api.js"></script>
<script>
let guruJitsiApi = null;

function openEmbeddedVicon(roomCode, title) {
    const titleEl = document.getElementById('viconRoomTitle');
    if (titleEl) titleEl.innerText = title || 'Interactive Virtual Classroom';

    const cleanRoom = (roomCode || 'ROOM').replace(/[^a-zA-Z0-9_-]/g, '');
    const popoutBtn = document.getElementById('viconPopoutBtn');
    if (popoutBtn) {
        popoutBtn.href = `https://meet.jit.si/${cleanRoom}`;
    }

    const container = document.getElementById('viconContainer');
    if (!container) return;
    container.innerHTML = '';

    const teacherName = <?= json_encode($guru['nama_lengkap'] ?? AuthHelper::user()['full_name'] ?? 'Guru Pengampu') ?>;
    const jitsiUrl = `https://meet.jit.si/${cleanRoom}#userInfo.displayName="${encodeURIComponent(teacherName)}"`;

    if (typeof JitsiMeetExternalAPI !== 'undefined') {
        if (guruJitsiApi) {
            try { guruJitsiApi.dispose(); } catch(e){}
        }
        try {
            guruJitsiApi = new JitsiMeetExternalAPI("meet.jit.si", {
                roomName: cleanRoom,
                width: '100%',
                height: 620,
                parentNode: container,
                userInfo: {
                    displayName: teacherName
                },
                configOverwrite: {
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,
                    disableDeepLinking: true
                }
            });
        } catch(err) {
            container.innerHTML = `<iframe id="jitsiMeetingFrame" src="${jitsiUrl}" style="width:100%; height:620px; border:none; border-radius:12px;" allow="camera; microphone; display-capture; autoplay; clipboard-write; fullscreen; speaker"></iframe>`;
        }
    } else {
        container.innerHTML = `<iframe id="jitsiMeetingFrame" src="${jitsiUrl}" style="width:100%; height:620px; border:none; border-radius:12px;" allow="camera; microphone; display-capture; autoplay; clipboard-write; fullscreen; speaker"></iframe>`;
    }

    const viconModalEl = document.getElementById('modalViconPlayer');
    if (viconModalEl) {
        const viconModal = bootstrap.Modal.getOrCreateInstance(viconModalEl);
        viconModal.show();
    }
}

function closeEmbeddedVicon() {
    if (guruJitsiApi) {
        try { guruJitsiApi.dispose(); } catch(e){}
        guruJitsiApi = null;
    }
    const container = document.getElementById('viconContainer');
    if (container) container.innerHTML = '';
}

function copyMeetingLink(link) {
    navigator.clipboard.writeText(link).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Link Disalin!',
            text: 'Link ruang pertemuan virtual telah disalin ke clipboard.',
            timer: 1800,
            showConfirmButton: false
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-launch-vicon');
        if (btn) {
            e.preventDefault();
            const roomCode = btn.getAttribute('data-room-code');
            const topik = btn.getAttribute('data-topik');
            openEmbeddedVicon(roomCode, topik);
        }
    });

    const searchInput = document.getElementById('searchLiveGuru');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('.table-live-custom tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
