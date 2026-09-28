<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$currentScanModuleUrl = $_GET['url'] ?? '';
$isAdminScanRoute = (strpos($currentScanModuleUrl, 'admin/') === 0 || strtolower(AuthHelper::user()['role_name'] ?? '') === 'administrator');
$dashboardUrl = $isAdminScanRoute ? BASE_URL . 'index.php?url=admin/dashboard' : BASE_URL . 'index.php?url=guru/dashboard';
?>
<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <?php if ($isAdminScanRoute): ?>
                <h4 class="fw-bold mb-1"><i class="bi bi-shield-check text-success me-2"></i>Terminal Scanner Presensi Utama (Siswa & Guru/GTK)</h4>
                <p class="text-muted small mb-0">Terminal Scanner Resmi Gerbang/Piket Sekolah untuk mencatat presensi harian Siswa dan Tenaga Pendidik (Guru/GTK).</p>
            <?php else: ?>
                <h4 class="fw-bold mb-1"><i class="bi bi-qr-code-scan text-primary me-2"></i>Scan QR Code Presensi Siswa (KBM Kelas)</h4>
                <p class="text-muted small mb-0">Arahkan kamera ke QR Code pada Kartu Pelajar Digital siswa untuk mencatat presensi otomatis di kelas.</p>
            <?php endif; ?>
        </div>
        <div>
            <a href="<?= $dashboardUrl ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Scanner Panel -->
        <div class="col-12 col-md-5 col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-camera-video-fill text-success me-2"></i>Kamera QR Scanner</h6>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" onclick="toggleCameraFacing()" class="btn btn-sm btn-light border text-primary rounded-pill px-2 py-0 fw-semibold" style="font-size: 0.78rem;" title="Beralih Kamera Depan / Belakang HP">
                            <i class="bi bi-camera-fill me-1"></i> Ganti Kamera
                        </button>
                        <button type="button" id="toggleVoiceBtn" onclick="toggleVoiceAnnouncement()" class="btn btn-sm btn-light border text-success rounded-pill px-2 py-0 fw-semibold" style="font-size: 0.78rem;" title="Aktifkan / Matikan Suara Pengumuman Presensi">
                            <i id="voiceIcon" class="bi bi-volume-up-fill me-1"></i><span id="voiceText">Suara ON</span>
                        </button>
                        <span class="badge bg-success-subtle text-success border border-success rounded-pill px-2 py-1">
                            <i class="bi bi-broadcast me-1"></i> Live
                        </span>
                    </div>
                </div>

                <!-- Camera Selection Dropdown -->
                <div class="mb-3">
                    <select id="cameraSelect" class="form-select form-select-sm rounded-3 d-none" onchange="switchCamera(this.value)">
                        <option value="">-- Pilih Kamera --</option>
                    </select>
                </div>

                <!-- Scan Status Alert -->
                <div id="scanResult" class="alert alert-info border-0 rounded-3 shadow-sm mb-3 d-none">
                    <i class="bi bi-clock-fill me-1"></i> Menunggu scan...
                </div>

                <!-- QR Reader Container -->
                <div id="qr-reader" class="mb-3 rounded-4 overflow-hidden border" style="min-height:220px; background:#000;"></div>

                <!-- Manual Input Fallback -->
                <div class="mt-3 pt-3 border-top">
                    <label class="form-label small fw-semibold text-dark mb-1">
                        <i class="bi bi-keyboard-fill text-primary me-1"></i> <?= $isAdminScanRoute ? 'Input NIS / NIP / ID Manual:' : 'Input NIS / NISN Manual:' ?>
                    </label>
                    <div class="input-group">
                        <input type="text" id="manualNis" class="form-control rounded-start-3" placeholder="<?= $isAdminScanRoute ? 'Ketik NIS/NISN siswa atau NIP guru...' : 'Ketik NIS/NISN siswa...' ?>" onkeypress="if(event.key === 'Enter') processManualScan();">
                        <button class="btn btn-success rounded-end-3 px-3 fw-bold" onclick="processManualScan()">
                            <i class="bi bi-check2-circle me-1"></i> Rekam
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Presensi Log Today -->
        <div class="col-12 col-md-7 col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm p-3 p-md-4 bg-white">
                <?php
                $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                $tglIndo = $namaHari[(int)date('w')] . ', ' . (int)date('j') . ' ' . $namaBulan[(int)date('n')] . ' ' . date('Y');

                $totalSiswa = 0;
                $totalGuru = 0;
                $totalPulang = 0;
                foreach (($presensiHariIni ?? []) as $p) {
                    $isG = ($p['role_label'] ?? '') === 'Guru' || ($p['nama_kelas'] ?? '') === 'GTK / Pendidik';
                    if ($isG) {
                        $totalGuru++;
                    } else {
                        $totalSiswa++;
                    }
                    if (!empty($p['waktu_pulang'])) {
                        $totalPulang++;
                    }
                }
                $totalAll = count($presensiHariIni ?? []);
                ?>

                <!-- Header Title & Date -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2 pb-2 border-bottom">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark d-flex align-items-center">
                            <span class="rounded-circle bg-primary-subtle text-primary p-2 me-2 d-inline-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                                <i class="bi bi-calendar2-check-fill fs-6"></i>
                            </span>
                            Log Presensi Hari Ini
                        </h5>
                        <small class="text-muted"><i class="bi bi-clock-history me-1 text-primary"></i> <?= $tglIndo ?> — Update real-time otomatis</small>
                    </div>
                    <div>
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fs-7 fw-semibold shadow-xs">
                            <i class="bi bi-calendar-event text-primary me-1.5"></i> <?= $tglIndo ?>
                        </span>
                    </div>
                </div>

                <!-- Summary Stat Pills -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-sm-3">
                        <div class="p-2.5 rounded-3 bg-primary-subtle border border-primary-subtle text-center">
                            <small class="text-primary fw-semibold d-block" style="font-size:0.75rem;"><i class="bi bi-people-fill me-1"></i>Total Hadir</small>
                            <span class="fs-5 fw-bold text-primary" id="statTotal"><?= $totalAll ?></span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-2.5 rounded-3 bg-info-subtle border border-info-subtle text-center">
                            <small class="text-info-emphasis fw-semibold d-block" style="font-size:0.75rem;"><i class="bi bi-backpack-fill me-1"></i>Siswa</small>
                            <span class="fs-5 fw-bold text-info-emphasis" id="statSiswa"><?= $totalSiswa ?></span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-2.5 rounded-3 bg-warning-subtle border border-warning-subtle text-center">
                            <small class="text-warning-emphasis fw-semibold d-block" style="font-size:0.75rem;"><i class="bi bi-person-workspace me-1"></i>Guru / GTK</small>
                            <span class="fs-5 fw-bold text-warning-emphasis" id="statGuru"><?= $totalGuru ?></span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-2.5 rounded-3 bg-success-subtle border border-success-subtle text-center">
                            <small class="text-success fw-semibold d-block" style="font-size:0.75rem;"><i class="bi bi-box-arrow-right me-1"></i>Pulang</small>
                            <span class="fs-5 fw-bold text-success" id="statPulang"><?= $totalPulang ?></span>
                        </div>
                    </div>
                </div>

                <!-- Filter & Search Toolbar -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div class="btn-group btn-group-sm flex-wrap" role="group">
                        <button type="button" class="btn btn-outline-primary active log-tab-btn px-2.5 py-1 fw-semibold" data-filter="all" onclick="setLogFilterTab('all', this)">
                            Semua (<span id="tabCountAll"><?= $totalAll ?></span>)
                        </button>
                        <button type="button" class="btn btn-outline-info log-tab-btn px-2.5 py-1 fw-semibold" data-filter="siswa" onclick="setLogFilterTab('siswa', this)">
                            Siswa (<span id="tabCountSiswa"><?= $totalSiswa ?></span>)
                        </button>
                        <button type="button" class="btn btn-outline-warning text-dark log-tab-btn px-2.5 py-1 fw-semibold" data-filter="guru" onclick="setLogFilterTab('guru', this)">
                            Guru/GTK (<span id="tabCountGuru"><?= $totalGuru ?></span>)
                        </button>
                        <button type="button" class="btn btn-outline-success log-tab-btn px-2.5 py-1 fw-semibold" data-filter="pulang" onclick="setLogFilterTab('pulang', this)">
                            Sudah Pulang (<span id="tabCountPulang"><?= $totalPulang ?></span>)
                        </button>
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <div class="input-group input-group-sm" style="max-width: 220px;">
                            <span class="input-group-text bg-light border-end-0 rounded-start-pill"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="logSearchInput" class="form-control bg-light border-start-0 rounded-end-pill" placeholder="Cari nama, NIS, NIP..." oninput="filterLogRows()">
                        </div>
                        <button type="button" onclick="window.location.reload()" class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" title="Segarkan Data Log" style="width:31px; height:31px;">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>

                <!-- Table Container with Scroll & Sticky Header -->
                <div class="table-responsive rounded-3 border" style="max-height: 520px; overflow-y: auto;">
                    <table class="table table-hover align-middle small mb-0" id="tablePresensiToday">
                        <thead class="table-light sticky-top shadow-xs" style="z-index: 5;">
                            <tr class="text-secondary fw-semibold text-uppercase" style="font-size: 0.74rem; letter-spacing: 0.4px;">
                                <th style="width: 45px;" class="text-center">#</th>
                                <th>Nama Lengkap</th>
                                <th style="width: 135px;">NIP / NIS</th>
                                <th style="width: 145px;">Rombel / Peran</th>
                                <th style="width: 125px;" class="text-center">Jam Masuk</th>
                                <th style="width: 125px;" class="text-center">Jam Pulang</th>
                                <th style="width: 135px;" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="presensiTbody">
                            <?php if (empty($presensiHariIni)): ?>
                                <tr id="emptyRow">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="py-3">
                                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                                                <i class="bi bi-qr-code-scan fs-2 text-primary opacity-75"></i>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1">Belum Ada Presensi Hari Ini</h6>
                                            <p class="text-muted small mb-0">Arahkan kartu QR code siswa atau NIP guru ke scanner kamera.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($presensiHariIni as $i => $p): 
                                    $jamMasukStr = !empty($p['waktu_masuk']) ? date('H:i', strtotime($p['waktu_masuk'])) : (!empty($p['waktu_hadir']) ? date('H:i', strtotime($p['waktu_hadir'])) : '-');
                                    $jamPulangStr = !empty($p['waktu_pulang']) ? date('H:i', strtotime($p['waktu_pulang'])) : '-';
                                    $isPulang = !empty($p['waktu_pulang']);
                                    $isGuru = ($p['role_label'] ?? '') === 'Guru' || ($p['nama_kelas'] ?? '') === 'GTK / Pendidik';
                                    $nisVal = $p['nis'] ?: ($p['nisn'] ?: '-');
                                    $rowId = 'row-' . ($isGuru ? 'g-' . $nisVal : 's-' . $nisVal);
                                    $namaClean = Security::safeText($p['nama_lengkap']);
                                    $initial = strtoupper(substr($namaClean, 0, 1));
                                    $isLate = (stripos($p['keterangan'] ?? '', 'terlambat') !== false || stripos($p['status'] ?? '', 'terlambat') !== false);
                                ?>
                                    <tr id="<?= htmlspecialchars($rowId) ?>" class="border-bottom log-presensi-row" 
                                        data-role="<?= $isGuru ? 'guru' : 'siswa' ?>" 
                                        data-status="<?= $isPulang ? 'pulang' : 'masuk' ?>" 
                                        data-search="<?= strtolower(htmlspecialchars($namaClean . ' ' . $nisVal . ' ' . ($p['nama_kelas'] ?? ''))) ?>">
                                        <td class="text-center text-muted fw-semibold"><?= $i + 1 ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle <?= $isGuru ? 'bg-warning-subtle text-warning-emphasis' : 'bg-primary-subtle text-primary' ?> fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px; height:32px; font-size:0.8rem;">
                                                    <?= $isGuru ? '<i class="bi bi-person-fill"></i>' : $initial ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark lh-sm"><?= htmlspecialchars($namaClean) ?></div>
                                                    <small class="text-muted d-md-none font-monospace"><?= htmlspecialchars($nisVal) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                                <i class="bi bi-person-badge text-secondary me-1"></i><?= htmlspecialchars($nisVal) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($isGuru): ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                                    <i class="bi bi-briefcase-fill me-1 text-warning"></i>Guru / GTK
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2.5 py-1">
                                                    <i class="bi bi-mortarboard-fill me-1 text-info"></i><?= htmlspecialchars($p['nama_kelas'] ?: 'Tanpa Kelas') ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold">
                                                <i class="bi bi-box-arrow-in-right me-1"></i><?= $jamMasukStr ?> WIB
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($isPulang): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold">
                                                    <i class="bi bi-box-arrow-right me-1"></i><?= $jamPulangStr ?> WIB
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border border-dashed rounded-pill px-2 py-1 fw-normal" style="font-size:0.75rem;">
                                                    <i class="bi bi-dash-circle me-1"></i>Belum Pulang
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($isPulang): ?>
                                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 shadow-xs">
                                                    <i class="bi bi-check-all me-1"></i>Lengkap
                                                </span>
                                            <?php elseif ($isLate): ?>
                                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 shadow-xs">
                                                    <i class="bi bi-clock-history me-1"></i>Terlambat
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1 shadow-xs">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Hadir
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
</main>

