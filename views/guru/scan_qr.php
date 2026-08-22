<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-qr-code-scan text-success me-2"></i>Scan QR Code Presensi Siswa</h4>
            <p class="text-muted small mb-0">Arahkan kamera ke QR Code pada Kartu Pelajar Digital siswa untuk mencatat presensi otomatis.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>index.php?url=guru/dashboard" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
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
                    <span class="badge bg-success-subtle text-success border border-success rounded-pill px-2 py-1">
                        <i class="bi bi-broadcast me-1"></i> Live
                    </span>
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
                        <i class="bi bi-keyboard-fill text-primary me-1"></i> Input NIS / NISN Manual:
                    </label>
                    <div class="input-group">
                        <input type="text" id="manualNis" class="form-control rounded-start-3" placeholder="Ketik NIS/NISN siswa..." onkeypress="if(event.key === 'Enter') processManualScan();">
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
                                <th>Nama Siswa</th>
                                <th>NIS / NISN</th>
                                <th>Kelas</th>
                                <th>Waktu Hadir</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="presensiTbody">
                            <?php if (empty($presensiHariIni)): ?>
                                <tr id="emptyRow">
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-qr-code fs-1 d-block mb-2 text-secondary"></i>
                                        <small class="fw-semibold">Belum ada presensi hari ini. Silakan mulai scan QR Code siswa.</small>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($presensiHariIni as $i => $p): ?>
                                    <tr class="border-bottom">
                                        <td><span class="badge bg-secondary rounded-circle py-1 px-2"><?= $i + 1 ?></span></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                                        <td><code><?= htmlspecialchars($p['nis'] ?: ($p['nisn'] ?: '-')) ?></code></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['nama_kelas'] ?: 'Tanpa Kelas') ?></span></td>
                                        <td class="fw-bold text-success"><?= date('H:i', strtotime($p['waktu_hadir'] ?? $p['created_at'])) ?> WIB</td>
                                        <td><span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Hadir</span></td>
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

    fetch('<?= BASE_URL ?>index.php?url=guru/processScan', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `identifier=${encodeURIComponent(identifier)}&csrf_token=<?= Security::csrfToken() ?>`
    })
    .then(r => r.json())
    .then(d => {
        isProcessing = false;
        if (d.success) {
            playAudioBeep();
            resultEl.className = 'alert alert-success border-0 rounded-3 shadow-sm mb-3';
            resultEl.innerHTML = '<i class="bi bi-check-circle-fill me-1 fs-5 align-middle"></i> <strong>' + d.nama + '</strong> (' + d.kelas + ') — ' + d.jam;

            scanCount++;
            const countEl = document.getElementById('totalHadir');
            if (countEl) countEl.textContent = scanCount;

            const tbody = document.getElementById('presensiTbody');
            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) emptyRow.remove();

            if (tbody) {
                const newRow = `
                    <tr class="table-success border-bottom">
                        <td><span class="badge bg-success rounded-circle py-1 px-2">${scanCount}</span></td>
                        <td class="fw-bold text-dark">${d.nama}</td>
                        <td><code>${d.nis}</code></td>
                        <td><span class="badge bg-light text-dark border">${d.kelas}</span></td>
                        <td class="fw-bold text-success">${d.jam}</td>
                        <td><span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Hadir</span></td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('afterbegin', newRow);
            }

            document.getElementById('manualNis').value = '';
            Swal.fire({ 
                icon: 'success', 
                title: 'Presensi Terekam!', 
                html: `<b>${d.nama}</b> (${d.kelas}) berhasil hadir pukul ${d.jam}.`, 
                timer: 2500, 
                showConfirmButton: false 
            });
        } else {
            resultEl.className = d.already_attended ? 'alert alert-warning border-0 rounded-3 shadow-sm mb-3' : 'alert alert-danger border-0 rounded-3 shadow-sm mb-3';
            resultEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (d.message || 'Siswa tidak ditemukan.');
            Swal.fire({ 
                icon: d.already_attended ? 'info' : 'error', 
                title: d.already_attended ? 'Presensi Sudah Tercatat' : 'Gagal!', 
                text: d.message || 'Siswa tidak ditemukan.' 
            });
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
