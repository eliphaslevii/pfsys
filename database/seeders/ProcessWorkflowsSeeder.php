<?php
use Illuminate\Database\Seeder;
use App\Models\{ProcessWorkflow, ProcessType, Sector, Level};

class ProcessWorkflowsSeeder extends Seeder
{
    public function run(): void
    {
        $recusaType = ProcessType::where('name', 'Recusa')->first();
        $devolucaoType = ProcessType::where('name', 'Devolução')->first();

        $fiscal = Sector::where('name', 'Fiscal')->first();
        $financeiro = Sector::where('name', 'Financeiro')->first();
        $comercial = Sector::where('name', 'Comercial')->first();

        $gerenteFiscal = Level::where('name', 'Gerente Fiscal')->first();
        $gerenteFinanceiro = Level::where('name', 'Gerente Financeiro')->first();
        $gerenteComercial = Level::where('name', 'Gerente Comercial')->first();

        $steps = [
            // 🔹 Fluxo Recusa
            [
                'process_type_id' => $recusaType->id,
                'step_name' => 'Análise Fiscal',
                'required_level_id' => $gerenteFiscal->id,
                'next_step' => null,
                'auto_notify' => true,
            ],
            [
                'process_type_id' => $recusaType->id,
                'step_name' => 'Aprovação Financeira',
                'required_level_id' => $gerenteFinanceiro->id,
                'next_step' => null,
                'auto_notify' => true,
            ],
            [
                'process_type_id' => $recusaType->id,
                'step_name' => 'Validação Comercial',
                'required_level_id' => $gerenteComercial->id,
                'next_step' => null,
                'auto_notify' => true,
            ],

            // 🔹 Fluxo Devolução
            [
                'process_type_id' => $devolucaoType->id,
                'step_name' => 'Análise Fiscal',
                'required_level_id' => $gerenteFiscal->id,
                'next_step' => null,
                'auto_notify' => true,
            ],
            [
                'process_type_id' => $devolucaoType->id,
                'step_name' => 'Aprovação Financeira',
                'required_level_id' => $gerenteFinanceiro->id,
                'next_step' => null,
                'auto_notify' => true,
            ],
            [
                'process_type_id' => $devolucaoType->id,
                'step_name' => 'Validação Comercial',
                'required_level_id' => $gerenteComercial->id,
                'next_step' => null,
                'auto_notify' => true,
            ],
        ];

        foreach ($steps as $step) {
            ProcessWorkflow::updateOrCreate(
                [
                    'process_type_id' => $step['process_type_id'],
                    'step_name' => $step['step_name'],
                ],
                $step
            );
        }

        $this->command->info('✅ Workflows atualizados para Recusa e Devolução.');
    }
}
