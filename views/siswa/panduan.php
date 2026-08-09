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
                    <i class="bi bi-book-half fs-2"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Panduan Pengguna & Manual Belajar Siswa (Hak Akses Siswa)</h4>
                    <p class="text-muted small mb-0">Petunjuk teknis gabung kelas virtual, akses materi, pengerjaan tugas & kuis CBT, presensi QR, serta E-Rapor Digital SMK Muthia Harapan Cicalengka.</p>
                </div>
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchPanduanSiswa" class="form-control border-start-0 ps-0" placeholder="Cari petunjuk (misal: gabung, tugas, cbt, rapor)..." onkeyup="filterPanduanSiswa()">
                </div>
            </div>
        </div>
    </div>

    <!-- Alur Belajar Utama Siswa (Diagram Step-by-Step) -->
    <div class="card-custom p-4 mb-4 shadow-sm">
        <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-diagram-3-fill me-2"></i>Alur Pembelajaran & Evaluasi Siswa</h6>
        <div class="row g-3">
            <div class="col-12 col-md-3">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative">
                    <span class="badge bg-primary mb-2">Langkah 1</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-bounding-box-circles text-primary me-1"></i>Gabung Rombel Kelas</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Masukkan Kode Akses `MH-XXXXXX` dari Guru atau pilih Rombel resmi di katalog kelas sekolah.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative">
                    <span class="badge bg-success mb-2">Langkah 2</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-book-fill text-success me-1"></i>Pelajari Modul & Video</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Akses modul PDF, tonton video MP4/YouTube (Preview tanpa unduh), & baca e-book perpustakaan.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative">
                    <span class="badge bg-warning text-dark mb-2">Langkah 3</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-pencil-square text-warning me-1"></i>Tugas & Kuis CBT</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Kirim berkas jawaban tugas & kerjakan Kuis CBT (Pilihan Ganda, Benar/Salah, Essay) dengan timer.</p>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="p-3 bg-light rounded-4 border h-100 position-relative">
                    <span class="badge bg-info text-dark mb-2">Langkah 4</span>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-file-earmark-text-fill text-info me-1"></i>Hasil & E-Rapor Digital</h6>
                    <p class="text-muted small mb-0" style="font-size:0.78rem;">Pantau transkrip nilai resmi E-Rapor, Kartu Pelajar QR Code, & cetak Sertifikat Kelulusan PDF.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modul-by-Modul Comprehensive Guides Accordion -->
    <div class="accordion" id="accordionPanduanSiswa">

        <!-- MODUL 1: DASHBOARD SISWA -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa">
            <h2 class="accordion-header">
                <button class="accordion-button fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul1">
                    <i class="bi bi-grid-1x2-fill text-primary me-2 fs-5"></i> Modul 1: Beranda & Dashboard Siswa (`siswa/dashboard`)
                </button>
            </h2>
            <div id="collapseSiswaModul1" class="accordion-collapse collapse show" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-4">
                    <h6 class="fw-bold text-dark"><i class="bi bi-info-circle text-primary me-1"></i>Fungsi Utama Beranda Siswa</h6>
                    <p class="small text-muted mb-3">Pusat ringkasan informasi KBM digital khusus untuk akun Siswa terdaftar.</p>

                    <h6 class="fw-bold text-dark mt-3"><i class="bi bi-gear-wide-connected text-primary me-1"></i>Komponen Utama:</h6>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2"><strong>Materi Pembelajaran:</strong> Jumlah modul dan video materi yang tersedia di rombel kelas Anda.</li>
                        <li class="mb-2"><strong>Tugas Saya:</strong> Jumlah penugasan dari Guru yang perlu dikerjakan beserta tenggat waktu.</li>
                        <li class="mb-2"><strong>Kuis CBT Aktif:</strong> Ujian CBT atau kuis online yang sedang berlangsung.</li>
                        <li class="mb-2"><strong>Jadwal Belajar:</strong> Rekap jadwal pelajaran harian rombel Anda di sekolah.</li>
                        <li class="mb-2"><strong>Pengumuman Sekolah:</strong> Wawasan dan kabar pengumuman resmi dari Admin/Guru.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- MODUL 2: PEMBELAJARAN & LEARNING PATH -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul2">
                    <i class="bi bi-book-fill text-success me-2 fs-5"></i> Modul 2: Gabung Kelas, Materi & Learning Path
                </button>
            </h2>
            <div id="collapseSiswaModul2" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-primary"><i class="bi bi-bounding-box-circles me-1"></i>1. Gabung Kelas Virtual (`siswa/gabungKelas`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-2"><strong>Metode 1 (Kode Akses):</strong> Masukkan Kode Akses Unik `MH-XXXXXX` yang dibagikan oleh Guru/Wali Kelas.</li>
                                    <li class="mb-2"><strong>Metode 2 (1-Klik Join):</strong> Pilih Rombel Kelas resmi di katalog sekolah dan klik <i>Gabung Ke Rombel Ini</i>.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-success"><i class="bi bi-book-fill me-1"></i>2. Materi & Video Learning (`siswa/materi`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-2"><strong>Pratinjau Modul PDF:</strong> Membaca materi tanpa mengunduh file via modal preview instan.</li>
                                    <li class="mb-2"><strong>Video MP4 & YouTube:</strong> Memutar tayangan video penjelasan Guru langsung di sistem.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODUL 3: TUGAS & EVALUASI -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul3">
                    <i class="bi bi-pencil-square text-warning me-2 fs-5"></i> Modul 3: Pengerjaan Tugas & Ujian Kuis CBT
                </button>
            </h2>
            <div id="collapseSiswaModul3" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-warning"><i class="bi bi-pencil-square me-1"></i>1. Kerjakan Tugas (`siswa/tugas`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-2"><strong>Upload File & Catatan:</strong> Unggah file dokumen/gambar tugas beserta catatan pengerjaan untuk Guru.</li>
                                    <li class="mb-2"><strong>Status Pengumpulan:</strong> Memeriksa status (Sudah Dikirim, Dinilai, atau Terlambat).</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 bg-light h-100">
                                <h6 class="fw-bold text-danger"><i class="bi bi-stopwatch-fill me-1"></i>2. Kuis & Ujian CBT (`siswa/quiz`)</h6>
                                <ul class="small text-muted ps-3 mb-0">
                                    <li class="mb-2"><strong>3 Tipe Soal CBT:</strong> Mengerjakan soal Pilihan Ganda (PG), Benar / Salah (True/False), dan Uraian (Essay).</li>
                                    <li class="mb-2"><strong>Timer Countdown:</strong> Waktu ujian dihitung mundur secara otomatis oleh sistem.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODUL 4: HASIL & IDENTITAS DIGITAL -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul4">
                    <i class="bi bi-file-earmark-text-fill text-info me-2 fs-5"></i> Modul 4: E-Rapor Digital, Kartu Pelajar & Sertifikat
                </button>
            </h2>
            <div id="collapseSiswaModul4" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-4">
                    <div class="p-3 border rounded-3 bg-light mb-3">
                        <h6 class="fw-bold text-info"><i class="bi bi-file-earmark-text-fill me-1"></i>E-Rapor Digital Siswa (`siswa/rapor`)</h6>
                        <ul class="small text-muted ps-3 mb-0">
                            <li class="mb-2"><strong>Transkrip Resmi:</strong> Menampilkan komponen nilai Tugas (20%), Quiz (20%), UTS (30%), dan UAS (30%), serta Nilai Akhir & Predikat.</li>
                            <li class="mb-2"><strong>Logo Kop & TTD Pengaturan:</strong> Terkoneksi dengan Logo Resmi Sekolah, Nama Kepala Sekolah, & Wali Kelas.</li>
                            <li class="mb-2"><strong>Cetak PDF Rapor:</strong> Klik <i>Cetak / Simpan PDF Rapor</i> untuk mencetak tanpa bilah scrollbar.</li>
                        </ul>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-person-badge text-primary me-1"></i>Kartu Pelajar Digital (`siswa/kartuPelajar`)</h6>
                                <p class="small text-muted mb-0">Kartu identitas resmi siswa dilengkapi QR Code presensi untuk pemindaian kamera.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-patch-check-fill text-warning me-1"></i>Sertifikat Digital (`siswa/sertifikat`)</h6>
                                <p class="small text-muted mb-0">Sertifikat apresiasi dan ketuntasan belajar digital yang dapat diunduh langsung.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODUL 5: KOMUNIKASI & INTERAKSI -->
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden panduan-card-siswa">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSiswaModul5">
                    <i class="bi bi-chat-square-quote-fill text-danger me-2 fs-5"></i> Modul 5: Forum Diskusi, Chat & Profil Siswa
                </button>
            </h2>
            <div id="collapseSiswaModul5" class="accordion-collapse collapse" data-bs-parent="#accordionPanduanSiswa">
                <div class="accordion-body bg-white p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-chat-square-text text-success me-1"></i>Forum Diskusi (`forum`)</h6>
                                <p class="small text-muted mb-0">Wadah tanya-jawab KBM per mapel bersama Guru dan teman sekelas.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-chat-dots text-primary me-1"></i>Chat Realtime (`chat`)</h6>
                                <p class="small text-muted mb-0">Pesan pribadi dengan Guru/Wali Kelas. Notifikasi bel header terhubung otomatis.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold text-dark"><i class="bi bi-person-circle text-info me-1"></i>Profil Siswa (`siswa/profil`)</h6>
                                <p class="small text-muted mb-0">Pengaturan foto profil, NIS/NISN, data diri, dan ubah kata sandi.</p>
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
function filterPanduanSiswa() {
    const query = document.getElementById('searchPanduanSiswa').value.toLowerCase();
    const cards = document.querySelectorAll('.panduan-card-siswa');

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
