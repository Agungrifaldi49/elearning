<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>

<?php
$durasiMenit = (int)($quizInfo['durasi_menit'] ?? 30);
$judulQuiz = htmlspecialchars($quizInfo['judul'] ?? 'Kuis Online CBT');
$namaMapel = htmlspecialchars($quizInfo['nama_mapel'] ?? 'Mata Pelajaran');
$totalSoal = count($soalList);
?>

<style>
/* Modern CBT Exam Engine Design Tokens & Styles */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

body {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
    user-select: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
    background-color: #f8fafc !important;
    color: #1e293b;
}

/* Header Gradient Navbar */
.cbt-header-bar {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #1e293b 100%);
    box-shadow: 0 4px 25px -5px rgba(15, 23, 42, 0.3);
    backdrop-filter: blur(10px);
}

/* Live Timer Pill */
.cbt-timer-box {
    background: rgba(15, 23, 42, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(8px);
}

/* Option Cards for Choices */
.cbt-choice-card {
    background-color: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px 18px;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    min-height: 52px;
}

.cbt-choice-card:hover {
    border-color: #3b82f6;
    background-color: #f8fafc;
    transform: translateY(-1px);
}

.cbt-choice-card.selected {
    border-color: #2563eb !important;
    background-color: #f0f7ff !important;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.1);
}

.cbt-choice-card.selected .choice-badge {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
}

.choice-badge {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 50%;
    background-color: #e2e8f0;
    color: #475569;
    font-weight: 700;
    font-size: 0.88rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

/* Question Grid Buttons */
.q-grid-btn {
    width: 44px;
    height: 44px;
    font-weight: 700;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    border: 1.5px solid #e2e8f0;
}

.q-grid-btn.unanswered {
    background-color: #f8fafc;
    color: #64748b;
}

.q-grid-btn.unanswered:hover {
    border-color: #94a3b8;
    background-color: #f1f5f9;
}

.q-grid-btn.answered {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    color: #ffffff !important;
    border-color: #047857 !important;
    box-shadow: 0 3px 10px rgba(16, 185, 129, 0.25);
}

.q-grid-btn.active-q {
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.35) !important;
    border-color: #2563eb !important;
    font-weight: 900;
}

/* Mobile Bottom Navigation Bar */
.cbt-mobile-bar {
    background: #ffffff;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
    border-top: 1px solid #e2e8f0;
}

/* Pulse Dot */
.dot-pulse {
    width: 8px;
    height: 8px;
    background-color: #10b981;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulse 1.6s infinite;
}

@keyframes pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

/* Responsive Overrides */
@media (max-width: 767.98px) {
    .cbt-header-bar {
        padding-top: 8px !important;
        padding-bottom: 8px !important;
    }
    .cbt-timer-box {
        padding: 4px 10px !important;
        font-size: 0.85rem !important;
    }
    .cbt-timer-box #timerDisplay {
        font-size: 0.9rem !important;
    }
    .q-grid-btn {
        width: 38px;
        height: 38px;
        font-size: 0.8rem;
        border-radius: 8px;
    }
    .cbt-choice-card {
        padding: 12px 14px;
    }
    .choice-badge {
        width: 28px;
        height: 28px;
        min-width: 28px;
        font-size: 0.8rem;
    }
}
</style>

