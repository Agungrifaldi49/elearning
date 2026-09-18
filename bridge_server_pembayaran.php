<?php
/**
 * ==============================================================================
 * SCRIPT JEMBATAN API SERVER PEMBAYARAN (UPLOAD FILE INI KE SERVER PEMBAYARAN)
 * E-Learning SMK Muthia Harapan Cicalengka
 * ==============================================================================
 * 
 * CARA PAKAI:
 * 1. Upload file ini ke Server Pembayaran Anda (misal di folder public_html/api/tagihan.php)
 * 2. Sesuaikan konfigurasi DATABASE SERVER PEMBAYARAN di bawah ini (baris 21-25).
 * 3. Sesuaikan query SELECT dengan nama tabel dan kolom pembayaran di server Anda (baris 45).
 * 4. Buka LMS E-Learning (Admin > Portal Pembayaran > Tarik Data Lintas Server) 
 *    dan masukkan URL file ini beserta TOKEN RAHASIA-nya.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, X-API-KEY, Content-Type');

// --- 1. KONFIGURASI DATABASE SERVER PEMBAYARAN ---
$db_host = '127.0.0.1'; // Host database di server pembayaran
$db_name = 'nama_database_pembayaran'; // Nama database keuangan / SPP
$db_user = 'root'; // User database keuangan
$db_pass = ''; // Password database keuangan

// --- 2. TOKEN KEAMANAN (Ganti dengan kata sandi rahasia Anda) ---
$secret_token = 'SMKMH_PAYMENT_SECRET_KEY_2026';

// Verifikasi Token
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? ($headers['authorization'] ?? '');
$apiKeyHeader = $headers['X-API-KEY'] ?? ($headers['x-api-key'] ?? '');
$providedToken = '';

if (!empty($authHeader) && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    $providedToken = trim($matches[1]);
} elseif (!empty($apiKeyHeader)) {
    $providedToken = trim($apiKeyHeader);
} elseif (isset($_GET['token'])) {
    $providedToken = trim($_GET['token']);
}

if ($providedToken !== $secret_token) {
    http_response_code(401);
    echo json_encode([
        'status' => false,
        'message' => 'Akses ditolak: Token rahasia API tidak valid atau belum diisi.'
    ]);
    exit();
}

// --- 3. KONEKSI KE DATABASE PEMBAYARAN ---
try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Gagal koneksi ke database pembayaran: ' . $e->getMessage()
    ]);
    exit();
}

// --- 4. QUERY MENGAMBIL DATA TAGIHAN & PEMBAYARAN ---
/**
 * CATATAN PENTING:
 * Sesuaikan nama tabel 'tagihan_siswa' dan nama kolom di bawah dengan struktur tabel di server Anda.
 * Yang wajib ada: nisn (atau nis), kode_tagihan, judul, nominal, nominal_terbayar, status
 */
try {
    $sql = "
        SELECT 
            nisn,
            nis,
            kode_tagihan,
            judul,
            jenis_pembayaran,
            nominal,
            nominal_terbayar,
            periode_bulan,
            tahun_ajaran,
            tanggal_jatuh_tempo,
            status,
            keterangan
        FROM tagihan_siswa
        ORDER BY id DESC
    ";
    
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll();

    echo json_encode([
        'status' => true,
        'total_data' => count($data),
        'data' => $data
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Gagal menjalankan query: ' . $e->getMessage()
    ]);
}
