<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Http\Controllers\EnrollmentController;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Starting Progress Logic Test...\n";

    // 1. Setup User and Lesson
    $user = User::first();
    if (!$user)
        die("No user found. Please seed users first.\n");

    // Simulate login
    session(['user_id' => $user->id]);

    $lesson = Lesson::first();
    if (!$lesson) {
        $lesson = Lesson::create([
            'title' => 'Test Lesson',
            'topic' => 'Test Topic',
            'teacher_id' => $user->id,
            'is_published' => true
        ]);
        echo "Created test lesson.\n";
    }

    // 2. Setup Enrollment
    $enrollment = Enrollment::updateOrCreate(
        ['user_id' => $user->id, 'lesson_id' => $lesson->id],
        ['status' => 'enrolled', 'progress' => 0, 'completed_items' => json_encode([])]
    );
    echo "Enrollment ID: " . $enrollment->id . "\n";

    // 3. Test Update Progress
    $controller = new EnrollmentController();
    $request = Request::create('/enrollment/' . $enrollment->id . '/progress', 'POST', [
        'item_id' => 'block_1',
        'status' => 'completed',
        'total_items' => 5
    ]);

    echo "Simulating Progress Update (block_1, total 5)...\n";
    $response = $controller->updateProgress($request, $enrollment->id);
    $data = $response->getData(true);

    print_r($data);

    if ($data['success'] && $data['progress'] == 20) {
        echo "PASS: Progress updated to 20%.\n";
    } else {
        echo "FAIL: Unexpected progress value.\n";
    }

    // 4. Test Second Item
    $request2 = Request::create('/enrollment/' . $enrollment->id . '/progress', 'POST', [
        'item_id' => 'block_2',
        'status' => 'completed',
        'total_items' => 5
    ]);

    echo "Simulating Progress Update (block_2, total 5)...\n";
    $response2 = $controller->updateProgress($request2, $enrollment->id);
    $data2 = $response2->getData(true);

    print_r($data2);

    if ($data2['success'] && $data2['progress'] == 40) {
        echo "PASS: Progress updated to 40%.\n";
    } else {
        echo "FAIL: Unexpected progress value.\n";
    }

    // 5. Verify DB persistence
    $refreshedEnrollment = Enrollment::find($enrollment->id);
    echo "DB Progress: " . $refreshedEnrollment->progress . "\n";
    echo "DB Items: " . $refreshedEnrollment->completed_items . "\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
