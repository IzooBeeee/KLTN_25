<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveOrRejectBatDongSanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:bat_dong_sans,id',
            'is_duyet' => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'ID BĐS là bắt buộc',
            'id.exists' => 'BĐS không tồn tại',
            'is_duyet.required' => 'Trạng thái duyệt là bắt buộc',
            'is_duyet.in' => 'Trạng thái duyệt phải là 0 (từ chối) hoặc 1 (duyệt)',
        ];
    }
}
