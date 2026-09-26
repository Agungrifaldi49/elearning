<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-2 px-sm-3 px-md-4 py-3">
<div class="container-fluid max-w-1200">

    <!-- 1. Compact Hero Header (Mobile-First) -->
    <div class="card border-0 rounded-4 shadow-sm bg-white p-3 p-md-4 mb-3 border-start border-4 border-success">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded-3 p-2.5 p-md-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-book-half fs-4"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                        <h5 class="fw-bold mb-0 text-dark">Panduan Mengajar & KBM Guru</h5>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 0.7rem;">Hak Akses Guru</span>
                    </div>
                    <p class="text-muted small mb-0 d-none d-sm-block" style="font-size: 0.8rem;">
                        Manual praktis KBM digital, Key Mapel, CBT anti-curang, Presensi Selfie GPS, Leger E-Rapor, & Wali Kelas.
                    </p>
                </div>
            </div>

            <!-- Single Quick Action Dropdown + Print (Minimal & Rapi) -->
            <div class="d-flex align-items-center gap-2 ms-auto ms-md-0">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-sliders2 me-1"></i>Opsi Tampilan
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 small">
                        <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="toggleAllAccordionsGuru(true)"><i class="bi bi-arrows-expand me-2 text-primary"></i>Buka Semua Topik</a></li>
                        <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="toggleAllAccordionsGuru(false)"><i class="bi bi-arrows-collapse me-2 text-secondary"></i>Tutup Semua Topik</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="window.print()"><i class="bi bi-printer me-2 text-dark"></i>Cetak / Simpan PDF</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Live Search Bar -->
        <div class="mt-3">
            <div class="input-group search-input-group">
                <span class="input-group-text bg-light border-end-0 text-success ps-3"><i class="bi bi-search"></i></span>
                <input type="text" id="searchPanduanGuru" class="form-control border-start-0 bg-light py-2" placeholder="Cari topik (misal: key mapel, gps, cbt, e-rapor, wali kelas)..." onkeyup="filterPanduanGuru()">
                <button class="btn btn-light border-start-0 text-muted pe-3" type="button" onclick="resetSearchGuru()" title="Reset"><i class="bi bi-x-circle"></i></button>
            </div>
        </div>

        <!-- Horizontal Swipeable Filter Chips (Clean, No-Wrap on Mobile) -->
        <div class="mt-2.5 pt-2 border-top">
            <div class="d-flex gap-1.5 overflow-x-auto no-scrollbar py-1" id="categoryFilterContainerGuru">
                <button class="btn btn-sm rounded-pill btn-success active cat-btn-guru text-nowrap flex-shrink-0" onclick="filterByCategoryGuru('all', this)">
                    <i class="bi bi-collection me-1"></i>Semua
                </button>
                <button class="btn btn-sm rounded-pill btn-light text-secondary border cat-btn-guru text-nowrap flex-shrink-0" onclick="filterByCategoryGuru('key', this)">
                    <i class="bi bi-key-fill text-warning me-1"></i>Key & Materi
                </button>
                <button class="btn btn-sm rounded-pill btn-light text-secondary border cat-btn-guru text-nowrap flex-shrink-0" onclick="filterByCategoryGuru('evaluasi', this)">
                    <i class="bi bi-patch-question-fill text-danger me-1"></i>Tugas & CBT
                </button>
                <button class="btn btn-sm rounded-pill btn-light text-secondary border cat-btn-guru text-nowrap flex-shrink-0" onclick="filterByCategoryGuru('presensi', this)">
                    <i class="bi bi-camera-fill text-success me-1"></i>Presensi GPS & QR
                </button>
                <button class="btn btn-sm rounded-pill btn-light text-secondary border cat-btn-guru text-nowrap flex-shrink-0" onclick="filterByCategoryGuru('rapor', this)">
                    <i class="bi bi-pencil-fill text-info me-1"></i>E-Rapor & CP-TP
                </button>
                <button class="btn btn-sm rounded-pill btn-light text-secondary border cat-btn-guru text-nowrap flex-shrink-0" onclick="filterByCategoryGuru('walikelas', this)">
                    <i class="bi bi-person-workspace text-primary me-1"></i>Wali Kelas
                </button>
                <button class="btn btn-sm rounded-pill btn-light text-secondary border cat-btn-guru text-nowrap flex-shrink-0" onclick="filterByCategoryGuru('faq', this)">
                    <i class="bi bi-question-circle text-danger me-1"></i>FAQ
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Alur Mengajar KBM (Swipeable Horizontal Step Strip on Mobile) -->
    <div class="card border-0 rounded-4 shadow-sm bg-white p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-success fw-bold small"><i class="bi bi-diagram-3-fill me-1.5"></i>Alur Mengajar KBM Digital</span>
            <small class="text-muted d-none d-md-inline" style="font-size:0.75rem;">5 Langkah Mudah Mengajar</small>
        </div>
        <div class="d-flex gap-2 overflow-x-auto no-scrollbar pb-1">
            <div class="step-chip flex-shrink-0 p-2 rounded-3 bg-light border text-nowrap">
                <span class="badge bg-primary me-1">1</span>
                <span class="small fw-semibold text-dark">Key Mapel & CP-TP</span>
            </div>
            <div class="step-chip flex-shrink-0 p-2 rounded-3 bg-light border text-nowrap">
                <span class="badge bg-success me-1">2</span>
                <span class="small fw-semibold text-dark">Materi & Learning Path</span>
            </div>
            <div class="step-chip flex-shrink-0 p-2 rounded-3 bg-light border text-nowrap">
                <span class="badge bg-warning text-dark me-1">3</span>
                <span class="small fw-semibold text-dark">Tugas & CBT Ujian</span>
            </div>
            <div class="step-chip flex-shrink-0 p-2 rounded-3 bg-light border text-nowrap">
                <span class="badge bg-info text-dark me-1">4</span>
                <span class="small fw-semibold text-dark">Presensi GPS & Scan QR</span>
            </div>
            <div class="step-chip flex-shrink-0 p-2 rounded-3 bg-light border text-nowrap">
                <span class="badge bg-danger me-1">5</span>
                <span class="small fw-semibold text-dark">Leger E-Rapor & Wali</span>
            </div>
        </div>
    </div>

    <!-- 3. Accordion Modul Panduan Guru (Ergonomic Touch Targets) -->
    <div class="accordion" id="accordionPanduanGuru">

        <!-- =========================================================================
             MODUL 1: DASHBOARD MENGAJAR
        ========================================================================= -->
        <div class="card border-0 rounded-4 shadow-sm mb-2.5 overflow-hidden panduan-card-guru" data-category="key">
            <div class="card-header bg-white border-0 p-0">
                <button class="accordion-button fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul1">
                    <div class="d-flex align-items-center gap-2.5 text-start w-100">
                        <div class="rounded-3 bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-grid-1x2-fill fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-dark d-block text-truncate">Modul 1: Dashboard Mengajar & Pengingat Sesi</span>
                            <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">Statistik KBM, Jadwal Sesi Hari Ini, dan Jadwal Sholat</small>
                        </div>
                    </div>
                </button>
            </div>
            <div id="collapseGuruModul1" class="accordion-collapse collapse show" data-bs-parent="#accordionPanduanGuru">
                <div class="card-body p-3 p-md-4 pt-1">
                    <p class="small text-muted mb-3">Pusat pemantauan tugas mengajar harian Anda. Begitu login, periksa informasi prioritas berikut:</p>
                    
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-primary small mb-1"><i class="bi bi-clock-history me-1"></i>Sesi Mengajar Hari Ini</h6>
                                <p class="text-muted small mb-0" style="font-size:0.8rem;">Mendeteksi hari aktif otomatis (Senin-Sabtu) lengkap dengan Jam KBM (WIB), Mapel, Rombel Target, dan Ruang Kelas/Lab.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-success small mb-1"><i class="bi bi-bar-chart-line-fill me-1"></i>Statistik Realtime KBM</h6>
                                <p class="text-muted small mb-0" style="font-size:0.8rem;">Memantau jumlah Materi Aktif, Tugas Berjalan, Kuis CBT, dan Siswa Terdaftar di kelas Anda.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-warning text-dark small mb-1"><i class="bi bi-moon-stars-fill me-1"></i>Waktu Sholat Sekolah</h6>
                                <p class="text-muted small mb-0" style="font-size:0.8rem;">Jadwal waktu ibadah terintegrasi sesuai titik lokasi sekolah untuk mendukung pembiasaan religi siswa.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-info small mb-1"><i class="bi bi-megaphone-fill me-1"></i>Pengumuman Resmi</h6>
                                <p class="text-muted small mb-0" style="font-size:0.8rem;">Surat edaran atau instruksi penting dari Kepala Sekolah dan Administrator.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 2: KELAS VIRTUAL & KEY MAPEL
        ========================================================================= -->
        <div class="card border-0 rounded-4 shadow-sm mb-2.5 overflow-hidden panduan-card-guru" data-category="key">
            <div class="card-header bg-white border-0 p-0">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul2">
                    <div class="d-flex align-items-center gap-2.5 text-start w-100">
                        <div class="rounded-3 bg-warning bg-opacity-10 text-warning text-dark p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-key-fill fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-dark d-block text-truncate">Modul 2: Kelas Virtual & Kode Akses (Key Mapel)</span>
                            <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">Pembuatan Passcode, Proteksi KBM, dan Siswa Terdaftar</small>
                        </div>
                    </div>
                </button>
            </div>
            <div id="collapseGuruModul2" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="card-body p-3 p-md-4 pt-1">
                    <p class="small text-muted mb-3">Modul ini terletak pada menu <strong>Kelas Virtual Saya (`guru/kelasVirtual`)</strong> yang memiliki 3 tab terpisah:</p>

                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-4">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <span class="badge bg-primary mb-1.5">Tab 1: Rombel Saya</span>
                                <h6 class="fw-bold text-dark small mb-1">Daftar Kelas yang Diampu</h6>
                                <p class="text-muted small mb-0" style="font-size:0.78rem;">Kartu rombel kelas yang diajar Guru. Dilengkapi penanda khusus jika Anda ditugaskan sebagai <strong>Wali Kelas</strong>.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <span class="badge bg-warning text-dark mb-1.5">Tab 2: Key Mapel</span>
                                <h6 class="fw-bold text-dark small mb-1">Passcode Pendaftaran Siswa</h6>
                                <p class="text-muted small mb-0" style="font-size:0.78rem;"><strong>Wajib Dibuat:</strong> Buat kunci unik per-mapel (contoh: <code>RPL-WEB-2026</code>). Siswa yang belum join tidak bisa melihat materi & ujian.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <span class="badge bg-success mb-1.5">Tab 3: Siswa Terdaftar</span>
                                <h6 class="fw-bold text-dark small mb-1">Monitoring Siswa Realtime</h6>
                                <p class="text-muted small mb-0" style="font-size:0.78rem;">Melihat daftar siswa yang berhasil memasukkan Key Mapel Anda, dilengkapi pencarian nama & NISN.</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success border-0 rounded-3 mt-3 mb-0 p-2.5 small" style="font-size: 0.8rem;">
                        <i class="bi bi-lightbulb-fill text-warning me-1"></i><strong>Tips Praktis:</strong> Bagikan Key Mapel saat sesi tatap muka pertama agar seluruh siswa serentak terdaftar di kelas digital Anda.
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 3: MATERI, VIDEO & LEARNING PATH
        ========================================================================= -->
        <div class="card border-0 rounded-4 shadow-sm mb-2.5 overflow-hidden panduan-card-guru" data-category="key">
            <div class="card-header bg-white border-0 p-0">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul3">
                    <div class="d-flex align-items-center gap-2.5 text-start w-100">
                        <div class="rounded-3 bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-book-fill fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-dark d-block text-truncate">Modul 3: Materi Multimedia & Urutan Learning Path</span>
                            <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">Upload Modul PDF, Video MP4, YouTube, dan Alur Belajar</small>
                        </div>
                    </div>
                </button>
            </div>
            <div id="collapseGuruModul3" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="card-body p-3 p-md-4 pt-1">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-success small mb-1"><i class="bi bi-cloud-upload me-1"></i>Unggah Modul & Video (`guru/materi`)</h6>
                                <ul class="small text-muted ps-3 mb-0" style="font-size:0.8rem;">
                                    <li class="mb-1"><strong>Otomatis Terkunci:</strong> Pilihan kelas & mapel hanya sesuai tugas mengajar Anda.</li>
                                    <li class="mb-1"><strong>Format Berkas:</strong> Mendukung PDF, Word, PowerPoint, Excel, Gambar, dan Video MP4.</li>
                                    <li class="mb-1"><strong>Player YouTube:</strong> Cukup tempel tautan video YouTube, siswa dapat menonton langsung di aplikasi tanpa iklan luar.</li>
                                    <li class="mb-1"><strong>Modal Pratinjau:</strong> Berkas PDF dapat dibaca langsung tanpa wajib download.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-primary small mb-1"><i class="bi bi-compass me-1"></i>Learning Path Terstruktur (`guru/learningPath`)</h6>
                                <ul class="small text-muted ps-3 mb-0" style="font-size:0.8rem;">
                                    <li class="mb-1">Menyusun urutan tahapan belajar bab-demi-bab (*Tahap 1 &gt; Tahap 2 &gt; Evaluasi*).</li>
                                    <li class="mb-1">Pengaturan prasyarat (*prerequisite*) agar siswa tidak melompat sebelum memahami bab dasar.</li>
                                    <li class="mb-1">Pemanfaatan koleksi <strong>Perpustakaan Digital (`library`)</strong> untuk referensi e-book kejuruan.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 4: PENUGASAN, RUBRIK & GAME EDUKASI
        ========================================================================= -->
        <div class="card border-0 rounded-4 shadow-sm mb-2.5 overflow-hidden panduan-card-guru" data-category="evaluasi">
            <div class="card-header bg-white border-0 p-0">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul4">
                    <div class="d-flex align-items-center gap-2.5 text-start w-100">
                        <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-card-checklist fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-dark d-block text-truncate">Modul 4: Kelola Penugasan, Rubrik & Game Edukasi</span>
                            <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">Deadline, Periksa Kiriman Siswa, dan Gamifikasi</small>
                        </div>
                    </div>
                </button>
            </div>
            <div id="collapseGuruModul4" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="card-body p-3 p-md-4 pt-1">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-danger small mb-1"><i class="bi bi-pencil-square me-1"></i>1. Membuat Tugas & Deadline</h6>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Tentukan instruksi penugasan, lampiran berkas soal, batas tanggal & jam akhir (*deadline*). Pengumpulan yang lewat batas waktu akan ditandai terlambat oleh sistem.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-success small mb-1"><i class="bi bi-award me-1"></i>2. Nilai Siswa & Umpan Balik</h6>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Klik tombol <strong>Nilai Siswa</strong> untuk memeriksa file kiriman tugas, input nilai skor (0-100), dan berikan komentar evaluasi yang otomatis tersinkron ke komponen E-Rapor.</p>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border">
                                <h6 class="fw-bold text-dark small mb-1"><i class="bi bi-controller text-warning me-1"></i>3. Game Edukasi Kejuruan (`game`)</h6>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Gunakan game edukasi interaktif sebagai variasi *ice breaking* di sela-sela KBM untuk melatih kecepatan daya tangkap siswa SMK.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 5: KUIS, UJIAN CBT ANTI-CURANG & BANK SOAL
        ========================================================================= -->
        <div class="card border-0 rounded-4 shadow-sm mb-2.5 overflow-hidden panduan-card-guru" data-category="evaluasi">
            <div class="card-header bg-white border-0 p-0">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul5">
                    <div class="d-flex align-items-center gap-2.5 text-start w-100">
                        <div class="rounded-3 bg-warning bg-opacity-10 text-dark p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-patch-question-fill fs-5 text-warning"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-dark d-block text-truncate">Modul 5: Kuis, Ujian CBT Anti-Curang & Bank Soal</span>
                            <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">Pengacakan Ganda, Timer Hitung Mundur, dan Bank Soal</small>
                        </div>
                    </div>
                </button>
            </div>
            <div id="collapseGuruModul5" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="card-body p-3 p-md-4 pt-1">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-dark small mb-1"><i class="bi bi-shield-check text-success me-1"></i>Fitur Unggulan CBT Ujian (`guru/quiz`)</h6>
                                <ul class="small text-muted ps-3 mb-0" style="font-size:0.8rem;">
                                    <li class="mb-1"><strong>3 Tipe Soal:</strong> Pilihan Ganda (PG), Benar/Salah, dan Essay.</li>
                                    <li class="mb-1"><strong>Pengacakan Ganda:</strong> Acak urutan butir soal & opsi jawaban (A/B/C/D/E) agar siswa tidak saling contek.</li>
                                    <li class="mb-1"><strong>Timer & Auto-Submit:</strong> Jawaban otomatis terkirim saat waktu habis.</li>
                                    <li class="mb-1"><strong>Deteksi Anti-Curang:</strong> Peringatan otomatis jika siswa berganti aplikasi atau membuka tab browser lain.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-dark small mb-1"><i class="bi bi-database text-info me-1"></i>Bank Soal & Analisis (`guru/bankSoal`)</h6>
                                <ul class="small text-muted ps-3 mb-0" style="font-size:0.8rem;">
                                    <li class="mb-1"><strong>Repositori Terpusat:</strong> Seluruh soal yang disusun tersimpan di Bank Soal.</li>
                                    <li class="mb-1"><strong>Daur Ulang Soal:</strong> Butir soal dapat diimpor langsung saat membuat paket ujian kelas lain.</li>
                                    <li class="mb-1"><strong>Koreksi Instan:</strong> Nilai pilihan ganda otomatis terhitung dan menghasilkan peringkat nilai secara instan.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 6: PRESENSI SELFIE GPS, SCAN QR & JADWAL
        ========================================================================= -->
        <div class="card border-0 rounded-4 shadow-sm mb-2.5 overflow-hidden panduan-card-guru" data-category="presensi">
            <div class="card-header bg-white border-0 p-0">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul6">
                    <div class="d-flex align-items-center gap-2.5 text-start w-100">
                        <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-camera-fill fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-dark d-block text-truncate">Modul 6: Presensi Selfie GPS, Scan QR & Jadwal Mengajar</span>
                            <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">Absen Mandiri GPS Sekolah, Scanner QR Kartu Siswa di Kelas</small>
                        </div>
                    </div>
                </button>
            </div>
            <div id="collapseGuruModul6" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="card-body p-3 p-md-4 pt-1">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-danger small mb-1"><i class="bi bi-geo-alt-fill me-1"></i>Presensi Selfie Guru GPS (`guru/presensiGuru`)</h6>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Buka menu ini lewat smartphone saat tiba di sekolah. Izinkan akses Kamera dan Lokasi GPS. Sistem akan memverifikasi apakah Anda berada di dalam radius sekolah sebelum mengizinkan pengiriman absensi Masuk/Pulang.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-success small mb-1"><i class="bi bi-qr-code-scan me-1"></i>Scan QR Kartu Siswa (`guru/scanQr`)</h6>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Gunakan kamera HP saat jam pelajaran untuk memindai QR Code pada Kartu Pelajar siswa. Sekali arahkan kamera, nama siswa dan status "Hadir" seketika tercatat dengan bunyi <i>beep</i>.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-primary small mb-1"><i class="bi bi-calendar-check me-1"></i>Presensi Siswa & Rekap Matrix (`guru/absensi`)</h6>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Pencatatan status Hadir, Izin, Sakit, Alpa per pertemuan. Menu <strong>Rekap Bulanan (`guru/recapBulanan`)</strong> menyajikan matriks tanggal 1-31 hari dan persentase kehadiran rombel.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-warning text-dark small mb-1"><i class="bi bi-clock-history me-1"></i>Jadwal Mengajar Saya (`guru/jadwal`)</h6>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Rincian jadwal mengajar mingguan (Senin-Sabtu) dengan tanda <span class="badge bg-success">Hari Ini ✔</span> pada sesi yang sedang aktif berjalan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 7: CP-TP, ASESMEN & LEGER E-RAPOR BATCH
        ========================================================================= -->
        <div class="card border-0 rounded-4 shadow-sm mb-2.5 overflow-hidden panduan-card-guru" data-category="rapor">
            <div class="card-header bg-white border-0 p-0">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul7">
                    <div class="d-flex align-items-center gap-2.5 text-start w-100">
                        <div class="rounded-3 bg-info bg-opacity-10 text-info p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-pencil-fill fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-dark d-block text-truncate">Modul 7: CP-TP, Asesmen & Leger E-Rapor Batch 1-Klik</span>
                            <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">Kurikulum Merdeka dan Pengisian Nilai Rombel Cepat</small>
                        </div>
                    </div>
                </button>
            </div>
            <div id="collapseGuruModul7" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="card-body p-3 p-md-4 pt-1">
                    <div class="row g-2 g-md-3 mb-2">
                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-primary small mb-1"><i class="bi bi-diagram-3 me-1"></i>Penyusunan CP & TP (`guru/cptp`)</h6>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Penyusunan Capaian Pembelajaran (CP) dan Tujuan Pembelajaran (TP) sesuai Kurikulum Merdeka Fase E (Kelas X) & Fase F (Kelas XI-XII SMK).</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-danger small mb-1"><i class="bi bi-bullseye me-1"></i>Asesmen & KKTP (`guru/asesmen`)</h6>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Penentuan Kriteria Ketercapaian Tujuan Pembelajaran dan kategori asesmen diagnostik, formatif, dan sumatif.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Leger E-Rapor Batch Highlight -->
                    <div class="p-3 rounded-3 bg-light border">
                        <h6 class="fw-bold text-success small mb-1.5"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Leger E-Rapor Batch (`guru/inputNilai`)</h6>
                        <p class="small text-muted mb-2" style="font-size:0.8rem;">Tabel pengisian nilai rombel otomatis dengan kalkulasi Nilai Akhir & Predikat (A/B/C/D) instan:</p>
                        
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <span class="badge bg-white text-primary border">Tugas (20%)</span>
                            <span class="badge bg-white text-success border">Formatif (20%)</span>
                            <span class="badge bg-white text-warning text-dark border">STS (30%)</span>
                            <span class="badge bg-white text-danger border">SAS (30%)</span>
                        </div>

                        <p class="small text-muted mb-0" style="font-size:0.8rem;">
                            <i class="bi bi-check2-circle text-success me-1"></i><strong>Batch Save:</strong> Cukup klik <strong>Simpan Seluruh E-Rapor Kelas Ini</strong> untuk menyimpan satu rombel sekaligus dalam hitungan detik.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 8: PERAN WALI KELAS & WHATSAPP ORANG TUA
        ========================================================================= -->
        <div class="card border-0 rounded-4 shadow-sm mb-2.5 overflow-hidden panduan-card-guru" data-category="walikelas">
            <div class="card-header bg-white border-0 p-0">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul8">
                    <div class="d-flex align-items-center gap-2.5 text-start w-100">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-person-workspace fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-dark d-block text-truncate">Modul 8: Peran Khusus Wali Kelas & Kontak WhatsApp Ortu</span>
                            <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">Kelas Binaan, Kontak Ortu WA, dan Cetak Rapor Massal</small>
                        </div>
                    </div>
                </button>
            </div>
            <div id="collapseGuruModul8" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="card-body p-3 p-md-4 pt-1">
                    <p class="small text-muted mb-2.5">Fitur ini otomatis aktif jika Anda ditugaskan sebagai <strong>Wali Kelas</strong> oleh Admin:</p>

                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-primary small mb-1"><i class="bi bi-easel me-1"></i>Monitoring Rombel Binaan (`guru/waliKelas`)</h6>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Pantau total siswa, rata-rata nilai kelas, dan persentase kehadiran. Tersedia tombol <strong>Cetak E-Rapor Sekaligus</strong> untuk mencetak buku rapor satu kelas dalam 1 berkas PDF.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-success small mb-1"><i class="bi bi-whatsapp me-1"></i>Hubungi WhatsApp Orang Tua Siswa</h6>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Tabel kelas binaan memuat Nomor HP Orang Tua/Wali (`no_ortu`). Klik tombol WhatsApp untuk chat langsung dengan orang tua perihal absensi atau perkembangan anak tanpa simpan kontak manual.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-warning text-dark small mb-1"><i class="bi bi-trophy me-1"></i>Leger & Ranking Siswa (`guru/rankingKelas`)</h6>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Peringkat siswa 1 s/d terakhir berdasarkan kalkulasi nilai akumulasi E-Rapor seluruh mata pelajaran.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-danger small mb-1"><i class="bi bi-activity me-1"></i>Bimbingan Ekskul (`guru/ekstrakurikuler`)</h6>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Khusus guru pembina ekstrakurikuler untuk mendata kehadiran latihan dan kartu anggota ekskul.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 9: KOMUNIKASI & LIVE CLASS
        ========================================================================= -->
        <div class="card border-0 rounded-4 shadow-sm mb-2.5 overflow-hidden panduan-card-guru" data-category="key">
            <div class="card-header bg-white border-0 p-0">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul9">
                    <div class="d-flex align-items-center gap-2.5 text-start w-100">
                        <div class="rounded-3 bg-secondary bg-opacity-10 text-secondary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-chat-dots-fill fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-dark d-block text-truncate">Modul 9: Live Class, Forum & Chat Interaktif</span>
                            <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">Tatap Muka Video Daring, Diskusi Mapel, dan Kartu Guru</small>
                        </div>
                    </div>
                </button>
            </div>
            <div id="collapseGuruModul9" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="card-body p-3 p-md-4 pt-1">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-4">
                            <div class="p-2.5 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-danger small mb-1"><i class="bi bi-camera-reels me-1"></i>Live Virtual Class (`guru/liveClass`)</h6>
                                <p class="small text-muted mb-0" style="font-size:0.78rem;">Tatap muka video online langsung di web tanpa perlu aplikasi luar.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-2.5 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-primary small mb-1"><i class="bi bi-chat-square-quote me-1"></i>Forum & Chat Siswa (`forum`)</h6>
                                <p class="small text-muted mb-0" style="font-size:0.78rem;">Ruang diskusi tanya jawab materi dan konsultasi tugas harian.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-2.5 rounded-3 bg-light border h-100">
                                <h6 class="fw-bold text-warning text-dark small mb-1"><i class="bi bi-person-badge me-1"></i>Kartu Guru Digital (`guru/kartuGuru`)</h6>
                                <p class="small text-muted mb-0" style="font-size:0.78rem;">Kartu identitas GTK ber-QR Code resmi untuk presensi GTK.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 10: FAQ KENDALA MENGAJAR
        ========================================================================= -->
        <div class="card border-0 rounded-4 shadow-sm mb-2.5 overflow-hidden panduan-card-guru" data-category="faq">
            <div class="card-header bg-white border-0 p-0">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul10">
                    <div class="d-flex align-items-center gap-2.5 text-start w-100">
                        <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                            <i class="bi bi-question-circle-fill fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-dark d-block text-truncate">Modul 10: Tanya Jawab Kendala Mengajar (FAQ)</span>
                            <small class="text-muted fw-normal d-block" style="font-size: 0.75rem;">Solusi Cepat untuk Masalah GPS, Key Mapel, dan Ujian Terkunci</small>
                        </div>
                    </div>
                </button>
            </div>
            <div id="collapseGuruModul10" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="card-body p-3 p-md-4 pt-1">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <strong class="text-danger small d-block mb-1">Presensi Selfie GPS "Di luar radius"?</strong>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Aktifkan mode "Akurasi Tinggi" pada GPS HP. Buka Google Maps sejenak agar sinyal lokasi mengunci, lalu refresh halaman presensi. Jika masih gagal, mintalah Admin menaikkan radius toleransi geofencing.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <strong class="text-primary small d-block mb-1">Siswa tidak bisa melihat materi/tugas saya?</strong>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Pastikan siswa telah memasukkan <strong>Kode Akses (Key Mapel)</strong> Anda di menu <i>Gabung Kelas</i> pada akun siswa. Cek daftar siswa terdaftar di Tab 3 pada menu <i>guru/kelasVirtual</i>.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <strong class="text-warning text-dark small d-block mb-1">Siswa terkunci saat ujian CBT?</strong>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Fitur anti-curang mendeteksi siswa membuka tab lain atau berpindah aplikasi. Guru pengawas dapat mereset sesi pengerjaan siswa pada tabel pemantauan kuis ujian.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-2.5 p-md-3 rounded-3 bg-light border h-100">
                                <strong class="text-success small d-block mb-1">Nilai tugas belum muncul di E-Rapor?</strong>
                                <p class="small text-muted mb-0" style="font-size:0.8rem;">Buka menu <i>guru/inputNilai</i>, pilih kelas & mapel, periksa nilai yang ditarik, lalu klik tombol hijau <strong>Simpan Seluruh E-Rapor Kelas Ini</strong>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Help & Support Footer Banner (Mobile Compact) -->
    <div class="card border-0 rounded-4 shadow-sm bg-success bg-opacity-10 text-success-emphasis p-3 p-md-4 mt-3">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success text-white rounded-3 p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-headset fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0.5 text-dark" style="font-size: 0.95rem;">Bantuan Mengajar & Kendala Sistem</h6>
                    <p class="small text-muted mb-0" style="font-size: 0.78rem;">Hubungi Tim IT Administrator sekolah jika membutuhkan penyesuaian jadwal mengajar atau akun siswa.</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>index.php?url=guru/dashboard" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold align-self-start align-self-sm-center text-nowrap">
                <i class="bi bi-speedometer2 me-1"></i>Ke Dashboard
            </a>
        </div>
    </div>

