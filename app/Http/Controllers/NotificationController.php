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

    protected function getCurrentUser()
    {
        return session('user_id') ? \App\Models\User::find(session('user_id')) : \Illuminate\Support\Facades\Auth::user();
    }
}

