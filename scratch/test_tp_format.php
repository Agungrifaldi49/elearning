<?php

function formatTpDescriptionHtml($text) {
    if (empty($text)) return '';
    $lines = preg_split('/\r\n|\r|\n/', trim($text));
    if (count($lines) <= 1 && !preg_match('/^(\d+[\.\)\-]|[a-zA-Z][\.\)]|[•\-\*✓▪])\s*/u', trim($text))) {
        return htmlspecialchars($text);
    }
    $html = '<div class="tp-formatted-list d-flex flex-column gap-1">';
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '') continue;
        if (preg_match('/^(\d+)[\.\)\-]\s*(.*)$/u', $trimmed, $m)) {
            $html .= '<div class="tp-list-row d-flex align-items-start gap-1.5"><span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-1.5 py-0.5 rounded-pill flex-shrink-0" style="font-size:0.68rem; min-width:20px; text-align:center;">' . $m[1] . '</span><span class="tp-list-text text-secondary" style="line-height:1.4;">' . htmlspecialchars($m[2]) . '</span></div>';
        } elseif (preg_match('/^([a-zA-Z])[\.\)]\s*(.*)$/u', $trimmed, $m)) {
            $html .= '<div class="tp-list-row d-flex align-items-start gap-1.5"><span class="badge bg-secondary-subtle text-dark border font-monospace px-1.5 py-0.5 rounded-pill flex-shrink-0" style="font-size:0.68rem; min-width:20px; text-align:center;">' . strtoupper($m[1]) . '</span><span class="tp-list-text text-secondary" style="line-height:1.4;">' . htmlspecialchars($m[2]) . '</span></div>';
        } elseif (preg_match('/^([•\-\*✓▪→\x{2022}\x{25AA}\x{2713}])\s*(.*)$/u', $trimmed, $m)) {
            $sym = ($m[1] === '-') ? '–' : $m[1];
            $html .= '<div class="tp-list-row d-flex align-items-start gap-1.5"><span class="text-primary flex-shrink-0 fw-bold" style="font-size:0.85rem; line-height:1.3; width:16px; text-align:center;">' . htmlspecialchars($sym) . '</span><span class="tp-list-text text-secondary" style="line-height:1.4;">' . htmlspecialchars($m[2]) . '</span></div>';
        } else {
            $html .= '<div class="tp-list-text text-secondary" style="line-height:1.4;">' . htmlspecialchars($trimmed) . '</div>';
        }
    }
    $html .= '</div>';
    return $html;
}

echo "--- TEST 1: Numbered ---\n" . formatTpDescriptionHtml("1. Test satu\n2. Test dua") . "\n";
echo "--- TEST 2: Lettered ---\n" . formatTpDescriptionHtml("a. Poin A\nb. Poin B") . "\n";
echo "--- TEST 3: Bulleted ---\n" . formatTpDescriptionHtml("• Bullet satu\n• Bullet dua") . "\n";
echo "--- TEST 4: Single Line ---\n" . formatTpDescriptionHtml("Peserta didik mampu memahami OOP.") . "\n";
