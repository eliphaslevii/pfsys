<?php

// database/migrations/YYYY_MM_DD_create_permissions_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Permissões
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Tabela Pivot (Nível <-> Permissão)
        Schema::create('level_permission', function (Blueprint $table) {
            $table->foreignId('level_id')->constrained('levels')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            
            // Chave primária composta para performance e unicidade
            $table->primary(['level_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('level_permission');
        Schema::dropIfExists('permissions');
    }
};