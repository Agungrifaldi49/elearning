<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$user = AuthHelper::user();
$roleId = (int)($user['role_id'] ?? ($_SESSION['role_id'] ?? 0));
$roleName = strtolower(trim($user['role_name'] ?? ($_SESSION['role_name'] ?? '')));

$isTeacher = ($roleId === 2 || strpos($roleName, 'guru') !== false || strpos($roleName, 'pengajar') !== false);
$isAdmin = ($roleId === 1 || strpos($roleName, 'admin') !== false);
$isStudent = ($roleId === 3 || strpos($roleName, 'siswa') !== false);

$totalGamesCount = count($games);
$totalPemainAll = 0;
foreach ($games as $gm) {
    $totalPemainAll += (int)($gm['total_pemain'] ?? 0);
}
?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Modern Premium Hero Banner -->
        <div class="card border-0 rounded-4 shadow-lg overflow-hidden mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%); color: white;">
            <div class="card-body p-4 p-md-5 position-relative">
                <div class="row align-items-center g-4 position-relative z-1">
                    <div class="col-lg-8 col-12">
                        <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 px-3 py-1 rounded-pill mb-3 border border-white border-opacity-20 backdrop-blur">
                            <span class="badge bg-danger rounded-pill">PRO</span>
                            <span class="small fw-semibold text-warning">Arena Kuis & Game Edukasi Interaktif</span>
                        </div>
                        <h2 class="fw-bold display-6 mb-2 text-white">
                            Pacu Semangat Belajar Melalui Game Kuis Interaktif 🚀
                        </h2>
                        <p class="text-white-50 leading-relaxed mb-4 max-w-xl">
                            Rancang, uji coba, dan jawab kuis seru berbasis waktu, nyawa, dan pengganda skor combo streak untuk meraih posisi tertinggi di Papan Peringkat Sekolah.
                        </p>

                        <!-- Stats Badge Row -->
                        <div class="d-flex flex-wrap gap-3">
                            <div class="bg-black bg-opacity-30 px-3 py-2 rounded-4 border border-white border-opacity-10">
                                <small class="text-white-50 d-block">Total Arena Game</small>
                                <span class="fw-bold fs-5 text-warning"><?= number_format($totalGamesCount) ?> Game</span>
                            </div>
                            <div class="bg-black bg-opacity-30 px-3 py-2 rounded-4 border border-white border-opacity-10">
                                <small class="text-white-50 d-block">Total Pemain Aktif</small>
                                <span class="fw-bold fs-5 text-info"><?= number_format($totalPemainAll) ?> Percobaan</span>
                            </div>
                            <div class="bg-black bg-opacity-30 px-3 py-2 rounded-4 border border-white border-opacity-10">
                                <small class="text-white-50 d-block">Hak Akses</small>
                                <span class="fw-bold fs-5 text-success">
                                    <?= $isTeacher ? 'Guru (Pembuat)' : ($isAdmin ? 'Admin (Read-Only)' : 'Siswa (Peserta)') ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-12 text-lg-end text-center">
                        <?php if ($isTeacher): ?>
                            <a href="<?= BASE_URL ?>index.php?url=game/create" class="btn btn-warning btn-lg rounded-pill px-4 py-3 fw-bold shadow-lg text-dark hover-scale">
                                <i class="bi bi-plus-circle-fill me-2 fs-5"></i> Buat Game Edukasi Baru
                            </a>
                        <?php elseif ($isAdmin): ?>
                            <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-20 d-inline-block text-start">
                                <small class="text-white-50 d-block mb-1"><i class="bi bi-shield-lock-fill text-warning me-1"></i> Mode Administrator</small>
                                <span class="fw-bold small text-white">Monitoring Read-Only & Papan Peringkat</span>
                            </div>
                        <?php elseif ($isStudent): ?>
                            <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-20 d-inline-block text-start">
                                <small class="text-white-50 d-block mb-1"><i class="bi bi-controller text-danger me-1"></i> Mode Siswa</small>
                                <span class="fw-bold small text-white">Pilih Game & Kumpulkan Poin Tertinggi!</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="card card-custom p-3 mb-4 shadow-sm border-0 rounded-4">
            <div class="row g-2 align-items-center">
                <div class="col-md-8 col-12">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="gameSearchInput" class="form-control bg-light border-0 rounded-end" placeholder="Cari judul game edukasi atau mata pelajaran...">
                    </div>
                </div>
                <div class="col-md-4 col-12 text-md-end text-muted small">
                    Menampilkan <strong id="visibleGameCount"><?= count($games) ?></strong> game edukasi
                </div>
            </div>
        </div>

        <!-- Empty State -->
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
                            <i class="bi bi-plus-circle me-1"></i> Buat Game Sekarang
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Game Cards Grid -->
            <div class="row g-4" id="gameCardsGrid">
                <?php foreach ($games as $g): ?>
                    <div class="col-12 col-md-6 col-lg-4 game-card-item" data-title="<?= strtolower(htmlspecialchars($g['judul'] . ' ' . $g['nama_mapel'])) ?>">
                        <div class="card card-custom h-100 shadow-sm border-0 rounded-4 position-relative overflow-hidden hover-top">
                            
                            <!-- Top Decor Banner -->
                            <div class="bg-gradient bg-primary text-white p-3 d-flex justify-content-between align-items-center">
                                <span class="badge bg-white text-primary fw-bold px-3 py-1 rounded-pill text-truncate max-w-xs">
                                    <i class="bi bi-journal-bookmark me-1"></i> <?= htmlspecialchars($g['nama_mapel']) ?>
                                </span>
                                <span class="small fw-semibold text-white-50">
                                    <i class="bi bi-stopwatch text-warning me-1"></i> <?= $g['durasi_per_soal'] ?>s / Soal
                                </span>
                            </div>

                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                        <h5 class="fw-bold text-dark mb-1 text-lh-sm"><?= htmlspecialchars($g['judul']) ?></h5>
                                        <?php if (!empty($g['nama_kelas'])): ?>
                                            <span class="badge bg-info-subtle text-dark border border-info-subtle rounded-pill small text-nowrap">
                                                <?= htmlspecialchars($g['nama_kelas']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small text-nowrap">
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
                                        <div class="p-2 bg-light rounded-3 mb-3 d-flex justify-content-between align-items-center border">
                                            <small class="text-muted">Skor Terbaik Anda:</small>
                                            <span class="fw-bold fs-6 <?= ($g['my_status'] === 'lulus') ? 'text-success' : 'text-danger' ?>">
                                                <?= $g['my_best_score'] ?> Poin 
                                                <?= ($g['my_status'] === 'lulus') ? '🎉 (Lulus)' : '❌ (Ulangi)' ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Action Buttons Row -->
                                <div class="pt-3 border-top d-flex align-items-center justify-content-between gap-1 flex-wrap">
                                    <a href="<?= BASE_URL ?>index.php?url=game/leaderboard&id=<?= $g['id'] ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-semibold" title="Papan Peringkat Top 15">
                                        <i class="bi bi-trophy-fill text-warning me-1"></i> Peringkat
                                    </a>

                                    <?php if ($isTeacher): ?>
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="<?= BASE_URL ?>index.php?url=game/edit&id=<?= $g['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2 fw-semibold" title="Edit Game Edukasi">
                                                <i class="bi bi-pencil-square me-1"></i> Edit
                                            </a>
                                            <form action="<?= BASE_URL ?>index.php?url=game/delete" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Game Edukasi ini?');" class="d-inline">
                                                <?= Security::csrfField() ?>
                                                <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1 d-inline-flex align-items-center justify-content-center" title="Hapus Game" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                            <a href="<?= BASE_URL ?>index.php?url=game/play&id=<?= $g['id'] ?>" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-sm text-nowrap">
                                                <i class="bi bi-eye me-1"></i> Pratinjau
                                            </a>
                                        </div>
                                    <?php elseif ($isStudent): ?>
                                        <a href="<?= BASE_URL ?>index.php?url=game/play&id=<?= $g['id'] ?>" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold shadow-sm text-nowrap">
                                            <i class="bi bi-controller me-1"></i> Mainkan Game
                                        </a>
                                    <?php elseif ($isAdmin): ?>
                                        <span class="badge bg-light text-muted border px-3 py-2 rounded-pill small">
                                            <i class="bi bi-eye me-1"></i> Monitoring Read-Only
                                        </span>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('gameSearchInput');
    const items = document.querySelectorAll('.game-card-item');
    const countDisplay = document.getElementById('visibleGameCount');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            let visibleCount = 0;

            items.forEach(item => {
                const title = item.getAttribute('data-title') || '';
                if (query === '' || title.includes(query)) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (countDisplay) countDisplay.textContent = visibleCount;
        });
    }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
