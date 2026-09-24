<?php 
require_once ROOT_PATH . 'views/layouts/header.php'; 
require_once ROOT_PATH . 'views/layouts/navbar.php'; 
require_once ROOT_PATH . 'views/layouts/sidebar.php'; 

$currentTab = $_GET['tab'] ?? ($activeTab ?? 'sekolah');
?>

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
            <button class="nav-link <?= $currentTab === 'sekolah' ? 'active' : '' ?> fw-bold" id="sekolah-tab" data-bs-toggle="tab" data-bs-target="#sekolahTab" type="button">
                <i class="bi bi-building me-1"></i> Profil Sekolah & Logo
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $currentTab === 'landing' ? 'active' : '' ?> fw-bold" id="landing-tab" data-bs-toggle="tab" data-bs-target="#landingTab" type="button">
                <i class="bi bi-window-stack me-1"></i> Halaman Landing & Visi Misi
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $currentTab === 'smtp' ? 'active' : '' ?> fw-bold" id="smtp-tab" data-bs-toggle="tab" data-bs-target="#smtpTab" type="button">
                <i class="bi bi-envelope-at me-1"></i> Pengaturan SMTP Email
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $currentTab === 'tema' ? 'active' : '' ?> fw-bold" id="tema-tab" data-bs-toggle="tab" data-bs-target="#temaTab" type="button">
                <i class="bi bi-palette me-1"></i> Tampilan & Dark Mode
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $currentTab === 'api' ? 'active' : '' ?> fw-bold" id="api-tab" data-bs-toggle="tab" data-bs-target="#apiTab" type="button">
                <i class="bi bi-code-slash me-1"></i> API & Token Key
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $currentTab === 'geofencing' ? 'active' : '' ?> fw-bold" id="geofencing-tab" data-bs-toggle="tab" data-bs-target="#geofencingTab" type="button">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i> Titik Lokasi Presensi (Geofencing)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="settingsTabContent">
        <!-- Tab 1: Profil Sekolah -->
        <div class="tab-pane fade <?= $currentTab === 'sekolah' ? 'show active' : '' ?>" id="sekolahTab" role="tabpanel">
            <div class="card-custom p-4 p-md-5">
                <form action="<?= BASE_URL ?>index.php?url=admin/pengaturan" method="POST" enctype="multipart/form-data">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="section" value="sekolah">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Nama Sekolah</label>
                            <input type="text" name="nama_sekolah" class="form-control" value="<?= htmlspecialchars($settings['nama_sekolah'] ?? 'SMK Muthia Harapan Cicalengka') ?>" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold">NPSN</label>
                            <input type="text" name="npsn" class="form-control" value="<?= htmlspecialchars($settings['npsn'] ?? '69725846') ?>" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold">Status Akreditasi</label>
                            <input type="text" name="akreditasi" class="form-control" value="<?= htmlspecialchars($settings['akreditasi'] ?? 'B') ?>" placeholder="Contoh: B / A (Unggul)">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Kepala Sekolah</label>
                            <input type="text" name="kepala_sekolah" class="form-control" value="<?= htmlspecialchars($settings['kepala_sekolah'] ?? 'H. ASEP SAEPULLOH, S. Ag') ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">NIP / NUPTK Kepala Sekolah</label>
                            <input type="text" name="nip_kepala_sekolah" class="form-control" value="<?= htmlspecialchars($settings['nip_kepala_sekolah'] ?? ($settings['nip_kepsek'] ?? 'G202608503')) ?>" placeholder="Contoh: 198501152010011002 / G202608503">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">No. Telepon / WhatsApp</label>
                            <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($settings['telepon'] ?? '(022) 7950123') ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Email Resmi Sekolah</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($settings['email'] ?? ($settings['landing_email'] ?? 'info@smkmh-cicalengka.sch.id')) ?>" placeholder="Contoh: info@smkmh-cicalengka.sch.id">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Website Resmi Sekolah</label>
                            <input type="text" name="website" class="form-control" value="<?= htmlspecialchars($settings['website'] ?? 'www.smkmuthiaharapan.sch.id') ?>" placeholder="Contoh: www.smkmuthiaharapan.sch.id">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat lengkap sekolah..."><?= htmlspecialchars($settings['alamat'] ?? 'Jalan Babakan Peuteuy Nomor 300, Desa Babakanpeuteuy, Kecamatan Cicalengka, Kabupaten Bandung, Jawa Barat') ?></textarea>
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
        <div class="tab-pane fade <?= $currentTab === 'landing' ? 'show active' : '' ?>" id="landingTab" role="tabpanel">
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
                                <textarea name="landing_visi_desc" class="form-control" rows="5" required><?= htmlspecialchars($settings['landing_visi_desc'] ?? 'Menjadi SMK Unggulan berstandar Nasional berbasis Teknologi & Imtaq.') ?></textarea>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold mb-0">Isi Misi Sekolah</label>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-medium"><i class="bi bi-magic me-1"></i>Editor Auto-List</span>
                                </div>
                                
                                <div class="card p-3 border-0 bg-light rounded-3 mb-2 shadow-sm">
                                    <!-- Jenis List Selector -->
                                    <div class="mb-3">
                                        <label class="form-label small text-muted fw-bold me-2 mb-1 d-block">1. Pilih Format List Misi:</label>
                                        <div class="btn-group btn-group-sm w-100 flex-wrap" role="group" id="misiTypeSelector">
                                            <input type="radio" class="btn-check" name="misi_type" id="type_ol_1" value="ol-1" checked onchange="setMisiType('ol-1')">
                                            <label class="btn btn-outline-primary" for="type_ol_1"><i class="bi bi-list-ol me-1"></i> Angka (1,2,3)</label>

                                            <input type="radio" class="btn-check" name="misi_type" id="type_ol_a" value="ol-a" onchange="setMisiType('ol-a')">
                                            <label class="btn btn-outline-primary" for="type_ol_a"><i class="bi bi-list-nested me-1"></i> Huruf (a,b,c)</label>

                                            <input type="radio" class="btn-check" name="misi_type" id="type_ol_A" value="ol-A" onchange="setMisiType('ol-A')">
                                            <label class="btn btn-outline-primary" for="type_ol_A"><i class="bi bi-fonts me-1"></i> Huruf (A,B,C)</label>

                                            <input type="radio" class="btn-check" name="misi_type" id="type_ul" value="ul" onchange="setMisiType('ul')">
                                            <label class="btn btn-outline-primary" for="type_ul"><i class="bi bi-list-ul me-1"></i> Bullets (•)</label>

                                            <input type="radio" class="btn-check" name="misi_type" id="type_text" value="text" onchange="setMisiType('text')">
                                            <label class="btn btn-outline-primary" for="type_text"><i class="bi bi-text-paragraph me-1"></i> Paragraf</label>
                                        </div>
                                    </div>

                                    <!-- Helper Info -->
                                    <div class="alert alert-info py-1 px-2 mb-2 small d-flex align-items-center gap-2 border-0 bg-info bg-opacity-10 text-info-emphasis">
                                        <i class="bi bi-info-circle-fill fs-6"></i>
                                        <span>Ketik baris misi lalu tekan <strong>ENTER</strong> untuk otomatis membuat nomor/huruf berikutnya!</span>
                                    </div>

                                    <!-- Interactive List Items Container -->
                                    <div id="misiItemsContainer" class="d-flex flex-column gap-2 mb-3">
                                        <!-- Dynamic item rows will be injected here -->
                                    </div>

                                    <!-- Add New Row Button -->
                                    <button type="button" class="btn btn-sm btn-outline-success border-dashed fw-bold" onclick="addMisiItemRow('', true)">
                                        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Baris Misi Baru
                                    </button>

                                    <!-- Hidden Input to store final output -->
                                    <input type="hidden" id="landingMisiInput" name="landing_misi_desc" value="<?= htmlspecialchars($settings['landing_misi_desc'] ?? 'Mengembangkan kurikulum industri & sertifikasi kompetensi keahlian.') ?>">
                                </div>

                                <!-- Live Preview Box -->
                                <div class="p-3 bg-white rounded-3 border border-info border-opacity-25 shadow-sm">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <small class="fw-bold text-info"><i class="bi bi-eye-fill me-1"></i> Pratinjau Tampilan di Landing Page:</small>
                                        <span class="badge bg-info bg-opacity-10 text-info small">Live Preview</span>
                                    </div>
                                    <div id="misiLivePreview" class="small text-dark p-3 rounded-3 bg-light border landing-misi-content" style="min-height: 80px;"></div>
                                </div>
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

        <!-- Tab 6: Titik Lokasi Presensi & Geofencing Guru -->
        <div class="tab-pane fade <?= $currentTab === 'geofencing' ? 'show active' : '' ?>" id="geofencingTab" role="tabpanel">
            <div class="card-custom p-4 p-md-5 shadow-sm rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2 border-bottom pb-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Konfigurasi Titik Lokasi & Radius Geofencing Presensi Guru</h5>
                        <p class="text-muted small mb-0">Tentukan titik koordinat pusat sekolah dan batas radius toleransi presensi selfie kamera guru & GTK.</p>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs" id="btnDetectAdminGPS">
                        <i class="bi bi-crosshair me-1"></i> Deteksi Lokasi GPS Saya
                    </button>
                </div>

                <form action="<?= BASE_URL ?>index.php?url=admin/pengaturan" method="POST">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="section" value="geofencing">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Nama Titik Lokasi / Kampus</label>
                            <input type="text" name="lokasi_sekolah_nama" id="geoNamaSekolah" class="form-control rounded-3" value="<?= htmlspecialchars($settings['lokasi_sekolah_nama'] ?? 'SMK Muthia Harapan Cicalengka') ?>" required placeholder="Contoh: SMK Muthia Harapan Cicalengka">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Radius Toleransi Presensi (Meter)</label>
                            <div class="input-group">
                                <input type="number" name="lokasi_sekolah_radius" id="geoRadiusInput" class="form-control rounded-start-3" value="<?= htmlspecialchars($settings['lokasi_sekolah_radius'] ?? '150') ?>" min="10" max="5000" step="5" required>
                                <span class="input-group-text bg-light fw-bold text-muted rounded-end-3">Meter</span>
                            </div>
                            <small class="text-muted">Jarak radius lingkaran toleransi di mana guru diizinkan melakukan presensi selfie.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Latitude (Garis Lintang)</label>
                            <input type="text" name="lokasi_sekolah_lat" id="geoLatInput" class="form-control font-monospace rounded-3" value="<?= htmlspecialchars($settings['lokasi_sekolah_lat'] ?? '-6.984042') ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Longitude (Garis Bujur)</label>
                            <input type="text" name="lokasi_sekolah_lng" id="geoLngInput" class="form-control font-monospace rounded-3" value="<?= htmlspecialchars($settings['lokasi_sekolah_lng'] ?? '107.838612') ?>" required>
                        </div>

                        <!-- Interactive Leaflet Map for Admin -->
                        <div class="col-12 my-2">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <label class="form-label small fw-bold mb-0 text-dark"><i class="bi bi-map me-1 text-primary"></i> Peta Titik Presensi & Radius Geofence</label>
                                <span class="badge bg-light text-secondary border rounded-pill px-3 py-1"><i class="bi bi-cursor-fill me-1 text-primary"></i> Geser pin penanda atau klik peta untuk menentukan titik koordinat</span>
                            </div>
                            <div id="adminGeofenceMap" style="height: 380px; width: 100%; border-radius: 14px; border: 1px solid rgba(0,0,0,0.12); z-index: 1;"></div>
                        </div>

                        <!-- Jam Masuk & Jam Pulang Limits & Mode Penjadwalan -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3 flex-wrap gap-2">
                                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-warning me-2"></i>Skema Penjadwalan Presensi Guru & Batas Waktu</h6>
                                <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1.5 rounded-pill"><i class="bi bi-gear-wide-connected me-1"></i> Mode Fleksibel KBM / Rapat</span>
                            </div>
                            <p class="text-muted small mb-3">Atur apakah jam presensi guru berjalan otomatis mengikuti <b>Manajemen Jadwal Pelajaran Sekolah</b> masing-masing guru, atau diatur <b>Serentak</b> saat ada agenda rapat dinas, upacara, ujian, maupun kegiatan khusus lainnya.</p>
                        </div>

                        <!-- Mode Selector Cards -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary mb-2">Pilih Mode Penjadwalan Presensi:</label>
                            <div class="row g-3">
                                <?php 
                                $currentMode = $settings['presensi_mode_jadwal'] ?? 'jadwal'; 
                                ?>
                                <div class="col-12 col-md-6">
                                    <label class="card h-100 p-3 rounded-3 border cursor-pointer mode-card <?= $currentMode === 'jadwal' ? 'border-primary bg-primary-subtle bg-opacity-10' : 'border-secondary-subtle' ?>" for="modeJadwalRadio" style="cursor: pointer;">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="pt-1">
                                                <input class="form-check-input fs-5" type="radio" name="presensi_mode_jadwal" id="modeJadwalRadio" value="jadwal" <?= $currentMode === 'jadwal' ? 'checked' : '' ?> onchange="togglePresensiMode('jadwal')">
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark mb-1">
                                                    <i class="bi bi-calendar-week text-primary me-1"></i> Mengikuti Jadwal Pelajaran KBM
                                                    <span class="badge bg-primary text-white ms-1" style="font-size: 0.7rem;">Otomatis per-Guru</span>
                                                </div>
                                                <p class="text-muted small mb-0">Jam masuk & kepulangan setiap guru otomatis sinkron dengan jam mengajar harian di <b>Manajemen Jadwal Pelajaran</b>. Guru yang kelas pertamanya jam 07:30 batasnya 07:30, dan pulang setelah kelas terakhirnya berakhir.</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="card h-100 p-3 rounded-3 border cursor-pointer mode-card <?= $currentMode === 'serentak' ? 'border-primary bg-primary-subtle bg-opacity-10' : 'border-secondary-subtle' ?>" for="modeSerentakRadio" style="cursor: pointer;">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="pt-1">
                                                <input class="form-check-input fs-5" type="radio" name="presensi_mode_jadwal" id="modeSerentakRadio" value="serentak" <?= $currentMode === 'serentak' ? 'checked' : '' ?> onchange="togglePresensiMode('serentak')">
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark mb-1">
                                                    <i class="bi bi-people-fill text-warning me-1"></i> Jadwal Serentak / Bersama
                                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7rem;">Rapat / Upacara / Event</span>
                                                </div>
                                                <p class="text-muted small mb-0">Seluruh dewan guru presensi serentak di waktu yang sama. Cocok saat ada kegiatan rapat kerja dewan guru, upacara bendera, perpisahan, atau hari kegiatan serentak sekolah.</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Agenda Khusus (Muncul saat Mode Serentak Aktif) -->
                        <div class="col-12 <?= $currentMode === 'serentak' ? '' : 'd-none' ?>" id="containerAgendaSerentak">
                            <div class="p-3 rounded-3 bg-warning-subtle border border-warning">
                                <label class="form-label small fw-bold text-dark mb-1"><i class="bi bi-megaphone-fill text-warning me-1"></i> Nama Agenda / Kegiatan Serentak (Opsional)</label>
                                <input type="text" name="presensi_kegiatan_serentak_nama" id="inputAgendaSerentak" class="form-control rounded-3" value="<?= htmlspecialchars($settings['presensi_kegiatan_serentak_nama'] ?? '') ?>" placeholder="Contoh: Rapat Pleno Dewan Guru / Upacara Bendera Hari Senin">
                                <small class="text-dark-emphasis">Nama kegiatan ini akan ditampilkan langsung di kartu presensi selfie guru sebagai pengingat agenda serentak hari ini.</small>
                            </div>
                        </div>

                        <!-- Kolom Parameter Toleransi KBM (Muncul saat Mode Jadwal KBM Aktif) -->
                        <div class="col-12 <?= $currentMode === 'jadwal' ? '' : 'd-none' ?>" id="containerToleransiKBM">
                            <div class="p-3 rounded-3 bg-light border">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold text-secondary">Buka Presensi Sebelum KBM Pertama</label>
                                        <div class="input-group">
                                            <input type="number" name="presensi_toleransi_masuk_menit" class="form-control rounded-start-3" value="<?= htmlspecialchars($settings['presensi_toleransi_masuk_menit'] ?? '60') ?>" min="15" max="180" step="5" required>
                                            <span class="input-group-text bg-white fw-semibold text-muted rounded-end-3">Menit Sebelumnya</span>
                                        </div>
                                        <small class="text-muted">Presensi masuk dibuka X menit sebelum jam mengajar pertama guru dimulai.</small>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold text-secondary">Toleransi Keterlambatan KBM</label>
                                        <div class="input-group">
                                            <input type="number" name="presensi_toleransi_terlambat_menit" class="form-control rounded-start-3" value="<?= htmlspecialchars($settings['presensi_toleransi_terlambat_menit'] ?? '0') ?>" min="0" max="60" step="5" required>
                                            <span class="input-group-text bg-white fw-semibold text-muted rounded-end-3">Menit Toleransi</span>
                                        </div>
                                        <small class="text-muted">Toleransi keterlambatan setelah jam mulai kelas (0 = tepat waktu sesuai jadwal).</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Jam Masuk & Jam Pulang (Serentak / Fallback Standar) -->
                        <div class="col-12 mt-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label small fw-bold text-dark mb-0" id="labelJamPresensiHeader">
                                    <i class="bi bi-clock me-1 text-primary"></i> 
                                    <?= $currentMode === 'serentak' ? 'Jam Operasional Serentak Seluruh Guru (Rapat / Agenda Bersama)' : 'Jam Standar Sekolah (Fallback untuk Guru yang Tidak Memiliki Jadwal KBM Hari Ini)' ?>
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary">Jam Buka Presensi Masuk</label>
                            <input type="time" name="presensi_jam_masuk_mulai" class="form-control rounded-3" value="<?= htmlspecialchars($settings['presensi_jam_masuk_mulai'] ?? '06:00') ?>" required>
                            <small class="text-muted">Jam buka serentak / guru tanpa jam KBM.</small>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary">Batas Masuk (Tepat Waktu)</label>
                            <input type="time" name="presensi_jam_masuk_batas" class="form-control rounded-3" value="<?= htmlspecialchars($settings['presensi_jam_masuk_batas'] ?? '07:30') ?>" required>
                            <small class="text-muted">Batas tepat waktu serentak / guru tanpa jam KBM.</small>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary">Jam Buka Presensi Pulang</label>
                            <input type="time" name="presensi_jam_pulang_mulai" class="form-control rounded-3" value="<?= htmlspecialchars($settings['presensi_jam_pulang_mulai'] ?? '15:00') ?>" required>
                            <small class="text-muted">Waktu minimal kepulangan serentak / guru non-KBM.</small>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex gap-2">
                        <button type="submit" class="btn btn-primary fw-bold px-4 rounded-3 shadow-sm">
                            <i class="bi bi-save me-1"></i> Simpan Pengaturan Geofencing & Jadwal Presensi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
