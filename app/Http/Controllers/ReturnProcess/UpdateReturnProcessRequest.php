<?php

namespace App\Http\Requests\ReturnProcess;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReturnProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'observacao' => 'nullable|string',
        ];
    }
}
