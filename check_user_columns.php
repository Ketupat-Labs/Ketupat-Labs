<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$columns = DB::getSchemaBuilder()->getColumnListing('user');
echo "User table columns: " . implode(', ', $columns) . "\n";
