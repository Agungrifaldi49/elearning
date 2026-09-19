<?php
define('ROOT_PATH', dirname(__DIR__) . '/');
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'models/AcademicModel.php';
$academic = new AcademicModel();
print_r($academic->getActiveTahunAjaran());
