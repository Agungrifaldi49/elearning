<?php
/**
 * ==============================================================================
 * JEMBATAN API SERVER PEMBAYARAN (FINANCE & BILLING BRIDGE)
 * E-Learning SMK Muthia Harapan Cicalengka <-> Server API Tata Usaha
 * ==============================================================================
 * 
 * File ini menghubungkan sistem E-Learning SMK Muthia Harapan Cicalengka dengan
 * REST API Tata Usaha (https://apitatausaha.smkmuthiaharapanclk.com).
 * 
 * PENGGUNAAN:
 * 1. Letakkan file ini di web server E-Learning (root direktori / public_html).
 * 2. Atur kredensial akun integrasi Tata Usaha pada config/tatausaha_api.json.
 * 3. Masukkan URL endpoint file ini ke modal "Tarik Data API" di Portal Pembayaran:
 *    Contoh: https://elearning.smkmuthiaharapan.sch.id/bridge_server_pembayaran.php
 * 4. Masukkan Token Rahasia Bridge (default: SMKMH_PAYMENT_SECRET_KEY_2026).
 * 5. Klik tombol "Tarik & Sinkronkan Sekarang".
 */

// Buffer output agar tidak ada whitespace atau notice PHP yang merusak JSON
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, X-API-KEY, Content-Type, Accept');

// Tangani HTTP OPTIONS Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ==============================================================================
// 1. MEMUAT KONFIGURASI DARI FILE AMAN (config/tatausaha_api.json)
// ==============================================================================
$configFile = __DIR__ . '/config/tatausaha_api.json';
$defaultConfig = [
    'api_base_url'        => 'https://apitatausaha.smkmuthiaharapanclk.com',
    'api_email'           => 'tatausaha@smkmuthiaharapanclk.com',
    'api_password'        => '',
    'api_jwt_token'       => '',
    'bridge_secret_token' => 'SMKMH_PAYMENT_SECRET_KEY_2026',
    'timeout_connect'     => 10,
    'timeout_response'    => 30,
    'verify_ssl'          => true,
    'cache_jwt'           => true
];

$config = $defaultConfig;
if (file_exists($configFile)) {
    $rawCfg = file_get_contents($configFile);
    $decodedCfg = json_decode($rawCfg, true);
    if (is_array($decodedCfg)) {
        $config = array_merge($defaultConfig, $decodedCfg);
    }
}

// Timpa konfigurasi via Environment Variables jika tersedia di server
if (getenv('TU_API_BASE_URL'))    $config['api_base_url'] = getenv('TU_API_BASE_URL');
if (getenv('TU_API_EMAIL'))       $config['api_email'] = getenv('TU_API_EMAIL');
if (getenv('TU_API_PASSWORD'))    $config['api_password'] = getenv('TU_API_PASSWORD');
if (getenv('TU_API_JWT'))         $config['api_jwt_token'] = getenv('TU_API_JWT');
if (getenv('TU_BRIDGE_SECRET'))   $config['bridge_secret_token'] = getenv('TU_BRIDGE_SECRET');

// ==============================================================================
// 2. VERIFIKASI KEAMANAN (SECRET BEARER TOKEN DARI E-LEARNING)
// ==============================================================================
$headers = function_exists('getallheaders') ? getallheaders() : [];
$authHeader = $headers['Authorization'] ?? ($headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? ''));
$apiKeyHeader = $headers['X-API-KEY'] ?? ($headers['x-api-key'] ?? ($_SERVER['HTTP_X_API_KEY'] ?? ''));

$providedToken = '';
if (!empty($authHeader) && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    $providedToken = trim($matches[1]);
} elseif (!empty($apiKeyHeader)) {
    $providedToken = trim($apiKeyHeader);
} elseif (!empty($_GET['token'])) {
    $providedToken = trim($_GET['token']);
} elseif (!empty($_POST['token'])) {
    $providedToken = trim($_POST['token']);
}

// Izinkan ping/health check status bridge tanpa mengekspos data keuangan
$action = strtolower(trim($_GET['action'] ?? ($_POST['action'] ?? 'pull')));
if ($action === 'ping' && empty($providedToken)) {
    sendJsonResponse(true, 'Bridge Server Pembayaran Aktif & Siap Menerima Permintaan', [
        'bridge_status' => 'online',
        'api_target'    => $config['api_base_url'],
        'time'          => date('Y-m-d H:i:s')
    ]);
}

