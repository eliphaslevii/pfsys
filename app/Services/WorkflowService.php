<?php
namespace App\Services;

use App\Models\{
    Process,
    ProcessWorkflow,
    ProcessExecution,
    ProcessStep,
    ProcessLog,
    ProcessNotification
};
use Illuminate\Support\Facades\{DB, Mail, Log, Auth};
use Exception;

class WorkflowService
{
    /**
     * Avança o processo para a próxima etapa.
     */
    public function advance(Process $process, array $data = []): WorkflowResult
    {
        return DB::transaction(function () use ($process, $data) {
            $user = Auth::user();
            $current = $process->currentWorkflow;

            if (!$current) {
                throw new Exception('Etapa atual não definida.');
            }

            // Verifica se o usuário tem nível suficiente
            if ($user->level_id != $current->required_level_id) {
                throw new Exception('Você não tem permissão para aprovar esta etapa.');
            }

            // Próxima etapa
            $next = ProcessWorkflow::where('process_type_id', $process->process_type_id)
                ->where('motivo', $process->motivo)
                ->where('step_name', $current->next_step)
                ->first();

            // Atualiza execução
            $execution = ProcessExecution::firstOrCreate(
                ['process_id' => $process->id],
                [
                    'assigned_to' => $user->id,
                    'current_workflow_id' => $current->id,
                    'status' => 'Em Andamento',
                    'observations' => $data['comment'] ?? null,
                ]
            );

            // Finaliza etapa atual no histórico
            ProcessStep::where('process_id', $process->id)
                ->where('is_current', true)
                ->update(['is_current' => false, 'status' => 'Concluído', 'completed_at' => now()]);

            // Caso seja a última etapa
            if (!$next) {
                $process->update(['status' => 'Finalizado', 'current_workflow_id' => null]);
                $execution->update(['status' => 'Finalizado']);

                $this->log($process, 'Finalizado', "{$user->name} finalizou o processo.");
                return new WorkflowResult(true, 'Processo finalizado.', null);
            }

            // Atualiza processo e execução
            $process->update([
                'current_workflow_id' => $next->id,
                'status' => 'Em Andamento',
            ]);

            $execution->update([
                'current_workflow_id' => $next->id,
                'status' => 'Em Andamento',
            ]);

            // Cria novo step
            ProcessStep::create([
                'process_id' => $process->id,
                'workflow_id' => $next->id,
                'user_id' => $user->id,
                'status' => 'Em Andamento',
                'is_current' => true,
                'action' => 'Aguardando aprovação',
            ]);

            $this->log($process, 'Avançar etapa', "{$current->step_name} → {$next->step_name}");

            // Notificação automática
            if ($next->auto_notify) {
                $this->notify($process, $next);
            }

            return new WorkflowResult(true, "Avançado para '{$next->step_name}'.", $next);
        });
    }

    public function reject(Process $process, string $reason): WorkflowResult
    {
        return DB::transaction(function () use ($process, $reason) {
            $user = Auth::user();

            $process->update(['status' => 'Recusado']);
            ProcessExecution::where('process_id', $process->id)->update(['status' => 'Recusado']);
            ProcessStep::where('process_id', $process->id)->where('is_current', true)->update([
                'is_current' => false,
                'status' => 'Rejeitado',
                'completed_at' => now()
            ]);

            $this->log($process, 'Recusado', "Recusado por {$user->name}: {$reason}");
            $this->notifyRejection($process, $reason);

            return new WorkflowResult(true, 'Processo recusado.', null);
        });
    }

    public function rollback(Process $process): WorkflowResult
    {
        return DB::transaction(function () use ($process) {
            $user = Auth::user();
            $current = $process->currentWorkflow;
            $previous = ProcessWorkflow::where('process_type_id', $process->process_type_id)
                ->where('next_step', $current->step_name)
                ->first();

            if (!$previous) {
                throw new Exception('Não há etapa anterior.');
            }

            $process->update(['current_workflow_id' => $previous->id, 'status' => 'Em revisão']);
            ProcessExecution::where('process_id', $process->id)->update([
                'current_workflow_id' => $previous->id,
                'status' => 'Em revisão'
            ]);

            ProcessStep::create([
                'process_id' => $process->id,
                'workflow_id' => $previous->id,
                'user_id' => $user->id,
                'status' => 'Em Revisão',
                'is_current' => true,
                'action' => 'Rollback',
            ]);

            $this->log($process, 'Retorno', "{$user->name} retornou para {$previous->step_name}.");

            return new WorkflowResult(true, "Retornado para '{$previous->step_name}'.", $previous);
        });
    }

    private function notify(Process $process, ProcessWorkflow $workflow)
    {
        $notification = ProcessNotification::where('workflow_id', $workflow->id)->first();
        if (!$notification)
            return;

        try {
            $to = array_filter(explode(',', $notification->to ?? ''));
            $subject = $notification->subject ?? "Processo avançado: {$workflow->step_name}";
            if (!empty($to)) {
                Mail::raw("O processo #{$process->id} avançou para {$workflow->step_name}.", function ($m) use ($to, $subject) {
                    $m->to($to)->subject($subject);
                });
            }
        } catch (Exception $e) {
            Log::error("Erro ao enviar notificação: " . $e->getMessage());
        }
    }

    private function notifyRejection(Process $process, string $reason)
    {
        try {
            if ($process->creator?->email) {
                Mail::raw(
                    "O processo #{$process->id} foi recusado.\nMotivo: {$reason}",
                    fn($m) => $m->to($process->creator->email)->subject('Processo Recusado')
                );
            }
        } catch (Exception $e) {
            Log::error("Erro ao notificar recusa: " . $e->getMessage());
        }
    }

    private function log(Process $process, string $action, string $message)
    {
        ProcessLog::create([
            'process_id' => $process->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'message' => $message
        ]);
    }
}

/**
 * Resultado padronizado para todas as operações do Workflow
 */
class WorkflowResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public ?ProcessWorkflow $nextStep = null
    ) {
    }
}
