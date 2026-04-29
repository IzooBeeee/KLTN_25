<?php

namespace App\Notifications;

use App\Models\BatDongSan;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * Thông báo cho ADMIN khi có bài đăng mới chờ duyệt.
 * ShouldBroadcastNow: broadcast ngay lập tức, không qua queue.
 *
 * KHÔNG định nghĩa broadcastOn() hay broadcastAs() ở đây.
 * → Laravel tự dùng Admin->receivesBroadcastNotificationsOn() = 'admin.{id}'
 * → Event name mặc định = Illuminate\Notifications\Events\BroadcastNotificationCreated
 * → Frontend dùng Echo.private('admin.{id}').notification(cb) để nhận
 */
class NewPostPendingApprovalNotification extends Notification implements ShouldBroadcastNow
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
            'type'        => 'new_post_pending',
            'loai'        => 'pending',
            'tieu_de'     => 'Có bài đăng mới chờ duyệt',
            'noi_dung'    => "Môi giới vừa đăng bài: {$this->batDongSan->tieu_de}",
            'bds_id'      => $this->batDongSan->id,
            'moi_gioi_id' => $this->batDongSan->moi_gioi_id,
            'link'        => "/admin/bat-dong-san/{$this->batDongSan->id}",
        ];
    }
}
