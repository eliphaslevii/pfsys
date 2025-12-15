<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_xml', function (Blueprint $table) {
            $table->id();

            // chave única do documento (ex: chave da NFe ou CTe)
            $table->string('chave')->unique();

            // tipo do documento
            $table->enum('tipo', ['nfe', 'cte', 'evento']);

            // arquivo xml completo
            $table->longText('xml');

            // info auxiliar para facilitar consultas
            $table->dateTime('data_emissao')->nullable();
            $table->string('cnpj_emitente')->nullable();
            $table->string('cnpj_destinatario')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_xml');
    }
};
