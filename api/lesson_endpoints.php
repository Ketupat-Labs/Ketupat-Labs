<?php

header('Content-Type: application/json');
require_once '../config/database.php';

// Session is started by database.php, but ensure it's available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sendResponse($status, $data, $message = '') {
    http_response_code($status);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['user_role'] ?? '';

if (!$user_id) {
    sendResponse(401, null, 'Unauthorized');
}

try {
    $db = getDatabaseConnection();
    
    switch ($action) {
        case 'get_lessons':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            // Get lessons based on user role
            if ($user_role === 'teacher') {
                // Teachers see their own lessons
                $stmt = $db->prepare("
                    SELECT 
                        l.id,
                        l.title,
                        l.topic,
                        l.content,
                        l.duration,
                        l.material_path,
                        l.url,
                        l.is_published,
                        l.created_at,
                        l.updated_at,
                        u.full_name as teacher_name,
                        u.username as teacher_username,
                        (SELECT COUNT(*) FROM enrollment WHERE lesson_id = l.id) as enrollment_count
                    FROM lessons l
                    LEFT JOIN user u ON l.teacher_id = u.id
                    WHERE l.teacher_id = ?
                    ORDER BY l.created_at DESC
                ");
                $stmt->execute([$user_id]);
            } else {
                // Students see published lessons
                $stmt = $db->prepare("
                    SELECT 
                        l.id,
                        l.title,
                        l.topic,
                        l.content,
                        l.duration,
                        l.material_path,
                        l.url,
                        l.is_published,
                        l.created_at,
                        l.updated_at,
                        u.full_name as teacher_name,
                        u.username as teacher_username,
                        (SELECT COUNT(*) FROM enrollment WHERE lesson_id = l.id) as enrollment_count,
                        (SELECT COUNT(*) > 0 FROM enrollment WHERE lesson_id = l.id AND user_id = ?) as is_enrolled
                    FROM lessons l
                    LEFT JOIN user u ON l.teacher_id = u.id
                    WHERE l.is_published = 1
                    ORDER BY l.created_at DESC
                ");
                $stmt->execute([$user_id]);
            }
            
            $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendResponse(200, ['lessons' => $lessons]);
            break;
        
        case 'get_lesson':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $lesson_id = $_GET['lesson_id'] ?? 0;
            
            if (!$lesson_id) {
                sendResponse(400, null, 'lesson_id required');
            }
            
            $stmt = $db->prepare("
                SELECT 
                    l.*,
                    u.full_name as teacher_name,
                    u.username as teacher_username,
                    (SELECT COUNT(*) FROM enrollment WHERE lesson_id = l.id) as enrollment_count
                FROM lessons l
                LEFT JOIN user u ON l.teacher_id = u.id
                WHERE l.id = ?
            ");
            $stmt->execute([$lesson_id]);
            $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$lesson) {
                sendResponse(404, null, 'Lesson not found');
            }
            
            // Check if user is enrolled (for students)
            if ($user_role === 'student') {
                $stmt = $db->prepare("SELECT * FROM enrollment WHERE lesson_id = ? AND user_id = ?");
                $stmt->execute([$lesson_id, $user_id]);
                $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
                $lesson['enrollment'] = $enrollment;
            }
            
            sendResponse(200, ['lesson' => $lesson]);
            break;
        
        case 'create_lesson':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            if ($user_role !== 'teacher') {
                sendResponse(403, null, 'Only teachers can create lessons');
            }
            
            // Handle FormData with JSON payload or direct JSON
            $data = null;
            if (isset($_POST['data'])) {
                $data = json_decode($_POST['data'], true);
            } else {
                $raw_input = file_get_contents('php://input');
                $data = json_decode($raw_input, true);
            }
            
            if (!$data) {
                sendResponse(400, null, 'Invalid request data');
            }
            
            $title = $data['title'] ?? '';
            $topic = $data['topic'] ?? '';
            $content = $data['content'] ?? '';
            $duration = $data['duration'] ?? null;
            $url = $data['url'] ?? null;
            $is_published = $data['is_published'] ?? true;
            
            if (empty($title) || empty($topic)) {
                sendResponse(400, null, 'Title and topic are required');
            }
            
            // Handle file upload if provided
            $material_path = null;
            if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/lessons/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['material_file']['name']);
                $target_path = $upload_dir . $file_name;
                
                $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $file_type = $_FILES['material_file']['type'];
                $file_ext = strtolower(pathinfo($_FILES['material_file']['name'], PATHINFO_EXTENSION));
                $allowed_exts = ['pdf', 'doc', 'docx'];
                
                if (!in_array($file_type, $allowed_types) && !in_array($file_ext, $allowed_exts)) {
                    sendResponse(400, null, 'Invalid file type. Only PDF, DOC, and DOCX are allowed');
                }
                
                if (move_uploaded_file($_FILES['material_file']['tmp_name'], $target_path)) {
                    $material_path = 'uploads/lessons/' . $file_name;
                } else {
                    sendResponse(500, null, 'File upload failed');
                }
            }
            
            $stmt = $db->prepare("
                INSERT INTO lessons (title, topic, content, duration, material_path, url, teacher_id, is_published)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $topic, $content, $duration, $material_path, $url, $user_id, $is_published ? 1 : 0]);
            $lesson_id = $db->lastInsertId();
            
            sendResponse(200, ['lesson_id' => $lesson_id], 'Lesson created successfully');
            break;
        
        case 'update_lesson':
            if ($method !== 'PUT') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            if ($user_role !== 'teacher') {
                sendResponse(403, null, 'Only teachers can update lessons');
            }
            
            // Handle FormData with JSON payload or direct JSON
            $data = null;
            if (isset($_POST['data'])) {
                $data = json_decode($_POST['data'], true);
            } else {
                $raw_input = file_get_contents('php://input');
                $data = json_decode($raw_input, true);
            }
            
            if (!$data) {
                sendResponse(400, null, 'Invalid request data');
            }
            
            $lesson_id = $data['lesson_id'] ?? 0;
            $title = $data['title'] ?? '';
            $topic = $data['topic'] ?? '';
            $content = $data['content'] ?? '';
            $duration = $data['duration'] ?? null;
            $url = $data['url'] ?? null;
            $is_published = $data['is_published'] ?? null;
            
            if (!$lesson_id) {
                sendResponse(400, null, 'lesson_id required');
            }
            
            // Check ownership
            $stmt = $db->prepare("SELECT teacher_id FROM lessons WHERE id = ?");
            $stmt->execute([$lesson_id]);
            $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$lesson) {
                sendResponse(404, null, 'Lesson not found');
            }
            
            if ($lesson['teacher_id'] != $user_id) {
                sendResponse(403, null, 'Not authorized to update this lesson');
            }
            
            // Build update query dynamically
            $update_fields = [];
            $params = [];
            
            if (!empty($title)) {
                $update_fields[] = "title = ?";
                $params[] = $title;
            }
            if (!empty($topic)) {
                $update_fields[] = "topic = ?";
                $params[] = $topic;
            }
            if (isset($content)) {
                $update_fields[] = "content = ?";
                $params[] = $content;
            }
            if (isset($duration)) {
                $update_fields[] = "duration = ?";
                $params[] = $duration;
            }
            if (isset($url)) {
                $update_fields[] = "url = ?";
                $params[] = $url;
            }
            if (isset($is_published)) {
                $update_fields[] = "is_published = ?";
                $params[] = $is_published ? 1 : 0;
            }
            
            // Handle file upload if provided (check both $_FILES and FormData)
            if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/lessons/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['material_file']['name']);
                $target_path = $upload_dir . $file_name;
                
                $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $file_type = $_FILES['material_file']['type'];
                
                // Also check file extension as fallback
                $file_ext = strtolower(pathinfo($_FILES['material_file']['name'], PATHINFO_EXTENSION));
                $allowed_exts = ['pdf', 'doc', 'docx'];
                
                if (in_array($file_type, $allowed_types) || in_array($file_ext, $allowed_exts)) {
                    if (move_uploaded_file($_FILES['material_file']['tmp_name'], $target_path)) {
                        // Delete old file if exists
                        $stmt = $db->prepare("SELECT material_path FROM lessons WHERE id = ?");
                        $stmt->execute([$lesson_id]);
                        $old_lesson = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($old_lesson && $old_lesson['material_path']) {
                            $old_file = '../' . $old_lesson['material_path'];
                            if (file_exists($old_file)) {
                                unlink($old_file);
                            }
                        }
                        
                        $update_fields[] = "material_path = ?";
                        $params[] = 'uploads/lessons/' . $file_name;
                    }
                }
            }
            
            // Handle FormData with JSON payload
            if (isset($_POST['data'])) {
                $formData = json_decode($_POST['data'], true);
                if ($formData) {
                    if (isset($formData['title'])) $title = $formData['title'];
                    if (isset($formData['topic'])) $topic = $formData['topic'];
                    if (isset($formData['content'])) $content = $formData['content'];
                    if (isset($formData['duration'])) $duration = $formData['duration'];
                    if (isset($formData['url'])) $url = $formData['url'];
                    if (isset($formData['is_published'])) $is_published = $formData['is_published'];
                }
            }
            
            if (empty($update_fields)) {
                sendResponse(400, null, 'No fields to update');
            }
            
            $params[] = $lesson_id;
            $sql = "UPDATE lessons SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            sendResponse(200, null, 'Lesson updated successfully');
            break;
        
        case 'delete_lesson':
            if ($method !== 'DELETE') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            if ($user_role !== 'teacher') {
                sendResponse(403, null, 'Only teachers can delete lessons');
            }
            
            $lesson_id = $_GET['lesson_id'] ?? 0;
            
            if (!$lesson_id) {
                sendResponse(400, null, 'lesson_id required');
            }
            
            // Check ownership
            $stmt = $db->prepare("SELECT teacher_id, material_path FROM lessons WHERE id = ?");
            $stmt->execute([$lesson_id]);
            $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$lesson) {
                sendResponse(404, null, 'Lesson not found');
            }
            
            if ($lesson['teacher_id'] != $user_id) {
                sendResponse(403, null, 'Not authorized to delete this lesson');
            }
            
            // Delete file if exists
            if ($lesson['material_path']) {
                $file_path = '../' . $lesson['material_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            // Delete lesson
            $stmt = $db->prepare("DELETE FROM lessons WHERE id = ?");
            $stmt->execute([$lesson_id]);
            
            sendResponse(200, null, 'Lesson deleted successfully');
            break;
        
        case 'assign_lesson':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            if ($user_role !== 'teacher') {
                sendResponse(403, null, 'Only teachers can assign lessons');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $classroom_id = $data['classroom_id'] ?? 0;
            $lesson_ids = $data['lesson_ids'] ?? [];
            
            if (!$classroom_id || empty($lesson_ids)) {
                sendResponse(400, null, 'classroom_id and lesson_ids are required');
            }
            
            // Verify classroom ownership
            $stmt = $db->prepare("SELECT teacher_id FROM classes WHERE id = ?");
            $stmt->execute([$classroom_id]);
            $classroom = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$classroom) {
                sendResponse(404, null, 'Classroom not found');
            }
            
            if ($classroom['teacher_id'] != $user_id) {
                sendResponse(403, null, 'Not authorized to assign lessons to this classroom');
            }
            
            $db->beginTransaction();
            try {
                // Get all students in the classroom
                $stmt = $db->prepare("SELECT student_id FROM class_students WHERE class_id = ?");
                $stmt->execute([$classroom_id]);
                $students = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($lesson_ids as $lesson_id) {
                    // Create lesson assignment
                    $stmt = $db->prepare("
                        INSERT INTO lesson_assignments (classroom_id, lesson_id, type, assigned_at)
                        VALUES (?, ?, 'Mandatory', NOW())
                        ON DUPLICATE KEY UPDATE assigned_at = NOW()
                    ");
                    $stmt->execute([$classroom_id, $lesson_id]);
                    
                    // Enroll all students in the lesson
                    foreach ($students as $student_id) {
                        $stmt = $db->prepare("
                            INSERT INTO enrollment (user_id, lesson_id, status, progress)
                            VALUES (?, ?, 'in_progress', 0)
                            ON DUPLICATE KEY UPDATE status = 'in_progress'
                        ");
                        $stmt->execute([$student_id, $lesson_id]);
                    }
                }
                
                $db->commit();
                sendResponse(200, null, 'Lessons assigned successfully');
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;
        
        case 'enroll_lesson':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            if ($user_role !== 'student') {
                sendResponse(403, null, 'Only students can enroll in lessons');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $lesson_id = $data['lesson_id'] ?? 0;
            
            if (!$lesson_id) {
                sendResponse(400, null, 'lesson_id required');
            }
            
            // Check if already enrolled
            $stmt = $db->prepare("SELECT id FROM enrollment WHERE user_id = ? AND lesson_id = ?");
            $stmt->execute([$user_id, $lesson_id]);
            if ($stmt->fetch()) {
                sendResponse(400, null, 'Already enrolled in this lesson');
            }
            
            // Create enrollment
            $stmt = $db->prepare("
                INSERT INTO enrollment (user_id, lesson_id, status, progress)
                VALUES (?, ?, 'in_progress', 0)
            ");
            $stmt->execute([$user_id, $lesson_id]);
            
            sendResponse(200, null, 'Enrolled successfully');
            break;
        
        default:
            sendResponse(404, null, 'Action not found');
    }
    
} catch (Exception $e) {
    sendResponse(500, null, 'Server error: ' . $e->getMessage());
}

