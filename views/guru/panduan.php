<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
<div class="container-fluid">

    <!-- 1. Hero Executive Header -->
    <div class="card-custom p-4 mb-4 border-start border-5 border-success shadow-sm">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success-subtle text-success rounded-4 p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-book-half fs-2"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h4 class="fw-bold mb-0 text-dark">Panduan Pengguna & Manual Mengajar Sistem</h4>
                        <span class="badge bg-success px-2.5 py-1">Hak Akses: Guru / Pendidik</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 small">Kurikulum Merdeka & KBM Digital</span>
                    </div>
                    <p class="text-muted small mb-0">Petunjuk teknis pengajaran digital, pembuatan Kode Akses (Key Mapel), Learning Path, upload modul & video, kuis CBT anti-curang, presensi selfie GPS, scan QR kartu siswa, penyusunan CP-TP, leger E-Rapor batch, peran Wali Kelas, hingga komunikasi WhatsApp orang tua murid.</p>
                </div>
            </div>
            <div class="col-12 col-md-5 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-success"></i></span>
                    <input type="text" id="searchPanduanGuru" class="form-control border-start-0 ps-0" placeholder="Ketik kata kunci (misal: key mapel, gps, e-rapor, wali kelas, cbt)..." onkeyup="filterPanduanGuru()">
                    <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" onclick="resetSearchGuru()" title="Reset Pencarian"><i class="bi bi-x-circle"></i></button>
                </div>
            </div>
        </div>

        <!-- Action Bar: Filter Kategori Cepat & Utilitas -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top">
            <div class="d-flex flex-wrap gap-1" id="categoryFilterContainerGuru">
                <button class="btn btn-sm btn-success active cat-btn-guru" onclick="filterByCategoryGuru('all', this)"><i class="bi bi-collection me-1"></i>Semua Topik</button>
                <button class="btn btn-sm btn-outline-success cat-btn-guru" onclick="filterByCategoryGuru('key', this)"><i class="bi bi-key-fill me-1"></i>Key Mapel & Siswa</button>
                <button class="btn btn-sm btn-outline-success cat-btn-guru" onclick="filterByCategoryGuru('materi', this)"><i class="bi bi-book-fill me-1"></i>Materi & Path</button>
                <button class="btn btn-sm btn-outline-success cat-btn-guru" onclick="filterByCategoryGuru('evaluasi', this)"><i class="bi bi-patch-question-fill me-1"></i>Tugas & CBT Ujian</button>
                <button class="btn btn-sm btn-outline-success cat-btn-guru" onclick="filterByCategoryGuru('presensi', this)"><i class="bi bi-camera-fill me-1"></i>Presensi Selfie GPS & QR</button>
                <button class="btn btn-sm btn-outline-success cat-btn-guru" onclick="filterByCategoryGuru('rapor', this)"><i class="bi bi-pencil-fill me-1"></i>CP-TP & Leger E-Rapor</button>
                <button class="btn btn-sm btn-outline-primary cat-btn-guru" onclick="filterByCategoryGuru('walikelas', this)"><i class="bi bi-person-workspace me-1"></i>Wali Kelas & Kontak Ortu</button>
                <button class="btn btn-sm btn-outline-danger cat-btn-guru" onclick="filterByCategoryGuru('faq', this)"><i class="bi bi-question-circle me-1"></i>FAQ Kendala</button>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="toggleAllAccordionsGuru(true)" title="Buka Semua Modul">
                    <i class="bi bi-arrows-expand me-1"></i>Buka Semua
                </button>
                <button class="btn btn-sm btn-outline-secondary" type="button" onclick="toggleAllAccordionsGuru(false)" title="Tutup Semua Modul">
                    <i class="bi bi-arrows-collapse me-1"></i>Tutup Semua
                </button>
                <button class="btn btn-sm btn-outline-dark" type="button" onclick="window.print()" title="Cetak Manual">
                    <i class="bi bi-printer-fill me-1"></i>Cetak Panduan
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Alur Utama KBM Digital Guru (Diagram Step-by-Step 5 Tahapan) -->
    <div class="card-custom p-4 mb-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-success mb-0"><i class="bi bi-diagram-3-fill me-2"></i>Roadmap KBM Digital & Evaluasi Pembelajaran Guru</h6>
            <span class="text-muted small d-none d-md-inline"><i class="bi bi-info-circle me-1"></i>Alur 5 Tahap dari Awal Semester hingga Cetak Rapor</span>
        </div>
        <div class="row g-3">
            <div class="col-12 col-md-6 col-lg">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative border-start border-4 border-primary">
                    <span class="badge bg-primary mb-2">Tahap 1</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-key-fill text-primary me-1"></i>Key Mapel & CP-TP</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Buat Kode Akses unik per-mapel di <i>Kelas Virtual Saya</i>, susun CP-TP Kurikulum Merdeka, dan bagikan key ke siswa.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative border-start border-4 border-success">
                    <span class="badge bg-success mb-2">Tahap 2</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-cloud-upload-fill text-success me-1"></i>Materi & Path</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Unggah modul PDF/Video MP4/YouTube, atur alur belajar (*Learning Path*), dan manfaatkan referensi Perpustakaan Digital.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative border-start border-4 border-warning">
                    <span class="badge bg-warning text-dark mb-2">Tahap 3</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-patch-question-fill text-warning me-1"></i>Tugas & CBT Ujian</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Rancang tugas ber-deadline, susun CBT multi-soal (PG, B/S, Essay) dengan anti-curang, timer otomatis, dan Bank Soal.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative border-start border-4 border-info">
                    <span class="badge bg-info text-dark mb-2">Tahap 4</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-camera-fill text-info me-1"></i>Presensi GPS & QR</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Lakukan Presensi Selfie GPS saat tiba di sekolah, pantau jadwal mengajar hari ini, serta scan QR presensi kartu siswa di kelas.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative border-start border-4 border-danger">
                    <span class="badge bg-danger mb-2">Tahap 5</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-pencil-fill text-danger me-1"></i>Leger Rapor & Wali</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Input Leger E-Rapor Batch 1-klik, kalkulasi nilai akhir otomatis, dan jalankan tugas Wali Kelas binaan serta WhatsApp Ortu.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Modul-by-Modul Comprehensive Guides Accordion -->
    <div class="accordion" id="accordionPanduanGuru">

        <!-- =========================================================================
             MODUL 1: DASHBOARD EXECUTIVE & PENGINGAT KBM
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru" data-category="dashboard">
            <h2 class="accordion-header">
                <button class="accordion-button fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul1">
                    <i class="bi bi-grid-1x2-fill text-success me-2 fs-5"></i> Modul 1: Dashboard Mengajar, Pengingat KBM & Waktu Sholat (`guru/dashboard`)
                </button>
            </h2>
            <div id="collapseGuruModul1" class="accordion-collapse collapse show" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-link-45deg me-1"></i>Navigasi: Dashboard & Panduan &gt; Dashboard Mengajar</span>
                        <span class="badge bg-light text-muted border px-2 py-1">URL: index.php?url=guru/dashboard</span>
                    </div>

                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle text-success me-1"></i>Pusat Monitoring Harian Guru</h6>
                    <p class="small text-muted mb-3">Dashboard dirancang khusus sebagai asisten mengajar digital Anda. Setiap kali login, sistem akan langsung menyajikan informasi prioritas tugas hari ini:</p>

                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary small mb-1"><i class="bi bi-clock-history me-1"></i>Sesi Mengajar Hari Ini</h6>
                                <p class="small text-muted mb-0">Mendeteksi otomatis hari berjalan (Senin-Sabtu) dan menampilkan jam KBM, rombel kelas target, mapel, serta ruangan/lab.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-success small mb-1"><i class="bi bi-bar-chart-line-fill me-1"></i>Statistik Pembelajaran Realtime</h6>
                                <p class="small text-muted mb-0">Menampilkan jumlah Materi Aktif, Tugas yang sedang berjalan, Kuis CBT, dan total Siswa yang telah mendaftar.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-warning small mb-1"><i class="bi bi-pie-chart-fill me-1"></i>Distribusi Siswa Terdaftar</h6>
                                <p class="small text-muted mb-0">Grafik donat yang memetakan proporsi jumlah siswa terdaftar di setiap mata pelajaran yang Anda ampu.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-danger small mb-1"><i class="bi bi-moon-stars-fill me-1"></i>Waktu Sholat Sekolah</h6>
                                <p class="small text-muted mb-0">Pengingat waktu ibadah Subuh, Dzuhur, Ashar, Maghrib, Isya terintegrasi berdasarkan koordinat wilayah sekolah.</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 mt-3 mb-0 small">
                        <i class="bi bi-megaphone-fill me-1"></i><strong>Informasi Resmi Sekolah:</strong> Bagian bawah dashboard juga menampilkan surat edaran atau pengumuman resmi yang diterbitkan oleh Kepala Sekolah atau Administrator.
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 2: KELAS VIRTUAL, KODE AKSES (KEY MAPEL) & SISWA TERDAFTAR
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru" data-category="key">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul2">
                    <i class="bi bi-bounding-box-circles text-primary me-2 fs-5"></i> Modul 2: Kelas Virtual, Kode Akses (Key Mapel), & Data Siswa Terdaftar (`guru/kelasVirtual`)
                </button>
            </h2>
            <div id="collapseGuruModul2" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <p class="small text-muted mb-3">Halaman ini merupakan pintu gerbang utama mengorganisir rombel belajar Anda. Terdiri atas <strong>3 Tab Navigasi Terstruktur</strong>:</p>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-success"><i class="bi bi-building me-1"></i>Tab 1: Rombel Kelas Virtual Saya</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Menampilkan seluruh kartu rombel kelas yang Anda ampu secara otomatis sesuai jadwal dari Admin.</li>
                                    <li class="mb-1.5">Terdapat badge status khusus <span class="badge bg-success">Saya Wali Kelas</span> jika Anda ditugaskan sebagai Wali Kelas pada rombel tersebut.</li>
                                    <li class="mb-1.5">Tersedia tombol pintas menuju Presensi Kelas, Upload Materi, dan Pembuatan Tugas.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-warning text-dark"><i class="bi bi-key-fill me-1"></i>Tab 2: Kode Akses (Key Mapel)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5"><strong>Wajib Dibuat di Awal Semester:</strong> Guru wajib membuat Passcode / Enrollment Key unik per mata pelajaran (misal: <code>RPL-PROG-2026</code>).</li>
                                    <li class="mb-1.5"><strong>Proteksi Belajar:</strong> Siswa yang belum memasukkan Key Mapel ini <strong>tidak dapat mengakses materi, tugas, maupun ujian CBT</strong> Anda.</li>
                                    <li class="mb-1.5">Kunci dapat diaktifkan, diganti, atau dinonaktifkan sewaktu-waktu jika pendaftaran telah ditutup.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-info"><i class="bi bi-people-fill me-1"></i>Tab 3: Siswa Terdaftar Mapel Saya</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Monitoring real-time seluruh siswa yang telah berhasil bergabung ke mapel Anda.</li>
                                    <li class="mb-1.5">Tersedia filter terisolasi (hanya menampilkan mapel dan rombel yang Anda ajar).</li>
                                    <li class="mb-1.5">Pencarian cepat berdasarkan Nama Siswa, NIS, NISN, atau Jurusan (RPL, TKJ, DKV, dll).</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success border-0 rounded-3 mb-0 small">
                        <i class="bi bi-lightbulb-fill text-warning me-1"></i><strong>Tips Praktis Guru:</strong> Pada pertemuan pertama KBM, tampilkan Kode Akses (Key Mapel) Anda di layar proyektor kelas atau bagikan melalui grup kelas agar siswa mendaftar secara serentak.
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 3: URUTAN LEARNING PATH SISWA
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru" data-category="materi">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul3">
                    <i class="bi bi-compass-fill text-warning me-2 fs-5"></i> Modul 3: Menyusun Urutan Alur Belajar (Learning Path) (`guru/learningPath`)
                </button>
            </h2>
            <div id="collapseGuruModul3" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <h6 class="fw-bold text-dark"><i class="bi bi-diagram-2 text-warning me-1"></i>Konsep Alur Belajar Mandiri Terstruktur</h6>
                    <p class="small text-muted mb-3">Fitur Learning Path memungkinkan Guru menyusun peta perjalanan belajar siswa secara runtut (Bab 1 &gt; Bab 2 &gt; Ujian Tengah &gt; Bab 3). Dengan alur ini, siswa tidak akan bingung mencari urutan materi yang harus dipelajari terlebih dahulu.</p>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark small mb-1"><i class="bi bi-list-ol text-primary me-1"></i>1. Menentukan Langkah Pembelajaran</h6>
                                <p class="small text-muted mb-0">Pilih mata pelajaran, buat judul tahapan (misal: <i>Tahap 1: Pengenalan Logika Pemrograman</i>), tautkan materi modul yang relevan, lalu tentukan evaluasi kuis pendukung di akhir tahapan.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-dark small mb-1"><i class="bi bi-lock-fill text-danger me-1"></i>2. Penguncian Prasyarat (*Prerequisite*)</h6>
                                <p class="small text-muted mb-0">Anda dapat mengatur agar tahapan berikutnya baru terbuka jika siswa telah menyelesaikan membaca modul atau mencapai batas KKM pada evaluasi tahapan sebelumnya.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 4: UPLOAD MATERI MULTIMEDIA & PERPUSTAKAAN DIGITAL
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru" data-category="materi">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul4">
                    <i class="bi bi-cloud-upload-fill text-success me-2 fs-5"></i> Modul 4: Upload Materi Multimedia & Perpustakaan Digital (`guru/materi` & `library`)
                </button>
            </h2>
            <div id="collapseGuruModul4" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-success mb-2"><i class="bi bi-file-earmark-arrow-up-fill me-1"></i>1. Unggah Materi & Video (`guru/materi`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5"><strong>Restriksi Pengampuan Otomatis:</strong> Saat menekan tombol <i>Upload Materi Baru</i>, dropdown Mata Pelajaran dan Rombel Kelas <strong>otomatis terkunci hanya untuk mapel yang Anda ajar</strong>.</li>
                                    <li class="mb-1.5"><strong>Format Berkas yang Didukung:</strong> Dokumen PDF, Word (DOCX), PowerPoint (PPTX), Excel (XLSX), Gambar, dan Video lokal MP4.</li>
                                    <li class="mb-1.5"><strong>Integrasi Video YouTube:</strong> Cukup tempel link YouTube (misal: <code>https://youtube.com/watch?v=...</code>). Sistem otomatis membuat player video yang dapat ditonton siswa langsung tanpa iklan yang mengganggu.</li>
                                    <li class="mb-1.5"><strong>Fitur Modal Pratinjau (Preview):</strong> Seluruh dokumen PDF dan video dapat dibaca/ditonton langsung di dalam portal tanpa mewajibkan siswa mengunduh file besar.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-bookshelf me-1"></i>2. Perpustakaan Digital Sekolah (`library`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Guru dapat memanfaatkan katalog E-Book resmi sekolah sebagai materi pengayaan atau referensi belajar siswa.</li>
                                    <li class="mb-1.5">Pencarian buku berdasarkan kategori kejuruan, pengarang, dan tahun terbit.</li>
                                    <li class="mb-1.5">Guru juga memiliki hak mengunggah modul ajar berformat PDF ke dalam perpustakaan sekolah agar dapat dibaca oleh seluruh siswa secara luas.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 5: TUGAS, RUBRIK PENILAIAN & GAME EDUKASI
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru" data-category="evaluasi">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul5">
                    <i class="bi bi-card-checklist text-danger me-2 fs-5"></i> Modul 5: Kelola Penugasan, Rubrik Evaluasi & Game Edukasi (`guru/tugas` & `game`)
                </button>
            </h2>
            <div id="collapseGuruModul5" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-danger"><i class="bi bi-pencil-square me-1"></i>1. Membuat Penugasan Baru</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Tentukan Judul Tugas, Petunjuk Pengerjaan, serta Kelas dan Mata Pelajaran target.</li>
                                    <li class="mb-1.5"><strong>Batas Waktu Pengumpulan (*Deadline*):</strong> Tentukan tanggal dan jam batas akhir secara presisi. Siswa yang mengumpulkan terlambat akan ditandai oleh sistem.</li>
                                    <li class="mb-1.5"><strong>Lampiran Berkas Soal:</strong> Anda dapat melampirkan file lembar kerja siswa (PDF/Word).</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-success"><i class="bi bi-award-fill me-1"></i>2. Pemeriksaan & Pemberian Nilai Siswa</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Klik tombol <strong>Nilai Siswa</strong> pada baris tugas untuk melihat daftar pengumpulan tugas.</li>
                                    <li class="mb-1.5">Unduh berkas jawaban tugas yang dikirim siswa atau periksa tautan repositori tugas mereka.</li>
                                    <li class="mb-1.5">Input nilai (skala 0 - 100) dan berikan catatan evaluasi/umpan balik konstruktif. Nilai ini otomatis tersimpan ke rekap E-Rapor.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="p-3 border rounded-4 bg-light">
                                <h6 class="fw-bold text-dark"><i class="bi bi-controller text-warning me-1"></i>3. Game Edukasi Interaktif (`game`)</h6>
                                <p class="small text-muted mb-0">Manfaatkan fitur game edukasi kejuruan sebagai variasi KBM di kelas atau penugasan mandiri yang menyenangkan untuk menguji daya ingat istilah teknis kejuruan siswa SMK.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 6: KUIS, UJIAN CBT ANTI-CURANG & BANK SOAL
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru" data-category="evaluasi">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul6">
                    <i class="bi bi-patch-question-fill text-warning me-2 fs-5"></i> Modul 6: Kuis, Ujian Online CBT Anti-Curang & Bank Soal (`guru/quiz` & `guru/bankSoal`)
                </button>
            </h2>
            <div id="collapseGuruModul6" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-stopwatch text-warning me-1"></i>1. Konfigurasi Ujian CBT (`guru/quiz`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5"><strong>3 Tipe Butir Soal:</strong> Pilihan Ganda (PG), Benar / Salah (True/False), dan Uraian / Essay.</li>
                                    <li class="mb-1.5"><strong>Pengacakan Ganda:</strong> Aktifkan <code>Acak Soal</code> dan <code>Acak Jawaban</code> agar setiap siswa menerima urutan pertanyaan dan opsi (A/B/C/D/E) yang berbeda untuk meminimalisir saling contek.</li>
                                    <li class="mb-1.5"><strong>Timer & Auto-Submit:</strong> Tentukan durasi menit pengerjaan. Jika timer habis, jawaban siswa otomatis dikirim ke server.</li>
                                    <li class="mb-1.5"><strong>Proteksi Anti-Curang:</strong> Sistem memonitor jika siswa berpindah aplikasi atau tab browser, dan akan memberikan peringatan hingga penguncian lembar ujian.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-database text-info me-1"></i>2. Bank Soal & Analisis Butir Soal (`guru/bankSoal`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5"><strong>Penyimpanan Master Soal:</strong> Seluruh soal yang Anda susun tersimpan aman di Bank Soal Guru.</li>
                                    <li class="mb-1.5"><strong>Daur Ulang (*Reuse*):</strong> Soal pada bank soal dapat diimpor langsung saat membuat paket kuis baru untuk kelas paralel atau ujian susulan tanpa perlu mengetik ulang.</li>
                                    <li class="mb-1.5"><strong>Analisis Nilai Instan:</strong> Soal pilihan ganda dinilai otomatis oleh sistem dan menghasilkan tabel rekapitulasi nilai dan perankingan secara langsung.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 7: PRESENSI SELFIE GPS, SCAN QR & JADWAL KBM
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru" data-category="presensi">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul7">
                    <i class="bi bi-camera-fill text-danger me-2 fs-5"></i> Modul 7: Presensi Selfie GPS Guru, Scan QR Siswa & Jadwal Mengajar (`guru/presensiGuru`, `guru/scanQr`, `guru/jadwal`)
                </button>
            </h2>
            <div id="collapseGuruModul7" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-danger"><i class="bi bi-geo-alt-fill me-1"></i>1. Presensi Selfie Guru GPS (`guru/presensiGuru`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5"><strong>Wajib Dilakukan Setiap Hari Mengajar:</strong> Buka menu ini melalui smartphone atau laptop Anda saat tiba di lingkungan sekolah.</li>
                                    <li class="mb-1.5"><strong>Izinkan Kamera & Lokasi:</strong> Izinkan peramban browser mengakses Kamera Depan dan Lokasi GPS perangkat.</li>
                                    <li class="mb-1.5"><strong>Validasi Geofencing:</strong> Sistem akan memeriksa posisi Anda terhadap titik koordinat sekolah. Jika Anda berada dalam radius sekolah, tombol <strong>Kirim Presensi Masuk / Pulang</strong> akan aktif dengan foto selfie terlampir.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-success"><i class="bi bi-qr-code-scan me-1"></i>2. Scan QR Code Presensi Siswa di Kelas (`guru/scanQr`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Saat memulai jam pelajaran di kelas, Guru dapat membuka pemindai kamera untuk scan QR Code Kartu Pelajar siswa.</li>
                                    <li class="mb-1.5">Siswa cukup menghadapkan kartu ke kamera. Status "Hadir" seketika tercatat disertai nada <i>beep</i> konfirmasi.</li>
                                    <li class="mb-1.5">Menghemat waktu presensi manual dibandingkan memanggil absen satu-persatu.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary"><i class="bi bi-calendar-check-fill me-1"></i>3. Presensi Siswa & Rekap Bulanan (`guru/absensi` & `guru/recapBulanan`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Pencatatan manual status <code>Hadir</code>, <code>Izin</code>, <code>Sakit</code>, atau <code>Alpa</code> per sesi pertemuan.</li>
                                    <li class="mb-1.5">Menu <strong>Rekap Absensi Bulanan Matrix</strong> menyajikan rekap kehadiran siswa dari tanggal 1 hingga 31 hari dalam satu bulan per rombel lengkap dengan persentase kehadiran.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-warning text-dark"><i class="bi bi-clock-history me-1"></i>4. Jadwal Mengajar Saya (`guru/jadwal`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Daftar jadwal mengajar mingguan (Senin s/d Sabtu) yang diplot oleh bagian Kurikulum.</li>
                                    <li class="mb-1.5">Hari berjalan otomatis diberi tanda khusus <span class="badge bg-success">Hari Ini ✔</span> untuk memudahkan pengecekan kelas berikutnya.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 8: KURIKULUM MERDEKA (CP-TP), ASESMEN & LEGER E-RAPOR BATCH
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru" data-category="rapor">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul8">
                    <i class="bi bi-pencil-fill text-info me-2 fs-5"></i> Modul 8: Kurikulum Merdeka (CP-TP), Asesmen KKTP & Leger E-Rapor Batch (`guru/cptp`, `guru/asesmen`, `guru/inputNilai`)
                </button>
            </h2>
            <div id="collapseGuruModul8" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-diagram-3-fill me-1"></i>1. Penyusunan CP & TP (`guru/cptp`)</h6>
                                <p class="small text-muted mb-0">Penyusunan Capaian Pembelajaran (CP) dan rincian Tujuan Pembelajaran (TP) per semester sesuai standar Kurikulum Merdeka Fase E (Kelas X) dan Fase F (Kelas XI & XII SMK).</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-danger mb-2"><i class="bi bi-bullseye me-1"></i>2. Asesmen & KKTP Siswa (`guru/asesmen`)</h6>
                                <p class="small text-muted mb-0">Penentuan Kriteria Ketercapaian Tujuan Pembelajaran (KKTP) dan pengkategorian asesmen diagnostik, formatif, dan sumatif kompetensi kejuruan.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Leger E-Rapor Batch -->
                    <div class="p-3.5 border rounded-4 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-success mb-0"><i class="bi bi-file-earmark-spreadsheet-fill me-1"></i>3. Input Nilai Leger E-Rapor Batch 1-Klik (`guru/inputNilai`)</h6>
                            <span class="badge bg-success-subtle text-success border border-success">Sangat Efisien</span>
                        </div>
                        <p class="small text-muted mb-2">Sistem telah dilengkapi tabel pengisian nilai massal tanpa perlu mengklik simpan satu-persatu per-siswa:</p>
                        
                        <div class="row g-2 mb-2">
                            <div class="col-6 col-md-3">
                                <div class="bg-white p-2 rounded-2 border text-center">
                                    <strong class="text-primary small d-block">Tugas Mandiri</strong>
                                    <span class="badge bg-primary-subtle text-primary">Bobot 20%</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-white p-2 rounded-2 border text-center">
                                    <strong class="text-success small d-block">Formatif / Kuis</strong>
                                    <span class="badge bg-success-subtle text-success">Bobot 20%</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-white p-2 rounded-2 border text-center">
                                    <strong class="text-warning text-dark small d-block">Sumatif Tengah (STS)</strong>
                                    <span class="badge bg-warning-subtle text-dark">Bobot 30%</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-white p-2 rounded-2 border text-center">
                                    <strong class="text-danger small d-block">Sumatif Akhir (SAS)</strong>
                                    <span class="badge bg-danger-subtle text-danger">Bobot 30%</span>
                                </div>
                            </div>
                        </div>

                        <ul class="small text-muted ps-3 mb-0">
                            <li class="mb-1"><strong>Kalkulasi Nilai Otomatis:</strong> Mengisi angka pada kolom tugas, formatif, STS, dan SAS akan langsung menghitung Nilai Akhir dan Predikat (A, B, C, D) seketika secara otomatis.</li>
                            <li class="mb-1"><strong>Simpan 1 Rombel Sekaligus:</strong> Tekan tombol hijau <strong>Simpan Seluruh E-Rapor Kelas Ini</strong> di bagian atas tabel untuk menyimpan seluruh baris nilai kelas binaan dalam 1 detik.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 9: PERAN WALI KELAS, LEGER RANKING & WHATSAPP ORANG TUA
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru" data-category="walikelas">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul9">
                    <i class="bi bi-person-workspace text-primary me-2 fs-5"></i> Modul 9: Peran Khusus Wali Kelas, Leger Ranking & Kontak WhatsApp Orang Tua (`guru/waliKelas`)
                </button>
            </h2>
            <div id="collapseGuruModul9" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <div class="alert alert-primary border-0 rounded-3 mb-3 small">
                        <i class="bi bi-info-circle-fill me-1"></i><strong>Menu Khusus Wali Kelas:</strong> Menu ini otomatis aktif di sidebar Anda jika Administrator menugaskan Anda sebagai <strong>Wali Kelas</strong> pada satu atau lebih rombel kelas.
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-easel-fill me-1"></i>1. Monitoring Kelas Binaan (`guru/waliKelas`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Melihat rekapitulasi jumlah siswa, rata-rata nilai rombel, dan persentase kehadiran seluruh peserta didik di kelas binaan Anda.</li>
                                    <li class="mb-1.5"><strong>Cetak E-Rapor Digital Rombel Sekaligus:</strong> Tombol 1-klik untuk mencetak seluruh buku E-Rapor siswa dalam 1 file siap cetak untuk dibagikan saat pembagian rapor semester.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-success mb-2"><i class="bi bi-whatsapp me-1"></i>2. Komunikasi WhatsApp Orang Tua Siswa</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5">Tabel kelas binaan menampilkan data lengkap siswa beserta <strong>Nomor WhatsApp Orang Tua / Wali (`no_ortu`)</strong>.</li>
                                    <li class="mb-1.5">Wali Kelas dapat langsung mengklik tombol WhatsApp untuk membuka obrolan dengan orang tua perihal keterlambatan, absensi, atau perkembangan sikap siswa tanpa harus menyimpan kontak di HP secara manual.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-warning text-dark mb-2"><i class="bi bi-trophy-fill me-1"></i>3. Leger & Ranking Siswa Rombel (`guru/rankingKelas`)</h6>
                                <p class="small text-muted mb-0">Menampilkan peringkat 1, 2, 3 hingga peringkat terakhir di kelas binaan berdasarkan akumulasi nilai rata-rata E-Rapor seluruh mata pelajaran. Sangat membantu dalam penentuan siswa berprestasi dan pemberian penghargaan kelas.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-danger mb-2"><i class="bi bi-activity me-1"></i>4. Bimbingan Ekstrakurikuler (`guru/ekstrakurikuler`)</h6>
                                <p class="small text-muted mb-0">Bagi Guru yang ditugaskan sebagai Pembina Ekskul (Pramuka, Paskibra, PMR, Futsal, IT Club), menu ini digunakan untuk mendata absensi kegiatan latihan rutin dan rekapitulasi anggota siswa.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 10: KOMUNIKASI, LIVE CLASS, KARTU GURU & FAQ KENDALA
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru" data-category="faq">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul10">
                    <i class="bi bi-question-circle-fill text-danger me-2 fs-5"></i> Modul 10: Komunikasi, Live Class, Kartu Guru & Tanya Jawab Solusi Kendala (FAQ)
                </button>
            </h2>
            <div id="collapseGuruModul10" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <!-- Fitur Tambahan -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-danger"><i class="bi bi-camera-reels-fill me-1"></i>Live Virtual Meeting (`guru/liveClass`)</h6>
                                <p class="small text-muted mb-0">Ruang tatap muka video langsung untuk KBM daring atau konsultasi belajar kelompok tanpa perlu akun Zoom/Google Meet luar.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-primary"><i class="bi bi-chat-dots-fill me-1"></i>Forum & Chat Siswa (`forum` & `chat`)</h6>
                                <p class="small text-muted mb-0">Diskusi materi belajar dan pesan pribadi dengan siswa yang terhubung dengan notifikasi unread badge di navbar atas.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-warning text-dark"><i class="bi bi-person-badge-fill me-1"></i>Kartu Guru Digital (`guru/kartuGuru`)</h6>
                                <p class="small text-muted mb-0">Kartu tanda pengenal resmi pendidik ber-QR Code untuk identitas GTK dan presensi scanner sekolah.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Kendala Guru -->
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-question-diamond-fill text-danger me-1"></i>Tanya Jawab Kendala Mengajar Sering Dihadapi Guru:</h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-primary small d-block mb-1">Presensi Selfie ditolak keterangan "Di luar radius"?</strong>
                                <p class="small text-muted mb-0">Pastikan GPS HP Anda dalam mode "Akurasi Tinggi". Buka Google Maps sebentar agar titik koordinat HP terkunci, lalu refresh halaman presensi. Jika posisi gedung sekolah agak jauh dari titik tengah, laporkan ke Admin untuk memperluas radius geofencing sekolah.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-primary small d-block mb-1">Siswa tidak bisa membuka materi atau tugas saya?</strong>
                                <p class="small text-muted mb-0">Pastikan siswa tersebut telah memasukkan <strong>Kode Akses (Key Mapel)</strong> Anda pada menu <i>Gabung Kelas</i> di akun siswa. Cek daftar siswa yang sudah terdaftar pada Tab 3 di <i>guru/kelasVirtual</i>.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-primary small d-block mb-1">Siswa terlempar / terkunci saat ujian CBT?</strong>
                                <p class="small text-muted mb-0">Hal ini terjadi jika siswa melanggar aturan anti-curang (misalnya beralih ke WhatsApp atau aplikasi lain saat ujian berlangsung). Sebagai Guru pembuat kuis, Anda dapat mereset sesi siswa tersebut pada tabel pemantauan hasil ujian.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-primary small d-block mb-1">Nilai tugas atau kuis tidak masuk ke E-Rapor?</strong>
                                <p class="small text-muted mb-0">Buka menu <strong>Input Nilai E-Rapor (`guru/inputNilai`)</strong>, pilih kelas dan mapel yang bersangkutan, periksa angka nilai yang ditarik, lalu tekan tombol hijau <strong>Simpan Seluruh E-Rapor Kelas Ini</strong>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Help & Support Footer Banner -->
    <div class="card-custom p-4 mt-4 shadow-sm border-0 bg-success-subtle text-success-emphasis rounded-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success text-white rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-mortarboard-fill fs-3"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">Semangat Mengajar Dewan Guru SMK Muthia Harapan</h6>
                    <p class="small text-muted mb-0">Portal E-Learning dirancang untuk menyederhanakan tugas administrasi guru sehingga Anda dapat berfokus mendidik dan menginspirasi siswa.</p>
                </div>
            </div>
            <div>
                <a href="<?= BASE_URL ?>index.php?url=guru/dashboard" class="btn btn-success fw-bold px-3 py-2">
                    <i class="bi bi-speedometer2 me-1"></i>Kembali ke Dashboard Mengajar
                </a>
            </div>
        </div>
    </div>

</div>
</main>

<style>
/* Styling khusus panduan guru agar rapi dan responsif */
.card-custom {
    background: #ffffff;
    border-radius: 1rem;
    border: 1px solid rgba(0, 0, 0, 0.06);
}
.accordion-button:not(.collapsed) {
    background-color: rgba(25, 135, 84, 0.08);
    color: #198754;
    box-shadow: none;
}
.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(0, 0, 0, 0.125);
}
.cat-btn-guru {
    transition: all 0.2s ease-in-out;
}
.cat-btn-guru.active {
    box-shadow: 0 2px 6px rgba(25, 135, 84, 0.3);
}

