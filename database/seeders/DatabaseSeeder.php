<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call(SectorSeeder::class);
        // 2. Permissões (Permissions)
        $this->call(PermissionSeeder::class);
        // 3. Níveis (Levels) - Depende de Setores e Permissões
        $this->call(LevelSeeder::class);

        // Opcional: Adicionar Usuário de Teste (Super Admin)
        $this->call(UserSeeder::class);
        $this->call([
            SectorsSeeder::class,
            LevelsSeeder::class,
            PermissionsSeeder::class,
            ProcessWorkflowsSeeder::class,
        ]);
    }
}
