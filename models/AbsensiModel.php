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
                waktu_masuk DATETIME NULL,
                waktu_pulang DATETIME NULL,
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
                if (!in_array('waktu_masuk', $cols)) {
                    $this->db->exec("ALTER TABLE absensi ADD COLUMN waktu_masuk DATETIME NULL AFTER tanggal");
                }
                if (!in_array('waktu_pulang', $cols)) {
                    $this->db->exec("ALTER TABLE absensi ADD COLUMN waktu_pulang DATETIME NULL AFTER waktu_masuk");
                }
                if (!in_array('waktu_hadir', $cols)) {
                    $this->db->exec("ALTER TABLE absensi ADD COLUMN waktu_hadir DATETIME DEFAULT CURRENT_TIMESTAMP AFTER waktu_pulang");
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

            $sql .= " ORDER BY COALESCE(a.waktu_pulang, a.waktu_masuk, a.waktu_hadir) DESC, a.id DESC";
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
                if (!empty($exist['waktu_pulang'])) {
                    $jamMasuk = date('H:i', strtotime($exist['waktu_masuk'] ?? $exist['waktu_hadir'] ?? $exist['created_at']));
                    $jamPulang = date('H:i', strtotime($exist['waktu_pulang']));
                    return [
                        'success' => false,
                        'already_attended' => true,
                        'nama' => $siswa['nama_lengkap'],
                        'nis' => $siswa['nis'] ?: ($siswa['nisn'] ?: '-'),
                        'kelas' => $siswa['nama_kelas'] ?: 'Tanpa Kelas',
                        'jam_masuk' => $jamMasuk . ' WIB',
                        'jam_pulang' => $jamPulang . ' WIB',
                        'message' => "Presensi siswa {$siswa['nama_lengkap']} sudah LENGKAP hari ini (Masuk: {$jamMasuk} WIB, Pulang: {$jamPulang} WIB)."
                    ];
                } else {
                    // Record Presensi Pulang
                    $stmtUpd = $this->db->prepare("
                        UPDATE absensi 
                        SET waktu_pulang = ?, keterangan = 'Presensi Masuk & Pulang Digital' 
                        WHERE id = ?
                    ");
                    $stmtUpd->execute([$now, $exist['id']]);

                    $jamMasuk = date('H:i', strtotime($exist['waktu_masuk'] ?? $exist['waktu_hadir'] ?? $exist['created_at']));
                    $jamPulang = date('H:i', strtotime($now));

                    return [
                        'success' => true,
                        'type' => 'pulang',
                        'nama' => $siswa['nama_lengkap'],
                        'nis' => $siswa['nis'] ?: ($siswa['nisn'] ?: '-'),
                        'kelas' => $siswa['nama_kelas'] ?: 'Tanpa Kelas',
                        'jam' => $jamPulang . ' WIB',
                        'jam_masuk' => $jamMasuk . ' WIB',
                        'jam_pulang' => $jamPulang . ' WIB',
                        'message' => "Presensi PULANG {$siswa['nama_lengkap']} ({$siswa['nama_kelas']}) berhasil dicatat pukul {$jamPulang} WIB! (Jam Masuk: {$jamMasuk} WIB)."
                    ];
                }
            }

            // New Presensi Masuk
            $qrCodeVal = "QR_" . $siswa['id'] . "_" . date('YmdHis');
            $stmtIns = $this->db->prepare("
                INSERT INTO absensi (jadwal_id, siswa_id, guru_id, tanggal, waktu_masuk, waktu_hadir, status, qr_code, keterangan) 
                VALUES (NULL, ?, ?, ?, ?, ?, 'Hadir', ?, 'Presensi Masuk Digital')
            ");
            $res = $stmtIns->execute([$siswa['id'], $guruId ? (int)$guruId : null, $today, $now, $now, $qrCodeVal]);

            if ($res) {
                $jamMasuk = date('H:i', strtotime($now));
                return [
                    'success' => true,
                    'type' => 'masuk',
                    'nama' => $siswa['nama_lengkap'],
                    'nis' => $siswa['nis'] ?: ($siswa['nisn'] ?: '-'),
                    'kelas' => $siswa['nama_kelas'] ?: 'Tanpa Kelas',
                    'jam' => $jamMasuk . ' WIB',
                    'jam_masuk' => $jamMasuk . ' WIB',
                    'message' => "Presensi MASUK {$siswa['nama_lengkap']} ({$siswa['nama_kelas']}) berhasil dicatat pukul {$jamMasuk} WIB! Otomatis HADIR di seluruh KBM mapel hari ini."
                ];
            } else {
                return ['success' => false, 'message' => 'Gagal menyimpan data presensi ke database.'];
            }
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem database: ' . $e->getMessage()];
        }
    }

    public function recordAttendance($jadwal_id, $siswa_id, $tanggal, $status, $keterangan = '') {
        $jadwal_id = (int)$jadwal_id;
        $siswa_id = (int)$siswa_id;

        $stmtExist = $this->db->prepare("
            SELECT id FROM absensi 
            WHERE siswa_id = ? AND tanggal = ? AND (jadwal_id = ? OR jadwal_id IS NULL)
            ORDER BY (jadwal_id IS NOT NULL) DESC, id DESC LIMIT 1
        ");
        $stmtExist->execute([$siswa_id, $tanggal, $jadwal_id]);
        $exist = $stmtExist->fetch();

        if ($exist) {
            $stmt = $this->db->prepare("UPDATE absensi SET jadwal_id = ?, status = ?, keterangan = ? WHERE id = ?");
            return $stmt->execute([$jadwal_id, $status, $keterangan, $exist['id']]);
        } else {
            $now = date('Y-m-d H:i:s');
            $qrCode = "ATT_" . $jadwal_id . "_" . $siswa_id . "_" . date('Ymd');
            $stmt = $this->db->prepare("INSERT INTO absensi (jadwal_id, siswa_id, tanggal, waktu_masuk, waktu_hadir, status, qr_code, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$jadwal_id, $siswa_id, $tanggal, $now, $now, $status, $qrCode, $keterangan]);
        }
    }

    public function getRecap($jadwal_id, $tanggal) {
        $kelasId = null;
        $mapelId = null;
        $guruId = null;

        $stmtJ = $this->db->prepare("SELECT kelas_id, mapel_id, guru_id FROM jadwal WHERE id = ?");
        $stmtJ->execute([(int)$jadwal_id]);
        $jData = $stmtJ->fetch();

        if ($jData) {
            $kelasId = $jData['kelas_id'];
            $mapelId = $jData['mapel_id'];
            $guruId = $jData['guru_id'];
        } else {
            $stmtK = $this->db->prepare("SELECT mapel_id, guru_id, kelas_id FROM mapel_enrollment_keys WHERE id = ?");
            $stmtK->execute([(int)$jadwal_id]);
            $kData = $stmtK->fetch();
            if ($kData) {
                $kelasId = $kData['kelas_id'];
                $mapelId = $kData['mapel_id'];
                $guruId = $kData['guru_id'];
            }
        }

        if ($kelasId) {
            $stmt = $this->db->prepare("
                SELECT s.id as siswa_id, s.nama_lengkap, s.nis, s.nisn, 
                       a.status, a.keterangan, a.created_at, a.waktu_hadir, a.waktu_masuk, a.waktu_pulang, a.qr_code
                FROM siswa s
                LEFT JOIN absensi a ON s.id = a.siswa_id AND a.tanggal = ?
                WHERE s.kelas_id = ?
                GROUP BY s.id
                ORDER BY s.nama_lengkap ASC
            ");
            $stmt->execute([$tanggal, $kelasId]);
            return $stmt->fetchAll();
        } elseif ($mapelId && $guruId) {
            $stmt = $this->db->prepare("
                SELECT s.id as siswa_id, s.nama_lengkap, s.nis, s.nisn, 
                       a.status, a.keterangan, a.created_at, a.waktu_hadir, a.waktu_masuk, a.waktu_pulang, a.qr_code
                FROM siswa s
                JOIN siswa_mapel_enrollment sme ON s.id = sme.siswa_id AND sme.mapel_id = ? AND sme.guru_id = ?
                LEFT JOIN absensi a ON s.id = a.siswa_id AND a.tanggal = ?
                GROUP BY s.id
                ORDER BY s.nama_lengkap ASC
            ");
            $stmt->execute([$mapelId, $guruId, $tanggal]);
            return $stmt->fetchAll();
        } else {
            $stmt = $this->db->prepare("
                SELECT s.id as siswa_id, s.nama_lengkap, s.nis, s.nisn, 
                       a.status, a.keterangan, a.created_at, a.waktu_hadir, a.waktu_masuk, a.waktu_pulang, a.qr_code
                FROM siswa s
                LEFT JOIN absensi a ON s.id = a.siswa_id AND a.tanggal = ?
                WHERE s.kelas_id IS NOT NULL
                GROUP BY s.id
                ORDER BY s.nama_lengkap ASC
            ");
            $stmt->execute([$tanggal]);
            return $stmt->fetchAll();
        }
    }
}
