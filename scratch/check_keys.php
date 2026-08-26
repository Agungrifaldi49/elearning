<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AcademicModel.php';

$model = new AcademicModel();
$keys = $model->getMapelEnrollmentKeys();

echo "TOTAL KEYS IN DB NOW: " . count($keys) . "\n\n";
foreach ($keys as $k) {
    echo "ID: {$k['id']} | Mapel: {$k['nama_mapel']} | Guru: {$k['nama_guru']} | Key: {$k['enrollment_key']}\n";
}