<!-- CBT Sticky Navbar Header -->
<div class="cbt-header-bar text-white py-2 px-3 px-md-4 fixed-top d-flex justify-content-between align-items-center z-3">
    <!-- Quiz & Mapel Info -->
    <div class="d-flex align-items-center gap-2 gap-sm-3 overflow-hidden me-2">
        <div class="bg-primary bg-gradient text-white rounded-3 p-2 d-none d-sm-flex align-items-center justify-content-center shadow-sm" style="width:40px; height:40px;">
            <i class="bi bi-laptop fs-5"></i>
        </div>
        <div class="text-truncate">
            <div class="fw-bold text-white text-truncate mb-0" style="font-size: 0.95rem; letter-spacing: -0.2px;"><?= $judulQuiz ?></div>
            <small class="text-info fw-semibold d-flex align-items-center gap-1" style="font-size:0.72rem;">
                <i class="bi bi-book-half"></i> <span class="text-truncate"><?= $namaMapel ?></span>
            </small>
        </div>
    </div>

    <!-- Status & Timer Controls -->
    <div class="d-flex align-items-center gap-1.5 gap-sm-3">
        <!-- Live Status Badge (Desktop/Tablet) -->
        <div class="bg-slate-800 text-emerald-400 border border-emerald-500/30 px-2.5 py-1 rounded-pill d-none d-md-inline-flex align-items-center gap-1.5 shadow-xs" style="background: rgba(15, 23, 42, 0.6);">
            <span class="dot-pulse"></span>
            <span class="small fw-semibold text-light" style="font-size: 0.72rem;">Fullscreen Active</span>
        </div>

        <!-- Violation Badge -->
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-300 px-2.5 py-1 rounded-pill d-inline-flex align-items-center gap-1" style="background: rgba(225, 29, 72, 0.15);">
            <i class="bi bi-shield-exclamation text-rose-400" style="font-size: 0.8rem;"></i>
            <span class="small fw-bold" style="font-size: 0.72rem;">Warn: <span id="violationCount" class="text-white">0</span>/2</span>
        </div>

        <!-- Live Countdown Clock -->
        <div class="cbt-timer-box text-warning px-2.5 py-1 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-1.5">
            <i class="bi bi-stopwatch-fill text-danger" style="font-size:0.88rem;"></i>
            <span id="timerDisplay" style="font-family: 'JetBrains Mono', monospace; font-size: 0.95rem; letter-spacing: 0.5px;">00:00:00</span>
        </div>
    </div>
</div>

<!-- Start Exam Confirmation Modal (Fullscreen Entry Prompt) -->
<div class="modal fade" id="startExamModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 bg-dark text-white p-3 p-sm-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary rounded-3 p-2 text-white shadow-sm">
                        <i class="bi bi-shield-check fs-3"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0 fs-6 fs-sm-5">Portal CBT Ujian Online</h5>
                        <small class="text-info fw-medium" style="font-size:0.75rem;">Sistem Keamanan Ujian Realtime</small>
                    </div>
                </div>
            </div>
            <div class="modal-body p-3 p-sm-4 text-center">
                <div class="mb-3">
                    <h5 class="fw-bold text-dark mb-1 fs-6 fs-sm-5"><?= $judulQuiz ?></h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold small">Mapel: <?= $namaMapel ?></span>
                </div>
                
                <p class="text-muted small mb-3">Harap perhatikan aturan pengawasan ujian otomatis di bawah ini:</p>

                <div class="bg-slate-50 p-3 rounded-3 text-start border border-slate-200 small mb-3" style="background-color: #f8fafc;">
                    <div class="d-flex align-items-center gap-2 mb-2 text-danger fw-bold">
                        <i class="bi bi-exclamation-octagon-fill"></i> Aturan Pengawasan Otomatis:
                    </div>
                    <div class="d-flex flex-column gap-2 text-slate-700" style="font-size:0.8rem;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-primary mt-0.5"></i>
                            <span>Layar akan otomatis dikunci ke **Mode Fullscreen**.</span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-primary mt-0.5"></i>
                            <span>Dilarang berpindah tab, membuka window baru, atau keluar fullscreen.</span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-danger mt-0.5"></i>
                            <span>Toleransi pelanggaran **maksimal 2 kali**. Lebih dari itu, **ujian otomatis dibatalkan**.</span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-primary mt-0.5"></i>
                            <span>Fitur Copy-Paste, Klik Kanan, dan Shortcut Keyboard dinonaktifkan.</span>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-primary btn-lg w-100 fw-bold rounded-pill shadow-lg py-2.5 text-white fs-6" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);" onclick="startCBTEngine()">
                    <i class="bi bi-play-circle-fill me-2"></i> Mulai Ujian (Fullscreen)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main Exam Body -->
