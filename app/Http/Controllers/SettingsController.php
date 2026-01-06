<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('settings.index', [
            'user' => $user,
        ]);
    }

    /**
     * Update user settings.
     */
    public function update(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'chatbot_enabled' => 'nullable|boolean',
            'share_badges_on_profile' => 'nullable|boolean',
            'visible_badge_codes' => 'nullable|array',
        ]);

        // Update settings
        if (isset($validated['chatbot_enabled'])) {
            $user->chatbot_enabled = $validated['chatbot_enabled'];
        }

        if (isset($validated['share_badges_on_profile'])) {
            $user->share_badges_on_profile = $validated['share_badges_on_profile'];
        }

        if (isset($validated['visible_badge_codes'])) {
            $user->visible_badge_codes = json_encode($validated['visible_badge_codes']);
        }

        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Tetapan berjaya dikemaskini',
        ]);
    }

    /**
     * Get user's earned badges for settings page
     */
    public function getUserBadges()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        // Get user's earned badges
        $userBadges = \Illuminate\Support\Facades\DB::table('user_badge')
            ->where('user_id', $user->id)
            ->whereIn('status', ['earned', 'redeemed'])
            ->join('badge', 'user_badge.badge_code', '=', 'badge.code')
            ->select('badge.code', 'badge.name', 'badge.icon', 'badge.color')
            ->get();

        // Get visible badge codes from user settings
        $visibleBadgeCodes = [];
        if ($user->visible_badge_codes) {
            $visibleBadgeCodes = json_decode($user->visible_badge_codes, true) ?? [];
        } else {
            // If no preference set, show all earned badges by default
            $visibleBadgeCodes = $userBadges->pluck('code')->toArray();
        }

        return response()->json([
            'status' => 200,
            'badges' => $userBadges,
            'visible_badge_codes' => $visibleBadgeCodes,
        ]);
    }

    /**
     * Get current user from session
     */
    protected function getCurrentUser()
    {
        $user = null;
        if (session('user_id')) {
            $user = \App\Models\User::find(session('user_id'));
        }
        
        if (!$user) {
            $user = Auth::user();
        }
        
        return $user;
    }
}
