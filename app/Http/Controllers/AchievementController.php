<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\Badge;
use App\Models\UserBadge;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AchievementController extends Controller
{
    // Method untuk /badges route
    public function badgesIndex()
    {
        // Gunakan session user ID (tanpa authentication)
        if (!Session::has('user_id')) {
            Session::put('user_id', 'user_' . uniqid());
            Session::put('demo_mode', true);
        }
        $userId = Session::get('user_id');
        
        try {
            // Get all badges with categories
            $badges = DB::table('badge')
                ->leftJoin('badge_category', 'badge.category_code', '=', 'badge_category.code')
                ->select('badge.*', 'badge_category.name as category_name', 'badge_category.color as category_color')
                ->orderBy('badge.name')
                ->get();
            
            // Get user badges
            $userBadges = DB::table('user_badge')
                ->where('user_id', $userId)
                ->get()
                ->keyBy('badge_code');
            
            // Process badges dengan status
            $badgesWithStatus = [];
            
            foreach ($badges as $badge) {
                $userBadge = $userBadges[$badge->code] ?? null;
                
                $badgeData = [
                    'id' => $badge->id,
                    'code' => $badge->code,
                    'name' => $badge->name,
                    'name_bm' => $badge->name_bm ?? $badge->name,
                    'description' => $badge->description,
                    'description_bm' => $badge->description_bm ?? $badge->description,
                    'icon' => $badge->icon ?? 'fas fa-award',
                    'color' => $badge->color ?? '#3498db',
                    'category' => $badge->category_code ?? 'general',
                    'category_name' => $badge->category_name ?? 'General',
                    'points_required' => $badge->points_required ?? 100,
                    'xp_reward' => $badge->xp_reward ?? 100,
                    'level' => $badge->level ?? 'Beginner',
                ];
                
                if ($userBadge) {
                    $badgeData['user_points'] = $userBadge->progress ?? 0;
                    $badgeData['status'] = $userBadge->is_redeemed ? 'redeemed' : ($userBadge->is_earned ? 'earned' : 'locked');
                    $badgeData['progress'] = $badgeData['points_required'] > 0 
                        ? min(100, ($badgeData['user_points'] / $badgeData['points_required']) * 100)
                        : 0;
                    $badgeData['redeemed'] = $userBadge->is_redeemed ?? false;
                    $badgeData['redeemed_at'] = $userBadge->redeemed_at;
                    $badgeData['earned_at'] = $userBadge->earned_at;
                } else {
                    $badgeData['user_points'] = 0;
                    $badgeData['status'] = 'locked';
                    $badgeData['progress'] = 0;
                    $badgeData['redeemed'] = false;
                    $badgeData['redeemed_at'] = null;
                    $badgeData['earned_at'] = null;
                }
                
                $badgeData['is_redeemable'] = ($badgeData['status'] === 'earned' && !$badgeData['redeemed']);
                
                $badgesWithStatus[] = $badgeData;
            }

            // Get categories for filter
            $categories = DB::table('badge_category')->get();
            
            // Calculate statistics
            $totalBadges = count($badgesWithStatus);
            $earnedBadges = collect($badgesWithStatus)
                            ->where('status', 'earned')
                            ->where('redeemed', false)
                            ->count();
            $redeemedBadges = collect($badgesWithStatus)
                             ->where('status', 'redeemed')
                             ->where('redeemed', true)
                             ->count();
            
            return view('badges.index', compact(
                'badgesWithStatus',
                'categories',
                'totalBadges',
                'earnedBadges',
                'redeemedBadges'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error in badgesIndex: ' . $e->getMessage());
            
            return view('badges.index', [
                'badgesWithStatus' => [],
                'categories' => collect(),
                'totalBadges' => 0,
                'earnedBadges' => 0,
                'redeemedBadges' => 0,
                'error' => 'Sistem sedang disediakan. Cuba /demo/badges untuk versi demo.'
            ]);
        }
    }

    public function myBadges()
    {
        // Gunakan session user ID
        if (!Session::has('user_id')) {
            Session::put('user_id', 'user_' . uniqid());
        }
        $userId = Session::get('user_id');
        
        // Check for "Rakan Baik" (Social) badge progress
        // Logic: 5 friends needed
        $friendCount = DB::table('friend')
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)->where('status', 'accepted');
            })
            ->orWhere(function($q) use ($userId) {
                $q->where('friend_id', $userId)->where('status', 'accepted');
            })
            ->count();
            
        $rakanBaikBadge = DB::table('badge')->where('code', 'friendly')->first();
        if ($rakanBaikBadge) {
            $isEarned = $friendCount >= ($rakanBaikBadge->points_required ?? 5);
            $newStatus = $isEarned ? 'earned' : 'locked';
            
            // Check current status safely
            $currentStatus = DB::table('user_badge')
                ->where('user_id', $userId)
                ->where('badge_code', 'friendly')
                ->value('status');
                
            // Only update if not already redeemed
            if ($currentStatus !== 'redeemed') {
                 DB::table('user_badge')->updateOrInsert(
                    ['user_id' => $userId, 'badge_code' => 'friendly'],
                    [
                        'progress' => $friendCount,
                        'updated_at' => now(),
                        'status' => ($currentStatus === 'earned' || $isEarned) ? 'earned' : 'locked',
                        'earned_at' => ($isEarned && $currentStatus !== 'earned') ? now() : DB::raw('earned_at')
                    ]
                );
            }
        }

        // 1. Get ALL badges with user progress
        $allBadges = DB::table('badge')
            ->leftJoin('badge_category', 'badge.category_code', '=', 'badge_category.code')
            ->select(
                'badge.*',
                'badge_category.name as category_name',
                'badge_category.color as category_color'
            )
            ->get();
            
        // 2. Get user specific progress
        $userBadges = DB::table('user_badge')
            ->where('user_id', $userId)
            ->get()
            ->keyBy('badge_code');

        // 3. Merge and calculate progress
        $allBadges->transform(function ($badge) use ($userBadges) {
            $userBadge = $userBadges[$badge->code] ?? null;
            
            $status = $userBadge->status ?? 'locked';
            
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
            $badge->redeemed_at = $userBadge->redeemed_at ?? null;
            $badge->earned_at = $userBadge->earned_at ?? null;
            
            // Calculate percentage
            $required = (int)($badge->points_required ?? 0);
            if ($required <= 0) $required = 100; // Default to 100 if invalid
            
            $percentage = min(100, round(($badge->progress / $required) * 100));
            $badge->progress_percentage = (int)$percentage;
            
            $badge->completed_count = $badge->progress; 
            $badge->total_requirements = $required;
            
            return $badge;
        });
        
        // Categorize badges for tabs
        $earnedBadges = $allBadges->where('is_earned', true)->where('is_redeemed', false);
        $redeemedBadges = $allBadges->where('is_redeemed', true);
        $inProgressBadges = $allBadges->where('progress_percentage', '>', 0)->where('is_earned', false);
        
        $totalXP = $redeemedBadges->sum('xp_reward');
        
        $stats = [
            'total_earned' => $earnedBadges->count(),
            'total_redeemed' => $redeemedBadges->count(),
            'total_xp' => $totalXP,
            'total_badges' => $allBadges->count(),
            'in_progress' => $inProgressBadges->count(),
            'locked' => $allBadges->count() - ($earnedBadges->count() + $redeemedBadges->count() + $inProgressBadges->count())
        ];

        // Filter functionality parameters
        $category = request('category', 'all');
        $status = request('status', 'all');
        $sort = request('sort', 'recent');
        $categories = DB::table('badge_category')->get();
        
        return view('badges.my', compact(
            'allBadges',
            'earnedBadges',
            'redeemedBadges',
            'stats',
            'categories',
            'category',
            'status',
            'sort'
        ));
    }

    // API to get redeemable badges
    public function getRedeemableBadges()
    {
        // Gunakan session user ID
        if (!Session::has('user_id')) {
            Session::put('user_id', 'user_' . uniqid());
        }
        $userId = Session::get('user_id');

        $redeemableBadges = DB::table('user_badge')
            ->where('user_id', $userId)
            ->where('status', 'earned') // Using status column
            ->join('badge', 'user_badge.badge_code', '=', 'badge.code')
            ->select('badge.*', 'user_badge.progress')
            ->get()
            ->map(function($badge) {
                return [
                    'code' => $badge->code,
                    'name' => $badge->name_bm ?? $badge->name,
                    'xp_reward' => $badge->xp_reward,
                    'progress' => $badge->progress,
                ];
            });

        return response()->json([
            'success' => true,
            'count' => count($redeemableBadges),
            'badges' => $redeemableBadges
        ]);
    }

    // API to redeem badge
    public function redeemBadge(Request $request)
    {
        $request->validate([
            'badge_id' => 'required'
        ]);

        // Gunakan session user ID
        if (!Session::has('user_id')) {
            Session::put('user_id', 'user_' . uniqid());
        }
        $userId = Session::get('user_id');

        $badgeId = $request->badge_id;
        
        // Find badge by code
        $badge = DB::table('badge')->where('code', $badgeId)->first();
        
        if (!$badge) {
            return response()->json([
                'success' => false,
                'message' => 'Badge not found'
            ], 404);
        }

        $userBadge = DB::table('user_badge')
            ->where('user_id', $userId)
            ->where('badge_code', $badge->code)
            ->first();

        if (!$userBadge) {
            return response()->json([
                'success' => false,
                'message' => 'You have not earned this badge yet'
            ], 400);
        }
        
        $status = $userBadge->status ?? 'locked';

        if ($status === 'locked') {
            return response()->json([
                'success' => false,
                'message' => 'You need to earn this badge first'
            ], 400);
        }

        if ($status === 'redeemed') {
            return response()->json([
                'success' => false,
                'message' => 'Already redeemed this badge'
            ], 400);
        }

        try {
            // Update to redeemed
            DB::table('user_badge')
                ->where('id', $userBadge->id)
                ->update([
                    'status' => 'redeemed',
                    'redeemed_at' => now(),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Badge redeemed successfully!',
                'data' => [
                    'badge' => [
                        'name' => $badge->name_bm ?? $badge->name,
                        'xp_reward' => $badge->xp_reward
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // API to earn badge points
    public function earnBadgePoints(Request $request)
    {
        $request->validate([
            'badge_id' => 'required',
            'points' => 'required|integer|min:1'
        ]);

        // Gunakan session user ID
        if (!Session::has('user_id')) {
            Session::put('user_id', 'user_' . uniqid());
        }
        $userId = Session::get('user_id');

        $badgeId = $request->badge_id;
        $points = $request->points;
        
        // Find badge
        $badge = DB::table('badge')->where('code', $badgeId)->first();
        
        if (!$badge) {
            return response()->json([
                'success' => false,
                'message' => 'Badge not found'
            ], 404);
        }

        $userBadge = DB::table('user_badge')
            ->where('user_id', $userId)
            ->where('badge_code', $badge->code)
            ->first();

        if ($userBadge) {
            // Update existing
            DB::table('user_badge')
                ->where('id', $userBadge->id)
                ->update([
                    'progress' => DB::raw('progress + ' . $points),
                    'updated_at' => now()
                ]);
                
            // Check if earned
            $updatedBadge = DB::table('user_badge')->where('id', $userBadge->id)->first();
            $status = $updatedBadge->status ?? 'locked';
            
            if ($updatedBadge->progress >= ($badge->points_required ?? 100) && $status === 'locked') {
                DB::table('user_badge')
                    ->where('id', $userBadge->id)
                    ->update([
                        'status' => 'earned', // Update status instead of boolean
                        'earned_at' => now()
                    ]);
                
                // Create notification for badge earned
                \App\Models\Notification::create([
                    'user_id' => $userId,
                    'type' => 'badge_earned',
                    'title' => 'Lencana Diperoleh!',
                    'message' => 'Tahniah! Anda telah memperoleh lencana "' . $badge->name . '"',
                    'related_type' => 'badge',
                    'related_id' => $badge->id,
                    'is_read' => false,
                ]);
            }
        } else {
            // Create new
            $isEarned = $points >= ($badge->points_required ?? 100);
            DB::table('user_badge')->insert([
                'user_id' => $userId,
                'badge_code' => $badge->code,
                'progress' => $points,
                'status' => $isEarned ? 'earned' : 'locked', // Use status
                'earned_at' => $isEarned ? now() : null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Create notification if badge is earned immediately
            if ($isEarned) {
                \App\Models\Notification::create([
                    'user_id' => $userId,
                    'type' => 'badge_earned',
                    'title' => 'Lencana Diperoleh!',
                    'message' => 'Tahniah! Anda telah memperoleh lencana "' . $badge->name . '"',
                    'related_type' => 'badge',
                    'related_id' => $badge->id,
                    'is_read' => false,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Points added successfully!',
            'data' => [
                'badge_code' => $badge->code,
                'points_added' => $points,
                'total_points' => ($userBadge->progress ?? 0) + $points, // Fix undefined variable access if userBadge null
                'points_required' => $badge->points_required ?? 100
            ]
        ]);
    }



    // Other methods...
    public function index()
    {
        return view('achievements.index');
    }
    
    public function updateProgress(Request $request)
    {
        // Your existing code
    }
}