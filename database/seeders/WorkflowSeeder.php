<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowReason;
use App\Models\ProcessWorkflow;

class WorkflowSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            'Sucateamento' => [
                'motivos' => [
                    'Material Descartado',
                    'Devolução + sucateamento'
                ],
                'steps' => [
                    'Comercial',
                    'Financeiro',
                    'Logística',
                    'Financeiro (Pós-Logística)',
                    'Logística (Refaturamento)',
                    'Financeiro 2'
                ]
            ],

            'Ajuste / Baixa Financeira' => [
                'motivos' => [
                    'Retorno de Material para a PFERD',
                    'Somente ajuste de estoque',
                    'Baixa financeira'
                ],
                'steps' => [
                    'Comercial',
                    'Financeiro',
                    'Logística',
                    'Financeiro 2'
                ]
            ],

            'Transporte PFERD' => [
                'motivos' => [
                    'Retorno de Material para a PFERD + Transporte PFERD'
                ],
                'steps' => [
                    'Comercial',
                    'Logística (Agendar Coleta)',
                    'Logística (Aguardando Recebimento)',
                    'Financeiro',
                    'Logística',
                    'Financeiro 2'
                ]
            ],

            'Transporte Cliente' => [
                'motivos' => [
                    'Retorno de Material para a PFERD + Transporte CLIENTE'
                ],
                'steps' => [
                    'Financeiro',
                    'Logística (Aguardando Recebimento)',
                    'Financeiro',
                    'Logística',
                    'Financeiro 2'
                ]
            ],

            'Padrão' => [
                'motivos' => [],
                'steps' => [
                    'Comercial',
                    'Financeiro',
                    'Logística',
                    'Comercial (Refaturamento)',
                    'Logística (Refaturado)',
                    'Financeiro 2'
                ]
            ],
        ];

        foreach ($templates as $name => $data) {
            $template = WorkflowTemplate::create(['name' => $name]);

            foreach ($data['motivos'] as $motivo) {
                WorkflowReason::create([
                    'name' => $motivo,
                    'workflow_template_id' => $template->id
                ]);
            }

            foreach ($data['steps'] as $i => $step) {
                ProcessWorkflow::create([
                    'workflow_template_id' => $template->id,
                    'name' => $step,
                    'step_order' => $i + 1
                ]);
            }
        }
    }
}
