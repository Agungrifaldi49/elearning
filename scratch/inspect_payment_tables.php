<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();

echo "--- TABLE: siswa ---\n";
$stmt = $db->query("DESCRIBE siswa");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$r['Field']} | {$r['Type']} | Null: {$r['Null']} | Key: {$r['Key']}\n";
}

echo "\n--- SAMPLE SISWA DATA ---\n";
$stmt = $db->query("SELECT id, nis, nisn, nama_lengkap FROM siswa LIMIT 5");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']} | NIS: {$r['nis']} | NISN: {$r['nisn']} | Nama: {$r['nama_lengkap']}\n";
}

echo "\n--- TABLE: pembayaran_tagihan ---\n";
$stmt = $db->query("DESCRIBE pembayaran_tagihan");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$r['Field']} | {$r['Type']} | Null: {$r['Null']} | Key: {$r['Key']}\n";
}

echo "\n--- TABLE: pembayaran_riwayat ---\n";
$stmt = $db->query("DESCRIBE pembayaran_riwayat");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$r['Field']} | {$r['Type']} | Null: {$r['Null']} | Key: {$r['Key']}\n";
}
