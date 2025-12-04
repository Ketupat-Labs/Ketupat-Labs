<?php

use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ManageActivitiesController;
use Illuminate\Support\Facades\Route;

// Test data route - ADD THIS
Route::get('/add-test-data', function () {
    // Clear existing data
    \App\Models\StudentAnswer::truncate();
    \App\Models\Student::truncate();
    \App\Models\Lesson::truncate();

    // Create sample students
    $students = [
        ['name' => 'Ahmad Albab', 'class' => '5A'],
        ['name' => 'Siti Nurhaliza', 'class' => '5A'],
        ['name' => 'Chong Wei', 'class' => '5A'],
        ['name' => 'Muthusamy', 'class' => '5B'],
        ['name' => 'Nurul Izzah', 'class' => '5B'],
        ['name' => 'Adam Haikal', 'class' => '5A'],
        ['name' => 'Tan Mei Ling', 'class' => '5B'],
        ['name' => 'Raju', 'class' => '5B'],
    ];

    foreach ($students as $student) {
        \App\Models\Student::create($student);
    }

    // Create sample lessons (HCI Topics in Malay)
    $lessons = [
        ['title' => 'Pengenalan kepada HCI', 'class' => '5A', 'total_questions' => 3],
        ['title' => 'Prinsip Rekabentuk UI', 'class' => '5A', 'total_questions' => 3],
        ['title' => 'Kebolehgunaan (Usability)', 'class' => '5A', 'total_questions' => 3],
        ['title' => 'Pengenalan kepada HCI', 'class' => '5B', 'total_questions' => 3],
        ['title' => 'Prototaip dan Penilaian', 'class' => '5B', 'total_questions' => 3],
    ];

    foreach ($lessons as $lesson) {
        \App\Models\Lesson::create($lesson);
    }

    // Create sample answers
    $allStudents = \App\Models\Student::all();
    $allLessons = \App\Models\Lesson::all();

    foreach ($allStudents as $student) {
        foreach ($allLessons as $lesson) {
            if ($student->class === $lesson->class) {
                $answers = [];
                $totalMarks = 0;
                
                // Simulate marks - some low to trigger notifications
                // Randomize to get some < 20% (0 or 1 mark out of 3 is <= 33%, wait. 
                // 0/3 = 0%, 1/3 = 33%. So only 0 marks is <= 20%)
                
                // Let's force some students to fail
                $forceFail = rand(0, 10) > 7; // 30% chance to fail badly
                
                for ($i = 1; $i <= $lesson->total_questions; $i++) {
                    if ($forceFail) {
                        $isCorrect = false; // Force wrong
                    } else {
                        $isCorrect = rand(0, 10) > 3; // 70% chance correct
                    }
                    
                    $answers["q{$i}"] = (bool)$isCorrect;
                    if ($isCorrect) $totalMarks++;
                }

                \App\Models\StudentAnswer::create([
                    'student_id' => $student->student_id,
                    'lesson_id' => $lesson->id,
                    'answers' => $answers,
                    'total_marks' => $totalMarks,
                ]);
            }
        }
    }

    return "Test data added! <a href='/performance'>Go to Performance Page</a>";
});

// Main application routes
Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');
Route::get('/performance/student/{studentId}', [PerformanceController::class, 'studentDetail'])->name('performance.student');

Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

Route::get('/manage-activities', [ManageActivitiesController::class, 'index'])->name('manage-activities.index');
Route::post('/manage-activities', [ManageActivitiesController::class, 'storeAssignment'])->name('manage-activities.store');
Route::delete('/manage-activities/{id}', [ManageActivitiesController::class, 'deleteAssignment'])->name('manage-activities.delete');
Route::post('/manage-activities/resend-lesson', [ManageActivitiesController::class, 'resendLesson'])->name('manage-activities.resend-lesson');
