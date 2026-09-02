<?php
/**
 * Database Configuration & PDO Singleton Connection
 * E-Learning SMK Muthia Harapan Cicalengka
 */

class Database {
    private static $host = '127.0.0.1';
    private static $db_name = 'db_elearning_smkmh';
    private static $username = 'root';
    private static $password = '';
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn !== null) {
            return self::$conn;
        }

        $hosts = [self::$host, 'localhost'];
        $ports = [3306];
        $lastException = null;

        $pdoOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 3,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];

        foreach ($hosts as $h) {
            foreach ($ports as $p) {
                try {
                    self::$conn = new PDO(
                        "mysql:host={$h};port={$p};dbname=" . self::$db_name . ";charset=utf8mb4",
                        self::$username,
                        self::$password,
                        $pdoOptions
                    );
                    if (self::$conn) {
                        break 2;
                    }
                } catch (PDOException $e) {
                    $lastException = $e;
                }
            }
        }

        if (self::$conn === null && $lastException) {
            $e = $lastException;
            $isUnknownDb = strpos($e->getMessage(), 'Unknown database') !== false || $e->getCode() == 1049;

            if ($isUnknownDb) {
                try {
                    $pdo = new PDO("mysql:host=" . self::$host . ";charset=utf8mb4", self::$username, self::$password, [PDO::ATTR_TIMEOUT => 3]);
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . self::$db_name . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                    
                    self::$conn = new PDO(
                        "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4",
                        self::$username,
                        self::$password,
                        $pdoOptions
                    );

                    self::autoImport();
                    self::ensurePerformanceIndexes();
                } catch (PDOException $ex) {
                    self::showConnectionError($ex);
                }
            } else {
                self::showConnectionError($e);
            }
        }

        return self::$conn;
    }

    private static function showConnectionError(PDOException $ex) {
        if (headers_sent() === false) {
            http_response_code(503);
        }
        
        $isJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
        if ($isJson || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => false,
                'message' => 'Kendala Server / Koneksi Database sedang sibuk. Silakan coba beberapa saat lagi.',
                'error' => $ex->getMessage()
            ]);
            exit();
        }

        die("<div style='font-family:sans-serif; padding:20px; max-width:600px; margin:40px auto; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:8px;'>
            <h3>Kendala Server / Koneksi Database</h3>
            <p>Sistem sedang menerima lonjakan trafik yang tinggi atau layanan MySQL terhenti.</p>
            <p><strong>Detail:</strong> " . htmlspecialchars($ex->getMessage()) . "</p>
            <p>Silakan segarkan (refresh) halaman beberapa saat lagi. Pastikan service MySQL di server/XAMPP sudah berjalan.</p>
        </div>");
    }

    private static function autoImport() {
        $schemaPath = ROOT_PATH . 'database/schema.sql';
        $seedersPath = ROOT_PATH . 'database/seeders.sql';

        if (file_exists($schemaPath)) {
            $sql = file_get_contents($schemaPath);
            self::$conn->exec($sql);
        }

        if (file_exists($seedersPath)) {
            $seedSql = file_get_contents($seedersPath);
            self::$conn->exec($seedSql);
        }
    }

    public static function ensurePerformanceIndexes() {
        if (self::$conn === null) return;
        static $indexesEnsured = false;
        if ($indexesEnsured) return;
        $indexesEnsured = true;

        $indexes = [
            "idx_log_login_username_status_created" => "ALTER TABLE log_login ADD INDEX idx_log_login_username_status_created (username, status, created_at)",
            "idx_log_login_created_at" => "ALTER TABLE log_login ADD INDEX idx_log_login_created_at (created_at)",
            "idx_users_role_status" => "ALTER TABLE users ADD INDEX idx_users_role_status (role_id, status)",
            "idx_siswa_user" => "ALTER TABLE siswa ADD INDEX idx_siswa_user (user_id)",
            "idx_guru_user" => "ALTER TABLE guru ADD INDEX idx_guru_user (user_id)",
            "idx_aktivitas_user_created" => "ALTER TABLE aktivitas ADD INDEX idx_aktivitas_user_created (user_id, created_at)",
            "idx_absensi_jadwal_tanggal" => "ALTER TABLE absensi ADD INDEX idx_absensi_jadwal_tanggal (jadwal_id, tanggal)",
            "idx_absensi_siswa_tanggal" => "ALTER TABLE absensi ADD INDEX idx_absensi_siswa_tanggal (siswa_id, tanggal)",
            "idx_jawaban_siswa_siswa_quiz" => "ALTER TABLE jawaban_siswa ADD INDEX idx_jawaban_siswa_siswa_quiz (siswa_id, quiz_id)",
            "idx_hasil_ujian_siswa_ujian" => "ALTER TABLE hasil_ujian ADD INDEX idx_hasil_ujian_siswa_ujian (siswa_id, ujian_id)",
            "idx_hasil_quiz_siswa_quiz" => "ALTER TABLE hasil_quiz ADD INDEX idx_hasil_quiz_siswa_quiz (siswa_id, quiz_id)",
            "idx_notifikasi_user_read" => "ALTER TABLE notifikasi ADD INDEX idx_notifikasi_user_read (user_id, is_read)",
            "idx_chat_sender_receiver" => "ALTER TABLE chat ADD INDEX idx_chat_sender_receiver (sender_id, receiver_id, is_read)"
        ];

        foreach ($indexes as $name => $sql) {
            try {
                self::$conn->exec($sql);
            } catch (\Throwable $e) {
                // Index may already exist or table missing, ignore safely
            }
        }
    }
}
