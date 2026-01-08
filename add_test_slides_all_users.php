<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ADD TEST SLIDES FOR ALL USERS ===\n\n";

// Get all users
$users = DB::table('user')->select('id', 'username')->get();

foreach ($users as $user) {
    $userId = $user->id;
    $cacheKey = 'user_' . $userId . '_slide_sets';

    // Create test slide for this user
    $testSlides = [
        [
            'id' => 'test_' . $userId . '_' . time(),
            'topic' => 'Test Slide for ' . $user->username,
            'slides' => [
                [
                    'title' => 'Test Slide 1',
                    'content' => 'This is a test slide'
                ]
            ],
            'status' => 'completed',
            'created_at' => now()->format('Y-m-d H:i:s'),
            'slide_count' => 1
        ]
    ];

    // Save to cache (24 hours)
    Cache::put($cacheKey, $testSlides, 86400);

    echo "✓ Added test slide for User ID $userId ($user->username)\n";

    // Verify
    $retrieved = Cache::get($cacheKey);
    echo "  Retrieved " . count($retrieved) . " slide sets\n\n";
}

echo "\n✅ ALL USERS NOW HAVE TEST SLIDES!\n";
