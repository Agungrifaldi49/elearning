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
            
            if (isset($_POST['is_ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Topik diskusi baru berhasil dibuat.']);
                exit();
            }

            FlashHelper::setSuccess('Topik diskusi baru berhasil dibuat.');
            header('Location: ' . BASE_URL . 'index.php?url=forum');
            exit();
        }

        $topics = $commModel->getForumTopics($user['id'], $roleName, $userKelasId);
        $mapelList = $academicModel->getMapel();
        $classList = $db->query("SELECT id, nama_kelas FROM kelas ORDER BY tingkat ASC, nama_kelas ASC")->fetchAll();

        require_once ROOT_PATH . 'views/forum/index.php';
    }

    public function fetchUpdates() {
        AuthHelper::requireLogin();
        header('Content-Type: application/json');

        $user = AuthHelper::user();
        $commModel = new CommunicationModel();

        $roleName = $user['role_name'] ?? '';
        $userKelasId = ($roleName === 'Siswa') ? $this->getUserKelasId($user['id']) : null;

        $topics = $commModel->getForumTopics($user['id'], $roleName, $userKelasId);
        $formattedTopics = [];

        foreach ($topics as $t) {
            $isAuthor = ((int)($t['user_id'] ?? 0) === (int)($user['id'] ?? 0));
            $isAdmin = (strtolower($roleName) === 'administrator');
            $canDelete = ($isAuthor || $isAdmin);
            $reactions = $commModel->getForumReactionSummary($t['id'], $user['id']);

            $formattedTopics[] = [
                'id' => (int)$t['id'],
                'user_id' => (int)$t['user_id'],
                'full_name' => htmlspecialchars($t['full_name']),
                'role_name' => htmlspecialchars($t['role_name']),
                'created_at' => date('d F Y, H:i', strtotime($t['created_at'])),
                'posted_time' => date('H:i', strtotime($t['created_at'])),
                'nama_mapel' => $t['nama_mapel'] ? htmlspecialchars($t['nama_mapel']) : '',
                'visibility' => $t['visibility'] ?? 'public',
                'target_role' => $t['target_role'] ? htmlspecialchars($t['target_role']) : '',
                'target_nama_kelas' => $t['target_nama_kelas'] ? htmlspecialchars($t['target_nama_kelas']) : '',
                'judul' => htmlspecialchars($t['judul']),
                'konten_preview' => htmlspecialchars(substr($t['konten'], 0, 220)),
                'total_replies' => (int)$t['total_replies'],
                'reactions' => $reactions,
                'can_delete' => $canDelete,
                'is_me' => $isAuthor
            ];
        }

        echo json_encode(['status' => 'success', 'topics' => $formattedTopics]);
        exit();
    }

    public function react() {
        AuthHelper::requireLogin();
        header('Content-Type: application/json');

        $user = AuthHelper::user();
        $commModel = new CommunicationModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                echo json_encode(['status' => 'error', 'message' => 'CSRF Token Invalid']);
                exit();
            }

            $forumId = (int)($_POST['forum_id'] ?? 0);
            $type = Security::sanitize($_POST['type'] ?? '');

            if ($forumId > 0 && !empty($type)) {
                $commModel->toggleForumReaction($forumId, $user['id'], $type);
                $summary = $commModel->getForumReactionSummary($forumId, $user['id']);
                echo json_encode(['status' => 'success', 'summary' => $summary, 'forum_id' => $forumId]);
                exit();
            }
        }

        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        exit();
    }

    public function fetchComments() {
        AuthHelper::requireLogin();
        header('Content-Type: application/json');

        $user = AuthHelper::user();
        $commModel = new CommunicationModel();

        $id = (int)($_GET['id'] ?? 0);
        $topic = $commModel->getForumDetail($id);

        if (!$topic) {
            echo json_encode(['status' => 'error', 'message' => 'Topik tidak ditemukan']);
            exit();
        }

        $comments = $commModel->getKomentar($id);
        $formattedComments = [];

        foreach ($comments as $c) {
            $formattedComments[] = [
                'id' => (int)$c['id'],
                'full_name' => htmlspecialchars($c['full_name']),
                'role_name' => htmlspecialchars($c['role_name']),
                'created_at' => date('d/m/Y H:i', strtotime($c['created_at'])),
                'komentar' => nl2br(htmlspecialchars($c['komentar']))
            ];
        }

        echo json_encode(['status' => 'success', 'comments' => $formattedComments]);
        exit();
    }

    public function delete() {
        AuthHelper::requireLogin();
        $user = AuthHelper::user();
        $commModel = new CommunicationModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                if (isset($_POST['is_ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'message' => 'CSRF Token Invalid']);
                    exit();
                }
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=forum');
                exit();
            }

            $topicId = (int)($_POST['topic_id'] ?? 0);
            $res = $commModel->deleteForumTopic($topicId, $user['id'], $user['role_name'] ?? '');
            
            if (isset($_POST['is_ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                header('Content-Type: application/json');
                if ($res) {
                    echo json_encode(['status' => 'success', 'message' => 'Topik diskusi berhasil dihapus.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus topik. Anda tidak memiliki hak akses.']);
                }
                exit();
            }

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
                if (isset($_POST['is_ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'message' => 'CSRF Token Invalid']);
                    exit();
                }
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . "index.php?url=forum/detail&id={$id}");
                exit();
            }

            $rawKomentar = Security::sanitize($_POST['komentar']);
            $komentar = ProfanityFilterHelper::filter($rawKomentar);
            $parentId = (int)($_POST['parent_id'] ?? 0);

            $commModel->addKomentar($id, $user['id'], $komentar, $parentId);

            if (isset($_POST['is_ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Komentar berhasil ditambahkan.']);
                exit();
            }

            FlashHelper::setSuccess('Komentar berhasil ditambahkan.');
            header('Location: ' . BASE_URL . "index.php?url=forum/detail&id={$id}");
            exit();
        }

        $comments = $commModel->getKomentar($id);
        $reactions = $commModel->getForumReactionSummary($id, $user['id']);
        require_once ROOT_PATH . 'views/forum/detail.php';
    }
}
