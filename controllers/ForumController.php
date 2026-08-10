<?php
/**
 * Forum Controller
 */
require_once ROOT_PATH . 'helpers/AuthHelper.php';
require_once ROOT_PATH . 'helpers/Security.php';
require_once ROOT_PATH . 'helpers/UploadHelper.php';
require_once ROOT_PATH . 'helpers/FlashHelper.php';
require_once ROOT_PATH . 'helpers/ProfanityFilterHelper.php';
require_once ROOT_PATH . 'models/CommunicationModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';

class ForumController {

    private function getUserKelasId($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT kelas_id FROM siswa WHERE user_id = ?");
        $stmt->execute([(int)$userId]);
        $row = $stmt->fetch();
        return $row ? (int)$row['kelas_id'] : null;
    }

    public function index() {
        AuthHelper::requireLogin();
        $user = AuthHelper::user();
        $commModel = new CommunicationModel();
        $academicModel = new AcademicModel();
        $db = Database::getConnection();

        $roleName = $user['role_name'] ?? '';
        $userKelasId = ($roleName === 'Siswa') ? $this->getUserKelasId($user['id']) : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=forum');
                exit();
            }

            $action = $_POST['action'] ?? 'create';

            if ($action === 'delete') {
                $topicId = (int)($_POST['topic_id'] ?? 0);
                $res = $commModel->deleteForumTopic($topicId, $user['id'], $roleName);
                if ($res) {
                    FlashHelper::setSuccess('Topik diskusi berhasil dihapus.');
                } else {
                    FlashHelper::setError('Gagal menghapus topik. Anda tidak memiliki hak akses.');
                }
                header('Location: ' . BASE_URL . 'index.php?url=forum');
                exit();
            }

            $rawJudul = Security::sanitize($_POST['judul']);
            $rawKonten = Security::sanitize($_POST['konten']);
            $judul = ProfanityFilterHelper::filter($rawJudul);
            $konten = ProfanityFilterHelper::filter($rawKonten);
            $mapelId = (int)($_POST['mapel_id'] ?? 0);
            $visibility = Security::sanitize($_POST['visibility'] ?? 'public');
            $targetRole = Security::sanitize($_POST['target_role'] ?? '');
            $targetKelasId = (int)($_POST['target_kelas_id'] ?? 0);
            $gambar = null;

            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $gambar = UploadHelper::upload($_FILES['gambar'], 'tugas');
            }

            $commModel->createForumTopic($user['id'], $mapelId, $judul, $konten, $gambar, $visibility, $targetRole, $targetKelasId);
            FlashHelper::setSuccess('Topik diskusi baru berhasil dibuat.');

            header('Location: ' . BASE_URL . 'index.php?url=forum');
            exit();
        }

        $topics = $commModel->getForumTopics($user['id'], $roleName, $userKelasId);
        $mapelList = $academicModel->getMapel();
        $classList = $db->query("SELECT id, nama_kelas FROM kelas ORDER BY tingkat ASC, nama_kelas ASC")->fetchAll();

        require_once ROOT_PATH . 'views/forum/index.php';
    }

    public function delete() {
        AuthHelper::requireLogin();
        $user = AuthHelper::user();
        $commModel = new CommunicationModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=forum');
                exit();
            }

            $topicId = (int)($_POST['topic_id'] ?? 0);
            $res = $commModel->deleteForumTopic($topicId, $user['id'], $user['role_name'] ?? '');
            if ($res) {
                FlashHelper::setSuccess('Topik diskusi berhasil dihapus.');
            } else {
                FlashHelper::setError('Gagal menghapus topik. Anda tidak memiliki hak akses.');
            }
        }

        header('Location: ' . BASE_URL . 'index.php?url=forum');
        exit();
    }

    public function detail() {
        AuthHelper::requireLogin();
        $user = AuthHelper::user();
        $commModel = new CommunicationModel();

        $id = (int)($_GET['id'] ?? 0);
        $topic = $commModel->getForumDetail($id);

        if (!$topic) {
            FlashHelper::setError('Topik diskusi tidak ditemukan.');
            header('Location: ' . BASE_URL . 'index.php?url=forum');
            exit();
        }

        // Check visibility access for private topics
        $roleName = strtolower($user['role_name'] ?? '');
        if ($roleName !== 'administrator' && ($topic['visibility'] ?? 'public') === 'private') {
            $isAuthor = ((int)$topic['user_id'] === (int)$user['id']);
            $userKelasId = ($roleName === 'siswa') ? $this->getUserKelasId($user['id']) : null;
            $matchRole = (empty($topic['target_role']) || $topic['target_role'] === 'all' || strtolower($topic['target_role']) === $roleName);
            $matchKelas = (empty($topic['target_kelas_id']) || (int)$topic['target_kelas_id'] === (int)$userKelasId);

            if (!$isAuthor && (!$matchRole || !$matchKelas)) {
                FlashHelper::setError('Anda tidak memiliki hak akses untuk melihat topik diskusi privat ini.');
                header('Location: ' . BASE_URL . 'index.php?url=forum');
                exit();
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . "index.php?url=forum/detail&id={$id}");
                exit();
            }

            $rawKomentar = Security::sanitize($_POST['komentar']);
            $komentar = ProfanityFilterHelper::filter($rawKomentar);
            $parentId = (int)($_POST['parent_id'] ?? 0);

            $commModel->addKomentar($id, $user['id'], $komentar, $parentId);
            FlashHelper::setSuccess('Komentar berhasil ditambahkan.');

            header('Location: ' . BASE_URL . "index.php?url=forum/detail&id={$id}");
            exit();
        }

        $comments = $commModel->getKomentar($id);
        require_once ROOT_PATH . 'views/forum/detail.php';
    }
}
