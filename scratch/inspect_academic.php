<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    echo "CONNECTED TO DATABASE: " . $db->query("SELECT DATABASE()")->fetchColumn() . "\n\n";

    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "=== ALL EXISTING TABLES (" . count($tables) . ") ===\n";
    foreach ($tables as $t) {
        $count = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "- $t ($count rows)\n";
    }

    echo "\n=== ACADEMIC & GRADING TABLES STRUCTURE ===\n";
    $targetTables = ['tahun_ajaran', 'semester', 'jurusan', 'kelas', 'mata_pelajaran', 'jadwal', 'materi', 'tugas', 'pengumpulan_tugas', 'quiz', 'hasil_quiz', 'ujian', 'hasil_ujian', 'nilai', 'nilai_rapor', 'absensi', 'mapel_enrollment_keys', 'siswa_mapel_enrollment', 'kurikulum', 'fase', 'capaian_pembelajaran', 'tujuan_pembelajaran'];

    foreach ($targetTables as $t) {
        if (!in_array($t, $tables)) {
            echo "\n[TABLE NOT FOUND: $t]\n";
            continue;
        }
        echo "\n--- TABLE: $t ---\n";
        $cols = $db->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo "  {$c['Field']} | {$c['Type']} | " . ($c['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . " | Key: {$c['Key']} | Def: " . var_export($c['Default'], true) . "\n";
        }
    }

    echo "\n=== FOREIGN KEYS IN DATABASE ===\n";
    $fks = $db->query("
        SELECT 
            TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            REFERENCED_TABLE_SCHEMA = (SELECT DATABASE())
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ORDER BY TABLE_NAME, COLUMN_NAME
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($fks as $fk) {
        echo "{$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']} ({$fk['CONSTRAINT_NAME']})\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
