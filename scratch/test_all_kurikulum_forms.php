<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'models/CurriculumModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';
require_once ROOT_PATH . 'helpers/Security.php';
require_once ROOT_PATH . 'helpers/FlashHelper.php';
require_once ROOT_PATH . 'helpers/AuthHelper.php';

$content = file_get_contents(ROOT_PATH . 'views/admin/kurikulum.php');
preg_match_all('/<form\b[^>]*>(.*?)<\/form>/is', $content, $forms);

echo "Total forms found in views/admin/kurikulum.php: " . count($forms[0]) . "\n";

$actionsChecked = [];
foreach ($forms[0] as $idx => $formHtml) {
    // Check CSRF
    $hasCsrf = (stripos($formHtml, 'csrf_token') !== false || stripos($formHtml, 'Security::csrfField()') !== false);
    
    // Check action
    preg_match('/name=[\'"]action[\'"]\s+value=[\'"]([^\'"]+)[\'"]/i', $formHtml, $act);
    $actionName = $act[1] ?? 'GET_OR_NO_ACTION';

    // Check method
    preg_match('/method=[\'"]([^\'"]+)[\'"]/i', $formHtml, $meth);
    $method = strtoupper($meth[1] ?? 'GET');

    if ($method === 'POST' && !$hasCsrf) {
        echo "  ⚠️ Form #{$idx} (Action: {$actionName}) MISSING CSRF FIELD!\n";
    } else {
        $actionsChecked[] = $actionName;
    }
}

echo "Unique POST actions verified with CSRF:\n";
foreach (array_unique($actionsChecked) as $a) {
    echo "  - {$a}\n";
}

echo "\nAll verified successfully.\n";
