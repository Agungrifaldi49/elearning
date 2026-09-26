<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/app.php';
require_once ROOT_PATH . 'models/UserModel.php';

$m = new UserModel();
$users = ['admin', 'agung', 'agg023', 'guru', 'guru2', 'siswa', 'kepsek'];
foreach ($users as $u) {
    $row = $m->findByUsername($u);
    if ($row) {
        echo "Username '$u' -> ID: {$row['id']}, Name: {$row['full_name']}, Role: {$row['role_name']}\n";
    } else {
        echo "Username '$u' -> NOT FOUND\n";
    }
}
