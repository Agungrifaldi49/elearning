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
            WHERE u.role_id = 2
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
            WHERE g.user_id = ? AND u.role_id = 2
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function ensureGuruProfile($userId, $fullName) {
        if (!$userId) {
            return null;
        }

        // STRICT ROLE CHECK: Only users with role_id = 2 (Guru) are allowed to have a teacher profile
        $stmtUser = $this->db->prepare("SELECT id, role_id, full_name FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch();
        if (!$user || (int)$user['role_id'] !== 2) {
            // Non-teachers (Admin, Siswa, Kepsek) MUST NEVER be inserted into guru table!
            return null;
        }

        $fullName = !empty($fullName) ? $fullName : ($user['full_name'] ?? 'Guru');
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

        return $this->getByUserId($userId);
    }

    public function addGuru($data) {
        $this->db->beginTransaction();
        try {
            // Create user account with explicit role_id = 2 (Guru)
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
        $stmt = $this->db->prepare("SELECT g.user_id, u.role_id FROM guru g LEFT JOIN users u ON g.user_id = u.id WHERE g.id = ?");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();

        if ($row) {
            $userId = (int)$row['user_id'];
            $roleId = (int)($row['role_id'] ?? 0);

            $this->db->prepare("DELETE FROM guru WHERE id = ?")->execute([(int)$id]);

            // Only delete user account if role is Guru (role_id = 2). NEVER delete Admin (1)!
            if ($userId > 0 && $roleId === 2) {
                $stmtDel = $this->db->prepare("DELETE FROM users WHERE id = ?");
                $stmtDel->execute([$userId]);
            }
            return true;
        }
        return false;
    }

    public function bulkDeleteGuru($guruIds) {
        if (empty($guruIds) || !is_array($guruIds)) return 0;
        $ids = array_map('intval', $guruIds);
        $inClause = implode(',', $ids);
        
        $this->db->beginTransaction();
        try {
            // Find guru users that actually have role_id = 2
            $stmtUserIds = $this->db->query("
                SELECT g.user_id 
                FROM guru g 
                JOIN users u ON g.user_id = u.id 
                WHERE g.id IN ({$inClause}) AND u.role_id = 2
            ");
            $uIds = $stmtUserIds ? $stmtUserIds->fetchAll(PDO::FETCH_COLUMN) : [];
            
            $this->db->exec("DELETE FROM guru WHERE id IN ({$inClause})");

            // Only delete users with role_id = 2. NEVER delete Admin (1)!
            if (!empty($uIds)) {
                $uIn = implode(',', array_map('intval', $uIds));
                $this->db->exec("DELETE FROM users WHERE id IN ({$uIn}) AND role_id = 2");
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
