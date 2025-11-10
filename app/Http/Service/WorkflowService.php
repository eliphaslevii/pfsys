<?php

namespace App\Services;

use App\Models\Process;
use App\Models\ProcessWorkflow;
use App\Models\ProcessLog;
use App\Models\ProcessNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Exception;

class WorkflowService
{
    /**
     * Avança o processo para a próxima etapa.
     */
    public function advance(Process $process, array $data = [])
    {
        $user = Auth::user();
        $currentWorkflow = $process->currentWorkflow;

        if (!$currentWorkflow) {
            throw new Exception('Etapa atual não definida.');
        }

        if ($user->level_id != $currentWorkflow->required_level_id) {
            throw new Exception('Você não tem permissão para aprovar esta etapa.');
        }

        $next = ProcessWorkflow::where('process_type_id', $process->process_type_id)
            ->where('step_name', $currentWorkflow->next_step)
            ->first();

        if (!$next) {
            throw new Exception('Não há próxima etapa configurada.');
        }

        DB::beginTransaction();
        try {
            // Atualiza o processo
            $process->update([
                'current_workflow_id' => $next->id,
                'status' => $next->next_step ? 'Em Andamento' : 'Finalizado',
            ]);

            // Cria log
            ProcessLog::create([
                'process_id' => $process->id,
                'user_id' => $user->id,
                'action' => 'Transição de etapa',
                'message' => "{$currentWorkflow->step_name} → {$next->step_name}",
            ]);

            // Notificação (se configurada)
            if ($next->auto_notify) {
                $this->notify($process, $next);
            }

            DB::commit();
            return $next;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Envia o e-mail da próxima etapa (se existir configuração).
     */
    private function notify(Process $process, ProcessWorkflow $workflow)
    {
        $notification = ProcessNotification::where('workflow_id', $workflow->id)
            ->where('is_active', true)
            ->first();

        if (!$notification)
            return false;

        $to = array_filter(explode(',', $notification->to ?? ''));
        $cc = array_filter(explode(',', $notification->cc ?? ''));

        if (!empty($to)) {
            Mail::to($to)
                ->cc($cc)
                ->send(new \App\Mail\returnUpdateProcessMail($process, $workflow->step_name));
        }
        return true;
    }
}
