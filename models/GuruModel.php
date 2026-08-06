<?php
/**
 * Guru Model
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class GuruModel extends BaseModel {

    public function getAll() {
        return $this->db->query("
            SELECT g.*, u.username, u.email, u.avatar 
            FROM guru g 
            JOIN users u ON g.user_id = u.id 
            ORDER BY g.nama_lengkap ASC
        ")->fetchAll();
    }

    public function getGuru() {
        return $this->getAll();
    }

    public function getByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT g.*, u.username, u.email, u.avatar 
            FROM guru g 
            JOIN users u ON g.user_id = u.id 
            WHERE g.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function ensureGuruProfile($userId, $fullName) {
        $guru = $this->getByUserId($userId);
        if ($guru) return $guru;

        try {
            $nip = 'G' . date('Ym') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            $stmt = $this->db->prepare("INSERT INTO guru (user_id, nip, nama_lengkap, jenis_kelamin, status) VALUES (?, ?, ?, 'L', 'aktif')");
            $stmt->execute([$userId, $nip, $fullName]);
            return $this->getByUserId($userId);
        } catch (Exception $e) {
            return ['id' => $userId, 'user_id' => $userId, 'nama_lengkap' => $fullName];
        }
    }

    public function addGuru($data) {
        $this->db->beginTransaction();
        try {
            // Create user account
            $stmtUser = $this->db->prepare("INSERT INTO users (role_id, username, email, password, full_name) VALUES (2, ?, ?, ?, ?)");
            $hash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmtUser->execute([$data['username'], $data['email'], $hash, $data['nama_lengkap']]);
            $userId = $this->db->lastInsertId();

            // Create guru profile
            $stmtGuru = $this->db->prepare("INSERT INTO guru (user_id, nip, nama_lengkap, jenis_kelamin, no_telepon, alamat) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtGuru->execute([$userId, $data['nip'], $data['nama_lengkap'], $data['jenis_kelamin'], $data['no_telepon'], $data['alamat']]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function updateGuru($id, $data) {
        $stmt = $this->db->prepare("SELECT user_id FROM guru WHERE id = ?");
        $stmt->execute([$id]);
        $guru = $stmt->fetch();

        if (!$guru) return false;

        $this->db->beginTransaction();
        try {
            $stmtGuru = $this->db->prepare("UPDATE guru SET nip = ?, nama_lengkap = ?, jenis_kelamin = ?, no_telepon = ?, alamat = ? WHERE id = ?");
            $stmtGuru->execute([$data['nip'], $data['nama_lengkap'], $data['jenis_kelamin'], $data['no_telepon'], $data['alamat'], $id]);

            $stmtUser = $this->db->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
            $stmtUser->execute([$data['nama_lengkap'], $data['email'], $guru['user_id']]);

            if (!empty($data['password'])) {
                $hash = password_hash($data['password'], PASSWORD_BCRYPT);
                $stmtPass = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmtPass->execute([$hash, $guru['user_id']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function deleteGuru($id) {
        $stmt = $this->db->prepare("SELECT user_id FROM guru WHERE id = ?");
        $stmt->execute([$id]);
        $guru = $stmt->fetch();

        if ($guru) {
            $stmtDel = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmtDel->execute([$guru['user_id']]);
        }
        return false;
    }
}
