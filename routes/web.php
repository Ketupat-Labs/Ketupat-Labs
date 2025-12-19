<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Favicon route
Route::get('/favicon.ico', function () {
    return redirect(asset('assets/images/LOGOCompuPlay.png'), 301);
});

// PHP Settings diagnostic route
Route::get('/php-settings', function () {
    return response()->json([
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_file_uploads' => ini_get('max_file_uploads'),
        'memory_limit' => ini_get('memory_limit'),
        'php_ini_loaded_file' => php_ini_loaded_file(),
        'php_ini_scanned_files' => php_ini_scanned_files() ?: 'None',
        'converted_bytes' => [
            'upload_max_filesize' => [
                'bytes' => (function($val) {
                    $val = trim($val);
                    $last = strtolower($val[strlen($val)-1]);
                    $val = (int)$val;
                    switch($last) {
                        case 'g': $val *= 1024;
                        case 'm': $val *= 1024;
                        case 'k': $val *= 1024;
                    }
                    return $val;
                })(ini_get('upload_max_filesize')),
                'mb' => round((function($val) {
                    $val = trim($val);
                    $last = strtolower($val[strlen($val)-1]);
                    $val = (int)$val;
                    switch($last) {
                        case 'g': $val *= 1024;
                        case 'm': $val *= 1024;
                        case 'k': $val *= 1024;
                    }
                    return $val / 1024 / 1024;
                })(ini_get('upload_max_filesize')), 2),
            ],
        ],
    ], 200, ['Content-Type' => 'application/json']);
});

Route::get('/', function () {
    return view('index');
})->name('home');

// Auth routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.login'); // Same page with registration form
})->name('register');

// Dashboard route - using DashboardController from Ketupat-Labs
// Note: DashboardController uses session('user_id') for auth, not Auth::check()
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

// Logout route
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// Test route to verify routing works
Route::get('/test-forum', function () {
    return 'Test route works!';
});

// Forum routes - using session-based auth like DashboardController
// TEMPORARY: Using /forums (plural) to avoid conflict with public/Forum directory
Route::get('/forums', function () {
    $userId = session('user_id');
    if (!$userId) {
        return redirect()->route('login');
    }
    return view('forum.forum');
})->name('forum.index');

// Also keep /forum for compatibility
Route::get('/forum', function () {
    $userId = session('user_id');
    if (!$userId) {
        return redirect()->route('login');
    }
    return view('forum.forum');
})->name('forum.index.alias');

// Forum sub-routes - these come AFTER the main /forum route
Route::prefix('forum')->group(function () {
        Route::get('/search', function () {
            return view('forum.forum-search');
        })->name('forum.search');
        
        Route::get('/create', function () {
            return view('forum.create-forum');
        })->name('forum.create');
        
        Route::get('/post/create', function () {
            return view('forum.create-post');
        })->name('forum.post.create');
        
        Route::get('/post/{id}', function ($id) {
            return view('forum.post-detail', ['id' => $id]);
        })->name('forum.post.detail');
        
        Route::get('/comment/{id}', function ($id) {
            return view('forum.comment-detail', ['id' => $id]);
        })->name('forum.comment.detail');
        
        Route::get('/manage/{id}', function ($id) {
            return view('forum.manage-forum', ['id' => $id]);
        })->name('forum.manage');
        
        // This must be last to avoid catching other routes
        Route::get('/{id}', function ($id) {
            return view('forum.forum-detail', ['id' => $id]);
        })->name('forum.detail');
    });

// Messaging routes - using session-based auth
// Note: Using direct route instead of prefix to avoid conflict with public/Messaging directory
Route::get('/friends', [\App\Http\Controllers\FriendController::class, 'index'])->name('friends.index')->middleware('auth');

Route::get('/messaging', function () {
    $userId = session('user_id');
    if (!$userId) {
        return redirect()->route('login');
    }
    return view('messaging.messaging');
})->name('messaging.index');

