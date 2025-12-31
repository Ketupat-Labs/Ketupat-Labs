<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "documents: " . (Schema::hasTable('documents') ? 'YES' : 'NO') . "\n";
echo "enrollment: " . (Schema::hasTable('enrollment') ? 'YES' : 'NO') . "\n";
echo "report: " . (Schema::hasTable('report') ? 'YES' : 'NO') . "\n";
echo "user: " . (Schema::hasTable('user') ? 'YES' : 'NO') . "\n";
echo "lesson: " . (Schema::hasTable('lesson') ? 'YES' : 'NO') . "\n";
