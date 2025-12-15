<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ctes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('documento_xml_id')
                ->nullable()
                ->constrained('documentos_xml')
                ->nullOnDelete();

            $table->string('chave')->unique();
            $table->string('numero')->nullable();
            $table->string('serie')->nullable();
            $table->dateTime('data_emissao')->nullable();
            $table->string('cfop')->nullable();
            $table->string('natOp')->nullable();
            $table->string('tipo_servico')->nullable();

            $table->string('origem_cidade')->nullable();
            $table->string('origem_uf')->nullable();

            $table->string('destino_cidade')->nullable();
            $table->string('destino_uf')->nullable();

            $table->string('emitente_cnpj')->nullable();
            $table->string('emitente_nome')->nullable();

            $table->string('remetente_cnpj')->nullable();
            $table->string('remetente_nome')->nullable();

            $table->string('destinatario_cnpj')->nullable();
            $table->string('destinatario_nome')->nullable();

            $table->decimal('valor_total', 15, 2)->nullable();
            $table->decimal('valor_receber', 15, 2)->nullable();

            $table->decimal('peso_bruto', 15, 3)->nullable();
            $table->decimal('peso_cubado', 15, 3)->nullable();
            $table->string('tipo_carga')->nullable();

            $table->text('observacoes')->nullable();
            $table->text('carac_ad')->nullable();
            $table->text('carac_ser')->nullable();

            $table->timestamps();
        });

        Schema::create('cte_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cte_id')->constrained('ctes')->cascadeOnDelete();
            $table->string('chave_nfe')->nullable();
            $table->string('modelo')->nullable();
            $table->string('numero')->nullable();
            $table->decimal('valor', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('cte_passagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cte_id')->constrained('ctes')->cascadeOnDelete();
            $table->string('cidade')->nullable();
            $table->string('uf')->nullable();
            $table->integer('ordem')->nullable();
            $table->timestamps();
        });

        Schema::create('cte_volumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cte_id')->constrained('ctes')->cascadeOnDelete();
            $table->string('tipo')->nullable();
            $table->decimal('quantidade', 15, 3)->nullable();
            $table->decimal('peso', 15, 3)->nullable();
            $table->timestamps();
        });

        Schema::create('cte_entrega_programada', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cte_id')->constrained('ctes')->cascadeOnDelete();
            $table->date('data')->nullable();
            $table->time('hora')->nullable();
            $table->string('tipo')->nullable();
            $table->timestamps();
        });

        Schema::create('cte_emitente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cte_id')->constrained('ctes')->cascadeOnDelete();

            $table->string('cnpj')->nullable();
            $table->string('ie')->nullable();
            $table->string('nome')->nullable();
            $table->string('fantasia')->nullable();

            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep')->nullable();
            $table->string('municipio')->nullable();
            $table->string('uf')->nullable();
            $table->string('fone')->nullable();

            $table->timestamps();
        });

        // 🔥 AQUI ESTÁ A PARTE QUE FALTAVA 🔥
        Schema::create('cte_remetente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cte_id')->constrained('ctes')->cascadeOnDelete();

            $table->string('cnpj')->nullable();
            $table->string('ie')->nullable();
            $table->string('nome')->nullable();
            $table->string('fantasia')->nullable();

            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep')->nullable();
            $table->string('municipio')->nullable();
            $table->string('uf')->nullable();
            $table->string('fone')->nullable();

            $table->timestamps();
        });

        Schema::create('cte_valores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cte_id')->constrained('ctes')->cascadeOnDelete();
            $table->decimal('valor_servico', 15, 2)->nullable();
            $table->decimal('valor_receber', 15, 2)->nullable();
            $table->decimal('base_icms', 15, 2)->nullable();
            $table->decimal('aliquota_icms', 5, 2)->nullable();
            $table->decimal('valor_icms', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('cte_componentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cte_id')->constrained('ctes')->cascadeOnDelete();
            $table->string('nome')->nullable();
            $table->decimal('valor', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('cte_carga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cte_id')->constrained('ctes')->cascadeOnDelete();

            $table->string('prod_pred')->nullable();
            $table->string('xOutCat')->nullable();
            $table->decimal('peso_bruto', 15, 3)->nullable();
            $table->decimal('peso_base_calculo', 15, 3)->nullable();

            $table->timestamps();
        });

        Schema::create('cte_lacres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cte_id')->constrained('ctes')->cascadeOnDelete();
            $table->string('numero')->nullable();
            $table->timestamps();
        });
        Schema::create('cte_destinatario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cte_id')->constrained('ctes')->cascadeOnDelete();

            $table->string('cnpj')->nullable();
            $table->string('ie')->nullable();
            $table->string('nome')->nullable();
            $table->string('fantasia')->nullable();

            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep')->nullable();
            $table->string('municipio')->nullable();
            $table->string('uf')->nullable();
            $table->string('fone')->nullable();

            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('cte_lacres');
        Schema::dropIfExists('cte_carga');
        Schema::dropIfExists('cte_componentes');
        Schema::dropIfExists('cte_valores');
        Schema::dropIfExists('cte_remetente');
        Schema::dropIfExists('cte_emitente');
        Schema::dropIfExists('cte_entrega_programada');
        Schema::dropIfExists('cte_volumes');
        Schema::dropIfExists('cte_passagens');
        Schema::dropIfExists('cte_documentos');
        Schema::dropIfExists('ctes');
        Schema::dropIfExists('cte_destinatario');
        Schema::dropIfExists('cte_remetente');


    }
};