<!-- QR Scanner JS Library via CDN -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode = new Html5Qrcode("qr-reader");
let statTotal = <?= (int)($totalAll ?? 0) ?>;
let statSiswa = <?= (int)($totalSiswa ?? 0) ?>;
let statGuru = <?= (int)($totalGuru ?? 0) ?>;
let statPulang = <?= (int)($totalPulang ?? 0) ?>;
let currentLogFilter = 'all';
let isProcessing = false;
let lastScannedText = '';
let lastScannedTime = 0;
let voiceEnabled = true;

function toggleVoiceAnnouncement() {
    voiceEnabled = !voiceEnabled;
    const icon = document.getElementById('voiceIcon');
    const text = document.getElementById('voiceText');
    const btn = document.getElementById('toggleVoiceBtn');
    if (voiceEnabled) {
        icon.className = 'bi bi-volume-up-fill me-1';
        text.textContent = 'Suara ON';
        btn.className = 'btn btn-sm btn-light border text-success rounded-pill px-2 py-0 fw-semibold';
        speakVoiceMessage('Suara pengumuman presensi diaktifkan.');
    } else {
        icon.className = 'bi bi-volume-mute-fill me-1';
        text.textContent = 'Suara OFF';
        btn.className = 'btn btn-sm btn-light border text-muted rounded-pill px-2 py-0 fw-semibold';
        if ('speechSynthesis' in window) window.speechSynthesis.cancel();
    }
}

