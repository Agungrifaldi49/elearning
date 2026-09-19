<?php
/**
 * Migration & Seeder Runner for Dynamic Curriculum System
 * E-Learning SMK Muthia Harapan Cicalengka
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    echo "=== 1. MENJALANKAN DDL MIGRATION SQL ===\n";

    $sql = file_get_contents(__DIR__ . '/migration_curriculum_system.sql');
    $db->exec($sql);
    echo "✓ 11 Tabel Baru Berhasil Dibuat (InnoDB, UTF8MB4)\n";

    echo "\n=== 2. SEEDING MASTER KURIKULUM DEFAULT ===\n";
    $stmt = $db->prepare("SELECT id FROM kurikulum WHERE kode = ?");
    $stmt->execute(['KMDK']);
    $kmdkId = $stmt->fetchColumn();

    if (!$kmdkId) {
        $insKur = $db->prepare("
            INSERT INTO kurikulum (kode, nama, tahun_mulai, tahun_selesai, status, deskripsi)
            VALUES (?, ?, ?, NULL, 'aktif', ?)
        ");
        $insKur->execute([
            'KMDK',
            'Kurikulum Merdeka SMK',
            2022,
            'Kurikulum Merdeka SMK Pusat Keunggulan dengan pembelajaran berbasis Capaian Pembelajaran (CP), Profil Pelajar Pancasila, dan asesmen fleksibel.'
        ]);
        $kmdkId = $db->lastInsertId();
        echo "✓ Kurikulum Merdeka (KMDK) berhasil ditambahkan (ID: $kmdkId)\n";
    } else {
        echo "✓ Kurikulum Merdeka (KMDK) sudah ada (ID: $kmdkId)\n";
    }

    echo "\n=== 3. SEEDING FASE / TINGKAT UNTUK KMDK ===\n";
    $fases = [
        ['kode' => 'E', 'nama' => 'Fase E (Kelas X)', 'tingkat' => 'X', 'ket' => 'Fase Fondasi Kejuruan untuk peserta didik kelas X SMK.'],
        ['kode' => 'F', 'nama' => 'Fase F (Kelas XI - XII)', 'tingkat' => 'XI,XII', 'ket' => 'Fase Konsentrasi & Pendalaman Kejuruan untuk peserta didik kelas XI dan XII SMK.']
    ];

    $faseIdMap = [];
    foreach ($fases as $f) {
        $stmtF = $db->prepare("SELECT id FROM fase WHERE kurikulum_id = ? AND kode = ?");
        $stmtF->execute([$kmdkId, $f['kode']]);
        $fId = $stmtF->fetchColumn();

        if (!$fId) {
            $insF = $db->prepare("
                INSERT INTO fase (kurikulum_id, kode, nama, tingkat_kelas, keterangan)
                VALUES (?, ?, ?, ?, ?)
            ");
            $insF->execute([$kmdkId, $f['kode'], $f['nama'], $f['tingkat'], $f['ket']]);
            $fId = $db->lastInsertId();
            echo "✓ Fase {$f['kode']} ({$f['nama']}) berhasil ditambahkan (ID: $fId)\n";
        } else {
            echo "✓ Fase {$f['kode']} sudah ada (ID: $fId)\n";
        }
        $faseIdMap[$f['kode']] = $fId;
    }

    echo "\n=== 4. SEEDING KOMPONEN PENILAIAN KMDK ===\n";
    $komponenList = [
        ['kode' => 'tugas', 'nama' => 'Tugas Mandiri / Terstruktur', 'bobot' => 20.00, 'ket' => 'Penugasan portofolio KBM harian siswa.'],
        ['kode' => 'quiz',  'nama' => 'Kuis / Formatif Harian',     'bobot' => 20.00, 'ket' => 'Evaluasi formatif pemahaman tujuan pembelajaran.'],
        ['kode' => 'uts',   'nama' => 'Sumatif Tengah Semester (STS)', 'bobot' => 30.00, 'ket' => 'Ujian evaluasi capaian tengah semester.'],
        ['kode' => 'uas',   'nama' => 'Sumatif Akhir Semester (SAS)',  'bobot' => 30.00, 'ket' => 'Ujian akhir evaluasi kompetensi semester.']
    ];

    foreach ($komponenList as $kItem) {
        $stmtK = $db->prepare("SELECT id FROM komponen_penilaian WHERE kurikulum_id = ? AND kode_komponen = ?");
        $stmtK->execute([$kmdkId, $kItem['kode']]);
        if (!$stmtK->fetch()) {
            $insK = $db->prepare("
                INSERT INTO komponen_penilaian (kurikulum_id, nama_komponen, kode_komponen, bobot_persen, is_active, deskripsi)
                VALUES (?, ?, ?, ?, 1, ?)
            ");
            $insK->execute([$kmdkId, $kItem['nama'], $kItem['kode'], $kItem['bobot'], $kItem['ket']]);
            echo "✓ Komponen penilaian {$kItem['nama']} ({$kItem['bobot']}%) berhasil ditambahkan\n";
        }
    }

    echo "\n=== 5. DISTRIBUSI MAPEL KE KURIKULUM MERDEKA ===\n";
    $mapelRows = $db->query("SELECT id, kode_mapel, nama_mapel, jurusan_id, kkm FROM mata_pelajaran")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($mapelRows as $mRow) {
        $stmtM = $db->prepare("SELECT id FROM kurikulum_mapel WHERE kurikulum_id = ? AND mapel_id = ?");
        $stmtM->execute([$kmdkId, $mRow['id']]);
        if (!$stmtM->fetch()) {
            $insKM = $db->prepare("
                INSERT INTO kurikulum_mapel (kurikulum_id, mapel_id, fase_id, tingkat, jurusan_id, kelompok_mapel, alokasi_jp, kkm, is_active)
                VALUES (?, ?, ?, ?, ?, 'Kejuruan', 4, ?, 1)
            ");
            $insKM->execute([
                $kmdkId,
                $mRow['id'],
                $faseIdMap['E'],
                'X',
                $mRow['jurusan_id'],
                $mRow['kkm'] ?: 75
            ]);
            echo "✓ Mapel {$mRow['nama_mapel']} berhasil dikaitkan ke Kurikulum Merdeka\n";
        }
    }

    echo "\n=== 6. SEEDING CP & TP PERCONTOHAN ===\n";
    $sampleMapelId = $mapelRows[0]['id'] ?? 1;
    $stmtCp = $db->prepare("SELECT id FROM capaian_pembelajaran WHERE kurikulum_id = ? AND mapel_id = ?");
    $stmtCp->execute([$kmdkId, $sampleMapelId]);
    $cpId = $stmtCp->fetchColumn();

    if (!$cpId) {
        $insCp = $db->prepare("
            INSERT INTO capaian_pembelajaran (kurikulum_id, mapel_id, fase_id, kode_cp, elemen, deskripsi)
            VALUES (?, ?, ?, 'CP-PPLG-01', 'Pemrograman Berorientasi Objek & Web', 'Peserta didik mampu memahami konsep dasar arsitektur web modern, basis data relasional, dan implementasi logika sistem terpadu.')
        ");
        $insCp->execute([$kmdkId, $sampleMapelId, $faseIdMap['E']]);
        $cpId = $db->lastInsertId();

        $insTp = $db->prepare("
            INSERT INTO tujuan_pembelajaran (cp_id, kode_tp, materi_pokok, deskripsi)
            VALUES (?, ?, ?, ?)
        ");
        $insTp->execute([$cpId, 'TP-01.1', 'Arsitektur Web MVC', 'Memahami alur data MVC dan integrasi database relational.']);
        $insTp->execute([$cpId, 'TP-01.2', 'Pengolahan Data & Asesmen', 'Menguasai manipulasi query CRUD dan kalkulasi nilai secara dinamis.']);
        echo "✓ CP & TP Percontohan berhasil ditambahkan (CP ID: $cpId)\n";
    }

    echo "\n=== 7. PEMETAAN ROMBEL KELAS EXISTING KE KURIKULUM MERDEKA ===\n";
    $activeTaId = $db->query("SELECT id FROM tahun_ajaran WHERE is_active = 1 LIMIT 1")->fetchColumn();
    if (!$activeTaId) {
        $activeTaId = $db->query("SELECT id FROM tahun_ajaran ORDER BY id DESC LIMIT 1")->fetchColumn() ?: 1;
    }

    $kelasList = $db->query("SELECT id, nama_kelas, tingkat FROM kelas")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($kelasList as $k) {
        $targetFase = (strtoupper($k['tingkat']) === 'X') ? $faseIdMap['E'] : $faseIdMap['F'];

        $stmtRombelKur = $db->prepare("SELECT id FROM rombel_kurikulum WHERE tahun_ajaran_id = ? AND rombel_id = ?");
        $stmtRombelKur->execute([$activeTaId, $k['id']]);
        $rkId = $stmtRombelKur->fetchColumn();

        if (!$rkId) {
            $insRk = $db->prepare("
                INSERT INTO rombel_kurikulum (rombel_id, tahun_ajaran_id, kurikulum_id, fase_id, status)
                VALUES (?, ?, ?, ?, 'aktif')
            ");
            $insRk->execute([$k['id'], $activeTaId, $kmdkId, $targetFase]);
            echo "✓ Rombel {$k['nama_kelas']} (Tingkat {$k['tingkat']}) -> Kurikulum Merdeka berhasil dipetakan\n";
        } else {
            echo "✓ Rombel {$k['nama_kelas']} sudah memiliki pemetaan kurikulum\n";
        }
    }

    echo "\n=== 8. MIGRASI DATA NILAI EXISTING KE RAPOR DINAMIS ===\n";
    $legacyNilai = $db->query("
        SELECT nr.*, s.kelas_id, s.nama_lengkap, k.tingkat, k.nama_kelas, mp.nama_mapel, COALESCE(mp.kkm, 75) as kkm_mapel
        FROM nilai_rapor nr
        JOIN siswa s ON nr.siswa_id = s.id
        JOIN kelas k ON s.kelas_id = k.id
        JOIN mata_pelajaran mp ON nr.mapel_id = mp.id
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo "Ditemukan " . count($legacyNilai) . " rekaman nilai di nilai_rapor. Melakukan sinkronisasi ke tabel rapor_siswa & rapor_nilai_detail...\n";

    $migratedCount = 0;
    foreach ($legacyNilai as $ln) {
        $sId = (int)$ln['siswa_id'];
        $kId = (int)$ln['kelas_id'];
        $mId = (int)$ln['mapel_id'];
        $targetFaseId = (strtoupper($ln['tingkat']) === 'X') ? $faseIdMap['E'] : $faseIdMap['F'];
        $faseNamaSnapshot = (strtoupper($ln['tingkat']) === 'X') ? 'Fase E (Kelas X)' : 'Fase F (Kelas XI - XII)';

        // 1. Get or create rapor_siswa
        $stmtRapor = $db->prepare("SELECT id FROM rapor_siswa WHERE siswa_id = ? AND tahun_ajaran_id = ? AND semester = 'Ganjil'");
        $stmtRapor->execute([$sId, $activeTaId]);
        $raporId = $stmtRapor->fetchColumn();

        if (!$raporId) {
            $insRapor = $db->prepare("
                INSERT INTO rapor_siswa (siswa_id, tahun_ajaran_id, semester, rombel_id, kurikulum_id, fase_id, kurikulum_nama_snapshot, fase_nama_snapshot, tanggal_cetak, status, catatan_akademik)
                VALUES (?, ?, 'Ganjil', ?, ?, ?, 'Kurikulum Merdeka SMK', ?, CURDATE(), 'terverifikasi', 'Lulus kompetensi pembelajaran semester berjalan dengan baik.')
            ");
            $insRapor->execute([$sId, $activeTaId, $kId, $kmdkId, $targetFaseId, $faseNamaSnapshot]);
            $raporId = $db->lastInsertId();
        }

        // 2. Insert or update rapor_nilai_detail
        $akhir = (float)$ln['nilai_akhir'];
        $kkmVal = (float)$ln['kkm_mapel'];
        $predikat = 'B';
        if ($akhir >= 88) $predikat = 'A';
        elseif ($akhir >= 78) $predikat = 'B';
        elseif ($akhir >= 68) $predikat = 'C';
        else $predikat = 'D';

        $capaian = ($akhir >= $kkmVal) 
            ? "Menunjukkan pemahaman sangat baik dan konsisten dalam menuntaskan seluruh capaian pembelajaran {$ln['nama_mapel']}."
            : "Perlu bimbingan lebih intensif dalam menguasai kompetensi dasar pada mata pelajaran {$ln['nama_mapel']}.";

        $stmtDet = $db->prepare("SELECT id FROM rapor_nilai_detail WHERE rapor_id = ? AND mapel_id = ?");
        $stmtDet->execute([$raporId, $mId]);
        $detId = $stmtDet->fetchColumn();

        if (!$detId) {
            $insDet = $db->prepare("
                INSERT INTO rapor_nilai_detail (rapor_id, mapel_id, mapel_nama_snapshot, nilai_akhir, kkm, predikat, capaian_kompetensi)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $insDet->execute([$raporId, $mId, $ln['nama_mapel'], $akhir, $kkmVal, $predikat, $capaian]);
            $migratedCount++;
        }
    }

    echo "✓ Berhasil menyinkronkan $migratedCount nilai ke rapor dinamis!\n";
    echo "\n========================================================================\n";
    echo "MIGRASI & SEEDER SISTEM KURIKULUM BERHASIL DILAKSANAKAN DENGAN SEMPURNA!\n";
    echo "========================================================================\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
