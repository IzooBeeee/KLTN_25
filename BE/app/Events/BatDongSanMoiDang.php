<?php

namespace App\Events;

use App\Models\BatDongSan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event nội bộ: Môi giới vừa đăng/publish bài mới.
 * Listener sẽ gửi Notification broadcast cho Admin.
 * Event này KHÔNG tự broadcast (tránh duplicate).
 */
class BatDongSanMoiDang
{
    use Dispatchable, SerializesModels;

    public BatDongSan $batDongSan;

    public function __construct(BatDongSan $batDongSan)
    {
        $this->batDongSan = $batDongSan;
    }
}