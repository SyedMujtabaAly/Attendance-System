<?php
/**
 * Database configuration for HRMS (PHP)
 * Uses SQLite by default for easy setup. For MySQL, see comments below.
 */

define('DB_PATH', __DIR__ . '/../data/hrms.sqlite');

try {
    // SQLite (default - no server needed)
    if (!extension_loaded('pdo_sqlite')) {
        throw new PDOException("Missing PDO SQLite driver (pdo_sqlite). Enable extension=pdo_sqlite and extension=sqlite3 in php.ini, then restart PHP.");
    }
    $pdo = new PDO(
        'sqlite:' . DB_PATH,
        null,
        null,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    /*
    // MySQL alternative - uncomment and set your credentials:
    $pdo = new PDO(
        'mysql:host=localhost;dbname=hrms;charset=utf8mb4',
        'your_username',
        'your_password',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    */
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

return $pdo;
