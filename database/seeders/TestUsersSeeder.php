<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Level;
use App\Models\Sector;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // 🔹 Garante um setor padrão
        $defaultSector = Sector::firstOrCreate(
            ['name' => 'Geral'],
            ['description' => 'Setor padrão para usuários de teste']
        );

        // 🔹 Garante os níveis existentes
        $levels = [
            1 => 'Super Admin',
            2 => 'Financeiro',
            7 => 'Logística',
            8 => 'Comercial',
            9 => 'Administrativo',
        ];

        foreach ($levels as $id => $name) {
            Level::firstOrCreate(
                ['id' => $id],
                [
                    'name' => $name,
                    'sector_id' => $defaultSector->id,
                ]
            );
        }

        // 🔹 Cria os usuários de teste
        $users = [
            ['name' => 'Super Admin', 'email' => 'luiz.cesar@pferd.com', 'password' => Hash::make('Jcr1st0#'), 'level_id' => 1, 'sector_id' => $defaultSector->id],
            ['name' => 'Comercial', 'email' => 'comercial@bsys.local', 'password' => Hash::make('123456'), 'level_id' => 8, 'sector_id' => $defaultSector->id],
            ['name' => 'Financeiro', 'email' => 'financeiro@bsys.local', 'password' => Hash::make('123456'), 'level_id' => 2, 'sector_id' => $defaultSector->id],
            ['name' => 'Logística', 'email' => 'logistica@bsys.local', 'password' => Hash::make('123456'), 'level_id' => 7, 'sector_id' => $defaultSector->id],
            ['name' => 'Administrativo', 'email' => 'admin@bsys.local', 'password' => Hash::make('123456'), 'level_id' => 9, 'sector_id' => $defaultSector->id],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['email' => $data['email']], $data);
        }
    }
}
