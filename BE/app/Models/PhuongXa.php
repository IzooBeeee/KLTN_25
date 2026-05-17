<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhuongXa extends Model
{
    protected $table = 'phuong_xas';

    protected $fillable = [
        'ten',
        'slug',
        'loai',
        'quan_huyen_id',
    ];

    public function quanHuyen(): BelongsTo
    {
        return $this->belongsTo(QuanHuyen::class, 'quan_huyen_id');
    }

    public function diaChis(): HasMany
    {
        return $this->hasMany(DiaChi::class, 'phuong_xa_id');
    }
}
