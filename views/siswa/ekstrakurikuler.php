<?php
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/navbar.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';

// Helper pemilih ikon dan palet visual ekskul modern
function getEkskulTheme($namaEkskul) {
    $namaLower = strtolower(trim($namaEkskul));
    if (strpos($namaLower, 'pramuka') !== false) {
        return ['icon' => 'bi-compass-fill', 'gradient' => 'linear-gradient(135deg, #065f46 0%, #047857 100%)', 'color' => '#059669', 'bg_soft' => 'rgba(5, 150, 105, 0.1)'];
    } elseif (strpos($namaLower, 'paskibra') !== false) {
        return ['icon' => 'bi-flag-fill', 'gradient' => 'linear-gradient(135deg, #991b1b 0%, #dc2626 100%)', 'color' => '#dc2626', 'bg_soft' => 'rgba(220, 38, 38, 0.1)'];
    } elseif (strpos($namaLower, 'futsal') !== false || strpos($namaLower, 'bola') !== false) {
        return ['icon' => 'bi-dribbble', 'gradient' => 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)', 'color' => '#0284c7', 'bg_soft' => 'rgba(2, 132, 199, 0.1)'];
    } elseif (strpos($namaLower, 'basket') !== false || strpos($namaLower, 'voli') !== false) {
        return ['icon' => 'bi-trophy-fill', 'gradient' => 'linear-gradient(135deg, #d97706 0%, #b45309 100%)', 'color' => '#d97706', 'bg_soft' => 'rgba(217, 119, 6, 0.1)'];
    } elseif (strpos($namaLower, 'pmr') !== false || strpos($namaLower, 'uks') !== false) {
        return ['icon' => 'bi-heart-pulse-fill', 'gradient' => 'linear-gradient(135deg, #e11d48 0%, #be123c 100%)', 'color' => '#e11d48', 'bg_soft' => 'rgba(225, 29, 72, 0.1)'];
    } elseif (strpos($namaLower, 'rohis') !== false || strpos($namaLower, 'islam') !== false) {
        return ['icon' => 'bi-moon-stars-fill', 'gradient' => 'linear-gradient(135deg, #0f766e 0%, #115e59 100%)', 'color' => '#0f766e', 'bg_soft' => 'rgba(15, 118, 110, 0.1)'];
    } elseif (strpos($namaLower, 'it') !== false || strpos($namaLower, 'coding') !== false || strpos($namaLower, 'komputer') !== false || strpos($namaLower, 'robot') !== false) {
        return ['icon' => 'bi-laptop-fill', 'gradient' => 'linear-gradient(135deg, #4338ca 0%, #3730a3 100%)', 'color' => '#4338ca', 'bg_soft' => 'rgba(67, 56, 202, 0.1)'];
    } elseif (strpos($namaLower, 'musik') !== false || strpos($namaLower, 'band') !== false || strpos($namaLower, 'seni') !== false) {
        return ['icon' => 'bi-music-note-beamed', 'gradient' => 'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)', 'color' => '#7c3aed', 'bg_soft' => 'rgba(124, 58, 237, 0.1)'];
    } elseif (strpos($namaLower, 'english') !== false || strpos($namaLower, 'bahasa') !== false) {
        return ['icon' => 'bi-translate', 'gradient' => 'linear-gradient(135deg, #0891b2 0%, #0e7490 100%)', 'color' => '#0891b2', 'bg_soft' => 'rgba(8, 145, 178, 0.1)'];
    }
    return ['icon' => 'bi-award-fill', 'gradient' => 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)', 'color' => '#2563eb', 'bg_soft' => 'rgba(37, 99, 235, 0.1)'];
}
?>

<style>
/* Modern LMS Ekstrakurikuler UI System */
.ekskul-hero-card {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #2563eb 100%);
    border-radius: 20px;
    box-shadow: 0 12px 30px -5px rgba(30, 58, 138, 0.25);
    position: relative;
    overflow: hidden;
}

.ekskul-hero-card::after {
    content: '';
    position: absolute;
    width: 280px;
    height: 280px;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
    top: -80px;
    right: -80px;
    border-radius: 50%;
    pointer-events: none;
}

/* My Ekskul Modern Card */
.my-ekskul-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.my-ekskul-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.1);
    border-color: #cbd5e1;
}

.my-ekskul-topbar {
    height: 6px;
    width: 100%;
}

.my-ekskul-icon-box {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    box-shadow: 0 6px 14px rgba(0,0,0,0.12);
    flex-shrink: 0;
}

.my-ekskul-meta-pill {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 12px;
}

.my-ekskul-rapor-box {
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1.5px dashed #cbd5e1;
    border-radius: 14px;
    padding: 12px 14px;
}

