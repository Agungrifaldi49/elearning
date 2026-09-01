<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>

<style>
/* Modern Login Page Custom Styling */
.login-wrapper {
    min-height: 100vh;
    background: #090d16;
    background-image: 
        radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.25) 0px, transparent 50%),
        radial-gradient(at 100% 100%, rgba(14, 165, 233, 0.2) 0px, transparent 50%),
        radial-gradient(at 50% 50%, rgba(99, 102, 241, 0.15) 0px, transparent 50%);
    position: relative;
    overflow: hidden;
}

/* Floating background glow orbs */
.login-glow-1 {
    position: absolute;
    width: 450px;
    height: 450px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.35) 0%, rgba(79, 70, 229, 0) 70%);
    top: -100px;
    left: -100px;
    border-radius: 50%;
    filter: blur(50px);
    animation: floatGlow 10s ease-in-out infinite alternate;
}

.login-glow-2 {
    position: absolute;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(14, 165, 233, 0.3) 0%, rgba(6, 182, 212, 0) 70%);
    bottom: -100px;
    right: -100px;
    border-radius: 50%;
    filter: blur(50px);
    animation: floatGlow 12s ease-in-out infinite alternate-reverse;
}

@keyframes floatGlow {
    0% { transform: translate(0, 0) scale(1); }
    100% { transform: translate(40px, 30px) scale(1.1); }
}

.login-card-container {
    max-width: 1080px;
    width: 100%;
    z-index: 10;
}

.login-hero-card {
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 28px;
    color: #f8fafc;
    position: relative;
    overflow: hidden;
}

.login-form-card {
    background: #ffffff;
    border-radius: 28px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    position: relative;
    z-index: 2;
}

.brand-badge {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    padding: 10px 18px;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #e2e8f0;
    font-size: 0.875rem;
    font-weight: 500;
}

.hero-feature-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 18px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    transition: all 0.3s ease;
}

.hero-feature-item:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(6px);
}

.feature-icon-box {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #ffffff;
    flex-shrink: 0;
    box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
}

/* Custom Form Input Controls */
.custom-input-group {
    position: relative;
}

.custom-input-group .form-control {
    height: 52px;
    border-radius: 14px;
    border: 1.5px solid #e2e8f0;
    padding-left: 48px;
    font-size: 0.95rem;
    color: #1e293b;
    background-color: #f8fafc;
    transition: all 0.25s ease;
}

.custom-input-group .form-control:focus {
    background-color: #ffffff;
    border-color: #4f46e5;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
}

.custom-input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 1.15rem;
    z-index: 5;
    transition: color 0.25s ease;
}

.custom-input-group .form-control:focus + .custom-input-icon,
.custom-input-group:focus-within .custom-input-icon {
    color: #4f46e5;
}

.password-toggle-btn {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 6px;
    border-radius: 8px;
    z-index: 5;
    transition: color 0.2s ease, background-color 0.2s ease;
}

.password-toggle-btn:hover {
    color: #4f46e5;
    background-color: #f1f5f9;
}

.captcha-card {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px;
}

.captcha-badge {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #ffffff;
    font-weight: 700;
    padding: 8px 16px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    letter-spacing: 1px;
}

