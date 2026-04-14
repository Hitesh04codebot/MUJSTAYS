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
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                // Disable ONLY_FULL_GROUP_BY for compatibility with MySQL 8.0
                $pdo->exec("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
            } catch (PDOException $e) {
                // For PBL evaluation debugging, we show the full error
                die('<div style="background:#fee;padding:20px;font-family:monospace;border-left:4px solid red">
                    <strong>Database Connection Failed:</strong><br>' . htmlspecialchars($e->getMessage()) . '
                    <br><br><strong>Current Config:</strong><br>
                    Host: ' . DB_HOST . '<br>
                    Port: ' . DB_PORT . '<br>
                    DB: ' . DB_NAME . '<br>
                    User: ' . DB_USER . '
                </div>');
            }

        }
        return $pdo;
    }
}

// Shortcut alias for legacy $pdo usage
$pdo = get_db();
