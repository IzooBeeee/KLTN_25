<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminDoiMatKhauRequest;
use App\Models\Admin;
use App\Models\MoiGioi;
use App\Models\KhachHang;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminUpdateProfileRequest;
use App\Http\Requests\DatLaiMatKhauRequest;
use App\Http\Requests\GuiMaQuenMatKhauRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Requests\XacThucMaQuenMatKhauRequest;
use App\Mail\ResetPasswordCodeMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function login(AdminLoginRequest $request)
    {
        $user = Admin::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 0,  // ✅ Integer 0
                'message' => 'Email hoặc mật khẩu không đúng'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 1,  // ✅ Integer 1
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'token_type' => 'Bearer',
            'user_type' => 'admin',
            'data' => $user
        ], 200);
    }

    public function checkToken()
    {
        $user = Auth::guard('sanctum')->user();
        if ($user && $user instanceof Admin) {
            return response()->json([
                'status' => 'success',
                'data' => $user,
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Token không hợp lệ'
            ], 401);
        }
    }

    public function profile(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $user->id,
                'ten' => $user->ten,
                'email' => $user->email,
                'so_dien_thoai' => $user->so_dien_thoai,
                'mo_ta' => $user->mo_ta,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,

            ]
        ]);
    }

    public function updateProfile(AdminUpdateProfileRequest $request)
    {
        $user = Auth::guard('sanctum')->user();

        // Cập nhật thông tin
        $user->update([
            'ten' => $request->ten,
            'email' => $request->email,
            'so_dien_thoai' => $request->so_dien_thoai,
            'mo_ta' => $request->mo_ta,
            'create_at' => now(),
            'update_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật profile thành công!',
            'data' => $user
        ]);
    }

    public function doiMatKhau(AdminDoiMatKhauRequest $request)
    {
        // ✅ 1. Lấy user đang đăng nhập (qua Sanctum)
        $user = Auth::guard('sanctum')->user();

        // ✅ 2. Kiểm tra mật khẩu cũ
        if (!Hash::check($request->mat_khau_cu, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Mật khẩu cũ không đúng!',
            ], 400);
        }
        $currentTokenId = $user->currentAccessToken()->id;

        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        $user->password = bcrypt($request->mat_khau_moi);
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Đổi mật khẩu thành công! Các thiết bị khác đã được đăng xuất.',
        ]);
    }

    public function logout()
    {
        /** @var Admin|null $user */
        $user = Auth::guard('sanctum')->user();
        if ($user) {
            $user->currentAccessToken()->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Đăng xuất thành công'
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người dùng hoặc token không hợp lệ'
            ], 401);
        }
    }

    public function logoutAll()
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy người dùng'
                ], 401);
            }

            // ✅ XÓA TẤT CẢ TOKEN MỘT LƯỢC (bao gồm current token)
            $user->tokens()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Đã đăng xuất tất cả thiết bị'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    // 1. Gửi mã xác nhận quên mật khẩu
    public function guiMaQuenMatKhau(GuiMaQuenMatKhauRequest $request)
    {
        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return response()->json([
                'status'  => 0,
                'message' => 'Email không tồn tại trong hệ thống!',
            ], 404);
        }

        $code = rand(100000, 999999);

        $admin->update([
            'hash_reset' => bcrypt($code),
            'hash_reset_expires_at' => now()->addMinutes(5),
        ]);

        // Gửi mail
        Mail::to($admin->email)->send(new ResetPasswordCodeMail($code));
        // Mail::raw('Test mail', function ($message) {
        //     $message->to('songviet011@gmail.com')
        //         ->subject('Test');
        // });

        return response()->json([
            'status'  => 1,
            'message' => 'Đã gửi mã xác nhận quên mật khẩu đến email của bạn!',
        ]);
    }

    // 2) Xác thực mã
    public function xacThucMaQuenMatKhau(XacThucMaQuenMatKhauRequest $request)
    {
        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !$admin->hash_reset) {
            return response()->json([
                'status'  => 0,
                'message' => 'Mã không hợp lệ!',
            ], 400);
        }

        // Check hết hạn
        if ($admin->hash_reset_expires_at < now()) {
            return response()->json([
                'status' => 0,
                'message' => 'Mã đã hết hạn!',
            ], 400);
        }

        // Check đúng mã
        if (!Hash::check(trim((string)$request->code), $admin->hash_reset)) {
            return response()->json([
                'status' => 0,
                'message' => 'Mã không đúng!',
            ], 400);
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Mã xác nhận hợp lệ.',
        ]);
    }

    // 3) Đặt lại mật khẩu
    public function datLaiMatKhau(DatLaiMatKhauRequest $request)
    {
        \Log::info('Reset password attempt:', [
            'email' => $request->email,
            'code' => $request->code,
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            \Log::error('Email not found');
            return response()->json([
                'status'  => 0,
                'message' => 'Email không tồn tại!',
            ], 400);
        }

        if (!$admin->hash_reset) {
            \Log::error('Hash reset is null - code already used or not generated');
            return response()->json([
                'status'  => 0,
                'message' => 'Mã xác nhận không tồn tại! Có thể đã được sử dụng.',
                'debug' => [
                    'hash_reset' => $admin->hash_reset,
                    'expires_at' => $admin->hash_reset_expires_at,
                    'now' => now(),
                ]
            ], 400);
        }

        if ($admin->hash_reset_expires_at < now()) {
            return response()->json([
                'status' => 0,
                'message' => 'Mã đã hết hạn!',
                'debug' => [
                    'expires_at' => $admin->hash_reset_expires_at,
                    'now' => now(),
                ]
            ], 400);
        }

        if (!Hash::check($request->code, $admin->hash_reset)) {
            return response()->json([
                'status' => 0,
                'message' => 'Mã không đúng!',
            ], 400);
        }

        $admin->update([
            'password' => bcrypt($request->password),
            'hash_reset' => null,
            'hash_reset_expires_at' => null,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Đặt lại mật khẩu thành công!',
        ]);
    }

    // ✅ Lấy danh sách thông báo
    public function getNotifications()
    {
        /** @var Admin $user */
        $user = Auth::guard('sanctum')->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        // Lấy 20 thông báo gần nhất
        $notifications = $user->notifications()->latest()->take(20)->get();

        return response()->json($notifications);
    }

    // ✅ Đánh dấu tất cả đã đọc
    public function markNotificationsRead()
    {
        /** @var Admin $user */
        $user = Auth::guard('sanctum')->user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $user->unreadNotifications->markAsRead();

        return response()->json(['status' => true, 'message' => 'Đã đánh dấu tất cả là đã đọc']);
    }
}