.btn-login-submit {
    height: 54px;
    border-radius: 14px;
    background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
    border: none;
    color: #ffffff;
    font-weight: 700;
    font-size: 1rem;
    letter-spacing: 0.3px;
    box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-login-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px -5px rgba(79, 70, 229, 0.5);
    background: linear-gradient(135deg, #4338ca 0%, #312e81 100%);
    color: #ffffff;
}

.btn-login-submit:active {
    transform: translateY(0);
}

.role-pill {
    font-size: 0.75rem;
    padding: 4px 10px;
    border-radius: 20px;
    background: #f1f5f9;
    color: #64748b;
    font-weight: 600;
}
</style>

<div class="login-wrapper d-flex align-items-center justify-content-center py-4 py-lg-5 px-3">
    <!-- Background Glows -->
    <div class="login-glow-1"></div>
    <div class="login-glow-2"></div>

    <div class="container login-card-container">
        <div class="row g-0 align-items-stretch shadow-2xl rounded-5 overflow-hidden">
            
            <!-- Left Side: Hero / Brand Showcase -->
            <div class="col-lg-6 login-hero-card d-none d-lg-flex flex-column justify-content-between p-5">
                <div>
                    <!-- Brand Badge -->
                    <div class="brand-badge mb-4">
                        <?php if (!empty($schoolLogo)): ?>
                            <img src="<?= htmlspecialchars($schoolLogo) ?>" alt="Logo" height="26" class="rounded-circle">
                        <?php else: ?>
                            <i class="bi bi-mortarboard-fill text-warning fs-5"></i>
                        <?php endif; ?>
                        <span><?= htmlspecialchars($schoolName) ?></span>
                    </div>

                    <h2 class="display-6 fw-bold text-white mb-3 lh-sm">
                        Portal E-Learning <br>
                        <span class="text-transparent bg-clip-text" style="background: linear-gradient(135deg, #818cf8 0%, #38bdf8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Modern & Terintegrasi</span>
                    </h2>
                    <p class="text-slate-300 fs-6 mb-5" style="max-width: 420px; color: #94a3b8; line-height: 1.6;">
                        Platform pembelajaran digital serba bisa untuk seluruh civitas akademika SMK Muthia Harapan Cicalengka.
                    </p>

                    <!-- Feature List -->
                    <div class="d-flex flex-column gap-3 mb-4" style="max-width: 440px;">
                        <div class="hero-feature-item">
                            <div class="feature-icon-box">
                                <i class="bi bi-journal-bookmark-fill"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-white fw-semibold fs-6">Materi & Tugas Interaktif</h6>
                                <small style="color: #94a3b8;">Akses modul digital & kumpulkan tugas online</small>
                            </div>
                        </div>

                        <div class="hero-feature-item">
                            <div class="feature-icon-box" style="background: linear-gradient(135deg, #0ea5e9 0%, #10b981 100%); box-shadow: 0 8px 16px rgba(14, 165, 233, 0.3);">
                                <i class="bi bi-laptop-fill"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-white fw-semibold fs-6">Ujian CBT & Quiz Online</h6>
                                <small style="color: #94a3b8;">Pelaksanaan ujian real-time dengan token aman</small>
                            </div>
                        </div>

                        <div class="hero-feature-item">
                            <div class="feature-icon-box" style="background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%); box-shadow: 0 8px 16px rgba(139, 92, 246, 0.3);">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-white fw-semibold fs-6">Monitoring & Rapor Akademik</h6>
                                <small style="color: #94a3b8;">Pantau perkembangan nilai dan absensi secara akurat</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Note inside Hero -->
                <div class="pt-4 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                    <small style="color: #64748b;" class="fw-medium">© <?= date('Y') ?> E-Learning SMKMH</small>
                    <span class="badge bg-indigo-900 text-indigo-200 border border-indigo-700 px-3 py-2 rounded-pill small" style="background: rgba(99, 102, 241, 0.15); color: #a5b4fc; border: 1px solid rgba(165, 180, 252, 0.2);">
                        <i class="bi bi-shield-lock me-1"></i> SSL Encrypted & Fast Database
                    </span>
                </div>
            </div>

            <!-- Right Side: Login Form Card -->
            <div class="col-12 col-lg-6 login-form-card p-4 p-sm-5 d-flex flex-column justify-content-center">
                
                <!-- Mobile Brand Header (Visible only on mobile/tablet) -->
                <div class="text-center mb-4 d-lg-none">
                    <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-4 mb-2" style="background: linear-gradient(135deg, #4f46e5 0%, #312e81 100%); color: white; box-shadow: 0 10px 20px rgba(79,70,229,0.3);">
                        <?php if (!empty($schoolLogo)): ?>
                            <img src="<?= htmlspecialchars($schoolLogo) ?>" alt="Logo" height="36" class="rounded-circle">
                        <?php else: ?>
                            <i class="bi bi-mortarboard-fill fs-2"></i>
                        <?php endif; ?>
                    </div>
                    <h4 class="fw-bold text-slate-900 mb-1" style="color: #0f172a;">Portal E-Learning</h4>
                    <p class="small text-muted mb-0">SMK Muthia Harapan Cicalengka</p>
                </div>

                <div class="mb-4">
                    <h3 class="fw-bold mb-1" style="color: #0f172a; letter-spacing: -0.5px;">Selamat Datang! 👋</h3>
                    <p class="text-muted small">Silakan masukkan akun Anda untuk masuk ke sistem.</p>
                </div>

                <!-- Main Form -->
                <form action="<?= BASE_URL ?>login.php" method="POST">
                    <?= Security::csrfField() ?>

                    <!-- Username / Email Field -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-slate-700 mb-1" style="color: #334155;">Username / Email</label>
                        <div class="custom-input-group">
                            <i class="bi bi-person custom-input-icon"></i>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username atau email terdaftar" required autofocus>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold text-slate-700 mb-0" style="color: #334155;">Password</label>
                            <a href="<?= BASE_URL ?>index.php?url=auth/forgotPassword" class="small text-decoration-none fw-bold" style="color: #4f46e5;">
                                Lupa Password?
                            </a>
                        </div>
                        <div class="custom-input-group">
                            <i class="bi bi-lock custom-input-icon"></i>
                            <input type="password" name="password" id="loginPassword" class="form-control" style="padding-right: 48px;" placeholder="Masukkan password akun Anda" required>
                            <button type="button" class="password-toggle-btn" onclick="toggleLoginPassword()" title="Tampilkan / Sembunyikan Password">
                                <i class="bi bi-eye" id="loginToggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Math Captcha Security Box -->
                    <div class="captcha-card mb-3">
                        <label class="form-label small fw-bold d-flex align-items-center gap-2 mb-2" style="color: #1e40af;">
                            <i class="bi bi-shield-check text-primary fs-5"></i> Verifikasi Keamanan Captcha
                        </label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="captcha-badge flex-shrink-0">
                                <?= $captchaQuestion ?> = ?
                            </div>
                            <input type="number" name="captcha" class="form-control border-1 rounded-3" style="height: 46px; font-weight: 600; font-size: 1rem; color: #1e293b; text-align: center;" placeholder="Jawaban" required>
                        </div>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check">
                            <input type="checkbox" name="remember_me" value="1" class="form-check-input" id="rememberMe" style="cursor: pointer;">
                            <label class="form-check-label small text-muted user-select-none" for="rememberMe" style="cursor: pointer;">
                                Ingat saya di perangkat ini
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-login-submit w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-box-arrow-in-right fs-5"></i> Masuk Sekarang
                    </button>
                </form>

                <!-- Multi-Role Hak Akses Indicator Footnote -->
                <div class="mt-4 pt-3 border-top text-center">
                    <span class="small text-muted d-block mb-2">Hak Akses Sistem Terintegrasi:</span>
                    <div class="d-flex justify-content-center flex-wrap gap-2">
                        <span class="role-pill"><i class="bi bi-person-badge me-1"></i>Siswa</span>
                        <span class="role-pill"><i class="bi bi-person-video3 me-1"></i>Guru</span>
                        <span class="role-pill"><i class="bi bi-shield-gear me-1"></i>Admin</span>
                        <span class="role-pill"><i class="bi bi-award me-1"></i>Kepala Sekolah</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function toggleLoginPassword() {
    const input = document.getElementById('loginPassword');
    const icon = document.getElementById('loginToggleIcon');
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash text-primary';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>

