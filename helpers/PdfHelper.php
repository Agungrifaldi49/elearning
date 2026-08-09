<?php
/**
 * PDF & Report Helper
 * Generates clean, printable HTML-to-PDF reports with School Kop & Logo
 */

class PdfHelper {

    /**
     * Render Printable HTML Report Layout
     */
    public static function renderReportPage($title, $subtitle, $tableHtml) {
        $settingsPath = ROOT_PATH . 'config/settings.json';
        $appSettings = [];
        if (file_exists($settingsPath)) {
            $appSettings = json_decode(file_get_contents($settingsPath), true) ?: [];
        }

        $schoolName = !empty($appSettings['nama_sekolah']) ? $appSettings['nama_sekolah'] : 'SMK MUTHIA HARAPAN CICALENGKA';
        $alamat = !empty($appSettings['alamat']) ? $appSettings['alamat'] : 'Jl. Raya Cicalengka, Kab. Bandung, Jawa Barat | Email: info@smkmh-cicalengka.sch.id';
        $kepalaSekolah = !empty($appSettings['kepala_sekolah']) ? $appSettings['kepala_sekolah'] : 'H. Supriyadi, M.M.';

        $rawLogo = $appSettings['logo'] ?? '';
        $logoUrl = null;
        if (!empty($rawLogo)) {
            if (strpos($rawLogo, 'assets/uploads/') === 0 || strpos($rawLogo, 'uploads/') === 0) {
                $logoUrl = BASE_URL . $rawLogo;
            } else {
                $logoUrl = BASE_URL . 'assets/uploads/logo/' . $rawLogo;
            }
        }

        $logoHtml = $logoUrl 
            ? "<img src='" . htmlspecialchars($logoUrl) . "' alt='Logo Sekolah' style='height:70px; max-width:200px; object-fit:contain;'>" 
            : "<div style='background:#0D6EFD; color:#fff; border-radius:12px; width:65px; height:65px; display:inline-flex; align-items:center; justify-content:center; font-size:32px; font-weight:bold;'>🎓</div>";

        $date = date('d F Y');
        return "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>{$title}</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 25px; }
                .kop-wrapper { display: table; width: 100%; border-bottom: 3px double #0D6EFD; padding-bottom: 12px; margin-bottom: 20px; }
                .kop-logo { display: table-cell; width: 90px; vertical-align: middle; text-align: center; }
                .kop-text { display: table-cell; vertical-align: middle; text-align: left; padding-left: 15px; }
                .kop-text h2 { margin: 0; color: #0D6EFD; font-size: 19px; text-transform: uppercase; letter-spacing: 0.5px; }
                .kop-text h3 { margin: 3px 0 0 0; font-size: 13px; font-weight: normal; color: #444; }
                .kop-text p { margin: 3px 0 0 0; font-size: 11px; color: #666; }
                .report-title { font-size: 16px; font-weight: bold; margin-bottom: 4px; text-align: center; text-decoration: underline; color: #0f172a; }
                .report-sub { font-size: 12px; text-align: center; margin-bottom: 18px; color: #555; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; }
                th { background-color: #f1f5f9; color: #0f172a; font-weight: bold; text-transform: uppercase; font-size: 11px; }
                tr:nth-child(even) { background-color: #f8fafc; }
                .footer { margin-top: 35px; width: 100%; }
                .footer-table { width: 100%; border: none; }
                .footer-table td { border: none; background: none; }
                @media print {
                    .no-print { display: none; }
                    body { margin: 0; }
                }
            </style>
        </head>
        <body>
            <div class='no-print' style='margin-bottom:15px; text-align:right;'>
                <button onclick='window.print()' style='padding:9px 18px; background:#0D6EFD; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:bold;'>🖨️ Cetak / Simpan PDF</button>
            </div>

            <div class='kop-wrapper'>
                <div class='kop-logo'>
                    {$logoHtml}
                </div>
                <div class='kop-text'>
                    <h2>" . htmlspecialchars($schoolName) . "</h2>
                    <h3>Portal E-Learning & Sistem Informasi Akademik</h3>
                    <p>" . htmlspecialchars($alamat) . "</p>
                </div>
            </div>

            <div class='report-title'>{$title}</div>
            <div class='report-sub'>{$subtitle}</div>

            {$tableHtml}

            <div class='footer'>
                <table class='footer-table'>
                    <tr>
                        <td style='width:65%;'></td>
                        <td style='text-align:center;'>
                            <p>Cicalengka, {$date}</p>
                            <p style='margin-top:55px;'><b><u>" . htmlspecialchars($kepalaSekolah) . "</u></b><br><small style='color:#555;'>Kepala Sekolah / Pengelola</small></p>
                        </td>
                    </tr>
                </table>
            </div>
        </body>
        </html>
        ";
    }
}
