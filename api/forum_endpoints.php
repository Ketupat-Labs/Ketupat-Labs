<?php

// Suppress any output before JSON
ob_start();

header('Content-Type: application/json');
require_once '../config/database.php';

// Session is started by database.php, but ensure it's available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sendResponse($status, $data, $message = '') {
    // Clear any output buffer to prevent PHP errors from breaking JSON
    ob_clean();
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
        case 'create_forum':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $title = $data['title'] ?? '';
            $description = $data['description'] ?? '';
            $category = $data['category'] ?? '';
            $tags = $data['tags'] ?? [];
            $visibility = $data['visibility'] ?? 'public';
            $class_id = $data['class_id'] ?? null;
            $start_date = $data['start_date'] ?? null;
            $end_date = $data['end_date'] ?? null;
            
            if (empty($title)) {
                sendResponse(400, null, 'Forum title is required');
            }
            
            if (strlen($description) < 20) {
                sendResponse(400, null, 'Description must be at least 20 characters');
            }

            // Validate class_id if visibility is 'class'
            if ($visibility === 'class' && empty($class_id)) {
                sendResponse(400, null, 'Class selection is required for class-only forum');
            }

            // Validate class access if provided
            if ($class_id) {
                $stmt = $db->prepare("SELECT teacher_id FROM classes WHERE id = ?");
                $stmt->execute([$class_id]);
                $class = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$class) {
                    sendResponse(400, null, 'Invalid class selected');
                }
                // Allow teacher or enrolled students to create forum for the class
                $stmt = $db->prepare("
                    SELECT 1 FROM classes c 
                    LEFT JOIN class_students cs ON c.id = cs.class_id AND cs.student_id = ?
                    WHERE c.id = ? AND (c.teacher_id = ? OR cs.student_id = ?)
                ");
                $stmt->execute([$user_id, $class_id, $user_id, $user_id]);
                if (!$stmt->fetch()) {
                    sendResponse(403, null, 'You do not have access to this class');
                }
            }
            
            $db->beginTransaction();
            
            try {
                $stmt = $db->prepare("
                    INSERT INTO forum (created_by, title, description, category, visibility, class_id, start_date, end_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $title, $description, $category, $visibility, $class_id, $start_date, $end_date]);
                $forum_id = $db->lastInsertId();
                
                foreach ($tags as $tag) {
                    if (strlen($tag) <= 50) {
                        $stmt = $db->prepare("INSERT INTO forum_tags (forum_id, tag_name) VALUES (?, ?)");
                        $stmt->execute([$forum_id, $tag]);
                    }
                }
                
                $stmt = $db->prepare("INSERT INTO forum_member (forum_id, user_id, role) VALUES (?, ?, 'admin')");
                $stmt->execute([$forum_id, $user_id]);
                
                // Update member count
                $stmt = $db->prepare("UPDATE forum SET member_count = member_count + 1 WHERE id = ?");
                $stmt->execute([$forum_id]);
                
                $db->commit();
                sendResponse(200, ['forum_id' => $forum_id], 'Forum created successfully');
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;
        
        case 'create_post':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $forum_id = $data['forum_id'] ?? 0;
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';
            $category = $data['category'] ?? null;
            $tags = $data['tags'] ?? [];
            $attachments = $data['attachments'] ?? [];
            $post_type = $data['post_type'] ?? 'post';
            $poll_option = $data['poll_option'] ?? [];
            
            if (empty($title)) {
                sendResponse(400, null, 'Title is required');
            }
            
            // Content is required for post and link types
            if (in_array($post_type, ['post', 'link']) && empty($content)) {
                sendResponse(400, null, 'Content is required');
            }
            
            // Poll must have at least 2 options
            if ($post_type === 'poll' && count($poll_option) < 2) {
                sendResponse(400, null, 'Poll must have at least 2 options');
            }
            
            $stmt = $db->prepare("SELECT id FROM forum_member WHERE forum_id = ? AND user_id = ?");
            $stmt->execute([$forum_id, $user_id]);
            if (!$stmt->fetch()) {
                sendResponse(403, null, 'Not a member of this forum');
            }
            
            $stmt = $db->prepare("
                SELECT id FROM muted_user 
                WHERE forum_id = ? AND user_id = ? 
                AND (muted_until IS NULL OR muted_until > NOW())
            ");
            $stmt->execute([$forum_id, $user_id]);
            if ($stmt->fetch()) {
                sendResponse(403, null, 'You are currently muted in this forum');
            }
            
            $db->beginTransaction();
            
            try {
                $stmt = $db->prepare("
                    INSERT INTO forum_post (forum_id, author_id, title, content, category, post_type)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$forum_id, $user_id, $title, $content, $category, $post_type]);
                $post_id = $db->lastInsertId();
                
                // Add tags
                foreach ($tags as $tag) {
                    $stmt = $db->prepare("INSERT INTO post_tags (post_id, tag_name) VALUES (?, ?)");
                    $stmt->execute([$post_id, $tag]);
                }
                
                // Add attachments
                foreach ($attachments as $attachment) {
                    $stmt = $db->prepare("
                        INSERT INTO post_attachment (post_id, file_url, file_name, file_type, file_size)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $post_id,
                        $attachment['url'],
                        $attachment['name'],
                        $attachment['type'] ?? '',
                        $attachment['size'] ?? 0
                    ]);
                }
                
                // Add poll options
                if ($post_type === 'poll') {
                    foreach ($poll_option as $option_text) {
                        $stmt = $db->prepare("INSERT INTO poll_option (post_id, option_text) VALUES (?, ?)");
                        $stmt->execute([$post_id, $option_text]);
                    }
                }
                
                $stmt = $db->prepare("UPDATE forum SET post_count = post_count + 1 WHERE id = ?");
                $stmt->execute([$forum_id]);
                
                $db->commit();
                sendResponse(200, ['post_id' => $post_id], 'Post created successfully');
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;
        
        case 'edit_post':
            if ($method !== 'PUT') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $post_id = $data['post_id'] ?? 0;
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';
            
            $stmt = $db->prepare("SELECT author_id FROM forum_post WHERE id = ? AND is_deleted = FALSE");
            $stmt->execute([$post_id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$post) {
                sendResponse(404, null, 'Post not found');
            }
            
            if ($post['author_id'] != $user_id) {
                sendResponse(403, null, 'Not authorized to edit this post');
            }
            
            $stmt = $db->prepare("
                UPDATE forum_post 
                SET title = ?, content = ?, is_edited = TRUE, edited_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$title, $content, $post_id]);
            
            sendResponse(200, null, 'Post updated');
            break;
        
        case 'delete_post':
            if ($method !== 'DELETE') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $post_id = $_GET['post_id'] ?? 0;
            
            $stmt = $db->prepare("
                SELECT fp.author_id, fp.forum_id, fm.role
                FROM forum_post fp
                LEFT JOIN forum_member fm ON fp.forum_id = fm.forum_id AND fm.user_id = ?
                WHERE fp.id = ? AND fp.is_deleted = FALSE
            ");
            $stmt->execute([$user_id, $post_id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$post) {
                sendResponse(404, null, 'Post not found');
            }
            
            if ($post['author_id'] != $user_id && !in_array($post['role'], ['admin', 'moderator'])) {
                sendResponse(403, null, 'Not authorized to delete this post');
            }
            
            $stmt = $db->prepare("UPDATE forum_post SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$post_id]);
            
            $stmt = $db->prepare("UPDATE forum SET post_count = post_count - 1 WHERE id = ?");
            $stmt->execute([$post['forum_id']]);
            
            sendResponse(200, null, 'Post deleted');
            break;
        
        case 'get_forums':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $category = $_GET['category'] ?? null;
            $tag = $_GET['tag'] ?? null;
            $sort = $_GET['sort'] ?? 'recent';
            $search = $_GET['search'] ?? '';
            
            switch ($sort) {
                case 'popular':
                    $order_by = 'f.post_count DESC, f.member_count DESC';
                    break;
                case 'name':
                    $order_by = 'f.title ASC';
                    break;
                default:
                    $order_by = 'f.created_at DESC';
                    break;
            }
            
            $query = "
                SELECT 
                    f.id,
                    f.title,
                    f.description,
                    f.category,
                    f.visibility,
                    f.status,
                    f.is_pinned,
                    f.member_count,
                    f.post_count,
                    f.created_at,
                    u.username as creator_username,
                    u.full_name as creator_name,
                    (
                        SELECT COUNT(*) FROM forum_tags WHERE forum_id = f.id
                    ) as tag_count,
                    (
                        SELECT GROUP_CONCAT(tag_name SEPARATOR ',')
                        FROM forum_tags
                        WHERE forum_id = f.id
                    ) as tags
                FROM forum f
                LEFT JOIN user u ON f.created_by = u.id
                WHERE f.status = 'active'
            ";
            
            $params = [];
            
            if ($category) {
                $query .= " AND f.category = ?";
                $params[] = $category;
            }
            
            if ($tag) {
                $query .= " AND EXISTS (
                    SELECT 1 FROM forum_tags WHERE forum_id = f.id AND tag_name = ?
                )";
                $params[] = $tag;
            }
            
            if ($search) {
                $query .= " AND MATCH(f.title, f.description) AGAINST(? IN NATURAL LANGUAGE MODE)";
                $params[] = $search;
            }
            
            $query .= " ORDER BY f.is_pinned DESC, $order_by";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $forums = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convert tags from comma-separated string to array
            foreach ($forums as &$forum) {
                if (!empty($forum['tags'])) {
                    $forum['tags'] = explode(',', $forum['tags']);
                } else {
                    $forum['tags'] = [];
                }
            }
            unset($forum);
            
            sendResponse(200, ['forums' => $forums]);
            break;
        
        case 'get_forum':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $category = $_GET['category'] ?? null;
            $tag = $_GET['tag'] ?? null;
            $sort = $_GET['sort'] ?? 'recent';
            $search = $_GET['search'] ?? '';
            
            switch ($sort) {
                case 'popular':
                    $order_by = 'f.post_count DESC, f.member_count DESC';
                    break;
                case 'name':
                    $order_by = 'f.title ASC';
                    break;
                default:
                    $order_by = 'f.created_at DESC';
                    break;
            }
            
            $query = "
                SELECT 
                    f.id,
                    f.title,
                    f.description,
                    f.category,
                    f.visibility,
                    f.status,
                    f.is_pinned,
                    f.member_count,
                    f.post_count,
                    f.created_at,
                    u.username as creator_username,
                    u.full_name as creator_name,
                    (
                        SELECT COUNT(*) FROM forum_tags WHERE forum_id = f.id
                    ) as tag_count,
                    (
                        SELECT GROUP_CONCAT(tag_name SEPARATOR ',')
                        FROM forum_tags
                        WHERE forum_id = f.id
                    ) as tags
                FROM forum f
                LEFT JOIN user u ON f.created_by = u.id
                INNER JOIN forum_member fm ON f.id = fm.forum_id AND fm.user_id = ?
                WHERE f.status = 'active'
            ";
            
            $params = [$user_id];
            
            if ($category) {
                $query .= " AND f.category = ?";
                $params[] = $category;
            }
            
            if ($tag) {
                $query .= " AND EXISTS (
                    SELECT 1 FROM forum_tags WHERE forum_id = f.id AND tag_name = ?
                )";
                $params[] = $tag;
            }
            
            if ($search) {
                $query .= " AND MATCH(f.title, f.description) AGAINST(? IN NATURAL LANGUAGE MODE)";
                $params[] = $search;
            }
            
            $query .= " ORDER BY f.is_pinned DESC, $order_by";
            
            // Ensure user_id is an integer
            $user_id = (int)$user_id;
            $params[0] = $user_id;
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $forums = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convert tags from comma-separated string to array
            foreach ($forums as &$forumItem) {
                if (!empty($forumItem['tags'])) {
                    $forumItem['tags'] = explode(',', $forumItem['tags']);
                } else {
                    $forumItem['tags'] = [];
                }
            }
            unset($forumItem);
            
            sendResponse(200, ['forum' => $forums]);
            break;
        
        case 'get_posts':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $forum_id = $_GET['forum_id'] ?? 0;
            $post_id = $_GET['post_id'] ?? 0;
            $post_ids = $_GET['post_ids'] ?? null;
            $tag = $_GET['tag'] ?? null;
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            $where_clause = "fp.is_deleted = FALSE";
            $params = [$user_id, $user_id, $user_id];
            
            if ($post_id) {
                $where_clause .= " AND fp.id = ?";
                $params[] = $post_id;
                $limit = 1;
                $offset = 0;
            } elseif ($post_ids) {
                // Support multiple post IDs (comma-separated)
                $post_id_array = array_filter(array_map('intval', explode(',', $post_ids)));
                if (!empty($post_id_array)) {
                    $placeholders = implode(',', array_fill(0, count($post_id_array), '?'));
                    $where_clause .= " AND fp.id IN ($placeholders)";
                    $params = array_merge($params, $post_id_array);
                    $limit = count($post_id_array);
                    $offset = 0;
                } else {
                    sendResponse(200, ['posts' => []]);
                    break;
                }
            } elseif ($forum_id) {
                $where_clause .= " AND fp.forum_id = ?";
                $params[] = $forum_id;
            }
            
            // Add filter for user's forum if no specific forum or post or post_ids
            if (!$post_id && !$forum_id && !$post_ids) {
                $where_clause .= " AND EXISTS (
                    SELECT 1 FROM forum_member fm WHERE fm.forum_id = fp.forum_id AND fm.user_id = ?
                )";
                $params[] = $user_id;
            }
            
            // Add filter for post tags
            if ($tag) {
                $where_clause .= " AND EXISTS (
                    SELECT 1 FROM post_tags pt WHERE pt.post_id = fp.id AND pt.tag_name = ?
                )";
                $params[] = $tag;
            }
            
            $stmt = $db->prepare("
                SELECT 
                    fp.id,
                    fp.forum_id,
                    fp.author_id,
                    fp.title,
                    fp.content,
                    fp.category,
                    fp.is_pinned,
                    fp.view_count,
                    fp.reply_count,
                    fp.reaction_count,
                    fp.is_edited,
                    fp.edited_at,
                    fp.created_at,
                    fp.updated_at,
                    f.title as forum_name,
                    u.username as author_username,
                    u.full_name as author_name,
                    u.avatar_url as author_avatar,
                    (
                        SELECT GROUP_CONCAT(
                            CONCAT(pa.id, '|', pa.file_url, '|', pa.file_name, '|', COALESCE(pa.file_type, ''), '|', COALESCE(pa.file_size, ''))
                            SEPARATOR '|||'
                        )
                        FROM post_attachment pa
                        WHERE pa.post_id = fp.id
                    ) as attachments,
                    (
                        SELECT GROUP_CONCAT(tag_name SEPARATOR ',')
                        FROM post_tags
                        WHERE post_id = fp.id
                    ) as tags,
                    (
                        SELECT COUNT(*) > 0
                        FROM saved_post
                        WHERE post_id = fp.id AND user_id = ?
                    ) as is_bookmarked,
                    (
                        SELECT COUNT(*) > 0
                        FROM reaction
                        WHERE target_type = 'post' AND target_id = fp.id AND user_id = ?
                    ) as user_reacted,
                    (
                        SELECT role
                        FROM forum_member
                        WHERE forum_id = fp.forum_id AND user_id = ?
                    ) as user_forum_role
                FROM forum_post fp
                JOIN user u ON fp.author_id = u.id
                JOIN forum f ON fp.forum_id = f.id
                WHERE $where_clause
                ORDER BY " . ($post_ids ? "FIELD(fp.id, " . implode(',', array_map('intval', explode(',', $post_ids))) . ")" : "fp.is_pinned DESC, fp.created_at DESC") . "
                LIMIT $limit OFFSET $offset
            ");
            $stmt->execute($params);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convert GROUP_CONCAT strings to arrays
            foreach ($posts as &$post) {
                // Parse attachments
                if (!empty($post['attachments'])) {
                    $attachmentStrings = explode('|||', $post['attachments']);
                    $post['attachments'] = array_map(function($str) {
                        $parts = explode('|', $str);
                        return [
                            'id' => $parts[0],
                            'url' => $parts[1],
                            'name' => $parts[2],
                            'type' => $parts[3],
                            'size' => $parts[4]
                        ];
                    }, $attachmentStrings);
                } else {
                    $post['attachments'] = [];
                }
                
                // Parse tags
                if (!empty($post['tags'])) {
                    $post['tags'] = json_encode(explode(',', $post['tags']));
                } else {
                    $post['tags'] = '[]';
                }
            }
            unset($post);
            
            if ($forum_id && !$post_id) {
                $stmt = $db->prepare("UPDATE forum_post SET view_count = view_count + 1 WHERE forum_id = ?");
                $stmt->execute([$forum_id]);
            }
            
            sendResponse(200, [
                'posts' => $posts,
                'page' => $page,
                'has_more' => count($posts) === $limit
            ]);
            break;
        
        case 'create_comment':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $post_id = $data['post_id'] ?? 0;
            $content = $data['content'] ?? '';
            $parent_id = $data['parent_id'] ?? null;
            
            if (empty($content)) {
                sendResponse(400, null, 'Comment content is required');
            }
            
            $db->beginTransaction();
            
            try {
                $stmt = $db->prepare("
                    INSERT INTO comment (post_id, author_id, parent_id, content)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$post_id, $user_id, $parent_id, $content]);
                $comment_id = $db->lastInsertId();
                
                $stmt = $db->prepare("UPDATE forum_post SET reply_count = reply_count + 1 WHERE id = ?");
                $stmt->execute([$post_id]);
                
                $stmt = $db->prepare("SELECT author_id, title FROM forum_post WHERE id = ?");
                $stmt->execute([$post_id]);
                $post = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($post['author_id'] != $user_id) {
                    $username = $_SESSION['username'] ?? 'Someone';
                    $stmt = $db->prepare("
                        INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
                        VALUES (?, 'comment', ?, ?, 'comment', ?)
                    ");
                    $stmt->execute([
                        $post['author_id'],
                        "New comment on your post",
                        $username . " commented on: " . $post['title'],
                        $comment_id
                    ]);
                }
                
                if ($parent_id) {
                    $stmt = $db->prepare("SELECT author_id FROM comment WHERE id = ?");
                    $stmt->execute([$parent_id]);
                    $parent_comment = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($parent_comment && $parent_comment['author_id'] != $user_id) {
                        $username = $_SESSION['username'] ?? 'Someone';
                        $stmt = $db->prepare("
                            INSERT INTO notifications (user_id, type, title, message, related_type, related_id)
                            VALUES (?, 'reply', ?, ?, 'comment', ?)
                        ");
                        $stmt->execute([
                            $parent_comment['author_id'],
                            "Reply to your comment",
                            $username . " replied to your comment",
                            $comment_id
                        ]);
                    }
                }
                
                $db->commit();
                sendResponse(200, ['comment_id' => $comment_id], 'Comment created successfully');
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;
        
        case 'get_comments':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $post_id = $_GET['post_id'] ?? 0;
            $sort = $_GET['sort'] ?? 'recent';
            $limit = $_GET['limit'] ?? 50;
            
            switch ($sort) {
                case 'popular':
                case 'top':
                    // Sort by reaction_count + reply_count (top comment)
                    $order_by = '(c.reaction_count + COALESCE((SELECT COUNT(*) FROM comment WHERE parent_id = c.id AND is_deleted = FALSE), 0)) DESC, c.reaction_count DESC, c.created_at DESC';
                    break;
                case 'oldest':
                    $order_by = 'c.created_at ASC';
                    break;
                default:
                    $order_by = 'c.created_at DESC';
                    break;
            }
            
            $stmt = $db->prepare("
                SELECT 
                    c.id,
                    c.parent_id,
                    c.content,
                    c.is_edited,
                    c.edited_at,
                    c.reaction_count,
                    c.created_at,
                    u.username as author_username,
                    u.full_name as author_name,
                    u.avatar_url as author_avatar,
                    (
                        SELECT COUNT(*)
                        FROM comment
                        WHERE parent_id = c.id AND is_deleted = FALSE
                    ) as reply_count,
                    (
                        SELECT COUNT(*) > 0
                        FROM reaction
                        WHERE target_type = 'comment' AND target_id = c.id AND user_id = ?
                    ) as user_reacted
                FROM comment c
                JOIN user u ON c.author_id = u.id
                WHERE c.post_id = ? AND c.is_deleted = FALSE AND c.parent_id IS NULL
                ORDER BY $order_by
                LIMIT $limit
            ");
            $stmt->execute([$user_id, $post_id]);
            $comment = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $tree = [];
            $indexed = [];
            
            foreach ($comment as $comment) {
                $indexed[$comment['id']] = $comment;
                $indexed[$comment['id']]['replies'] = [];
            }
            
            // Recursively load all replies (supports infinite nesting)
            if (!empty($indexed)) {
                // Use a queue-based approach for guaranteed recursion through all levels
                $queue = array_values(array_keys($indexed)); // Start with all top-level comment IDs
                $processed = []; // Track which comment IDs we've already queried for replies
                $iterations = 0;
                $max_iterations = 100; // Safety limit
                
                while (!empty($queue) && $iterations < $max_iterations) {
                    $iterations++;
                    
                    // Get all unprocessed IDs from queue (process them all in this iteration)
                    $current_ids = [];
                    foreach ($queue as $comment_id) {
                        if (!in_array($comment_id, $processed)) {
                            $current_ids[] = $comment_id;
                            $processed[] = $comment_id;
                        }
                    }
                    // Clear the queue - new items will be added during processing
                    $queue = [];
                    
                    if (empty($current_ids)) {
                        error_log("API: No unprocessed IDs in queue, breaking");
                        break;
                    }
                    
                    error_log("API: Iteration $iterations - Processing IDs: " . implode(',', $current_ids));
                    
                    $placeholders = implode(',', array_fill(0, count($current_ids), '?'));
                    
                    $reply_stmt = $db->prepare("
                        SELECT 
                            c.id,
                            c.parent_id,
                            c.content,
                            c.is_edited,
                            c.edited_at,
                            c.reaction_count,
                            c.created_at,
                            u.username as author_username,
                            u.full_name as author_name,
                            u.avatar_url as author_avatar,
                            (
                                SELECT COUNT(*)
                                FROM comment
                                WHERE parent_id = c.id AND is_deleted = FALSE
                            ) as reply_count,
                            (
                                SELECT COUNT(*) > 0
                                FROM reaction
                                WHERE target_type = 'comment' AND target_id = c.id AND user_id = ?
                            ) as user_reacted
                        FROM comment c
                        JOIN user u ON c.author_id = u.id
                        WHERE c.parent_id IN ($placeholders) AND c.is_deleted = FALSE
                        ORDER BY c.reaction_count DESC, c.created_at DESC
                    ");
                    $reply_params = array_merge([$user_id], $current_ids);
                    $reply_stmt->execute($reply_params);
                    $replies = $reply_stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Debug logging (can be removed later)
                    error_log("API: Querying replies for comment IDs: " . implode(',', $current_ids) . " - Found " . count($replies) . " replies");
                    if (!empty($replies)) {
                        foreach ($replies as $r) {
                            error_log("API: Found reply ID {$r['id']} with parent_id {$r['parent_id']}");
                        }
                    }
                    
                    // Process each reply
                    foreach ($replies as $reply) {
                        // Add to indexed if not already present
                        if (!isset($indexed[$reply['id']])) {
                            $indexed[$reply['id']] = $reply;
                            $indexed[$reply['id']]['replies'] = [];
                            // Always add this reply's ID to queue to check for its replies
                            $queue[] = $reply['id'];
                            error_log("API: Added reply ID {$reply['id']} to queue (parent: {$reply['parent_id']})");
                        }
                        
                        // Attach to parent
                        if (isset($indexed[$reply['parent_id']])) {
                            // Initialize replies array if needed
                            if (!isset($indexed[$reply['parent_id']]['replies'])) {
                                $indexed[$reply['parent_id']]['replies'] = [];
                            }
                            
                            // Check if already in parent's replies array
                            $exists = false;
                            foreach ($indexed[$reply['parent_id']]['replies'] as $existing_reply) {
                                if (isset($existing_reply['id']) && $existing_reply['id'] == $reply['id']) {
                                    $exists = true;
                                    break;
                                }
                            }
                            
                            if (!$exists) {
                                // Directly use the indexed version - this creates a reference
                                // that will update when we add nested replies to this reply
                                $indexed[$reply['parent_id']]['replies'][] = &$indexed[$reply['id']];
                                error_log("API: Attached reply ID {$reply['id']} to parent {$reply['parent_id']}, parent now has " . count($indexed[$reply['parent_id']]['replies']) . " replies");
                            }
                        }
                    }
                    
                    error_log("API: Queue after iteration $iterations: " . implode(',', $queue) . " (Processed: " . implode(',', $processed) . ")");
                }
            }
            
            // Helper function to recursively copy comment with all nested replies
            $deepCopyComment = function($indexed, $comment_id) use (&$deepCopyComment) {
                $comment = $indexed[$comment_id];
                $copy = $comment;
                $copy['replies'] = [];
                
                if (isset($comment['replies']) && is_array($comment['replies'])) {
                    foreach ($comment['replies'] as $reply) {
                        $copy['replies'][] = $deepCopyComment($indexed, $reply['id']);
                    }
                }
                
                return $copy;
            };
            
            // Build tree structure (only top-level comment) using deep copy
            foreach ($indexed as $comment_id => $comment) {
                if (!$comment['parent_id']) {
                    $tree[] = $deepCopyComment($indexed, $comment_id);
                }
            }
            
            // Debug: Check structure before JSON encoding
            $countAllReplies = function($c, &$count) use (&$countAllReplies) {
                if (isset($c['replies']) && is_array($c['replies'])) {
                    $count += count($c['replies']);
                    foreach ($c['replies'] as $r) {
                        $countAllReplies($r, $count);
                    }
                }
            };
            
            foreach ($tree as $comment) {
                $total_replies = 0;
                $countAllReplies($comment, $total_replies);
                error_log("API: Top comment {$comment['id']} has total nested replies: $total_replies");
                if (isset($comment['replies']) && count($comment['replies']) > 0) {
                    error_log("API: Top comment {$comment['id']} has " . count($comment['replies']) . " direct replies");
                    foreach ($comment['replies'] as $r1) {
                        error_log("API: Reply {$r1['id']} has " . (isset($r1['replies']) ? count($r1['replies']) : 0) . " nested replies");
                    }
                }
            }
            
            // Clean up reference issues by converting to JSON and back to break circular references
            $tree_json = json_encode($tree);
            if ($tree_json === false) {
                error_log("API: JSON encoding failed: " . json_last_error_msg());
            }
            $tree = json_decode($tree_json, true);
            
            sendResponse(200, ['comment' => $tree]);
            break;
        
        case 'add_reaction':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $target_type = $data['target_type'] ?? '';
            $target_id = $data['target_id'] ?? 0;
            $reaction_type = $data['reaction_type'] ?? 'like';
            
            if (!in_array($target_type, ['post', 'comment'])) {
                sendResponse(400, null, 'Invalid target type');
            }
            
            $stmt = $db->prepare("
                SELECT id, reaction_type FROM reaction 
                WHERE user_id = ? AND target_type = ? AND target_id = ?
            ");
            $stmt->execute([$user_id, $target_type, $target_id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                if ($existing['reaction_type'] === $reaction_type) {
                    $stmt = $db->prepare("DELETE FROM reaction WHERE id = ?");
                    $stmt->execute([$existing['id']]);
                    
                    $table_name = $target_type === 'post' ? 'forum_post' : 'comment';
                    $stmt = $db->prepare("UPDATE $table_name SET reaction_count = reaction_count - 1 WHERE id = ?");
                    $stmt->execute([$target_id]);
                    
                    sendResponse(200, null, 'Reaction removed');
                } else {
                    $stmt = $db->prepare("UPDATE reaction SET reaction_type = ? WHERE id = ?");
                    $stmt->execute([$reaction_type, $existing['id']]);
                    sendResponse(200, null, 'Reaction updated');
                }
            } else {
                $stmt = $db->prepare("
                    INSERT INTO reaction (user_id, target_type, target_id, reaction_type)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $target_type, $target_id, $reaction_type]);
                
                $table_name = $target_type === 'post' ? 'forum_post' : 'comment';
                $stmt = $db->prepare("UPDATE $table_name SET reaction_count = reaction_count + 1 WHERE id = ?");
                $stmt->execute([$target_id]);
                
                sendResponse(200, null, 'Reaction added');
            }
            break;
        
        case 'bookmark_post':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $post_id = $data['post_id'] ?? 0;
            $action = $data['action'] ?? 'add';
            
            if ($action === 'add') {
                try {
                    $stmt = $db->prepare("INSERT INTO saved_post (user_id, post_id) VALUES (?, ?)");
                    $stmt->execute([$user_id, $post_id]);
                    sendResponse(200, null, 'Post bookmarked');
                } catch (PDOException $e) {
                    if ($e->getCode() === '23000') {
                        sendResponse(400, null, 'Already bookmarked');
                    }
                    throw $e;
                }
            } else {
                $stmt = $db->prepare("DELETE FROM saved_post WHERE user_id = ? AND post_id = ?");
                $stmt->execute([$user_id, $post_id]);
                sendResponse(200, null, 'Bookmark removed');
            }
            break;
        
        case 'pin_post':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $post_id = $data['post_id'] ?? 0;
            $pin = $data['pin'] ?? true;
            
            $stmt = $db->prepare("
                SELECT fp.forum_id, fm.role
                FROM forum_post fp
                JOIN forum_member fm ON fp.forum_id = fm.forum_id
                WHERE fp.id = ? AND fm.user_id = ?
            ");
            $stmt->execute([$post_id, $user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || !in_array($result['role'], ['admin', 'moderator'])) {
                sendResponse(403, null, 'Not authorized to pin posts');
            }
            
            if ($pin) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM forum_post WHERE forum_id = ? AND is_pinned = TRUE");
                $stmt->execute([$result['forum_id']]);
                if ($stmt->fetchColumn() >= 5) {
                    sendResponse(400, null, 'Maximum 5 pinned posts allowed');
                }
            }
            
            $stmt = $db->prepare("UPDATE forum_post SET is_pinned = ? WHERE id = ?");
            $stmt->execute([$pin ? 1 : 0, $post_id]);
            
            sendResponse(200, null, $pin ? 'Post pinned' : 'Post unpinned');
            break;
        
        case 'mute_user':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $forum_id = $data['forum_id'] ?? 0;
            $target_user_id = $data['user_id'] ?? 0;
            $reason = $data['reason'] ?? '';
            $until = $data['until'] ?? null;
            
            $stmt = $db->prepare("
                SELECT role FROM forum_member 
                WHERE forum_id = ? AND user_id = ?
            ");
            $stmt->execute([$forum_id, $user_id]);
            $role = $stmt->fetchColumn();
            
            if (!in_array($role, ['admin', 'moderator'])) {
                sendResponse(403, null, 'Not authorized to mute user');
            }
            
            $stmt = $db->prepare("
                INSERT INTO muted_user (forum_id, user_id, muted_by, reason, muted_until)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    muted_by = VALUES(muted_by),
                    reason = VALUES(reason),
                    muted_until = VALUES(muted_until)
            ");
            $stmt->execute([$forum_id, $target_user_id, $user_id, $reason, $until]);
            
            sendResponse(200, null, 'User muted');
            break;
        
        case 'report_content':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $content_type = $data['content_type'] ?? '';
            $content_id = $data['content_id'] ?? 0;
            $reason = $data['reason'] ?? '';
            $description = $data['description'] ?? '';
            
            if (!in_array($content_type, ['post', 'comment', 'message'])) {
                sendResponse(400, null, 'Invalid content type');
            }
            
            $stmt = $db->prepare("
                SELECT id FROM reported_content 
                WHERE reporter_id = ? AND content_type = ? AND content_id = ?
            ");
            $stmt->execute([$user_id, $content_type, $content_id]);
            if ($stmt->fetch()) {
                sendResponse(400, null, 'Already reported');
            }
            
            $stmt = $db->prepare("
                INSERT INTO reported_content (reporter_id, content_type, content_id, reason, description)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $content_type, $content_id, $reason, $description]);
            
            sendResponse(200, null, 'Content reported');
            break;
        
        case 'get_classes':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            // Get classes where user is teacher or enrolled student
            $stmt = $db->prepare("
                SELECT DISTINCT
                    c.id,
                    c.name,
                    c.subject,
                    c.year,
                    c.created_at,
                    u.full_name as teacher_name,
                    CASE 
                        WHEN c.teacher_id = ? THEN 'teacher'
                        ELSE 'student'
                    END as user_role
                FROM classes c
                LEFT JOIN class_students cs ON c.id = cs.class_id AND cs.student_id = ?
                LEFT JOIN user u ON c.teacher_id = u.id
                WHERE c.teacher_id = ? OR cs.student_id = ?
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(200, ['classes' => $classes]);
            break;
        
        case 'get_forum_details':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $forum_id = $_GET['forum_id'] ?? 0;
            
            if (!$forum_id) {
                sendResponse(400, null, 'forum_id required');
            }
            
            // Get forum details with membership status
            $stmt = $db->prepare("
                SELECT 
                    f.id,
                    f.title,
                    f.description,
                    f.category,
                    f.visibility,
                    f.status,
                    f.is_pinned,
                    f.member_count,
                    f.post_count,
                    f.created_at,
                    fs.last_activity,
                    u.username as creator_username,
                    u.full_name as creator_name,
                    fm.id as is_member,
                    fm.role as user_role,
                    fm.is_favorite,
                    (
                        SELECT COUNT(*) > 0
                        FROM muted_user
                        WHERE forum_id = f.id AND user_id = ? AND (muted_until IS NULL OR muted_until > NOW())
                    ) as is_muted
                FROM forum f
                LEFT JOIN user u ON f.created_by = u.id
                LEFT JOIN forum_member fm ON f.id = fm.forum_id AND fm.user_id = ?
                LEFT JOIN forum_statistics fs ON f.id = fs.forum_id
                WHERE f.id = ?
            ");
            $stmt->execute([$user_id, $user_id, $forum_id]);
            $forum = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$forum) {
                sendResponse(404, null, 'Forum not found');
            }
            
            // Convert boolean values
            $forum['is_member'] = !empty($forum['is_member']);
            $forum['is_favorite'] = !empty($forum['is_favorite']);
            $forum['is_muted'] = !empty($forum['is_muted']);
            
            sendResponse(200, ['forum' => $forum]);
            break;
        
        case 'join_forum':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $forum_id = $data['forum_id'] ?? 0;
            
            if (!$forum_id) {
                sendResponse(400, null, 'forum_id required');
            }
            
            // Check if already a member
            $stmt = $db->prepare("SELECT id FROM forum_member WHERE forum_id = ? AND user_id = ?");
            $stmt->execute([$forum_id, $user_id]);
            if ($stmt->fetch()) {
                sendResponse(400, null, 'Already a member of this forum');
            }
            
            $db->beginTransaction();
            try {
                // Add user to forum
                $stmt = $db->prepare("INSERT INTO forum_member (forum_id, user_id, role) VALUES (?, ?, 'member')");
                $stmt->execute([$forum_id, $user_id]);
                
                // Update member count
                $stmt = $db->prepare("UPDATE forum SET member_count = member_count + 1 WHERE id = ?");
                $stmt->execute([$forum_id]);
                
                $db->commit();
                sendResponse(200, null, 'Joined forum successfully');
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;
        
        case 'leave_forum':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $forum_id = $data['forum_id'] ?? 0;
            
            if (!$forum_id) {
                sendResponse(400, null, 'forum_id required');
            }
            
            // Check if user is admin
            $stmt = $db->prepare("SELECT role FROM forum_member WHERE forum_id = ? AND user_id = ?");
            $stmt->execute([$forum_id, $user_id]);
            $role = $stmt->fetchColumn();
            
            if (!$role) {
                sendResponse(403, null, 'Not a member of this forum');
            }
            
            if ($role === 'admin') {
                sendResponse(403, null, 'Forum admins cannot leave. Please transfer admin rights first.');
            }
            
            $db->beginTransaction();
            try {
                // Remove user from forum
                $stmt = $db->prepare("DELETE FROM forum_member WHERE forum_id = ? AND user_id = ?");
                $stmt->execute([$forum_id, $user_id]);
                
                // Update member count
                $stmt = $db->prepare("UPDATE forum SET member_count = member_count - 1 WHERE id = ?");
                $stmt->execute([$forum_id]);
                
                $db->commit();
                sendResponse(200, null, 'Left forum successfully');
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;
        
        case 'mute_forum':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $forum_id = $data['forum_id'] ?? 0;
            
            if (!$forum_id) {
                sendResponse(400, null, 'forum_id required');
            }
            
            $stmt = $db->prepare("INSERT INTO muted_user (forum_id, user_id, muted_by) VALUES (?, ?, ?)");
            $stmt->execute([$forum_id, $user_id, $user_id]);
            
            sendResponse(200, null, 'Forum muted');
            break;
        
        case 'unmute_forum':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $forum_id = $data['forum_id'] ?? 0;
            
            if (!$forum_id) {
                sendResponse(400, null, 'forum_id required');
            }
            
            $stmt = $db->prepare("DELETE FROM muted_user WHERE forum_id = ? AND user_id = ?");
            $stmt->execute([$forum_id, $user_id]);
            
            sendResponse(200, null, 'Forum unmuted');
            break;
        
        case 'favorite_forum':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $forum_id = $data['forum_id'] ?? 0;
            
            if (!$forum_id) {
                sendResponse(400, null, 'forum_id required');
            }
            
            $stmt = $db->prepare("UPDATE forum_member SET is_favorite = TRUE WHERE forum_id = ? AND user_id = ?");
            $stmt->execute([$forum_id, $user_id]);
            
            sendResponse(200, null, 'Forum added to favorites');
            break;
        
        case 'unfavorite_forum':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $forum_id = $data['forum_id'] ?? 0;
            
            if (!$forum_id) {
                sendResponse(400, null, 'forum_id required');
            }
            
            $stmt = $db->prepare("UPDATE forum_member SET is_favorite = FALSE WHERE forum_id = ? AND user_id = ?");
            $stmt->execute([$forum_id, $user_id]);
            
            sendResponse(200, null, 'Forum removed from favorites');
            break;
        
        case 'get_forum_member':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $forum_id = $_GET['forum_id'] ?? 0;
            
            if (!$forum_id) {
                sendResponse(400, null, 'forum_id required');
            }
            
            // Check if user is admin
            $stmt = $db->prepare("SELECT role FROM forum_member WHERE forum_id = ? AND user_id = ?");
            $stmt->execute([$forum_id, $user_id]);
            $role = $stmt->fetchColumn();
            
            if ($role !== 'admin') {
                sendResponse(403, null, 'Only admins can view members');
            }
            
            $stmt = $db->prepare("
                SELECT 
                    fm.user_id,
                    fm.role,
                    u.username,
                    u.full_name
                FROM forum_member fm
                JOIN user u ON fm.user_id = u.id
                WHERE fm.forum_id = ?
                ORDER BY 
                    CASE fm.role
                        WHEN 'admin' THEN 1
                        WHEN 'moderator' THEN 2
                        WHEN 'member' THEN 3
                    END,
                    u.full_name ASC
            ");
            $stmt->execute([$forum_id]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(200, ['members' => $members]);
            break;
        
        case 'promote_member':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $forum_id = $data['forum_id'] ?? 0;
            $target_user_id = $data['user_id'] ?? 0;
            
            if (!$forum_id || !$target_user_id) {
                sendResponse(400, null, 'forum_id and user_id required');
            }
            
            // Check if user is admin
            $stmt = $db->prepare("SELECT role FROM forum_member WHERE forum_id = ? AND user_id = ?");
            $stmt->execute([$forum_id, $user_id]);
            $role = $stmt->fetchColumn();
            
            if ($role !== 'admin') {
                sendResponse(403, null, 'Only admins can promote members');
            }
            
            $stmt = $db->prepare("UPDATE forum_member SET role = 'admin' WHERE forum_id = ? AND user_id = ?");
            $stmt->execute([$forum_id, $target_user_id]);
            
            sendResponse(200, null, 'User promoted to admin');
            break;
        
        case 'remove_member':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $forum_id = $data['forum_id'] ?? 0;
            $target_user_id = $data['user_id'] ?? 0;
            
            if (!$forum_id || !$target_user_id) {
                sendResponse(400, null, 'forum_id and user_id required');
            }
            
            // Check if user is admin
            $stmt = $db->prepare("SELECT role FROM forum_member WHERE forum_id = ? AND user_id = ?");
            $stmt->execute([$forum_id, $user_id]);
            $role = $stmt->fetchColumn();
            
            if ($role !== 'admin') {
                sendResponse(403, null, 'Only admins can remove members');
            }
            
            // Check if target is admin
            $stmt = $db->prepare("SELECT role FROM forum_member WHERE forum_id = ? AND user_id = ?");
            $stmt->execute([$forum_id, $target_user_id]);
            $target_role = $stmt->fetchColumn();
            
            if ($target_role === 'admin') {
                sendResponse(403, null, 'Cannot remove admin members');
            }
            
            $db->beginTransaction();
            try {
                $stmt = $db->prepare("DELETE FROM forum_member WHERE forum_id = ? AND user_id = ?");
                $stmt->execute([$forum_id, $target_user_id]);
                
                $stmt = $db->prepare("UPDATE forum SET member_count = member_count - 1 WHERE id = ?");
                $stmt->execute([$forum_id]);
                
                $db->commit();
                sendResponse(200, null, 'Member removed');
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;
        
        case 'update_forum':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $forum_id = $data['forum_id'] ?? 0;
            $title = $data['title'] ?? '';
            $description = $data['description'] ?? '';
            
            if (!$forum_id) {
                sendResponse(400, null, 'forum_id required');
            }
            
            if (empty($title)) {
                sendResponse(400, null, 'Title is required');
            }
            
            // Check if user is admin
            $stmt = $db->prepare("SELECT role FROM forum_member WHERE forum_id = ? AND user_id = ?");
            $stmt->execute([$forum_id, $user_id]);
            $role = $stmt->fetchColumn();
            
            if ($role !== 'admin') {
                sendResponse(403, null, 'Only admins can update forum settings');
            }
            
            $stmt = $db->prepare("UPDATE forum SET title = ?, description = ? WHERE id = ?");
            $stmt->execute([$title, $description, $forum_id]);
            
            sendResponse(200, null, 'Forum updated successfully');
            break;
        
        case 'delete_forum':
            if ($method !== 'POST') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $forum_id = $data['forum_id'] ?? 0;
            
            if (!$forum_id) {
                sendResponse(400, null, 'forum_id required');
            }
            
            // Check if user is admin
            $stmt = $db->prepare("SELECT role FROM forum_member WHERE forum_id = ? AND user_id = ?");
            $stmt->execute([$forum_id, $user_id]);
            $role = $stmt->fetchColumn();
            
            if ($role !== 'admin') {
                sendResponse(403, null, 'Only admins can delete forum');
            }
            
            $stmt = $db->prepare("DELETE FROM forum WHERE id = ?");
            $stmt->execute([$forum_id]);
            
            sendResponse(200, null, 'Forum deleted successfully');
            break;
        
        case 'get_forum_tags':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $forum_id = $_GET['forum_id'] ?? 0;
            
            if (!$forum_id) {
                sendResponse(400, null, 'forum_id required');
            }
            
            $stmt = $db->prepare("
                SELECT tag_name
                FROM forum_tags
                WHERE forum_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$forum_id]);
            $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            sendResponse(200, ['tags' => $tags]);
            break;
        
        case 'get_comment_detail':
            if ($method !== 'GET') {
                sendResponse(405, null, 'Method not allowed');
            }
            
            $comment_id = $_GET['comment_id'] ?? 0;
            
            if (!$comment_id) {
                sendResponse(400, null, 'comment_id required');
            }
            
            // Get the main comment
            $stmt = $db->prepare("
                SELECT 
                    c.id,
                    c.post_id,
                    c.parent_id,
                    c.content,
                    c.is_edited,
                    c.edited_at,
                    c.reaction_count,
                    c.created_at,
                    u.username as author_username,
                    u.full_name as author_name,
                    u.avatar_url as author_avatar,
                    (
                        SELECT COUNT(*)
                        FROM comment
                        WHERE parent_id = c.id AND is_deleted = FALSE
                    ) as reply_count,
                    (
                        SELECT COUNT(*) > 0
                        FROM reaction
                        WHERE target_type = 'comment' AND target_id = c.id AND user_id = ?
                    ) as user_reacted,
                    fp.id as post_id,
                    fp.title as post_title,
                    fp.content as post_content
                FROM comment c
                JOIN user u ON c.author_id = u.id
                LEFT JOIN forum_post fp ON c.post_id = fp.id
                WHERE c.id = ? AND c.is_deleted = FALSE
            ");
            $stmt->execute([$user_id, $comment_id]);
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$comment) {
                sendResponse(404, null, 'Comment not found');
            }
            
            $comment['replies'] = [];
            $comment['post'] = [
                'id' => $comment['post_id'],
                'title' => $comment['post_title'],
                'content' => $comment['post_content']
            ];
            unset($comment['post_id'], $comment['post_title'], $comment['post_content']);
            
            // Recursively load all replies
            $indexed = [$comment['id'] => $comment];
            // Use a queue-based approach for guaranteed recursion through all levels
            $queue = [$comment['id']];
            $processed = [$comment['id']]; // Track which comment IDs we've already queried for replies
            $iterations = 0;
            $max_iterations = 100; // Safety limit
            
            while (!empty($queue) && $iterations < $max_iterations) {
                $iterations++;
                
                // Get next batch from queue that hasn't been processed yet
                $current_ids = [];
                foreach ($queue as $key => $comment_id) {
                    if (!in_array($comment_id, $processed)) {
                        $current_ids[] = $comment_id;
                        $processed[] = $comment_id;
                        unset($queue[$key]);
                    }
                    if (count($current_ids) >= 100) break;
                }
                
                // Re-index queue array after unsetting elements
                $queue = array_values($queue);
                
                if (empty($current_ids)) break;
                
                $placeholders = implode(',', array_fill(0, count($current_ids), '?'));
                
                $reply_stmt = $db->prepare("
                    SELECT 
                        c.id,
                        c.parent_id,
                        c.content,
                        c.is_edited,
                        c.edited_at,
                        c.reaction_count,
                        c.created_at,
                        u.username as author_username,
                        u.full_name as author_name,
                        u.avatar_url as author_avatar,
                        (
                            SELECT COUNT(*)
                            FROM comment
                            WHERE parent_id = c.id AND is_deleted = FALSE
                        ) as reply_count,
                        (
                            SELECT COUNT(*) > 0
                            FROM reaction
                            WHERE target_type = 'comment' AND target_id = c.id AND user_id = ?
                        ) as user_reacted
                    FROM comment c
                    JOIN user u ON c.author_id = u.id
                    WHERE c.parent_id IN ($placeholders) AND c.is_deleted = FALSE
                    ORDER BY c.reaction_count DESC, c.created_at DESC
                ");
                $reply_params = array_merge([$user_id], $current_ids);
                $reply_stmt->execute($reply_params);
                $replies = $reply_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Process each reply
                foreach ($replies as $reply) {
                    // Add to indexed if not already present
                    if (!isset($indexed[$reply['id']])) {
                        $indexed[$reply['id']] = $reply;
                        $indexed[$reply['id']]['replies'] = [];
                        // Add this reply's ID to queue to check for its replies (only if not already processed)
                        if (!in_array($reply['id'], $processed)) {
                            $queue[] = $reply['id'];
                        }
                    }
                    
                    // Attach to parent
                    if (isset($indexed[$reply['parent_id']])) {
                        // Initialize replies array if needed
                        if (!isset($indexed[$reply['parent_id']]['replies'])) {
                            $indexed[$reply['parent_id']]['replies'] = [];
                        }
                        
                        // Check if already in parent's replies array
                        $exists = false;
                        foreach ($indexed[$reply['parent_id']]['replies'] as $existing_reply) {
                            if (isset($existing_reply['id']) && $existing_reply['id'] == $reply['id']) {
                                $exists = true;
                                break;
                            }
                        }
                        
                        if (!$exists) {
                            // Use the indexed version to ensure consistency
                            $indexed[$reply['parent_id']]['replies'][] = $indexed[$reply['id']];
                        }
                    }
                }
            }
            
            // Clean up reference issues by converting to JSON and back
            $clean_comment = json_decode(json_encode($indexed[$comment_id]), true);
            sendResponse(200, ['comment' => $clean_comment]);
            break;
        
        default:
            sendResponse(404, null, 'Action not found');
    }
    
} catch (Exception $e) {
    // Clear any output buffer
    ob_clean();
    sendResponse(500, null, 'Server error: ' . $e->getMessage());
}

