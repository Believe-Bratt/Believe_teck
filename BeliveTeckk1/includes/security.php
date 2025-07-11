<?php
/**
 * Security Configuration and Functions
 * Include this file at the beginning of all admin pages
 */

// Prevent direct access to this file
if (!defined('SECURE_ACCESS')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access forbidden');
}

// Security headers
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' https://cdnjs.cloudflare.com; img-src \'self\' data: https:; font-src \'self\' https://cdnjs.cloudflare.com');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// CSRF Protection
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        error_log('CSRF token validation failed');
        return false;
    }
    return true;
}

// XSS Protection
function sanitizeOutput($value) {
    if (is_array($value)) {
        return array_map('sanitizeOutput', $value);
    }
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// SQL Injection Protection
function sanitizeInput($value) {
    if (is_array($value)) {
        return array_map('sanitizeInput', $value);
    }
    return strip_tags(trim($value));
}

// File Upload Security
function validateFileUpload($file, $allowedTypes, $maxSize) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload failed'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }

    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $fileInfo->file($file['tmp_name']);
    
    $allowedMimeTypes = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/webp' => ['webp']
    ];

    $isAllowed = false;
    foreach ($allowedMimeTypes as $mime => $extensions) {
        if ($mimeType === $mime && array_intersect($extensions, $allowedTypes)) {
            $isAllowed = true;
            break;
        }
    }

    if (!$isAllowed) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    return ['success' => true];
}

// Password Security
function validatePassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password);
}

// Rate Limiting
function checkRateLimit($key, $limit = 5, $timeWindow = 300) {
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = [
            'count' => 0,
            'first_attempt' => time()
        ];
    }

    $rateLimit = &$_SESSION['rate_limits'][$key];
    
    if (time() - $rateLimit['first_attempt'] > $timeWindow) {
        $rateLimit = [
            'count' => 1,
            'first_attempt' => time()
        ];
        return true;
    }

    if ($rateLimit['count'] >= $limit) {
        return false;
    }

    $rateLimit['count']++;
    return true;
}

// Activity Logging
function logActivity($user_id, $action, $details = '') {
    global $db;
    try {
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $details, $_SERVER['REMOTE_ADDR']]);
    } catch (Exception $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}

// Check if user has required permission
function checkPermission($permission) {
    if (!isset($_SESSION['admin_permissions']) || 
        !is_array($_SESSION['admin_permissions']) || 
        !in_array($permission, $_SESSION['admin_permissions'])) {
        return false;
    }
    return true;
}

// Validate admin session
function validateAdminSession() {
    if (!isset($_SESSION['admin_logged_in']) || 
        !$_SESSION['admin_logged_in'] || 
        !isset($_SESSION['admin_last_activity'])) {
        return false;
    }

    $timeout = 1800; // 30 minutes
    if (time() - $_SESSION['admin_last_activity'] > $timeout) {
        session_destroy();
        return false;
    }

    $_SESSION['admin_last_activity'] = time();
    return true;
} 