<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-2 px-md-4 py-3">
<div class="container-fluid px-1 px-md-2">

    <!-- 1. Hero Header Banner -->
    <div class="card-custom p-3 p-md-4 mb-4 border-start border-4 border-md-5 border-warning shadow-sm bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="hero-icon-box bg-warning-subtle text-warning text-dark rounded-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px;">
                    <i class="bi bi-shield-shaded fs-2"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h4 class="fw-bold mb-0 text-dark page-title">Buku Panduan Pengguna & Manual Eksekutif</h4>
                        <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill small"><i class="bi bi-person-badge me-1"></i>Hak Akses Kepala Sekolah</span>
                    </div>
                    <p class="text-muted small mb-0 page-subtitle">Petunjuk komprehensif pengawasan akademik, monitoring perangkat ajar (CP/TP/KKTP), presensi GPS, supervisi KBM, analitik E-Rapor, dan pelaporan eksekutif.</p>
                </div>
            </div>
            <div class="col-12 col-md-5 col-lg-4 mt-2 mt-md-0">
                <div class="input-group search-input-group shadow-sm rounded-pill overflow-hidden border">
                    <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-warning"></i></span>
                    <input type="text" id="searchPanduanKepsek" class="form-control border-0 ps-2 small" placeholder="Cari topik panduan (misal: guru, cp tp, supervisi, cbt, spp)..." onkeyup="filterPanduanKepsek()">
                </div>
            </div>
        </div>

        <!-- Category Filter Pills (Horizontal swipe on mobile) -->
        <div class="d-flex gap-2 mt-3 pt-2 border-top overflow-x-auto flex-nowrap no-scrollbar pb-1" id="categoryFilterContainerKepsek">
            <button class="btn btn-sm btn-primary rounded-pill px-3 py-1 flex-shrink-0 cat-btn-kepsek active" onclick="filterKategoriKepsek('all', this)"><i class="bi bi-grid-fill me-1"></i>Semua Modul</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-kepsek" onclick="filterKategoriKepsek('dashboard', this)"><i class="bi bi-speedometer2 me-1"></i>Dashboard</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-kepsek" onclick="filterKategoriKepsek('guru', this)"><i class="bi bi-person-check-fill me-1"></i>Supervisi Guru</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-kepsek" onclick="filterKategoriKepsek('perangkat', this)"><i class="bi bi-diagram-3-fill me-1"></i>Perangkat Ajar (CP/TP)</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-kepsek" onclick="filterKategoriKepsek('kbm', this)"><i class="bi bi-display-fill me-1"></i>KBM & Modul</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-kepsek" onclick="filterKategoriKepsek('evaluasi', this)"><i class="bi bi-patch-question-fill me-1"></i>Tugas & CBT</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-kepsek" onclick="filterKategoriKepsek('presensi', this)"><i class="bi bi-calendar-check-fill me-1"></i>Presensi Terpadu</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-kepsek" onclick="filterKategoriKepsek('rapor', this)"><i class="bi bi-journal-check me-1"></i>Leger E-Rapor</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-kepsek" onclick="filterKategoriKepsek('spp', this)"><i class="bi bi-wallet2 me-1"></i>Keuangan SPP</button>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 flex-shrink-0 cat-btn-kepsek" onclick="filterKategoriKepsek('faq', this)"><i class="bi bi-question-circle-fill me-1"></i>FAQ Kepsek</button>
        </div>
    </div>

    <!-- 2. Alur Supervisi & Pengawasan Eksekutif (5-Stage Visual Roadmap) -->
    <div class="card-custom roadmap-container p-3 p-md-4 mb-4 shadow-sm bg-white">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-warning text-dark fs-6"><i class="bi bi-diagram-3-fill me-2 text-warning"></i>Alur Supervisi & Manajemen Kepemimpinan Sekolah</h6>
            <span class="badge bg-warning-subtle text-dark border border-warning px-2.5 py-1 small"><i class="bi bi-shield-lock-fill me-1"></i>5 Siklus Penjaminan Mutu</span>
        </div>
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-primary">
                    <span class="badge bg-primary mb-2.5 px-2.5 py-1">Tahap 1</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-speedometer2 text-primary me-1.5"></i>Dashboard & KPI</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Pantau metrik makro: keaktifan KBM harian, rasio kehadiran guru-siswa, & tren rata-rata capaian akademik sekolah.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-warning">
                    <span class="badge bg-warning text-dark mb-2.5 px-2.5 py-1">Tahap 2</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-diagram-3-fill text-warning me-1.5"></i>Supervisi CP/TP</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Periksa matriks kelengkapan Perangkat Ajar Kurikulum Merdeka (CP, TP, KKTP) & laksanakan Supervisi Pembelajaran Guru.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-info">
                    <span class="badge bg-info text-dark mb-2.5 px-2.5 py-1">Tahap 3</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-laptop text-info me-1.5"></i>Monitoring KBM</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Supervisi materi ajar PDF/YouTube guru, jadwal KBM, distribusi tugas siswa, serta kesiapan bank soal & CBT online.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-success">
                    <span class="badge bg-success mb-2.5 px-2.5 py-1">Tahap 4</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-geo-alt-fill text-success me-1.5"></i>Presensi Terpadu</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Awasi absensi selfie GPS geofencing guru tepat waktu & scan QR kehadiran siswa di gerbang serta kelas harian.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="roadmap-step-card bg-light rounded-4 border h-100 position-relative border-start border-4 border-danger">
                    <span class="badge bg-danger mb-2.5 px-2.5 py-1">Tahap 5</span>
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-file-earmark-pdf-fill text-danger me-1.5"></i>E-Rapor & Keuangan</h6>
                    <p class="text-muted small mb-0 lh-base" style="font-size:0.82rem;">Verifikasi transkrip Leger E-Rapor akhir semester, pantau arus kas iuran SPP, dan terbitkan Laporan Eksekutif Resmi.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Modul-by-Modul Comprehensive Guides Accordion -->
    <div class="accordion" id="accordionPanduanKepsek">

        <!-- =========================================================================
             MODUL 1: DASHBOARD EKSEKUTIF & RINGKASAN KPI
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-kepsek" data-category="dashboard">
            <h2 class="accordion-header">
                <button class="accordion-button fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKepsekModul1">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-primary-subtle text-primary">
                            <i class="bi bi-grid-1x2-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-primary text-white px-2 py-0.5 rounded-pill small">Modul 1</span>
                                <span class="fw-bold text-dark modul-title-text">Dashboard Eksekutif & Ringkasan KPI Sekolah</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">4 Metrik KPI Utama, Tren Keaktifan Guru & Sebaran Siswa</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">kepsek/dashboard</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseKepsekModul1" class="accordion-collapse collapse show" data-bs-parent="#accordionPanduanKepsek">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="bi bi-link-45deg me-1"></i>Navigasi: Dashboard Executive</span>
                        <span class="badge bg-light text-muted border px-2 py-1">URL: index.php?url=kepsek/dashboard</span>
                    </div>

                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle text-primary me-1"></i>Pusat Kendali Pengawasan Tingkat Tinggi</h6>
                    <p class="small text-muted mb-3">Dashboard Eksekutif menyajikan indikator kinerja kunci (KPI) operasional sekolah secara otomatis langsung dari database tanpa rekayasa data:</p>

                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-primary small mb-1"><i class="bi bi-person-badge-fill me-1"></i>Guru & Produktivitas</h6>
                                <p class="small text-muted mb-0">Total Guru Aktif disertai total modul materi dan tugas yang telah dipublikasikan ke rombel.</p>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-success small mb-1"><i class="bi bi-people-fill me-1"></i>Siswa & Rombel</h6>
                                <p class="small text-muted mb-0">Total peserta didik aktif yang terdistribusi pada seluruh rombongan belajar sekolah.</p>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-warning small mb-1"><i class="bi bi-award-fill me-1"></i>Rata-Rata Capaian Nilai</h6>
                                <p class="small text-muted mb-0">Indikator rata-rata mutu nilai akhir siswa dari tugas harian, kuis formatif, STS, dan SAS.</p>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-info small mb-1"><i class="bi bi-calendar-check-fill me-1"></i>Tingkat Kehadiran KBM</h6>
                                <p class="small text-muted mb-0">Persentase kehadiran presensi harian siswa yang terverifikasi di gerbang dan kelas.</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 mb-0 small">
                        <i class="bi bi-lightbulb-fill text-warning me-1"></i><strong>Tips Kepala Sekolah:</strong> Manfaatkan tombol <strong>Cetak Laporan PDF</strong> di pojok kanan atas beranda untuk mengunduh resume resmi pengawasan sekolah ber-kop surat resmi.
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 2: MONITORING PRODUKTIVITAS & PRESENSI SELFIE GURU
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-kepsek" data-category="guru">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKepsekModul2">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-success-subtle text-success">
                            <i class="bi bi-person-check-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-success text-white px-2 py-0.5 rounded-pill small">Modul 2</span>
                                <span class="fw-bold text-dark modul-title-text">Monitoring Produktivitas & Presensi Selfie Guru</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Absensi Geofencing GPS Guru Hari Ini & Rekap Matrix Bulanan</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">kepsek/monitoringGuru</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseKepsekModul2" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanKepsek">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-success mb-2"><i class="bi bi-camera-fill me-1"></i>1. Presensi Selfie GPS Guru (`kepsek/presensiGuru`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5"><strong>Pantauan Realtime:</strong> Mengetahui daftar guru yang telah hadir hari ini lengkap dengan foto selfie, jam masuk, status (Tepat Waktu / Terlambat), dan koordinat jarak meter dari gerbang sekolah.</li>
                                    <li class="mb-1.5"><strong>Validasi Geofencing:</strong> Memastikan seluruh presensi pendidik dilakukan di dalam radius gedung sekolah resmi.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-graph-up-arrow me-1"></i>2. Produktivitas Mengajar Guru (`kepsek/monitoringGuru`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1.5"><strong>Distribusi Modul & Tugas:</strong> Memantau berapa banyak materi PDF, video KBM, dan paket kuis CBT yang telah diproduksi oleh tiap guru.</li>
                                    <li class="mb-1.5"><strong>Riwayat Nilai PKG:</strong> Menampilkan skor supervisi akademik terakhir guru bersangkutan.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 3: SUPERVISI AKADEMIK & KINERJA GURU (PKG)
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-kepsek" data-category="guru">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKepsekModul3">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-warning-subtle text-warning text-dark">
                            <i class="bi bi-award-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-warning text-dark px-2 py-0.5 rounded-pill small">Modul 3</span>
                                <span class="fw-bold text-dark modul-title-text">Supervisi Akademik & Kinerja Pengajar (PKG)</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Rubrik Supervisi Pembelajaran di Kelas & Rekomendasi Kepsek</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">kepsek/supervisiGuru</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseKepsekModul3" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanKepsek">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-clipboard2-check-fill text-warning me-1"></i>Instrumen Penilaian Kinerja Guru Terpadu</h6>
                    <p class="small text-muted mb-3">Kepala Sekolah dapat melakukan observasi KBM kelas secara berkala menggunakan instrumen rubrik digital terstandar:</p>

                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-primary small d-block mb-1"><i class="bi bi-1-circle me-1"></i>Perencanaan KBM (25%)</strong>
                                <p class="small text-muted mb-0">Kesiapan modul ajar, perumusan CP-TP, kejelasan asesmen KKTP, dan media pembelajaran.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-success small d-block mb-1"><i class="bi bi-2-circle me-1"></i>Pelaksanaan KBM (50%)</strong>
                                <p class="small text-muted mb-0">Penguasaan materi kejuruan, interaksi siswa di kelas, manajemen waktu, dan penggunaan IT.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-warning text-dark small d-block mb-1"><i class="bi bi-3-circle me-1"></i>Evaluasi & Tindak Lanjut (25%)</strong>
                                <p class="small text-muted mb-0">Umpan balik tugas, analisis hasil kuis CBT, dan catatan rekomendasi pembinaan dari Kepsek.</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning border-0 rounded-3 mb-0 small">
                        <i class="bi bi-check-circle-fill text-success me-1"></i>Hasil supervisi menghasilkan skor akhir (0-100), predikat kinerja (*Amat Baik / Baik / Cukup*), serta lembaran cetak Berita Acara Supervisi Akademik.
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 4: MONITORING PERANGKAT AJAR (CP, TP & KKTP GURU)
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-kepsek" data-category="perangkat">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKepsekModul4">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-primary-subtle text-primary">
                            <i class="bi bi-diagram-3-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-primary text-white px-2 py-0.5 rounded-pill small">Modul 4</span>
                                <span class="fw-bold text-dark modul-title-text">Monitoring Perangkat Ajar (CP, TP & KKTP Guru)</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Supervisi Keterisian Elemen CP, Target TP & Ambang KKTP Guru</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">kepsek/monitoringPerangkatAjar</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseKepsekModul4" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanKepsek">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill small"><i class="bi bi-star-fill me-1"></i>Fitur Unggulan Kurikulum Merdeka</span>
                        <span class="badge bg-light text-muted border px-2 py-1">Menu: Pembelajaran &gt; Monitoring CP, TP & KKTP Guru</span>
                    </div>

                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-eye-fill text-primary me-1"></i>Pengawasan Rinci Administrasi Pembelajaran Guru</h6>
                    <p class="small text-muted mb-3">Fitur ini memungkinkan Kepala Sekolah memantau secara transparan apakah seluruh dewan guru telah menyusun perangkat kurikulum merdeka secara lengkap atau masih ada yang belum tuntas:</p>

                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-primary small mb-1"><i class="bi bi-diagram-2 me-1"></i>Capaian Pembelajaran (CP)</h6>
                                <p class="small text-muted mb-0">Menghitung jumlah CP yang dirumuskan per elemen kejuruan, fase E (Kelas X) atau fase F (Kelas XI & XII), beserta uraian kompetensinya.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-info small mb-1"><i class="bi bi-bullseye me-1"></i>Tujuan Pembelajaran (TP)</h6>
                                <p class="small text-muted mb-0">Memeriksa butir-butir target ketercapaian siswa per-materi pokok yang ditautkan langsung di bawah setiap Capaian Pembelajaran.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-success small mb-1"><i class="bi bi-card-checklist me-1"></i>Kriteria Ketercapaian (KKTP)</h6>
                                <p class="small text-muted mb-0">Memverifikasi ambang batas nilai tuntas (misal: 75.00), metode rubrik/interval, serta rincian indikator penilaian per-TP.</p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-white h-100">
                                <strong class="text-dark small d-block mb-1"><i class="bi bi-funnel-fill text-warning me-1"></i>Matriks Status Keterisian:</strong>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li><span class="badge bg-success">Lengkap</span> : Guru telah menginputkan CP, TP, dan KKTP secara tuntas.</li>
                                    <li><span class="badge bg-warning text-dark">Sebagian</span> : Guru sudah mengisi CP/TP namun belum mengatur KKTP.</li>
                                    <li><span class="badge bg-danger">Belum Ada</span> : Guru sama sekali belum mengisi CP di sistem.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-white h-100">
                                <strong class="text-dark small d-block mb-1"><i class="bi bi-folder2-open text-primary me-1"></i>Inspeksi Rincian Drill-Down:</strong>
                                <p class="small text-muted mb-0">Klik tombol <strong>Rincian CP/TP</strong> pada baris nama guru untuk membuka pohon hirarki CP &rarr; TP &rarr; KKTP dan membaca teks deskripsi yang disusun oleh guru tersebut.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 5: MONITORING PEMBELAJARAN ROMBEL, MODUL & VIDEO
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-kepsek" data-category="kbm">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKepsekModul5">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-info-subtle text-info">
                            <i class="bi bi-display-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-info text-dark px-2 py-0.5 rounded-pill small">Modul 5</span>
                                <span class="fw-bold text-dark modul-title-text">Monitoring Pembelajaran Rombel, Modul & Video</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Supervisi Rombel Virtual, Materi PDF/YouTube & Perpus Digital</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">kepsek/monitoringPembelajaran</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseKepsekModul5" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanKepsek">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-building me-1"></i>1. Monitoring Rombel Virtual (`kepsek/monitoringPembelajaran`)</h6>
                                <p class="small text-muted mb-0">Melihat daftar seluruh rombel kelas sekolah, rasio siswa terdaftar, pengajar pengampu, serta tingkat aktivitas kelas digital secara menyeluruh.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-info mb-2"><i class="bi bi-book-half me-1"></i>2. Modul & Bahan Ajar Guru (`kepsek/monitoringMateri`)</h6>
                                <p class="small text-muted mb-0">Inspeksi berkas bahan ajar: modul PDF kejuruan, video MP4, sematan video YouTube, serta pemanfaatan buku perpustakaan digital sekolah.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 6: MONITORING EVALUASI, TUGAS SISWA & UJIAN CBT
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-kepsek" data-category="evaluasi">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKepsekModul6">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-danger-subtle text-danger">
                            <i class="bi bi-patch-question-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-danger text-white px-2 py-0.5 rounded-pill small">Modul 6</span>
                                <span class="fw-bold text-dark modul-title-text">Monitoring Evaluasi, Tugas Siswa & Ujian CBT</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Distribusi Tugas Berbobot & Pelaksanaan Ujian CBT Anti-Curang</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">kepsek/monitoringQuiz</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseKepsekModul6" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanKepsek">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-card-checklist me-1"></i>1. Supervisi Penugasan (`kepsek/monitoringTugas`)</h6>
                                <p class="small text-muted mb-0">Melihat daftar tugas yang diberikan guru kepada siswa, persentase pengumpulan tepat waktu, dan rata-rata nilai koreksi per rombel kelas.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-danger mb-2"><i class="bi bi-laptop me-1"></i>2. Pengawasan Ujian CBT (`kepsek/monitoringQuiz`)</h6>
                                <p class="small text-muted mb-0">Memantau kuis dan ujian tengah/akhir semester (PTS/PAS). Mengetahui paket soal yang aktif, timer durasi, serta log sistem anti-curang ujian.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 7: KESISWAAN, PRESENSI GERBANG & REKAP BULANAN
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-kepsek" data-category="presensi">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKepsekModul7">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-success-subtle text-success">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-success text-white px-2 py-0.5 rounded-pill small">Modul 7</span>
                                <span class="fw-bold text-dark modul-title-text">Kesiswaan, Presensi Gerbang & Rekap Bulanan</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Kartu Pelajar QR Code, Presensi Scan Gerbang & Matriks Bulanan</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">kepsek/presensiSiswa</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseKepsekModul7" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanKepsek">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-success mb-2"><i class="bi bi-qr-code-scan me-1"></i>1. Presensi Scan QR Gerbang (`kepsek/presensiSiswa`)</h6>
                                <p class="small text-muted mb-0">Memantau lalu lintas kedatangan siswa di gerbang sekolah melalui pemindaian QR kartu pelajar dan memverifikasi pengiriman notifikasi WhatsApp otomatis ke orang tua.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-table me-1"></i>2. Rekap Matriks 1-31 Hari (`kepsek/recapBulanan`)</h6>
                                <p class="small text-muted mb-0">Tabel absensi bulanan lengkap: Hadir (H), Izin (I), Sakit (S), Alpa (A) untuk dewan guru dan seluruh rombel kelas siswa dengan fitur cetak PDF.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 8: SUPERVISI LEGER NILAI & E-RAPOR DIGITAL
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-kepsek" data-category="rapor">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKepsekModul8">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-primary-subtle text-primary">
                            <i class="bi bi-journal-check fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-primary text-white px-2 py-0.5 rounded-pill small">Modul 8</span>
                                <span class="fw-bold text-dark modul-title-text">Supervisi Leger Nilai & E-Rapor Digital</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Rekapitulasi Nilai Akhir Rapor, Predikat Siswa & Cetak PDF</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">kepsek/monitoringNilai</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseKepsekModul8" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanKepsek">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <p class="small text-muted mb-3">Kepala Sekolah memiliki wewenang memvalidasi lembaran E-Rapor sebelum dibagikan kepada orang tua siswa:</p>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-1.5"><strong>Komposisi 4 Bobot:</strong> Memeriksa keakuratan perhitungan otomatis: Tugas (20%), Kuis/Formatif (20%), STS (30%), dan SAS (30%).</li>
                        <li class="mb-1.5"><strong>Distribusi Predikat:</strong> Evaluasi perolehan predikat siswa (A, B, C, D) di setiap rombel kelas binaan.</li>
                        <li class="mb-1.5"><strong>Pengesahan Rapor:</strong> Lembaran E-Rapor digital terkoneksi otomatis dengan nama resmi dan NIP Kepala Sekolah sebagai penandatangan sah.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 9: MONITORING PORTAL KEUANGAN SPP & EKSTRAKURIKULER
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-kepsek" data-category="spp">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKepsekModul9">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-success-subtle text-success">
                            <i class="bi bi-wallet2 fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-success text-white px-2 py-0.5 rounded-pill small">Modul 9</span>
                                <span class="fw-bold text-dark modul-title-text">Monitoring Portal Keuangan SPP & Ekstrakurikuler</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Realisasi Penerimaan Iuran, Status Tunggakan & Laporan PDF</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">kepsek/pembayaran</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseKepsekModul9" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanKepsek">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-success mb-2"><i class="bi bi-cash-coin me-1"></i>1. Pengawasan Keuangan SPP (`kepsek/pembayaran`)</h6>
                                <p class="small text-muted mb-0">Mengetahui total realisasi penerimaan iuran, sisa piutang/tunggakan per-tingkat kelas, serta kuitansi resmi bendahara sekolah dengan tombol cetak PDF laporan keuangan eksekutif.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-activity me-1"></i>2. Kegiatan Ekstrakurikuler</h6>
                                <p class="small text-muted mb-0">Memantau pendaftaran anggota ekskul (Pramuka, IT Club, Robotik, Futsal, dll.), pembina yang bertugas, serta rekapan nilai ekskul siswa di E-Rapor.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             MODUL 10: KOMUNIKASI EKSEKUTIF, MAKLUMAT & FAQ KEPSEK
        ========================================================================= -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-kepsek" data-category="faq">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold py-3 px-3 px-md-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKepsekModul10">
                    <div class="d-flex align-items-center gap-2.5 gap-md-3 w-100 me-2 text-start">
                        <div class="modul-icon-box bg-danger-subtle text-danger">
                            <i class="bi bi-question-circle-fill fs-5"></i>
                        </div>
                        <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <span class="badge bg-danger text-white px-2 py-0.5 rounded-pill small">Modul 10</span>
                                <span class="fw-bold text-dark modul-title-text">Komunikasi Eksekutif, Maklumat & FAQ Kepsek</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <small class="text-muted modul-desc-text">Live Meeting Daring, Surat Edaran & Solusi Kendala Pengawasan</small>
                                <code class="modul-route-tag bg-light border px-2 py-0.5 rounded small text-secondary font-monospace d-none d-md-inline-block">kepsek/liveClass</code>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapseKepsekModul10" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanKepsek">
                <div class="accordion-body bg-white p-3 p-md-4">
                    <!-- Fitur Eksekutif -->
                    <div class="row g-2 g-md-3 mb-3 mb-md-4">
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-danger"><i class="bi bi-camera-reels-fill me-1"></i>Live Virtual Meeting (`kepsek/liveClass`)</h6>
                                <p class="small text-muted mb-0">Ruang tatap muka video langsung untuk rapat dewan guru bulanan atau pengarahan resmi tanpa perlu install Zoom pihak ketiga.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-primary"><i class="bi bi-megaphone-fill me-1"></i>Maklumat Resmi Sekolah (`kepsek/pengumuman`)</h6>
                                <p class="small text-muted mb-0">Menerbitkan surat edaran penting langsung ke beranda akun dewan guru dan akun seluruh siswa terdaftar.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-info"><i class="bi bi-calendar3 me-1"></i>Kalender Akademik (`kepsek/kalender`)</h6>
                                <p class="small text-muted mb-0">Kalender agenda tahunan sekolah: jadwal ujian PTS/PAS, libur semester, dan agenda pembinaan dewan guru.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Kendala Eksekutif -->
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-question-diamond-fill text-danger me-1"></i>Tanya Jawab Kendala Pengawasan Sering Dihadapi Kepala Sekolah:</h6>
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-primary small d-block mb-1">Bagaimana cara mengetahui guru yang belum menginputkan CP atau TP?</strong>
                                <p class="small text-muted mb-0">Buka menu <code>kepsek/monitoringPerangkatAjar</code>, pilih filter status <strong>Belum Mengisi</strong> atau <strong>Sebagian</strong>. Sistem akan menampilkan daftar guru terkait dan Anda dapat langsung memberikan arahan pembinaan.</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <strong class="text-primary small d-block mb-1">Presensi guru ditandai di luar radius geofencing sekolah?</strong>
                                <p class="small text-muted mb-0">Buka menu <code>kepsek/presensiGuru</code> untuk melihat koordinat lintang/bujur dan foto selfie guru. Jika lokasi guru valid di lingkungan sekolah, koordinasikan dengan Admin untuk memperluas radius meter pada pengaturan geofencing sekolah.</p>
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
    background-color: rgba(255, 193, 7, 0.12);
    color: #000000;
    box-shadow: none;
}
.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(0, 0, 0, 0.125);
}
.cat-btn-kepsek {
    transition: all 0.2s ease-in-out;
    font-size: 0.82rem;
    padding: 0.35rem 0.75rem;
}
.cat-btn-kepsek.active {
    box-shadow: 0 2px 6px rgba(255, 193, 7, 0.4);
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
    .app-sidebar, .app-header, .btn, #categoryFilterContainerKepsek, #searchPanduanKepsek, .input-group {
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
    .panduan-card-kepsek {
        page-break-inside: avoid;
        margin-bottom: 1.5rem !important;
    }
}
</style>

<script>
// Filter Pencarian Teks Real-Time untuk Kepsek
function filterPanduanKepsek() {
    const query = document.getElementById('searchPanduanKepsek').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.panduan-card-kepsek');

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

// Filter Kategori Modul untuk Kepsek
function filterKategoriKepsek(cat, btn) {
    document.querySelectorAll('.cat-btn-kepsek').forEach(b => {
        b.classList.remove('active', 'btn-primary');
        b.classList.add('btn-light');
    });
    btn.classList.add('active', 'btn-primary');
    btn.classList.remove('btn-light');

    const cards = document.querySelectorAll('.panduan-card-kepsek');
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
