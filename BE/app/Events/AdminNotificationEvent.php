<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminNotificationEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $adminId,
        public array $data
    ) {
        \Illuminate\Support\Facades\Log::info('AdminNotificationEvent triggered', [
            'adminId' => $adminId,
            'data' => $data
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\Channel('admin-public.' . $this->adminId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }
}
