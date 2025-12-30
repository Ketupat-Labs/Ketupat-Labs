<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "class: " . (Schema::hasTable('class') ? 'YES' : 'NO') . "\n";
echo "classrooms: " . (Schema::hasTable('classrooms') ? 'YES' : 'NO') . "\n";
echo "ai_generated_content: " . (Schema::hasTable('ai_generated_content') ? 'YES' : 'NO') . "\n";
