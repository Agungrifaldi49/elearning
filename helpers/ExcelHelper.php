<?php
/**
 * ExcelHelper
 * Helper untuk membaca (parse) dan membuat (export/download) berkas spreadsheet
 * Mendukung format:
 * - XLSX (Office Open XML - format resmi Microsoft Excel modern)
 * - CSV (Comma / Semicolon / Tab / Pipe Delimited dengan pendeteksi otomatis & UTF-8 BOM)
 */

class ExcelHelper {

    /**
     * Parse berkas spreadsheet (otomatis deteksi format XLSX atau CSV/TXT)
     * @param string $filePath Path fisik berkas
     * @param string $originalName Nama berkas asli untuk deteksi ekstensi (opsional)
     * @return array Array 2 dimensi baris dan kolom: [ [cellA1, cellB1, ...], ... ]
     */
    public static function parse($filePath, $originalName = '') {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [];
        }

        $ext = strtolower(pathinfo($originalName ?: $filePath, PATHINFO_EXTENSION));

        // Periksa apakah berkas adalah ZIP/XLSX berdasarkan magic bytes (PK\x03\x04)
        $handle = fopen($filePath, 'rb');
        $magic = $handle ? fread($handle, 4) : '';
        if ($handle) fclose($handle);

        $isZip = ($magic === "PK\x03\x04" || $magic === "PK\x05\x06" || $magic === "PK\x07\x08");

        if ($isZip || in_array($ext, ['xlsx', 'xlsm', 'xltx'])) {
            $rows = self::parseXlsx($filePath);
            if (!empty($rows)) {
                return $rows;
            }
        }

        // Coba sebagai format XML Spreadsheet 2003 jika diawali <?xml
        if ($ext === 'xls' || $ext === 'xml') {
            $xmlRows = self::parseXmlSpreadsheet($filePath);
            if (!empty($xmlRows)) {
                return $xmlRows;
            }
        }

