<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChucVucreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ten_chuc_vu' => 'required|string|max:255|unique:chuc_vus,ten_chuc_vu',
            'mo_ta'       => 'nullable|string',
            'tinh_trang'  => 'nullable|integer|in:0,1',

            'ten'         => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:admins,email',
            'password'    => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'ten.required' => 'Vui lòng nhập tên người quản lý',
            'email.required' => 'Vui lòng nhập email đăng nhập',
            'email.email' => 'Email không hợp lệ',
            'email.unique' => 'Email đã được sử dụng',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        ];
    }
}
