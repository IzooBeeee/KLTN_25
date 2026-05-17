<?php

namespace App\Http\Controllers;

use App\Models\BatDongSan;
use Illuminate\Http\Request;

class MapController extends Controller
{
    //Lấy dữ liệu BĐS để hiển thị trên bản đồ
    public function getBatDongSanMap(Request $request)
    {
        $query = BatDongSan::with([
            'loai',
            'diaChi.tinh',
            'diaChi.quan',
            'moiGioi',
            'hinhAnh'
        ])
            ->where('is_duyet', true)
            ->where('status', 'published')
            ->whereHas('loai', fn($q) => $q->where('is_active', 1))
            ->whereHas('diaChi', function ($q) {
                $q->whereNotNull('latitude')
                    ->whereNotNull('longitude');
            });

        // Filter theo bounds (viewport)
        if ($request->has('bounds')) {
            $bounds = $request->bounds;
            $query->whereHas('diaChi', function ($q) use ($bounds) {
                $q->whereBetween('latitude', [$bounds['south'], $bounds['north']])
                    ->whereBetween('longitude', [$bounds['west'], $bounds['east']]);
            });
        }

        // Filter theo giá
        if ($request->has('min_price')) {
            $query->where('gia', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('gia', '<=', $request->max_price);
        }

        // Filter theo loại BĐS
        if ($request->has('loai_id')) {
            $query->where('loai_id', $request->loai_id);
        }

        // Filter theo từ khóa
        if ($request->has('keyword')) {
            $query->where('tieu_de', 'like', '%' . $request->keyword . '%');
        }

        $batDongSans = $query->get()->map(function ($bds) {
            return [
                'id' => $bds->id,
                'tieu_de' => $bds->tieu_de,
                'gia' => (float)$bds->gia,
                'gia_formatted' => $this->formatPrice($bds->gia),
                'dien_tich' => $bds->dien_tich,
                'anh_dai_dien_url' => $bds->anh_dai_dien_url,
                
                // 🔥 Phẳng hóa cho FE dễ dùng
                'dia_chi' => [
                    'lat' => (float)$bds->diaChi->latitude,
                    'lng' => (float)$bds->diaChi->longitude,
                    'dia_chi_chi_tiet' => $bds->diaChi->dia_chi_chi_tiet ?? '',
                    'tinh_ten' => $bds->diaChi->tinh->ten ?? '',
                    'quan_ten' => $bds->diaChi->quan->ten ?? '',
                ],

                'loai' => [
                    'ten_loai' => $bds->loai->ten_loai ?? 'BĐS',
                ],

                'moi_gioi' => [
                    'ten' => $bds->moiGioi->ten ?? '',
                    'sdt' => $bds->moiGioi->so_dien_thoai ?? '',
                ]
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $batDongSans,
            'total' => $batDongSans->count()
        ]);
    }

    //Format giá tiền
    private function formatPrice($price)
    {
        if ($price >= 1000000000) {
            return number_format($price / 1000000000, 2) . ' tỷ';
        }
        return number_format($price / 1000000, 0) . ' triệu';
    }

    //Lấy BĐS gần vị trí (?lat=16.0544&lng=108.2022&radius=5 (km))
    public function getNearbyProperties(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'nullable|numeric|max:50',
        ]);

        $lat = $request->lat;
        $lng = $request->lng;
        $radius = $request->radius ?? 5;

        // Lấy tất cả BĐS có diaChi, filter 
        $properties = BatDongSan::with(['diaChi.tinh', 'diaChi.quan', 'loai', 'moiGioi', 'hinhAnh', 'anhDaiDien'])
            ->where('is_duyet', true)
            ->where('status', 'published')
            ->whereHas('loai', fn($q) => $q->where('is_active', 1))
            ->whereHas('diaChi', function ($q) {
                $q->whereNotNull('latitude')
                    ->whereNotNull('longitude');
            })
            ->get()
            ->map(function ($bds) use ($lat, $lng) {
                $dist = $this->calculateDistance($lat, $lng, $bds->diaChi->latitude, $bds->diaChi->longitude);
                
                // Trả về cấu trúc đồng nhất
                return [
                    'id' => $bds->id,
                    'tieu_de' => $bds->tieu_de,
                    'gia' => (float)$bds->gia,
                    'gia_formatted' => $this->formatPrice($bds->gia),
                    'dien_tich' => $bds->dien_tich,
                    'anh_dai_dien_url' => $bds->anh_dai_dien_url,
                    'khoang_cach_km' => $dist,
                    
                    'dia_chi' => [
                        'lat' => (float)$bds->diaChi->latitude,
                        'lng' => (float)$bds->diaChi->longitude,
                        'dia_chi_chi_tiet' => $bds->diaChi->dia_chi_chi_tiet ?? '',
                        'tinh_ten' => $bds->diaChi->tinh->ten ?? '',
                        'quan_ten' => $bds->diaChi->quan->ten ?? '',
                    ],

                    'loai' => [
                        'ten_loai' => $bds->loai->ten_loai ?? 'BĐS',
                    ],

                    'moi_gioi' => [
                        'ten' => $bds->moiGioi->ten ?? '',
                        'sdt' => $bds->moiGioi->so_dien_thoai ?? '',
                    ]
                ];
            })
            ->filter(function ($item) use ($radius) {
                return $item['khoang_cach_km'] < $radius;
            })
            ->sortBy('khoang_cach_km')
            ->values();

        return response()->json([
            'status' => true,
            'data' => $properties,
            'center' => ['lat' => $lat, 'lng' => $lng],
            'radius_km' => $radius
        ]);
    }

    // Tính khoảng cách giữa 2 điểm (Haversine formula)
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }
}
