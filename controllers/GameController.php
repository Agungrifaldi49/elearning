<?php
/**
 * GameController.php
 * Controller Modul Game Edukasi Interaktif (Admin, Guru, Siswa)
 */
require_once ROOT_PATH . 'helpers/AuthHelper.php';
require_once ROOT_PATH . 'helpers/Security.php';
require_once ROOT_PATH . 'helpers/FlashHelper.php';
require_once ROOT_PATH . 'models/GameModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';
require_once ROOT_PATH . 'models/GuruModel.php';
require_once ROOT_PATH . 'models/SiswaModel.php';

class GameController {

    private function getGuruId($userId) {
        $guruModel = new GuruModel();
        $guru = $guruModel->getByUserId($userId);
        return $guru ? $guru['id'] : null;
    }

    private function getSiswaInfo($userId) {
        $siswaModel = new SiswaModel();
        $user = AuthHelper::user();
        return $siswaModel->ensureSiswaProfile($userId, $user['full_name'] ?? 'Siswa');
    }

    public function index() {
        AuthHelper::requireLogin();
        $user = AuthHelper::user();
        $roleName = strtolower(trim($user['role_name'] ?? ''));
        $gameModel = new GameModel();

        $guruId = null;
        $kelasId = null;
        $siswaInfo = null;

        if ($roleName === 'guru') {
            $guruId = $this->getGuruId($user['id']);
        } elseif ($roleName === 'siswa') {
            $siswaInfo = $this->getSiswaInfo($user['id']);
            $kelasId = $siswaInfo ? $siswaInfo['kelas_id'] : null;
        }

        $games = $gameModel->getAllGames($guruId, $kelasId);

        // Attach student's best score status if Siswa
        if ($roleName === 'siswa' && $siswaInfo) {
            foreach ($games as &$g) {
                $bestScore = $gameModel->getStudentBestScore($g['id'], $siswaInfo['id']);
                $g['my_best_score'] = $bestScore ? $bestScore['skor_akhir'] : null;
                $g['my_status'] = $bestScore ? $bestScore['status_lulus'] : null;
            }
            unset($g);
        }

        require_once ROOT_PATH . 'views/game/index.php';
    }

    public function create() {
        AuthHelper::requireLogin();
        $user = AuthHelper::user();
        $roleName = strtolower(trim($user['role_name'] ?? ''));

        if ($roleName !== 'guru') {
            FlashHelper::setError('Pembuatan Game Edukasi hanya dapat dilakukan oleh Guru Pengampu. Administrator hanya memiliki hak akses memantau (read-only).');
            header('Location: ' . BASE_URL . 'index.php?url=game');
            exit();
        }

        $academicModel = new AcademicModel();
        $gameModel = new GameModel();

        $mapelList = $academicModel->getMapel();
        $classList = $academicModel->getKelas();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=game/create');
                exit();
            }

            $guruId = $this->getGuruId($user['id']);

            $gameData = [
                'guru_id' => $guruId,
                'mapel_id' => (int)$_POST['mapel_id'],
                'kelas_id' => (int)($_POST['kelas_id'] ?? 0),
                'judul' => Security::sanitize($_POST['judul']),
                'deskripsi' => Security::sanitize($_POST['deskripsi'] ?? ''),
                'tipe_game' => Security::sanitize($_POST['tipe_game'] ?? 'quiz_speed'),
                'durasi_per_soal' => (int)($_POST['durasi_per_soal'] ?? 15),
                'kkm' => (int)($_POST['kkm'] ?? 75)
            ];

            $soalRaw = $_POST['soal'] ?? [];
            $soalList = [];

            foreach ($soalRaw as $s) {
                if (empty($s['pertanyaan']) || empty($s['opsi_a']) || empty($s['opsi_b'])) continue;
                $soalList[] = [
                    'pertanyaan' => Security::sanitize($s['pertanyaan']),
                    'opsi_a' => Security::sanitize($s['opsi_a']),
                    'opsi_b' => Security::sanitize($s['opsi_b']),
                    'opsi_c' => Security::sanitize($s['opsi_c'] ?? ''),
                    'opsi_d' => Security::sanitize($s['opsi_d'] ?? ''),
                    'kunci_jawaban' => Security::sanitize($s['kunci_jawaban'] ?? 'a'),
                    'poin' => (int)($s['poin'] ?? 10),
                    'penjelasan' => Security::sanitize($s['penjelasan'] ?? '')
                ];
            }

