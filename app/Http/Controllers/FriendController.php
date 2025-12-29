<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Friend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    public function addFriend(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'friend_id' => 'required|integer|exists:user,id',
        ]);

        $friendId = (int) $request->friend_id;

        if ($user->id === $friendId) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot add yourself as a friend',
            ], 400);
        }

        // Check if target user allows friend requests
        $targetUser = User::find($friendId);
        if (!$targetUser) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ], 404);
        }

        if (isset($targetUser->allow_friend_requests) && !$targetUser->allow_friend_requests) {
            return response()->json([
                'status' => 403,
                'message' => 'This user does not accept friend requests',
            ], 403);
        }

        // Check if already friends
        if ($user->isFriendWith($friendId)) {
            return response()->json([
                'status' => 400,
                'message' => 'Already friends',
            ], 400);
        }

        // Check if request already exists
        $existingRequest = Friend::where(function ($q) use ($user, $friendId) {
            $q->where('user_id', $user->id)->where('friend_id', $friendId);
        })->orWhere(function ($q) use ($user, $friendId) {
            $q->where('user_id', $friendId)->where('friend_id', $user->id);
        })->first();

        if ($existingRequest) {
            if ($existingRequest->status === 'pending') {
                return response()->json([
                    'status' => 400,
                    'message' => 'Friend request already pending',
                ], 400);
            }
        }

        // Create friend request
        $friendRequest = Friend::create([
            'user_id' => $user->id,
            'friend_id' => $friendId,
            'status' => 'pending',
        ]);

        // Create notification for the friend
        \App\Models\Notification::create([
            'user_id' => $friendId,
            'type' => 'friend_request',
            'title' => 'New Friend Request',
            'message' => $user->full_name ?? $user->username . ' wants to be your friend',
            'related_type' => 'friend',
            'related_id' => $friendRequest->id,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Friend request sent',
        ]);
    }

    public function acceptFriend(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'friend_id' => 'required|integer|exists:user,id',
        ]);

        $friendId = (int) $request->friend_id;

        // Find friend request where the current user is the receiver (friend_id)
        // and the friendId is the sender (user_id)
        $friendRequest = Friend::where('user_id', $friendId)
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$friendRequest) {
            return response()->json([
                'status' => 404,
                'message' => 'Friend request not found',
            ], 404);
        }

        $friendRequest->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        // Mark related notification as read
        \App\Models\Notification::where('user_id', $user->id)
            ->where('type', 'friend_request')
            ->where('related_type', 'friend')
            ->where('related_id', $friendRequest->id)
            ->update(['is_read' => true, 'read_at' => now()]);

        // Create notification for the requester that their request was accepted
        \App\Models\Notification::create([
            'user_id' => $friendId,
            'type' => 'friend_accepted',
            'title' => 'Friend Request Accepted',
            'message' => $user->full_name ?? $user->username . ' accepted your friend request',
            'related_type' => 'friend',
            'related_id' => $friendRequest->id,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Friend request accepted',
        ]);
    }

    public function removeFriend(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'friend_id' => 'required|integer|exists:user,id',
        ]);

        $friendId = (int) $request->friend_id;

        Friend::where(function ($q) use ($user, $friendId) {
            $q->where('user_id', $user->id)->where('friend_id', $friendId);
        })->orWhere(function ($q) use ($user, $friendId) {
            $q->where('user_id', $friendId)->where('friend_id', $user->id);
        })->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Friend removed',
        ]);
    }

    public function getFriends(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $friends = DB::table('friend')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'accepted');
            })
            ->orWhere(function ($q) use ($user) {
                $q->where('friend_id', $user->id)->where('status', 'accepted');
            })
            ->get()
            ->map(function ($f) use ($user) {
                $friendId = $f->user_id === $user->id ? $f->friend_id : $f->user_id;
                $friend = User::find($friendId);
                return [
                    'id' => $friend->id,
                    'username' => $friend->username,
                    'full_name' => $friend->full_name,
                    'avatar_url' => $friend->avatar_url,
                    'is_online' => $friend->is_online ?? false,
                ];
            });

        return response()->json([
            'status' => 200,
            'data' => ['friends' => $friends],
        ]);
    }

    public function getFriendRequest(Request $request, $requestId)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $friendRequest = Friend::find($requestId);
        
        if (!$friendRequest) {
            return response()->json([
                'status' => 404,
                'message' => 'Friend request not found',
            ], 404);
        }

        // Determine which user sent the request
        $senderId = $friendRequest->user_id;
        $receiverId = $friendRequest->friend_id;

        return response()->json([
            'status' => 200,
            'data' => [
                'user_id' => $senderId, // The user who sent the request
                'friend_id' => $receiverId, // The user who received the request
                'status' => $friendRequest->status,
            ],
        ]);
    }

    public function index()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return redirect()->route('login');
        }

        // Get accepted friends
        $friends = DB::table('friend')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'accepted');
            })
            ->orWhere(function ($q) use ($user) {
                $q->where('friend_id', $user->id)->where('status', 'accepted');
            })
            ->get()
            ->map(function ($f) use ($user) {
                $friendId = $f->user_id === $user->id ? $f->friend_id : $f->user_id;
                return User::find($friendId);
            })
            ->filter()
            ->values();

        // Get pending friend requests (received)
        $pendingRequests = DB::table('friend')
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->get()
            ->map(function ($f) {
                return User::find($f->user_id);
            })
            ->filter()
            ->values();

        // Get sent friend requests
        $sentRequests = DB::table('friend')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->get()
            ->map(function ($f) {
                return User::find($f->friend_id);
            })
            ->filter()
            ->values();

        return view('friends.index', compact('friends', 'pendingRequests', 'sentRequests'));
    }

    protected function getCurrentUser()
    {
        return session('user_id') ? User::find(session('user_id')) : \Illuminate\Support\Facades\Auth::user();
    }
}
