<?php
$lines = file(__DIR__ . '/../assets/css/style.css');
$balance = 0;
foreach ($lines as $num => $line) {
    $opens = substr_count($line, '{');
    $closes = substr_count($line, '}');
    $balance += ($opens - $closes);
    if ($balance < 0) {
        echo "Line " . ($num + 1) . " has extra close brace! Balance: $balance\n";
    }
}
echo "Final Balance at end of file: $balance\n";
