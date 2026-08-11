<?php
if (!class_exists('Security') && defined('ROOT_PATH') && file_exists(ROOT_PATH . 'helpers/Security.php')) {
    require_once ROOT_PATH . 'helpers/Security.php';
}
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/navbar.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';

$csrfTokenVal = (class_exists('Security') && method_exists('Security', 'generateCsrfToken')) ? Security::generateCsrfToken() : ($_SESSION['csrf_token'] ?? '');
$soalJsonData = json_encode($soalList ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
if (!$soalJsonData) $soalJsonData = '[]';
?>

<!-- Declare Game Engine & Window Helpers BEFORE HTML elements render -->
<script>
function toggleArenaFullscreen() {
    const arenaCard = document.getElementById('gameArenaCard');
    if (!arenaCard) return;

    try {
        if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
            if (arenaCard.requestFullscreen) {
                arenaCard.requestFullscreen().catch(() => {});
            } else if (arenaCard.webkitRequestFullscreen) {
                arenaCard.webkitRequestFullscreen();
            } else if (arenaCard.msRequestFullscreen) {
                arenaCard.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen().catch(() => {});
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    } catch(e) {}
}
window.toggleArenaFullscreen = toggleArenaFullscreen;

window.GameEngine = {
    data: {
        gameId: <?= (int)$game['id'] ?>,
        kkm: <?= (int)$game['kkm'] ?>,
        timerDuration: <?= (int)$game['durasi_per_soal'] ?>,
        questions: <?= $soalJsonData ?>,
        csrfToken: '<?= $csrfTokenVal ?>',
        baseUrl: '<?= BASE_URL ?>'
    },
    state: {
        currentIdx: 0,
        score: 0,
        combo: 0,
        maxCombo: 0,
        lives: 3,
        correctCount: 0,
        startTime: 0,
        timerInterval: null,
        timeLeft: 0,
        isAnswered: false,
        isEnded: false,
        isStarted: false
    },

    startArena: function() {
        if (this.state.isStarted) return;
        this.state.isStarted = true;

        // Trigger Fullscreen
        window.toggleArenaFullscreen();

        // Switch screens
        const overlay = document.getElementById('startScreenOverlay');
        const timerBox = document.getElementById('timerBarContainer');
        const quizBox = document.getElementById('quizBoxContainer');

        if (overlay) overlay.classList.add('d-none');
        if (timerBox) timerBox.classList.remove('d-none');
        if (quizBox) quizBox.classList.remove('d-none');

        this.init();
    },

    init: function() {
        try {
            this.state.startTime = Date.now();
            this.renderQuestion();
        } catch (err) {
            console.error('GameEngine Init Error:', err);
            const textEl = document.getElementById('questionText');
            if (textEl) textEl.textContent = 'Memulai arena permainan...';
            setTimeout(() => { this.renderQuestion(); }, 150);
        }
    },

    // Synthetic Sound Effect (Web Audio API)
    playSound: function(type) {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);

            if (type === 'correct') {
                osc.frequency.setValueAtTime(523.25, ctx.currentTime); // C5
                osc.frequency.setValueAtTime(659.25, ctx.currentTime + 0.1); // E5
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                osc.start();
                osc.stop(ctx.currentTime + 0.25);
            } else if (type === 'wrong') {
                osc.frequency.setValueAtTime(220, ctx.currentTime); // A3
                osc.frequency.setValueAtTime(164.81, ctx.currentTime + 0.1); // E3
                gain.gain.setValueAtTime(0.25, ctx.currentTime);
                osc.start();
                osc.stop(ctx.currentTime + 0.3);
            }
        } catch(e) {}
    },

    renderQuestion: function() {
        if (!this.data.questions || !Array.isArray(this.data.questions) || this.data.questions.length === 0) {
            document.getElementById('questionCounter').textContent = "Informasi Game";
            document.getElementById('questionText').innerHTML = "<div class='alert alert-warning text-dark border-0 rounded-4 p-4 my-2'><i class='bi bi-info-circle-fill me-2'></i>Bank soal untuk game ini sedang disiapkan oleh Guru Pengampu. Silakan coba game lainnya.</div>";
            document.getElementById('optionsContainer').innerHTML = "";
            return;
        }

        if (this.state.currentIdx >= this.data.questions.length || this.state.lives <= 0) {
            this.endGame();
            return;
        }

        this.state.isAnswered = false;
        const q = this.data.questions[this.state.currentIdx];

        document.getElementById('questionCounter').textContent = `Soal ${this.state.currentIdx + 1} dari ${this.data.questions.length}`;
        document.getElementById('questionText').textContent = q.pertanyaan;

        const optionsHtml = ['a', 'b', 'c', 'd'].map(opt => {
            const text = q['opsi_' + opt];
            if (!text) return '';
            const safeText = String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            return `
                <div class="col-12 col-md-6">
                    <button type="button" class="btn btn-outline-light w-100 p-3 rounded-4 text-start d-flex align-items-center gap-3 option-btn shadow-sm" onclick="window.GameEngine.checkAnswer('${opt}')">
                        <span class="rounded-circle bg-primary bg-gradient text-white d-flex align-items-center justify-content-center fw-bold fs-6" style="width: 38px; height: 38px; min-width: 38px;">
                            ${opt.toUpperCase()}
                        </span>
                        <span class="fw-semibold text-white fs-6 flex-grow-1">${safeText}</span>
                    </button>
                </div>
            `;
        }).join('');

        document.getElementById('optionsContainer').innerHTML = optionsHtml;

        this.startTimer();
    },

    startTimer: function() {
        clearInterval(this.state.timerInterval);
        this.state.timeLeft = this.data.timerDuration;
        const timerBar = document.getElementById('gameTimerBar');

        this.state.timerInterval = setInterval(() => {
            this.state.timeLeft -= 0.1;
            const pct = Math.max(0, (this.state.timeLeft / this.data.timerDuration) * 100);
            if (timerBar) timerBar.style.width = pct + '%';

            if (this.state.timeLeft <= 0) {
                clearInterval(this.state.timerInterval);
                this.handleTimeout();
            }
        }, 100);
    },

    checkAnswer: function(selectedOpt) {
        if (this.state.isAnswered) return;
        this.state.isAnswered = true;
        clearInterval(this.state.timerInterval);

        const q = this.data.questions[this.state.currentIdx];
        const keyAnswer = (q.kunci_jawaban || 'a').toString().trim().toLowerCase();
        const isCorrect = (selectedOpt.toLowerCase() === keyAnswer);

        if (isCorrect) {
            this.playSound('correct');
            this.state.combo++;
            if (this.state.combo > this.state.maxCombo) this.state.maxCombo = this.state.combo;
            this.state.correctCount++;

            const comboMultiplier = Math.min(5, Math.floor(this.state.combo / 2) + 1);
            const timeBonus = Math.floor(this.state.timeLeft * 2);
            const pointsGained = (parseInt(q.poin) || 10) * comboMultiplier + timeBonus;

            this.state.score += pointsGained;

            this.showFeedback(true, `Benar! +${pointsGained} Poin`, `${this.state.combo}x Combo Streak! 🔥`);
        } else {
            this.playSound('wrong');
            this.state.combo = 0;
            this.state.lives--;

            this.showFeedback(false, `Jawaban Salah!`, `Kunci Jawaban: Opsi ${q.kunci_jawaban.toUpperCase()}`);
        }

        this.updateHUD();

        setTimeout(() => {
            this.state.currentIdx++;
            this.renderQuestion();
        }, 1400);
    },

    handleTimeout: function() {
        if (this.state.isAnswered) return;
        this.state.isAnswered = true;
        this.playSound('wrong');

        this.state.combo = 0;
        this.state.lives--;
        this.updateHUD();

        const q = this.data.questions[this.state.currentIdx];
        this.showFeedback(false, `Waktu Habis! ⏱️`, `Kunci Jawaban: Opsi ${q.kunci_jawaban.toUpperCase()}`);

        setTimeout(() => {
            this.state.currentIdx++;
            this.renderQuestion();
        }, 1400);
    },

    updateHUD: function() {
        const scoreEl = document.getElementById('currentScore');
        const comboEl = document.getElementById('comboBadge');
        const livesEl = document.getElementById('livesContainer');

        if (scoreEl) scoreEl.textContent = this.state.score;
        if (comboEl) comboEl.textContent = `${this.state.combo}x 🔥`;

        let hearts = '';
        for (let i = 0; i < 3; i++) {
            hearts += (i < this.state.lives) ? '❤️' : '🖤';
        }
        if (livesEl) livesEl.textContent = hearts;
    },

    showFeedback: function(isSuccess, title, desc) {
        const banner = document.getElementById('feedbackBanner');
        const icon = document.getElementById('feedbackIcon');
        const titleElem = document.getElementById('feedbackTitle');
        const descElem = document.getElementById('feedbackDesc');

        if (!banner) return;

        banner.className = `alert position-absolute top-50 start-50 translate-middle shadow-lg rounded-4 text-center p-4 ${isSuccess ? 'alert-success border-success' : 'alert-danger border-danger'}`;
        icon.textContent = isSuccess ? '🎉' : '❌';
        titleElem.textContent = title;
        descElem.textContent = desc;

        banner.classList.remove('d-none');
        setTimeout(() => banner.classList.add('d-none'), 1200);
    },

    endGame: function() {
        if (this.state.isEnded) return;
        this.state.isEnded = true;
        clearInterval(this.state.timerInterval);

        // Auto exit fullscreen on game completion
        try {
            if (document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(() => {});
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }
        } catch(e) {}

        const elapsedTime = Math.round((Date.now() - this.state.startTime) / 1000);
        const isPassed = (this.state.score >= this.data.kkm);

        document.getElementById('endScoreVal').textContent = this.state.score;
        document.getElementById('endComboVal').textContent = `${this.state.maxCombo}x 🔥`;
        document.getElementById('endCorrectVal').textContent = `${this.state.correctCount} / ${this.data.questions.length}`;

        const statusElem = document.getElementById('endStatusVal');
        statusElem.textContent = isPassed ? 'LULUS 🎉' : 'TIDAK LULUS ❌';
        statusElem.className = isPassed ? 'text-success fw-bold' : 'text-danger fw-bold';

        let stars = '⭐';
        if (this.state.score >= this.data.kkm * 1.2) stars = '⭐⭐⭐';
        else if (isPassed) stars = '⭐⭐';
        document.getElementById('endGameStars').textContent = stars;

        // Save Score via AJAX
        const formData = new FormData();
        formData.append('game_id', this.data.gameId);
        formData.append('skor_akhir', this.state.score);
        formData.append('max_combo', this.state.maxCombo);
        formData.append('total_benar', this.state.correctCount);
        formData.append('total_soal', this.data.questions.length);
        formData.append('waktu_selesai', elapsedTime);
        formData.append('status_lulus', isPassed ? 'lulus' : 'tidak_lulus');
        formData.append('csrf_token', this.data.csrfToken);

        fetch(`${this.data.baseUrl}index.php?url=game/saveScore`, {
            method: 'POST',
            body: formData
        }).catch(() => {});

        const modalEl = document.getElementById('modalEndGame');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }
};

