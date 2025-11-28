<?php
// Require database FIRST - it will configure session cookie params and start session
// This is critical - session cookie params must be set BEFORE session_start()
require_once '../config/database.php';

// Session should now be started by database.php with correct cookie params
// Double-check that session is started
if (session_status() === PHP_SESSION_NONE) {
    // This shouldn't happen if database.php worked correctly
    error_log("WARNING: Session not started after requiring database.php");
    session_start();
}

// Debug: Log session state (remove in production)
error_log("Dashboard - Session ID: " . session_id());
error_log("Dashboard - Cookie params: " . print_r(session_get_cookie_params(), true));
error_log("Dashboard - user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("Dashboard - user_logged_in: " . ($_SESSION['user_logged_in'] ?? 'NOT SET'));
error_log("Dashboard - All session data: " . print_r($_SESSION, true));
error_log("Dashboard - All cookies: " . print_r($_COOKIE, true));

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in'])) {
    error_log("Dashboard: User not logged in, redirecting to login");
    error_log("Dashboard: Session ID: " . session_id());
    error_log("Dashboard: Session status: " . session_status());
    error_log("Dashboard: Session data available: " . (empty($_SESSION) ? 'EMPTY' : 'HAS DATA'));
    error_log("Dashboard: Cookies received: " . print_r($_COOKIE, true));
    error_log("Dashboard: Request headers: " . print_r(getallheaders(), true));
    header('Location: ../login.html');
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
        header('Location: ../login.html');
        exit;
    }
    
    // Set user role (map database role to UI role)
    $user_role_db = $user['role']; // teacher or student
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    header('Location: ../login.html');
    exit;
}

// Include appropriate dashboard view based on role
if ($user_role_db === 'teacher') {
    include 'dashboard-teacher.php';
} else {
    include 'dashboard-student.php';
}
?>

