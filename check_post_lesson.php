<?php

use Illuminate\Support\Facades\DB;
use App\Models\ForumPost;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$latestPost = ForumPost::latest()->first();

if ($latestPost) {
    echo "Latest Post ID: " . $latestPost->id . "\n";
    echo "Title: " . $latestPost->title . "\n";
    echo "Lesson ID: " . ($latestPost->lesson_id ?? 'NULL') . "\n";
} else {
    echo "No posts found.\n";
}