            if (empty($soalList)) {
                FlashHelper::setError('Game Edukasi harus memiliki minimal 1 soal pertanyaan.');
                header('Location: ' . BASE_URL . 'index.php?url=game/create');
                exit();
            }

            $res = $gameModel->createGame($gameData, $soalList);
            if ($res) {
                FlashHelper::setSuccess('Game Edukasi baru berhasil dibuat!');
                header('Location: ' . BASE_URL . 'index.php?url=game');
                exit();
            } else {
                FlashHelper::setError('Gagal membuat Game Edukasi. Silakan periksa kembali inputan Anda.');
            }
        }

        require_once ROOT_PATH . 'views/game/create.php';
    }

    public function edit() {
        AuthHelper::requireLogin();
        $user = AuthHelper::user();
        $roleId = (int)($user['role_id'] ?? 0);
        $roleName = strtolower(trim($user['role_name'] ?? ''));

        if ($roleId !== 2 && strpos($roleName, 'guru') === false) {
            FlashHelper::setError('Pengeditan Game Edukasi hanya dapat dilakukan oleh Guru Pengampu.');
            header('Location: ' . BASE_URL . 'index.php?url=game');
            exit();
        }

        $id = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
        $gameModel = new GameModel();
        $game = $gameModel->getGameDetail($id);

        if (!$game) {
            FlashHelper::setError('Game Edukasi tidak ditemukan.');
            header('Location: ' . BASE_URL . 'index.php?url=game');
            exit();
        }

        $guruId = $this->getGuruId($user['id']);
        if ((int)$game['guru_id'] !== (int)$guruId && $roleId !== 1) {
            FlashHelper::setError('Anda hanya dapat mengedit Game Edukasi buatan Anda sendiri.');
            header('Location: ' . BASE_URL . 'index.php?url=game');
            exit();
        }

        $academicModel = new AcademicModel();
        $mapelList = $academicModel->getMapel();
        $classList = $academicModel->getKelas();
        $soalList = $gameModel->getGameSoal($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=game/edit&id=' . $id);
                exit();
            }

            $gameData = [
                'mapel_id' => (int)$_POST['mapel_id'],
                'kelas_id' => (int)($_POST['kelas_id'] ?? 0),
                'judul' => Security::sanitize($_POST['judul']),
                'deskripsi' => Security::sanitize($_POST['deskripsi'] ?? ''),
                'durasi_per_soal' => (int)($_POST['durasi_per_soal'] ?? 15),
                'kkm' => (int)($_POST['kkm'] ?? 75)
            ];

            $soalRaw = $_POST['soal'] ?? [];
            $newSoalList = [];

            foreach ($soalRaw as $s) {
                if (empty($s['pertanyaan']) || empty($s['opsi_a']) || empty($s['opsi_b'])) continue;
                $newSoalList[] = [
                    'pertanyaan' => Security::sanitize($s['pertanyaan']),
                    'opsi_a' => Security::sanitize($s['opsi_a']),
                    'opsi_b' => Security::sanitize($s['opsi_b']),
                    'opsi_c' => Security::sanitize($s['opsi_c'] ?? ''),
                    'opsi_d' => Security::sanitize($s['opsi_d'] ?? ''),
                    'kunci_jawaban' => Security::sanitize($s['kunci_jawaban'] ?? 'a'),
                    'poin' => (int)($s['poin'] ?? 10),
                    'penjelasan' => Security::sanitize($s['penjelasan'] ?? '')
                ];
            }

            if (empty($newSoalList)) {
                FlashHelper::setError('Game Edukasi harus memiliki minimal 1 soal pertanyaan.');
                header('Location: ' . BASE_URL . 'index.php?url=game/edit&id=' . $id);
                exit();
            }

            $res = $gameModel->updateGame($id, $gameData, $newSoalList);
            if ($res) {
                FlashHelper::setSuccess('Game Edukasi berhasil diperbarui!');
                header('Location: ' . BASE_URL . 'index.php?url=game');
                exit();
            } else {
                FlashHelper::setError('Gagal memperbarui Game Edukasi.');
            }
        }

        require_once ROOT_PATH . 'views/game/edit.php';
    }

    public function play() {
        AuthHelper::requireLogin();
        $user = AuthHelper::user();
        $roleName = strtolower(trim($user['role_name'] ?? ''));
        $id = (int)($_GET['id'] ?? 0);

        if ($roleName === 'administrator' || $roleName === 'admin') {
            FlashHelper::setInfo('Administrator memiliki akses read-only (memantau Papan Peringkat & Statistik Hasil Game). Siswa yang mengerjakan sesuai ketentuan Guru.');
            header('Location: ' . BASE_URL . 'index.php?url=game/leaderboard&id=' . $id);
            exit();
        }

        $gameModel = new GameModel();

        $game = $gameModel->getGameDetail($id);
        if (!$game) {
            FlashHelper::setError('Game Edukasi tidak ditemukan.');
            header('Location: ' . BASE_URL . 'index.php?url=game');
            exit();
        }

        $soalList = $gameModel->getGameSoal($id);
        if (empty($soalList)) {
            FlashHelper::setError('Game Edukasi ini belum memiliki bank soal.');
            header('Location: ' . BASE_URL . 'index.php?url=game');
            exit();
        }

        $siswaInfo = ($roleName === 'siswa') ? $this->getSiswaInfo($user['id']) : null;

        require_once ROOT_PATH . 'views/game/play.php';
    }

    public function saveScore() {
        AuthHelper::requireLogin();
        header('Content-Type: application/json');

        $user = AuthHelper::user();
        $roleName = strtolower($user['role_name'] ?? '');
        $gameModel = new GameModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                echo json_encode(['status' => 'error', 'message' => 'CSRF Token Invalid']);
                exit();
            }

            $siswaInfo = $this->getSiswaInfo($user['id']);
            $siswaId = $siswaInfo ? $siswaInfo['id'] : null;

            if (!$siswaId && $roleName !== 'siswa') {
                // Allow preview attempt saving for demo/teacher
                $db = Database::getConnection();
                $sRow = $db->query("SELECT id FROM siswa LIMIT 1")->fetch();
                $siswaId = $sRow ? $sRow['id'] : 1;
            }

            $gameId = (int)($_POST['game_id'] ?? 0);
            $skorAkhir = (int)($_POST['skor_akhir'] ?? 0);
            $maxCombo = (int)($_POST['max_combo'] ?? 0);
            $totalBenar = (int)($_POST['total_benar'] ?? 0);
            $totalSoal = (int)($_POST['total_soal'] ?? 0);
            $waktuSelesai = (int)($_POST['waktu_selesai'] ?? 0);
            $statusLulus = Security::sanitize($_POST['status_lulus'] ?? 'tidak_lulus');

            if ($gameId > 0 && $siswaId > 0) {
                $res = $gameModel->saveScore($gameId, $siswaId, $skorAkhir, $maxCombo, $totalBenar, $totalSoal, $waktuSelesai, $statusLulus);
                if ($res) {
                    echo json_encode(['status' => 'success', 'message' => 'Skor permainan berhasil disimpan ke Papan Peringkat!']);
                    exit();
                }
            }
        }

        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan skor permainan']);
        exit();
    }

    public function leaderboard() {
        AuthHelper::requireLogin();
        $user = AuthHelper::user();
        $id = (int)($_GET['id'] ?? 0);

        $gameModel = new GameModel();
        $game = $gameModel->getGameDetail($id);

        if (!$game) {
            FlashHelper::setError('Game Edukasi tidak ditemukan.');
            header('Location: ' . BASE_URL . 'index.php?url=game');
            exit();
        }

        $leaderboard = $gameModel->getLeaderboard($id);
        require_once ROOT_PATH . 'views/game/leaderboard.php';
    }

    public function delete() {
        AuthHelper::requireLogin();
        $user = AuthHelper::user();
        $roleName = strtolower(trim($user['role_name'] ?? ''));

        if ($roleName !== 'guru') {
            FlashHelper::setError('Penghapusan Game Edukasi hanya dapat dilakukan oleh Guru Pengampu. Administrator hanya memiliki akses monitoring / read-only.');
            header('Location: ' . BASE_URL . 'index.php?url=game');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=game');
                exit();
            }

            $id = (int)($_POST['id'] ?? 0);
            $guruId = $this->getGuruId($user['id']);

            $gameModel = new GameModel();
            $res = $gameModel->deleteGame($id, $guruId, false);

            if ($res) {
                FlashHelper::setSuccess('Game Edukasi berhasil dihapus.');
            } else {
                FlashHelper::setError('Gagal menghapus Game Edukasi. Anda hanya dapat menghapus game buatan Anda sendiri.');
            }
        }

        header('Location: ' . BASE_URL . 'index.php?url=game');
        exit();
    }
}