<div class="container-fluid py-3 py-md-4 px-2 px-sm-3 px-md-4" style="margin-top: 60px; margin-bottom: 70px; max-width: 1400px;">
    <div class="row g-3 g-md-4">
        
        <!-- LEFT COLUMN: Active Question Card -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm p-3 p-sm-4 p-md-5 bg-white">
                
                <form id="formCBT" action="<?= BASE_URL ?>index.php?url=siswa/quiz&id=<?= $quiz_id ?>" method="POST">
                    <?= Security::csrfField() ?>

                    <?php foreach ($soalList as $idx => $soal): ?>
                        <div class="soal-block mb-2 <?= $idx > 0 ? 'd-none' : '' ?>" id="soalCard_<?= $idx ?>" data-index="<?= $idx ?>">
                            
                            <!-- Header Info Soal -->
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary bg-gradient px-3 py-2 rounded-pill shadow-xs" style="font-size:0.82rem;">
                                        Soal No. <strong class="fs-6 ms-1"><?= $idx + 1 ?></strong> / <?= $totalSoal ?>
                                    </span>
                                    <span class="badge bg-slate-100 text-slate-700 border px-2.5 py-1.5 rounded-pill small fw-semibold" style="background:#f1f5f9; color:#334155; font-size:0.75rem;">
                                        <i class="bi bi-star-fill text-warning me-1"></i>Bobot: <?= $soal['bobot'] ?> Poin
                                    </span>
                                </div>
                                <span class="badge status-tag-<?= $idx ?> bg-slate-100 text-slate-500 border px-2.5 py-1.5 rounded-pill fw-semibold" style="background:#f8fafc; color:#64748b; font-size:0.75rem;">
                                    ⚪ Belum Dijawab
                                </span>
                            </div>

                            <!-- Teks Pertanyaan -->
                            <div class="lh-lg fw-semibold text-slate-800 mb-3 fs-6 fs-md-5" style="color: #1e293b;">
                                <?= nl2br(htmlspecialchars($soal['pertanyaan'])) ?>
                            </div>

                            <!-- Gambar Soal (Jika Ada) -->
                            <?php if (!empty($soal['gambar'])): ?>
                                <div class="mb-3 text-center p-2.5 bg-slate-50 rounded-4 border border-slate-200">
                                    <img src="<?= BASE_URL ?>assets/uploads/soal/<?= htmlspecialchars($soal['gambar']) ?>" alt="Gambar Soal" class="img-fluid rounded-3 shadow-xs" style="max-height: 350px; object-fit: contain;">
                                </div>
                            <?php endif; ?>

                            <!-- Pilihan Jawaban PG, True/False, atau Textarea Essay -->
                            <?php if (($soal['jenis_soal'] === 'pg' || $soal['jenis_soal'] === 'tf') && !empty($soal['pilihan'])): ?>
                                <div class="d-flex flex-column gap-2.5 mb-4">
                                    <?php 
                                    $labels = ['A', 'B', 'C', 'D', 'E', 'F'];
                                    foreach ($soal['pilihan'] as $pIdx => $pil): 
                                        $lbl = ($soal['jenis_soal'] === 'tf') ? ($pIdx === 0 ? '✓' : '✗') : ($labels[$pIdx] ?? ($pIdx + 1));
                                    ?>
                                        <label class="cbt-choice-card d-flex align-items-center">
                                            <input class="form-check-input d-none input-jawaban" type="radio" name="jawaban[<?= $soal['id'] ?>]" value="<?= $pil['id'] ?>" data-soal-idx="<?= $idx ?>" onchange="onAnswerSelected(<?= $idx ?>)">
                                            <span class="choice-badge me-2.5 me-sm-3 <?= $soal['jenis_soal'] === 'tf' ? ($pIdx === 0 ? 'bg-success text-white' : 'bg-danger text-white') : '' ?>"><?= $lbl ?></span>
                                            <span class="fw-medium text-slate-800 flex-grow-1" style="font-size: 0.92rem; color:#1e293b;"><?= htmlspecialchars($pil['teks_pilihan']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-primary mb-1.5"><i class="bi bi-pencil-square me-1"></i>Jawaban Essay Siswa:</label>
                                    <textarea name="essay[<?= $soal['id'] ?>]" class="form-control input-essay rounded-4 p-3 border-2" rows="4" placeholder="Ketikkan lembar jawaban essay Anda secara lengkap di sini..." data-soal-idx="<?= $idx ?>" oninput="onAnswerSelected(<?= $idx ?>)" style="font-size: 0.9rem;"></textarea>
                                </div>
                            <?php endif; ?>

                            <!-- Navigation Buttons Footer -->
                            <div class="d-flex justify-content-between align-items-center pt-3 mt-2 border-top flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-secondary px-3 px-sm-4 py-2 rounded-pill fw-bold small" onclick="navSoal(<?= $idx - 1 ?>)" <?= $idx === 0 ? 'disabled' : '' ?>>
                                    <i class="bi bi-arrow-left me-1"></i> Sebelumnya
                                </button>
                                
                                <?php if ($idx < $totalSoal - 1): ?>
                                    <button type="button" class="btn btn-primary px-3 px-sm-4 py-2 rounded-pill fw-bold shadow-sm small" onclick="navSoal(<?= $idx + 1 ?>)">
                                        Berikutnya <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-success px-3 px-sm-4 py-2 rounded-pill fw-bold shadow-sm small" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" onclick="confirmSubmitExam()">
                                        <i class="bi bi-check-circle-fill me-1"></i> Selesaikan Ujian
                                    </button>
                                <?php endif; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </form>

            </div>
        </div>

        <!-- RIGHT COLUMN: Dashboard & History Navigasi Soal (Desktop & Tablet) -->
        <div class="col-12 col-lg-4 d-none d-lg-block">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white sticky-top" style="top: 75px;">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-grid-3x3-gap-fill text-primary fs-5"></i>
                        <span>Navigasi & Progress</span>
                    </h6>
                    <span id="progressPercentBadge" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold">0% Selesai</span>
                </div>

                <!-- Progress Bar -->
                <div class="progress mb-3.5" style="height: 8px; background-color: #e2e8f0; border-radius: 10px;">
                    <div id="examProgressBar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%; border-radius: 10px;"></div>
                </div>

                <!-- History Progress Counters -->
                <div class="row g-2 mb-3.5 text-center">
                    <div class="col-4">
                        <div class="p-2 rounded-3 border" style="background-color: #ecfdf5; border-color: #a7f3d0 !important;">
                            <div class="fw-bold fs-4 lh-1 text-emerald-600 countDijawab" style="color: #059669;">0</div>
                            <small class="fw-bold text-emerald-700" style="font-size:0.7rem; color: #047857;">Dijawab</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded-3 border" style="background-color: #f8fafc; border-color: #cbd5e1 !important;">
                            <div class="fw-bold fs-4 lh-1 text-slate-700 countBelum" style="color: #475569;"><?= $totalSoal ?></div>
                            <small class="fw-bold text-slate-500" style="font-size:0.7rem; color: #64748b;">Belum</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded-3 border" style="background-color: #eff6ff; border-color: #bfdbfe !important;">
                            <div class="fw-bold fs-4 lh-1 text-blue-600 countTotal" style="color: #2563eb;"><?= $totalSoal ?></div>
                            <small class="fw-bold text-blue-700" style="font-size:0.7rem; color: #1d4ed8;">Total Soal</small>
                        </div>
                    </div>
                </div>

                <hr class="my-2 text-muted opacity-25">

                <!-- Interactive Question Palette Matrix Grid -->
                <label class="small text-slate-500 fw-bold mb-2">Matriks Lembar Soal:</label>
                <div class="d-flex flex-wrap gap-2 mb-4 justify-content-start" style="max-height: 280px; overflow-y: auto; padding: 2px;">
                    <?php for ($i = 0; $i < $totalSoal; $i++): ?>
                        <div id="qBtn_<?= $i ?>" class="q-grid-btn unanswered <?= $i === 0 ? 'active-q' : '' ?>" onclick="navSoal(<?= $i ?>)">
                            <?= $i + 1 ?>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="d-flex flex-column gap-2 pt-2 border-top">
                    <button type="button" class="btn btn-success w-100 fw-bold py-2.5 rounded-pill shadow-sm text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" onclick="confirmSubmitExam()">
                        <i class="bi bi-check-circle-fill me-1.5"></i> Selesaikan Ujian
                    </button>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- MOBILE BOTTOM STICKY BAR & OFFCANVAS NAVIGATOR -->
<div class="cbt-mobile-bar fixed-bottom d-lg-none py-2 px-3 z-3">
    <div class="d-flex align-items-center justify-content-between gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-3 d-flex align-items-center gap-1.5" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavMobile">
            <i class="bi bi-grid-3x3-gap-fill"></i>
            <span>Matriks Soal (<span class="countDijawab">0</span>/<?= $totalSoal ?>)</span>
        </button>

        <button type="button" class="btn btn-success btn-sm rounded-pill fw-bold px-3 text-white shadow-xs" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);" onclick="confirmSubmitExam()">
            <i class="bi bi-send-fill me-1"></i> Selesai
        </button>
    </div>
