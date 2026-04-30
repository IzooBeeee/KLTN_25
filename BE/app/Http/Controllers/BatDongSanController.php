<?php

namespace App\Http\Controllers;

use App\Models\BatDongSan;
use App\Models\MoiGioi;
use App\Models\LoaiBatDongSan;
use App\Models\TrangThaiBatDongSan;
use App\Models\TinhThanh;
use App\Models\QuanHuyen;
use App\Models\DiaChi;
use App\Models\HinhAnhBatDongSan;
use App\Http\Requests\CreateBatDongSanRequest;
use App\Http\Requests\UpdateBatDongSanRequest;
use App\Http\Requests\SearchBatDongSanRequest;
use App\Http\Requests\ApproveOrRejectBatDongSanRequest;
use App\Http\Requests\ChangeBatDongSanStatusRequest;
use App\Http\Requests\DestroyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SearchBatDongSanAdminRequest;
use App\Http\Requests\UpdateImageBatDongSanRequest;
use App\Models\PhanQuyen;
use App\Events\BatDongSanMoiDang;
use App\Events\BatDongSanDuocDuyet;
use App\Events\BatDongSanBiTuChoi;
use App\Http\Requests\DeleteBatDongSanRequest;

class BatDongSanController extends Controller
{
    // Admin
    //Lấy danh sách BDS cho admin
    public function getData(Request $request)
    {
        $data = BatDongSan::with([
            'loai',
            'moiGioi',
            'diaChi.tinh',
            'diaChi.quan',
            'hinhAnh'
        ])
            // ✅ SẮP XẾP: Tin chưa duyệt (is_duyet = 0/false) lên trước
            ->orderByRaw("CASE WHEN is_duyet = 0 OR is_duyet = false THEN 0 ELSE 1 END")
            // ✅ Sau đó sắp xếp theo ngày tạo mới nhất
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    // Tìm kiếm BDS cho admin (có thể tìm theo tiêu đề, mô tả, giá, loại, địa chỉ)
    public function searchAdmin(SearchBatDongSanAdminRequest $request) // Chính xác
    {
        $id_chuc_nang = 60; // ID tìm kiếm BDS cho admin
        $user = Auth::guard('sanctum')->user();
        $check = PhanQuyen::where('id_chuc_vu', $user->id_chuc_vu)
            ->where('id_chuc_nang', $id_chuc_nang)
            ->first();
        if (!$user->is_super &&  !$check) {
            return response()->json([
                'status' => false,
                'message' => "Bạn không có quyền thực hiện chức năng này"
            ]);
        }
        // Lấy đúng keyword từ request
        $keyword = $request->keyword;

        if (!$keyword || trim($keyword) === '') {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng nhập từ khóa tìm kiếm'
            ], 400);
        }

        $noi_dung = '%' . $keyword . '%';

        // Tìm kiếm
        $data = BatDongSan::where('tieu_de', 'like', $noi_dung)
            ->orWhere('mo_ta', 'like', $noi_dung)
            ->orWhere('gia', 'like', $noi_dung)
            ->orWhere('loai_id', 'like', $noi_dung)
            ->orWhere('dia_chi_id', 'like', $noi_dung)
            ->get();