function speakVoiceMessage(textToSpeak) {
    if (!voiceEnabled || !('speechSynthesis' in window)) return;
    try {
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(textToSpeak);
        utterance.lang = 'id-ID';
        utterance.rate = 0.95;
        utterance.pitch = 1.0;

        const voices = window.speechSynthesis.getVoices();
        const idVoice = voices.find(v => (v.lang && (v.lang.includes('id') || v.lang.includes('ID'))));
        if (idVoice) {
            utterance.voice = idVoice;
        }

        window.speechSynthesis.speak(utterance);
    } catch(e) {
        console.error('Speech synthesis error:', e);
    }
}

if ('speechSynthesis' in window) {
    window.speechSynthesis.onvoiceschanged = () => {
        window.speechSynthesis.getVoices();
    };
}

function playAudioBeep() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.2);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.2);
    } catch(e) {}
}

function onScanSuccess(decodedText, decodedResult) {
    processQrData(decodedText);
}

function processQrData(data) {
    const now = Date.now();
    if (data === lastScannedText && (now - lastScannedTime) < 3000) {
        return;
    }
    if (isProcessing) return;

    lastScannedText = data;
    lastScannedTime = now;
    submitScan(data);
}

function processManualScan() {
    const nis = document.getElementById('manualNis').value.trim();
    if (!nis) { 
        Swal.fire('Peringatan', 'NIS / NISN tidak boleh kosong!', 'warning'); 
        return; 
    }
    submitScan(nis);
}

