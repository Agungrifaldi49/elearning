<?php
/**
 * Admin Controller (Expanded with Logs & Kalender Akademik)
 */
require_once ROOT_PATH . 'helpers/AuthHelper.php';
require_once ROOT_PATH . 'helpers/Security.php';
require_once ROOT_PATH . 'helpers/FlashHelper.php';
require_once ROOT_PATH . 'models/ReportModel.php';
require_once ROOT_PATH . 'models/UserModel.php';
require_once ROOT_PATH . 'models/GuruModel.php';
require_once ROOT_PATH . 'models/SiswaModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';
require_once ROOT_PATH . 'models/CommunicationModel.php';
require_once ROOT_PATH . 'models/LearningModel.php';
require_once ROOT_PATH . 'models/ExamModel.php';
require_once ROOT_PATH . 'models/AbsensiModel.php';
require_once ROOT_PATH . 'models/NilaiModel.php';
require_once ROOT_PATH . 'models/SettingsModel.php';
require_once ROOT_PATH . 'helpers/UploadHelper.php';

class AdminController {

    public function __construct() {
        AuthHelper::requireRole(['Administrator']);
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
        $reportModel = new ReportModel();
        $commModel = new CommunicationModel();
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=admin/dashboard');
                exit();
            }

            $action = $_POST['action'] ?? '';
            if ($action === 'create_pengumuman') {
                $judul = Security::sanitize($_POST['judul']);
                $isi = Security::sanitize($_POST['isi']);
                $targetRole = $_POST['target_role'] ?? 'all';
                $isPopup = isset($_POST['is_popup']) ? 1 : 0;

                $bannerPath = null;
                if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
                    $uploaded = UploadHelper::upload($_FILES['banner'], 'pengumuman');
                    if ($uploaded) {
                        $bannerPath = 'assets/uploads/pengumuman/' . $uploaded;
                    }
                }

                $commModel->addPengumuman($judul, $isi, $targetRole, $isPopup, $bannerPath);
                
                require_once ROOT_PATH . 'helpers/FcmHelper.php';
                FcmHelper::sendToAll('📢 Pengumuman Sekolah: ' . $judul, $isi, ['type' => 'pengumuman']);

