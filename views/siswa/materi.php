<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
// Helper to extract YouTube Embed URL
if (!function_exists('getYouTubeEmbedUrl')) {
    function getYouTubeEmbedUrl($url) {
        if (empty($url)) return '';
        if (strpos($url, 'embed/') !== false) return $url;
        
        $videoId = '';
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $match)) {
            $videoId = $match[1];
        }
        return !empty($videoId) ? "https://www.youtube.com/embed/" . $videoId : $url;
    }
}

$mapelList = $mapelList ?? [];
$selectedMapelId = (int)($selectedMapelId ?? ($_GET['mapel_id'] ?? 0));
$searchKeyword = trim($searchKeyword ?? ($_GET['q'] ?? ''));

$selectedMapelName = 'Semua Mata Pelajaran';
foreach ($mapelList as $mp) {
    if ((int)$mp['id'] === $selectedMapelId) {
        $selectedMapelName = $mp['nama_mapel'];
        break;
    }
}
?>

<style>
/* Modern Siswa Materi & Video Learning Styling */
.materi-siswa-page-wrapper {
}

/* Glassmorphic Hero Banner */
.materi-siswa-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #2563eb 100%);
    border-radius: 20px;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
    position: relative;
    overflow: hidden;
}

.materi-siswa-hero::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}

/* Cards Architecture */
.materi-card-item {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    transition: all 0.25s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.materi-card-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
}

.badge-mapel-tag {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-weight: 700;
    font-size: 0.76rem;
    padding: 5px 12px;
    border-radius: 50rem;
}

/* Tab Navigation Styling */
.materi-nav-tabs .nav-link {
    border: none;
    color: #64748b;
    font-weight: 600;
    padding: 12px 22px;
    border-radius: 12px;
    transition: all 0.2s ease;
    font-size: 0.92rem;
}
.materi-nav-tabs .nav-link:hover {
    color: #2563eb;
    background-color: rgba(37, 99, 235, 0.06);
}
.materi-nav-tabs .nav-link.active {
    color: #ffffff !important;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
}

.video-responsive-ratio {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 */
    height: 0;
    overflow: hidden;
    border-radius: 12px;
}
.video-responsive-ratio iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

/* 🔍 Modern Filter & Search Bar Styling */
.filter-search-panel {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    padding: 16px 20px;
    margin-bottom: 22px;
}
[data-bs-theme="dark"] .filter-search-panel {
    background: #1e293b;
    border-color: #334155;
}

