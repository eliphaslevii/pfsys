<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StepUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pode adicionar políticas se necessário
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|string|in:advance,reject,rollback',
            'comment' => 'nullable|string|max:1000',
            'docFaturamento' => 'nullable|string|max:255',
            'ordemEntrada' => 'nullable|string|max:255',
            'delivery' => 'nullable|string|max:255',
            'migo' => 'nullable|string|max:255',
            'skip_email' => 'nullable|boolean',
        ];
    }
}
