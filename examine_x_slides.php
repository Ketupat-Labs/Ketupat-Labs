<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== EXAMINING GENERATED SLIDE DATA ===\n\n";

// Find the slide set with topic "x"
$users = DB::table('user')->select('id', 'username')->get();

foreach ($users as $user) {
    $cacheKey = 'user_' . $user->id . '_slide_sets';
    $slideSets = Cache::get($cacheKey, []);

    foreach ($slideSets as $set) {
        if (isset($set['topic']) && strtolower($set['topic']) === 'x') {
            echo "Found slide set 'x' for user {$user->username} (ID: {$user->id})\n\n";
            echo "RAW DATA:\n";
            echo json_encode($set, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

            if (isset($set['slides']) && is_array($set['slides'])) {
                echo "Number of slides: " . count($set['slides']) . "\n\n";

                foreach ($set['slides'] as $i => $slide) {
                    echo "--- Slide " . ($i + 1) . " ---\n";
                    echo "Title: " . ($slide['title'] ?? 'MISSING') . "\n";
                    echo "Content type: " . gettype($slide['content'] ?? null) . "\n";

                    if (isset($slide['content'])) {
                        if (is_array($slide['content'])) {
                            echo "Content items: " . count($slide['content']) . "\n";
                            foreach ($slide['content'] as $j => $item) {
                                echo "  [$j] " . (is_string($item) ? substr($item, 0, 50) : gettype($item)) . "\n";
                            }
                        } else {
                            echo "Content: " . substr((string)$slide['content'], 0, 100) . "\n";
                        }
                    }

                    echo "Summary: " . (isset($slide['summary']) ? 'YES (' . strlen($slide['summary']) . ' chars)' : 'NO') . "\n";
                    echo "\n";
                }
            } else {
                echo "⚠️  WARNING: 'slides' key is missing or not an array!\n";
                echo "Slides value type: " . gettype($set['slides'] ?? null) . "\n";
            }
        }
    }
}
