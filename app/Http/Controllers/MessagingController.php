<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessagingController extends Controller
{
    public function sendMessage(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'conversation_id' => 'required|integer',
            'content' => 'required_if:message_type,text|string',
            'message_type' => 'in:text,file,image,link,shared_post',
            'attachment_url' => 'nullable|string',
            'attachment_name' => 'nullable|string',
            'attachment_size' => 'nullable|integer',
        ]);
        
        $conversation = Conversation::findOrFail($request->conversation_id);
        
        // Check if user is participant
        $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
        if (!$isParticipant) {
            return response()->json([
                'status' => 403,
                'message' => 'Not a participant in this conversation',
            ], 403);
        }
        
        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => $user->id,
            'content' => $request->content ?? '',
            'message_type' => $request->message_type ?? 'text',
            'attachment_url' => $request->attachment_url,
            'attachment_name' => $request->attachment_name,
            'attachment_size' => $request->attachment_size,
        ]);
        
        // Update conversation timestamp
        $conversation->touch();
        
        // Broadcast the message event
        broadcast(new MessageSent($message))->toOthers();
        
        // Create notifications for other participants
        $otherParticipants = $conversation->participants()
            ->where('user_id', '!=', $user->id)
            ->get();
        
        foreach ($otherParticipants as $participant) {
            Notification::create([
                'user_id' => $participant->id,
                'type' => 'message',
                'title' => 'Mesej Baharu daripada ' . $user->username,
                'message' => $request->content ?? 'Menghantar lampiran',
                'related_type' => 'message',
                'related_id' => $message->id,
            ]);
        }
        
        return response()->json([
            'status' => 200,
            'message' => 'Message sent successfully',
            'data' => ['message_id' => $message->id],
        ]);
    }

    public function getConversations(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $sort = $request->get('sort', 'recent');
        $order = $sort === 'oldest' ? 'asc' : 'desc';
        $includeArchived = $request->boolean('include_archived', false);
        
        $query = Conversation::whereHas('participants', function ($q) use ($user, $includeArchived) {
            $q->where('user_id', $user->id);
            if (!$includeArchived) {
                $q->where(function ($subQ) {
                    $subQ->where('conversation_participant.is_archived', 0)
                        ->orWhereNull('conversation_participant.is_archived');
                });
            }
        });
        
        // Eager load participants and last messages to avoid N+1 queries
        $conversationIds = $query->pluck('id')->toArray();
        
        if (empty($conversationIds)) {
            $conversationIds = [0]; // Prevent empty IN clause
        }
        
        // Get message IDs that are deleted for this user
        $deletedMessageIds = DB::table('message_user_deleted')
            ->where('user_id', $user->id)
            ->pluck('message_id')
            ->toArray();
        
        // Get last messages for all conversations - optimized query
        $lastMessages = DB::table('message')
            ->select('conversation_id', 'content', 'created_at')
            ->whereIn('conversation_id', $conversationIds)
            ->where('is_deleted', false)
            ->whereNotIn('id', $deletedMessageIds ?: [0]) // Exclude messages deleted by user
            ->whereIn('id', function ($query) use ($conversationIds, $deletedMessageIds) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('message')
                    ->whereIn('conversation_id', $conversationIds)
                    ->where('is_deleted', false)
                    ->whereNotIn('id', $deletedMessageIds ?: [0])
                    ->groupBy('conversation_id');
            })
            ->get()
            ->keyBy('conversation_id');
        
        // Get unread counts for all conversations - optimized with LEFT JOIN
        $unreadCounts = DB::table('message')
            ->select('message.conversation_id', DB::raw('COUNT(*) as unread_count'))
            ->leftJoin('conversation_participant', function ($join) use ($user) {
                $join->on('conversation_participant.conversation_id', '=', 'message.conversation_id')
                     ->where('conversation_participant.user_id', '=', $user->id);
            })
            ->whereIn('message.conversation_id', $conversationIds)
            ->where('message.is_deleted', false)
            ->whereNotIn('message.id', $deletedMessageIds ?: [0]) // Exclude messages deleted by user
            ->where('message.sender_id', '!=', $user->id)
            ->where(function ($q) {
                $q->whereNull('conversation_participant.last_read_at')
                  ->orWhereRaw('message.created_at > conversation_participant.last_read_at');
            })
            ->groupBy('message.conversation_id')
            ->get()
            ->keyBy('conversation_id');
        
        $conversations = $query->with(['participants' => function ($q) use ($user) {
            $q->where('user_id', '!=', $user->id)
              ->select('user.id', 'user.username', 'user.full_name', 'user.avatar_url', 'user.is_online');
        }])
        ->orderBy('updated_at', $order)
        ->get();
        
        $result = [];
        $conversationUserIds = []; // Track user IDs that already have conversations
        
        foreach ($conversations as $conv) {
            $convData = [
                'id' => $conv->id,
                'type' => $conv->type,
                'name' => $conv->name ?? '',
                'created_by' => $conv->created_by,
                'created_at' => $conv->created_at,
                'updated_at' => $conv->updated_at,
            ];
            
            if ($conv->type === 'direct') {
                $otherUser = $conv->participants->first();
                if ($otherUser) {
                    $convData['other_username'] = $otherUser->username;
                    $convData['other_full_name'] = $otherUser->full_name;
                    $convData['other_avatar'] = $otherUser->avatar_url ? asset($otherUser->avatar_url) : null;
                    $convData['is_online'] = $otherUser->is_online;
                    $convData['other_user_id'] = $otherUser->id;
                    // Track that this user already has a conversation
                    $conversationUserIds[] = $otherUser->id;
                }
            } else {
                // For groups, just get count without loading all participants
                $convData['member_count'] = DB::table('conversation_participant')
                    ->where('conversation_id', $conv->id)
                    ->count();
            }
            
            // Get last message from pre-loaded data
            $lastMessage = $lastMessages->get($conv->id);
            $convData['last_message'] = $lastMessage ? $lastMessage->content : null;
            $convData['last_message_time'] = $lastMessage ? $lastMessage->created_at : null;
            
            // Get unread count from pre-loaded data
            $unreadData = $unreadCounts->get($conv->id);
            $convData['unread_count'] = $unreadData ? (int)$unreadData->unread_count : 0;
            
            $result[] = $convData;
        }
        
        // Sort results by updated_at or created_at
        usort($result, function ($a, $b) use ($order) {
            $timeA = $a['updated_at'] ?? $a['created_at'] ?? '1970-01-01';
            $timeB = $b['updated_at'] ?? $b['created_at'] ?? '1970-01-01';
            
            if ($timeA === null && $timeB === null) return 0;
            if ($timeA === null) return 1; // Friends without conversations go to end
            if ($timeB === null) return -1;
            
            $compare = strtotime($timeA) <=> strtotime($timeB);
            return $order === 'asc' ? $compare : -$compare;
        });
        
        return response()->json([
            'status' => 200,
            'data' => ['conversations' => $result],
        ]);
    }

    public function getMessages(Request $request, $conversationId)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $conversation = Conversation::findOrFail($conversationId);
        
        // Check if user is participant
        $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
        if (!$isParticipant) {
            return response()->json([
                'status' => 403,
                'message' => 'Not a participant in this conversation',
            ], 403);
        }
        
        $page = max(1, (int) $request->get('page', 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        // Get message IDs that are deleted for this user
        $deletedMessageIds = DB::table('message_user_deleted')
            ->where('user_id', $user->id)
            ->pluck('message_id')
            ->toArray();
        
        $messages = Message::where('conversation_id', $conversationId)
            ->where('is_deleted', false)
            ->whereNotIn('id', $deletedMessageIds ?: [0]) // Use [0] to prevent empty IN clause
            ->with('sender:id,username,full_name,avatar_url')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(function ($message) {
                // Flatten sender data into message object for easier frontend access
                return [
                    'id' => $message->id,
                    'conversation_id' => $message->conversation_id,
                    'sender_id' => $message->sender_id,
                    'content' => $message->content,
                    'message_type' => $message->message_type,
                    'attachment_url' => $message->attachment_url,
                    'attachment_name' => $message->attachment_name,
                    'attachment_size' => $message->attachment_size,
                    'is_deleted' => $message->is_deleted,
                    'is_edited' => isset($message->is_edited) ? (bool)$message->is_edited : false,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                    // Flatten sender data
                    'username' => $message->sender->username ?? null,
                    'full_name' => $message->sender->full_name ?? null,
                    'avatar_url' => $message->sender->avatar_url ? asset($message->sender->avatar_url) : null,
                ];
            })
            ->reverse()
            ->values();
        
        // Update last read timestamp
        DB::table('conversation_participant')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);
        
        // Get conversation details - only load members if it's a group chat
        $conversationData = [
            'id' => $conversation->id,
            'type' => $conversation->type,
            'name' => $conversation->name,
            'created_by' => $conversation->created_by,
        ];
        
        if ($conversation->type === 'group') {
            // For group chats, only load members if requested (lazy loading)
            $loadMembers = $request->boolean('load_members', false);
            
            if ($loadMembers) {
                $conversation->load(['participants' => function ($q) {
                    $q->select('user.id', 'user.username', 'user.full_name', 'user.avatar_url', 'user.is_online')
                      ->orderBy('user.username');
                }]);
                
                $conversationData['members'] = $conversation->participants->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'username' => $p->username,
                        'full_name' => $p->full_name,
                        'avatar_url' => $p->avatar_url ? asset($p->avatar_url) : null,
                        'is_online' => $p->is_online,
                    ];
                });
            }
            
            // Always get member count (cheap query)
            $conversationData['member_count'] = DB::table('conversation_participant')
                ->where('conversation_id', $conversationId)
                ->count();
        } else {
            // For direct messages, just get the other participant
            $otherParticipant = $conversation->participants()
                ->where('user_id', '!=', $user->id)
                ->select('user.id', 'user.username', 'user.full_name', 'user.avatar_url', 'user.is_online')
                ->first();
            
            if ($otherParticipant) {
                $conversationData['other_user_id'] = $otherParticipant->id;
                $conversationData['other_username'] = $otherParticipant->username;
                $conversationData['other_full_name'] = $otherParticipant->full_name;
                $conversationData['other_avatar'] = $otherParticipant->avatar_url ? asset($otherParticipant->avatar_url) : null;
                $conversationData['is_online'] = $otherParticipant->is_online;
            }
        }
        
        return response()->json([
            'status' => 200,
            'data' => [
                'conversation' => $conversationData,
                'messages' => $messages,
                'page' => $page,
                'has_more' => $messages->count() === $limit,
            ],
        ]);
    }

    public function searchMessages(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'conversation_id' => 'required|integer',
            'keyword' => 'required|string',
        ]);
        
        $conversation = Conversation::findOrFail($request->conversation_id);
        
        // Check if user is participant
        $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
        if (!$isParticipant) {
            return response()->json([
                'status' => 403,
                'message' => 'Not a participant in this conversation',
            ], 403);
        }
        
        // Get message IDs that are deleted for this user
        $deletedMessageIds = DB::table('message_user_deleted')
            ->where('user_id', $user->id)
            ->pluck('message_id')
            ->toArray();
        
        $messages = Message::where('conversation_id', $request->conversation_id)
            ->where('is_deleted', false)
            ->whereNotIn('id', $deletedMessageIds ?: [0]) // Use [0] to prevent empty IN clause
            ->where('content', 'like', '%' . $request->keyword . '%')
            ->with('sender:id,username,full_name,avatar_url')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($message) {
                // Flatten sender data into message object for easier frontend access
                return [
                    'id' => $message->id,
                    'conversation_id' => $message->conversation_id,
                    'sender_id' => $message->sender_id,
                    'content' => $message->content,
                    'message_type' => $message->message_type,
                    'attachment_url' => $message->attachment_url,
                    'attachment_name' => $message->attachment_name,
                    'attachment_size' => $message->attachment_size,
                    'is_deleted' => $message->is_deleted,
                    'is_edited' => isset($message->is_edited) ? (bool)$message->is_edited : false,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                    // Flatten sender data
                    'username' => $message->sender->username ?? null,
                    'full_name' => $message->sender->full_name ?? null,
                    'avatar_url' => $message->sender->avatar_url ? asset($message->sender->avatar_url) : null,
                ];
            });
        
        return response()->json([
            'status' => 200,
            'data' => ['messages' => $messages],
        ]);
    }

    public function updateOnlineStatus(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $user->update([
            'is_online' => $request->boolean('is_online', true),
            'last_seen' => now(),
        ]);
        
        return response()->json([
            'status' => 200,
            'message' => 'Status updated',
        ]);
    }

    public function deleteMessage($messageId)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $message = Message::findOrFail($messageId);
        
        if ($message->sender_id !== $user->id) {
            return response()->json([
                'status' => 403,
                'message' => 'Not authorized to delete this message',
            ], 403);
        }
        
        $message->update([
            'is_deleted' => true,
            'deleted_at' => now(),
        ]);
        
        return response()->json([
            'status' => 200,
            'message' => 'Message deleted',
        ]);
    }

    public function createGroupChat(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'integer|exists:user,id',
        ]);
        
        DB::beginTransaction();
        try {
            $conversation = Conversation::create([
                'type' => 'group',
                'name' => $request->name,
                'created_by' => $user->id,
            ]);
            
            // Add creator as participant
            $conversation->participants()->attach($user->id);
            
            // Add other members (avoid duplicates)
            $memberIds = array_unique($request->member_ids);
            foreach ($memberIds as $memberId) {
                if ($memberId != $user->id) {
                    $conversation->participants()->syncWithoutDetaching([$memberId]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Group chat created',
                'data' => ['conversation_id' => $conversation->id],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Failed to create group chat: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function manageGroupMembers(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'conversation_id' => 'required|integer',
            'action' => 'required|in:add,remove',
            'member_id' => 'required|integer|exists:user,id',
        ]);
        
        $conversation = Conversation::findOrFail($request->conversation_id);
        
        // Check if user is creator
        if ($conversation->created_by !== $user->id) {
            return response()->json([
                'status' => 403,
                'message' => 'Not authorized to manage members',
            ], 403);
        }
        
        if ($request->action === 'add') {
            if ($conversation->participants()->where('user_id', $request->member_id)->exists()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'User is already a member',
                ], 400);
            }
            
            $conversation->participants()->attach($request->member_id);
            
            return response()->json([
                'status' => 200,
                'message' => 'Member added',
            ]);
        } else {
            $conversation->participants()->detach($request->member_id);
            
            return response()->json([
                'status' => 200,
                'message' => 'Member removed',
            ]);
        }
    }

    public function archiveConversation(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'conversation_id' => 'required|integer',
            'archive' => 'boolean',
        ]);
        
        $conversation = Conversation::findOrFail($request->conversation_id);
        
        // Check if user is participant
        $participant = $conversation->participants()->where('user_id', $user->id)->first();
        if (!$participant) {
            return response()->json([
                'status' => 403,
                'message' => 'Not a participant in this conversation',
            ], 403);
        }
        
        DB::table('conversation_participant')
            ->where('conversation_id', $request->conversation_id)
            ->where('user_id', $user->id)
            ->update(['is_archived' => $request->boolean('archive', true) ? 1 : 0]);
        
        return response()->json([
            'status' => 200,
            'message' => $request->boolean('archive', true) ? 'Conversation archived' : 'Conversation unarchived',
        ]);
    }

    public function getArchivedConversations(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $conversations = Conversation::whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->where('is_archived', 1);
        })
        ->with(['participants' => function ($q) use ($user) {
            $q->where('user_id', '!=', $user->id);
        }])
        ->orderBy('updated_at', 'desc')
        ->get();
        
        $result = [];
        foreach ($conversations as $conv) {
            $convData = [
                'id' => $conv->id,
                'type' => $conv->type,
                'name' => $conv->name ?? '',
                'created_by' => $conv->created_by,
                'created_at' => $conv->created_at,
                'updated_at' => $conv->updated_at,
            ];
            
            if ($conv->type === 'direct') {
                $otherUser = $conv->participants->first();
                if ($otherUser) {
                    $convData['other_username'] = $otherUser->username;
                    $convData['other_full_name'] = $otherUser->full_name;
                    $convData['other_avatar'] = $otherUser->avatar_url ? asset($otherUser->avatar_url) : null;
                    $convData['is_online'] = $otherUser->is_online;
                }
            }
            
            // Get last message (excluding messages deleted by user)
            $deletedMessageIdsForConv = DB::table('message_user_deleted')
                ->where('user_id', $user->id)
                ->pluck('message_id')
                ->toArray();
            
            $lastMessage = $conv->messages()
                ->where('is_deleted', false)
                ->whereNotIn('id', $deletedMessageIdsForConv ?: [0])
                ->orderBy('created_at', 'desc')
                ->first();
            
            $convData['last_message'] = $lastMessage ? $lastMessage->content : null;
            $convData['last_message_time'] = $lastMessage ? $lastMessage->created_at : null;
            
            $result[] = $convData;
        }
        
        return response()->json([
            'status' => 200,
            'data' => ['conversations' => $result],
        ]);
    }

    public function getOrCreateDirectConversation(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'user_id' => 'required|integer|exists:user,id',
        ]);
        
        $otherUserId = (int) $request->user_id;
        
        if ($user->id === $otherUserId) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot create conversation with yourself',
            ], 400);
        }
        
        // Check if conversation already exists
        $existingConversation = Conversation::where('type', 'direct')
            ->whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereHas('participants', function ($q) use ($otherUserId) {
                $q->where('user_id', $otherUserId);
            })
            ->first();
        
        if ($existingConversation) {
            return response()->json([
                'status' => 200,
                'message' => 'Conversation already exists',
                'data' => ['conversation_id' => $existingConversation->id],
            ]);
        }
        
        // Create new direct conversation
        $conversation = Conversation::create([
            'type' => 'direct',
            'created_by' => $user->id,
        ]);
        
        // Add both users as participants
        DB::table('conversation_participant')->insert([
            ['conversation_id' => $conversation->id, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()],
            ['conversation_id' => $conversation->id, 'user_id' => $otherUserId, 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        return response()->json([
            'status' => 200,
            'message' => 'Conversation created',
            'data' => ['conversation_id' => $conversation->id],
        ]);
    }

    public function getAvailableUsers(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $search = $request->get('search', '');
        $limit = $request->get('limit', 50);

        // Get users from recent conversations (direct messages only)
        $recentUserIds = DB::table('conversation_participant')
            ->join('conversation', 'conversation_participant.conversation_id', '=', 'conversation.id')
            ->where('conversation_participant.user_id', $user->id)
            ->where('conversation.type', 'direct')
            ->where('conversation.updated_at', '>=', now()->subDays(30)) // Last 30 days
            ->select('conversation_participant.conversation_id')
            ->pluck('conversation_id');

        $recentUserIds = DB::table('conversation_participant')
            ->whereIn('conversation_id', $recentUserIds)
            ->where('user_id', '!=', $user->id)
            ->pluck('user_id')
            ->unique()
            ->toArray();

        // Build query
        $query = User::where('id', '!=', $user->id);

        // If search is provided, search by name or username
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Get users
        $usersQuery = $query->select('id', 'username', 'full_name', 'avatar_url', 'is_online');
        
        // Order by recent users first if there are any
        if (!empty($recentUserIds)) {
            $usersQuery->orderByRaw('FIELD(id, ' . implode(',', $recentUserIds) . ') DESC');
        }
        
        $users = $usersQuery->orderBy('full_name', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($u) use ($recentUserIds) {
                return [
                    'id' => $u->id,
                    'username' => $u->username,
                    'full_name' => $u->full_name,
                    'avatar_url' => $u->avatar_url,
                    'is_online' => $u->is_online ?? false,
                    'is_recent' => in_array($u->id, $recentUserIds),
                ];
            });

        return response()->json([
            'status' => 200,
            'data' => ['users' => $users],
        ]);
    }

    public function deleteConversation(Request $request, $conversationId)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $conversation = Conversation::findOrFail($conversationId);
        
        // Check if user is participant
        $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
        if (!$isParticipant) {
            return response()->json([
                'status' => 403,
                'message' => 'Not a participant in this conversation',
            ], 403);
        }

        // For group chats, only creator/admin can delete the whole group
        if ($conversation->type === 'group') {
            // Check if user is the creator
            $isCreator = $conversation->created_by === $user->id;
            
            // Check if user is admin (you can add additional admin check logic here)
            // For now, we'll only allow the creator to delete the group
            if (!$isCreator) {
                return response()->json([
                    'status' => 403,
                    'message' => 'Only group creator can delete the group',
                ], 403);
            }
        }

        DB::beginTransaction();
        try {
            // Remove user from participants (for direct chats, this effectively "deletes" it for the user)
            if ($conversation->type === 'direct') {
                $conversation->participants()->detach($user->id);
            } else {
                // For group chats, permanently delete all messages first
                // Use DB facade to ensure hard delete (not soft delete)
                DB::table('message')
                    ->where('conversation_id', $conversation->id)
                    ->delete();
                
                // Remove all participants
                $conversation->participants()->detach();
                
                // Delete the conversation (this will also cascade delete messages, but we already did it explicitly)
                $conversation->delete();
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => $conversation->type === 'group' ? 'Group deleted' : 'Conversation deleted',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Failed to delete conversation: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function clearAllMessages(Request $request, $conversationId)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $conversation = Conversation::findOrFail($conversationId);
        
        // Check if user is participant
        $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
        if (!$isParticipant) {
            return response()->json([
                'status' => 403,
                'message' => 'Not a participant in this conversation',
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Get all message IDs in this conversation
            $messageIds = DB::table('message')
                ->where('conversation_id', $conversationId)
                ->where('is_deleted', false)
                ->pluck('id')
                ->toArray();
            
            if (!empty($messageIds)) {
                // Mark all messages as deleted for this user only
                $insertData = array_map(function ($messageId) use ($user) {
                    return [
                        'message_id' => $messageId,
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $messageIds);
                
                // Use insertOrIgnore to avoid duplicates
                foreach ($insertData as $data) {
                    DB::table('message_user_deleted')->insertOrIgnore($data);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'All messages cleared for you',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Failed to clear messages: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function permanentlyDeleteMessage($messageId)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $message = Message::findOrFail($messageId);
        
        if ($message->sender_id !== $user->id) {
            return response()->json([
                'status' => 403,
                'message' => 'Not authorized to delete this message',
            ], 403);
        }
        
        $message->delete();
        
        return response()->json([
            'status' => 200,
            'message' => 'Message permanently deleted',
        ]);
    }

    public function renameGroup(Request $request, $conversationId)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $conversation = Conversation::findOrFail($conversationId);
        
        // Check if it's a group chat
        if ($conversation->type !== 'group') {
            return response()->json([
                'status' => 400,
                'message' => 'This is not a group chat',
            ], 400);
        }
        
        // Check if user is creator
        if ($conversation->created_by !== $user->id) {
            return response()->json([
                'status' => 403,
                'message' => 'Only group creator can rename the group',
            ], 403);
        }

        $conversation->update([
            'name' => $request->name,
        ]);
        
        return response()->json([
            'status' => 200,
            'message' => 'Group renamed successfully',
            'data' => ['name' => $conversation->name],
        ]);
    }

    protected function getCurrentUser()
    {
        return session('user_id') ? User::find(session('user_id')) : Auth::user();
    }
}

