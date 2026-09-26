<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <!-- Hero Header -->
    <div class="card-custom p-4 mb-4 border-start border-5 border-primary shadow-sm">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary-subtle text-primary rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-journal-check fs-2"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h4 class="fw-bold mb-0 text-dark">Panduan Pengguna & Manual Operasional Sistem</h4>
                        <span class="badge bg-primary px-2.5 py-1">Hak Akses: Administrator</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 small">Edisi Lengkap Terintegrasi 2026</span>
                    </div>
                    <p class="text-muted small mb-0">Petunjuk teknis alur operasional, manajemen Master Data, Kontak WhatsApp Orang Tua, WhatsApp Gateway (Fonnte API), Geofencing GPS Presensi, CBT Ujian, E-Rapor, Keuangan SPP, hingga Keamanan Sistem.</p>
                </div>
            </div>
            <div class="col-12 col-md-5 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-primary"></i></span>
                    <input type="text" id="searchPanduan" class="form-control border-start-0 ps-0" placeholder="Ketik kata kunci (misal: wa ortu, fonnte, gps, cbt, excel)..." onkeyup="filterPanduan()">
                    <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" onclick="resetSearch()" title="Reset Pencarian"><i class="bi bi-x-circle"></i></button>
                </div>
            </div>
        </div>

        <!-- Action Bar: Filter Kategori & Kontrol Cepat -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top">
            <div class="d-flex gap-1.5 overflow-x-auto no-scrollbar py-1" id="categoryFilterContainer">
                <button class="btn btn-sm btn-primary active cat-btn text-nowrap flex-shrink-0" onclick="filterByCategory('all', this)"><i class="bi bi-collection me-1"></i>Semua Topik</button>
                <button class="btn btn-sm btn-outline-primary cat-btn text-nowrap flex-shrink-0" onclick="filterByCategory('master', this)"><i class="bi bi-people-fill me-1"></i>Master Data & Ortu WA</button>
                <button class="btn btn-sm btn-outline-primary cat-btn text-nowrap flex-shrink-0" onclick="filterByCategory('lms', this)"><i class="bi bi-laptop me-1"></i>LMS & CBT Ujian</button>
                <button class="btn btn-sm btn-outline-primary cat-btn text-nowrap flex-shrink-0" onclick="filterByCategory('presensi', this)"><i class="bi bi-geo-alt-fill me-1"></i>Presensi & E-Rapor</button>
                <button class="btn btn-sm btn-outline-success cat-btn text-nowrap flex-shrink-0" onclick="filterByCategory('whatsapp', this)"><i class="bi bi-whatsapp me-1"></i>WhatsApp Gateway</button>
                <button class="btn btn-sm btn-outline-primary cat-btn text-nowrap flex-shrink-0" onclick="filterByCategory('pengaturan', this)"><i class="bi bi-shield-lock me-1"></i>Sistem & Keamanan</button>
                <button class="btn btn-sm btn-outline-danger cat-btn text-nowrap flex-shrink-0" onclick="filterByCategory('faq', this)"><i class="bi bi-question-circle me-1"></i>FAQ & Kendala</button>
            </div>
            <div class="d-flex gap-1.5 ms-auto flex-shrink-0 action-btn-group">
                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="toggleAllAccordions(true)" title="Buka Semua Modul">
                    <i class="bi bi-arrows-expand me-1"></i><span class="d-none d-sm-inline">Buka Semua</span>
                </button>
                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="toggleAllAccordions(false)" title="Tutup Semua Modul">
                    <i class="bi bi-arrows-collapse me-1"></i><span class="d-none d-sm-inline">Tutup Semua</span>
                </button>
                <button class="btn btn-sm btn-outline-dark" type="button" onclick="window.print()" title="Cetak Manual">
                    <i class="bi bi-printer-fill me-1"></i><span class="d-none d-sm-inline">Cetak</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Alur Kerja Utama Sistem (Diagram Step-by-Step 5 Tahap) -->
    <div class="card-custom p-3 p-sm-4 mb-3 mb-md-4 shadow-sm roadmap-container">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-diagram-3-fill me-2"></i>Roadmap Operasional Pengelolaan Portal E-Learning Terpadu</h6>
            <span class="text-muted small d-none d-md-inline"><i class="bi bi-info-circle me-1"></i>5 Tahapan Terstruktur Menjalankan Portal</span>
        </div>
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-primary">
                    <span class="badge bg-primary mb-2.5 px-2.5 py-1">Tahap 1</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-gear-wide-connected text-primary me-1.5"></i>Setup Sistem</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Konfigurasi Profil Sekolah, Logo Resmi, Token WhatsApp Gateway Fonnte, dan Geofencing GPS Presensi.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-success">
                    <span class="badge bg-success mb-2.5 px-2.5 py-1">Tahap 2</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-database-fill-gear text-success me-1.5"></i>Master Data & Ortu</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Atur Tahun Ajaran Aktif, CP-TP, Rombel, Wali Kelas, serta Impor Guru & Siswa lengkap dengan No. HP Orang Tua.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-warning">
                    <span class="badge bg-warning text-dark mb-2.5 px-2.5 py-1">Tahap 3</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-laptop text-warning me-1.5"></i>KBM & CBT Ujian</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Kelola Kelas Virtual, Modul PDF/Video, Tugas & Rubrik, CBT Anti-Curang, Game Edukasi, dan Live Meeting.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-info">
                    <span class="badge bg-info text-dark mb-2.5 px-2.5 py-1">Tahap 4</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-qr-code-scan text-info me-1.5"></i>Presensi & SPP</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Presensi Selfie Guru GPS, Scan QR Siswa Gerbang, Rekap Absensi 1-31 Hari, dan Portal Keuangan SPP.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-danger">
                    <span class="badge bg-danger mb-2.5 px-2.5 py-1">Tahap 5</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-shield-check text-danger me-1.5"></i>WA & Keamanan</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Kirim Broadcast Notifikasi WA ke Ortu, Analitik Laporan PDF, Audit Log Jejak User, dan Backup SQL 1-Klik.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modul-by-Modul Comprehensive Guides Accordion -->
    <div class="accordion" id="accordionPanduan">

        <!-- =========================================================================
             MODUL 1: DASHBOARD EKSEKUTIF & RINGKASAN SISTEM
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card" data-category="pengaturan dashboard">
            <h2 class="accordion-header">
                <button class="accordion-button fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul1">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-primary-subtle text-primary">
                            <i class="bi bi-grid-1x2-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-primary text-white px-2 py-0.5 rounded-pill small">Modul 1</span>
                                <span class="fw-bold text-dark modul-title-text">Dashboard Eksekutif & Ringkasan Sistem</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">4 Metrik KPI Utama, Tren KBM 7 Hari & Jadwal Sholat Terintegrasi</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">admin/dashboard</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseModul1" class="accordion-collapse collapse show" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="bi bi-link-45deg me-1"></i>Navigasi: Dashboard & Panduan &gt; Ringkasan Sistem</span>
                        <span class="badge bg-light text-muted border px-2 py-1">URL: index.php?url=admin/dashboard</span>
                    </div>

                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle text-primary me-1"></i>Fungsi & Peran Utama</h6>
                    <p class="small text-muted mb-3">Dashboard merupakan ruang kendali utama bagi Administrator untuk memonitor indikator kinerja sekolah, tren aktivitas pembelajaran digital, status jadwal sholat harian, dan peringatan aktivitas pengguna secara real-time.</p>

                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-primary small mb-1"><i class="bi bi-speedometer2 me-1"></i>4 Kartu Metrik Utama</h6>
                                <p class="small text-muted mb-0">Total Guru Aktif, Siswa Terdaftar, Rombel Kelas, dan Mata Pelajaran yang terhubung langsung dengan database realtime.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-success small mb-1"><i class="bi bi-graph-up me-1"></i>Grafik Aktivitas 7 Hari</h6>
                                <p class="small text-muted mb-0">Visualisasi tren siswa yang aktif mengakses materi, mengerjakan kuis, atau submit tugas dari hari Senin hingga Minggu.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-warning small mb-1"><i class="bi bi-moon-stars-fill me-1"></i>Jadwal Sholat Terintegrasi</h6>
                                <p class="small text-muted mb-0">Informasi waktu Subuh, Dzuhur, Ashar, Maghrib, dan Isya otomatis berdasarkan koordinat lokasi sekolah untuk pembinaan karakter religi.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-danger small mb-1"><i class="bi bi-clock-history me-1"></i>Log Aktivitas Terkini</h6>
                                <p class="small text-muted mb-0">Pantauan 10 riwayat aktivitas terbaru: pengguna yang baru login, mengunggah materi, mengirim tugas, atau submit ujian CBT.</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 mt-3 mb-0 small">
                        <i class="bi bi-lightbulb-fill text-warning me-1"></i><strong>Tips Admin:</strong> Periksa dashboard setiap awal jam kerja untuk melihat ringkasan guru yang telah hadir dan memantau apakah ada lonjakan akses server pada masa ujian CBT.
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 2: MASTER DATA PENGGUNA & DATA KONTAK ORANG TUA
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card" data-category="master whatsapp">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul2">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-success-subtle text-success">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-success text-white px-2 py-0.5 rounded-pill small">Modul 2</span>
                                <span class="fw-bold text-dark modul-title-text">Master Data Pengguna, Kontak Ortu WA & Cetak Kartu</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Data Guru & Siswa, Nomor HP Ortu, Import Excel & Cetak QR Card</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">admin/siswa</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseModul2" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-4">
                        <!-- Data Siswa & No Ortu -->
                        <div class="col-12 col-lg-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold text-success mb-0"><i class="bi bi-person-lines-fill me-1"></i>1. Data Siswa & Kontak Orang Tua (`admin/siswa`)</h6>
                                    <span class="badge bg-success-subtle text-success border border-success">Penting</span>
                                </div>
                                <p class="small text-muted mb-2">Pengelolaan data induk siswa sekolah dan informasi wali murid untuk sinkronisasi pengiriman notifikasi WhatsApp otomatis.</p>
                                
                                <ul class="small text-muted ps-3 mb-3">
                                    <li class="mb-1.5"><strong>Atribut Data Lengkap:</strong> NIS, NISN, Nama Lengkap, Rombel Kelas, Jurusan, Jenis Kelamin, No. HP Siswa, dan <strong>No. HP Orang Tua/Wali (`no_ortu`)</strong>.</li>
                                    <li class="mb-1.5"><strong>Tombol Direct WhatsApp Ortu:</strong> Di setiap baris data siswa, terdapat tombol hijau berikon WhatsApp. Klik tombol ini untuk langsung membuka WhatsApp Web/Aplikasi dan mengirim pesan langsung ke orang tua siswa tanpa perlu simpan nomor di kontak HP.</li>
                                    <li class="mb-1.5"><strong>Cetak Kartu Pelajar Siswa:</strong> Klik tombol <i>Cetak Kartu</i> untuk menghasilkan Kartu Pelajar ber-Kop resmi dengan <strong>QR Code Unik</strong> yang siap dicetak dan dilaminasi untuk pemindaian presensi di gerbang sekolah.</li>
                                    <li class="mb-1.5"><strong>Filter Presisi Rombel:</strong> Terdapat dropdown filter kelas dan jurusan untuk memudahkan pencarian siswa per rombel secara akurat.</li>
                                </ul>

                                <div class="bg-white p-2.5 rounded-3 border small">
                                    <strong class="text-dark d-block mb-1"><i class="bi bi-file-earmark-spreadsheet-fill text-success me-1"></i>Format Import Excel Siswa (Termasuk No. Ortu):</strong>
                                    <p class="text-muted mb-1" style="font-size:0.75rem;">Saat mengunduh template CSV/Excel, pastikan format nomor telepon orang tua diawali angka <code>08...</code> atau <code>628...</code> tanpa spasi/tanda hubung:</p>
                                    <code class="d-block p-1 bg-light text-dark rounded border" style="font-size:0.72rem;">nis;nisn;nama;email;jenis_kelamin;kelas_id;jurusan_id;no_telp;no_ortu;alamat</code>
                                </div>
                            </div>
                        </div>

                        <!-- Data Guru & Staff -->
                        <div class="col-12 col-lg-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold text-success mb-0"><i class="bi bi-person-badge-fill me-1"></i>2. Data Guru & Tenaga Pendidik (`admin/guru`)</h6>
                                    <span class="badge bg-primary-subtle text-primary border border-primary">Akademik</span>
                                </div>
                                <p class="small text-muted mb-2">Pusat data dewan guru, riwayat pendidikan, penugasan mengajar, dan aktivasi akun login sistem.</p>

                                <ul class="small text-muted ps-3 mb-3">
                                    <li class="mb-1.5"><strong>CRUD & Detail Portofolio:</strong> Melihat riwayat kelas yang diampu guru, mata pelajaran, serta data kontak aktif (No. Telepon & Email).</li>
                                    <li class="mb-1.5"><strong>Import Excel Cepat & Rapi:</strong> Fitur impor massal menggunakan template `.csv` ber-BOM UTF-8 dengan pemisah titik koma (<code>;</code>) yang dijamin terbaca rapi di Microsoft Excel versi lama maupun baru.</li>
                                    <li class="mb-1.5"><strong>Akun Login Otomatis:</strong> Saat guru baru ditambahkan, sistem otomatis membuatkan akun pengguna dengan role <code>guru</code> sehingga guru dapat langsung login ke portal.</li>
                                    <li class="mb-1.5"><strong>Penugasan Wali Kelas:</strong> Guru yang berstatus aktif dapat dipilih sebagai Wali Kelas di modul Kelas Virtual.</li>
                                </ul>

                                <div class="alert alert-warning border-0 rounded-3 p-2 small mb-0">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Perhatian:</strong> Menghapus akun guru yang sudah memiliki riwayat nilai atau materi akan diproteksi sistem agar histori pembelajaran tetap aman.
                                </div>
                            </div>
                        </div>

                        <!-- Hak Akses & Users -->
                        <div class="col-12">
                            <div class="p-3 border rounded-4 bg-light">
                                <h6 class="fw-bold text-success mb-2"><i class="bi bi-person-gear me-1"></i>3. Hak Akses & Manajemen Pengguna (`admin/users`)</h6>
                                <p class="small text-muted mb-2">Manajemen keamanan akun, kontrol hak akses berbasis peran (Role-Based Access Control / RBAC), serta pemulihan kata sandi:</p>
                                <div class="row g-2">
                                    <div class="col-12 col-md-3">
                                        <div class="bg-white p-2.5 rounded-3 border">
                                            <strong class="text-primary small d-block mb-1"><i class="bi bi-shield-fill me-1"></i>Administrator</strong>
                                            <span class="text-muted" style="font-size:0.75rem;">Akses tak terbatas ke seluruh konfigurasi, master data, transaksi keuangan, log, dan backup server.</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="bg-white p-2.5 rounded-3 border">
                                            <strong class="text-success small d-block mb-1"><i class="bi bi-person-workspace me-1"></i>Guru</strong>
                                            <span class="text-muted" style="font-size:0.75rem;">Akses KBM kelas binaan, pembuatan modul, tugas, kuis CBT, presensi selfie GPS, dan leger E-Rapor.</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="bg-white p-2.5 rounded-3 border">
                                            <strong class="text-warning text-dark small d-block mb-1"><i class="bi bi-mortarboard-fill me-1"></i>Siswa</strong>
                                            <span class="text-muted" style="font-size:0.75rem;">Melihat materi, submit tugas, mengerjakan ujian CBT anti-curang, game edukasi, dan riwayat presensi.</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="bg-white p-2.5 rounded-3 border">
                                            <strong class="text-info text-dark small d-block mb-1"><i class="bi bi-briefcase-fill me-1"></i>Kepala Sekolah</strong>
                                            <span class="text-muted" style="font-size:0.75rem;">Executive monitoring: supervisi KBM guru, rekap presensi seluruh kelas, analitik LMS, dan approval resmi.</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 text-muted small">
                                    <i class="bi bi-key-fill text-warning me-1"></i><strong>Reset Password:</strong> Administrator dapat mereset password pengguna yang lupa kata sandi dengan menekan tombol <i>Edit User</i> lalu mengisi password baru.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 3: MANAJEMEN AKADEMIK, KURIKULUM MERDEKA & EKSKUL
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card" data-category="master">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul3">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-warning-subtle text-warning text-dark">
                            <i class="bi bi-journal-bookmark-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-warning text-dark px-2 py-0.5 rounded-pill small">Modul 3</span>
                                <span class="fw-bold text-dark modul-title-text">Manajemen Akademik, Kurikulum Merdeka & Ekskul</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Tahun Ajaran, Rombel Kelas, Penugasan Wali, Mapel CP-TP & Ekskul</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">admin/akademik</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseModul3" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark small"><i class="bi bi-calendar-range-fill text-primary me-1"></i>Tahun Ajaran & Semester (`admin/tahunAjaran`)</h6>
                                <p class="small text-muted mb-0">Menentukan tahun ajaran berjalan (misal: <code>2024/2025</code>) dan status semester aktif (Ganjil/Genap). Penggantian semester tidak akan menghapus data historis siswa pada semester sebelumnya.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark small"><i class="bi bi-diagram-3-fill text-success me-1"></i>Kurikulum & CP-TP (`admin/kurikulum`)</h6>
                                <p class="small text-muted mb-0">Penyusunan Capaian Pembelajaran (CP) dan Tujuan Pembelajaran (TP) standar Kurikulum Merdeka per fase (Fase E untuk Kelas X, Fase F untuk Kelas XI & XII SMK).</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark small"><i class="bi bi-key-fill text-warning me-1"></i>Enrollment Key Mapel (`admin/enrollmentKey`)</h6>
                                <p class="small text-muted mb-0">Pengaturan token/kunci akses pendaftaran mandiri mapel bagi siswa. Berguna agar siswa hanya dapat masuk ke mata pelajaran yang diizinkan oleh guru pengampu.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark small"><i class="bi bi-activity text-danger me-1"></i>Data Ekstrakurikuler (`admin/ekstrakurikuler`)</h6>
                                <p class="small text-muted mb-0">Manajemen kegiatan ekstrakurikuler sekolah (Pramuka, Paskibra, PMR, Futsal, IT Club), penunjukan guru pembina, dan pencatatan anggota siswa.</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 border rounded-3 bg-light">
                                <h6 class="fw-bold text-dark small mb-1"><i class="bi bi-bounding-box text-primary me-1"></i>Kelas, Jurusan & Penetapan Wali Kelas (`admin/akademik`)</h6>
                                <p class="small text-muted mb-2">Administrator dapat membuat struktur kelas berjenjang (X RPL 1, XI TKJ 2, dll). Pada setiap rombel kelas, tentukan Guru yang bertindak sebagai <strong>Wali Kelas</strong> resmi. Wali kelas akan memiliki wewenang khusus memantau rekap absensi harian dan status kemajuan belajar seluruh siswa di rombel binaannya.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 4: LEARNING MANAGEMENT SYSTEM (LMS) & EVALUASI CBT
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card" data-category="lms">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul4">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-danger-subtle text-danger">
                            <i class="bi bi-laptop fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-danger text-white px-2 py-0.5 rounded-pill small">Modul 4</span>
                                <span class="fw-bold text-dark modul-title-text">LMS, Evaluasi CBT & Bank Soal</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Materi PDF/Video, Tugas Berbobot, Ujian Anti-Curang & Sertifikat</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">admin/quiz</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseModul4" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-primary"><i class="bi bi-book-half me-1"></i>Modul & Materi Pembelajaran (`admin/materi`)</h6>
                                <p class="small text-muted mb-2">Mengelola unggahan bahan ajar digital guru. Mendukung berkas PDF interaktif, video MP4 lokal, dan link video YouTube dengan player tersemat rapi.</p>
                                <span class="badge bg-light text-dark border small"><i class="bi bi-check-circle me-1"></i>Bisa disaring per kelas & mapel</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-success"><i class="bi bi-card-checklist me-1"></i>Tugas & Evaluasi Rubrik (`admin/tugas`)</h6>
                                <p class="small text-muted mb-2">Pengaturan batas waktu pengumpulan (*deadline*), rubrik penilaian, peninjauan status siswa (Sudah/Belum Mengumpulkan), serta pengunduhan berkas jawaban tugas siswa.</p>
                                <span class="badge bg-light text-dark border small"><i class="bi bi-download me-1"></i>Download batch tugas</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-danger"><i class="bi bi-patch-question-fill me-1"></i>Quiz & CBT Ujian (`admin/quiz`)</h6>
                                <p class="small text-muted mb-2">Penyelenggaraan ujian online berbasis komputer dengan fitur canggih:</p>
                                <ul class="small text-muted ps-3 mb-0" style="font-size:0.75rem;">
                                    <li>Pengacakan urutan soal & opsi jawaban.</li>
                                    <li>Timer hitung mundur & auto-submit saat waktu habis.</li>
                                    <li><strong>Deteksi Anti-Curang:</strong> Peringatan otomatis jika siswa berpindah tab/membuka aplikasi lain.</li>
                                    <li>Koreksi instan pilihan ganda & rekap nilai otomatis.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-info"><i class="bi bi-database me-1"></i>Bank Soal Terpusat (`admin/bankSoal`)</h6>
                                <p class="small text-muted mb-0">Gudang master butir soal terstandarisasi per mata pelajaran. Guru dan Admin dapat mengambil soal dari bank soal tanpa perlu mengetik ulang setiap kali membuat paket kuis baru.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-warning text-dark"><i class="bi bi-controller me-1"></i>Game Edukasi Interaktif (`game`)</h6>
                                <p class="small text-muted mb-0">Gamifikasi pembelajaran interaktif untuk meningkatkan minat belajar siswa SMK melalui tebak istilah kejuruan, kuis interaktif berwaktu, dan sistem perolehan skor prestasi.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-award-fill text-warning me-1"></i>Template Sertifikat Digital (`admin/sertifikat`)</h6>
                                <p class="small text-muted mb-0">Desain sertifikat kelulusan / UKK / prestasi dengan Kop Resmi, Logo Sekolah, dan <strong>QR Code Verifikasi Keaslian</strong>. Mendukung penerbitan massal dan cetak instan ke PDF.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 5: SISTEM PRESENSI TERPADU (GEOFENCING GPS & SCAN QR)
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card" data-category="presensi">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul5">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-info-subtle text-info">
                            <i class="bi bi-qr-code-scan fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-info text-dark px-2 py-0.5 rounded-pill small">Modul 5</span>
                                <span class="fw-bold text-dark modul-title-text">Presensi Terpadu, GPS & Scan QR Gerbang</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Selfie GPS Guru, Scanner Kartu Siswa & Rekap Matrix 1-31 Hari</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">admin/scanQr</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseModul5" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <div class="alert alert-success border-0 rounded-3 mb-3 small">
                        <i class="bi bi-check-circle-fill me-1"></i><strong>Sistem Presensi 3-in-1:</strong> Portal E-Learning mendukung Presensi Selfie GPS untuk Guru, Pemindaian QR Code Kartu Pelajar Siswa di Gerbang, dan Rekap Absensi Bulanan Matrix 1-31 Hari.
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-danger"><i class="bi bi-camera-fill me-1"></i>1. Presensi Selfie Guru GPS (`guru/presensiGuru`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Guru melakukan absensi masuk dan pulang melalui smartphone/laptop dengan mengaktifkan kamera foto selfie dan izin lokasi GPS.</li>
                                    <li class="mb-1.5"><strong>Proteksi Geofencing:</strong> Sistem mencocokkan koordinat GPS guru dengan titik tengah sekolah yang diatur di menu Pengaturan. Jika guru berada di luar radius toleransi (misal >100m), sistem otomatis menolak absensi.</li>
                                    <li class="mb-1.5">Admin dapat melihat foto selfie dan rincian jam presensi pada daftar riwayat harian.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-success"><i class="bi bi-qr-code-scan me-1"></i>2. Scan QR Code Hadir Siswa (`admin/scanQr`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Digunakan oleh Petugas Piket / Satpam / Admin di gerbang masuk sekolah menggunakan kamera webcam laptop atau kamera HP.</li>
                                    <li class="mb-1.5">Siswa cukup menunjukkan <strong>Kartu Pelajar ber-QR Code</strong> ke hadapan kamera. Begitu terdeteksi, nama siswa dan status "Hadir" seketika tercatat di database dalam hitungan detik.</li>
                                    <li class="mb-1.5">Dilengkapi efek audio "Beep" dan notifikasi visual pop-up konfirmasi kehadiran.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-primary"><i class="bi bi-calendar-check-fill me-1"></i>3. Monitoring Presensi Harian (`admin/absensi`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Menampilkan status kehadiran seluruh guru dan siswa hari ini: <code>Hadir</code>, <code>Izin</code>, <code>Sakit</code>, atau <code>Alpa</code>.</li>
                                    <li class="mb-1.5">Admin dapat meninjau berkas surat keterangan dokter atau surat izin orang tua yang diunggah siswa.</li>
                                    <li class="mb-1.5">Tombol pengubahan status manual jika ada siswa yang lupa membawa kartu atau izin khusus.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-info"><i class="bi bi-file-earmark-spreadsheet-fill me-1"></i>4. Rekap Absensi Bulanan Matrix (`admin/recapBulanan`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Tampilan matriks tanggal 1 s/d 31 dalam satu bulan penuh untuk setiap rombel kelas.</li>
                                    <li class="mb-1.5">Menghitung total Hadir (H), Sakit (S), Izin (I), Alpa (A), serta kalkulasi persentase (%) tingkat kehadiran setiap siswa secara otomatis.</li>
                                    <li class="mb-1.5">Dilengkapi tombol cetak dan ekspor format cetak untuk diserahkan ke Guru BK dan bagian Kesiswaan/Kurikulum.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 6: PENILAIAN E-RAPOR & KEUANGAN SPP
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card" data-category="presensi">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul6">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-success-subtle text-success">
                            <i class="bi bi-wallet2 fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-success text-white px-2 py-0.5 rounded-pill small">Modul 6</span>
                                <span class="fw-bold text-dark modul-title-text">Penilaian E-Rapor & Keuangan SPP</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Rekapitulasi Nilai Akhir & Pos Iuran Kuitansi Resmi</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">admin/pembayaran</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseModul6" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-4">
                        <div class="col-12 col-lg-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-pencil-fill me-1"></i>Rekap Nilai & E-Rapor (`admin/inputNilai`)</h6>
                                <p class="small text-muted mb-2">Penyatuan rekapitulasi nilai formatif tugas harian, nilai sumatif kuis/CBT, nilai PTS (Penilaian Tengah Semester), dan PAS (Penilaian Akhir Semester):</p>
                                <ul class="small text-muted ps-3 mb-2">
                                    <li class="mb-1">Tabel input nilai terstruktur per rombel dan per mata pelajaran.</li>
                                    <li class="mb-1">Perhitungan rata-rata nilai akhir otomatis sesuai bobot KKM sekolah.</li>
                                    <li class="mb-1">Pencetakan Leger Nilai Kelas dan lembaran transkrip E-Rapor resmi siswa ber-Kop Sekolah.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-success mb-2"><i class="bi bi-cash-coin me-1"></i>Portal Pembayaran & Rekap SPP (`admin/pembayaran`)</h6>
                                <p class="small text-muted mb-2">Pengelolaan administrasi keuangan sekolah secara transparan dan akuntabel:</p>
                                <ul class="small text-muted ps-3 mb-2">
                                    <li class="mb-1"><strong>Pos Tagihan:</strong> Pengaturan nominal SPP bulanan, uang gedung, atau biaya ujian UKK kejuruan.</li>
                                    <li class="mb-1"><strong>Pencatatan Transaksi:</strong> Verifikasi pembayaran tunai via tata usaha atau transfer rekening bank sekolah.</li>
                                    <li class="mb-1"><strong>Status Tagihan:</strong> Label status <code>Lunas</code>, <code>Cicilan</code>, atau <code>Belum Bayar</code>.</li>
                                    <li class="mb-1"><strong>Cetak Kuitansi Resmi:</strong> Menghasilkan bukti kuitansi digital sah berstempel sekolah yang dapat diunduh/dicetak untuk bukti kepada orang tua murid.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 7: KONTEN PUBLIK, KOMUNIKASI & KALENDER AKADEMIK
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card" data-category="pengaturan">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul7">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-primary-subtle text-primary">
                            <i class="bi bi-broadcast fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-primary text-white px-2 py-0.5 rounded-pill small">Modul 7</span>
                                <span class="fw-bold text-dark modul-title-text">Konten Publik, Pengumuman & Kalender</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Landing Page Sekolah, Siaran Pengumuman, Live Class & Kalender</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">admin/landingPage</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseModul7" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-window-stack text-warning me-1"></i>Kelola Landing Page & Visi Misi (`admin/landingPage`)</h6>
                                <p class="small text-muted mb-0">Mengubah isi tampilan website publik sekolah tanpa perlu koding. Meliputi: Judul Banner Utama, Sambutan Kepala Sekolah, Visi & Misi, Keunggulan Jurusan Kejuruan, Testimoni Alumni, Galeri Foto, dan Informasi PPDB.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-megaphone-fill text-danger me-1"></i>Kelola Pengumuman Sekolah (`admin/pengumuman`)</h6>
                                <p class="small text-muted mb-0">Publikasi surat edaran atau berita penting dengan target audiens yang dapat diatur (Apakah ditujukan untuk <code>Semua Pengguna</code>, khusus <code>Dewan Guru</code>, atau khusus <code>Siswa</code>).</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-camera-reels-fill text-danger me-1"></i>Live Virtual Meeting (`admin/liveClass`)</h6>
                                <p class="small text-muted mb-0">Ruang tatap muka daring terintegrasi untuk rapat dewan guru atau kelas tatap muka virtual tanpa perlu install aplikasi pihak ketiga.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-chat-dots-fill text-primary me-1"></i>Forum & Chat Realtime (`forum` & `chat`)</h6>
                                <p class="small text-muted mb-0">Forum tanya jawab materi belajar dan fitur pesan pribadi instan yang terintegrasi dengan badge lonceng notifikasi header.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-calendar3 text-success me-1"></i>Kalender Akademik (`admin/kalender`)</h6>
                                <p class="small text-muted mb-0">Kalender agenda tahunan interaktif: jadwal Ujian CBT (PTS/PAS), Libur Nasional, Masa Orientasi Siswa (MPLS), dan Event Sekolah.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 8: INTEGRASI WHATSAPP GATEWAY (FONNTE API) & NOTIFIKASI ORTU
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card" data-category="whatsapp">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul8">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-success-subtle text-success">
                            <i class="bi bi-whatsapp fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-success text-white px-2 py-0.5 rounded-pill small">Modul 8</span>
                                <span class="fw-bold text-dark modul-title-text">WhatsApp Gateway & Notifikasi Otomatis</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Integrasi Token Fonnte API, Status Kuota & Auto-Broadcast Ortu</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">admin/pengaturan?tab=whatsapp</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseModul8" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-success text-white px-2.5 py-1"><i class="bi bi-shield-check me-1"></i>Fitur Unggulan Terbaru</span>
                        <span class="badge bg-light text-muted border px-2 py-1">Menu: Pengaturan &gt; Tab WhatsApp Gateway</span>
                    </div>

                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle text-success me-1"></i>Gambaran Umum WhatsApp Gateway</h6>
                    <p class="small text-muted mb-3">Portal E-Learning SMK telah terintegrasi dengan <strong>Fonnte WhatsApp API Gateway</strong>. Fitur ini memungkinkan sistem mengirimkan pesan pemberitahuan otomatis ke nomor WhatsApp orang tua/wali murid tanpa biaya per-pesan yang mahal seperti SMS konvensional.</p>

                    <!-- Step-by-Step Setup Fonnte -->
                    <div class="p-3.5 bg-light rounded-4 border mb-4">
                        <h6 class="fw-bold text-success mb-3"><i class="bi bi-123 me-1"></i>Panduan Langkah-demi-Langkah Konfigurasi WhatsApp Fonnte:</h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="bg-white p-3 rounded-3 border h-100">
                                    <span class="badge bg-success mb-2">Langkah 1</span>
                                    <h6 class="fw-bold text-dark small mb-1">Daftar & Hubungkan Device</h6>
                                    <p class="text-muted small mb-0" style="font-size:0.75rem;">Buka website resmi <a href="https://fonnte.com" target="_blank" class="fw-bold text-decoration-none">fonnte.com</a>, buat akun gratis/berlangganan, lalu tautkan nomor WhatsApp resmi sekolah dengan melakukan Scan QR Code di dashboard Fonnte.</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="bg-white p-3 rounded-3 border h-100">
                                    <span class="badge bg-success mb-2">Langkah 2</span>
                                    <h6 class="fw-bold text-dark small mb-1">Salin Token Fonnte</h6>
                                    <p class="text-muted small mb-0" style="font-size:0.75rem;">Pada menu <i>Device Settings</i> di dashboard Fonnte, salin string token perangkat Anda (contoh token: <code>abcde12345xyz...</code>).</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="bg-white p-3 rounded-3 border h-100">
                                    <span class="badge bg-success mb-2">Langkah 3</span>
                                    <h6 class="fw-bold text-dark small mb-1">Simpan & Uji Koneksi</h6>
                                    <p class="text-muted small mb-0" style="font-size:0.75rem;">Masuk ke portal: <i>Pengaturan &gt; Tab WhatsApp Gateway</i>, tempelkan token pada kolom <strong>Token WhatsApp (Fonnte API)</strong>, lalu klik <strong>Simpan Pengaturan</strong> dan tekan <strong>Uji Kirim Pesan & Cek Status Kuota</strong>.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Otomatisasi Notifikasi -->
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-robot text-primary me-1"></i>Otomatisasi Pesan yang Didukung:</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <strong class="text-success small d-block mb-1"><i class="bi bi-bell-fill me-1"></i>1. Notifikasi Absensi & Ketidakhadiran</strong>
                                <p class="small text-muted mb-0">Jika siswa tidak hadir saat jam masuk berakhir atau statusnya tercatat Alpa, sistem dapat mengirim notifikasi otomatis ke nomor orang tua/wali siswa sebagai wujud perhatian sekolah terhadap disiplin siswa.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <strong class="text-primary small d-block mb-1"><i class="bi bi-wallet-fill me-1"></i>2. Pengingat Tagihan SPP & Iuran Sekolah</strong>
                                <p class="small text-muted mb-0">Pesan otomatis pengingat batas waktu pembayaran iuran SPP bulanan yang dikirim secara santun langsung ke WhatsApp orang tua lengkap dengan rincian rekening sekolah.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <strong class="text-warning text-dark small d-block mb-1"><i class="bi bi-megaphone-fill me-1"></i>3. Broadcast Pengumuman Resmi</strong>
                                <p class="small text-muted mb-0">Kirim pesan siaran massal kepada seluruh wali murid (misal: pengumuman libur Idul Fitri, jadwal pembagian rapor, atau undangan rapat komite sekolah) dalam satu kali klik.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <strong class="text-dark small d-block mb-1"><i class="bi bi-chat-text-fill text-success me-1"></i>4. Tombol WhatsApp Chat di Data Siswa</strong>
                                <p class="small text-muted mb-0">Admin dan Guru dapat membuka komunikasi personal dengan orang tua siswa langsung dari tabel Data Siswa dengan menekan ikon hijau WhatsApp di kolom aksi.</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning border-0 rounded-3 mb-0 small">
                        <i class="bi bi-shield-exclamation me-1"></i><strong>Catatan Kuota Fonnte:</strong> Pastikan masa aktif atau kuota paket di akun Fonnte Anda selalu diperbarui. Jika kuota habis, notifikasi akan berstatus tertunda (*pending*) hingga kuota diisi kembali.
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 9: PENGATURAN SISTEM, GEOFENCING & PEMELIHARAAN KEAMANAN
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card" data-category="pengaturan">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul9">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-dark-subtle text-dark">
                            <i class="bi bi-shield-lock-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-dark text-white px-2 py-0.5 rounded-pill small">Modul 9</span>
                                <span class="fw-bold text-dark modul-title-text">Pengaturan Sistem, GPS & Keamanan Data</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Profil Sekolah, Titik Geofencing, Log Audit & Backup SQL 1-Klik</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">admin/pengaturan</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseModul9" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-4">
                        <!-- Profil & Logo -->
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark small mb-1"><i class="bi bi-building text-primary me-1"></i>Profil Sekolah & Logo Resmi (`admin/pengaturan`)</h6>
                                <p class="small text-muted mb-2">Mengatur Nama Resmi Sekolah, NPSN, Status Akreditasi, Alamat Lengkap, No. Telepon, Email, serta Nama dan NIP Kepala Sekolah.</p>
                                <div class="bg-white p-2 rounded-2 border small">
                                    <strong class="text-dark d-block mb-1"><i class="bi bi-image text-success me-1"></i>Upload Logo Sekolah:</strong>
                                    <span class="text-muted" style="font-size:0.75rem;">Unggah logo transparan (format PNG). Logo ini otomatis tampil pada Favicon browser, Header Surat, Kop Kartu Pelajar Siswa, Laporan PDF, dan Lembaran Sertifikat Kelulusan.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Geofencing Presensi -->
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark small mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Titik Koordinat Presensi Geofencing GPS</h6>
                                <p class="small text-muted mb-2">Pengaturan batas wilayah absensi selfie guru menggunakan titik koordinat Latitude dan Longitude:</p>
                                <div class="bg-white p-2 rounded-2 border small">
                                    <strong class="text-dark d-block mb-1"><i class="bi bi-compass text-danger me-1"></i>Cara Mencari Koordinat Sekolah:</strong>
                                    <span class="text-muted" style="font-size:0.75rem;">Buka Google Maps, klik kanan pada gerbang/gedung sekolah, lalu salin angka Latitude dan Longitude (contoh: <code>-6.985412, 107.834512</code>). Masukkan nilai tersebut pada kolom pengaturan disertai radius toleransi (disarankan <strong>100 - 250 meter</strong>).</span>
                                </div>
                            </div>
                        </div>

                        <!-- Audit Log -->
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark small mb-1"><i class="bi bi-shield-check text-info me-1"></i>Audit Log & Jejak Keamanan (`admin/logs`)</h6>
                                <p class="small text-muted mb-2">Merekam setiap aktivitas penting sistem secara real-time untuk keperluan forensik IT:</p>
                                <ul class="small text-muted ps-3 mb-0" style="font-size:0.75rem;">
                                    <li>Mencatat IP Address, User Agent peramban, dan nama akun yang melakukan aktivitas.</li>
                                    <li>Klasifikasi level ancaman: <code>INFO</code> (aktivitas normal), <code>WARNING</code> (kesalahan input/gagal login), dan <code>CRITICAL</code> (percobaan akses ilegal).</li>
                                    <li>Tersedia tombol <strong>Export CSV Per-Kolom Rapi</strong> untuk laporan audit dan tombol pembersihan log lama.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Backup Database -->
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark small mb-1"><i class="bi bi-database-fill-gear text-dark me-1"></i>Backup & Restore Database SQL (`admin/backup`)</h6>
                                <p class="small text-muted mb-2">Fasilitas perlindungan data sekolah terhadap risiko kerusakan server (*Disaster Recovery*):</p>
                                <ul class="small text-muted ps-3 mb-0" style="font-size:0.75rem;">
                                    <li><strong>1-Klik Backup:</strong> Sekali klik untuk menghasilkan file cadangan database MySQL lengkap (skema tabel + seluruh data nilai, akun, dan absensi).</li>
                                    <li>Daftar file cadangan tersimpan rapi dan dapat diunduh ke komputer lokal admin.</li>
                                    <li><strong>SOP Admin:</strong> Disarankan membuat backup secara berkala setiap akhir pekan atau sebelum melakukan ujian CBT serentak.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 10: TANYA JAWAB SERING DIAJUKAN & TROUBLESHOOTING (FAQ)
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card" data-category="faq">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul10">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-danger-subtle text-danger">
                            <i class="bi bi-question-circle-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-danger text-white px-2 py-0.5 rounded-pill small">Modul 10</span>
                                <span class="fw-bold text-dark modul-title-text">Pusat Bantuan & Solusi Kendala Admin</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Troubleshooting Akun, GPS Ditolak, Fonnte API & Kenaikan Kelas</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">admin/logs</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseModul10" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-primary small"><i class="bi bi-question-diamond-fill me-1"></i>Bagaimana jika Guru atau Siswa lupa kata sandi?</h6>
                                <p class="small text-muted mb-0">Masuk ke menu <strong>Hak Akses & User (`admin/users`)</strong>, cari username atau nama siswa/guru yang bersangkutan, klik tombol <strong>Edit</strong>, lalu ketik kata sandi baru pada kolom Password. Simpan perubahan dan sampaikan kata sandi baru kepada yang bersangkutan.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-primary small"><i class="bi bi-question-diamond-fill me-1"></i>Mengapa notifikasi WhatsApp Fonnte tidak masuk ke HP Orang Tua?</h6>
                                <p class="small text-muted mb-0">Periksa 3 hal: (1) Pastikan Token WhatsApp di menu Pengaturan sudah benar dan status device Fonnte "Connected". (2) Periksa nomor HP orang tua di menu Data Siswa diawali format yang benar (misal: <code>081234567890</code>). (3) Periksa apakah kuota pesan Fonnte Anda masih mencukupi.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-primary small"><i class="bi bi-question-diamond-fill me-1"></i>Presensi Selfie Guru ditolak dengan keterangan "Di Luar Radius"?</h6>
                                <p class="small text-muted mb-0">Hal ini terjadi jika GPS HP guru belum mengunci akurasi tinggi atau posisi guru berada lebih jauh dari radius yang ditentukan. Solusi: Buka <strong>Pengaturan &gt; Tab Geofencing</strong>, dan naikkan radius toleransi (misalnya dari 100 meter menjadi 250 meter).</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-primary small"><i class="bi bi-question-diamond-fill me-1"></i>File import Excel Guru atau Siswa gagal diunggah?</h6>
                                <p class="small text-muted mb-0">Pastikan Anda menggunakan tombol <strong>Unduh Template Excel</strong> resmi yang tersedia di halaman tersebut. Jangan mengubah susunan baris header kolom, dan simpan file dengan ekstensi <code>.csv</code> atau format Excel standar.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-primary small"><i class="bi bi-question-diamond-fill me-1"></i>Siswa terlempar atau diblokir saat ujian CBT berlangsung?</h6>
                                <p class="small text-muted mb-0">Sistem CBT dilengkapi fitur deteksi anti-curang. Jika siswa berpindah aplikasi atau membuka tab browser lain melebihi batas toleransi, ujian otomatis terkunci. Admin/Guru pengawas dapat membuka kunci melalui menu monitoring CBT ujian.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-primary small"><i class="bi bi-question-diamond-fill me-1"></i>Bagaimana langkah kenaikan kelas dan tahun ajaran baru?</h6>
                                <p class="small text-muted mb-0">Cukup masuk ke menu <strong>Tahun Ajaran (`admin/tahunAjaran`)</strong>, buat Tahun Ajaran baru (misal: 2025/2026 Ganjil) dan tandai sebagai <strong>Aktif</strong>. Kemudian pada menu Data Siswa, Anda dapat menaikkan rombel siswa secara bertahap tanpa kehilangan data nilai tahun sebelumnya.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Help & Support Footer Banner -->
    <div class="card-custom p-4 mt-4 shadow-sm border-0 bg-primary-subtle text-primary-emphasis rounded-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-headset fs-3"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">Pusat Bantuan & Tim IT Support SMK</h6>
                    <p class="small text-muted mb-0">Memerlukan bantuan teknis lanjutan terkait server database, konfigurasi domain, atau integrasi webhook Fonnte? Hubungi tim pengembang sistem melalui portal IT internal.</p>
                </div>
            </div>
            <div>
                <a href="<?= BASE_URL ?>index.php?url=admin/dashboard" class="btn btn-primary fw-bold px-3 py-2">
                    <i class="bi bi-arrow-left-circle me-1"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

