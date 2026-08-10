<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">
        <a href="<?= BASE_URL ?>index.php?url=game" class="btn btn-outline-secondary mb-3 rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Keluar Arena
        </a>

        <!-- Game Arena Card Container -->
        <div class="card card-custom p-4 p-md-5 mb-4 shadow-lg border-0 rounded-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white;" id="gameArenaCard">

            <!-- Arena Header Bar -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 pb-3 border-bottom border-secondary border-opacity-50">
                <div>
                    <h5 class="fw-bold mb-0 text-warning"><i class="bi bi-controller me-2"></i><?= htmlspecialchars($game['judul']) ?></h5>
                    <small class="text-white-50"><?= htmlspecialchars($game['nama_mapel']) ?> | KKM Target: <?= $game['kkm'] ?> Poin</small>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <!-- Nyawa / Lives -->
                    <div class="bg-black bg-opacity-40 px-3 py-1 rounded-pill d-flex align-items-center gap-1 border border-danger">
                        <span class="small text-white-50 me-1">Nyawa:</span>
                        <span id="livesContainer" class="fs-5">❤️❤️❤️</span>
                    </div>

                    <!-- Combo Streak -->
                    <div class="bg-black bg-opacity-40 px-3 py-1 rounded-pill d-flex align-items-center gap-1 border border-warning">
                        <span class="small text-white-50 me-1">Combo:</span>
                        <span id="comboBadge" class="fw-bold text-warning fs-6">1x 🔥</span>
                    </div>

                    <!-- Score Badge -->
                    <div class="bg-primary bg-gradient px-4 py-1 rounded-pill shadow">
                        <span class="small text-white-50 me-1">Skor:</span>
                        <span id="currentScore" class="fw-bold text-white fs-5">0</span>
                    </div>
                </div>
            </div>

            <!-- Timer Progress Bar -->
            <div class="progress bg-secondary bg-opacity-25 rounded-pill mb-4" style="height: 12px;">
                <div id="gameTimerBar" class="progress-bar bg-warning progress-bar-striped progress-bar-animated rounded-pill" role="progressbar" style="width: 100%;"></div>
            </div>

            <!-- Question Card Box -->
            <div id="quizBoxContainer" class="text-center py-4 px-2">
                <div class="mb-2">
                    <span class="badge bg-danger bg-opacity-75 text-white px-3 py-1 rounded-pill fw-semibold" id="questionCounter">Soal 1 dari <?= count($soalList) ?></span>
                </div>
                <h3 class="fw-bold text-white mb-4 px-md-5" id="questionText" style="line-height: 1.4;">
                    Loading Pertanyaan...
                </h3>

                <!-- Multiple Choice Options -->
                <div class="row g-3 max-w-2xl mx-auto text-start" id="optionsContainer">
                    <!-- Dynamic Answer Options -->
                </div>
            </div>

            <!-- Feedback Popup Banner -->
            <div id="feedbackBanner" class="alert position-absolute top-50 start-50 translate-middle shadow-lg rounded-4 text-center p-4 d-none" style="min-width: 320px; z-index: 1050;">
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
            <div class="modal-body p-4">
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

<script>
const GameEngine = {
    data: {
        gameId: <?= $game['id'] ?>,
        kkm: <?= $game['kkm'] ?>,
        timerDuration: <?= $game['durasi_per_soal'] ?>,
        questions: <?= json_encode($soalList) ?>,
        csrfToken: '<?= Security::csrfToken() ?>',
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
        isAnswered: false
    },

    init: function() {
        this.state.startTime = Date.now();
        this.renderQuestion();
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
            return `
                <div class="col-12 col-md-6">
                    <button type="button" class="btn btn-outline-light w-100 p-3 rounded-4 text-start d-flex align-items-center gap-3 option-btn shadow-sm" onclick="GameEngine.checkAnswer('${opt}')">
                        <span class="rounded-circle bg-primary bg-gradient text-white d-flex align-items-center justify-content-center fw-bold fs-6" style="width: 38px; height: 38px; min-width: 38px;">
                            ${opt.toUpperCase()}
                        </span>
                        <span class="fw-semibold text-white fs-6">${text}</span>
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
            timerBar.style.width = pct + '%';

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
        const isCorrect = (selectedOpt.toLowerCase() === q.kunci_jawaban.toLowerCase());

        if (isCorrect) {
            this.playSound('correct');
            this.state.combo++;
            if (this.state.combo > this.state.maxCombo) this.state.maxCombo = this.state.combo;
            this.state.correctCount++;

            const comboMultiplier = Math.min(5, Math.floor(this.state.combo / 2) + 1);
            const timeBonus = Math.floor(this.state.timeLeft * 2);
            const pointsGained = (q.poin || 10) * comboMultiplier + timeBonus;

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
        }, 1600);
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
        }, 1600);
    },

    updateHUD: function() {
        document.getElementById('currentScore').textContent = this.state.score;
        document.getElementById('comboBadge').textContent = `${this.state.combo}x 🔥`;

        let hearts = '';
        for (let i = 0; i < 3; i++) {
            hearts += (i < this.state.lives) ? '❤️' : '🖤';
        }
        document.getElementById('livesContainer').textContent = hearts;
    },

    showFeedback: function(isSuccess, title, desc) {
        const banner = document.getElementById('feedbackBanner');
        const icon = document.getElementById('feedbackIcon');
        const titleElem = document.getElementById('feedbackTitle');
        const descElem = document.getElementById('feedbackDesc');

        banner.className = `alert position-absolute top-50 start-50 translate-middle shadow-lg rounded-4 text-center p-4 ${isSuccess ? 'alert-success border-success' : 'alert-danger border-danger'}`;
        icon.textContent = isSuccess ? '🎉' : '❌';
        titleElem.textContent = title;
        descElem.textContent = desc;

        banner.classList.remove('d-none');
        setTimeout(() => banner.classList.add('d-none'), 1400);
    },

    endGame: function() {
        clearInterval(this.state.timerInterval);
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

        const modal = new bootstrap.Modal(document.getElementById('modalEndGame'));
        modal.show();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    GameEngine.init();
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
