<?php
/**
 * Database Configuration & PDO Singleton Connection
 * E-Learning SMK Muthia Harapan Cicalengka
 */

class Database {
    private static $host = 'localhost';
    private static $db_name = 'db_elearning_smkmh';
    private static $username = 'root';
    private static $password = '';
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn === null) {
            try {
                // First try connecting to specific DB
                self::$conn = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4",
                    self::$username,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                // If database doesn't exist, try connecting to MySQL root to auto-create
                try {
                    $pdo = new PDO("mysql:host=" . self::$host . ";charset=utf8mb4", self::$username, self::$password);
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . self::$db_name . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                    
                    self::$conn = new PDO(
                        "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4",
                        self::$username,
                        self::$password,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false,
                        ]
                    );

                    // Import schema & seeders automatically
                    self::autoImport();

                } catch (PDOException $ex) {
                    die("<div style='font-family:sans-serif; padding:20px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:8px;'>
                        <h3>Database Connection Error</h3>
                        <p>" . htmlspecialchars($ex->getMessage()) . "</p>
                        <p>Pastikan MySQL server di XAMPP telah dinyalakan dan database <code>" . self::$db_name . "</code> sudah disiapkannya.</p>
                    </div>");
                }
            }
        }
        return self::$conn;
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
}
