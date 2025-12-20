<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userId = 9;
echo "Debugging for User ID: $userId\n";

// 1. Check Newcomer Badge
$badge = DB::table('badge')->where('code', 'newcomer')->first();
if (!$badge) {
    echo "Badge 'newcomer' NOT found in DB!\n";
} else {
    echo "Badge 'newcomer' Found! Points: " . ($badge->points_required ?? 'NULL') . "\n";
}

// 2. Check if User has it
$hasBadge = DB::table('user_badge')
    ->where('user_id', $userId)
    ->where('badge_code', 'newcomer')
    ->exists();

if ($hasBadge) {
    echo "User ALREADY has 'newcomer' badge.\n";
} else {
    echo "User MISSING 'newcomer' badge. Awarding now...\n";
    if ($badge) {
        DB::table('user_badge')->insert([
            'user_id' => $userId,
            'badge_code' => 'newcomer',
            'status' => 'earned', // Auto-earn on first check
            'earned_at' => now(),
            'progress' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "Awarded 'newcomer' badge!\n";
    }
}

// 3. Final Verification
$userBadges = DB::table('user_badge')->where('user_id', $userId)->get();
echo "\n--- Current User Badges ---\n";
foreach ($userBadges as $ub) {
    echo "- Code: " . str_pad($ub->badge_code, 20) . " Status: " . $ub->status . "\n";
}
