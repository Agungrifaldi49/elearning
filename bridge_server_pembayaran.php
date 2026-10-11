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

// Naikkan batas waktu eksekusi agar pengambilan data massal berjalan lancar
@set_time_limit(180);
@ini_set('memory_limit', '256M');

// Buffer output agar tidak ada whitespace atau notice PHP yang merusak JSON
ob_start();

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, X-API-KEY, Content-Type, Accept');
}

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
    'api_email'           => 'admin@mhc.com',
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

// Timpa konfigurasi via Environment Variables jika tersedia di server hosting
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

// Izinkan ping status bridge tanpa mengekspos data keuangan
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
        if (!empty($this->staticJwt)) {
            return $this->staticJwt;
        }

        if ($this->cacheJwt && file_exists($this->jwtCacheFile)) {
            $cached = json_decode(@file_get_contents($this->jwtCacheFile), true);
            if (!empty($cached['token']) && !empty($cached['expires_at']) && time() < ($cached['expires_at'] - 60)) {
                return $cached['token'];
            }
        }

        if (empty($this->email) || empty($this->password)) {
            return '';
        }

        $loginRes = $this->login($this->email, $this->password);
        if ($loginRes['status'] && !empty($loginRes['token'])) {
            if ($this->cacheJwt) {
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
     * Eksekusi batch HTTP request cURL paralel berkecepatan tinggi
     */
    public function fetchMultiParallel(array $urls, $concurrency = 15) {
        $jwtToken = $this->getValidJwtToken();
        $headers = [
            'Accept: application/json',
            'User-Agent: E-Learning-SMK-Muthia-Harapan-Bridge/2.0'
        ];
        if (!empty($jwtToken)) {
            $headers[] = 'Authorization: Bearer ' . $jwtToken;
        }

        $allResults = [];
        $chunks = array_chunk($urls, $concurrency, true);

        foreach ($chunks as $chunk) {
            $mh = curl_multi_init();
            $handles = [];

            foreach ($chunk as $key => $relUrl) {
                $fullUrl = (strpos($relUrl, 'http') === 0) ? $relUrl : ($this->baseUrl . '/' . ltrim($relUrl, '/'));
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $fullUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeoutConnect);
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutResponse);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySsl);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifySsl ? 2 : 0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_multi_add_handle($mh, $ch);
                $handles[$key] = $ch;
            }

            $running = null;
            do {
                curl_multi_exec($mh, $running);
                curl_multi_select($mh, 0.2);
            } while ($running > 0);

            foreach ($handles as $key => $ch) {
                $raw = curl_multi_getcontent($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $json = json_decode($raw, true);
                $allResults[$key] = [
                    'status'    => ($httpCode >= 200 && $httpCode < 300),
                    'http_code' => $httpCode,
                    'data'      => $json
                ];
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
            curl_multi_close($mh);
        }

        return $allResults;
    }

    /**
     * Mengambil seluruh halaman data dengan cURL multi paralel super cepat (hingga ratusan halaman)
     */
    public function fetchAllPagesParallel($endpoint, $maxPages = 200, $concurrency = 15) {
        $separator = (strpos($endpoint, '?') !== false) ? '&' : '?';
        $firstUrl = $endpoint . $separator . 'page=1';
        $firstRes = $this->request('GET', $firstUrl);

        if (!$firstRes['status']) {
            return $firstRes;
        }

        $allRecords = [];
        $firstJson = $firstRes['data'] ?? [];
        $records1 = [];
        $lastPage = 1;

        if (isset($firstJson['data']) && is_array($firstJson['data'])) {
            $records1 = $firstJson['data'];
            $lastPage = $firstJson['meta']['last_page'] ?? ($firstJson['last_page'] ?? 1);
        } elseif (is_array($firstJson)) {
            $records1 = $firstJson;
        }

        foreach ($records1 as $r) {
            $allRecords[] = $r;
        }

        $lastPage = min((int)$lastPage, $maxPages);
        if ($lastPage <= 1) {
            return ['status' => true, 'total_data' => count($allRecords), 'records' => $allRecords];
        }

        $pageUrls = [];
        for ($p = 2; $p <= $lastPage; $p++) {
            $pageUrls[$p] = $endpoint . $separator . 'page=' . $p;
        }

        $multiRes = $this->fetchMultiParallel($pageUrls, $concurrency);
        foreach ($multiRes as $p => $resItem) {
            if (!empty($resItem['status']) && !empty($resItem['data']['data']) && is_array($resItem['data']['data'])) {
                foreach ($resItem['data']['data'] as $rec) {
                    $allRecords[] = $rec;
                }
            }
        }

        return [
            'status'     => true,
            'total_data' => count($allRecords),
            'records'    => $allRecords
        ];
    }

    /**
     * Mengambil data halaman dengan paginasi berkinerja tinggi (per_page=100)
     */
    public function fetchAllPages($endpoint, $maxPages = 20, $perPage = 100) {
        return $this->fetchAllPagesParallel($endpoint, $maxPages);
    }

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
// 4. ROUTER AKSI (HEALTH, ME, SUMMARY, TEST_LOGIN, SISWA, PULL)
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
        $email = trim($_POST['email'] ?? ($config['api_email'] ?? ''));
        $pass  = trim($_POST['password'] ?? ($config['api_password'] ?? ''));
        if (empty($email) || empty($pass)) {
            sendJsonResponse(false, 'Kredensial email atau password belum diatur pada config/tatausaha_api.json.', null, 400);
        }
        $loginRes = $apiClient->login($email, $pass);
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

    // --- D. AMBIL RINGKASAN KEUANGAN RESMI (/api/dashboard/summary) ---
    case 'summary':
        $sumRes = $apiClient->request('GET', '/api/dashboard/summary');
        sendJsonResponse($sumRes['status'], $sumRes['message'], $sumRes['data'] ?? null, $sumRes['http_code'] ?? 200);
        break;

    // --- E. AMBIL DAFTAR SISWA RESMI (/api/siswa) ---
    case 'siswa':
        $siswaRes = $apiClient->fetchAllPages('/api/siswa?per_page=100', 10);
        sendJsonResponse($siswaRes['status'], 'Data siswa berhasil ditarik', $siswaRes['records'] ?? [], 200);
        break;

    // --- F. TARIK REALTIME DATA PER SISWA TERTENTU (/api/siswa/{id}/tagihan & /api/siswa/{id}/riwayat-bayar) ---
    case 'student':
        $nis = trim($_GET['nis'] ?? ($_POST['nis'] ?? ''));
        if (empty($nis)) {
            sendJsonResponse(false, 'Parameter NIS siswa wajib diisi.', null, 400);
        }
        $token = $apiClient->getValidJwtToken();
        $searchRes = $apiClient->request('GET', '/api/siswa?q=' . urlencode($nis), null, $token);
        $stuData = null;
        if ($searchRes['status'] && !empty($searchRes['data'])) {
            $records = is_array($searchRes['data']) && isset($searchRes['data']['data']) ? $searchRes['data']['data'] : $searchRes['data'];
            if (is_array($records)) {
                foreach ($records as $item) {
                    if (trim((string)($item['nis'] ?? '')) === $nis) {
                        $stuData = $item;
                        break;
                    }
                }
                if (!$stuData && !empty($records[0])) {
                    $stuData = $records[0];
                }
            }
        }
        if (!$stuData || empty($stuData['id'])) {
            sendJsonResponse(false, "Siswa dengan NIS {$nis} tidak ditemukan di server Tata Usaha.", null, 404);
        }
        $tuId = (int)$stuData['id'];
        $billsRes = $apiClient->request('GET', "/api/siswa/{$tuId}/tagihan", null, $token);
        $historyRes = $apiClient->request('GET', "/api/siswa/{$tuId}/riwayat-bayar", null, $token);
        sendJsonResponse(true, "Data tagihan dan riwayat siswa NIS {$nis} realtime dari API Tata Usaha", [
            'siswa'   => $stuData,
            'tagihan' => $billsRes['data'] ?? [],
            'riwayat' => $historyRes['data'] ?? []
        ]);
        break;

    // --- G. WEBHOOK LISTENER INSTAN DARI SISTEM TATA USAHA ---
    case 'webhook':
        $rawPayload = file_get_contents('php://input');
        $payload = json_decode($rawPayload, true) ?: $_POST;
        // Simpan log event webhook untuk audit trail
        $logDir = __DIR__ . '/config';
        if (is_dir($logDir)) {
            @file_put_contents($logDir . '/webhook_log.json', json_encode([
                'received_at' => date('Y-m-d H:i:s'),
                'payload'     => $payload
            ], JSON_PRETTY_PRINT));
        }
        sendJsonResponse(true, 'Webhook event pembayaran berhasil diterima dan dicatat.', [
            'status'     => 'processed',
            'event_time' => date('Y-m-d H:i:s')
        ]);
        break;

    // --- H. AKSI UTAMA: TARIK DAN NORMALISASI DATA TAGIHAN UNTUK LMS (DEFAULT / PULL) ---
    case 'pull':
    default:
        pullAndNormalizeData($apiClient, $config);
        break;
}

