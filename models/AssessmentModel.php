<?php
/**
 * AssessmentModel
 * Mengelola arsitektur CP -> TP -> KKTP -> ASESMEN -> NILAI -> STATUS KETERCAPAIAN (1/0)
 * Mendukung Multi-TP, Remedial dengan histori nilai, Rekap Kelas & Siswa, serta Integrasi E-Rapor.
 */

require_once ROOT_PATH . 'models/BaseModel.php';

class AssessmentModel extends BaseModel {

    // =========================================================================
    // 1. KKTP (KRITERIA KETERCAPAIAN TUJUAN PEMBELAJARAN)
    // =========================================================================

    /**
     * Ekstrak indikator KKTP secara otomatis dari rumusan deskripsi TP
     * Membaca format butir: angka (1., 2.), huruf (a., b.), tanda hubung (-), bullet (•), centang (✓), panah (→),
     * atau merumuskan tahapan kompetensi dari materi pokok dan elemen CP jika berupa teks paragraf.
     */
    public function parseIndicatorsFromTpDescription($tpDesc, $materiPokok = '', $cpElemen = '') {
        $indicators = [];
        $lines = preg_split('/\r\n|\r|\n/', trim((string)$tpDesc));
        $idx = 1;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Pattern: 1. or 1) or a. or A. or - or • or * or ✓ or → or +
            if (preg_match('/^(\d+[\.\)\-]|[a-zA-Z][\.\)]|[•\-\*✓✔☑▪▫►▶→➔➢+~–—\x{2022}\x{25AA}\x{2713}\x{2714}])\s*(.*)$/u', $line, $m)) {
                $content = trim($m[2]);
                if (!empty($content)) {
                    $namaPendek = mb_strlen($content) > 65 ? mb_substr($content, 0, 62) . '...' : $content;
                    $indicators[] = [
                        'nama_indikator' => "Indikator {$idx}: {$namaPendek}",
                        'deskripsi_kriteria' => $content,
                        'bobot' => 1.00,
                        'urutan' => $idx
                    ];
                    $idx++;
                }
            }
        }

        // Jika tidak ditemukan butir-butir bernomor/bullet, coba pisahkan berdasarkan titik koma (;) atau titik (.)
        if (empty($indicators)) {
            $sentences = preg_split('/(?<=[;])\s*|(?<=[.])\s+(?=[A-Z0-9])/', trim((string)$tpDesc));
            $validSentences = [];
            foreach ($sentences as $s) {
                $s = trim($s, " \t\n\r\0\x0B;.");
                if (mb_strlen($s) >= 12) {
                    $validSentences[] = $s;
                }
            }

            if (count($validSentences) >= 2) {
                foreach ($validSentences as $s) {
                    $namaPendek = mb_strlen($s) > 65 ? mb_substr($s, 0, 62) . '...' : $s;
                    $indicators[] = [
                        'nama_indikator' => "Indikator {$idx}: {$namaPendek}",
                        'deskripsi_kriteria' => $s,
                        'bobot' => 1.00,
                        'urutan' => $idx
                    ];
                    $idx++;
                }
            } else {
                // Fallback: Bentuk 2-3 indikator capaian bertahap berbasis topik materi & TP
                $topik = !empty($materiPokok) ? $materiPokok : (!empty($cpElemen) ? $cpElemen : 'materi pokok');
                $indicators[] = [
                    'nama_indikator' => "Indikator 1: Pemahaman Konsep {$topik}",
                    'deskripsi_kriteria' => "Mampu mengidentifikasi, menjelaskan, dan memahami prinsip dasar {$topik}.",
                    'bobot' => 1.00,
                    'urutan' => 1
                ];
                $indicators[] = [
                    'nama_indikator' => "Indikator 2: Penerapan & Praktik {$topik}",
                    'deskripsi_kriteria' => "Mampu mengaplikasikan konsep dan menyelesaikan tugas/studi kasus {$topik}.",
                    'bobot' => 1.00,
                    'urutan' => 2
                ];
                if (!empty($tpDesc) && mb_strlen($tpDesc) > 30) {
                    $namaRingkas = mb_strlen($tpDesc) > 65 ? mb_substr($tpDesc, 0, 62) . '...' : $tpDesc;
                    $indicators[] = [
                        'nama_indikator' => "Indikator 3: Penguasaan Target Kompetensi",
                        'deskripsi_kriteria' => $tpDesc,
                        'bobot' => 1.00,
                        'urutan' => 3
                    ];
                }
            }
        }

        return $indicators;
    }

    /**
     * Generate data awal KKTP (kriteria, rubrik, indikator) secara otomatis dari CP & TP
     */
    public function generateDefaultKktpDataFromTp($tpId) {
        $tpId = (int)$tpId;
        $stmt = $this->db->prepare("
            SELECT tp.*, 
                   cp.kode_cp, cp.elemen AS cp_elemen, cp.deskripsi AS cp_deskripsi,
                   m.nama_mapel
            FROM tujuan_pembelajaran tp
            JOIN capaian_pembelajaran cp ON tp.cp_id = cp.id
            LEFT JOIN mata_pelajaran m ON cp.mapel_id = m.id
            WHERE tp.id = ?
        ");
        $stmt->execute([$tpId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [
                'id' => null,
                'tp_id' => $tpId,
                'metode' => 'interval_nilai',
                'nilai_minimum' => 75.00,
                'target_indikator_count' => 0,
                'deskripsi_kriteria' => 'Batas Ketercapaian Minimum 75.00',
                'rubrik_deskripsi' => '',
                'indikator' => []
            ];
        }

        $tpDesc = trim($row['deskripsi'] ?? '');
        $materi = trim($row['materi_pokok'] ?? '');
        $cpElemen = trim($row['cp_elemen'] ?? '');
        $cpKode = trim($row['kode_cp'] ?? '');
        $mapel = trim($row['nama_mapel'] ?? '');

        // 1. Ekstrak indikator dari rumusan TP
        $indicators = $this->parseIndicatorsFromTpDescription($tpDesc, $materi, $cpElemen);

        // 2. Susun rumusan deskripsi kriteria ketercapaian dari CP & TP
        $deskripsiKriteria = '';
        if (!empty($materi) && !empty($cpElemen)) {
            $deskripsiKriteria = "Peserta didik mencapai ketuntasan materi '{$materi}' pada elemen '{$cpElemen}' dengan penguasaan minimal 75%.";
        } elseif (!empty($materi)) {
            $deskripsiKriteria = "Peserta didik mencapai ketuntasan materi '{$materi}' dengan penguasaan kompetensi minimal 75%.";
        } elseif (!empty($cpElemen)) {
            $deskripsiKriteria = "Peserta didik mencapai ketuntasan kompetensi elemen '{$cpElemen}' dengan ketuntasan minimal 75%.";
        } else {
            $firstSentence = preg_split('/[\.\r\n]/', $tpDesc)[0] ?? $tpDesc;
            $firstSentence = trim(preg_replace('/^(\d+[\.\)\-]|[a-zA-Z][\.\)]|[•\-\*✓✔☑▪▫►▶→➔➢+~–—\x{2022}\x{25AA}\x{2713}\x{2714}])\s*/u', '', $firstSentence));
            $cleanSnippet = mb_strlen($firstSentence) > 110 ? mb_substr($firstSentence, 0, 107) . '...' : $firstSentence;
            $deskripsiKriteria = "Ketuntasan minimal ketercapaian kompetensi: {$cleanSnippet} (Ambang 75.00)";
        }

        // 3. Susun rubrik berjenjang operasional berdasarkan materi / TP
        $topik = !empty($materi) ? $materi : (!empty($cpElemen) ? $cpElemen : 'materi pokok');
        $rubrikDeskripsi = "- Mahir (>= 85): Menguasai seluruh kriteria {$topik} secara mandiri, akurat, dan mampu mengaplikasikan pada tugas kompleks.\n"
                         . "- Cakap (75 - 84): Menguasai kriteria utama {$topik} secara mandiri dan memenuhi standar ketercapaian pembelajaran.\n"
                         . "- Layak (65 - 74): Memahami sebagian konsep {$topik}, namun masih membutuhkan pendampingan pada bagian tertentu.\n"
                         . "- Perlu Bimbingan (< 65): Belum memenuhi batas minimum kompetensi {$topik} dan memerlukan remedial terstruktur.";

        $totalInd = count($indicators);
        $targetCount = $totalInd > 0 ? ($totalInd >= 3 ? $totalInd - 1 : $totalInd) : 2;

        return [
            'id' => null,
            'tp_id' => $tpId,
            'kode_tp' => $row['kode_tp'] ?? '',
            'materi_pokok' => $materi,
            'tp_deskripsi' => $tpDesc,
            'cp_id' => $row['cp_id'] ?? null,
            'cp_kode' => $cpKode,
            'cp_elemen' => $cpElemen,
            'cp_deskripsi' => $row['cp_deskripsi'] ?? '',
            'nama_mapel' => $mapel,
            'metode' => 'interval_nilai',
            'nilai_minimum' => 75.00,
            'target_indikator_count' => $targetCount,
            'deskripsi_kriteria' => $deskripsiKriteria,
            'rubrik_deskripsi' => $rubrikDeskripsi,
            'versi' => 1,
            'status' => 'aktif',
            'indikator' => $indicators,
            'is_auto_derived' => true
        ];
    }

    /**
     * Ambil data KKTP aktif untuk suatu TP
     * Jika belum pernah disimpan atau indikator kosong, perkaya dengan data turunan otomatis dari CP & TP
     */
    public function getKktpByTp($tpId) {
        $tpId = (int)$tpId;
        $stmt = $this->db->prepare("
            SELECT * FROM kktp 
            WHERE tp_id = ? AND status = 'aktif' 
            ORDER BY versi DESC, id DESC LIMIT 1
        ");
        $stmt->execute([$tpId]);
        $kktp = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ambil info konteks TP dan CP
        $stmtTp = $this->db->prepare("
            SELECT tp.*, 
                   cp.kode_cp, cp.elemen AS cp_elemen, cp.deskripsi AS cp_deskripsi,
                   m.nama_mapel
            FROM tujuan_pembelajaran tp
            JOIN capaian_pembelajaran cp ON tp.cp_id = cp.id
            LEFT JOIN mata_pelajaran m ON cp.mapel_id = m.id
            WHERE tp.id = ?
        ");
        $stmtTp->execute([$tpId]);
        $tpInfo = $stmtTp->fetch(PDO::FETCH_ASSOC);

        if (!$kktp) {
            // Belum ada konfigurasi KKTP, buatkan payload turunan otomatis dari TP dan CP
            return $this->generateDefaultKktpDataFromTp($tpId);
        }

        // Ambil indikator tersimpan jika ada
        $stmtInd = $this->db->prepare("
            SELECT * FROM kktp_indikator 
            WHERE kktp_id = ? 
            ORDER BY urutan ASC, id ASC
        ");
        $stmtInd->execute([(int)$kktp['id']]);
        $kktp['indikator'] = $stmtInd->fetchAll(PDO::FETCH_ASSOC);

        // Pasangkan info konteks CP & TP
        if ($tpInfo) {
            $kktp['kode_tp'] = $tpInfo['kode_tp'] ?? '';
            $kktp['materi_pokok'] = $tpInfo['materi_pokok'] ?? '';
            $kktp['tp_deskripsi'] = $tpInfo['deskripsi'] ?? '';
            $kktp['cp_id'] = $tpInfo['cp_id'] ?? null;
            $kktp['cp_kode'] = $tpInfo['kode_cp'] ?? '';
            $kktp['cp_elemen'] = $tpInfo['cp_elemen'] ?? '';
            $kktp['cp_deskripsi'] = $tpInfo['cp_deskripsi'] ?? '';
            $kktp['nama_mapel'] = $tpInfo['nama_mapel'] ?? '';
        }

        // Jika indikator tersimpan masih kosong, sertakan auto_indicators dari turunan TP
        $autoData = $this->generateDefaultKktpDataFromTp($tpId);
        $kktp['auto_indicators'] = $autoData['indikator'] ?? [];
        if (empty($kktp['deskripsi_kriteria']) || $kktp['deskripsi_kriteria'] === 'Batas Ketercapaian Minimum 75.00') {
            $kktp['auto_deskripsi_kriteria'] = $autoData['deskripsi_kriteria'] ?? '';
        }
        $kktp['auto_rubrik_deskripsi'] = $autoData['rubrik_deskripsi'] ?? '';

        return $kktp;
    }

    /**
     * Otomatis inisialisasi / seeder KKTP dan indikatornya saat TP baru dibuat
     */
    public function autoSeedKktpForTp($tpId) {
        $tpId = (int)$tpId;
        $autoData = $this->generateDefaultKktpDataFromTp($tpId);
        return $this->saveKktp($tpId, [
            'metode' => 'interval_nilai',
            'nilai_minimum' => 75.00,
            'target_indikator_count' => $autoData['target_indikator_count'] ?? 0,
            'deskripsi_kriteria' => $autoData['deskripsi_kriteria'] ?? 'Batas Ketercapaian Minimum 75.00',
            'indikator' => $autoData['indikator'] ?? []
        ]);
    }

    /**
     * Ambil data KKTP berdasarkan ID langsung (bisa versi lama/arsip untuk histori)
     */
    public function getKktpById($kktpId) {
        $stmt = $this->db->prepare("SELECT * FROM kktp WHERE id = ?");
        $stmt->execute([(int)$kktpId]);
        $kktp = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$kktp) return null;

        $stmtInd = $this->db->prepare("SELECT * FROM kktp_indikator WHERE kktp_id = ? ORDER BY urutan ASC, id ASC");
        $stmtInd->execute([(int)$kktp['id']]);
        $kktp['indikator'] = $stmtInd->fetchAll(PDO::FETCH_ASSOC);

        return $kktp;
    }

    /**
     * Simpan / Perbarui KKTP untuk suatu TP
     * Jika KKTP sudah digunakan dalam asesmen/nilai yang tersimpan, buat versi baru (arsip versi lama)
     * untuk menjaga integritas data histori pembelajaran.
     */
    public function saveKktp($tpId, $data) {
        $tpId = (int)$tpId;
        $metode = in_array($data['metode'] ?? '', ['interval_nilai', 'skala_nilai', 'rubrik', 'checklist']) ? $data['metode'] : 'interval_nilai';
        $nilaiMinimum = floatval($data['nilai_minimum'] ?? 75.00);
        $targetIndikator = (int)($data['target_indikator_count'] ?? 0);
        $deskripsi = trim($data['deskripsi_kriteria'] ?? '');
        $indikatorList = $data['indikator'] ?? [];

        // Cek KKTP aktif saat ini
        $stmtCur = $this->db->prepare("SELECT * FROM kktp WHERE tp_id = ? AND status = 'aktif' ORDER BY versi DESC LIMIT 1");
        $stmtCur->execute([$tpId]);
        $current = $stmtCur->fetch(PDO::FETCH_ASSOC);

        $kktpId = null;
        if ($current) {
            // Cek apakah KKTP ini sudah pernah dipakai di asesmen atau nilai_asesmen_tp
            $chkUsage = $this->db->prepare("
                SELECT COUNT(*) FROM nilai_asesmen_tp WHERE kktp_id = ? 
                UNION ALL 
                SELECT COUNT(*) FROM asesmen_tp WHERE kktp_id = ?
            ");
            $chkUsage->execute([(int)$current['id'], (int)$current['id']]);
            $counts = $chkUsage->fetchAll(PDO::FETCH_COLUMN);
            $isUsed = array_sum($counts) > 0;

            if ($isUsed) {
                // Arsipkan KKTP lama untuk melindungi histori penilaian
                $this->db->prepare("UPDATE kktp SET status = 'arsip' WHERE id = ?")->execute([(int)$current['id']]);
                
                // Buat versi baru
                $newVersi = (int)$current['versi'] + 1;
                $stmtIns = $this->db->prepare("
                    INSERT INTO kktp (tp_id, metode, nilai_minimum, target_indikator_count, deskripsi_kriteria, versi, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'aktif')
                ");
                $stmtIns->execute([$tpId, $metode, $nilaiMinimum, $targetIndikator, $deskripsi, $newVersi]);
                $kktpId = (int)$this->db->lastInsertId();
            } else {
                // Belum pernah dipakai penilaian, update langsung
                $stmtUpd = $this->db->prepare("
                    UPDATE kktp 
                    SET metode = ?, nilai_minimum = ?, target_indikator_count = ?, deskripsi_kriteria = ?
                    WHERE id = ?
                ");
                $stmtUpd->execute([$metode, $nilaiMinimum, $targetIndikator, $deskripsi, (int)$current['id']]);
                $kktpId = (int)$current['id'];

                // Hapus indikator lama sebelum simpan ulang
                $this->db->prepare("DELETE FROM kktp_indikator WHERE kktp_id = ?")->execute([$kktpId]);
            }
        } else {
            // Belum ada, insert baru
            $stmtIns = $this->db->prepare("
                INSERT INTO kktp (tp_id, metode, nilai_minimum, target_indikator_count, deskripsi_kriteria, versi, status)
                VALUES (?, ?, ?, ?, ?, 1, 'aktif')
            ");
            $stmtIns->execute([$tpId, $metode, $nilaiMinimum, $targetIndikator, $deskripsi]);
            $kktpId = (int)$this->db->lastInsertId();
        }

        // Simpan indikator jika ada (terutama untuk checklist / rubrik)
        if ($kktpId && !empty($indikatorList) && is_array($indikatorList)) {
            $stmtInd = $this->db->prepare("
                INSERT INTO kktp_indikator (kktp_id, nama_indikator, deskripsi_kriteria, bobot, urutan)
                VALUES (?, ?, ?, ?, ?)
            ");
            $urutan = 1;
            foreach ($indikatorList as $ind) {
                $nama = trim(is_array($ind) ? ($ind['nama_indikator'] ?? '') : (string)$ind);
                if (!empty($nama)) {
                    $desc = is_array($ind) ? trim($ind['deskripsi_kriteria'] ?? '') : '';
                    $bobot = is_array($ind) ? floatval($ind['bobot'] ?? 1.00) : 1.00;
                    $stmtInd->execute([$kktpId, $nama, $desc, $bobot, $urutan++]);
                }
            }
        }

        return [
            'status' => true,
            'kktp_id' => $kktpId,
            'message' => 'Konfigurasi KKTP berhasil disimpan.'
        ];
    }

    /**
     * Evaluasi ketercapaian secara dinamis berdasarkan konfigurasi KKTP
     * TIDAK BOLEH hardcoded ($nilai >= 75)!
     * Mengembalikan status_code (1 atau 0) dan status_ketercapaian ('Tercapai' / 'Belum Tercapai')
     */
    public function evaluateKetercapaian($kktp, $scoreOrInput) {
        $metode = $kktp['metode'] ?? 'interval_nilai';
        if ($metode === 'skala_nilai') $metode = 'interval_nilai';
        $status = 0;
        $keterangan = '';

        if ($metode === 'interval_nilai') {
            $score = floatval(is_array($scoreOrInput) ? ($scoreOrInput['nilai'] ?? 0) : $scoreOrInput);
            $minScore = floatval($kktp['nilai_minimum'] ?? 75.00);
            $status = ($score >= $minScore) ? 1 : 0;
            $keterangan = $status ? "Nilai {$score} memenuhi ambang batas ({$minScore})" : "Nilai {$score} di bawah ambang batas ({$minScore})";
        } 
        elseif ($metode === 'rubrik') {
            // Rubrik berjenjang: skor angka atau bobot jenjang
            $score = floatval(is_array($scoreOrInput) ? ($scoreOrInput['nilai'] ?? 0) : $scoreOrInput);
            $minScore = floatval($kktp['nilai_minimum'] ?? 75.00);
            $status = ($score >= $minScore) ? 1 : 0;
            $keterangan = $status ? "Skor rubrik {$score} mencapai kriteria tuntas (min {$minScore})" : "Skor rubrik {$score} belum mencapai kriteria tuntas (min {$minScore})";
        } 
        elseif ($metode === 'checklist') {
            // Checklist indikator: menghitung jumlah indikator yang dicentang
            $targetCount = (int)($kktp['target_indikator_count'] ?? 0);
            if ($targetCount <= 0 && !empty($kktp['indikator'])) {
                $targetCount = count($kktp['indikator']);
            }
            if ($targetCount <= 0) $targetCount = 1;

            $checkedList = [];
            if (is_array($scoreOrInput) && isset($scoreOrInput['checked_indicators'])) {
                $checkedList = (array)$scoreOrInput['checked_indicators'];
            } elseif (is_numeric($scoreOrInput)) {
                // Jika input berupa skor persentase/indikator
                $score = floatval($scoreOrInput);
                $minScore = floatval($kktp['nilai_minimum'] ?? 75.00);
                $status = ($score >= $minScore) ? 1 : 0;
                $keterangan = $status ? "Tercapai berdasarkan checklist ({$score}%)" : "Belum tercapai ({$score}%)";
                return [
                    'status_code' => $status,
                    'status_ketercapaian' => $status ? 'Tercapai' : 'Belum Tercapai',
                    'keterangan' => $keterangan,
                    'metode' => $metode
                ];
            }

            $countAchieved = count($checkedList);
            $status = ($countAchieved >= $targetCount) ? 1 : 0;
            $keterangan = "Tercapai {$countAchieved} dari target {$targetCount} indikator";
        }

        return [
            'status_code' => $status,
            'status_ketercapaian' => $status ? 'Tercapai' : 'Belum Tercapai',
            'keterangan' => $keterangan,
            'metode' => $metode
        ];
    }

    // =========================================================================
    // 2. CP & TP LIFECYCLE (ARSIP & BANK TP / COPY)
    // =========================================================================

    public function hasTpColumn($colName) {
        static $cols = null;
        if ($cols === null) {
            try {
                $cols = $this->db->query("SHOW COLUMNS FROM tujuan_pembelajaran")->fetchAll(PDO::FETCH_COLUMN);
            } catch (\Throwable $e) {
                $cols = [];
            }
        }
        return in_array($colName, $cols);
    }

    /**
     * Arsipkan Capaian Pembelajaran (CP) - aman untuk histori nilai
     */
    public function archiveCp($cpId) {
        $stmt = $this->db->prepare("UPDATE capaian_pembelajaran SET status = 'arsip' WHERE id = ?");
        $res = $stmt->execute([(int)$cpId]);
        return ['status' => (bool)$res, 'message' => 'Capaian Pembelajaran (CP) berhasil diarsipkan.'];
    }

    /**
     * Arsipkan Tujuan Pembelajaran (TP)
     */
    public function archiveTp($tpId) {
        if ($this->hasTpColumn('status')) {
            $stmt = $this->db->prepare("UPDATE tujuan_pembelajaran SET status = 'arsip' WHERE id = ?");
            $res = $stmt->execute([(int)$tpId]);
        } else {
            $res = true;
        }
        return ['status' => (bool)$res, 'message' => 'Tujuan Pembelajaran (TP) berhasil diarsipkan.'];
    }

    /**
     * Salin TP dari CP Sumber ke CP Tujuan (misal antar tahun ajaran / kelas)
     */
    public function copyTp($sourceCpId, $targetCpId, $guruId = null, $tahunAjaranId = null) {
        $sourceCpId = (int)$sourceCpId;
        $targetCpId = (int)$targetCpId;

        // Ambil TP dari source CP yang aktif
        $statusClause = $this->hasTpColumn('status') ? " AND status != 'arsip'" : "";
        $orderCol = $this->hasTpColumn('urutan') ? "urutan ASC, kode_tp ASC" : "kode_tp ASC";
        $stmt = $this->db->prepare("
            SELECT * FROM tujuan_pembelajaran 
            WHERE cp_id = ?{$statusClause}
            ORDER BY {$orderCol}
        ");
        $stmt->execute([$sourceCpId]);
        $tps = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($tps)) {
            return ['status' => false, 'message' => 'Tidak ada Tujuan Pembelajaran pada CP sumber yang dapat disalin.'];
        }

        $insertedCount = 0;
        $stmtChk = $this->db->prepare("SELECT id FROM tujuan_pembelajaran WHERE cp_id = ? AND kode_tp = ?");
        
        $fields = ["cp_id", "guru_id", "kode_tp", "materi_pokok", "deskripsi"];
        $placeholders = ["?", "?", "?", "?", "?"];
        if ($this->hasTpColumn('urutan')) { $fields[] = "urutan"; $placeholders[] = "?"; }
        if ($this->hasTpColumn('status')) { $fields[] = "status"; $placeholders[] = "'aktif'"; }
        if ($this->hasTpColumn('tahun_ajaran_id')) { $fields[] = "tahun_ajaran_id"; $placeholders[] = "?"; }
        
        $sqlIns = "INSERT INTO tujuan_pembelajaran (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmtIns = $this->db->prepare($sqlIns);

        foreach ($tps as $tp) {
            $stmtChk->execute([$targetCpId, $tp['kode_tp']]);
            if (!$stmtChk->fetch()) {
                $targetGuru = $guruId ?: $tp['guru_id'];
                $insParams = [$targetCpId, $targetGuru, $tp['kode_tp'], $tp['materi_pokok'], $tp['deskripsi']];
                if ($this->hasTpColumn('urutan')) {
                    $insParams[] = $tp['urutan'] ?? 1;
                }
                if ($this->hasTpColumn('tahun_ajaran_id')) {
                    $insParams[] = $tahunAjaranId;
                }
                $stmtIns->execute($insParams);
                $newTpId = (int)$this->db->lastInsertId();
                $insertedCount++;

                // Duplikasi KKTP juga jika ada
                $kktp = $this->getKktpByTp($tp['id']);
                if ($kktp && !empty($kktp['id'])) {
                    $this->saveKktp($newTpId, $kktp);
                }
            }
        }

        return [
            'status' => true,
            'count' => $insertedCount,
            'message' => "Berhasil menyalin {$insertedCount} Tujuan Pembelajaran ke CP target."
        ];
    }

    // =========================================================================
    // 3. ASESMEN MULTI-TP (1 Asesmen Mengukur Banyak TP)
    // =========================================================================

    /**
     * Buat Asesmen Pembelajaran yang dapat mengukur satu atau beberapa TP sekaligus
     */
    public function createAsesmenMultiTp($data, $tpIds = [], $tpWeights = []) {
        $rombelId = (int)($data['rombel_id'] ?? 0);
        $mapelId = (int)($data['mapel_id'] ?? 0);
        $guruId = (int)($data['guru_id'] ?? 0);
        $kurikulumId = (int)($data['kurikulum_id'] ?? 0);
        $tahunAjaranId = (int)($data['tahun_ajaran_id'] ?? 0);
        $semester = (int)($data['semester'] ?? 1);
        $namaAsesmen = trim($data['nama_asesmen'] ?? '');
        $jenisAsesmen = trim($data['jenis_asesmen'] ?? 'formatif');
        $tanggal = !empty($data['tanggal']) ? $data['tanggal'] : date('Y-m-d');
        $nilaiMaks = floatval($data['nilai_maksimum'] ?? 100.00);
        $bobot = floatval($data['bobot'] ?? 1.00);

        if (empty($namaAsesmen)) {
            return ['status' => false, 'message' => 'Nama asesmen wajib diisi.'];
        }
        if (empty($tpIds) || !is_array($tpIds)) {
            return ['status' => false, 'message' => 'Minimal 1 Tujuan Pembelajaran (TP) harus dipilih.'];
        }

        // Cari CP ID dari TP pertama
        $primaryTpId = (int)$tpIds[0];
        $stmtCp = $this->db->prepare("SELECT cp_id FROM tujuan_pembelajaran WHERE id = ?");
        $stmtCp->execute([$primaryTpId]);
        $primaryCpId = (int)$stmtCp->fetchColumn();

        // 1. Simpan ke tabel asesmen utama
        $refQuizId = !empty($data['ref_quiz_id']) ? (int)$data['ref_quiz_id'] : null;
        $refTugasId = !empty($data['ref_tugas_id']) ? (int)$data['ref_tugas_id'] : null;

        $stmt = $this->db->prepare("
            INSERT INTO asesmen (tahun_ajaran_id, semester, rombel_id, mapel_id, kurikulum_id, guru_id, cp_id, tp_id, jenis_asesmen, nama_asesmen, tanggal, nilai_maksimum, bobot, ref_quiz_id, ref_tugas_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tahunAjaranId,
            $semester,
            $rombelId,
            $mapelId,
            $kurikulumId,
            $guruId,
            $primaryCpId ?: null,
            $primaryTpId,
            $jenisAsesmen,
            $namaAsesmen,
            $tanggal,
            $nilaiMaks,
            $bobot,
            $refQuizId,
            $refTugasId
        ]);
        $asesmenId = (int)$this->db->lastInsertId();

        // 2. Hubungkan ke asesmen_tp untuk setiap TP yang diukur beserta snapshot kktp_id
        $stmtAtp = $this->db->prepare("
            INSERT INTO asesmen_tp (asesmen_id, tp_id, kktp_id, bobot_tp, nilai_maksimum)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($tpIds as $tId) {
            $tId = (int)$tId;
            if ($tId <= 0) continue;

            $kktp = $this->getKktpByTp($tId);
            $kktpId = !empty($kktp['id']) ? (int)$kktp['id'] : null;
            $bTp = isset($tpWeights[$tId]) ? floatval($tpWeights[$tId]) : 100.00;

            $stmtAtp->execute([$asesmenId, $tId, $kktpId, $bTp, $nilaiMaks]);
        }

        return [
            'status' => true,
            'asesmen_id' => $asesmenId,
            'message' => "Asesmen '{$namaAsesmen}' berhasil dibuat dengan " . count($tpIds) . " Tujuan Pembelajaran."
        ];
    }

    /**
     * Ambil data asesmen beserta seluruh TP yang diukurnya
     */
    public function getAsesmenById($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare("
            SELECT a.*, k.nama_kelas, k.nama_kelas as nama_rombel, k.tingkat, m.nama_mapel, g.nama_lengkap as nama_guru,
                   kur.nama as nama_kurikulum, kur.kode as kode_kurikulum,
                   q.judul as quiz_judul
            FROM asesmen a
            JOIN kelas k ON a.rombel_id = k.id
            JOIN mata_pelajaran m ON a.mapel_id = m.id
            LEFT JOIN guru g ON a.guru_id = g.id
            LEFT JOIN kurikulum kur ON a.kurikulum_id = kur.id
            LEFT JOIN quiz q ON a.ref_quiz_id = q.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $asesmen = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$asesmen) return null;

        // Ambil daftar TP yang diukur
        $stmtTp = $this->db->prepare("
            SELECT atp.*, tp.kode_tp, tp.deskripsi as deskripsi_tp, tp.materi_pokok,
                   k.metode as kktp_metode, k.nilai_minimum as kktp_nilai_min, 
                   k.target_indikator_count as kktp_target_ind, k.deskripsi_kriteria as kktp_kriteria
            FROM asesmen_tp atp
            JOIN tujuan_pembelajaran tp ON atp.tp_id = tp.id
            LEFT JOIN kktp k ON atp.kktp_id = k.id
            WHERE atp.asesmen_id = ?
            ORDER BY tp.urutan ASC, tp.kode_tp ASC
        ");
        $stmtTp->execute([$id]);
        $tps = $stmtTp->fetchAll(PDO::FETCH_ASSOC);

        // Jika tidak ada di asesmen_tp (legacy single TP), buatkan mapping dari a.tp_id
        if (empty($tps) && !empty($asesmen['tp_id'])) {
            $kktp = $this->getKktpByTp($asesmen['tp_id']);
            $stmtSingle = $this->db->prepare("SELECT kode_tp, deskripsi as deskripsi_tp, materi_pokok FROM tujuan_pembelajaran WHERE id = ?");
            $stmtSingle->execute([(int)$asesmen['tp_id']]);
            $singleTp = $stmtSingle->fetch(PDO::FETCH_ASSOC);
            if ($singleTp) {
                $tps[] = [
                    'id' => null,
                    'asesmen_id' => $id,
                    'tp_id' => (int)$asesmen['tp_id'],
                    'kktp_id' => $kktp['id'] ?? null,
                    'bobot_tp' => 100.00,
                    'nilai_maksimum' => $asesmen['nilai_maksimum'],
                    'kode_tp' => $singleTp['kode_tp'],
                    'deskripsi_tp' => $singleTp['deskripsi_tp'],
                    'materi_pokok' => $singleTp['materi_pokok'],
                    'kktp_metode' => $kktp['metode'] ?? 'interval_nilai',
                    'kktp_nilai_min' => $kktp['nilai_minimum'] ?? 75.00,
                    'kktp_target_ind' => $kktp['target_indikator_count'] ?? 0,
                    'kktp_kriteria' => $kktp['deskripsi_kriteria'] ?? ''
                ];
            }
        }

        $asesmen['tujuan_pembelajaran'] = $tps;
        return $asesmen;
    }

    /**
     * Ambil daftar asesmen dengan filter
     */
    public function getAsesmenList($filters = []) {
        $sql = "
            SELECT a.*, k.nama_kelas, k.nama_kelas as nama_rombel, k.tingkat, m.nama_mapel, g.nama_lengkap as nama_guru,
                   q.judul as quiz_judul,
                   (SELECT COUNT(*) FROM asesmen_tp atp WHERE atp.asesmen_id = a.id) as total_tp,
                   (SELECT COUNT(DISTINCT siswa_id) FROM nilai_asesmen_tp natp WHERE natp.asesmen_id = a.id) as total_siswa_dinilai
            FROM asesmen a
            JOIN kelas k ON a.rombel_id = k.id
            JOIN mata_pelajaran m ON a.mapel_id = m.id
            LEFT JOIN guru g ON a.guru_id = g.id
            LEFT JOIN quiz q ON a.ref_quiz_id = q.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['guru_id'])) {
            $sql .= " AND a.guru_id = ?";
            $params[] = (int)$filters['guru_id'];
        }
        if (!empty($filters['rombel_id'])) {
            $sql .= " AND a.rombel_id = ?";
            $params[] = (int)$filters['rombel_id'];
        }
        if (!empty($filters['mapel_id'])) {
            $sql .= " AND a.mapel_id = ?";
            $params[] = (int)$filters['mapel_id'];
        }
        if (!empty($filters['tahun_ajaran_id'])) {
            $sql .= " AND a.tahun_ajaran_id = ?";
            $params[] = (int)$filters['tahun_ajaran_id'];
        }
        if (!empty($filters['semester'])) {
            $sql .= " AND a.semester = ?";
            $params[] = (int)$filters['semester'];
        }

        $sql .= " ORDER BY a.tanggal DESC, a.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Perbarui data asesmen dan daftar TP yang diukurnya
     */
    public function updateAsesmenMultiTp($id, $data, $tpIds = [], $tpWeights = []) {
        $id = (int)$id;
        $rombelId = (int)($data['rombel_id'] ?? 0);
        $mapelId = (int)($data['mapel_id'] ?? 0);
        $namaAsesmen = trim($data['nama_asesmen'] ?? '');
        $jenisAsesmen = trim($data['jenis_asesmen'] ?? 'formatif');
        $tanggal = !empty($data['tanggal']) ? $data['tanggal'] : date('Y-m-d');
        $nilaiMaks = floatval($data['nilai_maksimum'] ?? 100.00);
        $bobot = floatval($data['bobot'] ?? 1.00);

        if ($id <= 0) {
            return ['status' => false, 'message' => 'ID Asesmen tidak valid.'];
        }
        if (empty($namaAsesmen)) {
            return ['status' => false, 'message' => 'Nama asesmen wajib diisi.'];
        }
        if (empty($tpIds) || !is_array($tpIds)) {
            return ['status' => false, 'message' => 'Minimal 1 Tujuan Pembelajaran (TP) harus dipilih.'];
        }

        $existing = $this->getAsesmenById($id);
        if (!$existing) {
            return ['status' => false, 'message' => 'Data asesmen tidak ditemukan.'];
        }

        // Cari CP ID dari TP pertama
        $primaryTpId = (int)$tpIds[0];
        $stmtCp = $this->db->prepare("SELECT cp_id FROM tujuan_pembelajaran WHERE id = ?");
        $stmtCp->execute([$primaryTpId]);
        $primaryCpId = (int)$stmtCp->fetchColumn();

        // Ambil kurikulum_id yang sesuai
        $kurikulumId = !empty($data['kurikulum_id']) ? (int)$data['kurikulum_id'] : (int)$existing['kurikulum_id'];

        try {
            $this->db->beginTransaction();

            // 1. Update tabel asesmen utama
            $refQuizId = isset($data['ref_quiz_id']) ? (!empty($data['ref_quiz_id']) ? (int)$data['ref_quiz_id'] : null) : ($existing['ref_quiz_id'] ?? null);

            $stmt = $this->db->prepare("
                UPDATE asesmen 
                SET rombel_id = ?, mapel_id = ?, kurikulum_id = ?, cp_id = ?, tp_id = ?, 
                    jenis_asesmen = ?, nama_asesmen = ?, tanggal = ?, nilai_maksimum = ?, bobot = ?, ref_quiz_id = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $rombelId ?: $existing['rombel_id'],
                $mapelId ?: $existing['mapel_id'],
                $kurikulumId,
                $primaryCpId ?: null,
                $primaryTpId,
                $jenisAsesmen,
                $namaAsesmen,
                $tanggal,
                $nilaiMaks,
                $bobot,
                $refQuizId,
                $id
            ]);

            // 2. Ambil TP yang saat ini terdaftar
            $stmtCurr = $this->db->prepare("SELECT tp_id FROM asesmen_tp WHERE asesmen_id = ?");
            $stmtCurr->execute([$id]);
            $currentTpIds = array_map('intval', $stmtCurr->fetchAll(PDO::FETCH_COLUMN));

            $targetTpIds = array_map('intval', $tpIds);

            // TPs to remove
            $toRemove = array_diff($currentTpIds, $targetTpIds);
            if (!empty($toRemove)) {
                $placeholders = implode(',', array_fill(0, count($toRemove), '?'));
                // Hapus nilai_asesmen_tp untuk TP yang di-uncheck
                $delNilai = $this->db->prepare("DELETE FROM nilai_asesmen_tp WHERE asesmen_id = ? AND tp_id IN ($placeholders)");
                $delNilai->execute(array_merge([$id], array_values($toRemove)));

                // Hapus dari asesmen_tp
                $delAtp = $this->db->prepare("DELETE FROM asesmen_tp WHERE asesmen_id = ? AND tp_id IN ($placeholders)");
                $delAtp->execute(array_merge([$id], array_values($toRemove)));
            }

            // TPs to add or update
            $stmtInsertAtp = $this->db->prepare("
                INSERT INTO asesmen_tp (asesmen_id, tp_id, kktp_id, bobot_tp, nilai_maksimum)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtUpdateAtp = $this->db->prepare("
                UPDATE asesmen_tp SET nilai_maksimum = ?, bobot_tp = ? WHERE asesmen_id = ? AND tp_id = ?
            ");

            foreach ($targetTpIds as $tId) {
                if ($tId <= 0) continue;
                $bTp = isset($tpWeights[$tId]) ? floatval($tpWeights[$tId]) : 100.00;

                if (in_array($tId, $currentTpIds)) {
                    $stmtUpdateAtp->execute([$nilaiMaks, $bTp, $id, $tId]);
                } else {
                    $kktp = $this->getKktpByTp($tId);
                    $kktpId = !empty($kktp['id']) ? (int)$kktp['id'] : null;
                    $stmtInsertAtp->execute([$id, $tId, $kktpId, $bTp, $nilaiMaks]);
                }
            }

            $this->db->commit();
            return [
                'status' => true,
                'message' => "Asesmen '{$namaAsesmen}' berhasil diperbarui beserta " . count($targetTpIds) . " Tujuan Pembelajaran."
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['status' => false, 'message' => 'Gagal memperbarui asesmen: ' . $e->getMessage()];
        }
    }

    /**
     * Hapus Asesmen beserta relasi asesmen_tp dan histori nilai_asesmen_tp secara bersih
     */
    public function deleteAsesmen($id, $guruId = null) {
        $id = (int)$id;
        if ($id <= 0) {
            return ['status' => false, 'message' => 'ID Asesmen tidak valid.'];
        }

        $queryCheck = "SELECT id, nama_asesmen, guru_id FROM asesmen WHERE id = ?";
        $paramsCheck = [$id];
        if ($guruId !== null && (int)$guruId > 0) {
            $queryCheck .= " AND guru_id = ?";
            $paramsCheck[] = (int)$guruId;
        }

        $stmt = $this->db->prepare($queryCheck);
        $stmt->execute($paramsCheck);
        $asesmen = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$asesmen) {
            return ['status' => false, 'message' => 'Asesmen tidak ditemukan atau Anda tidak memiliki akses untuk menghapusnya.'];
        }

        try {
            $this->db->beginTransaction();

            // 1. Hapus nilai siswa per TP di asesmen ini
            $delNilai = $this->db->prepare("DELETE FROM nilai_asesmen_tp WHERE asesmen_id = ?");
            $delNilai->execute([$id]);

            // 2. Hapus asesmen_tp relasi
            $delAtp = $this->db->prepare("DELETE FROM asesmen_tp WHERE asesmen_id = ?");
            $delAtp->execute([$id]);

            // 3. Hapus asesmen utama
            $delAsesmen = $this->db->prepare("DELETE FROM asesmen WHERE id = ?");
            $delAsesmen->execute([$id]);

            $this->db->commit();
            return [
                'status' => true,
                'message' => "Asesmen '" . htmlspecialchars($asesmen['nama_asesmen']) . "' berhasil dihapus secara bersih."
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['status' => false, 'message' => 'Gagal menghapus asesmen: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // 4. PENILAIAN SISWA BERBASIS TP & STATUS KETERCAPAIAN (1/0) + REMEDIAL
    // =========================================================================

    /**
     * Input atau update nilai siswa per TP untuk asesmen tertentu
     * Menyimpan nilai asli dan status ketercapaian (1 atau 0)
     * Mendukung REMEDIAL: mempertahankan nilai_awal dan menandai is_remedial = 1
     */
    public function inputNilaiSiswaPerTp($asesmenId, $tpId, $siswaId, $nilaiAsli, $isRemedial = false, $catatan = '', $extra = []) {
        $asesmenId = (int)$asesmenId;
        $tpId = (int)$tpId;
        $siswaId = (int)$siswaId;
        $nilaiAsli = floatval($nilaiAsli);

        // Ambil asesmen_tp row
        $stmtAtp = $this->db->prepare("SELECT id, kktp_id, nilai_maksimum FROM asesmen_tp WHERE asesmen_id = ? AND tp_id = ? LIMIT 1");
        $stmtAtp->execute([$asesmenId, $tpId]);
        $atp = $stmtAtp->fetch(PDO::FETCH_ASSOC);

        $asesmenTpId = $atp ? (int)$atp['id'] : null;
        $kktpId = $atp && !empty($atp['kktp_id']) ? (int)$atp['kktp_id'] : null;
        $nilaiMaks = $atp ? floatval($atp['nilai_maksimum']) : 100.00;

        // Ambil data KKTP untuk evaluasi
        $kktp = null;
        if ($kktpId) {
            $kktp = $this->getKktpById($kktpId);
        }
        if (!$kktp) {
            $kktp = $this->getKktpByTp($tpId);
            $kktpId = $kktp['id'] ?? null;
        }

        // Evaluasi status ketercapaian secara dinamis
        $evalInput = $nilaiAsli;
        if (!empty($extra['checked_indicators'])) {
            $evalInput = ['checked_indicators' => (array)$extra['checked_indicators'], 'nilai' => $nilaiAsli];
        }
        $eval = $this->evaluateKetercapaian($kktp, $evalInput);

        $statusCode = (int)$eval['status_code']; // 1 (tercapai) atau 0 (belum tercapai)
        $statusKetercapaian = $eval['status_ketercapaian']; // 'Tercapai' atau 'Belum Tercapai'
        $indikatorIdsStr = !empty($extra['checked_indicators']) ? implode(',', (array)$extra['checked_indicators']) : null;

        // Cek record nilai yang sudah ada
        $stmtCur = $this->db->prepare("
            SELECT id, nilai_asli, nilai_awal, is_remedial 
            FROM nilai_asesmen_tp 
            WHERE asesmen_id = ? AND tp_id = ? AND siswa_id = ?
            LIMIT 1
        ");
        $stmtCur->execute([$asesmenId, $tpId, $siswaId]);
        $current = $stmtCur->fetch(PDO::FETCH_ASSOC);

        if ($current) {
            $recordId = (int)$current['id'];
            if ($isRemedial) {
                // Preservasi nilai awal jika belum pernah tercatat
                $nilaiAwal = ($current['nilai_awal'] !== null) ? floatval($current['nilai_awal']) : floatval($current['nilai_asli']);
                $stmtUpd = $this->db->prepare("
                    UPDATE nilai_asesmen_tp
                    SET nilai_asli = ?, nilai_awal = ?, is_remedial = 1, 
                        status_code = ?, status_ketercapaian = ?, 
                        kktp_id = ?, indikator_tercapai_ids = ?, catatan = ?
                    WHERE id = ?
                ");
                $stmtUpd->execute([$nilaiAsli, $nilaiAwal, $statusCode, $statusKetercapaian, $kktpId, $indikatorIdsStr, $catatan, $recordId]);
            } else {
                // Update nilai normal biasa
                $stmtUpd = $this->db->prepare("
                    UPDATE nilai_asesmen_tp
                    SET nilai_asli = ?, nilai_awal = ?, is_remedial = 0, 
                        status_code = ?, status_ketercapaian = ?, 
                        kktp_id = ?, indikator_tercapai_ids = ?, catatan = ?
                    WHERE id = ?
                ");
                $stmtUpd->execute([$nilaiAsli, $nilaiAsli, $statusCode, $statusKetercapaian, $kktpId, $indikatorIdsStr, $catatan, $recordId]);
            }
        } else {
            // Insert baru
            $nilaiAwal = $isRemedial ? floatval($extra['nilai_awal'] ?? $nilaiAsli) : $nilaiAsli;
            $remedialFlag = $isRemedial ? 1 : 0;

            $stmtIns = $this->db->prepare("
                INSERT INTO nilai_asesmen_tp (
                    asesmen_id, asesmen_tp_id, tp_id, siswa_id, kktp_id, 
                    nilai_asli, nilai_maksimum, status_code, status_ketercapaian, 
                    indikator_tercapai_ids, is_remedial, nilai_awal, catatan
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtIns->execute([
                $asesmenId,
                $asesmenTpId,
                $tpId,
                $siswaId,
                $kktpId,
                $nilaiAsli,
                $nilaiMaks,
                $statusCode,
                $statusKetercapaian,
                $indikatorIdsStr,
                $remedialFlag,
                $nilaiAwal,
                $catatan
            ]);
        }

        // Sinkronisasi ke tabel nilai_asesmen_siswa legacy (composite average)
        $this->syncLegacyNilaiAsesmen($asesmenId, $siswaId);

        return [
            'status' => true,
            'status_code' => $statusCode,
            'status_ketercapaian' => $statusKetercapaian,
            'nilai_asli' => $nilaiAsli,
            'is_remedial' => (bool)$isRemedial,
            'message' => "Nilai TP berhasil disimpan. Status: {$statusKetercapaian} ({$statusCode})"
        ];
    }

    /**
     * Sinkronkan rata-rata nilai per TP ke tabel legacy nilai_asesmen_siswa
     */
    private function syncLegacyNilaiAsesmen($asesmenId, $siswaId) {
        try {
            $stmt = $this->db->prepare("
                SELECT AVG(nilai_asli) as rata_nilai 
                FROM nilai_asesmen_tp 
                WHERE asesmen_id = ? AND siswa_id = ?
            ");
            $stmt->execute([(int)$asesmenId, (int)$siswaId]);
            $avgScore = $stmt->fetchColumn();

            if ($avgScore !== false && $avgScore !== null) {
                $chk = $this->db->prepare("SELECT id FROM nilai_asesmen_siswa WHERE asesmen_id = ? AND siswa_id = ?");
                $chk->execute([(int)$asesmenId, (int)$siswaId]);
                if ($chk->fetch()) {
                    $this->db->prepare("UPDATE nilai_asesmen_siswa SET nilai = ? WHERE asesmen_id = ? AND siswa_id = ?")
                             ->execute([round($avgScore, 2), (int)$asesmenId, (int)$siswaId]);
                } else {
                    $this->db->prepare("INSERT INTO nilai_asesmen_siswa (asesmen_id, siswa_id, nilai) VALUES (?, ?, ?)")
                             ->execute([(int)$asesmenId, (int)$siswaId, round($avgScore, 2)]);
                }
            }
        } catch (\Throwable $e) {
            // Silently pass sync if legacy table behaves differently
        }
    }

    /**
     * Ambil matriks nilai seluruh siswa dalam suatu asesmen (berdasarkan daftar TP yang diukur)
     */
    public function getNilaiMatrixByAsesmen($asesmenId) {
        $asesmen = $this->getAsesmenById($asesmenId);
        if (!$asesmen) return null;

        $rombelId = (int)$asesmen['rombel_id'];

        // Ambil daftar siswa dalam rombel/kelas
        $stmtSiswa = $this->db->prepare("
            SELECT s.id as siswa_id, s.nis, s.nisn, s.nama_lengkap, s.jenis_kelamin
            FROM siswa s
            WHERE s.kelas_id = ?
            ORDER BY s.nama_lengkap ASC
        ");
        $stmtSiswa->execute([$rombelId]);
        $siswaList = $stmtSiswa->fetchAll(PDO::FETCH_ASSOC);

        // Ambil semua skor TP yang sudah tersimpan untuk asesmen ini
        $stmtScores = $this->db->prepare("
            SELECT * FROM nilai_asesmen_tp 
            WHERE asesmen_id = ?
        ");
        $stmtScores->execute([$asesmenId]);
        $rawScores = $stmtScores->fetchAll(PDO::FETCH_ASSOC);

        // Petakan ke format [siswa_id][tp_id] => data
        $scoreMap = [];
        foreach ($rawScores as $sc) {
            $scoreMap[$sc['siswa_id']][$sc['tp_id']] = $sc;
        }

        return [
            'asesmen' => $asesmen,
            'tujuan_pembelajaran' => $asesmen['tujuan_pembelajaran'],
            'siswa_list' => $siswaList,
            'scores' => $scoreMap
        ];
    }

    // =========================================================================
    // 5. REKAPITULASI KETERCAPAIAN SISWA & KELAS (DENGAN IDENTIFIKASI TP BERMASALAH)
    // =========================================================================

    /**
     * Rekap Ketercapaian TP Per Siswa pada suatu Mapel
     * Menampilkan daftar TP, nilai asli, nilai awal (jika remedial), dan status ketercapaian (1/0)
     */
    public function getRekapKetercapaianSiswa($siswaId, $mapelId, $tahunAjaranId, $semester = null) {
        $siswaId = (int)$siswaId;
        $mapelId = (int)$mapelId;
        $tahunAjaranId = (int)$tahunAjaranId;

        $sql = "
            SELECT natp.*, a.nama_asesmen, a.jenis_asesmen, a.tanggal,
                   tp.kode_tp, tp.deskripsi as deskripsi_tp, tp.materi_pokok,
                   k.metode as kktp_metode, k.nilai_minimum as kktp_nilai_min,
                   k.target_indikator_count as kktp_target_ind, k.deskripsi_kriteria as kktp_kriteria
            FROM nilai_asesmen_tp natp
            JOIN asesmen a ON natp.asesmen_id = a.id
            JOIN tujuan_pembelajaran tp ON natp.tp_id = tp.id
            LEFT JOIN kktp k ON natp.kktp_id = k.id
            WHERE natp.siswa_id = ?
              AND a.mapel_id = ?
              AND a.tahun_ajaran_id = ?
        ";
        $params = [$siswaId, $mapelId, $tahunAjaranId];

        if ($semester !== null) {
            $sql .= " AND a.semester = ?";
            $params[] = (int)$semester;
        }

        $sql .= " ORDER BY a.tanggal ASC, tp.urutan ASC, tp.kode_tp ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalEvaluated = count($records);
        $totalTercapai = 0;
        $totalBelumTercapai = 0;

        foreach ($records as $r) {
            if ((int)$r['status_code'] === 1) {
                $totalTercapai++;
            } else {
                $totalBelumTercapai++;
            }
        }

        $persentase = ($totalEvaluated > 0) ? round(($totalTercapai / $totalEvaluated) * 100, 1) : 0;

        // Rangkum status unik per TP (mengambil capaian terbaru per TP)
        $tpSummary = [];
        foreach ($records as $r) {
            $tpSummary[$r['tp_id']] = [
                'tp_id' => (int)$r['tp_id'],
                'kode_tp' => $r['kode_tp'],
                'deskripsi_tp' => $r['deskripsi_tp'],
                'materi_pokok' => $r['materi_pokok'],
                'nilai_asli' => (float)$r['nilai_asli'],
                'nilai_awal' => ($r['nilai_awal'] !== null) ? (float)$r['nilai_awal'] : null,
                'is_remedial' => (int)$r['is_remedial'],
                'status_code' => (int)$r['status_code'],
                'status_ketercapaian' => $r['status_ketercapaian'],
                'nama_asesmen' => $r['nama_asesmen'],
                'tanggal' => $r['tanggal']
            ];
        }

        $uniqueTotal = count($tpSummary);
        $uniqueTercapai = 0;
        $uniqueBelum = 0;
        foreach ($tpSummary as $u) {
            if ($u['status_code'] === 1) $uniqueTercapai++;
            else $uniqueBelum++;
        }
        $uniquePct = ($uniqueTotal > 0) ? round(($uniqueTercapai / $uniqueTotal) * 100, 1) : 0;

        return [
            'siswa_id' => $siswaId,
            'mapel_id' => $mapelId,
            'total_tp_dinilai' => $uniqueTotal,
            'total_tercapai' => $uniqueTercapai,
            'total_belum_tercapai' => $uniqueBelum,
            'persentase_ketercapaian' => $uniquePct,
            'tp_summary' => array_values($tpSummary),
            'total_event_asesmen' => $totalEvaluated,
            'records' => $records
        ];
    }

    /**
     * Rekap Ketercapaian Kelas Per TP
     * Mengidentifikasi TP mana saja yang persentase ketercapaiannya rendah (< 70%)
     * sehingga guru dapat mengambil tindakan penguatan / remidial klasikal
     */
    public function getRekapKetercapaianKelas($rombelId, $mapelId, $tahunAjaranId, $semester = null) {
        $rombelId = (int)$rombelId;
        $mapelId = (int)$mapelId;
        $tahunAjaranId = (int)$tahunAjaranId;

        // Ambil total siswa dalam kelas
        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM siswa WHERE kelas_id = ?");
        $stmtCount->execute([$rombelId]);
        $totalSiswaKelas = (int)$stmtCount->fetchColumn();

        $sql = "
            SELECT tp.id as tp_id, tp.kode_tp, tp.deskripsi as deskripsi_tp, tp.materi_pokok,
                   COUNT(natp.id) as total_asesmen_siswa,
                   SUM(CASE WHEN natp.status_code = 1 THEN 1 ELSE 0 END) as total_tercapai,
                   SUM(CASE WHEN natp.status_code = 0 THEN 1 ELSE 0 END) as total_belum_tercapai,
                   AVG(natp.nilai_asli) as rata_nilai_tp,
                   k.metode as kktp_metode, k.nilai_minimum as kktp_nilai_min,
                   k.deskripsi_kriteria as kktp_kriteria
            FROM tujuan_pembelajaran tp
            JOIN capaian_pembelajaran cp ON tp.cp_id = cp.id
            JOIN asesmen_tp atp ON atp.tp_id = tp.id
            JOIN asesmen a ON atp.asesmen_id = a.id
            LEFT JOIN kktp k ON atp.kktp_id = k.id
            LEFT JOIN nilai_asesmen_tp natp ON (natp.asesmen_id = a.id AND natp.tp_id = tp.id)
            WHERE a.rombel_id = ?
              AND a.mapel_id = ?
              AND a.tahun_ajaran_id = ?
        ";
        $params = [$rombelId, $mapelId, $tahunAjaranId];

        if ($semester !== null) {
            $sql .= " AND a.semester = ?";
            $params[] = (int)$semester;
        }

        $sql .= " GROUP BY tp.id ORDER BY tp.urutan ASC, tp.kode_tp ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tpRecap = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultList = [];
        foreach ($tpRecap as $row) {
            $totalAssessed = (int)($row['total_asesmen_siswa'] ?? 0);
            $tercapai = (int)($row['total_tercapai'] ?? 0);
            $belumTercapai = (int)($row['total_belum_tercapai'] ?? 0);
            $pct = ($totalAssessed > 0) ? round(($tercapai / $totalAssessed) * 100, 1) : 0;

            // Kategori tindak lanjut rekomendasi Kurikulum Merdeka
            $kategori = 'Tuntas';
            $badgeColor = 'success';
            if ($pct < 50) {
                $kategori = 'Perlu Remedial Klasikal / Pengajaran Ulang';
                $badgeColor = 'danger';
            } elseif ($pct < 75) {
                $kategori = 'Perlu Penguatan & Pendampingan Kelompok';
                $badgeColor = 'warning';
            } else {
                $kategori = 'Sangat Baik / Pengayaan';
                $badgeColor = 'success';
            }

            $row['persentase_ketercapaian'] = $pct;
            $row['kategori_tindak_lanjut'] = $kategori;
            $row['badge_color'] = $badgeColor;
            $row['rata_nilai_tp'] = round((float)($row['rata_nilai_tp'] ?? 0), 2);
            $resultList[] = $row;
        }

        return [
            'rombel_id' => $rombelId,
            'mapel_id' => $mapelId,
            'total_siswa_kelas' => $totalSiswaKelas,
            'tp_list' => $resultList
        ];
    }

    // =========================================================================
    // 6. INTEGRASI DENGAN E-RAPOR: GENERATOR DESKRIPSI CAPAIAN KOMPETENSI
    // =========================================================================

    /**
     * Membantu guru menghasilkan draf deskripsi capaian kompetensi E-Rapor
     * berdasarkan riwayat pencapaian TP siswa (TP mana yang tercapai & yang perlu bimbingan).
     */
    public function generateDeskripsiRaporFromTp($siswaId, $mapelId, $tahunAjaranId, $semester = null) {
        $rekap = $this->getRekapKetercapaianSiswa($siswaId, $mapelId, $tahunAjaranId, $semester);
        $records = $rekap['tp_summary'] ?? ($rekap['records'] ?? []);

        $tercapaiTps = [];
        $belumTercapaiTps = [];

        foreach ($records as $r) {
            $desc = trim($r['deskripsi_tp']);
            if (empty($desc)) $desc = trim($r['materi_pokok'] ?? $r['kode_tp']);
            $desc = rtrim($desc, '. ');

            if ((int)$r['status_code'] === 1) {
                $tercapaiTps[] = [
                    'kode' => $r['kode_tp'],
                    'deskripsi' => $desc,
                    'nilai' => (float)$r['nilai_asli']
                ];
            } else {
                $belumTercapaiTps[] = [
                    'kode' => $r['kode_tp'],
                    'deskripsi' => $desc,
                    'nilai' => (float)$r['nilai_asli']
                ];
            }
        }

        // Urutkan yang tercapai berdasarkan nilai tertinggi
        usort($tercapaiTps, function($a, $b) {
            return $b['nilai'] <=> $a['nilai'];
        });

        // Buat kalimat deskripsi tercapai
        $deskripsiTercapai = '';
        if (!empty($tercapaiTps)) {
            $sampleTercapai = array_slice($tercapaiTps, 0, 2); // Ambil 2 TP terbaik
            $parts = [];
            foreach ($sampleTercapai as $st) {
                $parts[] = lcfirst($st['deskripsi']);
            }
            $deskripsiTercapai = "Menunjukkan penguasaan yang sangat baik dalam hal " . implode(' serta ', $parts) . ".";
        } else {
            $deskripsiTercapai = "Menunjukkan pemahaman dasar pada materi yang diajarkan.";
        }

        // Buat kalimat deskripsi perlu bimbingan
        $deskripsiPerluBimbingan = '';
        if (!empty($belumTercapaiTps)) {
            $sampleBelum = array_slice($belumTercapaiTps, 0, 2); // Ambil 2 TP yang belum tuntas
            $parts = [];
            foreach ($sampleBelum as $sb) {
                $parts[] = lcfirst($sb['deskripsi']);
            }
            $deskripsiPerluBimbingan = "Perlu bimbingan dan pendampingan lebih lanjut dalam hal " . implode(' serta ', $parts) . ".";
        }

        // Gabungan utuh untuk kolom capaian_kompetensi rapor
        $fullDeskripsi = $deskripsiTercapai;
        if (!empty($deskripsiPerluBimbingan)) {
            $fullDeskripsi .= " " . $deskripsiPerluBimbingan;
        }

        return [
            'deskripsi_tercapai' => $deskripsiTercapai,
            'deskripsi_perlu_bimbingan' => $deskripsiPerluBimbingan,
            'capaian_kompetensi' => trim($fullDeskripsi),
            'total_tp' => count($records),
            'total_tercapai' => count($tercapaiTps),
            'total_belum_tercapai' => count($belumTercapaiTps)
        ];
    }

    /**
     * Ambil daftar Quiz CBT guru (atau difilter berdasarkan mapel & rombel)
     */
    public function getAvailableQuizzesForTeacher($guruId, $mapelId = null, $rombelId = null) {
        $sql = "
            SELECT q.id, q.judul, q.mapel_id, q.kelas_id, q.kelas_ids, q.deadline, q.status,
                   m.nama_mapel,
                   (SELECT COUNT(*) FROM hasil_quiz h WHERE h.quiz_id = q.id) as total_peserta
            FROM quiz q
            JOIN mata_pelajaran m ON q.mapel_id = m.id
            WHERE 1=1
        ";
        $params = [];
        if ($guruId) {
            $sql .= " AND q.guru_id = ?";
            $params[] = (int)$guruId;
        }
        if ($mapelId) {
            $sql .= " AND q.mapel_id = ?";
            $params[] = (int)$mapelId;
        }
        $sql .= " ORDER BY q.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($rombelId) {
            $filtered = [];
            foreach ($list as $item) {
                $kIds = [];
                if (!empty($item['kelas_ids'])) {
                    $kIds = array_map('intval', explode(',', $item['kelas_ids']));
                }
                if (!empty($item['kelas_id'])) {
                    $kIds[] = (int)$item['kelas_id'];
                }
                if (empty($kIds) || in_array((int)$rombelId, $kIds)) {
                    $filtered[] = $item;
                }
            }
            return $filtered;
        }

        return $list;
    }

    /**
     * Tarik nilai dari hasil Quiz CBT ke dalam Nilai Asesmen TP secara otomatis.
     * Otomatis mengevaluasi ketercapaian KKTP (1 = Tercapai, 0 = Belum Tercapai).
     */
    public function syncNilaiFromQuiz($asesmenId, $quizId, $tpId, $sourceMode = 'tertinggi') {
        $asesmen = $this->getAsesmenById($asesmenId);
        if (!$asesmen) {
            return ['status' => false, 'message' => 'Asesmen tidak ditemukan.'];
        }

        $rombelId = (int)$asesmen['rombel_id'];
        $tpId = (int)$tpId;

        if ($tpId <= 0) {
            return ['status' => false, 'message' => 'Tujuan Pembelajaran (TP) target wajib dipilih.'];
        }

        // Ambil daftar siswa di rombel
        $stmtSiswa = $this->db->prepare("SELECT id FROM siswa WHERE kelas_id = ?");
        $stmtSiswa->execute([$rombelId]);
        $siswaIds = $stmtSiswa->fetchAll(PDO::FETCH_COLUMN);

        if (empty($siswaIds)) {
            return ['status' => false, 'message' => 'Tidak ada siswa pada rombel kelas asesmen ini.'];
        }

        // Ambil hasil quiz untuk seluruh siswa ini
        $inSiswa = implode(',', array_map('intval', $siswaIds));
        $stmtHasil = $this->db->prepare("
            SELECT siswa_id, total_nilai, nilai_tertinggi 
            FROM hasil_quiz 
            WHERE quiz_id = ? AND siswa_id IN ($inSiswa)
        ");
        $stmtHasil->execute([(int)$quizId]);
        $hasilList = $stmtHasil->fetchAll(PDO::FETCH_ASSOC);

        if (empty($hasilList)) {
            return ['status' => false, 'message' => 'Belum ada data pengerjaan kuis siswa pada kelas ini untuk kuis yang dipilih.'];
        }

        // Ambil info kuis untuk catatan
        $stmtQ = $this->db->prepare("SELECT judul FROM quiz WHERE id = ?");
        $stmtQ->execute([(int)$quizId]);
        $quizJudul = $stmtQ->fetchColumn() ?: 'CBT Online';

        $syncedCount = 0;
        $tercapaiCount = 0;
        $belumCount = 0;

        foreach ($hasilList as $h) {
            $sId = (int)$h['siswa_id'];
            $nilaiScore = ($sourceMode === 'tertinggi' && isset($h['nilai_tertinggi']) && $h['nilai_tertinggi'] !== null)
                ? floatval($h['nilai_tertinggi'])
                : floatval($h['total_nilai'] ?? 0);

            // Batasi nilai maksimum sesuai asesmen jika perlu
            if ($asesmen['nilai_maksimum'] > 0 && $nilaiScore > $asesmen['nilai_maksimum']) {
                $nilaiScore = floatval($asesmen['nilai_maksimum']);
            }

            $catatan = "Diimpor dari CBT: " . $quizJudul . " (" . date('d/m/Y H:i') . ")";
            $evalRes = $this->inputNilaiSiswaPerTp($asesmenId, $tpId, $sId, $nilaiScore, false, $catatan);
            if (!empty($evalRes['status'])) {
                $syncedCount++;
                if (!empty($evalRes['status_code']) && (int)$evalRes['status_code'] === 1) {
                    $tercapaiCount++;
                } else {
                    $belumCount++;
                }
            }
        }

        // Update ref_quiz_id di asesmen jika belum diset
        if (empty($asesmen['ref_quiz_id'])) {
            $stmtUpRef = $this->db->prepare("UPDATE asesmen SET ref_quiz_id = ? WHERE id = ?");
            $stmtUpRef->execute([(int)$quizId, $asesmenId]);
        }

        return [
            'status' => true,
            'message' => "Berhasil menyinkronkan {$syncedCount} nilai siswa dari Quiz CBT '{$quizJudul}'. Hasil evaluasi KKTP: {$tercapaiCount} siswa Tercapai (1) dan {$belumCount} siswa Belum Tercapai / Remedial (0).",
            'synced_count' => $syncedCount,
            'tercapai_count' => $tercapaiCount,
            'belum_count' => $belumCount
        ];
    }
}
