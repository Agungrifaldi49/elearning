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

<!-- 🎮 RESPONSIVE FULLSCREEN & MOBILE LANDSCAPE STYLING -->
<style>
/* Fullscreen Arena Card Styling */
#gameArenaCard:fullscreen,
#gameArenaCard:-webkit-full-screen,
#gameArenaCard:-ms-fullscreen {
    width: 100vw !important;
    height: 100vh !important;
    max-height: 100vh !important;
    border-radius: 0 !important;
    margin: 0 !important;
    padding: 0.65rem 0.85rem !important;
    overflow-y: auto !important;
    display: flex !important;
    flex-direction: column !important;
    background: radial-gradient(circle at 50% 20%, #1e1b4b 0%, #0f172a 100%) !important;
}

/* Optimasi Khusus Mobile (Layar <= 768px) */
@media (max-width: 768px) {
    .main-content {
        padding-top: 0.35rem !important;
        padding-bottom: 0.5rem !important;
    }
    #gameArenaCard {
        padding: 0.65rem !important;
        margin-bottom: 0.75rem !important;
        min-height: auto !important;
        border-radius: 1rem !important;
    }
    /* Sembunyikan header yang terlalu lebar saat game sudah mulai */
    #gameArenaCard.game-active #arenaHeaderBar {
        display: none !important;
    }
    #topNavGameHeader.game-active {
        display: none !important;
    }
    .arena-header-bar {
        margin-bottom: 0.65rem !important;
        padding-bottom: 0.5rem !important;
    }
    .arena-title-text {
        font-size: 1.05rem !important;
    }
    .arena-sub-info {
        font-size: 0.75rem !important;
    }
    #marioCanvas {
        max-height: min(48vh, 310px) !important;
        border-radius: 12px !important;
    }
}

/* Optimasi Khusus Layar Pendek / Mobile Landscape (Tinggi <= 560px) */
@media (max-height: 560px) {
    #topNavGameHeader {
        display: none !important;
    }
    #gameArenaCard {
        padding: 0.35rem 0.65rem !important;
        min-height: 100vh !important;
    }
    #gameArenaCard #arenaHeaderBar {
        display: none !important;
    }
    #marioStageContainer .row {
        margin-bottom: 0.25rem !important;
    }
    #marioCanvas {
        max-height: calc(100vh - 85px) !important;
        border-radius: 10px !important;
    }
    .mario-option-btn {
        padding: 0.4rem 0.6rem !important;
    }
    .mario-option-btn span.fs-6 {
        font-size: 0.85rem !important;
    }
}

/* Opsi Jawaban Checkpoint Mario */
.mario-option-btn {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border-color: rgba(245, 158, 11, 0.45) !important;
    background: rgba(30, 41, 59, 0.85) !important;
}
.mario-option-btn:hover,
.mario-option-btn:active {
    background: rgba(245, 158, 11, 0.25) !important;
    border-color: #f59e0b !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(245, 158, 11, 0.35) !important;
}

/* Animasi Rotasi Ponsel */
.rotate-phone-animation {
    display: inline-block;
    animation: rotatePhone 2.2s infinite ease-in-out;
}
@keyframes rotatePhone {
    0%, 20% { transform: rotate(0deg); }
    50%, 70% { transform: rotate(-90deg); }
    100% { transform: rotate(0deg); }
}
</style>

<!-- Declare Game Engine & Window Helpers BEFORE HTML elements render -->
<script>
async function requestMobileLandscapeAndFullscreen() {
    const arenaCard = document.getElementById('gameArenaCard');
    if (!arenaCard) return;

    // 1. Fullscreen Request pada kartu game arena
    try {
        if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
            if (arenaCard.requestFullscreen) {
                await arenaCard.requestFullscreen().catch(() => {});
            } else if (arenaCard.webkitRequestFullscreen) {
                await arenaCard.webkitRequestFullscreen();
            } else if (arenaCard.msRequestFullscreen) {
                await arenaCard.msRequestFullscreen();
            }
        }
    } catch (err) {
        console.warn('Fullscreen notice:', err);
    }

    // 2. Kunci Orientasi ke Landscape (Screen Orientation API)
    try {
        if (screen.orientation && screen.orientation.lock) {
            await screen.orientation.lock('landscape').catch(() => {});
        } else if (screen.lockOrientation) {
            screen.lockOrientation('landscape');
        } else if (screen.webkitLockOrientation) {
            screen.webkitLockOrientation('landscape');
        } else if (screen.mozLockOrientation) {
            screen.mozLockOrientation('landscape');
        } else if (screen.msLockOrientation) {
            screen.msLockOrientation('landscape');
        }
    } catch (err) {
        console.warn('Orientation lock notice:', err);
    }

    if (typeof window.checkDeviceOrientation === 'function') {
        window.checkDeviceOrientation();
    }
}
window.requestMobileLandscapeAndFullscreen = requestMobileLandscapeAndFullscreen;

function toggleArenaFullscreen() {
    const arenaCard = document.getElementById('gameArenaCard');
    if (!arenaCard) return;

    if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
        requestMobileLandscapeAndFullscreen();
    } else {
        try {
            if (screen.orientation && screen.orientation.unlock) {
                screen.orientation.unlock();
            }
        } catch(e) {}
        try {
            if (document.exitFullscreen) {
                document.exitFullscreen().catch(() => {});
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        } catch(e) {}
    }
}
window.toggleArenaFullscreen = toggleArenaFullscreen;

function checkDeviceOrientation() {
    const isMobile = window.innerWidth <= 991 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const isPortrait = window.innerHeight > window.innerWidth;
    const promptEl = document.getElementById('rotateDevicePrompt');
    
    if (promptEl) {
        // Tampilkan prompt hanya jika di layar kecil dan orientasi portrait saat game berjalan
        if (isMobile && isPortrait && window.GameEngine && window.GameEngine.state && window.GameEngine.state.isStarted && !window.GameEngine.state.isEnded) {
            promptEl.classList.remove('d-none');
        } else {
            promptEl.classList.add('d-none');
        }
    }
}
window.checkDeviceOrientation = checkDeviceOrientation;

