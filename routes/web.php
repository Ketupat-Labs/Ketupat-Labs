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
        ['name' => 'Ali Ahmad', 'class' => '5A'],
        ['name' => 'Siti Rahman', 'class' => '5A'],
        ['name' => 'Wei Chen', 'class' => '5A'],
        ['name' => 'Raj Kumar', 'class' => '5B'],
        ['name' => 'Mei Ling', 'class' => '5B'],
    ];

    foreach ($students as $student) {
        \App\Models\Student::create($student);
    }

    // Create sample lessons
    $lessons = [
        ['title' => 'Math - Addition', 'class' => '5A', 'total_questions' => 5],
        ['title' => 'Math - Subtraction', 'class' => '5A', 'total_questions' => 5],
        ['title' => 'Science - Plants', 'class' => '5A', 'total_questions' => 4],
        ['title' => 'Math - Addition', 'class' => '5B', 'total_questions' => 5],
        ['title' => 'Math - Multiplication', 'class' => '5B', 'total_questions' => 5],
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
                
                for ($i = 1; $i <= $lesson->total_questions; $i++) {
                    $isCorrect = rand(0, 1);
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
