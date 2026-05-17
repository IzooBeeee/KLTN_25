<?php

namespace App\Http\Controllers;

use App\Models\LichHenXemNha;
use App\Models\BatDongSan;
use App\Models\ThongBao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LichHenXemNhaController extends Controller
{
    /**
     * 📅 Khách hàng đặt lịch hẹn xem nhà
     */
    public function datLich(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bat_dong_san_id' => 'required|exists:bat_dong_sans,id',
            'ngay_hen' => 'required|date|after_or_equal:today',
            'gio_hen' => 'required|date_format:H:i',
            'ghi_chu' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::guard('sanctum')->user();
        if (!$user || !method_exists($user, 'wasRecentlyCreated') && get_class($user) !== 'App\Models\KhachHang') {
            return response()->json([
                'status' => false,
                'message' => 'Bạn cần đăng nhập để đặt lịch'
            ], 401);
        }

        // Get property and broker
        $bds = BatDongSan::find($request->bat_dong_san_id);
        if (!$bds || !$bds->moi_gioi_id) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy thông tin môi giới'
            ], 404);
        }

        // Check if already has pending appointment for this property
        $existing = LichHenXemNha::where('bat_dong_san_id', $request->bat_dong_san_id)
            ->where('khach_hang_id', $user->id)
            ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
            ->first();

        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn đã có lịch hẹn đang chờ xác nhận cho bất động sản này'
            ], 400);
        }

        $lichHen = LichHenXemNha::create([
            'bat_dong_san_id' => $request->bat_dong_san_id,
            'khach_hang_id' => $user->id,
            'moi_gioi_id' => $bds->moi_gioi_id,
            'ngay_hen' => $request->ngay_hen,
            'gio_hen' => $request->gio_hen,
            'ghi_chu' => $request->ghi_chu,
            'trang_thai' => LichHenXemNha::STATUS_CHO_XAC_NHAN,
        ]);

        // 🔔 Tạo thông báo cho môi giới (nếu BĐS có môi giới)
        if ($bds->moi_gioi_id) {
            try {
                ThongBao::create([
                    'moi_gioi_id' => $bds->moi_gioi_id,
                    'khach_hang_id' => $user->id,
                    'bat_dong_san_id' => $request->bat_dong_san_id,
                    'tieu_de' => '📅 Lịch hẹn mới',
                    'noi_dung' => "{$user->ten} đã đặt lịch xem nhà vào {$request->ngay_hen} lúc {$request->gio_hen}",
                    'trang_thai' => 0, // chưa đọc
                ]);
            } catch (\Exception $e) {
                \Log::error('Lỗi tạo thông báo lịch hẹn: ' . $e->getMessage());
            }
        }

        // Load relationships
        $lichHen->load(['batDongSan', 'khachHang', 'moiGioi']);

        return response()->json([
            'status' => true,
            'message' => 'Đặt lịch thành công. Môi giới sẽ xác nhận sớm!',
            'data' => $lichHen
        ]);
    }

    /**
     * 📋 Khách hàng xem danh sách lịch hẹn của mình
     */
    public function danhSachKhachHang()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $lichHens = LichHenXemNha::with(['batDongSan.loai', 'batDongSan.diaChi', 'moiGioi'])
            ->where('khach_hang_id', $user->id)
            ->orderByRaw("CASE 
                WHEN trang_thai = 'cho_xac_nhan' THEN 1 
                WHEN trang_thai = 'da_xac_nhan' THEN 2 
                ELSE 3 
            END")
            ->orderBy('ngay_hen', 'asc')
            ->orderBy('gio_hen', 'asc')
            ->get()
            ->map(function ($lich) {
                return [
                    'id' => $lich->id,
                    'bat_dong_san' => [
                        'id' => $lich->batDongSan->id,
                        'tieu_de' => $lich->batDongSan->tieu_de,
                        'anh_dai_dien_url' => $lich->batDongSan->anh_dai_dien_url,
                        'loai' => $lich->batDongSan->loai?->ten_loai,
                        'dia_chi' => $lich->batDongSan->diaChi?->dia_chi_day_du,
                    ],
                    'moi_gioi' => [
                        'id' => $lich->moiGioi->id,
                        'ten' => $lich->moiGioi->ten,
                        'so_dien_thoai' => $lich->moiGioi->so_dien_thoai,
                    ],
                    'ngay_hen' => $lich->ngay_hen->format('d/m/Y'),
                    'gio_hen' => $lich->gio_hen->format('H:i'),
                    'ghi_chu' => $lich->ghi_chu,
                    'trang_thai' => $lich->trang_thai,
                    'status_label' => $lich->status_label,
                    'status_color' => $lich->status_color,
                    'created_at' => $lich->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $lichHens
        ]);
    }

    /**
     * 📋 Môi giới xem danh sách lịch hẹn
     */
    public function danhSachMoiGioi()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $lichHens = LichHenXemNha::with(['batDongSan.loai', 'batDongSan.diaChi', 'khachHang'])
            ->where('moi_gioi_id', $user->id)
            ->orderByRaw("CASE 
                WHEN trang_thai = 'cho_xac_nhan' THEN 1 
                WHEN trang_thai = 'da_xac_nhan' THEN 2 
                ELSE 3 
            END")
            ->orderBy('ngay_hen', 'asc')
            ->orderBy('gio_hen', 'asc')
            ->get()
            ->map(function ($lich) {
                return [
                    'id' => $lich->id,
                    'bat_dong_san' => [
                        'id' => $lich->batDongSan->id,
                        'tieu_de' => $lich->batDongSan->tieu_de,
                        'anh_dai_diem_url' => $lich->batDongSan->anh_dai_dien_url,
                        'loai' => $lich->batDongSan->loai?->ten_loai,
                        'dia_chi' => $lich->batDongSan->diaChi?->dia_chi_day_du,
                    ],
                    'khach_hang' => [
                        'id' => $lich->khachHang->id,
                        'ten' => $lich->khachHang->ten,
                        'email' => $lich->khachHang->email,
                        'so_dien_thoai' => $lich->khachHang->so_dien_thoai,
                    ],
                    'ngay_hen' => $lich->ngay_hen->format('d/m/Y'),
                    'gio_hen' => $lich->gio_hen->format('H:i'),
                    'ghi_chu' => $lich->ghi_chu,
                    'trang_thai' => $lich->trang_thai,
                    'status_label' => $lich->status_label,
                    'status_color' => $lich->status_color,
                    'created_at' => $lich->created_at->format('d/m/Y H:i'),
                ];
            });

        // Count pending
        $pendingCount = LichHenXemNha::where('moi_gioi_id', $user->id)
            ->where('trang_thai', LichHenXemNha::STATUS_CHO_XAC_NHAN)
            ->count();

        // Upcoming appointments for today and tomorrow
        $upcoming = LichHenXemNha::where('moi_gioi_id', $user->id)
            ->whereIn('trang_thai', [LichHenXemNha::STATUS_DA_XAC_NHAN, LichHenXemNha::STATUS_CHO_XAC_NHAN])
            ->whereBetween('ngay_hen', [Carbon::today(), Carbon::today()->addDays(7)])
            ->orderBy('ngay_hen', 'asc')
            ->orderBy('gio_hen', 'asc')
            ->count();

        return response()->json([
            'status' => true,
            'data' => [
                'appointments' => $lichHens,
                'stats' => [
                    'pending' => $pendingCount,
                    'upcoming' => $upcoming,
                    'total' => $lichHens->count(),
                ]
            ]
        ]);
    }

    /**
     * ✅ Môi giới xác nhận lịch hẹn
     */
    public function xacNhan($id)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $lichHen = LichHenXemNha::where('id', $id)
            ->where('moi_gioi_id', $user->id)
            ->first();

        if (!$lichHen) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy lịch hẹn'], 404);
        }

        if ($lichHen->trang_thai !== LichHenXemNha::STATUS_CHO_XAC_NHAN) {
            return response()->json([
                'status' => false,
                'message' => 'Lịch hẹn này đã được xử lý'
            ], 400);
        }

        $lichHen->update([
            'trang_thai' => LichHenXemNha::STATUS_DA_XAC_NHAN,
            'xac_nhan_at' => now(),
        ]);

        // 🔔 Tạo thông báo cho khách hàng
        try {
            ThongBao::create([
                'moi_gioi_id' => null, // Thông báo cho khách hàng
                'khach_hang_id' => $lichHen->khach_hang_id,
                'bat_dong_san_id' => $lichHen->bat_dong_san_id,
                'tieu_de' => '✅ Lịch hẹn đã được xác nhận',
                'noi_dung' => "Môi giới đã xác nhận lịch hẹn xem nhà của bạn vào {$lichHen->ngay_hen} lúc {$lichHen->gio_hen}",
                'trang_thai' => 0,
            ]);
        } catch (\Exception $e) {
            \Log::error('Lỗi tạo thông báo xác nhận: ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Đã xác nhận lịch hẹn thành công!',
            'data' => $lichHen
        ]);
    }

    /**
     * ✅ Môi giới đánh dấu hoàn thành
     */
    public function hoanThanh($id)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $lichHen = LichHenXemNha::where('id', $id)
            ->where('moi_gioi_id', $user->id)
            ->first();

        if (!$lichHen) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy lịch hẹn'], 404);
        }

        if (!in_array($lichHen->trang_thai, [LichHenXemNha::STATUS_DA_XAC_NHAN, LichHenXemNha::STATUS_CHO_XAC_NHAN])) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể cập nhật trạng thái'
            ], 400);
        }

        $lichHen->update([
            'trang_thai' => LichHenXemNha::STATUS_HOAN_THANH,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Đã đánh dấu hoàn thành lịch hẹn!',
            'data' => $lichHen
        ]);
    }

    /**
     * ❌ Hủy lịch hẹn (cả khách hàng và môi giới)
     */
    public function huyLich(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'ly_do' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng nhập lý do hủy',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $query = LichHenXemNha::where('id', $id);
        
        // Check if user is customer or broker
        if (method_exists($user, 'wasRecentlyCreated') || get_class($user) === 'App\Models\KhachHang') {
            $query->where('khach_hang_id', $user->id);
        } else {
            $query->where('moi_gioi_id', $user->id);
        }

        $lichHen = $query->first();

        if (!$lichHen) {
            return response()->json(['status' => false, 'message' => 'Không tìm thấy lịch hẹn'], 404);
        }

        if ($lichHen->trang_thai === LichHenXemNha::STATUS_HUY) {
            return response()->json([
                'status' => false,
                'message' => 'Lịch hẹn này đã bị hủy'
            ], 400);
        }

        $lichHen->update([
            'trang_thai' => LichHenXemNha::STATUS_HUY,
            'ly_do_huy' => $request->ly_do,
        ]);

        // 🔔 Tạo thông báo cho khách hàng nếu là môi giới hủy
        if ($lichHen->moi_gioi_id == $user->id) {
            try {
                ThongBao::create([
                    'moi_gioi_id' => null,
                    'khach_hang_id' => $lichHen->khach_hang_id,
                    'bat_dong_san_id' => $lichHen->bat_dong_san_id,
                    'tieu_de' => '❌ Lịch hẹn bị từ chối',
                    'noi_dung' => "Môi giới đã từ chối lịch hẹn vào {$lichHen->ngay_hen} lúc {$lichHen->gio_hen}. Lý do: {$request->ly_do}",
                    'trang_thai' => 0,
                ]);
            } catch (\Exception $e) {
                \Log::error('Lỗi tạo thông báo từ chối: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Đã hủy lịch hẹn thành công',
            'data' => $lichHen
        ]);
    }

    /**
     * 📊 Thống kê cho dashboard môi giới
     */
    public function thongKe()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $today = Carbon::today();
        
        $stats = [
            'today' => LichHenXemNha::where('moi_gioi_id', $user->id)
                ->whereDate('ngay_hen', $today)
                ->whereIn('trang_thai', [LichHenXemNha::STATUS_DA_XAC_NHAN, LichHenXemNha::STATUS_CHO_XAC_NHAN])
                ->count(),
            'pending' => LichHenXemNha::where('moi_gioi_id', $user->id)
                ->where('trang_thai', LichHenXemNha::STATUS_CHO_XAC_NHAN)
                ->count(),
            'this_week' => LichHenXemNha::where('moi_gioi_id', $user->id)
                ->whereBetween('ngay_hen', [$today, $today->copy()->addDays(7)])
                ->whereIn('trang_thai', [LichHenXemNha::STATUS_DA_XAC_NHAN, LichHenXemNha::STATUS_CHO_XAC_NHAN])
                ->count(),
            'completed_this_month' => LichHenXemNha::where('moi_gioi_id', $user->id)
                ->where('trang_thai', LichHenXemNha::STATUS_HOAN_THANH)
                ->whereMonth('ngay_hen', $today->month)
                ->whereYear('ngay_hen', $today->year)
                ->count(),
        ];

        return response()->json([
            'status' => true,
            'data' => $stats
        ]);
    }
}
