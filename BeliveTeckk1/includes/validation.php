<?php
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateRequired($value, $fieldName) {
    if (empty(trim($value))) {
        throw new Exception("$fieldName is required");
    }
    return $value;
}

function validateLength($value, $fieldName, $min, $max = null) {
    $length = strlen(trim($value));
    if ($length < $min) {
        throw new Exception("$fieldName must be at least $min characters long");
    }
    if ($max !== null && $length > $max) {
        throw new Exception("$fieldName must not exceed $max characters");
    }
    return $value;
}

function validateURL($url, $fieldName) {
    if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception("$fieldName must be a valid URL");
    }
    return $url;
}

function validateDate($date, $fieldName) {
    if (!empty($date)) {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            throw new Exception("$fieldName must be a valid date");
        }
    }
    return $date;
}

function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new Exception('Invalid file parameter');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new Exception('File size exceeds limit');
        case UPLOAD_ERR_PARTIAL:
            throw new Exception('File was only partially uploaded');
        case UPLOAD_ERR_NO_FILE:
            throw new Exception('No file was uploaded');
        case UPLOAD_ERR_NO_TMP_DIR:
            throw new Exception('Missing temporary folder');
        case UPLOAD_ERR_CANT_WRITE:
            throw new Exception('Failed to write file to disk');
        case UPLOAD_ERR_EXTENSION:
            throw new Exception('A PHP extension stopped the file upload');
        default:
            throw new Exception('Unknown upload error');
    }

    if ($file['size'] > $maxSize) {
        throw new Exception('File size exceeds limit');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif'
    ];

    if (!in_array($mimeType, $allowedMimeTypes)) {
        throw new Exception('Invalid file type');
    }

    return true;
} 