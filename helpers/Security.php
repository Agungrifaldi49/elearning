<?php
/**
 * Security Helper
 * Handles CSRF, XSS, Input Sanitization, Rate Limiting
 */

class Security {

    /**
     * Generate CSRF Token
     */
    public static function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfToken() {
        return self::generateCsrfToken();
    }

    /**
     * Output CSRF Form Field
     */
    public static function csrfField() {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verify CSRF Token
     */
    public static function verifyCsrfToken($token = null) {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Sanitize String for XSS Protection
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize($value);
            }
            return $data;
        }
        return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Clean Raw HTML for safe display (permits safe formatting tags like lists, bold, italic, underline)
     */
    public static function sanitizeHtml($html) {
        if (empty($html)) return '';
        // Remove script tags and inline event attributes
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $html);
        $html = preg_replace('/on[a-z]+\s*=\s*(["\']).*?\1/i', '', $html);
        $html = preg_replace('/on[a-z]+\s*=\s*[^ >]+/i', '', $html);
        $html = preg_replace('/javascript:/i', '', $html);

        // Allowed tags
        $allowed = '<ol><ul><li><p><br><b><strong><i><em><u><span><div><style>';
        return strip_tags(trim((string)$html), $allowed);
    }

    /**
     * Clean Raw HTML (for CKEditor content while preventing script injection)
     */
    public static function cleanHtml($html) {
        return self::sanitizeHtml($html);
    }

    /**
     * Get Client IP Address
     */
    public static function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
