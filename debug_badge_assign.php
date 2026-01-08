<?php

use App\Models\Badge;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Badge Search ---\n";
// Try to find the badge
$badge = Badge::where('name', 'like', '%Pengguna Baru%')
              ->orWhere('name_bm', 'like', '%Pengguna Baru%')
              ->orWhere('code', 'like', '%new_user%')
              ->first();

if (!$badge) {
    echo "Badge 'Pengguna Baru' NOT FOUND!\n";
} else {
    echo "Found Badge: {$badge->name} (Code: {$badge->code}, ID: {$badge->id})\n";
    echo "Category: {$badge->category_code}\n";
    
    echo "\n--- User Assignments ---\n";
    $users = User::with('badges')->get();
    foreach ($users as $u) {
        $hasBadge = $u->badges->contains('id', $badge->id);
        echo "User [{$u->id}] {$u->name} ({$u->email}) - Roles: " . implode(',', $u->getRoleNames()->toArray()) . "\n";
        echo "  - Has 'Pengguna Baru'?: " . ($hasBadge ? "YES" : "NO") . "\n";
        echo "  - Total Badges: " . $u->badges->count() . "\n";
    }
}
