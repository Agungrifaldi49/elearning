<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';
$db = Database::getConnection();

echo "=== KELAS & WALI KELAS ===\n";
$kelas = $db->query("SELECT k.id, k.nama_kelas, k.wali_kelas_id, g.nama_lengkap as nama_wali FROM kelas k LEFT JOIN guru g ON k.wali_kelas_id = g.id")->fetchAll(PDO::FETCH_ASSOC);
print_r($kelas);