</div>
</main>

<style>
/* Styling khusus panduan agar rapi dan responsif */
.card-custom {
    background: #ffffff;
    border-radius: 1rem;
    border: 1px solid rgba(0, 0, 0, 0.06);
}
.accordion-button:not(.collapsed) {
    background-color: rgba(13, 110, 253, 0.08);
    color: #0d6efd;
    box-shadow: none;
}
.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(0, 0, 0, 0.125);
}
.cat-btn {
    transition: all 0.2s ease-in-out;
}
.cat-btn.active {
    box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);
}

/* Sembunyikan scrollbar bawaan pada swipe chips */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Roadmap Step Cards Styling & Padding */
.roadmap-step-card {
    padding: 1.25rem 1.15rem;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.roadmap-step-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* Styling Modul Icon Box & Title Header */
.modul-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.modul-title-text {
    font-size: 0.98rem;
    line-height: 1.3;
}
.modul-desc-text {
    font-size: 0.78rem;
    line-height: 1.3;
}
.modul-route-tag {
    font-size: 0.72rem;
}

/* Penyesuaian Responsif Khusus Mobile */
@media (max-width: 768px) {
    .page-title {
        font-size: 1.15rem;
    }
    .page-subtitle {
        font-size: 0.78rem;
    }
    .hero-icon-box {
        width: 48px !important;
        height: 48px !important;
    }
    .hero-icon-box i {
        font-size: 1.5rem !important;
    }
    .roadmap-container {
        padding: 1.25rem 1rem !important;
    }
    .roadmap-step-card {
        padding: 1.25rem 1.25rem !important;
        margin-bottom: 0.25rem;
    }
    .roadmap-step-card h6 {
        font-size: 0.95rem !important;
        margin-bottom: 0.5rem !important;
    }
    .roadmap-step-card p {
        font-size: 0.82rem !important;
        line-height: 1.5 !important;
    }
    .modul-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 9px;
    }
    .modul-icon-box i {
        font-size: 1.15rem !important;
    }
    .modul-title-text {
        font-size: 0.9rem !important;
    }
    .modul-desc-text {
        font-size: 0.74rem !important;
    }
    .accordion-button {
        font-size: 0.92rem !important;
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }
}

