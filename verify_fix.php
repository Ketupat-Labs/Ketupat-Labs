<?php

use App\Models\Badge;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$badge = Badge::where('code', 'newcomer')->first();
$user = User::find(3);

echo "Badge: " . ($badge ? $badge->name : "MISSING") . "\n";
echo "Category: " . ($badge ? $badge->category_code : "N/A") . "\n";
echo "User 3 Has It: " . ($user && $badge && $user->badges->contains('id', $badge->id) ? "YES" : "NO") . "\n";
