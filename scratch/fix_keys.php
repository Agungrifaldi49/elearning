<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AcademicModel.php';

$db = Database::getConnection();

// Delete keys that do not belong to assigned subjects in jadwal, materi, tugas, or quiz
$db->exec("
    DELETE FROM mapel_enrollment_keys 
    WHERE (mapel_id, guru_id) NOT IN (
        SELECT mapel_id, guru_id FROM (
            SELECT mapel_id, guru_id FROM jadwal WHERE mapel_id IS NOT NULL AND guru_id IS NOT NULL
            UNION
            SELECT mapel_id, guru_id FROM materi WHERE mapel_id IS NOT NULL AND guru_id IS NOT NULL
            UNION
            SELECT mapel_id, guru_id FROM tugas WHERE mapel_id IS NOT NULL AND guru_id IS NOT NULL
            UNION
            SELECT mapel_id, guru_id FROM quiz WHERE mapel_id IS NOT NULL AND guru_id IS NOT NULL
        ) t
    )
");

$model = new AcademicModel();
$keys = $model->getMapelEnrollmentKeys();

echo "REMAINING VALID KEYS IN DB: " . count($keys) . "\n\n";
foreach ($keys as $k) {
    echo "ID: {$k['id']} | Mapel: {$k['nama_mapel']} | Guru: {$k['nama_guru']} | Key: {$k['enrollment_key']}\n";
}
