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
        'is_super',
    ];



    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_super' => 'boolean',
    ];

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
