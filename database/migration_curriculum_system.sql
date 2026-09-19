-- ========================================================================
-- MIGRATION: SISTEM KURIKULUM DINAMIS, CP/TP, ASESMEN & E-RAPOR SKALABEL
-- SMK Muthia Harapan Cicalengka
-- MySQL / MariaDB (InnoDB, UTF8MB4)
-- ========================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. TABEL MASTER KURIKULUM
CREATE TABLE IF NOT EXISTS `kurikulum` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kode` VARCHAR(30) NOT NULL UNIQUE,
    `nama` VARCHAR(150) NOT NULL,
    `tahun_mulai` INT NOT NULL,
    `tahun_selesai` INT NULL,
    `status` ENUM('aktif', 'non-aktif', 'arsip') NOT NULL DEFAULT 'aktif',
    `deskripsi` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABEL FASE / TINGKAT STRUKTUR
CREATE TABLE IF NOT EXISTS `fase` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kurikulum_id` INT NOT NULL,
    `kode` VARCHAR(30) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `tingkat_kelas` VARCHAR(50) NULL,
    `keterangan` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `u_kurikulum_fase` (`kurikulum_id`, `kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABEL ROMBEL KURIKULUM (Hubungan Kelas + Tahun Ajaran + Kurikulum)
CREATE TABLE IF NOT EXISTS `rombel_kurikulum` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rombel_id` INT NOT NULL,
    `tahun_ajaran_id` INT NOT NULL,
    `kurikulum_id` INT NOT NULL,
    `fase_id` INT NULL,
    `status` ENUM('aktif', 'selesai', 'non-aktif') NOT NULL DEFAULT 'aktif',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`rombel_id`) REFERENCES `kelas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`fase_id`) REFERENCES `fase`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `u_rombel_ta_kur` (`tahun_ajaran_id`, `rombel_id`, `kurikulum_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABEL STRUKTUR MATA PELAJARAN KURIKULUM
CREATE TABLE IF NOT EXISTS `kurikulum_mapel` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kurikulum_id` INT NOT NULL,
    `mapel_id` INT NOT NULL,
    `fase_id` INT NULL,
    `tingkat` VARCHAR(20) NULL,
    `jurusan_id` INT NULL,
    `kelompok_mapel` VARCHAR(50) DEFAULT 'Kejuruan',
    `alokasi_jp` INT DEFAULT 2,
    `kkm` DECIMAL(5,2) DEFAULT 75.00,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`fase_id`) REFERENCES `fase`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `u_kur_mapel_fase` (`kurikulum_id`, `mapel_id`, `fase_id`, `jurusan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABEL CAPAIAN PEMBELAJARAN (CP)
CREATE TABLE IF NOT EXISTS `capaian_pembelajaran` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kurikulum_id` INT NOT NULL,
    `mapel_id` INT NOT NULL,
    `fase_id` INT NULL,
    `kode_cp` VARCHAR(50) NOT NULL,
    `elemen` VARCHAR(150) NULL,
    `deskripsi` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`fase_id`) REFERENCES `fase`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABEL TUJUAN PEMBELAJARAN (TP)
CREATE TABLE IF NOT EXISTS `tujuan_pembelajaran` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cp_id` INT NOT NULL,
    `kode_tp` VARCHAR(50) NOT NULL,
    `materi_pokok` VARCHAR(255) NULL,
    `deskripsi` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`cp_id`) REFERENCES `capaian_pembelajaran`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABEL KOMPONEN & BOBOT PENILAIAN DINAMIS PER KURIKULUM
CREATE TABLE IF NOT EXISTS `komponen_penilaian` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kurikulum_id` INT NOT NULL,
    `nama_komponen` VARCHAR(100) NOT NULL,
    `kode_komponen` VARCHAR(50) NOT NULL,
    `bobot_persen` DECIMAL(5,2) NOT NULL DEFAULT 25.00,
    `is_active` TINYINT(1) DEFAULT 1,
    `deskripsi` VARCHAR(255) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. TABEL ASESMEN PEMBELAJARAN
CREATE TABLE IF NOT EXISTS `asesmen` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tahun_ajaran_id` INT NOT NULL,
    `semester` VARCHAR(20) NOT NULL DEFAULT 'Ganjil',
    `rombel_id` INT NOT NULL,
    `mapel_id` INT NOT NULL,
    `kurikulum_id` INT NOT NULL,
    `guru_id` INT NOT NULL,
    `cp_id` INT NULL,
    `tp_id` INT NULL,
    `jenis_asesmen` VARCHAR(50) NOT NULL DEFAULT 'formatif',
    `nama_asesmen` VARCHAR(150) NOT NULL,
    `tanggal` DATE NOT NULL,
    `nilai_maksimum` DECIMAL(5,2) DEFAULT 100.00,
    `bobot` DECIMAL(5,2) DEFAULT 1.00,
    `keterangan` TEXT NULL,
    `ref_tugas_id` INT NULL,
    `ref_quiz_id` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`rombel_id`) REFERENCES `kelas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`guru_id`) REFERENCES `guru`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`cp_id`) REFERENCES `capaian_pembelajaran`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`tp_id`) REFERENCES `tujuan_pembelajaran`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. TABEL NILAI ASESMEN SISWA
CREATE TABLE IF NOT EXISTS `nilai_asesmen_siswa` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `asesmen_id` INT NOT NULL,
    `siswa_id` INT NOT NULL,
    `nilai` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `catatan` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`asesmen_id`) REFERENCES `asesmen`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `u_asesmen_siswa` (`asesmen_id`, `siswa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. TABEL HEADER RAPOR SISWA (Dinamis & Terisolasi Per Periode & Snapshot Kurikulum)
CREATE TABLE IF NOT EXISTS `rapor_siswa` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `siswa_id` INT NOT NULL,
    `tahun_ajaran_id` INT NOT NULL,
    `semester` VARCHAR(20) NOT NULL DEFAULT 'Ganjil',
    `rombel_id` INT NOT NULL,
    `kurikulum_id` INT NOT NULL,
    `fase_id` INT NULL,
    `kurikulum_nama_snapshot` VARCHAR(150) NULL,
    `fase_nama_snapshot` VARCHAR(100) NULL,
    `tanggal_cetak` DATE NULL,
    `status` ENUM('draft', 'terverifikasi', 'final') NOT NULL DEFAULT 'terverifikasi',
    `catatan_akademik` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`rombel_id`) REFERENCES `kelas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`kurikulum_id`) REFERENCES `kurikulum`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`fase_id`) REFERENCES `fase`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `u_rapor_siswa_ta_sem` (`siswa_id`, `tahun_ajaran_id`, `semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. TABEL NILAI DETAIL RAPOR (Mata Pelajaran Dinamis Sebagai Baris)
CREATE TABLE IF NOT EXISTS `rapor_nilai_detail` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rapor_id` INT NOT NULL,
    `mapel_id` INT NOT NULL,
    `mapel_nama_snapshot` VARCHAR(150) NULL,
    `nilai_akhir` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `kkm` DECIMAL(5,2) DEFAULT 75.00,
    `predikat` VARCHAR(10) DEFAULT 'B',
    `capaian_kompetensi` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`rapor_id`) REFERENCES `rapor_siswa`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `u_rapor_mapel` (`rapor_id`, `mapel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
