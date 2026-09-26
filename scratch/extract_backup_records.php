<?php
$backupFile = __DIR__ . '/../database/auto_backup_2026-09-19_10-34-35.sql';
$lines = file($backupFile);

$keywords = [
    "'Administrator Utama'",
    "'AGUNG RIFALDI'",
    "'S2026097583'",
    "'522402055'",
    "'agung'"
];

$matched = [];
foreach ($lines as $idx => $line) {
    foreach ($keywords as $kw) {
        if (strpos($line, $kw) !== false) {
            $matched[] = "Line " . ($idx + 1) . ": " . trim($line);
            break;
        }
    }
}

echo "Found " . count($matched) . " occurrences in backup:\n";
foreach ($matched as $m) {
    echo $m . "\n";
}
