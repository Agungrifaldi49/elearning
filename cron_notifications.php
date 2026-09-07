<?php
/**
 * Automated Cron & Reminder Notification Processor
 * E-Learning SMK Muthia Harapan Cicalengka
 * 
 * Can be called via Cron Job / Scheduled Task or via REST API endpoint `check_reminders`.
 */

define('ROOT_PATH', __DIR__ . '/');
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'helpers/FcmHelper.php';
require_once ROOT_PATH . 'models/AbsensiModel.php';

class NotificationCronProcessor {
    private $db;
    private $absensiModel;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->absensiModel = new AbsensiModel();
    }

    public function runAllChecks() {
        $results = [
            'timestamp' => date('Y-m-d H:i:s'),
            'absen_masuk_siswa' => 0,
            'absen_masuk_guru' => 0,
            'absen_pulang_siswa' => 0,
            'absen_pulang_guru' => 0,
            'jadwal_siswa' => 0,
            'jadwal_guru' => 0,
        ];

        try {
            $todayDate = date('Y-m-d');
            $nowTime = date('H:i:s');

            // 1. Check-In Reminders (Active school day check)
            $results['absen_masuk_siswa'] = $this->checkSiswaAbsenMasuk($todayDate);
            $results['absen_masuk_guru'] = $this->checkGuruAbsenMasuk($todayDate);

            // 2. Check-Out Reminders (If time >= 12:00 WIB)
            if ($nowTime >= '12:00:00') {
                $results['absen_pulang_siswa'] = $this->checkSiswaAbsenPulang($todayDate);
                $results['absen_pulang_guru'] = $this->checkGuruAbsenPulang($todayDate);
            }

            // 3. Schedule Reminders (Today's active classes)
            $results['jadwal_siswa'] = $this->checkSiswaScheduleReminders($todayDate);
            $results['jadwal_guru'] = $this->checkGuruScheduleReminders($todayDate);

        } catch (\Throwable $e) {
            error_log("NotificationCronProcessor Error: " . $e->getMessage());
            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Run targeted reminders specifically for a single User ID (on login / dashboard load)
     */
    public function runUserReminders($userId) {
        $userId = (int)$userId;
        if ($userId <= 0) return false;

        try {
            $todayDate = date('Y-m-d');
            $nowTime = date('H:i:s');

            // Fetch user info
            $stmtU = $this->db->prepare("
                SELECT u.*, COALESCE(r.name, 'Siswa') as role_name 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.id = ? LIMIT 1
            ");
            $stmtU->execute([$userId]);
            $user = $stmtU->fetch(PDO::FETCH_ASSOC);

            if (!$user) return false;

            $roleId = intval($user['role_id'] ?? 3);
            $roleName = strtolower($user['role_name'] ?? 'siswa');
            $isGuru = ($roleId == 2 || $roleId == 4 || strpos($roleName, 'guru') !== false || strpos($roleName, 'kepala') !== false);
            $isSiswa = ($roleId == 3 || strpos($roleName, 'siswa') !== false);

            if ($isGuru && !$isSiswa) {
                // Find Guru details
                $stmtG = $this->db->prepare("
                    SELECT g.* FROM guru g 
                    WHERE g.user_id = ? OR g.nip = ? LIMIT 1
                ");
                $stmtG->execute([$userId, $user['username']]);
                $guru = $stmtG->fetch(PDO::FETCH_ASSOC);

                if ($guru) {
                    $guruId = (int)$guru['id'];

                    // A. Check Guru Absen Masuk
                    $stmtAbsG = $this->db->prepare("SELECT id FROM absensi_guru WHERE guru_id = ? AND tanggal = ? LIMIT 1");
                    $stmtAbsG->execute([$guruId, $todayDate]);
                    $hasAbsenG = $stmtAbsG->fetch();

                    if (!$hasAbsenG) {
                        $stmtCheck = $this->db->prepare("
                            SELECT COUNT(*) FROM notifikasi 
                            WHERE user_id = ? AND type = 'absensi' AND DATE(created_at) = ? AND title LIKE '%Masuk%'
                        ");
                        $stmtCheck->execute([$userId, $todayDate]);
                        if ((int)$stmtCheck->fetchColumn() === 0) {
                            FcmHelper::sendToUser(
                                $userId,
                                '⏰ Pengingat Absensi Guru',
                                "Bpk/Ibu {$guru['nama_lengkap']}, Anda belum melakukan presensi masuk GTK hari ini. Segera catat kehadiran!",
                                ['type' => 'absensi', 'action' => 'masuk']
                            );
                        }
                    } else {
                        // B. Check Guru Absen Pulang (Afternoon >= 12:00)
                        if ($nowTime >= '12:00:00') {
                            $stmtAbsGOut = $this->db->prepare("SELECT id FROM absensi_guru WHERE guru_id = ? AND tanggal = ? AND waktu_pulang IS NOT NULL AND waktu_pulang != '' LIMIT 1");
                            $stmtAbsGOut->execute([$guruId, $todayDate]);
                            if (!$stmtAbsGOut->fetch()) {
                                $stmtCheckOut = $this->db->prepare("
                                    SELECT COUNT(*) FROM notifikasi 
                                    WHERE user_id = ? AND type = 'absensi' AND DATE(created_at) = ? AND title LIKE '%Pulang%'
                                ");
                                $stmtCheckOut->execute([$userId, $todayDate]);
                                if ((int)$stmtCheckOut->fetchColumn() === 0) {
                                    FcmHelper::sendToUser(
                                        $userId,
                                        '🏠 Pengingat Absensi Pulang Guru',
                                        "Bpk/Ibu {$guru['nama_lengkap']}, jangan lupa melakukan presensi pulang GTK sebelum meninggalkan sekolah!",
                                        ['type' => 'absensi', 'action' => 'pulang']
                                    );
                                }
                            }
                        }
                    }

                    // C. Check Guru Schedule Reminders today
                    $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                    $dayName = $daysMap[date('N', strtotime($todayDate))] ?? 'Senin';

                    $stmtJadwalG = $this->db->prepare("
                        SELECT j.id as jadwal_id, j.jam_mulai, m.nama_mapel, COALESCE(k.nama_kelas, 'Rombel') as nama_kelas
                        FROM jadwal j
                        JOIN mata_pelajaran m ON j.mapel_id = m.id
                        LEFT JOIN kelas k ON j.kelas_id = k.id
                        WHERE j.guru_id = ? AND j.hari = ?
                    ");
                    $stmtJadwalG->execute([$guruId, $dayName]);
                    $schListG = $stmtJadwalG->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($schListG as $schG) {
                        $jamMulai = date('H:i', strtotime($schG['jam_mulai']));
                        $stmtCheckSch = $this->db->prepare("
                            SELECT COUNT(*) FROM notifikasi 
                            WHERE user_id = ? AND type = 'jadwal' AND target_id = ? AND DATE(created_at) = ?
                        ");
                        $stmtCheckSch->execute([$userId, $schG['jadwal_id'], $todayDate]);
                        if ((int)$stmtCheckSch->fetchColumn() === 0) {
                            FcmHelper::sendToUser(
                                $userId,
                                "📚 Pengingat Mengajar: {$schG['nama_mapel']}",
                                "Bpk/Ibu {$guru['nama_lengkap']}, Anda memiliki jadwal mengajar mapel {$schG['nama_mapel']} di {$schG['nama_kelas']} hari ini pukul {$jamMulai} WIB.",
                                ['type' => 'jadwal', 'id' => $schG['jadwal_id']]
                            );
                        }
                    }
                }

            } else if ($isSiswa) {
                // Siswa
                $stmtS = $this->db->prepare("
                    SELECT s.*, k.nama_kelas 
                    FROM siswa s 
                    LEFT JOIN kelas k ON s.kelas_id = k.id 
                    WHERE s.user_id = ? OR s.nis = ? LIMIT 1
                ");
                $stmtS->execute([$userId, $user['username']]);
                $siswa = $stmtS->fetch(PDO::FETCH_ASSOC);

                if ($siswa) {
                    $siswaId = (int)$siswa['id'];
                    $kelasId = intval($siswa['kelas_id'] ?? 0);

                    // A. Check Siswa Absen Masuk
                    $stmtAbsS = $this->db->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ? LIMIT 1");
                    $stmtAbsS->execute([$siswaId, $todayDate]);
                    $hasAbsenS = $stmtAbsS->fetch();

                    if (!$hasAbsenS) {
                        $stmtCheck = $this->db->prepare("
                            SELECT COUNT(*) FROM notifikasi 
                            WHERE user_id = ? AND type = 'absensi' AND DATE(created_at) = ? AND title LIKE '%Masuk%'
                        ");
                        $stmtCheck->execute([$userId, $todayDate]);
                        if ((int)$stmtCheck->fetchColumn() === 0) {
                            FcmHelper::sendToUser(
                                $userId,
                                '⏰ Pengingat Absensi Masuk',
                                "Halo {$siswa['nama_lengkap']}, Anda belum melakukan presensi masuk hari ini. Segera scan QR Code Presensi!",
                                ['type' => 'absensi', 'action' => 'masuk']
                            );
                        }
                    } else {
                        // B. Check Siswa Absen Pulang (Afternoon >= 12:00)
                        if ($nowTime >= '12:00:00') {
                            $stmtAbsSOut = $this->db->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ? AND waktu_pulang IS NOT NULL AND waktu_pulang != '' LIMIT 1");
                            $stmtAbsSOut->execute([$siswaId, $todayDate]);
                            if (!$stmtAbsSOut->fetch()) {
                                $stmtCheckOut = $this->db->prepare("
                                    SELECT COUNT(*) FROM notifikasi 
                                    WHERE user_id = ? AND type = 'absensi' AND DATE(created_at) = ? AND title LIKE '%Pulang%'
                                ");
                                $stmtCheckOut->execute([$userId, $todayDate]);
                                if ((int)$stmtCheckOut->fetchColumn() === 0) {
                                    FcmHelper::sendToUser(
                                        $userId,
                                        '🏠 Pengingat Absensi Pulang',
                                        "Halo {$siswa['nama_lengkap']}, jangan lupa melakukan presensi pulang sebelum meninggalkan lingkungan sekolah!",
                                        ['type' => 'absensi', 'action' => 'pulang']
                                    );
                                }
                            }
                        }
                    }

                    // C. Check Siswa Schedule Reminders today
                    $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                    $dayName = $daysMap[date('N', strtotime($todayDate))] ?? 'Senin';

                    if ($kelasId > 0) {
                        $stmtJadwalS = $this->db->prepare("
                            SELECT j.id as jadwal_id, j.jam_mulai, m.nama_mapel, COALESCE(g.nama_lengkap, 'Guru Pengampu') as nama_guru
                            FROM jadwal j
                            JOIN mata_pelajaran m ON j.mapel_id = m.id
                            LEFT JOIN guru g ON j.guru_id = g.id
                            WHERE j.kelas_id = ? AND j.hari = ?
                        ");
                        $stmtJadwalS->execute([$kelasId, $dayName]);
                        $schListS = $stmtJadwalS->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($schListS as $schS) {
                            $jamMulai = date('H:i', strtotime($schS['jam_mulai']));
                            $stmtCheckSch = $this->db->prepare("
                                SELECT COUNT(*) FROM notifikasi 
                                WHERE user_id = ? AND type = 'jadwal' AND target_id = ? AND DATE(created_at) = ?
                            ");
                            $stmtCheckSch->execute([$userId, $schS['jadwal_id'], $todayDate]);
                            if ((int)$stmtCheckSch->fetchColumn() === 0) {
                                FcmHelper::sendToUser(
                                    $userId,
                                    "📚 Pengingat Kelas: {$schS['nama_mapel']}",
                                    "Jadwal KBM {$schS['nama_mapel']} bersama {$schS['nama_guru']} hari ini dimulai pukul {$jamMulai} WIB. Bersiaplah!",
                                    ['type' => 'jadwal', 'id' => $schS['jadwal_id']]
                                );
                            }
                        }
                    }
                }
            }

            return true;
        } catch (\Throwable $e) {
            error_log("runUserReminders Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remind Siswa who haven't clocked in today
     */
    private function checkSiswaAbsenMasuk($todayDate) {
        $sentCount = 0;
        try {
            $stmt = $this->db->prepare("
                SELECT u.id as user_id, s.nama_lengkap
                FROM siswa s
                JOIN users u ON (s.user_id = u.id OR s.nis = u.username)
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE (u.role_id = 3 OR r.name = 'Siswa')
                  AND (s.status IS NULL OR s.status = 'aktif')
                  AND s.id NOT IN (
                      SELECT siswa_id FROM absensi WHERE tanggal = ?
                  )
                  AND u.id NOT IN (
                      SELECT user_id FROM notifikasi 
                      WHERE type = 'absensi' 
                        AND DATE(created_at) = ? 
                        AND title LIKE '%Masuk%'
                  )
                LIMIT 100
            ");
            $stmt->execute([$todayDate, $todayDate]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $r) {
                $success = FcmHelper::sendToUser(
                    $r['user_id'],
                    '⏰ Pengingat Absensi Masuk',
                    "Halo {$r['nama_lengkap']}, Anda belum melakukan presensi masuk hari ini. Segera scan QR Code Presensi!",
                    ['type' => 'absensi', 'action' => 'masuk']
                );
                if ($success) $sentCount++;
            }
        } catch (\Throwable $e) {
            error_log("checkSiswaAbsenMasuk Error: " . $e->getMessage());
        }
        return $sentCount;
    }

    /**
     * Remind Guru who haven't clocked in today
     */
    private function checkGuruAbsenMasuk($todayDate) {
        $sentCount = 0;
        try {
            $stmt = $this->db->prepare("
                SELECT u.id as user_id, g.nama_lengkap
                FROM guru g
                JOIN users u ON (g.user_id = u.id OR g.nip = u.username)
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE (u.role_id IN (2, 4) OR r.name IN ('Guru', 'Kepala Sekolah'))
                  AND (g.status IS NULL OR g.status = 'aktif')
                  AND g.id NOT IN (
                      SELECT guru_id FROM absensi_guru WHERE tanggal = ?
                  )
                  AND u.id NOT IN (
                      SELECT user_id FROM notifikasi 
                      WHERE type = 'absensi' 
                        AND DATE(created_at) = ? 
                        AND title LIKE '%Guru%'
                  )
                LIMIT 100
            ");
            $stmt->execute([$todayDate, $todayDate]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $r) {
                $success = FcmHelper::sendToUser(
                    $r['user_id'],
                    '⏰ Pengingat Absensi Guru',
                    "Bpk/Ibu {$r['nama_lengkap']}, Anda belum melakukan presensi masuk GTK hari ini. Segera catat kehadiran!",
                    ['type' => 'absensi', 'action' => 'masuk']
                );
                if ($success) $sentCount++;
            }
        } catch (\Throwable $e) {
            error_log("checkGuruAbsenMasuk Error: " . $e->getMessage());
        }
        return $sentCount;
    }

    /**
     * Remind Siswa who clocked in but haven't clocked out today
     */
    private function checkSiswaAbsenPulang($todayDate) {
        $sentCount = 0;
        try {
            $stmt = $this->db->prepare("
                SELECT u.id as user_id, s.nama_lengkap
                FROM absensi a
                JOIN siswa s ON a.siswa_id = s.id
                JOIN users u ON (s.user_id = u.id OR s.nis = u.username)
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE (u.role_id = 3 OR r.name = 'Siswa')
                  AND a.tanggal = ?
                  AND a.waktu_masuk IS NOT NULL
                  AND (a.waktu_pulang IS NULL OR a.waktu_pulang = '')
                  AND u.id NOT IN (
                      SELECT user_id FROM notifikasi 
                      WHERE type = 'absensi' 
                        AND DATE(created_at) = ? 
                        AND title LIKE '%Pulang%'
                  )
                LIMIT 100
            ");
            $stmt->execute([$todayDate, $todayDate]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $r) {
                $success = FcmHelper::sendToUser(
                    $r['user_id'],
                    '🏠 Pengingat Absensi Pulang',
                    "Halo {$r['nama_lengkap']}, jangan lupa melakukan presensi pulang sebelum meninggalkan lingkungan sekolah!",
                    ['type' => 'absensi', 'action' => 'pulang']
                );
                if ($success) $sentCount++;
            }
        } catch (\Throwable $e) {
            error_log("checkSiswaAbsenPulang Error: " . $e->getMessage());
        }
        return $sentCount;
    }

    /**
     * Remind Guru who clocked in but haven't clocked out today
     */
    private function checkGuruAbsenPulang($todayDate) {
        $sentCount = 0;
        try {
            $stmt = $this->db->prepare("
                SELECT u.id as user_id, g.nama_lengkap
                FROM absensi_guru ag
                JOIN guru g ON ag.guru_id = g.id
                JOIN users u ON (g.user_id = u.id OR g.nip = u.username)
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE (u.role_id IN (2, 4) OR r.name IN ('Guru', 'Kepala Sekolah'))
                  AND ag.tanggal = ?
                  AND ag.waktu_masuk IS NOT NULL
                  AND (ag.waktu_pulang IS NULL OR ag.waktu_pulang = '')
                  AND u.id NOT IN (
                      SELECT user_id FROM notifikasi 
                      WHERE type = 'absensi' 
                        AND DATE(created_at) = ? 
                        AND title LIKE '%Pulang Guru%'
                  )
                LIMIT 100
            ");
            $stmt->execute([$todayDate, $todayDate]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $r) {
                $success = FcmHelper::sendToUser(
                    $r['user_id'],
                    '🏠 Pengingat Absensi Pulang Guru',
                    "Bpk/Ibu {$r['nama_lengkap']}, jangan lupa melakukan presensi pulang GTK sebelum meninggalkan sekolah!",
                    ['type' => 'absensi', 'action' => 'pulang']
                );
                if ($success) $sentCount++;
            }
        } catch (\Throwable $e) {
            error_log("checkGuruAbsenPulang Error: " . $e->getMessage());
        }
        return $sentCount;
    }

    /**
     * Remind Siswa of active class schedule today
     */
    private function checkSiswaScheduleReminders($todayDate) {
        $sentCount = 0;
        try {
            $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
            $dayName = $daysMap[date('N', strtotime($todayDate))] ?? 'Senin';

            $stmt = $this->db->prepare("
                SELECT j.id as jadwal_id, j.jam_mulai, j.kelas_id, m.nama_mapel, COALESCE(g.nama_lengkap, 'Guru Pengampu') as nama_guru
                FROM jadwal j
                JOIN mata_pelajaran m ON j.mapel_id = m.id
                LEFT JOIN guru g ON j.guru_id = g.id
                WHERE j.hari = ?
            ");
            $stmt->execute([$dayName]);
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($schedules as $sch) {
                $kId = (int)$sch['kelas_id'];
                if ($kId > 0) {
                    $jamMulai = date('H:i', strtotime($sch['jam_mulai']));
                    $title = "📚 Pengingat Kelas: {$sch['nama_mapel']}";
                    $msg = "Jadwal KBM {$sch['nama_mapel']} bersama {$sch['nama_guru']} hari ini dimulai pukul {$jamMulai} WIB. Bersiaplah!";
                    
                    // Check if already notified for this schedule today
                    $stmtCheck = $this->db->prepare("
                        SELECT COUNT(*) FROM notifikasi 
                        WHERE type = 'jadwal' AND target_id = ? AND DATE(created_at) = ?
                    ");
                    $stmtCheck->execute([$sch['jadwal_id'], $todayDate]);
                    if ((int)$stmtCheck->fetchColumn() === 0) {
                        $success = FcmHelper::sendToKelas($kId, $title, $msg, ['type' => 'jadwal', 'id' => $sch['jadwal_id']]);
                        if ($success) $sentCount++;
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log("checkSiswaScheduleReminders Error: " . $e->getMessage());
        }
        return $sentCount;
    }

    /**
     * Remind Guru of active teaching schedule today
     */
    private function checkGuruScheduleReminders($todayDate) {
        $sentCount = 0;
        try {
            $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
            $dayName = $daysMap[date('N', strtotime($todayDate))] ?? 'Senin';

            $stmt = $this->db->prepare("
                SELECT j.id as jadwal_id, j.jam_mulai, u.id as user_id, m.nama_mapel, COALESCE(k.nama_kelas, 'Rombel') as nama_kelas, g.nama_lengkap
                FROM jadwal j
                JOIN mata_pelajaran m ON j.mapel_id = m.id
                JOIN guru g ON j.guru_id = g.id
                JOIN users u ON (g.user_id = u.id OR g.nip = u.username)
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN kelas k ON j.kelas_id = k.id
                WHERE j.hari = ? AND (u.role_id IN (2, 4) OR r.name IN ('Guru', 'Kepala Sekolah'))
            ");
            $stmt->execute([$dayName]);
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($schedules as $sch) {
                $jamMulai = date('H:i', strtotime($sch['jam_mulai']));
                $title = "📚 Pengingat Mengajar: {$sch['nama_mapel']}";
                $msg = "Bpk/Ibu {$sch['nama_lengkap']}, Anda memiliki jadwal mengajar mapel {$sch['nama_mapel']} di {$sch['nama_kelas']} hari ini pukul {$jamMulai} WIB.";

                $stmtCheck = $this->db->prepare("
                    SELECT COUNT(*) FROM notifikasi 
                    WHERE type = 'jadwal' AND target_id = ? AND user_id = ? AND DATE(created_at) = ?
                ");
                $stmtCheck->execute([$sch['jadwal_id'], $sch['user_id'], $todayDate]);
                if ((int)$stmtCheck->fetchColumn() === 0) {
                    $success = FcmHelper::sendToUser($sch['user_id'], $title, $msg, ['type' => 'jadwal', 'id' => $sch['jadwal_id']]);
                    if ($success) $sentCount++;
                }
            }
        } catch (\Throwable $e) {
            error_log("checkGuruScheduleReminders Error: " . $e->getMessage());
        }
        return $sentCount;
    }
}

// If executed directly via HTTP or CLI
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    header('Content-Type: application/json');
    $processor = new NotificationCronProcessor();
    $results = $processor->runAllChecks();
    echo json_encode(['status' => 'success', 'data' => $results]);
    exit();
}
