<?php
/**
 * Guru Controller (Per-Teacher Dynamic Data Scope)
 */
require_once ROOT_PATH . 'helpers/AuthHelper.php';
require_once ROOT_PATH . 'helpers/Security.php';
require_once ROOT_PATH . 'helpers/UploadHelper.php';
require_once ROOT_PATH . 'helpers/FlashHelper.php';
require_once ROOT_PATH . 'models/LearningModel.php';
require_once ROOT_PATH . 'models/ExamModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';
require_once ROOT_PATH . 'models/AbsensiModel.php';
require_once ROOT_PATH . 'models/GuruModel.php';
require_once ROOT_PATH . 'models/SiswaModel.php';
require_once ROOT_PATH . 'models/NilaiModel.php';
require_once ROOT_PATH . 'models/CurriculumModel.php';
require_once ROOT_PATH . 'models/AssessmentModel.php';
require_once ROOT_PATH . 'models/CommunicationModel.php';

class GuruController {

    public function __construct() {
        AuthHelper::requireRole(['Guru', 'Administrator', 'Admin', 'Kepala Sekolah', 'Kepsek']);
    }

    private function getGuruInfo() {
        $user = AuthHelper::user();
        if (!$user) return ['id' => 0, 'user_id' => 0, 'nama_lengkap' => 'Guru'];
        $guruModel = new GuruModel();
        $prof = $guruModel->ensureGuruProfile($user['id'], $user['full_name']);
        return $prof ?: ['id' => 0, 'user_id' => $user['id'], 'nama_lengkap' => $user['full_name']];
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
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'] ?? 0;

        $learningModel = new LearningModel();
        $examModel = new ExamModel();
        $academicModel = new AcademicModel();
        $commModel = new CommunicationModel();

        $materiList = $learningModel->getMateri(null, $guruId);
        $tugasList = $learningModel->getTugas(null, $guruId);
        $quizList = $examModel->getQuizList(null, $guruId);
        $myKeys = $academicModel->getMapelEnrollmentKeys($guruId);
        $enrolledStudents = $academicModel->getEnrolledStudentsForGuru($guruId);
        $pengumumanList = $commModel->getPengumuman('guru');
        $activeTa = $academicModel->getActiveTahunAjaran();
        $jadwalList = $academicModel->getJadwal(null, $guruId);

        $todayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][date('w')];
        $jadwalHariIni = [];
        foreach ($jadwalList as $j) {
            if (strcasecmp($j['hari'] ?? '', $todayName) === 0) {
                $jadwalHariIni[] = $j;
            }
        }

        $mapelDistribution = [];
        foreach ($enrolledStudents as $es) {
            $mName = $es['nama_mapel'] ?? 'Mapel';
            if (!isset($mapelDistribution[$mName])) {
                $mapelDistribution[$mName] = 0;
            }
            $mapelDistribution[$mName]++;
        }

        require_once ROOT_PATH . 'models/ReportModel.php';
        $reportModel = new ReportModel();
        $mySupervisiList = $reportModel->getSupervisiList($guruId);
        $supervisiTerbaru = !empty($mySupervisiList) ? $mySupervisiList[0] : null;

