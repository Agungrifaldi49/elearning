<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=smkmuth3_db_elearning_smkmh;charset=utf8mb4", "root", "");
$users = $pdo->query("SELECT id, username, full_name, role_id FROM users")->fetchAll(PDO::FETCH_ASSOC);
echo "Users in smkmuth3_db_elearning_smkmh:\n";
print_r($users);
$gurus = $pdo->query("SELECT id, nama_lengkap, nip FROM guru")->fetchAll(PDO::FETCH_ASSOC);
echo "Gurus in smkmuth3_db_elearning_smkmh:\n";
print_r($gurus);
