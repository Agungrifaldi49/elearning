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

        $credentials = [
            [
                'host' => defined('DB_HOST') ? DB_HOST : (getenv('DB_HOST') ?: self::$host),
                'db' => defined('DB_NAME') ? DB_NAME : (getenv('DB_NAME') ?: self::$db_name),
                'user' => defined('DB_USER') ? DB_USER : (getenv('DB_USER') ?: self::$username),
                'pass' => defined('DB_PASS') ? DB_PASS : (getenv('DB_PASS') ?: self::$password),
            ],
            [
                'host' => 'localhost',
                'db' => 'smkmuth3_db_elearning_smkmh',
                'user' => 'smkmuth3_admin',
                'pass' => 'Smkmhc@2011',
            ],
            [
                'host' => '127.0.0.1',
                'db' => 'smkmuth3_db_elearning_smkmh',
                'user' => 'smkmuth3_admin',
                'pass' => 'Smkmhc@2011',
            ]
        ];

        $lastException = null;
        $pdoOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 2,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];

        foreach ($credentials as $cred) {
            try {
                self::$conn = new PDO(
                    "mysql:host={$cred['host']};port=3306;dbname={$cred['db']};charset=utf8mb4",
                    $cred['user'],
                    $cred['pass'],
                    $pdoOptions
                );
                if (self::$conn) {
                    break;
                }
            } catch (PDOException $e) {
                $lastException = $e;
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

        if (self::$conn !== null) {
            self::ensureCustomTables();
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

    public static function ensureCustomTables() {
        if (self::$conn === null) return;
        static $tablesEnsured = false;
        if ($tablesEnsured) return;
        $tablesEnsured = true;

        try {
            self::$conn->exec("
                CREATE TABLE IF NOT EXISTS supervisi_guru (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    guru_id INT NOT NULL,
                    kepsek_id INT NOT NULL,
                    tanggal_supervisi DATE NOT NULL,
                    mapel_id INT NULL,
                    kelas_id INT NULL,
                    skor_perencanaan DECIMAL(5,2) DEFAULT 0,
                    skor_pelaksanaan DECIMAL(5,2) DEFAULT 0,
                    skor_evaluasi DECIMAL(5,2) DEFAULT 0,
                    skor_kedisiplinan DECIMAL(5,2) DEFAULT 0,
                    nilai_akhir DECIMAL(5,2) DEFAULT 0,
                    predikat VARCHAR(20) DEFAULT 'Baik',
                    catatan_kekuatan TEXT NULL,
                    catatan_perbaikan TEXT NULL,
                    rekomendasi_tindak_lanjut TEXT NULL,
                    status ENUM('draft', 'final') DEFAULT 'final',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_sup_guru (guru_id),
                    INDEX idx_sup_tgl (tanggal_supervisi)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS pembayaran_tagihan (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    siswa_id INT NOT NULL,
                    nis VARCHAR(30) NULL,
                    nisn VARCHAR(30) NULL,
                    jenis_pembayaran VARCHAR(50) NOT NULL DEFAULT 'SPP',
                    kode_tagihan VARCHAR(50) NOT NULL UNIQUE,
                    judul VARCHAR(150) NOT NULL,
                    nominal DECIMAL(12,2) NOT NULL DEFAULT 0,
                    nominal_terbayar DECIMAL(12,2) NOT NULL DEFAULT 0,
                    sisa_tagihan DECIMAL(12,2) NOT NULL DEFAULT 0,
                    periode_bulan VARCHAR(30) NULL,
                    tahun_ajaran VARCHAR(20) NOT NULL DEFAULT '2025/2026',
                    tanggal_jatuh_tempo DATE NULL,
                    status ENUM('lunas', 'belum_lunas', 'sebagian') NOT NULL DEFAULT 'belum_lunas',
                    keterangan TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_tagihan_siswa (siswa_id),
                    INDEX idx_tagihan_nisn (nisn),
                    INDEX idx_tagihan_status (status),
                    INDEX idx_tagihan_jenis (jenis_pembayaran)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS pembayaran_riwayat (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tagihan_id INT NOT NULL,
                    siswa_id INT NOT NULL,
                    nomor_transaksi VARCHAR(50) NOT NULL UNIQUE,
                    nominal_bayar DECIMAL(12,2) NOT NULL DEFAULT 0,
                    tanggal_bayar DATETIME NOT NULL,
                    metode_pembayaran VARCHAR(50) NOT NULL DEFAULT 'Transfer Bank',
                    channel VARCHAR(50) NULL,
                    status ENUM('berhasil', 'pending', 'batal') NOT NULL DEFAULT 'berhasil',
                    catatan TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_riwayat_tagihan (tagihan_id),
                    INDEX idx_riwayat_siswa (siswa_id),
                    INDEX idx_riwayat_tgl (tanggal_bayar)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS kurikulum (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    kode VARCHAR(30) NOT NULL UNIQUE,
                    nama VARCHAR(150) NOT NULL,
                    tahun_mulai INT NOT NULL,
                    tahun_selesai INT NULL,
                    status ENUM('aktif', 'non-aktif', 'arsip') NOT NULL DEFAULT 'aktif',
                    deskripsi TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS fase (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    kurikulum_id INT NOT NULL,
                    kode VARCHAR(30) NOT NULL,
                    nama VARCHAR(100) NOT NULL,
                    tingkat_kelas VARCHAR(50) NULL,
                    keterangan TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_fase_kur (kurikulum_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS rombel_kurikulum (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    rombel_id INT NOT NULL,
                    tahun_ajaran_id INT NOT NULL,
                    kurikulum_id INT NOT NULL,
                    fase_id INT NULL,
                    status ENUM('aktif', 'selesai', 'non-aktif') NOT NULL DEFAULT 'aktif',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_rk_rombel (rombel_id),
                    INDEX idx_rk_ta (tahun_ajaran_id),
                    INDEX idx_rk_kur (kurikulum_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS kurikulum_mapel (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    kurikulum_id INT NOT NULL,
                    mapel_id INT NOT NULL,
                    fase_id INT NULL,
                    tingkat VARCHAR(20) NULL,
                    jurusan_id INT NULL,
                    kelompok_mapel VARCHAR(50) DEFAULT 'Kejuruan',
                    alokasi_jp INT DEFAULT 2,
                    kkm DECIMAL(5,2) DEFAULT 75.00,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_km_kur (kurikulum_id),
                    INDEX idx_km_mapel (mapel_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS capaian_pembelajaran (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    kurikulum_id INT NOT NULL,
                    mapel_id INT NOT NULL,
                    fase_id INT NULL,
                    kode_cp VARCHAR(50) NOT NULL,
                    elemen VARCHAR(150) NULL,
                    deskripsi TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_cp_kur (kurikulum_id),
                    INDEX idx_cp_mapel (mapel_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS tujuan_pembelajaran (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    cp_id INT NOT NULL,
                    kode_tp VARCHAR(50) NOT NULL,
                    materi_pokok VARCHAR(255) NULL,
                    deskripsi TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_tp_cp (cp_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS komponen_penilaian (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    kurikulum_id INT NOT NULL,
                    nama_komponen VARCHAR(100) NOT NULL,
                    kode_komponen VARCHAR(50) NOT NULL,
                    bobot_persen DECIMAL(5,2) NOT NULL DEFAULT 25.00,
                    is_active TINYINT(1) DEFAULT 1,
                    deskripsi VARCHAR(255) NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_kp_kur (kurikulum_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS asesmen (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tahun_ajaran_id INT NOT NULL,
                    semester VARCHAR(20) NOT NULL DEFAULT 'Ganjil',
                    rombel_id INT NOT NULL,
                    mapel_id INT NOT NULL,
                    kurikulum_id INT NOT NULL,
                    guru_id INT NOT NULL,
                    cp_id INT NULL,
                    tp_id INT NULL,
                    jenis_asesmen VARCHAR(50) NOT NULL DEFAULT 'formatif',
                    nama_asesmen VARCHAR(150) NOT NULL,
                    tanggal DATE NOT NULL,
                    nilai_maksimum DECIMAL(5,2) DEFAULT 100.00,
                    bobot DECIMAL(5,2) DEFAULT 1.00,
                    keterangan TEXT NULL,
                    ref_tugas_id INT NULL,
                    ref_quiz_id INT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_asm_ta (tahun_ajaran_id),
                    INDEX idx_asm_rombel (rombel_id),
                    INDEX idx_asm_mapel (mapel_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS nilai_asesmen_siswa (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    asesmen_id INT NOT NULL,
                    siswa_id INT NOT NULL,
                    nilai DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                    catatan TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_nas_asm (asesmen_id),
                    INDEX idx_nas_siswa (siswa_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS rapor_siswa (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    siswa_id INT NOT NULL,
                    tahun_ajaran_id INT NOT NULL,
                    semester VARCHAR(20) NOT NULL DEFAULT 'Ganjil',
                    rombel_id INT NOT NULL,
                    kurikulum_id INT NOT NULL,
                    fase_id INT NULL,
                    snapshot_kurikulum_nama VARCHAR(150) NOT NULL,
                    snapshot_kurikulum_kode VARCHAR(30) NOT NULL,
                    snapshot_fase_kode VARCHAR(30) NULL,
                    snapshot_fase_nama VARCHAR(100) NULL,
                    status_kenaikan VARCHAR(50) NULL,
                    catatan_akademik TEXT NULL,
                    catatan_wali_kelas TEXT NULL,
                    catatan_industri TEXT NULL,
                    sakit INT DEFAULT 0,
                    izin INT DEFAULT 0,
                    tanpa_keterangan INT DEFAULT 0,
                    tanggal_terbit DATE NULL,
                    is_published TINYINT(1) DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_rs_siswa (siswa_id),
                    INDEX idx_rs_ta (tahun_ajaran_id),
                    INDEX idx_rs_rombel (rombel_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS rapor_nilai_detail (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    rapor_siswa_id INT NOT NULL,
                    mapel_id INT NOT NULL,
                    snapshot_mapel_nama VARCHAR(150) NOT NULL,
                    snapshot_kelompok VARCHAR(50) NULL,
                    nilai_akhir DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                    capaian_tertinggi TEXT NULL,
                    capaian_terendah TEXT NULL,
                    predikat VARCHAR(10) NULL,
                    deskripsi_kemajuan TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_rnd_rapor (rapor_siswa_id),
                    INDEX idx_rnd_mapel (mapel_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Seed default curriculum if empty
            $countKur = (int)self::$conn->query("SELECT COUNT(*) FROM kurikulum")->fetchColumn();
            if ($countKur === 0) {
                self::$conn->exec("
                    INSERT INTO kurikulum (id, kode, nama, tahun_mulai, status, deskripsi)
                    VALUES (1, 'KMDK', 'Kurikulum Merdeka SMK', 2024, 'aktif', 'Kurikulum Merdeka berorientasi kompetensi keahlian dan pembelajaran kontekstual.');

                    INSERT IGNORE INTO fase (id, kurikulum_id, kode, nama, tingkat_kelas, keterangan)
                    VALUES 
                    (1, 1, 'E', 'Fase E (Kelas X)', 'X', 'Fase dasar kejuruan dan umum kelas X SMK'),
                    (2, 1, 'F', 'Fase F (Kelas XI & XII)', 'XI,XII', 'Fase konsentrasi keahlian kejuruan kelas XI & XII SMK');

                    INSERT IGNORE INTO komponen_penilaian (kurikulum_id, nama_komponen, kode_komponen, bobot_persen, is_active, deskripsi)
                    VALUES 
                    (1, 'Tugas Mandiri / Terstruktur', 'tugas', 20.00, 1, 'Penugasan portofolio KBM harian siswa.'),
                    (1, 'Kuis / Formatif Harian', 'quiz', 20.00, 1, 'Evaluasi formatif pemahaman tujuan pembelajaran.'),
                    (1, 'Sumatif Tengah Semester (STS)', 'uts', 30.00, 1, 'Ujian evaluasi capaian tengah semester.'),
                    (1, 'Sumatif Akhir Semester (SAS)', 'uas', 30.00, 1, 'Ujian akhir evaluasi kompetensi semester.');
                ");
            }
        } catch (\Throwable $e) {
            // Silently ignore if table already exists or DDL restricted
        }
    }
}
