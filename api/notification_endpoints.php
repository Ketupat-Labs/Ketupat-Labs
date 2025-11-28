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

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
    sendResponse(401, null, 'Unauthorized');
}

try {
    $db = getDatabaseConnection();
    
    switch ($action) {
        
        case 'get_notifications':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $type = $_GET['type'] ?? null;
            $unread_only = $_GET['unread_only'] ?? false;
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            $query = "
                SELECT 
                    n.id,
                    n.type,
                    n.title,
                    n.message,
                    n.related_type,
                    n.related_id,
                    n.is_read,
                    n.read_at,
                    n.created_at
                FROM notifications n
                WHERE n.user_id = ?
            ";
            
            $params = [$user_id];
            
            if ($type) {
                $query .= " AND n.type = ?";
                $params[] = $type;
            }
            
            if ($unread_only) {
                $query .= " AND n.is_read = FALSE";
            }
            
            $query .= " ORDER BY n.created_at DESC LIMIT $limit OFFSET $offset";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
            $stmt->execute([$user_id]);
            $unread_count = $stmt->fetchColumn();
            
            sendResponse(200, [
                'notifications' => $notifications,
                'unread_count' => $unread_count,
                'page' => $page,
                'has_more' => count($notifications) === $limit
            ]);
            break;
        
        case 'mark_read':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $notification_id = $data['notification_id'] ?? 0;
            $mark_all = $data['mark_all'] ?? false;
            
            if ($mark_all) {
                $stmt = $db->prepare("
                    UPDATE notifications 
                    SET is_read = TRUE, read_at = NOW() 
                    WHERE user_id = ? AND is_read = FALSE
                ");
                $stmt->execute([$user_id]);
                sendResponse(200, null, 'All notifications marked as read');
            } else {
                $stmt = $db->prepare("
                    UPDATE notifications 
                    SET is_read = TRUE, read_at = NOW() 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$notification_id, $user_id]);
                sendResponse(200, null, 'Notification marked as read');
            }
            break;
        
        case 'delete_notification':
            if ($method !== 'DELETE') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $notification_id = $_GET['notification_id'] ?? 0;
            
            $stmt = $db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $stmt->execute([$notification_id, $user_id]);
            
            sendResponse(200, null, 'Notification deleted');
            break;
        
        case 'clear_all':
            if ($method !== 'DELETE') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $stmt = $db->prepare("DELETE FROM notifications WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            sendResponse(200, null, 'All notifications cleared');
            break;
        
        default:
            sendResponse(404, null, 'Action not found');
    }
    
} catch (Exception $e) {
    sendResponse(500, null, 'Server error: ' . $e->getMessage());
}

