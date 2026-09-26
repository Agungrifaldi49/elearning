<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'models/GuruModel.php';

$guruModel = new GuruModel();
$list = $guruModel->getAll();
echo "Total Guru returned by GuruModel::getAll(): " . count($list) . "\n";
print_r($list);
