<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sector;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        $sectors = [
            ['name' => 'Comercial', 'description' => 'Responsável por abertura e controle de processos comerciais.'],
            ['name' => 'Logística', 'description' => 'Gerencia transporte, recebimento e devoluções.'],
            ['name' => 'Fiscal', 'description' => 'Responsável pela parte fiscal e emissão de notas.'],
            ['name' => 'Financeiro', 'description' => 'Contas a pagar e faturamento.'],
            ['name' => 'Gerência de Produtos', 'description' => 'Controle de produtos e planejamento.'],
            ['name' => 'Administração', 'description' => 'Setor administrativo com acesso total.'],
        ];

        foreach ($sectors as $sector) {
            Sector::updateOrCreate(['name' => $sector['name']], $sector);
        }
    }
}
