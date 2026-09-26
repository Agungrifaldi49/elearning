<?php
foreach (['db_elearning_smkmh', 'smkmuth3_db_elearning_smkmh'] as $dbName) {
    echo "==========================================\n";
    echo "DATABASE: $dbName\n";
    echo "==========================================\n";
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=$dbName;charset=utf8mb4", "root", "");
    
    $users = $pdo->query("SELECT u.id, u.username, u.full_name, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.id ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) {
        echo "ID: {$u['id']} | User: {$u['username']} | Name: {$u['full_name']} | Role: {$u['role_name']}\n";
    }

    $gurus = $pdo->query("SELECT g.id, g.nip, g.nama_lengkap, u.username FROM guru g LEFT JOIN users u ON g.user_id = u.id ORDER BY g.id ASC")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nGURUS:\n";
    foreach ($gurus as $g) {
        echo "ID: {$g['id']} | NIP: {$g['nip']} | Name: {$g['nama_lengkap']} | User: {$g['username']}\n";
    }

    $siswas = $pdo->query("SELECT s.id, s.nis, s.nama_lengkap, k.nama_kelas, u.username FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id LEFT JOIN users u ON s.user_id = u.id ORDER BY s.id ASC")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nSISWAS:\n";
    foreach ($siswas as $s) {
        echo "ID: {$s['id']} | NIS: {$s['nis']} | Name: {$s['nama_lengkap']} | Kelas: {$s['nama_kelas']} | User: {$s['username']}\n";
    }
    echo "\n";
}