window.startGameArena = function() {
    if (window.GameEngine) {
        window.GameEngine.startArena();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const btnStart = document.getElementById('btnStartGame');
    if (btnStart) {
        btnStart.addEventListener('click', function(e) {
            e.preventDefault();
            window.startGameArena();
        });
    }
});
</script>

<main class="main-content px-2 px-md-4 py-3">
    <div class="container-fluid">
        <!-- Top Navigation Bar -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <a href="<?= BASE_URL ?>index.php?url=game" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Keluar Arena Game
            </a>
            <button type="button" class="btn btn-outline-warning rounded-pill px-4 py-2 fw-bold text-dark shadow-sm hover-scale" onclick="window.toggleArenaFullscreen()" id="btnFullscreenHeader">
                <i class="bi bi-arrows-fullscreen me-1"></i> Mode Layar Penuh (Fullscreen 🚀)
            </button>
        </div>

        <!-- Game Arena Card Container -->
        <div class="card card-custom p-3 p-md-5 mb-4 shadow-lg border-0 rounded-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%); color: white; min-height: 520px;" id="gameArenaCard">

            <!-- Arena Header Bar -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 pb-3 border-bottom border-secondary border-opacity-50">
                <div>
                    <?php if (strtolower(trim($_SESSION['user']['role_name'] ?? '')) === 'guru'): ?>
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill small mb-1 d-inline-block fw-bold shadow-sm">
                            <i class="bi bi-eye-fill me-1"></i> Mode Pratinjau Guru (Uji Coba Arena)
                        </span>
                    <?php endif; ?>
                    <h4 class="fw-bold mb-0 text-warning d-flex align-items-center gap-2">
                        <i class="bi bi-controller text-danger"></i> <?= htmlspecialchars($game['judul']) ?>
                    </h4>
                    <small class="text-white-50 fs-6"><?= htmlspecialchars($game['nama_mapel']) ?> | Target KKM: <strong><?= $game['kkm'] ?> Poin</strong></small>
                </div>

                <!-- HUD Status Badges -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- Nyawa / Lives -->
                    <div class="bg-black bg-opacity-50 px-3 py-2 rounded-pill d-flex align-items-center gap-1 border border-danger border-opacity-50 shadow-sm">
                        <small class="text-white-50 me-1 d-none d-sm-inline">Nyawa:</small>
                        <span id="livesContainer" class="fs-5">❤️❤️❤️</span>
                    </div>

                    <!-- Combo Streak -->
                    <div class="bg-black bg-opacity-50 px-3 py-2 rounded-pill d-flex align-items-center gap-1 border border-warning border-opacity-50 shadow-sm">
                        <small class="text-white-50 me-1 d-none d-sm-inline">Combo:</small>
                        <span id="comboBadge" class="fw-bold text-warning fs-6">1x 🔥</span>
                    </div>

                    <!-- Score Badge -->
                    <div class="bg-primary bg-gradient px-3 px-sm-4 py-2 rounded-pill shadow border border-primary border-opacity-50">
                        <small class="text-white-50 me-1">Skor:</small>
                        <span id="currentScore" class="fw-bold text-white fs-5">0</span>
                    </div>
                </div>
            </div>

            <!-- Start Screen Overlay Container (Initial State) -->
            <div id="startScreenOverlay" class="text-center py-4 px-2">
                <div class="mb-3">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold fs-6 shadow">
                        <i class="bi bi-controller me-1"></i> ARENA KUIS SIAP DIMULAI
                    </span>
                </div>
                <h2 class="fw-bold text-white mb-2 display-6"><?= htmlspecialchars($game['judul']) ?></h2>
                <p class="text-white-50 max-w-xl mx-auto mb-4 fs-6">
                    Mata Pelajaran: <strong><?= htmlspecialchars($game['nama_mapel']) ?></strong> | Target KKM: <strong><?= $game['kkm'] ?> Poin</strong>
                </p>

                <!-- Game Rules Info Box -->
                <div class="row g-3 justify-content-center max-w-2xl mx-auto mb-4 text-start">
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-10 text-center">
                            <div class="fs-3 mb-1">❤️❤️❤️</div>
                            <small class="text-white-50 d-block">Kesempatan</small>
                            <span class="fw-bold text-white">3 Nyawa Permainan</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-10 text-center">
                            <div class="fs-3 mb-1">⏱️ <?= $game['durasi_per_soal'] ?>s</div>
                            <small class="text-white-50 d-block">Timer per Soal</small>
                            <span class="fw-bold text-warning"><?= $game['durasi_per_soal'] ?> Detik / Soal</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-10 text-center">
                            <div class="fs-3 mb-1">🔥 5x</div>
                            <small class="text-white-50 d-block">Pengganda Skor</small>
                            <span class="fw-bold text-info">Combo Streak Bonus</span>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-warning btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg text-dark fs-4 hover-scale" id="btnStartGame" onclick="window.startGameArena()">
                    <i class="bi bi-play-circle-fill me-2 fs-3"></i> MULAI PERMAINAN (FULLSCREEN 🚀)
                </button>
            </div>

            <!-- Timer Progress Bar Box (Hidden Initially) -->
            <div id="timerBarContainer" class="progress bg-secondary bg-opacity-25 rounded-pill mb-4 d-none" style="height: 14px;">
                <div id="gameTimerBar" class="progress-bar bg-warning progress-bar-striped progress-bar-animated rounded-pill" role="progressbar" style="width: 100%;"></div>
            </div>

            <!-- Question Card Box (Hidden Initially) -->
            <div id="quizBoxContainer" class="text-center py-2 py-md-4 px-1 px-md-3 d-none">
                <div class="mb-3">
                    <span class="badge bg-danger bg-opacity-90 text-white px-3 py-2 rounded-pill fw-bold fs-6 shadow-sm" id="questionCounter">Soal 1 dari <?= count($soalList) ?></span>
                </div>

                <h3 class="fw-bold text-white mb-4 px-md-4 fs-3 fs-md-2" id="questionText" style="line-height: 1.4; word-break: break-word;">
                    Loading Pertanyaan...
                </h3>

                <!-- Multiple Choice Options Grid -->
                <div class="row g-3 max-w-2xl mx-auto text-start" id="optionsContainer">
                    <!-- Dynamic Answer Options -->
                </div>
            </div>

            <!-- Feedback Popup Banner -->
            <div id="feedbackBanner" class="alert position-absolute top-50 start-50 translate-middle shadow-lg rounded-4 text-center p-4 d-none" style="min-width: 290px; max-width: 90%; z-index: 1050; backdrop-filter: blur(8px);">
                <div id="feedbackIcon" class="display-3 mb-2"></div>
                <h4 id="feedbackTitle" class="fw-bold mb-1"></h4>
                <p id="feedbackDesc" class="small mb-0"></p>
            </div>
        </div>
    </div>
