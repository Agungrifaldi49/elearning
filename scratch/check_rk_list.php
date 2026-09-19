<?php
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'models/CurriculumModel.php';
$currModel = new CurriculumModel();

echo "=== ROMBEL KURIKULUM LIST (TA: 4) ===\n";
$list = $currModel->getRombelKurikulum(4);
foreach ($list as $r) {
    echo "ID: {$r['id']} | Rombel: {$r['nama_kelas']} ({$r['nama_jurusan']}) | Tingkat: {$r['tingkat']} | Kurikulum: {$r['nama_kurikulum']} | Fase: {$r['nama_fase']} ({$r['kode_fase']})\n";
}
