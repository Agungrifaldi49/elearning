<?php
/**
 * Siswa Controller (Per-Student Dynamic Data Scope & Isolation)
 */
require_once ROOT_PATH . 'helpers/AuthHelper.php';
require_once ROOT_PATH . 'helpers/Security.php';
require_once ROOT_PATH . 'helpers/UploadHelper.php';
require_once ROOT_PATH . 'helpers/FlashHelper.php';
require_once ROOT_PATH . 'models/LearningModel.php';
require_once ROOT_PATH . 'models/ExamModel.php';
require_once ROOT_PATH . 'models/SiswaModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';
require_once ROOT_PATH . 'models/CommunicationModel.php';
require_once ROOT_PATH . 'models/NilaiModel.php';
require_once ROOT_PATH . 'models/CurriculumModel.php';

class SiswaController {

    public function __construct() {
        AuthHelper::requireRole(['Siswa']);
    }

    private function getSiswaInfo() {
        $user = AuthHelper::user();
        if (!$user) return ['id' => 0, 'user_id' => 0, 'nama_lengkap' => 'Siswa', 'kelas_id' => 1, 'jurusan_id' => 1];
        $siswaModel = new SiswaModel();
        $prof = $siswaModel->ensureSiswaProfile($user['id'], $user['full_name']);
        return $prof ?: ['id' => 0, 'user_id' => $user['id'], 'nama_lengkap' => $user['full_name'], 'kelas_id' => 1, 'jurusan_id' => 1];
    }

    public function game() {
        require_once ROOT_PATH . 'controllers/GameController.php';
        $gameCtrl = new GameController();
        $url = $_GET['url'] ?? '';
        $parts = explode('/', rtrim($url, '/'));
        $subAction = $parts[2] ?? ($_GET['action'] ?? 'index');
        if (method_exists($gameCtrl, $subAction)) {
            $gameCtrl->$subAction();
        } else {
            $gameCtrl->index();
        }
    }

    public function gameEdukasi() {
        $this->game();
    }

