<?php
function logError($message, $context = []) {
    $logFile = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[$timestamp] ERROR: $message $contextStr\n";
    
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    
    error_log($logMessage, 3, $logFile);
}

function logActivity($message, $context = []) {
    $logFile = __DIR__ . '/../logs/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[$timestamp] ACTIVITY: $message $contextStr\n";
    
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    
    error_log($logMessage, 3, $logFile);
}

function logAdminAction($action, $details = []) {
    $userId = $_SESSION['admin_id'] ?? 'unknown';
    $username = $_SESSION['admin_username'] ?? 'unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $context = [
        'user_id' => $userId,
        'username' => $username,
        'ip' => $ip,
        'details' => $details
    ];
    
    logActivity("Admin Action: $action", $context);
}

function logFormSubmission($formName, $data = []) {
    $userId = $_SESSION['admin_id'] ?? 'unknown';
    $username = $_SESSION['admin_username'] ?? 'unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $context = [
        'user_id' => $userId,
        'username' => $username,
        'ip' => $ip,
        'form_data' => $data
    ];
    
    logActivity("Form Submission: $formName", $context);
} 