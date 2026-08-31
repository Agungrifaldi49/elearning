<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';

$db = Database::getConnection();

echo "=== CLEANING & RESTORING DATABASE DISCREPANCIES ===\n";

// 1. Remove non-siswa users from `siswa` table
$stmtDelSiswa = $db->query("
    DELETE s FROM siswa s 
    JOIN users u ON s.user_id = u.id 
    WHERE u.role_id != 3
");
echo "Cleaned bogus siswa rows for non-siswa users.\n";

// 2. Remove non-guru users from `guru` table
$stmtDelGuru = $db->query("
    DELETE g FROM guru g 
    JOIN users u ON g.user_id = u.id 
    WHERE u.role_id != 2
");
echo "Cleaned bogus guru rows for non-guru users.\n";

// 3. Check all users with role_id = 2 (Guru) and ensure proper guru profile exists
$guruUsers = $db->query("SELECT * FROM users WHERE role_id = 2")->fetchAll(PDO::FETCH_ASSOC);
foreach ($guruUsers as $u) {
    $check = $db->prepare("SELECT id FROM guru WHERE user_id = ?");
    $check->execute([$u['id']]);
    if (!$check->fetch()) {
        $nip = 'G' . date('Ym') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $ins = $db->prepare("INSERT INTO guru (user_id, nip, nama_lengkap, jenis_kelamin, status) VALUES (?, ?, ?, 'L', 'aktif')");
        $ins->execute([$u['id'], $nip, $u['full_name']]);
        echo "Restored missing guru profile for user #{$u['id']} ({$u['full_name']}).\n";
    }
}

// 4. Check all users with role_id = 3 (Siswa) and ensure proper siswa profile exists
$siswaUsers = $db->query("SELECT * FROM users WHERE role_id = 3")->fetchAll(PDO::FETCH_ASSOC);
foreach ($siswaUsers as $u) {
    $check = $db->prepare("SELECT id FROM siswa WHERE user_id = ?");
    $check->execute([$u['id']]);
    if (!$check->fetch()) {
        $nis = 'S' . date('Ym') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $ins = $db->prepare("INSERT INTO siswa (user_id, nis, nisn, nama_lengkap, kelas_id, jurusan_id, jenis_kelamin) VALUES (?, ?, ?, ?, 1, 1, 'L')");
        $ins->execute([$u['id'], $nis, $nis, $u['full_name']]);
        echo "Restored missing siswa profile for user #{$u['id']} ({$u['full_name']}).\n";
    }
}

echo "\nDB RESTORATION COMPLETE!\n";
