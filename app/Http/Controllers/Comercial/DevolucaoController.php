<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Process;
use App\Models\ProcessItem;
use App\Models\ProcessLog;
use App\Models\ProcessType;
use App\Models\WorkflowReason;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowStep;
use App\Models\User;

class DevolucaoController extends Controller
{
    /**
     * Tela de criação
     */
    public function create()
    {
        // Recupera o tipo "Recusa"
        $recusaType = ProcessType::where('name', 'Devolucao')->firstOrFail();

        // Motivos SOMENTE ligados ao fluxo de Recusa
        $motivos = WorkflowReason::whereHas('workflowTemplate', function ($q) use ($recusaType) {
            $q->where('process_type_id', $recusaType->id)
                ->where('is_active', true);
        })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('comercial.recusa', [
            'motivos'      => $motivos,
            'solicitantes' => User::orderBy('name')->get(),
        ]);
    }
    /**
     * Grava a Devolução
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // Básico
            'motivo'        => 'required|string',
            'observacoes'   => 'required|string',
            'responsavel'   => 'required|string',

            // Cliente
            'cliente_nome'  => 'required|string',
            'cliente_cnpj'  => 'required|string',

            // Fiscal — DEVOLUÇÃO
            'nf_saida'      => 'required|string',
            'nf_devolucao'  => 'required|string',
            'nfo'           => 'nullable|string',
            'nprot'         => 'nullable|string',
            'codigo_erro' => 'required|string',
            // Itens
            'itens'         => 'required|json',
            'movimentacao_mercadoria' => 'nullable|boolean',

        ]);

        return DB::transaction(function () use ($data) {

            /* 1️⃣ Tipo de processo */
            $processType = ProcessType::where('name', 'Devolucao')->firstOrFail();

            /* 2️⃣ Workflow */
            $reason   = WorkflowReason::where('name', $data['motivo'])->firstOrFail();
            $template = WorkflowTemplate::findOrFail($reason->workflow_template_id);

            $firstStep = WorkflowStep::where('workflow_template_id', $template->id)
                ->orderBy('order')
                ->first();

            /* 3️⃣ Processo */
            $process = Process::create([
                'process_type_id'      => $processType->id,
                'workflow_template_id' => $template->id,
                'workflow_reason_id'   => $reason->id,
                'current_step_id'      => NULL,
                'created_by'           => Auth::id(),
                'responsavel'          => $data['responsavel'],
                'status'               => 'Em Andamento',

                // Motivo (texto livre, redundante por design)
                'motivo'               => $data['motivo'],

                // Cliente
                'cliente_nome' => $data['cliente_nome'],
                'cliente_cnpj' => $data['cliente_cnpj'],

                'codigo_erro' => $data['codigo_erro'],

                // Fiscal
                'nf_saida'     => $data['nf_saida'],
                'nf_devolucao' => $data['nf_devolucao'],
                'nfo'          => $data['nfo'] ?? $data['nf_saida'],
                'nprot'        => $data['nprot'] ?? null,

                'movimentacao_mercadoria' => isset($data['movimentacao_mercadoria']) ? 1 : 0,

                // Observações
                'observacoes'  => $data['observacoes'],
            ]);


            /* 4️⃣ Itens */
            foreach (json_decode($data['itens'], true) as $item) {
                ProcessItem::create([
                    'process_id'     => $process->id,
                    'artigo'         => $item['artigo'],
                    'descricao'      => $item['descricao'],
                    'ncm'            => $item['ncm'] ?? null,
                    'quantidade'     => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario'],
                ]);
            }

            /* 5️⃣ Log */
            ProcessLog::create([
                'process_id' => $process->id,
                'user_id'    => Auth::id(),
                'action'     => 'Criação',
                'message'    => 'Processo de devolução criado',
                'to_step_id' => $firstStep?->id,
            ]);

            return response()->json([
                'success'    => true,
                'message'    => 'Devolução registrada com sucesso.',
                'process_id' => $process->id,
            ]);
        });
    }
}
