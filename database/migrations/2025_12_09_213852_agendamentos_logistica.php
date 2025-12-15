<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();

            $table->string('transportadora_nome');
            $table->string('transportadora_cnpj')->nullable();

            $table->dateTime('data_agendada')->nullable();   // janela de coleta
            $table->dateTime('data_confirmada')->nullable(); // operador confirma
            $table->dateTime('data_coleta')->nullable();     // coleta realizada

            // 🔥 STATUS OPERACIONAL + TÉCNICO
            $table->enum('status', [
                'pendente',       // criado
                'confirmado',     // assumido
                'em_coleta',      // docs enviados / transportadora avisada
                'coletado',       // finalizado manualmente
                'cancelado',      // cancelado ou erro grave
                'processando',    // job em execução
                'enviado',        // e-mail/zip enviados
                'erro'            // falha técnica
            ])->default('pendente');

            $table->text('observacoes')->nullable();

            $table->timestamps();
        });

        Schema::create('agendamento_nfe', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('agendamento_id');
            $table->unsignedBigInteger('nfe_id');

            $table->boolean('bipado')->default(false);
            $table->dateTime('bipado_em')->nullable();

            $table->timestamps();

            $table->foreign('agendamento_id')
                ->references('id')
                ->on('agendamentos')
                ->onDelete('cascade');

            $table->foreign('nfe_id')
                ->references('id')
                ->on('nfes')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendamento_nfe');
        Schema::dropIfExists('agendamentos');
    }
};
