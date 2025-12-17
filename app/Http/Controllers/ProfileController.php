<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Models\ForumPost;
use App\Models\Forum;
use App\Models\Friend;
use App\Models\SavedPost;
use App\Models\Badge;
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
        $isFriend = $currentUser->isFriendWith($profileUserId);
        $hasPendingRequest = $currentUser->hasPendingRequestWith($profileUserId);

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

        // Get all badges with category information
        $allBadges = \App\Models\Badge::with('category')
            ->orderBy('name', 'asc')
            ->get();
        
        // Get user's earned badge codes
        $earnedBadgeCodes = $profileUser->badges()->pluck('code')->toArray();
        
        // Mark which badges are earned
        $badges = $allBadges->map(function ($badge) use ($earnedBadgeCodes) {
            $badge->is_earned = in_array($badge->code, $earnedBadgeCodes);
            return $badge;
        });

        // Get friend count
        $friendCount = Friend::where(function ($q) use ($profileUserId) {
            $q->where('user_id', $profileUserId)->where('status', 'accepted');
        })->orWhere(function ($q) use ($profileUserId) {
            $q->where('friend_id', $profileUserId)->where('status', 'accepted');
        })->count();

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

        // Get categories for filter
        $categories = \App\Models\BadgeCategory::orderBy('name')->get();

        return view('profile.show', [
            'profileUser' => $profileUser,
            'currentUser' => $currentUser,
            'isOwnProfile' => $isOwnProfile,
            'isFriend' => $isFriend,
            'hasPendingRequest' => $hasPendingRequest,
            'posts' => $posts,
            'savedPosts' => $savedPosts,
            'badges' => $badges,
            'categories' => $categories,
            'friendCount' => $friendCount,
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
        
        // Only update allowed fields (email is excluded)
        $validated = $request->validated();
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
