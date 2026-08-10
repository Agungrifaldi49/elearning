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
require_once ROOT_PATH . 'models/CommunicationModel.php';

class GuruController {

    public function __construct() {
        AuthHelper::requireRole(['Guru']);
    }

    private function getGuruInfo() {
        $user = AuthHelper::user();
        $guruModel = new GuruModel();
        return $guruModel->ensureGuruProfile($user['id'], $user['full_name']);
    }

    public function dashboard() {
        $guru = $this->getGuruInfo();
        $guruId = $guru['id'];

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
                $kelas_id = (int)$_POST['kelas_id'];
                $jenis_file = $_POST['jenis_file'];
                $youtube_url = Security::sanitize($_POST['youtube_url'] ?? '');
                $filePath = null;

                if ($jenis_file !== 'youtube' && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $filePath = UploadHelper::upload($_FILES['file'], 'materi');
                }

                $learningModel->addMateri($guruId, $mapel_id, $kelas_id, $judul, $deskripsi, $jenis_file, $filePath, $youtube_url);
                FlashHelper::setSuccess('Materi Pembelajaran baru berhasil diunggah.');

            } elseif ($action === 'update' && $id > 0) {
                $judul = Security::sanitize($_POST['judul']);
                $deskripsi = Security::sanitize($_POST['deskripsi']);
                $mapel_id = (int)$_POST['mapel_id'];
                $kelas_id = (int)$_POST['kelas_id'];
                $jenis_file = $_POST['jenis_file'];
                $youtube_url = Security::sanitize($_POST['youtube_url'] ?? '');
                $filePath = null;

                if ($jenis_file !== 'youtube' && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $filePath = UploadHelper::upload($_FILES['file'], 'materi');
                }

                $learningModel->updateMateri($id, $mapel_id, $kelas_id, $judul, $deskripsi, $jenis_file, $filePath, $youtube_url);
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
                $kelas_id = (int)$_POST['kelas_id'];
                $deadline = $_POST['deadline'];
                $filePath = null;

                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $filePath = UploadHelper::upload($_FILES['file'], 'tugas');
                }

                $learningModel->addTugas($guruId, $mapel_id, $kelas_id, $judul, $deskripsi, $filePath, $deadline);
                
                $commModel = new CommunicationModel();
                $commModel->sendNotificationToClass(
                    $kelas_id, 
                    '📝 Tugas Pembelajaran Baru', 
                    "Guru mempublikasikan Tugas Baru: {$judul}. Batas deadline: {$deadline}.", 
                    'index.php?url=siswa/tugas'
                );

