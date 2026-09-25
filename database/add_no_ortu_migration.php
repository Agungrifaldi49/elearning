<?php
/**
 * Migration: Tambahkan kolom no_ortu di tabel siswa dan inisialisasi tabel wa_logs jika belum ada
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    echo "Mengecek kolom tabel siswa...\n";

    $stmt = $db->query("SHOW COLUMNS FROM siswa LIKE 'no_ortu'");
    $exists = $stmt->fetch();

    if (!$exists) {
        $db->exec("ALTER TABLE siswa ADD COLUMN no_ortu VARCHAR(25) NULL DEFAULT NULL AFTER no_telepon");
        echo "✓ Kolom 'no_ortu' berhasil ditambahkan ke tabel 'siswa'!\n";
    } else {
        echo "✓ Kolom 'no_ortu' sudah ada di tabel 'siswa'.\n";
    }

    // Buat tabel wa_logs untuk mencatat history pengiriman & webhook
    $db->exec("
        CREATE TABLE IF NOT EXISTS wa_logs (
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
    echo "✓ Tabel 'wa_logs' siap digunakan!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
