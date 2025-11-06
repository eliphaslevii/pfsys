<?php

// database/migrations/YYYY_MM_DD_add_levels_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Adiciona a coluna level_id e define como chave estrangeira, permitindo nulo (usuários sem nível definido inicialmente)
            $table->foreignId('level_id')
                ->nullable()
                ->after('password')
                ->constrained('levels')
                ->onDelete('set null'); // Previne a exclusão de usuário se o nível for excluído

            // Adiciona a coluna sector_id e define como chave estrangeira
            $table->foreignId('sector_id')
                ->nullable()
                ->after('level_id')
                ->constrained('sectors')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Verifica se as colunas existem antes de tentar remover
            if (Schema::hasColumn('users', 'level_id')) {
                $table->dropColumn('level_id');
            }
            if (Schema::hasColumn('users', 'sector_id')) {
                $table->dropColumn('sector_id');
            }
        });
    }
};