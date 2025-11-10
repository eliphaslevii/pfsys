<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcessType;

class ProcessTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Devolução',
                'description' => 'Processo de devolução de mercadorias, notas fiscais e ajustes.',
            ],
            [
                'name' => 'Recusa',
                'description' => 'Processo de recusa de notas fiscais ou mercadorias.',
            ],
        ];

        foreach ($types as $type) {
            ProcessType::firstOrCreate(['name' => $type['name']], $type);
        }

        echo "✅ Tipos de processo (Recusa/Devolução) criados com sucesso.\n";
    }
}
