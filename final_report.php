<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FINAL DIAGNOSTIC REPORT ===\n\n";

// 1. Show all users with slide counts
echo "📊 SLIDE COUNTS PER USER:\n";
echo str_repeat("-", 60) . "\n";

$users = DB::table('user')->select('id', 'username', 'email')->get();

foreach ($users as $user) {
    $cacheKey = 'user_' . $user->id . '_slide_sets';
    $slides = Cache::get($cacheKey, []);
    $count = count($slides);

    $status = $count > 0 ? "✓" : "✗";
    echo sprintf("%s User ID %d (%s): %d slide sets\n", $status, $user->id, $user->username, $count);

    if ($count > 0) {
        foreach ($slides as $slide) {
            echo "    - " . ($slide['topic'] ?? 'No topic') . " [" . ($slide['status'] ?? 'unknown') . "]\n";
        }
    }
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// 2. Show cache entries
echo "📁 ALL SLIDE-RELATED CACHE ENTRIES:\n";
$cacheEntries = DB::table('cache')
    ->where(function($query) {
        $query->where('key', 'like', '%slide%')
              ->orWhere('key', 'like', '%user_%');
    })
    ->get();

echo "Found " . $cacheEntries->count() . " entries\n\n";

foreach ($cacheEntries as $entry) {
    $key = str_replace('laravel-cache-', '', $entry->key);
    $expires = date('Y-m-d H:i:s', $entry->expiration);
    echo "  🔑 $key (expires: $expires)\n";
}

echo "\n" . str_repeat("=", 60) . "\n\n";

echo "💡 INSTRUCTIONS:\n";
echo "1. Check which user you are logged in as\n";
echo "2. Make sure session('user_id') matches one of the users above\n";
echo "3. If you're logged in as User ID 2 or 3, you should see the test slides!\n";
echo "4. To generate REAL slides, click 'Jana Slaid' button while logged in\n";

