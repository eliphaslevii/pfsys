<?php

namespace App\Http\Requests\ReturnProcess;

use Illuminate\Foundation\Http\FormRequest;

class StepUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'step' => 'required|string|max:100',
        ];
    }
}