                FlashHelper::setSuccess('Tugas baru berhasil dibuat.');

            } elseif ($action === 'update') {
                $id = (int)$_POST['id'];
                $judul = Security::sanitize($_POST['judul']);
                $deskripsi = Security::sanitize($_POST['deskripsi']);
                $mapel_id = (int)$_POST['mapel_id'];
                $kelas_id = (int)$_POST['kelas_id'];
                $deadline = $_POST['deadline'];
                $filePath = null;

                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $filePath = UploadHelper::upload($_FILES['file'], 'tugas');
                }

                $learningModel->updateTugas($id, $mapel_id, $kelas_id, $judul, $deskripsi, $filePath, $deadline);
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
                exit();
            }

            $action = $_POST['action'] ?? 'create';

            if ($action === 'import_soal_excel') {
                $quizId = (int)$_POST['quiz_id'];
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

                $quizId = $examModel->createQuiz(
                    $guruId,
                    (int)$_POST['mapel_id'],
                    (int)$_POST['kelas_id'],
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
            } elseif ($action === 'create') {
                $durasi = (int)$_POST['durasi_menit'];
                $pertanyaanArr = $_POST['pertanyaan'] ?? [];
                $jenisSoalArr = $_POST['jenis_soal'] ?? [];
                $jumlahSoal = count($pertanyaanArr);

                $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
                $maxAttempts = isset($_POST['max_attempts']) ? (int)$_POST['max_attempts'] : 1;
                $kategori = $_POST['kategori'] ?? 'kuis';
                $accessKey = !empty($_POST['access_key']) ? trim(strtoupper($_POST['access_key'])) : null;

                $quizId = $examModel->createQuiz(
                    $guruId,
                    (int)$_POST['mapel_id'],
                    (int)$_POST['kelas_id'],
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
                $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
                $maxAttempts = isset($_POST['max_attempts']) ? (int)$_POST['max_attempts'] : 1;
                $kategori = $_POST['kategori'] ?? 'kuis';
                $accessKey = !empty($_POST['access_key']) ? trim(strtoupper($_POST['access_key'])) : null;
                $examModel->updateQuiz(
                    $id,
                    (int)$_POST['mapel_id'],
                    (int)$_POST['kelas_id'],
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
                $examModel->deleteQuiz($id);
                FlashHelper::setSuccess('Paket Quiz beserta seluruh soal berhasil dihapus.');

            } elseif ($action === 'delete_soal') {
                $soalId = (int)$_POST['soal_id'];
                $examModel->deleteSoal($soalId);
                FlashHelper::setSuccess('Soal berhasil dihapus.');

            } elseif ($action === 'grade_quiz_essay') {
                $quizId = (int)$_POST['quiz_id'];
                $siswaId = (int)$_POST['siswa_id'];
                $essayScores = $_POST['nilai_essay'] ?? [];

                $db = Database::getConnection();
                $stmtGetSoal = $db->prepare("SELECT s.bobot FROM jawaban_siswa js JOIN soal s ON js.soal_id = s.id WHERE js.id = ?");
                $stmtUpdate = $db->prepare("UPDATE jawaban_siswa SET nilai = ?, is_benar = IF(? > 0, 1, 0) WHERE id = ?");

                foreach ($essayScores as $jawabanId => $scoreVal) {
                    $scoreNum = (float)$scoreVal;
                    
                    // Fetch max bobot for this question to validate
                    $stmtGetSoal->execute([(int)$jawabanId]);
                    $soalRow = $stmtGetSoal->fetch();
                    $maxBobot = (float)($soalRow['bobot'] ?? 10);

                    // If teacher entered score > maxBobot (e.g., entered 80 out of 100 for a question of bobot 10)
                    if ($scoreNum > $maxBobot) {
                        if ($scoreNum <= 100 && $maxBobot > 0) {
                            $scoreNum = round(($scoreNum / 100) * $maxBobot, 2);
                        } else {
                            $scoreNum = $maxBobot;
                        }
                    }
                    if ($scoreNum < 0) $scoreNum = 0;

                    $stmtUpdate->execute([$scoreNum, $scoreNum, (int)$jawabanId]);
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

            header('Location: ' . BASE_URL . 'index.php?url=guru/quiz');
            exit();
        }

        $quizList = $examModel->getQuizList(null, $guruId);
        $susulanRequests = $examModel->getSusulanRequestsByGuru($guruId);
        $hasilQuizSubmissions = $examModel->getHasilQuizListByGuru($guruId);

        $mapelList = $academicModel->getMapelByGuru($guruId);
        if (empty($mapelList)) $mapelList = $academicModel->getMapel();

        $kelasList = $academicModel->getKelasByGuru($guruId);
        if (empty($kelasList)) $kelasList = $academicModel->getKelas();

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
                if (empty($jadwalList)) {
                    $jadwalList = $academicModel->getJadwal();
                }
            }
        }

        $selectedJadwal = (int)($_GET['jadwal_id'] ?? ($jadwalList[0]['id'] ?? 1));
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=guru/absensi');
                exit();
            }

            $presensi = $_POST['absensi'] ?? [];
            foreach ($presensi as $siswaId => $status) {
                $keterangan = Security::sanitize($_POST['keterangan'][$siswaId] ?? '');
                $absensiModel->recordAttendance($selectedJadwal, $siswaId, $tanggal, $status, $keterangan);
            }

            FlashHelper::setSuccess('Rekap presensi berhasil disimpan.');
            header('Location: ' . BASE_URL . "index.php?url=guru/absensi&jadwal_id={$selectedJadwal}&tanggal={$tanggal}");
            exit();
        }

        $recap = $absensiModel->getRecap($selectedJadwal, $tanggal);
        require_once ROOT_PATH . 'views/guru/absensi.php';
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
            if (empty($kelasList)) {
                $kelasList = $academicModel->getKelas();
            }

            $mapelList = $academicModel->getMapelByGuru($guruId);
            if (empty($mapelList)) {
                $mapelList = $academicModel->getMapel();
            }
        }

        $selectedKelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : ($kelasList[0]['id'] ?? 0);
        $selectedMapelId = isset($_GET['mapel_id']) && $_GET['mapel_id'] !== '' ? (int)$_GET['mapel_id'] : null;

        if (!$selectedMapelId && $selectedKelasId) {
            $db = Database::getConnection();
            $stmtActiveMapel = $db->query("
                SELECT nr.mapel_id 
                FROM nilai_rapor nr
                JOIN siswa s ON nr.siswa_id = s.id
                WHERE s.kelas_id = {$selectedKelasId} 
                  AND (nr.nilai_tugas > 0 OR nr.nilai_quiz > 0 OR nr.nilai_uts > 0 OR nr.nilai_uas > 0)
                LIMIT 1
            ");
            $activeMapelId = $stmtActiveMapel ? $stmtActiveMapel->fetchColumn() : null;
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
                    $nilaiModel->saveNilai($siswaId, $selectedMapelId, 1, 1, $tugas, $quiz, $uts, $uas);
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

        $siswaList = $siswaModel->getAll($selectedKelasId);

        $existingNilai = [];
        if ($selectedKelasId && $selectedMapelId) {
            $existingNilai = $nilaiModel->getNilaiByKelasAndMapel($selectedKelasId, $selectedMapelId);
        }

        $selectedKelasInfo = null;
        if ($selectedKelasId) {
            $db = Database::getConnection();
            $stmtK = $db->query("SELECT k.*, j.nama_jurusan FROM kelas k LEFT JOIN jurusan j ON k.jurusan_id = j.id WHERE k.id = {$selectedKelasId}");
            $selectedKelasInfo = $stmtK ? $stmtK->fetch() : null;
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
        $guruId = $guru['id'];

        $examModel = new ExamModel();
        $quizList = $examModel->getQuizList(null, $guruId);

        require_once ROOT_PATH . 'views/guru/bank_soal.php';
    }

    public function scanQr() {
        $guru = $this->getGuruInfo();
        require_once ROOT_PATH . 'views/guru/scan_qr.php';
    }

    public function liveClass() {
        $guru = $this->getGuruInfo();
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

        // Header columns (Kolom A s/d Kolom J)
        fputcsv($output, ['jenis_soal', 'pertanyaan', 'bobot', 'gambar', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'jawaban_benar'], ';');

        // Sample Row 1: Pilihan Ganda dengan Gambar
        fputcsv($output, ['pg', 'Perhatikan gambar diagram HTML berikut! Tag manakah yang digunakan untuk membuat judul utama?', 10, 'diagram_html.png', '<h1>', '<body>', '<head>', '<div>', '', 'A'], ';');

        // Sample Row 2: True / False tanpa Gambar
        fputcsv($output, ['tf', 'PHP adalah bahasa pemrograman server-side.', 10, '', 'Benar', 'Salah', '', '', '', 'A'], ';');

        // Sample Row 3: Essay dengan URL Gambar
        fputcsv($output, ['essay', 'Jelaskan fungsi utama dari arsitektur jaringan pada gambar berikut!', 20, 'https://raw.githubusercontent.com/placeholder/image.png', '', '', '', '', '', ''], ';');

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
                if (($hqItem['ungraded_essay_count'] ?? 0) > 0) {
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
}
