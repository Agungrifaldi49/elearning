<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<style>
/* Executive Modern Style for Gabung Kelas Siswa */
.gabung-hero-card {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #1d4ed8 100%);
    border-radius: 1.25rem;
    color: #ffffff;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3);
    position: relative;
    overflow: hidden;
}
.gabung-hero-card::after {
    content: "";
    position: absolute;
    top: -50%;
    right: -10%;
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.key-item-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
}
.key-item-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.08);
    border-color: #cbd5e1;
}
</style>

<main class="main-content px-3 px-md-4 py-4">
<div class="container-fluid pt-3 pt-md-4">

    <!-- Executive Hero Section -->
    <div class="gabung-hero-card p-4 p-md-5 mb-4 mt-4 mt-md-5 shadow-lg">
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-white bg-opacity-20 text-white px-3 py-1.5 rounded-pill fw-bold text-uppercase" style="font-size: 0.75rem;">
                        <i class="bi bi-shield-lock me-1"></i> Passcode Key Protection
                    </span>
                </div>
                <h3 class="fw-bold mb-2 text-white">Gabung Rombel & Pendaftaran Mapel Digital</h3>
                <p class="text-white-50 mb-0 leading-relaxed" style="max-width: 600px;">
                    Daftarkan diri Anda pada mata pelajaran pengampuan guru dengan Passcode Key resmi, atau gabung ke rombel kelas utama Anda untuk memulai KBM.
                </p>
            </div>
            
            <div class="col-12 col-lg-5 text-lg-end">
                <div class="d-flex flex-column flex-sm-row justify-content-lg-end gap-2">
                    <button type="button" class="btn btn-warning text-dark fw-bold px-4 py-2.5 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEnrollMapel">
                        <i class="bi bi-key-fill me-1.5"></i> Input Key Mapel Baru
                    </button>
                    <button type="button" class="btn btn-outline-light fw-bold px-4 py-2.5 rounded-3" data-bs-toggle="modal" data-bs-target="#modalJoinKodeKelas">
                        <i class="bi bi-bounding-box me-1.5"></i> Kode Rombel Kelas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Class Status Card -->
    <?php if (!empty($siswa['nama_kelas'])): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white border-start border-4 border-success">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-column flex-md-row gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success text-white p-3 rounded-4 fs-3 d-flex align-items-center justify-content-center shadow-xs" style="width:52px; height:52px; flex-shrink:0;">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">Siswa Aktif Terdaftar</span>
                                <span class="text-muted small">• Jurusan: <strong><?= htmlspecialchars($siswa['nama_jurusan'] ?? 'Umum') ?></strong></span>
                            </div>
                            <h5 class="fw-bold mb-0 text-dark">Rombel Kelas Utama: <span class="text-success"><?= htmlspecialchars($siswa['nama_kelas']) ?></span></h5>
                            <small class="text-muted">Kode Gabung Rombel Resmi: <code class="fw-bold text-primary">MH-<?= strtoupper(substr(md5($siswa['kelas_id']), 0, 6)) ?></code></small>
                        </div>
                    </div>
                    <span class="badge bg-success text-white px-3.5 py-2 rounded-pill fs-6 fw-semibold shadow-xs align-self-start align-self-md-center">
                        <i class="bi bi-patch-check-fill me-1"></i> Terverifikasi Sistem
                    </span>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white border-start border-4 border-warning">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning text-dark p-3 rounded-4 fs-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:50px; height:50px;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark fs-6">Anda Belum Terdaftar di Rombel Kelas Utama</h6>
                        <p class="small text-muted mb-0">Silakan masukkan Kode Akses dari Wali Kelas atau pilih salah satu Rombel Kelas di bawah ini untuk memulai KBM.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- SECTION 1: PENDAFTARAN MAPEL PER-GURU (KEY SYSTEM) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-sm-center flex-column flex-sm-row mb-4 gap-3 pb-3 border-bottom">
                <div>
                    <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock-fill text-warning fs-4"></i>
                        <span>Pendaftaran Mata Pelajaran Per-Guru (Key System)</span>
                    </h5>
                    <p class="text-muted small mb-0">Daftarkan diri Anda pada mata pelajaran pengampuan guru agar tugas, kuis, dan presensi tersimpan tepat.</p>
                </div>
                <button type="button" class="btn btn-warning text-dark fw-bold shadow-sm px-3.5 py-2 text-nowrap rounded-3 align-self-start align-self-sm-center" data-bs-toggle="modal" data-bs-target="#modalEnrollMapel">
                    <i class="bi bi-key-fill me-1.5"></i> Input Key Mapel Baru
                </button>
            </div>

            <div class="row g-3 g-md-4">
                <?php if (empty($mapelKeys)): ?>
                    <div class="col-12 text-center py-5 text-muted">
                        <div class="bg-light rounded-circle p-4 d-inline-flex mb-3 text-secondary">
                            <i class="bi bi-key display-5"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Belum Ada Key Mapel yang Diterbitkan</h6>
                        <p class="small text-muted mb-0">Silakan hubungi Guru Pengampu untuk mendapatkan Passcode Key Mata Pelajaran Anda.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($mapelKeys as $mk): 
                        $isEnrolled = isset($enrolledMapelGuruKeys[$mk['mapel_id'] . '_' . $mk['guru_id']]);
                    ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="key-item-card p-4 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-bold text-truncate" style="max-width:180px;">
                                            <?= htmlspecialchars($mk['nama_mapel']) ?>
                                        </span>
                                        <?php if ($isEnrolled): ?>
                                            <span class="badge bg-success text-white px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Terdaftar</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-lock-fill me-1"></i>Terkunci</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h6 class="fw-bold text-dark mb-1 fs-6"><i class="bi bi-person-badge me-1.5 text-secondary"></i><?= htmlspecialchars($mk['nama_guru']) ?></h6>
                                    <p class="small text-muted mb-3">Rombel Ajar: <strong><?= htmlspecialchars($mk['nama_kelas'] ?? 'Semua Rombel') ?></strong></p>
                                </div>

                                <div class="pt-2 border-top">
                                    <?php if ($isEnrolled): ?>
                                        <button type="button" class="btn btn-sm btn-success w-100 fw-bold py-2 rounded-3" disabled>
                                            <i class="bi bi-patch-check-fill me-1"></i> Akses Terbuka (Terdaftar)
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark w-100 fw-bold py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#modalEnrollMapel">
                                            <i class="bi bi-key-fill me-1"></i> Input Key Untuk Daftar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SECTION 2: KATALOG ROMBEL KELAS RESMI SEKOLAH -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-sm-center flex-column flex-sm-row mb-4 gap-3 pb-3 border-bottom">
                <div>
                    <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-grid-3x3-gap-fill text-primary fs-4"></i>
                        <span>Katalog Rombel Kelas Resmi Sekolah</span>
                    </h5>
                    <p class="text-muted small mb-0">Pilih salah satu rombel kelas terdaftar di bawah ini untuk mendaftar rombel utama Anda.</p>
                </div>
                <span class="badge bg-light text-dark border px-3.5 py-2 rounded-pill fw-semibold text-nowrap align-self-start align-self-sm-center">
                    Total: <?= count($kelasList) ?> Rombel
                </span>
            </div>

            <div class="row g-3 g-md-4">
                <?php if (empty($kelasList)): ?>
                    <div class="col-12 text-center py-4 text-muted">
                        Belum ada rombel kelas yang terdaftar di sekolah.
                    </div>
                <?php else: ?>
                    <?php foreach ($kelasList as $k): 
                        $isMyCurrentClass = ($siswa['kelas_id'] ?? 0) == $k['id'];
                        $classCode = 'MH-' . strtoupper(substr(md5($k['id']), 0, 6));
                    ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="key-item-card p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-bold mb-1">Tingkat <?= htmlspecialchars($k['tingkat'] ?? 'X') ?></span>
                                        <h5 class="fw-bold mb-0 text-primary fs-5"><?= htmlspecialchars($k['nama_kelas']) ?></h5>
                                    </div>
                                    <?php if ($isMyCurrentClass): ?>
                                        <span class="badge bg-success text-white px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-check-circle me-1"></i>Rombel Saya</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill">Rombel Sekolah</span>
                                    <?php endif; ?>
                                </div>

                                <p class="text-muted small mb-3"><?= htmlspecialchars($k['nama_jurusan'] ?? 'Umum') ?></p>

                                <div class="p-3 bg-light rounded-3 mb-3 small border">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted">Wali Kelas:</span>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($k['nama_walikelas'] ?? 'Belum Ditentukan') ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Kode Gabung:</span>
                                        <code class="fw-bold text-primary"><?= $classCode ?></code>
                                    </div>
                                </div>
                            </div>

                            <form action="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" method="POST" class="mt-auto pt-2 border-top">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="kelas_id" value="<?= $k['id'] ?>">

                                <?php if ($isMyCurrentClass): ?>
                                    <button type="button" class="btn btn-sm btn-success w-100 fw-bold py-2 rounded-3" disabled>
                                        <i class="bi bi-check-circle-fill me-1"></i> Terdaftar di Rombel Ini
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-sm btn-outline-primary w-100 fw-bold py-2 rounded-3" onclick="return confirm('Pindah / Gabung ke Rombel Kelas <?= htmlspecialchars($k['nama_kelas']) ?>?');">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Gabung Ke Rombel Ini
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
</main>

