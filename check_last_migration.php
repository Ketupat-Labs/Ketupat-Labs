<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$last = \Illuminate\Support\Facades\DB::table('migrations')->orderBy('id', 'desc')->first();
if ($last) {
    echo "Last Migration: " . $last->migration . "\n";
    echo "Batch: " . $last->batch . "\n";
} else {
    echo "No migrations found.\n";
}
