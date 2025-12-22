<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkflowStep;
use App\Models\Sector;

class WorkflowNotificationSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * =====================================================
         * BUSCAR SETORES
         * =====================================================
         */
        $comercial  = Sector::where('name', 'Comercial')->first();
        $logistica  = Sector::where('name', 'Logística')->first();
        $financeiro = Sector::where('name', 'Financeiro')->first();
        $fiscal     = Sector::where('name', 'Fiscal')->first();

        /**
         * =====================================================
         * MAPA STEP → SETOR
         * =====================================================
         */
        $map = [
            // Comercial
            'Comercial'               => $comercial,
            'Aprovação Comercial'     => $comercial,

            // Fiscal
            'Fiscal'                  => $fiscal,
            'Aprovação Fiscal'        => $fiscal,
            'Validação Fiscal'        => $fiscal,

            // Logística
            'Logística'               => $logistica,

            // Financeiro
            'Financeiro'              => $financeiro,
            'Contas a pagar'          => $financeiro,
            'Contas a receber'        => $financeiro,
        ];

        foreach ($map as $stepName => $sector) {
            if (!$sector) {
                continue;
            }

            WorkflowStep::where('name', $stepName)
                ->update(['sector_id' => $sector->id]);
        }
    }
}
