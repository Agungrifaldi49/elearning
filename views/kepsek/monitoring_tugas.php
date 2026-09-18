<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-card-checklist text-primary me-2"></i>Monitoring Penugasan & Evaluasi Siswa</h4>
                <p class="text-muted small mb-0">Pengawasan pemberian tugas oleh guru, tingkat kepatuhan pengumpulan siswa, dan progres penilaian/koreksi nilai.</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fs-6">
                    <i class="bi bi-shield-check me-1"></i> Mode Pengawasan Eksekutif
                </span>
            </div>
        </div>

        <!-- Summary Stats -->
        <?php
        $totalTugas = count($tugasList);
        $totalTerkumpul = array_sum(array_column($tugasList, 'total_pengumpulan'));
        $totalDinilai = array_sum(array_column($tugasList, 'total_dinilai'));
        $avgProgressKoreksi = $totalTerkumpul > 0 ? round(($totalDinilai / $totalTerkumpul) * 100, 1) : 0;
        ?>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-primary text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Total Tugas Terbit</div>
                    <div class="display-6 fw-bold my-1"><?= $totalTugas ?> Tugas</div>
                    <small>Seluruh Mata Pelajaran</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-success text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Tugas Terkumpul</div>
                    <div class="display-6 fw-bold my-1"><?= $totalTerkumpul ?> Berkas</div>
                    <small>Pengumpulan Peserta Didik</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-info text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Sudah Dinilai Guru</div>
                    <div class="display-6 fw-bold my-1"><?= $totalDinilai ?> Berkas</div>
                    <small>Progres Koreksi Guru: <b><?= $avgProgressKoreksi ?>%</b></small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-warning text-dark">
                    <div class="small fw-semibold text-uppercase opacity-75">Menunggu Koreksi</div>
                    <div class="display-6 fw-bold my-1"><?= max(0, $totalTerkumpul - $totalDinilai) ?> Berkas</div>
                    <small>Pending Penilaian Guru</small>
                </div>
            </div>
        </div>

        <!-- Tabel Monitoring Tugas -->
        <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-journal-text text-primary me-2"></i>Daftar Tugas KBM Seluruh Rombel</h6>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">Data Realtime</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle <?= !empty($tugasList) ? 'datatable' : '' ?>">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Judul Tugas</th>
                            <th>Mata Pelajaran & Guru</th>
                            <th>Target Kelas</th>
                            <th>Tenggat Waktu</th>
                            <th class="text-center">Siswa Kumpul</th>
                            <th class="text-center">Koreksi Guru</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tugasList)): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada tugas yang diterbitkan guru.</td></tr>
                        <?php else: ?>
                            <?php foreach ($tugasList as $i => $t): 
                                $isExpired = !empty($t['deadline']) && strtotime($t['deadline']) < time();
                                $kumpul = (int)($t['total_pengumpulan'] ?? 0);
                                $dinilai = (int)($t['total_dinilai'] ?? 0);
                                $pctKoreksi = $kumpul > 0 ? round(($dinilai / $kumpul) * 100) : 0;
                            ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($t['judul']) ?></div>
                                        <?php if (!empty($t['file_tugas'])): ?>
                                            <span class="badge bg-light text-muted border"><i class="bi bi-paperclip me-1"></i>Lampiran Berkas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-primary"><?= htmlspecialchars($t['nama_mapel'] ?? '-') ?></div>
                                        <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($t['nama_guru'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-dark border border-info-subtle">
                                            <?= htmlspecialchars($t['nama_kelas'] ?? 'Semua Kelas') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($t['deadline'])): ?>
                                            <div class="small fw-semibold <?= $isExpired ? 'text-danger' : 'text-dark' ?>">
                                                <?= date('d M Y, H:i', strtotime($t['deadline'])) ?> WIB
                                            </div>
                                            <small class="text-muted"><?= $isExpired ? 'Tenggat Berakhir' : 'Masih Berjalan' ?></small>
                                        <?php else: ?>
                                            <span class="text-muted small">Tanpa Batas Waktu</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-6"><?= $kumpul ?> Siswa</span>
                                    </td>
                                    <td class="text-center" style="min-width: 140px;">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span><?= $dinilai ?> / <?= $kumpul ?></span>
                                            <b><?= $pctKoreksi ?>%</b>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pctKoreksi ?>%;"></div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isExpired): ?>
                                            <span class="badge bg-secondary">Berakhir</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Aktif</span>
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
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
