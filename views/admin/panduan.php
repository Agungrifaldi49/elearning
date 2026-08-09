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
                    <h4 class="fw-bold mb-1 text-dark">Panduan Pengguna & Manual Operasional System (Hak Akses Administrator)</h4>
                    <p class="text-muted small mb-0">Petunjuk teknis alur kerja terintegrasi, fungsi setiap menu, serta tata cara pengelolaan E-Learning SMK Muthia Harapan Cicalengka.</p>
                </div>
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchPanduan" class="form-control border-start-0 ps-0" placeholder="Cari topik panduan (misal: wali kelas, excel, sertifikat)..." onkeyup="filterPanduan()">
                </div>
            </div>
        </div>
    </div>

    <!-- Alur Kerja Utama Sistem (Diagram Step-by-Step) -->
    <div class="card-custom p-4 mb-4 shadow-sm">
        <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-diagram-3-fill me-2"></i>Alur Operasional Pengelolaan Sistem E-Learning</h6>
        <div class="row g-3">
            <div class="col-12 col-md-3">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative">
                    <span class="badge bg-primary mb-2">Langkah 1</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-database-fill-gear text-primary me-1"></i>Setup Master Data</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Pengaturan Profil Sekolah, Jurusan, Rombel Kelas, Penentuan Wali Kelas, serta Import Guru & Siswa via Excel.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative">
                    <span class="badge bg-success mb-2">Langkah 2</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-bounding-box-circles text-success me-1"></i>LMS & Pembelajaran</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Buat Kelas Virtual, Upload Modul/Video, Rancang Tugas Evaluasi, Kuis CBT, dan Kalender Akademik 1 Tahun.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative">
                    <span class="badge bg-warning text-dark mb-2">Langkah 3</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-award-fill text-warning me-1"></i>Penilaian & Sertifikasi</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Monitoring Presensi Realtime, Penilaian E-Rapor, serta Penerbitan Sertifikat Digital ber-Logo & QR Code.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative">
                    <span class="badge bg-info text-dark mb-2">Langkah 4</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-shield-check text-info me-1"></i>Monitoring & Keamanan</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Analitik LMS Database, Export PDF Laporan, Monitoring Audit Log Aktivitas/Login, dan Backup Database SQL.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modul-by-Modul Comprehensive Guides Accordion -->
    <div class="accordion" id="accordionPanduan">

        <!-- MODUL 1: DASHBOARD & RINGKASAN SISTEM -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card">
            <h2 class="accordion-header">
                <button class="accordion-button fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul1">
                    <i class="bi bi-grid-1x2-fill text-primary me-2 fs-5"></i> Modul 1: Dashboard & Ringkasan Sistem (`admin/dashboard`)
                </button>
            </h2>
            <div id="collapseModul1" class="accordion-collapse collapse show" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <h6 class="fw-bold text-dark"><i class="bi bi-info-circle text-primary me-1"></i>Fungsi Utama Menu</h6>
                    <p class="small text-muted mb-3">Pusat kendali eksekutif untuk memantau indikator kinerja utama (KPI) sekolah, aktivitas pembelajaran siswa 7 hari terakhir, serta log aktivitas real-time.</p>

                    <h6 class="fw-bold text-dark mt-3"><i class="bi bi-gear-wide-connected text-primary me-1"></i>Komponen & Cara Kerja:</h6>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2"><strong>4 Kartu Statistik Utama:</strong> Menampilkan total Guru Aktif, Siswa Terdaftar, Rombel Kelas, dan Mata Pelajaran yang langsung tersinkronisasi dengan database.</li>
                        <li class="mb-2"><strong>Grafik Aktivitas Pembelajaran:</strong> Visualisasi grafik garis (*Chart.js*) yang menampilkan tren akses siswa per hari (Senin - Minggu) selama 7 hari terakhir.</li>
                        <li class="mb-2"><strong>Ringkasan Log Aktivitas Terkini:</strong> Memantau 10 aktivitas pengguna terbaru yang melakukan login, perubahan data, atau pengerjaan tugas di sistem.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- MODUL 2: MASTER DATA -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul2">
                    <i class="bi bi-database-fill-gear text-success me-2 fs-5"></i> Modul 2: Master Data Pengguna & Akademik
                </button>
            </h2>
            <div id="collapseModul2" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-success"><i class="bi bi-person-badge-fill me-1"></i>1. Data Guru (`admin/guru`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1"><strong>CRUD Lengkap:</strong> Akses tombol <i>Detail</i>, <i>Edit</i>, dan <i>Hapus</i> di setiap baris tabel.</li>
                                    <li class="mb-1"><strong>Import Excel Rapi:</strong> Gunakan tombol <i>Import Excel Guru</i>. File CSV/Excel yang diunggah akan otomatis diproses per-kolom (NIP, Nama, Email, Jenis Kelamin, No Telp, Alamat).</li>
                                    <li class="mb-1"><strong>Unduh Template:</strong> Klik <i>Unduh Template Excel</i> untuk mendapatkan format `.csv` ber-BOM UTF-8 dan separator `;` yang otomatis terbuka rapi di Microsoft Excel.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-success"><i class="bi bi-people-fill me-1"></i>2. Data Siswa (`admin/siswa`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1"><strong>CRUD & Detail:</strong> Menampung data NIS, NISN, Nama, Kelas, Jurusan, Kontak, dan Akun Login Siswa.</li>
                                    <li class="mb-1"><strong>Filter Rombel Presisi:</strong> Saat membuka dari Kelas Virtual (*Lihat Anggota*), tabel akan menampilkan banner filter rombel khusus kelas tersebut.</li>
                                    <li class="mb-1"><strong>Import Massal:</strong> Fitur impor massal siswa via file Excel sesuai format template resmi sekolah.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-success"><i class="bi bi-journal-bookmark-fill me-1"></i>3. Kelas, Jurusan & Mapel (`admin/akademik`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1"><strong>Penentuan Wali Kelas:</strong> Pada form Tambah/Edit Kelas, Administrator dapat memilih Guru terdaftar sebagai Wali Kelas resmi.</li>
                                    <li class="mb-1"><strong>Jurusan & Tingkat:</strong> Mengatur kode jurusan (RPL, TKJ, DKV, dll) dan tingkat rombel (X, XI, XII).</li>
                                    <li class="mb-1"><strong>Mata Pelajaran:</strong> Menentukan kode mapel dan alokasi jurusan spesifik atau umum.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-success"><i class="bi bi-person-gear me-1"></i>4. Hak Akses & User (`admin/users`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-1"><strong>Manajemen Akun:</strong> Pengaturan username, email, password, dan status akun (Aktif/Blokir).</li>
                                    <li class="mb-1"><strong>Role RBAC:</strong> Penugasan peran sebagai <i>Administrator</i>, <i>Guru</i>, <i>Siswa</i>, atau <i>Kepala Sekolah</i>.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODUL 3: LEARNING MANAGEMENT SYSTEM (LMS) -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul3">
                    <i class="bi bi-bounding-box-circles text-warning me-2 fs-5"></i> Modul 3: Learning Management System (LMS)
                </button>
            </h2>
            <div id="collapseModul3" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3">
                                <h6 class="fw-bold text-dark"><i class="bi bi-bounding-box text-primary me-1"></i>Kelas Virtual (`admin/kelasVirtual`)</h6>
                                <p class="small text-muted mb-0">Daftar kartu rombel virtual lengkap dengan Kode Akses (`MH-XXXXXX`), Penentuan Wali Kelas via tombol <strong>Atur Wali Kelas</strong>, serta link <strong>Lihat Anggota Siswa</strong> per-rombel.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3">
                                <h6 class="fw-bold text-dark"><i class="bi bi-book-fill text-success me-1"></i>Modul & Materi Pembelajaran (`admin/materi`)</h6>
                                <p class="small text-muted mb-0">Upload materi pembelajaran berformat PDF, Video MP4, atau link YouTube yang dapat dialokasikan ke rombel kelas dan mata pelajaran tertentu.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3">
                                <h6 class="fw-bold text-dark"><i class="bi bi-card-checklist text-danger me-1"></i>Tugas & Evaluasi (`admin/tugas`)</h6>
                                <p class="small text-muted mb-0">Pengelolaan tugas penugasan siswa, batas waktu pengumpulan (*deadline*), rubrik penilaian, serta pengunduhan file berkas tugas siswa.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3">
                                <h6 class="fw-bold text-dark"><i class="bi bi-patch-question-fill text-info me-1"></i>Quiz & CBT Ujian (`admin/quiz`)</h6>
                                <p class="small text-muted mb-0">Pelaksanaan ujian online berbasis Komputer/CBT. Mendukung acak soal, timer durasi otomatis, penilaian instan pilihan ganda, dan rekap skor hasil ujian siswa.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3">
                                <h6 class="fw-bold text-dark"><i class="bi bi-award-fill text-warning me-1"></i>Template Sertifikat Digital (`admin/sertifikat`)</h6>
                                <p class="small text-muted mb-0">Pengaturan format sertifikat digital (Kelulusan LMS, Prestasi Akademik, Kompetensi UKK), Penerbitan Massal, dan Print Preview resmi dengan <strong>Logo Sekolah & QR Code pengesahan</strong>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODUL 4: PENILAIAN & PRESENSI -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul4">
                    <i class="bi bi-calendar-check-fill text-info me-2 fs-5"></i> Modul 4: Penilaian & Presensi E-Rapor
                </button>
            </h2>
            <div id="collapseModul4" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <p class="small text-muted mb-3">Pusat pencatatan kehadiran dan transkrip nilai akademik terpadu:</p>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2"><strong>Presensi Guru & Siswa (`admin/absensi`):</strong> Rekapitulasi kehadiran Harian (Hadir, Izin, Sakit, Alpa) dan pemindaian QR Code Presensi.</li>
                        <li class="mb-2"><strong>Rekap Nilai E-Rapor (`admin/inputNilai`):</strong> Penggabungan nilai harian tugas, skor kuis CBT, dan ujian akhir untuk pencetakan e-rapor semester.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- MODUL 5: KONTEN & KOMUNIKASI -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul5">
                    <i class="bi bi-chat-square-quote-fill text-danger me-2 fs-5"></i> Modul 5: Konten, Komunikasi & Kalender
                </button>
            </h2>
            <div id="collapseModul5" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-chat-square-text text-success me-1"></i>Forum Diskusi (`index.php?url=forum`)</h6>
                                <p class="small text-muted mb-0">Wadah diskusi interaktif antar guru dan siswa per mata pelajaran. Mendukung pembuatan topik baru, balasan komentar, dan indikator penyuka (*like*).</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-chat-dots text-primary me-1"></i>Chat Real-Time (`index.php?url=chat`)</h6>
                                <p class="small text-muted mb-0">Pesan percakapan pribadi langsung antar pengguna. Terintegrasi dengan <strong>Notifikasi Header Bel</strong> yang otomatis berkurang/hilang saat pesan dibaca.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-bookshelf text-info me-1"></i>Perpustakaan Digital (`index.php?url=library`)</h6>
                                <p class="small text-muted mb-0">Katalog e-book dan modul referensi digital. Dilengkapi fitur unggah file PDF, pencarian buku, pemfilteran kategori, serta statistik pembaca & pengunduh.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-calendar3 text-danger me-1"></i>Kalender Akademik (`admin/kalender`)</h6>
                                <p class="small text-muted mb-0">Kalender interaktif 1 Tahun Ajaran (`multiMonthYear`). Mendukung penambahan, pengubahan, dan penghapusan agenda Ujian CBT, Libur Nasional, & Event Sekolah.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODUL 6: LAPORAN & PENGATURAN -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModul6">
                    <i class="bi bi-graph-up-arrow text-primary me-2 fs-5"></i> Modul 6: Laporan, Audit Log & Keamanan
                </button>
            </h2>
            <div id="collapseModul6" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-graph-up-arrow text-primary me-1"></i>Laporan & Analitik LMS (`admin/laporan`)</h6>
                                <p class="small text-muted mb-2">Menyajikan grafik *Chart.js* aktivitas siswa real-time dan rekapitulasi kehadiran per-kelas. Dilengkapi tombol <strong>Export PDF Laporan Guru & Siswa</strong> ber-Kop & Logo Resmi Sekolah.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-shield-check text-danger me-1"></i>Audit Log & Keamanan (`admin/logs`)</h6>
                                <p class="small text-muted mb-2">Memantau catatan riwayat aktivitas dan sesi login (Sukses/Gagal). Mendukung filter level (INFO/WARNING/CRITICAL), <strong>Export CSV Per-Kolom Rapi</strong>, dan pembersihan log lama.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-gear-fill text-secondary me-1"></i>Pengaturan Profil Sekolah (`admin/pengaturan`)</h6>
                                <p class="small text-muted mb-2">Pengubahan nama sekolah, alamat, nama Kepala Sekolah, pengunggahan Logo Sekolah resmi, serta konfigurasi SMTP Email pengiriman notifikasi.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-database-fill-gear text-dark me-1"></i>Backup & Restore Database (`admin/backup`)</h6>
                                <p class="small text-muted mb-2">Fasilitas satu-klik untuk membuat file cadangan skema & data MySQL database (`.sql`) secara aman yang dapat diunduh sewaktu-waktu.</p>
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
function filterPanduan() {
    const query = document.getElementById('searchPanduan').value.toLowerCase();
    const cards = document.querySelectorAll('.panduan-card');

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
