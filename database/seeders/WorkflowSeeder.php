<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcessType;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowStep;
use App\Models\WorkflowReason;
use App\Models\Sector;
use Illuminate\Support\Facades\DB;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $recusaType = ProcessType::where('name', 'Recusa')->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | WORKFLOW TEMPLATES
            |--------------------------------------------------------------------------
            */
            $templates = [

                'Fluxo Especial - Sucateamento' => [
                    'motivos' => [
                        'Material Descartado',
                        'Devolução + sucateamento',
                    ],
                    'steps' => [
                        'Comercial',
                        'Fiscal',
                        'Logística',
                        'Fiscal (Pós-Logística)',
                        'Logística (Refaturamento)',
                        'Contas a Pagar',
                        'Finalizado',
                    ],
                ],

                'Fluxo Especial - Ajuste / Baixa' => [
                    'motivos' => [
                        'Retorno de Material para a PFERD',
                        'Somente ajuste de estoque',
                        'Baixa financeira',
                    ],
                    'steps' => [
                        'Comercial',
                        'Fiscal',
                        'Logística',
                        'Contas a Pagar',
                        'Finalizado',
                    ],
                ],

                'Fluxo Especial - Transporte PFERD' => [
                    'motivos' => [
                        'Retorno de Material para a PFERD + Transporte PFERD',
                    ],
                    'steps' => [
                        'Comercial',
                        'Logística (Agendar Coleta)',
                        'Logística (Aguardando Recebimento)',
                        'Fiscal',
                        'Logística',
                        'Contas a Pagar',
                        'Finalizado',
                    ],
                ],

                'Fluxo Especial - Transporte Cliente' => [
                    'motivos' => [
                        'Retorno de Material para a PFERD + Transporte CLIENTE',
                    ],
                    'steps' => [
                        'Comercial',
                        'Logística (Aguardando Recebimento)',
                        'Fiscal',
                        'Logística',
                        'Contas a Pagar',
                        'Finalizado',
                    ],
                ],

                'Fluxo Padrão - Recusa' => [
                    'motivos' => [
                        'Emissão de nova nota fiscal + reentrega',
                        'Somente Emissão de nova nota fiscal',
                    ],
                    'steps' => [
                        'Comercial',
                        'Fiscal',
                        'Logística',
                        'Comercial (Refaturamento)',
                        'Logística (Refaturado)',
                        'Contas a Pagar',
                        'Finalizado',
                    ],
                ],
            ];

            foreach ($templates as $templateName => $config) {

                $template = WorkflowTemplate::updateOrCreate(
                    [
                        'name' => $templateName,
                        'process_type_id' => $recusaType->id,
                    ],
                    [
                        'is_active' => true,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | STEPS
                |--------------------------------------------------------------------------
                */
                $order = 1;

                foreach ($config['steps'] as $stepName) {

                    $sectorName =
                        str_contains($stepName, 'Fiscal')
                            ? 'Fiscal'
                            : (str_contains($stepName, 'Logística')
                                ? 'Logística'
                                : (str_contains($stepName, 'Comercial')
                                    ? 'Comercial'
                                    : (str_contains($stepName, 'Contas')
                                        ? 'Contas a Pagar'
                                        : null)));

                    $sector = $sectorName
                        ? Sector::where('name', $sectorName)->first()
                        : null;

                    WorkflowStep::updateOrCreate(
                        [
                            'workflow_template_id' => $template->id,
                            'name' => $stepName,
                        ],
                        [
                            'order' => $order++,
                            'sector_id' => $sector?->id,
                            'required_level_id' => null, // nível legado removido
                        ]
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | MOTIVOS
                |--------------------------------------------------------------------------
                */
                foreach ($config['motivos'] as $motivoName) {
                    WorkflowReason::updateOrCreate(
                        [
                            'workflow_template_id' => $template->id,
                            'name' => $motivoName,
                        ],
                        [
                            'is_active' => true,
                        ]
                    );
                }
            }
        });
    }
}
