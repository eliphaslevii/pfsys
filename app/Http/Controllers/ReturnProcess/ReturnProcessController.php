<?php

namespace App\Http\Controllers\ReturnProcess;

use App\Http\Controllers\Controller;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\{
    Process,
    ProcessType,
    ProcessItem,
    ProcessWorkflow,
    ProcessExecution,
};
use App\Http\Requests\StoreReturnProcessRequest;
use Exception;

class ReturnProcessController extends Controller
{
    protected WorkflowService $workflow;

    public function __construct(WorkflowService $workflow)
    {
        $this->middleware('auth');
        $this->workflow = $workflow;
    }

    /**
     * Exibe a página principal do módulo de processos de devolução/recusa.
     */
    public function index()
    {
        $processes = \App\Models\Process::with(['creator', 'currentWorkflow'])
            ->whereHas('type', fn($q) => $q->where('name', 'Devolução / Recusa'))
            ->orderByDesc('id')
            ->get();

        return view('returnProcess.index', compact('processes'));
    }


    /**
     * Endpoint AJAX: retorna lista de processos em JSON.
     * Usado pela tabela principal (index.blade.php).
     */
    public function data(): JsonResponse
    {
        try {
            $processes = Process::with(['creator', 'currentWorkflow', 'type'])
                ->whereHas('type', function ($q) {
                    $q->whereIn('name', ['Devolução', 'Recusa']); // 👈 aceita ambos
                })
                ->orderByDesc('id')
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'tipo' => $p->type->name ?? '-',
                        'cliente' => $p->cliente_nome ?? '-',
                        'cnpj' => $p->cliente_cnpj ?? '-',
                        'status' => $p->status,
                        'etapa' => $p->currentWorkflow?->step_name ?? '-',
                        'responsavel' => $p->creator?->name ?? '-',
                        'created_at' => $p->created_at?->format('d/m/Y H:i'),
                    ];
                });

            return response()->json(['data' => $processes]);
        } catch (Exception $e) {
            Log::error('Erro ao carregar lista de processos: ' . $e->getMessage());
            return response()->json(['data' => [], 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * Exibe o formulário de criação de um novo processo.
     */
    public function create()
    {
        return view('returnProcess.create');
    }

    /**
     * Cria um novo processo de devolução/recusa.
     */
    public function store(Request $request)
    {

        \Log::info('📥 Dados recebidos no store:', $request->all());

        try {
            // 🔹 Validação base (campos obrigatórios do formulário)
            $validated = $request->validate([
                'tipo' => 'required|string',
                'nomeCliente' => 'required|string',
                'cnpjCliente' => 'required|string',
                'motivo' => 'required|string',
                'codigoErro' => 'required|string',
                'observacao' => 'required|string',
                'gestorSolicitante' => 'required|string',
                'xml_file' => 'nullable|file|mimes:xml|max:5120',
                'itens' => 'required',
                // 🔹 Campos opcionais vindos do XML
                'nf_saida' => 'nullable|string',
                'nf_devolucao' => 'nullable|string',
                'recusa_sefaz' => 'nullable|string',
            ]);


            // 🔹 Trata "itens" (aceita string JSON ou array)
            $itens = $validated['itens'];
            if (is_string($itens)) {
                $itens = json_decode($itens, true);
            }

            if (!is_array($itens) || count($itens) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum item informado ou formato inválido.',
                ], 422);
            }

            // 🔹 Cria ou busca o tipo de processo
            $processType = ProcessType::firstOrCreate(
                ['name' => $validated['tipo']],
                ['description' => "Tipo criado automaticamente."]
            );

            $userId = Auth::id() ?? 1;

            // 🔹 Salva XML, se houver
            $xmlPath = $request->hasFile('xml_file')
                ? $request->file('xml_file')->store('processes/xmls', 'public')
                : null;

            // 🔹 Cria o processo principal com campos adicionais extraídos do XML
            $process = Process::create([
                'process_type_id' => $processType->id,
                'created_by' => $userId,
                'status' => 'Aberto',
                'cliente_nome' => $validated['nomeCliente'],
                'cliente_cnpj' => $validated['cnpjCliente'],
                'observacoes' => $validated['observacao'],
                'movimentacao_mercadoria' => false,
                'nf_saida' => $request->input('nf_saida'),
                'nf_devolucao' => $request->input('nf_devolucao'),
                'nfo' => $request->input('nfo'),
                'protocolo' => $request->input('protocolo'),
                'recusa_sefaz' => $request->input('recusa_sefaz'),
            ]);

            // 🔹 Cria os itens vinculados
            foreach ($itens as $item) {
                $process->items()->create([
                    'artigo' => $item['artigo'] ?? '-',
                    'descricao' => $item['descricao'] ?? '-',
                    'ncm' => $item['ncm'] ?? null,
                    'quantidade' => $item['quantidade'] ?? 0,
                    'preco_unitario' => $item['preco_unitario'] ?? 0,
                ]);
            }

            // 🔹 Anexa XML ao processo (opcional)
            if ($xmlPath) {
                $process->documents()->create([
                    'file_name' => basename($xmlPath),
                    'file_path' => $xmlPath,
                    'file_type' => 'xml',
                    'uploaded_by' => $userId,
                ]);
            }

            // 🚀 Define a primeira etapa do workflow automaticamente
            $firstStep = ProcessWorkflow::where('process_type_id', $processType->id)
                ->orderBy('id', 'asc')
                ->first();

            if ($firstStep) {
                $process->update([
                    'current_workflow_id' => $firstStep->id,
                    'status' => 'Em Andamento',
                ]);

                ProcessExecution::create([
                    'process_id' => $process->id,
                    'current_workflow_id' => $firstStep->id,
                    'assigned_to' => $userId,
                    'status' => 'Em Andamento',
                    'observations' => 'Execução inicial do processo criada automaticamente.',
                ]);
            }


            \Log::info("✅ Processo criado com sucesso: #{$process->id}");

            return response()->json([
                'success' => true,
                'message' => 'Processo criado com sucesso!',
                'id' => $process->id,
            ]);
        }

        // 🔹 Captura de erros de validação
        catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        }

        // 🔹 Captura de exceções gerais
        catch (\Exception $e) {
            \Log::error('❌ Erro ao criar processo: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao salvar processo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Exibe detalhes de um processo.
     */
    public function show($id, Request $request)
    {
        try {
            $process = Process::with(['items', 'currentWorkflow', 'creator', 'type'])->findOrFail($id);

            // 🔹 Mapeia todos os dados, inclusive os campos XML
            $payload = [
                'id' => $process->id,
                'tipo' => $process->type->name ?? '-',
                'cliente_nome' => $process->cliente_nome,
                'cliente_cnpj' => $process->cliente_cnpj,
                'status' => $process->status,
                'etapa_atual' => $process->currentWorkflow->step_name ?? '—',
                'observacoes' => $process->observacoes,
                'recusa_sefaz' => $process->recusa_sefaz,
                'nf_saida' => $process->nf_saida,
                'nf_devolucao' => $process->nf_devolucao,
                'nfo' => $process->nfo,
                'protocolo' => $process->protocolo,
                'delivery' => $process->delivery,
                'doc_faturamento' => $process->doc_faturamento,
                'ordem_entrada' => $process->ordem_entrada,
                'migo' => $process->migo,

                // 🔹 Itens do processo
                'items' => $process->items->map(function ($i) {
                    return [
                        'artigo' => $i->artigo,
                        'descricao' => $i->descricao,
                        'ncm' => $i->ncm,
                        'quantidade' => $i->quantidade,
                        'preco_unitario' => $i->preco_unitario,
                    ];
                }),
            ];

            // 🔹 Se for uma requisição AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'data' => $payload]);
            }

            // 🔹 Caso contrário, exibe a view normalmente
            return view('returnProcess.show', compact('process'));
        } catch (\Exception $e) {
            \Log::error("Erro ao carregar processo {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar o processo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Exclui um processo e seus registros relacionados.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            // 🔐 Verifica se o usuário tem a permissão
            if (!$user->level || !$user->level->permissions->contains('name', 'process.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para excluir processos.'
                ], 403);
            }

            $process = Process::findOrFail($id);

            if ($process->status === 'Finalizado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir um processo finalizado.'
                ], 400);
            }

            $process->delete();

            Log::warning("🗑️ Processo #{$id} excluído por " . $user->email);

            return response()->json([
                'success' => true,
                'message' => 'Processo excluído com sucesso.'
            ]);
        } catch (Exception $e) {
            Log::error("Erro ao excluir processo {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir o processo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
