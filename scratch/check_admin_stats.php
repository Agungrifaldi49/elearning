<?php
define('ROOT_PATH', 'c:/xampp/htdocs/Elearning_Mhc/');
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'models/ReportModel.php';

$db = Database::getConnection();

echo "nilai_rapor avg: " . var_export($db->query("SELECT AVG(nilai_akhir) FROM nilai_rapor")->fetchColumn(), true) . "\n";
echo "hasil_quiz avg: " . var_export($db->query("SELECT AVG(total_nilai) FROM hasil_quiz")->fetchColumn(), true) . "\n";
echo "absensi count: " . var_export($db->query("SELECT COUNT(*) FROM absensi")->fetchColumn(), true) . "\n";
echo "absensi sample: ";
print_r($db->query("SELECT * FROM absensi LIMIT 5")->fetchAll());
echo "jurusan stats:\n";
print_r($db->query("
    SELECT j.nama_jurusan, COUNT(s.id) as total_siswa
    FROM jurusan j
    LEFT JOIN siswa s ON s.jurusan_id = j.id
    GROUP BY j.id, j.nama_jurusan
    ORDER BY total_siswa DESC
")->fetchAll());
