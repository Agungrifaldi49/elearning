<?php
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/database.php';
$db = Database::getConnection();

echo "=== MIGRATION: SEED TBSM CLASSES & ROMBEL KURIKULUM ===\n\n";

// 1. Check or Get Jurusan TBSM
$stmtJ = $db->prepare("SELECT id FROM jurusan WHERE kode_jurusan = 'TBSM' OR nama_jurusan LIKE '%Sepeda Motor%' LIMIT 1");
$stmtJ->execute();
$tbsmId = (int)$stmtJ->fetchColumn();

if (!$tbsmId) {
    $db->prepare("INSERT INTO jurusan (kode_jurusan, nama_jurusan, deskripsi) VALUES ('TBSM', 'Teknik Bisnis Sepeda Motor', 'Teknik dan bisnis sepeda motor')")->execute();
    $tbsmId = (int)$db->lastInsertId();
    echo "Created Jurusan TBSM (ID: {$tbsmId})\n";
} else {
    echo "Found Jurusan TBSM (ID: {$tbsmId})\n";
}

// 2. Insert TBSM Classes if not exist
$classesToAdd = [
    ['nama_kelas' => 'X TBSM 1', 'tingkat' => 'X', 'fase_kode' => 'E', 'fase_id' => 1],
    ['nama_kelas' => 'XI TBSM 1', 'tingkat' => 'XI', 'fase_kode' => 'F', 'fase_id' => 2],
    ['nama_kelas' => 'XII TBSM 1', 'tingkat' => 'XII', 'fase_kode' => 'F', 'fase_id' => 2],
];

// Active TA
$stmtTa = $db->query("SELECT id FROM tahun_ajaran WHERE is_active = 1 LIMIT 1");
$activeTaId = (int)$stmtTa->fetchColumn() ?: 4;

// Default Kurikulum Merdeka ID
$stmtKur = $db->query("SELECT id FROM kurikulum WHERE status = 'aktif' ORDER BY id ASC LIMIT 1");
$kurId = (int)$stmtKur->fetchColumn() ?: 1;

foreach ($classesToAdd as $c) {
    $chkK = $db->prepare("SELECT id FROM kelas WHERE nama_kelas = ?");
    $chkK->execute([$c['nama_kelas']]);
    $kId = (int)$chkK->fetchColumn();

    if (!$kId) {
        $insK = $db->prepare("INSERT INTO kelas (nama_kelas, jurusan_id, tingkat) VALUES (?, ?, ?)");
        $insK->execute([$c['nama_kelas'], $tbsmId, $c['tingkat']]);
        $kId = (int)$db->lastInsertId();
        echo "+ Created Kelas: {$c['nama_kelas']} (ID: {$kId})\n";
    } else {
        // Ensure jurusan_id is set to TBSM
        $db->prepare("UPDATE kelas SET jurusan_id = ?, tingkat = ? WHERE id = ?")->execute([$tbsmId, $c['tingkat'], $kId]);
        echo "= Existing Kelas: {$c['nama_kelas']} (ID: {$kId})\n";
    }

    // Link to rombel_kurikulum
    $chkRk = $db->prepare("SELECT id FROM rombel_kurikulum WHERE rombel_id = ? AND tahun_ajaran_id = ?");
    $chkRk->execute([$kId, $activeTaId]);
    $rkId = (int)$chkRk->fetchColumn();

    if (!$rkId) {
        $insRk = $db->prepare("
            INSERT INTO rombel_kurikulum (rombel_id, tahun_ajaran_id, kurikulum_id, fase_id, status)
            VALUES (?, ?, ?, ?, 'aktif')
        ");
        $insRk->execute([$kId, $activeTaId, $kurId, $c['fase_id']]);
        echo "  + Linked to Kurikulum Merdeka (Fase {$c['fase_kode']})\n";
    } else {
        $db->prepare("UPDATE rombel_kurikulum SET fase_id = ?, status = 'aktif' WHERE id = ?")->execute([$c['fase_id'], $rkId]);
        echo "  = Updated Kurikulum Mapping (Fase {$c['fase_kode']})\n";
    }

    // Add sample student for this class if class is empty
    $stmtSiswaCount = $db->prepare("SELECT COUNT(*) FROM siswa WHERE kelas_id = ?");
    $stmtSiswaCount->execute([$kId]);
    $sc = (int)$stmtSiswaCount->fetchColumn();

    if ($sc == 0) {
        $namaSample = "Siswa " . str_replace(' ', '', $c['nama_kelas']) . " Pratama";
        $nisSample = "TBSM" . $kId . "01";
        
        // Check or create user
        $uChk = $db->prepare("SELECT id FROM users WHERE username = ?");
        $uChk->execute([$nisSample]);
        $uId = (int)$uChk->fetchColumn();
        if (!$uId) {
            $passHash = password_hash('siswa123', PASSWORD_BCRYPT);
            $db->prepare("INSERT INTO users (username, password, role_id, email) VALUES (?, ?, 3, ?)")->execute([$nisSample, $passHash, "{$nisSample}@sekolah.id"]);
            $uId = (int)$db->lastInsertId();
        }

        $insS = $db->prepare("
            INSERT INTO siswa (user_id, nis, nisn, nama_lengkap, kelas_id, jurusan_id, jenis_kelamin, status)
            VALUES (?, ?, ?, ?, ?, ?, 'L', 'aktif')
        ");
        $insS->execute([$uId, $nisSample, "00" . $kId . "12345", $namaSample, $kId, $tbsmId]);
        echo "  + Added sample student: {$namaSample} (NIS: {$nisSample})\n";
    }
}

echo "\nMigration finished successfully!\n";
