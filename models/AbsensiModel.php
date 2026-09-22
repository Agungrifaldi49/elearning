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

            // Auto-migrate columns for absensi_guru (Selfie & Geofencing GPS)
            $stmtG = $this->db->query("SHOW COLUMNS FROM absensi_guru");
            if ($stmtG) {
                $rawColsG = $stmtG->fetchAll(PDO::FETCH_ASSOC);
                $colsG = array_map(function($c) {
                    return strtolower($c['Field'] ?? ($c['field'] ?? ''));
                }, $rawColsG);

                if (!in_array('foto_masuk', $colsG)) {
                    $this->db->exec("ALTER TABLE absensi_guru ADD COLUMN foto_masuk VARCHAR(255) NULL AFTER waktu_masuk");
                }
                if (!in_array('foto_pulang', $colsG)) {
                    $this->db->exec("ALTER TABLE absensi_guru ADD COLUMN foto_pulang VARCHAR(255) NULL AFTER waktu_pulang");
                }
                if (!in_array('latitude_masuk', $colsG)) {
                    $this->db->exec("ALTER TABLE absensi_guru ADD COLUMN latitude_masuk DECIMAL(10, 8) NULL AFTER foto_masuk");
                }
                if (!in_array('longitude_masuk', $colsG)) {
                    $this->db->exec("ALTER TABLE absensi_guru ADD COLUMN longitude_masuk DECIMAL(11, 8) NULL AFTER latitude_masuk");
                }
                if (!in_array('latitude_pulang', $colsG)) {
                    $this->db->exec("ALTER TABLE absensi_guru ADD COLUMN latitude_pulang DECIMAL(10, 8) NULL AFTER foto_pulang");
                }
                if (!in_array('longitude_pulang', $colsG)) {
                    $this->db->exec("ALTER TABLE absensi_guru ADD COLUMN longitude_pulang DECIMAL(11, 8) NULL AFTER latitude_pulang");
                }
                if (!in_array('jarak_masuk_meter', $colsG)) {
                    $this->db->exec("ALTER TABLE absensi_guru ADD COLUMN jarak_masuk_meter INT NULL AFTER longitude_masuk");
                }
                if (!in_array('jarak_pulang_meter', $colsG)) {
                    $this->db->exec("ALTER TABLE absensi_guru ADD COLUMN jarak_pulang_meter INT NULL AFTER longitude_pulang");
                }
                if (!in_array('tipe_presensi', $colsG)) {
                    $this->db->exec("ALTER TABLE absensi_guru ADD COLUMN tipe_presensi VARCHAR(30) DEFAULT 'selfie' AFTER status");
                }
            }
        } catch (Throwable $e) {}
    }

    public function getPresensiHariIniByGuru($guruId = null) {
        if (!$guruId) {
            return $this->getPresensiHariIniAll();
        }
        try {
            $today = date('Y-m-d');
            $gId = (int)$guruId;
            $stmtS = $this->db->prepare("
                SELECT DISTINCT a.id, 'Siswa' as role_label, a.tanggal, a.waktu_masuk, a.waktu_pulang, a.waktu_hadir, a.status, a.keterangan,
                       s.nama_lengkap, s.nis, s.nisn, k.nama_kelas
                FROM absensi a
                JOIN siswa s ON a.siswa_id = s.id
                LEFT JOIN kelas k ON s.kelas_id = k.id
                WHERE a.tanggal = ? AND (
                    a.guru_id = ? 
                    OR s.kelas_id IN (SELECT kelas_id FROM jadwal WHERE guru_id = ?)
                    OR s.kelas_id IN (SELECT kelas_id FROM mapel_enrollment_keys WHERE guru_id = ?)
                    OR s.id IN (SELECT siswa_id FROM siswa_mapel_enrollment WHERE guru_id = ?)
                )
                ORDER BY a.waktu_hadir DESC, a.id DESC
            ");
            $stmtS->execute([$today, $gId, $gId, $gId, $gId]);
            return $stmtS->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
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
        $classMinStart = null;
        $classMaxEnd = null;
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
                    SELECT COUNT(*) as cnt, MIN(jam_mulai) as class_min_start, MAX(jam_selesai) as class_max_end 
                    FROM jadwal 
                    WHERE kelas_id = ? AND hari = ?
                ");
                $stmtClassJadwal->execute([(int)$sData['kelas_id'], $todayDayName]);
                $cStat = $stmtClassJadwal->fetch();

                if ((int)($cStat['cnt'] ?? 0) === 0 && (int)($jStat['total_jadwal'] ?? 0) > 0) {
                    $studentClassHasSchedule = false;
                } else {
                    $classMinStart = $cStat['class_min_start'] ?? null;
                    $classMaxEnd = $cStat['class_max_end'] ?? null;
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

        $effectiveMaxEnd = $classMaxEnd ?: ($jStat['max_end'] ?: '14:00:00');
        // Allow exit scan starting 10 minutes before max KBM end
        $allowExitStart = date('H:i:s', strtotime($effectiveMaxEnd . ' -10 minutes'));

        return [
            'allowed' => true,
            'day_name' => $todayDayName,
            'min_start' => $minStart,
            'max_end' => $maxEnd,
            'class_max_end' => $effectiveMaxEnd,
            'allow_exit_start' => $allowExitStart
        ];
    }

    public function verifyTeacherScheduleToday($guruId, $todayDate = null, $nowTime = null) {
        if (!$todayDate) $todayDate = date('Y-m-d');
        if (!$nowTime) $nowTime = date('H:i:s');

        // 1. Kalender Akademik Holiday Check
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
                            'message' => "Bukan jadwal mengajar! Hari ini ({$ev['title']}) dinyatakan LIBUR pada Kalender Akademik Sekolah."
                        ];
                    }
                }
            }
        }

        // 2. Day Mapping
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

        // 3. Query teacher's teaching schedule for today from `jadwal`
        $stmtJ = $this->db->prepare("
            SELECT COUNT(*) as total_jadwal, MIN(jam_mulai) as min_start, MAX(jam_selesai) as max_end 
            FROM jadwal 
            WHERE guru_id = ? AND hari = ?
        ");
        $stmtJ->execute([(int)$guruId, $todayDayName]);
        $gStat = $stmtJ->fetch();

        // Check if school has any KBM schedule today
        $stmtSchoolDay = $this->db->prepare("SELECT COUNT(*) as cnt FROM jadwal WHERE hari = ?");
        $stmtSchoolDay->execute([$todayDayName]);
        $schoolDayStat = $stmtSchoolDay->fetch();
        $hasSchoolScheduleToday = ((int)($schoolDayStat['cnt'] ?? 0) > 0);

        if ((int)($gStat['total_jadwal'] ?? 0) === 0) {
            if ($todayDayName === 'Minggu' || !$hasSchoolScheduleToday) {
                return [
                    'allowed' => false,
                    'reason' => 'no_schedule_today',
                    'message' => "Bukan jadwal mengajar! Hari $todayDayName ($todayDate) adalah hari libur KBM sekolah. Pemindaian presensi tidak dapat dilakukan."
                ];
            } else {
                return [
                    'allowed' => false,
                    'reason' => 'no_schedule_today',
                    'message' => "Bukan jadwal mengajar! Bpk/Ibu Guru tidak memiliki jadwal KBM mengajar pada hari $todayDayName ($todayDate)."
                ];
            }
        }

        // Determine schedule bounds
        $minStart = (!empty($gStat['min_start'])) ? date('H:i:s', strtotime($gStat['min_start'])) : '07:00:00';
        $maxEnd = (!empty($gStat['max_end'])) ? date('H:i:s', strtotime($gStat['max_end'])) : '15:00:00';

        // Buffer: Allowed to scan entry 60 mins before first class (or min 06:00 WIB)
        $allowEntryStart = date('H:i:s', strtotime($minStart . ' -60 minutes'));
        if (strtotime($allowEntryStart) > strtotime('06:00:00')) $allowEntryStart = '06:00:00';

        // Exit buffer: Allowed to scan exit 10 mins before last class ends (or after last class ends)
        $allowExitStart = date('H:i:s', strtotime($maxEnd . ' -10 minutes'));

        return [
            'allowed' => true,
            'day_name' => $todayDayName,
            'total_jadwal' => (int)($gStat['total_jadwal'] ?? 0),
            'min_start' => $minStart,
            'max_end' => $maxEnd,
            'allow_entry_start' => $allowEntryStart,
            'allow_exit_start' => $allowExitStart
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
                       ag.foto_masuk, ag.foto_pulang, ag.latitude_masuk, ag.longitude_masuk, ag.latitude_pulang, ag.longitude_pulang,
                       ag.jarak_masuk_meter, ag.jarak_pulang_meter, ag.tipe_presensi,
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
                        'message' => "Akses Ditolak! Pemindaian QR Code Guru ({$guru['nama_lengkap']}) tidak diizinkan pada halaman ini. Halaman Scanner Guru ini KHUSUS untuk memindai presensi Siswa!"
                    ];
                }

                // Anti-Self-Scan Security Check
                if (class_exists('AuthHelper')) {
                    $currentUser = AuthHelper::user();
                    if (!empty($currentUser['id']) && !empty($guru['user_id']) && (int)$currentUser['id'] === (int)$guru['user_id']) {
                        return [
                            'success' => false,
                            'message' => "Kecurangan terdeteksi! Anda tidak diizinkan memindai QR Code milik sendiri."
                        ];
                    }
                }

                // Process Teacher Presensi in absensi_guru
                $today = date('Y-m-d');
                $now = date('Y-m-d H:i:s');
                $currentTime = date('H:i:s');

                // Verify Teacher Schedule & Operating Hours
                $gSched = $this->verifyTeacherScheduleToday($guru['id'], $today, $currentTime);
                if (!$gSched['allowed']) {
                    return [
                        'success' => false,
                        'is_not_scheduled' => true,
                        'reason' => $gSched['reason'],
                        'message' => $gSched['message']
                    ];
                }

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
                        // Check if teacher is allowed to exit (must reach allow_exit_start / max_end)
                        if ($currentTime < $gSched['allow_exit_start']) {
                            $formatMaxEnd = date('H:i', strtotime($gSched['max_end']));
                            return [
                                'success' => false,
                                'already_attended' => false,
                                'is_not_scheduled' => true,
                                'role' => 'Guru',
                                'nama' => $guru['nama_lengkap'],
                                'message' => "Presensi Pulang Belum Diizinkan! Jadwal KBM mengajar Bpk/Ibu {$guru['nama_lengkap']} pada hari ini ({$gSched['day_name']}) berakhir pukul {$formatMaxEnd} WIB."
                            ];
                        }

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

                // Check entry start time buffer
                if ($currentTime < $gSched['allow_entry_start']) {
                    $formatMinStart = date('H:i', strtotime($gSched['allow_entry_start']));
                    return [
                        'success' => false,
                        'is_not_scheduled' => true,
                        'message' => "Presensi Masuk Belum Dibuka! Presensi masuk Bpk/Ibu {$guru['nama_lengkap']} baru dapat dilakukan mulai pukul {$formatMinStart} WIB."
                    ];
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
                    $jamSelesaiKbm = date('H:i', strtotime($gSched['max_end']));
                    $isLate = ($currentTime > '07:15:00');
                    $statusKet = $isLate ? 'Terlambat' : 'Hadir Tepat Waktu';
                    return [
                        'success' => true,
                        'type' => 'masuk',
                        'role' => 'Guru',
                        'nama' => $guru['nama_lengkap'],
                        'nis' => $guru['nip'] ?: '-',
                        'kelas' => 'GTK / Pendidik',
                        'jam' => $jamMasuk . ' WIB',
                        'jam_masuk' => $jamMasuk . ' WIB',
                        'is_late' => $isLate,
                        'status_keterangan' => $statusKet,
                        'message' => "Presensi MASUK GURU/GTK ({$guru['nama_lengkap']}) berhasil dicatat pukul {$jamMasuk} WIB! Status: {$statusKet}. (Jadwal KBM Selesai: {$jamSelesaiKbm} WIB)."
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
                    'nama' => $siswa['nama_lengkap'],
                    'nis' => $siswa['nis'] ?: ($siswa['nisn'] ?: '-'),
                    'kelas' => $siswa['nama_kelas'] ?: 'Tanpa Kelas',
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
                    // Check if student is allowed to exit (must reach allow_exit_start)
                    if (!empty($schedCheck['allow_exit_start']) && $currentTime < $schedCheck['allow_exit_start']) {
                        $formatMaxEnd = date('H:i', strtotime($schedCheck['class_max_end'] ?? '14:00'));
                        return [
                            'success' => false,
                            'already_attended' => false,
                            'is_not_scheduled' => true,
                            'nama' => $siswa['nama_lengkap'],
                            'nis' => $siswa['nis'] ?: ($siswa['nisn'] ?: '-'),
                            'kelas' => $siswa['nama_kelas'] ?: 'Tanpa Kelas',
                            'message' => "Presensi Pulang Belum Diizinkan! Jadwal KBM kelas {$siswa['nama_kelas']} pada hari ini ({$schedCheck['day_name']}) berakhir pukul {$formatMaxEnd} WIB."
                        ];
                    }

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
                $isLate = ($currentTime > '07:15:00');
                $statusKet = $isLate ? 'Terlambat' : 'Hadir Tepat Waktu';
                return [
                    'success' => true,
                    'type' => 'masuk',
                    'nama' => $siswa['nama_lengkap'],
                    'nis' => $siswa['nis'] ?: ($siswa['nisn'] ?: '-'),
                    'kelas' => $siswa['nama_kelas'] ?: 'Tanpa Kelas',
                    'jam' => $jamMasuk . ' WIB',
                    'jam_masuk' => $jamMasuk . ' WIB',
                    'is_late' => $isLate,
                    'status_keterangan' => $statusKet,
                    'message' => "Presensi MASUK {$siswa['nama_lengkap']} ({$siswa['nama_kelas']}) berhasil dicatat pukul {$jamMasuk} WIB! Status: {$statusKet}."
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
            return [];
        }
    }

    public function getRecapGuru($tanggal = null) {
        if (!$tanggal) $tanggal = date('Y-m-d');
        try {
            $stmt = $this->db->prepare("
                SELECT g.id as guru_id, g.nip, g.nama_lengkap, g.status as status_guru,
                       ag.id as absensi_id, ag.tanggal, ag.waktu_masuk, ag.waktu_pulang, ag.waktu_hadir, ag.status, ag.qr_code, ag.keterangan,
                       ag.foto_masuk, ag.foto_pulang, ag.latitude_masuk, ag.longitude_masuk, ag.latitude_pulang, ag.longitude_pulang,
                       ag.jarak_masuk_meter, ag.jarak_pulang_meter, ag.tipe_presensi
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

    public function recordAttendanceGuru($guruId, $tanggal, $status, $keterangan = '') {
        $gId = (int)$guruId;
        if ($gId <= 0) return false;
        if (!$tanggal) $tanggal = date('Y-m-d');
        
        try {
            $stmt = $this->db->prepare("SELECT id FROM absensi_guru WHERE guru_id = ? AND tanggal = ? LIMIT 1");
            $stmt->execute([$gId, $tanggal]);
            $existing = $stmt->fetch();

            if ($existing) {
                $upd = $this->db->prepare("UPDATE absensi_guru SET status = ?, keterangan = ? WHERE id = ?");
                return $upd->execute([$status, $keterangan, $existing['id']]);
            } else {
                $now = date('H:i:s');
                $ins = $this->db->prepare("INSERT INTO absensi_guru (guru_id, tanggal, waktu_masuk, waktu_hadir, status, keterangan) VALUES (?, ?, ?, ?, ?, ?)");
                return $ins->execute([$gId, $tanggal, $now, $now, $status, $keterangan]);
            }
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Hitung jarak dua titik koordinat bumi dalam satuan meter (Haversine Formula)
     */
    public function calculateDistanceMeter($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; // Radius bumi dalam meter
        $latFrom = deg2rad((float)$lat1);
        $lonFrom = deg2rad((float)$lon1);
        $latTo = deg2rad((float)$lat2);
        $lonTo = deg2rad((float)$lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return (int)round($angle * $earthRadius);
    }

    /**
     * Ambil data presensi guru untuk tanggal tertentu (default hari ini)
     */
    public function getPresensiGuruHariIni($guruId, $tanggal = null) {
        if (!$tanggal) $tanggal = date('Y-m-d');
        try {
            $stmt = $this->db->prepare("
                SELECT ag.*, g.nama_lengkap, g.nip
                FROM absensi_guru ag
                JOIN guru g ON ag.guru_id = g.id
                WHERE ag.guru_id = ? AND ag.tanggal = ?
                LIMIT 1
            ");
            $stmt->execute([(int)$guruId, $tanggal]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Ambil riwayat presensi guru mandiri
     */
    public function getRiwayatPresensiGuru($guruId, $limit = 30) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM absensi_guru
                WHERE guru_id = ?
                ORDER BY tanggal DESC, id DESC
                LIMIT " . (int)$limit . "
            ");
            $stmt->execute([(int)$guruId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Hitung Jadwal Efektif Presensi Guru Hari Ini (Berdasarkan Mode KBM Dinamis atau Mode Serentak)
     */
    public function getEffectiveJadwalGuru($guruId, $tanggal = null) {
        $guruId = (int)$guruId;
        $tanggal = $tanggal ?: date('Y-m-d');

        require_once ROOT_PATH . 'models/SettingsModel.php';
        $settingsModel = new SettingsModel();
        $settings = $settingsModel->getAll();

        $mode = $settings['presensi_mode_jadwal'] ?? 'jadwal';
        $kegiatanSerentak = trim($settings['presensi_kegiatan_serentak_nama'] ?? '');
        $standarMasukMulai = $settings['presensi_jam_masuk_mulai'] ?? '06:00';
        $standarMasukBatas = $settings['presensi_jam_masuk_batas'] ?? '07:30';
        $standarPulangMulai = $settings['presensi_jam_pulang_mulai'] ?? '15:00';
        $toleransiMasukMenit = max(10, (int)($settings['presensi_toleransi_masuk_menit'] ?? 60));
        $toleransiTerlambatMenit = max(0, (int)($settings['presensi_toleransi_terlambat_menit'] ?? 0));

        // Mapping hari dalam bahasa Indonesia
        $daysMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu'
        ];
        $dayNum = (int)date('N', strtotime($tanggal));
        $hariName = $daysMap[$dayNum] ?? 'Senin';

        // 1. Jika Mode Serentak aktif (Rapat, Upacara, Kegiatan Khusus dewan guru)
        if ($mode === 'serentak') {
            return [
                'mode' => 'serentak',
                'title' => 'Jadwal Serentak / Bersama',
                'kegiatan_nama' => $kegiatanSerentak ?: 'Jadwal Bersama Dewan Guru',
                'hari' => $hariName,
                'tanggal' => $tanggal,
                'jam_masuk_mulai' => $standarMasukMulai,
                'jam_masuk_batas' => $standarMasukBatas,
                'jam_pulang_mulai' => $standarPulangMulai,
                'is_kbm' => false,
                'total_sesi' => 0,
                'kbm_list' => [],
                'keterangan_jadwal' => !empty($kegiatanSerentak) 
                    ? "Agenda Hari Ini: {$kegiatanSerentak} (Presensi Serentak Seluruh Guru)" 
                    : "Presensi Serentak Seluruh Guru"
            ];
        }

        // 2. Mode Jadwal Pelajaran Sekolah (Dinamis mengikuti jadwal KBM mengajar guru)
        $kbmList = [];
        try {
            $stmt = $this->db->prepare("
                SELECT j.id, j.kelas_id, j.mapel_id, j.hari, j.jam_mulai, j.jam_selesai, j.ruangan,
                       m.nama_mapel, k.nama_kelas
                FROM jadwal j
                LEFT JOIN mata_pelajaran m ON j.mapel_id = m.id
                LEFT JOIN kelas k ON j.kelas_id = k.id
                WHERE j.guru_id = ? AND j.hari = ?
                ORDER BY j.jam_mulai ASC
            ");
            $stmt->execute([$guruId, $hariName]);
            $kbmList = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            $kbmList = [];
        }

        if (!empty($kbmList)) {
            $firstSesi = $kbmList[0];
            $lastSesi = end($kbmList);

            $jamMulaiKbm = substr($firstSesi['jam_mulai'], 0, 5);
            $jamSelesaiKbm = substr($lastSesi['jam_selesai'], 0, 5);

            // Jam buka masuk = KBM pertama dikurangi toleransi menit (misal 60 menit sebelum sesi 1)
            $jamBukaMasukTimestamp = strtotime($tanggal . ' ' . $firstSesi['jam_mulai']) - ($toleransiMasukMenit * 60);
            $jamMasukMulaiCalculated = date('H:i', $jamBukaMasukTimestamp);

            // Batas masuk tepat waktu = jam mulai sesi KBM pertama (ditambah toleransi jika ada)
            if ($toleransiTerlambatMenit > 0) {
                $jamBatasMasukTimestamp = strtotime($tanggal . ' ' . $firstSesi['jam_mulai']) + ($toleransiTerlambatMenit * 60);
                $jamMasukBatasCalculated = date('H:i', $jamBatasMasukTimestamp);
            } else {
                $jamMasukBatasCalculated = $jamMulaiKbm;
            }

            // Jam buka pulang = saat sesi KBM terakhir guru hari itu selesai
            $jamPulangMulaiCalculated = $jamSelesaiKbm;

            return [
                'mode' => 'jadwal',
                'title' => 'Mengikuti Jadwal Pelajaran KBM',
                'kegiatan_nama' => 'Jadwal Mengajar KBM Hari Ini',
                'hari' => $hariName,
                'tanggal' => $tanggal,
                'jam_masuk_mulai' => $jamMasukMulaiCalculated,
                'jam_masuk_batas' => $jamMasukBatasCalculated,
                'jam_pulang_mulai' => $jamPulangMulaiCalculated,
                'is_kbm' => true,
                'total_sesi' => count($kbmList),
                'kbm_list' => $kbmList,
                'first_sesi' => $firstSesi,
                'last_sesi' => $lastSesi,
                'keterangan_jadwal' => "Mengikuti Jadwal KBM: {$firstSesi['nama_mapel']} ({$firstSesi['nama_kelas']}) s/d {$lastSesi['nama_mapel']} ({$lastSesi['nama_kelas']})"
            ];
        }

        // 3. Fallback: Guru tidak memiliki jadwal KBM pada hari ini (misal piket / tugas dinas / KBM kosong)
        return [
            'mode' => 'fallback_standar',
            'title' => 'Jam Standar Sekolah (Non-KBM)',
            'kegiatan_nama' => 'Tidak Ada Jadwal Mengajar KBM Hari Ini',
            'hari' => $hariName,
            'tanggal' => $tanggal,
            'jam_masuk_mulai' => $standarMasukMulai,
            'jam_masuk_batas' => $standarMasukBatas,
            'jam_pulang_mulai' => $standarPulangMulai,
            'is_kbm' => false,
            'total_sesi' => 0,
            'kbm_list' => [],
            'keterangan_jadwal' => "Tidak ada jadwal mengajar KBM hari {$hariName} (Mengikuti jam operasional standar sekolah)"
        ];
    }

    /**
     * Catat Presensi Selfie Guru dengan Validasi Geofencing
     */
    public function submitPresensiGuruSelfie($guruId, $data) {
        $guruId = (int)$guruId;
        if ($guruId <= 0) {
            return ['status' => 'error', 'message' => 'Identitas guru tidak valid atau sesi telah berakhir.'];
        }

        $jenis = strtolower(trim($data['jenis'] ?? 'masuk'));
        if (!in_array($jenis, ['masuk', 'pulang'])) {
            return ['status' => 'error', 'message' => 'Jenis presensi tidak valid (masuk atau pulang).'];
        }

        $lat = isset($data['latitude']) && is_numeric($data['latitude']) ? (float)$data['latitude'] : null;
        $lng = isset($data['longitude']) && is_numeric($data['longitude']) ? (float)$data['longitude'] : null;

        if ($lat === null || $lng === null || ($lat == 0 && $lng == 0)) {
            return ['status' => 'error', 'message' => 'Titik koordinat GPS tidak terdeteksi. Pastikan GPS aktif dan izin lokasi diizinkan di browser Anda.'];
        }

        $imageBase64 = $data['image_base64'] ?? '';
        if (empty($imageBase64)) {
            return ['status' => 'error', 'message' => 'Foto selfie tidak ditemukan. Harap aktifkan kamera dan ambil foto selfie.'];
        }

        // Ambil konfigurasi lokasi sekolah dari settings
        require_once ROOT_PATH . 'models/SettingsModel.php';
        $settingsModel = new SettingsModel();
        $settings = $settingsModel->getAll();

        $lokasiNama = $settings['lokasi_sekolah_nama'] ?? 'SMK Muthia Harapan Cicalengka';
        $lokasiLat = isset($settings['lokasi_sekolah_lat']) ? (float)$settings['lokasi_sekolah_lat'] : -6.984042;
        $lokasiLng = isset($settings['lokasi_sekolah_lng']) ? (float)$settings['lokasi_sekolah_lng'] : 107.838612;
        $radiusMaksimal = isset($settings['lokasi_sekolah_radius']) ? (int)$settings['lokasi_sekolah_radius'] : 150;

        // Ambil jadwal efektif presensi guru hari ini (Dinamis KBM atau Serentak)
        $today = date('Y-m-d');
        $effectiveJadwal = $this->getEffectiveJadwalGuru($guruId, $today);
        $jamMasukMulai = $effectiveJadwal['jam_masuk_mulai'];
        $jamMasukBatas = $effectiveJadwal['jam_masuk_batas'];
        $jamPulangMulai = $effectiveJadwal['jam_pulang_mulai'];

        // Hitung jarak Haversine ke titik koordinat sekolah
        $distance = $this->calculateDistanceMeter($lat, $lng, $lokasiLat, $lokasiLng);

        // Validasi radius Geofencing
        if ($distance > $radiusMaksimal) {
            return [
                'status' => 'error',
                'message' => "Lokasi Anda berada di luar radius presensi {$lokasiNama}! Jarak Anda saat ini: {$distance} meter dari titik sekolah (Batas maksimal radius: {$radiusMaksimal} meter).",
                'distance' => $distance,
                'max_radius' => $radiusMaksimal
            ];
        }

        // Decode dan simpan file foto selfie
        $uploadDir = ROOT_PATH . 'assets/uploads/presensi_guru/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $imageBase64, $type)) {
            $imageBase64 = substr($imageBase64, strpos($imageBase64, ',') + 1);
            $type = strtolower($type[1]);
            if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                return ['status' => 'error', 'message' => 'Format foto selfie tidak didukung. Gunakan JPG/PNG.'];
            }
        } else {
            return ['status' => 'error', 'message' => 'Format data foto kamera tidak valid.'];
        }

        $decodedImage = base64_decode($imageBase64);
        if ($decodedImage === false) {
            return ['status' => 'error', 'message' => 'Gagal memproses data gambar kamera.'];
        }

        $fileName = 'selfie_' . $guruId . '_' . $jenis . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.jpg';
        $filePath = $uploadDir . $fileName;
        $relativeFilePath = 'assets/uploads/presensi_guru/' . $fileName;

        if (file_put_contents($filePath, $decodedImage) === false) {
            return ['status' => 'error', 'message' => 'Gagal menyimpan file foto selfie ke server.'];
        }

        $nowDateTime = date('Y-m-d H:i:s');
        $nowTime = date('H:i:s');
        $existing = $this->getPresensiGuruHariIni($guruId, $today);

        if ($jenis === 'masuk') {
            if ($existing && !empty($existing['waktu_masuk']) && $existing['waktu_masuk'] !== '0000-00-00 00:00:00') {
                $masukDisplay = date('H:i', strtotime($existing['waktu_masuk']));
                return [
                    'status' => 'warning',
                    'message' => 'Anda sudah melakukan presensi masuk hari ini pada pukul ' . $masukDisplay . ' WIB.'
                ];
            }

            // Status Kehadiran (Hadir tepat waktu vs Terlambat)
            $statusPresensi = 'Hadir';
            $jamSekarang = date('H:i');
            if ($jamSekarang > $jamMasukBatas) {
                $statusPresensi = 'Terlambat';
            }

            $keterangan = trim($data['keterangan'] ?? '');
            if ($statusPresensi === 'Terlambat' && empty($keterangan)) {
                $keterangan = 'Terlambat hadir (Check-in ' . substr($nowTime, 0, 5) . ' WIB, batas: ' . $jamMasukBatas . ' WIB)';
            }

            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE absensi_guru 
                    SET waktu_masuk = ?, waktu_hadir = ?, status = ?, foto_masuk = ?, 
                        latitude_masuk = ?, longitude_masuk = ?, jarak_masuk_meter = ?, 
                        tipe_presensi = 'selfie', keterangan = CASE WHEN keterangan IS NULL OR keterangan = '' THEN ? ELSE keterangan END
                    WHERE id = ?
                ");
                $stmt->execute([$nowDateTime, $nowDateTime, $statusPresensi, $relativeFilePath, $lat, $lng, $distance, $keterangan, $existing['id']]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO absensi_guru 
                    (guru_id, tanggal, waktu_masuk, waktu_hadir, status, foto_masuk, latitude_masuk, longitude_masuk, jarak_masuk_meter, tipe_presensi, keterangan)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'selfie', ?)
                ");
                $stmt->execute([$guruId, $today, $nowDateTime, $nowDateTime, $statusPresensi, $relativeFilePath, $lat, $lng, $distance, $keterangan]);
            }

            return [
                'status' => 'success',
                'message' => "Presensi MASUK berhasil dicatat! Status: {$statusPresensi}. Jarak Anda ke sekolah: {$distance} meter.",
                'jenis' => 'masuk',
                'waktu' => substr($nowTime, 0, 5),
                'status_presensi' => $statusPresensi,
                'jarak' => $distance,
                'foto' => $relativeFilePath,
                'jadwal_efektif' => $effectiveJadwal
            ];

        } else { // pulang
            if (!$existing || empty($existing['waktu_masuk']) || $existing['waktu_masuk'] === '0000-00-00 00:00:00') {
                return [
                    'status' => 'error',
                    'message' => 'Anda belum melakukan presensi masuk hari ini. Harap lakukan presensi masuk terlebih dahulu sebelum presensi pulang.'
                ];
            }

            if (!empty($existing['waktu_pulang']) && $existing['waktu_pulang'] !== '0000-00-00 00:00:00') {
                $pulangDisplay = date('H:i', strtotime($existing['waktu_pulang']));
                return [
                    'status' => 'warning',
                    'message' => 'Anda sudah melakukan presensi pulang hari ini pada pukul ' . $pulangDisplay . ' WIB.'
                ];
            }

            $jamSekarang = date('H:i');
            $keteranganPulang = trim($data['keterangan'] ?? '');
            if ($jamSekarang < $jamPulangMulai && empty($keteranganPulang)) {
                $keteranganPulang = 'Pulang lebih awal ' . substr($nowTime, 0, 5) . ' WIB (Jadwal kepulangan: ' . $jamPulangMulai . ' WIB)';
            }

            if (!empty($keteranganPulang)) {
                $stmt = $this->db->prepare("
                    UPDATE absensi_guru 
                    SET waktu_pulang = ?, foto_pulang = ?, latitude_pulang = ?, longitude_pulang = ?, 
                        jarak_pulang_meter = ?, tipe_presensi = 'selfie',
                        keterangan = CASE WHEN keterangan IS NULL OR keterangan = '' THEN ? ELSE CONCAT(keterangan, ' | Pulang: ', ?) END
                    WHERE id = ?
                ");
                $stmt->execute([$nowDateTime, $relativeFilePath, $lat, $lng, $distance, $keteranganPulang, $keteranganPulang, $existing['id']]);
            } else {
                $stmt = $this->db->prepare("
                    UPDATE absensi_guru 
                    SET waktu_pulang = ?, foto_pulang = ?, latitude_pulang = ?, longitude_pulang = ?, 
                        jarak_pulang_meter = ?, tipe_presensi = 'selfie'
                    WHERE id = ?
                ");
                $stmt->execute([$nowDateTime, $relativeFilePath, $lat, $lng, $distance, $existing['id']]);
            }

            return [
                'status' => 'success',
                'message' => "Presensi PULANG berhasil dicatat pada pukul " . substr($nowTime, 0, 5) . " WIB! Jarak: {$distance} meter.",
                'jenis' => 'pulang',
                'waktu' => substr($nowTime, 0, 5),
                'jarak' => $distance,
                'foto' => $relativeFilePath,
                'jadwal_efektif' => $effectiveJadwal
            ];
        }
    }

    public function getMonthlyRecapSiswa($bulan, $tahun, $kelasId = null, $guruId = null) {
        $bulan = sprintf('%02d', (int)$bulan);
        $tahun = (int)$tahun;
        $numDays = (int)date('t', strtotime("$tahun-$bulan-01"));
        $startDate = "$tahun-$bulan-01";
        $endDate = "$tahun-$bulan-" . sprintf('%02d', $numDays);

        $params = [];
        $whereSql = "";
        if (is_array($kelasId) && !empty($kelasId)) {
            $kelasIdInts = array_map('intval', $kelasId);
            $inClause = implode(',', $kelasIdInts);
            $whereSql = " WHERE s.kelas_id IN ({$inClause}) ";
        } elseif (is_numeric($kelasId) && (int)$kelasId > 0) {
            $whereSql = " WHERE s.kelas_id = ? ";
            $params[] = (int)$kelasId;
        } elseif ($guruId && (int)$guruId > 0) {
            $gId = (int)$guruId;
            $whereSql = " WHERE s.kelas_id IN (
                SELECT id FROM kelas WHERE wali_kelas_id = {$gId}
                UNION
                SELECT kelas_id FROM mapel_enrollment_keys WHERE guru_id = {$gId} AND kelas_id IS NOT NULL
                UNION
                SELECT kelas_id FROM jadwal WHERE guru_id = {$gId} AND kelas_id IS NOT NULL
                UNION
                SELECT kelas_id FROM materi WHERE guru_id = {$gId} AND kelas_id IS NOT NULL
                UNION
                SELECT kelas_id FROM tugas WHERE guru_id = {$gId} AND kelas_id IS NOT NULL
                UNION
                SELECT kelas_id FROM live_class WHERE guru_id = {$gId} AND kelas_id IS NOT NULL
                UNION
                SELECT s.kelas_id FROM siswa_mapel_enrollment sme JOIN siswa s ON sme.siswa_id = s.id WHERE sme.guru_id = {$gId} AND s.kelas_id IS NOT NULL
            ) ";
        }

        $stmtS = $this->db->prepare("
            SELECT s.id as siswa_id, s.nis, s.nisn, s.nama_lengkap, k.nama_kelas
            FROM siswa s
            LEFT JOIN kelas k ON s.kelas_id = k.id
            {$whereSql}
            ORDER BY k.nama_kelas ASC, s.nama_lengkap ASC
        ");
        $stmtS->execute($params);
        $siswaList = $stmtS->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stmtA = $this->db->prepare("
            SELECT siswa_id, tanggal, waktu_masuk, waktu_pulang, status
            FROM absensi
            WHERE tanggal >= ? AND tanggal <= ?
        ");
        $stmtA->execute([$startDate, $endDate]);
        $absensiRows = $stmtA->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $absMap = [];
        foreach ($absensiRows as $row) {
            $sId = $row['siswa_id'];
            $dayNum = (int)date('j', strtotime($row['tanggal']));
            if (!isset($absMap[$sId])) {
                $absMap[$sId] = [];
            }
            $absMap[$sId][$dayNum] = $row;
        }

        $results = [];
        foreach ($siswaList as $s) {
            $sId = $s['siswa_id'];
            $daily = [];
            $hadir = 0;
            $terlambat = 0;
            $sakit = 0;
            $izin = 0;
            $alpa = 0;
            $pulang = 0;

            for ($d = 1; $d <= $numDays; $d++) {
                if (isset($absMap[$sId][$d])) {
                    $rec = $absMap[$sId][$d];
                    $st = strtolower($rec['status'] ?? 'hadir');
                    $wktMasuk = $rec['waktu_masuk'] ? date('H:i:s', strtotime($rec['waktu_masuk'])) : null;
                    $isLate = ($wktMasuk && $wktMasuk > '07:15:00');

                    if ($st === 'sakit') {
                        $daily[$d] = 'S';
                        $sakit++;
                    } elseif ($st === 'izin') {
                        $daily[$d] = 'I';
                        $izin++;
                    } elseif ($st === 'alpa' || $st === 'alpha') {
                        $daily[$d] = 'A';
                        $alpa++;
                    } else {
                        if ($isLate) {
                            $daily[$d] = 'TL';
                            $terlambat++;
                        } else {
                            $daily[$d] = 'H';
                        }
                        $hadir++;
                    }

                    if (!empty($rec['waktu_pulang'])) {
                        $pulang++;
                    }
                } else {
                    $daily[$d] = '-';
                }
            }

            $totalRecorded = $hadir + $sakit + $izin + $alpa;
            $percentage = ($totalRecorded > 0) ? round(($hadir / $totalRecorded) * 100, 1) : 0;

            $results[] = array_merge($s, [
                'daily' => $daily,
                'total_hadir' => $hadir,
                'total_terlambat' => $terlambat,
                'total_sakit' => $sakit,
                'total_izin' => $izin,
                'total_alpa' => $alpa,
                'total_pulang' => $pulang,
                'persentase' => $percentage
            ]);
        }

        return [
            'num_days' => $numDays,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'data' => $results
        ];
    }

    public function getMonthlyRecapGuru($bulan, $tahun) {
        $bulan = sprintf('%02d', (int)$bulan);
        $tahun = (int)$tahun;
        $numDays = (int)date('t', strtotime("$tahun-$bulan-01"));
        $startDate = "$tahun-$bulan-01";
        $endDate = "$tahun-$bulan-" . sprintf('%02d', $numDays);

        $stmtG = $this->db->prepare("
            SELECT id as guru_id, nip, nama_lengkap, status as status_guru
            FROM guru
            WHERE status = 'aktif'
            ORDER BY nama_lengkap ASC
        ");
        $stmtG->execute();
        $guruList = $stmtG->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stmtA = $this->db->prepare("
            SELECT guru_id, tanggal, waktu_masuk, waktu_pulang, status
            FROM absensi_guru
            WHERE tanggal >= ? AND tanggal <= ?
        ");
        $stmtA->execute([$startDate, $endDate]);
        $absensiRows = $stmtA->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $absMap = [];
        foreach ($absensiRows as $row) {
            $gId = $row['guru_id'];
            $dayNum = (int)date('j', strtotime($row['tanggal']));
            if (!isset($absMap[$gId])) {
                $absMap[$gId] = [];
            }
            $absMap[$gId][$dayNum] = $row;
        }

        $results = [];
        foreach ($guruList as $g) {
            $gId = $g['guru_id'];
            $daily = [];
            $hadir = 0;
            $terlambat = 0;
            $sakit = 0;
            $izin = 0;
            $alpa = 0;
            $pulang = 0;

            for ($d = 1; $d <= $numDays; $d++) {
                if (isset($absMap[$gId][$d])) {
                    $rec = $absMap[$gId][$d];
                    $st = strtolower($rec['status'] ?? 'hadir');
                    $wktMasuk = $rec['waktu_masuk'] ? date('H:i:s', strtotime($rec['waktu_masuk'])) : null;
                    $isLate = ($wktMasuk && $wktMasuk > '07:15:00');

                    if ($st === 'sakit') {
                        $daily[$d] = 'S';
                        $sakit++;
                    } elseif ($st === 'izin') {
                        $daily[$d] = 'I';
                        $izin++;
                    } elseif ($st === 'alpa' || $st === 'alpha') {
                        $daily[$d] = 'A';
                        $alpa++;
                    } else {
                        if ($isLate) {
                            $daily[$d] = 'TL';
                            $terlambat++;
                        } else {
                            $daily[$d] = 'H';
                        }
                        $hadir++;
                    }

                    if (!empty($rec['waktu_pulang'])) {
                        $pulang++;
                    }
                } else {
                    $daily[$d] = '-';
                }
            }

            $totalRecorded = $hadir + $sakit + $izin + $alpa;
            $percentage = ($totalRecorded > 0) ? round(($hadir / $totalRecorded) * 100, 1) : 0;

            $results[] = array_merge($g, [
                'daily' => $daily,
                'total_hadir' => $hadir,
                'total_terlambat' => $terlambat,
                'total_sakit' => $sakit,
                'total_izin' => $izin,
                'total_alpa' => $alpa,
                'total_pulang' => $pulang,
                'persentase' => $percentage
            ]);
        }

        return [
            'num_days' => $numDays,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'data' => $results
        ];
    }

    public function getEnrolledStudentsForAttendance($guruId, $mapelId, $tanggal = null) {
        $guruId = (int)$guruId;
        $mapelId = (int)$mapelId;
        if (!$tanggal) $tanggal = date('Y-m-d');

        $stmt = $this->db->prepare("
            SELECT s.id as siswa_id, s.nama_lengkap, s.nis, s.nisn, k.nama_kelas, j.nama_jurusan,
                   COALESCE(a.status, 'Belum Absen') as status_absensi,
                   a.keterangan, a.waktu_hadir, a.waktu_masuk, a.waktu_pulang, a.qr_code,
                   CASE 
                       WHEN a.qr_code IS NOT NULL AND (a.qr_code LIKE 'QR_%' OR a.qr_code LIKE 'GURU_%' OR a.qr_code LIKE 'SISWA_%') THEN 1
                       WHEN a.keterangan LIKE '%Scan%' OR a.keterangan LIKE '%Digital%' OR a.keterangan LIKE '%QR%' THEN 1
                       ELSE 0 
                   END as is_qr_scanned
            FROM siswa_mapel_enrollment sme
            JOIN siswa s ON sme.siswa_id = s.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN jurusan j ON s.jurusan_id = j.id
            LEFT JOIN absensi a ON s.id = a.siswa_id AND a.tanggal = ?
            WHERE sme.mapel_id = ? AND (sme.guru_id = ? OR sme.guru_id IS NULL)
            GROUP BY s.id
            ORDER BY k.nama_kelas ASC, s.nama_lengkap ASC
        ");
        $stmt->execute([$tanggal, $mapelId, $guruId]);
        return $stmt->fetchAll();
    }

    public function saveManualAttendance($guruId, $mapelId, $siswaId, $tanggal, $status, $keterangan = '', $kategori = 'masuk') {
        $guruId = (int)$guruId;
        $mapelId = (int)$mapelId;
        $siswaId = (int)$siswaId;
        $tanggal = Security::sanitize($tanggal ?: date('Y-m-d'));
        $status = ucfirst(strtolower(trim($status)));
        if (!in_array($status, ['Hadir', 'Izin', 'Sakit', 'Alpha'])) {
            $status = 'Hadir';
        }
        $kategori = strtolower(trim($kategori));
        if (!in_array($kategori, ['masuk', 'pulang'])) {
            $kategori = 'masuk';
        }

        // Verify enrollment with fallback
        $chk = $this->db->prepare("SELECT id FROM siswa_mapel_enrollment WHERE siswa_id = ? AND mapel_id = ?");
        $chk->execute([$siswaId, $mapelId]);
        if (!$chk->fetch()) {
            // Check if student exists in database as last fallback
            $chk2 = $this->db->prepare("SELECT id FROM siswa WHERE id = ?");
            $chk2->execute([$siswaId]);
            if (!$chk2->fetch()) {
                return false;
            }
        }

        $stmtExist = $this->db->prepare("SELECT id, waktu_masuk, waktu_pulang FROM absensi WHERE siswa_id = ? AND tanggal = ? LIMIT 1");
        $stmtExist->execute([$siswaId, $tanggal]);
        $exist = $stmtExist->fetch();

        $isAbsent = in_array($status, ['Izin', 'Sakit', 'Alpha']);
        $now = date('Y-m-d H:i:s');
        if ($exist) {
            if ($isAbsent) {
                $stmt = $this->db->prepare("UPDATE absensi SET guru_id = ?, status = ?, waktu_pulang = NULL, keterangan = ? WHERE id = ?");
                return $stmt->execute([$guruId, $status, $keterangan ?: 'Tidak Hadir Ke Sekolah (' . $status . ')', $exist['id']]);
            } else if ($kategori === 'pulang') {
                $stmt = $this->db->prepare("UPDATE absensi SET guru_id = ?, status = ?, waktu_pulang = COALESCE(waktu_pulang, ?), keterangan = ? WHERE id = ?");
                return $stmt->execute([$guruId, $status, $now, $keterangan ?: 'Presensi Manual Pulang Guru', $exist['id']]);
            } else {
                $stmt = $this->db->prepare("UPDATE absensi SET guru_id = ?, status = ?, waktu_masuk = COALESCE(waktu_masuk, ?), keterangan = ? WHERE id = ?");
                return $stmt->execute([$guruId, $status, $now, $keterangan ?: 'Presensi Manual Masuk Guru', $exist['id']]);
            }
        } else {
            $qrCodeVal = "MANUAL_" . $siswaId . "_" . date('YmdHis');
            if ($isAbsent) {
                $stmt = $this->db->prepare("
                    INSERT INTO absensi (jadwal_id, siswa_id, guru_id, tanggal, waktu_masuk, waktu_pulang, waktu_hadir, status, qr_code, keterangan) 
                    VALUES (NULL, ?, ?, ?, NULL, NULL, ?, ?, ?, ?)
                ");
                return $stmt->execute([$siswaId, $guruId, $tanggal, $now, $status, $qrCodeVal, $keterangan ?: 'Tidak Hadir Ke Sekolah (' . $status . ')']);
            } else if ($kategori === 'pulang') {
                $stmt = $this->db->prepare("
                    INSERT INTO absensi (jadwal_id, siswa_id, guru_id, tanggal, waktu_masuk, waktu_pulang, waktu_hadir, status, qr_code, keterangan) 
                    VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                return $stmt->execute([$siswaId, $guruId, $tanggal, $now, $now, $now, $status, $qrCodeVal, $keterangan ?: 'Presensi Manual Pulang Guru']);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO absensi (jadwal_id, siswa_id, guru_id, tanggal, waktu_masuk, waktu_pulang, waktu_hadir, status, qr_code, keterangan) 
                    VALUES (NULL, ?, ?, ?, ?, NULL, ?, ?, ?, ?)
                ");
                return $stmt->execute([$siswaId, $guruId, $tanggal, $now, $now, $status, $qrCodeVal, $keterangan ?: 'Presensi Manual Masuk Guru']);
            }
        }
    }

    public function getMonthlyRecapForGuru($guruId, $bulan, $tahun, $mapelId = null, $kelasId = null) {
        $guruId = (int)$guruId;
        $bulan = sprintf('%02d', (int)$bulan);
        $tahun = (int)$tahun;
        $mapelId = (int)$mapelId;
        $kelasId = (int)$kelasId;

        $numDays = (int)date('t', strtotime("$tahun-$bulan-01"));
        $startDate = "$tahun-$bulan-01";
        $endDate = "$tahun-$bulan-" . sprintf('%02d', $numDays);

        $params = [];
        $whereConds = [];

        if ($kelasId > 0) {
            $whereConds[] = "s.kelas_id = ?";
            $params[] = $kelasId;
        }

        if ($mapelId > 0) {
            $whereConds[] = "(sme.mapel_id = ? OR s.id IN (SELECT DISTINCT siswa_id FROM absensi WHERE tanggal >= ? AND tanggal <= ?))";
            $params[] = $mapelId;
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $whereSql = "";
        if (!empty($whereConds)) {
            $whereSql = " WHERE " . implode(" AND ", $whereConds);
        }

        $stmtS = $this->db->prepare("
            SELECT DISTINCT s.id as siswa_id, s.nis, s.nisn, s.nama_lengkap, k.nama_kelas, j.nama_jurusan
            FROM siswa s
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN jurusan j ON s.jurusan_id = j.id
            LEFT JOIN siswa_mapel_enrollment sme ON s.id = sme.siswa_id
            {$whereSql}
            ORDER BY k.nama_kelas ASC, s.nama_lengkap ASC
        ");
        $stmtS->execute($params);
        $siswaList = $stmtS->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Fallback: If mapel filter returns empty, show all students in class/school
        if (empty($siswaList)) {
            $fallbackParams = [];
            $fallbackWhere = "";
            if ($kelasId > 0) {
                $fallbackWhere = " WHERE s.kelas_id = ? ";
                $fallbackParams[] = $kelasId;
            }
            $stmtFB = $this->db->prepare("
                SELECT DISTINCT s.id as siswa_id, s.nis, s.nisn, s.nama_lengkap, k.nama_kelas, j.nama_jurusan
                FROM siswa s
                LEFT JOIN kelas k ON s.kelas_id = k.id
                LEFT JOIN jurusan j ON s.jurusan_id = j.id
                {$fallbackWhere}
                ORDER BY k.nama_kelas ASC, s.nama_lengkap ASC
            ");
            $stmtFB->execute($fallbackParams);
            $siswaList = $stmtFB->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        $stmtA = $this->db->prepare("
            SELECT siswa_id, tanggal, waktu_masuk, waktu_pulang, status
            FROM absensi
            WHERE tanggal >= ? AND tanggal <= ?
        ");
        $stmtA->execute([$startDate, $endDate]);
        $absensiRows = $stmtA->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $absMap = [];
        foreach ($absensiRows as $row) {
            $sId = $row['siswa_id'];
            $dayNum = (int)date('j', strtotime($row['tanggal']));
            if (!isset($absMap[$sId])) {
                $absMap[$sId] = [];
            }
            $absMap[$sId][$dayNum] = $row;
        }

        $results = [];
        $summaryHadir = 0;
        $summarySakit = 0;
        $summaryIzin = 0;
        $summaryAlpa = 0;

        foreach ($siswaList as $s) {
            $sId = $s['siswa_id'];
            $daily = [];
            $hadir = 0;
            $terlambat = 0;
            $sakit = 0;
            $izin = 0;
            $alpa = 0;

            for ($d = 1; $d <= $numDays; $d++) {
                if (isset($absMap[$sId][$d])) {
                    $rec = $absMap[$sId][$d];
                    $st = strtolower($rec['status'] ?? 'hadir');
                    if ($st === 'sakit') {
                        $daily[$d] = 'S';
                        $sakit++;
                    } elseif ($st === 'izin') {
                        $daily[$d] = 'I';
                        $izin++;
                    } elseif ($st === 'alpa' || $st === 'alpha') {
                        $daily[$d] = 'A';
                        $alpa++;
                    } else {
                        $daily[$d] = 'H';
                        $hadir++;
                    }
                } else {
                    $daily[$d] = '-';
                }
            }

            $summaryHadir += $hadir;
            $summarySakit += $sakit;
            $summaryIzin += $izin;
            $summaryAlpa += $alpa;

            $totalRecorded = $hadir + $sakit + $izin + $alpa;
            $percentage = ($totalRecorded > 0) ? round(($hadir / $totalRecorded) * 100, 1) : 0;

            $results[] = array_merge($s, [
                'daily' => $daily,
                'total_hadir' => $hadir,
                'total_terlambat' => $terlambat,
                'total_sakit' => $sakit,
                'total_izin' => $izin,
                'total_alpa' => $alpa,
                'persentase' => $percentage
            ]);
        }

        $grandTotal = $summaryHadir + $summarySakit + $summaryIzin + $summaryAlpa;
        $avgPercentage = ($grandTotal > 0) ? round(($summaryHadir / $grandTotal) * 100, 1) : 0;

        return [
            'num_days' => $numDays,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'total_students' => count($siswaList),
            'summary' => [
                'total_hadir' => $summaryHadir,
                'total_sakit' => $summarySakit,
                'total_izin' => $summaryIzin,
                'total_alpa' => $summaryAlpa,
                'avg_persentase' => $avgPercentage
            ],
            'data' => $results
        ];
    }
}
