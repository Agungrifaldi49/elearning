<?php
/**
 * NilaiModel.php
 * Model untuk E-Rapor: Input Nilai, Hitung Nilai Akhir, Predikat
 */
require_once ROOT_PATH . 'models/CurriculumModel.php';

class NilaiModel {
    private $db;
    private static $tableEnsured = false;

    public function __construct() {
        $this->db = Database::getConnection();
        if (!self::$tableEnsured) {
            $this->ensureTableExists();
            self::$tableEnsured = true;
        }
    }

    private function ensureTableExists() {
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM mata_pelajaran LIKE 'kkm'")->fetch();
            if (!$cols) {
                $this->db->exec("ALTER TABLE mata_pelajaran ADD COLUMN kkm INT DEFAULT 75");
            }
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

        // Fallback for students with un-enrolled mapels
        if (empty($enrolledNilai)) {
            $stmtFallback = $this->db->prepare("
                SELECT n.*, mp.nama_mapel, COALESCE(mp.kkm, 75) as kkm
                FROM nilai_rapor n
                JOIN mata_pelajaran mp ON n.mapel_id = mp.id
                WHERE n.siswa_id = ?
                ORDER BY mp.nama_mapel ASC
            ");
            $stmtFallback->execute([$siswaId]);
            $enrolledNilai = $stmtFallback->fetchAll();
        }

        return $enrolledNilai;
    }

    /**
     * Ambil konfigurasi bobot penilaian resmi per kurikulum
     */
    public function getBobotKomponenByKurikulum(int $kurId): array {
        try {
            require_once ROOT_PATH . 'models/CurriculumModel.php';
            $currModel = new CurriculumModel();
            $komponenList = $currModel->getKomponenPenilaian($kurId);

            if (!empty($komponenList)) {
                $bobot = [
                    'tugas' => 0.0,
                    'quiz'  => 0.0,
                    'uts'   => 0.0,
                    'uas'   => 0.0,
                    'labels' => [
                        'tugas' => 'Tugas Mandiri / Terstruktur',
                        'quiz'  => 'Kuis / Formatif Harian',
                        'uts'   => 'Sumatif Tengah Semester (STS)',
                        'uas'   => 'Sumatif Akhir Semester (SAS)'
                    ],
                    'raw_list' => $komponenList
                ];

                $assignedSlots = [];

                foreach ($komponenList as $kp) {
                    $code = strtolower(trim($kp['kode_komponen'] ?? ''));
                    $nama = strtolower(trim($kp['nama_komponen'] ?? ''));
                    $w = ((float)($kp['bobot_persen'] ?? 0)) / 100.0;
                    $label = trim($kp['nama_komponen'] ?? '');

                    // Check target slot mapping
                    $matchedSlot = null;
                    if (strpos($code, 'tugas') !== false || strpos($code, 'formatif') !== false || strpos($code, 'tp') !== false || strpos($nama, 'tugas') !== false || strpos($nama, 'formatif') !== false || strpos($nama, 'portofolio') !== false) {
                        $matchedSlot = 'tugas';
                    } elseif (strpos($code, 'quiz') !== false || strpos($code, 'kuis') !== false || strpos($code, 'teori') !== false || strpos($code, 'sumatif_lm') !== false || strpos($nama, 'kuis') !== false || strpos($nama, 'lingkup materi') !== false || strpos($nama, 'harian') !== false) {
                        $matchedSlot = 'quiz';
                    } elseif (strpos($code, 'uts') !== false || strpos($code, 'sts') !== false || strpos($code, 'praktik') !== false || strpos($code, 'projek') !== false || strpos($nama, 'tengah') !== false || strpos($nama, 'praktik') !== false || strpos($nama, 'sts') !== false) {
                        $matchedSlot = 'uts';
                    } elseif (strpos($code, 'uas') !== false || strpos($code, 'sas') !== false || strpos($code, 'sumatif_akhir') !== false || strpos($nama, 'akhir') !== false || strpos($nama, 'sas') !== false || strpos($nama, 'uas') !== false) {
                        $matchedSlot = 'uas';
                    }

                    if ($matchedSlot && !in_array($matchedSlot, $assignedSlots)) {
                        $bobot[$matchedSlot] = $w;
                        if (!empty($label)) $bobot['labels'][$matchedSlot] = $label;
                        $assignedSlots[] = $matchedSlot;
                    } else {
                        // Slot fallback if unassigned
                        $availableSlots = array_diff(['tugas', 'quiz', 'uts', 'uas'], $assignedSlots);
                        if (!empty($availableSlots)) {
                            $fallbackSlot = reset($availableSlots);
                            $bobot[$fallbackSlot] = $w;
                            if (!empty($label)) $bobot['labels'][$fallbackSlot] = $label;
                            $assignedSlots[] = $fallbackSlot;
                        }
                    }
                }

                $totalMappedW = $bobot['tugas'] + $bobot['quiz'] + $bobot['uts'] + $bobot['uas'];
                if ($totalMappedW > 0) {
                    $bobot['pct_tugas'] = round($bobot['tugas'] * 100);
                    $bobot['pct_quiz']  = round($bobot['quiz'] * 100);
                    $bobot['pct_uts']   = round($bobot['uts'] * 100);
                    $bobot['pct_uas']   = round($bobot['uas'] * 100);
                    return $bobot;
                }
            }
        } catch (\Throwable $e) {}

        return [
            'tugas' => 0.20,
            'quiz'  => 0.20,
            'uts'   => 0.30,
            'uas'   => 0.30,
            'pct_tugas' => 20,
            'pct_quiz'  => 20,
            'pct_uts'   => 30,
            'pct_uas'   => 30,
            'labels' => [
                'tugas' => 'Tugas Mandiri / Terstruktur',
                'quiz'  => 'Kuis / Formatif Harian',
                'uts'   => 'Sumatif Tengah Semester (STS)',
                'uas'   => 'Sumatif Akhir Semester (SAS)'
            ],
            'raw_list' => []
        ];
    }

    /**
     * Ambil bobot komponen penilaian dinamis sesuai kurikulum rombel siswa
     */
    public function getBobotKomponenForSiswa(int $siswaId): array {
        try {
            $stmtS = $this->db->prepare("SELECT kelas_id FROM siswa WHERE id = ?");
            $stmtS->execute([$siswaId]);
            $kelasId = (int)$stmtS->fetchColumn();

            if ($kelasId > 0) {
                require_once ROOT_PATH . 'models/CurriculumModel.php';
                $currModel = new CurriculumModel();
                $kurInfo = $currModel->getActiveKurikulumForRombel($kelasId);
                $kurId = (int)($kurInfo['kurikulum_id'] ?? 1);
                return $this->getBobotKomponenByKurikulum($kurId);
            }
        } catch (\Throwable $e) {}

        return $this->getBobotKomponenByKurikulum(1);
    }

    /**
     * Hitung Nilai Akhir secara akurat & proporsional berdasarkan bobot kurikulum
     */
    public static function hitungNilaiAkhir(float $tugas, float $quiz, float $uts, float $uas, array $bobot): float {
        $wTugas = (float)($bobot['tugas'] ?? 0.0);
        $wQuiz  = (float)($bobot['quiz'] ?? 0.0);
        $wUts   = (float)($bobot['uts'] ?? 0.0);
        $wUas   = (float)($bobot['uas'] ?? 0.0);

        // Jika semua bobot 0 (belum ada konfigurasi valid), fallback ke default
        $totalW = $wTugas + $wQuiz + $wUts + $wUas;
        if ($totalW <= 0) {
            $wTugas = 0.20;
            $wQuiz  = 0.20;
            $wUts   = 0.30;
            $wUas   = 0.30;
            $totalW = 1.0;
        }

        $weights = [];
        if ($tugas > 0 && $wTugas > 0) $weights[] = ['val' => $tugas, 'w' => $wTugas];
        if ($quiz > 0 && $wQuiz > 0)   $weights[] = ['val' => $quiz,  'w' => $wQuiz];
        if ($uts > 0 && $wUts > 0)    $weights[] = ['val' => $uts,   'w' => $wUts];
        if ($uas > 0 && $wUas > 0)    $weights[] = ['val' => $uas,   'w' => $wUas];

        if (!empty($weights)) {
            $sumVal = 0;
            $sumW = 0;
            foreach ($weights as $wItem) {
                $sumVal += ($wItem['val'] * $wItem['w']);
                $sumW += $wItem['w'];
            }
            $akhir = ($sumW > 0) ? round($sumVal / $sumW, 2) : 0.00;
        } else {
            $sumVal = ($tugas * $wTugas) + ($quiz * $wQuiz) + ($uts * $wUts) + ($uas * $wUas);
            $akhir = ($totalW > 0) ? round($sumVal / $totalW, 2) : 0.00;
        }

        return min(100.0, max(0.0, (float)$akhir));
    }

    /**
     * Simpan / update nilai siswa untuk satu mapel
     */
    public function simpanNilai(int $siswaId, int $mapelId, array $komponen): bool {
        $tugas = min(100.0, max(0.0, (float)($komponen['nilai_tugas'] ?? 0)));
        $quiz  = min(100.0, max(0.0, (float)($komponen['nilai_quiz'] ?? 0)));
        $uts   = min(100.0, max(0.0, (float)($komponen['nilai_uts'] ?? 0)));
        $uas   = min(100.0, max(0.0, (float)($komponen['nilai_uas'] ?? 0)));

        $bobot = $this->getBobotKomponenForSiswa($siswaId);
        $akhir = self::hitungNilaiAkhir($tugas, $quiz, $uts, $uas, $bobot);

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
            $res = $stmt->execute([$tugas, $quiz, $uts, $uas, $akhir, $siswaId, $mapelId]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO nilai_rapor (siswa_id, mapel_id, nilai_tugas, nilai_quiz, nilai_uts, nilai_uas, nilai_akhir, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $res = $stmt->execute([$siswaId, $mapelId, $tugas, $quiz, $uts, $uas, $akhir]);
        }

        if ($res) {
            $this->syncToDynamicRapor($siswaId);
        }
        return (bool)$res;
    }

    private function syncToDynamicRapor(int $siswaId) {
        try {
            require_once ROOT_PATH . 'models/CurriculumModel.php';
            $currModel = new CurriculumModel();
            $stmtTa = $this->db->query("SELECT id, semester FROM tahun_ajaran WHERE status = 'aktif' OR is_active = 1 ORDER BY id DESC LIMIT 1");
            $ta = $stmtTa ? $stmtTa->fetch(PDO::FETCH_ASSOC) : null;
            $taId = $ta['id'] ?? 1;
            $sem = $ta['semester'] ?? 'Ganjil';
            $currModel->generateOrSyncRaporSiswa($siswaId, $taId, $sem);
        } catch (\Throwable $e) {}
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

        $bobot = $this->getBobotKomponenForSiswa($siswaId);
        $akhir = self::hitungNilaiAkhir($tugas, $quiz, $uts, $uas, $bobot);

        $res = false;
        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE nilai_rapor
                SET nilai_tugas = ?, nilai_quiz = ?, nilai_uts = ?, nilai_uas = ?, nilai_akhir = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $res = $stmt->execute([$tugas, $quiz, $uts, $uas, $akhir, $existing['id']]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO nilai_rapor (siswa_id, mapel_id, nilai_tugas, nilai_quiz, nilai_uts, nilai_uas, nilai_akhir, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $res = $stmt->execute([$siswaId, $mapelId, $tugas, $quiz, $uts, $uas, $akhir]);
        }

        if ($res) {
            $this->syncToDynamicRapor($siswaId);
        }
        return (bool)$res;
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

    /**
     * Rekalkulasi seluruh nilai rapor & E-Rapor siswa untuk kurikulum yang diubah bobotnya
     */
    public function recalculateAllNilaiForKurikulum(int $kurId): int {
        try {
            require_once ROOT_PATH . 'models/CurriculumModel.php';
            $currModel = new CurriculumModel();
            $bobot = $this->getBobotKomponenByKurikulum($kurId);

            // Dapatkan tahun ajaran & semester aktif
            $stmtTa = $this->db->query("SELECT id, COALESCE(tahun_ajaran, tahun) as tahun_ajaran, COALESCE(semester, 'Ganjil') as semester FROM tahun_ajaran WHERE status = 'aktif' OR is_active = 1 ORDER BY id DESC LIMIT 1");
            $ta = $stmtTa ? $stmtTa->fetch(PDO::FETCH_ASSOC) : null;
            $taId = (int)($ta['id'] ?? 1);
            $sem = $ta['semester'] ?? 'Ganjil';

            // Ambil semua rombel yang terikat dengan kurikulum ini
            $stmtRombel = $this->db->prepare("SELECT DISTINCT rombel_id FROM rombel_kurikulum WHERE kurikulum_id = ?");
            $stmtRombel->execute([$kurId]);
            $rombelIds = $stmtRombel->fetchAll(PDO::FETCH_COLUMN);

            // Cek apakah ini kurikulum aktif global sekolah
            $activeKur = $currModel->getActiveKurikulum();
            $isGlobalActive = ($activeKur && (int)$activeKur['id'] === $kurId);
            if ($isGlobalActive) {
                // Sertakan semua rombel yang belum terdaftar di rombel_kurikulum
                $stmtUnassigned = $this->db->query("SELECT id FROM kelas WHERE id NOT IN (SELECT DISTINCT rombel_id FROM rombel_kurikulum)");
                $unassignedRombel = $stmtUnassigned ? $stmtUnassigned->fetchAll(PDO::FETCH_COLUMN) : [];
                $rombelIds = array_unique(array_merge($rombelIds, $unassignedRombel));
            }

            if (empty($rombelIds)) {
                $stmtAllSiswa = $this->db->query("SELECT id FROM siswa");
                $siswaIds = $stmtAllSiswa ? $stmtAllSiswa->fetchAll(PDO::FETCH_COLUMN) : [];
            } else {
                $inClause = implode(',', array_map('intval', $rombelIds));
                $stmtSiswa = $this->db->query("SELECT id FROM siswa WHERE kelas_id IN ({$inClause})");
                $siswaIds = $stmtSiswa ? $stmtSiswa->fetchAll(PDO::FETCH_COLUMN) : [];
            }

            if (empty($siswaIds)) {
                return 0;
            }

            $updatedStudentsCount = 0;
            $stmtGetNilai = $this->db->prepare("SELECT id, nilai_tugas, nilai_quiz, nilai_uts, nilai_uas, nilai_akhir FROM nilai_rapor WHERE siswa_id = ?");
            $stmtUpdateNilai = $this->db->prepare("UPDATE nilai_rapor SET nilai_akhir = ?, updated_at = NOW() WHERE id = ?");

            foreach ($siswaIds as $sId) {
                $sId = (int)$sId;
                $stmtGetNilai->execute([$sId]);
                $nilaiRows = $stmtGetNilai->fetchAll(PDO::FETCH_ASSOC);

                $hasNilai = !empty($nilaiRows);
                foreach ($nilaiRows as $nr) {
                    $newAkhir = self::hitungNilaiAkhir(
                        (float)($nr['nilai_tugas'] ?? 0),
                        (float)($nr['nilai_quiz'] ?? 0),
                        (float)($nr['nilai_uts'] ?? 0),
                        (float)($nr['nilai_uas'] ?? 0),
                        $bobot
                    );
                    $stmtUpdateNilai->execute([$newAkhir, (int)$nr['id']]);
                }

                // Sinkronkan ke modul e-rapor snapshot dinamis (rapor_siswa & rapor_nilai_detail)
                $currModel->generateOrSyncRaporSiswa($sId, $taId, $sem);
                if ($hasNilai) {
                    $updatedStudentsCount++;
                }
            }

            return $updatedStudentsCount;
        } catch (\Throwable $e) {
            error_log('Error recalculateAllNilaiForKurikulum: ' . $e->getMessage());
            return 0;
        }
    }

    public function getRekapNilai($kelasId, $mapelId = 0): array {
        return $this->getNilaiByKelasAndMapel((int)$kelasId, (int)$mapelId);
    }

    public function getNilaiByKelasAndMapel(int $kelasId, int $mapelId = 0): array {
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

    /**
     * Sanitasi & Rekalibrasi Otomatis Data Nilai Rapor > 100
     */
    public function sanitizeAndCapAllRaporNilai(): int {
        try {
            $stmt = $this->db->query("
                UPDATE nilai_rapor 
                SET nilai_tugas = LEAST(100.00, GREATEST(0.00, nilai_tugas)),
                    nilai_quiz  = LEAST(100.00, GREATEST(0.00, nilai_quiz)),
                    nilai_uts   = LEAST(100.00, GREATEST(0.00, nilai_uts)),
                    nilai_uas   = LEAST(100.00, GREATEST(0.00, nilai_uas)),
                    nilai_akhir = LEAST(100.00, GREATEST(0.00, nilai_akhir))
                WHERE nilai_tugas > 100 OR nilai_quiz > 100 OR nilai_uts > 100 OR nilai_uas > 100 OR nilai_akhir > 100
            ");
            return $stmt->rowCount();
        } catch (Exception $e) {
            return 0;
        }
    }
}
