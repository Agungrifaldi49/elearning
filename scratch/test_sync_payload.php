<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PembayaranModel.php';

$db = Database::getConnection();
$model = new PembayaranModel();

// Ambil siswa pertama dari database untuk testing
$siswa = $db->query("SELECT id, nis, nisn, nama_lengkap FROM siswa LIMIT 1")->fetch(PDO::FETCH_ASSOC);
echo "Testing dengan siswa: ID {$siswa['id']} | NIS {$siswa['nis']} | NISN {$siswa['nisn']} | Nama: {$siswa['nama_lengkap']}\n";

$testPayload = [
    [
        'nisn' => $siswa['nisn'],
        'nis'  => $siswa['nis'],
        'kode_tagihan' => 'TU-TEST-SPP-01',
        'judul' => 'SPP Bulan Oktober 2026 (Test Integrasi TU)',
        'jenis_pembayaran' => 'SPP',
        'nominal' => 250000,
        'nominal_terbayar' => 250000,
        'status' => 'lunas',
        'periode_bulan' => 'Oktober 2026',
        'tahun_ajaran' => '2025/2026',
        'tanggal_jatuh_tempo' => '2026-10-10',
        'keterangan' => 'Tagihan Uji Coba Bridge API Tata Usaha',
        'riwayat' => [
            [
                'nomor_transaksi' => 'TRX-TU-TEST-1001',
                'nominal_bayar' => 250000,
                'tanggal_bayar' => '2026-10-11 08:00:00',
                'metode_pembayaran' => 'Kasir TU Sekolah',
                'channel' => 'Loket Keuangan Tata Usaha',
                'status' => 'berhasil',
                'catatan' => 'Lunas Uji Coba'
            ]
        ]
    ]
];

echo "\n--- 1. Eksekusi Sync Pertama Kali ---\n";
$res1 = $model->syncExternalPaymentData($testPayload);
print_r($res1);

$countTagihan1 = $db->query("SELECT COUNT(*) FROM pembayaran_tagihan WHERE kode_tagihan = 'TU-TEST-SPP-01'")->fetchColumn();
$countRiwayat1 = $db->query("SELECT COUNT(*) FROM pembayaran_riwayat WHERE nomor_transaksi = 'TRX-TU-TEST-1001'")->fetchColumn();
echo "Jumlah Tagihan di DB: {$countTagihan1}, Jumlah Riwayat di DB: {$countRiwayat1}\n";

echo "\n--- 2. Eksekusi Sync Kedua Kali (Idempotensi: Tidak Boleh Duplikat) ---\n";
$res2 = $model->syncExternalPaymentData($testPayload);
print_r($res2);

$countTagihan2 = $db->query("SELECT COUNT(*) FROM pembayaran_tagihan WHERE kode_tagihan = 'TU-TEST-SPP-01'")->fetchColumn();
$countRiwayat2 = $db->query("SELECT COUNT(*) FROM pembayaran_riwayat WHERE nomor_transaksi = 'TRX-TU-TEST-1001'")->fetchColumn();
echo "Jumlah Tagihan di DB: {$countTagihan2}, Jumlah Riwayat di DB: {$countRiwayat2}\n";

// Bersihkan data uji coba agar database tetap bersih
$db->exec("DELETE FROM pembayaran_riwayat WHERE nomor_transaksi = 'TRX-TU-TEST-1001'");
$db->exec("DELETE FROM pembayaran_tagihan WHERE kode_tagihan = 'TU-TEST-SPP-01'");
echo "\nData pengujian telah dibersihkan.\n";
