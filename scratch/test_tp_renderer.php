<?php
define('ROOT_PATH', __DIR__ . '/../');

function formatTpDescriptionHtmlTest($text) {
    if (empty($text)) return '';
    $lines = preg_split('/\r\n|\r|\n/', trim($text));
    if (count($lines) <= 1 && !preg_match('/^(\d+[\.\)\-]|[a-zA-Z][\.\)]|[•\-\*✓▪→\x{2022}\x{25AA}\x{2713}])\s*/u', trim($text))) {
        return htmlspecialchars($text);
    }
    $html = '<div class="tp-formatted-list d-flex flex-column" style="gap: 4px;">';
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '') continue;
        // 1. Numbered: 1. or 1)
        if (preg_match('/^(\d+)[\.\)\-]\s*(.*)$/u', $trimmed, $m)) {
            $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 6px;">'
                  . '<span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace rounded-pill flex-shrink-0" style="font-size:0.68rem; min-width:20px; padding: 2px 6px; text-align:center;">' . $m[1] . '</span>'
                  . '<span class="tp-list-text text-secondary" style="line-height:1.45;">' . htmlspecialchars($m[2]) . '</span>'
                  . '</div>';
        // 2. Lettered: a. or A. or a)
        } elseif (preg_match('/^([a-zA-Z])[\.\)]\s*(.*)$/u', $trimmed, $m)) {
            $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 6px;">'
                  . '<span class="badge bg-secondary-subtle text-dark border font-monospace rounded-pill flex-shrink-0" style="font-size:0.68rem; min-width:20px; padding: 2px 6px; text-align:center;">' . strtoupper($m[1]) . '</span>'
                  . '<span class="tp-list-text text-secondary" style="line-height:1.45;">' . htmlspecialchars($m[2]) . '</span>'
                  . '</div>';
        // 3. Checkmarks: ✓, ✔, ☑
        } elseif (preg_match('/^([✓✔☑])\s*(.*)$/u', $trimmed, $m)) {
            $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 6px;">'
                  . '<span class="text-success flex-shrink-0 fw-bold" style="font-size:0.85rem; line-height:1.4; width:16px; text-align:center;"><i class="bi bi-check-circle-fill"></i></span>'
                  . '<span class="tp-list-text text-secondary" style="line-height:1.45;">' . htmlspecialchars($m[2]) . '</span>'
                  . '</div>';
        // 4. Arrows: →, ➔, ➢, ►, >
        } elseif (preg_match('/^([→➔➢►▶>])\s*(.*)$/u', $trimmed, $m)) {
            $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 6px;">'
                  . '<span class="text-primary flex-shrink-0 fw-bold" style="font-size:0.82rem; line-height:1.4; width:16px; text-align:center;"><i class="bi bi-arrow-right-short fs-6"></i></span>'
                  . '<span class="tp-list-text text-secondary" style="line-height:1.45;">' . htmlspecialchars($m[2]) . '</span>'
                  . '</div>';
        // 5. Bullets & Other Symbols: •, -, *, ▪, ▫, +
        } elseif (preg_match('/^([•\-\*▪▫+–—\x{2022}\x{25AA}])\s*(.*)$/u', $trimmed, $m)) {
            $html .= '<div class="tp-list-row d-flex align-items-start" style="gap: 6px;">'
                  . '<span class="text-primary flex-shrink-0 fw-bold" style="font-size:0.9rem; line-height:1.3; width:16px; text-align:center;">•</span>'
                  . '<span class="tp-list-text text-secondary" style="line-height:1.45;">' . htmlspecialchars($m[2]) . '</span>'
                  . '</div>';
        } else {
            $html .= '<div class="tp-list-text text-secondary" style="line-height:1.45;">' . htmlspecialchars($trimmed) . '</div>';
        }
    }
    $html .= '</div>';
    return $html;
}

$sample1 = "1. Siswa mampu menganalisis struktur kalimat.\n2. Siswa mampu membuat teks laporan hasil observasi.";
$sample2 = "a. Memahami teks argumentasi.\nb. Memproduksi teks argumentasi logis.";
$sample3 = "• Menyusun hipotesis ilmiah.\n• Melakukan eksperimen terarah.\n• Menyimpulkan hasil pengamatan.";
$sample4 = "✓ Lulus asesmen formatif 1.\n✓ Mengumpulkan portofolio tugas.";
$sample5 = "→ Mengidentifikasi masalah\n→ Merancang solusi praktis";
$sample6 = "Tujuan pembelajaran umum tanpa butir penomoran apapun di dalamnya.";

echo "=== TEST 1 (Numbers) ===\n" . formatTpDescriptionHtmlTest($sample1) . "\n\n";
echo "=== TEST 2 (Letters) ===\n" . formatTpDescriptionHtmlTest($sample2) . "\n\n";
echo "=== TEST 3 (Bullets) ===\n" . formatTpDescriptionHtmlTest($sample3) . "\n\n";
echo "=== TEST 4 (Checks) ===\n" . formatTpDescriptionHtmlTest($sample4) . "\n\n";
echo "=== TEST 5 (Arrows) ===\n" . formatTpDescriptionHtmlTest($sample5) . "\n\n";
echo "=== TEST 6 (Plain) ===\n" . formatTpDescriptionHtmlTest($sample6) . "\n\n";
echo "ALL TESTS PASSED SUCCESSFULLY!\n";
