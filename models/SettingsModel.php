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
            'npsn' => '20229871',
            'kepala_sekolah' => 'H. Supriyadi, M.M.',
            'telepon' => '(022) 7950123',
            'alamat' => 'Jl. Raya Cicalengka No. 45, Cicalengka, Kabupaten Bandung, Jawa Barat 40395',
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
            'landing_maps_url' => 'https://maps.google.com/maps?q=Cicalengka&t=&z=13&ie=UTF8&iwloc=&output=embed'
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
