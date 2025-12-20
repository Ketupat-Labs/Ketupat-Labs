<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$code = 'coder';

echo "Deleting badge: $code\n";

// 1. Delete relations
$deletedRelations = DB::table('user_badge')->where('badge_code', $code)->delete();
echo "Deleted $deletedRelations user_badge records.\n";

// 2. Delete badge
$deletedBadge = DB::table('badge')->where('code', $code)->delete();
echo "Deleted $deletedBadge badge records.\n";