        // Fallback ke CSV / Delimited text parser
        return self::parseCsv($filePath);
    }

    /**
     * Parse berkas Microsoft Excel XLSX (.xlsx)
     */
    public static function parseXlsx($filePath) {
        if (!class_exists('ZipArchive')) {
            return [];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Baca shared strings (jika ada)
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml !== false) {
            $xmlSS = @simplexml_load_string($ssXml);
            if ($xmlSS && isset($xmlSS->si)) {
                foreach ($xmlSS->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string)($r->t ?? '');
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Tentukan berkas worksheet pertama
        $sheetFile = 'xl/worksheets/sheet1.xml';
        if ($zip->locateName($sheetFile) === false) {
            // Cek workbook.xml.rels untuk worksheet target
            $wbRels = $zip->getFromName('xl/_rels/workbook.xml.rels');
            if ($wbRels !== false) {
                $xmlRels = @simplexml_load_string($wbRels);
                if ($xmlRels) {
                    foreach ($xmlRels->Relationship as $rel) {
                        $type = (string)$rel['Type'];
                        if (strpos($type, 'worksheet') !== false) {
                            $target = (string)$rel['Target'];
                            $sheetFile = 'xl/' . ltrim($target, '/');
                            break;
                        }
                    }
                }
            }
        }

        $sheetXml = $zip->getFromName($sheetFile);
        if ($sheetXml === false) {
            $zip->close();
            return [];
        }

        $xmlSheet = @simplexml_load_string($sheetXml);
        if (!$xmlSheet || !isset($xmlSheet->sheetData)) {
            $zip->close();
            return [];
        }

        $rows = [];
        foreach ($xmlSheet->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $c) {
                $cellRef = (string)$c['r'];
                preg_match('/^([A-Z]+)(\d+)$/', $cellRef, $matches);
                $colLetter = $matches[1] ?? 'A';
                $colIdx = self::colLetterToNumber($colLetter);

                $type = (string)$c['t'];
                $val = '';

                if ($type === 's') {
                    // Shared String
                    $sIdx = (int)$c->v;
                    $val = $sharedStrings[$sIdx] ?? '';
                } elseif ($type === 'inlineStr') {
                    $val = (string)($c->is->t ?? '');
                } elseif ($type === 'b') {
                    $val = ((string)$c->v === '1') ? '1' : '0';
                } elseif ($type === 'e') {
                    $val = '';
                } else {
                    // Number, string langsung, atau formula cached value
                    $val = (string)($c->v ?? '');
                }

                $rowData[$colIdx] = trim($val);
            }

            if (!empty($rowData)) {
                // Pastikan indeks kolom padat dari 0 hingga max index
                $maxIdx = max(array_keys($rowData));
                $denseRow = [];
                for ($i = 0; $i <= $maxIdx; $i++) {
                    $denseRow[$i] = $rowData[$i] ?? '';
                }

                // Jangan simpan baris yang benar-benar kosong semua
                $hasContent = false;
                foreach ($denseRow as $cell) {
                    if ($cell !== '') {
                        $hasContent = true;
                        break;
                    }
                }

                if ($hasContent) {
                    $rows[] = $denseRow;
                }
            }
        }

        $zip->close();
        return $rows;
    }

    /**
     * Konversi kode huruf kolom Excel (A, B, ..., Z, AA, AB) ke indeks integer 0-based
     */
    private static function colLetterToNumber($colStr) {
        $colStr = strtoupper($colStr);
        $len = strlen($colStr);
        $idx = 0;
        for ($i = 0; $i < $len; $i++) {
            $idx = $idx * 26 + (ord($colStr[$i]) - ord('A') + 1);
        }
        return $idx - 1;
    }

    /**
     * Konversi angka 0-based ke huruf kolom Excel (0 -> A, 1 -> B, ...)
     */
    private static function colNumberToLetter($colIdx) {
        $letter = '';
        $colIdx = (int)$colIdx + 1;
        while ($colIdx > 0) {
            $mod = ($colIdx - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $colIdx = (int)(($colIdx - $mod) / 26);
        }
        return $letter;
    }

    /**
     * Parse berkas XML Spreadsheet 2003 (.xls / .xml)
     */
    private static function parseXmlSpreadsheet($filePath) {
        $content = file_get_contents($filePath);
        if (strpos($content, '<Workbook') === false || strpos($content, '<Table') === false) {
            return [];
        }

        $rows = [];
        $xml = @simplexml_load_string($content);
        if (!$xml) return [];

        // Daftarkan namespace standar
        $namespaces = $xml->getNamespaces(true);
        $ssNs = $namespaces['ss'] ?? 'urn:schemas-microsoft-com:office:spreadsheet';
        $xml->registerXPathNamespace('ss', $ssNs);

        $tables = $xml->xpath('//ss:Table | //Table');
        if (empty($tables)) return [];

        $table = $tables[0];
        $xmlRows = $table->xpath('ss:Row | Row');

        foreach ($xmlRows as $row) {
            $cells = $row->xpath('ss:Cell | Cell');
            $rowData = [];
            $colIdx = 0;

            foreach ($cells as $cell) {
                // Tangani ss:Index jika sel melompati kolom
                $cellAttrs = $cell->attributes($ssNs);
                if (isset($cellAttrs['Index'])) {
                    $colIdx = (int)$cellAttrs['Index'] - 1;
                }

                $dataNodes = $cell->xpath('ss:Data | Data');
                $val = '';
                if (!empty($dataNodes)) {
                    $val = (string)$dataNodes[0];
                }
                $rowData[$colIdx] = trim($val);
                $colIdx++;
            }

            if (!empty($rowData)) {
                $maxIdx = max(array_keys($rowData));
                $denseRow = [];
                for ($i = 0; $i <= $maxIdx; $i++) {
                    $denseRow[$i] = $rowData[$i] ?? '';
                }
                $rows[] = $denseRow;
            }
        }

        return $rows;
    }

    /**
     * Parse berkas CSV / TSV / teks dengan deteksi delimiter otomatis
     */
    public static function parseCsv($filePath) {
        $content = file_get_contents($filePath);
        if ($content === false || strlen($content) === 0) {
            return [];
        }

        // Hapus UTF-8 BOM
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Ambil baris-baris pertama untuk mendeteksi delimiter yang paling konsisten
        $sampleLines = explode("\n", str_replace(["\r\n", "\r"], "\n", $content), 10);
        $delimiters = [';', ',', "\t", '|'];
        $delimScores = [';' => 0, ',' => 0, "\t" => 0, '|' => 0];

        foreach ($sampleLines as $line) {
            $line = trim($line);
            if (empty($line) || stripos($line, 'sep=') === 0) continue;
            foreach ($delimiters as $d) {
                $count = substr_count($line, $d);
                if ($count > 0) {
                    $delimScores[$d] += $count;
                }
            }
        }

        arsort($delimScores);
        $delimiter = key($delimScores);
        if ($delimScores[$delimiter] <= 0) {
            $delimiter = ';';
        }

        // Tulis ke temp stream agar fgetcsv dapat membaca multiline fields dan quote escaping dengan akurat
        $temp = fopen('php://temp', 'r+');
        fwrite($temp, $content);
        rewind($temp);

        $rows = [];
        while (($row = fgetcsv($temp, 0, $delimiter)) !== false) {
            if (empty($row)) continue;

            // Lewati baris penanda Excel 'sep=...'
            $firstCell = trim($row[0] ?? '');
            if (stripos($firstCell, 'sep=') === 0) {
                continue;
            }

            // Bersihkan spasi tepi setiap sel
            $cleaned = array_map(function($val) {
                return trim((string)$val);
            }, $row);

            // Lewati jika seluruh kolom dalam baris ini kosong
            $hasContent = false;
            foreach ($cleaned as $cell) {
                if ($cell !== '') {
                    $hasContent = true;
                    break;
                }
            }

            if ($hasContent) {
                $rows[] = $cleaned;
            }
        }

        fclose($temp);
        return $rows;
    }

    /**
     * Generate berkas binary XLSX murni ke string buffer
     * @param array $headers Array 1D nama header kolom: ['NIS', 'Nama', ...]
     * @param array $dataRows Array 2D baris data: [ ['123', 'Ahmad', ...], ... ]
     * @param string $sheetName Nama sheet
     * @return string Binary content XLSX
     */
    public static function createXlsxBuffer(array $headers, array $dataRows = [], $sheetName = 'Data Siswa') {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return '';
        }

        // Shared strings dictionary
        $strings = [];
        $stringMap = [];

        $getStringIdx = function($str) use (&$strings, &$stringMap) {
            $str = (string)$str;
            if (isset($stringMap[$str])) {
                return $stringMap[$str];
            }
            $idx = count($strings);
            $strings[] = $str;
            $stringMap[$str] = $idx;
            return $idx;
        };

        // Buat sheet1 XML
        $allRows = array_merge([$headers], $dataRows);
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        
        // Definisikan lebar kolom otomatis
        $colCount = count($headers);
        $sheetXml .= '  <cols>' . "\n";
        for ($c = 1; $c <= $colCount; $c++) {
            $sheetXml .= '    <col min="' . $c . '" max="' . $c . '" width="22" customWidth="1"/>' . "\n";
        }
        $sheetXml .= '  </cols>' . "\n";

        $sheetXml .= '  <sheetData>' . "\n";
        $rowNum = 1;
        foreach ($allRows as $row) {
            $isHeader = ($rowNum === 1);
            $styleAttr = $isHeader ? ' s="1"' : ' s="2"';
            $sheetXml .= '    <row r="' . $rowNum . '">' . "\n";

            $colIdx = 0;
            foreach ($row as $cellVal) {
                $colLetter = self::colNumberToLetter($colIdx);
                $cellRef = $colLetter . $rowNum;
                $strIdx = $getStringIdx((string)$cellVal);

                $sheetXml .= '      <c r="' . $cellRef . '" t="s"' . $styleAttr . '><v>' . $strIdx . '</v></c>' . "\n";
                $colIdx++;
            }

            $sheetXml .= '    </row>' . "\n";
            $rowNum++;
        }
        $sheetXml .= '  </sheetData>' . "\n";
        $sheetXml .= '</worksheet>';

        // Buat sharedStrings XML
        $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($strings) . '" uniqueCount="' . count($strings) . '">' . "\n";
        foreach ($strings as $str) {
            $escaped = htmlspecialchars($str, ENT_XML1, 'UTF-8');
            $ssXml .= '  <si><t>' . $escaped . '</t></si>' . "\n";
        }
        $ssXml .= '</sst>';

        // Content Types
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>');

        // Root rels
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

        // Workbook rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>');

        // Workbook
        $safeSheetName = htmlspecialchars(substr($sheetName, 0, 31), ENT_XML1, 'UTF-8');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="' . $safeSheetName . '" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>');

        // Styles (dengan font tebal & background hijau muda untuk header)
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="3">
    <font><name val="Segoe UI"/><sz val="10"/></font>
    <font><b/><name val="Segoe UI"/><sz val="11"/><color rgb="FFFFFFFF"/></font>
    <font><name val="Segoe UI"/><sz val="10"/><color rgb="FF1E293B"/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF059669"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left style="thin"><color rgb="FFCBD5E1"/></left>
      <right style="thin"><color rgb="FFCBD5E1"/></right>
      <top style="thin"><color rgb="FFCBD5E1"/></top>
      <bottom style="thin"><color rgb="FFCBD5E1"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="3">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="2" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>
  </cellXfs>
</styleSheet>');

        $zip->addFromString('xl/sharedStrings.xml', $ssXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();
        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }

    /**
     * Download langsung berkas XLSX ke browser
     */
    public static function downloadXlsx($filename, array $headers, array $dataRows = [], $sheetName = 'Data') {
        $buffer = self::createXlsxBuffer($headers, $dataRows, $sheetName);
        if (headers_sent()) {
            echo $buffer;
            exit();
        }

        if (strtolower(substr($filename, -5)) !== '.xlsx') {
            $filename .= '.xlsx';
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . strlen($buffer));

        echo $buffer;
        exit();
    }

    /**
     * Download berkas CSV dengan UTF-8 BOM dan separator semicolon ';' resmi untuk Excel
     */
    public static function downloadCsv($filename, array $headers, array $dataRows = []) {
        if (strtolower(substr($filename, -4)) !== '.csv') {
            $filename .= '.csv';
        }

        if (!headers_sent()) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        $output = fopen('php://output', 'w');
        // UTF-8 BOM agar terbaca rapi dengan karakter spesial di Microsoft Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fwrite($output, "sep=;\n");

        fputcsv($output, $headers, ';');
        foreach ($dataRows as $row) {
            fputcsv($output, $row, ';');
        }

        fclose($output);
        exit();
    }
}
