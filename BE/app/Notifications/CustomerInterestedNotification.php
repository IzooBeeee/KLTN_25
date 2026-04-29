<?php

namespace App\Notifications;

use App\Models\BatDongSan;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * Thông báo cho MÔI GIỚI khi khách hàng quan tâm/yêu thích bài đăng.
 *
 * KHÔNG định nghĩa broadcastAs().
 * → Frontend dùng Echo.private('user.{id}').notification(cb) để nhận
 */
class CustomerInterestedNotification extends Notification implements ShouldBroadcastNow
{
    public function __construct(
        public BatDongSan $batDongSan,
        public string $tenKhach,
        public string $loai = 'yeu_thich'
    ) {}

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
        $action = $this->loai === 'lien_he' ? 'liên hệ' : 'quan tâm';
        return [
            'type'     => 'customer_interested',
            'loai'     => 'khach_moi',
            'tieu_de'  => "Khách hàng {$action} bài đăng 👤",
            'noi_dung' => "Khách \"{$this->tenKhach}\" đã {$action} bài: {$this->batDongSan->tieu_de}",
            'bds_id'   => $this->batDongSan->id,
            'link'     => "/moi-gioi/khach-hang",
        ];
    }
}
