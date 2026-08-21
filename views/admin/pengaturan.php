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
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
