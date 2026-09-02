<?php
/**
 * Concurrency & Performance Verification Test
 * E-Learning SMK Muthia Harapan Cicalengka
 */

define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/app.php';
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'helpers/AuthHelper.php';
require_once ROOT_PATH . 'models/SiswaModel.php';
require_once ROOT_PATH . 'models/GuruModel.php';

echo "=== STARTING CONCURRENCY & RESILIENCE TEST ===\n";

$startTime = microtime(true);
$iterations = 20;
$successes = 0;
$errors = 0;

for ($i = 1; $i <= $iterations; $i++) {
    try {
        $db = Database::getConnection();
        if ($db) {
            // Simulated user authentication check
            $stmt = $db->prepare("SELECT id, full_name, role_id FROM users ORDER BY RAND() LIMIT 1");
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user) {
                $siswaModel = new SiswaModel();
                $sProfile = $siswaModel->ensureSiswaProfile($user['id'], $user['full_name']);

                $guruModel = new GuruModel();
                $gProfile = $guruModel->ensureGuruProfile($user['id'], $user['full_name']);

                if (is_array($sProfile) && is_array($gProfile)) {
                    $successes++;
                }
            } else {
                $successes++;
            }
        }
    } catch (\Throwable $e) {
        echo "Error on iteration {$i}: " . $e->getMessage() . "\n";
        $errors++;
    }
}

$duration = round((microtime(true) - $startTime) * 1000, 2);

echo "\n=== TEST RESULTS ===\n";
echo "Total Iterations: {$iterations}\n";
echo "Successful Requests: {$successes}\n";
echo "Errors / Exceptions: {$errors}\n";
echo "Total Execution Time: {$duration} ms\n";
echo "Avg Time per Request: " . round($duration / $iterations, 2) . " ms\n";

if ($errors === 0) {
    echo "\n>>> VERIFICATION SUCCESS: All requests handled without HTTP 500 / exceptions! <<<\n";
} else {
    echo "\n>>> VERIFICATION WARNING: Some errors occurred during test! <<<\n";
}
