<?php

namespace App\Http\Controllers\ReturnProcess;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Models\{
    Process,
    WorkflowStep,
    WorkflowReason,
    ProcessStep,
    ProcessExecution
};

class ReturnProcessFlowController extends Controller
{


    /**
     * ----------------------------------------------------------------------
     * 🔹 Retorna a timeline completa do processo
     * ----------------------------------------------------------------------
     */
    public function timeline(int $id)
    {
        $executions = ProcessExecution::where('process_id', $id)
            ->orderBy('id', 'asc')
            ->with('user')
            ->get();

        return response()->json([
            'success' => true,
            'timeline' => $executions
        ]);
    }


    /**
     * ----------------------------------------------------------------------
     * 🔹 Finalizar manualmente (caso um gerente precise forçar)
     * ----------------------------------------------------------------------
     */
    public function finalize(int $id)
    {
        try {
            $process = Process::findOrFail($id);

            $process->update([
                'status' => 'Finalizado',
                'etapa_atual' => 'Finalizado',
                'current_workflow_step_id' => null,
                'responsavel_setor' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Processo finalizado com sucesso.'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar processo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * ----------------------------------------------------------------------
     * 🔹 Regras de negócio por setor (ganchos personalizados)
     * ----------------------------------------------------------------------
     * Você pode colocar regras do Comercial, Fiscal, Logística, Financeiro etc.
     */
    private function applySectorRules(Process $process)
    {
        $sector = $process->currentWorkflowStep->sector_id;

        switch ($sector) {

            case 1: // Comercial
                break;

            case 2: // Fiscal
                break;

            case 3: // Logística
                break;

            case 4: // Gerência
                break;
        }
    }

    public function advance(Request $request, int $processId)
    {
        try {
            $process = Process::with(['currentWorkflowStep'])->findOrFail($processId);
            $process = Process::with(['currentWorkflowStep'])
                ->findOrFail($processId);

            $current = $process->currentWorkflowStep;
            $user = Auth::user();

            if (!$current) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma etapa ativa para este processo.'
                ], 422);
            }

            // -------------------------------------------------------
            // 🔐 1) VALIDAR PERMISSÃO PELO SETOR DA ETAPA
            // -------------------------------------------------------
            $sectorPermissions = [
                1 => 'process.step.comercial',     // Comercial
                2 => 'process.step.logistica',     // Logística
                3 => null,                         // Fiscal (não aprova etapas)
                4 => 'process.step.financeiro',    // Financeiro
                5 => null,                         // Administrativo
                6 => 'process.step.comercial',     // Gerência Comercial
            ];

            $requiredSectorId = $current->sector_id;
            $requiredPermission = $sectorPermissions[$requiredSectorId] ?? null;

            $canApprove = false;

            // Super admin sempre pode
            if ($user->level?->name === 'Super Admin') {
                $canApprove = true;
            }

            // Mesmo setor
            if ($user->sector_id == $requiredSectorId) {
                $canApprove = true;
            }

            // Permissão explícita
            if ($requiredPermission && $user->permissions->contains('name', $requiredPermission)) {
                $canApprove = true;
            }

            if (!$canApprove) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para aprovar esta etapa.'
                ], 403);
            }

            // -------------------------------------------------------
            // 📝 2) CAMPOS OBRIGATÓRIOS POR SETOR
            // -------------------------------------------------------

            $financeiroMotivosExtra = [
                "Material Descartado",
                "Devolução + sucateamento"
            ];

            $updates = [];

            switch ($current->name) {

                case 'Comercial (Refaturamento)':
                    $request->validate([
                        'delivery' => ['required', 'regex:/^5[0-9]*$/']
                    ]);
                    $updates['delivery'] = $request->delivery;
                    break;

                case 'Financeiro':
                    $request->validate([
                        'doc_faturamento' => 'required',
                        'ordem_entrada' => 'required'
                    ]);
                    $updates['doc_faturamento'] = $request->doc_faturamento;
                    $updates['ordem_entrada'] = $request->ordem_entrada;
                    break;

                case 'Financeiro (Pós-Logística)':

                    if (in_array($process->motivo, $financeiroMotivosExtra)) {
                        $request->validate([
                            'doc_faturamento' => 'required',
                            'ordem_entrada' => 'required',
                            'delivery' => ['required', 'regex:/^5[0-9]*$/']
                        ]);
                        $updates['delivery'] = $request->delivery;
                    } else {
                        $request->validate([
                            'doc_faturamento' => 'required',
                            'ordem_entrada' => 'required'
                        ]);
                    }

                    $updates['doc_faturamento'] = $request->doc_faturamento;
                    $updates['ordem_entrada'] = $request->ordem_entrada;
                    break;

                case 'Logística':
                case 'Logística (Refaturamento)':
                    $request->validate([
                        'migo' => 'required'
                    ]);
                    $updates['migo'] = $request->migo;
                    break;
            }

            // -------------------------------------------------------
            // 💾 3) Atualizar o PROCESSO com os campos do setor
            // -------------------------------------------------------
            if (!empty($updates)) {
                $process->update($updates);
            }

            // -------------------------------------------------------
            // 📘 4) Registrar finalização da etapa atual
            // -------------------------------------------------------
            ProcessExecution::create([
                'process_id' => $process->id,
                'current_workflow_step_id' => $current->id,
                'assigned_to' => $user->id,
                'approved_by' => $user->id,
                'status' => 'completed',
                'observations' => $request->observations ?? null,
                'completed_at' => now(),
            ]);

            // -------------------------------------------------------
            // 🔄 5) Avançar para a próxima etapa
            // -------------------------------------------------------
            $nextStep = WorkflowStep::where('workflow_template_id', $process->workflow_template_id)
                ->where('order', '>', $current->order)
                ->orderBy('order')
                ->first();

            if (!$nextStep) {
                $process->update([
                    'status' => 'Finalizado',
                    'etapa_atual' => 'Finalizado',
                    'current_workflow_step_id' => null,
                    'responsavel_setor' => null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Processo finalizado com sucesso.',
                    'finalizado' => true,
                ]);
            }

            // Marcar etapa atual como não ativa
            ProcessStep::where('process_id', $process->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            // Criar nova etapa
            ProcessStep::create([
                'process_id' => $process->id,
                'workflow_step_id' => $nextStep->id,
                'status' => 'pending',
                'is_current' => true,
            ]);

            // Atualizar processo
            $process->update([
                'current_workflow_step_id' => $nextStep->id,
                'etapa_atual' => $nextStep->name,
                'responsavel_setor' => $nextStep->sector_id,
                'status' => 'Em Execução',
            ]);

            return response()->json([
                'success' => true,
                'message' => "Etapa avançada para: {$nextStep->name}",
                'next_step' => $nextStep->name
            ]);

        } catch (\Throwable $e) {

            Log::error("Erro ao avançar etapa no processo {$processId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao avançar etapa.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
