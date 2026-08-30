<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/Security.php';
require_once __DIR__ . '/../helpers/FlashHelper.php';
require_once __DIR__ . '/../controllers/ApiController.php';

$_GET['user_id'] = 1;
$_GET['action'] = 'siswa/learning_path';

$db = Database::getConnection();

echo "=== USER & SISWA DB DATA ===\n";
$stmtS = $db->query("SELECT s.*, u.full_name, u.role_id, k.nama_kelas, j.nama_jurusan FROM siswa s JOIN users u ON s.user_id = u.id LEFT JOIN kelas k ON s.kelas_id = k.id LEFT JOIN jurusan j ON s.jurusan_id = j.id LIMIT 5");
$siswaList = $stmtS->fetchAll();
print_r($siswaList);

echo "\n=== MATA PELAJARAN DB DATA ===\n";
$stmtM = $db->query("SELECT * FROM mata_pelajaran");
print_r($stmtM->fetchAll());

echo "\n=== SISWA MAPEL ENROLLMENT DB DATA ===\n";
$stmtE = $db->query("SELECT * FROM siswa_mapel_enrollment");
print_r($stmtE->fetchAll());

echo "\n=== API LEARNING PATH CONTROLLER OUTPUT ===\n";
ob_start();
$api = new ApiController();
$api->siswa('learning_path');
$output = ob_get_clean();

echo $output;