</div>

<!-- OFFCANVAS DRAWER FOR MOBILE QUESTION NAVIGATOR -->
<div class="offcanvas offcanvas-bottom rounded-top-4 h-75 d-lg-none" tabindex="-1" id="offcanvasNavMobile">
    <div class="offcanvas-header border-bottom py-3">
        <h6 class="offcanvas-title fw-bold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-grid-3x3-gap-fill text-primary"></i>
            <span>Navigasi Soal Ujian</span>
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-3">
        <div class="row g-2 mb-3 text-center">
            <div class="col-4">
                <div class="p-2 rounded-3 border bg-success-subtle text-success">
                    <div class="fw-bold fs-5 lh-1 countDijawab">0</div>
                    <small class="fw-semibold" style="font-size:0.68rem;">Dijawab</small>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2 rounded-3 border bg-light text-slate-700">
                    <div class="fw-bold fs-5 lh-1 countBelum"><?= $totalSoal ?></div>
                    <small class="fw-semibold text-muted" style="font-size:0.68rem;">Belum</small>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2 rounded-3 border bg-primary-subtle text-primary">
                    <div class="fw-bold fs-5 lh-1 countTotal"><?= $totalSoal ?></div>
                    <small class="fw-semibold" style="font-size:0.68rem;">Total</small>
                </div>
            </div>
        </div>

        <label class="small text-slate-500 fw-bold mb-2">Pilih Nomor Soal:</label>
        <div class="d-flex flex-wrap gap-2 justify-content-start mb-3" style="max-height: 250px; overflow-y: auto;">
            <?php for ($i = 0; $i < $totalSoal; $i++): ?>
                <div id="qBtnMob_<?= $i ?>" class="q-grid-btn unanswered <?= $i === 0 ? 'active-q' : '' ?>" onclick="navSoal(<?= $i ?>); closeMobileOffcanvas();">
                    <?= $i + 1 ?>
                </div>
            <?php endfor; ?>
        </div>

        <button type="button" class="btn btn-success w-100 fw-bold py-2.5 rounded-pill shadow-sm text-white" onclick="closeMobileOffcanvas(); confirmSubmitExam();">
            <i class="bi bi-check-circle-fill me-1.5"></i> Selesaikan Ujian Sekarang
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const quizId = '<?= $quiz_id ?>';

