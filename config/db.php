<?php
// config/db.php

// $host = 'localhost';
// $db   = 'sheindana_db';
// $user = 'root';      // Default XAMPP user
// $pass = 'Stephan2k03';          // Default XAMPP password (leave empty)
// $charset = 'utf8mb4';

$host = 'localhost';
$db   = 'shinedana_co_db';
$user = 'shinedana_co';      // Default XAMPP user
$pass = 'shinedanaDBUSER2026@!';          // Default XAMPP password (leave empty)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Start session on every page load
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>