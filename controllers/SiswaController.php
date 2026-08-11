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

class SiswaController {

    public function __construct() {
        AuthHelper::requireRole(['Siswa']);
    }

    private function getSiswaInfo() {
        $user = AuthHelper::user();
        $siswaModel = new SiswaModel();
        return $siswaModel->ensureSiswaProfile($user['id'], $user['full_name']);
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
        $siswaModel = new SiswaModel();
        $siswaProfile = $siswaModel->ensureSiswaProfile($user['id'], $user['full_name']);
        $siswa = $siswaProfile;
        $siswaId = $siswa['id'];
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
            return isset($enrolledMapels[$m['mapel_id'] . '_' . $m['guru_id']]) || isset($enrolledMapels[$m['mapel_id']]);
        }));

        $tugasList = array_values(array_filter($allTugas, function($t) use ($enrolledMapels) {
            return isset($enrolledMapels[$t['mapel_id'] . '_' . $t['guru_id']]) || isset($enrolledMapels[$t['mapel_id']]);
        }));

        $quizList = array_values(array_filter($allQuiz, function($q) use ($enrolledMapels) {
            return isset($enrolledMapels[$q['mapel_id'] . '_' . $q['guru_id']]) || isset($enrolledMapels[$q['mapel_id']]);
        }));

        $jadwalList = $academicModel->getJadwal($kelasId);
        $pengumumanList = $commModel->getPengumuman('siswa');

        $certStats = $siswaModel->getSiswaCertificateRealStats($siswaId);
        $activeTa = $academicModel->getActiveTahunAjaran();

        $db = Database::getConnection();
        $stmtChart = $db->prepare("
            SELECT m.nama_mapel, ROUND(AVG(pt.nilai), 1) as avg_nilai
            FROM pengumpulan_tugas pt
            JOIN tugas t ON pt.tugas_id = t.id
            JOIN mata_pelajaran m ON t.mapel_id = m.id
            WHERE pt.siswa_id = ? AND pt.nilai IS NOT NULL
            GROUP BY m.id, m.nama_mapel
            LIMIT 6
        ");
        $stmtChart->execute([$siswaId]);
        $chartData = $stmtChart->fetchAll();

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
            return isset($enrolledMapels[$m['mapel_id'] . '_' . $m['guru_id']]) || isset($enrolledMapels[$m['mapel_id']]);
        }));

        $videoList = array_values(array_filter($allVideos, function($v) use ($enrolledMapels) {
            return isset($enrolledMapels[$v['mapel_id'] . '_' . $v['guru_id']]) || isset($enrolledMapels[$v['mapel_id']]);
        }));

        require_once ROOT_PATH . 'views/siswa/materi.php';
    }

    public function tugas() {
        $siswa = $this->getSiswaInfo();
        $siswaId = $siswa['id'];
        $kelasId = $siswa['kelas_id'] ?? null;

        $learningModel = new LearningModel();
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=siswa/tugas');
                exit();
            }

            $action = $_POST['action'] ?? '';
            if ($action === 'request_tugas_susulan') {
                $tugasId = (int)$_POST['tugas_id'];
                $catatan = Security::sanitize($_POST['catatan_susulan'] ?? 'Pengajuan Susulan Pengumpulan Tugas');
                $learningModel->requestTugasSusulan($tugasId, $siswaId, $catatan);

                $commModel = new CommunicationModel();
                $uName = AuthHelper::user()['full_name'] ?? 'Siswa';
                $commModel->sendNotificationToTeacherByTugas(
                    $tugasId, 
                    '📩 Permintaan Izin Susulan Tugas', 
                    "Siswa {$uName} mengajukan permohonan izin susulan pengumpulan Tugas.", 
                    'index.php?url=guru/tugas'
                );

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
            $stmtT = $db->prepare("SELECT mapel_id, guru_id, kelas_id FROM tugas WHERE id = ?");
            $stmtT->execute([$tugas_id]);
            $tInfo = $stmtT->fetch();

            if ($tInfo) {
                if ((int)$tInfo['kelas_id'] !== (int)$kelasId) {
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'record_violation') {
            $qId = (int)$_POST['quiz_id'];
            $resViolation = $examModel->recordPelanggaran($siswaId, $qId);

            if ($resViolation['is_disqualified']) {
                $examModel->recordViolation($qId, $siswaId);
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

            // Strict Enrollment & Class Isolation Check before taking quiz
            $db = Database::getConnection();
            $stmtQ = $db->prepare("SELECT mapel_id, guru_id, kelas_id FROM quiz WHERE id = ?");
            $stmtQ->execute([$quiz_id]);
            $qInfo = $stmtQ->fetch();

            if ($qInfo) {
                if ((int)$qInfo['kelas_id'] !== (int)$kelasId) {
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
                if (!Security::verifyCsrfToken()) {
                    FlashHelper::setError('CSRF Token Invalid');
                    header('Location: ' . BASE_URL . 'index.php?url=siswa/quiz');
                    exit();
                }

                $jawaban = $_POST['jawaban'] ?? [];
                $essay = $_POST['essay'] ?? [];

                foreach ($jawaban as $soalId => $pilihanId) {
                    $examModel->submitAnswer($siswaId, $quiz_id, $soalId, $pilihanId, null);
                }

                foreach ($essay as $soalId => $teksEssay) {
                    $examModel->submitAnswer($siswaId, $quiz_id, $soalId, null, Security::sanitize($teksEssay));
                }

                $totalScore = $examModel->finishQuiz($siswaId, $quiz_id);

                $commModel = new CommunicationModel();
                $uName = AuthHelper::user()['full_name'] ?? 'Siswa';
                $commModel->sendNotificationToTeacherByQuiz(
                    $quiz_id, 
                    '📊 Siswa Menyelesaikan Kuis', 
                    "Siswa {$uName} telah menyelesaikan pengerjaan Kuis. Nilai: {$totalScore}", 
                    'index.php?url=guru/quiz'
                );

                FlashHelper::setSuccess("Quiz Selesai! Nilai Anda: {$totalScore}");

                header('Location: ' . BASE_URL . 'index.php?url=siswa/nilai');
                exit();
            }

            $quizInfo = $examModel->getQuizById($quiz_id);
            $soalList = $examModel->getSoalByQuiz($quiz_id, true);
            require_once ROOT_PATH . 'views/siswa/kerjakan_quiz.php';
            exit();
        }

        $allQuiz = $examModel->getQuizList($kelasId);
        $cbtList = $examModel->getUjianCBT($kelasId);
        $enrolledList = $academicModel->getSiswaEnrolledMapels($siswaId);

        $db = Database::getConnection();
        $stmtH = $db->prepare("
            SELECT quiz_id, total_nilai, nilai_tertinggi, 
            COALESCE(NULLIF(attempt_count, 0), (SELECT COUNT(*) FROM hasil_quiz_history hqh WHERE hqh.siswa_id = hasil_quiz.siswa_id AND hqh.quiz_id = hasil_quiz.quiz_id), 1) AS attempt_count, 
            status_lulus 
            FROM hasil_quiz 
            WHERE siswa_id = ?
        ");
        $stmtH->execute([$siswaId]);
        $completedResults = $stmtH->fetchAll();
        $completedMap = [];
        foreach ($completedResults as $cr) {
            $completedMap[$cr['quiz_id']] = $cr;
        }

        $enrolledMapels = [];
        foreach ($enrolledList as $em) {
            $enrolledMapels[$em['mapel_id'] . '_' . $em['guru_id']] = true;
            $enrolledMapels[$em['mapel_id']] = true;
        }

        $quizList = array_values(array_filter($allQuiz, function($q) use ($enrolledMapels) {
            return isset($enrolledMapels[$q['mapel_id'] . '_' . $q['guru_id']]) || isset($enrolledMapels[$q['mapel_id']]);
        }));

        require_once ROOT_PATH . 'views/siswa/quiz.php';
    }

    public function nilai() {
        $siswa = $this->getSiswaInfo();
        $siswaId = $siswa['id'];

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

        $nilaiList = $nilaiModel->getNilaiBySiswa($siswaId);

        require_once ROOT_PATH . 'models/SettingsModel.php';
        $settingsModel = new SettingsModel();
        $settings = $settingsModel->getAll();

        require_once ROOT_PATH . 'views/siswa/rapor.php';
    }

    public function sertifikat() {
        $siswa = $this->getSiswaInfo();
        $siswaModel = new SiswaModel();

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
            $enrolledMapelGuruKeys[$em['mapel_id'] . '_' . $em['guru_id']] = true;
        }

        require_once ROOT_PATH . 'views/siswa/gabung_kelas.php';
    }

    public function panduan() {
        $user = AuthHelper::user();
        $siswa = $this->getSiswaInfo();
        require_once ROOT_PATH . 'views/siswa/panduan.php';
    }
}
