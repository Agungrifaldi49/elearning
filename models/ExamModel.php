<?php
/**
 * Exam Model (Quiz & CBT Ujian Engine)
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class ExamModel extends BaseModel {

    public function __construct() {
        parent::__construct();
        $this->ensureGambarColumn();
        $this->ensureDeadlineAndSusulanTables();
        $this->recalibrateAllQuizScores();
    }

    private function ensureGambarColumn() {
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM soal LIKE 'gambar'")->fetchAll();
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE soal ADD COLUMN gambar VARCHAR(255) NULL");
            }
        } catch (Exception $e) {}
    }

    private function ensureDeadlineAndSusulanTables() {
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM quiz LIKE 'deadline'")->fetchAll();
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE quiz ADD COLUMN deadline DATETIME NULL");
            }
        } catch (Exception $e) {}

        try {
            $cols = $this->db->query("SHOW COLUMNS FROM quiz LIKE 'max_attempts'")->fetchAll();
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE quiz ADD COLUMN max_attempts INT DEFAULT 1");
            }
        } catch (Exception $e) {}

        try {
            $cols = $this->db->query("SHOW COLUMNS FROM hasil_quiz LIKE 'attempt_count'")->fetchAll();
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE hasil_quiz ADD COLUMN attempt_count INT DEFAULT 1");
            }
        } catch (Exception $e) {}

        try {
            $cols = $this->db->query("SHOW COLUMNS FROM hasil_quiz LIKE 'nilai_tertinggi'")->fetchAll();
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE hasil_quiz ADD COLUMN nilai_tertinggi DECIMAL(5,2) DEFAULT 0.00");
            }
        } catch (Exception $e) {}

        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS hasil_quiz_history (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    siswa_id INT NOT NULL,
                    quiz_id INT NOT NULL,
                    attempt_number INT NOT NULL DEFAULT 1,
                    total_nilai DECIMAL(5,2) DEFAULT 0.00,
                    status_lulus ENUM('lulus','tidak_lulus','menunggu') DEFAULT 'lulus',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (Exception $e) {}

        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS quiz_susulan (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    quiz_id INT NOT NULL,
                    siswa_id INT NOT NULL,
                    status ENUM('pending', 'disetujui', 'ditolak') DEFAULT 'pending',
                    catatan VARCHAR(255) NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_quiz_siswa (quiz_id, siswa_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (Exception $e) {}
    }

    // --- QUIZ ---
    public function getQuizList($kelas_id = null, $guru_id = null) {
        $sql = "
            SELECT q.*, map.nama_mapel, k.nama_kelas, g.nama_lengkap as nama_guru
            FROM quiz q
            JOIN mata_pelajaran map ON q.mapel_id = map.id
            JOIN kelas k ON q.kelas_id = k.id
            JOIN guru g ON q.guru_id = g.id
            WHERE 1=1
        ";
        if ($kelas_id) {
            $sql .= " AND q.kelas_id = " . (int)$kelas_id;
        }
        if ($guru_id) {
            $sql .= " AND q.guru_id = " . (int)$guru_id;
        }
        $sql .= " ORDER BY q.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getQuizById($id) {
        $stmt = $this->db->prepare("
            SELECT q.*, map.nama_mapel, k.nama_kelas, g.nama_lengkap as nama_guru
            FROM quiz q
            JOIN mata_pelajaran map ON q.mapel_id = map.id
            JOIN kelas k ON q.kelas_id = k.id
            JOIN guru g ON q.guru_id = g.id
            WHERE q.id = ?
        ");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function createQuiz($guru_id, $mapel_id, $kelas_id, $judul, $deskripsi, $durasi, $jumlah_soal, $random_soal, $random_jawaban, $deadline = null, $max_attempts = 1, $kategori = 'kuis', $access_key = null) {
        $stmt = $this->db->prepare("
            INSERT INTO quiz (guru_id, mapel_id, kelas_id, judul, deskripsi, durasi_menit, jumlah_soal, random_soal, random_jawaban, deadline, max_attempts, kategori, access_key)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$guru_id, $mapel_id, $kelas_id, $judul, $deskripsi, $durasi, $jumlah_soal, $random_soal, $random_jawaban, $deadline ?: null, (int)$max_attempts, $kategori, $access_key ?: null]);
        return $this->db->lastInsertId();
    }

    public function updateQuiz($id, $mapel_id, $kelas_id, $judul, $deskripsi, $durasi, $random_soal, $deadline = null, $max_attempts = 1, $kategori = 'kuis', $access_key = null) {
        $stmt = $this->db->prepare("
            UPDATE quiz SET mapel_id = ?, kelas_id = ?, judul = ?, deskripsi = ?, durasi_menit = ?, random_soal = ?, deadline = ?, max_attempts = ?, kategori = ?, access_key = ?
            WHERE id = ?
        ");
        return $stmt->execute([$mapel_id, $kelas_id, $judul, $deskripsi, $durasi, $random_soal, $deadline ?: null, (int)$max_attempts, $kategori, $access_key ?: null, $id]);
    }

    public function verifyAccessKey($quizId, $inputKey) {
        $quiz = $this->getQuizById($quizId);
        if (!$quiz) return false;
        if (in_array($quiz['kategori'], ['uts', 'uas']) && !empty($quiz['access_key'])) {
            return trim(strtoupper($quiz['access_key'])) === trim(strtoupper($inputKey));
        }
        return true;
    }

    public function canSiswaAccessQuiz($quizId, $siswaId) {
        $quiz = $this->getQuizById($quizId);
        if (!$quiz) return ['access' => false, 'reason' => 'Quiz tidak ditemukan'];

        $now = date('Y-m-d H:i:s');
        $isExpired = (!empty($quiz['deadline']) && $now > $quiz['deadline']);

        $stmt = $this->db->prepare("SELECT * FROM quiz_susulan WHERE quiz_id = ? AND siswa_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$quizId, $siswaId]);
        $susulan = $stmt->fetch();

        if ($susulan) {
            if ($susulan['status'] === 'disetujui') {
                return [
                    'access' => true,
                    'is_expired' => $isExpired,
                    'status' => 'disetujui_susulan',
                    'susulan' => $susulan,
                    'quiz' => $quiz
                ];
            } else {
                return [
                    'access' => false,
                    'is_expired' => $isExpired,
                    'status' => $susulan['status'],
                    'susulan' => $susulan,
                    'quiz' => $quiz
                ];
            }
        }

        // Disqualification check
        $stmtHQ = $this->db->prepare("SELECT is_disqualified, pelanggaran_count FROM hasil_quiz WHERE quiz_id = ? AND siswa_id = ?");
        $stmtHQ->execute([$quizId, $siswaId]);
        $hqRow = $stmtHQ->fetch();
        if ($hqRow && ($hqRow['is_disqualified'] == 1 || $hqRow['pelanggaran_count'] >= 2)) {
            return [
                'access' => false,
                'is_expired' => $isExpired,
                'status' => 'diskualifikasi',
                'susulan' => null,
                'quiz' => $quiz,
                'pelanggaran_count' => $hqRow['pelanggaran_count']
            ];
        }

        if ($isExpired) {
            return [
                'access' => false,
                'is_expired' => true,
                'status' => 'terkunci',
                'susulan' => null,
                'quiz' => $quiz
            ];
        }

        // Max Attempts Check
        $maxAttempts = isset($quiz['max_attempts']) ? (int)$quiz['max_attempts'] : 1;
        if ($maxAttempts > 0) {
            $stmtH = $this->db->prepare("SELECT attempt_count, total_nilai, nilai_tertinggi FROM hasil_quiz WHERE quiz_id = ? AND siswa_id = ?");
            $stmtH->execute([$quizId, $siswaId]);
            $hRow = $stmtH->fetch();
            $attemptCount = $hRow ? (int)$hRow['attempt_count'] : 0;

            if ($attemptCount >= $maxAttempts) {
                return [
                    'access' => false,
                    'is_expired' => false,
                    'status' => 'max_attempts_reached',
                    'susulan' => null,
                    'quiz' => $quiz,
                    'attempt_count' => $attemptCount,
                    'max_attempts' => $maxAttempts,
                    'nilai_tertinggi' => $hRow['nilai_tertinggi'] ?? $hRow['total_nilai'] ?? 0
                ];
            }
        }

        return [
            'access' => true,
            'is_expired' => false,
            'status' => 'terbuka',
            'susulan' => null,
            'quiz' => $quiz
        ];
    }

    public function recordPelanggaran($siswaId, $quizId) {
        $stmt = $this->db->prepare("SELECT id, pelanggaran_count FROM hasil_quiz WHERE quiz_id = ? AND siswa_id = ?");
        $stmt->execute([$quizId, $siswaId]);
        $row = $stmt->fetch();

        if ($row) {
            $newCount = $row['pelanggaran_count'] + 1;
            $isDisq = ($newCount >= 2) ? 1 : 0;

            $up = $this->db->prepare("UPDATE hasil_quiz SET pelanggaran_count = ?, is_disqualified = ?, status_lulus = IF(? = 1, 'diskualifikasi', status_lulus) WHERE id = ?");
            $up->execute([$newCount, $isDisq, $isDisq, $row['id']]);

            return [
                'pelanggaran_count' => $newCount,
                'is_disqualified' => ($newCount >= 2)
            ];
        } else {
            $ins = $this->db->prepare("INSERT INTO hasil_quiz (siswa_id, quiz_id, total_nilai, status_lulus, started_at, pelanggaran_count, is_disqualified) VALUES (?, ?, 0, 'menunggu', NOW(), 1, 0)");
            $ins->execute([$siswaId, $quizId]);
            return [
                'pelanggaran_count' => 1,
                'is_disqualified' => false
            ];
        }
    }

    public function approveSusulanRequest($quizId, $siswaId, $catatan = 'Disetujui Guru/Admin') {
        $stmt = $this->db->prepare("
            INSERT INTO quiz_susulan (quiz_id, siswa_id, status, catatan, updated_at)
            VALUES (?, ?, 'disetujui', ?, NOW())
            ON DUPLICATE KEY UPDATE status = 'disetujui', catatan = ?, updated_at = NOW()
        ");
        $stmt->execute([$quizId, $siswaId, $catatan, $catatan]);

        $upHQ = $this->db->prepare("
            UPDATE hasil_quiz 
            SET is_disqualified = 0, pelanggaran_count = 0, status_lulus = 'menunggu'
            WHERE quiz_id = ? AND siswa_id = ?
        ");
        $upHQ->execute([$quizId, $siswaId]);

        return true;
    }

    public function recordViolation($quizId, $siswaId, $catatan = 'Didiskualifikasi otomatis karena melanggar aturan ujian online (berpindah tab / keluar fullscreen)') {
        $stmt = $this->db->prepare("
            INSERT INTO quiz_susulan (quiz_id, siswa_id, status, catatan)
            VALUES (?, ?, 'didiskualifikasi', ?)
            ON DUPLICATE KEY UPDATE status = 'didiskualifikasi', catatan = VALUES(catatan), updated_at = NOW()
        ");
        return $stmt->execute([$quizId, $siswaId, $catatan]);
    }

    public function requestSusulan($quizId, $siswaId, $catatan = '') {
        $stmt = $this->db->prepare("
            INSERT INTO quiz_susulan (quiz_id, siswa_id, status, catatan)
            VALUES (?, ?, 'pending', ?)
            ON DUPLICATE KEY UPDATE status = 'pending', catatan = VALUES(catatan), updated_at = NOW()
        ");
        return $stmt->execute([$quizId, $siswaId, $catatan]);
    }

    public function getSusulanRequestsByGuru($guruId = null) {
        $sql = "
            SELECT qs.*, q.judul as judul_quiz, s.nama_lengkap as nama_siswa, s.nisn, k.nama_kelas, map.nama_mapel
            FROM quiz_susulan qs
            JOIN quiz q ON qs.quiz_id = q.id
            JOIN siswa s ON qs.siswa_id = s.id
            JOIN kelas k ON q.kelas_id = k.id
            JOIN mata_pelajaran map ON q.mapel_id = map.id
        ";
        if ($guruId !== null) {
            $sql .= " WHERE q.guru_id = " . (int)$guruId;
        }
        $sql .= " ORDER BY qs.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function updateSusulanStatus($requestId, $status) {
        $stmt = $this->db->prepare("UPDATE quiz_susulan SET status = ? WHERE id = ?");
        return $stmt->execute([$status, (int)$requestId]);
    }

    public function deleteQuiz($id) {
        $this->db->beginTransaction();
        try {
            $idInt = (int)$id;
            $this->db->exec("DELETE FROM pilihan_jawaban WHERE soal_id IN (SELECT id FROM soal WHERE quiz_id = {$idInt})");
            $this->db->exec("DELETE FROM soal WHERE quiz_id = {$idInt}");
            $this->db->exec("DELETE FROM quiz WHERE id = {$idInt}");
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function deleteSoal($soal_id) {
        $this->db->beginTransaction();
        try {
            $soalIdInt = (int)$soal_id;
            $this->db->exec("DELETE FROM pilihan_jawaban WHERE soal_id = {$soalIdInt}");
            $this->db->exec("DELETE FROM soal WHERE id = {$soalIdInt}");
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function addSoal($quiz_id, $jenis_soal, $pertanyaan, $bobot, $pilihanArray, $gambar = null) {
        $bobotVal = ((int)$bobot > 0) ? (int)$bobot : 10;
        $stmt = $this->db->prepare("INSERT INTO soal (quiz_id, jenis_soal, pertanyaan, bobot, gambar) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$quiz_id, $jenis_soal, $pertanyaan, $bobotVal, $gambar]);
        $soal_id = $this->db->lastInsertId();

        if (($jenis_soal === 'pg' || $jenis_soal === 'tf') && !empty($pilihanArray)) {
            $stmtPil = $this->db->prepare("INSERT INTO pilihan_jawaban (soal_id, teks_pilihan, is_benar) VALUES (?, ?, ?)");
            foreach ($pilihanArray as $pil) {
                $stmtPil->execute([$soal_id, $pil['teks'], $pil['is_benar'] ? 1 : 0]);
            }
        }
        return $soal_id;
    }

    /**
     * Import Soal (PG, Essay, True/False) beserta Gambar dari file Excel / CSV ke Quiz
     */
    public function importSoalFromExcel($quizId, $fileTmpPath, array $uploadedImages = []): array {
        if (!file_exists($fileTmpPath)) {
            return ['status' => false, 'message' => 'Berkas Excel/CSV tidak ditemukan.'];
        }

        // Process uploaded image files if provided
        $imageMap = [];
        if (!empty($uploadedImages) && isset($uploadedImages['name'])) {
            $names = is_array($uploadedImages['name']) ? $uploadedImages['name'] : [$uploadedImages['name']];
            $tmpNames = is_array($uploadedImages['tmp_name']) ? $uploadedImages['tmp_name'] : [$uploadedImages['tmp_name']];
            $errors = is_array($uploadedImages['error']) ? $uploadedImages['error'] : [$uploadedImages['error']];

            $uploadDir = ROOT_PATH . 'assets/uploads/soal/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            foreach ($names as $i => $rawName) {
                if (($errors[$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK && !empty($tmpNames[$i])) {
                    $origLower = strtolower(trim($rawName));
                    $ext = strtolower(pathinfo($origLower, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $newName = 'soal_excel_' . time() . '_' . uniqid() . '.' . $ext;
                        if (@move_uploaded_file($tmpNames[$i], $uploadDir . $newName) || @copy($tmpNames[$i], $uploadDir . $newName)) {
                            $imageMap[$origLower] = $newName;
                        }
                    }
                }
            }
        }

        $content = file_get_contents($fileTmpPath);
        $delimiter = (substr_count($content, ';') > substr_count($content, ',')) ? ';' : ',';

        $handle = fopen($fileTmpPath, 'r');
        if (!$handle) {
            return ['status' => false, 'message' => 'Gagal membaca berkas Excel/CSV.'];
        }

        // Read header & detect columns dynamically
        $headerRow = null;
        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty($line)) continue;
            $firstCell = strtolower(trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $line[0] ?? '')));
            if ($firstCell === 'sep=') continue;
            if (strpos($firstCell, '#') === 0 || strpos($firstCell, '//') === 0) continue;

            // Found header row!
            $headerRow = $line;
            break;
        }

        if (!$headerRow) {
            fclose($handle);
            return ['status' => false, 'message' => 'Format header berkas Excel/CSV tidak valid.'];
        }

        $colIndexes = [];
        foreach ($headerRow as $idx => $colName) {
            $cleanCol = strtolower(trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $colName)));
            $colIndexes[$cleanCol] = $idx;
        }

        $jenisIdx = $colIndexes['jenis_soal'] ?? 0;
        $tanyaIdx = $colIndexes['pertanyaan'] ?? 1;
        $bobotIdx = $colIndexes['bobot'] ?? 2;
        $gambarIdx = $colIndexes['gambar'] ?? ($colIndexes['file_gambar'] ?? ($colIndexes['image'] ?? null));

        $opsiA_Idx = $colIndexes['opsi_a'] ?? ($gambarIdx !== null ? 4 : 3);
        $opsiB_Idx = $colIndexes['opsi_b'] ?? ($opsiA_Idx + 1);
        $opsiC_Idx = $colIndexes['opsi_c'] ?? ($opsiA_Idx + 2);
        $opsiD_Idx = $colIndexes['opsi_d'] ?? ($opsiA_Idx + 3);
        $opsiE_Idx = $colIndexes['opsi_e'] ?? ($opsiA_Idx + 4);
        $jawabIdx  = $colIndexes['jawaban_benar'] ?? ($opsiA_Idx + 5);

        $importedCount = 0;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty($data) || count($data) < 2) continue;

            $rawJenis = strtolower(trim($data[$jenisIdx] ?? ''));
            $rawJenis = preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $rawJenis);

            if (empty($rawJenis) || strpos($rawJenis, '#') === 0 || strpos($rawJenis, '//') === 0 || strpos($rawJenis, 'sep=') === 0 || $rawJenis === 'jenis_soal') {
                continue;
            }

            $pertanyaan = trim($data[$tanyaIdx] ?? '');
            $bobot = (int)($data[$bobotIdx] ?? 10);
            if ($bobot <= 0) $bobot = 10;

            if (empty($pertanyaan)) continue;

            // Handle Gambar column if specified
            $finalGambarName = null;
            if ($gambarIdx !== null && isset($data[$gambarIdx])) {
                $gambarVal = trim($data[$gambarIdx]);
                if (!empty($gambarVal)) {
                    $lowVal = strtolower($gambarVal);
                    if (isset($imageMap[$lowVal])) {
                        $finalGambarName = $imageMap[$lowVal];
                    } elseif (filter_var($gambarVal, FILTER_VALIDATE_URL)) {
                        // Download external URL image
                        $ext = strtolower(pathinfo(parse_url($gambarVal, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg';
                        $newName = 'soal_url_' . time() . '_' . uniqid() . '.' . $ext;
                        $destPath = ROOT_PATH . 'assets/uploads/soal/' . $newName;
                        $imgBytes = @file_get_contents($gambarVal);
                        if ($imgBytes) {
                            file_put_contents($destPath, $imgBytes);
                            $finalGambarName = $newName;
                        }
                    } elseif (file_exists(ROOT_PATH . 'assets/uploads/soal/' . $gambarVal)) {
                        $finalGambarName = $gambarVal;
                    }
                }
            }

            // Normalize jenis_soal
            $jenisSoal = 'pg';
            if (in_array($rawJenis, ['essay', 'uraian', 'esay'])) {
                $jenisSoal = 'essay';
            } elseif (in_array($rawJenis, ['tf', 'true/false', 'true_false', 'bs', 'benar/salah', 'benar_salah'])) {
                $jenisSoal = 'tf';
            }

            $pilihanArray = [];

            if ($jenisSoal === 'pg') {
                $opsiA = trim($data[$opsiA_Idx] ?? '');
                $opsiB = trim($data[$opsiB_Idx] ?? '');
                $opsiC = trim($data[$opsiC_Idx] ?? '');
                $opsiD = trim($data[$opsiD_Idx] ?? '');
                $opsiE = trim($data[$opsiE_Idx] ?? '');
                $jawabanBenar = strtoupper(trim($data[$jawabIdx] ?? 'A'));

                $rawOptions = [
                    'A' => $opsiA,
                    'B' => $opsiB,
                    'C' => $opsiC,
                    'D' => $opsiD,
                    'E' => $opsiE,
                ];

                foreach ($rawOptions as $key => $teks) {
                    if ($teks !== '') {
                        $isBenar = ($jawabanBenar === $key || strtolower($jawabanBenar) === strtolower($teks)) ? 1 : 0;
                        $pilihanArray[] = [
                            'teks' => $teks,
                            'is_benar' => $isBenar
                        ];
                    }
                }

                if (!empty($pilihanArray)) {
                    $hasCorrect = false;
                    foreach ($pilihanArray as $p) {
                        if ($p['is_benar'] == 1) { $hasCorrect = true; break; }
                    }
                    if (!$hasCorrect) {
                        $pilihanArray[0]['is_benar'] = 1;
                    }
                }
            } elseif ($jenisSoal === 'tf') {
                $opsiA = trim($data[$opsiA_Idx] ?? '');
                $opsiB = trim($data[$opsiB_Idx] ?? '');
                if (empty($opsiA)) $opsiA = 'Benar';
                if (empty($opsiB)) $opsiB = 'Salah';

                $jawabanBenar = strtoupper(trim($data[$jawabIdx] ?? 'A'));
                $isA_Correct = ($jawabanBenar === 'A' || $jawabanBenar === 'BENAR' || $jawabanBenar === 'TRUE' || $jawabanBenar === '1');

                $pilihanArray = [
                    ['teks' => $opsiA, 'is_benar' => $isA_Correct ? 1 : 0],
                    ['teks' => $opsiB, 'is_benar' => $isA_Correct ? 0 : 1]
                ];
            }

            $soalId = $this->addSoal($quizId, $jenisSoal, $pertanyaan, $bobot, $pilihanArray, $finalGambarName);
            if ($soalId) {
                $importedCount++;
            }
        }

        fclose($handle);

        // Sync total question count in quiz table
        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM soal WHERE quiz_id = ?");
        $stmtCount->execute([$quizId]);
        $totalSoal = (int)$stmtCount->fetchColumn();

        $stmtUp = $this->db->prepare("UPDATE quiz SET jumlah_soal = ? WHERE id = ?");
        $stmtUp->execute([$totalSoal, $quizId]);

        return [
            'status' => true,
            'message' => "Berhasil meng-import {$importedCount} soal (PG, Essay, True/False, beserta Gambar) dari Excel ke kuis ini.",
            'imported' => $importedCount,
            'total_soal' => $totalSoal
        ];
    }

    public function getSoalByQuiz($quiz_id, $random = false) {
        $sql = "SELECT * FROM soal WHERE quiz_id = " . (int)$quiz_id;
        if ($random) {
            $sql .= " ORDER BY RAND()";
        }
        $soals = $this->db->query($sql)->fetchAll();

        foreach ($soals as &$s) {
            if ($s['jenis_soal'] === 'pg' || $s['jenis_soal'] === 'tf') {
                $stmtPil = $this->db->prepare("SELECT * FROM pilihan_jawaban WHERE soal_id = ? " . ($random ? "ORDER BY RAND()" : "ORDER BY id ASC"));
                $stmtPil->execute([$s['id']]);
                $s['pilihan'] = $stmtPil->fetchAll();
            }
        }
        return $soals;
    }

    public function submitAnswer($siswa_id, $quiz_id, $soal_id, $pilihan_id = null, $essay = null) {
        $is_benar = 0;
        $nilai = 0;

        if ($pilihan_id) {
            $stmtPil = $this->db->prepare("SELECT is_benar FROM pilihan_jawaban WHERE id = ?");
            $stmtPil->execute([$pilihan_id]);
            $pil = $stmtPil->fetch();

            if ($pil && $pil['is_benar'] == 1) {
                $is_benar = 1;
                $stmtSoal = $this->db->prepare("SELECT bobot FROM soal WHERE id = ?");
                $stmtSoal->execute([$soal_id]);
                $s = $stmtSoal->fetch();
                $nilai = $s['bobot'] ?? 10;
            }
        }

        $stmtExist = $this->db->prepare("SELECT id FROM jawaban_siswa WHERE siswa_id = ? AND quiz_id = ? AND soal_id = ?");
        $stmtExist->execute([$siswa_id, $quiz_id, $soal_id]);
        $exist = $stmtExist->fetch();

        if ($exist) {
            $stmt = $this->db->prepare("UPDATE jawaban_siswa SET pilihan_id = ?, teks_jawaban_essay = ?, is_benar = ?, nilai = ? WHERE id = ?");
            return $stmt->execute([$pilihan_id, $essay, $is_benar, $nilai, $exist['id']]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO jawaban_siswa (siswa_id, quiz_id, soal_id, pilihan_id, teks_jawaban_essay, is_benar, nilai) VALUES (?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$siswa_id, $quiz_id, $soal_id, $pilihan_id, $essay, $is_benar, $nilai]);
        }
    }

    public function finishQuiz($siswa_id, $quiz_id, $isNewAttempt = true) {
        $stmtScore = $this->db->prepare("SELECT SUM(COALESCE(nilai, 0)) FROM jawaban_siswa WHERE siswa_id = ? AND quiz_id = ?");
        $stmtScore->execute([$siswa_id, $quiz_id]);
        $earnedScore = (float)$stmtScore->fetchColumn();

        // Calculate total max weight of questions in this quiz
        $stmtTotalBobot = $this->db->prepare("SELECT SUM(bobot) FROM soal WHERE quiz_id = ?");
        $stmtTotalBobot->execute([$quiz_id]);
        $totalMaxBobot = (float)$stmtTotalBobot->fetchColumn();

        if ($totalMaxBobot > 0) {
            $currentScore = round(($earnedScore / $totalMaxBobot) * 100, 2);
        } else {
            $currentScore = 0.00;
        }

        // Check if quiz has ungraded essay questions
        $stmtUngraded = $this->db->prepare("
            SELECT COUNT(*) FROM soal s
            LEFT JOIN jawaban_siswa js ON js.soal_id = s.id AND js.siswa_id = ? AND js.quiz_id = ?
            WHERE s.quiz_id = ? AND s.jenis_soal = 'essay' AND (js.nilai IS NULL OR js.id IS NULL)
        ");
        $stmtUngraded->execute([$siswa_id, $quiz_id, $quiz_id]);
        $hasUngradedEssay = ((int)$stmtUngraded->fetchColumn()) > 0;

        $statusLulus = $hasUngradedEssay ? 'menunggu' : (($currentScore >= 70) ? 'lulus' : 'tidak_lulus');

        if ($isNewAttempt) {
            // Count previous attempts in history
            $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM hasil_quiz_history WHERE siswa_id = ? AND quiz_id = ?");
            $stmtCount->execute([$siswa_id, $quiz_id]);
            $attemptNum = ((int)$stmtCount->fetchColumn()) + 1;

            // Log this attempt in history table
            $stmtHist = $this->db->prepare("INSERT INTO hasil_quiz_history (siswa_id, quiz_id, attempt_number, total_nilai, status_lulus) VALUES (?, ?, ?, ?, ?)");
            $stmtHist->execute([$siswa_id, $quiz_id, $attemptNum, $currentScore, $statusLulus]);
        } else {
            // Update latest attempt in history table if exists
            $stmtLastHist = $this->db->prepare("SELECT id FROM hasil_quiz_history WHERE siswa_id = ? AND quiz_id = ? ORDER BY id DESC LIMIT 1");
            $stmtLastHist->execute([$siswa_id, $quiz_id]);
            $lastHistId = $stmtLastHist->fetchColumn();

            if ($lastHistId) {
                $stmtUpdateHist = $this->db->prepare("UPDATE hasil_quiz_history SET total_nilai = ?, status_lulus = ? WHERE id = ?");
                $stmtUpdateHist->execute([$currentScore, $statusLulus, $lastHistId]);
            }

            $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM hasil_quiz_history WHERE siswa_id = ? AND quiz_id = ?");
            $stmtCount->execute([$siswa_id, $quiz_id]);
            $attemptNum = (int)$stmtCount->fetchColumn();
            if ($attemptNum == 0) $attemptNum = 1;
        }

        // Get highest score achieved across all attempts
        $stmtMax = $this->db->prepare("SELECT MAX(total_nilai) FROM hasil_quiz_history WHERE siswa_id = ? AND quiz_id = ?");
        $stmtMax->execute([$siswa_id, $quiz_id]);
        $highestScore = $stmtMax->fetchColumn();
        $highestScore = ($highestScore !== false && $highestScore !== null) ? (float)$highestScore : $currentScore;

        if ($highestScore < $currentScore) {
            $highestScore = $currentScore;
        }

        $statusLulusHighest = $hasUngradedEssay ? 'menunggu' : (($highestScore >= 70) ? 'lulus' : 'tidak_lulus');

        $stmtExist = $this->db->prepare("SELECT id, attempt_count FROM hasil_quiz WHERE siswa_id = ? AND quiz_id = ?");
        $stmtExist->execute([$siswa_id, $quiz_id]);
        $exist = $stmtExist->fetch();

        if ($exist) {
            $stmt = $this->db->prepare("UPDATE hasil_quiz SET total_nilai = ?, nilai_tertinggi = ?, attempt_count = ?, status_lulus = ?, finished_at = NOW() WHERE id = ?");
            $stmt->execute([$highestScore, $highestScore, $attemptNum, $statusLulusHighest, $exist['id']]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO hasil_quiz (siswa_id, quiz_id, total_nilai, nilai_tertinggi, attempt_count, status_lulus, finished_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$siswa_id, $quiz_id, $highestScore, $highestScore, $attemptNum, $statusLulusHighest]);
        }

        // Sync Highest Score to E-Rapor nilairapor if not pending
        if (!$hasUngradedEssay) {
            try {
                $stmtQ = $this->db->prepare("SELECT mapel_id FROM quiz WHERE id = ?");
                $stmtQ->execute([$quiz_id]);
                $qRow = $stmtQ->fetch();
                if ($qRow) {
                    require_once ROOT_PATH . 'models/NilaiModel.php';
                    $nilaiModel = new NilaiModel();
                    $nilaiModel->syncSiswaMapelNilai($siswa_id, $qRow['mapel_id']);
                }
            } catch (Exception $e) {}
        }

        return $highestScore;
    }

    public function recalculateQuizScore($siswa_id, $quiz_id) {
        return $this->finishQuiz($siswa_id, $quiz_id, false);
    }

    public function recalibrateAllQuizScores() {
        try {
            $stmt = $this->db->query("SELECT DISTINCT siswa_id, quiz_id FROM hasil_quiz");
            $rows = $stmt->fetchAll();
            foreach ($rows as $r) {
                $this->recalculateQuizScore((int)$r['siswa_id'], (int)$r['quiz_id']);
            }
        } catch (Exception $e) {}
    }

    public function getHasilQuizListByGuru($guruId = null) {
        // Auto-heal any stale 'menunggu' status in database where ungraded_essay_count is 0
        try {
            $this->db->exec("
                UPDATE hasil_quiz hq
                SET hq.status_lulus = IF(hq.total_nilai >= 70, 'lulus', 'tidak_lulus')
                WHERE hq.status_lulus = 'menunggu'
                AND NOT EXISTS (
                    SELECT 1 FROM soal s 
                    LEFT JOIN jawaban_siswa js ON js.soal_id = s.id AND js.siswa_id = hq.siswa_id AND js.quiz_id = hq.quiz_id
                    WHERE s.quiz_id = hq.quiz_id AND s.jenis_soal = 'essay' AND (js.nilai IS NULL OR js.id IS NULL)
                )
            ");
        } catch (Exception $e) {}

        $sql = "
            SELECT hq.*, q.judul as nama_quiz, map.nama_mapel, k.nama_kelas, jur.nama_jurusan, s.nama_lengkap as nama_siswa, s.nis,
            (SELECT COUNT(*) FROM soal s2 WHERE s2.quiz_id = hq.quiz_id AND s2.jenis_soal = 'essay') as total_essay_count,
            (SELECT COUNT(*) FROM soal s2 LEFT JOIN jawaban_siswa js2 ON js2.soal_id = s2.id AND js2.siswa_id = hq.siswa_id AND js2.quiz_id = hq.quiz_id WHERE s2.quiz_id = hq.quiz_id AND s2.jenis_soal = 'essay' AND (js2.nilai IS NULL OR js2.id IS NULL)) as ungraded_essay_count,
            (SELECT COUNT(*) FROM soal s2 JOIN jawaban_siswa js2 ON js2.soal_id = s2.id AND js2.siswa_id = hq.siswa_id AND js2.quiz_id = hq.quiz_id WHERE s2.quiz_id = hq.quiz_id AND s2.jenis_soal = 'essay' AND js2.nilai IS NOT NULL) as graded_essay_count
            FROM hasil_quiz hq
            INNER JOIN (
                SELECT MAX(id) as max_id
                FROM hasil_quiz
                GROUP BY siswa_id, quiz_id
            ) latest_hq ON hq.id = latest_hq.max_id
            JOIN quiz q ON hq.quiz_id = q.id
            JOIN mata_pelajaran map ON q.mapel_id = map.id
            JOIN siswa s ON hq.siswa_id = s.id
            LEFT JOIN kelas k ON (s.kelas_id = k.id OR q.kelas_id = k.id)
            LEFT JOIN jurusan jur ON (s.jurusan_id = jur.id OR k.jurusan_id = jur.id)
        ";
        if ($guruId !== null) {
            $sql .= " WHERE q.guru_id = " . (int)$guruId;
        }
        $sql .= " ORDER BY hq.finished_at DESC, hq.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getEssayAnswersByHasil($quizId, $siswaId) {
        $stmt = $this->db->prepare("
            SELECT s.id as soal_id, s.pertanyaan, s.bobot, js.id as jawaban_id, js.teks_jawaban_essay, js.nilai
            FROM soal s
            LEFT JOIN jawaban_siswa js ON js.soal_id = s.id AND js.siswa_id = ? AND js.quiz_id = ?
            WHERE s.quiz_id = ? AND s.jenis_soal = 'essay'
            ORDER BY s.id ASC
        ");
        $stmt->execute([(int)$siswaId, (int)$quizId, (int)$quizId]);
        return $stmt->fetchAll();
    }

    // --- UJIAN CBT ---
    public function getUjianCBT($kelas_id = null) {
        $sql = "
            SELECT u.*, map.nama_mapel, k.nama_kelas, g.nama_lengkap as nama_guru
            FROM ujian u
            JOIN mata_pelajaran map ON u.mapel_id = map.id
            JOIN kelas k ON u.kelas_id = k.id
            JOIN guru g ON u.guru_id = g.id
            WHERE u.is_active = 1
        ";
        if ($kelas_id) {
            $sql .= " AND u.kelas_id = " . (int)$kelas_id;
        }
        $sql .= " ORDER BY u.tgl_mulai DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getReviewQuiz($quiz_id, $siswa_id) {
        $quizInfo = $this->getQuizById($quiz_id);
        if (!$quizInfo) return null;

        $soals = $this->getSoalByQuiz($quiz_id, false);

        $stmtAns = $this->db->prepare("SELECT * FROM jawaban_siswa WHERE quiz_id = ? AND siswa_id = ?");
        $stmtAns->execute([(int)$quiz_id, (int)$siswa_id]);
        $jawabanList = $stmtAns->fetchAll();

        $jawabanMap = [];
        foreach ($jawabanList as $j) {
            $jawabanMap[$j['soal_id']] = $j;
        }

        foreach ($soals as &$s) {
            $s['jawaban_siswa'] = $jawabanMap[$s['id']] ?? null;
        }

        $stmtResult = $this->db->prepare("SELECT * FROM hasil_quiz WHERE quiz_id = ? AND siswa_id = ?");
        $stmtResult->execute([(int)$quiz_id, (int)$siswa_id]);
        $hasilQuiz = $stmtResult->fetch();

        return [
            'quiz' => $quizInfo,
            'soal' => $soals,
            'hasil' => $hasilQuiz
        ];
    }
}
