<?php
/**
 * Kepala Sekolah Controller
 */
require_once ROOT_PATH . 'helpers/AuthHelper.php';
require_once ROOT_PATH . 'helpers/PdfHelper.php';
require_once ROOT_PATH . 'models/ReportModel.php';
require_once ROOT_PATH . 'models/GuruModel.php';
require_once ROOT_PATH . 'models/SiswaModel.php';

class KepsekController {

    public function __construct() {
        AuthHelper::requireRole(['Kepala Sekolah', 'Administrator']);
    }

    public function dashboard() {
        $reportModel = new ReportModel();
        $guruModel = new GuruModel();
        $siswaModel = new SiswaModel();

        $stats = $reportModel->getKepsekStats();
        $guruList = $guruModel->getAll();
        $siswaList = $siswaModel->getAll();

        require_once ROOT_PATH . 'views/kepsek/dashboard.php';
    }

    public function monitoringGuru() {
        $db = Database::getConnection();
        $guruList = $db->query("
            SELECT g.*, u.username, u.email,
                   (SELECT COUNT(*) FROM materi m WHERE m.guru_id = g.id) as total_materi,
                   (SELECT COUNT(*) FROM tugas t WHERE t.guru_id = g.id) as total_tugas,
                   (SELECT COUNT(*) FROM quiz q WHERE q.guru_id = g.id) as total_quiz,
                   (SELECT GROUP_CONCAT(DISTINCT k.nama_kelas SEPARATOR ', ') 
                    FROM jadwal j JOIN kelas k ON j.kelas_id = k.id WHERE j.guru_id = g.id) as kelas_ajar
            FROM guru g
            JOIN users u ON g.user_id = u.id
            ORDER BY g.nama_lengkap ASC
        ")->fetchAll();

        require_once ROOT_PATH . 'views/kepsek/monitoring_guru.php';
    }

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

    public function cetakLaporan() {
        $type = $_GET['type'] ?? 'guru';
        $db = Database::getConnection();
        $title = ($type === 'guru') ? "Laporan Resmi Monitoring Tenaga Pengajar / Guru" : "Laporan Resmi Monitoring Siswa & Progress Belajar";

        if ($type === 'guru') {
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
        } else {
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

        // Fetch fresh user data directly from DB
        $stmtU = $db->prepare("SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
        $stmtU->execute([$userId]);
        $user = $stmtU->fetch();

        require_once ROOT_PATH . 'views/kepsek/profil.php';
    }

    public function panduan() {
        require_once ROOT_PATH . 'views/kepsek/panduan.php';
    }
}
