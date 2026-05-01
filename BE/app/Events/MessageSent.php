<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        $conversation = \App\Models\Conversation::find($this->message->conversation_id);
        
        $channels = [
            new Channel('chat.' . $this->message->conversation_id),
        ];

        // Gửi thông báo đến private channel của môi giới và khách hàng
        if ($conversation) {
            $channels[] = new \Illuminate\Broadcasting\PrivateChannel('user.' . $conversation->moi_gioi_id);
            // Nếu khách hàng sau này dùng echo thì cũng sẽ nhận được
            $channels[] = new \Illuminate\Broadcasting\PrivateChannel('khach-hang.' . $conversation->khach_hang_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        $sender = $this->message->sender;
        $senderName = $sender ? ($sender->ten ?? $sender->name ?? 'Người dùng') : 'Người dùng';

        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'sender_type' => $this->message->sender_type,
            'sender_name' => $senderName,
            'content' => $this->message->content,
            'type' => $this->message->type,
            'is_read' => $this->message->is_read,
            'created_at' => optional($this->message->created_at)->toDateTimeString(),
        ];
    }
}