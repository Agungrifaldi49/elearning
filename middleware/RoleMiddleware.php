<?php
/**
 * Role Middleware (RBAC)
 */
require_once ROOT_PATH . 'helpers/AuthHelper.php';

class RoleMiddleware {
    public static function allow($roles = []) {
        AuthHelper::requireRole($roles);
    }
}
