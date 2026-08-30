<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/LearningModel.php';

$lm = new LearningModel();
$materi = $lm->getMateri();
echo "--- ALL MATERI ---\n";
print_r($materi);

$db = Database::getConnection();
$stmt = $db->query("SELECT * FROM materi");
echo "\n--- RAW MATERI TABLE ---\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
