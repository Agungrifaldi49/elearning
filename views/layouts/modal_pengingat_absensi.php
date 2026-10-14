<?php
/**
 * Pop-up Otomatis Pengingat Waktu Absensi Kelas Siswa untuk Guru
 * Muncul saat jam pelajaran KBM telah tiba dan otomatis HILANG jika absensi sudah diisi.
 */
if (!class_exists('AuthHelper') || !AuthHelper::check()) {
    return;
}

$currentUser = AuthHelper::user();
$isGuruRole = (!empty($currentUser) && (strtolower($currentUser['role_name'] ?? '') === 'guru' || ($currentUser['role_id'] ?? 0) == 2));

if (!$isGuruRole) {
    return;
}

try {
    $dbConn = Database::getInstance()->getConnection();
    $stmtGuru = $dbConn->prepare("SELECT id, nama_lengkap FROM guru WHERE user_id = ? LIMIT 1");
    $stmtGuru->execute([(int)$currentUser['id']]);
    $guruProfile = $stmtGuru->fetch(PDO::FETCH_ASSOC);

    if (!$guruProfile || empty($guruProfile['id'])) {
        return;
    }

    require_once ROOT_PATH . 'models/AbsensiModel.php';
    $absensiModelReminder = new AbsensiModel();
    $todayDate = date('Y-m-d');
    $nowTime = date('H:i:s');

    $reminderData = $absensiModelReminder->getPendingClassAttendanceReminderForGuru((int)$guruProfile['id'], $todayDate, $nowTime);

    // Jika semua sesi KBM sudah diabsensi (atau belum masuk jam KBM), pop up TIDAK muncul
    if (!$reminderData || empty($reminderData['has_pending'])) {
        return;
    }

    $primarySched = $reminderData['primary'];
    $currentUrl = $_GET['url'] ?? '';
    $currentJadwalParam = (int)($_GET['jadwal_id'] ?? 0);

    // Jangan tampilkan pop up jika guru saat ini sedang aktif membuka form absensi jadwal tersebut
    $isAlreadyOnThisForm = (strpos($currentUrl, 'guru/absensi') !== false && $currentJadwalParam === (int)$primarySched['id']);
} catch (\Throwable $eReminder) {
    return;
}
?>

<!-- Modal Pop-Up Pengingat Absensi Guru -->
<div class="modal fade" id="modalPengingatAbsensiGuru" tabindex="-1" aria-labelledby="modalPengingatAbsensiGuruLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header border-0 text-white p-4" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center p-3 shadow-xs" style="width: 56px; height: 56px; min-width: 56px;">
                        <i class="bi bi-alarm-fill fs-2 text-white"></i>
                    </div>
                    <div>
                        <span class="badge bg-white text-dark fw-bold rounded-pill px-2.5 py-1 mb-1" style="font-size: 0.72rem;">
                            <i class="bi bi-clock-history me-1 text-warning"></i> Waktunya Absensi Siswa
                        </span>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalPengingatAbsensiGuruLabel">
                            Pengingat Absensi Kelas KBM
                        </h5>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="snoozeAbsensiReminder(<?= (int)$primarySched['id'] ?>)"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-light bg-opacity-50">
                <div class="text-center mb-3">
                    <p class="text-secondary small mb-0">
                        Halo <strong>Bpk/Ibu <?= htmlspecialchars($guruProfile['nama_lengkap']) ?></strong>, saat ini merupakan jam pembelajaran untuk mata pelajaran di bawah ini. Mohon segera melakukan presensi kehadiran siswa di kelas:
                    </p>
                </div>

                <!-- Card Sesi Utama -->
                <div class="card border border-warning rounded-4 p-3.5 mb-3 bg-white shadow-xs">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge <?= !empty($primarySched['is_live']) ? 'bg-danger text-white' : 'bg-warning-subtle text-dark border border-warning' ?> px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                            <?= !empty($primarySched['is_live']) ? 'KBM Sedang Berlangsung' : 'Jadwal KBM Hari Ini' ?>
                        </span>
                        <span class="badge bg-light text-secondary border px-2 py-1">
                            <i class="bi bi-door-closed me-1"></i><?= htmlspecialchars($primarySched['ruangan'] ?? 'Ruang Kelas') ?>
                        </span>
                    </div>

                    <h5 class="fw-bold text-dark mb-1">
                        <?= htmlspecialchars($primarySched['nama_mapel']) ?>
                    </h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                        <span class="badge bg-primary px-2.5 py-1 rounded-3 fw-bold">
                            <i class="bi bi-building me-1"></i>Kelas <?= htmlspecialchars($primarySched['nama_kelas']) ?>
                        </span>
                        <span class="text-muted small">
                            <i class="bi bi-clock me-1 text-primary"></i>Pukul <strong><?= $primarySched['jam_mulai_fmt'] ?> - <?= $primarySched['jam_selesai_fmt'] ?> WIB</strong>
                        </span>
                    </div>
                    <div class="p-2 rounded-3 bg-warning-subtle text-dark border border-warning-subtle small d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-circle-fill text-warning fs-5"></i>
                        <span>Presensi siswa untuk kelas ini <strong>belum diisi</strong> hari ini.</span>
                    </div>
                </div>

                <?php if ($reminderData['count'] > 1): ?>
                    <!-- Sesi Lainnya yang Juga Belum Diabsen -->
                    <div class="accordion mb-3" id="accPendingList">
                        <div class="accordion-item border-0 rounded-3 overflow-hidden shadow-2xs">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2 px-3 bg-white small fw-bold text-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOthers">
                                    <i class="bi bi-collection me-1.5 text-warning"></i> + <?= $reminderData['count'] - 1 ?> sesi KBM lainnya hari ini juga belum diabsen
                                </button>
                            </h2>
                            <div id="collapseOthers" class="accordion-collapse collapse" data-bs-parent="#accPendingList">
                                <div class="accordion-body p-2 bg-light">
                                    <?php foreach (array_slice($reminderData['schedules'], 1) as $otherSched): ?>
                                        <div class="d-flex justify-content-between align-items-center p-2 mb-1.5 rounded-3 bg-white border small">
                                            <div>
                                                <strong><?= htmlspecialchars($otherSched['nama_mapel']) ?></strong> (<?= htmlspecialchars($otherSched['nama_kelas']) ?>)
                                                <div class="text-muted" style="font-size:0.75rem;"><?= $otherSched['jam_mulai_fmt'] ?> - <?= $otherSched['jam_selesai_fmt'] ?> WIB</div>
                                            </div>
                                            <a href="<?= BASE_URL ?>index.php?url=guru/absensi&jadwal_id=<?= $otherSched['id'] ?>&tanggal=<?= $todayDate ?>&tab=siswa" class="btn btn-xs btn-outline-warning text-dark fw-bold rounded-pill px-2.5 py-1" style="font-size:0.75rem;">
                                                Absen
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tombol Aksi Utama -->
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>index.php?url=guru/absensi&jadwal_id=<?= $primarySched['id'] ?>&tanggal=<?= $todayDate ?>&tab=siswa" class="btn btn-warning btn-lg fw-bold rounded-pill shadow-sm py-2.5 text-dark d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-clipboard-check-fill fs-5"></i>
                        <span>Buka Lembar Absensi Siswa Sekarang</span>
                    </a>
                    <button type="button" class="btn btn-link text-muted text-decoration-none small" data-bs-dismiss="modal" onclick="snoozeAbsensiReminder(<?= (int)$primarySched['id'] ?>)">
                        <i class="bi bi-hourglass-split me-1"></i>Ingatkan Lagi Nanti (10 Menit)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Notification Pill (Tampil jika modal ditutup sebelum absen) -->
