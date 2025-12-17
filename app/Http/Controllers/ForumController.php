<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\ForumPost;
use App\Models\ForumTag;
use App\Models\PostAttachment;
use App\Models\PostTag;
use App\Models\Comment;
use App\Models\Reaction;
use App\Models\SavedPost;
use App\Models\MutedUser;
use App\Models\Notification;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ForumController extends Controller
{
    public function createForum(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:20',
            'category' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'visibility' => 'required|in:public,class,group',
            'class_id' => 'nullable|integer|required_if:visibility,class',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);
        
        // Validate class access if provided
        if ($request->visibility === 'class' && $request->class_id) {
            // Add class validation logic here if needed
        }
        
        DB::beginTransaction();
        try {
            $forum = Forum::create([
                'created_by' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'visibility' => $request->visibility,
                'class_id' => $request->class_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'member_count' => 1,
                'post_count' => 0,
            ]);
            
            // Add tags
            if ($request->tags) {
                foreach ($request->tags as $tag) {
                    if (strlen($tag) <= 50) {
                        ForumTag::create([
                            'forum_id' => $forum->id,
                            'tag_name' => $tag,
                        ]);
                    }
                }
            }
            
            // Add creator as admin member
            $forum->members()->attach($user->id, ['role' => 'admin']);
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Forum created successfully',
                'data' => ['forum_id' => $forum->id],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Failed to create forum: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getForums(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        // Get all forum IDs where the user is a member
        $userForumIds = DB::table('forum_member')
            ->where('user_id', $user->id)
            ->pluck('forum_id')
            ->toArray();
        
        // If search parameter is provided, show ALL forums (for discovery)
        // Otherwise, only show forums where the user is a member (for "My Forums" sidebar)
        $query = Forum::where('status', 'active')
            ->with(['creator:id,username,full_name', 'tags']);
        
        // Only filter by membership if NOT searching (for sidebar)
        if (!$request->search) {
            $query->whereIn('id', $userForumIds);
        }
        
        if ($request->category) {
            $query->where('category', $request->category);
        }
        
        if ($request->tag) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tag_name', $request->tag);
            });
        }
        
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'popular':
                $query->orderBy('post_count', 'desc')->orderBy('member_count', 'desc');
                break;
            case 'name':
                $query->orderBy('title', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        $forums = $query->get()->map(function ($forum) use ($userForumIds) {
            // Check if user is a member of this forum
            $isMember = in_array($forum->id, $userForumIds);
            
            return [
                'id' => $forum->id,
                'title' => $forum->title,
                'description' => $forum->description,
                'category' => $forum->category,
                'visibility' => $forum->visibility,
                'status' => $forum->status,
                'is_pinned' => $forum->is_pinned ?? false,
                'member_count' => $forum->member_count,
                'post_count' => $forum->post_count,
                'created_at' => $forum->created_at,
                'creator_username' => $forum->creator->username ?? null,
                'creator_name' => $forum->creator->full_name ?? null,
                'tags' => $forum->tags->pluck('tag_name')->toArray(),
                'is_member' => $isMember, // Add membership status for search results
            ];
        });
        
        return response()->json([
            'status' => 200,
            'data' => ['forums' => $forums],
        ]);
    }

    public function getForum($id)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $forum = Forum::with(['creator:id,username,full_name', 'tags'])
            ->findOrFail($id);
        
        // Check membership
        $isMember = $forum->members()->where('user_id', $user->id)->exists();
        $userRole = null;
        if ($isMember) {
            $member = $forum->members()->where('user_id', $user->id)->first();
            $userRole = $member->pivot->role ?? 'member';
        }
        
        return response()->json([
            'status' => 200,
            'data' => [
                'forum' => [
                    'id' => $forum->id,
                    'title' => $forum->title,
                    'description' => $forum->description,
                    'category' => $forum->category,
                    'visibility' => $forum->visibility,
                    'status' => $forum->status,
                    'created_by' => $forum->created_by,
                    'member_count' => $forum->member_count,
                    'post_count' => $forum->post_count,
                    'created_at' => $forum->created_at ? ($forum->created_at instanceof \Carbon\Carbon ? $forum->created_at->toDateTimeString() : (string) $forum->created_at) : null,
                    'creator_username' => $forum->creator->username ?? null,
                    'creator_name' => $forum->creator->full_name ?? null,
                    'tags' => $forum->tags->pluck('tag_name')->toArray(),
                    'is_member' => $isMember,
                    'user_role' => $userRole,
                    'is_muted' => false, // TODO: Implement mute functionality
                    'is_favorite' => false, // TODO: Implement favorite functionality
                ],
            ],
        ]);
    }

    public function createPost(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'forum_id' => 'required|integer|exists:forum,id',
            'title' => 'required|string|max:255',
            'content' => 'required_if:post_type,post,link|nullable|string',
            'category' => 'nullable|string',
            'tags' => 'nullable|array',
            'attachments' => 'nullable|array',
            'post_type' => 'required|in:post,link,poll',
            'poll_option' => 'required_if:post_type,poll|array|min:2',
        ]);
        
        $forum = Forum::findOrFail($request->forum_id);
        
        // Check if user is member
        $isMember = $forum->members()->where('user_id', $user->id)->exists();
        if (!$isMember) {
            return response()->json([
                'status' => 403,
                'message' => 'Not a member of this forum',
            ], 403);
        }
        
        // Check if user is muted
        $muted = MutedUser::where('forum_id', $request->forum_id)
            ->where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('muted_until')
                  ->orWhere('muted_until', '>', now());
            })
            ->exists();
        
        if ($muted) {
            return response()->json([
                'status' => 403,
                'message' => 'You are currently muted in this forum',
            ], 403);
        }
        
        DB::beginTransaction();
        try {
            // Map post_type to match database enum values
            $postTypeMap = [
                'post' => 'discussion',
                'link' => 'discussion',
                'poll' => 'discussion',
            ];
            $dbPostType = $postTypeMap[$request->post_type] ?? 'discussion';
            
            // Ensure post_type matches the ENUM values exactly
            $validPostTypes = ['discussion', 'question', 'announcement'];
            if (!in_array($dbPostType, $validPostTypes)) {
                $dbPostType = 'discussion';
            }
            $dbPostType = trim($dbPostType);
            
            $post = ForumPost::create([
                'forum_id' => $request->forum_id,
                'author_id' => $user->id,
                'title' => trim($request->title),
                'content' => $request->content ?? '',
                'category' => $request->category ? trim($request->category) : null,
                'post_type' => $dbPostType,
            ]);
            
            // Add tags
            if ($request->tags) {
                foreach ($request->tags as $tag) {
                    PostTag::create([
                        'post_id' => $post->id,
                        'tag_name' => $tag,
                    ]);
                }
            }
            
            // Add attachments
            if ($request->attachments) {
                foreach ($request->attachments as $attachment) {
                    PostAttachment::create([
                        'post_id' => $post->id,
                        'file_url' => $attachment['url'] ?? '',
                        'file_name' => $attachment['name'] ?? '',
                        'file_type' => $attachment['type'] ?? '',
                        'file_size' => $attachment['size'] ?? 0,
                    ]);
                }
            }
            
            // Add poll options
            if ($request->post_type === 'poll' && $request->poll_option) {
                foreach ($request->poll_option as $optionText) {
                    DB::table('poll_option')->insert([
                        'post_id' => $post->id,
                        'option_text' => $optionText,
                    ]);
                }
            }
            
            // Update forum post count
            $forum->increment('post_count');
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Post created successfully',
                'data' => ['post_id' => $post->id],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Failed to create post: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getPosts(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        // Get all forum IDs where the user is a member
        $userForumIds = DB::table('forum_member')
            ->where('user_id', $user->id)
            ->pluck('forum_id')
            ->toArray();
        
        // If specific forum is requested, check visibility and membership
        if ($request->forum_id) {
            $forum = Forum::find($request->forum_id);
            if (!$forum) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Forum not found',
                ], 404);
            }
            
            $isMember = in_array($request->forum_id, $userForumIds);
            
            // If forum is public, show all posts (even if not a member)
            if ($forum->visibility === 'public') {
                $query = ForumPost::with(['author:id,username,full_name,avatar_url', 'forum:id,title', 'attachments', 'tags'])
                    ->where('is_deleted', false)
                    ->where('forum_id', $request->forum_id);
            } else {
                // Private or class forum - require membership
                if (!$isMember) {
                    return response()->json([
                        'status' => 403,
                        'message' => 'You must join this forum to view posts',
                        'data' => [
                            'posts' => [],
                            'page' => 1,
                            'has_more' => false,
                            'requires_membership' => true,
                        ],
                    ], 403);
                }
                // User is a member, show posts
                $query = ForumPost::with(['author:id,username,full_name,avatar_url', 'forum:id,title', 'attachments', 'tags'])
                    ->where('is_deleted', false)
                    ->where('forum_id', $request->forum_id);
            }
        } else {
            // No specific forum - show posts from:
            // 1. All public forums
            // 2. Private/class forums where user is a member
            $publicForumIds = Forum::where('visibility', 'public')
                ->where('status', 'active')
                ->pluck('id')
                ->toArray();
            
            // Combine public forums and user's member forums
            $allowedForumIds = array_unique(array_merge($publicForumIds, $userForumIds));
            
            $query = ForumPost::with(['author:id,username,full_name,avatar_url', 'forum:id,title', 'attachments', 'tags'])
                ->where('is_deleted', false)
                ->whereIn('forum_id', $allowedForumIds);
        }
        
        if ($request->post_id) {
            $query->where('id', $request->post_id);
        }
        
        // Handle post_ids parameter (comma-separated list)
        if ($request->post_ids) {
            $postIds = explode(',', $request->post_ids);
            $postIds = array_filter(array_map('intval', $postIds));
            if (!empty($postIds)) {
                $query->whereIn('id', $postIds);
            }
        }
        
        if ($request->tag) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tag_name', $request->tag);
            });
        }
        
        $page = max(1, (int) $request->get('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $posts = $query->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(function ($post) use ($user) {
                // Get user's role in the forum
                $userForumRole = null;
                if ($post->forum_id) {
                    $forum = Forum::find($post->forum_id);
                    if ($forum) {
                        $member = $forum->members()->where('user_id', $user->id)->first();
                        if ($member) {
                            $userForumRole = $member->pivot->role ?? null;
                        }
                    }
                }
                
                // Get poll options if this is a poll post
                $pollOptions = [];
                if ($post->post_type === 'poll') {
                    $pollOptions = DB::table('poll_option')
                        ->where('post_id', $post->id)
                        ->get()
                        ->map(function ($option) {
                            return [
                                'id' => $option->id,
                                'text' => $option->option_text,
                                'vote_count' => DB::table('poll_vote')
                                    ->where('option_id', $option->id)
                                    ->count(),
                            ];
                        })
                        ->toArray();
                }
                
                return [
                    'id' => $post->id,
                    'forum_id' => $post->forum_id,
                    'author_id' => $post->author_id,
                    'title' => $post->title,
                    'content' => $post->content,
                    'post_type' => $post->post_type ?? 'post', // Include post_type
                    'category' => $post->category,
                    'is_pinned' => $post->is_pinned,
                    'view_count' => $post->view_count,
                    'reply_count' => $post->reply_count,
                    'is_edited' => $post->is_edited ?? false,
                    'edited_at' => $post->edited_at,
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at,
                    'forum_name' => $post->forum->title ?? null,
                    'author_username' => $post->author->username ?? null,
                    'author_name' => $post->author->full_name ?? null,
                    'author_avatar' => $post->author->avatar_url ?? null,
                    'user_forum_role' => $userForumRole,
                    'poll_options' => $pollOptions, // Include poll options for poll posts
                    'attachments' => $post->attachments->map(function ($att) {
                        // Convert file_url to absolute URL
                        $url = $att->file_url;
                        if (empty($url)) {
                            return [
                                'id' => $att->id,
                                'url' => '',
                                'name' => $att->file_name,
                                'type' => $att->file_type,
                                'size' => $att->file_size,
                            ];
                        }
                        
                        // If already absolute URL, return as-is
                        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                            return [
                                'id' => $att->id,
                                'url' => $url,
                                'name' => $att->file_name,
                                'type' => $att->file_type,
                                'size' => $att->file_size,
                            ];
                        }
                        
                        // Clean up the URL - remove any Material/ prefix that might have been added
                        $url = str_replace('/Material/', '/', $url);
                        $url = str_replace('Material/', '', $url);
                        
                        // If URL already starts with /uploads/, it's correct (direct public access)
                        if (str_starts_with($url, '/uploads/')) {
                            // URL is already correct for public/uploads/ directory
                            $url = $url;
                        } elseif (str_starts_with($url, '/storage/uploads/')) {
                            // Convert /storage/uploads/ to /uploads/ (files are in public/uploads/)
                            $url = str_replace('/storage/uploads/', '/uploads/', $url);
                        } elseif (str_contains($url, 'uploads/')) {
                            // If it's an uploads path, ensure it starts with /
                            $url = ltrim($url, '/');
                            // Remove storage/ prefix if present
                            $url = str_replace('storage/', '', $url);
                            $url = '/' . $url;
                        } elseif (str_starts_with($url, '/')) {
                            // Already absolute path, use as-is
                            $url = $url;
                        } else {
                            // Relative path, make it absolute
                            $url = '/' . $url;
                        }
                        
                        return [
                            'id' => $att->id,
                            'url' => $url,
                            'name' => $att->file_name,
                            'type' => $att->file_type,
                            'size' => $att->file_size,
                        ];
                    }),
                    'tags' => $post->tags->pluck('tag_name')->toArray(),
                    'report_count' => $post->reports()->where('status', 'pending')->count(),
                    'is_forum_member' => $userForumRole !== null, // User is a member if they have a role
                    'reaction_count' => Reaction::where('target_type', 'post')
                        ->where('target_id', $post->id)
                        ->count(),
                    'is_bookmarked' => SavedPost::where('post_id', $post->id)
                        ->where('user_id', $user->id)
                        ->exists(),
                    'user_reacted' => Reaction::where('target_type', 'post')
                        ->where('target_id', $post->id)
                        ->where('user_id', $user->id)
                        ->exists(),
                ];
            });
        
        return response()->json([
            'status' => 200,
            'data' => [
                'posts' => $posts,
                'page' => $page,
                'has_more' => $posts->count() === $limit,
            ],
        ]);
    }

    public function editPost(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $post = ForumPost::findOrFail($id);
        
        if ($post->author_id !== $user->id) {
            return response()->json([
                'status' => 403,
                'message' => 'Not authorized to edit this post',
            ], 403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        
        $post->update([
            'title' => $request->title,
            'content' => $request->content,
            'is_edited' => true,
            'edited_at' => now(),
        ]);
        
        return response()->json([
            'status' => 200,
            'message' => 'Post updated',
        ]);
    }

    public function deletePost($id)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $post = ForumPost::findOrFail($id);
        
        // Check authorization
        $isAuthor = $post->author_id === $user->id;
        $isModerator = $post->forum->members()
            ->where('user.id', $user->id)
            ->whereIn('forum_member.role', ['admin', 'moderator'])
            ->exists();
        
        if (!$isAuthor && !$isModerator) {
            return response()->json([
                'status' => 403,
                'message' => 'Not authorized to delete this post',
            ], 403);
        }
        
        DB::beginTransaction();
        try {
            // Delete attachments (files and records)
            foreach ($post->attachments as $attachment) {
                // Delete file if exists
                $filePath = str_replace('/uploads/', 'uploads/', $attachment->file_url);
                $filePath = ltrim($filePath, '/');
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
            
            // Delete related records
            $post->attachments()->delete();
            $post->tags()->delete();
            DB::table('poll_option')->where('post_id', $post->id)->delete();
            DB::table('poll_vote')->where('post_id', $post->id)->delete();
            
            // Soft delete post
            $post->update([
                'is_deleted' => true,
                'deleted_at' => now(),
            ]);
            
            // Update forum post count
            $post->forum->decrement('post_count');
            
            // Notify post author if deleted by moderator (not by themselves)
            if (!$isAuthor && $isModerator && $post->author_id !== $user->id) {
                Notification::create([
                    'user_id' => $post->author_id,
                    'type' => 'post_moderation',
                    'title' => 'Post Deleted',
                    'message' => "Your post '{$post->title}' has been deleted by a forum moderator",
                    'related_type' => 'post',
                    'related_id' => $post->id,
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Post deleted',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Failed to delete post: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createComment(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'post_id' => 'required|integer|exists:forum_post,id',
            'content' => 'required|string',
            'parent_id' => 'nullable|integer|exists:comment,id',
        ]);
        
        DB::beginTransaction();
        try {
            $comment = Comment::create([
                'post_id' => $request->post_id,
                'author_id' => $user->id,
                'parent_id' => $request->parent_id,
                'content' => $request->content,
            ]);
            
            // Update post reply count
            ForumPost::where('id', $request->post_id)->increment('reply_count');
            
            // Create notifications
            $post = ForumPost::find($request->post_id);
            if ($post->author_id !== $user->id) {
                Notification::create([
                    'user_id' => $post->author_id,
                    'type' => 'comment',
                    'title' => 'New comment on your post',
                    'message' => $user->username . ' commented on: ' . $post->title,
                    'related_type' => 'comment',
                    'related_id' => $comment->id,
                ]);
            }
            
            if ($request->parent_id) {
                $parentComment = Comment::find($request->parent_id);
                if ($parentComment && $parentComment->author_id !== $user->id) {
                    Notification::create([
                        'user_id' => $parentComment->author_id,
                        'type' => 'reply',
                        'title' => 'Reply to your comment',
                        'message' => $user->username . ' replied to your comment',
                        'related_type' => 'comment',
                        'related_id' => $comment->id,
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Comment created successfully',
                'data' => ['comment_id' => $comment->id],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Failed to create comment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getComments(Request $request, $postId)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $sort = $request->get('sort', 'recent');
        $limit = min((int) $request->get('limit', 20), 100); // Default to 20 for pagination
        $offset = max(0, (int) $request->get('offset', 0));
        $topLimit = (int) $request->get('top_limit', 0); // For limited top comments display
        
        $query = Comment::where('post_id', $postId)
            ->where('is_deleted', false)
            ->whereNull('parent_id'); // Only get top-level comments for pagination
        
        switch ($sort) {
            case 'popular':
            case 'top':
                $query->orderByRaw('(reaction_count + (SELECT COUNT(*) FROM comment WHERE parent_id = comment.id AND is_deleted = FALSE)) DESC')
                      ->orderBy('reaction_count', 'desc')
                      ->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        // If top_limit is specified, only return that many top comments
        if ($topLimit > 0 && $offset === 0) {
            $query->limit($topLimit);
        } else {
            $query->limit($limit)->offset($offset);
        }
        
        // Load replies up to level 3 (to flatten level 3+ into level 2)
        $comments = $query->with([
            'author:id,username,full_name,avatar_url',
            'replies' => function ($query) {
                $query->where('is_deleted', false)
                      ->with(['author:id,username,full_name,avatar_url', 'replies' => function ($q) {
                          // Level 2: Load replies and their replies (level 3) to flatten them
                          $q->where('is_deleted', false)
                            ->with(['author:id,username,full_name,avatar_url', 'replies' => function ($q2) {
                                // Level 3: Load to process and flatten
                                $q2->where('is_deleted', false)
                                   ->with('author:id,username,full_name,avatar_url');
                            }]);
                      }]);
            }
        ])->get();
        
        // Sort replies within each comment based on the same sort order
        $sortType = $request->get('sort', 'recent');
        $comments = $comments->map(function ($comment) use ($user, $sortType) {
            $commentData = $comment->toArray();
            $commentData['author_id'] = $comment->author_id;
            // Map author data for easier access in frontend
            $commentData['author_name'] = $comment->author->full_name ?? $comment->author->username ?? 'Unknown';
            $commentData['author_username'] = $comment->author->username ?? 'Unknown';
            $commentData['author_avatar'] = $comment->author->avatar_url ?? null;
            $commentData['can_edit'] = $comment->author_id === $user->id;
            $commentData['can_delete'] = $comment->author_id === $user->id;
            
            // Recursive function to flatten all replies to level 1 with quotes
            // But keep nested replies separate for show/hide functionality
            $flattenReplies = function ($replies, $parentContent = null, $parentAuthor = null) use ($user, $sortType, &$flattenReplies) {
                if (!is_array($replies) || count($replies) === 0) {
                    return [];
                }
                
                $flattened = [];
                
                foreach ($replies as $reply) {
                    // Map author data
                    if (isset($reply['author'])) {
                        $reply['author_name'] = $reply['author']['full_name'] ?? $reply['author']['username'] ?? 'Unknown';
                        $reply['author_username'] = $reply['author']['username'] ?? 'Unknown';
                        $reply['author_avatar'] = $reply['author']['avatar_url'] ?? null;
                    }
                    $reply['can_edit'] = ($reply['author_id'] ?? null) === $user->id;
                    $reply['can_delete'] = ($reply['author_id'] ?? null) === $user->id;
                    
                    // Add quoted content from parent (always quote what you're replying to)
                    if ($parentContent && $parentAuthor) {
                        $reply['quoted_content'] = $parentContent;
                        $reply['quoted_author'] = $parentAuthor;
                    }
                    
                    // Store current reply info for nested replies before processing
                    $currentReplyContent = $reply['content'] ?? '';
                    $currentReplyAuthor = $reply['author_name'] ?? $reply['author_username'] ?? 'Unknown';
                    $nestedReplies = $reply['replies'] ?? [];
                    
                    // If this reply has nested replies, keep them separate for show/hide
                    if (is_array($nestedReplies) && count($nestedReplies) > 0) {
                        // Recursively flatten nested replies - ensure ALL nested levels are flattened
                        $nestedFlattened = [];
                        foreach ($nestedReplies as $nestedReply) {
                            // Process this nested reply
                            if (isset($nestedReply['author'])) {
                                $nestedReply['author_name'] = $nestedReply['author']['full_name'] ?? $nestedReply['author']['username'] ?? 'Unknown';
                                $nestedReply['author_username'] = $nestedReply['author']['username'] ?? 'Unknown';
                                $nestedReply['author_avatar'] = $nestedReply['author']['avatar_url'] ?? null;
                            }
                            $nestedReply['can_edit'] = ($nestedReply['author_id'] ?? null) === $user->id;
                            $nestedReply['can_delete'] = ($nestedReply['author_id'] ?? null) === $user->id;
                            $nestedReply['quoted_content'] = $currentReplyContent;
                            $nestedReply['quoted_author'] = $currentReplyAuthor;
                            
                            // Check if this nested reply has its own nested replies
                            $deepNestedReplies = $nestedReply['replies'] ?? [];
                            if (is_array($deepNestedReplies) && count($deepNestedReplies) > 0) {
                                // Recursively flatten deeper nested replies
                                $deepNestedContent = $nestedReply['content'] ?? '';
                                $deepNestedAuthor = $nestedReply['author_name'] ?? $nestedReply['author_username'] ?? 'Unknown';
                                $deepFlattened = $flattenReplies($deepNestedReplies, $deepNestedContent, $deepNestedAuthor);
                                
                                // Store deeper nested replies separately
                                $nestedReply['nested_replies'] = $deepFlattened;
                                $nestedReply['nested_replies_count'] = count($deepFlattened);
                            }
                            
                            // Remove nested structure
                            unset($nestedReply['replies']);
                            
                            // Add to flattened array
                            $nestedFlattened[] = $nestedReply;
                        }
                        
                        // Store nested replies separately (they'll be shown/hidden)
                        $reply['nested_replies'] = $nestedFlattened;
                        $reply['nested_replies_count'] = count($nestedFlattened);
                    }
                    
                    // Remove nested structure from main replies array
                    unset($reply['replies']);
                    
                    // Add this reply to flattened array
                    $flattened[] = $reply;
                }
                
                // Sort flattened replies based on sort type
                switch ($sortType) {
                    case 'popular':
                    case 'top':
                        usort($flattened, function ($a, $b) {
                            $scoreA = ($a['reaction_count'] ?? 0);
                            $scoreB = ($b['reaction_count'] ?? 0);
                            if ($scoreB !== $scoreA) return $scoreB <=> $scoreA;
                            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
                        });
                        break;
                    case 'oldest':
                        usort($flattened, function ($a, $b) {
                            return strtotime($a['created_at']) <=> strtotime($b['created_at']);
                        });
                        break;
                    default: // recent
                        usort($flattened, function ($a, $b) {
                            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
                        });
                }
                
                return $flattened;
            };
            
            // Flatten all replies to level 1 with quotes
            if (isset($commentData['replies']) && is_array($commentData['replies']) && count($commentData['replies']) > 0) {
                $parentContent = $commentData['content'] ?? '';
                $parentAuthor = $commentData['author_name'] ?? $commentData['author_username'] ?? 'Unknown';
                $commentData['replies'] = $flattenReplies($commentData['replies'], $parentContent, $parentAuthor);
            }
            
            return $commentData;
        });
        
        // Get total count for pagination
        $totalCount = Comment::where('post_id', $postId)
            ->where('is_deleted', false)
            ->whereNull('parent_id')
            ->count();
        
        return response()->json([
            'status' => 200,
            'data' => [
                'comments' => $comments,
                'total' => $totalCount,
                'has_more' => ($offset + $comments->count()) < $totalCount,
                'offset' => $offset,
                'limit' => $limit,
            ],
        ]);
    }

    public function editComment(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $comment = Comment::findOrFail($id);
        
        // Check authorization - only author can edit
        if ($comment->author_id !== $user->id) {
            return response()->json([
                'status' => 403,
                'message' => 'Not authorized to edit this comment',
            ], 403);
        }
        
        $request->validate([
            'content' => 'required|string|min:1',
        ]);
        
        $comment->update([
            'content' => $request->content,
            'is_edited' => true,
            'edited_at' => now(),
        ]);
        
        return response()->json([
            'status' => 200,
            'message' => 'Comment updated successfully',
            'data' => ['comment' => $comment->fresh(['author:id,username,full_name,avatar_url'])],
        ]);
    }

    public function deleteComment($id)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $comment = Comment::findOrFail($id);
        
        // Check authorization - only author can delete
        if ($comment->author_id !== $user->id) {
            return response()->json([
                'status' => 403,
                'message' => 'Not authorized to delete this comment',
            ], 403);
        }
        
        DB::beginTransaction();
        try {
            // Soft delete the comment
            $comment->update([
                'is_deleted' => true,
                'deleted_at' => now(),
            ]);
            
            // Update post reply count
            ForumPost::where('id', $comment->post_id)->decrement('reply_count');
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Comment deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Failed to delete comment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function react(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'target_type' => 'required|in:post,comment',
            'target_id' => 'required|integer',
            'reaction_type' => 'required|in:like,love,laugh,angry,sad',
        ]);
        
        $existing = Reaction::where('user_id', $user->id)
            ->where('target_type', $request->target_type)
            ->where('target_id', $request->target_id)
            ->first();
        
        if ($existing) {
            if ($existing->reaction_type === $request->reaction_type) {
                // Remove reaction
                $existing->delete();
                $action = 'removed';
            } else {
                // Update reaction
                $existing->update(['reaction_type' => $request->reaction_type]);
                $action = 'updated';
            }
        } else {
            // Create new reaction
            Reaction::create([
                'user_id' => $user->id,
                'target_type' => $request->target_type,
                'target_id' => $request->target_id,
                'reaction_type' => $request->reaction_type,
            ]);
            $action = 'added';
        }
        
        return response()->json([
            'status' => 200,
            'message' => 'Reaction ' . $action,
        ]);
    }

    public function bookmark(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $validated = $request->validate([
            'post_id' => 'required|integer|exists:forum_post,id',
        ]);
        
        $postId = (int) $validated['post_id'];
        
        $existing = SavedPost::where('user_id', $user->id)
            ->where('post_id', $postId)
            ->first();
        
        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            SavedPost::create([
                'user_id' => $user->id,
                'post_id' => $postId,
            ]);
            $action = 'added';
        }
        
        return response()->json([
            'status' => 200,
            'message' => 'Bookmark ' . $action,
        ]);
    }

    public function joinForum(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'forum_id' => 'required|integer|exists:forum,id',
        ]);
        
        $forum = Forum::findOrFail($request->forum_id);
        
        // Check if already a member
        if ($forum->members()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'status' => 200,
                'message' => 'Already a member',
            ]);
        }
        
        // Add user as member
        $forum->members()->attach($user->id, ['role' => 'member']);
        $forum->increment('member_count');
        
        return response()->json([
            'status' => 200,
            'message' => 'Successfully joined forum',
        ]);
    }

    public function leaveForum(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'forum_id' => 'required|integer|exists:forum,id',
        ]);
        
        $forum = Forum::findOrFail($request->forum_id);
        
        // Check if user is the creator
        if ($forum->created_by === $user->id) {
            return response()->json([
                'status' => 403,
                'message' => 'Forum creator cannot leave the forum',
            ], 403);
        }
        
        // Remove user from members
        if ($forum->members()->where('user_id', $user->id)->exists()) {
            $forum->members()->detach($user->id);
            $forum->decrement('member_count');
        }
        
        return response()->json([
            'status' => 200,
            'message' => 'Successfully left forum',
        ]);
    }

    public function getCategories(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        // Get all unique categories from forums
        $categories = Forum::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();
        
        return response()->json([
            'status' => 200,
            'data' => ['categories' => $categories],
        ]);
    }

    public function getAllTags(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        
        $type = $request->get('type', 'all'); // 'forum', 'post', or 'all'
        $limit = (int) $request->get('limit', 100);
        
        $tags = [];
        
        if ($type === 'forum' || $type === 'all') {
            $forumTags = ForumTag::select('tag_name', DB::raw('COUNT(*) as count'))
                ->groupBy('tag_name')
                ->orderBy('count', 'desc')
                ->orderBy('tag_name', 'asc')
                ->limit($limit)
                ->get()
                ->map(function ($tag) {
                    return [
                        'name' => $tag->tag_name,
                        'count' => $tag->count,
                        'type' => 'forum',
                    ];
                });
            $tags = array_merge($tags, $forumTags->toArray());
        }
        
        if ($type === 'post' || $type === 'all') {
            $postTags = PostTag::select('post_tags.tag_name', DB::raw('COUNT(*) as count'))
                ->join('forum_post', 'post_tags.post_id', '=', 'forum_post.id')
                ->where('forum_post.is_deleted', false)
                ->groupBy('post_tags.tag_name')
                ->orderBy('count', 'desc')
                ->orderBy('post_tags.tag_name', 'asc')
                ->limit($limit)
                ->get()
                ->map(function ($tag) {
                    return [
                        'name' => $tag->tag_name,
                        'count' => $tag->count,
                        'type' => 'post',
                    ];
                });
            $tags = array_merge($tags, $postTags->toArray());
        }
        
        // Merge tags with same name and combine counts
        $mergedTags = [];
        foreach ($tags as $tag) {
            $name = $tag['name'];
            if (isset($mergedTags[$name])) {
                $mergedTags[$name]['count'] += $tag['count'];
            } else {
                $mergedTags[$name] = [
                    'name' => $name,
                    'count' => $tag['count'],
                ];
            }
        }
        
        // Sort by count descending, then by name
        usort($mergedTags, function ($a, $b) {
            if ($b['count'] === $a['count']) {
                return strcmp($a['name'], $b['name']);
            }
            return $b['count'] - $a['count'];
        });
        
        // Limit results
        $mergedTags = array_slice($mergedTags, 0, $limit);
        
        return response()->json([
            'status' => 200,
            'data' => ['tags' => $mergedTags],
        ]);
    }

    public function reportPost(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'post_id' => 'required|integer|exists:forum_post,id',
            'reason' => 'required|in:spam,harassment,inappropriate,misinformation,other',
            'details' => 'nullable|string|max:500',
        ]);

        $post = ForumPost::findOrFail($request->post_id);
        
        // Check if user is a member of the forum
        $forum = $post->forum;
        $isMember = $forum->members()->where('user_id', $user->id)->exists();
        
        if (!$isMember) {
            return response()->json([
                'status' => 403,
                'message' => 'Only forum members can report posts',
            ], 403);
        }

        // Check if user already reported this post
        $existingReport = Report::where('reporter_id', $user->id)
            ->where('reportable_type', 'post')
            ->where('reportable_id', $post->id)
            ->first();

        if ($existingReport) {
            return response()->json([
                'status' => 400,
                'message' => 'You have already reported this post',
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Create report
            $report = Report::create([
                'reporter_id' => $user->id,
                'reportable_type' => 'post',
                'reportable_id' => $post->id,
                'reason' => $request->reason,
                'details' => $request->details,
                'status' => 'pending',
            ]);

            // Get report count for this post
            $reportCount = Report::where('reportable_type', 'post')
                ->where('reportable_id', $post->id)
                ->where('status', 'pending')
                ->count();

            // Notify all forum admins and moderators immediately
            $adminsAndModerators = $forum->members()
                ->whereIn('forum_member.role', ['admin', 'moderator'])
                ->get();

            foreach ($adminsAndModerators as $admin) {
                // Skip notifying the reporter if they are an admin/moderator
                if ($admin->id === $user->id) {
                    continue;
                }

                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'report',
                    'title' => 'Post Reported',
                    'message' => "Post '{$post->title}' has been reported for: " . ucfirst($request->reason) . ($reportCount > 1 ? " ({$reportCount} total reports)" : ""),
                    'related_type' => 'post',
                    'related_id' => $post->id,
                ]);
            }

            // Also notify if report count reaches threshold (3+)
            if ($reportCount >= 3) {
                foreach ($adminsAndModerators as $admin) {
                    if ($admin->id === $user->id) {
                        continue;
                    }

                    // Update existing notification or create new one for multiple reports
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'report',
                        'title' => 'Post Reported Multiple Times',
                        'message' => "Post '{$post->title}' has been reported {$reportCount} times and may need immediate review",
                        'related_type' => 'post',
                        'related_id' => $post->id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Post reported successfully. Forum administrators have been notified.',
                'data' => [
                    'report_id' => $report->id,
                    'report_count' => $reportCount,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Failed to submit report: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function hidePost(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $post = ForumPost::findOrFail($id);
        $forum = $post->forum;

        // Check if user is admin or moderator
        $member = $forum->members()->where('user_id', $user->id)->first();
        $userRole = $member ? $member->pivot->role : null;

        if (!in_array($userRole, ['admin', 'moderator'])) {
            return response()->json([
                'status' => 403,
                'message' => 'Only moderators and admins can hide posts',
            ], 403);
        }

        $request->validate([
            'hide' => 'required|boolean',
        ]);

        if ($request->hide) {
            // Note: is_hidden column doesn't exist in database, so we'll just mark as deleted instead
            $post->update([
                'is_deleted' => true,
            ]);
            $message = 'Post hidden successfully';
            
            // Notify post author if hidden by moderator (not by themselves)
            if ($post->author_id !== $user->id) {
                Notification::create([
                    'user_id' => $post->author_id,
                    'type' => 'post_moderation',
                    'title' => 'Post Hidden',
                    'message' => "Your post '{$post->title}' has been hidden by a forum moderator",
                    'related_type' => 'post',
                    'related_id' => $post->id,
                ]);
            }
        } else {
            $post->update([
                'is_deleted' => false,
            ]);
            $message = 'Post unhidden successfully';
            
            // Notify post author if unhidden by moderator (not by themselves)
            if ($post->author_id !== $user->id) {
                Notification::create([
                    'user_id' => $post->author_id,
                    'type' => 'post_moderation',
                    'title' => 'Post Unhidden',
                    'message' => "Your post '{$post->title}' has been unhidden by a forum moderator",
                    'related_type' => 'post',
                    'related_id' => $post->id,
                ]);
            }
        }

        return response()->json([
            'status' => 200,
            'message' => $message,
        ]);
    }

    public function getPostReports(Request $request, $postId)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $post = ForumPost::findOrFail($postId);
        $forum = $post->forum;

        // Check if user is admin or moderator
        $member = $forum->members()->where('user_id', $user->id)->first();
        $userRole = $member ? $member->pivot->role : null;

        if (!in_array($userRole, ['admin', 'moderator'])) {
            return response()->json([
                'status' => 403,
                'message' => 'Only moderators and admins can view reports',
            ], 403);
        }

        $reports = Report::where('reportable_type', 'post')
            ->where('reportable_id', $postId)
            ->with(['reporter:id,username,full_name'])
            ->orderBy('created_at', 'desc')
            ->get();

        $reportCount = $reports->count();
        $pendingCount = $reports->where('status', 'pending')->count();

        return response()->json([
            'status' => 200,
            'data' => [
                'reports' => $reports,
                'total_count' => $reportCount,
                'pending_count' => $pendingCount,
            ],
        ]);
    }

    public function getForumReports(Request $request, $forumId)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $forum = Forum::findOrFail($forumId);

        // Check if user is forum creator or admin
        $isCreator = $forum->created_by === $user->id;
        $member = $forum->members()->where('user_id', $user->id)->first();
        $userRole = $member ? $member->pivot->role : null;
        $isAdmin = in_array($userRole, ['admin']);

        if (!$isCreator && !$isAdmin) {
            return response()->json([
                'status' => 403,
                'message' => 'Only forum creator and admins can view reports',
            ], 403);
        }

        // Get all posts in this forum
        $postIds = ForumPost::where('forum_id', $forumId)->pluck('id');

        // Get all reports for posts in this forum
        $reports = Report::where('reportable_type', 'post')
            ->whereIn('reportable_id', $postIds)
            ->with([
                'reporter:id,username,full_name',
                'reportable:id,title,author_id',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'post_id' => $report->reportable_id,
                    'post_title' => $report->reportable->title ?? 'Deleted Post',
                    'reporter_id' => $report->reporter_id,
                    'reporter_name' => $report->reporter->full_name ?? $report->reporter->username ?? 'Unknown',
                    'reporter_username' => $report->reporter->username ?? 'Unknown',
                    'reason' => $report->reason,
                    'details' => $report->details,
                    'status' => $report->status,
                    'reviewed_by' => $report->reviewed_by,
                    'reviewed_at' => $report->reviewed_at,
                    'review_notes' => $report->review_notes,
                    'created_at' => $report->created_at,
                ];
            });

        $statusCounts = [
            'pending' => $reports->where('status', 'pending')->count(),
            'reviewed' => $reports->where('status', 'reviewed')->count(),
            'resolved' => $reports->where('status', 'resolved')->count(),
            'dismissed' => $reports->where('status', 'dismissed')->count(),
        ];

        return response()->json([
            'status' => 200,
            'data' => [
                'reports' => $reports,
                'status_counts' => $statusCounts,
                'total_count' => $reports->count(),
            ],
        ]);
    }

    public function updateReportStatus(Request $request, $reportId)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,dismissed',
            'review_notes' => 'nullable|string|max:500',
        ]);

        $report = Report::findOrFail($reportId);
        $post = ForumPost::findOrFail($report->reportable_id);
        $forum = $post->forum;

        // Check if user is forum creator or admin
        $isCreator = $forum->created_by === $user->id;
        $member = $forum->members()->where('user_id', $user->id)->first();
        $userRole = $member ? $member->pivot->role : null;
        $isAdmin = in_array($userRole, ['admin']);

        if (!$isCreator && !$isAdmin) {
            return response()->json([
                'status' => 403,
                'message' => 'Only forum creator and admins can update report status',
            ], 403);
        }

        $oldStatus = $report->status;
        
        $report->update([
            'status' => $request->status,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $request->review_notes,
        ]);

        // Notify the reporter about the status update
        $reporter = User::find($report->reporter_id);
        if ($reporter && $oldStatus !== $request->status) {
            $statusMessages = [
                'reviewed' => 'Your report has been reviewed by a forum administrator',
                'resolved' => 'Your report has been resolved. The issue has been addressed',
                'dismissed' => 'Your report has been dismissed by a forum administrator',
                'pending' => 'Your report status has been reset to pending',
            ];

            $message = $statusMessages[$request->status] ?? "Your report status has been updated to: " . ucfirst($request->status);
            if ($request->review_notes) {
                $message .= ". Notes: " . $request->review_notes;
            }

            Notification::create([
                'user_id' => $reporter->id,
                'type' => 'report_status',
                'title' => 'Report Status Updated',
                'message' => $message . " - Post: '{$post->title}'",
                'related_type' => 'report',
                'related_id' => $report->id,
            ]);
        }

        // If post is hidden/unhidden as a result of resolving/dismissing, notify post author
        if ($request->status === 'resolved' || $request->status === 'dismissed') {
            // Optionally notify post author if their post was reported
            // This is optional - you may want to notify them that action was taken
        }

        return response()->json([
            'status' => 200,
            'message' => 'Report status updated successfully',
            'data' => $report,
        ]);
    }

    public function deleteForum($id)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $forum = Forum::findOrFail($id);

        // Only creator can delete the forum
        if ($forum->created_by !== $user->id) {
            return response()->json([
                'status' => 403,
                'message' => 'Only the forum creator can delete the forum',
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Delete all related data
            $forum->members()->detach();
            $forum->tags()->delete();
            
            // Delete all posts and their related data
            foreach ($forum->posts as $post) {
                $post->attachments()->delete();
                $post->tags()->delete();
                $post->comments()->delete();
                $post->reactions()->delete();
                DB::table('poll_option')->where('post_id', $post->id)->delete();
                DB::table('poll_vote')->where('post_id', $post->id)->delete();
            }
            
            $forum->posts()->delete();
            $forum->delete();

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Forum deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Failed to delete forum: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getForumMembers($id)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $forum = Forum::findOrFail($id);

        // Check if user is creator or admin
        $isCreator = $forum->created_by === $user->id;
        $member = $forum->members()->where('user_id', $user->id)->first();
        $isAdmin = $member && in_array($member->pivot->role, ['admin']);

        if (!$isCreator && !$isAdmin) {
            return response()->json([
                'status' => 403,
                'message' => 'Only forum creator and admins can view members',
            ], 403);
        }

        $members = $forum->members()
            ->select('user.id', 'user.username', 'user.full_name', 'user.avatar_url', 'forum_member.role')
            ->orderBy('forum_member.created_at', 'asc')
            ->get()
            ->map(function ($member) use ($forum) {
                return [
                    'user_id' => $member->id,
                    'username' => $member->username,
                    'full_name' => $member->full_name,
                    'avatar_url' => $member->avatar_url,
                    'role' => $member->pivot->role,
                    'is_creator' => $forum->created_by === $member->id,
                ];
            });

        return response()->json([
            'status' => 200,
            'data' => ['members' => $members],
        ]);
    }

    public function promoteMemberToAdmin(Request $request, $forumId)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'user_id' => 'required|integer|exists:user,id',
        ]);

        $forum = Forum::findOrFail($forumId);

        // Only creator can promote members to admin
        if ($forum->created_by !== $user->id) {
            return response()->json([
                'status' => 403,
                'message' => 'Only the forum creator can promote members to admin',
            ], 403);
        }

        // Cannot promote the creator (they're already the creator)
        if ($forum->created_by === $request->user_id) {
            return response()->json([
                'status' => 400,
                'message' => 'The forum creator is already the owner',
            ], 400);
        }

        // Check if user is a member
        $member = $forum->members()->where('user_id', $request->user_id)->first();
        if (!$member) {
            return response()->json([
                'status' => 404,
                'message' => 'User is not a member of this forum',
            ], 404);
        }

        // Update role to admin
        $forum->members()->updateExistingPivot($request->user_id, ['role' => 'admin']);

        return response()->json([
            'status' => 200,
            'message' => 'Member promoted to admin successfully',
        ]);
    }

    public function removeMember(Request $request, $forumId)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'user_id' => 'required|integer|exists:user,id',
        ]);

        $forum = Forum::findOrFail($forumId);

        // Check if user is creator or admin
        $isCreator = $forum->created_by === $user->id;
        $member = $forum->members()->where('user_id', $user->id)->first();
        $isAdmin = $member && in_array($member->pivot->role, ['admin']);

        if (!$isCreator && !$isAdmin) {
            return response()->json([
                'status' => 403,
                'message' => 'Only forum creator and admins can remove members',
            ], 403);
        }

        // Cannot remove the creator
        if ($forum->created_by === $request->user_id) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot remove the forum creator',
            ], 400);
        }

        // Cannot remove if trying to remove yourself (use leave forum instead)
        if ($user->id === $request->user_id && !$isCreator) {
            return response()->json([
                'status' => 400,
                'message' => 'Use "Leave Forum" to remove yourself from the forum',
            ], 400);
        }

        // Check if user is a member
        if (!$forum->members()->where('user_id', $request->user_id)->exists()) {
            return response()->json([
                'status' => 404,
                'message' => 'User is not a member of this forum',
            ], 404);
        }

        // Remove member
        $forum->members()->detach($request->user_id);
        $forum->decrement('member_count');

        return response()->json([
            'status' => 200,
            'message' => 'Member removed successfully',
        ]);
    }

    public function updateForum(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $forum = Forum::findOrFail($id);

        // Check if user is creator or admin
        $isCreator = $forum->created_by === $user->id;
        $member = $forum->members()->where('user_id', $user->id)->first();
        $isAdmin = $member && in_array($member->pivot->role, ['admin']);

        if (!$isCreator && !$isAdmin) {
            return response()->json([
                'status' => 403,
                'message' => 'Only forum creator and admins can update forum settings',
            ], 403);
        }

        $forum->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Forum settings updated successfully',
            'data' => ['forum' => $forum],
        ]);
    }

    protected function getCurrentUser()
    {
        return session('user_id') ? User::find(session('user_id')) : Auth::user();
    }
}

