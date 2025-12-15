<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('process_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained('processes')->onDelete('cascade');
            $table->foreignId('workflow_step_id')->nullable()->constrained('workflow_steps')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'approved', 'rejected', 'skipped'])->default('pending');
            $table->text('comments')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

    }


    public function down(): void
    {
        Schema::dropIfExists('process_steps');
    }
};
