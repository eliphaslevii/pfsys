<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nfes', function (Blueprint $table) {
            $table->id();

            // Relacionamento com tabela de XML bruto
            $table->foreignId('documento_xml_id')
                ->nullable()
                ->constrained('documentos_xml')
                ->nullOnDelete();

            // Identificação
            $table->string('chave')->unique();
            $table->string('numero')->nullable();      // nNF
            $table->string('serie')->nullable();
            $table->dateTime('data_emissao')->nullable();
            $table->string('natureza_operacao')->nullable(); // natOp
            $table->string('modelo')->nullable(); // mod
            $table->string('tipo_operacao')->nullable(); // tpNF
            $table->string('destino_operacao')->nullable(); // idDest

            // Emitente
            $table->string('emitente_cnpj')->nullable();
            $table->string('emitente_nome')->nullable();
            $table->string('emitente_ie')->nullable();
            $table->string('emitente_endereco')->nullable();
            $table->string('emitente_municipio')->nullable();
            $table->string('emitente_uf')->nullable();
            $table->string('emitente_cep')->nullable();

            // Destinatário
            $table->string('dest_cnpj')->nullable();
            $table->string('dest_nome')->nullable();
            $table->string('dest_ie')->nullable();
            $table->string('dest_endereco')->nullable();
            $table->string('dest_municipio')->nullable();
            $table->string('dest_uf')->nullable();
            $table->string('dest_cep')->nullable();

            // Totais
            $table->decimal('valor_total', 15, 2)->nullable();       // vNF
            $table->decimal('valor_produtos', 15, 2)->nullable();    // vProd
            $table->decimal('valor_frete', 15, 2)->nullable();       // vFrete
            $table->decimal('valor_seguro', 15, 2)->nullable();      // vSeg
            $table->decimal('valor_desconto', 15, 2)->nullable();    // vDesc
            $table->decimal('valor_tributos', 15, 2)->nullable();    // vTotTrib

            // Transporte
            $table->string('transportadora_cnpj')->nullable();
            $table->string('transportadora_nome')->nullable();
            $table->string('mod_frete')->nullable();
            $table->integer('volume_quantidade')->nullable();        // qVol
            $table->string('volume_especie')->nullable();            // esp
            $table->decimal('peso_bruto', 15, 3)->nullable();        // pesoB
            $table->decimal('peso_liquido', 15, 3)->nullable();      // pesoL

            // Cobrança / duplicatas
            $table->string('fatura_numero')->nullable();             // nFat
            $table->decimal('fatura_valor', 15, 2)->nullable();      // vOrig
            $table->date('data_vencimento')->nullable();             // dVenc

            // Informações adicionais
            $table->text('informacoes_adicionais')->nullable();
            $table->string('emitente_iest')->nullable();   // IEST
            $table->string('emitente_im')->nullable();     // IM
            $table->string('crt')->nullable();             // CRT (regime tributário)

            $table->string('protocolo_autorizacao')->nullable();  // nProt
            $table->dateTime('data_autorizacao')->nullable();     // dhRecbto
            $table->string('status_autorizacao')->nullable();     // cStat
            $table->string('motivo_autorizacao')->nullable();   // xMotivo
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nfes');
    }
};
