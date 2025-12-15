<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();

            $table->string('tipo_evento')->nullable();
            $table->string('codigo_evento')->nullable();
            $table->string('chave')->index();
            $table->string('sequencia')->nullable();

            $table->dateTime('data_evento')->nullable();
            $table->string('descricao')->nullable();

            $table->string('protocolo')->nullable();   // ← ESTA FALTAVA
            $table->string('status')->nullable();      // ← E ESTA

            $table->string('arquivo_origem')->nullable();

            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
