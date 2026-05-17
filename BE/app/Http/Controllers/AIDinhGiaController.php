<?php

namespace App\Http\Controllers;

use App\Models\BatDongSan;
use Illuminate\Http\Request;

class AIDinhGiaController extends Controller
{
    public function predictPrice(Request $request)
    {
        // Nhận dữ liệu đầu vào
        $loai_id = $request->input('loai_id');
        $dien_tich = (float) $request->input('dien_tich');
        $tinh_id = $request->input('tinh_id');
        $quan_id = $request->input('quan_id'); // Có thể có hoặc không

        if (empty($loai_id) || empty($dien_tich) || empty($tinh_id)) {
            return response()->json(['status' => false, 'message' => 'Vui lòng nhập đủ thông tin Loại BĐS, Tỉnh/Thành, Diện tích']);
        }

        if ($dien_tich <= 0 || $dien_tich > 100000) {
             return response()->json(['status' => false, 'message' => 'Diện tích không hợp lệ']);
        }

        // Lấy danh sách BĐS tương đồng (cùng Loại, cùng Tỉnh, và Quận nếu có) để tính đơn giá trung bình
        $query = BatDongSan::where('loai_id', $loai_id)
            ->whereHas('diaChi', function($q) use ($tinh_id, $quan_id) {
                $q->where('tinh_id', $tinh_id);
                if (!empty($quan_id)) {
                    $q->where('quan_id', $quan_id);
                }
            })
            ->where('dien_tich', '>', 0)
            ->where('gia', '>', 0);

        // Giới hạn dữ liệu tham khảo để tính toán (lấy 500 bđs gần nhất)
        // Select full fields needed for displaying reference list
        $properties = $query->with(['diaChi.tinh', 'diaChi.quan', 'diaChi.phuongXa'])
                            ->orderBy('id', 'desc')
                            ->limit(500)
                            ->get();

        $unitPrice = 0;

        if ($properties->count() > 0) {
            $totalGia = 0;
            $totalDienTich = 0;
            foreach ($properties as $p) {
                $totalGia += $p->gia;
                $totalDienTich += $p->dien_tich;
            }
            $unitPrice = $totalGia / $totalDienTich;
        } else {
            // Fallback: Nếu không có dữ liệu thật trong khu vực đó, dùng bộ định mức cơ bản
            // Căn hộ (1) ~ 40M/m2, Nhà phố (2) ~ 100M/m2, Biệt thự (3) ~ 150M/m2, Đất nền (4) ~ 50M/m2
            $fallbacks = [
                1 => 40000000,
                2 => 100000000,
                3 => 150000000,
                4 => 50000000
            ];
            $unitPrice = $fallbacks[$loai_id] ?? 60000000;
        }

        // Lấy 3 mẫu BĐS để hiển thị cho user xem tham khảo
        $referenceProperties = $properties->take(3)->map(function ($bds) {
            return [
                'id' => $bds->id,
                'tieu_de' => $bds->tieu_de,
                'gia' => $bds->gia,
                'dien_tich' => $bds->dien_tich,
                'anh_dai_dien_url' => $bds->anh_dai_dien_url,
                'dia_chi' => $bds->diaChi ? ($bds->diaChi->quan->ten ?? '') . ', ' . ($bds->diaChi->tinh->ten ?? '') : ''
            ];
        });

        // Tạo sự dao động ngẫu nhiên (hoặc dựa trên Quận) cho chân thực hơn
        // Nếu có quan_id thì sai số ít hơn, nếu chỉ có tinh_id thì sai số nhiều hơn
        $basePrice = $unitPrice * $dien_tich;
        
        $minVariance = $quan_id ? 0.92 : 0.85; // Dao động dưới 8% nếu có Quận, 15% nếu chỉ có Tỉnh
        $maxVariance = $quan_id ? 1.08 : 1.15;
        
        $minPrice = $basePrice * $minVariance;
        $maxPrice = $basePrice * $maxVariance;

        // Làm tròn đến hàng triệu (VD: 3,550,000,000)
        $minPrice = round($minPrice, -6);
        $maxPrice = round($maxPrice, -6);
        $basePrice = round($basePrice, -6);

        // Tính khoảng giá chênh lệch để hiển thị biểu đồ
        return response()->json([
            'status' => true,
            'data' => [
                'gia_du_doan_min' => $minPrice,
                'gia_du_doan_max' => $maxPrice,
                'gia_trung_binh' => $basePrice,
                'don_gia' => round($unitPrice, -5), // làm tròn đến trăm nghìn
                'so_mau_tham_khao' => $properties->count(),
                'danh_sach_tham_khao' => $referenceProperties
            ]
        ]);
    }
}
