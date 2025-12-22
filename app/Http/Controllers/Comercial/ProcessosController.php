<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Process;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\WorkflowStep;
use App\Jobs\NotifyNextSectorJob;

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

            ->where('status', 'Em Andamento')

            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json([
            'data' => $processes->map(function (Process $p) use ($user) {

                $currentStep = $p->currentStep;

                $canAdvance = false;

                if ($currentStep && $user) {

                    if ($user->hasPermission('coreflow.admin')) {
                        $canAdvance = true;
                    } elseif (
                        $user->hasPermission('process.advance')
                        && $currentStep->sector_id !== null
                        && $user->sector_id === $currentStep->sector_id
                    ) {
                        $canAdvance = true;
                    }
                }


                return [
                    'id' => $p->id,
                    'tipo' => $p->processType->name ?? '-',
                    'nomeCliente' => $p->cliente_nome,
                    'cnpjCliente' => $p->cliente_cnpj,
                    'motivo' => $p->motivo ?? '-',
                    'codigoErro' => $p->codigo_erro,

                    'etapa' => $p->status === 'Finalizado'
                        ? 'Finalizado'
                        : ($currentStep?->name ?? 'Pendente Aprovação'),

                    'responsavel' => $p->responsavel,
                    'created_at' => $p->created_at->toISOString(),

                    // 🔁 WORKFLOW (FONTE DA VERDADE)
                    'current_step' => $currentStep?->name,
                    'current_step_sector' => $currentStep?->sector?->name, // 🔥 ESSENCIAL

                    // 🔐 CONTROLE DE FLUXO
                    'needs_approval' => is_null($p->current_step_id),
                    'can_approve'    => $user?->canApproveProcess() ?? false,
                    'can_advance'    => $canAdvance,
                    'can_delete'     => $user?->hasPermission('process.delete') ?? false,
                ];
            }),

            'meta' => [
                'current_page' => $processes->currentPage(),
                'last_page'    => $processes->lastPage(),
                'per_page'     => $processes->perPage(),
                'total'        => $processes->total(),
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
                'id'           => $process->id,
                'tipo'         => $process->processType->name,
                'cliente_nome' => $process->cliente_nome,
                'cliente_cnpj' => $process->cliente_cnpj,

                // fiscais
                'nf_saida'     => $process->nf_saida,
                'nf_devolucao' => $process->nf_devolucao,
                'nfo'          => $process->nfo,
                'nfd'          => $process->nfd,
                'nprot'        => $process->nprot,

                // workflow
                'motivo'       => $process->motivo ?? $process->workflowReason?->name,
                'codigo_erro'  => $process->codigo_erro,
                'status'       => $process->status,
                'etapa'        => $process->status === 'Finalizado'
                    ? 'Finalizado'
                    : ($process->currentStep?->name ?? 'Pendente Aprovação'),

                'observacoes'  => $process->observacoes,
            ],

            // 🔥 DADOS PREENCHIDOS AO LONGO DO FLUXO
            'process_data' => [
                'delivery'         => $process->delivery,
                'doc_faturamento'  => $process->doc_faturamento,
                'ordem_entrada'    => $process->ordem_entrada,
                'migo'             => $process->migo,
            ],

            'itens' => $process->items->map(fn($i) => [
                'artigo'         => $i->artigo,
                'descricao'      => $i->descricao,
                'ncm'            => $i->ncm,
                'quantidade'     => $i->quantidade,
                'preco_unitario' => number_format($i->preco_unitario, 2, ',', '.'),
            ]),
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
        /** @var User $user */
        $user = $request->user();

        if (!$user || !$user->hasPermission('process.approve')) {
            abort(403, 'Você não tem permissão para aprovar este processo.');
        }

        if ($process->current_step_id !== null) {
            return response()->json([
                'message' => 'Processo já iniciado.'
            ], 422);
        }

        DB::transaction(function () use ($process, $user, &$firstStep) {

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

        // 📧 NOTIFICA CRIADOR + PRIMEIRO SETOR
        NotifyNextSectorJob::dispatch(
            $process->fresh('currentStep'),
            $firstStep,
            'approved'
        );

        return response()->json([
            'message' => 'Processo aprovado com sucesso.'
        ]);
    }

    public function advance(Process $process, Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($process->status !== 'Em Andamento' || !$process->current_step_id) {
            return response()->json([
                'message' => 'Processo não pode ser avançado.'
            ], 422);
        }

        $currentStep = $process->currentStep;

        /*
    |--------------------------------------------------------------------------
    | 🔐 PERMISSÕES
    |--------------------------------------------------------------------------
    */
        if (!$user->hasPermission('coreflow.admin')) {

            // setor
            if (
                $currentStep->sector_id !== null &&
                $currentStep->sector_id !== $user->sector_id
            ) {
                abort(403, 'Você não pertence ao setor desta etapa.');
            }

            // nível (fallback legado, pode remover no futuro)
            if (
                $currentStep->required_level_id &&
                $user->level_id !== $currentStep->required_level_id
            ) {
                abort(403, 'Você não possui nível para avançar esta etapa.');
            }
        }

        /*
    |--------------------------------------------------------------------------
    | 💾 SALVAR DADOS DA ETAPA (NO PROCESSO)
    |--------------------------------------------------------------------------
    */
        $stepName = $currentStep->name;

        $stepFields = [
            'Comercial (Refaturamento)' => ['delivery'],
            'Fiscal' => ['doc_faturamento', 'ordem_entrada'],
            'Logística' => ['migo'],
            'Logística (Agendar Coleta)' => ['coleta_agendada'],
            'Contas a Pagar' => [],
        ];

        $allowedFields = $stepFields[$stepName] ?? [];


        foreach ($allowedFields as $field) {
            if (!$request->filled($field)) {
                return response()->json([
                    'message' => "Campo obrigatório não informado: {$field}"
                ], 422);
            }

            $updateData[$field] = $request->input($field);
        }

        if (!empty($updateData)) {
            $process->update($updateData);
        }

        /*
    |--------------------------------------------------------------------------
    | 🔎 PRÓXIMO STEP
    |--------------------------------------------------------------------------
    */
        $nextStep = WorkflowStep::where('workflow_template_id', $process->workflow_template_id)
            ->where('order', '>', $currentStep->order)
            ->orderBy('order')
            ->first();

        if (!$nextStep) {
            return response()->json([
                'message' => 'Fluxo inconsistente. Próxima etapa não encontrada.'
            ], 500);
        }

        /*
    |--------------------------------------------------------------------------
    | ▶️ AVANÇAR STEP
    |--------------------------------------------------------------------------
    */
        $process->update([
            'current_step_id' => $nextStep->id,
        ]);

        $process->logs()->create([
            'user_id'      => $user->id,
            'action'       => 'AVANÇO',
            'message'      => "Avançou para etapa {$nextStep->name}.",
            'from_step_id' => $currentStep->id,
            'to_step_id'   => $nextStep->id,
        ]);

        /*
    |--------------------------------------------------------------------------
    | 🏁 FINALIZAÇÃO
    |--------------------------------------------------------------------------
    */
        if ($nextStep->name === 'Finalizado') {

            $process->update([
                'status' => 'Finalizado',
                'current_step_id' => null,
            ]);

            $process->logs()->create([
                'user_id' => $user->id,
                'action'  => 'FINALIZAÇÃO',
                'message' => 'Processo finalizado automaticamente.',
            ]);

            return response()->json([
                'message' => 'Processo finalizado com sucesso.'
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | 📧 NOTIFICA PRÓXIMO SETOR
    |--------------------------------------------------------------------------
    */
        logger()->info('DISPATCH NotifyNextSectorJob', [
            'process_id' => $process->id,
            'step' => $nextStep->name,
        ]);

        NotifyNextSectorJob::dispatch(
            $process,
            $nextStep,
            'advanced'
        );

        return response()->json([
            'message'   => 'Etapa avançada com sucesso.',
            'next_step' => $nextStep->name,
        ]);
    }

    public function reject(Process $process, Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        // 🔐 permissão
        if (!$user->hasPermission('process.reject')) {
            abort(403, 'Você não tem permissão para recusar este processo.');
        }

        $currentStep = $process->currentStep;

        // 🔐 só Fiscal pode recusar
        if (!$currentStep || $currentStep->name !== 'Fiscal') {
            return response()->json([
                'message' => 'Recusa permitida apenas na etapa Fiscal.'
            ], 422);
        }

        $data = $request->validate([
            'comment' => 'required|string|min:5',
        ]);

        DB::transaction(function () use ($process, $user, $data, $currentStep) {

            // 🛑 atualiza processo
            $process->update([
                'status' => 'Recusado',
                'current_step_id' => null,
                'observacoes' => $data['comment'],
            ]);

            // 🧾 log
            $process->logs()->create([
                'user_id'      => $user->id,
                'action'       => 'RECUSA',
                'message'      => $data['comment'],
                'from_step_id' => $currentStep->id,
            ]);
        });

        // 📧 notificação
        NotifyNextSectorJob::dispatch(
            $process,
            null,
            'rejected'
        );

        return response()->json([
            'message' => 'Processo recusado com sucesso.'
        ]);
    }
}
