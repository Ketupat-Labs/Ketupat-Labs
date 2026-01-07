<?php

use App\Models\Badge;
use App\Models\BadgeCategory;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$badge = Badge::where('code', 'newcomer')->first();
if (!$badge) {
    echo "Badge 'newcomer' NOT FOUND.\n";
    exit;
}

$pencapaian = BadgeCategory::where('code', 'pencapaian')->first();
if (!$pencapaian) {
    echo "Category 'pencapaian' NOT FOUND.\n";
    exit;
}

// Update Badge
$badge->category_id = $pencapaian->id;
$badge->category_slug = 'pencapaian';
// Do NOT set category_code as it likely doesn't exist
$badge->save();
echo "Badge updated to category 'pencapaian'.\n";

// Award to All Users
$users = User::all();
echo "Found " . $users->count() . " users. Awarding 'newcomer' badge...\n";

foreach ($users as $user) {
    if (!$user->badges->contains('id', $badge->id)) {
        $user->badges()->attach($badge->id);
        echo " - Awarded to User ID {$user->id} ({$user->email})\n";
    } else {
        echo " - User ID {$user->id} already has it.\n";
    }
}

// Final Verify
$freshBadge = Badge::find($badge->id);
echo "Final State - CatID: {$freshBadge->category_id}, Slug: {$freshBadge->category_slug}\n";
