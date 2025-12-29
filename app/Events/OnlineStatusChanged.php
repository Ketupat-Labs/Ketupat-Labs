<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OnlineStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $isOnline;
    public $conversationIds;

    /**
     * Create a new event instance.
     */
    public function __construct(int $userId, bool $isOnline, array $conversationIds = [])
    {
        $this->userId = $userId;
        $this->isOnline = $isOnline;
        $this->conversationIds = $conversationIds;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];
        foreach ($this->conversationIds as $conversationId) {
            $channels[] = new PrivateChannel('conversation.' . $conversationId);
        }
        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'online_status_update';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'type' => 'online_status_update',
            'user_id' => $this->userId,
            'is_online' => $this->isOnline,
        ];
    }
}