// 1. Instant LocalStorage Violation Check (Prevents Refresh Bypass)
if (localStorage.getItem('cbt_violation_locked_' + quizId) === '1') {
    window.location.href = '<?= BASE_URL ?>index.php?url=siswa/quiz';
}

let currentSoalIdx = 0;
const totalSoalCount = <?= $totalSoal ?>;
let durationSeconds = <?= $durasiMenit ?> * 60;
let timerInterval = null;
let warningCount = 0;
let isExamActive = false;

// 2. Restore saved warning count if student refreshed during exam
const savedWarns = sessionStorage.getItem('cbt_warning_count_' + quizId);
if (savedWarns) {
    warningCount = parseInt(savedWarns);
}

document.addEventListener('DOMContentLoaded', function() {
    // Update violation badge UI if restored
    document.getElementById('violationCount').textContent = warningCount;

    // Show Start Modal
    const startModal = new bootstrap.Modal(document.getElementById('startExamModal'));
    startModal.show();

    // Choice Selection Click Logic
    document.querySelectorAll('.cbt-choice-card').forEach(card => {
        card.addEventListener('click', function(e) {
            const radio = this.querySelector('input[type="radio"]');
            if (radio && !radio.checked) {
                radio.checked = true;
                const event = new Event('change', { bubbles: true });
                radio.dispatchEvent(event);
            }
        });
    });

    // Attach Option Card Highlight Logic
    document.querySelectorAll('input.input-jawaban').forEach(radio => {
        radio.addEventListener('change', function() {
            const card = this.closest('.soal-block');
            card.querySelectorAll('.cbt-choice-card').forEach(c => c.classList.remove('selected'));
            if (this.checked) {
                this.closest('.cbt-choice-card').classList.add('selected');
            }
        });
    });

    enableAntiCheating();
});

