<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Permissões Administrativas/Sistema
            ['name' => 'system.admin', 'description' => 'Acesso total a configurações de sistema e usuários.'],
            ['name' => 'system.view_logs', 'description' => 'Visualização de logs de atividade e erros.'],

            // Permissões de Usuários
            ['name' => 'user.create', 'description' => 'Permite criar novos usuários.'],
            ['name' => 'user.edit', 'description' => 'Permite editar informações de usuários (exceto Super Admin).'],
            ['name' => 'user.view_all', 'description' => 'Permite visualizar todos os usuários, independentemente do setor.'],

            // Permissões Comerciais (Exemplo)
            ['name' => 'commercial.view_pipeline', 'description' => 'Visualizar o pipeline completo de vendas.'],
            ['name' => 'commercial.create_opportunity', 'description' => 'Criar uma nova oportunidade de venda.'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}