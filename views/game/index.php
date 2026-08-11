<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$roleName = strtolower(trim($_SESSION['user']['role_name'] ?? ''));
$isTeacher = ($roleName === 'guru');
$isAdmin = ($roleName === 'administrator' || $roleName === 'admin');
$isStudent = ($roleName === 'siswa');
?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bi bi-controller text-danger me-2"></i>Game Edukasi Interaktif
                </h4>
                <p class="text-muted small mb-0">
                    Arena pembelajaran seru berhadiah poin, combo streak, dan papan peringkat skor terbaik!
                </p>
            </div>
            <div>
                <?php if ($isTeacher): ?>
                    <a href="<?= BASE_URL ?>index.php?url=game/create" class="btn btn-primary shadow-sm rounded-pill px-4 fw-bold">
                        <i class="bi bi-plus-circle me-1"></i> Buat Game Edukasi Baru
                    </a>
                <?php elseif ($isAdmin): ?>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 rounded-pill small">
                        <i class="bi bi-shield-lock-fill me-1 text-primary"></i> Mode Administrator (Read-Only / Memantau)
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($games)): ?>
            <div class="card card-custom p-5 text-center shadow-sm border-0 rounded-4">
                <div class="my-3">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex p-4 mb-3">
                        <i class="bi bi-controller display-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Belum Ada Game Edukasi Tersedia</h5>
                    <p class="text-muted small max-w-md mx-auto mb-4">
                        <?= ($isTeacher) ? 'Ayo buat game edukasi interaktif pertama Anda untuk meningkatkan antusiasme belajar siswa!' : (($isAdmin) ? 'Belum ada game edukasi yang dipublikasikan oleh Bapak/Ibu Guru.' : 'Belum ada game edukasi yang dipublikasikan oleh Bapak/Ibu Guru untuk kelas Anda.') ?>
                    </p>
                    <?php if ($isTeacher): ?>
                        <a href="<?= BASE_URL ?>index.php?url=game/create" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                            <i class="bi bi-controller me-1"></i> Buat Game Sekarang
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($games as $g): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card card-custom h-100 shadow-sm border-0 rounded-4 position-relative overflow-hidden hover-top">
                            <!-- Top Decor Banner -->
                            <div class="bg-gradient bg-primary text-white p-3 d-flex justify-content-between align-items-center">
                                <span class="badge bg-white text-primary fw-bold px-3 py-1 rounded-pill">
                                    <i class="bi bi-journal-bookmark me-1"></i> <?= htmlspecialchars($g['nama_mapel']) ?>
                                </span>
                                <span class="small fw-semibold">
                                    <i class="bi bi-stopwatch me-1"></i> <?= $g['durasi_per_soal'] ?>s / Soal
                                </span>
                            </div>

                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($g['judul']) ?></h5>
                                        <?php if (!empty($g['nama_kelas'])): ?>
                                            <span class="badge bg-info-subtle text-dark border border-info-subtle rounded-pill small">
                                                <?= htmlspecialchars($g['nama_kelas']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                                Semua Kelas
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <p class="text-muted small mb-3 text-truncate-2" style="min-height: 40px;">
                                        <?= htmlspecialchars($g['deskripsi'] ?: 'Uji kemampuan dan pacu kecepatan Anda dalam arena game edukasi ini!') ?>
                                    </p>

                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <span class="badge bg-light text-secondary border rounded-pill">
                                            <i class="bi bi-question-circle me-1"></i> <?= $g['total_soal'] ?> Pertanyaan
                                        </span>
                                        <span class="badge bg-light text-secondary border rounded-pill">
                                            <i class="bi bi-award me-1"></i> KKM <?= $g['kkm'] ?> Poin
                                        </span>
                                        <span class="badge bg-light text-secondary border rounded-pill">
                                            <i class="bi bi-people me-1"></i> <?= $g['total_pemain'] ?> Pemain
                                        </span>
                                    </div>

                                    <?php if ($isStudent && isset($g['my_best_score'])): ?>
                                        <div class="p-2 bg-light rounded-3 mb-3 d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Skor Terbaik Anda:</small>
                                            <span class="fw-bold fs-6 <?= ($g['my_status'] === 'lulus') ? 'text-success' : 'text-danger' ?>">
                                                <?= $g['my_best_score'] ?> Poin 
                                                <?= ($g['my_status'] === 'lulus') ? '🎉 (Lulus)' : '❌ (Ulangi)' ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="pt-3 border-top d-flex align-items-center justify-content-between gap-2">
                                    <a href="<?= BASE_URL ?>index.php?url=game/leaderboard&id=<?= $g['id'] ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-semibold w-100 text-center" title="Papan Peringkat Top 15">
                                        <i class="bi bi-trophy-fill text-warning me-1"></i> Papan Peringkat
                                    </a>

                                    <?php if (!$isAdmin): ?>
                                        <div class="d-flex gap-1">
                                            <?php if ($isTeacher): ?>
                                                <form action="<?= BASE_URL ?>index.php?url=game/delete" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Game Edukasi ini?');">
                                                    <?= Security::csrfField() ?>
                                                    <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-2" title="Hapus Game">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </form>
                                                <a href="<?= BASE_URL ?>index.php?url=game/play&id=<?= $g['id'] ?>" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-sm text-nowrap">
                                                    <i class="bi bi-eye me-1"></i> Pratinjau Arena
                                                </a>
                                            <?php elseif ($isStudent): ?>
                                                <a href="<?= BASE_URL ?>index.php?url=game/play&id=<?= $g['id'] ?>" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold shadow-sm text-nowrap">
                                                    <i class="bi bi-controller me-1"></i> Mainkan Game
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
