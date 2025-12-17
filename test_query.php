<?php

use App\Models\User;
use App\Models\Activity;
use App\Models\ActivitySubmission;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::where('role', 'teacher')->first();
if (!$user) {
    echo "No teacher found.\n";
    exit;
}

echo "Teacher: " . $user->id . "\n";

$activities = Activity::take(5)->get();
echo "Activities: " . $activities->count() . "\n";

try {
    $exists = ActivitySubmission::where('user_id', $user->id)
        ->whereHas('assignment', function($q) use ($activities) {
            $q->whereIn('activity_id', $activities->pluck('id'));
        })->exists();

    echo "Query Result: " . ($exists ? 'true' : 'false') . "\n";
    echo "SUCCESS: Query ran without error.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
