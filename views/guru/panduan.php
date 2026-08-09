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
                    <h4 class="fw-bold mb-1 text-dark">Panduan Pengguna & Manual Mengajar Sistem (Hak Akses Guru)</h4>
                    <p class="text-muted small mb-0">Petunjuk lengkap alur KBM digital, Kode Akses (Key Mapel), restriksi pengampuan, tugas & rubrik, kuis CBT, presensi, jadwal KBM, dan Leger E-Rapor SMK MH Cicalengka.</p>
                </div>
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchPanduanGuru" class="form-control border-start-0 ps-0" placeholder="Cari petunjuk (misal: key, e-rapor, jadwal, cbt)..." onkeyup="filterPanduanGuru()">
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Alur Kerja Utama KBM Guru (Diagram Step-by-Step) -->
    <div class="card-custom p-4 mb-4 shadow-sm">
        <h6 class="fw-bold mb-3 text-success"><i class="bi bi-diagram-3-fill me-2"></i>Alur Utama KBM Digital & Evaluasi Guru</h6>
        <div class="row g-3">
            <div class="col-12 col-md-3">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative">
                    <span class="badge bg-primary mb-2">Langkah 1</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-key-fill text-primary me-1"></i>Key Mapel & Terdaftar</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Buat Kode Akses (Key Mapel) unik di <i>Kelas Virtual Saya</i> (`guru/kelasVirtual` -> Tab 2), bagikan ke siswa, & pantau daftar siswa terdaftar pada Tab 3.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative">
                    <span class="badge bg-success mb-2">Langkah 2</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-cloud-upload-fill text-success me-1"></i>Materi & Penugasan</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Unggah modul PDF/Video MP4/YouTube & buat Tugas Evaluasi. Pilihan Kelas & Mapel otomatis terkunci khusus pengampuan Anda.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative">
                    <span class="badge bg-warning text-dark mb-2">Langkah 3</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-patch-question-fill text-warning me-1"></i>Kuis CBT Multi-Soal</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Buat Kuis CBT Multi-Soal (Pilihan Ganda, Benar/Salah, Essay), atur timer durasi, acak urutan soal, & manfaatkan Bank Soal.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative">
                    <span class="badge bg-info text-dark mb-2">Langkah 4</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-pencil-fill text-info me-1"></i>Presensi & E-Rapor</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Periksa Pengingat KBM di Dashboard & Jadwal Mengajar, isi Presensi/Scan QR, serta lakukan pengisian Leger E-Rapor Batch Kelas.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Modul-by-Modul Comprehensive Guides Accordion -->
    <div class="accordion" id="accordionPanduanGuru">

        <!-- MODUL 1: DASHBOARD EXECUTIVE & PENGINGAT KBM -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru">
            <h2 class="accordion-header">
                <button class="accordion-button fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul1">
                    <i class="bi bi-grid-1x2-fill text-primary me-2 fs-5"></i> Modul 1: Dashboard Executive & Pengingat Sesi KBM Hari Ini (`guru/dashboard`)
                </button>
            </h2>
            <div id="collapseGuruModul1" class="accordion-collapse collapse show" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <h6 class="fw-bold text-dark"><i class="bi bi-info-circle text-primary me-1"></i>Fungsi Executive Dashboard Guru</h6>
                    <p class="small text-muted mb-3">Pusat pemantauan KBM digital secara realtime yang menyajikan statistik, grafik analitik, jadwal pengingat mengajar hari ini, serta pengumuman resmi dari Admin Sekolah.</p>

                    <div class="row g-3 mt-2">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary"><i class="bi bi-clock-history me-1"></i>1. Widget Pengingat Sesi KBM Hari Ini</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-2"><strong>Mendeteksi Hari Berjalan Otomatis:</strong> Menampilkan sesi mengajar Guru pada hari aktif (Senin – Sabtu).</li>
                                    <li class="mb-2"><strong>Rincian Sesi Lengkap:</strong> Menampilkan Jam KBM (WIB), Mata Pelajaran, Rombel Kelas Target, dan Ruangan KBM / Lab Komputer.</li>
                                    <li class="mb-2"><strong>Tombol Pintas Jadwal:</strong> Akses 1-klik menuju halaman jadwal mengajar mingguan lengkap.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-primary"><i class="bi bi-bar-chart-line-fill me-1"></i>2. Grafik Analitik Realtime & Informasi Admin</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-2"><strong>Grafik Batang KBM:</strong> Menampilkan jumlah realtime Materi, Tugas, Quiz, Key Mapel, dan Siswa Terdaftar milik Guru.</li>
                                    <li class="mb-2"><strong>Grafik Donat Distribusi Siswa:</strong> Visualisasi proporsi jumlah siswa terdaftar per-mata pelajaran yang diampu.</li>
                                    <li class="mb-2"><strong>Informasi Resmi Admin:</strong> Menampilkan pengumuman penting yang diterbitkan Administrator Sekolah.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODUL 2: KELAS VIRTUAL, KEY MAPEL & DATA SISWA TERDAFTAR -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul2">
                    <i class="bi bi-bounding-box-circles text-success me-2 fs-5"></i> Modul 2: Kelas Virtual, Kode Akses (Key Mapel), & Data Siswa Terdaftar (`guru/kelasVirtual`)
                </button>
            </h2>
            <div id="collapseGuruModul2" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <p class="small text-muted mb-3">Modul ini dibagi menjadi 3 Tab Navigasi Utama untuk kerapihan dan kenyamanan Guru:</p>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-success"><i class="bi bi-building me-1"></i>Tab 1: Rombel Kelas Virtual Saya</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-2"><strong>Kartu Rombel Kelas:</strong> Menampilkan daftar kelas yang diampu oleh Guru.</li>
                                    <li class="mb-2"><strong>Badge Status Wali Kelas:</strong> Menampilkan penanda <span class="badge bg-success">Saya Wali Kelas</span> apabila Guru bertindak sebagai Wali Kelas.</li>
                                    <li class="mb-2"><strong>Akses Pintas:</strong> Tombol navigasi langsung ke Presensi, Upload Materi, dan Tugas Kelas.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-warning text-dark"><i class="bi bi-key-fill me-1"></i>Tab 2: Kode Akses (Key Mapel)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-2"><strong>Pembuatan Key Mapel:</strong> Guru wajib membuat Passcode / Key unik per-Mata Pelajaran agar siswa dapat mendaftar.</li>
                                    <li class="mb-2"><strong>Kelola & Edit Key:</strong> Guru dapat memperbarui Key Mapel atau menonaktifkan pendaftaran mapel kapan saja.</li>
                                    <li class="mb-2"><strong>Fungsi Kunci Pembelajaran:</strong> Siswa yang belum mendaftar Key Mapel tidak dapat membuka materi, tugas, dan kuis.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-info"><i class="bi bi-people-fill me-1"></i>Tab 3: Data Siswa Terdaftar Mapel Saya</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-2"><strong>Monitoring Realtime:</strong> Menampilkan seluruh siswa yang berhasil mendaftar menggunakan Key Mapel Guru.</li>
                                    <li class="mb-2"><strong>Filter Terisolasi Pengampuan:</strong> Dropdown Mapel & Kelas otomatis menyaring hanya mapel dan kelas yang diajar Guru.</li>
                                    <li class="mb-2"><strong>Fitur Pencarian:</strong> Cari siswa berdasarkan Nama, NISN, Kelas, maupun Jurusan (RPL, TKJ, DKP, dll.).</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODUL 3: MODUL & MATERI PEMBELAJARAN -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul3">
                    <i class="bi bi-book-fill text-warning me-2 fs-5"></i> Modul 3: Upload Materi & Video Pembelajaran (`guru/materi`)
                </button>
            </h2>
            <div id="collapseGuruModul3" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <div class="p-3 bg-light rounded-4 border mb-3">
                        <h6 class="fw-bold text-dark"><i class="bi bi-shield-check text-success me-1"></i>Restriksi Pengampuan & Format Berkas</h6>
                        <ul class="small text-muted ps-3 mb-0">
                            <li class="mb-2"><strong>Dropdown Terisolasi:</strong> Saat menekan tombol <strong>Upload Materi Baru</strong>, dropdown Mata Pelajaran & Kelas Target <strong>hanya menyajikan mapel & kelas yang diajar oleh Guru tersebut</strong>.</li>
                            <li class="mb-2"><strong>Format yang Didukung:</strong> Dokumen PDF, Word (DOCX), PowerPoint (PPTX), Excel (XLSX), Gambar, Video MP4, dan Tautan Embed YouTube.</li>
                            <li class="mb-2"><strong>Fitur Pratinjau (Preview):</strong> Seluruh dokumen PDF, Video MP4, dan Tautan YouTube dapat diputar/dibaca langsung oleh siswa dalam modal pratinjau tanpa wajib mengunduh file.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODUL 4: PENUGASAN & RUBRIK EVALUATION -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul4">
                    <i class="bi bi-card-checklist text-danger me-2 fs-5"></i> Modul 4: Kelola Penugasan & Rubrik Evaluation (`guru/tugas`)
                </button>
            </h2>
            <div id="collapseGuruModul4" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-danger"><i class="bi bi-plus-circle me-1"></i>1. Membuat Tugas Baru</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-2"><strong>Restriksi Kelas & Mapel:</strong> Dropdown pilihan Mata Pelajaran dan Kelas Target disesuaikan secara presisi dengan tugas mengajar Guru.</li>
                                    <li class="mb-2"><strong>Pengaturan Deadline:</strong> Tentukan tanggal & jam batas akhir pengumpulan tugas secara presisi.</li>
                                    <li class="mb-2"><strong>Lampiran Soal:</strong> Opsional untuk melampirkan berkas PDF/DOCX petunjuk soal.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-danger"><i class="bi bi-award me-1"></i>2. Penilaian & Umpan Balik Siswa</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-2"><strong>Tombol "Nilai Siswa":</strong> Membuka modal pengumpulan tugas siswa untuk memeriksa file kiriman.</li>
                                    <li class="mb-2"><strong>Pemberian Nilai & Komentar:</strong> Input skor nilai (0 – 100) serta berikan umpan balik / komentar evaluasi untuk siswa.</li>
                                    <li class="mb-2"><strong>Integrasi E-Rapor:</strong> Nilai tugas siswa secara otomatis siap digunakan pada komponen nilai E-Rapor.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODUL 5: QUIZ, UJIAN CBT & BANK SOAL -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul5">
                    <i class="bi bi-patch-question-fill text-warning me-2 fs-5"></i> Modul 5: Quiz, Ujian Online CBT & Bank Soal (`guru/quiz` & `guru/bankSoal`)
                </button>
            </h2>
            <div id="collapseGuruModul5" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-stopwatch text-warning me-1"></i>1. Pembuatan Ujian CBT Online (`guru/quiz`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-2"><strong>3 Tipe Soal:</strong> Pilihan Ganda (PG), Benar / Salah (True/False), dan Essay / Uraian.</li>
                                    <li class="mb-2"><strong>Fitur Pengacakan (Randomizer):</strong> Opsi acak nomor soal (`random_soal`) & acak urutan pilihan jawaban (`random_jawaban`).</li>
                                    <li class="mb-2"><strong>Pengaturan Durasi:</strong> Mengatur batas waktu pengerjaan ujian dalam menit.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 bg-light h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-database text-info me-1"></i>2. Bank Soal & Analisis (`guru/bankSoal`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-2"><strong>Penyimpanan Soal Terpusat:</strong> Seluruh butir soal yang dibuat tersimpan rapi dalam Bank Soal Guru.</li>
                                    <li class="mb-2"><strong>Penggunaan Ulang (Reuse):</strong> Soal dari Bank Soal dapat digunakan kembali untuk paket ujian CBT di semester berikutnya.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODUL 6: JADWAL MENGAJAR SAYA -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul6">
                    <i class="bi bi-clock-history text-warning me-2 fs-5"></i> Modul 6: Jadwal Mengajar Saya (`guru/jadwal`)
                </button>
            </h2>
            <div id="collapseGuruModul6" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <div class="p-3 border rounded-4 bg-light">
                        <h6 class="fw-bold text-dark"><i class="bi bi-calendar3 text-warning me-1"></i>Fitur Jadwal KBM Read-Only Guru</h6>
                        <ul class="small text-muted ps-3 mb-0">
                            <li class="mb-2"><strong>Pengelompokan Hari KBM:</strong> Menampilkan jadwal mengajar Guru yang tersusun rapi dari hari <strong>Senin, Selasa, Rabu, Kamis, Jumat, hingga Sabtu</strong>.</li>
                            <li class="mb-2"><strong>Penanda Hari Ini:</strong> Hari mengajar berjalan secara otomatis ditandai dengan badge hijau <span class="badge bg-success">Hari Ini ✔</span>.</li>
                            <li class="mb-2"><strong>Rincian Sesi KBM:</strong> Jam Mulai - Jam Selesai KBM (WIB), Nama Mata Pelajaran, Rombel Kelas Target, dan Ruangan KBM / Lab Komputer.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODUL 7: LEGER E-RAPOR, PRESENSI & SCAN QR -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-guru">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuruModul7">
                    <i class="bi bi-pencil-fill text-info me-2 fs-5"></i> Modul 7: Leger E-Rapor, Presensi Siswa & Scan QR Code (`guru/inputNilai`, `guru/absensi`, `guru/scanQr`)
                </button>
            </h2>
            <div id="collapseGuruModul7" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanGuru">
                <div class="accordion-body bg-white p-4">
                    <div class="p-3 border rounded-4 bg-light mb-3">
                        <h6 class="fw-bold text-info"><i class="bi bi-pencil-fill me-1"></i>Leger E-Rapor Batch & Kalkulasi Otomatis (`guru/inputNilai`)</h6>
                        <ul class="small text-muted ps-3 mb-0">
                            <li class="mb-2"><strong>Restriksi Filter Kelas & Mapel:</strong> Dropdown pilihan Rombel Kelas dan Mata Pelajaran disesuaikan secara presisi hanya untuk kelas & mapel yang diajar Guru.</li>
                            <li class="mb-2"><strong>Bobot Perhitungan Rapor:</strong> Tugas (20%), Quiz (20%), UTS (30%), dan UAS (30%). Mengisi angka nilai langsung mengkalkulasi Nilai Akhir & Predikat (A/B/C/D) secara otomatis.</li>
                            <li class="mb-2"><strong>Batch Save 1-Klik:</strong> Simpan seluruh nilai siswa dalam 1 rombel kelas sekaligus dengan menekan <strong>Simpan Seluruh E-Rapor Kelas Ini</strong>.</li>
                        </ul>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-calendar-check text-success me-1"></i>Presensi Siswa (`guru/absensi`)</h6>
                                <p class="small text-muted mb-0">Pencatatan status kehadiran harian siswa (Hadir, Izin, Sakit, Alpa) per-sesi jadwal KBM Guru.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-4 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-qr-code-scan text-primary me-1"></i>Scan QR Code Hadir (`guru/scanQr`)</h6>
                                <p class="small text-muted mb-0">Pemindaian QR Code pada Kartu Pelajar Digital siswa menggunakan kamera HP/Laptop untuk presensi otomatis instan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
</main>

<script>
function filterPanduanGuru() {
    const query = document.getElementById('searchPanduanGuru').value.toLowerCase();
    const cards = document.querySelectorAll('.panduan-card-guru');

    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(query)) {
            card.style.display = '';
            const collapse = card.querySelector('.accordion-collapse');
            if (collapse && query.length > 2) {
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapse);
                bsCollapse.show();
            }
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
