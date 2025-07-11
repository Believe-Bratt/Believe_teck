<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'believe_teckk_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'Believe Teckk');
define('SITE_URL', 'http://localhost/BeliveTeckk');
define('ADMIN_EMAIL', 'admin@beliveteckk.com');

// File upload configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Session configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'beliveteckk_session');

// Security configuration
define('HASH_COST', 10);
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LENGTH', 32);

// Pagination configuration
define('ITEMS_PER_PAGE', 10);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Time zone
date_default_timezone_set('UTC');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Function to get database connection
function getDBConnection() {
    try {
        $db = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
        );
        error_log("Database connection successful in config.php");
        return $db;
    } catch (PDOException $e) {
        error_log("Database connection failed in config.php: " . $e->getMessage());
        die("Connection failed: " . $e->getMessage());
    }
} 