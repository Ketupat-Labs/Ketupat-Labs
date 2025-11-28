<?php
// Check current user session status
// Prevent any output before JSON
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Suppress error display, log them instead
error_reporting(E_ALL);
// In development, we want to see errors in response
// Set to 0 in production
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Require database FIRST - it will configure session cookie params and start session
try {
    if (!file_exists('../../config/database.php')) {
        throw new Exception('Database configuration file not found');
    }
    require_once '../../config/database.php';
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 500,
        'message' => 'Configuration error: ' . $e->getMessage()
    ]);
    exit;
}

// Set JSON header
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    ob_clean();
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['status' => 405, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in'])) {
    ob_clean();
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 401,
        'message' => 'Not authenticated',
        'data' => null
    ]);
    exit;
}

// Get user data from database
try {
    $db = getDatabaseConnection();
    $stmt = $db->prepare("SELECT id, username, email, full_name, role, avatar_url FROM user WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        ob_clean();
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 401,
            'message' => 'User not found',
            'data' => null
        ]);
        exit;
    }
    
    $ui_role = ($user['role'] === 'teacher') ? 'cikgu' : 'pelajar';
    
    ob_clean();
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 200,
        'message' => 'Authenticated',
        'data' => [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['full_name'],
            'username' => $user['username'],
            'role' => $ui_role,
            'avatar_url' => $user['avatar_url']
        ]
    ]);
    exit;
} catch (PDOException $e) {
    ob_clean();
    $errorMsg = $e->getMessage();
    error_log("Auth check PDO error: " . $errorMsg);
    error_log("PDO error code: " . $e->getCode());
    http_response_code(500);
    header('Content-Type: application/json');
    // Always include error details for debugging (remove in production)
    echo json_encode([
        'status' => 500,
        'message' => 'Database error: ' . $errorMsg,
        'error_code' => $e->getCode(),
        'error_details' => $errorMsg
    ]);
    exit;
} catch (Exception $e) {
    ob_clean();
    $errorMsg = $e->getMessage();
    error_log("Auth check error: " . $errorMsg);
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json');
    // Always include error details for debugging (remove in production)
    echo json_encode([
        'status' => 500,
        'message' => 'Server error: ' . $errorMsg,
        'error_details' => $errorMsg,
        'stack_trace' => (ini_get('display_errors')) ? $e->getTraceAsString() : null
    ]);
    exit;
}
?>

