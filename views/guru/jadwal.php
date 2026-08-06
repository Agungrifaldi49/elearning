<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-clock-history text-warning me-2"></i>Jadwal Mengajar Saya</h4>
                <p class="text-muted small mb-0">Jadwal resmi Kegiatan Belajar Mengajar (KBM) Guru per-hari pada periode akademik aktif.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold">
                    <i class="bi bi-calendar-event me-1"></i> Periode Aktif: <?= htmlspecialchars($activeTa['tahun_ajaran'] ?? '2025/2026') ?> - <?= htmlspecialchars($activeTa['semester'] ?? 'Ganjil') ?>
                </span>
                <span class="badge bg-secondary px-3 py-2 rounded-pill fw-bold">
                    <i class="bi bi-eye-fill me-1"></i> Mode Lihat Saja (Read-Only)
                </span>
            </div>
        </div>

        <!-- Schedule By Day Cards -->
        <div class="row g-4 mb-4">
            <?php foreach ($hariList as $hari): 
                $items = $jadwalByHari[$hari] ?? [];
                $isToday = (date('N') == array_search($hari, ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']) + 1);
            ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card-custom p-4 h-100 shadow-sm border-top border-4 <?= $isToday ? 'border-success' : 'border-primary' ?>">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">
                                <i class="bi bi-calendar-day text-primary me-2"></i>Hari <?= $hari ?>
                            </h5>
                            <?php if ($isToday): ?>
                                <span class="badge bg-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Hari Ini</span>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border"><?= count($items) ?> Sesi</span>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($items)): ?>
                            <div class="p-3 bg-light rounded-3 text-center text-muted small">
                                <i class="bi bi-calendar-x d-block fs-4 mb-1 text-secondary"></i>
                                Tidak ada jadwal mengajar pada hari <?= $hari ?>.
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($items as $j): ?>
                                    <div class="p-3 bg-light rounded-4 border">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-warning text-dark fw-bold">
                                                <i class="bi bi-clock me-1"></i><?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?> WIB
                                            </span>
                                            <span class="badge bg-primary-subtle text-primary fw-bold">
                                                <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($j['ruangan'] ?? 'Ruang Kelas') ?>
                                            </span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($j['nama_mapel']) ?></h6>
                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                            <small class="text-muted">Kelas Target:</small>
                                            <span class="badge bg-success text-white fw-bold">
                                                <i class="bi bi-building me-1"></i><?= htmlspecialchars($j['nama_kelas']) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
