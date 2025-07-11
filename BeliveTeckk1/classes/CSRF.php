<?php
class CSRF {
    public static function generateToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    public static function validateToken($token) {
        if (!isset($_SESSION[CSRF_TOKEN_NAME]) || !isset($token)) {
            return false;
        }
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    public static function getTokenField() {
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . self::generateToken() . '">';
    }
    
    public static function validateRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST[CSRF_TOKEN_NAME]) || !self::validateToken($_POST[CSRF_TOKEN_NAME])) {
                http_response_code(403);
                die('Invalid CSRF token');
            }
        }
    }
    
    public static function refreshToken() {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        return $_SESSION[CSRF_TOKEN_NAME];
    }
} 