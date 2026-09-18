<?php
/**
 * Kepala Sekolah Controller
 * Full System Executive Supervision & Monitoring
 * E-Learning SMK Muthia Harapan Cicalengka
 */
require_once ROOT_PATH . 'helpers/AuthHelper.php';
require_once ROOT_PATH . 'helpers/PdfHelper.php';
require_once ROOT_PATH . 'helpers/Security.php';
require_once ROOT_PATH . 'helpers/FlashHelper.php';
require_once ROOT_PATH . 'helpers/UploadHelper.php';
require_once ROOT_PATH . 'models/ReportModel.php';
require_once ROOT_PATH . 'models/GuruModel.php';
require_once ROOT_PATH . 'models/SiswaModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';
require_once ROOT_PATH . 'models/AbsensiModel.php';
require_once ROOT_PATH . 'models/LearningModel.php';
require_once ROOT_PATH . 'models/ExamModel.php';
require_once ROOT_PATH . 'models/NilaiModel.php';
require_once ROOT_PATH . 'models/CommunicationModel.php';

class KepsekController {

    public function __construct() {
        AuthHelper::requireRole(['Kepala Sekolah', 'Administrator']);
        try {
            $reportModel = new ReportModel();
            $reportModel->ensureSupervisiTable();
        } catch (\Throwable $e) {}
    }

    /**
     * 1. Dashboard Eksekutif
     */
    public function dashboard() {
        $reportModel = new ReportModel();
        $guruModel = new GuruModel();
        $siswaModel = new SiswaModel();

        $stats = $reportModel->getKepsekStats();
        $guruList = $guruModel->getAll();
        $siswaList = $siswaModel->getAll();
        $todayAttendance = $reportModel->getTodayTeacherAttendanceStats();

        require_once ROOT_PATH . 'views/kepsek/dashboard.php';
    }

