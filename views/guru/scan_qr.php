<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-qr-code-scan text-success me-2"></i>Scan QR Code Presensi Siswa</h4>
            <p class="text-muted small mb-0">Arahkan kamera ke QR Code pada Kartu Pelajar Digital siswa untuk mencatat presensi otomatis.</p>
        </div>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Scanner -->
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card-custom p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-camera-video-fill text-success me-2"></i>Kamera QR Scanner</h6>

                <!-- Status -->
                <div id="scanResult" class="alert alert-info mb-3 d-none">
                    <i class="bi bi-clock-fill me-1"></i> Menunggu scan...
                </div>

                <!-- QR Reader Container -->
                <div id="qr-reader" class="mb-3" style="min-height:200px;"></div>

                <!-- Manual Input Fallback -->
                <div class="mt-3">
                    <label class="form-label small fw-semibold">Atau input NIS manual:</label>
                    <div class="input-group">
                        <input type="text" id="manualNis" class="form-control" placeholder="Ketik NIS siswa…">
                        <button class="btn btn-success" onclick="processManualScan()">
                            <i class="bi bi-check2-circle me-1"></i> Rekam
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Presensi Log Today -->
        <div class="col-12 col-md-6 col-lg-7">
            <div class="card-custom p-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-list-check text-primary me-2"></i>
                    Log Presensi Hari Ini — <?= date('d F Y') ?>
                </h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle small datatable">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>NIS</th>
                                <th>Kelas</th>
                                <th>Jam Hadir</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="presensiTbody">
                            <?php if (empty($presensiHariIni)): ?>
                                <tr id="emptyRow">
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-qr-code fs-1 d-block mb-2 text-secondary"></i>
                                        <small>Belum ada presensi hari ini. Mulai scan QR Code siswa.</small>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($presensiHariIni as $i => $p): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                                        <td><code><?= htmlspecialchars($p['nis']) ?></code></td>
                                        <td><?= htmlspecialchars($p['nama_kelas']) ?></td>
                                        <td><?= date('H:i', strtotime($p['waktu_hadir'])) ?></td>
                                        <td><span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Hadir</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 text-muted small">
                    Total hadir: <span id="totalHadir" class="fw-bold text-success"><?= count($presensiHariIni ?? []) ?></span> siswa
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

function onScanSuccess(decodedText, decodedResult) {
    processQrData(decodedText);
}

html5QrCode.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: { width: 220, height: 220 } },
    onScanSuccess,
    (err) => {}
).catch(err => {
    document.getElementById('qr-reader').innerHTML = '<div class="alert alert-warning m-2"><i class="bi bi-exclamation-triangle me-1"></i> Tidak dapat mengakses kamera. Gunakan input manual NIS di bawah.</div>';
});

function processQrData(data) {
    // Expected format: SMKMH-SISWA-NISN
    const parts = data.split('-');
    const nisn = parts[2] || data;
    submitScan(nisn);
}

function processManualScan() {
    const nis = document.getElementById('manualNis').value.trim();
    if (!nis) { Swal.fire('Peringatan', 'NIS tidak boleh kosong!', 'warning'); return; }
    submitScan(nis);
}

function submitScan(identifier) {
    const resultEl = document.getElementById('scanResult');
    resultEl.className = 'alert alert-info';
    resultEl.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div> Memproses presensi...';
    resultEl.classList.remove('d-none');

    fetch('<?= BASE_URL ?>index.php?url=guru/processScan', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `identifier=${encodeURIComponent(identifier)}&csrf_token=<?= Security::csrfToken() ?>`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            resultEl.className = 'alert alert-success';
            resultEl.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> <strong>' + d.nama + '</strong> (' + d.kelas + ') — ' + d.jam;

            scanCount++;
            document.getElementById('totalHadir').textContent = scanCount;

            const tbody = document.getElementById('presensiTbody');
            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) emptyRow.remove();

            tbody.insertAdjacentHTML('beforeend',
                `<tr class="table-success">
                    <td>${scanCount}</td>
                    <td class="fw-bold">${d.nama}</td>
                    <td><code>${d.nis}</code></td>
                    <td>${d.kelas}</td>
                    <td>${d.jam}</td>
                    <td><span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Hadir</span></td>
                </tr>`
            );

            document.getElementById('manualNis').value = '';
            Swal.fire({ icon:'success', title:'Presensi Terekam!', text: d.nama + ' berhasil hadir.', timer:2000, showConfirmButton:false });
        } else {
            resultEl.className = 'alert alert-danger';
            resultEl.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> ' + (d.message || 'Siswa tidak ditemukan.');
            Swal.fire({ icon:'error', title:'Gagal!', text: d.message || 'Siswa tidak ditemukan.' });
        }
    })
    .catch(() => {
        resultEl.className = 'alert alert-danger';
        resultEl.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Koneksi gagal. Periksa jaringan.';
    });
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
