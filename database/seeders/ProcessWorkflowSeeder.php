<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcessType;
use App\Models\ProcessWorkflow;
use App\Models\ProcessNotification;

class ProcessWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        // 🔹 Garante o tipo de processo base
        $processType = ProcessType::firstOrCreate(
            ['name' => 'Devolução'],
            ['description' => 'Fluxo padrão de devolução/recusa de mercadoria.']
        );

        /**
         * 🔹 Fluxo base (equivalente ao antigo sistema)
         * Comercial → Financeiro → Logística → Comercial (Refaturamento) → Financeiro 2 → Finalizado
         */
        // 🔹 Cria níveis padrão (se não existirem)
        // 🔹 Garante um setor padrão (para associar os níveis)
        $defaultSector = \App\Models\Sector::firstOrCreate(
            ['name' => 'Geral'],
            [] // caso sua tabela tenha outras colunas obrigatórias, podemos incluir aqui
        );

        // 🔹 Cria níveis padrão (se não existirem)
        $levels = [
            2 => 'Financeiro',
            7 => 'Logística',
            8 => 'Comercial',
            10 => 'Financeiro 2',
        ];

        foreach ($levels as $id => $name) {
            \App\Models\Level::firstOrCreate(
                ['id' => $id],
                [
                    'name' => $name,
                    'sector_id' => $defaultSector->id, // ✅ ligação obrigatória
                ]
            );
        }

        $steps = [
            ['step_name' => 'Comercial', 'required_level_id' => 8, 'next_step' => 'Financeiro', 'auto_notify' => true],
            ['step_name' => 'Financeiro', 'required_level_id' => 2, 'next_step' => 'Logística', 'auto_notify' => true],
            ['step_name' => 'Logística', 'required_level_id' => 7, 'next_step' => 'Comercial (Refaturamento)', 'auto_notify' => true],
            ['step_name' => 'Comercial (Refaturamento)', 'required_level_id' => 8, 'next_step' => 'Financeiro 2', 'auto_notify' => true],
            ['step_name' => 'Financeiro 2', 'required_level_id' => 10, 'next_step' => 'Finalizado', 'auto_notify' => true],
            ['step_name' => 'Finalizado', 'required_level_id' => null, 'next_step' => null, 'auto_notify' => false],
        ];

        foreach ($steps as $step) {
            $workflow = ProcessWorkflow::firstOrCreate(
                [
                    'process_type_id' => $processType->id,
                    'step_name' => $step['step_name'],
                ],
                [
                    'required_level_id' => $step['required_level_id'],
                    'next_step' => $step['next_step'],
                    'auto_notify' => $step['auto_notify'],
                ]
            );

            // 🔹 Define notificações básicas por etapa
            $notificationData = match ($step['step_name']) {
                'Comercial' => [
                    'to' => 'priscila.fabris@pferd.com',
                    'cc' => null,
                ],
                'Financeiro' => [
                    'to' => 'vitor.hugo@pferd.com,luiz.felipe@pferd.com',
                    'cc' => null,
                ],
                'Logística' => [
                    'to' => 'leandro.castro@pferd.com,airton.fialla@pferd.com',
                    'cc' => null,
                ],
                'Comercial (Refaturamento)' => [
                    'to' => 'priscila.fabris@pferd.com',
                    'cc' => null,
                ],
                'Financeiro 2' => [
                    'to' => 'simone.quadros@pferd.com',
                    'cc' => 'priscila.fabris@pferd.com',
                ],
                default => ['to' => null, 'cc' => null],
            };

            ProcessNotification::updateOrCreate(
                [
                    'process_type_id' => $processType->id,
                    'workflow_id' => $workflow->id,
                    'step_name' => $step['step_name'],
                ],
                [
                    'to' => $notificationData['to'],
                    'cc' => $notificationData['cc'],
                    'subject' => "Processo de {$processType->name} - Etapa {$step['step_name']}",
                    'template_view' => 'emails.returnProcessNotification',
                    'is_active' => true,
                ]
            );
        }

        $this->command->info("Fluxo de processo '{$processType->name}' criado com sucesso!");
    }
}
