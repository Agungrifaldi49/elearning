<?php
$sql = file_get_contents(__DIR__ . '/../database/auto_backup_2026-09-19_10-34-35.sql');
preg_match_all('/CREATE TABLE `([^`]+)`/', $sql, $m);
echo "Tables in auto_backup_2026-09-19_10-34-35.sql (" . count($m[1]) . " tables):\n";
print_r($m[1]);

preg_match_all('/INSERT INTO `([^`]+)`/', $sql, $mIns);
$counts = array_count_values($mIns[1]);
echo "\nInsert counts per table:\n";
print_r($counts);
