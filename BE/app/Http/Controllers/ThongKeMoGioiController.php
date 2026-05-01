<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ThongBao;
use App\Models\GiaoDich;
use App\Models\YeuThich;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ThongKeMoGioiController extends Controller
{
    public function tongTinDaDang()
    {
        try {
            $user = auth('sanctum')->user();

            // ✅ Đếm BĐS đã được duyệt VÀ chưa hết hạn
            $count = $user->batDongSans()
                ->where('is_duyet', true)           // Đã duyệt
                ->where(function ($q) {               // VÀ (chưa có hạn HOẶC còn hạn)
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->count();

            return response()->json([
                'status' => true,
                'message' => 'Thành công',
                'data' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function tinConLai()
    {
        try {
            $user = auth('sanctum')->user();

            return response()->json([
                'status' => true,
                'message' => 'Thành công',
                'data' => $user->so_tin_con_lai ?? 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function tongYeuThich()
    {
        try {
            $user = auth('sanctum')->user();

            // ✅ Sửa logic: đếm khách hàng yêu thích BĐS của môi giới
            $count = YeuThich::whereHas('batDongSan', function ($q) use ($user) {
                $q->where('moi_gioi_id', $user->id);
            })
                ->distinct('khach_hang_id')
                ->count('khach_hang_id');

            return response()->json([
                'status' => true,
                'message' => 'Thành công',
                'data' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function tongTien()
    {
        try {
            $user = auth('sanctum')->user();

            $total = GiaoDich::where('moi_gioi_id', $user->id)
                ->where('trang_thai', GiaoDich::STATUS_SUCCESS)
                ->sum('so_tien');

            return response()->json([
                'status' => true,
                'message' => 'Thành công',
                'data' => $total
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function bieuDoBaiDang(Request $request)
    {
        try {
            $user = auth('sanctum')->user();
            $range = $request->input('range', 7);
            $startDate = Carbon::now()->subDays($range);

            $rawData = $user->batDongSans()
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('total', 'date');

            $labels = [];
            $values = [];
            $current = clone $startDate;

            while ($current <= Carbon::now()) {
                $dateStr = $current->format('Y-m-d');
                $labels[] = $dateStr;
                $values[] = $rawData->get($dateStr, 0);
                $current->addDay();
            }

            return response()->json([
                'status' => true,
                'message' => 'Thành công',
                'data' => [
                    'labels' => $labels,
                    'values' => $values,
                    'range' => $range
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function khachHangLienHe()
    {
        try {
            $user = auth('sanctum')->user();

            $data = ThongBao::select(
                'khach_hang_id',
                DB::raw('MAX(created_at) as last_contact'),
                DB::raw('COUNT(*) as contact_count')
            )
                ->with(['khachHang', 'batDongSan'])
                ->where('moi_gioi_id', $user->id)
                ->whereNotNull('khach_hang_id')
                ->groupBy('khach_hang_id')
                ->orderByDesc('last_contact')
                ->take(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'khach_hang' => $item->khachHang,
                        'bat_dong_san' => $item->batDongSan,
                        'last_contact' => $item->last_contact,
                        'contact_count' => $item->contact_count
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Thành công',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function tongKhachHang()
    {
        try {
            $user = auth('sanctum')->user();

            $count = ThongBao::where('moi_gioi_id', $user->id)
                ->whereNotNull('khach_hang_id')
                ->distinct('khach_hang_id')
                ->count('khach_hang_id');

            return response()->json([
                'status' => true,
                'data'   => $count
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 📊 Dashboard tổng hợp cho môi giới
     */
    public function getDashboard()
    {
        try {
            $user = auth('sanctum')->user();

            // Số BĐS đang hoạt động
            $activeBds = $user->batDongSans()
                ->where('is_duyet', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->count();

            // Số BĐS chờ duyệt
            $pendingBds = $user->batDongSans()
                ->where('is_duyet', false)
                ->count();

            // Số BĐS đã hết hạn
            $expiredBds = $user->batDongSans()
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->count();

            // Tổng BĐS đã đăng
            $totalBds = $user->batDongSans()->count();

            // Tín chỉ còn lại
            $credits = $user->so_tin_con_lai ?? 0;

            // Gói hiện tại
            $currentPlan = null;
            if ($user->goiTin) {
                $currentPlan = [
                    'ten_goi' => $user->goiTin->ten_goi,
                    'gia' => $user->goiTin->gia,
                    'so_luong_tin' => $user->goiTin->so_luong_tin,
                    'so_ngay' => $user->goiTin->so_ngay,
                ];
            }

            // Ngày hết hạn gói
            $planExpiry = $user->ngay_het_han_goi ? $user->ngay_het_han_goi->format('d/m/Y') : null;
            $daysUntilExpiry = $user->ngay_het_han_goi ? now()->diffInDays($user->ngay_het_han_goi, false) : null;

            // Tổng yêu thích (khách hàng đã tim BĐS của môi giới)
            $totalFavorites = YeuThich::whereHas('batDongSan', function ($q) use ($user) {
                $q->where('moi_gioi_id', $user->id);
            })->distinct('khach_hang_id')->count('khach_hang_id');

            // Tổng khách hàng đã liên hệ
            $totalContacts = ThongBao::where('moi_gioi_id', $user->id)
                ->whereNotNull('khach_hang_id')
                ->distinct('khach_hang_id')
                ->count('khach_hang_id');

            // Tổng chi tiêu
            $totalSpent = GiaoDich::where('moi_gioi_id', $user->id)
                ->where('trang_thai', GiaoDich::STATUS_SUCCESS)
                ->sum('so_tien');

            // BĐS gần đây (5 tin mới nhất)
            $recentBds = $user->batDongSans()
                ->with(['loai', 'diaChi'])
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($b) => [
                    'id' => $b->id,
                    'tieu_de' => $b->tieu_de,
                    'gia' => $b->gia,
                    'anh_dai_dien_url' => $b->anh_dai_dien_url,
                    'is_duyet' => $b->is_duyet,
                    'expires_at' => $b->expires_at?->format('d/m/Y'),
                    'loai' => $b->loai?->ten_loai,
                    'dia_chi' => $b->diaChi?->dia_chi_day_du,
                    'created_at' => $b->created_at->format('d/m/Y'),
                ]);

            // Chart: BĐS theo tháng (6 tháng gần nhất)
            $chartLabels = [];
            $chartData = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $chartLabels[] = $month->format('m/Y');
                $count = $user->batDongSans()
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
                $chartData[] = $count;
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'stats' => [
                        'active_bds' => $activeBds,
                        'pending_bds' => $pendingBds,
                        'expired_bds' => $expiredBds,
                        'total_bds' => $totalBds,
                        'credits_remaining' => $credits,
                        'total_favorites' => $totalFavorites,
                        'total_contacts' => $totalContacts,
                        'total_spent' => $totalSpent,
                    ],
                    'plan' => [
                        'current' => $currentPlan,
                        'expiry_date' => $planExpiry,
                        'days_until_expiry' => $daysUntilExpiry,
                    ],
                    'recent_bds' => $recentBds,
                    'chart' => [
                        'labels' => $chartLabels,
                        'data' => $chartData,
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
