<?php
/**
 * Migration: Tambahkan kolom jabatan di tabel guru pada semua database
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

    // Kumpulkan semua database yang memiliki tabel guru
    $allDbs = $db->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    $targetDbs = [];

    foreach ($allDbs as $d) {
        if (in_array($d, ['information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin'])) continue;
        try {
            $hasGuru = $db->query("SHOW TABLES FROM `{$d}` LIKE 'guru'")->fetch();
            if ($hasGuru) {
                $targetDbs[] = $d;
            }
        } catch (\Throwable $e) {}
    }

    if (empty($targetDbs)) {
        $targetDbs = [$currentDb];
    }

    foreach ($targetDbs as $tDb) {
        echo "--> Memeriksa database: `{$tDb}`...\n";

        // Tambah kolom jabatan di tabel guru
        $colCheck = $db->query("SHOW COLUMNS FROM `{$tDb}`.`guru` LIKE 'jabatan'")->fetch();
        if (!$colCheck) {
            try {
                $db->exec("ALTER TABLE `{$tDb}`.`guru` ADD COLUMN `jabatan` VARCHAR(50) NOT NULL DEFAULT 'Guru Pengajar' AFTER `nama_lengkap`");
            } catch (\Throwable $eAlter) {
                $db->exec("ALTER TABLE `{$tDb}`.`guru` ADD COLUMN `jabatan` VARCHAR(50) NOT NULL DEFAULT 'Guru Pengajar'");
            }
            echo "    ✓ Kolom 'jabatan' BERHASIL ditambahkan ke tabel `{$tDb}`.`guru`!\n";
        } else {
            echo "    ✓ Kolom 'jabatan' sudah ada di tabel `{$tDb}`.`guru`.\n";
        }
    }

    echo "\n==========================================\n";
    echo "MIGRASI JABATAN SELESAI DENGAN SUKSES!\n";
    echo "Semua database sudah memiliki kolom 'jabatan' di tabel guru.\n";
    echo "==========================================\n";

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