/* Print styling khusus agar jika di-print hasilnya rapi layaknya buku manual */
@media print {
    .app-sidebar, .app-header, .btn, #categoryFilterContainer, #searchPanduan, .input-group {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .accordion-collapse {
        display: block !important;
        height: auto !important;
    }
    .accordion-button::after {
        display: none !important;
    }
    .card-custom {
        border: 1px solid #ccc !important;
        page-break-inside: avoid;
    }
    .panduan-card {
        page-break-inside: avoid;
        margin-bottom: 1.5rem !important;
    }
}
</style>

<script>
// Filter Pencarian Teks Real-Time
function filterPanduan() {
    const query = document.getElementById('searchPanduan').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.panduan-card');

    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(query)) {
            card.style.display = '';
            const collapse = card.querySelector('.accordion-collapse');
            if (collapse && query.length >= 2) {
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapse);
                bsCollapse.show();
            }
        } else {
            card.style.display = 'none';
        }
    });
}

function resetSearch() {
    document.getElementById('searchPanduan').value = '';
    filterPanduan();
}

// Filter Berdasarkan Kategori
function filterByCategory(category, btnElement) {
    // Reset active button
    document.querySelectorAll('.cat-btn').forEach(btn => {
        btn.classList.remove('active', 'btn-primary', 'btn-success', 'btn-danger');
        btn.classList.add(btn.getAttribute('onclick').includes('whatsapp') ? 'btn-outline-success' : (btn.getAttribute('onclick').includes('faq') ? 'btn-outline-danger' : 'btn-outline-primary'));
    });

    // Set active class on clicked button
    btnElement.classList.add('active');
    if (category === 'whatsapp') {
        btnElement.classList.remove('btn-outline-success');
        btnElement.classList.add('btn-success');
    } else if (category === 'faq') {
        btnElement.classList.remove('btn-outline-danger');
        btnElement.classList.add('btn-danger');
    } else {
        btnElement.classList.remove('btn-outline-primary');
        btnElement.classList.add('btn-primary');
    }

    const cards = document.querySelectorAll('.panduan-card');
    cards.forEach(card => {
        const cardCategories = (card.getAttribute('data-category') || '').toLowerCase();
        if (category === 'all' || cardCategories.includes(category)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// Buka / Tutup Semua Accordion
function toggleAllAccordions(open) {
    const collapses = document.querySelectorAll('#accordionPanduan .accordion-collapse');
    collapses.forEach(el => {
        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el);
        if (open) {
            bsCollapse.show();
        } else {
            bsCollapse.hide();
        }
    });
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
