<?php

namespace App\Notifications;

use App\Models\GiaoDich;
use App\Models\GoiTin;
use App\Models\MoiGioi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * Thông báo cho ADMIN khi môi giới thanh toán thành công.
 * broadcastOn routing qua Admin::routeNotificationForBroadcast → private-admin.{adminId}
 */
class PaymentSuccessAdminNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public MoiGioi $moiGioi,
        public GoiTin $goiTin,
        public GiaoDich $giaoDich
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

    /**
     * ✅ Không dùng hardcode — routing qua routeNotificationForBroadcast trong Admin model
     */
    public function broadcastOn(): array
    {
        return [];
    }

    public function broadcastAs(): string
    {
        return 'notification.new';
    }

    private function payload(): array
    {
        return [
            'type'        => 'payment_success',
            'loai'        => 'payment',
            'tieu_de'     => 'Môi giới vừa thanh toán gói 💰',
            'noi_dung'    => "Môi giới \"{$this->moiGioi->ten}\" đã thanh toán gói \"{$this->goiTin->ten_goi}\" - " . number_format($this->giaoDich->so_tien, 0, ',', '.') . 'đ',
            'moi_gioi_id' => $this->moiGioi->id,
            'goi_tin_id'  => $this->goiTin->id,
            'so_tien'     => $this->giaoDich->so_tien,
            'link'        => "/admin/giao-dich",
        ];
    }
}
