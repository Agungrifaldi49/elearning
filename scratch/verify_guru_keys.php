<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AcademicModel.php';

$model = new AcademicModel();

$db = Database::getConnection();
$gurus = $db->query("SELECT id, nama_lengkap FROM guru")->fetchAll();

foreach ($gurus as $g) {
    $keys = $model->getMapelEnrollmentKeys($g['id']);
    $myMapels = $model->getMapelByGuru($g['id']);
    echo "=== GURU: {$g['nama_lengkap']} (Guru ID: {$g['id']}) ===\n";
    echo "Total Keys Owned: " . count($keys) . "\n";
    foreach ($keys as $k) {
        echo "  - Mapel: {$k['nama_mapel']} | Key: {$k['enrollment_key']}\n";
    }
    echo "Total Assigned Mapels for Dropdown: " . count($myMapels) . "\n";
    foreach ($myMapels as $m) {
        echo "  - Dropdown Option: {$m['nama_mapel']}\n";
    }
    echo "\n";
}