<div id="floatingAbsensiReminderPill" 
     class="position-fixed bottom-0 end-0 m-3 shadow-lg rounded-pill p-2 px-3.5 bg-warning text-dark fw-bold border border-white" 
     style="z-index: 1045; cursor: pointer; display: none; transition: transform 0.2s ease;"
     onclick="openAbsensiReminderModal()"
     title="Klik untuk membuka pengingat absensi kelas">
    <div class="d-flex align-items-center gap-2">
        <span class="spinner-grow spinner-grow-sm text-danger" role="status"></span>
        <i class="bi bi-alarm-fill text-dark"></i>
        <span style="font-size: 0.82rem;">Waktunya Absen Kelas (<?= htmlspecialchars($primarySched['nama_kelas']) ?>)</span>
        <span class="badge bg-danger rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">Isi</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const primaryId = <?= (int)$primarySched['id'] ?>;
    const isAlreadyOnThisForm = <?= $isAlreadyOnThisForm ? 'true' : 'false' ?>;
    const snoozeKey = 'snooze_absensi_reminder_' + primaryId;
    const snoozedUntil = parseInt(sessionStorage.getItem(snoozeKey) || '0', 10);
    const nowTs = Math.floor(Date.now() / 1000);

    const modalEl = document.getElementById('modalPengingatAbsensiGuru');
    const pillEl = document.getElementById('floatingAbsensiReminderPill');

    // Jika guru sedang tidak membuka form absensi untuk jadwal tersebut
    if (!isAlreadyOnThisForm && modalEl && typeof bootstrap !== 'undefined') {
        if (nowTs >= snoozedUntil) {
            // Tampilkan pop-up modal otomatis setelah jeda singkat (700ms) agar transisi halaman halus
            setTimeout(() => {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }, 700);
        } else {
            // Jika dalam masa tunda (snooze 10 menit), tampilkan floating pill kecil di pojok
            if (pillEl) pillEl.style.display = 'block';
        }
    }
});

function snoozeAbsensiReminder(schedId) {
    // Tunda pop-up otomatis selama 10 menit (600 detik) untuk sesi browser ini
    const snoozeUntil = Math.floor(Date.now() / 1000) + (10 * 60);
    sessionStorage.setItem('snooze_absensi_reminder_' + schedId, snoozeUntil.toString());

    // Tampilkan floating pill agar guru tetap bisa mengaksesnya dengan mudah
    const pillEl = document.getElementById('floatingAbsensiReminderPill');
    if (pillEl) {
        pillEl.style.display = 'block';
    }
}

function openAbsensiReminderModal() {
    const modalEl = document.getElementById('modalPengingatAbsensiGuru');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
}
</script>
