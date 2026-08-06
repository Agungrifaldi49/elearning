<?php
/**
 * Flash Message Helper
 * Renders SweetAlert2 notifications & Bootstrap Alerts
 */

class FlashHelper {

    public static function setSuccess($message) {
        $_SESSION['flash_success'] = $message;
    }

    public static function setError($message) {
        $_SESSION['flash_error'] = $message;
    }

    public static function setInfo($message) {
        $_SESSION['flash_info'] = $message;
    }

    public static function render() {
        $html = '';

        if (!empty($_SESSION['flash_success'])) {
            $msg = htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8');
            $html .= "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '{$msg}',
                        timer: 3000,
                        showConfirmButton: false,
                        customClass: { popup: 'rounded-4 shadow' }
                    });
                });
            </script>";
            unset($_SESSION['flash_success']);
        }

        if (!empty($_SESSION['flash_error'])) {
            $msg = htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8');
            $html .= "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: '{$msg}',
                        customClass: { popup: 'rounded-4 shadow' }
                    });
                });
            </script>";
            unset($_SESSION['flash_error']);
        }

        if (!empty($_SESSION['flash_info'])) {
            $msg = htmlspecialchars($_SESSION['flash_info'], ENT_QUOTES, 'UTF-8');
            $html .= "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'info',
                        title: 'Informasi',
                        text: '{$msg}',
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
}
