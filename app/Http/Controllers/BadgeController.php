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
}