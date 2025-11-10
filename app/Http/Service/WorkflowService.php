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
     *
     * @param Process $process
     * @param array $data
     * @return ProcessWorkflow $next
     * @throws Exception
     */
    public function advance(Process $process, array $data = [])
    {
        $user = Auth::user();
        $current = $process->currentWorkflow;

        if (!$current) {
            throw new Exception('Etapa atual não definida.');
        }

        // 🔒 Validação de permissão (nível)
        if ($user->level_id != $current->required_level_id) {
            throw new Exception('Você não tem permissão para aprovar esta etapa.');
        }

        // 🔎 Busca o próximo step
        $next = ProcessWorkflow::where('process_type_id', $process->process_type_id)
            ->where('step_name', $current->next_step)
            ->first();

        DB::beginTransaction();

        try {
            if (!$next) {
                // Última etapa: marcar como finalizado
                $process->update([
                    'status' => 'Finalizado',
                    'current_workflow_id' => null,
                ]);

                ProcessLog::create([
                    'process_id' => $process->id,
                    'user_id' => $user->id,
                    'action' => 'Finalizado',
                    'message' => "{$user->name} finalizou o processo.",
                ]);

                DB::commit();
                return null;
            }

            // 🧭 Atualiza o processo para a próxima etapa
            $process->update([
                'current_workflow_id' => $next->id,
                'status' => $next->next_step ? 'Em Andamento' : 'Finalizado',
            ]);

            // 🕓 Cria log da transição
            ProcessLog::create([
                'process_id' => $process->id,
                'user_id' => $user->id,
                'action' => 'Avançar etapa',
                'message' => "{$current->step_name} → {$next->step_name}",
            ]);

            // ✉️ Notifica usuários se configurado
            if ($next->auto_notify) {
                $this->notify($process, $next);
            }

            DB::commit();
            return $next;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Erro ao avançar processo: " . $e->getMessage());
        }
    }
    public function reject(Process $process, string $reason)
    {
        $user = Auth::user();
        $current = $process->currentWorkflow;

        if (!$current) {
            throw new Exception('Etapa atual não definida.');
        }

        DB::beginTransaction();

        try {
            // 🚫 Atualiza o status do processo
            $process->update([
                'status' => 'Recusado',
            ]);

            // 🧾 Log da recusa
            ProcessLog::create([
                'process_id' => $process->id,
                'user_id' => $user->id,
                'action' => 'Recusado',
                'message' => "Processo recusado por {$user->name} na etapa {$current->step_name}: {$reason}",
            ]);

            // ✉️ Notifica criador do processo (ou responsáveis)
            $this->notifyRejection($process, $current, $reason);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Erro ao recusar processo: " . $e->getMessage());
        }
    }

    /**
     * Volta o processo para a etapa anterior.
     *
     * @param Process $process
     * @return ProcessWorkflow|null
     * @throws Exception
     */
    public function rollback(Process $process)
    {
        $user = Auth::user();
        $current = $process->currentWorkflow;

        if (!$current) {
            throw new Exception('Etapa atual não definida.');
        }

        // 🔎 Busca a etapa anterior (aquela cujo next_step é a atual)
        $previous = ProcessWorkflow::where('process_type_id', $process->process_type_id)
            ->where('next_step', $current->step_name)
            ->first();

        if (!$previous) {
            throw new Exception('Não há etapa anterior para retornar.');
        }

        DB::beginTransaction();

        try {
            // 🔁 Atualiza o processo
            $process->update([
                'current_workflow_id' => $previous->id,
                'status' => 'Em revisão',
            ]);

            // 📜 Log do retorno
            ProcessLog::create([
                'process_id' => $process->id,
                'user_id' => $user->id,
                'action' => 'Retorno de etapa',
                'message' => "{$user->name} retornou o processo para a etapa {$previous->step_name}.",
            ]);

            DB::commit();
            return $previous;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Erro ao retornar processo: " . $e->getMessage());
        }
    }

    /**
     * Notifica sobre recusa (mensagem simples).
     */
    private function notifyRejection(Process $process, ProcessWorkflow $workflow, string $reason): void
    {
        try {
            $to = $process->creator->email ?? null;

            if ($to) {
                Mail::raw(
                    "O processo #{$process->id} foi recusado na etapa '{$workflow->step_name}'.\n\nMotivo: {$reason}",
                    function ($message) use ($to) {
                        $message->to($to)->subject('Processo recusado');
                    }
                );
            }
        } catch (Exception $e) {
            \Log::error("Erro ao enviar notificação de recusa: " . $e->getMessage());
        }
    }
    /**
     * Envia notificação configurada para a etapa.
     */
    private function notify(Process $process, ProcessWorkflow $workflow): void
    {
        $notification = ProcessNotification::where('workflow_id', $workflow->id)
            ->where('is_active', true)
            ->first();

        if (!$notification) {
            return;
        }

        $to = array_filter(explode(',', $notification->to ?? ''));
        $cc = array_filter(explode(',', $notification->cc ?? ''));
        $subject = $notification->subject ?? "Processo atualizado: {$workflow->step_name}";

        // ⚠️ Aqui podemos plugar Mailables no futuro.
        try {
            if (!empty($to)) {
                Mail::raw(
                    "O processo #{$process->id} avançou para a etapa: {$workflow->step_name}.",
                    function ($message) use ($to, $cc, $subject) {
                        $message->to($to)->cc($cc)->subject($subject);
                    }
                );
            }
        } catch (Exception $e) {
            \Log::error("Erro ao enviar notificação: " . $e->getMessage());
        }
    }
}