        // Kiểm tra nếu không có kết quả
        if ($data->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'Không tìm thấy bất động sản nào phù hợp',
                'data' => []
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    // Duyệt tin BDS (Dành cho admin, môi giới không có quyền này, khách hàng không có quyền này)
    public function duyetTin(ApproveOrRejectBatDongSanRequest $request) //chính xác
    {
        $id_chuc_nang = 5; // ID chức năng duyệt BDS
        $user = Auth::guard('sanctum')->user();
        $check = PhanQuyen::where('id_chuc_vu', $user->id_chuc_vu)
            ->where('id_chuc_nang', $id_chuc_nang)
            ->first();
        if (!$user->is_super &&  !$check) {
            return response()->json([
                'status' => false,
                'message' => "Bạn không có quyền thực hiện chức năng này"
            ]);
        }
        $bds = BatDongSan::find($request->id);
        if ($bds) {
            $isDuyet = (int) $request->input('is_duyet', 0);
            $bds->is_duyet = $isDuyet;
            if ($isDuyet == 1) {
                $bds->trang_thai_id = 2; // Đã duyệt
                $bds->save();
                // ✅ Dispatch event duyệt → listener gửi notification cho môi giới
                event(new BatDongSanDuocDuyet($bds));
            } else {
                $bds->trang_thai_id = 3; // Từ chối
                $bds->save();
                // ✅ Dispatch event từ chối → listener gửi notification cho môi giới
                $lyDo = $request->input('ly_do');
                event(new BatDongSanBiTuChoi($bds, $lyDo));
            }

            return response()->json([
                'status' => true,
                'message' => 'Thay đổi trạng thái duyệt thành công',
                'data' => [
                    'id'           => $bds->id,
                    'is_duyet'     => $bds->is_duyet,
                    'trang_thai_id' => $bds->trang_thai_id
                ]
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy BDS'
            ]);
        }
    }

    // Xóa BDS (Dành cho admin, môi giới có thể xóa nhưng khách hàng không có quyền này)
    public function delete($id) //chính xác 
    {
        // $id_chuc_nang = 4; // ID chức năng xóa BDS
        // $user = Auth::guard('sanctum')->user();
        // $check = PhanQuyen::where('id_chuc_vu', $user->id_chuc_vu)
        //     ->where('id_chuc_nang', $id_chuc_nang)
        //     ->first();
        // if (!$user->is_super &&  !$check) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => "Bạn không có quyền thực hiện chức năng này"
        //     ]);
        // }
        $data = BatDongSan::find($id);
        if ($data) {
            $data->delete();
            return response()->json([
                'status' => true,
                'message' => 'Xóa thành công'
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Không tìm thấy BDS'
        ]);
    }

    // Thay đổi trạng thái BDS (Dành cho admin, môi giới, khách hàng không có quyền này)
    public function changeStatus(ChangeBatDongSanStatusRequest $request) //chính xác
    {
        $id_chuc_nang = 61; // ID chức năng thay đổi trạng thái BDS ví dụ (đang bán, đã bán, tạm ngưng)
        $user = Auth::guard('sanctum')->user();
        $check = PhanQuyen::where('id_chuc_vu', $user->id_chuc_vu)
            ->where('id_chuc_nang', $id_chuc_nang)
            ->first();
        if (!$user->is_super &&  !$check) {
            return response()->json([
                'status' => false,
                'message' => "Bạn không có quyền thực hiện chức năng này"
            ]);
        }
        $bds = BatDongSan::find($request->id);
        if ($bds) {
            $bds->trang_thai_id = $request->trang_thai_id;
            $bds->save();
            return response()->json([
                'status' => true,
                'message' => 'Cập nhật trạng thái thành công'
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Không tìm thấy BDS'
        ]);
    }

    public function changeStatusMoiGioi(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        $bds = BatDongSan::where('id', $request->id)
            ->where('moi_gioi_id', $user->id)
            ->first();

        if ($bds) {
            $bds->trang_thai_id = $request->trang_thai_id;
            $bds->save();
            return response()->json([
                'status' => true,
                'message' => 'Cập nhật trạng thái thành công'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Không tìm thấy bất động sản hoặc bạn không có quyền'
        ], 403);
    }

    // Chi tiết BDS (Dành cho tất cả mọi người)
    public function xemChiTietBDS($id)
    {
        // $id_chuc_nang = 59; // ID chức năng xem danh sách BDS cho admin
        // $user = Auth::guard('sanctum')->user();
        // $check = PhanQuyen::where('id_chuc_vu', $user->id_chuc_vu)
        //     ->where('id_chuc_nang', $id_chuc_nang)
        //     ->first();
        // if (!$user->is_super &&  !$check) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => "Bạn không có quyền thực hiện chức năng này"
        //     ]);
        // }
        $data = BatDongSan::with([
            'anhDaiDien',
            'hinhAnh',
            'loai',
            'moiGioi',
            'diaChi',
            'diaChi.tinh',
            'diaChi.quan'
        ])
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->find($id);
        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => "Không tìm thấy bất động sản"
            ]);
        }

        $data->setAppends(['anh_dai_dien_url', 'is_expired']);

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    // MoiGioi
    // Lấy danh sách BDS của môi giới đang đăng (Dành cho môi giới, admin có thể xem tất cả, khách hàng không có quyền này)
    public function getDataDanhChoMoiGioi(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Chưa đăng nhập hoặc token không hợp lệ!'
            ], 401);
        }

        $perPage = $request->per_page ?? 10;

        $baseQuery = BatDongSan::where('moi_gioi_id', $user->id);

        $query = BatDongSan::with([
            'loai',
            'trangThai',
            'diaChi.quan',
            'diaChi.tinh',
            'hinhAnh'
        ])
            ->where('moi_gioi_id', $user->id)
            ->orderBy('created_at', 'desc');

        // 🔥 filter draft / published
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $data = $query->paginate($perPage);

