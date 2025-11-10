<?php

namespace App\Http\Controllers\ReturnProcess;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Process;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class ReturnProcessStepController extends Controller
{
    protected WorkflowService $workflow;

    public function __construct(WorkflowService $workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * Atualiza o passo (etapa) de um processo.
     * Pode ser chamado via AJAX.
     */
    public function update($id, Request $request): JsonResponse
    {
        try {
            $process = Process::findOrFail($id);
            $action = $request->input('action', 'advance'); // padrão: avançar
            $msg = '';

            switch ($action) {
                case 'advance':
                    $next = $this->workflow->advance($process, $request->all());
                    $msg = $next
                        ? "Processo avançado para a etapa: {$next->step_name}."
                        : "Processo finalizado com sucesso.";
                    break;

                case 'reject':
                    $comment = $request->input('comment');
                    if (empty($comment)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Comentário obrigatório para rejeitar.'
                        ], 422);
                    }
                    $this->workflow->reject($process, $comment);
                    $msg = "Processo rejeitado com sucesso.";
                    break;

                case 'rollback':
                    $this->workflow->rollback($process);
                    $msg = "Processo retornado para a etapa anterior.";
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Ação inválida.'
                    ], 400);
            }

            Log::info("🧩 Processo {$process->id} atualizado via '{$action}' por " . (Auth::user()->email ?? 'system'));

            return response()->json([
                'success' => true,
                'message' => $msg,
                'process_id' => $process->id,
                'current_step' => $process->currentWorkflow->step_name ?? null,
            ]);

        } catch (Exception $e) {
            Log::error("Erro ao atualizar etapa do processo #{$id}: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar a etapa do processo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
