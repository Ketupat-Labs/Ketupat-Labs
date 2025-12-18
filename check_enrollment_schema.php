<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Table: enrollments\n";
$columns = Schema::getColumnListing('enrollments');
print_r($columns);

if (in_array('completed_items', $columns)) {
    echo "Column 'completed_items' EXISTS.\n";
} else {
    echo "Column 'completed_items' MISSING.\n";
}
