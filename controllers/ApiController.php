<?php
/**
 * REST API Controller for Mobile Application (elearning_mobile)
 * E-Learning SMK Muthia Harapan Cicalengka
 */

require_once ROOT_PATH . 'config/database.php';

class ApiController {
    private $db;

    public function __construct() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        try {
            $this->db = Database::getConnection();
        } catch (\Throwable $e) {
            $this->jsonResponse(false, 'Koneksi Database Server Gagal: ' . $e->getMessage(), null, 500);
        }
    }

    private function jsonResponse($success, $message, $data = null, $statusCode = 200) {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => (bool)$success,
            'status' => (bool)$success,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }

    private function getPostInput() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        if (is_array($data)) {
            return $data;
        }
        return $_POST;
    }

    public function index() {
        $this->jsonResponse(true, 'SMK Muthia Harapan E-Learning Mobile API v1.0', [
            'app' => 'E-Learning Mobile',
            'version' => '1.0.0',
            'status' => 'online'
        ]);
    }

    public function login() {
        $input = $this->getPostInput();
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');

        if (empty($username) || empty($password)) {
            $this->jsonResponse(false, 'Username dan password wajib diisi!', null, 400);
        }

        try {
            // Safe query compatible with or without roles table join
            $user = null;
            try {
                $stmt = $this->db->prepare("
                    SELECT u.*, r.name as role_name 
                    FROM users u 
                    LEFT JOIN roles r ON u.role_id = r.id 
                    WHERE u.username = ? OR u.email = ?
                    LIMIT 1
                ");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();
            } catch (\Throwable $e1) {
                // Fallback query if roles table or role_id does not exist
                $stmt = $this->db->prepare("
                    SELECT u.* 
                    FROM users u 
                    WHERE u.username = ? OR u.email = ?
                    LIMIT 1
                ");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();
            }

            if (!$user || !isset($user['password']) || !password_verify($password, $user['password'])) {
                $this->jsonResponse(false, 'Username atau password salah!', null, 401);
            }

            if (isset($user['status']) && $user['status'] !== 'active') {
                $this->jsonResponse(false, 'Akun Anda sedang tidak aktif!', null, 403);
            }

            $role = strtolower($user['role_name'] ?? $user['role'] ?? 'siswa');
            if ($role !== 'guru' && $role !== 'siswa') {
                $this->jsonResponse(false, 'Akses mobile hanya tersedia untuk Guru dan Siswa.', null, 403);
            }

            $details = null;
            if ($role === 'guru') {
                try {
                    $stmtG = $this->db->prepare("SELECT * FROM guru WHERE user_id = :uid LIMIT 1");
                    $stmtG->execute(['uid' => $user['id']]);
                    $details = $stmtG->fetch() ?: null;
                } catch (\Throwable $eG) {}
            } else if ($role === 'siswa') {
                try {
                    $stmtS = $this->db->prepare("
                        SELECT s.*, k.nama_kelas, j.nama_jurusan 
                        FROM siswa s 
                        LEFT JOIN kelas k ON s.kelas_id = k.id 
                        LEFT JOIN jurusan j ON s.jurusan_id = j.id 
                        WHERE s.user_id = :uid LIMIT 1
                    ");
                    $stmtS->execute(['uid' => $user['id']]);
                    $details = $stmtS->fetch() ?: null;
                } catch (\Throwable $eS) {
                    try {
                        $stmtS = $this->db->prepare("SELECT * FROM siswa WHERE user_id = :uid LIMIT 1");
                        $stmtS->execute(['uid' => $user['id']]);
                        $details = $stmtS->fetch() ?: null;
                    } catch (\Throwable $eS2) {}
                }

                if (!$details) {
                    try {
                        require_once ROOT_PATH . 'models/SiswaModel.php';
                        $sm = new SiswaModel();
                        $details = $sm->ensureSiswaProfile($user['id'], $user['full_name']);
                    } catch (\Throwable $eEns) {}
                }
            }

            $avFile = $user['avatar'] ?? ($details['foto'] ?? ($details['foto_profil'] ?? ($details['avatar'] ?? '')));
            if (!empty($avFile) && $avFile !== 'default_avatar.png' && $avFile !== 'default.png') {
                $user['avatar_url'] = BASE_URL . 'assets/uploads/profile/' . $avFile;
            } else {
                $user['avatar_url'] = null;
            }

            // Token simple generator for mobile session
            $token = bin2hex(random_bytes(32));

            unset($user['password']);

            $this->jsonResponse(true, 'Login berhasil', [
                'user' => $user,
                'role' => $role,
                'details' => $details,
                'token' => $token
            ]);
        } catch (\Throwable $e) {
            $this->jsonResponse(false, 'Proses login bermasalah: ' . $e->getMessage(), null, 500);
        }
    }

    public function siswa($endpoint = 'dashboard') {
        $endpoint = strtolower(trim(explode('?', $endpoint)[0], '/'));
        $endpoint = str_replace('-', '_', $endpoint);
        $input = $this->getPostInput();
        $userId = intval($_GET['user_id'] ?? $_POST['user_id'] ?? $input['user_id'] ?? $_GET['siswa_id'] ?? $_POST['siswa_id'] ?? $input['siswa_id'] ?? 0);
        if ($userId === 0) {
            $rawQuery = $_SERVER['QUERY_STRING'] ?? '';
            if (preg_match('/user_id=(\d+)/i', $rawQuery, $mUid)) {
                $userId = intval($mUid[1]);
            } elseif (preg_match('/siswa_id=(\d+)/i', $rawQuery, $mSid)) {
                $userId = intval($mSid[1]);
            }
        }
        if ($userId === 0 && AuthHelper::check()) {
            $sessionUser = AuthHelper::user();
            if (strtolower($sessionUser['role_name'] ?? '') === 'siswa') {
                $userId = intval($sessionUser['id'] ?? 0);
            }
        }

        require_once ROOT_PATH . 'models/SiswaModel.php';
        require_once ROOT_PATH . 'models/AcademicModel.php';
        require_once ROOT_PATH . 'models/LearningModel.php';
        require_once ROOT_PATH . 'models/ExamModel.php';
        require_once ROOT_PATH . 'models/CommunicationModel.php';

        $siswaModel = new SiswaModel();
        $academicModel = new AcademicModel();
        $learningModel = new LearningModel();
        $examModel = new ExamModel();
        $commModel = new CommunicationModel();

        // Get logged in user details if available
        $userObj = null;
        if ($userId > 0) {
            $stmtU = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmtU->execute([$userId]);
            $userObj = $stmtU->fetch(PDO::FETCH_ASSOC);
        }

        $siswa = null;
        if ($userId > 0) {
            $siswa = $siswaModel->ensureSiswaProfile($userId, $userObj['full_name'] ?? '');
        }

        if (!$siswa && $userId > 0) {
            $stmtS2 = $this->db->prepare("
                SELECT s.*, k.nama_kelas, j.nama_jurusan 
                FROM siswa s 
                LEFT JOIN kelas k ON s.kelas_id = k.id 
                LEFT JOIN jurusan j ON s.jurusan_id = j.id 
                WHERE s.id = :sid LIMIT 1
            ");
            $stmtS2->execute(['sid' => $userId]);
            $siswa = $stmtS2->fetch(PDO::FETCH_ASSOC);
        }

        if (!$siswa) {
            $stmtS = $this->db->query("
                SELECT s.*, k.nama_kelas, j.nama_jurusan 
                FROM siswa s 
                LEFT JOIN kelas k ON s.kelas_id = k.id 
                LEFT JOIN jurusan j ON s.jurusan_id = j.id 
                ORDER BY s.id ASC LIMIT 1
            ");
            $siswa = $stmtS->fetch(PDO::FETCH_ASSOC);
        }

        if ($siswa) {
            if (empty($siswa['nama_kelas']) || empty($siswa['nama_jurusan'])) {
                $stmtDetails = $this->db->prepare("
                    SELECT s.*, k.nama_kelas, j.nama_jurusan 
                    FROM siswa s 
                    LEFT JOIN kelas k ON s.kelas_id = k.id 
                    LEFT JOIN jurusan j ON s.jurusan_id = j.id 
                    WHERE s.id = :sid LIMIT 1
                ");
                $stmtDetails->execute(['sid' => $siswa['id']]);
                $fullDetails = $stmtDetails->fetch(PDO::FETCH_ASSOC);
                if ($fullDetails) {
                    $siswa = array_merge($siswa, $fullDetails);
                }
            }
        }

        if (!$siswa) {
            $this->jsonResponse(false, 'Data siswa tidak ditemukan', null, 404);
        }

        $siswaId = intval($siswa['id'] ?? 0);
        $kelasId = intval($siswa['kelas_id'] ?? 0);

        $enrolledList = [];
        try {
            $enrolledList = $academicModel->getSiswaEnrolledMapels($siswaId);
        } catch (\Throwable $eEnr) {
            $enrolledList = [];
        }

        $enrolledMapels = [];
        foreach ($enrolledList as $em) {
            if (!empty($em['mapel_id'])) {
                $enrolledMapels[$em['mapel_id'] . '_' . ($em['guru_id'] ?? 0)] = true;
                $enrolledMapels[$em['mapel_id']] = true;
            }
        }

        switch ($endpoint) {
            case 'dashboard':
                // 1. Active Academic Year & Semester
                $activeTa = $academicModel->getActiveTahunAjaran();

                // 2. Real Certificate & Performance Stats
                $certStats = $siswaModel->getSiswaCertificateRealStats($siswaId);

                // 3. Complete list using Web Model logic
                $allMateri = $learningModel->getMateri($kelasId);
                $allTugas = $learningModel->getTugas($kelasId);
                $allQuiz = $examModel->getQuizList($kelasId);

                $materiList = array_values(array_filter($allMateri, function($m) use ($enrolledMapels) {
                    if (empty($enrolledMapels)) return true;
                    return isset($enrolledMapels[$m['mapel_id'] . '_' . ($m['guru_id'] ?? 0)]) || isset($enrolledMapels[$m['mapel_id']]);
                }));

                $tugasList = array_values(array_filter($allTugas, function($t) use ($enrolledMapels) {
                    if (empty($enrolledMapels)) return true;
                    return isset($enrolledMapels[$t['mapel_id'] . '_' . ($t['guru_id'] ?? 0)]) || isset($enrolledMapels[$t['mapel_id']]);
                }));

                $quizList = array_values(array_filter($allQuiz, function($q) use ($enrolledMapels) {
                    if (empty($enrolledMapels)) return true;
                    return isset($enrolledMapels[$q['mapel_id'] . '_' . ($q['guru_id'] ?? 0)]) || isset($enrolledMapels[$q['mapel_id']]);
                }));

                $totalMateri = count($materiList);
                $totalTugas = count($tugasList);
                $totalQuiz = count($quizList);

                // 4. Nearest Task Deadline
                $tugasTerdekat = null;
                if (!empty($tugasList)) {
                    $firstTugas = $tugasList[0];
                    $tugasTerdekat = [
                        'id' => intval($firstTugas['id']),
                        'judul' => $firstTugas['judul'] ?? '',
                        'deadline' => $firstTugas['deadline'] ?? '',
                        'mapel_id' => intval($firstTugas['mapel_id'] ?? 0)
                    ];
                }

                // 5. Chart Data (Average grade per subject for student)
                $stmtChart = $this->db->prepare("
                    SELECT m.nama_mapel, ROUND(AVG(COALESCE(pt.nilai, hq.total_nilai)), 1) as avg_nilai
                    FROM mata_pelajaran m
                    LEFT JOIN tugas t ON t.mapel_id = m.id
                    LEFT JOIN pengumpulan_tugas pt ON pt.tugas_id = t.id AND pt.siswa_id = ? AND pt.nilai IS NOT NULL
                    LEFT JOIN quiz q ON q.mapel_id = m.id
                    LEFT JOIN hasil_quiz hq ON hq.quiz_id = q.id AND hq.siswa_id = ? AND hq.total_nilai IS NOT NULL
                    WHERE pt.nilai IS NOT NULL OR hq.total_nilai IS NOT NULL
                    GROUP BY m.id, m.nama_mapel
                    LIMIT 6
                ");
                $stmtChart->execute([$siswaId, $siswaId]);
                $chartData = $stmtChart->fetchAll();

                // 6. Announcements (Siswa + All Target)
                $pengumumanList = $commModel->getPengumuman('siswa');
                $pengumuman = [];
                foreach ($pengumumanList as $p) {
                    $bUrl = null;
                    if (!empty($p['banner'])) {
                        $bUrl = (strpos($p['banner'], 'http') === 0) ? $p['banner'] : BASE_URL . ltrim($p['banner'], '/');
                    }
                    $p['banner_url'] = $bUrl;
                    $p['banner'] = $bUrl ?: ($p['banner'] ?? null);
                    $pengumuman[] = $p;
                }

                // 7. Jadwal Pelajaran (Hari ini & Full Rombel)
                $jadwalList = $academicModel->getJadwal($kelasId);
                if (empty($jadwalList)) {
                    $stmtJAll = $this->db->prepare("
                        SELECT j.*, m.nama_mapel, g.nama_lengkap as nama_guru 
                        FROM jadwal j 
                        LEFT JOIN mata_pelajaran m ON j.mapel_id = m.id 
                        LEFT JOIN guru g ON j.guru_id = g.id 
                        WHERE (j.kelas_id = :kid OR FIND_IN_SET(:kid, j.kelas_ids) OR j.kelas_id IS NULL OR j.kelas_id = 0) 
                        ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), j.jam_mulai ASC
                    ");
                    $stmtJAll->execute(['kid' => $kelasId]);
                    $jadwalList = $stmtJAll->fetchAll();
                }

                $days = [1=>'Senin', 2=>'Selasa', 3=>'Rabu', 4=>'Kamis', 5=>'Jumat', 6=>'Sabtu', 7=>'Minggu'];
                $today = $days[date('N')] ?? 'Senin';

                $jadwalToday = array_values(array_filter($jadwalList, function($j) use ($today) {
                    return strcasecmp($j['hari'] ?? '', $today) === 0;
                }));

                // 8. Presensi Hari Ini
                $tglNow = date('Y-m-d');
                $stmtAbsToday = $this->db->prepare("
                    SELECT * FROM absensi 
                    WHERE siswa_id = :sid AND (tanggal = :tgl1 OR DATE(created_at) = :tgl2 OR DATE(waktu_masuk) = :tgl3)
                    ORDER BY id DESC LIMIT 1
                ");
                $stmtAbsToday->execute(['sid' => $siswaId, 'tgl1' => $tglNow, 'tgl2' => $tglNow, 'tgl3' => $tglNow]);
                $absToday = $stmtAbsToday->fetch();

                $hasClockedIn = false;
                $hasClockedOut = false;
                $isAbsent = false;
                $statusToday = null;

                if ($absToday) {
                    $st = strtolower($absToday['status'] ?? '');
                    $statusToday = $absToday['status'];
                    if ($st === 'izin' || $st === 'sakit' || $st === 'alpha' || $st === 'alpa') {
                        $isAbsent = true;
                    } else {
                        $hasClockedIn = true;
                        if (!empty($absToday['waktu_pulang'])) {
                            $hasClockedOut = true;
                        }
                    }
                }

                $presensiLogVal = $certStats['presensi_log'] ?? '0%';
                if ($presensiLogVal === 'Belum Ada Data') {
                    $presensiLogVal = '0%';
                }

                $hakAksesInfo = [
                    'role' => 'Siswa',
                    'role_id' => 3,
                    'status_akun' => $siswa['status'] ?? 'aktif',
                    'hak_akses' => 'Siswa (Aktif)',
                    'is_active' => true,
                    'nis' => $siswa['nis'] ?? '-',
                    'nisn' => $siswa['nisn'] ?? '-',
                    'kelas' => $siswa['nama_kelas'] ?? '-',
                    'jurusan' => $siswa['nama_jurusan'] ?? '-',
                ];

                $this->jsonResponse(true, 'Data Dashboard Siswa Terhubung Realtime', [
                    'active_ta' => $activeTa ?: ['tahun_ajaran' => '2025/2026', 'semester' => 'Ganjil'],
                    'siswa_profile' => array_merge($siswa ?: [], [
                        'role_name' => 'Siswa',
                        'hak_akses' => 'Siswa (Aktif)',
                        'status' => $siswa['status'] ?? 'aktif'
                    ]),
                    'hak_akses_info' => $hakAksesInfo,
                    'cert_stats' => $certStats,
                    'stats' => [
                        'materi' => $totalMateri,
                        'tugas' => $totalTugas,
                        'quiz' => $totalQuiz,
                        'presensi_log' => $presensiLogVal
                    ],
                    'tugas_terdekat' => $tugasTerdekat,
                    'chart_data' => $chartData,
                    'pengumuman' => $pengumuman,
                    'jadwal_list' => $jadwalList,
                    'jadwal_hari_ini' => $jadwalToday,
                    'absensi_today' => [
                        'has_clocked_in' => $hasClockedIn,
                        'has_clocked_out' => $hasClockedOut,
                        'is_absent' => $isAbsent,
                        'status' => $statusToday,
                        'waktu_masuk' => $absToday['waktu_masuk'] ?? null,
                        'waktu_pulang' => $absToday['waktu_pulang'] ?? null,
                    ]
                ]);
                break;

            case 'learning_path':
            case 'learningPath':
            case 'alur_belajar':
                require_once ROOT_PATH . 'models/AcademicModel.php';
                $academicModel = new AcademicModel();

                $siswaId = intval($siswa['id'] ?? 0);
                $kelasId = intval($siswa['kelas_id'] ?? 0);

                // Get enrolled mapels
                $enrolledListRaw = [];
                try {
                    $enrolledListRaw = $academicModel->getSiswaEnrolledMapels($siswaId);
                } catch (\Throwable $eEnr) {
                    $enrolledListRaw = [];
                }

                $enrolledMapelMap = [];
                foreach ($enrolledListRaw as $em) {
                    $mId = intval($em['mapel_id'] ?? $em['id'] ?? 0);
                    if ($mId > 0) {
                        $enrolledMapelMap[$mId] = true;
                    }
                }

                $classMapels = [];
                if ($kelasId > 0) {
                    $classMapels = $academicModel->getMapelByKelas($kelasId);
                }
                if (empty($classMapels)) {
                    $classMapels = $academicModel->getMapel();
                }

                $seenMapelIds = [];
                $mergedList = [];

                foreach ($enrolledListRaw as $em) {
                    $mId = intval($em['mapel_id'] ?? $em['id'] ?? 0);
                    if ($mId > 0 && !isset($seenMapelIds[$mId])) {
                        $seenMapelIds[$mId] = true;
                        $em['is_enrolled'] = true;
                        $mergedList[] = $em;
                    }
                }

                foreach ($classMapels as $cm) {
                    $mId = intval($cm['mapel_id'] ?? $cm['id'] ?? 0);
                    if ($mId > 0 && !isset($seenMapelIds[$mId])) {
                        $seenMapelIds[$mId] = true;
                        $cm['is_enrolled'] = isset($enrolledMapelMap[$mId]);
                        $mergedList[] = $cm;
                    }
                }

                $enrolledList = $mergedList;

                $mapelList = [];
                $selesaiCount = 0;
                $prosesCount = 0;
                $belumCount = 0;
                $totalProgressSum = 0;

                foreach ($enrolledList as $em) {
                    $mId = intval($em['mapel_id'] ?? $em['id'] ?? 0);
                    $kId = !empty($em['kelas_id']) ? intval($em['kelas_id']) : $kelasId;
                    $namaMapel = $em['nama_mapel'] ?? 'Mata Pelajaran';
                    $kodeMapel = $em['kode_mapel'] ?? ('MP' . $mId);
                    $namaGuru = $em['nama_guru'] ?? 'Guru Pengampu';

                    $stmtMat = $this->db->prepare("
                        SELECT m.*, COALESCE(g.nama_lengkap, u.full_name, 'Guru Pengampu') as nama_guru
                        FROM materi m
                        LEFT JOIN guru g ON m.guru_id = g.id
                        LEFT JOIN users u ON g.user_id = u.id
                        WHERE m.mapel_id = ?
                          AND ({$kId} = 0 OR m.kelas_id = {$kId} OR m.kelas_id IS NULL OR m.kelas_id = 0)
                        ORDER BY m.id ASC
                    ");
                    $stmtMat->execute([$mId]);
                    $materiRows = $stmtMat->fetchAll();

                    $stmtTug = $this->db->prepare("
                        SELECT t.*, COALESCE(g.nama_lengkap, u.full_name, 'Guru Pengampu') as nama_guru,
                                pt.id as submission_id, pt.nilai, pt.submitted_at, pt.komentar_guru
                        FROM tugas t
                        LEFT JOIN guru g ON t.guru_id = g.id
                        LEFT JOIN users u ON g.user_id = u.id
                        LEFT JOIN pengumpulan_tugas pt ON (pt.tugas_id = t.id AND pt.siswa_id = ?)
                        WHERE t.mapel_id = ?
                          AND ({$kId} = 0 OR t.kelas_id = {$kId} OR t.kelas_id IS NULL OR t.kelas_id = 0)
                        ORDER BY t.id ASC
                    ");
                    $stmtTug->execute([$siswaId, $mId]);
                    $tugasRows = $stmtTug->fetchAll();

                    $stmtQz = $this->db->prepare("
                        SELECT q.*, COALESCE(g.nama_lengkap, u.full_name, 'Guru Pengampu') as nama_guru,
                                hq.id as hasil_id, hq.total_nilai, hq.status_lulus, hq.finished_at
                        FROM quiz q
                        LEFT JOIN guru g ON q.guru_id = g.id
                        LEFT JOIN users u ON g.user_id = u.id
                        LEFT JOIN (
                            SELECT * FROM hasil_quiz WHERE siswa_id = ?
                        ) hq ON hq.quiz_id = q.id
                        WHERE q.mapel_id = ?
                          AND ({$kId} = 0 OR q.kelas_id = {$kId} OR q.kelas_id IS NULL OR q.kelas_id = 0)
                          AND (q.status IS NULL OR q.status = 'published')
                        ORDER BY q.id ASC
                    ");
                    $stmtQz->execute([$siswaId, $mId]);
                    $quizRows = $stmtQz->fetchAll();

                    $stmtUj = $this->db->prepare("
                        SELECT u.*, COALESCE(g.nama_lengkap, us.full_name, 'Guru Pengampu') as nama_guru,
                                hu.id as hasil_id, hu.total_nilai, hu.status as status_hasil, hu.finished_at
                        FROM ujian u
                        LEFT JOIN guru g ON u.guru_id = g.id
                        LEFT JOIN users us ON g.user_id = us.id
                        LEFT JOIN (
                            SELECT * FROM hasil_ujian WHERE siswa_id = ?
                        ) hu ON hu.ujian_id = u.id
                        WHERE u.mapel_id = ?
                          AND ({$kId} = 0 OR u.kelas_id = {$kId} OR u.kelas_id IS NULL OR u.kelas_id = 0)
                          AND u.is_active = 1
                        ORDER BY u.id ASC
                    ");
                    $stmtUj->execute([$siswaId, $mId]);
                    $ujianRows = $stmtUj->fetchAll();

                    $sequenceItems = [];

                    // 1. Real Materi added by Guru
                    foreach ($materiRows as $mItem) {
                        $fileUrl = !empty($mItem['file_path']) 
                            ? (str_starts_with($mItem['file_path'], 'http') ? $mItem['file_path'] : BASE_URL . ltrim($mItem['file_path'], '/'))
                            : null;

                        $sequenceItems[] = [
                            'id' => intval($mItem['id']),
                            'type' => 'materi',
                            'title' => $mItem['judul'],
                            'desc' => $mItem['deskripsi'] ?: 'Modul materi pembelajaran KBM.',
                            'guru' => $mItem['nama_guru'] ?: $namaGuru,
                            'file_url' => $fileUrl,
                            'is_completed' => true,
                            'status_text' => 'Tersedia',
                            'action_label' => 'Buka Materi',
                            'action_type' => 'materi'
                        ];
                    }

                    // 2. Real Tugas added by Guru & Student Submission History
                    foreach ($tugasRows as $tItem) {
                        $isSub = !empty($tItem['submission_id']);
                        $nilai = ($tItem['nilai'] !== null) ? floatval($tItem['nilai']) : null;
                        $submittedAt = $tItem['submitted_at'] ?? null;
                        $komentarGuru = $tItem['komentar_guru'] ?? null;

                        $statusText = 'Belum Dikerjakan';
                        $actionLabel = 'Kirim Tugas';

                        if ($isSub) {
                            if ($nilai !== null) {
                                $statusText = "Sudah Dinilai: $nilai / 100";
                                $actionLabel = "History Tugas (Nilai: $nilai)";
                            } else {
                                $statusText = "Terkirim: " . ($submittedAt ? date('d/m/Y H:i', strtotime($submittedAt)) : 'Menunggu Nilai');
                                $actionLabel = "History Pengerjaan";
                            }
                        }

                        $sequenceItems[] = [
                            'id' => intval($tItem['id']),
                            'type' => 'tugas',
                            'title' => 'Penugasan: ' . $tItem['judul'],
                            'desc' => $tItem['deskripsi'] ?: 'Tugas KBM & Praktikum.',
                            'guru' => $tItem['nama_guru'] ?: $namaGuru,
                            'is_completed' => $isSub,
                            'submission_id' => $tItem['submission_id'] ? intval($tItem['submission_id']) : null,
                            'nilai' => $nilai,
                            'submitted_at' => $submittedAt,
                            'komentar_guru' => $komentarGuru,
                            'status_text' => $statusText,
                            'action_label' => $actionLabel,
                            'action_type' => 'tugas'
                        ];
                    }

                    // 3. Real Quiz added by Guru & Completion History
                    foreach ($quizRows as $qItem) {
                        $isFin = !empty($qItem['finished_at']) || !empty($qItem['hasil_id']);
                        $totalNilai = ($qItem['total_nilai'] !== null) ? floatval($qItem['total_nilai']) : null;
                        $statusLulus = $qItem['status_lulus'] ?? null;

                        $statusText = 'Belum Diikuti';
                        $actionLabel = 'Ikuti Kuis';

                        if ($isFin) {
                            $statusText = "Selesai (" . ($statusLulus == 'lulus' ? 'Lulus' : 'Selesai') . "): " . ($totalNilai !== null ? $totalNilai : 0) . " / 100";
                            $actionLabel = "History Kuis" . ($totalNilai !== null ? " ($totalNilai)" : "");
                        }

                        $sequenceItems[] = [
                            'id' => intval($qItem['id']),
                            'type' => 'quiz',
                            'title' => 'Evaluasi Kuis: ' . $qItem['judul'],
                            'desc' => 'Kuis Online Berbasis CBT.',
                            'guru' => $qItem['nama_guru'] ?: $namaGuru,
                            'is_completed' => $isFin,
                            'hasil_id' => $qItem['hasil_id'] ? intval($qItem['hasil_id']) : null,
                            'total_nilai' => $totalNilai,
                            'status_lulus' => $statusLulus,
                            'status_text' => $statusText,
                            'action_label' => $actionLabel,
                            'action_type' => 'quiz'
                        ];
                    }

                    // 4. Real Ujian (UTS / UAS / PAT) added by Guru & Exam History
                    foreach ($ujianRows as $uItem) {
                        $isFin = !empty($uItem['finished_at']) || ($uItem['status_hasil'] ?? '') === 'selesai';
                        $totalNilai = ($uItem['total_nilai'] !== null) ? floatval($uItem['total_nilai']) : null;
                        $jenisUjian = strtoupper($uItem['jenis_ujian'] ?? 'UTS');

                        $statusText = 'Belum Diikuti';
                        $actionLabel = 'Ikuti ' . $jenisUjian;

                        if ($isFin) {
                            $statusText = "Selesai: " . ($totalNilai !== null ? $totalNilai : 0) . " / 100";
                            $actionLabel = "History $jenisUjian" . ($totalNilai !== null ? " ($totalNilai)" : "");
                        }

                        $sequenceItems[] = [
                            'id' => intval($uItem['id']),
                            'type' => strtolower($jenisUjian),
                            'title' => $jenisUjian . ': ' . $uItem['nama_ujian'],
                            'desc' => 'Ujian Resmi Semester Berbasis CBT.',
                            'guru' => $uItem['nama_guru'] ?: $namaGuru,
                            'is_completed' => $isFin,
                            'total_nilai' => $totalNilai,
                            'status_text' => $statusText,
                            'action_label' => $actionLabel,
                            'action_type' => 'cbt'
                        ];
                    }

                    $totalItems = count($sequenceItems);
                    $completedItems = 0;
                    foreach ($sequenceItems as $seq) {
                        if (!empty($seq['is_completed'])) {
                            $completedItems++;
                        }
                    }

                    $progressPct = ($totalItems > 0) ? intval(round(($completedItems / $totalItems) * 100)) : 0;
                    $totalProgressSum += $progressPct;

                    $statusCat = 'belum_dimulai';
                    $statusLabel = 'Belum Dimulai';

                    if ($progressPct >= 100 && $totalItems > 0) {
                        $statusCat = 'selesai';
                        $statusLabel = 'Selesai Tuntas';
                        $selesaiCount++;
                    } elseif ($progressPct > 0) {
                        $statusCat = 'dalam_proses';
                        $statusLabel = 'Dalam Proses';
                        $prosesCount++;
                    } else {
                        $belumCount++;
                    }

                    $currentStep = min(5, max(1, intval(ceil(($progressPct / 100) * 5))));
                    if ($progressPct == 0) $currentStep = 1;

                    $steps = [
                        [
                            'step_no' => 1,
                            'title' => 'Tahap 1: Modul Teori & Materi Digital',
                            'judul' => 'Tahap 1: Modul Teori & Materi Digital',
                            'desc' => 'Pelajari konsep dasar & modul KBM.',
                            'sub' => 'Pelajari konsep dasar & modul KBM.',
                            'is_completed' => ($progressPct >= 20 || $completedItems >= 1),
                            'is_unlocked' => true,
                            'is_active' => ($currentStep === 1),
                            'is_current' => ($currentStep === 1),
                            'is_locked' => false,
                            'completed_count' => ($completedItems >= 1 ? 1 : 0),
                            'total_count' => count($materiRows) ?: 1,
                            'action_label' => 'Buka Materi',
                            'action_type' => 'materi'
                        ],
                        [
                            'step_no' => 2,
                            'title' => 'Tahap 2: Diskusi KBM & Praktikum',
                            'judul' => 'Tahap 2: Diskusi KBM & Praktikum',
                            'desc' => 'Praktik langsung & pendalaman materi.',
                            'sub' => 'Praktik langsung & pendalaman materi.',
                            'is_completed' => ($progressPct >= 40),
                            'is_unlocked' => ($progressPct >= 20 || $completedItems >= 1),
                            'is_active' => ($currentStep === 2),
                            'is_current' => ($currentStep === 2),
                            'is_locked' => !($progressPct >= 20 || $completedItems >= 1),
                            'completed_count' => ($progressPct >= 40 ? 1 : 0),
                            'total_count' => 1,
                            'action_label' => 'Buka Diskusi',
                            'action_type' => 'materi'
                        ],
                        [
                            'step_no' => 3,
                            'title' => 'Tahap 3: Penugasan KBM Terstruktur',
                            'judul' => 'Tahap 3: Penugasan KBM Terstruktur',
                            'desc' => 'Kirim laporan & tugas praktik.',
                            'sub' => 'Kirim laporan & tugas praktik.',
                            'is_completed' => ($progressPct >= 60),
                            'is_unlocked' => ($progressPct >= 40),
                            'is_active' => ($currentStep === 3),
                            'is_current' => ($currentStep === 3),
                            'is_locked' => !($progressPct >= 40),
                            'completed_count' => ($progressPct >= 60 ? count($tugasRows) : 0),
                            'total_count' => count($tugasRows) ?: 1,
                            'action_label' => 'Kirim Tugas',
                            'action_type' => 'tugas'
                        ],
                        [
                            'step_no' => 4,
                            'title' => 'Tahap 4: Uji Evaluasi & Kuis CBT',
                            'judul' => 'Tahap 4: Uji Evaluasi & Kuis CBT',
                            'desc' => 'Evaluasi kompetensi berbasis CBT.',
                            'sub' => 'Evaluasi kompetensi berbasis CBT.',
                            'is_completed' => ($progressPct >= 80),
                            'is_unlocked' => ($progressPct >= 60),
                            'is_active' => ($currentStep === 4),
                            'is_current' => ($currentStep === 4),
                            'is_locked' => !($progressPct >= 60),
                            'completed_count' => ($progressPct >= 80 ? count($quizRows) : 0),
                            'total_count' => count($quizRows) ?: 1,
                            'action_label' => 'Ikuti Kuis',
                            'action_type' => 'quiz'
                        ],
                        [
                            'step_no' => 5,
                            'title' => 'Tahap 5: Tuntas Semester & Sertifikasi',
                            'judul' => 'Tahap 5: Tuntas Semester & Sertifikasi',
                            'desc' => 'Kelulusan modul & sertifikat KBM.',
                            'sub' => 'Kelulusan modul & sertifikat KBM.',
                            'is_completed' => ($progressPct >= 100),
                            'is_unlocked' => ($progressPct >= 80),
                            'is_active' => ($currentStep === 5),
                            'is_current' => ($currentStep === 5),
                            'is_locked' => !($progressPct >= 80),
                            'completed_count' => ($progressPct >= 100 ? 1 : 0),
                            'total_count' => 1,
                            'action_label' => 'Sertifikat',
                            'action_type' => 'cbt'
                        ]
                    ];

                    $mapelList[] = [
                        'mapel_id' => $mId,
                        'nama_mapel' => $namaMapel,
                        'kode_mapel' => $kodeMapel,
                        'nama_guru' => $namaGuru,
                        'is_enrolled' => !empty($em['is_enrolled']),
                        'status_category' => $statusCat,
                        'status_label' => $statusLabel,
                        'progress_percent' => $progressPct,
                        'current_step' => $currentStep,
                        'steps' => $steps,
                        'sequence_items' => $sequenceItems
                    ];
                }

                $totalMapel = count($mapelList);
                $capaianPersen = ($totalMapel > 0) ? intval(round($totalProgressSum / $totalMapel)) : 0;

                $this->jsonResponse(true, 'Alur Learning Path Siswa', [
                    'tingkat' => $siswa['nama_kelas'] ?? 'Kelas Siswa',
                    'jurusan' => $siswa['nama_jurusan'] ?? 'Teknik & Kejuruan',
                    'nama_kelas' => $siswa['nama_kelas'] ?? 'Kelas Siswa',
                    'nama_jurusan' => $siswa['nama_jurusan'] ?? 'Teknik & Kejuruan',
                    'capaian_persen' => $capaianPersen,
                    'total_mapel' => $totalMapel,
                    'selesai_count' => $selesaiCount,
                    'proses_count' => $prosesCount,
                    'belum_count' => $belumCount,
                    'mapel_list' => $mapelList
                ]);
                break;

            case 'jadwal':
                $stmtJ = $this->db->prepare("
                    SELECT j.*, m.nama_mapel, m.kode_mapel, g.nama_lengkap as nama_guru 
                    FROM jadwal j 
                    JOIN mata_pelajaran m ON j.mapel_id = m.id 
                    JOIN guru g ON j.guru_id = g.id 
                    WHERE j.kelas_id = :kid 
                    ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), j.jam_mulai ASC
                ");
                $stmtJ->execute(['kid' => $siswa['kelas_id']]);
                $jadwal = $stmtJ->fetchAll();

                $this->jsonResponse(true, 'Data Jadwal Siswa', $jadwal);
                break;

            case 'materi':
                require_once ROOT_PATH . 'models/LearningModel.php';
                $learningModel = new LearningModel();
                $kelasId = intval($siswa['kelas_id'] ?? 0);

                $allMateri = $learningModel->getMateri($kelasId);
                $allVideos = $learningModel->getVideos($kelasId);

                $materiList = array_values(array_filter($allMateri, function($m) use ($enrolledMapels) {
                    if (empty($enrolledMapels)) return true;
                    return isset($enrolledMapels[$m['mapel_id'] . '_' . ($m['guru_id'] ?? 0)]) || isset($enrolledMapels[$m['mapel_id']]);
                }));

                $videoList = array_values(array_filter($allVideos, function($v) use ($enrolledMapels) {
                    if (empty($enrolledMapels)) return true;
                    return isset($enrolledMapels[$v['mapel_id'] . '_' . ($v['guru_id'] ?? 0)]) || isset($enrolledMapels[$v['mapel_id']]);
                }));

                $this->jsonResponse(true, 'Daftar Materi Pembelajaran', [
                    'materi' => $materiList,
                    'videos' => $videoList
                ]);
                break;

            case 'tugas':
                require_once ROOT_PATH . 'models/LearningModel.php';
                $learningModel = new LearningModel();
                $siswaId = intval($siswa['id'] ?? 0);
                $kelasId = intval($siswa['kelas_id'] ?? 0);

                $allTugas = $learningModel->getTugas($kelasId);

                // Fetch student submission map from pengumpulan_tugas
                $stmtSub = $this->db->prepare("SELECT * FROM pengumpulan_tugas WHERE siswa_id = ?");
                $stmtSub->execute([$siswaId]);
                $submittedList = $stmtSub->fetchAll();
                $submittedMap = [];
                foreach ($submittedList as $sub) {
                    $submittedMap[$sub['tugas_id']] = $sub;
                }

                $tugasList = array_values(array_filter($allTugas, function($t) use ($enrolledMapels) {
                    if (empty($enrolledMapels)) return true;
                    return isset($enrolledMapels[$t['mapel_id'] . '_' . ($t['guru_id'] ?? 0)]) || isset($enrolledMapels[$t['mapel_id']]);
                }));
                if (empty($tugasList) && !empty($allTugas)) {
                    $tugasList = $allTugas;
                }

                foreach ($tugasList as &$t) {
                    $tId = intval($t['id']);
                    $accessCheck = $learningModel->canSiswaSubmitTugas($tId, $siswaId);
                    $t['can_submit'] = $accessCheck['access'];
                    $t['is_expired'] = $accessCheck['is_expired'] ?? false;
                    $t['lock_status'] = $accessCheck['status'] ?? 'terbuka';
                    $susulanRec = (isset($accessCheck['susulan']) && is_array($accessCheck['susulan'])) ? $accessCheck['susulan'] : null;
                    $t['susulan_status'] = $susulanRec ? ($susulanRec['status'] ?? null) : null;

                    if (isset($submittedMap[$tId])) {
                        $sub = $submittedMap[$tId];
                        $t['submission_id'] = intval($sub['id']);
                        $t['nilai'] = $sub['nilai'] !== null ? floatval($sub['nilai']) : null;
                        $t['catatan_siswa'] = $sub['catatan_siswa'] ?? '';
                        $t['komentar_guru'] = $sub['komentar_guru'] ?? '';
                        $t['submitted_at'] = $sub['submitted_at'] ?? null;
                        $t['file_path_siswa'] = $sub['file_path'] ?? null;
                        $t['is_submitted'] = true;
                    } else {
                        $t['submission_id'] = null;
                        $t['nilai'] = null;
                        $t['catatan_siswa'] = null;
                        $t['komentar_guru'] = null;
                        $t['submitted_at'] = null;
                        $t['file_path_siswa'] = null;
                        $t['is_submitted'] = false;
                    }
                }
                unset($t);

                $this->jsonResponse(true, 'Daftar Tugas Siswa', $tugasList);
                break;

            case 'submit_tugas':
                $input = $this->getPostInput();
                $tugasId = intval($_POST['tugas_id'] ?? $input['tugas_id'] ?? 0);
                $catatan = trim($_POST['catatan_siswa'] ?? $input['catatan_siswa'] ?? '');
                $filePath = trim($_POST['file_path'] ?? $input['file_path'] ?? '');

                if ($tugasId <= 0) {
                    $this->jsonResponse(false, 'ID Tugas tidak valid', null, 400);
                }

                // Strict Deadline & Susulan Check
                $accessCheck = $learningModel->canSiswaSubmitTugas($tugasId, $siswa['id']);
                if (!$accessCheck['access']) {
                    $this->jsonResponse(false, 'Akses Pengumpulan Terkunci! Waktu pengumpulan tugas ini telah melewati deadline. Silakan ajukan izin Susulan ke Guru Pengampu.', null, 403);
                }

                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    require_once ROOT_PATH . 'helpers/UploadHelper.php';
                    $filePath = UploadHelper::upload($_FILES['file'], 'tugas');
                }

                // Check existing submission
                $stmtSub = $this->db->prepare("SELECT id FROM pengumpulan_tugas WHERE tugas_id = :tid AND siswa_id = :sid");
                $stmtSub->execute(['tid' => $tugasId, 'sid' => $siswa['id']]);
                $exist = $stmtSub->fetch();

                if ($exist) {
                    $stmtUpd = $this->db->prepare("
                        UPDATE pengumpulan_tugas 
                        SET catatan_siswa = :catatan, file_path = IF(:fp != '', :fp, file_path), submitted_at = NOW() 
                        WHERE id = :sub_id
                    ");
                    $stmtUpd->execute(['catatan' => $catatan, 'fp' => $filePath, 'sub_id' => $exist['id']]);
                } else {
                    $stmtIns = $this->db->prepare("
                        INSERT INTO pengumpulan_tugas (tugas_id, siswa_id, file_path, catatan_siswa, submitted_at) 
                        VALUES (:tid, :sid, :fp, :catatan, NOW())
                    ");
                    $stmtIns->execute(['tid' => $tugasId, 'sid' => $siswa['id'], 'fp' => $filePath, 'catatan' => $catatan]);
                }

                $this->jsonResponse(true, 'Tugas berhasil dikumpulkan!');
                break;

            case 'request_tugas_susulan':
            case 'request_tugas_permission':
                $input = $this->getPostInput();
                $tugasId = intval($_POST['tugas_id'] ?? $input['tugas_id'] ?? 0);
                $catatan = trim($_POST['catatan_susulan'] ?? $input['catatan_susulan'] ?? $_POST['catatan'] ?? $input['catatan'] ?? 'Permohonan izin pengumpulan tugas susulan via mobile app');

                if ($tugasId <= 0) {
                    $this->jsonResponse(false, 'ID Tugas tidak valid', null, 400);
                }

                $learningModel->requestTugasSusulan($tugasId, $siswa['id'], $catatan);

                try {
                    $commModel = new CommunicationModel();
                    $uName = $siswa['nama_lengkap'] ?? 'Siswa';
                    $commModel->sendNotificationToTeacherByTugas(
                        $tugasId,
                        '📩 Permintaan Izin Susulan Tugas',
                        "Siswa {$uName} mengajukan permohonan izin susulan pengumpulan Tugas via Mobile. Catatan: {$catatan}",
                        'index.php?url=guru/tugas'
                    );
                } catch (\Throwable $eN) {}

                $this->jsonResponse(true, 'Permohonan izin pengumpulan tugas susulan telah dikirimkan ke Guru Pengampu.');
                break;

            case 'quiz':
                require_once ROOT_PATH . 'models/ExamModel.php';
                $examModel = new ExamModel();
                $siswaId = intval($siswa['id'] ?? 0);
                $kelasId = intval($siswa['kelas_id'] ?? 0);

                $allQuiz = $examModel->getQuizList($kelasId);

                // Fetch student completed quiz attempts from hasil_quiz
                $stmtCompleted = $this->db->prepare("
                    SELECT quiz_id, total_nilai, nilai_tertinggi, status_lulus, finished_at, is_disqualified, pelanggaran_count,
                           (SELECT COUNT(*) FROM hasil_quiz_history hqh WHERE hqh.siswa_id = hasil_quiz.siswa_id AND hqh.quiz_id = hasil_quiz.quiz_id) as total_attempts
                    FROM hasil_quiz 
                    WHERE siswa_id = ?
                ");
                $stmtCompleted->execute([$siswaId]);
                $completedRows = $stmtCompleted->fetchAll();
                $completedMap = [];
                foreach ($completedRows as $cr) {
                    $completedMap[$cr['quiz_id']] = $cr;
                }

                $quizList = array_values(array_filter($allQuiz, function($q) use ($enrolledMapels) {
                    if (empty($enrolledMapels)) return true;
                    return isset($enrolledMapels[$q['mapel_id'] . '_' . ($q['guru_id'] ?? 0)]) || isset($enrolledMapels[$q['mapel_id']]);
                }));

                foreach ($quizList as &$q) {
                    $qId = intval($q['id']);
                    $accessCheck = $examModel->canSiswaAccessQuiz($qId, $siswaId);

                    $q['can_access'] = $accessCheck['access'];
                    $q['is_expired'] = $accessCheck['is_expired'] ?? false;
                    $q['access_status'] = $accessCheck['status'] ?? 'terbuka';
                    $q['access_reason'] = ($accessCheck['status'] ?? '') === 'diskualifikasi' 
                        ? 'Anda telah DIDISKUALIFIKASI karena 2x melanggar aturan (berpindah aplikasi / keluar fullscreen)'
                        : (isset($accessCheck['reason']) ? $accessCheck['reason'] : '');
                    $q['susulan_status'] = $accessCheck['susulan']['status'] ?? null;
                    $q['max_attempts'] = isset($accessCheck['max_attempts']) ? intval($accessCheck['max_attempts']) : (isset($q['max_attempts']) ? intval($q['max_attempts']) : 1);
                    $q['attempt_count'] = isset($accessCheck['attempt_count']) ? intval($accessCheck['attempt_count']) : 0;

                    if (isset($completedMap[$qId])) {
                        $cr = $completedMap[$qId];
                        $q['total_nilai'] = $cr['total_nilai'] !== null ? floatval($cr['total_nilai']) : null;
                        $q['nilai_tertinggi'] = $cr['nilai_tertinggi'] !== null ? floatval($cr['nilai_tertinggi']) : null;
                        $q['status_lulus'] = $cr['status_lulus'] ?? null;
                        $q['finished_at'] = $cr['finished_at'] ?? null;
                        $q['is_disqualified'] = (!empty($cr['is_disqualified']) && (int)$cr['is_disqualified'] === 1) || (($accessCheck['status'] ?? '') === 'diskualifikasi');
                        $q['pelanggaran_count'] = intval($cr['pelanggaran_count'] ?? $accessCheck['pelanggaran_count'] ?? 0);
                        $q['attempt_count'] = max($q['attempt_count'], intval($cr['total_attempts'] ?? 1));
                        $q['is_completed'] = true;
                    } else {
                        $q['total_nilai'] = null;
                        $q['nilai_tertinggi'] = null;
                        $q['status_lulus'] = null;
                        $q['finished_at'] = null;
                        $q['is_disqualified'] = ($accessCheck['status'] ?? '') === 'diskualifikasi';
                        $q['pelanggaran_count'] = intval($accessCheck['pelanggaran_count'] ?? 0);
                        $q['is_completed'] = false;
                    }
                }
                unset($q);

                $this->jsonResponse(true, 'Daftar Quiz / CBT Ujian Siswa', $quizList);
                break;

            case 'quiz_detail':
                require_once ROOT_PATH . 'models/ExamModel.php';
                $examModel = new ExamModel();
                $quizId = intval($_GET['quiz_id'] ?? 0);

                $stmtQ = $this->db->prepare("SELECT * FROM quiz WHERE id = :qid LIMIT 1");
                $stmtQ->execute(['qid' => $quizId]);
                $quiz = $stmtQ->fetch();

                if (!$quiz) {
                    $this->jsonResponse(false, 'Quiz tidak ditemukan', null, 404);
                }

                $accessCheck = $examModel->canSiswaAccessQuiz($quizId, $siswa['id']);
                if (!$accessCheck['access']) {
                    $reason = 'Akses kuis ini telah terkunci atau deadline telah berakhir.';
                    if (($accessCheck['status'] ?? '') === 'diskualifikasi') {
                        $reason = 'Akses Terkunci! Anda telah DIDISKUALIFIKASI dari kuis ini karena 2x melanggar aturan ujian online (berpindah aplikasi / keluar fullscreen). Silakan ajukan izin ke Guru.';
                    }
                    $this->jsonResponse(false, $reason, [
                        'can_access' => false,
                        'access_status' => $accessCheck['status'] ?? 'terkunci',
                        'is_disqualified' => (($accessCheck['status'] ?? '') === 'diskualifikasi')
                    ], 403);
                }

                $examModel->startQuizAttempt($quizId, $siswa['id']);

                $isRandomSoal = ($quiz['random_soal'] ?? 'Y') === 'Y';
                $isRandomJawaban = ($quiz['random_jawaban'] ?? 'Y') === 'Y';

                $orderClause = $isRandomSoal ? "ORDER BY RAND()" : "ORDER BY id ASC";
                $stmtSoal = $this->db->prepare("SELECT * FROM soal WHERE quiz_id = :qid {$orderClause}");
                $stmtSoal->execute(['qid' => $quizId]);
                $soalList = $stmtSoal->fetchAll();

                foreach ($soalList as &$s) {
                    $orderPilihan = $isRandomJawaban ? "ORDER BY RAND()" : "ORDER BY id ASC";
                    $stmtP = $this->db->prepare("SELECT id, soal_id, teks_pilihan FROM pilihan_jawaban WHERE soal_id = :sid {$orderPilihan}");
                    $stmtP->execute(['sid' => $s['id']]);
                    $s['pilihan'] = $stmtP->fetchAll();

                    $img = !empty($s['file_gambar']) ? $s['file_gambar'] : (!empty($s['gambar']) ? $s['gambar'] : null);
                    if (!empty($img)) {
                        $s['file_gambar'] = $img;
                        if (!str_starts_with($img, 'http://') && !str_starts_with($img, 'https://')) {
                            $cleanImg = ltrim($img, '/');
                            if (!str_starts_with($cleanImg, 'assets/uploads/') && !str_starts_with($cleanImg, 'uploads/')) {
                                $cleanImg = 'assets/uploads/soal/' . $cleanImg;
                            } else if (str_starts_with($cleanImg, 'uploads/')) {
                                $cleanImg = 'assets/' . $cleanImg;
                            }
                            
                            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                            
                            $s['file_gambar_url'] = "{$scheme}://{$host}/" . $cleanImg;
                        } else {
                            $s['file_gambar_url'] = $img;
                        }
                    } else {
                        $s['file_gambar'] = null;
                        $s['file_gambar_url'] = null;
                    }
                }

                $this->jsonResponse(true, 'Detail Quiz & Soal', [
                    'quiz' => $quiz,
                    'soal' => $soalList
                ]);
                break;

            case 'record_violation':
                require_once ROOT_PATH . 'models/ExamModel.php';
                $examModel = new ExamModel();
                $input = $this->getPostInput();
                $quizId = intval($input['quiz_id'] ?? $_GET['quiz_id'] ?? 0);

                if ($quizId <= 0) {
                    $this->jsonResponse(false, 'Quiz ID tidak valid', null, 400);
                }

                $res = $examModel->recordViolation($quizId, $siswa['id']);
                $this->jsonResponse(true, 'Pelanggaran dicatat', $res);
                break;

            case 'request_susulan':
            case 'ajukan_susulan':
                $input = $this->getPostInput();
                $tugasId = intval($_POST['tugas_id'] ?? $input['tugas_id'] ?? 0);
                $quizId = intval($_POST['quiz_id'] ?? $input['quiz_id'] ?? $_GET['quiz_id'] ?? 0);

                if ($tugasId > 0 && $quizId <= 0) {
                    $catatan = trim($_POST['catatan_susulan'] ?? $input['catatan_susulan'] ?? $_POST['catatan'] ?? $input['catatan'] ?? 'Permohonan izin pengumpulan tugas susulan via mobile app');
                    $learningModel->requestTugasSusulan($tugasId, $siswa['id'], $catatan);
                    try {
                        $commModel = new CommunicationModel();
                        $uName = $siswa['nama_lengkap'] ?? 'Siswa';
                        $commModel->sendNotificationToTeacherByTugas(
                            $tugasId,
                            '📩 Permintaan Izin Susulan Tugas',
                            "Siswa {$uName} mengajukan permohonan izin susulan pengumpulan Tugas via Mobile. Catatan: {$catatan}",
                            'index.php?url=guru/tugas'
                        );
                    } catch (\Throwable $eN) {}
                    $this->jsonResponse(true, 'Permohonan izin pengumpulan tugas susulan telah dikirimkan ke Guru Pengampu.');
                    break;
                }

                require_once ROOT_PATH . 'models/ExamModel.php';
                require_once ROOT_PATH . 'models/CommunicationModel.php';
                $examModel = new ExamModel();
                $catatan = trim($input['catatan'] ?? $input['alasan'] ?? 'Permohonan izin ujian susulan / buka suspend via mobile app');

                if ($quizId <= 0) {
                    $this->jsonResponse(false, 'Quiz ID atau Tugas ID tidak valid', null, 400);
                }

                $res = $examModel->requestSusulan($quizId, $siswa['id'], $catatan);

                try {
                    $commModel = new CommunicationModel();
                    $uName = $siswa['nama_lengkap'] ?? 'Siswa';
                    $commModel->sendNotificationToTeacherByQuiz(
                        $quizId,
                        '📩 Permintaan Izin Ujian Mobile',
                        "Siswa {$uName} mengajukan permohonan izin Ujian Susulan / Buka Suspend via Mobile. Catatan: {$catatan}",
                        'index.php?url=guru/quiz'
                    );
                } catch (\Throwable $eN) {}

                $this->jsonResponse(true, 'Permohonan izin Ujian Susulan / Buka Suspend telah dikirimkan ke Guru Pengampu.');
                break;

            case 'submit_quiz':
                $input = $this->getPostInput();
                $quizId = intval($input['quiz_id'] ?? 0);
                $answers = $input['answers'] ?? []; // Map of [soal_id => pilihan_id]
                $essayAnswers = $input['essay_answers'] ?? []; // Map of [soal_id => text_jawaban]

                if ($quizId <= 0) {
                    $this->jsonResponse(false, 'Quiz ID tidak valid', null, 400);
                }

                $stmtSoal = $this->db->prepare("SELECT * FROM soal WHERE quiz_id = :qid");
                $stmtSoal->execute(['qid' => $quizId]);
                $allSoal = $stmtSoal->fetchAll();
                $totalBobot = 0;
                $skorDapat = 0;

                foreach ($allSoal as $soal) {
                    $bobot = floatval($soal['bobot'] ?? 10);
                    if ($bobot <= 0) $bobot = 10;
                    $totalBobot += $bobot;

                    $soalId = $soal['id'];
                    $jenisSoal = strtolower($soal['jenis_soal'] ?? 'pg');

                    if ($jenisSoal === 'essay') {
                        $teksEssay = trim($essayAnswers[$soalId] ?? $essayAnswers[strval($soalId)] ?? '');

                        $stmtCheckJ = $this->db->prepare("SELECT id FROM jawaban_siswa WHERE siswa_id = :sid AND quiz_id = :qid AND soal_id = :soal_id LIMIT 1");
                        $stmtCheckJ->execute(['sid' => $siswa['id'], 'qid' => $quizId, 'soal_id' => $soalId]);
                        $existingJ = $stmtCheckJ->fetch();

                        if ($existingJ) {
                            $stmtJ = $this->db->prepare("
                                UPDATE jawaban_siswa 
                                SET teks_jawaban_essay = :teks, is_benar = 0, nilai = 0 
                                WHERE id = :jid
                            ");
                            $stmtJ->execute([
                                'jid' => $existingJ['id'],
                                'teks' => $teksEssay
                            ]);
                        } else {
                            $stmtJ = $this->db->prepare("
                                INSERT INTO jawaban_siswa (siswa_id, quiz_id, soal_id, teks_jawaban_essay, is_benar, nilai) 
                                VALUES (:sid, :qid, :soal_id, :teks, 0, 0)
                            ");
                            $stmtJ->execute([
                                'sid' => $siswa['id'],
                                'qid' => $quizId,
                                'soal_id' => $soalId,
                                'teks' => $teksEssay
                            ]);
                        }
                    } else {
                        $pilihanId = intval($answers[$soalId] ?? $answers[strval($soalId)] ?? 0);

                        // Check if answer correct
                        $isBenar = 0;
                        if ($pilihanId > 0) {
                            $stmtCheck = $this->db->prepare("SELECT is_benar FROM pilihan_jawaban WHERE id = :pid LIMIT 1");
                            $stmtCheck->execute(['pid' => $pilihanId]);
                            $pj = $stmtCheck->fetch();
                            if ($pj && ($pj['is_benar'] == 1 || $pj['is_benar'] === '1' || $pj['is_benar'] === true)) {
                                $isBenar = 1;
                                $skorDapat += $bobot;
                            }
                        }

                        // Save or Update student answer
                        $stmtCheckJ = $this->db->prepare("SELECT id FROM jawaban_siswa WHERE siswa_id = :sid AND quiz_id = :qid AND soal_id = :soal_id LIMIT 1");
                        $stmtCheckJ->execute(['sid' => $siswa['id'], 'qid' => $quizId, 'soal_id' => $soalId]);
                        $existingJ = $stmtCheckJ->fetch();

                        if ($existingJ) {
                            $stmtJ = $this->db->prepare("
                                UPDATE jawaban_siswa 
                                SET pilihan_id = :pid, is_benar = :ib, nilai = :nil 
                                WHERE id = :jid
                            ");
                            $stmtJ->execute([
                                'jid' => $existingJ['id'],
                                'pid' => $pilihanId ?: null,
                                'ib' => $isBenar,
                                'nil' => $isBenar ? $bobot : 0
                            ]);
                        } else {
                            $stmtJ = $this->db->prepare("
                                INSERT INTO jawaban_siswa (siswa_id, quiz_id, soal_id, pilihan_id, is_benar, nilai) 
                                VALUES (:sid, :qid, :soal_id, :pid, :ib, :nil)
                            ");
                            $stmtJ->execute([
                                'sid' => $siswa['id'],
                                'qid' => $quizId,
                                'soal_id' => $soalId,
                                'pid' => $pilihanId ?: null,
                                'ib' => $isBenar,
                                'nil' => $isBenar ? $bobot : 0
                            ]);
                        }
                    }
                }

                if ($totalBobot <= 0) {
                    $totalBobot = count($allSoal) * 10;
                }
                $nilaiAkhir = ($totalBobot > 0) ? round(($skorDapat / $totalBobot) * 100, 2) : 0;
                $statusLulus = ($nilaiAkhir >= 70) ? 'lulus' : 'tidak_lulus';

                // Save or Update Hasil Quiz and Attempt Count
                $stmtCheckH = $this->db->prepare("SELECT id, attempt_count FROM hasil_quiz WHERE siswa_id = :sid AND quiz_id = :qid LIMIT 1");
                $stmtCheckH->execute(['sid' => $siswa['id'], 'qid' => $quizId]);
                $existingH = $stmtCheckH->fetch();

                $newAttemptCount = 1;
                if ($existingH) {
                    $newAttemptCount = max(1, intval($existingH['attempt_count'] ?? 0) + 1);
                    $stmtH = $this->db->prepare("
                        UPDATE hasil_quiz 
                        SET total_nilai = :tn, status_lulus = :sl, attempt_count = :att, finished_at = NOW() 
                        WHERE id = :hid
                    ");
                    $stmtH->execute([
                        'hid' => $existingH['id'],
                        'tn' => $nilaiAkhir,
                        'sl' => $statusLulus,
                        'att' => $newAttemptCount
                    ]);
                } else {
                    $stmtH = $this->db->prepare("
                        INSERT INTO hasil_quiz (siswa_id, quiz_id, total_nilai, status_lulus, attempt_count, finished_at) 
                        VALUES (:sid, :qid, :tn, :sl, 1, NOW())
                    ");
                    $stmtH->execute([
                        'sid' => $siswa['id'],
                        'qid' => $quizId,
                        'tn' => $nilaiAkhir,
                        'sl' => $statusLulus
                    ]);
                }

                // Insert into hasil_quiz_history for attempt tracking
                try {
                    $stmtHistIns = $this->db->prepare("
                        INSERT INTO hasil_quiz_history (siswa_id, quiz_id, attempt_number, total_nilai, status_lulus, created_at)
                        VALUES (:sid, :qid, :att, :tn, :sl, NOW())
                    ");
                    $stmtHistIns->execute([
                        'sid' => $siswa['id'],
                        'qid' => $quizId,
                        'att' => $newAttemptCount,
                        'tn' => $nilaiAkhir,
                        'sl' => $statusLulus
                    ]);
                } catch (\Throwable $eH) {}

                $this->jsonResponse(true, 'Quiz berhasil dikirim!', [
                    'total_nilai' => $nilaiAkhir,
                    'status_lulus' => $statusLulus,
                    'attempt_count' => $newAttemptCount
                ]);
                break;

            case 'quiz_review':
                $quizId = intval($_GET['quiz_id'] ?? $_POST['quiz_id'] ?? 0);

                if ($quizId <= 0) {
                    $this->jsonResponse(false, 'Quiz ID tidak valid', null, 400);
                }

                $stmtQ = $this->db->prepare("
                    SELECT q.*, mp.nama_mapel, g.nama_lengkap as nama_guru, k.nama_kelas 
                    FROM quiz q
                    LEFT JOIN mata_pelajaran mp ON q.mapel_id = mp.id
                    LEFT JOIN guru g ON q.guru_id = g.id
                    LEFT JOIN kelas k ON q.kelas_id = k.id
                    WHERE q.id = :qid LIMIT 1
                ");
                $stmtQ->execute(['qid' => $quizId]);
                $quizInfo = $stmtQ->fetch();

                if (!$quizInfo) {
                    $this->jsonResponse(false, 'Data quiz tidak ditemukan', null, 404);
                }

                $stmtH = $this->db->prepare("
                    SELECT * FROM hasil_quiz 
                    WHERE quiz_id = :qid AND siswa_id = :sid 
                    ORDER BY id DESC LIMIT 1
                ");
                $stmtH->execute(['qid' => $quizId, 'sid' => $siswa['id']]);
                $hasilQuiz = $stmtH->fetch();

                $stmtSoal = $this->db->prepare("SELECT * FROM soal WHERE quiz_id = :qid ORDER BY id ASC");
                $stmtSoal->execute(['qid' => $quizId]);
                $soalList = $stmtSoal->fetchAll();

                $totalBenar = 0;
                $totalSalah = 0;

                foreach ($soalList as &$s) {
                    $stmtP = $this->db->prepare("SELECT id, soal_id, teks_pilihan, is_benar FROM pilihan_jawaban WHERE soal_id = :sid ORDER BY id ASC");
                    $stmtP->execute(['sid' => $s['id']]);
                    $s['pilihan'] = $stmtP->fetchAll();

                    $img = !empty($s['file_gambar']) ? $s['file_gambar'] : (!empty($s['gambar']) ? $s['gambar'] : null);
                    if (!empty($img)) {
                        $s['file_gambar'] = $img;
                        if (!str_starts_with($img, 'http://') && !str_starts_with($img, 'https://')) {
                            $cleanImg = ltrim($img, '/');
                            if (!str_starts_with($cleanImg, 'assets/uploads/') && !str_starts_with($cleanImg, 'uploads/')) {
                                $cleanImg = 'assets/uploads/soal/' . $cleanImg;
                            } else if (str_starts_with($cleanImg, 'uploads/')) {
                                $cleanImg = 'assets/' . $cleanImg;
                            }
                            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                            $s['file_gambar_url'] = "{$scheme}://{$host}/" . $cleanImg;
                        } else {
                            $s['file_gambar_url'] = $img;
                        }
                    } else {
                        $s['file_gambar_url'] = null;
                    }

                    $stmtJ = $this->db->prepare("
                        SELECT * FROM jawaban_siswa 
                        WHERE quiz_id = :qid AND siswa_id = :sid AND soal_id = :soal_id 
                        LIMIT 1
                    ");
                    $stmtJ->execute(['qid' => $quizId, 'sid' => $siswa['id'], 'soal_id' => $s['id']]);
                    $jSiswa = $stmtJ->fetch();

                    $s['jawaban_siswa'] = $jSiswa ? [
                        'pilihan_id' => $jSiswa['pilihan_id'] ? intval($jSiswa['pilihan_id']) : null,
                        'is_benar' => intval($jSiswa['is_benar']),
                        'nilai' => floatval($jSiswa['nilai']),
                    ] : null;

                    if ($jSiswa && intval($jSiswa['is_benar']) === 1) {
                        $totalBenar++;
                    } else {
                        $totalSalah++;
                    }
                }

                $this->jsonResponse(true, 'Detail Hasil Kuis & Pembahasan', [
                    'quiz' => $quizInfo,
                    'hasil' => $hasilQuiz ?: [
                        'total_nilai' => 0,
                        'status_lulus' => 'tidak_lulus',
                        'finished_at' => null
                    ],
                    'summary' => [
                        'total_soal' => count($soalList),
                        'total_benar' => $totalBenar,
                        'total_salah' => $totalSalah,
                    ],
                    'soal' => $soalList
                ]);
                break;

            case 'absensi':
            case 'history_absensi':
                try {
                    $stmtAbs = $this->db->prepare("
                        SELECT a.*, j.hari, j.jam_mulai, j.jam_selesai, mp.nama_mapel, g.nama_lengkap as nama_guru
                        FROM absensi a 
                        LEFT JOIN jadwal j ON a.jadwal_id = j.id 
                        LEFT JOIN mata_pelajaran mp ON j.mapel_id = mp.id 
                        LEFT JOIN guru g ON a.guru_id = g.id
                        WHERE a.siswa_id = :sid 
                        ORDER BY a.tanggal DESC, a.id DESC 
                        LIMIT 100
                    ");
                    $stmtAbs->execute(['sid' => $siswa['id']]);
                    $history = $stmtAbs->fetchAll();

                    $totalHadir = 0;
                    $tepatWaktu = 0;
                    $terlambat = 0;
                    $sudahPulang = 0;
                    $izinSakit = 0;

                    foreach ($history as $h) {
                        $st = strtolower($h['status'] ?? '');
                        if (!empty($h['waktu_pulang'])) {
                            $sudahPulang++;
                        }
                        if ($st === 'sakit' || $st === 'izin' || $st === 'alpha' || $st === 'alpa') {
                            $izinSakit++;
                        } elseif (strpos($st, 'telat') !== false || strpos($st, 'terlambat') !== false) {
                            $terlambat++;
                            $totalHadir++;
                        } else {
                            $tepatWaktu++;
                            $totalHadir++;
                        }
                    }

                    $this->jsonResponse(true, 'Laporan & History Presensi Siswa', [
                        'stats' => [
                            'total_hadir' => $totalHadir,
                            'tepat_waktu' => $tepatWaktu,
                            'terlambat' => $terlambat,
                            'sudah_pulang' => $sudahPulang,
                            'izin_sakit_alpha' => $izinSakit,
                        ],
                        'history' => $history
                    ]);
                } catch (\Throwable $eAbs) {
                    $this->jsonResponse(false, 'Gagal memuat riwayat absensi: ' . $eAbs->getMessage(), null, 500);
                }
                break;

            case 'checkin_absensi':
                $input = $this->getPostInput();
                $jadwalId = intval($input['jadwal_id'] ?? 0);
                $status = $input['status'] ?? 'Hadir';

                if ($jadwalId <= 0) {
                    $this->jsonResponse(false, 'Jadwal presensi tidak valid', null, 400);
                }

                $today = date('Y-m-d');
                $stmtIns = $this->db->prepare("
                    INSERT INTO absensi (jadwal_id, siswa_id, tanggal, status, created_at) 
                    VALUES (:jid, :sid, :tgl, :st, NOW())
                    ON DUPLICATE KEY UPDATE status = :st
                ");
                $stmtIns->execute([
                    'jid' => $jadwalId,
                    'sid' => $siswa['id'],
                    'tgl' => $today,
                    'st' => $status
                ]);

                $this->jsonResponse(true, 'Presensi berhasil dicatat!');
                break;

            case 'join_mapel':
            case 'enroll_mapel':
                require_once ROOT_PATH . 'models/AcademicModel.php';
                $academicModel = new AcademicModel();
                $input = $this->getPostInput();
                $keyInput = trim($input['key_mapel'] ?? $input['enrollment_key'] ?? $_POST['key_mapel'] ?? $_POST['enrollment_key'] ?? '');

                if (empty($keyInput)) {
                    $this->jsonResponse(false, 'Kode Key Mapel wajib diisi!', null, 400);
                }

                $res = $academicModel->enrollSiswaByMapelKey($siswa['id'], $keyInput);
                if ($res['status'] === true) {
                    $this->jsonResponse(true, $res['message'], $res['enrollment'] ?? null);
                } else {
                    $this->jsonResponse(false, $res['message'], null, 400);
                }
                break;

            case 'nilai':
            case 'rapor':
                require_once ROOT_PATH . 'models/NilaiModel.php';
                require_once ROOT_PATH . 'models/AcademicModel.php';

                $nilaiModel = new NilaiModel();
                $academicModel = new AcademicModel();

                $siswaId = intval($siswa['id']);

                // Auto-sync real-time scores for all enrolled/class mapels for this student
                try {
                    $enrolledList = $academicModel->getSiswaEnrolledMapels($siswaId);
                    $mapelIdsToSync = [];
                    if (!empty($enrolledList)) {
                        foreach ($enrolledList as $em) {
                            if (!empty($em['mapel_id'])) {
                                $mapelIdsToSync[] = (int)$em['mapel_id'];
                            }
                        }
                    }

                    if (empty($mapelIdsToSync)) {
                        $stmtMapelClass = $this->db->prepare("SELECT id FROM mata_pelajaran WHERE kelas_id = ? OR kelas_id IS NULL OR kelas_id = 0");
                        $stmtMapelClass->execute([$siswa['kelas_id'] ?? 0]);
                        $mapelIdsToSync = $stmtMapelClass->fetchAll(PDO::FETCH_COLUMN);

                        if (empty($mapelIdsToSync)) {
                            $stmtAllMapels = $this->db->query("SELECT id FROM mata_pelajaran");
                            $mapelIdsToSync = $stmtAllMapels->fetchAll(PDO::FETCH_COLUMN);
                        }
                    }

                    foreach ($mapelIdsToSync as $mId) {
                        $nilaiModel->syncSiswaMapelNilai($siswaId, (int)$mId);
                    }
                } catch (\Throwable $eSync) {}

                $nilaiList = $nilaiModel->getNilaiBySiswa($siswaId);

                // Format & enrich response data with predikat & ketuntasan
                $formattedNilai = [];
                foreach ($nilaiList as $n) {
                    $kkm = intval($n['kkm'] ?? 75);
                    $nilaiAkhir = floatval($n['nilai_akhir'] ?? 0);
                    $predikatInfo = NilaiModel::getPredikat($nilaiAkhir);
                    $isTuntas = ($nilaiAkhir >= $kkm);

                    $formattedNilai[] = [
                        'id' => intval($n['id']),
                        'siswa_id' => intval($n['siswa_id']),
                        'mapel_id' => intval($n['mapel_id']),
                        'nama_mapel' => $n['nama_mapel'] ?? '',
                        'kode_mapel' => $n['kode_mapel'] ?? ('MP' . $n['mapel_id']),
                        'kkm' => $kkm,
                        'nilai_tugas' => floatval($n['nilai_tugas'] ?? 0),
                        'nilai_quiz' => floatval($n['nilai_quiz'] ?? 0),
                        'nilai_uts' => floatval($n['nilai_uts'] ?? 0),
                        'nilai_uas' => floatval($n['nilai_uas'] ?? 0),
                        'nilai_akhir' => $nilaiAkhir,
                        'predikat' => $predikatInfo['grade'],
                        'predikat_label' => $predikatInfo['label'],
                        'is_tuntas' => $isTuntas,
                        'status_ketuntasan' => $isTuntas ? 'TUNTAS' : 'BELUM TUNTAS'
                    ];
                }

                $this->jsonResponse(true, 'Rekap Nilai & E-Rapor Digital Siswa', $formattedNilai);
                break;

            case 'kartu':
                $this->jsonResponse(true, 'Kartu Pelajar Digital Siswa', [
                    'nis' => $siswa['nis'] ?? '-',
                    'nama_lengkap' => $siswa['nama_lengkap'] ?? '-',
                    'nama_kelas' => $siswa['nama_kelas'] ?? '-',
                    'nama_jurusan' => $siswa['nama_jurusan'] ?? '-',
                    'foto' => $siswa['foto'] ?? null,
                    'qr_code' => 'SISWA-' . ($siswa['nis'] ?? $siswa['id'])
                ]);
                break;

            case 'available_mapel':
            case 'key_mapel':
            case 'mapel_keys':
            case 'gabung_kelas_list':
                try {
                    require_once ROOT_PATH . 'models/AcademicModel.php';
                    $academicModel = new AcademicModel();
                    $mapelKeys = $academicModel->getMapelEnrollmentKeys();
                    $enrolledList = $academicModel->getSiswaEnrolledMapels($siswa['id']);

                    $enrolledMapelGuruKeys = [];
                    foreach ($enrolledList as $em) {
                        $eKId = !empty($em['kelas_id']) ? (int)$em['kelas_id'] : 0;
                        $enrolledMapelGuruKeys[$em['mapel_id'] . '_' . $em['guru_id'] . '_' . $eKId] = true;
                        if ($eKId === 0) {
                            $enrolledMapelGuruKeys[$em['mapel_id'] . '_' . $em['guru_id'] . '_global'] = true;
                        }
                    }

                    $search = strtolower(trim($_GET['search'] ?? $_POST['search'] ?? ''));
                    $list = [];

                    foreach ($mapelKeys as $mk) {
                        $mkKId = !empty($mk['kelas_id']) ? (int)$mk['kelas_id'] : 0;
                        $isEnrolled = isset($enrolledMapelGuruKeys[$mk['mapel_id'] . '_' . $mk['guru_id'] . '_' . $mkKId])
                                   || ($mkKId > 0 && isset($enrolledMapelGuruKeys[$mk['mapel_id'] . '_' . $mk['guru_id'] . '_0']))
                                   || isset($enrolledMapelGuruKeys[$mk['mapel_id'] . '_' . $mk['guru_id'] . '_global']);

                        $namaMapel = $mk['nama_mapel'] ?? '';
                        $namaGuru = $mk['nama_guru'] ?? '';
                        $namaKelas = $mk['nama_kelas'] ?? 'Semua Rombel';

                        if (!empty($search)) {
                            $kw = strtolower($namaMapel . ' ' . $namaGuru . ' ' . $namaKelas);
                            if (strpos($kw, $search) === false) {
                                continue;
                            }
                        }

                        $list[] = [
                            'key_id' => intval($mk['id']),
                            'mapel_id' => intval($mk['mapel_id']),
                            'guru_id' => intval($mk['guru_id']),
                            'kelas_id' => $mk['kelas_id'] ? intval($mk['kelas_id']) : null,
                            'nama_mapel' => $namaMapel,
                            'kode_mapel' => $mk['kode_mapel'] ?? ('MP' . $mk['mapel_id']),
                            'nama_guru' => $namaGuru,
                            'nama_kelas' => $namaKelas,
                            'tingkat' => intval($mk['tingkat'] ?? 0),
                            'nama_jurusan' => $mk['nama_jurusan'] ?? '',
                            'enrollment_key' => $mk['enrollment_key'] ?? '',
                            'is_enrolled' => $isEnrolled ? 1 : 0
                        ];
                    }

                    $this->jsonResponse(true, 'Daftar Key Mapel & Status Pendaftaran Siswa', $list);
                } catch (\Throwable $eAm) {
                    $this->jsonResponse(false, 'Gagal memuat data key mapel: ' . $eAm->getMessage(), null, 500);
                }
                break;

            case 'gabung_kelas':
                $input = $this->getPostInput();
                $action = $input['action'] ?? 'join_kelas';
                $keyMapel = trim($input['key_mapel'] ?? $input['passcode_key'] ?? '');
                $kodeKelas = trim($input['kode_kelas'] ?? '');

                if ($action === 'enroll_mapel' || (!empty($keyMapel) && empty($kodeKelas))) {
                    if (empty($keyMapel)) {
                        $this->jsonResponse(false, 'Kode Akses / Key Mapel tidak boleh kosong!', null, 400);
                    }
                    require_once ROOT_PATH . 'models/AcademicModel.php';
                    $academicModel = new AcademicModel();
                    $res = $academicModel->enrollSiswaByMapelKey($siswa['id'], $keyMapel);

                    if ($res['status'] === true) {
                        $this->jsonResponse(true, $res['message'], $res['enrollment'] ?? null);
                    } else {
                        $this->jsonResponse(false, $res['message'], null, 400);
                    }
                } else {
                    require_once ROOT_PATH . 'models/AcademicModel.php';
                    $academicModel = new AcademicModel();

                    $kelasIdInput = intval($input['kelas_id'] ?? $_POST['kelas_id'] ?? 0);
                    $kodeKelas = trim($input['kode_kelas'] ?? $input['kode_rombel'] ?? $_POST['kode_kelas'] ?? '');

                    if (empty($kodeKelas) && $kelasIdInput <= 0) {
                        $this->jsonResponse(false, 'Kode Akses Rombel / Pilihan Kelas wajib diisi!', null, 400);
                    }

                    if ($kelasIdInput > 0) {
                        $res = $academicModel->joinKelasById($siswa['id'], $kelasIdInput);
                    } else {
                        $res = $academicModel->joinKelasByCode($siswa['id'], $kodeKelas);
                    }

                    if ($res['status']) {
                        $this->jsonResponse(true, $res['message'], $res['kelas'] ?? null);
                    } else {
                        $this->jsonResponse(false, $res['message'], null, 400);
                    }
                }
                break;

            case 'learning_path':
            case 'learningpath':
            case 'alur_pembelajaran':
                $this->siswa('learning_path');
                break;

            case 'review_quiz':
                $quizId = intval($_GET['quiz_id'] ?? 0);
                try {
                    $stmtJ = $this->db->prepare("
                        SELECT js.*, s.pertanyaan, s.bobot, pj.teks_pilihan as jawaban_siswa 
                        FROM jawaban_siswa js 
                        JOIN soal s ON js.soal_id = s.id 
                        LEFT JOIN pilihan_jawaban pj ON js.pilihan_id = pj.id 
                        WHERE js.quiz_id = :qid AND js.siswa_id = :sid
                    ");
                    $stmtJ->execute(['qid' => $quizId, 'sid' => $siswa['id']]);
                    $review = $stmtJ->fetchAll();
                    $this->jsonResponse(true, 'Hasil Review Jawaban Quiz', $review);
                } catch (\Throwable $eRv) {
                    $this->jsonResponse(true, 'Hasil Review Jawaban Quiz', []);
                }
                break;

            case 'sertifikat':
                $this->jsonResponse(true, 'Sertifikat Kelulusan & Capaian Belajar', [
                    'nama_siswa' => $siswa['nama_lengkap'] ?? 'Siswa',
                    'nis' => $siswa['nis'] ?? '-',
                    'predikat' => 'Sangat Baik (A)',
                    'tgl_terbit' => date('Y-m-d'),
                    'no_sertifikat' => 'CERT-MH-' . date('Y') . '-' . ($siswa['id'] ?? '1')
                ]);
                break;

            default:
                $this->jsonResponse(false, 'Endpoint siswa tidak ditemukan', null, 404);
        }
    }

    public function guru($endpoint = 'dashboard') {
        $endpoint = strtolower(explode('?', $endpoint)[0]);
        $input = $this->getPostInput();
        $userId = intval($_GET['user_id'] ?? $_POST['user_id'] ?? $input['user_id'] ?? $_GET['guru_id'] ?? $_POST['guru_id'] ?? $input['guru_id'] ?? 0);
        if ($userId === 0) {
            $rawQuery = $_SERVER['QUERY_STRING'] ?? '';
            if (preg_match('/user_id=(\d+)/i', $rawQuery, $mUid)) {
                $userId = intval($mUid[1]);
            } elseif (preg_match('/guru_id=(\d+)/i', $rawQuery, $mGid)) {
                $userId = intval($mGid[1]);
            }
        }

        require_once ROOT_PATH . 'models/GuruModel.php';
        require_once ROOT_PATH . 'models/LearningModel.php';
        require_once ROOT_PATH . 'models/ExamModel.php';
        require_once ROOT_PATH . 'models/AcademicModel.php';
        require_once ROOT_PATH . 'models/CommunicationModel.php';

        $guruModel = new GuruModel();
        $learningModel = new LearningModel();
        $examModel = new ExamModel();
        $academicModel = new AcademicModel();
        $commModel = new CommunicationModel();

        // Get logged in user details if available
        $userObj = null;
        if ($userId > 0) {
            $stmtU = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmtU->execute([$userId]);
            $userObj = $stmtU->fetch(PDO::FETCH_ASSOC);
        }

        $guru = null;
        if ($userId > 0) {
            $guru = $guruModel->ensureGuruProfile($userId, $userObj['full_name'] ?? '');
        }

        if (!$guru) {
            $stmtG = $this->db->query("SELECT * FROM guru ORDER BY id ASC LIMIT 1");
            $guru = $stmtG->fetch(PDO::FETCH_ASSOC);
        }

        if (!$guru) {
            $this->jsonResponse(false, 'Data guru tidak ditemukan', null, 404);
        }

        $guruId = (int)$guru['id'];

        switch ($endpoint) {
            case 'dashboard':
                // Data models matching Web GuruController dashboard
                $materiList = $learningModel->getMateri(null, $guruId);
                $tugasList = $learningModel->getTugas(null, $guruId);
                $quizList = $examModel->getQuizList(null, $guruId);
                $myKeys = $academicModel->getMapelEnrollmentKeys($guruId);
                $enrolledStudents = $academicModel->getEnrolledStudentsForGuru($guruId);

                $totalMateri = count($materiList);
                $totalTugas = count($tugasList);
                $totalQuiz = count($quizList);
                $totalKeys = count($myKeys);
                $totalSiswaTerdaftar = count($enrolledStudents);

                // Schedule Today (Strictly matching AcademicModel getJadwal and current day)
                $jadwalList = $academicModel->getJadwal(null, $guruId);
                $todayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][date('w')];
                $jadwalHariIni = [];
                foreach ($jadwalList as $j) {
                    if (strcasecmp(trim($j['hari'] ?? ''), $todayName) === 0) {
                        $jadwalHariIni[] = $j;
                    }
                }

                // Check Guru Today Attendance
                $todayDate = date('Y-m-d');
                $stmtAbsG = $this->db->prepare("SELECT * FROM absensi_guru WHERE guru_id = ? AND tanggal = ? LIMIT 1");
                $stmtAbsG->execute([$guruId, $todayDate]);
                $absGuru = $stmtAbsG->fetch(PDO::FETCH_ASSOC) ?: [];

                $hasClockedIn = !empty($absGuru['waktu_masuk']) || !empty($absGuru['waktu_hadir']);
                $hasClockedOut = !empty($absGuru['waktu_pulang']);
                $absGuruStatus = $absGuru['status'] ?? 'Belum Absen';

                // Active Academic Year & Semester matching Admin setting
                $activeTa = $academicModel->getActiveTahunAjaran();
                $taTahun = trim($activeTa['tahun_ajaran'] ?? ($activeTa['tahun'] ?? '2025/2026'));
                $taSem = trim($activeTa['semester'] ?? 'Ganjil');
                $tahunAjaranStr = "T.A. $taTahun — Semester $taSem";

                // Announcements (Guru + All Target)
                $pengumumanList = $commModel->getPengumuman('guru');
                $pengumumanGuru = [];
                foreach ($pengumumanList as $p) {
                    $bUrl = null;
                    if (!empty($p['banner'])) {
                        $bUrl = (strpos($p['banner'], 'http') === 0) ? $p['banner'] : BASE_URL . ltrim($p['banner'], '/');
                    }
                    $p['banner_url'] = $bUrl;
                    $p['banner'] = $bUrl ?: $p['banner'];
                    $pengumumanGuru[] = $p;
                }

                // Recent materi & tugas for preview cards
                $materiTerbaru = array_slice($materiList, 0, 5);
                $tugasTerbaru = array_slice($tugasList, 0, 5);

                $this->jsonResponse(true, 'Dashboard Guru Overview', [
                    'guru' => $guru,
                    'user' => $userObj ?: ['full_name' => $guru['nama_lengkap']],
                    'tahun_ajaran' => $tahunAjaranStr,
                    'active_ta' => $activeTa,
                    'stats' => [
                        'materi' => $totalMateri,
                        'tugas' => $totalTugas,
                        'quiz' => $totalQuiz,
                        'keys' => $totalKeys,
                        'siswa_terdaftar' => $totalSiswaTerdaftar,
                    ],
                    'pengumuman' => $pengumumanGuru,
                    'jadwal_hari_ini' => $jadwalHariIni,
                    'jadwal_list' => $jadwalList,
                    'materi_terbaru' => $materiTerbaru,
                    'tugas_terbaru' => $tugasTerbaru,
                    'absensi_today' => [
                        'has_clocked_in' => $hasClockedIn,
                        'has_clocked_out' => $hasClockedOut,
                        'status' => $absGuruStatus,
                        'waktu_masuk' => $absGuru['waktu_masuk'] ?? $absGuru['waktu_hadir'] ?? null,
                        'waktu_pulang' => $absGuru['waktu_pulang'] ?? null,
                    ]
                ]);
                break;

            case 'jadwal':
                $stmtJ = $this->db->prepare("
                    SELECT j.*, m.nama_mapel, k.nama_kelas 
                    FROM jadwal j 
                    JOIN mata_pelajaran m ON j.mapel_id = m.id 
                    JOIN kelas k ON j.kelas_id = k.id 
                    WHERE j.guru_id = :gid 
                    ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), j.jam_mulai ASC
                ");
                $stmtJ->execute(['gid' => $guru['id']]);
                $jadwal = $stmtJ->fetchAll();

                $this->jsonResponse(true, 'Jadwal Mengajar Guru', $jadwal);
                break;

            case 'materi':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $input = $this->getPostInput();
                    $judul = trim($input['judul'] ?? '');
                    $deskripsi = trim($input['deskripsi'] ?? '');
                    $mapelId = intval($input['mapel_id'] ?? 0);
                    $jenisFile = $input['jenis_file'] ?? 'pdf';
                    $youtubeUrl = trim($input['youtube_url'] ?? '');

                    $kelasIds = [];
                    if (isset($input['kelas_ids']) && is_array($input['kelas_ids'])) {
                        $kelasIds = array_map('intval', $input['kelas_ids']);
                    } elseif (!empty($input['kelas_id'])) {
                        if (is_array($input['kelas_id'])) {
                            $kelasIds = array_map('intval', $input['kelas_id']);
                        } else {
                            $kelasIds = array_map('intval', explode(',', (string)$input['kelas_id']));
                        }
                    }
                    $kelasIds = array_values(array_filter($kelasIds, function($id) { return $id > 0; }));

                    if (empty($judul) || $mapelId <= 0 || empty($kelasIds)) {
                        $this->jsonResponse(false, 'Judul, Mapel, dan minimal 1 Kelas wajib diisi', null, 400);
                    }

                    $primaryKelasId = $kelasIds[0] ?? 0;
                    $kelasIdsStr = implode(',', $kelasIds);

                    $stmtIns = $this->db->prepare("
                        INSERT INTO materi (guru_id, mapel_id, kelas_id, kelas_ids, judul, deskripsi, jenis_file, youtube_url, created_at) 
                        VALUES (:gid, :mpid, :kid, :kids, :jdl, :desk, :jf, :yt, NOW())
                    ");
                    $stmtIns->execute([
                        'gid' => $guru['id'],
                        'mpid' => $mapelId,
                        'kid' => $primaryKelasId,
                        'kids' => $kelasIdsStr,
                        'jdl' => $judul,
                        'desk' => $deskripsi,
                        'jf' => $jenisFile,
                        'yt' => $youtubeUrl
                    ]);

                    $this->jsonResponse(true, 'Materi berhasil ditambahkan untuk kelas yang dipilih!');
                }

                $stmtM = $this->db->prepare("
                    SELECT m.*, mp.nama_mapel, k.nama_kelas 
                    FROM materi m 
                    LEFT JOIN mata_pelajaran mp ON m.mapel_id = mp.id 
                    LEFT JOIN kelas k ON m.kelas_id = k.id 
                    WHERE m.guru_id = :gid 
                    ORDER BY m.created_at DESC
                ");
                $stmtM->execute(['gid' => $guru['id']]);
                $materi = $stmtM->fetchAll();

                $kelasList = $this->db->query("SELECT id, nama_kelas FROM kelas")->fetchAll(PDO::FETCH_KEY_PAIR);
                foreach ($materi as &$item) {
                    $targetIds = !empty($item['kelas_ids']) ? array_map('intval', explode(',', $item['kelas_ids'])) : [(int)$item['kelas_id']];
                    $names = [];
                    foreach ($targetIds as $tid) {
                        if (isset($kelasList[$tid])) {
                            $names[] = $kelasList[$tid];
                        }
                    }
                    $item['nama_kelas'] = !empty($names) ? implode(', ', $names) : ($item['nama_kelas'] ?? 'Semua Kelas');
                }
                unset($item);

                $this->jsonResponse(true, 'Kelola Materi Pembelajaran', $materi);
                break;

            case 'tugas':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $input = $this->getPostInput();
                    $action = $input['action'] ?? 'create';

                    if ($action === 'create') {
                        $judul = trim($input['judul'] ?? '');
                        $deskripsi = trim($input['deskripsi'] ?? '');
                        $mapelId = intval($input['mapel_id'] ?? 0);
                        $filePath = trim($input['file_path'] ?? '');
                        
                        $kelasIds = [];
                        if (isset($input['kelas_ids']) && is_array($input['kelas_ids'])) {
                            $kelasIds = array_map('intval', $input['kelas_ids']);
                        } elseif (!empty($input['kelas_id'])) {
                            if (is_array($input['kelas_id'])) {
                                $kelasIds = array_map('intval', $input['kelas_id']);
                            } else {
                                $kelasIds = array_map('intval', explode(',', (string)$input['kelas_id']));
                            }
                        }
                        $kelasIds = array_values(array_filter($kelasIds, function($id) { return $id > 0; }));

                        $deadline = $input['deadline'] ?? date('Y-m-d H:i:s', strtotime('+7 days'));

                        if (empty($judul) || $mapelId <= 0 || empty($kelasIds)) {
                            $this->jsonResponse(false, 'Judul, Mapel, dan minimal 1 Kelas wajib diisi', null, 400);
                        }

                        $primaryKelasId = $kelasIds[0] ?? 0;
                        $kelasIdsStr = implode(',', $kelasIds);

                        $stmtIns = $this->db->prepare("
                            INSERT INTO tugas (guru_id, mapel_id, kelas_id, kelas_ids, judul, deskripsi, file_path, deadline, created_at) 
                            VALUES (:gid, :mpid, :kid, :kids, :jdl, :desk, :fp, :dl, NOW())
                        ");
                        $stmtIns->execute([
                            'gid' => $guru['id'],
                            'mpid' => $mapelId,
                            'kid' => $primaryKelasId,
                            'kids' => $kelasIdsStr,
                            'jdl' => $judul,
                            'desk' => $deskripsi,
                            'fp' => $filePath,
                            'dl' => $deadline
                        ]);

                        $this->jsonResponse(true, 'Tugas berhasil dipublikasikan!');
                    } else if ($action === 'grade') {
                        $submissionId = intval($input['submission_id'] ?? 0);
                        $nilai = floatval($input['nilai'] ?? 0);
                        $komentar = trim($input['komentar_guru'] ?? '');

                        $stmtG = $this->db->prepare("
                            UPDATE pengumpulan_tugas 
                            SET nilai = :nil, komentar_guru = :kom, graded_at = NOW() 
                            WHERE id = :sub_id
                        ");
                        $stmtG->execute(['nil' => $nilai, 'kom' => $komentar, 'sub_id' => $submissionId]);

                        $this->jsonResponse(true, 'Nilai tugas berhasil disimpan!');
                    }
                }

                $stmtT = $this->db->prepare("
                    SELECT t.*, mp.nama_mapel, k.nama_kelas,
                           (SELECT COUNT(*) FROM pengumpulan_tugas pt WHERE pt.tugas_id = t.id) as total_pengumpulan 
                    FROM tugas t 
                    LEFT JOIN mata_pelajaran mp ON t.mapel_id = mp.id 
                    LEFT JOIN kelas k ON t.kelas_id = k.id 
                    WHERE (t.guru_id = :gid OR t.guru_id = :uid) 
                    ORDER BY t.created_at DESC
                ");
                $stmtT->execute(['gid' => $guru['id'], 'uid' => $guru['user_id']]);
                $tugas = $stmtT->fetchAll();

                if (empty($tugas)) {
                    $tugas = [];
                }

                $academicModel = new AcademicModel();
                $mapelListRaw = $academicModel->getMapelByGuru($guru['id']);
                if (empty($mapelListRaw)) {
                    $mapelListRaw = $academicModel->getMapel();
                }

                $kelasListRaw = $academicModel->getKelasByGuru($guru['id']);
                if (empty($kelasListRaw)) {
                    $kelasListRaw = $academicModel->getKelas();
                }

                $allKelasMap = $this->db->query("SELECT id, nama_kelas FROM kelas")->fetchAll(PDO::FETCH_KEY_PAIR);

                foreach ($tugas as &$item) {
                    $targetIds = !empty($item['kelas_ids']) ? array_map('intval', explode(',', $item['kelas_ids'])) : [(int)$item['kelas_id']];
                    $names = [];
                    foreach ($targetIds as $tid) {
                        if (isset($allKelasMap[$tid])) {
                            $names[] = $allKelasMap[$tid];
                        }
                    }
                    $item['nama_kelas'] = !empty($names) ? implode(', ', $names) : ($item['nama_kelas'] ?? 'Semua Kelas');
                }
                unset($item);

                $this->jsonResponse(true, 'Kelola Tugas', [
                    'tugas' => $tugas,
                    'mapel_list' => $mapelListRaw,
                    'kelas_list' => $kelasListRaw
                ]);
                break;

            case 'submissions':
                $tugasId = intval($_GET['tugas_id'] ?? $_POST['tugas_id'] ?? 0);
                $stmtSub = $this->db->prepare("
                    SELECT pt.*, s.nama_lengkap as nama_siswa, s.nis, s.nisn, 
                           COALESCE(k.nama_kelas, 'Semua Kelas') as nama_kelas
                    FROM pengumpulan_tugas pt 
                    JOIN siswa s ON pt.siswa_id = s.id 
                    LEFT JOIN kelas k ON s.kelas_id = k.id
                    WHERE pt.tugas_id = :tid 
                    ORDER BY pt.submitted_at DESC
                ");
                $stmtSub->execute(['tid' => $tugasId]);
                $submissions = $stmtSub->fetchAll();

                $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $baseUrl = "{$scheme}://{$host}/";

                foreach ($submissions as &$sub) {
                    $fPath = trim($sub['file_path'] ?? '');
                    $catatan = trim($sub['catatan_siswa'] ?? '');

                    $extractedUrl = null;
                    if (preg_match('/(https?:\/\/[^\s]+)/i', $catatan, $m)) {
                        $extractedUrl = $m[1];
                    }

                    if (!empty($fPath)) {
                        if (str_starts_with($fPath, 'http://') || str_starts_with($fPath, 'https://')) {
                            $sub['file_url'] = $fPath;
                        } else {
                            $sub['file_url'] = $baseUrl . 'assets/uploads/tugas/' . ltrim($fPath, '/');
                        }
                    } else if (!empty($extractedUrl)) {
                        $sub['file_url'] = $extractedUrl;
                    } else {
                        $sub['file_url'] = null;
                    }
                }
                unset($sub);

                $this->jsonResponse(true, 'Daftar Pengumpulan Tugas', $submissions);
                break;

            case 'quiz':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $input = $this->getPostInput();
                    $judul = trim($input['judul'] ?? '');
                    $deskripsi = trim($input['deskripsi'] ?? '');
                    $mapelId = intval($input['mapel_id'] ?? 0);
                    $kelasId = intval($input['kelas_id'] ?? 0);
                    $durasi = intval($input['durasi_menit'] ?? 30);

                    if (empty($judul) || $mapelId <= 0 || $kelasId <= 0) {
                        $this->jsonResponse(false, 'Judul, Mapel, dan Kelas wajib diisi', null, 400);
                    }

                    $stmtIns = $this->db->prepare("
                        INSERT INTO quiz (guru_id, mapel_id, kelas_id, judul, deskripsi, durasi_menit, status, created_at) 
                        VALUES (:gid, :mpid, :kid, :jdl, :desk, :dur, 'published', NOW())
                    ");
                    $stmtIns->execute([
                        'gid' => $guru['id'],
                        'mpid' => $mapelId,
                        'kid' => $kelasId,
                        'jdl' => $judul,
                        'desk' => $deskripsi,
                        'dur' => $durasi
                    ]);

                    $this->jsonResponse(true, 'Quiz / Ujian berhasil dibuat!');
                }

                $stmtQ = $this->db->prepare("
                    SELECT q.*, mp.nama_mapel, k.nama_kelas,
                           (SELECT COUNT(*) FROM hasil_quiz hq WHERE hq.quiz_id = q.id) as total_peserta 
                    FROM quiz q 
                    JOIN mata_pelajaran mp ON q.mapel_id = mp.id 
                    JOIN kelas k ON q.kelas_id = k.id 
                    WHERE q.guru_id = :gid 
                    ORDER BY q.created_at DESC
                ");
                $stmtQ->execute(['gid' => $guru['id']]);
                $quizList = $stmtQ->fetchAll();

                $this->jsonResponse(true, 'Daftar Quiz / Ujian Guru', $quizList);
                break;

            case 'susulan_requests':
                require_once ROOT_PATH . 'models/ExamModel.php';
                $examModel = new ExamModel();
                $targetGId = !empty($guru['id']) ? (int)$guru['id'] : $userId;
                $susulanList = $examModel->getSusulanRequestsByGuru($targetGId);
                $this->jsonResponse(true, 'Daftar Permintaan Izin Susulan / Buka Suspend', $susulanList);
                break;

            case 'approve_susulan':
                require_once ROOT_PATH . 'models/ExamModel.php';
                $examModel = new ExamModel();
                $input = $this->getPostInput();
                $requestId = intval($input['request_id'] ?? 0);
                $quizId = intval($input['quiz_id'] ?? 0);
                $siswaId = intval($input['siswa_id'] ?? 0);
                $catatan = trim($input['catatan'] ?? 'Disetujui Guru via Mobile App');

                if ($requestId > 0) {
                    $examModel->approveSusulanById($requestId, $catatan);
                } else if ($quizId > 0 && $siswaId > 0) {
                    $examModel->approveSusulanRequest($quizId, $siswaId, $catatan);
                } else {
                    $this->jsonResponse(false, 'Request ID atau Quiz ID & Siswa ID wajib diisi', null, 400);
                }

                $this->jsonResponse(true, 'Permintaan Ujian Susulan / Buka Suspend berhasil DISETUJUI!');
                break;

            case 'reject_susulan':
                require_once ROOT_PATH . 'models/ExamModel.php';
                $examModel = new ExamModel();
                $input = $this->getPostInput();
                $requestId = intval($input['request_id'] ?? 0);
                $catatan = trim($input['catatan'] ?? 'Ditolak Guru via Mobile App');

                if ($requestId <= 0) {
                    $this->jsonResponse(false, 'Request ID tidak valid', null, 400);
                }

                $examModel->rejectSusulanById($requestId, $catatan);
                $this->jsonResponse(true, 'Permintaan Ujian Susulan / Buka Suspend DITOLAK.');
                break;

            case 'absensi':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $input = $this->getPostInput();
                    $jadwalId = intval($input['jadwal_id'] ?? $_POST['jadwal_id'] ?? 0);
                    $tanggal = trim($input['tanggal'] ?? $_POST['tanggal'] ?? date('Y-m-d'));
                    $records = $input['records'] ?? $_POST['records'] ?? [];
                    $keteranganMap = $input['keterangan'] ?? $_POST['keterangan'] ?? [];

                    if (empty($records)) {
                        $this->jsonResponse(false, 'Data absensi tidak boleh kosong', null, 400);
                    }

                    require_once ROOT_PATH . 'models/AbsensiModel.php';
                    $absensiModel = new AbsensiModel();

                    foreach ($records as $siswaId => $status) {
                        $ket = trim($keteranganMap[$siswaId] ?? $keteranganMap[strval($siswaId)] ?? '');
                        $absensiModel->recordAttendance($jadwalId, intval($siswaId), $tanggal, $status, $ket);
                    }

                    $this->jsonResponse(true, 'Presensi siswa terdaftar berhasil disimpan!');
                }

                $guruId = intval($guru['id'] ?? 0);
                $tanggal = trim($_GET['tanggal'] ?? $_POST['tanggal'] ?? date('Y-m-d'));

                require_once ROOT_PATH . 'models/AcademicModel.php';
                $academicModel = new AcademicModel();

                $userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
                $isAdmin = in_array($userRole, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);

                if ($isAdmin) {
                    $jadwalList = $academicModel->getJadwal();
                } else {
                    $jadwalList = $academicModel->getJadwal(null, $guruId);
                    if (empty($jadwalList)) {
                        $myKeys = $academicModel->getMapelEnrollmentKeys($guruId);
                        $jadwalList = [];
                        foreach ($myKeys as $mk) {
                            $jadwalList[] = [
                                'id' => $mk['id'],
                                'hari' => 'Hari KBM',
                                'nama_mapel' => $mk['nama_mapel'],
                                'nama_kelas' => $mk['nama_kelas'] ?? 'Semua Kelas',
                                'jam_mulai' => '07:30',
                                'jam_selesai' => '15:00'
                            ];
                        }
                        if (empty($jadwalList)) {
                            $jadwalList = [];
                        }
                    }
                }

                $reqJadwalId = intval($_GET['jadwal_id'] ?? $_POST['jadwal_id'] ?? 0);
                if ($reqJadwalId > 0) {
                    $selectedJadwalId = $reqJadwalId;
                } else if (!empty($jadwalList)) {
                    $selectedJadwalId = intval($jadwalList[0]['id'] ?? 1);
                } else {
                    $selectedJadwalId = 1;
                }

                require_once ROOT_PATH . 'models/AbsensiModel.php';
                $absensiModel = new AbsensiModel();
                $studentsRecap = $absensiModel->getRecap($selectedJadwalId, $tanggal) ?: [];

                // Attach kelas_name & mapel_name metadata for each student
                $stmtJadwalInfo = $this->db->prepare("
                    SELECT j.kelas_id, k.nama_kelas, j.mapel_id, mp.nama_mapel 
                    FROM jadwal j 
                    LEFT JOIN kelas k ON j.kelas_id = k.id 
                    LEFT JOIN mata_pelajaran mp ON j.mapel_id = mp.id 
                    WHERE j.id = ?
                ");
                $stmtJadwalInfo->execute([$selectedJadwalId]);
                $jInfo = $stmtJadwalInfo->fetch();

                foreach ($studentsRecap as &$s) {
                    $s['id'] = intval($s['siswa_id']);
                    $s['siswa_id'] = intval($s['siswa_id']);
                    if (empty($s['nama_kelas'])) {
                        $s['nama_kelas'] = $jInfo['nama_kelas'] ?? 'Tanpa Kelas';
                    }
                    if (empty($s['nama_mapel'])) {
                        $s['nama_mapel'] = $jInfo['nama_mapel'] ?? '';
                    }
                }

                // If getRecap returned empty (e.g. invalid schedule id), fall back to all students
                if (empty($studentsRecap)) {
                    $stmtFb = $this->db->prepare("
                        SELECT s.id as siswa_id, s.id, s.nama_lengkap, s.nis, s.nisn, s.kelas_id,
                               COALESCE(k.nama_kelas, 'Tanpa Kelas') as nama_kelas,
                               a.status, a.keterangan, a.created_at, a.waktu_hadir, a.waktu_masuk, a.waktu_pulang, a.qr_code
                        FROM siswa s
                        LEFT JOIN kelas k ON s.kelas_id = k.id
                        LEFT JOIN absensi a ON s.id = a.siswa_id AND a.tanggal = ?
                        ORDER BY k.nama_kelas ASC, s.nama_lengkap ASC
                    ");
                    $stmtFb->execute([$tanggal]);
                    $studentsRecap = $stmtFb->fetchAll(PDO::FETCH_ASSOC) ?: [];
                }

                $stmtK = $this->db->query("SELECT id, nama_kelas, kode_kelas FROM kelas ORDER BY nama_kelas ASC");
                $classList = $stmtK->fetchAll(PDO::FETCH_ASSOC) ?: [];

                $this->jsonResponse(true, 'Data Presensi Siswa Terdaftar', [
                    'jadwal_list' => $jadwalList,
                    'selected_jadwal_id' => $selectedJadwalId,
                    'tanggal' => $tanggal,
                    'classes' => $classList,
                    'students' => $studentsRecap
                ]);
                break;

            case 'recap_absensi':
            case 'rekap_absensi':
                require_once ROOT_PATH . 'models/AcademicModel.php';
                require_once ROOT_PATH . 'models/AbsensiModel.php';
                $academicModel = new AcademicModel();
                $absensiModel = new AbsensiModel();
                $guruId = intval($guru['id'] ?? 0);

                $input = $this->getPostInput();
                $kelasId = intval($_GET['kelas_id'] ?? $_POST['kelas_id'] ?? $input['kelas_id'] ?? 0);
                $mapelId = intval($_GET['mapel_id'] ?? $_POST['mapel_id'] ?? $input['mapel_id'] ?? 0);
                $bulan = sprintf('%02d', (int)($_GET['bulan'] ?? $_POST['bulan'] ?? $input['bulan'] ?? date('m')));
                $tahun = (int)($_GET['tahun'] ?? $_POST['tahun'] ?? $input['tahun'] ?? date('Y'));

                $myMapelList = $academicModel->getMapelByGuru($guruId);
                if (empty($myMapelList)) {
                    $myMapelList = $academicModel->getMapel();
                }

                $myKelasList = $academicModel->getKelasByGuru($guruId);
                if (empty($myKelasList)) {
                    $myKelasList = $academicModel->getKelas();
                }

                $recapData = $absensiModel->getMonthlyRecapForGuru($guruId, $bulan, $tahun, $mapelId, $kelasId);

                $this->jsonResponse(true, 'Rekap Bulanan Presensi Siswa', array_merge([
                    'mapel_list' => $myMapelList,
                    'kelas_list' => $myKelasList,
                    'selected_mapel_id' => $mapelId,
                    'selected_kelas_id' => $kelasId,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ], $recapData));
                break;

            case 'key_mapel':
            case 'kelas_virtual':
                require_once ROOT_PATH . 'models/AcademicModel.php';
                $academicModel = new AcademicModel();

                if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                    $input = $this->getPostInput();
                    $mapelId = intval($input['mapel_id'] ?? $_POST['mapel_id'] ?? 0);
                    $kelasId = !empty($input['kelas_id'] ?? $_POST['kelas_id'] ?? null) ? intval($input['kelas_id'] ?? $_POST['kelas_id']) : null;
                    $key = Security::sanitize(trim($input['enrollment_key'] ?? $input['key_mapel'] ?? $_POST['enrollment_key'] ?? $_POST['key_mapel'] ?? ''));

                    if ($mapelId <= 0) {
                        $this->jsonResponse(false, 'Mata pelajaran harus dipilih', null, 400);
                    }
                    if (empty($key)) {
                        $this->jsonResponse(false, 'Kode Key Mapel tidak boleh kosong', null, 400);
                    }

                    $guruId = intval($guru['id'] ?? 0);
                    $ok = $academicModel->setMapelEnrollmentKey($mapelId, $guruId, $key, $kelasId);

                    if ($ok) {
                        $this->jsonResponse(true, 'Kode Key Mapel pengampuan Anda berhasil diperbarui!');
                    } else {
                        $this->jsonResponse(false, 'Gagal memperbarui Key Mapel', null, 500);
                    }
                }

                $guruId = intval($guru['id'] ?? 0);
                $myKeys = $academicModel->getMapelEnrollmentKeys($guruId);
                $mapelList = $academicModel->getMapelByGuru($guruId);
                if (empty($mapelList)) {
                    $mapelList = $academicModel->getMapel();
                }
                $classList = $academicModel->getKelasByGuru($guruId);
                if (empty($classList)) {
                    $classList = $academicModel->getKelas();
                }

                $this->jsonResponse(true, 'Data Key Mapel Guru', [
                    'keys' => $myKeys,
                    'mapel_list' => $mapelList,
                    'classes' => $classList
                ]);
                break;

            case 'siswa_enrolled':
            case 'enrolled_students':
                require_once ROOT_PATH . 'models/AcademicModel.php';
                $academicModel = new AcademicModel();

                $mapelId = !empty($_GET['mapel_id'] ?? $_POST['mapel_id'] ?? null) ? intval($_GET['mapel_id'] ?? $_POST['mapel_id']) : null;
                $kelasId = !empty($_GET['kelas_id'] ?? $_POST['kelas_id'] ?? null) ? intval($_GET['kelas_id'] ?? $_POST['kelas_id']) : null;
                $search = !empty($_GET['search'] ?? $_POST['search'] ?? null) ? Security::sanitize($_GET['search'] ?? $_POST['search']) : null;

                $guruId = intval($guru['id'] ?? 0);
                $students = $academicModel->getEnrolledStudentsForGuru($guruId, $mapelId, $kelasId, null, $search);
                $myKeys = $academicModel->getMapelEnrollmentKeys($guruId);
                $myKelasList = $academicModel->getKelasByGuru($guruId);

                $this->jsonResponse(true, 'Data Siswa Terdaftar Mapel Guru', [
                    'total_enrolled' => count($students),
                    'keys' => $myKeys,
                    'classes' => $myKelasList,
                    'students' => $students
                ]);
                break;

            case 'input_absensi':
            case 'presensi_manual':
                require_once ROOT_PATH . 'models/AcademicModel.php';
                require_once ROOT_PATH . 'models/AbsensiModel.php';
                $academicModel = new AcademicModel();
                $absensiModel = new AbsensiModel();
                $guruId = intval($guru['id'] ?? 0);

                if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                    $input = $this->getPostInput();
                    $mapelId = intval($input['mapel_id'] ?? $_POST['mapel_id'] ?? 0);
                    $tanggal = Security::sanitize($input['tanggal'] ?? $_POST['tanggal'] ?? date('Y-m-d'));
                    $kategori = Security::sanitize($input['kategori'] ?? $_POST['kategori'] ?? $_GET['kategori'] ?? 'masuk');
                    $presensi = $input['absensi'] ?? $_POST['absensi'] ?? [];
                    $keteranganMap = $input['keterangan'] ?? $_POST['keterangan'] ?? [];

                    if ($mapelId <= 0) {
                        $this->jsonResponse(false, 'Mata pelajaran harus dipilih', null, 400);
                    }
                    if (empty($presensi) || !is_array($presensi)) {
                        $this->jsonResponse(false, 'Data presensi siswa tidak boleh kosong', null, 400);
                    }

                    $saved = 0;
                    foreach ($presensi as $siswaId => $status) {
                        $sId = intval($siswaId);
                        $ket = Security::sanitize($keteranganMap[$siswaId] ?? '');
                        if ($sId > 0 && !empty($status)) {
                            if ($absensiModel->saveManualAttendance($guruId, $mapelId, $sId, $tanggal, $status, $ket, $kategori)) {
                                $saved++;
                            }
                        }
                    }

                    if ($saved > 0) {
                        $this->jsonResponse(true, "Presensi manual berhasil disimpan untuk {$saved} siswa!");
                    } else {
                        $this->jsonResponse(false, "Gagal menyimpan presensi manual. Silakan periksa kembali data siswa.", null, 400);
                    }
                }

                $myMapelList = $academicModel->getMapelByGuru($guruId);
                $selectedMapelId = intval($_GET['mapel_id'] ?? $_POST['mapel_id'] ?? ($myMapelList[0]['id'] ?? 0));
                $tanggal = Security::sanitize($_GET['tanggal'] ?? $_POST['tanggal'] ?? date('Y-m-d'));

                $students = [];
                if ($selectedMapelId > 0) {
                    $students = $absensiModel->getEnrolledStudentsForAttendance($guruId, $selectedMapelId, $tanggal);
                }

                $this->jsonResponse(true, 'Data Presensi Manual Guru', [
                    'mapel_list' => $myMapelList,
                    'selected_mapel_id' => $selectedMapelId,
                    'tanggal' => $tanggal,
                    'students' => $students
                ]);
                break;

            case 'kartu':
                $this->jsonResponse(true, 'Kartu Digital Guru', [
                    'nip' => $guru['nip'] ?? '-',
                    'nama_lengkap' => $guru['nama_lengkap'] ?? '-',
                    'email' => $guru['email'] ?? '-',
                    'foto' => $guru['foto'] ?? null,
                    'qr_code' => 'GURU-' . ($guru['nip'] ?? $guru['id'])
                ]);
                break;

            case 'input_nilai':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $input = $this->getPostInput();
                    $siswaId = intval($input['siswa_id'] ?? 0);
                    $mapelId = intval($input['mapel_id'] ?? 0);
                    $nilaiTugas = floatval($input['nilai_tugas'] ?? 0);
                    $nilaiUts = floatval($input['nilai_uts'] ?? 0);
                    $nilaiUas = floatval($input['nilai_uas'] ?? 0);

                    if ($siswaId <= 0 || $mapelId <= 0) {
                        $this->jsonResponse(false, 'Siswa dan Mapel wajib dipilih', null, 400);
                    }

                    $nilaiAkhir = round(($nilaiTugas * 0.3) + ($nilaiUts * 0.3) + ($nilaiUas * 0.4), 2);

                    try {
                        $stmtIns = $this->db->prepare("
                            INSERT INTO nilai (siswa_id, mapel_id, nilai_tugas, nilai_uts, nilai_uas, nilai_akhir) 
                            VALUES (:sid, :mid, :nt, :uts, :uas, :na)
                            ON DUPLICATE KEY UPDATE nilai_tugas = :nt, nilai_uts = :uts, nilai_uas = :uas, nilai_akhir = :na
                        ");
                        $stmtIns->execute([
                            'sid' => $siswaId,
                            'mid' => $mapelId,
                            'nt' => $nilaiTugas,
                            'uts' => $nilaiUts,
                            'uas' => $nilaiUas,
                            'na' => $nilaiAkhir
                        ]);
                        $this->jsonResponse(true, 'Nilai siswa berhasil disimpan!');
                    } catch (\Throwable $eN) {
                        $this->jsonResponse(false, 'Gagal menyimpan nilai: ' . $eN->getMessage(), null, 500);
                    }
                }
                break;

            case 'bank_soal':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $input = $this->getPostInput();
                    $action = trim($input['action'] ?? '');
                    if ($action === 'delete') {
                        $soalId = intval($input['soal_id'] ?? 0);
                        if ($soalId > 0) {
                            $this->db->prepare("DELETE FROM pilihan_jawaban WHERE soal_id = ?")->execute([$soalId]);
                            $this->db->prepare("DELETE FROM soal WHERE id = ?")->execute([$soalId]);
                            $this->jsonResponse(true, 'Soal berhasil dihapus!');
                        }
                    }

                    $quizId = intval($input['quiz_id'] ?? 0);
                    $pertanyaan = trim($input['pertanyaan'] ?? '');
                    $jenisSoal = trim($input['jenis_soal'] ?? 'pg');
                    $bobot = intval($input['bobot'] ?? 10);
                    $pilihans = $input['pilihan'] ?? [];

                    if ($quizId <= 0 || empty($pertanyaan)) {
                        $this->jsonResponse(false, 'Quiz dan pertanyaan wajib diisi', null, 400);
                    }
                    try {
                        $stmtIns = $this->db->prepare("INSERT INTO soal (quiz_id, jenis_soal, pertanyaan, bobot) VALUES (:qid, :js, :pt, :bb)");
                        $stmtIns->execute(['qid' => $quizId, 'js' => $jenisSoal, 'pt' => $pertanyaan, 'bb' => $bobot]);
                        $newSoalId = $this->db->lastInsertId();

                        if (!empty($pilihans) && is_array($pilihans)) {
                            $stmtP = $this->db->prepare("INSERT INTO pilihan_jawaban (soal_id, teks_pilihan, is_benar) VALUES (:sid, :tp, :ib)");
                            foreach ($pilihans as $p) {
                                $teks = trim(is_array($p) ? ($p['teks'] ?? $p['teks_pilihan'] ?? '') : $p);
                                $isBenar = is_array($p) ? (!empty($p['is_benar']) ? 1 : 0) : 0;
                                if (!empty($teks)) {
                                    $stmtP->execute(['sid' => $newSoalId, 'tp' => $teks, 'ib' => $isBenar]);
                                }
                            }
                        }
                        $this->jsonResponse(true, 'Soal berhasil ditambahkan ke bank soal!');
                    } catch (\Throwable $eBs) {
                        $this->jsonResponse(false, 'Gagal menambahkan soal: ' . $eBs->getMessage(), null, 500);
                    }
                }

                $quizId = intval($_GET['quiz_id'] ?? 0);
                try {
                    if ($quizId > 0) {
                        $stmtS = $this->db->prepare("SELECT s.*, q.judul as judul_quiz, mp.nama_mapel, k.nama_kelas 
                            FROM soal s 
                            JOIN quiz q ON s.quiz_id = q.id 
                            LEFT JOIN mata_pelajaran mp ON q.mapel_id = mp.id 
                            LEFT JOIN kelas k ON q.kelas_id = k.id 
                            WHERE s.quiz_id = :qid ORDER BY s.id ASC");
                        $stmtS->execute(['qid' => $quizId]);
                        $soals = $stmtS->fetchAll();
                    } else {
                        $gid = intval($guru['id'] ?? 0);
                        $uid = intval($userId ?? 0);
                        $stmtS = $this->db->prepare("SELECT s.*, q.judul as judul_quiz, mp.nama_mapel, k.nama_kelas 
                            FROM soal s 
                            JOIN quiz q ON s.quiz_id = q.id 
                            LEFT JOIN mata_pelajaran mp ON q.mapel_id = mp.id 
                            LEFT JOIN kelas k ON q.kelas_id = k.id 
                            WHERE (q.guru_id = :gid OR q.guru_id = :uid) ORDER BY s.id DESC");
                        $stmtS->execute(['gid' => $gid, 'uid' => $uid]);
                        $soals = $stmtS->fetchAll();

                        if (empty($soals)) {
                            $soals = [];
                        }
                    }

                    $stmtP = $this->db->prepare("SELECT * FROM pilihan_jawaban WHERE soal_id = :sid ORDER BY id ASC");
                    foreach ($soals as &$s) {
                        $stmtP->execute(['sid' => $s['id']]);
                        $s['pilihan'] = $stmtP->fetchAll();
                    }
                    unset($s);

                    $this->jsonResponse(true, 'Daftar Bank Soal Quiz', $soals);
                } catch (\Throwable $eBs2) {
                    $this->jsonResponse(true, 'Daftar Bank Soal Quiz', []);
                }
                break;

            case 'koreksi_quiz':
            case 'koreksi':
                require_once ROOT_PATH . 'models/ExamModel.php';
                $examModel = new ExamModel();

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $input = $this->getPostInput();
                    $quizId = intval($input['quiz_id'] ?? 0);
                    $siswaId = intval($input['siswa_id'] ?? 0);
                    $nilaiEssay = $input['nilai_essay'] ?? [];

                    if ($quizId <= 0 || $siswaId <= 0) {
                        $this->jsonResponse(false, 'Quiz dan Siswa wajib ditentukan!', null, 400);
                    }

                    try {
                        $stmtGetSoal = $this->db->prepare("SELECT bobot FROM soal WHERE id = ?");
                        $stmtCheckExist = $this->db->prepare("SELECT id FROM jawaban_siswa WHERE siswa_id = ? AND quiz_id = ? AND soal_id = ?");
                        $stmtUpdate = $this->db->prepare("UPDATE jawaban_siswa SET nilai = ?, is_benar = IF(? > 0, 1, 0) WHERE id = ?");
                        $stmtInsert = $this->db->prepare("INSERT INTO jawaban_siswa (siswa_id, quiz_id, soal_id, is_benar, nilai) VALUES (?, ?, ?, IF(? > 0, 1, 0), ?)");

                        if (is_array($nilaiEssay)) {
                            foreach ($nilaiEssay as $keyId => $scoreVal) {
                                $keyId = intval($keyId);
                                if ($keyId <= 0) continue;
                                $scoreNum = floatval($scoreVal);

                                $stmtCheckExist->execute([$siswaId, $quizId, $keyId]);
                                $existId = $stmtCheckExist->fetchColumn();

                                if ($existId) {
                                    $stmtGetSoal->execute([$keyId]);
                                    $soalRow = $stmtGetSoal->fetch();
                                    $maxBobot = floatval($soalRow['bobot'] ?? 10);

                                    if ($scoreNum > $maxBobot && $maxBobot > 0) {
                                        $scoreNum = $maxBobot;
                                    }
                                    if ($scoreNum < 0) $scoreNum = 0;

                                    $stmtUpdate->execute([$scoreNum, $scoreNum, intval($existId)]);
                                } else {
                                    $stmtDirectUpdate = $this->db->prepare("UPDATE jawaban_siswa SET nilai = ?, is_benar = IF(? > 0, 1, 0) WHERE id = ?");
                                    $stmtDirectUpdate->execute([$scoreNum, $scoreNum, $keyId]);

                                    if ($stmtDirectUpdate->rowCount() == 0) {
                                        $stmtInsert->execute([$siswaId, $quizId, $keyId, $scoreNum, $scoreNum]);
                                    }
                                }
                            }
                        }

                        $examModel->recalculateQuizScore($siswaId, $quizId);
                        $this->jsonResponse(true, 'Nilai essay siswa berhasil dikoreksi dan total nilai kuis telah diperbarui!');
                    } catch (\Throwable $eK) {
                        $this->jsonResponse(false, 'Gagal menyimpan koreksi essay: ' . $eK->getMessage(), null, 500);
                    }
                }

                $quizId = intval($_GET['quiz_id'] ?? $_POST['quiz_id'] ?? 0);
                $siswaId = intval($_GET['siswa_id'] ?? $_POST['siswa_id'] ?? 0);

                if ($quizId > 0 && $siswaId > 0) {
                    try {
                        $stmtDetail = $this->db->prepare("
                            SELECT s.id as soal_id, s.pertanyaan, s.jenis_soal, s.bobot as max_bobot,
                                   js.id as jawaban_id, js.teks_jawaban_essay, js.pilihan_id, js.is_benar, js.nilai as nilai_diberikan,
                                   pj.teks_pilihan as jawaban_pg_dipilih
                            FROM soal s
                            LEFT JOIN jawaban_siswa js ON js.soal_id = s.id AND js.siswa_id = :sid AND js.quiz_id = :qid
                            LEFT JOIN pilihan_jawaban pj ON js.pilihan_id = pj.id
                            WHERE s.quiz_id = :qid2
                            ORDER BY s.id ASC
                        ");
                        $stmtDetail->execute(['sid' => $siswaId, 'qid' => $quizId, 'qid2' => $quizId]);
                        $details = $stmtDetail->fetchAll();
                        $this->jsonResponse(true, 'Detail Jawaban Siswa', $details);
                    } catch (\Throwable $eD) {
                        $this->jsonResponse(false, 'Gagal mengambil detail jawaban: ' . $eD->getMessage(), null, 500);
                    }
                } else {
                    try {
                        $guruId = intval($guru['id'] ?? 0);
                        $list = $examModel->getHasilQuizListByGuru($guruId);
                        $this->jsonResponse(true, 'Daftar Hasil Quiz Siswa untuk Koreksi', $list);
                    } catch (\Throwable $eL) {
                        $this->jsonResponse(true, 'Daftar Hasil Quiz Siswa untuk Koreksi', []);
                    }
                }
                break;

            case 'scan_qr':
            case 'scan-qr':
            case 'scan':
                $input = $this->getPostInput();
                $qrCode = trim($input['qr_code'] ?? $input['identifier'] ?? '');
                if (empty($qrCode)) {
                    $this->jsonResponse(false, 'Kode QR atau Identitas NIS/NIP wajib diisi!', null, 400);
                }
                try {
                    require_once ROOT_PATH . 'models/AbsensiModel.php';
                    $absensiModel = new AbsensiModel();
                    $res = $absensiModel->processQrScan($qrCode, $guru['id'], false);
                    if (!empty($res['success'])) {
                        $this->jsonResponse(true, $res['message'] ?? 'Presensi QR Berhasil!', $res);
                    } else {
                        $this->jsonResponse(false, $res['message'] ?? 'Gagal memproses QR Presensi', $res, 400);
                    }
                } catch (\Throwable $eQr) {
                    $this->jsonResponse(false, 'Gagal memproses QR Presensi: ' . $eQr->getMessage(), null, 500);
                }
                break;

            case 'susulan_requests':
            case 'susulan':
            case 'tugas_susulan_requests':
                require_once ROOT_PATH . 'models/LearningModel.php';
                require_once ROOT_PATH . 'models/ExamModel.php';
                $learningModel = new LearningModel();
                $examModel = new ExamModel();
                $guruId = intval($guru['id'] ?? 0);

                $tugasSusulan = [];
                try {
                    $tugasSusulan = $learningModel->getTugasSusulanRequestsByGuru($guruId);
                    foreach ($tugasSusulan as &$ts) {
                        $ts['type'] = 'tugas';
                        $ts['judul'] = $ts['judul_tugas'] ?? '';
                    }
                    unset($ts);
                } catch (\Throwable $eTs) {
                    $tugasSusulan = [];
                }

                $quizSusulan = [];
                try {
                    $quizSusulan = $examModel->getSusulanRequestsByGuru($guruId);
                    foreach ($quizSusulan as &$qs) {
                        $qs['type'] = 'quiz';
                        $qs['judul'] = $qs['judul_quiz'] ?? '';
                    }
                    unset($qs);
                } catch (\Throwable $eQs) {
                    $quizSusulan = [];
                }

                $allRequests = array_merge($tugasSusulan, $quizSusulan);
                $this->jsonResponse(true, 'Daftar Permohonan Susulan Siswa', $allRequests);
                break;

            case 'approve_susulan':
            case 'acc_susulan':
                $input = $this->getPostInput();
                $requestId = intval($_POST['request_id'] ?? $input['request_id'] ?? 0);
                $type = trim($_POST['type'] ?? $input['type'] ?? '');

                if ($requestId <= 0) {
                    $this->jsonResponse(false, 'ID Permohonan tidak valid', null, 400);
                }

                require_once ROOT_PATH . 'models/LearningModel.php';
                require_once ROOT_PATH . 'models/ExamModel.php';
                $learningModel = new LearningModel();
                $examModel = new ExamModel();

                $success = false;
                if ($type === 'tugas') {
                    $success = $learningModel->updateTugasSusulanStatus($requestId, 'disetujui');
                } elseif ($type === 'quiz') {
                    $success = $examModel->updateSusulanStatus($requestId, 'disetujui');
                } else {
                    $success = $learningModel->updateTugasSusulanStatus($requestId, 'disetujui');
                    if (!$success) {
                        $success = $examModel->updateSusulanStatus($requestId, 'disetujui');
                    }
                }

                if ($success) {
                    $this->jsonResponse(true, 'Permohonan susulan siswa berhasil disetujui (ACC)!');
                } else {
                    $this->jsonResponse(false, 'Gagal menyetujui permohonan susulan', null, 500);
                }
                break;

            case 'reject_susulan':
            case 'tolak_susulan':
                $input = $this->getPostInput();
                $requestId = intval($_POST['request_id'] ?? $input['request_id'] ?? 0);
                $type = trim($_POST['type'] ?? $input['type'] ?? '');

                if ($requestId <= 0) {
                    $this->jsonResponse(false, 'ID Permohonan tidak valid', null, 400);
                }

                require_once ROOT_PATH . 'models/LearningModel.php';
                require_once ROOT_PATH . 'models/ExamModel.php';
                $learningModel = new LearningModel();
                $examModel = new ExamModel();

                $success = false;
                if ($type === 'tugas') {
                    $success = $learningModel->updateTugasSusulanStatus($requestId, 'ditolak');
                } elseif ($type === 'quiz') {
                    $success = $examModel->updateSusulanStatus($requestId, 'ditolak');
                } else {
                    $success = $learningModel->updateTugasSusulanStatus($requestId, 'ditolak');
                    if (!$success) {
                        $success = $examModel->updateSusulanStatus($requestId, 'ditolak');
                    }
                }

                if ($success) {
                    $this->jsonResponse(true, 'Permohonan susulan siswa telah ditolak.');
                } else {
                    $this->jsonResponse(false, 'Gagal menolak permohonan susulan', null, 500);
                }
                break;

            default:
                $this->jsonResponse(false, 'Endpoint guru tidak ditemukan', null, 404);
        }
    }

    public function profil() {
        $userId = intval($_GET['user_id'] ?? $_POST['user_id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->getPostInput();
            $userId = intval($input['user_id'] ?? $_POST['user_id'] ?? $userId);
            $fullName = trim($input['full_name'] ?? $_POST['full_name'] ?? '');
            $email = trim($input['email'] ?? $_POST['email'] ?? '');
            $noTelp = trim($input['no_telepon'] ?? $_POST['no_telepon'] ?? '');
            $jk = trim($input['jenis_kelamin'] ?? $_POST['jenis_kelamin'] ?? '');
            $alamat = trim($input['alamat'] ?? $_POST['alamat'] ?? '');
            $newPassword = trim($input['password'] ?? $_POST['password'] ?? '');
            $avatarBase64 = $input['avatar_base64'] ?? $_POST['avatar_base64'] ?? '';

            if ($userId <= 0) {
                $this->jsonResponse(false, 'ID Pengguna tidak valid', null, 400);
            }

            try {
                // Process base64 profile image upload if present
                $newAvatarFilename = null;
                if (!empty($avatarBase64)) {
                    if (preg_match('/^data:image\/(\w+);base64,/', $avatarBase64, $type)) {
                        $avatarBase64 = substr($avatarBase64, strpos($avatarBase64, ',') + 1);
                        $ext = strtolower($type[1]);
                    } else {
                        $ext = 'jpg';
                    }
                    $avatarData = base64_decode($avatarBase64);
                    if ($avatarData !== false) {
                        $uploadDir = ROOT_PATH . 'assets/uploads/profile/';
                        if (!is_dir($uploadDir)) {
                            @mkdir($uploadDir, 0777, true);
                        }
                        $newAvatarFilename = 'profil_' . $userId . '_' . time() . '.' . $ext;
                        @file_put_contents($uploadDir . $newAvatarFilename, $avatarData);
                    }
                }

                // 1. Dynamic UPDATE users query without placeholder duplication
                $userFields = [];
                $userParams = ['uid' => $userId];

                if ($fullName !== '') {
                    $userFields[] = "full_name = :fullName";
                    $userParams['fullName'] = $fullName;
                }
                if ($email !== '') {
                    $userFields[] = "email = :email";
                    $userParams['email'] = $email;
                }
                if ($newPassword !== '') {
                    $userFields[] = "password = :password";
                    $userParams['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
                }
                if ($newAvatarFilename !== null) {
                    $userFields[] = "avatar = :avatar";
                    $userParams['avatar'] = $newAvatarFilename;
                }

                if (!empty($userFields)) {
                    $sqlUser = "UPDATE users SET " . implode(', ', $userFields) . " WHERE id = :uid";
                    $stmtUpd = $this->db->prepare($sqlUser);
                    $stmtUpd->execute($userParams);
                }

                // 2. Dynamic UPDATE detail (siswa or guru)
                $detailFields = [];
                $detailParams = ['uid' => $userId];

                if ($noTelp !== '') {
                    $detailFields[] = "no_telepon = :noTelp";
                    $detailParams['noTelp'] = $noTelp;
                }
                if ($jk !== '') {
                    $detailFields[] = "jenis_kelamin = :jk";
                    $detailParams['jk'] = $jk;
                }
                if ($alamat !== '') {
                    $detailFields[] = "alamat = :alamat";
                    $detailParams['alamat'] = $alamat;
                }

                if (!empty($detailFields)) {
                    // Try updating table `siswa`
                    try {
                        $siswaFields = $detailFields;
                        $siswaParams = $detailParams;
                        if ($newAvatarFilename !== null) {
                            $siswaFields[] = "foto = :foto";
                            $siswaParams['foto'] = $newAvatarFilename;
                        }
                        $sqlSiswa = "UPDATE siswa SET " . implode(', ', $siswaFields) . " WHERE user_id = :uid";
                        $stmtS = $this->db->prepare($sqlSiswa);
                        $stmtS->execute($siswaParams);
                    } catch (\Throwable $eS) {}

                    // Try updating table `guru`
                    try {
                        $guruFields = $detailFields;
                        $guruParams = $detailParams;
                        if ($newAvatarFilename !== null) {
                            $guruFields[] = "foto_profil = :fotoProfil";
                            $guruParams['fotoProfil'] = $newAvatarFilename;
                        }
                        $sqlGuru = "UPDATE guru SET " . implode(', ', $guruFields) . " WHERE user_id = :uid";
                        $stmtG = $this->db->prepare($sqlGuru);
                        $stmtG->execute($guruParams);
                    } catch (\Throwable $eG) {}
                } else if ($newAvatarFilename !== null) {
                    try {
                        $stmtS = $this->db->prepare("UPDATE siswa SET foto = ? WHERE user_id = ?");
                        $stmtS->execute([$newAvatarFilename, $userId]);
                    } catch (\Throwable $eS) {}
                    try {
                        $stmtG = $this->db->prepare("UPDATE guru SET foto_profil = ? WHERE user_id = ?");
                        $stmtG->execute([$newAvatarFilename, $userId]);
                    } catch (\Throwable $eG) {}
                }

                $this->jsonResponse(true, 'Profil dan data diri berhasil diperbarui!');
            } catch (\Throwable $eP) {
                $this->jsonResponse(false, 'Gagal memperbarui profil: ' . $eP->getMessage(), null, 500);
            }
        }

        try {
            $stmtU = $this->db->prepare("SELECT id, username, email, full_name, avatar, role_id FROM users WHERE id = :uid LIMIT 1");
            $stmtU->execute(['uid' => $userId]);
            $user = $stmtU->fetch();

            // Get extra details
            $details = null;
            try {
                $stmtS = $this->db->prepare("
                    SELECT s.*, k.nama_kelas, j.nama_jurusan 
                    FROM siswa s 
                    LEFT JOIN kelas k ON s.kelas_id = k.id 
                    LEFT JOIN jurusan j ON s.jurusan_id = j.id 
                    WHERE s.user_id = :uid LIMIT 1
                ");
                $stmtS->execute(['uid' => $userId]);
                $details = $stmtS->fetch() ?: null;
            } catch (\Throwable $eS) {}

            if (!$details) {
                try {
                    $stmtG = $this->db->prepare("SELECT * FROM guru WHERE user_id = :uid LIMIT 1");
                    $stmtG->execute(['uid' => $userId]);
                    $details = $stmtG->fetch() ?: null;
                } catch (\Throwable $eG) {}
            }

            if ($user) {
                $avFile = $user['avatar'] ?? ($details['foto'] ?? ($details['foto_profil'] ?? ($details['avatar'] ?? '')));
                if (!empty($avFile) && $avFile !== 'default_avatar.png' && $avFile !== 'default.png') {
                    $user['avatar_url'] = BASE_URL . 'assets/uploads/profile/' . $avFile;
                } else {
                    $user['avatar_url'] = null;
                }
            }

            $this->jsonResponse(true, 'Data Profil User', [
                'user' => $user,
                'details' => $details
            ]);
        } catch (\Throwable $eP2) {
            $this->jsonResponse(false, 'Data profil tidak ditemukan', null, 404);
        }
    }

    public function live_class() {
        try {
            $stmt = $this->db->query("
                SELECT lc.*, mp.nama_mapel, g.nama_lengkap as nama_guru, k.nama_kelas 
                FROM live_class lc 
                LEFT JOIN mata_pelajaran mp ON lc.mapel_id = mp.id 
                LEFT JOIN guru g ON lc.guru_id = g.id 
                LEFT JOIN kelas k ON lc.kelas_id = k.id 
                ORDER BY lc.created_at DESC LIMIT 20
            ");
            $classes = $stmt->fetchAll();
            $this->jsonResponse(true, 'Daftar Kelas Virtual Live Meeting', $classes);
        } catch (\Throwable $e) {
            $this->jsonResponse(true, 'Daftar Kelas Virtual Live Meeting', [
                [
                    'id' => 1,
                    'topik' => 'Live Zoom Pembelajaran Pemrograman Mobile',
                    'nama_guru' => 'Tim Pengajar MH',
                    'nama_kelas' => 'XII RPL',
                    'waktu' => date('Y-m-d H:i:s'),
                    'meeting_link' => 'https://meet.google.com/abc-defg-hij',
                    'status' => 'Ongoing'
                ]
            ]);
        }
    }

    public function panduan() {
        $this->jsonResponse(true, 'Buku Panduan & Petunjuk LMS E-Learning', [
            [
                'id' => 1,
                'judul' => 'Panduan Presensi QR Code Mobile',
                'kategori' => 'Absensi',
                'deskripsi' => "1. Buka Tab Presensi / Scan QR Code pada aplikasi Mobile.\n2. Untuk Guru: Arahkan kamera HP ke Kartu QR Presensi Siswa.\n3. Untuk Siswa: Tunjukkan Kartu Presensi Digital ke Guru.\n4. Sistem akan secara otomatis memutar suara konfirmasi presensi dan menyimpan data ke database E-Learning."
            ],
            [
                'id' => 2,
                'judul' => 'Panduan Pengerjaan CBT & Quiz Online',
                'kategori' => 'Ujian',
                'deskripsi' => "1. Masuk ke Tab CBT & Quiz.\n2. Pilih Ujian atau Quiz yang tersedia dan klik 'Mulai Ujian'.\n3. Jawab soal secara berurutan sebelum batas waktu timer habis.\n4. Klik 'Kirim Ujian Selesai' untuk mengirimkan nilai secara otomatis ke database."
            ],
            [
                'id' => 3,
                'judul' => 'Panduan Pengunduhan & Pengumpulan Tugas',
                'kategori' => 'Tugas',
                'deskripsi' => "1. Masuk ke Tab Tugas.\n2. Klik pada kartu Tugas untuk membuka Detail Instruksi dan File Lampiran dari Guru.\n3. Unduh atau buka file instruksi tugas.\n4. Ketikkan Catatan Jawaban / Masukkan nama file jawaban lalu klik 'Kirim Jawaban'."
            ],
            [
                'id' => 4,
                'judul' => 'Panduan Pengelolaan Bank Soal Guru',
                'kategori' => 'Guru',
                'deskripsi' => "1. Guru masuk ke Tab CBT & Quiz lalu pilih 'Buat Quiz CBT'.\n2. Klik pada Kartu Quiz untuk membuka Bank Soal.\n3. Klik 'Tambah Soal' untuk memasukkan pertanyaan, bobot nilai, dan pilihan jawaban.\n4. Soal yang dibuat akan langsung tersimpan di Bank Soal E-Learning."
            ],
            [
                'id' => 5,
                'judul' => 'Panduan Passcode Key System & Gabung Rombel',
                'kategori' => 'Pendaftaran',
                'deskripsi' => "1. Masuk ke Menu 'Gabung Rombel & Key Mapel'.\n2. Gunakan kolom pencarian untuk mencari Mata Pelajaran atau Guru Pengampu.\n3. Untuk mendaftar ke Mapel Guru: Minta Passcode Key resmi ke Guru Pengampu lalu ketikkan pada kolom Passcode Key.\n4. Untuk mendaftar ke Rombel Kelas Utama: Minta Kode Akses Rombel (misal: MH-A1B2C3) ke Wali Kelas."
            ],
            [
                'id' => 6,
                'judul' => 'Panduan Forum Diskusi & Pesan Direct Chat',
                'kategori' => 'Komunikasi',
                'deskripsi' => "1. Forum Diskusi: Buka Menu Forum untuk membaca, membuat topik pertanyaan baru, atau berdiskusi pada kolom komentar.\n2. Direct Chat: Buka Menu Chat Direct untuk memilih kontak Guru/Siswa dan berkirim pesan secara langsung."
            ],
            [
                'id' => 7,
                'judul' => 'Panduan Edit Profil & Upload Foto Mobile',
                'kategori' => 'Pengaturan',
                'deskripsi' => "1. Buka Header AppBar / Profil pada aplikasi Mobile.\n2. Ketuk foto profil untuk memilih foto dari Galeri HP atau Kamera.\n3. Perbarui nomor telepon, alamat, password baru jika perlu, lalu simpan perubahan."
            ]
        ]);
    }

    public function forum($endpoint = 'list') {
        $endpoint = strtolower(explode('&', explode('?', $endpoint)[0])[0]);
        $input = $this->getPostInput();
        $userId = intval($_GET['user_id'] ?? $_POST['user_id'] ?? $input['user_id'] ?? 0);

        $realUserId = 1;
        try {
            $stmtU = $this->db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1");
            $uRow = $stmtU->fetch();
            if ($uRow && !empty($uRow['id'])) {
                $realUserId = intval($uRow['id']);
            }
        } catch (\Throwable $eU) {}

        if ($userId <= 0) {
            $userId = $realUserId;
        }

        try { $this->db->exec("ALTER TABLE forum ADD COLUMN kategori VARCHAR(50) DEFAULT 'Umum'"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE forum ADD COLUMN visibility ENUM('public', 'private') DEFAULT 'public'"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE forum ADD COLUMN target_role VARCHAR(50) NULL"); } catch (\Throwable $e) {}
        try { $this->db->exec("ALTER TABLE forum ADD COLUMN target_kelas_id INT NULL"); } catch (\Throwable $e) {}

        if (file_exists(ROOT_PATH . 'helpers/ProfanityFilterHelper.php')) {
            require_once ROOT_PATH . 'helpers/ProfanityFilterHelper.php';
        }
        if (file_exists(ROOT_PATH . 'models/CommunicationModel.php')) {
            require_once ROOT_PATH . 'models/CommunicationModel.php';
        }

        $commModel = class_exists('CommunicationModel') ? new CommunicationModel() : null;

        if ($endpoint === 'create' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $endpoint !== 'comment')) {
            $judul = trim($input['judul'] ?? '');
            $konten = trim($input['konten'] ?? '');
            $kategori = trim($input['kategori'] ?? 'Umum');
            $visibility = trim($input['visibility'] ?? 'public');
            $targetKelasId = intval($input['target_kelas_id'] ?? 0);
            $mapelId = intval($input['mapel_id'] ?? 0);
            $gambarFilename = null;

            // Process image file or base64 if provided
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                if (file_exists(ROOT_PATH . 'helpers/UploadHelper.php')) {
                    require_once ROOT_PATH . 'helpers/UploadHelper.php';
                    $gambarFilename = UploadHelper::upload($_FILES['gambar'], 'forum');
                }
            }
            if (!$gambarFilename && !empty($input['gambar_base64'])) {
                $base64 = $input['gambar_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $base64 = substr($base64, strpos($base64, ',') + 1);
                    $ext = strtolower($type[1]);
                } else {
                    $ext = 'jpg';
                }
                $imgData = base64_decode($base64);
                if ($imgData !== false) {
                    $uploadDir = ROOT_PATH . 'assets/uploads/forum/';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    $gambarFilename = 'forum_' . time() . '_' . uniqid() . '.' . $ext;
                    @file_put_contents($uploadDir . $gambarFilename, $imgData);
                }
            }

            if ($targetKelasId <= 0 && $userId > 0) {
                $stmtS = $this->db->prepare("SELECT kelas_id FROM siswa WHERE user_id = :uid LIMIT 1");
                $stmtS->execute(['uid' => $userId]);
                $sData = $stmtS->fetch();
                if ($sData && !empty($sData['kelas_id'])) {
                    $targetKelasId = intval($sData['kelas_id']);
                }
            }

            if (empty($judul) || empty($konten)) {
                $this->jsonResponse(false, 'Judul dan konten topik wajib diisi!', null, 400);
            }

            if (class_exists('ProfanityFilterHelper')) {
                $judul = ProfanityFilterHelper::filter($judul);
                $konten = ProfanityFilterHelper::filter($konten);
            }

            if ($commModel) {
                try {
                    $commModel->createForumTopic($userId, $mapelId > 0 ? $mapelId : null, $judul, $konten, $gambarFilename, $visibility, null, $targetKelasId > 0 ? $targetKelasId : null);
                    $this->jsonResponse(true, 'Topik diskusi berhasil diterbitkan!');
                } catch (\Throwable $eCm) {}
            }

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO forum (user_id, judul, konten, gambar, kategori, visibility, target_kelas_id, created_at) 
                    VALUES (:uid, :jdl, :ktn, :gbr, :ktg, :vis, :tkid, NOW())
                ");
                $stmt->execute([
                    'uid' => $userId,
                    'jdl' => $judul,
                    'ktn' => $konten,
                    'gbr' => $gambarFilename,
                    'ktg' => $kategori,
                    'vis' => $visibility === 'private' ? 'private' : 'public',
                    'tkid' => $targetKelasId > 0 ? $targetKelasId : null
                ]);
                $this->jsonResponse(true, 'Topik diskusi berhasil diterbitkan!');
            } catch (\Throwable $eC) {
                try {
                    $stmtFB = $this->db->prepare("
                        INSERT INTO forum (user_id, judul, konten, gambar, created_at) 
                        VALUES (:uid, :jdl, :ktn, :gbr, NOW())
                    ");
                    $stmtFB->execute([
                        'uid' => $userId,
                        'jdl' => $judul,
                        'ktn' => $konten,
                        'gbr' => $gambarFilename
                    ]);
                    $this->jsonResponse(true, 'Topik diskusi berhasil diterbitkan!');
                } catch (\Throwable $eFB) {
                    $this->jsonResponse(false, 'Gagal menerbitkan topik: ' . $eFB->getMessage(), null, 500);
                }
            }
        } elseif ($endpoint === 'comment') {
            $forumId = intval($input['forum_id'] ?? $_GET['forum_id'] ?? 0);
            $komentar = trim($input['komentar'] ?? $input['isi_komentar'] ?? '');
            $gambarFilename = null;

            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                if (file_exists(ROOT_PATH . 'helpers/UploadHelper.php')) {
                    require_once ROOT_PATH . 'helpers/UploadHelper.php';
                    $gambarFilename = UploadHelper::upload($_FILES['gambar'], 'forum');
                }
            }
            if (!$gambarFilename && !empty($input['gambar_base64'])) {
                $base64 = $input['gambar_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $base64 = substr($base64, strpos($base64, ',') + 1);
                    $ext = strtolower($type[1]);
                } else {
                    $ext = 'jpg';
                }
                $imgData = base64_decode($base64);
                if ($imgData !== false) {
                    $uploadDir = ROOT_PATH . 'assets/uploads/forum/';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    $gambarFilename = 'comment_' . time() . '_' . uniqid() . '.' . $ext;
                    @file_put_contents($uploadDir . $gambarFilename, $imgData);
                }
            }

            if ($forumId <= 0 || empty($komentar)) {
                $this->jsonResponse(false, 'ID Forum dan isi komentar wajib diisi!', null, 400);
            }

            if (class_exists('ProfanityFilterHelper')) {
                $komentar = ProfanityFilterHelper::filter($komentar);
            }

            if ($commModel) {
                try {
                    $commModel->addKomentar($forumId, $userId, $komentar, null, $gambarFilename);
                    $this->jsonResponse(true, 'Komentar berhasil ditambahkan!');
                } catch (\Throwable $eAm) {}
            }

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO komentar (forum_id, user_id, komentar, gambar, created_at) 
                    VALUES (:fid, :uid, :km, :gbr, NOW())
                ");
                $stmt->execute([
                    'fid' => $forumId,
                    'uid' => $userId,
                    'km' => $komentar,
                    'gbr' => $gambarFilename
                ]);
                $this->jsonResponse(true, 'Komentar berhasil ditambahkan!');
            } catch (\Throwable $eKm) {
                $this->jsonResponse(false, 'Gagal menambahkan komentar: ' . $eKm->getMessage(), null, 500);
            }
        } elseif ($endpoint === 'detail') {
            $forumId = intval($_GET['forum_id'] ?? $_GET['id'] ?? 0);
            $topic = null;
            $comments = [];

            if ($commModel) {
                try {
                    $topic = $commModel->getForumDetail($forumId);
                    $comments = $commModel->getKomentar($forumId);
                } catch (\Throwable $eFdM) {}
            }

            if (!$topic) {
                try {
                    $stmtF = $this->db->prepare("
                        SELECT f.id, f.user_id, f.judul, f.konten, f.gambar,
                               COALESCE(f.kategori, 'Umum') as kategori,
                               COALESCE(f.visibility, 'public') as visibility,
                               f.created_at,
                               COALESCE(u.full_name, 'Pengguna') as full_name, 
                               COALESCE(s.foto_profil, g.foto, u.avatar, '') as avatar_file, 
                               COALESCE(r.name, 'Member') as role_name
                        FROM forum f
                        LEFT JOIN users u ON f.user_id = u.id
                        LEFT JOIN roles r ON u.role_id = r.id
                        LEFT JOIN siswa s ON s.user_id = u.id
                        LEFT JOIN guru g ON g.user_id = u.id
                        WHERE f.id = :fid
                        LIMIT 1
                    ");
                    $stmtF->execute(['fid' => $forumId]);
                    $topic = $stmtF->fetch();
                } catch (\Throwable $eFd) {}
            }

            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

            if ($topic) {
                $avFile = $topic['avatar_file'] ?? ($topic['avatar'] ?? ($topic['foto_profil'] ?? ''));
                if (!empty($avFile) && $avFile !== 'default_avatar.png' && $avFile !== 'default.png') {
                    $topic['avatar_url'] = strpos($avFile, 'http') === 0 ? $avFile : "{$scheme}://{$host}/assets/uploads/profile/" . $avFile;
                } else {
                    $topic['avatar_url'] = null;
                }

                if (!empty($topic['gambar'])) {
                    $gFile = $topic['gambar'];
                    if (strpos($gFile, 'http') === 0) {
                        $topic['gambar_url'] = $gFile;
                    } else {
                        $folder = file_exists(ROOT_PATH . 'assets/uploads/forum/' . $gFile) ? 'forum' : 'tugas';
                        $topic['gambar_url'] = "{$scheme}://{$host}/assets/uploads/{$folder}/" . $gFile;
                    }
                } else {
                    $topic['gambar_url'] = null;
                }
            }

            if (empty($comments)) {
                try {
                    $stmtK = $this->db->prepare("
                        SELECT k.id, k.forum_id, k.user_id, k.komentar as isi_komentar, k.gambar, k.created_at,
                               COALESCE(u.full_name, 'Pengguna') as full_name, 
                               COALESCE(s.foto_profil, g.foto, u.avatar, '') as avatar_file
                        FROM komentar k
                        LEFT JOIN users u ON k.user_id = u.id
                        LEFT JOIN siswa s ON s.user_id = u.id
                        LEFT JOIN guru g ON g.user_id = u.id
                        WHERE k.forum_id = :fid
                        ORDER BY k.created_at ASC
                    ");
                    $stmtK->execute(['fid' => $forumId]);
                    $comments = $stmtK->fetchAll();
                } catch (\Throwable $eK) {}
            }

            foreach ($comments as &$c) {
                $c['isi_komentar'] = $c['isi_komentar'] ?? ($c['komentar'] ?? '');
                $avFile = $c['avatar_file'] ?? ($c['avatar'] ?? '');
                if (!empty($avFile) && $avFile !== 'default_avatar.png' && $avFile !== 'default.png') {
                    $c['avatar_url'] = strpos($avFile, 'http') === 0 ? $avFile : "{$scheme}://{$host}/assets/uploads/profile/" . $avFile;
                } else {
                    $c['avatar_url'] = null;
                }

                if (!empty($c['gambar'])) {
                    $cgFile = $c['gambar'];
                    if (strpos($cgFile, 'http') === 0) {
                        $c['gambar_url'] = $cgFile;
                    } else {
                        $c['gambar_url'] = "{$scheme}://{$host}/assets/uploads/forum/" . $cgFile;
                    }
                } else {
                    $c['gambar_url'] = null;
                }
            }

            $this->jsonResponse(true, 'Detail Topik Forum', [
                'topic' => $topic,
                'comments' => $comments
            ]);
        } else {
            // list
            $list = [];
            $userRole = 'Siswa';
            $userKelasId = null;

            if ($userId > 0) {
                try {
                    $stmtU = $this->db->prepare("
                        SELECT u.*, r.name as role_name, s.kelas_id 
                        FROM users u 
                        LEFT JOIN roles r ON u.role_id = r.id 
                        LEFT JOIN siswa s ON s.user_id = u.id 
                        WHERE u.id = ? LIMIT 1
                    ");
                    $stmtU->execute([$userId]);
                    $uData = $stmtU->fetch();
                    if ($uData) {
                        $userRole = $uData['role_name'] ?? 'Siswa';
                        $userKelasId = $uData['kelas_id'] ?? null;
                    }
                } catch (\Throwable $eU) {}
            }

            if ($commModel) {
                try {
                    $list = $commModel->getForumTopics($userId, $userRole, $userKelasId);
                } catch (\Throwable $eC) {}
            }

            if (empty($list) || !is_array($list)) {
                try {
                    $stmt = $this->db->query("
                        SELECT f.*, 
                               COALESCE(f.kategori, 'Umum') as kategori,
                               COALESCE(f.visibility, 'public') as visibility,
                               COALESCE(k.nama_kelas, 'Semua Kelas') as target_nama_kelas,
                               COALESCE(u.full_name, 'Pengguna E-Learning') as full_name, 
                               COALESCE(s.foto_profil, g.foto, u.avatar, '') as avatar_file, 
                               COALESCE(r.name, 'Member') as role_name,
                               (SELECT COUNT(*) FROM komentar km WHERE km.forum_id = f.id) as total_komentar
                        FROM forum f
                        LEFT JOIN users u ON f.user_id = u.id
                        LEFT JOIN roles r ON u.role_id = r.id
                        LEFT JOIN siswa s ON s.user_id = u.id
                        LEFT JOIN guru g ON g.user_id = u.id
                        LEFT JOIN kelas k ON f.target_kelas_id = k.id
                        ORDER BY f.id DESC
                        LIMIT 50
                    ");
                    $list = $stmt->fetchAll();
                } catch (\Throwable $eL) {
                    try {
                        $stmt2 = $this->db->query("
                            SELECT f.*, 
                                   COALESCE(u.full_name, 'Pengguna E-Learning') as full_name, 
                                   COALESCE(s.foto_profil, g.foto, u.avatar, '') as avatar_file, 
                                   COALESCE(r.name, 'Member') as role_name,
                                   (SELECT COUNT(*) FROM komentar km WHERE km.forum_id = f.id) as total_komentar
                            FROM forum f
                            LEFT JOIN users u ON f.user_id = u.id
                            LEFT JOIN roles r ON u.role_id = r.id
                            LEFT JOIN siswa s ON s.user_id = u.id
                            LEFT JOIN guru g ON g.user_id = u.id
                            ORDER BY f.id DESC
                            LIMIT 50
                        ");
                        $list = $stmt2->fetchAll();
                    } catch (\Throwable $eL2) {
                        $list = [];
                    }
                }
            }

            if (empty($list) || !is_array($list)) {
                try {
                    $stmtIns = $this->db->prepare("
                        INSERT INTO forum (user_id, judul, konten, created_at) 
                        VALUES (:uid, 'Selamat Datang di Forum Komunitas SMK Muthia Harapan Cicalengka', 'Diskusi seputar KBM, absensi QR Code, jadwal pelajaran, dan CBT Online SMK Muthia Harapan Cicalengka.', NOW())
                    ");
                    $stmtIns->execute(['uid' => $realUserId]);

                    $stmt2 = $this->db->query("
                        SELECT f.id, COALESCE(f.user_id, 1) as user_id, f.judul, f.konten, f.gambar, f.created_at,
                               COALESCE(u.full_name, 'Admin E-Learning') as full_name, 
                               COALESCE(s.foto_profil, g.foto, u.avatar, '') as avatar_file, 
                               COALESCE(r.name, 'Admin') as role_name,
                               (SELECT COUNT(*) FROM komentar km WHERE km.forum_id = f.id) as total_komentar
                        FROM forum f
                        LEFT JOIN users u ON f.user_id = u.id
                        LEFT JOIN roles r ON u.role_id = r.id
                        LEFT JOIN siswa s ON s.user_id = u.id
                        LEFT JOIN guru g ON g.user_id = u.id
                        ORDER BY f.id DESC
                        LIMIT 50
                    ");
                    $list = $stmt2->fetchAll();
                } catch (\Throwable $eSeed) {}
            }

            if (empty($list) || !is_array($list)) {
                $list = [
                    [
                        'id' => 1,
                        'user_id' => $realUserId,
                        'judul' => 'Forum Komunitas SMK Muthia Harapan Cicalengka',
                        'konten' => 'Selamat datang di Forum Komunitas SMK Muthia Harapan Cicalengka. Silakan ketuk tombol + Topik Baru di kanan bawah untuk membuat topik diskusi baru!',
                        'kategori' => 'Umum',
                        'visibility' => 'public',
                        'target_nama_kelas' => 'Semua Kelas',
                        'full_name' => 'Admin E-Learning',
                        'avatar_url' => null,
                        'gambar' => null,
                        'gambar_url' => null,
                        'role_name' => 'Admin',
                        'total_komentar' => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ];
            }

            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

            foreach ($list as &$f) {
                if (!isset($f['kategori']) || empty($f['kategori'])) $f['kategori'] = 'Umum';
                if (!isset($f['visibility']) || empty($f['visibility'])) $f['visibility'] = 'public';
                if (!isset($f['target_nama_kelas']) || empty($f['target_nama_kelas'])) $f['target_nama_kelas'] = 'Semua Kelas';
                if (isset($f['total_replies']) && !isset($f['total_komentar'])) {
                    $f['total_komentar'] = (int)$f['total_replies'];
                }

                $avFile = $f['avatar_file'] ?? ($f['avatar'] ?? ($f['foto_profil'] ?? ''));
                if (!empty($avFile) && $avFile !== 'default_avatar.png' && $avFile !== 'default.png') {
                    $f['avatar_url'] = strpos($avFile, 'http') === 0 ? $avFile : "{$scheme}://{$host}/assets/uploads/profile/" . $avFile;
                } else {
                    $f['avatar_url'] = null;
                }

                if (!empty($f['gambar'])) {
                    $gFile = $f['gambar'];
                    if (strpos($gFile, 'http') === 0) {
                        $f['gambar_url'] = $gFile;
                    } else {
                        $folder = file_exists(ROOT_PATH . 'assets/uploads/forum/' . $gFile) ? 'forum' : 'tugas';
                        $f['gambar_url'] = "{$scheme}://{$host}/assets/uploads/{$folder}/" . $gFile;
                    }
                } else {
                    $f['gambar_url'] = null;
                }
            }
            $this->jsonResponse(true, 'Daftar Forum Diskusi', $list);
        }
    }

    public function chat($endpoint = 'contacts') {
        $endpoint = strtolower(explode('?', $endpoint)[0]);
        $input = $this->getPostInput();
        $userId = intval($_GET['user_id'] ?? $_POST['user_id'] ?? $input['user_id'] ?? 0);

        try {
            $this->db->exec("ALTER TABLE chat ADD COLUMN is_read TINYINT(1) DEFAULT 0");
            $this->db->exec("UPDATE chat SET is_read = 0 WHERE is_read IS NULL");
        } catch (\Throwable $eIgn) {}

        if (file_exists(ROOT_PATH . 'helpers/ProfanityFilterHelper.php')) {
            require_once ROOT_PATH . 'helpers/ProfanityFilterHelper.php';
        }

        if ($endpoint === 'messages' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $receiverId = intval($input['receiver_id'] ?? 0);
            $pesan = trim($input['pesan'] ?? $input['message'] ?? '');

            if ($receiverId <= 0 || empty($pesan)) {
                $this->jsonResponse(false, 'Penerima dan pesan tidak boleh kosong!', null, 400);
            }

            if (class_exists('ProfanityFilterHelper')) {
                $pesan = ProfanityFilterHelper::filter($pesan);
            }

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO chat (sender_id, receiver_id, message, created_at, is_read) 
                    VALUES (:sid, :rid, :msg, NOW(), 0)
                ");
                $stmt->execute([
                    'sid' => $userId,
                    'rid' => $receiverId,
                    'msg' => $pesan
                ]);
                $this->jsonResponse(true, 'Pesan terkirim!');
            } catch (\Throwable $eMsg) {
                $this->jsonResponse(false, 'Gagal mengirim pesan: ' . $eMsg->getMessage(), null, 500);
            }
        } elseif ($endpoint === 'messages' || $endpoint === 'detail') {
            $receiverId = intval($_GET['receiver_id'] ?? $_GET['contact_id'] ?? 0);
            try {
                // Fetch direct messages between user and contact
                $stmt = $this->db->prepare("
                    SELECT c.id, c.sender_id, c.receiver_id, c.message as pesan, c.created_at, c.is_read,
                           u1.full_name as sender_name, u2.full_name as receiver_name
                    FROM chat c
                    JOIN users u1 ON c.sender_id = u1.id
                    JOIN users u2 ON c.receiver_id = u2.id
                    WHERE (c.sender_id = :uid1 AND c.receiver_id = :rec1)
                       OR (c.sender_id = :rec2 AND c.receiver_id = :uid2)
                    ORDER BY c.created_at ASC
                ");
                $stmt->execute([
                    'uid1' => $userId,
                    'rec1' => $receiverId,
                    'rec2' => $receiverId,
                    'uid2' => $userId
                ]);
                $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Mark messages from contact to current user as read (is_read = 1)
                if ($receiverId > 0 && $userId > 0) {
                    $updRead = $this->db->prepare("UPDATE chat SET is_read = 1 WHERE sender_id = :rec AND receiver_id = :uid AND (is_read = 0 OR is_read IS NULL OR is_read = '0')");
                    $updRead->execute(['rec' => $receiverId, 'uid' => $userId]);
                }

                $this->jsonResponse(true, 'Riwayat Chat Direct', $messages);
            } catch (\Throwable $eH) {
                $this->jsonResponse(true, 'Riwayat Chat Direct', []);
            }
        } elseif ($endpoint === 'mark_read' || $endpoint === 'read') {
            $contactId = intval($input['contact_id'] ?? $input['receiver_id'] ?? $_GET['contact_id'] ?? $_GET['receiver_id'] ?? 0);
            if ($contactId > 0 && $userId > 0) {
                try {
                    $updRead = $this->db->prepare("UPDATE chat SET is_read = 1 WHERE sender_id = :cid AND receiver_id = :uid AND (is_read = 0 OR is_read IS NULL OR is_read = '0')");
                    $updRead->execute(['cid' => $contactId, 'uid' => $userId]);
                    $this->jsonResponse(true, 'Chat berhasil ditandai terbaca');
                } catch (\Throwable $eMark) {
                    $this->jsonResponse(false, 'Gagal mark read: ' . $eMark->getMessage(), null, 500);
                }
            } else {
                $this->jsonResponse(false, 'Contact ID & User ID required', null, 400);
            }
        } else {
            // contacts list with unread_count calculation
            try {
                $stmt = $this->db->prepare("
                    SELECT u.id, 
                           COALESCE(s.nama_lengkap, g.nama_lengkap, u.full_name) as full_name,
                           COALESCE(s.foto_profil, g.foto, u.avatar, '') as avatar_file,
                           COALESCE(r.name, 'Pengguna') as role_name,
                           (SELECT message FROM chat WHERE ((sender_id = u.id AND receiver_id = :uid1) OR (sender_id = :uid2 AND receiver_id = u.id)) ORDER BY id DESC LIMIT 1) as last_message,
                           (SELECT created_at FROM chat WHERE ((sender_id = u.id AND receiver_id = :uid3) OR (sender_id = :uid4 AND receiver_id = u.id)) ORDER BY id DESC LIMIT 1) as last_time,
                           (SELECT COUNT(*) FROM chat WHERE sender_id = u.id AND receiver_id = :uid_unr AND (is_read = 0 OR is_read IS NULL OR is_read = '0')) as unread_count
                    FROM users u
                    LEFT JOIN roles r ON u.role_id = r.id
                    LEFT JOIN siswa s ON s.user_id = u.id
                    LEFT JOIN guru g ON g.user_id = u.id
                    WHERE u.id != :uid5
                    ORDER BY unread_count DESC, last_time DESC, full_name ASC
                ");
                $stmt->execute([
                    'uid1' => $userId,
                    'uid2' => $userId,
                    'uid3' => $userId,
                    'uid4' => $userId,
                    'uid_unr' => $userId,
                    'uid5' => $userId
                ]);
                $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Throwable $eCt) {
                try {
                    $stmtFB = $this->db->prepare("
                        SELECT u.id, u.full_name, COALESCE(u.avatar, '') as avatar_file, COALESCE(r.name, 'Pengguna') as role_name 
                        FROM users u 
                        LEFT JOIN roles r ON u.role_id = r.id 
                        WHERE u.id != :uid 
                        ORDER BY u.full_name ASC
                    ");
                    $stmtFB->execute(['uid' => $userId]);
                    $contacts = $stmtFB->fetchAll(PDO::FETCH_ASSOC);
                } catch (\Throwable $eFB) {
                    $contacts = [];
                }
            }

            foreach ($contacts as &$c) {
                $c['id'] = (int)$c['id'];
                $c['unread_count'] = (int)($c['unread_count'] ?? 0);
                $c['nama'] = $c['full_name'] ?? '';
                $c['updated_at'] = $c['last_time'] ?? '';

                $avFile = $c['avatar_file'] ?? '';
                if (!empty($avFile) && $avFile !== 'default_avatar.png' && $avFile !== 'default.png') {
                    $c['avatar_url'] = strpos($avFile, 'http') === 0 ? $avFile : BASE_URL . 'assets/uploads/profile/' . $avFile;
                } else {
                    $c['avatar_url'] = null;
                }
            }
            unset($c);
            $this->jsonResponse(true, 'Daftar Kontak Direct Chat', $contacts);
        }
    }

    public function library($endpoint = 'list') {
        try {
            $search = trim($_GET['search'] ?? $_POST['search'] ?? '');
            $kategori = trim($_GET['kategori'] ?? $_POST['kategori'] ?? '');

            $sql = "SELECT l.*, COALESCE(u.full_name, 'Admin') as nama_uploader 
                    FROM library l 
                    LEFT JOIN users u ON l.uploader_id = u.id";
            $params = [];
            $where = [];

            if (!empty($search)) {
                $where[] = "(l.judul LIKE :s1 OR l.penulis LIKE :s2 OR l.deskripsi LIKE :s3)";
                $params['s1'] = "%$search%";
                $params['s2'] = "%$search%";
                $params['s3'] = "%$search%";
            }

            if (!empty($kategori) && strtolower($kategori) !== 'semua') {
                $where[] = "LOWER(l.kategori) = :kat";
                $params['kat'] = strtolower($kategori);
            }

            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }

            $sql .= " ORDER BY l.id DESC LIMIT 100";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $books = $stmt->fetchAll();

            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = BASE_URL;

            foreach ($books as &$b) {
                if (!empty($b['file_path'])) {
                    if (strpos($b['file_path'], 'http') !== 0) {
                        $b['file_url'] = $baseUrl . ltrim($b['file_path'], '/');
                    } else {
                        $b['file_url'] = $b['file_path'];
                    }
                } else {
                    $b['file_url'] = $baseUrl . 'assets/docs/panduan.pdf';
                }
                $b['rating'] = 4.8;
                $b['views_count'] = intval($b['view_count'] ?? $b['views_count'] ?? 0);
                $b['is_featured'] = intval($b['is_featured'] ?? (intval($b['view_count'] ?? 0) >= 800 ? 1 : 0));
            }

            $this->jsonResponse(true, 'Daftar Buku Digital / Perpustakaan', $books);
        } catch (\Throwable $e) {
            $this->jsonResponse(false, 'Gagal memuat perpustakaan dari database: ' . $e->getMessage(), [], 500);
        }
    }

    public function game($endpoint = 'list') {
        require_once ROOT_PATH . 'models/GameModel.php';
        require_once ROOT_PATH . 'models/SiswaModel.php';
        require_once ROOT_PATH . 'models/GuruModel.php';
        $gameModel = new GameModel();

        $input = $this->getPostInput();
        $userId = intval($_GET['user_id'] ?? $_POST['user_id'] ?? $input['user_id'] ?? 0);
        $gameId = intval($_GET['id'] ?? $_GET['game_id'] ?? $_POST['game_id'] ?? $input['game_id'] ?? 0);

        $guruId = null;
        $kelasId = null;
        $siswaId = null;

        if ($userId > 0) {
            $siswaModel = new SiswaModel();
            $guruModel = new GuruModel();
            $siswa = $siswaModel->getByUserId($userId);
            if ($siswa) {
                $siswaId = $siswa['id'];
                $kelasId = $siswa['kelas_id'] ?? null;
            } else {
                $guru = $guruModel->getByUserId($userId);
                if ($guru) {
                    $guruId = $guru['id'];
                }
            }
        }

        switch (strtolower($endpoint)) {
            case 'play':
            case 'detail':
                if ($gameId <= 0) {
                    $this->jsonResponse(false, 'ID Game Edukasi tidak valid', null, 400);
                }
                $gameDetail = $gameModel->getGameDetail($gameId);
                if (!$gameDetail) {
                    $this->jsonResponse(false, 'Game Edukasi tidak ditemukan', null, 404);
                }
                $soalList = $gameModel->getGameSoal($gameId);
                $leaderboard = $gameModel->getLeaderboard($gameId);
                
                $this->jsonResponse(true, 'Data Game & Soal Edukasi', [
                    'game' => $gameDetail,
                    'soal' => $soalList,
                    'leaderboard' => $leaderboard
                ]);
                break;

            case 'submit_score':
            case 'save_score':
                if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                    $this->jsonResponse(false, 'Method Request harus POST', null, 405);
                }
                if ($gameId <= 0 || ($siswaId <= 0 && $userId <= 0)) {
                    $this->jsonResponse(false, 'Parameter ID Game / Siswa tidak lengkap', null, 400);
                }

                if (!$siswaId && $userId > 0) {
                    $siswaModel = new SiswaModel();
                    $siswa = $siswaModel->getByUserId($userId);
                    if ($siswa) $siswaId = $siswa['id'];
                }

                if ($siswaId <= 0) {
                    $this->jsonResponse(false, 'Hanya akun Siswa yang dapat menyimpan skor game', null, 400);
                }

                $skorAkhir = intval($input['skor_akhir'] ?? $_POST['skor_akhir'] ?? 0);
                $maxCombo = intval($input['max_combo'] ?? $_POST['max_combo'] ?? 0);
                $totalBenar = intval($input['total_benar'] ?? $_POST['total_benar'] ?? 0);
                $totalSoal = intval($input['total_soal'] ?? $_POST['total_soal'] ?? 0);
                $waktuSelesai = intval($input['waktu_selesai'] ?? $_POST['waktu_selesai'] ?? 0);

                $gameDetail = $gameModel->getGameDetail($gameId);
                $kkm = intval($gameDetail['kkm'] ?? 75);
                $statusLulus = ($skorAkhir >= $kkm) ? 'lulus' : 'tidak_lulus';

                $ok = $gameModel->saveScore($gameId, $siswaId, $skorAkhir, $maxCombo, $totalBenar, $totalSoal, $waktuSelesai, $statusLulus);
                $leaderboard = $gameModel->getLeaderboard($gameId);

                if ($ok) {
                    $this->jsonResponse(true, 'Skor game berhasil disimpan!', [
                        'game_id' => $gameId,
                        'skor_akhir' => $skorAkhir,
                        'max_combo' => $maxCombo,
                        'total_benar' => $totalBenar,
                        'total_soal' => $totalSoal,
                        'waktu_selesai' => $waktuSelesai,
                        'status_lulus' => $statusLulus,
                        'kkm' => $kkm,
                        'leaderboard' => $leaderboard
                    ]);
                } else {
                    $this->jsonResponse(false, 'Gagal menyimpan skor game', null, 500);
                }
                break;

            case 'leaderboard':
                if ($gameId <= 0) {
                    $this->jsonResponse(false, 'ID Game Edukasi tidak valid', null, 400);
                }
                $leaderboard = $gameModel->getLeaderboard($gameId);
                $this->jsonResponse(true, 'Papan Peringkat Game', $leaderboard);
                break;

            case 'list':
            default:
                $games = $gameModel->getAllGames($guruId, $kelasId);
                if ($siswaId) {
                    foreach ($games as &$g) {
                        $bestScore = $gameModel->getStudentBestScore($g['id'], $siswaId);
                        $g['my_best_score'] = $bestScore ? intval($bestScore['skor_akhir']) : null;
                        $g['my_status'] = $bestScore ? $bestScore['status_lulus'] : null;
                    }
                    unset($g);
                }
                $this->jsonResponse(true, 'Daftar Game Edukasi Interaktif', $games);
                break;
        }
    }
}
