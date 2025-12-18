<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Process;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\WorkflowStep;

class ProcessosController extends Controller
{
    /**
     * View principal (tabela)
     */
    public function index()
    {
        return view('comercial.processos');
    }

    /**
     * Dados da tabela (Recusa + Devolução)
     */
    public function data(Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();

        $processes = Process::with([
            'processType',
            'workflowReason',
            'currentStep',
            'creator'
        ])
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json([
            'data' => $processes->map(function (Process $p) use ($user) {

                return [
                    'id' => $p->id,
                    'tipo' => $p->processType->name ?? '-',
                    'nomeCliente' => $p->cliente_nome,
                    'cnpjCliente' => $p->cliente_cnpj,
                    'motivo' => $p->motivo ?? '-',
                    'codigoErro' => $p->codigo_erro,
                    'etapa' => $p->currentStep?->name ?? 'Pendente Aprovação',
                    'responsavel' => $p->responsavel,
                    'created_at' => $p->created_at->toISOString(),

                    // 🔐 CONTROLE DE FLUXO
                    'needs_approval' => is_null($p->current_step_id),
                    'can_approve' => $user?->canApproveProcess() ?? false,
                    'can_delete'     => $user?->hasPermission('process.delete') ?? false,
                ];
            }),

            'meta' => [
                'current_page' => $processes->currentPage(),
                'last_page' => $processes->lastPage(),
                'per_page' => $processes->perPage(),
                'total' => $processes->total(),
            ]
        ]);
    }

    /**
     * Detalhes do processo (modal)
     */
    public function detalhes(int $id)
    {
        $process = Process::with([
            'items',
            'processType',
            'workflowReason',
            'currentStep'
        ])->findOrFail($id);

        return response()->json([
            'process' => [
                'id'            => $process->id,
                'tipo'          => $process->processType->name,
                'cliente_nome'  => $process->cliente_nome,
                'cliente_cnpj'  => $process->cliente_cnpj,

                // fiscais
                'nfd'           => $process->nfd,
                'nf_saida'      => $process->nf_saida,
                'nf_devolucao'  => $process->nf_devolucao,
                'nfo'           => $process->nfo,
                'nprot'         => $process->nprot,

                // workflow
                'motivo'        => $process->motivo ?? $process->workflowReason->name,
                'codigo_erro'   => $process->codigo_erro,
                'etapa' => $p->currentStep?->name ?? 'Pendente Aprovação',
                'status'        => $process->status,
                'observacoes'   => $process->observacoes,
            ],

            'itens' => $process->items->map(fn($i) => [
                'artigo'         => $i->artigo,
                'descricao'      => $i->descricao,
                'ncm'            => $i->ncm,
                'quantidade'     => $i->quantidade,
                'preco_unitario' => number_format($i->preco_unitario, 2, ',', '.'),
            ])
        ]);
    }

    public function destroy(Process $process, Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();

        // 🔐 Permissão
        if (!$user || !$user->hasPermission('process.delete')) {
            abort(403, 'Você não tem permissão para excluir este processo.');
        }

        DB::transaction(function () use ($process, $user) {

            // Log antes de excluir
            $process->logs()->create([
                'user_id' => $user->id,
                'action' => 'EXCLUSÃO',
                'message' => 'Processo excluído pelo gestor.',
            ]);

            $process->delete();
        });

        return response()->json([
            'message' => 'Processo excluído com sucesso.'
        ]);
    }
    public function approve(Process $process, Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user || !$user->hasPermission('process.approve')) {
            abort(403, 'Você não tem permissão para aprovar este processo.');
        }

        if ($process->current_step_id !== null) {
            return response()->json([
                'message' => 'Processo já iniciado.'
            ], 422);
        }

        DB::transaction(function () use ($process, $user) {

            $firstStep = WorkflowStep::where('workflow_template_id', $process->workflow_template_id)
                ->orderBy('order')
                ->firstOrFail();

            $process->update([
                'current_step_id' => $firstStep->id,
                'status' => 'Em Andamento',
            ]);

            $process->logs()->create([
                'user_id'   => $user->id,
                'action'    => 'APROVAÇÃO',
                'message'   => 'Processo aprovado e fluxo iniciado.',
                'to_step_id' => $firstStep->id,
            ]);
        });

        // 🔥 ISSO É O QUE ESTAVA FALTANDO
        return response()->json([
            'message' => 'Processo aprovado com sucesso.'
        ]);
    }
}
