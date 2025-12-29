<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
echo "Tables in " . config('database.connections.mysql.database') . ":\n";
foreach ($tables as $table) {
    echo reset($table) . "\n";
}
