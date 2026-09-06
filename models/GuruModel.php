<?php
/**
 * Guru Model
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class GuruModel extends BaseModel {

    public function getAll($keyword = null, $jenisKelamin = null, $status = null) {
        $sql = "
            SELECT g.*, u.username, u.email, u.avatar 
            FROM guru g 
            JOIN users u ON g.user_id = u.id 
            WHERE 1=1
        ";
        $params = [];

        if ($jenisKelamin && in_array(strtoupper($jenisKelamin), ['L', 'P'])) {
            $sql .= " AND g.jenis_kelamin = ?";
            $params[] = strtoupper($jenisKelamin);
        }

        if ($status && trim($status) !== '') {
            $sql .= " AND g.status = ?";
            $params[] = trim($status);
        }

        if ($keyword && trim($keyword) !== '') {
            $sql .= " AND (g.nip LIKE ? OR g.nama_lengkap LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR g.no_telepon LIKE ?)";
            $term = '%' . trim($keyword) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY g.nama_lengkap ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
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
        if (!$userId) {
            return ['id' => 0, 'user_id' => 0, 'nama_lengkap' => $fullName];
        }

        $guru = $this->getByUserId($userId);
        if ($guru) return $guru;

        try {
            for ($attempt = 0; $attempt < 5; $attempt++) {
                try {
                    $nip = 'G' . date('Ym') . str_pad(rand(100, 9999), 4, '0', STR_PAD_LEFT);
                    $stmt = $this->db->prepare("INSERT INTO guru (user_id, nip, nama_lengkap, jenis_kelamin, status) VALUES (?, ?, ?, 'L', 'aktif')");
                    $stmt->execute([$userId, $nip, $fullName]);
                    $created = $this->getByUserId($userId);
                    if ($created) return $created;
                } catch (\Throwable $exAttempt) {
                    // Collision retry
                }
            }
        } catch (\Throwable $e) {}

        $fallback = $this->getByUserId($userId);
        return $fallback ?: ['id' => 0, 'user_id' => $userId, 'nama_lengkap' => $fullName];
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

    public function bulkDeleteGuru($guruIds) {
        if (empty($guruIds) || !is_array($guruIds)) return 0;
        $ids = array_map('intval', $guruIds);
        $inClause = implode(',', $ids);
        
        $this->db->beginTransaction();
        try {
            $stmtUserIds = $this->db->query("SELECT user_id FROM guru WHERE id IN ({$inClause})");
            $uIds = $stmtUserIds ? $stmtUserIds->fetchAll(PDO::FETCH_COLUMN) : [];
            
            if (!empty($uIds)) {
                $uIn = implode(',', array_map('intval', $uIds));
                $this->db->exec("DELETE FROM users WHERE id IN ({$uIn})");
            }
            $this->db->commit();
            return count($ids);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return 0;
        }
    }

    public function bulkUpdateStatusGuru($guruIds, $status) {
        if (empty($guruIds) || !is_array($guruIds) || empty($status)) return 0;
        $ids = array_map('intval', $guruIds);
        $inClause = implode(',', $ids);
        $sql = "UPDATE guru SET status = ? WHERE id IN ({$inClause})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([Security::sanitize($status)]);
        return $stmt->rowCount();
    }

    public function bulkUpdateMatrix($matrixData) {
        if (empty($matrixData) || !is_array($matrixData)) return 0;
        $count = 0;
        $this->db->beginTransaction();
        try {
            $stmtGuru = $this->db->prepare("UPDATE guru SET nip = ?, nama_lengkap = ?, jenis_kelamin = ?, no_telepon = ?, status = ? WHERE id = ?");
            $stmtUser = $this->db->prepare("UPDATE users u JOIN guru g ON u.id = g.user_id SET u.full_name = ?, u.email = ? WHERE g.id = ?");

            foreach ($matrixData as $id => $row) {
                $gId = (int)$id;
                if ($gId <= 0) continue;
                $nip = Security::sanitize($row['nip'] ?? '');
                $nama = Security::sanitize($row['nama_lengkap'] ?? '');
                $email = Security::sanitize($row['email'] ?? '');
                $telepon = Security::sanitize($row['no_telepon'] ?? '');
                $jk = in_array(strtoupper($row['jenis_kelamin'] ?? 'L'), ['L', 'P']) ? strtoupper($row['jenis_kelamin']) : 'L';
                $status = in_array(strtolower($row['status'] ?? 'aktif'), ['aktif', 'nonaktif']) ? strtolower($row['status']) : 'aktif';

                if (!empty($nama) && !empty($nip)) {
                    $stmtGuru->execute([$nip, $nama, $jk, $telepon, $status, $gId]);
                    if (!empty($email)) {
                        $stmtUser->execute([$nama, $email, $gId]);
                    } else {
                        $stmtUser2 = $this->db->prepare("UPDATE users u JOIN guru g ON u.id = g.user_id SET u.full_name = ? WHERE g.id = ?");
                        $stmtUser2->execute([$nama, $gId]);
                    }
                    $count++;
                }
            }
            $this->db->commit();
            return $count;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return 0;
        }
    }
}
