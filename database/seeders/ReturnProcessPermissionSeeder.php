<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Level;
use Illuminate\Support\Facades\DB;

class ReturnProcessPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // 🔹 Permissões principais do módulo
            $permissions = [
                ['name' => 'return_process.view', 'description' => 'Visualizar processos de devolução'],
                ['name' => 'return_process.create', 'description' => 'Criar novo processo de devolução'],
                ['name' => 'return_process.update_step', 'description' => 'Avançar etapas do processo'],
                ['name' => 'return_process.reject', 'description' => 'Recusar processo'],
                ['name' => 'return_process.send_financeiro2', 'description' => 'Enviar processo para Financeiro 2'],
                ['name' => 'return_process.delete', 'description' => 'Excluir processo de devolução'],
            ];

            foreach ($permissions as $permData) {
                Permission::firstOrCreate(
                    ['name' => $permData['name']],
                    ['description' => $permData['description']]
                );
            }

            // 🔹 Mapeamento de níveis → permissões
            $map = [
                '1' => [ // Super Admin
                    'return_process.view',
                    'return_process.create',
                    'return_process.update_step',
                    'return_process.reject',
                    'return_process.send_financeiro2',
                    'return_process.delete',
                ],
                '8' => [ // Comercial
                    'return_process.view',
                    'return_process.create',
                    'return_process.update_step',
                    'return_process.delete',
                ],
                '2' => [ // Financeiro
                    'return_process.view',
                    'return_process.update_step',
                    'return_process.reject',
                ],
                '7' => [ // Logística
                    'return_process.view',
                    'return_process.update_step',
                ],
                '9' => [ // Administrativo
                    'return_process.view',
                    'return_process.update_step',
                    'return_process.send_financeiro2',
                ],
            ];

            // 🔗 Associação (permite múltiplas permissões por nível)
            foreach ($map as $levelId => $permNames) {
                $level = Level::find($levelId);
                if (!$level) continue;

                $permIds = Permission::whereIn('name', $permNames)->pluck('id');
                $level->permissions()->syncWithoutDetaching($permIds);
            }
        });
    }
}
