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
 * Notification (database + broadcast) cho ADMIN chờ duyệt
 */
class TaoThongBaoBDSMoi
{
    public function handle(BatDongSanMoiDang $event): void
    {
        $batDongSan = $event->batDongSan;

        \Illuminate\Support\Facades\Log::info('LISTENER RUN: TaoThongBaoBDSMoi', ['bds_id' => $batDongSan->id]);

        // ── Notification mới: gửi cho tất cả ADMIN ─────────────────────────
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