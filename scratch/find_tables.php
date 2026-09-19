<?php
$content = file_get_contents('views/admin/kurikulum.php');

// Find all <table>...</table>
preg_match_all('/<table[^>]*>(.*?)<\/table>/is', $content, $matches, PREG_OFFSET_CAPTURE);

foreach ($matches[0] as $index => $tableMatch) {
    $tableHtml = $tableMatch[0];
    
    // Check table tag attributes
    preg_match('/<table([^>]*)>/i', $tableHtml, $tagMatch);
    $tableAttrs = $tagMatch[1] ?? '';
    
    // Count <th> in <thead>
    preg_match('/<thead[^>]*>(.*?)<\/thead>/is', $tableHtml, $theadMatch);
    $theadHtml = $theadMatch[1] ?? '';
    preg_match_all('/<th[^>]*>/i', $theadHtml, $thMatches);
    $thCount = count($thMatches[0]);
    
    // Check rows in tbody
    preg_match('/<tbody[^>]*>(.*?)<\/tbody>/is', $tableHtml, $tbodyMatch);
    $tbodyHtml = $tbodyMatch[1] ?? '';
    
    preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $tbodyHtml, $trMatches);
    
    echo "=== Table index {$index} (DataTables_Table_{$index}) ===\n";
    echo "Attrs: " . trim($tableAttrs) . "\n";
    echo "Thead TH count: {$thCount}\n";
    echo "Tbody TR count: " . count($trMatches[0]) . "\n";
    
    foreach ($trMatches[0] as $rIndex => $rowHtml) {
        if (stripos($rowHtml, 'colspan') !== false) {
            echo "  ⚠️ Row {$rIndex} has COLSPAN in TBODY: " . trim(strip_tags($rowHtml)) . "\n";
        }
    }
    echo "\n";
}
