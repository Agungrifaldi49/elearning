<?php
/**
 * Halaman E-Book Materi per Mata Pelajaran (Siswa)
 * Tampilan terstruktur per-bab lengkap dengan Quiz & Tugas terkait.
 */
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/navbar.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';

// Helper embed YouTube
if (!function_exists('ytEmbedUrl')) {
    function ytEmbedUrl($url) {
        if (empty($url)) return '';
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        return $url;
    }
}

// Warna bab berputar (palet harmonis)
$babColors = ['#2563eb','#7c3aed','#0891b2','#059669','#d97706','#dc2626','#db2777','#65a30d'];

// Helper: ikon tipe file
if (!function_exists('getFileIcon')) {
    function getFileIcon($jenis, $ext = '') {
        if ($jenis === 'youtube' || $ext === 'youtube') return ['bi-youtube text-danger', 'YouTube'];
        if ($jenis === 'video' || in_array($ext, ['mp4','webm'])) return ['bi-play-circle-fill text-danger', 'Video'];
        if ($jenis === 'pdf' || $ext === 'pdf') return ['bi-file-earmark-pdf-fill text-danger', 'PDF'];
        if (in_array($jenis, ['doc','docx']) || in_array($ext, ['doc','docx'])) return ['bi-file-earmark-word-fill text-primary', 'Word'];
        if (in_array($jenis, ['ppt','pptx']) || in_array($ext, ['ppt','pptx'])) return ['bi-file-earmark-ppt-fill text-warning', 'PPT'];
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) return ['bi-file-earmark-image-fill text-success', 'Gambar'];
        return ['bi-file-earmark-text-fill text-secondary', 'Berkas'];
    }
}
?>

<style>
/* ══════════════════════════════════════════════════════════
   E-BOOK MATERI MAPEL — MODERN EBOOK READER STYLE
   ══════════════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.ebook-wrapper { font-family: 'Inter', sans-serif; }

/* ── Hero Banner ── */
.ebook-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 45%, #312e81 100%);
    border-radius: 20px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.ebook-hero::before {
    content: '';
    position: absolute;
    width: 340px; height: 340px;
    background: radial-gradient(circle, rgba(139,92,246,.3) 0%, transparent 70%);
    top: -80px; right: -60px;
    pointer-events: none;
}
.ebook-hero::after {
    content: '';
    position: absolute;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(56,189,248,.2) 0%, transparent 70%);
    bottom: -40px; left: 40px;
    pointer-events: none;
}

/* ── Stat Pills ── */
.ebook-stat-pill {
    background: rgba(255,255,255,.12);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 50rem;
    padding: 6px 16px;
    font-size: .82rem;
    font-weight: 600;
    color: #fff;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* ── Progress Bar ── */
.mapel-progress-bar { border-radius: 50rem; height: 6px; background: rgba(255,255,255,.2); overflow: hidden; }
.mapel-progress-fill { height: 100%; background: linear-gradient(90deg, #38bdf8, #a78bfa); border-radius: 50rem; transition: width .6s ease; }

/* ── BAB Card ── */
.bab-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0,0,0,.04);
    overflow: hidden;
    transition: box-shadow .2s ease;
}
.bab-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,.08); }
[data-bs-theme="dark"] .bab-card {
    background: #1e293b;
    border-color: #334155;
}