</main>

<!-- Modal End Game Victory / Defeat -->
<div class="modal fade" id="modalEndGame" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg text-center p-4">
            <div class="modal-body p-3 p-md-4">
                <div id="endGameIcon" class="display-1 mb-2">🏆</div>
                <h3 id="endGameTitle" class="fw-bold text-dark mb-1">Permainan Selesai!</h3>
                <div id="endGameStars" class="fs-2 text-warning mb-3">⭐⭐⭐</div>

                <div class="p-3 bg-light rounded-4 mb-4">
                    <div class="row g-2 text-center">
                        <div class="col-6 border-end">
                            <small class="text-muted d-block">Skor Akhir</small>
                            <span class="fw-bold fs-3 text-primary" id="endScoreVal">0</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Max Combo Streak</small>
                            <span class="fw-bold fs-3 text-warning" id="endComboVal">0x 🔥</span>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Total Benar: <strong class="text-dark" id="endCorrectVal">0</strong></span>
                        <span>Status: <strong id="endStatusVal">LULUS</strong></span>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>index.php?url=game/play&id=<?= $game['id'] ?>" class="btn btn-outline-danger rounded-pill w-100 py-2 fw-bold">
                        <i class="bi bi-arrow-repeat me-1"></i> Main Lagi
                    </a>
                    <a href="<?= BASE_URL ?>index.php?url=game/leaderboard&id=<?= $game['id'] ?>" class="btn btn-warning rounded-pill w-100 py-2 fw-bold shadow">
                        <i class="bi bi-trophy-fill me-1"></i> Peringkat
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