function submitScan(identifier) {
    if (isProcessing) return;
    isProcessing = true;

    const resultEl = document.getElementById('scanResult');
    resultEl.className = 'alert alert-info border-0 rounded-3 shadow-sm mb-3';
    resultEl.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div> Memproses presensi...';
    resultEl.classList.remove('d-none');

<?php
$currentScanModuleUrl = $_GET['url'] ?? '';
$isAdminScanRoute = (strpos($currentScanModuleUrl, 'admin/') === 0 || strtolower(AuthHelper::user()['role_name'] ?? '') === 'administrator');
$processScanEndpoint = $isAdminScanRoute ? BASE_URL . 'index.php?url=admin/processScan' : BASE_URL . 'index.php?url=guru/processScan';
?>
    fetch('<?= $processScanEndpoint ?>', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `identifier=${encodeURIComponent(identifier)}&csrf_token=<?= Security::csrfToken() ?>`
    })
    .then(r => r.json())
    .then(d => {
        // Continuous Kiosk Toast Notification (No OK Button, Auto-Dismiss, Non-blocking)
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        if (d.success) {
            playAudioBeep();
            
            const isPulang = d.type === 'pulang';
            const statusBadge = d.is_late ? '<span class="badge bg-warning text-dark ms-2"><i class="bi bi-clock-history me-1"></i>Terlambat</span>' : '<span class="badge bg-success ms-2"><i class="bi bi-check-circle me-1"></i>Hadir Tepat Waktu</span>';

            resultEl.className = isPulang ? 'alert alert-primary border-0 rounded-3 shadow-sm mb-3' : 'alert alert-success border-0 rounded-3 shadow-sm mb-3';
            resultEl.innerHTML = '<i class="bi bi-check-circle-fill me-1 fs-5 align-middle"></i> <strong>' + d.nama + '</strong> (' + d.kelas + ') — ' + (isPulang ? 'Pulang: ' + d.jam : 'Masuk: ' + d.jam) + (!isPulang ? statusBadge : '');

            const isGuru = (d.role === 'Guru');
            const cleanNis = escapeHtml(d.nis || '-');
            const rowId = 'row-' + (isGuru ? 'g-' + cleanNis : 's-' + cleanNis);
            const existingRow = document.getElementById(rowId);
            const wasExistingPulang = existingRow && existingRow.getAttribute('data-status') === 'pulang';

            if (!existingRow) {
                statTotal++;
                if (isGuru) {
                    statGuru++;
                } else {
                    statSiswa++;
                }
            }
            if (isPulang && !wasExistingPulang) {
                statPulang++;
            }
            updateStatBadges();

            // Dynamic Real-time Table Prepend (No Page Reload)
            const tbody = document.getElementById('presensiTbody');
            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) emptyRow.remove();

            const rowId = 'row-' + (d.role === 'Guru' ? 'g-' + d.nis : 's-' + d.nis);
            const existingRow = document.getElementById(rowId);
            if (existingRow) existingRow.remove();

            const cleanName = escapeHtml(d.nama || 'Pengguna');
            const cleanKelas = escapeHtml(d.kelas || (isGuru ? 'GTK / Pendidik' : 'Tanpa Kelas'));
            const initial = cleanName.charAt(0).toUpperCase() || 'U';

            const tr = document.createElement('tr');
            tr.id = rowId;
            tr.className = 'border-bottom log-presensi-row bg-success-subtle';
            tr.setAttribute('data-role', isGuru ? 'guru' : 'siswa');
            tr.setAttribute('data-status', isPulang ? 'pulang' : 'masuk');
            tr.setAttribute('data-search', ((d.nama || '') + ' ' + (d.nis || '') + ' ' + (d.kelas || '')).toLowerCase());

            const roleBadge = isGuru ? 
                '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1"><i class="bi bi-briefcase-fill me-1 text-warning"></i>Guru / GTK</span>' : 
                '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2.5 py-1"><i class="bi bi-mortarboard-fill me-1 text-info"></i>' + cleanKelas + '</span>';

            const jamMasukDisp = d.jam_masuk || d.jam || '-';
            const jamPulangDisp = isPulang ? (d.jam_pulang || d.jam || '-') : '';

            const pulangBadge = isPulang ?
                '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold"><i class="bi bi-box-arrow-right me-1"></i>' + jamPulangDisp + '</span>' :
                '<span class="badge bg-light text-muted border border-dashed rounded-pill px-2 py-1 fw-normal" style="font-size:0.75rem;"><i class="bi bi-dash-circle me-1"></i>Belum Pulang</span>';

            let tableStatusBadge = '';
            if (isPulang) {
                tableStatusBadge = '<span class="badge bg-primary text-white rounded-pill px-2.5 py-1 shadow-xs"><i class="bi bi-check-all me-1"></i>Lengkap</span>';
            } else if (d.is_late) {
                tableStatusBadge = '<span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 shadow-xs"><i class="bi bi-clock-history me-1"></i>Terlambat</span>';
            } else {
                tableStatusBadge = '<span class="badge bg-success text-white rounded-pill px-2.5 py-1 shadow-xs"><i class="bi bi-check-circle-fill me-1"></i>Hadir</span>';
            }

            tr.innerHTML = `
                <td class="text-center"><span class="badge bg-success text-white rounded-circle px-1.5 py-1" style="font-size:0.65rem;" title="Presensi Baru">Baru</span></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle ${isGuru ? 'bg-warning-subtle text-warning-emphasis' : 'bg-primary-subtle text-primary'} fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px; height:32px; font-size:0.8rem;">
                            ${isGuru ? '<i class="bi bi-person-fill"></i>' : initial}
                        </div>
                        <div>
                            <div class="fw-bold text-dark lh-sm">${cleanName}</div>
                            <small class="text-muted d-md-none font-monospace">${cleanNis}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                        <i class="bi bi-person-badge text-secondary me-1"></i>${cleanNis}
                    </span>
                </td>
                <td>${roleBadge}</td>
                <td class="text-center">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold">
                        <i class="bi bi-box-arrow-in-right me-1"></i>${jamMasukDisp}
                    </span>
                </td>
                <td class="text-center">${pulangBadge}</td>
                <td class="text-center">${tableStatusBadge}</td>
            `;

            tbody.insertBefore(tr, tbody.firstChild);
            filterLogRows();
            setTimeout(() => { tr.classList.remove('bg-success-subtle'); }, 2000);

            document.getElementById('manualNis').value = '';

            // TTS Voice Announcement
            let speechText = '';
            if (isPulang) {
                speechText = `Terima kasih ${d.nama}. Presensi pulang berhasil. Hati-hati di jalan.`;
            } else {
                if (d.is_late) {
                    speechText = `Selamat pagi ${d.nama}. Presensi masuk berhasil, terlambat.`;
                } else {
                    speechText = `Selamat pagi ${d.nama}. Presensi masuk berhasil, hadir tepat waktu.`;
                }
            }
            speakVoiceMessage(speechText);

            // Centered SweetAlert Modal Popup WITHOUT OK Button (Auto-dismiss in 2.5s)
            Swal.fire({
                icon: isPulang ? 'info' : (d.is_late ? 'warning' : 'success'),
                title: isPulang ? 'Presensi PULANG Terekam!' : (d.is_late ? 'Presensi MASUK (Terlambat)' : 'Presensi MASUK (Tepat Waktu)'),
                html: isPulang ? `<b>${d.nama}</b> (${d.kelas}) berhasil presensi PULANG pukul <b>${d.jam_pulang}</b>. (Masuk: ${d.jam_masuk}).` : `<b>${d.nama}</b> (${d.kelas}) berhasil presensi MASUK pukul <b>${d.jam_masuk}</b>.<br><span class="badge bg-success-subtle text-success border border-success mt-2 px-3 py-1 fs-6">Status: ${d.status_keterangan || 'Hadir Tepat Waktu'}</span>`,
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            }).then(() => {
                isProcessing = false;
            });

            setTimeout(() => { isProcessing = false; }, 2500);
        } else {
            const isNotScheduled = d.is_not_scheduled;
            resultEl.className = (d.already_attended || isNotScheduled) ? 'alert alert-warning border-0 rounded-3 shadow-sm mb-3' : 'alert alert-danger border-0 rounded-3 shadow-sm mb-3';
            resultEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (d.message || 'Data tidak ditemukan.');
            
            let swalTitle = 'Gagal!';
            let swalIcon = 'error';
            let speechText = '';

            if (d.already_attended) {
                swalTitle = 'Presensi Sudah Lengkap';
                swalIcon = 'info';
                speechText = `Presensi ${d.nama || ''} sudah lengkap hari ini.`;
            } else if (isNotScheduled) {
                swalTitle = 'Penolakan Presensi';
                swalIcon = 'warning';
                speechText = `Maaf, ${d.message || 'Bukan jadwal presensi.'}`;
            } else {
                speechText = `Peringatan! ${d.message || 'Data tidak ditemukan.'}`;
            }

            speakVoiceMessage(speechText);

            // Centered SweetAlert Modal Popup WITHOUT OK Button for errors (Auto-dismiss in 3s)
            Swal.fire({ 
                icon: swalIcon, 
                title: swalTitle, 
                text: d.message || 'Data tidak ditemukan.',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            }).then(() => {
                isProcessing = false;
            });

            setTimeout(() => { isProcessing = false; }, 3000);
        }
    })
    .catch(() => {
        isProcessing = false;
        resultEl.className = 'alert alert-danger border-0 rounded-3 shadow-sm mb-3';
        resultEl.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Koneksi gagal. Periksa jaringan internet Anda.';
    });
}

