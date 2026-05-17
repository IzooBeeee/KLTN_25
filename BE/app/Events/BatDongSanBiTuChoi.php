<?php

namespace App\Events;

use App\Models\BatDongSan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BatDongSanBiTuChoi
{
    use Dispatchable, SerializesModels;

    public BatDongSan $batDongSan;
    public ?string $lyDo;

    public function __construct(BatDongSan $batDongSan, ?string $lyDo = null)
    {
        $this->batDongSan = $batDongSan;
        $this->lyDo = $lyDo;
    }
}
