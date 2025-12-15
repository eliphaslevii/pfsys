<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sector;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        $sectors = [
            [
                'name' => 'Comercial',
                'description' => 'Setor responsável por abrir, validar e acompanhar processos.'
            ],
            [
                'name' => 'Logística',
                'description' => 'Transporte, recebimento e devoluções.'
            ],
            [
                'name' => 'Fiscal',
                'description' => 'Emissão, análise e correção de notas fiscais.'
            ],
            [
                'name' => 'Financeiro',
                'description' => 'Faturamento, crédito, cobrança e baixas financeiras.'
            ],
            [
                'name' => 'Administrativo',
                'description' => 'Configurações gerais e administração do sistema.'
            ],
        ];

        foreach ($sectors as $sector) {
            Sector::updateOrCreate(
                ['name' => $sector['name']],
                $sector
            );
        }

        echo "✅ Sectors atualizados corretamente.\n";
    }
}
