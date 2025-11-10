<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Level;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'process.view', 'description' => 'Visualizar processos.'],
            ['name' => 'process.create', 'description' => 'Criar novo processo.'],
            ['name' => 'process.approve', 'description' => 'Aprovar etapa do processo.'],
            ['name' => 'process.reject', 'description' => 'Recusar processo.'],
            ['name' => 'process.delete', 'description' => 'Excluir processo.'],
            ['name' => 'process.manage_config', 'description' => 'Gerenciar fluxos e notificações.'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['name' => $perm['name']], $perm);
        }

        // 🔗 vincula permissões aos cargos
        $managers = Level::where('name', 'like', '%Gerente%')->get();
        $analysts = Level::where('name', 'like', '%Analista%')->get();

        $allPerms = Permission::pluck('id', 'name');

        foreach ($managers as $level) {
            $level->permissions()->sync($allPerms->values()); // full access
        }

        foreach ($analysts as $level) {
            $level->permissions()->sync([
                $allPerms['process.view'],
                $allPerms['process.create'],
            ]);
        }
    }
}
