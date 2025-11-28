<?php
// Prevent any output before JSON
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Suppress error display, log them instead
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Require database FIRST (it will configure session cookie params and start session)
// This MUST happen before any headers are sent or session is started
try {
    if (!file_exists('../../config/database.php')) {
        throw new Exception('Database configuration file not found');
    }
    require_once '../../config/database.php';
    // database.php will configure session cookie params and start session
    
// Set JSON header after session is configured
header('Content-Type: application/json');
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

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    ob_clean();
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['status' => 405, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$role_input = $data['role'] ?? 'pelajar'; // cikgu or pelajar from UI
$remember_me = $data['remember_me'] ?? false;

if (empty($email) || empty($password)) {
    ob_clean();
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['status' => 400, 'message' => 'Email dan kata laluan diperlukan']);
    exit;
}

try {
    $db = getDatabaseConnection();
    
    // Map UI role to database role
    $db_role = ($role_input === 'cikgu') ? 'teacher' : 'student';
    
    // First, check if email exists (without role check)
    $stmt = $db->prepare("
        SELECT id, username, email, password, role, full_name, avatar_url 
        FROM user 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Email doesn't exist
        ob_clean();
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 401,
            'message' => 'Emel tidak dijumpai. Sila daftar terlebih dahulu.'
        ]);
        exit;
    }
    
    // Check password
    if (!password_verify($password, $user['password'])) {
        // Password is wrong
        ob_clean();
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 401,
            'message' => 'Kata laluan tidak betul. Sila cuba lagi.'
        ]);
        exit;
    }
    
    // Check if role matches
    if ($user['role'] !== $db_role) {
        // Role doesn't match - tell user which role to use
        $correct_role_ui = ($user['role'] === 'teacher') ? 'Cikgu' : 'Pelajar';
        ob_clean();
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 401,
            'message' => 'Akaun ini didaftarkan sebagai ' . $correct_role_ui . '. Sila pilih peranan ' . $correct_role_ui . ' untuk log masuk.'
        ]);
        exit;
    }
    
    // All checks passed - login successful
    // Update last_seen and is_online
    $update_stmt = $db->prepare("
        UPDATE user 
        SET is_online = 1, last_seen = NOW() 
        WHERE id = ?
    ");
    $update_stmt->execute([$user['id']]);
    
    // Map database role back to UI role
    $ui_role = ($user['role'] === 'teacher') ? 'cikgu' : 'pelajar';
    
    // CRITICAL: Regenerate session ID BEFORE setting session variables
    // This prevents session fixation attacks and ensures a fresh session
    // The old session data will be preserved and transferred to the new session
    session_regenerate_id(true);
    
    // Set session variables AFTER regenerating session ID
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $ui_role; // Keep UI role (cikgu/pelajar)
    $_SESSION['user_role_db'] = $user['role']; // Database role (teacher/student)
    $_SESSION['user_logged_in'] = true;
    $_SESSION['avatar_url'] = $user['avatar_url'];
    
    // Debug: Log session to verify it's set
    error_log("Login successful - Session ID: " . session_id());
    error_log("Login successful - User ID in session: " . $_SESSION['user_id']);
    error_log("Login successful - Session data: " . print_r($_SESSION, true));
    error_log("Login successful - Session cookie params: " . print_r(session_get_cookie_params(), true));
    
    // Set cookie if remember me is checked
    if ($remember_me) {
        setcookie('user_logged_in', 'true', time() + (86400 * 30), '/', '', false, true); // 30 days, httponly
        setcookie('user_email', $email, time() + (86400 * 30), '/', '', false, true);
        setcookie('user_id', $user['id'], time() + (86400 * 30), '/', '', false, true);
    }
    
    // Force session write - ensure all session data is saved
    // Don't close the session - let PHP handle it automatically when script exits
    // Closing it too early might prevent the session cookie from being sent properly
    // The session will be automatically saved when the script ends
    
    ob_clean(); // Clear any output before JSON
    http_response_code(200);
    header('Content-Type: application/json');
    
    // Include session ID in response for debugging
    $response = [
        'status' => 200,
        'message' => 'Log masuk berjaya',
        'data' => [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['full_name'],
            'username' => $user['username'],
            'role' => $ui_role,
            'avatar_url' => $user['avatar_url']
        ],
        'session_id' => session_id() // For debugging - remove in production
    ];
    
    echo json_encode($response);
    
    // Flush output to ensure response is sent immediately
    if (ob_get_level()) {
        ob_end_flush();
    }
    flush();
    
    // Session will be automatically saved when script exits
    // Don't close it here as it might prevent the cookie from being sent
    exit;
} catch (PDOException $e) {
    ob_clean(); // Clear any output
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 500,
        'message' => 'Ralat pelayan. Sila cuba lagi kemudian.'
    ]);
    exit;
} catch (Exception $e) {
    ob_clean(); // Clear any output
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 500,
        'message' => 'Ralat berlaku. Sila cuba lagi kemudian.'
    ]);
    exit;
}

