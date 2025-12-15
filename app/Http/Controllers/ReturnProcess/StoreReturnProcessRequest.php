<?php

namespace App\Http\Controllers\ReturnProcess;


use Illuminate\Foundation\Http\FormRequest;

class StoreReturnProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => 'required|string|max:100',
            'nomeCliente' => 'required|string|max:255',
            'cnpjCliente' => 'required|string|max:20',
            'motivo' => 'required|string',
            'codigoErro' => 'required|string',
            'observacao' => 'nullable|string',
            'gestorSolicitante' => 'nullable|string',
            'movimentacaoMercadoria' => 'nullable|boolean',
        ];
    }
}