function startCBTEngine() {
    // Clear any previous violation locks for fresh new attempt session
    localStorage.removeItem('cbt_violation_locked_' + quizId);
    sessionStorage.removeItem('cbt_warning_count_' + quizId);
    warningCount = 0;
    if (document.getElementById('violationCount')) {
        document.getElementById('violationCount').textContent = 0;
    }

    isExamActive = true;
    const startModalElem = document.getElementById('startExamModal');
    const bsModal = bootstrap.Modal.getInstance(startModalElem);
    if (bsModal) bsModal.hide();

    // Enter Fullscreen
    requestFullscreenMode();

    // Start Timer
    const savedTime = sessionStorage.getItem('cbt_remaining_time_' + quizId);
    if (savedTime && parseInt(savedTime) > 0) {
        durationSeconds = parseInt(savedTime);
    }
    startTimer();
}

function requestFullscreenMode() {
    const elem = document.documentElement;
    if (elem.requestFullscreen) {
        elem.requestFullscreen().catch(err => console.log(err));
    } else if (elem.webkitRequestFullscreen) {
        elem.webkitRequestFullscreen();
    } else if (elem.msRequestFullscreen) {
        elem.msRequestFullscreen();
    }
}

function startTimer() {
    updateTimerDisplay();
    timerInterval = setInterval(() => {
        durationSeconds--;
        sessionStorage.setItem('cbt_remaining_time_' + quizId, durationSeconds);
        updateTimerDisplay();

        if (durationSeconds <= 0) {
            clearInterval(timerInterval);
            sessionStorage.removeItem('cbt_remaining_time_' + quizId);
            
            Swal.fire({
                icon: 'warning',
                title: 'Waktu Ujian Habis!',
                text: 'Jawaban Anda dikirim otomatis ke server.',
                confirmButtonText: 'OK',
                allowOutsideClick: false
            }).then(() => {
                submitExamForm();
            });
        }
    }, 1000);
}

function updateTimerDisplay() {
    const hours = Math.floor(durationSeconds / 3600);
    const minutes = Math.floor((durationSeconds % 3600) / 60);
    const seconds = durationSeconds % 60;

    const h = hours < 10 ? '0' + hours : hours;
    const m = minutes < 10 ? '0' + minutes : minutes;
    const s = seconds < 10 ? '0' + seconds : seconds;

    const displayElem = document.getElementById('timerDisplay');
    if (displayElem) displayElem.textContent = `${h}:${m}:${s}`;

    if (durationSeconds < 300) {
        displayElem.classList.remove('text-warning');
        displayElem.classList.add('text-danger', 'fw-black');
    }
}

function navSoal(targetIdx) {
    if (targetIdx < 0 || targetIdx >= totalSoalCount) return;

    // Hide Current Card
    const currCard = document.getElementById('soalCard_' + currentSoalIdx);
    const currBtn = document.getElementById('qBtn_' + currentSoalIdx);
    const currBtnMob = document.getElementById('qBtnMob_' + currentSoalIdx);
    if (currCard) currCard.classList.add('d-none');
    if (currBtn) currBtn.classList.remove('active-q');
    if (currBtnMob) currBtnMob.classList.remove('active-q');

    // Show Target Card
    currentSoalIdx = targetIdx;
    const targetCard = document.getElementById('soalCard_' + currentSoalIdx);
    const targetBtn = document.getElementById('qBtn_' + currentSoalIdx);
    const targetBtnMob = document.getElementById('qBtnMob_' + currentSoalIdx);
    if (targetCard) targetCard.classList.remove('d-none');
    if (targetBtn) targetBtn.classList.add('active-q');
    if (targetBtnMob) targetBtnMob.classList.add('active-q');

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function closeMobileOffcanvas() {
    const offcanvasElem = document.getElementById('offcanvasNavMobile');
    const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElem);
    if (bsOffcanvas) bsOffcanvas.hide();
}

