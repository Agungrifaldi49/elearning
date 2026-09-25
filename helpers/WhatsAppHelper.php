<?php
/**
 * WhatsApp Gateway Helper (cURL Native)
 * E-Learning SMK Muthia Harapan Cicalengka
 * 
 * Mengirim pesan WhatsApp ke nomor orang tua siswa saat absensi
 * dan menyediakan integrasi dinamis dengan template pesan yang dapat diubah.
 */

require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'models/SettingsModel.php';

class WhatsAppHelper {

    /**
     * Bersihkan dan format nomor telepon ke standar WhatsApp internasional (62xxx)
     */
    public static function formatPhone($phone) {
        if (empty($phone)) return '';
        
        // Hapus karakter selain angka dan tanda tambah
        $clean = preg_replace('/[^0-9]/', '', (string)$phone);
        
        // Jika berawalan '08', ubah menjadi '628'
        if (strpos($clean, '0') === 0) {
            $clean = '62' . substr($clean, 1);
        } elseif (strpos($clean, '8') === 0) {
            $clean = '62' . $clean;
        }

        return $clean;
    }

    /**
     * Kirim Pesan WhatsApp via PHP Native cURL
     * Sesuai spesifikasi API WhatsApp Gateway
     * 
     * @param string $phone Nomor HP penerima
     * @param string $message Isi pesan
     * @param string|null $mediaUrl URL lampiran opsional
     * @return array Status dan response
     */
    public static function send($phone, $message, $mediaUrl = null) {
        $settingsModel = new SettingsModel();
        $settings = $settingsModel->getAll();

        // Periksa apakah gateway aktif
        $enabled = ($settings['wa_gateway_enabled'] ?? '1') === '1';
        if (!$enabled) {
            return ['status' => false, 'message' => 'WhatsApp Gateway sedang dinonaktifkan di pengaturan sistem.'];
        }

        $formattedPhone = self::formatPhone($phone);
        if (empty($formattedPhone)) {
            return ['status' => false, 'message' => 'Nomor WhatsApp tujuan kosong atau format tidak valid.'];
        }

        $url = trim($settings['wa_gateway_url'] ?? 'https://whatsaap-gateway.smkmuthiaharapancicalengka.my.id/api/send-message');
        if (empty($url)) {
            $url = 'https://whatsaap-gateway.smkmuthiaharapancicalengka.my.id/api/send-message';
        }

        $apiKey = trim($settings['wa_api_key'] ?? 'my_secret_api_key_123');

        $data = [
            'phone'   => $formattedPhone,
            'message' => $message,
        ];
        if ($mediaUrl) {
            $data['mediaUrl'] = $mediaUrl;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
            ],
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $isSuccess = false;
        $decoded = null;

        if ($err) {
            $result = ['status' => false, 'message' => 'cURL Error: ' . $err, 'http_code' => $httpCode];
        } else {
            $decoded = json_decode($response, true);
            if (is_array($decoded) && isset($decoded['status']) && $decoded['status'] == true) {
                $isSuccess = true;
                $result = ['status' => true, 'message' => $decoded['message'] ?? 'Pesan berhasil dikirim.', 'response' => $decoded];
            } elseif ($httpCode >= 200 && $httpCode < 300) {
                $isSuccess = true;
                $result = ['status' => true, 'message' => 'Pesan WhatsApp berhasil dikirim.', 'response' => $decoded ?: $response];
            } else {
                $errMsg = $decoded['message'] ?? ($decoded['error'] ?? ('Gagal mengirim pesan (HTTP ' . $httpCode . ')'));
                $result = ['status' => false, 'message' => $errMsg, 'response' => $decoded ?: $response];
            }
        }

        // Simpan log pengiriman
        self::logPengiriman($formattedPhone, $message, $isSuccess ? 'sent' : 'failed', $result);

        return $result;
    }

