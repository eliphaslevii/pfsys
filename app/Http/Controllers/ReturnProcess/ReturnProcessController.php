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
    WorkflowReason,
    WorkflowStep,
    ReturnProcess,
    ProcessStep,
};
use App\Http\Requests\StoreReturnProcessRequest;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Mail;


class ReturnProcessController extends Controller
{
    protected WorkflowService $workflow;

    public function __construct(WorkflowService $workflow)
    {
        $this->middleware('auth');
        $this->workflow = $workflow;
    }


    public function index()
    {
        $processes = Process::with(['creator', 'currentWorkflowStep', 'type'])
            ->whereHas(
                'type',
                fn($q) =>
                $q->whereIn('name', ['Recusa', 'Devolução'])
            )
            ->orderByDesc('id')
            ->get();

        // Agora PEGAMOS os motivos reais do banco
        $motivos = WorkflowReason::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        $solicitantes = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('returnProcess.index', compact('processes', 'motivos','solicitantes'));
    }



    /**
     * Endpoint AJAX: retorna lista de processos em JSON.
     * Usado pela tabela principal (index.blade.php).
     */
    public function data(): JsonResponse
    {
        try {
            $processes = Process::with(['creator', 'currentWorkflowStep', 'type'])
                ->whereHas(
                    'type',
                    fn($q) =>
                    $q->whereIn('name', ['Recusa', 'Devolução'])
                )
                ->orderByDesc('id')
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'tipo' => $p->type->name ?? '-',
                        'cliente' => $p->cliente_nome ?? '-',
                        'motivo' => $p->motivo ?? '-',
                        'codigoErro' => $p->codigo_erro ?? '-',
                        'cnpj' => $p->cliente_cnpj ?? '-',
                        'status' => $p->status,
                        'etapa' => $p->currentWorkflowStep?->name ?? $p->etapa_atual ?? '-',
                        'responsavel' => $p->creator?->name ?? $p->responsavelInterno ?? '-',
                        'created_at' => $p->created_at?->format('d/m/Y H:i'),
                    ];
                });

            return response()->json(['data' => $processes]);
        } catch (\Exception $e) {

            Log::error('Erro ao carregar lista de processos: ' . $e->getMessage());

            return response()->json([
                'data' => [],
                'error' => $e->getMessage()
            ], 500);
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
        Log::info('📥 Dados recebidos no store:', $request->all());

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
                'itens' => 'required',

                'nf_saida' => 'nullable|string',
                'nf_devolucao' => 'nullable|string',
                'recusa_sefaz' => 'nullable|string',
                'nfo' => 'nullable|string',
                'protocolo' => 'nullable|string',
            ]);
            // 🔒 Validações exclusivas Recusa / Devolução
            if ($validated['tipo'] === 'Recusa') {

                if (!empty($validated['nf_devolucao']) || !empty($validated['nfo'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Para processos do tipo RECUSA não é permitido enviar campos de devolução (nf_devolucao, nfo).',
                    ], 422);
                }
            }

            if ($validated['tipo'] === 'Devolução') {

                if (!empty($validated['nf_saida']) || !empty($validated['recusa_sefaz'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Para processos do tipo DEVOLUÇÃO não é permitido enviar campos de recusa (nf_saida, recusa_sefaz).',
                    ], 422);
                }
            }

            // -------------------------
            // Trata itens
            // -------------------------
            $itens = is_string($validated['itens'])
                ? json_decode($validated['itens'], true)
                : $validated['itens'];

            if (!is_array($itens) || count($itens) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum item informado.',
                ], 422);
            }

            // -------------------------
            // ProcessType único: Devolução / Recusa
            // -------------------------
            $processType = ProcessType::where('name', $validated['tipo'])->first();

            if (!$processType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de processo inválido. Deve ser Recusa ou Devolução.',
                ], 422);
            }


            $userId = Auth::id();

            // -------------------------
            // Upload XML
            // -------------------------
            $xmlPath = $request->hasFile('xml_file')
                ? $request->file('xml_file')->store('processes/xmls', 'public')
                : null;

            // -------------------------
            // 1) Encontra o motivo correto
            // -------------------------
            $reason = WorkflowReason::where('name', $validated['motivo'])
                ->whereHas('template', function ($q) use ($processType) {
                    $q->where('process_type_id', $processType->id);
                })
                ->first();

            if (!$reason) {
                Log::error("❌ Motivo '{$validated['motivo']}' não vinculado a nenhum fluxo.");
                return response()->json([
                    'success' => false,
                    'message' => "Nenhum fluxo está configurado para o motivo '{$validated['motivo']}'.",
                ], 422);
            }

            // -------------------------
            // 2) Busca primeira etapa do fluxo
            // -------------------------
            $firstStep = WorkflowStep::where('workflow_template_id', $reason->workflow_template_id)
                ->orderBy('order', 'asc')
                ->first();

            if (!$firstStep) {
                return response()->json([
                    'success' => false,
                    'message' => 'O fluxo selecionado não possui etapas.',
                ], 422);
            }

            // -------------------------
            // 3) Criar processo
            // -------------------------
            $process = Process::create([
                'process_type_id' => $processType->id,
                'created_by' => $userId,
                'cliente_nome' => $validated['nomeCliente'],
                'cliente_cnpj' => $validated['cnpjCliente'],
                'observacoes' => $validated['observacao'],
                'status' => 'Pendente Comercial',
                'etapa_atual' => 'Aguardando Aprovação Comercial',
                'motivo' => $validated['motivo'],
                'workflow_reason_id' => $reason->id,
                'workflow_template_id' => $reason->workflow_template_id,
                'current_workflow_step_id' => $firstStep->id,
                'codigo_erro' => $validated['codigoErro'],
                'nf_saida' => $validated['nf_saida'] ?? null,
                'nf_devolucao' => $validated['nf_devolucao'] ?? null,
                'nfo' => $validated['nfo'] ?? null,
                'protocolo' => $validated['protocolo'] ?? null,
                'recusa_sefaz' => $validated['recusa_sefaz'] ?? null,
            ]);

            // -------------------------
            // 4) Itens
            // -------------------------
            foreach ($itens as $item) {
                $process->items()->create([
                    'artigo' => $item['artigo'],
                    'descricao' => $item['descricao'],
                    'ncm' => $item['ncm'] ?? null,
                    'quantidade' => $item['quantidade'] ?? 0,
                    'preco_unitario' => $item['preco_unitario'] ?? 0,
                ]);
            }

            // -------------------------
            // 5) Documento XML
            // -------------------------
            if ($xmlPath) {
                $process->documents()->create([
                    'file_name' => basename($xmlPath),
                    'file_path' => $xmlPath,
                    'file_type' => 'xml',
                    'uploaded_by' => $userId,
                ]);
            }

            // -------------------------
            // 6) Execução inicial
            // -------------------------
            ProcessExecution::create([
                'process_id' => $process->id,
                'current_workflow_step_id' => $firstStep->id,
                'assigned_to' => $userId,
                'status' => 'Em Andamento',
                'observations' => 'Execução inicial gerada automaticamente.',
            ]);
            ProcessStep::create([
                'process_id' => $process->id,
                'workflow_step_id' => $firstStep->id,
                'status' => 'pending', // padrão da tabela
                'is_current' => true,
            ]);
            // -------------------------
            // 8) Enviar e-mail para o gestor comercial responsável pela etapa
            // -------------------------
            try {
                $emailsGerencia = \App\Models\User::where('sector_id', $firstStep->sector_id)
                    ->where('level_id', $firstStep->required_level_id)
                    ->pluck('email')
                    ->toArray();

                foreach ($emailsGerencia as $email) {
                    Mail::raw(
                        "Novo processo #{$process->id} está aguardando aprovação comercial.",
                        function ($mail) use ($email, $process) {
                            $mail->to($email)
                                ->subject("Processo #{$process->id} – Aguardando Aprovação Comercial");
                        }
                    );
                }
            } catch (\Throwable $e) {
                Log::error("Erro ao enviar e-mail para gestor comercial: " . $e->getMessage());
            }

            Log::info("✅ Processo criado com sucesso: {$process->id}");

            return response()->json([
                'success' => true,
                'message' => 'Processo criado com sucesso!',
                'id' => $process->id,
            ]);
        } catch (\Exception $e) {

            Log::error("❌ Erro ao criar processo: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao salvar processo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Exibe detalhes de um processo.
     */
    public function show($id, Request $request)
    {
        try {
            $process = Process::with(['items', 'currentWorkflowStep', 'creator', 'type'])->findOrFail($id);

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
            Log::error("Erro ao carregar processo {$id}: " . $e->getMessage());
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