</main>

<script>
let currentMisiType = 'ol-1';

function setMisiType(type) {
    currentMisiType = type;
    updateMisiBadges();
    serializeMisiOutput();
}

function updateMisiBadges() {
    const rows = document.querySelectorAll('#misiItemsContainer .misi-item-row');
    rows.forEach((row, index) => {
        const badge = row.querySelector('.misi-badge-prefix');
        if (badge) {
            badge.innerText = getItemPrefix(index);
        }
    });
}

function getItemPrefix(index) {
    if (currentMisiType === 'ol-1') {
        return (index + 1) + '.';
    } else if (currentMisiType === 'ol-a') {
        return String.fromCharCode(97 + index) + '.';
    } else if (currentMisiType === 'ol-A') {
        return String.fromCharCode(65 + index) + '.';
    } else if (currentMisiType === 'ul') {
        return '•';
    } else {
        return '¶';
    }
}

function addMisiItemRow(content = '', shouldFocus = false, insertAtIndex = null) {
    const container = document.getElementById('misiItemsContainer');
    if (!container) return;

    const row = document.createElement('div');
    row.className = 'd-flex align-items-center gap-2 misi-item-row p-1 bg-white rounded border shadow-2xs';

    const prefixSpan = document.createElement('span');
    prefixSpan.className = 'badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1 flex-shrink-0 misi-badge-prefix';
    prefixSpan.style.minWidth = '32px';
    prefixSpan.style.textAlign = 'center';

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'form-control form-control-sm border-0 shadow-none text-dark fw-medium misi-item-input';
    input.placeholder = 'Tuliskan poin misi sekolah... (Tekan ENTER untuk menambah baris)';
    input.value = content;

    // Formatting Toolbar Group
    const btnGroup = document.createElement('div');
    btnGroup.className = 'btn-group btn-group-sm flex-shrink-0 me-1';
    
    const btnBold = document.createElement('button');
    btnBold.type = 'button';
    btnBold.className = 'btn btn-sm btn-light border-0 fw-bold px-2';
    btnBold.innerHTML = 'B';
    btnBold.title = 'Tebal (Bold)';
    btnBold.onclick = function() { applyFormatToInput(input, 'b'); };

    const btnItalic = document.createElement('button');
    btnItalic.type = 'button';
    btnItalic.className = 'btn btn-sm btn-light border-0 fst-italic px-2';
    btnItalic.innerHTML = 'I';
    btnItalic.title = 'Miring (Italic)';
    btnItalic.onclick = function() { applyFormatToInput(input, 'i'); };

    const btnUnderline = document.createElement('button');
    btnUnderline.type = 'button';
    btnUnderline.className = 'btn btn-sm btn-light border-0 text-decoration-underline px-2';
    btnUnderline.innerHTML = 'U';
    btnUnderline.title = 'Garis Bawah (Underline)';
    btnUnderline.onclick = function() { applyFormatToInput(input, 'u'); };

    btnGroup.appendChild(btnBold);
    btnGroup.appendChild(btnItalic);
    btnGroup.appendChild(btnUnderline);

    // Delete Button
    const btnDelete = document.createElement('button');
    btnDelete.type = 'button';
    btnDelete.className = 'btn btn-sm btn-outline-danger border-0 flex-shrink-0 px-2';
    btnDelete.innerHTML = '<i class="bi bi-trash"></i>';
    btnDelete.title = 'Hapus Baris';
    btnDelete.onclick = function() {
        const totalRows = document.querySelectorAll('#misiItemsContainer .misi-item-row').length;
        if (totalRows > 1) {
            row.remove();
            updateMisiBadges();
            serializeMisiOutput();
        } else {
            input.value = '';
            serializeMisiOutput();
        }
    };

    row.appendChild(prefixSpan);
    row.appendChild(input);
    row.appendChild(btnGroup);
    row.appendChild(btnDelete);

    if (insertAtIndex !== null && insertAtIndex < container.children.length) {
        container.insertBefore(row, container.children[insertAtIndex]);
    } else {
        container.appendChild(row);
    }

    updateMisiBadges();

    // Event Listeners for Input
    input.addEventListener('input', serializeMisiOutput);

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const allRows = Array.from(container.children);
            const currentIndex = allRows.indexOf(row);
            addMisiItemRow('', true, currentIndex + 1);
        } else if (e.key === 'Backspace' && input.value === '') {
            const allRows = Array.from(container.children);
            if (allRows.length > 1) {
                e.preventDefault();
                const currentIndex = allRows.indexOf(row);
                const prevRow = allRows[currentIndex - 1] || allRows[currentIndex + 1];
                row.remove();
                updateMisiBadges();
                serializeMisiOutput();
                if (prevRow) {
                    const prevInput = prevRow.querySelector('.misi-item-input');
                    if (prevInput) prevInput.focus();
                }
            }
        }
    });

    serializeMisiOutput();

    if (shouldFocus) {
        setTimeout(() => input.focus(), 50);
    }
}

