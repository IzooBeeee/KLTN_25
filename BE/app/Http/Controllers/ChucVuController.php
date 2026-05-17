<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChucVucreateRequest;
use App\Http\Requests\ChucVuDeleteRequest;
use App\Http\Requests\ChucVuUpdateRequest;
use App\Models\ChucVu;
use App\Models\PhanQuyen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ChucVuController extends Controller
{
    private function checkPermission($id_chuc_nang)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn chưa đăng nhập!'
            ], 401);
        }

        $id_chuc_vu = $user->id_chuc_vu;

        $check_quyen = PhanQuyen::where('id_chuc_vu', $id_chuc_vu)
            ->where('id_chuc_nang', $id_chuc_nang)
            ->first();

        if (!$user->is_super && !$check_quyen) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền thực hiện chức năng này!'
            ], 403);
        }

        return null;
    }

    public function getData()
    {
        $permission = $this->checkPermission(52);
        if ($permission) return $permission;

        $data = ChucVu::orderBy('id', 'desc')->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function store(ChucVucreateRequest $request)
    {
        $permission = $this->checkPermission(51);
        if ($permission) return $permission;

        DB::beginTransaction();

        try {
            $chucVu = ChucVu::create([
                'slug_chuc_vu' => Str::slug($request->ten_chuc_vu),
                'ten_chuc_vu' => $request->ten_chuc_vu,
                'mo_ta'       => $request->mo_ta,
                'tinh_trang'  => $request->tinh_trang ?? 1,
            ]);

            Admin::create([
                'ten'         => $request->ten,
                'email'       => $request->email,
                'password'    => Hash::make($request->password),
                'id_chuc_vu'  => $chucVu->id,
                'is_super'    => 0,
                'is_active'   => $request->tinh_trang ?? 1,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Tạo chức vụ và tài khoản quản lý thành công',
                'data' => $chucVu,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra khi tạo chức vụ',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(ChucVuUpdateRequest $request)
    {
        $permission = $this->checkPermission(53);
        if ($permission) return $permission;

        $chucVu = ChucVu::find($request->id);

        if (!$chucVu) {
            return response()->json([
                'status' => false,
                'message' => 'Chức vụ không tồn tại!'
            ], 404);
        }

        $chucVu->update([
            'slug_chuc_vu' => Str::slug($request->ten_chuc_vu),
            'ten_chuc_vu' => $request->ten_chuc_vu,
            'mo_ta'       => $request->mo_ta,
            'tinh_trang'  => $request->tinh_trang,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật chức vụ ' . $chucVu->ten_chuc_vu . ' thành công',
            'data' => $chucVu
        ]);
    }

    public function destroy(ChucVuDeleteRequest $request)
    {
        $permission = $this->checkPermission(54);
        if ($permission) return $permission;

        $chucVu = ChucVu::find($request->id);

        if (!$chucVu) {
            return response()->json([
                'status' => false,
                'message' => 'Chức vụ không tồn tại!'
            ], 404);
        }

        $tenChucVu = $chucVu->ten_chuc_vu;
        $chucVu->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa chức vụ ' . $tenChucVu . ' thành công',
        ]);
    }
}
