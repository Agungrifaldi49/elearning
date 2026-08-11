<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
// Helper Function to resolve student profile photo URL or gradient fallback badge
function renderStudentAvatar($studentName, $avatarFile, $size = 64, $extraBorderClass = '') {
    $cleanName = htmlspecialchars($studentName ?: 'Siswa');
    $initial = strtoupper(substr(trim($cleanName), 0, 1));
    if (empty($initial)) $initial = 'S';

    $avatarUrl = null;
    if (!empty($avatarFile) && $avatarFile !== 'default_avatar.png') {
        $possiblePaths = [
            'assets/uploads/profile/' . $avatarFile,
            'assets/uploads/' . $avatarFile,
            'assets/uploads/avatar/' . $avatarFile,
            'assets/uploads/avatars/' . $avatarFile
        ];
        foreach ($possiblePaths as $relPath) {
            if (file_exists(ROOT_PATH . $relPath)) {
                $avatarUrl = BASE_URL . $relPath;
                break;
            }
        }
    }

    if ($avatarUrl) {
        return '<img src="' . $avatarUrl . '" class="rounded-circle object-fit-cover shadow-sm ' . $extraBorderClass . '" style="width: ' . $size . 'px; height: ' . $size . 'px;" alt="' . $cleanName . '" onError="this.onerror=null; this.src=\'' . BASE_URL . 'assets/images/default_avatar.png\';">';
    } else {
        $fontSize = max(14, round($size * 0.4));
        return '<div class="rounded-circle bg-primary bg-gradient text-white d-inline-flex align-items-center justify-content-center fw-bold shadow-sm ' . $extraBorderClass . '" style="width: ' . $size . 'px; height: ' . $size . 'px; font-size: ' . $fontSize . 'px;">' . $initial . '</div>';
    }
}
?>

