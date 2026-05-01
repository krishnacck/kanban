<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveTaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status_id' => ['required', 'exists:statuses,id'],
            'country_id' => ['required', 'exists:countries,id'],
            'position' => ['required', 'integer', 'min:0'],
        ];
    }
}
