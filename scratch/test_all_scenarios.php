<?php
/**
 * Test Suite: Validasi 6 Skenario Pengujian Kurikulum Dinamis (Skenario A s/d F)
 * E-Learning SMK Muthia Harapan Cicalengka
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/CurriculumModel.php';
require_once __DIR__ . '/../models/AcademicModel.php';
require_once __DIR__ . '/../models/NilaiModel.php';

$db = Database::getConnection();
$currModel = new CurriculumModel();
$acadModel = new AcademicModel();

echo "========================================================================\n";
echo "   PENGUJIAN VALIDASI SKENARIO SISTEM KURIKULUM DINAMIS (A s/d F)       \n";
echo "========================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest($name, $condition, $detail = '') {
    global $passCount, $failCount;
    if ($condition) {
        echo "✓ [PASS] $name\n";
        if ($detail) echo "   Detail: $detail\n";
        $passCount++;
    } else {
        echo "✗ [FAIL] $name\n";
        if ($detail) echo "   Error: $detail\n";
        $failCount++;
    }
}

try {
    // --- PERSIAPAN DATA PENGUJIAN ---
    // 1. Dapatkan atau buat rombel 'X PPLG'
    $stmtK = $db->prepare("SELECT id FROM kelas WHERE nama_kelas LIKE '%PPLG%' LIMIT 1");
    $stmtK->execute();
    $xPplgId = $stmtK->fetchColumn();

    if (!$xPplgId) {
        $jurId = (int)$db->query("SELECT id FROM jurusan LIMIT 1")->fetchColumn() ?: 1;
        $insK = $db->prepare("INSERT INTO kelas (nama_kelas, jurusan_id, tingkat) VALUES ('X PPLG 1', ?, 'X')");
        $insK->execute([$jurId]);
        $xPplgId = $db->lastInsertId();
    }

    // 2. Siapkan 3 Tahun Ajaran: 2026/2027, 2027/2028, 2029/2030
    function getOrCreateTa($tahunStr) {
        global $db;
        $stmt = $db->prepare("SELECT id FROM tahun_ajaran WHERE tahun = ? OR tahun_ajaran = ? LIMIT 1");
        $stmt->execute([$tahunStr, $tahunStr]);
        $id = $stmt->fetchColumn();
        if (!$id) {
            $ins = $db->prepare("INSERT INTO tahun_ajaran (tahun, status, tahun_ajaran, semester, is_active) VALUES (?, 'non-aktif', ?, 'Ganjil', 0)");
            $ins->execute([$tahunStr, $tahunStr]);
            $id = $db->lastInsertId();
        }
        return (int)$id;
    }

    $ta2026Id = getOrCreateTa('2026/2027');
    $ta2027Id = getOrCreateTa('2027/2028');
    $ta2029Id = getOrCreateTa('2029/2030');

    // 3. Dapatkan Kurikulum Merdeka (KMDK)
    $kmdk = $db->query("SELECT id FROM kurikulum WHERE kode = 'KMDK'")->fetch(PDO::FETCH_ASSOC);
    $kmdkId = (int)($kmdk['id'] ?? 1);

    // Dapatkan Fase E KMDK
    $stmtF = $db->prepare("SELECT id FROM fase WHERE kurikulum_id = ? AND kode = 'E' LIMIT 1");
    $stmtF->execute([$kmdkId]);
    $faseEId = (int)$stmtF->fetchColumn();

    // 4. Siapkan 'Kurikulum Baru' (K2029)
    $stmtKurBaru = $db->prepare("SELECT id FROM kurikulum WHERE kode = 'K2029'");
    $stmtKurBaru->execute();
    $k2029Id = $stmtKurBaru->fetchColumn();

    if (!$k2029Id) {
        $resKB = $currModel->addKurikulum([
            'kode' => 'K2029',
            'nama' => 'Kurikulum Vokasi Industri 2029',
            'tahun_mulai' => 2029,
            'tahun_selesai' => null,
            'status' => 'aktif',
            'deskripsi' => 'Kurikulum generasi baru berbasis kecerdasan buatan dan cloud computing.'
        ]);
        $k2029Id = (int)$resKB['id'];
    }

    // Bersihkan data uji rombel_kurikulum khusus untuk X PPLG di 3 tahun ajaran uji agar reproducible
    $db->prepare("DELETE FROM rombel_kurikulum WHERE rombel_id = ? AND tahun_ajaran_id IN (?, ?, ?)")
       ->execute([$xPplgId, $ta2026Id, $ta2027Id, $ta2029Id]);

    // =========================================================================
    // SKENARIO A: 2026/2027 + X PPLG + Kurikulum Merdeka
    // =========================================================================
    echo "--- SKENARIO A: 2026/2027 + X PPLG + Kurikulum Merdeka ---\n";
    $resA = $currModel->assignRombelKurikulum([
        'rombel_id' => $xPplgId,
        'tahun_ajaran_id' => $ta2026Id,
        'kurikulum_id' => $kmdkId,
        'fase_id' => $faseEId,
        'status' => 'selesai' // Periode masa lalu
    ]);
    assertTest("Skenario A: Tersimpan pada Tahun Ajaran 2026/2027", $resA['status'] === true, $resA['message']);

    // =========================================================================
    // SKENARIO B: 2027/2028 + X PPLG + Kurikulum Merdeka
    // =========================================================================
    echo "\n--- SKENARIO B: 2027/2028 + X PPLG + Kurikulum Merdeka ---\n";
    $resB = $currModel->assignRombelKurikulum([
        'rombel_id' => $xPplgId,
        'tahun_ajaran_id' => $ta2027Id,
        'kurikulum_id' => $kmdkId,
        'fase_id' => $faseEId,
        'status' => 'selesai' // Periode masa lalu
    ]);
    assertTest("Skenario B: Tersimpan pada Tahun Ajaran 2027/2028", $resB['status'] === true, $resB['message']);

    // =========================================================================
    // SKENARIO C: 2029/2030 + X PPLG + Kurikulum Baru
    // =========================================================================
    echo "\n--- SKENARIO C: 2029/2030 + X PPLG + Kurikulum Baru (K2029) ---\n";
    $resC = $currModel->assignRombelKurikulum([
        'rombel_id' => $xPplgId,
        'tahun_ajaran_id' => $ta2029Id,
        'kurikulum_id' => $k2029Id,
        'fase_id' => null,
        'status' => 'aktif' // Periode berjalan
    ]);
    assertTest("Skenario C: Tersimpan pada Tahun Ajaran 2029/2030 dengan Kurikulum Baru", $resC['status'] === true, $resC['message']);

    // Verifikasi ketiga riwayat tersimpan berdampingan tanpa bentrok
    $checkHistori = $db->prepare("SELECT COUNT(*) FROM rombel_kurikulum WHERE rombel_id = ? AND tahun_ajaran_id IN (?, ?, ?)");
    $checkHistori->execute([$xPplgId, $ta2026Id, $ta2027Id, $ta2029Id]);
    $totalHistori = (int)$checkHistori->fetchColumn();
    assertTest("Verifikasi Histori: 3 rekaman akademik tersimpan utuh dan terisolasi", $totalHistori === 3, "Total data: $totalHistori/3");

    // =========================================================================
    // SKENARIO D: UJI BENTROK (2 Kurikulum Aktif Pada Rombel & TA yang Sama)
    // =========================================================================
    echo "\n--- SKENARIO D: UJI BENTROK 2 KURIKULUM AKTIF PADA SATU ROMBEL & TAHUN AJARAN ---\n";
    // X PPLG di 2029/2030 SUDAH memiliki Kurikulum Baru (K2029) dengan status 'aktif'.
    // Sekarang coba masukkan Kurikulum Merdeka (KMDK) juga sebagai 'aktif' untuk X PPLG di 2029/2030:
    $resD = $currModel->assignRombelKurikulum([
        'rombel_id' => $xPplgId,
        'tahun_ajaran_id' => $ta2029Id,
        'kurikulum_id' => $kmdkId,
        'fase_id' => $faseEId,
        'status' => 'aktif'
    ]);
    assertTest(
        "Skenario D: Sistem MENOLAK kurikulum aktif ganda pada rombel yang sama",
        $resD['status'] === false,
        "Pesan Penolakan: " . ($resD['message'] ?? '')
    );

    // =========================================================================
    // SKENARIO E: ARSIP / NONAKTIFKAN KURIKULUM LAMA & UJI INTEGRITAS RAPOR
    // =========================================================================
    echo "\n--- SKENARIO E: ARSIP KURIKULUM LAMA & HISTORI RAPOR TETAP BISA DIBUKA ---\n";
    // 1. Ambil siswa yang memiliki riwayat nilai di bawah Kurikulum Merdeka
    $sampleSiswaId = (int)$db->query("SELECT DISTINCT siswa_id FROM nilai_rapor LIMIT 1")->fetchColumn() ?: 1;

    $currModel->generateOrSyncRaporSiswa($sampleSiswaId, $ta2026Id, 'Ganjil');

    // 2. Ubah status Kurikulum Merdeka menjadi 'non-aktif' atau 'arsip'
    $db->prepare("UPDATE kurikulum SET status = 'arsip' WHERE id = ?")->execute([$kmdkId]);

    // 3. Baca kembali rapor siswa tahun 2026
    $raporLama = $currModel->getRaporSiswa($sampleSiswaId, $ta2026Id, 'Ganjil');

    $isRaporIntact = (
        !empty($raporLama) &&
        !empty($raporLama['kurikulum_nama_snapshot']) &&
        !empty($raporLama['nilai_list']) &&
        count($raporLama['nilai_list']) > 0
    );

    assertTest(
        "Skenario E: Rapor siswa lama tetap utuh & memiliki snapshot nama kurikulum saat kurikulum diarsipkan",
        $isRaporIntact,
        "Snapshot Kurikulum: " . ($raporLama['kurikulum_nama_snapshot'] ?? '-') . " | Total Mapel: " . count($raporLama['nilai_list'] ?? [])
    );

    // Kembalikan status Kurikulum Merdeka ke aktif
    $db->prepare("UPDATE kurikulum SET status = 'aktif' WHERE id = ?")->execute([$kmdkId]);

    // =========================================================================
    // SKENARIO F: TAMBAH MAPEL BARU KE KURIKULUM BARU (TANPA ALTER TABEL NILAI)
    // =========================================================================
    echo "\n--- SKENARIO F: MAPEL BARU DITAMBAHKAN TANPA MERUBAH STRUKTUR TABEL RAPOR ---\n";
    // 1. Tambah mapel baru di master mata_pelajaran
    $stmtMapelBaru = $db->prepare("SELECT id FROM mata_pelajaran WHERE kode_mapel = 'MP-AI-01'");
    $stmtMapelBaru->execute();
    $aiMapelId = $stmtMapelBaru->fetchColumn();

    if (!$aiMapelId) {
        $insM = $db->prepare("INSERT INTO mata_pelajaran (kode_mapel, nama_mapel, kkm) VALUES ('MP-AI-01', 'Kecerdasan Buatan & Cloud Computing', 80.0)");
        $insM->execute();
        $aiMapelId = $db->lastInsertId();
    }

    // 2. Kaitkan ke Kurikulum Baru (K2029)
    $resF = $currModel->addStrukturMapel([
        'kurikulum_id' => $k2029Id,
        'mapel_id' => $aiMapelId,
        'tingkat' => 'X',
        'kelompok_mapel' => 'Kejuruan Khusus',
        'alokasi_jp' => 4,
        'kkm' => 80.0
    ]);
    assertTest("Skenario F: Mapel baru berhasil dikaitkan ke Kurikulum Baru", $resF['status'] === true, $resF['message']);

    // 3. Masukkan nilai untuk mapel baru ini di rapor tahun 2029
    // Buat header rapor 2029
    $stmtRapor2029 = $db->prepare("SELECT id FROM rapor_siswa WHERE siswa_id = ? AND tahun_ajaran_id = ? AND semester = 'Ganjil'");
    $stmtRapor2029->execute([$sampleSiswaId, $ta2029Id]);
    $rapor2029Id = $stmtRapor2029->fetchColumn();

    if (!$rapor2029Id) {
        $insR29 = $db->prepare("
            INSERT INTO rapor_siswa (siswa_id, tahun_ajaran_id, semester, rombel_id, kurikulum_id, kurikulum_nama_snapshot, status)
            VALUES (?, ?, 'Ganjil', ?, ?, 'Kurikulum Vokasi Industri 2029', 'terverifikasi')
        ");
        $insR29->execute([$sampleSiswaId, $ta2029Id, $xPplgId, $k2029Id]);
        $rapor2029Id = $db->lastInsertId();
    }

    // Insert baris mapel baru di rapor_nilai_detail
    $insDet29 = $db->prepare("
        INSERT INTO rapor_nilai_detail (rapor_id, mapel_id, mapel_nama_snapshot, nilai_akhir, kkm, predikat, capaian_kompetensi)
        VALUES (?, ?, 'Kecerdasan Buatan & Cloud Computing', 92.5, 80.0, 'A', 'Sangat mahir merancang model AI dan integrasi pipeline cloud.')
        ON DUPLICATE KEY UPDATE nilai_akhir = 92.5, predikat = 'A'
    ");
    $insDet29->execute([$rapor2029Id, $aiMapelId]);

    // Baca kembali rapor tahun 2029
    $rapor2029 = $currModel->getRaporSiswa($sampleSiswaId, $ta2029Id, 'Ganjil');
    $hasNewMapel = false;
    foreach ($rapor2029['nilai_list'] ?? [] as $nl) {
        if ($nl['mapel_id'] == $aiMapelId) {
            $hasNewMapel = true;
            break;
        }
    }

    assertTest(
        "Skenario F: Mapel baru tampil dinamis sebagai baris rapor tanpa modifikasi kolom tabel rapor",
        $hasNewMapel,
        "Mapel 'Kecerdasan Buatan & Cloud Computing' tampil dengan Nilai: 92.5 (Predikat A)"
    );

    // =========================================================================
    // RINGKASAN HASIL PENGUJIAN
    // =========================================================================
    echo "\n========================================================================\n";
    echo "HASIL PENGUJIAN AKHIR: $passCount BERHASIL (PASS) / $failCount GAGAL (FAIL)\n";
    echo "========================================================================\n";

} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
