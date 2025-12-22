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

            // 📌 Processos (geral)
            ['name' => 'process.view', 'description' => 'Visualizar processos.'],
            ['name' => 'process.create', 'description' => 'Criar processos.'],
            ['name' => 'process.update', 'description' => 'Atualizar processos.'],
            ['name' => 'process.delete', 'description' => 'Excluir processos.'],
            ['name' => 'process.reject', 'description' => 'Recusar processos.'],
            ['name' => 'process.approve', 'description' => 'Aprovar e iniciar fluxo.'],
            ['name' => 'process.advance', 'description' => 'Avançar etapa do workflow.'],

            // 📦 Módulos
            ['name' => 'return.process', 'description' => 'Acessar módulo de devoluções.'],

            // 🎛️ Configuração
            ['name' => 'process.manage_config', 'description' => 'Gerenciar fluxos e motivos.'],

            // 👑 Admin
            ['name' => 'coreflow.admin', 'description' => 'Admin geral do sistema.'],
        ];

        // ==========================
        // Criar / atualizar permissões
        // ==========================
        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['name' => $perm['name']],
                $perm
            );
        }

        $all = Permission::pluck('id', 'name');

        // ==========================
        // SUPER ADMIN — TODAS
        // ==========================
        Level::where('name', 'Super Admin')->each(function ($lvl) use ($all) {
            $lvl->permissions()->sync($all->values());
        });

        // ==========================
        // ANALISTA COMERCIAL
        // ==========================
        Level::where('name', 'Analista Comercial')->each(function ($lvl) use ($all) {
            $lvl->permissions()->syncWithoutDetaching([
                $all['process.view'],
                $all['process.create'],
                $all['process.advance'],
                $all['return.process'],
            ]);
        });

        // ==========================
        // GESTOR COMERCIAL
        // ==========================
        Level::where('name', 'Gestor Comercial')->each(function ($lvl) use ($all) {
            $lvl->permissions()->syncWithoutDetaching([
                $all['process.view'],
                $all['process.create'],
                $all['process.reject'],
                $all['process.delete'],
                $all['process.approve'],
                $all['process.advance'],
                $all['process.manage_config'],
                $all['return.process'],
            ]);
        });

        // ==========================
        // FINANCEIRO
        // ==========================
        Level::where('name', 'Analista Financeiro')->each(function ($lvl) use ($all) {
            $lvl->permissions()->syncWithoutDetaching([
                $all['process.view'],
                $all['process.advance'],
                $all['return.process'],
            ]);
        });

        // ==========================
        // LOGÍSTICA
        // ==========================
        Level::where('name', 'Analista Logística')->each(function ($lvl) use ($all) {
            $lvl->permissions()->syncWithoutDetaching([
                $all['process.view'],
                $all['process.advance'],
                $all['return.process'],
            ]);
        });

        // ==========================
        // FISCAL
        // ==========================
        Level::where('name', 'Analista Fiscal')->each(function ($lvl) use ($all) {
            $lvl->permissions()->syncWithoutDetaching([
                $all['process.view'],
                $all['return.process'],
            ]);
        });
    }
}
