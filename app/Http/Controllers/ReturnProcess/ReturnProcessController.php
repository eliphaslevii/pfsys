<?php

namespace App\Http\Controllers\ReturnProcess;

use App\Http\Controllers\AjaxController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ProcessType;
use Illuminate\Support\Facades\Auth;

class ReturnProcessController extends AjaxController
{
    public function index()
    {
        return view('returnProcess.index');
    }

    public function create()
    {
        return view('returnProcess.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipo' => 'required|string',
                'nomeCliente' => 'required|string',
                'cnpjCliente' => 'required|string',
                'motivo' => 'required|string',
                'codigoErro' => 'required|string',
                'observacao' => 'required|string',
                'gestorSolicitante' => 'required|string',
                'xml_file' => 'nullable|file|mimes:xml|max:5120',
                'itens' => 'required|string',
            ]);

            // 🔎 Busca o tipo de processo ("Recusa" / "Devolução")
            $processType = ProcessType::where('name', $validated['tipo'])->first();

            if (!$processType) {
                return response()->json([
                    'success' => false,
                    'message' => "Tipo de processo '{$validated['tipo']}' não encontrado em process_types."
                ], 422);
            }

            // 📄 Salva o XML (opcional)
            $xmlPath = null;
            if ($request->hasFile('xml_file')) {
                $xmlPath = $request->file('xml_file')->store('processes/xmls', 'public');
            }

            // 👤 Usuário logado
            $userId = Auth::id() ?? 1; // fallback se estiver testando sem login

            // 💾 Cria o processo principal
            // 🔎 Busca o tipo de processo (Recusa / Devolução)
            $processType = \App\Models\ProcessType::firstOrCreate(
                ['name' => $validated['tipo']],
                ['description' => "Tipo criado automaticamente pelo sistema."]
            );

            // 👤 Usuário logado ou fallback
            $userId = \Illuminate\Support\Facades\Auth::id() ?? 1; // usa ID 1 se for teste local sem login

            // 💾 Cria o processo principal
            $process = \App\Models\Process::create([
                'process_type_id' => $processType->id,
                'created_by' => $userId,
                'status' => 'Aberto',
                'cliente_nome' => $validated['nomeCliente'],
                'cliente_cnpj' => $validated['cnpjCliente'],
                'observacoes' => $validated['observacao'],
                'movimentacao_mercadoria' => false,
            ]);


            // 💾 Salva itens (JSON → array)
            $itens = json_decode($validated['itens'], true);
            foreach ($itens as $item) {
                $process->items()->create([
                    'artigo' => $item['artigo'] ?? '',
                    'descricao' => $item['descricao'] ?? '',
                    'ncm' => $item['ncm'] ?? '',
                    'quantidade' => $item['quantidade'] ?? 0,
                    'preco_unitario' => $item['preco_unitario'] ?? 0,
                ]);
            }

            // 📎 (opcional) cria registro em process_documents
            if ($xmlPath) {
                $process->documents()->create([
                    'file_name' => basename($xmlPath),
                    'file_path' => $xmlPath,
                    'file_type' => 'xml',
                    'uploaded_by' => $userId,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Processo salvo com sucesso!',
                'id' => $process->id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Falha na validação.',
                'data' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar processo: ' . $e->getMessage()
            ], 500);
        }
    }
    public function show($id)
    {
        return view('returnProcess.show', ['id' => $id]);
    }

    public function reject($id, Request $request): JsonResponse
    {
        return $this->ajaxResponse(true, 'Processo recusado com sucesso!');
    }

    public function destroy($id): JsonResponse
    {
        return $this->ajaxResponse(true, 'Processo excluído com sucesso!');
    }
}
