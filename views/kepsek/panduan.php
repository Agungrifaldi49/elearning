<?php require_once ROOT_PATH . 'views/layouts/header.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/navbar.php'; ?>
<?php require_once ROOT_PATH . 'views/layouts/sidebar.php'; ?>

<main class="main-content px-3 px-md-4">
    <div class="container-fluid">

        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bi bi-book-half text-warning me-2"></i>Buku Panduan Pengguna & Manual Eksekutif (Hak Akses Kepala Sekolah)
                </h4>
                <p class="text-muted small mb-0">Petunjuk komprehensif alur kerja pengawasan akademik, supervisi pengajar, monitoring KBM, & pencetakan laporan eksekutif.</p>
            </div>
            <div class="position-relative" style="min-width: 280px;">
                <input type="text" id="searchPanduanKepsek" class="form-control ps-5 shadow-sm rounded-pill" placeholder="Cari topik panduan eksekutif...">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            </div>
        </div>

        <!-- Workflow Diagram Card (System Operational Flow) -->
        <div class="card card-custom p-4 mb-4 shadow-sm border-0 rounded-4">
            <h6 class="fw-bold text-dark mb-3">
                <i class="bi bi-diagram-3-fill text-primary me-2"></i>Alur Sistem Pengawasan & Supervisi Eksekutif (4 Langkah Utama)
            </h6>
            <div class="row g-3 text-center">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="p-3 bg-light rounded-4 h-100 border border-primary-subtle">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow" style="width: 44px; height: 44px; font-weight: bold;">1</div>
                        <h6 class="fw-bold text-primary mb-1">Dashboard Executive</h6>
                        <small class="text-muted d-block" style="font-size: 0.78rem;">Monitoring statistik KPI utama, rata-rata E-Rapor, & chart keaktifan KBM secara realtime.</small>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="p-3 bg-light rounded-4 h-100 border border-success-subtle">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow" style="width: 44px; height: 44px; font-weight: bold;">2</div>
                        <h6 class="fw-bold text-success mb-1">Supervisi Guru</h6>
                        <small class="text-muted d-block" style="font-size: 0.78rem;">Pantau keaktifan pengungsian modul materi, penugasan, & kuis CBT per Guru pengampu.</small>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="p-3 bg-light rounded-4 h-100 border border-warning-subtle">
                        <div class="bg-warning text-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow" style="width: 44px; height: 44px; font-weight: bold;">3</div>
                        <h6 class="fw-bold text-dark mb-1">Monitoring Siswa & Rombel</h6>
                        <small class="text-muted d-block" style="font-size: 0.78rem;">Supervisi progress tugas siswa, persentase kehadiran presensi, & nilai leger kelas.</small>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="p-3 bg-light rounded-4 h-100 border border-info-subtle">
                        <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow" style="width: 44px; height: 44px; font-weight: bold;">4</div>
                        <h6 class="fw-bold text-info mb-1">Penerbitan Laporan PDF</h6>
                        <small class="text-muted d-block" style="font-size: 0.78rem;">Cetak laporan resmi data Guru & Siswa ber-Kop Surat Sekolah & Tanda Tangan Resmi.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Operational Manual Modules -->
        <div id="panduanAccordion" class="accordion d-flex flex-column gap-3 mb-5">

            <!-- Modul 1 -->
            <div class="accordion-item card-custom border-0 shadow-sm rounded-4 overflow-hidden panduan-item">
                <h2 class="accordion-header">
                    <button class="accordion-button fw-bold py-3 text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#modul1" aria-expanded="true" aria-controls="modul1">
                        <i class="bi bi-speedometer2 text-primary me-2 fs-5"></i> 1. Modul Dashboard Executive & Realtime Analytics (`kepsek/dashboard`)
                    </button>
                </h2>
                <div id="modul1" class="accordion-collapse collapse show" data-bs-parent="#panduanAccordion">
                    <div class="accordion-body border-top">
                        <p class="text-muted small">Dashboard Eksekutif merupakan pusat informasi pengawasan tingkat tinggi Kepala Sekolah yang menampilkan data terkini secara otomatis dari database MySQL.</p>
                        <ul class="small mb-3">
                            <li><strong>Kartu Metric KPI Utama</strong>: Menampilkan total Guru Aktif, Siswa Terdaftar, Rata-Rata Nilai Akhir (Leger E-Rapor & CBT), serta Tingkat Kehadiran Presensi Harian.</li>
                            <li><strong>Grafik Keaktifan Guru (Grouped Bar Chart)</strong>: Membandingkan produktivitas pemberian tugas, upload modul materi, dan pembuatan kuis CBT oleh setiap Guru.</li>
                            <li><strong>Grafik Sebaran Siswa Per Jurusan (Doughnut Chart)</strong>: Menampilkan komposisi jumlah siswa pada setiap Program Keahlian.</li>
                            <li><strong>Grafik Performa Rombel Kelas</strong>: Perbandingan rata-rata Leger E-Rapor dan persentase kehadiran presensi harian di setiap Rombel Kelas.</li>
                        </ul>
                        <div class="alert alert-info py-2 small mb-0"><i class="bi bi-info-circle me-1"></i> <strong>Catatan DB</strong>: Jika belum ada input nilai atau presensi di database, sistem menampilkan indikator `0.0` / `Belum Ada Data` secara presisi tanpa angka buatan.</div>
                    </div>
                </div>
            </div>

            <!-- Modul 2 -->
            <div class="accordion-item card-custom border-0 shadow-sm rounded-4 overflow-hidden panduan-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold py-3 text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#modul2" aria-expanded="false" aria-controls="modul2">
                        <i class="bi bi-person-check-fill text-success me-2 fs-5"></i> 2. Modul Monitoring Tenaga Pengajar / Guru (`kepsek/monitoringGuru`)
                    </button>
                </h2>
                <div id="modul2" class="accordion-collapse collapse" data-bs-parent="#panduanAccordion">
                    <div class="accordion-body border-top">
                        <p class="text-muted small">Menu ini berfungsi untuk memonitor produktivitas seluruh Guru dan Tenaga Pengajar secara detail.</p>
                        <ul class="small mb-3">
                            <li><strong>Identitas & Kontak Guru</strong>: NIP, Nama Lengkap, Jenis Kelamin, Nomor Telepon, dan Email Kedinasan Guru.</li>
                            <li><strong>Rombel Kelas Ajar</strong>: Menampilkan daftar rombel kelas yang diampu oleh Guru berdasarkan jadwal pembelajaran.</li>
                            <li><strong>Metrik Produktivitas</strong>: Menampilkan jumlah Modul Materi diunggah, Tugas diterbitkan, dan Kuis CBT dibuat oleh masing-masing Guru.</li>
                            <li><strong>Status Keaktifan</strong>: Indikator status akun Guru (`Aktif` / `Nonaktif`).</li>
                        </ul>
                        <div class="alert alert-success py-2 small mb-0"><i class="bi bi-printer me-1"></i> Gunakan tombol <strong>Cetak Laporan Guru PDF</strong> di pojok kanan atas untuk mengunduh laporan resmi supervisi pengajar.</div>
                    </div>
                </div>
            </div>

            <!-- Modul 3 -->
            <div class="accordion-item card-custom border-0 shadow-sm rounded-4 overflow-hidden panduan-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold py-3 text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#modul3" aria-expanded="false" aria-controls="modul3">
                        <i class="bi bi-graph-up-arrow text-warning me-2 fs-5"></i> 3. Modul Monitoring Siswa & Progress Belajar (`kepsek/monitoringSiswa`)
                    </button>
                </h2>
                <div id="modul3" class="accordion-collapse collapse" data-bs-parent="#panduanAccordion">
                    <div class="accordion-body border-top">
                        <p class="text-muted small">Menu ini menyajikan laporan capaian akademik siswa, pengumpulan tugas, dan evaluasi KKM.</p>
                        <ul class="small mb-3">
                            <li><strong>Data Siswa & Rombel</strong>: NIS/NISN, Nama Lengkap Siswa, Rombel Kelas, dan Program Keahlian.</li>
                            <li><strong>Tugas Dikumpul</strong>: Jumlah berkas penugasan yang telah dikirimkan oleh siswa.</li>
                            <li><strong>Rata-Rata E-Rapor</strong>: Rata-rata pencapaian nilai leger E-Rapor siswa (menampilkan `Belum Dinilai` jika belum ada nilai).</li>
                            <li><strong>Logika Status Ketuntasan</strong>:
                                <br>- <span class="badge bg-secondary">BELUM ADA DATA</span> : Jika belum ada input nilai E-Rapor & 0 berkas tugas dikumpul.
                                <br>- <span class="badge bg-success">TUNTAS</span> : Rata-rata nilai E-Rapor $\ge 75$ (mencapai KKM).
                                <br>- <span class="badge bg-danger">BELUM TUNTAS</span> : Rata-rata nilai E-Rapor $< 75$ (di bawah KKM).
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Modul 4 -->
            <div class="accordion-item card-custom border-0 shadow-sm rounded-4 overflow-hidden panduan-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold py-3 text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#modul4" aria-expanded="false" aria-controls="modul4">
                        <i class="bi bi-display-fill text-info me-2 fs-5"></i> 4. Modul Monitoring Pembelajaran Virtual Class (`kepsek/monitoringPembelajaran`)
                    </button>
                </h2>
                <div id="modul4" class="accordion-collapse collapse" data-bs-parent="#panduanAccordion">
                    <div class="accordion-body border-top">
                        <p class="text-muted small">Memantau keaktifan pelaksanaan KBM digital pada seluruh Rombongan Belajar (Rombel).</p>
                        <ul class="small mb-3">
                            <li><strong>Summary Widgets</strong>: Menampilkan total Rombel Kelas Virtual, Modul Pembelajaran Publik, Tugas Terbit, dan Paket Kuis CBT.</li>
                            <li><strong>Tabel Per Rombel Kelas</strong>: Rekapitulasi Nama Kelas, Jurusan, Wali Kelas Pengampu, Jumlah Modul, Jumlah Tugas, Jumlah Kuis CBT, & Rata-Rata Nilai.</li>
                            <li><strong>Status Keaktifan KBM Rombel</strong>:
                                <br>- <span class="badge bg-secondary">BELUM ADA KBM</span> : Belum ada modul/tugas/nilai di rombel tersebut.
                                <br>- <span class="badge bg-success">SANGAT AKTIF</span> : Aktivitas KBM tinggi & nilai akademik memuaskan.
                                <br>- <span class="badge bg-primary">AKTIF</span> : KBM berjalan lancar sesuai kurikulum.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Modul 5 -->
            <div class="accordion-item card-custom border-0 shadow-sm rounded-4 overflow-hidden panduan-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold py-3 text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#modul5" aria-expanded="false" aria-controls="modul5">
                        <i class="bi bi-printer-fill text-danger me-2 fs-5"></i> 5. Modul Penerbitan & Cetak Laporan Resmi PDF (`kepsek/cetakLaporan`)
                    </button>
                </h2>
                <div id="modul5" class="accordion-collapse collapse" data-bs-parent="#panduanAccordion">
                    <div class="accordion-body border-top">
                        <p class="text-muted small">Fitur pencetakan dokumen eksekutif resmi berformat PDF yang siap dicetak atau disimpan digital.</p>
                        <ul class="small mb-3">
                            <li><strong>Laporan Data Guru (PDF)</strong>: Dokumen resmi berisi data pengajar, kontak, produktivitas modul/tugas, dan status keaktifan.</li>
                            <li><strong>Laporan Data Siswa (PDF)</strong>: Dokumen resmi berisi data siswa, rombel, status ketuntasan belajar, dan berkas tugas dikumpul.</li>
                            <li><strong>Komponen Dokumen Resmi</strong>: Dilengkapi <strong>Kop Surat Sekolah</strong>, <strong>Logo Resmi Sekolah</strong>, Alamat, Tanggal Cetak Otomatis, serta **Tanda Tangan Kepala Sekolah**.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Modul 6 -->
            <div class="accordion-item card-custom border-0 shadow-sm rounded-4 overflow-hidden panduan-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold py-3 text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#modul6" aria-expanded="false" aria-controls="modul6">
                        <i class="bi bi-person-circle text-primary me-2 fs-5"></i> 6. Modul Profil Eksekutif & Unggah Foto Avatar (`kepsek/profil`)
                    </button>
                </h2>
                <div id="modul6" class="accordion-collapse collapse" data-bs-parent="#panduanAccordion">
                    <div class="accordion-body border-top">
                        <p class="text-muted small">Fasilitas pengolahan identitas pimpinan, foto profil resmi, dan keamanan kata sandi.</p>
                        <ul class="small mb-3">
                            <li><strong>Update Identitas</strong>: Mengubah Nama Lengkap & Gelar serta Email Kedinasan Resmi.</li>
                            <li><strong>Unggah Foto Profil (Opsional)</strong>: Mengunggah foto formal (JPG, PNG, WEBP) yang akan otomatis muncul di Kartu Profil Eksekutif dan Navbar Atas.</li>
                            <li><strong>Ubah Password Security</strong>: Mengubah kata sandi akun eksekutif secara berkala untuk menjaga keamanan portal.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Modul 7 -->
            <div class="accordion-item card-custom border-0 shadow-sm rounded-4 overflow-hidden panduan-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold py-3 text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#modul7" aria-expanded="false" aria-controls="modul7">
                        <i class="bi bi-chat-dots-fill text-success me-2 fs-5"></i> 7. Modul Komunikasi (Chat Realtime & Forum Diskusi)
                    </button>
                </h2>
                <div id="modul7" class="accordion-collapse collapse" data-bs-parent="#panduanAccordion">
                    <div class="accordion-body border-top">
                        <p class="text-muted small">Sarana komunikasi dua arah antara Kepala Sekolah, Guru, Admin, dan Siswa.</p>
                        <ul class="small mb-3">
                            <li><strong>Chat Realtime (`chat`)</strong>: Mengirim pesan langsung secara pribadi kepada Guru atau Siswa.</li>
                            <li><strong>Forum Diskusi Pembelajaran (`forum`)</strong>: Memantau dan memberikan tanggapan/pengumuman penting di utas diskusi sekolah.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

    </div>
</main>

<!-- Interactive Keyword Search JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchPanduanKepsek');
    const items = document.querySelectorAll('.panduan-item');

    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const query = this.value.toLowerCase().trim();

            items.forEach(function(item) {
                const text = item.innerText.toLowerCase();
                const collapseElem = item.querySelector('.accordion-collapse');
                
                if (query === '') {
                    item.style.display = '';
                } else if (text.includes(query)) {
                    item.style.display = '';
                    if (collapseElem && !collapseElem.classList.contains('show')) {
                        new bootstrap.Collapse(collapseElem, { show: true });
                    }
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>
