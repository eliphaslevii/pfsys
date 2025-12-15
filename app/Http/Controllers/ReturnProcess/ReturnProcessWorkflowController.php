<?php

namespace App\Http\Controllers\ReturnProcess;

use App\Models\Process;
use App\Http\Controllers\Controller;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;


class ReturnProcessWorkflowController extends Controller
{
    protected WorkflowService $workflow;

    public function __construct(WorkflowService $workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * Avança o processo para a próxima etapa.
     */
    public function advance(Process $process, Request $request)
    {
        try {
            $next = $this->workflow->advance($process, $request->all());

            return redirect()
                ->back()
                ->with('success', $next
                    ? "Processo avançado para a etapa '{$next->step_name}'."
                    : 'Processo finalizado com sucesso!'
                );
        } catch (Exception $e) {
            Log::error("Erro ao avançar processo {$process->id}: " . $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * Rejeita o processo com motivo informado.
     */
    public function reject(Process $process, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->workflow->reject($process, $request->input('reason'));
            return redirect()->back()->with('warning', 'Processo rejeitado com sucesso.');
        } catch (Exception $e) {
            Log::error("Erro ao recusar processo {$process->id}: " . $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * Retorna o processo para a etapa anterior.
     */
    public function rollback(Process $process)
    {
        try {
            $previous = $this->workflow->rollback($process);
            return redirect()
                ->back()
                ->with('info', "Processo retornado para a etapa '{$previous->step_name}'.");
        } catch (Exception $e) {
            Log::error("Erro ao retornar processo {$process->id}: " . $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
