<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Workflow
            'process_type_id'      => 'required|exists:process_types,id',
            'workflow_template_id' => 'required|exists:workflow_templates,id',

            // Cliente
            'nomeCliente' => 'required|string|max:255',
            'cnpjCliente' => 'required|string|max:20',

            // XML
            'nf_saida'     => 'nullable|string|max:50',
            'nf_devolucao' => 'nullable|string|max:50',
            'nfo'          => 'nullable|string|max:50',
            'protocolo'    => 'nullable|string|max:100',
            'recusa_sefaz' => 'nullable|string|max:100',

            // Itens
            'itens' => 'required|json',

            // Arquivo
            'xml_file' => 'nullable|file|mimes:xml|max:5120',

            // Observações
            'observacao' => 'nullable|string',
        ];
    }
}
