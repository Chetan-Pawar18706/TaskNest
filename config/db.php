<?php
/**
 * TaskNest - Database Configuration
 * Phase 1: Core Configuration
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tasknest');
define('DB_PORT', 3306);

// Project Root — works on localhost, hosting, subdirectory, anywhere
define('PROJECT_ROOT', dirname(__DIR__));

// Site Configuration
// Hosting pe:  define('SITE_URL', 'https://tasknest.is-best.net');
// Localhost pe: define('SITE_URL', 'http://localhost/TaskNest');

if (!defined('SITE_URL')) {
    $is_secure = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
    );
    $protocol = $is_secure ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    define('SITE_URL', $protocol . '://' . $host . $path);
}
define('SITE_NAME', 'TaskNest');
define('SITE_DESCRIPTION', 'All-in-one Personal Life Management System');

// Security Configuration
define('JWT_SECRET', getenv('TASKNEST_JWT_SECRET') ?: 'CHANGE-THIS-IN-PRODUCTION-min-32-chars-random');
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('REMEMBER_ME_DURATION', 604800); // 7 days
define('CSRF_TOKEN_LENGTH', 32);

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_UPLOAD_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('AVATAR_UPLOAD_DIR', UPLOAD_DIR . 'avatars/');
define('AVATAR_UPLOAD_URL', UPLOAD_URL . 'avatars/');

// Email Configuration — Gmail SMTP (Free, 500 emails/day)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'chetanpawar1876@gmail.com');
define('SMTP_PASS', 'raoq nhry vkez uyvi');
define('MAIL_FROM_EMAIL', 'chetanpawar1876@gmail.com');
define('MAIL_FROM_NAME', 'TaskNest');

// Debug Mode — production pe false rakho
define('DEBUG', false);
define('LOG_ERRORS', true);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', DEBUG ? 1 : 0);
ini_set('log_errors', LOG_ERRORS ? 1 : 0);

// MySQLi Connection with Error Handling
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($mysqli->connect_error) {
    if (DEBUG) {
        die('Database Connection Error: ' . $mysqli->connect_error);
    } else {
        die('Database Connection Error. Please contact administrator.');
    }
}

// Set charset to utf8mb4
$mysqli->set_charset("utf8mb4");

// Enable strict mode for prepared statements
if (defined('MYSQLI_OPT_INT_AND_FLOAT_NATIVE')) {
    $mysqli->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
}

// Function to log errors
function logError($message, $severity = 'ERROR') {
    if (LOG_ERRORS) {
        $log_file = __DIR__ . '/../logs/errors.log';
        if (!is_dir(dirname($log_file))) {
            mkdir(dirname($log_file), 0755, true);
        }
        $timestamp = date('Y-m-d H:i:s');
        error_log("[$timestamp] [$severity] $message\n", 3, $log_file);
    }
}

// Set error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logError("$errstr in $errfile on line $errline", 'PHP_ERROR');
    return false;
});

// Set exception handler
set_exception_handler(function($exception) {
    logError($exception->getMessage(), 'EXCEPTION');
    if (!DEBUG) {
        echo 'An error occurred. Please try again later.';
    } else {
        echo 'Exception: ' . $exception->getMessage();
    }
});

// Close connection function
function closeDatabase() {
    global $mysqli;
    if ($mysqli) {
        $mysqli->close();
    }
}

register_shutdown_function('closeDatabase');
