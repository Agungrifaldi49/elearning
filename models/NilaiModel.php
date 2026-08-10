<?php
/**
 * NilaiModel.php
 * Model untuk E-Rapor: Input Nilai, Hitung Nilai Akhir, Predikat
 */
class NilaiModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        try {
            $this->db->exec("ALTER TABLE mata_pelajaran ADD COLUMN IF NOT EXISTS kkm INT DEFAULT 75");
        } catch (Exception $e) {}
        try {
            $sql = "CREATE TABLE IF NOT EXISTS nilai_rapor (
                id INT AUTO_INCREMENT PRIMARY KEY,
                siswa_id INT NOT NULL,
                mapel_id INT NOT NULL,
                nilai_tugas DECIMAL(5,2) DEFAULT 0,
                nilai_quiz DECIMAL(5,2) DEFAULT 0,
                nilai_uts DECIMAL(5,2) DEFAULT 0,
                nilai_uas DECIMAL(5,2) DEFAULT 0,
                nilai_akhir DECIMAL(5,2) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL,
                FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
                FOREIGN KEY (mapel_id) REFERENCES mata_pelajaran(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->exec($sql);
        } catch (Exception $e) {}
    }

    /**
     * Ambil semua nilai milik seorang siswa
     */
    public function getNilaiBySiswa(int $siswaId): array {
        $stmt = $this->db->prepare("
            SELECT n.*, mp.nama_mapel, COALESCE(mp.kkm, 75) as kkm
            FROM nilai_rapor n
            JOIN mata_pelajaran mp ON n.mapel_id = mp.id
            JOIN siswa_mapel_enrollment sme ON (n.siswa_id = sme.siswa_id AND n.mapel_id = sme.mapel_id)
            WHERE n.siswa_id = ?
            GROUP BY n.id, mp.id
            ORDER BY mp.nama_mapel ASC
        ");
        $stmt->execute([$siswaId]);
        $enrolledNilai = $stmt->fetchAll();

        // Fallback for students with un-enrolled non-zero grades
        if (empty($enrolledNilai)) {
            $stmtFallback = $this->db->prepare("
                SELECT n.*, mp.nama_mapel, COALESCE(mp.kkm, 75) as kkm
                FROM nilai_rapor n
                JOIN mata_pelajaran mp ON n.mapel_id = mp.id
                WHERE n.siswa_id = ? AND (n.nilai_tugas > 0 OR n.nilai_quiz > 0 OR n.nilai_uts > 0 OR n.nilai_uas > 0)
                ORDER BY mp.nama_mapel ASC
            ");
            $stmtFallback->execute([$siswaId]);
            $enrolledNilai = $stmtFallback->fetchAll();
        }

        return $enrolledNilai;
    }

    /**
     * Simpan / update nilai siswa untuk satu mapel
     */
    public function simpanNilai(int $siswaId, int $mapelId, array $komponen): bool {
        $tugas = (float)($komponen['nilai_tugas'] ?? 0);
        $quiz  = (float)($komponen['nilai_quiz'] ?? 0);
        $uts   = (float)($komponen['nilai_uts'] ?? 0);
        $uas   = (float)($komponen['nilai_uas'] ?? 0);

        // Formula: 20% tugas + 20% quiz + 30% UTS + 30% UAS
        $akhir = ($tugas * 0.20) + ($quiz * 0.20) + ($uts * 0.30) + ($uas * 0.30);

        // Cek apakah sudah ada data nilai untuk siswa + mapel ini
        $check = $this->db->prepare("SELECT id FROM nilai_rapor WHERE siswa_id = ? AND mapel_id = ?");
        $check->execute([$siswaId, $mapelId]);
        $existing = $check->fetch();

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE nilai_rapor
                SET nilai_tugas=?, nilai_quiz=?, nilai_uts=?, nilai_uas=?, nilai_akhir=?, updated_at=NOW()
                WHERE siswa_id=? AND mapel_id=?
            ");
            return $stmt->execute([$tugas, $quiz, $uts, $uas, $akhir, $siswaId, $mapelId]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO nilai_rapor (siswa_id, mapel_id, nilai_tugas, nilai_quiz, nilai_uts, nilai_uas, nilai_akhir, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            return $stmt->execute([$siswaId, $mapelId, $tugas, $quiz, $uts, $uas, $akhir]);
        }
    }

    public function saveNilai($siswaId, $mapelId, $semesterId = 1, $tahunId = 1, $tugas = 0, $quiz = 0, $uts = 0, $uas = 0): bool {
        return $this->simpanNilai((int)$siswaId, (int)$mapelId, [
            'nilai_tugas' => (float)$tugas,
            'nilai_quiz'  => (float)$quiz,
            'nilai_uts'   => (float)$uts,
            'nilai_uas'   => (float)$uas
        ]);
    }

    /**
     * Smart Synchronizer: Calculates Real-Time Tugas & Quiz Scores for a student & mapel
     * and merges them with existing UTS & UAS values in nilai_rapor without wiping data.
     */
    public function syncSiswaMapelNilai(int $siswaId, int $mapelId): bool {
        // 1. Calculate Real-Time Tugas Average
        $stmtTugas = $this->db->prepare("
            SELECT AVG(pt.nilai) 
            FROM pengumpulan_tugas pt
            JOIN tugas t ON pt.tugas_id = t.id
            WHERE pt.siswa_id = ? AND t.mapel_id = ? AND pt.nilai IS NOT NULL
        ");
        $stmtTugas->execute([$siswaId, $mapelId]);
        $avgTugasVal = $stmtTugas->fetchColumn();

        // 2. Calculate Real-Time Quiz Highest Score Average (kategori = 'kuis' or null/empty)
        $stmtQuiz = $this->db->prepare("
            SELECT AVG(CASE WHEN COALESCE(hq.nilai_tertinggi, 0) > 0 THEN hq.nilai_tertinggi ELSE hq.total_nilai END)
            FROM hasil_quiz hq
            JOIN quiz q ON hq.quiz_id = q.id
            WHERE hq.siswa_id = ? AND q.mapel_id = ? 
            AND (q.kategori IS NULL OR q.kategori = 'kuis' OR q.kategori = '')
            AND (hq.status_lulus IS NULL OR hq.status_lulus != 'menunggu')
        ");
        $stmtQuiz->execute([$siswaId, $mapelId]);
        $avgQuizVal = $stmtQuiz->fetchColumn();

        // 3. Calculate Real-Time UTS Highest Score Average (kategori = 'uts')
        $stmtUts = $this->db->prepare("
            SELECT AVG(CASE WHEN COALESCE(hq.nilai_tertinggi, 0) > 0 THEN hq.nilai_tertinggi ELSE hq.total_nilai END)
            FROM hasil_quiz hq
            JOIN quiz q ON hq.quiz_id = q.id
            WHERE hq.siswa_id = ? AND q.mapel_id = ? 
            AND q.kategori = 'uts'
            AND (hq.status_lulus IS NULL OR hq.status_lulus != 'menunggu')
        ");
        $stmtUts->execute([$siswaId, $mapelId]);
        $avgUtsVal = $stmtUts->fetchColumn();

        // 4. Calculate Real-Time UAS Highest Score Average (kategori = 'uas')
        $stmtUas = $this->db->prepare("
            SELECT AVG(CASE WHEN COALESCE(hq.nilai_tertinggi, 0) > 0 THEN hq.nilai_tertinggi ELSE hq.total_nilai END)
            FROM hasil_quiz hq
            JOIN quiz q ON hq.quiz_id = q.id
            WHERE hq.siswa_id = ? AND q.mapel_id = ? 
            AND q.kategori = 'uas'
            AND (hq.status_lulus IS NULL OR hq.status_lulus != 'menunggu')
        ");
        $stmtUas->execute([$siswaId, $mapelId]);
        $avgUasVal = $stmtUas->fetchColumn();

        // 5. Read Existing Record from nilai_rapor
        $check = $this->db->prepare("SELECT * FROM nilai_rapor WHERE siswa_id = ? AND mapel_id = ?");
        $check->execute([$siswaId, $mapelId]);
        $existing = $check->fetch();

        $tugas = ($avgTugasVal !== false && $avgTugasVal !== null) ? (float)$avgTugasVal : (float)($existing['nilai_tugas'] ?? 0);
        $quiz  = ($avgQuizVal  !== false && $avgQuizVal  !== null) ? (float)$avgQuizVal  : (float)($existing['nilai_quiz']  ?? 0);
        $uts   = ($avgUtsVal   !== false && $avgUtsVal   !== null) ? (float)$avgUtsVal   : (float)($existing['nilai_uts']   ?? 0);
        $uas   = ($avgUasVal   !== false && $avgUasVal   !== null) ? (float)$avgUasVal   : (float)($existing['nilai_uas']   ?? 0);

        // Calculate Proportional Weighted Average for Available Evaluation Components
        $weights = [];
        if ($avgTugasVal !== false && $avgTugasVal !== null) {
            $weights[] = ['val' => (float)$avgTugasVal, 'w' => 0.20];
        } elseif (!empty($existing['nilai_tugas']) && (float)$existing['nilai_tugas'] > 0) {
            $weights[] = ['val' => (float)$existing['nilai_tugas'], 'w' => 0.20];
        }

        if ($avgQuizVal !== false && $avgQuizVal !== null) {
            $weights[] = ['val' => (float)$avgQuizVal, 'w' => 0.20];
        } elseif (!empty($existing['nilai_quiz']) && (float)$existing['nilai_quiz'] > 0) {
            $weights[] = ['val' => (float)$existing['nilai_quiz'], 'w' => 0.20];
        }

        if ($avgUtsVal !== false && $avgUtsVal !== null) {
            $weights[] = ['val' => (float)$avgUtsVal, 'w' => 0.30];
        } elseif (!empty($existing['nilai_uts']) && (float)$existing['nilai_uts'] > 0) {
            $weights[] = ['val' => (float)$existing['nilai_uts'], 'w' => 0.30];
        }

        if ($avgUasVal !== false && $avgUasVal !== null) {
            $weights[] = ['val' => (float)$avgUasVal, 'w' => 0.30];
        } elseif (!empty($existing['nilai_uas']) && (float)$existing['nilai_uas'] > 0) {
            $weights[] = ['val' => (float)$existing['nilai_uas'], 'w' => 0.30];
        }

        if (!empty($weights)) {
            $sumVal = 0;
            $sumW = 0;
            foreach ($weights as $wItem) {
                $sumVal += ($wItem['val'] * $wItem['w']);
                $sumW += $wItem['w'];
            }
            $akhir = ($sumW > 0) ? round($sumVal / $sumW, 2) : 0.00;
        } else {
            $akhir = ($tugas * 0.20) + ($quiz * 0.20) + ($uts * 0.30) + ($uas * 0.30);
        }

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE nilai_rapor
                SET nilai_tugas = ?, nilai_quiz = ?, nilai_uts = ?, nilai_uas = ?, nilai_akhir = ?, updated_at = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([$tugas, $quiz, $uts, $uas, $akhir, $existing['id']]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO nilai_rapor (siswa_id, mapel_id, nilai_tugas, nilai_quiz, nilai_uts, nilai_uas, nilai_akhir, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            return $stmt->execute([$siswaId, $mapelId, $tugas, $quiz, $uts, $uas, $akhir]);
        }
    }

    /**
     * Batch Sync All Students and All Enrolled Mapels across the database
     */
    public function syncAllNilaiRapor(): int {
        $stmtSiswa = $this->db->query("SELECT id FROM siswa");
        $siswas = $stmtSiswa->fetchAll(PDO::FETCH_COLUMN);

        $stmtMapel = $this->db->query("SELECT id FROM mata_pelajaran");
        $mapels = $stmtMapel->fetchAll(PDO::FETCH_COLUMN);

        $syncedCount = 0;
        foreach ($siswas as $sId) {
            foreach ($mapels as $mId) {
                $this->syncSiswaMapelNilai((int)$sId, (int)$mId);
                $syncedCount++;
            }
        }
        return $syncedCount;
    }

    public function getNilaiByKelasAndMapel(int $kelasId, int $mapelId = 0): array {
        $stmtSiswa = $this->db->prepare("SELECT id FROM siswa WHERE kelas_id = ?");
        $stmtSiswa->execute([$kelasId]);
        $siswas = $stmtSiswa->fetchAll(PDO::FETCH_COLUMN);

        $stmtMapel = $this->db->query("SELECT id FROM mata_pelajaran");
        $allMapels = $stmtMapel->fetchAll(PDO::FETCH_COLUMN);

        foreach ($siswas as $sId) {
            foreach ($allMapels as $mId) {
                $this->syncSiswaMapelNilai((int)$sId, (int)$mId);
            }
        }

        $sql = "
            SELECT n.*, mp.nama_mapel
            FROM nilai_rapor n
            JOIN siswa s ON n.siswa_id = s.id
            JOIN mata_pelajaran mp ON n.mapel_id = mp.id
            WHERE s.kelas_id = ?
        ";
        $params = [$kelasId];

        if ($mapelId > 0) {
            $sql .= " AND n.mapel_id = ?";
            $params[] = $mapelId;
        }

        $sql .= " ORDER BY mp.nama_mapel ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $r) {
            if ($mapelId > 0) {
                $result[$r['siswa_id']] = $r;
            } else {
                $result[$r['siswa_id']][$r['mapel_id']] = $r;
            }
        }
        return $result;
    }

    /**
     * Hitung predikat huruf berdasarkan nilai akhir
     * Standar SMK: A=88–100, B=78–87, C=68–77, D=0–67
     */
    public static function getPredikat(float $nilai): array {
        if ($nilai >= 88) {
            return ['grade' => 'A', 'label' => 'Sangat Baik', 'class' => 'bg-success'];
        } elseif ($nilai >= 78) {
            return ['grade' => 'B', 'label' => 'Baik', 'class' => 'bg-primary'];
        } elseif ($nilai >= 68) {
            return ['grade' => 'C', 'label' => 'Cukup', 'class' => 'bg-warning text-dark'];
        } else {
            return ['grade' => 'D', 'label' => 'Kurang', 'class' => 'bg-danger'];
        }
    }

    /**
     * Rata-rata nilai semua siswa per mapel (untuk guru)
     */
    public function getRataRataPerMapel(): array {
        $stmt = $this->db->query("
            SELECT mp.nama_mapel, ROUND(AVG(n.nilai_akhir), 1) as avg_nilai,
                   COUNT(DISTINCT n.siswa_id) as total_siswa
            FROM nilai_rapor n
            JOIN mata_pelajaran mp ON n.mapel_id = mp.id
            GROUP BY mp.id, mp.nama_mapel
            ORDER BY avg_nilai DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Ambil rekap nilai untuk 1 kelas (laporan kepsek/wali kelas)
     */
    public function getRaporByKelas(int $kelasId): array {
        $stmt = $this->db->prepare("
            SELECT u.full_name as nama_lengkap, s.nis, s.nisn,
                   mp.nama_mapel, n.nilai_akhir
            FROM nilai_rapor n
            JOIN siswa s ON n.siswa_id = s.id
            JOIN users u ON s.user_id = u.id
            JOIN mata_pelajaran mp ON n.mapel_id = mp.id
            WHERE s.kelas_id = ?
            ORDER BY u.full_name ASC, mp.nama_mapel ASC
        ");
        $stmt->execute([$kelasId]);
        return $stmt->fetchAll();
    }

    /**
     * Statistik ringkasan (jumlah nilai per predikat)
     */
    public function getStatistikPredikat(): array {
        $stmt = $this->db->query("
            SELECT
                SUM(CASE WHEN nilai_akhir >= 88 THEN 1 ELSE 0 END) as predikat_a,
                SUM(CASE WHEN nilai_akhir BETWEEN 78 AND 87.9 THEN 1 ELSE 0 END) as predikat_b,
                SUM(CASE WHEN nilai_akhir BETWEEN 68 AND 77.9 THEN 1 ELSE 0 END) as predikat_c,
                SUM(CASE WHEN nilai_akhir < 68 THEN 1 ELSE 0 END) as predikat_d,
                COUNT(*) as total
            FROM nilai_rapor
        ");
        return $stmt->fetch() ?: [];
    }
}
