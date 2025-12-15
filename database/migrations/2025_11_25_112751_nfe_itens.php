<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('nfe_itens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nfe_id')->constrained('nfes')->cascadeOnDelete();

            $table->integer('n_item');                       // nItem
            $table->string('codigo_produto')->nullable();    // cProd
            $table->string('descricao')->nullable();         // xProd
            $table->string('ncm')->nullable();
            $table->string('cfop')->nullable();
            $table->decimal('quantidade', 15, 4)->nullable();
            $table->string('unidade')->nullable();
            $table->decimal('valor_unitario', 15, 4)->nullable();
            $table->decimal('valor_total', 15, 2)->nullable();
            $table->string('fci')->nullable();
            $table->string('pedido_cliente')->nullable();

            $table->decimal('valor_tributos_totais', 15, 2)->nullable(); // vTotTrib

            $table->timestamps();
        });
        Schema::create('nfe_item_icms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nfe_item_id')
                ->constrained('nfe_itens')
                ->cascadeOnDelete();

            $table->string('orig')->nullable();   // origem da mercadoria
            $table->string('cst')->nullable();
            $table->string('mod_bc')->nullable();
            $table->decimal('v_bc', 15, 2)->nullable();
            $table->decimal('p_icms', 10, 4)->nullable();
            $table->decimal('v_icms', 15, 2)->nullable();

            $table->string('tipos')->nullable(); // ex: ICMS00, ICMS10 (opcional para debug)

            $table->timestamps();
        });
        Schema::create('nfe_item_ipi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nfe_item_id')
                ->constrained('nfe_itens')
                ->cascadeOnDelete();

            $table->string('cst')->nullable();
            $table->string('c_enq')->nullable();

            $table->decimal('v_bc', 15, 2)->nullable();
            $table->decimal('p_ipi', 10, 4)->nullable();
            $table->decimal('v_ipi', 15, 2)->nullable();

            $table->timestamps();
        });
        Schema::create('nfe_item_pis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nfe_item_id')
                ->constrained('nfe_itens')
                ->cascadeOnDelete();

            $table->string('cst')->nullable();
            $table->decimal('v_bc', 15, 2)->nullable();
            $table->decimal('p_pis', 10, 4)->nullable();
            $table->decimal('v_pis', 15, 2)->nullable();

            $table->timestamps();
        });
        Schema::create('nfe_item_cofins', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nfe_item_id')
                ->constrained('nfe_itens')
                ->cascadeOnDelete();

            $table->string('cst')->nullable();
            $table->decimal('v_bc', 15, 2)->nullable();
            $table->decimal('p_cofins', 10, 4)->nullable();
            $table->decimal('v_cofins', 15, 2)->nullable();

            $table->timestamps();
        });

        Schema::create('nfe_item_ibscbs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nfe_item_id')
                ->constrained('nfe_itens')
                ->cascadeOnDelete();

            $table->string('cst')->nullable();
            $table->string('class_tributaria')->nullable();  // cClassTrib

            $table->decimal('vbc', 15, 2)->nullable();

            // IBS estadual
            $table->decimal('p_ibsu_f', 10, 4)->nullable();
            $table->decimal('v_ibsu_f', 15, 2)->nullable();

            // IBS municipal
            $table->decimal('p_ibsm_u', 10, 4)->nullable();
            $table->decimal('v_ibsm_u', 15, 2)->nullable();

            // total IBS do item
            $table->decimal('v_ibs', 15, 2)->nullable();

            // CBS
            $table->decimal('p_cbs', 10, 4)->nullable();
            $table->decimal('v_cbs', 15, 2)->nullable();

            $table->timestamps();
        });
        Schema::create('nfe_ibscbs_tot', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nfe_id')
                ->constrained('nfes')
                ->cascadeOnDelete();

            $table->decimal('vbc_ibscbs', 15, 2)->nullable();

            // Totais IBS
            $table->decimal('ibs_uf_vdif', 15, 2)->nullable();
            $table->decimal('ibs_uf_dev', 15, 2)->nullable();
            $table->decimal('ibs_uf_total', 15, 2)->nullable();

            $table->decimal('ibs_mun_vdif', 15, 2)->nullable();
            $table->decimal('ibs_mun_dev', 15, 2)->nullable();
            $table->decimal('ibs_mun_total', 15, 2)->nullable();

            // total IBS
            $table->decimal('v_ibs', 15, 2)->nullable();

            // CBS total
            $table->decimal('v_cbs', 15, 2)->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
