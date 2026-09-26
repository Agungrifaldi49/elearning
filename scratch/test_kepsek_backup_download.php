<?php
define('ROOT_PATH', __DIR__ . '/../');
require_once ROOT_PATH . 'config/app.php';
require_once ROOT_PATH . 'models/ReportModel.php';
require_once ROOT_PATH . 'helpers/AuthHelper.php';
require_once ROOT_PATH . 'helpers/FlashHelper.php';

$reportModel = new ReportModel();
$backups = $reportModel->getBackups();
echo "1. Total Backups in DB: " . count($backups) . "\n";
if (!empty($backups)) {
    $first = $backups[0];
    $filePath = ROOT_PATH . 'database/' . $first['file_name'];
    echo "2. First backup file: {$first['file_name']}\n";
    echo "   Exists on disk: " . (file_exists($filePath) ? "YES (" . filesize($filePath) . " bytes)" : "NO") . "\n";
}

// Test Live Backup creation
$testLive = $reportModel->createDatabaseBackup('manual', 'Test Backup by Kepsek');
echo "3. Live Backup created: {$testLive}\n";
$livePath = ROOT_PATH . 'database/' . $testLive;
echo "   Exists on disk: " . (file_exists($livePath) ? "YES (" . filesize($livePath) . " bytes)" : "NO") . "\n";

// Clean up test file
if (file_exists($livePath)) {
    @unlink($livePath);
    $db = Database::getConnection();
    $db->prepare("DELETE FROM backup WHERE file_name = ?")->execute([$testLive]);
    echo "✓ Test live backup cleaned up successfully.\n";
}
