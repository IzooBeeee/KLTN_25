<?php

namespace App\Listeners;

use App\Events\ThanhToanThanhCong;
use App\Mail\PaymentSuccessMail;
use App\Models\GiaoDich;
use App\Models\GoiTin;
use App\Models\MoiGioi;
use App\Models\Admin;
use App\Notifications\PaymentSuccessAdminNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class XuLyThanhToanThanhCong
{
    public function handle(ThanhToanThanhCong $event): void
    {
        try {
            $moiGioi = MoiGioi::find($event->userId);
            if (!$moiGioi) return;

            // Lấy giao dịch thành công mới nhất của môi giới
            $giaoDich = GiaoDich::where('moi_gioi_id', $moiGioi->id)
                ->where('trang_thai', 'success')
                ->latest('paid_at')
                ->first();

            if (!$giaoDich) return;

            $goiTin = GoiTin::find($giaoDich->goi_tin_id);
            if (!$goiTin) return;

            // ── 1. Gửi Mail cho MÔI GIỚI ──────────────────────────────────────
            if ($moiGioi->email) {
                Mail::to($moiGioi->email)->send(new PaymentSuccessMail($moiGioi, $goiTin, $giaoDich));
            }

            // ── 2. Notification cho ADMIN ──────────────────────────────────────
            $notification = new PaymentSuccessAdminNotification($moiGioi, $goiTin, $giaoDich);
            $admins = Admin::all();
            foreach ($admins as $admin) {
                $admin->notify($notification);
            }

            Log::info("XuLyThanhToanThanhCong: Sent mail + admin notification for user {$moiGioi->id}");
        } catch (\Throwable $e) {
            Log::error('XuLyThanhToanThanhCong error', ['error' => $e->getMessage()]);
        }
    }
}
