<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$liveClasses = is_array($liveClasses ?? null) ? $liveClasses : [];
?>

<style>
/* Modern Cyber-Glassmorphic Student Live Class Tokens */
.student-live-hero {
    background: linear-gradient(135deg, #0284c7 0%, #1e1b4b 60%, #4c1d95 100%);
    border-radius: 24px;
    color: #ffffff;
    padding: 32px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(2, 132, 199, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.student-live-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 360px;
    height: 360px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.35) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}

.live-card-siswa {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
}

.live-card-siswa:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 32px rgba(0, 0, 0, 0.09);
    border-color: #cbd5e1;
}

.live-card-header {
    padding: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.live-card-body {
    padding: 20px;
}

.live-card-footer {
    padding: 16px 20px;
    background-color: #f8fafc;
    border-top: 1px solid #f1f5f9;
}

.avatar-guru-vicon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}

.vicon-modal-container {
    background: #090d16;
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

#jitsiStudentFrame {
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

    <!-- Student Hero Banner -->
    <div class="student-live-hero mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-15 px-3 py-1.5 rounded-pill text-white mb-3" style="font-size:0.78rem; font-weight:700;">
                    <i class="bi bi-broadcast text-warning fs-6"></i> PORTAL RUANG LIVE VIRTUAL MEETING
                </div>
                <h2 class="fw-bold mb-2 text-white">Sesi Tatap Muka Digital & Hybrid Class</h2>
                <p class="text-white-50 mb-0 leading-relaxed" style="font-size: 0.95rem;">
                    Bergabunglah secara langsung ke dalam sesi Video Conference interaktif bersama guru pengampu. Nikmati ruang vicon tanpa install aplikasi atau klik link resmi Google Meet / Zoom.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="https://meet.google.com" target="_blank" class="btn btn-light rounded-pill px-4 py-2.5 fw-bold text-primary shadow-sm border-0">
                    <i class="bi bi-camera-video me-1"></i> Check Google Meet Portal
                </a>
            </div>
        </div>
    </div>

    <!-- Active Sesi Virtual Meeting Grid -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-camera-reels-fill text-danger me-2"></i>Jadwal Live Virtual Meeting Kelas Anda</h5>
        <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-bold"><?= count($liveClasses ?? []) ?> Sesi Terdaftar</span>
    </div>

    <?php if (empty($liveClasses)): ?>
        <div class="card border-0 rounded-4 shadow-sm p-5 text-center my-4">
            <div class="p-4 bg-light rounded-circle d-inline-block mx-auto mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-camera-video-off fs-1 text-secondary"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">Belum Ada Sesi Live Class Aktif</h5>
            <p class="text-muted small mb-0">Saat ini belum ada jadwal tatap muka digital untuk kelas Anda. Guru akan memberikan notifikasi ketika sesi baru dipublikasikan.</p>
        </div>
    <?php else: ?>
        <div class="row g-4 mb-4">
            <?php foreach ($liveClasses as $room):
                $isToday = ($room['tgl_pertemuan'] === date('Y-m-d'));
                $pType = strtolower($room['platform'] ?? 'embedded');
                $avatarPath = !empty($room['avatar_guru']) ? BASE_URL . 'assets/uploads/avatars/' . $room['avatar_guru'] : BASE_URL . 'assets/img/default-avatar.png';
            ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="live-card-siswa h-100 d-flex flex-column">
                        <div class="live-card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold">
                                <i class="bi bi-book-fill me-1"></i><?= htmlspecialchars($room['nama_mapel']) ?>
                            </span>
                            <?php if ($isToday): ?>
                                <span class="badge bg-danger animate-pulse rounded-pill px-2.5 py-1" style="font-size:0.7rem;">HARI INI</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-dark border rounded-pill px-2.5 py-1" style="font-size:0.7rem;"><?= date('d M Y', strtotime($room['tgl_pertemuan'])) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="live-card-body flex-grow-1">
                            <h5 class="fw-bold text-dark mb-2 leading-tight"><?= htmlspecialchars($room['topik']) ?></h5>
                            <?php if (!empty($room['deskripsi'])): ?>
                                <p class="text-muted small mb-3 text-truncate-2" style="font-size:0.82rem; min-height: 38px;">
                                    <?= htmlspecialchars($room['deskripsi']) ?>
                                </p>
                            <?php else: ?>
                                <p class="text-muted small italic mb-3" style="font-size:0.82rem; min-height: 38px;">Tidak ada pengantar tambahan.</p>
                            <?php endif; ?>

                            <div class="d-flex align-items-center gap-2.5 p-2.5 bg-light rounded-3 mb-3">
                                <img src="<?= $avatarPath ?>" alt="Avatar Guru" class="avatar-guru-vicon" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($room['nama_guru']) ?>&background=random';">
                                <div>
                                    <small class="text-muted d-block" style="font-size:0.72rem;">Guru Pengampu</small>
                                    <div class="fw-bold text-dark small mb-0"><?= htmlspecialchars($room['nama_guru']) ?></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center text-muted small" style="font-size:0.8rem;">
                                <div>
                                    <i class="bi bi-clock-history me-1 text-primary"></i>
                                    <strong><?= date('H:i', strtotime($room['jam_mulai'])) ?> WIB</strong>
                                    <?= !empty($room['jam_selesai']) ? ' - ' . date('H:i', strtotime($room['jam_selesai'])) : '' ?>
                                </div>
                                <div>
                                    <i class="bi bi-people me-1"></i><?= htmlspecialchars($room['nama_kelas']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="live-card-footer bg-light">
                            <?php if ($pType === 'embedded'): ?>
                                <button class="btn btn-danger w-100 rounded-pill fw-bold shadow-xs py-2" onclick="openStudentVicon('<?= htmlspecialchars($room['room_code']) ?>', '<?= htmlspecialchars(addslashes($room['topik'])) ?>')">
                                    <i class="bi bi-play-circle-fill me-1"></i> Masuk Room Vicon (Built-in)
                                </button>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($room['meeting_link'] ?: 'https://meet.google.com') ?>" target="_blank" class="btn btn-success w-100 rounded-pill fw-bold shadow-xs py-2">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Bergabung via <?= ucfirst($pType) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
</main>

<!-- Modal Fullscreen Student Embedded Vicon Player -->
<div class="modal fade" id="modalStudentVicon" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content vicon-modal-container text-white">
            <div class="modal-header border-0 pb-0 px-4 pt-3 d-flex justify-content-between align-items-center">
                <div>
                    <div class="badge bg-danger rounded-pill px-3 py-1 fw-bold mb-1"><i class="bi bi-broadcast me-1"></i> SISWA LIVE VICON ROOM</div>
                    <h5 class="fw-bold mb-0 text-white" id="studentViconTitle">Interactive Virtual Classroom</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="closeStudentVicon()"></button>
            </div>
            <div class="modal-body p-3">
                <div id="studentViconContainer" class="w-100 rounded-4 overflow-hidden" style="min-height:600px; background: #000;">
                    <!-- Embedded iframe player will be dynamically injected here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openStudentVicon(roomCode, title) {
    document.getElementById('studentViconTitle').innerText = title || 'Interactive Virtual Classroom';
    const container = document.getElementById('studentViconContainer');
    
    // Clean roomCode for Jitsi URL
    const cleanRoom = roomCode.replace(/[^a-zA-Z0-9_-]/g, '');
    const jitsiUrl = `https://meet.jit.si/${cleanRoom}#userInfo.displayName="Siswa E-Learning"`;
    
    container.innerHTML = `<iframe id="jitsiStudentFrame" src="${jitsiUrl}" allow="camera; microphone; display-capture; autoplay; clipboard-write; fullscreen"></iframe>`;
    
    const viconModal = new bootstrap.Modal(document.getElementById('modalStudentVicon'));
    viconModal.show();
}

function closeStudentVicon() {
    const container = document.getElementById('studentViconContainer');
    container.innerHTML = '';
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
