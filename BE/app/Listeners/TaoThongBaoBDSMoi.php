<?php

namespace App\Listeners;

use App\Events\BatDongSanMoiDang;
use App\Models\Admin;
use App\Models\KhachHang;
use App\Models\ThongBao;
use App\Notifications\NewPostPendingApprovalNotification;
use Illuminate\Support\Facades\Log;

/**
 * Khi môi giới publish bài:
 * 1. Thông báo DB cũ cho tất cả khách hàng (giữ nguyên hành vi cũ)
 * 2. Notification (database + broadcast) cho ADMIN chờ duyệt
 */
class TaoThongBaoBDSMoi
{
    public function handle(BatDongSanMoiDang $event): void
    {
        $batDongSan = $event->batDongSan;

        \Illuminate\Support\Facades\Log::info('LISTENER RUN: TaoThongBaoBDSMoi', ['bds_id' => $batDongSan->id]);

        // ── 1. Thông báo cũ: gửi cho khách hàng (giữ nguyên) ─────────────────
        $khachHangs = KhachHang::where('is_active', true)->get();
        foreach ($khachHangs as $khachHang) {
            ThongBao::create([
                'moi_gioi_id'     => $batDongSan->moi_gioi_id,
                'khach_hang_id'   => $khachHang->id,
                'bat_dong_san_id' => $batDongSan->id,
                'tieu_de'         => 'Có bất động sản mới',
                'noi_dung'        => "Môi giới vừa đăng BĐS {$batDongSan->tieu_de}",
                'trang_thai'      => 0,
            ]);
        }

        // ── 2. Notification mới: gửi cho tất cả ADMIN ─────────────────────────
        try {
            \Illuminate\Support\Facades\Log::info('NOTIFY ADMIN: Preparing to notify admins', ['count' => \App\Models\Admin::count()]);
            $notification = new NewPostPendingApprovalNotification($batDongSan);
            $admins = \App\Models\Admin::all();
            foreach ($admins as $admin) {
                \Illuminate\Support\Facades\Log::info('NOTIFY ADMIN: Notifying admin', ['admin_id' => $admin->id]);
                $admin->notify($notification);
            }
        } catch (\Throwable $e) {
            Log::error('TaoThongBaoBDSMoi: Lỗi gửi notification admin', ['error' => $e->getMessage()]);
        }
    }
}