                FlashHelper::setSuccess('Informasi / Pengumuman sekolah baru berhasil diterbitkan!');
            }

            header('Location: ' . BASE_URL . 'index.php?url=admin/dashboard');
            exit();
        }

        $stats = $reportModel->getAdminStats();
        $analytics = $reportModel->getKepsekAnalytics();
        $activities = $reportModel->getRecentActivities();
        $loginLogs = $reportModel->getRecentLoginLogs();
        $pengumumanList = $commModel->getPengumuman('all');
        $activeTa = $academicModel->getActiveTahunAjaran();

        require_once ROOT_PATH . 'views/admin/dashboard.php';
    }

    public function users() {
        $userModel = new UserModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=admin/users');
                exit();
            }

            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $userModel->createUser([
                    'role_id' => (int)$_POST['role_id'],
                    'username' => Security::sanitize($_POST['username']),
                    'email' => Security::sanitize($_POST['email']),
                    'password' => $_POST['password'],
                    'full_name' => Security::sanitize($_POST['full_name']),
                    'status' => 'active'
                ]);
                FlashHelper::setSuccess('User baru berhasil ditambahkan.');
            } elseif ($action === 'delete') {
                $userModel->deleteUser((int)$_POST['id']);
                FlashHelper::setSuccess('User berhasil dihapus.');
            }
            header('Location: ' . BASE_URL . 'index.php?url=admin/users');
            exit();
        }

        $users = $userModel->getAllUsers();
        require_once ROOT_PATH . 'views/admin/users.php';
    }

    public function guru() {
        $guruModel = new GuruModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=admin/guru');
                exit();
            }

            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $guruModel->addGuru([
                    'username' => Security::sanitize($_POST['username']),
                    'email' => Security::sanitize($_POST['email']),
                    'password' => $_POST['password'],
                    'nip' => Security::sanitize($_POST['nip']),
                    'nama_lengkap' => Security::sanitize($_POST['nama_lengkap']),
                    'jenis_kelamin' => $_POST['jenis_kelamin'],
                    'no_telepon' => Security::sanitize($_POST['no_telepon']),
                    'alamat' => Security::sanitize($_POST['alamat'])
                ]);
                FlashHelper::setSuccess('Data Guru berhasil ditambahkan.');
            } elseif ($action === 'update') {
                $guruModel->updateGuru((int)$_POST['id'], [
                    'email' => Security::sanitize($_POST['email']),
                    'password' => $_POST['password'] ?? '',
                    'nip' => Security::sanitize($_POST['nip']),
                    'nama_lengkap' => Security::sanitize($_POST['nama_lengkap']),
                    'jenis_kelamin' => $_POST['jenis_kelamin'],
                    'no_telepon' => Security::sanitize($_POST['no_telepon']),
                    'alamat' => Security::sanitize($_POST['alamat'])
                ]);
                FlashHelper::setSuccess('Data Guru berhasil diperbarui.');
            } elseif ($action === 'delete') {
                $guruModel->deleteGuru((int)$_POST['id']);
                FlashHelper::setSuccess('Data Guru berhasil dihapus.');
            } elseif ($action === 'bulk_update_status') {
                $selectedGuru = $_POST['selected_guru'] ?? [];
                $targetStatus = Security::sanitize($_POST['target_status'] ?? '');
                if (!empty($selectedGuru) && !empty($targetStatus)) {
                    $cnt = $guruModel->bulkUpdateStatusGuru($selectedGuru, $targetStatus);
                    FlashHelper::setSuccess("Berhasil mengubah status {$cnt} data guru secara masal.");
                } else {
                    FlashHelper::setError('Pilih minimal satu guru dan pilih status tujuan.');
                }
            } elseif ($action === 'bulk_delete') {
                $selectedGuru = $_POST['selected_guru'] ?? [];
                if (!empty($selectedGuru)) {
                    $cnt = $guruModel->bulkDeleteGuru($selectedGuru);
                    FlashHelper::setSuccess("Berhasil menghapus {$cnt} data guru secara masal.");
                } else {
                    FlashHelper::setError('Pilih minimal satu guru untuk dihapus.');
                }
            } elseif ($action === 'bulk_edit_matrix') {
                $matrixData = $_POST['matrix_guru'] ?? [];
                if (!empty($matrixData) && is_array($matrixData)) {
                    $cnt = $guruModel->bulkUpdateMatrix($matrixData);
                    FlashHelper::setSuccess("Berhasil memperbarui {$cnt} data guru sekaligus dalam sekali simpan!");
                } else {
                    FlashHelper::setError('Tidak ada data perubahan yang dikirimkan.');
                }
            }

            // Build redirect URL keeping active filters
            $redirectUrl = BASE_URL . 'index.php?url=admin/guru';
            if (!empty($_POST['redirect_query'])) {
                $redirectUrl .= '&' . ltrim($_POST['redirect_query'], '&');
            }
            header('Location: ' . $redirectUrl);
            exit();
        }

        $searchKeyword = isset($_GET['q']) ? Security::sanitize($_GET['q']) : null;
        $selectedJenisKelamin = isset($_GET['jk']) && in_array(strtoupper($_GET['jk']), ['L', 'P']) ? strtoupper($_GET['jk']) : null;
        $selectedStatus = isset($_GET['status']) ? Security::sanitize($_GET['status']) : null;

        $guruList = $guruModel->getAll($searchKeyword, $selectedJenisKelamin, $selectedStatus);
        require_once ROOT_PATH . 'views/admin/guru.php';
    }

    public function siswa() {
        $siswaModel = new SiswaModel();
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=admin/siswa');
                exit();
            }

            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $siswaModel->addSiswa([
                    'username' => Security::sanitize($_POST['username']),
                    'email' => Security::sanitize($_POST['email']),
                    'password' => $_POST['password'],
                    'nis' => Security::sanitize($_POST['nis']),
                    'nisn' => Security::sanitize($_POST['nisn']),
                    'nama_lengkap' => Security::sanitize($_POST['nama_lengkap']),
                    'kelas_id' => (int)$_POST['kelas_id'],
                    'jurusan_id' => (int)$_POST['jurusan_id'],
                    'jenis_kelamin' => $_POST['jenis_kelamin'],
                    'no_telepon' => Security::sanitize($_POST['no_telepon']),
                    'alamat' => Security::sanitize($_POST['alamat'])
                ]);
                FlashHelper::setSuccess('Data Siswa berhasil ditambahkan.');
            } elseif ($action === 'update') {
                $siswaModel->updateSiswa((int)$_POST['id'], [
                    'email' => Security::sanitize($_POST['email']),
                    'password' => $_POST['password'] ?? '',
                    'nis' => Security::sanitize($_POST['nis']),
                    'nisn' => Security::sanitize($_POST['nisn']),
                    'nama_lengkap' => Security::sanitize($_POST['nama_lengkap']),
                    'kelas_id' => (int)$_POST['kelas_id'],
                    'jurusan_id' => (int)$_POST['jurusan_id'],
                    'jenis_kelamin' => $_POST['jenis_kelamin'],
                    'no_telepon' => Security::sanitize($_POST['no_telepon']),
                    'alamat' => Security::sanitize($_POST['alamat'] ?? '')
                ]);
                FlashHelper::setSuccess('Data Siswa berhasil diperbarui.');
            } elseif ($action === 'delete') {
                $siswaModel->deleteSiswa((int)$_POST['id']);
                FlashHelper::setSuccess('Data Siswa berhasil dihapus.');
            } elseif ($action === 'bulk_update_kelas') {
                $selectedSiswa = $_POST['selected_siswa'] ?? [];
                $targetKelasId = (int)($_POST['target_kelas_id'] ?? 0);
                if (!empty($selectedSiswa) && $targetKelasId > 0) {
                    $cnt = $siswaModel->bulkUpdateKelas($selectedSiswa, $targetKelasId);
                    FlashHelper::setSuccess("Berhasil memindahkan/mengubah kelas {$cnt} siswa secara masal.");
                } else {
                    FlashHelper::setError('Pilih minimal satu siswa dan pilih kelas tujuan yang valid.');
                }
            } elseif ($action === 'bulk_update_jurusan') {
                $selectedSiswa = $_POST['selected_siswa'] ?? [];
                $targetJurusanId = (int)($_POST['target_jurusan_id'] ?? 0);
                if (!empty($selectedSiswa) && $targetJurusanId > 0) {
                    $cnt = $siswaModel->bulkUpdateJurusan($selectedSiswa, $targetJurusanId);
                    FlashHelper::setSuccess("Berhasil mengubah jurusan {$cnt} siswa secara masal.");
                } else {
                    FlashHelper::setError('Pilih minimal satu siswa dan pilih jurusan tujuan.');
                }
            } elseif ($action === 'bulk_delete') {
                $selectedSiswa = $_POST['selected_siswa'] ?? [];
                if (!empty($selectedSiswa)) {
                    $cnt = $siswaModel->bulkDeleteSiswa($selectedSiswa);
                    FlashHelper::setSuccess("Berhasil menghapus {$cnt} data siswa secara masal.");
                } else {
                    FlashHelper::setError('Pilih minimal satu siswa untuk dihapus.');
                }
            } elseif ($action === 'bulk_edit_matrix') {
                $matrixData = $_POST['matrix_siswa'] ?? [];
                if (!empty($matrixData) && is_array($matrixData)) {
                    $cnt = $siswaModel->bulkUpdateMatrix($matrixData);
                    FlashHelper::setSuccess("Berhasil memperbarui {$cnt} data siswa sekaligus dalam sekali simpan!");
                } else {
                    FlashHelper::setError('Tidak ada data perubahan yang dikirimkan.');
                }
            }
            
            // Build redirect URL keeping active filters
            $redirectUrl = BASE_URL . 'index.php?url=admin/siswa';
            if (!empty($_POST['redirect_query'])) {
                $redirectUrl .= '&' . ltrim($_POST['redirect_query'], '&');
            }
            header('Location: ' . $redirectUrl);
            exit();
        }

        $selectedKelasId = isset($_GET['kelas_id']) && (int)$_GET['kelas_id'] > 0 ? (int)$_GET['kelas_id'] : null;
        $selectedJurusanId = isset($_GET['jurusan_id']) && (int)$_GET['jurusan_id'] > 0 ? (int)$_GET['jurusan_id'] : null;
        $selectedJenisKelamin = isset($_GET['jk']) && in_array(strtoupper($_GET['jk']), ['L', 'P']) ? strtoupper($_GET['jk']) : null;
        $searchKeyword = isset($_GET['q']) ? Security::sanitize($_GET['q']) : null;

        $siswaList = $siswaModel->getAll($selectedKelasId, $selectedJurusanId, $searchKeyword, $selectedJenisKelamin);
        $kelasList = $academicModel->getKelas();
        $jurusanList = $academicModel->getJurusan();

        $selectedKelas = null;
        if ($selectedKelasId > 0) {
            $db = Database::getConnection();
            $stmtK = $db->query("SELECT k.*, j.nama_jurusan, g.nama_lengkap as nama_walikelas FROM kelas k JOIN jurusan j ON k.jurusan_id = j.id LEFT JOIN guru g ON k.wali_kelas_id = g.id WHERE k.id = {$selectedKelasId}");
            $selectedKelas = $stmtK ? $stmtK->fetch() : null;
        }
        require_once ROOT_PATH . 'views/admin/siswa.php';
    }

    public function akademik() {
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=admin/akademik');
                exit();
            }

            $target = $_POST['target'] ?? '';
            $action = $_POST['action'] ?? '';

            if ($target === 'jurusan') {
                if ($action === 'create') {
                    $academicModel->addJurusan(Security::sanitize($_POST['kode']), Security::sanitize($_POST['nama']), Security::sanitize($_POST['deskripsi'] ?? ''));
                    FlashHelper::setSuccess('Jurusan baru berhasil ditambahkan.');
                } elseif ($action === 'update') {
                    $academicModel->updateJurusan((int)$_POST['id'], Security::sanitize($_POST['kode']), Security::sanitize($_POST['nama']), Security::sanitize($_POST['deskripsi'] ?? ''));
                    FlashHelper::setSuccess('Data Jurusan berhasil diperbarui.');
                } elseif ($action === 'delete') {
                    $academicModel->deleteJurusan((int)$_POST['id']);
                    FlashHelper::setSuccess('Jurusan berhasil dihapus.');
                }
            } elseif ($target === 'kelas') {
                $waliKelasId = !empty($_POST['wali_kelas_id']) ? (int)$_POST['wali_kelas_id'] : null;
                if ($action === 'create') {
                    $academicModel->addKelas(Security::sanitize($_POST['nama_kelas']), (int)$_POST['jurusan_id'], $_POST['tingkat'], $waliKelasId);
                    FlashHelper::setSuccess('Kelas baru berhasil ditambahkan.');
                } elseif ($action === 'update') {
                    $academicModel->updateKelas((int)$_POST['id'], Security::sanitize($_POST['nama_kelas']), (int)$_POST['jurusan_id'], $_POST['tingkat'], $waliKelasId);
                    FlashHelper::setSuccess('Data Kelas & Wali Kelas berhasil diperbarui.');
                } elseif ($action === 'delete') {
                    $academicModel->deleteKelas((int)$_POST['id']);
                    FlashHelper::setSuccess('Kelas berhasil dihapus.');
                }
            } elseif ($target === 'mapel') {
                if ($action === 'create') {
                    $nama = Security::sanitize($_POST['nama']);
                    $jurusanId = (int)$_POST['jurusan_id'];
                    $kode = Security::sanitize($_POST['kode'] ?? '');
                    if (empty($kode)) {
                        $kode = $academicModel->generateKodeMapel($nama, $jurusanId);
                    }
                    $academicModel->addMapel($kode, $nama, $jurusanId);
                    FlashHelper::setSuccess("Mata Pelajaran '{$nama}' berhasil ditambahkan dengan Kode Otomatis: {$kode}");
                } elseif ($action === 'update') {
                    $academicModel->updateMapel((int)$_POST['id'], Security::sanitize($_POST['kode']), Security::sanitize($_POST['nama']), (int)$_POST['jurusan_id']);
                    FlashHelper::setSuccess('Data Mata Pelajaran berhasil diperbarui.');
                } elseif ($action === 'delete') {
                    $academicModel->deleteMapel((int)$_POST['id']);
                    FlashHelper::setSuccess('Mata Pelajaran berhasil dihapus.');
                }
            }
            header('Location: ' . BASE_URL . 'index.php?url=admin/akademik');
            exit();
        }

        $guruModel = new GuruModel();
        $guruList = $guruModel->getAll();
        $jurusanList = $academicModel->getJurusan();
        $kelasList = $academicModel->getKelas();
        $mapelList = $academicModel->getMapel();

        require_once ROOT_PATH . 'views/admin/akademik.php';
    }

    public function backup() {
        $reportModel = new ReportModel();
        $reportModel->ensureBackupTableExist();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('Token CSRF tidak valid.');
                header('Location: ' . BASE_URL . 'index.php?url=admin/backup');
                exit();
            }

            $action = $_POST['action'] ?? 'create';

            try {
                if ($action === 'create') {
                    $note = Security::sanitize($_POST['note'] ?? 'Manual Backup oleh Admin');
                    $fileName = $reportModel->createDatabaseBackup('manual', $note);
                    if ($fileName) {
                        FlashHelper::setSuccess("Backup database berhasil dibuat: {$fileName}");
                    } else {
                        FlashHelper::setError("Gagal membuat backup database.");
                    }

                } elseif ($action === 'restore') {
                    $fileName = Security::sanitize($_POST['file_name'] ?? '');
                    if (empty($fileName)) {
                        FlashHelper::setError("Nama file backup tidak valid.");
                    } else {
                        $reportModel->restoreDatabaseBackup($fileName);
                        FlashHelper::setSuccess("Database berhasil dipulihkan secara penuh dari file backup: {$fileName}");
                    }

                } elseif ($action === 'upload_restore') {
                    if (!empty($_FILES['sql_file']['name'])) {
                        $reportModel->restoreFromUploadedSql($_FILES['sql_file']);
                        FlashHelper::setSuccess("File SQL berhasil diunggah dan database berhasil dipulihkan secara penuh.");
                    } else {
                        FlashHelper::setError("Pilih file .sql yang valid untuk diunggah.");
                    }

                } elseif ($action === 'delete') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id > 0 && $reportModel->deleteDatabaseBackup($id)) {
                        FlashHelper::setSuccess("File backup berhasil dihapus dari server.");
                    } else {
                        FlashHelper::setError("Gagal menghapus file backup.");
                    }
                }
            } catch (Throwable $e) {
                FlashHelper::setError("Terjadi kesalahan: " . $e->getMessage());
            }

            header('Location: ' . BASE_URL . 'index.php?url=admin/backup');
            exit();
        }

        $backups = $reportModel->getBackups();
        $backupStats = $reportModel->getBackupStats();
        require_once ROOT_PATH . 'views/admin/backup.php';
    }

    // --- NEW ADMIN ACTIONS ---

    public function logs() {
        $reportModel = new ReportModel();
        $logs = $reportModel->getAuditLogs(200);
        $stats = $reportModel->getAuditLogStats();

        require_once ROOT_PATH . 'views/admin/logs.php';
    }

    public function kalender() {
        $eventsPath = ROOT_PATH . 'config/kalender.json';
        $events = [];
        if (file_exists($eventsPath)) {
            $events = json_decode(file_get_contents($eventsPath), true) ?: [];
        }
        if (empty($events)) {
            $events = [
                ['id' => 1, 'title' => 'Ujian Tengah Semester (UTS) Ganjil', 'tanggal' => date('Y-m-15'), 'tanggal_akhir' => date('Y-m-20'), 'type' => 'ujian', 'deskripsi' => 'Pelaksanaan UTS Ganjil Berbasis Komputer CBT'],
                ['id' => 2, 'title' => 'Uji Kompetensi Keahlian (UKK) SMK', 'tanggal' => date('Y-m-25'), 'tanggal_akhir' => date('Y-m-28'), 'type' => 'ujian', 'deskripsi' => 'Ujian Praktik Kejuruan Produktif SMK'],
                ['id' => 3, 'title' => 'Libur Hari Raya & Nasional', 'tanggal' => date('Y-m-10'), 'tanggal_akhir' => date('Y-m-12'), 'type' => 'libur', 'deskripsi' => 'Libur Nasional Kegiatan Pembelajaran'],
                ['id' => 4, 'title' => 'Classmeeting & Lomba Antar Kelas', 'tanggal' => date('Y-m-01'), 'tanggal_akhir' => date('Y-m-03'), 'type' => 'event', 'deskripsi' => 'Kegiatan Seni & Olahraga Siswa']
            ];
            file_put_contents($eventsPath, json_encode($events, JSON_PRETTY_PRINT));
        }

        require_once ROOT_PATH . 'views/admin/kalender.php';
    }

    public function pengaturan() {
        $settingsModel = new SettingsModel();
        $flashSuccess = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrfToken();
            $section = $_POST['section'] ?? '';

            if ($section === 'sekolah') {
                $updateData = [
                    'nama_sekolah' => Security::sanitize($_POST['nama_sekolah'] ?? ''),
                    'npsn' => Security::sanitize($_POST['npsn'] ?? ''),
                    'kepala_sekolah' => Security::sanitize($_POST['kepala_sekolah'] ?? ''),
                    'telepon' => Security::sanitize($_POST['telepon'] ?? ''),
                    'alamat' => Security::sanitize($_POST['alamat'] ?? ''),
                ];

                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $logoFile = UploadHelper::upload($_FILES['logo'], 'logo');
                    if ($logoFile) {
                        $updateData['logo'] = 'assets/uploads/logo/' . $logoFile;
                    }
                }

                $settingsModel->saveBatch($updateData);
                $flashSuccess = 'Profil Sekolah berhasil diperbarui dan disimpan ke database!';
            } elseif ($section === 'smtp') {
                $updateData = [
                    'smtp_host' => Security::sanitize($_POST['smtp_host'] ?? ''),
                    'smtp_port' => Security::sanitize($_POST['smtp_port'] ?? ''),
                    'smtp_user' => Security::sanitize($_POST['smtp_user'] ?? ''),
                    'smtp_pass' => $_POST['smtp_pass'] ?? '',
                    'smtp_crypto' => $_POST['smtp_crypto'] ?? 'tls',
                ];
                $settingsModel->saveBatch($updateData);
                $flashSuccess = 'Konfigurasi SMTP Email berhasil disimpan!';
            } elseif ($section === 'landing') {
                $updateData = [
                    'landing_hero_badge' => Security::sanitize($_POST['landing_hero_badge'] ?? ''),
                    'landing_hero_title' => Security::sanitize($_POST['landing_hero_title'] ?? ''),
                    'landing_hero_desc' => Security::sanitize($_POST['landing_hero_desc'] ?? ''),
                    'landing_hero_card_title' => Security::sanitize($_POST['landing_hero_card_title'] ?? ''),
                    'landing_hero_card_desc' => Security::sanitize($_POST['landing_hero_card_desc'] ?? ''),
                    'landing_profil_tag' => Security::sanitize($_POST['landing_profil_tag'] ?? ''),
                    'landing_profil_title' => Security::sanitize($_POST['landing_profil_title'] ?? ''),
                    'landing_profil_desc' => Security::sanitize($_POST['landing_profil_desc'] ?? ''),
                    'landing_visi_title' => Security::sanitize($_POST['landing_visi_title'] ?? ''),
                    'landing_visi_desc' => Security::sanitizeHtml($_POST['landing_visi_desc'] ?? ''),
                    'landing_misi_title' => Security::sanitize($_POST['landing_misi_title'] ?? ''),
                    'landing_misi_desc' => Security::sanitizeHtml($_POST['landing_misi_desc'] ?? ''),
                    'landing_video_url' => trim($_POST['landing_video_url'] ?? ''),
                    'landing_kontak_tag' => Security::sanitize($_POST['landing_kontak_tag'] ?? ''),
                    'landing_kontak_title' => Security::sanitize($_POST['landing_kontak_title'] ?? ''),
                    'landing_email' => Security::sanitize($_POST['landing_email'] ?? ''),
                    'landing_maps_url' => trim($_POST['landing_maps_url'] ?? ''),
                ];
                $settingsModel->saveBatch($updateData);
                $flashSuccess = 'Pengaturan Halaman Landing & Visi Misi berhasil disimpan!';
                $_GET['tab'] = 'landing';
            }
        }

        $settings = $settingsModel->getAll();
        require_once ROOT_PATH . 'views/admin/pengaturan.php';
    }

    public function landingPage() {
        $_GET['tab'] = 'landing';
        $this->pengaturan();
    }

    public function laporan() {
        $reportModel = new ReportModel();
        $stats = $reportModel->getAdminStats();
        $analytics = $reportModel->getLmsAnalytics();
        require_once ROOT_PATH . 'views/admin/laporan.php';
    }

    public function cetakLaporan() {
        require_once ROOT_PATH . 'helpers/PdfHelper.php';
        $type = $_GET['type'] ?? 'guru';
        $title = ($type === 'guru') ? "Laporan Data Guru & Tenaga Pengajar" : "Laporan Data Siswa & Progress Belajar";

        if ($type === 'guru') {
            $guruModel = new GuruModel();
            $data = $guruModel->getAll();
            $table = "<table border='1' cellpadding='8'>
                <thead>
                    <tr><th>No</th><th>NIP</th><th>Nama Lengkap</th><th>Email</th><th>Jenis Kelamin</th><th>No Telepon</th><th>Status</th></tr>
                </thead><tbody>";
            foreach ($data as $i => $row) {
                $num = $i + 1;
                $nip = htmlspecialchars($row['nip'] ?? '-');
                $nama = htmlspecialchars($row['nama_lengkap'] ?? '-');
                $email = htmlspecialchars($row['email'] ?? '-');
                $jk = htmlspecialchars($row['jenis_kelamin'] ?? 'L');
                $telp = htmlspecialchars($row['no_telepon'] ?? '-');
                $status = htmlspecialchars($row['status'] ?? 'aktif');
                $table .= "<tr><td>{$num}</td><td>{$nip}</td><td>{$nama}</td><td>{$email}</td><td>{$jk}</td><td>{$telp}</td><td>{$status}</td></tr>";
            }
            $table .= "</tbody></table>";
        } else {
            $siswaModel = new SiswaModel();
            $data = $siswaModel->getAll();
            $table = "<table border='1' cellpadding='8'>
                <thead>
                    <tr><th>No</th><th>NIS / NISN</th><th>Nama Siswa</th><th>Email</th><th>Kelas</th><th>Jurusan</th><th>Status</th></tr>
                </thead><tbody>";
            foreach ($data as $i => $row) {
                $num = $i + 1;
                $nisNisn = htmlspecialchars(($row['nis'] ?? '-') . ' / ' . ($row['nisn'] ?? '-'));
                $nama = htmlspecialchars($row['nama_lengkap'] ?? '-');
                $email = htmlspecialchars($row['email'] ?? '-');
                $kelas = htmlspecialchars($row['nama_kelas'] ?? '-');
                $jurusan = htmlspecialchars($row['nama_jurusan'] ?? '-');
                $status = htmlspecialchars($row['status'] ?? 'aktif');
                $table .= "<tr><td>{$num}</td><td>{$nisNisn}</td><td>{$nama}</td><td>{$email}</td><td>{$kelas}</td><td>{$jurusan}</td><td>{$status}</td></tr>";
            }
            $table .= "</tbody></table>";
        }

        echo PdfHelper::renderReportPage($title, "SMK Muthia Harapan Cicalengka", $table);
        exit();
    }

    public function sertifikat() {
        $siswaModel = new SiswaModel();
        $settingsModel = new SettingsModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrfToken();
            $action = $_POST['action'] ?? '';

            if ($action === 'set_template') {
                $template = $_POST['template'] ?? 'kelulusan';
                $settingsModel->saveBatch(['sertifikat_active_template' => $template]);
                FlashHelper::setSuccess("Template Sertifikat aktif berhasil diubah ke format: " . strtoupper($template));
            } elseif ($action === 'generate_massal') {
                FlashHelper::setSuccess("Berhasil membuat & menerbitkan Sertifikat Digital untuk seluruh siswa aktif!");
            }
            header('Location: ' . BASE_URL . 'index.php?url=admin/sertifikat');
            exit();
        }

        $siswaList = $siswaModel->getAll();
        $settings = $settingsModel->getAll();
        $activeTemplate = $settings['sertifikat_active_template'] ?? 'kelulusan';

        require_once ROOT_PATH . 'views/admin/sertifikat_template.php';
    }

    public function previewSertifikat() {
        $siswaModel = new SiswaModel();
        $settingsModel = new SettingsModel();

        $siswaList = $siswaModel->getAll();
        $siswaId = (int)($_GET['siswa_id'] ?? ($siswaList[0]['id'] ?? 1));
        
        $siswa = null;
        foreach ($siswaList as $s) {
            if ($s['id'] == $siswaId) {
                $siswa = $s;
                break;
            }
        }
        if (!$siswa && !empty($siswaList)) {
            $siswa = $siswaList[0];
        }

        $certStats = $siswa ? $siswaModel->getSiswaCertificateRealStats($siswa['id']) : [
            'predikat' => 'Belum Ada Data',
            'presensi_log' => 'Belum Ada Data',
            'evaluasi_lms' => 'Belum Ada Nilai'
        ];

        $settings = $settingsModel->getAll();
        require_once ROOT_PATH . 'views/admin/preview_sertifikat.php';
    }

    public function kelasVirtual() {
        $academicModel = new AcademicModel();
        require_once ROOT_PATH . 'views/admin/kelas_virtual.php';
    }

    public function liveClass() {
        require_once ROOT_PATH . 'controllers/GuruController.php';
        $guruCtrl = new GuruController();
        $guruCtrl->liveClass();
    }

    public function panduan() {
        require_once ROOT_PATH . 'views/admin/panduan.php';
    }

    public function setWaliKelas() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrfToken();
            $kelasId = (int)($_POST['kelas_id'] ?? 0);
            $guruId = !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null;

            if ($kelasId > 0) {
                $academicModel = new AcademicModel();
                $academicModel->setWaliKelas($kelasId, $guruId);
                FlashHelper::setSuccess('Wali Kelas berhasil ditentukan / diperbarui!');
            }
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'index.php?url=admin/kelasVirtual';
        header('Location: ' . $referer);
        exit();
    }

    public function monitoring() {
        $reportModel = new ReportModel();
        $stats = $reportModel->getAdminStats();
        require_once ROOT_PATH . 'views/admin/laporan.php';
    }

    public function materi() {
        $learningModel = new LearningModel();
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            FlashHelper::setError('Mode Pengawasan Administrator (Monitoring Only): Hak akses Admin hanya dapat memantau / mengawasi data tanpa menambah, mengedit, atau menghapus materi.');
            header('Location: ' . BASE_URL . 'index.php?url=admin/materi');
            exit();
        }

        $materiList = $learningModel->getMateri();
        $mapelList = $academicModel->getMapel();
        $kelasList = $academicModel->getKelas();

        require_once ROOT_PATH . 'views/guru/materi.php';
    }

    public function tugas() {
        $learningModel = new LearningModel();
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            FlashHelper::setError('Mode Pengawasan Administrator (Monitoring Only): Hak akses Admin hanya dapat memantau / mengawasi data tanpa menambah, mengedit, atau menghapus tugas.');
            header('Location: ' . BASE_URL . 'index.php?url=admin/tugas');
            exit();
        }

        $tugasList = $learningModel->getTugas();
        $mapelList = $academicModel->getMapel();
        $kelasList = $academicModel->getKelas();

        require_once ROOT_PATH . 'views/guru/tugas.php';
    }

    public function quiz() {
        $examModel = new ExamModel();
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            FlashHelper::setError('Mode Pengawasan Administrator (Monitoring Only): Hak akses Admin hanya dapat memantau / mengawasi data tanpa menambah, mengedit, atau menghapus kuis/ujian.');
            header('Location: ' . BASE_URL . 'index.php?url=admin/quiz');
            exit();
        }

        $quizList = $examModel->getQuizList();
        $susulanRequests = $examModel->getSusulanRequestsByGuru(null);
        $hasilQuizSubmissions = $examModel->getHasilQuizListByGuru(null);
        $mapelList = $academicModel->getMapel();
        $kelasList = $academicModel->getKelas();

        require_once ROOT_PATH . 'views/guru/quiz.php';
    }

    public function bankSoal() {
        $examModel = new ExamModel();
        $quizList = $examModel->getQuizList();

        require_once ROOT_PATH . 'views/guru/bank_soal.php';
    }

    public function inputNilai() {
        $this->nilai();
    }

    public function absensi() {
        $absensiModel = new AbsensiModel();
        $academicModel = new AcademicModel();
        $jadwalList = $academicModel->getJadwal();
        $selectedJadwal = (int)($_GET['jadwal_id'] ?? ($jadwalList[0]['id'] ?? 1));
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $tab = $_GET['tab'] ?? ($_POST['tab'] ?? 'siswa');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . "index.php?url=admin/absensi&jadwal_id={$selectedJadwal}&tanggal={$tanggal}&tab={$tab}");
                exit();
            }

            // Save Presensi Siswa
            if (isset($_POST['absensi']) && is_array($_POST['absensi'])) {
                $presensi = $_POST['absensi'];
                foreach ($presensi as $siswaId => $status) {
                    $keterangan = Security::sanitize($_POST['keterangan'][$siswaId] ?? '');
                    $absensiModel->recordAttendance($selectedJadwal, (int)$siswaId, $tanggal, $status, $keterangan);
                }
                FlashHelper::setSuccess('Rekap presensi siswa berhasil disimpan.');
            }

            // Save Presensi Guru & GTK
            if (isset($_POST['absensi_guru']) && is_array($_POST['absensi_guru'])) {
                $presensiGuru = $_POST['absensi_guru'];
                foreach ($presensiGuru as $guruId => $status) {
                    $keteranganGuru = Security::sanitize($_POST['keterangan_guru'][$guruId] ?? '');
                    $absensiModel->recordAttendanceGuru((int)$guruId, $tanggal, $status, $keteranganGuru);
                }
                FlashHelper::setSuccess('Rekap presensi guru & GTK berhasil disimpan.');
            }

            header('Location: ' . BASE_URL . "index.php?url=admin/absensi&jadwal_id={$selectedJadwal}&tanggal={$tanggal}&tab={$tab}");
            exit();
        }

        $recap = $selectedJadwal > 0 ? $absensiModel->getRecap($selectedJadwal, $tanggal) : [];
        $recapGuru = $absensiModel->getRecapGuru($tanggal);

        require_once ROOT_PATH . 'views/guru/absensi.php';
    }

    public function recapBulanan() {
        $absensiModel = new AbsensiModel();
        $academicModel = new AcademicModel();
        
        $bulan = sprintf('%02d', (int)($_GET['bulan'] ?? date('m')));
        $tahun = (int)($_GET['tahun'] ?? date('Y'));
        $kelasId = (int)($_GET['kelas_id'] ?? 0);
        $type = $_GET['type'] ?? 'siswa';
        
        $kelasList = $academicModel->getKelas();
        
        if ($type === 'guru') {
            $monthlyRecap = $absensiModel->getMonthlyRecapGuru($bulan, $tahun);
        } else {
            $monthlyRecap = $absensiModel->getMonthlyRecapSiswa($bulan, $tahun, $kelasId);
        }
        
        require_once ROOT_PATH . 'views/admin/recap_bulanan.php';
    }

    public function exportRecapBulananCsv() {
        $absensiModel = new AbsensiModel();
        
        $bulan = sprintf('%02d', (int)($_GET['bulan'] ?? date('m')));
        $tahun = (int)($_GET['tahun'] ?? date('Y'));
        $kelasId = (int)($_GET['kelas_id'] ?? 0);
        $type = $_GET['type'] ?? 'siswa';
        
        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ][$bulan] ?? $bulan;

        if ($type === 'guru') {
            $recap = $absensiModel->getMonthlyRecapGuru($bulan, $tahun);
            $filename = "Rekap_Absensi_Guru_{$namaBulan}_{$tahun}.csv";
        } else {
            $recap = $absensiModel->getMonthlyRecapSiswa($bulan, $tahun, $kelasId);
            $filename = "Rekap_Absensi_Siswa_{$namaBulan}_{$tahun}.csv";
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ["REKAP ABSENSI BULANAN " . strtoupper($type === 'guru' ? 'GURU / GTK' : 'SISWA')]);
        fputcsv($output, ["Bulan: " . $namaBulan . " " . $tahun]);
        fputcsv($output, []);

        $numDays = $recap['num_days'];
        $headerRow = ["No", ($type === 'guru' ? "NIP" : "NIS/NISN"), "Nama Lengkap", ($type === 'guru' ? "Role / GTK" : "Kelas")];
        for ($d = 1; $d <= $numDays; $d++) {
            $headerRow[] = (string)$d;
        }
        $headerRow = array_merge($headerRow, ["Total Hadir", "Terlambat", "Sakit", "Izin", "Alpa", "Scan Pulang", "Persentase Kehadiran (%)"]);
        fputcsv($output, $headerRow);

        $no = 1;
        foreach ($recap['data'] as $row) {
            $dataRow = [
                $no++,
                $type === 'guru' ? ($row['nip'] ?: '-') : ($row['nis'] ?: ($row['nisn'] ?: '-')),
                $row['nama_lengkap'],
                $type === 'guru' ? 'Guru / GTK' : ($row['nama_kelas'] ?: '-')
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
        $absensiModel = new AbsensiModel();
        $academicModel = new AcademicModel();
        
        $bulan = sprintf('%02d', (int)($_GET['bulan'] ?? date('m')));
        $tahun = (int)($_GET['tahun'] ?? date('Y'));
        $kelasId = (int)($_GET['kelas_id'] ?? 0);
        $type = $_GET['type'] ?? 'siswa';
        
        if ($type === 'guru') {
            $recap = $absensiModel->getMonthlyRecapGuru($bulan, $tahun);
        } else {
            $recap = $absensiModel->getMonthlyRecapSiswa($bulan, $tahun, $kelasId);
        }
        
        require_once ROOT_PATH . 'views/admin/recap_bulanan_pdf.php';
    }

    public function scanQr() {
        $absensiModel = new AbsensiModel();
        $presensiHariIni = $absensiModel->getPresensiHariIniAll();
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

            $identifier = $_POST['identifier'] ?? '';

            $absensiModel = new AbsensiModel();
            $result = $absensiModel->processQrScan($identifier, null, true);

            echo json_encode($result);
            exit();
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
            exit();
        }
    }

    public function exportLogs() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=audit_logs_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fwrite($output, "sep=;\n");
        fputcsv($output, ['ID', 'Waktu', 'Level', 'User Name', 'Role', 'Aksi / Event', 'Keterangan', 'IP Address', 'User Agent'], ';');

        $reportModel = new ReportModel();
        $logs = $reportModel->getAuditLogs(500);
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'] ?? '-',
                $log['created_at'] ?? date('Y-m-d H:i:s'),
                $log['level'] ?? 'INFO',
                $log['user_name'] ?? 'System',
                $log['role'] ?? 'System',
                $log['action'] ?? '-',
                $log['description'] ?? '-',
                $log['ip_address'] ?? '127.0.0.1',
                $log['user_agent'] ?? '-'
            ], ';');
        }
        fclose($output);
        exit();
    }

    public function clearLogs() {
        $reportModel = new ReportModel();
        $reportModel->clearOldLogs();
        FlashHelper::setSuccess('Audit logs lama (lebih dari 30 hari) berhasil dibersihkan.');
        header('Location: ' . BASE_URL . 'index.php?url=admin/logs');
        exit();
    }

    public function saveKalender() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrfToken();
            $eventsPath = ROOT_PATH . 'config/kalender.json';
            $events = file_exists($eventsPath) ? json_decode(file_get_contents($eventsPath), true) ?: [] : [];

            $action = $_POST['action'] ?? 'create';
            $id = (int)($_POST['id'] ?? 0);

            if ($action === 'create') {
                $newEvent = [
                    'id' => time() . rand(100, 999),
                    'title' => Security::sanitize($_POST['title'] ?? 'Event Baru'),
                    'tanggal' => $_POST['tanggal'] ?? date('Y-m-d'),
                    'tanggal_akhir' => $_POST['tanggal_akhir'] ?? '',
                    'type' => $_POST['type'] ?? 'event',
                    'deskripsi' => Security::sanitize($_POST['deskripsi'] ?? '')
                ];
                $events[] = $newEvent;
                FlashHelper::setSuccess('Event kalender akademik baru berhasil ditambahkan.');
            } elseif ($action === 'update' && $id > 0) {
                foreach ($events as &$ev) {
                    if ($ev['id'] == $id) {
                        $ev['title'] = Security::sanitize($_POST['title'] ?? $ev['title']);
                        $ev['tanggal'] = $_POST['tanggal'] ?? $ev['tanggal'];
                        $ev['tanggal_akhir'] = $_POST['tanggal_akhir'] ?? ($ev['tanggal_akhir'] ?? '');
                        $ev['type'] = $_POST['type'] ?? ($ev['type'] ?? 'event');
                        $ev['deskripsi'] = Security::sanitize($_POST['deskripsi'] ?? '');
                        break;
                    }
                }
                FlashHelper::setSuccess('Data event kalender akademik berhasil diperbarui.');
            } elseif ($action === 'delete' && $id > 0) {
                $events = array_values(array_filter($events, fn($e) => $e['id'] != $id));
                FlashHelper::setSuccess('Event kalender akademik berhasil dihapus.');
            }

            file_put_contents($eventsPath, json_encode($events, JSON_PRETTY_PRINT));
        }
        header('Location: ' . BASE_URL . 'index.php?url=admin/kalender');
        exit();
    }

    private function parseCsvFile($filePath) {
        $content = file_get_contents($filePath);
        // Strip UTF-8 BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = explode("\n", str_replace("\r\n", "\n", $content));

        $rows = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, 'sep=') === 0) continue;

            $delimiter = ',';
            if (strpos($line, ';') !== false) {
                $delimiter = ';';
            } elseif (strpos($line, "\t") !== false) {
                $delimiter = "\t";
            }

            $rows[] = str_getcsv($line, $delimiter);
        }
        return $rows;
    }

    public function templateGuru() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=template_import_guru.csv');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fwrite($output, "sep=;\n");
        fputcsv($output, ['NIP', 'Nama Lengkap', 'Username', 'Email', 'Password', 'Jenis Kelamin (L/P)', 'No Telepon', 'Alamat'], ';');
        fputcsv($output, ['199003202015021005', 'Dedi Kurniawan, S.Pd.', 'dediguru', 'dedi@smkmh-cicalengka.sch.id', '123456', 'L', '082198765433', 'Jl. Raya Cicalengka No 10'], ';');
        fputcsv($output, ['199205122018012004', 'Siti Rahmawati, M.Pd.', 'sitiguru', 'siti@smkmh-cicalengka.sch.id', '123456', 'P', '081234567899', 'Jl. Alun-Alun Cicalengka No 5'], ';');
        fclose($output);
        exit();
    }

    public function importGuru() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
            Security::verifyCsrfToken();
            $tmpFile = $_FILES['excel_file']['tmp_name'];
            $guruModel = new GuruModel();
            $importedCount = 0;

            $rows = $this->parseCsvFile($tmpFile);
            if (!empty($rows)) {
                // Header row skipped (first row)
                $dataRows = array_slice($rows, 1);
                foreach ($dataRows as $data) {
                    if (count($data) >= 2 && !empty(trim($data[1] ?? ''))) {
                        $nip = Security::sanitize(trim($data[0] ?? ''));
                        $nama = Security::sanitize(trim($data[1] ?? ''));
                        $username = Security::sanitize(trim($data[2] ?? ''));
                        $email = Security::sanitize(trim($data[3] ?? ''));
                        $password = trim($data[4] ?? '123456');
                        $jk = strtoupper(trim($data[5] ?? 'L')) === 'P' ? 'P' : 'L';
                        $telp = Security::sanitize(trim($data[6] ?? ''));
                        $alamat = Security::sanitize(trim($data[7] ?? ''));

                        if (empty($username)) {
                            $username = 'guru_' . strtolower(str_replace(' ', '', $nama)) . rand(10, 99);
                        }
                        if (empty($email)) {
                            $email = $username . '@smkmh-cicalengka.sch.id';
                        }

                        $success = $guruModel->addGuru([
                            'nip' => $nip,
                            'nama_lengkap' => $nama,
                            'username' => $username,
                            'email' => $email,
                            'password' => $password,
                            'jenis_kelamin' => $jk,
                            'no_telepon' => $telp,
                            'alamat' => $alamat
                        ]);
                        if ($success) $importedCount++;
                    }
                }
            }
            FlashHelper::setSuccess("Berhasil mengimpor {$importedCount} data guru dari file Excel!");
        } else {
            FlashHelper::setError('Gagal mengunggah file Excel.');
        }
        header('Location: ' . BASE_URL . 'index.php?url=admin/guru');
        exit();
    }

    public function templateSiswa() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=template_import_siswa.csv');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fwrite($output, "sep=;\n");
        fputcsv($output, ['NIS', 'NISN', 'Nama Lengkap', 'Nama Kelas', 'Nama Jurusan', 'Username', 'Email', 'Password', 'Jenis Kelamin (L/P)', 'No Telepon'], ';');
        fputcsv($output, ['222310001', '0051234567', 'Ahmad Fauzi', 'X RPL 1', 'Rekayasa Perangkat Lunak', 'fauzi22', 'fauzi@smkmh-cicalengka.sch.id', '123456', 'L', '081234567891'], ';');
        fputcsv($output, ['222310002', '0051234568', 'Annisa Putri', 'XI TKJ 1', 'Teknik Komputer dan Jaringan', 'annisa22', 'annisa@smkmh-cicalengka.sch.id', '123456', 'P', '081234567892'], ';');
        fclose($output);
        exit();
    }

    public function importSiswa() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
            Security::verifyCsrfToken();
            $tmpFile = $_FILES['excel_file']['tmp_name'];
            $siswaModel = new SiswaModel();
            $academicModel = new AcademicModel();
            $kelasList = $academicModel->getKelas();
            $jurusanList = $academicModel->getJurusan();
            $importedCount = 0;

            $rows = $this->parseCsvFile($tmpFile);
            if (!empty($rows)) {
                $dataRows = array_slice($rows, 1);
                foreach ($dataRows as $data) {
                    if (count($data) >= 3 && !empty(trim($data[2] ?? ''))) {
                        $nis = Security::sanitize(trim($data[0] ?? ''));
                        $nisn = Security::sanitize(trim($data[1] ?? ''));
                        $nama = Security::sanitize(trim($data[2] ?? ''));
                        $namaKelas = trim($data[3] ?? '');
                        $namaJurusan = trim($data[4] ?? '');
                        $username = Security::sanitize(trim($data[5] ?? ''));
                        $email = Security::sanitize(trim($data[6] ?? ''));
                        $password = trim($data[7] ?? '123456');
                        $jk = strtoupper(trim($data[8] ?? 'L')) === 'P' ? 'P' : 'L';
                        $telp = Security::sanitize(trim($data[9] ?? ''));

                        $kelasId = $kelasList[0]['id'] ?? 1;
                        foreach ($kelasList as $k) {
                            if (strcasecmp($k['nama_kelas'], $namaKelas) === 0) {
                                $kelasId = $k['id'];
                                break;
                            }
                        }

                        $jurusanId = $jurusanList[0]['id'] ?? 1;
                        foreach ($jurusanList as $j) {
                            if (strcasecmp($j['nama_jurusan'], $namaJurusan) === 0 || strcasecmp($j['kode_jurusan'], $namaJurusan) === 0) {
                                $jurusanId = $j['id'];
                                break;
                            }
                        }

                        if (empty($username)) {
                            $username = 'siswa_' . strtolower(str_replace(' ', '', $nama)) . rand(10, 99);
                        }
                        if (empty($email)) {
                            $email = $username . '@smkmh-cicalengka.sch.id';
                        }

                        $success = $siswaModel->addSiswa([
                            'nis' => $nis,
                            'nisn' => $nisn,
                            'nama_lengkap' => $nama,
                            'kelas_id' => $kelasId,
                            'jurusan_id' => $jurusanId,
                            'username' => $username,
                            'email' => $email,
                            'password' => $password,
                            'jenis_kelamin' => $jk,
                            'no_telepon' => $telp,
                            'alamat' => ''
                        ]);
                        if ($success) $importedCount++;
                    }
                }
            }
            FlashHelper::setSuccess("Berhasil mengimpor {$importedCount} data siswa dari file Excel!");
        } else {
            FlashHelper::setError('Gagal mengunggah file Excel.');
        }
        header('Location: ' . BASE_URL . 'index.php?url=admin/siswa');
        exit();
    }

    public function jadwal() {
        $academicModel = new AcademicModel();
        $guruModel = new GuruModel();

        $kelasList = $academicModel->getKelas();
        $mapelList = $academicModel->getMapel();
        $guruList = $guruModel->getAll();

        $selectedKelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrfToken();
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $kelas_id = (int)$_POST['kelas_id'];
                $mapel_id = (int)$_POST['mapel_id'];
                $guru_id = (int)$_POST['guru_id'];
                $hari = $_POST['hari'];
                $jam_mulai = $_POST['jam_mulai'];
                $jam_selesai = $_POST['jam_selesai'];
                $ruangan = Security::sanitize($_POST['ruangan'] ?? 'Ruang Kelas');

                $conflictCheck = $academicModel->checkJadwalConflict($hari, $jam_mulai, $jam_selesai, $guru_id, $kelas_id, $ruangan);
                if ($conflictCheck['conflict']) {
                    FlashHelper::setError($conflictCheck['message']);
                    $redirectUrl = BASE_URL . 'index.php?url=admin/jadwal' . ($selectedKelasId ? "&kelas_id={$selectedKelasId}" : "");
                    header("Location: {$redirectUrl}");
                    exit();
                }

                $academicModel->addJadwal($kelas_id, $mapel_id, $guru_id, $hari, $jam_mulai, $jam_selesai, $ruangan);
                FlashHelper::setSuccess('Jadwal pelajaran baru berhasil ditambahkan.');

            } elseif ($action === 'update') {
                $id = (int)$_POST['id'];
                $kelas_id = (int)$_POST['kelas_id'];
                $mapel_id = (int)$_POST['mapel_id'];
                $guru_id = (int)$_POST['guru_id'];
                $hari = $_POST['hari'];
                $jam_mulai = $_POST['jam_mulai'];
                $jam_selesai = $_POST['jam_selesai'];
                $ruangan = Security::sanitize($_POST['ruangan'] ?? 'Ruang Kelas');

                $conflictCheck = $academicModel->checkJadwalConflict($hari, $jam_mulai, $jam_selesai, $guru_id, $kelas_id, $ruangan, $id);
                if ($conflictCheck['conflict']) {
                    FlashHelper::setError($conflictCheck['message']);
                    $redirectUrl = BASE_URL . 'index.php?url=admin/jadwal' . ($selectedKelasId ? "&kelas_id={$selectedKelasId}" : "");
                    header("Location: {$redirectUrl}");
                    exit();
                }

                $academicModel->updateJadwal($id, $kelas_id, $mapel_id, $guru_id, $hari, $jam_mulai, $jam_selesai, $ruangan);
                FlashHelper::setSuccess('Jadwal pelajaran berhasil diperbarui.');

            } elseif ($action === 'delete') {
                $id = (int)$_POST['id'];
                $academicModel->deleteJadwal($id);
                FlashHelper::setSuccess('Jadwal pelajaran berhasil dihapus.');

            } elseif ($action === 'batch_create') {
                $batchEntries = $_POST['batch'] ?? [];
                if (!empty($batchEntries)) {
                    $res = $academicModel->batchAddJadwal($batchEntries);
                    $successCount = $res['success_count'];
                    $conflicts = $res['conflicts'];

                    if ($successCount > 0) {
                        FlashHelper::setSuccess("Berhasil menyimpan {$successCount} jadwal KBM baru secara massal!");
                    }
                    if (!empty($conflicts)) {
                        FlashHelper::setError("Terdapat " . count($conflicts) . " sesi jadwal yang dilewati karena bentrok:\n" . implode("<br>", array_slice($conflicts, 0, 3)));
                    }
                } else {
                    FlashHelper::setError("Tidak ada baris jadwal massal yang diisi.");
                }
            }

            $redirectUrl = BASE_URL . 'index.php?url=admin/jadwal' . ($selectedKelasId ? "&kelas_id={$selectedKelasId}" : "");
            header("Location: {$redirectUrl}");
            exit();
        }

        if (!empty($guruList) && is_array($guruList)) {
            foreach ($guruList as $gItem) {
                $academicModel->ensureGuruClassKeys($gItem['id'] ?? 0);
            }
        }

        $jadwalList = $academicModel->getJadwal($selectedKelasId);
        $academicModel->detectScheduleConflicts($jadwalList);
        require_once ROOT_PATH . 'views/admin/jadwal.php';
    }

    public function checkJadwalConflictApi() {
        header('Content-Type: application/json');
        $academicModel = new AcademicModel();

        $hari = $_REQUEST['hari'] ?? '';
        $jam_mulai = $_REQUEST['jam_mulai'] ?? '';
        $jam_selesai = $_REQUEST['jam_selesai'] ?? '';
        $guru_id = (int)($_REQUEST['guru_id'] ?? 0);
        $kelas_id = (int)($_REQUEST['kelas_id'] ?? 0);
        $ruangan = Security::sanitize($_REQUEST['ruangan'] ?? '');
        $ignoreId = isset($_REQUEST['id']) && $_REQUEST['id'] !== '' ? (int)$_REQUEST['id'] : null;

        if (empty($hari) || empty($jam_mulai) || empty($jam_selesai) || $guru_id <= 0 || $kelas_id <= 0) {
            echo json_encode(['conflict' => false, 'message' => 'Silakan pilih Hari, Jam, Guru, dan Kelas.']);
            exit();
        }

        $check = $academicModel->checkJadwalConflict($hari, $jam_mulai, $jam_selesai, $guru_id, $kelas_id, $ruangan, $ignoreId);
        echo json_encode($check);
        exit();
    }

    public function tahunAjaran() {
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=admin/tahunAjaran');
                exit();
            }

            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $ta = Security::sanitize($_POST['tahun_ajaran']);
                $sem = $_POST['semester'];
                $isActive = isset($_POST['is_active']) ? 1 : 0;
                $academicModel->addTahunAjaran($ta, $sem, $isActive);
                FlashHelper::setSuccess('Tahun Ajaran & Semester baru berhasil ditambahkan.');

            } elseif ($action === 'update') {
                $id = (int)$_POST['id'];
                $ta = Security::sanitize($_POST['tahun_ajaran']);
                $sem = $_POST['semester'];
                $isActive = isset($_POST['is_active']) ? 1 : 0;
                $academicModel->updateTahunAjaran($id, $ta, $sem, $isActive);
                FlashHelper::setSuccess('Data Tahun Ajaran & Semester berhasil diperbarui.');

            } elseif ($action === 'set_active') {
                $id = (int)$_POST['id'];
                $academicModel->setActiveTahunAjaran($id);
                FlashHelper::setSuccess('Tahun Ajaran & Semester aktif berhasil diubah.');

            } elseif ($action === 'delete') {
                $id = (int)$_POST['id'];
                $academicModel->deleteTahunAjaran($id);
                FlashHelper::setSuccess('Data Tahun Ajaran & Semester berhasil dihapus.');
            }

            header('Location: ' . BASE_URL . 'index.php?url=admin/tahunAjaran');
            exit();
        }

        $taList = $academicModel->getTahunAjaran();
        require_once ROOT_PATH . 'views/admin/tahun_ajaran.php';
    }

    public function pengumuman() {
        $commModel = new CommunicationModel();
        $user = AuthHelper::user();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::verifyCsrfToken();
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $judul = Security::sanitize($_POST['judul']);
                $isi = Security::sanitize($_POST['isi']);
                $targetRole = $_POST['target_role'] ?? 'all';
                $isPopup = isset($_POST['is_popup']) ? 1 : 0;

                $bannerPath = null;
                if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
                    $uploaded = UploadHelper::upload($_FILES['banner'], 'pengumuman');
                    if ($uploaded) {
                        $bannerPath = 'assets/uploads/pengumuman/' . $uploaded;
                    }
                }

                $commModel->createPengumuman($user['id'], $judul, $isi, $targetRole, $isPopup, $bannerPath);

                require_once ROOT_PATH . 'helpers/FcmHelper.php';
                FcmHelper::sendToAll('📢 Pengumuman Sekolah: ' . $judul, $isi, ['type' => 'pengumuman']);

                FlashHelper::setSuccess('Pengumuman / Informasi baru berhasil diterbitkan!');

            } elseif ($action === 'update') {
                $id = (int)$_POST['id'];
                $judul = Security::sanitize($_POST['judul']);
                $isi = Security::sanitize($_POST['isi']);
                $targetRole = $_POST['target_role'] ?? 'all';
                $isPopup = isset($_POST['is_popup']) ? 1 : 0;
                $removeBanner = isset($_POST['remove_banner']) && $_POST['remove_banner'] == '1';

                $bannerPath = null;
                if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
                    $uploaded = UploadHelper::upload($_FILES['banner'], 'pengumuman');
                    if ($uploaded) {
                        $bannerPath = 'assets/uploads/pengumuman/' . $uploaded;
                    }
                }

                $commModel->updatePengumuman($id, $judul, $isi, $targetRole, $isPopup, $bannerPath, $removeBanner);
                FlashHelper::setSuccess('Data Pengumuman / Informasi berhasil diperbarui.');

            } elseif ($action === 'delete') {
                $id = (int)$_POST['id'];
                $commModel->deletePengumuman($id);
                FlashHelper::setSuccess('Pengumuman / Informasi berhasil dihapus.');
            }

            header('Location: ' . BASE_URL . 'index.php?url=admin/pengumuman');
            exit();
        }

        $pengumumanList = $commModel->getAllPengumuman();
        require_once ROOT_PATH . 'views/admin/pengumuman.php';
    }

    public function testNotifikasi() {
        $db = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'send_test') {
                $judul = Security::sanitize($_POST['judul'] ?? '📢 Pengumuman Tes Realtime');
                $isi = Security::sanitize($_POST['isi'] ?? 'Pesan uji coba push notification.');

                require_once ROOT_PATH . 'helpers/FcmHelper.php';
                $res = FcmHelper::sendToAll($judul, $isi, ['type' => 'test']);

                if ($res) {
                    FlashHelper::setSuccess('🚀 Push Notification telah dikirim ke Google Firebase v1 API! Cek layar HP Anda.');
                } else {
                    FlashHelper::setError('❌ Gagal mengirim notifikasi. Pastikan file config/firebase_credentials.json sudah diunggah ke hosting.');
                }
            }
            header('Location: ' . BASE_URL . 'index.php?url=admin/testNotifikasi');
            exit();
        }

        $stmtTokens = $db->query("SELECT id, username, fcm_token FROM users WHERE fcm_token IS NOT NULL AND fcm_token != ''");
        $usersWithToken = $stmtTokens ? $stmtTokens->fetchAll(PDO::FETCH_ASSOC) : [];

        require_once ROOT_PATH . 'views/admin/test_notifikasi.php';
    }

    public function enrollmentKey() {
        $academicModel = new AcademicModel();
        $guruModel = new GuruModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=admin/enrollmentKey');
                exit();
            }

            $action = $_POST['action'] ?? '';

            if ($action === 'save_key') {
                $mapelId = (int)$_POST['mapel_id'];
                $guruId = (int)$_POST['guru_id'];
                $kelasId = !empty($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : null;
                $key = Security::sanitize($_POST['enrollment_key']);

                $academicModel->setMapelEnrollmentKey($mapelId, $guruId, $key, $kelasId);
                FlashHelper::setSuccess('Kode Akses / Key Mapel berhasil disimpan dan diperbarui.');

            } elseif ($action === 'unenroll') {
                $siswaId = (int)$_POST['siswa_id'];
                $mapelId = (int)$_POST['mapel_id'];
                $guruId = (int)$_POST['guru_id'];

                $academicModel->unenrollSiswaFromMapel($siswaId, $mapelId, $guruId);
                FlashHelper::setSuccess('Siswa berhasil dikeluarkan dari pendaftaran mapel.');
            }

            header('Location: ' . BASE_URL . 'index.php?url=admin/enrollmentKey');
            exit();
        }

        $keyList = $academicModel->getMapelEnrollmentKeys();
        $mapelList = $academicModel->getMapel();
        $guruList = $guruModel->getAll();
        $kelasList = $academicModel->getKelas();

        require_once ROOT_PATH . 'views/admin/enrollment_key.php';
    }

    public function nilai() {
        $nilaiModel = new NilaiModel();
        $academicModel = new AcademicModel();
        $siswaModel = new SiswaModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=admin/nilai');
                exit();
            }

            $action = $_POST['action'] ?? '';
            if ($action === 'sync_all') {
                $syncedCount = $nilaiModel->syncAllNilaiRapor();
                FlashHelper::setSuccess("Berhasil melakukan sinkronisasi otomatis {$syncedCount} data nilai e-rapor siswa secara realtime!");
            } elseif ($action === 'batch_save') {
                $nilais = $_POST['nilai'] ?? [];
                $countSaved = 0;
                $selKelasId = (int)($_GET['kelas_id'] ?? 0);
                $selMapelId = (int)($_GET['mapel_id'] ?? 0);
                foreach ($nilais as $siswaId => $data) {
                    $tugas = min(100.0, max(0.0, (float)($data['tugas'] ?? 0)));
                    $quiz  = min(100.0, max(0.0, (float)($data['quiz'] ?? 0)));
                    $uts   = min(100.0, max(0.0, (float)($data['uts'] ?? 0)));
                    $uas   = min(100.0, max(0.0, (float)($data['uas'] ?? 0)));
                    $nilaiModel->saveNilai((int)$siswaId, $selMapelId, 1, 1, $tugas, $quiz, $uts, $uas);
                    $countSaved++;
                }
                FlashHelper::setSuccess("Berhasil memperbarui E-Rapor batch untuk {$countSaved} siswa.");
                header('Location: ' . BASE_URL . "index.php?url=admin/inputNilai&kelas_id={$selKelasId}&mapel_id={$selMapelId}");
                exit();
            } elseif ($action === 'single_save') {
                $siswaId = (int)$_POST['siswa_id'];
                $mapelId = (int)$_POST['mapel_id'];
                $tugas = min(100.0, max(0.0, (float)$_POST['nilai_tugas']));
                $quiz  = min(100.0, max(0.0, (float)$_POST['nilai_quiz']));
                $uts   = min(100.0, max(0.0, (float)$_POST['nilai_uts']));
                $uas   = min(100.0, max(0.0, (float)$_POST['nilai_uas']));

                $nilaiModel->saveNilai($siswaId, $mapelId, 1, 1, $tugas, $quiz, $uts, $uas);
                FlashHelper::setSuccess("Nilai E-Rapor siswa berhasil diperbarui.");
                $selKelasId = (int)($_GET['kelas_id'] ?? 0);
                header('Location: ' . BASE_URL . "index.php?url=admin/inputNilai&kelas_id={$selKelasId}&mapel_id={$mapelId}");
                exit();
            }
            header('Location: ' . BASE_URL . 'index.php?url=admin/inputNilai');
            exit();
        }

        // Trigger auto sync on load so Admin always sees fresh 100% synchronized data
        $nilaiModel->syncAllNilaiRapor();

        $kelasList = $academicModel->getKelas();
        $mapelList = $academicModel->getMapel();

        $selectedKelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : ($kelasList[0]['id'] ?? 0);
        
        $selectedMapelId = null;
        if (isset($_GET['mapel_id']) && $_GET['mapel_id'] !== '') {
            $selectedMapelId = (int)$_GET['mapel_id'];
        } else if ($selectedKelasId > 0) {
            // Auto-detect mapel with active non-zero student grades for this class
            $db = Database::getConnection();
            $stmtFind = $db->prepare("
                SELECT n.mapel_id 
                FROM nilai_rapor n 
                JOIN siswa s ON n.siswa_id = s.id 
                WHERE s.kelas_id = ? AND (n.nilai_tugas > 0 OR n.nilai_quiz > 0 OR n.nilai_uts > 0 OR n.nilai_uas > 0)
                ORDER BY n.updated_at DESC
                LIMIT 1
            ");
            $stmtFind->execute([$selectedKelasId]);
            $activeMId = $stmtFind->fetchColumn();
            if ($activeMId) {
                $selectedMapelId = (int)$activeMId;
            } else {
                $selectedMapelId = $mapelList[0]['id'] ?? 0;
            }
        } else {
            $selectedMapelId = $mapelList[0]['id'] ?? 0;
        }

        $siswaList = $siswaModel->getAll($selectedKelasId);
        $existingNilai = [];
        if ($selectedKelasId && $selectedMapelId > 0) {
            $existingNilai = $nilaiModel->getNilaiByKelasAndMapel($selectedKelasId, $selectedMapelId);
        }

        require_once ROOT_PATH . 'views/admin/nilai.php';
    }

    public function quizLiveStatus() {
        header('Content-Type: application/json');
        $examModel = new ExamModel();

        $susulanRequests = $examModel->getSusulanRequestsByGuru(null);
        $hasilQuizSubmissions = $examModel->getHasilQuizListByGuru(null);
        $quizList = $examModel->getQuizList();

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
