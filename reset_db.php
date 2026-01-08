<?php

$host = '127.0.0.1';
$user = 'root';
$pass = ''; // Default XAMPP password

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating database compuplay_clean...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS compuplay_clean CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