    public function dashboard() {
        $user = AuthHelper::user();
        $siswaProfile = $this->getSiswaInfo();
        $siswa = $siswaProfile;
        $siswaId = $siswa['id'] ?? 0;
        $kelasId = $siswa['kelas_id'] ?? null;

        $learningModel = new LearningModel();
        $examModel = new ExamModel();
        $academicModel = new AcademicModel();
        $commModel = new CommunicationModel();

        $enrolledList = $academicModel->getSiswaEnrolledMapels($siswaId);
        $enrolledMapels = [];
        foreach ($enrolledList as $em) {
            $enrolledMapels[$em['mapel_id'] . '_' . $em['guru_id']] = true;
            $enrolledMapels[$em['mapel_id']] = true;
        }

        $allMateri = $learningModel->getMateri($kelasId);
        $allTugas = $learningModel->getTugas($kelasId);
        $allQuiz = $examModel->getQuizList($kelasId);

        $materiList = array_values(array_filter($allMateri, function($m) use ($enrolledMapels) {
            if (empty($enrolledMapels)) return true;
            return isset($enrolledMapels[$m['mapel_id'] . '_' . $m['guru_id']]) || isset($enrolledMapels[$m['mapel_id']]);
        }));

        $tugasList = array_values(array_filter($allTugas, function($t) use ($enrolledMapels) {
            if (empty($enrolledMapels)) return true;
            return isset($enrolledMapels[$t['mapel_id'] . '_' . $t['guru_id']]) || isset($enrolledMapels[$t['mapel_id']]);
        }));

        $quizList = array_values(array_filter($allQuiz, function($q) use ($enrolledMapels) {
            if (empty($enrolledMapels)) return true;
            return isset($enrolledMapels[$q['mapel_id'] . '_' . $q['guru_id']]) || isset($enrolledMapels[$q['mapel_id']]);
        }));

        $jadwalList = $academicModel->getJadwal($kelasId);
        $pengumumanList = $commModel->getPengumuman('siswa');

        $nilaiModel = new NilaiModel();
        if (!empty($enrolledList)) {
            foreach ($enrolledList as $em) {
                $nilaiModel->syncSiswaMapelNilai($siswaId, (int)$em['mapel_id']);
            }
        }

        $siswaModel = new SiswaModel();
        $certStats = $siswaModel->getSiswaCertificateRealStats($siswaId);
        $presensiLogs = $siswaModel->getSiswaPresensiLogs($siswaId, 6);
        $activeTa = $academicModel->getActiveTahunAjaran();

        $db = Database::getConnection();
        $stmtChart = $db->prepare("
            SELECT m.id as mapel_id, m.nama_mapel, 
                   ROUND(AVG(COALESCE(pt.nilai, hq.total_nilai)), 1) as avg_nilai
            FROM mata_pelajaran m
            LEFT JOIN tugas t ON t.mapel_id = m.id
            LEFT JOIN pengumpulan_tugas pt ON pt.tugas_id = t.id AND pt.siswa_id = ? AND pt.nilai IS NOT NULL
            LEFT JOIN quiz q ON q.mapel_id = m.id
            LEFT JOIN hasil_quiz hq ON hq.quiz_id = q.id AND hq.siswa_id = ? AND hq.total_nilai IS NOT NULL
            WHERE pt.nilai IS NOT NULL OR hq.total_nilai IS NOT NULL
            GROUP BY m.id, m.nama_mapel
            LIMIT 8
        ");
        $stmtChart->execute([$siswaId, $siswaId]);
        $chartData = $stmtChart->fetchAll();

        if (empty($chartData)) {
            $enrolledNames = [];
            try {
                $stmtEnrolled = $db->prepare("
                    SELECT DISTINCT m.nama_mapel
                    FROM siswa_mapel_enrollment sm
                    JOIN mata_pelajaran m ON sm.mapel_id = m.id
                    WHERE sm.siswa_id = ?
                    LIMIT 6
                ");
                $stmtEnrolled->execute([$siswaId]);
                $enrolledNames = $stmtEnrolled->fetchAll(PDO::FETCH_COLUMN);
            } catch (\Throwable $e) {}

            if (empty($enrolledNames)) {
                $enrolledNames = array_unique(array_column($tugasList, 'nama_mapel'));
            }
            if (empty($enrolledNames)) {
                $enrolledNames = ['Pemrograman Web', 'Basis Data', 'Matematika', 'Bahasa Inggris', 'Informatika'];
            }

            $chartData = [];
            foreach ($enrolledNames as $eName) {
                $chartData[] = [
                    'nama_mapel' => $eName,
                    'avg_nilai' => 0
                ];
            }
        }

        require_once ROOT_PATH . 'views/siswa/dashboard.php';
    }

    public function materi() {
        $siswa = $this->getSiswaInfo();
        $siswaId = $siswa['id'];
        $kelasId = $siswa['kelas_id'] ?? null;

        $learningModel = new LearningModel();
        $academicModel = new AcademicModel();

        $enrolledList = $academicModel->getSiswaEnrolledMapels($siswaId);
        $enrolledMapels = [];
        foreach ($enrolledList as $em) {
            $enrolledMapels[$em['mapel_id'] . '_' . $em['guru_id']] = true;
            $enrolledMapels[$em['mapel_id']] = true;
        }

        $allMateri = $learningModel->getMateri($kelasId);
        $allVideos = $learningModel->getVideos($kelasId);

        $materiList = array_values(array_filter($allMateri, function($m) use ($enrolledMapels) {
            if (empty($enrolledMapels)) return true;
            return isset($enrolledMapels[$m['mapel_id'] . '_' . $m['guru_id']]) || isset($enrolledMapels[$m['mapel_id']]);
        }));

        $videoList = array_values(array_filter($allVideos, function($v) use ($enrolledMapels) {
            return isset($enrolledMapels[$v['mapel_id'] . '_' . $v['guru_id']]) || isset($enrolledMapels[$v['mapel_id']]);
        }));

        // Ambil daftar unik mata pelajaran untuk filter dropdown
        $mapelList = [];
        foreach (array_merge($allMateri, $allVideos) as $item) {
            if (!empty($item['mapel_id']) && !isset($mapelList[$item['mapel_id']])) {
                $mapelList[$item['mapel_id']] = [
                    'id' => (int)$item['mapel_id'],
                    'nama_mapel' => $item['nama_mapel'] ?? 'Mata Pelajaran'
                ];
            }
        }
        $classMapels = $academicModel->getMapelByKelas($kelasId);
        foreach ($classMapels as $cm) {
            if (!isset($mapelList[$cm['id']])) {
                $mapelList[$cm['id']] = [
                    'id' => (int)$cm['id'],
                    'nama_mapel' => $cm['nama_mapel']
                ];
            }
        }
        usort($mapelList, function($a, $b) {
            return strcasecmp($a['nama_mapel'], $b['nama_mapel']);
        });

        $selectedMapelId = isset($_GET['mapel_id']) ? (int)$_GET['mapel_id'] : 0;
        $searchKeyword = trim($_GET['q'] ?? '');

        // Filter server-side jika parameter query tersedia
        if ($selectedMapelId > 0) {
            $materiList = array_values(array_filter($materiList, function($m) use ($selectedMapelId) {
                return (int)$m['mapel_id'] === $selectedMapelId;
            }));
            $videoList = array_values(array_filter($videoList, function($v) use ($selectedMapelId) {
                return (int)$v['mapel_id'] === $selectedMapelId;
            }));
        }

        if (!empty($searchKeyword)) {
            $kw = mb_strtolower($searchKeyword);
            $materiList = array_values(array_filter($materiList, function($m) use ($kw) {
                return strpos(mb_strtolower($m['judul'] ?? ''), $kw) !== false
                    || strpos(mb_strtolower($m['deskripsi'] ?? ''), $kw) !== false
                    || strpos(mb_strtolower($m['nama_guru'] ?? ''), $kw) !== false
                    || strpos(mb_strtolower($m['nama_mapel'] ?? ''), $kw) !== false;
            }));
            $videoList = array_values(array_filter($videoList, function($v) use ($kw) {
                return strpos(mb_strtolower($v['judul'] ?? ''), $kw) !== false
                    || strpos(mb_strtolower($v['deskripsi'] ?? ''), $kw) !== false
                    || strpos(mb_strtolower($v['nama_guru'] ?? ''), $kw) !== false
                    || strpos(mb_strtolower($v['nama_mapel'] ?? ''), $kw) !== false;
            }));
        }

        require_once ROOT_PATH . 'views/siswa/materi.php';
    }

    /**
     * Mobile-First Reader Screen: Membaca materi/modul pembelajaran secara optimal di HP & Desktop
     */
    public function bacaMateri() {
        $siswa = $this->getSiswaInfo();
        $siswaId = (int)($siswa['id'] ?? 0);
        $kelasId = (int)($siswa['kelas_id'] ?? 0);

        $materiId = (int)($_GET['id'] ?? 0);
        if ($materiId <= 0) {
            FlashHelper::setError('Materi pembelajaran tidak ditemukan.');
            header('Location: ' . BASE_URL . 'index.php?url=siswa/materi');
            exit();
        }

        $learningModel = new LearningModel();
        $academicModel = new AcademicModel();

        $materi = $learningModel->getMateriDetailById($materiId);
        if (!$materi) {
            FlashHelper::setError('Materi tidak ditemukan atau telah dihapus oleh pengajar.');
            header('Location: ' . BASE_URL . 'index.php?url=siswa/materi');
            exit();
        }

        // Verifikasi hak akses materi (berdasarkan kelas target & enrollment mapel)
        $targetKelasIds = !empty($materi['kelas_ids']) ? array_map('intval', explode(',', $materi['kelas_ids'])) : [(int)$materi['kelas_id']];
        $isClassTarget = empty($targetKelasIds) || in_array(0, $targetKelasIds) || in_array($kelasId, $targetKelasIds);

        $enrolledList = $academicModel->getSiswaEnrolledMapels($siswaId);
        $enrolledMapels = [];
        foreach ($enrolledList as $em) {
            $enrolledMapels[$em['mapel_id'] . '_' . $em['guru_id']] = true;
            $enrolledMapels[$em['mapel_id']] = true;
        }

        $isEnrolled = empty($enrolledMapels) || isset($enrolledMapels[$materi['mapel_id'] . '_' . $materi['guru_id']]) || isset($enrolledMapels[$materi['mapel_id']]);
        if (!$isEnrolled) {
            FlashHelper::setError('Anda belum terdaftar pada mata pelajaran ini. Silakan masukkan Enrollment Key terlebih dahulu.');
            header('Location: ' . BASE_URL . 'index.php?url=siswa/gabungKelas');
            exit();
        }

        // Cek materi lain pada mapel yang sama untuk navigasi cepat
        $allMateriKelas = $learningModel->getMateri($kelasId);
        $materiTerkait = [];
        foreach ($allMateriKelas as $mItem) {
            if ((int)$mItem['mapel_id'] === (int)$materi['mapel_id'] && (int)$mItem['id'] !== $materiId) {
                $materiTerkait[] = $mItem;
            }
        }

        require_once ROOT_PATH . 'views/siswa/baca_materi.php';
    }

    public function tugas() {
        $siswa = $this->getSiswaInfo();
        $siswaId = $siswa['id'];
        $kelasId = $siswa['kelas_id'] ?? null;

        $learningModel = new LearningModel();
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rawInput = file_get_contents('php://input');
            $jsonInput = json_decode($rawInput, true) ?: [];
            $inputData = array_merge($_POST, $jsonInput);

            $isJsonOrApi = !empty($jsonInput) || 
                           (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                           (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

            if (!$isJsonOrApi && !Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=siswa/tugas');
                exit();
            }

            $action = $inputData['action'] ?? $_GET['action'] ?? '';
            $tugasId = intval($inputData['tugas_id'] ?? $_GET['tugas_id'] ?? 0);

            if ($action === 'request_tugas_susulan' || $action === 'request_susulan' || strpos($_SERVER['REQUEST_URI'] ?? '', 'request_tugas_susulan') !== false) {
                $catatan = Security::sanitize($inputData['catatan_susulan'] ?? $inputData['catatan'] ?? 'Pengajuan Susulan Pengumpulan Tugas');
                $learningModel->requestTugasSusulan($tugasId, $siswaId, $catatan);

                $commModel = new CommunicationModel();
                $uName = AuthHelper::user()['full_name'] ?? 'Siswa';
                $commModel->sendNotificationToTeacherByTugas(
                    $tugasId, 
                    '📩 Permintaan Izin Susulan Tugas', 
                    "Siswa {$uName} mengajukan permohonan izin susulan pengumpulan Tugas.", 
                    'index.php?url=guru/tugas'
                );

                if ($isJsonOrApi) {
                    echo json_encode([
                        'success' => true,
                        'status' => true,
                        'message' => 'Permintaan Izin Pengumpulan Tugas Susulan telah dikirimkan ke Guru Pengampu.'
                    ]);
                    exit();
                }

                FlashHelper::setSuccess('Permintaan Izin Pengumpulan Tugas Susulan telah dikirimkan ke Guru Pengampu. Silakan tunggu konfirmasi persetujuan.');
                header('Location: ' . BASE_URL . 'index.php?url=siswa/tugas');
                exit();
            }

            $tugas_id = (int)$_POST['tugas_id'];

            // Strict Deadline & Susulan Check
            $accessCheck = $learningModel->canSiswaSubmitTugas($tugas_id, $siswaId);
            if (!$accessCheck['access']) {
                FlashHelper::setError('Akses Pengumpulan Terkunci! Waktu pengumpulan tugas ini telah melewati deadline. Silakan ajukan izin Susulan ke Guru Pengampu.');
                header('Location: ' . BASE_URL . 'index.php?url=siswa/tugas');
                exit();
            }

            $catatan = Security::sanitize($_POST['catatan_siswa']);
            $filePath = null;

            // Strict Enrollment & Class Isolation Check before submitting task
            $db = Database::getConnection();
            $stmtT = $db->prepare("SELECT mapel_id, guru_id, kelas_id, kelas_ids FROM tugas WHERE id = ?");
            $stmtT->execute([$tugas_id]);
            $tInfo = $stmtT->fetch();

            if ($tInfo) {
                $targetKelasIds = [];
                if (!empty($tInfo['kelas_ids'])) {
                    $targetKelasIds = array_map('intval', explode(',', $tInfo['kelas_ids']));
                }
                if (!empty($tInfo['kelas_id'])) {
                    $targetKelasIds[] = (int)$tInfo['kelas_id'];
                }
                $targetKelasIds = array_values(array_unique(array_filter($targetKelasIds, function($id) { return $id > 0; })));

                $hasAccess = empty($targetKelasIds) || in_array((int)$kelasId, $targetKelasIds);

                if (!$hasAccess) {
                    FlashHelper::setError('Akses Terkunci! Penugasan ini diperuntukkan untuk kelas/jurusan lain.');
                    header('Location: ' . BASE_URL . 'index.php?url=siswa/tugas');
                    exit();
                }

                $isEnrolled = $academicModel->isSiswaEnrolledInMapel($siswaId, $tInfo['mapel_id'], $tInfo['guru_id']);
                if (!$isEnrolled) {
                    FlashHelper::setError('Akses Terkunci! Anda harus mendaftar terlebih dahulu pada Mata Pelajaran ini menggunakan Kode Akses (Key) dari Guru.');
                    header('Location: ' . BASE_URL . 'index.php?url=siswa/gabungKelas');
                    exit();
                }
            }

            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $filePath = UploadHelper::upload($_FILES['file'], 'tugas');
            }

            $learningModel->submitTugas($tugas_id, $siswaId, $filePath, $catatan);

            $commModel = new CommunicationModel();
            $uName = AuthHelper::user()['full_name'] ?? 'Siswa';
            $commModel->sendNotificationToTeacherByTugas(
                $tugas_id, 
                '📥 Jawaban Tugas Baru Dikirim', 
                "Siswa {$uName} telah mengunggah jawaban tugas.", 
                'index.php?url=guru/tugas'
            );

            FlashHelper::setSuccess('Tugas berhasil dikirim ke guru.');

            header('Location: ' . BASE_URL . 'index.php?url=siswa/tugas');
            exit();
        }

        $allTugas = $learningModel->getTugas($kelasId);
        $enrolledList = $academicModel->getSiswaEnrolledMapels($siswaId);

        $db = Database::getConnection();
        $stmtSub = $db->prepare("SELECT * FROM pengumpulan_tugas WHERE siswa_id = ?");
        $stmtSub->execute([$siswaId]);
        $submittedList = $stmtSub->fetchAll();
        $submittedMap = [];
        foreach ($submittedList as $sub) {
            $submittedMap[$sub['tugas_id']] = $sub;
        }

        $enrolledMapels = [];
        foreach ($enrolledList as $em) {
            $enrolledMapels[$em['mapel_id'] . '_' . $em['guru_id']] = true;
            $enrolledMapels[$em['mapel_id']] = true;
        }

        $tugasList = array_values(array_filter($allTugas, function($t) use ($enrolledMapels) {
            return isset($enrolledMapels[$t['mapel_id'] . '_' . $t['guru_id']]) || isset($enrolledMapels[$t['mapel_id']]);
        }));

        require_once ROOT_PATH . 'views/siswa/tugas.php';
    }

    public function quiz() {
        $siswa = $this->getSiswaInfo();
        $siswaId = $siswa['id'];
        $kelasId = $siswa['kelas_id'] ?? null;

        $examModel = new ExamModel();
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_susulan') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=siswa/quiz');
                exit();
            }

            $qId = (int)$_POST['quiz_id'];
            $catatan = Security::sanitize($_POST['catatan_susulan'] ?? 'Pengajuan Ujian Susulan');
            $examModel->requestSusulan($qId, $siswaId, $catatan);

            $commModel = new CommunicationModel();
            $uName = AuthHelper::user()['full_name'] ?? 'Siswa';
            $commModel->sendNotificationToTeacherByQuiz(
                $qId, 
                '📩 Permintaan Izin Ujian Susulan', 
                "Siswa {$uName} mengajukan permohonan izin Ujian Susulan Kuis.", 
                'index.php?url=guru/quiz'
            );

            FlashHelper::setSuccess('Permintaan Ujian Susulan telah dikirimkan ke Guru Pengampu. Silakan tunggu konfirmasi persetujuan.');
            header('Location: ' . BASE_URL . 'index.php?url=siswa/quiz');
            exit();
        }

