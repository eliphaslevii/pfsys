<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sector;
use App\Models\Level;
use App\Models\Permission;
use Exception;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ATENÇÃO: Busca pelos nomes criados no SectorSeeder
        $adminSector = Sector::where('name', 'Administrativo')->first();
        $commercialSector = Sector::where('name', 'Comercial')->first();
        
        if (!$adminSector || !$commercialSector) {
            throw new Exception("Setores base (Administrativo ou Comercial) não encontrados. Verifique o SectorSeeder.");
        }

        $allPermissions = Permission::pluck('id')->toArray();
        
        $commercialPermissions = Permission::whereIn('name', ['commercial.view_pipeline', 'commercial.create_opportunity'])->pluck('id')->toArray();
        $viewAllPermission = Permission::where('name', 'user.view_all')->first();

        // --- 1. Nível de SUPER ADMIN (Autoridade Máxima) ---
        $superAdminLevel = Level::firstOrCreate(
            [
                'name' => 'Super Admin (TI)', // Nome de criação crucial para o UserSeeder
                'sector_id' => $adminSector->id,
            ],
            [
                'authority_level' => 100, // Máximo
            ]
        );
        $superAdminLevel->permissions()->sync($allPermissions);

        // --- 2. Nível Gerencial ---
        $managerLevel = Level::firstOrCreate(
            [
                'name' => 'Gerente Comercial',
                'sector_id' => $commercialSector->id,
            ],
            [
                'authority_level' => 70, 
            ]
        );

        $managerPermissions = $commercialPermissions;
        if ($viewAllPermission) {
            $managerPermissions = array_merge($managerPermissions, [$viewAllPermission->id]);
        }
        $managerLevel->permissions()->sync($managerPermissions);

        // --- 3. Nível Operacional ---
        $juniorLevel = Level::firstOrCreate(
            [
                'name' => 'Funcionário Comercial Júnior',
                'sector_id' => $commercialSector->id,
            ],
            [
                'authority_level' => 10,
            ]
        );
        $juniorLevel->permissions()->sync($commercialPermissions);
    }
}