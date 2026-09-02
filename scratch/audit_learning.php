<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AcademicModel.php';

$db = Database::getConnection();

echo "=== USERS & ROLES ===\n";
$users = $db->query("SELECT u.id, u.username, u.full_name, u.role_id, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id")->fetchAll();
print_r($users);

echo "=== SISWA PROFILES ===\n";
$siswa = $db->query("SELECT s.*, k.nama_kelas, j.nama_jurusan FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id LEFT JOIN jurusan j ON s.jurusan_id = j.id")->fetchAll();
print_r($siswa);

echo "=== QUIZ LIST IN DB ===\n";
$quiz = $db->query("SELECT q.*, m.nama_mapel, k.nama_kelas FROM quiz q LEFT JOIN mata_pelajaran m ON q.mapel_id = m.id LEFT JOIN kelas k ON q.kelas_id = k.id")->fetchAll();
print_r($quiz);

echo "=== MATERI LIST IN DB ===\n";
$materi = $db->query("SELECT m.*, mp.nama_mapel, k.nama_kelas FROM materi m LEFT JOIN mata_pelajaran mp ON m.mapel_id = mp.id LEFT JOIN kelas k ON m.kelas_id = k.id")->fetchAll();
print_r($materi);

echo "=== TUGAS LIST IN DB ===\n";
$tugas = $db->query("SELECT t.*, mp.nama_mapel, k.nama_kelas FROM tugas t LEFT JOIN mata_pelajaran mp ON t.mapel_id = mp.id LEFT JOIN kelas k ON t.kelas_id = k.id")->fetchAll();
print_r($tugas);