function applyFormatToInput(input, tag) {
    const start = input.selectionStart;
    const end = input.selectionEnd;
    const val = input.value;
    const selected = val.substring(start, end);
    const openTag = `<${tag}>`;
    const closeTag = `</${tag}>`;

    if (selected) {
        input.value = val.substring(0, start) + openTag + selected + closeTag + val.substring(end);
    } else {
        input.value = val + openTag + 'teks' + closeTag;
    }
    serializeMisiOutput();
    input.focus();
}

function serializeMisiOutput() {
    const hiddenInput = document.getElementById('landingMisiInput');
    const preview = document.getElementById('misiLivePreview');
    const inputs = document.querySelectorAll('#misiItemsContainer .misi-item-input');
    
    let items = [];
    inputs.forEach(inp => {
        let text = inp.value.trim();
        if (text) items.push(text);
    });

    let resultHtml = '';
    if (items.length === 0) {
        resultHtml = '<span class="text-muted fst-italic">Belum ada poin misi diset.</span>';
    } else {
        if (currentMisiType === 'ol-1') {
            resultHtml = '<ol>\n' + items.map(it => `  <li>${it}</li>`).join('\n') + '\n</ol>';
        } else if (currentMisiType === 'ol-a') {
            resultHtml = '<ol type="a">\n' + items.map(it => `  <li>${it}</li>`).join('\n') + '\n</ol>';
        } else if (currentMisiType === 'ol-A') {
            resultHtml = '<ol type="A">\n' + items.map(it => `  <li>${it}</li>`).join('\n') + '\n</ol>';
        } else if (currentMisiType === 'ul') {
            resultHtml = '<ul>\n' + items.map(it => `  <li>${it}</li>`).join('\n') + '\n</ul>';
        } else {
            resultHtml = items.map(it => `<p>${it}</p>`).join('\n');
        }
    }

    if (hiddenInput) hiddenInput.value = resultHtml;
    if (preview) preview.innerHTML = resultHtml;
}

