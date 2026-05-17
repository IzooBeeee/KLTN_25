<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatBotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string|max:100',
            'role' => 'nullable|string|in:khach-hang,moi-gioi,guest',
            'action_type' => 'nullable|string|max:120',
            'action_payload' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Vui lòng nhập tin nhắn',
            'message.string' => 'Tin nhắn phải là chuỗi ký tự',
            'message.max' => 'Tin nhắn không được vượt quá 1000 ký tự',
        ];
    }
}
