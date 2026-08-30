<?php
/**
 * LibraryModel.php
 * Model untuk Perpustakaan Digital (koleksi e-book, modul, referensi)
 */
class LibraryModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS library (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    judul VARCHAR(255) NOT NULL,
                    penulis VARCHAR(100) NULL,
                    deskripsi TEXT NULL,
                    kategori VARCHAR(50) DEFAULT 'Umum',
                    kelas_target VARCHAR(100) NULL,
                    file_type VARCHAR(20) NOT NULL DEFAULT 'pdf',
                    file_path VARCHAR(255) NOT NULL,
                    cover_path VARCHAR(255) NULL,
                    file_size BIGINT DEFAULT 0,
                    uploader_id INT NOT NULL,
                    view_count INT DEFAULT 0,
                    download_count INT DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            try {
                $this->db->exec("ALTER TABLE library ADD COLUMN cover_path VARCHAR(255) NULL");
            } catch (Exception $e) {}
        } catch (Exception $e) {
            // Ignore if already exists or DDL is restricted
        }
    }

    /**
     * Ambil semua koleksi dengan paginasi
     */
    public function getAll(int $limit = 100, int $offset = 0): array {
        $limit = (int)$limit;
        $offset = (int)$offset;
        $stmt = $this->db->query("
            SELECT lib.*, u.full_name as uploader_name
            FROM library lib
            LEFT JOIN users u ON lib.uploader_id = u.id
            ORDER BY lib.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ");
        return $stmt->fetchAll();
    }

    /**
     * Cari koleksi berdasarkan keyword dan filter
     */
    public function search(string $q, string $kategori = '', string $tipe = ''): array {
        $sql = "SELECT lib.*, u.full_name as uploader_name FROM library lib LEFT JOIN users u ON lib.uploader_id = u.id WHERE 1=1";
        $params = [];
        if ($q) { $sql .= " AND (lib.judul LIKE ? OR lib.deskripsi LIKE ? OR lib.penulis LIKE ?)"; $params = array_merge($params, ["%$q%","%$q%","%$q%"]); }
        if ($kategori) { $sql .= " AND lib.kategori = ?"; $params[] = $kategori; }
        if ($tipe) { $sql .= " AND lib.file_type = ?"; $params[] = $tipe; }
        $sql .= " ORDER BY lib.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ambil 1 buku berdasarkan ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT lib.*, u.full_name as uploader_name FROM library lib LEFT JOIN users u ON lib.uploader_id = u.id WHERE lib.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Simpan koleksi baru ke library
     */
    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO library (judul, penulis, deskripsi, kategori, kelas_target, file_type, file_path, cover_path, file_size, uploader_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['judul'], $data['penulis'] ?? '', $data['deskripsi'] ?? '',
            $data['kategori'] ?? 'Umum', $data['kelas_target'] ?? null,
            $data['file_type'], $data['file_path'], $data['cover_path'] ?? null,
            $data['file_size'] ?? 0, $data['uploader_id']
        ]);
    }

    /**
     * Hapus koleksi
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM library WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Tambahkan view count
     */
    public function incrementView(int $id): void {
        $this->db->prepare("UPDATE library SET view_count = view_count + 1 WHERE id = ?")->execute([$id]);
    }

    /**
     * Tambahkan download count
     */
    public function incrementDownload(int $id): void {
        $this->db->prepare("UPDATE library SET download_count = download_count + 1 WHERE id = ?")->execute([$id]);
    }

    /**
     * Total koleksi
     */
    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM library")->fetchColumn();
    }

    /**
     * Koleksi terpopuler (by views)
     */
    public function getPopular(int $limit = 6): array {
        $stmt = $this->db->prepare("SELECT * FROM library ORDER BY view_count DESC, download_count DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
