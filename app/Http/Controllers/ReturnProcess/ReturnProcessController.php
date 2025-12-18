<?php

namespace App\Http\Controllers\ReturnProcess;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProcessRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\{
    Process,
    ProcessType,
    WorkflowTemplate,
    WorkflowStep,
    User
};

class ReturnProcessController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * ======================================================
     * INDEX – Tela inicial (tabela + botão novo)
     * ======================================================
     */
    public function index()
    {
        $processes = Process::with(['currentStep', 'creator', 'type'])
            ->orderByDesc('id')
            ->get();

        $motivos = WorkflowTemplate::where('is_active', true)
            ->with('processType')
            ->orderBy('name')
            ->get();

        $solicitantes = User::orderBy('name')->get(['id', 'name']);

        return view('comercial.index', compact(
            'processes',
            'motivos',
            'solicitantes'
        ));
    }

    /**
     * ======================================================
     * DATA – JSON para DataTable
     * ======================================================
     */
    public function data(): JsonResponse
    {
        $data = Process::with(['currentStep', 'creator', 'type'])
            ->orderByDesc('id')
            ->get()
            ->map(fn($p) => [
                'id'        => $p->id,
                'tipo'      => $p->type->name,
                'cliente'   => $p->cliente_nome,
                'cnpj'      => $p->cliente_cnpj,
                'status'    => $p->status,
                'etapa'     => $p->currentStep?->name ?? 'Finalizado',
                'criado_por'=> $p->creator?->name,
                'created_at'=> $p->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json(['data' => $data]);
    }

    /**
     * ======================================================
     * STORE – Criação do processo (Recusa / Devolução)
     * ======================================================
     */
    public function store(StoreProcessRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {

            $data = $request->validated();

            $processType = ProcessType::findOrFail($data['process_type_id']);
            $template    = WorkflowTemplate::findOrFail($data['workflow_template_id']);

            // primeira etapa
            $firstStep = WorkflowStep::where('workflow_template_id', $template->id)
                ->orderBy('order')
                ->firstOrFail();

            $process = Process::create([
                'process_type_id'      => $processType->id,
                'workflow_template_id' => $template->id,
                'current_step_id'      => $firstStep->id,
                'created_by'           => Auth::id(),

                'cliente_nome' => $data['nomeCliente'],
                'cliente_cnpj' => $data['cnpjCliente'],

                'status'       => 'Em Andamento',
                'observacoes'  => $data['observacao'] ?? null,

                // XML
                'nf_saida'       => $data['nf_saida']       ?? null,
                'nf_devolucao'   => $data['nf_devolucao']   ?? null,
                'nfo'            => $data['nfo']            ?? null,
                'protocolo'      => $data['protocolo']      ?? null,
                'recusa_sefaz'   => $data['recusa_sefaz']   ?? null,
            ]);

            /**
             * -------------------------
             * ITENS
             * -------------------------
             */
            foreach (json_decode($data['itens'], true) as $item) {
                $process->items()->create([
                    'artigo'         => $item['artigo'],
                    'descricao'      => $item['descricao'],
                    'ncm'            => $item['ncm'] ?? null,
                    'quantidade'     => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario'],
                ]);
            }

            /**
             * -------------------------
             * XML
             * -------------------------
             */
            if ($request->hasFile('xml_file')) {
                $path = $request->file('xml_file')->store('processes/xmls', 'public');

                $process->documents()->create([
                    'file_name'  => basename($path),
                    'file_path'  => $path,
                    'file_type'  => 'xml',
                    'uploaded_by'=> Auth::id(),
                ]);
            }

            /**
             * -------------------------
             * LOG INICIAL
             * -------------------------
             */
            $process->logs()->create([
                'user_id' => Auth::id(),
                'action'  => 'CREATED',
                'message' => 'Processo criado via formulário com XML'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Processo criado com sucesso!',
                'id'      => $process->id
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('Erro ao criar processo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar processo.'
            ], 500);
        }
    }

    /**
     * ======================================================
     * SHOW – Modal / Detalhe
     * ======================================================
     */
    public function show(Process $process): JsonResponse
    {
        $process->load(['items', 'documents', 'currentStep', 'creator', 'type']);

        return response()->json([
            'success' => true,
            'data' => $process
        ]);
    }

    /**
     * ======================================================
     * DESTROY
     * ======================================================
     */
    public function destroy(Process $process): JsonResponse
    {
        if ($process->status === 'Finalizado') {
            return response()->json([
                'success' => false,
                'message' => 'Processo finalizado não pode ser excluído.'
            ], 422);
        }

        $process->delete();

        return response()->json([
            'success' => true,
            'message' => 'Processo excluído.'
        ]);
    }
}
