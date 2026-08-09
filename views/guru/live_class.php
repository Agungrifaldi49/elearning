<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-camera-reels-fill text-danger me-2"></i>Live Virtual Meeting & Hybrid Class</h4>
            <p class="text-muted small mb-0">Integrasi Google Meet & Zoom untuk tatap muka digital interaktif.</p>
        </div>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createRoomModal">
            <div class="d-flex align-items-center gap-1">
                <i class="bi bi-plus-circle me-1"></i> Buat Room Meeting Baru
            </div>
        </button>
    </div>

    <div class="row g-4">
        <!-- Google Meet Shortcut Card -->
        <div class="col-12 col-md-6">
            <div class="card-custom p-4 border-start border-4 border-success">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-success-subtle text-success p-3 rounded-4">
                        <i class="bi bi-camera-video-fill fs-2"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Google Meet Quick Launcher</h6>
                        <small class="text-muted">Buat ruang rapat instan secara gratis melalui Google Workspace.</small>
                    </div>
                </div>
                <a href="https://meet.google.com/new" target="_blank" class="btn btn-success w-100 fw-bold">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Launch Instant Google Meet
                </a>
            </div>
        </div>

        <!-- Zoom Shortcut Card -->
        <div class="col-12 col-md-6">
            <div class="card-custom p-4 border-start border-4 border-primary">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary-subtle text-primary p-3 rounded-4">
                        <i class="bi bi-display-fill fs-2"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Zoom Meeting Portal</h6>
                        <small class="text-muted">Buka aplikasi Zoom untuk memulai sesi kelas terjadwal.</small>
                    </div>
                </div>
                <a href="https://zoom.us/start/videon" target="_blank" class="btn btn-primary w-100 fw-bold">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Launch Zoom Meeting
                </a>
            </div>
        </div>
    </div>

    <!-- Active Scheduled Meetings Table -->
    <div class="card-custom p-4 mt-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-calendar-event-fill text-primary me-2"></i>Jadwal Live Class Terdaftar</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Topik / Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Platform</th>
                        <th>Waktu Mulai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td class="fw-bold">Pembahasan Algoritma & Struktur Data (PHP 8)</td>
                        <td><span class="badge bg-info text-dark">X RPL 1</span></td>
                        <td><span class="badge bg-success"><i class="bi bi-camera-video me-1"></i>Google Meet</span></td>
                        <td>Hari Ini, 09:00 WIB</td>
                        <td><span class="badge bg-danger animate-pulse">BERLANGSUNG</span></td>
                        <td>
                            <a href="https://meet.google.com" target="_blank" class="btn btn-sm btn-danger px-3 fw-bold">
                                <i class="bi bi-play-fill me-1"></i> Masuk Room
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td class="fw-bold">Praktikum Desain UI/UX & Figma Layout</td>
                        <td><span class="badge bg-info text-dark">XI DKV 2</span></td>
                        <td><span class="badge bg-primary"><i class="bi bi-display me-1"></i>Zoom</span></td>
                        <td>Besok, 13:00 WIB</td>
                        <td><span class="badge bg-secondary">Terjadwal</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary px-3" onclick="navigator.clipboard.writeText('https://zoom.us/j/123456789'); Swal.fire('Disalin!','Link Zoom disalin','success')">
                                <i class="bi bi-share me-1"></i> Bagikan Link
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>

<!-- Modal Create Room -->
<div class="modal fade" id="createRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold"><i class="bi bi-plus-circle text-danger me-2"></i>Buat Schedule Live Class</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <form onsubmit="event.preventDefault(); Swal.fire('Sukses!','Live Class berhasil dijadwalkan dan dinotifikasikan ke siswa.','success'); $('#createRoomModal').modal('hide');">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Topik Pertemuan</label>
                        <input type="text" class="form-control" required placeholder="Contoh: Sesi Tanya Jawab Ujian Tengah Semester">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Platform Video Call</label>
                        <select class="form-select" required>
                            <option value="meet">Google Meet</option>
                            <option value="zoom">Zoom Meeting</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Link Meeting (URL)</label>
                        <input type="url" class="form-control" required placeholder="https://meet.google.com/abc-defg-hij">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Tanggal</label>
                            <input type="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Jam Mulai</label>
                            <input type="time" class="form-control" value="09:00" required>
                        </div>
                    </div>
                    <button class="btn btn-danger w-100 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Simpan & Bagikan ke Siswa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
