<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('index');
})->name('index');
Route::get('/documents', function () {
    return view('documents');
})->middleware(['auth', 'verified'])->name('documents');

Route::get('/ai-analyzer', function () {
    return view('ai-analyzer');
})->middleware(['auth', 'verified'])->name('ai.analyzer');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/picture', [ProfileController::class, 'uploadProfilePicture'])->name('profile.picture.upload');
    Route::delete('/profile/picture', [ProfileController::class, 'deleteProfilePicture'])->name('profile.picture.delete');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::put('/profile/settings', [ProfileController::class, 'updateSettings'])->name('profile.settings.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// AI Content Generation Routes (US024)
Route::middleware(['auth'])->prefix('api/ai-content')->group(function () {
    Route::post('/analyze', [\App\Http\Controllers\AiContentController::class, 'analyze'])->name('ai.analyze');
    Route::get('/', [\App\Http\Controllers\AiContentController::class, 'index'])->name('ai.index');
    Route::get('/{id}', [\App\Http\Controllers\AiContentController::class, 'show'])->name('ai.show');
    Route::put('/{id}', [\App\Http\Controllers\AiContentController::class, 'update'])->name('ai.update');
    Route::post('/{id}/share', [\App\Http\Controllers\AiContentController::class, 'share'])->name('ai.share');
    Route::post('/{id}/export', [\App\Http\Controllers\AiContentController::class, 'export'])->name('ai.export');
    Route::delete('/{id}', [\App\Http\Controllers\AiContentController::class, 'destroy'])->name('ai.destroy');
});

// AI Text Explanation Routes (US025)
Route::middleware(['auth'])->prefix('api/ai-explain')->group(function () {
    Route::post('/explain', [\App\Http\Controllers\AiExplanationController::class, 'explain'])->name('ai.explain');
    Route::get('/history', [\App\Http\Controllers\AiExplanationController::class, 'history'])->name('ai.explain.history');
});

// Search Routes
Route::middleware(['auth'])->prefix('api/search')->group(function () {
    Route::get('/', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');
    Route::get('/suggestions', [\App\Http\Controllers\SearchController::class, 'suggestions'])->name('search.suggestions');
});

// Notification Routes
Route::middleware(['auth'])->prefix('api/notifications')->group(function () {
    Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/{id}/unread', [\App\Http\Controllers\NotificationController::class, 'markAsUnread'])->name('notifications.mark-unread');
    Route::post('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/clear/read', [\App\Http\Controllers\NotificationController::class, 'clearRead'])->name('notifications.clear-read');
});

// Activity Log Routes
Route::middleware(['auth'])->prefix('api/activity-logs')->group(function () {
    Route::get('/', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/{id}', [\App\Http\Controllers\ActivityLogController::class, 'show'])->name('activity-logs.show');
    Route::get('/export/csv', [\App\Http\Controllers\ActivityLogController::class, 'export'])->name('activity-logs.export');
});

// Help/FAQ Routes
Route::get('/help', function () {
    return view('help.index');
})->name('help.index');

Route::get('/faq', function () {
    return view('help.faq');
})->name('help.faq');

// This file includes all the routes for login, register, password reset, etc.
require __DIR__.'/auth.php';
