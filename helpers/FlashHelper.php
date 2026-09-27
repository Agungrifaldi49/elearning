<?php
/**
 * Flash Message Helper
 * Renders SweetAlert2 notifications & Bootstrap Alerts
 */

class FlashHelper {

    public static function set($key, $message) {
        if ($key === 'success') {
            self::setSuccess($message);
        } elseif ($key === 'error') {
            self::setError($message);
        } elseif ($key === 'warning') {
            self::setWarning($message);
        } elseif ($key === 'info') {
            self::setInfo($message);
        } else {
            $_SESSION['flash_' . $key] = $message;
        }
    }

    public static function has($key = null) {
        if ($key === 'success') {
            return self::hasSuccess();
        } elseif ($key === 'error') {
            return self::hasError();
        } elseif ($key === 'warning') {
            return self::hasWarning();
        } elseif ($key === 'info') {
            return self::hasInfo();
        } elseif ($key !== null) {
            return !empty($_SESSION['flash_' . $key]);
        }
        return self::hasSuccess() || self::hasError() || self::hasWarning() || self::hasInfo();
    }

    public static function get($key = null) {
        if ($key === 'success') {
            return self::getSuccess();
        } elseif ($key === 'error') {
            return self::getError();
        } elseif ($key === 'warning') {
            return self::getWarning();
        } elseif ($key === 'info') {
            return self::getInfo();
        } elseif ($key !== null) {
            if (!empty($_SESSION['flash_' . $key])) {
                $msg = $_SESSION['flash_' . $key];
                unset($_SESSION['flash_' . $key]);
                return $msg;
            }
            return null;
        }
        return self::getSuccess() ?: (self::getError() ?: (self::getWarning() ?: self::getInfo()));
    }

    public static function setSuccess($message) {
        $_SESSION['flash_success'] = $message;
    }

    public static function setError($message) {
        $_SESSION['flash_error'] = $message;
    }

    public static function setWarning($message) {
        $_SESSION['flash_warning'] = $message;
    }

    public static function setInfo($message) {
        $_SESSION['flash_info'] = $message;
    }

    public static function hasSuccess() {
        return !empty($_SESSION['flash_success']);
    }

    public static function hasError() {
        return !empty($_SESSION['flash_error']);
    }

    public static function hasWarning() {
        return !empty($_SESSION['flash_warning']);
    }

    public static function hasInfo() {
        return !empty($_SESSION['flash_info']);
    }

    public static function getSuccess() {
        if (!empty($_SESSION['flash_success'])) {
            $msg = $_SESSION['flash_success'];
            unset($_SESSION['flash_success']);
            return $msg;
        }
        return null;
    }

    public static function getError() {
        if (!empty($_SESSION['flash_error'])) {
            $msg = $_SESSION['flash_error'];
            unset($_SESSION['flash_error']);
            return $msg;
        }
        return null;
    }

    public static function getWarning() {
        if (!empty($_SESSION['flash_warning'])) {
            $msg = $_SESSION['flash_warning'];
            unset($_SESSION['flash_warning']);
            return $msg;
        }
        return null;
    }

    public static function getInfo() {
        if (!empty($_SESSION['flash_info'])) {
            $msg = $_SESSION['flash_info'];
            unset($_SESSION['flash_info']);
            return $msg;
        }
        return null;
    }

    public static function render() {
        $html = '';

        if (!empty($_SESSION['flash_success'])) {
            $raw = $_SESSION['flash_success'];
            $clean = trim(strip_tags(html_entity_decode((string)$raw, ENT_QUOTES, 'UTF-8')));
            $jsonText = json_encode($clean, JSON_UNESCAPED_UNICODE);
            $html .= "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: {$jsonText},
                        timer: 3500,
                        showConfirmButton: false,
                        customClass: { popup: 'rounded-4 shadow' }
                    });
                });
            </script>";
            unset($_SESSION['flash_success']);
        }

        if (!empty($_SESSION['flash_error'])) {
            $raw = $_SESSION['flash_error'];
            $clean = trim(strip_tags(html_entity_decode((string)$raw, ENT_QUOTES, 'UTF-8')));
            $jsonText = json_encode($clean, JSON_UNESCAPED_UNICODE);
            $html .= "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: {$jsonText},
                        customClass: { popup: 'rounded-4 shadow' }
                    });
                });
            </script>";
            unset($_SESSION['flash_error']);
        }

        if (!empty($_SESSION['flash_warning'])) {
            $raw = $_SESSION['flash_warning'];
            $clean = trim(strip_tags(html_entity_decode((string)$raw, ENT_QUOTES, 'UTF-8')));
            $jsonText = json_encode($clean, JSON_UNESCAPED_UNICODE);
            $html .= "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: {$jsonText},
                        timer: 4500,
                        showConfirmButton: true,
                        customClass: { popup: 'rounded-4 shadow' }
                    });
                });
            </script>";
            unset($_SESSION['flash_warning']);
        }

        if (!empty($_SESSION['flash_info'])) {
            $raw = $_SESSION['flash_info'];
            $clean = trim(strip_tags(html_entity_decode((string)$raw, ENT_QUOTES, 'UTF-8')));
            $jsonText = json_encode($clean, JSON_UNESCAPED_UNICODE);
            $html .= "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'info',
                        title: 'Informasi',
                        text: {$jsonText},
                        timer: 4000,
                        showConfirmButton: true,
                        customClass: { popup: 'rounded-4 shadow' }
                    });
                });
            </script>";
            unset($_SESSION['flash_info']);
        }

        return $html;
    }

    public static function display() {
        echo self::render();
    }
}
