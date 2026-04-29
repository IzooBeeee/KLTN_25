<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class MoiGioiMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // ✅ Bước 1: Thử Sanctum guard (session-based)
        $user = Auth::guard('sanctum')->user();

        // ✅ Bước 2: Fallback qua Bearer token nếu guard chưa resolve
        if (!$user) {
            $token = $request->bearerToken();
            if ($token) {
                $accessToken = PersonalAccessToken::findToken($token);
                if ($accessToken) {
                    $user = $accessToken->tokenable;
                    Auth::guard('sanctum')->setUser($user);
                }
            }
        }

        if ($user && $user instanceof \App\Models\MoiGioi) {
            return $next($request);
        }

        return response()->json([
            'status'  => false,
            'message' => 'Unauthorized: Bạn cần đăng nhập với tài khoản Môi Giới',
        ], 401);
    }
}
