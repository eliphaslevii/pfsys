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

            // 📌 Permissões gerais
            ['name' => 'process.view', 'description' => 'Visualizar processos.'],
            ['name' => 'process.create', 'description' => 'Criar processos.'],
            ['name' => 'process.update', 'description' => 'Atualizar processos.'],
            ['name' => 'process.delete', 'description' => 'Excluir processos.'],
            ['name' => 'process.reject', 'description' => 'Recusar processos.'],
            ['name' => 'return.process', 'description' => 'Acessar módulo de devoluções.'],
            ['name' => 'process.approve', 'description' => 'Gestão comercial autoriza processo.'],
            // 🎛️ GERENCIAMENTO DE FLUXO
            ['name' => 'process.manage_config', 'description' => 'Gerenciar fluxos e motivos.'],

            // 🎯 Etapas (workflow)
            ['name' => 'process.step.comercial', 'description' => 'Avançar etapa Comercial.'],
            ['name' => 'process.step.financeiro', 'description' => 'Avançar etapa Financeiro.'],
            ['name' => 'process.step.logistica', 'description' => 'Avançar etapa Logística.'],
            ['name' => 'process.step.comercial_refaturamento', 'description' => 'Avançar etapa Comercial (Refaturamento).'],
            ['name' => 'process.step.logistica_refaturado', 'description' => 'Avançar etapa Logística (Refaturado).'],
            ['name' => 'process.step.financeiro2', 'description' => 'Avançar etapa Financeiro 2.'],

            // Gerencia Logística
            
            // 👑 Admin
            ['name' => 'coreflow.admin', 'description' => 'Admin geral do sistema.'],
        ];

        // Cria permissões
        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['name' => $perm['name']], $perm);
        }

        $all = Permission::pluck('id','name');

        // ================================
        // SUPER ADMIN — TODAS
        // ================================
        Level::where('name', 'Super Admin')->each(function ($lvl) use ($all) {
            $lvl->permissions()->sync($all->values());
        });

        // ================================
        // ANALISTA COMERCIAL
        // ================================
        Level::where('name', 'Analista Comercial')->each(function ($lvl) use ($all) {
            $lvl->permissions()->syncWithoutDetaching([
                $all['process.view'],
                $all['return.process'],
                $all['process.create'],
                $all['process.step.comercial'],
                $all['process.step.comercial_refaturamento'],
            ]);
        });

        // ================================
        // GESTOR COMERCIAL
        // ================================
        Level::where('name','Gestor Comercial')->each(function ($lvl) use ($all) {
            $lvl->permissions()->syncWithoutDetaching([
                $all['process.view'],
                $all['return.process'],
                $all['process.create'],
                $all['process.reject'],
                $all['process.delete'],
                $all['process.approve'],
                $all['process.step.comercial'],
                $all['process.step.comercial_refaturamento'],
                $all['process.manage_config'], // 👈 AGORA TEM!
            ]);
        });

        // ================================
        // FINANCEIRO
        // ================================
        Level::where('name','Analista Financeiro')->each(function ($lvl) use ($all) {
            $lvl->permissions()->syncWithoutDetaching([
                $all['process.view'],
                $all['return.process'],
                $all['process.step.financeiro'],
                $all['process.step.financeiro2'],
            ]);
        });

        // ================================
        // LOGÍSTICA
        // ================================
        Level::where('name','Analista Logística')->each(function ($lvl) use ($all) {
            $lvl->permissions()->syncWithoutDetaching([
                $all['process.view'],
                $all['return.process'],
                $all['process.step.logistica'],
                $all['process.step.logistica_refaturado'],
            ]);
        });

        // ================================
        // FISCAL
        // ================================
        Level::where('name','Analista Fiscal')->each(function ($lvl) use ($all) {
            $lvl->permissions()->syncWithoutDetaching([
                $all['process.view'],
                $all['return.process'],
            ]);
        });
    }
}