// Verifikasi Secret Token menggunakan timing-safe comparison
$expectedToken = (string)$config['bridge_secret_token'];
if (empty($providedToken) || !hash_equals($expectedToken, $providedToken)) {
    sendJsonResponse(false, 'Akses Ditolak (HTTP 401): Secret Bearer Token jembatan API tidak valid atau belum diisi.', null, 401);
}

// ==============================================================================
// 3. KELAS KLIEN REST API TATA USAHA (cURL + HTTPS + JWT AUTH)
// ==============================================================================
class TataUsahaApiClient {
    private $baseUrl;
    private $email;
    private $password;
    private $staticJwt;
    private $timeoutConnect;
    private $timeoutResponse;
    private $verifySsl;
    private $cacheJwt;
    private $jwtCacheFile;

    public function __construct($config) {
        $this->baseUrl         = rtrim($config['api_base_url'], '/');
        $this->email           = (string)$config['api_email'];
        $this->password        = (string)$config['api_password'];
        $this->staticJwt       = (string)$config['api_jwt_token'];
        $this->timeoutConnect  = (int)($config['timeout_connect'] ?? 10);
        $this->timeoutResponse = (int)($config['timeout_response'] ?? 30);
        $this->verifySsl       = (bool)($config['verify_ssl'] ?? true);
        $this->cacheJwt        = (bool)($config['cache_jwt'] ?? true);
        $this->jwtCacheFile    = __DIR__ . '/config/.tu_jwt_cache.json';
    }

