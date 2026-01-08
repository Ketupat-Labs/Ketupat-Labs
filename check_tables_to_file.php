<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $columns = DB::select('DESCRIBE message');
    $output = "Message Table Columns:\n";
    foreach ($columns as $col) {
        $output .= $col->Field . " (" . $col->Type . ")\n";
    }
    file_put_contents('users_columns.txt', $output);
    echo "Written to users_columns.txt";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