window.addEventListener('resize', checkDeviceOrientation);
window.addEventListener('orientationchange', () => {
    setTimeout(checkDeviceOrientation, 250);
});
if (screen.orientation && screen.orientation.addEventListener) {
    screen.orientation.addEventListener('change', checkDeviceOrientation);
}

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
        stageTimeLeft: <?= (int)($game['durasi_per_soal'] ?: 30) ?>,
        marioDamageCooldown: 0,
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
        marioX: 90,
        marioY: 216,
        marioVy: 0,
        marioGroundY: 260,
        isJumping: false,
        jumpCount: 0,
        marioDistance: 0,
        marioNextCheckpoint: 120,
        marioSpeed: 4.6,
        marioInvincibleTimer: 0,
        marioRunFrame: 0,
        marioStompCombo: 0,
        screenShake: 0,
        marioEntities: {
            goombas: [],
            coins: [],
            blocks: [],
            pipes: [],
            flagpole: null,
            particles: [],
            popups: []
        },
        bgOffset: 0,
        flippedCards: [],
        matchedPairs: 0,
        // 🏎️ Turbo Car Racing Runner State
        racingLoopInterval: null,
        racingLane: 1, // 0: Kiri (x:250), 1: Tengah (x:400), 2: Kanan (x:550)
        racingCarX: 400,
        racingTargetX: 400,
        racingSpeed: 11,
        racingBaseSpeed: 11,
        racingBoostTimer: 0,
        racingDistance: 0,
        racingGateStep: 100, // Setiap 100 meter muncul gerbang soal
        racingNextGateDist: 100,
        racingIsAtGate: false,
        racingCrashShakeTimer: 0,
        racingRoadOffset: 0,
        racingPickups: [],
        racingExplosionParticles: [],
        racingCrashParticles: []
    },

    startArena: function() {
        if (this.state.isStarted) return;
        this.state.isStarted = true;

        const arenaCard = document.getElementById('gameArenaCard');
        if (arenaCard) arenaCard.classList.add('game-active');
        const topNav = document.getElementById('topNavGameHeader');
        if (topNav) topNav.classList.add('game-active');

        if (window.requestMobileLandscapeAndFullscreen) {
            window.requestMobileLandscapeAndFullscreen();
        } else if (window.toggleArenaFullscreen) {
            window.toggleArenaFullscreen();
        }

        const overlay = document.getElementById('startScreenOverlay');
        const timerBox = document.getElementById('timerBarContainer');
        const quizBox = document.getElementById('quizBoxContainer');

        const marioBox = document.getElementById('marioStageContainer');
        const racingBox = document.getElementById('racingStageContainer');
        const speedBox = document.getElementById('speedStageContainer');
        const wheelBox = document.getElementById('spinWheelStageContainer');
        const memoryBox = document.getElementById('memoryStageContainer');

        if (overlay) overlay.classList.add('d-none');

        // Route to distinct visual stage based on gameType
        if (this.data.gameType === 'mario_run') {
            if (marioBox) marioBox.classList.remove('d-none');
            this.initMarioCanvas();
        } else if (this.data.gameType === 'car_racing') {
            if (racingBox) racingBox.classList.remove('d-none');
            this.initRacingCanvas();
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
            } else if (this.data.gameType === 'car_racing') {
                this.startRacingGame();
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

    // 🍄 MODE 1: ENHANCED SUPER MARIO RETRO PLATFORM RUNNER ENGINE
    initMarioCanvas: function() {
        const canvas = document.getElementById('marioCanvas');
        if (!canvas) return;
        this.canvasCtx = canvas.getContext('2d');

        // Keyboard jumping controls (Space, ArrowUp, 'w', 'W')
        window.addEventListener('keydown', (e) => {
            if (this.data.gameType !== 'mario_run') return;
            if (e.code === 'Space' || e.code === 'ArrowUp' || e.key === 'w' || e.key === 'W') {
                e.preventDefault();
                this.marioJump();
            }
        });

        // Touch & Mouse click anywhere on the canvas jumps!
        canvas.addEventListener('pointerdown', (e) => {
            if (this.data.gameType !== 'mario_run') return;
            e.preventDefault();
            this.marioJump();
        });
    },

    resetMarioWorld: function() {
        this.state.marioX = 90;
        this.state.marioY = 216;
        this.state.marioVy = 0;
        this.state.isJumping = false;
        this.state.jumpCount = 0;
        this.state.screenShake = 0;
        this.state.marioRunFrame = 0;

        // Initialize dynamic world entities
        this.state.marioEntities = {
            goombas: [
                { x: 550, y: 230, vx: 1.8, isSquished: false, squishTimer: 0, isBlasted: false, blastedVy: 0 },
                { x: 920, y: 230, vx: 1.9, isSquished: false, squishTimer: 0, isBlasted: false, blastedVy: 0 }
            ],
            coins: [
                { x: 300, y: 200, collected: false },
                { x: 340, y: 175, collected: false },
                { x: 380, y: 165, collected: false },
                { x: 420, y: 175, collected: false },
                { x: 460, y: 200, collected: false },
                { x: 740, y: 185, collected: false },
                { x: 775, y: 185, collected: false },
                { x: 810, y: 185, collected: false }
            ],
            blocks: [
                { x: 380, y: 155, hit: false, bumpY: 0 },
                { x: 775, y: 155, hit: false, bumpY: 0 },
                { x: 1150, y: 155, hit: false, bumpY: 0 }
            ],
            pipes: [
                { x: 680, y: 216, h: 44, hasPiranha: true, piranhaY: 0 },
                { x: 1300, y: 216, h: 44, hasPiranha: false, piranhaY: 0 }
            ],
            flagpole: null,
            particles: [],
            popups: []
        };
    },

    startMarioRun: function() {
        this.state.isMarioRunning = true;

        const totalDuration = this.data.timerDuration || 30;
        if (typeof this.state.stageTimeLeft === 'undefined' || this.state.stageTimeLeft <= 0) {
            this.state.stageTimeLeft = totalDuration;
        }

        const curSec = Math.max(0, Math.ceil(this.state.stageTimeLeft));
        const curPct = Math.max(0, Math.min(100, (this.state.stageTimeLeft / totalDuration) * 100));
        if (this.updateStageTimerHUD) {
            this.updateStageTimerHUD(curSec, curPct);
        }

        if (!this.state.marioEntities || !this.state.marioEntities.goombas || this.state.marioEntities.goombas.length === 0) {
            this.resetMarioWorld();
        }

        if (this.state.marioLoopInterval) clearInterval(this.state.marioLoopInterval);

        this.state.marioLoopInterval = setInterval(() => {
            if (!this.state.isMarioRunning || this.state.isEnded) return;
            this.updateMarioPhysics();
            this.drawMarioCanvas();
        }, 1000 / 60);
    },

    marioJump: function() {
        if (!this.state.isStarted || this.state.isEnded || !this.state.isMarioRunning) return;
        if (!this.state.isJumping) {
            this.state.isJumping = true;
            this.state.jumpCount = 1;
            this.state.marioVy = -13.5;
            this.playSound('jump');
            this.createDust(this.state.marioX + 16, 258, 5);
        } else if (this.state.jumpCount === 1) {
            // Exciting mid-air Double Jump!
            this.state.jumpCount = 2;
            this.state.marioVy = -11.5;
            this.playSound('jump');
            this.addPopup('DOUBLE JUMP! 🦘', this.state.marioX, this.state.marioY - 15, '#00f5d4');
            this.createDust(this.state.marioX + 16, this.state.marioY + 36, 6);
        }
    },

    createDust: function(x, y, count) {
        if (!this.state.marioEntities) return;
        for (let i = 0; i < count; i++) {
            this.state.marioEntities.particles.push({
                x: x + (Math.random() * 8 - 4),
                y: y + (Math.random() * 4 - 2),
                vx: -(Math.random() * 2 + 1),
                vy: -(Math.random() * 1.5 + 0.3),
                radius: Math.random() * 3 + 2,
                color: 'rgba(255, 255, 255, 0.7)',
                alpha: 0.8,
                decay: 0.04
            });
        }
    },

    createSparkles: function(x, y, count) {
        if (!this.state.marioEntities) return;
        const colors = ['#ffd166', '#ffb703', '#00f5d4', '#ffffff'];
        for (let i = 0; i < count; i++) {
            const angle = Math.random() * Math.PI * 2;
            const speed = Math.random() * 3.5 + 1.5;
            this.state.marioEntities.particles.push({
                x: x,
                y: y,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed - 1,
                radius: Math.random() * 3 + 2,
                color: colors[Math.floor(Math.random() * colors.length)],
                alpha: 1.0,
                decay: 0.035
            });
        }
    },

    addPopup: function(text, x, y, color) {
        if (!this.state.marioEntities) return;
        this.state.marioEntities.popups.push({
            text: text,
            x: x,
            y: y,
            vy: -1.6,
            alpha: 1.0,
            color: color || '#ffd166'
        });
    },

    updateMarioPhysics: function() {
        const ents = this.state.marioEntities;
        if (!ents) return;

        // Effective Mario speed (boosted during Star Invincibility)
        const isStarActive = (this.state.marioInvincibleTimer > 0);
        const currentSpeed = this.state.marioSpeed + (isStarActive ? 2.5 : 0);

        if (this.state.marioInvincibleTimer > 0) {
            this.state.marioInvincibleTimer--;
            if (this.state.marioInvincibleTimer % 4 === 0) {
                this.createSparkles(this.state.marioX + Math.random() * 28, this.state.marioY + Math.random() * 40, 1);
            }
        }

        if (this.state.screenShake > 0) {
            this.state.screenShake *= 0.86;
            if (this.state.screenShake < 0.5) this.state.screenShake = 0;
        }

        // Mario gravity & vertical position
        this.state.marioY += this.state.marioVy;
        this.state.marioVy += 0.68;

        // Ground landing check
        if (this.state.marioY >= 216) {
            if (this.state.isJumping) {
                this.createDust(this.state.marioX + 16, 258, 6);
            }
            this.state.marioY = 216;
            this.state.marioVy = 0;
            this.state.isJumping = false;
            this.state.jumpCount = 0;
        }

        // Run stride animation & dust puffs
        if (!this.state.isJumping) {
            this.state.marioRunFrame++;
            if (this.state.marioRunFrame % 8 === 0) {
                this.createDust(this.state.marioX + 4, 258, 2);
            }
        }

        // World background scroll & distance progression
        this.state.bgOffset += currentSpeed;
        this.state.marioDistance += (currentSpeed * 0.08);

        // Hitung mundur Durasi Timer per Soal (Detik) sesuai pengaturan game
        const totalDuration = this.data.timerDuration || 30;
        if (typeof this.state.stageTimeLeft === 'undefined' || this.state.stageTimeLeft <= 0) {
            this.state.stageTimeLeft = totalDuration;
        }

        // Mario Damage Cooldown Timer (flicker kebal setelah terkena musuh)
        if (this.state.marioDamageCooldown > 0) {
            this.state.marioDamageCooldown--;
        }

        // Kurangi waktu secara presisi 1/60 detik per frame
        this.state.stageTimeLeft -= (1 / 60);
        const remSec = Math.max(0, Math.ceil(this.state.stageTimeLeft));
        const timerPct = Math.max(0, Math.min(100, (this.state.stageTimeLeft / totalDuration) * 100));
        
        if (this.updateStageTimerHUD) {
            this.updateStageTimerHUD(remSec, timerPct);
        }

        // Munculkan Gerbang Bendera Checkpoint saat waktu tersisa 2.5 detik
        if (this.state.stageTimeLeft <= 2.5 && !ents.flagpole) {
            ents.flagpole = { x: 840, reached: false };
        }

        // Flagpole checkpoint interaction
        if (ents.flagpole) {
            ents.flagpole.x -= currentSpeed;
            if (!ents.flagpole.reached && ents.flagpole.x <= this.state.marioX + 24) {
                ents.flagpole.reached = true;
                this.playSound('powerup');
                this.addPopup('🏁 GERBANG CHECKPOINT! 🌟', this.state.marioX, this.state.marioY - 25, '#ffd166');
                setTimeout(() => {
                    ents.flagpole = null;
                    this.triggerMarioQuestionCheckpoint('checkpoint_reached');
                }, 350);
                return;
            }
        }

        // Tepat ketika Durasi Timer per Soal (Detik) HABIS: munculkan tantangan kuis checkpoint!
        if (this.state.stageTimeLeft <= 0) {
            this.state.stageTimeLeft = 0;
            if (this.updateStageTimerHUD) this.updateStageTimerHUD(0, 0);
            this.playSound('powerup');
            this.triggerMarioQuestionCheckpoint('checkpoint_reached');
            return;
        }

        // Update Goombas
        for (let i = ents.goombas.length - 1; i >= 0; i--) {
            const g = ents.goombas[i];
            if (g.isBlasted) {
                g.y += g.blastedVy;
                g.blastedVy += 0.8;
                g.x += 4;
            } else if (g.isSquished) {
                g.squishTimer--;
                if (g.squishTimer <= 0) {
                    ents.goombas.splice(i, 1);
                    continue;
                }
            } else {
                g.x -= (currentSpeed + g.vx);

                // Stomp Detection: Mario lands on Goomba from above
                const mx = this.state.marioX;
                const my = this.state.marioY;
                const marioFeetY = my + 44;
                const goombaTopY = g.y;

                if (this.state.marioVy > 0 &&
                    marioFeetY >= goombaTopY && marioFeetY <= goombaTopY + 16 &&
                    Math.abs((mx + 16) - (g.x + 15)) < 24) {
                    // Stomp successful!
                    g.isSquished = true;
                    g.squishTimer = 26;
                    this.state.marioVy = -11.8;
                    this.state.jumpCount = 1;
                    this.state.score += 50;
                    this.state.marioStompCombo++;
                    this.state.coins += 1;
                    this.playSound('stomp');
                    this.addPopup('+50 STOMP!', g.x, g.y - 12, '#ffd166');
                    this.createSparkles(g.x + 15, g.y + 10, 8);
                    this.updateHUD();
                } else if (Math.abs((mx + 16) - (g.x + 15)) < 22 && Math.abs((my + 22) - (g.y + 15)) < 24) {
                    // Horizontal collision with Goomba
                    if (isStarActive) {
                        g.isBlasted = true;
                        g.blastedVy = -12;
                        this.state.score += 100;
                        this.playSound('stomp');
                        this.addPopup('+100 STAR KILL! 🌟', g.x, g.y - 15, '#00f5d4');
                        this.createSparkles(g.x + 15, g.y + 15, 12);
                        this.updateHUD();
                    } else {
                        // Jika Mario sedang dalam masa flicker kebal (damage cooldown), abaikan
                        if (this.state.marioDamageCooldown > 0) {
                            continue;
                        }

                        // Player takes damage: kurangi nyawa tapi TETAP LANJUT LARI sampai waktu habis!
                        this.state.marioDamageCooldown = 90; // 1.5 detik kebal berkedip
                        this.state.screenShake = 16;
                        this.playSound('bump');
                        this.state.lives--;
                        this.state.combo = 0;
                        this.addPopup('OUCH! -1 ❤️', this.state.marioX, this.state.marioY - 20, '#ef4444');
                        this.updateHUD();
                        g.isBlasted = true;
                        g.blastedVy = -10;

                        if (this.state.lives <= 0) {
                            this.endGame();
                            return;
                        }
                    }
                }
            }

            if (g.x < -80 || g.y > 400) {
                ents.goombas.splice(i, 1);
            }
        }

        // Spawn new Goombas dynamically
        if (ents.goombas.length < 2 && Math.random() < 0.02) {
            const lastG = ents.goombas[ents.goombas.length - 1];
            const startX = lastG ? Math.max(850, lastG.x + 280) : 850;
            ents.goombas.push({
                x: startX,
                y: 230,
                vx: Math.random() * 0.8 + 1.2,
                isSquished: false,
                squishTimer: 0,
                isBlasted: false,
                blastedVy: 0
            });
        }

        // Update Mystery '?' Blocks
        for (let i = ents.blocks.length - 1; i >= 0; i--) {
            const b = ents.blocks[i];
            b.x -= currentSpeed;

            if (b.bumpY < 0) {
                b.bumpY += 1.2;
                if (b.bumpY > 0) b.bumpY = 0;
            }

            // Head bump hit detection from underneath
            const mx = this.state.marioX;
            const my = this.state.marioY;
            if (!b.hit && this.state.marioVy < 0 &&
                Math.abs((mx + 16) - (b.x + 16)) < 24 &&
                my <= (b.y + 32) && my >= (b.y + 16)) {
                b.hit = true;
                b.bumpY = -10;
                this.state.score += 25;
                this.state.coins += 1;
                this.state.stamina = Math.min(100, this.state.stamina + 8);
                this.playSound('bump');
                this.playSound('coin');
                this.addPopup('+25 GOLD COIN! 🪙', b.x, b.y - 15, '#ffd166');
                this.createSparkles(b.x + 16, b.y, 8);
                this.updateHUD();
            }

            if (b.x < -80) {
                ents.blocks.splice(i, 1);
            }
        }

        // Spawn new Mystery Blocks
        if (ents.blocks.length < 2 && Math.random() < 0.015) {
            const lastB = ents.blocks[ents.blocks.length - 1];
            const startX = lastB ? Math.max(860, lastB.x + 320) : 860;
            ents.blocks.push({
                x: startX,
                y: 155,
                hit: false,
                bumpY: 0
            });
        }

        // Update Airborne Coins
        for (let i = ents.coins.length - 1; i >= 0; i--) {
            const c = ents.coins[i];
            c.x -= currentSpeed;

            if (!c.collected) {
                const mx = this.state.marioX;
                const my = this.state.marioY;
                if (Math.hypot((mx + 16) - (c.x + 9), (my + 22) - (c.y + 12)) < 26) {
                    c.collected = true;
                    this.state.coins += 1;
                    this.state.score += 10;
                    this.state.stamina = Math.min(100, this.state.stamina + 2);
                    this.playSound('coin');
                    this.createSparkles(c.x + 9, c.y + 12, 6);
                    this.addPopup('+10', c.x, c.y - 8, '#ffd166');
                    this.updateHUD();
                }
            }

            if (c.x < -60 || c.collected) {
                ents.coins.splice(i, 1);
            }
        }

        // Spawn new Coin Arcs
        if (ents.coins.length < 3 && Math.random() < 0.02) {
            const baseStartX = 840 + Math.random() * 60;
            const coinHeights = [200, 175, 165, 175, 200];
            for (let j = 0; j < coinHeights.length; j++) {
                ents.coins.push({
                    x: baseStartX + (j * 38),
                    y: coinHeights[j],
                    collected: false
                });
            }
        }

        // Update Pipes
        for (let i = ents.pipes.length - 1; i >= 0; i--) {
            const p = ents.pipes[i];
            p.x -= currentSpeed;
            if (p.hasPiranha) {
                p.piranhaY = Math.sin(Date.now() / 450) * 16;
            }

            // Pipe horizontal collision
            const mx = this.state.marioX;
            const my = this.state.marioY;
            if (mx + 30 >= p.x && mx <= p.x + 44 && my + 44 > p.y + 8) {
                if (my + 44 <= p.y + 18 && this.state.marioVy >= 0) {
                    // Standing on top of pipe
                    this.state.marioY = p.y - 44;
                    this.state.marioVy = 0;
                    this.state.isJumping = false;
                    this.state.jumpCount = 0;
                } else if (!isStarActive) {
                    // Bumping against pipe wall: soft knockback
                    this.state.marioX = Math.max(30, p.x - 32);
                }
            }

            if (p.x < -80) {
                ents.pipes.splice(i, 1);
            }
        }

        // Spawn new Warp Pipe
        if (ents.pipes.length < 1 && Math.random() < 0.01) {
            ents.pipes.push({
                x: 900 + Math.random() * 200,
                y: 216,
                h: 44,
                hasPiranha: Math.random() < 0.5,
                piranhaY: 0
            });
        }

        // Update Particles
        for (let i = ents.particles.length - 1; i >= 0; i--) {
            const pt = ents.particles[i];
            pt.x += pt.vx;
            pt.y += pt.vy;
            pt.alpha -= pt.decay;
            if (pt.alpha <= 0) {
                ents.particles.splice(i, 1);
            }
        }

        // Update Floating Popups
        for (let i = ents.popups.length - 1; i >= 0; i--) {
            const pp = ents.popups[i];
            pp.y += pp.vy;
            pp.alpha -= 0.022;
            if (pp.alpha <= 0) {
                ents.popups.splice(i, 1);
            }
        }
    },

    drawMarioCanvas: function() {
        const ctx = this.canvasCtx;
        if (!ctx) return;

        const w = 800;
        const h = 320;
        const ents = this.state.marioEntities;

        ctx.save();

        // Screen shake transform
        if (this.state.screenShake > 0) {
            const shakeX = (Math.random() - 0.5) * this.state.screenShake;
            const shakeY = (Math.random() - 0.5) * this.state.screenShake;
            ctx.translate(shakeX, shakeY);
        }

        // 1. Sky Gradient
        const skyGrad = ctx.createLinearGradient(0, 0, 0, h);
        skyGrad.addColorStop(0, '#3b82f6');
        skyGrad.addColorStop(0.65, '#60a5fa');
        skyGrad.addColorStop(1, '#93c5fd');
        ctx.fillStyle = skyGrad;
        ctx.fillRect(0, 0, w, h);

        // 2. Sun with Warm Corona
        ctx.fillStyle = 'rgba(255, 253, 208, 0.4)';
        ctx.beginPath();
        ctx.arc(80, 50, 42, 0, Math.PI * 2);
        ctx.fill();
        ctx.fillStyle = '#fffbeb';
        ctx.beginPath();
        ctx.arc(80, 50, 24, 0, Math.PI * 2);
        ctx.fill();

        // 3. Clouds (Slow parallax: speed * 0.2)
        ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
        for (let i = 0; i < 4; i++) {
            let cx = ((i * 260) - (this.state.bgOffset * 0.2)) % (w + 140);
            if (cx < -120) cx += w + 260;
            const cy = 40 + (i % 2) * 25;
            ctx.beginPath();
            ctx.arc(cx, cy, 20, 0, Math.PI * 2);
            ctx.arc(cx + 18, cy - 6, 26, 0, Math.PI * 2);
            ctx.arc(cx + 40, cy, 20, 0, Math.PI * 2);
            ctx.fill();
        }

        // 4. Distant Mountains (Parallax: speed * 0.4)
        ctx.fillStyle = '#64748b';
        for (let i = 0; i < 3; i++) {
            let mx = ((i * 380) - (this.state.bgOffset * 0.4)) % (w + 200);
            if (mx < -200) mx += w + 400;
            ctx.beginPath();
            ctx.moveTo(mx, 260);
            ctx.lineTo(mx + 90, 140);
            ctx.lineTo(mx + 180, 260);
            ctx.fill();
            // Snow peak
            ctx.fillStyle = '#f8fafc';
            ctx.beginPath();
            ctx.moveTo(mx + 90, 140);
            ctx.lineTo(mx + 60, 180);
            ctx.lineTo(mx + 90, 175);
            ctx.lineTo(mx + 120, 180);
            ctx.fill();
            ctx.fillStyle = '#64748b';
        }

        // 5. Rolling Grassy Hills & Bushes (Parallax: speed * 0.75)
        ctx.fillStyle = '#22c55e';
        for (let i = 0; i < 4; i++) {
            let hx = ((i * 280) - (this.state.bgOffset * 0.75)) % (w + 150);
            if (hx < -150) hx += w + 300;
            ctx.beginPath();
            ctx.arc(hx, 270, 75, Math.PI, 0);
            ctx.fill();
            // Round bush accents
            ctx.fillStyle = '#16a34a';
            ctx.beginPath();
            ctx.arc(hx + 30, 255, 20, Math.PI, 0);
            ctx.fill();
            ctx.fillStyle = '#22c55e';
        }

        // 6. Checkpoint Flagpole (if spawned)
        if (ents && ents.flagpole) {
            const fpx = ents.flagpole.x;
            // Stone base
            ctx.fillStyle = '#15803d';
            ctx.fillRect(fpx - 14, 236, 28, 24);
            ctx.fillStyle = '#166534';
            ctx.strokeRect(fpx - 14, 236, 28, 24);
            // Mast
            ctx.fillStyle = '#f1f5f9';
            ctx.fillRect(fpx - 3, 70, 6, 166);
            // Golden finial ball
            ctx.fillStyle = '#eab308';
            ctx.beginPath();
            ctx.arc(fpx, 68, 8, 0, Math.PI * 2);
            ctx.fill();
            // Waving Red Mario Flag
            const wave = Math.sin(Date.now() / 120) * 4;
            ctx.fillStyle = '#dc2626';
            ctx.beginPath();
            ctx.moveTo(fpx + 3, 78);
            ctx.lineTo(fpx + 45 + wave, 92);
            ctx.lineTo(fpx + 3, 108);
            ctx.fill();
            // White 'M' emblem on flag
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 12px sans-serif';
            ctx.fillText('M', fpx + 12, 98);
        }

        // 7. Warp Pipes
        if (ents && ents.pipes) {
            ents.pipes.forEach(p => {
                const px = p.x;
                const py = p.y;
                // Piranha Plant
                if (p.hasPiranha) {
                    const plantY = py - 18 + Math.max(-14, Math.min(10, p.piranhaY));
                    ctx.fillStyle = '#ef4444';
                    ctx.beginPath();
                    ctx.arc(px + 23, plantY, 12, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.fillStyle = '#ffffff';
                    ctx.beginPath();
                    ctx.arc(px + 18, plantY - 3, 3, 0, Math.PI * 2);
                    ctx.arc(px + 27, plantY + 3, 3, 0, Math.PI * 2);
                    ctx.fill();
                    // Jaws
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(px + 20, plantY - 2, 7, 4);
                }
                // Pipe Collar / Rim
                ctx.fillStyle = '#22c55e';
                ctx.fillRect(px - 3, py, 52, 16);
                ctx.fillStyle = '#15803d';
                ctx.fillRect(px + 40, py, 9, 16);
                ctx.strokeStyle = '#052e16';
                ctx.lineWidth = 2;
                ctx.strokeRect(px - 3, py, 52, 16);
                // Pipe Body
                ctx.fillStyle = '#22c55e';
                ctx.fillRect(px + 2, py + 16, 42, 28);
                ctx.fillStyle = '#15803d';
                ctx.fillRect(px + 34, py + 16, 10, 28);
                ctx.strokeRect(px + 2, py + 16, 42, 28);
            });
        }

        // 8. Foreground Ground
        const groundY = 260;
        // Grass top turf
        ctx.fillStyle = '#22c55e';
        ctx.fillRect(0, groundY, w, 10);
        ctx.fillStyle = '#16a34a';
        ctx.fillRect(0, groundY + 8, w, 4);
        // Brick / Earth soil tiles
        const soilGrad = ctx.createLinearGradient(0, groundY + 12, 0, h);
        soilGrad.addColorStop(0, '#c2410c');
        soilGrad.addColorStop(1, '#7c2d12');
        ctx.fillStyle = soilGrad;
        ctx.fillRect(0, groundY + 12, w, h - groundY - 12);
        // Brick pattern overlay
        ctx.strokeStyle = '#9a3412';
        ctx.lineWidth = 1.5;
        const brickW = 32;
        const brickH = 16;
        const bOff = (this.state.bgOffset) % brickW;
        for (let bx = -brickW; bx < w + brickW; bx += brickW) {
            ctx.strokeRect(bx - bOff, groundY + 12, brickW, brickH);
            ctx.strokeRect(bx - bOff + (brickW / 2), groundY + 12 + brickH, brickW, brickH);
            ctx.strokeRect(bx - bOff, groundY + 12 + (brickH * 2), brickW, brickH);
        }

        // 9. Mystery '?' Blocks
        if (ents && ents.blocks) {
            ents.blocks.forEach(b => {
                const by = b.y + b.bumpY;
                if (b.hit) {
                    // Empty hit bronze brick block
                    ctx.fillStyle = '#78350f';
                    ctx.fillRect(b.x, by, 32, 32);
                    ctx.strokeStyle = '#451a03';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(b.x, by, 32, 32);
                    // Rivets
                    ctx.fillStyle = '#451a03';
                    ctx.fillRect(b.x + 3, by + 3, 3, 3);
                    ctx.fillRect(b.x + 26, by + 3, 3, 3);
                    ctx.fillRect(b.x + 3, by + 26, 3, 3);
                    ctx.fillRect(b.x + 26, by + 26, 3, 3);
                } else {
                    // Shiny Golden Mystery '?' Block
                    const bGrad = ctx.createLinearGradient(b.x, by, b.x, by + 32);
                    bGrad.addColorStop(0, '#fde047');
                    bGrad.addColorStop(0.5, '#eab308');
                    bGrad.addColorStop(1, '#ca8a04');
                    ctx.fillStyle = bGrad;
                    ctx.fillRect(b.x, by, 32, 32);
                    ctx.strokeStyle = '#713f12';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(b.x, by, 32, 32);
                    // Corner rivets
                    ctx.fillStyle = '#713f12';
                    ctx.fillRect(b.x + 3, by + 3, 3, 3);
                    ctx.fillRect(b.x + 26, by + 3, 3, 3);
                    ctx.fillRect(b.x + 3, by + 26, 3, 3);
                    ctx.fillRect(b.x + 26, by + 26, 3, 3);
                    // Animated glowing '?'
                    ctx.fillStyle = '#ffffff';
                    ctx.font = 'bold 20px "Courier New", monospace';
                    ctx.fillText('?', b.x + 10, by + 24);
                }
            });
        }

        // 10. Airborne Coins (Rotating 3D Gold Coins)
        if (ents && ents.coins) {
            ents.coins.forEach(c => {
                if (c.collected) return;
                const spin = Math.cos(Date.now() / 140);
                const coinW = Math.max(3, Math.abs(spin) * 14);
                ctx.save();
                ctx.translate(c.x + 9, c.y + 12);
                ctx.fillStyle = '#facc15';
                ctx.beginPath();
                ctx.ellipse(0, 0, coinW, 12, 0, 0, Math.PI * 2);
                ctx.fill();
                ctx.strokeStyle = '#ca8a04';
                ctx.lineWidth = 1.5;
                ctx.stroke();
                // Inner vertical sheen line
                if (coinW > 6) {
                    ctx.fillStyle = '#fef08a';
                    ctx.fillRect(-2, -7, 4, 14);
                }
                ctx.restore();
            });
        }

        // 11. Goombas (Jamur Musuh)
        if (ents && ents.goombas) {
            ents.goombas.forEach(g => {
                ctx.save();
                if (g.isBlasted) {
                    ctx.translate(g.x + 15, g.y + 15);
                    ctx.rotate(Date.now() / 80);
                    ctx.translate(-(g.x + 15), -(g.y + 15));
                }

                if (g.isSquished) {
                    // Squished Pancake Goomba
                    ctx.fillStyle = '#78350f';
                    ctx.beginPath();
                    ctx.ellipse(g.x + 15, g.y + 24, 18, 6, 0, 0, Math.PI * 2);
                    ctx.fill();
                    // 'X X' Squished Eyes
                    ctx.fillStyle = '#ffffff';
                    ctx.font = 'bold 10px sans-serif';
                    ctx.fillText('x  x', g.x + 8, g.y + 26);
                } else {
                    // Mushroom Cap
                    ctx.fillStyle = '#92400e';
                    ctx.beginPath();
                    ctx.moveTo(g.x + 2, g.y + 20);
                    ctx.bezierCurveTo(g.x + 2, g.y, g.x + 28, g.y, g.x + 28, g.y + 20);
                    ctx.closePath();
                    ctx.fill();
                    // Face
                    ctx.fillStyle = '#fef3c7';
                    ctx.beginPath();
                    ctx.arc(g.x + 15, g.y + 18, 9, 0, Math.PI * 2);
                    ctx.fill();
                    // Mean Furrowed Eyebrows
                    ctx.strokeStyle = '#1e293b';
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    ctx.moveTo(g.x + 7, g.y + 12);
                    ctx.lineTo(g.x + 14, g.y + 15);
                    ctx.moveTo(g.x + 23, g.y + 12);
                    ctx.lineTo(g.x + 16, g.y + 15);
                    ctx.stroke();
                    // Angry Eyes
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(g.x + 8, g.y + 15, 4, 6);
                    ctx.fillRect(g.x + 18, g.y + 15, 4, 6);
                    ctx.fillStyle = '#0f172a';
                    ctx.fillRect(g.x + 10, g.y + 16, 2, 4);
                    ctx.fillRect(g.x + 18, g.y + 16, 2, 4);
                    // Fangs
                    ctx.fillStyle = '#ffffff';
                    ctx.beginPath();
                    ctx.moveTo(g.x + 10, g.y + 24);
                    ctx.lineTo(g.x + 12, g.y + 21);
                    ctx.lineTo(g.x + 14, g.y + 24);
                    ctx.moveTo(g.x + 16, g.y + 24);
                    ctx.lineTo(g.x + 18, g.y + 21);
                    ctx.lineTo(g.x + 20, g.y + 24);
                    ctx.fill();
                    // Waddling Feet
                    const footSwing = Math.sin(Date.now() / 90) * 4;
                    ctx.fillStyle = '#0f172a';
                    ctx.beginPath();
                    ctx.ellipse(g.x + 7 + footSwing, g.y + 28, 6, 3, 0, 0, Math.PI * 2);
                    ctx.ellipse(g.x + 23 - footSwing, g.y + 28, 6, 3, 0, 0, Math.PI * 2);
                    ctx.fill();
                }
                ctx.restore();
            });
        }

        // 12. Mario Character (Pixel-Art Styling & Animation)
        const mx = this.state.marioX;
        const my = this.state.marioY;
        const isStarActive = (this.state.marioInvincibleTimer > 0);

        ctx.save();

        // Invulnerability Damage Blink
        if (this.state.marioDamageCooldown > 0) {
            if (Math.floor(this.state.marioDamageCooldown / 6) % 2 === 0) {
                ctx.globalAlpha = 0.35;
            }
        }

        // Star Power Rainbow Aura & Motion Blur
        if (isStarActive) {
            const hue = (Date.now() / 4) % 360;
            ctx.shadowColor = `hsl(${hue}, 100%, 65%)`;
            ctx.shadowBlur = 18;
            // Ghost trail after-image
            ctx.fillStyle = `hsla(${hue}, 100%, 60%, 0.3)`;
            ctx.fillRect(mx - 8, my, 32, 44);
        }

        // A. Red Cap
        ctx.fillStyle = isStarActive ? `hsl(${(Date.now() / 3) % 360}, 100%, 55%)` : '#dc2626';
        ctx.beginPath();
        ctx.roundRect(mx + 6, my + 2, 22, 10, [6, 6, 0, 0]);
        ctx.fill();
        // Cap Visor
        ctx.fillRect(mx + 18, my + 8, 12, 4);
        // White 'M' Emblem
        ctx.fillStyle = '#ffffff';
        ctx.beginPath();
        ctx.arc(mx + 15, my + 6, 4.5, 0, Math.PI * 2);
        ctx.fill();
        ctx.fillStyle = '#dc2626';
        ctx.font = 'bold 6px sans-serif';
        ctx.fillText('M', mx + 13, my + 8);

        // B. Face & Nose
        ctx.fillStyle = '#fed7aa';
        ctx.fillRect(mx + 8, my + 12, 16, 12);
        // Round Nose
        ctx.beginPath();
        ctx.arc(mx + 23, my + 17, 4.5, 0, Math.PI * 2);
        ctx.fill();
        // Hair at back
        ctx.fillStyle = '#451a03';
        ctx.fillRect(mx + 4, my + 11, 6, 10);
        // Eye
        ctx.fillStyle = '#1e3a8a';
        ctx.fillRect(mx + 18, my + 13, 3, 5);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(mx + 19, my + 13, 1, 2);
        // Big Black Mustache
        ctx.fillStyle = '#0f172a';
        ctx.beginPath();
        ctx.roundRect(mx + 13, my + 19, 13, 5, [2, 4, 4, 2]);
        ctx.fill();

        // C. Red Shirt Body
        ctx.fillStyle = isStarActive ? `hsl(${(Date.now() / 3) % 360}, 100%, 55%)` : '#dc2626';
        ctx.fillRect(mx + 6, my + 24, 20, 10);

        // D. Blue Denim Dungarees / Overalls
        ctx.fillStyle = '#1d4ed8';
        ctx.fillRect(mx + 8, my + 26, 16, 12);
        // Golden Overall Buttons
        ctx.fillStyle = '#facc15';
        ctx.beginPath();
        ctx.arc(mx + 10, my + 28, 2, 0, Math.PI * 2);
        ctx.arc(mx + 18, my + 28, 2, 0, Math.PI * 2);
        ctx.fill();

        // E. Hands & Gloved Arms
        ctx.fillStyle = '#ffffff';
        if (this.state.isJumping) {
            // Jumping: Victory Fist raised high in air!
            ctx.beginPath();
            ctx.arc(mx + 24, my + 6, 5, 0, Math.PI * 2);
            ctx.fill();
            // Trailing back hand
            ctx.beginPath();
            ctx.arc(mx + 4, my + 28, 4.5, 0, Math.PI * 2);
            ctx.fill();
        } else {
            // Running: Hands pumping back and forth
            const armPuff = Math.sin(this.state.marioRunFrame * 0.4) * 5;
            ctx.beginPath();
            ctx.arc(mx + 24 + armPuff, my + 28, 4.5, 0, Math.PI * 2);
            ctx.arc(mx + 4 - armPuff, my + 28, 4.5, 0, Math.PI * 2);
            ctx.fill();
        }

        // F. Running Legs & Chunky Boots
        ctx.fillStyle = '#78350f';
        if (this.state.isJumping) {
            // Tucked jumping boots
            ctx.fillRect(mx + 7, my + 38, 9, 6);
            ctx.fillRect(mx + 18, my + 38, 9, 6);
        } else {
            // 3-frame running leg stride
            const stride = Math.sin(this.state.marioRunFrame * 0.35) * 6;
            ctx.fillRect(mx + 6 + stride, my + 38, 10, 6);
            ctx.fillRect(mx + 18 - stride, my + 38, 10, 6);
        }

        ctx.restore();

        // 13. Particles & Sparkles
        if (ents && ents.particles) {
            ents.particles.forEach(pt => {
                ctx.save();
                ctx.globalAlpha = Math.max(0, pt.alpha);
                ctx.fillStyle = pt.color;
                ctx.beginPath();
                ctx.arc(pt.x, pt.y, pt.radius, 0, Math.PI * 2);
                ctx.fill();
                ctx.restore();
            });
        }

        // 14. Floating Popups (+50 STOMP, +10 COIN, etc.)
        if (ents && ents.popups) {
            ents.popups.forEach(pp => {
                ctx.save();
                ctx.globalAlpha = Math.max(0, pp.alpha);
                ctx.fillStyle = pp.color;
                ctx.font = 'bold 15px "Segoe UI", sans-serif';
                ctx.shadowColor = '#000000';
                ctx.shadowBlur = 6;
                ctx.fillText(pp.text, pp.x, pp.y);
                ctx.restore();
            });
        }

        // 15. In-Canvas HUD Elements
        ctx.save();
        // Top-left Stage Countdown Timer & Distance
        const stageRemSec = Math.max(0, Math.ceil(this.state.stageTimeLeft || (this.data.timerDuration || 30)));
        ctx.fillStyle = stageRemSec <= 5 ? 'rgba(239, 68, 68, 0.88)' : 'rgba(15, 23, 42, 0.82)';
        ctx.beginPath();
        ctx.roundRect(16, 14, 210, 28, [14]);
        ctx.fill();
        ctx.strokeStyle = stageRemSec <= 5 ? '#ef4444' : 'rgba(255, 255, 255, 0.25)';
        ctx.lineWidth = 1.5;
        ctx.stroke();
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 12px sans-serif';
        ctx.fillText(`⏱️ Gerbang Soal: ${stageRemSec}s (${Math.round(this.state.marioDistance)}m)`, 24, 32);

        // Center Star Power Status Banner
        if (isStarActive) {
            const secLeft = Math.ceil(this.state.marioInvincibleTimer / 60);
            ctx.fillStyle = 'rgba(234, 179, 8, 0.9)';
            ctx.beginPath();
            ctx.roundRect(w / 2 - 100, 14, 200, 28, [14]);
            ctx.fill();
            ctx.fillStyle = '#0f172a';
            ctx.font = 'bold 12px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(`🌟 STAR POWER AKTIF (${secLeft}s)!`, w / 2, 32);
            ctx.textAlign = 'left';
        }

        // Top-right Stomp Combo
        if (this.state.marioStompCombo > 1) {
            ctx.fillStyle = 'rgba(239, 68, 68, 0.85)';
            ctx.beginPath();
            ctx.roundRect(w - 180, 14, 164, 28, [14]);
            ctx.fill();
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 12px sans-serif';
            ctx.fillText(`🔥 STOMP x${this.state.marioStompCombo}`, w - 165, 32);
        }
        ctx.restore();

        ctx.restore(); // Final restore from screen shake
    },

    triggerMarioQuestionCheckpoint: function(reason) {
        if (!this.state.isMarioRunning) return;
        this.state.isMarioRunning = false;
        clearInterval(this.state.marioLoopInterval);

        // Render konten soal checkpoint
        this.renderMarioModalQuestion(reason);

        // Tampilkan in-game overlay langsung di dalam arena fullscreen!
        const overlay = document.getElementById('marioCheckpointOverlay');
        if (overlay) {
            overlay.classList.remove('d-none');
        } else {
            const quizModalEl = document.getElementById('modalMarioQuiz');
            if (quizModalEl) {
                const modal = new bootstrap.Modal(quizModalEl);
                modal.show();
            } else {
                const quizBox = document.getElementById('quizBoxContainer');
                if (quizBox) quizBox.classList.remove('d-none');
                this.renderQuestion();
            }
        }
    },

    renderMarioModalQuestion: function(reason) {
        if (this.state.currentIdx >= this.data.questions.length || this.state.lives <= 0) {
            const overlay = document.getElementById('marioCheckpointOverlay');
            if (overlay) overlay.classList.add('d-none');

            const modalEl = document.getElementById('modalMarioQuiz');
            if (modalEl) {
                const instance = bootstrap.Modal.getInstance(modalEl);
                if (instance) instance.hide();
            }
            this.endGame();
            return;
        }

        const q = this.data.questions[this.state.currentIdx];
        const titleEl = document.getElementById('marioModalMainTitle');
        const counterEl = document.getElementById('marioModalCounter');
        const questionEl = document.getElementById('marioModalQuestion');
        const optionsEl = document.getElementById('marioModalOptions');

        let checkpointTitle = `TANTANGAN KUIS CHECKPOINT MARIO`;
        let subTitle = `🏁 WAKTU HABIS! GERBANG CHECKPOINT #${this.state.currentIdx + 1} (${this.state.currentIdx + 1}/${this.data.questions.length})`;
        
        if (reason === 'checkpoint_reached') {
            checkpointTitle = `🏁 GERBANG CHECKPOINT TERCAPAI!`;
            subTitle = `Waktu stage selesai! Jawab tantangan kuis #${this.state.currentIdx + 1} dari ${this.data.questions.length} untuk melanjutkan petualangan!`;
        }

        if (titleEl) titleEl.textContent = checkpointTitle;
        if (counterEl) counterEl.textContent = subTitle;
        if (questionEl) questionEl.textContent = q.pertanyaan;

        if (optionsEl) {
            optionsEl.innerHTML = ['a', 'b', 'c', 'd'].map(opt => {
                const text = q['opsi_' + opt];
                if (!text) return '';
                const safeText = String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                return `
                    <div class="col-12 col-md-6">
                        <button type="button" class="btn btn-outline-warning w-100 p-2.5 p-md-3 rounded-4 text-start d-flex align-items-center gap-2 gap-md-3 mario-option-btn shadow-sm text-white" onclick="window.GameEngine.submitMarioAnswer('${opt}')">
                            <span class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold fs-6" style="width: 36px; height: 36px; min-width: 36px;">
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
            this.playSound('powerup');
            this.state.combo++;
            if (this.state.combo > this.state.maxCombo) this.state.maxCombo = this.state.combo;
            this.state.correctCount++;

            // Berikan 6 detik Super Star Power kebal & cepat!
            this.state.marioInvincibleTimer = 60 * 6;

            const pointsGained = (parseInt(q.poin) || 10) + 25;
            this.state.score += pointsGained;
            this.updateHUD();

            this.showFeedback(true, '🎉 JAWABAN BENAR!', '🌟 SUPER STAR POWER AKTIF! KEBAL & BONUS POIN! 🚀');
        } else {
            this.playSound('wrong');
            this.state.combo = 0;
            this.state.lives--;
            this.updateHUD();

            this.showFeedback(false, '❌ JAWABAN KURANG TEPAT', `Kunci Jawaban: Opsi ${(q.kunci_jawaban || 'A').toUpperCase()}`);
        }

        setTimeout(() => {
            this.state.currentIdx++;
            this.state.isAnswered = false;

            // Sembunyikan in-game overlay seketika
            const overlay = document.getElementById('marioCheckpointOverlay');
            if (overlay) overlay.classList.add('d-none');

            const modalEl = document.getElementById('modalMarioQuiz');
            if (modalEl) {
                const instance = bootstrap.Modal.getInstance(modalEl);
                if (instance) instance.hide();
            }

            if (this.state.currentIdx >= this.data.questions.length || this.state.lives <= 0) {
                this.endGame();
            } else {
                // Reset waktu stage ke Durasi Timer per Soal untuk soal berikutnya!
                this.state.stageTimeLeft = this.data.timerDuration || 30;
                this.state.marioDamageCooldown = 0;
                this.startMarioRun();
            }
        }, 1400);
    },

    // 🏎️ MODE 5: ENDLESS TURBO CAR RACING RUNNER ENGINE
    initRacingCanvas: function() {
        const canvas = document.getElementById('racingCanvas');
        if (!canvas) return;
        this.racingCtx = canvas.getContext('2d');

        // Keyboard steer controls: ArrowLeft, ArrowRight, 'a', 'd', 'A', 'D'
        window.addEventListener('keydown', (e) => {
            if (this.data.gameType !== 'car_racing') return;
            if (e.code === 'ArrowLeft' || e.key === 'a' || e.key === 'A') {
                e.preventDefault();
                this.racingSteer(-1);
            } else if (e.code === 'ArrowRight' || e.key === 'd' || e.key === 'D') {
                e.preventDefault();
                this.racingSteer(1);
            }
        });

        // Touch & Pointer click on canvas: Left half steers left, Right half steers right
        canvas.addEventListener('pointerdown', (e) => {
            if (this.data.gameType !== 'car_racing') return;
            e.preventDefault();
            const rect = canvas.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            if (clickX < rect.width * 0.5) {
                this.racingSteer(-1);
            } else {
                this.racingSteer(1);
            }
        });
    },

    racingSteer: function(dir) {
        if (this.state.isEnded) return;
        this.state.racingLane = Math.max(0, Math.min(2, this.state.racingLane + dir));
        const laneXMap = [250, 400, 550];
        this.state.racingTargetX = laneXMap[this.state.racingLane];
    },

    startRacingGame: function() {
        if (this.state.racingLoopInterval) clearInterval(this.state.racingLoopInterval);

        // Reset racing parameters
        const totalDuration = this.data.timerDuration || 30;
        this.state.racingTimeLeft = totalDuration;
        this.state.racingLane = 1;
        this.state.racingCarX = 400;
        this.state.racingTargetX = 400;
        this.state.racingSpeed = 11;
        this.state.racingBaseSpeed = 11;
        this.state.racingBoostTimer = 0;
        this.state.racingDistance = 0;
        this.state.racingIsAtGate = false;
        this.state.racingCrashShakeTimer = 0;
        this.state.racingRoadOffset = 0;
        this.state.racingPickups = [];
        this.state.racingExplosionParticles = [];
        this.state.racingCrashParticles = [];

        this.updateHUD();
        this.updateRacingHUD();

        const canvas = document.getElementById('racingCanvas');
        if (canvas) this.racingCtx = canvas.getContext('2d');

        this.state.racingLoopInterval = setInterval(() => {
            this.updateRacingPhysics();
            this.drawRacingCanvas();
        }, 1000 / 60);
    },

    updateRacingPhysics: function() {
        if (this.state.isEnded) return;

        const totalDuration = this.data.timerDuration || 30;
        if (typeof this.state.racingTimeLeft === 'undefined' || this.state.racingTimeLeft <= 0) {
            this.state.racingTimeLeft = totalDuration;
        }

        // Smooth steering interpolation towards target lane X
        this.state.racingCarX += (this.state.racingTargetX - this.state.racingCarX) * 0.18;

        // Speed calculation: Boost active gives 2.2x speed!
        let currentSpeed = this.state.racingBaseSpeed;
        if (this.state.racingBoostTimer > 0) {
            this.state.racingBoostTimer--;
            currentSpeed = 24; // Dorongan kecepatan nitro!
            if (this.state.racingBoostTimer % 4 === 0) {
                this.createNitroFlame(this.state.racingCarX - 16, 310);
                this.createNitroFlame(this.state.racingCarX + 16, 310);
            }
        }

        // If currently stopped at a barrier gate, speed is 0
        if (this.state.racingIsAtGate) {
            currentSpeed = 0;
        }

        this.state.racingSpeed = currentSpeed;

        // Distance progression & countdown timer presisi 1/60 detik per frame
        if (!this.state.racingIsAtGate) {
            this.state.racingDistance += (currentSpeed * 0.15);
            this.state.racingRoadOffset += currentSpeed;
            this.state.racingTimeLeft -= (1 / 60);
        }

        // Tepat ketika durasi waktu soal per detik HABIS (atau <= 0): munculkan gerbang penghalang!
        if (this.state.racingTimeLeft <= 0 && !this.state.racingIsAtGate && this.state.currentIdx < this.data.questions.length) {
            this.state.racingTimeLeft = 0;
            this.state.racingIsAtGate = true;
            this.playSound('bump');
            this.openBarrierGateQuestion();
        }

        // Spawn roadside coins & pickups
        if (!this.state.racingIsAtGate && Math.random() < 0.04 && this.state.racingPickups.length < 5) {
            const lane = Math.floor(Math.random() * 3);
            const isNitro = Math.random() < 0.25;
            this.state.racingPickups.push({
                lane: lane,
                y: 100, // Vanishing point
                type: isNitro ? 'nitro' : 'coin',
                collected: false
            });
        }

        // Update pickups movement & collision
        for (let i = this.state.racingPickups.length - 1; i >= 0; i--) {
            const p = this.state.racingPickups[i];
            p.y += currentSpeed * 0.7;

            // Collision check with car near y = 275
            if (!p.collected && p.y >= 250 && p.y <= 295 && p.lane === this.state.racingLane) {
                p.collected = true;
                if (p.type === 'coin') {
                    this.state.coins++;
                    this.state.score += 15;
                    this.playSound('coin');
                    this.updateHUD();
                } else {
                    this.state.score += 25;
                    this.state.racingBoostTimer = Math.max(this.state.racingBoostTimer, 50);
                    this.playSound('turbo');
                    this.updateHUD();
                }
            }

            if (p.y > 380 || p.collected) {
                this.state.racingPickups.splice(i, 1);
            }
        }

        // Update explosion particles
        for (let i = this.state.racingExplosionParticles.length - 1; i >= 0; i--) {
            const pt = this.state.racingExplosionParticles[i];
            pt.x += pt.vx;
            pt.y += pt.vy;
            pt.vy += 0.15; // Gravity
            pt.alpha -= 0.022;
            pt.size *= 0.97;
            if (pt.alpha <= 0) {
                this.state.racingExplosionParticles.splice(i, 1);
            }
        }

        // Update crash particles
        for (let i = this.state.racingCrashParticles.length - 1; i >= 0; i--) {
            const cp = this.state.racingCrashParticles[i];
            cp.x += cp.vx;
            cp.y += cp.vy;
            cp.alpha -= 0.03;
            cp.size *= 0.96;
            if (cp.alpha <= 0) {
                this.state.racingCrashParticles.splice(i, 1);
            }
        }

        this.updateRacingHUD();
    },

    createNitroFlame: function(x, y) {
        if (!this.state.racingCrashParticles) this.state.racingCrashParticles = [];
        this.state.racingCrashParticles.push({
            x: x + (Math.random() - 0.5) * 6,
            y: y,
            vx: (Math.random() - 0.5) * 2,
            vy: 4 + Math.random() * 4,
            size: 8 + Math.random() * 6,
            color: Math.random() > 0.5 ? '#00f5d4' : '#ff9e00',
            alpha: 0.9
        });
    },

    createBarrierExplosion: function(bx, by) {
        this.playSound('explosion');
        this.playSound('powerup');
        this.state.screenShake = 16;
        const colors = ['#ff0055', '#ff5400', '#ffd60a', '#00f5d4', '#ffffff', '#7209b7'];
        for (let i = 0; i < 48; i++) {
            const angle = Math.random() * Math.PI * 2;
            const spd = 3 + Math.random() * 9;
            this.state.racingExplosionParticles.push({
                x: bx + (Math.random() - 0.5) * 40,
                y: by + (Math.random() - 0.5) * 20,
                vx: Math.cos(angle) * spd,
                vy: Math.sin(angle) * spd - 2,
                size: 6 + Math.random() * 12,
                color: colors[Math.floor(Math.random() * colors.length)],
                alpha: 1.0
            });
        }
    },

    createCrashSparks: function(cx, cy) {
        this.state.screenShake = 12;
        for (let i = 0; i < 24; i++) {
            const angle = Math.random() * Math.PI * 2;
            const spd = 2 + Math.random() * 6;
            this.state.racingCrashParticles.push({
                x: cx + (Math.random() - 0.5) * 20,
                y: cy - 15 + (Math.random() - 0.5) * 10,
                vx: Math.cos(angle) * spd,
                vy: Math.sin(angle) * spd - 1,
                size: 4 + Math.random() * 8,
                color: Math.random() > 0.4 ? '#f59e0b' : '#ef4444',
                alpha: 1.0
            });
        }
    },

    openBarrierGateQuestion: function() {
        if (this.state.currentIdx >= this.data.questions.length) {
            this.endGame();
            return;
        }

        const q = this.data.questions[this.state.currentIdx];
        const overlay = document.getElementById('racingBarrierOverlay');
        const headerEl = document.getElementById('racingBarrierHeader');
        const questionEl = document.getElementById('racingQuestionText');
        const crashNotice = document.getElementById('racingCrashNotice');

        if (crashNotice) crashNotice.classList.add('d-none');
        if (headerEl) headerEl.textContent = `🛑 GERBANG PENGHALANG BALAPAN #${this.state.currentIdx + 1} (${this.state.currentIdx + 1}/${this.data.questions.length})`;
        if (questionEl) questionEl.textContent = q.pertanyaan;

        this.renderRacingOptions();

        if (overlay) overlay.classList.remove('d-none');
    },

    renderRacingOptions: function() {
        const q = this.data.questions[this.state.currentIdx];
        const optionsContainer = document.getElementById('racingOptionsContainer');
        if (!optionsContainer || !q) return;

        // Ambil opsi yang tersedia
        const rawOptions = [
            { key: 'a', text: q.opsi_a },
            { key: 'b', text: q.opsi_b },
            { key: 'c', text: q.opsi_c },
            { key: 'd', text: q.opsi_d }
        ].filter(opt => opt.text && String(opt.text).trim() !== '');

        // Acak urutan opsi jawaban (Shuffle Options)
        for (let i = rawOptions.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [rawOptions[i], rawOptions[j]] = [rawOptions[j], rawOptions[i]];
        }

        optionsContainer.innerHTML = rawOptions.map((opt, idx) => {
            const safeText = String(opt.text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            return `
                <div class="col-12 col-md-6">
                    <button type="button" class="btn btn-outline-warning w-100 p-2.5 p-md-3 rounded-4 text-start d-flex align-items-center gap-2 gap-md-3 shadow-sm text-white hover-scale" onclick="window.GameEngine.submitRacingAnswer('${opt.key}')">
                        <span class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center fw-bold fs-6" style="width: 36px; height: 36px; min-width: 36px; border: 2px solid #ffffff;">
                            ${String.fromCharCode(65 + idx)}
                        </span>
                        <span class="fw-semibold text-white fs-6 flex-grow-1">${safeText}</span>
                    </button>
                </div>
            `;
        }).join('');
    },

    submitRacingAnswer: function(selectedOpt) {
        if (this.state.isAnswered) return;

        const q = this.data.questions[this.state.currentIdx];
        const keyAnswer = (q.kunci_jawaban || 'a').toString().trim().toLowerCase();
        const isCorrect = (selectedOpt.toLowerCase() === keyAnswer);

        if (isCorrect) {
            // ==============================================================
            // KETENTUAN 1: JIKA PEMAIN MEMILIH OPSI JAWABAN YANG BENAR
            // - Poin bertambah +10
            // - Efek visual ledakan pada gerbang
            // - Beri karakter dorongan kecepatan (speed boost) selama 2 detik
            // - Karakter lanjut berlari ke soal berikutnya
            // ==============================================================
            this.state.isAnswered = true;
            this.state.score += (parseInt(q.poin) || 10);
            this.state.correctCount++;
            this.state.combo++;
            if (this.state.combo > this.state.maxCombo) this.state.maxCombo = this.state.combo;
            this.updateHUD();

            // Efek visual ledakan pada gerbang penghalang
            this.createBarrierExplosion(400, 230);

            // Beri karakter dorongan kecepatan (speed boost) selama 2 detik (120 frames @ 60 FPS)
            this.state.racingBoostTimer = 120;
            this.playSound('turbo');

            // Sembunyikan barrier overlay
            const overlay = document.getElementById('racingBarrierOverlay');
            if (overlay) overlay.classList.add('d-none');

            // Reset durasi waktu per soal untuk balapan menuju soal berikutnya!
            this.state.racingTimeLeft = this.data.timerDuration || 30;

            // Karakter lanjut melaju ke soal berikutnya
            this.state.racingIsAtGate = false;
            this.state.currentIdx++;
            this.state.isAnswered = false;

            // Jika semua soal selesai, tamatkan game
            if (this.state.currentIdx >= this.data.questions.length) {
                setTimeout(() => { this.endGame(); }, 1200);
            }
        } else {
            // ==============================================================
            // KETENTUAN 2: JIKA PEMAIN MEMILIH OPSI JAWABAN YANG SALAH
            // - Karakter memainkan animasi menabrak
            // - Game berhenti bergerak maju (paused)
            // - Soal yang sama tetap ditampilkan di layar (opsi jawaban diacak ulang)
            // - Karakter tidak boleh bergerak maju sampai pemain berhasil memilih jawaban yang benar
            // ==============================================================
            this.state.combo = 0;
            this.updateHUD();

            // Karakter memainkan animasi menabrak
            this.state.racingCrashShakeTimer = 35;
            this.createCrashSparks(this.state.racingCarX, 275);
            this.playSound('wrong');
            this.playSound('bump');

            // Game berhenti bergerak maju (paused)
            this.state.racingIsAtGate = true;
            this.state.racingSpeed = 0;
            this.state.racingBoostTimer = 0;

            // Tampilkan notifikasi menabrak & acak ulang opsi jawaban
            const crashNotice = document.getElementById('racingCrashNotice');
            if (crashNotice) {
                crashNotice.classList.remove('d-none');
            }

            // Opsi jawaban diacak ulang (shuffled) dan tetap di layar!
            this.renderRacingOptions();
        }
    },

    updateRacingHUD: function() {
        const totalDuration = this.data.timerDuration || 30;
        const speedKmh = Math.round(this.state.racingSpeed * 6.5);
        const dist = Math.round(this.state.racingDistance);
        const remSec = Math.max(0, Math.ceil(this.state.racingTimeLeft || 0));
        const progressPct = Math.max(0, Math.min(100, Math.round(((totalDuration - (this.state.racingTimeLeft || 0)) / totalDuration) * 100)));

        const isBoost = this.state.racingBoostTimer > 0;

        ['racingSpeedVal', 'racingSpeedValMobile'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = speedKmh;
        });

        const boostInd = document.getElementById('racingBoostIndicator');
        if (boostInd) {
            if (isBoost) boostInd.classList.remove('d-none');
            else boostInd.classList.add('d-none');
        }

        const boostBadgeMobile = document.getElementById('racingBoostBadgeMobile');
        if (boostBadgeMobile) {
            boostBadgeMobile.textContent = isBoost ? '🔥 NITRO 2s!' : '🚀 READY';
            boostBadgeMobile.className = isBoost ? 'badge bg-danger text-white rounded-pill px-2 py-0.5 fw-bold' : 'badge bg-dark text-warning border border-warning rounded-pill px-2 py-0.5 fw-bold';
        }

        ['racingGateProgressBar', 'racingGateProgressBarMobile'].forEach(id => {
            const bar = document.getElementById(id);
            if (bar) {
                bar.style.width = progressPct + '%';
                bar.textContent = `⏱️ ${remSec}s Menuju Gerbang (${dist}m)`;
                if (remSec <= 4) {
                    bar.className = 'progress-bar bg-danger text-white fw-bold progress-bar-striped progress-bar-animated';
                } else if (remSec <= 8) {
                    bar.className = 'progress-bar bg-warning text-dark fw-bold progress-bar-striped progress-bar-animated';
                } else {
                    bar.className = 'progress-bar bg-info text-dark fw-bold progress-bar-striped progress-bar-animated';
                }
            }
        });

        ['racingCoinVal', 'racingCoinValMobile'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = this.state.coins;
        });

        const mobScore = document.getElementById('currentScoreRacingMobile');
        if (mobScore) mobScore.textContent = this.state.score;
    },

    drawRacingCanvas: function() {
        const ctx = this.racingCtx;
        if (!ctx) return;

        const W = 800;
        const H = 360;

        // Apply screen shake if active
        ctx.save();
        if (this.state.screenShake > 0) {
            const shake = (Math.random() - 0.5) * this.state.screenShake;
            ctx.translate(shake, shake);
            this.state.screenShake--;
        }

        ctx.clearRect(0, 0, W, H);

        // 1. SKY GRADIENT (Synthwave Cyber Sunset)
        const skyGrad = ctx.createLinearGradient(0, 0, 0, 100);
        skyGrad.addColorStop(0, '#090214');
        skyGrad.addColorStop(0.6, '#3b0764');
        skyGrad.addColorStop(1, '#be185d');
        ctx.fillStyle = skyGrad;
        ctx.fillRect(0, 0, W, 100);

        // Neon Sun on the horizon
        ctx.save();
        const sunGrad = ctx.createRadialGradient(400, 100, 5, 400, 100, 48);
        sunGrad.addColorStop(0, '#fef08a');
        sunGrad.addColorStop(0.5, '#f43f5e');
        sunGrad.addColorStop(1, 'rgba(244, 63, 94, 0)');
        ctx.fillStyle = sunGrad;
        ctx.beginPath();
        ctx.arc(400, 100, 48, Math.PI, 0, false);
        ctx.fill();
        ctx.restore();

        // Distant Cyber City Skyline Silhouettes
        ctx.fillStyle = '#170c2e';
        const buildings = [
            [20, 40, 24], [55, 60, 30], [95, 35, 20], [130, 50, 36], [180, 70, 26],
            [220, 45, 32], [265, 60, 28], [510, 55, 32], [555, 75, 26], [590, 40, 34],
            [640, 65, 30], [680, 45, 22], [720, 70, 28], [760, 50, 30]
        ];
        buildings.forEach(b => {
            ctx.fillRect(b[0], 100 - b[1], b[2], b[1]);
        });

        // 2. ROADSIDE GRASS & HIGHWAY (Pseudo-3D Perspective)
        const terrainGrad = ctx.createLinearGradient(0, 100, 0, H);
        terrainGrad.addColorStop(0, '#0f172a');
        terrainGrad.addColorStop(1, '#020617');
        ctx.fillStyle = terrainGrad;
        ctx.fillRect(0, 100, W, H - 100);

        // Highway Asphalt Polygon
        ctx.beginPath();
        ctx.moveTo(330, 100);
        ctx.lineTo(470, 100);
        ctx.lineTo(740, H);
        ctx.lineTo(60, H);
        ctx.closePath();
        ctx.fillStyle = '#111827';
        ctx.fill();

        // 3. CURB STRIPES (Bahu jalan bergantian merah-putih)
        const offset = (this.state.racingRoadOffset || 0) % 40;
        const numSegments = 16;
        for (let i = 0; i < numSegments; i++) {
            const t1 = (i * 2.5 + (offset / 16)) / numSegments;
            const t2 = ((i + 1) * 2.5 + (offset / 16)) / numSegments;
            if (t1 > 1) continue;

            const y1 = 100 + Math.pow(t1, 1.8) * 260;
            const y2 = 100 + Math.pow(Math.min(1, t2), 1.8) * 260;

            const leftX1 = 330 - (330 - 60) * Math.pow(t1, 1.8);
            const leftX2 = 330 - (330 - 60) * Math.pow(Math.min(1, t2), 1.8);

            const rightX1 = 470 + (740 - 470) * Math.pow(t1, 1.8);
            const rightX2 = 470 + (740 - 470) * Math.pow(Math.min(1, t2), 1.8);

            const isRed = (i % 2 === 0);
            ctx.fillStyle = isRed ? '#dc2626' : '#f8fafc';

            // Left curb
            ctx.beginPath();
            ctx.moveTo(leftX1, y1);
            ctx.lineTo(leftX1 - 12 * Math.pow(t1, 1.5), y1);
            ctx.lineTo(leftX2 - 12 * Math.pow(t2, 1.5), y2);
            ctx.lineTo(leftX2, y2);
            ctx.fill();

            // Right curb
            ctx.beginPath();
            ctx.moveTo(rightX1, y1);
            ctx.lineTo(rightX1 + 12 * Math.pow(t1, 1.5), y1);
            ctx.lineTo(rightX2 + 12 * Math.pow(t2, 1.5), y2);
            ctx.lineTo(rightX2, y2);
            ctx.fill();
        }

        // 4. LANE DIVIDERS (Dashed neon lines for 3 lanes)
        for (let i = 0; i < 12; i++) {
            const t = (i * 3 + (offset / 12)) / 12;
            if (t < 0.05 || t > 0.98) continue;
            const y = 100 + Math.pow(t, 2) * 255;
            const h = 8 + Math.pow(t, 2) * 24;

            // Lane 1 divider
            const leftLaneX = (330 + (470 - 330) * 0.33) + ((60 + (740 - 60) * 0.33) - (330 + (470 - 330) * 0.33)) * Math.pow(t, 2);
            ctx.fillStyle = '#06b6d4';
            ctx.fillRect(leftLaneX - 1.5, y, 3 + t * 3, h);

            // Lane 2 divider
            const rightLaneX = (330 + (470 - 330) * 0.67) + ((60 + (740 - 60) * 0.67) - (330 + (470 - 330) * 0.67)) * Math.pow(t, 2);
            ctx.fillStyle = '#06b6d4';
            ctx.fillRect(rightLaneX - 1.5, y, 3 + t * 3, h);
        }

        // 5. ROAD PICKUPS (Coins & Nitro Cells)
        if (this.state.racingPickups) {
            this.state.racingPickups.forEach(p => {
                const t = Math.max(0, Math.min(1, (p.y - 100) / 260));
                const laneCenters = [
                    (330 + (60 - 330) * t) + (140 + (680 - 140) * t) * 0.17,
                    (330 + (60 - 330) * t) + (140 + (680 - 140) * t) * 0.5,
                    (330 + (60 - 330) * t) + (140 + (680 - 140) * t) * 0.83
                ];
                const px = laneCenters[p.lane];
                const py = p.y;
                const size = 10 + t * 18;

                if (p.type === 'coin') {
                    ctx.save();
                    ctx.fillStyle = '#fbbf24';
                    ctx.beginPath();
                    ctx.arc(px, py, size * 0.5, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.strokeStyle = '#d97706';
                    ctx.lineWidth = 2;
                    ctx.stroke();
                    ctx.fillStyle = '#78350f';
                    ctx.font = `bold ${Math.round(size * 0.5)}px sans-serif`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText('🪙', px, py);
                    ctx.restore();
                } else {
                    ctx.save();
                    ctx.fillStyle = '#06b6d4';
                    ctx.fillRect(px - size * 0.4, py - size * 0.6, size * 0.8, size * 1.2);
                    ctx.fillStyle = '#fef08a';
                    ctx.font = `bold ${Math.round(size * 0.5)}px sans-serif`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText('⚡', px, py);
                    ctx.restore();
                }
            });
        }

        // 6. GERBANG PENGHALANG (Laser Barrier Gate)
        const timeLeft = typeof this.state.racingTimeLeft !== 'undefined' ? this.state.racingTimeLeft : 30;
        if (timeLeft <= 3.5 || this.state.racingIsAtGate) {
            const gateT = this.state.racingIsAtGate ? 1 : Math.max(0, 1 - (timeLeft / 3.5));
            const gy = 100 + Math.pow(gateT, 1.5) * 140; // Moves down to y = 240
            const gw = 180 + Math.pow(gateT, 1.5) * 440; // Expands across the road
            const gx = 400 - gw * 0.5;

            // Heavy Steel Side Pillars
            ctx.fillStyle = '#1e293b';
            ctx.fillRect(gx - 14, gy - 60, 20, 80);
            ctx.fillRect(gx + gw - 6, gy - 60, 20, 80);

            // Hazard Stripes on Pillars
            ctx.fillStyle = '#eab308';
            ctx.fillRect(gx - 10, gy - 50, 12, 10);
            ctx.fillRect(gx - 10, gy - 30, 12, 10);
            ctx.fillRect(gx + gw - 2, gy - 50, 12, 10);
            ctx.fillRect(gx + gw - 2, gy - 30, 12, 10);

            // Overhead Girder Beam
            ctx.fillStyle = '#0f172a';
            ctx.fillRect(gx - 10, gy - 60, gw + 20, 22);
            ctx.strokeStyle = '#ef4444';
            ctx.lineWidth = 2;
            ctx.strokeRect(gx - 10, gy - 60, gw + 20, 22);

            // Girder Signboard
            ctx.fillStyle = '#dc2626';
            ctx.fillRect(gx + gw * 0.15, gy - 56, gw * 0.7, 14);
            ctx.fillStyle = '#ffffff';
            ctx.font = `bold ${Math.max(8, Math.round(11 * gateT))}px sans-serif`;
            ctx.textAlign = 'center';
            ctx.fillText(`🛑 GERBANG SOAL #${this.state.currentIdx + 1} (⏱️ ${Math.ceil(timeLeft)}s)`, 400, gy - 45);

            // Pulsing Red Laser Energy Grid Barrier
            const laserAlpha = 0.5 + Math.sin(Date.now() / 100) * 0.35;
            ctx.save();
            ctx.strokeStyle = `rgba(239, 68, 68, ${laserAlpha})`;
            ctx.lineWidth = 4 * gateT;
            for (let l = 0; l < 4; l++) {
                const ly = gy - 36 + l * 12 * gateT;
                ctx.beginPath();
                ctx.moveTo(gx, ly);
                ctx.lineTo(gx + gw, ly);
                ctx.stroke();
            }
            ctx.restore();
        }

        // 7. PLAYER RACING CAR
        let cx = this.state.racingCarX;
        let cy = 275;

        // Apply crash shake if active
        if (this.state.racingCrashShakeTimer > 0) {
            cx += (Math.random() - 0.5) * 14;
            cy += (Math.random() - 0.5) * 8;
        }

        // Car Shadow
        ctx.fillStyle = 'rgba(0, 0, 0, 0.45)';
        ctx.beginPath();
        ctx.ellipse(cx, cy + 30, 36, 12, 0, 0, Math.PI * 2);
        ctx.fill();

        // Headlight Beams on Asphalt
        ctx.save();
        const beamGrad = ctx.createLinearGradient(cx, cy, cx, cy - 90);
        beamGrad.addColorStop(0, 'rgba(254, 240, 138, 0.45)');
        beamGrad.addColorStop(1, 'rgba(254, 240, 138, 0)');
        ctx.fillStyle = beamGrad;
        ctx.beginPath();
        ctx.moveTo(cx - 24, cy - 25);
        ctx.lineTo(cx - 50, cy - 100);
        ctx.lineTo(cx + 50, cy - 100);
        ctx.lineTo(cx + 24, cy - 25);
        ctx.closePath();
        ctx.fill();
        ctx.restore();

        // Car Wheels (4 tires)
        ctx.fillStyle = '#0f172a';
        ctx.fillRect(cx - 30, cy - 20, 8, 16); // Front Left
        ctx.fillRect(cx + 22, cy - 20, 8, 16); // Front Right
        ctx.fillRect(cx - 32, cy + 10, 9, 20); // Rear Left
        ctx.fillRect(cx + 23, cy + 10, 9, 20); // Rear Right

        // Main Car Body (Aerodynamic Red Sports Car)
        const carGrad = ctx.createLinearGradient(cx - 24, cy, cx + 24, cy);
        carGrad.addColorStop(0, '#991b1b');
        carGrad.addColorStop(0.5, '#ef4444');
        carGrad.addColorStop(1, '#991b1b');
        ctx.fillStyle = carGrad;

        ctx.beginPath();
        ctx.moveTo(cx - 16, cy - 35); // Nose
        ctx.lineTo(cx + 16, cy - 35);
        ctx.lineTo(cx + 24, cy - 10);
        ctx.lineTo(cx + 26, cy + 26);
        ctx.lineTo(cx - 26, cy + 26);
        ctx.lineTo(cx - 24, cy - 10);
        ctx.closePath();
        ctx.fill();
        ctx.strokeStyle = '#fca5a5';
        ctx.lineWidth = 1.5;
        ctx.stroke();

        // Tinted Windshield Glass
        ctx.fillStyle = '#1e293b';
        ctx.beginPath();
        ctx.moveTo(cx - 12, cy - 18);
        ctx.lineTo(cx + 12, cy - 18);
        ctx.lineTo(cx + 16, cy - 2);
        ctx.lineTo(cx - 16, cy - 2);
        ctx.closePath();
        ctx.fill();
        ctx.strokeStyle = '#38bdf8';
        ctx.lineWidth = 1;
        ctx.stroke();

        // Cockpit Roof & Racing Stripe
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(cx - 3, cy - 34, 6, 56);

        // Rear Spoiler Wing
        ctx.fillStyle = '#020617';
        ctx.fillRect(cx - 28, cy + 24, 56, 6);
        ctx.fillStyle = '#dc2626';
        ctx.fillRect(cx - 30, cy + 22, 6, 10);
        ctx.fillRect(cx + 24, cy + 22, 6, 10);

        // Rear LED Lightbar
        ctx.fillStyle = '#ef4444';
        ctx.fillRect(cx - 20, cy + 20, 40, 3);

        // 8. NITRO EXHAUST FLAMES (when boost is active)
        if (this.state.racingBoostTimer > 0) {
            ctx.save();
            const flameLen = 25 + Math.random() * 20;
            // Left flame
            const fGrad1 = ctx.createLinearGradient(cx - 14, cy + 28, cx - 14, cy + 28 + flameLen);
            fGrad1.addColorStop(0, '#ffffff');
            fGrad1.addColorStop(0.3, '#00f5d4');
            fGrad1.addColorStop(1, 'rgba(0, 245, 212, 0)');
            ctx.fillStyle = fGrad1;
            ctx.beginPath();
            ctx.moveTo(cx - 18, cy + 28);
            ctx.lineTo(cx - 10, cy + 28);
            ctx.lineTo(cx - 14, cy + 28 + flameLen);
            ctx.fill();

            // Right flame
            const fGrad2 = ctx.createLinearGradient(cx + 14, cy + 28, cx + 14, cy + 28 + flameLen);
            fGrad2.addColorStop(0, '#ffffff');
            fGrad2.addColorStop(0.3, '#00f5d4');
            fGrad2.addColorStop(1, 'rgba(0, 245, 212, 0)');
            ctx.fillStyle = fGrad2;
            ctx.beginPath();
            ctx.moveTo(cx + 10, cy + 28);
            ctx.lineTo(cx + 18, cy + 28);
            ctx.lineTo(cx + 14, cy + 28 + flameLen);
            ctx.fill();
            ctx.restore();
        }

        // 9. EXPLOSION PARTICLES (Barrier gate destruction)
        if (this.state.racingExplosionParticles) {
            this.state.racingExplosionParticles.forEach(pt => {
                ctx.save();
                ctx.globalAlpha = Math.max(0, pt.alpha);
                ctx.fillStyle = pt.color;
                ctx.beginPath();
                ctx.arc(pt.x, pt.y, Math.max(1, pt.size), 0, Math.PI * 2);
                ctx.fill();
                ctx.restore();
            });
        }

        // 10. CRASH PARTICLES (Sparks & smoke on collision)
        if (this.state.racingCrashParticles) {
            this.state.racingCrashParticles.forEach(cp => {
                ctx.save();
                ctx.globalAlpha = Math.max(0, cp.alpha);
                ctx.fillStyle = cp.color;
                ctx.beginPath();
                ctx.arc(cp.x, cp.y, Math.max(1, cp.size), 0, Math.PI * 2);
                ctx.fill();
                ctx.restore();
            });
        }

        // 11. IN-CANVAS HUD OVERLAY BADGE
        const remSecCanvas = Math.max(0, Math.ceil(this.state.racingTimeLeft || 0));
        ctx.save();
        ctx.fillStyle = 'rgba(0, 0, 0, 0.65)';
        ctx.roundRect(14, 14, 235, 32, 16);
        ctx.fill();
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 12px sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText(`⏱️ Gerbang: ${remSecCanvas}s (${Math.round(this.state.racingDistance)}m)`, 24, 34);

        if (this.state.racingBoostTimer > 0) {
            ctx.fillStyle = 'rgba(239, 68, 68, 0.85)';
            ctx.roundRect(W - 200, 14, 186, 32, 16);
            ctx.fill();
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 12px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(`🔥 NITRO BOOST 2s! ⚡`, W - 107, 34);
        }
        ctx.restore();

        ctx.restore(); // Restore shake
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

    updateStageTimerHUD: function(remSec, pct) {
        const valPct = Math.max(0, Math.min(100, Math.round(pct)));
        ['marioStaminaBar', 'marioStaminaBarMobile'].forEach(id => {
            const bar = document.getElementById(id);
            if (bar) {
                bar.style.width = valPct + '%';
                bar.textContent = `${remSec}s`;
                if (remSec <= 5) {
                    bar.className = 'progress-bar bg-danger text-white fw-bold progress-bar-striped progress-bar-animated';
                } else if (remSec <= 10) {
                    bar.className = 'progress-bar bg-warning text-dark fw-bold progress-bar-striped progress-bar-animated';
                } else {
                    bar.className = 'progress-bar bg-info text-dark fw-bold progress-bar-striped progress-bar-animated';
                }
            }
        });

        const dist = Math.round(this.state.marioDistance);
        const target = Math.round(this.state.marioNextCheckpoint);
        ['marioDistVal', 'marioDistValMobile'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = dist;
        });
        ['marioTargetVal', 'marioTargetValMobile'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = target;
        });
    },

    updateStaminaHUD: function(val) {
        const totalDuration = this.data.timerDuration || 30;
        const remSec = Math.round((val / 100) * totalDuration);
        this.updateStageTimerHUD(remSec, val);
    },

    // Synthetic Retro 8-Bit Sound Synthesizer (Web Audio API)
    playSound: function(type) {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            const now = ctx.currentTime;

            if (type === 'jump') {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'square';
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.setValueAtTime(160, now);
                osc.frequency.exponentialRampToValueAtTime(520, now + 0.14);
                gain.gain.setValueAtTime(0.14, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.14);
                osc.start(now);
                osc.stop(now + 0.14);
            } else if (type === 'coin') {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.setValueAtTime(987.77, now); // B5
                osc.frequency.setValueAtTime(1318.51, now + 0.08); // E6
                gain.gain.setValueAtTime(0.18, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.32);
                osc.start(now);
                osc.stop(now + 0.32);
            } else if (type === 'stomp') {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'triangle';
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.setValueAtTime(180, now);
                osc.frequency.exponentialRampToValueAtTime(45, now + 0.18);
                gain.gain.setValueAtTime(0.28, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.18);
                osc.start(now);
                osc.stop(now + 0.18);
            } else if (type === 'bump') {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'square';
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.setValueAtTime(120, now);
                osc.frequency.setValueAtTime(80, now + 0.06);
                gain.gain.setValueAtTime(0.16, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.1);
                osc.start(now);
                osc.stop(now + 0.1);
            } else if (type === 'powerup') {
                const notes = [261.63, 329.63, 392.00, 523.25, 659.25, 783.99]; // C-E-G-C-E-G Fanfare
                notes.forEach((freq, idx) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'triangle';
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.frequency.setValueAtTime(freq, now + idx * 0.07);
                    gain.gain.setValueAtTime(0.16, now + idx * 0.07);
                    gain.gain.linearRampToValueAtTime(0.01, now + (idx + 1) * 0.07);
                    osc.start(now + idx * 0.07);
                    osc.stop(now + (idx + 1) * 0.07);
                });
            } else if (type === 'correct') {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.setValueAtTime(523.25, now);
                osc.frequency.setValueAtTime(659.25, now + 0.1);
                osc.frequency.setValueAtTime(783.99, now + 0.2);
                gain.gain.setValueAtTime(0.2, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.35);
                osc.start(now);
                osc.stop(now + 0.35);
            } else if (type === 'wrong') {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sawtooth';
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.setValueAtTime(220, now);
                osc.frequency.setValueAtTime(146.83, now + 0.12);
                gain.gain.setValueAtTime(0.25, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.3);
                osc.start(now);
                osc.stop(now + 0.3);
            } else if (type === 'explosion') {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sawtooth';
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.setValueAtTime(140, now);
                osc.frequency.exponentialRampToValueAtTime(30, now + 0.45);
                gain.gain.setValueAtTime(0.35, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.45);
                osc.start(now);
                osc.stop(now + 0.45);
            } else if (type === 'turbo') {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'triangle';
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.setValueAtTime(220, now);
                osc.frequency.exponentialRampToValueAtTime(780, now + 0.35);
                gain.gain.setValueAtTime(0.25, now);
                gain.gain.linearRampToValueAtTime(0.01, now + 0.35);
                osc.start(now);
                osc.stop(now + 0.35);
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
        ['currentScore', 'currentScoreMobile'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = this.state.score;
        });
        ['comboBadge', 'comboBadgeMobile'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = `${this.state.combo}x 🔥`;
        });
        ['marioCoinVal', 'marioCoinValMobile'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = this.state.coins;
        });

        let hearts = '';
        for (let i = 0; i < 3; i++) {
            hearts += (i < this.state.lives) ? '❤️' : '🖤';
        }
        ['livesContainer', 'livesContainerMobile'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = hearts;
        });
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
        if (this.state.racingLoopInterval) clearInterval(this.state.racingLoopInterval);

        const racingOverlay = document.getElementById('racingBarrierOverlay');
        if (racingOverlay) racingOverlay.classList.add('d-none');

        const elapsedTime = Math.round((Date.now() - this.state.startTime) / 1000);
        const isPassed = (this.state.score >= this.data.kkm);

        const setElemText = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };
        setElemText('endScoreVal', this.state.score);
        setElemText('endComboVal', `${this.state.maxCombo}x 🔥`);
        setElemText('endCorrectVal', `${this.state.correctCount} / ${this.data.questions.length}`);

        const statusElem = document.getElementById('endStatusVal');
        if (statusElem) {
            statusElem.textContent = isPassed ? 'LULUS 🎉' : 'TIDAK LULUS ❌';
            statusElem.className = isPassed ? 'text-success fw-bold' : 'text-danger fw-bold';
        }

        let stars = '⭐';
        if (this.state.score >= this.data.kkm * 1.2) stars = '⭐⭐⭐';
        else if (isPassed) stars = '⭐⭐';
        setElemText('endGameStars', stars);

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

        // Tampilkan in-game overlay langsung di dalam arena fullscreen!
        const endOverlay = document.getElementById('gameEndOverlay');
        if (endOverlay) {
            endOverlay.classList.remove('d-none');
        } else {
            const modalEl = document.getElementById('modalEndGame');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }
    }
};

