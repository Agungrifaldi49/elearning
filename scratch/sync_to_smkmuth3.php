<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    // 1. Create database smkmuth3_db_elearning_smkmh locally if not exists
    $db->exec("CREATE DATABASE IF NOT EXISTS `smkmuth3_db_elearning_smkmh` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "✓ Database smkmuth3_db_elearning_smkmh created / verified.\n";

    // 2. Connect to smkmuth3_db_elearning_smkmh
    $pdoRemote = new PDO("mysql:host=127.0.0.1;dbname=smkmuth3_db_elearning_smkmh;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Copy schema from db_elearning_smkmh if empty
    $tables = $pdoRemote->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in smkmuth3_db_elearning_smkmh: " . count($tables) . "\n";

    if (count($tables) == 0) {
        echo "Copying schema and tables to smkmuth3_db_elearning_smkmh...\n";
        $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
        $pdoRemote->exec($schema);

        $seed = file_get_contents(__DIR__ . '/../database/seeders.sql');
        $pdoRemote->exec($seed);
        echo "✓ Base schema and seeders imported to smkmuth3_db_elearning_smkmh.\n";
    }

    // Run curriculum migration
    $currSql = file_get_contents(__DIR__ . '/../database/migration_curriculum_system.sql');
    $pdoRemote->exec($currSql);
    echo "✓ Curriculum migration executed on smkmuth3_db_elearning_smkmh.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