    /**
     * Request HTTP cURL Universal dengan penanganan error komprehensif
     */
    public function request($method, $endpoint, $payload = null, $customToken = null) {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeoutConnect);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutResponse);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySsl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifySsl ? 2 : 0);

        $headers = [
            'Accept: application/json',
            'User-Agent: E-Learning-SMK-Muthia-Harapan-Bridge/2.0'
        ];

        // Gunakan token JWT jika ada
        $token = $customToken !== null ? $customToken : $this->getValidJwtToken();
        if (!empty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $methodUpper = strtoupper($method);
        if ($methodUpper === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($payload !== null) {
                $jsonPayload = is_string($payload) ? $payload : json_encode($payload);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
                $headers[] = 'Content-Type: application/json; charset=utf-8';
            }
        } elseif ($methodUpper !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $methodUpper);
            if ($payload !== null) {
                $jsonPayload = is_string($payload) ? $payload : json_encode($payload);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
                $headers[] = 'Content-Type: application/json; charset=utf-8';
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrNo = curl_errno($ch);
        $curlErrMsg = curl_error($ch);
        curl_close($ch);

        // 1. Penanganan Kegagalan Koneksi / Timeout cURL
        if ($curlErrNo !== 0) {
            $errorDesc = $this->translateCurlError($curlErrNo, $curlErrMsg);
            return [
                'status'    => false,
                'http_code' => 504,
                'message'   => "Gagal menghubungi server API Tata Usaha: {$errorDesc}",
                'raw'       => null,
                'data'      => null
            ];
        }

        // 2. Parsing Respon JSON
        $jsonData = json_decode($rawResponse, true);
        $isJson = (json_last_error() === JSON_ERROR_NONE && is_array($jsonData));

        // 3. Penanganan HTTP Status Code Spesifik
        if ($httpCode === 401 || $httpCode === 403) {
            $apiMsg = $jsonData['message'] ?? 'Otorisasi ditolak (Token tidak valid atau kedaluwarsa).';
            return [
                'status'    => false,
                'http_code' => $httpCode,
                'message'   => "Autentikasi API Tata Usaha gagal ({$httpCode}): {$apiMsg}",
                'data'      => null,
                'raw'       => $rawResponse
            ];
        }

        if ($httpCode === 404) {
            return [
                'status'    => false,
                'http_code' => 404,
                'message'   => "Endpoint API Tata Usaha tidak ditemukan (HTTP 404 pada {$endpoint}).",
                'data'      => null,
                'raw'       => $rawResponse
            ];
        }

        if ($httpCode === 429) {
            return [
                'status'    => false,
                'http_code' => 429,
                'message'   => "Batas permintaan terlampaui (Rate Limit HTTP 429 pada server Tata Usaha). Harap tunggu sejenak.",
                'data'      => null,
                'raw'       => $rawResponse
            ];
        }

        if ($httpCode >= 500) {
            return [
                'status'    => false,
                'http_code' => $httpCode,
                'message'   => "Server API Tata Usaha mengalami kendala internal (HTTP {$httpCode}).",
                'data'      => null,
                'raw'       => $rawResponse
            ];
        }

        if (!$isJson) {
            return [
                'status'    => false,
                'http_code' => 502,
                'message'   => "Respon dari server API Tata Usaha bukan format JSON valid (HTTP {$httpCode}).",
                'data'      => null,
                'raw'       => substr((string)$rawResponse, 0, 500)
            ];
        }

        return [
            'status'    => ($httpCode >= 200 && $httpCode < 300),
            'http_code' => $httpCode,
            'message'   => $jsonData['message'] ?? 'Permintaan berhasil',
            'data'      => $jsonData,
            'raw'       => $rawResponse
        ];
    }

    /**
     * Memperoleh Token JWT yang sah (dari static token, cache, atau via login otomatis)
     */
    public function getValidJwtToken() {
        // Prioritas 1: Static JWT yang diisi di config jika ada
        if (!empty($this->staticJwt)) {
            return $this->staticJwt;
        }

        // Prioritas 2: Baca dari Cache jika belum kedaluwarsa
        if ($this->cacheJwt && file_exists($this->jwtCacheFile)) {
            $cached = json_decode(@file_get_contents($this->jwtCacheFile), true);
            if (!empty($cached['token']) && !empty($cached['expires_at']) && time() < ($cached['expires_at'] - 60)) {
                return $cached['token'];
            }
        }

        // Prioritas 3: Login otomatis menggunakan email & password
        if (empty($this->email) || empty($this->password)) {
            return ''; // Belum dikonfigurasi
        }

        $loginRes = $this->login($this->email, $this->password);
        if ($loginRes['status'] && !empty($loginRes['token'])) {
            if ($this->cacheJwt) {
                // Simpan token dengan masa aktif 3600 detik (1 jam)
                $expiresAt = time() + (int)($loginRes['expires_in'] ?? 3600);
                @file_put_contents($this->jwtCacheFile, json_encode([
                    'token'      => $loginRes['token'],
                    'expires_at' => $expiresAt,
                    'created_at' => date('Y-m-d H:i:s')
                ]), LOCK_EX);
            }
            return $loginRes['token'];
        }

        return '';
    }

    /**
     * Login ke API Tata Usaha via POST /api/auth/login
     */
    public function login($email, $password) {
        $url = $this->baseUrl . '/api/auth/login';
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => $email, 'password' => $password]));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeoutConnect);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutResponse);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySsl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifySsl ? 2 : 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json'
        ]);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errNo = curl_errno($ch);
        $errMsg = curl_error($ch);
        curl_close($ch);

        if ($errNo !== 0) {
            return ['status' => false, 'message' => "Koneksi ke endpoint login gagal: " . $this->translateCurlError($errNo, $errMsg)];
        }

        $json = json_decode($raw, true);
        if ($httpCode === 401 || $httpCode === 422) {
            $msg = $json['message'] ?? 'Email atau password akun integrasi Tata Usaha tidak sesuai.';
            return ['status' => false, 'message' => "Autentikasi Login Gagal ({$httpCode}): {$msg}"];
        }

        if ($httpCode !== 200 && $httpCode !== 201) {
            return ['status' => false, 'message' => "Login API Tata Usaha menghasilkan HTTP {$httpCode}: " . ($json['message'] ?? 'Unknown')];
        }

        // Ekstraksi token dari berbagai pola respons umum
        $token = $json['data']['token'] 
              ?? $json['data']['access_token'] 
              ?? $json['token'] 
              ?? $json['access_token'] 
              ?? '';

        if (empty($token)) {
            return ['status' => false, 'message' => 'Format token JWT tidak ditemukan pada respons login API Tata Usaha.'];
        }

        $expiresIn = $json['data']['expires_in'] ?? $json['expires_in'] ?? 3600;

        return [
            'status'     => true,
            'token'      => $token,
            'expires_in' => $expiresIn,
            'user'       => $json['data']['user'] ?? $json['user'] ?? null
        ];
    }

    /**
     * Mengambil seluruh halaman data dengan pagination otomatis (anti-halaman pertama saja)
     */
    public function fetchAllPages($endpoint, $maxPages = 50) {
        $allRecords = [];
        $page = 1;
        $separator = (strpos($endpoint, '?') !== false) ? '&' : '?';

        while ($page <= $maxPages) {
            $pageUrl = $endpoint . $separator . 'page=' . $page;
            $res = $this->request('GET', $pageUrl);

            if (!$res['status']) {
                // Jika halaman pertama error, kembalikan status error
                if ($page === 1) {
                    return $res;
                }
                // Jika halaman lanjutan gagal/404, hentikan pagination
                break;
            }

            $jsonData = $res['data'] ?? [];
            $records = [];

            // Pola 1: Laravel / Standard Paginator ($json['data']['data'])
            if (isset($jsonData['data']['data']) && is_array($jsonData['data']['data'])) {
                $records = $jsonData['data']['data'];
                $lastPage = $jsonData['data']['last_page'] ?? null;
            }
            // Pola 2: Paginator dengan data di root ($json['data'] array dan meta terpisah)
            elseif (isset($jsonData['data']) && is_array($jsonData['data'])) {
                $records = $jsonData['data'];
                $lastPage = $jsonData['meta']['last_page'] ?? $jsonData['pagination']['total_pages'] ?? null;
            }
            // Pola 3: Items wrapper ($json['items'])
            elseif (isset($jsonData['items']) && is_array($jsonData['items'])) {
                $records = $jsonData['items'];
                $lastPage = $jsonData['total_pages'] ?? null;
            }
            // Pola 4: Array langsung
            elseif (is_array($jsonData) && isset($jsonData[0])) {
                $records = $jsonData;
                $lastPage = 1;
            }

            if (empty($records)) {
                break; // Tidak ada data lagi
            }

            foreach ($records as $item) {
                $allRecords[] = $item;
            }

            // Cek apakah sudah mencapai halaman terakhir
            if ($lastPage !== null && $page >= (int)$lastPage) {
                break;
            }

            // Jika jumlah record sedikit, kemungkinan tidak ada halaman berikutnya
            if (count($records) < 10 && $lastPage === null) {
                break;
            }

            $page++;
        }

        return [
            'status'     => true,
            'total_data' => count($allRecords),
            'records'    => $allRecords
        ];
    }

    /**
     * Terjemahan kode error cURL ke bahasa Indonesia yang ramah pengguna
     */
    private function translateCurlError($errNo, $rawMsg) {
        switch ($errNo) {
            case CURLE_COULDNT_RESOLVE_HOST:
                return "Nama domain API Tata Usaha tidak dapat diakses (DNS host tidak ditemukan). Periksa koneksi internet server.";
            case CURLE_COULDNT_CONNECT:
                return "Tidak dapat terhubung ke server API Tata Usaha (Port tertutup atau server down).";
            case CURLE_OPERATION_TIMEDOUT:
                return "Waktu koneksi ke server API Tata Usaha habis (Connection/Response Timeout).";
            case CURLE_SSL_CONNECT_ERROR:
            case CURLE_PEER_FAILED_VERIFICATION:
                return "Sertifikat SSL API Tata Usaha gagal divalidasi. Pastikan sertifikat HTTPS aktif.";
            default:
                return "Kendala cURL ({$errNo}): {$rawMsg}";
        }
    }
}

