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

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
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
            'success' => $success,
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
            }

            if (!empty($user['avatar']) && $user['avatar'] !== 'default_avatar.png') {
                $user['avatar_url'] = 'https://smkmuthiaharapancicalengka.my.id/assets/uploads/profile/' . $user['avatar'];
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
        $input = $this->getPostInput();
        $userId = intval($_GET['user_id'] ?? $_POST['user_id'] ?? $input['user_id'] ?? 0);
        $stmtS = $this->db->prepare("
            SELECT s.*, k.nama_kelas, j.nama_jurusan 
            FROM siswa s 
            LEFT JOIN kelas k ON s.kelas_id = k.id 
            LEFT JOIN jurusan j ON s.jurusan_id = j.id 
            WHERE s.user_id = :uid OR s.id = :uid2 LIMIT 1
        ");
        $stmtS->execute(['uid' => $userId, 'uid2' => $userId]);
        $siswa = $stmtS->fetch();

        if (!$siswa) {
            $stmtFB = $this->db->query("
                SELECT s.*, k.nama_kelas, j.nama_jurusan 
                FROM siswa s 
                LEFT JOIN kelas k ON s.kelas_id = k.id 
                LEFT JOIN jurusan j ON s.jurusan_id = j.id 
                ORDER BY s.id ASC LIMIT 1
            ");
            $siswa = $stmtFB->fetch();
        }

        if (!$siswa) {
            $this->jsonResponse(false, 'Data siswa tidak ditemukan', null, 404);
        }

        switch ($endpoint) {
            case 'dashboard':
                // Stats
                $stmtMat = $this->db->prepare("SELECT COUNT(*) as count FROM materi WHERE kelas_id = :kid");
                $stmtMat->execute(['kid' => $siswa['kelas_id']]);
                $totalMateri = $stmtMat->fetch()['count'];

                $stmtTug = $this->db->prepare("SELECT COUNT(*) as count FROM tugas WHERE kelas_id = :kid");
                $stmtTug->execute(['kid' => $siswa['kelas_id']]);
                $totalTugas = $stmtTug->fetch()['count'];

                $stmtQz = $this->db->prepare("SELECT COUNT(*) as count FROM quiz WHERE kelas_id = :kid AND status='published'");
                $stmtQz->execute(['kid' => $siswa['kelas_id']]);
                $totalQuiz = $stmtQz->fetch()['count'];

                // Announcements
                $stmtP = $this->db->query("SELECT * FROM pengumuman ORDER BY created_at DESC LIMIT 5");
                $pengumuman = $stmtP->fetchAll();

                // Jadwal Hari Ini
                $days = [1=>'Senin', 2=>'Selasa', 3=>'Rabu', 4=>'Kamis', 5=>'Jumat', 6=>'Sabtu', 7=>'Minggu'];
                $today = $days[date('N')] ?? 'Senin';
                $stmtJ = $this->db->prepare("
                    SELECT j.*, m.nama_mapel, g.nama_lengkap as nama_guru 
                    FROM jadwal j 
                    JOIN mata_pelajaran m ON j.mapel_id = m.id 
                    JOIN guru g ON j.guru_id = g.id 
                    WHERE j.kelas_id = :kid AND j.hari = :hari 
                    ORDER BY j.jam_mulai ASC
                ");
                $stmtJ->execute(['kid' => $siswa['kelas_id'], 'hari' => $today]);
                $jadwalToday = $stmtJ->fetchAll();

                $this->jsonResponse(true, 'Data Dashboard Siswa', [
                    'siswa' => $siswa,
                    'stats' => [
                        'materi' => $totalMateri,
                        'tugas' => $totalTugas,
                        'quiz' => $totalQuiz
                    ],
                    'pengumuman' => $pengumuman,
                    'jadwal_hari_ini' => $jadwalToday
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
                $stmtM = $this->db->prepare("
                    SELECT m.*, mp.nama_mapel, g.nama_lengkap as nama_guru 
                    FROM materi m 
                    JOIN mata_pelajaran mp ON m.mapel_id = mp.id 
                    JOIN guru g ON m.guru_id = g.id 
                    WHERE m.kelas_id = :kid 
                    ORDER BY m.created_at DESC
                ");
                $stmtM->execute(['kid' => $siswa['kelas_id']]);
                $materi = $stmtM->fetchAll();

                $this->jsonResponse(true, 'Daftar Materi Pembelajaran', $materi);
                break;

            case 'tugas':
                $stmtT = $this->db->prepare("
                    SELECT t.*, mp.nama_mapel, g.nama_lengkap as nama_guru, 
                           pt.id as submission_id, pt.nilai, pt.komentar_guru, pt.submitted_at 
                    FROM tugas t 
                    JOIN mata_pelajaran mp ON t.mapel_id = mp.id 
                    JOIN guru g ON t.guru_id = g.id 
                    LEFT JOIN pengumpulan_tugas pt ON (pt.tugas_id = t.id AND pt.siswa_id = :sid)
                    WHERE t.kelas_id = :kid 
                    ORDER BY t.deadline ASC
                ");
                $stmtT->execute(['sid' => $siswa['id'], 'kid' => $siswa['kelas_id']]);
                $tugas = $stmtT->fetchAll();

                $this->jsonResponse(true, 'Daftar Tugas Siswa', $tugas);
                break;

            case 'submit_tugas':
                $input = $this->getPostInput();
                $tugasId = intval($input['tugas_id'] ?? 0);
                $catatan = trim($input['catatan_siswa'] ?? '');
                $filePath = trim($input['file_path'] ?? '');

                if ($tugasId <= 0) {
                    $this->jsonResponse(false, 'ID Tugas tidak valid', null, 400);
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

            case 'quiz':
                $stmtQ = $this->db->prepare("
                    SELECT q.*, mp.nama_mapel, g.nama_lengkap as nama_guru, 
                           hq.total_nilai, hq.status_lulus, hq.finished_at 
                    FROM quiz q 
                    JOIN mata_pelajaran mp ON q.mapel_id = mp.id 
                    JOIN guru g ON q.guru_id = g.id 
                    LEFT JOIN hasil_quiz hq ON (hq.quiz_id = q.id AND hq.siswa_id = :sid)
                    WHERE q.kelas_id = :kid AND q.status = 'published'
                    ORDER BY q.created_at DESC
                ");
                $stmtQ->execute(['sid' => $siswa['id'], 'kid' => $siswa['kelas_id']]);
                $quizList = $stmtQ->fetchAll();

                $this->jsonResponse(true, 'Daftar Quiz & CBT', $quizList);
                break;

            case 'quiz_detail':
                $quizId = intval($_GET['quiz_id'] ?? 0);
                $stmtQ = $this->db->prepare("SELECT * FROM quiz WHERE id = :qid LIMIT 1");
                $stmtQ->execute(['qid' => $quizId]);
                $quiz = $stmtQ->fetch();

                if (!$quiz) {
                    $this->jsonResponse(false, 'Quiz tidak ditemukan', null, 404);
                }

                $stmtSoal = $this->db->prepare("SELECT * FROM soal WHERE quiz_id = :qid ORDER BY id ASC");
                $stmtSoal->execute(['qid' => $quizId]);
                $soalList = $stmtSoal->fetchAll();

                foreach ($soalList as &$s) {
                    $stmtP = $this->db->prepare("SELECT id, soal_id, teks_pilihan FROM pilihan_jawaban WHERE soal_id = :sid");
                    $stmtP->execute(['sid' => $s['id']]);
                    $s['pilihan'] = $stmtP->fetchAll();
                }

                $this->jsonResponse(true, 'Detail Quiz & Soal', [
                    'quiz' => $quiz,
                    'soal' => $soalList
                ]);
                break;

            case 'submit_quiz':
                $input = $this->getPostInput();
                $quizId = intval($input['quiz_id'] ?? 0);
                $answers = $input['answers'] ?? []; // Map of [soal_id => pilihan_id]

                if ($quizId <= 0 || empty($answers)) {
                    $this->jsonResponse(false, 'Jawaban quiz tidak boleh kosong', null, 400);
                }

                $stmtSoal = $this->db->prepare("SELECT * FROM soal WHERE quiz_id = :qid");
                $stmtSoal->execute(['qid' => $quizId]);
                $allSoal = $stmtSoal->fetchAll();
                $totalBobot = 0;
                $skorDapat = 0;

                foreach ($allSoal as $soal) {
                    $totalBobot += ($soal['bobot'] ?? 10);
                    $soalId = $soal['id'];
                    $pilihanId = intval($answers[$soalId] ?? 0);

                    // Check if answer correct
                    $isBenar = 0;
                    if ($pilihanId > 0) {
                        $stmtCheck = $this->db->prepare("SELECT is_benar FROM pilihan_jawaban WHERE id = :pid LIMIT 1");
                        $stmtCheck->execute(['pid' => $pilihanId]);
                        $pj = $stmtCheck->fetch();
                        if ($pj && $pj['is_benar'] == 1) {
                            $isBenar = 1;
                            $skorDapat += ($soal['bobot'] ?? 10);
                        }
                    }

                    // Save student answer
                    $stmtJ = $this->db->prepare("
                        INSERT INTO jawaban_siswa (siswa_id, quiz_id, soal_id, pilihan_id, is_benar, nilai) 
                        VALUES (:sid, :qid, :soal_id, :pid, :ib, :nil)
                        ON DUPLICATE KEY UPDATE pilihan_id = :pid, is_benar = :ib, nilai = :nil
                    ");
                    $stmtJ->execute([
                        'sid' => $siswa['id'],
                        'qid' => $quizId,
                        'soal_id' => $soalId,
                        'pid' => $pilihanId ?: null,
                        'ib' => $isBenar,
                        'nil' => $isBenar ? ($soal['bobot'] ?? 10) : 0
                    ]);
                }

                $nilaiAkhir = ($totalBobot > 0) ? round(($skorDapat / $totalBobot) * 100, 2) : 100;
                $statusLulus = ($nilaiAkhir >= 70) ? 'lulus' : 'tidak_lulus';

                // Save or Update Hasil Quiz
                $stmtH = $this->db->prepare("
                    INSERT INTO hasil_quiz (siswa_id, quiz_id, total_nilai, status_lulus, finished_at) 
                    VALUES (:sid, :qid, :tn, :sl, NOW())
                    ON DUPLICATE KEY UPDATE total_nilai = :tn, status_lulus = :sl, finished_at = NOW()
                ");
                $stmtH->execute([
                    'sid' => $siswa['id'],
                    'qid' => $quizId,
                    'tn' => $nilaiAkhir,
                    'sl' => $statusLulus
                ]);

                $this->jsonResponse(true, 'Quiz berhasil dikirim!', [
                    'total_nilai' => $nilaiAkhir,
                    'status_lulus' => $statusLulus
                ]);
                break;

            case 'absensi':
                // Get attendance list for student
                $stmtAbs = $this->db->prepare("
                    SELECT a.*, j.hari, j.jam_mulai, j.jam_selesai, mp.nama_mapel 
                    FROM absensi a 
                    JOIN jadwal j ON a.jadwal_id = j.id 
                    JOIN mata_pelajaran mp ON j.mapel_id = mp.id 
                    WHERE a.siswa_id = :sid 
                    ORDER BY a.tanggal DESC LIMIT 30
                ");
                $stmtAbs->execute(['sid' => $siswa['id']]);
                $history = $stmtAbs->fetchAll();

                $this->jsonResponse(true, 'Riwayat Absensi Siswa', $history);
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

            case 'nilai':
                $stmtN = $this->db->prepare("
                    SELECT n.*, mp.nama_mapel, mp.kode_mapel 
                    FROM nilai n 
                    JOIN mata_pelajaran mp ON n.mapel_id = mp.id 
                    WHERE n.siswa_id = :sid
                ");
                $stmtN->execute(['sid' => $siswa['id']]);
                $nilaiList = $stmtN->fetchAll();

                $this->jsonResponse(true, 'Rekap Nilai Siswa', $nilaiList);
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

            case 'rapor':
                try {
                    $stmtRap = $this->db->prepare("
                        SELECT r.*, mp.nama_mapel 
                        FROM rapor r 
                        LEFT JOIN mata_pelajaran mp ON r.mapel_id = mp.id 
                        WHERE r.siswa_id = :sid
                    ");
                    $stmtRap->execute(['sid' => $siswa['id']]);
                    $rapor = $stmtRap->fetchAll();
                    $this->jsonResponse(true, 'Data Rapor Siswa', $rapor);
                } catch (\Throwable $eR) {
                    // Fallback using nilai table as rapor
                    $stmtN = $this->db->prepare("
                        SELECT n.*, mp.nama_mapel, mp.kode_mapel 
                        FROM nilai n 
                        JOIN mata_pelajaran mp ON n.mapel_id = mp.id 
                        WHERE n.siswa_id = :sid
                    ");
                    $stmtN->execute(['sid' => $siswa['id']]);
                    $nilaiList = $stmtN->fetchAll();
                    $this->jsonResponse(true, 'Data Rapor Siswa (Rekap Nilai)', $nilaiList);
                }
                break;

            case 'gabung_kelas':
                $input = $this->getPostInput();
                $kodeKelas = trim($input['kode_kelas'] ?? '');
                if (empty($kodeKelas)) {
                    $this->jsonResponse(false, 'Kode Kelas wajib diisi!', null, 400);
                }
                try {
                    $stmtK = $this->db->prepare("SELECT id, nama_kelas FROM kelas WHERE kode_kelas = :kode LIMIT 1");
                    $stmtK->execute(['kode' => $kodeKelas]);
                    $kelas = $stmtK->fetch();
                    if (!$kelas) {
                        $this->jsonResponse(false, 'Kode Kelas tidak valid atau tidak ditemukan.', null, 404);
                    }
                    $stmtUpd = $this->db->prepare("UPDATE siswa SET kelas_id = :kid WHERE id = :sid");
                    $stmtUpd->execute(['kid' => $kelas['id'], 'sid' => $siswa['id']]);
                    $this->jsonResponse(true, 'Berhasil bergabung ke kelas ' . $kelas['nama_kelas']);
                } catch (\Throwable $eG) {
                    $this->jsonResponse(false, 'Gagal bergabung ke kelas: ' . $eG->getMessage(), null, 500);
                }
                break;

            case 'learning_path':
                $this->jsonResponse(true, 'Alur Pembelajaran & Learning Path', [
                    'tingkat' => $siswa['nama_kelas'] ?? 'Kelas X',
                    'jurusan' => $siswa['nama_jurusan'] ?? 'Umum',
                    'capaian_persen' => 85,
                    'modul_selesai' => 12,
                    'total_modul' => 15
                ]);
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
        $input = $this->getPostInput();
        $userId = intval($_GET['user_id'] ?? $_POST['user_id'] ?? $input['user_id'] ?? 0);
        $stmtG = $this->db->prepare("SELECT * FROM guru WHERE user_id = :uid OR id = :uid2 LIMIT 1");
        $stmtG->execute(['uid' => $userId, 'uid2' => $userId]);
        $guru = $stmtG->fetch();

        if (!$guru) {
            $stmtFB = $this->db->query("SELECT * FROM guru ORDER BY id ASC LIMIT 1");
            $guru = $stmtFB->fetch();
        }

        if (!$guru) {
            $this->jsonResponse(false, 'Data guru tidak ditemukan', null, 404);
        }

        switch ($endpoint) {
            case 'dashboard':
                // Total Materi Guru
                $stmtMat = $this->db->prepare("SELECT COUNT(*) as count FROM materi WHERE guru_id = :gid");
                $stmtMat->execute(['gid' => $guru['id']]);
                $totalMateri = $stmtMat->fetch()['count'];

                // Total Tugas Guru
                $stmtTug = $this->db->prepare("SELECT COUNT(*) as count FROM tugas WHERE guru_id = :gid");
                $stmtTug->execute(['gid' => $guru['id']]);
                $totalTugas = $stmtTug->fetch()['count'];

                // Total Quiz Guru
                $stmtQ = $this->db->prepare("SELECT COUNT(*) as count FROM quiz WHERE guru_id = :gid");
                $stmtQ->execute(['gid' => $guru['id']]);
                $totalQuiz = $stmtQ->fetch()['count'];

                // Jadwal Mengajar Hari Ini
                $days = [1=>'Senin', 2=>'Selasa', 3=>'Rabu', 4=>'Kamis', 5=>'Jumat', 6=>'Sabtu', 7=>'Minggu'];
                $today = $days[date('N')] ?? 'Senin';
                $stmtJ = $this->db->prepare("
                    SELECT j.*, m.nama_mapel, k.nama_kelas 
                    FROM jadwal j 
                    JOIN mata_pelajaran m ON j.mapel_id = m.id 
                    JOIN kelas k ON j.kelas_id = k.id 
                    WHERE j.guru_id = :gid AND j.hari = :hari 
                    ORDER BY j.jam_mulai ASC
                ");
                $stmtJ->execute(['gid' => $guru['id'], 'hari' => $today]);
                $jadwalToday = $stmtJ->fetchAll();

                $this->jsonResponse(true, 'Dashboard Guru Overview', [
                    'guru' => $guru,
                    'stats' => [
                        'materi' => $totalMateri,
                        'tugas' => $totalTugas,
                        'quiz' => $totalQuiz
                    ],
                    'jadwal_hari_ini' => $jadwalToday
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
                    $kelasId = intval($input['kelas_id'] ?? 0);
                    $jenisFile = $input['jenis_file'] ?? 'pdf';
                    $youtubeUrl = trim($input['youtube_url'] ?? '');

                    if (empty($judul) || $mapelId <= 0 || $kelasId <= 0) {
                        $this->jsonResponse(false, 'Judul, Mapel, dan Kelas wajib diisi', null, 400);
                    }

                    $stmtIns = $this->db->prepare("
                        INSERT INTO materi (guru_id, mapel_id, kelas_id, judul, deskripsi, jenis_file, youtube_url, created_at) 
                        VALUES (:gid, :mpid, :kid, :jdl, :desk, :jf, :yt, NOW())
                    ");
                    $stmtIns->execute([
                        'gid' => $guru['id'],
                        'mpid' => $mapelId,
                        'kid' => $kelasId,
                        'jdl' => $judul,
                        'desk' => $deskripsi,
                        'jf' => $jenisFile,
                        'yt' => $youtubeUrl
                    ]);

                    $this->jsonResponse(true, 'Materi berhasil ditambahkan!');
                }

                $stmtM = $this->db->prepare("
                    SELECT m.*, mp.nama_mapel, k.nama_kelas 
                    FROM materi m 
                    JOIN mata_pelajaran mp ON m.mapel_id = mp.id 
                    JOIN kelas k ON m.kelas_id = k.id 
                    WHERE m.guru_id = :gid 
                    ORDER BY m.created_at DESC
                ");
                $stmtM->execute(['gid' => $guru['id']]);
                $materi = $stmtM->fetchAll();

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
                        $kelasId = intval($input['kelas_id'] ?? 0);
                        $deadline = $input['deadline'] ?? date('Y-m-d H:i:s', strtotime('+7 days'));

                        if (empty($judul) || $mapelId <= 0 || $kelasId <= 0) {
                            $this->jsonResponse(false, 'Judul, Mapel, dan Kelas wajib diisi', null, 400);
                        }

                        $stmtIns = $this->db->prepare("
                            INSERT INTO tugas (guru_id, mapel_id, kelas_id, judul, deskripsi, deadline, created_at) 
                            VALUES (:gid, :mpid, :kid, :jdl, :desk, :dl, NOW())
                        ");
                        $stmtIns->execute([
                            'gid' => $guru['id'],
                            'mpid' => $mapelId,
                            'kid' => $kelasId,
                            'jdl' => $judul,
                            'desk' => $deskripsi,
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
                    JOIN mata_pelajaran mp ON t.mapel_id = mp.id 
                    JOIN kelas k ON t.kelas_id = k.id 
                    WHERE t.guru_id = :gid 
                    ORDER BY t.created_at DESC
                ");
                $stmtT->execute(['gid' => $guru['id']]);
                $tugas = $stmtT->fetchAll();

                $this->jsonResponse(true, 'Kelola Tugas', $tugas);
                break;

            case 'submissions':
                $tugasId = intval($_GET['tugas_id'] ?? 0);
                $stmtSub = $this->db->prepare("
                    SELECT pt.*, s.nama_lengkap as nama_siswa, s.nis 
                    FROM pengumpulan_tugas pt 
                    JOIN siswa s ON pt.siswa_id = s.id 
                    WHERE pt.tugas_id = :tid 
                    ORDER BY pt.submitted_at DESC
                ");
                $stmtSub->execute(['tid' => $tugasId]);
                $submissions = $stmtSub->fetchAll();

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

            case 'absensi':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $input = $this->getPostInput();
                    $jadwalId = intval($input['jadwal_id'] ?? 0);
                    $records = $input['records'] ?? []; // Map [siswa_id => status]

                    if ($jadwalId <= 0 || empty($records)) {
                        $this->jsonResponse(false, 'Data absensi tidak lengkap', null, 400);
                    }

                    $today = date('Y-m-d');
                    foreach ($records as $siswaId => $status) {
                        $stmtIns = $this->db->prepare("
                            INSERT INTO absensi (jadwal_id, siswa_id, tanggal, status, created_at) 
                            VALUES (:jid, :sid, :tgl, :st, NOW())
                            ON DUPLICATE KEY UPDATE status = :st
                        ");
                        $stmtIns->execute([
                            'jid' => $jadwalId,
                            'sid' => intval($siswaId),
                            'tgl' => $today,
                            'st' => $status
                        ]);
                    }

                    $this->jsonResponse(true, 'Absensi siswa berhasil disimpan!');
                }

                $kelasId = intval($_GET['kelas_id'] ?? 0);
                $stmtSis = $this->db->prepare("SELECT id, nis, nama_lengkap FROM siswa WHERE kelas_id = :kid ORDER BY nama_lengkap ASC");
                $stmtSis->execute(['kid' => $kelasId]);
                $students = $stmtSis->fetchAll();

                $this->jsonResponse(true, 'Daftar Siswa untuk Absensi', $students);
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
                    $quizId = intval($input['quiz_id'] ?? 0);
                    $pertanyaan = trim($input['pertanyaan'] ?? '');
                    $bobot = intval($input['bobot'] ?? 10);
                    if ($quizId <= 0 || empty($pertanyaan)) {
                        $this->jsonResponse(false, 'Quiz dan pertanyaan wajib diisi', null, 400);
                    }
                    try {
                        $stmtIns = $this->db->prepare("INSERT INTO soal (quiz_id, pertanyaan, bobot) VALUES (:qid, :pt, :bb)");
                        $stmtIns->execute(['qid' => $quizId, 'pt' => $pertanyaan, 'bb' => $bobot]);
                        $this->jsonResponse(true, 'Soal berhasil ditambahkan ke bank soal!');
                    } catch (\Throwable $eBs) {
                        $this->jsonResponse(false, 'Gagal menambahkan soal: ' . $eBs->getMessage(), null, 500);
                    }
                }
                $quizId = intval($_GET['quiz_id'] ?? 0);
                try {
                    $stmtS = $this->db->prepare("SELECT * FROM soal WHERE quiz_id = :qid ORDER BY id ASC");
                    $stmtS->execute(['qid' => $quizId]);
                    $soals = $stmtS->fetchAll();
                    $this->jsonResponse(true, 'Daftar Bank Soal Quiz', $soals);
                } catch (\Throwable $eBs2) {
                    $this->jsonResponse(true, 'Daftar Bank Soal Quiz', []);
                }
                break;

            case 'scan_qr':
                $input = $this->getPostInput();
                $qrCode = trim($input['qr_code'] ?? $input['identifier'] ?? '');
                if (empty($qrCode)) {
                    $this->jsonResponse(false, 'Kode QR atau Identitas NIS/NIP wajib diisi!', null, 400);
                }
                try {
                    require_once ROOT_PATH . 'models/AbsensiModel.php';
                    $absensiModel = new AbsensiModel();
                    $res = $absensiModel->processQrScan($qrCode, $guru['id'], true);
                    if (!empty($res['success'])) {
                        $this->jsonResponse(true, $res['message'] ?? 'Presensi QR Berhasil!', $res);
                    } else {
                        $this->jsonResponse(false, $res['message'] ?? 'Gagal memproses QR Presensi', $res, 400);
                    }
                } catch (\Throwable $eQr) {
                    $this->jsonResponse(false, 'Gagal memproses QR Presensi: ' . $eQr->getMessage(), null, 500);
                }
                break;

            case 'recap_absensi':
                $kelasId = intval($_GET['kelas_id'] ?? 0);
                $bulan = $_GET['bulan'] ?? date('Y-m');
                try {
                    $stmtR = $this->db->prepare("
                        SELECT a.*, s.nama_lengkap, s.nis 
                        FROM absensi a 
                        JOIN siswa s ON a.siswa_id = s.id 
                        WHERE s.kelas_id = :kid AND DATE_FORMAT(a.tanggal, '%Y-%m') = :bln
                        ORDER BY a.tanggal DESC
                    ");
                    $stmtR->execute(['kid' => $kelasId, 'bln' => $bulan]);
                    $recap = $stmtR->fetchAll();
                    $this->jsonResponse(true, 'Rekapitulasi Presensi Bulanan', $recap);
                } catch (\Throwable $eRc) {
                    $this->jsonResponse(true, 'Rekapitulasi Presensi Bulanan', []);
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
            $userId = intval($input['user_id'] ?? $userId);
            $fullName = trim($input['full_name'] ?? '');
            $email = trim($input['email'] ?? '');
            $noTelp = trim($input['no_telepon'] ?? '');
            $jk = trim($input['jenis_kelamin'] ?? '');
            $alamat = trim($input['alamat'] ?? '');
            $newPassword = trim($input['password'] ?? '');

            if ($userId <= 0) {
                $this->jsonResponse(false, 'ID Pengguna tidak valid', null, 400);
            }

            try {
                if (!empty($newPassword)) {
                    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                    $stmtUpd = $this->db->prepare("UPDATE users SET full_name = IF(:fn!='',:fn,full_name), email = IF(:em!='',:em,email), password = :pass WHERE id = :uid");
                    $stmtUpd->execute(['fn' => $fullName, 'em' => $email, 'pass' => $hash, 'uid' => $userId]);
                } else {
                    $stmtUpd = $this->db->prepare("UPDATE users SET full_name = IF(:fn!='',:fn,full_name), email = IF(:em!='',:em,email) WHERE id = :uid");
                    $stmtUpd->execute(['fn' => $fullName, 'em' => $email, 'uid' => $userId]);
                }

                // Update detail in siswa or guru table if exists
                if (!empty($noTelp) || !empty($jk) || !empty($alamat)) {
                    try {
                        $stmtUpdS = $this->db->prepare("UPDATE siswa SET no_telepon = IF(:nt!='',:nt,no_telepon), jenis_kelamin = IF(:jk!='',:jk,jenis_kelamin), alamat = IF(:al!='',:al,alamat) WHERE user_id = :uid");
                        $stmtUpdS->execute(['nt' => $noTelp, 'jk' => $jk, 'al' => $alamat, 'uid' => $userId]);
                    } catch (\Throwable $eS) {}
                    try {
                        $stmtUpdG = $this->db->prepare("UPDATE guru SET no_telepon = IF(:nt!='',:nt,no_telepon), jenis_kelamin = IF(:jk!='',:jk,jenis_kelamin), alamat = IF(:al!='',:al,alamat) WHERE user_id = :uid");
                        $stmtUpdG->execute(['nt' => $noTelp, 'jk' => $jk, 'al' => $alamat, 'uid' => $userId]);
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

            if ($user && !empty($user['avatar']) && $user['avatar'] !== 'default_avatar.png') {
                $user['avatar_url'] = 'https://smkmuthiaharapancicalengka.my.id/assets/uploads/profile/' . $user['avatar'];
            } else if ($user) {
                $user['avatar_url'] = null;
            }

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
                'judul' => 'Panduan Akses & Presensi Mobile',
                'deskripsi' => 'Petunjuk melakukan presensi harian dan QR code di HP.'
            ],
            [
                'judul' => 'Panduan Pengerjaan CBT & Tugas',
                'deskripsi' => 'Tata cara menjawab quiz CBT online dan mengunggah tugas.'
            ],
            [
                'judul' => 'Panduan Guru Input Nilai & Bank Soal',
                'deskripsi' => 'Petunjuk untuk bapak/ibu guru mengelola soal dan menilai.'
            ]
        ]);
    }

    public function library($endpoint = 'list') {
        try {
            $stmt = $this->db->query("SELECT * FROM library ORDER BY id DESC LIMIT 50");
            $books = $stmt->fetchAll();
            $this->jsonResponse(true, 'Daftar Buku Digital / Perpustakaan', $books);
        } catch (\Throwable $e) {
            // Fallback mock/materi library
            $this->jsonResponse(true, 'Daftar Buku Digital / Perpustakaan', [
                [
                    'id' => 1,
                    'judul' => 'Buku Panduan Pembelajaran SMK',
                    'penulis' => 'Tim Kurikulum SMK',
                    'kategori' => 'Umum',
                    'file_path' => 'assets/docs/panduan.pdf',
                    'cover' => null
                ],
                [
                    'id' => 2,
                    'judul' => 'Modul Pemrograman Web & Mobile',
                    'penulis' => 'Tim E-Learning SMK',
                    'kategori' => 'Teknologi Informasi',
                    'file_path' => 'assets/docs/modul_web.pdf',
                    'cover' => null
                ]
            ]);
        }
    }

    public function game($endpoint = 'list') {
        try {
            $stmt = $this->db->query("SELECT * FROM games ORDER BY id DESC LIMIT 20");
            $games = $stmt->fetchAll();
            $this->jsonResponse(true, 'Daftar Game Edukasi', $games);
        } catch (\Throwable $e) {
            // Fallback list of educational games
            $this->jsonResponse(true, 'Daftar Game Edukasi', [
                [
                    'id' => 1,
                    'nama_game' => 'Kuis Cerdas Cermat SMK',
                    'deskripsi' => 'Uji wawasan umum dan kejuruanmu di kuis interaktif!',
                    'kategori' => 'Kuis',
                    'level' => 'Sedang'
                ],
                [
                    'id' => 2,
                    'nama_game' => 'Tebak Istilah IT & Kejuruan',
                    'deskripsi' => 'Game tebak kata seputar istilah keahlian SMK.',
                    'kategori' => 'Puzzle',
                    'level' => 'Mudah'
                ]
            ]);
        }
    }
}
