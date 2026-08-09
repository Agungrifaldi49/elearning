<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-database text-primary me-2"></i>Bank Soal & Analisis Butir Soal</h4>
            <p class="text-muted small mb-0">Repositori soal terpusat beserta analisis tingkat kesulitan dan persentase jawaban benar.</p>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center">
                <div class="fw-bold text-primary fs-3"><?= count($quizList) ?></div>
                <small class="text-muted">Total Paket Quiz</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center">
                <div class="fw-bold text-success fs-3">
                    <?php
                        $db = Database::getConnection();
                        echo $db->query("SELECT COUNT(*) FROM soal")->fetchColumn();
                    ?>
                </div>
                <small class="text-muted">Total Butir Soal</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center">
                <div class="fw-bold text-warning fs-3">
                    <?= $db->query("SELECT COUNT(*) FROM soal WHERE jenis_soal='pg'")->fetchColumn() ?>
                </div>
                <small class="text-muted">Soal Pilihan Ganda</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-custom p-3 text-center">
                <div class="fw-bold text-info fs-3">
                    <?= $db->query("SELECT COUNT(*) FROM soal WHERE jenis_soal='essay'")->fetchColumn() ?>
                </div>
                <small class="text-muted">Soal Essay</small>
            </div>
        </div>
    </div>

    <!-- Quiz List with Soal Detail -->
    <?php foreach ($quizList as $q):
        $stmtSoal = $db->prepare("SELECT s.*, COUNT(js.id) as total_jawaban, SUM(js.is_benar) as total_benar FROM soal s LEFT JOIN jawaban_siswa js ON s.id = js.soal_id WHERE s.quiz_id = ? GROUP BY s.id ORDER BY s.id ASC");
        $stmtSoal->execute([$q['id']]);
        $soalList = $stmtSoal->fetchAll();
    ?>
        <div class="card-custom p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($q['judul']) ?></h6>
                    <small class="text-muted">Mapel: <?= htmlspecialchars($q['nama_mapel']) ?> | Kelas: <?= htmlspecialchars($q['nama_kelas']) ?> | Durasi: <?= $q['durasi_menit'] ?> menit</small>
                </div>
                <span class="badge badge-pill-lg bg-primary"><?= count($soalList) ?> Butir Soal</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Pertanyaan</th>
                            <th>Jenis</th>
                            <th>Bobot</th>
                            <th>Dijawab</th>
                            <th>% Benar</th>
                            <th>Tingkat Kesulitan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($soalList)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">Belum ada soal di paket ini.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($soalList as $i => $s):
                            $pct = $s['total_jawaban'] > 0 ? round(($s['total_benar'] / $s['total_jawaban']) * 100) : 0;
                            $difficulty = $pct >= 70 ? ['Mudah','success'] : ($pct >= 40 ? ['Sedang','warning'] : ['Sulit','danger']);
                        ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="fw-medium"><?= htmlspecialchars(substr($s['pertanyaan'], 0, 80)) ?>...</td>
                                <td>
                                    <span class="badge bg-<?= $s['jenis_soal'] === 'pg' ? 'primary' : ($s['jenis_soal'] === 'essay' ? 'info' : 'secondary') ?>">
                                        <?= strtoupper($s['jenis_soal']) ?>
                                    </span>
                                </td>
                                <td><?= $s['bobot'] ?> poin</td>
                                <td><?= $s['total_jawaban'] ?? 0 ?> siswa</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress progress-custom flex-grow-1" style="min-width:60px;">
                                            <div class="progress-bar bg-<?= $pct >= 70 ? 'success' : ($pct >= 40 ? 'warning' : 'danger') ?>"
                                                 style="width:<?= $pct ?>%"></div>
                                        </div>
                                        <span class="fw-bold" style="min-width:30px;"><?= $pct ?>%</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-<?= $difficulty[1] ?>"><?= $difficulty[0] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
