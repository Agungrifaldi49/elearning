<?php
/**
 * Learning Model (Materi, Video, Tugas, Pengumpulan)
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class LearningModel extends BaseModel {

    public function __construct() {
        parent::__construct();
        $this->ensureTugasSusulanTable();
        $this->ensureMateriKelasIdsColumn();
        $this->ensureTugasKelasIdsColumn();
    }

    private function ensureTugasSusulanTable() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS tugas_susulan (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tugas_id INT NOT NULL,
                    siswa_id INT NOT NULL,
                    status ENUM('pending', 'disetujui', 'ditolak') DEFAULT 'pending',
                    catatan VARCHAR(255) NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_tugas_siswa (tugas_id, siswa_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (Exception $e) {}
    }

    private function ensureMateriKelasIdsColumn() {
        try {
            $this->db->exec("ALTER TABLE materi ADD COLUMN kelas_ids VARCHAR(255) NULL AFTER kelas_id");
        } catch (Exception $e) {}
        try {
            $this->db->exec("UPDATE materi SET kelas_ids = CAST(kelas_id AS CHAR) WHERE kelas_ids IS NULL OR TRIM(kelas_ids) = ''");
        } catch (Exception $e) {}
    }

    private function ensureTugasKelasIdsColumn() {
        try {
            $this->db->exec("ALTER TABLE tugas ADD COLUMN kelas_ids VARCHAR(255) NULL AFTER kelas_id");
        } catch (Exception $e) {}
        try {
            $this->db->exec("UPDATE tugas SET kelas_ids = CAST(kelas_id AS CHAR) WHERE kelas_ids IS NULL OR TRIM(kelas_ids) = ''");
        } catch (Exception $e) {}
    }

    // --- MATERI ---
    public function getMateri($kelas_id = null, $guru_id = null) {
        $sql = "
            SELECT m.*, map.nama_mapel, COALESCE(k.nama_kelas, 'Semua Kelas') as nama_kelas, g.nama_lengkap as nama_guru
            FROM materi m
            JOIN mata_pelajaran map ON m.mapel_id = map.id
            LEFT JOIN kelas k ON m.kelas_id = k.id
            JOIN guru g ON m.guru_id = g.id
            WHERE 1=1
        ";
        if ($kelas_id) {
            $kid = (int)$kelas_id;
            $sql .= " AND (FIND_IN_SET({$kid}, m.kelas_ids) OR m.kelas_id = {$kid} OR m.kelas_id = 0 OR m.kelas_id IS NULL OR m.kelas_ids IS NULL OR TRIM(m.kelas_ids) = '')";
        }
        if ($guru_id) {
            $sql .= " AND m.guru_id = " . (int)$guru_id;
        }
        $sql .= " ORDER BY m.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getMateriById($id) {
        $stmt = $this->db->prepare("SELECT * FROM materi WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function addMateri($guru_id, $mapel_id, $kelas_ids, $judul, $deskripsi, $jenis_file, $file_path, $youtube_url) {
        $kelasIdArray = is_array($kelas_ids) ? array_map('intval', $kelas_ids) : [(int)$kelas_ids];
        $kelasIdArray = array_values(array_filter($kelasIdArray, function($id) { return $id > 0; }));
        
        $primaryKelasId = $kelasIdArray[0] ?? 0;
        $kelasIdsStr = !empty($kelasIdArray) ? implode(',', $kelasIdArray) : (string)$primaryKelasId;

        $stmt = $this->db->prepare("
            INSERT INTO materi (guru_id, mapel_id, kelas_id, kelas_ids, judul, deskripsi, jenis_file, file_path, youtube_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$guru_id, $mapel_id, $primaryKelasId, $kelasIdsStr, $judul, $deskripsi, $jenis_file, $file_path, $youtube_url]);
    }

    public function updateMateri($id, $mapel_id, $kelas_ids, $judul, $deskripsi, $jenis_file, $file_path = null, $youtube_url = null) {
        $kelasIdArray = is_array($kelas_ids) ? array_map('intval', $kelas_ids) : [(int)$kelas_ids];
        $kelasIdArray = array_values(array_filter($kelasIdArray, function($kId) { return $kId > 0; }));
        
        $primaryKelasId = $kelasIdArray[0] ?? 0;
        $kelasIdsStr = !empty($kelasIdArray) ? implode(',', $kelasIdArray) : (string)$primaryKelasId;

        if ($file_path) {
            $stmt = $this->db->prepare("
                UPDATE materi SET mapel_id = ?, kelas_id = ?, kelas_ids = ?, judul = ?, deskripsi = ?, jenis_file = ?, file_path = ?, youtube_url = ?
                WHERE id = ?
            ");
            return $stmt->execute([$mapel_id, $primaryKelasId, $kelasIdsStr, $judul, $deskripsi, $jenis_file, $file_path, $youtube_url, $id]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE materi SET mapel_id = ?, kelas_id = ?, kelas_ids = ?, judul = ?, deskripsi = ?, jenis_file = ?, youtube_url = ?
                WHERE id = ?
            ");
            return $stmt->execute([$mapel_id, $primaryKelasId, $kelasIdsStr, $judul, $deskripsi, $jenis_file, $youtube_url, $id]);
        }
    }

    public function deleteMateri($id) {
        $stmt = $this->db->prepare("DELETE FROM materi WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- VIDEO STREAMING ---
    public function getVideos($kelas_id = null, $guru_id = null) {
        $sql = "
            SELECT 
                m.id, 
                m.guru_id, 
                m.mapel_id, 
                m.kelas_id, 
                m.judul, 
                m.deskripsi, 
                m.file_path, 
                m.youtube_url, 
                m.created_at,
                map.nama_mapel, 
                k.nama_kelas, 
                g.nama_lengkap as nama_guru
            FROM materi m
            JOIN mata_pelajaran map ON m.mapel_id = map.id
            JOIN kelas k ON m.kelas_id = k.id
            JOIN guru g ON m.guru_id = g.id
            WHERE m.youtube_url IS NOT NULL AND TRIM(m.youtube_url) != ''
        ";
        if ($kelas_id) {
            $sql .= " AND m.kelas_id = " . (int)$kelas_id;
        }
        if ($guru_id) {
            $sql .= " AND m.guru_id = " . (int)$guru_id;
        }
        $sql .= " ORDER BY m.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function addVideo($guru_id, $mapel_id, $judul, $deskripsi, $file_path, $youtube_id, $duration) {
        $stmt = $this->db->prepare("
            INSERT INTO video (guru_id, mapel_id, judul, deskripsi, file_path, youtube_id, duration)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$guru_id, $mapel_id, $judul, $deskripsi, $file_path, $youtube_id, $duration]);
    }

    // --- TUGAS ---
    public function getTugas($kelas_id = null, $guru_id = null) {
        $sql = "
            SELECT t.*, map.nama_mapel, COALESCE(k.nama_kelas, 'Semua Kelas') as nama_kelas, g.nama_lengkap as nama_guru
            FROM tugas t
            JOIN mata_pelajaran map ON t.mapel_id = map.id
            LEFT JOIN kelas k ON t.kelas_id = k.id
            JOIN guru g ON t.guru_id = g.id
            WHERE 1=1
        ";
        if ($kelas_id) {
            $kid = (int)$kelas_id;
            $sql .= " AND (
                t.kelas_id = {$kid} 
                OR t.kelas_id = 0 
                OR t.kelas_id IS NULL 
                OR (t.kelas_ids IS NOT NULL AND FIND_IN_SET({$kid}, REPLACE(t.kelas_ids, ' ', '')) > 0)
            )";
        }
        if ($guru_id) {
            $sql .= " AND t.guru_id = " . (int)$guru_id;
        }
        $sql .= " ORDER BY t.deadline ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function addTugas($guru_id, $mapel_id, $kelas_ids, $judul, $deskripsi, $file_path, $deadline) {
        $kelasIdArray = is_array($kelas_ids) ? array_map('intval', $kelas_ids) : [(int)$kelas_ids];
        $kelasIdArray = array_values(array_filter($kelasIdArray, function($id) { return $id > 0; }));
        
        $primaryKelasId = $kelasIdArray[0] ?? 0;
        $kelasIdsStr = !empty($kelasIdArray) ? implode(',', $kelasIdArray) : (string)$primaryKelasId;

        $stmt = $this->db->prepare("
            INSERT INTO tugas (guru_id, mapel_id, kelas_id, kelas_ids, judul, deskripsi, file_path, deadline)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$guru_id, $mapel_id, $primaryKelasId, $kelasIdsStr, $judul, $deskripsi, $file_path, $deadline]);
    }

    public function updateTugas($id, $mapel_id, $kelas_ids, $judul, $deskripsi, $file_path = null, $deadline = null) {
        $kelasIdArray = is_array($kelas_ids) ? array_map('intval', $kelas_ids) : [(int)$kelas_ids];
        $kelasIdArray = array_values(array_filter($kelasIdArray, function($kId) { return $kId > 0; }));
        
        $primaryKelasId = $kelasIdArray[0] ?? 0;
        $kelasIdsStr = !empty($kelasIdArray) ? implode(',', $kelasIdArray) : (string)$primaryKelasId;

        if ($file_path) {
            $stmt = $this->db->prepare("
                UPDATE tugas SET mapel_id = ?, kelas_id = ?, kelas_ids = ?, judul = ?, deskripsi = ?, file_path = ?, deadline = ?
                WHERE id = ?
            ");
            return $stmt->execute([$mapel_id, $primaryKelasId, $kelasIdsStr, $judul, $deskripsi, $file_path, $deadline, $id]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE tugas SET mapel_id = ?, kelas_id = ?, kelas_ids = ?, judul = ?, deskripsi = ?, deadline = ?
                WHERE id = ?
            ");
            return $stmt->execute([$mapel_id, $primaryKelasId, $kelasIdsStr, $judul, $deskripsi, $deadline, $id]);
        }
    }

    public function deleteTugas($id) {
        $this->db->beginTransaction();
        try {
            $stmt1 = $this->db->prepare("DELETE FROM pengumpulan_tugas WHERE tugas_id = ?");
            $stmt1->execute([$id]);

            $stmt2 = $this->db->prepare("DELETE FROM tugas WHERE id = ?");
            $stmt2->execute([$id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // --- PENGUMPULAN TUGAS ---
    public function submitTugas($tugas_id, $siswa_id, $file_path, $catatan) {
        $stmtCheck = $this->db->prepare("SELECT id FROM pengumpulan_tugas WHERE tugas_id = ? AND siswa_id = ?");
        $stmtCheck->execute([$tugas_id, $siswa_id]);
        $exist = $stmtCheck->fetch();

        if ($exist) {
            $stmt = $this->db->prepare("
                UPDATE pengumpulan_tugas SET file_path = ?, catatan_siswa = ?, submitted_at = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([$file_path, $catatan, $exist['id']]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO pengumpulan_tugas (tugas_id, siswa_id, file_path, catatan_siswa)
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([$tugas_id, $siswa_id, $file_path, $catatan]);
        }
    }

    public function getPengumpulanByTugas($tugas_id) {
        $stmt = $this->db->prepare("
            SELECT pt.*, s.nama_lengkap, s.nis, s.nisn, u.avatar
            FROM pengumpulan_tugas pt
            JOIN siswa s ON pt.siswa_id = s.id
            JOIN users u ON s.user_id = u.id
            WHERE pt.tugas_id = ?
            ORDER BY pt.submitted_at DESC
        ");
        $stmt->execute([$tugas_id]);
        return $stmt->fetchAll();
    }

    public function getPengumpulanBySiswa($siswa_id) {
        $stmt = $this->db->prepare("
            SELECT pt.*, t.judul as nama_tugas, map.nama_mapel, g.nama_lengkap as nama_guru, t.deadline
            FROM pengumpulan_tugas pt
            JOIN tugas t ON pt.tugas_id = t.id
            JOIN mata_pelajaran map ON t.mapel_id = map.id
            JOIN guru g ON t.guru_id = g.id
            WHERE pt.siswa_id = ?
            ORDER BY pt.submitted_at DESC
        ");
        $stmt->execute([$siswa_id]);
        return $stmt->fetchAll();
    }

    public function gradeTugas($pengumpulan_id, $nilai, $komentar) {
        $stmt = $this->db->prepare("
            UPDATE pengumpulan_tugas 
            SET nilai = ?, komentar_guru = ?, graded_at = NOW() 
            WHERE id = ?
        ");
        $success = $stmt->execute([$nilai, $komentar, $pengumpulan_id]);

        if ($success) {
            try {
                $stmtInfo = $this->db->prepare("
                    SELECT pt.siswa_id, t.mapel_id 
                    FROM pengumpulan_tugas pt
                    JOIN tugas t ON pt.tugas_id = t.id
                    WHERE pt.id = ?
                ");
                $stmtInfo->execute([$pengumpulan_id]);
                $info = $stmtInfo->fetch();
                if ($info) {
                    require_once ROOT_PATH . 'models/NilaiModel.php';
                    $nilaiModel = new NilaiModel();
                    $nilaiModel->syncSiswaMapelNilai((int)$info['siswa_id'], (int)$info['mapel_id']);
                }
            } catch (Exception $e) {}
        }

        return $success;
    }

    public function getTugasById($id) {
        $stmt = $this->db->prepare("
            SELECT t.*, map.nama_mapel, k.nama_kelas, g.nama_lengkap as nama_guru
            FROM tugas t
            JOIN mata_pelajaran map ON t.mapel_id = map.id
            JOIN kelas k ON t.kelas_id = k.id
            JOIN guru g ON t.guru_id = g.id
            WHERE t.id = ?
        ");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function canSiswaSubmitTugas($tugasId, $siswaId) {
        $tugas = $this->getTugasById($tugasId);
        if (!$tugas) return ['access' => false, 'reason' => 'Tugas tidak ditemukan'];

        $now = date('Y-m-d H:i:s');
        $isExpired = (!empty($tugas['deadline']) && $now > $tugas['deadline']);

        if (!$isExpired) {
            return [
                'access' => true,
                'is_expired' => false,
                'status' => 'terbuka',
                'tugas' => $tugas
            ];
        }

        $stmt = $this->db->prepare("SELECT * FROM tugas_susulan WHERE tugas_id = ? AND siswa_id = ?");
        $stmt->execute([(int)$tugasId, (int)$siswaId]);
        $susulan = $stmt->fetch();

        if ($susulan && $susulan['status'] === 'disetujui') {
            return [
                'access' => true,
                'is_expired' => true,
                'status' => 'disetujui_susulan',
                'susulan' => $susulan,
                'tugas' => $tugas
            ];
        }

        return [
            'access' => false,
            'is_expired' => true,
            'status' => $susulan['status'] ?? 'terkunci',
            'susulan' => $susulan,
            'tugas' => $tugas
        ];
    }

    public function requestTugasSusulan($tugasId, $siswaId, $catatan = '') {
        $stmt = $this->db->prepare("
            INSERT INTO tugas_susulan (tugas_id, siswa_id, status, catatan)
            VALUES (?, ?, 'pending', ?)
            ON DUPLICATE KEY UPDATE status = 'pending', catatan = VALUES(catatan), updated_at = NOW()
        ");
        return $stmt->execute([(int)$tugasId, (int)$siswaId, $catatan]);
    }

    public function getTugasSusulanRequestsByGuru($guruId) {
        $stmt = $this->db->prepare("
            SELECT ts.*, t.judul as judul_tugas, s.nama_lengkap as nama_siswa, s.nisn, 
                   COALESCE(ks.nama_kelas, k.nama_kelas, 'Umum') as nama_kelas, 
                   map.nama_mapel
            FROM tugas_susulan ts
            JOIN tugas t ON ts.tugas_id = t.id
            JOIN siswa s ON ts.siswa_id = s.id
            LEFT JOIN kelas ks ON s.kelas_id = ks.id
            LEFT JOIN kelas k ON t.kelas_id = k.id
            JOIN mata_pelajaran map ON t.mapel_id = map.id
            WHERE t.guru_id = ?
            ORDER BY ts.id DESC
        ");
        $stmt->execute([(int)$guruId]);
        return $stmt->fetchAll();
    }

    public function updateTugasSusulanStatus($requestId, $status) {
        $stmt = $this->db->prepare("UPDATE tugas_susulan SET status = ? WHERE id = ?");
        return $stmt->execute([$status, (int)$requestId]);
    }

    // --- LIVE CLASS & VIRTUAL MEETING ---
    public function ensureLiveClassTableExist() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS live_class (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    guru_id INT NOT NULL,
                    mapel_id INT NOT NULL,
                    kelas_id INT NULL,
                    topik VARCHAR(255) NOT NULL,
                    deskripsi TEXT NULL,
                    platform VARCHAR(50) DEFAULT 'embedded',
                    meeting_link VARCHAR(500) NULL,
                    room_code VARCHAR(100) NOT NULL,
                    tgl_pertemuan DATE NOT NULL,
                    jam_mulai TIME NOT NULL,
                    jam_selesai TIME NULL,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX (guru_id),
                    INDEX (kelas_id),
                    INDEX (tgl_pertemuan)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (Exception $e) {}
    }

    public function getLiveClasses($guruId = null, $kelasId = null) {
        $this->ensureLiveClassTableExist();
        $sql = "
            SELECT lc.*, map.nama_mapel, COALESCE(k.nama_kelas, 'Semua Kelas') as nama_kelas, g.nama_lengkap as nama_guru, u.avatar as avatar_guru
            FROM live_class lc
            JOIN mata_pelajaran map ON lc.mapel_id = map.id
            LEFT JOIN kelas k ON lc.kelas_id = k.id
            JOIN guru g ON lc.guru_id = g.id
            LEFT JOIN users u ON g.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if ($guruId !== null && (int)$guruId > 0) {
            $sql .= " AND lc.guru_id = ? ";
            $params[] = (int)$guruId;
        }

        if ($kelasId !== null && (int)$kelasId > 0) {
            $sql .= " AND (lc.kelas_id IS NULL OR lc.kelas_id = 0 OR lc.kelas_id = ?) ";
            $params[] = (int)$kelasId;
        }

        $sql .= " ORDER BY lc.tgl_pertemuan DESC, lc.jam_mulai DESC, lc.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getLiveClassById($id) {
        $this->ensureLiveClassTableExist();
        $stmt = $this->db->prepare("
            SELECT lc.*, map.nama_mapel, COALESCE(k.nama_kelas, 'Semua Kelas') as nama_kelas, g.nama_lengkap as nama_guru, u.avatar as avatar_guru
            FROM live_class lc
            JOIN mata_pelajaran map ON lc.mapel_id = map.id
            LEFT JOIN kelas k ON lc.kelas_id = k.id
            JOIN guru g ON lc.guru_id = g.id
            LEFT JOIN users u ON g.user_id = u.id
            WHERE lc.id = ?
        ");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function createLiveClass($guruId, $mapelId, $kelasId, $topik, $deskripsi, $platform, $meetingLink, $tglPertemuan, $jamMulai, $jamSelesai = null) {
        $this->ensureLiveClassTableExist();
        $roomCode = "SMKMH-ROOM-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        $stmt = $this->db->prepare("
            INSERT INTO live_class (guru_id, mapel_id, kelas_id, topik, deskripsi, platform, meeting_link, room_code, tgl_pertemuan, jam_mulai, jam_selesai, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            (int)$guruId,
            (int)$mapelId,
            !empty($kelasId) ? (int)$kelasId : null,
            $topik,
            $deskripsi,
            $platform ?: 'embedded',
            $meetingLink ?: null,
            $roomCode,
            $tglPertemuan,
            $jamMulai,
            $jamSelesai ?: null
        ]);
        return $this->db->lastInsertId();
    }

    public function updateLiveClass($id, $mapelId, $kelasId, $topik, $deskripsi, $platform, $meetingLink, $tglPertemuan, $jamMulai, $jamSelesai = null, $isActive = 1) {
        $this->ensureLiveClassTableExist();
        $stmt = $this->db->prepare("
            UPDATE live_class 
            SET mapel_id = ?, kelas_id = ?, topik = ?, deskripsi = ?, platform = ?, meeting_link = ?, tgl_pertemuan = ?, jam_mulai = ?, jam_selesai = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            (int)$mapelId,
            !empty($kelasId) ? (int)$kelasId : null,
            $topik,
            $deskripsi,
            $platform ?: 'embedded',
            $meetingLink ?: null,
            $tglPertemuan,
            $jamMulai,
            $jamSelesai ?: null,
            (int)$isActive,
            (int)$id
        ]);
    }

    public function deleteLiveClass($id, $guruId = null, $isAdmin = false) {
        $this->ensureLiveClassTableExist();
        $room = $this->getLiveClassById($id);
        if (!$room) return false;

        if (!$isAdmin && (int)($room['guru_id'] ?? 0) !== (int)$guruId) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM live_class WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }
}