/* Available Ekskul Modern Card */
.avail-ekskul-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.avail-ekskul-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
    border-color: #93c5fd;
}

/* Micro Badges */
.badge-coach-guru {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 0.70rem;
    font-weight: 600;
}

.badge-coach-luar {
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
    font-size: 0.70rem;
    font-weight: 600;
}

.badge-active-joined {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
    font-size: 0.70rem;
    font-weight: 700;
    letter-spacing: 0.2px;
}
</style>

<main class="main-content px-2 px-sm-3 px-md-4 py-3">
    <div class="container-fluid">

        <!-- Hero Banner Header -->
        <div class="ekskul-hero-card text-white p-4 p-md-5 mb-4">
            <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row gap-3 position-relative z-1">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-15 p-3 rounded-4 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 58px; height: 58px; backdrop-filter: blur(8px);">
                        <i class="bi bi-activity fs-2"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h3 class="fw-bold text-white mb-0" style="letter-spacing: -0.4px;">Portal Ekstrakurikuler Siswa</h3>
                            <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2.5 py-1 small">Tahun Ajaran <?= htmlspecialchars($activeTa['tahun_ajaran'] ?? '2026/2027') ?></span>
                        </div>
                        <p class="text-white-50 small mb-0 fw-medium">
                            Salurkan minat, bakat, kepemimpinan & raih capaian prestasi non-akademik resmi yang terintegrasi pada E-Rapor Digital.
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div class="bg-white bg-opacity-10 rounded-pill px-3.5 py-2 text-nowrap border border-white border-opacity-20">
                        <small class="text-white-50 d-block" style="font-size: 0.68rem; line-height: 1;">Status Keikutsertaan</small>
                        <strong class="text-white fs-6"><?= count($myEkskul) ?> Ekskul Diikuti</strong>
                    </div>
                </div>
            </div>
        </div>

        <?= FlashHelper::display() ?>

        <!-- =========================================================================
             SECTION 1: EKSTRAKURIKULER YANG SAYA IKUTI (MODERN, RAPIH & RESPONSIVE)
        ========================================================================= -->
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3.5">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success bg-opacity-10 text-success p-2 rounded-3">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0 fs-5">Ekstrakurikuler yang Saya Ikuti</h5>
                        <small class="text-muted">Kegiatan pembinaan aktif yang akan dicantumkan pada nilai rapor Anda.</small>
                    </div>
                </div>
                <?php if (!empty($myEkskul)): ?>
                    <span class="badge bg-success-subtle text-success border border-success rounded-pill px-3 py-1.5 fw-bold" style="font-size: 0.74rem;">
                        <i class="bi bi-patch-check-fill me-1"></i> Terdaftar Resmi
                    </span>
                <?php endif; ?>
            </div>

            <?php if (empty($myEkskul)): ?>
                <!-- Empty State Bersih & Menarik -->
                <div class="card border-0 rounded-4 shadow-sm bg-white p-4 p-md-5 text-center">
                    <div class="py-3 max-w-md mx-auto" style="max-width: 480px;">
                        <div class="bg-primary bg-opacity-10 text-primary d-inline-flex p-3 rounded-circle mb-3">
                            <i class="bi bi-compass fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Anda Belum Mengikuti Ekstrakurikuler</h5>
                        <p class="text-muted small mb-3">
                            Pilihlah salah satu atau lebih ekstrakurikuler di bawah ini. Kegiatan ekskul sangat berharga untuk melatih kemandirian dan mengisi nilai capaian di lembar E-Rapor Digital.
                        </p>
                        <a href="#daftarEkskulTersedia" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-xs">
                            <i class="bi bi-arrow-down-circle me-1.5"></i> Pilih Ekstrakurikuler Sekarang
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Grid Modern Cards: Responsive di Layar HP, Tablet, Laptop -->
                <div class="row g-3 g-md-4">
                    <?php foreach ($myEkskul as $me): 
                        $theme = getEkskulTheme($me['nama_ekskul']);
                        $isLuar = ($me['tipe_pembimbing'] === 'luar');
                        $pembimbingName = $isLuar 
                            ? (!empty($me['nama_pembimbing_luar']) ? $me['nama_pembimbing_luar'] : 'Instruktur Eksternal')
                            : (!empty($me['nama_guru_pembimbing']) ? $me['nama_guru_pembimbing'] : 'Guru Pembimbing');
                    ?>
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="my-ekskul-card h-100">
                                <!-- Top Accent Bar -->
                                <div class="my-ekskul-topbar" style="background: <?= $theme['gradient'] ?>;"></div>
                                
                                <div class="p-3.5 p-sm-4 d-flex flex-column h-100">
                                    <!-- Header Card: Icon, Judul Ekskul, Status Badge & Action -->
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                        <div class="d-flex align-items-center gap-3 overflow-hidden">
                                            <div class="my-ekskul-icon-box" style="background: <?= $theme['gradient'] ?>;">
                                                <i class="bi <?= $theme['icon'] ?> fs-4"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <h5 class="fw-bold text-dark mb-0.5 text-truncate" title="<?= htmlspecialchars($me['nama_ekskul']) ?>">
                                                    <?= htmlspecialchars($me['nama_ekskul']) ?>
                                                </h5>
                                                <span class="badge badge-active-joined rounded-pill px-2.5 py-0.5">
                                                    <i class="bi bi-check2-circle me-1"></i>Anggota Aktif
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Tombol Batal Ikut -->
                                        <form action="<?= BASE_URL ?>index.php?url=siswa/ekstrakurikuler" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan keikutsertaan pada ekstrakurikuler <?= htmlspecialchars(addslashes($me['nama_ekskul'])) ?>?')">
                                            <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                                            <input type="hidden" name="action" value="leave_ekskul">
                                            <input type="hidden" name="ekskul_id" value="<?= $me['ekskul_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 0.70rem;" title="Batalkan keikutsertaan">
                                                <i class="bi bi-x-circle me-1"></i> Batal
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Profil Pembimbing / Coach Info -->
                                    <div class="p-2.5 rounded-3 bg-light border mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <small class="text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.2px;">PEMBIMBING KEGIATAN:</small>
                                            <span class="badge <?= $isLuar ? 'badge-coach-luar' : 'badge-coach-guru' ?> rounded-pill px-2 py-0.5">
                                                <i class="bi <?= $isLuar ? 'bi-person-walking' : 'bi-person-badge' ?> me-1"></i><?= $isLuar ? 'Pembimbing Luar' : 'Guru Internal' ?>
                                            </span>
                                        </div>
                                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.84rem;">
                                            <?= htmlspecialchars($pembimbingName) ?>
                                        </div>
                                        <?php if (!empty($me['kontak_pembimbing'])): ?>
                                            <div class="mt-1 d-flex align-items-center gap-1.5">
                                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $me['kontak_pembimbing']) ?>" target="_blank" class="text-success text-decoration-none small fw-semibold" style="font-size: 0.72rem;">
                                                    <i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($me['kontak_pembimbing']) ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Jadwal & Lokasi 2 Kolom Mini -->
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="my-ekskul-meta-pill h-100">
                                                <div class="text-muted small" style="font-size: 0.68rem;">
                                                    <i class="bi bi-calendar3 text-warning me-1"></i> Jadwal:
                                                </div>
                                                <div class="fw-bold text-dark text-truncate" style="font-size: 0.76rem;" title="<?= htmlspecialchars($me['hari'] ?: '-') ?>">
                                                    <?= htmlspecialchars($me['hari'] ?: '-') ?>
                                                </div>
                                                <small class="text-muted text-truncate d-block" style="font-size: 0.70rem;">
                                                    <?= htmlspecialchars($me['jam'] ?: '-') ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="my-ekskul-meta-pill h-100">
                                                <div class="text-muted small" style="font-size: 0.68rem;">
                                                    <i class="bi bi-geo-alt text-danger me-1"></i> Lokasi:
                                                </div>
                                                <div class="fw-bold text-dark text-truncate" style="font-size: 0.76rem;" title="<?= htmlspecialchars($me['tempat'] ?: '-') ?>">
                                                    <?= htmlspecialchars($me['tempat'] ?: '-') ?>
                                                </div>
                                                <small class="text-muted text-truncate d-block" style="font-size: 0.70rem;">
                                                    Area Sekolah
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status Nilai E-Rapor Digital (Official Score Snippet) -->
                                    <div class="my-ekskul-rapor-box mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                                            <span class="fw-bold text-primary" style="font-size: 0.72rem;">
                                                <i class="bi bi-file-earmark-text-fill me-1"></i> Penilaian E-Rapor Digital
                                            </span>
                                            <span class="badge bg-primary rounded-pill px-2.5 py-0.5" style="font-size: 0.68rem;">
                                                Predikat: <?= htmlspecialchars($me['predikat'] ?: 'Sangat Baik') ?>
                                            </span>
                                        </div>
                                        <div class="text-dark bg-white p-2 rounded-2 border" style="font-size: 0.74rem; line-height: 1.35;">
                                            <?php if (!empty($me['nilai_deskripsi'])): ?>
                                                <i class="bi bi-quote text-primary me-0.5"></i><?= htmlspecialchars($me['nilai_deskripsi']) ?>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">
                                                    <i class="bi bi-hourglass-split me-1"></i>Deskripsi capaian nilai sedang dalam proses evaluasi oleh Pembimbing Ekstrakurikuler.
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- =========================================================================
             SECTION 2: DAFTAR SELURUH EKSTRAKURIKULER TERSEDIA
        ========================================================================= -->
        <div class="mb-4" id="daftarEkskulTersedia">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3.5">
                <div>
                    <h5 class="fw-bold text-dark mb-0 fs-5">
                        <i class="bi bi-grid-fill text-primary me-2"></i>Pilihan Ekstrakurikuler Sekolah
                    </h5>
                    <small class="text-muted">Jelajahi dan pilih kegiatan ekstrakurikuler yang sesuai dengan bakat dan minat Anda.</small>
                </div>
                <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill" style="font-size: 0.75rem;">
                    <?= count($allEkskul) ?> Pilihan Ekskul Aktif
                </span>
            </div>

            <?php if (empty($allEkskul)): ?>
                <div class="card border-0 rounded-4 shadow-sm bg-white p-5 text-center text-muted">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                    Belum ada kegiatan ekstrakurikuler aktif yang dibuka oleh sekolah saat ini.
                </div>
            <?php else: ?>
                <div class="row g-3 g-md-4">
                    <?php foreach ($allEkskul as $ae): 
                        $isJoined = in_array($ae['id'], $enrolledIds);
                        $theme = getEkskulTheme($ae['nama_ekskul']);
                        $isLuar = ($ae['tipe_pembimbing'] === 'luar');
                        $coachName = $isLuar 
                            ? (!empty($ae['nama_pembimbing_luar']) ? $ae['nama_pembimbing_luar'] : 'Instruktur Luar')
                            : (!empty($ae['nama_guru']) ? $ae['nama_guru'] : 'Guru Pembimbing');
                    ?>
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="avail-ekskul-card p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="my-ekskul-icon-box" style="background: <?= $theme['gradient'] ?>;">
                                        <i class="bi <?= $theme['icon'] ?> fs-4"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h5 class="fw-bold text-dark mb-0.5 text-truncate" title="<?= htmlspecialchars($ae['nama_ekskul']) ?>">
                                            <?= htmlspecialchars($ae['nama_ekskul']) ?>
                                        </h5>
                                        <span class="badge <?= $isLuar ? 'badge-coach-luar' : 'badge-coach-guru' ?> rounded-pill px-2.5 py-0.5">
                                            <i class="bi <?= $isLuar ? 'bi-person-walking' : 'bi-person-badge' ?> me-1"></i><?= $isLuar ? 'Pembimbing Luar' : 'Guru Internal' ?>
                                        </span>
                                    </div>
                                </div>

                                <p class="text-muted small mb-3 flex-grow-1" style="font-size: 0.78rem; line-height: 1.45;">
                                    <?= htmlspecialchars($ae['deskripsi'] ?: 'Kegiatan pembinaan minat, bakat, ketrampilan serta pembentukan karakter positif siswa.') ?>
                                </p>

                                <div class="bg-light p-2.5 rounded-3 border mb-3 small">
                                    <div class="d-flex justify-content-between mb-1 text-truncate">
                                        <span class="text-muted"><i class="bi bi-person text-primary me-1"></i> Pembimbing:</span>
                                        <strong class="text-dark text-truncate" style="max-width: 60%;" title="<?= htmlspecialchars($coachName) ?>"><?= htmlspecialchars($coachName) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted"><i class="bi bi-calendar3 text-warning me-1"></i> Jadwal:</span>
                                        <strong class="text-dark"><?= htmlspecialchars($ae['hari'] ?: '-') ?> (<?= htmlspecialchars($ae['jam'] ?: '-') ?>)</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted"><i class="bi bi-geo-alt text-danger me-1"></i> Lokasi:</span>
                                        <strong class="text-dark"><?= htmlspecialchars($ae['tempat'] ?: '-') ?></strong>
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <?php if ($isJoined): ?>
                                        <button type="button" class="btn btn-outline-success w-100 rounded-pill fw-bold py-2 shadow-xs" disabled style="font-size: 0.84rem;">
                                            <i class="bi bi-check2-all me-1"></i> Sudah Anda Ikuti
                                        </button>
                                    <?php else: ?>
                                        <form action="<?= BASE_URL ?>index.php?url=siswa/ekstrakurikuler" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                                            <input type="hidden" name="action" value="join_ekskul">
                                            <input type="hidden" name="ekskul_id" value="<?= $ae['id'] ?>">
                                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-xs" style="font-size: 0.84rem;">
                                                <i class="bi bi-plus-circle me-1.5"></i> Ikuti Ekstrakurikuler
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
