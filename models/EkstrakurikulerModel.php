<?php
/**
 * Ekstrakurikuler Model
 * Menangani Master Data Ekstrakurikuler, Pembimbing (Guru/Luar), Pendaftaran Siswa, & Penilaian Deskripsi E-Rapor
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class EkstrakurikulerModel extends BaseModel {

    public function __construct() {
        parent::__construct();
        $this->ensureTablesExist();
    }

    /**
     * Membuat tabel ekstrakurikuler & anggotanya secara otomatis jika belum ada di database
     */
    public function ensureTablesExist() {
        try {
            $sqlEkskul = "CREATE TABLE IF NOT EXISTS ekstrakurikuler (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nama_ekskul VARCHAR(100) NOT NULL,
                tipe_pembimbing ENUM('guru', 'luar') DEFAULT 'guru',
                guru_id INT NULL,
                nama_pembimbing_luar VARCHAR(150) NULL,
                kontak_pembimbing VARCHAR(50) NULL,
                hari VARCHAR(50) NULL,
                jam VARCHAR(50) NULL,
                tempat VARCHAR(100) NULL,
                deskripsi TEXT NULL,
                status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (guru_id),
                INDEX (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $this->db->exec($sqlEkskul);

            $sqlSiswaEkskul = "CREATE TABLE IF NOT EXISTS ekstrakurikuler_siswa (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ekskul_id INT NOT NULL,
                siswa_id INT NOT NULL,
                tahun_ajaran_id INT NULL,
                semester VARCHAR(10) NULL,
                predikat VARCHAR(20) DEFAULT 'Sangat Baik',
                nilai_deskripsi TEXT NULL,
                status ENUM('aktif', 'selesai', 'keluar') DEFAULT 'aktif',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_ekskul_siswa (ekskul_id, siswa_id, tahun_ajaran_id, semester),
                INDEX (siswa_id),
                INDEX (ekskul_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $this->db->exec($sqlSiswaEkskul);
        } catch (\Throwable $e) {
            // diamkan jika tabel sudah ada atau ada kendala minor
        }
    }

    /**
     * Mengambil seluruh data ekstrakurikuler
     */
    public function getAllEkskul($onlyActive = false) {
        try {
            $where = $onlyActive ? "WHERE e.status = 'aktif'" : "";
            $sql = "SELECT e.*, 
                           g.nama_lengkap AS nama_guru, 
                           g.nip AS nip_guru,
                           (SELECT COUNT(*) FROM ekstrakurikuler_siswa es WHERE es.ekskul_id = e.id AND es.status = 'aktif') AS total_anggota
                    FROM ekstrakurikuler e
                    LEFT JOIN guru g ON e.guru_id = g.id
                    {$where}
                    ORDER BY e.nama_ekskul ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Mengambil detail satu ekskul berdasarkan ID
     */
    public function getEkskulById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT e.*, 
                       g.nama_lengkap AS nama_guru, 
                       g.nip AS nip_guru,
                       g.no_telepon AS telp_guru
                FROM ekstrakurikuler e
                LEFT JOIN guru g ON e.guru_id = g.id
                WHERE e.id = ?
            ");
            $stmt->execute([(int)$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Menyimpan data ekskul baru
     */
    public function createEkskul($data) {
        try {
            $tipe = ($data['tipe_pembimbing'] ?? 'guru') === 'luar' ? 'luar' : 'guru';
            $guruId = ($tipe === 'guru' && !empty($data['guru_id'])) ? (int)$data['guru_id'] : null;
            $namaLuar = ($tipe === 'luar') ? trim($data['nama_pembimbing_luar'] ?? '') : null;
            $kontak = trim($data['kontak_pembimbing'] ?? '');

            $stmt = $this->db->prepare("
                INSERT INTO ekstrakurikuler (nama_ekskul, tipe_pembimbing, guru_id, nama_pembimbing_luar, kontak_pembimbing, hari, jam, tempat, deskripsi, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                trim($data['nama_ekskul']),
                $tipe,
                $guruId,
                $namaLuar,
                $kontak,
                trim($data['hari'] ?? ''),
                trim($data['jam'] ?? ''),
                trim($data['tempat'] ?? ''),
                trim($data['deskripsi'] ?? ''),
                $data['status'] ?? 'aktif'
            ]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Mengubah data ekskul
     */
    public function updateEkskul($id, $data) {
        try {
            $tipe = ($data['tipe_pembimbing'] ?? 'guru') === 'luar' ? 'luar' : 'guru';
            $guruId = ($tipe === 'guru' && !empty($data['guru_id'])) ? (int)$data['guru_id'] : null;
            $namaLuar = ($tipe === 'luar') ? trim($data['nama_pembimbing_luar'] ?? '') : null;
            $kontak = trim($data['kontak_pembimbing'] ?? '');

            $stmt = $this->db->prepare("
                UPDATE ekstrakurikuler 
                SET nama_ekskul = ?, 
                    tipe_pembimbing = ?, 
                    guru_id = ?, 
                    nama_pembimbing_luar = ?, 
                    kontak_pembimbing = ?, 
                    hari = ?, 
                    jam = ?, 
                    tempat = ?, 
                    deskripsi = ?, 
                    status = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                trim($data['nama_ekskul']),
                $tipe,
                $guruId,
                $namaLuar,
                $kontak,
                trim($data['hari'] ?? ''),
                trim($data['jam'] ?? ''),
                trim($data['tempat'] ?? ''),
                trim($data['deskripsi'] ?? ''),
                $data['status'] ?? 'aktif',
                (int)$id
            ]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Menghapus ekskul
     */
    public function deleteEkskul($id) {
        try {
            $id = (int)$id;
            $this->db->prepare("DELETE FROM ekstrakurikuler_siswa WHERE ekskul_id = ?")->execute([$id]);
            return $this->db->prepare("DELETE FROM ekstrakurikuler WHERE id = ?")->execute([$id]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Mengambil daftar anggota siswa dalam suatu ekskul
     */
    public function getAnggotaEkskul($ekskulId, $taId = null, $semester = null) {
        try {
            $params = [(int)$ekskulId];
            $where = "WHERE es.ekskul_id = ?";
            if ($taId) {
                $where .= " AND (es.tahun_ajaran_id = ? OR es.tahun_ajaran_id IS NULL)";
                $params[] = (int)$taId;
            }
            if ($semester) {
                $where .= " AND (es.semester = ? OR es.semester IS NULL)";
                $params[] = $semester;
            }

            $sql = "SELECT es.*, 
                           s.nama_lengkap, s.nis, s.nisn, 
                           k.nama_kelas
                    FROM ekstrakurikuler_siswa es
                    JOIN siswa s ON es.siswa_id = s.id
                    LEFT JOIN kelas k ON s.kelas_id = k.id
                    {$where}
                    ORDER BY k.nama_kelas ASC, s.nama_lengkap ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Siswa mengikuti (Join) ekstrakurikuler
     */
    public function joinEkskul($siswaId, $ekskulId, $taId = null, $semester = null) {
        try {
            $sId = (int)$siswaId;
            $eId = (int)$ekskulId;

            // Cek apakah sudah pernah terdaftar
            $stmtCheck = $this->db->prepare("
                SELECT id, status FROM ekstrakurikuler_siswa 
                WHERE siswa_id = ? AND ekskul_id = ?
            ");
            $stmtCheck->execute([$sId, $eId]);
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Aktifkan kembali jika sebelumnya nonaktif
                $stmtUp = $this->db->prepare("
                    UPDATE ekstrakurikuler_siswa 
                    SET status = 'aktif', tahun_ajaran_id = ?, semester = ? 
                    WHERE id = ?
                ");
                return $stmtUp->execute([$taId, $semester, $existing['id']]);
            }

            $stmtIns = $this->db->prepare("
                INSERT INTO ekstrakurikuler_siswa (ekskul_id, siswa_id, tahun_ajaran_id, semester, predikat, nilai_deskripsi, status)
                VALUES (?, ?, ?, ?, 'Sangat Baik', '', 'aktif')
            ");
            return $stmtIns->execute([$eId, $sId, $taId, $semester]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Siswa membatalkan (Leave) ekstrakurikuler
     */
    public function leaveEkskul($siswaId, $ekskulId) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM ekstrakurikuler_siswa 
                WHERE siswa_id = ? AND ekskul_id = ?
            ");
            return $stmt->execute([(int)$siswaId, (int)$ekskulId]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Admin/Pembimbing memperbarui nilai predikat & deskripsi capaian ekskul siswa
     */
    public function updateNilaiDeskripsi($anggotaId, $predikat, $nilaiDeskripsi) {
        try {
            $stmt = $this->db->prepare("
                UPDATE ekstrakurikuler_siswa 
                SET predikat = ?, nilai_deskripsi = ? 
                WHERE id = ?
            ");
            return $stmt->execute([
                trim($predikat),
                trim($nilaiDeskripsi),
                (int)$anggotaId
            ]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Mengambil daftar ekstrakurikuler yang diikuti oleh seorang siswa untuk E-Rapor
     */
    public function getEkskulBySiswa($siswaId, $taId = null, $semester = null) {
        try {
            $sId = (int)$siswaId;
            $params = [$sId];
            $where = "WHERE es.siswa_id = ? AND es.status = 'aktif'";
            if ($taId) {
                $where .= " AND (es.tahun_ajaran_id = ? OR es.tahun_ajaran_id IS NULL)";
                $params[] = (int)$taId;
            }
            if ($semester) {
                $where .= " AND (es.semester = ? OR es.semester IS NULL)";
                $params[] = $semester;
            }

            $sql = "SELECT es.*, 
                           e.nama_ekskul, e.tipe_pembimbing, e.nama_pembimbing_luar, e.kontak_pembimbing,
                           e.hari, e.jam, e.tempat, e.deskripsi AS deskripsi_ekskul,
                           g.nama_lengkap AS nama_guru_pembimbing
                    FROM ekstrakurikuler_siswa es
                    JOIN ekstrakurikuler e ON es.ekskul_id = e.id
                    LEFT JOIN guru g ON e.guru_id = g.id
                    {$where}
                    ORDER BY e.nama_ekskul ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Memformat nama pembimbing secara konsisten
            foreach ($rows as &$r) {
                if ($r['tipe_pembimbing'] === 'luar') {
                    $r['pembimbing'] = !empty($r['nama_pembimbing_luar']) ? $r['nama_pembimbing_luar'] . ' (Pembimbing Luar)' : 'Pembimbing Luar';
                } else {
                    $r['pembimbing'] = !empty($r['nama_guru_pembimbing']) ? $r['nama_guru_pembimbing'] . ' (Guru)' : 'Guru Pembimbing';
                }
            }
            return $rows;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Mendapatkan ID ekskul yang sedang diikuti oleh siswa (array sederhana)
     */
    public function getEnrolledEkskulIds($siswaId) {
        try {
            $stmt = $this->db->prepare("
                SELECT ekskul_id 
                FROM ekstrakurikuler_siswa 
                WHERE siswa_id = ? AND status = 'aktif'
            ");
            $stmt->execute([(int)$siswaId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
