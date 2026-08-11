<?php
/**
 * User Model
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class UserModel extends BaseModel {

    public function findByUsername($username) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.username = ? OR u.email = ?
            LIMIT 1
        ");
        $stmt->execute([$username, $username]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function logLoginAttempt($userId, $username, $status) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $stmt = $this->db->prepare("INSERT INTO log_login (user_id, username, status, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $username, $status, $ip, $agent]);
    }

    public function countFailedLogins($username) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM log_login 
            WHERE username = ? AND status = 'failed' 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        $stmt->execute([$username]);
        return (int)$stmt->fetchColumn();
    }

    public function getAllUsers() {
        $stmt = $this->db->query("
            SELECT u.*, r.name as role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            ORDER BY u.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function createUser($data) {
        $stmt = $this->db->prepare("
            INSERT INTO users (role_id, username, email, password, full_name, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt->execute([
            $data['role_id'],
            $data['username'],
            $data['email'],
            $hash,
            $data['full_name'],
            $data['status'] ?? 'active'
        ]);
        return $this->db->lastInsertId();
    }

    public function updateUser($id, $data) {
        if (!empty($data['password'])) {
            $hash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("
                UPDATE users SET full_name = ?, email = ?, password = ?, status = ? WHERE id = ?
            ");
            return $stmt->execute([$data['full_name'], $data['email'], $hash, $data['status'], $id]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE users SET full_name = ?, email = ?, status = ? WHERE id = ?
            ");
            return $stmt->execute([$data['full_name'], $data['email'], $data['status'], $id]);
        }
    }

    public function updateAvatar($userId, $avatarName) {
        $stmt = $this->db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        return $stmt->execute([$avatarName, $userId]);
    }

    public function updateProfileFull($userId, $role, $data, $file = null) {
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $avatarName = null;
            if (!empty($data['cropped_base64']) && preg_match('/^data:image\/(\w+);base64,/', $data['cropped_base64'], $type)) {
                $imgData = substr($data['cropped_base64'], strpos($data['cropped_base64'], ',') + 1);
                $imgData = base64_decode($imgData);
                if ($imgData !== false) {
                    $ext = strtolower($type[1]);
                    if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
                        $ext = 'png';
                    }
                    $newFileName = 'profile_' . time() . '_' . uniqid() . '.' . $ext;
                    $targetDir = ROOT_PATH . 'assets/uploads/profile/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    file_put_contents($targetDir . $newFileName, $imgData);
                    $avatarName = $newFileName;
                }
            } elseif ($file && is_array($file) && ($file['error'] ?? -1) === UPLOAD_ERR_OK) {
                require_once ROOT_PATH . 'helpers/UploadHelper.php';
                $uploaded = UploadHelper::upload($file, 'profile');
                if ($uploaded) {
                    $avatarName = $uploaded;
                }
            }

            $sqlUser = "UPDATE users SET full_name = ?, email = ?";
            $paramsUser = [$data['full_name'], $data['email']];

            if ($avatarName) {
                $sqlUser .= ", avatar = ?";
                $paramsUser[] = $avatarName;
            }

            if (!empty($data['password'])) {
                $hash = password_hash($data['password'], PASSWORD_BCRYPT);
                $sqlUser .= ", password = ?";
                $paramsUser[] = $hash;
            }

            $sqlUser .= " WHERE id = ?";
            $paramsUser[] = $userId;

            $stmtU = $db->prepare($sqlUser);
            $stmtU->execute($paramsUser);

            if ($role === 'guru') {
                $stmtG = $db->prepare("
                    UPDATE guru 
                    SET nama_lengkap = ?, no_telepon = ?, alamat = ?, jenis_kelamin = ? 
                    WHERE user_id = ?
                ");
                $stmtG->execute([
                    $data['full_name'],
                    $data['no_telepon'] ?? '',
                    $data['alamat'] ?? '',
                    $data['jenis_kelamin'] ?? 'L',
                    $userId
                ]);
            } elseif ($role === 'siswa') {
                $stmtS = $db->prepare("
                    UPDATE siswa 
                    SET nama_lengkap = ?, no_telepon = ?, alamat = ?, jenis_kelamin = ? 
                    WHERE user_id = ?
                ");
                $stmtS->execute([
                    $data['full_name'],
                    $data['no_telepon'] ?? '',
                    $data['alamat'] ?? '',
                    $data['jenis_kelamin'] ?? 'L',
                    $userId
                ]);
            }

            $db->commit();

            $_SESSION['full_name'] = $data['full_name'];
            $_SESSION['email'] = $data['email'];
            if ($avatarName) {
                $_SESSION['avatar'] = $avatarName;
            }

            return ['status' => true, 'avatar' => $avatarName];
        } catch (Exception $e) {
            $db->rollBack();
            return ['status' => false, 'message' => 'Gagal memperbarui profil: ' . $e->getMessage()];
        }
    }

    public function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function verifyUserForReset($username, $email) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE LOWER(TRIM(u.username)) = LOWER(TRIM(?)) 
              AND LOWER(TRIM(u.email)) = LOWER(TRIM(?))
              AND u.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$username, $email]);
        return $stmt->fetch();
    }

    public function resetUserPassword($userId, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hash, (int)$userId]);
    }
}
