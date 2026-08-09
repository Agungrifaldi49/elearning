<?php
/**
 * Upload Helper
 * Handles secure file uploads with validation & unique file naming
 */

class UploadHelper {

    private static $allowed_extensions = [
        'materi' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'mp4'],
        'video' => ['mp4', 'webm', 'mkv', 'avi'],
        'tugas' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar', 'jpg', 'jpeg', 'png'],
        'profile' => ['jpg', 'jpeg', 'png', 'webp'],
        'sertifikat' => ['pdf', 'jpg', 'png'],
        'logo' => ['jpg', 'jpeg', 'png', 'webp', 'svg', 'ico'],
        'soal' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
    ];

    private static $max_sizes = [
        'materi' => 50 * 1024 * 1024,   // 50 MB
        'video' => 100 * 1024 * 1024,  // 100 MB
        'tugas' => 25 * 1024 * 1024,   // 25 MB
        'profile' => 5 * 1024 * 1024,   // 5 MB
        'sertifikat' => 10 * 1024 * 1024, // 10 MB
        'logo' => 5 * 1024 * 1024,       // 5 MB
        'soal' => 10 * 1024 * 1024      // 10 MB
    ];

    /**
     * Upload File Safely
     * 
     * @param array $file $_FILES['input_name']
     * @param string $category 'materi' | 'video' | 'tugas' | 'profile' | 'sertifikat' | 'soal'
     * @return string|false Saved filename on success, false on failure
     */
    public static function upload($file, $category = 'materi') {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $targetDir = UPLOADS_PATH . $category . '/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = $file['name'];
        $fileSize = $file['size'];
        $tmpName = $file['tmp_name'];

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate Extension
        $allowed = self::$allowed_extensions[$category] ?? ['pdf', 'jpg', 'png'];
        if (!in_array($fileExt, $allowed)) {
            $_SESSION['flash_error'] = "Format file tidak diperbolehkan. Ekstensi yang diizinkan: " . implode(', ', $allowed);
            return false;
        }

        // Validate Size
        $maxSize = self::$max_sizes[$category] ?? 10 * 1024 * 1024;
        if ($fileSize > $maxSize) {
            $_SESSION['flash_error'] = "Ukuran file terlalu besar! Maksimal " . round($maxSize / (1024 * 1024)) . " MB.";
            return false;
        }

        // Generate Unique File Name
        $newFileName = $category . '_' . time() . '_' . uniqid() . '.' . $fileExt;
        $destination = $targetDir . $newFileName;

        if (move_uploaded_file($tmpName, $destination)) {
            return $newFileName;
        }

        $_SESSION['flash_error'] = "Gagal mengunggah file ke server.";
        return false;
    }

    /**
     * Upload File element from $_FILES array structure
     */
    public static function uploadArrayElement($filesArray, $index, $category = 'soal') {
        if (!isset($filesArray['name'][$index]) || $filesArray['error'][$index] !== UPLOAD_ERR_OK) {
            return false;
        }

        $singleFile = [
            'name' => $filesArray['name'][$index],
            'type' => $filesArray['type'][$index],
            'tmp_name' => $filesArray['tmp_name'][$index],
            'error' => $filesArray['error'][$index],
            'size' => $filesArray['size'][$index],
        ];

        return self::upload($singleFile, $category);
    }
}
