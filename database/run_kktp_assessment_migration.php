<?php
/**
 * Migration: Modul Pembelajaran & Penilaian Berbasis CP -> TP -> KKTP -> ASESMEN -> NILAI -> KETERCAPAIAN
 */
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "Starting Migration for CP -> TP -> KKTP -> Asesmen -> Nilai -> Ketercapaian..." . PHP_EOL;

// 1. Alter capaian_pembelajaran if needed
$cpCols = $db->query("SHOW COLUMNS FROM capaian_pembelajaran")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('status', $cpCols)) {
    $db->exec("ALTER TABLE capaian_pembelajaran ADD COLUMN status ENUM('aktif','nonaktif','arsip') NOT NULL DEFAULT 'aktif' AFTER deskripsi");
    echo "✓ Added column 'status' to capaian_pembelajaran" . PHP_EOL;
}

// 2. Alter tujuan_pembelajaran if needed
$tpCols = $db->query("SHOW COLUMNS FROM tujuan_pembelajaran")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('urutan', $tpCols)) {
    $db->exec("ALTER TABLE tujuan_pembelajaran ADD COLUMN urutan INT NOT NULL DEFAULT 1 AFTER deskripsi");
    echo "✓ Added column 'urutan' to tujuan_pembelajaran" . PHP_EOL;
}
if (!in_array('status', $tpCols)) {
    $db->exec("ALTER TABLE tujuan_pembelajaran ADD COLUMN status ENUM('aktif','nonaktif','arsip') NOT NULL DEFAULT 'aktif' AFTER urutan");
    echo "✓ Added column 'status' to tujuan_pembelajaran" . PHP_EOL;
}
if (!in_array('tahun_ajaran_id', $tpCols)) {
    $db->exec("ALTER TABLE tujuan_pembelajaran ADD COLUMN tahun_ajaran_id INT NULL AFTER status");
    echo "✓ Added column 'tahun_ajaran_id' to tujuan_pembelajaran" . PHP_EOL;
}

// 3. Create table kktp
$db->exec("
    CREATE TABLE IF NOT EXISTS `kktp` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `tp_id` INT NOT NULL,
        `metode` ENUM('skala_nilai','rubrik','checklist') NOT NULL DEFAULT 'skala_nilai',
        `nilai_minimum` DECIMAL(5,2) DEFAULT 75.00,
        `target_indikator_count` INT DEFAULT 1,
        `deskripsi_kriteria` TEXT NULL,
        `versi` INT DEFAULT 1,
        `status` ENUM('aktif','arsip') DEFAULT 'aktif',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY `idx_tp_kktp` (`tp_id`, `status`),
        CONSTRAINT `fk_kktp_tp` FOREIGN KEY (`tp_id`) REFERENCES `tujuan_pembelajaran`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "✓ Table 'kktp' created or verified." . PHP_EOL;

// 4. Create table kktp_indikator
$db->exec("
    CREATE TABLE IF NOT EXISTS `kktp_indikator` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `kktp_id` INT NOT NULL,
        `nama_indikator` VARCHAR(255) NOT NULL,
        `deskripsi_kriteria` TEXT NULL,
        `bobot` DECIMAL(5,2) DEFAULT 1.00,
        `urutan` INT DEFAULT 1,
        KEY `idx_kktp` (`kktp_id`),
        CONSTRAINT `fk_kktp_ind` FOREIGN KEY (`kktp_id`) REFERENCES `kktp`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "✓ Table 'kktp_indikator' created or verified." . PHP_EOL;

// 5. Create table asesmen_tp (Many-to-Many Asesmen to TP)
$db->exec("
    CREATE TABLE IF NOT EXISTS `asesmen_tp` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `asesmen_id` INT NOT NULL,
        `tp_id` INT NOT NULL,
        `kktp_id` INT NULL,
        `bobot_tp` DECIMAL(5,2) DEFAULT 1.00,
        `nilai_maksimum` DECIMAL(5,2) DEFAULT 100.00,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `u_asesmen_tp` (`asesmen_id`, `tp_id`),
        CONSTRAINT `fk_atp_asesmen` FOREIGN KEY (`asesmen_id`) REFERENCES `asesmen`(`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_atp_tp` FOREIGN KEY (`tp_id`) REFERENCES `tujuan_pembelajaran`(`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_atp_kktp` FOREIGN KEY (`kktp_id`) REFERENCES `kktp`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "✓ Table 'asesmen_tp' created or verified." . PHP_EOL;

// 6. Create table nilai_asesmen_tp
$db->exec("
    CREATE TABLE IF NOT EXISTS `nilai_asesmen_tp` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `asesmen_id` INT NOT NULL,
        `asesmen_tp_id` INT NOT NULL,
        `tp_id` INT NOT NULL,
        `siswa_id` INT NOT NULL,
        `kktp_id` INT NULL,
        `nilai_asli` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        `nilai_maksimum` DECIMAL(5,2) DEFAULT 100.00,
        `status_code` TINYINT(1) NOT NULL DEFAULT 0,
        `status_ketercapaian` VARCHAR(50) NOT NULL DEFAULT 'BELUM_TERCAPAI',
        `indikator_tercapai_ids` TEXT NULL,
        `is_remedial` TINYINT(1) DEFAULT 0,
        `nilai_awal` DECIMAL(5,2) NULL,
        `catatan` TEXT NULL,
        `evaluated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `u_asesmen_tp_siswa_rem` (`asesmen_tp_id`, `siswa_id`, `is_remedial`),
        KEY `idx_tp_siswa` (`tp_id`, `siswa_id`),
        CONSTRAINT `fk_natp_asesmen` FOREIGN KEY (`asesmen_id`) REFERENCES `asesmen`(`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_natp_atp` FOREIGN KEY (`asesmen_tp_id`) REFERENCES `asesmen_tp`(`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_natp_tp` FOREIGN KEY (`tp_id`) REFERENCES `tujuan_pembelajaran`(`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_natp_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_natp_kktp` FOREIGN KEY (`kktp_id`) REFERENCES `kktp`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "✓ Table 'nilai_asesmen_tp' created or verified." . PHP_EOL;

echo "Migration completed successfully!" . PHP_EOL;
