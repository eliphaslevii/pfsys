<?php

// database/migrations/YYYY_MM_DD_create_levels_sectors_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de Setores
        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Hierarquia de Setores (Setor pai)
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('sectors')
                  ->onDelete('set null');

            $table->timestamps();
        });

        // Tabela de Níveis/Cargos
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sector_id')
                  ->constrained('sectors')
                  ->onDelete('cascade');
                  
            $table->string('name', 100);
            $table->integer('authority_level')->default(10); // Nível numérico de autoridade (0-100)
            
            // Garante que o nome do cargo é único DENTRO de um setor.
            $table->unique(['sector_id', 'name']);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
        Schema::dropIfExists('sectors');
    }
};