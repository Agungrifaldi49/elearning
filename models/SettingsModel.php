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
            'api_key' => 'smkmh_live_api_88923a19e83c7410294b'
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
