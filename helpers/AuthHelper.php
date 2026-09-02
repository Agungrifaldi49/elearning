<?php
/**
 * Auth Helper
 * Session Management & Role Based Access Control
 */

require_once ROOT_PATH . 'config/database.php';

class AuthHelper {

    private static $cachedUser = null;

    /**
     * Check if user is logged in
     */
    public static function check() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // Check Session Timeout (Inactivity)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            self::logout();
            $_SESSION['flash_error'] = 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan login kembali.';
            return false;
        }

        $_SESSION['last_activity'] = time(); // Update activity time
        return true;
    }

    /**
     * Get Current Logged In User with static memory caching
     */
    /**
     * Get Current Logged In User with static memory caching
     */
    public static function user() {
        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        if (!self::check()) {
            return null;
        }

        if (isset($_SESSION['user_id'])) {
            // Periodic sync every 15 minutes or if session missing role_name
            $needSync = !isset($_SESSION['role_name']) || !isset($_SESSION['last_sync']) || (time() - $_SESSION['last_sync'] > 900);
            if ($needSync) {
                try {
                    $db = Database::getConnection();
                    if ($db) {
                        $stmtSync = $db->prepare("
                            SELECT u.full_name, u.email, u.avatar, u.role_id, r.name as role_name 
                            FROM users u 
                            LEFT JOIN roles r ON u.role_id = r.id 
                            WHERE u.id = ?
                        ");
                        $stmtSync->execute([$_SESSION['user_id']]);
                        $uRow = $stmtSync->fetch();
                        if ($uRow) {
                            $_SESSION['full_name'] = $uRow['full_name'];
                            $_SESSION['email'] = $uRow['email'];
                            $_SESSION['avatar'] = $uRow['avatar'] ?? 'default_avatar.png';
                            $_SESSION['role_id'] = $uRow['role_id'];
                            $_SESSION['role_name'] = $uRow['role_name'];
                            $_SESSION['last_sync'] = time();
                        }
                    }
                } catch (\Throwable $e) {}
            }
        }

        self::$cachedUser = [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? '',
            'full_name' => $_SESSION['full_name'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'role_id' => $_SESSION['role_id'] ?? null,
            'role_name' => $_SESSION['role_name'] ?? '',
            'avatar' => $_SESSION['avatar'] ?? 'default_avatar.png',
            'member_id' => $_SESSION['member_id'] ?? null, // guru_id or siswa_id if applicable
            'kelas_id' => $_SESSION['kelas_id'] ?? null,
            'jurusan_id' => $_SESSION['jurusan_id'] ?? null
        ];

        return self::$cachedUser;
    }

    /**
     * Set User Session after successful login
     */
    public static function login($user) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }

        self::$cachedUser = null; // Reset static cache

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['role_name'] = $user['role_name'];
        $_SESSION['avatar'] = $user['avatar'] ?? 'default_avatar.png';
        $_SESSION['last_activity'] = time();
        $_SESSION['last_sync'] = time();

        // Fetch student or teacher specific IDs if applicable
        try {
            $db = Database::getConnection();
            if ($db) {
                if ($user['role_id'] == 2 || strtolower($user['role_name'] ?? '') === 'guru') { // Guru
                    $stmt = $db->prepare("SELECT id FROM guru WHERE user_id = ?");
                    $stmt->execute([$user['id']]);
                    $guru = $stmt->fetch();
                    $_SESSION['member_id'] = $guru['id'] ?? null;
                } elseif ($user['role_id'] == 3 || strtolower($user['role_name'] ?? '') === 'siswa') { // Siswa
                    $stmt = $db->prepare("SELECT id, kelas_id, jurusan_id FROM siswa WHERE user_id = ?");
                    $stmt->execute([$user['id']]);
                    $siswa = $stmt->fetch();
                    $_SESSION['member_id'] = $siswa['id'] ?? null;
                    $_SESSION['kelas_id'] = $siswa['kelas_id'] ?? null;
                    $_SESSION['jurusan_id'] = $siswa['jurusan_id'] ?? null;
                }
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Require Login Guard
     */
    public static function requireLogin() {
        if (!self::check()) {
            if (headers_sent() === false) {
                header('Location: ' . BASE_URL . 'login.php');
            } else {
                echo "<script>window.location.href='" . BASE_URL . "login.php';</script>";
            }
            exit();
        }
    }

    /**
     * Require Specific Role Guard
     */
    public static function requireRole($allowedRoles = []) {
        self::requireLogin();
        $user = self::user();

        $userRoleLower = strtolower($user['role_name'] ?? '');

        // Alias normalization
        $normalizedUserRole = match($userRoleLower) {
            'admin', 'administrator' => 'administrator',
            'kepsek', 'kepala sekolah' => 'kepala sekolah',
            default => $userRoleLower
        };

        $allowedLower = array_map(function($r) {
            $r = strtolower($r);
            return match($r) {
                'admin', 'administrator' => 'administrator',
                'kepsek', 'kepala sekolah' => 'kepala sekolah',
                default => $r
            };
        }, (array)$allowedRoles);

        if (!in_array($normalizedUserRole, $allowedLower)) {
            $redirectUrl = match($normalizedUserRole) {
                'administrator' => BASE_URL . 'index.php?url=admin/dashboard',
                'guru' => BASE_URL . 'index.php?url=guru/dashboard',
                'siswa' => BASE_URL . 'index.php?url=siswa/dashboard',
                'kepala sekolah' => BASE_URL . 'index.php?url=kepsek/dashboard',
                default => BASE_URL . 'index.php?url=landing'
            };
            
            $currentUrl = $_GET['url'] ?? '';
            if (strpos($redirectUrl, $currentUrl) === false || empty($currentUrl)) {
                if (headers_sent() === false) {
                    header('Location: ' . $redirectUrl);
                } else {
                    echo "<script>window.location.href='" . $redirectUrl . "';</script>";
                }
                exit();
            }
        }
    }

    /**
     * Destroy Session & Logout
     */
    public static function logout() {
        self::$cachedUser = null;
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        $_SESSION = [];
        if (session_id() != '' || headers_sent() === false) {
            @session_destroy();
        }
    }
}
