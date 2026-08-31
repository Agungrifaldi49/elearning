<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';

$db = Database::getConnection();

echo "=== ROLES TABLE ===\n";
$roles = $db->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
print_r($roles);

echo "\n=== USERS TABLE ===\n";
$users = $db->query("SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id")->fetchAll(PDO::FETCH_ASSOC);
print_r($users);

echo "\n=== GURU TABLE ===\n";
$gurus = $db->query("SELECT g.*, u.username, u.role_id FROM guru g LEFT JOIN users u ON g.user_id = u.id")->fetchAll(PDO::FETCH_ASSOC);
print_r($gurus);

echo "\n=== SISWA TABLE ===\n";
$siswas = $db->query("SELECT s.*, u.username, u.role_id FROM siswa s LEFT JOIN users u ON s.user_id = u.id")->fetchAll(PDO::FETCH_ASSOC);
print_r($siswas);
