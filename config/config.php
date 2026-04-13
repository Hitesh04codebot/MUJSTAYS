<?php
// ============================================================
// MUJSTAYS — THE SINGLE CONFIG FILE
// Update ONLY this file when switching XAMPP ↔ cPanel
// ============================================================

// --- Database (Railway / Render / Clever Cloud Compatible) ---
define('DB_HOST',    ($_ENV['MYSQL_ADDON_HOST']   ?? $_ENV['DB_HOST']     ?? $_ENV['MYSQLHOST']     ?? getenv('MYSQLHOST')) ?: 'localhost');
define('DB_NAME',    ($_ENV['MYSQL_ADDON_DB']     ?? $_ENV['DB_NAME']     ?? $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE')) ?: 'mujstays_db');
define('DB_USER',    ($_ENV['MYSQL_ADDON_USER']   ?? $_ENV['DB_USER']     ?? $_ENV['MYSQLUSER']     ?? getenv('MYSQLUSER')) ?: 'root');
define('DB_PASS',    ($_ENV['MYSQL_ADDON_PASSWORD'] ?? $_ENV['DB_PASS']     ?? $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD')) ?: '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT',    ($_ENV['MYSQL_ADDON_PORT']   ?? $_ENV['DB_PORT']     ?? $_ENV['MYSQLPORT']     ?? getenv('MYSQLPORT')) ?: '3306');





// --- URLs & Paths ---
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// Smart detection: If we are on localhost, use /MUJSTAYS, otherwise use root /
$subdir = (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) ? '/MUJSTAYS' : '';
define('BASE_URL', $protocol . "://" . $host . $subdir);          // No trailing slash
define('UPLOAD_PATH', __DIR__ . '/../uploads/');           // Absolute path to uploads dir


// --- Site Identity ---
define('SITE_NAME', 'MUJSTAYS');
define('SITE_TAGLINE', 'Find Your Perfect PG Near MUJ');
define('SITE_EMAIL', 'hello@mujstays.com');
define('ADMIN_EMAIL', 'admin@mujstays.com');

// --- File Upload Limits ---
define('MAX_FILE_SIZE_MB', 10);
define('MAX_VIDEO_SIZE_MB', 50);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm']);

// --- Platform Settings ---
define('COMMISSION_RATE', 5);           // Platform commission %
define('SESSION_LIFETIME', 86400);      // 24 hours in seconds
define('OTP_EXPIRY_MINUTES', 10);
define('OTP_RESEND_COOLDOWN', 60);      // Seconds
define('LOGIN_MAX_ATTEMPTS', 100);
define('LOGIN_LOCKOUT_MINUTES', 15);
define('MUJ_LAT', 26.8370);             // MUJ Main Gate coordinates
define('MUJ_LNG', 75.5560);

// --- Payment Gateway (Razorpay) ---
define('RAZORPAY_KEY_ID', 'rzp_test_YOUR_KEY_ID');
define('RAZORPAY_KEY_SECRET', 'YOUR_KEY_SECRET');

// --- Email / SMTP ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'hiteshkandari@gmail.com');
define('SMTP_PASS', 'mlyq tung sbqy ioxe');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// --- Google Maps ---
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY');

// --- Debug Mode ---
define('DEBUG_MODE', true); // Set to false on production

// --- Error Handling ---
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
    error_reporting(E_ALL);
}

// --- Session Configuration (must be set BEFORE session_start()) ---
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
}

