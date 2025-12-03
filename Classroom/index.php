<?php
// Require database FIRST - it will configure session cookie params and start session
require_once '../config/database.php';

// Session should now be started by database.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_logged_in'])) {
    header('Location: ../login.html');
    exit;
}

// Get user data from database to determine role
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
    
    // Redirect based on user role
    if ($user['role'] === 'teacher') {
        header('Location: Lesson/manage-lessons.php');
    } else {
        header('Location: Lesson/student-lessons.php');
    }
    exit;
} catch (Exception $e) {
    error_log("Classroom index error: " . $e->getMessage());
    header('Location: ../login.html');
    exit;
}
?>

