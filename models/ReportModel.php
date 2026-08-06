<?php
/**
 * Report & Analytics Model
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class ReportModel extends BaseModel {

    public function getAdminStats() {
        $count = function($sql) {
            try {
                return (int)$this->db->query($sql)->fetchColumn();
            } catch (Exception $e) {
                return 0;
            }
        };

        return [
            'total_guru' => $count("SELECT COUNT(*) FROM guru"),
            'total_siswa' => $count("SELECT COUNT(*) FROM siswa"),
            'total_kelas' => $count("SELECT COUNT(*) FROM kelas"),
            'total_materi' => $count("SELECT COUNT(*) FROM materi"),
            'total_tugas' => $count("SELECT COUNT(*) FROM tugas"),
            'total_quiz' => $count("SELECT COUNT(*) FROM quiz"),
            'total_ujian' => $count("SELECT COUNT(*) FROM quiz"),
        ];
    }

    public function getKepsekStats() {
        return $this->getKepsekAnalytics();
    }

    public function getKepsekAnalytics() {
        $count = function($sql) {
            try {
                return (int)$this->db->query($sql)->fetchColumn();
            } catch (Exception $e) {
                return 0;
            }
        };

        $totalGuru = $count("SELECT COUNT(*) FROM guru");
        $totalSiswa = $count("SELECT COUNT(*) FROM siswa");
        $totalKelas = $count("SELECT COUNT(*) FROM kelas");
        $totalMapel = $count("SELECT COUNT(*) FROM mata_pelajaran");
        $totalMateri = $count("SELECT COUNT(*) FROM materi");
        $totalTugas = $count("SELECT COUNT(*) FROM tugas");
        $totalQuiz = $count("SELECT COUNT(*) FROM quiz");

        $totalGuru = $count("SELECT COUNT(*) FROM guru");
        $totalSiswa = $count("SELECT COUNT(*) FROM siswa");
        $totalKelas = $count("SELECT COUNT(*) FROM kelas");
        $totalMapel = $count("SELECT COUNT(*) FROM mata_pelajaran");
        $totalMateri = $count("SELECT COUNT(*) FROM materi");
        $totalTugas = $count("SELECT COUNT(*) FROM tugas");
        $totalQuiz = $count("SELECT COUNT(*) FROM quiz");

        $avgRaporVal = $this->db->query("SELECT AVG(nilai_akhir) FROM nilai_rapor")->fetchColumn();
        $avgQuizVal = $this->db->query("SELECT AVG(total_nilai) FROM hasil_quiz")->fetchColumn();

        $avgRapor = ($avgRaporVal !== false && $avgRaporVal !== null) ? (float)$avgRaporVal : null;
        $avgQuiz = ($avgQuizVal !== false && $avgQuizVal !== null) ? (float)$avgQuizVal : null;

        if ($avgRapor !== null && $avgQuiz !== null) {
            $avgScore = round(($avgRapor + $avgQuiz) / 2, 1);
        } elseif ($avgRapor !== null) {
            $avgScore = round($avgRapor, 1);
        } elseif ($avgQuiz !== null) {
            $avgScore = round($avgQuiz, 1);
        } else {
            $avgScore = 0;
        }

        $attVal = $this->db->query("SELECT (COUNT(CASE WHEN status='Hadir' THEN 1 END) / NULLIF(COUNT(*),0)) * 100 FROM absensi")->fetchColumn();
        $attRate = ($attVal !== false && $attVal !== null) ? round((float)$attVal, 1) : 0;

        $jurusanStats = $this->db->query("
            SELECT j.nama_jurusan, COUNT(s.id) as total_siswa
            FROM jurusan j
            LEFT JOIN siswa s ON s.jurusan_id = j.id
            GROUP BY j.id, j.nama_jurusan
            ORDER BY total_siswa DESC
        ")->fetchAll();

        $kelasStats = $this->db->query("
            SELECT k.nama_kelas, ROUND(COALESCE(AVG(n.nilai_akhir), 0), 1) as avg_nilai
            FROM kelas k
            LEFT JOIN siswa s ON s.kelas_id = k.id
            LEFT JOIN nilai_rapor n ON n.siswa_id = s.id
            GROUP BY k.id, k.nama_kelas
            ORDER BY k.tingkat ASC, k.nama_kelas ASC
        ")->fetchAll();

        $guruMonitoring = $this->db->query("
            SELECT g.*, u.username,
                   (SELECT COUNT(*) FROM materi m WHERE m.guru_id = g.id) as total_materi,
                   (SELECT COUNT(*) FROM tugas t WHERE t.guru_id = g.id) as total_tugas,
                   (SELECT COUNT(*) FROM quiz q WHERE q.guru_id = g.id) as total_quiz
            FROM guru g
            JOIN users u ON g.user_id = u.id
            ORDER BY g.nama_lengkap ASC
        ")->fetchAll();

        $rekapKelas = $this->db->query("
            SELECT k.*, j.nama_jurusan, g.nama_lengkap as nama_walikelas,
                   (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa,
                   ROUND(COALESCE((SELECT AVG(n.nilai_akhir) FROM nilai_rapor n JOIN siswa s2 ON n.siswa_id = s2.id WHERE s2.kelas_id = k.id), 0), 1) as avg_nilai
            FROM kelas k
            LEFT JOIN jurusan j ON k.jurusan_id = j.id
            LEFT JOIN guru g ON k.wali_kelas_id = g.id
            ORDER BY k.tingkat ASC, k.nama_kelas ASC
        ")->fetchAll();

        $guruKeaktifan = $this->db->query("
            SELECT g.nama_lengkap,
                   (SELECT COUNT(*) FROM materi m WHERE m.guru_id = g.id) as total_materi,
                   (SELECT COUNT(*) FROM tugas t WHERE t.guru_id = g.id) as total_tugas,
                   (SELECT COUNT(*) FROM quiz q WHERE q.guru_id = g.id) as total_quiz,
                   ((SELECT COUNT(*) FROM materi m WHERE m.guru_id = g.id) + 
                    (SELECT COUNT(*) FROM tugas t WHERE t.guru_id = g.id) + 
                    (SELECT COUNT(*) FROM quiz q WHERE q.guru_id = g.id)) as total_aktivitas
            FROM guru g
            ORDER BY total_aktivitas DESC
        ")->fetchAll();

        $rekapRombelDetail = $this->db->query("
            SELECT k.id as kelas_id, k.nama_kelas, j.nama_jurusan, g.nama_lengkap as nama_walikelas,
                   (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa,
                   (SELECT COUNT(*) FROM pengumpulan_tugas pt JOIN siswa s2 ON pt.siswa_id = s2.id WHERE s2.kelas_id = k.id) as total_tugas_dikumpul,
                   ROUND(COALESCE((SELECT (COUNT(CASE WHEN a.status='Hadir' THEN 1 END)/NULLIF(COUNT(*),0))*100 FROM absensi a JOIN siswa s3 ON a.siswa_id = s3.id WHERE s3.kelas_id = k.id), 0), 1) as pct_kehadiran,
                   ROUND(COALESCE((SELECT AVG(n.nilai_akhir) FROM nilai_rapor n JOIN siswa s4 ON n.siswa_id = s4.id WHERE s4.kelas_id = k.id), 0), 1) as avg_nilai
            FROM kelas k
            LEFT JOIN jurusan j ON k.jurusan_id = j.id
            LEFT JOIN guru g ON k.wali_kelas_id = g.id
            ORDER BY k.tingkat ASC, k.nama_kelas ASC
        ")->fetchAll();

        $siswaTop = $this->db->query("
            SELECT s.nama_lengkap, k.nama_kelas, j.nama_jurusan,
                   ROUND(COALESCE((SELECT AVG(n.nilai_akhir) FROM nilai_rapor n WHERE n.siswa_id = s.id), 0), 1) as avg_rapor,
                   (SELECT COUNT(*) FROM pengumpulan_tugas pt WHERE pt.siswa_id = s.id) as total_tugas
            FROM siswa s
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN jurusan j ON s.jurusan_id = j.id
            ORDER BY avg_rapor DESC, total_tugas DESC
            LIMIT 5
        ")->fetchAll();

        $tugasStats = [
            'dinilai' => $count("SELECT COUNT(*) FROM pengumpulan_tugas WHERE status = 'dinilai'"),
            'dikumpul' => $count("SELECT COUNT(*) FROM pengumpulan_tugas WHERE status = 'dikumpulkan'"),
            'total_tugas' => $count("SELECT COUNT(*) FROM tugas")
        ];

        return [
            'total_guru' => $totalGuru,
            'total_siswa' => $totalSiswa,
            'total_kelas' => $totalKelas,
            'total_mapel' => $totalMapel,
            'total_materi' => $totalMateri,
            'total_tugas' => $totalTugas,
            'total_quiz' => $totalQuiz,
            'avg_score' => $avgScore,
            'avgScore' => $avgScore,
            'attendance_rate' => $attRate,
            'attRate' => $attRate,
            'jurusan_stats' => $jurusanStats,
            'jurusanStats' => $jurusanStats,
            'kelas_stats' => $kelasStats,
            'kelasStats' => $kelasStats,
            'guru_monitoring' => $guruMonitoring,
            'guru_keaktifan' => $guruKeaktifan,
            'rekap_rombel' => $rekapRombelDetail,
            'siswa_top' => $siswaTop,
            'tugas_stats' => $tugasStats
        ];
    }

    public function getRecentActivities() {
        return $this->db->query("
            SELECT a.*, u.full_name, r.name as role_name 
            FROM aktivitas a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY a.id DESC LIMIT 10
        ")->fetchAll();
    }

    public function getRecentLoginLogs() {
        return $this->db->query("
            SELECT l.*, u.full_name 
            FROM log_login l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.id DESC LIMIT 10
        ")->fetchAll();
    }

    public function getAuditLogs($limit = 100) {
        $limit = (int)$limit;
        try {
            return $this->db->query("
                SELECT 
                    created_at,
                    level,
                    user_name,
                    role,
                    action,
                    description,
                    ip_address,
                    user_agent
                FROM (
                    SELECT 
                        a.id as raw_id,
                        a.created_at,
                        'INFO' as level,
                        COALESCE(u.full_name, 'System') as user_name,
                        COALESCE(r.name, 'System') as role,
                        'Aktivitas Sistem' as action,
                        a.activity as description,
                        a.ip_address,
                        'Web Browser' as user_agent
                    FROM aktivitas a
                    LEFT JOIN users u ON a.user_id = u.id
                    LEFT JOIN roles r ON u.role_id = r.id

                    UNION ALL

                    SELECT 
                        l.id as raw_id,
                        l.created_at,
                        IF(l.status = 'success', 'INFO', 'WARNING') as level,
                        COALESCE(u.full_name, l.username) as user_name,
                        COALESCE(r.name, 'Pengguna') as role,
                        IF(l.status = 'success', 'Login Berhasil', 'Login Gagal') as action,
                        CONCAT('Sesi login ', IF(l.status = 'success', 'berhasil untuk user ', 'gagal pada username '), l.username) as description,
                        l.ip_address,
                        l.user_agent
                    FROM log_login l
                    LEFT JOIN users u ON l.user_id = u.id
                    LEFT JOIN roles r ON u.role_id = r.id
                ) combined_logs
                ORDER BY created_at DESC
                LIMIT {$limit}
            ")->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getAuditLogStats() {
        $count = function($sql) {
            try {
                return (int)$this->db->query($sql)->fetchColumn();
            } catch (Exception $e) {
                return 0;
            }
        };

        return [
            'total_logs' => $count("SELECT COUNT(*) FROM aktivitas") + $count("SELECT COUNT(*) FROM log_login"),
            'login_sukses' => $count("SELECT COUNT(*) FROM log_login WHERE status = 'success'"),
            'login_gagal' => $count("SELECT COUNT(*) FROM log_login WHERE status = 'failed'"),
            'aktivitas_hari_ini' => $count("SELECT COUNT(*) FROM aktivitas WHERE DATE(created_at) = CURRENT_DATE") + $count("SELECT COUNT(*) FROM log_login WHERE DATE(created_at) = CURRENT_DATE")
        ];
    }

    public function clearOldLogs() {
        try {
            $this->db->exec("DELETE FROM aktivitas WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $this->db->exec("DELETE FROM log_login WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function createDatabaseBackup() {
        $fileName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = ROOT_PATH . 'database/' . $fileName;

        $tables = $this->db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $output = "-- Database Backup: " . APP_NAME . "\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            $createTable = $this->db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            $output .= "\n\n" . $createTable['Create Table'] . ";\n\n";

            $rows = $this->db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $fields = array_map(function($val) {
                    if ($val === null) return "NULL";
                    return "'" . addslashes($val) . "'";
                }, array_values($row));
                $output .= "INSERT INTO `$table` VALUES(" . implode(', ', $fields) . ");\n";
            }
        }

        file_put_contents($filePath, $output);
        $fileSize = round(filesize($filePath) / 1024, 2) . ' KB';

        $stmt = $this->db->prepare("INSERT INTO backup (file_name, file_size) VALUES (?, ?)");
        $stmt->execute([$fileName, $fileSize]);

        return $fileName;
    }

    public function getBackups() {
        return $this->db->query("SELECT * FROM backup ORDER BY id DESC")->fetchAll();
    }

    public function getLmsAnalytics() {
        $safeQuery = function($sql, $default = null) {
            try {
                $val = $this->db->query($sql)->fetchColumn();
                return $val !== false && $val !== null ? $val : $default;
            } catch (Exception $e) {
                return $default;
            }
        };

        // Real attendance rate from database absensi table
        $attVal = $safeQuery("SELECT (COUNT(CASE WHEN status='Hadir' THEN 1 END) / NULLIF(COUNT(*),0)) * 100 FROM absensi", null);
        $attendanceRate = ($attVal !== null) ? (float)$attVal : 0.0;

        // Real average score from nilai_rapor or quiz
        $avgRaporVal = $safeQuery("SELECT AVG(nilai_akhir) FROM nilai_rapor", null);
        $avgQuizVal = $safeQuery("SELECT AVG(total_nilai) FROM hasil_quiz", null);

        if ($avgRaporVal !== null && $avgQuizVal !== null) {
            $avgScore = round(((float)$avgRaporVal + (float)$avgQuizVal) / 2, 1);
        } elseif ($avgRaporVal !== null) {
            $avgScore = round((float)$avgRaporVal, 1);
        } elseif ($avgQuizVal !== null) {
            $avgScore = round((float)$avgQuizVal, 1);
        } else {
            $avgScore = 0.0;
        }

        // Real module completion rate
        $totalTugas = (int)$safeQuery("SELECT COUNT(*) FROM tugas", 0);
        $submittedTugas = (int)$safeQuery("SELECT COUNT(*) FROM pengumpulan_tugas", 0);
        $moduleCompletion = ($totalTugas > 0) ? round(($submittedTugas / $totalTugas) * 100, 1) : 0.0;

        // Real logins count
        $monthlyLogins = (int)$safeQuery("SELECT COUNT(*) FROM log_login WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)", 0);

        // Real daily activity chart (last 7 days)
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $dailyActivity = [0, 0, 0, 0, 0, 0, 0];
        try {
            $recent = $this->db->query("
                SELECT DATE_FORMAT(created_at, '%w') as day_idx, COUNT(*) as cnt 
                FROM log_login 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                GROUP BY day_idx
            ")->fetchAll(PDO::FETCH_KEY_PAIR);
            if (!empty($recent)) {
                $map = [1=>0, 2=>1, 3=>2, 4=>3, 5=>4, 6=>5, 0=>6];
                foreach ($map as $dbIdx => $chartIdx) {
                    if (isset($recent[$dbIdx])) {
                        $dailyActivity[$chartIdx] = (int)$recent[$dbIdx];
                    }
                }
            }
        } catch (Exception $e) {}

        // Real class recap
        $classRecap = [];
        try {
            $classes = $this->db->query("
                SELECT k.id, k.nama_kelas, j.nama_jurusan,
                (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa
                FROM kelas k
                LEFT JOIN jurusan j ON k.jurusan_id = j.id
                ORDER BY k.tingkat ASC, k.nama_kelas ASC
            ")->fetchAll();

            foreach ($classes as $c) {
                $classId = (int)$c['id'];
                
                $cAttVal = $safeQuery("
                    SELECT (COUNT(CASE WHEN a.status='Hadir' THEN 1 END) / NULLIF(COUNT(*),0)) * 100 
                    FROM absensi a 
                    JOIN jadwal jd ON a.jadwal_id = jd.id 
                    WHERE jd.kelas_id = {$classId}
                ", null);
                $classAtt = ($cAttVal !== null) ? round((float)$cAttVal, 1) : 0.0;

                $cRaporVal = $safeQuery("
                    SELECT AVG(n.nilai_akhir) 
                    FROM nilai_rapor n 
                    JOIN siswa s ON n.siswa_id = s.id 
                    WHERE s.kelas_id = {$classId}
                ", null);
                
                if ($cRaporVal === null) {
                    $cRaporVal = $safeQuery("
                        SELECT AVG(hq.total_nilai) 
                        FROM hasil_quiz hq 
                        JOIN siswa s ON hq.siswa_id = s.id 
                        WHERE s.kelas_id = {$classId}
                    ", null);
                }
                $classScore = ($cRaporVal !== null) ? round((float)$cRaporVal, 1) : 0.0;

                $statusLms = ($classAtt >= 90) ? 'Sangat Aktif' : (($classAtt >= 75) ? 'Aktif' : ($classAtt > 0 ? 'Cukup Aktif' : 'Belum Ada Presensi'));

                $classRecap[] = [
                    'nama_kelas' => $c['nama_kelas'],
                    'nama_jurusan' => $c['nama_jurusan'] ?? 'Umum',
                    'total_siswa' => (int)$c['total_siswa'],
                    'attendance_pct' => $classAtt,
                    'avg_score' => $classScore,
                    'status_lms' => $statusLms
                ];
            }
        } catch (Exception $e) {}

        return [
            'attendance_rate' => round($attendanceRate, 1),
            'avg_score' => $avgScore,
            'module_completion' => $moduleCompletion,
            'monthly_logins' => $monthlyLogins,
            'chart_days' => $days,
            'chart_activity' => $dailyActivity,
            'chart_materi' => [
                (int)$safeQuery("SELECT COUNT(*) FROM pengumpulan_tugas WHERE status='dinilai'", 0),
                (int)$safeQuery("SELECT COUNT(*) FROM pengumpulan_tugas WHERE status='dikumpulkan'", 0),
                (int)$safeQuery("SELECT COUNT(*) FROM tugas", 0)
            ],
            'class_recap' => $classRecap
        ];
    }
}