// Inisialisasi Klien API Tata Usaha
$apiClient = new TataUsahaApiClient($config);

// ==============================================================================
// 4. ROUTER AKSI (HEALTH, ME, SUMMARY, TEST_LOGIN, PULL, SYNC_DIRECT)
// ==============================================================================

switch ($action) {

    // --- A. CEK STATUS KESEHATAN API TATA USAHA (/api/health) ---
    case 'health':
    case 'status':
        $healthRes = $apiClient->request('GET', '/api/health');
        sendJsonResponse($healthRes['status'], $healthRes['message'], [
            'api_target'     => $config['api_base_url'],
            'response_code'  => $healthRes['http_code'] ?? 0,
            'raw_health'     => $healthRes['data'] ?? null,
            'jwt_configured' => (!empty($config['api_jwt_token']) || (!empty($config['api_email']) && !empty($config['api_password']))),
            'timestamp'      => date('Y-m-d H:i:s')
        ]);
        break;

    // --- B. UJI COBA IDENTITAS TOKEN (/api/auth/me) ---
    case 'me':
        $token = $apiClient->getValidJwtToken();
        if (empty($token)) {
            sendJsonResponse(false, 'Token JWT belum tersedia. Pastikan email dan password diisi di config/tatausaha_api.json.', null, 400);
        }
        $meRes = $apiClient->request('GET', '/api/auth/me', null, $token);
        sendJsonResponse($meRes['status'], $meRes['message'], $meRes['data'] ?? null, $meRes['http_code'] ?? 200);
        break;

    // --- C. UJI COBA LOGIN API TATA USAHA ---
    case 'test_login':
        if (empty($config['api_email']) || empty($config['api_password'])) {
            sendJsonResponse(false, 'Kredensial email atau password belum diatur pada config/tatausaha_api.json.', null, 400);
        }
        $loginRes = $apiClient->login($config['api_email'], $config['api_password']);
        if ($loginRes['status']) {
            sendJsonResponse(true, 'Login ke API Tata Usaha Berhasil!', [
                'token_sample' => substr($loginRes['token'], 0, 15) . '...' . substr($loginRes['token'], -10),
                'expires_in'   => $loginRes['expires_in'],
                'user'         => $loginRes['user']
            ]);
        } else {
            sendJsonResponse(false, $loginRes['message'], null, 401);
        }
        break;

    // --- D. AMBIL RINGKASAN KEUANGAN (/api/dashboard/summary) ---
    case 'summary':
        $sumRes = $apiClient->request('GET', '/api/dashboard/summary');
        sendJsonResponse($sumRes['status'], $sumRes['message'], $sumRes['data'] ?? null, $sumRes['http_code'] ?? 200);
        break;

    // --- E. AMBIL DAFTAR SISWA SAJA (/api/siswa) ---
    case 'siswa':
        $siswaRes = $apiClient->fetchAllPages('/api/siswa');
        sendJsonResponse($siswaRes['status'], 'Data siswa berhasil ditarik', $siswaRes['records'] ?? [], 200);
        break;

    // --- F. AKSI UTAMA: TARIK DAN NORMALISASI DATA TAGIHAN UNTUK LMS (DEFAULT / PULL) ---
    case 'pull':
    default:
        pullAndNormalizeData($apiClient, $config);
        break;
}

