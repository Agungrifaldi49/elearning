<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<?php
$totalSoalCount = count($soalList);
$totalCorrectCount = 0;
$totalIncorrectCount = 0;

foreach ($soalList as $s) {
    $j = $s['jawaban_siswa'] ?? null;
    if ($j && (int)($j['is_benar'] ?? 0) === 1) {
        $totalCorrectCount++;
    } else {
        $totalIncorrectCount++;
    }
}
$isLulus = (($hasilQuiz['status_lulus'] ?? '') === 'lulus');
$scoreVal = (float)($hasilQuiz['total_nilai'] ?? 0);
?>

<style>
/* Modern LMS Quiz Review Engine */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.review-quiz-wrapper {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

/* Premium Hero Banner */
.review-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
    border-radius: 20px;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2);
    position: relative;
    overflow: hidden;
}

/* Question Review Card */
.soal-review-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.03);
    margin-bottom: 20px;
}

/* Choice Option Review Cards */
.choice-review-option {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 18px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
    background-color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.choice-review-option.user-correct {
    background-color: #f0fdf4 !important;
    border-color: #22c55e !important;
    color: #15803d !important;
}

.choice-review-option.user-incorrect {
    background-color: #fef2f2 !important;
    border-color: #ef4444 !important;
    color: #b91c1c !important;
}

.choice-review-option.key-correct {
    background-color: #f0fdf4 !important;
    border: 2px dashed #16a34a !important;
    color: #15803d !important;
}
</style>

<main class="main-content px-2 px-sm-3 px-md-4 py-3 review-quiz-wrapper">
<div class="container-fluid pt-3">
    
    <!-- Hero Banner Header -->
    <div class="review-hero-banner text-white p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row gap-3 position-relative z-1">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-gradient p-3 rounded-4 text-white shadow-sm d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-file-earmark-check-fill fs-2"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-white mb-1" style="letter-spacing: -0.3px;">Review Lembar Jawaban CBT</h4>
                    <p class="text-info-subtle small mb-0 fw-medium">
                        <?= htmlspecialchars($quizInfo['judul']) ?> &bull; Mapel: <?= htmlspecialchars($quizInfo['nama_mapel']) ?>
                    </p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASE_URL ?>index.php?url=siswa/nilai" class="btn btn-outline-light rounded-pill fw-bold px-3.5 py-2" style="font-size: 0.88rem;">
                    <i class="bi bi-arrow-left me-1.5"></i> Kembali ke Transkrip Nilai
                </a>
            </div>
        </div>
    </div>

    <!-- Summary KPI Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-4 border-primary">
                <small class="text-muted fw-bold d-block mb-1" style="font-size:0.75rem;">SKOR AKHIR SISWA</small>
                <div class="d-flex align-items-center justify-content-between">
                    <h2 class="fw-black text-primary mb-0"><?= number_format($scoreVal, 1) ?></h2>
                    <span class="badge bg-<?= $isLulus ? 'success' : 'danger' ?> rounded-pill px-3 py-1.5 fw-bold">
                        <?= $isLulus ? 'LULUS' : 'REMEDIAL' ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-4 border-info">
                <small class="text-muted fw-bold d-block mb-1" style="font-size:0.75rem;">TOTAL SOAL EVALUASI</small>
                <h2 class="fw-black text-slate-800 mb-0"><?= $totalSoalCount ?></h2>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-4 border-success">
                <small class="text-muted fw-bold d-block mb-1" style="font-size:0.75rem;">JAWABAN BENAR</small>
                <h2 class="fw-black text-success mb-0"><?= $totalCorrectCount ?></h2>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-4 border-danger">
                <small class="text-muted fw-bold d-block mb-1" style="font-size:0.75rem;">JAWABAN SALAH / KOSONG</small>
                <h2 class="fw-black text-danger mb-0"><?= $totalIncorrectCount ?></h2>
            </div>
        </div>
    </div>

    <!-- Review Questions Section -->
    <div class="d-flex align-items-center justify-content-between mb-3 px-1">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-list-check text-primary"></i>
            <span>Rincian Evaluasi Soal demi Soal</span>
        </h5>
        <small class="text-muted fw-semibold" style="font-size:0.8rem;">
            <i class="bi bi-info-circle me-1"></i>Pilihan jawaban Anda & Kunci Jawaban Resmi
        </small>
    </div>

    <?php foreach ($soalList as $idx => $s): 
        $j = $s['jawaban_siswa'] ?? null;
        $isBenar = ($j && (int)($j['is_benar'] ?? 0) === 1);
        $userPilId = $j['pilihan_id'] ?? null;
    ?>
        <div class="soal-review-card p-4">
            
            <!-- Question Header -->
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
                <span class="badge bg-dark text-white rounded-pill px-3 py-1.5 fw-bold" style="font-size: 0.85rem;">
                    Soal No. <?= $idx + 1 ?>
                </span>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-slate-100 text-slate-700 border rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem; background-color: #f8fafc;">
                        Bobot: <?= $s['bobot'] ?? 10 ?> Poin
                    </span>

                    <?php if ($isBenar): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold small">
                            <i class="bi bi-check-circle-fill me-1"></i>Jawaban Anda BENAR (+<?= $j['nilai'] ?? $s['bobot'] ?> Poin)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold small">
                            <i class="bi bi-x-circle-fill me-1"></i>Jawaban Anda SALAH (0 Poin)
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Question Text -->
            <div class="fw-semibold text-slate-800 fs-6 mb-3" style="line-height: 1.6; color: #1e293b;">
                <?= nl2br(htmlspecialchars($s['pertanyaan'])) ?>
            </div>

            <?php if (!empty($s['gambar'])): ?>
                <div class="mb-3">
                    <img src="<?= BASE_URL ?>assets/uploads/soal/<?= htmlspecialchars($s['gambar']) ?>" class="img-fluid rounded-3 border shadow-xs" style="max-height: 250px;">
                </div>
            <?php endif; ?>

            <!-- Options Review for Multiple Choice / True False -->
            <?php if (($s['jenis_soal'] === 'pg' || $s['jenis_soal'] === 'tf') && !empty($s['pilihan'])): ?>
                <div class="mt-3">
                    <?php 
                    $labels = ['A', 'B', 'C', 'D', 'E'];
                    foreach ($s['pilihan'] as $pIdx => $p): 
                        $isUserSelected = ($userPilId == $p['id']);
                        $isKeyAnswer = ($p['is_benar'] == 1);

                        $optionClass = '';
                        $badgeText = '';

                        if ($isUserSelected && $isKeyAnswer) {
                            $optionClass = 'user-correct';
                            $badgeText = '<span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-bold small"><i class="bi bi-check-lg me-1"></i>Jawaban Anda (BENAR)</span>';
                        } elseif ($isUserSelected && !$isKeyAnswer) {
                            $optionClass = 'user-incorrect';
                            $badgeText = '<span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-bold small"><i class="bi bi-x-lg me-1"></i>Jawaban Anda (SALAH)</span>';
                        } elseif (!$isUserSelected && $isKeyAnswer) {
                            $optionClass = 'key-correct';
                            $badgeText = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold small"><i class="bi bi-key-fill me-1"></i>Kunci Jawaban Yang Benar</span>';
                        }
                    ?>
                        <div class="choice-review-option <?= $optionClass ?>">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width:28px; height:28px; font-size:0.8rem;">
                                    <?= $labels[$pIdx] ?? ($pIdx + 1) ?>
                                </span>
                                <span class="fw-medium" style="font-size: 0.92rem;"><?= htmlspecialchars($p['teks_pilihan']) ?></span>
                            </div>
                            <div>
                                <?= $badgeText ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($s['jenis_soal'] === 'essay'): ?>
                <div class="p-3 bg-slate-50 rounded-3 border border-slate-200 mt-2">
                    <small class="text-muted fw-bold d-block mb-1">Jawaban Essay Anda:</small>
                    <p class="mb-2 text-dark font-monospace" style="font-size:0.9rem;"><?= nl2br(htmlspecialchars($j['teks_jawaban_essay'] ?? 'Tidak Menjawab')) ?></p>
                    <small class="text-primary fw-bold"><i class="bi bi-star-fill me-1"></i>Nilai Essay Guru: <?= number_format((float)($j['nilai'] ?? 0), 1) ?> Poin</small>
                </div>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
