<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexión PDO a MySQL
$host = '127.0.0.1';
$db   = 'bibliobros';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('DB error: ' . $e->getMessage());
}
