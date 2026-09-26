<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';

$db = Database::getConnection();

echo "=== START RESTORING DATA FROM AUTO BACKUP ===\n";

// 1. Restore User 7 to AGUNG RIFALDI (Siswa) first
$db->prepare("
    UPDATE users 
    SET role_id = 3,
        username = 'agung',
        email = 'agung.siswa@smkmuthia.sch.id',
        password = '$2y$10$MsZ8QwWQCprzC6XGSt65/./ai16Ok66tA24mFp//yV3F2rBy9vGCO',
        full_name = 'AGUNG RIFALDI',
        avatar = 'profile_1785912791_6a72ddd7ab8d6.png',
        status = 'active'
    WHERE id = 7
")->execute();
echo "✓ User ID 7 restored to 'AGUNG RIFALDI' (username: agung, role: Siswa).\n";

// 2. Restore Siswa ID 3 to AGUNG RIFALDI
$db->prepare("
    UPDATE siswa 
    SET user_id = 7,
        nis = '522402055',
        nisn = '522402055',
        nama_lengkap = 'AGUNG RIFALDI',
        kelas_id = 1,
        jurusan_id = 1,
        jenis_kelamin = 'L',
        no_telepon = '082317864874',
        no_ortu = '081234567890',
        alamat = 'Cicalengka, Bandung',
        status = 'aktif'
    WHERE id = 3
")->execute();
echo "✓ Siswa ID 3 restored to 'AGUNG RIFALDI' (NIS: 522402055, Kelas: X RPL 1).\n";

// 3. Preserve Ahmad Fauzi as a separate new user and siswa so his test data remains available
$checkAf = $db->query("SELECT id FROM users WHERE id = 14 OR username = 'ahmadfauzi_siswa'")->fetch();
if (!$checkAf) {
    $hashAf = password_hash('siswa123', PASSWORD_BCRYPT);
    $stmtAfU = $db->prepare("INSERT INTO users (id, role_id, username, email, password, full_name, avatar, status) VALUES (14, 3, 'ahmadfauzi_siswa', 'ahmadfauzi@smkmh-cicalengka.sch.id', ?, 'Ahmad Fauzi', 'default_avatar.png', 'active')");
    $stmtAfU->execute([$hashAf]);

    $stmtAfS = $db->prepare("INSERT INTO siswa (id, user_id, nis, nisn, nama_lengkap, kelas_id, jurusan_id, jenis_kelamin, no_telepon, no_ortu, alamat, status) VALUES (12, 14, '522402099', '522402099', 'Ahmad Fauzi', 1, 1, 'L', '082317864874', '081299998888', 'Cicalengka, Bandung', 'aktif')");
    $stmtAfS->execute();
    echo "✓ Preserved Ahmad Fauzi as separate Siswa record (Siswa ID 12, User ID 14).\n";
}

// 4. Restore Siswa ID 6 (Administrator Utama)
$checkSiswa6 = $db->query("SELECT id FROM siswa WHERE id = 6 OR (user_id = 1 AND nis = 'S2026097583')")->fetch();
if (!$checkSiswa6) {
    $db->prepare("
        INSERT INTO siswa (id, user_id, nis, nisn, nama_lengkap, kelas_id, jurusan_id, jenis_kelamin, no_telepon, no_ortu, alamat, status)
        VALUES (6, 1, 'S2026097583', 'S2026097583', 'Administrator Utama', 1, 1, 'L', '081234567899', '081234567890', 'Cicalengka, Bandung', 'aktif')
    ")->execute();
    echo "✓ Siswa ID 6 (Administrator Utama) inserted into table siswa.\n";
} else {
    $db->prepare("
        UPDATE siswa 
        SET user_id = 1,
            nis = 'S2026097583',
            nisn = 'S2026097583',
            nama_lengkap = 'Administrator Utama',
            kelas_id = 1,
            jurusan_id = 1,
            jenis_kelamin = 'L',
            status = 'aktif'
        WHERE id = 6 OR (user_id = 1 AND nis = 'S2026097583')
    ")->execute();
    echo "✓ Siswa ID 6 (Administrator Utama) updated.\n";
}

// 5. Restore Guru ID 11 (Administrator Utama)
$checkGuru11 = $db->query("SELECT id FROM guru WHERE id = 11 OR (user_id = 1 AND nip = 'G202608810')")->fetch();
if (!$checkGuru11) {
    $db->prepare("
        INSERT INTO guru (id, user_id, nip, nama_lengkap, jenis_kelamin, no_telepon, alamat, status)
        VALUES (11, 1, 'G202608810', 'Administrator Utama', 'L', '081234567899', 'SMK Muthia Harapan Cicalengka', 'aktif')
    ")->execute();
    echo "✓ Guru ID 11 (Administrator Utama) inserted into table guru.\n";
} else {
    $db->prepare("
        UPDATE guru 
        SET user_id = 1,
            nip = 'G202608810',
            nama_lengkap = 'Administrator Utama',
            jenis_kelamin = 'L',
            status = 'aktif'
        WHERE id = 11 OR (user_id = 1 AND nip = 'G202608810')
    ")->execute();
    echo "✓ Guru ID 11 (Administrator Utama) updated.\n";
}

echo "\n=== VERIFYING RESTORED DATA IN KEPSEK CONTROLLER QUERIES ===\n";

// Query Siswa in Kelas 1 (X RPL 1)
$siswaKelas1 = $db->query("
    SELECT s.id, s.nis, s.nama_lengkap, s.status, k.nama_kelas, u.username, u.role_id, r.name as role_name
    FROM siswa s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN roles r ON u.role_id = r.id
    LEFT JOIN kelas k ON s.kelas_id = k.id
    WHERE s.kelas_id = 1
    ORDER BY s.nama_lengkap ASC
")->fetchAll(PDO::FETCH_ASSOC);
echo "Siswa in Kelas X RPL 1:\n";
print_r($siswaKelas1);

// Query Guru
$gurus = $db->query("
    SELECT g.id, g.nip, g.nama_lengkap, g.status, u.username, r.name as role_name
    FROM guru g
    JOIN users u ON g.user_id = u.id
    LEFT JOIN roles r ON u.role_id = r.id
    ORDER BY g.nama_lengkap ASC
")->fetchAll(PDO::FETCH_ASSOC);
echo "\nAll Gurus in Database:\n";
print_r($gurus);
