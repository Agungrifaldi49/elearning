<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';

// Generate valid bcrypt hashes
$hashAdmin = password_hash('admin123', PASSWORD_BCRYPT);
$hashGuru = password_hash('guru123', PASSWORD_BCRYPT);
$hashSiswa = password_hash('siswa123', PASSWORD_BCRYPT);
$hashKepsek = password_hash('kepsek123', PASSWORD_BCRYPT);

echo "Generating valid hashes:\n";
echo "Admin ('admin123'): $hashAdmin\n";
echo "Guru ('guru123'): $hashGuru\n";
echo "Siswa ('siswa123'): $hashSiswa\n";
echo "Kepsek ('kepsek123'): $hashKepsek\n";

$backupFile = ROOT_PATH . 'database/auto_backup_2026-09-19_10-34-35.sql';
$content = file_get_contents($backupFile);

// Replace passwords in the backup content with valid bcrypt hashes
// 1. admin
$content = preg_replace(
    "/INSERT INTO `users` VALUES\('1', '1', 'admin', 'admin@smkmh-cicalengka\.sch\.id', '[^']+',/",
    "INSERT INTO `users` VALUES('1', '1', 'admin', 'admin@smkmh-cicalengka.sch.id', '{$hashAdmin}',",
    $content
);
// 2. guru
$content = preg_replace(
    "/INSERT INTO `users` VALUES\('2', '2', 'guru', 'guru@smkmh-cicalengka\.sch\.id', '[^']+',/",
    "INSERT INTO `users` VALUES('2', '2', 'guru', 'guru@smkmh-cicalengka.sch.id', '{$hashGuru}',",
    $content
);
// 3. kepsek
$content = preg_replace(
    "/INSERT INTO `users` VALUES\('4', '4', 'kepsek', 'kepsek@smkmh-cicalengka\.sch\.id', '[^']+',/",
    "INSERT INTO `users` VALUES('4', '4', 'kepsek', 'kepsek@smkmh-cicalengka.sch.id', '{$hashKepsek}',",
    $content
);
// 4. guru2
$content = preg_replace(
    "/INSERT INTO `users` VALUES\('5', '2', 'guru2', 'budi@smkmh-cicalengka\.sch\.id', '[^']+',/",
    "INSERT INTO `users` VALUES('5', '2', 'guru2', 'budi@smkmh-cicalengka.sch.id', '{$hashGuru}',",
    $content
);
// 5. agung (siswa)
$content = preg_replace(
    "/INSERT INTO `users` VALUES\('7', '3', 'agung', 'agung\.siswa@smkmuthia\.sch\.id', '[^']+',/",
    "INSERT INTO `users` VALUES('7', '3', 'agung', 'agung.siswa@smkmuthia.sch.id', '{$hashSiswa}',",
    $content
);
// 6. agg023 (guru Agung)
$content = preg_replace(
    "/INSERT INTO `users` VALUES\('9', '2', 'agg023', 'agg023@smkmh-cicalengka\.sch\.id', '[^']+',/",
    "INSERT INTO `users` VALUES('9', '2', 'agg023', 'agg023@smkmh-cicalengka.sch.id', '{$hashGuru}',",
    $content
);

// Append TBSM users and siswa if not already in the backup
$extraInserts = "
-- Extra TBSM Class & Student Seeds
INSERT IGNORE INTO `users` (`id`, `role_id`, `username`, `email`, `password`, `full_name`, `avatar`, `status`) VALUES
('11', '3', 'TBSM901', 'TBSM901@sekolah.id', '{$hashSiswa}', 'Siswa XTBSM1 Pratama', 'default_avatar.png', 'active'),
('12', '3', 'TBSM1001', 'TBSM1001@sekolah.id', '{$hashSiswa}', 'Siswa XITBSM1 Pratama', 'default_avatar.png', 'active'),
('13', '3', 'TBSM1101', 'TBSM1101@sekolah.id', '{$hashSiswa}', 'Siswa XIITBSM1 Pratama', 'default_avatar.png', 'active');

INSERT IGNORE INTO `kelas` (`id`, `tingkat`, `nama_kelas`, `jurusan_id`, `wali_kelas_id`) VALUES
(9, 'X', 'X TBSM 1', 4, NULL),
(10, 'XI', 'XI TBSM 1', 4, NULL),
(11, 'XII', 'XII TBSM 1', 4, NULL);

INSERT IGNORE INTO `siswa` (`id`, `user_id`, `nis`, `nisn`, `nama_lengkap`, `kelas_id`, `jurusan_id`, `jenis_kelamin`, `status`) VALUES
('8', '11', 'TBSM901', '00912345', 'Siswa XTBSM1 Pratama', '9', '4', 'L', 'aktif'),
('9', '12', 'TBSM1001', '001012345', 'Siswa XITBSM1 Pratama', '10', '4', 'L', 'aktif'),
('10', '13', 'TBSM1101', '001112345', 'Siswa XIITBSM1 Pratama', '11', '4', 'L', 'aktif');
";

$content = str_replace("SET FOREIGN_KEY_CHECKS = 1;", $extraInserts . "\nSET FOREIGN_KEY_CHECKS = 1;", $content);

$exportPath = ROOT_PATH . 'database/RESTORE_LENGKAP_CPANEL.sql';
file_put_contents($exportPath, $content);
echo "✓ File created: database/RESTORE_LENGKAP_CPANEL.sql (" . strlen($content) . " bytes)\n";

// Now import into both local databases
$dbs = ['db_elearning_smkmh', 'smkmuth3_db_elearning_smkmh'];
foreach ($dbs as $dbName) {
    echo "\n--> Importing into database `{$dbName}`...\n";
    $pdo = new PDO("mysql:host=127.0.0.1;charset=utf8mb4", "root", "");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `{$dbName}`;");
    
    // Execute SQL script
    $pdo->exec($content);
    echo "✓ Imported successfully into `{$dbName}`!\n";

    // Verify
    $uCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $gCount = $pdo->query("SELECT COUNT(*) FROM guru")->fetchColumn();
    $sCount = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
    echo "   Users: {$uCount} | Gurus: {$gCount} | Siswas: {$sCount}\n";
}
