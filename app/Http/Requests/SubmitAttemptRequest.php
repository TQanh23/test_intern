<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAttemptRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * `answers` có dạng question_id => option_id. Câu bỏ trống thì không có trong mảng.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'answers' => ['sometimes', 'array'],
            'answers.*' => ['nullable', 'integer'],
        ];
    }
}
