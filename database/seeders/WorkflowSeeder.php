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
        /* =====================
         * SETORES / NÍVEIS
         * ===================== */
        $sectors = [
            'Comercial'  => Sector::firstWhere('name', 'Comercial'),
            'Financeiro' => Sector::firstWhere('name', 'Financeiro'),
            'Logística'  => Sector::firstWhere('name', 'Logística'),
        ];

        $levels = [
            8 => Level::where('level', 8)->first(),
            7 => Level::where('level', 7)->first(),
            2 => Level::where('level', 2)->first(),
        ];

        /* =====================
         * TIPOS DE PROCESSO
         * ===================== */
        $recusa = ProcessType::firstOrCreate(['name' => 'Recusa']);
        $devolucao = ProcessType::firstOrCreate(['name' => 'Devolução']);

        $processTypes = [$recusa, $devolucao];

        /* =====================
         * DEFINIÇÃO DOS FLUXOS
         * ===================== */
        $flows = [

            /* 🔴 SUCATEAMENTO */
            [
                'name' => 'Fluxo Sucateamento',
                'motivos' => [
                    'Material Descartado',
                    'Devolução + sucateamento',
                ],
                'steps' => [
                    ['Comercial', 8],
                    ['Financeiro', 2],
                    ['Logística', 7],
                    ['Financeiro (Pós-Logística)', 2],
                    ['Logística (Refaturamento)', 7],
                    ['Financeiro 2', 2],
                    ['Finalizado', null],
                ],
            ],

            /* 🟡 SIMPLES */
            [
                'name' => 'Fluxo Simples',
                'motivos' => [
                    'Somente ajuste de estoque',
                    'Baixa financeira',
                    'Somente Emissão de nova nota fiscal',
                ],
                'steps' => [
                    ['Comercial', 8],
                    ['Financeiro', 2],
                    ['Logística', 7],
                    ['Financeiro 2', 2],
                    ['Finalizado', null],
                ],
            ],

            /* 🔵 TRANSPORTE PFERD */
            [
                'name' => 'Fluxo Transporte PFERD',
                'motivos' => [
                    'Retorno de Material para a PFERD + Transporte PFERD',
                ],
                'steps' => [
                    ['Comercial', 8],
                    ['Logística (Agendar Coleta)', 7],
                    ['Logística (Aguardando Recebimento)', 7],
                    ['Financeiro', 2],
                    ['Logística', 7],
                    ['Financeiro 2', 2],
                    ['Finalizado', null],
                ],
            ],

            /* 🟣 TRANSPORTE CLIENTE */
            [
                'name' => 'Fluxo Transporte CLIENTE',
                'motivos' => [
                    'Retorno de Material para a PFERD + Transporte CLIENTE',
                ],
                'steps' => [
                    ['Comercial', 8],
                    ['Logística (Aguardando Recebimento)', 7],
                    ['Financeiro', 2],
                    ['Logística', 7],
                    ['Financeiro 2', 2],
                    ['Finalizado', null],
                ],
            ],

            /* ⚪ PADRÃO */
            [
                'name' => 'Fluxo Padrão',
                'motivos' => [
                    'Emissão de nova nota fiscal + reentrega',
                    'Retorno do material para a PFERD',
                    'Retorno de material para PFERD + Envio de nova remessa',
                ],
                'steps' => [
                    ['Comercial', 8],
                    ['Financeiro', 2],
                    ['Logística', 7],
                    ['Comercial (Refaturamento)', 8],
                    ['Logística (Refaturado)', 7],
                    ['Financeiro 2', 2],
                    ['Finalizado', null],
                ],
            ],
        ];

        /* =====================
         * CRIAÇÃO EFETIVA
         * ===================== */
        foreach ($flows as $flow) {

            foreach ($processTypes as $type) {

                $template = WorkflowTemplate::firstOrCreate([
                    'name' => $flow['name'].' - '.$type->name,
                    'process_type_id' => $type->id,
                ], [
                    'is_active' => true,
                ]);

                foreach ($flow['motivos'] as $motivo) {
                    WorkflowReason::firstOrCreate([
                        'name' => $motivo,
                        'workflow_template_id' => $template->id,
                    ]);
                }

                foreach ($flow['steps'] as $order => [$stepName, $requiredLevel]) {

                    $sectorKey = strtok($stepName, ' ');

                    WorkflowStep::firstOrCreate([
                        'workflow_template_id' => $template->id,
                        'order' => $order + 1,
                    ], [
                        'name' => $stepName,
                        'sector_id' => $sectors[$sectorKey]->id ?? null,
                        'required_level_id' => $requiredLevel
                            ? $levels[$requiredLevel]?->id
                            : null,
                        'auto_notify' => true,
                    ]);
                }
            }
        }
    }
}
