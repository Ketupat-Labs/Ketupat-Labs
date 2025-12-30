<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $query = Notification::where('user_id', $user->id);
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->boolean('unread_only')) {
            $query->where('is_read', false);
        }
        
        $page = max(1, (int) $request->get('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $notifications = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();
        
        // Count all unread notifications
        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
        
        return response()->json([
            'status' => 200,
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
                'page' => $page,
                'has_more' => $notifications->count() === $limit,
            ],
        ]);
    }

    public function markRead(Request $request, $id = null)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        if ($request->boolean('mark_all')) {
            Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            
            return response()->json([
                'status' => 200,
                'message' => 'All notifications marked as read',
            ]);
        }
        
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$notification) {
            return response()->json([
                'status' => 404,
                'message' => 'Notification not found',
            ], 404);
        }
        
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
        
        return response()->json([
            'status' => 200,
            'message' => 'Notification marked as read',
        ]);
    }

    public function destroy($id)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$notification) {
            return response()->json([
                'status' => 404,
                'message' => 'Notification not found',
            ], 404);
        }
        
        $notification->delete();
        
        return response()->json([
            'status' => 200,
            'message' => 'Notification deleted',
        ]);
    }

    public function clearAll()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        Notification::where('user_id', $user->id)->delete();
        
        return response()->json([
            'status' => 200,
            'message' => 'All notifications cleared',
        ]);
    }

    public function getRedirectUrl($id)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$notification) {
            return response()->json([
                'status' => 404,
                'message' => 'Notification not found',
            ], 404);
        }
        
        $redirectUrl = null;
        
        // Determine redirect URL based on notification type and related_type
        switch ($notification->type) {
            case 'message':
                // For messages, try to get conversation ID from message
                if ($notification->related_type === 'message' && $notification->related_id) {
                    $message = \App\Models\Message::find($notification->related_id);
                    if ($message && $message->conversation_id) {
                        // Redirect to messaging page - the frontend can handle opening the conversation
                        $redirectUrl = route('messaging.index') . '#conversation-' . $message->conversation_id;
                    } else {
                        $redirectUrl = route('messaging.index');
                    }
                } else {
                    $redirectUrl = route('messaging.index');
                }
                break;
                
            case 'forum_post':
            case 'comment':
            case 'reply':
            case 'mention':
            case 'post_moderation':
                // For forum-related notifications, redirect to the post
                if ($notification->related_type === 'post' && $notification->related_id) {
                    $redirectUrl = route('forum.post.detail', ['id' => $notification->related_id]);
                } elseif ($notification->related_type === 'comment' && $notification->related_id) {
                    // For comments, we need to get the post_id from the comment
                    $comment = \App\Models\Comment::find($notification->related_id);
                    if ($comment && $comment->post_id) {
                        $redirectUrl = route('forum.post.detail', ['id' => $comment->post_id]);
                    } else {
                        // Fallback to forum index if comment not found
                        $redirectUrl = route('forum.index');
                    }
                } else {
                    // Fallback to forum index
                    $redirectUrl = route('forum.index');
                }
                break;
                
            case 'report':
            case 'report_status':
                // For reports, redirect to the post
                if ($notification->related_type === 'post' && $notification->related_id) {
                    $redirectUrl = route('forum.post.detail', ['id' => $notification->related_id]);
                } else {
                    $redirectUrl = route('forum.index');
                }
                break;
                
            case 'lesson_assigned':
            case 'lesson_completed':
                // For lesson notifications, redirect to the lesson
                if ($notification->related_type === 'lesson' && $notification->related_id) {
                    $redirectUrl = route('lesson.show', ['lesson' => $notification->related_id]);
                } else {
                    $redirectUrl = route('lesson.index');
                }
                break;
                
            case 'activity_assigned':
            case 'activity_completed':
            case 'activity_submission':
            case 'activity_graded':
                // For activity notifications, redirect to the activity
                if ($notification->related_type === 'activity' && $notification->related_id) {
                    $redirectUrl = route('activities.show', ['activity' => $notification->related_id]);
                } else {
                    $redirectUrl = route('activities.index');
                }
                break;
                
            case 'badge_earned':
                // For badge notifications, redirect to badges page
                $redirectUrl = route('badges.my');
                break;
                
            case 'class_enrollment':
                // For class enrollment, redirect to dashboard (can be enhanced later with classroom detail page)
                $redirectUrl = route('dashboard');
                break;
                
            default:
                // Default to dashboard
                $redirectUrl = route('dashboard');
                break;
        }
        
        // Ensure we always have a redirect URL
        if (!$redirectUrl) {
            $redirectUrl = route('dashboard');
        }
        
        return response()->json([
            'status' => 200,
            'data' => [
                'redirect_url' => $redirectUrl,
            ],
        ]);
    }

    public function showAll(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Show all notifications without filtering
        $query = Notification::where('user_id', $user->id);
        
        // Pagination
        $perPage = 20;
        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
        
        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    protected function getCurrentUser()
    {
        return session('user_id') ? \App\Models\User::find(session('user_id')) : \Illuminate\Support\Facades\Auth::user();
    }
}

