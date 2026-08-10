<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <a href="<?= BASE_URL ?>index.php?url=game" class="btn btn-outline-secondary mb-3 rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Arena Game
        </a>

        <!-- Leaderboard Header Card -->
        <div class="card card-custom p-4 p-md-5 mb-4 shadow-sm border-0 rounded-4 text-center bg-gradient" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: white;">
            <div class="bg-warning bg-opacity-20 text-warning rounded-circle d-inline-flex p-3 mb-2">
                <i class="bi bi-trophy-fill display-4"></i>
            </div>
            <h3 class="fw-bold mb-1 text-white">Papan Peringkat Top 15</h3>
            <h5 class="fw-semibold text-warning mb-2"><?= htmlspecialchars($game['judul']) ?></h5>
            <p class="text-white-50 small mb-0"><?= htmlspecialchars($game['nama_mapel']) ?> | KKM Kelulusan: <?= $game['kkm'] ?> Poin</p>
        </div>

        <?php if (empty($leaderboard)): ?>
            <div class="card card-custom p-5 text-center shadow-sm border-0 rounded-4">
                <i class="bi bi-award display-4 text-muted opacity-50 mb-2"></i>
                <h5 class="fw-bold text-dark mb-1">Belum Ada Skor Tercatat</h5>
                <p class="text-muted small mb-3">Jadilah pemain pertama yang menaklukkan arena game edukasi ini!</p>
                <a href="<?= BASE_URL ?>index.php?url=game/play&id=<?= $game['id'] ?>" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm d-inline-block max-w-xs mx-auto">
                    <i class="bi bi-controller me-1"></i> Mainkan Sekarang
                </a>
            </div>
        <?php else: ?>
            <!-- Podium Top 3 (If available) -->
            <?php if (count($leaderboard) >= 1): ?>
                <div class="row g-3 justify-content-center mb-4 align-items-end">
                    <!-- Rank 2 (Silver) -->
                    <?php if (isset($leaderboard[1])): $p2 = $leaderboard[1]; ?>
                        <div class="col-4 col-md-3 text-center">
                            <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-light">
                                <div class="fs-1 mb-1">🥈</div>
                                <h6 class="fw-bold mb-0 text-truncate"><?= htmlspecialchars($p2['nama_siswa']) ?></h6>
                                <small class="text-muted d-block mb-2"><?= htmlspecialchars($p2['nama_kelas'] ?? 'Siswa') ?></small>
                                <span class="badge bg-secondary rounded-pill px-3 py-1 fs-6"><?= $p2['skor_akhir'] ?> Poin</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Rank 1 (Gold) -->
                    <?php $p1 = $leaderboard[0]; ?>
                    <div class="col-4 col-md-3 text-center">
                        <div class="card card-custom p-4 shadow-lg border-2 border-warning rounded-4 bg-warning bg-opacity-10">
                            <div class="fs-1 mb-1">🥇</div>
                            <h5 class="fw-bold mb-0 text-dark text-truncate"><?= htmlspecialchars($p1['nama_siswa']) ?></h5>
                            <small class="text-muted d-block mb-2"><?= htmlspecialchars($p1['nama_kelas'] ?? 'Siswa') ?></small>
                            <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-2 fs-5 shadow-sm"><?= $p1['skor_akhir'] ?> Poin</span>
                        </div>
                    </div>

                    <!-- Rank 3 (Bronze) -->
                    <?php if (isset($leaderboard[2])): $p3 = $leaderboard[2]; ?>
                        <div class="col-4 col-md-3 text-center">
                            <div class="card card-custom p-3 shadow-sm border-0 rounded-4 bg-light">
                                <div class="fs-1 mb-1">🥉</div>
                                <h6 class="fw-bold mb-0 text-truncate"><?= htmlspecialchars($p3['nama_siswa']) ?></h6>
                                <small class="text-muted d-block mb-2"><?= htmlspecialchars($p3['nama_kelas'] ?? 'Siswa') ?></small>
                                <span class="badge bg-danger bg-opacity-75 text-white rounded-pill px-3 py-1 fs-6"><?= $p3['skor_akhir'] ?> Poin</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Detailed Leaderboard Table -->
            <div class="card card-custom p-4 shadow-sm border-0 rounded-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Peringkat</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th class="text-center">Benar / Total</th>
                                <th class="text-center">Max Combo</th>
                                <th class="text-center">Waktu</th>
                                <th class="text-end">Skor Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaderboard as $idx => $lb): ?>
                                <tr>
                                    <td class="fw-bold fs-6">
                                        <?php if ($idx === 0): ?>🥇 <span class="text-warning">#1</span>
                                        <?php elseif ($idx === 1): ?>🥈 <span class="text-secondary">#2</span>
                                        <?php elseif ($idx === 2): ?>🥉 <span class="text-danger">#3</span>
                                        <?php else: ?>#<?= $idx + 1 ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($lb['nama_siswa']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($lb['nama_kelas'] ?? '-') ?></span></td>
                                    <td class="text-center"><?= $lb['total_benar'] ?> / <?= $lb['total_soal'] ?></td>
                                    <td class="text-center"><span class="badge bg-warning bg-opacity-20 text-warning-emphasis border border-warning rounded-pill"><?= $lb['max_combo'] ?>x 🔥</span></td>
                                    <td class="text-center small text-muted"><?= $lb['waktu_selesai'] ?> Detik</td>
                                    <td class="text-end fw-bold fs-5 text-primary"><?= number_format($lb['skor_akhir']) ?> Poin</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
