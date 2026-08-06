-- Database Backup: E-Learning SMK Muthia Harapan Cicalengka
-- Date: 2026-08-02 10:12:45



CREATE TABLE `absensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jadwal_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Hadir','Izin','Sakit','Alpa') DEFAULT 'Hadir',
  `qr_code` varchar(255) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `jadwal_id` (`jadwal_id`),
  KEY `siswa_id` (`siswa_id`),
  CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal` (`id`) ON DELETE CASCADE,
  CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `aktivitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `activity` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `aktivitas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `aktivitas` VALUES('1', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-02 07:11:43');
INSERT INTO `aktivitas` VALUES('2', '1', 'Logout dari sistem', '::1', '2026-08-02 07:12:42');
INSERT INTO `aktivitas` VALUES('3', '2', 'Login ke sistem sebagai Guru', '::1', '2026-08-02 07:13:18');
INSERT INTO `aktivitas` VALUES('4', '2', 'Logout dari sistem', '::1', '2026-08-02 07:14:43');
INSERT INTO `aktivitas` VALUES('5', '3', 'Login ke sistem sebagai Siswa', '::1', '2026-08-02 07:15:17');
INSERT INTO `aktivitas` VALUES('6', '3', 'Logout dari sistem', '::1', '2026-08-02 07:16:08');
INSERT INTO `aktivitas` VALUES('7', '4', 'Login ke sistem sebagai Kepala Sekolah', '::1', '2026-08-02 07:16:28');
INSERT INTO `aktivitas` VALUES('8', '4', 'Logout dari sistem', '::1', '2026-08-02 07:16:47');
INSERT INTO `aktivitas` VALUES('9', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-02 07:18:53');
INSERT INTO `aktivitas` VALUES('10', '1', 'Logout dari sistem', '::1', '2026-08-02 07:24:46');
INSERT INTO `aktivitas` VALUES('11', '2', 'Login ke sistem sebagai Guru', '::1', '2026-08-02 07:25:21');
INSERT INTO `aktivitas` VALUES('12', '2', 'Logout dari sistem', '::1', '2026-08-02 07:27:44');
INSERT INTO `aktivitas` VALUES('13', '3', 'Login ke sistem sebagai Siswa', '::1', '2026-08-02 07:28:14');
INSERT INTO `aktivitas` VALUES('14', '3', 'Logout dari sistem', '::1', '2026-08-02 07:32:13');
INSERT INTO `aktivitas` VALUES('15', '2', 'Login ke sistem sebagai Guru', '::1', '2026-08-02 07:40:59');
INSERT INTO `aktivitas` VALUES('16', '2', 'Logout dari sistem', '::1', '2026-08-02 07:41:51');
INSERT INTO `aktivitas` VALUES('17', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-02 07:42:09');
INSERT INTO `aktivitas` VALUES('18', '1', 'Logout dari sistem', '::1', '2026-08-02 08:10:54');
INSERT INTO `aktivitas` VALUES('19', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-02 08:11:05');
INSERT INTO `aktivitas` VALUES('20', '1', 'Logout dari sistem', '::1', '2026-08-02 08:11:54');
INSERT INTO `aktivitas` VALUES('21', '3', 'Login ke sistem sebagai Siswa', '::1', '2026-08-02 08:12:04');
INSERT INTO `aktivitas` VALUES('22', '3', 'Logout dari sistem', '::1', '2026-08-02 08:13:40');
INSERT INTO `aktivitas` VALUES('23', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-02 08:14:06');
INSERT INTO `aktivitas` VALUES('24', '1', 'Logout dari sistem', '::1', '2026-08-02 08:18:39');
INSERT INTO `aktivitas` VALUES('25', '4', 'Login ke sistem sebagai Kepala Sekolah', '::1', '2026-08-02 08:19:05');
INSERT INTO `aktivitas` VALUES('26', '4', 'Logout dari sistem', '::1', '2026-08-02 08:20:13');
INSERT INTO `aktivitas` VALUES('27', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-02 08:20:36');
INSERT INTO `aktivitas` VALUES('28', '7', 'Login ke sistem sebagai Siswa', '::1', '2026-08-02 08:44:39');
INSERT INTO `aktivitas` VALUES('29', '7', 'Logout dari sistem', '::1', '2026-08-02 09:03:43');
INSERT INTO `aktivitas` VALUES('30', '4', 'Login ke sistem sebagai Kepala Sekolah', '::1', '2026-08-02 09:03:58');
INSERT INTO `aktivitas` VALUES('31', '4', 'Logout dari sistem', '::1', '2026-08-02 09:24:52');
INSERT INTO `aktivitas` VALUES('32', '2', 'Login ke sistem sebagai Guru', '::1', '2026-08-02 09:25:09');
INSERT INTO `aktivitas` VALUES('33', '2', 'Logout dari sistem', '::1', '2026-08-02 09:43:18');
INSERT INTO `aktivitas` VALUES('34', '8', 'Login ke sistem sebagai Siswa', '::1', '2026-08-02 09:43:33');
INSERT INTO `aktivitas` VALUES('35', '8', 'Logout dari sistem', '::1', '2026-08-02 09:43:43');
INSERT INTO `aktivitas` VALUES('36', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-02 09:48:29');
INSERT INTO `aktivitas` VALUES('37', '9', 'Logout dari sistem', '::1', '2026-08-02 09:49:07');
INSERT INTO `aktivitas` VALUES('38', '2', 'Login ke sistem sebagai Guru', '::1', '2026-08-02 09:49:16');
INSERT INTO `aktivitas` VALUES('39', '2', 'Logout dari sistem', '::1', '2026-08-02 09:49:26');
INSERT INTO `aktivitas` VALUES('40', '7', 'Login ke sistem sebagai Siswa', '::1', '2026-08-02 09:54:57');


CREATE TABLE `backup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `file_size` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `chat` VALUES('1', '3', '2', 'Assalamu alaikum Pak Ahmad, mau bertanya terkait deadline tugas 1 web.', '1', '2026-08-02 07:09:38');
INSERT INTO `chat` VALUES('2', '2', '3', 'Wa alaikumussalam Rizky, deadline tugas 1 diset sampai minggu depan jam 23:59 WIB ya.', '1', '2026-08-02 07:09:38');
INSERT INTO `chat` VALUES('3', '1', '7', 'test', '1', '2026-08-02 09:56:56');
INSERT INTO `chat` VALUES('4', '7', '1', 'gimana min aman?', '1', '2026-08-02 09:57:11');
INSERT INTO `chat` VALUES('5', '1', '7', 'aman alhamdulillah', '1', '2026-08-02 09:57:20');
INSERT INTO `chat` VALUES('6', '7', '1', 'okey', '1', '2026-08-02 09:57:25');


CREATE TABLE `forum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `mapel_id` int(11) DEFAULT NULL,
  `judul` varchar(150) NOT NULL,
  `konten` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `likes_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `mapel_id` (`mapel_id`),
  CONSTRAINT `forum_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `forum_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `forum` VALUES('1', '2', '1', 'Diskusi Penggunaan Prepared Statements pada PDO', 'Bagaimana pendapat kalian tentang performa Prepared Statements dibanding query konvensional?', NULL, '5', '2026-08-02 07:09:38');
INSERT INTO `forum` VALUES('2', '3', '1', 'Tanya: Error Session Timeout saat Pengerjaan Quiz', 'Halo Pak/Bu Guru, jika koneksi internet terputus saat quiz apakah jawaban otomatis tersimpan?', NULL, '2', '2026-08-02 07:09:38');
INSERT INTO `forum` VALUES('3', '1', NULL, 'Saling Sapa', 'Bagaimana Kabar Hari ini?', NULL, '0', '2026-08-02 09:54:34');


CREATE TABLE `guru` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status` enum('aktif','non-aktif') DEFAULT 'aktif',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `nip` (`nip`),
  CONSTRAINT `guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `guru` VALUES('1', '2', '198501152010011002', 'Drs. Ahmad Hidayat, M.Pd.', 'L', '081234567890', 'Jl. Raya Cicalengka No. 45, Bandung', 'aktif');
INSERT INTO `guru` VALUES('2', '5', '199003202015021004', 'Budi Santoso, S.T.', 'L', '082198765432', 'Jl. Alun-Alun Cicalengka No. 12', 'aktif');
INSERT INTO `guru` VALUES('3', '9', '32042523010400001', 'AGUNG RIFALDI, S.Tr. Kom', 'L', '82198765433', 'Kp. Munggang Rt. 01/08 Desa Dampit Kec. Cicalengka Kab. Bandung', 'aktif');


CREATE TABLE `hasil_quiz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `total_nilai` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status_lulus` enum('lulus','tidak_lulus','menunggu') DEFAULT 'lulus',
  `started_at` datetime DEFAULT current_timestamp(),
  `finished_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `siswa_id` (`siswa_id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `hasil_quiz_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hasil_quiz_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `hasil_quiz` VALUES('1', '1', '1', '35.00', 'tidak_lulus', '2026-08-02 07:30:01', '2026-08-02 07:30:47');


CREATE TABLE `hasil_ujian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `ujian_id` int(11) NOT NULL,
  `total_nilai` decimal(5,2) DEFAULT 0.00,
  `status` enum('berlangsung','selesai','didiskualifikasi') DEFAULT 'berlangsung',
  `started_at` datetime DEFAULT current_timestamp(),
  `finished_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `siswa_id` (`siswa_id`),
  KEY `ujian_id` (`ujian_id`),
  CONSTRAINT `hasil_ujian_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hasil_ujian_ibfk_2` FOREIGN KEY (`ujian_id`) REFERENCES `ujian` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kelas_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `ruangan` varchar(50) DEFAULT 'Ruang Kelas',
  PRIMARY KEY (`id`),
  KEY `kelas_id` (`kelas_id`),
  KEY `mapel_id` (`mapel_id`),
  KEY `guru_id` (`guru_id`),
  CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_ibfk_3` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `jadwal` VALUES('1', '2', '1', '1', 'Senin', '07:30:00', '09:30:00', 'Lab Komputer 1');
INSERT INTO `jadwal` VALUES('2', '2', '2', '2', 'Selasa', '09:45:00', '11:45:00', 'Lab Komputer 2');
INSERT INTO `jadwal` VALUES('3', '5', '3', '2', 'Rabu', '08:00:00', '10:00:00', 'Lab Jaringan');


CREATE TABLE `jawaban_siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `soal_id` int(11) NOT NULL,
  `pilihan_id` int(11) DEFAULT NULL,
  `teks_jawaban_essay` text DEFAULT NULL,
  `is_benar` tinyint(1) DEFAULT 0,
  `nilai` decimal(5,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `siswa_id` (`siswa_id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `soal_id` (`soal_id`),
  KEY `pilihan_id` (`pilihan_id`),
  CONSTRAINT `jawaban_siswa_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jawaban_siswa_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jawaban_siswa_ibfk_3` FOREIGN KEY (`soal_id`) REFERENCES `soal` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jawaban_siswa_ibfk_4` FOREIGN KEY (`pilihan_id`) REFERENCES `pilihan_jawaban` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `jawaban_siswa` VALUES('1', '1', '1', '1', '1', NULL, '1', '35.00');
INSERT INTO `jawaban_siswa` VALUES('2', '1', '1', '2', '6', NULL, '0', '0.00');
INSERT INTO `jawaban_siswa` VALUES('3', '1', '1', '3', NULL, 'dsgsdgvsdv', '0', '0.00');


CREATE TABLE `jurusan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_jurusan` varchar(20) NOT NULL,
  `nama_jurusan` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_jurusan` (`kode_jurusan`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `jurusan` VALUES('1', 'RPL', 'Rekayasa Perangkat Lunak', 'Pengembangan software, web development, dan aplikasi mobile.');
INSERT INTO `jurusan` VALUES('2', 'TKJ', 'Teknik Komputer dan Jaringan', 'Spesialisasi jaringan komputer, server, dan cyber security.');
INSERT INTO `jurusan` VALUES('3', 'DKV', 'Desain Komunikasi Visual', 'Desain grafis, multimedia, fotografi, dan animasi 2D/3D.');
INSERT INTO `jurusan` VALUES('4', 'TKR', 'Teknik Kendaraan Ringan', 'Pemeliharaan dan perbaikan mesin otomotif modern.');


CREATE TABLE `kelas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(50) NOT NULL,
  `jurusan_id` int(11) NOT NULL,
  `tingkat` enum('X','XI','XII') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jurusan_id` (`jurusan_id`),
  CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `kelas` VALUES('1', 'X RPL 1', '1', 'X');
INSERT INTO `kelas` VALUES('2', 'XI RPL 1', '1', 'XI');
INSERT INTO `kelas` VALUES('3', 'XII RPL 1', '1', 'XII');
INSERT INTO `kelas` VALUES('4', 'X TKJ 1', '2', 'X');
INSERT INTO `kelas` VALUES('5', 'XI TKJ 1', '2', 'XI');
INSERT INTO `kelas` VALUES('6', 'X DKV 1', '3', 'X');


CREATE TABLE `komentar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forum_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `komentar` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `forum_id` (`forum_id`),
  KEY `user_id` (`user_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `komentar_ibfk_1` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`id`) ON DELETE CASCADE,
  CONSTRAINT `komentar_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `komentar_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `komentar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `komentar` VALUES('1', '1', '3', NULL, 'Prepared Statement jauh lebih aman dari serangan SQL Injection Pak! Serta query execution plan bisa dire-use oleh MySQL server.', '2026-08-02 07:09:38');
INSERT INTO `komentar` VALUES('2', '1', '2', '1', 'Sangat tepat Rizky! Keamanan dan efisiensi query menjadi prioritas utama.', '2026-08-02 07:09:38');
INSERT INTO `komentar` VALUES('3', '3', '7', NULL, 'Baik Min, Bagaimana Kabar mimin Juga?', '2026-08-02 09:55:25');


CREATE TABLE `library` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `penulis` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` varchar(50) DEFAULT 'Umum',
  `kelas_target` varchar(100) DEFAULT NULL,
  `file_type` varchar(20) NOT NULL DEFAULT 'pdf',
  `file_path` varchar(255) NOT NULL,
  `file_size` bigint(20) DEFAULT 0,
  `uploader_id` int(11) NOT NULL,
  `view_count` int(11) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `log_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `status` enum('success','failed') NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `log_login_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `log_login` VALUES('1', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 07:11:43');
INSERT INTO `log_login` VALUES('2', '2', 'guru', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 07:13:18');
INSERT INTO `log_login` VALUES('3', '3', 'siswa', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 07:15:17');
INSERT INTO `log_login` VALUES('4', '4', 'kepsek', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 07:16:28');
INSERT INTO `log_login` VALUES('5', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 07:18:53');
INSERT INTO `log_login` VALUES('6', '2', 'guru', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 07:25:21');
INSERT INTO `log_login` VALUES('7', '3', 'siswa', 'success', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-08-02 07:28:14');
INSERT INTO `log_login` VALUES('8', '2', 'guru', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 07:40:59');
INSERT INTO `log_login` VALUES('9', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 07:42:09');
INSERT INTO `log_login` VALUES('10', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 08:11:05');
INSERT INTO `log_login` VALUES('11', '3', 'siswa', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 08:12:04');
INSERT INTO `log_login` VALUES('12', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 08:14:06');
INSERT INTO `log_login` VALUES('13', '4', 'kepsek', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 08:19:05');
INSERT INTO `log_login` VALUES('14', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 08:20:36');
INSERT INTO `log_login` VALUES('15', NULL, 'agung023', 'failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 08:44:07');
INSERT INTO `log_login` VALUES('16', '7', 'agung', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 08:44:39');
INSERT INTO `log_login` VALUES('17', '4', 'kepsek', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 09:03:58');
INSERT INTO `log_login` VALUES('18', '2', 'guru', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 09:25:09');
INSERT INTO `log_login` VALUES('19', '8', 'dwihan', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 09:43:33');
INSERT INTO `log_login` VALUES('20', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 09:48:29');
INSERT INTO `log_login` VALUES('21', '2', 'guru', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 09:49:16');
INSERT INTO `log_login` VALUES('22', '7', 'agung', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 09:54:57');


CREATE TABLE `mata_pelajaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_mapel` varchar(20) NOT NULL,
  `nama_mapel` varchar(100) NOT NULL,
  `jurusan_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_mapel` (`kode_mapel`),
  KEY `jurusan_id` (`jurusan_id`),
  CONSTRAINT `mata_pelajaran_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `mata_pelajaran` VALUES('1', 'MP01', 'Pemrograman Web dan Perangkat Bergerak', '1');
INSERT INTO `mata_pelajaran` VALUES('2', 'MP02', 'Pemodelan Perangkat Lunak', '1');
INSERT INTO `mata_pelajaran` VALUES('3', 'MP03', 'Administrasi Infrastruktur Jaringan', '2');
INSERT INTO `mata_pelajaran` VALUES('4', 'MP04', 'Desain Grafis Percetakan', '3');
INSERT INTO `mata_pelajaran` VALUES('5', 'MP05', 'Bahasa Indonesia', NULL);
INSERT INTO `mata_pelajaran` VALUES('6', 'MP06', 'Matematika Terapan', NULL);


CREATE TABLE `materi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `jenis_file` enum('pdf','doc','ppt','video','youtube','image','other') DEFAULT 'pdf',
  `file_path` varchar(255) DEFAULT NULL,
  `youtube_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `guru_id` (`guru_id`),
  KEY `mapel_id` (`mapel_id`),
  KEY `kelas_id` (`kelas_id`),
  CONSTRAINT `materi_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `materi_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `materi_ibfk_3` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `materi` VALUES('1', '1', '1', '2', 'Konsep dasar MVC pada PHP Native', 'Materi lengkap arsitektur Model-View-Controller dalam pembuatan web modern.', 'pdf', 'materi_mvc_php.pdf', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2026-08-02 07:09:38');
INSERT INTO `materi` VALUES('2', '2', '2', '2', 'Prinsip Object Oriented Programming (OOP)', 'Pemahaman class, object, inheritance, dan encapsulation dalam PHP 8.', 'ppt', 'materi_oop_php.pptx', NULL, '2026-08-02 07:09:38');


CREATE TABLE `nilai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL,
  `nilai_tugas` decimal(5,2) DEFAULT 0.00,
  `nilai_quiz` decimal(5,2) DEFAULT 0.00,
  `nilai_uts` decimal(5,2) DEFAULT 0.00,
  `nilai_uas` decimal(5,2) DEFAULT 0.00,
  `nilai_akhir` decimal(5,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `siswa_id` (`siswa_id`),
  KEY `mapel_id` (`mapel_id`),
  KEY `semester_id` (`semester_id`),
  KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  CONSTRAINT `nilai_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nilai_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nilai_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nilai_ibfk_4` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `pengumpulan_tugas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tugas_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `catatan_siswa` text DEFAULT NULL,
  `nilai` decimal(5,2) DEFAULT NULL,
  `komentar_guru` text DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `graded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tugas_id` (`tugas_id`),
  KEY `siswa_id` (`siswa_id`),
  CONSTRAINT `pengumpulan_tugas_ibfk_1` FOREIGN KEY (`tugas_id`) REFERENCES `tugas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengumpulan_tugas_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `pengumuman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `isi` text NOT NULL,
  `target_role` enum('all','guru','siswa','kepsek') DEFAULT 'all',
  `is_popup` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `pengumuman_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pengumuman` VALUES('1', '1', 'Selamat Datang di E-Learning SMK Muthia Harapan Cicalengka', 'Portal pembelajaran digital resmi SMK Muthia Harapan Cicalengka telah siap digunakan untuk kegiatan KBM online.', 'all', '1', '2026-08-02 07:09:38');
INSERT INTO `pengumuman` VALUES('2', '1', 'Jadwal Penilaian Tengah Semester (PTS) Ganjil', 'Diinformasikan kepada seluruh siswa dan guru bahwa PTS Ganjil akan dimulai pekan depan.', 'all', '0', '2026-08-02 07:09:38');


CREATE TABLE `pilihan_jawaban` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `soal_id` int(11) NOT NULL,
  `teks_pilihan` text NOT NULL,
  `is_benar` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `soal_id` (`soal_id`),
  CONSTRAINT `pilihan_jawaban_ibfk_1` FOREIGN KEY (`soal_id`) REFERENCES `soal` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pilihan_jawaban` VALUES('1', '1', 'Model View Controller', '1');
INSERT INTO `pilihan_jawaban` VALUES('2', '1', 'Main View Center', '0');
INSERT INTO `pilihan_jawaban` VALUES('3', '1', 'Modular Visual Code', '0');
INSERT INTO `pilihan_jawaban` VALUES('4', '1', 'Mode Variable Class', '0');
INSERT INTO `pilihan_jawaban` VALUES('5', '2', 'Koneksi database aman dengan Prepared Statements', '1');
INSERT INTO `pilihan_jawaban` VALUES('6', '2', 'Membuat UI responsive Bootstrap', '0');
INSERT INTO `pilihan_jawaban` VALUES('7', '2', 'Manipulasi gambar dan file PDF', '0');
INSERT INTO `pilihan_jawaban` VALUES('8', '4', 'Sarana Informasi Berbasis Digital', '1');
INSERT INTO `pilihan_jawaban` VALUES('9', '4', 'Kurang Tau', '0');
INSERT INTO `pilihan_jawaban` VALUES('10', '4', 'Sarana Informasi Berbasis Non Digital', '0');
INSERT INTO `pilihan_jawaban` VALUES('11', '4', 'Tidak Tau', '0');


CREATE TABLE `quiz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `durasi_menit` int(11) NOT NULL DEFAULT 30,
  `jumlah_soal` int(11) DEFAULT 10,
  `random_soal` enum('Y','N') DEFAULT 'Y',
  `random_jawaban` enum('Y','N') DEFAULT 'Y',
  `status` enum('draft','published','archived') DEFAULT 'published',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `guru_id` (`guru_id`),
  KEY `mapel_id` (`mapel_id`),
  KEY `kelas_id` (`kelas_id`),
  CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_ibfk_3` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `quiz` VALUES('1', '1', '1', '2', 'Kuis 1 - PHP & Framework Basics', 'Kuis evaluasi pemahaman dasar PHP Native 8 dan MVC Architecture.', '30', '3', 'Y', 'Y', 'published', '2026-08-02 07:09:38');
INSERT INTO `quiz` VALUES('2', '1', '1', '1', 'Ujian Harian', 'Kerjakan Sesuai Dengan Soal Yang Dibawah', '5', '0', 'Y', 'Y', 'published', '2026-08-02 07:27:34');


CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` VALUES('1', 'Administrator', 'Sistem Administrator / Operator Sekolah');
INSERT INTO `roles` VALUES('2', 'Guru', 'Tenaga Pengajar SMK Muthia Harapan Cicalengka');
INSERT INTO `roles` VALUES('3', 'Siswa', 'Peserta Didik SMK Muthia Harapan Cicalengka');
INSERT INTO `roles` VALUES('4', 'Kepala Sekolah', 'Pimpinan / Kepala Sekolah');


CREATE TABLE `semester` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_semester` enum('Ganjil','Genap') NOT NULL,
  `status` enum('aktif','non-aktif') DEFAULT 'aktif',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `semester` VALUES('1', 'Ganjil', 'aktif');
INSERT INTO `semester` VALUES('2', 'Genap', 'non-aktif');


CREATE TABLE `sertifikat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `judul_sertifikat` varchar(150) NOT NULL,
  `nomor_sertifikat` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `issued_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_sertifikat` (`nomor_sertifikat`),
  KEY `siswa_id` (`siswa_id`),
  CONSTRAINT `sertifikat_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `settings` VALUES('alamat', 'Jalan Babakan Peuteuy Nomor 300, Desa Babakanpeuteuy, Kecamatan Cicalengka, Kabupaten Bandung, Jawa', '2026-08-02 08:37:01');
INSERT INTO `settings` VALUES('api_key', 'smkmh_live_api_88923a19e83c7410294b', '2026-08-02 08:30:08');
INSERT INTO `settings` VALUES('kepala_sekolah', 'H. ASEP SAEPULLOH, S. Ag', '2026-08-02 08:37:01');
INSERT INTO `settings` VALUES('logo', 'logo_1785634621_6a6e9f3d37d9e.jpg', '2026-08-02 08:37:01');
INSERT INTO `settings` VALUES('nama_sekolah', 'SMK Muthia Harapan Cicalengka', '2026-08-02 08:37:01');
INSERT INTO `settings` VALUES('npsn', '69725846', '2026-08-02 08:37:01');
INSERT INTO `settings` VALUES('sertifikat_active_template', 'kelulusan', '2026-08-02 09:12:36');
INSERT INTO `settings` VALUES('smtp_crypto', 'tls', '2026-08-02 08:30:08');
INSERT INTO `settings` VALUES('smtp_host', 'smtp.gmail.com', '2026-08-02 08:30:08');
INSERT INTO `settings` VALUES('smtp_pass', '••••••••••••', '2026-08-02 08:30:08');
INSERT INTO `settings` VALUES('smtp_port', '587', '2026-08-02 08:30:08');
INSERT INTO `settings` VALUES('smtp_user', 'elearning@smkmuthiaharapan.sch.id', '2026-08-02 08:30:08');
INSERT INTO `settings` VALUES('telepon', '(022) 7950123', '2026-08-02 08:37:01');
INSERT INTO `settings` VALUES('tema', 'light', '2026-08-02 08:30:08');


CREATE TABLE `siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nis` varchar(30) DEFAULT NULL,
  `nisn` varchar(30) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `jurusan_id` int(11) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status` enum('aktif','alumni','drop') DEFAULT 'aktif',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `nis` (`nis`),
  UNIQUE KEY `nisn` (`nisn`),
  KEY `kelas_id` (`kelas_id`),
  KEY `jurusan_id` (`jurusan_id`),
  CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `siswa_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `siswa_ibfk_3` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `siswa` VALUES('1', '3', '20231001', '0061234567', 'Muhammad Rizky Pratama', '2', '1', 'L', '085712345678', 'Jl. Cikopo Cicalengka No. 8', 'aktif');
INSERT INTO `siswa` VALUES('2', '6', '20231002', '0067654321', 'Siti Rahmawati', '2', '1', 'P', '085798765432', 'Jl. Nagreg No. 15, Bandung', 'aktif');
INSERT INTO `siswa` VALUES('3', '7', '522402055', '522402055', 'AGUNG RIFALDI', '1', '1', 'L', '082317864874', '', 'aktif');
INSERT INTO `siswa` VALUES('4', '8', '522402056', '522402056', 'Dwi Handoko', '1', '1', 'L', '82366965785', '', 'aktif');


CREATE TABLE `soal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `jenis_soal` enum('pg','essay','tf') NOT NULL DEFAULT 'pg',
  `pertanyaan` text NOT NULL,
  `file_gambar` varchar(255) DEFAULT NULL,
  `bobot` int(11) DEFAULT 10,
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `soal_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `soal` VALUES('1', '1', 'pg', 'Apa kepanjangan dari MVC dalam pengembangan perangkat lunak?', NULL, '35');
INSERT INTO `soal` VALUES('2', '1', 'pg', 'Fungsi utama PDO pada PHP 8 adalah untuk?', NULL, '35');
INSERT INTO `soal` VALUES('3', '1', 'essay', 'Jelaskan perbedaan mendasar antara HTTP GET dan POST!', NULL, '30');
INSERT INTO `soal` VALUES('4', '2', 'pg', 'Apa yang Dimaksud Dengan Website ?', NULL, '100');


CREATE TABLE `tahun_ajaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tahun` varchar(20) NOT NULL,
  `status` enum('aktif','non-aktif') DEFAULT 'aktif',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tahun` (`tahun`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tahun_ajaran` VALUES('1', '2025/2026', 'aktif');
INSERT INTO `tahun_ajaran` VALUES('2', '2024/2025', 'non-aktif');


CREATE TABLE `tugas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `deadline` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `guru_id` (`guru_id`),
  KEY `mapel_id` (`mapel_id`),
  KEY `kelas_id` (`kelas_id`),
  CONSTRAINT `tugas_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tugas_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tugas_ibfk_3` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tugas` VALUES('1', '1', '1', '2', 'Tugas 1: Membuat Auth Login MVC', 'Silakan buat modul login lengkap dengan CSRF token dan Session Handling.', 'panduan_tugas1.pdf', '2026-08-09 07:09:38', '2026-08-02 07:09:38');


CREATE TABLE `ujian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `nama_ujian` varchar(150) NOT NULL,
  `jenis_ujian` enum('UTS','UAS','PAT','US') DEFAULT 'UTS',
  `durasi_menit` int(11) NOT NULL DEFAULT 60,
  `tgl_mulai` datetime NOT NULL,
  `tgl_selesai` datetime NOT NULL,
  `token_ujian` varchar(10) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `guru_id` (`guru_id`),
  KEY `mapel_id` (`mapel_id`),
  KEY `kelas_id` (`kelas_id`),
  CONSTRAINT `ujian_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ujian_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ujian_ibfk_3` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ujian` VALUES('1', '1', '1', '2', 'Ujian Tengah Semester (UTS) Web Programming', 'UTS', '60', '2026-08-02 07:09:38', '2026-09-01 07:09:38', 'SMKMH1', '1');


CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `avatar` varchar(255) DEFAULT 'default_avatar.png',
  `status` enum('active','inactive','blocked') DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` VALUES('1', '1', 'admin', 'admin@smkmh-cicalengka.sch.id', '$2y$10$qS2iG1GqV2L3N.a5v7Z0eeR2h8kL4A2.v7qB6C8m9D0e1F2g3H4i5', 'Administrator Utama', 'default_avatar.png', 'active', NULL, '2026-08-02 07:09:37', '2026-08-02 07:09:37');
INSERT INTO `users` VALUES('2', '2', 'guru', 'guru@smkmh-cicalengka.sch.id', '$2y$10$qS2iG1GqV2L3N.a5v7Z0eeR2h8kL4A2.v7qB6C8m9D0e1F2g3H4i5', 'Drs. Ahmad Hidayat, M.Pd.', 'guru_avatar.png', 'active', NULL, '2026-08-02 07:09:37', '2026-08-02 07:09:37');
INSERT INTO `users` VALUES('3', '3', 'siswa', 'siswa@smkmh-cicalengka.sch.id', '$2y$10$qS2iG1GqV2L3N.a5v7Z0eeR2h8kL4A2.v7qB6C8m9D0e1F2g3H4i5', 'Muhammad Rizky Pratama', 'siswa_avatar.png', 'active', NULL, '2026-08-02 07:09:37', '2026-08-02 07:09:37');
INSERT INTO `users` VALUES('4', '4', 'kepsek', 'kepsek@smkmh-cicalengka.sch.id', '$2y$10$qS2iG1GqV2L3N.a5v7Z0eeR2h8kL4A2.v7qB6C8m9D0e1F2g3H4i5', 'H. Supriyadi, M.M.', 'kepsek_avatar.png', 'active', NULL, '2026-08-02 07:09:37', '2026-08-02 07:09:37');
INSERT INTO `users` VALUES('5', '2', 'guru2', 'budi@smkmh-cicalengka.sch.id', '$2y$10$qS2iG1GqV2L3N.a5v7Z0eeR2h8kL4A2.v7qB6C8m9D0e1F2g3H4i5', 'Budi Santoso, S.T.', 'default_avatar.png', 'active', NULL, '2026-08-02 07:09:37', '2026-08-02 07:09:37');
INSERT INTO `users` VALUES('6', '3', 'siswa2', 'siti@smkmh-cicalengka.sch.id', '$2y$10$qS2iG1GqV2L3N.a5v7Z0eeR2h8kL4A2.v7qB6C8m9D0e1F2g3H4i5', 'Siti Rahmawati', 'default_avatar.png', 'active', NULL, '2026-08-02 07:09:37', '2026-08-02 07:09:37');
INSERT INTO `users` VALUES('7', '3', 'agung', 'aggrfld23@gmail.com', '$2y$10$ffyyoF7s5fdEyjECXNCSk.LGoWI8Td5pMvn0AM3U5ieZL3rQy3Nq6', 'AGUNG RIFALDI', 'default_avatar.png', 'active', NULL, '2026-08-02 08:41:59', '2026-08-02 08:41:59');
INSERT INTO `users` VALUES('8', '3', 'dwihan', 'dwihan@gmail.com', '$2y$10$a6FdhD.jAlOsYgHuyiQdQO.VuN193SooQ73RGZemL5vCIUiwstA1i', 'Dwi Handoko', 'default_avatar.png', 'active', NULL, '2026-08-02 09:43:03', '2026-08-02 09:43:03');
INSERT INTO `users` VALUES('9', '2', 'agg023', 'agg023@smkmh-cicalengka.sch.id', '$2y$10$xvO261uFDFZjnnLWJreADuRMkd.ne/VgZoNpwUONqtxbVqAv98DiW', 'AGUNG RIFALDI, S.Tr. Kom', 'default_avatar.png', 'active', NULL, '2026-08-02 09:48:07', '2026-08-02 09:48:07');


CREATE TABLE `video` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `youtube_id` varchar(50) DEFAULT NULL,
  `duration` varchar(20) DEFAULT '00:00',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `guru_id` (`guru_id`),
  KEY `mapel_id` (`mapel_id`),
  CONSTRAINT `video_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `video_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

