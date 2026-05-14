<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Broadcasting\PrivateChannel;

class Admin extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $table = 'admins';

    protected $fillable = [
        'ten',
        'email',
        'password',
        'mo_ta',
        'so_dien_thoai',
        'id_chuc_vu',
        'is_super',
        'is_active',
        'hash_reset',
        'hash_reset_expires_at',
    ];



    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_super' => 'boolean',
        'is_active' => 'boolean',
        'hash_reset_expires_at' => 'datetime',
    ];

    public function chucVu()
    {
        return $this->belongsTo(ChucVu::class, 'id_chuc_vu');
    }

    /**
     * ✅ Route broadcast notifications → channel: private-admin.{id}
     * PHẢI trả về ARRAY chứa PrivateChannel object.
     * Channel name KHÔNG có prefix "private-" (Laravel Echo tự thêm).
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'admin.' . $this->id;
    }
}
