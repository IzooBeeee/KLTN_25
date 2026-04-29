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
                $moiGioi->notify(new PostRejectedNotification($bds, $event->lyDo));
            }
        } catch (\Throwable $e) {
            Log::error('GuiThongBaoTuChoi error', ['error' => $e->getMessage()]);
        }
    }
}
