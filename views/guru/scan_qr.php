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
                                        <small class="fw-semibold">Belum ada presensi hari ini. Silakan mulai scan QR Code siswa.</small>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($presensiHariIni as $i => $p): 
                                    $jamMasukStr = !empty($p['waktu_masuk']) ? date('H:i', strtotime($p['waktu_masuk'])) : (!empty($p['waktu_hadir']) ? date('H:i', strtotime($p['waktu_hadir'])) : '-');
                                    $jamPulangStr = !empty($p['waktu_pulang']) ? date('H:i', strtotime($p['waktu_pulang'])) : '-';
                                    $isPulang = !empty($p['waktu_pulang']);
                                ?>
                                    <tr class="border-bottom">
                                        <td><span class="badge bg-secondary rounded-circle py-1 px-2"><?= $i + 1 ?></span></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                                        <td><code><?= htmlspecialchars($p['nis'] ?: ($p['nisn'] ?: '-')) ?></code></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['nama_kelas'] ?: 'Tanpa Kelas') ?></span></td>
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
                                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Hadir (KBM All Mapel)</span>
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
        isProcessing = false;
        if (d.success) {
            playAudioBeep();
            
            const isPulang = d.type === 'pulang';
            resultEl.className = isPulang ? 'alert alert-primary border-0 rounded-3 shadow-sm mb-3' : 'alert alert-success border-0 rounded-3 shadow-sm mb-3';
            resultEl.innerHTML = '<i class="bi bi-check-circle-fill me-1 fs-5 align-middle"></i> <strong>' + d.nama + '</strong> (' + d.kelas + ') — ' + (isPulang ? 'Pulang: ' + d.jam : 'Masuk: ' + d.jam);

            if (!isPulang) {
                scanCount++;
                const countEl = document.getElementById('totalHadir');
                if (countEl) countEl.textContent = scanCount;
            }

            const tbody = document.getElementById('presensiTbody');
            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) emptyRow.remove();

            document.getElementById('manualNis').value = '';

            Swal.fire({ 
                icon: 'success', 
                title: isPulang ? 'Presensi PULANG Terekam!' : 'Presensi MASUK Terekam!', 
                html: isPulang ? `<b>${d.nama}</b> (${d.kelas}) berhasil presensi PULANG pukul ${d.jam_pulang}. (Masuk: ${d.jam_masuk}).` : `<b>${d.nama}</b> (${d.kelas}) berhasil presensi MASUK pukul ${d.jam_masuk}. Otomatis HADIR di seluruh KBM mapel hari ini.`, 
                timer: 3000, 
                showConfirmButton: false 
            }).then(() => {
                window.location.reload();
            });
        } else {
            const isNotScheduled = d.is_not_scheduled;
            resultEl.className = (d.already_attended || isNotScheduled) ? 'alert alert-warning border-0 rounded-3 shadow-sm mb-3' : 'alert alert-danger border-0 rounded-3 shadow-sm mb-3';
            resultEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (d.message || 'Siswa tidak ditemukan.');
            
            let swalTitle = 'Gagal!';
            let swalIcon = 'error';
            if (d.already_attended) {
                swalTitle = 'Presensi Sudah Lengkap';
                swalIcon = 'info';
            } else if (isNotScheduled) {
                swalTitle = 'Bukan Jadwal Masuk Sekolah';
                swalIcon = 'warning';
            }

            Swal.fire({ 
                icon: swalIcon, 
                title: swalTitle, 
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
