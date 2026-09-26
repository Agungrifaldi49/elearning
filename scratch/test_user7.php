<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/app.php';
require_once ROOT_PATH . 'models/UserModel.php';

$m = new UserModel();
print_r($m->findById(7));
