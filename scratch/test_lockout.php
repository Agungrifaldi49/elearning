<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/app.php';
require_once ROOT_PATH . 'models/UserModel.php';

$u = new UserModel();
echo "Failed logins for admin in last 5 mins: " . $u->countFailedLogins('admin') . "\n";

$db = Database::getConnection();
$latest = $db->query("SELECT * FROM log_login ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
echo "Latest 5 logs in DB:\n";
print_r($latest);
