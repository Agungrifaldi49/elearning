<?php
/**
 * Siswa Model
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class SiswaModel extends BaseModel {

    public function getAll($kelasId = null, $jurusanId = null, $keyword = null) {
        $sql = "
            SELECT s.*, k.nama_kelas, j.nama_jurusan, u.username, u.email, u.avatar 
            FROM siswa s 
            JOIN users u ON s.user_id = u.id 
            JOIN kelas k ON s.kelas_id = k.id 
            JOIN jurusan j ON s.jurusan_id = j.id 
            WHERE 1=1
        ";
        $params = [];

        if ($kelasId && (int)$kelasId > 0) {
            $sql .= " AND s.kelas_id = ?";
            $params[] = (int)$kelasId;
        }

        if ($jurusanId && (int)$jurusanId > 0) {
            $sql .= " AND s.jurusan_id = ?";
            $params[] = (int)$jurusanId;
        }

        if ($keyword && trim($keyword) !== '') {
            $sql .= " AND (s.nisn LIKE ? OR s.nis LIKE ? OR s.nama_lengkap LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
            $term = '%' . trim($keyword) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY s.nama_lengkap ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT s.*, k.nama_kelas, j.nama_jurusan, u.username, u.email, u.avatar 
            FROM siswa s 
            JOIN users u ON s.user_id = u.id 
            LEFT JOIN kelas k ON s.kelas_id = k.id 
            LEFT JOIN jurusan j ON s.jurusan_id = j.id 
            WHERE s.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function ensureSiswaProfile($userId, $fullName) {
        $siswa = $this->getByUserId($userId);
        if ($siswa) return $siswa;

        // Check if user is actually a Siswa (role_id = 3) before creating profile
        $stmtRole = $this->db->prepare("SELECT role_id FROM users WHERE id = ?");
        $stmtRole->execute([$userId]);
        $uRow = $stmtRole->fetch();
        $roleId = (int)($uRow['role_id'] ?? 0);

        if ($roleId !== 3) {
            return null; // Do not auto-create student profile for teacher/admin accounts
        }

        try {
            $kStmt = $this->db->query("SELECT id, jurusan_id FROM kelas ORDER BY id ASC LIMIT 1");
            $kRow = $kStmt ? $kStmt->fetch() : null;
            $kelasId = $kRow['id'] ?? 1;
            $jurusanId = $kRow['jurusan_id'] ?? 1;

            $nis = 'S' . date('Ym') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            $stmt = $this->db->prepare("INSERT INTO siswa (user_id, nis, nisn, nama_lengkap, kelas_id, jurusan_id, jenis_kelamin) VALUES (?, ?, ?, ?, ?, ?, 'L')");
            $stmt->execute([$userId, $nis, $nis, $fullName, $kelasId, $jurusanId]);
            return $this->getByUserId($userId);
        } catch (Exception $e) {
            return ['id' => $userId, 'user_id' => $userId, 'nama_lengkap' => $fullName, 'kelas_id' => 1, 'jurusan_id' => 1];
        }
    }

    public function addSiswa($data) {
        $this->db->beginTransaction();
        try {
            // Create user account
            $stmtUser = $this->db->prepare("INSERT INTO users (role_id, username, email, password, full_name) VALUES (3, ?, ?, ?, ?)");
            $hash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmtUser->execute([$data['username'], $data['email'], $hash, $data['nama_lengkap']]);
            $userId = $this->db->lastInsertId();

            // Create siswa profile
            $stmtSiswa = $this->db->prepare("INSERT INTO siswa (user_id, nis, nisn, nama_lengkap, kelas_id, jurusan_id, jenis_kelamin, no_telepon, alamat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtSiswa->execute([$userId, $data['nis'], $data['nisn'], $data['nama_lengkap'], $data['kelas_id'], $data['jurusan_id'], $data['jenis_kelamin'], $data['no_telepon'], $data['alamat']]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function updateSiswa($id, $data) {
        $stmt = $this->db->prepare("SELECT user_id FROM siswa WHERE id = ?");
        $stmt->execute([$id]);
        $siswa = $stmt->fetch();

        if (!$siswa) return false;

        $this->db->beginTransaction();
        try {
            $stmtSiswa = $this->db->prepare("UPDATE siswa SET nis = ?, nisn = ?, nama_lengkap = ?, kelas_id = ?, jurusan_id = ?, jenis_kelamin = ?, no_telepon = ? WHERE id = ?");
            $stmtSiswa->execute([$data['nis'], $data['nisn'], $data['nama_lengkap'], $data['kelas_id'], $data['jurusan_id'], $data['jenis_kelamin'], $data['no_telepon'], $id]);

            $stmtUser = $this->db->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
            $stmtUser->execute([$data['nama_lengkap'], $data['email'], $siswa['user_id']]);

            if (!empty($data['password'])) {
                $hash = password_hash($data['password'], PASSWORD_BCRYPT);
                $stmtPass = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmtPass->execute([$hash, $siswa['user_id']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function deleteSiswa($id) {
        $stmt = $this->db->prepare("SELECT user_id FROM siswa WHERE id = ?");
        $stmt->execute([$id]);
        $siswa = $stmt->fetch();

        if ($siswa) {
            $stmtDel = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmtDel->execute([$siswa['user_id']]);
        }
        return false;
    }

    public function getSiswaCertificateRealStats($siswaId) {
        $siswaId = (int)$siswaId;
        
        // 1. Presensi Log Rate
        $stmtAtt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_absensi,
                COUNT(CASE WHEN LOWER(status) = 'hadir' THEN 1 END) as total_hadir
            FROM absensi 
            WHERE siswa_id = ?
        ");
        $stmtAtt->execute([$siswaId]);
        $attData = $stmtAtt->fetch();

        $presensiStr = "Belum Ada Data";
        if ($attData && (int)$attData['total_absensi'] > 0) {
            $rate = round(((int)$attData['total_hadir'] / (int)$attData['total_absensi']) * 100);
            $presensiStr = $rate . "%";
        }

        // 2. Scores from nilai_rapor, hasil_quiz, pengumpulan_tugas
        $scores = [];

        try {
            $stmtRapor = $this->db->prepare("SELECT AVG(nilai_akhir) as avg_rapor, COUNT(nilai_akhir) as cnt_rapor FROM nilai_rapor WHERE siswa_id = ? AND nilai_akhir IS NOT NULL");
            $stmtRapor->execute([$siswaId]);
            $rData = $stmtRapor->fetch();
            if ($rData && (int)$rData['cnt_rapor'] > 0 && $rData['avg_rapor'] !== null) {
                $scores[] = (float)$rData['avg_rapor'];
            }
        } catch (Exception $e) {}

        try {
            $stmtQuiz = $this->db->prepare("SELECT AVG(total_nilai) as avg_quiz, COUNT(total_nilai) as cnt_quiz FROM hasil_quiz WHERE siswa_id = ? AND total_nilai IS NOT NULL");
            $stmtQuiz->execute([$siswaId]);
            $qData = $stmtQuiz->fetch();
            if ($qData && (int)$qData['cnt_quiz'] > 0 && $qData['avg_quiz'] !== null) {
                $scores[] = (float)$qData['avg_quiz'];
            }
        } catch (Exception $e) {}

        try {
            $stmtTugas = $this->db->prepare("SELECT AVG(nilai) as avg_tugas, COUNT(nilai) as cnt_tugas FROM pengumpulan_tugas WHERE siswa_id = ? AND nilai IS NOT NULL");
            $stmtTugas->execute([$siswaId]);
            $tData = $stmtTugas->fetch();
            if ($tData && (int)$tData['cnt_tugas'] > 0 && $tData['avg_tugas'] !== null) {
                $scores[] = (float)$tData['avg_tugas'];
            }
        } catch (Exception $e) {}

        $evaluasiLmsStr = "Belum Ada Nilai";
        $predikatStr = "Belum Ada Data";

        if (!empty($scores)) {
            $finalAvg = round(array_sum($scores) / count($scores), 1);
            $evaluasiLmsStr = number_format($finalAvg, 1) . " / 100";

            if ($finalAvg >= 90) {
                $predikatStr = "A (Sangat Memuaskan)";
            } elseif ($finalAvg >= 80) {
                $predikatStr = "B (Baik)";
            } elseif ($finalAvg >= 70) {
                $predikatStr = "C (Cukup)";
            } else {
                $predikatStr = "D (Perlu Bimbingan)";
            }
        }

        return [
            'predikat' => $predikatStr,
            'presensi_log' => $presensiStr,
            'evaluasi_lms' => $evaluasiLmsStr
        ];
    }
}
