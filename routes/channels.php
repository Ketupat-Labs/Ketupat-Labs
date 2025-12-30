<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;
<<<<<<< HEAD

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Conversation channel - only participants can access
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    
    if (!$conversation) {
        return false;
    }
    
    // Check if user is a participant in the conversation
    return $conversation->participants()->where('user_id', $user->id)->exists();
});

// User notification channel - users can only listen to their own notifications
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
=======
>>>>>>> 6d44d3eac56b827c0904d252e11f4532a06f0633
