<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChucVuUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:chuc_vus,id',
            'ten_chuc_vu' => 'required|string|max:255|unique:chuc_vus,ten_chuc_vu,' . $this->id,
            'mo_ta' => 'nullable|string',
            'tinh_trang' => 'required|integer|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'Thiếu ID chức vụ',
            'id.exists' => 'Chức vụ không tồn tại',
            'ten_chuc_vu.required' => 'Vui lòng nhập tên chức vụ',
            'ten_chuc_vu.unique' => 'Tên chức vụ đã tồn tại',
            'ten_chuc_vu.max' => 'Tên chức vụ không được quá 255 ký tự',
            'mo_ta.required' => 'Vui lòng nhập mô tả',
            'tinh_trang.required' => 'Vui lòng chọn tình trạng',
            'tinh_trang.in' => 'Tình trạng không hợp lệ',
        ];
    }
}
