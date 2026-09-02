<?php
/**
 * Academic Model (Jurusan, Kelas, Mapel, Jadwal, Semester, Tahun Ajaran)
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class AcademicModel extends BaseModel {

    public function __construct() {
        parent::__construct();
        $this->ensureWaliKelasColumn();
        $this->ensureEnrollmentTables();
    }

    private function ensureWaliKelasColumn() {
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM kelas LIKE 'wali_kelas_id'")->fetchAll();
            if (empty($cols)) {
                $this->db->exec("ALTER TABLE kelas ADD COLUMN wali_kelas_id INT NULL");
            }
        } catch (Exception $e) {}
    }

    // --- JURUSAN ---
    public function getJurusan() {
        return $this->db->query("SELECT * FROM jurusan ORDER BY nama_jurusan ASC")->fetchAll();
    }

    public function addJurusan($kode, $nama, $deskripsi) {
        $stmt = $this->db->prepare("INSERT INTO jurusan (kode_jurusan, nama_jurusan, deskripsi) VALUES (?, ?, ?)");
        return $stmt->execute([$kode, $nama, $deskripsi]);
    }

    public function updateJurusan($id, $kode, $nama, $deskripsi) {
        $stmt = $this->db->prepare("UPDATE jurusan SET kode_jurusan = ?, nama_jurusan = ?, deskripsi = ? WHERE id = ?");
        return $stmt->execute([$kode, $nama, $deskripsi, $id]);
    }

    public function deleteJurusan($id) {
        $stmt = $this->db->prepare("DELETE FROM jurusan WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- KELAS ---
    public function getKelas() {
        return $this->db->query("
            SELECT k.*, j.nama_jurusan, g.nama_lengkap as nama_walikelas, g.nip as nip_walikelas 
            FROM kelas k 
            JOIN jurusan j ON k.jurusan_id = j.id 
            LEFT JOIN guru g ON k.wali_kelas_id = g.id
            ORDER BY k.tingkat ASC, k.nama_kelas ASC
        ")->fetchAll();
    }

    public function addKelas($nama, $jurusan_id, $tingkat, $wali_kelas_id = null) {
        $stmt = $this->db->prepare("INSERT INTO kelas (nama_kelas, jurusan_id, tingkat, wali_kelas_id) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$nama, $jurusan_id, $tingkat, $wali_kelas_id ?: null]);
    }

    public function updateKelas($id, $nama, $jurusan_id, $tingkat, $wali_kelas_id = null) {
        $stmt = $this->db->prepare("UPDATE kelas SET nama_kelas = ?, jurusan_id = ?, tingkat = ?, wali_kelas_id = ? WHERE id = ?");
        return $stmt->execute([$nama, $jurusan_id, $tingkat, $wali_kelas_id ?: null, $id]);
    }

    public function setWaliKelas($kelas_id, $guru_id) {
        $stmt = $this->db->prepare("UPDATE kelas SET wali_kelas_id = ? WHERE id = ?");
        return $stmt->execute([$guru_id ?: null, $kelas_id]);
    }

    public function deleteKelas($id) {
        $stmt = $this->db->prepare("DELETE FROM kelas WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function joinKelasByCode($siswaId, $kodeInput) {
        $rawUpper = strtoupper(trim($kodeInput));
        $cleanCode = strtoupper(trim(str_replace('MH-', '', $kodeInput)));
        $kelasList = $this->getKelas();
        $targetKelas = null;

        foreach ($kelasList as $k) {
            $codeCalculated = strtoupper(substr(md5($k['id']), 0, 6));
            $dbKode = strtoupper(trim($k['kode_kelas'] ?? ''));
            $namaKelasUpper = strtoupper(trim($k['nama_kelas'] ?? ''));

            if ($cleanCode === $codeCalculated || 
                $rawUpper === $codeCalculated || 
                $cleanCode === (string)$k['id'] || 
                (!empty($dbKode) && ($rawUpper === $dbKode || $cleanCode === $dbKode)) ||
                $rawUpper === $namaKelasUpper ||
                $cleanCode === $namaKelasUpper) {
                $targetKelas = $k;
                break;
            }
        }

        if (!$targetKelas) {
            return ['status' => false, 'message' => 'Kode Rombel Kelas tidak ditemukan atau tidak valid. Silakan periksa kembali kode dari Wali Kelas/Guru.'];
        }

        return $this->joinKelasById($siswaId, $targetKelas['id']);
    }

    public function joinKelasById($siswaId, $kelasId) {
        try {
            $stmtK = $this->db->prepare("SELECT k.*, j.nama_jurusan FROM kelas k LEFT JOIN jurusan j ON k.jurusan_id = j.id WHERE k.id = ?");
            $stmtK->execute([$kelasId]);
            $kRow = $stmtK->fetch();

            if (!$kRow) {
                return ['status' => false, 'message' => 'Rombel Kelas tidak ditemukan.'];
            }

            $stmtS = $this->db->prepare("UPDATE siswa SET kelas_id = ?, jurusan_id = ? WHERE id = ?");
            $stmtS->execute([$kRow['id'], $kRow['jurusan_id'], $siswaId]);

            return [
                'status' => true,
                'message' => "Selamat! Anda resmi terdaftar di Rombel Kelas Virtual: " . $kRow['nama_kelas'] . " (" . ($kRow['nama_jurusan'] ?? 'Umum') . ")",
                'kelas' => $kRow
            ];
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Gagal memperbarui kelas: ' . $e->getMessage()];
        }
    }

    // --- MATA PELAJARAN ---
    public function getMapel() {
        return $this->db->query("
            SELECT m.*, j.nama_jurusan 
            FROM mata_pelajaran m 
            LEFT JOIN jurusan j ON m.jurusan_id = j.id 
            ORDER BY m.nama_mapel ASC
        ")->fetchAll();
    }

    public function getMapelByKelas($kelas_id) {
        $this->ensureEnrollmentTables();
        $kid = (int)$kelas_id;
        if ($kid <= 0) {
            return $this->getMapel();
        }

        $stmtK = $this->db->prepare("SELECT jurusan_id FROM kelas WHERE id = ?");
        $stmtK->execute([$kid]);
        $jurusanId = (int)$stmtK->fetchColumn();

        $sql = "
            SELECT DISTINCT m.*, j.nama_jurusan
            FROM mata_pelajaran m
            LEFT JOIN jurusan j ON m.jurusan_id = j.id
            WHERE m.id IN (
                SELECT mapel_id FROM jadwal WHERE (kelas_id = {$kid} OR kelas_id IS NULL OR kelas_id = 0) AND mapel_id IS NOT NULL
                UNION
                SELECT mapel_id FROM materi WHERE (kelas_id = {$kid} OR kelas_id IS NULL OR kelas_id = 0) AND mapel_id IS NOT NULL
                UNION
                SELECT mapel_id FROM tugas WHERE (kelas_id = {$kid} OR kelas_id IS NULL OR kelas_id = 0) AND mapel_id IS NOT NULL
                UNION
                SELECT mapel_id FROM quiz WHERE (kelas_id = {$kid} OR kelas_id IS NULL OR kelas_id = 0) AND mapel_id IS NOT NULL
            )
            OR (m.jurusan_id = {$jurusanId} OR m.jurusan_id IS NULL OR m.jurusan_id = 0)
            ORDER BY m.nama_mapel ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getMapelByGuru($guru_id) {
        $this->ensureEnrollmentTables();
        $gId = (int)$guru_id;
        $sql = "
            SELECT DISTINCT m.*, j.nama_jurusan
            FROM mata_pelajaran m
            LEFT JOIN jurusan j ON m.jurusan_id = j.id
            WHERE m.id IN (
                SELECT mapel_id FROM jadwal WHERE guru_id = {$gId} AND mapel_id IS NOT NULL
                UNION
                SELECT mapel_id FROM materi WHERE guru_id = {$gId} AND mapel_id IS NOT NULL
                UNION
                SELECT mapel_id FROM tugas WHERE guru_id = {$gId} AND mapel_id IS NOT NULL
                UNION
                SELECT mapel_id FROM quiz WHERE guru_id = {$gId} AND mapel_id IS NOT NULL
            )
            ORDER BY m.nama_mapel ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getKelasByGuru($guru_id) {
        $this->ensureEnrollmentTables();
        $gId = (int)$guru_id;
        $sql = "
            SELECT DISTINCT k.*, j.nama_jurusan
            FROM kelas k
            LEFT JOIN jurusan j ON k.jurusan_id = j.id
            WHERE k.id IN (
                SELECT id FROM kelas WHERE wali_kelas_id = {$gId}
                UNION
                SELECT kelas_id FROM mapel_enrollment_keys WHERE guru_id = {$gId} AND kelas_id IS NOT NULL
                UNION
                SELECT kelas_id FROM jadwal WHERE guru_id = {$gId} AND kelas_id IS NOT NULL
                UNION
                SELECT kelas_id FROM materi WHERE guru_id = {$gId} AND kelas_id IS NOT NULL
                UNION
                SELECT kelas_id FROM tugas WHERE guru_id = {$gId} AND kelas_id IS NOT NULL
                UNION
                SELECT s.kelas_id FROM siswa_mapel_enrollment sme JOIN siswa s ON sme.siswa_id = s.id WHERE sme.guru_id = {$gId} AND s.kelas_id IS NOT NULL
            )
            ORDER BY k.tingkat ASC, k.nama_kelas ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function generateKodeMapel($nama = '', $jurusan_id = 0) {
        $prefix = 'MP';
        if ($jurusan_id > 0) {
            $stmtJ = $this->db->prepare("SELECT kode_jurusan FROM jurusan WHERE id = ?");
            $stmtJ->execute([(int)$jurusan_id]);
            $jKode = $stmtJ->fetchColumn();
            if ($jKode) {
                $prefix = 'MPL-' . strtoupper(trim($jKode));
            }
        }

        $stmt = $this->db->query("SELECT MAX(id) FROM mata_pelajaran");
        $maxId = (int)$stmt->fetchColumn();
        $nextNum = $maxId + 1;

        if ($prefix === 'MP') {
            $candidate = sprintf('MP%02d', $nextNum);
        } else {
            $candidate = sprintf('%s-%02d', $prefix, $nextNum);
        }

        $chk = $this->db->prepare("SELECT id FROM mata_pelajaran WHERE kode_mapel = ?");
        $chk->execute([$candidate]);
        if ($chk->fetch()) {
            $candidate = sprintf('MP%02d-%d', $nextNum, rand(10, 99));
        }

        return $candidate;
    }

    public function addMapel($kode, $nama, $jurusan_id) {
        if (empty(trim($kode ?? ''))) {
            $kode = $this->generateKodeMapel($nama, $jurusan_id);
        }
        $stmt = $this->db->prepare("INSERT INTO mata_pelajaran (kode_mapel, nama_mapel, jurusan_id) VALUES (?, ?, ?)");
        return $stmt->execute([$kode, $nama, $jurusan_id ?: null]);
    }

    public function updateMapel($id, $kode, $nama, $jurusan_id) {
        $stmt = $this->db->prepare("UPDATE mata_pelajaran SET kode_mapel = ?, nama_mapel = ?, jurusan_id = ? WHERE id = ?");
        return $stmt->execute([$kode, $nama, $jurusan_id ?: null, $id]);
    }

    public function deleteMapel($id) {
        $stmt = $this->db->prepare("DELETE FROM mata_pelajaran WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- JADWAL ---
    public function getJadwal($kelas_id = null, $guru_id = null) {
        $sql = "
            SELECT j.*, k.nama_kelas, k.tingkat, m.nama_mapel, m.kode_mapel, g.nama_lengkap as nama_guru,
                   COALESCE(
                       (SELECT enrollment_key FROM mapel_enrollment_keys mek WHERE mek.mapel_id = j.mapel_id AND mek.guru_id = j.guru_id AND mek.kelas_id = j.kelas_id LIMIT 1),
                       (SELECT enrollment_key FROM mapel_enrollment_keys mek WHERE mek.mapel_id = j.mapel_id AND mek.guru_id = j.guru_id AND (mek.kelas_id IS NULL OR mek.kelas_id = 0) LIMIT 1)
                   ) as enrollment_key
            FROM jadwal j
            JOIN kelas k ON j.kelas_id = k.id
            JOIN mata_pelajaran m ON j.mapel_id = m.id
            JOIN guru g ON j.guru_id = g.id
            WHERE 1=1
        ";
        if ($kelas_id) {
            $sql .= " AND j.kelas_id = " . (int)$kelas_id;
        }
        if ($guru_id) {
            $sql .= " AND j.guru_id = " . (int)$guru_id;
        }
        $sql .= " ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), j.jam_mulai ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function addJadwal($kelas_id, $mapel_id, $guru_id, $hari, $jam_mulai, $jam_selesai, $ruangan) {
        $stmt = $this->db->prepare("
            INSERT INTO jadwal (kelas_id, mapel_id, guru_id, hari, jam_mulai, jam_selesai, ruangan)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $res = $stmt->execute([$kelas_id, $mapel_id, $guru_id, $hari, $jam_mulai, $jam_selesai, $ruangan]);

        if ($guru_id) {
            $this->ensureGuruClassKeys($guru_id);
        }

        return $res;
    }

    public function updateJadwal($id, $kelas_id, $mapel_id, $guru_id, $hari, $jam_mulai, $jam_selesai, $ruangan) {
        $stmt = $this->db->prepare("
            UPDATE jadwal 
            SET kelas_id = ?, mapel_id = ?, guru_id = ?, hari = ?, jam_mulai = ?, jam_selesai = ?, ruangan = ?
            WHERE id = ?
        ");
        return $stmt->execute([$kelas_id, $mapel_id, $guru_id, $hari, $jam_mulai, $jam_selesai, $ruangan, $id]);
    }

    public function deleteJadwal($id) {
        $stmt = $this->db->prepare("DELETE FROM jadwal WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function batchAddJadwal($entries) {
        $countSuccess = 0;
        $conflictMessages = [];
        $stmt = $this->db->prepare("
            INSERT INTO jadwal (kelas_id, mapel_id, guru_id, hari, jam_mulai, jam_selesai, ruangan)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($entries as $e) {
            $kelasId = (int)($e['kelas_id'] ?? 0);
            $mapelId = (int)($e['mapel_id'] ?? 0);
            $guruId = (int)($e['guru_id'] ?? 0);
            $hari = $e['hari'] ?? 'Senin';
            $jamMulai = $e['jam_mulai'] ?? '07:30';
            $jamSelesai = $e['jam_selesai'] ?? '09:00';
            $ruangan = Security::sanitize($e['ruangan'] ?? 'Ruang Kelas');

            if ($kelasId > 0 && $mapelId > 0 && $guruId > 0) {
                $conflict = $this->checkJadwalConflict($hari, $jamMulai, $jamSelesai, $guruId, $kelasId, $ruangan);
                if (!$conflict['conflict']) {
                    if ($stmt->execute([$kelasId, $mapelId, $guruId, $hari, $jamMulai, $jamSelesai, $ruangan])) {
                        $countSuccess++;
                    }
                } else {
                    $conflictMessages[] = $conflict['message'];
                }
            }
        }
        return [
            'success_count' => $countSuccess,
            'conflicts' => $conflictMessages
        ];
    }

    public function isGenericRoom($ruangan) {
        if (empty($ruangan)) return true;
        $r = strtolower(trim($ruangan));
        $generics = [
            'ruang kelas',
            'ruang kelas masing-masing',
            'ruang kbm',
            'kelas',
            '-',
            'rg. kelas',
            'ruangan kelas',
            'ruang kelas / rombel'
        ];
        if (in_array($r, $generics)) return true;
        if (strpos($r, 'ruang kelas') !== false) return true;
        return false;
    }

    public function checkJadwalConflict($hari, $jam_mulai, $jam_selesai, $guru_id, $kelas_id, $ruangan, $ignoreId = null) {
        $sql = "
            SELECT j.*, k.nama_kelas, m.nama_mapel, g.nama_lengkap as nama_guru
            FROM jadwal j
            JOIN kelas k ON j.kelas_id = k.id
            JOIN mata_pelajaran m ON j.mapel_id = m.id
            JOIN guru g ON j.guru_id = g.id
            WHERE j.hari = ?
              AND (j.jam_mulai < ? AND j.jam_selesai > ?)
        ";
        $params = [$hari, $jam_selesai, $jam_mulai];

        if ($ignoreId) {
            $sql .= " AND j.id != ?";
            $params[] = (int)$ignoreId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $conflicts = $stmt->fetchAll();

        foreach ($conflicts as $c) {
            $startStr = date('H:i', strtotime($c['jam_mulai']));
            $endStr = date('H:i', strtotime($c['jam_selesai']));

            if ((int)$c['guru_id'] === (int)$guru_id) {
                return [
                    'conflict' => true,
                    'type' => 'guru',
                    'message' => "⚠️ Gagal Menyimpan! Bentrok Jadwal Mengajar Guru: Guru {$c['nama_guru']} sudah memiliki jadwal mengajar di kelas {$c['nama_kelas']} ({$c['nama_mapel']}) pada hari {$hari} jam {$startStr} - {$endStr} WIB!"
                ];
            }

            if ((int)$c['kelas_id'] === (int)$kelas_id) {
                return [
                    'conflict' => true,
                    'type' => 'kelas',
                    'message' => "⚠️ Gagal Menyimpan! Bentrok Jadwal Kelas: Rombel Kelas {$c['nama_kelas']} sudah memiliki jadwal mata pelajaran {$c['nama_mapel']} bersama Guru {$c['nama_guru']} pada hari {$hari} jam {$startStr} - {$endStr} WIB!"
                ];
            }

            if (!$this->isGenericRoom($ruangan) && !$this->isGenericRoom($c['ruangan'] ?? '') && strcasecmp(trim($c['ruangan'] ?? ''), trim($ruangan)) === 0) {
                return [
                    'conflict' => true,
                    'type' => 'ruangan',
                    'message' => "⚠️ Gagal Menyimpan! Bentrok Alokasi Ruangan: Ruangan '{$ruangan}' sedang digunakan oleh kelas {$c['nama_kelas']} ({$c['nama_mapel']}, Guru: {$c['nama_guru']}) pada hari {$hari} jam {$startStr} - {$endStr} WIB!"
                ];
            }
        }

        return ['conflict' => false];
    }

    public function detectScheduleConflicts(&$jadwalList) {
        $count = count($jadwalList);
        for ($i = 0; $i < $count; $i++) {
            $jadwalList[$i]['is_conflict'] = false;
            $jadwalList[$i]['conflict_reasons'] = [];
        }

        $allJadwal = $this->getJadwal();

        for ($i = 0; $i < $count; $i++) {
            $a = $jadwalList[$i];

            foreach ($allJadwal as $b) {
                if ((int)$a['id'] === (int)$b['id']) continue;

                if (strcasecmp($a['hari'], $b['hari']) === 0) {
                    $startA = strtotime("1970-01-01 " . $a['jam_mulai']);
                    $endA   = strtotime("1970-01-01 " . $a['jam_selesai']);
                    $startB = strtotime("1970-01-01 " . $b['jam_mulai']);
                    $endB   = strtotime("1970-01-01 " . $b['jam_selesai']);

                    if ($startA < $endB && $endA > $startB) {
                        $reasons = [];
                        if ((int)$a['guru_id'] === (int)$b['guru_id']) {
                            $reasons[] = "Guru {$a['nama_guru']} mengajar 2 kelas bersamaan ({$a['nama_kelas']} & {$b['nama_kelas']})";
                        }
                        if ((int)$a['kelas_id'] === (int)$b['kelas_id']) {
                            $reasons[] = "Kelas {$a['nama_kelas']} mempunyai 2 mapel bersamaan ({$a['nama_mapel']} & {$b['nama_mapel']})";
                        }
                        if (!$this->isGenericRoom($a['ruangan'] ?? '') && !$this->isGenericRoom($b['ruangan'] ?? '') && strcasecmp(trim($a['ruangan']), trim($b['ruangan'])) === 0) {
                            $reasons[] = "Ruangan '{$a['ruangan']}' dipakai 2 kelas bersamaan ({$a['nama_kelas']} & {$b['nama_kelas']})";
                        }

                        if (!empty($reasons)) {
                            $jadwalList[$i]['is_conflict'] = true;
                            $jadwalList[$i]['conflict_reasons'] = array_unique(array_merge($jadwalList[$i]['conflict_reasons'], $reasons));
                        }
                    }
                }
            }
        }
    }

    // --- TAHUN AJARAN & SEMESTER ---
    // --- TAHUN AJARAN & SEMESTER ---
    private function ensureTahunAjaranTable() {
        try {
            $this->db->exec("ALTER TABLE tahun_ajaran ADD COLUMN IF NOT EXISTS tahun_ajaran VARCHAR(20) NULL");
            $this->db->exec("ALTER TABLE tahun_ajaran ADD COLUMN IF NOT EXISTS semester VARCHAR(20) DEFAULT 'Ganjil'");
            $this->db->exec("ALTER TABLE tahun_ajaran ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 0");
            $this->db->exec("ALTER TABLE tahun_ajaran ADD COLUMN IF NOT EXISTS created_at DATETIME DEFAULT CURRENT_TIMESTAMP");

            $this->db->exec("UPDATE tahun_ajaran SET tahun_ajaran = tahun WHERE (tahun_ajaran IS NULL OR tahun_ajaran = '') AND tahun IS NOT NULL");
            $this->db->exec("UPDATE tahun_ajaran SET is_active = 1 WHERE status = 'aktif' AND is_active = 0");

            // Drop UNIQUE constraint on legacy 'tahun' column if present so multiple semesters can be saved
            $indexes = $this->db->query("SHOW INDEX FROM tahun_ajaran WHERE Key_name = 'tahun'")->fetchAll();
            if (!empty($indexes)) {
                $this->db->exec("ALTER TABLE tahun_ajaran DROP INDEX tahun");
            }
        } catch (Exception $e) {}
    }

    public function getTahunAjaran() {
        $this->ensureTahunAjaranTable();
        $raw = $this->db->query("SELECT * FROM tahun_ajaran ORDER BY id DESC")->fetchAll();
        
        $activeSemester = 'Ganjil';
        try {
            $sem = $this->db->query("SELECT nama_semester FROM semester WHERE status = 'aktif' LIMIT 1")->fetchColumn();
            if ($sem) $activeSemester = $sem;
        } catch (Exception $e) {}

        $normalized = [];
        foreach ($raw as $r) {
            $tahunName = !empty($r['tahun_ajaran']) ? $r['tahun_ajaran'] : (!empty($r['tahun']) ? $r['tahun'] : '2025/2026');
            $semName = !empty($r['semester']) ? $r['semester'] : $activeSemester;
            $isActive = (isset($r['is_active']) && $r['is_active'] == 1) || (isset($r['status']) && $r['status'] === 'aktif');
            $createdAt = !empty($r['created_at']) ? $r['created_at'] : date('Y-m-d H:i:s');

            $normalized[] = [
                'id' => (int)$r['id'],
                'tahun_ajaran' => $tahunName,
                'semester' => $semName,
                'is_active' => $isActive ? 1 : 0,
                'status' => $isActive ? 'aktif' : 'non-aktif',
                'created_at' => $createdAt
            ];
        }
        return $normalized;
    }

    public function getActiveTahunAjaran() {
        $all = $this->getTahunAjaran();
        foreach ($all as $item) {
            if ($item['is_active'] == 1) {
                return $item;
            }
        }
        return $all[0] ?? ['id' => 1, 'tahun_ajaran' => '2025/2026', 'semester' => 'Ganjil', 'is_active' => 1];
    }

    public function addTahunAjaran($tahun_ajaran, $semester, $is_active = 0) {
        $this->ensureTahunAjaranTable();
        if ($is_active) {
            $this->db->exec("UPDATE tahun_ajaran SET is_active = 0, status = 'non-aktif'");
        }
        $statusStr = $is_active ? 'aktif' : 'non-aktif';

        try {
            $stmt = $this->db->prepare("INSERT INTO tahun_ajaran (tahun, status, tahun_ajaran, semester, is_active) VALUES (?, ?, ?, ?, ?)");
            $res = $stmt->execute([$tahun_ajaran, $statusStr, $tahun_ajaran, $semester, $is_active ? 1 : 0]);
        } catch (Exception $e) {
            $stmt = $this->db->prepare("INSERT INTO tahun_ajaran (tahun_ajaran, semester, is_active) VALUES (?, ?, ?)");
            $res = $stmt->execute([$tahun_ajaran, $semester, $is_active ? 1 : 0]);
        }

        $id = $this->db->lastInsertId();
        if ($is_active && $id) {
            $this->setActiveTahunAjaran($id);
        }
        return $res;
    }

    public function updateTahunAjaran($id, $tahun_ajaran, $semester, $is_active = 0) {
        $this->ensureTahunAjaranTable();
        if ($is_active) {
            $this->db->exec("UPDATE tahun_ajaran SET is_active = 0, status = 'non-aktif'");
        }
        $statusStr = $is_active ? 'aktif' : 'non-aktif';

        try {
            $stmt = $this->db->prepare("UPDATE tahun_ajaran SET tahun = ?, status = ?, tahun_ajaran = ?, semester = ?, is_active = ? WHERE id = ?");
            $res = $stmt->execute([$tahun_ajaran, $statusStr, $tahun_ajaran, $semester, $is_active ? 1 : 0, $id]);
        } catch (Exception $e) {
            $stmt = $this->db->prepare("UPDATE tahun_ajaran SET tahun_ajaran = ?, semester = ?, is_active = ? WHERE id = ?");
            $res = $stmt->execute([$tahun_ajaran, $semester, $is_active ? 1 : 0, $id]);
        }

        if ($is_active) {
            $this->setActiveTahunAjaran($id);
        }
        return $res;
    }

    public function setActiveTahunAjaran($id) {
        $this->ensureTahunAjaranTable();
        $this->db->exec("UPDATE tahun_ajaran SET is_active = 0, status = 'non-aktif'");
        $stmt = $this->db->prepare("UPDATE tahun_ajaran SET is_active = 1, status = 'aktif' WHERE id = ?");
        $res = $stmt->execute([$id]);

        $active = $this->getActiveTahunAjaran();
        if ($active) {
            $semName = $active['semester'] ?? 'Ganjil';
            try {
                $this->db->exec("UPDATE semester SET status = 'non-aktif'");
                $this->db->exec("UPDATE semester SET status = 'aktif' WHERE nama_semester = " . $this->db->quote($semName));
            } catch (Exception $e) {}

            require_once ROOT_PATH . 'models/SettingsModel.php';
            $settingsModel = new SettingsModel();
            $settingsModel->saveBatch([
                'tahun_ajaran' => $active['tahun_ajaran'],
                'semester' => $active['semester']
            ]);
        }
        return $res;
    }

    public function deleteTahunAjaran($id) {
        $this->ensureTahunAjaranTable();
        $stmt = $this->db->prepare("DELETE FROM tahun_ajaran WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getSemester() {
        return $this->getTahunAjaran();
    }

    // --- ENROLLMENT KEY & KODE AKSES MAPEL PER GURU ---
    public function ensureEnrollmentTables() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS mapel_enrollment_keys (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    mapel_id INT NOT NULL,
                    guru_id INT NOT NULL,
                    kelas_id INT NULL,
                    enrollment_key VARCHAR(50) NOT NULL,
                    passcode VARCHAR(50) NULL,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            try {
                $this->db->exec("ALTER TABLE mapel_enrollment_keys DROP INDEX u_mapel_guru");
            } catch (\Throwable $eDrop) {}

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS siswa_mapel_enrollment (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    siswa_id INT NOT NULL,
                    mapel_id INT NOT NULL,
                    guru_id INT NOT NULL,
                    kelas_id INT NULL,
                    enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            try {
                $this->db->exec("ALTER TABLE siswa_mapel_enrollment ADD COLUMN kelas_id INT NULL AFTER guru_id");
            } catch (\Throwable $eCol) {}

        } catch (\Throwable $e) {}
    }

    public function deduplicateKeys() {
        $this->ensureEnrollmentTables();
        try {
            // Delete duplicate mapel_enrollment_keys rows keeping only the smallest ID per (mapel_id, guru_id, COALESCE(kelas_id, 0))
            $sql = "
                DELETE k1 FROM mapel_enrollment_keys k1
                INNER JOIN mapel_enrollment_keys k2 
                ON k1.mapel_id = k2.mapel_id 
               AND k1.guru_id = k2.guru_id 
               AND (k1.kelas_id = k2.kelas_id OR (k1.kelas_id IS NULL AND k2.kelas_id IS NULL))
               AND k1.id > k2.id
            ";
            $this->db->exec($sql);
        } catch (\Throwable $e) {}
    }

    public function syncKeysWithSchedule() {
        $this->ensureEnrollmentTables();
        $this->deduplicateKeys();
        try {
            $sql = "
                SELECT DISTINCT j.mapel_id, j.kelas_id, j.guru_id, m.nama_mapel, k.nama_kelas
                FROM jadwal j
                JOIN mata_pelajaran m ON j.mapel_id = m.id
                JOIN kelas k ON j.kelas_id = k.id
                WHERE j.mapel_id IS NOT NULL AND j.kelas_id IS NOT NULL AND j.guru_id IS NOT NULL AND j.guru_id > 0
            ";
            $jadwalPairs = $this->db->query($sql)->fetchAll();

            foreach ($jadwalPairs as $jp) {
                $mId = (int)$jp['mapel_id'];
                $kId = (int)$jp['kelas_id'];
                $gId = (int)$jp['guru_id'];

                if ($mId <= 0 || $kId <= 0 || $gId <= 0) continue;

                $stmtChk = $this->db->prepare("SELECT id, guru_id FROM mapel_enrollment_keys WHERE mapel_id = ? AND guru_id = ? AND (kelas_id = ? OR (kelas_id IS NULL AND ? IS NULL))");
                $stmtChk->execute([$mId, $gId, $kId, $kId]);
                $existingKey = $stmtChk->fetch();

                if (!$existingKey) {
                    $mText = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $jp['nama_mapel']), 0, 5));
                    $kText = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $jp['nama_kelas']), 0, 6));
                    $rand = rand(100, 999);
                    $smartKey = ($mText ?: 'MPL') . '-' . ($kText ?: 'KLS') . '-' . $rand;

                    $this->setMapelEnrollmentKey($mId, $gId, $smartKey, $kId);
                }
            }
            $this->deduplicateKeys();
        } catch (\Throwable $e) {}
    }

    public function ensureGuruClassKeys($guru_id) {
        $this->syncKeysWithSchedule();
        $gId = (int)$guru_id;
        if ($gId <= 0) return;

        try {
            $sql = "
                SELECT DISTINCT j.mapel_id, j.kelas_id, m.nama_mapel, k.nama_kelas, k.tingkat
                FROM jadwal j
                JOIN mata_pelajaran m ON j.mapel_id = m.id
                JOIN kelas k ON j.kelas_id = k.id
                WHERE j.guru_id = ? AND j.mapel_id IS NOT NULL AND j.kelas_id IS NOT NULL
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$gId]);
            $assignedPairs = $stmt->fetchAll();

            foreach ($assignedPairs as $pair) {
                $mId = (int)$pair['mapel_id'];
                $kId = (int)$pair['kelas_id'];

                if ($mId <= 0 || $kId <= 0) continue;

                $chk = $this->db->prepare("SELECT id FROM mapel_enrollment_keys WHERE mapel_id = ? AND guru_id = ? AND (kelas_id = ? OR (kelas_id IS NULL AND ? IS NULL))");
                $chk->execute([$mId, $gId, $kId, $kId]);
                if (!$chk->fetch()) {
                    $mText = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $pair['nama_mapel']), 0, 5));
                    $kText = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $pair['nama_kelas']), 0, 6));
                    $rand = rand(100, 999);
                    $smartKey = ($mText ?: 'MPL') . '-' . ($kText ?: 'KLS') . '-' . $rand;

                    $this->setMapelEnrollmentKey($mId, $gId, $smartKey, $kId);
                }
            }
            $this->deduplicateKeys();
        } catch (\Throwable $e) {}
    }

    public function getMapelEnrollmentKeys($guru_id = null) {
        $this->syncKeysWithSchedule();
        $this->deduplicateKeys();
        $sql = "
            SELECT mek.*, m.nama_mapel, m.kode_mapel, g.nama_lengkap as nama_guru, g.nip, k.nama_kelas, k.tingkat, j.nama_jurusan,
            (SELECT COUNT(DISTINCT sme.siswa_id) FROM siswa_mapel_enrollment sme JOIN siswa s ON sme.siswa_id = s.id WHERE sme.mapel_id = mek.mapel_id AND sme.guru_id = mek.guru_id AND (mek.kelas_id IS NULL OR s.kelas_id = mek.kelas_id)) as total_siswa
            FROM mapel_enrollment_keys mek
            JOIN mata_pelajaran m ON mek.mapel_id = m.id
            JOIN guru g ON mek.guru_id = g.id
            LEFT JOIN kelas k ON mek.kelas_id = k.id
            LEFT JOIN jurusan j ON k.jurusan_id = j.id
            WHERE 1=1
        ";
        if ($guru_id) {
            $gId = (int)$guru_id;
            $sql .= " AND mek.guru_id = {$gId} AND (
                mek.kelas_id IS NULL 
                OR (mek.mapel_id, mek.kelas_id) IN (
                    SELECT mapel_id, kelas_id FROM jadwal WHERE guru_id = {$gId} AND mapel_id IS NOT NULL AND kelas_id IS NOT NULL
                    UNION
                    SELECT mapel_id, kelas_id FROM materi WHERE guru_id = {$gId} AND mapel_id IS NOT NULL AND kelas_id IS NOT NULL
                    UNION
                    SELECT mapel_id, kelas_id FROM tugas WHERE guru_id = {$gId} AND mapel_id IS NOT NULL AND kelas_id IS NOT NULL
                    UNION
                    SELECT me.mapel_id, s.kelas_id FROM siswa_mapel_enrollment me JOIN siswa s ON me.siswa_id = s.id WHERE me.guru_id = {$gId} AND s.kelas_id IS NOT NULL
                )
            )";
        }
        $sql .= " GROUP BY mek.id ORDER BY m.nama_mapel ASC, k.tingkat ASC, k.nama_kelas ASC, g.nama_lengkap ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function setMapelEnrollmentKey($mapel_id, $guru_id, $key, $kelas_id = null) {
        $this->ensureEnrollmentTables();
        $cleanKey = strtoupper(trim($key));
        $mId = (int)$mapel_id;
        $gId = (int)$guru_id;
        $kelasIdVal = !empty($kelas_id) ? (int)$kelas_id : null;
        
        if ($mId <= 0 || $gId <= 0 || empty($cleanKey)) {
            return false;
        }

        // Try exact match (mapel_id, guru_id, kelas_id)
        if ($kelasIdVal) {
            $chk = $this->db->prepare("SELECT id, enrollment_key, kelas_id FROM mapel_enrollment_keys WHERE mapel_id = ? AND guru_id = ? AND kelas_id = ?");
            $chk->execute([$mId, $gId, $kelasIdVal]);
            $row = $chk->fetch();
        } else {
            $chk = $this->db->prepare("SELECT id, enrollment_key, kelas_id FROM mapel_enrollment_keys WHERE mapel_id = ? AND guru_id = ? AND (kelas_id IS NULL OR kelas_id = 0)");
            $chk->execute([$mId, $gId]);
            $row = $chk->fetch();
        }

        // Fallback search if exact match wasn't found
        if (!$row) {
            $chk = $this->db->prepare("SELECT id, enrollment_key, kelas_id FROM mapel_enrollment_keys WHERE mapel_id = ? AND guru_id = ? ORDER BY id DESC LIMIT 1");
            $chk->execute([$mId, $gId]);
            $row = $chk->fetch();
        }

        $res = false;
        if ($row) {
            $stmt = $this->db->prepare("UPDATE mapel_enrollment_keys SET enrollment_key = ?, passcode = ?, kelas_id = ? WHERE id = ?");
            $res = $stmt->execute([$cleanKey, $cleanKey, $kelasIdVal ?: $row['kelas_id'], $row['id']]);

            if ($res) {
                if ($kelasIdVal) {
                    $delClass = $this->db->prepare("
                        DELETE sme FROM siswa_mapel_enrollment sme
                        JOIN siswa s ON sme.siswa_id = s.id
                        WHERE sme.mapel_id = ? AND sme.guru_id = ? AND s.kelas_id = ?
                    ");
                    $delClass->execute([$mId, $gId, $kelasIdVal]);
                }
                
                $delGlobal = $this->db->prepare("DELETE FROM siswa_mapel_enrollment WHERE mapel_id = ? AND guru_id = ?");
                $delGlobal->execute([$mId, $gId]);
            }
        } else {
            $stmt = $this->db->prepare("INSERT INTO mapel_enrollment_keys (mapel_id, guru_id, kelas_id, enrollment_key, passcode) VALUES (?, ?, ?, ?, ?)");
            $res = $stmt->execute([$mId, $gId, $kelasIdVal, $cleanKey, $cleanKey]);
        }

        $this->deduplicateKeys();
        return $res;
    }

    public function enrollSiswaByMapelKey($siswa_id, $key_input) {
        $this->ensureEnrollmentTables();
        $cleanKey = strtoupper(trim($key_input));

        if (empty($cleanKey)) {
            return ['status' => false, 'message' => 'Kode Akses / Key Mapel tidak boleh kosong.'];
        }

        $stmt = $this->db->prepare("
            SELECT mek.*, m.nama_mapel, g.nama_lengkap as nama_guru, k.nama_kelas, k.tingkat
            FROM mapel_enrollment_keys mek
            JOIN mata_pelajaran m ON mek.mapel_id = m.id
            JOIN guru g ON mek.guru_id = g.id
            LEFT JOIN kelas k ON mek.kelas_id = k.id
            WHERE UPPER(mek.enrollment_key) = ? OR UPPER(mek.passcode) = ?
        ");
        $stmt->execute([$cleanKey, $cleanKey]);
        $target = $stmt->fetch();

        if (!$target) {
            if (preg_match('/^MPL-(\d+)-(\d+)-(\d+)$/i', $cleanKey, $matches)) {
                $mId = intval($matches[1]);
                $gId = intval($matches[2]);
                $kId = intval($matches[3]);
                $this->setMapelEnrollmentKey($mId, $gId, $cleanKey, $kId);
                $stmt->execute([$cleanKey, $cleanKey]);
                $target = $stmt->fetch();
            }
        }

        if (!$target) {
            return ['status' => false, 'message' => 'Kode Akses / Key Mapel tidak valid. Silakan minta Key resmi dari Guru atau Admin.'];
        }

        // Verify student class targeting
        $stmtS = $this->db->prepare("SELECT s.*, k.nama_kelas, k.tingkat FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id WHERE s.id = ?");
        $stmtS->execute([(int)$siswa_id]);
        $siswa = $stmtS->fetch();

        if ($target['kelas_id'] && $siswa && $siswa['kelas_id']) {
            if ((int)$target['kelas_id'] !== (int)$siswa['kelas_id']) {
                $targetKelasName = $target['nama_kelas'] ?? ('Kelas ' . ($target['tingkat'] ?? ''));
                $siswaKelasName = $siswa['nama_kelas'] ?? ('Kelas ' . ($siswa['tingkat'] ?? ''));
                return [
                    'status' => false,
                    'message' => "⚠️ Gagal Mendaftar! Key Mapel '{$cleanKey}' ditujukan khusus untuk Rombel {$targetKelasName}. Kelas Anda saat ini adalah {$siswaKelasName}. Silakan gunakan Key Mapel yang sesuai dengan kelas Anda!"
                ];
            }
        }

        try {
            $targetKelasId = !empty($target['kelas_id']) ? (int)$target['kelas_id'] : null;

            if ($targetKelasId) {
                $chk = $this->db->prepare("SELECT id FROM siswa_mapel_enrollment WHERE siswa_id = ? AND mapel_id = ? AND guru_id = ? AND kelas_id = ?");
                $chk->execute([(int)$siswa_id, $target['mapel_id'], $target['guru_id'], $targetKelasId]);
            } else {
                $chk = $this->db->prepare("SELECT id FROM siswa_mapel_enrollment WHERE siswa_id = ? AND mapel_id = ? AND guru_id = ?");
                $chk->execute([(int)$siswa_id, $target['mapel_id'], $target['guru_id']]);
            }

            if (!$chk->fetch()) {
                $ins = $this->db->prepare("INSERT INTO siswa_mapel_enrollment (siswa_id, mapel_id, guru_id, kelas_id) VALUES (?, ?, ?, ?)");
                $ins->execute([(int)$siswa_id, $target['mapel_id'], $target['guru_id'], $targetKelasId]);
            }

            $kelasInfo = !empty($target['nama_kelas']) ? " [{$target['nama_kelas']}]" : '';

            return [
                'status' => true,
                'message' => 'Selamat! Anda berhasil terdaftar di Mata Pelajaran ' . $target['nama_mapel'] . $kelasInfo . ' bersama Guru ' . $target['nama_guru'] . '.',
                'enrollment' => $target
            ];
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Gagal mendaftar mapel: ' . $e->getMessage()];
        }
    }

    public function getSiswaEnrolledMapels($siswa_id) {
        $this->ensureEnrollmentTables();
        $stmt = $this->db->prepare("
            SELECT sme.*, m.nama_mapel, m.kode_mapel, COALESCE(g.nama_lengkap, 'Guru Pengampu') as nama_guru, k.nama_kelas, k.tingkat
            FROM siswa_mapel_enrollment sme
            JOIN mata_pelajaran m ON sme.mapel_id = m.id
            LEFT JOIN guru g ON sme.guru_id = g.id
            LEFT JOIN kelas k ON sme.kelas_id = k.id
            WHERE sme.siswa_id = ?
            ORDER BY m.nama_mapel ASC
        ");
        $stmt->execute([(int)$siswa_id]);
        return $stmt->fetchAll();
    }

    public function isSiswaEnrolledInMapel($siswa_id, $mapel_id, $guru_id = null) {
        $this->ensureEnrollmentTables();
        $sql = "SELECT id FROM siswa_mapel_enrollment WHERE siswa_id = ? AND mapel_id = ?";
        $params = [$siswa_id, $mapel_id];
        if ($guru_id) {
            $sql .= " AND guru_id = ?";
            $params[] = $guru_id;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    public function getEnrolledStudentsForMapel($mapel_id, $guru_id) {
        $this->ensureEnrollmentTables();
        $stmt = $this->db->prepare("
            SELECT s.*, k.nama_kelas, sme.enrolled_at
            FROM siswa_mapel_enrollment sme
            JOIN siswa s ON sme.siswa_id = s.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            WHERE sme.mapel_id = ? AND sme.guru_id = ?
            ORDER BY s.nama_lengkap ASC
        ");
        $stmt->execute([$mapel_id, $guru_id]);
        return $stmt->fetchAll();
    }

    public function unenrollSiswaFromMapel($siswa_id, $mapel_id, $guru_id) {
        $this->ensureEnrollmentTables();
        $stmt = $this->db->prepare("DELETE FROM siswa_mapel_enrollment WHERE siswa_id = ? AND mapel_id = ? AND guru_id = ?");
        return $stmt->execute([$siswa_id, $mapel_id, $guru_id]);
    }

    public function getEnrolledStudentsForGuru($guru_id, $mapel_id = null, $kelas_id = null, $jurusan_id = null, $search = null) {
        $this->ensureEnrollmentTables();
        $sql = "
            SELECT s.*, k.nama_kelas, j.nama_jurusan, m.nama_mapel, m.kode_mapel, sme.enrolled_at, sme.mapel_id, sme.guru_id
            FROM siswa_mapel_enrollment sme
            JOIN siswa s ON sme.siswa_id = s.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN jurusan j ON s.jurusan_id = j.id
            JOIN mata_pelajaran m ON sme.mapel_id = m.id
            WHERE sme.guru_id = ?
        ";
        $params = [(int)$guru_id];

        if (!empty($mapel_id)) {
            $sql .= " AND sme.mapel_id = ?";
            $params[] = (int)$mapel_id;
        }
        if (!empty($kelas_id)) {
            $sql .= " AND s.kelas_id = ?";
            $params[] = (int)$kelas_id;
        }
        if (!empty($jurusan_id)) {
            $sql .= " AND s.jurusan_id = ?";
            $params[] = (int)$jurusan_id;
        }
        if (!empty($search)) {
            $sql .= " AND (s.nama_lengkap LIKE ? OR s.nisn LIKE ? OR s.nis LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $sql .= " ORDER BY m.nama_mapel ASC, k.nama_kelas ASC, s.nama_lengkap ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
