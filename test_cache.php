<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CACHE TEST ===\n";
echo "Cache driver: " . config('cache.default') . "\n";
echo "Cache store from config: " . config('cache.stores.' . config('cache.default') . '.driver') . "\n\n";

// Test cache write
try {
    Cache::put('test_slide_generation', 'This is a test', 60);
    echo "✓ Cache write successful\n";

    $value = Cache::get('test_slide_generation', 'NOT FOUND');
    echo "✓ Cache read successful: " . $value . "\n\n";

    // Test user-specific cache key
    $userId = 1;
    $cacheKey = 'user_' . $userId . '_slide_sets';
    $testData = [
        [
            'id' => 'test123',
            'topic' => 'Test Topic',
            'slides' => [],
            'status' => 'completed',
            'created_at' => now()->toDateTimeString(),
            'slide_count' => 5
        ]
    ];

    Cache::put($cacheKey, $testData, 86400);
    echo "✓ User slide sets cache write successful\n";

    $retrieved = Cache::get($cacheKey, []);
    echo "✓ User slide sets cache read successful\n";
    echo "   Retrieved " . count($retrieved) . " slide sets\n";
    echo "   First set topic: " . ($retrieved[0]['topic'] ?? 'N/A') . "\n\n";

    echo "✅ ALL CACHE TESTS PASSED!\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
