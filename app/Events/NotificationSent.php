<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $unreadCount;
    public $userId;
    public $notification;

    public function __construct($userId, $unreadCount, $notification = null)
    {
        $this->userId = $userId;
        $this->unreadCount = $unreadCount;
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        // Private channel per user
        return new Channel('user.' . $this->userId);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'notification.new';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'unread_count' => $this->unreadCount,
            'notification' => $this->notification
        ];
    }
}