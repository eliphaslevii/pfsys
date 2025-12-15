<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Level;
use App\Models\Sector;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // ============================
        // 1) BUSCAR SETORES REAIS
        // ============================
        $sectorComercial = Sector::firstOrCreate(['name' => 'Comercial']);
        $sectorGerenciaComercial = Sector::firstOrCreate(['name' => 'Gerência Comercial']);
        $sectorLogistica = Sector::firstOrCreate(['name' => 'Logística']);
        $sectorFiscal = Sector::firstOrCreate(['name' => 'Fiscal']);
        $sectorFinanceiro = Sector::firstOrCreate(['name' => 'Financeiro']);
        $sectorAdmin = Sector::firstOrCreate(['name' => 'Administrativo']);

        // ============================
        // 2) CRIAR LEVELS POR SETOR
        // ============================
        $levels = [
            [
                'name' => 'Super Admin',
                'sector_id' => $sectorAdmin->id,
                'authority_level' => 999,
            ],
            [
                'name' => 'Analista Comercial',
                'sector_id' => $sectorComercial->id,
                'authority_level' => 10,
            ],
            [
                'name' => 'Gestor Comercial',
                'sector_id' => $sectorGerenciaComercial->id,
                'authority_level' => 20,
            ],
            [
                'name' => 'Analista Logística',
                'sector_id' => $sectorLogistica->id,
                'authority_level' => 30,
            ],
            [
                'name' => 'Analista Fiscal',
                'sector_id' => $sectorFiscal->id,
                'authority_level' => 40,
            ],
            [
                'name' => 'Analista Financeiro',
                'sector_id' => $sectorFinanceiro->id,
                'authority_level' => 50,
            ],
        ];

        foreach ($levels as $level) {
            Level::updateOrCreate(
                ['name' => $level['name']],
                $level
            );
        }

        // Buscar levels criados
        $superAdminLevel = Level::where('name','Super Admin')->first();

        $lvlComercial = Level::where('name','Analista Comercial')->first();
        $lvlGerencia = Level::where('name','Gestor Comercial')->first();
        $lvlLogistica = Level::where('name','Analista Logística')->first();
        $lvlFiscal = Level::where('name','Analista Fiscal')->first();
        $lvlFinanceiro = Level::where('name','Analista Financeiro')->first();

        // ============================
        // 3) CRIAR USUÁRIOS
        // ============================
        $users = [
            // SUPER ADMIN (não alterar)
            [
                'name' => 'Super Admin',
                'email' => 'luiz.cesar@pferd.com',
                'password' => Hash::make('Jcr1st0#'),
                'level_id' => $superAdminLevel->id,
                'sector_id' => $sectorAdmin->id,
            ],

            // PRISCILA FABRIS — Gerência Comercial
            [
                'name' => 'Priscila Fabris',
                'email' => 'priscila.fabris@pferd.com',
                'password' => Hash::make('Pferd@123'),
                'level_id' => $lvlGerencia->id,
                'sector_id' => $sectorGerenciaComercial->id,
            ],

            // CLÉIA SILVA — Funcionária Comercial
            [
                'name' => 'Cléia Silva',
                'email' => 'cleia.silva@pferd.com',
                'password' => Hash::make('Pferd@123'),
                'level_id' => $lvlComercial->id,
                'sector_id' => $sectorComercial->id,
            ],

            // COMERCIAL GENÉRICO (já existia)
            [
                'name' => 'Comercial User',
                'email' => 'comercial@bsys.local',
                'password' => Hash::make('123456'),
                'level_id' => $lvlComercial->id,
                'sector_id' => $sectorComercial->id,
            ],

            // GERÊNCIA COMERCIAL (genérico)
            [
                'name' => 'Gestor Comercial',
                'email' => 'gerencia.comercial@bsys.local',
                'password' => Hash::make('123456'),
                'level_id' => $lvlGerencia->id,
                'sector_id' => $sectorGerenciaComercial->id,
            ],

            // LOGÍSTICA
            [
                'name' => 'Logística User',
                'email' => 'logistica@bsys.local',
                'password' => Hash::make('123456'),
                'level_id' => $lvlLogistica->id,
                'sector_id' => $sectorLogistica->id,
            ],

            // FISCAL
            [
                'name' => 'Fiscal User',
                'email' => 'fiscal@bsys.local',
                'password' => Hash::make('123456'),
                'level_id' => $lvlFiscal->id,
                'sector_id' => $sectorFiscal->id,
            ],

            // FINANCEIRO
            [
                'name' => 'Financeiro User',
                'email' => 'financeiro@bsys.local',
                'password' => Hash::make('123456'),
                'level_id' => $lvlFinanceiro->id,
                'sector_id' => $sectorFinanceiro->id,
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['email' => $data['email']], $data);
        }
    }
}
