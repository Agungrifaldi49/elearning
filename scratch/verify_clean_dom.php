<?php
$content = file_get_contents(__DIR__ . '/../views/admin/kurikulum.php');
preg_match_all('/<tbody[^>]*>(.*?)<\/tbody>/is', $content, $m);

foreach ($m[1] as $idx => $tb) {
    preg_match_all('/<div[^>]*class=[\'"][^\'"]*modal[^\'"]*[\'"][^>]*>/i', $tb, $divModals);
    if (!empty($divModals[0])) {
        echo "FAIL: Tbody #{$idx} contains modal <div> tags: " . implode(', ', $divModals[0]) . "\n";
    } else {
        echo "PASS: Tbody #{$idx} has NO modal <div> tags.\n";
    }
}
