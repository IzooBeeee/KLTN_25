<?php

namespace App\Listeners;

use App\Events\BatDongSanDuocYeuThich;
use App\Models\ThongBao;
use App\Notifications\CustomerInterestedNotification;
use Illuminate\Support\Facades\Log;

/**
 * Khi khách yêu thích BĐS:
 * 1. ThongBao DB cũ (giữ nguyên hành vi cũ)
 * 2. Notification (database + broadcast) cho MÔI GIỚI
 */
class TaoThongBaoKhiYeuThich
{
    public function handle(BatDongSanDuocYeuThich $event): void
    {
        $khachHang  = $event->khachHang;
        $batDongSan = $event->batDongSan;

        // ── 1. ThongBao cũ (giữ nguyên) ──────────────────────────────────────
        ThongBao::updateOrCreate(
            [
                'moi_gioi_id'     => $batDongSan->moi_gioi_id,
                'khach_hang_id'   => $khachHang->id,
                'bat_dong_san_id' => $batDongSan->id,
            ],
            [
                'tieu_de'    => 'Khách hàng vừa tương tác bất động sản',
                'noi_dung'   => "Khách hàng {$khachHang->ten} đã thả tim BĐS {$batDongSan->tieu_de}",
                'trang_thai' => 0,
            ]
        );

        // ── 2. Notification mới: gửi cho MÔI GIỚI sở hữu BĐS ────────────────
        try {
            $moiGioi = $batDongSan->moiGioi;
            if ($moiGioi) {
                $moiGioi->notify(new CustomerInterestedNotification(
                    $batDongSan,
                    $khachHang->ten,
                    'yeu_thich'
                ));
            }
        } catch (\Throwable $e) {
            Log::error('TaoThongBaoKhiYeuThich: Lỗi gửi notification', ['error' => $e->getMessage()]);
        }
    }
}
