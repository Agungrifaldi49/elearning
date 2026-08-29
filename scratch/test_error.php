<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/CommunicationModel.php';
require_once __DIR__ . '/../models/AcademicModel.php';
require_once __DIR__ . '/../controllers/ForumController.php';

try {
    echo "Testing CommunicationModel...\n";
    $commModel = new CommunicationModel();
    $topics = $commModel->getForumTopics(1, 'Administrator', null);
    echo "Topics count: " . count($topics) . "\n";
    print_r(array_slice($topics, 0, 1));
} catch (Throwable $e) {
    echo "FATAL ERROR CAUGHT:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