function onAnswerSelected(idx) {
    const card = document.getElementById('soalCard_' + idx);
    if (!card) return;

    let isAnswered = false;
    const radioChecked = card.querySelector('input[type="radio"]:checked');
    const essayText = card.querySelector('textarea.input-essay');

    if (radioChecked) {
        isAnswered = true;
    } else if (essayText && essayText.value.trim() !== '') {
        isAnswered = true;
    }

    const qBtn = document.getElementById('qBtn_' + idx);
    const qBtnMob = document.getElementById('qBtnMob_' + idx);
    const statusTag = card.querySelector('.status-tag-' + idx);

    if (isAnswered) {
        if (qBtn) {
            qBtn.classList.remove('unanswered');
            qBtn.classList.add('answered');
        }
        if (qBtnMob) {
            qBtnMob.classList.remove('unanswered');
            qBtnMob.classList.add('answered');
        }
        if (statusTag) {
            statusTag.className = 'badge status-tag-' + idx + ' bg-success text-white px-2.5 py-1.5 rounded-pill fw-semibold';
            statusTag.innerHTML = '🟢 Sudah Dijawab';
        }
    } else {
        if (qBtn) {
            qBtn.classList.remove('answered');
            qBtn.classList.add('unanswered');
        }
        if (qBtnMob) {
            qBtnMob.classList.remove('answered');
            qBtnMob.classList.add('unanswered');
        }
        if (statusTag) {
            statusTag.className = 'badge status-tag-' + idx + ' bg-slate-100 text-slate-500 border px-2.5 py-1.5 rounded-pill fw-semibold';
            statusTag.style.background = '#f8fafc';
            statusTag.style.color = '#64748b';
            statusTag.innerHTML = '⚪ Belum Dijawab';
        }
    }

    updateAnswerCounters();
}

function updateAnswerCounters() {
    let answeredCount = 0;
    for (let i = 0; i < totalSoalCount; i++) {
        const card = document.getElementById('soalCard_' + i);
        if (card) {
            const radioChecked = card.querySelector('input[type="radio"]:checked');
            const essayText = card.querySelector('textarea.input-essay');
            if (radioChecked || (essayText && essayText.value.trim() !== '')) {
                answeredCount++;
            }
        }
    }

    const unanswerCount = totalSoalCount - answeredCount;
    document.querySelectorAll('.countDijawab').forEach(el => el.textContent = answeredCount);
    document.querySelectorAll('.countBelum').forEach(el => el.textContent = unanswerCount);

    // Update Progress Bar
    const pct = totalSoalCount > 0 ? Math.round((answeredCount / totalSoalCount) * 100) : 0;
    const pBar = document.getElementById('examProgressBar');
    const pBadge = document.getElementById('progressPercentBadge');
    if (pBar) pBar.style.width = pct + '%';
    if (pBadge) pBadge.textContent = pct + '% Selesai';
}

function confirmSubmitExam() {
    let answeredCount = 0;
    for (let i = 0; i < totalSoalCount; i++) {
        const card = document.getElementById('soalCard_' + i);
        if (card) {
            const radioChecked = card.querySelector('input[type="radio"]:checked');
            const essayText = card.querySelector('textarea.input-essay');
            if (radioChecked || (essayText && essayText.value.trim() !== '')) {
                answeredCount++;
            }
        }
    }
    const unanswerCount = totalSoalCount - answeredCount;

    let subMsg = `Anda telah menjawab ${answeredCount} dari ${totalSoalCount} soal.`;
    if (unanswerCount > 0) {
        subMsg += ` Masih ada ${unanswerCount} soal yang belum dijawab! Yakin ingin menyelesaikan ujian?`;
    }

    Swal.fire({
        icon: 'question',
        title: 'Selesaikan & Kirim Ujian?',
        text: subMsg,
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Kirim Sekarang',
        cancelButtonText: 'Batal / Cek Lagi'
    }).then((result) => {
        if (result.isConfirmed) {
            submitExamForm();
        }
    });
}

