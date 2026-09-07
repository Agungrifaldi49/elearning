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

            // 1. Verify school operating schedule today
            $schoolCheck = $this->absensiModel->verifySchoolScheduleToday(null, $todayDate, $nowTime);
            
            // Proceed with attendance reminders only if school is open today
            if ($schoolCheck['allowed'] || strpos($schoolCheck['reason'] ?? '', 'out_of_hours') !== false) {
                // A. Check-In Reminders (Morning 06:30 - 10:00 WIB)
                if ($nowTime >= '06:30:00' && $nowTime <= '10:00:00') {
                    $results['absen_masuk_siswa'] = $this->checkSiswaAbsenMasuk($todayDate);
                    $results['absen_masuk_guru'] = $this->checkGuruAbsenMasuk($todayDate);
                }

                // B. Check-Out Reminders (Afternoon 13:30 - 18:00 WIB)
                if ($nowTime >= '13:30:00' && $nowTime <= '18:00:00') {
                    $results['absen_pulang_siswa'] = $this->checkSiswaAbsenPulang($todayDate);
                    $results['absen_pulang_guru'] = $this->checkGuruAbsenPulang($todayDate);
                }
            }

            // C. Schedule Reminders (Upcoming classes today)
            $results['jadwal_siswa'] = $this->checkSiswaScheduleReminders($todayDate, $nowTime);
            $results['jadwal_guru'] = $this->checkGuruScheduleReminders($todayDate, $nowTime);

        } catch (\Throwable $e) {
            error_log("NotificationCronProcessor Error: " . $e->getMessage());
            $results['error'] = $e->getMessage();
        }

        return $results;
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
                WHERE s.status = 'aktif'
                  AND s.id NOT IN (
                      SELECT siswa_id FROM absensi WHERE tanggal = ?
                  )
                  AND u.id NOT IN (
                      SELECT user_id FROM notifications 
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
                WHERE g.status = 'aktif'
                  AND g.id NOT IN (
                      SELECT guru_id FROM absensi_guru WHERE tanggal = ?
                  )
                  AND u.id NOT IN (
                      SELECT user_id FROM notifications 
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
                WHERE a.tanggal = ?
                  AND a.waktu_masuk IS NOT NULL
                  AND (a.waktu_pulang IS NULL OR a.waktu_pulang = '')
                  AND u.id NOT IN (
                      SELECT user_id FROM notifications 
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
                WHERE ag.tanggal = ?
                  AND ag.waktu_masuk IS NOT NULL
                  AND (ag.waktu_pulang IS NULL OR ag.waktu_pulang = '')
                  AND u.id NOT IN (
                      SELECT user_id FROM notifications 
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
     * Remind Siswa of upcoming class schedule within 15-30 minutes
     */
    private function checkSiswaScheduleReminders($todayDate, $nowTime) {
        $sentCount = 0;
        try {
            $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
            $dayName = $daysMap[date('N', strtotime($todayDate))] ?? 'Senin';

            $windowStart = date('H:i:s', strtotime($nowTime));
            $windowEnd = date('H:i:s', strtotime($nowTime . ' +30 minutes'));

            $stmt = $this->db->prepare("
                SELECT j.id as jadwal_id, j.jam_mulai, j.kelas_id, m.nama_mapel, COALESCE(g.nama_lengkap, 'Guru') as nama_guru
                FROM jadwal j
                JOIN mata_pelajaran m ON j.mapel_id = m.id
                LEFT JOIN guru g ON j.guru_id = g.id
                WHERE j.hari = ?
                  AND j.jam_mulai >= ? AND j.jam_mulai <= ?
            ");
            $stmt->execute([$dayName, $windowStart, $windowEnd]);
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($schedules as $sch) {
                $kId = (int)$sch['kelas_id'];
                if ($kId > 0) {
                    $jamMulai = date('H:i', strtotime($sch['jam_mulai']));
                    $title = "📚 Pengingat Kelas: {$sch['nama_mapel']}";
                    $msg = "Kelas {$sch['nama_mapel']} bersama {$sch['nama_guru']} dimulai pukul {$jamMulai} WIB. Bersiaplah!";
                    
                    // Check if already notified for this schedule today
                    $stmtCheck = $this->db->prepare("
                        SELECT COUNT(*) FROM notifications 
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
     * Remind Guru of upcoming teaching schedule within 15-30 minutes
     */
    private function checkGuruScheduleReminders($todayDate, $nowTime) {
        $sentCount = 0;
        try {
            $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
            $dayName = $daysMap[date('N', strtotime($todayDate))] ?? 'Senin';

            $windowStart = date('H:i:s', strtotime($nowTime));
            $windowEnd = date('H:i:s', strtotime($nowTime . ' +30 minutes'));

            $stmt = $this->db->prepare("
                SELECT j.id as jadwal_id, j.jam_mulai, u.id as user_id, m.nama_mapel, COALESCE(k.nama_kelas, 'Rombel') as nama_kelas, g.nama_lengkap
                FROM jadwal j
                JOIN mata_pelajaran m ON j.mapel_id = m.id
                JOIN guru g ON j.guru_id = g.id
                JOIN users u ON (g.user_id = u.id OR g.nip = u.username)
                LEFT JOIN kelas k ON j.kelas_id = k.id
                WHERE j.hari = ?
                  AND j.jam_mulai >= ? AND j.jam_mulai <= ?
            ");
            $stmt->execute([$dayName, $windowStart, $windowEnd]);
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($schedules as $sch) {
                $jamMulai = date('H:i', strtotime($sch['jam_mulai']));
                $title = "📚 Pengingat Mengajar: {$sch['nama_mapel']}";
                $msg = "Bpk/Ibu {$sch['nama_lengkap']}, Anda memiliki jadwal mengajar mapel {$sch['nama_mapel']} di {$sch['nama_kelas']} pukul {$jamMulai} WIB.";

                $stmtCheck = $this->db->prepare("
                    SELECT COUNT(*) FROM notifications 
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
