<?php
function getDatabaseConnection() {
    static $db = null;
    
    if ($db === null) {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'compuplay';
        $username = $_ENV['DB_USERNAME'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        
        try {
            $db = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            error_log("Database connection failed: " . $errorMsg);
            error_log("PDO error code: " . $e->getCode());
            // Include the actual error message for debugging
            throw new Exception("Database connection failed: " . $errorMsg);
        }
    }
    
    return $db;
}

if (session_status() === PHP_SESSION_NONE) {
    // Configure session cookie settings to work across all directories
    // IMPORTANT: This MUST be called BEFORE session_start()
    session_set_cookie_params([
        'lifetime' => 0, // Until browser closes (or use remember_me cookie)
        'path' => '/', // Available for entire domain (works for localhost/Material)
        'domain' => '', // Current domain (localhost)
        'secure' => false, // Set to true in production with HTTPS
        'httponly' => true, // Prevent JavaScript access
        'samesite' => 'Lax' // Allow cross-site GET requests
    ]);
    session_start();
    
    // Debug: Log session cookie params
    error_log("Session started - ID: " . session_id());
    error_log("Session cookie params: " . print_r(session_get_cookie_params(), true));
}

