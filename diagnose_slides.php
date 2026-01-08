<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SLIDE GENERATION DIAGNOSTIC ===\n\n";

// Check all possible user IDs in database
try {
    $users = DB::table('user')->select('id', 'username', 'email')->limit(10)->get();

    echo "📊 Checking slides for " . $users->count() . " users:\n\n";    foreach ($users as $user) {
        echo "User ID {$user->id} ({$user->username}):\n";

        // Check cache
        $cacheKey = 'user_' . $user->id . '_slide_sets';
        $slideSets = Cache::get($cacheKey, null);

        if ($slideSets) {
            echo "  ✓ Found " . count($slideSets) . " slide sets in cache\n";
            foreach ($slideSets as $set) {
                echo "    - " . ($set['topic'] ?? 'No topic') . " (" . ($set['status'] ?? 'unknown') . ")\n";
            }
        } else {
            echo "  ✗ No slides in cache\n";
        }

        // Check generation status
        $sessionKey = 'slide_generation_' . $user->id;
        $status = Cache::get($sessionKey . '_status', 'none');
        echo "  Status: $status\n";

        if ($status === 'error') {
            $error = Cache::get($sessionKey . '_error', 'Unknown');
            echo "  Error: $error\n";
        }

        echo "\n";
    }

    // Check cache table directly
    echo "\n📁 Checking cache table:\n";
    $cacheEntries = DB::table('cache')
        ->where('key', 'like', '%slide%')
        ->orWhere('key', 'like', '%user_%')
        ->get();

    echo "Found " . $cacheEntries->count() . " cache entries related to slides/users\n\n";

    foreach ($cacheEntries as $entry) {
        echo "  Key: " . $entry->key . "\n";
        echo "  Expires: " . date('Y-m-d H:i:s', $entry->expiration) . "\n";

        // Try to unserialize and show data
        $value = unserialize($entry->value);
        if (is_array($value)) {
            echo "  Data: " . json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "  Data: " . (is_string($value) ? substr($value, 0, 100) : gettype($value)) . "\n";
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
