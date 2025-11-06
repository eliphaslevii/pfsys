<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Level;
use App\Models\Sector;
use Illuminate\Support\Facades\Hash;
use Exception;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ATENÇÃO: Sincronização dos nomes com os Seeders de dependência

        // Busca Level: 'Super Admin (TI)' (Nome criado no LevelSeeder)
        $superAdminLevel = Level::where('name', 'Super Admin (TI)')->first();
        
        // Busca Sector: 'Administrativo' (Onde o Super Admin está alocado)
        $adminSector = Sector::where('name', 'Administrativo')->first(); 

        // CRÍTICO: Lançamento de exceção se a busca falhar, indicando a falha de dependência
        if (!$adminSector) {
            throw new Exception("Falha de Dependência: O Sector 'Administrativo' não foi encontrado. Verifique o SectorSeeder.");
        }
        if (!$superAdminLevel) {
            throw new Exception("Falha de Dependência: O Level 'Super Admin (TI)' não foi encontrado. Verifique o LevelSeeder.");
        }

        // 2. Criar ou atualizar Usuário Super Admin
        User::updateOrCreate(
            ['email' => 'luiz.cesar@pferd.com'],
            [
                'name' => 'Luiz Cesar (Admin TI)',
                'password' => Hash::make('vbox@bsys'), 
                'level_id' => $superAdminLevel->id,
                'sector_id' => $adminSector->id,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Usuário Super Admin TI criado/atualizado com sucesso.');
    }
}