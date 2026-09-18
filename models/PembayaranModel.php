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
     * Auto-seed realistic sample data if payment tables are empty
     */
    public function seedInitialDataIfEmpty() {
        try {
            $stmtCheck = $this->db->query("SELECT COUNT(*) FROM pembayaran_tagihan");
            if ((int)$stmtCheck->fetchColumn() > 0) {
                return; // Already populated
            }

            // Fetch active students
            $stmtSiswa = $this->db->query("
                SELECT s.id, s.nis, s.nisn, s.nama_lengkap, k.nama_kelas, j.nama_jurusan 
                FROM siswa s
                LEFT JOIN kelas k ON s.kelas_id = k.id
                LEFT JOIN jurusan j ON s.jurusan_id = j.id
                LIMIT 30
            ");
            $students = $stmtSiswa->fetchAll(PDO::FETCH_ASSOC);
            if (empty($students)) return;

            $currentTa = '2025/2026';
            $bulanList = [
                ['bulan' => 'Juli 2025', 'due' => '2025-07-10', 'paid' => true, 'tgl_bayar' => '2025-07-08 09:30:00', 'metode' => 'Transfer Bank BCA'],
                ['bulan' => 'Agustus 2025', 'due' => '2025-08-10', 'paid' => true, 'tgl_bayar' => '2025-08-05 14:15:00', 'metode' => 'Kasir TU Sekolah'],
                ['bulan' => 'September 2025', 'due' => '2025-09-10', 'paid' => false, 'tgl_bayar' => null, 'metode' => null],
                ['bulan' => 'Oktober 2025', 'due' => '2025-10-10', 'paid' => false, 'tgl_bayar' => null, 'metode' => null]
            ];

            foreach ($students as $idx => $s) {
                $siswaId = (int)$s['id'];
                $nis = !empty($s['nis']) ? $s['nis'] : '2526' . str_pad($siswaId, 4, '0', STR_PAD_LEFT);
                $nisn = !empty($s['nisn']) ? $s['nisn'] : '00' . rand(70000000, 99999999);

                // 1. Tagihan SPP Bulanan
                foreach ($bulanList as $bIdx => $b) {
                    $kodeTagihan = 'SPP-' . $nis . '-' . ($bIdx + 1);
                    $nominal = 250000.00;
                    // Student 1 or even indexed students have paid Sept, others haven't
                    $isPaid = $b['paid'] || ($siswaId % 2 === 1 && $bIdx === 2);
                    $terbayar = $isPaid ? $nominal : 0.00;
                    $sisa = $isPaid ? 0.00 : $nominal;
                    $status = $isPaid ? 'lunas' : 'belum_lunas';

                    $stmtInsert = $this->db->prepare("
                        INSERT INTO pembayaran_tagihan 
                        (siswa_id, nis, nisn, jenis_pembayaran, kode_tagihan, judul, nominal, nominal_terbayar, sisa_tagihan, periode_bulan, tahun_ajaran, tanggal_jatuh_tempo, status, keterangan)
                        VALUES (?, ?, ?, 'SPP', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $judul = 'SPP Bulanan - ' . $b['bulan'];
                    $ket = 'Iuran Pembinaan Pendidikan (SPP) Reguler SMK Muthia Harapan';
                    $stmtInsert->execute([$siswaId, $nis, $nisn, $kodeTagihan, $judul, $nominal, $terbayar, $sisa, $b['bulan'], $currentTa, $b['due'], $status, $ket]);
                    $tagihanId = (int)$this->db->lastInsertId();

                    if ($isPaid) {
                        $noTrx = 'TRX-' . date('Ymd') . '-' . rand(1000, 9999) . '-' . $tagihanId;
                        $tglBayar = $b['tgl_bayar'] ?: date('Y-m-d H:i:s', strtotime('-' . rand(1, 10) . ' days'));
                        $metode = $b['metode'] ?: 'Transfer Bank Mandiri';
                        $stmtTrx = $this->db->prepare("
                            INSERT INTO pembayaran_riwayat 
                            (tagihan_id, siswa_id, nomor_transaksi, nominal_bayar, tanggal_bayar, metode_pembayaran, channel, status, catatan)
                            VALUES (?, ?, ?, ?, ?, ?, 'Portal Keuangan SMK', 'berhasil', 'Pembayaran diverifikasi otomatis oleh Sistem Keuangan')
                        ");
                        $stmtTrx->execute([$tagihanId, $siswaId, $noTrx, $nominal, $tglBayar, $metode]);
                    }
                }

                // 2. Tagihan Dana Pengembangan Gedung / DSP (Sekali per Tahun)
                $kodeDsp = 'DSP-' . $nis . '-2025';
                $nomDsp = 750000.00;
                $isDspPaid = ($siswaId % 3 === 0);
                $terbayarDsp = $isDspPaid ? $nomDsp : 0.00;
                $sisaDsp = $isDspPaid ? 0.00 : $nomDsp;
                $statusDsp = $isDspPaid ? 'lunas' : 'belum_lunas';

                $stmtDsp = $this->db->prepare("
                    INSERT INTO pembayaran_tagihan 
                    (siswa_id, nis, nisn, jenis_pembayaran, kode_tagihan, judul, nominal, nominal_terbayar, sisa_tagihan, periode_bulan, tahun_ajaran, tanggal_jatuh_tempo, status, keterangan)
                    VALUES (?, ?, ?, 'DSP', ?, 'Dana Pengembangan Sarpras / DSP', ?, ?, ?, 'Semester Ganjil', ?, '2025-11-30', ?, 'Iuran Sarana & Prasarana Pendidikan')
                ");
                $stmtDsp->execute([$siswaId, $nis, $nisn, $kodeDsp, $nomDsp, $terbayarDsp, $sisaDsp, $currentTa, $statusDsp]);

                // 3. Tagihan Biaya Ujian CBT & Praktik Kejuruan
                $kodeUjian = 'UJN-' . $nis . '-2025';
                $nomUjian = 150000.00;
                $stmtUjian = $this->db->prepare("
                    INSERT INTO pembayaran_tagihan 
                    (siswa_id, nis, nisn, jenis_pembayaran, kode_tagihan, judul, nominal, nominal_terbayar, sisa_tagihan, periode_bulan, tahun_ajaran, tanggal_jatuh_tempo, status, keterangan)
                    VALUES (?, ?, ?, 'Ujian', ?, 'Biaya Administrasi Ujian CBT & Praktik', ?, 150000.00, 0.00, 'Semester Ganjil', ?, '2025-08-30', 'lunas', 'Biaya Lisensi CBT & Uji Kompetensi Kejuruan')
                ");
                $stmtUjian->execute([$siswaId, $nis, $nisn, $kodeUjian, $nomUjian, $currentTa]);
                $ujnTagihanId = (int)$this->db->lastInsertId();

                $stmtTrxUjian = $this->db->prepare("
                    INSERT INTO pembayaran_riwayat 
                    (tagihan_id, siswa_id, nomor_transaksi, nominal_bayar, tanggal_bayar, metode_pembayaran, channel, status, catatan)
                    VALUES (?, ?, ?, ?, ?, 'Kasir TU Sekolah', 'Loket Keuangan', 'berhasil', 'Lunas Registrasi Ujian CBT')
                ");
                $stmtTrxUjian->execute([$ujnTagihanId, $siswaId, 'TRX-UJN-' . $nis . '-01', $nomUjian, '2025-08-20 10:00:00']);
            }
        } catch (\Throwable $e) {
            // Silently ignore seed issues
        }
    }

    /**
     * Get All Bills for a Specific Student
     */
    public function getSiswaBills($siswaId) {
        $siswaId = (int)$siswaId;
        $stmt = $this->db->prepare("
            SELECT t.*,
                   COALESCE((SELECT r.tanggal_bayar FROM pembayaran_riwayat r WHERE r.tagihan_id = t.id AND r.status = 'berhasil' ORDER BY r.id DESC LIMIT 1), NULL) as tgl_terakhir_bayar,
                   COALESCE((SELECT r.metode_pembayaran FROM pembayaran_riwayat r WHERE r.tagihan_id = t.id AND r.status = 'berhasil' ORDER BY r.id DESC LIMIT 1), NULL) as metode_terakhir_bayar,
                   COALESCE((SELECT r.nomor_transaksi FROM pembayaran_riwayat r WHERE r.tagihan_id = t.id AND r.status = 'berhasil' ORDER BY r.id DESC LIMIT 1), NULL) as nomor_transaksi
            FROM pembayaran_tagihan t
            WHERE t.siswa_id = ?
            ORDER BY 
                CASE WHEN t.status = 'belum_lunas' THEN 1 WHEN t.status = 'sebagian' THEN 2 ELSE 3 END,
                t.tanggal_jatuh_tempo ASC, 
                t.id DESC
        ");
        $stmt->execute([$siswaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get Student Payment Summary (Total, Paid, Outstanding, Clearance)
     */
    public function getSiswaPaymentSummary($siswaId) {
        $siswaId = (int)$siswaId;
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_item_tagihan,
                SUM(nominal) as total_nominal_tagihan,
                SUM(nominal_terbayar) as total_terbayar,
                SUM(sisa_tagihan) as total_tunggakan,
                SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as count_lunas,
                SUM(CASE WHEN status != 'lunas' THEN 1 ELSE 0 END) as count_belum_lunas
            FROM pembayaran_tagihan
            WHERE siswa_id = ?
        ");
        $stmt->execute([$siswaId]);
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
                'persen_lunas' => 0,
                'count_lunas' => 0,
                'count_belum_lunas' => 0,
                'has_bills' => false,
                'is_bebas_keuangan' => false,
                'status_label' => 'Belum Ada Data Tagihan',
                'badge_class' => 'bg-secondary text-white'
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
    public function getSiswaRiwayatPembayaran($siswaId) {
        $siswaId = (int)$siswaId;
        $stmt = $this->db->prepare("
            SELECT r.*, t.judul as nama_tagihan, t.jenis_pembayaran, t.periode_bulan, t.kode_tagihan, t.tahun_ajaran
            FROM pembayaran_riwayat r
            JOIN pembayaran_tagihan t ON r.tagihan_id = t.id
            WHERE r.siswa_id = ?
            ORDER BY r.tanggal_bayar DESC, r.id DESC
        ");
        $stmt->execute([$siswaId]);
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
     * Format per item: [ 'nisn' => '...', 'kode_tagihan' => '...', 'judul' => '...', 'nominal' => 250000, 'terbayar' => 250000, 'status' => 'lunas', 'periode_bulan' => '...', ... ]
     */
    public function syncExternalPaymentData($items) {
        if (!is_array($items) || empty($items)) {
            return ['status' => false, 'message' => 'Data tagihan kosong atau format tidak valid.'];
        }

        $syncedCount = 0;
        $createdCount = 0;

        foreach ($items as $item) {
            $nisn = trim($item['nisn'] ?? '');
            $nis = trim($item['nis'] ?? '');
            $kodeTagihan = trim($item['kode_tagihan'] ?? '');

            if (empty($kodeTagihan) && (empty($nisn) && empty($nis))) {
                continue;
            }

            // Find matching student
            $siswa = null;
            if (!empty($nisn)) {
                $stmt = $this->db->prepare("SELECT id, nis, nisn FROM siswa WHERE nisn = ? LIMIT 1");
                $stmt->execute([$nisn]);
                $siswa = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            if (!$siswa && !empty($nis)) {
                $stmt = $this->db->prepare("SELECT id, nis, nisn FROM siswa WHERE nis = ? LIMIT 1");
                $stmt->execute([$nis]);
                $siswa = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            if (!$siswa) continue; // Student not found in LMS

            $siswaId = (int)$siswa['id'];
            $finalNis = $siswa['nis'] ?: $nis;
            $finalNisn = $siswa['nisn'] ?: $nisn;
            $judul = $item['judul'] ?? 'Iuran Sekolah';
            $jenis = $item['jenis_pembayaran'] ?? 'SPP';
            $nominal = (float)($item['nominal'] ?? 0);
            $terbayar = (float)($item['nominal_terbayar'] ?? ($item['terbayar'] ?? 0));
            $sisa = max(0, $nominal - $terbayar);
            $status = $sisa <= 0 ? 'lunas' : ($terbayar > 0 ? 'sebagian' : 'belum_lunas');
            $periode = $item['periode_bulan'] ?? null;
            $ta = $item['tahun_ajaran'] ?? '2025/2026';
            $due = $item['tanggal_jatuh_tempo'] ?? null;
            $ket = $item['keterangan'] ?? 'Sinkronisasi Sistem Pembayaran';

            // Check if tagihan already exists by kode_tagihan
            $stmtCheck = $this->db->prepare("SELECT id FROM pembayaran_tagihan WHERE kode_tagihan = ? LIMIT 1");
            $stmtCheck->execute([$kodeTagihan]);
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $stmtUp = $this->db->prepare("
                    UPDATE pembayaran_tagihan
                    SET nominal = ?, nominal_terbayar = ?, sisa_tagihan = ?, status = ?, keterangan = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmtUp->execute([$nominal, $terbayar, $sisa, $status, $ket, $existing['id']]);
                $syncedCount++;
            } else {
                $stmtIn = $this->db->prepare("
                    INSERT INTO pembayaran_tagihan
                    (siswa_id, nis, nisn, jenis_pembayaran, kode_tagihan, judul, nominal, nominal_terbayar, sisa_tagihan, periode_bulan, tahun_ajaran, tanggal_jatuh_tempo, status, keterangan)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtIn->execute([$siswaId, $finalNis, $finalNisn, $jenis, $kodeTagihan, $judul, $nominal, $terbayar, $sisa, $periode, $ta, $due, $status, $ket]);
                $createdCount++;
            }
        }

        return [
            'status' => true,
            'message' => "Sinkronisasi berhasil: {$syncedCount} diperbarui, {$createdCount} ditambahkan.",
            'synced' => $syncedCount,
            'created' => $createdCount
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

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
            $this->saveBridgeConfig(['server_url' => $remoteUrl, 'secret_token' => $secretToken, 'last_sync' => date('Y-m-d H:i:s'), 'last_status' => 'Gagal HTTP ' . $httpCode]);
            return ['status' => false, 'message' => 'Server pembayaran merespon dengan kode HTTP: ' . $httpCode];
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
