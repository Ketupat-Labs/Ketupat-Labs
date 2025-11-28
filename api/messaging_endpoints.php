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

// Get user_id from session
$user_id = $_SESSION['user_id'] ?? null;

// If no session user_id, handle based on action
if (!$user_id && in_array($action, ['get_conversations', 'get_messages', 'send_message', 'create_group_chat', 'update_online_status'])) {
    // For get_conversations, return empty array instead of error to avoid alerts
    if ($action === 'get_conversations') {
        sendResponse(200, ['conversations' => []]);
    } else {
        sendResponse(401, null, 'Tidak dibenarkan. Sila log masuk terlebih dahulu.');
    }
    exit;
}

try {
    $db = getDatabaseConnection();
    
    switch ($action) {
        case 'send_message':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $conversation_id = $data['conversation_id'] ?? 0;
            $content = $data['content'] ?? '';
            $message_type = $data['message_type'] ?? 'text';
            $attachment_url = $data['attachment_url'] ?? null;
            $attachment_name = $data['attachment_name'] ?? null;
            $attachment_size = $data['attachment_size'] ?? null;
            
            if (empty($content) && $message_type === 'text') {
                sendResponse(400, null, 'Message content is required');
            }
            
            $stmt = $db->prepare("SELECT id FROM conversation_participants WHERE conversation_id = ? AND user_id = ?");
            $stmt->execute([$conversation_id, $user_id]);
            if (!$stmt->fetch()) {
                sendResponse(403, null, 'Not a participant in this conversation');
            }
            
            $stmt = $db->prepare("
                INSERT INTO messages (conversation_id, sender_id, content, message_type, attachment_url, attachment_name, attachment_size)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$conversation_id, $user_id, $content, $message_type, $attachment_url, $attachment_name, $attachment_size]);
            $message_id = $db->lastInsertId();
            
            $stmt = $db->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?");
            $stmt->execute([$conversation_id]);
            
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
                SELECT user_id, 'message', ?, ?, 'message', ?
                FROM conversation_participants
                WHERE conversation_id = ? AND user_id != ?
            ");
            $senderName = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
            $title = "New message from " . $senderName;
            $stmt->execute([$title, $content, $message_id, $conversation_id, $user_id]);
            
            sendResponse(200, ['message_id' => $message_id], 'Message sent successfully');
            break;
        
        case 'get_conversations':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $sort = $_GET['sort'] ?? 'recent';
            $order = $sort === 'oldest' ? 'ASC' : 'DESC';
            
            try {
                // Check if tables exist first (simple check)
                try {
                    $db->query("SELECT 1 FROM conversations LIMIT 1");
                    $db->query("SELECT 1 FROM conversation_participants LIMIT 1");
                } catch (PDOException $e) {
                    // Tables don't exist, return empty array
                    sendResponse(200, ['conversations' => []]);
                    break;
                }
                
                // First, get all conversations for this user
                $stmt = $db->prepare("
                    SELECT 
                        c.id,
                        c.type,
                        COALESCE(c.name, '') as name,
                        c.created_at,
                        c.updated_at
                    FROM conversations c
                    INNER JOIN conversation_participants cp ON c.id = cp.conversation_id
                    WHERE cp.user_id = ?
                    ORDER BY COALESCE(c.updated_at, c.created_at) $order
                ");
                $stmt->execute([$user_id]);
                $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // If no conversations, return empty array
                if (empty($conversations)) {
                    sendResponse(200, ['conversations' => []]);
                    break;
                }
                
                // For each conversation, get additional details
                $conversationIds = array_column($conversations, 'id');
                $placeholders = str_repeat('?,', count($conversationIds) - 1) . '?';
                
                foreach ($conversations as &$conv) {
                    // Get other user info for direct conversations
                    if ($conv['type'] === 'direct') {
                        $stmt = $db->prepare("
                            SELECT u.id, u.username, u.full_name, u.avatar_url, u.is_online, u.last_seen
                            FROM conversation_participants cp
                            JOIN user u ON cp.user_id = u.id
                            WHERE cp.conversation_id = ? AND cp.user_id != ?
                            LIMIT 1
                        ");
                        $stmt->execute([$conv['id'], $user_id]);
                        $otherUser = $stmt->fetch();
                        
                        if ($otherUser) {
                            $conv['other_username'] = $otherUser['username'];
                            $conv['other_full_name'] = $otherUser['full_name'];
                            $conv['other_avatar'] = $otherUser['avatar_url'];
                            $conv['is_online'] = $otherUser['is_online'];
                            $conv['last_seen'] = $otherUser['last_seen'];
                        } else {
                            $conv['other_username'] = null;
                            $conv['other_full_name'] = null;
                            $conv['other_avatar'] = null;
                            $conv['is_online'] = 0;
                            $conv['last_seen'] = null;
                        }
                    }
                    
                    // Get last message (with error handling)
                    try {
                        $stmt = $db->prepare("
                            SELECT content, created_at
                            FROM messages
                            WHERE conversation_id = ?
                            ORDER BY created_at DESC
                            LIMIT 1
                        ");
                        $stmt->execute([$conv['id']]);
                        $lastMessage = $stmt->fetch();
                        
                        $conv['last_message'] = $lastMessage ? $lastMessage['content'] : null;
                        $conv['last_message_time'] = $lastMessage ? $lastMessage['created_at'] : null;
                    } catch (PDOException $e) {
                        $conv['last_message'] = null;
                        $conv['last_message_time'] = null;
                    }
                    
                    // Get unread count (with error handling)
                    try {
                        $stmt = $db->prepare("
                            SELECT COUNT(*) as unread_count
                            FROM messages m
                            INNER JOIN conversation_participants cp ON m.conversation_id = cp.conversation_id
                            WHERE m.conversation_id = ? 
                                AND cp.user_id = ?
                                AND m.sender_id != ?
                                AND (cp.last_read_at IS NULL OR m.created_at > cp.last_read_at)
                        ");
                        $stmt->execute([$conv['id'], $user_id, $user_id]);
                        $unread = $stmt->fetch();
                        $conv['unread_count'] = $unread ? (int)$unread['unread_count'] : 0;
                    } catch (PDOException $e) {
                        $conv['unread_count'] = 0;
                    }
                }
                
                sendResponse(200, ['conversations' => $conversations]);
            } catch (PDOException $e) {
                error_log("Error in get_conversations: " . $e->getMessage());
                error_log("SQL Error Info: " . print_r($stmt->errorInfo(), true));
                // Return empty array instead of error if query fails
                sendResponse(200, ['conversations' => []]);
            }
            break;
        
        case 'get_messages':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $conversation_id = $_GET['conversation_id'] ?? 0;
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 50;
            $offset = ($page - 1) * $limit;
            
            $stmt = $db->prepare("SELECT id FROM conversation_participants WHERE conversation_id = ? AND user_id = ?");
            $stmt->execute([$conversation_id, $user_id]);
            if (!$stmt->fetch()) {
                sendResponse(403, null, 'Not a participant in this conversation');
            }
            
            $stmt = $db->prepare("
                SELECT 
                    m.id,
                    m.sender_id,
                    u.username,
                    u.full_name,
                    u.avatar_url,
                    m.content,
                    m.message_type,
                    m.attachment_url,
                    m.attachment_name,
                    m.attachment_size,
                    m.is_edited,
                    m.edited_at,
                    m.created_at,
                    m.updated_at
                FROM messages m
                JOIN user u ON m.sender_id = u.id
                WHERE m.conversation_id = ? AND m.is_deleted = FALSE
                ORDER BY m.created_at DESC
                LIMIT $limit OFFSET $offset
            ");
            $stmt->execute([$conversation_id]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $db->prepare("
                UPDATE conversation_participants 
                SET last_read_at = NOW() 
                WHERE conversation_id = ? AND user_id = ?
            ");
            $stmt->execute([$conversation_id, $user_id]);
            
            $stmt = $db->prepare("
                SELECT 
                    c.id,
                    c.type,
                    c.name,
                    c.created_by,
                    (
                        SELECT COUNT(*) 
                        FROM conversation_participants 
                        WHERE conversation_id = c.id
                    ) as member_count,
                    (
                        SELECT GROUP_CONCAT(
                            CONCAT(u.id, '|', u.username, '|', COALESCE(u.full_name, ''), '|', COALESCE(u.avatar_url, ''), '|', u.is_online)
                            SEPARATOR '|||'
                        )
                        FROM conversation_participants cp
                        JOIN user u ON cp.user_id = u.id
                        WHERE cp.conversation_id = c.id
                    ) as members
                FROM conversations c
                WHERE c.id = ?
            ");
            $stmt->execute([$conversation_id]);
            $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Convert members from GROUP_CONCAT string to array
            if (!empty($conversation['members'])) {
                $memberStrings = explode('|||', $conversation['members']);
                $conversation['members'] = array_map(function($str) {
                    $parts = explode('|', $str);
                    return [
                        'id' => $parts[0],
                        'username' => $parts[1],
                        'full_name' => $parts[2],
                        'avatar_url' => $parts[3],
                        'is_online' => $parts[4] == '1' || $parts[4] == 1
                    ];
                }, $memberStrings);
            } else {
                $conversation['members'] = [];
            }
            
            sendResponse(200, [
                'conversation' => $conversation,
                'messages' => array_reverse($messages),
                'page' => $page,
                'has_more' => count($messages) === $limit
            ]);
            break;
        
        case 'search_messages':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $keyword = $_GET['keyword'] ?? '';
            $conversation_id = $_GET['conversation_id'] ?? 0;
            
            if (empty($keyword)) {
                sendResponse(400, null, 'Search keyword is required');
            }
            
            $stmt = $db->prepare("SELECT id FROM conversation_participants WHERE conversation_id = ? AND user_id = ?");
            $stmt->execute([$conversation_id, $user_id]);
            if (!$stmt->fetch()) {
                sendResponse(403, null, 'Not a participant in this conversation');
            }
            
            $stmt = $db->prepare("
                SELECT 
                    m.id,
                    m.sender_id,
                    u.username,
                    u.full_name,
                    m.content,
                    m.created_at
                FROM messages m
                JOIN user u ON m.sender_id = u.id
                WHERE m.conversation_id = ? 
                    AND m.is_deleted = FALSE
                    AND m.content LIKE ?
                ORDER BY m.created_at DESC
                LIMIT 50
            ");
            $stmt->execute([$conversation_id, "%$keyword%"]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(200, ['messages' => $messages]);
            break;
        
        case 'get_online_status':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $conversation_id = $_GET['conversation_id'] ?? 0;
            
            $stmt = $db->prepare("SELECT id FROM conversation_participants WHERE conversation_id = ? AND user_id = ?");
            $stmt->execute([$conversation_id, $user_id]);
            if (!$stmt->fetch()) {
                sendResponse(403, null, 'Not a participant in this conversation');
            }
            
            $stmt = $db->prepare("
                SELECT 
                    u.id,
                    u.username,
                    u.full_name,
                    u.is_online,
                    u.last_seen
                FROM conversation_participants cp
                JOIN user u ON cp.user_id = u.id
                WHERE cp.conversation_id = ? AND cp.user_id != ?
            ");
            $stmt->execute([$conversation_id, $user_id]);
            $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(200, ['participants' => $participants]);
            break;
        
        case 'update_online_status':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $is_online = $data['is_online'] ?? true;
            
            $stmt = $db->prepare("UPDATE user SET is_online = ?, last_seen = NOW() WHERE id = ?");
            $stmt->execute([$is_online ? 1 : 0, $user_id]);
            
            sendResponse(200, null, 'Status updated');
            break;
        
        case 'delete_message':
            if ($method !== 'DELETE') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $message_id = $_GET['message_id'] ?? 0;
            
            $stmt = $db->prepare("SELECT sender_id FROM messages WHERE id = ?");
            $stmt->execute([$message_id]);
            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$message) {
                sendResponse(404, null, 'Message not found');
            }
            
            if ($message['sender_id'] != $user_id) {
                sendResponse(403, null, 'Not authorized to delete this message');
            }
            
            $stmt = $db->prepare("UPDATE messages SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$message_id]);
            
            sendResponse(200, null, 'Message deleted');
            break;
        
        case 'create_group_chat':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $name = $data['name'] ?? '';
            $member_ids = $data['member_ids'] ?? [];
            
            if (empty($name)) {
                sendResponse(400, null, 'Group name is required');
            }
            
            if (empty($member_ids)) {
                sendResponse(400, null, 'At least one member is required');
            }
            
            $stmt = $db->prepare("INSERT INTO conversations (type, name, created_by) VALUES ('group', ?, ?)");
            $stmt->execute([$name, $user_id]);
            $conversation_id = $db->lastInsertId();
            
            $stmt = $db->prepare("INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)");
            $stmt->execute([$conversation_id, $user_id]);
            
            foreach ($member_ids as $member_id) {
                $stmt->execute([$conversation_id, $member_id]);
            }
            
            sendResponse(200, ['conversation_id' => $conversation_id], 'Group chat created');
            break;
        
        case 'manage_group_members':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $conversation_id = $data['conversation_id'] ?? 0;
            $action = $data['action'] ?? '';
            $member_id = $data['member_id'] ?? 0;
            
            $stmt = $db->prepare("SELECT created_by FROM conversations WHERE id = ? AND created_by = ?");
            $stmt->execute([$conversation_id, $user_id]);
            if (!$stmt->fetch()) {
                sendResponse(403, null, 'Not authorized to manage members');
            }
            
            if ($action === 'add') {
                $stmt = $db->prepare("SELECT id FROM conversation_participants WHERE conversation_id = ? AND user_id = ?");
                $stmt->execute([$conversation_id, $member_id]);
                if ($stmt->fetch()) {
                    sendResponse(400, null, 'User is already a member');
                }
                
                $stmt = $db->prepare("INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)");
                $stmt->execute([$conversation_id, $member_id]);
                sendResponse(200, null, 'Member added');
            } elseif ($action === 'remove') {
                $stmt = $db->prepare("DELETE FROM conversation_participants WHERE conversation_id = ? AND user_id = ?");
                $stmt->execute([$conversation_id, $member_id]);
                sendResponse(200, null, 'Member removed');
            } else {
                sendResponse(400, null, 'Invalid action');
            }
            break;
        
        default:
            sendResponse(404, null, 'Action not found');
    }
    
} catch (PDOException $e) {
    error_log("Database error in messaging_endpoints.php: " . $e->getMessage());
    sendResponse(500, null, 'Ralat pangkalan data. Sila cuba lagi kemudian.');
} catch (Exception $e) {
    error_log("Error in messaging_endpoints.php: " . $e->getMessage());
    sendResponse(500, null, 'Ralat pelayan: ' . $e->getMessage());
}

