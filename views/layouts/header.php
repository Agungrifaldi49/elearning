<?php
if (!defined('APP_NAME') && defined('ROOT_PATH')) {
    require_once ROOT_PATH . 'config/app.php';
}

// Load dynamic settings for Favicon & Page Title
$appSettings = [];
$settingsPath = ROOT_PATH . 'config/settings.json';
if (file_exists($settingsPath)) {
    $appSettings = json_decode(file_get_contents($settingsPath), true) ?: [];
} else {
    try {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $appSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    } catch (Exception $e) {}
}

$schoolName = !empty($appSettings['nama_sekolah']) ? $appSettings['nama_sekolah'] : APP_NAME;

$rawLogo = $appSettings['logo'] ?? '';
$schoolLogo = null;
if (!empty($rawLogo)) {
    if (strpos($rawLogo, 'assets/uploads/') === 0 || strpos($rawLogo, 'uploads/') === 0) {
        $schoolLogo = BASE_URL . $rawLogo;
    } else {
        $schoolLogo = BASE_URL . 'assets/uploads/logo/' . $rawLogo;
    }
}

$faviconVersion = !empty($rawLogo) ? @filemtime(ROOT_PATH . (strpos($rawLogo, 'assets/') === 0 ? $rawLogo : 'assets/uploads/logo/' . $rawLogo)) ?: time() : time();
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? $schoolName) ?></title>
    <meta name="description" content="<?= htmlspecialchars($schoolName) ?> - Portal Learning Management System Modern & Terintegrasi.">

    <!-- Favicon Dynamic (Logo Sekolah / Custom Upload / Graduation Cap SVG) -->
    <?php if ($schoolLogo): ?>
        <link rel="icon" type="image/png" href="<?= htmlspecialchars($schoolLogo) ?>?v=<?= $faviconVersion ?>">
        <link rel="shortcut icon" type="image/png" href="<?= htmlspecialchars($schoolLogo) ?>?v=<?= $faviconVersion ?>">
        <link rel="apple-touch-icon" href="<?= htmlspecialchars($schoolLogo) ?>?v=<?= $faviconVersion ?>">
    <?php else: ?>
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%230D6EFD'/><text y='.75em' x='10' font-size='70'>🎓</text></svg>">
        <link rel="shortcut icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%230D6EFD'/><text y='.75em' x='10' font-size='70'>🎓</text></svg>">
    <?php endif; ?>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Google Fonts Inter & Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom CSS with cache buster -->
    <link href="<?= BASE_URL ?>assets/css/style.css?v=<?= time() ?>" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>
</head>
<body data-user-id="<?= $_SESSION['user_id'] ?? '' ?>">