/* Print styling khusus */
@media print {
    .app-sidebar, .app-header, .btn, #categoryFilterContainerGuru, #searchPanduanGuru, .input-group {
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
    .panduan-card-guru {
        page-break-inside: avoid;
        margin-bottom: 1.5rem !important;
    }
}
</style>

<script>
// Filter Pencarian Teks Real-Time untuk Guru
function filterPanduanGuru() {
    const query = document.getElementById('searchPanduanGuru').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.panduan-card-guru');

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

function resetSearchGuru() {
    document.getElementById('searchPanduanGuru').value = '';
    filterPanduanGuru();
}

// Filter Berdasarkan Kategori untuk Guru
function filterByCategoryGuru(category, btnElement) {
    // Reset active button
    document.querySelectorAll('.cat-btn-guru').forEach(btn => {
        btn.classList.remove('active', 'btn-success', 'btn-primary', 'btn-danger');
        if (btn.getAttribute('onclick').includes('walikelas')) {
            btn.classList.add('btn-outline-primary');
        } else if (btn.getAttribute('onclick').includes('faq')) {
            btn.classList.add('btn-outline-danger');
        } else {
            btn.classList.add('btn-outline-success');
        }
    });

    // Set active class on clicked button
    btnElement.classList.add('active');
    if (category === 'walikelas') {
        btnElement.classList.remove('btn-outline-primary');
        btnElement.classList.add('btn-primary');
    } else if (category === 'faq') {
        btnElement.classList.remove('btn-outline-danger');
        btnElement.classList.add('btn-danger');
    } else {
        btnElement.classList.remove('btn-outline-success');
        btnElement.classList.add('btn-success');
    }

    const cards = document.querySelectorAll('.panduan-card-guru');
    cards.forEach(card => {
        const cardCategories = (card.getAttribute('data-category') || '').toLowerCase();
        if (category === 'all' || cardCategories.includes(category)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// Buka / Tutup Semua Accordion Guru
function toggleAllAccordionsGuru(open) {
    const collapses = document.querySelectorAll('#accordionPanduanGuru .accordion-collapse');
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