<!-- Modal Input Key Mapel Siswa -->
<div class="modal fade" id="modalEnrollMapel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered px-2">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title fs-6 fs-md-5"><i class="bi bi-key-fill text-warning me-2"></i>Pendaftaran Mata Pelajaran Per-Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" method="POST">
                <div class="modal-body p-3 p-md-4">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="enroll_mapel">

                    <div class="mb-3 text-center">
                        <div class="bg-warning-subtle text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:56px; height:56px;">
                            <i class="bi bi-shield-lock-fill fs-3"></i>
                        </div>
                        <h6 class="fw-bold text-dark fs-6">Masukkan Key / Password Mapel</h6>
                        <p class="text-muted small mb-0">Minta Kode Akses (Key) resmi dari Guru Pengampu atau Admin untuk mendaftar pada mata pelajaran tersebut.</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Kode Akses / Key Mapel (Passcode)</label>
                        <input type="text" name="key_mapel" class="form-control text-uppercase fw-bold text-center fs-6 fs-md-5 rounded-3" placeholder="Misal: MPL-1-2-582 atau WEB-GURU1" required style="letter-spacing:2px;">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between flex-nowrap">
                    <button type="button" class="btn btn-light rounded-3 fw-semibold px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark px-3 px-md-4 fw-bold rounded-3 text-nowrap"><i class="bi bi-check-circle-fill me-1"></i> Verifikasi & Daftar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Input Kode Akses Rombel Kelas -->
