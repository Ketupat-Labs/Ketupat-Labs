<?php

use App\Models\BadgeCategory;
use App\Models\Badge;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Categories:\n";
$categories = BadgeCategory::all();
foreach ($categories as $c) {
    echo "- [{$c->id}] {$c->code} ({$c->name}) - Role: {$c->role_restriction}\n";
}

echo "\nBadges (Sample):\n";
$badges = Badge::limit(10)->get();
foreach ($badges as $b) {
    echo "- [{$b->id}] {$b->code} ({$b->name}) -> Cat: {$b->category_code}\n";
}