let currentFacingMode = "environment";

function initCameraScanner() {
    startCameraWithFacingMode(currentFacingMode);
}

function startCameraWithFacingMode(facingMode) {
    const qrContainer = document.getElementById('qr-reader');
    if (!qrContainer) return;

    // Mobile-optimized config without forced aspectRatio (fixes portrait camera black screen)
    const config = { 
        fps: 10, 
        qrbox: { width: 230, height: 230 },
        disableFlip: facingMode === "environment"
    };

    const runStart = () => {
        return html5QrCode.start(
            { facingMode: facingMode }, 
            config, 
            onScanSuccess, 
            (err) => {}
        ).then(() => {
            ensureVideoPlaysInline();
            populateCameraList();
        });
    };

    if (html5QrCode.isScanning) {
        html5QrCode.stop().then(runStart).catch(runStart);
    } else {
        runStart().catch(err1 => {
            console.warn("FacingMode constraint failed, trying getCameras...", err1);
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    populateCameraList(devices);
                    let targetCam = devices.find(d => /back|rear|belakang|environment/i.test(d.label)) || devices[0];
                    return html5QrCode.start(targetCam.id, config, onScanSuccess, (err) => {});
                } else {
                    return html5QrCode.start({ facingMode: "user" }, config, onScanSuccess, (err) => {});
                }
            })
            .then(() => {
                ensureVideoPlaysInline();
            })
            .catch(err2 => {
                console.error("Camera start failed entirely:", err2);
                showCameraPermissionPromptUI();
            });
        });
    }
}