// ==============================================================================
// 5. FUNGSI PENGAMBILAN & NORMALISASI DATA KEUANGAN
// ==============================================================================
function pullAndNormalizeData(TataUsahaApiClient $apiClient, array $config) {
    $jwtToken = $apiClient->getValidJwtToken();
    if (empty($jwtToken)) {
        sendJsonResponse(false, 'Kredensial API Tata Usaha belum lengkap: silakan masukkan email & password akun integrasi atau JWT token pada config/tatausaha_api.json.', null, 400);
    }

    // 1. Ambil seluruh data siswa (/api/siswa) untuk mapping kelas dan identitas akurat
    $siswaMap = [];
    $siswaRes = $apiClient->fetchAllPagesParallel('/api/siswa?per_page=100', 10, 10);
    if ($siswaRes['status'] && !empty($siswaRes['records'])) {
        foreach ($siswaRes['records'] as $st) {
            $sNis = trim((string)($st['nis'] ?? ''));
            if (!empty($sNis)) {
                $siswaMap[$sNis] = [
                    'nama'       => $st['nama'] ?? '',
                    'nama_kelas' => $st['nama_kelas'] ?? '',
                    'angkatan'   => $st['angkatan_nama'] ?? '',
                    'status'     => $st['status'] ?? 'aktif'
                ];
            }
        }
    }

    // 2. Ambil seluruh data riwayat transaksi pembayaran (/api/pembayaran)
    $rawPayments = [];
    $paymentsByBill = [];
    $trxRes = $apiClient->fetchAllPagesParallel('/api/pembayaran?per_page=100', 80, 15);
    if ($trxRes['status'] && !empty($trxRes['records'])) {
        $rawPayments = $trxRes['records'];
        foreach ($rawPayments as $p) {
            $billIdKey = (int)($p['id_tagihan_siswa'] ?? 0);
            $nisTrx    = trim((string)($p['nis'] ?? ''));
            $kdTagihan = trim((string)($p['kode_tagihan'] ?? ''));

            $formattedTrx = [
                'nomor_transaksi'   => $p['kode_pembayaran'] ?? ($p['nomor_transaksi'] ?? ('PAY-' . ($p['id'] ?? uniqid()))),
                'nominal_bayar'     => (float)($p['jumlah_bayar'] ?? ($p['nominal_bayar'] ?? 0)),
                'tanggal_bayar'     => !empty($p['tanggal']) ? ($p['tanggal'] . ' 08:00:00') : ($p['created_at'] ?? date('Y-m-d H:i:s')),
                'metode_pembayaran' => $p['metode'] ?? 'tunai',
                'channel'           => 'Loket Keuangan Tata Usaha',
                'status'            => normalizeTransactionStatus($p['status'] ?? 'berhasil'),
                'catatan'           => $p['keterangan'] ?? 'Pembayaran Loket TU'
            ];

            // Pasangkan transaksi ke ID tagihan siswa yang spesifik
            if ($billIdKey > 0) {
                $paymentsByBill['id_' . $billIdKey][] = $formattedTrx;
            }
            if (!empty($nisTrx) && !empty($kdTagihan)) {
                $paymentsByBill['nis_' . $nisTrx . '_' . $kdTagihan][] = $formattedTrx;
            }
        }
    }

    // 3. Ambil data tagihan seluruh siswa tanpa terpotong (/api/tagihan-siswa - mencakup Kelas X, XI, XII & Alumni)
    $rawBills = [];
    $billsRes = $apiClient->fetchAllPagesParallel('/api/tagihan-siswa?per_page=100', 180, 15);
    if ($billsRes['status'] && !empty($billsRes['records'])) {
        $rawBills = $billsRes['records'];
    }

    if (empty($rawBills)) {
        sendJsonResponse(true, 'Koneksi ke API Tata Usaha berhasil, namun belum ada data tagihan yang diterbitkan di server Tata Usaha.', [
            'total_data' => 0,
            'data'       => []
        ]);
    }

    // 4. Normalisasi data tagihan siswa sesuai format yang diharapkan E-Learning
    $normalizedItems = [];
    foreach ($rawBills as $b) {
        $nis = trim((string)($b['nis'] ?? ''));
        if (empty($nis)) continue;

        $billId = (int)($b['id'] ?? 0);
        $masterKode = trim((string)($b['kode_tagihan'] ?? 'TAG'));
        
        // Kode unik stabil per tagihan siswa untuk mencegah bentrok UNIQUE KEY
        $kodeTagihan = 'TU-TAG-' . $billId . '-' . $masterKode;

        $studentInfo = $siswaMap[$nis] ?? [];
        $namaSiswa   = !empty($studentInfo['nama']) ? $studentInfo['nama'] : trim((string)($b['siswa_nama'] ?? ''));
        $namaKelas   = !empty($studentInfo['nama_kelas']) ? $studentInfo['nama_kelas'] : '';

        $judul       = trim((string)($b['nama_tagihan'] ?? ($b['judul'] ?? 'Iuran Sekolah')));

        // Klasifikasi Jenis Pembayaran
        $jenis = 'SPP';
        if (stripos($judul, 'ujian') !== false || stripos($masterKode, 'ujian') !== false) {
            $jenis = 'Ujian';
        } elseif (stripos($judul, 'dsp') !== false || stripos($judul, 'gedung') !== false || stripos($masterKode, 'dsp') !== false) {
            $jenis = 'DSP';
        } elseif (stripos($judul, 'spp') === false) {
            $jenis = 'Iuran Sekolah';
        }

        $nominal  = (float)($b['total_tagihan'] ?? ($b['nominal'] ?? 0));
        $terbayar = (float)($b['total_terbayar'] ?? ($b['nominal_terbayar'] ?? 0));
        $sisa     = (float)($b['sisa_tagihan'] ?? ($b['sisa'] ?? max(0, $nominal - $terbayar)));

        // Pasangkan riwayat transaksi yang valid milik tagihan siswa ini
        $riwayatList = [];
        if ($billId > 0 && isset($paymentsByBill['id_' . $billId])) {
            $riwayatList = $paymentsByBill['id_' . $billId];
        } elseif (isset($paymentsByBill['nis_' . $nis . '_' . $masterKode])) {
            $riwayatList = $paymentsByBill['nis_' . $nis . '_' . $masterKode];
        }

        // Tentukan status pelunasan
        $rawStatus = strtolower(trim((string)($b['status'] ?? '')));
        if ($rawStatus === 'lunas' || $sisa <= 0) {
            $status = 'lunas';
        } elseif ($rawStatus === 'sebagian' || $terbayar > 0) {
            $status = 'sebagian';
        } else {
            $status = 'belum_lunas';
        }

        $normalizedItems[] = [
            'nisn'                => $nis,
            'nis'                 => $nis,
            'nama_siswa'          => $namaSiswa,
            'nama_kelas'          => $namaKelas,
            'kode_tagihan'        => $kodeTagihan,
            'judul'               => $judul,
            'jenis_pembayaran'    => $jenis,
            'nominal'             => $nominal,
            'nominal_terbayar'    => $terbayar,
            'sisa_tagihan'        => $sisa,
            'status'              => $status,
            'periode_bulan'       => $judul,
            'tahun_ajaran'        => '2024/2025',
            'tanggal_jatuh_tempo' => null,
            'keterangan'          => 'Tagihan Resmi API Tata Usaha SMK Muthia Harapan',
            'riwayat'             => $riwayatList
        ];
    }

    sendJsonResponse(true, 'Data tagihan & pembayaran resmi berhasil diambil dari API Tata Usaha.', [
        'total_data' => count($normalizedItems),
        'data'       => $normalizedItems,
        'meta'       => [
            'source'          => $config['api_base_url'],
            'synced_at'       => date('Y-m-d H:i:s'),
            'total_tagihan'   => count($normalizedItems),
            'total_transaksi' => count($rawPayments)
        ]
    ]);
}

function normalizeTransactionStatus($rawStatus) {
    $s = strtolower(trim((string)$rawStatus));
    if (in_array($s, ['berhasil', 'lunas', 'paid', 'success', 'approved', 'tunai'])) {
        return 'berhasil';
    }
    if (in_array($s, ['pending', 'menunggu', 'process'])) {
        return 'pending';
    }
    return 'batal';
}

function sendJsonResponse($status, $message, $data = null, $httpCode = 200) {
    if (ob_get_length()) {
        ob_clean();
    }

    if (!headers_sent()) {
        http_response_code($httpCode);
    }

    $payload = [
        'status'  => (bool)$status,
        'message' => (string)$message
    ];

    if ($data !== null) {
        if (is_array($data) && isset($data['data'])) {
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
