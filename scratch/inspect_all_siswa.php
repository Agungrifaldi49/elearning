<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';
$db = Database::getConnection();

$siswas = $db->query("SELECT s.*, u.username, u.full_name as user_full_name, u.role_id, r.name as role_name FROM siswa s LEFT JOIN users u ON s.user_id = u.id LEFT JOIN roles r ON u.role_id = r.id")->fetchAll(PDO::FETCH_ASSOC);
echo "Current rows in siswa table:\n";
print_r($siswas);