        require_once ROOT_PATH . 'views/guru/dashboard.php';
    }

    public function materi() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'];

        $learningModel = new LearningModel();
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=guru/materi');
                exit();
            }

            $action = $_POST['action'] ?? 'create';
            $id = (int)($_POST['id'] ?? 0);

            if ($action === 'create') {
                $judul = Security::sanitize($_POST['judul']);
                $deskripsi = Security::sanitize($_POST['deskripsi']);
                $mapel_id = (int)$_POST['mapel_id'];
                
                // Ambil daftar kelas_ids
                $kelas_ids = [];
                if (isset($_POST['kelas_ids']) && is_array($_POST['kelas_ids'])) {
                    $kelas_ids = array_map('intval', $_POST['kelas_ids']);
                } elseif (isset($_POST['kelas_id'])) {
                    $kelas_ids = [(int)$_POST['kelas_id']];
                }
                $kelas_ids = array_values(array_filter($kelas_ids, function($kId) { return $kId > 0; }));

                if (empty($kelas_ids)) {
                    FlashHelper::setError('Pilih minimal satu kelas target.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/materi');
                    exit();
                }

                $jenis_file = $_POST['jenis_file'];
                $youtube_url = Security::sanitize($_POST['youtube_url'] ?? '');
                $filePath = null;

                if ($jenis_file !== 'youtube' && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $filePath = UploadHelper::upload($_FILES['file'], 'materi');
                }

                // Simpan 1 baris materi dengan seluruh kelas_ids yang dicentang
                $learningModel->addMateri($guruId, $mapel_id, $kelas_ids, $judul, $deskripsi, $jenis_file, $filePath, $youtube_url);

                require_once ROOT_PATH . 'helpers/FcmHelper.php';
                foreach ($kelas_ids as $kId) {
                    FcmHelper::sendToKelas($kId, '📚 Materi Pembelajaran Baru: ' . $judul, 'Guru mengunggah materi baru untuk kelas Anda.', ['type' => 'materi']);
                }

                FlashHelper::setSuccess('Materi Pembelajaran baru berhasil diunggah.');

            } elseif ($action === 'update' && $id > 0) {
                $judul = Security::sanitize($_POST['judul']);
                $deskripsi = Security::sanitize($_POST['deskripsi']);
                $mapel_id = (int)$_POST['mapel_id'];
                
                $kelas_ids = [];
                if (isset($_POST['kelas_ids']) && is_array($_POST['kelas_ids'])) {
                    $kelas_ids = array_map('intval', $_POST['kelas_ids']);
                } elseif (isset($_POST['kelas_id'])) {
                    $kelas_ids = [(int)$_POST['kelas_id']];
                }
                $kelas_ids = array_values(array_filter($kelas_ids, function($kId) { return $kId > 0; }));

                if (empty($kelas_ids)) {
                    FlashHelper::setError('Pilih minimal satu kelas target.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/materi');
                    exit();
                }

                $jenis_file = $_POST['jenis_file'];
                $youtube_url = Security::sanitize($_POST['youtube_url'] ?? '');
                $filePath = null;

                if ($jenis_file !== 'youtube' && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $filePath = UploadHelper::upload($_FILES['file'], 'materi');
                }

                // Update 1 baris materi tersebut dengan kelas_ids baru tanpa duplikasi
                $learningModel->updateMateri($id, $mapel_id, $kelas_ids, $judul, $deskripsi, $jenis_file, $filePath, $youtube_url);
                FlashHelper::setSuccess('Data Materi Pembelajaran berhasil diperbarui.');

            } elseif ($action === 'delete' && $id > 0) {
                $learningModel->deleteMateri($id);
                FlashHelper::setSuccess('Materi Pembelajaran berhasil dihapus.');
            }

            header('Location: ' . BASE_URL . 'index.php?url=guru/materi');
            exit();
        }

        $materiList = $learningModel->getMateri(null, $guruId);

        $userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
        $isAdmin = in_array($userRole, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);

        if ($isAdmin) {
            $mapelList = $academicModel->getMapel();
            $kelasList = $academicModel->getKelas();
        } else {
            $mapelList = $academicModel->getMapelByGuru($guruId);
            if (empty($mapelList)) {
                $mapelList = $academicModel->getMapel();
            }

            $kelasList = $academicModel->getKelasByGuru($guruId);
            if (empty($kelasList)) {
                $kelasList = $academicModel->getKelas();
            }
        }

        require_once ROOT_PATH . 'views/guru/materi.php';
    }

    public function tugas() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'];

        $learningModel = new LearningModel();
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=guru/tugas');
                exit();
            }

            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $judul = Security::sanitize($_POST['judul']);
                $deskripsi = Security::sanitize($_POST['deskripsi']);
                $mapel_id = (int)$_POST['mapel_id'];
                
                $kelas_ids = [];
                if (isset($_POST['kelas_ids']) && is_array($_POST['kelas_ids'])) {
                    $kelas_ids = array_map('intval', $_POST['kelas_ids']);
                } elseif (isset($_POST['kelas_id'])) {
                    $kelas_ids = [(int)$_POST['kelas_id']];
                }
                $kelas_ids = array_values(array_filter($kelas_ids, function($kId) { return $kId > 0; }));

                if (empty($kelas_ids)) {
                    FlashHelper::setError('Pilih minimal satu kelas target.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/tugas');
                    exit();
                }

                $deadline = $_POST['deadline'];
                $filePath = null;

                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $filePath = UploadHelper::upload($_FILES['file'], 'tugas');
                }

                $learningModel->addTugas($guruId, $mapel_id, $kelas_ids, $judul, $deskripsi, $filePath, $deadline);
                
                $commModel = new CommunicationModel();
                require_once ROOT_PATH . 'helpers/FcmHelper.php';
                foreach ($kelas_ids as $kId) {
                    $commModel->sendNotificationToClass(
                        $kId, 
                        '📝 Tugas Pembelajaran Baru', 
                        "Guru mempublikasikan Tugas Baru: {$judul}. Batas deadline: {$deadline}.", 
                        'index.php?url=siswa/tugas'
                    );
                    FcmHelper::sendToKelas($kId, '📝 Tugas Pembelajaran Baru: ' . $judul, "Batas deadline: {$deadline}", ['type' => 'tugas']);
                }

                FlashHelper::setSuccess('Tugas baru berhasil dibuat.');

            } elseif ($action === 'update') {
                $id = (int)$_POST['id'];
                $judul = Security::sanitize($_POST['judul']);
                $deskripsi = Security::sanitize($_POST['deskripsi']);
                $mapel_id = (int)$_POST['mapel_id'];
                
                $kelas_ids = [];
                if (isset($_POST['kelas_ids']) && is_array($_POST['kelas_ids'])) {
                    $kelas_ids = array_map('intval', $_POST['kelas_ids']);
                } elseif (isset($_POST['kelas_id'])) {
                    $kelas_ids = [(int)$_POST['kelas_id']];
                }
                $kelas_ids = array_values(array_filter($kelas_ids, function($kId) { return $kId > 0; }));

                if (empty($kelas_ids)) {
                    FlashHelper::setError('Pilih minimal satu kelas target.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/tugas');
                    exit();
                }

                $deadline = $_POST['deadline'];
                $filePath = null;

                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $filePath = UploadHelper::upload($_FILES['file'], 'tugas');
                }

                $learningModel->updateTugas($id, $mapel_id, $kelas_ids, $judul, $deskripsi, $filePath, $deadline);
                FlashHelper::setSuccess('Data Penugasan berhasil diperbarui.');

            } elseif ($action === 'delete') {
                $id = (int)$_POST['id'];
                $learningModel->deleteTugas($id);
                FlashHelper::setSuccess('Data Penugasan berhasil dihapus.');

            } elseif ($action === 'grade') {
                $pengumpulan_id = (int)$_POST['pengumpulan_id'];
                $nilai = (float)$_POST['nilai'];
                $komentar = Security::sanitize($_POST['komentar']);

                $learningModel->gradeTugas($pengumpulan_id, $nilai, $komentar);

                // Auto Sync to E-Rapor Nilai Model
                $stmtPengInfo = Database::getConnection()->prepare("
                    SELECT pt.siswa_id, t.mapel_id, t.judul 
                    FROM pengumpulan_tugas pt 
                    JOIN tugas t ON pt.tugas_id = t.id 
                    WHERE pt.id = ?
                ");
                $stmtPengInfo->execute([$pengumpulan_id]);
                $pData = $stmtPengInfo->fetch();
                if ($pData) {
                    require_once ROOT_PATH . 'models/NilaiModel.php';
                    $nilaiModel = new NilaiModel();
                    $nilaiModel->syncSiswaMapelNilai((int)$pData['siswa_id'], (int)$pData['mapel_id']);

                    $commModel = new CommunicationModel();
                    $commModel->sendNotificationToStudent(
                        $pData['siswa_id'], 
                        '🏆 Nilai Tugas Telah Diberikan', 
                        "Guru telah memberikan Nilai {$nilai} untuk tugas: {$pData['judul']}.", 
                        'index.php?url=siswa/tugas'
                    );
                }

                FlashHelper::setSuccess('Nilai tugas siswa berhasil disimpan dan tersinkronisasi ke E-Rapor.');

            } elseif ($action === 'bulk_grade') {
                $grades = $_POST['grades'] ?? [];
                $gradedCount = 0;
                $studentsToSync = [];

                if (is_array($grades) && !empty($grades)) {
                    require_once ROOT_PATH . 'models/NilaiModel.php';
                    $nilaiModel = new NilaiModel();
                    $commModel = new CommunicationModel();

                    foreach ($grades as $pengId => $gData) {
                        $pengId = (int)$pengId;
                        if ($pengId <= 0) continue;

                        $rawNilai = $gData['nilai'] ?? null;
                        if ($rawNilai === '' || $rawNilai === null) continue;

                        $nilai = (float)$rawNilai;
                        $komentar = Security::sanitize($gData['komentar'] ?? '');

                        $learningModel->gradeTugas($pengId, $nilai, $komentar);
                        $gradedCount++;

                        $stmtPengInfo = Database::getConnection()->prepare("
                            SELECT pt.siswa_id, t.mapel_id, t.judul 
                            FROM pengumpulan_tugas pt 
                            JOIN tugas t ON pt.tugas_id = t.id 
                            WHERE pt.id = ?
                        ");
                        $stmtPengInfo->execute([$pengId]);
                        $pData = $stmtPengInfo->fetch(PDO::FETCH_ASSOC);
                        if ($pData) {
                            $sId = (int)$pData['siswa_id'];
                            $mId = (int)$pData['mapel_id'];
                            $studentsToSync[$sId] = $mId;

                            try {
                                $commModel->sendNotificationToStudent(
                                    $sId, 
                                    '🏆 Nilai Tugas Telah Diberikan', 
                                    "Guru telah memberikan Nilai {$nilai} untuk tugas: {$pData['judul']}.", 
                                    'index.php?url=siswa/tugas'
                                );
                            } catch (Throwable $eNotif) {}
                        }
                    }

                    foreach ($studentsToSync as $sId => $mId) {
                        try {
                            $nilaiModel->syncSiswaMapelNilai($sId, $mId);
                        } catch (Throwable $eSync) {}
                    }
                }

                if ($gradedCount > 0) {
                    FlashHelper::setSuccess("Berhasil menyimpan nilai untuk {$gradedCount} siswa secara bersamaan dan tersinkronisasi ke E-Rapor.");
                } else {
                    FlashHelper::setError("Tidak ada nilai siswa yang diisi atau diperbarui.");
                }

            } elseif ($action === 'approve_tugas_susulan') {
                $reqId = (int)$_POST['request_id'];
                $learningModel->updateTugasSusulanStatus($reqId, 'disetujui');

                $stmtReq = Database::getConnection()->prepare("
                    SELECT ts.siswa_id, t.judul 
                    FROM tugas_susulan ts 
                    JOIN tugas t ON ts.tugas_id = t.id 
                    WHERE ts.id = ?
                ");
                $stmtReq->execute([$reqId]);
                $rData = $stmtReq->fetch();
                if ($rData) {
                    $commModel = new CommunicationModel();
                    $commModel->sendNotificationToStudent(
                        $rData['siswa_id'], 
                        '✅ Izin Susulan Tugas Disetujui', 
                        "Guru telah MENYETUJUI izin susulan pengumpulan tugas: {$rData['judul']}. Akses pengumpulan kini terbuka!", 
                        'index.php?url=siswa/tugas'
                    );
                }

                FlashHelper::setSuccess('Permintaan pengumpulan tugas susulan siswa berhasil DISETUJUI. Akses pengumpulan tugas kini telah dibuka.');

            } elseif ($action === 'reject_tugas_susulan') {
                $reqId = (int)$_POST['request_id'];
                $learningModel->updateTugasSusulanStatus($reqId, 'ditolak');

                $stmtReq = Database::getConnection()->prepare("
                    SELECT ts.siswa_id, t.judul 
                    FROM tugas_susulan ts 
                    JOIN tugas t ON ts.tugas_id = t.id 
                    WHERE ts.id = ?
                ");
                $stmtReq->execute([$reqId]);
                $rData = $stmtReq->fetch();
                if ($rData) {
                    $commModel = new CommunicationModel();
                    $commModel->sendNotificationToStudent(
                        $rData['siswa_id'], 
                        '❌ Izin Susulan Tugas Ditolak', 
                        "Guru telah MENOLAK permohonan izin susulan pengumpulan tugas: {$rData['judul']}.", 
                        'index.php?url=siswa/tugas'
                    );
                }

                FlashHelper::setSuccess('Permintaan pengumpulan tugas susulan siswa DITOLAK.');
            }

            header('Location: ' . BASE_URL . 'index.php?url=guru/tugas');
            exit();
        }

        $tugasList = $learningModel->getTugas(null, $guruId);
        $tugasSusulanRequests = $learningModel->getTugasSusulanRequestsByGuru($guruId);

        $userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
        $isAdmin = in_array($userRole, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);

        if ($isAdmin) {
            $mapelList = $academicModel->getMapel();
            $kelasList = $academicModel->getKelas();
        } else {
            $mapelList = $academicModel->getMapelByGuru($guruId);
            if (empty($mapelList)) {
                $mapelList = $academicModel->getMapel();
            }

            $kelasList = $academicModel->getKelasByGuru($guruId);
            if (empty($kelasList)) {
                $kelasList = $academicModel->getKelas();
            }
        }

        require_once ROOT_PATH . 'views/guru/tugas.php';
    }

    public function quiz() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'];

        $examModel = new ExamModel();
        $academicModel = new AcademicModel();
        $commModel = new CommunicationModel();

        $roleName = strtolower(AuthHelper::user()['role_name'] ?? '');
        $isAdminMonitoring = ($roleName === 'administrator');
        $queryGuruId = $isAdminMonitoring ? null : $guruId;

        $checkQuizPermission = function($targetQuizId) use ($examModel, $isAdminMonitoring, $guruId) {
            if ($isAdminMonitoring) return true;
            $qz = $examModel->getQuizById((int)$targetQuizId);
            return ($qz && (int)($qz['guru_id'] ?? 0) === (int)$guruId);
        };

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                exit();
            }

            $action = $_POST['action'] ?? 'create';

            if ($action === 'import_soal_excel') {
                $quizId = (int)$_POST['quiz_id'];
                if (!$checkQuizPermission($quizId)) {
                    FlashHelper::setError('Anda tidak memiliki hak akses untuk mengelola kuis ini.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                    exit();
                }
                if (isset($_FILES['file_excel']) && $_FILES['file_excel']['error'] === UPLOAD_ERR_OK) {
                    $uploadedImages = $_FILES['gambar_soal_files'] ?? [];
                    $resImport = $examModel->importSoalFromExcel($quizId, $_FILES['file_excel']['tmp_name'], $uploadedImages);
                    if ($resImport['status']) {
                        FlashHelper::setSuccess($resImport['message']);
                    } else {
                        FlashHelper::setError($resImport['message']);
                    }
                } else {
                    FlashHelper::setError('Silakan pilih berkas Excel/CSV template soal.');
                }
                header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                exit();
            } elseif ($action === 'create_import_excel') {
                $durasi = (int)$_POST['durasi_menit'];
                $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
                $maxAttempts = isset($_POST['max_attempts']) ? (int)$_POST['max_attempts'] : 1;
                $kategori = $_POST['kategori'] ?? 'kuis';
                $accessKey = !empty($_POST['access_key']) ? trim(strtoupper($_POST['access_key'])) : null;

                $kelas_ids = [];
                if (isset($_POST['kelas_ids']) && is_array($_POST['kelas_ids'])) {
                    $kelas_ids = array_map('intval', $_POST['kelas_ids']);
                } elseif (isset($_POST['kelas_id'])) {
                    $kelas_ids = [(int)$_POST['kelas_id']];
                }
                $kelas_ids = array_values(array_filter($kelas_ids, function($kId) { return $kId > 0; }));

                if (empty($kelas_ids)) {
                    FlashHelper::setError('Pilih minimal satu kelas target.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                    exit();
                }

                $quizId = $examModel->createQuiz(
                    $guruId,
                    (int)$_POST['mapel_id'],
                    $kelas_ids,
                    Security::sanitize($_POST['judul']),
                    Security::sanitize($_POST['deskripsi']),
                    $durasi,
                    0,
                    $_POST['random_soal'] ?? 'Y',
                    $_POST['random_jawaban'] ?? 'Y',
                    $deadline,
                    $maxAttempts,
                    $kategori,
                    $accessKey
                );

                require_once ROOT_PATH . 'helpers/FcmHelper.php';
                $judulQuiz = Security::sanitize($_POST['judul'] ?? 'Paket Ujian');
                foreach ($kelas_ids as $kId) {
                    FcmHelper::sendToKelas($kId, '📝 Ujian / CBT Baru: ' . $judulQuiz, 'Paket Ujian baru telah dipublikasikan untuk kelas Anda.', ['type' => 'quiz', 'id' => $quizId]);
                }

                if (isset($_FILES['file_excel']) && $_FILES['file_excel']['error'] === UPLOAD_ERR_OK) {
                    $uploadedImages = $_FILES['gambar_soal_files'] ?? [];
                    $resImport = $examModel->importSoalFromExcel($quizId, $_FILES['file_excel']['tmp_name'], $uploadedImages);
                    FlashHelper::setSuccess("Paket Ujian (" . strtoupper($kategori) . ") baru berhasil dibuat dan {$resImport['imported']} soal dari Excel berhasil di-import!");
                } else {
                    FlashHelper::setSuccess("Paket Ujian (" . strtoupper($kategori) . ") baru berhasil dibuat.");
                }
                header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                exit();
            } elseif ($action === 'update_gambar_soal') {
                $soalId = (int)$_POST['soal_id'];
                $stmtSoalCheck = Database::getConnection()->prepare("SELECT quiz_id FROM soal WHERE id = ?");
                $stmtSoalCheck->execute([$soalId]);
                $targetQId = $stmtSoalCheck->fetchColumn();
                if (!$checkQuizPermission($targetQId)) {
                    FlashHelper::setError('Anda tidak memiliki hak akses untuk mengelola soal ini.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                    exit();
                }
                if (isset($_FILES['gambar_soal']) && $_FILES['gambar_soal']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['gambar_soal']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $newName = 'soal_' . time() . '_' . uniqid() . '.' . $ext;
                        $uploadDir = ROOT_PATH . 'assets/uploads/soal/';
                        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);
                        if (move_uploaded_file($_FILES['gambar_soal']['tmp_name'], $uploadDir . $newName)) {
                            $db = Database::getConnection();
                            $db->prepare("UPDATE soal SET gambar = ? WHERE id = ?")->execute([$newName, $soalId]);
                            FlashHelper::setSuccess('Gambar soal berhasil diperbarui!');
                        }
                    }
                }
                header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                exit();

            } elseif ($action === 'delete_gambar_soal') {
                $soalId = (int)$_POST['soal_id'];
                $stmtSoalCheck = Database::getConnection()->prepare("SELECT quiz_id FROM soal WHERE id = ?");
                $stmtSoalCheck->execute([$soalId]);
                $targetQId = $stmtSoalCheck->fetchColumn();
                if (!$checkQuizPermission($targetQId)) {
                    FlashHelper::setError('Anda tidak memiliki hak akses untuk mengelola soal ini.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                    exit();
                }
                $db = Database::getConnection();
                $stmtImg = $db->prepare("SELECT gambar FROM soal WHERE id = ?");
                $stmtImg->execute([$soalId]);
                $imgPath = $stmtImg->fetchColumn();

                if ($imgPath && file_exists(ROOT_PATH . 'assets/uploads/soal/' . $imgPath)) {
                    @unlink(ROOT_PATH . 'assets/uploads/soal/' . $imgPath);
                }

                $db->prepare("UPDATE soal SET gambar = NULL WHERE id = ?")->execute([$soalId]);
                FlashHelper::setSuccess('Gambar soal berhasil dihapus!');
                header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                exit();

            } elseif ($action === 'batch_upload_gambar_soal') {
                $quizId = (int)$_POST['quiz_id'];
                if (!$checkQuizPermission($quizId)) {
                    FlashHelper::setError('Anda tidak memiliki hak akses untuk mengelola kuis ini.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                    exit();
                }
                $db = Database::getConnection();
                $examModel = new ExamModel();
                $soalList = $examModel->getSoalByQuiz($quizId);
                $uploadDir = ROOT_PATH . 'assets/uploads/soal/';
                if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);

                $updatedCount = 0;

                // Process image deletions requested via checkboxes
                if (!empty($_POST['hapus_gambar_ids']) && is_array($_POST['hapus_gambar_ids'])) {
                    foreach ($_POST['hapus_gambar_ids'] as $delSoalId) {
                        $delSoalId = (int)$delSoalId;
                        $stmtImg = $db->prepare("SELECT gambar FROM soal WHERE id = ? AND quiz_id = ?");
                        $stmtImg->execute([$delSoalId, $quizId]);
                        $imgPath = $stmtImg->fetchColumn();

                        if ($imgPath && file_exists($uploadDir . $imgPath)) {
                            @unlink($uploadDir . $imgPath);
                        }

                        $db->prepare("UPDATE soal SET gambar = NULL WHERE id = ? AND quiz_id = ?")->execute([$delSoalId, $quizId]);
                        $updatedCount++;
                    }
                }

                // Mode 1: Individual File Picker Matrix (gambar_soal_batch[soal_id])
                if (isset($_FILES['gambar_soal_batch']) && is_array($_FILES['gambar_soal_batch']['name'])) {
                    foreach ($_FILES['gambar_soal_batch']['name'] as $soalId => $fileName) {
                        if (empty($fileName)) continue;
                        $err = $_FILES['gambar_soal_batch']['error'][$soalId] ?? UPLOAD_ERR_NO_FILE;
                        if ($err === UPLOAD_ERR_OK) {
                            $tmpName = $_FILES['gambar_soal_batch']['tmp_name'][$soalId];
                            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                $newName = 'soal_' . time() . '_' . uniqid() . '.' . $ext;
                                if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
                                    $db->prepare("UPDATE soal SET gambar = ? WHERE id = ? AND quiz_id = ?")->execute([$newName, (int)$soalId, $quizId]);
                                    $updatedCount++;
                                }
                            }
                        }
                    }
                }

                // Mode 2: Multi-File Automatic Matcher (batch_images[])
                if (isset($_FILES['batch_images']) && is_array($_FILES['batch_images']['name'])) {
                    $totalFiles = count($_FILES['batch_images']['name']);
                    for ($i = 0; $i < $totalFiles; $i++) {
                        $fName = $_FILES['batch_images']['name'][$i] ?? '';
                        $err = $_FILES['batch_images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                        if (empty($fName) || $err !== UPLOAD_ERR_OK) continue;

                        $tmpName = $_FILES['batch_images']['tmp_name'][$i];
                        $ext = strtolower(pathinfo($fName, PATHINFO_EXTENSION));
                        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) continue;

                        $targetSoalId = null;
                        if (preg_match('/(?:soal[_-]?|gambar[_-]?|num[_-]?)?(\d+)/i', pathinfo($fName, PATHINFO_FILENAME), $matches)) {
                            $qNum = (int)$matches[1];
                            if (isset($soalList[$qNum - 1])) {
                                $targetSoalId = $soalList[$qNum - 1]['id'];
                            }
                        }

                        if (!$targetSoalId && isset($soalList[$i])) {
                            $targetSoalId = $soalList[$i]['id'];
                        }

                        if ($targetSoalId) {
                            $newName = 'soal_batch_' . time() . '_' . uniqid() . '.' . $ext;
                            if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
                                $db->prepare("UPDATE soal SET gambar = ? WHERE id = ? AND quiz_id = ?")->execute([$newName, $targetSoalId, $quizId]);
                                $updatedCount++;
                            }
                        }
                    }
                }

                if ($updatedCount > 0) {
                    FlashHelper::setSuccess("Berhasil mengunggah {$updatedCount} gambar soal sekaligus!");
                } else {
                    FlashHelper::setWarning("Tidak ada berkas gambar valid yang diunggah.");
                }

                header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                exit();
            } elseif ($action === 'create') {
                $durasi = (int)$_POST['durasi_menit'];
                $pertanyaanArr = $_POST['pertanyaan'] ?? [];
                $jenisSoalArr = $_POST['jenis_soal'] ?? [];
                $jumlahSoal = count($pertanyaanArr);

                $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
                $maxAttempts = isset($_POST['max_attempts']) ? (int)$_POST['max_attempts'] : 1;
                $kategori = $_POST['kategori'] ?? 'kuis';
                $accessKey = !empty($_POST['access_key']) ? trim(strtoupper($_POST['access_key'])) : null;

                $kelas_ids = [];
                if (isset($_POST['kelas_ids']) && is_array($_POST['kelas_ids'])) {
                    $kelas_ids = array_map('intval', $_POST['kelas_ids']);
                } elseif (isset($_POST['kelas_id'])) {
                    $kelas_ids = [(int)$_POST['kelas_id']];
                }
                $kelas_ids = array_values(array_filter($kelas_ids, function($kId) { return $kId > 0; }));

                if (empty($kelas_ids)) {
                    FlashHelper::setError('Pilih minimal satu kelas target.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                    exit();
                }

                $quizId = $examModel->createQuiz(
                    $guruId,
                    (int)$_POST['mapel_id'],
                    $kelas_ids,
                    Security::sanitize($_POST['judul']),
                    Security::sanitize($_POST['deskripsi']),
                    $durasi,
                    $jumlahSoal,
                    $_POST['random_soal'] ?? 'Y',
                    $_POST['random_jawaban'] ?? 'Y',
                    $deadline,
                    $maxAttempts,
                    $kategori,
                    $accessKey
                );

                require_once ROOT_PATH . 'helpers/FcmHelper.php';
                $judulQuiz = Security::sanitize($_POST['judul'] ?? 'Quiz/CBT');
                foreach ($kelas_ids as $kId) {
                    FcmHelper::sendToKelas($kId, '📝 Quiz / CBT Baru: ' . $judulQuiz, 'Quiz baru telah dipublikasikan untuk kelas Anda.', ['type' => 'quiz', 'id' => $quizId]);
                }

                $pilAArr = $_POST['pil_a'] ?? [];
                $pilBArr = $_POST['pil_b'] ?? [];
                $pilCArr = $_POST['pil_c'] ?? [];
                $pilDArr = $_POST['pil_d'] ?? [];
                $pilEArr = $_POST['pil_e'] ?? [];
                $pilFArr = $_POST['pil_f'] ?? [];
                $jawabanArr = $_POST['jawaban'] ?? [];
                $jawabanTfArr = $_POST['jawaban_tf'] ?? [];

                foreach ($pertanyaanArr as $idx => $tanya) {
                    if (!empty($tanya)) {
                        $jSoal = $jenisSoalArr[$idx] ?? 'pg';

                        $gambarPath = null;
                        if (isset($_FILES['gambar_soal'])) {
                            $resImg = UploadHelper::uploadArrayElement($_FILES['gambar_soal'], $idx, 'soal');
                            if ($resImg) {
                                $gambarPath = $resImg;
                            }
                        }

                        $pilihan = [];
                        if ($jSoal === 'pg') {
                            $possibleOptions = [
                                'A' => $pilAArr[$idx] ?? '',
                                'B' => $pilBArr[$idx] ?? '',
                                'C' => $pilCArr[$idx] ?? '',
                                'D' => $pilDArr[$idx] ?? '',
                                'E' => $pilEArr[$idx] ?? '',
                                'F' => $pilFArr[$idx] ?? '',
                            ];
                            $targetJawaban = $jawabanArr[$idx] ?? 'A';
                            foreach ($possibleOptions as $letter => $rawVal) {
                                $val = Security::sanitize($rawVal);
                                if ($val !== '') {
                                    $pilihan[] = [
                                        'teks' => $val,
                                        'is_benar' => ($targetJawaban === $letter)
                                    ];
                                }
                            }
                        } elseif ($jSoal === 'tf') {
                            $tfVal = $jawabanTfArr[$idx] ?? 'BENAR';
                            $pilihan = [
                                ['teks' => 'Benar (True)', 'is_benar' => ($tfVal === 'BENAR')],
                                ['teks' => 'Salah (False)', 'is_benar' => ($tfVal === 'SALAH')],
                            ];
                        }
                        $examModel->addSoal($quizId, $jSoal, Security::sanitize($tanya), 10, $pilihan, $gambarPath);
                    }
                }
                $commModel->sendNotificationToClass(
                    (int)$_POST['kelas_id'], 
                    '✏️ Kuis CBT Baru Dipublikasikan', 
                    "Guru mempublikasikan Kuis Baru: " . Security::sanitize($_POST['judul']) . ". Durasi: {$durasi} Menit.", 
                    'index.php?url=siswa/quiz'
                );

                FlashHelper::setSuccess('Paket Quiz & ' . count($pertanyaanArr) . ' Soal berhasil dibuat.');

            } elseif ($action === 'add_soal') {
                $quizId = (int)$_POST['quiz_id'];
                if (!$checkQuizPermission($quizId)) {
                    FlashHelper::setError('Anda tidak memiliki hak akses untuk mengelola kuis ini.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                    exit();
                }
                $jSoal = $_POST['jenis_soal'] ?? 'pg';
                $pertanyaan = Security::sanitize($_POST['pertanyaan']);
                
                $gambarPath = null;
                if (isset($_FILES['gambar_soal']) && $_FILES['gambar_soal']['error'] === UPLOAD_ERR_OK) {
                    $resImg = UploadHelper::upload($_FILES['gambar_soal'], 'soal');
                    if ($resImg) {
                        $gambarPath = $resImg;
                    }
                }

                $pilihan = [];

                if ($jSoal === 'pg') {
                    $possibleOptions = [
                        'A' => $_POST['pil_a'] ?? '',
                        'B' => $_POST['pil_b'] ?? '',
                        'C' => $_POST['pil_c'] ?? '',
                        'D' => $_POST['pil_d'] ?? '',
                        'E' => $_POST['pil_e'] ?? '',
                        'F' => $_POST['pil_f'] ?? '',
                    ];
                    $targetJawaban = $_POST['jawaban'] ?? 'A';
                    foreach ($possibleOptions as $letter => $rawVal) {
                        $val = Security::sanitize($rawVal);
                        if ($val !== '') {
                            $pilihan[] = [
                                'teks' => $val,
                                'is_benar' => ($targetJawaban === $letter)
                            ];
                        }
                    }
                } elseif ($jSoal === 'tf') {
                    $tfVal = $_POST['jawaban_tf'] ?? 'BENAR';
                    $pilihan = [
                        ['teks' => 'Benar (True)', 'is_benar' => ($tfVal === 'BENAR')],
                        ['teks' => 'Salah (False)', 'is_benar' => ($tfVal === 'SALAH')],
                    ];
                }

                $examModel->addSoal($quizId, $jSoal, $pertanyaan, 10, $pilihan, $gambarPath);
                FlashHelper::setSuccess('Soal baru (' . strtoupper($jSoal) . ') berhasil ditambahkan ke paket Quiz.');

            } elseif ($action === 'update') {
                $id = (int)$_POST['id'];
                if (!$checkQuizPermission($id)) {
                    FlashHelper::setError('Anda tidak memiliki hak akses untuk memperbarui kuis ini.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                    exit();
                }
                $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
                $maxAttempts = isset($_POST['max_attempts']) ? (int)$_POST['max_attempts'] : 1;
                $kategori = $_POST['kategori'] ?? 'kuis';
                $accessKey = !empty($_POST['access_key']) ? trim(strtoupper($_POST['access_key'])) : null;

                $kelas_ids = [];
                if (isset($_POST['kelas_ids']) && is_array($_POST['kelas_ids'])) {
                    $kelas_ids = array_map('intval', $_POST['kelas_ids']);
                } elseif (isset($_POST['kelas_id'])) {
                    $kelas_ids = [(int)$_POST['kelas_id']];
                }
                $kelas_ids = array_values(array_filter($kelas_ids, function($kId) { return $kId > 0; }));

                if (empty($kelas_ids)) {
                    FlashHelper::setError('Pilih minimal satu kelas target.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                    exit();
                }

                $examModel->updateQuiz(
                    $id,
                    (int)$_POST['mapel_id'],
                    $kelas_ids,
                    Security::sanitize($_POST['judul']),
                    Security::sanitize($_POST['deskripsi']),
                    (int)$_POST['durasi_menit'],
                    $_POST['random_soal'] ?? 'Y',
                    $deadline,
                    $maxAttempts,
                    $kategori,
                    $accessKey
                );
                FlashHelper::setSuccess('Data Ujian/Quiz berhasil diperbarui.');

            } elseif ($action === 'approve_susulan') {
                $reqId = (int)$_POST['request_id'];
                $stmtReqCheck = Database::getConnection()->prepare("SELECT quiz_id FROM quiz_susulan WHERE id = ?");
                $stmtReqCheck->execute([$reqId]);
                $targetQId = $stmtReqCheck->fetchColumn();
                if (!$checkQuizPermission($targetQId)) {
                    FlashHelper::setError('Anda tidak memiliki hak akses untuk memproses permohonan susulan kuis ini.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz&tab=susulan');
                    exit();
                }
                $stmtReq = Database::getConnection()->prepare("
                    SELECT qs.quiz_id, qs.siswa_id, q.judul 
                    FROM quiz_susulan qs 
                    JOIN quiz q ON qs.quiz_id = q.id 
                    WHERE qs.id = ?
                ");
                $stmtReq->execute([$reqId]);
                $rData = $stmtReq->fetch();
                if ($rData) {
                    $examModel->approveSusulanRequest($rData['quiz_id'], $rData['siswa_id']);
                    $commModel->sendNotificationToStudent(
                        $rData['siswa_id'], 
                        '✅ Izin Ujian Susulan Disetujui', 
                        "Guru telah MENYETUJUI izin Ujian Susulan Kuis/UTS/UAS: {$rData['judul']}. Akses pengerjaan kuis kini telah dibuka kembali!", 
                        'index.php?url=siswa/quiz'
                    );
                } else {
                    $examModel->updateSusulanStatus($reqId, 'disetujui');
                }

                FlashHelper::setSuccess('Permintaan Ujian Susulan / Buka Kunci siswa berhasil DISETUJUI. Akses pengerjaan ujian siswa telah dibuka.');

            } elseif ($action === 'reject_susulan') {
                $reqId = (int)$_POST['request_id'];
                $stmtReqCheck = Database::getConnection()->prepare("SELECT quiz_id FROM quiz_susulan WHERE id = ?");
                $stmtReqCheck->execute([$reqId]);
                $targetQId = $stmtReqCheck->fetchColumn();
                if (!$checkQuizPermission($targetQId)) {
                    FlashHelper::setError('Anda tidak memiliki hak akses untuk memproses permohonan susulan kuis ini.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz&tab=susulan');
                    exit();
                }
                $examModel->updateSusulanStatus($reqId, 'ditolak');

                $stmtReq = Database::getConnection()->prepare("
                    SELECT qs.siswa_id, q.judul 
                    FROM quiz_susulan qs 
                    JOIN quiz q ON qs.quiz_id = q.id 
                    WHERE qs.id = ?
                ");
                $stmtReq->execute([$reqId]);
                $rData = $stmtReq->fetch();
                if ($rData) {
                    $commModel->sendNotificationToStudent(
                        $rData['siswa_id'], 
                        '❌ Izin Ujian Susulan Ditolak', 
                        "Guru telah MENOLAK permohonan izin Ujian Susulan Kuis: {$rData['judul']}.", 
                        'index.php?url=siswa/quiz'
                    );
                }

                FlashHelper::setSuccess('Permintaan Ujian Susulan siswa DITOLAK.');

            } elseif ($action === 'delete') {
                $id = (int)$_POST['id'];
                if (!$checkQuizPermission($id)) {
                    FlashHelper::setError('Anda tidak memiliki hak akses untuk menghapus paket kuis ini.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                    exit();
                }
                $examModel->deleteQuiz($id);
                FlashHelper::setSuccess('Paket Quiz beserta seluruh soal berhasil dihapus.');

            } elseif ($action === 'delete_soal') {
                $soalId = (int)$_POST['soal_id'];
                $stmtSoalCheck = Database::getConnection()->prepare("SELECT quiz_id FROM soal WHERE id = ?");
                $stmtSoalCheck->execute([$soalId]);
                $targetQId = $stmtSoalCheck->fetchColumn();
                if (!$checkQuizPermission($targetQId)) {
                    FlashHelper::setError('Anda tidak memiliki hak akses untuk menghapus soal ini.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                    exit();
                }
                $examModel->deleteSoal($soalId);
                FlashHelper::setSuccess('Soal berhasil dihapus.');

            } elseif ($action === 'delete_hasil_quiz') {
                $quizId = (int)$_POST['quiz_id'];
                if (!$checkQuizPermission($quizId)) {
                    FlashHelper::setError('Anda tidak memiliki hak akses untuk menghapus pengerjaan kuis ini.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz&tab=koreksi');
                    exit();
                }
                $siswaId = (int)$_POST['siswa_id'];
                $examModel->deleteHasilQuiz($quizId, $siswaId);
                FlashHelper::setSuccess('Data pengerjaan kuis siswa berhasil dihapus.');

            } elseif ($action === 'grade_quiz_essay') {
                $quizId = (int)$_POST['quiz_id'];
                if (!$checkQuizPermission($quizId)) {
                    FlashHelper::setError('Anda tidak memiliki hak akses untuk mengoreksi kuis ini.');
                    header('Location: ' . BASE_URL . 'index.php?url=guru/quiz&tab=koreksi');
                    exit();
                }
                $siswaId = (int)$_POST['siswa_id'];
                $essayScores = $_POST['nilai_essay'] ?? [];

                $db = Database::getConnection();
                
                $stmtGetSoal = $db->prepare("SELECT bobot FROM soal WHERE id = ?");
                $stmtCheckExist = $db->prepare("SELECT id FROM jawaban_siswa WHERE siswa_id = ? AND quiz_id = ? AND soal_id = ?");
                $stmtUpdate = $db->prepare("UPDATE jawaban_siswa SET nilai = ?, is_benar = IF(? > 0, 1, 0) WHERE id = ?");
                $stmtInsert = $db->prepare("INSERT INTO jawaban_siswa (siswa_id, quiz_id, soal_id, is_benar, nilai) VALUES (?, ?, ?, IF(? > 0, 1, 0), ?)");

                foreach ($essayScores as $keyId => $scoreVal) {
                    $keyId = (int)$keyId;
                    if ($keyId <= 0) continue;
                    $scoreNum = (float)$scoreVal;

                    // Try looking up by soal_id first
                    $stmtCheckExist->execute([$siswaId, $quizId, $keyId]);
                    $existId = $stmtCheckExist->fetchColumn();

                    if ($existId) {
                        $stmtGetSoal->execute([$keyId]);
                        $soalRow = $stmtGetSoal->fetch();
                        $maxBobot = (float)($soalRow['bobot'] ?? 10);

                        if ($scoreNum > $maxBobot) {
                            if ($scoreNum <= 100 && $maxBobot > 0) {
                                $scoreNum = round(($scoreNum / 100) * $maxBobot, 2);
                            } else {
                                $scoreNum = $maxBobot;
                            }
                        }
                        if ($scoreNum < 0) $scoreNum = 0;

                        $stmtUpdate->execute([$scoreNum, $scoreNum, (int)$existId]);
                    } else {
                        // Direct update if keyId was a jawaban_id
                        $stmtDirectUpdate = $db->prepare("UPDATE jawaban_siswa SET nilai = ?, is_benar = IF(? > 0, 1, 0) WHERE id = ?");
                        $stmtDirectUpdate->execute([$scoreNum, $scoreNum, $keyId]);
                        
                        if ($stmtDirectUpdate->rowCount() == 0) {
                            // Insert new row if neither existed
                            $stmtInsert->execute([$siswaId, $quizId, $keyId, $scoreNum, $scoreNum]);
                        }
                    }
                }

                if (!empty($_POST['unban_siswa'])) {
                    $db->prepare("UPDATE hasil_quiz SET status_banned = 0 WHERE quiz_id = ? AND siswa_id = ?")->execute([$quizId, $siswaId]);
                }

                $examModel->recalculateQuizScore($siswaId, $quizId);

                $commModel = new CommunicationModel();
                $commModel->sendNotificationToStudent(
                    $siswaId, 
                    '🏆 Penilaian Essay Kuis Selesai', 
                    "Guru telah mengoreksi dan memberikan nilai untuk jawaban essay Anda pada kuis.", 
                    'index.php?url=siswa/nilai'
                );

                FlashHelper::setSuccess('Nilai Essay siswa berhasil disimpan dan total nilai kuis telah diperbarui.');
            }

            $redirectTab = $_POST['redirect_tab'] ?? ($action === 'grade_quiz_essay' ? 'koreksi' : (($action === 'approve_susulan' || $action === 'reject_susulan') ? 'susulan' : 'paket'));
            header('Location: ' . BASE_URL . "index.php?url=guru/quiz&tab={$redirectTab}");
            exit();
        }

        $quizList = $examModel->getQuizList(null, $queryGuruId);

        $susulanRequests = $examModel->getSusulanRequestsByGuru($queryGuruId);
        $hasilQuizSubmissions = $examModel->getHasilQuizListByGuru($queryGuruId);

        // --- NEW REPORT & MATRIX DATA FOR QUIZ & CBT ---
        $reportQuizId = isset($_GET['report_quiz_id']) ? $_GET['report_quiz_id'] : 'all';
        $reportKelasId = isset($_GET['report_kelas_id']) && !empty($_GET['report_kelas_id']) ? (int)$_GET['report_kelas_id'] : null;

        $quizReportDetail = null;
        if ($reportQuizId !== 'all' && (int)$reportQuizId > 0) {
            $quizReportDetail = $examModel->getDetailedReportByQuiz((int)$reportQuizId);
            if ($queryGuruId !== null && $quizReportDetail && (int)($quizReportDetail['quiz']['guru_id'] ?? 0) !== (int)$queryGuruId) {
                $quizReportDetail = null;
            }
        }
        $rekapCbtMatrix = $examModel->getRekapNilaiCbtMatrixByGuru($queryGuruId, $reportKelasId);

        $mapelList = $academicModel->getMapelByGuru($guruId);
        if (empty($mapelList)) $mapelList = $academicModel->getMapel();

        $kelasList = $academicModel->getKelasByGuru($guruId);
        if (empty($kelasList)) $kelasList = $academicModel->getKelas();

        $jurusanList = $academicModel->getJurusan();

        require_once ROOT_PATH . 'views/guru/quiz.php';
    }

    public function absensi() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'] ?? 0;

        $absensiModel = new AbsensiModel();
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
            }
        }

        $selectedJadwal = (int)($_GET['jadwal_id'] ?? ($jadwalList[0]['id'] ?? 0));
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $tab = $_GET['tab'] ?? ($_POST['tab'] ?? 'siswa');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . "index.php?url=guru/absensi&jadwal_id={$selectedJadwal}&tanggal={$tanggal}&tab={$tab}");
                exit();
            }

            $isJadwalValid = false;
            foreach ($jadwalList as $jItem) {
                if ((int)($jItem['id'] ?? 0) === $selectedJadwal) {
                    $isJadwalValid = true;
                    break;
                }
            }

            if (!$isAdmin && !$isJadwalValid && !empty($jadwalList)) {
                FlashHelper::setError('Anda tidak memiliki hak akses untuk mengelola presensi jadwal ini.');
                header('Location: ' . BASE_URL . "index.php?url=guru/absensi&jadwal_id={$selectedJadwal}&tanggal={$tanggal}&tab={$tab}");
                exit();
            }

            if (isset($_POST['absensi']) && is_array($_POST['absensi'])) {
                $presensi = $_POST['absensi'];
                foreach ($presensi as $siswaId => $status) {
                    $keterangan = Security::sanitize($_POST['keterangan'][$siswaId] ?? '');
                    $absensiModel->recordAttendance($selectedJadwal, $siswaId, $tanggal, $status, $keterangan);
                }
                FlashHelper::setSuccess('Rekap presensi siswa berhasil disimpan.');
            }

            if (isset($_POST['absensi_guru']) && is_array($_POST['absensi_guru'])) {
                $presensiGuru = $_POST['absensi_guru'];
                foreach ($presensiGuru as $guruId => $status) {
                    $keteranganGuru = Security::sanitize($_POST['keterangan_guru'][$guruId] ?? '');
                    $absensiModel->recordAttendanceGuru((int)$guruId, $tanggal, $status, $keteranganGuru);
                }
                FlashHelper::setSuccess('Rekap presensi guru & GTK berhasil disimpan.');
            }

            header('Location: ' . BASE_URL . "index.php?url=guru/absensi&jadwal_id={$selectedJadwal}&tanggal={$tanggal}&tab={$tab}");
            exit();
        }

        $recap = $selectedJadwal > 0 ? $absensiModel->getRecap($selectedJadwal, $tanggal) : [];
        $recapGuru = $absensiModel->getRecapGuru($tanggal);
        require_once ROOT_PATH . 'views/guru/absensi.php';
    }

    /**
     * Presensi Mandiri Guru dengan Foto Selfie Kamera & Validasi Titik Geofencing GPS
     */
    public function presensiGuru() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'] ?? 0;

        $absensiModel = new AbsensiModel();
        require_once ROOT_PATH . 'models/SettingsModel.php';
        $settingsModel = new SettingsModel();
        $settings = $settingsModel->getAll();

        // AJAX Request for selfie presensi
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_presensi_selfie') {
            header('Content-Type: application/json');

            if ($guruId <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Profil guru tidak ditemukan. Harap hubungi administrator.']);
                exit();
            }

            $jenis = $_POST['jenis'] ?? 'masuk';
            $lat = $_POST['latitude'] ?? null;
            $lng = $_POST['longitude'] ?? null;
            $image = $_POST['image_base64'] ?? '';
            $keterangan = $_POST['keterangan'] ?? '';

            $result = $absensiModel->submitPresensiGuruSelfie($guruId, [
                'jenis' => $jenis,
                'latitude' => $lat,
                'longitude' => $lng,
                'image_base64' => $image,
                'keterangan' => $keterangan
            ]);

            echo json_encode($result);
            exit();
        }

        $presensiHariIni = $absensiModel->getPresensiGuruHariIni($guruId);
        $riwayatPresensi = $absensiModel->getRiwayatPresensiGuru($guruId, 20);

        require_once ROOT_PATH . 'views/guru/presensi_selfie.php';
    }

    public function recapBulanan() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'] ?? 0;

        $absensiModel = new AbsensiModel();
        $academicModel = new AcademicModel();
        
        $userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
        $isAdmin = in_array($userRole, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);

        $bulan = sprintf('%02d', (int)($_GET['bulan'] ?? date('m')));
        $tahun = (int)($_GET['tahun'] ?? date('Y'));
        $kelasId = (int)($_GET['kelas_id'] ?? 0);
        $type = 'siswa';
        
        if ($isAdmin) {
            $kelasList = $academicModel->getKelas();
            $targetKelas = $kelasId;
            $queryGuruId = null;
        } else {
            $myKelas = $academicModel->getKelasByGuru($guruId);
            if (!empty($myKelas)) {
                $kelasList = $myKelas;
                $myKelasIds = array_column($myKelas, 'id');
                if ($kelasId > 0 && in_array($kelasId, $myKelasIds)) {
                    $targetKelas = $kelasId;
                } else {
                    $targetKelas = $myKelasIds;
                }
                $queryGuruId = null;
            } else {
                $kelasList = $academicModel->getKelas();
                $targetKelas = $kelasId;
                $queryGuruId = null;
            }
        }

        $monthlyRecap = $absensiModel->getMonthlyRecapSiswa($bulan, $tahun, $targetKelas, $queryGuruId);
        
        require_once ROOT_PATH . 'views/guru/recap_bulanan.php';
    }

    public function exportRecapBulananCsv() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'] ?? 0;

        $absensiModel = new AbsensiModel();
        $academicModel = new AcademicModel();

        $userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
        $isAdmin = in_array($userRole, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);

        $bulan = sprintf('%02d', (int)($_GET['bulan'] ?? date('m')));
        $tahun = (int)($_GET['tahun'] ?? date('Y'));
        $kelasId = (int)($_GET['kelas_id'] ?? 0);
        $type = 'siswa';

        $queryGuruId = null;
        if (!$isAdmin) {
            $myKelas = $academicModel->getKelasByGuru($guruId);
            if (!empty($myKelas)) {
                $myKelasIds = array_column($myKelas, 'id');
                if ($kelasId > 0 && in_array($kelasId, $myKelasIds)) {
                    $targetKelas = $kelasId;
                } else {
                    $targetKelas = $myKelasIds;
                }
            } else {
                $targetKelas = $kelasId;
            }
        } else {
            $targetKelas = $kelasId;
        }
        
        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ][$bulan] ?? $bulan;

        $recap = $absensiModel->getMonthlyRecapSiswa($bulan, $tahun, $targetKelas, $queryGuruId);
        $filename = "Rekap_Absensi_Siswa_{$namaBulan}_{$tahun}.csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ["REKAP ABSENSI BULANAN SISWA"]);
        fputcsv($output, ["Bulan: " . $namaBulan . " " . $tahun]);
        fputcsv($output, []);

        $numDays = $recap['num_days'];
        $headerRow = ["No", "NIS/NISN", "Nama Lengkap", "Kelas"];
        for ($d = 1; $d <= $numDays; $d++) {
            $headerRow[] = (string)$d;
        }
        $headerRow = array_merge($headerRow, ["Total Hadir", "Terlambat", "Sakit", "Izin", "Alpa", "Scan Pulang", "Persentase Kehadiran (%)"]);
        fputcsv($output, $headerRow);

        $no = 1;
        foreach ($recap['data'] as $row) {
            $dataRow = [
                $no++,
                $row['nis'] ?: ($row['nisn'] ?: '-'),
                $row['nama_lengkap'],
                $row['nama_kelas'] ?: '-'
            ];
            for ($d = 1; $d <= $numDays; $d++) {
                $dataRow[] = $row['daily'][$d] ?? '-';
            }
            $dataRow[] = $row['total_hadir'];
            $dataRow[] = $row['total_terlambat'];
            $dataRow[] = $row['total_sakit'];
            $dataRow[] = $row['total_izin'];
            $dataRow[] = $row['total_alpa'];
            $dataRow[] = $row['total_pulang'];
            $dataRow[] = $row['persentase'] . '%';
            fputcsv($output, $dataRow);
        }

        fclose($output);
        exit();
    }

    public function exportRecapBulananPdf() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'] ?? 0;

        $absensiModel = new AbsensiModel();
        $academicModel = new AcademicModel();
        
        $userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
        $isAdmin = in_array($userRole, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);

        $bulan = sprintf('%02d', (int)($_GET['bulan'] ?? date('m')));
        $tahun = (int)($_GET['tahun'] ?? date('Y'));
        $kelasId = (int)($_GET['kelas_id'] ?? 0);
        $type = 'siswa';
        
        $queryGuruId = null;
        if (!$isAdmin) {
            $myKelas = $academicModel->getKelasByGuru($guruId);
            if (!empty($myKelas)) {
                $myKelasIds = array_column($myKelas, 'id');
                if ($kelasId > 0 && in_array($kelasId, $myKelasIds)) {
                    $targetKelas = $kelasId;
                } else {
                    $targetKelas = $myKelasIds;
                }
            } else {
                $targetKelas = $kelasId;
            }
        } else {
            $targetKelas = $kelasId;
        }

        $recap = $absensiModel->getMonthlyRecapSiswa($bulan, $tahun, $targetKelas, $queryGuruId);
        
        require_once ROOT_PATH . 'views/guru/recap_bulanan_pdf.php';
    }

    public function inputNilai() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'] ?? 0;
        $siswaModel = new SiswaModel();
        $academicModel = new AcademicModel();
        $nilaiModel = new NilaiModel();

        $userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
        $isAdmin = in_array($userRole, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);

        if ($isAdmin) {
            $kelasList = $academicModel->getKelas();
            $mapelList = $academicModel->getMapel();
        } else {
            $kelasList = $academicModel->getKelasByGuru($guruId);
            $mapelList = $academicModel->getMapelByGuru($guruId);
            // Fallback: If guru has no specific kelas or mapel in assignment records, provide all active kelas & mapel so guru is never stuck
            if (empty($kelasList)) {
                $kelasList = $academicModel->getKelas();
            }
            if (empty($mapelList)) {
                $mapelList = $academicModel->getMapel();
            }
        }

        $selectedKelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : ($kelasList[0]['id'] ?? 0);
        $selectedMapelId = isset($_GET['mapel_id']) && $_GET['mapel_id'] !== '' ? (int)$_GET['mapel_id'] : null;

        if (!$selectedMapelId && $selectedKelasId) {
            $db = Database::getConnection();
            $stmtActiveMapel = $db->prepare("
                SELECT nr.mapel_id 
                FROM nilai_rapor nr
                JOIN siswa s ON nr.siswa_id = s.id
                WHERE s.kelas_id = ? 
                  AND (nr.nilai_tugas > 0 OR nr.nilai_quiz > 0 OR nr.nilai_uts > 0 OR nr.nilai_uas > 0)
                LIMIT 1
            ");
            $stmtActiveMapel->execute([$selectedKelasId]);
            $activeMapelId = $stmtActiveMapel->fetchColumn();
            if ($activeMapelId) {
                $selectedMapelId = (int)$activeMapelId;
            } else {
                $selectedMapelId = $mapelList[0]['id'] ?? 0;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . "index.php?url=guru/inputNilai&kelas_id={$selectedKelasId}&mapel_id={$selectedMapelId}");
                exit();
            }

            $action = $_POST['action'] ?? 'single_save';
            if ($action === 'batch_save') {
                $nilais = $_POST['nilai'] ?? [];
                $countSaved = 0;
                foreach ($nilais as $siswaId => $data) {
                    $tugas = min(100.0, max(0.0, (float)($data['tugas'] ?? 0)));
                    $quiz  = min(100.0, max(0.0, (float)($data['quiz'] ?? 0)));
                    $uts   = min(100.0, max(0.0, (float)($data['uts'] ?? 0)));
                    $uas   = min(100.0, max(0.0, (float)($data['uas'] ?? 0)));
                    $nilaiModel->saveNilai((int)$siswaId, $selectedMapelId, 1, 1, $tugas, $quiz, $uts, $uas);
                    $countSaved++;
                }
                FlashHelper::setSuccess("Berhasil memperbarui E-Rapor batch untuk {$countSaved} siswa kelas ini.");

            } else {
                $siswa_id = (int)$_POST['siswa_id'];
                $mapel_id = (int)$_POST['mapel_id'];
                $tugas = min(100.0, max(0.0, (float)$_POST['nilai_tugas']));
                $quiz  = min(100.0, max(0.0, (float)$_POST['nilai_quiz']));
                $uts   = min(100.0, max(0.0, (float)$_POST['nilai_uts']));
                $uas   = min(100.0, max(0.0, (float)$_POST['nilai_uas']));

                $nilaiModel->saveNilai($siswa_id, $mapel_id, 1, 1, $tugas, $quiz, $uts, $uas);
                FlashHelper::setSuccess('Nilai E-Rapor Siswa berhasil dihitung & disimpan.');
            }

            header('Location: ' . BASE_URL . "index.php?url=guru/inputNilai&kelas_id={$selectedKelasId}&mapel_id={$selectedMapelId}");
            exit();
        }

        $siswaList = ($selectedKelasId > 0) ? $siswaModel->getAll($selectedKelasId) : [];

        $existingNilai = [];
        if ($selectedKelasId && $selectedMapelId) {
            $existingNilai = $nilaiModel->getNilaiByKelasAndMapel($selectedKelasId, $selectedMapelId);
        }

        $selectedKelasInfo = null;
        if ($selectedKelasId > 0) {
            $db = Database::getConnection();
            $stmtK = $db->prepare("SELECT k.*, j.nama_jurusan FROM kelas k LEFT JOIN jurusan j ON k.jurusan_id = j.id WHERE k.id = ?");
            $stmtK->execute([$selectedKelasId]);
            $selectedKelasInfo = $stmtK ? $stmtK->fetch(PDO::FETCH_ASSOC) : null;
        }

        require_once ROOT_PATH . 'views/guru/input_nilai.php';
    }

    public function jadwal() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'];
        $academicModel = new AcademicModel();

        $jadwalList = $academicModel->getJadwal(null, $guruId);
        $activeTa = $academicModel->getActiveTahunAjaran();

        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $jadwalByHari = [];
        foreach ($hariList as $h) {
            $jadwalByHari[$h] = [];
        }

        foreach ($jadwalList as $j) {
            $hName = ucfirst(strtolower($j['hari'] ?? 'Senin'));
            if (!isset($jadwalByHari[$hName])) {
                $jadwalByHari[$hName] = [];
            }
            $jadwalByHari[$hName][] = $j;
        }

        require_once ROOT_PATH . 'views/guru/jadwal.php';
    }

    public function bankSoal() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'] ?? 0;

        $examModel = new ExamModel();
        
        $userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
        $isAdmin = in_array($userRole, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);

        if ($isAdmin) {
            $quizList = $examModel->getQuizList();
        } else {
            $quizList = $examModel->getQuizList(null, $guruId);
        }

        require_once ROOT_PATH . 'views/guru/bank_soal.php';
    }

    public function kartuGuru() {
        $guru = $this->getGuruInfo();
        require_once ROOT_PATH . 'views/guru/kartu_guru.php';
    }

    public function kartuPendidik() {
        $this->kartuGuru();
    }

    public function scanQr() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'] ?? 0;

        $absensiModel = new AbsensiModel();
        $presensiHariIni = $absensiModel->getPresensiHariIniByGuru($guruId);

        require_once ROOT_PATH . 'views/guru/scan_qr.php';
    }

    public function processScan() {
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
                exit();
            }

            if (!Security::verifyCsrfToken()) {
                echo json_encode(['success' => false, 'message' => 'Sesi / CSRF token telah kedaluwarsa. Silakan refresh halaman.']);
                exit();
            }

            $guru = $this->getGuruInfo();
            $guruId = $guru['id'] ?? null;
            $identifier = $_POST['identifier'] ?? '';

            $absensiModel = new AbsensiModel();
            $result = $absensiModel->processQrScan($identifier, $guruId, false);

            echo json_encode($result);
            exit();
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
            exit();
        }
    }

    public function liveClass() {
        $guru = $this->getGuruInfo();
        $guruId = (int)($guru['id'] ?? 0);

        $roleName = strtolower(AuthHelper::user()['role_name'] ?? '');
        $isAdmin = in_array($roleName, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);
        $queryGuruId = $isAdmin ? null : $guruId;
        $redirectRoute = $isAdmin ? 'admin/liveClass' : 'guru/liveClass';

        $learningModel = new LearningModel();
        $academicModel = new AcademicModel();

        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($requestMethod === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=' . $redirectRoute);
                exit();
            }

            try {
                $action = $_POST['action'] ?? 'create';

                if ($action === 'create') {
                    $mapelId = (int)($_POST['mapel_id'] ?? 0);
                    $kelasId = !empty($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : null;
                    $topik = Security::sanitize($_POST['topik'] ?? '');
                    $deskripsi = Security::sanitize($_POST['deskripsi'] ?? '');
                    $platform = Security::sanitize($_POST['platform'] ?? 'embedded');
                    $meetingLink = Security::sanitize($_POST['meeting_link'] ?? '');
                    $tglPertemuan = $_POST['tgl_pertemuan'] ?? date('Y-m-d');
                    $jamMulai = $_POST['jam_mulai'] ?? date('H:i');
                    $jamSelesai = !empty($_POST['jam_selesai']) ? $_POST['jam_selesai'] : null;

                    if (empty($topik) || $mapelId <= 0) {
                        FlashHelper::setError('Topik pertemuan dan mata pelajaran wajib diisi.');
                        header('Location: ' . BASE_URL . 'index.php?url=' . $redirectRoute);
                        exit();
                    }

                    $roomId = $learningModel->createLiveClass($guruId, $mapelId, $kelasId, $topik, $deskripsi, $platform, $meetingLink, $tglPertemuan, $jamMulai, $jamSelesai);

                    if ($kelasId && $kelasId > 0 && class_exists('CommunicationModel')) {
                        try {
                            $commModel = new CommunicationModel();
                            $commModel->sendNotificationToClass(
                                $kelasId,
                                '📹 Sesi Live Class / Virtual Meeting Baru',
                                "Guru telah mempublikasikan Sesi Meeting Baru: {$topik} pada tanggal {$tglPertemuan} pukul {$jamMulai} WIB.",
                                'index.php?url=siswa/liveClass'
                            );
                        } catch (Throwable $eNotif) {}
                    }

                    FlashHelper::setSuccess('Ruang Live Virtual Meeting baru berhasil dibuat dan dipublikasikan.');

                } elseif ($action === 'update') {
                    $id = (int)($_POST['id'] ?? 0);
                    $mapelId = (int)($_POST['mapel_id'] ?? 0);
                    $kelasId = !empty($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : null;
                    $topik = Security::sanitize($_POST['topik'] ?? '');
                    $deskripsi = Security::sanitize($_POST['deskripsi'] ?? '');
                    $platform = Security::sanitize($_POST['platform'] ?? 'embedded');
                    $meetingLink = Security::sanitize($_POST['meeting_link'] ?? '');
                    $tglPertemuan = $_POST['tgl_pertemuan'] ?? date('Y-m-d');
                    $jamMulai = $_POST['jam_mulai'] ?? date('H:i');
                    $jamSelesai = !empty($_POST['jam_selesai']) ? $_POST['jam_selesai'] : null;
                    $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

                    $existing = $learningModel->getLiveClassById($id);
                    if (!$existing || (!$isAdmin && (int)$existing['guru_id'] !== (int)$guruId)) {
                        FlashHelper::setError('Anda tidak memiliki hak akses untuk mengedit ruang meeting ini.');
                        header('Location: ' . BASE_URL . 'index.php?url=' . $redirectRoute);
                        exit();
                    }

                    $learningModel->updateLiveClass($id, $mapelId, $kelasId, $topik, $deskripsi, $platform, $meetingLink, $tglPertemuan, $jamMulai, $jamSelesai, $isActive);
                    FlashHelper::setSuccess('Data Sesi Live Virtual Meeting berhasil diperbarui.');

                } elseif ($action === 'delete') {
                    $id = (int)($_POST['id'] ?? 0);
                    $res = $learningModel->deleteLiveClass($id, $guruId, $isAdmin);
                    if ($res) {
                        FlashHelper::setSuccess('Ruang Live Virtual Meeting berhasil dihapus.');
                    } else {
                        FlashHelper::setError('Gagal menghapus ruang meeting.');
                    }
                }
            } catch (Throwable $ePost) {
                FlashHelper::setError('Terjadi kesalahan: ' . $ePost->getMessage());
            }

            header('Location: ' . BASE_URL . 'index.php?url=' . $redirectRoute);
            exit();
        }

        try {
            $liveClasses = $learningModel->getLiveClasses($queryGuruId);
        } catch (Throwable $e1) {
            $liveClasses = [];
        }

        try {
            $mapelList = $academicModel->getMapelByGuru($guruId);
            if (empty($mapelList)) $mapelList = $academicModel->getMapel();
        } catch (Throwable $e2) {
            $mapelList = [];
        }

        try {
            $kelasList = $academicModel->getKelasByGuru($guruId);
            if (empty($kelasList)) $kelasList = $academicModel->getKelas();
        } catch (Throwable $e3) {
            $kelasList = [];
        }

        require_once ROOT_PATH . 'views/guru/live_class.php';
    }

    public function learningPath() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'];

        $academicModel = new AcademicModel();
        $learningModel = new LearningModel();
        $examModel = new ExamModel();

        $myMapelList = $academicModel->getMapelByGuru($guruId);
        if (empty($myMapelList)) {
            $myMapelList = $academicModel->getMapel();
        }

        $selectedMapelId = isset($_GET['mapel_id']) && $_GET['mapel_id'] !== '' ? (int)$_GET['mapel_id'] : ($myMapelList[0]['id'] ?? null);

        $materiList = $learningModel->getMateri(null, $guruId);
        if ($selectedMapelId) {
            $materiList = array_values(array_filter($materiList, function($m) use ($selectedMapelId) {
                return $m['mapel_id'] == $selectedMapelId;
            }));
        }

        $tugasList = $learningModel->getTugas(null, $guruId);
        if ($selectedMapelId) {
            $tugasList = array_values(array_filter($tugasList, function($t) use ($selectedMapelId) {
                return $t['mapel_id'] == $selectedMapelId;
            }));
        }

        $quizList = $examModel->getQuizList(null, $guruId);
        if ($selectedMapelId) {
            $quizList = array_values(array_filter($quizList, function($q) use ($selectedMapelId) {
                return ($q['mapel_id'] ?? null) == $selectedMapelId;
            }));
        }

        $selectedMapelInfo = null;
        foreach ($myMapelList as $m) {
            if ($m['id'] == $selectedMapelId) {
                $selectedMapelInfo = $m;
                break;
            }
        }

        require_once ROOT_PATH . 'views/guru/learning_path.php';
    }

    public function profil() {
        $user = AuthHelper::user();
        $guru = $this->getGuruInfo();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=guru/profil');
                exit();
            }

            $fullName = Security::sanitize($_POST['full_name'] ?? '');
            $email = Security::sanitize($_POST['email'] ?? '');
            $noTelp = Security::sanitize($_POST['no_telepon'] ?? '');
            $alamat = Security::sanitize($_POST['alamat'] ?? '');
            $jk = $_POST['jenis_kelamin'] ?? ($guru['jenis_kelamin'] ?? 'L');
            $password = $_POST['password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            if (!empty($password) && strlen($password) < 6) {
                FlashHelper::setError('Password baru minimal 6 karakter!');
                header('Location: ' . BASE_URL . 'index.php?url=guru/profil');
                exit();
            }

            if (!empty($password) && $password !== $confirmPass) {
                FlashHelper::setError('Konfirmasi password baru tidak cocok!');
                header('Location: ' . BASE_URL . 'index.php?url=guru/profil');
                exit();
            }

            $foto = (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) ? $_FILES['foto_profil'] : null;
            $croppedBase64 = $_POST['cropped_avatar_base64'] ?? '';

            require_once ROOT_PATH . 'models/UserModel.php';
            $userModel = new UserModel();
            $res = $userModel->updateProfileFull($user['id'], 'guru', [
                'full_name' => $fullName,
                'email' => $email,
                'no_telepon' => $noTelp,
                'alamat' => $alamat,
                'jenis_kelamin' => $jk,
                'password' => $password,
                'cropped_base64' => $croppedBase64
            ], $foto);

            if ($res['status']) {
                FlashHelper::setSuccess('Profil dan foto akun Guru Anda berhasil diperbarui!');
            } else {
                FlashHelper::setError($res['message'] ?? 'Gagal memperbarui profil.');
            }

            header('Location: ' . BASE_URL . 'index.php?url=guru/profil');
            exit();
        }

        require_once ROOT_PATH . 'views/guru/profil.php';
    }

    public function kelasVirtual() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'];
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=guru/kelasVirtual');
                exit();
            }

            $action = $_POST['action'] ?? '';
            if ($action === 'save_key') {
                $mapelId = (int)$_POST['mapel_id'];
                $kelasId = !empty($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : null;
                $key = Security::sanitize($_POST['enrollment_key']);

                $academicModel->setMapelEnrollmentKey($mapelId, $guruId, $key, $kelasId);
                FlashHelper::setSuccess('Kode Akses / Key Mapel Pengampuan Anda berhasil diperbarui!');
            }

            header('Location: ' . BASE_URL . 'index.php?url=guru/kelasVirtual');
            exit();
        }

        $db = Database::getConnection();
        $kelasList = $db->query("
            SELECT k.*, j.nama_jurusan, g.nama_lengkap as nama_walikelas,
                   (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa,
                   (SELECT COUNT(*) FROM materi m WHERE m.kelas_id = k.id AND m.guru_id = {$guruId}) as total_materi_guru,
                   (SELECT COUNT(*) FROM tugas t WHERE t.kelas_id = k.id AND t.guru_id = {$guruId}) as total_tugas_guru
            FROM kelas k
            LEFT JOIN jurusan j ON k.jurusan_id = j.id
            LEFT JOIN guru g ON k.wali_kelas_id = g.id
            ORDER BY k.tingkat ASC, k.nama_kelas ASC
        ")->fetchAll();

        $academicModel->ensureGuruClassKeys($guruId);
        $myKeys = $academicModel->getMapelEnrollmentKeys($guruId);
        $mapelList = $academicModel->getMapel();
        $myMapelList = $academicModel->getMapelByGuru($guruId);
        $myKelasList = $academicModel->getKelasByGuru($guruId);
        $jurusanList = $academicModel->getJurusan();

        $filterMapelId = isset($_GET['mapel_id']) && $_GET['mapel_id'] !== '' ? (int)$_GET['mapel_id'] : null;
        $filterKelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;
        $filterJurusanId = isset($_GET['jurusan_id']) && $_GET['jurusan_id'] !== '' ? (int)$_GET['jurusan_id'] : null;
        $filterSearch = isset($_GET['search']) ? Security::sanitize($_GET['search']) : null;

        $siswaEnrolledList = $academicModel->getEnrolledStudentsForGuru($guruId, $filterMapelId, $filterKelasId, $filterJurusanId, $filterSearch);

        require_once ROOT_PATH . 'views/guru/kelas_virtual.php';
    }

    public function panduan() {
        $user = AuthHelper::user();
        $guru = $this->getGuruInfo();
        require_once ROOT_PATH . 'views/guru/panduan.php';
    }

    public function downloadTemplateSoal() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Template_Soal_Quiz_SMKMH.csv');
        
        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        // Explicit Excel Column Separator Directive so Excel opens directly per column A - J
        fwrite($output, "sep=;\n");

        // Embedded User Guidance & Instructions Header inside the file
        fputcsv($output, ['# =========================================================================================='], ';');
        fputcsv($output, ['# PANDUAN PENGISIAN TEMPLATE SOAL QUIZ CBT ONLINE - SMK MUTHIA HARAPAN CICALENGKA'], ';');
        fputcsv($output, ['# 1. jenis_soal       : Isi dengan "pg" (Pilihan Ganda), "tf" (Benar/Salah), atau "essay" (Uraian)'], ';');
        fputcsv($output, ['# 2. pertanyaan       : Masukkan teks pertanyaan soal.'], ';');
        fputcsv($output, ['# 3. bobot            : Masukkan angka bobot nilai (Contoh: 10, 20, 25).'], ';');
        fputcsv($output, ['# 4. gambar (Opsional): '], ';');
        fputcsv($output, ['#    - OPSI A (Upload): Isi nama file gambar (misal: diagram.png). Saat import di web, unggah berkas gambarnya sekaligus!'], ';');
        fputcsv($output, ['#    - OPSI B (Link URL): Isi link/URL gambar dari internet (misal: https://domain.com/gambar.png).'], ';');
        fputcsv($output, ['#    - OPSI C (Kosong): Kosongkan jika soal tidak bergambar (gambar bisa di-upload nanti via Bank Soal).'], ';');
        fputcsv($output, ['# 5. opsi_a - opsi_e  : '], ';');
        fputcsv($output, ['#    - Untuk PG       : Isi teks pilihan jawaban A, B, C, D, dan E (opsi E opsional).'], ';');
        fputcsv($output, ['#    - Untuk TF       : Isi opsi_a dengan "Benar" dan opsi_b dengan "Salah".'], ';');
        fputcsv($output, ['#    - Untuk Essay    : Biarkan kolom opsi_a sampai opsi_e kosong.'], ';');
        fputcsv($output, ['# 6. jawaban_benar    : '], ';');
        fputcsv($output, ['#    - Untuk PG       : Isi huruf kunci jawaban yang benar (A, B, C, D, atau E).'], ';');
        fputcsv($output, ['#    - Untuk TF       : Isi A (jika Benar yang betul) atau B (jika Salah yang betul).'], ';');
        fputcsv($output, ['#    - Untuk Essay    : Biarkan kosong.'], ';');
        fputcsv($output, ['# =========================================================================================='], ';');

        // Header columns (Kolom A s/d Kolom K)
        fputcsv($output, ['no_soal', 'jenis_soal', 'pertanyaan', 'bobot', 'gambar', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'jawaban_benar'], ';');

        // Sample Row 1: Pilihan Ganda dengan Gambar
        fputcsv($output, [1, 'pg', 'Perhatikan gambar diagram HTML berikut! Tag manakah yang digunakan untuk membuat judul utama?', 10, 'diagram_html.png', '<h1>', '<body>', '<head>', '<div>', '', 'A'], ';');

        // Sample Row 2: True / False tanpa Gambar
        fputcsv($output, [2, 'tf', 'PHP adalah bahasa pemrograman server-side.', 10, '', 'Benar', 'Salah', '', '', '', 'A'], ';');

        // Sample Row 3: Essay dengan URL Gambar
        fputcsv($output, [3, 'essay', 'Jelaskan fungsi utama dari arsitektur jaringan pada gambar berikut!', 20, 'https://raw.githubusercontent.com/placeholder/image.png', '', '', '', '', '', ''], ';');

        fclose($output);
        exit();
    }

    /**
     * Download Template Excel / CSV untuk Pengisian Capaian & Tujuan Pembelajaran (CP & TP)
     */
    public function downloadTemplateCpTp() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Template_CP_TP_Kurikulum_Merdeka.csv');
        
        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        // Explicit Excel Column Separator Directive so Excel opens directly per column A - G
        fwrite($output, "sep=;\n");

        // Panduan Pengisian di dalam berkas Excel
        fputcsv($output, ['# =========================================================================================================================='], ';');
        fputcsv($output, ['# PANDUAN PENGISIAN TEMPLATE CAPAIAN PEMBELAJARAN (CP) & TUJUAN PEMBELAJARAN (TP) - KURIKULUM MERDEKA SMK'], ';');
        fputcsv($output, ['# 1. elemen           : Wajib diisi. Domain / ranah materi kompetensi CP (Contoh: Pemrograman Sisi Klien).'], ';');
        fputcsv($output, ['# 2. deskripsi_cp     : Wajib diisi. Kalimat rumusan Capaian Pembelajaran (CP) acuan.'], ';');
        fputcsv($output, ['# 3. kode_cp          : Opsional. Boleh dikosongkan agar sistem men-generate otomatis (Contoh: CP-MP01-01).'], ';');
        fputcsv($output, ['# 4. fase             : Opsional. Isi dengan "E" (Kelas X) atau "F" (Kelas XI/XII).'], ';');
        fputcsv($output, ['# 5. materi_pokok_tp  : Wajib diisi. Sub-topik pokok bahasan untuk butir Tujuan Pembelajaran ini.'], ';');
        fputcsv($output, ['# 6. deskripsi_tp     : Wajib diisi. Rumusan Tujuan Pembelajaran turunan operasional.'], ';');
        fputcsv($output, ['# 7. kode_tp          : Opsional. Boleh dikosongkan agar sistem men-generate kode otomatis (Contoh: TP-01.1).'], ';');
        fputcsv($output, ['# CATATAN PENTING: Jika 1 CP memiliki beberapa butir TP, ulangi elemen dan deskripsi_cp yang sama pada baris-baris berikutnya.'], ';');
        fputcsv($output, ['# Sistem akan otomatis menggabungkannya ke dalam 1 CP induk yang sama.'], ';');
        fputcsv($output, ['# =========================================================================================================================='], ';');

        // Header kolom
        fputcsv($output, ['elemen', 'deskripsi_cp', 'kode_cp', 'fase', 'materi_pokok_tp', 'deskripsi_tp', 'kode_tp'], ';');

        // Contoh Data 1 (CP 1 - Butir TP 1)
        fputcsv($output, [
            'Pemrograman Web Sisi Klien (Client-Side)',
            'Peserta didik mampu menerapkan bahasa pemrograman sisi klien (HTML5, CSS3, JavaScript modern/ES6+, dan framework antarmuka) untuk membangun antarmuka web yang responsif, dinamis, dan memenuhi kaidah UX/UI.',
            '',
            'F',
            'Struktur Semantik HTML5 & Responsive CSS Layout',
            "1. Menganalisis struktur dokumen semantik HTML5 standar.\n2. Mengembangkan antarmuka adaptif menggunakan CSS Grid dan Flexbox.",
            ''
        ], ';');

        // Contoh Data 2 (CP 1 - Butir TP 2 dengan CP yang sama)
        fputcsv($output, [
            'Pemrograman Web Sisi Klien (Client-Side)',
            'Peserta didik mampu menerapkan bahasa pemrograman sisi klien (HTML5, CSS3, JavaScript modern/ES6+, dan framework antarmuka) untuk membangun antarmuka web yang responsif, dinamis, dan memenuhi kaidah UX/UI.',
            '',
            'F',
            'Manipulasi DOM & Validasi Formulir Interaktif',
            "1. Mengimplementasikan manipulasi elemen DOM menggunakan JavaScript modern.\n2. Menerapkan validasi input data formulir di sisi klien.",
            ''
        ], ';');

        // Contoh Data 3 (CP 2 - Butir TP 1)
        fputcsv($output, [
            'Pemrograman Sisi Server & Arsitektur MVC',
            'Peserta didik mampu merancang, memprogram, menguji, dan mengamankan aplikasi web berbasis sisi server (server-side scripting) menggunakan arsitektur Model-View-Controller (MVC) dan basis data relasional.',
            '',
            'F',
            'Konsep Arsitektur MVC & Routing Web',
            "1. Memahami alur kerja Model-View-Controller (MVC).\n2. Membangun pengontrol sistem (controller) dan manajemen sesi otentikasi login.",
            ''
        ], ';');

        // Contoh Data 4 (CP 2 - Butir TP 2)
        fputcsv($output, [
            'Pemrograman Sisi Server & Arsitektur MVC',
            'Peserta didik mampu merancang, memprogram, menguji, dan mengamankan aplikasi web berbasis sisi server (server-side scripting) menggunakan arsitektur Model-View-Controller (MVC) dan basis data relasional.',
            '',
            'F',
            'Operasi CRUD Basis Data & Keamanan Web',
            "1. Mengimplementasikan operasi Create, Read, Update, Delete data dengan PDO.\n2. Menerapkan pencegahan terhadap SQL Injection, XSS, dan CSRF.",
            ''
        ], ';');

        fclose($output);
        exit();
    }

    public function quizLiveStatus() {
        header('Content-Type: application/json');
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'] ?? 0;

        $userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
        $isAdmin = in_array($userRole, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);

        $examModel = new ExamModel();
        $targetGuruId = $isAdmin ? null : $guruId;

        $susulanRequests = $examModel->getSusulanRequestsByGuru($targetGuruId);
        $hasilQuizSubmissions = $examModel->getHasilQuizListByGuru($targetGuruId);
        $quizList = $examModel->getQuizList(null, $targetGuruId);

        $pendingEssayCount = 0;
        if (!empty($hasilQuizSubmissions)) {
            foreach ($hasilQuizSubmissions as $hqItem) {
                $tEssay = (int)($hqItem['total_essay_count'] ?? 0);
                $uEssay = (int)($hqItem['ungraded_essay_count'] ?? 0);
                $isBanned = (!empty($hqItem['status_banned']) && (string)$hqItem['status_banned'] !== '0');
                if (($tEssay > 0 && $uEssay > 0) || $isBanned) {
                    $pendingEssayCount++;
                }
            }
        }

        $pendingSusulanCount = 0;
        if (!empty($susulanRequests)) {
            foreach ($susulanRequests as $srItem) {
                if (($srItem['status'] ?? '') === 'pending') {
                    $pendingSusulanCount++;
                }
            }
        }

        echo json_encode([
            'status' => true,
            'pending_essay_count' => $pendingEssayCount,
            'pending_susulan_count' => $pendingSusulanCount,
            'total_submissions' => count($hasilQuizSubmissions ?? []),
            'total_quizzes' => count($quizList ?? [])
        ]);
        exit();
    }

    public function cetakCbtReportPdf() {
        AuthHelper::requireRole(['guru', 'administrator']);
        $guru = $this->getGuruInfo();
        $guruId = $guru ? $guru['id'] : null;

        $roleName = strtolower(AuthHelper::user()['role_name'] ?? '');
        $queryGuruId = ($roleName === 'administrator') ? null : $guruId;

        $examModel = new ExamModel();
        $academicModel = new AcademicModel();

        $reportQuizId = $_GET['report_quiz_id'] ?? 'all';
        $reportKelasId = !empty($_GET['report_kelas_id']) ? (int)$_GET['report_kelas_id'] : null;

        $quizReportDetail = null;
        if ($reportQuizId !== 'all' && (int)$reportQuizId > 0) {
            $quizReportDetail = $examModel->getDetailedReportByQuiz((int)$reportQuizId);
            if ($queryGuruId !== null && $quizReportDetail && (int)($quizReportDetail['quiz']['guru_id'] ?? 0) !== (int)$queryGuruId) {
                $quizReportDetail = null;
            }
        }
        $rekapCbtMatrix = $examModel->getRekapNilaiCbtMatrixByGuru($queryGuruId, $reportKelasId);

        require_once ROOT_PATH . 'views/guru/cetak_cbt_report.php';
    }

    public function exportCbtReportExcel() {
        AuthHelper::requireRole(['guru', 'administrator']);
        $guru = $this->getGuruInfo();
        $guruId = $guru ? $guru['id'] : null;

        $roleName = strtolower(AuthHelper::user()['role_name'] ?? '');
        $queryGuruId = ($roleName === 'administrator') ? null : $guruId;

        $examModel = new ExamModel();

        $reportQuizId = $_GET['report_quiz_id'] ?? 'all';
        $reportKelasId = !empty($_GET['report_kelas_id']) ? (int)$_GET['report_kelas_id'] : null;

        header('Content-Type: text/csv; charset=utf-8');

        if ($reportQuizId !== 'all' && (int)$reportQuizId > 0) {
            $quizReport = $examModel->getDetailedReportByQuiz((int)$reportQuizId);
            if ($queryGuruId !== null && $quizReport && (int)($quizReport['quiz']['guru_id'] ?? 0) !== (int)$queryGuruId) {
                FlashHelper::setError('Anda tidak memiliki hak akses untuk mengunduh rekap kuis ini.');
                header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                exit();
            }
            $qInfo = $quizReport['quiz'] ?? [];
            $quizTitle = preg_replace('/[^A-Za-z0-9_-]/', '_', $qInfo['judul'] ?? 'Quiz');
            $fileName = "Rekap_Nilai_CBT_{$quizTitle}_" . date('Ymd_His') . ".csv";

            header('Content-Disposition: attachment; filename=' . $fileName);
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fwrite($output, "sep=;\n");

            fputcsv($output, ['LAPORAN REKAPITULASI HASIL EVALUASI QUIZ / UJIAN CBT'], ';');
            fputcsv($output, ['SMK MUTHIA HARAPAN CICALENGKA'], ';');
            fputcsv($output, ['Judul Paket', $qInfo['judul'] ?? '-'], ';');
            fputcsv($output, ['Mata Pelajaran', $qInfo['nama_mapel'] ?? '-'], ';');
            fputcsv($output, ['Kelas', $qInfo['nama_kelas'] ?? '-'], ';');
            fputcsv($output, ['Tanggal Eksport', date('d/m/Y H:i:s')], ';');
            fputcsv($output, [''], ';');

            fputcsv($output, ['No', 'NISN', 'NIS', 'Nama Siswa', 'Kelas', 'Status Pengerjaan', 'Attempt', 'Waktu Selesai', 'Nilai Quiz', 'Keterangan Status'], ';');

            $no = 1;
            foreach (($quizReport['report_data'] ?? []) as $item) {
                $st = $item['siswa'];
                $sub = $item['submission'];
                $score = $item['score'];
                $status = $item['status'];

                $statusLabel = 'Belum Ada Data';
                if ($status === 'lulus') $statusLabel = 'LULUS (>= KKM 70)';
                elseif ($status === 'tidak_lulus') $statusLabel = 'BELUM TUNTAS';
                elseif ($status === 'menunggu_essay') $statusLabel = 'PERLU KOREKSI ESSAY';
                elseif ($status === 'belum_mengerjakan') $statusLabel = 'BELUM MENGERJAKAN';

                fputcsv($output, [
                    $no++,
                    $st['nisn'] ?: '-',
                    $st['nis'] ?: '-',
                    $st['nama_lengkap'],
                    $st['nama_kelas'],
                    $sub ? 'Sudah Mengerjakan' : 'Belum Mengerjakan',
                    $sub ? ($sub['attempt_count'] . 'x') : '-',
                    $sub && !empty($sub['finished_at']) ? date('d/m/Y H:i', strtotime($sub['finished_at'])) : '-',
                    $score !== null ? number_format($score, 1) : '-',
                    $statusLabel
                ], ';');
            }
            fclose($output);
            exit();

        } else {
            $rekapMatrix = $examModel->getRekapNilaiCbtMatrixByGuru($queryGuruId, $reportKelasId);
            $quizzes = $rekapMatrix['quizzes'] ?? [];
            $matrix = $rekapMatrix['matrix'] ?? [];

            $fileName = "Rekapitulasi_Matrix_Nilai_CBT_" . date('Ymd_His') . ".csv";
            header('Content-Disposition: attachment; filename=' . $fileName);
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fwrite($output, "sep=;\n");

            fputcsv($output, ['REKAPITULASI MATRIX HASIL SELURUH QUIZ & UJIAN CBT'], ';');
            fputcsv($output, ['SMK MUTHIA HARAPAN CICALENGKA'], ';');
            fputcsv($output, ['Tanggal Eksport', date('d/m/Y H:i:s')], ';');
            fputcsv($output, [''], ';');

            $headerRow = ['No', 'NISN', 'NIS', 'Nama Siswa', 'Kelas'];
            foreach ($quizzes as $qz) {
                $headerRow[] = $qz['judul'] . ' (' . strtoupper($qz['kategori'] ?? 'Kuis') . ')';
            }
            $headerRow[] = 'RATA-RATA NILAI AKHIR';
            $headerRow[] = 'PREDIKAT';

            fputcsv($output, $headerRow, ';');

            $no = 1;
            foreach ($matrix as $rowM) {
                $stM = $rowM['siswa'];
                $scMap = $rowM['scores'];
                $finalAvg = $rowM['final_avg'];
                $predikat = $rowM['predikat'];
                $predikatLabel = $rowM['predikat_label'];

                $dataRow = [
                    $no++,
                    $stM['nisn'] ?: '-',
                    $stM['nis'] ?: '-',
                    $stM['nama_lengkap'],
                    $stM['nama_kelas']
                ];

                foreach ($quizzes as $qz) {
                    $qId = $qz['id'];
                    $val = $scMap[$qId] ?? null;
                    $dataRow[] = $val !== null ? number_format($val, 1) : '-';
                }

                $dataRow[] = number_format($finalAvg, 1);
                $dataRow[] = "{$predikat} ({$predikatLabel})";

                fputcsv($output, $dataRow, ';');
            }
            fclose($output);
            exit();
        }
    }

    public function approve_tugas_susulan() {
        $this->tugas();
    }
    public function reject_tugas_susulan() {
        $this->tugas();
    }
    public function approve_susulan() {
        $this->tugas();
    }
    public function reject_susulan() {
        $this->tugas();
    }
    public function susulan_requests() {
        $this->tugas();
    }

    /**
     * Hasil Supervisi Akademik & Penilaian Kinerja Guru oleh Kepala Sekolah
     */
    public function supervisi() {
        require_once ROOT_PATH . 'models/ReportModel.php';
        $guru = $this->getGuruInfo();
        $guruId = (int)($guru['id'] ?? 0);

        $reportModel = new ReportModel();
        $supervisiList = $reportModel->getSupervisiList($guruId);

        $totalSupervisi = count($supervisiList);
        $avgSupervisi = $totalSupervisi > 0 ? round(array_sum(array_column($supervisiList, 'nilai_akhir')) / $totalSupervisi, 1) : 0;
        $supervisiTerbaru = !empty($supervisiList) ? $supervisiList[0] : null;

        require_once ROOT_PATH . 'views/guru/supervisi.php';
    }

    public function supervisiAkademik() {
        $this->supervisi();
    }

    /**
     * Cetak Lembar Supervisi Akademik & Instrumen Penilaian Kinerja Guru (PDF)
     */
    public function cetakSupervisi() {
        require_once ROOT_PATH . 'models/ReportModel.php';
        require_once ROOT_PATH . 'helpers/PdfHelper.php';

        $guru = $this->getGuruInfo();
        $guruId = (int)($guru['id'] ?? 0);

        $reportModel = new ReportModel();
        $id = (int)($_GET['id'] ?? 0);
        $type = $_GET['type'] ?? '';

        $supervisiList = $reportModel->getSupervisiList($guruId);

        // Ambil data resmi Kepala Sekolah dari konfigurasi sekolah (settings.json)
        $settingsPath = ROOT_PATH . 'config/settings.json';
        $appSettings = [];
        if (file_exists($settingsPath)) {
            $appSettings = json_decode(file_get_contents($settingsPath), true) ?: [];
        }

        $namaKepsekResmi = !empty($appSettings['kepala_sekolah']) ? $appSettings['kepala_sekolah'] : 'H. ASEP SAEPULLOH, S. Ag';
        $nipKepsekResmi = !empty($appSettings['nip_kepala_sekolah']) ? $appSettings['nip_kepala_sekolah'] : ($appSettings['nip_kepsek'] ?? ($appSettings['nip'] ?? ''));

        // Jika tidak ada ID spesifik dan bukan mode rekap, default ke supervisi terbaru jika ada
        if ($id <= 0 && $type !== 'rekap' && !empty($supervisiList)) {
            $id = (int)$supervisiList[0]['id'];
        }

        if ($id > 0 && $type !== 'rekap') {
            // Cetak Lembar Instrumen Supervisi Resmi (Per Berkas)
            $supervisi = $reportModel->getSupervisiById($id);
            if (!$supervisi || (int)$supervisi['guru_id'] !== $guruId) {
                echo "<script>alert('Berkas lembar supervisi tidak ditemukan atau Anda tidak memiliki akses.'); window.close();</script>";
                exit();
            }

            $title = "LEMBAR HASIL SUPERVISI AKADEMIK & PENILAIAN KINERJA GURU";
            $subtitle = "Instrumen Observasi Pembelajaran & Evaluasi Mutu Pendidik";

            $tglObservasi = date('d F Y', strtotime($supervisi['tanggal_supervisi']));
            $namaGuru = htmlspecialchars($supervisi['nama_guru']);
            $nip = htmlspecialchars($supervisi['nip'] ?? '-');
            $mapel = htmlspecialchars($supervisi['nama_mapel'] ?? 'Semua Mata Pelajaran');
            $kelas = htmlspecialchars($supervisi['nama_kelas'] ?? 'Rombel Umum');
            $penilai = (!empty($supervisi['nama_kepsek']) && $supervisi['nama_kepsek'] !== 'Kepala Sekolah')
                ? htmlspecialchars($supervisi['nama_kepsek'])
                : htmlspecialchars($namaKepsekResmi);

            $skorP = (float)$supervisi['skor_perencanaan'];
            $skorL = (float)$supervisi['skor_pelaksanaan'];
            $skorE = (float)$supervisi['skor_evaluasi'];
            $skorD = (float)$supervisi['skor_kedisiplinan'];
            $nilaiAkhir = (float)$supervisi['nilai_akhir'];
            $predikat = htmlspecialchars($supervisi['predikat'] ?? '-');

            $kekuatan = nl2br(htmlspecialchars($supervisi['catatan_kekuatan'] ?: 'Modul ajar dan perangkat pembelajaran tersusun rapi serta KBM berlangsung interaktif.'));
            $perbaikan = nl2br(htmlspecialchars($supervisi['catatan_perbaikan'] ?: 'Tingkatkan keteraturan dokumentasi tindak lanjut dan umpan balik tugas siswa di LMS.'));
            $rekomendasi = nl2br(htmlspecialchars($supervisi['rekomendasi_tindak_lanjut'] ?: 'Pertahankan mutu KBM dan terus kembangkan media pembelajaran berbasis digital.'));

            $content = "
            <table style='width:100%; border:none; margin-bottom:15px; font-size:12px;'>
                <tr>
                    <td style='width:18%; border:none; padding:4px 0;'><b>Nama Guru</b></td>
                    <td style='width:2%; border:none; padding:4px 0;'>:</td>
                    <td style='width:35%; border:none; padding:4px 0;'><b>{$namaGuru}</b></td>
                    <td style='width:18%; border:none; padding:4px 0;'><b>Hari / Tanggal</b></td>
                    <td style='width:2%; border:none; padding:4px 0;'>:</td>
                    <td style='width:25%; border:none; padding:4px 0;'>{$tglObservasi}</td>
                </tr>
                <tr>
                    <td style='border:none; padding:4px 0;'><b>NIP / Identitas</b></td>
                    <td style='border:none; padding:4px 0;'>:</td>
                    <td style='border:none; padding:4px 0;'>{$nip}</td>
                    <td style='border:none; padding:4px 0;'><b>Kelas / Rombel</b></td>
                    <td style='border:none; padding:4px 0;'>:</td>
                    <td style='border:none; padding:4px 0;'>{$kelas}</td>
                </tr>
                <tr>
                    <td style='border:none; padding:4px 0;'><b>Mata Pelajaran</b></td>
                    <td style='border:none; padding:4px 0;'>:</td>
                    <td style='border:none; padding:4px 0;'>{$mapel}</td>
                    <td style='border:none; padding:4px 0;'><b>Supervisor / Penilai</b></td>
                    <td style='border:none; padding:4px 0;'>:</td>
                    <td style='border:none; padding:4px 0;'><b>{$penilai}</b></td>
                </tr>
            </table>

            <div style='font-size:13px; font-weight:bold; margin-top:10px; margin-bottom:6px; color:#0f172a;'>I. RUBRIK PENILAIAN KINERJA PEMBELAJARAN (4 PILAR STANDAR)</div>
            <table border='1' cellpadding='7' cellspacing='0' style='width:100%; border-collapse:collapse; margin-bottom:15px;'>
                <thead>
                    <tr style='background-color:#f1f5f9;'>
                        <th style='width:35px; text-align:center;'>No</th>
                        <th>Komponen Pilar Supervisi</th>
                        <th>Indikator & Aspek Pengamatan</th>
                        <th style='width:70px; text-align:center;'>Bobot</th>
                        <th style='width:70px; text-align:center;'>Skor (0-100)</th>
                        <th style='width:80px; text-align:center;'>Skor Terbobot</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style='text-align:center;'>1</td>
                        <td><b>Perencanaan Pembelajaran</b></td>
                        <td>Kesiapan modul ajar/RPP, keselarasan CP/TP, bahan ajar digital, LKPD, dan media belajar LMS</td>
                        <td style='text-align:center;'>25%</td>
                        <td style='text-align:center;'><b>{$skorP}</b></td>
                        <td style='text-align:center;'>" . number_format($skorP * 0.25, 2) . "</td>
                    </tr>
                    <tr>
                        <td style='text-align:center;'>2</td>
                        <td><b>Pelaksanaan Pembelajaran</b></td>
                        <td>Penguasaan materi, metode student-centered, interaktivitas, motivasi siswa, dan pemanfaatan LMS</td>
                        <td style='text-align:center;'>35%</td>
                        <td style='text-align:center;'><b>{$skorL}</b></td>
                        <td style='text-align:center;'>" . number_format($skorL * 0.35, 2) . "</td>
                    </tr>
                    <tr>
                        <td style='text-align:center;'>3</td>
                        <td><b>Evaluasi & Penilaian</b></td>
                        <td>Pelaksanaan tugas terstruktur, paket kuis CBT, rubrik asesmen, dan ketertiban e-rapor</td>
                        <td style='text-align:center;'>25%</td>
                        <td style='text-align:center;'><b>{$skorE}</b></td>
                        <td style='text-align:center;'>" . number_format($skorE * 0.25, 2) . "</td>
                    </tr>
                    <tr>
                        <td style='text-align:center;'>4</td>
                        <td><b>Kedisiplinan & Presensi</b></td>
                        <td>Ketepatan kehadiran mengajar, presensi selfie geotagging, kepatuhan jadwal dan jam mengajar</td>
                        <td style='text-align:center;'>15%</td>
                        <td style='text-align:center;'><b>{$skorD}</b></td>
                        <td style='text-align:center;'>" . number_format($skorD * 0.15, 2) . "</td>
                    </tr>
                    <tr style='background-color:#f8fafc; font-size:13px;'>
                        <td colspan='4' style='text-align:right; font-weight:bold;'>NILAI AKHIR KINERJA GURU:</td>
                        <td colspan='2' style='text-align:center; font-weight:bold; color:#0d6efd; font-size:15px;'>" . number_format($nilaiAkhir, 2) . "</td>
                    </tr>
                    <tr style='background-color:#f1f5f9; font-size:12px;'>
                        <td colspan='4' style='text-align:right; font-weight:bold;'>PREDIKAT KINERJA / KUALIFIKASI:</td>
                        <td colspan='2' style='text-align:center; font-weight:bold; text-transform:uppercase;'>{$predikat}</td>
                    </tr>
                </tbody>
            </table>

            <div style='font-size:13px; font-weight:bold; margin-top:15px; margin-bottom:6px; color:#0f172a;'>II. CATATAN KUALITATIF & PEMBINAAN KEPALA SEKOLAH</div>
            <table border='1' cellpadding='8' cellspacing='0' style='width:100%; border-collapse:collapse; margin-bottom:10px;'>
                <tr>
                    <td style='width:30%; background-color:#f8fafc;'><b>1. Kekuatan & Keunggulan Guru</b></td>
                    <td>{$kekuatan}</td>
                </tr>
                <tr>
                    <td style='background-color:#f8fafc;'><b>2. Aspek Perlu Peningkatan</b></td>
                    <td>{$perbaikan}</td>
                </tr>
                <tr>
                    <td style='background-color:#fffbeb;'><b>3. Rekomendasi Tindak Lanjut</b></td>
                    <td style='background-color:#fffdf5;'><b>{$rekomendasi}</b></td>
                </tr>
            </table>
            ";

            $dateNow = date('d F Y');
            $nipKepsekHtml = !empty($nipKepsekResmi) ? "<small style='color:#555;'>NIP: " . htmlspecialchars($nipKepsekResmi) . "</small>" : "<small style='color:#555;'>Pimpinan Satuan Pendidikan</small>";
            $customFooter = "
            <table class='footer-table'>
                <tr>
                    <td style='width:50%; text-align:center; vertical-align:top;'>
                        <p style='margin-bottom:0;'>Guru yang Disupervisi,</p>
                        <p style='margin-top:60px; margin-bottom:0;'><b><u>{$namaGuru}</u></b></p>
                        <small style='color:#555;'>NIP: {$nip}</small>
                    </td>
                    <td style='width:50%; text-align:center; vertical-align:top;'>
                        <p style='margin-bottom:0;'>Cicalengka, {$dateNow}<br>Kepala Sekolah / Supervisor,</p>
                        <p style='margin-top:45px; margin-bottom:0;'><b><u>{$penilai}</u></b></p>
                        {$nipKepsekHtml}
                    </td>
                </tr>
            </table>
            ";

            echo PdfHelper::renderReportPage($title, $subtitle, $content, $customFooter);
            exit();

        } else {
            // Cetak Rekapitulasi Riwayat Supervisi Guru
            $namaGuru = htmlspecialchars($guru['nama_lengkap'] ?? 'Guru');
            $nip = htmlspecialchars($guru['nip'] ?? '-');

            $title = "REKAPITULASI HASIL SUPERVISI AKADEMIK GURU";
            $subtitle = "Nama Pengajar: {$namaGuru} | NIP: {$nip} | Satuan Pendidikan: SMK Muthia Harapan Cicalengka";

            $table = "<table border='1' cellpadding='8' cellspacing='0' style='width:100%; border-collapse:collapse;'>
                <thead>
                    <tr style='background-color:#f1f5f9; text-align:left;'>
                        <th style='width:30px; text-align:center;'>No</th>
                        <th>Tanggal</th>
                        <th>Mapel & Kelas</th>
                        <th>Supervisor (Kepala Sekolah)</th>
                        <th style='text-align:center;'>Skor 4 Pilar (P/L/E/D)</th>
                        <th style='text-align:center;'>Nilai Akhir</th>
                        <th style='text-align:center;'>Predikat</th>
                        <th>Rekomendasi Tindak Lanjut</th>
                    </tr>
                </thead><tbody>";

            if (empty($supervisiList)) {
                $table .= "<tr><td colspan='8' style='text-align:center; padding:25px;'>Belum ada riwayat lembar supervisi akademik yang diterbitkan.</td></tr>";
            } else {
                foreach ($supervisiList as $i => $row) {
                    $num = $i + 1;
                    $tgl = date('d/m/Y', strtotime($row['tanggal_supervisi']));
                    $mapelKls = htmlspecialchars(($row['nama_mapel'] ?? '-') . ' (' . ($row['nama_kelas'] ?? '-') . ')');
                    $kepsekName = (!empty($row['nama_kepsek']) && $row['nama_kepsek'] !== 'Kepala Sekolah')
                        ? htmlspecialchars($row['nama_kepsek'])
                        : htmlspecialchars($namaKepsekResmi);
                    $pilar = "P:{$row['skor_perencanaan']} | L:{$row['skor_pelaksanaan']} | E:{$row['skor_evaluasi']} | D:{$row['skor_kedisiplinan']}";
                    $nilai = number_format((float)$row['nilai_akhir'], 1);
                    $predikat = htmlspecialchars($row['predikat'] ?? '-');
                    $rekom = htmlspecialchars($row['rekomendasi_tindak_lanjut'] ?? '-');

                    $table .= "<tr>
                        <td style='text-align:center;'>{$num}</td>
                        <td>{$tgl}</td>
                        <td>{$mapelKls}</td>
                        <td>{$kepsekName}</td>
                        <td style='text-align:center; font-size:11px;'>{$pilar}</td>
                        <td style='text-align:center;'><b>{$nilai}</b></td>
                        <td style='text-align:center;'><b>{$predikat}</b></td>
                        <td style='font-size:11px;'>{$rekom}</td>
                    </tr>";
                }
            }
            $table .= "</tbody></table>";

            $dateNow = date('d F Y');
            $nipKepsekHtml = !empty($nipKepsekResmi) ? "<small style='color:#555;'>NIP: " . htmlspecialchars($nipKepsekResmi) . "</small>" : "<small style='color:#555;'>Pimpinan Satuan Pendidikan</small>";
            $customFooter = "
            <table class='footer-table'>
                <tr>
                    <td style='width:50%; text-align:center; vertical-align:top;'>
                        <p style='margin-bottom:0;'>Mengetahui Pengajar,</p>
                        <p style='margin-top:60px; margin-bottom:0;'><b><u>{$namaGuru}</u></b></p>
                        <small style='color:#555;'>NIP: {$nip}</small>
                    </td>
                    <td style='width:50%; text-align:center; vertical-align:top;'>
                        <p style='margin-bottom:0;'>Cicalengka, {$dateNow}<br>Kepala Sekolah,</p>
                        <p style='margin-top:45px; margin-bottom:0;'><b><u>" . htmlspecialchars($namaKepsekResmi) . "</u></b></p>
                        {$nipKepsekHtml}
                    </td>
                </tr>
            </table>
            ";

            echo PdfHelper::renderReportPage($title, $subtitle, $table, $customFooter);
            exit();
        }
    }

    /**
     * Modul Penyusunan Capaian Pembelajaran (CP) & Tujuan Pembelajaran (TP) oleh Guru
     */
    public function cptp() {
        $guru = $this->getGuruInfo();
        $guruId = (int)($guru['id'] ?? 0);
        $currModel = new CurriculumModel();
        $academicModel = new AcademicModel();

        // Handle POST Actions (Create, Update, Delete CP & TP)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('Sesi keamanan tidak valid (CSRF token invalid). Silakan muat ulang.');
                header('Location: ' . BASE_URL . 'index.php?url=guru/cptp');
                exit();
            }

            $action = $_POST['action'] ?? '';

            if ($action === 'create_cp') {
                $kurId = (int)$_POST['kurikulum_id'];
                $mapelId = (int)$_POST['mapel_id'];
                $kodeCp = Security::sanitize($_POST['kode_cp'] ?? '');
                if (empty($kodeCp)) {
                    $kodeCp = $currModel->generateNextCPCode($kurId, $mapelId);
                }

                $res = $currModel->addCP([
                    'kurikulum_id' => $kurId,
                    'mapel_id' => $mapelId,
                    'fase_id' => !empty($_POST['fase_id']) ? (int)$_POST['fase_id'] : null,
                    'guru_id' => $guruId,
                    'kode_cp' => $kodeCp,
                    'elemen' => Security::sanitize($_POST['elemen'] ?? ''),
                    'deskripsi' => Security::sanitize($_POST['deskripsi'] ?? '')
                ]);
                if ($res['status']) FlashHelper::setSuccess($res['message']);
                else FlashHelper::setError($res['message']);

            } elseif ($action === 'update_cp') {
                $id = (int)$_POST['id'];
                $res = $currModel->updateCP($id, [
                    'kurikulum_id' => !empty($_POST['kurikulum_id']) ? (int)$_POST['kurikulum_id'] : null,
                    'mapel_id' => !empty($_POST['mapel_id']) ? (int)$_POST['mapel_id'] : null,
                    'fase_id' => !empty($_POST['fase_id']) ? (int)$_POST['fase_id'] : null,
                    'guru_id' => $guruId,
                    'kode_cp' => Security::sanitize($_POST['kode_cp']),
                    'elemen' => Security::sanitize($_POST['elemen'] ?? ''),
                    'deskripsi' => Security::sanitize($_POST['deskripsi'] ?? '')
                ]);
                if ($res['status']) FlashHelper::setSuccess($res['message']);
                else FlashHelper::setError($res['message']);

            } elseif ($action === 'delete_cp') {
                $id = (int)$_POST['id'];
                $res = $currModel->deleteCP($id);
                if ($res['status']) FlashHelper::setSuccess($res['message']);
                else FlashHelper::setError($res['message']);

            } elseif ($action === 'create_tp') {
                $cpId = (int)$_POST['cp_id'];
                $kodeTp = Security::sanitize($_POST['kode_tp'] ?? '');
                if (empty($kodeTp)) {
                    $kodeTp = $currModel->generateNextTPCode($cpId);
                }

                $res = $currModel->addTP([
                    'cp_id' => $cpId,
                    'guru_id' => $guruId,
                    'kode_tp' => $kodeTp,
                    'materi_pokok' => Security::sanitize($_POST['materi_pokok'] ?? ''),
                    'deskripsi' => Security::sanitize($_POST['deskripsi'] ?? '')
                ]);
                if ($res['status']) FlashHelper::setSuccess($res['message']);
                else FlashHelper::setError($res['message']);

            } elseif ($action === 'update_tp') {
                $id = (int)$_POST['id'];
                $res = $currModel->updateTP($id, [
                    'cp_id' => !empty($_POST['cp_id']) ? (int)$_POST['cp_id'] : null,
                    'guru_id' => $guruId,
                    'kode_tp' => Security::sanitize($_POST['kode_tp']),
                    'materi_pokok' => Security::sanitize($_POST['materi_pokok'] ?? ''),
                    'deskripsi' => Security::sanitize($_POST['deskripsi'] ?? '')
                ]);
                if ($res['status']) FlashHelper::setSuccess($res['message']);
                else FlashHelper::setError($res['message']);

            } elseif ($action === 'delete_tp') {
                $id = (int)$_POST['id'];
                $res = $currModel->deleteTP($id);
                if ($res['status']) FlashHelper::setSuccess($res['message']);
                else FlashHelper::setError($res['message']);

            } elseif ($action === 'archive_tp') {
                $id = (int)$_POST['id'];
                $assessModel = new AssessmentModel();
                $res = $assessModel->archiveTp($id);
                if ($res['status']) FlashHelper::setSuccess($res['message']);
                else FlashHelper::setError($res['message']);

            } elseif ($action === 'archive_cp') {
                $id = (int)$_POST['id'];
                $assessModel = new AssessmentModel();
                $res = $assessModel->archiveCp($id);
                if ($res['status']) FlashHelper::setSuccess($res['message']);
                else FlashHelper::setError($res['message']);

            } elseif ($action === 'save_kktp') {
                $tpId = (int)$_POST['tp_id'];
                $metode = Security::sanitize($_POST['metode'] ?? 'interval_nilai');
                
                if ($metode === 'rubrik') {
                    $nilaiMin = floatval(!empty($_POST['rubrik_nilai_min']) ? $_POST['rubrik_nilai_min'] : ($_POST['nilai_minimum'] ?? 75.00));
                    $deskripsiKriteria = Security::sanitize(!empty($_POST['rubrik_deskripsi']) ? $_POST['rubrik_deskripsi'] : ($_POST['deskripsi_kriteria'] ?? ''));
                } else {
                    $nilaiMin = floatval($_POST['nilai_minimum'] ?? 75.00);
                    $deskripsiKriteria = Security::sanitize($_POST['deskripsi_kriteria'] ?? '');
                }
                $targetInd = (int)($_POST['target_indikator_count'] ?? 0);

                // Parse indikator jika ada
                $indikatorList = [];
                if (!empty($_POST['indikator_nama']) && is_array($_POST['indikator_nama'])) {
                    foreach ($_POST['indikator_nama'] as $idx => $nama) {
                        $nama = trim($nama);
                        if (!empty($nama)) {
                            $indikatorList[] = [
                                'nama_indikator' => Security::sanitize($nama),
                                'deskripsi_kriteria' => Security::sanitize($_POST['indikator_desc'][$idx] ?? ''),
                                'bobot' => floatval($_POST['indikator_bobot'][$idx] ?? 1.00)
                            ];
                        }
                    }
                }

                $assessModel = new AssessmentModel();
                $res = $assessModel->saveKktp($tpId, [
                    'metode' => $metode,
                    'nilai_minimum' => $nilaiMin,
                    'target_indikator_count' => $targetInd,
                    'deskripsi_kriteria' => $deskripsiKriteria,
                    'indikator' => $indikatorList
                ]);
                if ($res['status']) FlashHelper::setSuccess($res['message']);
                else FlashHelper::setError($res['message']);

            } elseif ($action === 'copy_tp') {
                $sourceCpId = (int)$_POST['source_cp_id'];
                $targetCpId = (int)$_POST['target_cp_id'];
                $assessModel = new AssessmentModel();
                $res = $assessModel->copyTp($sourceCpId, $targetCpId, $guruId);
                if ($res['status']) FlashHelper::setSuccess($res['message']);
                else FlashHelper::setError($res['message']);
            } elseif ($action === 'import_cptp_excel') {
                $kurId = (int)($_POST['kurikulum_id'] ?? 0);
                $mapelId = (int)($_POST['mapel_id'] ?? 0);
                $defaultFaseId = !empty($_POST['fase_id']) ? (int)$_POST['fase_id'] : null;

                if ($kurId <= 0 || $mapelId <= 0) {
                    FlashHelper::setError('Silakan tentukan Kurikulum dan Mata Pelajaran tujuan import.');
                } elseif (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
                    FlashHelper::setError('Silakan pilih berkas file template Excel/CSV yang valid untuk diunggah.');
                } else {
                    $tmpPath = $_FILES['file_excel']['tmp_name'];
                    $fileName = $_FILES['file_excel']['name'];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if (!in_array($fileExt, ['csv', 'txt', 'xls', 'xlsx'])) {
                        FlashHelper::setError('Format berkas tidak didukung. Harap gunakan berkas template Excel berformat .csv atau .xlsx/.xls.');
                    } else {
                        // Baca file CSV / Excel
                        $content = file_get_contents($tmpPath);
                        $delimiter = (substr_count($content, ';') >= substr_count($content, ',')) ? ';' : ',';
                        if (substr_count($content, "\t") > substr_count($content, $delimiter)) {
                            $delimiter = "\t";
                        }

                        $handle = fopen($tmpPath, 'r');
                        if (!$handle) {
                            FlashHelper::setError('Gagal membaca isi berkas Excel.');
                        } else {
                            $headerRow = null;
                            $rows = [];

                            while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
                                if (empty($line)) continue;
                                $firstCell = strtolower(trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $line[0] ?? '')));
                                if ($firstCell === 'sep=' || strpos($firstCell, '#') === 0 || strpos($firstCell, '//') === 0) {
                                    continue;
                                }

                                if (!$headerRow) {
                                    $headerRow = array_map(function($h) {
                                        return strtolower(trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $h)));
                                    }, $line);
                                    continue;
                                }

                                // Ambil baris data
                                if (count(array_filter($line)) === 0) continue;
                                $rowAssoc = [];
                                foreach ($headerRow as $idx => $colName) {
                                    $rowAssoc[$colName] = isset($line[$idx]) ? trim($line[$idx]) : '';
                                }
                                $rows[] = $rowAssoc;
                            }
                            fclose($handle);

                            if (empty($rows)) {
                                FlashHelper::setError('Berkas Excel tidak memuat baris data CP & TP yang dapat diproses.');
                            } else {
                                $dbConn = Database::getConnection();
                                
                                // Ambil semua fase untuk auto-matching
                                $allFases = $currModel->getAllFase();
                                $faseMap = [];
                                foreach ($allFases as $f) {
                                    if ($f['kurikulum_id'] == $kurId) {
                                        $faseMap[strtoupper(trim($f['kode']))] = (int)$f['id'];
                                    }
                                }

                                // Kelompokkan TP berdasarkan CP
                                $cpGroups = [];
                                foreach ($rows as $r) {
                                    $elemen = $r['elemen'] ?? ($r['nama_elemen'] ?? '');
                                    $deskCp = $r['deskripsi_cp'] ?? ($r['deskripsi'] ?? ($r['cp'] ?? ''));
                                    $kodeCp = strtoupper(trim($r['kode_cp'] ?? ''));
                                    $faseStr = strtoupper(trim($r['fase'] ?? ($r['kode_fase'] ?? '')));
                                    $materiTp = $r['materi_pokok_tp'] ?? ($r['materi_pokok'] ?? ($r['materi'] ?? ''));
                                    $deskTp = $r['deskripsi_tp'] ?? ($r['tujuan_pembelajaran'] ?? ($r['tp'] ?? ''));
                                    $kodeTp = strtoupper(trim($r['kode_tp'] ?? ''));

                                    if (empty($elemen) && empty($deskCp) && empty($deskTp)) {
                                        continue;
                                    }

                                    // Tentukan fase_id untuk baris ini
                                    $rowFaseId = $defaultFaseId;
                                    if (!empty($faseStr) && isset($faseMap[$faseStr])) {
                                        $rowFaseId = $faseMap[$faseStr];
                                    }

                                    $groupKey = !empty($kodeCp) ? $kodeCp : md5($elemen . '|||' . $deskCp);
                                    if (!isset($cpGroups[$groupKey])) {
                                        $cpGroups[$groupKey] = [
                                            'elemen' => !empty($elemen) ? $elemen : 'Materi Pembelajaran',
                                            'deskripsi' => !empty($deskCp) ? $deskCp : $elemen,
                                            'kode_cp' => $kodeCp,
                                            'fase_id' => $rowFaseId,
                                            'tp_list' => []
                                        ];
                                    }

                                    if (!empty($deskTp)) {
                                        $cpGroups[$groupKey]['tp_list'][] = [
                                            'materi_pokok' => !empty($materiTp) ? $materiTp : $elemen,
                                            'deskripsi' => $deskTp,
                                            'kode_tp' => $kodeTp
                                        ];
                                    }
                                }

                                $cpCreated = 0;
                                $tpCreated = 0;

                                foreach ($cpGroups as $g) {
                                    $targetKodeCp = $g['kode_cp'];
                                    if (empty($targetKodeCp)) {
                                        $targetKodeCp = $currModel->generateNextCPCode($kurId, $mapelId);
                                    }

                                    // Cek apakah CP dengan kode atau deskripsi yang sama sudah ada di DB untuk mapel ini
                                    $chkCp = $dbConn->prepare("SELECT id FROM capaian_pembelajaran WHERE kurikulum_id = ? AND mapel_id = ? AND (kode_cp = ? OR (elemen = ? AND deskripsi = ?)) LIMIT 1");
                                    $chkCp->execute([$kurId, $mapelId, $targetKodeCp, $g['elemen'], $g['deskripsi']]);
                                    $existingCp = $chkCp->fetch(PDO::FETCH_ASSOC);

                                    $cpId = 0;
                                    if ($existingCp) {
                                        $cpId = (int)$existingCp['id'];
                                    } else {
                                        $resCp = $currModel->addCP([
                                            'kurikulum_id' => $kurId,
                                            'mapel_id' => $mapelId,
                                            'fase_id' => $g['fase_id'],
                                            'guru_id' => $guruId,
                                            'kode_cp' => $targetKodeCp,
                                            'elemen' => $g['elemen'],
                                            'deskripsi' => $g['deskripsi']
                                        ]);
                                        if ($resCp['status'] && !empty($resCp['id'])) {
                                            $cpId = (int)$resCp['id'];
                                            $cpCreated++;
                                        }
                                    }

                                    if ($cpId > 0 && !empty($g['tp_list'])) {
                                        foreach ($g['tp_list'] as $tpItem) {
                                            $targetKodeTp = $tpItem['kode_tp'];
                                            if (empty($targetKodeTp)) {
                                                $targetKodeTp = $currModel->generateNextTPCode($cpId);
                                            }

                                            $resTp = $currModel->addTP([
                                                'cp_id' => $cpId,
                                                'guru_id' => $guruId,
                                                'kode_tp' => $targetKodeTp,
                                                'materi_pokok' => $tpItem['materi_pokok'],
                                                'deskripsi' => $tpItem['deskripsi']
                                            ]);
                                            if ($resTp['status']) {
                                                $tpCreated++;
                                            }
                                        }
                                    }
                                }

                                if ($cpCreated > 0 || $tpCreated > 0) {
                                    FlashHelper::setSuccess("Berhasil men-generate {$cpCreated} Capaian Pembelajaran (CP) dan {$tpCreated} Tujuan Pembelajaran (TP) dari berkas template Excel! Anda dapat menyesuaikan atau mengedit datanya secara manual kapan saja.");
                                } else {
                                    FlashHelper::setError("Tidak ada data CP atau TP baru yang dapat disimpan. Kemungkinan data dari template Excel sudah terdaftar sebelumnya.");
                                }
                            }
                        }
                    }
                }
            }

            $extra = '';
            if (!empty($_POST['filter_mapel_id'])) $extra .= '&filter_mapel_id=' . (int)$_POST['filter_mapel_id'];
            if (!empty($_POST['filter_kurikulum_id'])) $extra .= '&filter_kurikulum_id=' . (int)$_POST['filter_kurikulum_id'];
            if (!empty($_POST['filter_fase_id'])) $extra .= '&filter_fase_id=' . (int)$_POST['filter_fase_id'];

            header('Location: ' . BASE_URL . 'index.php?url=guru/cptp' . $extra);
            exit();
        }

        $assessmentModel = new AssessmentModel();

        // Mata Pelajaran yang diampu guru ini
        $teacherMapelList = $academicModel->getMapelByGuru($guruId);
        if (empty($teacherMapelList)) {
            $teacherMapelList = $academicModel->getMapel(); // Fallback jika belum diplot jadwal
        }
        $allMapelList = $academicModel->getMapel();

        // Kelas yang diajar guru
        $teacherKelasList = $academicModel->getKelasByGuru($guruId);

        // Master Kurikulum & Fase
        $kurikulumList = $currModel->getAllKurikulum();
        $allFaseList = $currModel->getAllFase();

        // Filter state
        $filterMapelId = isset($_GET['filter_mapel_id']) && $_GET['filter_mapel_id'] !== '' ? (int)$_GET['filter_mapel_id'] : null;
        $filterKurId = !empty($_GET['filter_kurikulum_id']) ? (int)$_GET['filter_kurikulum_id'] : ($kurikulumList[0]['id'] ?? null);
        $filterFaseId = !empty($_GET['filter_fase_id']) ? (int)$_GET['filter_fase_id'] : null;

        $teacherMapelIds = array_column($teacherMapelList, 'id');
        $effectiveMapelFilter = $filterMapelId ?: ($teacherMapelIds ?: null);

        // CP & TP data
        $cpList = $currModel->getCPList($filterKurId, $effectiveMapelFilter, $filterFaseId);
        $allCpForDropdown = $currModel->getCPList($filterKurId, $teacherMapelIds ?: null);
        $tpList = $currModel->getTPList(null, null, false);

        // Precompute auto-code maps for instant preview in modals
        $nextCpCodeMap = [];
        foreach ($kurikulumList as $kur) {
            $nextCpCodeMap[$kur['id']] = [];
            foreach ($teacherMapelList as $mp) {
                $nextCpCodeMap[$kur['id']][$mp['id']] = $currModel->generateNextCPCode($kur['id'], $mp['id']);
            }
        }
        $nextTpCodeMap = [];
        foreach ($allCpForDropdown as $c) {
            $nextTpCodeMap[$c['id']] = $currModel->generateNextTPCode($c['id']);
        }

        require_once ROOT_PATH . 'views/guru/cptp.php';
    }

    /**
     * Modul Asesmen Pembelajaran, Penilaian Siswa Per-TP, Remedial, dan Rekapitulasi KKTP
     * Alur: CP -> TP -> KKTP -> ASESMEN -> NILAI -> STATUS KETERCAPAIAN (1/0)
     */
    public function asesmen() {
        $guru = $this->getGuruInfo();
        $guruId = (int)($guru['id'] ?? 0);
        $assessModel = new AssessmentModel();
        $currModel = new CurriculumModel();
        $academicModel = new AcademicModel();

        $activeTa = $academicModel->getActiveTahunAjaran();
        $activeTaId = (int)($activeTa['id'] ?? 1);
        $activeSemester = ($activeTa['semester'] === 'Genap') ? 2 : 1;

        // AJAX handlers
        $ajaxAction = $_GET['ajax_action'] ?? ($_POST['ajax_action'] ?? '');
        if (!empty($ajaxAction)) {
            header('Content-Type: application/json; charset=utf-8');

            if ($ajaxAction === 'get_tp_by_mapel') {
                $mapelId = (int)($_GET['mapel_id'] ?? 0);
                $kurId = (int)($_GET['kurikulum_id'] ?? 0);
                $cpList = $currModel->getCPList($kurId ?: null, $mapelId ?: null);
                $cpIds = array_column($cpList, 'id');
                $tps = [];
                if (!empty($cpIds)) {
                    foreach ($cpIds as $cpId) {
                        $tpsForCp = $currModel->getTPList($cpId, null, false);
                        foreach ($tpsForCp as $t) {
                            $tps[] = [
                                'id' => $t['id'],
                                'kode_tp' => $t['kode_tp'],
                                'deskripsi' => $t['deskripsi'],
                                'materi_pokok' => $t['materi_pokok'],
                                'kode_cp' => $t['kode_cp'],
                                'kktp_metode' => $t['kktp_metode'] ?? 'interval_nilai',
                                'kktp_nilai_min' => $t['kktp_nilai_min'] ?? 75.00
                            ];
                        }
                    }
                }
                echo json_encode(['status' => true, 'data' => $tps]);
                exit();
            }

            if ($ajaxAction === 'get_kktp_info') {
                $tpId = (int)($_GET['tp_id'] ?? 0);
                $kktp = $assessModel->getKktpByTp($tpId);
                echo json_encode(['status' => true, 'data' => $kktp]);
                exit();
            }

            if ($ajaxAction === 'generate_kktp_auto') {
                $tpId = (int)($_GET['tp_id'] ?? 0);
                $autoKktp = $assessModel->generateDefaultKktpDataFromTp($tpId);
                echo json_encode(['status' => true, 'data' => $autoKktp]);
                exit();
            }

            if ($ajaxAction === 'generate_rapor_desc') {
                $siswaId = (int)($_GET['siswa_id'] ?? 0);
                $mapelId = (int)($_GET['mapel_id'] ?? 0);
                $desc = $assessModel->generateDeskripsiRaporFromTp($siswaId, $mapelId, $activeTaId, $activeSemester);
                echo json_encode(['status' => true, 'data' => $desc]);
                exit();
            }

            if ($ajaxAction === 'get_asesmen_detail') {
                $asesmenId = (int)($_GET['asesmen_id'] ?? 0);
                $asesmen = $assessModel->getAsesmenById($asesmenId);
                if ($asesmen) {
                    $assignedTpIds = !empty($asesmen['tujuan_pembelajaran']) ? array_column($asesmen['tujuan_pembelajaran'], 'tp_id') : [];
                    echo json_encode([
                        'status' => true, 
                        'data' => $asesmen,
                        'assigned_tp_ids' => $assignedTpIds
                    ]);
                } else {
                    echo json_encode(['status' => false, 'message' => 'Asesmen tidak ditemukan.']);
                }
                exit();
            }

            if ($ajaxAction === 'save_single_nilai') {
                if (!Security::verifyCsrfToken()) {
                    echo json_encode(['status' => false, 'message' => 'Token CSRF tidak valid.']);
                    exit();
                }
                $asesmenId = (int)($_POST['asesmen_id'] ?? 0);
                $tpId = (int)($_POST['tp_id'] ?? 0);
                $siswaId = (int)($_POST['siswa_id'] ?? 0);
                $nilaiAsli = floatval($_POST['nilai_asli'] ?? 0);
                $isRemedial = !empty($_POST['is_remedial']) && (int)$_POST['is_remedial'] === 1;
                $catatan = Security::sanitize($_POST['catatan'] ?? '');

                $extra = [];
                if (!empty($_POST['checked_indicators'])) {
                    $extra['checked_indicators'] = (array)$_POST['checked_indicators'];
                }

                $res = $assessModel->inputNilaiSiswaPerTp($asesmenId, $tpId, $siswaId, $nilaiAsli, $isRemedial, $catatan, $extra);
                echo json_encode($res);
                exit();
            }

            echo json_encode(['status' => false, 'message' => 'Action tidak dikenali.']);
            exit();
        }

        // Handle POST Requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('Sesi keamanan tidak valid (CSRF). Silakan coba lagi.');
                header('Location: ' . BASE_URL . 'index.php?url=guru/asesmen');
                exit();
            }

            $action = $_POST['action'] ?? '';

            if ($action === 'create_asesmen') {
                $rombelId = (int)$_POST['rombel_id'];
                $mapelId = (int)$_POST['mapel_id'];
                $namaAsesmen = Security::sanitize($_POST['nama_asesmen'] ?? '');
                $jenisAsesmen = Security::sanitize($_POST['jenis_asesmen'] ?? 'formatif');
                $tanggal = !empty($_POST['tanggal']) ? Security::sanitize($_POST['tanggal']) : date('Y-m-d');
                $nilaiMaks = floatval($_POST['nilai_maksimum'] ?? 100.00);
                $bobot = floatval($_POST['bobot'] ?? 1.00);
                $tpIds = !empty($_POST['tp_ids']) && is_array($_POST['tp_ids']) ? array_map('intval', $_POST['tp_ids']) : [];

                // Cari kurikulum_id aktif dari rombel/kelas terpilih
                $kurikulumInfo = $currModel->getActiveKurikulumForRombel($rombelId);
                $kurikulumId = (int)($kurikulumInfo['kurikulum_id'] ?? 1);

                $res = $assessModel->createAsesmenMultiTp([
                    'rombel_id' => $rombelId,
                    'mapel_id' => $mapelId,
                    'guru_id' => $guruId,
                    'kurikulum_id' => $kurikulumId,
                    'tahun_ajaran_id' => $activeTaId,
                    'semester' => $activeSemester,
                    'nama_asesmen' => $namaAsesmen,
                    'jenis_asesmen' => $jenisAsesmen,
                    'tanggal' => $tanggal,
                    'nilai_maksimum' => $nilaiMaks,
                    'bobot' => $bobot
                ], $tpIds);

                if ($res['status']) {
                    FlashHelper::setSuccess($res['message']);
                    header('Location: ' . BASE_URL . 'index.php?url=guru/asesmen&tab=penilaian&asesmen_id=' . (int)$res['asesmen_id']);
                    exit();
                } else {
                    FlashHelper::setError($res['message']);
                }

            } elseif ($action === 'update_asesmen') {
                $asesmenId = (int)($_POST['asesmen_id'] ?? 0);
                $rombelId = (int)($_POST['rombel_id'] ?? 0);
                $mapelId = (int)($_POST['mapel_id'] ?? 0);
                $namaAsesmen = Security::sanitize($_POST['nama_asesmen'] ?? '');
                $jenisAsesmen = Security::sanitize($_POST['jenis_asesmen'] ?? 'formatif');
                $tanggal = !empty($_POST['tanggal']) ? Security::sanitize($_POST['tanggal']) : date('Y-m-d');
                $nilaiMaks = floatval($_POST['nilai_maksimum'] ?? 100.00);
                $bobot = floatval($_POST['bobot'] ?? 1.00);
                $tpIds = !empty($_POST['tp_ids']) && is_array($_POST['tp_ids']) ? array_map('intval', $_POST['tp_ids']) : [];

                // Cari kurikulum_id aktif dari rombel/kelas terpilih
                $kurikulumInfo = $currModel->getActiveKurikulumForRombel($rombelId);
                $kurikulumId = (int)($kurikulumInfo['kurikulum_id'] ?? 1);

                $res = $assessModel->updateAsesmenMultiTp($asesmenId, [
                    'rombel_id' => $rombelId,
                    'mapel_id' => $mapelId,
                    'guru_id' => $guruId,
                    'kurikulum_id' => $kurikulumId,
                    'nama_asesmen' => $namaAsesmen,
                    'jenis_asesmen' => $jenisAsesmen,
                    'tanggal' => $tanggal,
                    'nilai_maksimum' => $nilaiMaks,
                    'bobot' => $bobot
                ], $tpIds);

                if ($res['status']) {
                    FlashHelper::setSuccess($res['message']);
                } else {
                    FlashHelper::setError($res['message']);
                }
                header('Location: ' . BASE_URL . 'index.php?url=guru/asesmen&tab=asesmen&rombel_id=' . $rombelId . '&mapel_id=' . $mapelId);
                exit();

            } elseif ($action === 'delete_asesmen') {
                $asesmenId = (int)($_POST['asesmen_id'] ?? 0);
                $res = $assessModel->deleteAsesmen($asesmenId, $guruId);
                if ($res['status']) {
                    FlashHelper::setSuccess($res['message']);
                } else {
                    FlashHelper::setError($res['message']);
                }
                header('Location: ' . BASE_URL . 'index.php?url=guru/asesmen&tab=asesmen');
                exit();

            } elseif ($action === 'save_batch_nilai') {
                $asesmenId = (int)$_POST['asesmen_id'];
                $scores = $_POST['nilai'] ?? []; // [siswa_id][tp_id] => value
                $savedCount = 0;

                foreach ($scores as $sId => $tps) {
                    $sId = (int)$sId;
                    foreach ($tps as $tId => $val) {
                        $tId = (int)$tId;
                        if ($val !== '' && $val !== null) {
                            $nilaiAsli = floatval($val);
                            $isRemedial = !empty($_POST['is_remedial'][$sId][$tId]);
                            $catatan = Security::sanitize($_POST['catatan'][$sId][$tId] ?? '');
                            $assessModel->inputNilaiSiswaPerTp($asesmenId, $tId, $sId, $nilaiAsli, $isRemedial, $catatan);
                            $savedCount++;
                        }
                    }
                }

                FlashHelper::setSuccess("Berhasil menyimpan {$savedCount} rekaman penilaian siswa.");
                header('Location: ' . BASE_URL . 'index.php?url=guru/asesmen&tab=penilaian&asesmen_id=' . $asesmenId);
                exit();

            } elseif ($action === 'save_remedial_modal') {
                $asesmenId = (int)$_POST['asesmen_id'];
                $tpId = (int)$_POST['tp_id'];
                $siswaId = (int)$_POST['siswa_id'];
                $nilaiRemedial = floatval($_POST['nilai_remedial']);
                $catatan = Security::sanitize($_POST['catatan'] ?? 'Remedial perbaikan ketercapaian');

                $res = $assessModel->inputNilaiSiswaPerTp($asesmenId, $tpId, $siswaId, $nilaiRemedial, true, $catatan);
                if ($res['status']) {
                    FlashHelper::setSuccess("Nilai remedial berhasil disimpan! Status saat ini: {$res['status_ketercapaian']} (Kode: {$res['status_code']})");
                } else {
                    FlashHelper::setError($res['message']);
                }
                header('Location: ' . BASE_URL . 'index.php?url=guru/asesmen&tab=penilaian&asesmen_id=' . $asesmenId);
                exit();
            }

            header('Location: ' . BASE_URL . 'index.php?url=guru/asesmen');
            exit();
        }

        // Active Tab: 'asesmen' | 'penilaian' | 'rekap_kelas' | 'rekap_siswa'
        $activeTab = $_GET['tab'] ?? 'asesmen';

        // Master Data
        $teacherMapelList = $academicModel->getMapelByGuru($guruId) ?: $academicModel->getMapel();
        $teacherKelasList = $academicModel->getKelasByGuru($guruId);
        $rombelList = $academicModel->getKelas();

        // Kelompokkan rombel berdasarkan Jurusan (RPL, TBSM, dll) agar terstruktur rapi
        $rombelByJurusan = [];
        foreach ($rombelList as $rb) {
            $jKode = !empty($rb['kode_jurusan']) ? strtoupper(trim($rb['kode_jurusan'])) : 'UMUM';
            $jNama = !empty($rb['nama_jurusan']) ? trim($rb['nama_jurusan']) : 'Umum';
            $groupTitle = "Jurusan {$jNama} ({$jKode})";
            if (!isset($rombelByJurusan[$groupTitle])) {
                $rombelByJurusan[$groupTitle] = [];
            }
            $rombelByJurusan[$groupTitle][] = $rb;
        }

        // Filter Param
        $filterRombelId = !empty($_GET['rombel_id']) ? (int)$_GET['rombel_id'] : (!empty($rombelList[0]['id']) ? (int)$rombelList[0]['id'] : 0);
        $filterMapelId = !empty($_GET['mapel_id']) ? (int)$_GET['mapel_id'] : (!empty($teacherMapelList[0]['id']) ? (int)$teacherMapelList[0]['id'] : 0);
        $selectedAsesmenId = !empty($_GET['asesmen_id']) ? (int)$_GET['asesmen_id'] : 0;
        $selectedSiswaId = !empty($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : 0;

        // Data for Tab 1: Asesmen List
        $asesmenList = $assessModel->getAsesmenList([
            'guru_id' => $guruId,
            'rombel_id' => $filterRombelId ?: null,
            'mapel_id' => $filterMapelId ?: null,
            'tahun_ajaran_id' => $activeTaId
        ]);

        // Data for Tab 2: Penilaian Matrix
        $matrixData = null;
        if ($selectedAsesmenId > 0) {
            $matrixData = $assessModel->getNilaiMatrixByAsesmen($selectedAsesmenId);
        } elseif (!empty($asesmenList)) {
            $selectedAsesmenId = (int)$asesmenList[0]['id'];
            $matrixData = $assessModel->getNilaiMatrixByAsesmen($selectedAsesmenId);
        }

        // Data for Tab 3: Rekap Ketercapaian Kelas
        $rekapKelas = null;
        if ($activeTab === 'rekap_kelas' || $filterRombelId) {
            $rekapKelas = $assessModel->getRekapKetercapaianKelas($filterRombelId, $filterMapelId, $activeTaId, $activeSemester);
        }

        // Data for Tab 4: Rekap Ketercapaian Siswa
        $rekapSiswa = null;
        $siswaInRombel = [];
        if ($filterRombelId > 0) {
            $stmtS = Database::getConnection()->prepare("
                SELECT s.id, s.nis, s.nama_lengkap 
                FROM siswa s 
                WHERE s.kelas_id = ? ORDER BY s.nama_lengkap ASC
            ");
            $stmtS->execute([$filterRombelId]);
            $siswaInRombel = $stmtS->fetchAll(PDO::FETCH_ASSOC);

            if ($selectedSiswaId <= 0 && !empty($siswaInRombel)) {
                $selectedSiswaId = (int)$siswaInRombel[0]['id'];
            }
        }
        if ($selectedSiswaId > 0) {
            $rekapSiswa = $assessModel->getRekapKetercapaianSiswa($selectedSiswaId, $filterMapelId, $activeTaId, $activeSemester);
        }

        require_once ROOT_PATH . 'views/guru/asesmen.php';
    }

    /**
     * Manajemen Bimbingan Ekstrakurikuler & Penilaian E-Rapor oleh Guru Pembimbing
     */
    public function ekstrakurikuler() {
        $guru = $this->getGuruInfo();
        $guruId = (int)($guru['id'] ?? 0);

        require_once ROOT_PATH . 'models/EkstrakurikulerModel.php';
        require_once ROOT_PATH . 'models/AcademicModel.php';
        require_once ROOT_PATH . 'models/SiswaModel.php';

        $ekskulModel = new EkstrakurikulerModel();
        $academicModel = new AcademicModel();
        $siswaModel = new SiswaModel();

        $activeTa = $academicModel->getActiveTahunAjaran();
        $taId = $activeTa['id'] ?? 4;
        $activeSemester = $activeTa['semester'] ?? 'Ganjil';

        // Ambil seluruh ekstrakurikuler yang ditugaskan oleh Admin kepada guru ini
        $guidedEkskul = $ekskulModel->getEkskulByGuru($guruId);
        $isPembimbing = !empty($guidedEkskul);

        // Jika form POST disubmit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('Token keamanan CSRF tidak valid.');
                header('Location: ' . BASE_URL . 'index.php?url=guru/ekstrakurikuler');
                exit();
            }

            $action = $_POST['action'] ?? '';
            $ekskulId = (int)($_POST['ekskul_id'] ?? 0);

            // Validasi hak bimbing: pastikan ekskulId ini benar dibimbing oleh guru yang login
            $allowedEkskulIds = array_column($guidedEkskul, 'id');
            if (!in_array($ekskulId, $allowedEkskulIds)) {
                FlashHelper::setError('Anda tidak memiliki hak akses bimbingan pada ekstrakurikuler ini.');
                header('Location: ' . BASE_URL . 'index.php?url=guru/ekstrakurikuler');
                exit();
            }

            if ($action === 'save_nilai_deskripsi') {
                $nilaiData = $_POST['nilai'] ?? []; // array [anggota_id => ['predikat' => ..., 'deskripsi' => ...]]
                $countSaved = 0;

                if (!empty($nilaiData) && is_array($nilaiData)) {
                    foreach ($nilaiData as $anggotaId => $val) {
                        $pred = trim($val['predikat'] ?? 'Sangat Baik');
                        $desk = trim($val['deskripsi'] ?? '');
                        if ($ekskulModel->updateNilaiDeskripsi((int)$anggotaId, $pred, $desk)) {
                            $countSaved++;
                        }
                    }
                    FlashHelper::setSuccess("Berhasil memperbarui nilai deskripsi capaian {$countSaved} siswa untuk E-Rapor.");
                }

                header('Location: ' . BASE_URL . 'index.php?url=guru/ekstrakurikuler&id=' . $ekskulId);
                exit();
            }

            if ($action === 'add_anggota_manual') {
                $siswaId = (int)($_POST['siswa_id'] ?? 0);
                if ($siswaId > 0) {
                    $ekskulModel->joinEkskul($siswaId, $ekskulId, $taId, $activeSemester);
                    FlashHelper::setSuccess('Siswa berhasil didaftarkan ke dalam kelompok bimbingan ekstrakurikuler.');
                }
                header('Location: ' . BASE_URL . 'index.php?url=guru/ekstrakurikuler&id=' . $ekskulId);
                exit();
            }

            if ($action === 'remove_anggota') {
                $siswaId = (int)($_POST['siswa_id'] ?? 0);
                if ($siswaId > 0) {
                    $ekskulModel->leaveEkskul($siswaId, $ekskulId);
                    FlashHelper::setSuccess('Siswa berhasil dikeluarkan dari kelompok ekstrakurikuler.');
                }
                header('Location: ' . BASE_URL . 'index.php?url=guru/ekstrakurikuler&id=' . $ekskulId);
                exit();
            }
        }

        // Tentukan ekskul yang sedang aktif dibuka
        $selectedId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $selectedEkskul = null;
        if ($selectedId > 0) {
            foreach ($guidedEkskul as $ge) {
                if ((int)$ge['id'] === $selectedId) {
                    $selectedEkskul = $ge;
                    break;
                }
            }
        }
        if (!$selectedEkskul && !empty($guidedEkskul)) {
            $selectedEkskul = $guidedEkskul[0];
            $selectedId = (int)$selectedEkskul['id'];
        }

        $anggotaList = [];
        $availableSiswa = [];
        if ($selectedEkskul) {
            $anggotaList = $ekskulModel->getAnggotaEkskul($selectedId, $taId, $activeSemester);

            // Ambil daftar seluruh siswa untuk penambahan manual
            $allSiswa = $siswaModel->getAll();
            $joinedSiswaIds = array_column($anggotaList, 'siswa_id');
            foreach ($allSiswa as $s) {
                if (!in_array($s['id'], $joinedSiswaIds)) {
                    $availableSiswa[] = $s;
                }
            }
        }

        require_once ROOT_PATH . 'views/guru/ekstrakurikuler.php';
    }

    /**
     * Helper untuk mengompilasi data lengkap E-Rapor satu siswa (Nilai, KKM, Predikat, Ketuntasan, Absensi, Ekskul, TTD)
     */
    private function getStudentRaporFullData($siswaId, $taId, $activeSemester) {
        try {
            require_once ROOT_PATH . 'models/CurriculumModel.php';
            require_once ROOT_PATH . 'models/NilaiModel.php';
            require_once ROOT_PATH . 'models/AcademicModel.php';
            require_once ROOT_PATH . 'models/EkstrakurikulerModel.php';
            require_once ROOT_PATH . 'models/SettingsModel.php';

            $currModel = new CurriculumModel();
            $nilaiModel = new NilaiModel();
            $academicModel = new AcademicModel();
            $ekskulModel = new EkstrakurikulerModel();
            $settingsModel = new SettingsModel();

            $db = Database::getConnection();

            // 1. Data Siswa & Rombel
            $stmtS = $db->prepare("
                SELECT s.*, k.nama_kelas, k.tingkat, j.nama_jurusan, k.wali_kelas_id
                FROM siswa s
                JOIN kelas k ON s.kelas_id = k.id
                LEFT JOIN jurusan j ON k.jurusan_id = j.id
                WHERE s.id = ?
            ");
            $stmtS->execute([(int)$siswaId]);
            $siswa = $stmtS->fetch(PDO::FETCH_ASSOC);
            if (!$siswa) return null;

            // Ambil data rapor header dan list nilai tanpa loop sync berat yang memicu 500 timeout
            $raporData = $currModel->getRaporSiswa($siswaId, $taId, $activeSemester);
            if (!is_array($raporData)) {
                $raporData = [];
            }

            // Fallback catatan wali kelas jika belum terisi di header
            if (empty($raporData['catatan_wali_kelas'])) {
                $stmtCat = $db->prepare("SELECT catatan_wali_kelas FROM rapor_siswa WHERE siswa_id = ? AND tahun_ajaran_id = ? AND semester = ?");
                $stmtCat->execute([(int)$siswaId, (int)$taId, $activeSemester]);
                $raporData['catatan_wali_kelas'] = $stmtCat->fetchColumn() ?: '';
            }

            // Ambil data nilai tersimpan
            $nilaiList = $nilaiModel->getNilaiBySiswa($siswaId);
            if (empty($nilaiList)) {
                $stmtNr = $db->prepare("
                    SELECT nr.*, mp.nama_mapel, mp.kode_mapel, COALESCE(mp.kkm, 75) as kkm
                    FROM nilai_rapor nr
                    JOIN mata_pelajaran mp ON nr.mapel_id = mp.id
                    WHERE nr.siswa_id = ?
                    ORDER BY mp.nama_mapel ASC
                ");
                $stmtNr->execute([(int)$siswaId]);
                $nilaiList = $stmtNr->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }

            $kelasId = (int)$siswa['kelas_id'];
            $kurInfo = $currModel->getActiveKurikulumForRombel($kelasId, $taId);
            $kurId = (int)($kurInfo['kurikulum_id'] ?? 1);
            $bobotKomponen = $nilaiModel->getBobotKomponenByKurikulum($kurId);

            // 2. Data Wali Kelas
            $waliKelas = null;
            if (!empty($siswa['wali_kelas_id'])) {
                $stmtWali = $db->prepare("SELECT nama_lengkap, nip, no_telepon FROM guru WHERE id = ?");
                $stmtWali->execute([(int)$siswa['wali_kelas_id']]);
                $waliKelas = $stmtWali->fetch(PDO::FETCH_ASSOC);
            }

            // 3. Data Pengaturan Sekolah & Kepala Sekolah
            $settings = $settingsModel->getAll();
            $kepsekNama = !empty($settings['kepala_sekolah']) ? $settings['kepala_sekolah'] : 'H. ASEP SAEPULLOH, S. Ag';
            $kepsekNip  = !empty($settings['nip_kepala_sekolah']) ? $settings['nip_kepala_sekolah'] : (!empty($settings['nip_kepsek']) ? $settings['nip_kepsek'] : 'G202608503');

            // 4. Data Rekap Ketidakhadiran (Sakit, Izin, Alpa)
            $absensiRekap = ['total' => 0, 'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0];
            try {
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
                $stmtAtt->execute([(int)$siswaId]);
                $attRow = $stmtAtt->fetch(PDO::FETCH_ASSOC);
                if ($attRow) {
                    $absensiRekap['total'] = (int)($attRow['total_absensi'] ?? 0);
                    $absensiRekap['hadir'] = (int)($attRow['total_hadir'] ?? 0);
                    $absensiRekap['izin']  = (int)($attRow['total_izin'] ?? 0);
                    $absensiRekap['sakit'] = (int)($attRow['total_sakit'] ?? 0);
                    $absensiRekap['alpa']  = (int)($attRow['total_alpa'] ?? 0);
                }
            } catch (\Throwable $e) {}

            // 5. Data Ekstrakurikuler yang Diikuti Siswa
            $ekskulList = [];
            try {
                $ekskulList = $ekskulModel->getEkskulBySiswa($siswaId, $taId, $activeSemester);
            } catch (\Throwable $e) {}

            // 6. Pemetaan Capaian Deskripsi & Perhitungan Nilai Rapor
            $capaianMap = [];
            if (!empty($raporData['nilai_list']) && is_array($raporData['nilai_list'])) {
                foreach ($raporData['nilai_list'] as $rd) {
                    if (isset($rd['mapel_id'])) {
                        $capaianMap[$rd['mapel_id']] = $rd['capaian_kompetensi'] ?? '';
                    }
                }
            }

            $calculatedRows = [];
            $totalAkhir = 0;
            $allTuntas = true;

            if (!empty($nilaiList)) {
                foreach ($nilaiList as $i => $n) {
                    $kkmVal = (float)($n['kkm'] ?? 75);
                    $recalcAkhir = NilaiModel::hitungNilaiAkhir(
                        (float)($n['nilai_tugas'] ?? 0),
                        (float)($n['nilai_quiz'] ?? 0),
                        (float)($n['nilai_uts'] ?? 0),
                        (float)($n['nilai_uas'] ?? 0),
                        $bobotKomponen
                    );
                    $akhirRow = ($recalcAkhir > 0 || (float)($n['nilai_akhir'] ?? 0) <= 0) ? $recalcAkhir : (float)$n['nilai_akhir'];
                    $pred = NilaiModel::getPredikat($akhirRow);
                    $isTuntas = ($akhirRow >= $kkmVal);
                    if (!$isTuntas) $allTuntas = false;
                    $totalAkhir += $akhirRow;

                    $mapelId = $n['mapel_id'] ?? 0;
                    $deskripsiCapaian = $capaianMap[$mapelId] ?? (
                        $isTuntas 
                        ? "Menunjukkan penguasaan sangat baik dalam menuntaskan seluruh tujuan pembelajaran {$n['nama_mapel']}."
                        : "Perlu bimbingan dan tindak lanjut remedial pada beberapa kompetensi dasar mata pelajaran {$n['nama_mapel']}."
                    );

                    $calculatedRows[] = [
                        'no' => $i + 1,
                        'mapel' => $n['nama_mapel'],
                        'kkm' => $kkmVal,
                        'akhir' => $akhirRow,
                        'pred' => $pred,
                        'is_tuntas' => $isTuntas,
                        'deskripsi' => $deskripsiCapaian
                    ];
                }
            }
            $countMapel = count($calculatedRows);
            $avgAkhir = $countMapel > 0 ? ($totalAkhir / $countMapel) : 0;
            $avgPred  = NilaiModel::getPredikat($avgAkhir);

            return [
                'siswa' => $siswa,
                'raporData' => $raporData,
                'calculatedRows' => $calculatedRows,
                'avgAkhir' => $avgAkhir,
                'avgPred' => $avgPred,
                'allTuntas' => $allTuntas,
                'absensiRekap' => $absensiRekap,
                'ekskulList' => $ekskulList,
                'waliKelas' => $waliKelas,
                'kepsekNama' => $kepsekNama,
                'kepsekNip' => $kepsekNip,
                'settings' => $settings
            ];
        } catch (\Throwable $e) {
            error_log("Error in getStudentRaporFullData for student {$siswaId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Dashboard & Panel Khusus Wali Kelas (Rekap Nilai, Presensi, & E-Rapor Rombel Binaan)
     */
    public function waliKelas() {
        $guru = $this->getGuruInfo();
        $guruId = (int)($guru['id'] ?? 0);

        $db = Database::getConnection();
        require_once ROOT_PATH . 'models/AcademicModel.php';
        require_once ROOT_PATH . 'models/CurriculumModel.php';
        require_once ROOT_PATH . 'models/NilaiModel.php';

        $academicModel = new AcademicModel();
        $currModel = new CurriculumModel();
        $activeTa = $academicModel->getActiveTahunAjaran();
        $taId = $activeTa['id'] ?? 4;
        $activeSemester = $activeTa['semester'] ?? 'Ganjil';

        // Ambil rombel yang dibimbing oleh guru sebagai wali kelas
        $stmtWali = $db->prepare("
            SELECT k.*, j.nama_jurusan, j.kode_jurusan,
                   (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa
            FROM kelas k
            LEFT JOIN jurusan j ON k.jurusan_id = j.id
            WHERE k.wali_kelas_id = ?
            ORDER BY k.tingkat ASC, k.nama_kelas ASC
        ");
        $stmtWali->execute([$guruId]);
        $myWaliKelas = $stmtWali->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $isWaliKelas = !empty($myWaliKelas);

        if (!$isWaliKelas) {
            FlashHelper::setError('Anda tidak terdaftar sebagai Wali Kelas pada rombel manapun.');
            header('Location: ' . BASE_URL . 'index.php?url=guru/dashboard');
            exit();
        }

        // Tentukan kelas yang aktif dipilih
        $selectedKelasId = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
        $selectedKelas = null;
        if ($selectedKelasId > 0) {
            foreach ($myWaliKelas as $mwk) {
                if ((int)$mwk['id'] === $selectedKelasId) {
                    $selectedKelas = $mwk;
                    break;
                }
            }
        }
        if (!$selectedKelas && !empty($myWaliKelas)) {
            $selectedKelas = $myWaliKelas[0];
            $selectedKelasId = (int)$selectedKelas['id'];
        }

        // Self-healing & defensive check untuk tabel rapor_siswa
        $colsRapor = [];
        try {
            $colsRapor = $db->query("SHOW COLUMNS FROM rapor_siswa")->fetchAll(PDO::FETCH_COLUMN) ?: [];
            if (!empty($colsRapor) && !in_array('catatan_wali_kelas', $colsRapor)) {
                $db->exec("ALTER TABLE `rapor_siswa` ADD COLUMN `catatan_wali_kelas` TEXT NULL AFTER `catatan_akademik`");
                $colsRapor[] = 'catatan_wali_kelas';
            }
        } catch (\Throwable $e) {}

        $colCatatanSelect = in_array('catatan_wali_kelas', $colsRapor)
            ? (in_array('catatan_akademik', $colsRapor) ? "COALESCE(catatan_wali_kelas, catatan_akademik, '')" : "COALESCE(catatan_wali_kelas, '')")
            : (in_array('catatan_akademik', $colsRapor) ? "COALESCE(catatan_akademik, '')" : "''");

        // Handle POST: Simpan Catatan Wali Kelas Massal
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrf = $_POST['csrf_token'] ?? '';
            if (!CsrfHelper::validateToken($csrf)) {
                FlashHelper::setError('Token keamanan tidak valid.');
                header('Location: ' . BASE_URL . 'index.php?url=guru/waliKelas&kelas_id=' . $selectedKelasId);
                exit();
            }

            $action = $_POST['action'] ?? '';
            if ($action === 'save_catatan_wali') {
                $catatanData = $_POST['catatan'] ?? []; // [siswa_id => '...']
                $countUpdated = 0;
                if (!empty($catatanData) && is_array($catatanData)) {
                    foreach ($catatanData as $sId => $catText) {
                        $sId = (int)$sId;
                        $catClean = trim($catText);
                        // Pastikan rapor_siswa ada untuk semester ini
                        $currModel->generateOrSyncRaporSiswa($sId, $taId, $activeSemester);
                        
                        try {
                            if (in_array('catatan_wali_kelas', $colsRapor) && in_array('catatan_akademik', $colsRapor)) {
                                $stmtUpCat = $db->prepare("
                                    UPDATE rapor_siswa 
                                    SET catatan_wali_kelas = ?, catatan_akademik = ? 
                                    WHERE siswa_id = ? AND tahun_ajaran_id = ? AND semester = ?
                                ");
                                if ($stmtUpCat->execute([$catClean, $catClean, $sId, $taId, $activeSemester])) {
                                    $countUpdated++;
                                }
                            } elseif (in_array('catatan_wali_kelas', $colsRapor)) {
                                $stmtUpCat = $db->prepare("
                                    UPDATE rapor_siswa 
                                    SET catatan_wali_kelas = ? 
                                    WHERE siswa_id = ? AND tahun_ajaran_id = ? AND semester = ?
                                ");
                                if ($stmtUpCat->execute([$catClean, $sId, $taId, $activeSemester])) {
                                    $countUpdated++;
                                }
                            } elseif (in_array('catatan_akademik', $colsRapor)) {
                                $stmtUpCat = $db->prepare("
                                    UPDATE rapor_siswa 
                                    SET catatan_akademik = ? 
                                    WHERE siswa_id = ? AND tahun_ajaran_id = ? AND semester = ?
                                ");
                                if ($stmtUpCat->execute([$catClean, $sId, $taId, $activeSemester])) {
                                    $countUpdated++;
                                }
                            }
                        } catch (\Throwable $eUp) {}
                    }
                }
                FlashHelper::setSuccess("Berhasil memperbarui catatan wali kelas untuk {$countUpdated} siswa.");
                header('Location: ' . BASE_URL . 'index.php?url=guru/waliKelas&kelas_id=' . $selectedKelasId);
                exit();
            }
        }

        // Ambil seluruh siswa di rombel terpilih
        $stmtSiswa = $db->prepare("
            SELECT s.*, u.username 
            FROM siswa s 
            LEFT JOIN users u ON s.user_id = u.id 
            WHERE s.kelas_id = ? 
            ORDER BY s.nama_lengkap ASC
        ");
        $stmtSiswa->execute([$selectedKelasId]);
        $siswaList = $stmtSiswa->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Ambil ringkasan nilai, absensi, dan catatan wali kelas untuk setiap siswa
        $studentSummary = [];
        $totalRombelAvg = 0;
        $totalRombelHadir = 0;
        $totalRombelPresensi = 0;

        foreach ($siswaList as $sw) {
            $sId = (int)$sw['id'];

            // Nilai Akhir Rata-rata dari tabel nilai_rapor atau CurriculumModel
            $stmtAvg = $db->prepare("
                SELECT AVG(nilai_akhir) as avg_nilai, COUNT(*) as total_mapel
                FROM nilai_rapor 
                WHERE siswa_id = ?
            ");
            $stmtAvg->execute([$sId]);
            $avgRow = $stmtAvg->fetch(PDO::FETCH_ASSOC);
            $avgVal = (float)($avgRow['avg_nilai'] ?? 0);
            $predikat = NilaiModel::getPredikat($avgVal);

            // Presensi per siswa
            $stmtAbs = $db->prepare("
                SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN LOWER(TRIM(status)) = 'hadir' THEN 1 END) as hadir,
                    COUNT(CASE WHEN LOWER(TRIM(status)) IN ('izin', 'ijin') THEN 1 END) as izin,
                    COUNT(CASE WHEN LOWER(TRIM(status)) = 'sakit' THEN 1 END) as sakit,
                    COUNT(CASE WHEN LOWER(TRIM(status)) IN ('alpa', 'alpha', 'tanpa keterangan') THEN 1 END) as alpa
                FROM absensi 
                WHERE siswa_id = ?
            ");
            $stmtAbs->execute([$sId]);
            $absRow = $stmtAbs->fetch(PDO::FETCH_ASSOC);
            $totAbs = (int)($absRow['total'] ?? 0);
            $hadirAbs = (int)($absRow['hadir'] ?? 0);

            // Catatan wali kelas dari rapor_siswa
            $catatanWali = '';
            try {
                $stmtRapor = $db->prepare("
                    SELECT {$colCatatanSelect} 
                    FROM rapor_siswa 
                    WHERE siswa_id = ? AND tahun_ajaran_id = ? AND semester = ?
                ");
                $stmtRapor->execute([$sId, $taId, $activeSemester]);
                $catatanWali = $stmtRapor->fetchColumn() ?: '';
            } catch (\Throwable $eCat) {}

            $studentSummary[$sId] = [
                'siswa' => $sw,
                'avg_nilai' => $avgVal,
                'predikat' => $predikat,
                'total_mapel' => (int)($avgRow['total_mapel'] ?? 0),
                'absensi' => [
                    'total' => $totAbs,
                    'hadir' => $hadirAbs,
                    'izin' => (int)($absRow['izin'] ?? 0),
                    'sakit' => (int)($absRow['sakit'] ?? 0),
                    'alpa' => (int)($absRow['alpa'] ?? 0),
                    'persen' => $totAbs > 0 ? round(($hadirAbs / $totAbs) * 100) : 100
                ],
                'catatan_wali' => $catatanWali
            ];

            $totalRombelAvg += $avgVal;
            $totalRombelHadir += $hadirAbs;
            $totalRombelPresensi += $totAbs;
        }

        $countSiswa = count($siswaList);
        $rombelAvgNilai = $countSiswa > 0 ? ($totalRombelAvg / $countSiswa) : 0;
        $rombelKehadiranPersen = $totalRombelPresensi > 0 ? round(($totalRombelHadir / $totalRombelPresensi) * 100) : 100;

        require_once ROOT_PATH . 'views/guru/wali_kelas.php';
    }

    /**
     * Cetak Sekaligus Semua E-Rapor Siswa dalam Satu Rombel (Bulk Print / A4/F4)
     * Menggunakan Batch Query Super Cepat, Ringan, & Anti-500
     */
    public function cetakRaporRombel() {
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '256M');

        $kelasId = (int)($_GET['kelas_id'] ?? 0);

        try {
            $guru = $this->getGuruInfo();
            $guruId = (int)($guru['id'] ?? 0);
            $userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
            $isAdmin = in_array($userRole, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);

            $db = Database::getConnection();

            // Validasi: pastikan kelasId ini benar dibimbing oleh guru yang bersangkutan (atau admin)
            $stmtK = $db->prepare("
                SELECT k.*, j.nama_jurusan, g.nama_lengkap as nama_walikelas, g.nip as nip_walikelas
                FROM kelas k
                LEFT JOIN jurusan j ON k.jurusan_id = j.id
                LEFT JOIN guru g ON k.wali_kelas_id = g.id
                WHERE k.id = ?
            ");
            $stmtK->execute([$kelasId]);
            $kelas = $stmtK->fetch(PDO::FETCH_ASSOC);

            if (!$kelas) {
                FlashHelper::setError('Data kelas rombel tidak ditemukan.');
                header('Location: ' . BASE_URL . 'index.php?url=guru/waliKelas');
                exit();
            }

            if (!$isAdmin && (int)($kelas['wali_kelas_id'] ?? 0) !== $guruId) {
                FlashHelper::setError('Anda tidak memiliki otorisasi untuk mencetak E-Rapor kelas ini.');
                header('Location: ' . BASE_URL . 'index.php?url=guru/waliKelas');
                exit();
            }

            require_once ROOT_PATH . 'models/AcademicModel.php';
            require_once ROOT_PATH . 'models/CurriculumModel.php';
            require_once ROOT_PATH . 'models/NilaiModel.php';
            require_once ROOT_PATH . 'models/SettingsModel.php';
            require_once ROOT_PATH . 'models/EkstrakurikulerModel.php';

            $academicModel = new AcademicModel();
            $currModel = new CurriculumModel();
            $settingsModel = new SettingsModel();
            $ekskulModel = new EkstrakurikulerModel(); // Memastikan tabel ekstrakurikuler & anggotanya otomatis ada

            $activeTa = $academicModel->getActiveTahunAjaran();
            $taId = (int)($activeTa['id'] ?? 4);
            $activeSemester = $activeTa['semester'] ?? 'Ganjil';

            $settings = $settingsModel->getAll();
            $kepsekNama = !empty($settings['kepala_sekolah']) ? $settings['kepala_sekolah'] : 'H. ASEP SAEPULLOH, S. Ag';
            $kepsekNip  = !empty($settings['nip_kepala_sekolah']) ? $settings['nip_kepala_sekolah'] : (!empty($settings['nip_kepsek']) ? $settings['nip_kepsek'] : 'G202608503');

            $kurInfo = $currModel->getActiveKurikulumForRombel($kelasId, $taId);
            $kurId = (int)($kurInfo['kurikulum_id'] ?? 1);
            $nilaiModel = new NilaiModel();
            $bobotKomponen = $nilaiModel->getBobotKomponenByKurikulum($kurId);

            $waliKelas = [
                'nama_lengkap' => $kelas['nama_walikelas'] ?? '',
                'nip' => $kelas['nip_walikelas'] ?? ''
            ];

            // 1. Ambil seluruh siswa di rombel ini
            $stmtS = $db->prepare("
                SELECT s.*, k.nama_kelas, k.tingkat, j.nama_jurusan
                FROM siswa s
                JOIN kelas k ON s.kelas_id = k.id
                LEFT JOIN jurusan j ON k.jurusan_id = j.id
                WHERE s.kelas_id = ?
                ORDER BY s.nama_lengkap ASC
            ");
            $stmtS->execute([$kelasId]);
            $allSiswa = $stmtS->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if (empty($allSiswa)) {
                $allRaporList = [];
                require_once ROOT_PATH . 'views/guru/cetak_rapor_rombel.php';
                return;
            }

            $siswaIds = array_column($allSiswa, 'id');
            $inIds = implode(',', array_map('intval', $siswaIds));

            // 2. BATCH QUERY: Nilai seluruh siswa di rombel ini (1 query cepat)
            $nilaiBySiswa = [];
            try {
                $stmtNilai = $db->query("
                    SELECT nr.siswa_id, nr.mapel_id, nr.nilai_tugas, nr.nilai_quiz, nr.nilai_uts, nr.nilai_uas, nr.nilai_akhir,
                           mp.nama_mapel, mp.kode_mapel, COALESCE(mp.kkm, 75) as kkm
                    FROM nilai_rapor nr
                    JOIN mata_pelajaran mp ON nr.mapel_id = mp.id
                    WHERE nr.siswa_id IN ({$inIds})
                    ORDER BY nr.siswa_id, mp.nama_mapel ASC
                ");
                if ($stmtNilai) {
                    $allNilaiRows = $stmtNilai->fetchAll(PDO::FETCH_ASSOC) ?: [];
                    foreach ($allNilaiRows as $nr) {
                        $nilaiBySiswa[$nr['siswa_id']][] = $nr;
                    }
                }
            } catch (\Throwable $eNilai) {
                error_log("Error batch nilai cetakRaporRombel: " . $eNilai->getMessage());
            }

            // 3. BATCH QUERY: Catatan wali kelas & rapor snapshot (Defensive Column Check)
            $raporHeaderBySiswa = [];
            try {
                $colsR = $db->query("SHOW COLUMNS FROM rapor_siswa")->fetchAll(PDO::FETCH_COLUMN) ?: [];
                if (!in_array('catatan_wali_kelas', $colsR)) {
                    try {
                        $db->exec("ALTER TABLE `rapor_siswa` ADD COLUMN `catatan_wali_kelas` TEXT NULL AFTER `catatan_akademik`");
                        $colsR[] = 'catatan_wali_kelas';
                    } catch (\Throwable $eAlter) {}
                }
                $colCat = in_array('catatan_wali_kelas', $colsR) 
                    ? (in_array('catatan_akademik', $colsR) ? "COALESCE(rs.catatan_wali_kelas, rs.catatan_akademik, '')" : "COALESCE(rs.catatan_wali_kelas, '')")
                    : (in_array('catatan_akademik', $colsR) ? "COALESCE(rs.catatan_akademik, '')" : "''");

                $stmtRapor = $db->prepare("
                    SELECT rs.siswa_id, {$colCat} as catatan_wali_kelas, rs.kurikulum_nama_snapshot, rs.fase_nama_snapshot, rs.status
                    FROM rapor_siswa rs
                    WHERE rs.siswa_id IN ({$inIds}) AND rs.tahun_ajaran_id = ? AND rs.semester = ?
                ");
                $stmtRapor->execute([$taId, $activeSemester]);
                $allRaporHeaders = $stmtRapor->fetchAll(PDO::FETCH_ASSOC) ?: [];
                foreach ($allRaporHeaders as $rh) {
                    $raporHeaderBySiswa[$rh['siswa_id']] = $rh;
                }
            } catch (\Throwable $eRapor) {
                error_log("Error batch rapor header cetakRaporRombel: " . $eRapor->getMessage());
            }

            // 4. BATCH QUERY: Rekapitulasi absensi seluruh siswa (1 query cepat)
            $absBySiswa = [];
            try {
                $stmtAbs = $db->query("
                    SELECT 
                        siswa_id,
                        COUNT(*) as total_absensi,
                        COUNT(CASE WHEN LOWER(TRIM(status)) = 'hadir' THEN 1 END) as total_hadir,
                        COUNT(CASE WHEN LOWER(TRIM(status)) IN ('izin', 'ijin') THEN 1 END) as total_izin,
                        COUNT(CASE WHEN LOWER(TRIM(status)) = 'sakit' THEN 1 END) as total_sakit,
                        COUNT(CASE WHEN LOWER(TRIM(status)) IN ('alpa', 'alpha', 'tanpa keterangan') THEN 1 END) as total_alpa
                    FROM absensi
                    WHERE siswa_id IN ({$inIds})
                    GROUP BY siswa_id
                ");
                if ($stmtAbs) {
                    $allAbsRows = $stmtAbs->fetchAll(PDO::FETCH_ASSOC) ?: [];
                    foreach ($allAbsRows as $ab) {
                        $absBySiswa[$ab['siswa_id']] = [
                            'total' => (int)$ab['total_absensi'],
                            'hadir' => (int)$ab['total_hadir'],
                            'izin'  => (int)$ab['total_izin'],
                            'sakit' => (int)$ab['total_sakit'],
                            'alpa'  => (int)$ab['total_alpa']
                        ];
                    }
                }
            } catch (\Throwable $eAbs) {
                error_log("Error batch absensi cetakRaporRombel: " . $eAbs->getMessage());
            }

            // 5. BATCH QUERY: Ekstrakurikuler yang diikuti seluruh siswa (1 query cepat)
            $eksBySiswa = [];
            try {
                $stmtEks = $db->query("
                    SELECT es.siswa_id, es.ekskul_id, es.predikat, es.nilai_deskripsi, e.nama_ekskul
                    FROM ekstrakurikuler_siswa es
                    JOIN ekstrakurikuler e ON es.ekskul_id = e.id
                    WHERE es.siswa_id IN ({$inIds}) AND es.status = 'aktif'
                    ORDER BY es.siswa_id, e.nama_ekskul ASC
                ");
                if ($stmtEks) {
                    $allEksRows = $stmtEks->fetchAll(PDO::FETCH_ASSOC) ?: [];
                    foreach ($allEksRows as $er) {
                        $eksBySiswa[$er['siswa_id']][] = $er;
                    }
                }
            } catch (\Throwable $eEks) {
                error_log("Error batch ekskul cetakRaporRombel: " . $eEks->getMessage());
            }

            // 6. Kompilasi data E-Rapor setiap siswa dalam rombel (100% in-memory)
            $allRaporList = [];
            foreach ($allSiswa as $s) {
                $sId = (int)$s['id'];
                $nilaiList = $nilaiBySiswa[$sId] ?? [];
                $rHeader = $raporHeaderBySiswa[$sId] ?? [
                    'catatan_wali_kelas' => '',
                    'kurikulum_nama_snapshot' => $kurInfo['nama_kurikulum'] ?? 'Kurikulum Merdeka SMK',
                    'fase_nama_snapshot' => $kurInfo['nama_fase'] ?? 'Fase F (Kelas XI - XII)',
                    'status' => 'terverifikasi'
                ];

                $calculatedRows = [];
                $totalAkhir = 0;
                $allTuntas = true;

                foreach ($nilaiList as $i => $n) {
                    $kkmVal = (float)($n['kkm'] ?? 75);
                    $recalcAkhir = NilaiModel::hitungNilaiAkhir(
                        (float)($n['nilai_tugas'] ?? 0),
                        (float)($n['nilai_quiz'] ?? 0),
                        (float)($n['nilai_uts'] ?? 0),
                        (float)($n['nilai_uas'] ?? 0),
                        $bobotKomponen
                    );
                    $akhirRow = ($recalcAkhir > 0 || (float)($n['nilai_akhir'] ?? 0) <= 0) ? $recalcAkhir : (float)$n['nilai_akhir'];
                    $pred = NilaiModel::getPredikat($akhirRow);
                    $isTuntas = ($akhirRow >= $kkmVal);
                    if (!$isTuntas) $allTuntas = false;
                    $totalAkhir += $akhirRow;

                    $deskripsiCapaian = $isTuntas 
                        ? "Menunjukkan penguasaan sangat baik dalam menuntaskan seluruh tujuan pembelajaran {$n['nama_mapel']}."
                        : "Perlu bimbingan dan tindak lanjut remedial pada beberapa kompetensi dasar mata pelajaran {$n['nama_mapel']}.";

                    $calculatedRows[] = [
                        'no' => $i + 1,
                        'mapel' => $n['nama_mapel'],
                        'kkm' => $kkmVal,
                        'akhir' => $akhirRow,
                        'pred' => $pred,
                        'is_tuntas' => $isTuntas,
                        'deskripsi' => $deskripsiCapaian
                    ];
                }

                $countMapel = count($calculatedRows);
                $avgAkhir = $countMapel > 0 ? ($totalAkhir / $countMapel) : 0;
                $avgPred  = NilaiModel::getPredikat($avgAkhir);

                $allRaporList[] = [
                    'siswa' => $s,
                    'raporData' => $rHeader,
                    'calculatedRows' => $calculatedRows,
                    'avgAkhir' => $avgAkhir,
                    'avgPred' => $avgPred,
                    'allTuntas' => $allTuntas,
                    'absensiRekap' => $absBySiswa[$sId] ?? ['total' => 0, 'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0],
                    'ekskulList' => $eksBySiswa[$sId] ?? [],
                    'waliKelas' => $waliKelas,
                    'kepsekNama' => $kepsekNama,
                    'kepsekNip' => $kepsekNip,
                    'settings' => $settings
                ];
            }

            require_once ROOT_PATH . 'views/guru/cetak_rapor_rombel.php';

        } catch (\Throwable $e) {
            error_log("Fatal error cetakRaporRombel: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            FlashHelper::setError('Gagal mencetak rapor rombel: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'index.php?url=guru/waliKelas&kelas_id=' . $kelasId);
            exit();
        }
    }

    /**
     * Cetak E-Rapor Satu Siswa Perorangan oleh Wali Kelas
     */
    public function cetakRaporSiswa() {
        @ini_set('max_execution_time', '120');

        $siswaId = (int)($_GET['siswa_id'] ?? 0);

        try {
            $guru = $this->getGuruInfo();
            $guruId = (int)($guru['id'] ?? 0);
            $userRole = strtolower(AuthHelper::user()['role_name'] ?? '');
            $isAdmin = in_array($userRole, ['administrator', 'admin', 'kepala sekolah', 'kepsek']);

            $db = Database::getConnection();
            $stmtS = $db->prepare("SELECT s.*, k.wali_kelas_id, k.id as kelas_id FROM siswa s JOIN kelas k ON s.kelas_id = k.id WHERE s.id = ?");
            $stmtS->execute([$siswaId]);
            $siswa = $stmtS->fetch(PDO::FETCH_ASSOC);

            if (!$siswa) {
                FlashHelper::setError('Data siswa tidak ditemukan.');
                header('Location: ' . BASE_URL . 'index.php?url=guru/waliKelas');
                exit();
            }

            if (!$isAdmin && (int)($siswa['wali_kelas_id'] ?? 0) !== $guruId) {
                FlashHelper::setError('Anda tidak memiliki hak akses sebagai Wali Kelas siswa ini.');
                header('Location: ' . BASE_URL . 'index.php?url=guru/waliKelas');
                exit();
            }

            $kelasId = (int)$siswa['kelas_id'];

            require_once ROOT_PATH . 'models/AcademicModel.php';
            require_once ROOT_PATH . 'models/CurriculumModel.php';
            require_once ROOT_PATH . 'models/NilaiModel.php';
            require_once ROOT_PATH . 'models/SettingsModel.php';
            require_once ROOT_PATH . 'models/EkstrakurikulerModel.php';

            $academicModel = new AcademicModel();
            $currModel = new CurriculumModel();
            $settingsModel = new SettingsModel();
            $ekskulModel = new EkstrakurikulerModel();

            $activeTa = $academicModel->getActiveTahunAjaran();
            $taId = (int)($activeTa['id'] ?? 4);
            $activeSemester = $activeTa['semester'] ?? 'Ganjil';

            $settings = $settingsModel->getAll();
            $kepsekNama = !empty($settings['kepala_sekolah']) ? $settings['kepala_sekolah'] : 'H. ASEP SAEPULLOH, S. Ag';
            $kepsekNip  = !empty($settings['nip_kepala_sekolah']) ? $settings['nip_kepala_sekolah'] : (!empty($settings['nip_kepsek']) ? $settings['nip_kepsek'] : 'G202608503');

            $stmtK = $db->prepare("
                SELECT k.*, j.nama_jurusan, g.nama_lengkap as nama_walikelas, g.nip as nip_walikelas
                FROM kelas k
                LEFT JOIN jurusan j ON k.jurusan_id = j.id
                LEFT JOIN guru g ON k.wali_kelas_id = g.id
                WHERE k.id = ?
            ");
            $stmtK->execute([$kelasId]);
            $kelas = $stmtK->fetch(PDO::FETCH_ASSOC);

            $waliKelas = [
                'nama_lengkap' => $kelas['nama_walikelas'] ?? '',
                'nip' => $kelas['nip_walikelas'] ?? ''
            ];

            $kurInfo = $currModel->getActiveKurikulumForRombel($kelasId, $taId);
            $kurId = (int)($kurInfo['kurikulum_id'] ?? 1);
            $nilaiModel = new NilaiModel();
            $bobotKomponen = $nilaiModel->getBobotKomponenByKurikulum($kurId);

            // Fetch single student info
            $stmtSFull = $db->prepare("
                SELECT s.*, k.nama_kelas, k.tingkat, j.nama_jurusan
                FROM siswa s
                JOIN kelas k ON s.kelas_id = k.id
                LEFT JOIN jurusan j ON k.jurusan_id = j.id
                WHERE s.id = ?
            ");
            $stmtSFull->execute([$siswaId]);
            $s = $stmtSFull->fetch(PDO::FETCH_ASSOC);

            // Nilai
            $nilaiList = [];
            try {
                $stmtNilai = $db->prepare("
                    SELECT nr.siswa_id, nr.mapel_id, nr.nilai_tugas, nr.nilai_quiz, nr.nilai_uts, nr.nilai_uas, nr.nilai_akhir,
                           mp.nama_mapel, mp.kode_mapel, COALESCE(mp.kkm, 75) as kkm
                    FROM nilai_rapor nr
                    JOIN mata_pelajaran mp ON nr.mapel_id = mp.id
                    WHERE nr.siswa_id = ?
                    ORDER BY mp.nama_mapel ASC
                ");
                $stmtNilai->execute([$siswaId]);
                $nilaiList = $stmtNilai->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $eNilai) {}

            // Header Rapor
            $rHeader = [
                'catatan_wali_kelas' => '',
                'kurikulum_nama_snapshot' => $kurInfo['nama_kurikulum'] ?? 'Kurikulum Merdeka SMK',
                'fase_nama_snapshot' => $kurInfo['nama_fase'] ?? 'Fase F (Kelas XI - XII)',
                'status' => 'terverifikasi'
            ];
            try {
                $colsR = $db->query("SHOW COLUMNS FROM rapor_siswa")->fetchAll(PDO::FETCH_COLUMN) ?: [];
                $colCat = in_array('catatan_wali_kelas', $colsR) 
                    ? (in_array('catatan_akademik', $colsR) ? "COALESCE(rs.catatan_wali_kelas, rs.catatan_akademik, '')" : "COALESCE(rs.catatan_wali_kelas, '')")
                    : (in_array('catatan_akademik', $colsR) ? "COALESCE(rs.catatan_akademik, '')" : "''");

                $stmtRapor = $db->prepare("
                    SELECT rs.siswa_id, {$colCat} as catatan_wali_kelas, rs.kurikulum_nama_snapshot, rs.fase_nama_snapshot, rs.status
                    FROM rapor_siswa rs
                    WHERE rs.siswa_id = ? AND rs.tahun_ajaran_id = ? AND rs.semester = ?
                ");
                $stmtRapor->execute([$siswaId, $taId, $activeSemester]);
                $foundR = $stmtRapor->fetch(PDO::FETCH_ASSOC);
                if ($foundR) {
                    $rHeader = $foundR;
                }
            } catch (\Throwable $eRapor) {}

            // Absensi
            $absRekap = ['total' => 0, 'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0];
            try {
                $stmtAbs = $db->prepare("
                    SELECT 
                        COUNT(*) as total_absensi,
                        COUNT(CASE WHEN LOWER(TRIM(status)) = 'hadir' THEN 1 END) as total_hadir,
                        COUNT(CASE WHEN LOWER(TRIM(status)) IN ('izin', 'ijin') THEN 1 END) as total_izin,
                        COUNT(CASE WHEN LOWER(TRIM(status)) = 'sakit' THEN 1 END) as total_sakit,
                        COUNT(CASE WHEN LOWER(TRIM(status)) IN ('alpa', 'alpha', 'tanpa keterangan') THEN 1 END) as total_alpa
                    FROM absensi
                    WHERE siswa_id = ?
                ");
                $stmtAbs->execute([$siswaId]);
                $ab = $stmtAbs->fetch(PDO::FETCH_ASSOC);
                if ($ab) {
                    $absRekap = [
                        'total' => (int)($ab['total_absensi'] ?? 0),
                        'hadir' => (int)($ab['total_hadir'] ?? 0),
                        'izin'  => (int)($ab['total_izin'] ?? 0),
                        'sakit' => (int)($ab['total_sakit'] ?? 0),
                        'alpa'  => (int)($ab['total_alpa'] ?? 0)
                    ];
                }
            } catch (\Throwable $eAbs) {}

            // Ekskul
            $eksList = [];
            try {
                $stmtEks = $db->prepare("
                    SELECT es.siswa_id, es.ekskul_id, es.predikat, es.nilai_deskripsi, e.nama_ekskul
                    FROM ekstrakurikuler_siswa es
                    JOIN ekstrakurikuler e ON es.ekskul_id = e.id
                    WHERE es.siswa_id = ? AND es.status = 'aktif'
                    ORDER BY e.nama_ekskul ASC
                ");
                $stmtEks->execute([$siswaId]);
                $eksList = $stmtEks->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (\Throwable $eEks) {}

            $calculatedRows = [];
            $totalAkhir = 0;
            $allTuntas = true;

            foreach ($nilaiList as $i => $n) {
                $kkmVal = (float)($n['kkm'] ?? 75);
                $recalcAkhir = NilaiModel::hitungNilaiAkhir(
                    (float)($n['nilai_tugas'] ?? 0),
                    (float)($n['nilai_quiz'] ?? 0),
                    (float)($n['nilai_uts'] ?? 0),
                    (float)($n['nilai_uas'] ?? 0),
                    $bobotKomponen
                );
                $akhirRow = ($recalcAkhir > 0 || (float)($n['nilai_akhir'] ?? 0) <= 0) ? $recalcAkhir : (float)$n['nilai_akhir'];
                $pred = NilaiModel::getPredikat($akhirRow);
                $isTuntas = ($akhirRow >= $kkmVal);
                if (!$isTuntas) $allTuntas = false;
                $totalAkhir += $akhirRow;

                $deskripsiCapaian = $isTuntas 
                    ? "Menunjukkan penguasaan sangat baik dalam menuntaskan seluruh tujuan pembelajaran {$n['nama_mapel']}."
                    : "Perlu bimbingan dan tindak lanjut remedial pada beberapa kompetensi dasar mata pelajaran {$n['nama_mapel']}.";

                $calculatedRows[] = [
                    'no' => $i + 1,
                    'mapel' => $n['nama_mapel'],
                    'kkm' => $kkmVal,
                    'akhir' => $akhirRow,
                    'pred' => $pred,
                    'is_tuntas' => $isTuntas,
                    'deskripsi' => $deskripsiCapaian
                ];
            }

            $countMapel = count($calculatedRows);
            $avgAkhir = $countMapel > 0 ? ($totalAkhir / $countMapel) : 0;
            $avgPred  = NilaiModel::getPredikat($avgAkhir);

            $allRaporList = [[
                'siswa' => $s,
                'raporData' => $rHeader,
                'calculatedRows' => $calculatedRows,
                'avgAkhir' => $avgAkhir,
                'avgPred' => $avgPred,
                'allTuntas' => $allTuntas,
                'absensiRekap' => $absRekap,
                'ekskulList' => $eksList,
                'waliKelas' => $waliKelas,
                'kepsekNama' => $kepsekNama,
                'kepsekNip' => $kepsekNip,
                'settings' => $settings
            ]];

            require_once ROOT_PATH . 'views/guru/cetak_rapor_rombel.php';

        } catch (\Throwable $e) {
            error_log("Fatal error cetakRaporSiswa: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            FlashHelper::setError('Gagal mencetak rapor siswa: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'index.php?url=guru/waliKelas');
            exit();
        }
    }
}
