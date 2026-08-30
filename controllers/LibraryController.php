<?php
/**
 * LibraryController.php
 * Controller untuk Perpustakaan Digital
 */
require_once ROOT_PATH . 'models/LibraryModel.php';

class LibraryController {
    private $libraryModel;

    public function __construct() {
        AuthHelper::requireLogin();
        $this->libraryModel = new LibraryModel();
    }

    /**
     * Halaman utama perpustakaan
     */
    public function index(): void {
        $books = $this->libraryModel->getAll();
        require_once ROOT_PATH . 'views/library/index.php';
    }

    /**
     * Halaman upload buku/modul (admin & guru only)
     */
    public function upload(): void {
        AuthHelper::requireRole(['administrator', 'Guru']);
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('CSRF Token Invalid');
                header('Location: ' . BASE_URL . 'index.php?url=library/upload');
                exit();
            }

            $judul       = Security::sanitize($_POST['judul'] ?? '');
            $penulis     = Security::sanitize($_POST['penulis'] ?? '');
            $deskripsi   = Security::sanitize($_POST['deskripsi'] ?? '');
            $kategori    = Security::sanitize($_POST['kategori'] ?? 'Umum');
            $kelasTarget = Security::sanitize($_POST['kelas_target'] ?? '');

            if (empty($judul)) {
                $error = 'Judul tidak boleh kosong.';
            } elseif (empty($_FILES['file']['name'])) {
                $error = 'File koleksi wajib diupload.';
            } else {
                $file         = $_FILES['file'];
                $allowedTypes = ['pdf','docx','doc','pptx','ppt','xlsx','mp4','mkv'];
                $ext          = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $maxSize      = 50 * 1024 * 1024; // 50MB

                if (!in_array($ext, $allowedTypes)) {
                    $error = 'Format file tidak didukung. Gunakan: ' . implode(', ', $allowedTypes);
                } elseif ($file['size'] > $maxSize) {
                    $error = 'Ukuran file maksimum 50MB.';
                } else {
                    $uploadDir = ROOT_PATH . 'assets/uploads/library/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                    $fileName = uniqid('lib_', true) . '.' . $ext;
                    $destPath = $uploadDir . $fileName;

                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        $fileType = in_array($ext, ['mp4','mkv','avi']) ? 'video' : $ext;
                        
                        $coverPath = null;
                        if (!empty($_FILES['cover']['name']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
                            $cFile = $_FILES['cover'];
                            $cExt  = strtolower(pathinfo($cFile['name'], PATHINFO_EXTENSION));
                            if (in_array($cExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                                $coverDir = ROOT_PATH . 'assets/uploads/library/covers/';
                                if (!is_dir($coverDir)) mkdir($coverDir, 0755, true);
                                $cFileName = uniqid('cover_', true) . '.' . $cExt;
                                if (move_uploaded_file($cFile['tmp_name'], $coverDir . $cFileName)) {
                                    $coverPath = 'assets/uploads/library/covers/' . $cFileName;
                                }
                            }
                        }

                        $this->libraryModel->create([
                            'judul'        => $judul,
                            'penulis'      => $penulis,
                            'deskripsi'    => $deskripsi,
                            'kategori'     => $kategori,
                            'kelas_target' => $kelasTarget ?: null,
                            'file_type'    => $fileType,
                            'file_path'    => 'assets/uploads/library/' . $fileName,
                            'cover_path'   => $coverPath,
                            'file_size'    => $file['size'],
                            'uploader_id'  => AuthHelper::user()['id'],
                        ]);
                        FlashHelper::setSuccess('Koleksi e-book/modul berhasil diunggah ke perpustakaan!');
                        header('Location: ' . BASE_URL . 'index.php?url=library');
                        exit();
                    } else {
                        $error = 'Gagal menyimpan file. Periksa izin direktori.';
                    }
                }
            }
        }

        require_once ROOT_PATH . 'views/library/upload.php';
    }

    /**
     * Buka / view dokumen (increment view count)
     */
    public function view(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $book = $this->libraryModel->getById($id);

        if (!$book) {
            FlashHelper::setError('Koleksi buku/modul tidak ditemukan.');
            header('Location: ' . BASE_URL . 'index.php?url=library');
            exit();
        }

        $this->libraryModel->incrementView($id);

        $filePath = ROOT_PATH . $book['file_path'];
        $fileUrl  = BASE_URL . $book['file_path'];

        require_once ROOT_PATH . 'views/library/viewer.php';
    }

    /**
     * Download file (increment download count)
     */
    public function download(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $book = $this->libraryModel->getById($id);

        if (!$book) {
            http_response_code(404);
            die('File tidak ditemukan.');
        }

        $this->libraryModel->incrementDownload($id);

        $filePath = ROOT_PATH . $book['file_path'];
        if (!file_exists($filePath)) {
            die('File tidak tersedia di server.');
        }

        $fileName = basename($filePath);
        $mimeType = mime_content_type($filePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit();
    }

    /**
     * Hapus koleksi (admin only)
     */
    public function delete(): void {
        AuthHelper::requireRole(['administrator']);
        Security::verifyCsrfToken();

        $id   = (int)($_GET['id'] ?? 0);
        $book = $this->libraryModel->getById($id);

        if ($book) {
            $filePath = ROOT_PATH . $book['file_path'];
            if (file_exists($filePath)) unlink($filePath);
            $this->libraryModel->delete($id);
            FlashHelper::setSuccess('Koleksi perpustakaan berhasil dihapus.');
        }

        header('Location: ' . BASE_URL . 'index.php?url=library');
        exit();
    }
}