</div>
</main>

<style>
/* Desain Mobile-First, Bersih, Ergonomis, & Ringan */
.max-w-1200 {
    max-width: 1200px;
    margin: 0 auto;
}
.search-input-group .form-control:focus {
    box-shadow: none;
    background-color: #ffffff;
    border-color: #198754;
}
.search-input-group {
    border-radius: 50rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

/* Horizontal Chip Navigation on Mobile */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.cat-btn-guru {
    font-size: 0.78rem;
    padding: 0.35rem 0.85rem;
    transition: all 0.15s ease-in-out;
}
.cat-btn-guru.active {
    background-color: #198754 !important;
    color: #ffffff !important;
    border-color: #198754 !important;
    box-shadow: 0 2px 6px rgba(25, 135, 84, 0.25);
}

/* Step Chip */
.step-chip {
    font-size: 0.78rem;
}

/* Accordion Touch Ergonomics */
.accordion-button {
    background-color: #ffffff;
    border: none !important;
    box-shadow: none !important;
}
.accordion-button:not(.collapsed) {
    background-color: rgba(25, 135, 84, 0.05);
    color: #198754;
}
.accordion-button:focus {
    box-shadow: none;
}
.accordion-button::after {
    background-size: 0.9rem;
}

/* Print Friendly */
@media print {
    .app-sidebar, .app-header, .btn, #categoryFilterContainerGuru, .search-input-group, .dropdown {
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
    .card {
        border: 1px solid #ddd !important;
        page-break-inside: avoid;
        margin-bottom: 1rem !important;
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

// Filter Berdasarkan Kategori untuk Guru (Minimalis)
function filterByCategoryGuru(category, btnElement) {
    // Reset all buttons to inactive style
    document.querySelectorAll('.cat-btn-guru').forEach(btn => {
        btn.classList.remove('active', 'btn-success');
        btn.classList.add('btn-light', 'text-secondary', 'border');
    });

    // Set active button
    btnElement.classList.remove('btn-light', 'text-secondary', 'border');
    btnElement.classList.add('active', 'btn-success');

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
