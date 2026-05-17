<?php

namespace App\Listeners;

use App\Events\BatDongSanBiTuChoi;
use App\Notifications\PostRejectedNotification;
use Illuminate\Support\Facades\Log;

class GuiThongBaoTuChoi
{
    public function handle(BatDongSanBiTuChoi $event): void
    {
        try {
            $bds     = $event->batDongSan->load('moiGioi');
            $moiGioi = $bds->moiGioi;
            if ($moiGioi) {
                \App\Models\ThongBao::create([
                    'moi_gioi_id'     => $moiGioi->id,
                    'khach_hang_id'   => null,
                    'bat_dong_san_id' => $bds->id,
                    'tieu_de'         => 'Bài đăng bị từ chối ❌',
                    'noi_dung'        => "Bài đăng \"{$bds->tieu_de}\" đã bị admin từ chối. Lý do: {$event->lyDo}",
                    'trang_thai'      => 0,
                ]);

                $moiGioi->notify(new PostRejectedNotification($bds, $event->lyDo));
            }
        } catch (\Throwable $e) {
            Log::error('GuiThongBaoTuChoi error', ['error' => $e->getMessage()]);
        }
    }
}