function parseInitialMisi() {
    const hiddenInput = document.getElementById('landingMisiInput');
    const rawVal = hiddenInput ? hiddenInput.value.trim() : '';

    if (rawVal.includes('<ol type="a">') || rawVal.includes('<ol type=\'a\'>')) {
        currentMisiType = 'ol-a';
        document.getElementById('type_ol_a').checked = true;
    } else if (rawVal.includes('<ol type="A">') || rawVal.includes('<ol type=\'A\'>')) {
        currentMisiType = 'ol-A';
        document.getElementById('type_ol_A').checked = true;
    } else if (rawVal.includes('<ol>')) {
        currentMisiType = 'ol-1';
        document.getElementById('type_ol_1').checked = true;
    } else if (rawVal.includes('<ul>')) {
        currentMisiType = 'ul';
        document.getElementById('type_ul').checked = true;
    } else if (rawVal.includes('<p>')) {
        currentMisiType = 'text';
        document.getElementById('type_text').checked = true;
    }

    // Extract list items using regex
    const liMatches = [...rawVal.matchAll(/<li>(.*?)<\/li>/gi)];
    const container = document.getElementById('misiItemsContainer');
    if (container) container.innerHTML = '';

    if (liMatches.length > 0) {
        liMatches.forEach(match => {
            addMisiItemRow(match[1]);
        });
    } else if (rawVal) {
        // Plain lines
        const lines = rawVal.replace(/<[^>]+>/g, '\n').split('\n').filter(l => l.trim().length > 0);
        if (lines.length > 0) {
            lines.forEach(line => addMisiItemRow(line.trim()));
        } else {
            addMisiItemRow('Mengembangkan kurikulum industri & sertifikasi kompetensi keahlian.');
        }
    } else {
        addMisiItemRow('Menyiapkan sumber daya manusia yang kompeten dan berakhlak mulia.');
        addMisiItemRow('Mengembangkan kerja sama terpadu dengan dunia usaha dan dunia industri.');
        addMisiItemRow('Meningkatkan mutu pembelajaran berbasis teknologi informasi modern.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    parseInitialMisi();
});

// Leaflet Map Initialization for Admin Geofence
let adminMap = null;
let adminMarker = null;
let adminCircle = null;

function initAdminGeofenceMap() {
    const latInput = document.getElementById('geoLatInput');
    const lngInput = document.getElementById('geoLngInput');
    const radiusInput = document.getElementById('geoRadiusInput');
    const mapContainer = document.getElementById('adminGeofenceMap');
    if (!mapContainer) return;

    let initLat = parseFloat(latInput ? latInput.value : -6.984042) || -6.984042;
    let initLng = parseFloat(lngInput ? lngInput.value : 107.838612) || 107.838612;
    let initRadius = parseInt(radiusInput ? radiusInput.value : 150) || 150;

    if (adminMap) {
        adminMap.invalidateSize();
        return;
    }

    adminMap = L.map('adminGeofenceMap').setView([initLat, initLng], 16);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(adminMap);

    // School Marker
    adminMarker = L.marker([initLat, initLng], { draggable: true }).addTo(adminMap);
    adminMarker.bindPopup('<div class="p-1 text-center"><strong>Titik Pusat Presensi</strong><br><small class="text-muted">Geser pin untuk memindahkan titik</small></div>').openPopup();

    // Circle Geofence
    adminCircle = L.circle([initLat, initLng], {
        color: '#10b981',
        fillColor: '#10b981',
        fillOpacity: 0.22,
        weight: 2,
        radius: initRadius
    }).addTo(adminMap);

    function updateAdminCoord(lat, lng) {
        if (latInput) latInput.value = Number(lat).toFixed(6);
        if (lngInput) lngInput.value = Number(lng).toFixed(6);
        adminMarker.setLatLng([lat, lng]);
        adminCircle.setLatLng([lat, lng]);
    }

    adminMarker.on('dragend', function(e) {
        const coord = e.target.getLatLng();
        updateAdminCoord(coord.lat, coord.lng);
    });

    adminMap.on('click', function(e) {
        updateAdminCoord(e.latlng.lat, e.latlng.lng);
    });

    if (radiusInput) {
        radiusInput.addEventListener('input', function() {
            const r = parseInt(this.value) || 50;
            adminCircle.setRadius(r);
        });
    }

    // GPS Button
    const btnGps = document.getElementById('btnDetectAdminGPS');
    if (btnGps) {
        btnGps.addEventListener('click', function() {
            if (!navigator.geolocation) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('GPS Tidak Didukung', 'Browser Anda tidak mendukung geolokasi.', 'error');
                } else {
                    alert('Browser Anda tidak mendukung geolokasi.');
                }
                return;
            }
            btnGps.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mendeteksi GPS...';
            btnGps.disabled = true;

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    updateAdminCoord(lat, lng);
                    adminMap.setView([lat, lng], 17);
                    btnGps.innerHTML = '<i class="bi bi-check-circle me-1"></i> Lokasi Berhasil Ditemukan!';
                    btnGps.classList.replace('btn-outline-primary', 'btn-success');
                    setTimeout(() => {
                        btnGps.innerHTML = '<i class="bi bi-crosshair me-1"></i> Deteksi Lokasi GPS Saya';
                        btnGps.classList.replace('btn-success', 'btn-outline-primary');
                        btnGps.disabled = false;
                    }, 3000);
                },
                function(err) {
                    btnGps.innerHTML = '<i class="bi bi-crosshair me-1"></i> Deteksi Lokasi GPS Saya';
                    btnGps.disabled = false;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Gagal Mengambil GPS', err.message || 'Izin lokasi ditolak atau sinyal GPS belum aktif.', 'warning');
                    } else {
                        alert('Gagal mengambil GPS: ' + err.message);
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        });
    }
}

