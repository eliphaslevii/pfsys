<?php

namespace App\Http\Controllers\ReturnProcess;

use App\Http\Controllers\AjaxController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ProcessType;
use Illuminate\Support\Facades\Auth;
use App\Models\ReturnProcess;
use Illuminate\Support\Facades\DB;

class ReturnProcessController extends AjaxController
{
    public function index()
    {
        // 🔹 Garante que o tipo 'Devolução / Recusa' existe
        $processType = ProcessType::where('name', 'Devolução / Recusa')->first();

        // Caso não exista ainda, cria automaticamente
        if (!$processType) {
            $processType = ProcessType::create([
                'name' => 'Devolução / Recusa',
                'description' => 'Processos de devolução e recusa de mercadorias',
            ]);
        }

        // 🔹 Filtra os processos desse tipo
        $processes = ReturnProcess::where('process_type_id', $processType->id)
            ->latest()
            ->get();

        // 🔹 Estatísticas básicas
        $stats = [
            'total' => $processes->count(),
            'approved' => $processes->where('status', 'Aprovado')->count(),
            'pending' => $processes->where('status', 'Aberto')->count(),
            'rejected' => $processes->where('status', 'Recusado')->count(),
        ];

        return view('returnProcess.index', compact('stats', 'processes'));
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

            // 🔹 Busca ou cria o tipo de processo
            $processType = ProcessType::firstOrCreate(
                ['name' => $validated['tipo']],
                ['description' => "Tipo criado automaticamente pelo sistema."]
            );

            // 👤 Usuário logado ou fallback
            $userId = Auth::id() ?? 1;

            // 📄 Salva o XML (opcional)
            $xmlPath = null;
            if ($request->hasFile('xml_file')) {
                $xmlPath = $request->file('xml_file')->store('processes/xmls', 'public');
            }

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

            // 💾 Cria os itens vinculados
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

            /**
             * 🚀 Criação automática da execução (process_executions)
             */
            $firstStep = \App\Models\ProcessWorkflow::where('process_type_id', $processType->id)
                ->orderBy('id', 'asc')
                ->first();

            if ($firstStep) {
                \App\Models\ProcessExecution::create([
                    'process_id' => $process->id,
                    'current_workflow_id' => $firstStep->id,
                    'assigned_to' => $userId,
                    'status' => 'Em Andamento',
                    'observations' => 'Execução inicial do processo criada automaticamente.',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Processo e execução inicial criados com sucesso!',
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
    public function getProcessesData()
    {
        try {
            $processes = DB::table('processes')
                ->join('process_types', 'processes.process_type_id', '=', 'process_types.id')
                ->leftJoin('process_workflows', 'processes.current_workflow_id', '=', 'process_workflows.id')
                ->leftJoin('users', 'processes.created_by', '=', 'users.id')
                ->select(
                    'processes.id',
                    'process_types.name as tipo',
                    'processes.status',
                    'processes.cliente_nome as nomeCliente',
                    'processes.cliente_cnpj as cnpjCliente',
                    'processes.observacoes',
                    'process_workflows.step_name as step',
                    'users.name as responsavelInterno',
                    'processes.created_at'
                )
                ->orderByDesc('processes.id')
                ->get();

            return response()->json(['data' => $processes]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados dos processos.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
