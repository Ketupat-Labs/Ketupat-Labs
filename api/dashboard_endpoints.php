<?php
session_start();
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

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Get user_id from session
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    sendResponse(401, null, 'Tidak dibenarkan. Sila log masuk terlebih dahulu.');
    exit;
}

try {
    $db = getDatabaseConnection();
    
    // Get user role
    $user_stmt = $db->prepare("SELECT role FROM user WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch();
    
    if (!$user) {
        sendResponse(401, null, 'Pengguna tidak dijumpai.');
        exit;
    }
    
    $user_role = $user['role'];
    
    switch ($action) {
        case 'get_stats':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $stats = [];
            
            // Check if lessons table exists
            $lessons_exists = false;
            try {
                $check_lessons = $db->query("SHOW TABLES LIKE 'lessons'");
                $lessons_exists = $check_lessons->rowCount() > 0;
            } catch (Exception $e) {
                $lessons_exists = false;
            }
            
            // Check if quiz_attempts table exists
            $quiz_exists = false;
            try {
                $check_quiz = $db->query("SHOW TABLES LIKE 'quiz_attempts'");
                $quiz_exists = $check_quiz->rowCount() > 0;
            } catch (Exception $e) {
                $quiz_exists = false;
            }
            
            // Check if submissions table exists
            $submissions_exists = false;
            try {
                $check_submissions = $db->query("SHOW TABLES LIKE 'submissions'");
                $submissions_exists = $check_submissions->rowCount() > 0;
            } catch (Exception $e) {
                $submissions_exists = false;
            }
            
            if ($user_role === 'teacher') {
                // Teacher stats
                if ($lessons_exists) {
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM lessons WHERE teacher_id = ?");
                    $stmt->execute([$user_id]);
                    $total_lessons = $stmt->fetch()['count'] ?? 0;
                    
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM lessons WHERE teacher_id = ? AND is_published = 1");
                    $stmt->execute([$user_id]);
                    $published_lessons = $stmt->fetch()['count'] ?? 0;
                } else {
                    $total_lessons = 0;
                    $published_lessons = 0;
                }
                
                if ($submissions_exists) {
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM submissions WHERE status = ?");
                    $stmt->execute(['Submitted - Awaiting Grade']);
                    $pending_submissions = $stmt->fetch()['count'] ?? 0;
                    
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM submissions");
                    $stmt->execute();
                    $total_submissions = $stmt->fetch()['count'] ?? 0;
                } else {
                    $pending_submissions = 0;
                    $total_submissions = 0;
                }
                
                $stats = [
                    [
                        'label' => 'Published Lessons',
                        'value' => $published_lessons,
                        'color' => '#2454FF',
                        'icon' => 'lessons'
                    ],
                    [
                        'label' => 'Total Lessons',
                        'value' => $total_lessons,
                        'color' => '#5FAD56',
                        'icon' => 'lessons'
                    ],
                    [
                        'label' => 'Pending Submissions',
                        'value' => $pending_submissions,
                        'color' => '#F26430',
                        'icon' => 'quiz'
                    ],
                    [
                        'label' => 'Total Submissions',
                        'value' => $total_submissions,
                        'color' => '#FFBA08',
                        'icon' => 'submissions'
                    ]
                ];
            } else {
                // Student stats
                if ($lessons_exists) {
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM lessons WHERE is_published = 1");
                    $stmt->execute();
                    $available_lessons = $stmt->fetch()['count'] ?? 0;
                } else {
                    $available_lessons = 0;
                }
                
                if ($quiz_exists) {
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM quiz_attempts WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $quiz_attempts = $stmt->fetch()['count'] ?? 0;
                } else {
                    $quiz_attempts = 0;
                }
                
                if ($submissions_exists) {
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM submissions WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $my_submissions = $stmt->fetch()['count'] ?? 0;
                    
                    // Points column doesn't exist in user table, set to 0
                    $user_points = 0;
                } else {
                    $my_submissions = 0;
                    $user_points = 0;
                }
                
                $stats = [
                    [
                        'label' => 'Available Lessons',
                        'value' => $available_lessons,
                        'color' => '#2454FF',
                        'icon' => 'lessons'
                    ],
                    [
                        'label' => 'Quiz Attempts',
                        'value' => $quiz_attempts,
                        'color' => '#F26430',
                        'icon' => 'quiz'
                    ],
                    [
                        'label' => 'My Submissions',
                        'value' => $my_submissions,
                        'color' => '#FFBA08',
                        'icon' => 'submissions'
                    ],
                    [
                        'label' => 'Points Earned',
                        'value' => $user_points,
                        'color' => '#5FAD56',
                        'icon' => 'points'
                    ]
                ];
            }
            
            // Get counts for quick access cards
            $counts = [];
            if ($lessons_exists) {
                if ($user_role === 'teacher') {
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM lessons WHERE teacher_id = ?");
                    $stmt->execute([$user_id]);
                    $counts['myLessons'] = $stmt->fetch()['count'] ?? 0;
                } else {
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM lessons WHERE is_published = 1");
                    $stmt->execute();
                    $counts['availableLessons'] = $stmt->fetch()['count'] ?? 0;
                }
            } else {
                $counts['myLessons'] = 0;
                $counts['availableLessons'] = 0;
            }
            
            if ($quiz_exists && $user_role === 'student') {
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM quiz_attempts WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $counts['quizAttempts'] = $stmt->fetch()['count'] ?? 0;
            } else {
                $counts['quizAttempts'] = 0;
            }
            
            if ($submissions_exists) {
                if ($user_role === 'teacher') {
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM submissions WHERE status = ?");
                    $stmt->execute(['Submitted - Awaiting Grade']);
                    $counts['pendingSubmissions'] = $stmt->fetch()['count'] ?? 0;
                } else {
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM submissions WHERE user_id = ? AND status = ?");
                    $stmt->execute([$user_id, 'Submitted - Awaiting Grade']);
                    $counts['pendingSubmissions'] = $stmt->fetch()['count'] ?? 0;
                }
            } else {
                $counts['pendingSubmissions'] = 0;
            }
            
            sendResponse(200, [
                'stats' => $stats,
                'counts' => $counts
            ]);
            break;
            
        case 'get_recent_lessons':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            // Check if lessons table exists
            $lessons_exists = false;
            try {
                $check_lessons = $db->query("SHOW TABLES LIKE 'lessons'");
                $lessons_exists = $check_lessons->rowCount() > 0;
            } catch (Exception $e) {
                $lessons_exists = false;
            }
            
            if (!$lessons_exists) {
                sendResponse(200, ['lessons' => []]);
                exit;
            }
            
            if ($user_role === 'teacher') {
                $stmt = $db->prepare("
                    SELECT id, title, topic, duration, is_published, created_at 
                    FROM lessons 
                    WHERE teacher_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 3
                ");
                $stmt->execute([$user_id]);
            } else {
                $stmt = $db->prepare("
                    SELECT id, title, topic, duration, created_at 
                    FROM lessons 
                    WHERE is_published = 1 
                    ORDER BY created_at DESC 
                    LIMIT 3
                ");
                $stmt->execute();
            }
            
            $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(200, ['lessons' => $lessons]);
            break;
            
        default:
            sendResponse(400, null, 'Invalid action');
            break;
    }
    
} catch (Exception $e) {
    error_log("Dashboard endpoint error: " . $e->getMessage());
    sendResponse(500, null, 'Server error occurred');
}

