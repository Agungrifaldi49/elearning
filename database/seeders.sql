-- Seeders for E-Learning SMK Muthia Harapan Cicalengka

-- 1. Insert Roles
INSERT INTO roles (id, name, description) VALUES
(1, 'Administrator', 'Sistem Administrator / Operator Sekolah'),
(2, 'Guru', 'Tenaga Pengajar SMK Muthia Harapan Cicalengka'),
(3, 'Siswa', 'Peserta Didik SMK Muthia Harapan Cicalengka'),
(4, 'Kepala Sekolah', 'Pimpinan / Kepala Sekolah');

-- 2. Insert Users ($2y$10$abcdefghijklmnopqrstuu... default hash for 'admin123', 'guru123', 'siswa123', 'kepsek123')
INSERT INTO users (id, role_id, username, email, password, full_name, avatar, status) VALUES
(1, 1, 'admin', 'admin@smkmh-cicalengka.sch.id', '$2y$10$qS2iG1GqV2L3N.a5v7Z0eeR2h8kL4A2.v7qB6C8m9D0e1F2g3H4i5', 'Administrator Utama', 'default_avatar.png', 'active'),
(2, 2, 'guru', 'guru@smkmh-cicalengka.sch.id', '$2y$10$qS2iG1GqV2L3N.a5v7Z0eeR2h8kL4A2.v7qB6C8m9D0e1F2g3H4i5', 'Drs. Ahmad Hidayat, M.Pd.', 'guru_avatar.png', 'active'),
(3, 3, 'siswa', 'siswa@smkmh-cicalengka.sch.id', '$2y$10$qS2iG1GqV2L3N.a5v7Z0eeR2h8kL4A2.v7qB6C8m9D0e1F2g3H4i5', 'Muhammad Rizky Pratama', 'siswa_avatar.png', 'active'),
(4, 4, 'kepsek', 'kepsek@smkmh-cicalengka.sch.id', '$2y$10$qS2iG1GqV2L3N.a5v7Z0eeR2h8kL4A2.v7qB6C8m9D0e1F2g3H4i5', 'H. Supriyadi, M.M.', 'kepsek_avatar.png', 'active'),
(5, 2, 'guru2', 'budi@smkmh-cicalengka.sch.id', '$2y$10$qS2iG1GqV2L3N.a5v7Z0eeR2h8kL4A2.v7qB6C8m9D0e1F2g3H4i5', 'Budi Santoso, S.T.', 'default_avatar.png', 'active'),
(6, 3, 'siswa2', 'siti@smkmh-cicalengka.sch.id', '$2y$10$qS2iG1GqV2L3N.a5v7Z0eeR2h8kL4A2.v7qB6C8m9D0e1F2g3H4i5', 'Siti Rahmawati', 'default_avatar.png', 'active');

-- 3. Insert Jurusan
INSERT INTO jurusan (id, kode_jurusan, nama_jurusan, deskripsi) VALUES
(1, 'RPL', 'Rekayasa Perangkat Lunak', 'Pengembangan software, web development, dan aplikasi mobile.'),
(2, 'TKJ', 'Teknik Komputer dan Jaringan', 'Spesialisasi jaringan komputer, server, dan cyber security.'),
(3, 'DKV', 'Desain Komunikasi Visual', 'Desain grafis, multimedia, fotografi, dan animasi 2D/3D.'),
(4, 'TKR', 'Teknik Kendaraan Ringan', 'Pemeliharaan dan perbaikan mesin otomotif modern.');

-- 4. Insert Kelas
INSERT INTO kelas (id, nama_kelas, jurusan_id, tingkat) VALUES
(1, 'X RPL 1', 1, 'X'),
(2, 'XI RPL 1', 1, 'XI'),
(3, 'XII RPL 1', 1, 'XII'),
(4, 'X TKJ 1', 2, 'X'),
(5, 'XI TKJ 1', 2, 'XI'),
(6, 'X DKV 1', 3, 'X');

-- 5. Insert Guru
INSERT INTO guru (id, user_id, nip, nama_lengkap, jenis_kelamin, no_telepon, alamat, status) VALUES
(1, 2, '198501152010011002', 'Drs. Ahmad Hidayat, M.Pd.', 'L', '081234567890', 'Jl. Raya Cicalengka No. 45, Bandung', 'aktif'),
(2, 5, '199003202015021004', 'Budi Santoso, S.T.', 'L', '082198765432', 'Jl. Alun-Alun Cicalengka No. 12', 'aktif');

