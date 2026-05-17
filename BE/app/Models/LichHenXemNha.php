<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LichHenXemNha extends Model
{
    protected $table = 'lich_hen_xem_nha';

    protected $fillable = [
        'bat_dong_san_id',
        'khach_hang_id',
        'moi_gioi_id',
        'ngay_hen',
        'gio_hen',
        'ghi_chu',
        'trang_thai',
        'ly_do_huy',
        'xac_nhan_at',
    ];

    protected $casts = [
        'ngay_hen' => 'date',
        'gio_hen' => 'datetime:H:i',
        'xac_nhan_at' => 'datetime',
    ];

    const STATUS_CHO_XAC_NHAN = 'cho_xac_nhan';
    const STATUS_DA_XAC_NHAN = 'da_xac_nhan';
    const STATUS_HOAN_THANH = 'hoan_thanh';
    const STATUS_HUY = 'huy';

    public function batDongSan(): BelongsTo
    {
        return $this->belongsTo(BatDongSan::class, 'bat_dong_san_id');
    }

    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    public function moiGioi(): BelongsTo
    {
        return $this->belongsTo(MoiGioi::class, 'moi_gioi_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->trang_thai) {
            self::STATUS_CHO_XAC_NHAN => 'Chờ xác nhận',
            self::STATUS_DA_XAC_NHAN => 'Đã xác nhận',
            self::STATUS_HOAN_THANH => 'Hoàn thành',
            self::STATUS_HUY => 'Đã hủy',
            default => 'Không xác định',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->trang_thai) {
            self::STATUS_CHO_XAC_NHAN => 'warning',
            self::STATUS_DA_XAC_NHAN => 'info',
            self::STATUS_HOAN_THANH => 'success',
            self::STATUS_HUY => 'danger',
            default => 'secondary',
        };
    }
}