// ==============================================================================
// 5. FUNGSI PENGAMBILAN & NORMALISASI DATA KEUANGAN
// ==============================================================================
function pullAndNormalizeData(TataUsahaApiClient $apiClient, array $config) {
    // 1. Cek apakah token JWT tersedia
    $jwtToken = $apiClient->getValidJwtToken();
    if (empty($jwtToken)) {
        sendJsonResponse(false, 'Kredensial API Tata Usaha belum lengkap: silakan masukkan email & password akun integrasi atau JWT token pada config/tatausaha_api.json.', null, 400);
    }

    $normalizedItems = [];
    $rawBills = [];
    $rawPayments = [];

    // 2. Ambil data transaksi pembayaran (/api/pembayaran) untuk pencocokan riwayat bayar
    $trxRes = $apiClient->fetchAllPages('/api/pembayaran');
    if ($trxRes['status'] && !empty($trxRes['records'])) {
        $rawPayments = $trxRes['records'];
    }

    // Kelompokkan pembayaran berdasarkan tagihan_id, kode_tagihan, atau nisn
    $paymentsByBill = [];
    foreach ($rawPayments as $p) {
        $keyBill = $p['kode_tagihan'] ?? ($p['tagihan_id'] ?? ($p['id_tagihan'] ?? null));
        if ($keyBill !== null) {
            $paymentsByBill[(string)$keyBill][] = [
                'nomor_transaksi'   => $p['nomor_transaksi'] ?? ($p['no_transaksi'] ?? ($p['kode_transaksi'] ?? ('TRX-' . ($p['id'] ?? uniqid())))),
                'nominal_bayar'     => (float)($p['nominal_bayar'] ?? ($p['nominal'] ?? ($p['jumlah_bayar'] ?? 0))),
                'tanggal_bayar'     => $p['tanggal_bayar'] ?? ($p['tgl_bayar'] ?? ($p['created_at'] ?? date('Y-m-d H:i:s'))),
                'metode_pembayaran' => $p['metode_pembayaran'] ?? ($p['metode'] ?? 'Kasir TU Sekolah'),
                'channel'           => $p['channel'] ?? 'Loket Keuangan Tata Usaha',
                'status'            => normalizeTransactionStatus($p['status'] ?? 'berhasil'),
                'catatan'           => $p['catatan'] ?? ($p['keterangan'] ?? 'Sinkronisasi dari API Tata Usaha')
            ];
        }
    }

    // 3. Strategi Utama: Ambil daftar tagihan siswa (/api/tagihan-siswa)
    $billsRes = $apiClient->fetchAllPages('/api/tagihan-siswa');
    if ($billsRes['status'] && !empty($billsRes['records'])) {
        $rawBills = $billsRes['records'];
    }

    // 4. Strategi Cadangan: Jika /api/tagihan-siswa kosong atau 404, coba /api/tagihan master atau /api/siswa
    if (empty($rawBills)) {
        // Coba per-siswa: /api/siswa lalu ambil /api/siswa/{id}/tagihan
        $siswaRes = $apiClient->fetchAllPages('/api/siswa', 20);
        if ($siswaRes['status'] && !empty($siswaRes['records'])) {
            foreach ($siswaRes['records'] as $s) {
                $sid = $s['id'] ?? null;
                if (!$sid) continue;

                $sBillsRes = $apiClient->request('GET', "/api/siswa/{$sid}/tagihan");
                if ($sBillsRes['status'] && !empty($sBillsRes['data'])) {
                    $sList = $sBillsRes['data']['data'] ?? ($sBillsRes['data']['tagihan'] ?? ($sBillsRes['data'] ?? []));
                    if (is_array($sList)) {
                        foreach ($sList as $bItem) {
                            if (is_array($bItem)) {
                                $bItem['nisn'] = $bItem['nisn'] ?? ($s['nisn'] ?? '');
                                $bItem['nis']  = $bItem['nis'] ?? ($s['nis'] ?? '');
                                $rawBills[] = $bItem;
                            }
                        }
                    }
                }
            }
        }
    }

    // 5. Jika kedua strategi di atas belum mengembalikan tagihan, coba endpoint master /api/tagihan
    if (empty($rawBills)) {
        $masterTagihanRes = $apiClient->fetchAllPages('/api/tagihan');
        if ($masterTagihanRes['status'] && !empty($masterTagihanRes['records'])) {
            $rawBills = $masterTagihanRes['records'];
        }
    }

    // Jika server Tata Usaha memang belum memiliki tagihan apa pun
    if (empty($rawBills)) {
        sendJsonResponse(true, 'Koneksi ke API Tata Usaha berhasil, namun belum ada data tagihan yang diterbitkan di server Tata Usaha.', [
            'total_data' => 0,
            'data'       => []
        ]);
    }

    // 6. Normalisasi Seluruh Data Tagihan Sesuai Skema E-Learning
    foreach ($rawBills as $idx => $b) {
        $nisn = trim((string)($b['nisn'] ?? ($b['siswa']['nisn'] ?? '')));
        $nis  = trim((string)($b['nis'] ?? ($b['siswa']['nis'] ?? '')));

        // Lewati jika tidak ada satupun identitas siswa
        if (empty($nisn) && empty($nis)) {
            continue;
        }

        $idBill      = $b['id'] ?? ($idx + 1);
        $kodeTagihan = trim((string)($b['kode_tagihan'] ?? ($b['no_tagihan'] ?? ('TU-TAG-' . $idBill))));
        $judul       = trim((string)($b['judul'] ?? ($b['nama_tagihan'] ?? ($b['jenis_pembayaran'] ?? 'Iuran Sekolah'))));
        $jenis       = trim((string)($b['jenis_pembayaran'] ?? ($b['kategori'] ?? 'SPP')));
        $nominal     = (float)($b['nominal'] ?? ($b['total_tagihan'] ?? ($b['jumlah'] ?? 0)));
        $terbayar    = (float)($b['nominal_terbayar'] ?? ($b['terbayar'] ?? ($b['total_bayar'] ?? 0)));

        // Tambahkan riwayat pembayaran yang cocok dengan tagihan ini
        $riwayatList = [];
        if (isset($paymentsByBill[$kodeTagihan])) {
            $riwayatList = $paymentsByBill[$kodeTagihan];
        } elseif (isset($paymentsByBill[(string)$idBill])) {
            $riwayatList = $paymentsByBill[(string)$idBill];
        }

        // Jika nominal terbayar di tagihan 0 tapi ada riwayat pembayaran yang berhasil, akumulasikan
        if ($terbayar <= 0 && !empty($riwayatList)) {
            foreach ($riwayatList as $rw) {
                if ($rw['status'] === 'berhasil') {
                    $terbayar += $rw['nominal_bayar'];
                }
            }
        }

        // Kalkulasi sisa tunggakan dan status pelunasan
        $sisa = max(0.00, $nominal - $terbayar);
        if ($sisa <= 0 && $nominal > 0) {
            $status = 'lunas';
        } elseif ($terbayar > 0 && $sisa > 0) {
            $status = 'sebagian';
        } else {
            $status = 'belum_lunas';
        }

        $periode     = $b['periode_bulan'] ?? ($b['bulan'] ?? null);
        $tahunAjaran = $b['tahun_ajaran'] ?? ($b['ta'] ?? '2025/2026');
        $jatuhTempo  = $b['tanggal_jatuh_tempo'] ?? ($b['jatuh_tempo'] ?? ($b['due_date'] ?? null));
        $keterangan  = $b['keterangan'] ?? ($b['deskripsi'] ?? 'Sinkronisasi API Tata Usaha SMK Muthia Harapan');

        $normalizedItems[] = [
            'nisn'                => $nisn,
            'nis'                 => $nis,
            'kode_tagihan'        => $kodeTagihan,
            'judul'               => $judul,
            'jenis_pembayaran'    => $jenis,
            'nominal'             => $nominal,
            'nominal_terbayar'    => $terbayar,
            'sisa_tagihan'        => $sisa,
            'status'              => $status,
            'periode_bulan'       => $periode,
            'tahun_ajaran'        => $tahunAjaran,
            'tanggal_jatuh_tempo' => $jatuhTempo,
            'keterangan'          => $keterangan,
            'riwayat'             => $riwayatList
        ];
    }

    // 7. Kembalikan Payload Standar yang Siap Diconsume oleh PembayaranModel::pullFromRemoteServer
    sendJsonResponse(true, 'Data tagihan & pembayaran berhasil disinkronkan dari API Tata Usaha.', [
        'total_data' => count($normalizedItems),
        'data'       => $normalizedItems,
        'meta'       => [
            'source'         => $config['api_base_url'],
            'synced_at'      => date('Y-m-d H:i:s'),
            'total_tagihan'  => count($normalizedItems),
            'total_transaksi'=> count($rawPayments)
        ]
    ]);
}

/**
 * Normalisasi status transaksi agar sesuai dengan ENUM e-learning
 */
function normalizeTransactionStatus($rawStatus) {
    $s = strtolower(trim((string)$rawStatus));
    if (in_array($s, ['berhasil', 'lunas', 'paid', 'success', 'approved'])) {
        return 'berhasil';
    }
    if (in_array($s, ['pending', 'menunggu', 'process'])) {
        return 'pending';
    }
    return 'batal';
}

/**
 * Kirim respon JSON terstruktur dan hentikan eksekusi script
 */
function sendJsonResponse($status, $message, $data = null, $httpCode = 200) {
    // Bersihkan buffer agar tidak ada output lain
    if (ob_get_length()) {
        ob_clean();
    }

    http_response_code($httpCode);

    $payload = [
        'status'  => (bool)$status,
        'message' => (string)$message
    ];

    if ($data !== null) {
        if (is_array($data) && isset($data['data'])) {
            // Jika data sudah terstruktur dengan key data
            $payload = array_merge($payload, $data);
        } else {
            $payload['data'] = $data;
            if (is_array($data)) {
                $payload['total_data'] = count($data);
            }
        }
    }

    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit();
}
