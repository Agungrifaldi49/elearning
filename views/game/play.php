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
$gameType = $game['tipe_game'] ?? 'mario_run';
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
        gameType: '<?= htmlspecialchars($gameType) ?>',
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
        coins: 0,
        stamina: 100,
        startTime: 0,
        timerInterval: null,
        marioLoopInterval: null,
        wheelAngle: 0,
        isWheelSpinning: false,
        timeLeft: 0,
        isAnswered: false,
        isEnded: false,
        isStarted: false,
        isMarioRunning: false,
        marioX: 50,
        marioY: 210,
        marioVy: 0,
        isJumping: false,
        bgOffset: 0,
        flippedCards: [],
        matchedPairs: 0
    },

    startArena: function() {
        if (this.state.isStarted) return;
        this.state.isStarted = true;

        window.toggleArenaFullscreen();

        const overlay = document.getElementById('startScreenOverlay');
        const timerBox = document.getElementById('timerBarContainer');
        const quizBox = document.getElementById('quizBoxContainer');

        const marioBox = document.getElementById('marioStageContainer');
        const speedBox = document.getElementById('speedStageContainer');
        const wheelBox = document.getElementById('spinWheelStageContainer');
        const memoryBox = document.getElementById('memoryStageContainer');

        if (overlay) overlay.classList.add('d-none');

        // Route to distinct visual stage based on gameType
        if (this.data.gameType === 'mario_run') {
            if (marioBox) marioBox.classList.remove('d-none');
            this.initMarioCanvas();
        } else if (this.data.gameType === 'spin_wheel') {
            if (wheelBox) wheelBox.classList.remove('d-none');
            this.initSpinWheelCanvas();
        } else if (this.data.gameType === 'memory_match') {
            if (memoryBox) memoryBox.classList.remove('d-none');
            this.initMemoryGrid();
        } else {
            // Default or quiz_speed Mode
            if (speedBox) speedBox.classList.remove('d-none');
            if (timerBox) timerBox.classList.remove('d-none');
            if (quizBox) quizBox.classList.remove('d-none');
        }

        this.init();
    },

    init: function() {
        try {
            this.state.startTime = Date.now();
            if (this.data.gameType === 'mario_run') {
                this.startMarioRun();
            } else if (this.data.gameType === 'spin_wheel') {
                this.drawSpinWheel();
            } else if (this.data.gameType === 'memory_match') {
                // Handled in initMemoryGrid
            } else {
                this.renderQuestion();
            }
        } catch (err) {
            console.error('GameEngine Init Error:', err);
            const textEl = document.getElementById('questionText');
            if (textEl) textEl.textContent = 'Memulai arena permainan...';
            setTimeout(() => { this.renderQuestion(); }, 150);
        }
    },

    // 🍄 MODE 1: SUPER MARIO PLATFORM RUNNER ENGINE
    initMarioCanvas: function() {
        const canvas = document.getElementById('marioCanvas');
        if (!canvas) return;
        this.canvasCtx = canvas.getContext('2d');

        window.addEventListener('keydown', (e) => {
            if (e.code === 'Space' || e.code === 'ArrowUp') {
                e.preventDefault();
                this.marioJump();
            }
        });
    },

    startMarioRun: function() {
        this.state.isMarioRunning = true;
        this.updateStaminaHUD(100);

        if (this.state.marioLoopInterval) clearInterval(this.state.marioLoopInterval);

        this.state.marioLoopInterval = setInterval(() => {
            if (!this.state.isMarioRunning || this.state.isEnded) return;

            this.state.stamina -= 0.35;
            if (this.state.stamina <= 0) {
                this.state.stamina = 0;
                this.triggerMarioQuestionCheckpoint('stamina_empty');
            }

            this.updateStaminaHUD(this.state.stamina);
            this.drawMarioCanvas();
        }, 1000 / 30);
    },

    marioJump: function() {
        if (!this.state.isStarted || this.state.isEnded) return;
        if (!this.state.isJumping) {
            this.state.isJumping = true;
            this.state.marioVy = -13;
            this.playSound('jump');

            this.state.coins += 1;
            this.state.score += 5;
            this.updateHUD();
        }
    },

    drawMarioCanvas: function() {
        const ctx = this.canvasCtx;
        if (!ctx) return;

        const w = 800;
        const h = 320;

        const skyGrad = ctx.createLinearGradient(0, 0, 0, h);
        skyGrad.addColorStop(0, '#5c94fc');
        skyGrad.addColorStop(1, '#89cff0');
        ctx.fillStyle = skyGrad;
        ctx.fillRect(0, 0, w, h);

        this.state.bgOffset += 2;
        ctx.fillStyle = 'rgba(255, 255, 255, 0.85)';
        for (let i = 0; i < 4; i++) {
            let cx = ((i * 240) - (this.state.bgOffset * 0.5)) % (w + 100);
            if (cx < -100) cx += w + 200;
            ctx.beginPath();
            ctx.arc(cx, 50, 22, 0, Math.PI * 2);
            ctx.arc(cx + 20, 45, 28, 0, Math.PI * 2);
            ctx.arc(cx + 42, 50, 22, 0, Math.PI * 2);
            ctx.fill();
        }

        ctx.fillStyle = '#38b000';
        for (let i = 0; i < 3; i++) {
            let hx = ((i * 350) - (this.state.bgOffset * 0.8)) % (w + 150);
            if (hx < -150) hx += w + 300;
            ctx.beginPath();
            ctx.arc(hx, 250, 70, Math.PI, 0);
            ctx.fill();
        }

        const groundY = 240;
        ctx.fillStyle = '#d04648';
        ctx.fillRect(0, groundY, w, h - groundY);
        ctx.fillStyle = '#e56b6f';
        ctx.fillRect(0, groundY, w, 8);

        let boxX = ((w * 0.7) - (this.state.bgOffset % w));
        if (boxX < -50) boxX += w;
        ctx.fillStyle = '#ffb703';
        ctx.fillRect(boxX, 150, 36, 36);
        ctx.fillStyle = '#000000';
        ctx.font = 'bold 20px monospace';
        ctx.fillText('?', boxX + 12, 175);

        if (this.state.isJumping) {
            this.state.marioY += this.state.marioVy;
            this.state.marioVy += 0.85;
            if (this.state.marioY >= 210) {
                this.state.marioY = 210;
                this.state.isJumping = false;
                this.state.marioVy = 0;
            }
        }

        const mx = this.state.marioX;
        const my = this.state.marioY;

        ctx.fillStyle = '#e63946';
        ctx.fillRect(mx + 6, my - 30, 20, 8);
        ctx.fillRect(mx + 8, my - 16, 16, 16);

        ctx.fillStyle = '#ffb703';
        ctx.fillRect(mx + 8, my - 22, 16, 8);

        ctx.fillStyle = '#1d3557';
        ctx.fillRect(mx + 6, my - 8, 20, 14);

        ctx.fillStyle = '#4a2810';
        let legOffset = Math.sin(this.state.bgOffset * 0.2) * 4;
        ctx.fillRect(mx + 4 + legOffset, my + 6, 10, 6);
        ctx.fillRect(mx + 16 - legOffset, my + 6, 10, 6);
    },

    triggerMarioQuestionCheckpoint: function(reason) {
        if (!this.state.isMarioRunning) return;
        this.state.isMarioRunning = false;

        const quizModalEl = document.getElementById('modalMarioQuiz');
        if (quizModalEl) {
            const modal = new bootstrap.Modal(quizModalEl);
            this.renderMarioModalQuestion();
            modal.show();
        } else {
            const quizBox = document.getElementById('quizBoxContainer');
            if (quizBox) quizBox.classList.remove('d-none');
            this.renderQuestion();
        }
    },

    renderMarioModalQuestion: function() {
        if (this.state.currentIdx >= this.data.questions.length || this.state.lives <= 0) {
            const modalEl = document.getElementById('modalMarioQuiz');
            if (modalEl) {
                const instance = bootstrap.Modal.getInstance(modalEl);
                if (instance) instance.hide();
            }
            this.endGame();
            return;
        }

        const q = this.data.questions[this.state.currentIdx];
        const counterEl = document.getElementById('marioModalCounter');
        const questionEl = document.getElementById('marioModalQuestion');
        const optionsEl = document.getElementById('marioModalOptions');

        if (counterEl) counterEl.textContent = `Tantangan Kuis Checkpoint #${this.state.currentIdx + 1} dari ${this.data.questions.length}`;
        if (questionEl) questionEl.textContent = q.pertanyaan;

        if (optionsEl) {
            optionsEl.innerHTML = ['a', 'b', 'c', 'd'].map(opt => {
                const text = q['opsi_' + opt];
                if (!text) return '';
                const safeText = String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                return `
                    <div class="col-12 col-md-6">
                        <button type="button" class="btn btn-outline-warning w-100 p-3 rounded-4 text-start d-flex align-items-center gap-3 option-btn shadow-sm text-white" onclick="window.GameEngine.submitMarioAnswer('${opt}')">
                            <span class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold fs-6" style="width: 38px; height: 38px; min-width: 38px;">
                                ${opt.toUpperCase()}
                            </span>
                            <span class="fw-semibold text-white fs-6 flex-grow-1">${safeText}</span>
                        </button>
                    </div>
                `;
            }).join('');
        }
    },

    submitMarioAnswer: function(selectedOpt) {
        if (this.state.isAnswered) return;
        this.state.isAnswered = true;

        const q = this.data.questions[this.state.currentIdx];
        const keyAnswer = (q.kunci_jawaban || 'a').toString().trim().toLowerCase();
        const isCorrect = (selectedOpt.toLowerCase() === keyAnswer);

        if (isCorrect) {
            this.playSound('correct');
            this.state.combo++;
            if (this.state.combo > this.state.maxCombo) this.state.maxCombo = this.state.combo;
            this.state.correctCount++;

            this.state.stamina = 100;
            this.updateStaminaHUD(100);

            const pointsGained = (parseInt(q.poin) || 10) + 20;
            this.state.score += pointsGained;
            this.updateHUD();

            this.showFeedback(true, '🎉 JAWABAN BENAR!', '⚡ STAMINA REFILLED 100% FULL! MARIO KEMBALI BERLARI! 🚀');
        } else {
            this.playSound('wrong');
            this.state.combo = 0;
            this.state.lives--;
            this.state.stamina = 25;
            this.updateStaminaHUD(25);
            this.updateHUD();

            this.showFeedback(false, '❌ JAWABAN SALAH', `Kunci Jawaban: Opsi ${q.kunci_jawaban.toUpperCase()}`);
        }

        setTimeout(() => {
            this.state.currentIdx++;
            this.state.isAnswered = false;

            const modalEl = document.getElementById('modalMarioQuiz');
            if (modalEl) {
                const instance = bootstrap.Modal.getInstance(modalEl);
                if (instance) instance.hide();
            }

            if (this.state.currentIdx >= this.data.questions.length || this.state.lives <= 0) {
                this.endGame();
            } else {
                this.startMarioRun();
            }
        }, 1500);
    },

    // 🎡 MODE 3: SPIN WHEEL QUIZ ENGINE
    initSpinWheelCanvas: function() {
        const canvas = document.getElementById('wheelCanvas');
        if (!canvas) return;
        this.wheelCtx = canvas.getContext('2d');
        this.drawSpinWheel();
    },

    drawSpinWheel: function() {
        const ctx = this.wheelCtx;
        if (!ctx) return;

        const cx = 200;
        const cy = 200;
        const radius = 170;
        const slices = [
            { label: '🎯 Soal Utama', color: '#ff4d6d' },
            { label: '🔥 2x Skor', color: '#7209b7' },
            { label: '💡 Kuis Santai', color: '#4cc9f0' },
            { label: '⚡ Kuis Cepat', color: '#38b000' },
            { label: '🎁 Bonus Free', color: '#ffb703' },
            { label: '🌟 Jackpot 3x', color: '#f72585' }
        ];

        ctx.clearRect(0, 0, 400, 400);

        const sliceAngle = (Math.PI * 2) / slices.length;

        for (let i = 0; i < slices.length; i++) {
            const angle = this.state.wheelAngle + i * sliceAngle;
            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.arc(cx, cy, radius, angle, angle + sliceAngle);
            ctx.closePath();

            ctx.fillStyle = slices[i].color;
            ctx.fill();
            ctx.lineWidth = 4;
            ctx.strokeStyle = '#ffffff';
            ctx.stroke();

            // Text Label inside Slice
            ctx.save();
            ctx.translate(cx, cy);
            ctx.rotate(angle + sliceAngle / 2);
            ctx.textAlign = 'right';
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 14px system-ui';
            ctx.fillText(slices[i].label, radius - 18, 5);
            ctx.restore();
        }

        // Center Pin Cap
        ctx.beginPath();
        ctx.arc(cx, cy, 28, 0, Math.PI * 2);
        ctx.fillStyle = '#1e1b4b';
        ctx.fill();
        ctx.lineWidth = 4;
        ctx.strokeStyle = '#ffb703';
        ctx.stroke();

        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 12px monospace';
        ctx.textAlign = 'center';
        ctx.fillText('SPIN', cx, cy + 4);

        // Top Pointer Needle
        ctx.beginPath();
        ctx.moveTo(cx - 12, 10);
        ctx.lineTo(cx + 12, 10);
        ctx.lineTo(cx, 34);
        ctx.closePath();
        ctx.fillStyle = '#ffb703';
        ctx.fill();
        ctx.lineWidth = 2;
        ctx.strokeStyle = '#000000';
        ctx.stroke();
    },

    spinWheel: function() {
        if (this.state.isWheelSpinning || this.state.isEnded) return;
        if (this.state.currentIdx >= this.data.questions.length || this.state.lives <= 0) {
            this.endGame();
            return;
        }

        this.state.isWheelSpinning = true;
        this.playSound('jump');

        let spinTime = 0;
        const totalSpinTime = 2500;
        const startSpeed = Math.random() * 0.3 + 0.4;

        const spinInterval = setInterval(() => {
            spinTime += 40;
            const progress = spinTime / totalSpinTime;
            const easeOutSpeed = startSpeed * Math.pow(1 - progress, 2);
            this.state.wheelAngle += easeOutSpeed;

            this.drawSpinWheel();

            if (spinTime >= totalSpinTime) {
                clearInterval(spinInterval);
                this.state.isWheelSpinning = false;
                
                // Trigger quiz modal after spin stops
                setTimeout(() => {
                    const quizBox = document.getElementById('quizBoxContainer');
                    if (quizBox) quizBox.classList.remove('d-none');
                    this.renderQuestion();
                }, 300);
            }
        }, 40);
    },

    // 🧩 MODE 4: MEMORY MATCH CARDS ENGINE
    initMemoryGrid: function() {
        const gridContainer = document.getElementById('memoryGrid');
        if (!gridContainer) return;

        const totalQ = Math.min(6, this.data.questions.length);
        let cardsData = [];

        for (let i = 0; i < totalQ; i++) {
            const q = this.data.questions[i];
            cardsData.push({ id: i, type: 'q', text: `Pertanyaan #${i+1}: ${q.pertanyaan.substring(0, 45)}...` });
            cardsData.push({ id: i, type: 'a', text: `Jawaban #${i+1}: Opsi ${q.kunci_jawaban.toUpperCase()}` });
        }

        // Shuffle cards
        cardsData.sort(() => Math.random() - 0.5);

        gridContainer.innerHTML = cardsData.map((c, idx) => `
            <div class="col-6 col-md-4 col-lg-3">
                <div class="memory-card bg-dark border border-warning rounded-4 p-3 text-center cursor-pointer shadow-sm hover-scale" onclick="window.GameEngine.flipMemoryCard(this, ${idx}, ${c.id})">
                    <div class="card-inner py-4">
                        <div class="card-front text-warning fs-1 mb-1">🧩</div>
                        <div class="card-back text-white small d-none font-monospace">${c.text}</div>
                    </div>
                </div>
            </div>
        `).join('');
    },

    flipMemoryCard: function(cardElem, idx, qId) {
        if (this.state.flippedCards.length >= 2 || cardElem.classList.contains('matched')) return;

        const backElem = cardElem.querySelector('.card-back');
        const frontElem = cardElem.querySelector('.card-front');

        if (frontElem) frontElem.classList.add('d-none');
        if (backElem) backElem.classList.remove('d-none');
        cardElem.classList.add('border-primary', 'bg-primary', 'bg-opacity-25');

        this.playSound('jump');
        this.state.flippedCards.push({ elem: cardElem, qId: qId });

        if (this.state.flippedCards.length === 2) {
            const c1 = this.state.flippedCards[0];
            const c2 = this.state.flippedCards[1];

            if (c1.qId === c2.qId) {
                // Match found! Unlock Question Challenge
                this.playSound('correct');
                c1.elem.classList.add('matched', 'border-success', 'bg-success', 'bg-opacity-25');
                c2.elem.classList.add('matched', 'border-success', 'bg-success', 'bg-opacity-25');

                this.state.flippedCards = [];
                this.state.currentIdx = qId;

                setTimeout(() => {
                    const quizBox = document.getElementById('quizBoxContainer');
                    if (quizBox) quizBox.classList.remove('d-none');
                    this.renderQuestion();
                }, 600);
            } else {
                // Not match! Flip back
                this.playSound('wrong');
                setTimeout(() => {
                    [c1, c2].forEach(c => {
                        const b = c.elem.querySelector('.card-back');
                        const f = c.elem.querySelector('.card-front');
                        if (b) b.classList.add('d-none');
                        if (f) f.classList.remove('d-none');
                        c.elem.classList.remove('border-primary', 'bg-primary', 'bg-opacity-25');
                    });
                    this.state.flippedCards = [];
                }, 1000);
            }
        }
    },

    updateStaminaHUD: function(val) {
        const pct = Math.max(0, Math.min(100, Math.round(val)));
        const bar = document.getElementById('marioStaminaBar');
        if (bar) {
            bar.style.width = pct + '%';
            bar.textContent = `⚡ STAMINA ${pct}%`;
            if (pct < 30) {
                bar.className = 'progress-bar bg-danger text-white fw-bold progress-bar-striped progress-bar-animated';
            } else {
                bar.className = 'progress-bar bg-warning text-dark fw-bold progress-bar-striped progress-bar-animated';
            }
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
                osc.frequency.setValueAtTime(523.25, ctx.currentTime);
                osc.frequency.setValueAtTime(659.25, ctx.currentTime + 0.1);
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                osc.start();
                osc.stop(ctx.currentTime + 0.25);
            } else if (type === 'wrong') {
                osc.frequency.setValueAtTime(220, ctx.currentTime);
                osc.frequency.setValueAtTime(164.81, ctx.currentTime + 0.1);
                gain.gain.setValueAtTime(0.25, ctx.currentTime);
                osc.start();
                osc.stop(ctx.currentTime + 0.3);
            } else if (type === 'jump') {
                osc.frequency.setValueAtTime(150, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(400, ctx.currentTime + 0.15);
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                osc.start();
                osc.stop(ctx.currentTime + 0.15);
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
        const marioCoinEl = document.getElementById('marioCoinVal');

        if (scoreEl) scoreEl.textContent = this.state.score;
        if (comboEl) comboEl.textContent = `${this.state.combo}x 🔥`;
        if (marioCoinEl) marioCoinEl.textContent = this.state.coins;

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
        setTimeout(() => banner.classList.add('d-none'), 1400);
    },

    endGame: function() {
        if (this.state.isEnded) return;
        this.state.isEnded = true;
        clearInterval(this.state.timerInterval);
        if (this.state.marioLoopInterval) clearInterval(this.state.marioLoopInterval);

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
                        <?php if ($gameType === 'mario_run'): ?>
                            <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fs-6">🍄 Super Mario</span>
                        <?php elseif ($gameType === 'spin_wheel'): ?>
                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1 fs-6">🎡 Spin Wheel</span>
                        <?php elseif ($gameType === 'memory_match'): ?>
                            <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fs-6">🧩 Memory Match</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fs-6">⚡ Quiz Speed</span>
                        <?php endif; ?>
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
                            <?php if ($gameType === 'mario_run'): ?>
                                <div class="fs-3 mb-1">🍄 ⚡ 100%</div>
                                <small class="text-white-50 d-block">Aturan Stamina</small>
                                <span class="fw-bold text-warning">Isi Stamina (Jawaban Benar)</span>
                            <?php elseif ($gameType === 'spin_wheel'): ?>
                                <div class="fs-3 mb-1">🎡 🌟</div>
                                <small class="text-white-50 d-block">Roda Keberuntungan</small>
                                <span class="fw-bold text-warning">Spin Wheel Challenge</span>
                            <?php elseif ($gameType === 'memory_match'): ?>
                                <div class="fs-3 mb-1">🧩 🎴</div>
                                <small class="text-white-50 d-block">Pencocokan Kartu</small>
                                <span class="fw-bold text-warning">Memory Flip Cards</span>
                            <?php else: ?>
                                <div class="fs-3 mb-1">⚡ ⏱️</div>
                                <small class="text-white-50 d-block">Kecepatan Kuis</small>
                                <span class="fw-bold text-warning">Arcade Speed Battle</span>
                            <?php endif; ?>
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

            <!-- 🍄 MODE 1: SUPER MARIO CANVASES & HUD STAGE (Hidden Initially) -->
            <div id="marioStageContainer" class="d-none text-center py-2">
                <div class="row align-items-center g-2 mb-3 px-2">
                    <div class="col-12 col-md-5">
                        <div class="progress rounded-pill bg-dark border border-warning shadow-sm" style="height: 24px;">
                            <div id="marioStaminaBar" class="progress-bar bg-warning text-dark fw-bold progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%; font-size:0.85rem;">
                                ⚡ STAMINA 100%
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 text-start text-md-center">
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold fs-6 shadow-sm">
                            🪙 <span id="marioCoinVal">0</span> Coins
                        </span>
                    </div>
                    <div class="col-6 col-md-4 text-end">
                        <button type="button" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark fs-6 shadow hover-scale" onclick="window.GameEngine.marioJump()">
                            🦘 LOMPAT (SPASI)
                        </button>
                    </div>
                </div>
                <div class="position-relative overflow-hidden rounded-4 border border-secondary shadow-lg">
                    <canvas id="marioCanvas" width="800" height="320" class="w-100 h-auto rounded-4" style="background:#5c94fc; max-height:360px;"></canvas>
                </div>
            </div>

            <!-- 🎡 MODE 3: SPIN WHEEL STAGE (Hidden Initially) -->
            <div id="spinWheelStageContainer" class="d-none text-center py-2">
                <div class="mb-3">
                    <h4 class="fw-bold text-warning mb-1">🎡 RODA KEBERUNTUNGAN KUIS</h4>
                    <p class="text-white-50 small mb-3">Putar roda untuk menentukan kategori pertanyaan kuis!</p>
                </div>
                <div class="d-flex flex-column align-items-center justify-content-center mb-3">
                    <canvas id="wheelCanvas" width="400" height="400" class="rounded-circle shadow-lg mb-3" style="max-width: 320px; max-height: 320px;"></canvas>
                    <button type="button" class="btn btn-warning btn-lg rounded-pill px-5 py-3 fw-bold text-dark shadow-lg hover-scale fs-5" onclick="window.GameEngine.spinWheel()">
                        <i class="bi bi-arrow-repeat me-2"></i> PUTAR RODA HOKI!
                    </button>
                </div>
            </div>

            <!-- 🧩 MODE 4: MEMORY MATCH CARDS STAGE (Hidden Initially) -->
            <div id="memoryStageContainer" class="d-none text-center py-2">
                <div class="mb-3">
                    <h4 class="fw-bold text-info mb-1">🧩 ARENA PENCOCOKAN KARTU MEMORI</h4>
                    <p class="text-white-50 small mb-3">Buka 2 kartu pasangan untuk membuka tantangan kuis!</p>
                </div>
                <div class="row g-3 justify-content-center max-w-4xl mx-auto" id="memoryGrid">
                    <!-- Dynamic Flip Cards -->
                </div>
            </div>

            <!-- ⚡ MODE 2: QUIZ SPEED STAGE (Hidden Initially) -->
            <div id="speedStageContainer" class="d-none"></div>

            <!-- Timer Progress Bar Box (For Quiz Speed Mode) -->
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

<!-- 🍄 MODAL MARIO QUIZ CHECKPOINT POPUP -->
<div class="modal fade" id="modalMarioQuiz" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);">
            <div class="modal-header border-0 bg-warning text-dark p-3.5">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-3">🍄</span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">TANTANGAN KUIS CHECKPOINT MARIO</h5>
                        <small class="fw-semibold text-muted" id="marioModalCounter">Jawab Pertanyaan Untuk Isi Ulang Stamina!</small>
                    </div>
                </div>
            </div>
            <div class="modal-body p-4 text-center">
                <h4 class="fw-bold text-white mb-4 px-md-3" id="marioModalQuestion" style="line-height: 1.4;">
                    Loading Pertanyaan Checkpoint...
                </h4>

                <div class="row g-3 text-start" id="marioModalOptions">
                    <!-- Options -->
                </div>
            </div>
        </div>
    </div>
</div>

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
