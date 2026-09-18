<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-patch-question-fill text-danger me-2"></i>Monitoring Ujian & Kuis CBT Online</h4>
                <p class="text-muted small mb-0">Pengawasan pelaksanaan Computer Based Test (CBT), keikutsertaan siswa, durasi pengerjaan, dan integritas ujian.</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fs-6">
                    <i class="bi bi-shield-check me-1"></i> Supervisi Ujian Eksekutif
                </span>
            </div>
        </div>

        <!-- Summary Stats -->
        <?php
        $totalQuiz = count($quizList);
        $totalPeserta = array_sum(array_column($quizList, 'total_peserta'));
        $activeExams = count(array_filter($quizList, fn($q) => ($q['status'] ?? '') === 'published'));
        ?>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-primary text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Total Paket Kuis / Ujian CBT</div>
                    <div class="display-6 fw-bold my-1"><?= $totalQuiz ?> Paket</div>
                    <small>Seluruh Jenjang & Mata Pelajaran</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-success text-white">
                    <div class="small fw-semibold text-uppercase opacity-75">Siswa Telah Mengerjakan</div>
                    <div class="display-6 fw-bold my-1"><?= $totalPeserta ?> Sesi</div>
                    <small>Hasil Jawaban Masuk ke Server</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-warning text-dark">
                    <div class="small fw-semibold text-uppercase opacity-75">Status Ujian Aktif</div>
                    <div class="display-6 fw-bold my-1"><?= $activeExams ?> Terbit</div>
                    <small>Siap Diakses / Dikerjakan Siswa</small>
                </div>
            </div>
        </div>

        <!-- Tabel Monitoring Quiz CBT -->
        <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-list-columns-reverse text-primary me-2"></i>Daftar Kuis & Ujian CBT Sekolah</h6>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">Data Realtime</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle datatable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Judul Ujian CBT</th>
                            <th>Mata Pelajaran & Guru</th>
                            <th>Target Rombel</th>
                            <th class="text-center">Durasi & Soal</th>
                            <th class="text-center">Jadwal Pelaksanaan</th>
                            <th class="text-center">Peserta Selesai</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($quizList)): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada paket kuis CBT yang terdaftar.</td></tr>
                        <?php else: ?>
                            <?php foreach ($quizList as $i => $q): 
                                $status = $q['status'] ?? 'draft';
                                $badgeSt = match($status) {
                                    'published' => 'bg-success',
                                    'draft' => 'bg-warning text-dark',
                                    default => 'bg-secondary'
                                };
                            ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($q['judul']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars(substr($q['deskripsi'] ?? 'Ujian CBT', 0, 50)) ?></small>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-primary"><?= htmlspecialchars($q['nama_mapel'] ?? '-') ?></div>
                                        <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($q['nama_guru'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars($q['nama_kelas'] ?? 'Semua Kelas') ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info-subtle text-dark border border-info-subtle mb-1 d-block">
                                            <i class="bi bi-stopwatch me-1"></i><?= $q['durasi_menit'] ?? 60 ?> Menit
                                        </span>
                                        <small class="text-muted"><?= $q['total_soal'] ?? 0 ?> Butir Soal</small>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($q['waktu_mulai'])): ?>
                                            <div class="small fw-semibold"><?= date('d/m/y H:i', strtotime($q['waktu_mulai'])) ?></div>
                                            <small class="text-muted">s/d <?= date('d/m/y H:i', strtotime($q['waktu_selesai'])) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted small">Fleksibel</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-6"><?= (int)($q['total_peserta'] ?? 0) ?> Siswa</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $badgeSt ?> px-3 py-2 rounded-pill">
                                            <?= ucfirst($status) ?>
                                        </span>
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
