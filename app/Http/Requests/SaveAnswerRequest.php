<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaveAnswerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * `option_id` phải có trong body nhưng được để null, nghĩa là bỏ chọn câu đó.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question_id' => ['required', 'integer'],
            'option_id' => ['present', 'nullable', 'integer'],
        ];
    }
}
