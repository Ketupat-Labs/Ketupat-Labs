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
$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$role_input = $data['role'] ?? 'pelajar'; // cikgu or pelajar from UI

if (empty($name) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'message' => 'Semua medan diperlukan']);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'message' => 'Kata laluan mesti sekurang-kurangnya 8 aksara']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'message' => 'Format emel tidak sah']);
    exit;
}

try {
    $db = getDatabaseConnection();
    
    // Map UI role to database role
    $db_role = ($role_input === 'cikgu') ? 'teacher' : 'student';
    
    // Check if email already exists
    $check_stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->execute([$email]);
    if ($check_stmt->fetch()) {
        http_response_code(409);
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
    $username_check = $db->prepare("SELECT id FROM users WHERE username = ?");
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
        INSERT INTO users (username, email, password, role, full_name, is_online, last_seen) 
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
    
    http_response_code(200);
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
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    
    // Check for duplicate entry error
    if ($e->getCode() == 23000) {
        http_response_code(409);
        echo json_encode([
            'status' => 409,
            'message' => 'Emel atau nama pengguna sudah wujud. Sila cuba dengan maklumat lain.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 500,
            'message' => 'Ralat pelayan. Sila cuba lagi kemudian.'
        ]);
    }
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'message' => 'Ralat berlaku. Sila cuba lagi kemudian.'
    ]);
}

