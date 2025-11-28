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

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header early
header('Content-Type: application/json');

// Require database after headers are set
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

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    ob_clean();
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['status' => 405, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$role_input = $data['role'] ?? 'pelajar'; // cikgu or pelajar from UI

if (empty($name) || empty($email) || empty($password)) {
    ob_clean();
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['status' => 400, 'message' => 'Semua medan diperlukan']);
    exit;
}

if (strlen($password) < 8) {
    ob_clean();
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['status' => 400, 'message' => 'Kata laluan mesti sekurang-kurangnya 8 aksara']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_clean();
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['status' => 400, 'message' => 'Format emel tidak sah']);
    exit;
}

try {
    $db = getDatabaseConnection();
    
    // Map UI role to database role
    $db_role = ($role_input === 'cikgu') ? 'teacher' : 'student';
    
    // Check if email already exists
    $check_stmt = $db->prepare("SELECT id FROM user WHERE email = ?");
    $check_stmt->execute([$email]);
    if ($check_stmt->fetch()) {
        ob_clean();
        http_response_code(409);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 409,
            'message' => 'Emel sudah didaftarkan. Sila gunakan emel lain atau log masuk.'
        ]);
        exit;
    }
    
    // Generate username from email (before @) or from name
    $username_base = strtolower(explode('@', $email)[0]);
    $username = $username_base;
    $counter = 1;
    
    // Check if username exists and generate unique one
    $username_check = $db->prepare("SELECT id FROM user WHERE username = ?");
    while (true) {
        $username_check->execute([$username]);
        if (!$username_check->fetch()) {
            break; // Username is available
        }
        $username = $username_base . $counter;
        $counter++;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user into database
    $insert_stmt = $db->prepare("
        INSERT INTO user (username, email, password, role, full_name, is_online, last_seen) 
        VALUES (?, ?, ?, ?, ?, 1, NOW())
    ");
    $insert_stmt->execute([$username, $email, $hashed_password, $db_role, $name]);
    
    $new_user_id = $db->lastInsertId();
    
    // Set session for auto-login after registration
    $_SESSION['user_id'] = $new_user_id;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $name;
    $_SESSION['username'] = $username;
    $_SESSION['user_role'] = $role_input; // Keep UI role (cikgu/pelajar)
    $_SESSION['user_role_db'] = $db_role; // Database role (teacher/student)
    $_SESSION['user_logged_in'] = true;
    
    ob_clean();
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 200,
        'message' => 'Pendaftaran berjaya!',
        'data' => [
            'user_id' => $new_user_id,
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'role' => $role_input
        ]
    ]);
    exit;
} catch (PDOException $e) {
    ob_clean();
    error_log("Registration error: " . $e->getMessage());
    
    // Check for duplicate entry error
    if ($e->getCode() == 23000) {
        http_response_code(409);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 409,
            'message' => 'Emel atau nama pengguna sudah wujud. Sila cuba dengan maklumat lain.'
        ]);
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 500,
            'message' => 'Ralat pelayan. Sila cuba lagi kemudian.'
        ]);
    }
    exit;
} catch (Exception $e) {
    ob_clean();
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 500,
        'message' => 'Ralat berlaku. Sila cuba lagi kemudian.'
    ]);
    exit;
}

