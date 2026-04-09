<?php
// includes/db.php — PDO Singleton Connection
// Included by every page that needs database access

require_once __DIR__ . '/../config/config.php';

/**
 * Global PDO singleton — call get_db() anywhere
 */
if (!function_exists('get_db')) {
    function get_db(): PDO {
        static $pdo = null;
        if ($pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                if (defined('DEBUG_MODE') && DEBUG_MODE) {
                    die('<div style="background:#fee;padding:20px;font-family:monospace;border-left:4px solid red">
                        <strong>Database Connection Failed:</strong><br>' . htmlspecialchars($e->getMessage()) . '
                    </div>');
                } else {
                    error_log('DB Connect Error: ' . $e->getMessage());
                    die('<h3>Service temporarily unavailable. Please try again later.</h3>');
                }
            }
        }
        return $pdo;
    }
}

// Shortcut alias for legacy $pdo usage
$pdo = get_db();
