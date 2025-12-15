<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcessType;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowReason;
use App\Models\WorkflowStep;
use App\Models\Sector;
use App\Models\Level;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        // =====================
        // 1) Setores e Níveis
        // =====================

        $sectorComercial  = Sector::firstWhere('name', 'Comercial');
        $sectorFinanceiro = Sector::firstWhere('name', 'Financeiro');
        $sectorLogistica  = Sector::firstWhere('name', 'Logística');

        $levelComercial  = Level::firstWhere('name', 'Funcionário Comercial');
        $levelFinanceiro = Level::firstWhere('name', 'Funcionário Financeiro');
        $levelLogistica  = Level::firstWhere('name', 'Funcionário Logística');

        // =====================
        // 2) Criar Process Types
        // =====================

        $recusa = ProcessType::firstOrCreate(
            ['name' => 'Recusa'],
            ['description' => 'Processo de recusa de mercadoria / NF']
        );

        $devolucao = ProcessType::firstOrCreate(
            ['name' => 'Devolução'],
            ['description' => 'Processo de devolução de mercadoria / NF']
        );

        // =====================
        // 3) Motivos (tudo duplicado)
        // =====================

        $motivos = [
            'Devolução total',
            'Devolução parcial',
            'Material Descartado',
            'Devolução + sucateamento',
            'Retorno de Material para a PFERD',
            'Somente ajuste de estoque',
            'Baixa financeira',
            'Retorno de Material para a PFERD + Transporte PFERD',
            'Retorno de Material para a PFERD + Transporte CLIENTE',
            'Produto errado',
            'Faturamento sem autorização',
            'Duplicidade',
            'Produto avariado',
            'Erro de NF',
            'Outros',
        ];

        $processTypes = [$recusa, $devolucao];

        foreach ($processTypes as $type) {

            // Template genérico
            $template = WorkflowTemplate::firstOrCreate(
                ['name' => 'Fluxo Genérico - '.$type->name, 'process_type_id' => $type->id],
                ['is_active' => true]
            );

            // Motivos duplicados para cada tipo
            foreach ($motivos as $motivo) {
                WorkflowReason::firstOrCreate([
                    'name' => $motivo,
                    'workflow_template_id' => $template->id
                ]);
            }

            // Passos básicos
            $steps = [
                ['Comercial',  $sectorComercial,  $levelComercial],
                ['Financeiro', $sectorFinanceiro, $levelFinanceiro],
                ['Logística',  $sectorLogistica,  $levelLogistica],
                ['Finalizado', null, null],
            ];

            foreach ($steps as $index => $step) {
                WorkflowStep::firstOrCreate(
                    [
                        'workflow_template_id' => $template->id,
                        'order' => $index + 1
                    ],
                    [
                        'name' => $step[0],
                        'sector_id' => $step[1]->id ?? null,
                        'required_level_id' => $step[2]->id ?? null,
                        'auto_notify' => true
                    ]
                );
            }
        }
    }
}
