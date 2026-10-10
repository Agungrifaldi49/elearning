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
        this.updateStaminaHUD(this.state.stamina || 100);

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

        // Running energy consumption
        this.state.stamina -= (isStarActive ? 0.08 : 0.20);
        if (this.state.stamina <= 0) {
            this.state.stamina = 0;
            this.updateStaminaHUD(0);
            this.triggerMarioQuestionCheckpoint('stamina_empty');
            return;
        }
        this.updateStaminaHUD(this.state.stamina);

        // Spawn flagpole when reaching distance checkpoint
        if (this.state.marioDistance >= this.state.marioNextCheckpoint && !ents.flagpole) {
            ents.flagpole = { x: 840, reached: false };
        }

        // Flagpole checkpoint interaction
        if (ents.flagpole) {
            ents.flagpole.x -= currentSpeed;
            if (!ents.flagpole.reached && ents.flagpole.x <= this.state.marioX + 24) {
                ents.flagpole.reached = true;
                this.playSound('powerup');
                this.addPopup('🏁 CHECKPOINT TERCAPAI!', this.state.marioX, this.state.marioY - 25, '#ffd166');
                setTimeout(() => {
                    ents.flagpole = null;
                    this.state.marioNextCheckpoint += 120;
                    this.triggerMarioQuestionCheckpoint('checkpoint_reached');
                }, 400);
                return;
            }
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
                        // Player takes damage!
                        this.state.screenShake = 16;
                        this.playSound('wrong');
                        this.state.lives--;
                        this.updateHUD();
                        g.isBlasted = true;
                        g.blastedVy = -10;
                        if (this.state.lives <= 0) {
                            this.endGame();
                            return;
                        } else {
                            this.triggerMarioQuestionCheckpoint('obstacle_hit');
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
        // Top-left Distance & Stage Pill
        ctx.fillStyle = 'rgba(15, 23, 42, 0.75)';
        ctx.beginPath();
        ctx.roundRect(16, 14, 175, 28, [14]);
        ctx.fill();
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
        ctx.lineWidth = 1;
        ctx.stroke();
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 12px sans-serif';
        ctx.fillText(`🚩 Jarak: ${Math.round(this.state.marioDistance)}m`, 28, 32);

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

        const quizModalEl = document.getElementById('modalMarioQuiz');
        if (quizModalEl) {
            const modal = new bootstrap.Modal(quizModalEl);
            this.renderMarioModalQuestion(reason);
            modal.show();
        } else {
            const quizBox = document.getElementById('quizBoxContainer');
            if (quizBox) quizBox.classList.remove('d-none');
            this.renderQuestion();
        }
    },

    renderMarioModalQuestion: function(reason) {
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

        let checkpointTitle = `Tantangan Checkpoint #${this.state.currentIdx + 1} dari ${this.data.questions.length}`;
        if (reason === 'obstacle_hit') {
            checkpointTitle = `⚠️ RESCUE DARURAT! Jawab Benar Untuk Pulihkan Mario!`;
        } else if (reason === 'stamina_empty') {
            checkpointTitle = `⚡ STAMINA HABIS! Jawab Benar Untuk Isi Ulang 100%!`;
        } else if (reason === 'checkpoint_reached') {
            checkpointTitle = `🏁 CHECKPOINT GERBANG BINTANG #${this.state.currentIdx + 1}!`;
        }

        if (counterEl) counterEl.textContent = checkpointTitle;
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
            this.playSound('powerup');
            this.state.combo++;
            if (this.state.combo > this.state.maxCombo) this.state.maxCombo = this.state.combo;
            this.state.correctCount++;

            // Refill stamina 100% full
            this.state.stamina = 100;
            this.updateStaminaHUD(100);

            // Grant 7 seconds of invincible Star Power!
            this.state.marioInvincibleTimer = 60 * 7;

            const pointsGained = (parseInt(q.poin) || 10) + 25;
            this.state.score += pointsGained;
            this.updateHUD();

            this.showFeedback(true, '🎉 JAWABAN BENAR!', '🌟 SUPER STAR POWER AKTIF! MARIO KEBAL & STAMINA 100% PENUH! 🚀');
        } else {
            this.playSound('wrong');
            this.state.combo = 0;
            this.state.lives--;
            this.state.stamina = 35;
            this.updateStaminaHUD(35);
            this.updateHUD();

            this.showFeedback(false, '❌ JAWABAN KURANG TEPAT', `Kunci Jawaban: Opsi ${(q.kunci_jawaban || 'A').toUpperCase()}`);
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
        }, 1400);
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
            bar.textContent = `${pct}%`;
            if (pct < 30) {
                bar.className = 'progress-bar bg-danger text-white fw-bold progress-bar-striped progress-bar-animated';
            } else {
                bar.className = 'progress-bar bg-warning text-dark fw-bold progress-bar-striped progress-bar-animated';
            }
        }

        const distEl = document.getElementById('marioDistVal');
        const targetEl = document.getElementById('marioTargetVal');
        if (distEl) distEl.textContent = Math.round(this.state.marioDistance);
        if (targetEl) targetEl.textContent = Math.round(this.state.marioNextCheckpoint);
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

            <!-- 🍄 MODE 1: ENHANCED SUPER MARIO RETRO PLATFORM RUNNER STAGE -->
            <div id="marioStageContainer" class="d-none text-center py-2">
                <!-- Mario Top HUD Bar -->
                <div class="row align-items-center g-2 mb-3 px-2">
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-bold text-warning text-nowrap"><i class="bi bi-lightning-charge-fill"></i> Stamina:</span>
                            <div class="progress rounded-pill bg-dark border border-warning flex-grow-1 shadow-sm" style="height: 22px;">
                                <div id="marioStaminaBar" class="progress-bar bg-warning text-dark fw-bold progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%; font-size:0.82rem;">
                                    100%
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
                    <canvas id="marioCanvas" width="800" height="320" class="w-100 h-auto rounded-4 d-block" style="background:#3b82f6; max-height:380px; cursor: pointer;"></canvas>
                    
                    <!-- Floating Mobile Jump Button Overlay -->
                    <div class="position-absolute bottom-0 end-0 p-3 d-md-none" style="z-index: 10;">
                        <button type="button" class="btn btn-warning rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 62px; height: 62px; font-size: 1.5rem;" onclick="window.GameEngine.marioJump()">
                            🦘
                        </button>
                    </div>
                </div>

                <!-- Instructional Badges & Pro-Tips -->
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 gap-md-3 mt-3 text-white-50 small">
                    <span class="badge bg-dark bg-opacity-60 border border-secondary px-3 py-2 rounded-pill">
                        🎮 <strong>Kontrol:</strong> Spasi / Panah Atas / Klik Layar = Lompat (Bisa Double Jump! 🦘)
                    </span>
                    <span class="badge bg-dark bg-opacity-60 border border-secondary px-3 py-2 rounded-pill">
                        🍄 <strong>Injak Jamur Goomba:</strong> Lompat ke atas musuh untuk Stomp +50 Poin & Combo!
                    </span>
                    <span class="badge bg-dark bg-opacity-60 border border-secondary px-3 py-2 rounded-pill">
                        ❓ <strong>Balok Misteri:</strong> Sundul balok <strong>'?'</strong> dari bawah untuk Koin & Star Power!
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
