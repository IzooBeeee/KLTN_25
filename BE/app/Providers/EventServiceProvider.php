<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // ── Môi giới đăng tin mới ─────────────────────────────────────────────
        \App\Events\BatDongSanMoiDang::class => [
            \App\Listeners\TaoThongBaoBDSMoi::class,   // cũ: TB khách hàng + MỚI: notify admin
        ],

        // ── Admin duyệt bài ───────────────────────────────────────────────────
        \App\Events\BatDongSanDuocDuyet::class => [
            \App\Listeners\GuiThongBaoDuyet::class,    // notify môi giới
        ],

        // ── Admin từ chối bài ─────────────────────────────────────────────────
        \App\Events\BatDongSanBiTuChoi::class => [
            \App\Listeners\GuiThongBaoTuChoi::class,   // notify môi giới
        ],

        // ── Khách yêu thích BĐS ───────────────────────────────────────────────
        \App\Events\BatDongSanDuocYeuThich::class => [
            \App\Listeners\TaoThongBaoKhiYeuThich::class, // cũ: ThongBao + MỚI: notify môi giới
        ],

        // ── Thanh toán thành công ─────────────────────────────────────────────
        \App\Events\ThanhToanThanhCong::class => [
            \App\Listeners\XuLyThanhToanThanhCong::class, // mail môi giới + notify admin
        ],
    ];
}