    /**
     * 2. Monitoring Guru & Produktivitas
     */
    public function monitoringGuru() {
        $reportModel = new ReportModel();
        $reportModel->ensureSupervisiTable();
        $db = Database::getConnection();

        $hasSupervisi = false;
        try {
            $check = $db->query("SHOW TABLES LIKE 'supervisi_guru'")->fetch();
            $hasSupervisi = !empty($check);
        } catch (\Throwable $e) {
            $hasSupervisi = false;
        }

        $supervisiCountSql = $hasSupervisi ? "(SELECT COUNT(*) FROM supervisi_guru sg WHERE sg.guru_id = g.id)" : "0";
        $supervisiNilaiSql = $hasSupervisi ? "(SELECT nilai_akhir FROM supervisi_guru sg WHERE sg.guru_id = g.id ORDER BY tanggal_supervisi DESC LIMIT 1)" : "NULL";

        $guruList = $db->query("
            SELECT g.*, u.username, u.email,
                   (SELECT COUNT(*) FROM materi m WHERE m.guru_id = g.id) as total_materi,
                   (SELECT COUNT(*) FROM tugas t WHERE t.guru_id = g.id) as total_tugas,
                   (SELECT COUNT(*) FROM quiz q WHERE q.guru_id = g.id) as total_quiz,
                   {$supervisiCountSql} as total_supervisi,
                   {$supervisiNilaiSql} as nilai_supervisi_terakhir,
                   (SELECT GROUP_CONCAT(DISTINCT k.nama_kelas SEPARATOR ', ') 
                    FROM jadwal j JOIN kelas k ON j.kelas_id = k.id WHERE j.guru_id = g.id) as kelas_ajar
            FROM guru g
            JOIN users u ON g.user_id = u.id
            ORDER BY g.nama_lengkap ASC
        ")->fetchAll();

        require_once ROOT_PATH . 'views/kepsek/monitoring_guru.php';
    }

    /**
     * 3. Monitoring Presensi Selfie Guru Hari Ini & Riwayat
     */
    public function presensiGuru() {
        $reportModel = new ReportModel();
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        
        $attendanceStats = $reportModel->getTodayTeacherAttendanceStats($tanggal);
        
        require_once ROOT_PATH . 'views/kepsek/presensi_guru.php';
    }

    /**
     * 4. Supervisi Akademik Guru & Penilaian Kinerja
     */
    public function supervisiGuru() {
        $reportModel = new ReportModel();
        $academicModel = new AcademicModel();
        $guruModel = new GuruModel();
        $userSession = AuthHelper::user();

        // Handle Form Submission (Simpan / Update Supervisi)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=kepsek/supervisiGuru');
                exit();
            }

            $action = $_POST['action'] ?? 'save';

            if ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($reportModel->deleteSupervisi($id)) {
                    FlashHelper::setSuccess('Data supervisi akademik berhasil dihapus.');
                } else {
                    FlashHelper::setError('Gagal menghapus data supervisi.');
                }
            } else {
                $data = [
                    'id' => !empty($_POST['id']) ? (int)$_POST['id'] : null,
                    'guru_id' => (int)$_POST['guru_id'],
                    'kepsek_id' => (int)$userSession['id'],
                    'tanggal_supervisi' => $_POST['tanggal_supervisi'] ?? date('Y-m-d'),
                    'mapel_id' => !empty($_POST['mapel_id']) ? (int)$_POST['mapel_id'] : null,
                    'kelas_id' => !empty($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : null,
                    'skor_perencanaan' => (float)($_POST['skor_perencanaan'] ?? 0),
                    'skor_pelaksanaan' => (float)($_POST['skor_pelaksanaan'] ?? 0),
                    'skor_evaluasi' => (float)($_POST['skor_evaluasi'] ?? 0),
                    'skor_kedisiplinan' => (float)($_POST['skor_kedisiplinan'] ?? 0),
                    'catatan_kekuatan' => Security::sanitize($_POST['catatan_kekuatan'] ?? ''),
                    'catatan_perbaikan' => Security::sanitize($_POST['catatan_perbaikan'] ?? ''),
                    'rekomendasi_tindak_lanjut' => Security::sanitize($_POST['rekomendasi_tindak_lanjut'] ?? ''),
                    'status' => $_POST['status'] ?? 'final'
                ];

                $res = $reportModel->saveSupervisi($data);
                if ($res) {
                    FlashHelper::setSuccess('Lembar supervisi akademik guru berhasil disimpan.');
                } else {
                    FlashHelper::setError('Gagal menyimpan lembar supervisi akademik.');
                }
            }

            header('Location: ' . BASE_URL . 'index.php?url=kepsek/supervisiGuru');
            exit();
        }

        $guruList = $guruModel->getAll();
        $mapelList = $academicModel->getMapel();
        $kelasList = $academicModel->getKelas();
        $supervisiList = $reportModel->getSupervisiList();

        $selectedGuruId = (int)($_GET['guru_id'] ?? 0);
        $editId = (int)($_GET['edit_id'] ?? 0);
        $editData = $editId > 0 ? $reportModel->getSupervisiById($editId) : null;

