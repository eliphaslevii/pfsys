<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sector;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usando firstOrCreate para evitar erro de violação de chave única
        Sector::firstOrCreate(
            ['name' => 'Administrativo'],
            ['description' => 'Setor de gestão interna, financeiro e recursos humanos.', 'is_active' => true]
        );

        Sector::firstOrCreate(
            ['name' => 'Comercial'],
            ['description' => 'Setor responsável pelas vendas e relacionamento com clientes.', 'is_active' => true]
        );

        Sector::firstOrCreate(
            ['name' => 'Desenvolvimento'],
            ['description' => 'Setor responsável pelo desenvolvimento e manutenção de sistemas.', 'is_active' => true]
        );
    }
}