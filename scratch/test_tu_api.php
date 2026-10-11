<?php
$baseUrl = 'https://apitatausaha.smkmuthiaharapanclk.com';

function callApi($method, $endpoint, $data = null, $token = null) {
    global $baseUrl;
    $ch = curl_init($baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    $headers = ['Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            $payload = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $headers[] = 'Content-Type: application/json';
        }
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    return [
        'http' => $httpCode,
        'error' => $err,
        'body' => $res,
        'json' => json_decode($res, true)
    ];
}

echo "=== 1. Health ===\n";
print_r(callApi('GET', '/api/health'));

echo "=== 2. Login with dummy credentials ===\n";
print_r(callApi('POST', '/api/auth/login', ['email' => 'tatausaha@smkmuthiaharapanclk.com', 'password' => 'password123']));

echo "=== 3. Get /api/siswa without token ===\n";
print_r(callApi('GET', '/api/siswa'));

echo "=== 4. Get /api/tagihan without token ===\n";
print_r(callApi('GET', '/api/tagihan'));

echo "=== 5. Get /api/pembayaran without token ===\n";
print_r(callApi('GET', '/api/pembayaran'));
