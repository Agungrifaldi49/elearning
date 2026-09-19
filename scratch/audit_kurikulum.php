<?php
$content = file_get_contents('views/admin/kurikulum.php');

// 1. Check modals inside tbody
preg_match_all('/<tbody>(.*?)<\/tbody>/is', $content, $matches);
foreach ($matches[1] as $i => $tb) {
    if (stripos($tb, 'class="modal') !== false || stripos($tb, "class='modal") !== false) {
        echo "⚠️ Table {$i} has MODALS inside <tbody>!\n";
    }
}

// 2. Check all forms in kurikulum.php
preg_match_all('/<form([^>]*)>(.*?)<\/form>/is', $content, $formMatches);
echo "Total forms in kurikulum.php: " . count($formMatches[0]) . "\n";

foreach ($formMatches[0] as $fIndex => $formHtml) {
    preg_match('/action=[\'"]([^\'"]*)[\'"]/i', $formHtml, $actionAttr);
    preg_match('/name=[\'"]action[\'"][^>]*value=[\'"]([^\'"]*)[\'"]/i', $formHtml, $valAction);
    preg_match('/name=[\'"]redirect_tab[\'"][^>]*value=[\'"]([^\'"]*)[\'"]/i', $formHtml, $redirectTab);
    
    $act = $valAction[1] ?? 'NO_ACTION_INPUT';
    $tab = $redirectTab[1] ?? 'NO_TAB_INPUT';
    $hasCsrf = stripos($formHtml, 'csrf') !== false;
    
    echo "Form #{$fIndex}: action='{$act}', redirect_tab='{$tab}', CSRF: " . ($hasCsrf ? 'OK' : 'MISSING!') . "\n";
}
