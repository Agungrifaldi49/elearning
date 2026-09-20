<?php
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/navbar.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';
?>

<main class="main-content px-3 px-md-4 py-3">
    <div class="container-fluid">

        <!-- Hero Banner Header -->
        <div class="p-4 p-md-5 mb-4 rounded-4 text-white shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #2563eb 100%);">
            <div class="d-flex align-items-center gap-3 position-relative z-1">
                <div class="p-3 bg-white bg-opacity-10 rounded-4 text-white d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="bi bi-activity fs-2"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-1">Kegiatan Ekstrakurikuler Siswa</h3>
                    <p class="text-white-50 small mb-0">
                        Kembangkan bakat, minat, kepemimpinan, dan raih prestasi non-akademik di SMK Muthia Harapan Cicalengka.
                    </p>
                </div>
            </div>
        </div>

        <?= FlashHelper::display() ?>

        <!-- SECTION 1: EKSTRAKURIKULER YANG SAYA IKUTI -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-check2-circle text-success me-2"></i>Ekstrakurikuler yang Saya Ikuti
                </h5>
                <span class="badge bg-primary rounded-pill px-3 py-1.5" style="font-size: 0.75rem;">
                    <?= count($myEkskul) ?> Ekskul Aktif
                </span>
            </div>

            <?php if (empty($myEkskul)): ?>
                <div class="card border-0 rounded-4 shadow-sm bg-white p-4 text-center">
                    <div class="py-3">
                        <i class="bi bi-activity text-muted fs-1 d-block mb-2"></i>
                        <h6 class="fw-bold text-dark mb-1">Anda Belum Mengikuti Ekstrakurikuler</h6>
                        <p class="text-muted small mb-3">
                            Pilihlah salah satu atau lebih kegiatan ekstrakurikuler di bawah ini untuk meningkatkan soft skill dan portofolio E-Rapor Anda.
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($myEkskul as $me): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card border-0 rounded-4 shadow-sm bg-white h-100 p-3.5 border-top border-4 border-primary position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($me['nama_ekskul']) ?></h5>
                                        <span class="badge bg-success-subtle text-success border border-success rounded-pill px-2.5 py-0.5 mt-1" style="font-size: 0.68rem;">
                                            <i class="bi bi-check-circle-fill me-1"></i>Anggota Aktif
                                        </span>
                                    </div>
                                    <form action="<?= BASE_URL ?>index.php?url=siswa/ekstrakurikuler" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan keikutsertaan pada ekstrakurikuler ini?')">
                                        <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                                        <input type="hidden" name="action" value="leave_ekskul">
                                        <input type="hidden" name="ekskul_id" value="<?= $me['ekskul_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" style="font-size: 0.72rem;" title="Batal Mengikuti">
                                            <i class="bi bi-x-circle me-1"></i> Batal Ikut
                                        </button>
                                    </form>
                                </div>

                                <ul class="list-unstyled small text-muted mb-3">
                                    <li class="mb-1.5">
                                        <i class="bi bi-person-badge text-primary me-1.5"></i>
                                        Pembimbing: <strong class="text-dark"><?= htmlspecialchars($me['pembimbing']) ?></strong>
                                    </li>
                                    <?php if (!empty($me['kontak_pembimbing'])): ?>
                                    <li class="mb-1.5">
                                        <i class="bi bi-telephone text-success me-1.5"></i>
                                        Kontak: <strong><?= htmlspecialchars($me['kontak_pembimbing']) ?></strong>
                                    </li>
                                    <?php endif; ?>
                                    <li class="mb-1.5">
                                        <i class="bi bi-calendar-event text-warning me-1.5"></i>
                                        Jadwal: <strong><?= htmlspecialchars($me['hari'] ?: '-') ?> (<?= htmlspecialchars($me['jam'] ?: '-') ?>)</strong>
                                    </li>
                                    <li>
                                        <i class="bi bi-geo-alt text-danger me-1.5"></i>
                                        Lokasi: <strong><?= htmlspecialchars($me['tempat'] ?: '-') ?></strong>
                                    </li>
                                </ul>

                                <!-- Status Nilai Capaian E-Rapor -->
                                <div class="mt-auto p-2.5 rounded-3 bg-light border">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="fw-bold text-dark" style="font-size: 0.72rem;">Nilai E-Rapor Digital:</small>
                                        <span class="badge bg-primary rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                            Predikat: <?= htmlspecialchars($me['predikat'] ?: 'Sangat Baik') ?>
                                        </span>
                                    </div>
                                    <p class="small text-muted mb-0 fst-italic" style="font-size: 0.74rem; line-height: 1.35;">
                                        <?= !empty($me['nilai_deskripsi']) 
                                            ? htmlspecialchars($me['nilai_deskripsi']) 
                                            : 'Deskripsi capaian sedang dalam proses evaluasi oleh Pembimbing Ekstrakurikuler.' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- SECTION 2: DAFTAR SELURUH EKSTRAKURIKULER TERSEDIA -->
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-1">
                <i class="bi bi-grid-fill text-primary me-2"></i>Pilihan Ekstrakurikuler Sekolah
            </h5>
            <p class="text-muted small mb-3">
                Silakan jelajahi ekstrakurikuler yang sesuai dengan minat Anda dan klik tombol <strong>Ikuti Ekstrakurikuler</strong>.
            </p>

            <?php if (empty($allEkskul)): ?>
                <div class="card border-0 rounded-4 shadow-sm bg-white p-5 text-center text-muted">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                    Belum ada kegiatan ekstrakurikuler aktif yang dibuka oleh sekolah saat ini.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($allEkskul as $ae): 
                        $isJoined = in_array($ae['id'], $enrolledIds);
                        $pembimbingStr = ($ae['tipe_pembimbing'] === 'luar') 
                            ? (!empty($ae['nama_pembimbing_luar']) ? $ae['nama_pembimbing_luar'] . ' (Instruktur Luar)' : 'Instruktur Luar')
                            : (!empty($ae['nama_guru']) ? $ae['nama_guru'] . ' (Guru)' : 'Guru Pembimbing');
                    ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card border-0 rounded-4 shadow-sm bg-white h-100 p-4 d-flex flex-column transition-hover">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                                        <i class="bi bi-award-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($ae['nama_ekskul']) ?></h5>
                                        <?php if ($ae['tipe_pembimbing'] === 'luar'): ?>
                                            <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 mt-1" style="font-size: 0.65rem;">
                                                <i class="bi bi-person-walking me-1"></i>Pembimbing Luar
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark rounded-pill px-2 py-0.5 mt-1" style="font-size: 0.65rem;">
                                                <i class="bi bi-person-badge me-1"></i>Guru Internal
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <p class="text-muted small mb-3 flex-grow-1" style="line-height: 1.4;">
                                    <?= htmlspecialchars($ae['deskripsi'] ?: 'Kegiatan pembinaan minat, bakat, dan ketrampilan siswa.') ?>
                                </p>

                                <ul class="list-unstyled small text-muted mb-4 border-top pt-2">
                                    <li class="mb-1.5 d-flex justify-content-between">
                                        <span><i class="bi bi-person text-primary me-1"></i> Pembimbing:</span>
                                        <strong class="text-dark text-end" style="max-width: 60%;"><?= htmlspecialchars($pembimbingStr) ?></strong>
                                    </li>
                                    <li class="mb-1.5 d-flex justify-content-between">
                                        <span><i class="bi bi-calendar3 text-warning me-1"></i> Jadwal:</span>
                                        <strong class="text-dark"><?= htmlspecialchars($ae['hari'] ?: '-') ?> (<?= htmlspecialchars($ae['jam'] ?: '-') ?>)</strong>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <span><i class="bi bi-geo-alt text-danger me-1"></i> Tempat:</span>
                                        <strong class="text-dark"><?= htmlspecialchars($ae['tempat'] ?: '-') ?></strong>
                                    </li>
                                </ul>

                                <div class="mt-auto">
                                    <?php if ($isJoined): ?>
                                        <button type="button" class="btn btn-outline-success w-100 rounded-pill fw-bold py-2" disabled>
                                            <i class="bi bi-check2-all me-1"></i> Sudah Anda Ikuti
                                        </button>
                                    <?php else: ?>
                                        <form action="<?= BASE_URL ?>index.php?url=siswa/ekstrakurikuler" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                                            <input type="hidden" name="action" value="join_ekskul">
                                            <input type="hidden" name="ekskul_id" value="<?= $ae['id'] ?>">
                                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-xs">
                                                <i class="bi bi-plus-circle me-1"></i> Ikuti Ekstrakurikuler
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
