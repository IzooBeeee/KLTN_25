<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Broadcasting\PrivateChannel;

class MoiGioi extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $table = 'moi_giois';

    protected $fillable = [
        'ten',
        'email',
        'so_dien_thoai',
        'password',
        'avatar',
        'mo_ta',
        'zalo_link',
        'is_active',
        'trang_thai',
        'goi_tin_id',
        'so_tin_con_lai',
        'ngay_het_han_goi',
        'hash_reset',
        'hash_reset_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Tự động ép kiểu
    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'so_tin_con_lai' => 'integer',
        'ngay_het_han_goi' => 'datetime',
        'hash_reset_expires_at' => 'datetime',
    ];

    public function batDongSans(): HasMany
    {
        return $this->hasMany(BatDongSan::class, 'moi_gioi_id');
    }

    public function giaoDichs(): HasMany
    {
        return $this->hasMany(GiaoDich::class, 'moi_gioi_id');
    }

    public function lichSuGoiTins(): HasMany
    {
        return $this->hasMany(LichSuGoiTin::class, 'moi_gioi_id');
    }

    public function yeuThichs(): HasMany
    {
        return $this->hasMany(YeuThich::class, 'moi_gioi_id');
    }
    public function goiTin()
    {
        return $this->belongsTo(GoiTin::class, 'goi_tin_id' , 'id');
    }

    /**
     * ✅ Route broadcast notifications → channel: private-user.{id}
     * Channel name KHÔNG có prefix "private-" (Laravel Echo tự thêm).
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'user.' . $this->id;
    }
}
