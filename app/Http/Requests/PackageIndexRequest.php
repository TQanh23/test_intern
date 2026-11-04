<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageIndexRequest extends FormRequest
{
    /**
     * Ai đã đăng nhập cũng xem được danh sách gói.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'level' => ['nullable', 'integer', 'in:1,2,3'],
        ];
    }
}
