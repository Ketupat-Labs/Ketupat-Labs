<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$table = 'activity';
if (Schema::hasTable($table)) {
    $columns = Schema::getColumnListing($table);
    foreach ($columns as $column) {
        $type = Schema::getColumnType($table, $column);
        echo "$column ($type)\n";
    }
} else {
    echo "Table $table does not exist.\n";
}
