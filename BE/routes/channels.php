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
    // Admin authenticate qua Sanctum → $user là Admin model
    return $user instanceof \App\Models\Admin && (int) $user->id === (int) $id;
});

// ── MoiGioi channel: private-user.{id} ────────────────────────────────
Broadcast::channel('user.{id}', function ($user, $id) {
    // MoiGioi authenticate qua Sanctum → $user là MoiGioi model
    return $user instanceof \App\Models\MoiGioi && (int) $user->id === (int) $id;
});