// Ketupat-Labs Routes - Lessons, Classrooms, Assignments, etc.
Route::middleware('auth')->group(function () {
    // Profile routes - specific routes must come before parameterized routes
    Route::get('/profile', function () {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }
        return redirect()->route('profile.show', $userId);
    });
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/{userId}', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    
    // Settings routes
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    
    // Lesson routes
    // IMPORTANT: Specific routes must come BEFORE Route::resource
    
    // Test route for new block editor (temporary - for testing)
    Route::get('/lessons/create-blocks', function () {
        return view('lessons.create-new');
    })->name('lessons.create-blocks');
    
    // Resource routes (this creates /lessons/create, /lessons/{id}, etc.)
    Route::resource('lessons', \App\Http\Controllers\LessonController::class);
    
    Route::get('/lesson', [\App\Http\Controllers\LessonController::class, 'studentIndex'])->name('lesson.index');
    Route::get('/lesson/{lesson}', [\App\Http\Controllers\LessonController::class, 'studentShow'])->name('lesson.show');
    
    // Quiz routes
    Route::get('/quiz/{lesson?}', [\App\Http\Controllers\QuizController::class, 'show'])->name('quiz.show');
    Route::post('/quiz', [\App\Http\Controllers\QuizController::class, 'submit'])->name('quiz.submit');
    
    // Submission routes
    Route::get('/submission', [\App\Http\Controllers\SubmissionController::class, 'show'])->name('submission.show');
    Route::post('/submission', [\App\Http\Controllers\SubmissionController::class, 'submit'])->name('submission.submit');
    Route::get('/submissions', [\App\Http\Controllers\SubmissionController::class, 'index'])->name('submission.index');
    Route::get('/submissions/{submission}/grading', [\App\Http\Controllers\SubmissionController::class, 'gradingView'])->name('submission.grading');
    Route::get('/submissions/{submission}/file', [\App\Http\Controllers\SubmissionController::class, 'viewFile'])->name('submission.file');
    Route::post('/submissions/{submission}/grade', [\App\Http\Controllers\SubmissionController::class, 'grade'])->name('submission.grade');
    
    // Assignment routes
    Route::resource('assignments', \App\Http\Controllers\LessonAssignmentController::class);
    
    // Enrollment routes
    Route::get('/enrollment', [\App\Http\Controllers\EnrollmentController::class, 'index'])->name('enrollment.index');
    Route::post('/enrollment', [\App\Http\Controllers\EnrollmentController::class, 'store'])->name('enrollment.store');
    
    // Monitoring routes
    Route::get('/monitoring', [\App\Http\Controllers\MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/progress', [\App\Http\Controllers\ProgressController::class, 'index'])->name('progress.index');
    Route::get('/performance', [\App\Http\Controllers\PerformanceController::class, 'index'])->name('performance.index');
    Route::match(['get', 'post'], '/schedule', [\App\Http\Controllers\ScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule/store', [\App\Http\Controllers\ScheduleController::class, 'store'])->name('schedule.store');
    
    // New Activity Management
    Route::get('/activities', [\App\Http\Controllers\ActivityController::class, 'index'])->name('activities.index');
    Route::get('/activities/create', [\App\Http\Controllers\ActivityController::class, 'create'])->name('activities.create');
    Route::post('/activities', [\App\Http\Controllers\ActivityController::class, 'store'])->name('activities.store');
    Route::post('/activities/{activity}/assign', [\App\Http\Controllers\ActivityController::class, 'assign'])->name('activities.assign');
    Route::get('/activities/{activity}', [\App\Http\Controllers\ActivityController::class, 'show'])->name('activities.show');
    
    // Classroom routes
    Route::resource('classrooms', \App\Http\Controllers\ClassroomController::class);
    Route::post('/classrooms/{classroom}/students', [\App\Http\Controllers\ClassroomController::class, 'addStudent'])->name('classrooms.students.add');
    Route::delete('/classrooms/{classroom}/students/{student}', [\App\Http\Controllers\ClassroomController::class, 'removeStudent'])->name('classrooms.students.remove');
    
    // AI Generator routes
    Route::get('/ai-generator', [\App\Http\Controllers\AIGeneratorController::class, 'index'])->name('ai-generator.index');
    Route::get('/ai-generator/slides', function () {
        return view('ai-generator.slides');
    })->name('ai-generator.slides');
    Route::get('/ai-generator/quiz', function () {
        return view('ai-generator.quiz');
    })->name('ai-generator.quiz');
});

