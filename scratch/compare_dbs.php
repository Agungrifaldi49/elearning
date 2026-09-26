<?php
$pdo = new PDO("mysql:host=127.0.0.1;charset=utf8mb4", "root", "");
$dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
echo "All databases on MySQL:\n";
print_r($dbs);

foreach (['db_elearning_smkmh', 'smkmuth3_db_elearning_smkmh'] as $dbName) {
    if (in_array($dbName, $dbs)) {
        echo "\n=== Database: $dbName ===\n";
        $pdoDb = new PDO("mysql:host=127.0.0.1;dbname=$dbName;charset=utf8mb4", "root", "");
        
        $users = $pdoDb->query("SELECT id, username, full_name, role_id, status FROM users WHERE full_name LIKE '%Agung%' OR username LIKE '%ag%'")->fetchAll(PDO::FETCH_ASSOC);
        echo "Users:\n";
        print_r($users);

        $gurus = $pdoDb->query("SELECT id, user_id, nip, nama_lengkap, status FROM guru WHERE nama_lengkap LIKE '%Agung%'")->fetchAll(PDO::FETCH_ASSOC);
        echo "Gurus:\n";
        print_r($gurus);

        $siswas = $pdoDb->query("SELECT id, user_id, nis, nama_lengkap, status FROM siswa WHERE nama_lengkap LIKE '%Agung%'")->fetchAll(PDO::FETCH_ASSOC);
        echo "Siswas:\n";
        print_r($siswas);
    }
}
