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
                'description' => 'Emissão, análise, recusa e correção de notas fiscais.'
            ],
            [
                'name' => 'Contas a Pagar',
                'description' => 'Baixas financeiras, pagamentos, comunicação com cliente.'
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

        // ❌ REMOVE SETOR ANTIGO (SE EXISTIR)
        Sector::where('name', 'Financeiro')->delete();

        echo "✅ Sectors atualizados corretamente.\n";
    }
}
