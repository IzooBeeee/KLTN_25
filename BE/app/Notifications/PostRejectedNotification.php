<?php

namespace App\Notifications;

use App\Models\BatDongSan;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * Thông báo cho MÔI GIỚI khi bài đăng bị admin từ chối.
 *
 * KHÔNG định nghĩa broadcastAs().
 * → Frontend dùng Echo.private('user.{id}').notification(cb) để nhận
 */
class PostRejectedNotification extends Notification implements ShouldBroadcastNow
{
    public function __construct(
        public BatDongSan $batDongSan,
        public ?string $lyDo = null
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
        $noiDung = "Bài đăng \"{$this->batDongSan->tieu_de}\" đã bị từ chối.";
        if ($this->lyDo) $noiDung .= " Lý do: {$this->lyDo}";

        return [
            'type'     => 'post_rejected',
            'loai'     => 'rejected',
            'tieu_de'  => 'Bài đăng bị từ chối ❌',
            'noi_dung' => $noiDung,
            'bds_id'   => $this->batDongSan->id,
            'link'     => "/moi-gioi/quan-ly-tin",
        ];
    }
}
