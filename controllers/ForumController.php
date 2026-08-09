<?php
/**
 * Forum Controller
 */
require_once ROOT_PATH . 'helpers/AuthHelper.php';
require_once ROOT_PATH . 'helpers/Security.php';
require_once ROOT_PATH . 'helpers/UploadHelper.php';
require_once ROOT_PATH . 'helpers/FlashHelper.php';
require_once ROOT_PATH . 'models/CommunicationModel.php';
require_once ROOT_PATH . 'models/AcademicModel.php';

class ForumController {

    public function index() {
        AuthHelper::requireLogin();
        $user = AuthHelper::user();
        $commModel = new CommunicationModel();
        $academicModel = new AcademicModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=forum');
                exit();
            }

            $judul = Security::sanitize($_POST['judul']);
            $konten = Security::sanitize($_POST['konten']);
            $mapelId = (int)($_POST['mapel_id'] ?? 0);
            $gambar = null;

            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $gambar = UploadHelper::upload($_FILES['gambar'], 'tugas'); // use image uploader
            }

            $commModel->createForumTopic($user['id'], $mapelId, $judul, $konten, $gambar);
            FlashHelper::setSuccess('Topik diskusi baru berhasil dibuat.');

            header('Location: ' . BASE_URL . 'index.php?url=forum');
            exit();
        }

        $topics = $commModel->getForumTopics();
        $mapelList = $academicModel->getMapel();

        require_once ROOT_PATH . 'views/forum/index.php';
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . "index.php?url=forum/detail&id={$id}");
                exit();
            }

            $komentar = Security::sanitize($_POST['komentar']);
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
