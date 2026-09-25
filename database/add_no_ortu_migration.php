<?php
/**
 * Migration: Tambahkan kolom no_ortu di tabel siswa dan inisialisasi tabel wa_logs jika belum ada
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = Database::getConnection();
    $currentDb = $db->query("SELECT DATABASE()")->fetchColumn();
    echo "==========================================\n";
    echo "Database Aktif: {$currentDb}\n";
    echo "==========================================\n\n";

    // Kumpulkan semua database yang memiliki tabel siswa
    $allDbs = $db->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    $targetDbs = [];

    foreach ($allDbs as $d) {
        if (in_array($d, ['information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin'])) continue;
        try {
            $hasSiswa = $db->query("SHOW TABLES FROM `{$d}` LIKE 'siswa'")->fetch();
            if ($hasSiswa) {
                $targetDbs[] = $d;
            }
        } catch (\Throwable $e) {}
    }

    if (empty($targetDbs)) {
        $targetDbs = [$currentDb];
    }

    foreach ($targetDbs as $tDb) {
        echo "--> Memeriksa database: `{$tDb}`...\n";

        // 1. Tambah kolom no_ortu di tabel siswa
        $colCheck = $db->query("SHOW COLUMNS FROM `{$tDb}`.`siswa` LIKE 'no_ortu'")->fetch();
        if (!$colCheck) {
            try {
                $db->exec("ALTER TABLE `{$tDb}`.`siswa` ADD COLUMN `no_ortu` VARCHAR(25) NULL DEFAULT NULL AFTER `no_telepon`");
            } catch (\Throwable $eAlter) {
                // Jika no_telepon tidak ada, tambahkan di akhir
                $db->exec("ALTER TABLE `{$tDb}`.`siswa` ADD COLUMN `no_ortu` VARCHAR(25) NULL DEFAULT NULL");
            }
            echo "    ✓ Kolom 'no_ortu' BERHASIL ditambahkan ke tabel `{$tDb}`.`siswa`!\n";
        } else {
            echo "    ✓ Kolom 'no_ortu' sudah ada di tabel `{$tDb}`.`siswa`.\n";
        }

        // 2. Buat tabel wa_logs untuk history pengiriman & webhook
        $db->exec("
            CREATE TABLE IF NOT EXISTS `{$tDb}`.`wa_logs` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                siswa_id INT NULL,
                phone VARCHAR(30) NOT NULL,
                type VARCHAR(50) NOT NULL DEFAULT 'absensi',
                status VARCHAR(30) NOT NULL DEFAULT 'pending',
                message TEXT NOT NULL,
                response TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_phone (phone),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        echo "    ✓ Tabel 'wa_logs' siap digunakan di `{$tDb}`.\n\n";
    }

    echo "==========================================\n";
    echo "MIGRASI SELESAI DENGAN SUKSES!\n";
    echo "Semua database sudah memiliki kolom 'no_ortu' dan tabel 'wa_logs'.\n";
    echo "==========================================\n";

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