-- 6. Insert Siswa
INSERT INTO siswa (id, user_id, nis, nisn, nama_lengkap, kelas_id, jurusan_id, jenis_kelamin, no_telepon, alamat, status) VALUES
(1, 3, '20231001', '0061234567', 'Muhammad Rizky Pratama', 2, 1, 'L', '085712345678', 'Jl. Cikopo Cicalengka No. 8', 'aktif'),
(2, 6, '20231002', '0067654321', 'Siti Rahmawati', 2, 1, 'P', '085798765432', 'Jl. Nagreg No. 15, Bandung', 'aktif');

-- 7. Insert Mata Pelajaran
INSERT INTO mata_pelajaran (id, kode_mapel, nama_mapel, jurusan_id) VALUES
(1, 'MP01', 'Pemrograman Web dan Perangkat Bergerak', 1),
(2, 'MP02', 'Pemodelan Perangkat Lunak', 1),
(3, 'MP03', 'Administrasi Infrastruktur Jaringan', 2),
(4, 'MP04', 'Desain Grafis Percetakan', 3),
(5, 'MP05', 'Bahasa Indonesia', NULL),
(6, 'MP06', 'Matematika Terapan', NULL);

-- 8. Insert Jadwal
INSERT INTO jadwal (id, kelas_id, mapel_id, guru_id, hari, jam_mulai, jam_selesai, ruangan) VALUES
(1, 2, 1, 1, 'Senin', '07:30:00', '09:30:00', 'Lab Komputer 1'),
(2, 2, 2, 2, 'Selasa', '09:45:00', '11:45:00', 'Lab Komputer 2'),
(3, 5, 3, 2, 'Rabu', '08:00:00', '10:00:00', 'Lab Jaringan');

-- 9. Insert Tahun Ajaran & Semester
INSERT INTO tahun_ajaran (id, tahun, status) VALUES
(1, '2025/2026', 'aktif'),
(2, '2024/2025', 'non-aktif');

INSERT INTO semester (id, nama_semester, status) VALUES
(1, 'Ganjil', 'aktif'),
(2, 'Genap', 'non-aktif');

