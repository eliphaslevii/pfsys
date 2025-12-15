<?php

namespace App\Http\Controllers\ReturnProcess;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Models\{
    Process,
    WorkflowStep,
    ReturnProcessStep,
    WorkflowReason,
    ProcessStep
};

class ReturnProcessStepController extends Controller
{
    /**
     * Avança o processo para o próximo step no workflow.
     */
    public function update($id): JsonResponse
    {
        try {
            $process = Process::findOrFail($id);

            // Step atual
            $currentStep = ReturnProcessStep::where('process_id', $process->id)
                ->where('workflow_step_id', $process->workflow_step_id)
                ->first();

            if (!$currentStep) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum step ativo encontrado para este processo.'
                ], 404);
            }

            // 🔹 Marcar step atual como concluído
            $currentStep->update([
                'status' => 'Concluído',
                'completed_at' => now(),
            ]);

            // 🔹 Buscar workflow do motivo
            $reason = WorkflowReason::find($process->workflow_reason_id);
            $templateId = $reason->workflow_template_id;

            // 🔹 Buscar todos os steps do fluxo
            $steps = WorkflowStep::where('workflow_template_id', $templateId)
                ->orderBy('order', 'asc')
                ->get();

            // 🔹 Encontrar o próximo step
            $currentIndex = $steps->search(fn($s) => $s->id == $currentStep->workflow_step_id);
            $nextStep = $steps[$currentIndex + 1] ?? null;

            // =====================
            // 🔚 SE NÃO TEM PRÓXIMO STEP → FINALIZAR
            // =====================
            if (!$nextStep) {
                $process->update([
                    'status' => 'Finalizado',
                    'etapa_atual' => 'Finalizado',
                    'responsavel_setor' => null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Processo finalizado com sucesso.',
                    'newStep' => null
                ]);
            }

            // =====================
            // 👉 CRIAR O NOVO STEP
            // =====================
            ReturnProcessStep::create([
                'process_id' => $process->id,
                'workflow_step_id' => $nextStep->id,
                'status' => 'Em Andamento',
            ]);

            // =====================
            // 👉 ATUALIZAR PROCESSO
            // =====================
            $process->update([
                'workflow_step_id' => $nextStep->id,
                'responsavel_setor' => $nextStep->setor_id,
                'etapa_atual' => $nextStep->name,
                'status' => 'Em Execução',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Etapa concluída. Fluxo avançado.',
                'newStep' => $nextStep->name
            ]);
        }
        catch (\Throwable $e) {

            Log::error("Erro ao avançar processo {$id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao avançar etapa.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approve(Process $process)
{
    try {
        $current = $process->currentWorkflowStep;

        // Marcar step atual como concluído
        $process->steps()->where('is_current', true)->update([
            'status' => 'approved',
            'is_current' => false,
            'completed_at' => now(),
        ]);

        // Buscar próximo
        $nextStep = WorkflowStep::find($current->next_step_id);

        // Se não há etapa => finalizar processo
        if (!$nextStep) {
            $process->update([
                'status' => 'Finalizado',
                'current_workflow_step_id' => null,
            ]);

            ProcessStep::create([
                'process_id' => $process->id,
                'workflow_step_id' => null,
                'status' => 'approved',
                'is_current' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Processo finalizado com sucesso.'
            ]);
        }

        // Criar novo step
        ProcessStep::create([
            'process_id'        => $process->id,
            'workflow_step_id'  => $nextStep->id,
            'status'            => 'pending',
            'is_current'        => true,
        ]);

        // Atualizar processo
        $process->update([
            'current_workflow_step_id' => $nextStep->id,
            'status' => 'Em Execução',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Etapa avançada para {$nextStep->name}"
        ]);

    } catch (\Throwable $e) {
        Log::error("Erro ao aprovar processo {$process->id}: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erro ao avançar etapa.'
        ], 500);
    }
}

}
