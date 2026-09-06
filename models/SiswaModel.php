<?php
/**
 * Siswa Model
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class SiswaModel extends BaseModel {

    public function getAll($kelasId = null, $jurusanId = null, $keyword = null, $jenisKelamin = null) {
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

        if ($jenisKelamin && in_array(strtoupper($jenisKelamin), ['L', 'P'])) {
            $sql .= " AND s.jenis_kelamin = ?";
            $params[] = strtoupper($jenisKelamin);
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
        if (!$userId) {
            return ['id' => 0, 'user_id' => 0, 'nama_lengkap' => $fullName, 'kelas_id' => 1, 'jurusan_id' => 1];
        }

        $siswa = $this->getByUserId($userId);
        if ($siswa) return $siswa;

        try {
            $kStmt = $this->db->query("SELECT id, jurusan_id FROM kelas ORDER BY id ASC LIMIT 1");
            $kRow = $kStmt ? $kStmt->fetch() : null;
            $kelasId = $kRow['id'] ?? 1;
            $jurusanId = $kRow['jurusan_id'] ?? 1;

            for ($attempt = 0; $attempt < 5; $attempt++) {
                try {
                    $nis = 'S' . date('Ym') . str_pad(rand(100, 9999), 4, '0', STR_PAD_LEFT);
                    $stmt = $this->db->prepare("INSERT INTO siswa (user_id, nis, nisn, nama_lengkap, kelas_id, jurusan_id, jenis_kelamin) VALUES (?, ?, ?, ?, ?, ?, 'L')");
                    $stmt->execute([$userId, $nis, $nis, $fullName, $kelasId, $jurusanId]);
                    $created = $this->getByUserId($userId);
                    if ($created) return $created;
                } catch (\Throwable $exAttempt) {
                    // Collision retry
                }
            }
        } catch (\Throwable $e) {}

        $fallback = $this->getByUserId($userId);
        return $fallback ?: ['id' => 0, 'user_id' => $userId, 'nama_lengkap' => $fullName, 'kelas_id' => 1, 'jurusan_id' => 1];
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

    public function bulkUpdateKelas($siswaIds, $newKelasId) {
        if (empty($siswaIds) || !is_array($siswaIds) || (int)$newKelasId <= 0) return 0;
        $ids = array_map('intval', $siswaIds);
        $inClause = implode(',', $ids);
        
        $stmtK = $this->db->prepare("SELECT jurusan_id FROM kelas WHERE id = ?");
        $stmtK->execute([(int)$newKelasId]);
        $kRow = $stmtK->fetch();
        $jurusanId = $kRow ? (int)$kRow['jurusan_id'] : 0;

        if ($jurusanId > 0) {
            $sql = "UPDATE siswa SET kelas_id = ?, jurusan_id = ? WHERE id IN ({$inClause})";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$newKelasId, $jurusanId]);
        } else {
            $sql = "UPDATE siswa SET kelas_id = ? WHERE id IN ({$inClause})";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$newKelasId]);
        }
        return $stmt->rowCount();
    }

    public function bulkUpdateJurusan($siswaIds, $newJurusanId) {
        if (empty($siswaIds) || !is_array($siswaIds) || (int)$newJurusanId <= 0) return 0;
        $ids = array_map('intval', $siswaIds);
        $inClause = implode(',', $ids);
        $sql = "UPDATE siswa SET jurusan_id = ? WHERE id IN ({$inClause})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$newJurusanId]);
        return $stmt->rowCount();
    }

    public function bulkDeleteSiswa($siswaIds) {
        if (empty($siswaIds) || !is_array($siswaIds)) return 0;
        $ids = array_map('intval', $siswaIds);
        $inClause = implode(',', $ids);
        
        $this->db->beginTransaction();
        try {
            $stmtUserIds = $this->db->query("SELECT user_id FROM siswa WHERE id IN ({$inClause})");
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

    public function bulkUpdateMatrix($matrixData) {
        if (empty($matrixData) || !is_array($matrixData)) return 0;
        $count = 0;
        $this->db->beginTransaction();
        try {
            $stmtSiswa = $this->db->prepare("UPDATE siswa SET nis = ?, nisn = ?, nama_lengkap = ?, kelas_id = ?, jurusan_id = ?, jenis_kelamin = ? WHERE id = ?");
            $stmtUser = $this->db->prepare("UPDATE users u JOIN siswa s ON u.id = s.user_id SET u.full_name = ? WHERE s.id = ?");

            foreach ($matrixData as $id => $row) {
                $sId = (int)$id;
                if ($sId <= 0) continue;
                $nis = Security::sanitize($row['nis'] ?? '');
                $nisn = Security::sanitize($row['nisn'] ?? '');
                $nama = Security::sanitize($row['nama_lengkap'] ?? '');
                $kelasId = (int)($row['kelas_id'] ?? 0);
                $jurusanId = (int)($row['jurusan_id'] ?? 0);
                $jk = in_array(strtoupper($row['jenis_kelamin'] ?? 'L'), ['L', 'P']) ? strtoupper($row['jenis_kelamin']) : 'L';

                if (!empty($nama) && $kelasId > 0 && $jurusanId > 0) {
                    $stmtSiswa->execute([$nis, $nisn, $nama, $kelasId, $jurusanId, $jk, $sId]);
                    $stmtUser->execute([$nama, $sId]);
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
