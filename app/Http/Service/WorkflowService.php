<?php

namespace App\Services;

use App\Models\Process;
use App\Models\ProcessWorkflow;
use App\Models\ProcessExecution;
use App\Models\ProcessLog;
use App\Models\ProcessNotification;
use App\Models\ProcessStep;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class WorkflowService
{
    /**
     * Avança o processo para a próxima etapa.
     *
     * @param Process $process
     * @param array $data
     * @return ProcessWorkflow|null  Próxima etapa ou null se finalizado
     * @throws Exception
     */
    public function advance(Process $process, array $data = []): ?ProcessWorkflow
    {
        $user = Auth::user();
        $current = $process->currentWorkflow;

        if (!$current) {
            throw new Exception('Etapa atual não definida.');
        }

        // Verificação de permissão por nível
        if (($user->level_id ?? null) != ($current->required_level_id ?? null)) {
            throw new Exception('Você não tem permissão para aprovar esta etapa.');
        }

        $next = ProcessWorkflow::where('process_type_id', $process->process_type_id)
            ->where('step_name', $current->next_step)
            ->first();

        DB::beginTransaction();

        try {
            // Busca ou cria a execução ativa
            $execution = ProcessExecution::where('process_id', $process->id)
                ->latest()
                ->first();

            if (!$execution) {
                $execution = ProcessExecution::create([
                    'process_id' => $process->id,
                    'current_workflow_id' => $current->id,
                    'assigned_to' => $user->id,
                    'status' => 'Em Andamento',
                    'observations' => 'Execução criada automaticamente ao avançar.',
                ]);
            }

            // Marca step atual como concluído no histórico (se existir)
            ProcessStep::where('process_id', $process->id)
                ->where('is_current', true)
                ->update([
                    'is_current' => false,
                    'status' => 'Concluído',
                    'completed_at' => now(),
                ]);

            if (!$next) {
                // Última etapa: finalizar processo e execução
                $process->update([
                    'status' => 'Finalizado',
                    'current_workflow_id' => null,
                ]);

                $execution->update([
                    'status' => 'Finalizado',
                    'current_workflow_id' => null,
                ]);

                ProcessLog::create([
                    'process_id' => $process->id,
                    'user_id' => $user->id,
                    'action' => 'Finalizado',
                    'message' => "{$user->name} finalizou o processo.",
                ]);

                // registra step finalizado (opcional)
                ProcessStep::create([
                    'process_id' => $process->id,
                    'workflow_id' => $current->id,
                    'user_id' => $user->id,
                    'status' => 'Concluído',
                    'action' => 'Finalizar processo',
                    'comments' => $data['comment'] ?? null,
                    'is_current' => false,
                    'completed_at' => now(),
                ]);

                DB::commit();

                Log::info("✅ Processo #{$process->id} finalizado por {$user->email}");
                return null;
            }

            // Atualiza processo e execução para o próximo workflow
            $process->update([
                'current_workflow_id' => $next->id,
                'status' => $next->next_step ? 'Em Andamento' : 'Finalizado',
            ]);

            $execution->update([
                'current_workflow_id' => $next->id,
                'status' => 'Em Andamento',
                'updated_at' => now(),
            ]);

            // Log da transição
            ProcessLog::create([
                'process_id' => $process->id,
                'user_id' => $user->id,
                'action' => 'Avançar etapa',
                'message' => "{$current->step_name} → {$next->step_name}",
            ]);

            // Cria novo registro em process_steps indicando etapa atual
            ProcessStep::create([
                'process_id' => $process->id,
                'workflow_id' => $next->id,
                'user_id' => $user->id,
                'status' => 'Em Andamento',
                'action' => 'Aguardando aprovação',
                'comments' => $data['comment'] ?? null,
                'is_current' => true,
            ]);

            // Notifica responsáveis da próxima etapa, se configurado
            if ($next->auto_notify) {
                $this->notify($process, $next);
            }

            DB::commit();

            Log::info("✅ Processo #{$process->id} avançado para '{$next->step_name}' por {$user->email}");
            return $next;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erro em WorkflowService::advance para processo {$process->id}: " . $e->getMessage());
            throw new Exception("Erro ao avançar processo: " . $e->getMessage());
        }
    }

    /**
     * Rejeita o processo e registra motivo.
     *
     * @param Process $process
     * @param string $reason
     * @return void
     * @throws Exception
     */
    public function reject(Process $process, string $reason): void
    {
        $user = Auth::user();
        $current = $process->currentWorkflow;

        if (!$current) {
            throw new Exception('Etapa atual não definida.');
        }

        DB::beginTransaction();

        try {
            $execution = ProcessExecution::where('process_id', $process->id)
                ->latest()
                ->first();

            // Atualiza status geral
            $process->update(['status' => 'Recusado']);
            if ($execution) {
                $execution->update(['status' => 'Recusado']);
            }

            // Log da recusa
            ProcessLog::create([
                'process_id' => $process->id,
                'user_id' => $user->id,
                'action' => 'Recusado',
                'message' => "Recusado por {$user->name} na etapa {$current->step_name}: {$reason}",
            ]);

            // Atualiza step atual como rejeitado
            ProcessStep::where('process_id', $process->id)
                ->where('is_current', true)
                ->update([
                    'status' => 'Rejeitado',
                    'is_current' => false,
                    'completed_at' => now(),
                ]);

            // Cria registro explicando a recusa
            ProcessStep::create([
                'process_id' => $process->id,
                'workflow_id' => $current->id,
                'user_id' => $user->id,
                'status' => 'Rejeitado',
                'action' => 'Recusa de etapa',
                'comments' => $reason,
                'is_current' => false,
                'completed_at' => now(),
            ]);

            // Notifica criador / responsáveis
            $this->notifyRejection($process, $current, $reason);

            DB::commit();

            Log::warning("🚫 Processo #{$process->id} recusado por {$user->email}: {$reason}");
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erro em WorkflowService::reject para processo {$process->id}: " . $e->getMessage());
            throw new Exception("Erro ao recusar processo: " . $e->getMessage());
        }
    }

    /**
     * Volta o processo para a etapa anterior.
     *
     * @param Process $process
     * @return ProcessWorkflow
     * @throws Exception
     */
    public function rollback(Process $process): ProcessWorkflow
    {
        $user = Auth::user();
        $current = $process->currentWorkflow;

        if (!$current) {
            throw new Exception('Etapa atual não definida.');
        }

        $previous = ProcessWorkflow::where('process_type_id', $process->process_type_id)
            ->where('next_step', $current->step_name)
            ->first();

        if (!$previous) {
            throw new Exception('Não há etapa anterior para retornar.');
        }

        DB::beginTransaction();

        try {
            $execution = ProcessExecution::where('process_id', $process->id)
                ->latest()
                ->first();

            // marca step atual como retornado
            ProcessStep::where('process_id', $process->id)
                ->where('is_current', true)
                ->update([
                    'status' => 'Retornado',
                    'is_current' => false,
                    'completed_at' => now(),
                ]);

            // cria novo step com is_current = true apontando para anterior
            ProcessStep::create([
                'process_id' => $process->id,
                'workflow_id' => $previous->id,
                'user_id' => $user->id,
                'status' => 'Em Revisão',
                'action' => 'Rollback',
                'comments' => null,
                'is_current' => true,
            ]);

            $process->update([
                'current_workflow_id' => $previous->id,
                'status' => 'Em revisão',
            ]);

            if ($execution) {
                $execution->update([
                    'current_workflow_id' => $previous->id,
                    'status' => 'Em revisão',
                ]);
            }

            ProcessLog::create([
                'process_id' => $process->id,
                'user_id' => $user->id,
                'action' => 'Retorno de etapa',
                'message' => "{$user->name} retornou o processo para a etapa {$previous->step_name}.",
            ]);

            DB::commit();

            Log::info("↩️ Processo #{$process->id} retornado para '{$previous->step_name}' por {$user->email}");
            return $previous;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Erro em WorkflowService::rollback para processo {$process->id}: " . $e->getMessage());
            throw new Exception("Erro ao retornar processo: " . $e->getMessage());
        }
    }

    /**
     * Notifica o criador / responsável sobre a recusa.
     *
     * @param Process $process
     * @param ProcessWorkflow $workflow
     * @param string $reason
     * @return void
     */
    private function notifyRejection(Process $process, ProcessWorkflow $workflow, string $reason): void
    {
        try {
            $to = $process->creator->email ?? null;

            if ($to) {
                Mail::raw(
                    "O processo #{$process->id} foi recusado na etapa '{$workflow->step_name}'.\nMotivo: {$reason}",
                    function ($message) use ($to) {
                        $message->to($to)->subject('Processo Recusado');
                    }
                );
            }
        } catch (Exception $e) {
            Log::error("Erro ao enviar notificação de recusa (process {$process->id}): {$e->getMessage()}");
        }
    }

    /**
     * Envia notificação configurada para a etapa.
     *
     * @param Process $process
     * @param ProcessWorkflow $workflow
     * @return void
     */
    private function notify(Process $process, ProcessWorkflow $workflow): void
    {
        try {
            $notification = ProcessNotification::where('workflow_id', $workflow->id)
                ->where('is_active', true)
                ->first();

            if (!$notification) {
                return;
            }

            $to = array_filter(array_map('trim', explode(',', $notification->to ?? '')));
            $cc = array_filter(array_map('trim', explode(',', $notification->cc ?? '')));
            $subject = $notification->subject ?? "Processo atualizado: {$workflow->step_name}";

            if (!empty($to)) {
                Mail::raw(
                    "O processo #{$process->id} avançou para a etapa: {$workflow->step_name}.",
                    function ($message) use ($to, $cc, $subject) {
                        $message->to($to)->cc($cc)->subject($subject);
                    }
                );
            }
        } catch (Exception $e) {
            Log::error("Erro ao enviar notificação (process {$process->id}, workflow {$workflow->id}): {$e->getMessage()}");
        }
    }
}
