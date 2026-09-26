<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';

$db = Database::getConnection();

echo "=== CHECK USERS TABLE ===\n";
$users = $db->query("SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.username LIKE '%ag%' OR u.full_name LIKE '%Agung%'")->fetchAll(PDO::FETCH_ASSOC);
print_r($users);

echo "\n=== CHECK GURU TABLE ===\n";
$gurus = $db->query("SELECT g.*, u.username, u.email, u.status as user_status FROM guru g LEFT JOIN users u ON g.user_id = u.id WHERE g.nama_lengkap LIKE '%Agung%' OR u.username LIKE '%ag%'")->fetchAll(PDO::FETCH_ASSOC);
print_r($gurus);

echo "\n=== CHECK SISWA TABLE ===\n";
$siswas = $db->query("SELECT s.*, u.username, u.email FROM siswa s LEFT JOIN users u ON s.user_id = u.id WHERE s.nama_lengkap LIKE '%Agung%' OR u.username LIKE '%ag%'")->fetchAll(PDO::FETCH_ASSOC);
print_r($siswas);

echo "\n=== CHECK ALL GURUS ===\n";
$allGurus = $db->query("SELECT g.id, g.user_id, g.nip, g.nama_lengkap, g.status, u.username, u.role_id, r.name as role_name FROM guru g LEFT JOIN users u ON g.user_id = u.id LEFT JOIN roles r ON u.role_id = r.id")->fetchAll(PDO::FETCH_ASSOC);
print_r($allGurus);
