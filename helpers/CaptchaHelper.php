<?php
/**
 * Captcha Helper
 * Generates simple & secure mathematical verification captchas
 */

class CaptchaHelper {

    /**
     * Generate a new math captcha
     */
    public static function generate() {
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        $operators = ['+', '*'];
        $op = $operators[rand(0, 1)];

        if ($op === '+') {
            $answer = $num1 + $num2;
            $question = "$num1 + $num2";
        } else {
            $answer = $num1 * $num2;
            $question = "$num1 x $num2";
        }

        $_SESSION['captcha_answer'] = (string)$answer;

        return $question;
    }

    /**
     * Verify captcha answer
     */
    public static function verify($input) {
        if (!isset($_SESSION['captcha_answer'])) {
            return false;
        }
        $valid = trim((string)$input) === (string)$_SESSION['captcha_answer'];
        unset($_SESSION['captcha_answer']); // One-time use
        return $valid;
    }
}
