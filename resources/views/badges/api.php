<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

Route::middleware(['auth:sanctum'])->group(function () {
    // API untuk get fresh badge data
    Route::get('/my-badges-data', function (Request $request) {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Get fresh stats
        $stats = [
            'total' => DB::table('user_badges')
                ->where('user_id', Auth::id())
                ->count(),
            'approved' => DB::table('user_badges')
                ->where('user_id', Auth::id())
                ->where('status', 'approved')
                ->count(),
            'pending' => DB::table('user_badges')
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->count(),
            'xp_total' => DB::table('users')
                ->where('id', Auth::id())
                ->value('xp') ?? 0
        ];
        
        // Get fresh badges
        $badges = DB::table('user_badges')
            ->where('user_id', Auth::id())
            ->join('badges', 'user_badges.badge_code', '=', 'badges.code')
            ->join('badge_categories', 'badges.category_code', '=', 'badge_categories.code')
            ->select(
                'badges.*',
                'badge_categories.name as category_name',
                'badge_categories.color as category_color',
                'user_badges.status as badge_status',
                'user_badges.obtained_at',
                'user_badges.given_by',
                'user_badges.notes'
            )
            ->orderBy('user_badges.obtained_at', 'desc')
            ->get();
        
        // Render HTML partials
        $statsHtml = view('partials.badge-stats', ['stats' => $stats])->render();
        $badgesHtml = view('partials.badges-list', ['badges' => $badges])->render();
        
        return response()->json([
            'stats_html' => $statsHtml,
            'badges_html' => $badgesHtml,
            'timestamp' => now()->toDateTimeString()
        ]);
    });
});