<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // ✅ Bước 1: Thử lấy user từ Sanctum guard (session-based / đã được resolve trước)
        $user = Auth::guard('sanctum')->user();

        // ✅ Bước 2: Nếu null → fallback thủ công qua Bearer token
        if (!$user) {
            $token = $request->bearerToken();
            if ($token) {
                $accessToken = PersonalAccessToken::findToken($token);
                if ($accessToken) {
                    $user = $accessToken->tokenable;
                    // Set lại trên guard để auth()->user() hoạt động trong controller
                    Auth::guard('sanctum')->setUser($user);
                }
            }
        }

        if ($user && $user instanceof \App\Models\Admin) {
            return $next($request);
        }

        return response()->json([
            'status'  => false,
            'message' => 'Unauthorized: Bạn cần đăng nhập với tài khoản Admin',
        ], 401);
    }
}


































// $user = Auth::guard('sanctum')->user();

        // // nếu không có session → dùng Sanctum token
        // if (!$user) {
        //     $token = $request->bearerToken();

        //     if ($token) {
        //         $accessToken = PersonalAccessToken::findToken($token);

        //         if ($accessToken && $accessToken->tokenable instanceof \App\Models\Admin) {
        //             $user = $accessToken->tokenable;
        //         }
        //     }
        // }

        // if (!$user) {
        //     return response()->json([
        //         'message' => 'Bạn cần đăng nhập để thực hiện chức năng này'
        //     ], 401);
        // }

        // if (!($user instanceof \App\Models\Admin)) {
        //     return response()->json([
        //         'message' => 'Bạn cần đăng nhập Admin tai khoan'
        //     ], 401);
        // }

        // // set lại user cho guard admin
        // Auth::guard('sanctum')->setUser($user);

        // return $next($request);