function submitExamForm() {
    sessionStorage.removeItem('cbt_remaining_time_' + quizId);
    sessionStorage.removeItem('cbt_warning_count_' + quizId);
    isExamActive = false;
    document.getElementById('formCBT').submit();
}

// --- STRICT ANTI-CHEATING & FULLSCREEN ENFORCEMENT ENGINE ---
function enableAntiCheating() {
    // 1. Right Click Prevention
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });

    // 2. Anti Copy / Cut / Paste
    document.addEventListener('copy', e => e.preventDefault());
    document.addEventListener('cut', e => e.preventDefault());
    document.addEventListener('paste', e => e.preventDefault());

    // 3. Anti Keyboard Shortcuts (F12, Ctrl+Shift+I, Ctrl+C, etc)
    document.addEventListener('keydown', function(e) {
        if (
            e.keyCode === 123 || // F12
            (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) || // Ctrl+Shift+I/J/C
            (e.ctrlKey && (e.keyCode === 85 || e.keyCode === 83 || e.keyCode === 67 || e.keyCode === 86)) // Ctrl+U/S/C/V
        ) {
            e.preventDefault();
            return false;
        }
    });

    // 4. Tab Switch & Fullscreen Departure Listener
    document.addEventListener('fullscreenchange', handleSecurityViolation);
    document.addEventListener('webkitfullscreenchange', handleSecurityViolation);
    document.addEventListener('visibilitychange', function() {
        if (document.hidden && isExamActive) {
            handleSecurityViolation();
        }
    });

    window.addEventListener('blur', function() {
        if (isExamActive) {
            handleSecurityViolation();
        }
    });
}

async function handleSecurityViolation() {
    if (!isExamActive) return;

    const isFull = document.fullscreenElement || document.webkitFullscreenElement;
    if (!isFull || document.hidden) {
        warningCount++;
        sessionStorage.setItem('cbt_warning_count_' + quizId, warningCount);
        document.getElementById('violationCount').textContent = warningCount;

        if (warningCount < 2) {
            Swal.fire({
                icon: 'warning',
                title: '⚠️ PERINGATAN KECURANGAN UJIAN (1/2)',
                html: '<strong class="text-danger">Anda terdeteksi keluar dari layar penuh / berpindah tab!</strong><br><br>Mohon kembali ke mode layar penuh. Jika Anda melanggar 1x lagi, <strong>ujian Anda akan DIBATALKAN otomatis oleh sistem!</strong>',
                confirmButtonText: 'Kembali Ke Fullscreen (Lanjutkan Ujian)',
                confirmButtonColor: '#e11d48',
                allowOutsideClick: false
            }).then(() => {
                requestFullscreenMode();
            });
        } else {
            // Cancel and Void Exam on 2nd Violation IMMEDIATELY!
            isExamActive = false;
            clearInterval(timerInterval);
            sessionStorage.removeItem('cbt_remaining_time_' + quizId);
            localStorage.setItem('cbt_violation_locked_' + quizId, '1');

            // Synchronous Await POST to record violation in database BEFORE displaying popup!
            const formData = new FormData();
            formData.append('action', 'record_violation');
            formData.append('quiz_id', quizId);

            try {
                await fetch('<?= BASE_URL ?>index.php?url=siswa/quiz', {
                    method: 'POST',
                    body: formData
                });
            } catch (err) {
                console.log(err);
            }

            Swal.fire({
                icon: 'error',
                title: '🚫 UJIAN DIBATALKAN & TERKUNCI PERMANEN!',
                html: '<strong class="text-danger">Anda telah melanggar aturan ujian online sebanyak 2 kali (keluar fullscreen/berpindah tab).</strong><br><br>Pengerjaan kuis Anda secara otomatis dibatalkan dan <strong>terkunci di database</strong>. Anda TIDAK BISA melanjutkan kuis ini meskipun di-refresh.<br><br>Silakan ajukan permohonan izin Ujian Susulan ke Guru Pengampu.',
                confirmButtonText: 'Kembali Ke Menu Quiz',
                confirmButtonColor: '#e11d48',
                allowOutsideClick: false
            }).then(() => {
                window.location.href = '<?= BASE_URL ?>index.php?url=siswa/quiz';
            });
        }
    }
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
