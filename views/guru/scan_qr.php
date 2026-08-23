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
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-list-check text-primary me-2"></i> Log Presensi Hari Ini — <?= date('d F Y') ?>
                    </h6>
                    <span class="badge bg-success rounded-pill px-3 py-2 fs-6">
                        Total Hadir: <span id="totalHadir" class="fw-bold"><?= count($presensiHariIni ?? []) ?></span> Siswa
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Lengkap</th>
                                <th>NIP / NIS</th>
                                <th>Rombel / Peran</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status Presensi</th>
                            </tr>
                        </thead>
                        <tbody id="presensiTbody">
                            <?php if (empty($presensiHariIni)): ?>
                                <tr id="emptyRow">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-qr-code fs-1 d-block mb-2 text-secondary"></i>
                                        <small class="fw-semibold">Belum ada presensi hari ini. Silakan mulai scan QR Code.</small>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($presensiHariIni as $i => $p): 
                                    $jamMasukStr = !empty($p['waktu_masuk']) ? date('H:i', strtotime($p['waktu_masuk'])) : (!empty($p['waktu_hadir']) ? date('H:i', strtotime($p['waktu_hadir'])) : '-');
                                    $jamPulangStr = !empty($p['waktu_pulang']) ? date('H:i', strtotime($p['waktu_pulang'])) : '-';
                                    $isPulang = !empty($p['waktu_pulang']);
                                    $isGuru = ($p['role_label'] ?? '') === 'Guru' || ($p['nama_kelas'] ?? '') === 'GTK / Pendidik';
                                ?>
                                    <tr class="border-bottom">
                                        <td><span class="badge bg-secondary rounded-circle py-1 px-2"><?= $i + 1 ?></span></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                                        <td><code><?= htmlspecialchars($p['nis'] ?: ($p['nisn'] ?: '-')) ?></code></td>
                                        <td>
                                            <?php if ($isGuru): ?>
                                                <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1"><i class="bi bi-person-workspace me-1 text-warning"></i>Guru / GTK</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($p['nama_kelas'] ?: 'Tanpa Kelas') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold text-success"><i class="bi bi-box-arrow-in-right me-1"></i><?= $jamMasukStr ?> WIB</td>
                                        <td class="fw-bold text-primary">
                                            <?php if ($isPulang): ?>
                                                <i class="bi bi-box-arrow-right me-1"></i><?= $jamPulangStr ?> WIB
                                            <?php else: ?>
                                                <span class="text-muted fw-normal small">Belum Scan Pulang</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($isPulang): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1"><i class="bi bi-check-all me-1"></i>Lengkap (Masuk & Pulang)</span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Hadir</span>
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
let scanCount = <?= count($presensiHariIni ?? []) ?>;
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

            if (!isPulang) {
                scanCount++;
                const countEl = document.getElementById('totalHadir');
                if (countEl) countEl.textContent = scanCount;
            }

            // Dynamic Real-time Table Prepend (No Page Reload)
            const tbody = document.getElementById('presensiTbody');
            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) emptyRow.remove();

            const rowId = 'row-' + (d.role === 'Guru' ? 'g-' + d.nis : 's-' + d.nis);
            const existingRow = document.getElementById(rowId);
            if (existingRow) existingRow.remove();

            const tr = document.createElement('tr');
            tr.className = 'border-bottom bg-success-subtle';
            tr.id = rowId;

            const roleBadge = (d.role === 'Guru') ? 
                '<span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1"><i class="bi bi-person-workspace me-1 text-warning"></i>Guru / GTK</span>' : 
                `<span class="badge bg-light text-dark border">${d.kelas}</span>`;

            const tableStatusBadge = isPulang ?
                '<span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1"><i class="bi bi-check-all me-1"></i>Lengkap (Masuk & Pulang)</span>' :
                '<span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Hadir</span>';

            tr.innerHTML = `
                <td><span class="badge bg-success rounded-circle py-1 px-2">Baru</span></td>
                <td class="fw-bold text-dark">${d.nama}</td>
                <td><code>${d.nis}</code></td>
                <td>${roleBadge}</td>
                <td class="fw-bold text-success"><i class="bi bi-box-arrow-in-right me-1"></i>${d.jam_masuk || d.jam}</td>
                <td class="fw-bold text-primary">${isPulang ? '<i class="bi bi-box-arrow-right me-1"></i>' + d.jam_pulang : '<span class="text-muted fw-normal small">Belum Scan Pulang</span>'}</td>
                <td>${tableStatusBadge}</td>
            `;

            tbody.insertBefore(tr, tbody.firstChild);

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

            // Floating Auto-Dismiss Toast Alert (No OK button)
            Toast.fire({
                icon: 'success',
                title: isPulang ? `Presensi PULANG: ${d.nama}` : `Presensi MASUK: ${d.nama}`,
                text: isPulang ? `Pulang pukul ${d.jam_pulang}` : `Masuk pukul ${d.jam_masuk} (${d.status_keterangan || 'Tepat Waktu'})`
            });

            // Re-enable fast scanning mode instantly (1.5 seconds delay for duplicate prevention)
            setTimeout(() => { isProcessing = false; }, 1500);
        } else {
            const isNotScheduled = d.is_not_scheduled;
            resultEl.className = (d.already_attended || isNotScheduled) ? 'alert alert-warning border-0 rounded-3 shadow-sm mb-3' : 'alert alert-danger border-0 rounded-3 shadow-sm mb-3';
            resultEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (d.message || 'Data tidak ditemukan.');
            
            let swalTitle = 'Gagal';
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

            // Non-blocking Auto-Dismiss Toast without OK button
            Toast.fire({ 
                icon: swalIcon, 
                title: swalTitle, 
                text: d.message || 'Data tidak ditemukan.' 
            });

            setTimeout(() => { isProcessing = false; }, 1500);
        }
    })
    .catch(() => {
        isProcessing = false;
        resultEl.className = 'alert alert-danger border-0 rounded-3 shadow-sm mb-3';
        resultEl.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Koneksi gagal. Periksa jaringan internet Anda.';
    });
}