function ensureVideoPlaysInline() {
    setTimeout(() => {
        const qrContainer = document.getElementById('qr-reader');
        if (!qrContainer) return;
        const videoEl = qrContainer.querySelector('video');
        if (videoEl) {
            videoEl.setAttribute('playsinline', 'true');
            videoEl.setAttribute('webkit-playsinline', 'true');
            videoEl.setAttribute('muted', 'true');
            videoEl.play().catch(() => {});
        }
    }, 300);
}

function toggleCameraFacing() {
    currentFacingMode = (currentFacingMode === "environment") ? "user" : "environment";
    startCameraWithFacingMode(currentFacingMode);
}

function populateCameraList(devicesList) {
    const select = document.getElementById('cameraSelect');
    if (!select) return;

    const fillSelect = (devices) => {
        if (!devices || !devices.length) return;
        select.innerHTML = '<option value="">-- Pilih Kamera Input --</option>';
        devices.forEach((device, index) => {
            const opt = document.createElement('option');
            opt.value = device.id;
            opt.text = device.label || `Kamera ${index + 1}`;
            select.appendChild(opt);
        });
        select.classList.remove('d-none');
    };

    if (devicesList) {
        fillSelect(devicesList);
    } else {
        Html5Qrcode.getCameras().then(devices => fillSelect(devices)).catch(() => {});
    }
}