<main class="main-content px-3 px-md-4 py-3">
    <div class="container-fluid">

        <!-- Top Navigation Action Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <a href="<?= BASE_URL ?>index.php?url=game" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Arena Game
            </a>
            <a href="<?= BASE_URL ?>index.php?url=game/play&id=<?= $game['id'] ?>" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow-sm hover-scale">
                <i class="bi bi-controller me-1"></i> Mainkan Game Ini 🚀
            </a>
        </div>

        <!-- Leaderboard Hero Banner (Clean Dark Gradient Backdrop) -->
        <div class="card border-0 rounded-4 shadow-lg overflow-hidden mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%); color: white;">
            <div class="card-body p-4 p-md-5 text-center position-relative">
                <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-20 text-warning rounded-circle p-3 mb-3 border border-warning border-opacity-30 shadow" style="width: 72px; height: 72px;">
                    <i class="bi bi-trophy-fill fs-1"></i>
                </div>
                <h3 class="fw-bold mb-1 text-white display-6">Papan Peringkat Top 15</h3>
                <h5 class="fw-bold text-warning mb-2"><?= htmlspecialchars($game['judul']) ?></h5>
                <p class="text-white-50 small mb-0 max-w-lg mx-auto">
                    Mata Pelajaran: <strong><?= htmlspecialchars($game['nama_mapel']) ?></strong> | Target KKM Kelulusan: <strong class="text-warning"><?= $game['kkm'] ?> Poin</strong>
                </p>
            </div>
        </div>

        <?php if (empty($leaderboard)): ?>
            <div class="card p-5 text-center shadow-sm border-0 rounded-4 my-4">
                <div class="my-3">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex p-4 mb-3">
                        <i class="bi bi-award display-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Belum Ada Skor Tercatat</h5>
                    <p class="text-muted small max-w-md mx-auto mb-4">
                        Jadilah siswa pertama yang menaklukkan arena game edukasi ini dan raih posisi tertinggi di Papan Peringkat!
                    </p>
                    <a href="<?= BASE_URL ?>index.php?url=game/play&id=<?= $game['id'] ?>" class="btn btn-danger rounded-pill px-4 py-2 fw-bold shadow-sm">
                        <i class="bi bi-controller me-1"></i> Mainkan Sekarang
                    </a>
                </div>
            </div>
        <?php else: ?>

            <!-- Podium Top 3 Section -->
            <div class="row g-3 justify-content-center mb-4 align-items-end">
                
                <!-- Rank 2 (Silver) -->
                <?php if (isset($leaderboard[1])): $p2 = $leaderboard[1]; ?>
                    <div class="col-12 col-sm-4 col-md-3 text-center order-2 order-sm-1">
                        <div class="card p-3 p-md-4 shadow-sm border-0 rounded-4 bg-white hover-top h-100">
                            <div class="position-relative d-inline-block mb-2">
                                <?= renderStudentAvatar($p2['nama_siswa'], $p2['avatar'] ?? '', 72, 'border border-2 border-secondary') ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-secondary fs-6 p-2 shadow">🥈</span>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill small mb-2 d-inline-block">Peringkat #2</span>
                            <h6 class="fw-bold mb-1 text-dark text-truncate" title="<?= htmlspecialchars($p2['nama_siswa']) ?>"><?= htmlspecialchars($p2['nama_siswa']) ?></h6>
                            <small class="text-muted d-block mb-3"><?= htmlspecialchars($p2['nama_kelas'] ?? 'Siswa') ?></small>
                            <span class="badge bg-secondary rounded-pill px-3 py-2 fs-6 shadow-sm"><?= number_format($p2['skor_akhir']) ?> Poin</span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Rank 1 (Gold - Center & Highlighted) -->
                <?php $p1 = $leaderboard[0]; ?>
                <div class="col-12 col-sm-4 col-md-4 text-center order-1 order-sm-2">
                    <div class="card p-4 shadow-lg border-2 border-warning rounded-4 bg-white hover-top position-relative overflow-hidden">
                        <div class="bg-warning bg-opacity-20 py-1 text-dark fw-bold small text-center mb-3 rounded-pill border border-warning border-opacity-50">
                            👑 JUARA 1 ARENA GAME 👑
                        </div>
                        <div class="position-relative d-inline-block mb-2">
                            <?= renderStudentAvatar($p1['nama_siswa'], $p1['avatar'] ?? '', 88, 'border border-3 border-warning shadow') ?>
                            <span class="position-absolute top-0 start-100 translate-middle fs-3">🥇</span>
                        </div>
                        <h5 class="fw-bold mb-1 text-dark text-truncate" title="<?= htmlspecialchars($p1['nama_siswa']) ?>"><?= htmlspecialchars($p1['nama_siswa']) ?></h5>
                        <span class="badge bg-info-subtle text-dark border border-info-subtle rounded-pill mb-3"><?= htmlspecialchars($p1['nama_kelas'] ?? 'Siswa') ?></span>
                        <div>
                            <span class="badge bg-warning text-dark fw-bold rounded-pill px-4 py-2 fs-5 shadow"><?= number_format($p1['skor_akhir']) ?> Poin</span>
                        </div>
                    </div>
                </div>

                <!-- Rank 3 (Bronze) -->
                <?php if (isset($leaderboard[2])): $p3 = $leaderboard[2]; ?>
                    <div class="col-12 col-sm-4 col-md-3 text-center order-3 order-sm-3">
                        <div class="card p-3 p-md-4 shadow-sm border-0 rounded-4 bg-white hover-top h-100">
                            <div class="position-relative d-inline-block mb-2">
                                <?= renderStudentAvatar($p3['nama_siswa'], $p3['avatar'] ?? '', 72, 'border border-2 border-danger') ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger fs-6 p-2 shadow">🥉</span>
                            </div>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small mb-2 d-inline-block">Peringkat #3</span>
                            <h6 class="fw-bold mb-1 text-dark text-truncate" title="<?= htmlspecialchars($p3['nama_siswa']) ?>"><?= htmlspecialchars($p3['nama_siswa']) ?></h6>
                            <small class="text-muted d-block mb-3"><?= htmlspecialchars($p3['nama_kelas'] ?? 'Siswa') ?></small>
                            <span class="badge bg-danger bg-opacity-75 text-white rounded-pill px-3 py-2 fs-6 shadow-sm"><?= number_format($p3['skor_akhir']) ?> Poin</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Detailed Leaderboard Table Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
                <div class="card-header bg-white p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-list-ol text-primary me-2"></i>Daftar Peringkat Lengkap</h5>
                    <span class="badge bg-light text-secondary border rounded-pill">Total <?= count($leaderboard) ?> Siswa</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Peringkat</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th class="text-center">Benar / Total</th>
                                <th class="text-center">Max Combo</th>
                                <th class="text-center">Waktu Pengerjaan</th>
                                <th class="text-end pe-4">Skor Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaderboard as $idx => $lb): ?>
                                <tr>
                                    <td class="ps-4 fw-bold fs-6">
                                        <?php if ($idx === 0): ?>
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1 shadow-sm">🥇 #1</span>
                                        <?php elseif ($idx === 1): ?>
                                            <span class="badge bg-secondary rounded-pill px-3 py-1 shadow-sm">🥈 #2</span>
                                        <?php elseif ($idx === 2): ?>
                                            <span class="badge bg-danger bg-opacity-75 text-white rounded-pill px-3 py-1 shadow-sm">🥉 #3</span>
                                        <?php else: ?>
                                            <span class="text-muted ms-2">#<?= $idx + 1 ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <?= renderStudentAvatar($lb['nama_siswa'], $lb['avatar'] ?? '', 42) ?>
                                            <div>
                                                <span class="fw-bold text-dark d-block"><?= htmlspecialchars($lb['nama_siswa']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                                            <?= htmlspecialchars($lb['nama_kelas'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="text-center fw-semibold text-dark">
                                        <?= $lb['total_benar'] ?> / <?= $lb['total_soal'] ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning bg-opacity-20 text-warning-emphasis border border-warning rounded-pill px-3 py-1">
                                            <?= $lb['max_combo'] ?>x 🔥
                                        </span>
                                    </td>
                                    <td class="text-center small text-muted">
                                        <i class="bi bi-clock me-1"></i><?= $lb['waktu_selesai'] ?> Detik
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="fw-bold fs-5 text-primary"><?= number_format($lb['skor_akhir']) ?> Poin</span>
                                    </td>
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