-- 10. Insert Materi
INSERT INTO materi (id, guru_id, mapel_id, kelas_id, judul, deskripsi, jenis_file, file_path, youtube_url) VALUES
(1, 1, 1, 2, 'Konsep dasar MVC pada PHP Native', 'Materi lengkap arsitektur Model-View-Controller dalam pembuatan web modern.', 'pdf', 'materi_mvc_php.pdf', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'),
(2, 2, 2, 2, 'Prinsip Object Oriented Programming (OOP)', 'Pemahaman class, object, inheritance, dan encapsulation dalam PHP 8.', 'ppt', 'materi_oop_php.pptx', NULL);

-- 11. Insert Tugas
INSERT INTO tugas (id, guru_id, mapel_id, kelas_id, judul, deskripsi, file_path, deadline) VALUES
(1, 1, 1, 2, 'Tugas 1: Membuat Auth Login MVC', 'Silakan buat modul login lengkap dengan CSRF token dan Session Handling.', 'panduan_tugas1.pdf', DATE_ADD(NOW(), INTERVAL 7 DAY));

-- 12. Insert Quiz
INSERT INTO quiz (id, guru_id, mapel_id, kelas_id, judul, deskripsi, durasi_menit, jumlah_soal, random_soal, random_jawaban, status) VALUES
(1, 1, 1, 2, 'Kuis 1 - PHP & Framework Basics', 'Kuis evaluasi pemahaman dasar PHP Native 8 dan MVC Architecture.', 30, 3, 'Y', 'Y', 'published');

-- 13. Insert Soal & Pilihan Jawaban
INSERT INTO soal (id, quiz_id, jenis_soal, pertanyaan, bobot) VALUES
(1, 1, 'pg', 'Apa kepanjangan dari MVC dalam pengembangan perangkat lunak?', 35),
(2, 1, 'pg', 'Fungsi utama PDO pada PHP 8 adalah untuk?', 35),
(3, 1, 'essay', 'Jelaskan perbedaan mendasar antara HTTP GET dan POST!', 30);

INSERT INTO pilihan_jawaban (id, soal_id, teks_pilihan, is_benar) VALUES
(1, 1, 'Model View Controller', 1),
(2, 1, 'Main View Center', 0),
(3, 1, 'Modular Visual Code', 0),
(4, 1, 'Mode Variable Class', 0),
(5, 2, 'Koneksi database aman dengan Prepared Statements', 1),
(6, 2, 'Membuat UI responsive Bootstrap', 0),
(7, 2, 'Manipulasi gambar dan file PDF', 0);

-- 14. Insert Ujian CBT
INSERT INTO ujian (id, guru_id, mapel_id, kelas_id, nama_ujian, jenis_ujian, durasi_menit, tgl_mulai, tgl_selesai, token_ujian, is_active) VALUES
(1, 1, 1, 2, 'Ujian Tengah Semester (UTS) Web Programming', 'UTS', 60, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'SMKMH1', 1);

-- 15. Insert Pengumuman
INSERT INTO pengumuman (id, user_id, judul, isi, target_role, is_popup) VALUES
(1, 1, 'Selamat Datang di E-Learning SMK Muthia Harapan Cicalengka', 'Portal pembelajaran digital resmi SMK Muthia Harapan Cicalengka telah siap digunakan untuk kegiatan KBM online.', 'all', 1),
(2, 1, 'Jadwal Penilaian Tengah Semester (PTS) Ganjil', 'Diinformasikan kepada seluruh siswa dan guru bahwa PTS Ganjil akan dimulai pekan depan.', 'all', 0);

-- 16. Insert Forum Topics
INSERT INTO forum (id, user_id, mapel_id, judul, konten, likes_count) VALUES
(1, 2, 1, 'Diskusi Penggunaan Prepared Statements pada PDO', 'Bagaimana pendapat kalian tentang performa Prepared Statements dibanding query konvensional?', 5),
(2, 3, 1, 'Tanya: Error Session Timeout saat Pengerjaan Quiz', 'Halo Pak/Bu Guru, jika koneksi internet terputus saat quiz apakah jawaban otomatis tersimpan?', 2);

-- 17. Insert Forum Replies
INSERT INTO komentar (id, forum_id, user_id, parent_id, komentar) VALUES
(1, 1, 3, NULL, 'Prepared Statement jauh lebih aman dari serangan SQL Injection Pak! Serta query execution plan bisa dire-use oleh MySQL server.'),
(2, 1, 2, 1, 'Sangat tepat Rizky! Keamanan dan efisiensi query menjadi prioritas utama.');

-- 18. Insert Chat Demo
INSERT INTO chat (id, sender_id, receiver_id, message, is_read) VALUES
(1, 3, 2, 'Assalamu alaikum Pak Ahmad, mau bertanya terkait deadline tugas 1 web.', 1),
(2, 2, 3, 'Wa alaikumussalam Rizky, deadline tugas 1 diset sampai minggu depan jam 23:59 WIB ya.', 1);

-- 19. Insert Library (Perpustakaan Digital)
INSERT INTO library (id, judul, penulis, deskripsi, kategori, kelas_target, file_type, file_path, file_size, uploader_id, view_count, download_count) VALUES
(1, 'Buku Panduan Pembelajaran Digital SMK', 'Tim Kurikulum SMK Muthia Harapan', 'Panduan resmi penggunaan LMS Mobile, kelas virtual, presensi QR Code, dan e-learning interaktif SMK Muthia Harapan Cicalengka.', 'Panduan', 'Semua Kelas', 'pdf', 'assets/docs/panduan.pdf', 2450000, 1, 1420, 350),
(2, 'Modul Pemrograman Web & Mobile Framework', 'Tim IT & Rekayasa Perangkat Lunak', 'Modul praktikum komprehensif pengembangan aplikasi Web modern berbasis PHP MySQL & Flutter Mobile Engine.', 'Teknologi Informasi', 'XII RPL 1', 'pdf', 'assets/docs/modul_web.pdf', 3800000, 2, 980, 210),
(3, 'Dasar-Dasar Kejuruan & Otomasi Industri', 'Tim Pendidik Kejuruan', 'Buku referensi konsep dasar otomasi industri, kelistrikan, dan teknik mekanik otomotif SMK.', 'Kejuruan', 'X & XI TKR', 'pdf', 'assets/docs/modul_kejuruan.pdf', 4100000, 2, 750, 180),
(4, 'Ensiklopedia Sains & Teknologi Modern', 'Dr. Ir. Hendra Gunawan', 'Ensiklopedia sains populer mengenai kecerdasan buatan, IoT, Cloud Computing, dan fisika terapan.', 'Sains', 'Semua Kelas', 'pdf', 'assets/docs/ensiklopedia_sains.pdf', 5200000, 1, 1100, 420),
(5, 'Modul Bahasa Indonesia & Literasi Industri', 'Dra. Endang Rahayu', 'Modul komprehensif tata bahasa Indonesia, penulisan laporan ilmiah, dan komunikasi bisnis industri.', 'Umum', 'Semua Kelas', 'pdf', 'assets/docs/modul_bindo.pdf', 1900000, 1, 620, 140);
