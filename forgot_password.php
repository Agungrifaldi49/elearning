<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/controllers/AuthController.php';

$auth = new AuthController();
$auth->forgotPassword();
