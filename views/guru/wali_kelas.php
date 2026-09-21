<?php
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/navbar.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';
?>

<main class="main-content px-3 px-md-4 py-3">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?url=guru/dashboard" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Wali Kelas</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-person-workspace text-info me-2"></i>Kelas Binaan (Wali Kelas) & E-Rapor
                </h4>
                <p class="text-muted small mb-0">
                    Kelola rekap nilai siswa rombel, pantau kehadiran/presensi peserta didik, dan cetak E-Rapor Digital secara massal maupun perorangan.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill font-monospace small">
                    <i class="bi bi-calendar3 me-1"></i> TA <?= htmlspecialchars($activeTa['tahun'] ?? 'Aktif') ?> (<?= htmlspecialchars($activeSemester) ?>)
                </span>
                <?php if ($selectedKelas && !empty($siswaList)): ?>
                    <a href="<?= BASE_URL ?>index.php?url=guru/cetakRaporRombel&kelas_id=<?= $selectedKelasId ?>" target="_blank" class="btn btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm">
                        <i class="bi bi-printer-fill me-1.5"></i> Cetak E-Rapor Sekaligus (<?= $countSiswa ?> Siswa)
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?= FlashHelper::display() ?>

        <!-- Selector Tab Jika Guru Ditugaskan Menjadi Wali Kelas Lebih Dari 1 Rombel -->
        <?php if (count($myWaliKelas) > 1): ?>
            <div class="card border-0 rounded-4 shadow-sm bg-white p-3 mb-4">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small fw-semibold me-2"><i class="bi bi-collection me-1"></i>Pilih Rombel Binaan:</span>
                    <div class="nav nav-pills gap-2" role="tablist">
                        <?php foreach ($myWaliKelas as $mwk): ?>
                            <?php $isSelected = ((int)$mwk['id'] === (int)$selectedKelasId); ?>
                            <a href="<?= BASE_URL ?>index.php?url=guru/waliKelas&kelas_id=<?= $mwk['id'] ?>" 
                               class="nav-link rounded-pill px-3 py-1.5 fw-semibold <?= $isSelected ? 'active bg-primary text-white shadow-sm' : 'bg-light text-dark' ?>">
                                <i class="bi bi-easel-fill me-1"></i><?= htmlspecialchars($mwk['nama_kelas']) ?>
                                <span class="badge <?= $isSelected ? 'bg-white text-primary' : 'bg-secondary' ?> rounded-pill ms-1"><?= (int)$mwk['total_siswa'] ?> Siswa</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stat Cards Ringkasan Kelas Binaan -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary p-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-easel-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Rombel Binaan</span>
                            <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($selectedKelas['nama_kelas'] ?? '-') ?></h5>
                            <small class="text-primary fw-semibold" style="font-size: 0.72rem;"><?= htmlspecialchars($selectedKelas['nama_jurusan'] ?? '') ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success p-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Total Peserta Didik</span>
                            <h5 class="fw-bold text-dark mb-0"><?= $countSiswa ?> <span class="fs-6 fw-normal text-muted">Siswa</span></h5>
                            <small class="text-success fw-semibold" style="font-size: 0.72rem;">Aktif Terdaftar</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-warning bg-opacity-10 text-warning p-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-award-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Rata-Rata Nilai Rombel</span>
                            <h5 class="fw-bold text-dark mb-0"><?= number_format($rombelAvgNilai, 1) ?></h5>
                            <small class="text-warning fw-semibold" style="font-size: 0.72rem;">Skala 100 E-Rapor</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-info bg-opacity-10 text-info p-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-calendar-check-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Tingkat Kehadiran</span>
                            <h5 class="fw-bold text-dark mb-0"><?= $rombelKehadiranPersen ?>%</h5>
                            <small class="text-info fw-semibold" style="font-size: 0.72rem;">Presensi Semester Ini</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card With Tabs -->
        <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom pt-3 pb-0 px-4">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#tabNilaiRapor" type="button" role="tab">
                            <i class="bi bi-file-earmark-text-fill text-primary me-1.5"></i>Rekap Nilai Siswa & Catatan E-Rapor
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#tabPresensi" type="button" role="tab">
                            <i class="bi bi-calendar2-check-fill text-success me-1.5"></i>Rekap Presensi Rombel
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content">
                    
                    <!-- TAB 1: REKAP NILAI SISWA & CATATAN E-RAPOR -->
                    <div class="tab-pane fade show active" id="tabNilaiRapor" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Daftar Nilai Siswa Rombel <?= htmlspecialchars($selectedKelas['nama_kelas'] ?? '') ?></h6>
                                <small class="text-muted">Sebagai wali kelas, Anda dapat mengisi <strong>Catatan Wali Kelas</strong> yang akan otomatis tercetak di lembar E-Rapor Digital masing-masing siswa.</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="<?= BASE_URL ?>index.php?url=guru/cetakRaporRombel&kelas_id=<?= $selectedKelasId ?>" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold">
                                    <i class="bi bi-printer me-1"></i> Cetak Semua Rapor (Massal)
                                </a>
                            </div>
                        </div>

                        <?php if (empty($siswaList)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                                Belum ada data peserta didik yang terdaftar pada rombel ini.
                            </div>
                        <?php else: ?>
                            <form action="<?= BASE_URL ?>index.php?url=guru/waliKelas&kelas_id=<?= $selectedKelasId ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= Security::getCsrfToken() ?>">
                                <input type="hidden" name="action" value="save_catatan_wali">

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-3" style="font-size: 0.83rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 40px;" class="text-center">No</th>
                                                <th style="min-width: 170px;">Identitas Siswa</th>
                                                <th style="width: 110px;" class="text-center">Rata-Rata Nilai</th>
                                                <th style="width: 90px;" class="text-center">Predikat</th>
                                                <th style="min-width: 280px;">Catatan Wali Kelas (Untuk E-Rapor)</th>
                                                <th style="width: 110px;" class="text-center">Aksi E-Rapor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($siswaList as $idx => $sw): ?>
                                                <?php 
                                                $sm = $studentSummary[$sw['id']] ?? [];
                                                $avg = (float)($sm['avg_nilai'] ?? 0);
                                                $pred = $sm['predikat'] ?? ['grade' => '-', 'class' => 'bg-secondary'];
                                                ?>
                                                <tr>
                                                    <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                                                    <td>
                                                        <strong class="text-dark d-block"><?= htmlspecialchars($sw['nama_lengkap']) ?></strong>
                                                        <small class="text-muted">NIS: <?= htmlspecialchars($sw['nis'] ?: ($sw['nisn'] ?: '-')) ?> | <?= htmlspecialchars($sw['jenis_kelamin'] ?: '-') ?></small>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (($sm['total_mapel'] ?? 0) > 0): ?>
                                                            <span class="fs-6 fw-bold <?= $avg >= 75 ? 'text-primary' : 'text-danger' ?>">
                                                                <?= number_format($avg, 1) ?>
                                                            </span>
                                                            <small class="d-block text-muted" style="font-size: 0.70rem;"><?= (int)$sm['total_mapel'] ?> Mapel Terdaftar</small>
                                                        <?php else: ?>
                                                            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                                                                <i class="bi bi-dash-circle me-1"></i>0 Mapel
                                                            </span>
                                                            <small class="d-block text-muted" style="font-size: 0.68rem;">Belum Daftar</small>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sm['enrolled_mapels'])): ?>
                                                            <div class="mt-1 d-flex flex-wrap justify-content-center gap-1">
                                                                <?php foreach ($sm['enrolled_mapels'] as $mName): ?>
                                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-0.5 px-1.5" style="font-size: 0.65rem;" title="<?= htmlspecialchars($mName) ?>">
                                                                        <?= htmlspecialchars(mb_strimwidth($mName, 0, 16, '...')) ?>
                                                                    </span>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (($sm['total_mapel'] ?? 0) > 0): ?>
                                                            <span class="badge <?= $pred['class'] ?> rounded-pill px-2.5 py-1">
                                                                <?= $pred['grade'] ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <textarea name="catatan[<?= $sw['id'] ?>]" rows="2" class="form-control form-control-sm rounded-3" placeholder="Tuliskan catatan apresiasi, motivasi, atau evaluasi belajar siswa untuk rapor..."><?= htmlspecialchars($sm['catatan_wali'] ?? '') ?></textarea>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?= BASE_URL ?>index.php?url=guru/cetakRaporSiswa&siswa_id=<?= $sw['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold" title="Cetak Lembar E-Rapor Siswa Ini">
                                                            <i class="bi bi-printer-fill me-1"></i> Cetak
                                                        </a>
                                                    </td>

                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>Klik tombol di samping untuk menyimpan seluruh Catatan Wali Kelas yang telah Anda masukkan.
                                    </small>
                                    <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm">
                                        <i class="bi bi-check2-circle me-1"></i> Simpan Seluruh Catatan Wali Kelas
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- TAB 2: REKAP PRESENSI ROMBEL -->
                    <div class="tab-pane fade" id="tabPresensi" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Rekapitulasi Kehadiran Siswa Rombel <?= htmlspecialchars($selectedKelas['nama_kelas'] ?? '') ?></h6>
                                <small class="text-muted">Data terhubung langsung dengan sistem absensi harian dan QR Code presensi.</small>
                            </div>
                            <!-- Tombol Tautan Cepat Ke Fitur Presensi yang Sudah Ada (Sesuai Instruksi User) -->
                            <div class="d-flex gap-2">
                                <a href="<?= BASE_URL ?>index.php?url=guru/absensi" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold">
                                    <i class="bi bi-calendar-check me-1"></i> Input Presensi KBM
                                </a>
                                <a href="<?= BASE_URL ?>index.php?url=guru/recapBulanan&kelas_id=<?= $selectedKelasId ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-semibold">
                                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Rekap Bulanan Kelas
                                </a>
                            </div>
                        </div>

                        <?php if (empty($siswaList)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                                Belum ada data siswa pada rombel ini.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.83rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40px;" class="text-center">No</th>
                                            <th style="min-width: 170px;">Nama Lengkap Siswa</th>
                                            <th style="width: 100px;" class="text-center">Hadir</th>
                                            <th style="width: 90px;" class="text-center">Sakit (S)</th>
                                            <th style="width: 90px;" class="text-center">Izin (I)</th>
                                            <th style="width: 110px;" class="text-center">Alpa (A)</th>
                                            <th style="width: 140px;" class="text-center">Persentase</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($siswaList as $idx => $sw): ?>
                                            <?php 
                                            $sm = $studentSummary[$sw['id']]['absensi'] ?? ['total' => 0, 'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'persen' => 100];
                                            $pct = (int)$sm['persen'];
                                            ?>
                                            <tr>
                                                <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                                                <td>
                                                    <strong class="text-dark d-block"><?= htmlspecialchars($sw['nama_lengkap']) ?></strong>
                                                    <small class="text-muted">NIS: <?= htmlspecialchars($sw['nis'] ?: ($sw['nisn'] ?: '-')) ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success-subtle text-success border border-success px-2.5 py-1">
                                                        <?= (int)$sm['hadir'] ?> Hari
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info-subtle text-info-emphasis border border-info px-2.5 py-1">
                                                        <?= (int)$sm['sakit'] ?> Hari
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2.5 py-1">
                                                        <?= (int)$sm['izin'] ?> Hari
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge <?= (int)$sm['alpa'] > 0 ? 'bg-danger-subtle text-danger border border-danger' : 'bg-light text-muted border' ?> px-2.5 py-1">
                                                        <?= (int)$sm['alpa'] ?> Hari
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                                        <div class="progress flex-grow-1" style="height: 6px; min-width: 60px;">
                                                            <div class="progress-bar <?= $pct >= 85 ? 'bg-success' : ($pct >= 75 ? 'bg-warning' : 'bg-danger') ?>" role="progressbar" style="width: <?= $pct ?>%;"></div>
                                                        </div>
                                                        <span class="fw-bold small text-dark"><?= $pct ?>%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
