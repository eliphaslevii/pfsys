<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        /**
         * =========================
         * 1) TIPOS DE PROCESSO
         * =========================
         */
        Schema::create('process_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /**
         * =========================
         * 2) TEMPLATES DE WORKFLOW
         * =========================
         */
        Schema::create('workflow_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_type_id')
                ->constrained('process_types')
                ->cascadeOnDelete();

            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /**
         * =========================
         * 3) MOTIVOS DO WORKFLOW (Workflow Reasons)
         * Nota: Criado antes de 'processes' e 'workflow_steps' para evitar erro 1824
         * =========================
         */
        Schema::create('workflow_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id')
                ->constrained('workflow_templates')
                ->cascadeOnDelete();
            
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /**
         * =========================
         * 4) STEPS DO WORKFLOW
         * =========================
         */
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id')
                ->constrained('workflow_templates')
                ->cascadeOnDelete();

            $table->string('name', 100);
            $table->unsignedInteger('order')->default(1);

            // Relacionamentos opcionais (Set Null)
            $table->foreignId('required_level_id')
                ->nullable()
                ->constrained('levels')
                ->nullOnDelete();

            $table->foreignId('sector_id')
                ->nullable()
                ->constrained('sectors')
                ->nullOnDelete();

            // Auto-relacionamento (Próximo passo / Rejeição)
            $table->foreignId('next_step_id')
                ->nullable()
                ->constrained('workflow_steps')
                ->nullOnDelete();

            $table->foreignId('next_on_reject_step_id')
                ->nullable()
                ->constrained('workflow_steps')
                ->nullOnDelete();

            $table->boolean('auto_notify')->default(true);
            $table->json('rules_json')->nullable();
            $table->timestamps();
        });

        /**
         * =========================
         * 5) PROCESSOS (INSTÂNCIA)
         * =========================
         */
        Schema::create('processes', function (Blueprint $table) {
            $table->id();

            /* --- CONTROLE DO PROCESSO --- */
            $table->foreignId('process_type_id')
                ->constrained('process_types')
                ->cascadeOnDelete();

            $table->foreignId('workflow_template_id')
                ->constrained('workflow_templates')
                ->cascadeOnDelete();

            // Referência à tabela criada no passo 3
            $table->foreignId('workflow_reason_id')
                ->nullable()
                ->constrained('workflow_reasons')
                ->nullOnDelete();

            // Referência à tabela criada no passo 4
            $table->foreignId('current_step_id')
                ->nullable()
                ->constrained('workflow_steps')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('status', 50)->default('Em Andamento');

            /* --- DADOS ESPECÍFICOS --- */
            $table->string('cliente_nome')->nullable();
            $table->string('cliente_cnpj', 20)->nullable();
            $table->string('responsavel', 150)->nullable();
            
            // Campos de texto para histórico ou fallback
            $table->string('motivo')->nullable(); 
            $table->string('codigo_erro')->nullable();

            /* --- DOCUMENTOS FISCAIS --- */
            $table->string('nf_saida', 50)->nullable();
            $table->string('nf_devolucao', 50)->nullable();
            $table->string('nfo', 50)->nullable();
            $table->string('protocolo', 100)->nullable();
            $table->string('recusa_sefaz', 100)->nullable();
            $table->string('nprot', 100)->nullable();

            /* --- LOGÍSTICA / SAP --- */
            $table->string('delivery', 50)->nullable();
            $table->string('doc_faturamento', 50)->nullable();
            $table->string('ordem_entrada', 50)->nullable();
            $table->string('migo', 50)->nullable();
            $table->decimal('valor_cte', 12, 2)->nullable();

            $table->boolean('movimentacao_mercadoria')->default(false);
            $table->text('observacoes')->nullable();

            $table->timestamps();
        });

        /**
         * =========================
         * 6) ITENS DO PROCESSO
         * =========================
         */
        Schema::create('process_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')
                ->constrained('processes')
                ->cascadeOnDelete();

            $table->string('artigo', 50);
            $table->string('descricao', 255);
            $table->string('ncm', 20)->nullable();
            $table->decimal('quantidade', 10, 2)->default(0);
            $table->decimal('preco_unitario', 10, 2)->default(0);
            $table->timestamps();
        });

        /**
         * =========================
         * 7) DOCUMENTOS
         * =========================
         */
        Schema::create('process_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')
                ->constrained('processes')
                ->cascadeOnDelete();

            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('file_type', 50)->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });

        /**
         * =========================
         * 8) LOGS DO PROCESSO
         * =========================
         */
        Schema::create('process_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')
                ->constrained('processes')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('action', 100);
            $table->text('message')->nullable();

            $table->foreignId('from_step_id')
                ->nullable()
                ->constrained('workflow_steps')
                ->nullOnDelete();

            $table->foreignId('to_step_id')
                ->nullable()
                ->constrained('workflow_steps')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        // A ordem de DROP deve ser inversa à de criação para evitar erro de Foreign Key
        Schema::dropIfExists('process_logs');
        Schema::dropIfExists('process_documents');
        Schema::dropIfExists('process_items');
        Schema::dropIfExists('processes');
        Schema::dropIfExists('workflow_steps');   // Steps dependem de Templates
        Schema::dropIfExists('workflow_reasons'); // Reasons dependem de Templates
        Schema::dropIfExists('workflow_templates');
        Schema::dropIfExists('process_types');
    }
};