.bab-header {
    padding: 18px 22px 14px;
    display: flex;
    align-items: center;
    gap: 14px;
    cursor: pointer;
    user-select: none;
}
.bab-number {
    width: 42px; height: 42px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; font-weight: 800;
    color: #fff;
    flex-shrink: 0;
}
.bab-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0; line-height: 1.3; }
[data-bs-theme="dark"] .bab-title { color: #f1f5f9; }
.bab-meta { font-size: .78rem; color: #64748b; margin: 0; }
.bab-chevron { margin-left: auto; color: #94a3b8; transition: transform .25s ease; font-size: 1.1rem; }
.bab-card.is-open .bab-chevron { transform: rotate(-180deg); }

.bab-body {
    border-top: 1px solid #f1f5f9;
    padding: 0;
    display: none;
}
[data-bs-theme="dark"] .bab-body { border-color: #334155; }
.bab-card.is-open .bab-body { display: block; }

/* ── Materi Row ── */
.materi-row {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px 22px;
    border-bottom: 1px solid #f8fafc;
    transition: background .15s ease;
}
.materi-row:last-child { border-bottom: none; }
.materi-row:hover { background: #f8fafc; }
[data-bs-theme="dark"] .materi-row { border-color: #1e293b; }
[data-bs-theme="dark"] .materi-row:hover { background: rgba(255,255,255,.04); }

.materi-row-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
    margin-top: 2px;
}
.materi-row-body { flex: 1; min-width: 0; }
.materi-row-title { font-size: .95rem; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
[data-bs-theme="dark"] .materi-row-title { color: #f1f5f9; }
.materi-row-desc { font-size: .78rem; color: #64748b; margin: 0; }
.materi-row-actions { display: flex; gap: 6px; flex-shrink: 0; margin-top: 4px; }
.btn-materi-action {
    font-size: .78rem; font-weight: 600;
    padding: 5px 14px;
    border-radius: 50rem;
    display: inline-flex; align-items: center; gap: 5px;
    white-space: nowrap;
    text-decoration: none;
    border: 1.5px solid transparent;
    transition: all .2s ease;
}
.btn-materi-read  { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.btn-materi-read:hover { background: #2563eb; color: #fff; }
.btn-materi-video { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
.btn-materi-video:hover { background: #dc2626; color: #fff; }

/* ── Quiz & Tugas Divider & Card ── */
.qanda-divider {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 22px 4px;
}
.qanda-divider::before, .qanda-divider::after {
    content: ''; flex: 1; height: 1px;
    background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
}
[data-bs-theme="dark"] .qanda-divider::before,
[data-bs-theme="dark"] .qanda-divider::after { background: linear-gradient(90deg, transparent, #334155, transparent); }
.qanda-divider span {
    font-size: .72rem; font-weight: 700;
    color: #94a3b8; letter-spacing: .06em;
    text-transform: uppercase; white-space: nowrap;
}

.qa-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 22px;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
    transition: background .15s;
}
.qa-item:last-child { border-bottom: none; }
.qa-item:hover { background: #f1f5f9; }
[data-bs-theme="dark"] .qa-item { background: rgba(15,23,42,.4); border-color: #1e293b; }
[data-bs-theme="dark"] .qa-item:hover { background: rgba(15,23,42,.6); }

.qa-item-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.qa-item-body { flex: 1; min-width: 0; }
.qa-item-title { font-size: .88rem; font-weight: 700; margin: 0; color: #0f172a; }
[data-bs-theme="dark"] .qa-item-title { color: #f1f5f9; }
.qa-item-meta  { font-size: .73rem; color: #64748b; margin: 0; }
.qa-item-badge { font-size: .7rem; font-weight: 700; padding: 3px 10px; border-radius: 50rem; white-space: nowrap; }
.badge-done    { background: #dcfce7; color: #15803d; }
.badge-pending { background: #fff7ed; color: #c2410c; }
.badge-nilai   { background: #eff6ff; color: #1d4ed8; }

/* ── Empty bab ── */
.bab-empty { padding: 28px 22px; text-align: center; color: #94a3b8; font-size: .88rem; }

/* ── Empty total ── */
.ebook-empty-state {
    background: #fff; border-radius: 18px; border: 1px solid #e2e8f0;
    padding: 60px 24px; text-align: center;
}
[data-bs-theme="dark"] .ebook-empty-state { background: #1e293b; border-color: #334155; }

/* ── Back Button ── */
.btn-ebook-back {
    background: rgba(255,255,255,.12);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,.22);
    color: #fff; border-radius: 50rem;
    padding: 6px 18px;
    font-size: .82rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 6px;
    text-decoration: none; transition: all .2s ease;
}
.btn-ebook-back:hover { background: rgba(255,255,255,.22); color: #fff; }

/* ── Responsive ── */
@media (max-width: 576px) {
    .materi-row { flex-direction: column; gap: 10px; }
    .materi-row-actions { width: 100%; }
    .bab-title { font-size: .9rem; }
}
</style>

<main class="main-content px-3 px-md-4 ebook-wrapper">
<div class="container-fluid">

    <?php
    // Flash messages
    if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_SESSION['flash_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['flash_success']); endif;
    if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['flash_error']); endif; ?>

    <!-- ══════════ HERO BANNER ══════════ -->
    <div class="ebook-hero p-4 p-md-5 mb-4">
        <div class="position-relative" style="z-index:2;">
            <!-- Back Button -->
            <div class="mb-3">
                <a href="<?= BASE_URL ?>index.php?url=siswa/materi" class="btn-ebook-back">
                    <i class="bi bi-arrow-left"></i> Semua Mata Pelajaran
                </a>
            </div>

            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-3"
                         style="background:rgba(255,255,255,.12); backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,.2); font-size:.78rem; font-weight:700; color:#fff;">
                        <i class="bi bi-book-half"></i>
                        <span>E-Book Materi Pembelajaran</span>
                    </div>
                    <h2 class="fw-bold text-white mb-1" style="font-size:clamp(1.4rem,3vw,2rem); letter-spacing:-.5px;">
                        <?= htmlspecialchars($namaMapel ?: 'Mata Pelajaran') ?>
                    </h2>
                    <?php if (!empty($namaGuru)): ?>
                    <p class="text-white mb-3" style="opacity:.85; font-size:.9rem;">
                        <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($namaGuru) ?>
                    </p>
                    <?php endif; ?>

                    <!-- Stat Pills -->
                    <div class="d-flex flex-wrap gap-2">
                        <span class="ebook-stat-pill"><i class="bi bi-journals"></i> <?= $totalMateri ?> Materi</span>
                        <span class="ebook-stat-pill"><i class="bi bi-patch-question-fill"></i> <?= $totalQuiz ?> Quiz</span>
                        <span class="ebook-stat-pill"><i class="bi bi-clipboard2-check-fill"></i> <?= $totalTugas ?> Tugas</span>
                        <?php if ($totalQuiz > 0 || $totalTugas > 0): ?>
                        <span class="ebook-stat-pill" style="background:rgba(52,211,153,.2); border-color:rgba(52,211,153,.3);">
                            <i class="bi bi-check2-all"></i>
                            Selesai: <?= $doneQuiz ?>/<?= $totalQuiz ?> Quiz · <?= $doneTugas ?>/<?= $totalTugas ?> Tugas
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <?php
                    $totalTask = $totalQuiz + $totalTugas;
                    $doneTask  = $doneQuiz + $doneTugas;
                    $pctDone   = $totalTask > 0 ? round(($doneTask / $totalTask) * 100) : ($totalMateri > 0 ? 15 : 0);
                    ?>
                    <div class="text-white mb-2" style="font-size:.82rem; font-weight:600; opacity:.8;">Progress Penyelesaian</div>
                    <div class="mapel-progress-bar mb-1">
                        <div class="mapel-progress-fill" style="width:<?= $pctDone ?>%"></div>
                    </div>
                    <div class="text-white" style="font-size:.82rem; opacity:.7;"><?= $pctDone ?>% Selesai</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════ BAB LIST (E-BOOK) ══════════ -->
    <?php if (empty($materiGroupedWithLinks)): ?>
        <div class="ebook-empty-state">
            <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex mb-3"
                 style="width:64px;height:64px;align-items:center;justify-content:center;">
                <i class="bi bi-journals fs-2"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">Belum Ada Materi</h5>
            <p class="text-muted small mb-3">Guru belum mengunggah materi untuk mata pelajaran ini.</p>
            <a href="<?= BASE_URL ?>index.php?url=siswa/materi" class="btn btn-primary rounded-pill px-4 fw-bold">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

    <?php else:
        $babIndex = 0;
        foreach ($materiGroupedWithLinks as $babNama => $materiItems):
            $color = $babColors[$babIndex % count($babColors)];
            $babIndex++;
            $totalItemBab = count($materiItems);
            $totalQuizBab = 0; $totalTugasBab = 0;
            foreach ($materiItems as $mi) {
                $totalQuizBab  += count($mi['linked_quiz']);
                $totalTugasBab += count($mi['linked_tugas']);
            }
    ?>
        <div class="bab-card mb-4 is-open" id="bab-card-<?= $babIndex ?>">
            <!-- BAB HEADER (clickable toggle) -->
            <div class="bab-header" onclick="toggleBab(<?= $babIndex ?>)" role="button" tabindex="0"
                 onkeydown="if(event.key==='Enter') toggleBab(<?= $babIndex ?>)">
                <div class="bab-number" style="background:<?= $color ?>;">
                    <?= $babIndex ?>
                </div>
                <div class="flex-grow-1 min-width-0">
                    <p class="bab-title"><?= htmlspecialchars($babNama) ?></p>
                    <p class="bab-meta">
                        <?= $totalItemBab ?> Materi
                        <?php if ($totalQuizBab > 0): ?> &bull; <?= $totalQuizBab ?> Quiz<?php endif; ?>
                        <?php if ($totalTugasBab > 0): ?> &bull; <?= $totalTugasBab ?> Tugas<?php endif; ?>
                    </p>
                </div>
                <i class="bi bi-chevron-down bab-chevron"></i>
            </div>

            <!-- BAB BODY -->
            <div class="bab-body">
                <?php foreach ($materiItems as $idx => $m):
                    $ext = strtolower(pathinfo($m['file_path'] ?? '', PATHINFO_EXTENSION));
                    [$fileIconClass, $fileTypeName] = getFileIcon($m['jenis_file'] ?? '', $ext);
                    $isYT   = ($m['jenis_file'] === 'youtube' || !empty($m['youtube_url']));
                    $hasFile = !empty($m['file_path']);
                    $fileUrl = $hasFile ? BASE_URL . 'assets/uploads/materi/' . htmlspecialchars($m['file_path']) : null;

                    // bg ikon sesuai warna bab (muted)
                    $iconBg = 'rgba(' . implode(',', sscanf(ltrim($color, '#'), "%02x%02x%02x")) . ',.12)';
                ?>
                    <!-- MATERI ROW -->
                    <div class="materi-row">
                        <div class="materi-row-icon" style="background:<?= $iconBg ?>;">
                            <i class="bi <?= $fileIconClass ?>"></i>
                        </div>
                        <div class="materi-row-body">
                            <div class="materi-row-title"><?= htmlspecialchars($m['judul']) ?></div>
                            <div class="materi-row-desc">
                                <span class="badge" style="background:<?= $iconBg ?>;color:<?= $color ?>;font-size:.68rem;font-weight:700;border-radius:50rem;padding:2px 8px;"><?= $fileTypeName ?></span>
                                <?php if (!empty($m['deskripsi'])): ?>
                                <?php $plainSnippet = trim(strip_tags($m['deskripsi'])); ?>
                                <?php if (!empty($plainSnippet)): ?>
                                &nbsp;<span><?= htmlspecialchars(mb_substr($plainSnippet, 0, 80)) ?><?= mb_strlen($plainSnippet) > 80 ? '...' : '' ?></span>
                                <?php endif; ?>
                                <?php endif; ?>
                                <span class="text-muted ms-1" style="font-size:.7rem;">
                                    &bull; <?= date('d M Y', strtotime($m['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                        <div class="materi-row-actions flex-wrap">
                            <?php if ($hasFile || $m['jenis_file'] !== 'youtube'): ?>
                                <a href="<?= BASE_URL ?>index.php?url=siswa/bacaMateri&id=<?= $m['id'] ?>"
                                   class="btn-materi-action btn-materi-read">
                                    <i class="bi bi-book-half"></i>
                                    <?= $m['jenis_file'] === 'video' ? 'Tonton' : 'Baca' ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($isYT && !empty($m['youtube_url'])): ?>
                                <a href="<?= BASE_URL ?>index.php?url=siswa/bacaMateri&id=<?= $m['id'] ?>"
                                   class="btn-materi-action btn-materi-video">
                                    <i class="bi bi-youtube"></i> Video
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php
                    // Setelah materi terakhir, tampilkan divider + quiz/tugas terkait
                    $isLastInBab = ($idx === count($materiItems) - 1);

                    // Kumpulkan semua linked quiz & tugas di bab ini
                    $babLinkedQuiz  = $m['linked_quiz']  ?? [];
                    $babLinkedTugas = $m['linked_tugas'] ?? [];

                    if (!empty($babLinkedQuiz) || !empty($babLinkedTugas)):
                    ?>
                        <!-- Quiz & Tugas Divider -->
                        <div class="qanda-divider">
                            <span><i class="bi bi-arrow-down-short"></i> Selesai membaca? Kerjakan di bawah</span>
                        </div>

                        <?php foreach ($babLinkedQuiz as $q):
                            $sdDikerjakan = $q['sudah_dikerjakan'] ?? false;
                            $nilaiQ       = $q['nilai_siswa'] ?? null;
                            $isExpired    = !empty($q['tanggal_selesai']) && strtotime($q['tanggal_selesai']) < time();
                        ?>
                        <div class="qa-item">
                            <div class="qa-item-icon"
                                 style="background:<?= $sdDikerjakan ? '#dcfce7' : '#fef3c7' ?>;">
                                <i class="bi bi-patch-question-fill"
                                   style="color:<?= $sdDikerjakan ? '#16a34a' : '#d97706' ?>;"></i>
                            </div>
                            <div class="qa-item-body">
                                <p class="qa-item-title"><?= htmlspecialchars($q['judul']) ?></p>
                                <p class="qa-item-meta">
                                    Quiz
                                    <?php if (!empty($q['tanggal_selesai'])): ?>
                                    &bull; Ditutup: <?= date('d M Y, H:i', strtotime($q['tanggal_selesai'])) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                                <?php if ($sdDikerjakan): ?>
                                    <span class="qa-item-badge badge-done">
                                        <i class="bi bi-check-circle-fill me-1"></i>Selesai
                                    </span>
                                    <?php if ($nilaiQ !== null): ?>
                                    <span class="qa-item-badge badge-nilai">Nilai: <?= $nilaiQ ?></span>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>index.php?url=siswa/reviewQuiz&quiz_id=<?= $q['id'] ?>"
                                       class="btn-materi-action" style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;font-size:.72rem;">
                                        <i class="bi bi-eye"></i> Review
                                    </a>
                                <?php elseif ($isExpired): ?>
                                    <span class="qa-item-badge" style="background:#f1f5f9;color:#64748b;">
                                        <i class="bi bi-lock-fill me-1"></i>Ditutup
                                    </span>
                                <?php else: ?>
                                    <span class="qa-item-badge badge-pending">
                                        <i class="bi bi-clock-fill me-1"></i>Belum
                                    </span>
                                    <a href="<?= BASE_URL ?>index.php?url=siswa/kerjakanQuiz&quiz_id=<?= $q['id'] ?>"
                                       class="btn-materi-action"
                                       style="background:#fef3c7;color:#92400e;border-color:#fcd34d;font-size:.72rem;">
                                        <i class="bi bi-pencil-fill"></i> Kerjakan
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php foreach ($babLinkedTugas as $t):
                            $sdDikumpul = $t['sudah_dikumpul'] ?? false;
                            $nilaiT     = $t['nilai_siswa']   ?? null;
                            $isExpired  = !empty($t['deadline']) && strtotime($t['deadline']) < time();
                        ?>
                        <div class="qa-item">
                            <div class="qa-item-icon"
                                 style="background:<?= $sdDikumpul ? '#dcfce7' : '#fce7f3' ?>;">
                                <i class="bi bi-clipboard2-check-fill"
                                   style="color:<?= $sdDikumpul ? '#16a34a' : '#be185d' ?>;"></i>
                            </div>
                            <div class="qa-item-body">
                                <p class="qa-item-title"><?= htmlspecialchars($t['judul']) ?></p>
                                <p class="qa-item-meta">
                                    Tugas
                                    <?php if (!empty($t['deadline'])): ?>
                                    &bull; Deadline: <?= date('d M Y, H:i', strtotime($t['deadline'])) ?>
                                    <span class="ms-1 <?= $isExpired ? 'text-danger' : 'text-success' ?> fw-semibold" style="font-size:.7rem;">
                                        (<?= $isExpired ? 'Lewat' : 'Aktif' ?>)
                                    </span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                                <?php if ($sdDikumpul): ?>
                                    <span class="qa-item-badge badge-done">
                                        <i class="bi bi-check-circle-fill me-1"></i>Dikumpul
                                    </span>
                                    <?php if ($nilaiT !== null): ?>
                                    <span class="qa-item-badge badge-nilai">Nilai: <?= $nilaiT ?></span>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>index.php?url=siswa/tugas&id=<?= $t['id'] ?>"
                                       class="btn-materi-action" style="background:#dcfce7;color:#15803d;border-color:#86efac;font-size:.72rem;">
                                        <i class="bi bi-eye"></i> Lihat
                                    </a>
                                <?php elseif ($isExpired): ?>
                                    <span class="qa-item-badge" style="background:#fef2f2;color:#dc2626;">
                                        <i class="bi bi-x-circle-fill me-1"></i>Terlambat
                                    </span>
                                    <a href="<?= BASE_URL ?>index.php?url=siswa/tugas&id=<?= $t['id'] ?>"
                                       class="btn-materi-action" style="background:#fef2f2;color:#dc2626;border-color:#fecaca;font-size:.72rem;">
                                        <i class="bi bi-upload"></i> Susulan
                                    </a>
                                <?php else: ?>
                                    <span class="qa-item-badge badge-pending">
                                        <i class="bi bi-clock-fill me-1"></i>Belum
                                    </span>
                                    <a href="<?= BASE_URL ?>index.php?url=siswa/tugas&id=<?= $t['id'] ?>"
                                       class="btn-materi-action"
                                       style="background:#fce7f3;color:#9d174d;border-color:#fbcfe8;font-size:.72rem;">
                                        <i class="bi bi-upload"></i> Kumpulkan
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                    <?php endif; // if linked_quiz/tugas ?>

                <?php endforeach; // foreach materiItems ?>

                <?php if (empty($materiItems)): ?>
                    <div class="bab-empty"><i class="bi bi-inbox me-2"></i>Belum ada materi dalam bab ini.</div>
                <?php endif; ?>
            </div><!-- /.bab-body -->
        </div><!-- /.bab-card -->
    <?php endforeach; // foreach bab ?>
    <?php endif; // if materiGroupedWithLinks ?>

    <!-- Quiz & Tugas Tidak Terhubung (ditampilkan di bagian bawah) -->
    <?php
    // Kumpulkan quiz/tugas yang mapel ini tapi belum di-link ke materi mana pun
    $linkedQuizIds  = [];
    $linkedTugasIds = [];
    foreach ($materiGroupedWithLinks as $bab => $items) {
        foreach ($items as $item) {
            foreach ($item['linked_quiz']  as $q) { $linkedQuizIds[]  = (int)$q['id']; }
            foreach ($item['linked_tugas'] as $t) { $linkedTugasIds[] = (int)$t['id']; }
        }
    }
    $unlinkQuiz  = array_values(array_filter($quizForMapel,  fn($q) => !in_array((int)$q['id'], $linkedQuizIds)));
    $unlinkTugas = array_values(array_filter($tugasForMapel, fn($t) => !in_array((int)$t['id'], $linkedTugasIds)));

    if (!empty($unlinkQuiz) || !empty($unlinkTugas)): ?>
    <div class="bab-card mb-4 is-open" id="bab-card-extra">
        <div class="bab-header" onclick="toggleBabExtra()" role="button" tabindex="0">
            <div class="bab-number" style="background:#6b7280;">
                <i class="bi bi-collection-fill" style="font-size:.9rem;"></i>
            </div>
            <div class="flex-grow-1">
                <p class="bab-title">Kuis &amp; Tugas Umum Mapel</p>
                <p class="bab-meta">Tidak terkait dengan materi spesifik</p>
            </div>
            <i class="bi bi-chevron-down bab-chevron"></i>
        </div>
        <div class="bab-body">
            <?php if (!empty($unlinkQuiz)): ?>
            <div class="qanda-divider"><span>Quiz</span></div>
            <?php foreach ($unlinkQuiz as $q):
                $sdDikerjakan = isset($hasilQuizMap[(int)$q['id']]);
                $nilaiQ       = $hasilQuizMap[(int)$q['id']]['total_nilai'] ?? null;
                $isExpired    = !empty($q['tanggal_selesai']) && strtotime($q['tanggal_selesai']) < time();
            ?>
            <div class="qa-item">
                <div class="qa-item-icon" style="background:<?= $sdDikerjakan ? '#dcfce7' : '#fef3c7' ?>;">
                    <i class="bi bi-patch-question-fill" style="color:<?= $sdDikerjakan ? '#16a34a' : '#d97706' ?>;"></i>
                </div>
                <div class="qa-item-body">
                    <p class="qa-item-title"><?= htmlspecialchars($q['judul']) ?></p>
                    <p class="qa-item-meta">Quiz <?php if (!empty($q['tanggal_selesai'])): ?>&bull; Ditutup: <?= date('d M Y', strtotime($q['tanggal_selesai'])) ?><?php endif; ?></p>
                </div>
                <div class="d-flex gap-2 flex-wrap justify-content-end">
                    <?php if ($sdDikerjakan): ?>
                        <span class="qa-item-badge badge-done"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
                        <?php if ($nilaiQ !== null): ?><span class="qa-item-badge badge-nilai">Nilai: <?= $nilaiQ ?></span><?php endif; ?>
                    <?php elseif ($isExpired): ?>
                        <span class="qa-item-badge" style="background:#f1f5f9;color:#64748b;"><i class="bi bi-lock-fill me-1"></i>Ditutup</span>
                    <?php else: ?>
                        <span class="qa-item-badge badge-pending"><i class="bi bi-clock-fill me-1"></i>Belum</span>
                        <a href="<?= BASE_URL ?>index.php?url=siswa/kerjakanQuiz&quiz_id=<?= $q['id'] ?>"
                           class="btn-materi-action" style="background:#fef3c7;color:#92400e;border-color:#fcd34d;font-size:.72rem;">
                            <i class="bi bi-pencil-fill"></i> Kerjakan
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($unlinkTugas)): ?>
            <div class="qanda-divider"><span>Tugas</span></div>
            <?php foreach ($unlinkTugas as $t):
                $sdDikumpul = isset($pengumpulanMap[(int)$t['id']]);
                $nilaiT     = $pengumpulanMap[(int)$t['id']]['nilai'] ?? null;
                $isExpired  = !empty($t['deadline']) && strtotime($t['deadline']) < time();
            ?>
            <div class="qa-item">
                <div class="qa-item-icon" style="background:<?= $sdDikumpul ? '#dcfce7' : '#fce7f3' ?>;">
                    <i class="bi bi-clipboard2-check-fill" style="color:<?= $sdDikumpul ? '#16a34a' : '#be185d' ?>;"></i>
                </div>
                <div class="qa-item-body">
                    <p class="qa-item-title"><?= htmlspecialchars($t['judul']) ?></p>
                    <p class="qa-item-meta">Tugas <?php if (!empty($t['deadline'])): ?>&bull; Deadline: <?= date('d M Y', strtotime($t['deadline'])) ?><?php endif; ?></p>
                </div>
                <div class="d-flex gap-2 flex-wrap justify-content-end">
                    <?php if ($sdDikumpul): ?>
                        <span class="qa-item-badge badge-done"><i class="bi bi-check-circle-fill me-1"></i>Dikumpul</span>
                        <?php if ($nilaiT !== null): ?><span class="qa-item-badge badge-nilai">Nilai: <?= $nilaiT ?></span><?php endif; ?>
                    <?php elseif ($isExpired): ?>
                        <span class="qa-item-badge" style="background:#fef2f2;color:#dc2626;"><i class="bi bi-x-circle-fill me-1"></i>Terlambat</span>
                    <?php else: ?>
                        <span class="qa-item-badge badge-pending"><i class="bi bi-clock-fill me-1"></i>Belum</span>
                        <a href="<?= BASE_URL ?>index.php?url=siswa/tugas&id=<?= $t['id'] ?>"
                           class="btn-materi-action" style="background:#fce7f3;color:#9d174d;border-color:#fbcfe8;font-size:.72rem;">
                            <i class="bi bi-upload"></i> Kumpulkan
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>
</main>

<script>
function toggleBab(idx) {
    const card = document.getElementById('bab-card-' + idx);
    if (card) card.classList.toggle('is-open');
}
function toggleBabExtra() {
    const card = document.getElementById('bab-card-extra');
    if (card) card.classList.toggle('is-open');
}

// Keyboard accessibility
document.querySelectorAll('.bab-header').forEach(function(header) {
    header.addEventListener('keydown', function(e) {
        if (e.key === ' ') { e.preventDefault(); header.click(); }
    });
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
