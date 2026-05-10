<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveTaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'status_id' => ['required', Rule::exists('statuses', 'id')->where('user_id', $userId)],
            'country_id' => ['required', Rule::exists('countries', 'id')->where('user_id', $userId)],
            'position' => ['required', 'integer', 'min:0'],
        ];
    }
}
