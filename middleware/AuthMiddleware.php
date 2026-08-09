<?php
/**
 * Auth Middleware
 */
require_once ROOT_PATH . 'helpers/AuthHelper.php';

class AuthMiddleware {
    public static function handle() {
        AuthHelper::requireLogin();
    }
}
