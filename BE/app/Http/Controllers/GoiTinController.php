<?php

namespace App\Http\Controllers;

use App\Models\GoiTin;
use App\Models\GiaoDich;
use App\Models\LichSuGoiTin;
use App\Models\MoiGioi;
use App\Models\KhachHang;
use App\Http\Requests\CreateGoiTinRequest;
use App\Http\Requests\UpdateGoiTinRequest;
use App\Http\Requests\MuaGoiTinRequest;
use App\Http\Requests\DestroyRequest;
use App\Http\Requests\UpdateTrangThaiGoiTinRequest;
use App\Models\PhanQuyen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GoiTinController extends Controller
{
    // Admin xem danh sách gói tin
    public function getData()
    {
        $id_chuc_nang = 19; // ID chức năng xem gói tin
        $user = Auth::guard('sanctum')->user();
        $check = PhanQuyen::where('id_chuc_vu', $user->id_chuc_vu)
            ->where('id_chuc_nang', $id_chuc_nang)
            ->first();
        if (!$user->is_super &&  !$check) {
            return response()->json([
                'status' => false,
                'message' => "Bạn không có quyền thực hiện chức năng này"
            ], 403);
        }
        return response()->json([
            'status' => true,
            'data' => GoiTin::all()
        ]);
    }

    // Admin tạo gói tin
    public function store(CreateGoiTinRequest $request)
    {
        $id_chuc_nang = 20; // ID chức năng tạo gói tin
        $user = Auth::guard('sanctum')->user();
        $check = PhanQuyen::where('id_chuc_vu', $user->id_chuc_vu)
            ->where('id_chuc_nang', $id_chuc_nang)
            ->first();
        if (!$user->is_super &&  !$check) {
            return response()->json([
                'status' => false,
                'message' => "Bạn không có quyền thực hiện chức năng này"
            ], 403);
        }
        $data = GoiTin::create([
            'ten_goi'      => $request->ten_goi,
            'gia'          => $request->gia,
            'so_ngay'      => $request->so_ngay,
            'so_luong_tin' => $request->so_luong_tin,
        ]);
        return response()->json([
            'status'  => true,
            'message' => 'Tạo thành công',
            'data'    => $data
        ]);
    }

    // Admin cập nhật gói tin
    public function update(UpdateGoiTinRequest $request)
    {
        $id_chuc_nang = 21; // ID chức năng cập nhật gói tin
        $user = Auth::guard('sanctum')->user();
        $check = PhanQuyen::where('id_chuc_vu', $user->id_chuc_vu)
            ->where('id_chuc_nang', $id_chuc_nang)
            ->first();
        if (!$user->is_super &&  !$check) {
            return response()->json([
                'status' => false,
                'message' => "Bạn không có quyền thực hiện chức năng này"
            ], 403);
        }
        GoiTin::where('id', $request->id)
            ->update([
                'ten_goi'      => $request->ten_goi,
                'gia'          => $request->gia,
                'so_ngay'      => $request->so_ngay,
                'so_luong_tin' => $request->so_luong_tin,
            ]);
        return response()->json([
            'status'  => true,
            'message' => 'Cập nhật thành công',
        ]);
    }

    // Admin xóa gói tin
    public function destroy(Request $request)
    {
        $id_chuc_nang = 22; // ID chức năng xóa gói tin
        $user = Auth::guard('sanctum')->user();
        $check = PhanQuyen::where('id_chuc_vu', $user->id_chuc_vu)
            ->where('id_chuc_nang', $id_chuc_nang)
            ->first();
        if (!$user->is_super &&  !$check) {
            return response()->json([
                'status' => false,
                'message' => "Bạn không có quyền thực hiện chức năng này"
            ], 403);
        }
        GoiTin::where('id', $request->id)->delete();
        return response()->json([
            'status' => true,
            'message' => 'Xóa thành công'
        ], 200);
    }

    public function changeStatus(UpdateTrangThaiGoiTinRequest $request)
    {
        $id_chuc_nang = 23; // ID chức năng thay đổi trạng thái gói tin
        $user = Auth::guard('sanctum')->user();
        $check = PhanQuyen::where('id_chuc_vu', $user->id_chuc_vu)
            ->where('id_chuc_nang', $id_chuc_nang)
            ->first();
        if (!$user->is_super &&  !$check) {
            return response()->json([
                'status' => false,
                'message' => "Bạn không có quyền thực hiện chức năng này"
            ], 403);
        }

        try {
            $goiTin = GoiTin::findOrFail($request->id);
            $goiTin->update(['trang_thai' => $request->trang_thai]);

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật trạng thái thành công',
                'data' => $goiTin
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi cập nhật: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPublicPackages()
    {
        $plans = GoiTin::where('trang_thai', 'active')
            ->orderBy('gia', 'asc')
            ->get(['id', 'ten_goi', 'gia', 'so_ngay', 'so_luong_tin', 'mo_ta', 'uu_tien_hien_thi']);

        return response()->json([
            'status' => true,
            'data'   => $plans,
        ]);
    }

    public function getAll()
    {
        // ✅ LẤY USER HIỆN TẠI
        $user = auth()->guard('sanctum')->user();

        $plans = GoiTin::where('trang_thai', 'active')
            ->orderBy('gia', 'asc')
            ->get();

        $formattedPlans = $plans->map(function ($plan) {
            return [
                'id'            => $plan->id,
                'name'          => $plan->ten_goi,
                'label'         => $plan->gan_nhan_vip ? 'VIP' : null,
                'description'   => $plan->mo_ta,
                'monthlyPrice'  => (int) $plan->gia,
                'yearlyPrice'   => (int) ($plan->gia * 12 * 0.8),
                'durationDays'  => (int) $plan->so_ngay,
                'postLimit'     => (int) $plan->so_luong_tin,
                'isPopular'     => $plan->uu_tien_hien_thi == 1,

                'features' => [
                    "Đăng tối đa <strong>{$plan->so_luong_tin}</strong> tin",
                    "Hiệu lực <strong>{$plan->so_ngay}</strong> ngày",
                    $plan->gan_nhan_vip
                        ? "🔥 Gắn nhãn VIP & ưu tiên hiển thị"
                        : "Hiển thị tin đăng tiêu chuẩn",
                    "Hỗ trợ kỹ thuật qua Email/Zalo"
                ],

                'btnText'  => $plan->gan_nhan_vip ? 'Mua VIP Ngay' : 'Chọn Gói',
                'btnClass' => $plan->gan_nhan_vip ? 'btn-warning' : 'btn-outline-primary',
                'cardClass' => $plan->uu_tien_hien_thi == 1 ? 'popular' : ''
            ];
        });

        // Trả về thông tin gói hiện tại + số tin còn lại + hạn dùng
        $currentPlan = null;
        if ($user) {
            $isExpired = $user->ngay_het_han_goi
                ? $user->ngay_het_han_goi->isPast()
                : true;

            $currentPlanData = $user->goi_tin_id
                ? GoiTin::find($user->goi_tin_id)
                : null;

            $currentPlan = [
                'id'             => $user->goi_tin_id,
                'so_tin_con_lai' => $user->so_tin_con_lai ?? 0,
                'ngay_het_han'   => $user->ngay_het_han_goi
                    ? $user->ngay_het_han_goi->format('d/m/Y')
                    : null,
                'het_han'        => $isExpired,
                'postLimit'      => $currentPlanData ? $currentPlanData->so_luong_tin : 0,
                'gia_hien_tai'   => $currentPlanData ? (int) $currentPlanData->gia : 0,
            ];
        }

        return response()->json([
            'status'       => true,
            'data'         => $formattedPlans,
            'current_plan' => $currentPlan,
        ]);
    }

    public function muaGoi(MuaGoiTinRequest $request)
    {
        $user = Auth::guard('sanctum')->user();

        $goi = GoiTin::find($request->goi_tin_id);

        if (!$goi) {
            return response()->json([
                'status' => false,
                'message' => 'Gói không tồn tại'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // ✅ cập nhật môi giới
            $user->update([
                'goi_tin_id' => $goi->id,
                'so_tin_con_lai' => $goi->so_luong_tin,
                'ngay_het_han_goi' => now()->addDays($goi->so_ngay),
            ]);

            // (tuỳ bạn) lưu lịch sử
            LichSuGoiTin::create([
                'moi_gioi_id' => $user->id,
                'goi_tin_id' => $goi->id,
                'so_tien' => $goi->gia,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Mua gói thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }

    public function muaCredit(Request $request)
    {
        $request->validate([
            'count' => 'required|integer|min:1|max:100',
            'price' => 'required|numeric|min:0',
        ]);

        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $user->increment('so_tin_con_lai', (int) $request->count);

        return response()->json([
            'status'  => true,
            'message' => 'Mua thêm ' . $request->count . ' tin đăng thành công',
            'so_tin_con_lai' => $user->fresh()->so_tin_con_lai,
        ]);
    }
}
