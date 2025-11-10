<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sector;
use App\Models\Level;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            // Comercial
            ['sector' => 'Comercial', 'name' => 'Gerente Comercial', 'authority_level' => 80],
            ['sector' => 'Comercial', 'name' => 'Funcionário Comercial', 'authority_level' => 10],

            // Logística
            ['sector' => 'Logística', 'name' => 'Gerente Logística', 'authority_level' => 70],
            ['sector' => 'Logística', 'name' => 'Funcionário Logística', 'authority_level' => 10],

            // Fiscal
            ['sector' => 'Fiscal', 'name' => 'Gerente Fiscal', 'authority_level' => 60],
            ['sector' => 'Fiscal', 'name' => 'Funcionário Fiscal', 'authority_level' => 10],

            // Financeiro
            ['sector' => 'Financeiro', 'name' => 'Gerente Financeiro', 'authority_level' => 50],
            ['sector' => 'Financeiro', 'name' => 'Funcionário Financeiro', 'authority_level' => 10],

            // Gerência de Produtos
            ['sector' => 'Gerência de Produtos', 'name' => 'Gerente de Produtos', 'authority_level' => 40],

            // Administração
            ['sector' => 'Administrativo', 'name' => 'Admin Master', 'authority_level' => 100],

            ['sector' => 'Administrativo', 'name' => 'Super Admin (TI)', 'authority_level' => 100],

        ];

        foreach ($levels as $data) {
            $sector = Sector::where('name', $data['sector'])->first();
            if ($sector) {
                Level::updateOrCreate(
                    ['name' => $data['name'], 'sector_id' => $sector->id],
                    ['authority_level' => $data['authority_level']]
                );
            }
        }
    }
}
