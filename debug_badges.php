<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Badge;
use App\Models\BadgeCategory;

echo "--- Debugging Badges and Categories ---\n";

// 1. Check Schema
echo "\n1. Checking Schema:\n";
echo "Table 'badges' exists: " . (Schema::hasTable('badges') ? "YES" : "NO") . "\n";
if (Schema::hasTable('badges')) {
    $columns = Schema::getColumnListing('badges');
    echo "'badges' columns: " . implode(', ', $columns) . "\n";
}

echo "Table 'badge_categories' exists: " . (Schema::hasTable('badge_categories') ? "YES" : "NO") . "\n";
if (Schema::hasTable('badge_categories')) {
    $columns = Schema::getColumnListing('badge_categories');
    echo "'badge_categories' columns: " . implode(', ', $columns) . "\n";
}

// 2. Check Data Counts
echo "\n2. Checking Data Counts:\n";
try {
    $badgeCount = DB::table('badges')->count();
    echo "Badges count: $badgeCount\n";

    $categoryCount = DB::table('badge_categories')->count();
    echo "Categories count: $categoryCount\n";
} catch (\Exception $e) {
    echo "Error counting data: " . $e->getMessage() . "\n";
}

// 3. Check Relationships
echo "\n3. Checking Relationships:\n";
try {
    $badges = Badge::limit(5)->get();
    foreach ($badges as $badge) {
        echo "Badge: " . ($badge->name ?? 'Unknown') . " (ID: " . $badge->id . ")\n";
        echo "  - Category ID: " . ($badge->category_id ?? 'NULL') . "\n";
        echo "  - Category Slug: " . ($badge->category_slug ?? 'NULL') . "\n";

        try {
            $category = $badge->category;
            echo "  - Linked Category: " . ($category ? $category->name . " (ID: {$category->id})" : "NONE") . "\n";
        } catch (\Exception $e) {
            echo "  - Error fetching category: " . $e->getMessage() . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Error fetching badges: " . $e->getMessage() . "\n";
}

// 4. Check Categories
echo "\n4. Sample Categories:\n";
try {
    $categories = BadgeCategory::limit(5)->get();
    foreach ($categories as $cat) {
        echo "Category: " . $cat->name . " (ID: " . $cat->id . ", Code/Slug: " . ($cat->code ?? $cat->slug ?? 'NULL') . ")\n";
    }
} catch (\Exception $e) {
    echo "Error fetching categories: " . $e->getMessage() . "\n";
}
