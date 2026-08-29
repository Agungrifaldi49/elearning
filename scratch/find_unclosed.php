<?php
$lines = file(__DIR__ . '/../assets/css/style.css');
$stack = [];
foreach ($lines as $num => $line) {
    $lineNum = $num + 1;
    $trim = trim($line);
    if (strpos($trim, '{') !== false) {
        $stack[] = ["line" => $lineNum, "text" => $trim];
    }
    if (strpos($trim, '}') !== false) {
        if (!empty($stack)) {
            array_pop($stack);
        } else {
            echo "Unexpected } on line $lineNum\n";
        }
    }
}

echo "Unclosed blocks remaining:\n";
foreach ($stack as $s) {
    echo "Line " . $s['line'] . ": " . $s['text'] . "\n";
}
