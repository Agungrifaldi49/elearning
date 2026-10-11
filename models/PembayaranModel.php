<?php
/**
 * Model Portal Pembayaran Terintegrasi (Data Bridge)
 * E-Learning SMK Muthia Harapan Cicalengka
 */

require_once ROOT_PATH . 'config/database.php';

class PembayaranModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
        Database::ensureCustomTables();
        // Auto-seed disabled so tables start completely clean for real data pull
    }

    /**
     * Murni data resmi: Seed dummy data dinonaktifkan total untuk menjaga integritas data keuangan
     */
    public function seedInitialDataIfEmpty() {
        // Dinonaktifkan: Seluruh data tagihan dan pembayaran wajib berasal murni dari API Tata Usaha resmi.
        return;
    }

    /**
     * Get All Bills for a Specific Student
     */
    public function getSiswaBills($siswaId, $nis = null, $nisn = null) {
        $siswaId = (int)$siswaId;
        $clauses = ["t.siswa_id = ?"];
        $params = [$siswaId];
        if (!empty($nisn)) {
            $clauses[] = "(t.nisn IS NOT NULL AND t.nisn != '' AND t.nisn = ?)";
            $params[] = trim($nisn);
        }
        if (!empty($nis)) {
            $clauses[] = "(t.nis IS NOT NULL AND t.nis != '' AND t.nis = ?)";
            $params[] = trim($nis);
        }
        $where = "(" . implode(" OR ", $clauses) . ")";

        $stmt = $this->db->prepare("
            SELECT t.*,
                   COALESCE((SELECT r.tanggal_bayar FROM pembayaran_riwayat r WHERE r.tagihan_id = t.id AND r.status = 'berhasil' ORDER BY r.id DESC LIMIT 1), NULL) as tgl_terakhir_bayar,
                   COALESCE((SELECT r.metode_pembayaran FROM pembayaran_riwayat r WHERE r.tagihan_id = t.id AND r.status = 'berhasil' ORDER BY r.id DESC LIMIT 1), NULL) as metode_terakhir_bayar,
                   COALESCE((SELECT r.nomor_transaksi FROM pembayaran_riwayat r WHERE r.tagihan_id = t.id AND r.status = 'berhasil' ORDER BY r.id DESC LIMIT 1), NULL) as nomor_transaksi
            FROM pembayaran_tagihan t
            WHERE {$where}
            ORDER BY 
                CASE WHEN t.status = 'belum_lunas' THEN 1 WHEN t.status = 'sebagian' THEN 2 ELSE 3 END,
                t.tanggal_jatuh_tempo ASC, 
                t.id DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get Student Payment Summary (Total, Paid, Outstanding, Clearance)
     */
    public function getSiswaPaymentSummary($siswaId, $nis = null, $nisn = null) {
        $siswaId = (int)$siswaId;
        $clauses = ["siswa_id = ?"];
        $params = [$siswaId];
        if (!empty($nisn)) {
            $clauses[] = "(nisn IS NOT NULL AND nisn != '' AND nisn = ?)";
            $params[] = trim($nisn);
        }
        if (!empty($nis)) {
            $clauses[] = "(nis IS NOT NULL AND nis != '' AND nis = ?)";
            $params[] = trim($nis);
        }
        $where = "(" . implode(" OR ", $clauses) . ")";

        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_item_tagihan,
                SUM(nominal) as total_nominal_tagihan,
                SUM(nominal_terbayar) as total_terbayar,
                SUM(sisa_tagihan) as total_tunggakan,
                SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as count_lunas,
                SUM(CASE WHEN status != 'lunas' THEN 1 ELSE 0 END) as count_belum_lunas
            FROM pembayaran_tagihan
            WHERE {$where}
        ");
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $totalTagihan = (float)($row['total_nominal_tagihan'] ?? 0);
        $totalTerbayar = (float)($row['total_terbayar'] ?? 0);
        $totalTunggakan = (float)($row['total_tunggakan'] ?? 0);
        $countBelumLunas = (int)($row['count_belum_lunas'] ?? 0);

        if ($totalTagihan <= 0 && $totalTerbayar <= 0) {
            return [
                'total_tagihan' => 0,
                'total_terbayar' => 0,
                'total_tunggakan' => 0,
                'persen_lunas' => 100,
                'count_lunas' => 0,
                'count_belum_lunas' => 0,
                'has_bills' => false,
                'is_bebas_keuangan' => true,
                'status_label' => 'Bebas Tanggungan (Tidak Ada Tagihan)',
                'badge_class' => 'bg-success text-white'
            ];
        }

        $persenLunas = $totalTagihan > 0 ? round(($totalTerbayar / $totalTagihan) * 100, 1) : 100;
        $isBebasKeuangan = ($totalTunggakan <= 0 && $countBelumLunas === 0);

        return [
            'total_tagihan' => $totalTagihan,
            'total_terbayar' => $totalTerbayar,
            'total_tunggakan' => $totalTunggakan,
            'persen_lunas' => $persenLunas,
            'count_lunas' => (int)($row['count_lunas'] ?? 0),
            'count_belum_lunas' => $countBelumLunas,
            'has_bills' => true,
            'is_bebas_keuangan' => $isBebasKeuangan,
            'status_label' => $isBebasKeuangan ? 'Bebas Keuangan (Lunas)' : 'Terdapat Tunggakan Aktif',
            'badge_class' => $isBebasKeuangan ? 'bg-success text-white' : 'bg-warning text-dark'
        ];
    }

    /**
     * Get Student Payment Transaction History
     */
    public function getSiswaRiwayatPembayaran($siswaId, $nis = null, $nisn = null) {
        $siswaId = (int)$siswaId;
        $clauses = ["r.siswa_id = ?"];
        $params = [$siswaId];
        if (!empty($nisn)) {
            $clauses[] = "(t.nisn IS NOT NULL AND t.nisn != '' AND t.nisn = ?)";
            $params[] = trim($nisn);
        }
        if (!empty($nis)) {
            $clauses[] = "(t.nis IS NOT NULL AND t.nis != '' AND t.nis = ?)";
            $params[] = trim($nis);
        }
        $where = "(" . implode(" OR ", $clauses) . ")";

        $stmt = $this->db->prepare("
            SELECT r.*, t.judul as nama_tagihan, t.jenis_pembayaran, t.periode_bulan, t.kode_tagihan, t.tahun_ajaran
            FROM pembayaran_riwayat r
            JOIN pembayaran_tagihan t ON r.tagihan_id = t.id
            WHERE {$where}
            ORDER BY r.tanggal_bayar DESC, r.id DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get All Student Payments for Admin (With Filtering & Search)
     */
    public function getAllStudentPayments($filters = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['kelas_id'])) {
            $where[] = "s.kelas_id = ?";
            $params[] = (int)$filters['kelas_id'];
        }
        if (!empty($filters['jurusan_id'])) {
            $where[] = "s.jurusan_id = ?";
            $params[] = (int)$filters['jurusan_id'];
        }
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'lunas') {
                $where[] = "sub.total_tunggakan = 0";
            } elseif ($filters['status'] === 'belum_lunas') {
                $where[] = "sub.total_tunggakan > 0";
            }
        }
        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(s.nama_lengkap LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ? OR u.username LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = implode(" AND ", $where);

        $sql = "
            SELECT s.id as siswa_id, s.nis, s.nisn, s.nama_lengkap, u.email,
                   k.nama_kelas, j.nama_jurusan,
                   COALESCE(sub.total_nominal_tagihan, 0) as total_nominal_tagihan,
                   COALESCE(sub.total_terbayar, 0) as total_terbayar,
                   COALESCE(sub.total_tunggakan, 0) as total_tunggakan,
                   COALESCE(sub.count_belum_lunas, 0) as count_belum_lunas,
                   COALESCE(sub.count_lunas, 0) as count_lunas,
                   COALESCE(sub.total_item_tagihan, 0) as total_item_tagihan
            FROM siswa s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN jurusan j ON s.jurusan_id = j.id
            LEFT JOIN (
                SELECT siswa_id,
                       SUM(nominal) as total_nominal_tagihan,
                       SUM(nominal_terbayar) as total_terbayar,
                       SUM(sisa_tagihan) as total_tunggakan,
                       SUM(CASE WHEN status != 'lunas' THEN 1 ELSE 0 END) as count_belum_lunas,
                       SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as count_lunas,
                       COUNT(*) as total_item_tagihan
                FROM pembayaran_tagihan
                GROUP BY siswa_id
            ) sub ON sub.siswa_id = s.id
            WHERE {$whereClause}
            ORDER BY sub.total_tunggakan DESC, k.nama_kelas ASC, s.nama_lengkap ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get Admin Global Financial Summary
     */
    public function getAdminGlobalStats() {
        $stmt = $this->db->query("
            SELECT 
                COALESCE(SUM(nominal), 0) as total_target,
                COALESCE(SUM(nominal_terbayar), 0) as total_masuk,
                COALESCE(SUM(sisa_tagihan), 0) as total_piutang,
                COUNT(DISTINCT siswa_id) as total_siswa_terdaftar
            FROM pembayaran_tagihan
        ");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $target = (float)($stats['total_target'] ?? 0);
        $masuk = (float)($stats['total_masuk'] ?? 0);
        $piutang = (float)($stats['total_piutang'] ?? 0);
        $rate = $target > 0 ? round(($masuk / $target) * 100, 1) : 0;

        // Count students fully paid vs with unpaid bills
        $stmtSiswa = $this->db->query("
            SELECT 
                SUM(CASE WHEN sisa <= 0 THEN 1 ELSE 0 END) as siswa_lunas,
                SUM(CASE WHEN sisa > 0 THEN 1 ELSE 0 END) as siswa_menunggak
            FROM (
                SELECT siswa_id, SUM(sisa_tagihan) as sisa
                FROM pembayaran_tagihan
                GROUP BY siswa_id
            ) t
        ");
        $siswaCount = $stmtSiswa->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_target' => $target,
            'total_masuk' => $masuk,
            'total_piutang' => $piutang,
            'rate_pelunasan' => $rate,
            'siswa_lunas' => (int)($siswaCount['siswa_lunas'] ?? 0),
            'siswa_menunggak' => (int)($siswaCount['siswa_menunggak'] ?? 0),
            'total_siswa' => (int)($stats['total_siswa_terdaftar'] ?? 0)
        ];
    }

    /**
     * Get Executive Financial Stats for Kepala Sekolah (Dashboard & Reports)
     */
    public function getKepsekExecutiveStats() {
        $global = $this->getAdminGlobalStats();

        // Breakdown by Jenis Pembayaran (SPP, DSP, Ujian, dll.)
        $stmtKategori = $this->db->query("
            SELECT 
                jenis_pembayaran,
                COUNT(*) as jumlah_tagihan,
                SUM(nominal) as target_nominal,
                SUM(nominal_terbayar) as realisasi_nominal,
                SUM(sisa_tagihan) as sisa_nominal,
                ROUND((SUM(nominal_terbayar) / NULLIF(SUM(nominal), 0)) * 100, 1) as persentase
            FROM pembayaran_tagihan
            GROUP BY jenis_pembayaran
            ORDER BY target_nominal DESC
        ");
        $breakdownKategori = $stmtKategori->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Breakdown by Rombel Kelas
        $stmtKelas = $this->db->query("
            SELECT 
                COALESCE(k.nama_kelas, 'Tanpa Kelas') as nama_kelas,
                COUNT(DISTINCT s.id) as total_siswa,
                SUM(t.nominal) as target_kelas,
                SUM(t.nominal_terbayar) as realisasi_kelas,
                SUM(t.sisa_tagihan) as sisa_kelas,
                ROUND((SUM(t.nominal_terbayar) / NULLIF(SUM(t.nominal), 0)) * 100, 1) as persentase_kelas
            FROM kelas k
            JOIN siswa s ON s.kelas_id = k.id
            LEFT JOIN pembayaran_tagihan t ON t.siswa_id = s.id
            GROUP BY k.id, k.nama_kelas
            ORDER BY persentase_kelas DESC, k.nama_kelas ASC
        ");
        $breakdownKelas = $stmtKelas->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'global' => $global,
            'breakdown_kategori' => $breakdownKategori,
            'breakdown_kelas' => $breakdownKelas
        ];
    }

    /**
     * Get Slip / Bukti Pembayaran Details for Print
     */
    public function getSlipPembayaran($riwayatId, $siswaId = null) {
        $sql = "
            SELECT r.*, t.judul as nama_tagihan, t.jenis_pembayaran, t.periode_bulan, t.kode_tagihan, t.tahun_ajaran,
                   s.nama_lengkap, s.nis, s.nisn, k.nama_kelas, j.nama_jurusan
            FROM pembayaran_riwayat r
            JOIN pembayaran_tagihan t ON r.tagihan_id = t.id
            JOIN siswa s ON r.siswa_id = s.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN jurusan j ON s.jurusan_id = j.id
            WHERE r.id = ?
        ";
        $params = [(int)$riwayatId];
        if ($siswaId !== null) {
            $sql .= " AND r.siswa_id = ?";
            $params[] = (int)$siswaId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Sync / Pull Payment Data from External Payload (Method 1 & API Bridge)
     * Kinerja tinggi: Pre-load memory maps, bulk transactions, pencocokan rombel resmi X, XI, XII
     */
    public function syncExternalPaymentData($items) {
        if (!is_array($items) || empty($items)) {
            return ['status' => false, 'message' => 'Data tagihan kosong atau format tidak valid.'];
        }

        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $syncedCount = 0;
        $createdCount = 0;
        $studentsCreatedCount = 0;
        $studentsUpdatedCount = 0;
        $riwayatSyncedCount = 0;

        // 1. Pre-load Rombel Kelas ke Memory Map
        $kelasRows = $this->db->query("SELECT id, nama_kelas, jurusan_id, tingkat FROM kelas")->fetchAll(PDO::FETCH_ASSOC);
        $kelasMap = [];
        foreach ($kelasRows as $kr) {
            $kelasMap[trim(strtolower($kr['nama_kelas']))] = [
                'id'         => (int)$kr['id'],
                'jurusan_id' => (int)$kr['jurusan_id'],
                'tingkat'    => $kr['tingkat']
            ];
        }

        // 2. Pre-load Users & Siswa ke Memory Map
        $userRows = $this->db->query("SELECT id, username, email FROM users")->fetchAll(PDO::FETCH_ASSOC);
        $userMap = [];
        foreach ($userRows as $ur) {
            if (!empty($ur['username'])) $userMap[trim(strtolower($ur['username']))] = (int)$ur['id'];
            if (!empty($ur['email'])) $userMap[trim(strtolower($ur['email']))] = (int)$ur['id'];
        }

        $siswaRows = $this->db->query("SELECT id, user_id, nis, nisn, kelas_id, jurusan_id, nama_lengkap FROM siswa")->fetchAll(PDO::FETCH_ASSOC);
        $siswaMap = [];
        foreach ($siswaRows as $sr) {
            $obj = [
                'id'           => (int)$sr['id'],
                'user_id'      => (int)$sr['user_id'],
                'nis'          => trim((string)$sr['nis']),
                'nisn'         => trim((string)$sr['nisn']),
                'kelas_id'     => (int)$sr['kelas_id'],
                'jurusan_id'   => (int)$sr['jurusan_id'],
                'nama_lengkap' => $sr['nama_lengkap']
            ];
            if (!empty($sr['nis']))  $siswaMap['nis_' . trim($sr['nis'])] = $obj;
            if (!empty($sr['nisn'])) $siswaMap['nisn_' . trim($sr['nisn'])] = $obj;
        }

        // 3. Pre-load Tagihan & Riwayat Transaksi ke Memory Map
        $existingTagihanMap = $this->db->query("SELECT kode_tagihan, id FROM pembayaran_tagihan")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        $existingTrxMap = $this->db->query("SELECT nomor_transaksi, id FROM pembayaran_riwayat")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        // 4. Siapkan Prepared Statements
        $stmtInUser = $this->db->prepare("
            INSERT INTO users (username, email, password, full_name, role_id, status, created_at)
            VALUES (?, ?, ?, ?, 4, 'active', NOW())
        ");
        $stmtInSiswa = $this->db->prepare("
            INSERT INTO siswa (user_id, nis, nisn, nama_lengkap, kelas_id, jurusan_id, jenis_kelamin, status)
            VALUES (?, ?, ?, ?, ?, ?, 'L', 'aktif')
        ");
        $stmtUpSiswaKelas = $this->db->prepare("
            UPDATE siswa SET kelas_id = ?, jurusan_id = ?, nama_lengkap = ? WHERE id = ?
        ");
        $stmtInKelas = $this->db->prepare("
            INSERT INTO kelas (nama_kelas, tingkat, jurusan_id) VALUES (?, ?, ?)
        ");
        $stmtUpTagihan = $this->db->prepare("
            UPDATE pembayaran_tagihan
            SET nominal = ?, nominal_terbayar = ?, sisa_tagihan = ?, status = ?, keterangan = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmtInTagihan = $this->db->prepare("
            INSERT INTO pembayaran_tagihan
            (siswa_id, nis, nisn, jenis_pembayaran, kode_tagihan, judul, nominal, nominal_terbayar, sisa_tagihan, periode_bulan, tahun_ajaran, tanggal_jatuh_tempo, status, keterangan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtInTrx = $this->db->prepare("
            INSERT INTO pembayaran_riwayat
            (tagihan_id, siswa_id, nomor_transaksi, nominal_bayar, tanggal_bayar, metode_pembayaran, channel, status, catatan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // Helper fungsi resolusi kelas
        $resolveKelas = function($targetClass) use (&$kelasMap, $stmtInKelas) {
            $norm = trim(strtolower($targetClass));
            if (empty($norm)) {
                return ['id' => 1, 'jurusan_id' => 1];
            }
            if (isset($kelasMap[$norm])) {
                return $kelasMap[$norm];
            }

            // Tentukan tingkat
            $tingkat = 'XII';
            if (stripos($targetClass, 'XII') !== false) {
                $tingkat = 'XII';
            } elseif (stripos($targetClass, 'XI') !== false) {
                $tingkat = 'XI';
            } elseif (stripos($targetClass, 'X') !== false) {
                $tingkat = 'X';
            }

            // Tentukan jurusan
            $isRpl = (stripos($targetClass, 'RPL') !== false || stripos($targetClass, 'PPLG') !== false);
            $jurId = $isRpl ? 1 : 4;

            try {
                $stmtInKelas->execute([trim($targetClass), $tingkat, $jurId]);
                $newKid = (int)$this->db->lastInsertId();
                $kelasInfo = ['id' => $newKid, 'jurusan_id' => $jurId, 'tingkat' => $tingkat];
                $kelasMap[$norm] = $kelasInfo;
                return $kelasInfo;
            } catch (\Throwable $eK) {
                return ['id' => 1, 'jurusan_id' => 1];
            }
        };

        // 5. Eksekusi Sinkronisasi dalam Transaksi Database
        $this->db->beginTransaction();

        try {
            foreach ($items as $item) {
                $nisn = trim((string)($item['nisn'] ?? ''));
                $nis  = trim((string)($item['nis'] ?? ''));
                $kodeTagihan = trim((string)($item['kode_tagihan'] ?? ''));

                if (empty($kodeTagihan) && empty($nisn) && empty($nis)) {
                    continue;
                }

                // Cari siswa di cache memory
                $siswa = null;
                if (!empty($nis) && isset($siswaMap['nis_' . $nis])) {
                    $siswa = $siswaMap['nis_' . $nis];
                } elseif (!empty($nisn) && isset($siswaMap['nisn_' . $nisn])) {
                    $siswa = $siswaMap['nisn_' . $nisn];
                }

                $targetClass = trim((string)($item['nama_kelas'] ?? ''));
                $kelasInfo = $resolveKelas($targetClass);
                $studentName = !empty($item['nama_siswa']) ? trim($item['nama_siswa']) : ('Siswa ' . ($nis ?: $nisn));

                // Jika siswa belum ada di LMS, daftarkan akun dan baris siswa resmi
                if (!$siswa) {
                    $userLoginKey = !empty($nis) ? $nis : $nisn;
                    if (empty($userLoginKey)) continue;

                    $normLogin = trim(strtolower($userLoginKey));
                    $userEmail = $userLoginKey . '@siswa.smkmuthiaharapan.sch.id';
                    $normEmail = trim(strtolower($userEmail));

                    $userId = $userMap[$normLogin] ?? ($userMap[$normEmail] ?? null);
                    if (!$userId) {
                        $hashedPwd = password_hash($userLoginKey, PASSWORD_BCRYPT);
                        $stmtInUser->execute([$userLoginKey, $userEmail, $hashedPwd, $studentName]);
                        $userId = (int)$this->db->lastInsertId();
                        $userMap[$normLogin] = $userId;
                        $userMap[$normEmail] = $userId;
                    }

                    $stmtInSiswa->execute([
                        $userId,
                        $nis ?: $nisn,
                        $nisn ?: $nis,
                        $studentName,
                        $kelasInfo['id'],
                        $kelasInfo['jurusan_id']
                    ]);
                    $newSiswaId = (int)$this->db->lastInsertId();

                    $siswa = [
                        'id'           => $newSiswaId,
                        'user_id'      => $userId,
                        'nis'          => $nis ?: $nisn,
                        'nisn'         => $nisn ?: $nis,
                        'kelas_id'     => $kelasInfo['id'],
                        'jurusan_id'   => $kelasInfo['jurusan_id'],
                        'nama_lengkap' => $studentName
                    ];

                    if (!empty($nis))  $siswaMap['nis_' . $nis] = $siswa;
                    if (!empty($nisn)) $siswaMap['nisn_' . $nisn] = $siswa;
                    $studentsCreatedCount++;
                } else {
                    // Jika siswa sudah ada, pastikan rombel kelas dan nama tersinkron dengan data resmi API Tata Usaha
                    if (!empty($kelasInfo['id']) && $siswa['kelas_id'] !== $kelasInfo['id']) {
                        $stmtUpSiswaKelas->execute([
                            $kelasInfo['id'],
                            $kelasInfo['jurusan_id'],
                            !empty($studentName) ? $studentName : $siswa['nama_lengkap'],
                            $siswa['id']
                        ]);
                        $siswa['kelas_id'] = $kelasInfo['id'];
                        $siswa['jurusan_id'] = $kelasInfo['jurusan_id'];
                        if (!empty($studentName)) $siswa['nama_lengkap'] = $studentName;
                        if (!empty($nis))  $siswaMap['nis_' . $nis] = $siswa;
                        if (!empty($nisn)) $siswaMap['nisn_' . $nisn] = $siswa;
                        $studentsUpdatedCount++;
                    }
                }

                $siswaId   = (int)$siswa['id'];
                $finalNis  = $siswa['nis'] ?: $nis;
                $finalNisn = $siswa['nisn'] ?: $nisn;
                $judul     = $item['judul'] ?? 'Iuran Sekolah';
                $jenis     = $item['jenis_pembayaran'] ?? 'SPP';
                $nominal   = (float)($item['nominal'] ?? 0);
                $terbayar  = (float)($item['nominal_terbayar'] ?? ($item['terbayar'] ?? 0));
                $sisa      = max(0, $nominal - $terbayar);
                $status    = $sisa <= 0 ? 'lunas' : ($terbayar > 0 ? 'sebagian' : 'belum_lunas');
                $periode   = $item['periode_bulan'] ?? null;
                $ta        = $item['tahun_ajaran'] ?? '2024/2025';
                $due       = $item['tanggal_jatuh_tempo'] ?? null;
                $ket       = $item['keterangan'] ?? 'Tagihan Resmi API Tata Usaha';

                // Upsert Tagihan Siswa
                $tagihanDbId = null;
                if (isset($existingTagihanMap[$kodeTagihan])) {
                    $tagihanDbId = (int)$existingTagihanMap[$kodeTagihan];
                    $stmtUpTagihan->execute([$nominal, $terbayar, $sisa, $status, $ket, $tagihanDbId]);
                    $syncedCount++;
                } else {
                    $stmtInTagihan->execute([
                        $siswaId, $finalNis, $finalNisn, $jenis, $kodeTagihan, $judul,
                        $nominal, $terbayar, $sisa, $periode, $ta, $due, $status, $ket
                    ]);
                    $tagihanDbId = (int)$this->db->lastInsertId();
                    $existingTagihanMap[$kodeTagihan] = $tagihanDbId;
                    $createdCount++;
                }

                // Sync Riwayat Transaksi Pembayaran Resmi
                if ($tagihanDbId && !empty($item['riwayat']) && is_array($item['riwayat'])) {
                    foreach ($item['riwayat'] as $rw) {
                        $noTrx = trim((string)($rw['nomor_transaksi'] ?? ''));
                        if (empty($noTrx) || isset($existingTrxMap[$noTrx])) {
                            continue;
                        }

                        $nomTrx     = (float)($rw['nominal_bayar'] ?? 0);
                        $tglTrx     = $rw['tanggal_bayar'] ?? date('Y-m-d H:i:s');
                        $metodeTrx  = $rw['metode_pembayaran'] ?? 'Kasir TU Sekolah';
                        $channelTrx = $rw['channel'] ?? 'API Tata Usaha';
                        $statusTrx  = in_array($rw['status'] ?? '', ['berhasil', 'pending', 'batal']) ? $rw['status'] : 'berhasil';
                        $catatanTrx = $rw['catatan'] ?? 'Sinkronisasi dari API Tata Usaha';

                        $stmtInTrx->execute([
                            $tagihanDbId, $siswaId, $noTrx, $nomTrx, $tglTrx,
                            $metodeTrx, $channelTrx, $statusTrx, $catatanTrx
                        ]);
                        $existingTrxMap[$noTrx] = (int)$this->db->lastInsertId();
                        $riwayatSyncedCount++;
                    }
                }
            }

            $this->db->commit();
        } catch (\Throwable $eTrans) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return [
                'status'  => false,
                'message' => 'Gagal memproses sinkronisasi database: ' . $eTrans->getMessage()
            ];
        }

        $extraMsgParts = [];
        if (!empty($studentsCreatedCount)) {
            $extraMsgParts[] = "{$studentsCreatedCount} siswa baru didaftarkan";
        }
        if (!empty($studentsUpdatedCount)) {
            $extraMsgParts[] = "{$studentsUpdatedCount} rombel siswa diselaraskan";
        }
        if (!empty($riwayatSyncedCount)) {
            $extraMsgParts[] = "{$riwayatSyncedCount} transaksi resmi dicatat";
        }
        $extraMsg = !empty($extraMsgParts) ? (" (" . implode(", ", $extraMsgParts) . ")") : "";

        return [
            'status'     => true,
            'message'    => "Sinkronisasi berhasil: {$createdCount} tagihan baru ditambahkan, {$syncedCount} tagihan diperbarui{$extraMsg}.",
            'synced'     => $syncedCount,
            'created'    => $createdCount,
            'riwayat'    => $riwayatSyncedCount,
            'siswa_baru' => $studentsCreatedCount
        ];
    }

    /**
     * Get Payment Bridge Remote Config
     */
    public function getBridgeConfig() {
        $path = ROOT_PATH . 'config/payment_bridge.json';
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true) ?: [];
        }
        return [
            'server_url' => '',
            'secret_token' => '',
            'last_sync' => null,
            'last_status' => null
        ];
    }

    /**
     * Save Payment Bridge Remote Config
     */
    public function saveBridgeConfig($data) {
        $path = ROOT_PATH . 'config/payment_bridge.json';
        $current = $this->getBridgeConfig();
        $updated = array_merge($current, $data);
        file_put_contents($path, json_encode($updated, JSON_PRETTY_PRINT));
        return $updated;
    }

    /**
     * Get School Bank Accounts & Payment Procedure Config
     */
    public function getRekeningConfig() {
        $path = ROOT_PATH . 'config/payment_rekening.json';
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [
            'nama_sekolah' => 'SMK Muthia Harapan Cicalengka',
            'bank_accounts' => [
                [
                    'bank' => 'BANK BCA',
                    'nomor_rekening' => '7780918234',
                    'atas_nama' => 'SMK Muthia Harapan Cicalengka',
                    'warna_badge' => 'primary'
                ],
                [
                    'bank' => 'BANK MANDIRI',
                    'nomor_rekening' => '1310019827721',
                    'atas_nama' => 'SMK Muthia Harapan Cicalengka',
                    'warna_badge' => 'info'
                ]
            ],
            'prosedur' => [
                'Cantumkan NISN / Nama Siswa pada berita transfer.',
                'Kirimkan bukti transfer ke Bagian Keuangan / Tata Usaha (Loket TU Sekolah atau WhatsApp Keuangan).',
                'Status pembayaran di portal ini akan terbarui otomatis setelah divalidasi oleh sistem keuangan.'
            ],
            'kontak_konfirmasi' => '0812-xxxx-xxxx (Bagian Keuangan TU)',
            'catatan_tambahan' => 'Pastikan nomor rekening dan nama penerima sesuai sebelum menyelesaikan transaksi transfer.'
        ];
    }

    /**
     * Save School Bank Accounts & Payment Procedure Config
     */
    public function saveRekeningConfig($data) {
        $path = ROOT_PATH . 'config/payment_rekening.json';
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return $data;
    }

    /**
     * Pull data from Remote Payment Server via cURL / REST API
     */
    public function pullFromRemoteServer($remoteUrl, $secretToken = '') {
        $remoteUrl = trim($remoteUrl);
        if (empty($remoteUrl) || !filter_var($remoteUrl, FILTER_VALIDATE_URL)) {
            return ['status' => false, 'message' => 'URL Server Pembayaran tidak valid atau kosong.'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remoteUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        $isHttps = (stripos($remoteUrl, 'https://') === 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $isHttps);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $isHttps ? 2 : 0);

        $headers = [
            'Accept: application/json',
            'User-Agent: E-Learning-SMK-Muthia-Harapan-Bridge/1.0'
        ];
        if (!empty($secretToken)) {
            $headers[] = 'Authorization: Bearer ' . trim($secretToken);
            $headers[] = 'X-API-KEY: ' . trim($secretToken);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if (!empty($curlErr)) {
            $this->saveBridgeConfig(['server_url' => $remoteUrl, 'secret_token' => $secretToken, 'last_sync' => date('Y-m-d H:i:s'), 'last_status' => 'Gagal: ' . $curlErr]);
            return ['status' => false, 'message' => 'Koneksi ke server pembayaran gagal: ' . $curlErr];
        }

        if ($httpCode !== 200) {
            $errJson = json_decode($response, true);
            $errMsg = !empty($errJson['message']) ? $errJson['message'] : ('Server pembayaran merespon dengan kode HTTP: ' . $httpCode);
            $this->saveBridgeConfig(['server_url' => $remoteUrl, 'secret_token' => $secretToken, 'last_sync' => date('Y-m-d H:i:s'), 'last_status' => 'Gagal HTTP ' . $httpCode]);
            return ['status' => false, 'message' => $errMsg];
        }

        $json = json_decode($response, true);
        if (!is_array($json)) {
            $this->saveBridgeConfig(['server_url' => $remoteUrl, 'secret_token' => $secretToken, 'last_sync' => date('Y-m-d H:i:s'), 'last_status' => 'Format Non-JSON']);
            return ['status' => false, 'message' => 'Format respon dari server pembayaran bukan JSON yang valid.'];
        }

        $items = $json['data'] ?? $json['items'] ?? (isset($json[0]) ? $json : []);
        if (empty($items)) {
            $this->saveBridgeConfig(['server_url' => $remoteUrl, 'secret_token' => $secretToken, 'last_sync' => date('Y-m-d H:i:s'), 'last_status' => 'Data Kosong']);
            return ['status' => true, 'message' => 'Koneksi berhasil, namun belum ada tagihan di server pembayaran.', 'synced' => 0, 'created' => 0];
        }

        $syncRes = $this->syncExternalPaymentData($items);
        $this->saveBridgeConfig([
            'server_url' => $remoteUrl,
            'secret_token' => $secretToken,
            'last_sync' => date('Y-m-d H:i:s'),
            'last_status' => 'Sukses (' . ($syncRes['synced'] ?? 0) . ' update, ' . ($syncRes['created'] ?? 0) . ' baru)'
        ]);

        return $syncRes;
    }

    /**
     * Import Payment Records from CSV File
     */
    public function importFromCsv($filePath) {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return ['status' => false, 'message' => 'File CSV tidak dapat dibaca.'];
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['status' => false, 'message' => 'Gagal membuka file CSV.'];
        }

        // Read header
        $header = fgetcsv($handle, 2000, ',');
        if (!$header || count($header) < 2) {
            rewind($handle);
            $header = fgetcsv($handle, 2000, ';');
            $delim = ';';
        } else {
            $delim = ',';
        }

        if (!$header) {
            fclose($handle);
            return ['status' => false, 'message' => 'Header CSV tidak ditemukan atau file kosong.'];
        }

        $headerNormalized = array_map(function($h) {
            return strtolower(trim(str_replace(['"', "'", ' '], ['', '', '_'], $h)));
        }, $header);

        $items = [];
        while (($row = fgetcsv($handle, 2000, $delim)) !== false) {
            if (empty(array_filter($row))) continue;
            $item = [];
            foreach ($headerNormalized as $idx => $colName) {
                $item[$colName] = $row[$idx] ?? '';
            }
            $items[] = $item;
        }
        fclose($handle);

        if (empty($items)) {
            return ['status' => false, 'message' => 'Tidak ada baris data tagihan yang ditemukan dalam file.'];
        }

        return $this->syncExternalPaymentData($items);
    }

    /**
     * Clear all payment data
     */
    public function clearAllPaymentData() {
        try {
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE pembayaran_riwayat; TRUNCATE TABLE pembayaran_tagihan; SET FOREIGN_KEY_CHECKS = 1;");
            return ['status' => true, 'message' => 'Seluruh data tagihan dan riwayat pembayaran telah berhasil dikosongkan.'];
        } catch (\Throwable $e) {
            return ['status' => false, 'message' => 'Gagal mengosongkan data: ' . $e->getMessage()];
        }
    }
}
