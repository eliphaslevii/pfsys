<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sector;
use App\Models\Level;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        // GARANTIR setores criados
        $sectorComercial  = Sector::firstOrCreate(['name' => 'Comercial']);
        $sectorLogistica  = Sector::firstOrCreate(['name' => 'Logística']);
        $sectorFinanceiro = Sector::firstOrCreate(['name' => 'Financeiro']);
        $sectorFiscal     = Sector::firstOrCreate(['name' => 'Fiscal']);
        $sectorAdmin      = Sector::firstOrCreate(['name' => 'Administrativo']);

        // Níveis por setor — PADRONIZADOS
        $levels = [

            // 🔵 COMERCIAL
            [
                'sector_id' => $sectorComercial->id,
                'name' => 'Gestor Comercial',
                'authority_level' => 80,
            ],
            [
                'sector_id' => $sectorComercial->id,
                'name' => 'Analista Comercial',
                'authority_level' => 10,
            ],

            // 🟣 LOGÍSTICA
            [
                'sector_id' => $sectorLogistica->id,
                'name' => 'Gestor Logística',
                'authority_level' => 70,
            ],
            [
                'sector_id' => $sectorLogistica->id,
                'name' => 'Analista Logística',
                'authority_level' => 10,
            ],

            // 🟢 FINANCEIRO
            [
                'sector_id' => $sectorFinanceiro->id,
                'name' => 'Gestor Financeiro',
                'authority_level' => 50,
            ],
            [
                'sector_id' => $sectorFinanceiro->id,
                'name' => 'Analista Financeiro',
                'authority_level' => 10,
            ],

            // 🟡 FISCAL
            [
                'sector_id' => $sectorFiscal->id,
                'name' => 'Analista Fiscal',
                'authority_level' => 40,
            ],

            // 🔴 ADMIN
            [
                'sector_id' => $sectorAdmin->id,
                'name' => 'Super Admin',
                'authority_level' => 999,
            ],
        ];

        foreach ($levels as $data) {
            Level::updateOrCreate(
                ['name' => $data['name'], 'sector_id' => $data['sector_id']],
                ['authority_level' => $data['authority_level']]
            );
        }

        echo "✅ Levels atualizados com sucesso.\n";
    }
}
