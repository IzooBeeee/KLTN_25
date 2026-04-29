<?php

namespace App\Notifications;

use App\Models\BatDongSan;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * Thông báo cho MÔI GIỚI khi bài đăng được admin duyệt.
 * 
 * KHÔNG định nghĩa broadcastAs().
 * → Frontend dùng Echo.private('user.{id}').notification(cb) để nhận
 */
class PostApprovedNotification extends Notification implements ShouldBroadcastNow
{
    public function __construct(public BatDongSan $batDongSan) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    private function payload(): array
    {
        return [
            'type'     => 'post_approved',
            'loai'     => 'approved',
            'tieu_de'  => 'Bài đăng đã được duyệt ✅',
            'noi_dung' => "Bài đăng \"{$this->batDongSan->tieu_de}\" đã được admin duyệt.",
            'bds_id'   => $this->batDongSan->id,
            'link'     => "/moi-gioi/quan-ly-tin",
        ];
    }
}
