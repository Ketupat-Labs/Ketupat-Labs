<?php
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('index');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

// Protected routes - check session instead of Laravel auth
Route::middleware('session.auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('lessons', LessonController::class);
    
    // Student lesson viewing route (singular /lesson)
    Route::get('/lesson', [LessonController::class, 'studentIndex'])->name('lesson.index');
    Route::get('/lesson/{lesson}', [LessonController::class, 'studentShow'])->name('lesson.show');
    
    // Quiz routes
    Route::get('/quiz/{lesson?}', [\App\Http\Controllers\QuizController::class, 'show'])->name('quiz.show');
    Route::post('/quiz', [\App\Http\Controllers\QuizController::class, 'submit'])->name('quiz.submit');
    
    // Submission routes
    Route::get('/submission', [\App\Http\Controllers\SubmissionController::class, 'show'])->name('submission.show');
    Route::post('/submission', [\App\Http\Controllers\SubmissionController::class, 'submit'])->name('submission.submit');

    // Assign routes
    Route::get('/lessons/assign', [LessonController::class, 'showAssignForm'])->name('lessons.assign.form');
    Route::post('/lessons/assign', [LessonController::class, 'assignLessons'])->name('lessons.assign.store');
});

require __DIR__.'/auth.php';


