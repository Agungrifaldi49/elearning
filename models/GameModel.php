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
                    tipe_game VARCHAR(50) DEFAULT 'mario_run',
                    durasi_per_soal INT DEFAULT 15,
                    kkm INT DEFAULT 75,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            try {
                $this->db->exec("ALTER TABLE game_edukasi MODIFY COLUMN tipe_game VARCHAR(50) DEFAULT 'mario_run'");
            } catch(Exception $ex) {}

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
            $sql .= " AND (g.guru_id = ? OR g.kelas_id IS NULL OR g.kelas_id = 0)";
            $params[] = (int)$guruId;
        }

        if ($kelasId) {
            $sql .= " AND (g.kelas_id IS NULL OR g.kelas_id = 0 OR g.kelas_id = ?)";
            $params[] = (int)$kelasId;
        }

        $sql .= " ORDER BY g.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function importGameSoalFromExcel($gameId, $filePath) {
        if (!file_exists($filePath)) {
            return ['status' => false, 'message' => 'Berkas Excel/CSV tidak ditemukan.', 'imported' => 0];
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['status' => false, 'message' => 'Gagal membaca berkas Excel/CSV.', 'imported' => 0];
        }

        // Auto-detect delimiter
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

        $headerRow = null;
        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty($line)) continue;
            $firstCell = strtolower(trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $line[0] ?? '')));
            if ($firstCell === 'sep=' || strpos($firstCell, '#') === 0 || strpos($firstCell, '//') === 0) continue;

            $headerRow = $line;
            break;
        }

        if (!$headerRow) {
            fclose($handle);
            return ['status' => false, 'message' => 'Format header berkas Excel/CSV tidak valid.', 'imported' => 0];
        }

        $colIndexes = [];
        foreach ($headerRow as $idx => $colName) {
            $cleanCol = strtolower(trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $colName)));
            $colIndexes[$cleanCol] = $idx;
        }

        $tanyaIdx  = $colIndexes['pertanyaan'] ?? 1;
        $opsiA_Idx = $colIndexes['opsi_a'] ?? 2;
        $opsiB_Idx = $colIndexes['opsi_b'] ?? 3;
        $opsiC_Idx = $colIndexes['opsi_c'] ?? 4;
        $opsiD_Idx = $colIndexes['opsi_d'] ?? 5;
        $jawabIdx  = $colIndexes['kunci_jawaban'] ?? 6;
        $poinIdx   = $colIndexes['poin'] ?? 7;
        $penjelasanIdx = $colIndexes['penjelasan'] ?? 8;

        $importedCount = 0;

        $stmtS = $this->db->prepare("
            INSERT INTO game_soal (game_id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, kunci_jawaban, poin, penjelasan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty($data) || count($data) < 2) continue;

            $pertanyaan = trim($data[$tanyaIdx] ?? '');
            if (empty($pertanyaan) || strtolower($pertanyaan) === 'pertanyaan' || strtolower($pertanyaan) === 'no_soal' || strpos($pertanyaan, '#') === 0 || strpos($pertanyaan, 'sep=') === 0) continue;

            $opsiA = trim($data[$opsiA_Idx] ?? '');
            $opsiB = trim($data[$opsiB_Idx] ?? '');
            $opsiC = trim($data[$opsiC_Idx] ?? '');
            $opsiD = trim($data[$opsiD_Idx] ?? '');
            $kunci = strtolower(trim($data[$jawabIdx] ?? 'a'));
            if (!in_array($kunci, ['a', 'b', 'c', 'd'])) {
                if (strtolower($kunci) === strtolower($opsiA)) $kunci = 'a';
                elseif (strtolower($kunci) === strtolower($opsiB)) $kunci = 'b';
                elseif (strtolower($kunci) === strtolower($opsiC)) $kunci = 'c';
                elseif (strtolower($kunci) === strtolower($opsiD)) $kunci = 'd';
                else $kunci = 'a';
            }

            $poin = (int)($data[$poinIdx] ?? 10);
            if ($poin <= 0) $poin = 10;
            $penjelasan = trim($data[$penjelasanIdx] ?? '');

            if (!empty($opsiA) && !empty($opsiB)) {
                $stmtS->execute([
                    (int)$gameId,
                    $pertanyaan,
                    $opsiA,
                    $opsiB,
                    $opsiC,
                    $opsiD,
                    $kunci,
                    $poin,
                    $penjelasan
                ]);
                $importedCount++;
            }
        }

        fclose($handle);

        return [
            'status' => true,
            'message' => "Berhasil meng-import {$importedCount} soal dari Excel/CSV ke Game Edukasi ini.",
            'imported' => $importedCount
        ];
    }

    public function parseGameSoalFromExcel($filePath) {
        if (!file_exists($filePath)) return [];

        $handle = fopen($filePath, 'r');
        if (!$handle) return [];

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

        $headerRow = null;
        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty($line)) continue;
            $firstCell = strtolower(trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $line[0] ?? '')));
            if ($firstCell === 'sep=' || strpos($firstCell, '#') === 0 || strpos($firstCell, '//') === 0) continue;

            $headerRow = $line;
            break;
        }

        if (!$headerRow) {
            fclose($handle);
            return [];
        }

        $colIndexes = [];
        foreach ($headerRow as $idx => $colName) {
            $cleanCol = strtolower(trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $colName)));
            $colIndexes[$cleanCol] = $idx;
        }

        $tanyaIdx  = $colIndexes['pertanyaan'] ?? 1;
        $opsiA_Idx = $colIndexes['opsi_a'] ?? 2;
        $opsiB_Idx = $colIndexes['opsi_b'] ?? 3;
        $opsiC_Idx = $colIndexes['opsi_c'] ?? 4;
        $opsiD_Idx = $colIndexes['opsi_d'] ?? 5;
        $jawabIdx  = $colIndexes['kunci_jawaban'] ?? 6;
        $poinIdx   = $colIndexes['poin'] ?? 7;
        $penjelasanIdx = $colIndexes['penjelasan'] ?? 8;

        $parsedSoal = [];

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty($data) || count($data) < 2) continue;

            $pertanyaan = trim($data[$tanyaIdx] ?? '');
            if (empty($pertanyaan) || strtolower($pertanyaan) === 'pertanyaan' || strtolower($pertanyaan) === 'no_soal' || strpos($pertanyaan, '#') === 0 || strpos($pertanyaan, 'sep=') === 0) continue;

            $opsiA = trim($data[$opsiA_Idx] ?? '');
            $opsiB = trim($data[$opsiB_Idx] ?? '');
            $opsiC = trim($data[$opsiC_Idx] ?? '');
            $opsiD = trim($data[$opsiD_Idx] ?? '');
            $kunci = strtolower(trim($data[$jawabIdx] ?? 'a'));
            if (!in_array($kunci, ['a', 'b', 'c', 'd'])) $kunci = 'a';

            $poin = (int)($data[$poinIdx] ?? 10);
            if ($poin <= 0) $poin = 10;
            $penjelasan = trim($data[$penjelasanIdx] ?? '');

            if (!empty($opsiA) && !empty($opsiB)) {
                $parsedSoal[] = [
                    'pertanyaan' => $pertanyaan,
                    'opsi_a' => $opsiA,
                    'opsi_b' => $opsiB,
                    'opsi_c' => $opsiC,
                    'opsi_d' => $opsiD,
                    'kunci_jawaban' => $kunci,
                    'poin' => $poin,
                    'penjelasan' => $penjelasan
                ];
            }
        }

        fclose($handle);
        return $parsedSoal;
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
        $soal = $stmt->fetchAll();

        if (empty($soal)) {
            $this->populateFallbackSoal($gameId);
            $stmt->execute([(int)$gameId]);
            $soal = $stmt->fetchAll();
        }

        return $soal;
    }

    public function populateFallbackSoal($gameId) {
        $game = $this->getGameDetail($gameId);
        if (!$game) return;

        $mapelName = $game['nama_mapel'] ?? 'Mata Pelajaran';
        $sampleQuestions = [
            [
                'pertanyaan' => 'Apa tujuan utama dari mata pelajaran ' . $mapelName . ' dalam pembelajaran?',
                'opsi_a' => 'Mengembangkan keterampilan & pemahaman mendalam secara praktis',
                'opsi_b' => 'Menghafal teori tanpa melakukan praktik langsung',
                'opsi_c' => 'Hanya untuk formalitas kelengkapan nilai ujian',
                'opsi_d' => 'Mengabaikan standar kompetensi kelulusan',
                'kunci_jawaban' => 'a',
                'poin' => 25,
                'penjelasan' => 'Pembelajaran bertujuan membangun kompetensi keterampilan dasar dan pemahaman mendalam.'
            ],
            [
                'pertanyaan' => 'Manakah langkah pertama yang paling tepat sebelum memulai pembuatan proyek dalam bidang ' . $mapelName . '?',
                'opsi_a' => 'Perencanaan, Perancangan & Analisis Kebutuhan',
                'opsi_b' => 'Langsung membuat produk tanpa adanya perencanaan',
                'opsi_c' => 'Menunggu instruksi tanpa persiapan materi',
                'opsi_d' => 'Menutup seluruh dokumentasi teknis',
                'kunci_jawaban' => 'a',
                'poin' => 25,
                'penjelasan' => 'Perencanaan dan analisis kebutuhan adalah fondasi utama keberhasilan setiap proyek.'
            ],
            [
                'pertanyaan' => 'Sikap manakah yang mencerminkan etika kerja & belajar profesional?',
                'opsi_a' => 'Disiplin, Jujur, Tanggung Jawab & Kerjasama Tim',
                'opsi_b' => 'Mengcopy karya orang lain tanpa izin',
                'opsi_c' => 'Apatis terhadap pencapaian proyek bersama',
                'opsi_d' => 'Mengabaikan tenggat waktu deadline yang disepakati',
                'kunci_jawaban' => 'a',
                'poin' => 25,
                'penjelasan' => 'Disiplin, kejujuran, dan integritas adalah pilar etika kerja profesional.'
            ],
            [
                'pertanyaan' => 'Bagaimanakah cara terbaik untuk mengevaluasi keberhasilan suatu tugas atau karya?',
                'opsi_a' => 'Melakukan Pengujian (Testing) & Reviu Umpan Balik',
                'opsi_b' => 'Menganggap karya langsung sempurna tanpa diuji',
                'opsi_c' => 'Menghindari kritik dan perbaikan karya',
                'opsi_d' => 'Menghapus hasil pekerjaan sebelum dinilai',
                'kunci_jawaban' => 'a',
                'poin' => 25,
                'penjelasan' => 'Pengujian (testing) dan reviu umpan balik memastikan kualitas akhir memenuhi kriteria.'
            ]
        ];

        try {
            $stmtS = $this->db->prepare("
                INSERT INTO game_soal (game_id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, kunci_jawaban, poin, penjelasan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($sampleQuestions as $s) {
                $stmtS->execute([
                    (int)$gameId,
                    $s['pertanyaan'],
                    $s['opsi_a'],
                    $s['opsi_b'],
                    $s['opsi_c'],
                    $s['opsi_d'],
                    $s['kunci_jawaban'],
                    $s['poin'],
                    $s['penjelasan']
                ]);
            }
        } catch (Exception $e) {}
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

    public function updateGame($id, $gameData, $soalList) {
        $this->db->beginTransaction();
        try {
            $stmtG = $this->db->prepare("
                UPDATE game_edukasi 
                SET mapel_id = ?, kelas_id = ?, judul = ?, deskripsi = ?, tipe_game = ?, durasi_per_soal = ?, kkm = ?
                WHERE id = ?
            ");
            $stmtG->execute([
                (int)$gameData['mapel_id'],
                !empty($gameData['kelas_id']) ? (int)$gameData['kelas_id'] : null,
                $gameData['judul'],
                $gameData['deskripsi'] ?? '',
                $gameData['tipe_game'] ?? 'mario_run',
                (int)($gameData['durasi_per_soal'] ?? 15),
                (int)($gameData['kkm'] ?? 75),
                (int)$id
            ]);

            $stmtDel = $this->db->prepare("DELETE FROM game_soal WHERE game_id = ?");
            $stmtDel->execute([(int)$id]);

            $stmtS = $this->db->prepare("
                INSERT INTO game_soal (game_id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, kunci_jawaban, poin, penjelasan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($soalList as $s) {
                if (empty($s['pertanyaan']) || empty($s['opsi_a']) || empty($s['opsi_b'])) continue;
                $stmtS->execute([
                    (int)$id,
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
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
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
            JOIN (
                SELECT siswa_id, MAX(skor_akhir) as max_score
                FROM game_skor
                WHERE game_id = ?
                GROUP BY siswa_id
            ) best ON gs.siswa_id = best.siswa_id AND gs.skor_akhir = best.max_score
            JOIN siswa s ON gs.siswa_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            WHERE gs.game_id = ?
            GROUP BY gs.siswa_id
            ORDER BY gs.skor_akhir DESC, gs.max_combo DESC, gs.waktu_selesai ASC
            LIMIT 15
        ");
        $stmt->execute([(int)$gameId, (int)$gameId]);
        return $stmt->fetchAll();
    }
}
