<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';

try {
    $db = Database::getConnection();
    echo "=== SISWA COUNT ===\n";
    echo $db->query("SELECT COUNT(*) FROM siswa")->fetchColumn() . "\n";
    
    echo "=== SISWA SAMPLE ===\n";
    print_r($db->query("SELECT s.id, s.nis, s.nama_lengkap, s.kelas_id, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id LIMIT 10")->fetchAll(PDO::FETCH_ASSOC));

    echo "=== GURU SAMPLE ===\n";
    print_r($db->query("SELECT id, user_id, nip, nama_lengkap FROM guru LIMIT 10")->fetchAll(PDO::FETCH_ASSOC));

    echo "=== JADWAL SAMPLE ===\n";
    print_r($db->query("SELECT * FROM jadwal LIMIT 10")->fetchAll(PDO::FETCH_ASSOC));

    echo "=== SISWA_MAPEL_ENROLLMENT ===\n";
    try {
        print_r($db->query("SELECT * FROM siswa_mapel_enrollment LIMIT 10")->fetchAll(PDO::FETCH_ASSOC));
    } catch (Throwable $e) {
        echo "Error siswa_mapel_enrollment: " . $e->getMessage() . "\n";
    }

    echo "=== MAPEL_ENROLLMENT_KEYS ===\n";
    try {
        print_r($db->query("SELECT * FROM mapel_enrollment_keys LIMIT 10")->fetchAll(PDO::FETCH_ASSOC));
    } catch (Throwable $e) {
        echo "Error mapel_enrollment_keys: " . $e->getMessage() . "\n";
    }

    echo "=== ABSENSI COUNT & SAMPLE ===\n";
    echo "Count: " . $db->query("SELECT COUNT(*) FROM absensi")->fetchColumn() . "\n";
    print_r($db->query("SELECT * FROM absensi ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC));

} catch (Throwable $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
