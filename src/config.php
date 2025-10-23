<?php
/*
 * config.php
 * Database connection settings and site configuration.
 */

// --- DATABASE SETTINGS ---
// !! Replace with your actual database credentials !!
define('DB_HOST', 'localhost');
define('DB_NAME', 'sustainable_delivery');
define('DB_USER', 'root');
define('DB_PASS', '');

// --- PDO Database Connection ---
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// --- Session ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}