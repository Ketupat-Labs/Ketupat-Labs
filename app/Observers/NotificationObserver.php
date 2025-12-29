<?php

namespace App\Observers;

use App\Events\NotificationCreated;
use App\Models\Notification;

class NotificationObserver
{
    /**
     * Handle the Notification "created" event.
     */
    public function created(Notification $notification): void
    {
        // Broadcast the notification to the user via WebSocket
        event(new NotificationCreated($notification));
    }
}