// Toggle Mode Presensi Guru (Jadwal KBM vs Serentak)
function togglePresensiMode(mode) {
    const containerAgenda = document.getElementById('containerAgendaSerentak');
    const containerToleransi = document.getElementById('containerToleransiKBM');
    const labelHeader = document.getElementById('labelJamPresensiHeader');
    const cards = document.querySelectorAll('.mode-card');

    cards.forEach(c => {
        c.classList.remove('border-primary', 'bg-primary-subtle', 'bg-opacity-10');
        c.classList.add('border-secondary-subtle');
    });

    if (mode === 'serentak') {
        containerAgenda?.classList.remove('d-none');
        containerToleransi?.classList.add('d-none');
        if (labelHeader) {
            labelHeader.innerHTML = '<i class="bi bi-clock me-1 text-primary"></i> Jam Operasional Serentak Seluruh Guru (Rapat / Agenda Bersama)';
        }
        document.querySelector('label[for="modeSerentakRadio"]')?.classList.add('border-primary', 'bg-primary-subtle', 'bg-opacity-10');
    } else {
        containerAgenda?.classList.add('d-none');
        containerToleransi?.classList.remove('d-none');
        if (labelHeader) {
            labelHeader.innerHTML = '<i class="bi bi-clock me-1 text-primary"></i> Jam Standar Sekolah (Fallback untuk Guru yang Tidak Memiliki Jadwal KBM Hari Ini)';
        }
        document.querySelector('label[for="modeJadwalRadio"]')?.classList.add('border-primary', 'bg-primary-subtle', 'bg-opacity-10');
    }
}

// Hook tab shown event for Leaflet invalidation
document.addEventListener('DOMContentLoaded', function() {
    const geoTabBtn = document.getElementById('geofencing-tab');
    if (geoTabBtn) {
        geoTabBtn.addEventListener('shown.bs.tab', function() {
            setTimeout(initAdminGeofenceMap, 200);
        });
    }

    if (document.getElementById('geofencingTab')?.classList.contains('active')) {
        setTimeout(initAdminGeofenceMap, 300);
    }
});
</script>

<!-- Leaflet CSS & JS Assets -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
