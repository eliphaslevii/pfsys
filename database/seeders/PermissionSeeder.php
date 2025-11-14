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
            // 🧩 Processos gerais
            ['name' => 'process.view', 'description' => 'Visualizar processos.'],
            ['name' => 'process.create', 'description' => 'Criar novo processo.'],
            ['name' => 'process.approve', 'description' => 'Aprovar etapa do processo.'],
            ['name' => 'process.reject', 'description' => 'Recusar processo.'],
            ['name' => 'process.delete', 'description' => 'Excluir processo.'],
            ['name' => 'process.manage_config', 'description' => 'Gerenciar fluxos e notificações.'],

            // 🔹 Módulo de devoluções
            ['name' => 'return.process', 'description' => 'Acessar módulo de processos de devolução.'],
        ];

        $adminPermissions = [
            ['name' => 'coreflow.admin', 'description' => 'Gerenciar usuários e tudo no sistema.'],
        ];

        // 🧱 Cria ou atualiza permissões gerais
        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['name' => $perm['name']], $perm);
        }

        // 🧱 Cria ou atualiza permissões administrativas
        foreach ($adminPermissions as $adminPerm) {
            Permission::updateOrCreate(['name' => $adminPerm['name']], $adminPerm);
        }

        // 🔄 Carrega IDs
        $allPerms = Permission::pluck('id', 'name');

        // 👑 Admins — todas as permissões
        $admins = Level::where('name', 'like', '%Admin%')->get();
        foreach ($admins as $level) {
            $level->permissions()->syncWithoutDetaching($allPerms->values());
        }

        // 🧭 Gerentes — todas as permissões exceto admin
        $managers = Level::where('name', 'like', '%Gerente%')->get();
        foreach ($managers as $level) {
            $level->permissions()->syncWithoutDetaching($allPerms->values());
        }

        // 📋 Analistas — apenas visualizar e criar
        $analysts = Level::where('name', 'like', '%Analista%')->get();
        foreach ($analysts as $level) {
            $level->permissions()->syncWithoutDetaching([
                $allPerms['process.view'] ?? null,
                $allPerms['process.create'] ?? null,
            ]);
        }

        // 🧾 Funcionários do Comercial
        $comercialStaff = Level::where('name', 'Funcionário Comercial')->first();
        if ($comercialStaff) {
            $comercialStaff->permissions()->syncWithoutDetaching([
                $allPerms['process.view'] ?? null,
                $allPerms['process.create'] ?? null,
                $allPerms['return.process'] ?? null,
            ]);
        }

        // 🧰 Funcionários de outros setores
        $otherStaffs = Level::where('name', 'like', 'Funcionário%')
            ->where('name', '!=', 'Funcionário Comercial')
            ->get();

        foreach ($otherStaffs as $level) {
            $level->permissions()->syncWithoutDetaching([
                $allPerms['process.view'] ?? null,
            ]);
        }

        info("✅ Permissões e vínculos atualizados com sucesso!");
    }
}
