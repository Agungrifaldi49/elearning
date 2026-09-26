<?php
/**
 * SettingsModel.php
 * Model untuk Pengaturan Sistem, Profil Sekolah, SMTP, dan Tema
 */
class SettingsModel {
    private $db;
    private $settingsFile;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->settingsFile = ROOT_PATH . 'config/settings.json';
        $this->initTable();
    }

    /**
     * Inisialisasi tabel settings atau file JSON cadangan
     */
    private function initTable() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS settings (
                    `setting_key` VARCHAR(100) PRIMARY KEY,
                    `setting_value` TEXT NULL,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (Exception $e) {
            // Ignore if already exists
        }

        // Default values if empty
        $defaults = [
            'nama_sekolah' => 'SMK Muthia Harapan Cicalengka',
            'npsn' => '69725846',
            'akreditasi' => 'B',
            'kepala_sekolah' => 'H. ASEP SAEPULLOH, S. Ag',
            'telepon' => '(022) 7950123',
            'email' => 'info@smkmh-cicalengka.sch.id',
            'website' => 'www.smkmuthiaharapan.sch.id',
            'alamat' => 'Jalan Babakan Peuteuy Nomor 300, Desa Babakanpeuteuy, Kecamatan Cicalengka, Kabupaten Bandung, Jawa Barat',
            'logo' => '',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => '587',
            'smtp_user' => 'elearning@smkmuthiaharapan.sch.id',
            'smtp_pass' => '••••••••••••',
            'smtp_crypto' => 'tls',
            'tema' => 'light',
            'api_key' => 'smkmh_live_api_88923a19e83c7410294b',
            'landing_hero_badge' => 'Portal Pembelajaran Digital',
            'landing_hero_title' => 'E-Learning SMK Muthia Harapan Cicalengka',
            'landing_hero_desc' => 'Sistem Manajemen Pembelajaran Digital Interaktif, Transparan, dan Modern untuk Membentuk Generasi Unggul Siap Kerja.',
            'landing_hero_card_title' => 'KBM Digital Terpadu',
            'landing_hero_card_desc' => 'Materi, CBT, Quiz, Absensi QR Code, & Laporan Real-time',
            'landing_profil_tag' => 'Profil Sekolah',
            'landing_profil_title' => 'Mencetak Lulusan Berkarakter & Competent',
            'landing_profil_desc' => 'SMK Muthia Harapan Cicalengka berkomitmen memberikan pendidikan kejuruan berkualitas tinggi berbasis teknologi informasi dan industri modern di Jawa Barat.',
            'landing_visi_title' => 'Visi Utama',
            'landing_visi_desc' => 'Menjadi SMK Unggulan berstandar Nasional berbasis Teknologi & Imtaq.',
            'landing_misi_title' => 'Misi Presisi',
            'landing_misi_desc' => 'Mengembangkan kurikulum industri & sertifikasi kompetensi keahlian.',
            'landing_video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'landing_kontak_tag' => 'Hubungi Kami',
            'landing_kontak_title' => 'Lokasi & Kontak Sekolah',
            'landing_email' => 'info@smkmh-cicalengka.sch.id',
            'landing_maps_url' => 'https://maps.google.com/maps?q=Cicalengka&t=&z=13&ie=UTF8&iwloc=&output=embed',
            'landing_wa_enabled' => '1',
            'landing_wa_number' => '082198765433',
            'landing_wa_label' => 'Butuh Bantuan? Chat Admin',
            'landing_wa_text' => 'Halo Tim Bantuan E-Learning SMK Muthia Harapan, saya mengalami kendala teknis saat menggunakan website. Mohon bantuannya.',
            'lokasi_sekolah_nama' => 'SMK Muthia Harapan Cicalengka',
            'lokasi_sekolah_lat' => '-6.984042',
            'lokasi_sekolah_lng' => '107.838612',
            'lokasi_sekolah_radius' => '150',
            'presensi_mode_jadwal' => 'jadwal',
            'presensi_kegiatan_serentak_nama' => '',
            'presensi_toleransi_masuk_menit' => '60',
            'presensi_toleransi_terlambat_menit' => '0',
            'presensi_guru_tanpa_jadwal' => 'standar',
            'presensi_jam_masuk_mulai' => '06:00',
            'presensi_jam_masuk_batas' => '07:30',
            'presensi_jam_pulang_mulai' => '15:00',
            // WhatsApp Gateway & Parent Notification Settings
            'wa_gateway_enabled' => '1',
            'wa_gateway_url' => 'https://whatsaap-gateway.smkmuthiaharapancicalengka.my.id/api/send-message',
            'wa_api_key' => 'my_secret_api_key_123',
            'wa_webhook_secret' => 'whsec_secret_anda',
            'wa_template_masuk_tepat' => "Halo Bapak/Ibu Orang Tua/Wali dari {nama_siswa} (Kelas {kelas}),\n\nKami menginformasikan bahwa putra/putri Anda telah tiba di {sekolah} dan berhasil melakukan presensi MASUK pada:\n📅 Hari/Tanggal: {tanggal}\n⏰ Pukul: {jam}\n📌 Status: {status}\n\nTerima kasih atas kerja sama Bapak/Ibu dalam mendukung kedisiplinan belajar ananda.",
            'wa_template_masuk_terlambat' => "Halo Bapak/Ibu Orang Tua/Wali dari {nama_siswa} (Kelas {kelas}),\n\nKami menginformasikan bahwa putra/putri Anda telah tiba di {sekolah} dan melakukan presensi MASUK (TERLAMBAT) pada:\n📅 Hari/Tanggal: {tanggal}\n⏰ Pukul: {jam}\n📌 Status: {status}\nℹ️ Keterangan: {keterangan}\n\nMohon perhatian Bapak/Ibu untuk dapat memotivasi ananda agar tiba lebih awal di sekolah. Terima kasih.",
            'wa_template_pulang' => "Halo Bapak/Ibu Orang Tua/Wali dari {nama_siswa} (Kelas {kelas}),\n\nKami menginformasikan bahwa kegiatan pembelajaran di {sekolah} hari ini telah selesai. Putra/putri Anda telah melakukan presensi PULANG pada:\n📅 Hari/Tanggal: {tanggal}\n⏰ Pukul: {jam}\n📌 Status: {status}\n\nSemoga ananda sampai di rumah dengan selamat dan sehat walafiat. Terima kasih.",
            'wa_template_tidak_hadir' => "Halo Bapak/Ibu Orang Tua/Wali dari {nama_siswa} (Kelas {kelas}),\n\nInformasi Presensi Sekolah dari {sekolah}:\nPutra/putri Anda hari ini tercatat dengan status:\n📅 Hari/Tanggal: {tanggal}\n📌 Status: {status}\nℹ️ Keterangan: {keterangan}\n\nJika terdapat kekeliruan atau kendala terkait kehadiran ananda, silakan hubungi pihak sekolah / wali kelas. Terima kasih."
        ];

        foreach ($defaults as $key => $val) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->execute([$key, $val]);
        }
    }

    /**
     * Ambil semua data pengaturan
     */
    public function getAll(): array {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM settings");
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        if (empty($results) && file_exists($this->settingsFile)) {
            $json = file_get_contents($this->settingsFile);
            return json_decode($json, true) ?: [];
        }

        return $results ?: [];
    }

    /**
     * Simpan / update sekelompok data pengaturan
     */
    public function saveBatch(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
        ");

        foreach ($data as $key => $value) {
            $stmt->execute([$key, (string)$value]);
        }

        // Mirror to JSON file as fallback
        $all = $this->getAll();
        file_put_contents($this->settingsFile, json_encode($all, JSON_PRETTY_PRINT));

        return true;
    }
}