        return response()->json([
            'status'  => true,
            'message' => 'Lấy danh sách BĐS thành công!',
            'data'    => $data,

            'stats' => [
                'total'     => (clone $baseQuery)->count(),
                'approved'  => (clone $baseQuery)->where('is_duyet', 1)->count(),
                'pending'   => (clone $baseQuery)->where('is_duyet', 0)->count(),
                'rejected'  => (clone $baseQuery)->where('is_duyet', 2)->count(),
            ]
        ]);
    }

    // Tạo bài đăng BĐS (Dành cho môi giới)
    public function store(CreateBatDongSanRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = Auth::guard('sanctum')->user();
            $status = $request->input('status', 'draft');
            $goi = $user->goiTin;

            if ($status === 'published') {
                if (!$user->is_active) {
                    return response()->json(['status' => false, 'message' => 'Tài khoản bị khóa'], 403);
                }

                if (!$user->goi_tin_id) {
                    return response()->json(['status' => false, 'message' => 'Bạn chưa mua gói'], 403);
                }

                if (now()->greaterThan($user->ngay_het_han_goi)) {
                    return response()->json(['status' => false, 'message' => 'Gói đã hết hạn'], 403);
                }

                if ($user->so_tin_con_lai <= 0) {
                    return response()->json(['status' => false, 'message' => 'Đã hết số tin đăng'], 403);
                }

                if (!$goi) {
                    return response()->json(['status' => false, 'message' => 'Gói không hợp lệ'], 403);
                }
            }

            // 1. Tạo hoặc lấy địa chỉ
            $diaChi = DiaChi::create([
                'tinh_id'           => $request->tinh_id,
                'quan_id'           => $request->quan_id,
                'phuong_xa_id'      => $request->phuong_id,
                'dia_chi_chi_tiet'  => $request->dia_chi_chi_tiet,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
            ]);

            // 2. Tạo Bất động sản
            $batDongSan = BatDongSan::create([
                'tieu_de'       => $request->tieu_de,
                'gia'           => $request->gia,
                'dien_tich'     => $request->dien_tich,
                'loai_id'       => $request->loai_id,
                'trang_thai_id' => $request->trang_thai_id,
                'mo_ta'         => $request->mo_ta,
                'dia_chi_id'    => $diaChi->id,
                'so_phong_ngu'  => $request->so_phong_ngu,
                'so_phong_tam'  => $request->so_phong_tam,

                // 🔥 QUAN TRỌNG
                'is_noi_bat'  => ($status === 'published' && $goi) ? $goi->gan_nhan_vip : false,
                'expires_at'  => ($status === 'published' && $goi) ? now()->addDays($goi->so_ngay) : null,
                'status'      => $status,

                'moi_gioi_id' => $user->id,
                'is_duyet'    => false, // Luôn chờ duyệt
            ]);

            if ($request->hasFile('hinh_anh')) {
                foreach ($request->file('hinh_anh') as $index => $file) {
                    $path = $file->store('bds/' . $batDongSan->id, 'public');

                    HinhAnhBatDongSan::create([
                        'bds_id' => $batDongSan->id,
                        'url' => $path,
                        'thu_tu' => $index,
                        'is_anh_dai_dien' => $index == 0,
                    ]);
                }
            }

            if ($status === 'published') {
                $user->decrement('so_tin_con_lai');
                \Illuminate\Support\Facades\Log::info('EVENT FIRED: BatDongSanMoiDang from store', ['bds_id' => $batDongSan->id]);
                event(new BatDongSanMoiDang($batDongSan));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => $status === 'published' ? 'Đăng tin thành công' : 'Đã lưu nháp',
                'data' => $batDongSan
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    //
    public function publish($id)
    {
        DB::beginTransaction();

        try {
            $user = Auth::guard('sanctum')->user();

            $bds = BatDongSan::where('id', $id)
                ->where('moi_gioi_id', $user->id)
                ->first();

            if (!$bds) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy bài đăng'
                ], 404);
            }

            if ($bds->status === 'published') {
                return response()->json([
                    'status' => false,
                    'message' => 'Bài đã được đăng trước đó'
                ]);
            }

            // 🔥 CHECK GÓI (giống store)
            if (!$user->is_active) {
                return response()->json(['status' => false, 'message' => 'Tài khoản bị khóa'], 403);
            }

            if (!$user->goi_tin_id) {
                return response()->json(['status' => false, 'message' => 'Bạn chưa mua gói'], 403);
            }

            if (now()->greaterThan($user->ngay_het_han_goi)) {
                return response()->json(['status' => false, 'message' => 'Gói đã hết hạn'], 403);
            }

            if ($user->so_tin_con_lai <= 0) {
                return response()->json(['status' => false, 'message' => 'Đã hết số tin đăng'], 403);
            }

            $goi = $user->goiTin;

            if (!$goi) {
                return response()->json(['status' => false, 'message' => 'Gói không hợp lệ'], 403);
            }

            // 🔥 UPDATE
            $bds->update([
                'status' => 'published',
                'is_noi_bat' => $goi->gan_nhan_vip,
                'expires_at' => now()->addDays($goi->so_ngay),
                'is_duyet' => false,
            ]);

            // 🔥 TRỪ TIN
            $user->decrement('so_tin_con_lai');

            // 🔥 EVENT
            \Illuminate\Support\Facades\Log::info('EVENT FIRED: BatDongSanMoiDang from publish', ['bds_id' => $bds->id]);
            event(new BatDongSanMoiDang($bds));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Đăng tin thành công',
                'data' => $bds
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }


    // Danh sách BDS của tôi
    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $query = BatDongSan::where('moi_gioi_id', $user->id);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'data' => $query->latest()->get()
        ]);
    }

    // Cập nhật bài đăng BDS (Dành cho môi giới, admin chỉ có thể duyệt khách hàng không có quyền này)
    public function update(UpdateBatDongSanRequest $request)
    {
        $user = Auth::guard('sanctum')->user();
        //Lấy bài đăng
        $data = BatDongSan::find($request->id);
        if (!$data || $data->moi_gioi_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn chỉ được cập nhật bài đăng của chính mình'
            ], 403);
        }

        //Lấy dữ liệu hợp lệ
        $updateData = $request->validated();
        unset($updateData['id']);

        DB::beginTransaction();
        try {
            //Update basic info
            $data->fill($updateData);

            // Chỉ reset is_duyet khi update bài đã published (không reset khi đang là nháp)
            if ($data->status !== 'draft') {
                $data->is_duyet = false;
            }
            $data->save();

            // Xử lý hình ảnh mới nếu có
            if ($request->hasFile('hinh_anh')) {
                foreach ($request->file('hinh_anh') as $index => $file) {
                    $path = $file->store('bds/' . $data->id, 'public');

                    HinhAnhBatDongSan::create([
                        'bds_id'          => $data->id,
                        'url'             => $path,
                        'thu_tu'          => $index,
                        'is_anh_dai_dien' => $index == 0 && !$data->hinhAnh()->where('is_anh_dai_dien', true)->exists(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật data thành công và đang chờ duyệt lại',
                'data' => $data->load(['hinhAnh', 'diaChi.tinh', 'diaChi.quan'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Lỗi cập nhật: ' . $e->getMessage()
            ], 500);
        }
    }

    // Xóa bài đăng BDS (Dành cho môi giới, admin chỉ có thể duyệt khách hàng không có quyền này)
    public function destroy(DeleteBatDongSanRequest $request)
    {
        $user = Auth::guard('sanctum')->user();
        $data = BatDongSan::find($request->id);
        if (!$data || $data->moi_gioi_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn chỉ được xóa bài đăng của chính mình'
            ], 403);
        }
        $data->delete();
        return response()->json([
            'status' => true,
            'message' => 'Xóa bài đăng thành công'
        ]);
    }

    public function setImage(UpdateImageBatDongSanRequest $request, $id)
    {
        $bds = BatDongSan::find($id);

        // Kiểm tra BĐS tồn tại
        if (!$bds) {
            return response()->json([
                'status'  => false,
                'message' => 'Bất động sản không tồn tại!',
            ], 404);
        }

        // Kiểm tra phân quyền: Chỉ môi giới sở hữu BĐS mới được sửa
        $user = Auth::guard('sanctum')->user();
        if ($bds->moi_gioi_id !== $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Bạn không có quyền sửa ảnh của BĐS này!',
            ], 403);
        }

        // Kiểm tra ảnh thuộc về BĐS này (tránh thao tác chéo)
        $anh = HinhAnhBatDongSan::where('id', $request->anh_id)
            ->where('bds_id', $id)
            ->first();

        if (!$anh) {
            return response()->json([
                'status'  => false,
                'message' => 'Ảnh không thuộc về BĐS này!',
            ], 400);
        }

        // Bỏ đánh dấu ảnh đại diện cũ của BĐS này
        HinhAnhBatDongSan::where('bds_id', $id)
            ->update(['is_anh_dai_dien' => false]);

        // Đánh dấu ảnh mới là đại diện
        $anh->update(['is_anh_dai_dien' => true]);

        return response()->json([
            'status'  => true,
            'message' => 'Đã chọn ảnh đại diện thành công!',
            'data'    => [
                'anh_id' => $anh->id,
                'anh_dai_dien_url' => asset('storage/' . $anh->url)
            ]
        ]);
    }
}
