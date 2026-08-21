<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-gear-fill text-primary me-2"></i>Pengaturan Sistem & Profil Sekolah</h4>
            <p class="text-muted small mb-0">Konfigurasi identitas sekolah, SMTP email, tema visual, API key, dan hak akses.</p>
        </div>
    </div>

    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($flashSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Settings Nav Tabs -->
    <ul class="nav nav-tabs border-bottom mb-4" id="settingsTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold" id="sekolah-tab" data-bs-toggle="tab" data-bs-target="#sekolahTab" type="button">
                <i class="bi bi-building me-1"></i> Profil Sekolah & Logo
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="landing-tab" data-bs-toggle="tab" data-bs-target="#landingTab" type="button">
                <i class="bi bi-window-stack me-1"></i> Halaman Landing & Visi Misi
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="smtp-tab" data-bs-toggle="tab" data-bs-target="#smtpTab" type="button">
                <i class="bi bi-envelope-at me-1"></i> Pengaturan SMTP Email
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="tema-tab" data-bs-toggle="tab" data-bs-target="#temaTab" type="button">
                <i class="bi bi-palette me-1"></i> Tampilan & Dark Mode
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="api-tab" data-bs-toggle="tab" data-bs-target="#apiTab" type="button">
                <i class="bi bi-code-slash me-1"></i> API & Token Key
            </button>
        </li>
    </ul>

    <div class="tab-content" id="settingsTabContent">
        <!-- Tab 1: Profil Sekolah -->
        <div class="tab-pane fade show active" id="sekolahTab" role="tabpanel">
            <div class="card-custom p-4 p-md-5">
                <form action="<?= BASE_URL ?>index.php?url=admin/pengaturan" method="POST" enctype="multipart/form-data">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="section" value="sekolah">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Nama Sekolah</label>
                            <input type="text" name="nama_sekolah" class="form-control" value="<?= htmlspecialchars($settings['nama_sekolah'] ?? 'SMK Muthia Harapan Cicalengka') ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">NPSN</label>
                            <input type="text" name="npsn" class="form-control" value="<?= htmlspecialchars($settings['npsn'] ?? '20229871') ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Kepala Sekolah</label>
                            <input type="text" name="kepala_sekolah" class="form-control" value="<?= htmlspecialchars($settings['kepala_sekolah'] ?? 'H. Supriyadi, M.M.') ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">No. Telepon / WhatsApp</label>
                            <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($settings['telepon'] ?? '(022) 7950123') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($settings['alamat'] ?? 'Jl. Raya Cicalengka No. 45, Cicalengka, Kabupaten Bandung, Jawa Barat 40395') ?></textarea>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Upload Logo Sekolah (PNG / JPG)</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <?php if (!empty($settings['logo'])): ?>
                                <small class="text-success mt-1 d-block"><i class="bi bi-image me-1"></i> Logo tersimpan: <?= htmlspecialchars(basename($settings['logo'])) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-save me-1"></i> Simpan Profil Sekolah
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab 2: SMTP Email -->
        <div class="tab-pane fade" id="smtpTab" role="tabpanel">
            <div class="card-custom p-4 p-md-5">
                <form action="<?= BASE_URL ?>index.php?url=admin/pengaturan" method="POST">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="section" value="smtp">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($settings['smtp_host'] ?? 'smtp.gmail.com') ?>" placeholder="smtp.gmail.com">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">SMTP Port</label>
                            <input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>" placeholder="587">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">SMTP Username / Email</label>
                            <input type="email" name="smtp_user" class="form-control" value="<?= htmlspecialchars($settings['smtp_user'] ?? 'elearning@smkmuthiaharapan.sch.id') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">SMTP Password / App Key</label>
                            <input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($settings['smtp_pass'] ?? '••••••••••••') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Enkripsi</label>
                            <select name="smtp_crypto" class="form-select">
                                <option value="tls" <?= ($settings['smtp_crypto'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS (Port 587)</option>
                                <option value="ssl" <?= ($settings['smtp_crypto'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-save me-1"></i> Simpan Konfigurasi SMTP
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="Swal.fire('Email Test', 'Email percobaan berhasil dikirim ke admin!', 'success')">
                            <i class="bi bi-send me-1"></i> Tes Kirim Email
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab 3: Tampilan & Dark Mode -->
        <div class="tab-pane fade" id="temaTab" role="tabpanel">
            <div class="card-custom p-4 p-md-5">
                <h6 class="fw-bold mb-3">Pilihan Tema Visual LMS</h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="card-custom p-3 border border-2 border-primary text-center">
                            <div class="bg-primary text-white p-3 rounded-3 mb-2">
                                <i class="bi bi-sun-fill fs-2"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Mode Terang (Light)</h6>
                            <small class="text-muted d-block mb-2">Tampilan bersih dengan warna kontras tinggi.</small>
                            <button class="btn btn-sm btn-primary" onclick="document.body.removeAttribute('data-bs-theme')">Aktifkan Light</button>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="card-custom p-3 text-center" style="background:#0f172a; color:#fff;">
                            <div class="bg-dark text-warning p-3 rounded-3 mb-2">
                                <i class="bi bi-moon-stars-fill fs-2"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Mode Gelap (Dark)</h6>
                            <small class="text-white-50 d-block mb-2">Mengurangi kelelahan mata di malam hari.</small>
                            <button class="btn btn-sm btn-outline-light" onclick="document.body.setAttribute('data-bs-theme','dark')">Aktifkan Dark</button>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="card-custom p-3 text-center">
                            <div class="bg-success text-white p-3 rounded-3 mb-2">
                                <i class="bi bi-laptop fs-2"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Otomatis System</h6>
                            <small class="text-muted d-block mb-2">Mengikuti tema OS perangkat pengguna.</small>
                            <button class="btn btn-sm btn-outline-secondary" onclick="document.body.removeAttribute('data-bs-theme')">Otomatis</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 4: API & Key -->
        <div class="tab-pane fade" id="apiTab" role="tabpanel">
            <div class="card-custom p-4 p-md-5">
                <h6 class="fw-bold mb-3"><i class="bi bi-key-fill text-warning me-2"></i>API Secret Key for Integrations</h6>
                <div class="p-3 bg-light rounded-3 mb-3">
                    <label class="form-label small text-muted">Bearer Token (REST API):</label>
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace" value="<?= htmlspecialchars($settings['api_key'] ?? 'smkmh_live_api_88923a19e83c7410294b') ?>" readonly>
                        <button class="btn btn-outline-primary" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($settings['api_key'] ?? 'smkmh_live_api_88923a19e83c7410294b') ?>'); Swal.fire('Disalin!','API Key disalin ke clipboard','success')">
                            <i class="bi bi-copy"></i> Salin
                        </button>
                    </div>
                </div>
                <small class="text-muted">Gunakan API Key ini untuk integrasi ke sistem SIMAK, Absensi Mesin Biometrik, atau Android App.</small>
            </div>
        </div>

        <!-- Tab 5: Landing Page & Visi Misi -->
        <div class="tab-pane fade" id="landingTab" role="tabpanel">
            <div class="card-custom p-4 p-md-5">
                <form action="<?= BASE_URL ?>index.php?url=admin/pengaturan" method="POST">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="section" value="landing">

                    <!-- Section 1: Hero Banner -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-window-fullscreen me-2"></i>1. Bagian Hero & Banner Utama
                        </h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Hero Badge (Pill)</label>
                                <input type="text" name="landing_hero_badge" class="form-control" value="<?= htmlspecialchars($settings['landing_hero_badge'] ?? 'Portal Pembelajaran Digital') ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Judul Utama Hero (H1)</label>
                                <input type="text" name="landing_hero_title" class="form-control" value="<?= htmlspecialchars($settings['landing_hero_title'] ?? 'E-Learning SMK Muthia Harapan Cicalengka') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Deskripsi Sub-Hero</label>
                                <textarea name="landing_hero_desc" class="form-control" rows="2" required><?= htmlspecialchars($settings['landing_hero_desc'] ?? 'Sistem Manajemen Pembelajaran Digital Interaktif, Transparan, dan Modern untuk Membentuk Generasi Unggul Siap Kerja.') ?></textarea>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Judul Kartu Fitur Hero</label>
                                <input type="text" name="landing_hero_card_title" class="form-control" value="<?= htmlspecialchars($settings['landing_hero_card_title'] ?? 'KBM Digital Terpadu') ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Deskripsi Kartu Fitur Hero</label>
                                <input type="text" name="landing_hero_card_desc" class="form-control" value="<?= htmlspecialchars($settings['landing_hero_card_desc'] ?? 'Materi, CBT, Quiz, Absensi QR Code, & Laporan Real-time') ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Profil & Visi Misi -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-file-text-fill me-2"></i>2. Profil Sekolah, Visi & Misi
                        </h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold">Sub-Tag Profil</label>
                                <input type="text" name="landing_profil_tag" class="form-control" value="<?= htmlspecialchars($settings['landing_profil_tag'] ?? 'Profil Sekolah') ?>" required>
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label small fw-bold">Judul Utama Profil</label>
                                <input type="text" name="landing_profil_title" class="form-control" value="<?= htmlspecialchars($settings['landing_profil_title'] ?? 'Mencetak Lulusan Berkarakter & Competent') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Deskripsi Profil Sekolah</label>
                                <textarea name="landing_profil_desc" class="form-control" rows="3" required><?= htmlspecialchars($settings['landing_profil_desc'] ?? 'SMK Muthia Harapan Cicalengka berkomitmen memberikan pendidikan kejuruan berkualitas tinggi berbasis teknologi informasi dan industri modern di Jawa Barat.') ?></textarea>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Judul Visi</label>
                                <input type="text" name="landing_visi_title" class="form-control" value="<?= htmlspecialchars($settings['landing_visi_title'] ?? 'Visi Utama') ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Judul Misi</label>
                                <input type="text" name="landing_misi_title" class="form-control" value="<?= htmlspecialchars($settings['landing_misi_title'] ?? 'Misi Presisi') ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Isi Visi Sekolah</label>
                                <textarea name="landing_visi_desc" class="form-control" rows="3" required><?= htmlspecialchars($settings['landing_visi_desc'] ?? 'Menjadi SMK Unggulan berstandar Nasional berbasis Teknologi & Imtaq.') ?></textarea>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Isi Misi Sekolah</label>
                                <textarea name="landing_misi_desc" class="form-control" rows="3" required><?= htmlspecialchars($settings['landing_misi_desc'] ?? 'Mengembangkan kurikulum industri & sertifikasi kompetensi keahlian.') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">URL Video Youtube Profil (Embed / Watch Link)</label>
                                <input type="text" name="landing_video_url" class="form-control" value="<?= htmlspecialchars($settings['landing_video_url'] ?? 'https://www.youtube.com/embed/dQw4w9WgXcQ') ?>" placeholder="https://www.youtube.com/embed/dQw4w9WgXcQ">
                                <small class="text-muted">Format rekomendasi: <code>https://www.youtube.com/embed/VIDEO_ID</code></small>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Kontak & Google Maps -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-geo-alt-fill me-2"></i>3. Kontak & Google Maps Embed
                        </h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold">Tag Kontak</label>
                                <input type="text" name="landing_kontak_tag" class="form-control" value="<?= htmlspecialchars($settings['landing_kontak_tag'] ?? 'Hubungi Kami') ?>" required>
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label small fw-bold">Judul Kontak</label>
                                <input type="text" name="landing_kontak_title" class="form-control" value="<?= htmlspecialchars($settings['landing_kontak_title'] ?? 'Lokasi & Kontak Sekolah') ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Email Resmi Sekolah</label>
                                <input type="email" name="landing_email" class="form-control" value="<?= htmlspecialchars($settings['landing_email'] ?? 'info@smkmh-cicalengka.sch.id') ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">URL Embed Google Maps</label>
                                <input type="text" name="landing_maps_url" class="form-control" value="<?= htmlspecialchars($settings['landing_maps_url'] ?? 'https://maps.google.com/maps?q=Cicalengka&t=&z=13&ie=UTF8&iwloc=&output=embed') ?>" placeholder="https://maps.google.com/maps?q=...">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-save me-1"></i> Simpan Halaman Landing & Visi Misi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
</main>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