    /**
     * Catat log pengiriman WhatsApp ke database
     */
    private static function logPengiriman($phone, $message, $status, $response) {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO wa_logs (phone, type, status, message, response, created_at)
                VALUES (?, 'absensi_notif', ?, ?, ?, NOW())
            ");
            $respJson = is_array($response) ? json_encode($response) : (string)$response;
            $stmt->execute([$phone, $status, $message, $respJson]);
        } catch (\Throwable $e) {
            // Abaikan error logging agar tidak mengganggu alur utama
        }
    }

    /**
     * Ganti placeholder dalam template pesan WhatsApp
     */
    public static function parseTemplate($template, $data = []) {
        $search = [
            '{nama_siswa}',
            '{nis}',
            '{nisn}',
            '{kelas}',
            '{jurusan}',
            '{tanggal}',
            '{jam}',
            '{status}',
            '{keterangan}',
            '{sekolah}',
            '{petugas}'
        ];

        $replace = [
            $data['nama_siswa'] ?? '',
            $data['nis'] ?? '',
            $data['nisn'] ?? '',
            $data['kelas'] ?? '',
            $data['jurusan'] ?? '',
            $data['tanggal'] ?? date('d-m-Y'),
            $data['jam'] ?? date('H:i') . ' WIB',
            $data['status'] ?? 'Hadir',
            $data['keterangan'] ?? '-',
            $data['sekolah'] ?? 'SMK Muthia Harapan Cicalengka',
            $data['petugas'] ?? 'Sistem Presensi'
        ];

        return str_replace($search, $replace, $template);
    }

    /**
     * Mengirim notifikasi WhatsApp absensi ke nomor orang tua siswa
     * 
     * @param array $siswa Data siswa lengkap (harus ada minimal nama_lengkap, no_ortu, nama_kelas)
     * @param string $tipeJenis 'masuk_tepat' | 'masuk_terlambat' | 'pulang' | 'izin' | 'sakit' | 'alpha'
     * @param array $extraInfo Informasi tambahan (jam, tanggal, status, keterangan)
     * @return array Status pengiriman
     */
    public static function sendNotificationAbsensi($siswa, $tipeJenis = 'masuk_tepat', $extraInfo = []) {
        // Cek nomor orang tua
        $noOrtu = trim($siswa['no_ortu'] ?? '');
        if (empty($noOrtu)) {
            return ['status' => false, 'message' => 'Siswa tidak memiliki nomor orang tua (no_ortu kosong).'];
        }

        $settingsModel = new SettingsModel();
        $settings = $settingsModel->getAll();

        if (($settings['wa_gateway_enabled'] ?? '1') !== '1') {
            return ['status' => false, 'message' => 'Notifikasi WhatsApp dinonaktifkan.'];
        }

        // Tentukan template berdasarkan jenis absensi
        $templateKey = 'wa_template_masuk_tepat';
        $statusStr = 'Hadir Tepat Waktu';

        switch (strtolower($tipeJenis)) {
            case 'masuk_terlambat':
                $templateKey = 'wa_template_masuk_terlambat';
                $statusStr = 'Hadir (Terlambat)';
                break;
            case 'pulang':
                $templateKey = 'wa_template_pulang';
                $statusStr = 'Pulang';
                break;
            case 'izin':
                $templateKey = 'wa_template_tidak_hadir';
                $statusStr = 'Izin';
                break;
            case 'sakit':
                $templateKey = 'wa_template_tidak_hadir';
                $statusStr = 'Sakit';
                break;
            case 'alpha':
            case 'alpa':
                $templateKey = 'wa_template_tidak_hadir';
                $statusStr = 'Alpha (Tanpa Keterangan)';
                break;
            case 'masuk_tepat':
            default:
                $templateKey = 'wa_template_masuk_tepat';
                $statusStr = 'Hadir Tepat Waktu';
                break;
        }

        $template = $settings[$templateKey] ?? '';
        if (empty($template)) {
            // Fallback default template jika kosong
            $template = "Halo Bapak/Ibu Orang Tua dari {nama_siswa} (Kelas {kelas}),\n\nKami menginformasikan presensi putra/putri Anda di {sekolah}:\nTanggal: {tanggal}\nJam: {jam}\nStatus: {status}\nKeterangan: {keterangan}\n\nTerima kasih.";
        }

        // Format tanggal bahasa Indonesia
        $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        $tglRaw = $extraInfo['tanggal'] ?? date('Y-m-d');
        $timeObj = strtotime($tglRaw);
        $tglFormatted = $namaHari[date('w', $timeObj)] . ', ' . date('j', $timeObj) . ' ' . $namaBulan[(int)date('n', $timeObj)] . ' ' . date('Y', $timeObj);

        $jamFormatted = $extraInfo['jam'] ?? (date('H:i') . ' WIB');
        if (strpos($jamFormatted, 'WIB') === false) {
            $jamFormatted .= ' WIB';
        }

        $data = [
            'nama_siswa' => $siswa['nama_lengkap'] ?? 'Siswa',
            'nis'        => $siswa['nis'] ?? ($siswa['nisn'] ?? '-'),
            'nisn'       => $siswa['nisn'] ?? '-',
            'kelas'      => $siswa['nama_kelas'] ?? ($siswa['kelas'] ?? '-'),
            'jurusan'    => $siswa['nama_jurusan'] ?? '-',
            'tanggal'    => $tglFormatted,
            'jam'        => $jamFormatted,
            'status'     => $extraInfo['status'] ?? $statusStr,
            'keterangan' => $extraInfo['keterangan'] ?? ($extraInfo['status_keterangan'] ?? '-'),
            'sekolah'    => $settings['nama_sekolah'] ?? 'SMK Muthia Harapan Cicalengka',
            'petugas'    => $extraInfo['petugas'] ?? 'Sistem Presensi Digital'
        ];

        $pesanTerkirim = self::parseTemplate($template, $data);

        return self::send($noOrtu, $pesanTerkirim);
    }
}