.search-box-wrapper {
    position: relative;
}
.search-box-wrapper .search-icon {
    position: absolute;
    top: 50%;
    left: 16px;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
    font-size: 1rem;
}
.search-box-wrapper .form-control {
    padding-left: 44px;
    padding-right: 42px;
    height: 46px;
    border-radius: 50rem;
    font-size: 0.9rem;
    border: 1.5px solid #cbd5e1;
    transition: all 0.2s ease;
}
.search-box-wrapper .form-control:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
.search-box-wrapper .btn-clear-q {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 2px 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.search-box-wrapper .btn-clear-q:hover {
    color: #334155;
}

.mapel-select-wrapper {
    position: relative;
}
.mapel-select-wrapper .select-icon {
    position: absolute;
    top: 50%;
    left: 16px;
    transform: translateY(-50%);
    color: #3b82f6;
    pointer-events: none;
    font-size: 1rem;
}
.mapel-select-wrapper .form-select {
    padding-left: 44px;
    height: 46px;
    border-radius: 50rem;
    font-size: 0.9rem;
    font-weight: 600;
    border: 1.5px solid #cbd5e1;
    color: #1e293b;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mapel-select-wrapper .form-select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
[data-bs-theme="dark"] .mapel-select-wrapper .form-select {
    background-color: #0f172a;
    color: #f8fafc;
    border-color: #334155;
}
[data-bs-theme="dark"] .search-box-wrapper .form-control {
    background-color: #0f172a;
    color: #f8fafc;
    border-color: #334155;
}
</style>

<main class="main-content px-3 px-md-4 materi-siswa-page-wrapper">
    <div class="container-fluid">

        <!-- 🚀 HERO BANNER SISWA MATERI -->
        <div class="materi-siswa-hero p-4 p-md-5 mb-4">
            <div class="row align-items-center relative-zIndex-1">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="d-inline-flex align-items-center gap-2 px-3.5 py-2 rounded-pill bg-warning text-dark shadow-sm small fw-bold mb-3">
                        <i class="bi bi-book-half text-dark fs-6"></i>
                        <span>Materi & Video Streaming Learning Studio</span>
                    </div>
                    <h2 class="fw-bold mb-2 text-white" style="letter-spacing: -0.5px;">Materi & Video Pembelajaran</h2>
                    <p class="text-white text-opacity-85 small mb-0 lh-lg" style="max-width: 650px;">
                        Pelajari modul materi (PDF/Office/Gambar) dengan fitur pratinjau langsung, dan tonton video streaming YouTube dari Guru Pengampu secara interaktif tanpa keluar aplikasi.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" class="btn btn-warning text-dark px-4 py-2.5 rounded-pill fw-bold shadow-lg d-inline-flex align-items-center gap-2 hover-scale">
                        <i class="bi bi-key-fill fs-5"></i>
                        <span>Daftar Mapel Baru (Key)</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 🔍 FILTER MATA PELAJARAN & PENCARIAN MATERI -->
        <div class="filter-search-panel">
            <form method="GET" action="<?= BASE_URL ?>index.php" id="formFilterMateri" class="m-0">
                <input type="hidden" name="url" value="siswa/materi">

                <div class="row g-2.5 align-items-center">
                    <!-- 1. Kolom Pencarian Cepat -->
                    <div class="col-12 col-md-6 col-lg-7">
                        <div class="search-box-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" 
                                   name="q" 
                                   id="inputSearchMateri" 
                                   class="form-control" 
                                   placeholder="Cari judul materi, topik bab, atau nama guru..." 
                                   value="<?= htmlspecialchars($searchKeyword) ?>"
                                   autocomplete="off">
                            <button type="button" id="btnClearSearch" class="btn-clear-q <?= empty($searchKeyword) ? 'd-none' : '' ?>" title="Hapus Pencarian">
                                <i class="bi bi-x-circle-fill fs-5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- 2. Dropdown Filter Mata Pelajaran -->
                    <div class="col-12 col-sm-8 col-md-4 col-lg-3">
                        <div class="mapel-select-wrapper">
                            <i class="bi bi-journal-bookmark-fill select-icon"></i>
                            <select name="mapel_id" id="selectFilterMapel" class="form-select">
                                <option value="0">Semua Mata Pelajaran</option>
                                <?php foreach ($mapelList as $mp): ?>
                                    <option value="<?= $mp['id'] ?>" <?= ($selectedMapelId === (int)$mp['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mp['nama_mapel']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- 3. Tombol Reset Filter -->
                    <div class="col-12 col-sm-4 col-md-2 col-lg-2">
                        <a href="<?= BASE_URL ?>index.php?url=siswa/materi" class="btn btn-light border rounded-pill w-100 fw-bold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-xs text-muted hover-scale" id="btnResetFilter" style="height:46px;" title="Reset Filter & Pencarian">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </div>

                <!-- Status Filter & Realtime Counter Bar -->
                <div class="d-flex align-items-center gap-2 mt-3 pt-2.5 border-top flex-wrap" style="font-size:0.8rem;">
                    <span class="text-muted fw-semibold">
                        <i class="bi bi-funnel-fill text-primary me-1"></i>Filter Aktif:
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold" id="chipMapelStatus">
                        Mapel: <span id="chipMapelName"><?= htmlspecialchars($selectedMapelName) ?></span>
                    </span>
                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-3 py-1 fw-bold <?= empty($searchKeyword) ? 'd-none' : '' ?>" id="chipSearchStatus">
                        Kata Kunci: "<span id="chipSearchKeyword"><?= htmlspecialchars($searchKeyword) ?></span>"
                    </span>
                    <span class="ms-auto text-muted fw-semibold small" id="matchingSummaryText">
                        Menampilkan <b id="countSummaryModul"><?= count($materiList) ?></b> Modul &bull; <b id="countSummaryVideo"><?= count($videoList ?? []) ?></b> Video
                    </span>
                </div>
            </form>
        </div>

        <!-- 📚 DAFTAR MATA PELAJARAN YANG TERDAFTAR -->
        <div class="row g-4" id="mapelCardGrid">
            <?php
            // Kelompokkan materi per mapel untuk statistik
            $mapelStats = [];
            foreach ($materiList as $m) {
                $mid = (int)$m['mapel_id'];
                if (!isset($mapelStats[$mid])) {
                    $mapelStats[$mid] = [
                        'mapel_id'   => $mid,
                        'nama_mapel' => $m['nama_mapel'],
                        'nama_guru'  => $m['nama_guru'],
                        'jumlah_materi' => 0,
                    ];
                }
                $mapelStats[$mid]['jumlah_materi']++;
            }

            // Tambahkan mapel enrolled yang mungkin belum punya materi
            foreach ($enrolledList ?? [] as $em) {
                $mid = (int)$em['mapel_id'];
                if (!isset($mapelStats[$mid])) {
                    $mapelStats[$mid] = [
                        'mapel_id'      => $mid,
                        'nama_mapel'    => $em['nama_mapel'] ?? 'Mata Pelajaran',
                        'nama_guru'     => $em['nama_guru']  ?? '',
                        'jumlah_materi' => 0,
                    ];
                }
            }

            // Hitung quiz & tugas per mapel
            require_once ROOT_PATH . 'models/ExamModel.php';
            $examModelForStats = new ExamModel();
            $siswaKelasId = $siswa['kelas_id'] ?? null;
            $allQuizStats  = $examModelForStats->getQuizList($siswaKelasId);
            $allTugasStats = $learningModel->getTugas($siswaKelasId);

            $quizPerMapel  = [];
            foreach ($allQuizStats as $q) {
                $qmid = (int)$q['mapel_id'];
                if (isset($mapelStats[$qmid])) {
                    $quizPerMapel[$qmid][] = $q;
                }
            }
            $tugasPerMapel = [];
            foreach ($allTugasStats as $t) {
                $tmid = (int)$t['mapel_id'];
                if (isset($mapelStats[$tmid])) {
                    $tugasPerMapel[$tmid][] = $t;
                }
            }

            // Palet warna kartu (rotasi)
            $cardPalette = [
                ['from'=>'#2563eb','to'=>'#1d4ed8','light'=>'#eff6ff'],
                ['from'=>'#7c3aed','to'=>'#6d28d9','light'=>'#ede9fe'],
                ['from'=>'#0891b2','to'=>'#0e7490','light'=>'#ecfeff'],
                ['from'=>'#059669','to'=>'#047857','light'=>'#d1fae5'],
                ['from'=>'#d97706','to'=>'#b45309','light'=>'#fef3c7'],
                ['from'=>'#dc2626','to'=>'#b91c1c','light'=>'#fee2e2'],
                ['from'=>'#db2777','to'=>'#be185d','light'=>'#fce7f3'],
                ['from'=>'#65a30d','to'=>'#4d7c0f','light'=>'#ecfccb'],
            ];

            // Ikon mapel berdasarkan kata kunci nama (fallback bi-book)
            if (!function_exists('mapelIcon')) {
                function mapelIcon($nama) {
                    $n = mb_strtolower($nama);
                    if (str_contains($n,'mat'))  return 'bi-calculator-fill';
                    if (str_contains($n,'fis'))  return 'bi-lightning-charge-fill';
                    if (str_contains($n,'kim'))  return 'bi-eyedropper';
                    if (str_contains($n,'bio'))  return 'bi-flower1';
                    if (str_contains($n,'ing'))  return 'bi-translate';
                    if (str_contains($n,'indo') || str_contains($n,'bhs')) return 'bi-chat-quote-fill';
                    if (str_contains($n,'sej') || str_contains($n,'his')) return 'bi-bank2';
                    if (str_contains($n,'geo'))  return 'bi-globe-americas';
                    if (str_contains($n,'agam') || str_contains($n,'pai')) return 'bi-moon-stars-fill';
                    if (str_contains($n,'pkn') || str_contains($n,'ppkn')) return 'bi-flag-fill';
                    if (str_contains($n,'pjok') || str_contains($n,'olk')) return 'bi-bicycle';
                    if (str_contains($n,'seni')) return 'bi-palette-fill';
                    if (str_contains($n,'prog') || str_contains($n,'rpg') || str_contains($n,'web') || str_contains($n,'php')) return 'bi-code-slash';
                    if (str_contains($n,'data') || str_contains($n,'basis')) return 'bi-database-fill';
                    if (str_contains($n,'jar') || str_contains($n,'net')) return 'bi-hdd-network-fill';
                    if (str_contains($n,'prod') || str_contains($n,'kreat')) return 'bi-stars';
                    if (str_contains($n,'ekon') || str_contains($n,'eko')) return 'bi-graph-up-arrow';
                    return 'bi-book-half';
                }
            }

            if (empty($mapelStats)):
            ?>
                <div class="col-12">
                    <div class="text-center py-5 bg-white rounded-4 border shadow-sm">
                        <i class="bi bi-journal-x fs-1 text-muted d-block mb-3"></i>
                        <h5 class="fw-bold text-dark mb-2">Belum Ada Mata Pelajaran</h5>
                        <p class="text-muted small mb-3">Anda belum terdaftar di mata pelajaran manapun. Masukkan kode kunci untuk bergabung.</p>
                        <a href="<?= BASE_URL ?>index.php?url=siswa/gabungKelas" class="btn btn-warning text-dark rounded-pill px-4 fw-bold">
                            <i class="bi bi-key-fill me-1"></i> Daftar Mapel Baru
                        </a>
                    </div>
                </div>
            <?php else:
                $ci = 0;
                foreach ($mapelStats as $mid => $ms):
                    $pal = $cardPalette[$ci % count($cardPalette)];
                    $ci++;
                    $totalQ = count($quizPerMapel[$mid] ?? []);
                    $totalT = count($tugasPerMapel[$mid] ?? []);
                    $icon   = mapelIcon($ms['nama_mapel']);

                    // Hitung tugas aktif (deadline belum lewat)
                    $aktifTugas = 0;
                    foreach ($tugasPerMapel[$mid] ?? [] as $t) {
                        if (empty($t['deadline']) || strtotime($t['deadline']) > time()) $aktifTugas++;
                    }
                    // Quiz aktif
                    $aktifQuiz = 0;
                    foreach ($quizPerMapel[$mid] ?? [] as $q) {
                        if (empty($q['tanggal_selesai']) || strtotime($q['tanggal_selesai']) > time()) $aktifQuiz++;
                    }
            ?>
                <div class="col-12 col-sm-6 col-lg-4 mapel-card-col"
                     data-nama="<?= htmlspecialchars(mb_strtolower($ms['nama_mapel'] . ' ' . $ms['nama_guru'])) ?>">
                    <div class="mapel-subject-card h-100" style="--card-from:<?= $pal['from'] ?>;--card-to:<?= $pal['to'] ?>;--card-light:<?= $pal['light'] ?>;">

                        <!-- Card Top Gradient -->
                        <div class="mapel-card-top" style="background:linear-gradient(135deg,<?= $pal['from'] ?> 0%,<?= $pal['to'] ?> 100%);">
                            <div class="mapel-card-icon-wrap">
                                <i class="bi <?= $icon ?>"></i>
                            </div>
                            <!-- Mapel Name -->
                            <h5 class="mapel-card-name"><?= htmlspecialchars($ms['nama_mapel']) ?></h5>
                            <?php if (!empty($ms['nama_guru'])): ?>
                            <p class="mapel-card-guru">
                                <i class="bi bi-person-fill"></i>
                                <?= htmlspecialchars($ms['nama_guru']) ?>
                            </p>
                            <?php endif; ?>
                        </div>

                        <!-- Card Bottom Stats -->
                        <div class="mapel-card-bottom">
                            <div class="mapel-card-stats">
                                <div class="mapel-stat-item">
                                    <span class="mapel-stat-num"><?= $ms['jumlah_materi'] ?></span>
                                    <span class="mapel-stat-label">Materi</span>
                                </div>
                                <div class="mapel-stat-divider"></div>
                                <div class="mapel-stat-item">
                                    <span class="mapel-stat-num"><?= $totalQ ?></span>
                                    <span class="mapel-stat-label">Quiz</span>
                                </div>
                                <div class="mapel-stat-divider"></div>
                                <div class="mapel-stat-item">
                                    <span class="mapel-stat-num"><?= $totalT ?></span>
                                    <span class="mapel-stat-label">Tugas</span>
                                </div>
                            </div>

                            <!-- Badges aktif -->
                            <?php if ($aktifQuiz > 0 || $aktifTugas > 0): ?>
                            <div class="mapel-card-badges">
                                <?php if ($aktifQuiz > 0): ?>
                                <span class="mapel-badge-active mapel-badge-quiz">
                                    <i class="bi bi-patch-question-fill"></i> <?= $aktifQuiz ?> Quiz Aktif
                                </span>
                                <?php endif; ?>
                                <?php if ($aktifTugas > 0): ?>
                                <span class="mapel-badge-active mapel-badge-tugas">
                                    <i class="bi bi-clipboard2-check-fill"></i> <?= $aktifTugas ?> Tugas Aktif
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Open Button -->
                            <a href="<?= BASE_URL ?>index.php?url=siswa/materiMapel&mapel_id=<?= $mid ?>"
                               class="mapel-card-btn" style="background:linear-gradient(135deg,<?= $pal['from'] ?> 0%,<?= $pal['to'] ?> 100%);">
                                <i class="bi bi-book-open-fill"></i>
                                <span>Buka Materi</span>
                                <i class="bi bi-arrow-right ms-auto"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <!-- Empty search state -->
            <div class="col-12 d-none" id="emptyMapelSearch">
                <div class="text-center py-5 bg-white rounded-4 border shadow-sm">
                    <i class="bi bi-search fs-1 text-muted d-block mb-2"></i>
                    <h5 class="fw-bold text-dark mb-1">Mapel Tidak Ditemukan</h5>
                    <p class="text-muted small">Tidak ada mata pelajaran yang cocok dengan pencarian Anda.</p>
                </div>
            </div>
        </div><!-- /.row #mapelCardGrid -->

    </div>
</main>


<style>
/* ── Mapel Subject Cards ── */
.mapel-subject-card {
    border-radius: 20px;
    overflow: hidden;
    border: none;
    box-shadow: 0 6px 24px rgba(0,0,0,.07);
    transition: transform .25s ease, box-shadow .25s ease;
}
.mapel-subject-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 14px 36px rgba(0,0,0,.12);
}
.mapel-card-top {
    padding: 28px 22px 22px;
    position: relative;
    overflow: hidden;
}
.mapel-card-top::after {
    content: '';
    position: absolute;
    width: 120px; height: 120px;
    background: rgba(255,255,255,.12);
    border-radius: 50%;
    bottom: -30px; right: -30px;
    pointer-events: none;
}
.mapel-card-icon-wrap {
    width: 52px; height: 52px;
    background: rgba(255,255,255,.2);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    color: #fff;
    margin-bottom: 14px;
}
.mapel-card-name {
    font-size: 1.05rem; font-weight: 800;
    color: #fff; margin: 0 0 6px;
    line-height: 1.3;
}
.mapel-card-guru {
    font-size: .8rem; color: rgba(255,255,255,.82);
    margin: 0; font-weight: 500;
    display: flex; align-items: center; gap: 5px;
}
.mapel-card-bottom {
    background: #fff;
    padding: 18px 20px 20px;
    display: flex; flex-direction: column; gap: 12px;
}
[data-bs-theme="dark"] .mapel-card-bottom { background: #1e293b; }

.mapel-card-stats {
    display: flex; align-items: center; gap: 0;
    background: #f8fafc; border-radius: 12px; padding: 10px 0;
}
[data-bs-theme="dark"] .mapel-card-stats { background: #0f172a; }
.mapel-stat-item {
    flex: 1; text-align: center;
    display: flex; flex-direction: column; gap: 1px;
}
.mapel-stat-num {
    font-size: 1.25rem; font-weight: 800; color: #0f172a; line-height: 1;
}
[data-bs-theme="dark"] .mapel-stat-num { color: #f1f5f9; }
.mapel-stat-label {
    font-size: .68rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em;
}
.mapel-stat-divider {
    width: 1px; height: 36px; background: #e2e8f0; flex-shrink: 0;
}
[data-bs-theme="dark"] .mapel-stat-divider { background: #334155; }

.mapel-card-badges {
    display: flex; flex-wrap: wrap; gap: 6px;
}
.mapel-badge-active {
    font-size: .72rem; font-weight: 700;
    border-radius: 50rem; padding: 4px 10px;
    display: inline-flex; align-items: center; gap: 4px;
}
.mapel-badge-quiz  { background: #fef3c7; color: #92400e; }
.mapel-badge-tugas { background: #fce7f3; color: #9d174d; }

.mapel-card-btn {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 18px;
    border-radius: 12px;
    color: #fff; font-weight: 700; font-size: .88rem;
    text-decoration: none;
    transition: opacity .2s ease, transform .2s ease;
}
.mapel-card-btn:hover { color: #fff; opacity: .9; transform: scale(1.01); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputSearch  = document.getElementById('inputSearchMateri');
    const btnClear     = document.getElementById('btnClearSearch');
    const chipKeyword  = document.getElementById('chipSearchKeyword');
    const chipStatus   = document.getElementById('chipSearchStatus');
    const emptyEl      = document.getElementById('emptyMapelSearch');
    let debounce;

    function filterCards() {
        const q = inputSearch ? inputSearch.value.trim().toLowerCase() : '';
        if (btnClear) btnClear.classList.toggle('d-none', q.length === 0);
        if (chipStatus && chipKeyword) {
            chipKeyword.textContent = q;
            chipStatus.classList.toggle('d-none', q.length === 0);
        }
        let visible = 0;
        document.querySelectorAll('.mapel-card-col').forEach(function(col) {
            const match = !q || (col.dataset.nama || '').indexOf(q) !== -1;
            col.classList.toggle('d-none', !match);
            if (match) visible++;
        });
        if (emptyEl) emptyEl.classList.toggle('d-none', visible > 0);
    }

    if (inputSearch) {
        inputSearch.setAttribute('placeholder', 'Cari nama mata pelajaran atau guru...');
        inputSearch.addEventListener('input', function() {
            clearTimeout(debounce);
            debounce = setTimeout(filterCards, 150);
        });
    }
    if (btnClear) {
        btnClear.addEventListener('click', function() {
            if (inputSearch) { inputSearch.value = ''; inputSearch.focus(); }
            filterCards();
        });
    }
    // Hide dropdown mapel filter (tidak relevan lagi)
    const mapelSelect = document.getElementById('selectFilterMapel');
    if (mapelSelect) { mapelSelect.closest('.col-12, .col-sm-8, .col-md-4, .col-lg-3')?.classList.add('d-none'); }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
