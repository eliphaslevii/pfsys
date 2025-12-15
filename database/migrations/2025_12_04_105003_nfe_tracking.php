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
        Schema::create('nfe_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nfe_id')->constrained('nfes')->cascadeOnDelete();

            $table->string('transportadora')->nullable();
            $table->string('status')->nullable();
            $table->string('mensagem')->nullable();
            $table->timestamp('data_evento')->nullable();

            $table->timestamps();
        });

        Schema::create('nfe_tracking_state', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nfe_id')
                ->constrained('nfes')
                ->cascadeOnDelete();

            $table->dateTime('next_check_at')->nullable();

            $table->string('last_status')->nullable();
            $table->string('last_message')->nullable();

            $table->boolean('stop_tracking')->default(false);

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
