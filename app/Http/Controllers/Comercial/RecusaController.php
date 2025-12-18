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

class RecusaController extends Controller
{
    /**
     * Tela de criação
     */

    public function create()
    {
        // Recupera o tipo "Recusa"
        $recusaType = ProcessType::where('name', 'Recusa')->firstOrFail();

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
     * Grava a Recusa
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // Negócio
            'motivo'                  => 'required|string',
            'codigo_erro'             => 'required|string',
            'responsavel'             => 'required|string',
            'observacoes'             => 'required|string',
            'movimentacao_mercadoria' => 'nullable|boolean',

            // Cliente (EMITENTE)
            'cliente_nome' => 'required|string',
            'cliente_cnpj' => 'required|string',

            // Fiscal — RECUSA
            'nfd'   => 'required|string',   // Nota recusada
            'nfo'   => 'required|string',   // = nfd
            'nprot' => 'nullable|string',   // Protocolo SEFAZ
            'inf_cpl' => 'nullable|string',

            // Itens
            'itens' => 'required|json',
        ]);

        return DB::transaction(function () use ($request, $data) {

            /* 1️⃣ Tipo de processo */
            $processType = ProcessType::where('name', 'Recusa')->firstOrFail();

            /* 2️⃣ Motivo → Workflow */
            $reason   = WorkflowReason::where('name', $data['motivo'])->firstOrFail();
            $template = WorkflowTemplate::findOrFail($reason->workflow_template_id);

            /* 3️⃣ Primeiro step */
            $firstStep = WorkflowStep::where('workflow_template_id', $template->id)
                ->orderBy('order')
                ->first();

            /* 4️⃣ Processo */
            $process = Process::create([
                'process_type_id'      => $processType->id,
                'workflow_template_id' => $template->id,
                'workflow_reason_id'   => $reason->id,
                'current_step_id'      => NULL,
                'created_by'           => Auth::id(),
                'status'               => 'Em Andamento',

                // Cliente
                'cliente_nome' => $data['cliente_nome'],
                'cliente_cnpj' => $data['cliente_cnpj'],

                // Responsabilidade
                'responsavel' => $data['responsavel'],
                'motivo'      => $data['motivo'],
                'codigo_erro' => $data['codigo_erro'],

                // Fiscal (RECUSA)
                'nfd'   => $data['nfd'],
                'nfo'   => $data['nfo'],   // sempre igual ao nfd
                'nprot' => $data['nprot'] ?? null,

                // Operacional
                'movimentacao_mercadoria' => isset($data['movimentacao_mercadoria']) ? 1 : 0,
                'observacoes'             => $data['observacoes'],
            ]);

            /* 5️⃣ Itens */
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

            /* 6️⃣ Log */
            ProcessLog::create([
                'process_id' => $process->id,
                'user_id'    => Auth::id(),
                'action'     => 'Criação',
                'message'    => 'Processo de recusa criado',
                'to_step_id' => $firstStep?->id,
            ]);

            return response()->json([
                'success'    => true,
                'message'    => 'Recusa registrada com sucesso.',
                'process_id' => $process->id,
            ]);
        });
    }
}
