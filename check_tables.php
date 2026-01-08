<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $tables = DB::select('SHOW TABLES');
    echo "Checking for specific tables:\n";
    foreach ($tables as $table) {
        foreach ($table as $key => $value) {
            if (str_contains($value, 'message') || str_contains($value, 'user') || str_contains($value, 'post')) {
                echo $value . "\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
