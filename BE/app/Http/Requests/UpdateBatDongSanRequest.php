<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBatDongSanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'               => 'required|integer|exists:bat_dong_sans,id',
            'tieu_de'          => 'required|string|max:255',
            'mo_ta'            => 'nullable|string',
            'gia'              => 'required|numeric|min:0',
            'dien_tich'        => 'required|numeric|min:0',
            'loai_id'          => 'required|integer|exists:loai_bat_dong_sans,id',
            'trang_thai_id'    => 'nullable|integer|exists:trang_thai_bat_dong_sans,id',
            'status'           => 'nullable|in:draft,published',
            'so_phong_ngu'     => 'nullable|integer|min:0',
            'so_phong_tam'     => 'nullable|integer|min:0',
            'tinh_id'          => 'required|integer|exists:tinh_thanhs,id',
            'quan_id'          => 'required|integer|exists:quan_huyens,id',
            'phuong_id'        => 'nullable|integer|exists:phuong_xas,id',
            'dia_chi_chi_tiet' => 'nullable|string|max:255',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'hinh_anh'         => 'nullable|array|max:10',
            'deleted_images'   => 'nullable|array',
            'deleted_images.*' => 'nullable|integer'
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'ID BĐS là bắt buộc',
            'id.exists' => 'BĐS không tồn tại',
            'tieu_de.required' => 'Tiêu đề là bắt buộc',
            'loai_id.exists' => 'Loại BĐS không tồn tại',
            'trang_thai_id.exists' => 'Trạng thái không tồn tại',
        ];
    }
}
