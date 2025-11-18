<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 405, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$role_input = $data['role'] ?? 'pelajar'; // cikgu or pelajar from UI
$remember_me = $data['remember_me'] ?? false;

if (empty($email) || empty($password)) {
    http_response_code(400);
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
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Email doesn't exist
        http_response_code(401);
        echo json_encode([
            'status' => 401,
            'message' => 'Emel tidak dijumpai. Sila daftar terlebih dahulu.'
        ]);
        exit;
    }
    
    // Check password
    if (!password_verify($password, $user['password'])) {
        // Password is wrong
        http_response_code(401);
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
        http_response_code(401);
        echo json_encode([
            'status' => 401,
            'message' => 'Akaun ini didaftarkan sebagai ' . $correct_role_ui . '. Sila pilih peranan ' . $correct_role_ui . ' untuk log masuk.'
        ]);
        exit;
    }
    
    // All checks passed - login successful
    // Update last_seen and is_online
    $update_stmt = $db->prepare("
        UPDATE users 
        SET is_online = 1, last_seen = NOW() 
        WHERE id = ?
    ");
    $update_stmt->execute([$user['id']]);
    
    // Map database role back to UI role
    $ui_role = ($user['role'] === 'teacher') ? 'cikgu' : 'pelajar';
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $ui_role; // Keep UI role (cikgu/pelajar)
    $_SESSION['user_role_db'] = $user['role']; // Database role (teacher/student)
    $_SESSION['user_logged_in'] = true;
    $_SESSION['avatar_url'] = $user['avatar_url'];
    
    // Set cookie if remember me is checked
    if ($remember_me) {
        setcookie('user_logged_in', 'true', time() + (86400 * 30), '/'); // 30 days
        setcookie('user_email', $email, time() + (86400 * 30), '/');
        setcookie('user_id', $user['id'], time() + (86400 * 30), '/');
    }
    
    http_response_code(200);
    echo json_encode([
        'status' => 200,
        'message' => 'Log masuk berjaya',
        'data' => [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['full_name'],
            'username' => $user['username'],
            'role' => $ui_role,
            'avatar_url' => $user['avatar_url']
        ]
    ]);
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'message' => 'Ralat pelayan. Sila cuba lagi kemudian.'
    ]);
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'message' => 'Ralat berlaku. Sila cuba lagi kemudian.'
    ]);
}

