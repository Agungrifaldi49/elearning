<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/CurriculumModel.php';

$currModel = new CurriculumModel();
$db = Database::getConnection();

$sId = (int)$db->query("SELECT DISTINCT siswa_id FROM nilai_rapor LIMIT 1")->fetchColumn();
$taId = (int)$db->query("SELECT id FROM tahun_ajaran WHERE is_active = 1 LIMIT 1")->fetchColumn();

echo "Testing Siswa ID: $sId, TA ID: $taId\n";
$currModel->generateOrSyncRaporSiswa($sId, $taId, 'Ganjil');
$rapor = $currModel->getRaporSiswa($sId, $taId, 'Ganjil');

echo "Snapshot: " . $rapor['kurikulum_nama_snapshot'] . "\n";
echo "Total Mapel: " . count($rapor['nilai_list']) . "\n";
print_r($rapor['nilai_list']);
