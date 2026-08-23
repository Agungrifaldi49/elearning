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

            $sqlGuru = "CREATE TABLE IF NOT EXISTS absensi_guru (
                id INT AUTO_INCREMENT PRIMARY KEY,
                guru_id INT NOT NULL,
                tanggal DATE NOT NULL,
                waktu_masuk DATETIME NULL,
                waktu_pulang DATETIME NULL,
                waktu_hadir DATETIME DEFAULT CURRENT_TIMESTAMP,
                status VARCHAR(20) DEFAULT 'Hadir',
                qr_code VARCHAR(100) NULL,
                keterangan TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (guru_id),
                INDEX (tanggal),
                INDEX (qr_code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $this->db->exec($sqlGuru);

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
        return $this->getPresensiHariIniAll();
    }

    public function verifySchoolScheduleToday($siswaId = null, $todayDate = null, $nowTime = null) {
        if (!$todayDate) $todayDate = date('Y-m-d');
        if (!$nowTime) $nowTime = date('H:i:s');

        // 1. Check Kalender Akademik Holidays (kalender.json)
        $kalenderPath = ROOT_PATH . 'config/kalender.json';
        if (file_exists($kalenderPath)) {
            $events = json_decode(file_get_contents($kalenderPath), true) ?: [];
            foreach ($events as $ev) {
                $type = strtolower($ev['type'] ?? '');
                $tglMulai = $ev['tanggal'] ?? '';
                $tglAkhir = !empty($ev['tanggal_akhir']) ? $ev['tanggal_akhir'] : $tglMulai;

                if ($todayDate >= $tglMulai && $todayDate <= $tglAkhir) {
                    if ($type === 'libur' || strpos(strtolower($ev['title'] ?? ''), 'libur') !== false || strpos(strtolower($ev['title'] ?? ''), 'tanggal merah') !== false) {
                        return [
                            'allowed' => false,
                            'reason' => 'holiday',
                            'message' => "Bukan jadwal masuk sekolah! Hari ini ({$ev['title']}) dinyatakan LIBUR pada Kalender Akademik Sekolah. Pemindaian QR Code presensi tidak dapat dilakukan."
                        ];
                    }
                }
            }
        }

        // 2. Map day number to Indonesian day name
        $daysMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu'
        ];
        $dayNum = date('N', strtotime($todayDate));
        $todayDayName = $daysMap[$dayNum] ?? 'Senin';

        // 3. Query if there is ANY active schedule in `jadwal` table for $todayDayName
        $stmtJ = $this->db->prepare("
            SELECT COUNT(*) as total_jadwal, MIN(jam_mulai) as min_start, MAX(jam_selesai) as max_end 
            FROM jadwal 
            WHERE hari = ?
        ");
        $stmtJ->execute([$todayDayName]);
        $jStat = $stmtJ->fetch();

        $studentClassHasSchedule = true;
        if ($siswaId) {
            $stmtSiswaClass = $this->db->prepare("
                SELECT s.kelas_id, k.nama_kelas 
                FROM siswa s 
                LEFT JOIN kelas k ON s.kelas_id = k.id 
                WHERE s.id = ?
            ");
            $stmtSiswaClass->execute([(int)$siswaId]);
            $sData = $stmtSiswaClass->fetch();

            if ($sData && !empty($sData['kelas_id'])) {
                $stmtClassJadwal = $this->db->prepare("
                    SELECT COUNT(*) as cnt 
                    FROM jadwal 
                    WHERE kelas_id = ? AND hari = ?
                ");
                $stmtClassJadwal->execute([(int)$sData['kelas_id'], $todayDayName]);
                $cStat = $stmtClassJadwal->fetch();

                if ((int)($cStat['cnt'] ?? 0) === 0 && (int)($jStat['total_jadwal'] ?? 0) > 0) {
                    $studentClassHasSchedule = false;
                }
            }
        }

        if ((int)($jStat['total_jadwal'] ?? 0) === 0) {
            return [
                'allowed' => false,
                'reason' => 'no_schedule_day',
                'message' => "Bukan jadwal masuk sekolah! Tidak ada jadwal KBM sekolah pada hari $todayDayName ($todayDate). Kemungkinan hari libur sekolah atau akhir pekan."
            ];
        }

        if (!$studentClassHasSchedule) {
            return [
                'allowed' => false,
                'reason' => 'no_class_schedule',
                'message' => "Bukan jadwal KBM rombel! Kelas siswa tidak memiliki jadwal mata pelajaran pada hari $todayDayName ($todayDate)."
            ];
        }

        // 4. Operating Hours Buffer Check (05:30 WIB to 18:30 WIB or based on min/max schedule time with buffer)
        $minStart = $jStat['min_start'] ? date('H:i:s', strtotime($jStat['min_start'] . ' -60 minutes')) : '06:00:00';
        $maxEnd = $jStat['max_end'] ? date('H:i:s', strtotime($jStat['max_end'] . ' +120 minutes')) : '18:00:00';

        if (strtotime($minStart) > strtotime('06:00:00')) $minStart = '06:00:00';
        if (strtotime($maxEnd) < strtotime('18:00:00')) $maxEnd = '18:00:00';

        if ($nowTime < $minStart || $nowTime > $maxEnd) {
            $formatStart = date('H:i', strtotime($minStart));
            $formatEnd = date('H:i', strtotime($maxEnd));
            return [
                'allowed' => false,
                'reason' => 'out_of_hours',
                'message' => "Bukan jam masuk sekolah! Pemindaian di luar jam operasional presensi resmi ($formatStart s/d $formatEnd WIB)."
            ];
        }

        return [
            'allowed' => true,
            'day_name' => $todayDayName,
            'min_start' => $minStart,
            'max_end' => $maxEnd
        ];
    }

    public function getPresensiHariIniAll() {
        try {
            $today = date('Y-m-d');

            $stmtS = $this->db->prepare("
                SELECT a.id, 'Siswa' as role_label, a.tanggal, a.waktu_masuk, a.waktu_pulang, a.waktu_hadir, a.status, a.keterangan,
                       s.nama_lengkap, s.nis, s.nisn, k.nama_kelas
                FROM absensi a
                JOIN siswa s ON a.siswa_id = s.id
                LEFT JOIN kelas k ON s.kelas_id = k.id
                WHERE a.tanggal = ?
            ");
            $stmtS->execute([$today]);
            $sLogs = $stmtS->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $stmtG = $this->db->prepare("
                SELECT ag.id, 'Guru' as role_label, ag.tanggal, ag.waktu_masuk, ag.waktu_pulang, ag.waktu_hadir, ag.status, ag.keterangan,
                       g.nama_lengkap, g.nip as nis, g.nip as nisn, 'GTK / Pendidik' as nama_kelas
                FROM absensi_guru ag
                JOIN guru g ON ag.guru_id = g.id
                WHERE ag.tanggal = ?
            ");
            $stmtG->execute([$today]);
            $gLogs = $stmtG->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $logs = array_merge($sLogs, $gLogs);
            usort($logs, function($a, $b) {
                $tA = strtotime($a['waktu_pulang'] ?? $a['waktu_masuk'] ?? $a['waktu_hadir'] ?? $a['tanggal']);
                $tB = strtotime($b['waktu_pulang'] ?? $b['waktu_masuk'] ?? $b['waktu_hadir'] ?? $b['tanggal']);
                return $tB <=> $tA;
            });

            return $logs;
        } catch (Throwable $e) {
            return [];
        }
    }

    public function processQrScan($identifier, $guruId = null, $allowGuruScan = false) {
        try {
            $cleanId = trim($identifier);
            $rawId = $cleanId;

            $isGuruPrefix = (strpos(strtoupper($cleanId), 'SMKMH-GURU-') === 0 || strpos(strtoupper($cleanId), 'GURU-') === 0);

            if (strpos(strtoupper($cleanId), 'SMKMH-SISWA-') === 0) {
                $cleanId = substr($cleanId, 12);
            } elseif (strpos(strtoupper($cleanId), 'SISWA-') === 0) {
                $cleanId = substr($cleanId, 6);
            } elseif (strpos(strtoupper($cleanId), 'SMKMH-GURU-') === 0) {
                $cleanId = substr($cleanId, 11);
            } elseif (strpos(strtoupper($cleanId), 'GURU-') === 0) {
                $cleanId = substr($cleanId, 5);
            }

            $cleanId = trim($cleanId);
            if (empty($cleanId)) {
                return ['success' => false, 'message' => 'Kode QR atau Identitas tidak valid.'];
            }

            // Check if scanning a Guru/GTK
            $guru = null;
            if ($isGuruPrefix || $allowGuruScan) {
                $numericId = ctype_digit($cleanId) ? (int)$cleanId : -1;
                $stmtG = $this->db->prepare("
                    SELECT g.*, u.full_name
                    FROM guru g
                    LEFT JOIN users u ON g.user_id = u.id
                    WHERE g.nip = ? OR g.id = ? OR g.nama_lengkap LIKE ?
                    LIMIT 1
                ");
                $stmtG->execute([$cleanId, $numericId, '%' . $cleanId . '%']);
                $guru = $stmtG->fetch();
            }

            if ($guru) {
                if (!$allowGuruScan) {
                    return [
                        'success' => false,
                        'message' => "Pemindaian QR Code Guru ({$guru['nama_lengkap']}) tidak diizinkan pada scanner Guru. Presensi Guru hanya dapat dipindai melalui Scanner Admin Sekolah."
                    ];
                }

                // Process Teacher Presensi in absensi_guru
                $today = date('Y-m-d');
                $now = date('Y-m-d H:i:s');

                $stmtExistG = $this->db->prepare("
                    SELECT * FROM absensi_guru 
                    WHERE guru_id = ? AND tanggal = ? 
                    LIMIT 1
                ");
                $stmtExistG->execute([$guru['id'], $today]);
                $existG = $stmtExistG->fetch();

                if ($existG) {
                    if (!empty($existG['waktu_pulang'])) {
                        $jamMasuk = date('H:i', strtotime($existG['waktu_masuk'] ?? $existG['waktu_hadir'] ?? $existG['created_at']));
                        $jamPulang = date('H:i', strtotime($existG['waktu_pulang']));
                        return [
                            'success' => false,
                            'already_attended' => true,
                            'role' => 'Guru',
                            'nama' => $guru['nama_lengkap'],
                            'nis' => $guru['nip'] ?: '-',
                            'kelas' => 'GTK / Pendidik',
                            'jam_masuk' => $jamMasuk . ' WIB',
                            'jam_pulang' => $jamPulang . ' WIB',
                            'message' => "Presensi Guru {$guru['nama_lengkap']} sudah LENGKAP hari ini (Masuk: {$jamMasuk} WIB, Pulang: {$jamPulang} WIB)."
                        ];
                    } else {
                        // Record Guru Pulang
                        $stmtUpdG = $this->db->prepare("
                            UPDATE absensi_guru 
                            SET waktu_pulang = ?, keterangan = 'Presensi Guru Masuk & Pulang Digital' 
                            WHERE id = ?
                        ");
                        $stmtUpdG->execute([$now, $existG['id']]);

                        $jamMasuk = date('H:i', strtotime($existG['waktu_masuk'] ?? $existG['waktu_hadir'] ?? $existG['created_at']));
                        $jamPulang = date('H:i', strtotime($now));

                        return [
                            'success' => true,
                            'type' => 'pulang',
                            'role' => 'Guru',
                            'nama' => $guru['nama_lengkap'],
                            'nis' => $guru['nip'] ?: '-',
                            'kelas' => 'GTK / Pendidik',
                            'jam' => $jamPulang . ' WIB',
                            'jam_masuk' => $jamMasuk . ' WIB',
                            'jam_pulang' => $jamPulang . ' WIB',
                            'message' => "Presensi PULANG GURU/GTK ({$guru['nama_lengkap']}) berhasil dicatat pukul {$jamPulang} WIB! (Masuk: {$jamMasuk} WIB)."
                        ];
                    }
                }

                // New Guru Presensi Masuk
                $qrCodeVal = "QR_GURU_" . $guru['id'] . "_" . date('YmdHis');
                $stmtInsG = $this->db->prepare("
                    INSERT INTO absensi_guru (guru_id, tanggal, waktu_masuk, waktu_hadir, status, qr_code, keterangan) 
                    VALUES (?, ?, ?, ?, 'Hadir', ?, 'Presensi Guru Masuk Digital')
                ");
                $resG = $stmtInsG->execute([$guru['id'], $today, $now, $now, $qrCodeVal]);

                if ($resG) {
                    $jamMasuk = date('H:i', strtotime($now));
                    return [
                        'success' => true,
                        'type' => 'masuk',
                        'role' => 'Guru',
                        'nama' => $guru['nama_lengkap'],
                        'nis' => $guru['nip'] ?: '-',
                        'kelas' => 'GTK / Pendidik',
                        'jam' => $jamMasuk . ' WIB',
                        'jam_masuk' => $jamMasuk . ' WIB',
                        'message' => "Presensi MASUK GURU/GTK ({$guru['nama_lengkap']}) berhasil dicatat pukul {$jamMasuk} WIB!"
                    ];
                }
            }

            // Otherwise, search for Siswa
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
                return ['success' => false, 'message' => "Siswa / Guru dengan NISN/NIS/NIP '$cleanId' tidak ditemukan di sistem."];
            }

            $today = date('Y-m-d');
            $now = date('Y-m-d H:i:s');
            $currentTime = date('H:i:s');

            // Strict Academic Schedule & Holiday Verification
            $schedCheck = $this->verifySchoolScheduleToday($siswa['id'], $today, $currentTime);
            if (!$schedCheck['allowed']) {
                return [
                    'success' => false,
                    'is_not_scheduled' => true,
                    'reason' => $schedCheck['reason'],
                    'message' => $schedCheck['message']
                ];
            }

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

    public function getRecapGuru($tanggal = null) {
        if (!$tanggal) $tanggal = date('Y-m-d');
        try {
            $stmt = $this->db->prepare("
                SELECT g.id as guru_id, g.nip, g.nama_lengkap, g.status as status_guru,
                       ag.id as absensi_id, ag.tanggal, ag.waktu_masuk, ag.waktu_pulang, ag.waktu_hadir, ag.status, ag.qr_code, ag.keterangan
                FROM guru g
                LEFT JOIN absensi_guru ag ON g.id = ag.guru_id AND ag.tanggal = ?
                WHERE g.status = 'aktif'
                ORDER BY g.nama_lengkap ASC
            ");
            $stmt->execute([$tanggal]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }
}
