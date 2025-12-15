<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
            Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_template_id')->constrained('workflow_templates')->onDelete('cascade');
            $table->string('name', 100);
            $table->unsignedInteger('order')->default(1);
            $table->foreignId('required_level_id')->nullable()->constrained('levels')->onDelete('set null');
            $table->foreignId('sector_id')->nullable()->constrained('sectors')->onDelete('set null');
            $table->foreignId('next_step_id')->nullable()->constrained('workflow_steps')->onDelete('set null');
            $table->foreignId('next_on_reject_step_id')->nullable()->constrained('workflow_steps')->onDelete('set null');
            $table->boolean('auto_notify')->default(true);
            $table->json('rules_json')->nullable();
            $table->timestamps();
        });

    }


    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};
