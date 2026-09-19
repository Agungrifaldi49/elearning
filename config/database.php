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
            "idx_chat_sender_receiver" => "ALTER TABLE chat ADD INDEX idx_chat_sender_receiver (sender_id, receiver_id, is_read)",
            "idx_nr_siswa_mapel" => "ALTER TABLE nilai_rapor ADD INDEX idx_nr_siswa_mapel (siswa_id, mapel_id)",
            "idx_nr_mapel_siswa" => "ALTER TABLE nilai_rapor ADD INDEX idx_nr_mapel_siswa (mapel_id, siswa_id)",
            "idx_siswa_kelas" => "ALTER TABLE siswa ADD INDEX idx_siswa_kelas (kelas_id)",
            "idx_jadwal_guru_kelas" => "ALTER TABLE jadwal ADD INDEX idx_jadwal_guru_kelas (guru_id, kelas_id)",
            "idx_materi_guru_kelas" => "ALTER TABLE materi ADD INDEX idx_materi_guru_kelas (guru_id, kelas_id)",
            "idx_tugas_guru_kelas" => "ALTER TABLE tugas ADD INDEX idx_tugas_guru_kelas (guru_id, kelas_id)",
            "idx_quiz_guru_kelas" => "ALTER TABLE quiz ADD INDEX idx_quiz_guru_kelas (guru_id, kelas_id)",
            "idx_sme_guru_siswa" => "ALTER TABLE siswa_mapel_enrollment ADD INDEX idx_sme_guru_siswa (guru_id, siswa_id)"
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
                    guru_id INT NULL,
                    kode_cp VARCHAR(50) NOT NULL,
                    elemen VARCHAR(150) NULL,
                    deskripsi TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_cp_kur (kurikulum_id),
                    INDEX idx_cp_mapel (mapel_id),
                    INDEX idx_cp_guru (guru_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS tujuan_pembelajaran (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    cp_id INT NOT NULL,
                    guru_id INT NULL,
                    kode_tp VARCHAR(50) NOT NULL,
                    materi_pokok VARCHAR(255) NULL,
                    deskripsi TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_tp_cp (cp_id),
                    INDEX idx_tp_guru (guru_id)
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
                    kurikulum_nama_snapshot VARCHAR(150) NULL,
                    fase_nama_snapshot VARCHAR(100) NULL,
                    tanggal_cetak DATE NULL,
                    status ENUM('draft', 'terverifikasi', 'final') NOT NULL DEFAULT 'terverifikasi',
                    catatan_akademik TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_rs_siswa (siswa_id),
                    INDEX idx_rs_ta (tahun_ajaran_id),
                    INDEX idx_rs_rombel (rombel_id),
                    UNIQUE KEY u_rapor_siswa_ta_sem (siswa_id, tahun_ajaran_id, semester)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS rapor_nilai_detail (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    rapor_id INT NOT NULL,
                    mapel_id INT NOT NULL,
                    mapel_nama_snapshot VARCHAR(150) NULL,
                    nilai_akhir DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                    kkm DECIMAL(5,2) DEFAULT 75.00,
                    predikat VARCHAR(10) DEFAULT 'B',
                    capaian_kompetensi TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_rnd_rapor (rapor_id),
                    INDEX idx_rnd_mapel (mapel_id),
                    UNIQUE KEY u_rapor_mapel (rapor_id, mapel_id)
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
            
            // Ensure guru_id column exists on existing installations
            // Ensure capaian_pembelajaran columns exist on existing installations
            $colsCpAll = self::$conn->query("SHOW COLUMNS FROM capaian_pembelajaran")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('guru_id', $colsCpAll)) {
                self::$conn->exec("ALTER TABLE capaian_pembelajaran ADD COLUMN guru_id INT NULL AFTER fase_id, ADD INDEX idx_cp_guru (guru_id)");
            }
            if (!in_array('status', $colsCpAll)) {
                self::$conn->exec("ALTER TABLE capaian_pembelajaran ADD COLUMN status ENUM('aktif', 'arsip') NOT NULL DEFAULT 'aktif' AFTER deskripsi, ADD INDEX idx_cp_status (status)");
            }

            // Ensure tujuan_pembelajaran columns exist on existing installations
            $colsTpAll = self::$conn->query("SHOW COLUMNS FROM tujuan_pembelajaran")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('guru_id', $colsTpAll)) {
                self::$conn->exec("ALTER TABLE tujuan_pembelajaran ADD COLUMN guru_id INT NULL AFTER cp_id, ADD INDEX idx_tp_guru (guru_id)");
            }
            if (!in_array('urutan', $colsTpAll)) {
                self::$conn->exec("ALTER TABLE tujuan_pembelajaran ADD COLUMN urutan INT NOT NULL DEFAULT 1 AFTER deskripsi");
            }
            if (!in_array('status', $colsTpAll)) {
                self::$conn->exec("ALTER TABLE tujuan_pembelajaran ADD COLUMN status ENUM('aktif', 'arsip') NOT NULL DEFAULT 'aktif' AFTER urutan, ADD INDEX idx_tp_status (status)");
            }
            if (!in_array('tahun_ajaran_id', $colsTpAll)) {
                self::$conn->exec("ALTER TABLE tujuan_pembelajaran ADD COLUMN tahun_ajaran_id INT NULL AFTER status, ADD INDEX idx_tp_ta (tahun_ajaran_id)");
            }

            // Ensure rapor_siswa snapshot columns exist (self-healing migration for existing databases)
            $colsRapor = self::$conn->query("SHOW COLUMNS FROM rapor_siswa")->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($colsRapor)) {
                if (!in_array('kurikulum_nama_snapshot', $colsRapor)) {
                    self::$conn->exec("ALTER TABLE rapor_siswa ADD COLUMN kurikulum_nama_snapshot VARCHAR(150) NULL AFTER fase_id");
                    if (in_array('snapshot_kurikulum_nama', $colsRapor)) {
                        self::$conn->exec("UPDATE rapor_siswa SET kurikulum_nama_snapshot = snapshot_kurikulum_nama WHERE kurikulum_nama_snapshot IS NULL");
                    }
                }
                if (!in_array('fase_nama_snapshot', $colsRapor)) {
                    self::$conn->exec("ALTER TABLE rapor_siswa ADD COLUMN fase_nama_snapshot VARCHAR(100) NULL AFTER kurikulum_nama_snapshot");
                    if (in_array('snapshot_fase_nama', $colsRapor)) {
                        self::$conn->exec("UPDATE rapor_siswa SET fase_nama_snapshot = snapshot_fase_nama WHERE fase_nama_snapshot IS NULL");
                    }
                }
                if (!in_array('tanggal_cetak', $colsRapor)) {
                    self::$conn->exec("ALTER TABLE rapor_siswa ADD COLUMN tanggal_cetak DATE NULL AFTER fase_nama_snapshot");
                    if (in_array('tanggal_terbit', $colsRapor)) {
                        self::$conn->exec("UPDATE rapor_siswa SET tanggal_cetak = tanggal_terbit WHERE tanggal_cetak IS NULL");
                    }
                }
                if (!in_array('status', $colsRapor)) {
                    self::$conn->exec("ALTER TABLE rapor_siswa ADD COLUMN status ENUM('draft', 'terverifikasi', 'final') NOT NULL DEFAULT 'terverifikasi' AFTER tanggal_cetak");
                }
                if (!in_array('catatan_akademik', $colsRapor)) {
                    self::$conn->exec("ALTER TABLE rapor_siswa ADD COLUMN catatan_akademik TEXT NULL AFTER status");
                }
            }

            // Ensure rapor_nilai_detail columns exist (self-healing migration for existing databases)
            $colsRnd = self::$conn->query("SHOW COLUMNS FROM rapor_nilai_detail")->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($colsRnd)) {
                if (!in_array('rapor_id', $colsRnd)) {
                    self::$conn->exec("ALTER TABLE rapor_nilai_detail ADD COLUMN rapor_id INT NOT NULL DEFAULT 0 AFTER id, ADD INDEX idx_rnd_rapor_id (rapor_id)");
                    if (in_array('rapor_siswa_id', $colsRnd)) {
                        self::$conn->exec("UPDATE rapor_nilai_detail SET rapor_id = rapor_siswa_id WHERE rapor_id = 0");
                    }
                }
                if (!in_array('mapel_nama_snapshot', $colsRnd)) {
                    self::$conn->exec("ALTER TABLE rapor_nilai_detail ADD COLUMN mapel_nama_snapshot VARCHAR(150) NULL AFTER mapel_id");
                    if (in_array('snapshot_mapel_nama', $colsRnd)) {
                        self::$conn->exec("UPDATE rapor_nilai_detail SET mapel_nama_snapshot = snapshot_mapel_nama WHERE mapel_nama_snapshot IS NULL");
                    }
                }
                if (!in_array('kkm', $colsRnd)) {
                    self::$conn->exec("ALTER TABLE rapor_nilai_detail ADD COLUMN kkm DECIMAL(5,2) DEFAULT 75.00 AFTER nilai_akhir");
                }
                if (!in_array('predikat', $colsRnd)) {
                    self::$conn->exec("ALTER TABLE rapor_nilai_detail ADD COLUMN predikat VARCHAR(10) DEFAULT 'B' AFTER kkm");
                }
                if (!in_array('capaian_kompetensi', $colsRnd)) {
                    self::$conn->exec("ALTER TABLE rapor_nilai_detail ADD COLUMN capaian_kompetensi TEXT NULL AFTER predikat");
                    if (in_array('deskripsi_kemajuan', $colsRnd)) {
                        self::$conn->exec("UPDATE rapor_nilai_detail SET capaian_kompetensi = deskripsi_kemajuan WHERE capaian_kompetensi IS NULL");
                    }
                }
            }

            // Ensure KKTP & Multi-TP Assessment Tables
            self::$conn->exec("
                CREATE TABLE IF NOT EXISTS `kktp` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `tp_id` INT NOT NULL,
                    `metode` ENUM('interval_nilai', 'rubrik', 'checklist') NOT NULL DEFAULT 'interval_nilai',
                    `nilai_minimum` DECIMAL(5,2) DEFAULT 75.00,
                    `target_indikator_count` INT DEFAULT 0,
                    `deskripsi_kriteria` TEXT NULL,
                    `versi` INT DEFAULT 1,
                    `status` ENUM('aktif', 'arsip') NOT NULL DEFAULT 'aktif',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_kktp_tp` (`tp_id`),
                    INDEX `idx_kktp_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            self::$conn->exec("
                CREATE TABLE IF NOT EXISTS `kktp_indikator` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `kktp_id` INT NOT NULL,
                    `nama_indikator` VARCHAR(255) NOT NULL,
                    `deskripsi_kriteria` TEXT NULL,
                    `bobot` DECIMAL(5,2) DEFAULT 1.00,
                    `urutan` INT DEFAULT 1,
                    INDEX `idx_kktp_ind_kktp` (`kktp_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            self::$conn->exec("
                CREATE TABLE IF NOT EXISTS `asesmen_tp` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `asesmen_id` INT NOT NULL,
                    `tp_id` INT NOT NULL,
                    `kktp_id` INT NULL,
                    `bobot_tp` DECIMAL(5,2) DEFAULT 100.00,
                    `nilai_maksimum` DECIMAL(5,2) DEFAULT 100.00,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_atp_asesmen` (`asesmen_id`),
                    INDEX `idx_atp_tp` (`tp_id`),
                    INDEX `idx_atp_kktp` (`kktp_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            self::$conn->exec("
                CREATE TABLE IF NOT EXISTS `nilai_asesmen_tp` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `asesmen_id` INT NOT NULL,
                    `asesmen_tp_id` INT NULL,
                    `tp_id` INT NOT NULL,
                    `siswa_id` INT NOT NULL,
                    `kktp_id` INT NULL,
                    `nilai_asli` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                    `nilai_maksimum` DECIMAL(5,2) NOT NULL DEFAULT 100.00,
                    `status_code` TINYINT(1) NOT NULL DEFAULT 0,
                    `status_ketercapaian` ENUM('Tercapai', 'Belum Tercapai') NOT NULL DEFAULT 'Belum Tercapai',
                    `indikator_tercapai_ids` TEXT NULL,
                    `is_remedial` TINYINT(1) NOT NULL DEFAULT 0,
                    `nilai_awal` DECIMAL(5,2) NULL,
                    `catatan` TEXT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_natp_asesmen` (`asesmen_id`),
                    INDEX `idx_natp_tp` (`tp_id`),
                    INDEX `idx_natp_siswa` (`siswa_id`),
                    INDEX `idx_natp_kktp` (`kktp_id`),
                    INDEX `idx_natp_status` (`status_code`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (\Throwable $e) {
            // Silently ignore if table already exists or DDL restricted
        }
    }
}
