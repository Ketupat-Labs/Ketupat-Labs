<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>PHP is working!</h1>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Test writing to storage
$storagePath = __DIR__ . '/../storage/logs/laravel.log';
echo "<p>Checking storage permissions...</p>";
if (is_writable(dirname($storagePath))) {
    echo "<p style='color:green'>Storage is writable.</p>";
} else {
    echo "<p style='color:red'>Storage is NOT writable.</p>";
}

// Test Database Connection (using raw PDO to bypass Laravel)
echo "<h3>Database Connection Test</h3>";
try {
    $dsn = "mysql:host=" . getenv('DB_HOST') . ";port=" . getenv('DB_PORT') . ";dbname=" . getenv('DB_DATABASE');
    echo "Connecting to: " . str_replace(getenv('DB_PASSWORD'), '****', $dsn) . "<br>";
    
    $pdo = new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    echo "<p style='color:green'>Database connection successful!</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Database connection failed: " . $e->getMessage() . "</p>";
}

// Check Environment Variables
echo "<h3>Environment Variables Check</h3>";
$required = ['APP_KEY', 'DB_HOST', 'DB_USERNAME', 'DB_PASSWORD'];
foreach ($required as $key) {
    $val = getenv($key);
    if ($val) {
        $len = strlen($val);
        echo "$key: Set (length: $len)<br>";
    } else {
        echo "$key: <strong style='color:red'>MISSING</strong><br>";
    }
}