window.startGameArena = async function() {
    if (window.requestMobileLandscapeAndFullscreen) {
        await window.requestMobileLandscapeAndFullscreen();
    }
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
        <div class="d-flex justify-content-between align-items-center mb-2 mb-md-3 flex-wrap gap-2" id="topNavGameHeader">
            <a href="<?= BASE_URL ?>index.php?url=game" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 py-md-2 fw-semibold btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Keluar Arena Game
            </a>
            <button type="button" class="btn btn-outline-warning rounded-pill px-3 px-md-4 py-1.5 py-md-2 fw-bold text-dark shadow-sm hover-scale btn-sm" onclick="window.toggleArenaFullscreen()" id="btnFullscreenHeader">
                <i class="bi bi-arrows-fullscreen me-1"></i> <span class="d-none d-sm-inline">Mode </span>Layar Penuh (🚀)
            </button>
        </div>

        <!-- Game Arena Card Container -->
        <div class="card card-custom p-2 p-sm-3 p-md-4 mb-3 shadow-lg border-0 rounded-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%); color: white; min-height: 480px;" id="gameArenaCard">

            <!-- Arena Header Bar -->
            <div class="arena-header-bar d-flex justify-content-between align-items-center mb-3 mb-md-4 flex-wrap gap-2 gap-md-3 pb-2 pb-md-3 border-bottom border-secondary border-opacity-50" id="arenaHeaderBar">
                <div>
                    <?php if (strtolower(trim($_SESSION['user']['role_name'] ?? '')) === 'guru'): ?>
                        <span class="badge bg-warning text-dark px-2.5 py-0.5 rounded-pill small mb-1 d-inline-block fw-bold shadow-sm" style="font-size: 0.72rem;">
                            <i class="bi bi-eye-fill me-1"></i> Mode Pratinjau Guru
                        </span>
                    <?php endif; ?>
                    <h4 class="fw-bold mb-0 text-warning d-flex align-items-center gap-2 arena-title-text fs-5 fs-md-4">
                        <i class="bi bi-controller text-danger"></i> <?= htmlspecialchars($game['judul']) ?>
                        <?php if ($gameType === 'mario_run'): ?>
                            <span class="badge bg-danger text-white rounded-pill px-2 py-0.5" style="font-size: 0.75rem;">🍄 Mario Run</span>
                        <?php elseif ($gameType === 'car_racing'): ?>
                            <span class="badge bg-danger text-white rounded-pill px-2 py-0.5" style="font-size: 0.75rem;">🏎️ Turbo Racing</span>
                        <?php elseif ($gameType === 'spin_wheel'): ?>
                            <span class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size: 0.75rem;">🎡 Spin Wheel</span>
                        <?php elseif ($gameType === 'memory_match'): ?>
                            <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 0.75rem;">🧩 Memory</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5" style="font-size: 0.75rem;">⚡ Speed</span>
                        <?php endif; ?>
                    </h4>
                    <small class="text-white-50 arena-sub-info" style="font-size: 0.78rem;"><?= htmlspecialchars($game['nama_mapel']) ?> | KKM: <strong><?= $game['kkm'] ?> Poin</strong></small>
                </div>

                <!-- HUD Status Badges -->
                <div class="d-flex align-items-center gap-1.5 gap-md-2 flex-wrap">
                    <!-- Nyawa / Lives -->
                    <div class="bg-black bg-opacity-50 px-2.5 px-md-3 py-1.5 py-md-2 rounded-pill d-flex align-items-center gap-1 border border-danger border-opacity-50 shadow-sm">
                        <small class="text-white-50 me-1 d-none d-sm-inline" style="font-size: 0.75rem;">Nyawa:</small>
                        <span id="livesContainer" class="fs-6 fs-md-5">❤️❤️❤️</span>
                    </div>

                    <!-- Combo Streak -->
                    <div class="bg-black bg-opacity-50 px-2.5 px-md-3 py-1.5 py-md-2 rounded-pill d-flex align-items-center gap-1 border border-warning border-opacity-50 shadow-sm">
                        <small class="text-white-50 me-1 d-none d-sm-inline" style="font-size: 0.75rem;">Combo:</small>
                        <span id="comboBadge" class="fw-bold text-warning" style="font-size: 0.8rem;">1x 🔥</span>
                    </div>

                    <!-- Score Badge -->
                    <div class="bg-primary bg-gradient px-3 px-md-4 py-1.5 py-md-2 rounded-pill shadow border border-primary border-opacity-50">
                        <small class="text-white-50 me-1" style="font-size: 0.75rem;">Skor:</small>
                        <span id="currentScore" class="fw-bold text-white fs-6 fs-md-5">0</span>
                    </div>

                    <!-- Quick Fullscreen / Landscape Toggle Button -->
                    <button type="button" class="btn btn-outline-warning rounded-circle p-1.5 d-inline-flex align-items-center justify-content-center shadow-sm hover-scale text-warning" onclick="window.toggleArenaFullscreen()" title="Layar Penuh / Landscape (Fullscreen 🚀)" style="width: 38px; height: 38px; min-width: 38px;">
                        <i class="bi bi-arrows-fullscreen" style="font-size: 0.85rem;"></i>
                    </button>
                </div>
            </div>

            <!-- Start Screen Overlay Container (Initial State) -->
            <div id="startScreenOverlay" class="text-center py-2 py-md-4 px-2">
                <div class="mb-2 mb-md-3">
                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold small shadow">
                        <i class="bi bi-controller me-1"></i> ARENA KUIS SIAP DIMULAI
                    </span>
                </div>
                <h2 class="fw-bold text-white mb-1.5 display-6 fs-3 fs-md-2"><?= htmlspecialchars($game['judul']) ?></h2>
                <p class="text-white-50 max-w-xl mx-auto mb-3 fs-6" style="font-size: 0.85rem !important;">
                    Mata Pelajaran: <strong><?= htmlspecialchars($game['nama_mapel']) ?></strong> | Target KKM: <strong><?= $game['kkm'] ?> Poin</strong>
                </p>

                <!-- Game Rules Info Box (Responsive 3 Columns di Mobile & Desktop) -->
                <div class="row g-2 g-md-3 justify-content-center max-w-2xl mx-auto mb-3 mb-md-4 text-start">
                    <div class="col-4">
                        <div class="p-2 p-md-3 bg-white bg-opacity-10 rounded-3 rounded-md-4 border border-white border-opacity-10 text-center h-100">
                            <?php if ($gameType === 'mario_run'): ?>
                                <div class="fs-4 fs-md-3 mb-1">🍄 🏃</div>
                                <small class="text-white-50 d-block" style="font-size: 0.68rem;">Lari Gerbang</small>
                                <span class="fw-bold text-warning d-block" style="font-size: 0.75rem;">Sesuai Timer</span>
                            <?php elseif ($gameType === 'car_racing'): ?>
                                <div class="fs-4 fs-md-3 mb-1">🏎️ 💥</div>
                                <small class="text-white-50 d-block" style="font-size: 0.68rem;">Endless Runner</small>
                                <span class="fw-bold text-danger d-block" style="font-size: 0.75rem;">Balapan Mobil</span>
                            <?php elseif ($gameType === 'spin_wheel'): ?>
                                <div class="fs-4 fs-md-3 mb-1">🎡 🌟</div>
                                <small class="text-white-50 d-block" style="font-size: 0.68rem;">Roda</small>
                                <span class="fw-bold text-warning d-block" style="font-size: 0.75rem;">Spin Wheel</span>
                            <?php elseif ($gameType === 'memory_match'): ?>
                                <div class="fs-4 fs-md-3 mb-1">🧩 🎴</div>
                                <small class="text-white-50 d-block" style="font-size: 0.68rem;">Kartu</small>
                                <span class="fw-bold text-warning d-block" style="font-size: 0.75rem;">Memory Match</span>
                            <?php else: ?>
                                <div class="fs-4 fs-md-3 mb-1">⚡ ⏱️</div>
                                <small class="text-white-50 d-block" style="font-size: 0.68rem;">Kecepatan</small>
                                <span class="fw-bold text-warning d-block" style="font-size: 0.75rem;">Speed Battle</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 p-md-3 bg-white bg-opacity-10 rounded-3 rounded-md-4 border border-white border-opacity-10 text-center h-100">
                            <div class="fs-4 fs-md-3 mb-1"><?= ($gameType === 'car_racing') ? '🛑 100m' : '⏱️ ' . $game['durasi_per_soal'] . 's' ?></div>
                            <small class="text-white-50 d-block" style="font-size: 0.68rem;"><?= ($gameType === 'car_racing') ? 'Gerbang Soal' : 'Timer' ?></small>
                            <span class="fw-bold text-warning d-block" style="font-size: 0.75rem;"><?= ($gameType === 'car_racing') ? 'Tiap 100 Meter' : $game['durasi_per_soal'] . ' Detik' ?></span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 p-md-3 bg-white bg-opacity-10 rounded-3 rounded-md-4 border border-white border-opacity-10 text-center h-100">
                            <div class="fs-4 fs-md-3 mb-1"><?= ($gameType === 'car_racing') ? '⚡ +10' : '🔥 5x' ?></div>
                            <small class="text-white-50 d-block" style="font-size: 0.68rem;"><?= ($gameType === 'car_racing') ? 'Benar = Boost' : 'Bonus' ?></small>
                            <span class="fw-bold text-info d-block" style="font-size: 0.75rem;"><?= ($gameType === 'car_racing') ? '2s Speed Boost' : 'Combo Multi' ?></span>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-warning btn-lg rounded-pill px-4 px-md-5 py-2.5 py-md-3 fw-bold shadow-lg text-dark fs-5 fs-md-4 hover-scale" id="btnStartGame" onclick="window.startGameArena()">
                    <i class="bi bi-play-circle-fill me-2 fs-4"></i> MULAI PERMAINAN (FULLSCREEN 🚀)
                </button>
            </div>

            <!-- 🍄 MODE 1: ENHANCED SUPER MARIO RETRO PLATFORM RUNNER STAGE -->
            <div id="marioStageContainer" class="d-none text-center py-1">
                <!-- 📱 Mobile Compact Arcade Status Bar 1 (Hanya Tampil di Mobile: Tinggi Hanya ~30px) -->
                <div class="d-flex d-md-none justify-content-between align-items-center mb-1.5 px-2 py-1 rounded-pill bg-black bg-opacity-40 border border-white border-opacity-10 shadow-sm">
                    <a href="<?= BASE_URL ?>index.php?url=game" class="btn btn-outline-light btn-sm rounded-pill px-2 py-0 border-0 text-white-50 d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; height: 24px;">
                        <i class="bi bi-arrow-left"></i> Keluar
                    </a>
                    <div class="d-flex align-items-center gap-1.5">
                        <span id="livesContainerMobile" class="fs-6" style="line-height: 1;">❤️❤️❤️</span>
                        <span class="badge bg-primary px-2 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;">Skor: <span id="currentScoreMobile">0</span></span>
                        <span id="comboBadgeMobile" class="badge bg-dark text-warning border border-warning px-1.5 py-0.5 rounded-pill" style="font-size: 0.68rem;">1x 🔥</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-warning rounded-circle p-0 text-warning d-flex align-items-center justify-content-center" onclick="window.toggleArenaFullscreen()" title="Fullscreen" style="width: 26px; height: 26px;">
                        <i class="bi bi-arrows-fullscreen" style="font-size: 0.72rem;"></i>
                    </button>
                </div>

                <!-- 📱 Mobile Compact Sub-Bar: Timer Soal, Jarak & Koin (Tinggi Hanya ~24px) -->
                <div class="d-flex d-md-none justify-content-between align-items-center gap-2 mb-1.5 px-1">
                    <div class="d-flex align-items-center gap-1 flex-grow-1" style="min-width: 105px;">
                        <span class="text-warning fw-bold" style="font-size: 0.72rem;" title="Timer Menuju Gerbang Checkpoint"><i class="bi bi-stopwatch-fill"></i></span>
                        <div class="progress rounded-pill bg-dark border border-warning flex-grow-1 shadow-sm" style="height: 13px;">
                            <div id="marioStaminaBarMobile" class="progress-bar bg-warning text-dark fw-bold progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%; font-size: 0.65rem; line-height: 13px;">--s</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge bg-danger bg-opacity-80 text-white rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.7rem;">
                            🚩 <span id="marioDistValMobile">0</span>/<span id="marioTargetValMobile">120</span>m
                        </span>
                        <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.7rem;">
                            🪙 <span id="marioCoinValMobile">0</span>
                        </span>
                    </div>
                </div>

                <!-- 💻 Desktop Mario Top HUD Bar (Tampil di Layar Desktop) -->
                <div class="d-none d-md-flex row align-items-center g-2 mb-3 px-2">
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-bold text-warning text-nowrap"><i class="bi bi-stopwatch-fill"></i> Timer Menuju Gerbang:</span>
                            <div class="progress rounded-pill bg-dark border border-warning flex-grow-1 shadow-sm" style="height: 22px;">
                                <div id="marioStaminaBar" class="progress-bar bg-warning text-dark fw-bold progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%; font-size:0.82rem;">
                                    --s
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 text-center">
                        <span class="badge bg-danger bg-opacity-75 text-white rounded-pill px-3 py-2 fw-bold small shadow-sm border border-danger border-opacity-50">
                            🚩 Jarak: <span id="marioDistVal">0</span>m / <span id="marioTargetVal">120</span>m
                        </span>
                    </div>
                    <div class="col-6 col-md-2 text-center">
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold small shadow-sm">
                            🪙 <span id="marioCoinVal">0</span> Coins
                        </span>
                    </div>
                    <div class="col-12 col-md-3 text-end d-flex gap-2 justify-content-end">
                        <button type="button" id="btnMarioJumpAction" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark fs-6 shadow hover-scale w-100 w-md-auto" onclick="window.GameEngine.marioJump()">
                            🦘 LOMPAT (SPASI)
                        </button>
                    </div>
                </div>

                <!-- Mario Retro Game Canvas Screen -->
                <div class="position-relative overflow-hidden rounded-4 border border-warning border-opacity-50 shadow-2xl mx-auto" style="max-width: 960px;">
                    <canvas id="marioCanvas" width="800" height="320" class="w-100 h-auto rounded-4 d-block" style="background:#3b82f6; cursor: pointer;"></canvas>
                    
                    <!-- Floating Mobile Jump Button Overlay -->
                    <div class="position-absolute bottom-0 end-0 p-2 p-sm-3 d-md-none" style="z-index: 25;">
                        <button type="button" class="btn btn-warning rounded-circle shadow-2xl d-flex align-items-center justify-content-center" style="width: 58px; height: 58px; font-size: 1.5rem; background: rgba(245, 158, 11, 0.9); backdrop-filter: blur(4px); border: 2px solid #ffffff;" onclick="window.GameEngine.marioJump()" title="Lompat">
                            🦘
                        </button>
                    </div>
                </div>

                <!-- Instructional Badges & Pro-Tips -->
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-1.5 gap-md-3 mt-2 text-white-50" style="font-size: 0.74rem;">
                    <span class="badge bg-dark bg-opacity-60 border border-secondary px-2.5 py-1.5 rounded-pill">
                        🎮 <strong>Kontrol:</strong> Ketuk Layar / Spasi = Lompat (Double Jump! 🦘)
                    </span>
                    <span class="badge bg-dark bg-opacity-60 border border-secondary px-2.5 py-1.5 rounded-pill d-none d-sm-inline-block">
                        🍄 <strong>Injak Goomba:</strong> +50 Poin & Combo!
                    </span>
                    <span class="badge bg-dark bg-opacity-60 border border-secondary px-2.5 py-1.5 rounded-pill d-none d-sm-inline-block">
                        ❓ <strong>Balok '?':</strong> Koin & Star Power!
                    </span>
                </div>
            </div>

            <!-- 🏎️ MODE 5: ENDLESS TURBO CAR RACING RUNNER STAGE -->
            <div id="racingStageContainer" class="d-none text-center py-1">
                <!-- 📱 Mobile Compact Arcade Status Bar (Tinggi Hanya ~30px) -->
                <div class="d-flex d-md-none justify-content-between align-items-center mb-1.5 px-2 py-1 rounded-pill bg-black bg-opacity-40 border border-white border-opacity-10 shadow-sm">
                    <a href="<?= BASE_URL ?>index.php?url=game" class="btn btn-outline-light btn-sm rounded-pill px-2 py-0 border-0 text-white-50 d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; height: 24px;">
                        <i class="bi bi-arrow-left"></i> Keluar
                    </a>
                    <div class="d-flex align-items-center gap-1.5">
                        <span id="racingSpeedBadgeMobile" class="badge bg-danger px-2 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;">⚡ <span id="racingSpeedValMobile">70</span> KM/H</span>
                        <span class="badge bg-primary px-2 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;">Skor: <span id="currentScoreRacingMobile">0</span></span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-warning rounded-circle p-0 text-warning d-flex align-items-center justify-content-center" onclick="window.toggleArenaFullscreen()" title="Fullscreen" style="width: 26px; height: 26px;">
                        <i class="bi bi-arrows-fullscreen" style="font-size: 0.72rem;"></i>
                    </button>
                </div>

                <!-- 📱 Mobile Compact Sub-Bar: Timer Menuju Gerbang, Status Nitro & Koin -->
                <div class="d-flex d-md-none justify-content-between align-items-center gap-2 mb-1.5 px-1">
                    <div class="d-flex align-items-center gap-1 flex-grow-1" style="min-width: 110px;">
                        <span class="text-danger fw-bold" style="font-size: 0.72rem;" title="Timer Menuju Gerbang Checkpoint"><i class="bi bi-stopwatch-fill"></i></span>
                        <div class="progress rounded-pill bg-dark border border-danger flex-grow-1 shadow-sm" style="height: 13px;">
                            <div id="racingGateProgressBarMobile" class="progress-bar bg-danger text-white fw-bold progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%; font-size: 0.65rem; line-height: 13px;">--s</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge bg-dark text-warning border border-warning rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.7rem;" id="racingBoostBadgeMobile">
                            🚀 READY
                        </span>
                        <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.7rem;">
                            🪙 <span id="racingCoinValMobile">0</span>
                        </span>
                    </div>
                </div>

                <!-- 💻 Desktop Racing Top HUD Bar -->
                <div class="d-none d-md-flex row align-items-center g-2 mb-3 px-2">
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-bold text-danger text-nowrap"><i class="bi bi-stopwatch-fill"></i> Timer Menuju Gerbang:</span>
                            <div class="progress rounded-pill bg-dark border border-danger flex-grow-1 shadow-sm" style="height: 22px;">
                                <div id="racingGateProgressBar" class="progress-bar bg-danger text-white fw-bold progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%; font-size: 0.82rem;">
                                    --s
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 text-center">
                        <span class="badge bg-dark text-danger border border-danger rounded-pill px-3 py-2 fw-bold small shadow-sm">
                            ⚡ Kecepatan: <span id="racingSpeedVal">70</span> KM/H <span id="racingBoostIndicator" class="text-warning ms-1 d-none">🔥 NITRO!</span>
                        </span>
                    </div>
                    <div class="col-6 col-md-2 text-center">
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold small shadow-sm">
                            🪙 <span id="racingCoinVal">0</span> Koin
                        </span>
                    </div>
                    <div class="col-12 col-md-3 text-end d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-outline-light rounded-pill px-3 py-1.5 fw-bold small" onclick="window.GameEngine.racingSteer(-1)">
                            ◀ KIRI (A)
                        </button>
                        <button type="button" class="btn btn-outline-light rounded-pill px-3 py-1.5 fw-bold small" onclick="window.GameEngine.racingSteer(1)">
                            KANAN (D) ▶
                        </button>
                    </div>
                </div>

                <!-- 🏎️ Racing Retro/Futuristic Highway Canvas Screen -->
                <div class="position-relative overflow-hidden rounded-4 border border-danger border-opacity-50 shadow-2xl mx-auto" style="max-width: 960px;">
                    <canvas id="racingCanvas" width="800" height="360" class="w-100 h-auto rounded-4 d-block" style="background:#090d16; cursor: pointer;"></canvas>

                    <!-- Floating Mobile Steering Buttons Overlay -->
                    <div class="position-absolute bottom-0 start-0 p-2 p-sm-3 d-md-none" style="z-index: 25;">
                        <button type="button" class="btn btn-danger rounded-circle shadow-2xl d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; font-size: 1.3rem; background: rgba(220, 38, 38, 0.85); backdrop-filter: blur(4px); border: 2px solid #ffffff;" onclick="window.GameEngine.racingSteer(-1)" title="Belok Kiri">
                            ◀
                        </button>
                    </div>
                    <div class="position-absolute bottom-0 end-0 p-2 p-sm-3 d-md-none" style="z-index: 25;">
                        <button type="button" class="btn btn-danger rounded-circle shadow-2xl d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; font-size: 1.3rem; background: rgba(220, 38, 38, 0.85); backdrop-filter: blur(4px); border: 2px solid #ffffff;" onclick="window.GameEngine.racingSteer(1)" title="Belok Kanan">
                            ▶
                        </button>
                    </div>

                    <!-- 🛑 IN-GAME BARRIER QUESTION OVERLAY (Muncul di layar saat mobil mencapai gerbang penghalang) -->
                    <div id="racingBarrierOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none d-flex flex-column justify-content-center align-items-center p-2 p-md-4" style="background: rgba(10, 15, 29, 0.94); backdrop-filter: blur(8px); z-index: 50; overflow-y: auto;">
                        <div class="w-100 max-w-2xl bg-black bg-opacity-70 border border-danger border-2 rounded-4 p-3 p-md-4 shadow-2xl text-center position-relative my-auto">
                            <!-- Gate Alert Header -->
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-secondary border-opacity-50">
                                <span class="badge bg-danger text-white rounded-pill px-3 py-1 fw-bold fs-6 shadow-sm" id="racingBarrierHeader">
                                    🛑 GERBANG PENGHALANG BALAPAN #1
                                </span>
                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fw-bold small" id="racingBarrierReward">
                                    🎁 BENAR: LEDAKAN + SPEED BOOST 2 DETIK (+10 POIN)
                                </span>
                            </div>

                            <!-- Crash Notice (Ditampilkan jika salah menjawab) -->
                            <div id="racingCrashNotice" class="alert alert-danger border-2 border-danger py-2 px-3 rounded-3 mb-2 d-none text-start animate__animated animate__shakeX">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-4">💥</span>
                                    <div>
                                        <strong class="d-block text-danger">MENABRAK GERBANG! Mobil terhenti (PAUSED)!</strong>
                                        <small class="text-white-50">Opsi jawaban telah diacak ulang. Pilih opsi yang benar untuk menghancurkan gerbang & melaju kencang!</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Question Text -->
                            <div class="bg-dark bg-opacity-80 p-3 rounded-3 mb-3 border border-secondary border-opacity-30 text-start">
                                <small class="text-warning fw-bold d-block mb-1">SOAL PERTANYAAN:</small>
                                <h5 class="fw-bold text-white mb-0" id="racingQuestionText" style="line-height: 1.4;">Pertanyaan...</h5>
                            </div>

                            <!-- Shuffled Options Container -->
                            <div class="row g-2 text-start" id="racingOptionsContainer">
                                <!-- Dynamic Options Buttons -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pro-Tips Bar -->
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-1.5 gap-md-3 mt-2 text-white-50" style="font-size: 0.74rem;">
                    <span class="badge bg-dark bg-opacity-60 border border-secondary px-2.5 py-1.5 rounded-pill">
                        🎮 <strong>Kontrol Mobil:</strong> Tombol Panah Kiri/Kanan / A & D = Steer / Pindah Jalur
                    </span>
                    <span class="badge bg-dark bg-opacity-60 border border-secondary px-2.5 py-1.5 rounded-pill">
                        🛑 <strong>Gerbang Soal:</strong> Hadang tiap 100m. Benar = Gerbang Meledak & Speed Boost 2s (+10 Poin)!
                    </span>
                    <span class="badge bg-dark bg-opacity-60 border border-secondary px-2.5 py-1.5 rounded-pill d-none d-sm-inline-block">
                        💥 <strong>Salah Jawab:</strong> Animasi menabrak, mobil berhenti & opsi diacak ulang sampai benar!
                    </span>
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

            <!-- 🍄 IN-GAME MARIO QUIZ CHECKPOINT OVERLAY (Tampil Langsung di Fullscreen Tanpa Perlu ESC!) -->
            <div id="marioCheckpointOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none d-flex flex-column justify-content-center align-items-center p-2 p-md-4" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(12px); z-index: 1060; overflow-y: auto;">
                <div class="card border-0 rounded-4 shadow-2xl text-white w-100 my-auto" style="max-width: 840px; background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); border: 2px solid rgba(245, 158, 11, 0.5) !important;">
                    <div class="card-header border-0 bg-warning text-dark p-3 rounded-top-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-2">🍄</span>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark" id="marioModalMainTitle">TANTANGAN KUIS CHECKPOINT MARIO</h5>
                                <small class="fw-bold text-dark text-opacity-75 d-block" id="marioModalCounter">Jawab Pertanyaan Untuk Isi Ulang Stamina!</small>
                            </div>
                        </div>
                        <span class="badge bg-dark text-warning px-3 py-1.5 rounded-pill fw-bold small">
                            ⭐ Checkpoint Arena
                        </span>
                    </div>
                    <div class="card-body p-3 p-md-4 text-center">
                        <h4 class="fw-bold text-white mb-3 mb-md-4 px-md-2" id="marioModalQuestion" style="line-height: 1.4; font-size: clamp(1.05rem, 2.2vw, 1.35rem);">
                            Loading Pertanyaan Checkpoint...
                        </h4>

                        <div class="row g-2 g-md-3 text-start" id="marioModalOptions">
                            <!-- Options dipasang dinamis oleh GameEngine -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🏆 IN-GAME END GAME OVERLAY (Tampil Langsung di Fullscreen Tanpa ESC!) -->
            <div id="gameEndOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none d-flex flex-column justify-content-center align-items-center p-3" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(14px); z-index: 1070; overflow-y: auto;">
                <div class="card border-0 rounded-4 shadow-2xl text-center p-3 p-md-4 w-100 my-auto" style="max-width: 480px; background: #ffffff; color: #1e293b;">
                    <div class="card-body p-2 p-md-3">
                        <div id="endGameIcon" class="display-3 mb-1">🏆</div>
                        <h3 id="endGameTitle" class="fw-bold text-dark mb-1">Permainan Selesai!</h3>
                        <div id="endGameStars" class="fs-2 text-warning mb-3">⭐⭐⭐</div>

                        <div class="p-3 bg-light rounded-4 mb-3 border text-center">
                            <div class="row g-2">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block fw-semibold">Skor Akhir</small>
                                    <span class="fw-bold fs-2 text-primary" id="endScoreVal">0</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block fw-semibold">Max Combo</small>
                                    <span class="fw-bold fs-2 text-warning" id="endComboVal">0x 🔥</span>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between text-muted small fw-medium">
                                <span>Total Benar: <strong class="text-dark" id="endCorrectVal">0</strong></span>
                                <span>Status: <strong id="endStatusVal">LULUS</strong></span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="<?= BASE_URL ?>index.php?url=game/play&id=<?= $game['id'] ?>" class="btn btn-outline-danger rounded-pill w-100 py-2.5 fw-bold">
                                <i class="bi bi-arrow-repeat me-1"></i> Main Lagi
                            </a>
                            <a href="<?= BASE_URL ?>index.php?url=game/leaderboard&id=<?= $game['id'] ?>" class="btn btn-warning rounded-pill w-100 py-2.5 fw-bold shadow">
                                <i class="bi bi-trophy-fill me-1"></i> Peringkat
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🔄 MOBILE ROTATE DEVICE PROMPT (Petunjuk Otomatis Jika Ponsel Dalam Posisi Portrait) -->
            <div id="rotateDevicePrompt" class="position-absolute top-0 start-0 w-100 h-100 d-none d-flex flex-column justify-content-center align-items-center p-4 text-center" style="background: rgba(15, 23, 42, 0.97); backdrop-filter: blur(12px); z-index: 1090;">
                <div class="p-4 rounded-4 border border-warning border-opacity-50 shadow-2xl" style="max-width: 380px; background: rgba(30, 41, 59, 0.95);">
                    <div class="display-1 mb-3 text-warning rotate-phone-animation">📱</div>
                    <h4 class="fw-bold text-white mb-2">Putar Layar ke Landscape</h4>
                    <p class="text-white-50 small mb-4">
                        Game Super Mario Runner dirancang untuk pengalaman bermain terbaik dalam mode <strong>Landscape (Mendatar)</strong> dan Layar Penuh.
                    </p>
                    <button type="button" class="btn btn-warning rounded-pill px-4 py-2.5 fw-bold text-dark w-100 shadow hover-scale" onclick="window.requestMobileLandscapeAndFullscreen()">
                        <i class="bi bi-arrows-fullscreen me-1"></i> Kunci Landscape & Fullscreen
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
