<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcessWorkflow;
use App\Models\ProcessType;
use App\Models\Level;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $devolucao = ProcessType::where('name', 'Devolução')->first();
        $recusa = ProcessType::where('name', 'Recusa')->first();

        if (!$devolucao || !$recusa) {
            throw new \Exception("Os tipos de processo 'Devolução' e 'Recusa' precisam existir antes de rodar este seeder.");
        }

        $levels = Level::pluck('id', 'name'); // pega os IDs por nome

        $workflows = [
            // 🔹 Etapas da Devolução
            [
                'process_type_id' => $devolucao->id,
                'step_name' => 'Comercial (Validação)',
                'required_level_id' => $levels['Comercial'] ?? null,
                'next_step' => 'Financeiro (Análise)',
                'auto_notify' => true,
            ],
            [
                'process_type_id' => $devolucao->id,
                'step_name' => 'Financeiro (Análise)',
                'required_level_id' => $levels['Financeiro'] ?? null,
                'next_step' => 'Fiscal (Validação)',
                'auto_notify' => true,
            ],
            [
                'process_type_id' => $devolucao->id,
                'step_name' => 'Fiscal (Validação)',
                'required_level_id' => $levels['Fiscal'] ?? null,
                'next_step' => null, // fim do processo
                'auto_notify' => true,
            ],

            // 🔹 Etapas da Recusa
            [
                'process_type_id' => $recusa->id,
                'step_name' => 'Financeiro (Pré-Análise)',
                'required_level_id' => $levels['Financeiro'] ?? null,
                'next_step' => 'Comercial (Verificação)',
                'auto_notify' => true,
            ],
            [
                'process_type_id' => $recusa->id,
                'step_name' => 'Comercial (Verificação)',
                'required_level_id' => $levels['Comercial'] ?? null,
                'next_step' => 'Fiscal (Conclusão)',
                'auto_notify' => true,
            ],
            [
                'process_type_id' => $recusa->id,
                'step_name' => 'Fiscal (Conclusão)',
                'required_level_id' => $levels['Fiscal'] ?? null,
                'next_step' => null,
                'auto_notify' => true,
            ],
        ];

        foreach ($workflows as $wf) {
            ProcessWorkflow::firstOrCreate(
                ['process_type_id' => $wf['process_type_id'], 'step_name' => $wf['step_name']],
                $wf
            );
        }

        echo "✅ Workflows de Devolução e Recusa criados com sucesso.\n";
    }
}
