<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('process_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /**
         * 2️⃣ Workflow (etapas e quem pode atuar)
         */
        Schema::create('process_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_type_id')->constrained('process_types')->onDelete('cascade');
            $table->string('step_name', 100);
            $table->foreignId('required_level_id')->nullable()->constrained('levels')->onDelete('set null');
            $table->string('next_step')->nullable();
            $table->boolean('auto_notify')->default(true);
            $table->timestamps();
        });

        /**
         * 3️⃣ Processos principais
         */
        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_type_id')->constrained('process_types')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('current_workflow_id')->nullable()->constrained('process_workflows')->onDelete('set null');

            $table->string('status', 50)->default('Aberto');
            $table->string('cliente_nome')->nullable();
            $table->string('cliente_cnpj', 20)->nullable();

            // Campos fiscais e de controle
            $table->string('nf_saida', 50)->nullable();
            $table->string('nf_devolucao', 50)->nullable();
            $table->string('nfo', 50)->nullable();             // nota fiscal original referenciada
            $table->string('protocolo', 100)->nullable();      // nProt
            $table->string('recusa_sefaz', 100)->nullable();
            $table->string('delivery', 50)->nullable();
            $table->string('doc_faturamento', 50)->nullable();
            $table->string('ordem_entrada', 50)->nullable();
            $table->string('migo', 50)->nullable();

            $table->boolean('movimentacao_mercadoria')->default(false);
            $table->text('observacoes')->nullable();

            $table->timestamps();
        });


        /**
         * 4️⃣ Itens do processo (produtos, quantidades, preços, etc.)
         */
        Schema::create('process_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained('processes')->onDelete('cascade');
            $table->string('artigo', 50);
            $table->string('descricao', 255);
            $table->string('ncm', 20)->nullable();
            $table->decimal('quantidade', 10, 2)->default(0);
            $table->decimal('preco_unitario', 10, 2)->default(0);
            $table->timestamps();
        });

        /**
         * 5️⃣ Documentos anexados (XML, PDFs, imagens, etc.)
         */
        Schema::create('process_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained('processes')->onDelete('cascade');
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('file_type', 50)->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        /**
         * 6️⃣ Logs de movimentação (histórico completo)
         */
        Schema::create('process_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained('processes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action', 100);
            $table->text('message')->nullable();
            $table->timestamps();
        });

        /**
         * 7️⃣ Controle real do processo (andamento / steps executados)
         */
        Schema::create('process_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained('processes')->onDelete('cascade');
            $table->foreignId('workflow_id')->nullable()->constrained('process_workflows')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status', 50)->default('Pendente'); // Pendente, Aprovado, Rejeitado, Em análise
            $table->string('action', 100)->nullable(); // Ex: "Aprovação", "Correção", etc.
            $table->text('comments')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        /**
         * 8️⃣ Regras especiais do processo (validações ou fluxos condicionais)
         */
        Schema::create('process_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_type_id')->constrained('process_types')->onDelete('cascade');
            $table->string('rule_name', 100);
            $table->text('condition')->nullable();
            $table->text('action')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /**
         * 9️⃣ Notificações automáticas (e-mails dinâmicos configuráveis)
         */
        Schema::create('process_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_type_id')->constrained('process_types')->onDelete('cascade');
            $table->foreignId('workflow_id')->nullable()->constrained('process_workflows')->onDelete('set null');
            $table->string('step_name', 100)->nullable();
            $table->string('to', 255)->nullable();
            $table->string('cc', 255)->nullable();
            $table->string('bcc', 255)->nullable();
            $table->string('subject', 255)->nullable();
            $table->string('template_view', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_notifications');
        Schema::dropIfExists('process_rules');
        Schema::dropIfExists('process_steps');
        Schema::dropIfExists('process_logs');
        Schema::dropIfExists('process_workflows');
        Schema::dropIfExists('process_documents');
        Schema::dropIfExists('process_items');
        Schema::dropIfExists('processes');
        Schema::dropIfExists('process_types');
    }
};
