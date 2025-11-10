<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1️⃣ Estruturas base
        $this->call([
            SectorSeeder::class,
            PermissionSeeder::class,
            LevelSeeder::class,
        ]);

        // 2️⃣ Usuários padrão
        $this->call([
            UserSeeder::class,
            TestUsersSeeder::class, // opcional — remova se não existir
        ]);

        // 3️⃣ Processos e workflows
        $this->call([
            ProcessTypeSeeder::class,
            WorkflowSeeder::class,
        ]);
    }
}
