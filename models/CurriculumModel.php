<?php
/**
 * CurriculumModel.php
 * Model Terpadu untuk Manajemen Kurikulum Dinamis, Fase, CP/TP, Komponen Nilai & E-Rapor Skalabel
 * E-Learning SMK Muthia Harapan Cicalengka
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class CurriculumModel extends BaseModel {

    public function __construct() {
        parent::__construct();
        $this->ensureCurriculumTables();
    }

    public function ensureCurriculumTables() {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        if (class_exists('Database')) {
            Database::ensureCustomTables();
        }
    }

    // =========================================================================
    // 1. MASTER KURIKULUM
    // =========================================================================

    public function getAllKurikulum($status = null) {
        $sql = "
            SELECT k.*,
                   (SELECT COUNT(*) FROM rombel_kurikulum rk WHERE rk.kurikulum_id = k.id) as total_rombel_terhubung,
                   (SELECT COUNT(*) FROM fase f WHERE f.kurikulum_id = k.id) as total_fase,
                   (SELECT COUNT(*) FROM capaian_pembelajaran cp WHERE cp.kurikulum_id = k.id) as total_cp,
                   (SELECT COUNT(*) FROM rapor_siswa rs WHERE rs.kurikulum_id = k.id) as total_rapor_terbit
            FROM kurikulum k
        ";
        $params = [];
        if ($status) {
            $sql .= " WHERE k.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY k.tahun_mulai DESC, k.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getKurikulumById($id) {
        $stmt = $this->db->prepare("SELECT * FROM kurikulum WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActiveKurikulum() {
        $stmt = $this->db->prepare("SELECT * FROM kurikulum WHERE status = 'aktif' ORDER BY tahun_mulai DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addKurikulum($data) {
        $kode = strtoupper(trim($data['kode'] ?? ''));
        $nama = trim($data['nama'] ?? '');
        $tahunMulai = (int)($data['tahun_mulai'] ?? date('Y'));
        $tahunSelesai = !empty($data['tahun_selesai']) ? (int)$data['tahun_selesai'] : null;
        $status = in_array($data['status'] ?? '', ['aktif', 'non-aktif', 'arsip']) ? $data['status'] : 'aktif';
        $deskripsi = trim($data['deskripsi'] ?? '');

        if (empty($kode) || empty($nama)) {
            return ['status' => false, 'message' => 'Kode dan Nama Kurikulum wajib diisi.'];
        }

        // Check duplicate code
        $chk = $this->db->prepare("SELECT id FROM kurikulum WHERE kode = ?");
        $chk->execute([$kode]);
        if ($chk->fetch()) {
            return ['status' => false, 'message' => "Kode Kurikulum '{$kode}' sudah digunakan. Gunakan kode lain."];
        }

        $stmt = $this->db->prepare("
            INSERT INTO kurikulum (kode, nama, tahun_mulai, tahun_selesai, status, deskripsi)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $res = $stmt->execute([$kode, $nama, $tahunMulai, $tahunSelesai, $status, $deskripsi]);
        $newId = $this->db->lastInsertId();

        if ($res && $newId) {
            // Automatically initialize default evaluation components
            $this->initDefaultKomponen($newId);
        }

        return ['status' => (bool)$res, 'id' => $newId, 'message' => 'Kurikulum baru berhasil didaftarkan.'];
    }

    public function updateKurikulum($id, $data) {
        $id = (int)$id;
        $kode = strtoupper(trim($data['kode'] ?? ''));
        $nama = trim($data['nama'] ?? '');
        $tahunMulai = (int)($data['tahun_mulai'] ?? date('Y'));
        $tahunSelesai = !empty($data['tahun_selesai']) ? (int)$data['tahun_selesai'] : null;
        $status = in_array($data['status'] ?? '', ['aktif', 'non-aktif', 'arsip']) ? $data['status'] : 'aktif';
        $deskripsi = trim($data['deskripsi'] ?? '');

        if (empty($kode) || empty($nama)) {
            return ['status' => false, 'message' => 'Kode dan Nama Kurikulum wajib diisi.'];
        }

        $chk = $this->db->prepare("SELECT id FROM kurikulum WHERE kode = ? AND id != ?");
        $chk->execute([$kode, $id]);
        if ($chk->fetch()) {
            return ['status' => false, 'message' => "Kode Kurikulum '{$kode}' sudah digunakan oleh kurikulum lain."];
        }

        $stmt = $this->db->prepare("
            UPDATE kurikulum
            SET kode = ?, nama = ?, tahun_mulai = ?, tahun_selesai = ?, status = ?, deskripsi = ?
            WHERE id = ?
        ");
        $res = $stmt->execute([$kode, $nama, $tahunMulai, $tahunSelesai, $status, $deskripsi, $id]);
        return ['status' => (bool)$res, 'message' => 'Data Kurikulum berhasil diperbarui.'];
    }

    public function deleteKurikulum($id) {
        $id = (int)$id;
        // Safety constraint: Never delete curriculum that has historical grade/rapor records
        $stmtRapor = $this->db->prepare("SELECT COUNT(*) FROM rapor_siswa WHERE kurikulum_id = ?");
        $stmtRapor->execute([$id]);
        $hasRapor = (int)$stmtRapor->fetchColumn();

        $stmtRombel = $this->db->prepare("SELECT COUNT(*) FROM rombel_kurikulum WHERE kurikulum_id = ?");
        $stmtRombel->execute([$id]);
        $hasRombel = (int)$stmtRombel->fetchColumn();

        if ($hasRapor > 0 || $hasRombel > 0) {
            return [
                'status' => false,
                'message' => "⚠️ Kurikulum tidak boleh dihapus karena telah memiliki {$hasRapor} riwayat rapor siswa dan {$hasRombel} rombel terhubung. Silakan ubah status menjadi 'Non-Aktif' atau 'Arsip' untuk menjaga integritas data historis sekolah."
            ];
        }

        $stmt = $this->db->prepare("DELETE FROM kurikulum WHERE id = ?");
        $res = $stmt->execute([$id]);
        return ['status' => (bool)$res, 'message' => 'Kurikulum berhasil dihapus.'];
    }

    // =========================================================================
    // 2. FASE / TINGKAT KURIKULUM
    // =========================================================================

    public function getFaseByKurikulum($kurikulumId) {
        $stmt = $this->db->prepare("
            SELECT f.*, k.nama as nama_kurikulum, k.kode as kode_kurikulum,
                   (SELECT COUNT(*) FROM capaian_pembelajaran cp WHERE cp.fase_id = f.id) as total_cp,
                   (SELECT COUNT(*) FROM rombel_kurikulum rk WHERE rk.fase_id = f.id) as total_rombel
            FROM fase f
            JOIN kurikulum k ON f.kurikulum_id = k.id
            WHERE f.kurikulum_id = ?
            ORDER BY f.kode ASC, f.id ASC
        ");
        $stmt->execute([(int)$kurikulumId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllFase() {
        $stmt = $this->db->query("
            SELECT f.*, k.nama as nama_kurikulum, k.kode as kode_kurikulum
            FROM fase f
            JOIN kurikulum k ON f.kurikulum_id = k.id
            ORDER BY k.nama ASC, f.kode ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addFase($data) {
        $kurikulumId = (int)($data['kurikulum_id'] ?? 0);
        $kode = strtoupper(trim($data['kode'] ?? ''));
        $nama = trim($data['nama'] ?? '');
        $tingkatKelas = trim($data['tingkat_kelas'] ?? '');
        $keterangan = trim($data['keterangan'] ?? '');

        if ($kurikulumId <= 0 || empty($kode) || empty($nama)) {
            return ['status' => false, 'message' => 'Kurikulum, Kode Fase, dan Nama Fase wajib diisi.'];
        }

        $chk = $this->db->prepare("SELECT id FROM fase WHERE kurikulum_id = ? AND kode = ?");
        $chk->execute([$kurikulumId, $kode]);
        if ($chk->fetch()) {
            return ['status' => false, 'message' => "Kode Fase '{$kode}' sudah ada pada kurikulum ini."];
        }

        $stmt = $this->db->prepare("
            INSERT INTO fase (kurikulum_id, kode, nama, tingkat_kelas, keterangan)
            VALUES (?, ?, ?, ?, ?)
        ");
        $res = $stmt->execute([$kurikulumId, $kode, $nama, $tingkatKelas, $keterangan]);
        return ['status' => (bool)$res, 'message' => 'Fase / Tingkat baru berhasil ditambahkan.'];
    }

    public function updateFase($id, $data) {
        $id = (int)$id;
        $kode = strtoupper(trim($data['kode'] ?? ''));
        $nama = trim($data['nama'] ?? '');
        $tingkatKelas = trim($data['tingkat_kelas'] ?? '');
        $keterangan = trim($data['keterangan'] ?? '');
        $kurikulumId = !empty($data['kurikulum_id']) ? (int)$data['kurikulum_id'] : null;

        $stmtKur = $this->db->prepare("SELECT kurikulum_id FROM fase WHERE id = ?");
        $stmtKur->execute([$id]);
        $currentKurId = $stmtKur->fetchColumn();

        if (!$currentKurId || empty($kode) || empty($nama)) {
            return ['status' => false, 'message' => 'Data tidak lengkap atau Fase tidak ditemukan.'];
        }

        $targetKurId = $kurikulumId ?: (int)$currentKurId;

        $chk = $this->db->prepare("SELECT id FROM fase WHERE kurikulum_id = ? AND kode = ? AND id != ?");
        $chk->execute([$targetKurId, $kode, $id]);
        if ($chk->fetch()) {
            return ['status' => false, 'message' => "Kode Fase '{$kode}' sudah ada pada kurikulum ini."];
        }

        $stmt = $this->db->prepare("
            UPDATE fase
            SET kurikulum_id = ?, kode = ?, nama = ?, tingkat_kelas = ?, keterangan = ?
            WHERE id = ?
        ");
        $res = $stmt->execute([$targetKurId, $kode, $nama, $tingkatKelas, $keterangan, $id]);
        return ['status' => (bool)$res, 'message' => 'Fase berhasil diperbarui.'];
    }

    public function deleteFase($id) {
        $id = (int)$id;

        // Safety check: ensure fase is not currently in use by CP, Rombel, or Struktur Mapel
        $stmtCp = $this->db->prepare("SELECT COUNT(*) FROM capaian_pembelajaran WHERE fase_id = ?");
        $stmtCp->execute([$id]);
        $cpCount = (int)$stmtCp->fetchColumn();

        $stmtRk = $this->db->prepare("SELECT COUNT(*) FROM rombel_kurikulum WHERE fase_id = ?");
        $stmtRk->execute([$id]);
        $rkCount = (int)$stmtRk->fetchColumn();

        if ($cpCount > 0 || $rkCount > 0) {
            return [
                'status' => false,
                'message' => "⚠️ Fase ini tidak dapat dihapus karena masih digunakan oleh {$cpCount} Capaian Pembelajaran dan {$rkCount} Rombel Kelas."
            ];
        }

        $stmt = $this->db->prepare("DELETE FROM fase WHERE id = ?");
        $res = $stmt->execute([$id]);
        return ['status' => (bool)$res, 'message' => 'Fase berhasil dihapus.'];
    }

    // =========================================================================
    // 3. ROMBEL KURIKULUM (PENETAPAN KURIKULUM KELAS + VALIDASI BENTROK)
    // =========================================================================

    public function getRombelKurikulum($tahunAjaranId = null, $rombelId = null) {
        $sql = "
            SELECT rk.*,
                   k.nama_kelas, k.tingkat, j.nama_jurusan,
                   ta.tahun as nama_tahun, ta.tahun_ajaran, ta.semester, ta.is_active as ta_is_active,
                   kur.nama as nama_kurikulum, kur.kode as kode_kurikulum, kur.status as status_kurikulum,
                   f.kode as kode_fase, f.nama as nama_fase
            FROM rombel_kurikulum rk
            JOIN kelas k ON rk.rombel_id = k.id
            LEFT JOIN jurusan j ON k.jurusan_id = j.id
            JOIN tahun_ajaran ta ON rk.tahun_ajaran_id = ta.id
            JOIN kurikulum kur ON rk.kurikulum_id = kur.id
            LEFT JOIN fase f ON rk.fase_id = f.id
            WHERE 1=1
        ";
        $params = [];
        if ($tahunAjaranId) {
            $sql .= " AND rk.tahun_ajaran_id = ?";
            $params[] = (int)$tahunAjaranId;
        }
        if ($rombelId) {
            $sql .= " AND rk.rombel_id = ?";
            $params[] = (int)$rombelId;
        }
        $sql .= " ORDER BY ta.id DESC, k.tingkat ASC, k.nama_kelas ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validasi & Penetapan Kurikulum Rombel
     * Aturan:
     * 1. Cegah duplikasi persis (tahun_ajaran_id + rombel_id + kurikulum_id)
     * 2. Default: Satu rombel HANYA boleh memiliki SATU kurikulum AKTIF pada satu tahun ajaran!
     */
    public function assignRombelKurikulum($data) {
        $rombelId = (int)($data['rombel_id'] ?? 0);
        $tahunAjaranId = (int)($data['tahun_ajaran_id'] ?? 0);
        $kurikulumId = (int)($data['kurikulum_id'] ?? 0);
        $faseId = !empty($data['fase_id']) ? (int)$data['fase_id'] : null;
        $status = in_array($data['status'] ?? '', ['aktif', 'selesai', 'non-aktif']) ? $data['status'] : 'aktif';

        if ($rombelId <= 0 || $tahunAjaranId <= 0 || $kurikulumId <= 0) {
            return ['status' => false, 'message' => 'Rombel Kelas, Tahun Ajaran, dan Kurikulum wajib dipilih.'];
        }

        // VALIDASI BENTROK 1: Kombinasi tahun_ajaran + rombel + kurikulum
        $chkDuplicate = $this->db->prepare("
            SELECT id FROM rombel_kurikulum
            WHERE tahun_ajaran_id = ? AND rombel_id = ? AND kurikulum_id = ?
        ");
        $chkDuplicate->execute([$tahunAjaranId, $rombelId, $kurikulumId]);
        if ($chkDuplicate->fetch()) {
            return [
                'status' => false,
                'message' => '⚠️ Data Duplikat! Rombel ini sudah terdaftar dengan kurikulum yang sama pada Tahun Ajaran ini.'
            ];
        }

        // VALIDASI BENTROK 2 (ATURAN UTAMA): Hanya boleh ada 1 kurikulum aktif per rombel pada 1 tahun ajaran!
        if ($status === 'aktif') {
            $chkActive = $this->db->prepare("
                SELECT rk.id, kur.nama as nama_kurikulum, k.nama_kelas, ta.tahun_ajaran
                FROM rombel_kurikulum rk
                JOIN kurikulum kur ON rk.kurikulum_id = kur.id
                JOIN kelas k ON rk.rombel_id = k.id
                JOIN tahun_ajaran ta ON rk.tahun_ajaran_id = ta.id
                WHERE rk.tahun_ajaran_id = ? AND rk.rombel_id = ? AND rk.status = 'aktif'
            ");
            $chkActive->execute([$tahunAjaranId, $rombelId]);
            $existingActive = $chkActive->fetch(PDO::FETCH_ASSOC);

            if ($existingActive) {
                return [
                    'status' => false,
                    'message' => "⚠️ BENTROK KURIKULUM DITOLAK: Rombel {$existingActive['nama_kelas']} pada Tahun Ajaran {$existingActive['tahun_ajaran']} sudah menggunakan '{$existingActive['nama_kurikulum']}' sebagai Kurikulum Aktif! Satu rombel hanya diizinkan memiliki 1 kurikulum aktif dalam satu periode tahun ajaran."
                ];
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO rombel_kurikulum (rombel_id, tahun_ajaran_id, kurikulum_id, fase_id, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $res = $stmt->execute([$rombelId, $tahunAjaranId, $kurikulumId, $faseId, $status]);
        return ['status' => (bool)$res, 'message' => 'Kurikulum rombel kelas berhasil disimpan.'];
    }

    public function updateRombelKurikulum($id, $data) {
        $id = (int)$id;
        $kurikulumId = (int)($data['kurikulum_id'] ?? 0);
        $faseId = !empty($data['fase_id']) ? (int)$data['fase_id'] : null;
        $status = in_array($data['status'] ?? '', ['aktif', 'selesai', 'non-aktif']) ? $data['status'] : 'aktif';

        $stmtGet = $this->db->prepare("SELECT rombel_id, tahun_ajaran_id FROM rombel_kurikulum WHERE id = ?");
        $stmtGet->execute([$id]);
        $curr = $stmtGet->fetch(PDO::FETCH_ASSOC);

        if (!$curr) {
            return ['status' => false, 'message' => 'Data penetapan kurikulum rombel tidak ditemukan.'];
        }

        // Validate conflict on active status
        if ($status === 'aktif') {
            $chkActive = $this->db->prepare("
                SELECT rk.id, kur.nama as nama_kurikulum
                FROM rombel_kurikulum rk
                JOIN kurikulum kur ON rk.kurikulum_id = kur.id
                WHERE rk.tahun_ajaran_id = ? AND rk.rombel_id = ? AND rk.status = 'aktif' AND rk.id != ?
            ");
            $chkActive->execute([$curr['tahun_ajaran_id'], $curr['rombel_id'], $id]);
            $existingActive = $chkActive->fetch(PDO::FETCH_ASSOC);

            if ($existingActive) {
                return [
                    'status' => false,
                    'message' => "⚠️ Gagal mengaktifkan! Rombel ini sudah memiliki kurikulum aktif: '{$existingActive['nama_kurikulum']}' pada tahun ajaran tersebut."
                ];
            }
        }

        $stmt = $this->db->prepare("
            UPDATE rombel_kurikulum
            SET kurikulum_id = ?, fase_id = ?, status = ?
            WHERE id = ?
        ");
        $res = $stmt->execute([$kurikulumId, $faseId, $status, $id]);
        return ['status' => (bool)$res, 'message' => 'Data penetapan kurikulum rombel berhasil diperbarui.'];
    }

    public function deleteRombelKurikulum($id) {
        $id = (int)$id;
        $stmt = $this->db->prepare("DELETE FROM rombel_kurikulum WHERE id = ?");
        $res = $stmt->execute([$id]);
        return ['status' => (bool)$res, 'message' => 'Penetapan kurikulum rombel berhasil dihapus.'];
    }

    public function getActiveKurikulumForRombel($rombelId, $tahunAjaranId = null) {
        $rombelId = (int)$rombelId;
        if (!$tahunAjaranId) {
            $tahunAjaranId = (int)$this->db->query("SELECT id FROM tahun_ajaran WHERE is_active = 1 LIMIT 1")->fetchColumn();
        }

        $stmt = $this->db->prepare("
            SELECT rk.*,
                   kur.nama as nama_kurikulum, kur.kode as kode_kurikulum, kur.status as status_kurikulum,
                   f.kode as kode_fase, f.nama as nama_fase, f.tingkat_kelas
            FROM rombel_kurikulum rk
            JOIN kurikulum kur ON rk.kurikulum_id = kur.id
            LEFT JOIN fase f ON rk.fase_id = f.id
            WHERE rk.rombel_id = ? AND rk.tahun_ajaran_id = ? AND rk.status = 'aktif'
            LIMIT 1
        ");
        $stmt->execute([$rombelId, $tahunAjaranId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fallback to latest registered curriculum if not explicitly marked
        if (!$row) {
            $stmtFallback = $this->db->prepare("
                SELECT rk.*,
                       kur.nama as nama_kurikulum, kur.kode as kode_kurikulum, kur.status as status_kurikulum,
                       f.kode as kode_fase, f.nama as nama_fase, f.tingkat_kelas
                FROM rombel_kurikulum rk
                JOIN kurikulum kur ON rk.kurikulum_id = kur.id
                LEFT JOIN fase f ON rk.fase_id = f.id
                WHERE rk.rombel_id = ?
                ORDER BY rk.id DESC
                LIMIT 1
            ");
            $stmtFallback->execute([$rombelId]);
            $row = $stmtFallback->fetch(PDO::FETCH_ASSOC);
        }

        // Global default fallback
        if (!$row) {
            $defaultKur = $this->getActiveKurikulum();
            $row = [
                'kurikulum_id' => $defaultKur['id'] ?? 1,
                'nama_kurikulum' => $defaultKur['nama'] ?? 'Kurikulum Merdeka SMK',
                'kode_kurikulum' => $defaultKur['kode'] ?? 'KMDK',
                'fase_id' => 1,
                'kode_fase' => 'E',
                'nama_fase' => 'Fase E (Kelas X)'
            ];
        }

        return $row;
    }

    // =========================================================================
    // 4. STRUKTUR MATA PELAJARAN PER KURIKULUM
    // =========================================================================

    public function getStrukturMapel($kurikulumId, $jurusanId = null) {
        $sql = "
            SELECT km.*, mp.nama_mapel, mp.kode_mapel, j.nama_jurusan, f.kode as kode_fase, f.nama as nama_fase
            FROM kurikulum_mapel km
            JOIN mata_pelajaran mp ON km.mapel_id = mp.id
            LEFT JOIN jurusan j ON km.jurusan_id = j.id
            LEFT JOIN fase f ON km.fase_id = f.id
            WHERE km.kurikulum_id = ?
        ";
        $params = [(int)$kurikulumId];
        if ($jurusanId) {
            $sql .= " AND (km.jurusan_id = ? OR km.jurusan_id IS NULL)";
            $params[] = (int)$jurusanId;
        }
        $sql .= " ORDER BY km.kelompok_mapel ASC, mp.nama_mapel ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addStrukturMapel($data) {
        $kurikulumId = (int)($data['kurikulum_id'] ?? 0);
        $mapelId = (int)($data['mapel_id'] ?? 0);
        $faseId = !empty($data['fase_id']) ? (int)$data['fase_id'] : null;
        $tingkat = trim($data['tingkat'] ?? 'X');
        $jurusanId = !empty($data['jurusan_id']) ? (int)$data['jurusan_id'] : null;
        $kelompok = trim($data['kelompok_mapel'] ?? 'Kejuruan');
        $alokasiJp = (int)($data['alokasi_jp'] ?? 2);
        $kkm = (float)($data['kkm'] ?? 75.0);

        if ($kurikulumId <= 0 || $mapelId <= 0) {
            return ['status' => false, 'message' => 'Kurikulum dan Mata Pelajaran wajib ditentukan.'];
        }

        // Check if mapel already mapped for this curriculum and grade
        $chk = $this->db->prepare("
            SELECT id FROM kurikulum_mapel 
            WHERE kurikulum_id = ? AND mapel_id = ? AND (tingkat = ? OR (tingkat IS NULL AND ? = ''))
        ");
        $chk->execute([$kurikulumId, $mapelId, $tingkat, $tingkat]);
        $existingId = $chk->fetchColumn();

        if ($existingId) {
            return $this->updateStrukturMapel($existingId, [
                'fase_id' => $faseId,
                'tingkat' => $tingkat,
                'kelompok_mapel' => $kelompok,
                'alokasi_jp' => $alokasiJp,
                'kkm' => $kkm
            ]);
        }

        $stmt = $this->db->prepare("
            INSERT INTO kurikulum_mapel (kurikulum_id, mapel_id, fase_id, tingkat, jurusan_id, kelompok_mapel, alokasi_jp, kkm, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $res = $stmt->execute([$kurikulumId, $mapelId, $faseId, $tingkat, $jurusanId, $kelompok, $alokasiJp, $kkm]);
        return ['status' => (bool)$res, 'message' => 'Struktur mata pelajaran kurikulum berhasil disimpan.'];
    }

    public function getStrukturMapelById($id) {
        $stmt = $this->db->prepare("
            SELECT km.*, mp.nama_mapel, mp.kode_mapel, kur.nama as nama_kurikulum, f.nama as nama_fase
            FROM kurikulum_mapel km
            JOIN mata_pelajaran mp ON km.mapel_id = mp.id
            JOIN kurikulum kur ON km.kurikulum_id = kur.id
            LEFT JOIN fase f ON km.fase_id = f.id
            WHERE km.id = ?
        ");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStrukturMapel($id, $data) {
        $id = (int)$id;
        $faseId = !empty($data['fase_id']) ? (int)$data['fase_id'] : null;
        $tingkat = trim($data['tingkat'] ?? 'X');
        $kelompok = trim($data['kelompok_mapel'] ?? 'Kejuruan');
        $alokasiJp = (int)($data['alokasi_jp'] ?? 2);
        $kkm = (float)($data['kkm'] ?? 75.0);

        $stmt = $this->db->prepare("
            UPDATE kurikulum_mapel
            SET fase_id = ?, tingkat = ?, kelompok_mapel = ?, alokasi_jp = ?, kkm = ?
            WHERE id = ?
        ");
        $res = $stmt->execute([$faseId, $tingkat, $kelompok, $alokasiJp, $kkm, $id]);
        return ['status' => (bool)$res, 'message' => 'Konfigurasi struktur mata pelajaran berhasil diperbarui.'];
    }

    public function deleteStrukturMapel($id) {
        $stmt = $this->db->prepare("DELETE FROM kurikulum_mapel WHERE id = ?");
        $res = $stmt->execute([(int)$id]);
        return ['status' => (bool)$res, 'message' => 'Mata pelajaran berhasil dilepas dari kurikulum.'];
    }

    // =========================================================================
    // 5. CAPAIAN PEMBELAJARAN (CP) & TUJUAN PEMBELAJARAN (TP)
    // =========================================================================

    public function getCPList($kurikulumId = null, $mapelId = null, $faseId = null) {
        $sql = "
            SELECT cp.*,
                   kur.nama as nama_kurikulum, kur.kode as kode_kurikulum,
                   mp.nama_mapel, mp.kode_mapel,
                   f.kode as kode_fase, f.nama as nama_fase,
                   (SELECT COUNT(*) FROM tujuan_pembelajaran tp WHERE tp.cp_id = cp.id) as total_tp
            FROM capaian_pembelajaran cp
            JOIN kurikulum kur ON cp.kurikulum_id = kur.id
            JOIN mata_pelajaran mp ON cp.mapel_id = mp.id
            LEFT JOIN fase f ON cp.fase_id = f.id
            WHERE 1=1
        ";
        $params = [];
        if ($kurikulumId) {
            $sql .= " AND cp.kurikulum_id = ?";
            $params[] = (int)$kurikulumId;
        }
        if ($mapelId) {
            $sql .= " AND cp.mapel_id = ?";
            $params[] = (int)$mapelId;
        }
        if ($faseId) {
            $sql .= " AND cp.fase_id = ?";
            $params[] = (int)$faseId;
        }
        $sql .= " ORDER BY mp.nama_mapel ASC, cp.kode_cp ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCP($data) {
        $kurikulumId = (int)($data['kurikulum_id'] ?? 0);
        $mapelId = (int)($data['mapel_id'] ?? 0);
        $faseId = !empty($data['fase_id']) ? (int)$data['fase_id'] : null;
        $kodeCp = strtoupper(trim($data['kode_cp'] ?? ''));
        $elemen = trim($data['elemen'] ?? '');
        $deskripsi = trim($data['deskripsi'] ?? '');

        if ($kurikulumId <= 0 || $mapelId <= 0 || empty($kodeCp) || empty($deskripsi)) {
            return ['status' => false, 'message' => 'Kurikulum, Mapel, Kode CP, dan Deskripsi Capaian wajib diisi.'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO capaian_pembelajaran (kurikulum_id, mapel_id, fase_id, kode_cp, elemen, deskripsi)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $res = $stmt->execute([$kurikulumId, $mapelId, $faseId, $kodeCp, $elemen, $deskripsi]);
        return ['status' => (bool)$res, 'message' => 'Capaian Pembelajaran (CP) berhasil ditambahkan.'];
    }

    public function getCPById($id) {
        $stmt = $this->db->prepare("
            SELECT cp.*, kur.nama as nama_kurikulum, kur.kode as kode_kurikulum,
                   mp.nama_mapel, mp.kode_mapel, f.nama as nama_fase, f.kode as kode_fase
            FROM capaian_pembelajaran cp
            JOIN kurikulum kur ON cp.kurikulum_id = kur.id
            JOIN mata_pelajaran mp ON cp.mapel_id = mp.id
            LEFT JOIN fase f ON cp.fase_id = f.id
            WHERE cp.id = ?
        ");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCP($id, $data) {
        $id = (int)$id;
        $kodeCp = strtoupper(trim($data['kode_cp'] ?? ''));
        $elemen = trim($data['elemen'] ?? '');
        $deskripsi = trim($data['deskripsi'] ?? '');
        $faseId = !empty($data['fase_id']) ? (int)$data['fase_id'] : null;

        $updates = ["kode_cp = ?", "elemen = ?", "deskripsi = ?", "fase_id = ?"];
        $params = [$kodeCp, $elemen, $deskripsi, $faseId];

        if (!empty($data['kurikulum_id'])) {
            $updates[] = "kurikulum_id = ?";
            $params[] = (int)$data['kurikulum_id'];
        }
        if (!empty($data['mapel_id'])) {
            $updates[] = "mapel_id = ?";
            $params[] = (int)$data['mapel_id'];
        }

        $params[] = $id;
        $stmt = $this->db->prepare("UPDATE capaian_pembelajaran SET " . implode(', ', $updates) . " WHERE id = ?");
        $res = $stmt->execute($params);
        return ['status' => (bool)$res, 'message' => 'Capaian Pembelajaran (CP) berhasil diperbarui.'];
    }

    public function deleteCP($id) {
        $id = (int)$id;
        // Clean up child TPs first to ensure database consistency
        $this->db->prepare("DELETE FROM tujuan_pembelajaran WHERE cp_id = ?")->execute([$id]);
        $stmt = $this->db->prepare("DELETE FROM capaian_pembelajaran WHERE id = ?");
        $res = $stmt->execute([$id]);
        return ['status' => (bool)$res, 'message' => 'Capaian Pembelajaran beserta TP turunannya berhasil dihapus.'];
    }

    public function getTPList($cpId = null) {
        $sql = "
            SELECT tp.*, cp.kode_cp, cp.elemen, cp.kurikulum_id, cp.mapel_id, mp.nama_mapel, kur.kode as kode_kurikulum
            FROM tujuan_pembelajaran tp
            JOIN capaian_pembelajaran cp ON tp.cp_id = cp.id
            JOIN kurikulum kur ON cp.kurikulum_id = kur.id
            JOIN mata_pelajaran mp ON cp.mapel_id = mp.id
            WHERE 1=1
        ";
        $params = [];
        if ($cpId) {
            $sql .= " AND tp.cp_id = ?";
            $params[] = (int)$cpId;
        }
        $sql .= " ORDER BY tp.kode_tp ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTPById($id) {
        $stmt = $this->db->prepare("
            SELECT tp.*, cp.kode_cp, cp.elemen, cp.kurikulum_id, cp.mapel_id, mp.nama_mapel
            FROM tujuan_pembelajaran tp
            JOIN capaian_pembelajaran cp ON tp.cp_id = cp.id
            JOIN mata_pelajaran mp ON cp.mapel_id = mp.id
            WHERE tp.id = ?
        ");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addTP($data) {
        $cpId = (int)($data['cp_id'] ?? 0);
        $kodeTp = strtoupper(trim($data['kode_tp'] ?? ''));
        $materiPokok = trim($data['materi_pokok'] ?? '');
        $deskripsi = trim($data['deskripsi'] ?? '');

        if ($cpId <= 0 || empty($kodeTp) || empty($deskripsi)) {
            return ['status' => false, 'message' => 'Induk CP, Kode TP, dan Deskripsi Tujuan Pembelajaran wajib diisi.'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO tujuan_pembelajaran (cp_id, kode_tp, materi_pokok, deskripsi)
            VALUES (?, ?, ?, ?)
        ");
        $res = $stmt->execute([$cpId, $kodeTp, $materiPokok, $deskripsi]);
        return ['status' => (bool)$res, 'message' => 'Tujuan Pembelajaran (TP) berhasil ditambahkan.'];
    }

    public function updateTP($id, $data) {
        $id = (int)$id;
        $kodeTp = strtoupper(trim($data['kode_tp'] ?? ''));
        $materiPokok = trim($data['materi_pokok'] ?? '');
        $deskripsi = trim($data['deskripsi'] ?? '');

        $updates = ["kode_tp = ?", "materi_pokok = ?", "deskripsi = ?"];
        $params = [$kodeTp, $materiPokok, $deskripsi];

        if (!empty($data['cp_id'])) {
            $updates[] = "cp_id = ?";
            $params[] = (int)$data['cp_id'];
        }

        $params[] = $id;
        $stmt = $this->db->prepare("UPDATE tujuan_pembelajaran SET " . implode(', ', $updates) . " WHERE id = ?");
        $res = $stmt->execute($params);
        return ['status' => (bool)$res, 'message' => 'Tujuan Pembelajaran (TP) berhasil diperbarui.'];
    }

    public function deleteTP($id) {
        $stmt = $this->db->prepare("DELETE FROM tujuan_pembelajaran WHERE id = ?");
        $res = $stmt->execute([(int)$id]);
        return ['status' => (bool)$res, 'message' => 'Tujuan Pembelajaran berhasil dihapus.'];
    }

    // =========================================================================
    // 6. KOMPONEN & BOBOT PENILAIAN DINAMIS
    // =========================================================================

    public function initDefaultKomponen($kurikulumId) {
        $defaults = [
            ['kode' => 'tugas', 'nama' => 'Tugas Mandiri / Terstruktur', 'bobot' => 20.00, 'ket' => 'Penugasan portofolio KBM harian siswa.'],
            ['kode' => 'quiz',  'nama' => 'Kuis / Formatif Harian',     'bobot' => 20.00, 'ket' => 'Evaluasi formatif pemahaman tujuan pembelajaran.'],
            ['kode' => 'uts',   'nama' => 'Sumatif Tengah Semester (STS)', 'bobot' => 30.00, 'ket' => 'Ujian evaluasi capaian tengah semester.'],
            ['kode' => 'uas',   'nama' => 'Sumatif Akhir Semester (SAS)',  'bobot' => 30.00, 'ket' => 'Ujian akhir evaluasi kompetensi semester.']
        ];
        foreach ($defaults as $d) {
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO komponen_penilaian (kurikulum_id, nama_komponen, kode_komponen, bobot_persen, is_active, deskripsi)
                VALUES (?, ?, ?, ?, 1, ?)
            ");
            $stmt->execute([$kurikulumId, $d['nama'], $d['kode'], $d['bobot'], $d['ket']]);
        }
    }

    public function getKomponenPenilaian($kurikulumId) {
        $stmt = $this->db->prepare("
            SELECT * FROM komponen_penilaian
            WHERE kurikulum_id = ? AND is_active = 1
            ORDER BY id ASC
        ");
        $stmt->execute([(int)$kurikulumId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fallback default if empty
        if (empty($rows)) {
            return [
                ['kode_komponen' => 'tugas', 'nama_komponen' => 'Tugas', 'bobot_persen' => 20.0],
                ['kode_komponen' => 'quiz',  'nama_komponen' => 'Kuis',  'bobot_persen' => 20.0],
                ['kode_komponen' => 'uts',   'nama_komponen' => 'UTS/STS', 'bobot_persen' => 30.0],
                ['kode_komponen' => 'uas',   'nama_komponen' => 'UAS/SAS', 'bobot_persen' => 30.0]
            ];
        }
        return $rows;
    }

    public function saveKomponenPenilaian($kurikulumId, $komponenList) {
        $kurId = (int)$kurikulumId;
        $totalBobot = 0;
        foreach ($komponenList as $k) {
            $totalBobot += (float)($k['bobot_persen'] ?? 0);
        }

        if (abs($totalBobot - 100.0) > 0.5) {
            return [
                'status' => false,
                'message' => "⚠️ Total bobot seluruh komponen penilaian harus tepat 100%! (Total saat ini: {$totalBobot}%)"
            ];
        }

        $this->db->beginTransaction();
        try {
            // Delete old components for curriculum
            $stmtDel = $this->db->prepare("DELETE FROM komponen_penilaian WHERE kurikulum_id = ?");
            $stmtDel->execute([$kurId]);

            $stmtIns = $this->db->prepare("
                INSERT INTO komponen_penilaian (kurikulum_id, nama_komponen, kode_komponen, bobot_persen, is_active, deskripsi)
                VALUES (?, ?, ?, ?, 1, ?)
            ");

            foreach ($komponenList as $k) {
                $stmtIns->execute([
                    $kurId,
                    trim($k['nama_komponen']),
                    strtolower(trim($k['kode_komponen'])),
                    (float)$k['bobot_persen'],
                    trim($k['deskripsi'] ?? '')
                ]);
            }

            $this->db->commit();
            return ['status' => true, 'message' => 'Bobot komponen penilaian berhasil disimpan.'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => false, 'message' => 'Gagal menyimpan komponen: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // 7. E-RAPOR DIGITAL DINAMIS & SNAPSHOT HISTORIS
    // =========================================================================

    public function getRaporSiswa($siswaId, $tahunAjaranId = null, $semester = null) {
        $sId = (int)$siswaId;
        if (!$tahunAjaranId) {
            $activeTa = $this->db->query("SELECT id, tahun_ajaran, semester FROM tahun_ajaran WHERE is_active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $tahunAjaranId = $activeTa['id'] ?? 4;
            if (!$semester) {
                $semester = $activeTa['semester'] ?? 'Ganjil';
            }
        }
        if (!$semester) $semester = 'Ganjil';

        // 1. Get Rapor Header
        $stmtR = $this->db->prepare("
            SELECT rs.*, s.nama_lengkap, s.nis, s.nisn,
                   k.nama_kelas, k.tingkat, j.nama_jurusan,
                   ta.tahun_ajaran, ta.semester as semester_ta
            FROM rapor_siswa rs
            JOIN siswa s ON rs.siswa_id = s.id
            JOIN kelas k ON rs.rombel_id = k.id
            LEFT JOIN jurusan j ON k.jurusan_id = j.id
            JOIN tahun_ajaran ta ON rs.tahun_ajaran_id = ta.id
            WHERE rs.siswa_id = ? AND rs.tahun_ajaran_id = ? AND rs.semester = ?
        ");
        $stmtR->execute([$sId, $tahunAjaranId, $semester]);
        $header = $stmtR->fetch(PDO::FETCH_ASSOC);

        if (!$header) {
            // Auto generate/sync if not yet created
            $this->generateOrSyncRaporSiswa($sId, $tahunAjaranId, $semester);
            $stmtR->execute([$sId, $tahunAjaranId, $semester]);
            $header = $stmtR->fetch(PDO::FETCH_ASSOC);
        }

        if (!$header) return null;

        // 2. Get Dynamic Details (Subjects as rows)
        $stmtD = $this->db->prepare("
            SELECT rd.*, mp.kode_mapel, mp.nama_mapel as live_nama_mapel,
                   COALESCE(rd.mapel_nama_snapshot, mp.nama_mapel) as nama_mapel
            FROM rapor_nilai_detail rd
            JOIN mata_pelajaran mp ON rd.mapel_id = mp.id
            WHERE rd.rapor_id = ?
            ORDER BY mp.nama_mapel ASC
        ");
        $stmtD->execute([$header['id']]);
        $details = $stmtD->fetchAll(PDO::FETCH_ASSOC);

        $header['nilai_list'] = $details;
        return $header;
    }

    public function generateOrSyncRaporSiswa($siswaId, $tahunAjaranId, $semester) {
        $sId = (int)$siswaId;
        $taId = (int)$tahunAjaranId;

        // Get student info
        $stmtS = $this->db->prepare("SELECT s.*, k.tingkat, k.nama_kelas FROM siswa s JOIN kelas k ON s.kelas_id = k.id WHERE s.id = ?");
        $stmtS->execute([$sId]);
        $siswa = $stmtS->fetch(PDO::FETCH_ASSOC);
        if (!$siswa || empty($siswa['kelas_id'])) return false;

        $kId = (int)$siswa['kelas_id'];
        $kurInfo = $this->getActiveKurikulumForRombel($kId, $taId);

        // Check header
        $stmtH = $this->db->prepare("SELECT id FROM rapor_siswa WHERE siswa_id = ? AND tahun_ajaran_id = ? AND semester = ?");
        $stmtH->execute([$sId, $taId, $semester]);
        $raporId = $stmtH->fetchColumn();

        if (!$raporId) {
            $insH = $this->db->prepare("
                INSERT INTO rapor_siswa (siswa_id, tahun_ajaran_id, semester, rombel_id, kurikulum_id, fase_id, kurikulum_nama_snapshot, fase_nama_snapshot, tanggal_cetak, status, catatan_akademik)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'terverifikasi', 'Menunjukkan kemajuan belajar dan kedisiplinan yang baik.')
            ");
            $insH->execute([
                $sId,
                $taId,
                $semester,
                $kId,
                $kurInfo['kurikulum_id'] ?? 1,
                $kurInfo['fase_id'] ?? null,
                $kurInfo['nama_kurikulum'] ?? 'Kurikulum Merdeka SMK',
                $kurInfo['nama_fase'] ?? 'Fase E (Kelas X)'
            ]);
            $raporId = $this->db->lastInsertId();
        }

        // Pull enrolled mapels or existing legacy nilai_rapor
        $stmtLegacy = $this->db->prepare("
            SELECT nr.*, mp.nama_mapel, COALESCE(mp.kkm, 75) as kkm_mapel
            FROM nilai_rapor nr
            JOIN mata_pelajaran mp ON nr.mapel_id = mp.id
            WHERE nr.siswa_id = ?
        ");
        $stmtLegacy->execute([$sId]);
        $legacyRows = $stmtLegacy->fetchAll(PDO::FETCH_ASSOC);

        foreach ($legacyRows as $lr) {
            $mId = (int)$lr['mapel_id'];
            $akhir = (float)$lr['nilai_akhir'];
            $kkmVal = (float)$lr['kkm_mapel'];
            $predikat = 'B';
            if ($akhir >= 88) $predikat = 'A';
            elseif ($akhir >= 78) $predikat = 'B';
            elseif ($akhir >= 68) $predikat = 'C';
            else $predikat = 'D';

            $capaian = ($akhir >= $kkmVal)
                ? "Menunjukkan penguasaan sangat baik dalam menuntaskan seluruh tujuan pembelajaran {$lr['nama_mapel']}."
                : "Perlu bimbingan dan tindak lanjut remedial pada beberapa kompetensi dasar mata pelajaran {$lr['nama_mapel']}.";

            $stmtUpsert = $this->db->prepare("
                INSERT INTO rapor_nilai_detail (rapor_id, mapel_id, mapel_nama_snapshot, nilai_akhir, kkm, predikat, capaian_kompetensi)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    mapel_nama_snapshot = VALUES(mapel_nama_snapshot),
                    nilai_akhir = VALUES(nilai_akhir),
                    kkm = VALUES(kkm),
                    predikat = VALUES(predikat),
                    capaian_kompetensi = VALUES(capaian_kompetensi)
            ");
            $stmtUpsert->execute([$raporId, $mId, $lr['nama_mapel'], $akhir, $kkmVal, $predikat, $capaian]);
        }

        return true;
    }
}
