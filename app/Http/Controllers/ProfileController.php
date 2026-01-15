<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Models\ForumPost;
use App\Models\Forum;
use App\Models\SavedPost;
use App\Models\Badge;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show(Request $request, $userId = null): View
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return redirect()->route('login');
        }

        // If no userId provided, show current user's profile
        $profileUserId = $userId ? (int) $userId : $currentUser->id;
        $profileUser = User::findOrFail($profileUserId);
        
        $isOwnProfile = $currentUser->id === $profileUser->id;

        // Get user's forum posts
        // Only show posts from:
        // 1. Public forums
        // 2. Private/class forums where the current viewer is a member
        $userForumIds = [];
        if (!$isOwnProfile) {
            // Get forums the current user is a member of
            $userForumIds = DB::table('forum_member')
                ->where('user_id', $currentUser->id)
                ->pluck('forum_id')
                ->toArray();
        } else {
            // For own profile, get all forums user is a member of
            $userForumIds = DB::table('forum_member')
                ->where('user_id', $profileUser->id)
                ->pluck('forum_id')
                ->toArray();
        }

        // Get public forum IDs
        $publicForumIds = Forum::where('visibility', 'public')
            ->pluck('id')
            ->toArray();

        // Combine public forums and user's member forums
        $allowedForumIds = array_unique(array_merge($publicForumIds, $userForumIds));

        // Get posts from allowed forums
        $posts = ForumPost::where('author_id', $profileUser->id)
            ->where('is_deleted', false)
            ->whereIn('forum_id', $allowedForumIds)
            ->with(['forum:id,title,visibility', 'author:id,username,full_name,avatar_url'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Sync Rakan Baik badge progress if viewing own profile or if needed for display
        // Note: Friend functionality removed, but badge logic remains for existing data
        $friendCount = 0; // Friend functionality removed
        $rakanBaikBadge = DB::table('badge')->where('code', 'friendly')->first();
        if ($rakanBaikBadge) {
            $isEarned = $friendCount >= ($rakanBaikBadge->points_required ?? 5);
            $newStatus = $isEarned ? 'earned' : 'locked';
            
            // Check current status safely
            $currentStatus = DB::table('user_badge')
                ->where('user_id', $profileUserId)
                ->where('badge_code', 'friendly')
                ->value('status');
                
            // Only update if not already redeemed
            if ($currentStatus !== 'redeemed') {
                 DB::table('user_badge')->updateOrInsert(
                    ['user_id' => $profileUserId, 'badge_code' => 'friendly'],
                    [
                        'progress' => $friendCount,
                        'updated_at' => now(),
                        'status' => ($currentStatus === 'earned' || $isEarned) ? 'earned' : 'locked',
                        'earned_at' => ($isEarned && $currentStatus !== 'earned') ? now() : DB::raw('earned_at')
                    ]
                );
            }
        }

        // Get all badges with category information - Filtered by Profile User's Role
        $role = $profileUser->role ?? 'student';
        
        $allBadges = \App\Models\Badge::with('category')
            ->where(function($query) use ($role) {
                $query->whereHas('category', function($q) use ($role) {
                    $q->where('role_restriction', 'all')
                      ->orWhere('role_restriction', $role)
                      ->orWhereNull('role_restriction');
                })
                ->orWhereDoesntHave('category');
            })
            ->orderBy('name', 'asc')
            ->get();
        
        // Get user's badge progress details
        $userBadges = DB::table('user_badge')
            ->where('user_id', $profileUserId)
            ->get()
            ->keyBy('badge_code');

        // Map badges with full status and progress
        $badges = $allBadges->map(function ($badge) use ($userBadges) {
            $userBadge = $userBadges[$badge->code] ?? null;
            
            // Determine status from database column
            // 'user_badge' table uses 'status' column (locked, earned, redeemed)
            // It might NOT have 'is_earned' column which caused the error
            $status = $userBadge->status ?? 'locked';
            
            // Prioritize 'status' column logic
            $badge->is_earned = ($status === 'earned' || $status === 'redeemed');
            $badge->is_redeemed = ($status === 'redeemed');
            
            // Fallback if legacy boolean columns exist (safeguard)
            if (!$badge->is_earned && !empty($userBadge->is_earned)) {
                 $badge->is_earned = (bool)$userBadge->is_earned;
            }
            if (!$badge->is_redeemed && !empty($userBadge->is_redeemed)) {
                 $badge->is_redeemed = (bool)$userBadge->is_redeemed;
            }

            $badge->progress = (int)($userBadge->progress ?? 0);
            
            // Use requirement_value or points_required (for compatibility)
            $required = (int)($badge->requirement_value ?? $badge->points_required ?? 0);
            $badge->points_required = $required; // Add for consistency
            if ($required <= 0) $required = 100; // Default to 100 if invalid
            
            $percentage = min(100, round(($badge->progress / $required) * 100));
            $badge->progress_percentage = (int)$percentage;
            
            // Determine visibility based on user settings
            $visibleBadgeCodes = [];
            
            // Try to decode settings
            if (!empty($profileUser->visible_badge_codes)) {
                $decoded = json_decode($profileUser->visible_badge_codes, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $visibleBadgeCodes = $decoded;
                }
            }
            
            // If no specific badges selected (or empty selection), default to showing all earned badges
            // This ensures if 'share' is ON, we show something rather than nothing
            if (empty($visibleBadgeCodes)) {
                $visibleBadgeCodes = $userBadges->keys()->toArray();
            }
            
            $badge->is_visible = $badge->is_earned && in_array($badge->code, $visibleBadgeCodes);

            return $badge;
        });

        // Friend count - functionality removed
        $friendCount = 0;

        // Get saved posts (only for own profile)
        $savedPosts = collect();
        if ($isOwnProfile) {
            $savedPostIds = SavedPost::where('user_id', $profileUserId)
                ->pluck('post_id')
                ->toArray();
            
            if (!empty($savedPostIds)) {
                $savedPosts = ForumPost::whereIn('id', $savedPostIds)
                    ->where('is_deleted', false)
                    ->whereIn('forum_id', $allowedForumIds)
                    ->with(['forum:id,title,visibility', 'author:id,username,full_name,avatar_url'])
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();
            }
        }

        // Get categories for filter (only those with active badges)
        $activeCategoryIds = $allBadges->pluck('category_id')->unique()->filter()->toArray();
        $categories = \App\Models\BadgeCategory::whereIn('id', $activeCategoryIds)->orderBy('name')->get();

        // Get follow counts
        $followingCount = DB::table('follow')
            ->where('follower_id', $profileUserId)
            ->count();
        
        $followersCount = DB::table('follow')
            ->where('following_id', $profileUserId)
            ->count();

        // Check if current user is following this profile user
        $isFollowing = false;
        if (!$isOwnProfile) {
            $isFollowing = DB::table('follow')
                ->where('follower_id', $currentUser->id)
                ->where('following_id', $profileUserId)
                ->exists();
        }

        return view('profile.show', [
            'profileUser' => $profileUser,
            'currentUser' => $currentUser,
            'isOwnProfile' => $isOwnProfile,
            'posts' => $posts,
            'savedPosts' => $savedPosts,
            'badges' => $badges,
            'categories' => $categories,
            'followingCount' => $followingCount,
            'followersCount' => $followersCount,
            'isFollowing' => $isFollowing,
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Refresh user to get latest avatar_url
        $user->refresh();
        
        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            
            // Validate file
            if ($file->isValid() && in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif'])) {
                // Delete old avatar if exists
                if ($user->avatar_url && file_exists(public_path($user->avatar_url))) {
                    @unlink(public_path($user->avatar_url));
                }
                
                // Generate directory path: uploads/avatars/YYYY/MM/
                $directory = public_path('uploads/avatars/' . date('Y/m/'));
                
                // Create directory if it doesn't exist
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Generate unique filename
                $filename = 'avatar_' . $user->id . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Store file
                $file->move($directory, $filename);
                
                // Generate URL path (relative to public directory)
                $avatarUrl = '/uploads/avatars/' . date('Y/m/') . $filename;
                
                // Update user avatar_url
                $user->avatar_url = $avatarUrl;
            }
        } elseif ($request->has('remove_avatar') && $request->remove_avatar) {
            // Remove avatar
            if ($user->avatar_url && file_exists(public_path($user->avatar_url))) {
                @unlink(public_path($user->avatar_url));
            }
            $user->avatar_url = null;
        }
        
        // Only update allowed fields (email is excluded)
        $validated = $request->validated();
        // Remove avatar from validated array as we handle it separately
        unset($validated['avatar'], $validated['remove_avatar']);
        $user->fill($validated);
        $user->save();

        return Redirect::route('profile.show', $user->id)->with('status', 'profile-updated');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.'], 'updatePassword');
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return redirect()->route('login');
        }
        
        $request->validateWithBag('userDeletion', [
            'password' => ['required'],
        ]);
        
        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'The password is incorrect.']);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Follow a user
     */
    public function follow(Request $request, $userId)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $userToFollow = User::findOrFail($userId);

        // Can't follow yourself
        if ($currentUser->id === $userToFollow->id) {
            return response()->json(['status' => 400, 'message' => 'Cannot follow yourself'], 400);
        }

        // Check if already following
        $existingFollow = DB::table('follow')
            ->where('follower_id', $currentUser->id)
            ->where('following_id', $userToFollow->id)
            ->first();

        if ($existingFollow) {
            return response()->json(['status' => 200, 'message' => 'Already following', 'data' => ['is_following' => true]], 200);
        }

        // Create follow relationship
        DB::table('follow')->insert([
            'follower_id' => $currentUser->id,
            'following_id' => $userToFollow->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create notification for the user being followed
        // The NotificationObserver will automatically broadcast this via WebSocket
        $followerName = $currentUser->full_name ?? $currentUser->username;
        Notification::create([
            'user_id' => $userToFollow->id,
            'type' => 'follow',
            'title' => 'Pengguna Baharu Mengikuti Anda',
            'message' => $followerName . ' telah mula mengikuti anda',
            'related_type' => 'user',
            'related_id' => $currentUser->id,
            'is_read' => false,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Successfully followed user',
            'data' => ['is_following' => true]
        ], 200);
    }

    /**
     * Unfollow a user
     */
    public function unfollow(Request $request, $userId)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $userToUnfollow = User::findOrFail($userId);

        // Remove follow relationship
        DB::table('follow')
            ->where('follower_id', $currentUser->id)
            ->where('following_id', $userToUnfollow->id)
            ->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Successfully unfollowed user',
            'data' => ['is_following' => false]
        ], 200);
    }

    /**
     * Get users that a user is following
     */
    public function getFollowing(Request $request, $userId)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $profileUser = User::findOrFail($userId);

        $following = DB::table('follow')
            ->where('follower_id', $profileUser->id)
            ->join('user', 'follow.following_id', '=', 'user.id')
            ->select('user.id', 'user.username', 'user.full_name', 'user.avatar_url')
            ->orderBy('follow.created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'full_name' => $user->full_name ?? $user->username,
                    'avatar_url' => $user->avatar_url,
                ];
            });

        return response()->json([
            'status' => 200,
            'data' => ['users' => $following]
        ], 200);
    }

    /**
     * Get users that are following a user
     */
    public function getFollowers(Request $request, $userId)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $profileUser = User::findOrFail($userId);

        $followers = DB::table('follow')
            ->where('following_id', $profileUser->id)
            ->join('user', 'follow.follower_id', '=', 'user.id')
            ->select('user.id', 'user.username', 'user.full_name', 'user.avatar_url')
            ->orderBy('follow.created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'full_name' => $user->full_name ?? $user->username,
                    'avatar_url' => $user->avatar_url,
                ];
            });

        return response()->json([
            'status' => 200,
            'data' => ['users' => $followers]
        ], 200);
    }

    /**
     * Get current user's following list (for DM creation)
     */
    public function getMyFollowing(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $following = DB::table('follow')
            ->where('follower_id', $currentUser->id)
            ->join('user', 'follow.following_id', '=', 'user.id')
            ->select('user.id', 'user.username', 'user.full_name', 'user.avatar_url', 'user.is_online')
            ->orderBy('follow.created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'full_name' => $user->full_name ?? $user->username,
                    'avatar_url' => $user->avatar_url,
                    'is_online' => $user->is_online ?? false,
                ];
            });

        return response()->json([
            'status' => 200,
            'data' => ['users' => $following]
        ], 200);
    }

    /**
     * Get current user from session
     */
    protected function getCurrentUser()
    {
        $user = null;
        if (session('user_id')) {
            $user = User::find(session('user_id'));
        }
        
        if (!$user) {
            $user = Auth::user();
        }
        
        return $user;
    }
}
