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
            WHERE u.role_id = 3
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
            SELECT s.*, k.nama_kelas, k.tingkat, j.nama_jurusan, u.username, u.email, u.avatar 
            FROM siswa s 
            JOIN users u ON s.user_id = u.id 
            LEFT JOIN kelas k ON s.kelas_id = k.id 
            LEFT JOIN jurusan j ON s.jurusan_id = j.id 
            WHERE s.user_id = ? AND u.role_id = 3
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function ensureSiswaProfile($userId, $fullName) {
        if (!$userId) {
            return null;
        }

        // STRICT ROLE CHECK: Only users with role_id = 3 (Siswa) are allowed to have a student profile
        $stmtUser = $this->db->prepare("SELECT id, role_id, full_name FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch();
        if (!$user || (int)$user['role_id'] !== 3) {
            // Non-students (Admin, Guru, Kepsek) MUST NEVER be inserted into siswa or assigned to rombel!
            return null;
        }

        $fullName = !empty($fullName) ? $fullName : ($user['full_name'] ?? 'Siswa');
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

        return $this->getByUserId($userId);
    }

    public function addSiswa($data) {
        $this->db->beginTransaction();
        try {
            // Create user account with explicit role_id = 3 (Siswa)
            $stmtUser = $this->db->prepare("INSERT INTO users (role_id, username, email, password, full_name) VALUES (3, ?, ?, ?, ?)");
            $hash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmtUser->execute([$data['username'], $data['email'], $hash, $data['nama_lengkap']]);
            $userId = $this->db->lastInsertId();

            // Create siswa profile
            $stmtSiswa = $this->db->prepare("INSERT INTO siswa (user_id, nis, nisn, nama_lengkap, kelas_id, jurusan_id, jenis_kelamin, no_telepon, no_ortu, alamat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtSiswa->execute([$userId, $data['nis'], $data['nisn'], $data['nama_lengkap'], $data['kelas_id'], $data['jurusan_id'], $data['jenis_kelamin'], $data['no_telepon'], $data['no_ortu'] ?? null, $data['alamat']]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    protected $lastError = null;

    public function getLastError() {
        return $this->lastError;
    }

    public function updateSiswa($id, $data) {
        $id = (int)$id;
        if ($id <= 0) {
            $this->lastError = 'ID Siswa tidak valid.';
            return false;
        }

        $stmt = $this->db->prepare("SELECT s.*, u.email as current_email FROM siswa s LEFT JOIN users u ON s.user_id = u.id WHERE s.id = ?");
        $stmt->execute([$id]);
        $siswa = $stmt->fetch();

        if (!$siswa) {
            $this->lastError = "Data siswa dengan ID #{$id} tidak ditemukan.";
            return false;
        }

        // Ambil data baru atau fallback ke data lama jika kosong / hanya menambahkan no_ortu
        $nis = !empty(trim($data['nis'] ?? '')) ? trim($data['nis']) : ($siswa['nis'] ?? '');
        $nisn = !empty(trim($data['nisn'] ?? '')) ? trim($data['nisn']) : ($siswa['nisn'] ?? '');
        $nama = !empty(trim($data['nama_lengkap'] ?? '')) ? trim($data['nama_lengkap']) : ($siswa['nama_lengkap'] ?? 'Siswa');
        $kelasId = (int)($data['kelas_id'] ?? 0);
        if ($kelasId <= 0) $kelasId = (int)($siswa['kelas_id'] ?? 1);
        $jurusanId = (int)($data['jurusan_id'] ?? 0);
        if ($jurusanId <= 0) $jurusanId = (int)($siswa['jurusan_id'] ?? 1);
        $jkRaw = strtoupper($data['jenis_kelamin'] ?? ($siswa['jenis_kelamin'] ?? 'L'));
        $jk = in_array($jkRaw, ['L', 'P']) ? $jkRaw : 'L';
        $noTelp = isset($data['no_telepon']) ? trim($data['no_telepon']) : ($siswa['no_telepon'] ?? '');
        $noOrtu = trim($data['no_ortu'] ?? $data['no_hp_ortu'] ?? $data['no_hp'] ?? $data['telepon_ortu'] ?? ($siswa['no_ortu'] ?? ''));
        $alamat = isset($data['alamat']) && trim($data['alamat']) !== '' ? trim($data['alamat']) : ($siswa['alamat'] ?? '');

        // Validasi keunikan NIS terhadap siswa lain jika diubah
        if (!empty($nis)) {
            $chkNis = $this->db->prepare("SELECT id FROM siswa WHERE nis = ? AND id != ? LIMIT 1");
            $chkNis->execute([$nis, $id]);
            if ($chkNis->fetch()) {
                $this->lastError = "NIS '{$nis}' sudah terdaftar untuk siswa lain.";
                return false;
            }
        }

        // Validasi keunikan NISN terhadap siswa lain jika diubah
        if (!empty($nisn)) {
            $chkNisn = $this->db->prepare("SELECT id FROM siswa WHERE nisn = ? AND id != ? LIMIT 1");
            $chkNisn->execute([$nisn, $id]);
            if ($chkNisn->fetch()) {
                $this->lastError = "NISN '{$nisn}' sudah terdaftar untuk siswa lain.";
                return false;
            }
        }

        $this->db->beginTransaction();
        try {
            $stmtSiswa = $this->db->prepare("
                UPDATE siswa 
                SET nis = ?, nisn = ?, nama_lengkap = ?, kelas_id = ?, jurusan_id = ?, jenis_kelamin = ?, no_telepon = ?, no_ortu = ?, alamat = ? 
                WHERE id = ?
            ");
            $stmtSiswa->execute([$nis, $nisn, $nama, $kelasId, $jurusanId, $jk, $noTelp, $noOrtu, $alamat, $id]);

            if (!empty($siswa['user_id'])) {
                $userId = (int)$siswa['user_id'];
                $newEmail = trim($data['email'] ?? '');
                
                // Cek apakah email sudah dipakai user lain
                $emailToSave = null;
                if (!empty($newEmail) && filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                    $chkEmail = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
                    $chkEmail->execute([$newEmail, $userId]);
                    if (!$chkEmail->fetch()) {
                        $emailToSave = $newEmail;
                    }
                }

                if ($emailToSave !== null) {
                    $stmtUser = $this->db->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                    $stmtUser->execute([$nama, $emailToSave, $userId]);
                } else {
                    $stmtUser = $this->db->prepare("UPDATE users SET full_name = ? WHERE id = ?");
                    $stmtUser->execute([$nama, $userId]);
                }

                if (!empty($data['password'])) {
                    $hash = password_hash($data['password'], PASSWORD_BCRYPT);
                    $stmtPass = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmtPass->execute([$hash, $userId]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function deleteSiswa($id) {
        $stmt = $this->db->prepare("SELECT s.user_id, u.role_id FROM siswa s LEFT JOIN users u ON s.user_id = u.id WHERE s.id = ?");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();

        if ($row) {
            $userId = (int)$row['user_id'];
            $roleId = (int)($row['role_id'] ?? 0);

            // Clean related tables
            $this->db->prepare("DELETE FROM nilai_rapor WHERE siswa_id = ?")->execute([(int)$id]);
            $this->db->prepare("DELETE FROM rapor_siswa WHERE siswa_id = ?")->execute([(int)$id]);
            $this->db->prepare("DELETE FROM siswa_mapel_enrollment WHERE siswa_id = ?")->execute([(int)$id]);
            $this->db->prepare("DELETE FROM absensi WHERE siswa_id = ?")->execute([(int)$id]);
            $this->db->prepare("DELETE FROM cbt_peserta WHERE siswa_id = ?")->execute([(int)$id]);
            $this->db->prepare("DELETE FROM cbt_jawaban WHERE siswa_id = ?")->execute([(int)$id]);

            // Delete from siswa table
            $this->db->prepare("DELETE FROM siswa WHERE id = ?")->execute([(int)$id]);

            // Only delete user account if role is Siswa (role_id = 3). NEVER delete Admin (1) or Guru (2)!
            if ($userId > 0 && $roleId === 3) {
                $stmtDel = $this->db->prepare("DELETE FROM users WHERE id = ?");
                $stmtDel->execute([$userId]);
            }
            return true;
        }
        return false;
    }

    public function getSiswaCertificateRealStats($siswaId) {
        $siswaId = (int)$siswaId;
        
        // 1. Presensi Log Metrics & Rate
        $totalAbsensi = 0;
        $totalHadir   = 0;
        $totalIzin    = 0;
        $totalSakit   = 0;
        $totalAlpa    = 0;
        $rateHadir    = 0;
        $presensiStr  = "Belum Ada Data";

        try {
            $stmtAtt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_absensi,
                    COUNT(CASE WHEN LOWER(status) = 'hadir' THEN 1 END) as total_hadir,
                    COUNT(CASE WHEN LOWER(status) = 'izin' THEN 1 END) as total_izin,
                    COUNT(CASE WHEN LOWER(status) = 'sakit' THEN 1 END) as total_sakit,
                    COUNT(CASE WHEN LOWER(status) = 'alpa' THEN 1 END) as total_alpa
                FROM absensi 
                WHERE siswa_id = ?
            ");
            $stmtAtt->execute([$siswaId]);
            $attData = $stmtAtt->fetch();
            if ($attData) {
                $totalAbsensi = (int)$attData['total_absensi'];
                $totalHadir   = (int)$attData['total_hadir'];
                $totalIzin    = (int)$attData['total_izin'];
                $totalSakit   = (int)$attData['total_sakit'];
                $totalAlpa    = (int)$attData['total_alpa'];

                if ($totalAbsensi > 0) {
                    $rateHadir = round(($totalHadir / $totalAbsensi) * 100);
                    $presensiStr = $rateHadir . "%";
                }
            }
        } catch (\Throwable $e) {}

        // 2. Real Academic Evaluation Pillars (Tugas, Kuis/CBT, E-Rapor)
        $cntTugas = 0;
        $avgTugas = null;
        try {
            $stmtTugas = $this->db->prepare("
                SELECT COUNT(nilai) as cnt, AVG(nilai) as avg_val 
                FROM pengumpulan_tugas 
                WHERE siswa_id = ? AND nilai IS NOT NULL
            ");
            $stmtTugas->execute([$siswaId]);
            $tData = $stmtTugas->fetch();
            if ($tData && (int)$tData['cnt'] > 0 && $tData['avg_val'] !== null) {
                $cntTugas = (int)$tData['cnt'];
                $avgTugas = round((float)$tData['avg_val'], 1);
            }
        } catch (\Throwable $e) {}

        $cntQuiz = 0;
        $cntQuizLulus = 0;
        $avgQuiz = null;
        try {
            $stmtQuiz = $this->db->prepare("
                SELECT COUNT(total_nilai) as cnt,
                       COUNT(CASE WHEN status_lulus = 'lulus' THEN 1 END) as cnt_lulus,
                       AVG(COALESCE(nilai_tertinggi, total_nilai)) as avg_val 
                FROM hasil_quiz 
                WHERE siswa_id = ? AND total_nilai IS NOT NULL
            ");
            $stmtQuiz->execute([$siswaId]);
            $qData = $stmtQuiz->fetch();
            if ($qData && (int)$qData['cnt'] > 0 && $qData['avg_val'] !== null) {
                $cntQuiz = (int)$qData['cnt'];
                $cntQuizLulus = (int)$qData['cnt_lulus'];
                $avgQuiz = round((float)$qData['avg_val'], 1);
            }
        } catch (\Throwable $e) {}

        $cntRapor = 0;
        $avgRapor = null;
        try {
            // Filter nilai_akhir > 0 to avoid un-enrolled placeholder 0.00 dragging down true score
            $stmtRapor = $this->db->prepare("
                SELECT COUNT(nilai_akhir) as cnt, AVG(nilai_akhir) as avg_val 
                FROM nilai_rapor 
                WHERE siswa_id = ? AND nilai_akhir > 0
            ");
            $stmtRapor->execute([$siswaId]);
            $rData = $stmtRapor->fetch();
            if ($rData && (int)$rData['cnt'] > 0 && $rData['avg_val'] !== null) {
                $cntRapor = (int)$rData['cnt'];
                $avgRapor = round((float)$rData['avg_val'], 1);
            }
        } catch (\Throwable $e) {}

        // 3. Proportional Weighted Composite Evaluation
        $validPillars = [];
        if ($avgTugas !== null) {
            $validPillars[] = ['val' => $avgTugas, 'weight' => 0.25];
        }
        if ($avgQuiz !== null) {
            $validPillars[] = ['val' => $avgQuiz, 'weight' => 0.35];
        }
        if ($avgRapor !== null) {
            $validPillars[] = ['val' => $avgRapor, 'weight' => 0.40];
        }

        $finalAvg = 0.0;
        if (!empty($validPillars)) {
            $sumVal = 0;
            $sumWeight = 0;
            foreach ($validPillars as $p) {
                $sumVal += ($p['val'] * $p['weight']);
                $sumWeight += $p['weight'];
            }
            $finalAvg = ($sumWeight > 0) ? round($sumVal / $sumWeight, 1) : 0.0;
        }

        // 4. Standard SMK Grade Predicate Scale
        $grade = 'D';
        $label = 'Perlu Bimbingan';
        $predClass = 'bg-danger text-white';
        $borderClass = 'border-danger';

        if ($finalAvg >= 88) {
            $grade = 'A';
            $label = 'Sangat Memuaskan';
            $predClass = 'bg-success text-white';
            $borderClass = 'border-success';
        } elseif ($finalAvg >= 78) {
            $grade = 'B';
            $label = 'Baik';
            $predClass = 'bg-primary text-white';
            $borderClass = 'border-primary';
        } elseif ($finalAvg >= 68) {
            $grade = 'C';
            $label = 'Cukup';
            $predClass = 'bg-warning text-dark';
            $borderClass = 'border-warning';
        } else {
            $grade = 'D';
            $label = 'Perlu Bimbingan';
            $predClass = 'bg-danger text-white';
            $borderClass = 'border-danger';
        }

        $evaluasiLmsStr = ($finalAvg > 0) ? number_format($finalAvg, 1) . " / 100" : "Belum Ada Nilai";
        $predikatStr    = ($finalAvg > 0) ? "{$grade} ({$label})" : "Belum Ada Data";

        return [
            // Backward-compatible string keys
            'predikat' => $predikatStr,
            'presensi_log' => $presensiStr,
            'evaluasi_lms' => $evaluasiLmsStr,
            // Rich structured fields
            'evaluasi_nilai' => $finalAvg,
            'predikat_grade' => $grade,
            'predikat_label' => $label,
            'predikat_class' => $predClass,
            'border_class' => $borderClass,
            'is_tuntas' => ($finalAvg >= 75),
            'kkm' => 75,
            // Presensi detailed metrics
            'total_absensi' => $totalAbsensi,
            'total_hadir' => $totalHadir,
            'total_izin' => $totalIzin,
            'total_sakit' => $totalSakit,
            'total_alpa' => $totalAlpa,
            'rate_hadir' => $rateHadir,
            // Academic pillars
            'total_tugas' => $cntTugas,
            'avg_tugas' => $avgTugas,
            'total_quiz' => $cntQuiz,
            'total_quiz_lulus' => $cntQuizLulus,
            'avg_quiz' => $avgQuiz,
            'total_mapel_rapor' => $cntRapor,
            'avg_rapor' => $avgRapor,
        ];
    }

    /**
     * Ambil riwayat log presensi riil siswa langsung dari tabel absensi
     */
    public function getSiswaPresensiLogs($siswaId, $limit = 10) {
        $siswaId = (int)$siswaId;
        $limit = max(1, min(50, (int)$limit));
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       COALESCE(g.nama_lengkap, u.full_name, 'Guru Pengampu') as nama_guru,
                       COALESCE(mp.nama_mapel, 'Kegiatan KBM Harian') as nama_mapel
                FROM absensi a
                LEFT JOIN guru g ON a.guru_id = g.id
                LEFT JOIN users u ON g.user_id = u.id
                LEFT JOIN jadwal j ON a.jadwal_id = j.id
                LEFT JOIN mata_pelajaran mp ON j.mapel_id = mp.id
                WHERE a.siswa_id = ?
                ORDER BY a.tanggal DESC, a.waktu_masuk DESC, a.id DESC
                LIMIT {$limit}
            ");
            $stmt->execute([$siswaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
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
            // Find student users that actually have role_id = 3 (Siswa)
            $stmtUserIds = $this->db->query("
                SELECT s.user_id 
                FROM siswa s 
                JOIN users u ON s.user_id = u.id 
                WHERE s.id IN ({$inClause}) AND u.role_id = 3
            ");
            $uIds = $stmtUserIds ? $stmtUserIds->fetchAll(PDO::FETCH_COLUMN) : [];
            
            // Clean related tables
            $this->db->exec("DELETE FROM nilai_rapor WHERE siswa_id IN ({$inClause})");
            $this->db->exec("DELETE FROM rapor_siswa WHERE siswa_id IN ({$inClause})");
            $this->db->exec("DELETE FROM siswa_mapel_enrollment WHERE siswa_id IN ({$inClause})");
            $this->db->exec("DELETE FROM absensi WHERE siswa_id IN ({$inClause})");
            $this->db->exec("DELETE FROM cbt_peserta WHERE siswa_id IN ({$inClause})");
            $this->db->exec("DELETE FROM cbt_jawaban WHERE siswa_id IN ({$inClause})");

            // Delete from siswa table
            $this->db->exec("DELETE FROM siswa WHERE id IN ({$inClause})");

            // Only delete users with role_id = 3. NEVER delete Admin (1) or Guru (2)!
            if (!empty($uIds)) {
                $uIn = implode(',', array_map('intval', $uIds));
                $this->db->exec("DELETE FROM users WHERE id IN ({$uIn}) AND role_id = 3");
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
            $stmtSiswa = $this->db->prepare("UPDATE siswa SET nis = ?, nisn = ?, nama_lengkap = ?, kelas_id = ?, jurusan_id = ?, jenis_kelamin = ?, no_ortu = ? WHERE id = ?");
            $stmtUser = $this->db->prepare("UPDATE users SET full_name = ? WHERE id = (SELECT user_id FROM siswa WHERE id = ?)");

            foreach ($matrixData as $id => $row) {
                $sId = (int)$id;
                if ($sId <= 0) continue;
                $nis = Security::sanitize($row['nis'] ?? '');
                $nisn = Security::sanitize($row['nisn'] ?? '');
                $nama = Security::sanitize($row['nama_lengkap'] ?? '');
                $kelasId = (int)($row['kelas_id'] ?? 0);
                $jurusanId = (int)($row['jurusan_id'] ?? 0);
                $jkRaw = strtoupper($row['jenis_kelamin'] ?? 'L');
                $jk = in_array($jkRaw, ['L', 'P']) ? $jkRaw : 'L';
                $noOrtu = Security::sanitize($row['no_ortu'] ?? $row['no_hp_ortu'] ?? $row['no_hp'] ?? $row['telepon_ortu'] ?? '');

                if (!empty($nama)) {
                    if ($kelasId <= 0 || $jurusanId <= 0) {
                        $curr = $this->db->query("SELECT kelas_id, jurusan_id FROM siswa WHERE id = {$sId}")->fetch();
                        if ($kelasId <= 0) $kelasId = (int)($curr['kelas_id'] ?? 1);
                        if ($jurusanId <= 0) $jurusanId = (int)($curr['jurusan_id'] ?? 1);
                    }
                    $stmtSiswa->execute([$nis, $nisn, $nama, $kelasId, $jurusanId, $jk, $noOrtu, $sId]);
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
