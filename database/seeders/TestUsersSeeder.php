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
        /**
         * =====================================================
         * 1️⃣ SETORES REAIS
         * =====================================================
         */
        $sectorComercial  = Sector::firstOrCreate(['name' => 'Comercial']);
        $sectorGerCom     = Sector::firstOrCreate(['name' => 'Gerência Comercial']);
        $sectorLogistica  = Sector::firstOrCreate(['name' => 'Logística']);
        $sectorFiscal     = Sector::firstOrCreate(['name' => 'Fiscal']);
        $sectorFinanceiro = Sector::firstOrCreate(['name' => 'Financeiro']);
        $sectorAdmin      = Sector::firstOrCreate(['name' => 'Administrativo']);

        /**
         * =====================================================
         * 2️⃣ LEVELS (CARGOS REAIS)
         * =====================================================
         */
        $levels = [
            ['name' => 'Super Admin',         'sector_id' => $sectorAdmin->id,      'authority_level' => 999],
            ['name' => 'Analista Comercial',  'sector_id' => $sectorComercial->id,   'authority_level' => 10],
            ['name' => 'Gestor Comercial',    'sector_id' => $sectorGerCom->id,      'authority_level' => 20],
            ['name' => 'Analista Logística',  'sector_id' => $sectorLogistica->id,   'authority_level' => 30],
            ['name' => 'Gestor Logística',    'sector_id' => $sectorLogistica->id,   'authority_level' => 35],
            ['name' => 'Analista Fiscal',     'sector_id' => $sectorFiscal->id,      'authority_level' => 40],
            ['name' => 'Analista Financeiro', 'sector_id' => $sectorFinanceiro->id,  'authority_level' => 50],
        ];

        foreach ($levels as $level) {
            Level::updateOrCreate(
                ['name' => $level['name']],
                $level
            );
        }

        /**
         * =====================================================
         * 3️⃣ BUSCAR LEVELS
         * =====================================================
         */
        $lvlSuperAdmin = Level::where('name', 'Super Admin')->first();
        $lvlAnalistaCom = Level::where('name', 'Analista Comercial')->first();
        $lvlGestorCom   = Level::where('name', 'Gestor Comercial')->first();
        $lvlAnalistaLog = Level::where('name', 'Analista Logística')->first();
        $lvlGestorLog   = Level::where('name', 'Gestor Logística')->first();
        $lvlFiscal      = Level::where('name', 'Analista Fiscal')->first();
        $lvlFinanceiro  = Level::where('name', 'Analista Financeiro')->first();

        /**
         * =====================================================
         * 4️⃣ USUÁRIOS REAIS (PROCESSO)
         * =====================================================
         */
        $users = [

            // SUPER ADMIN
            [
                'name'      => 'Luiz Cesar',
                'email'     => 'luiz.cesar@pferd.com',
                'password'  => Hash::make('Jcr1st0#'),
                'sector_id' => $sectorAdmin->id,
                'level_id'  => $lvlSuperAdmin->id,
                'active'    => true,
            ],

            // COMERCIAL — ANALISTAS
            [
                'name'      => 'Cléia Silva',
                'email'     => 'cleia.silva@pferd.com',
                'password'  => Hash::make('Pferd@123'),
                'sector_id' => $sectorComercial->id,
                'level_id'  => $lvlAnalistaCom->id,
                'active'    => true,
            ],
            [
                'name'      => 'Cristiane Moreira',
                'email'     => 'cristiane.moreira@pferd.com',
                'password'  => Hash::make('Pferd@123'),
                'sector_id' => $sectorComercial->id,
                'level_id'  => $lvlAnalistaCom->id,
                'active'    => true,
            ],
            [
                'name'      => 'Denise Vieira',
                'email'     => 'denise.vieira@pferd.com',
                'password'  => Hash::make('Pferd@123'),
                'sector_id' => $sectorComercial->id,
                'level_id'  => $lvlAnalistaCom->id,
                'active'    => true,
            ],

            // GERÊNCIA COMERCIAL
            [
                'name'      => 'Priscila Fabris',
                'email'     => 'priscila.fabris@pferd.com',
                'password'  => Hash::make('Pferd@123'),
                'sector_id' => $sectorGerCom->id,
                'level_id'  => $lvlGestorCom->id,
                'active'    => true,
            ],

            // FISCAL
            [
                'name'      => 'Vitor Hugo',
                'email'     => 'vitor.hugo@pferd.com',
                'password'  => Hash::make('Pferd@123'),
                'sector_id' => $sectorFiscal->id,
                'level_id'  => $lvlFiscal->id,
                'active'    => true,
            ],

            // LOGÍSTICA
            [
                'name'      => 'Leandro Castro',
                'email'     => 'leandro.castro@pferd.com',
                'password'  => Hash::make('Pferd@123'),
                'sector_id' => $sectorLogistica->id,
                'level_id'  => $lvlGestorLog->id,
                'active'    => true,
            ],
            [
                'name'      => 'Marcelo Mendes',
                'email'     => 'marcelo.mendes@pferd.com',
                'password'  => Hash::make('Pferd@123'),
                'sector_id' => $sectorLogistica->id,
                'level_id'  => $lvlAnalistaLog->id,
                'active'    => true,
            ],

            // FINANCEIRO
            [
                'name'      => 'Simone Quadros',
                'email'     => 'simone.quadros@pferd.com',
                'password'  => Hash::make('Pferd@123'),
                'sector_id' => $sectorFinanceiro->id,
                'level_id'  => $lvlFinanceiro->id,
                'active'    => true,
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
    }
}
