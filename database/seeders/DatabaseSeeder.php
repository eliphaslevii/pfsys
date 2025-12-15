<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ Estruturas base
        $this->call([
            SectorSeeder::class,        // setores primeiro
            LevelSeeder::class,         // níveis dependem de setores
            PermissionSeeder::class,    // permissões são atribuídas aos níveis
        ]);

        // 2️⃣ Usuários padrão
        $this->call([
            TestUsersSeeder::class,     // cria usuários com levels definidos acima
        ]);

        // 3️⃣ Processos e workflows
        $this->call([
            ProcessTypeSeeder::class,
            WorkflowSeeder::class,
        ]);
    }
}