function switchCamera(cameraId) {
    if (!cameraId) return;
    html5QrCode.stop().then(() => {
        startScanner(cameraId);
    }).catch(() => {
        startScanner(cameraId);
    });
}

function startScanner(cameraId) {
    html5QrCode.start(
        cameraId,
        { fps: 10, qrbox: { width: 240, height: 240 } },
        onScanSuccess,
        (err) => {}
    ).catch(err => {
        startScannerWithFacingMode();
    });
}

function startScannerWithFacingMode() {
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 240, height: 240 } },
        onScanSuccess,
        (err) => {}
    ).catch(err => {
        document.getElementById('qr-reader').innerHTML = '<div class="alert alert-warning m-2 rounded-3"><i class="bi bi-exclamation-triangle-fill me-1"></i> Tidak dapat mengakses kamera peramban. Silakan beri izin kamera atau gunakan input manual NIS/NISN di bawah.</div>';
    });
}

// Camera initialization
Html5Qrcode.getCameras().then(devices => {
    if (devices && devices.length) {
        const select = document.getElementById('cameraSelect');
        if (select) {
            select.innerHTML = '<option value="">-- Pilih Kamera Input --</option>';
            devices.forEach((device, index) => {
                const opt = document.createElement('option');
                opt.value = device.id;
                opt.text = device.label || `Kamera ${index + 1}`;
                if (index === 0) opt.selected = true;
                select.appendChild(opt);
            });
            select.classList.remove('d-none');
        }
        startScanner(devices[0].id);
    } else {
        startScannerWithFacingMode();
    }
}).catch(err => {
    startScannerWithFacingMode();
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