        // Keepalive / Heartbeat Ping to prevent session timeout during exam
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['keepalive', 'ping'])) {
            $_SESSION['last_activity'] = time();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'success',
                'csrf_token' => Security::csrfToken(),
                'time' => time()
            ]);
            exit();
        }

        // Real-Time Answer Autosave to MySQL database
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'autosave') {
            $qId = (int)($_POST['quiz_id'] ?? 0);
            $soalId = (int)($_POST['soal_id'] ?? 0);
            $pilihanId = !empty($_POST['pilihan_id']) ? (int)$_POST['pilihan_id'] : null;
            $teksEssay = isset($_POST['essay']) ? Security::sanitize($_POST['essay']) : null;

            if ($qId <= 0 || $soalId <= 0) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
                exit();
            }

            // Access check
            $accessCheck = $examModel->canSiswaAccessQuiz($qId, $siswaId);
            if (!$accessCheck['access']) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'Akses kuis tidak diizinkan']);
                exit();
            }

            $examModel->submitAnswer($siswaId, $qId, $soalId, $pilihanId, $teksEssay);
            $_SESSION['last_activity'] = time();

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'success',
                'message' => 'Tersimpan otomatis',
                'saved_at' => date('H:i:s'),
                'soal_id' => $soalId,
                'csrf_token' => Security::csrfToken()
            ]);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'record_violation') {
            $qId = (int)$_POST['quiz_id'];
            $resViolation = $examModel->recordPelanggaran($siswaId, $qId);

            if ($resViolation['is_disqualified']) {
                $commModel = new CommunicationModel();
                $uName = AuthHelper::user()['full_name'] ?? 'Siswa';
                $commModel->sendNotificationToTeacherByQuiz(
                    $qId, 
                    '🚨 Diskualifikasi Ujian Online (UTS/UAS/CBT)', 
                    "Siswa {$uName} didiskualifikasi dari Ujian karena 2x melanggar aturan (berpindah tab/keluar fullscreen).", 
                    'index.php?url=guru/quiz'
                );
            }

            echo json_encode(['status' => 'success', 'pelanggaran_count' => $resViolation['pelanggaran_count'], 'is_disqualified' => $resViolation['is_disqualified']]);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_access_key') {
            $quiz_id = (int)$_POST['quiz_id'];
            $inputKey = trim($_POST['access_key'] ?? '');
            if ($examModel->verifyAccessKey($quiz_id, $inputKey)) {
                $_SESSION['quiz_access_key_' . $quiz_id] = true;
                FlashHelper::setSuccess('Kunci Akses (Token) Ujian Benar! Selamat mengerjakan.');
                header('Location: ' . BASE_URL . 'index.php?url=siswa/quiz&id=' . $quiz_id);
                exit();
            } else {
                FlashHelper::setError('Kunci Akses (Token) UTS/UAS Salah! Silakan tanyakan Token Kunci ke Guru atau Admin.');
                header('Location: ' . BASE_URL . 'index.php?url=siswa/quiz');
                exit();
            }
        }

        $quiz_id = (int)($_GET['id'] ?? 0);

        if ($quiz_id > 0) {
            $quizInfo = $examModel->getQuizById($quiz_id);
            if (!$quizInfo) {
                FlashHelper::setError('Ujian / Kuis tidak ditemukan.');
                header('Location: ' . BASE_URL . 'index.php?url=siswa/quiz');
                exit();
            }

            // Access Key Check for UTS & UAS
            if (in_array($quizInfo['kategori'], ['uts', 'uas']) && !empty($quizInfo['access_key'])) {
                if (empty($_SESSION['quiz_access_key_' . $quiz_id])) {
                    FlashHelper::setError('Ujian ini (' . strtoupper($quizInfo['kategori']) . ') Memerlukan Kunci Akses (Token). Masukkan Token Kunci Ujian terlebih dahulu!');
                    header('Location: ' . BASE_URL . 'index.php?url=siswa/quiz');
                    exit();
                }
            }

            // Strict Deadline, Susulan & Violation Check
            $accessCheck = $examModel->canSiswaAccessQuiz($quiz_id, $siswaId);
            if (!$accessCheck['access']) {
                $reason = 'Batas waktu (deadline) pengerjaan kuis ini telah berakhir';
                if (($accessCheck['status'] ?? '') === 'diskualifikasi' || ($accessCheck['status'] ?? '') === 'didiskualifikasi') {
                    $reason = 'Anda telah DIDISKUALIFIKASI dari ujian ini karena 2x melanggar aturan (berpindah tab / keluar fullscreen)';
                }
                FlashHelper::setError("Akses Ujian Terkunci! {$reason}. Silakan ajukan permohonan izin Ujian Susulan / Buka Kunci ke Guru atau Admin.");
                header('Location: ' . BASE_URL . 'index.php?url=siswa/quiz');
                exit();
            }

            // Start & register active attempt session in DB
            $examModel->startQuizAttempt($quiz_id, $siswaId);

            // Strict Enrollment & Class Isolation Check before taking quiz
            $db = Database::getConnection();
            $stmtQ = $db->prepare("SELECT mapel_id, guru_id, kelas_id, kelas_ids FROM quiz WHERE id = ?");
            $stmtQ->execute([$quiz_id]);
            $qInfo = $stmtQ->fetch();

            if ($qInfo) {
                $targetIds = !empty($qInfo['kelas_ids']) ? array_map('intval', explode(',', $qInfo['kelas_ids'])) : [(int)$qInfo['kelas_id']];
                if (!in_array((int)$kelasId, $targetIds) && (int)$qInfo['kelas_id'] !== 0) {
                    FlashHelper::setError('Akses Terkunci! Kuis ini diperuntukkan untuk kelas/jurusan lain.');
                    header('Location: ' . BASE_URL . 'index.php?url=siswa/quiz');
                    exit();
                }

                $isEnrolled = $academicModel->isSiswaEnrolledInMapel($siswaId, $qInfo['mapel_id'], $qInfo['guru_id']);
                if (!$isEnrolled) {
                    FlashHelper::setError('Akses Terkunci! Anda wajib terdaftar terlebih dahulu di Mata Pelajaran ini dengan Kode Akses (Key) dari Guru.');
                    header('Location: ' . BASE_URL . 'index.php?url=siswa/gabungKelas');
                    exit();
                }
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                          (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                          (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1');

                if (!Security::verifyCsrfToken()) {
                    if ($isAjax) {
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode(['status' => 'error', 'message' => 'Token CSRF tidak valid atau sesi berakhir. Silakan refresh halaman.']);
                        exit();
                    }
                    FlashHelper::setError('CSRF Token Invalid');
                    header('Location: ' . BASE_URL . 'index.php?url=siswa/quiz');
                    exit();
                }

                $jawaban = $_POST['jawaban'] ?? [];
                $essay = $_POST['essay'] ?? [];

                foreach ($jawaban as $soalId => $pilihanId) {
                    if (!empty($pilihanId)) {
                        $examModel->submitAnswer($siswaId, $quiz_id, (int)$soalId, (int)$pilihanId, null);
                    }
                }

                foreach ($essay as $soalId => $teksEssay) {
                    $examModel->submitAnswer($siswaId, $quiz_id, (int)$soalId, null, Security::sanitize($teksEssay));
                }

                $totalScore = $examModel->finishQuiz($siswaId, $quiz_id);

                // Clear question ordering session for fresh future attempts
                unset($_SESSION['quiz_order_' . $quiz_id . '_' . $siswaId]);

                $commModel = new CommunicationModel();
                $uName = AuthHelper::user()['full_name'] ?? 'Siswa';
                $commModel->sendNotificationToTeacherByQuiz(
                    $quiz_id, 
                    '📊 Siswa Menyelesaikan Kuis', 
                    "Siswa {$uName} telah menyelesaikan pengerjaan Kuis. Nilai: {$totalScore}", 
                    'index.php?url=guru/quiz'
                );

                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'status' => 'success',
                        'message' => "Quiz Selesai! Nilai Anda: {$totalScore}",
                        'score' => $totalScore,
                        'redirect' => BASE_URL . 'index.php?url=siswa/nilai'
                    ]);
                    exit();
                }

                FlashHelper::setSuccess("Quiz Selesai! Nilai Anda: {$totalScore}");
                header('Location: ' . BASE_URL . 'index.php?url=siswa/nilai');
                exit();
            }

            $quizInfo = $examModel->getQuizById($quiz_id);
            $savedAnswers = $examModel->getSavedAnswers($quiz_id, $siswaId);

            // Deterministic Question Ordering per student exam session
            $sessionOrderKey = 'quiz_order_' . $quiz_id . '_' . $siswaId;
            $isRandomSoal = !empty($quizInfo['random_soal']) && strtoupper($quizInfo['random_soal']) === 'Y';
            $isRandomJawaban = !empty($quizInfo['random_jawaban']) && strtoupper($quizInfo['random_jawaban']) === 'Y';

            if (!empty($_SESSION[$sessionOrderKey]) && is_array($_SESSION[$sessionOrderKey])) {
                $soalList = $examModel->getSoalByQuizOrdered($quiz_id, $_SESSION[$sessionOrderKey], $isRandomJawaban);
            } else {
                $soalList = $examModel->getSoalByQuiz($quiz_id, $isRandomSoal);
                if (!empty($soalList)) {
                    $_SESSION[$sessionOrderKey] = array_column($soalList, 'id');
                }
            }

            require_once ROOT_PATH . 'views/siswa/kerjakan_quiz.php';
            exit();
        }

        $allQuiz = $examModel->getQuizList($kelasId);
        $cbtList = $examModel->getUjianCBT($kelasId);

        $db = Database::getConnection();
        $stmtCompleted = $db->prepare("
            SELECT quiz_id, total_nilai, nilai_tertinggi, 
                   (SELECT hqh.status_lulus FROM hasil_quiz_history hqh WHERE hqh.siswa_id = hasil_quiz.siswa_id AND hqh.quiz_id = hasil_quiz.quiz_id ORDER BY hqh.id DESC LIMIT 1) as status_lulus,
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

        $enrolledList = $academicModel->getSiswaEnrolledMapels($siswaId);
        $enrolledMapels = [];
        foreach ($enrolledList as $em) {
            $enrolledMapels[$em['mapel_id'] . '_' . $em['guru_id']] = true;
            $enrolledMapels[$em['mapel_id']] = true;
        }

        $quizList = array_values(array_filter($allQuiz, function($q) use ($enrolledMapels) {
            if (empty($enrolledMapels)) return true;
            return isset($enrolledMapels[$q['mapel_id'] . '_' . $q['guru_id']]) || isset($enrolledMapels[$q['mapel_id']]);
        }));

        require_once ROOT_PATH . 'views/siswa/quiz.php';
    }

    public function nilai() {
        $siswa = $this->getSiswaInfo();
        $siswaId = $siswa['id'];

        $nilaiModel = new NilaiModel();
        $academicModel = new AcademicModel();

        // Auto-sync real-time scores for all enrolled mapels for student
        $enrolledList = $academicModel->getSiswaEnrolledMapels($siswaId);
        if (!empty($enrolledList)) {
            foreach ($enrolledList as $em) {
                $nilaiModel->syncSiswaMapelNilai($siswaId, (int)$em['mapel_id']);
            }
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT hq.*, q.judul as nama_quiz, q.durasi_menit, m.nama_mapel, g.nama_lengkap as nama_guru 
            FROM hasil_quiz hq
            JOIN quiz q ON hq.quiz_id = q.id
            JOIN mata_pelajaran m ON q.mapel_id = m.id
            JOIN guru g ON q.guru_id = g.id
            WHERE hq.siswa_id = ?
            ORDER BY hq.id DESC
        ");
        $stmt->execute([$siswaId]);
        $hasilQuizList = $stmt->fetchAll();

        $learningModel = new LearningModel();
        $hasilTugasList = $learningModel->getPengumpulanBySiswa($siswaId);

        require_once ROOT_PATH . 'views/siswa/nilai.php';
    }

    public function reviewQuiz() {
        $siswa = $this->getSiswaInfo();
        $siswaId = $siswa['id'];
        $quizId = (int)($_GET['id'] ?? 0);

        if ($quizId <= 0) {
            FlashHelper::setError('Quiz tidak ditemukan.');
            header('Location: ' . BASE_URL . 'index.php?url=siswa/nilai');
            exit();
        }

        $examModel = new ExamModel();
        $reviewData = $examModel->getReviewQuiz($quizId, $siswaId);

        if (!$reviewData || !$reviewData['hasil']) {
            FlashHelper::setError('Anda belum mengerjakan kuis ini.');
            header('Location: ' . BASE_URL . 'index.php?url=siswa/quiz');
            exit();
        }

        $quizInfo = $reviewData['quiz'];
        $soalList = $reviewData['soal'];
        $hasilQuiz = $reviewData['hasil'];

        require_once ROOT_PATH . 'views/siswa/review_quiz.php';
    }

    public function kartuPelajar() {
        $user = AuthHelper::user();
        $siswa = $this->getSiswaInfo();
        require_once ROOT_PATH . 'views/siswa/kartu_pelajar.php';
    }

    public function rapor() {
        $siswa = $this->getSiswaInfo();
        $siswaId = $siswa['id'];

        $nilaiModel = new NilaiModel();
        $academicModel = new AcademicModel();

        // Auto-sync real-time scores for all enrolled mapels for student
        $enrolledList = $academicModel->getSiswaEnrolledMapels($siswaId);
        if (!empty($enrolledList)) {
            foreach ($enrolledList as $em) {
                $nilaiModel->syncSiswaMapelNilai($siswaId, (int)$em['mapel_id']);
            }
        }

        require_once ROOT_PATH . 'models/CurriculumModel.php';
        $currModel = new CurriculumModel();
        $activeTa = $academicModel->getActiveTahunAjaran();
        $taId = $activeTa['id'] ?? 4;
        $activeSemester = $activeTa['semester'] ?? 'Ganjil';
        $raporData = $currModel->getRaporSiswa($siswaId, $taId, $activeSemester);

        // Ambil data nilai terkini setelah diselaraskan oleh CurriculumModel
        $nilaiList = $nilaiModel->getNilaiBySiswa($siswaId);

        // Ambil konfigurasi bobot penilaian kurikulum rombel siswa
        $kelasId = (int)($siswa['kelas_id'] ?? 0);
        $kurInfo = $currModel->getActiveKurikulumForRombel($kelasId, $taId);
        $kurId = (int)($kurInfo['kurikulum_id'] ?? 1);
        $bobotKomponen = $nilaiModel->getBobotKomponenByKurikulum($kurId);
        $komponenList = $currModel->getKomponenPenilaian($kurId);

        require_once ROOT_PATH . 'models/SettingsModel.php';
        $settingsModel = new SettingsModel();
        $settings = $settingsModel->getAll();

        // Ambil data Wali Kelas Rombel Siswa dari relasi kelas -> wali_kelas_id -> guru
        $waliKelas = null;
        if ($kelasId > 0) {
            $db = Database::getConnection();
            $stmtWali = $db->prepare("
                SELECT g.nama_lengkap, g.nip, g.no_telepon 
                FROM kelas k 
                JOIN guru g ON k.wali_kelas_id = g.id 
                WHERE k.id = ?
            ");
            $stmtWali->execute([$kelasId]);
            $waliKelas = $stmtWali->fetch(PDO::FETCH_ASSOC);
        }

        // Ambil nama & NIP/NUPTK Kepala Sekolah resmi
        $kepsekNama = !empty($settings['kepala_sekolah']) ? $settings['kepala_sekolah'] : 'H. ASEP SAEPULLOH, S. Ag';
        $kepsekNip  = !empty($settings['nip_kepala_sekolah']) ? $settings['nip_kepala_sekolah'] : (!empty($settings['nip_kepsek']) ? $settings['nip_kepsek'] : '');
        if (empty($kepsekNip)) {
            try {
                $db = Database::getConnection();
                $cleanName = trim(explode(',', $kepsekNama)[0]);
                $stmtKep = $db->prepare("SELECT nip FROM guru WHERE nama_lengkap LIKE ? OR nip = 'G202608503' OR user_id = 4 ORDER BY id DESC LIMIT 1");
                $stmtKep->execute(['%' . $cleanName . '%']);
                $kepsekNip = $stmtKep->fetchColumn() ?: 'G202608503';
            } catch (\Throwable $eKep) {
                $kepsekNip = 'G202608503';
            }
        }

        // Ambil Data Rekap Absensi (Sakit, Izin, Alpa) dan Riwayat Presensi Siswa
        $absensiRekap = [
            'total' => 0,
            'hadir' => 0,
            'izin'  => 0,
            'sakit' => 0,
            'alpa'  => 0
        ];
        $historyAbsen = [];
        try {
            $db = Database::getConnection();
            $stmtAtt = $db->prepare("
                SELECT 
                    COUNT(*) as total_absensi,
                    COUNT(CASE WHEN LOWER(TRIM(status)) = 'hadir' THEN 1 END) as total_hadir,
                    COUNT(CASE WHEN LOWER(TRIM(status)) IN ('izin', 'ijin') THEN 1 END) as total_izin,
                    COUNT(CASE WHEN LOWER(TRIM(status)) = 'sakit' THEN 1 END) as total_sakit,
                    COUNT(CASE WHEN LOWER(TRIM(status)) IN ('alpa', 'alpha', 'tanpa keterangan') THEN 1 END) as total_alpa
                FROM absensi 
                WHERE siswa_id = ?
            ");
            $stmtAtt->execute([$siswaId]);
            $attRow = $stmtAtt->fetch(PDO::FETCH_ASSOC);
            if ($attRow) {
                $absensiRekap['total'] = (int)($attRow['total_absensi'] ?? 0);
                $absensiRekap['hadir'] = (int)($attRow['total_hadir'] ?? 0);
                $absensiRekap['izin']  = (int)($attRow['total_izin'] ?? 0);
                $absensiRekap['sakit'] = (int)($attRow['total_sakit'] ?? 0);
                $absensiRekap['alpa']  = (int)($attRow['total_alpa'] ?? 0);
            }

            // Ambil detail riwayat / log presensi siswa (history absen)
            $stmtHist = $db->prepare("
                SELECT a.id, a.tanggal, a.status, a.keterangan, a.waktu_masuk, a.waktu_hadir,
                       COALESCE(m.nama_mapel, 'Presensi Harian / KBM') as nama_mapel,
                       COALESCE(g.nama_lengkap, 'Guru Pengampu / Wali') as nama_guru
                FROM absensi a
                LEFT JOIN jadwal j ON a.jadwal_id = j.id
                LEFT JOIN mata_pelajaran m ON j.mapel_id = m.id
                LEFT JOIN guru g ON COALESCE(a.guru_id, j.guru_id) = g.id
                WHERE a.siswa_id = ?
                ORDER BY a.tanggal DESC, a.id DESC
                LIMIT 50
            ");
            $stmtHist->execute([$siswaId]);
            $historyAbsen = $stmtHist->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $eAtt) {
            // fallback jika koneksi bermasalah
        }

        // Ambil Data Ekstrakurikuler yang Diikuti Siswa & Nilai Deskripsi untuk E-Rapor
        $ekskulList = [];
        try {
            require_once ROOT_PATH . 'models/EkstrakurikulerModel.php';
            $ekskulModel = new EkstrakurikulerModel();
            $ekskulList = $ekskulModel->getEkskulBySiswa($siswaId, $taId, $activeSemester);
        } catch (\Throwable $eEks) {
            $ekskulList = [];
        }

        require_once ROOT_PATH . 'views/siswa/rapor.php';
    }

    public function sertifikat() {
        $siswa = $this->getSiswaInfo();
        $siswaModel = new SiswaModel();
        $academicModel = new AcademicModel();
        $nilaiModel = new NilaiModel();

        if ($siswa && !empty($siswa['id'])) {
            $enrolledList = $academicModel->getSiswaEnrolledMapels($siswa['id']);
            if (!empty($enrolledList)) {
                foreach ($enrolledList as $em) {
                    $nilaiModel->syncSiswaMapelNilai($siswa['id'], (int)$em['mapel_id']);
                }
            }
        }

        $certStats = $siswa ? $siswaModel->getSiswaCertificateRealStats($siswa['id']) : [
            'predikat' => 'Belum Ada Data',
            'presensi_log' => 'Belum Ada Data',
            'evaluasi_lms' => 'Belum Ada Nilai'
        ];

        require_once ROOT_PATH . 'views/siswa/sertifikat.php';
    }

    public function learningPath() {
        $siswa = $this->getSiswaInfo();
        $siswaId = $siswa['id'];
        $kelasId = $siswa['kelas_id'] ?? null;

        $academicModel = new AcademicModel();
        $learningModel = new LearningModel();
        $examModel = new ExamModel();

        $enrolledList = $academicModel->getSiswaEnrolledMapels($siswaId);
        $enrolledMapels = [];
        foreach ($enrolledList as $em) {
            $enrolledMapels[$em['mapel_id'] . '_' . $em['guru_id']] = true;
            $enrolledMapels[$em['mapel_id']] = true;
        }

        $myMapelList = $enrolledList;
        $selectedMapelId = isset($_GET['mapel_id']) && $_GET['mapel_id'] !== '' ? (int)$_GET['mapel_id'] : ($myMapelList[0]['mapel_id'] ?? ($myMapelList[0]['id'] ?? null));

        $allMateri = $learningModel->getMateri($kelasId);
        $materiList = [];
        foreach ($allMateri as $m) {
            if ($selectedMapelId && $m['mapel_id'] == $selectedMapelId) {
                $materiList[] = $m;
            }
        }

        $allTugas = $learningModel->getTugas($kelasId);
        $tugasList = [];
        foreach ($allTugas as $t) {
            if ($selectedMapelId && $t['mapel_id'] == $selectedMapelId) {
                $tugasList[] = $t;
            }
        }

        $allQuiz = $examModel->getQuizList($kelasId);
        $quizList = [];
        foreach ($allQuiz as $q) {
            if ($selectedMapelId && ($q['mapel_id'] ?? null) == $selectedMapelId) {
                $quizList[] = $q;
            }
        }

        $selectedMapelInfo = null;
        foreach ($myMapelList as $m) {
            $mId = $m['mapel_id'] ?? ($m['id'] ?? null);
            if ($mId == $selectedMapelId) {
                $selectedMapelInfo = $m;
                break;
            }
        }

        require_once ROOT_PATH . 'views/siswa/learning_path.php';
    }

    public function profil() {
        $user = AuthHelper::user();
        $siswa = $this->getSiswaInfo();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=siswa/profil');
                exit();
            }

            $fullName = Security::sanitize($_POST['full_name'] ?? '');
            $email = Security::sanitize($_POST['email'] ?? '');
            $noTelp = Security::sanitize($_POST['no_telepon'] ?? '');
            $alamat = Security::sanitize($_POST['alamat'] ?? '');
            $jk = $_POST['jenis_kelamin'] ?? ($siswa['jenis_kelamin'] ?? 'L');
            $password = $_POST['password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            if (!empty($password) && strlen($password) < 6) {
                FlashHelper::setError('Password baru minimal 6 karakter!');
                header('Location: ' . BASE_URL . 'index.php?url=siswa/profil');
                exit();
            }

            if (!empty($password) && $password !== $confirmPass) {
                FlashHelper::setError('Konfirmasi password baru tidak cocok!');
                header('Location: ' . BASE_URL . 'index.php?url=siswa/profil');
                exit();
            }

            $foto = (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) ? $_FILES['foto_profil'] : null;
            $croppedBase64 = $_POST['cropped_avatar_base64'] ?? '';

            require_once ROOT_PATH . 'models/UserModel.php';
            $userModel = new UserModel();
            $res = $userModel->updateProfileFull($user['id'], 'siswa', [
                'full_name' => $fullName,
                'email' => $email,
                'no_telepon' => $noTelp,
                'alamat' => $alamat,
                'jenis_kelamin' => $jk,
                'password' => $password,
                'cropped_base64' => $croppedBase64
            ], $foto);

            if ($res['status']) {
                FlashHelper::setSuccess('Profil dan foto akun Siswa Anda berhasil diperbarui!');
            } else {
                FlashHelper::setError($res['message'] ?? 'Gagal memperbarui profil.');
            }

            header('Location: ' . BASE_URL . 'index.php?url=siswa/profil');
            exit();
        }

        require_once ROOT_PATH . 'views/siswa/profil.php';
    }

    public function gabungKelas() {
        $siswa = $this->getSiswaInfo();
        $siswaId = $siswa['id'];
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=siswa/gabungKelas');
                exit();
            }

            $action = $_POST['action'] ?? '';

            if ($action === 'enroll_mapel') {
                $keyInput = $_POST['key_mapel'] ?? '';
                $res = $academicModel->enrollSiswaByMapelKey($siswaId, $keyInput);
                if ($res['status']) {
                    FlashHelper::setSuccess($res['message']);
                } else {
                    FlashHelper::setError($res['message']);
                }
            } else {
                $kodeKelas = $_POST['kode_kelas'] ?? '';
                $kelasIdInput = (int)($_POST['kelas_id'] ?? 0);

                if (!empty($kodeKelas)) {
                    $res = $academicModel->joinKelasByCode($siswaId, $kodeKelas);
                } elseif ($kelasIdInput > 0) {
                    $res = $academicModel->joinKelasById($siswaId, $kelasIdInput);
                } else {
                    $res = ['status' => false, 'message' => 'Silakan masukkan Kode Akses atau pilih pilihan yang tersedia.'];
                }

                if ($res['status']) {
                    FlashHelper::setSuccess($res['message']);
                } else {
                    FlashHelper::setError($res['message']);
                }
            }

            header('Location: ' . BASE_URL . 'index.php?url=siswa/gabungKelas');
            exit();
        }

        $kelasList = $academicModel->getKelas();
        $mapelKeys = $academicModel->getMapelEnrollmentKeys();
        $enrolledList = $academicModel->getSiswaEnrolledMapels($siswaId);

        $enrolledMapelGuruKeys = [];
        foreach ($enrolledList as $em) {
            $eKId = !empty($em['kelas_id']) ? (int)$em['kelas_id'] : 0;
            $enrolledMapelGuruKeys[$em['mapel_id'] . '_' . $em['guru_id'] . '_' . $eKId] = true;
            if ($eKId === 0) {
                $enrolledMapelGuruKeys[$em['mapel_id'] . '_' . $em['guru_id'] . '_global'] = true;
            }
        }

        require_once ROOT_PATH . 'views/siswa/gabung_kelas.php';
    }

    public function panduan() {
        $user = AuthHelper::user();
        $siswa = $this->getSiswaInfo();
        require_once ROOT_PATH . 'views/siswa/panduan.php';
    }

    public function liveClass() {
        $siswa = $this->getSiswaInfo();
        $kelasId = $siswa['kelas_id'] ?? null;

        $learningModel = new LearningModel();
        $liveClasses = $learningModel->getLiveClasses(null, $kelasId);

        require_once ROOT_PATH . 'views/siswa/live_class.php';
    }

    public function request_tugas_susulan() {
        $this->tugas();
    }

    public function request_susulan() {
        $this->tugas();
    }

    public function ajukan_tugas_susulan() {
        $this->tugas();
    }

    public function pembayaran() {
        $user = AuthHelper::user();
        $siswa = $this->getSiswaInfo();
        $siswaId = $siswa['id'];

        require_once ROOT_PATH . 'models/PembayaranModel.php';
        $pembayaranModel = new PembayaranModel();

        $bills = $pembayaranModel->getSiswaBills($siswaId);
        $summary = $pembayaranModel->getSiswaPaymentSummary($siswaId);
        $riwayat = $pembayaranModel->getSiswaRiwayatPembayaran($siswaId);
        $rekeningConfig = $pembayaranModel->getRekeningConfig();

        $unpaidBills = array_values(array_filter($bills, function($b) {
            return $b['status'] !== 'lunas';
        }));
        $paidBills = array_values(array_filter($bills, function($b) {
            return $b['status'] === 'lunas';
        }));

        require_once ROOT_PATH . 'views/siswa/pembayaran.php';
    }

    public function cetakSlip() {
        $siswa = $this->getSiswaInfo();
        $siswaId = $siswa['id'];
        $riwayatId = (int)($_GET['id'] ?? 0);

        require_once ROOT_PATH . 'models/PembayaranModel.php';
        $pembayaranModel = new PembayaranModel();
        $slip = $pembayaranModel->getSlipPembayaran($riwayatId, $siswaId);

        if (!$slip) {
            FlashHelper::setError('Slip bukti pembayaran tidak ditemukan.');
            header('Location: ' . BASE_URL . 'index.php?url=siswa/pembayaran');
            exit();
        }

        require_once ROOT_PATH . 'views/siswa/slip_pembayaran.php';
    }

    /**
     * Halaman Ekstrakurikuler Siswa (Daftar & Ikuti Ekskul)
     */
    public function ekstrakurikuler() {
        $siswa = $this->getSiswaInfo();
        $siswaId = $siswa['id'];

        require_once ROOT_PATH . 'models/EkstrakurikulerModel.php';
        require_once ROOT_PATH . 'models/AcademicModel.php';
        $ekskulModel = new EkstrakurikulerModel();
        $academicModel = new AcademicModel();

        $activeTa = $academicModel->getActiveTahunAjaran();
        $taId = $activeTa['id'] ?? 4;
        $activeSemester = $activeTa['semester'] ?? 'Ganjil';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('Token keamanan CSRF tidak valid.');
                header('Location: ' . BASE_URL . 'index.php?url=siswa/ekstrakurikuler');
                exit();
            }

            $action = $_POST['action'] ?? '';
            $ekskulId = (int)($_POST['ekskul_id'] ?? 0);

            if ($action === 'join_ekskul' && $ekskulId > 0) {
                $joined = $ekskulModel->joinEkskul($siswaId, $ekskulId, $taId, $activeSemester);
                if ($joined) {
                    FlashHelper::setSuccess('Selamat! Anda telah berhasil mengikuti kegiatan ekstrakurikuler ini.');
                } else {
                    FlashHelper::setError('Gagal bergabung ke dalam ekstrakurikuler.');
                }
                header('Location: ' . BASE_URL . 'index.php?url=siswa/ekstrakurikuler');
                exit();
            }

            if ($action === 'leave_ekskul' && $ekskulId > 0) {
                $left = $ekskulModel->leaveEkskul($siswaId, $ekskulId);
                if ($left) {
                    FlashHelper::setSuccess('Anda telah membatalkan keikutsertaan pada ekstrakurikuler ini.');
                } else {
                    FlashHelper::setError('Gagal membatalkan keikutsertaan.');
                }
                header('Location: ' . BASE_URL . 'index.php?url=siswa/ekstrakurikuler');
                exit();
            }
        }

        // Ambil seluruh ekskul aktif
        $allEkskul = $ekskulModel->getAllEkskul(true);
        // Ambil data ekskul yang sedang diikuti siswa
        $myEkskul = $ekskulModel->getEkskulBySiswa($siswaId, $taId, $activeSemester);
        $enrolledIds = array_column($myEkskul, 'ekskul_id');

        require_once ROOT_PATH . 'views/siswa/ekstrakurikuler.php';
    }
}
