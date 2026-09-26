-- ========================================================
-- Database Backup: E-Learning SMK Muthia Harapan Cicalengka
-- Date: 2026-09-19 10:34:35
-- Type: AUTO
-- Note: Otomatisasi Backup pasca aktivitas Guru (Guru Simulasi) - Source: user_activity
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Table structure for `absensi` --
DROP TABLE IF EXISTS `absensi`;
CREATE TABLE `absensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jadwal_id` int(11) DEFAULT NULL,
  `siswa_id` int(11) NOT NULL,
  `guru_id` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `waktu_masuk` datetime DEFAULT NULL,
  `waktu_pulang` datetime DEFAULT NULL,
  `waktu_hadir` datetime DEFAULT current_timestamp(),
  `status` enum('Hadir','Izin','Sakit','Alpa') DEFAULT 'Hadir',
  `qr_code` varchar(255) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `jadwal_id` (`jadwal_id`),
  KEY `siswa_id` (`siswa_id`),
  CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal` (`id`) ON DELETE CASCADE,
  CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `absensi` --
INSERT INTO `absensi` VALUES('1', NULL, '3', '3', '2026-08-26', '2026-08-26 23:28:46', NULL, '2026-08-26 23:28:46', 'Hadir', 'MANUAL_3_20260826232846', 'Presensi Manual Guru - Tidak bawa HP', '2026-08-26 23:28:46');

-- Table structure for `absensi_guru` --
DROP TABLE IF EXISTS `absensi_guru`;
CREATE TABLE `absensi_guru` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu_masuk` datetime DEFAULT NULL,
  `foto_masuk` varchar(255) DEFAULT NULL,
  `latitude_masuk` decimal(10,8) DEFAULT NULL,
  `longitude_masuk` decimal(11,8) DEFAULT NULL,
  `jarak_masuk_meter` int(11) DEFAULT NULL,
  `waktu_pulang` datetime DEFAULT NULL,
  `foto_pulang` varchar(255) DEFAULT NULL,
  `latitude_pulang` decimal(10,8) DEFAULT NULL,
  `longitude_pulang` decimal(11,8) DEFAULT NULL,
  `jarak_pulang_meter` int(11) DEFAULT NULL,
  `waktu_hadir` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Hadir',
  `tipe_presensi` varchar(30) DEFAULT 'selfie',
  `qr_code` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `guru_id` (`guru_id`),
  KEY `tanggal` (`tanggal`),
  KEY `qr_code` (`qr_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `aktivitas` --
DROP TABLE IF EXISTS `aktivitas`;
CREATE TABLE `aktivitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `activity` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `aktivitas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `aktivitas` --
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
INSERT INTO `aktivitas` VALUES('41', '7', 'Logout dari sistem', '::1', '2026-08-02 10:21:22');
INSERT INTO `aktivitas` VALUES('42', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-02 10:22:00');
INSERT INTO `aktivitas` VALUES('43', '1', 'Logout dari sistem', '::1', '2026-08-02 11:22:43');
INSERT INTO `aktivitas` VALUES('44', '9', 'Logout dari sistem', '::1', '2026-08-02 11:23:00');
INSERT INTO `aktivitas` VALUES('45', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-02 20:55:02');
INSERT INTO `aktivitas` VALUES('46', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-02 21:28:35');
INSERT INTO `aktivitas` VALUES('47', '1', 'Logout dari sistem', '::1', '2026-08-02 21:37:24');
INSERT INTO `aktivitas` VALUES('48', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-02 21:37:46');
INSERT INTO `aktivitas` VALUES('49', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-02 21:45:11');
INSERT INTO `aktivitas` VALUES('50', '7', 'Login ke sistem sebagai Siswa', '::1', '2026-08-02 22:01:09');
INSERT INTO `aktivitas` VALUES('51', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-02 22:38:30');
INSERT INTO `aktivitas` VALUES('52', '7', 'Logout dari sistem', '::1', '2026-08-02 23:15:18');
INSERT INTO `aktivitas` VALUES('53', '3', 'Login ke sistem sebagai Siswa', '::1', '2026-08-02 23:15:42');
INSERT INTO `aktivitas` VALUES('54', '3', 'Logout dari sistem', '::1', '2026-08-02 23:19:48');
INSERT INTO `aktivitas` VALUES('55', '7', 'Login ke sistem sebagai Siswa', '::1', '2026-08-02 23:20:01');
INSERT INTO `aktivitas` VALUES('56', '9', 'Logout dari sistem', '::1', '2026-08-02 23:38:29');
INSERT INTO `aktivitas` VALUES('57', '1', 'Logout dari sistem', '::1', '2026-08-02 23:38:40');
INSERT INTO `aktivitas` VALUES('58', '7', 'Logout dari sistem', '::1', '2026-08-02 23:38:44');
INSERT INTO `aktivitas` VALUES('59', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-03 06:42:24');
INSERT INTO `aktivitas` VALUES('60', '9', 'Logout dari sistem', '::1', '2026-08-03 06:43:07');
INSERT INTO `aktivitas` VALUES('61', '7', 'Login ke sistem sebagai Siswa', '::1', '2026-08-03 06:43:23');
INSERT INTO `aktivitas` VALUES('62', '7', 'Logout dari sistem', '::1', '2026-08-03 06:44:56');
INSERT INTO `aktivitas` VALUES('63', '4', 'Login ke sistem sebagai Kepala Sekolah', '::1', '2026-08-03 06:45:11');
INSERT INTO `aktivitas` VALUES('64', '4', 'Logout dari sistem', '::1', '2026-08-03 07:01:32');
INSERT INTO `aktivitas` VALUES('65', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-03 07:01:51');
INSERT INTO `aktivitas` VALUES('66', '9', 'Logout dari sistem', '::1', '2026-08-03 07:02:13');
INSERT INTO `aktivitas` VALUES('67', '4', 'Login ke sistem sebagai Kepala Sekolah', '::1', '2026-08-03 07:02:41');
INSERT INTO `aktivitas` VALUES('68', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-03 07:13:16');
INSERT INTO `aktivitas` VALUES('69', '1', 'Logout dari sistem', '::1', '2026-08-03 07:15:18');
INSERT INTO `aktivitas` VALUES('70', '7', 'Login ke sistem sebagai Siswa', '::1', '2026-08-03 07:15:40');
INSERT INTO `aktivitas` VALUES('71', '4', 'Logout dari sistem', '::1', '2026-08-03 08:35:39');
INSERT INTO `aktivitas` VALUES('72', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-03 08:35:51');
INSERT INTO `aktivitas` VALUES('73', '7', 'Logout dari sistem', '::1', '2026-08-03 09:17:03');
INSERT INTO `aktivitas` VALUES('74', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-03 09:17:21');
INSERT INTO `aktivitas` VALUES('75', '9', 'Logout dari sistem', '::1', '2026-08-03 09:23:21');
INSERT INTO `aktivitas` VALUES('76', '1', 'Logout dari sistem', '::1', '2026-08-03 09:23:27');
INSERT INTO `aktivitas` VALUES('77', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-03 15:33:10');
INSERT INTO `aktivitas` VALUES('78', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-03 15:50:52');
INSERT INTO `aktivitas` VALUES('79', '7', 'Login ke sistem sebagai Siswa', '::1', '2026-08-03 15:51:39');
INSERT INTO `aktivitas` VALUES('80', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-04 14:27:30');
INSERT INTO `aktivitas` VALUES('81', '1', 'Logout dari sistem', '::1', '2026-08-04 14:27:37');
INSERT INTO `aktivitas` VALUES('82', '1', 'Login ke sistem sebagai Administrator', '127.0.0.1', '2026-08-04 15:01:19');
INSERT INTO `aktivitas` VALUES('83', '1', 'Logout dari sistem', '127.0.0.1', '2026-08-04 15:02:03');
INSERT INTO `aktivitas` VALUES('84', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-04 15:02:19');
INSERT INTO `aktivitas` VALUES('85', '7', 'Login ke sistem sebagai Siswa', '::1', '2026-08-04 15:15:55');
INSERT INTO `aktivitas` VALUES('86', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-04 17:10:52');
INSERT INTO `aktivitas` VALUES('87', '7', 'Login ke sistem sebagai Siswa', '::1', '2026-08-04 17:11:19');
INSERT INTO `aktivitas` VALUES('88', '2', 'Login ke sistem sebagai Guru', '::1', '2026-08-04 18:17:49');
INSERT INTO `aktivitas` VALUES('89', '2', 'Logout dari sistem', '::1', '2026-08-04 18:30:07');
INSERT INTO `aktivitas` VALUES('90', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-04 18:30:54');
INSERT INTO `aktivitas` VALUES('91', '9', 'Logout dari sistem', '::1', '2026-08-04 18:33:29');
INSERT INTO `aktivitas` VALUES('92', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-04 18:33:39');
INSERT INTO `aktivitas` VALUES('93', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-04 18:58:30');
INSERT INTO `aktivitas` VALUES('94', '4', 'Login ke sistem sebagai Kepala Sekolah', '::1', '2026-08-04 20:47:20');
INSERT INTO `aktivitas` VALUES('95', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-05 12:14:14');
INSERT INTO `aktivitas` VALUES('96', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-05 12:14:58');
INSERT INTO `aktivitas` VALUES('97', '7', 'Login ke sistem sebagai Siswa', '::1', '2026-08-05 12:20:17');
INSERT INTO `aktivitas` VALUES('98', '4', 'Login ke sistem sebagai Kepala Sekolah', '::1', '2026-08-05 12:48:20');
INSERT INTO `aktivitas` VALUES('99', '7', 'Logout dari sistem', '::1', '2026-08-05 12:52:53');
INSERT INTO `aktivitas` VALUES('100', '6', 'Login ke sistem sebagai Siswa', '::1', '2026-08-05 12:53:10');
INSERT INTO `aktivitas` VALUES('101', '6', 'Logout dari sistem', '::1', '2026-08-05 12:58:13');
INSERT INTO `aktivitas` VALUES('102', '10', 'Login ke sistem sebagai Siswa', '::1', '2026-08-05 12:58:35');
INSERT INTO `aktivitas` VALUES('103', '9', 'Logout dari sistem', '::1', '2026-08-05 13:47:46');
INSERT INTO `aktivitas` VALUES('104', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-05 13:47:58');
INSERT INTO `aktivitas` VALUES('105', '10', 'Logout dari sistem', '::1', '2026-08-05 13:48:53');
INSERT INTO `aktivitas` VALUES('106', '7', 'Login ke sistem sebagai Siswa', '::1', '2026-08-05 13:49:11');
INSERT INTO `aktivitas` VALUES('107', '9', 'Logout dari sistem', '::1', '2026-08-05 13:55:20');
INSERT INTO `aktivitas` VALUES('108', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-05 13:55:47');
INSERT INTO `aktivitas` VALUES('109', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-05 15:39:12');
INSERT INTO `aktivitas` VALUES('110', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-05 20:14:44');
INSERT INTO `aktivitas` VALUES('111', '7', 'Login ke sistem sebagai Siswa', '::1', '2026-08-05 20:42:10');
INSERT INTO `aktivitas` VALUES('112', '4', 'Login ke sistem sebagai Kepala Sekolah', '::1', '2026-08-05 21:01:50');
INSERT INTO `aktivitas` VALUES('113', '4', 'Logout dari sistem', '::1', '2026-08-05 21:02:44');
INSERT INTO `aktivitas` VALUES('114', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-05 21:02:57');
INSERT INTO `aktivitas` VALUES('115', '9', 'Logout dari sistem', '::1', '2026-08-06 00:30:37');
INSERT INTO `aktivitas` VALUES('116', '7', 'Logout dari sistem', '::1', '2026-08-06 00:30:42');
INSERT INTO `aktivitas` VALUES('117', '1', 'Logout dari sistem', '::1', '2026-08-06 00:30:46');
INSERT INTO `aktivitas` VALUES('118', '9', 'Login ke sistem sebagai Guru', '::1', '2026-08-10 18:54:56');
INSERT INTO `aktivitas` VALUES('119', '1', 'Login ke sistem sebagai Administrator', '::1', '2026-08-10 21:28:15');
INSERT INTO `aktivitas` VALUES('120', '7', 'Reset password akun secara mandiri via Lupa Password', '::1', '2026-08-11 21:36:40');
INSERT INTO `aktivitas` VALUES('121', '7', 'Login ke sistem sebagai Siswa', '::1', '2026-08-11 21:37:12');
INSERT INTO `aktivitas` VALUES('122', '7', 'Logout dari sistem', '::1', '2026-08-11 21:37:19');
INSERT INTO `aktivitas` VALUES('123', '7', 'Login ke sistem sebagai Siswa', '::1', '2026-08-11 21:50:02');
INSERT INTO `aktivitas` VALUES('124', '7', 'Logout dari sistem', '::1', '2026-08-11 22:12:02');

-- Table structure for `asesmen` --
DROP TABLE IF EXISTS `asesmen`;
CREATE TABLE `asesmen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tahun_ajaran_id` int(11) NOT NULL,
  `semester` varchar(20) NOT NULL DEFAULT 'Ganjil',
  `rombel_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `kurikulum_id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `cp_id` int(11) DEFAULT NULL,
  `tp_id` int(11) DEFAULT NULL,
  `jenis_asesmen` varchar(50) NOT NULL DEFAULT 'formatif',
  `nama_asesmen` varchar(150) NOT NULL,
  `tanggal` date NOT NULL,
  `nilai_maksimum` decimal(5,2) DEFAULT 100.00,
  `bobot` decimal(5,2) DEFAULT 1.00,
  `keterangan` text DEFAULT NULL,
  `ref_tugas_id` int(11) DEFAULT NULL,
  `ref_quiz_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  KEY `rombel_id` (`rombel_id`),
  KEY `mapel_id` (`mapel_id`),
  KEY `kurikulum_id` (`kurikulum_id`),
  KEY `guru_id` (`guru_id`),
  KEY `cp_id` (`cp_id`),
  KEY `tp_id` (`tp_id`),
  CONSTRAINT `asesmen_ibfk_1` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asesmen_ibfk_2` FOREIGN KEY (`rombel_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asesmen_ibfk_3` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asesmen_ibfk_4` FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asesmen_ibfk_5` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asesmen_ibfk_6` FOREIGN KEY (`cp_id`) REFERENCES `capaian_pembelajaran` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asesmen_ibfk_7` FOREIGN KEY (`tp_id`) REFERENCES `tujuan_pembelajaran` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `capaian_pembelajaran` --
DROP TABLE IF EXISTS `capaian_pembelajaran`;
CREATE TABLE `capaian_pembelajaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kurikulum_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `fase_id` int(11) DEFAULT NULL,
  `guru_id` int(11) DEFAULT NULL,
  `kode_cp` varchar(50) NOT NULL,
  `elemen` varchar(150) DEFAULT NULL,
  `deskripsi` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `kurikulum_id` (`kurikulum_id`),
  KEY `mapel_id` (`mapel_id`),
  KEY `fase_id` (`fase_id`),
  KEY `idx_cp_guru` (`guru_id`),
  CONSTRAINT `capaian_pembelajaran_ibfk_1` FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum` (`id`) ON DELETE CASCADE,
  CONSTRAINT `capaian_pembelajaran_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `capaian_pembelajaran_ibfk_3` FOREIGN KEY (`fase_id`) REFERENCES `fase` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `capaian_pembelajaran` --
INSERT INTO `capaian_pembelajaran` VALUES('1', '1', '1', '1', NULL, 'CP-PPLG-01', 'Pemrograman Berorientasi Objek & Web', 'Peserta didik mampu memahami konsep dasar arsitektur web modern, basis data relasional, dan implementasi logika sistem terpadu.', '2026-09-19 08:47:06', '2026-09-19 08:47:06');

-- Table structure for `chat` --
DROP TABLE IF EXISTS `chat`;
CREATE TABLE `chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_edited` tinyint(1) DEFAULT 0,
  `deleted_by_sender` tinyint(1) DEFAULT 0,
  `deleted_by_receiver` tinyint(1) DEFAULT 0,
  `is_deleted_everyone` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `chat` --
INSERT INTO `chat` VALUES('21', '9', '7', 'kamu kalau ngerjain jangan keluar tab terus kamu searcing ya cari jawaban?', '1', '2026-08-04 17:57:16', '0', '0', '1', '0');
INSERT INTO `chat` VALUES('22', '7', '9', 'baik pa mohon maaf', '1', '2026-08-04 17:57:29', '0', '1', '0', '0');
INSERT INTO `chat` VALUES('23', '7', '9', 'Pesan uji coba untuk tes fitur hapus pesan.', '0', '2026-08-10 20:59:44', '0', '1', '0', '1');
INSERT INTO `chat` VALUES('24', '7', '9', 'Pesan uji coba hapus untuk saya.', '0', '2026-08-10 20:59:44', '0', '1', '0', '0');

-- Table structure for `fase` --
DROP TABLE IF EXISTS `fase`;
CREATE TABLE `fase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kurikulum_id` int(11) NOT NULL,
  `kode` varchar(30) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tingkat_kelas` varchar(50) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_kurikulum_fase` (`kurikulum_id`,`kode`),
  CONSTRAINT `fase_ibfk_1` FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `fase` --
INSERT INTO `fase` VALUES('1', '1', 'E', 'Fase E (Kelas X)', 'X', 'Fase Fondasi Kejuruan untuk peserta didik kelas X SMK.', '2026-09-19 08:47:06', '2026-09-19 08:47:06');
INSERT INTO `fase` VALUES('2', '1', 'F', 'Fase F (Kelas XI - XII)', 'XI,XII', 'Fase Konsentrasi & Pendalaman Kejuruan untuk peserta didik kelas XI dan XII SMK.', '2026-09-19 08:47:06', '2026-09-19 08:47:06');

-- Table structure for `forum` --
DROP TABLE IF EXISTS `forum`;
CREATE TABLE `forum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `mapel_id` int(11) DEFAULT NULL,
  `judul` varchar(150) NOT NULL,
  `konten` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `likes_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `visibility` enum('public','private') DEFAULT 'public',
  `target_role` varchar(50) DEFAULT NULL,
  `target_kelas_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `mapel_id` (`mapel_id`),
  CONSTRAINT `forum_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `forum_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `forum` --
INSERT INTO `forum` VALUES('8', '9', NULL, 'Test Multi-Emoji Reactions Topic', 'Uraian testing reaksi emoji ❤️ 👍 😂 😢 😮 🔥', NULL, '0', '2026-08-10 22:06:57', 'public', NULL, NULL);

-- Table structure for `forum_reactions` --
DROP TABLE IF EXISTS `forum_reactions`;
CREATE TABLE `forum_reactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forum_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reaction_type` enum('love','like','laugh','sad','wow','fire') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_forum` (`forum_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `forum_reactions_ibfk_1` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`id`) ON DELETE CASCADE,
  CONSTRAINT `forum_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `forum_reactions` --
INSERT INTO `forum_reactions` VALUES('2', '8', '7', 'fire', '2026-08-10 22:06:57');

-- Table structure for `game_edukasi` --
DROP TABLE IF EXISTS `game_edukasi`;
CREATE TABLE `game_edukasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tipe_game` enum('quiz_speed','spin_wheel','memory_match') DEFAULT 'quiz_speed',
  `durasi_per_soal` int(11) DEFAULT 15,
  `kkm` int(11) DEFAULT 75,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `game_edukasi` --
INSERT INTO `game_edukasi` VALUES('1', '1', '1', NULL, 'Quis Dasar Dasar Pengembangan Perangkat Lunak Dan Gim', 'Pengembangan Perangkat Lunak Dan Gim (DDPK)', 'quiz_speed', '15', '75', '2026-08-11 19:25:01');
INSERT INTO `game_edukasi` VALUES('2', '1', '1', NULL, 'Tantangan Master Web Programming & Database 🚀', 'Uji kecepatan dan ketepatan Anda dalam menjawab kuis interaktif seputar HTML, PHP Prepared Statements, dan SQL Database!', 'quiz_speed', '15', '70', '2026-08-10 22:31:37');

-- Table structure for `game_skor` --
DROP TABLE IF EXISTS `game_skor`;
CREATE TABLE `game_skor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `skor_akhir` int(11) NOT NULL,
  `max_combo` int(11) DEFAULT 0,
  `total_benar` int(11) NOT NULL,
  `total_soal` int(11) NOT NULL,
  `waktu_selesai` int(11) NOT NULL,
  `status_lulus` enum('lulus','tidak_lulus') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`),
  CONSTRAINT `game_skor_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `game_edukasi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `game_soal` --
DROP TABLE IF EXISTS `game_soal`;
CREATE TABLE `game_soal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `pertanyaan` text NOT NULL,
  `opsi_a` varchar(255) NOT NULL,
  `opsi_b` varchar(255) NOT NULL,
  `opsi_c` varchar(255) NOT NULL,
  `opsi_d` varchar(255) NOT NULL,
  `kunci_jawaban` enum('a','b','c','d') NOT NULL,
  `poin` int(11) DEFAULT 10,
  `penjelasan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`),
  CONSTRAINT `game_soal_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `game_edukasi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `game_soal` --
INSERT INTO `game_soal` VALUES('4', '2', 'Fitur keamanan apakah yang paling efektif mencegah serangan SQL Injection pada aplikasi PHP?', 'PDO Prepared Statements', 'HTML Formatting', 'CSS Flexbox Styling', 'JavaScript Alert Box', 'a', '25', 'PDO Prepared Statements memisahkan query SQL dari data masukan user sehingga mencegah eksekusi injeksi kode.');
INSERT INTO `game_soal` VALUES('5', '2', 'Simbol apakah yang digunakan untuk mendeklarasikan variabel dalam bahasa pemrograman PHP?', 'Tanda Dolar ($)', 'Tanda Pagar (#)', 'Tanda Persen (%)', 'Tanda Ampersand (&)', 'a', '25', 'Semua variabel dalam PHP selalu diawali dengan simbol tanda dolar ($).');
INSERT INTO `game_soal` VALUES('6', '2', 'Metode HTTP apakah yang paling aman digunakan untuk mengirimkan kata sandi pada form login?', 'POST', 'GET', 'PUT', 'OPTIONS', 'a', '25', 'Metode POST menyembunyikan data sensitif di dalam request body dan tidak menampilkannya pada URL browser.');
INSERT INTO `game_soal` VALUES('7', '2', 'Manakah perintah SQL yang digunakan untuk memperbarui data record yang sudah ada di tabel?', 'UPDATE', 'SELECT', 'INSERT', 'ALTER', 'a', '25', 'Perintah UPDATE digunakan untuk memperbarui atau mengedit baris data yang ada pada database.');
INSERT INTO `game_soal` VALUES('8', '1', 'Apa tujuan utama dari mata pelajaran Pemrograman Web dan Perangkat Bergerak dalam pembelajaran?', 'Mengembangkan keterampilan & pemahaman mendalam secara praktis', 'Menghafal teori tanpa melakukan praktik langsung', 'Hanya untuk formalitas kelengkapan nilai ujian', 'Mengabaikan standar kompetensi kelulusan', 'a', '25', 'Pembelajaran bertujuan membangun kompetensi keterampilan dasar dan pemahaman mendalam.');
INSERT INTO `game_soal` VALUES('9', '1', 'Manakah langkah pertama yang paling tepat sebelum memulai pembuatan proyek dalam bidang Pemrograman Web dan Perangkat Bergerak?', 'Perencanaan, Perancangan & Analisis Kebutuhan', 'Langsung membuat produk tanpa adanya perencanaan', 'Menunggu instruksi tanpa persiapan materi', 'Menutup seluruh dokumentasi teknis', 'a', '25', 'Perencanaan dan analisis kebutuhan adalah fondasi utama keberhasilan setiap proyek.');
INSERT INTO `game_soal` VALUES('10', '1', 'Sikap manakah yang mencerminkan etika kerja & belajar profesional?', 'Disiplin, Jujur, Tanggung Jawab & Kerjasama Tim', 'Mengcopy karya orang lain tanpa izin', 'Apatis terhadap pencapaian proyek bersama', 'Mengabaikan tenggat waktu deadline yang disepakati', 'a', '25', 'Disiplin, kejujuran, dan integritas adalah pilar etika kerja profesional.');
INSERT INTO `game_soal` VALUES('11', '1', 'Bagaimanakah cara terbaik untuk mengevaluasi keberhasilan suatu tugas atau karya?', 'Melakukan Pengujian (Testing) & Reviu Umpan Balik', 'Menganggap karya langsung sempurna tanpa diuji', 'Menghindari kritik dan perbaikan karya', 'Menghapus hasil pekerjaan sebelum dinilai', 'a', '25', 'Pengujian (testing) dan reviu umpan balik memastikan kualitas akhir memenuhi kriteria.');

-- Table structure for `guru` --
DROP TABLE IF EXISTS `guru`;
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `guru` --
INSERT INTO `guru` VALUES('1', '2', '198501152010011002', 'Drs. Ahmad Hidayat, M.Pd.', 'L', '081234567890', 'Jl. Raya Cicalengka No. 45, Bandung', 'aktif');
INSERT INTO `guru` VALUES('2', '5', '199003202015021004', 'Budi Santoso, S.T.', 'L', '082198765432', 'Jl. Alun-Alun Cicalengka No. 12', 'aktif');
INSERT INTO `guru` VALUES('3', '9', '32042523010400001', 'AGUNG RIFALDI, S.Tr. Kom', 'L', '82198765433', 'Kp. Munggang Rt. 01/08 Desa Dampit Kec. Cicalengka Kab. Bandung', 'aktif');
INSERT INTO `guru` VALUES('11', '1', 'G202608810', 'Administrator Utama', 'L', NULL, NULL, 'aktif');
INSERT INTO `guru` VALUES('12', '4', 'G202608503', 'H. ASEP SAEPULLOH, S.Ag', 'L', NULL, NULL, 'aktif');

-- Table structure for `hasil_quiz` --
DROP TABLE IF EXISTS `hasil_quiz`;
CREATE TABLE `hasil_quiz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `total_nilai` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status_lulus` varchar(50) DEFAULT 'menunggu',
  `pelanggaran_count` int(11) NOT NULL DEFAULT 0,
  `is_disqualified` tinyint(1) NOT NULL DEFAULT 0,
  `started_at` datetime DEFAULT current_timestamp(),
  `finished_at` datetime DEFAULT NULL,
  `attempt_count` int(11) DEFAULT 1,
  `nilai_tertinggi` decimal(5,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `siswa_id` (`siswa_id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `hasil_quiz_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hasil_quiz_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `hasil_quiz` --
INSERT INTO `hasil_quiz` VALUES('1', '1', '1', '35.00', 'tidak_lulus', '0', '0', '2026-08-02 07:30:01', '2026-09-16 21:00:47', '1', '35.00');
INSERT INTO `hasil_quiz` VALUES('2', '3', '4', '100.00', 'lulus', '0', '0', '2026-08-04 18:23:12', '2026-09-16 21:00:47', '4', '100.00');
INSERT INTO `hasil_quiz` VALUES('3', '3', '5', '90.00', 'lulus', '0', '0', '2026-08-04 22:40:54', '2026-09-16 21:00:47', '1', '90.00');
INSERT INTO `hasil_quiz` VALUES('6', '3', '19', '100.00', 'lulus', '0', '0', '2026-08-05 23:40:25', '2026-09-16 21:00:47', '2', '100.00');
INSERT INTO `hasil_quiz` VALUES('7', '3', '20', '100.00', 'lulus', '0', '0', '2026-08-05 23:49:35', '2026-09-16 21:00:47', '2', '100.00');

-- Table structure for `hasil_quiz_history` --
DROP TABLE IF EXISTS `hasil_quiz_history`;
CREATE TABLE `hasil_quiz_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `attempt_number` int(11) NOT NULL DEFAULT 1,
  `total_nilai` decimal(5,2) DEFAULT 0.00,
  `status_lulus` varchar(50) DEFAULT 'lulus',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `hasil_quiz_history` --
INSERT INTO `hasil_quiz_history` VALUES('1', '3', '4', '1', '40.00', 'tidak_lulus', '2026-08-04 19:07:20');
INSERT INTO `hasil_quiz_history` VALUES('2', '3', '4', '2', '58.00', 'tidak_lulus', '2026-08-04 19:13:21');
INSERT INTO `hasil_quiz_history` VALUES('3', '3', '4', '3', '50.00', 'tidak_lulus', '2026-08-04 19:17:59');
INSERT INTO `hasil_quiz_history` VALUES('4', '3', '4', '4', '100.00', 'lulus', '2026-08-04 19:18:21');
INSERT INTO `hasil_quiz_history` VALUES('5', '3', '5', '1', '90.00', 'lulus', '2026-08-04 22:40:54');
INSERT INTO `hasil_quiz_history` VALUES('6', '3', '19', '1', '20.00', 'tidak_lulus', '2026-08-05 23:40:25');
INSERT INTO `hasil_quiz_history` VALUES('7', '3', '19', '2', '100.00', 'lulus', '2026-08-05 23:41:13');
INSERT INTO `hasil_quiz_history` VALUES('8', '3', '20', '1', '20.00', 'tidak_lulus', '2026-08-05 23:49:35');
INSERT INTO `hasil_quiz_history` VALUES('9', '3', '20', '2', '100.00', 'lulus', '2026-08-05 23:49:52');
INSERT INTO `hasil_quiz_history` VALUES('10', '3', '21', '1', '20.00', 'tidak_lulus', '2026-08-06 00:05:22');
INSERT INTO `hasil_quiz_history` VALUES('11', '3', '21', '2', '40.00', 'tidak_lulus', '2026-08-06 00:05:43');

-- Table structure for `hasil_ujian` --
DROP TABLE IF EXISTS `hasil_ujian`;
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

-- Table structure for `jadwal` --
DROP TABLE IF EXISTS `jadwal`;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `jadwal` --
INSERT INTO `jadwal` VALUES('4', '1', '7', '3', 'Senin', '07:30:00', '09:00:00', 'Ruang Kelas');
INSERT INTO `jadwal` VALUES('5', '1', '9', '3', 'Rabu', '07:30:00', '09:00:00', 'Ruang Kelas');

-- Table structure for `jawaban_siswa` --
DROP TABLE IF EXISTS `jawaban_siswa`;
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `jawaban_siswa` --
INSERT INTO `jawaban_siswa` VALUES('1', '1', '1', '1', '1', NULL, '1', '35.00');
INSERT INTO `jawaban_siswa` VALUES('2', '1', '1', '2', '6', NULL, '0', '0.00');
INSERT INTO `jawaban_siswa` VALUES('3', '1', '1', '3', NULL, 'dsgsdgvsdv', '0', '0.00');
INSERT INTO `jawaban_siswa` VALUES('4', '3', '4', '9', '23', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('5', '3', '4', '12', '32', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('6', '3', '4', '11', '29', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('7', '3', '4', '6', '16', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('8', '3', '4', '7', '20', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('9', '3', '4', '10', NULL, 'Hadware adalah perangkat keras pada komputer yang bisa di raba dan dilihat seperti hardisk dan ram sedangkan software adalah perangkat lunak pada komputer yang tidak bisa di raba namun bisa dilihat seperti aplikasi ms office', '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('10', '3', '4', '8', NULL, 'Software Adalah perangkat lunak komputer arti dari perangkat lunak ini jadi bisa dilihat namun tidak bisa di pegang seperti aplikasi microsoft word dilihat ada namun tidak bisa di pegang', '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('11', '3', '5', '16', '51', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('12', '3', '5', '15', '46', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('13', '3', '5', '18', '58', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('14', '3', '5', '19', '63', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('15', '3', '5', '22', '77', NULL, '0', '0.00');
INSERT INTO `jawaban_siswa` VALUES('16', '3', '5', '20', '67', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('17', '3', '5', '13', '37', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('18', '3', '5', '21', '71', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('19', '3', '5', '14', '41', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('20', '3', '5', '17', '56', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('21', '3', '19', '53', '141', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('22', '3', '19', '54', '145', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('23', '3', '19', '55', NULL, 'Fungsi dari arsitektur diatas itu adalah bisa disebut arsitektur jaringan bus', '1', '20.00');
INSERT INTO `jawaban_siswa` VALUES('24', '3', '20', '57', '151', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('25', '3', '20', '56', '147', NULL, '1', '10.00');
INSERT INTO `jawaban_siswa` VALUES('26', '3', '20', '58', NULL, 'ARSITEKTUR JARINGAN BUS', '1', '20.00');

-- Table structure for `jurusan` --
DROP TABLE IF EXISTS `jurusan`;
CREATE TABLE `jurusan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_jurusan` varchar(20) NOT NULL,
  `nama_jurusan` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_jurusan` (`kode_jurusan`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `jurusan` --
INSERT INTO `jurusan` VALUES('1', 'RPL', 'Rekayasa Perangkat Lunak', 'Pengembangan software, web development, dan aplikasi mobile.');
INSERT INTO `jurusan` VALUES('4', 'TBSM', 'Teknik Bisnis Sepeda Motor', 'Pemeliharaan dan perbaikan mesin otomotif modern.');

-- Table structure for `kelas` --
DROP TABLE IF EXISTS `kelas`;
CREATE TABLE `kelas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(50) NOT NULL,
  `jurusan_id` int(11) NOT NULL,
  `tingkat` enum('X','XI','XII') NOT NULL,
  `wali_kelas_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jurusan_id` (`jurusan_id`),
  CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `kelas` --
INSERT INTO `kelas` VALUES('1', 'X RPL 1', '1', 'X', '3');
INSERT INTO `kelas` VALUES('2', 'XI RPL 1', '1', 'XI', NULL);
INSERT INTO `kelas` VALUES('3', 'XII RPL 1', '1', 'XII', NULL);
INSERT INTO `kelas` VALUES('7', 'XII RPL 3', '1', 'XII', NULL);
INSERT INTO `kelas` VALUES('8', 'X PPLG 1', '1', 'X', NULL);

-- Table structure for `komentar` --
DROP TABLE IF EXISTS `komentar`;
CREATE TABLE `komentar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forum_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `komentar` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `gambar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `forum_id` (`forum_id`),
  KEY `user_id` (`user_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `komentar_ibfk_1` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`id`) ON DELETE CASCADE,
  CONSTRAINT `komentar_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `komentar_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `komentar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `komponen_penilaian` --
DROP TABLE IF EXISTS `komponen_penilaian`;
CREATE TABLE `komponen_penilaian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kurikulum_id` int(11) NOT NULL,
  `nama_komponen` varchar(100) NOT NULL,
  `kode_komponen` varchar(50) NOT NULL,
  `bobot_persen` decimal(5,2) NOT NULL DEFAULT 25.00,
  `is_active` tinyint(1) DEFAULT 1,
  `deskripsi` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `kurikulum_id` (`kurikulum_id`),
  CONSTRAINT `komponen_penilaian_ibfk_1` FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `komponen_penilaian` --
INSERT INTO `komponen_penilaian` VALUES('1', '1', 'Tugas Mandiri / Terstruktur', 'tugas', '20.00', '1', 'Penugasan portofolio KBM harian siswa.', '2026-09-19 08:47:06');
INSERT INTO `komponen_penilaian` VALUES('2', '1', 'Kuis / Formatif Harian', 'quiz', '20.00', '1', 'Evaluasi formatif pemahaman tujuan pembelajaran.', '2026-09-19 08:47:06');
INSERT INTO `komponen_penilaian` VALUES('3', '1', 'Sumatif Tengah Semester (STS)', 'uts', '30.00', '1', 'Ujian evaluasi capaian tengah semester.', '2026-09-19 08:47:06');
INSERT INTO `komponen_penilaian` VALUES('4', '1', 'Sumatif Akhir Semester (SAS)', 'uas', '30.00', '1', 'Ujian akhir evaluasi kompetensi semester.', '2026-09-19 08:47:06');
INSERT INTO `komponen_penilaian` VALUES('5', '2', 'Tugas Mandiri / Terstruktur', 'tugas', '20.00', '1', 'Penugasan portofolio KBM harian siswa.', '2026-09-19 08:50:17');
INSERT INTO `komponen_penilaian` VALUES('6', '2', 'Kuis / Formatif Harian', 'quiz', '20.00', '1', 'Evaluasi formatif pemahaman tujuan pembelajaran.', '2026-09-19 08:50:17');
INSERT INTO `komponen_penilaian` VALUES('7', '2', 'Sumatif Tengah Semester (STS)', 'uts', '30.00', '1', 'Ujian evaluasi capaian tengah semester.', '2026-09-19 08:50:17');
INSERT INTO `komponen_penilaian` VALUES('8', '2', 'Sumatif Akhir Semester (SAS)', 'uas', '30.00', '1', 'Ujian akhir evaluasi kompetensi semester.', '2026-09-19 08:50:17');

-- Table structure for `kurikulum` --
DROP TABLE IF EXISTS `kurikulum`;
CREATE TABLE `kurikulum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(30) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `tahun_mulai` int(11) NOT NULL,
  `tahun_selesai` int(11) DEFAULT NULL,
  `status` enum('aktif','non-aktif','arsip') NOT NULL DEFAULT 'aktif',
  `deskripsi` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `kurikulum` --
INSERT INTO `kurikulum` VALUES('1', 'KMDK', 'Kurikulum Merdeka SMK', '2022', NULL, 'aktif', 'Kurikulum Merdeka SMK Pusat Keunggulan dengan pembelajaran berbasis Capaian Pembelajaran (CP), Profil Pelajar Pancasila, dan asesmen fleksibel.', '2026-09-19 08:47:06', '2026-09-19 09:03:09');
INSERT INTO `kurikulum` VALUES('2', 'K2029', 'Kurikulum Vokasi Industri 2029', '2029', NULL, 'aktif', 'Kurikulum generasi baru berbasis kecerdasan buatan dan cloud computing.', '2026-09-19 08:50:17', '2026-09-19 08:50:17');

-- Table structure for `kurikulum_mapel` --
DROP TABLE IF EXISTS `kurikulum_mapel`;
CREATE TABLE `kurikulum_mapel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kurikulum_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `fase_id` int(11) DEFAULT NULL,
  `tingkat` varchar(20) DEFAULT NULL,
  `jurusan_id` int(11) DEFAULT NULL,
  `kelompok_mapel` varchar(50) DEFAULT 'Kejuruan',
  `alokasi_jp` int(11) DEFAULT 2,
  `kkm` decimal(5,2) DEFAULT 75.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_kur_mapel_fase` (`kurikulum_id`,`mapel_id`,`fase_id`,`jurusan_id`),
  KEY `mapel_id` (`mapel_id`),
  KEY `fase_id` (`fase_id`),
  KEY `jurusan_id` (`jurusan_id`),
  CONSTRAINT `kurikulum_mapel_ibfk_1` FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kurikulum_mapel_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kurikulum_mapel_ibfk_3` FOREIGN KEY (`fase_id`) REFERENCES `fase` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kurikulum_mapel_ibfk_4` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `kurikulum_mapel` --
INSERT INTO `kurikulum_mapel` VALUES('1', '1', '1', '1', 'X', '1', 'Kejuruan', '4', '75.00', '1', '2026-09-19 08:47:06');
INSERT INTO `kurikulum_mapel` VALUES('2', '1', '2', '1', 'X', '1', 'Kejuruan', '4', '75.00', '1', '2026-09-19 08:47:06');
INSERT INTO `kurikulum_mapel` VALUES('3', '1', '7', '1', 'X', '1', 'Kejuruan', '4', '75.00', '1', '2026-09-19 08:47:06');
INSERT INTO `kurikulum_mapel` VALUES('4', '1', '8', '1', 'X', '1', 'Kejuruan', '4', '75.00', '1', '2026-09-19 08:47:06');
INSERT INTO `kurikulum_mapel` VALUES('5', '1', '9', '1', 'X', NULL, 'Kejuruan', '4', '75.00', '1', '2026-09-19 08:47:06');
INSERT INTO `kurikulum_mapel` VALUES('6', '2', '10', NULL, 'X', NULL, 'Kejuruan Khusus', '4', '80.00', '1', '2026-09-19 08:50:18');
INSERT INTO `kurikulum_mapel` VALUES('7', '2', '10', NULL, 'X', NULL, 'Kejuruan Khusus', '4', '80.00', '1', '2026-09-19 08:51:01');
INSERT INTO `kurikulum_mapel` VALUES('8', '2', '10', NULL, 'X', NULL, 'Kejuruan Khusus', '4', '80.00', '1', '2026-09-19 09:03:09');

-- Table structure for `library` --
DROP TABLE IF EXISTS `library`;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `library` --
INSERT INTO `library` VALUES('1', 'Perangkat Keras Pada Komputer', 'TIM GURU REKAYASA PERANGKAT LUNAK', 'Tim dari jurusan rekayasa perangkat lunak membuat sebuah karya yaitu buku dengan judul perangkat keras pada komputer', 'Kejuruan', NULL, 'pdf', 'assets/uploads/library/lib_6a73420303bef4.79143975.pdf', '6335288', '9', '1', '0', '2026-08-05 21:00:35');

-- Table structure for `live_class` --
DROP TABLE IF EXISTS `live_class`;
CREATE TABLE `live_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `topik` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `platform` varchar(50) DEFAULT 'embedded',
  `meeting_link` varchar(500) DEFAULT NULL,
  `room_code` varchar(100) NOT NULL,
  `tgl_pertemuan` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `live_class` --
INSERT INTO `live_class` VALUES('1', '1', '1', NULL, 'Sesi Interactive WebRTC Vicon: Review & Live Discussion', 'Selamat datang di ruang Vicon bawaan E-Learning. Silakan bergabung untuk mengikuti diskusi materi dan tanya jawab interaktif secara langsung.', 'embedded', NULL, 'SMKMH-ROOM-3FEF0408', '2026-08-31', '08:00:00', '10:00:00', '1', '2026-08-31 22:11:31');
INSERT INTO `live_class` VALUES('2', '1', '1', NULL, 'Tatap Muka Digital: Google Meet Hybrid Classroom', 'Sesi tatap muka interaktif diselenggarakan via Google Meet resmi.', 'meet', 'https://meet.google.com', 'SMKMH-ROOM-B92E9434', '2026-08-31', '10:30:00', '12:00:00', '1', '2026-08-31 22:11:31');

-- Table structure for `log_login` --
DROP TABLE IF EXISTS `log_login`;
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
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `log_login` --
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
INSERT INTO `log_login` VALUES('23', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 10:22:00');
INSERT INTO `log_login` VALUES('24', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 20:55:02');
INSERT INTO `log_login` VALUES('25', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 21:28:35');
INSERT INTO `log_login` VALUES('26', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 21:37:46');
INSERT INTO `log_login` VALUES('27', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 21:45:11');
INSERT INTO `log_login` VALUES('28', '7', 'agung', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 22:01:09');
INSERT INTO `log_login` VALUES('29', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 22:38:30');
INSERT INTO `log_login` VALUES('30', '3', 'siswa', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 23:15:42');
INSERT INTO `log_login` VALUES('31', '7', 'agung', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-02 23:20:01');
INSERT INTO `log_login` VALUES('32', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 06:42:24');
INSERT INTO `log_login` VALUES('33', '7', 'agung', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 06:43:23');
INSERT INTO `log_login` VALUES('34', '4', 'kepsek', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 06:45:11');
INSERT INTO `log_login` VALUES('35', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 07:01:51');
INSERT INTO `log_login` VALUES('36', '4', 'kepsek', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 07:02:41');
INSERT INTO `log_login` VALUES('37', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 07:13:16');
INSERT INTO `log_login` VALUES('38', '7', 'agung', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 07:15:40');
INSERT INTO `log_login` VALUES('39', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 08:35:51');
INSERT INTO `log_login` VALUES('40', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 09:17:21');
INSERT INTO `log_login` VALUES('41', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 15:33:10');
INSERT INTO `log_login` VALUES('42', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 15:50:52');
INSERT INTO `log_login` VALUES('43', '7', 'agung', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 15:51:39');
INSERT INTO `log_login` VALUES('44', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-04 14:27:30');
INSERT INTO `log_login` VALUES('45', '1', 'admin', 'success', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-04 15:01:19');
INSERT INTO `log_login` VALUES('46', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-04 15:02:19');
INSERT INTO `log_login` VALUES('47', '7', 'agung', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-04 15:15:55');
INSERT INTO `log_login` VALUES('48', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-04 17:10:52');
INSERT INTO `log_login` VALUES('49', '7', 'agung', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-04 17:11:19');
INSERT INTO `log_login` VALUES('50', '2', 'guru', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-04 18:17:49');
INSERT INTO `log_login` VALUES('51', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-04 18:30:54');
INSERT INTO `log_login` VALUES('52', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-04 18:33:39');
INSERT INTO `log_login` VALUES('53', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-04 18:58:30');
INSERT INTO `log_login` VALUES('54', '4', 'kepsek', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-04 20:47:20');
INSERT INTO `log_login` VALUES('55', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 12:14:14');
INSERT INTO `log_login` VALUES('56', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 12:14:58');
INSERT INTO `log_login` VALUES('57', '7', 'agung', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 12:20:17');
INSERT INTO `log_login` VALUES('58', '4', 'kepsek', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 12:48:20');
INSERT INTO `log_login` VALUES('59', '6', 'siswa2', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 12:53:10');
INSERT INTO `log_login` VALUES('60', '10', 'yusan', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 12:58:35');
INSERT INTO `log_login` VALUES('61', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 13:47:58');
INSERT INTO `log_login` VALUES('62', '7', 'agung', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 13:49:11');
INSERT INTO `log_login` VALUES('63', '9', 'agg023', 'failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 13:55:33');
INSERT INTO `log_login` VALUES('64', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 13:55:47');
INSERT INTO `log_login` VALUES('65', '1', 'admin', 'failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 15:38:54');
INSERT INTO `log_login` VALUES('66', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 15:39:12');
INSERT INTO `log_login` VALUES('67', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 20:14:44');
INSERT INTO `log_login` VALUES('68', '7', 'agung', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 20:42:10');
INSERT INTO `log_login` VALUES('69', '4', 'kepsek', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 21:01:50');
INSERT INTO `log_login` VALUES('70', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-05 21:02:57');
INSERT INTO `log_login` VALUES('71', '9', 'agg023', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-10 18:54:56');
INSERT INTO `log_login` VALUES('72', '1', 'admin', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-10 21:28:15');
INSERT INTO `log_login` VALUES('73', '7', 'agung', 'failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-11 21:37:00');
INSERT INTO `log_login` VALUES('74', '7', 'agung', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-11 21:37:12');
INSERT INTO `log_login` VALUES('75', '7', 'agung', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-11 21:50:02');

-- Table structure for `mapel_enrollment_keys` --
DROP TABLE IF EXISTS `mapel_enrollment_keys`;
CREATE TABLE `mapel_enrollment_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mapel_id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `enrollment_key` varchar(50) NOT NULL,
  `passcode` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `mapel_enrollment_keys` --
INSERT INTO `mapel_enrollment_keys` VALUES('1', '7', '3', '1', 'MPL-7-3-403', 'MPL-7-3-403', '1', '2026-08-03 15:50:15');
INSERT INTO `mapel_enrollment_keys` VALUES('2', '1', '1', '2', 'MPL-1-1-876', 'MPL-1-1-876', '1', '2026-08-03 15:50:15');
INSERT INTO `mapel_enrollment_keys` VALUES('3', '2', '2', '2', 'MPL-2-2-855', 'MPL-2-2-855', '1', '2026-08-03 15:50:15');
INSERT INTO `mapel_enrollment_keys` VALUES('4', '9', '3', NULL, 'MPL-9-3-142', 'MPL-9-3-142', '1', '2026-08-04 21:36:52');

-- Table structure for `mata_pelajaran` --
DROP TABLE IF EXISTS `mata_pelajaran`;
CREATE TABLE `mata_pelajaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_mapel` varchar(20) NOT NULL,
  `nama_mapel` varchar(100) NOT NULL,
  `jurusan_id` int(11) DEFAULT NULL,
  `kkm` int(11) DEFAULT 75,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_mapel` (`kode_mapel`),
  KEY `jurusan_id` (`jurusan_id`),
  CONSTRAINT `mata_pelajaran_ibfk_1` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `mata_pelajaran` --
INSERT INTO `mata_pelajaran` VALUES('1', 'MP01', 'Pemrograman Web dan Perangkat Bergerak', '1', '75');
INSERT INTO `mata_pelajaran` VALUES('2', 'MP02', 'Pemodelan Perangkat Lunak', '1', '75');
INSERT INTO `mata_pelajaran` VALUES('7', 'MP07', 'Pengembangan Perangkat Lunak Dan Gim(DDPK)', '1', '75');
INSERT INTO `mata_pelajaran` VALUES('8', 'MP08', 'Kompetensi Keahlian', '1', '75');
INSERT INTO `mata_pelajaran` VALUES('9', 'MP09', 'INFORMATIKA', NULL, '75');
INSERT INTO `mata_pelajaran` VALUES('10', 'MP-AI-01', 'Kecerdasan Buatan & Cloud Computing', NULL, '80');

-- Table structure for `materi` --
DROP TABLE IF EXISTS `materi`;
CREATE TABLE `materi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `kelas_ids` varchar(255) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `materi` --
INSERT INTO `materi` VALUES('1', '1', '1', '2', '2', 'Konsep dasar MVC pada PHP Native', 'Materi lengkap arsitektur Model-View-Controller dalam pembuatan web modern.', 'pdf', 'materi_mvc_php.pdf', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '2026-08-02 07:09:38');
INSERT INTO `materi` VALUES('2', '2', '2', '2', '2', 'Prinsip Object Oriented Programming (OOP)', 'Pemahaman class, object, inheritance, dan encapsulation dalam PHP 8.', 'ppt', 'materi_oop_php.pptx', NULL, '2026-08-02 07:09:38');
INSERT INTO `materi` VALUES('3', '3', '7', '1', '1', 'Perangkat Keras Pada Komputer', 'Silahkan pelajari materi yang saya share dan nanti kita bahas bersama sama dikelas ya', 'pdf', 'materi_1785682279_6a6f59676c291.pdf', 'https://youtu.be/2tQtnxGo1eE?si=UQ2IWXn0JlOI1Bmr', '2026-08-02 21:51:19');
INSERT INTO `materi` VALUES('4', '3', '9', '1', '1', 'Berpikir Secara Komputasional', 'Silahkan Kalian Baca terlebih Dahulu Nanti kita bahas secara bersama sama', 'pdf', 'materi_1785854488_6a71fa18839b9.pdf', 'https://youtu.be/jCb9fpPrxLc?si=DmMItiibtIynQWb-', '2026-08-04 21:41:28');

-- Table structure for `nilai` --
DROP TABLE IF EXISTS `nilai`;
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

-- Table structure for `nilai_asesmen_siswa` --
DROP TABLE IF EXISTS `nilai_asesmen_siswa`;
CREATE TABLE `nilai_asesmen_siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asesmen_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `nilai` decimal(5,2) NOT NULL DEFAULT 0.00,
  `catatan` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_asesmen_siswa` (`asesmen_id`,`siswa_id`),
  KEY `siswa_id` (`siswa_id`),
  CONSTRAINT `nilai_asesmen_siswa_ibfk_1` FOREIGN KEY (`asesmen_id`) REFERENCES `asesmen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nilai_asesmen_siswa_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `nilai_rapor` --
DROP TABLE IF EXISTS `nilai_rapor`;
CREATE TABLE `nilai_rapor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `nilai_tugas` decimal(5,2) DEFAULT 0.00,
  `nilai_quiz` decimal(5,2) DEFAULT 0.00,
  `nilai_uts` decimal(5,2) DEFAULT 0.00,
  `nilai_uas` decimal(5,2) DEFAULT 0.00,
  `nilai_akhir` decimal(5,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `siswa_id` (`siswa_id`),
  KEY `mapel_id` (`mapel_id`),
  CONSTRAINT `nilai_rapor_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nilai_rapor_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `nilai_rapor` --
INSERT INTO `nilai_rapor` VALUES('1', '3', '7', '98.00', '100.00', '100.00', '100.00', '99.60', '2026-08-04 19:07:20', '2026-09-18 23:16:53');
INSERT INTO `nilai_rapor` VALUES('6', '1', '1', '0.00', '35.00', '0.00', '0.00', '35.00', '2026-08-04 20:34:21', '2026-09-16 21:00:47');
INSERT INTO `nilai_rapor` VALUES('7', '1', '2', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 20:34:21', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('8', '1', '7', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 20:34:21', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('9', '1', '8', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 20:34:21', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('14', '2', '1', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 20:34:21', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('15', '2', '2', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 20:34:21', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('16', '2', '7', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 20:34:21', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('17', '2', '8', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 20:34:21', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('22', '3', '1', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 20:34:21', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('23', '3', '2', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 20:34:21', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('24', '3', '8', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 20:34:21', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('29', '4', '1', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 20:34:21', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('30', '4', '2', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 20:34:21', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('31', '4', '7', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 20:34:21', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('32', '4', '8', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 20:34:21', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('33', '3', '9', '0.00', '90.00', '0.00', '0.00', '90.00', '2026-08-04 22:40:54', '2026-09-18 23:16:53');
INSERT INTO `nilai_rapor` VALUES('34', '1', '9', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 22:41:58', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('35', '2', '9', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 22:41:58', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('36', '4', '9', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-04 22:41:58', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('37', '5', '9', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-05 13:41:59', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('38', '5', '1', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-05 13:41:59', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('39', '5', '2', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-05 13:41:59', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('40', '5', '7', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-05 13:41:59', '2026-08-10 19:37:23');
INSERT INTO `nilai_rapor` VALUES('41', '5', '8', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-08-05 13:41:59', '2026-08-10 19:37:23');

-- Table structure for `notifikasi` --
DROP TABLE IF EXISTS `notifikasi`;
CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `notifikasi` --
INSERT INTO `notifikasi` VALUES('1', '9', '📩 Permintaan Izin Ujian Susulan', 'Siswa AGUNG RIFALDI mengajukan permohonan izin Ujian Susulan Kuis.', 'index.php?url=guru/quiz', '1', '2026-08-04 17:11:53', NULL, NULL);
INSERT INTO `notifikasi` VALUES('2', '7', '✅ Izin Ujian Susulan Disetujui', 'Guru telah MENYETUJUI izin Ujian Susulan Kuis: Ujian Harian Perangkat Keras Pada Komputer. Akses pengerjaan kuis kini terbuka!', 'index.php?url=siswa/quiz', '1', '2026-08-04 17:12:13', NULL, NULL);
INSERT INTO `notifikasi` VALUES('3', '7', '❌ Izin Ujian Susulan Ditolak', 'Guru telah MENOLAK permohonan izin Ujian Susulan Kuis: Ujian Harian Perangkat Keras Pada Komputer.', 'index.php?url=siswa/quiz', '1', '2026-08-04 17:12:29', NULL, NULL);
INSERT INTO `notifikasi` VALUES('4', '7', '✅ Izin Ujian Susulan Disetujui', 'Guru telah MENYETUJUI izin Ujian Susulan Kuis: Ujian Harian Perangkat Keras Pada Komputer. Akses pengerjaan kuis kini terbuka!', 'index.php?url=siswa/quiz', '1', '2026-08-04 17:13:41', NULL, NULL);
INSERT INTO `notifikasi` VALUES('5', '7', '❌ Izin Ujian Susulan Ditolak', 'Guru telah MENOLAK permohonan izin Ujian Susulan Kuis: Ujian Harian Perangkat Keras Pada Komputer.', 'index.php?url=siswa/quiz', '1', '2026-08-04 17:13:50', NULL, NULL);
INSERT INTO `notifikasi` VALUES('6', '7', '✅ Izin Ujian Susulan Disetujui', 'Guru telah MENYETUJUI izin Ujian Susulan Kuis: Ujian Harian Perangkat Keras Pada Komputer. Akses pengerjaan kuis kini terbuka!', 'index.php?url=siswa/quiz', '1', '2026-08-04 17:43:23', NULL, NULL);
INSERT INTO `notifikasi` VALUES('7', '9', '🚨 Diskualifikasi Ujian Online', 'Siswa AGUNG RIFALDI didiskualifikasi dari Kuis karena melanggar aturan (berpindah tab/keluar fullscreen).', 'index.php?url=guru/quiz', '1', '2026-08-04 17:43:45', NULL, NULL);
INSERT INTO `notifikasi` VALUES('8', '9', '🚨 Diskualifikasi Ujian Online', 'Siswa AGUNG RIFALDI didiskualifikasi dari Kuis karena melanggar aturan (berpindah tab/keluar fullscreen).', 'index.php?url=guru/quiz', '1', '2026-08-04 17:49:23', NULL, NULL);
INSERT INTO `notifikasi` VALUES('9', '9', '🚨 Diskualifikasi Ujian Online', 'Siswa AGUNG RIFALDI didiskualifikasi dari Kuis karena melanggar aturan (berpindah tab/keluar fullscreen).', 'index.php?url=guru/quiz', '1', '2026-08-04 17:53:10', NULL, NULL);
INSERT INTO `notifikasi` VALUES('10', '7', '❌ Izin Ujian Susulan Ditolak', 'Guru telah MENOLAK permohonan izin Ujian Susulan Kuis: Ujian Harian Perangkat Keras Pada Komputer.', 'index.php?url=siswa/quiz', '1', '2026-08-04 17:54:35', NULL, NULL);
INSERT INTO `notifikasi` VALUES('11', '7', '✅ Izin Ujian Susulan Disetujui', 'Guru telah MENYETUJUI izin Ujian Susulan Kuis: Ujian Harian Perangkat Keras Pada Komputer. Akses pengerjaan kuis kini terbuka!', 'index.php?url=siswa/quiz', '1', '2026-08-04 17:55:41', NULL, NULL);
INSERT INTO `notifikasi` VALUES('12', '9', '📊 Siswa Menyelesaikan Kuis', 'Siswa AGUNG RIFALDI telah menyelesaikan pengerjaan Kuis. Nilai: 20', 'index.php?url=guru/quiz', '1', '2026-08-04 18:23:12', NULL, NULL);
INSERT INTO `notifikasi` VALUES('13', '9', '📊 Siswa Menyelesaikan Kuis', 'Siswa AGUNG RIFALDI telah menyelesaikan pengerjaan Kuis. Nilai: 40', 'index.php?url=guru/quiz', '1', '2026-08-04 19:07:20', NULL, NULL);
INSERT INTO `notifikasi` VALUES('14', '7', '🏆 Penilaian Essay Kuis Selesai', 'Guru telah mengoreksi dan memberikan nilai untuk jawaban essay Anda pada kuis.', 'index.php?url=siswa/nilai', '1', '2026-08-04 19:13:21', NULL, NULL);
INSERT INTO `notifikasi` VALUES('15', '9', '📊 Siswa Menyelesaikan Kuis', 'Siswa AGUNG RIFALDI telah menyelesaikan pengerjaan Kuis. Nilai: 58', 'index.php?url=guru/quiz', '1', '2026-08-04 19:17:59', NULL, NULL);
INSERT INTO `notifikasi` VALUES('16', '7', '🏆 Penilaian Essay Kuis Selesai', 'Guru telah mengoreksi dan memberikan nilai untuk jawaban essay Anda pada kuis.', 'index.php?url=siswa/nilai', '1', '2026-08-04 19:18:21', NULL, NULL);
INSERT INTO `notifikasi` VALUES('17', '7', '📝 Tugas Pembelajaran Baru', 'Guru mempublikasikan Tugas Baru: Berpikir Secara Komputasional. Batas deadline: 2026-08-05T23:59.', 'index.php?url=siswa/tugas', '1', '2026-08-04 22:16:59', NULL, NULL);
INSERT INTO `notifikasi` VALUES('18', '7', '✏️ Kuis CBT Baru Dipublikasikan', 'Guru mempublikasikan Kuis Baru: Berpikir Secara Komputasional. Durasi: 10 Menit.', 'index.php?url=siswa/quiz', '1', '2026-08-04 22:35:10', NULL, NULL);
INSERT INTO `notifikasi` VALUES('19', '9', '🚨 Diskualifikasi Ujian Online', 'Siswa AGUNG RIFALDI didiskualifikasi dari Kuis karena melanggar aturan (berpindah tab/keluar fullscreen).', 'index.php?url=guru/quiz', '1', '2026-08-04 22:37:41', NULL, NULL);
INSERT INTO `notifikasi` VALUES('20', '9', '📩 Permintaan Izin Ujian Susulan', 'Siswa AGUNG RIFALDI mengajukan permohonan izin Ujian Susulan Kuis.', 'index.php?url=guru/quiz', '1', '2026-08-04 22:38:22', NULL, NULL);
INSERT INTO `notifikasi` VALUES('21', '7', '✅ Izin Ujian Susulan Disetujui', 'Guru telah MENYETUJUI izin Ujian Susulan Kuis: Berpikir Secara Komputasional. Akses pengerjaan kuis kini terbuka!', 'index.php?url=siswa/quiz', '1', '2026-08-04 22:38:33', NULL, NULL);
INSERT INTO `notifikasi` VALUES('22', '9', '📊 Siswa Menyelesaikan Kuis', 'Siswa AGUNG RIFALDI telah menyelesaikan pengerjaan Kuis. Nilai: 90', 'index.php?url=guru/quiz', '1', '2026-08-04 22:40:54', NULL, NULL);
INSERT INTO `notifikasi` VALUES('23', '9', '📥 Jawaban Tugas Baru Dikirim', 'Siswa AGUNG RIFALDI telah mengunggah jawaban tugas.', 'index.php?url=guru/tugas', '1', '2026-08-05 15:37:00', NULL, NULL);
INSERT INTO `notifikasi` VALUES('24', '7', '🏆 Nilai Tugas Telah Diberikan', 'Guru telah memberikan Nilai 98 untuk tugas: Perangkat Keras Pada Komputer.', 'index.php?url=siswa/tugas', '1', '2026-08-05 15:37:40', NULL, NULL);
INSERT INTO `notifikasi` VALUES('25', '9', '📊 Siswa Menyelesaikan Kuis', 'Siswa AGUNG RIFALDI telah menyelesaikan pengerjaan Kuis. Nilai: 20', 'index.php?url=guru/quiz', '1', '2026-08-05 23:40:25', NULL, NULL);
INSERT INTO `notifikasi` VALUES('26', '7', '🏆 Penilaian Essay Kuis Selesai', 'Guru telah mengoreksi dan memberikan nilai untuk jawaban essay Anda pada kuis.', 'index.php?url=siswa/nilai', '1', '2026-08-05 23:41:13', NULL, NULL);
INSERT INTO `notifikasi` VALUES('27', '9', '📊 Siswa Menyelesaikan Kuis', 'Siswa AGUNG RIFALDI telah menyelesaikan pengerjaan Kuis. Nilai: 20', 'index.php?url=guru/quiz', '1', '2026-08-05 23:49:35', NULL, NULL);
INSERT INTO `notifikasi` VALUES('28', '7', '🏆 Penilaian Essay Kuis Selesai', 'Guru telah mengoreksi dan memberikan nilai untuk jawaban essay Anda pada kuis.', 'index.php?url=siswa/nilai', '1', '2026-08-05 23:49:53', NULL, NULL);
INSERT INTO `notifikasi` VALUES('29', '9', '📩 Permintaan Izin Ujian Susulan', 'Siswa AGUNG RIFALDI mengajukan permohonan izin Ujian Susulan Kuis.', 'index.php?url=guru/quiz', '0', '2026-08-05 23:56:35', NULL, NULL);
INSERT INTO `notifikasi` VALUES('30', '7', '✅ Izin Ujian Susulan Disetujui', 'Guru telah MENYETUJUI izin Ujian Susulan Kuis/UTS/UAS: UTS SEMESTER 1 INFORMATIKA. Akses pengerjaan kuis kini telah dibuka kembali!', 'index.php?url=siswa/quiz', '1', '2026-08-05 23:59:50', NULL, NULL);
INSERT INTO `notifikasi` VALUES('31', '7', '❌ Izin Ujian Susulan Ditolak', 'Guru telah MENOLAK permohonan izin Ujian Susulan Kuis: UTS SEMESTER 1 INFORMATIKA.', 'index.php?url=siswa/quiz', '1', '2026-08-06 00:00:43', NULL, NULL);
INSERT INTO `notifikasi` VALUES('32', '7', '✅ Izin Ujian Susulan Disetujui', 'Guru telah MENYETUJUI izin Ujian Susulan Kuis/UTS/UAS: UTS SEMESTER 1 INFORMATIKA. Akses pengerjaan kuis kini telah dibuka kembali!', 'index.php?url=siswa/quiz', '1', '2026-08-06 00:04:36', NULL, NULL);
INSERT INTO `notifikasi` VALUES('33', '9', '📊 Siswa Menyelesaikan Kuis', 'Siswa AGUNG RIFALDI telah menyelesaikan pengerjaan Kuis. Nilai: 20', 'index.php?url=guru/quiz', '0', '2026-08-06 00:05:22', NULL, NULL);
INSERT INTO `notifikasi` VALUES('34', '7', '🏆 Penilaian Essay Kuis Selesai', 'Guru telah mengoreksi dan memberikan nilai untuk jawaban essay Anda pada kuis.', 'index.php?url=siswa/nilai', '1', '2026-08-06 00:05:43', NULL, NULL);
INSERT INTO `notifikasi` VALUES('35', '2', '📩 Permintaan Izin Susulan Tugas', 'Siswa Muhammad Rizky Pratama mengajukan permohonan izin susulan pengumpulan Tugas via Mobile. Catatan: Test permohonan', 'index.php?url=guru/tugas', '0', '2026-09-02 20:51:14', NULL, NULL);
INSERT INTO `notifikasi` VALUES('36', '2', '📩 Permintaan Izin Susulan Tugas', 'Siswa Muhammad Rizky Pratama mengajukan permohonan izin susulan pengumpulan Tugas via Mobile. Catatan: Test permohonan susulan tugas', 'index.php?url=guru/tugas', '0', '2026-09-02 20:58:55', NULL, NULL);
INSERT INTO `notifikasi` VALUES('37', '2', '📩 Permintaan Izin Susulan Tugas', 'Siswa Muhammad Rizky Pratama mengajukan permohonan izin susulan pengumpulan Tugas via Mobile. Catatan: Sakit / Izin Medis', 'index.php?url=guru/tugas', '0', '2026-09-02 21:06:34', NULL, NULL);
INSERT INTO `notifikasi` VALUES('38', '2', '📩 Permintaan Izin Susulan Tugas', 'Siswa Muhammad Rizky Pratama mengajukan permohonan izin susulan pengumpulan Tugas via Mobile. Catatan: Sakit / Izin Medis', 'index.php?url=guru/tugas', '0', '2026-09-02 21:13:28', NULL, NULL);
INSERT INTO `notifikasi` VALUES('39', '2', '📩 Permintaan Izin Susulan Tugas', 'Siswa Muhammad Rizky Pratama mengajukan permohonan izin susulan pengumpulan Tugas via Mobile. Catatan: Sakit / Izin Medis', 'index.php?url=guru/tugas', '0', '2026-09-02 21:18:35', NULL, NULL);
INSERT INTO `notifikasi` VALUES('40', '2', '📩 Permintaan Izin Susulan Tugas', 'Siswa Muhammad Rizky Pratama mengajukan permohonan izin susulan pengumpulan Tugas via Mobile. Catatan: Sakit / Izin Medis', 'index.php?url=guru/tugas', '0', '2026-09-02 21:26:29', NULL, NULL);
INSERT INTO `notifikasi` VALUES('54', '7', '⏰ Pengingat Absensi Masuk', 'Halo AGUNG RIFALDI, Anda belum melakukan presensi masuk hari ini. Segera scan QR Code Presensi!', NULL, '0', '2026-09-07 08:33:29', 'absensi', NULL);
INSERT INTO `notifikasi` VALUES('55', '7', '📚 Pengingat Kelas: Pengembangan Perangkat Lunak Dan Gim(DDPK)', 'Jadwal KBM Pengembangan Perangkat Lunak Dan Gim(DDPK) bersama AGUNG RIFALDI, S.Tr. Kom hari ini dimulai pukul 07:30 WIB. Bersiaplah!', NULL, '0', '2026-09-07 08:33:29', 'jadwal', '4');
INSERT INTO `notifikasi` VALUES('56', '9', '⏰ Pengingat Absensi Guru', 'Bpk/Ibu AGUNG RIFALDI, S.Tr. Kom, Anda belum melakukan presensi masuk GTK hari ini. Segera catat kehadiran!', NULL, '0', '2026-09-07 08:33:29', 'absensi', NULL);
INSERT INTO `notifikasi` VALUES('57', '9', '📚 Pengingat Mengajar: Pengembangan Perangkat Lunak Dan Gim(DDPK)', 'Bpk/Ibu AGUNG RIFALDI, S.Tr. Kom, Anda memiliki jadwal mengajar mapel Pengembangan Perangkat Lunak Dan Gim(DDPK) di X RPL 1 hari ini pukul 07:30 WIB.', NULL, '0', '2026-09-07 08:33:29', 'jadwal', '4');
INSERT INTO `notifikasi` VALUES('58', '3', '⏰ Pengingat Absensi Masuk', 'Halo Muhammad Rizky Pratama, Anda belum melakukan presensi masuk hari ini. Segera scan QR Code Presensi!', NULL, '0', '2026-09-07 08:33:29', 'absensi', NULL);
INSERT INTO `notifikasi` VALUES('59', '6', '⏰ Pengingat Absensi Masuk', 'Halo Siti Rahmawati, Anda belum melakukan presensi masuk hari ini. Segera scan QR Code Presensi!', NULL, '0', '2026-09-07 08:33:29', 'absensi', NULL);
INSERT INTO `notifikasi` VALUES('60', '8', '⏰ Pengingat Absensi Masuk', 'Halo Dwi Handoko, Anda belum melakukan presensi masuk hari ini. Segera scan QR Code Presensi!', NULL, '0', '2026-09-07 08:33:29', 'absensi', NULL);
INSERT INTO `notifikasi` VALUES('61', '10', '⏰ Pengingat Absensi Masuk', 'Halo Yulia, Anda belum melakukan presensi masuk hari ini. Segera scan QR Code Presensi!', NULL, '0', '2026-09-07 08:33:29', 'absensi', NULL);
INSERT INTO `notifikasi` VALUES('62', '2', '⏰ Pengingat Absensi Guru', 'Bpk/Ibu Drs. Ahmad Hidayat, M.Pd., Anda belum melakukan presensi masuk GTK hari ini. Segera catat kehadiran!', NULL, '0', '2026-09-07 08:33:29', 'absensi', NULL);
INSERT INTO `notifikasi` VALUES('63', '4', '⏰ Pengingat Absensi Guru', 'Bpk/Ibu H. ASEP SAEPULLOH, S.Ag, Anda belum melakukan presensi masuk GTK hari ini. Segera catat kehadiran!', NULL, '0', '2026-09-07 08:33:29', 'absensi', NULL);
INSERT INTO `notifikasi` VALUES('64', '5', '⏰ Pengingat Absensi Guru', 'Bpk/Ibu Budi Santoso, S.T., Anda belum melakukan presensi masuk GTK hari ini. Segera catat kehadiran!', NULL, '0', '2026-09-07 08:33:29', 'absensi', NULL);

-- Table structure for `pembayaran_riwayat` --
DROP TABLE IF EXISTS `pembayaran_riwayat`;
CREATE TABLE `pembayaran_riwayat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tagihan_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `nomor_transaksi` varchar(50) NOT NULL,
  `nominal_bayar` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tanggal_bayar` datetime NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL DEFAULT 'Transfer Bank',
  `channel` varchar(50) DEFAULT NULL,
  `status` enum('berhasil','pending','batal') NOT NULL DEFAULT 'berhasil',
  `catatan` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_transaksi` (`nomor_transaksi`),
  KEY `idx_riwayat_tagihan` (`tagihan_id`),
  KEY `idx_riwayat_siswa` (`siswa_id`),
  KEY `idx_riwayat_tgl` (`tanggal_bayar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `pembayaran_tagihan` --
DROP TABLE IF EXISTS `pembayaran_tagihan`;
CREATE TABLE `pembayaran_tagihan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `nis` varchar(30) DEFAULT NULL,
  `nisn` varchar(30) DEFAULT NULL,
  `jenis_pembayaran` varchar(50) NOT NULL DEFAULT 'SPP',
  `kode_tagihan` varchar(50) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `nominal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `nominal_terbayar` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sisa_tagihan` decimal(12,2) NOT NULL DEFAULT 0.00,
  `periode_bulan` varchar(30) DEFAULT NULL,
  `tahun_ajaran` varchar(20) NOT NULL DEFAULT '2025/2026',
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `status` enum('lunas','belum_lunas','sebagian') NOT NULL DEFAULT 'belum_lunas',
  `keterangan` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_tagihan` (`kode_tagihan`),
  KEY `idx_tagihan_siswa` (`siswa_id`),
  KEY `idx_tagihan_nisn` (`nisn`),
  KEY `idx_tagihan_status` (`status`),
  KEY `idx_tagihan_jenis` (`jenis_pembayaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `pengumpulan_tugas` --
DROP TABLE IF EXISTS `pengumpulan_tugas`;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `pengumpulan_tugas` --
INSERT INTO `pengumpulan_tugas` VALUES('1', '2', '3', 'tugas_1785919020_6a72f62c7c133.pdf', '', '98.00', 'Bagus AGUNG', '2026-08-05 15:37:00', '2026-08-05 15:37:40');

-- Table structure for `pengumuman` --
DROP TABLE IF EXISTS `pengumuman`;
CREATE TABLE `pengumuman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `isi` text NOT NULL,
  `target_role` enum('all','guru','siswa','kepsek') DEFAULT 'all',
  `is_popup` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `banner` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `pengumuman_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `pengumuman` --
INSERT INTO `pengumuman` VALUES('1', '1', 'Selamat Datang di E-Learning SMK Muthia Harapan Cicalengka', 'Portal pembelajaran digital resmi SMK Muthia Harapan Cicalengka telah siap digunakan untuk kegiatan KBM online.', 'all', '1', '2026-08-02 07:09:38', NULL);
INSERT INTO `pengumuman` VALUES('2', '1', 'Jadwal Penilaian Tengah Semester (PTS) Ganjil', 'Diinformasikan kepada seluruh siswa dan guru bahwa PTS Ganjil akan dimulai pekan depan.', 'all', '0', '2026-08-02 07:09:38', NULL);
INSERT INTO `pengumuman` VALUES('3', '1', 'Besok Ujian !!!', 'Besok Ujian Persiapkan Diri Kalian Dan Kepada Guru Agar segera Menyiapkan Soalnya', 'all', '1', '2026-08-03 09:16:47', NULL);
INSERT INTO `pengumuman` VALUES('4', '1', 'Pengumpulan Bahan Ajar', 'Tolong untuk di perhatikan kepada seluruh tenaga pendidik di SMK MUTHIA HARAPAN CICALENGKA agar segera mengumpulkan bahan ajarnya, paling lambat 12 oktober 2026', 'guru', '1', '2026-08-03 19:12:11', NULL);

-- Table structure for `pilihan_jawaban` --
DROP TABLE IF EXISTS `pilihan_jawaban`;
CREATE TABLE `pilihan_jawaban` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `soal_id` int(11) NOT NULL,
  `teks_pilihan` text NOT NULL,
  `is_benar` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `soal_id` (`soal_id`),
  CONSTRAINT `pilihan_jawaban_ibfk_1` FOREIGN KEY (`soal_id`) REFERENCES `soal` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `pilihan_jawaban` --
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
INSERT INTO `pilihan_jawaban` VALUES('16', '6', 'merukan perangkat keras di komputer yang bisa dilihat dan di rasakan', '1');
INSERT INTO `pilihan_jawaban` VALUES('17', '6', 'merukan perangkat keras di komputer yang tidak bisa dilihat dan di rasakan', '0');
INSERT INTO `pilihan_jawaban` VALUES('18', '6', 'merupakan perangkat lunak di komputer', '0');
INSERT INTO `pilihan_jawaban` VALUES('19', '6', 'merupakan alat untuk memperbaiki komputer', '0');
INSERT INTO `pilihan_jawaban` VALUES('20', '7', 'Benar (True)', '1');
INSERT INTO `pilihan_jawaban` VALUES('21', '7', 'Salah (False)', '0');
INSERT INTO `pilihan_jawaban` VALUES('22', '9', 'Perngkat keras', '0');
INSERT INTO `pilihan_jawaban` VALUES('23', '9', 'Manusia yang memakai, mengatur, dan mengoperasikan sistem komputer', '1');
INSERT INTO `pilihan_jawaban` VALUES('24', '9', 'perangkat lunak', '0');
INSERT INTO `pilihan_jawaban` VALUES('25', '9', 'bentuk fisik pada komputer', '0');
INSERT INTO `pilihan_jawaban` VALUES('26', '11', 'Power Supply', '0');
INSERT INTO `pilihan_jawaban` VALUES('27', '11', 'SSD', '0');
INSERT INTO `pilihan_jawaban` VALUES('28', '11', 'hardisk', '0');
INSERT INTO `pilihan_jawaban` VALUES('29', '11', 'motherboard', '1');
INSERT INTO `pilihan_jawaban` VALUES('30', '12', 'Power Supply', '0');
INSERT INTO `pilihan_jawaban` VALUES('31', '12', 'SSD', '0');
INSERT INTO `pilihan_jawaban` VALUES('32', '12', 'hardisk', '1');
INSERT INTO `pilihan_jawaban` VALUES('33', '12', 'motherboard', '0');
INSERT INTO `pilihan_jawaban` VALUES('34', '12', 'Processor', '0');
INSERT INTO `pilihan_jawaban` VALUES('35', '13', 'Sistem mikropresessor', '0');
INSERT INTO `pilihan_jawaban` VALUES('36', '13', 'Struktur graphene', '0');
INSERT INTO `pilihan_jawaban` VALUES('37', '13', 'Artificial intelligence', '1');
INSERT INTO `pilihan_jawaban` VALUES('38', '13', 'Digitalizer singularistic', '0');
INSERT INTO `pilihan_jawaban` VALUES('39', '13', 'Embedded system', '0');
INSERT INTO `pilihan_jawaban` VALUES('40', '14', 'Menggantikan logika dengan emosi', '0');
INSERT INTO `pilihan_jawaban` VALUES('41', '14', 'Membantu manusia memahami dan menyelesaikan masalah', '1');
INSERT INTO `pilihan_jawaban` VALUES('42', '14', 'Membuat manusia kehilangan pekerjaan', '0');
INSERT INTO `pilihan_jawaban` VALUES('43', '14', 'Mempercepat seluruh aktivitas manusia', '0');
INSERT INTO `pilihan_jawaban` VALUES('44', '15', 'Pengelolaan sumber daya', '0');
INSERT INTO `pilihan_jawaban` VALUES('45', '15', 'Perencanaan pembangunan', '0');
INSERT INTO `pilihan_jawaban` VALUES('46', '15', 'Tipografi', '1');
INSERT INTO `pilihan_jawaban` VALUES('47', '15', 'Investigasi ilmiah', '0');
INSERT INTO `pilihan_jawaban` VALUES('48', '15', 'Perencanaan rute', '0');
INSERT INTO `pilihan_jawaban` VALUES('49', '16', 'Menyederhanakan masalah secara berlebihan', '0');
INSERT INTO `pilihan_jawaban` VALUES('50', '16', 'Mengidentifikasi pola dalam data', '0');
INSERT INTO `pilihan_jawaban` VALUES('51', '16', 'Menggunakan solusi pada permasalahan lain yang serupa', '1');
INSERT INTO `pilihan_jawaban` VALUES('52', '16', 'Mengecilkan ukuran program komputer', '0');
INSERT INTO `pilihan_jawaban` VALUES('53', '17', 'Perangkat keras komputer', '0');
INSERT INTO `pilihan_jawaban` VALUES('54', '17', 'Kode program', '0');
INSERT INTO `pilihan_jawaban` VALUES('55', '17', 'Bahasa pemrograman', '0');
INSERT INTO `pilihan_jawaban` VALUES('56', '17', 'Kinerja solusi atau algoritma', '1');
INSERT INTO `pilihan_jawaban` VALUES('57', '18', 'Membuat data lebih kompleks', '0');
INSERT INTO `pilihan_jawaban` VALUES('58', '18', 'Mengolah data agar sesuai kebutuhan pemrosesan', '1');
INSERT INTO `pilihan_jawaban` VALUES('59', '18', 'Menjadikan data lebih sulit dipahami', '0');
INSERT INTO `pilihan_jawaban` VALUES('60', '18', 'Menyesuaikan data dengan preferensi pemrogram', '0');
INSERT INTO `pilihan_jawaban` VALUES('61', '19', 'Perdagangan', '0');
INSERT INTO `pilihan_jawaban` VALUES('62', '19', 'Jasa', '0');
INSERT INTO `pilihan_jawaban` VALUES('63', '19', 'Manufaktur', '1');
INSERT INTO `pilihan_jawaban` VALUES('64', '19', 'Perusahaan kecil', '0');
INSERT INTO `pilihan_jawaban` VALUES('65', '19', 'Perbaikan', '0');
INSERT INTO `pilihan_jawaban` VALUES('66', '20', 'Proses berpikir kritis tentang komputer', '0');
INSERT INTO `pilihan_jawaban` VALUES('67', '20', 'Cara berpikir seperti komputer dalam memecahkan masalah', '1');
INSERT INTO `pilihan_jawaban` VALUES('68', '20', 'Pemikiran seputar permasalahan komputer', '0');
INSERT INTO `pilihan_jawaban` VALUES('69', '20', 'Proses berpikir untuk pemrograman komputer', '0');
INSERT INTO `pilihan_jawaban` VALUES('70', '21', 'Getar', '0');
INSERT INTO `pilihan_jawaban` VALUES('71', '21', 'Fase', '1');
INSERT INTO `pilihan_jawaban` VALUES('72', '21', 'Input', '0');
INSERT INTO `pilihan_jawaban` VALUES('73', '21', 'Output', '0');
INSERT INTO `pilihan_jawaban` VALUES('74', '21', 'Transisi', '0');
INSERT INTO `pilihan_jawaban` VALUES('75', '22', 'Terapan', '0');
INSERT INTO `pilihan_jawaban` VALUES('76', '22', 'Murni', '1');
INSERT INTO `pilihan_jawaban` VALUES('77', '22', 'Semi-manual', '0');
INSERT INTO `pilihan_jawaban` VALUES('78', '22', 'Kontemporer', '0');
INSERT INTO `pilihan_jawaban` VALUES('79', '22', 'Implikasi', '0');
INSERT INTO `pilihan_jawaban` VALUES('141', '53', '<h1>', '1');
INSERT INTO `pilihan_jawaban` VALUES('142', '53', '<body>', '0');
INSERT INTO `pilihan_jawaban` VALUES('143', '53', '<head>', '0');
INSERT INTO `pilihan_jawaban` VALUES('144', '53', '<div>', '0');
INSERT INTO `pilihan_jawaban` VALUES('145', '54', 'Benar', '1');
INSERT INTO `pilihan_jawaban` VALUES('146', '54', 'Salah', '0');
INSERT INTO `pilihan_jawaban` VALUES('147', '56', '<h1>', '1');
INSERT INTO `pilihan_jawaban` VALUES('148', '56', '<body>', '0');
INSERT INTO `pilihan_jawaban` VALUES('149', '56', '<head>', '0');
INSERT INTO `pilihan_jawaban` VALUES('150', '56', '<div>', '0');
INSERT INTO `pilihan_jawaban` VALUES('151', '57', 'Benar', '1');
INSERT INTO `pilihan_jawaban` VALUES('152', '57', 'Salah', '0');

-- Table structure for `quiz` --
DROP TABLE IF EXISTS `quiz`;
CREATE TABLE `quiz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `kelas_ids` varchar(255) DEFAULT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `durasi_menit` int(11) NOT NULL DEFAULT 30,
  `jumlah_soal` int(11) DEFAULT 10,
  `random_soal` enum('Y','N') DEFAULT 'Y',
  `random_jawaban` enum('Y','N') DEFAULT 'Y',
  `status` enum('draft','published','archived') DEFAULT 'published',
  `kategori` enum('kuis','uts','uas') NOT NULL DEFAULT 'kuis',
  `access_key` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `deadline` datetime DEFAULT NULL,
  `max_attempts` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `guru_id` (`guru_id`),
  KEY `mapel_id` (`mapel_id`),
  KEY `kelas_id` (`kelas_id`),
  CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_ibfk_3` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `quiz` --
INSERT INTO `quiz` VALUES('1', '1', '1', '2', '2', 'Kuis 1 - PHP & Framework Basics', 'Kuis evaluasi pemahaman dasar PHP Native 8 dan MVC Architecture.', '30', '3', 'Y', 'Y', 'published', 'kuis', NULL, '2026-08-02 07:09:38', NULL, '1');
INSERT INTO `quiz` VALUES('2', '1', '1', '1', '1', 'Ujian Harian', 'Kerjakan Sesuai Dengan Soal Yang Dibawah', '5', '0', 'Y', 'Y', 'published', 'kuis', NULL, '2026-08-02 07:27:34', NULL, '1');
INSERT INTO `quiz` VALUES('4', '3', '7', '1', '1', 'Ujian Harian Perangkat Keras Pada Komputer', 'Kerjakan Sesuai Dengan Soal Yang Dibawah', '10', '4', 'Y', 'Y', 'published', 'kuis', NULL, '2026-08-02 22:23:25', '2026-08-05 23:59:00', '3');
INSERT INTO `quiz` VALUES('5', '3', '9', '1', '1', 'Berpikir Secara Komputasional', 'Kerjakan Sesuai Dengan Soal Yang Dibawah', '10', '10', 'Y', 'Y', 'published', 'kuis', NULL, '2026-08-04 22:35:10', '2026-08-05 23:59:00', '2');
INSERT INTO `quiz` VALUES('19', '3', '7', '1', '1', 'UTS Perangkat Keras Pada Komputer', '', '45', '3', 'Y', 'Y', 'published', 'uts', '7C3J2X', '2026-08-05 23:37:32', '2026-08-06 23:59:00', '1');
INSERT INTO `quiz` VALUES('20', '3', '7', '1', '1', 'UAS SEMESTER 1 PERANGKAT KERAS KOMPUTER', '', '45', '3', 'Y', 'Y', 'published', 'uas', '92DN49', '2026-08-05 23:48:29', '2026-08-06 23:48:00', '1');

-- Table structure for `quiz_susulan` --
DROP TABLE IF EXISTS `quiz_susulan`;
CREATE TABLE `quiz_susulan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `status` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `alasan` text DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_quiz_siswa` (`quiz_id`,`siswa_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `quiz_susulan` --
INSERT INTO `quiz_susulan` VALUES('1', '4', '3', 'ditolak', NULL, 'Disetujui Guru Pengampu', '2026-08-04 17:11:53', '2026-08-06 00:04:10');
INSERT INTO `quiz_susulan` VALUES('5', '5', '3', 'disetujui', NULL, 'Pa maaf agar bisa mengijinkan saya ikut lagi quis ini', '2026-08-04 22:37:41', '2026-08-04 22:38:33');
INSERT INTO `quiz_susulan` VALUES('8', '21', '3', 'disetujui', NULL, 'Disetujui Guru/Admin', '2026-08-05 23:56:35', '2026-08-06 00:04:36');

-- Table structure for `rapor_nilai_detail` --
DROP TABLE IF EXISTS `rapor_nilai_detail`;
CREATE TABLE `rapor_nilai_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rapor_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `mapel_nama_snapshot` varchar(150) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) NOT NULL DEFAULT 0.00,
  `kkm` decimal(5,2) DEFAULT 75.00,
  `predikat` varchar(10) DEFAULT 'B',
  `capaian_kompetensi` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_rapor_mapel` (`rapor_id`,`mapel_id`),
  KEY `mapel_id` (`mapel_id`),
  CONSTRAINT `rapor_nilai_detail_ibfk_1` FOREIGN KEY (`rapor_id`) REFERENCES `rapor_siswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rapor_nilai_detail_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `rapor_nilai_detail` --
INSERT INTO `rapor_nilai_detail` VALUES('1', '1', '1', 'Pemrograman Web dan Perangkat Bergerak', '35.00', '75.00', 'D', 'Perlu bimbingan dan tindak lanjut remedial pada beberapa kompetensi dasar mata pelajaran Pemrograman Web dan Perangkat Bergerak.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('2', '2', '1', 'Pemrograman Web dan Perangkat Bergerak', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Pemrograman Web dan Perangkat Bergerak.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('3', '3', '1', 'Pemrograman Web dan Perangkat Bergerak', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Pemrograman Web dan Perangkat Bergerak.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('4', '4', '1', 'Pemrograman Web dan Perangkat Bergerak', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Pemrograman Web dan Perangkat Bergerak.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('5', '5', '1', 'Pemrograman Web dan Perangkat Bergerak', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Pemrograman Web dan Perangkat Bergerak.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('6', '1', '2', 'Pemodelan Perangkat Lunak', '0.00', '75.00', 'D', 'Perlu bimbingan dan tindak lanjut remedial pada beberapa kompetensi dasar mata pelajaran Pemodelan Perangkat Lunak.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('7', '2', '2', 'Pemodelan Perangkat Lunak', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Pemodelan Perangkat Lunak.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('8', '3', '2', 'Pemodelan Perangkat Lunak', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Pemodelan Perangkat Lunak.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('9', '4', '2', 'Pemodelan Perangkat Lunak', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Pemodelan Perangkat Lunak.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('10', '5', '2', 'Pemodelan Perangkat Lunak', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Pemodelan Perangkat Lunak.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('11', '1', '7', 'Pengembangan Perangkat Lunak Dan Gim(DDPK)', '0.00', '75.00', 'D', 'Perlu bimbingan dan tindak lanjut remedial pada beberapa kompetensi dasar mata pelajaran Pengembangan Perangkat Lunak Dan Gim(DDPK).', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('12', '2', '7', 'Pengembangan Perangkat Lunak Dan Gim(DDPK)', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Pengembangan Perangkat Lunak Dan Gim(DDPK).', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('13', '3', '7', 'Pengembangan Perangkat Lunak Dan Gim(DDPK)', '99.60', '75.00', 'A', 'Menunjukkan pemahaman sangat baik dan konsisten dalam menuntaskan seluruh capaian pembelajaran Pengembangan Perangkat Lunak Dan Gim(DDPK).', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('14', '4', '7', 'Pengembangan Perangkat Lunak Dan Gim(DDPK)', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Pengembangan Perangkat Lunak Dan Gim(DDPK).', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('15', '5', '7', 'Pengembangan Perangkat Lunak Dan Gim(DDPK)', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Pengembangan Perangkat Lunak Dan Gim(DDPK).', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('16', '1', '8', 'Kompetensi Keahlian', '0.00', '75.00', 'D', 'Perlu bimbingan dan tindak lanjut remedial pada beberapa kompetensi dasar mata pelajaran Kompetensi Keahlian.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('17', '2', '8', 'Kompetensi Keahlian', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Kompetensi Keahlian.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('18', '3', '8', 'Kompetensi Keahlian', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Kompetensi Keahlian.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('19', '4', '8', 'Kompetensi Keahlian', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Kompetensi Keahlian.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('20', '5', '8', 'Kompetensi Keahlian', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran Kompetensi Keahlian.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('21', '1', '9', 'INFORMATIKA', '0.00', '75.00', 'D', 'Perlu bimbingan dan tindak lanjut remedial pada beberapa kompetensi dasar mata pelajaran INFORMATIKA.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('22', '2', '9', 'INFORMATIKA', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran INFORMATIKA.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('23', '3', '9', 'INFORMATIKA', '90.00', '75.00', 'A', 'Menunjukkan pemahaman sangat baik dan konsisten dalam menuntaskan seluruh capaian pembelajaran INFORMATIKA.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('24', '4', '9', 'INFORMATIKA', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran INFORMATIKA.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('25', '5', '9', 'INFORMATIKA', '0.00', '75.00', 'D', 'Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran INFORMATIKA.', '2026-09-19 08:47:06');
INSERT INTO `rapor_nilai_detail` VALUES('26', '7', '10', 'Kecerdasan Buatan & Cloud Computing', '92.50', '80.00', 'A', 'Sangat mahir merancang model AI dan integrasi pipeline cloud.', '2026-09-19 08:50:18');
INSERT INTO `rapor_nilai_detail` VALUES('37', '8', '10', 'Kecerdasan Buatan & Cloud Computing', '92.50', '80.00', 'A', 'Sangat mahir merancang model AI dan integrasi pipeline cloud.', '2026-09-19 08:51:01');

-- Table structure for `rapor_siswa` --
DROP TABLE IF EXISTS `rapor_siswa`;
CREATE TABLE `rapor_siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL,
  `semester` varchar(20) NOT NULL DEFAULT 'Ganjil',
  `rombel_id` int(11) NOT NULL,
  `kurikulum_id` int(11) NOT NULL,
  `fase_id` int(11) DEFAULT NULL,
  `kurikulum_nama_snapshot` varchar(150) DEFAULT NULL,
  `fase_nama_snapshot` varchar(100) DEFAULT NULL,
  `tanggal_cetak` date DEFAULT NULL,
  `status` enum('draft','terverifikasi','final') NOT NULL DEFAULT 'terverifikasi',
  `catatan_akademik` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_rapor_siswa_ta_sem` (`siswa_id`,`tahun_ajaran_id`,`semester`),
  KEY `tahun_ajaran_id` (`tahun_ajaran_id`),
  KEY `rombel_id` (`rombel_id`),
  KEY `kurikulum_id` (`kurikulum_id`),
  KEY `fase_id` (`fase_id`),
  CONSTRAINT `rapor_siswa_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rapor_siswa_ibfk_2` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rapor_siswa_ibfk_3` FOREIGN KEY (`rombel_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rapor_siswa_ibfk_4` FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rapor_siswa_ibfk_5` FOREIGN KEY (`fase_id`) REFERENCES `fase` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `rapor_siswa` --
INSERT INTO `rapor_siswa` VALUES('1', '1', '4', 'Ganjil', '2', '1', '2', 'Kurikulum Merdeka SMK', 'Fase F (Kelas XI - XII)', '2026-09-19', 'terverifikasi', 'Lulus kompetensi pembelajaran semester berjalan dengan baik.', '2026-09-19 08:47:06', '2026-09-19 08:47:06');
INSERT INTO `rapor_siswa` VALUES('2', '2', '4', 'Ganjil', '2', '1', '2', 'Kurikulum Merdeka SMK', 'Fase F (Kelas XI - XII)', '2026-09-19', 'terverifikasi', 'Lulus kompetensi pembelajaran semester berjalan dengan baik.', '2026-09-19 08:47:06', '2026-09-19 08:47:06');
INSERT INTO `rapor_siswa` VALUES('3', '3', '4', 'Ganjil', '1', '1', '1', 'Kurikulum Merdeka SMK', 'Fase E (Kelas X)', '2026-09-19', 'terverifikasi', 'Lulus kompetensi pembelajaran semester berjalan dengan baik.', '2026-09-19 08:47:06', '2026-09-19 08:47:06');
INSERT INTO `rapor_siswa` VALUES('4', '4', '4', 'Ganjil', '7', '1', '2', 'Kurikulum Merdeka SMK', 'Fase F (Kelas XI - XII)', '2026-09-19', 'terverifikasi', 'Lulus kompetensi pembelajaran semester berjalan dengan baik.', '2026-09-19 08:47:06', '2026-09-19 08:47:06');
INSERT INTO `rapor_siswa` VALUES('5', '5', '4', 'Ganjil', '1', '1', '1', 'Kurikulum Merdeka SMK', 'Fase E (Kelas X)', '2026-09-19', 'terverifikasi', 'Lulus kompetensi pembelajaran semester berjalan dengan baik.', '2026-09-19 08:47:06', '2026-09-19 08:47:06');
INSERT INTO `rapor_siswa` VALUES('6', '6', '4', 'Ganjil', '1', '1', '1', 'Kurikulum Merdeka SMK', 'Fase E (Kelas X)', '2026-09-19', 'terverifikasi', 'Menunjukkan kemajuan belajar dan kedisiplinan yang baik.', '2026-09-19 08:50:17', '2026-09-19 08:50:17');
INSERT INTO `rapor_siswa` VALUES('7', '6', '6', 'Ganjil', '8', '2', NULL, 'Kurikulum Vokasi Industri 2029', NULL, NULL, 'terverifikasi', NULL, '2026-09-19 08:50:18', '2026-09-19 08:50:18');
INSERT INTO `rapor_siswa` VALUES('8', '1', '6', 'Ganjil', '8', '2', NULL, 'Kurikulum Vokasi Industri 2029', NULL, NULL, 'terverifikasi', NULL, '2026-09-19 08:51:01', '2026-09-19 08:51:01');

-- Table structure for `roles` --
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `roles` --
INSERT INTO `roles` VALUES('1', 'Administrator', 'Sistem Administrator / Operator Sekolah');
INSERT INTO `roles` VALUES('2', 'Guru', 'Tenaga Pengajar SMK Muthia Harapan Cicalengka');
INSERT INTO `roles` VALUES('3', 'Siswa', 'Peserta Didik SMK Muthia Harapan Cicalengka');
INSERT INTO `roles` VALUES('4', 'Kepala Sekolah', 'Pimpinan / Kepala Sekolah');

-- Table structure for `rombel_kurikulum` --
DROP TABLE IF EXISTS `rombel_kurikulum`;
CREATE TABLE `rombel_kurikulum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rombel_id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL,
  `kurikulum_id` int(11) NOT NULL,
  `fase_id` int(11) DEFAULT NULL,
  `status` enum('aktif','selesai','non-aktif') NOT NULL DEFAULT 'aktif',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_rombel_ta_kur` (`tahun_ajaran_id`,`rombel_id`,`kurikulum_id`),
  KEY `rombel_id` (`rombel_id`),
  KEY `kurikulum_id` (`kurikulum_id`),
  KEY `fase_id` (`fase_id`),
  CONSTRAINT `rombel_kurikulum_ibfk_1` FOREIGN KEY (`rombel_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rombel_kurikulum_ibfk_2` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rombel_kurikulum_ibfk_3` FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rombel_kurikulum_ibfk_4` FOREIGN KEY (`fase_id`) REFERENCES `fase` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `rombel_kurikulum` --
INSERT INTO `rombel_kurikulum` VALUES('1', '1', '4', '1', '1', 'aktif', '2026-09-19 08:47:06', '2026-09-19 08:47:06');
INSERT INTO `rombel_kurikulum` VALUES('2', '2', '4', '1', '2', 'aktif', '2026-09-19 08:47:06', '2026-09-19 08:47:06');
INSERT INTO `rombel_kurikulum` VALUES('3', '3', '4', '1', '2', 'aktif', '2026-09-19 08:47:06', '2026-09-19 08:47:06');
INSERT INTO `rombel_kurikulum` VALUES('4', '7', '4', '1', '2', 'aktif', '2026-09-19 08:47:06', '2026-09-19 08:47:06');
INSERT INTO `rombel_kurikulum` VALUES('11', '8', '4', '1', '1', 'selesai', '2026-09-19 09:03:09', '2026-09-19 09:03:09');
INSERT INTO `rombel_kurikulum` VALUES('12', '8', '5', '1', '1', 'selesai', '2026-09-19 09:03:09', '2026-09-19 09:03:09');
INSERT INTO `rombel_kurikulum` VALUES('13', '8', '6', '2', NULL, 'aktif', '2026-09-19 09:03:09', '2026-09-19 09:03:09');

-- Table structure for `semester` --
DROP TABLE IF EXISTS `semester`;
CREATE TABLE `semester` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_semester` enum('Ganjil','Genap') NOT NULL,
  `status` enum('aktif','non-aktif') DEFAULT 'aktif',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `semester` --
INSERT INTO `semester` VALUES('1', 'Ganjil', 'aktif');
INSERT INTO `semester` VALUES('2', 'Genap', 'non-aktif');

-- Table structure for `sertifikat` --
DROP TABLE IF EXISTS `sertifikat`;
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

-- Table structure for `settings` --
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `settings` --
INSERT INTO `settings` VALUES('alamat', 'Jalan Babakan Peuteuy Nomor 300, Desa Babakanpeuteuy, Kecamatan Cicalengka, Kabupaten Bandung, Jawa', '2026-08-02 08:37:01');
INSERT INTO `settings` VALUES('api_key', 'smkmh_live_api_88923a19e83c7410294b', '2026-08-02 08:30:08');
INSERT INTO `settings` VALUES('kepala_sekolah', 'H. ASEP SAEPULLOH, S. Ag', '2026-08-02 08:37:01');
INSERT INTO `settings` VALUES('landing_email', 'info@smkmh-cicalengka.sch.id', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_hero_badge', 'Portal Pembelajaran Digital', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_hero_card_desc', 'Materi, CBT, Quiz, Absensi QR Code, & Laporan Real-time', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_hero_card_title', 'KBM Digital Terpadu', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_hero_desc', 'Sistem Manajemen Pembelajaran Digital Interaktif, Transparan, dan Modern untuk Membentuk Generasi Unggul Siap Kerja.', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_hero_title', 'E-Learning SMK Muthia Harapan Cicalengka', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_kontak_tag', 'Hubungi Kami', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_kontak_title', 'Lokasi & Kontak Sekolah', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_maps_url', 'https://maps.google.com/maps?q=Cicalengka&t=&z=13&ie=UTF8&iwloc=&output=embed', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_misi_desc', 'Mengembangkan kurikulum industri & sertifikasi kompetensi keahlian.', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_misi_title', 'Misi Presisi', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_profil_desc', 'SMK Muthia Harapan Cicalengka berkomitmen memberikan pendidikan kejuruan berkualitas tinggi berbasis teknologi informasi dan industri modern di Jawa Barat.', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_profil_tag', 'Profil Sekolah', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_profil_title', 'Mencetak Lulusan Berkarakter & Competent', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_video_url', 'https://www.youtube.com/embed/dQw4w9WgXcQ', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_visi_desc', 'Menjadi SMK Unggulan berstandar Nasional berbasis Teknologi & Imtaq.', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('landing_visi_title', 'Visi Utama', '2026-09-16 20:48:30');
INSERT INTO `settings` VALUES('logo', 'logo_1785634621_6a6e9f3d37d9e.jpg', '2026-08-02 08:37:01');
INSERT INTO `settings` VALUES('lokasi_sekolah_lat', '-6.984042', '2026-09-16 20:50:29');
INSERT INTO `settings` VALUES('lokasi_sekolah_lng', '107.838612', '2026-09-16 20:50:29');
INSERT INTO `settings` VALUES('lokasi_sekolah_nama', 'SMK Muthia Harapan Cicalengka', '2026-09-16 20:50:29');
INSERT INTO `settings` VALUES('lokasi_sekolah_radius', '150', '2026-09-16 20:50:29');
INSERT INTO `settings` VALUES('nama_sekolah', 'SMK Muthia Harapan Cicalengka', '2026-08-02 08:37:01');
INSERT INTO `settings` VALUES('npsn', '69725846', '2026-08-02 08:37:01');
INSERT INTO `settings` VALUES('presensi_jam_masuk_batas', '07:30', '2026-09-16 20:50:29');
INSERT INTO `settings` VALUES('presensi_jam_masuk_mulai', '06:00', '2026-09-16 20:50:29');
INSERT INTO `settings` VALUES('presensi_jam_pulang_mulai', '15:00', '2026-09-16 20:50:29');
INSERT INTO `settings` VALUES('semester', 'Ganjil', '2026-08-03 09:13:48');
INSERT INTO `settings` VALUES('sertifikat_active_template', 'kelulusan', '2026-08-02 09:12:36');
INSERT INTO `settings` VALUES('smtp_crypto', 'tls', '2026-08-02 08:30:08');
INSERT INTO `settings` VALUES('smtp_host', 'smtp.gmail.com', '2026-08-02 08:30:08');
INSERT INTO `settings` VALUES('smtp_pass', '••••••••••••', '2026-08-02 08:30:08');
INSERT INTO `settings` VALUES('smtp_port', '587', '2026-08-02 08:30:08');
INSERT INTO `settings` VALUES('smtp_user', 'elearning@smkmuthiaharapan.sch.id', '2026-08-02 08:30:08');
INSERT INTO `settings` VALUES('tahun_ajaran', '2026/2027', '2026-08-03 09:13:48');
INSERT INTO `settings` VALUES('telepon', '(022) 7950123', '2026-08-02 08:37:01');
INSERT INTO `settings` VALUES('tema', 'light', '2026-08-02 08:30:08');

-- Table structure for `siswa` --
DROP TABLE IF EXISTS `siswa`;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `siswa` --
INSERT INTO `siswa` VALUES('1', '3', '20231001', '0061234567', 'Muhammad Rizky Pratama', '2', '1', 'L', '085712345678', 'Jl. Cikopo Cicalengka No. 8', 'aktif');
INSERT INTO `siswa` VALUES('2', '6', '20231002', '0067654321', 'Siti Rahmawati', '2', '1', 'P', '085798765432', 'Jl. Nagreg No. 15, Bandung', 'aktif');
INSERT INTO `siswa` VALUES('3', '7', '522402055', '522402055', 'AGUNG RIFALDI', '1', '1', 'L', '082317864874', 'Cicalengka, Bandung', 'aktif');
INSERT INTO `siswa` VALUES('4', '8', '522402056', '522402056', 'Dwi Handoko', '7', '1', 'L', '82366965785', '', 'aktif');
INSERT INTO `siswa` VALUES('5', '10', '230104001', '230104001', 'Yulia', '1', '1', 'P', '82366965785', '', 'aktif');
INSERT INTO `siswa` VALUES('6', '1', 'S2026097583', 'S2026097583', 'Administrator Utama', '1', '1', 'L', NULL, NULL, 'aktif');

-- Table structure for `siswa_mapel_enrollment` --
DROP TABLE IF EXISTS `siswa_mapel_enrollment`;
CREATE TABLE `siswa_mapel_enrollment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `enrolled_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `siswa_mapel_enrollment` --
INSERT INTO `siswa_mapel_enrollment` VALUES('1', '1', '2', '2', NULL, '2026-08-03 15:53:02');
INSERT INTO `siswa_mapel_enrollment` VALUES('2', '3', '7', '3', NULL, '2026-08-03 16:02:08');
INSERT INTO `siswa_mapel_enrollment` VALUES('3', '3', '9', '3', NULL, '2026-08-04 21:42:21');

-- Table structure for `soal` --
DROP TABLE IF EXISTS `soal`;
CREATE TABLE `soal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `jenis_soal` enum('pg','essay','tf') NOT NULL DEFAULT 'pg',
  `pertanyaan` text NOT NULL,
  `file_gambar` varchar(255) DEFAULT NULL,
  `bobot` int(11) DEFAULT 10,
  `gambar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `soal_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `soal` --
INSERT INTO `soal` VALUES('1', '1', 'pg', 'Apa kepanjangan dari MVC dalam pengembangan perangkat lunak?', NULL, '35', NULL);
INSERT INTO `soal` VALUES('2', '1', 'pg', 'Fungsi utama PDO pada PHP 8 adalah untuk?', NULL, '35', NULL);
INSERT INTO `soal` VALUES('3', '1', 'essay', 'Jelaskan perbedaan mendasar antara HTTP GET dan POST!', NULL, '30', NULL);
INSERT INTO `soal` VALUES('4', '2', 'pg', 'Apa yang Dimaksud Dengan Website ?', NULL, '100', NULL);
INSERT INTO `soal` VALUES('6', '4', 'pg', 'Apa yang dimaksud dengan hardware ?', NULL, '10', NULL);
INSERT INTO `soal` VALUES('7', '4', 'tf', 'Hardware Merupakan perangkat keras pada komputer yang bisa di raba dan di lihat bentuk fisiknya', NULL, '10', NULL);
INSERT INTO `soal` VALUES('8', '4', 'essay', 'Apa yang dimaksud dengan Software?', NULL, '10', NULL);
INSERT INTO `soal` VALUES('9', '4', 'pg', 'Apa yang dimaksud dengan brainware?', NULL, '10', NULL);
INSERT INTO `soal` VALUES('10', '4', 'essay', 'Jelaskan dan Sebutkan Perbedaan Hardware dan Software?', NULL, '10', NULL);
INSERT INTO `soal` VALUES('11', '4', 'pg', 'apa nama komponen gambar dibawah ini ?', NULL, '10', 'soal_1785831197_6a719f1d8e84c.webp');
INSERT INTO `soal` VALUES('12', '4', 'pg', 'apa nama komponen gambar dibawah ini ?', NULL, '10', 'soal_1785947352_6a7364d81d236.jpg');
INSERT INTO `soal` VALUES('13', '5', 'pg', 'Salah satu penerapan kecerdasan buatan dalam manufaktur dikenal sebagai…', NULL, '10', NULL);
INSERT INTO `soal` VALUES('14', '5', 'pg', 'Apa keuntungan utama dari konsep Berpikir Komputasional?', NULL, '10', NULL);
INSERT INTO `soal` VALUES('15', '5', 'pg', 'Teknologi sistem informasi geografis memiliki banyak manfaat kecuali…', NULL, '10', NULL);
INSERT INTO `soal` VALUES('16', '5', 'pg', 'Apa yang dimaksud dengan proses generalisasi dalam Berpikir Komputasional?', NULL, '10', NULL);
INSERT INTO `soal` VALUES('17', '5', 'pg', 'Proses evaluasi dalam komponen Berpikir Komputasional dilakukan untuk mengevaluasi…', NULL, '10', NULL);
INSERT INTO `soal` VALUES('18', '5', 'pg', 'Apa arti “data manipulation” dalam konsep Berpikir Komputasional?', NULL, '10', NULL);
INSERT INTO `soal` VALUES('19', '5', 'pg', 'Komputasi sering digunakan untuk mendukung industri otomasi pada sektor…', NULL, '10', NULL);
INSERT INTO `soal` VALUES('20', '5', 'pg', 'Berpikir Komputasional dapat didefinisikan sebagai…', NULL, '10', NULL);
INSERT INTO `soal` VALUES('21', '5', 'pg', 'Diagram fase pada mesin Moore akan menampilkan keluaran pada setiap…', NULL, '10', NULL);
INSERT INTO `soal` VALUES('22', '5', 'pg', 'Kajian dalam bidang komputasi masih kurang berkembang karena lebih condong sebagai kajian teori…', NULL, '10', NULL);
INSERT INTO `soal` VALUES('53', '19', 'pg', 'Perhatikan gambar diagram HTML berikut! Tag manakah yang digunakan untuk membuat judul utama?', NULL, '10', NULL);
INSERT INTO `soal` VALUES('54', '19', 'tf', 'PHP adalah bahasa pemrograman server-side.', NULL, '10', NULL);
INSERT INTO `soal` VALUES('55', '19', 'essay', 'Jelaskan fungsi utama dari arsitektur jaringan pada gambar berikut!', NULL, '20', 'soal_1785947921_6a736711e84b6.png');
INSERT INTO `soal` VALUES('56', '20', 'pg', 'Perhatikan gambar diagram HTML berikut! Tag manakah yang digunakan untuk membuat judul utama?', NULL, '10', NULL);
INSERT INTO `soal` VALUES('57', '20', 'tf', 'PHP adalah bahasa pemrograman server-side.', NULL, '10', NULL);
INSERT INTO `soal` VALUES('58', '20', 'essay', 'Jelaskan fungsi utama dari arsitektur jaringan pada gambar berikut!', NULL, '20', 'soal_1785948522_6a73696a99b3c.png');

-- Table structure for `supervisi_guru` --
DROP TABLE IF EXISTS `supervisi_guru`;
CREATE TABLE `supervisi_guru` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_id` int(11) NOT NULL,
  `kepsek_id` int(11) NOT NULL,
  `tanggal_supervisi` date NOT NULL,
  `mapel_id` int(11) DEFAULT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `skor_perencanaan` decimal(5,2) DEFAULT 0.00,
  `skor_pelaksanaan` decimal(5,2) DEFAULT 0.00,
  `skor_evaluasi` decimal(5,2) DEFAULT 0.00,
  `skor_kedisiplinan` decimal(5,2) DEFAULT 0.00,
  `nilai_akhir` decimal(5,2) DEFAULT 0.00,
  `predikat` varchar(20) DEFAULT 'Baik',
  `catatan_kekuatan` text DEFAULT NULL,
  `catatan_perbaikan` text DEFAULT NULL,
  `rekomendasi_tindak_lanjut` text DEFAULT NULL,
  `status` enum('draft','final') DEFAULT 'final',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_sup_guru` (`guru_id`),
  KEY `idx_sup_tgl` (`tanggal_supervisi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `tahun_ajaran` --
DROP TABLE IF EXISTS `tahun_ajaran`;
CREATE TABLE `tahun_ajaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tahun` varchar(20) NOT NULL,
  `status` enum('aktif','non-aktif') DEFAULT 'aktif',
  `tahun_ajaran` varchar(20) DEFAULT NULL,
  `semester` varchar(20) DEFAULT 'Ganjil',
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `tahun_ajaran` --
INSERT INTO `tahun_ajaran` VALUES('4', '2026/2027', 'aktif', '2026/2027', 'Ganjil', '1', '2026-08-03 09:13:48');
INSERT INTO `tahun_ajaran` VALUES('5', '2027/2028', 'non-aktif', '2027/2028', 'Ganjil', '0', '2026-09-19 08:50:17');
INSERT INTO `tahun_ajaran` VALUES('6', '2029/2030', 'non-aktif', '2029/2030', 'Ganjil', '0', '2026-09-19 08:50:17');

-- Table structure for `tugas` --
DROP TABLE IF EXISTS `tugas`;
CREATE TABLE `tugas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `kelas_ids` varchar(255) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `tugas` --
INSERT INTO `tugas` VALUES('1', '1', '1', '2', '2', 'Tugas 1: Membuat Auth Login MVC', 'Silakan buat modul login lengkap dengan CSRF token dan Session Handling.', 'panduan_tugas1.pdf', '2026-08-09 07:09:38', '2026-08-02 07:09:38');
INSERT INTO `tugas` VALUES('2', '3', '7', '1', '1', 'Perangkat Keras Pada Komputer', 'Silahkan Kerjakan Sesuai dengan ketentuan soal yang saya kirim dan ingat jika tidak mengerjakan maka akan mendapatkan sanksi', 'tugas_1785683061_6a6f5c75a7c77.pdf', '2026-08-05 23:59:00', '2026-08-02 22:04:21');
INSERT INTO `tugas` VALUES('3', '3', '9', '1', '1', 'Berpikir Secara Komputasional', 'Silahkan kerjakan soal berikut ini', 'tugas_1785856619_6a72026b00928.pdf', '2026-08-05 23:59:00', '2026-08-04 22:16:59');

-- Table structure for `tugas_susulan` --
DROP TABLE IF EXISTS `tugas_susulan`;
CREATE TABLE `tugas_susulan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tugas_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `status` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tugas_siswa` (`tugas_id`,`siswa_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `tugas_susulan` --
INSERT INTO `tugas_susulan` VALUES('1', '2', '3', 'ditolak', 'mohon maaf pa untuk tugas perangkat keras apakah bisa di buka lagi atau diperpanjang masa pengerjaanya?', '2026-08-04 16:35:32', '2026-08-04 16:36:48');
INSERT INTO `tugas_susulan` VALUES('2', '1', '1', 'pending', 'Sakit / Izin Medis', '2026-09-02 20:51:14', '2026-09-02 21:26:29');

-- Table structure for `tujuan_pembelajaran` --
DROP TABLE IF EXISTS `tujuan_pembelajaran`;
CREATE TABLE `tujuan_pembelajaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cp_id` int(11) NOT NULL,
  `guru_id` int(11) DEFAULT NULL,
  `kode_tp` varchar(50) NOT NULL,
  `materi_pokok` varchar(255) DEFAULT NULL,
  `deskripsi` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cp_id` (`cp_id`),
  KEY `idx_tp_guru` (`guru_id`),
  CONSTRAINT `tujuan_pembelajaran_ibfk_1` FOREIGN KEY (`cp_id`) REFERENCES `capaian_pembelajaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `tujuan_pembelajaran` --
INSERT INTO `tujuan_pembelajaran` VALUES('1', '1', NULL, 'TP-01.1', 'Arsitektur Web MVC', 'Memahami alur data MVC dan integrasi database relational.', '2026-09-19 08:47:06', '2026-09-19 08:47:06');
INSERT INTO `tujuan_pembelajaran` VALUES('2', '1', NULL, 'TP-01.2', 'Pengolahan Data & Asesmen', 'Menguasai manipulasi query CRUD dan kalkulasi nilai secara dinamis.', '2026-09-19 08:47:06', '2026-09-19 08:47:06');

-- Table structure for `ujian` --
DROP TABLE IF EXISTS `ujian`;
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

-- Dumping data for `ujian` --
INSERT INTO `ujian` VALUES('1', '1', '1', '2', 'Ujian Tengah Semester (UTS) Web Programming', 'UTS', '60', '2026-08-02 07:09:38', '2026-09-01 07:09:38', 'SMKMH1', '1');

-- Table structure for `users` --
DROP TABLE IF EXISTS `users`;
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
  `last_seen` datetime DEFAULT NULL,
  `fcm_token` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `users` --
INSERT INTO `users` VALUES('1', '1', 'admin', 'admin@smkmh-cicalengka.sch.id', 'y$uzlMinqHn61YNzYHO7JAMeAygpDYBuX7l1qcsIvctRwtk6lFbEJju', 'Administrator Utama', 'default_avatar.png', 'active', NULL, '2026-08-02 07:09:37', '2026-08-10 21:49:07', '2026-08-10 21:49:07', NULL);
INSERT INTO `users` VALUES('2', '2', 'guru', 'guru@smkmh-cicalengka.sch.id', 'y$L7z.BOsIzrCmQ3DzBMIi0.zrA6KTFtfkgf1yRfHP8K2fKJE9OjlnO', 'Drs. Ahmad Hidayat, M.Pd.', 'default_avatar.png', 'active', NULL, '2026-08-02 07:09:37', '2026-08-05 13:51:45', NULL, NULL);
INSERT INTO `users` VALUES('3', '3', 'siswa', 'agung.guru@smkmuthia.sch.id', '$2y$10$UdiGbshHx9oePjOAopI4FuCU9JN/pxspzKEsrgbuumzcsUAL9L.aS', 'Muhammad Rizky Pratama', 'default_avatar.png', 'active', NULL, '2026-08-02 07:09:37', '2026-08-11 21:25:14', NULL, NULL);
INSERT INTO `users` VALUES('4', '4', 'kepsek', 'kepsek@smkmh-cicalengka.sch.id', 'y$vDUgWL4AaAJA/8X95QXKA.IYjnC8ChmMjc1HJbMUtoWvXBv3kB.ru', 'H. ASEP SAEPULLOH, S.Ag', 'avatar_kepsek_4_1785716342.jpg', 'active', NULL, '2026-08-02 07:09:37', '2026-08-03 07:19:02', NULL, NULL);
INSERT INTO `users` VALUES('5', '2', 'guru2', 'budi@smkmh-cicalengka.sch.id', 'y$L7z.BOsIzrCmQ3DzBMIi0.zrA6KTFtfkgf1yRfHP8K2fKJE9OjlnO', 'Budi Santoso, S.T.', 'default_avatar.png', 'active', NULL, '2026-08-02 07:09:37', '2026-08-02 07:09:37', NULL, NULL);
INSERT INTO `users` VALUES('6', '3', 'siswa2', 'siti@smkmh-cicalengka.sch.id', '$2y$10$qS2iG1GqV2L3N.a5v7Z0eeR2h8kL4A2.v7qB6C8m9D0e1F2g3H4i5', 'Siti Rahmawati', 'default_avatar.png', 'active', NULL, '2026-08-02 07:09:37', '2026-08-02 07:09:37', NULL, NULL);
INSERT INTO `users` VALUES('7', '3', 'agung', 'agung.siswa@smkmuthia.sch.id', 'y$Zv5aC7vSYBo2xhEzpJaTd.1UDtgoPox4tn4QfEy5X78ppMAyvXZA6', 'AGUNG RIFALDI', 'profile_1785912791_6a72ddd7ab8d6.png', 'active', NULL, '2026-08-02 08:41:59', '2026-08-11 21:36:40', '2026-08-10 21:21:27', NULL);
INSERT INTO `users` VALUES('8', '3', 'dwihan', 'dwihan@gmail.com', '$2y$10$a6FdhD.jAlOsYgHuyiQdQO.VuN193SooQ73RGZemL5vCIUiwstA1i', 'Dwi Handoko', 'default_avatar.png', 'active', NULL, '2026-08-02 09:43:03', '2026-08-02 09:43:03', NULL, NULL);
INSERT INTO `users` VALUES('9', '2', 'agg023', 'agg023@smkmh-cicalengka.sch.id', 'y$L7z.BOsIzrCmQ3DzBMIi0.zrA6KTFtfkgf1yRfHP8K2fKJE9OjlnO', 'AGUNG RIFALDI, S.Tr. Kom', 'profile_1785912173_6a72db6dd8de0.png', 'active', NULL, '2026-08-02 09:48:07', '2026-08-05 13:55:14', NULL, NULL);
INSERT INTO `users` VALUES('10', '3', 'yusan', 'yusan@gmail.com', '$2y$10$pD37Ho1013xL1jpHj6FZnOVS7SLddYuS6FEuPPpHftWEbpQxt8l8e', 'Yulia', 'default_avatar.png', 'active', NULL, '2026-08-05 12:57:45', '2026-08-05 12:57:45', NULL, NULL);

-- Table structure for `video` --
DROP TABLE IF EXISTS `video`;
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


-- Extra TBSM Class & Student Seeds
INSERT IGNORE INTO `users` (`id`, `role_id`, `username`, `email`, `password`, `full_name`, `avatar`, `status`) VALUES
('11', '3', 'TBSM901', 'TBSM901@sekolah.id', '$2y$10$Zv5aC7vSYBo2xhEzpJaTd.1UDtgoPox4tn4QfEy5X78ppMAyvXZA6', 'Siswa XTBSM1 Pratama', 'default_avatar.png', 'active'),
('12', '3', 'TBSM1001', 'TBSM1001@sekolah.id', '$2y$10$Zv5aC7vSYBo2xhEzpJaTd.1UDtgoPox4tn4QfEy5X78ppMAyvXZA6', 'Siswa XITBSM1 Pratama', 'default_avatar.png', 'active'),
('13', '3', 'TBSM1101', 'TBSM1101@sekolah.id', '$2y$10$Zv5aC7vSYBo2xhEzpJaTd.1UDtgoPox4tn4QfEy5X78ppMAyvXZA6', 'Siswa XIITBSM1 Pratama', 'default_avatar.png', 'active');

INSERT IGNORE INTO `kelas` (`id`, `tingkat`, `nama_kelas`, `jurusan_id`, `wali_kelas_id`) VALUES
(9, 'X', 'X TBSM 1', 4, NULL),
(10, 'XI', 'XI TBSM 1', 4, NULL),
(11, 'XII', 'XII TBSM 1', 4, NULL);

INSERT IGNORE INTO `siswa` (`id`, `user_id`, `nis`, `nisn`, `nama_lengkap`, `kelas_id`, `jurusan_id`, `jenis_kelamin`, `status`) VALUES
('8', '11', 'TBSM901', '00912345', 'Siswa XTBSM1 Pratama', '9', '4', 'L', 'aktif'),
('9', '12', 'TBSM1001', '001012345', 'Siswa XITBSM1 Pratama', '10', '4', 'L', 'aktif'),
('10', '13', 'TBSM1101', '001112345', 'Siswa XIITBSM1 Pratama', '11', '4', 'L', 'aktif');

SET FOREIGN_KEY_CHECKS = 1;
