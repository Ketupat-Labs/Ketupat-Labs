<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\MessagingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\LinkPreviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Temporary Email Diagnostic Route
Route::get('/auth/test-email', function (Request $request) {
    $email = $request->query('email');
    if (!$email) {
        return response()->json(['error' => 'Sila masukkan parameter emel (?email=...)'], 400);
    }
    
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $result = \App\Services\EmailService::sendOtpEmail($email, $otp);
    
    return response()->json([
        'success' => $result,
        'message' => $result ? "Emel ujian dihantar ke $email" : "Gagal menghantar emel ujian ke $email",
        'debug_info' => config('app.debug') ? [
            'host' => env('MAIL_HOST'),
            'port' => env('MAIL_PORT'),
            'user' => env('MAIL_USERNAME'),
            'encryption' => env('MAIL_ENCRYPTION'),
        ] : 'Debug info disabled'
    ]);
});

Route::post('/auth/forgot-password', [AuthController::class, 'sendPasswordResetLink']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Link preview endpoint (public, but can be rate-limited)
Route::get('/link-preview', [LinkPreviewController::class, 'fetch']);

// Protected routes
Route::middleware('auth:web')->group(function () {
    // Auth routes
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Upload route
    Route::post('/upload', [UploadController::class, 'upload']);

    // Forum routes - specific routes must come before parameterized routes
    Route::prefix('forum')->group(function () {
        Route::post('/', [ForumController::class, 'createForum']);
        Route::get('/', [ForumController::class, 'getForums']);
        Route::get('/categories', [ForumController::class, 'getCategories']);
        Route::get('/tags', [ForumController::class, 'getAllTags']);

        // Post routes - must come before /{id} route
        Route::post('/post', [ForumController::class, 'createPost']);
        Route::get('/post', [ForumController::class, 'getPosts']);
        Route::get('/post/{postId}/comments', [ForumController::class, 'getComments']);
        Route::put('/post/{id}', [ForumController::class, 'editPost']);
        Route::delete('/post/{id}', [ForumController::class, 'deletePost']);

        // Comment and other routes
        Route::post('/comment', [ForumController::class, 'createComment']);
        Route::put('/comment/{id}', [ForumController::class, 'editComment']);
        Route::delete('/comment/{id}', [ForumController::class, 'deleteComment']);
        Route::post('/react', [ForumController::class, 'react']);
        Route::post('/poll/vote', [ForumController::class, 'votePoll']);
        Route::post('/bookmark', [ForumController::class, 'bookmark']);
        Route::get('/saved-posts', [ForumController::class, 'getSavedPosts']);
        Route::post('/join', [ForumController::class, 'joinForum']);
        Route::post('/leave', [ForumController::class, 'leaveForum']);
        Route::post('/{id}/mute', [ForumController::class, 'muteForum']);
        Route::post('/{id}/unmute', [ForumController::class, 'unmuteForum']);

        // Report routes
        Route::post('/post/report', [ForumController::class, 'reportPost']);
        Route::get('/post/{postId}/reports', [ForumController::class, 'getPostReports']);
        Route::post('/post/{id}/hide', [ForumController::class, 'hidePost']);
        Route::get('/{forumId}/reports', [ForumController::class, 'getForumReports']);
        Route::put('/report/{reportId}/status', [ForumController::class, 'updateReportStatus']);

        // Forum management routes - must come before /{id} route
        Route::get('/{id}/members', [ForumController::class, 'getForumMembers']);
        Route::post('/{id}/members/promote', [ForumController::class, 'promoteMemberToAdmin']);
        Route::delete('/{id}/members', [ForumController::class, 'removeMember']);
        Route::put('/{id}', [ForumController::class, 'updateForum']);
        Route::delete('/{id}', [ForumController::class, 'deleteForum']);

        // This must be last to avoid catching other routes
        Route::get('/{id}', [ForumController::class, 'getForum']);
    });

    // Messaging routes
    Route::prefix('messaging')->group(function () {
        Route::post('/send', [MessagingController::class, 'sendMessage']);
        Route::get('/conversations', [MessagingController::class, 'getConversations']);
        Route::post('/conversation/direct', [MessagingController::class, 'getOrCreateDirectConversation']);
        Route::get('/conversation/{conversationId}/messages', [MessagingController::class, 'getMessages']);
        Route::get('/search', [MessagingController::class, 'searchMessages']);
        Route::post('/status', [MessagingController::class, 'updateOnlineStatus']);
        Route::delete('/message/{messageId}', [MessagingController::class, 'deleteMessage']);
        Route::delete('/message/{messageId}/permanent', [MessagingController::class, 'permanentlyDeleteMessage']);
        Route::post('/group', [MessagingController::class, 'createGroupChat']);
        Route::post('/group/{conversationId}/rename', [MessagingController::class, 'renameGroup']);
        Route::post('/group/members', [MessagingController::class, 'manageGroupMembers']);
        Route::post('/archive', [MessagingController::class, 'archiveConversation']);
        Route::get('/archived', [MessagingController::class, 'getArchivedConversations']);
        Route::delete('/conversation/{conversationId}', [MessagingController::class, 'deleteConversation']);
        Route::delete('/conversation/{conversationId}/messages', [MessagingController::class, 'clearAllMessages']);
        Route::get('/available-users', [MessagingController::class, 'getAvailableUsers']);
        Route::post('/message/{messageId}/reaction', [MessagingController::class, 'toggleReaction']);
        Route::get('/message/{messageId}/reactions', [MessagingController::class, 'getMessageReactions']);
    });

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/{id}/redirect', [NotificationController::class, 'getRedirectUrl']);
        Route::post('/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('/read-all', [NotificationController::class, 'markRead']); // mark_all=true
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'clearAll']);
    });


    // Mention routes
    Route::prefix('mentions')->group(function () {
        Route::get('/users', [\App\Http\Controllers\ForumController::class, 'getMentionableUsers']);
    });

    // User search route
    Route::get('/user/search', [\App\Http\Controllers\ForumController::class, 'searchUserByUsername']);

    // Follow routes
    // IMPORTANT: Specific routes (like /me/following) must come BEFORE parameterized routes (/{userId}/following)
    Route::prefix('profile')->group(function () {
        Route::get('/me/following', [\App\Http\Controllers\ProfileController::class, 'getMyFollowing']); // Get current user's following - must be first!
        Route::post('/{userId}/follow', [\App\Http\Controllers\ProfileController::class, 'follow']);
        Route::post('/{userId}/unfollow', [\App\Http\Controllers\ProfileController::class, 'unfollow']);
        Route::get('/{userId}/following', [\App\Http\Controllers\ProfileController::class, 'getFollowing']);
        Route::get('/{userId}/followers', [\App\Http\Controllers\ProfileController::class, 'getFollowers']);
    });

    // Classroom routes
    Route::get('/classrooms', [\App\Http\Controllers\ClassroomController::class, 'index']);
    Route::post('/classroom/{classroom}/create-forum', [\App\Http\Controllers\ClassroomController::class, 'createForum']);






    // AI Content routes (New Document Analyzer)
    Route::prefix('ai-content')->group(function () {
        Route::post('/analyze', [\App\Http\Controllers\AiContentController::class, 'analyze']);
        Route::get('/', [\App\Http\Controllers\AiContentController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\AiContentController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\AiContentController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\AiContentController::class, 'destroy']);
    });

    // Lesson block editor routes
    Route::prefix('lessons')->group(function () {
        Route::post('/upload-image', [\App\Http\Controllers\LessonController::class, 'uploadImage']);
    });
});

// Chatbot routes
Route::prefix('chatbot')->group(function () {
    Route::post('/chat', [ChatbotController::class, 'chat']);
});


