<?php
$files = [
    'login.php',
    'config/app.php',
    'config/database.php',
    'controllers/AuthController.php',
    'helpers/AuthHelper.php',
    'helpers/Security.php',
    'helpers/CaptchaHelper.php',
    'helpers/FlashHelper.php',
    'models/UserModel.php'
];

foreach ($files as $f) {
    $content = file_get_contents(__DIR__ . '/../' . $f);
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        echo "BOM FOUND in $f!\n";
    }
    if (preg_match('/^\s+<\?php/', $content)) {
        echo "Leading whitespace before <?php in $f!\n";
    }
}
echo "BOM & whitespace check completed.\n";
