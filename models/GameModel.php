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

            $this->seedDefaultGamesIfEmpty();
        } catch (Exception $e) {}
    }

    public function seedDefaultGamesIfEmpty() {
        try {
            $cnt = (int)$this->db->query("SELECT COUNT(*) FROM game_edukasi")->fetchColumn();
            if ($cnt > 0) return;

            $guruId = (int)($this->db->query("SELECT id FROM guru LIMIT 1")->fetchColumn() ?: 1);
            $mapelId = (int)($this->db->query("SELECT id FROM mata_pelajaran LIMIT 1")->fetchColumn() ?: 1);

            // Game 1: Kuis Speed
            $g1 = [
                'guru_id' => $guruId,
                'mapel_id' => $mapelId,
                'kelas_id' => null,
                'judul' => 'Kuis Cerdas Cermat SMK & Kejuruan',
                'deskripsi' => 'Uji wawasan keahlian vokasi dan pengetahuan umummu dalam kuis kecepatan interaktif!',
                'tipe_game' => 'quiz_speed',
                'durasi_per_soal' => 15,
                'kkm' => 75
            ];
            $soal1 = [
                [
                    'pertanyaan' => 'Apa tujuan utama dari proses perencanaan proyek dalam bidang kejuruan?',
                    'opsi_a' => 'Meminimalkan risiko dan memastikan efisiensi pelaksanaan kerja',
                    'opsi_b' => 'Memperpanjang durasi waktu pengerjaan proyek',
                    'opsi_c' => 'Menambah alokasi biaya pengeluaran bahan',
                    'opsi_d' => 'Mengabaikan standar keselamatan kerja industri',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'Perencanaan proyek bertujuan meminimalkan kendala teknis dan memastikan efisiensi efisiensi waktu serta biaya.'
                ],
                [
                    'pertanyaan' => 'Sikap profesional manakah yang sangat diutamakan dalam dunia kerja industri?',
                    'opsi_a' => 'Integritas, Disiplin, Tanggung Jawab, & Kerja Sama Tim',
                    'opsi_b' => 'Apatis terhadap pencapaian target tim',
                    'opsi_c' => 'Mengabaikan Prosedur Operasional Standar (SOP)',
                    'opsi_d' => 'Bekerja tanpa mematuhi tenggat waktu',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'Kedisiplinan, kejujuran, dan kerjasama tim adalah pilar utama etika kerja profesional di industri.'
                ],
                [
                    'pertanyaan' => 'Tahapan penting manakah yang wajib dilakukan setelah penyelesaian tugas atau produk?',
                    'opsi_a' => 'Pengujian (Testing) & Reviu Kualitas Umpan Balik',
                    'opsi_b' => 'Langsung didistribusikan tanpa tahap pemeriksaan',
                    'opsi_c' => 'Menghapus dokumentasi hasil kerja',
                    'opsi_d' => 'Menghentikan seluruh proses evaluasi karya',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'Tahap pengujian dan evaluasi umpan balik memastikan produk memenuhi standar mutu yang ditentukan.'
                ],
                [
                    'pertanyaan' => 'Mengapa penerapan K3 (Keselamatan & Kesehatan Kerja) sangat krusial di bengkel/laboratorium?',
                    'opsi_a' => 'Mencegah kecelakaan kerja dan melindungi keselamatan tenaga kerja',
                    'opsi_b' => 'Hanya sekadar formalitas syarat administratif',
                    'opsi_c' => 'Memperlambat proses produksi barang',
                    'opsi_d' => 'Menambah risiko kerusakan alat produksi',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'K3 menjamin perlindungan keselamatan jiwa dan lingkungan kerja yang kondusif.'
                ]
            ];
            $this->createGame($g1, $soal1);

            // Game 2: Spin Wheel Quiz
            $g2 = [
                'guru_id' => $guruId,
                'mapel_id' => $mapelId,
                'kelas_id' => null,
                'judul' => 'Tebak Istilah Vokasi & Teknologi',
                'deskripsi' => 'Tebak istilah populer keahlian dan kejuruan SMK dalam mode kuis pilihan cepat!',
                'tipe_game' => 'spin_wheel',
                'durasi_per_soal' => 20,
                'kkm' => 70
            ];
            $soal2 = [
                [
                    'pertanyaan' => 'Istilah manakah yang merujuk pada dokumen standar langkah-langkah kerja operasional?',
                    'opsi_a' => 'SOP (Standard Operating Procedure)',
                    'opsi_b' => 'KTSP (Kurikulum Tingkat Satuan Pendidikan)',
                    'opsi_c' => 'CV (Curriculum Vitae)',
                    'opsi_d' => 'MOU (Memorandum of Understanding)',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'SOP adalah panduan acuan baku pelaksanaan urutan pengerjaan tugas.'
                ],
                [
                    'pertanyaan' => 'Apakah fungsi dari proses Troubleshooting dalam pemeliharaan sarana prasarana?',
                    'opsi_a' => 'Mendeteksi, mendiagnosis, dan merawat/memperbaiki kerusakan sistem',
                    'opsi_b' => 'Menghapus seluruh file cadangan data',
                    'opsi_c' => 'Membuat masalah baru pada perangkat',
                    'opsi_d' => 'Menjual sisa bahan produksi',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'Troubleshooting adalah langkah pemecahan masalah teknis untuk memulihkan fungsi sistem.'
                ],
                [
                    'pertanyaan' => 'Istilah industri apakah yang menggambarkan pengujian mutu produk akhir?',
                    'opsi_a' => 'Quality Control (QC)',
                    'opsi_b' => 'Human Resources (HR)',
                    'opsi_c' => 'Public Relations (PR)',
                    'opsi_d' => 'Supply Chain (SC)',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'Quality Control bertugas memeriksa dan menjamin produk sesuai spesifikasi standar.'
                ],
                [
                    'pertanyaan' => 'Konsep belajar berbasis proyek nyata di SMK sering disebut dengan istilah?',
                    'opsi_a' => 'Project-Based Learning (PjBL)',
                    'opsi_b' => 'Rote Learning (Hafalan)',
                    'opsi_c' => 'Passive Learning',
                    'opsi_d' => 'Single-Subject Study',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'PjBL mendorong pembelajaran praktis berbasis proyek nyata industri.'
                ]
            ];
            $this->createGame($g2, $soal2);

            // Game 3: Memory Match
            $g3 = [
                'guru_id' => $guruId,
                'mapel_id' => $mapelId,
                'kelas_id' => null,
                'judul' => 'Memory Match Kosa Kata & Konsep SMK',
                'deskripsi' => 'Uji daya ingat dan pemahaman konsep keahlianmu dalam tantangan Memory Match!',
                'tipe_game' => 'memory_match',
                'durasi_per_soal' => 20,
                'kkm' => 75
            ];
            $soal3 = [
                [
                    'pertanyaan' => 'Manakah yang merupakan komponen utama dalam perancangan produk unggulan?',
                    'opsi_a' => 'Fungsionalitas, Estetika, Ergonomi & Kualitas Bahan',
                    'opsi_b' => 'Harga mahal tanpa jaminan kualitas',
                    'opsi_c' => 'Desain rumit yang sulit digunakan',
                    'opsi_d' => 'Bahan bekas yang membahayakan pengguna',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'Produk unggulan menggabungkan aspek fungsionalitas, kenyamanan, serta kualitas bahan.'
                ],
                [
                    'pertanyaan' => 'Keterampilan abad 21 manakah yang paling dibutuhkan lulusan SMK?',
                    'opsi_a' => 'Berpikir Kritis, Kreativitas, Komunikasi, & Kolaborasi',
                    'opsi_b' => 'Ketergantungan penuh pada instruksi guru',
                    'opsi_c' => 'Menutup diri dari perkembangan teknologi',
                    'opsi_d' => 'Menghindari kerja kelompok',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => '4C (Critical Thinking, Creativity, Communication, Collaboration) adalah softskill utama.'
                ],
                [
                    'pertanyaan' => 'Prinsip 5R/5S di lingkungan kerja laboratorium/bengkel meliputi?',
                    'opsi_a' => 'Ringkas, Rapi, Resik, Rawat, Rajin',
                    'opsi_b' => 'Ragu, Rusak, Runtuh, Raba, Reka',
                    'opsi_c' => 'Rencana, Realisasi, Reviu, Rangkum, Rilis',
                    'opsi_d' => 'Rintis, Rancang, Rakit, Rawat, Rusak',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'Budaya 5R/5S memastikan tempat kerja tertata, bersih, dan produktif.'
                ],
                [
                    'pertanyaan' => 'Apa manfaat dari pelaksanaan Praktik Kerja Lapangan (PKL) bagi siswa SMK?',
                    'opsi_a' => 'Mendapatkan pengalaman kerja nyata dan budaya industri',
                    'opsi_b' => 'Mengurangi jam istirahat sekolah',
                    'opsi_c' => 'Mengganti ujian nasional semata',
                    'opsi_d' => 'Menghindari kegiatan pembelajaran kelas',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'PKL memberikan kesempatan mengaplikasikan ilmu langsung di Dunia Usaha/Dunia Industri (DUDI).'
                ]
            ];
            $this->createGame($g3, $soal3);

            // Game 4: Mario Runner
            $g4 = [
                'guru_id' => $guruId,
                'mapel_id' => $mapelId,
                'kelas_id' => null,
                'judul' => 'Runner Quiz Kecepatan Kejuruan',
                'deskripsi' => 'Berlari cepat dan jawab tantangan kuis keahlian kejuruan sebelum waktu habis!',
                'tipe_game' => 'mario_run',
                'durasi_per_soal' => 15,
                'kkm' => 75
            ];
            $soal4 = [
                [
                    'pertanyaan' => 'Tindakan pertama jika melihat bahaya listrik atau percikan api di laboratorium adalah?',
                    'opsi_a' => 'Mematikan Sakelar Utama / MCB dan melapor pada instruktur',
                    'opsi_b' => 'Menyiram listrik dengan air biasa',
                    'opsi_c' => 'Membiarkan percikan api membesar',
                    'opsi_d' => 'Menyentuh kabel yang terkelupas',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'Mematikan pasokan listrik utama adalah prosedur darurat utama dalam penanganan bahaya kelistrikan.'
                ],
                [
                    'pertanyaan' => 'Alat Pelindung Diri (APD) standar saat bekerja dengan mesin atau bahan kimia adalah?',
                    'opsi_a' => 'Kacamata Safety, Sarung Tangan, Masker, & Sepatu Safety',
                    'opsi_b' => 'Sandal jepit dan kaus tipis',
                    'opsi_c' => 'Kacamata hitam biasa dan perhiasan',
                    'opsi_d' => 'Tanpa menggunakan APD apapun',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'APD standar melindungi mata, pernapasan, serta fisik dari paparan bahan berbahaya.'
                ],
                [
                    'pertanyaan' => 'Manakah yang termasuk contoh sumber daya terbuka (Open Source) dalam bidang teknologi?',
                    'opsi_a' => 'Linux, Python, & VS Code',
                    'opsi_b' => 'Software berlisensi rahasia berbayar mahal',
                    'opsi_c' => 'Perangkat keras buatan sendiri tanpa skema',
                    'opsi_d' => 'Dokumentasi tertutup tanpa izin baca',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'Open source mengizinkan akses dan pengembangan kode secara kolaboratif.'
                ],
                [
                    'pertanyaan' => 'Apa arti dari istilah "Benchmarking" dalam evaluasi kualitas produk?',
                    'opsi_a' => 'Membandingkan kinerja produk dengan standar terbaik di pasaran',
                    'opsi_b' => 'Menurunkan mutu produk agar lebih murah',
                    'opsi_c' => 'Mengabaikan produk pesaing industri',
                    'opsi_d' => 'Menghapus garansi resmi produk',
                    'kunci_jawaban' => 'a',
                    'poin' => 25,
                    'penjelasan' => 'Benchmarking dilakukan untuk membandingkan standar kualitas dengan acuan terbaik.'
                ]
            ];
            $this->createGame($g4, $soal4);

        } catch (Exception $e) {}
    }

    public function getAllGames($guruId = null, $kelasId = null) {
        $this->seedDefaultGamesIfEmpty();

        $sql = "
            SELECT g.*, 
                   COALESCE(m.nama_mapel, 'Pengetahuan & Kejuruan') as nama_mapel, 
                   COALESCE(k.nama_kelas, 'Semua Kelas') as nama_kelas, 
                   COALESCE(gr.nama_lengkap, 'Tim Kurikulum SMK') as nama_guru,
                   (SELECT COUNT(*) FROM game_soal WHERE game_id = g.id) as total_soal,
                   (SELECT COUNT(*) FROM game_skor WHERE game_id = g.id) as total_pemain
            FROM game_edukasi g
            LEFT JOIN mata_pelajaran m ON g.mapel_id = m.id
            LEFT JOIN kelas k ON g.kelas_id = k.id
            LEFT JOIN guru gr ON g.guru_id = gr.id
            WHERE 1=1
        ";
        $params = [];

        if ($guruId) {
            $sql .= " AND (g.guru_id = ? OR g.kelas_id IS NULL OR g.kelas_id = 0 OR g.guru_id = 0)";
            $params[] = (int)$guruId;
        }

        if ($kelasId) {
            $sql .= " AND (g.kelas_id IS NULL OR g.kelas_id = 0 OR g.kelas_id = ?)";
            $params[] = (int)$kelasId;
        }

        $sql .= " ORDER BY g.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $games = $stmt->fetchAll();

        if (empty($games)) {
            $stmtFallback = $this->db->query("
                SELECT g.*, 
                       COALESCE(m.nama_mapel, 'Pengetahuan & Kejuruan') as nama_mapel, 
                       COALESCE(k.nama_kelas, 'Semua Kelas') as nama_kelas, 
                       COALESCE(gr.nama_lengkap, 'Tim Kurikulum SMK') as nama_guru,
                       (SELECT COUNT(*) FROM game_soal WHERE game_id = g.id) as total_soal,
                       (SELECT COUNT(*) FROM game_skor WHERE game_id = g.id) as total_pemain
                FROM game_edukasi g
                LEFT JOIN mata_pelajaran m ON g.mapel_id = m.id
                LEFT JOIN kelas k ON g.kelas_id = k.id
                LEFT JOIN guru gr ON g.guru_id = gr.id
                ORDER BY g.id DESC
            ");
            $games = $stmtFallback->fetchAll();
        }

        return $games;
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
