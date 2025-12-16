<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\BadgeCategory;

echo "--- Cleaning Categories ---\n";

// List of invalid patterns or names to remove
// Matches migration filenames like 2025_...
$invalidPattern = '/^\d{4}_\d{2}_\d{2}_/';

$categories = BadgeCategory::all();

foreach ($categories as $cat) {
    if (preg_match($invalidPattern, $cat->name)) {
        echo "Removing invalid category: {$cat->name} (ID: {$cat->id})\n";

        // Unlink badges first? Or delete cascade?
        // Let's safe delete: set category_id to null for badges
        DB::table('badges')->where('category_id', $cat->id)->update(['category_id' => null]);

        $cat->delete();
    } else {
        echo "Keeping valid category: {$cat->name}\n";
    }
}

echo "Done cleaning.\n";
