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
                $moiGioi->notify(new PostApprovedNotification($bds));
            }
        } catch (\Throwable $e) {
            Log::error('GuiThongBaoDuyet error', ['error' => $e->getMessage()]);
        }
    }
}
