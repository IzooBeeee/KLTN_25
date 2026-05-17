<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Channels cho hệ thống thông báo real-time:
|   - admin.{id}  → Admin nhận thông báo bài đăng mới chờ duyệt
|   - user.{id}   → Môi giới nhận thông báo duyệt/từ chối/khách quan tâm
|
*/

// ── Admin channel: private-admin.{id} ──────────────────────────────────
Broadcast::channel('admin.{id}', function ($user, $id) {
    \Illuminate\Support\Facades\Log::info('ADMIN Channel Auth Attempt', [
        'user_id' => $user->id,
        'user_class' => get_class($user),
        'requested_id' => $id
    ]);
    // Admin authenticate qua Sanctum → $user là Admin model
    return $user instanceof \App\Models\Admin && (int) $user->id === (int) $id;
});

// ── MoiGioi channel: private-user.{id} ────────────────────────────────
Broadcast::channel('user.{id}', function ($user, $id) {
    \Illuminate\Support\Facades\Log::info('Broadcasting Auth Attempt', [
        'user_id' => $user->id,
        'user_class' => get_class($user),
        'requested_id' => $id
    ]);
    
    // MoiGioi authenticate qua Sanctum → $user là MoiGioi model
    $match = $user instanceof \App\Models\MoiGioi && (int) $user->id === (int) $id;
    
    \Illuminate\Support\Facades\Log::info('Broadcasting Auth Result', ['match' => $match]);
    return $match;
});

// ── KhachHang channel: private-khach-hang.{id} ──────────────────────────
Broadcast::channel('khach-hang.{id}', function ($user, $id) {
    return $user instanceof \App\Models\KhachHang && (int) $user->id === (int) $id;
});
