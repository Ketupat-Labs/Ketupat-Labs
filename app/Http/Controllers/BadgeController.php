<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BadgeCategory;
use App\Models\Badge;

class BadgeController extends Controller
{
    public function index()
{
    $categories = BadgeCategory::all();
    $badges = Badge::with('category')->get();

    $user = auth()->user();
    $userPoints = $user ? $user->points : 0;

    if ($user) {
        foreach ($badges as $badge) {
            $userBadge = $user->badges()->where('badge_code', $badge->code)->first();

            if ($userPoints >= $badge->requirement_value) {
                // User has enough points
                if (!$userBadge) {
                    // First time earning
                    $user->badges()->attach($badge->code, ['status' => 'earned']);
                    // Create notification
                    \App\Models\Notification::create([
                        'user_id' => $user->id,
                        'type' => 'badge_earned',
                        'title' => 'Lencana Diperoleh!',
                        'message' => 'Tahniah! Anda telah memperoleh lencana "' . $badge->name . '"',
                        'related_type' => 'badge',
                        'related_id' => $badge->id,
                        'is_read' => false,
                    ]);
                } elseif ($userBadge->pivot->status === 'locked') {
                    // Previously locked, now earned
                    $user->badges()->updateExistingPivot($badge->code, ['status' => 'earned']);
                    // Create notification
                    \App\Models\Notification::create([
                        'user_id' => $user->id,
                        'type' => 'badge_earned',
                        'title' => 'Lencana Diperoleh!',
                        'message' => 'Tahniah! Anda telah memperoleh lencana "' . $badge->name . '"',
                        'related_type' => 'badge',
                        'related_id' => $badge->id,
                        'is_read' => false,
                    ]);
                }
            } else {
                // User doesn’t have enough points
                if (!$userBadge) {
                    // Make sure locked badges exist in pivot
                    $user->badges()->attach($badge->code, ['status' => 'locked']);
                }
            }
        }
    }

    $userBadges = $user ? $user->badges()->pluck('status','badge_code')->toArray() : [];

    $badgesWithStatus = $badges->map(function($badge) use ($userBadges, $userPoints) {
        $status = $userBadges[$badge->code] ?? 'locked';
        $progress = min(100, ($userPoints / max(1,$badge->requirement_value))*100);

        return [
            'id' => $badge->id,
            'code' => $badge->code,
            'name' => $badge->name,
            'description' => $badge->description,
            'category_slug' => $badge->category->code ?? 'general',
            'category_name' => $badge->category->name ?? 'General',
            'icon' => $badge->icon,
            'color' => $badge->color,
            'requirement_value' => $badge->requirement_value,
            'xp_reward' => $badge->xp_reward,
            'user_points' => $userPoints,
            'progress' => $progress,
            'status' => $status,
            'is_redeemable' => $status === 'earned' // Only earned badges are redeemable
        ];
    });

    return view('badges.index', compact('categories','badgesWithStatus'));
}


    public function redeem(Request $request)
    {
        $user = auth()->user();
        $badgeCode = $request->badge_code;
    
        if (!$user || !$badgeCode) {
            return response()->json(['success'=>false,'message'=>'Invalid request']);
        }
    
        $userBadge = $user->badges()->where('badge_code', $badgeCode)->first();
    
        if (!$userBadge || $userBadge->pivot->status !== 'earned') {
            return response()->json(['success'=>false,'message'=>'Badge cannot be redeemed']);
        }
    
        // Redeem badge
        $user->badges()->updateExistingPivot($badgeCode, ['status'=>'redeemed']);
    
        // Optionally give XP
        $user->increment('xp', $userBadge->xp_reward);
    
        return response()->json(['success'=>true,'message'=>'Badge redeemed successfully!']);
    }

    // ========== TEACHER BADGE MANAGEMENT ==========

    /**
     * Display teacher's custom badges
     */
    public function manage()
    {
        $teacher = auth()->user();
        
        // Get only custom badges created by this teacher
        $badges = Badge::where('creator_id', $teacher->id)
            ->where('is_custom', true)
            ->with('activity')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('badges.manage', compact('badges'));
    }

    /**
     * Show form to create new custom badge
     */
    public function createForm()
    {
        return view('badges.create');
    }

    /**
     * Store new custom badge
     */
    public function storeCustom(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'required',
            'icon' => 'required|max:10', // Emoji (single character or short string)
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/', // Hex color
            'xp_reward' => 'required|integer|min:0|max:1000',
        ]);

        $teacher = auth()->user();

        // Generate unique code
        $code = 'custom_' . $teacher->id . '_' . time();

        $badge = Badge::create([
            'code' => $code,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'icon' => $validated['icon'],
            'color' => $validated['color'],
            'xp_reward' => $validated['xp_reward'],
            'creator_id' => $teacher->id,
            'is_custom' => true,
            'category_id' => null, // Custom badges don't need categories
            'category_slug' => 'custom', // Default slug for custom badges
            'requirement_type' => null, // Custom badges use activity completion, not requirement_type
            'requirement_value' => 0, // Default value for legacy field
        ]);

        return redirect()->route('badges.manage')
            ->with('success', 'Lencana berjaya dicipta!');
    }

    /**
     * Show form to edit custom badge
     */
    public function editForm($id)
    {
        $badge = Badge::where('id', $id)
            ->where('creator_id', auth()->id())
            ->where('is_custom', true)
            ->firstOrFail();

        return view('badges.edit', compact('badge'));
    }

    /**
     * Update custom badge
     */
    public function updateCustom(Request $request, $id)
    {
        $badge = Badge::where('id', $id)
            ->where('creator_id', auth()->id())
            ->where('is_custom', true)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'required',
            'icon' => 'required|max:10',
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'xp_reward' => 'required|integer|min:0|max:1000',
        ]);

        $badge->update($validated);

        return redirect()->route('badges.manage')
            ->with('success', 'Lencana berjaya dikemaskini!');
    }

    /**
     * Delete custom badge
     */
    public function destroyCustom($id)
    {
        $badge = Badge::where('id', $id)
            ->where('creator_id', auth()->id())
            ->where('is_custom', true)
            ->firstOrFail();

        // Check if badge is linked to an activity
        if ($badge->activity_id) {
            return redirect()->route('badges.manage')
                ->with('error', 'Tidak boleh padam lencana yang telah ditugaskan kepada aktiviti. Sila nyahtugaskan dahulu.');
        }

        $badge->delete();

        return redirect()->route('badges.manage')
            ->with('success', 'Lencana berjaya dipadam!');
    }
}