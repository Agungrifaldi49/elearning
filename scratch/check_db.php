<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    echo "--- USERS TABLE COLUMNS ---\n";
    $cols = $db->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo $c['Field'] . " (" . $c['Type'] . ")\n";
    }

    echo "\n--- SAMPLE USERS WITH AVATAR/FOTO ---\n";
    $users = $db->query("SELECT id, username, full_name, avatar FROM users LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
