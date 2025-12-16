<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- Categories ---\n";
try {
    $names = DB::table('badge_categories')->pluck('name');
    print_r($names->toArray());
} catch (\Exception $e) {
    echo "Error fetching categories: " . $e->getMessage() . "\n";
}

echo "\n--- Tables like '%progress%' ---\n";
try {
    $tables = DB::select("SHOW TABLES LIKE '%progress%'");
    foreach ($tables as $table) {
        // dynamic key access
        foreach ($table as $key => $name) {
            echo "Table: $name\n";
        }
    }
} catch (\Exception $e) {
    echo "Error fetching tables: " . $e->getMessage() . "\n";
}
