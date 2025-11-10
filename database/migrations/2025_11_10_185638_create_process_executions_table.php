<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_executions', function (Blueprint $table) {
            $table->id();

            // 🔗 Referência ao processo principal
            $table->foreignId('process_id')
                ->constrained('processes')
                ->onDelete('cascade');

            // 🔗 Referência ao passo atual no workflow
            $table->foreignId('current_workflow_id')
                ->nullable()
                ->constrained('process_workflows')
                ->onDelete('set null');

            // 🔗 Usuário atualmente responsável pela etapa
            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // 🔗 Usuário que aprovou (caso a etapa tenha sido concluída)
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // 📅 Data/hora da aprovação
            $table->timestamp('approved_at')->nullable();

            // 🧭 Status geral da execução (Ex: Em Andamento, Pendente, Concluído, Rejeitado)
            $table->string('status', 50)->default('Em Andamento');

            // 📝 Observações ou comentários do responsável
            $table->text('observations')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_executions');
    }
};
