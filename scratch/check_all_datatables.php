<?php
$dir = 'views';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($files as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    $filePath = $file->getPathname();
    $content = file_get_contents($filePath);
    
    if (stripos($content, 'datatable') === false) continue;
    
    // Find all tables with class datatable
    preg_match_all('/<table[^>]*class=[\'"][^\'"]*datatable[^\'"]*[\'"][^>]*>(.*?)<\/table>/is', $content, $tables);
    foreach ($tables[0] as $tIndex => $tbl) {
        if (preg_match('/<tbody[^>]*>(.*?)<\/tbody>/is', $tbl, $tbMatch)) {
            $tbody = $tbMatch[1];
            if (stripos($tbody, 'colspan') !== false) {
                echo "File: {$filePath}\n";
                echo "  Table #{$tIndex} has COLSPAN in TBODY!\n";
                preg_match_all('/<tr[^>]*>.*?colspan.*?<\/tr>/is', $tbody, $rowMatches);
                foreach ($rowMatches[0] as $r) {
                    echo "    " . trim(strip_tags($r)) . "\n";
                }
                echo "\n";
            }
        }
    }
}
