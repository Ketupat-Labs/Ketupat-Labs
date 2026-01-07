<?php

use App\Models\Badge;
use App\Models\BadgeCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$badge = Badge::where('code', 'newcomer')->first();

if (!$badge) {
    echo "Badge 'newcomer' NOT FOUND.\n";
    exit;
}

echo "Badge: {$badge->name} ({$badge->code})\n";
echo "Current Category Code: {$badge->category_code}\n";
echo "Current Category ID: {$badge->category_id}\n";

$category = BadgeCategory::find($badge->category_id);
echo "Category Details: " . ($category ? $category->name . " ({$category->code})" : "NULL") . "\n";

// Check User 3
$user = User::find(3);
if ($user) {
    if ($user->badges->contains('id', $badge->id)) {
        echo "User 3 HAS this badge.\n";
    } else {
        echo "User 3 does NOT have this badge. Awarding now...\n";
        $user->badges()->attach($badge->id);
        echo "Awarded!\n";
    }
}

// Fix Category if needed
if ($badge->category_code !== 'pencapaian' || !$category || $category->code !== 'pencapaian') {
    echo "Fixing category to 'pencapaian'...\n";
    $pencapaian = BadgeCategory::where('code', 'pencapaian')->first();
    if ($pencapaian) {
        $badge->category_code = 'pencapaian';
        $badge->category_slug = 'pencapaian'; // Legacy support
        $badge->category_id = $pencapaian->id;
        $badge->save();
        echo "Category updated to 'pencapaian'.\n";
    } else {
        echo "Error: 'pencapaian' category not found!\n";
    }
}
