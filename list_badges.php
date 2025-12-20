<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$badges = DB::table('badge')->get(['id', 'code', 'name', 'icon', 'points_required', 'category_id', 'category_code']);

echo str_pad("ID", 5) . str_pad("Code", 20) . str_pad("Name", 20) . str_pad("Cat ID", 10) . str_pad("Cat Code", 15) . "Points\n";
echo str_repeat("-", 90) . "\n";

foreach ($badges as $badge) {
    echo str_pad($badge->id, 5) . 
         str_pad($badge->code ?? 'NULL', 20) . 
         str_pad(substr($badge->name, 0, 18), 20) . 
         str_pad($badge->category_id ?? 'NULL', 10) . 
         str_pad($badge->category_code ?? 'NULL', 15) . 
         $badge->points_required . "\n";
}
