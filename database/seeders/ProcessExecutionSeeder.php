<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Process;
use App\Models\ProcessType;
use App\Models\ProcessWorkflow;
use App\Models\ProcessExecution;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProcessExecutionSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 🔹 Usuário padrão
            $user = User::first();

            if (!$user) {
                throw new \Exception("Nenhum usuário encontrado. Execute o UserSeeder primeiro.");
            }

            // 🔹 Garante que os tipos básicos existam
            $recusa = ProcessType::firstOrCreate(
                ['name' => 'Recusa'],
                ['description' => 'Processos de recusa de mercadoria']
            );

            $devolucao = ProcessType::firstOrCreate(
                ['name' => 'Devolução'],
                ['description' => 'Processos de devolução de mercadoria']
            );

            // 🔹 Workflows de exemplo
            $steps = [
                ['process_type_id' => $recusa->id, 'step_name' => 'Comercial', 'next_step' => 'Financeiro'],
                ['process_type_id' => $recusa->id, 'step_name' => 'Financeiro', 'next_step' => 'Logística'],
                ['process_type_id' => $recusa->id, 'step_name' => 'Logística', 'next_step' => 'Finalizado'],

                ['process_type_id' => $devolucao->id, 'step_name' => 'Comercial', 'next_step' => 'Financeiro'],
                ['process_type_id' => $devolucao->id, 'step_name' => 'Financeiro', 'next_step' => 'Logística'],
                ['process_type_id' => $devolucao->id, 'step_name' => 'Logística', 'next_step' => 'Financeiro 2'],
                ['process_type_id' => $devolucao->id, 'step_name' => 'Financeiro 2', 'next_step' => 'Finalizado'],
            ];

            foreach ($steps as $step) {
                ProcessWorkflow::firstOrCreate([
                    'process_type_id' => $step['process_type_id'],
                    'step_name' => $step['step_name'],
                ], [
                    'next_step' => $step['next_step'],
                    'auto_notify' => true,
                ]);
            }

            // 🔹 Cria processos de teste
            $recusaProcess = Process::create([
                'process_type_id' => $recusa->id,
                'created_by' => $user->id,
                'status' => 'Aberto',
                'cliente_nome' => 'Cliente Teste Recusa',
                'cliente_cnpj' => '00.000.000/0001-00',
                'nf_saida' => 'NF123',
                'observacoes' => 'Teste de recusa automática',
            ]);

            $devolucaoProcess = Process::create([
                'process_type_id' => $devolucao->id,
                'created_by' => $user->id,
                'status' => 'Aberto',
                'cliente_nome' => 'Cliente Teste Devolução',
                'cliente_cnpj' => '11.111.111/0001-11',
                'nf_saida' => 'NF456',
                'observacoes' => 'Teste de devolução automática',
            ]);

            // 🔹 Busca os primeiros workflows de cada tipo
            $firstRecusaStep = ProcessWorkflow::where('process_type_id', $recusa->id)->first();
            $firstDevolucaoStep = ProcessWorkflow::where('process_type_id', $devolucao->id)->first();

            // 🔹 Cria entradas de execução
            ProcessExecution::create([
                'process_id' => $recusaProcess->id,
                'current_workflow_id' => $firstRecusaStep->id,
                'assigned_to' => $user->id,
                'status' => 'Em Andamento',
                'observations' => 'Processo de recusa em fase inicial.',
            ]);

            ProcessExecution::create([
                'process_id' => $devolucaoProcess->id,
                'current_workflow_id' => $firstDevolucaoStep->id,
                'assigned_to' => $user->id,
                'status' => 'Em Andamento',
                'observations' => 'Processo de devolução em fase inicial.',
            ]);

            DB::commit();

            echo "✅ ProcessExecutionSeeder executado com sucesso.\n";

        } catch (\Exception $e) {
            DB::rollBack();
            echo "❌ Erro ao rodar ProcessExecutionSeeder: " . $e->getMessage() . "\n";
        }
    }
}
