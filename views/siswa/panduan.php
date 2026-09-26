<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-2 px-md-4 py-3">
<div class="container-fluid px-1 px-md-2">

    <!-- 1. Hero Header Banner -->
    <div class="card-custom p-3 p-md-4 mb-4 border-start border-4 border-md-5 border-primary shadow-sm bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="hero-icon-box bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px;">
                    <i class="bi bi-mortarboard-fill fs-2"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1 text-dark page-title">Panduan Pengguna & Manual Belajar Siswa</h4>
                    <p class="text-muted small mb-0 page-subtitle">Petunjuk lengkap gabung kelas virtual, akses materi ajar, pengumpulan tugas, kuis CBT online, presensi QR, E-Rapor digital, dan administrasi siswa.</p>
                </div>
            </div>
            <div class="col-12 col-md-5 col-lg-4 mt-2 mt-md-0">
                <div class="input-group search-input-group shadow-sm rounded-pill overflow-hidden border">
                    <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-primary"></i></span>
                    <input type="text" id="searchPanduanSiswa" class="form-control border-0 ps-2 small" placeholder="Cari panduan (misal: gabung, tugas, cbt, rapor, spp)..." onkeyup="filterPanduanSiswa()">
                </div>
            </div>
        </div>

        <!-- Category Filter Pills (Horizontal swipe on mobile) -->
        <div class="d-flex gap-2 mt-3 pt-2 border-top overflow-x-auto flex-nowrap no-scrollbar pb-1" id="categoryFilterContainerSiswa">
            <button class="btn btn-sm btn-primary rounded-pill px-3 py-1 flex-shrink-0 cat-btn-siswa active" onclick="filterKategoriSiswa('all', this)"><i class="bi bi-grid-fill me-1"></i>Semua Modul</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-siswa" onclick="filterKategoriSiswa('dashboard', this)"><i class="bi bi-speedometer2 me-1"></i>Beranda</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-siswa" onclick="filterKategoriSiswa('kelas', this)"><i class="bi bi-bounding-box-circles me-1"></i>Gabung Kelas</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-siswa" onclick="filterKategoriSiswa('materi', this)"><i class="bi bi-book-half me-1"></i>Materi & Modul</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-siswa" onclick="filterKategoriSiswa('tugas', this)"><i class="bi bi-file-earmark-check me-1"></i>Tugas Siswa</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-siswa" onclick="filterKategoriSiswa('cbt', this)"><i class="bi bi-patch-question me-1"></i>Ujian CBT</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-siswa" onclick="filterKategoriSiswa('presensi', this)"><i class="bi bi-qr-code-scan me-1"></i>Presensi QR</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-siswa" onclick="filterKategoriSiswa('rapor', this)"><i class="bi bi-file-earmark-spreadsheet me-1"></i>E-Rapor & Nilai</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-siswa" onclick="filterKategoriSiswa('spp', this)"><i class="bi bi-wallet2 me-1"></i>SPP & Ekskul</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-siswa" onclick="filterKategoriSiswa('faq', this)"><i class="bi bi-question-circle me-1"></i>FAQ Kendala</button>
        </div>
    </div>

    <!-- 2. Alur Pembelajaran Digital Siswa (5-Stage Visual Roadmap) -->
    <div class="card-custom roadmap-container p-3 p-md-4 mb-4 shadow-sm bg-white">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-primary fs-6"><i class="bi bi-diagram-3-fill me-2"></i>Alur Pembelajaran & Evaluasi Digital Siswa</h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 small"><i class="bi bi-lightning-charge-fill me-1"></i>5 Tahap Sukses Belajar</span>
        </div>
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-primary">
                    <span class="badge bg-primary mb-2.5 px-2.5 py-1">Tahap 1</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-key-fill text-primary me-1.5"></i>Gabung Rombel</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Masukkan Kode Akses (*Key Mapel*) dari Guru pengampu atau pilih rombel kelas resmi di portal sekolah.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-success">
                    <span class="badge bg-success mb-2.5 px-2.5 py-1">Tahap 2</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-book-fill text-success me-1.5"></i>Pelajari Materi</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Ikuti alur *Learning Path*, baca modul PDF tanpa unduh, tonton video MP4/YouTube, dan pinjam E-Book perpustakaan.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-warning">
                    <span class="badge bg-warning text-dark mb-2.5 px-2.5 py-1">Tahap 3</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-pencil-square text-warning me-1.5"></i>Tugas & Kuis CBT</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Upload tugas ber-deadline sebelum batas waktu & selesaikan ujian CBT online dengan timer dan anti-curang browser.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-info">
                    <span class="badge bg-info text-dark mb-2.5 px-2.5 py-1">Tahap 4</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-qr-code-scan text-info me-1.5"></i>Presensi QR</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Bawa Kartu Pelajar Digital ber-QR Code untuk di-scan oleh Guru di kelas atau petugas di gerbang sekolah.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-danger">
                    <span class="badge bg-danger mb-2.5 px-2.5 py-1">Tahap 5</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-award-fill text-danger me-1.5"></i>E-Rapor & Prestasi</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Pantau rekap transkrip nilai resmi, cetak lembaran E-Rapor PDF, dan unduh Sertifikat Kelulusan Belajar.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Modul-by-Modul Comprehensive Guides Accordion -->
    <div class="accordion" id="accordionPanduanSiswa">

        <!-- =========================================================================
             MODUL 1: BERANDA & DASHBOARD BELAJAR SISWA
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa" data-category="dashboard">
            <h2 class="accordion-header">
                <button class="accordion-button fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul1">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-primary-subtle text-primary">
                            <i class="bi bi-grid-1x2-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-primary text-white px-2 py-0.5 rounded-pill small">Modul 1</span>
                                <span class="fw-bold text-dark modul-title-text">Beranda & Dashboard Belajar Siswa</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Ringkasan KBM, Jadwal Pelajaran, Notifikasi & Grafik Nilai</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">siswa/dashboard</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseSiswaModul1" class="accordion-collapse collapse show" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="bi bi-link-45deg me-1"></i>Navigasi: Beranda Siswa</span>
                        <span class="badge bg-light text-muted border px-2 py-1">URL: index.php?url=siswa/dashboard</span>
                    </div>

                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle text-primary me-1"></i>Pusat Kendali Belajar Harian Siswa</h6>
                    <p class="small text-muted mb-3">Dashboard merupakan beranda utama saat siswa berhasil masuk ke aplikasi. Seluruh aktivitas KBM, tugas yang harus diselesaikan, hingga jadwal harian disajikan secara transparan dan mudah diakses:</p>

                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-primary small mb-1"><i class="bi bi-book-half me-1"></i>Materi & Modul Baru</h6>
                                <p class="small text-muted mb-0">Menghitung jumlah bahan ajar yang baru diunggah oleh Guru pengampu sesuai kelas Anda.</p>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-warning small mb-1"><i class="bi bi-card-checklist me-1"></i>Tugas Perlu Dikerjakan</h6>
                                <p class="small text-muted mb-0">Daftar tugas yang belum diserahkan beserta peringatan tenggat waktu pengerjaan (deadline).</p>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-danger small mb-1"><i class="bi bi-patch-question-fill me-1"></i>Kuis & Ujian CBT Aktif</h6>
                                <p class="small text-muted mb-0">Paket evaluasi CBT yang dibuka hari ini. Klik langsung untuk menuju lembar pengerjaan kuis.</p>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-success small mb-1"><i class="bi bi-calendar-event me-1"></i>Jadwal Belajar & Sholat</h6>
                                <p class="small text-muted mb-0">Jadwal mata pelajaran sesi hari ini lengkap dengan info waktu sholat otomatis sekolah.</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 mt-3 mb-0 small">
                        <i class="bi bi-lightbulb-fill text-warning me-1"></i><strong>Tips Siswa:</strong> Buka beranda setiap pagi sebelum KBM dimulai untuk mengecek tugas yang mendekati batas waktu serta pengumuman penting dari sekolah.
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 2: GABUNG KELAS VIRTUAL & KEY MAPEL
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa" data-category="kelas">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul2">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-success-subtle text-success">
                            <i class="bi bi-bounding-box-circles fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-success text-white px-2 py-0.5 rounded-pill small">Modul 2</span>
                                <span class="fw-bold text-dark modul-title-text">Gabung Kelas Virtual & Key Mapel Guru</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Pendaftaran Rombel, Input Passcode Mapel & Katalog Kelas</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">siswa/gabungKelas</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseSiswaModul2" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <p class="small text-muted mb-3">Untuk mengakses materi, tugas, dan ujian, Anda wajib terdaftar terlebih dahulu pada mata pelajaran guru pengampu. Tersedia <strong>2 Cara Mudah Mendaftar</strong>:</p>

                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-success mb-2"><i class="bi bi-key-fill me-1"></i>Cara 1: Memasukkan Kode Akses (Key Mapel)</h6>
                                <ol class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Minta <strong>Kode Akses / Enrollment Key</strong> kepada Guru mapel Anda (contoh format: <code>RPL-WEB-2026</code>).</li>
                                    <li class="mb-1.5">Buka menu <strong>Gabung Kelas</strong> di sidebar kiri.</li>
                                    <li class="mb-1.5">Ketikkan kode akses pada kolom <em>"Masukkan Kode Akses Mapel"</em>.</li>
                                    <li class="mb-1.5">Klik tombol <strong>Gabung Sekarang</strong>. Jika kode cocok, Anda langsung terdaftar sebagai peserta kelas aktif.</li>
                                </ol>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-grid-3x3-gap-fill me-1"></i>Cara 2: Pilih Rombel Resmi Sekolah</h6>
                                <ol class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Pada halaman Gabung Kelas, gulir ke bagian <strong>Katalog Rombel Kelas Terbuka</strong>.</li>
                                    <li class="mb-1.5">Cari nama mata pelajaran dan kelas Anda (contoh: <em>Basis Data - XI RPL 1</em>).</li>
                                    <li class="mb-1.5">Klik tombol hijau <strong>Gabung Ke Rombel Ini</strong>.</li>
                                    <li class="mb-1.5">Sistem akan secara instan mendaftarkan akun Anda ke dalam kelas tersebut.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning border-0 rounded-3 mb-0 small">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i><strong>Perhatian:</strong> Jika Anda belum bergabung ke mapel, materi pembelajaran dan lembar ujian CBT mapel tersebut tidak akan muncul pada akun Anda.
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 3: ALUR BELAJAR RUNTUT (LEARNING PATH)
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa" data-category="materi">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul3">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-warning-subtle text-warning text-dark">
                            <i class="bi bi-compass-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-warning text-dark px-2 py-0.5 rounded-pill small">Modul 3</span>
                                <span class="fw-bold text-dark modul-title-text">Alur Belajar Runtut (Learning Path)</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Tahapan Bab Terstruktur, Prasyarat Materi & Progress Bar</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">siswa/learningPath</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseSiswaModul3" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-signpost-split-fill text-warning me-1"></i>Belajar Bertahap Tanpa Bingung</h6>
                    <p class="small text-muted mb-3">Fitur <strong>Learning Path</strong> dirancang oleh Guru agar siswa mempelajari materi secara berurutan dan terstruktur dari bab pengantar hingga mahir:</p>

                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-primary small d-block mb-1"><i class="bi bi-1-circle-fill me-1"></i>Peta Tahapan Modul</strong>
                                <p class="small text-muted mb-0">Melihat daftar bab kompetensi dasar kejuruan yang wajib dipelajari dari awal hingga akhir semester.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-warning text-dark small d-block mb-1"><i class="bi bi-lock-fill me-1"></i>Materi Prasyarat</strong>
                                <p class="small text-muted mb-0">Sebagian bab memiliki prasyarat: Anda harus menyelesaikan bab sebelumnya sebelum bab berikutnya dapat dibuka.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-success small d-block mb-1"><i class="bi bi-percent me-1"></i>Indikator Progress Belajar</strong>
                                <p class="small text-muted mb-0">Bilah progress otomatis terisi saat Anda menandai materi telah selesai dibaca atau dipraktikkan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 4: MATERI AJAR, VIDEO & PERPUSTAKAAN DIGITAL
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa" data-category="materi">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul4">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-primary-subtle text-primary">
                            <i class="bi bi-book-half fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-primary text-white px-2 py-0.5 rounded-pill small">Modul 4</span>
                                <span class="fw-bold text-dark modul-title-text">Materi Ajar, Video & Perpustakaan Digital</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Pratinjau PDF Instan, Player Video YouTube & E-Book Sekolah</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">siswa/materi</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseSiswaModul4" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-file-earmark-pdf-fill me-1"></i>1. Akses Materi & Pratinjau Instan (`siswa/materi`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5"><strong>Pratinjau Tanpa Unduh:</strong> Berkas PDF, PPTX, dan Word dapat dibaca langsung melalui modal pratinjau browser sehingga menghemat memori penyimpanan HP Anda.</li>
                                    <li class="mb-1.5"><strong>Player Video Interaktif:</strong> Tonton video penjelasan Guru atau sematan video tutorial YouTube tanpa gangguan iklan eksternal.</li>
                                    <li class="mb-1.5"><strong>Unduh Berkas:</strong> Tombol download tetap disediakan apabila Anda ingin menyimpan modul untuk belajar saat offline.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-success mb-2"><i class="bi bi-bookshelf me-1"></i>2. Perpustakaan Digital Sekolah (`library`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5"><strong>Katalog E-Book:</strong> Akses ratusan buku paket sekolah resmi Kemendikbudristek dan buku panduan kejuruan SMK.</li>
                                    <li class="mb-1.5"><strong>Pencarian Cepat:</strong> Filter buku berdasarkan mata pelajaran, judul, penulis, atau jurusan.</li>
                                    <li class="mb-1.5"><strong>Bebas Akses 24/7:</strong> Baca kapan saja tanpa batasan waktu untuk pengayaan wawasan literasi digital Anda.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 5: PENGERJAAN TUGAS & PENGIRIMAN JAWABAN
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa" data-category="tugas">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul5">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-danger-subtle text-danger">
                            <i class="bi bi-file-earmark-arrow-up-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-danger text-white px-2 py-0.5 rounded-pill small">Modul 5</span>
                                <span class="fw-bold text-dark modul-title-text">Pengumpulan Tugas Terstruktur & Deadline</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Upload Dokumen/Foto, Catatan Siswa, Status Nilai & Feedback</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">siswa/tugas</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseSiswaModul5" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-card-checklist text-danger me-1"></i>Langkah Mengerjakan & Mengirim Tugas:</h6>
                    <ol class="small text-muted ps-3 mb-3">
                        <li class="mb-1.5">Masuk ke menu <strong>Tugas Saya</strong> (`siswa/tugas`).</li>
                        <li class="mb-1.5">Pilih tugas yang bertuliskan badge kuning <span class="badge bg-warning text-dark">Belum Dikerjakan</span>. Perhatikan batas waktu (deadline) di kartu tugas.</li>
                        <li class="mb-1.5">Klik tombol <strong>Kirim Jawaban</strong>.</li>
                        <li class="mb-1.5">Unggah berkas jawaban Anda (format dokumen: PDF, DOCX, ZIP atau foto hasil pengerjaan di buku tulis: JPG, PNG).</li>
                        <li class="mb-1.5">Tuliskan pesan atau catatan tambahan untuk guru pada kolom catatan jika diperlukan, lalu klik <strong>Submit Tugas</strong>.</li>
                    </ol>

                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-light text-center h-100">
                                <span class="badge bg-warning text-dark mb-1">Status: Menunggu Dinilai</span>
                                <p class="small text-muted mb-0">Tugas Anda telah sukses masuk ke meja koreksi Guru dan menunggu input nilai.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-light text-center h-100">
                                <span class="badge bg-success mb-1">Status: Sudah Dinilai</span>
                                <p class="small text-muted mb-0">Angka nilai (0-100) serta catatan revisi/feedback dari Guru langsung ditampilkan di kartu tugas.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-light text-center h-100">
                                <span class="badge bg-danger mb-1">Status: Terlambat</span>
                                <p class="small text-muted mb-0">Pengiriman melewati tenggat waktu akan ditandai terlambat sesuai kebijakan guru pengampu.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 6: KUIS & UJIAN CBT ONLINE ANTI-CURANG
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa" data-category="cbt">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul6">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-warning-subtle text-warning text-dark">
                            <i class="bi bi-patch-question-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-warning text-dark px-2 py-0.5 rounded-pill small">Modul 6</span>
                                <span class="fw-bold text-dark modul-title-text">Ujian CBT Online & Sistem Anti-Curang</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Pilihan Ganda, Benar/Salah, Essay, Timer & Proteksi Tab</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">siswa/quiz</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseSiswaModul6" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-danger mb-2"><i class="bi bi-shield-lock-fill me-1"></i>Sistem Proteksi Anti-Curang CBT</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5"><strong>Deteksi Pindah Tab / Aplikasi:</strong> Sistem mencatat setiap kali siswa membuka tab baru, browser lain, atau aplikasi chat. Peringatan akan muncul dan dapat berakibat ujian terkunci.</li>
                                    <li class="mb-1.5"><strong>Pengacakan Soal & Pilihan:</strong> Urutan nomor soal dan opsi jawaban (A/B/C/D/E) diacak otomatis per-siswa.</li>
                                    <li class="mb-1.5"><strong>Timer Countdown Realtime:</strong> Menghitung mundur sisa menit pengerjaan. Jika timer habis, lembar jawaban otomatis tersimpan (*Auto-Submit*).</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-ui-checks me-1"></i>Navigasi Lembar Ujian CBT</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5"><strong>Palet Nomor Soal:</strong> Mengklik nomor di palet untuk melompat langsung ke pertanyaan tertentu.</li>
                                    <li class="mb-1.5"><strong>Penanda Ragu-Ragu:</strong> Tandai nomor soal dengan warna kuning jika jawaban masih ingin ditinjau kembali.</li>
                                    <li class="mb-1.5"><strong>Review Kunci Jawaban:</strong> Setelah ujian selesai dan hasil dipublikasikan oleh Guru, Anda dapat melihat review pembahasan soal di menu <code>siswa/quiz</code>.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 7: KARTU PELAJAR QR CODE & PRESENSI GERBANG
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa" data-category="presensi">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul7">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-info-subtle text-info">
                            <i class="bi bi-qr-code-scan fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-info text-dark px-2 py-0.5 rounded-pill small">Modul 7</span>
                                <span class="fw-bold text-dark modul-title-text">Kartu Pelajar Digital & Presensi QR Code</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Kartu Tanda Siswa QR, Scan Presensi di Gerbang & Riwayat Hadir</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">siswa/kartuPelajar</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseSiswaModul7" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-info mb-2"><i class="bi bi-person-badge-fill me-1"></i>1. Kartu Pelajar Digital Resmi (`siswa/kartuPelajar`)</h6>
                                <p class="small text-muted mb-2">Memuat identitas lengkap siswa: Foto resmi, Nama, NIS, NISN, Rombel Kelas, Jurusan, serta <strong>QR Code Kehadiran Terenkripsi</strong>.</p>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1">Dapat diunduh atau dicetak (*print*) menjadi kartu fisik seukuran KTP/ATM.</li>
                                    <li class="mb-1">Dapat pula ditunjukkan langsung dari layar HP Anda saat tiba di gerbang sekolah.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-success mb-2"><i class="bi bi-clock-history me-1"></i>2. Cara Presensi Scan QR & Rekap Kehadiran</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5"><strong>Scan di Gerbang:</strong> Arahkan QR Code kartu ke kamera pemindai petugas piket gerbang saat pagi hari. Suara notifikasi akan berbunyi dan kehadiran Anda langsung tercatat.</li>
                                    <li class="mb-1.5"><strong>Scan di Ruang Kelas:</strong> Guru pengampu juga dapat memindai kartu Anda saat jam mata pelajaran berlangsung.</li>
                                    <li class="mb-1.5"><strong>Notifikasi WA ke Ortu:</strong> Saat presensi sukses, pesan WhatsApp otomatis terkirim ke HP orang tua mengabarkan Anda telah tiba di sekolah.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 8: TRANSKRIP NILAI & E-RAPOR DIGITAL RESMI
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa" data-category="rapor">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul8">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-success-subtle text-success">
                            <i class="bi bi-file-earmark-spreadsheet-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-success text-white px-2 py-0.5 rounded-pill small">Modul 8</span>
                                <span class="fw-bold text-dark modul-title-text">Transkrip Nilai & E-Rapor Digital Resmi</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">4 Komponen Bobot, Nilai Akhir Otomatis & Cetak PDF Rapor</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">siswa/rapor</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseSiswaModul8" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <div class="p-3 border rounded-4 bg-light mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <h6 class="fw-bold text-success mb-0"><i class="bi bi-file-earmark-text-fill me-1"></i>4 Komponen Penilaian Resmi Kurikulum Merdeka</h6>
                            <span class="badge bg-success-subtle text-success border border-success">Transparan & Realtime</span>
                        </div>
                        <p class="small text-muted mb-2">Nilai Akhir Rapor dihitung secara otomatis berdasarkan pembobotan standar sekolah:</p>
                        
                        <div class="row g-2 mb-2 text-center">
                            <div class="col-6 col-md-3">
                                <div class="bg-white p-2 rounded-2 border">
                                    <strong class="text-primary small d-block">Tugas Mandiri</strong>
                                    <span class="badge bg-primary-subtle text-primary">Bobot 20%</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-white p-2 rounded-2 border">
                                    <strong class="text-success small d-block">Kuis / Formatif</strong>
                                    <span class="badge bg-success-subtle text-success">Bobot 20%</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-white p-2 rounded-2 border">
                                    <strong class="text-warning text-dark small d-block">Sumatif Tengah (STS)</strong>
                                    <span class="badge bg-warning-subtle text-dark">Bobot 30%</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-white p-2 rounded-2 border">
                                    <strong class="text-danger small d-block">Sumatif Akhir (SAS)</strong>
                                    <span class="badge bg-danger-subtle text-danger">Bobot 30%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-primary mb-1"><i class="bi bi-printer-fill me-1"></i>Cetak / Simpan PDF Lembaran Rapor</h6>
                                <p class="small text-muted mb-0">Klik tombol <strong>Cetak / Simpan PDF Rapor</strong> di halaman `siswa/rapor`. Dokumen siap dicetak dengan kop resmi sekolah, tanda tangan Kepala Sekolah, dan Wali Kelas tanpa bilah scrollbar.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-warning text-dark mb-1"><i class="bi bi-award-fill me-1"></i>Sertifikat Digital Siswa (`siswa/sertifikat`)</h6>
                                <p class="small text-muted mb-0">Siswa yang menuntaskan program pembelajaran atau meraih kompetensi unggulan dapat mengunduh lembaran Sertifikat Apresiasi ber-QR Code resmi untuk portofolio kerja kelak.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 9: PORTAL KEUANGAN SPP & EKSTRAKURIKULER
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa" data-category="spp">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul9">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-success-subtle text-success">
                            <i class="bi bi-wallet2 fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-success text-white px-2 py-0.5 rounded-pill small">Modul 9</span>
                                <span class="fw-bold text-dark modul-title-text">Portal Keuangan SPP & Ekstrakurikuler</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Cek Tagihan Bulanan, Unduh Kuitansi Pembayaran & Daftar Ekskul</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">siswa/pembayaran</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseSiswaModul9" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-success mb-2"><i class="bi bi-cash-coin me-1"></i>1. Portal Keuangan SPP (`siswa/pembayaran`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5"><strong>Transparansi Tagihan:</strong> Cek status iuran bulanan apakah berstatus <span class="badge bg-success">Lunas</span> atau <span class="badge bg-danger">Belum Lunas</span>.</li>
                                    <li class="mb-1.5"><strong>Kuitansi Resmi:</strong> Klik tombol <em>Kuitansi</em> pada pembayaran yang telah lunas untuk mencetak atau mengunduh slip bukti bayar ber-barcode resmi sekolah.</li>
                                    <li class="mb-1.5"><strong>Pengingat Ortu:</strong> Tagihan yang belum terselesaikan akan dikirimkan secara otomatis lewat WhatsApp Gateway ke nomor HP orang tua.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-flag-fill me-1"></i>2. Kegiatan Ekstrakurikuler (`siswa/ekstrakurikuler`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5"><strong>Pilihan Ekskul:</strong> Pramuka, Paskibra, PMR, Futsal, Rohis, IT Club, Robotik, dan Seni Musik.</li>
                                    <li class="mb-1.5"><strong>Daftar 1-Klik:</strong> Pilih pembina dan jadwal ekskul yang sesuai minat Anda lalu klik <strong>Daftar Anggota</strong>.</li>
                                    <li class="mb-1.5"><strong>Nilai Ekstrakurikuler:</strong> Nilai keaktifan dan predikat ekskul akan langsung masuk ke lembaran E-Rapor Anda di akhir semester.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 10: LIVE CLASS, FORUM & FAQ KENDALA SISWA
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa" data-category="faq">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul10">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-danger-subtle text-danger">
                            <i class="bi bi-question-circle-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-danger text-white px-2 py-0.5 rounded-pill small">Modul 10</span>
                                <span class="fw-bold text-dark modul-title-text">Live Class, Forum Diskusi & FAQ Kendala</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Tatap Muka Video, Chat Guru, Game Edukasi & Solusi Masalah</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">siswa/liveClass</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseSiswaModul10" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <!-- Fitur Interaksi -->
                    <div class="row g-2 g-md-3 mb-3 mb-md-4">
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-danger"><i class="bi bi-camera-reels-fill me-1"></i>Live Virtual Meeting (`siswa/liveClass`)</h6>
                                <p class="small text-muted mb-0">Masuk ke ruang tatap muka video langsung bersama Guru dan teman sekelas tanpa perlu install aplikasi Zoom atau Google Meet luar.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-primary"><i class="bi bi-chat-dots-fill me-1"></i>Forum & Chat Pribadi (`forum` & `chat`)</h6>
                                <p class="small text-muted mb-0">Diskusikan materi di forum rombel atau kirim pesan pribadi kepada Wali Kelas dan Guru mata pelajaran Anda.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-warning text-dark"><i class="bi bi-controller me-1"></i>Game Edukasi Interaktif (`game`)</h6>
                                <p class="small text-muted mb-0">Uji pengetahuan dan istilah teknis kejuruan melalui kuis mini interaktif yang menyenangkan di sela waktu belajar.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Kendala Belajar Siswa -->
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-question-diamond-fill text-danger me-1"></i>Tanya Jawab Kendala Belajar yang Sering Dihadapi Siswa:</h6>
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-primary small d-block mb-1">Materi ajar atau kuis tidak muncul di akun saya?</strong>
                                <p class="small text-muted mb-0">Pastikan Anda telah memasukkan <strong>Kode Akses (Key Mapel)</strong> dari Guru pengampu di menu <code>Gabung Kelas</code>. Jika belum bergabung, sistem mengunci materi demi ketertiban rombel.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-primary small d-block mb-1">Ujian CBT terhenti atau browser keluar tiba-tiba?</strong>
                                <p class="small text-muted mb-0">Tenang, sistem menyimpan jawaban terakhir Anda secara otomatis ke server. Segera buka kembali menu <code>siswa/quiz</code> dan klik tombol <strong>Lanjutkan Ujian</strong> sebelum timer habis.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-primary small d-block mb-1">Gagal mengunggah file tugas?</strong>
                                <p class="small text-muted mb-0">Pastikan ukuran file tugas tidak melebihi 10 MB dan berekstensi yang diizinkan (PDF, DOCX, JPG, PNG, atau ZIP). Jika file terlalu besar, ubah dokumen ke format PDF berukuran ringkas.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-primary small d-block mb-1">Bagaimana cara orang tua saya menerima info nilai dan absensi?</strong>
                                <p class="small text-muted mb-0">Pastikan <strong>Nomor WhatsApp Orang Tua</strong> telah terdaftar dengan benar di profil siswa atau hubungi Wali Kelas/Admin untuk pembaruan nomor WA orang tua di database sekolah.</p>
                            </div>
                        </div>
                    </div>
                </div>
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
.cat-btn-siswa {
    transition: all 0.2s ease-in-out;
    font-size: 0.82rem;
    padding: 0.35rem 0.75rem;
}
.cat-btn-siswa.active {
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
    /* Padding ekstra lega pada Roadmap di layar HP agar isi tidak berdempetan */
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
    .accordion-button {
        font-size: 0.92rem !important;
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
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
}

/* Print styling khusus agar jika di-print hasilnya rapi layaknya buku manual */
@media print {
    .app-sidebar, .app-header, .btn, #categoryFilterContainerSiswa, #searchPanduanSiswa, .input-group {
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
    .panduan-card-siswa {
        page-break-inside: avoid;
        margin-bottom: 1.5rem !important;
    }
}
</style>

<script>
// Filter Pencarian Teks Real-Time untuk Siswa
function filterPanduanSiswa() {
    const query = document.getElementById('searchPanduanSiswa').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.panduan-card-siswa');

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

// Filter Kategori Modul untuk Siswa
function filterKategoriSiswa(cat, btn) {
    document.querySelectorAll('.cat-btn-siswa').forEach(b => {
        b.classList.remove('active', 'btn-primary');
        b.classList.add('btn-light');
    });
    btn.classList.add('active', 'btn-primary');
    btn.classList.remove('btn-light');

    const cards = document.querySelectorAll('.panduan-card-siswa');
    cards.forEach(card => {
        if (cat === 'all') {
            card.style.display = '';
        } else {
            const cardCat = card.getAttribute('data-category') || '';
            if (cardCat.includes(cat)) {
                card.style.display = '';
                const collapse = card.querySelector('.accordion-collapse');
                if (collapse) {
                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapse);
                    bsCollapse.show();
                }
            } else {
                card.style.display = 'none';
            }
        }
    });
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