        require_once ROOT_PATH . 'views/kepsek/supervisi_guru.php';
    }

    /**
     * 5. Monitoring Siswa & Progress Belajar
     */
    public function monitoringSiswa() {
        $db = Database::getConnection();
        $siswaList = $db->query("
            SELECT s.*, k.nama_kelas, j.nama_jurusan, u.username, u.email,
                   ROUND(COALESCE((SELECT AVG(n.nilai_akhir) FROM nilai_rapor n WHERE n.siswa_id = s.id), 0), 1) as avg_rapor,
                   ROUND(COALESCE((SELECT AVG(hq.total_nilai) FROM hasil_quiz hq WHERE hq.siswa_id = s.id), 0), 1) as avg_quiz,
                   (SELECT COUNT(*) FROM pengumpulan_tugas pt WHERE pt.siswa_id = s.id) as total_tugas_dikumpul
            FROM siswa s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN jurusan j ON s.jurusan_id = j.id
            ORDER BY k.tingkat ASC, k.nama_kelas ASC, s.nama_lengkap ASC
        ")->fetchAll();

        require_once ROOT_PATH . 'views/kepsek/monitoring_siswa.php';
    }

    /**
     * 6. Monitoring Presensi Harian Siswa per Rombel
     */
    public function presensiSiswa() {
        $absensiModel = new AbsensiModel();
        $academicModel = new AcademicModel();

        $kelasList = $academicModel->getKelas();
        $selectedKelasId = (int)($_GET['kelas_id'] ?? ($kelasList[0]['id'] ?? 1));
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');

        $jadwalList = $academicModel->getJadwal();
        $filteredJadwal = array_filter($jadwalList, fn($j) => (int)$j['kelas_id'] === $selectedKelasId);
        $selectedJadwal = !empty($filteredJadwal) ? (int)reset($filteredJadwal)['id'] : 0;

        $recap = $selectedJadwal > 0 ? $absensiModel->getRecap($selectedJadwal, $tanggal) : [];

        require_once ROOT_PATH . 'views/kepsek/presensi_siswa.php';
    }

    /**
     * 7. Rekap Absensi Bulanan (Guru & Siswa)
     */
    public function recapBulanan() {
        $absensiModel = new AbsensiModel();
        $academicModel = new AcademicModel();

        $bulan = sprintf('%02d', (int)($_GET['bulan'] ?? date('m')));
        $tahun = (int)($_GET['tahun'] ?? date('Y'));
        $kelasId = (int)($_GET['kelas_id'] ?? 0);
        $type = $_GET['type'] ?? 'guru';

        $kelasList = $academicModel->getKelas();

        if ($type === 'guru') {
            $monthlyRecap = $absensiModel->getMonthlyRecapGuru($bulan, $tahun);
        } else {
            $monthlyRecap = $absensiModel->getMonthlyRecapSiswa($bulan, $tahun, $kelasId);
        }

        require_once ROOT_PATH . 'views/admin/recap_bulanan.php';
    }

    /**
     * 8. Monitoring Pembelajaran Virtual & Rombel
     */
    public function monitoringPembelajaran() {
        $db = Database::getConnection();

        $summary = [
            'total_kelas' => (int)$db->query("SELECT COUNT(*) FROM kelas")->fetchColumn(),
            'total_materi' => (int)$db->query("SELECT COUNT(*) FROM materi")->fetchColumn(),
            'total_tugas' => (int)$db->query("SELECT COUNT(*) FROM tugas")->fetchColumn(),
            'total_quiz' => (int)$db->query("SELECT COUNT(*) FROM quiz")->fetchColumn(),
        ];

        $kelasPembelajaran = $db->query("
            SELECT k.*, j.nama_jurusan, g.nama_lengkap as nama_walikelas,
                   (SELECT COUNT(*) FROM materi m WHERE m.kelas_id = k.id) as total_materi,
                   (SELECT COUNT(*) FROM tugas t WHERE t.kelas_id = k.id) as total_tugas,
                   (SELECT COUNT(*) FROM quiz q WHERE q.kelas_id = k.id) as total_quiz,
                   ROUND(COALESCE((SELECT AVG(n.nilai_akhir) FROM nilai_rapor n JOIN siswa s ON n.siswa_id = s.id WHERE s.kelas_id = k.id), 0), 1) as avg_nilai
            FROM kelas k
            LEFT JOIN jurusan j ON k.jurusan_id = j.id
            LEFT JOIN guru g ON k.wali_kelas_id = g.id
            ORDER BY k.tingkat ASC, k.nama_kelas ASC
        ")->fetchAll();

        require_once ROOT_PATH . 'views/kepsek/monitoring_pembelajaran.php';
    }

    /**
     * 9. Monitoring Modul Materi & Video Ajar
     */
    public function monitoringMateri() {
        $learningModel = new LearningModel();
        $academicModel = new AcademicModel();

        $materiList = $learningModel->getMateri();
        $mapelList = $academicModel->getMapel();
        $kelasList = $academicModel->getKelas();

        require_once ROOT_PATH . 'views/kepsek/monitoring_materi.php';
    }

    /**
     * 10. Monitoring Tugas & Pengumpulan Siswa
     */
    public function monitoringTugas() {
        $learningModel = new LearningModel();
        $academicModel = new AcademicModel();

        $tugasList = $learningModel->getTugas();
        $mapelList = $academicModel->getMapel();
        $kelasList = $academicModel->getKelas();

        require_once ROOT_PATH . 'views/kepsek/monitoring_tugas.php';
    }

    /**
     * 11. Monitoring Quiz & CBT Ujian
     */
    public function monitoringQuiz() {
        $examModel = new ExamModel();
        $academicModel = new AcademicModel();

        $quizList = $examModel->getQuizzes();
        $mapelList = $academicModel->getMapel();
        $kelasList = $academicModel->getKelas();

        require_once ROOT_PATH . 'views/kepsek/monitoring_quiz.php';
    }

    /**
     * 12. Monitoring Rekap Leger Nilai E-Rapor
     */
    public function monitoringNilai() {
        $academicModel = new AcademicModel();
        $nilaiModel = new NilaiModel();

        $kelasList = $academicModel->getKelas();
        $mapelList = $academicModel->getMapel();

        $selectedKelasId = (int)($_GET['kelas_id'] ?? ($kelasList[0]['id'] ?? 1));
        $selectedMapelId = (int)($_GET['mapel_id'] ?? ($mapelList[0]['id'] ?? 1));

        $nilaiList = $nilaiModel->getRekapNilai($selectedKelasId, $selectedMapelId);

        // Render via input_nilai in read-only mode for Kepsek
        require_once ROOT_PATH . 'views/guru/input_nilai.php';
    }

    /**
     * 13. Monitoring Jadwal Pelajaran Sekolah
     */
    public function monitoringJadwal() {
        $academicModel = new AcademicModel();
        $guruModel = new GuruModel();

        $jadwalList = $academicModel->getJadwal();
        $kelasList = $academicModel->getKelas();
        $mapelList = $academicModel->getMapel();
        $guruList = $guruModel->getAll();

        require_once ROOT_PATH . 'views/kepsek/monitoring_jadwal.php';
    }

    /**
     * 14. Monitoring Live Virtual Meeting Room
     */
    public function monitoringLiveClass() {
        require_once ROOT_PATH . 'controllers/GuruController.php';
        $guruCtrl = new GuruController();
        $guruCtrl->liveClass();
    }

    /**
     * 15. Maklumat & Pengumuman Resmi Kepala Sekolah
     */
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
                FcmHelper::sendToAll('📢 Maklumat Kepala Sekolah: ' . $judul, $isi, ['type' => 'pengumuman']);

                FlashHelper::setSuccess('Maklumat / Pengumuman Resmi Kepala Sekolah berhasil diterbitkan!');
            } elseif ($action === 'delete') {
                $id = (int)$_POST['id'];
                $commModel->deletePengumuman($id);
                FlashHelper::setSuccess('Pengumuman berhasil dihapus.');
            }

            header('Location: ' . BASE_URL . 'index.php?url=kepsek/pengumuman');
            exit();
        }

        $pengumumanList = $commModel->getPengumuman();
        require_once ROOT_PATH . 'views/admin/pengumuman.php';
    }

    /**
     * 16. Kalender Akademik Sekolah
     */
    public function kalender() {
        $eventsPath = ROOT_PATH . 'config/kalender.json';
        $events = [];
        if (file_exists($eventsPath)) {
            $events = json_decode(file_get_contents($eventsPath), true) ?: [];
        }
        if (empty($events)) {
            $events = [
                ['id' => 1, 'title' => 'Ujian Tengah Semester (UTS) Ganjil', 'tanggal' => date('Y-m-15'), 'tanggal_akhir' => date('Y-m-20'), 'type' => 'ujian', 'deskripsi' => 'Pelaksanaan UTS Ganjil Berbasis Komputer CBT'],
                ['id' => 2, 'title' => 'Rapat Pleno Kenaikan Kelas & Supervisi Guru', 'tanggal' => date('Y-m-25'), 'type' => 'libur', 'deskripsi' => 'Evaluasi KBM bersama Pimpinan dan Majelis Guru'],
                ['id' => 3, 'title' => 'Pembagian Rapor Semester & Sertifikat', 'tanggal' => date('Y-m-28'), 'type' => 'rapor', 'deskripsi' => 'Penyerahan E-Rapor Digital ke Orang Tua/Wali']
            ];
        }

        require_once ROOT_PATH . 'views/admin/kalender.php';
    }

    /**
     * 17. Audit Log Aktivitas & Keamanan Sistem
     */
    public function logs() {
        $reportModel = new ReportModel();
        $logs = $reportModel->getAuditLogs(200);
        $stats = $reportModel->getAuditLogStats();

        require_once ROOT_PATH . 'views/admin/logs.php';
    }

    /**
     * 18. Status Kesehatan Sistem & Backup Database
     */
    public function backupStatus() {
        $reportModel = new ReportModel();
        $backups = $reportModel->getBackups();
        $backupStats = $reportModel->getBackupStats();

        require_once ROOT_PATH . 'views/kepsek/backup_status.php';
    }

    /**
     * 19. Cetak Laporan Eksekutif Resmi (PDF)
     */
    public function cetakLaporan() {
        $type = $_GET['type'] ?? 'guru';
        $db = Database::getConnection();
        $reportModel = new ReportModel();

        if ($type === 'guru') {
            $title = "Laporan Resmi Monitoring Tenaga Pengajar / Guru";
            $data = $db->query("
                SELECT g.*, u.email,
                       (SELECT COUNT(*) FROM materi m WHERE m.guru_id = g.id) as total_materi,
                       (SELECT COUNT(*) FROM tugas t WHERE t.guru_id = g.id) as total_tugas,
                       (SELECT COUNT(*) FROM quiz q WHERE q.guru_id = g.id) as total_quiz
                FROM guru g
                JOIN users u ON g.user_id = u.id
                ORDER BY g.nama_lengkap ASC
            ")->fetchAll();

            $table = "<table border='1' cellpadding='8' cellspacing='0' style='width:100%; border-collapse:collapse;'>
                <thead>
                    <tr style='background-color:#f1f5f9; text-align:left;'>
                        <th style='width:30px;'>No</th>
                        <th>NIP</th>
                        <th>Nama Guru</th>
                        <th>No. Telepon / Email</th>
                        <th style='text-align:center;'>Modul</th>
                        <th style='text-align:center;'>Tugas</th>
                        <th style='text-align:center;'>Kuis CBT</th>
                        <th style='text-align:center;'>Status</th>
                    </tr>
                </thead><tbody>";
            foreach ($data as $i => $row) {
                $num = $i + 1;
                $nip = htmlspecialchars($row['nip'] ?? '-');
                $nama = htmlspecialchars($row['nama_lengkap']);
                $kontak = htmlspecialchars($row['no_telepon'] ?? '-') . " / " . htmlspecialchars($row['email'] ?? '-');
                $status = ucfirst($row['status'] ?? 'aktif');
                $table .= "<tr>
                    <td>{$num}</td>
                    <td><code>{$nip}</code></td>
                    <td><b>{$nama}</b></td>
                    <td>{$kontak}</td>
                    <td style='text-align:center;'>{$row['total_materi']} File</td>
                    <td style='text-align:center;'>{$row['total_tugas']} Tugas</td>
                    <td style='text-align:center;'>{$row['total_quiz']} Kuis</td>
                    <td style='text-align:center;'>{$status}</td>
                </tr>";
            }
            $table .= "</tbody></table>";

        } elseif ($type === 'presensi_guru') {
            $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
            $title = "Laporan Presensi Kehadiran Tenaga Pengajar / Guru - " . date('d F Y', strtotime($tanggal));
            $attStats = $reportModel->getTodayTeacherAttendanceStats($tanggal);

            $table = "<div style='margin-bottom:15px;'>
                <b>Ringkasan:</b> Total: {$attStats['total_guru']} Guru | Hadir: {$attStats['hadir']} | Terlambat: {$attStats['terlambat']} | Izin/Sakit: {$attStats['izin']} | Belum Hadir: {$attStats['belum_hadir']}
            </div>
            <table border='1' cellpadding='8' cellspacing='0' style='width:100%; border-collapse:collapse;'>
                <thead>
                    <tr style='background-color:#f1f5f9; text-align:left;'>
                        <th style='width:30px;'>No</th>
                        <th>NIP</th>
                        <th>Nama Guru</th>
                        <th style='text-align:center;'>Waktu Masuk</th>
                        <th style='text-align:center;'>Waktu Pulang</th>
                        <th style='text-align:center;'>Jarak GPS</th>
                        <th style='text-align:center;'>Status Kehadiran</th>
                    </tr>
                </thead><tbody>";
            foreach ($attStats['list'] as $i => $row) {
                $num = $i + 1;
                $nip = htmlspecialchars($row['nip'] ?? '-');
                $nama = htmlspecialchars($row['nama_lengkap']);
                $wMasuk = !empty($row['waktu_masuk']) ? date('H:i', strtotime($row['waktu_masuk'])) . ' WIB' : '-';
                $wPulang = !empty($row['waktu_pulang']) ? date('H:i', strtotime($row['waktu_pulang'])) . ' WIB' : '-';
                $jarak = !empty($row['jarak_masuk_meter']) ? $row['jarak_masuk_meter'] . ' m' : '-';
                $st = $row['status_kehadiran'] ?? (!empty($row['waktu_masuk']) ? 'Hadir' : 'Belum Hadir');

                $table .= "<tr>
                    <td>{$num}</td>
                    <td><code>{$nip}</code></td>
                    <td><b>{$nama}</b></td>
                    <td style='text-align:center;'>{$wMasuk}</td>
                    <td style='text-align:center;'>{$wPulang}</td>
                    <td style='text-align:center;'>{$jarak}</td>
                    <td style='text-align:center;'><b>{$st}</b></td>
                </tr>";
            }
            $table .= "</tbody></table>";

        } elseif ($type === 'supervisi') {
            $title = "Laporan Resmi Hasil Supervisi Akademik & Kinerja Pengajar";
            $supList = $reportModel->getSupervisiList();

            $table = "<table border='1' cellpadding='8' cellspacing='0' style='width:100%; border-collapse:collapse;'>
                <thead>
                    <tr style='background-color:#f1f5f9; text-align:left;'>
                        <th style='width:30px;'>No</th>
                        <th>Tanggal</th>
                        <th>Nama Guru</th>
                        <th>Mapel & Kelas</th>
                        <th style='text-align:center;'>Nilai Akhir</th>
                        <th style='text-align:center;'>Predikat</th>
                        <th>Catatan & Rekomendasi Kepala Sekolah</th>
                    </tr>
                </thead><tbody>";
            foreach ($supList as $i => $row) {
                $num = $i + 1;
                $tgl = date('d/m/Y', strtotime($row['tanggal_supervisi']));
                $nama = htmlspecialchars($row['nama_guru']);
                $mpKls = htmlspecialchars(($row['nama_mapel'] ?? '-') . ' (' . ($row['nama_kelas'] ?? '-') . ')');
                $nilai = number_format((float)$row['nilai_akhir'], 1);
                $pred = htmlspecialchars($row['predikat'] ?? '-');
                $rekom = htmlspecialchars($row['rekomendasi_tindak_lanjut'] ?? '-');

                $table .= "<tr>
                    <td>{$num}</td>
                    <td>{$tgl}</td>
                    <td><b>{$nama}</b></td>
                    <td>{$mpKls}</td>
                    <td style='text-align:center;'><b>{$nilai}</b></td>
                    <td style='text-align:center;'>{$pred}</td>
                    <td>{$rekom}</td>
                </tr>";
            }
            $table .= "</tbody></table>";

        } else {
            $title = "Laporan Resmi Monitoring Siswa & Progress Belajar";
            $data = $db->query("
                SELECT s.*, k.nama_kelas, j.nama_jurusan,
                       ROUND(COALESCE((SELECT AVG(n.nilai_akhir) FROM nilai_rapor n WHERE n.siswa_id = s.id), 0), 1) as avg_rapor,
                       (SELECT COUNT(*) FROM pengumpulan_tugas pt WHERE pt.siswa_id = s.id) as total_tugas_dikumpul
                FROM siswa s
                LEFT JOIN kelas k ON s.kelas_id = k.id
                LEFT JOIN jurusan j ON s.jurusan_id = j.id
                ORDER BY k.tingkat ASC, k.nama_kelas ASC, s.nama_lengkap ASC
            ")->fetchAll();

            $table = "<table border='1' cellpadding='8' cellspacing='0' style='width:100%; border-collapse:collapse;'>
                <thead>
                    <tr style='background-color:#f1f5f9; text-align:left;'>
                        <th style='width:30px;'>No</th>
                        <th>NIS / NISN</th>
                        <th>Nama Siswa</th>
                        <th>Rombel Kelas</th>
                        <th>Program Keahlian</th>
                        <th style='text-align:center;'>Tugas Dikumpul</th>
                        <th style='text-align:center;'>Rata-Rata E-Rapor</th>
                        <th style='text-align:center;'>Status</th>
                    </tr>
                </thead><tbody>";
            foreach ($data as $i => $row) {
                $num = $i + 1;
                $nis = htmlspecialchars($row['nis'] ?? '-') . " / " . htmlspecialchars($row['nisn'] ?? '-');
                $nama = htmlspecialchars($row['nama_lengkap']);
                $kelas = htmlspecialchars($row['nama_kelas'] ?? 'Belum Ada Kelas');
                $jurusan = htmlspecialchars($row['nama_jurusan'] ?? 'Umum');
                $avgVal = (float)($row['avg_rapor'] ?? 0);
                $totTugas = (int)($row['total_tugas_dikumpul'] ?? 0);
                $avgText = ($avgVal > 0) ? number_format($avgVal, 1) : 'Belum Dinilai';

                if ($avgVal == 0 && $totTugas == 0) {
                    $status = 'BELUM ADA DATA';
                } elseif ($avgVal >= 75) {
                    $status = 'TUNTAS';
                } else {
                    $status = 'BELUM TUNTAS';
                }

                $table .= "<tr>
                    <td>{$num}</td>
                    <td><code>{$nis}</code></td>
                    <td><b>{$nama}</b></td>
                    <td>{$kelas}</td>
                    <td>{$jurusan}</td>
                    <td style='text-align:center;'>{$totTugas} Berkas</td>
                    <td style='text-align:center;'><b>{$avgText}</b></td>
                    <td style='text-align:center;'>{$status}</td>
                </tr>";
            }
            $table .= "</tbody></table>";
        }

        echo PdfHelper::renderReportPage($title, "SMK Muthia Harapan Cicalengka", $table);
        exit();
    }

    /**
     * 20. Profil Eksekutif Kepala Sekolah
     */
    public function profil() {
        $userSession = AuthHelper::user();
        $userId = $userSession['id'];
        $db = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=kepsek/profil');
                exit();
            }

            $fullName = Security::sanitize($_POST['full_name']);
            $email = Security::sanitize($_POST['email']);
            $password = $_POST['password'] ?? '';

            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$fullName, $email, $hash, $userId]);
            } else {
                $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                $stmt->execute([$fullName, $email, $userId]);
            }

            // Handle optional avatar photo upload
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['avatar'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($ext, $allowed)) {
                    $uploadDir = ROOT_PATH . 'assets/uploads/avatar/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $fileName = 'avatar_kepsek_' . $userId . '_' . time() . '.' . $ext;
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $stmtAv = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                        $stmtAv->execute([$fileName, $userId]);
                        $_SESSION['avatar'] = $fileName;
                    }
                }
            }

            // Also update guru profile if exists
            try {
                $stmtG = $db->prepare("UPDATE guru SET nama_lengkap = ?, email = ? WHERE user_id = ?");
                $stmtG->execute([$fullName, $email, $userId]);
            } catch (Exception $e) {}

            // Update session values
            $_SESSION['full_name'] = $fullName;
            $_SESSION['email'] = $email;

            FlashHelper::setSuccess('Profil Eksekutif Kepala Sekolah berhasil diperbarui.');

            header('Location: ' . BASE_URL . 'index.php?url=kepsek/profil');
            exit();
        }

        $stmtU = $db->prepare("SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
        $stmtU->execute([$userId]);
        $user = $stmtU->fetch();

        require_once ROOT_PATH . 'views/kepsek/profil.php';
    }

    /**
     * 21. Panduan Pengguna Kepsek
     */
    public function panduan() {
        require_once ROOT_PATH . 'views/kepsek/panduan.php';
    }
}