<div class="modal fade" id="modalJoinKodeKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered px-2">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold modal-title fs-6 fs-md-5"><i class="bi bi-bounding-box text-primary me-2"></i>Verifikasi Kode Rombel Utama</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" method="POST">
                <div class="modal-body p-3 p-md-4">
                    <?= Security::csrfField() ?>

                    <div class="mb-3 text-center">
                        <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:56px; height:56px;">
                            <i class="bi bi-check-circle-fill fs-3"></i>
                        </div>
                        <h6 class="fw-bold text-dark fs-6">Masukkan Kode Akses Rombel</h6>
                        <p class="text-muted small mb-0">Minta Kode Akses Rombel resmi dari Wali Kelas Anda untuk bergabung secara otomatis.</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Kode Akses Rombel Kelas</label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold bg-light">MH-</span>
                            <input type="text" name="kode_kelas" class="form-control text-uppercase fw-bold rounded-end-3" placeholder="6 Karakter (Misal: A1B2C3)" required style="letter-spacing:1px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-between flex-nowrap">
                    <button type="button" class="btn btn-light rounded-3 fw-semibold px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary text-white px-3 px-md-4 fw-bold rounded-3 text-nowrap"><i class="bi bi-box-arrow-in-right me-1"></i> Verifikasi Rombel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
