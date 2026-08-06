<?php
/**
 * CSRF Middleware
 */
require_once ROOT_PATH . 'helpers/Security.php';
require_once ROOT_PATH . 'helpers/FlashHelper.php';

class CSRFMiddleware {
    public static function handle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrfToken()) {
                FlashHelper::setError('Token keamanan (CSRF) tidak valid. Silakan coba lagi.');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
                exit();
            }
        }
    }
}
