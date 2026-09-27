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

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT s.*, k.nama_kelas, k.tingkat, j.nama_jurusan, u.username, u.email, u.avatar 
            FROM siswa s 
            LEFT JOIN users u ON s.user_id = u.id 
            LEFT JOIN kelas k ON s.kelas_id = k.id 
            LEFT JOIN jurusan j ON s.jurusan_id = j.id 
            WHERE s.id = ?
        ");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
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

    public static function ensureNoOrtuColumn($db) {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        try {
            $colCheck = $db->query("SHOW COLUMNS FROM `siswa` LIKE 'no_ortu'")->fetch();
            if (!$colCheck) {
                try {
                    $db->exec("ALTER TABLE `siswa` ADD COLUMN `no_ortu` VARCHAR(25) NULL DEFAULT NULL AFTER `no_telepon`");
                } catch (\Throwable $eAlter) {
                    $db->exec("ALTER TABLE `siswa` ADD COLUMN `no_ortu` VARCHAR(25) NULL DEFAULT NULL");
                }
            }
        } catch (\Throwable $e) {}
    }

    public function generateUniqueUsername($nama, $preferredUsername = '', $excludeUserId = null) {
        $clean = preg_replace('/[^a-zA-Z0-9]/', '', (string)$nama);
        $base = !empty(trim((string)$preferredUsername)) ? trim((string)$preferredUsername) : ('siswa_' . strtolower($clean));
        if (empty($base) || $base === 'siswa_') {
            $base = 'siswa_' . date('Ymd');
        }
        $base = substr($base, 0, 40);

        $username = $base;
        $counter = 1;
        while (true) {
            $sql = "SELECT id FROM users WHERE username = ?";
            $params = [$username];
            if ($excludeUserId) {
                $sql .= " AND id != ?";
                $params[] = (int)$excludeUserId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            if (!$stmt->fetch()) {
                return $username;
            }
            $username = $base . $counter;
            $counter++;
        }
    }

    public function generateUniqueEmail($username, $preferredEmail = '', $excludeUserId = null) {
        if (!empty(trim((string)$preferredEmail))) {
            $email = trim((string)$preferredEmail);
            $sql = "SELECT id FROM users WHERE email = ?";
            $params = [$email];
            if ($excludeUserId) {
                $sql .= " AND id != ?";
                $params[] = (int)$excludeUserId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            if (!$stmt->fetch()) {
                return $email;
            }
        }

        $baseEmail = $username . '@smkmh-cicalengka.sch.id';
        $email = $baseEmail;
        $counter = 1;
        while (true) {
            $sql = "SELECT id FROM users WHERE email = ?";
            $params = [$email];
            if ($excludeUserId) {
                $sql .= " AND id != ?";
                $params[] = (int)$excludeUserId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            if (!$stmt->fetch()) {
                return $email;
            }
            $parts = explode('@', $baseEmail);
            $email = $parts[0] . $counter . '@' . ($parts[1] ?? 'smkmh-cicalengka.sch.id');
            $counter++;
        }
    }

    public function findExistingSiswa($nis = null, $nisn = null, $username = null) {
        $nis = !empty(trim((string)$nis)) ? trim((string)$nis) : null;
        $nisn = !empty(trim((string)$nisn)) ? trim((string)$nisn) : null;
        $username = !empty(trim((string)$username)) ? trim((string)$username) : null;

        if ($nis !== null) {
            $stmt = $this->db->prepare("SELECT s.*, u.username, u.email FROM siswa s JOIN users u ON s.user_id = u.id WHERE s.nis = ? LIMIT 1");
            $stmt->execute([$nis]);
            $res = $stmt->fetch();
            if ($res) return $res;
        }

        if ($nisn !== null) {
            $stmt = $this->db->prepare("SELECT s.*, u.username, u.email FROM siswa s JOIN users u ON s.user_id = u.id WHERE s.nisn = ? LIMIT 1");
            $stmt->execute([$nisn]);
            $res = $stmt->fetch();
            if ($res) return $res;
        }

        if ($username !== null) {
            $stmt = $this->db->prepare("SELECT s.*, u.username, u.email FROM siswa s JOIN users u ON s.user_id = u.id WHERE u.username = ? LIMIT 1");
            $stmt->execute([$username]);
            $res = $stmt->fetch();
            if ($res) return $res;
        }

        return null;
    }

    public function saveOrUpdateSiswa($data) {
        self::ensureNoOrtuColumn($this->db);

        $nis = !empty(trim((string)($data['nis'] ?? ''))) ? trim((string)$data['nis']) : null;
        $nisn = !empty(trim((string)($data['nisn'] ?? ''))) ? trim((string)$data['nisn']) : null;
        $nama = trim((string)($data['nama_lengkap'] ?? ''));
        $username = trim((string)($data['username'] ?? ''));

        if (empty($nama)) {
            $this->lastError = 'Nama lengkap siswa wajib diisi.';
            return ['status' => false, 'message' => $this->lastError];
        }

        // Cari apakah siswa sudah terdaftar berdasarkan NIS, NISN, atau Username
        $existing = $this->findExistingSiswa($nis, $nisn, $username);

        if ($existing) {
            // Mode Sinkronisasi / Update data siswa yang sudah ada
            $updateData = [
                'nama_lengkap' => $nama,
                'nis' => $nis ?? $existing['nis'],
                'nisn' => $nisn ?? $existing['nisn'],
                'kelas_id' => !empty($data['kelas_id']) ? (int)$data['kelas_id'] : $existing['kelas_id'],
                'jurusan_id' => !empty($data['jurusan_id']) ? (int)$data['jurusan_id'] : $existing['jurusan_id'],
                'jenis_kelamin' => !empty($data['jenis_kelamin']) ? $data['jenis_kelamin'] : $existing['jenis_kelamin'],
                'no_telepon' => !empty($data['no_telepon']) ? $data['no_telepon'] : $existing['no_telepon'],
                'no_ortu' => !empty($data['no_ortu']) ? $data['no_ortu'] : ($existing['no_ortu'] ?? ''),
                'alamat' => !empty($data['alamat']) ? $data['alamat'] : $existing['alamat']
            ];

            // Update akun user jika ada perubahan nama / email / password
            try {
                $uSql = "UPDATE users SET full_name = ?";
                $uParams = [$nama];
                if (!empty($data['email'])) {
                    $newEmail = $this->generateUniqueEmail($existing['username'], $data['email'], $existing['user_id']);
                    $uSql .= ", email = ?";
                    $uParams[] = $newEmail;
                }
                if (!empty($data['password']) && $data['password'] !== '123456') {
                    $uSql .= ", password = ?";
                    $uParams[] = password_hash($data['password'], PASSWORD_BCRYPT);
                }
                $uSql .= " WHERE id = ?";
                $uParams[] = (int)$existing['user_id'];
                $stmtU = $this->db->prepare($uSql);
                $stmtU->execute($uParams);
            } catch (\Throwable $eU) {}

            $success = $this->updateSiswa($existing['id'], $updateData);
            if ($success) {
                return ['status' => true, 'action' => 'updated', 'id' => $existing['id']];
            } else {
                return ['status' => false, 'message' => $this->getLastError() ?: 'Gagal memperbarui siswa.'];
            }
        } else {
            // Siswa Baru: Tambahkan akun dan profil
            $finalUsername = $this->generateUniqueUsername($nama, $username);
            $finalEmail = $this->generateUniqueEmail($finalUsername, $data['email'] ?? '');

            $addData = [
                'username' => $finalUsername,
                'email' => $finalEmail,
                'password' => !empty($data['password']) ? $data['password'] : '123456',
                'nis' => $nis,
                'nisn' => $nisn,
                'nama_lengkap' => $nama,
                'kelas_id' => !empty($data['kelas_id']) ? (int)$data['kelas_id'] : 1,
                'jurusan_id' => !empty($data['jurusan_id']) ? (int)$data['jurusan_id'] : 1,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? 'L',
                'no_telepon' => $data['no_telepon'] ?? '',
                'no_ortu' => $data['no_ortu'] ?? '',
                'alamat' => $data['alamat'] ?? ''
            ];

            $res = $this->addSiswa($addData);
            if ($res) {
                return ['status' => true, 'action' => 'inserted'];
            } else {
                return ['status' => false, 'message' => $this->getLastError() ?: 'Gagal menambahkan siswa baru.'];
            }
        }
    }

    public function addSiswa($data) {
        self::ensureNoOrtuColumn($this->db);

        $nama = trim((string)($data['nama_lengkap'] ?? ''));
        if (empty($nama)) {
            $this->lastError = 'Nama lengkap siswa wajib diisi.';
            return false;
        }

        // NIS & NISN: jika kosong, simpan sebagai NULL agar tidak bentrok UNIQUE constraint di MySQL
        $nis = !empty(trim((string)($data['nis'] ?? ''))) ? trim((string)$data['nis']) : null;
        $nisn = !empty(trim((string)($data['nisn'] ?? ''))) ? trim((string)$data['nisn']) : null;

        // Cek duplikasi NIS
        if ($nis !== null) {
            $chkNis = $this->db->prepare("SELECT id FROM siswa WHERE nis = ? LIMIT 1");
            $chkNis->execute([$nis]);
            if ($chkNis->fetch()) {
                $this->lastError = "NIS '{$nis}' sudah terdaftar untuk siswa lain.";
                return false;
            }
        }

        // Cek duplikasi NISN
        if ($nisn !== null) {
            $chkNisn = $this->db->prepare("SELECT id FROM siswa WHERE nisn = ? LIMIT 1");
            $chkNisn->execute([$nisn]);
            if ($chkNisn->fetch()) {
                $this->lastError = "NISN '{$nisn}' sudah terdaftar untuk siswa lain.";
                return false;
            }
        }

        // Pastikan username & email unik tanpa error
        $username = $this->generateUniqueUsername($nama, $data['username'] ?? '');
        $email = $this->generateUniqueEmail($username, $data['email'] ?? '');
        $password = !empty($data['password']) ? $data['password'] : '123456';
        $kelasId = !empty($data['kelas_id']) ? (int)$data['kelas_id'] : 1;
        $jurusanId = !empty($data['jurusan_id']) ? (int)$data['jurusan_id'] : 1;
        $jkRaw = strtoupper(trim((string)($data['jenis_kelamin'] ?? 'L')));
        $jk = in_array($jkRaw, ['L', 'P']) ? $jkRaw : 'L';
        $noTelp = trim((string)($data['no_telepon'] ?? ''));
        $noOrtu = trim((string)($data['no_ortu'] ?? $data['no_hp_ortu'] ?? ''));
        $alamat = trim((string)($data['alamat'] ?? ''));

        $this->db->beginTransaction();
        try {
            // Create user account with explicit role_id = 3 (Siswa)
            $stmtUser = $this->db->prepare("INSERT INTO users (role_id, username, email, password, full_name) VALUES (3, ?, ?, ?, ?)");
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmtUser->execute([$username, $email, $hash, $nama]);
            $userId = $this->db->lastInsertId();

            // Create siswa profile with self-healing fallback
            try {
                $stmtSiswa = $this->db->prepare("INSERT INTO siswa (user_id, nis, nisn, nama_lengkap, kelas_id, jurusan_id, jenis_kelamin, no_telepon, no_ortu, alamat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtSiswa->execute([$userId, $nis, $nisn, $nama, $kelasId, $jurusanId, $jk, $noTelp, $noOrtu ?: null, $alamat]);
            } catch (\Throwable $eInsert) {
                if (strpos($eInsert->getMessage(), 'no_ortu') !== false || $eInsert->getCode() == '42S22') {
                    $stmtSiswa = $this->db->prepare("INSERT INTO siswa (user_id, nis, nisn, nama_lengkap, kelas_id, jurusan_id, jenis_kelamin, no_telepon, alamat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmtSiswa->execute([$userId, $nis, $nisn, $nama, $kelasId, $jurusanId, $jk, $noTelp, $alamat]);
                } else {
                    throw $eInsert;
                }
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->lastError = $e->getMessage();
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

        self::ensureNoOrtuColumn($this->db);

        $this->db->beginTransaction();
        try {
            try {
                $stmtSiswa = $this->db->prepare("
                    UPDATE siswa 
                    SET nis = ?, nisn = ?, nama_lengkap = ?, kelas_id = ?, jurusan_id = ?, jenis_kelamin = ?, no_telepon = ?, no_ortu = ?, alamat = ? 
                    WHERE id = ?
                ");
                $stmtSiswa->execute([$nis, $nisn, $nama, $kelasId, $jurusanId, $jk, $noTelp, $noOrtu, $alamat, $id]);
            } catch (\Throwable $eSiswa) {
                // Self-healing jika kolom no_ortu belum ada di database ini (error 1054)
                if (strpos($eSiswa->getMessage(), 'no_ortu') !== false || $eSiswa->getCode() == '42S22') {
                    try {
                        $this->db->exec("ALTER TABLE `siswa` ADD COLUMN `no_ortu` VARCHAR(25) NULL DEFAULT NULL AFTER `no_telepon`");
                    } catch (\Throwable $eAlter) {
                        try {
                            $this->db->exec("ALTER TABLE `siswa` ADD COLUMN `no_ortu` VARCHAR(25) NULL DEFAULT NULL");
                        } catch (\Throwable $eIgn) {}
                    }

                    try {
                        $stmtSiswa = $this->db->prepare("
                            UPDATE siswa 
                            SET nis = ?, nisn = ?, nama_lengkap = ?, kelas_id = ?, jurusan_id = ?, jenis_kelamin = ?, no_telepon = ?, no_ortu = ?, alamat = ? 
                            WHERE id = ?
                        ");
                        $stmtSiswa->execute([$nis, $nisn, $nama, $kelasId, $jurusanId, $jk, $noTelp, $noOrtu, $alamat, $id]);
                    } catch (\Throwable $eRetry) {
                        // Fallback update tanpa no_ortu jika DDL dibatasi
                        $stmtSiswa = $this->db->prepare("
                            UPDATE siswa 
                            SET nis = ?, nisn = ?, nama_lengkap = ?, kelas_id = ?, jurusan_id = ?, jenis_kelamin = ?, no_telepon = ?, alamat = ? 
                            WHERE id = ?
                        ");
                        $stmtSiswa->execute([$nis, $nisn, $nama, $kelasId, $jurusanId, $jk, $noTelp, $alamat, $id]);
                    }
                } else {
                    throw $eSiswa;
                }
            }

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

            try {
                $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");
            } catch (\Throwable $eFk) {}

            $cleanTables = [
                'nilai_rapor',
                'rapor_siswa',
                'nilai',
                'nilai_asesmen_siswa',
                'nilai_asesmen_tp',
                'hasil_quiz',
                'hasil_ujian',
                'jawaban_siswa',
                'pengumpulan_tugas',
                'sertifikat',
                'siswa_mapel_enrollment',
                'absensi',
                'cbt_peserta',
                'cbt_jawaban',
                'pembayaran_riwayat',
                'pembayaran_tagihan',
                'wa_logs'
            ];

            foreach ($cleanTables as $tbl) {
                try {
                    $this->db->prepare("DELETE FROM `{$tbl}` WHERE siswa_id = ?")->execute([(int)$id]);
                } catch (\Throwable $eTbl) {
                    // Abaikan jika tabel tidak ada di database ini
                }
            }

            // Delete from siswa table
            $res = $this->db->prepare("DELETE FROM siswa WHERE id = ?")->execute([(int)$id]);

            // Only delete user account if role is Siswa (role_id = 3). NEVER delete Admin (1) or Guru (2)!
            if ($userId > 0 && $roleId === 3) {
                try {
                    $stmtDel = $this->db->prepare("DELETE FROM users WHERE id = ?");
                    $stmtDel->execute([$userId]);
                } catch (\Throwable $eUser) {}
            }

            try {
                $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
            } catch (\Throwable $eFk) {}

            return $res;
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
            
            try {
                $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");
            } catch (\Throwable $eFk) {}

            $cleanTables = [
                'nilai_rapor',
                'rapor_siswa',
                'nilai',
                'nilai_asesmen_siswa',
                'nilai_asesmen_tp',
                'hasil_quiz',
                'hasil_ujian',
                'jawaban_siswa',
                'pengumpulan_tugas',
                'sertifikat',
                'siswa_mapel_enrollment',
                'absensi',
                'cbt_peserta',
                'cbt_jawaban',
                'pembayaran_riwayat',
                'pembayaran_tagihan',
                'wa_logs'
            ];

            foreach ($cleanTables as $tbl) {
                try {
                    $this->db->exec("DELETE FROM `{$tbl}` WHERE siswa_id IN ({$inClause})");
                } catch (\Throwable $eTbl) {
                    // Abaikan jika tabel tidak ada di database ini
                }
            }

            // Delete from siswa table
            $this->db->exec("DELETE FROM siswa WHERE id IN ({$inClause})");

            // Only delete users with role_id = 3. NEVER delete Admin (1) or Guru (2)!
            if (!empty($uIds)) {
                $uIn = implode(',', array_map('intval', $uIds));
                try {
                    $this->db->exec("DELETE FROM users WHERE id IN ({$uIn}) AND role_id = 3");
                } catch (\Throwable $eUser) {}
            }

            try {
                $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
            } catch (\Throwable $eFk) {}

            $this->db->commit();
            return count($ids);
        } catch (\Throwable $e) {
            try {
                $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
            } catch (\Throwable $eFk) {}
            $this->db->rollBack();
            return 0;
        }
    }

    public function bulkUpdateMatrix($matrixData) {
        if (empty($matrixData) || !is_array($matrixData)) return 0;
        self::ensureNoOrtuColumn($this->db);
        $count = 0;
        $this->db->beginTransaction();
        try {
            $hasNoOrtu = true;
            try {
                $stmtSiswa = $this->db->prepare("UPDATE siswa SET nis = ?, nisn = ?, nama_lengkap = ?, kelas_id = ?, jurusan_id = ?, jenis_kelamin = ?, no_ortu = ? WHERE id = ?");
            } catch (\Throwable $ePrep) {
                $hasNoOrtu = false;
                $stmtSiswa = $this->db->prepare("UPDATE siswa SET nis = ?, nisn = ?, nama_lengkap = ?, kelas_id = ?, jurusan_id = ?, jenis_kelamin = ? WHERE id = ?");
            }
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

                    if ($hasNoOrtu) {
                        try {
                            $stmtSiswa->execute([$nis, $nisn, $nama, $kelasId, $jurusanId, $jk, $noOrtu, $sId]);
                        } catch (\Throwable $eExec) {
                            if (strpos($eExec->getMessage(), 'no_ortu') !== false || $eExec->getCode() == '42S22') {
                                try {
                                    $this->db->exec("ALTER TABLE `siswa` ADD COLUMN `no_ortu` VARCHAR(25) NULL DEFAULT NULL AFTER `no_telepon`");
                                } catch (\Throwable $eAlter) {
                                    try {
                                        $this->db->exec("ALTER TABLE `siswa` ADD COLUMN `no_ortu` VARCHAR(25) NULL DEFAULT NULL");
                                    } catch (\Throwable $eIgn) {}
                                }
                                try {
                                    $stmtSiswa->execute([$nis, $nisn, $nama, $kelasId, $jurusanId, $jk, $noOrtu, $sId]);
                                } catch (\Throwable $eRetry) {
                                    $stmtFallback = $this->db->prepare("UPDATE siswa SET nis = ?, nisn = ?, nama_lengkap = ?, kelas_id = ?, jurusan_id = ?, jenis_kelamin = ? WHERE id = ?");
                                    $stmtFallback->execute([$nis, $nisn, $nama, $kelasId, $jurusanId, $jk, $sId]);
                                }
                            } else {
                                throw $eExec;
                            }
                        }
                    } else {
                        $stmtSiswa->execute([$nis, $nisn, $nama, $kelasId, $jurusanId, $jk, $sId]);
                    }

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
