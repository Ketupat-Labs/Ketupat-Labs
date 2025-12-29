<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "user: " . (Schema::hasTable('user') ? 'YES' : 'NO') . "\n";
echo "users: " . (Schema::hasTable('users') ? 'YES' : 'NO') . "\n";
echo "forum_post: " . (Schema::hasTable('forum_post') ? 'YES' : 'NO') . "\n";
echo "forum_posts: " . (Schema::hasTable('forum_posts') ? 'YES' : 'NO') . "\n";
echo "comment: " . (Schema::hasTable('comment') ? 'YES' : 'NO') . "\n";
echo "comments: " . (Schema::hasTable('comments') ? 'YES' : 'NO') . "\n";
