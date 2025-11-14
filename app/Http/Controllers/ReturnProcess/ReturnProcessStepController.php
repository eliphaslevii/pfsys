<?php

namespace App\Http\Controllers\ReturnProcess;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Process;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StepUpdateRequest;
use Exception;

class ReturnProcessStepController extends Controller
{
    protected WorkflowService $workflow;

    public function __construct(WorkflowService $workflow)
    {
        $this->middleware('auth');
        $this->workflow = $workflow;
    }

    /**
     * Atualiza o passo (etapa) de um processo.
     * Accepts action: advance | reject | rollback
     */
    public function update($id, StepUpdateRequest $request): JsonResponse
    {
        try {
            $process = Process::findOrFail($id);
            $action = $request->input('action', 'advance');

            // optional: guard clause to block actions on finalized/rejected processes
            if (in_array($process->status, ['Finalizado', 'Recusado'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Ação inválida: processo já está com status '{$process->status}'."
                ], 400);
            }

            $user = Auth::user();
            $result = null;

            switch ($action) {
                case 'advance':
                    // $request may contain extra fields (docFaturamento, delivery, migo, comment...)
                    $result = $this->workflow->advance($process, $request->only([
                        'docFaturamento', 'ordemEntrada', 'delivery', 'migo', 'comment', 'skip_email'
                    ]));

                    // WorkflowResult standard: ->success, ->message, ->nextStep
                    if (!($result instanceof \WorkflowResult ?? false) && is_object($result)) {
                        // backwards-compat fallback if your WorkflowService returns ProcessWorkflow
                        $nextStepName = $result->step_name ?? ($result->nextStep->step_name ?? null);
                    } else {
                        $nextStepName = $result->nextStep->step_name ?? null;
                    }

                    return response()->json([
                        'success' => true,
                        'message' => $result->message ?? 'Etapa avançada com sucesso.',
                        'newStep' => $nextStepName
                    ]);
                    break;

                case 'reject':
                    $comment = $request->input('comment') ?? '';
                    if (empty(trim($comment))) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Comentário obrigatório para rejeitar.'
                        ], 422);
                    }

                    $result = $this->workflow->reject($process, $comment);

                    return response()->json([
                        'success' => true,
                        'message' => $result->message ?? 'Processo rejeitado com sucesso.',
                        'newStep' => null
                    ]);
                    break;

                case 'rollback':
                    $result = $this->workflow->rollback($process);

                    $previousName = $result->nextStep->step_name ?? null;

                    return response()->json([
                        'success' => true,
                        'message' => $result->message ?? 'Processo retornado para etapa anterior.',
                        'newStep' => $previousName
                    ]);
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Ação inválida.'
                    ], 400);
            }
        } catch (Exception $e) {
            Log::error("Erro ao atualizar etapa do processo #{$id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);

            // Se for ModelNotFoundException, já capturamos com findOrFail e o Laravel vai 404, mas mantemos fallback:
            $status = $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500;

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar a etapa do processo.',
                'error' => $e->getMessage(),
            ], $status);
        }
    }
}
