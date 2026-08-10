<?php
/**
 * GameModel.php
 * Model Data untuk Modul Game Edukasi Interaktif
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class GameModel extends BaseModel {

    public function __construct() {
        parent::__construct();
        $this->ensureGameTablesExist();
    }

    private function ensureGameTablesExist() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS game_edukasi (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    guru_id INT NOT NULL,
                    mapel_id INT NOT NULL,
                    kelas_id INT NULL,
                    judul VARCHAR(255) NOT NULL,
                    deskripsi TEXT NULL,
                    tipe_game ENUM('quiz_speed', 'spin_wheel', 'memory_match') DEFAULT 'quiz_speed',
                    durasi_per_soal INT DEFAULT 15,
                    kkm INT DEFAULT 75,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS game_soal (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    game_id INT NOT NULL,
                    pertanyaan TEXT NOT NULL,
                    opsi_a VARCHAR(255) NOT NULL,
                    opsi_b VARCHAR(255) NOT NULL,
                    opsi_c VARCHAR(255) NOT NULL,
                    opsi_d VARCHAR(255) NOT NULL,
                    kunci_jawaban ENUM('a', 'b', 'c', 'd') NOT NULL,
                    poin INT DEFAULT 10,
                    penjelasan TEXT NULL,
                    FOREIGN KEY (game_id) REFERENCES game_edukasi(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS game_skor (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    game_id INT NOT NULL,
                    siswa_id INT NOT NULL,
                    skor_akhir INT NOT NULL,
                    max_combo INT DEFAULT 0,
                    total_benar INT NOT NULL,
                    total_soal INT NOT NULL,
                    waktu_selesai INT NOT NULL,
                    status_lulus ENUM('lulus', 'tidak_lulus') NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (game_id) REFERENCES game_edukasi(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (Exception $e) {}
    }

    public function getAllGames($guruId = null, $kelasId = null) {
        $sql = "
            SELECT g.*, m.nama_mapel, k.nama_kelas, gr.nama_lengkap as nama_guru,
                   (SELECT COUNT(*) FROM game_soal WHERE game_id = g.id) as total_soal,
                   (SELECT COUNT(*) FROM game_skor WHERE game_id = g.id) as total_pemain
            FROM game_edukasi g
            JOIN mata_pelajaran m ON g.mapel_id = m.id
            LEFT JOIN kelas k ON g.kelas_id = k.id
            LEFT JOIN guru gr ON g.guru_id = gr.id
            WHERE 1=1
        ";
        $params = [];

        if ($guruId) {
            $sql .= " AND g.guru_id = ?";
            $params[] = (int)$guruId;
        }

        if ($kelasId) {
            $sql .= " AND (g.kelas_id IS NULL OR g.kelas_id = ?)";
            $params[] = (int)$kelasId;
        }

        $sql .= " ORDER BY g.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getGameDetail($id) {
        $stmt = $this->db->prepare("
            SELECT g.*, m.nama_mapel, k.nama_kelas, gr.nama_lengkap as nama_guru
            FROM game_edukasi g
            JOIN mata_pelajaran m ON g.mapel_id = m.id
            LEFT JOIN kelas k ON g.kelas_id = k.id
            LEFT JOIN guru gr ON g.guru_id = gr.id
            WHERE g.id = ?
        ");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function getGameSoal($gameId) {
        $stmt = $this->db->prepare("SELECT * FROM game_soal WHERE game_id = ? ORDER BY id ASC");
        $stmt->execute([(int)$gameId]);
        return $stmt->fetchAll();
    }

    public function createGame($gameData, $soalList) {
        $this->db->beginTransaction();
        try {
            $stmtG = $this->db->prepare("
                INSERT INTO game_edukasi (guru_id, mapel_id, kelas_id, judul, deskripsi, tipe_game, durasi_per_soal, kkm) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtG->execute([
                (int)$gameData['guru_id'],
                (int)$gameData['mapel_id'],
                !empty($gameData['kelas_id']) ? (int)$gameData['kelas_id'] : null,
                $gameData['judul'],
                $gameData['deskripsi'] ?? '',
                $gameData['tipe_game'] ?? 'quiz_speed',
                (int)($gameData['durasi_per_soal'] ?? 15),
                (int)($gameData['kkm'] ?? 75)
            ]);
            $gameId = $this->db->lastInsertId();

            $stmtS = $this->db->prepare("
                INSERT INTO game_soal (game_id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, kunci_jawaban, poin, penjelasan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($soalList as $s) {
                if (empty($s['pertanyaan']) || empty($s['opsi_a']) || empty($s['opsi_b'])) continue;
                $stmtS->execute([
                    $gameId,
                    $s['pertanyaan'],
                    $s['opsi_a'],
                    $s['opsi_b'],
                    $s['opsi_c'] ?? '',
                    $s['opsi_d'] ?? '',
                    strtolower($s['kunci_jawaban'] ?? 'a'),
                    (int)($s['poin'] ?? 10),
                    $s['penjelasan'] ?? ''
                ]);
            }

            $this->db->commit();
            return $gameId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function deleteGame($id, $guruId = null, $isAdmin = false) {
        $game = $this->getGameDetail($id);
        if (!$game) return false;

        if (!$isAdmin && (int)$game['guru_id'] !== (int)$guruId) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM game_edukasi WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function saveScore($gameId, $siswaId, $skorAkhir, $maxCombo, $totalBenar, $totalSoal, $waktuSelesai, $statusLulus) {
        $stmt = $this->db->prepare("
            INSERT INTO game_skor (game_id, siswa_id, skor_akhir, max_combo, total_benar, total_soal, waktu_selesai, status_lulus)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            (int)$gameId,
            (int)$siswaId,
            (int)$skorAkhir,
            (int)$maxCombo,
            (int)$totalBenar,
            (int)$totalSoal,
            (int)$waktuSelesai,
            $statusLulus
        ]);
    }

    public function getStudentBestScore($gameId, $siswaId) {
        $stmt = $this->db->prepare("
            SELECT * FROM game_skor 
            WHERE game_id = ? AND siswa_id = ? 
            ORDER BY skor_akhir DESC, waktu_selesai ASC LIMIT 1
        ");
        $stmt->execute([(int)$gameId, (int)$siswaId]);
        return $stmt->fetch();
    }

    public function getLeaderboard($gameId) {
        $stmt = $this->db->prepare("
            SELECT gs.*, s.nama_lengkap as nama_siswa, s.nisn, k.nama_kelas, u.avatar
            FROM game_skor gs
            JOIN siswa s ON gs.siswa_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            WHERE gs.game_id = ?
            ORDER BY gs.skor_akhir DESC, gs.max_combo DESC, gs.waktu_selesai ASC
            LIMIT 15
        ");
        $stmt->execute([(int)$gameId]);
        return $stmt->fetchAll();
    }
}
