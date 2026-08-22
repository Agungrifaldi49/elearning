<?php
/**
 * Absensi Model
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class AbsensiModel extends BaseModel {

    public function __construct() {
        parent::__construct();
        $this->ensureAbsensiTableExist();
    }

    public function ensureAbsensiTableExist() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS absensi (
                id INT AUTO_INCREMENT PRIMARY KEY,
                jadwal_id INT NULL,
                siswa_id INT NOT NULL,
                guru_id INT NULL,
                tanggal DATE NOT NULL,
                waktu_hadir DATETIME DEFAULT CURRENT_TIMESTAMP,
                status VARCHAR(20) DEFAULT 'Hadir',
                qr_code VARCHAR(100) NULL,
                keterangan TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (siswa_id),
                INDEX (tanggal),
                INDEX (qr_code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $this->db->exec($sql);

            // Ensure jadwal_id column allows NULL for general daily QR presensi
            $this->db->exec("ALTER TABLE absensi MODIFY COLUMN jadwal_id INT NULL");

            $stmt = $this->db->query("SHOW COLUMNS FROM absensi");
            if ($stmt) {
                $rawCols = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $cols = array_map(function($c) {
                    return strtolower($c['Field'] ?? ($c['field'] ?? ''));
                }, $rawCols);

                if (!in_array('guru_id', $cols)) {
                    $this->db->exec("ALTER TABLE absensi ADD COLUMN guru_id INT NULL AFTER siswa_id");
                }
                if (!in_array('waktu_hadir', $cols)) {
                    $this->db->exec("ALTER TABLE absensi ADD COLUMN waktu_hadir DATETIME DEFAULT CURRENT_TIMESTAMP AFTER tanggal");
                }
            }
        } catch (Throwable $e) {}
    }

    public function getPresensiHariIniByGuru($guruId = null) {
        try {
            $today = date('Y-m-d');
            $sql = "SELECT a.*, s.nama_lengkap, s.nis, s.nisn, k.nama_kelas
                    FROM absensi a
                    JOIN siswa s ON a.siswa_id = s.id
                    LEFT JOIN kelas k ON s.kelas_id = k.id
                    WHERE a.tanggal = ?";
            $params = [$today];

            if ($guruId) {
                $sql .= " AND (a.guru_id = ? OR a.guru_id IS NULL)";
                $params[] = (int)$guruId;
            }

            $sql .= " ORDER BY a.waktu_hadir DESC, a.id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    public function processQrScan($identifier, $guruId = null) {
        try {
            $cleanId = trim($identifier);
            
            if (strpos($cleanId, 'SMKMH-SISWA-') === 0) {
                $cleanId = substr($cleanId, 12);
            } elseif (strpos($cleanId, 'SISWA-') === 0) {
                $cleanId = substr($cleanId, 6);
            }

            $cleanId = trim($cleanId);
            if (empty($cleanId)) {
                return ['success' => false, 'message' => 'Kode QR atau NIS/NISN tidak valid.'];
            }

            $numericId = ctype_digit($cleanId) ? (int)$cleanId : -1;
            $stmtS = $this->db->prepare("
                SELECT s.*, k.nama_kelas 
                FROM siswa s 
                LEFT JOIN kelas k ON s.kelas_id = k.id 
                WHERE s.nisn = ? OR s.nis = ? OR s.id = ? OR s.nama_lengkap LIKE ?
                LIMIT 1
            ");
            $stmtS->execute([$cleanId, $cleanId, $numericId, '%' . $cleanId . '%']);
            $siswa = $stmtS->fetch();

            if (!$siswa) {
                return ['success' => false, 'message' => "Siswa dengan NISN/NIS '$cleanId' tidak ditemukan di sistem."];
            }

            $today = date('Y-m-d');
            $now = date('Y-m-d H:i:s');

            $stmtExist = $this->db->prepare("
                SELECT * FROM absensi 
                WHERE siswa_id = ? AND tanggal = ? 
                LIMIT 1
            ");
            $stmtExist->execute([$siswa['id'], $today]);
            $exist = $stmtExist->fetch();

            if ($exist) {
                $jamHadir = date('H:i', strtotime($exist['waktu_hadir'] ?? $exist['created_at']));
                return [
                    'success' => false,
                    'already_attended' => true,
                    'nama' => $siswa['nama_lengkap'],
                    'nis' => $siswa['nis'] ?: ($siswa['nisn'] ?: '-'),
                    'kelas' => $siswa['nama_kelas'] ?: 'Tanpa Kelas',
                    'jam' => $jamHadir . ' WIB',
                    'message' => "Siswa {$siswa['nama_lengkap']} sudah dicatat HADIR hari ini pada pukul {$jamHadir} WIB."
                ];
            }

            $qrCodeVal = "QR_" . $siswa['id'] . "_" . date('YmdHis');
            $stmtIns = $this->db->prepare("
                INSERT INTO absensi (jadwal_id, siswa_id, guru_id, tanggal, waktu_hadir, status, qr_code, keterangan) 
                VALUES (NULL, ?, ?, ?, ?, 'Hadir', ?, 'Hadir via Scan QR Code Digital')
            ");
            $res = $stmtIns->execute([$siswa['id'], $guruId ? (int)$guruId : null, $today, $now, $qrCodeVal]);

            if ($res) {
                $jamHadir = date('H:i', strtotime($now));
                return [
                    'success' => true,
                    'nama' => $siswa['nama_lengkap'],
                    'nis' => $siswa['nis'] ?: ($siswa['nisn'] ?: '-'),
                    'kelas' => $siswa['nama_kelas'] ?: 'Tanpa Kelas',
                    'jam' => $jamHadir . ' WIB',
                    'message' => "Presensi {$siswa['nama_lengkap']} ({$siswa['nama_kelas']}) berhasil dicatat!"
                ];
            } else {
                return ['success' => false, 'message' => 'Gagal menyimpan data presensi ke database.'];
            }
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem database: ' . $e->getMessage()];
        }
    }

    public function recordAttendance($jadwal_id, $siswa_id, $tanggal, $status, $keterangan = '') {
        $qrCode = "ATT_" . $jadwal_id . "_" . $siswa_id . "_" . date('Ymd');
        $stmtExist = $this->db->prepare("SELECT id FROM absensi WHERE jadwal_id = ? AND siswa_id = ? AND tanggal = ?");
        $stmtExist->execute([$jadwal_id, $siswa_id, $tanggal]);
        $exist = $stmtExist->fetch();

        if ($exist) {
            $stmt = $this->db->prepare("UPDATE absensi SET status = ?, keterangan = ? WHERE id = ?");
            return $stmt->execute([$status, $keterangan, $exist['id']]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO absensi (jadwal_id, siswa_id, tanggal, status, qr_code, keterangan) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$jadwal_id, $siswa_id, $tanggal, $status, $qrCode, $keterangan]);
        }
    }

    public function getRecap($jadwal_id, $tanggal) {
        $kelasId = null;
        $mapelId = null;
        $guruId = null;

        $stmtJ = $this->db->prepare("SELECT kelas_id, mapel_id, guru_id FROM jadwal WHERE id = ?");
        $stmtJ->execute([$jadwal_id]);
        $jData = $stmtJ->fetch();

        if ($jData) {
            $kelasId = $jData['kelas_id'];
            $mapelId = $jData['mapel_id'];
            $guruId = $jData['guru_id'];
        } else {
            $stmtK = $this->db->prepare("SELECT mapel_id, guru_id, kelas_id FROM mapel_enrollment_keys WHERE id = ?");
            $stmtK->execute([$jadwal_id]);
            $kData = $stmtK->fetch();
            if ($kData) {
                $kelasId = $kData['kelas_id'];
                $mapelId = $kData['mapel_id'];
                $guruId = $kData['guru_id'];
            }
        }

        if ($kelasId) {
            $stmt = $this->db->prepare("
                SELECT s.id as siswa_id, s.nama_lengkap, s.nis, s.nisn, a.status, a.keterangan, a.created_at
                FROM siswa s
                LEFT JOIN absensi a ON s.id = a.siswa_id AND a.jadwal_id = ? AND a.tanggal = ?
                WHERE s.kelas_id = ?
                ORDER BY s.nama_lengkap ASC
            ");
            $stmt->execute([$jadwal_id, $tanggal, $kelasId]);
            return $stmt->fetchAll();
        } elseif ($mapelId && $guruId) {
            $stmt = $this->db->prepare("
                SELECT s.id as siswa_id, s.nama_lengkap, s.nis, s.nisn, a.status, a.keterangan, a.created_at
                FROM siswa s
                JOIN siswa_mapel_enrollment sme ON s.id = sme.siswa_id AND sme.mapel_id = ? AND sme.guru_id = ?
                LEFT JOIN absensi a ON s.id = a.siswa_id AND a.jadwal_id = ? AND a.tanggal = ?
                ORDER BY s.nama_lengkap ASC
            ");
            $stmt->execute([$mapelId, $guruId, $jadwal_id, $tanggal]);
            return $stmt->fetchAll();
        } else {
            $stmt = $this->db->prepare("
                SELECT s.id as siswa_id, s.nama_lengkap, s.nis, s.nisn, a.status, a.keterangan, a.created_at
                FROM siswa s
                LEFT JOIN absensi a ON s.id = a.siswa_id AND a.jadwal_id = ? AND a.tanggal = ?
                WHERE s.kelas_id IS NOT NULL
                ORDER BY s.nama_lengkap ASC
            ");
            $stmt->execute([$jadwal_id, $tanggal]);
            return $stmt->fetchAll();
        }
    }
}
