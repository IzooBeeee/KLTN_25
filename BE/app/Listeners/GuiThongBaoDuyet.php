<?php

namespace App\Listeners;

use App\Events\BatDongSanDuocDuyet;
use App\Notifications\PostApprovedNotification;
use Illuminate\Support\Facades\Log;

class GuiThongBaoDuyet
{
    public function handle(BatDongSanDuocDuyet $event): void
    {
        try {
            $bds     = $event->batDongSan->load('moiGioi');
            $moiGioi = $bds->moiGioi;
            if ($moiGioi) {
                \App\Models\ThongBao::create([
                    'moi_gioi_id'     => $moiGioi->id,
                    'khach_hang_id'   => null,
                    'bat_dong_san_id' => $bds->id,
                    'tieu_de'         => 'Bài đăng đã được duyệt ✅',
                    'noi_dung'        => "Bài đăng \"{$bds->tieu_de}\" đã được admin duyệt.",
                    'trang_thai'      => 0,
                ]);

                $moiGioi->notify(new PostApprovedNotification($bds));
            }
        } catch (\Throwable $e) {
            Log::error('GuiThongBaoDuyet error', ['error' => $e->getMessage()]);
        }
    }
}
