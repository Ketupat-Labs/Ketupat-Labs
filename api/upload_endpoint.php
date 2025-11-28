<?php
header('Content-Type: application/json');
require_once '../config/database.php';

function sendResponse($status, $data, $message = '') {
    http_response_code($status);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Allow uploads without session/login; fall back to provided user_id or default 1
$user_id = $_SESSION['user_id'] ?? ($_POST['user_id'] ?? ($_GET['user_id'] ?? 1));

// Support both single file and multiple files
$files = [];
if (isset($_FILES['files'])) {
    // Multiple files
    if (is_array($_FILES['files']['name'])) {
        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
            if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                $files[] = [
                    'name' => $_FILES['files']['name'][$i],
                    'type' => $_FILES['files']['type'][$i],
                    'tmp_name' => $_FILES['files']['tmp_name'][$i],
                    'size' => $_FILES['files']['size'][$i]
                ];
            }
        }
    }
} elseif (isset($_FILES['file'])) {
    // Single file
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $files[] = $_FILES['file'];
    }
}

if (empty($files)) {
    sendResponse(400, null, 'No files uploaded or upload error');
}

$upload_dir = '../uploads/' . date('Y/m/');
$max_size = 10 * 1024 * 1024; // 10MB

$uploaded_files = [];

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

foreach ($files as $file) {
    if ($file['size'] > $max_size) {
        sendResponse(400, null, "File {$file['name']} exceeds 10MB limit");
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('file_', true) . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        sendResponse(500, null, "Failed to save file {$file['name']}");
    }
    
    $uploaded_files[] = [
        'url' => str_replace('../', '', $filepath),
        'name' => $file['name'],
        'type' => $file['type'],
        'size' => $file['size']
    ];
}

sendResponse(200, ['files' => $uploaded_files], 'Files uploaded successfully');

