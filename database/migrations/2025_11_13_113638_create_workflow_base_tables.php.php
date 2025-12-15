<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        /**
         * ============================================
         * 1) TEMPLATES DE FLUXO (Criados pelo Comercial)
         * ============================================
         * Ex.: "Devolução — Fluxo Padrão"
         *      "Sucateamento"
         *      "Retorno de Material"
         */
        Schema::create('workflow_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_type_id')->constrained('process_types')->onDelete('cascade');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });


        /**
         * ============================================
         * 2) MOTIVOS (Razões que selecionam um template)
         * ============================================
         * Ex.: "Material Descartado"
         *      "Preço errado"
         *      "Transporte PFERD"
         */
        Schema::create('workflow_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id')->constrained('workflow_templates')->onDelete('cascade');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /**
         * ============================================
         * 3) STEPS DO TEMPLATE (etapas ordenadas)
         * ============================================
         * Ex.: Step 1: Comercial (level_id=8)
         *      Step 2: Financeiro (level_id=2)
         *      Step 3: Logística
         */

    }


    public function down(): void
    {
        Schema::dropIfExists('workflow_reasons');
    }
};