function switchCamera(cameraId) {
    if (!cameraId) return;
    const config = { fps: 10, qrbox: { width: 220, height: 220 } };
    if (html5QrCode.isScanning) {
        html5QrCode.stop().then(() => {
            html5QrCode.start(cameraId, config, onScanSuccess, (err) => {}).then(() => ensureVideoPlaysInline());
        }).catch(() => {
            html5QrCode.start(cameraId, config, onScanSuccess, (err) => {}).then(() => ensureVideoPlaysInline());
        });
    } else {
        html5QrCode.start(cameraId, config, onScanSuccess, (err) => {}).then(() => ensureVideoPlaysInline());
    }
}

function showCameraPermissionPromptUI() {
    const isHttps = window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    let httpsNotice = '';
    if (!isHttps) {
        httpsNotice = `
            <div class="alert alert-danger border-0 rounded-3 mt-3 p-2 text-start small">
                <i class="bi bi-shield-lock-fill me-1 fw-bold"></i> <b>Perhatian HTTPS:</b> Browser HP (Android Chrome / Safari iOS) melarang stream kamera jika diakses melalui IP HTTP (tanpa SSL). Silakan akses via HTTPS atau buka di PC/Laptop.
            </div>
        `;
    }

    const qrContainer = document.getElementById('qr-reader');
    qrContainer.innerHTML = `
        <div class="card border-0 bg-light rounded-4 p-4 text-center my-2 shadow-xs">
            <div class="mb-3">
                <span class="bg-warning-subtle text-warning p-3 rounded-circle d-inline-block shadow-xs">
                    <i class="bi bi-camera-video-off-fill fs-2"></i>
                </span>
            </div>
            <h6 class="fw-bold text-dark mb-1">Kamera Belum Aktif</h6>
            <p class="text-muted small mb-3">Klik tombol di bawah untuk mencoba membuka kamera HP Anda kembali.</p>
            <div>
                <button type="button" onclick="startCameraWithFacingMode(currentFacingMode)" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm">
                    <i class="bi bi-camera-video-fill me-2"></i> Coba Buka Kamera HP
                </button>
            </div>
            ${httpsNotice}
        </div>
    `;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function updateStatBadges() {
    const elTotal = document.getElementById('statTotal');
    const elTabAll = document.getElementById('tabCountAll');
    if (elTotal) elTotal.textContent = statTotal;
    if (elTabAll) elTabAll.textContent = statTotal;

    const elSiswa = document.getElementById('statSiswa');
    const elTabSiswa = document.getElementById('tabCountSiswa');
    if (elSiswa) elSiswa.textContent = statSiswa;
    if (elTabSiswa) elTabSiswa.textContent = statSiswa;

    const elGuru = document.getElementById('statGuru');
    const elTabGuru = document.getElementById('tabCountGuru');
    if (elGuru) elGuru.textContent = statGuru;
    if (elTabGuru) elTabGuru.textContent = statGuru;

    const elPulang = document.getElementById('statPulang');
    const elTabPulang = document.getElementById('tabCountPulang');
    if (elPulang) elPulang.textContent = statPulang;
    if (elTabPulang) elTabPulang.textContent = statPulang;
}

function setLogFilterTab(filterType, btnEl) {
    currentLogFilter = filterType;
    document.querySelectorAll('.log-tab-btn').forEach(btn => btn.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
    filterLogRows();
}

function filterLogRows() {
    const query = (document.getElementById('logSearchInput')?.value || '').trim().toLowerCase();
    const rows = document.querySelectorAll('.log-presensi-row');
    
    rows.forEach(row => {
        const role = row.getAttribute('data-role');
        const status = row.getAttribute('data-status');
        const searchText = row.getAttribute('data-search') || '';

        let matchTab = true;
        if (currentLogFilter === 'siswa') {
            matchTab = (role === 'siswa');
        } else if (currentLogFilter === 'guru') {
            matchTab = (role === 'guru');
        } else if (currentLogFilter === 'pulang') {
            matchTab = (status === 'pulang');
        }

        const matchSearch = !query || searchText.includes(query);

        if (matchTab && matchSearch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Start camera scanner on page load
initCameraScanner();
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
